<?php

require_once('init.php');

// Check for incompatible plugins
$warninghtml = q_smilies_compatibility_check();

// Load smilie sets
$q_smilies_sets = q_smilies_list_sets();

// Process admin form
$updatedhtml = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$q_smilies_set = $_POST['speedy_smilies_set'];
	if ($q_smilies_set) update_option('speedy_smilies_set', $q_smilies_set);
	if (!$_POST['use_smilies']) {
		update_option('use_smilies', '0');
		q_smilies_init();
		$updatedhtml = '<div class="updated"><p><strong>Settings saved. Smilies are disabled.</strong></p></div>';
	} else {
		update_option('use_smilies', '1');
		q_smilies_init();
		q_smilies_admin_styles();
		$a = $q_smilies_sets[$_POST['speedy_smilies_set']];
		if ($a['author']) $updatedhtml = '<div class="updated"><p><strong>Settings saved. Please update your blog&apos;s footer or &quot;About&quot; page to give credit to <a target="_blank" href="' . $a['authoruri'] . '">' . $a['author'] . '</a>.</strong></p></div>';
		else $updatedhtml = '<div class="updated"><p><strong>Settings saved.</strong></p></div>';
	}
}


// Display admin form
$disabled = !get_option('use_smilies');
$q_smilies_set = get_option('speedy_smilies_set');
if (!$q_smilies_set) $q_smilies_set = 'wordpress';

$formhtml = '<fieldset><legend>Enable/Disable Smilies</legend><label for="use_smilies"><input name="use_smilies" type="checkbox" id="use_smilies" value="1" onclick="q_smilies_input_disable(\'q_smilies_fieldset\', !this.checked)"' . (get_option('use_smilies') ? ' checked="checked"' : '') . '/> ' . _('Convert emoticons like <code>:-)</code> and <code>:-P</code> to graphics on display') . '</label><br /></fieldset><fieldset id ="q_smilies_fieldset"><legend>Select Smilies</legend>';

foreach ($q_smilies_sets as $set => $a) {
	if ($a['author']) $formhtml .= '<input type="radio" name="speedy_smilies_set" value="' . $set . '"' . ($q_smilies_set == $set ? ' checked="checked"' : '') . ($disabled ? ' disabled="disabled"' : '') . ' /> <strong>' . $a['name'] . '</strong> by <a target="_blank" href="' . $a['authoruri'] . '">' . $a['author'] . '</a><br />';
	else $formhtml .= '<input type="radio" name="speedy_smilies_set" value="' . $set . '"' . ($q_smilies_set == $set ? ' checked="checked"' : '') . ($disabled ? ' disabled="disabled"' : '') . ' /> <strong>' . $a['name'] . '</strong><br />';

	$formhtml .= '<img src="' . WP_PLUGIN_URL . '/speedy-smilies/' . $set . '.png" alt="' . $a['name'] . '" title="' . $a['name']. '" /><br /><br />';
}
$formhtml .= '</fieldset><p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p>';

$sampletext = q_smilies_replace(q_smilies_sample_text());

print <<<HTML
<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h2>Speedy Smilies</h2>
$warninghtml
$updatedhtml
<p>These icons have been provided free of charge under a <a target="_blank" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 license</a> (or similar).<br />Your blog must give credit to the respective graphic designer and include a link back to the icon author's web page.</p>

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
