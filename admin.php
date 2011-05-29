<?php
/*

    Speedy Smilies
    Copyright 2011 Nick Venturella

    Speedy Smilies is free software licensed under the GNU GPL version 3.
    See the plugin's main file, speedy-smilies.php, for full details.

*/


// List the smilie sets
function q_smilies_list_sets() {
	$list = array();
	foreach (glob(plugin_dir_path(__FILE__) . "sets/*.php") as $set) {
		$toeval = 'return array( ' . str_replace(';', '', file_get_contents($set)) . ');';
		$set_array = eval($toeval);
		$list[basename($set, '.php')] = array('name' => $set_array['name'], 'authors' => $set_array['authors']);
	}
	return $list;
}

function q_smilies_authors($a) {
	$uris = array();
	foreach ($a as $name => $uri) { if($uri) $uris[] = "<a href=\"$uri\" target=\"_blank\">$name</a>"; else $uris[] = "$name"; }
	$last = array_pop($uris);
	if (count($uris) == 0) return $last;
	elseif (count($uris) == 1) return $uris[0] . ' and ' . $last;
	else return join(', ', $uris) . ' and ' . $last;
}


// Generate sample text
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
		"Konnichiwa"		=> " <em>and</em> you can greet friends in Japanese!",
		"Ahoy hoy"		=> " <em>and</em> you can greet sailors! In fact, this nautical greeting so infatuated inventor Alexander Graham Bell he suggested this be the proper way to answer the telephone; try it the next time someone gives you a call.",
		"Jambo"			=> " <em>and</em> you can greet friends in Swahili!",
		"Namaste"		=> " <em>and</em> you can greet friends in Hindi!",
		"Sawubona"		=> " <em>and</em> you can greet friends in Zulu!",
		"Hej"			=> " <em>and</em> you can greet friends in Swedish and Danish!",
		"Hei"			=> " <em>and</em> you can greet friends in Norwegian!",
		"Mingalarba"		=> " <em>and</em> you can greet friends in Burmese!",
		"Hola"			=> " <em>and</em> you can greet friends in Spanish!",
		"Privyet"		=> " <em>and</em> you can greet friends in Russian!",
		"Moi"			=> " <em>and</em> you can greet friends in Finnish!",
		"Annyong"		=> " <em>and</em> you can greet friends in Korean!",
		"Salve"			=> " <em>and</em> you can greet friends in Latin!",
		"Merhaba"		=> " <em>and</em> you can greet friends in Turkish!",
		"Bula"			=> " <em>and</em> you can greet friends in Fijian!"
	);
	$greeting = array_rand($greetings);

	return "<p>$greeting, $user_identity! :p In case you were wondering, :?: you&apos;re looking at some <em>fancy fresh</em> sample text. Oh my! :eek:</p><p>The sun broke quickly over the endless African savanna beginning another clear day. All was quiet save a marimba playing in the distance. :roll: James closed his eyes and began daydreaming. ;-) Suddenly, pieces of broken glass were flying through the air in all directions. :shock: With a thunderous crash, his Jeep bounded out of the underbrush. :lol: </p><p>&quot;Although it is always an adventure,&quot; :| James mused, &quot;this is the last time I let the monkey drive!&quot; :mad: He laced his boots, grabbed his gun, and ran out the door... :s</p><p>:arrow: Now you know how the smilies on your blog will appear{$greetings[$greeting]} :) What a lovely plugin this Speedy Smilies is! 8)</p>";
}


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
		$updatedhtml = '<div class="updated fade"><p><strong><img class="q_smilies_icon" src="' . plugin_dir_url(__FILE__) . 'icon.gif" /> Speedy Smilies</strong> settings saved. Smilies are disabled.</p></div>';
	} else {
		update_option('use_smilies', '1');
		q_smilies_init();
		q_smilies_rebuild(false);
		q_smilies_admin_styles();
		$a = $q_smilies_sets[ $_POST['speedy_smilies_set'] ];
		if ($a['authors']) $updatedhtml = '<div class="updated fade"><p><strong><img class="q_smilies_icon" src="' . plugin_dir_url(__FILE__) . 'icon.gif" /> Speedy Smilies</strong> settings saved. Please ensure your blog&apos;s footer or &quot;About&quot; page gives credit to ' . q_smilies_authors($a['authors']) . '.</p></div>';
		else $updatedhtml = '<div class="updated fade"><p><strong><img class="q_smilies_icon" src="' . plugin_dir_url(__FILE__) . 'icon.gif" /> Speedy Smilies</strong> settings saved.</p></div>';
	}
}


