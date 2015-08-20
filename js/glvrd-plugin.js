(function() {
    tinymce.create('tinymce.plugins.glvrd', {

		hintCls: 'glvrd-underline',
		activeHintCls: 'glvrd-underline-active',
		hoverHintCls: 'glvrd-underline-hover',

		isFirstLaunch: true,
		delayedProofread: undefined,

        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
        	var me = this;

 			ed.addButton('glvrd', {
                title : 'Проверить текст на стоп-слова',
                cmd : 'glvrdCheckContent',
                image : url + '/../images/glvrd-icon.png'
            });

            ed.on('init', function() {
	            if (ed.settings.content_css !== false) {
	               ed.dom.loadCSS(url + '/../css/glvrd.css');
	            }
				
				jQuery(ed.getBody()).delegate('.' + this.hintCls + ', .' + this.activeHintCls, 'mouseenter mouseleave', function(e){
					jQuery(e.target).toggleClass(this.hoverHintCls);
				}.bind(this));				

	            jQuery(ed.getBody()).delegate('.' + this.hintCls, 'mouseover', function(e){
	            	var target = jQuery(e.target);
	            	jQuery(ed.getDoc()).find('.' + this.activeHintCls).toggleClass(this.activeHintCls).toggleClass(this.hintCls);

	            	target.toggleClass(this.activeHintCls).toggleClass(this.hintCls);
	            	jQuery('#glvrd_section .inside .rule').html(
	            		'<h1>' + target.data('name') + '</h1>' +
	            		'<div class="rule-description">' + target.data('description') + '</div>'
            		).show('fast');

	            }.bind(this));
         	}.bind(this));

         	ed.on('ExecCommand', function(e) {
         		if(e.command === 'mceInsertContent') {
         			var selection = e.target.selection,
         				bookmark = selection.getBookmark(2, true),
         				cleanContent = this.removeMarkup(e.target.getContent());

     				e.target.setContent(cleanContent, ({format: "raw"}));
     				selection.moveToBookmark(bookmark);
         		}
         	}.bind(this));

         	ed.on('SaveContent', function(e){
         		e.content = this.removeMarkup(e.target.getContent());
         	}.bind(this));

            ed.addCommand('glvrdCheckContent', function(e) {
            	var bookmark = this.selection.getBookmark(2, true);

            	me.proofread(this, bookmark);

            	if (me.isFirstLaunch) {
            		me.isFirstLaunch = false;
            		jQuery(this.getBody()).on('keyup paste change keypress', function(event) {
            			if (me.delayedProofread) {
            				console.log('clearTimeout');
            				clearTimeout(me.delayedProofread);
            			}
            			console.log('setTimeout');
            			me.delayedProofread = setTimeout(function(){
            				console.log('ExecutingTimeout');
            				me.proofread(this, this.selection.getBookmark(2, true));
            			}.bind(this), 1000);
            		}.bind(this));
            	}
        	});
        },

        proofread: function(editor, bookmark) {
        	var me = this,
        		content = me.removeMarkup(editor.getContent()),
        		strippedContent = content.replace(/(<([^>]+)>)/ig, "");

        	window.glvrd.proofread(content, function(result) {
            		if (result.status = 'ok') {
            			var $statsBlock = jQuery('#glvrd_section .stats'),
            				offset = 0;

            			jQuery('#glvrd_section .rule').hide('fast').html('');
            			$statsBlock.find('.stats-score').text(result.score);
	            		$statsBlock.find('.stats-stopwords').text(result.fragments.length);
            			$statsBlock.find('.stats-sentences').text(me.countSentences(strippedContent));
            			$statsBlock.find('.stats-words').text(me.countWords(strippedContent));
            			$statsBlock.find('.stats-chars').text(me.countChars(strippedContent));

            			if (result.fragments.length) {
            				$statsBlock.find('a.send-to-glvrd').attr('href', result.fragments[0].url);
            			}
            			$statsBlock.show('slow');

            			result.fragments.forEach(function(fragment) {
            				var tagOpen = '<span class="glvrd-underline" data-glvrd="true" data-description="' + fragment.hint.description + '" data-name="' + fragment.hint.name + '" >',
            					tagClose = '</span>',
            					tagsLength = tagOpen.length + tagClose.length;

							content = content.substring(0, fragment.start + offset)
                    			+ tagOpen + content.substring(fragment.start + offset, fragment.end + offset)
                    			+ tagClose + content.substring(fragment.end + offset, content.length);
                			offset += tagsLength;
            			});
            			this.setContent(content, ({format: "raw"}));
            			this.selection.moveToBookmark(bookmark);
            		} else {
            			alert(result.message);
            		}
            	}.bind(editor));
		},

        countSentences: function(text) {
        	if (text.length === 0) {
        		return 0;
        	}

        	var sentencesQuantity = text.match(/[^\s](\.|…|\!|\?)(?!\w)(?!\.\.)/g).length;
			if (!/(\.|…|\!|\?)/g.test(text.slice(-1))) {
  				sentencesQuantity++;
			}
    		return sentencesQuantity;
        },

        countWords: function(text) {
        	if (text.length === 0) {
        		return 0;
        	}

    		return text.replace(/[А-Яа-яA-Za-z0-9-]+([^А-Яа-яA-Za-z0-9-]+)?/g, ".").length;
        },

        countChars: function(text) {
        	if (text.length === 0) {
        		return 0;
        	}

    		return text.replace(/[^А-Яа-яA-Za-z0-9-\s.,()-]+/g, "").length;
        },

        removeMarkup: function(text) {
        	var reg = /(<span[^>]*data-glvrd="true"[^>]*>)(.+?)(<\/span>)/g;
        	return text.replace(reg,'$2');
        },
 
        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'Glvrd for TinyMCE',
                author : 'Nick Lopin',
                authorurl : 'http://lopinopulos.ru',
                infourl : 'http://glvrd.ru',
                version : "0.1"
            };
        }
    });
 
    // Register plugin
    tinymce.PluginManager.add( 'glvrd', tinymce.plugins.glvrd );
})();