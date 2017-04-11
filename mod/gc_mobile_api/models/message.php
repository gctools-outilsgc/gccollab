<?php
/*
 * Exposes API endpoints for Message entities
 */

elgg_ws_expose_function(
	"get.message",
	"get_message",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => true)
	),
	'Retrieves a message based on user id and message id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.messages",
	"get_messages",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Retrieves a user\'s messages based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.sentmessages",
	"get_sent_messages",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Retrieves a user\'s sent messages based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"send.message",
	"send_message",
	array(
		"fromuser" => array('type' => 'string', 'required' => true),
		"touser" => array('type' => 'string', 'required' => true),
		"message" => array('type' => 'string', 'required' => true)
	),
	'Submits a message based on "from" user id and "to" user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"reply.message",
	"reply_message",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"message" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Submits a reply to a message based on user id and message id',
	'POST',
	true,
	false
);

function get_message( $user, $guid ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$entity = get_entity( $guid );
	if( !$entity ) return "Message was not found. Please try a different GUID";
	if( !$entity->subtype !== "messages" ) return "Invalid message. Please try a different GUID";

	elgg_set_ignore_access(true);
	
	$messages = elgg_list_entities(array(
	    'type' => 'object',
		'subtype' => 'messages',
		'guid' => $guid
	));
	$message = json_decode($messages)[0];

	$likes = elgg_get_annotations(array(
		'guid' => $message->guid,
		'annotation_name' => 'likes'
	));
	$message->likes = count($likes);

	$liked = elgg_get_annotations(array(
		'guid' => $message->guid,
		'annotation_owner_guid' => $user->guid,
		'annotation_name' => 'likes'
	));
	$message->liked = count($liked) > 0;

	$message->read = $message->readYet;
	$message->fromUserDetails = get_user_block($message->fromId);
	$message->toUserDetails = get_user_block($message->toId);

	return $message;
}

function get_messages( $user, $limit, $offset ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$messages = elgg_list_entities_from_metadata(array(
		"type" => "object",
		"subtype" => "messages",
		"metadata_name" => "toId",
		"metadata_value" => $user_entity->guid,
		"limit" => $limit,
		"offset" => $offset
	));
	$messages = json_decode($messages);

	foreach($messages as $object){
		$likes = elgg_get_annotations(array(
			'guid' => $object->guid,
			'annotation_name' => 'likes'
		));
		$object->likes = count($likes);

		$liked = elgg_get_annotations(array(
			'guid' => $object->guid,
			'annotation_owner_guid' => $viewer->guid,
			'annotation_name' => 'likes'
		));
		$object->liked = count($liked) > 0;

		$object->userDetails = get_user_block($object->owner_guid);
	}

	return $messages;
}

function get_sent_messages( $user, $limit, $offset ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$messages = elgg_list_entities_from_metadata(array(
		"type" => "object",
		"subtype" => "messages",
		"metadata_name" => "fromId",
		"metadata_value" => $user_entity->guid,
		"limit" => $limit,
		"offset" => $offset
	));
	$messages = json_decode($messages);

	foreach($messages as $object){
		$likes = elgg_get_annotations(array(
			'guid' => $object->guid,
			'annotation_name' => 'likes'
		));
		$object->likes = count($likes);

		$liked = elgg_get_annotations(array(
			'guid' => $object->guid,
			'annotation_owner_guid' => $viewer->guid,
			'annotation_name' => 'likes'
		));
		$object->liked = count($liked) > 0;

		$object->userDetails = get_user_block($object->owner_guid);
	}

	return $messages;
}

function send_message( $fromuser, $touser, $subject, $message ){
	$from_user_entity = is_numeric($fromuser) ? get_user($fromuser) : ( strpos($fromuser, '@') !== FALSE ? get_user_by_email($fromuser)[0] : get_user_by_username($fromuser) );
 	if( !$from_user_entity ) return "\"From\" User was not found. Please try a different GUID, username, or email address";
	if( !$from_user_entity instanceof ElggUser ) return "Invalid \"from\" user. Please try a different GUID, username, or email address";

	$to_user_entity = is_numeric($touser) ? get_user($touser) : ( strpos($touser, '@') !== FALSE ? get_user_by_email($touser)[0] : get_user_by_username($touser) );
 	if( !$to_user_entity ) return "\"To\" User was not found. Please try a different GUID, username, or email address";
	if( !$to_user_entity instanceof ElggUser ) return "Invalid \"to\" user. Please try a different GUID, username, or email address";

	if( trim($subject) == "" ) return "A subject must be provided to send a message";
	if( trim($message) == "" ) return "A message must be provided to send a message";

	if( elgg_is_active_plugin('cp_notifications') ){
		$messageData = array(
			'cp_from' => $from_user_entity,
			'cp_to' => $to_user_entity,
			'cp_topic_title' => $subject,
			'cp_topic_description' => $message,
			'cp_msg_type' => 'cp_site_msg_type',
			'cp_topic_url' => elgg_get_site_url() . '/messages/inbox/',
		);
		$result = elgg_trigger_plugin_hook('cp_overwrite_notification', 'all', $messageData);
		$result = true;
	} else {
		$result = messages_send($subject, $message, $to_user_entity->guid, $from_user_entity->guid);
	}

	if( !$result ) return elgg_echo("messages:error");

	return elgg_echo("messages:posted");
}

function reply_message( $user, $message, $guid ){
	$from_user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$from_user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$from_user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$entity = get_entity( $guid );
	if( !$entity ) return "Message was not found. Please try a different GUID";
	if( !$entity->subtype !== "messages" ) return "Invalid message. Please try a different GUID";

	$to_user_entity = get_user( $entity->fromId );

	if( trim($message) == "" ) return "A message must be provided to send a message";

	if( elgg_is_active_plugin('cp_notifications') ){
		$messageData = array(
			'cp_from' => $from_user_entity,
			'cp_to' => $to_user_entity,
			'cp_topic_title' => $entity->title,
			'cp_topic_description' => $message,
			'cp_msg_type' => 'cp_site_msg_type',
			'cp_topic_url' => elgg_get_site_url() . '/messages/inbox/',
		);
		$result = elgg_trigger_plugin_hook('cp_overwrite_notification', 'all', $messageData);
		$result = true;
	} else {
		$result = messages_send($entity->title, $message, $to_user_entity->guid, $from_user_entity->guid, $guid);
	}

	if( !$result ) return elgg_echo("messages:error");

	return elgg_echo("messages:posted");
}
