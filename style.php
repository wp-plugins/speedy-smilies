<?php

require('../../../wp-blog-header.php'); 

$css = file_get_contents(get_stylesheet_directory() . "/style.css");
$directory = get_stylesheet_directory_uri();
$css = preg_replace('!url\(\s?\'?"?(.+?)\'?"?\s?\)!', "url(\\1)", $css);
$css = preg_replace('!url\(([^/].+?)\)!', "url($directory/\\1)", $css);

header("Content-Type: text/css");
print "$css\r\n\r\n/* Added by Speedy Smilies plugin */\r\n.wp-smiley { background-image: url($q_smilies_src); background-repeat: no-repeat; vertical-align: text-top; padding: 0; border: none; height: {$q_smilies_size}px; width: {$q_smilies_size}px; }\r\n";
foreach (array_unique($q_smilies_positions) as $smiley => $position) print ".wp-smiley.smiley-$position { background-position: " . ($position - 1) * $q_smilies_size * -1 . "px; }\r\n";
