<?php
/*
* GC_MODIFICATION
* Description: Added accessible labels + GSA tie in
* Author: GCTools Team
*/
 $params = array(
 	'name' => 'q', // GSA name so the query goes through gsa
//     //'action'=>'/search',
//     'id' => 'member_query',
// 	'class' => 'mbm',
// 	'required' => true,
 );
echo '<label for="member_query" class="wb-inv">'.elgg_echo('members:search').'</label>'.elgg_view('input/text', $params);
//echo elgg_view('input/hidden', array('name'=>'gcconnex[]', 'value'=>'Members',)); //hidden input to filter search results to only gcconnex members

$user_types = array(
	'' => elgg_echo('gcRegister:make_selection'),
	'academic' => elgg_echo('gcRegister:occupation:academic'),
	'student' => elgg_echo('gcRegister:occupation:student'),
	'federal' => elgg_echo('gcRegister:occupation:federal'),
	'provincial' => elgg_echo('gcRegister:occupation:provincial'),
	'municipal' => elgg_echo('gcRegister:occupation:municipal'),
	'international' => elgg_echo('gcRegister:occupation:international'),
	'ngo' => elgg_echo('gcRegister:occupation:ngo'),
	'community' => elgg_echo('gcRegister:occupation:community'),
	'business' => elgg_echo('gcRegister:occupation:business'),
	'media' => elgg_echo('gcRegister:occupation:media'),
	'retired' => elgg_echo('gcRegister:occupation:retired'),
	'other' => elgg_echo('gcRegister:occupation:other')
);
echo "<label class='mtm' for='user_type'>" . elgg_echo('gcRegister:membertype') . "</label>" . elgg_view('input/dropdown', array('id' => 'user_type', 'class' => 'mbm', 'name' => 'user_type', 'options_values' => $user_types));

// cyu - patched so that the member search will use the gsa (gcintranet)
echo elgg_view('input/hidden', array('name'=>'a', 'value'=>'s'));
echo elgg_view('input/hidden', array('name'=>'s', 'value'=>'3'));
echo elgg_view('input/hidden', array('name'=>'chk4', 'value'=>'on'));
echo elgg_view('input/hidden', array('name'=>'gcc', 'value'=>'2'));


echo elgg_view('input/submit', array('value' => elgg_echo('search')));

echo "<p class='mtl elgg-text-help timeStamp'>" . elgg_echo('members:total', array(get_number_users())) . "</p>";
