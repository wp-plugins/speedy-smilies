<?php

// This should be automated...
$q_smilies_sets = array(
	'fugue'	=> array(name => 'Fugue', author => 'Yusuke Kamiyamane', authoruri => 'http://pinvoke.com/'),
	'silk'	=> array(name => 'Silk', author => 'Mark James', authoruri => 'http://www.famfamfam.com/lab/icons/silk/')
);


// Process form
$updatedhtml = '';
if ($_POST['speedy_smilies_set']) {
	update_option('speedy_smilies_set', $_POST['speedy_smilies_set']);
	$a = $q_smilies_sets[$_POST['speedy_smilies_set']];
	$updatedhtml = '<div class="updated"><p><strong>Settings saved. Please update your blog&apos;s footer or &quot;About&quot; page to give credit to <a target="_blank" href="' . $a['authoruri'] . '">' . $a['author'] . '</a>.</strong></p></div>';
}


// Display form
$q_smilies_set = get_option('speedy_smilies_set');
if (!$q_smilies_set) $q_smilies_set = 'fugue';

$formhtml = '';
foreach ($q_smilies_sets as $set => $a) {
	$formhtml .= '<input type="radio" name="speedy_smilies_set" value="' . $set . '"' . ($q_smilies_set == $set ? ' checked="checked"' : '') . ' /> <strong>' . $a['name'] . '</strong> by <a target="_blank" href="' . $a['authoruri'] . '">' . $a['author'] . '</a><br />';
	$formhtml .= '<img src="' . WP_PLUGIN_URL . '/speedy-smilies/' . $set . '.png" alt="' . $a['name'] . '" title="' . $a['name']. '" /><br /><br />';
}
$formhtml .= '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p>';

print <<<HTML
<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h2>Speedy Smilies</h2><br />
$updatedhtml
<p>These icons have been provided free of charge under a <a target="_blank" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 license</a>.<br />
Your blog must give credit to the respective graphic designer and include a link back to the icon author's page.</p>
<form method="post" action="{$_SERVER['REQUEST_URI']}">
$formhtml
</form>
</div>
HTML;
