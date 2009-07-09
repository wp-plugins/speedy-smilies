<?php

// This should be automated...
$q_smilies_sets = array('fugue', 'silk');


// Process form
$updatedhtml = '';
if ($_POST['speedy_smilies_set']) {
	update_option('speedy_smilies_set', $_POST['speedy_smilies_set']);
	$updatedhtml = '<div class="updated"><p><strong>Settings saved.</strong></p></div>';
}


// Display form
$q_smilies_set = get_option('speedy_smilies_set');
if (!$q_smilies_set) $q_smilies_set = 'fugue';

$formhtml = '';
foreach ($q_smilies_sets as $set) {
	$formhtml .= '<input type="radio" name="speedy_smilies_set" value="' . $set . '"' . ($q_smilies_set == $set ? ' checked="checked"' : '') . ' /> <b>' . $set . '</b><br />';
	$formhtml .= '<img src="' . WP_PLUGIN_URL . '/speedy-smilies/' . $set . '.png" alt="' . $set . '" title="' . $set . '" /><br /><br />';
}
$formhtml .= '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p>';

print <<<HTML
<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h2>Speedy Smilies</h2><br />
$updatedhtml
<form method="post" action="{$_SERVER['REQUEST_URI']}">
$formhtml
</form>
</div>
HTML;
