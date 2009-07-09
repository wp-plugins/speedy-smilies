<?php
/*
Plugin Name: Speedy Smilies
Plugin URI: http://quietmint.com/speedy-smilies/
Description: Speeds up and beautifies your blog by substituting the individually-wrapped WordPress smilies with a single CSS image sprite containing all emoticons.
Author: Nick Venturella
Version: 0.2
Author URI: http://quietmint.com/


=== ATTENTION USERS! ===
You can adjust the setting for this plugin using the Appearance > Speedy Smilies section of the admin control panel.
You should not need to edit any of the code in the plugin's .php files.

*/




$q_smilies_set = get_option('speedy_smilies_set');

function q_smilies_admin_menu() {
	add_theme_page('Speedy Smilies', 'Speedy Smilies', 8, 'speedy-smilies/admin.php');
	add_meta_box('q_smilies_sectionid', __( 'Speedy Smilies', 'q_smilies_textdomain' ), 'q_smilies_meta_box', 'post', 'side', 'high');
	add_meta_box('q_smilies_sectionid', __( 'Speedy Smilies', 'q_smilies_textdomain' ), 'q_smilies_meta_box', 'page', 'side', 'high');
}

function q_smilies_admin_styles() {
	global $q_smilies_src, $q_smilies_size, $q_smilies_positions;
	print "\r\n<style type='text/css'>\r\n/* Added by Speedy Smilies plugin */\r\na.smiley { padding: 5px; display: block; float: left; }\r\n.wp-smiley { background-image: url($q_smilies_src); background-repeat: no-repeat; vertical-align: text-top; padding: 0; border: none; height: {$q_smilies_size}px; width: {$q_smilies_size}px; }\r\n";
	foreach (array_unique($q_smilies_positions) as $smiley => $position) print ".wp-smiley.smiley-$position { background-position: " . ($position - 1) * $q_smilies_size * -1 . "px; }\r\n";
	print <<<HTML
</style>
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
</script>

HTML;
}

function q_smilies_meta_box() {
	global $q_smilies_set, $q_smilies_src, $q_smilies_size, $q_smilies_positions, $q_smilies_search, $q_smilies_replace;
	foreach (array_unique($q_smilies_positions) as $smiley => $position) {
		$alt = attribute_escape($smiley);
		print " <a class='smiley' href='#' onclick='q_smilies_insert(\" $alt \");'><img src='/wp-includes/images/blank.gif' alt='$alt' title='$alt' class='wp-smiley smiley-$position' /></a> ";
	}
	print '<div style="clear: both;"></div>';
}

function q_smilies_init() {
	global $q_smilies_set, $q_smilies_src, $q_smilies_size, $q_smilies_positions, $q_smilies_search, $q_smilies_replace;

	// don't bother setting up smilies if they are disabled
	if (!get_option('use_smilies')) return;

	switch ($q_smilies_set) {
		case 'silk':
			$q_smilies_src = 'silk.png';
			$q_smilies_size = 16;
			$q_smilies_positions = array(
				':evil:'	=> 1,
				'(6)'		=> 1,
				':D'		=> 2,
				':-D'		=> 2,
				':grin:'	=> 2,
				'8)'		=> 3,
				'8-)'		=> 3,
				':cool:'	=> 3,
				'^_^'		=> 3,
				'XD'		=> 3,
				'X-D'		=> 3,
				':lol:'		=> 3,
				':)'		=> 4,
				':-)'		=> 4,
				':smile:'	=> 4,
				':o'		=> 5,
				':-o'		=> 5,
				':shock:'	=> 5,
				':p'		=> 6,
				':-p'		=> 6,
				':tounge:'	=> 6,
				':('		=> 7,
				':-('		=> 7,
				':sad:'		=> 7,
				':s'		=> 8,
				':-s'		=> 8,
				';)'		=> 9,
				';-)'		=> 9,
				':wink:'	=> 9
			);
			break;
			
		default:
			$q_smilies_src = 'fugue.png';
			$q_smilies_size = 16;
			$q_smilies_positions = array(
				':)'		=> 1,
				':-)'		=> 1,
				':smile:'	=> 1,
				':s'		=> 2,
				':-s'		=> 2,
				'8)'		=> 3,
				'8-)'		=> 3,
				':cool:'	=> 3,
				':\'('		=> 4,
				':\'-('		=> 4,
				'8o'		=> 5,
				'8-o'		=> 5,
				':eek:'		=> 5,
				':evil:'	=> 6,
				'(6)'		=> 6,
				':fat:'		=> 7,
				':D'		=> 8,
				':-D'		=> 8,
				':grin:'	=> 8,
				'^_^'		=> 9,
				'XD'		=> 9,
				'X-D'		=> 9,
				':lol:'		=> 9,
				':@'		=> 10,
				':-@'		=> 10,
				':mad:'		=> 10,
				':angry:'	=> 10,
				':|'		=> 11,
				':-|'		=> 11,
				':paint:'	=> 12,
				':p'		=> 13,
				':-p'		=> 13,
				':tounge:'	=> 13,
				':$'		=> 14,
				':-$'		=> 14,
				':roll:'	=> 15,
				':('		=> 16,
				':-('		=> 16,
				':sad:'		=> 16,
				':slim:'	=> 17,
				':o'		=> 18,
				':-o'		=> 18,
				':shock:'	=> 18,
				'(666)'		=> 19,
				':evilgrin:'	=> 19,
				';)'		=> 20,
				';-)'		=> 20,
				':wink:'	=> 20,
				':yell:'	=> 21
			);
			break;
	}
	$q_smilies_src = WP_PLUGIN_URL . "/speedy-smilies/$q_smilies_src";

	foreach ($q_smilies_positions as $smiley => $position) {
		$alt = attribute_escape($smiley);
		$q_smilies_search[] = '/(\s|^)' . preg_quote( $smiley, '/' ) . '(\s|$)/';		
		$q_smilies_replace[] = " <img src='/wp-includes/images/blank.gif' alt='$alt' title='$alt' class='wp-smiley smiley-$position' /> ";
	}
}

function q_smilies_replace($text) {
	global $q_smilies_search, $q_smilies_replace;
	$output = '';
	// HTML loop taken from texturize function, could possible be consolidated
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
add_action('admin_print_styles', 'q_smilies_admin_styles');
add_filter('stylesheet_uri', 'q_smilies_stylesheet_uri');
add_filter('the_content', 'q_smilies_replace');
add_filter('the_excerpt', 'q_smilies_replace');
add_filter('comment_text', 'q_smilies_replace', 20);
