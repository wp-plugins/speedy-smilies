=== Speedy Smilies ===
Contributors: quietmint
Donate link: http://quietmint.com/speedy-smilies/
Tags: smilies, smiles, emoticons, emotes, css, css sprite, css sprites, css image sprite, css image sprites, css minification, css compression, css optimization
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: trunk

Speeds up and beautifies your blog by substituting the individually-wrapped WordPress smilies with a single CSS image sprite containing all emoticons.

== Description ==
Speedy Smilies takes emoticons in WordPress to the next level (where it should be already and hopefully one day will). The end goal is to make smilies load faster in the browser for visitors and make them easy to insert into posts/pages for authors.

Speedy Smilies is free software licensed under the GNU GPL version 3.

== Installation ==

1. Extract `speedy-smilies.zip` and upload the resulting `speedy-smilies` directory to `wp-content/plugins/speedy-smilies`. The plugin directory must be named `speedy-smilies`.
1. Activate the plugin through the 'Plugins' section of WordPress.
1. Configure the plugin using the new 'Speedy Smilies' section under the 'Appearance' section of WordPress.

== Screenshots ==

1. Here we're looking at the control panel settings page for the plugin where you can select a smilie set.
2. An editing pane allows you to insert smilies into posts and pages simply by clicking on them.

== Changelog ==

= 0.6 =
* Bug fix: For increased security, style.php will not execute when the plugin is disabled.

= 0.5 =
* Bug fix: The plugin now makes use of the include_url() function instead and will now work properly if you've installed your blog into a subdirectory (e.g., http://yourdomain.com/blog/).

= 0.4 =
* Compatible with WordPress 2.8.4.
* Realized that style.php is executing on EVERY page load and wasn't cacheable by browsers. Oops. For the time being, we now cache style.php for 10 minutes.

= 0.3 =
* Added a basic check for incompatible plugins.
* CSS is now minified as it passes through the Speedy Smilies plugin to yield faster download and rendering times for visitors.
* Improved the plugin's control panel settings page and added a sample text box to preview smilies (see screenshot).
* Added two new smilie sets (WordPress Default, in case anyone actually likes the smilies that ship with WordPress, and Moskis).

= 0.2 =
* First public release!
