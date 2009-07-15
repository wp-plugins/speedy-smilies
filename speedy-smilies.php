<?php
/*
Plugin Name: Speedy Smilies
Plugin URI: http://quietmint.com/speedy-smilies/
Description: Speeds up and beautifies your blog by substituting the individually-wrapped WordPress smilies with a single CSS image sprite containing all emoticons.
Author: Nick Venturella
Version: 0.3
Author URI: http://quietmint.com/


    Speedy Smilies
    Copyright 2009 Nick Venturella

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


require_once('init.php');
$q_smilies_set = get_option('speedy_smilies_set');

function q_smilies_admin_notice() { if(substr($_SERVER["PHP_SELF"], -11) == 'plugins.php') print q_smilies_compatibility_check(); }
function q_smilies_admin_menu() {
	global $q_smilies_src;
	add_theme_page('Speedy Smilies', 'Speedy Smilies', 8, 'speedy-smilies/admin.php');

	if(empty($q_smilies_src)) return;
	add_meta_box('q_smilies_sectionid', __( 'Speedy Smilies', 'q_smilies_textdomain' ), 'q_smilies_meta_box', 'post', 'side', 'high');
	add_meta_box('q_smilies_sectionid', __( 'Speedy Smilies', 'q_smilies_textdomain' ), 'q_smilies_meta_box', 'page', 'side', 'high');
}

function q_smilies_admin_styles() {
	global $q_smilies_src, $q_smilies_width, $q_smilies_height, $q_smilies_positions;
	print <<<HTML
<!-- Begin Speedy Smilies plugin -->
<script type='text/javascript'>
function q_smilies_insert(addition) {
	try { tinyMCE.execCommand("mceInsertContent", false, " " + addition + " "); }
	catch(e) {
		var content = document.getElementById('content');
		var startPos = content.selectionStart;
		var endPos = content.selectionEnd;
		content.value = content.value.substring(0, startPos) +  addition + content.value.substring(endPos, content.value.length);
		content.selectionStart = endPos + addition.length;
		content.selectionEnd = content.selectionStart;
		content.focus();
	}
	return false;
}

function q_smilies_input_disable(element, state) {
	var input = document.getElementById(element).getElementsByTagName("input");
	for(var i = 0; i < input.length; i++) input[i].disabled = state;
}
</script>
<style type='text/css'>
form.q_smilies_form fieldset {border:1px solid #DFDFDF;margin:0 0 1.5em;padding:0.5em 2em;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;}form.q_smilies_form legend {font-weight:bold;margin-left:-1em;}.q_smilies_sample {float:right;width:40%;margin-left:2em;}a.smiley{padding:5px;display:block;float:left}
HTML;
	if(!empty($q_smilies_src)) {
		print ".wp-smiley{background-image:url($q_smilies_src);background-repeat:no-repeat;vertical-align:text-top;padding:0;border:none;height:{$q_smilies_height}px;width:{$q_smilies_width}px}";
		foreach (array_unique($q_smilies_positions) as $smiley => $position) print ".wp-smiley.smiley-$position{background-position:" . ($position - 1) * $q_smilies_width * -1 . "px}";
	}
	print "\r\n</style>\r\n<!-- End Speedy Smilies plugin -->\r\n";
}

function q_smilies_meta_box() {
	global $q_smilies_positions;
	if(empty($q_smilies_positions)) return;

	foreach (array_unique($q_smilies_positions) as $smiley => $position) {
		$alt = attribute_escape($smiley);
		print " <a class='smiley' href='#' onclick='q_smilies_insert(\" $alt \");'><img src='/wp-includes/images/blank.gif' alt='$alt' title='$alt' class='wp-smiley smiley-$position' /></a> ";
	}
	print '<div style="clear: both;"></div>';
}

function q_smilies_init() {
	global $q_smilies_set, $q_smilies_src, $q_smilies_width, $q_smilies_height, $q_smilies_positions, $q_smilies_search, $q_smilies_replace;

	$q_smilies_src = ''; $q_smilies_width = 0; $q_smilies_height= 0; $q_smilies_positions = array(); $q_smilies_search = array(); $q_smilies_replace = array();

	// Don't bother setting up smilies if they are disabled
	if (!get_option('use_smilies')) return;

	q_smilies_load_set();
	foreach ($q_smilies_positions as $smiley => $position) {
		$alt = attribute_escape($smiley);
		$q_smilies_search[] = '/(\s|^)' . preg_quote( $smiley, '/' ) . '(\s|$)/';		
		$q_smilies_replace[] = " <img src='/wp-includes/images/blank.gif' alt='$alt' title='$alt' class='wp-smiley smiley-$position' /> ";
	}
}

function q_smilies_replace($text) {
	global $q_smilies_search, $q_smilies_replace;
	if(empty($q_smilies_search)) return $text;

	$output = '';
	// (Old) HTML loop taken from texturize function, could possible be consolidated
	$textarr = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
	$stop = count($textarr);// loop stuff
	for ($i = 0; $i < $stop; $i++) {
		$content = $textarr[$i];
		if ((strlen($content) > 0) && ('<' != $content{0})) { // If it's not a tag
			$content = preg_replace($q_smilies_search, $q_smilies_replace, $content);
		}
		$output .= $content;
	}
	return $output;
}

function q_smilies_stylesheet_uri($text) { return WP_PLUGIN_URL . "/speedy-smilies/style.php"; }

// Disable WordPress default smilies
remove_action('init', 'smilies_init', 5);
remove_filter('the_content', 'convert_smilies');
remove_filter('the_excerpt', 'convert_smilies');
remove_filter('comment_text', 'convert_smilies', 20);

add_action('init', 'q_smilies_init', 5);
add_action('admin_menu', 'q_smilies_admin_menu');
add_action('admin_notices', 'q_smilies_admin_notice');
add_action('admin_print_styles', 'q_smilies_admin_styles');
add_filter('stylesheet_uri', 'q_smilies_stylesheet_uri');
add_filter('the_content', 'q_smilies_replace');
add_filter('the_excerpt', 'q_smilies_replace');
add_filter('comment_text', 'q_smilies_replace', 20);
