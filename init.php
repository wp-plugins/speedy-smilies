<?php
/*

    Speedy Smilies
    Copyright 2009 Nick Venturella

    Speedy Smilies is free software licensed under the GNU GPL version 3.
    See the plugin's main file, speedy-smilies.php, for full details.

*/


// Check for incompatible plugins
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
	return $q_smilies_warninghtml;
}


// List the smilie sets. This should be automated, but for now we are just hard coding the different sets
function q_smilies_list_sets() {
	return array(
		'wordpress'	=> array(name => 'WordPress Default'),
		'moskis'	=> array(name => 'Moskis', author => 'Jos&eacute; Rafael Pardilla', authoruri => 'http://blog.moskis.net/downloads/moskis-smiley-pack/'),
		'silk'		=> array(name => 'Silk', author => 'Mark James', authoruri => 'http://www.famfamfam.com/lab/icons/silk/'),
		'fugue'		=> array(name => 'Fugue', author => 'Yusuke Kamiyamane', authoruri => 'http://pinvoke.com/')
	);
}


// Get the specific smilie set
function q_smilies_load_set() {
	global $q_smilies_set, $q_smilies_src, $q_smilies_width, $q_smilies_height, $q_smilies_positions;
	switch ($q_smilies_set) {
		case 'silk':
			$q_smilies_width = 16;
			$q_smilies_height = 16;
			$q_smilies_positions = array(
				':evil:'	=> 1,
				'(6)'		=> 1,
				':D'		=> 2,
				':-D'		=> 2,
				':grin:'	=> 2,
				'8)'		=> 3,
				'8-)'		=> 3,
				':cool:'	=> 3,
				'^_^'		=> 3,
				'XD'		=> 3,
				'xD'		=> 3,
				'X-D'		=> 3,
				'x-D'		=> 3,
				':lol:'		=> 3,
				':)'		=> 4,
				':-)'		=> 4,
				':smile:'	=> 4,
				':o'		=> 5,
				':O'		=> 5,
				':-o'		=> 5,
				':-O'		=> 5,
				':eek:'		=> 5,
				'8O'		=> 5,
				'8o'		=> 5,
				'8-O'		=> 5,
				'8-o'		=> 5,
				':shock:'	=> 5,
				':p'		=> 6,
				':P'		=> 6,
				':-p'		=> 6,
				':-P'		=> 6,
				':razz:'	=> 6,
				':tounge:'	=> 6,
				':('		=> 7,
				':-('		=> 7,
				':sad:'		=> 7,
				':waii:'	=> 8,
				';)'		=> 9,
				';-)'		=> 9,
				':wink:'	=> 9,
				':arrow:'	=> 10,
				':!:'		=> 11,
				':?:'		=> 12,
				':idea:'	=> 13
			);
			break;
			
		case 'fugue':
			$q_smilies_width = 16;
			$q_smilies_height = 16;
			$q_smilies_positions = array(
				':)'		=> 1,
				':-)'		=> 1,
				':smile:'	=> 1,
				':?'		=> 2,
				':-?'		=> 2,
				':???:'		=> 2,
				':s'		=> 2,
				':S'		=> 2,
				':-s'		=> 2,
				':-S'		=> 2,
				'8)'		=> 3,
				'8-)'		=> 3,
				':cool:'	=> 3,
				':\'('		=> 4,
				':\'-('		=> 4,
				':cry:'		=> 4,
				'8O'		=> 5,
				'8o'		=> 5,
				'8-O'		=> 5,
				'8-o'		=> 5,
				':shock:'	=> 5,
				':evil:'	=> 6,
				'(6)'		=> 6,
				':fat:'		=> 7,
				':D'		=> 8,
				':-D'		=> 8,
				':grin:'	=> 8,
				'^_^'		=> 9,
				'XD'		=> 9,
				'xD'		=> 9,
				'X-D'		=> 9,
				'x-D'		=> 9,
				':lol:'		=> 9,
				':x'		=> 10,
				':-x'		=> 10,
				':@'		=> 10,
				':-@'		=> 10,
				':mad:'		=> 10,
				':|'		=> 11,
				':-|'		=> 11,
				':neutral:'	=> 11,
				':paint:'	=> 12,
				':p'		=> 13,
				':P'		=> 13,
				':-p'		=> 13,
				':-P'		=> 13,
				':razz:'	=> 13,
				':tounge:'	=> 13,
				':$'		=> 14,
				':-$'		=> 14,
				':oops:'	=> 14,
				':roll:'	=> 15,
				':('		=> 16,
				':-('		=> 16,
				':sad:'		=> 16,
				':slim:'	=> 17,
				':o'		=> 18,
				':O'		=> 18,
				':-o'		=> 18,
				':-O'		=> 18,
				':eek:'		=> 18,
				':twisted:'	=> 19,
				'(666)'		=> 19,
				';)'		=> 20,
				';-)'		=> 20,
				':wink:'	=> 20,
				':yell:'	=> 21,
				':arrow:'	=> 22,
				':!:'		=> 22,
				':?:'		=> 23,
				':idea:'	=> 24
			);
			break;

		default: // moskis and wordpress
			if ($q_smilies_set !== 'moskis') $q_smilies_set = 'wordpress';
			$q_smilies_width = 16;
			$q_smilies_height = 15;
			$q_smilies_positions = array(
				':arrow:'	=> 1,
				':D'		=> 2,
				':-D'		=> 2,
				':grin:'	=> 2,
				':?'		=> 3,
				':-?'		=> 3,
				':???:'		=> 3,
				':s'		=> 3,
				':S'		=> 3,
				':-s'		=> 3,
				':-S'		=> 3,
				'8)'		=> 4,
				'8-)'		=> 4,
				':cool:'	=> 4,
				':\'('		=> 5,
				':\'-('		=> 5,
				':cry:'		=> 5,
				'8O'		=> 6,
				'8o'		=> 6,
				'8-O'		=> 6,
				'8-o'		=> 6,
				':shock:'	=> 6,
				':evil:'	=> 7,
				'(6)'		=> 7,
				':!:'		=> 8,
				':idea:'	=> 9,
				'^_^'		=> 10,
				'XD'		=> 10,
				'xD'		=> 10,
				'X-D'		=> 10,
				'x-D'		=> 10,
				':lol:'		=> 10,
				':x'		=> 11,
				':-x'		=> 11,
				':@'		=> 11,
				':-@'		=> 11,
				':mad:'		=> 11,
				':mrgreen:'	=> 12,
				':|'		=> 13,
				':-|'		=> 13,
				':neutral:'	=> 13,
				':?:'		=> 14,
				':p'		=> 15,
				':P'		=> 15,
				':-p'		=> 15,
				':-P'		=> 15,
				':razz:'	=> 15,
				':tounge:'	=> 15,
				':$'		=> 16,
				':-$'		=> 16,
				':oops:'	=> 16,
				':roll:'	=> 17,
				':('		=> 18,
				':-('		=> 18,
				':sad:'		=> 18,
				':)'		=> 19,
				':-)'		=> 19,
				':smile:'	=> 19,
				':o'		=> 20,
				':O'		=> 20,
				':-o'		=> 20,
				':-O'		=> 20,
				':eek:'		=> 20,
				':twisted:'	=> 21,
				'(666)'		=> 21,
				';)'		=> 22,
				';-)'		=> 22,
				':wink:'	=> 22
			);
			break;
	}
	$q_smilies_src = plugins_url(NULL, __FILE__) . "/$q_smilies_set.png";
}


