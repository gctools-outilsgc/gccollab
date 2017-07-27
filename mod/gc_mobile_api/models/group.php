<?php
/*
 * Exposes API endpoints for Group entities
 */

elgg_ws_expose_function(
	"get.group",
	"get_group",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => true),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Retrieves a group based on user id and group id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.groups",
	"get_groups",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Retrieves a group based on user id and group id',
	'POST',
	true,
	false
);

function get_group( $user, $guid, $lang ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$entity = get_entity( $guid );
	if( !$entity ) return "Group was not found. Please try a different GUID";
	if( !$entity instanceof ElggGroup ) return "Invalid group. Please try a different GUID";

	elgg_set_ignore_access(true);
	
	$groups = elgg_list_entities(array(
		'type' => 'group',
		'guid' => $guid
	));
	$group = json_decode($groups)[0];

	$likes = elgg_get_annotations(array(
		'guid' => $group->guid,
		'annotation_name' => 'likes'
	));
	$group->likes = count($likes);

	$liked = elgg_get_annotations(array(
		'guid' => $group->guid,
		'annotation_owner_guid' => $user_entity->guid,
		'annotation_name' => 'likes'
	));
	$group->liked = count($liked) > 0;

	$group->comments = get_entity_comments($group->guid);
	
	$group->userDetails = get_user_block($group->owner_guid);
	$group->description = clean_text($group->description);

	return $group;
}

function get_groups( $user, $limit, $offset, $lang ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	elgg_set_ignore_access(true);
	
	$all_groups = elgg_list_entities(array(
		// 'type' => 'object',
		'type' => 'group',
		'limit' => $limit,
		'offset' => $offset
	));
	$groups = json_decode($all_groups);

	foreach($groups as $group){
		$likes = elgg_get_annotations(array(
			'guid' => $group->guid,
			'annotation_name' => 'likes'
		));
		$group->likes = count($likes);

		$liked = elgg_get_annotations(array(
			'guid' => $group->guid,
			'annotation_owner_guid' => $user_entity->guid,
			'annotation_name' => 'likes'
		));
		$group->liked = count($liked) > 0;

		$groupObj = get_entity($group->guid);
		$group->member = $groupObj->isMember($user_entity);
		$group->owner = ($groupObj->getOwnerEntity() == $user_entity);
		$group->iconURL = $groupObj->geticon();
		$group->count = $groupObj->getMembers(array('count' => true));

		$group->userDetails = get_user_block($group->owner_guid);
		$group->description = clean_text($group->description);
	}

	return $groups;
}
