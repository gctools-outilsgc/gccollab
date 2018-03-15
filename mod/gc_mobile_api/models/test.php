<?php


elgg_ws_expose_function(
	"submit.commenttest",
	"submit_comment_test",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => true),
		"message" => array('type' => 'string', 'required' => true),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Gets comments for given entity (guid), uses limit and offset.',
	'POST',
	true,
	false
);



function submit_comment_test($user, $guid, $message, $lang)
{
	// No Empty Comments
	if (empty($message)){
		return elgg_echo("generic_comment:blank");
	}

	$user_entity = is_numeric($user) ? get_user($user) : (strpos($user, '@') !== false ? get_user_by_email($user)[0] : get_user_by_username($user));
	if (!$user_entity) {
		return "User was not found. Please try a different GUID, username, or email address";
	}
	if (!$user_entity instanceof ElggUser) {
		return "Invalid user. Please try a different GUID, username, or email address";
	}
	if (!elgg_is_logged_in()) {
		login($user_entity);
	}

	$entity = get_entity($guid);
	$subtype = 'nothing';
	if (!$entity) {
		return elgg_echo("generic_comment:notfound");
	} else if (elgg_instanceof($entity, "object", "groupforumtopic")){
		$subtype = 'discussion_reply';
		// Check if member of group
		$group = $entity->getContainerEntity();
		if (!$group->canWriteToContainer()) {
			return elgg_echo('groups:notmember');
		}
	} else {
		$subtype = 'comment';
	}


	$reply = '';
	if ($subtype == 'discussion_reply'){
		$reply = new ElggDiscussionReply();
	} else if ($subtype == 'comment'){
		$reply = new ElggComment();
	}

	$reply->description = $message;
	$reply->owner_guid = $user_entity->getGUID();
	$reply->container_guid =  $entity->getGUID();
	$reply->access_id = $entity->access_id; //idk

	$reply_guid = $reply->save();
	// If save comment fails, return message about it
	if ($reply_guid == false){
		if ($subtype == 'discussion_reply') {
			return elgg_echo('groupspost:failure');
		} else if ($subtype == 'comment'){
			return elgg_echo("generic_comment:failure");
		} else {
			return "reply failed - unknown - ref 00001 "; //this shouldnt happen
		}
	}


	//River Item
	if ($subtype == 'discussion_reply') {
		elgg_create_river_item(array(
		'view' => 'river/object/discussion_reply/create',
		'action_type' => 'reply',
		'subject_guid' => $user_entity->guid,
		'object_guid' => $reply_guid,
		'target_guid' => $entity->guid,
		));
		return elgg_echo('groupspost:success');
	} else if ($subtype == 'comment'){
		elgg_create_river_item(array(
			'view' => 'river/object/comment/create',
			'action_type' => 'comment',
			'subject_guid' => $user_entity->guid,
			'object_guid' => $reply_guid,
			'target_guid' => $entity->guid,
		));
		return elgg_echo('generic_comment:posted');
	}

	$result = "Should not reach this, but in-case, ref 00002";
	return $result;
}
