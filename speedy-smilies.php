<?php
/*
Plugin Name: Speedy Smilies
Plugin URI: http://quietmint.com/speedy-smilies/
Description: Speeds up and beautifies your blog by substituting the individually-wrapped WordPress smilies with a single CSS image sprite containing all emoticons. <a href="themes.php?page=speedy-smilies/admin.php">Configure Speedy Smilies</a>
Author: Nick Venturella
Version: 14
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

function q_smilies_admin_menu() {
	global $q_smilies_src;
	add_theme_page('Speedy Smilies', 'Speedy Smilies', 8, 'speedy-smilies/admin.php');
	add_contextual_help('speedy-smilies/admin', '<p>Speedy Smilies lets you easily change the appearance of the smilies (also called emoticons) that are displayed on your WordPress site.</p><p>Please visit the <a href="http://wordpress.org/tags/speedy-smilies" target="_blank">WordPress.org Forums for Speedy Smilies</a> to ask questions, leave comments, or report bugs.');

	if(empty($q_smilies_src)) return;
	add_meta_box('q_smilies_sectionid', __( 'Speedy Smilies', 'q_smilies_textdomain' ), 'q_smilies_meta_box', 'post', 'side', 'high');
	add_meta_box('q_smilies_sectionid', __( 'Speedy Smilies', 'q_smilies_textdomain' ), 'q_smilies_meta_box', 'page', 'side', 'high');
}

function q_smilies_admin_styles() {
	$cssfile = get_stylesheet_directory() . '/style.css';
	$cssstat = stat($cssfile);
	if (get_option('speedy_smilies_themecache') !== $cssstat['size'] . '-' . $cssstat['mtime'] . '-' . $cssstat['ino']) {
		q_smilies_rebuild();
	}
	unset($cssstat);
	
	$pluginurl = plugin_dir_url(__FILE__);
	$css = <<<CSS
.q_smilies_form fieldset {
	border: 1px solid #DFDFDF;
	margin-bottom: 1.5em;
	padding: .5em 2em;
	border-radius: 3px;
	-moz-border-radius: 3px;
	-khtml-border-radius: 3px;
	-webkit-border-radius: 3px;
}

.q_smilies_form legend {
	font-weight: 700;
	margin-left: -1em;
}

.q_smilies_sample {
	margin: 8px 0 0 20px;
	float: right;
	width: 280px;
}

a.smiley {
	padding: 5px;
	display: block;
	float: left;
}

.q_smilies_indent_div {
	margin-left: 20px;
}

.q_smilies_small_div {
	font-size: 11px;
	line-height: 14px;
	margin: 0 0 8px 21px;
}

.q_smilies_cc_div {
	font-size: 11px;
	line-height: 14px;
	margin-bottom: 10px;
}

.q_smilies_error {
	background-color: #FFEBE8;
	border: 1px solid #CC0000;
	border-radius: 3px;
	-moz-border-radius: 3px;
	-khtml-border-radius: 3px;
	-webkit-border-radius: 3px;
	margin: 4px 0;
	padding: 2px .6em;
}

.q_smilies_icon {
	padding-right: 5px;
	vertical-align: top;
}

.q_smilies_icon_menu {
	padding-right: 4px;
	vertical-align: text-top;
}

.q_smilies_help {
	float: right;
	margin: 24px -18px 0 0;
	padding-right: 18px;
	font-size: 9px;
	line-height: 11px;
	background: url(${pluginurl}helparrow.png) no-repeat center right;
	text-align: right;
	color: #D54E21;
}

.q_smilies_small {
	font-size: 11px;
	line-height: 14px;
}
CSS;
	$css = q_smilies_css_optimize($css);
	
	print <<<HTML
<!-- Begin Speedy Smilies plugin admin -->
<script type='text/javascript'>
/* <![CDATA[ */
function q_smilies_insert(addition){try{tinyMCE.execCommand("mceInsertContent",false," "+addition+" ")}catch(e){var content=document.getElementById('content');var startPos=content.selectionStart;var endPos=content.selectionEnd;content.value=content.value.substring(0,startPos)+addition+content.value.substring(endPos,content.value.length);content.selectionStart=endPos+addition.length;content.selectionEnd=content.selectionStart;content.focus()}return false}function q_smilies_input_disable(element,state){var input=document.getElementById(element).getElementsByTagName("input");for(var i=0;i<input.length;i++)input[i].disabled=state}
/* ]]> */
</script>
<style type='text/css'>
$css
</style>
HTML;
	q_smilies_stylesheet_head();
	print "<!-- End Speedy Smilies plugin admin -->\r\n";
}

