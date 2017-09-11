<?php
/*
 * Exposes API endpoints for Member entities
 */

elgg_ws_expose_function(
	"get.members",
	"get_members",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Retrieves members registered on GCcollab',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.memberscolleague",
	"get_members_colleague",
	array(
		"profileemail" => array('type' => 'string', 'required' => true),
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Retrieves members registered on GCcollab',
	'POST',
	true,
	false
);

function get_members( $user, $limit, $offset, $lang ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	elgg_set_ignore_access(true);

	$members = elgg_get_entities(array(
        'type' => 'user',
        'limit' => $limit,
        'offset' => $offset
    ));

	$data = array();
	foreach($members as $member){
		$member_obj = get_user($member->guid);
		$member_data = get_user_block($member->guid);

		$about = "";
		if( $member_obj->description ){
			$about = strip_tags($member_obj->description, '<p>');
			$about = str_replace("<p>&nbsp;</p>", '', $about);
		}

		$member_data['about'] = $about;
		$data[] = $member_data;
	}

	return $data;
}

function get_members_colleague( $profileemail, $user, $limit, $offset, $lang ){
	$user_entity = is_numeric($profileemail) ? get_user($profileemail) : ( strpos($profileemail, '@') !== FALSE ? get_user_by_email($profileemail)[0] : get_user_by_username($profileemail) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$viewer = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$viewer ) return "Viewer user was not found. Please try a different GUID, username, or email address";
	if( !$viewer instanceof ElggUser ) return "Invalid viewer user. Please try a different GUID, username, or email address";
	
	elgg_set_ignore_access(true);

	$members = $user_entity->listFriends('', $limit, array(
		'limit' => $limit,
        'offset' => $offset
	));
	$members = json_decode($members);

	$data = array();
	foreach($members as $member){
		$member_obj = get_user($member->guid);
		$member_data = get_user_block($member->guid);
		
		$about = "";
		if( $member_obj->description ){
			$about = strip_tags($member_obj->description, '<p>');
			$about = str_replace("<p>&nbsp;</p>", '', $about);
		}

		$member_data['about'] = $about;
		$data[] = $member_data;
	}

	return $data;
}
