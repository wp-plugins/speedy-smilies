<?php
/*
Speedy Smilies
Copyright 2011 Nick Venturella

Speedy Smilies is free software licensed under the GNU GPL version 3.
See the plugin's main file, speedy-smilies.php, for full details.
*/


/**
 * One-time: Plugin installation from scratch when the plugin is activated by the user.
 * Called by register_activation_hook().
 */
function q_smilies_activate() {
	add_option('speedy_smilies_set', 'wordpress', '', 'yes');
	add_option('speedy_smilies_method', '', '', 'yes');
	add_option('speedy_smilies_cache', '', '', 'yes');
	add_option('speedy_smilies_themecache', '', '', 'no');
	add_option('speedy_smilies_donotify', '', '', 'no');
	add_option('speedy_smilies_datauri', '', '', 'no');
}


/**
 * Per-page: Smiley initialization.
 * Called by add_action('init').
 */
function q_smilies_init() {
	global $q_smilies_set, $q_smilies_src, $q_smilies_width, $q_smilies_height, $q_smilies_positions, $q_smilies_search, $q_smilies_replace;
	$q_smilies_src = ''; $q_smilies_width = 0; $q_smilies_height= 0; $q_smilies_positions = array(); $q_smilies_search = array(); $q_smilies_replace = array();

	// Don't bother setting up smilies if they are disabled
	if (!get_option('use_smilies')) return;

	$set_array = json_decode(file_get_contents( plugin_dir_path(__FILE__) . "sets/$q_smilies_set.json" ), true);
	$q_smilies_width = $set_array['width'];
	$q_smilies_height = $set_array['height'];
	$q_smilies_positions = $set_array['map'];
	$q_smilies_src = plugin_dir_url(__FILE__) . "sets/$q_smilies_set.png";

	foreach ($q_smilies_positions as $smiley => $position) {
		$alt = attribute_escape($smiley);
		$q_smilies_search[] = '/(\s|^)' . preg_quote( $smiley, '/' ) . '(\s|$)/';
		$q_smilies_replace[] = " <img src='" . includes_url() . "images/blank.gif' alt='$alt' class='wp-smiley smiley-$position' /> ";
	}

	// Rebuild the CSS if it does not exist
	$cache = get_option('speedy_smilies_cache');
	if (!$cache) q_smilies_rebuild();
	else {
		$cssfile = plugin_dir_path(__FILE__) . 'cache/' . $cache . '.css';
		if (!is_file($cssfile)) q_smilies_rebuild();
	}
}


/**
 * Admin: Runs afrter the basic admin panel menu structure is place. Adds Speedy Smilies options to the menu.
 * Called by add_action('admin_menu').
 */
function q_smilies_admin_menu() {
	add_theme_page('Speedy Smilies', 'Speedy Smilies', 8, 'speedy-smilies/admin.php');
	add_contextual_help('speedy-smilies/admin', '<p>Speedy Smilies lets you easily change the appearance of the smilies (also called emoticons) that are displayed on your WordPress site.</p><p>Please visit the <a href="http://wordpress.org/tags/speedy-smilies" target="_blank">WordPress.org Forums for Speedy Smilies</a> to ask questions, leave comments, or report bugs.');
}


/**
 * Admin: Runs when the "edit post" page loads.
 * Called by add_action('add_meta_boxes').
 *
 * @uses WordPress 3.0+
 */
function q_smilies_admin_meta() {
	global $q_smilies_src;
	if(empty($q_smilies_src)) return;
	add_meta_box('q_smilies_sectionid', __( 'Speedy Smilies', 'q_smilies_textdomain' ), 'q_smilies_meta_box', 'post', 'side', 'high');
	add_meta_box('q_smilies_sectionid', __( 'Speedy Smilies', 'q_smilies_textdomain' ), 'q_smilies_meta_box', 'page', 'side', 'high');
}


/**
 * Admin: Output Speedy Smilies chooser on the "edit post" page.
 * Called by q_smilies_admin_meta().
 */