function q_smilies_meta_box() {
	global $q_smilies_positions;
	if(empty($q_smilies_positions)) return;

	foreach (array_unique($q_smilies_positions) as $smiley => $position) {
		$alt = attribute_escape($smiley);
		print " <a class='smiley' href='#' onclick='q_smilies_insert(\" $alt \");'><img src='" . includes_url() . "images/blank.gif' alt='$alt' title='$alt' class='wp-smiley smiley-$position' /></a> ";
	}
	print '<div style="clear: both;"></div>';
}


// Check for incompatible plugins
function q_smilies_compatibility_check() {
	global $q_smilies_warninghtml;
	$method = get_option('speedy_smilies_method');
	$q_smilies_warninghtml = '';
	remove_filter('stylesheet_uri', 'q_smilies_stylesheet_uri');
	if (has_filter('stylesheet_uri')) {
		$q_smilies_warninghtml = '<div class="q_smilies_error"><strong>Compatibility Warning:</strong> Another plugin is attempting to use the <code>stylesheet_uri</code> filter. Speedy Smilies should still work, but for optimal speed and performance, disable the conflicting plugin then enable the preferred method below.</div>';
		if ($method === 'fast') {
			$method = 'slow';
			update_option('speedy_smilies_method', 'slow');
		}
	}
	if ($method === 'fast') add_filter('stylesheet_uri', 'q_smilies_stylesheet_uri');
}


function q_smilies_activate() {
	add_option('speedy_smilies_set', 'wordpress', '', 'yes');
	add_option('speedy_smilies_method', '', '', 'yes');
	add_option('speedy_smilies_cache', '', '', 'yes');
	add_option('speedy_smilies_themecache', '', '', 'no');
	add_option('speedy_smilies_donotify', '', '', 'no');
	add_option('speedy_smilies_datauri', '', '', 'no');
}


