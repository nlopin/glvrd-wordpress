<?php
/**
 * @package Glvrd
 */
/*
Plugin Name: Glvrd. Info style check
Plugin URI: http://GLVRD.com/
Description: Plugin uses the glvrd.ru service to help user to write perfect text in Russian language.
Version: 1.0
Author: Nick Lopin
Author URI: http://lopinopulos.ru
License: GPLv2 or later
Text Domain: glvrd
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

define('GLVRD_VERSION', '1.0');

// add new buttons
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

add_action('admin_init', 'glvrd_admin_init');

function glvrd_admin_init()
{
    wp_enqueue_script('my-plugin-script', 'http://api.glvrd.ru/v1/glvrd.js', array('jquery')); //TODO only on post and page edit screens
    wp_enqueue_style('glvrd-styles', plugins_url('/css/glvrd.css', __FILE__));
}

add_action('add_meta_boxes', 'glvrd_add_meta_box');

function glvrd_add_meta_box()
{
    $screens = array('post', 'page');
    foreach ($screens as $screen) {
        add_meta_box(
            'glvrd_section',
            __('Glvrd advice', 'glvrd'),
            'glvrd_meta_box_markup',
            $screen,
            'normal',
            'core'
        );
    }
}

function glvrd_meta_box_markup()
{
    //statistic block
    // echo '<div class="stats" style="display:none;">
    //advices block
    echo '<div class="rule">' . __('No results', 'glvrd') . '</div>';
    echo '<div class="stats">
                    <div class="stats-score-div">
                        <span class="stats-score">-</span>
                        <span class="stats-score-suffix">баллов</span> из 10
                        <br>
                        по шкале Главреда
                    </div>

                    <div class="stats-text-div">
                        <span class="stats-sentences">2</span>
                        <span class="stats-sentences-suffix">предложения</span>
                        <br>
                        <span class="stats-words">70</span>
                        <span class="stats-words-suffix">слов</span>,

                        <span class="stats-chars">569</span>
                        <span class="stats-chars-suffix">знаков</span>
                    </div>
            
                    <div class="stats-result-div">
                        <span class="stats-stopwords">6</span>
                        <span class="stats-stopwords-suffix">стоп-слов</span>.

                        <span class="stats-sections-prefix">Основные проблемы:</span>

                        <span class="stats-sections"><em data-section="необъективная оценка" class="">необъективная оценка</em> <em data-section="скажите это по-русски">скажите это по-русски</em> <em data-section="обобщение" class="active">обобщение</em> <em data-section="усилитель">усилитель</em></span>
                    </div>
                    <br style="clear: both;">

                </div>';
}