function q_smilies_meta_box() {
	global $q_smilies_positions;
	if(empty($q_smilies_positions)) return;

	// array_flip: "If a value has several occurrences, the latest key will be used as its values, and all others will be lost."
	// This means the WordPress default for each smiley should be the LAST key defined in the map.
	$flip = array_flip($q_smilies_positions);

	foreach ($flip as $position => $smiley) {
		$alt = attribute_escape($smiley);
		print " <a class='smiley' href='#' onclick='q_smilies_insert(\" $alt \");'><img src='" . includes_url() . "images/blank.gif' alt='$alt' class='wp-smiley smiley-$position' /></a> ";
	}
	print '<div style="clear: both;"></div>';
}


/**
 * Admin: Add JavaScript and CSS to the edit pages.
 * Called by add_action('admin_print_styles').
 */
function q_smilies_admin_styles() {
	// Rebuild the CSS if it is outdated
	$cssfile = get_stylesheet_directory() . '/style.css';
	$cssstat = stat($cssfile);
	if (get_option('speedy_smilies_themecache') !== $cssstat['size'] . '-' . $cssstat['mtime'] . '-' . $cssstat['ino']) q_smilies_rebuild();
	unset($cssstat);

	$pluginurl = plugin_dir_url(__FILE__);
	$css = <<<CSS
.q_smilies_form fieldset {
	border: 1px solid #DFDFDF;
	margin-bottom: 1.5em;
	padding: .5em 2em;
	border-radius: 3px;
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
	position: absolute;
	right: 58px;
	margin: 24px 0 0;
	padding-right: 18px;
	font-size: 10px;
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
<script type='text/javascript' src='{$pluginurl}admin.js'></script>
<style type='text/css'>$css</style>
HTML;
	q_smilies_stylesheet_head();
	print "<!-- End Speedy Smilies plugin admin -->\r\n";
}


/**
 * Admin: Notify the user when the CSS files are rebuilt.
 * Called by add_action('admin_notices').
 */
function q_smilies_rebuild_notify() {
	$cachedate = date("j M Y \a\\t g:i a", get_option('speedy_smilies_cache'));
	print '<div class="updated fade"><p><img class="q_smilies_icon" src="' . plugin_dir_url(__FILE__) . 'icon.gif" /> <strong>Speedy Smilies</strong> detected a change in your blog and regenerated the stylesheet cache on ' . $cachedate . '</p></div>';
	update_option('speedy_smilies_donotify', '');
}


/**
 * Per-page: Outputs a link to the standalone (smilies only) CSS file.
 * Called by add_filter('wp_head') when method = slow, q_smilies_admin_styles().
 */
function q_smilies_stylesheet_head() {
	print '<link rel="stylesheet" type="text/css" href="' . plugin_dir_url(__FILE__) . 'cache/' . get_option('speedy_smilies_cache') . '_standalone.css" />' . "\r\n";
}


/**
 * Per-page: Return the Speedy Smilies CSS URL.
 * Called by add_filter('stylesheet_uri') when method = fast.
 *
 * @return The URL to the Speedy Smilies CSS file.
 */
function q_smilies_stylesheet_uri() {
	return plugin_dir_url(__FILE__) . 'cache/' . get_option('speedy_smilies_cache') . '.css';
}


/**
 * Check for incompatible plugins.
 * Called when get_option('speedy_smilies_method') is undefined.
 */
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


/**
 * Per-page: Replace text smilies with HTML <img> tags.
 * Called by add_filter('the_content'), add_filter('the_excerpt'), add_filter('comment_text').
 *
 * @param string $text The text
 * @return string The text with smilies converted to HTML <img> tags.
 */
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


/**
 * Rebuild the CSS files.
 * Called by add_action('switch_theme'), q_smilies_init(),
 *
 * @param boolean $donotify Whether to display a notification in the admin control panel.
 */
function q_smilies_rebuild($donotify = true) {
	global $q_smilies_set, $q_smilies_src, $q_smilies_width, $q_smilies_height, $q_smilies_positions;

	// Save the stat info for the theme's CSS to auto-detect changes later
	$cssfile = get_stylesheet_directory() . '/style.css';
	$cssstat = stat($cssfile);
	update_option('speedy_smilies_themecache', $cssstat['size'] . '-' . $cssstat['mtime'] . '-' . $cssstat['ino']);
	unset($cssstat);

	// Load the theme's CSS
	$css = file_get_contents($cssfile);
	$base_url = get_stylesheet_directory_uri();
	$base_path = get_stylesheet_directory();

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
	$css = q_smilies_css_optimize($css, $base_url, $base_path);
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


/**
 * Optimize CSS by removing comments and whitespace.
 * Called by q_smilies_rebuild(), q_smilies_admin_styles().
 *
 * @param string $css The CSS to optimize.
 * @param string $base_url The base URL for relative links (or null to leave relative links unchanged).
 * @param string $base_path The file path represented by the base URL.
 * @return string The optimized CSS.
 */
function q_smilies_css_optimize($css, $base_url = null, $base_path = null) {
	// Delete comments
	$css = preg_replace('!/\*.*?\*/!s', "", $css);

	// Delete unnecessary spaces
	$css = preg_replace('!\s*([;:{},])\s*!', "$1", $css);
	$css = preg_replace('!\s+!', " ", $css);
	
	// Delete unnecessay quotes for url()s
	$css = preg_replace('!url\(["\']?(.*?)["\']?\)!i', "url($1)", $css);

	// Delete trailing semicolons
	$css = preg_replace('!;}!', "}", $css);
	
	// Fix broken stylesheets. The last character of a selector must be one of: a-z 0-9 * ) ]
	$css = preg_replace('![^a-z0-9*)\]]+{!i', "{", $css);

	// Delete unnecessary measurements
	$css = preg_replace('!([: ])0(%|cm|em|ex|in|mm|pc|pt|px)!i', "\${1}0", $css);
	$css = preg_replace('!:([0-9]+(?:\.[0-9]*)?+(?:%|cm|em|ex|in|mm|pc|pt|px)?) \1 \1 \1!iU', ":$1", $css);
	$css = preg_replace('!:([0-9]+(?:\.[0-9]*)?+(?:%|cm|em|ex|in|mm|pc|pt|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|cm|em|ex|in|mm|pc|pt|px)?) \1 \2!iU', ":$1 $2", $css);
	$css = preg_replace('!:([0-9]+(?:\.[0-9]*)?+(?:%|cm|em|ex|in|mm|pc|pt|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|cm|em|ex|in|mm|pc|pt|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|cm|em|ex|in|mm|pc|pt|px)?) \2!iU', ":$1 $2 $3", $css);
	
	if($base_url) {
		// Recursively inline relative @import statements
		if (preg_match_all('!@import (.*?);!i', $css, $imports)) {
			foreach($imports[1] as $i) {
				preg_match('!^(?:"|\'|url\()(.*)(?:"|\'|\))(.*)$!i', $i, $matches);
				
				// Get the @import's URL and base directory
				$url = $matches[1];

				// Skip @import URLs that are absolute
				if (preg_match('!^(/|data:|https?:)!i', $url)) continue;

				// Determine the base URL and base path of the @import.
				$import_base_url = dirname("$base_url/$url");
				$import_base_path = dirname(realpath("$base_path/$url"));
				
				// Get the @import's media selector
				$media = preg_replace('!\s*([;:{},])\s*!', "$1", $matches[2]);
				$media = trim(preg_replace('!\s+!', " ", $media));
				
				// Read the @import file and optimize it
				$css_import = '';
				if($import_base_path) $css_import = @file_get_contents("$base_path/$url");
				if($css_import) {
					$css_import = q_smilies_css_optimize($css_import, $import_base_url, $import_base_path);
					if ($media) $css_import = "@media $media{{$css_import}}";
				}

				// Replace the @import statement with the optimized CSS
				$css = preg_replace('!@import ' . preg_quote($i, '!') . ';!i', $css_import, $css, 1);
			}
		}
		
		// Move remaining [absolute] @import statements to the beginning
		$css = preg_replace('!(.+)(@import .*?;)!is', "$2$1", $css);
		
		// Rewrite relative URLs
		$css = preg_replace('~url\((?!/|data:|https?:)(.*?)\)~i', "url($base_url/$1)", $css);
	}
	
	return trim($css);
}


/**
 * Admin: List all the smiley sets.
 *
 * @return array List of smiley sets.
 */
function q_smilies_list_sets() {
	$list = array();
	foreach (glob(plugin_dir_path(__FILE__) . "sets/*.json") as $set) {
		$basename = str_replace('.json', '', $set);
		$setname = basename($set, '.json');
		$set_array = json_decode(file_get_contents($set), true);
		$list[$setname] = array(
			'name'		=> $set_array['name'],
			'authors'	=> $set_array['authors'],
			'width'		=> floor($set_array['width']),
			'height'	=> floor($set_array['height']),
			'bytes'		=> filesize("$basename.png"),
		);
	}
	return $list;
}


/**
 * Admin: Convert an array of authors to HTML tags.
 *
 * @param array $a The author array.
 * @return string The author HTML.
 */
function q_smilies_authors($a) {
	$uris = array();
	foreach ($a as $name => $uri) { if($uri) $uris[] = "<a href=\"$uri\" target=\"_blank\">$name</a>"; else $uris[] = "$name"; }
	$last = array_pop($uris);
	if (count($uris) == 0) return $last;
	elseif (count($uris) == 1) return $uris[0] . ' and ' . $last;
	else return join(', ', $uris) . ' and ' . $last;
}


/**
 * Admin: Generate random sample text.
 *
 * @return string The sample text
 */
function q_smilies_sample_text() {
	global $q_smilies_set, $user_identity;
	$greetings = array(
		"Hi"			=> ".",
		"Hello"			=> ".",
		"Salut"			=> " <em>and</em> you can greet friends in French!",
		"Ciao"			=> " <em>and</em> you can greet friends in Italian!",
		"Aloha"			=> " <em>and</em> you can greet friends in Hawaiian!",
		"G'day"			=> " <em>and</em> you can greet the Aussies!",
		"Ni hao" 		=> " <em>and</em> you can greet friends in Chinese!",
		"Konnichiwa"	=> " <em>and</em> you can greet friends in Japanese!",
		"Ahoy hoy"		=> " <em>and</em> you can greet sailors! In fact, this nautical greeting so infatuated inventor Alexander Graham Bell he suggested this be the proper way to answer the telephone; try it the next time someone gives you a call.",
		"Jambo"			=> " <em>and</em> you can greet friends in Swahili!",
		"Namaste"		=> " <em>and</em> you can greet friends in Hindi!",
		"Sawubona"		=> " <em>and</em> you can greet friends in Zulu!",
		"Hej"			=> " <em>and</em> you can greet friends in Swedish and Danish!",
		"Hei"			=> " <em>and</em> you can greet friends in Norwegian!",
		"Mingalarba"	=> " <em>and</em> you can greet friends in Burmese!",
		"Hola"			=> " <em>and</em> you can greet friends in Spanish!",
		"Privyet"		=> " <em>and</em> you can greet friends in Russian!",
		"Moi"			=> " <em>and</em> you can greet friends in Finnish!",
		"Annyong"		=> " <em>and</em> you can greet friends in Korean!",
		"Salve"			=> " <em>and</em> you can greet friends in Latin!",
		"Merhaba"		=> " <em>and</em> you can greet friends in Turkish!",
		"Bula"			=> " <em>and</em> you can greet friends in Fijian!"
		);
		$greeting = array_rand($greetings);

		return "<p>$greeting, $user_identity! :p In case you were wondering, :?: you&apos;re looking at some <em>fancy fresh</em> sample text. Oh my! :eek:</p><p>The sun broke quickly over the endless African savanna beginning another clear day. 8) All was quiet save a marimba playing in the distance. :roll: James closed his eyes and began daydreaming. ;-) Suddenly, pieces of broken glass were flying through the air in all directions. :shock: With a thunderous crash, his Jeep bounded out of the underbrush. :lol: </p><p>&quot;Although it is always an adventure,&quot; :| James mused, &quot;this is the last time I let the monkey drive!&quot; :mad: He laced his boots, grabbed his gun, and ran out the door... :s</p><p>:arrow: Now you know how the smilies on your blog will appear{$greetings[$greeting]} :) What a lovely plugin this Speedy Smilies is! <3</p>";
}
