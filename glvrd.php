<?php
/**
 * @package Glvrd
 */
/*
Plugin Name: Russian texts proofreader Glvrd
Plugin URI: https://glvrd.ru/apps/
Description: Plugin uses the glvrd.ru service for proofreading in russian language.
Version: 1.0
Author: Nick Lopin
Author URI: http://lopinopulos.ru
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

add_filter('mce_buttons', 'glvrd_register_button');

function glvrd_register_button($buttons)
{
    array_push($buttons, 'glvrd');
    return $buttons;
}

add_filter('mce_external_plugins', 'glvrd_register_tinymce_plugin');

function glvrd_register_tinymce_plugin($plugins_array)
{
    $plugins_array['glvrd'] = plugins_url('/js/glvrd-plugin.js', __FILE__);
    return $plugins_array;
}

add_action('admin_enqueue_scripts', 'glvrd_enqueue_scripts');

function glvrd_enqueue_scripts($hook) 
{
	if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
		wp_enqueue_script('sprintf', plugins_url('/js/sprintf.js', __FILE__));
        wp_enqueue_script('glvrd-script', '//api.glvrd.ru/v1/glvrd.js', array('jquery', 'sprintf'));
        wp_enqueue_style('glvrd-styles', plugins_url('/css/glvrd.css', __FILE__));
    }
}

add_action('add_meta_boxes', 'glvrd_add_meta_box');

function glvrd_add_meta_box()
{
    $screens = array('post', 'page');
    foreach ($screens as $screen) {
        add_meta_box(
            'glvrd_section',
            'Главред советует',
            'glvrd_meta_box_markup',
            $screen,
            'normal',
            'core'
        );
    }
}

function glvrd_meta_box_markup()
{
    //advices block
    echo '<div class="rule">' .
    		'<p>Вставьте текст и нажмите на оранжевую кнопку с курительной трубкой для проверки.</p>' .
    		'<p>Подходит для рекламы, новостей, статей, сайтов, инструкций, писем и коммерческих предложений.</p>' .
    	'</div>';
    //statistic block
    echo '<div class="stats" style="display:none">
                    <div class="stats-score-div">
                        <span class="stats-score">-</span>
                        <span class="stats-score-suffix">баллов</span> из 10
                        <br>
                        по шкале Главреда
                    </div>

                    <div class="stats-text-div">
                        <span class="stats-sentences"></span>
                        <br>
                        <span class="stats-words"></span>,
                        <span class="stats-chars"></span>
                    </div>
            
                    <div class="stats-result-div">
                        <span class="stats-stopwords"></span>
						<br>
                        <span>
                        	<a class="send-to-glvrd" href="http://glvrd.ru" target="_blank">Отправить в Главред</a>
                    	</span>
                    </div>
                    <br style="clear: both;">

                </div>';
}