function q_smilies_sample_text() {
	global $q_smilies_set, $user_identity;
	$greetings = array(
		"Hi"				=> ".",
		"Hello"				=> ".",
		"Salut"				=> " <em>and</em> you can greet friends in French!",
		"Ciao"				=> " <em>and</em> you can greet friends in Italian!",
		"Aloha"				=> " <em>and</em> you can greet friends in Hawaiian!",
		"G'day"				=> " <em>and</em> you can greet the Aussies!",
		"Ni hao" 			=> " <em>and</em> you can greet friends in Chinese!",
		"Konnichiwa"		=> " <em>and</em> you can greet friends in Japanese!",
		"Ahoy hoy"			=> " <em>and</em> you can greet sailors! In fact, this nautical greeting so infatuated inventor Alexander Graham Bell he suggested this be the proper way to answer the telephone; try it the next time someone gives you a call.",
		"Jambo"				=> " <em>and</em> you can greet friends in Swahili!",
		"Namaste"			=> " <em>and</em> you can greet friends in Hindi!",
		"Sawubona"			=> " <em>and</em> you can greet friends in Zulu!",
		"Hej"				=> " <em>and</em> you can greet friends in Swedish and Danish!",
		"Hei"				=> " <em>and</em> you can greet friends in Norwegian!",
		"Mingalarba"		=> " <em>and</em> you can greet friends in Burmese!",
		"Hola"				=> " <em>and</em> you can greet friends in Spanish!",
		"Privyet"			=> " <em>and</em> you can greet friends in Russian!",
		"Moi"				=> " <em>and</em> you can greet friends in Finnish!",
		"Annyong"			=> " <em>and</em> you can greet friends in Korean!",
		"Salve"				=> " <em>and</em> you can greet friends in Latin!",
		"Merhaba"			=> " <em>and</em> you can greet friends in Turkish!",
		"Bula"				=> " <em>and</em> you can greet friends in Fijian!"
	);
	$greeting = array_rand($greetings);

	return "<p>$greeting, $user_identity! :p In case you were wondering, :?: you&apos;re looking at some <em>fancy fresh</em> sample text. Oh my! :eek:</p><p>The sun broke quickly over the endless African savanna beginning another clear day. All was quiet save a marimba playing in the distance. :roll: James closed his eyes and began daydreaming. ;-) Suddenly, pieces of broken glass were flying through the air in all directions. :shock: With a thunderous crash, his Jeep bounded out of the underbrush. :lol: </p><p>&quot;Although it is always an adventure,&quot; :| James mused, &quot;this is the last time I let the monkey drive!&quot; :mad: He laced his boots, grabbed his gun, and ran out the door... :s</p><p>:arrow: Now you know how the smilies on your blog will appear{$greetings[$greeting]} :) What a lovely plugin this Speedy Smilies is! 8)</p>";
}
