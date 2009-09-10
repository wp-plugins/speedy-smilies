<?php
/*

    Speedy Smilies
    Copyright 2009 Nick Venturella

    Speedy Smilies is free software licensed under the GNU GPL version 3.
    See the plugin's main file, speedy-smilies.php, for full details.

*/


// Load WordPress base
require('../../../wp-blog-header.php'); 
$cssfile = get_stylesheet_directory() . "/style.css";
$css = file_get_contents($cssfile);
$directory = get_stylesheet_directory_uri();

// Rewrite relative URLs
$css = preg_replace('!url\(\s?\'?"?(.+?)\'?"?\s?\)!', "url(\\1)", $css);
$css = preg_replace('!url\(([^/].+?)\)!', "url($directory/\\1)", $css);

// Add Speedy Smilies CSS
$css .= ".wp-smiley { background-image: url($q_smilies_src); background-repeat: no-repeat; vertical-align: text-top; padding: 0; border: none; height: {$q_smilies_height}px; width: {$q_smilies_width}px }";
foreach (array_unique($q_smilies_positions) as $smiley => $position) $css .= ".wp-smiley.smiley-$position { background-position: " . ($position - 1) * $q_smilies_width * -1 . "px }";

// Compress CSS
$css = preg_replace('!/\*.+?\*/!s', "", $css);
$css = preg_replace('!\s*([;:{},])\s*!', "$1", $css);
$css = preg_replace('!;}!', "}", $css);
$css = preg_replace('![\r\n]!', "", $css);
$css = preg_replace('!\s+!', " ", $css);
$css = preg_replace('!([: ])0(px|em|%)!', "\${1}0", $css);
$css = preg_replace('!([0-9]+(?:\.[0-9]*)?+(?:%|in|cm|mm|em|ex|pt|pc|px)?) \1 \1 \1!iU', "$1", $css);
$css = preg_replace('!([0-9]+(?:\.[0-9]*)?+(?:%|in|cm|mm|em|ex|pt|pc|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|in|cm|mm|em|ex|pt|pc|px)?) \1 \2!iU', "$1 $2", $css);
$css = preg_replace('!([0-9]+(?:\.[0-9]*)?+(?:%|in|cm|mm|em|ex|pt|pc|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|in|cm|mm|em|ex|pt|pc|px)?) ([0-9]+(?:\.[0-9]*)?(?:%|in|cm|mm|em|ex|pt|pc|px)?) \2!iU', "$1 $2 $3", $css);

// Output
// To do: Better caching! The style.php file will run on *every* page load. Yikes!
// Right now, we just cache everything for 10 minutes.
header("Expires: " . date(DATE_RFC1123, time() + 600));
header("Cache-Control: max-age=600");
header("Last-Modified: " . date(DATE_RFC1123, time()));
header("Content-Type: text/css");
print $css;