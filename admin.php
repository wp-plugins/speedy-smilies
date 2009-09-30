<?php
/*

    Speedy Smilies
    Copyright 2009 Nick Venturella

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

	if (!$_POST['use_smilies']) {
		update_option('use_smilies', '0');
		q_smilies_init();
		$updatedhtml = '<div class="updated"><p><strong>Settings saved. Smilies are disabled.</strong></p></div>';
	} else {
		update_option('use_smilies', '1');
		q_smilies_init();
		q_smilies_rebuild();
		q_smilies_admin_styles();
		$a = $q_smilies_sets[$_POST['speedy_smilies_set']];
		if ($a['author']) $updatedhtml = '<div class="updated"><p><strong>Settings saved. Please ensure your blog&apos;s footer or &quot;About&quot; page gives credit to <a target="_blank" href="' . $a['authoruri'] . '">' . $a['author'] . '</a>.</strong></p></div>';
		else $updatedhtml = '<div class="updated"><p><strong>Settings saved.</strong></p></div>';
	}
}


// Display admin form
$cachedate = date("j F Y \a\\t g:i:s a", get_option('speedy_smilies_cache'));
$disabled = !get_option('use_smilies');
$q_smilies_set = get_option('speedy_smilies_set');
if (!$q_smilies_set) $q_smilies_set = 'wordpress';

$formhtml = '<fieldset><legend>Enable/Disable Smilies</legend><label for="use_smilies"><input name="use_smilies" type="checkbox" id="use_smilies" value="1" onclick="q_smilies_input_disable(\'q_smilies_fieldset_set\', !this.checked);' . ($q_smilies_warninghtml ? '' : 'q_smilies_input_disable(\'q_smilies_fieldset_advanced\', !this.checked)') . '"' . (get_option('use_smilies') ? ' checked="checked"' : '') . '/> ' . _('Convert emoticons like <code>:-)</code> and <code>:-P</code> to graphics on display') . '</label></fieldset><fieldset id ="q_smilies_fieldset_set"><legend>Select Smilie Set</legend><div class="q_smilies_cc_div">These icons have been provided free of charge under a <a target="_blank" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 license</a> (or similar). Your web site must include a link back to the icon author&apos;s page.</div>';

foreach ($q_smilies_sets as $set => $a) {
	if ($a['author']) $formhtml .= '<input type="radio" name="speedy_smilies_set" value="' . $set . '"' . ($q_smilies_set == $set ? ' checked="checked"' : '') . ($disabled ? ' disabled="disabled"' : '') . ' /> <strong>' . $a['name'] . '</strong> by <a target="_blank" href="' . $a['authoruri'] . '">' . $a['author'] . '</a><br />';
	else $formhtml .= '<input type="radio" name="speedy_smilies_set" value="' . $set . '"' . ($q_smilies_set == $set ? ' checked="checked"' : '') . ($disabled ? ' disabled="disabled"' : '') . ' /> <strong>' . $a['name'] . '</strong><br />';

	$formhtml .= '<img src="' . WP_PLUGIN_URL . '/speedy-smilies/' . $set . '.png" alt="' . $a['name'] . '" title="' . $a['name']. '" /><br /><br />';
}
$formhtml .= '</fieldset><fieldset id ="q_smilies_fieldset_advanced"><legend>Advanced Settings</legend>How should Speedy Smilies modify your blog&apos;s stylesheets? ' . $q_smilies_warninghtml . '<div class="q_smilies_indent_div"><label><input name="speedy_smilies_method" type="radio" value="fast"'. (get_option('speedy_smilies_method') === 'fast' ? ' checked="checked"' : '') . ($q_smilies_warninghtml || $disabled ? ' disabled="disabled"' : '') . ' /> Use the faster preferred method</label><div class="q_smilies_small_div">The theme and Speedy Smilies style rules will be combined into a single, minified CSS file.</div><label><input name="speedy_smilies_method" type="radio" value="slow"'. (get_option('speedy_smilies_method') !== 'fast' ? ' checked="checked"' : '') . ($q_smilies_warninghtml || $disabled ? ' disabled="disabled"' : '') . ' /> Use the slower compatibility method</label><div class="q_smilies_small_div">The Speedy Smilies style rules will be loaded from an independent CSS file. This partially defeats the purpose of this plugin, but may allow you to use Speedy Smilies with incompatible plugins or broken themes.</div></div><p>The stylesheet cache was last updated on <b>' . $cachedate . '</b>. This cache updates automatically when you change themes or when you click &quot;Save Changes&quot; below.</p></fieldset><p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p>';

$sampletext = q_smilies_replace(q_smilies_sample_text());

print <<<HTML
<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h2>Speedy Smilies</h2>
$updatedhtml

<div class="q_smilies_sample">
	<div class="postbox">
		<div style="padding: 0 1.5em .5em;"><p style="font-size: 1.1em;"><strong>Lorem Ipsum</strong></p>$sampletext</div>
	</div>
</div>

<form class="q_smilies_form" method="post" action="{$_SERVER['REQUEST_URI']}">
$formhtml
</form>
</div>
HTML;
