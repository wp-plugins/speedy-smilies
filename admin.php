<?php
/*
Speedy Smilies
Copyright 2011 Nick Venturella

Speedy Smilies is free software licensed under the GNU GPL version 3.
See the plugin's main file, speedy-smilies.php, for full details.
*/

// Check for incompatible plugins
q_smilies_compatibility_check();

// Load smilie sets
$q_smilies_sets = q_smilies_list_sets();

// Process admin form
$updatedhtml = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$q_smilies_set = $_POST['speedy_smilies_set'];
	if ($q_smilies_set) update_option('speedy_smilies_set', $q_smilies_set);

	if ($_POST['speedy_smilies_method'] === 'fast' && !$q_smilies_warninghtml) update_option('speedy_smilies_method', 'fast');
	else update_option('speedy_smilies_method', 'slow');

	if ($_POST['speedy_smilies_datauri'] === 'yes') update_option('speedy_smilies_datauri', 'yes');
	else update_option('speedy_smilies_datauri', 'no');

	if (!$_POST['use_smilies']) {
		update_option('use_smilies', '0');
		q_smilies_init();
		$updatedhtml = '<div class="updated fade"><p><img class="q_smilies_icon" src="' . plugin_dir_url(__FILE__) . 'icon.gif" /> <strong>Speedy Smilies</strong> settings saved. Smilies are disabled.</p></div>';
	} else {
		update_option('use_smilies', '1');
		q_smilies_init();
		q_smilies_rebuild(false);
		q_smilies_admin_styles();
		$a = $q_smilies_sets[ $_POST['speedy_smilies_set'] ];
		if ($a['authors']) $updatedhtml = '<div class="updated fade"><p><img class="q_smilies_icon" src="' . plugin_dir_url(__FILE__) . 'icon.gif" /> <strong>Speedy Smilies</strong> settings saved. Please ensure your blog&apos;s footer or &quot;About&quot; page gives credit to ' . q_smilies_authors($a['authors']) . '.</p></div>';
		else $updatedhtml = '<div class="updated fade"><p><img class="q_smilies_icon" src="' . plugin_dir_url(__FILE__) . 'icon.gif" /> <strong>Speedy Smilies</strong> settings saved.</p></div>';
	}
}


// Display admin form
$cachedate = date("j M Y \a\\t g:i a", get_option('speedy_smilies_cache'));
$cachesize =  number_format(filesize( plugin_dir_path(__FILE__) . 'cache/' .  get_option('speedy_smilies_cache') . '.css' ));
$disabled = !get_option('use_smilies');
$q_smilies_set = get_option('speedy_smilies_set');
if (!$q_smilies_set) $q_smilies_set = 'wordpress';

// List each smiley set
foreach ($q_smilies_sets as $set => $a) {
	$label_this = "<strong>{$a['name']}:</strong> <span class='q_smilies_small'>{$a['width']}x{$a['height']} &mdash; "
		. number_format($a['bytes']) . ' bytes'
		. ($a['authors'] ? ' &mdash; by ' . q_smilies_authors($a['authors']) : '')
		. '</span>';
	$checked_this = ($q_smilies_set == $set ? 'checked="checked"' : '');
	$disabled_this = ($disabled ? 'disabled="disabled"' : '');
	$img_this = "<div style='height: {$a['height']}px; background: url(" . plugin_dir_url(__FILE__) . 'sets/' . $set . ".png) no-repeat;' title='{$a['name']}'></div>";
	
	$formhtml .= <<<HTML
<label>
	<input type="radio" name="speedy_smilies_set" value="$set" $checked_this $disasbled_this />
	$label_this<br />
	$img_this
</label><br />
HTML;
}


$sampletext = q_smilies_replace(q_smilies_sample_text());