// Display admin form
$cachedate = date("j M Y \a\\t g:i a", get_option('speedy_smilies_cache'));
$cachesize = filesize( plugin_dir_path(__FILE__) . 'cache/' .  get_option('speedy_smilies_cache') . '.css' );
$disabled = !get_option('use_smilies');
$q_smilies_set = get_option('speedy_smilies_set');
if (!$q_smilies_set) $q_smilies_set = 'wordpress';

$formhtml = '<fieldset><legend>Enable/Disable Smilies</legend><label for="use_smilies"><input name="use_smilies" type="checkbox" id="use_smilies" value="1" onclick="q_smilies_input_disable(\'q_smilies_fieldset_set\', !this.checked);' . ($q_smilies_warninghtml ? '' : 'q_smilies_input_disable(\'q_smilies_fieldset_advanced\', !this.checked)') . '"' . (get_option('use_smilies') ? ' checked="checked"' : '') . '/> ' . _('Convert emoticons like <code>:-)</code> and <code>:-P</code> to graphics on display') . '</label></fieldset><fieldset id ="q_smilies_fieldset_set"><legend>Select Smilie Set</legend><div class="q_smilies_cc_div">These icons have been provided free of charge under a <a target="_blank" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 license</a> (or similar). Your web site must include a link back to the icon author&apos;s page.</div>';

foreach ($q_smilies_sets as $set => $a) {
	$formhtml .= '<input type="radio" name="speedy_smilies_set" value="' . $set . '"' . ($q_smilies_set == $set ? ' checked="checked"' : '') . ($disabled ? ' disabled="disabled"' : '') . ' /> <strong>' . $a['name'] . '</strong>' . ($a['authors'] ? ' by ' . q_smilies_authors($a['authors']) : '') . '<br /><img src="' . plugin_dir_url(__FILE__) . 'sets/' . $set . '.png" alt="' . $a['name'] . '" title="' . $a['name']. '" /><br /><br />';
}
$formhtml .= '</fieldset><fieldset id ="q_smilies_fieldset_advanced">
<legend>Advanced Settings</legend>
How should Speedy Smilies modify your blog&apos;s stylesheets? ' . $q_smilies_warninghtml .
'<div class="q_smilies_indent_div">
<label><input name="speedy_smilies_method" type="radio" value="fast"'. (get_option('speedy_smilies_method') === 'fast' ? ' checked="checked"' : '') . ($q_smilies_warninghtml || $disabled ? ' disabled="disabled"' : '') . ' /> Use the faster preferred method</label>
<div class="q_smilies_small_div">The theme and Speedy Smilies style rules will be combined into a single, minified CSS file.</div>
<label><input name="speedy_smilies_method" type="radio" value="slow"'. (get_option('speedy_smilies_method') !== 'fast' ? ' checked="checked"' : '') . ($q_smilies_warninghtml || $disabled ? ' disabled="disabled"' : '') . ' /> Use the slower compatibility method</label>
<div class="q_smilies_small_div">The Speedy Smilies style rules will be loaded from an independent CSS file. This partially defeats the purpose of this plugin, but may allow you to use Speedy Smilies with incompatible plugins or broken themes.</div></div>
<label><input name="speedy_smilies_datauri" type="checkbox" value="yes"'. (get_option('speedy_smilies_datauri') === 'yes' ? ' checked="checked"' : '') . ($disabled ? ' disabled="disabled"' : '') . ' /> Use inline image embedding? (<i>experimental</i>)</label>
<div class="q_smilies_small_div">The images will be included within the CSS itself using a <a href="http://en.wikipedia.org/wiki/Data_URI_scheme" target="_blank">data: URI</a>, which further increasing speed, but is not supported in old versions of Internet Explorer.</div>
<p>The stylesheet cache was last updated on <b>' . $cachedate . '</b> and is <b>' . number_format($cachesize) . ' bytes</b> in size. This cache updates automatically as needed, but you can also click &quot;Save Changes&quot; below to force an immediate update.</p></fieldset>
<p class="submit"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p>';

$sampletext = q_smilies_replace(q_smilies_sample_text());

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
$formhtml
</form>
</div>
HTML;