function q_smilies_init() {
	global $q_smilies_set, $q_smilies_src, $q_smilies_width, $q_smilies_height, $q_smilies_positions, $q_smilies_search, $q_smilies_replace;
	$q_smilies_src = ''; $q_smilies_width = 0; $q_smilies_height= 0; $q_smilies_positions = array(); $q_smilies_search = array(); $q_smilies_replace = array();

	// Don't bother setting up smilies if they are disabled
	if (!get_option('use_smilies')) return;

	$toeval = 'return array( ' . file_get_contents(plugin_dir_path(__FILE__) . "sets/$q_smilies_set.php") . ');';
	$set_array = eval($toeval);
	$q_smilies_width = $set_array['width'];
	$q_smilies_height = $set_array['height'];
	$q_smilies_positions = $set_array['map'];
	$q_smilies_src = plugin_dir_url(__FILE__) . "sets/$q_smilies_set.png";

	foreach ($q_smilies_positions as $smiley => $position) {
		$alt = attribute_escape($smiley);
		$q_smilies_search[] = '/(\s|^)' . preg_quote( $smiley, '/' ) . '(\s|$)/';		
		$q_smilies_replace[] = " <img src='" . includes_url() . "images/blank.gif' alt='$alt' title='$alt' class='wp-smiley smiley-$position' /> ";
	}
	
	// Rebuild CSS cache if necessary
	if (!get_option('speedy_smilies_cache')) q_smilies_rebuild();
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

function q_smilies_stylesheet_uri() { return plugin_dir_url(__FILE__) . 'cache/' . get_option('speedy_smilies_cache') . '.css'; }
function q_smilies_stylesheet_head() { print '<link rel="stylesheet" type="text/css" href="' . plugin_dir_url(__FILE__) . 'cache/' . get_option('speedy_smilies_cache') . '_standalone.css" />' . "\r\n"; }


function q_smilies_rebuild_notify() {
	$cachedate = date("j M Y \a\\t g:i a", get_option('speedy_smilies_cache'));
	print '<div class="updated fade"><p><strong><img class="q_smilies_icon" src="' . plugin_dir_url(__FILE__) . 'icon.gif" /> Speedy Smilies</strong> detected a change in your blog and regenerated the stylesheet cache on ' . $cachedate . '</p></div>';
	update_option('speedy_smilies_donotify', '');
}

function q_smilies_rebuild($donotify = true) {
	global $q_smilies_set, $q_smilies_src, $q_smilies_width, $q_smilies_height, $q_smilies_positions;
	
	// Save the stat info for the theme's CSS to auto-detect changes later
	$cssfile = get_stylesheet_directory() . '/style.css';
	$cssstat = stat($cssfile);
	update_option('speedy_smilies_themecache', $cssstat['size'] . '-' . $cssstat['mtime'] . '-' . $cssstat['ino']);
	unset($cssstat);

	// Load the theme's CSS
	$css = file_get_contents($cssfile);
	$directory = get_stylesheet_directory_uri();

	// Rewrite relative URLs in theme CSS
	$css = preg_replace('!url\(\s?\'?"?(.+?)\'?"?\s?\)!', "url(\\1)", $css);
	$css = preg_replace('!url\(([^/].+?)\)!', "url($directory/\\1)", $css);

	// Embed the image with a data: URI?
	$includedimage = $q_smilies_src;
	if(get_option('speedy_smilies_datauri') == 'yes') {
		$imagebase64 = base64_encode(file_get_contents( dirname(__FILE__) . "/sets/$q_smilies_set.png" ));
		$includedimage = 'data:image/png;base64,' . $imagebase64;
	}

	// Generate Speedy Smilies CSS
	$smiliescss = <<<CSS
.wp-smiley {
	background-image: url($includedimage) !important;
	background-repeat: no-repeat !important;
	vertical-align: text-top !important;
	display: inline !important;
	padding: 0 !important;
	border: none !important;
	height: {$q_smilies_height}px !important;
	width: {$q_smilies_width}px !important;
}
CSS;
	foreach (array_unique($q_smilies_positions) as $smiley => $position)
	$smiliescss .= ".wp-smiley.smiley-$position{background-position:" . ($position - 1) * $q_smilies_width * -1 . "px!important}";
	
	// Compress and optimize CSS
	$css = q_smilies_css_optimize($css);
	$smiliescss = q_smilies_css_optimize($smiliescss);

	// Delete old CSS files
	$dir = plugin_dir_path(__FILE__);
	if (is_dir("{$dir}cache")) {	
		$oldcache = get_option('speedy_smilies_cache');
		if($oldcache) {
			@unlink("{$dir}cache/{$oldcache}.css");
			@unlink("{$dir}cache/{$oldcache}_standalone.css");
		}
	} else { @mkdir("{$dir}cache"); }

	// Save new CSS files
	$newcache = microtime(1);
	file_put_contents("{$dir}cache/{$newcache}.css", $css . $smiliescss);
	file_put_contents("{$dir}cache/{$newcache}_standalone.css", $smiliescss);
	update_option('speedy_smilies_cache', $newcache);

	// Clear cache if WP Super Cache or W3 Total Cache plugins are running
	if (function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
	if (function_exists('w3tc_pgcache_flush')) w3tc_pgcache_flush();

	// Notify admin user
	if ($donotify) {
		update_option('speedy_smilies_donotify', 'yes');
		add_action('admin_notices', 'q_smilies_rebuild_notify');
	}
}

function q_smilies_css_optimize($css) {
	// Delete comments
	$css = preg_replace('!/\*.*?\*/!s', "", $css);

	// Delete unnecessary spaces
	$css = preg_replace('!\s*([;:{},])\s*!', "$1", $css);
	$css = preg_replace('!\s+!', " ", $css);
	
	// Delete trailing semicolons
	$css = preg_replace('!;}!', "}", $css);
	
	// Delete unnecessary measurements
	$css = preg_replace('!([: ])0(%|cm|em|ex|in|mm|pc|pt|px)!', "\${1}0", $css);
	$css = preg_replace('!:([0-9]+(?:\.[0-9]*)?+(?:%|cm|em|ex|in|mm|pc|pt|px)?) \1 \1 \1!iU', ":$1", $css);
	$css = preg_replace('!:([0-9]+(?:\.[0-9]*)?+(?:%|cm|em|ex|in|mm|pc|pt|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|cm|em|ex|in|mm|pc|pt|px)?) \1 \2!iU', ":$1 $2", $css);
	$css = preg_replace('!:([0-9]+(?:\.[0-9]*)?+(?:%|cm|em|ex|in|mm|pc|pt|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|cm|em|ex|in|mm|pc|pt|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|cm|em|ex|in|mm|pc|pt|px)?) \2!iU', ":$1 $2 $3", $css);
	
	return $css;
}

// Disable WordPress default smilies
remove_action('init', 'smilies_init', 5);
remove_filter('the_content', 'convert_smilies');
remove_filter('the_excerpt', 'convert_smilies');
remove_filter('comment_text', 'convert_smilies', 20);

// Add Speedy Smilies hooks
register_activation_hook(__FILE__, 'q_smilies_activate');
add_action('init', 'q_smilies_init', 5);
add_action('admin_menu', 'q_smilies_admin_menu');
add_action('admin_print_styles', 'q_smilies_admin_styles');
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