$label_use_smilies = __('Convert emoticons like <code>:-)</code> and <code>:-P</code> to graphics on display');
$checked_use_smilies = (get_option('use_smilies') ? 'checked="checked" ' : '');
$onclick_use_smilies = 'q_smilies_input_disable(\'q_smilies_fieldset_set\', !this.checked);' . ($q_smilies_warninghtml ? '' : 'q_smilies_input_disable(\'q_smilies_fieldset_advanced\', !this.checked)');

$checked_method_fast = (get_option('speedy_smilies_method') === 'fast' ? 'checked="checked"' : '');
$checked_method_slow = (get_option('speedy_smilies_method') !== 'fast' ? ' checked="checked"' : '');
$disabled_method = ($q_smilies_warninghtml || $disabled ? 'disabled="disabled"' : '');

$checked_datauri = (get_option('speedy_smilies_datauri') === 'yes' ? 'checked="checked"' : '');
$disabled_datauri = ($disabled ? 'disabled="disabled"' : '');

print <<<HTML
<div class="wrap">
<div class="q_smilies_help">Questions? Bugs?<br />Click here for help</div>
<div id="icon-themes" class="icon32"><br /></div>
<h2>Speedy Smilies</h2>
$updatedhtml

<div class="q_smilies_sample">
	<div class="postbox">
		<div style="padding: 0 1.5em .5em;"><p style="font-size: 1.1em;"><strong>Lorem Ipsum</strong></p>$sampletext</div>
	</div>
</div>

<form class="q_smilies_form" method="post" action="{$_SERVER['REQUEST_URI']}">
<fieldset>
	<legend>Enable/Disable Smilies</legend>
	<label for="use_smilies">
		<input name="use_smilies" type="checkbox" id="use_smilies" value="1" onclick="$onclick_use_smilies" $checked_use_smilies />
		$label_use_smilies
	</label>
</fieldset>

<fieldset id="q_smilies_fieldset_set">
	<legend>Select Smilie Set</legend>
	<div class="q_smilies_cc_div">
		These icons are licensed under <a target="_blank" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0</a> (or similar).
		Your must include a link back to the icon author&apos;s page.
	</div>

$formhtml
</fieldset>

<fieldset id="q_smilies_fieldset_advanced">
	<legend>Advanced Settings</legend>
	How should Speedy Smilies modify your blog&apos;s stylesheets? $q_smilies_warninghtml
	<div class="q_smilies_indent_div">
		<label>
			<input name="speedy_smilies_method" type="radio" value="fast" $checked_method_fast $disabled_method />
			Use the faster preferred method
			<div class="q_smilies_small_div">
				The theme and Speedy Smilies style rules will be combined into a single, minified CSS file.
			</div>
		</label>
		
		<label>
			<input name="speedy_smilies_method" type="radio" value="slow" $checked_method_slow $disabled_method />
			Use the slower compatibility method
			<div class="q_smilies_small_div">
				The Speedy Smilies style rules will be loaded from an independent CSS file.
				This partially defeats the purpose of this plugin, but may allow you to use Speedy Smilies with incompatible plugins or broken themes.
			</div>
		</label>		
	</div>
	
	<label>
		<input name="speedy_smilies_datauri" type="checkbox" value="yes" $checked_datauri $disabled_datauri />
		Use inline image embedding? (<i>experimental</i>)
		<div class="q_smilies_small_div">
			The smiley images will be included within the CSS itself using a <a target="_blank" href="http://en.wikipedia.org/wiki/Data_URI_scheme">data: URI</a>.
			This further increases speed, but is not supported in old versions of Internet Explorer (see <a target="_blank" href="http://caniuse.com/datauri">browser compatibility table</a>).
		</div>
	</label>
	
	<p>
		The stylesheet cache was last updated on <b>$cachedate</b> and is <b>$cachesize bytes</b> in size.
		This cache updates automatically as needed, but you can also click &quot;Save Changes&quot; below to force an immediate update.
	</p>
</fieldset>

<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
</p>
</form>
</div>
HTML;
