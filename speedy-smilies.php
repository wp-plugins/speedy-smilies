<?php
/*
Plugin Name: Speedy Smilies
Plugin URI: http://quietmint.com/speedy-smilies/
Description: Speeds up and beautifies your blog by substituting the individually-wrapped WordPress smilies with a single CSS image sprite containing all emoticons. <a href="themes.php?page=speedy-smilies/admin.php">Configure Speedy Smilies</a>
Author: Nick Venturella
Version: 15
Author URI: http://quietmint.com/

Speedy Smilies
Copyright 2011 Nick Venturella

Speedy Smilies is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Speedy Smilies is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Speedy Smilies.  If not, see <http://www.gnu.org/licenses/>.
*/


global $q_smilies_set, $q_smilies_src, $q_smilies_width, $q_smilies_height, $q_smilies_positions, $q_smilies_search, $q_smilies_replace;
$q_smilies_set = get_option('speedy_smilies_set');

require_once('functions.php');


// Disable WordPress default smilies
remove_action('init', 'smilies_init', 5);
remove_filter('the_content', 'convert_smilies');
remove_filter('the_excerpt', 'convert_smilies');
remove_filter('comment_text', 'convert_smilies', 20);

// Add Speedy Smilies hooks
register_activation_hook(__FILE__, 'q_smilies_activate');
add_action('init', 'q_smilies_init', 5);
add_action('admin_menu', 'q_smilies_admin_menu');
add_action('add_meta_boxes', 'q_smilies_admin_meta');
add_action('admin_print_styles-speedy-smilies/admin.php', 'q_smilies_admin_styles');
add_action('admin_print_styles-post.php', 'q_smilies_admin_styles');
add_action('admin_print_styles-post-new.php', 'q_smilies_admin_styles');
if (get_option('speedy_smilies_donotify') === 'yes') add_action('admin_notices', 'q_smilies_rebuild_notify');
add_filter('the_content', 'q_smilies_replace');
add_filter('the_excerpt', 'q_smilies_replace');
add_filter('comment_text', 'q_smilies_replace', 20);

// Select method of implementation
$method = get_option('speedy_smilies_method');
if (!$method) {
	// If no method is selected, default to fast unless there are known compatibility problems
	update_option('speedy_smilies_method', 'fast');
	q_smilies_compatibility_check();
	$method = get_option('speedy_smilies_method');
}

// Engage Speedy Smilies
if ($method === 'fast') {
	add_filter('stylesheet_uri', 'q_smilies_stylesheet_uri');
	add_action('switch_theme', 'q_smilies_rebuild');
} else add_filter('wp_head', 'q_smilies_stylesheet_head');
