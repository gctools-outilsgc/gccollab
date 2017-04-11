<?php
/*
 * Exposes API endpoints for Wire entities
 */

elgg_ws_expose_function(
	"get.wirepost",
	"get_wirepost",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => true),
		"thread" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Retrieves a wire post & all replies based on user id and wire post id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.wireposts",
	"get_wireposts",
	array(
		"profileemail" => array('type' => 'string', 'required' => true),
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Retrieves a user\'s wire posts based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"reply.wire",
	"reply_wire",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"message" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Submits a reply to a wire post based on user id and wire post id',
	'POST',
	true,
	false
);

function get_wirepost( $user, $guid, $thread ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$entity = get_entity( $guid );
	if( !$entity ) return "Wire was not found. Please try a different GUID";
	if( !$entity instanceof ElggWire ) return "Invalid wire. Please try a different GUID";

	$thread_id = $entity->wire_thread;

	if( $thread ){
		$all_wire_posts = elgg_list_entities_from_metadata(array(
			"metadata_name" => "wire_thread",
			"metadata_value" => $thread_id,
			"type" => "object",
			"subtype" => "thewire",
			"limit" => 0,
			"preload_owners" => true
		));
		$wire_posts = json_decode($all_wire_posts);

		foreach($wire_posts as $wire_post){
			$likes = elgg_get_annotations(array(
				'guid' => $wire_post->guid,
				'annotation_name' => 'likes'
			));
			$wire_post->likes = count($likes);

			$liked = elgg_get_annotations(array(
				'guid' => $wire_post->guid,
				'annotation_owner_guid' => $user_entity->guid,
				'annotation_name' => 'likes'
			));
			$wire_post->liked = count($liked) > 0;

			$replied = elgg_get_entities_from_metadata(array(
				"metadata_name" => "wire_thread",
				"metadata_value" => $thread_id,
				"type" => "object",
				"subtype" => "thewire",
				"owner_guid" => $user_entity->guid
			));
			$wire_post->replied = count($replied) > 0;

			$wire_post->thread_id = $thread_id;

			$wire_post->userDetails = get_user_block($wire_post->owner_guid);
		}
	} else {
		$wire_posts = elgg_list_entities(array(
			"type" => "object",
			"subtype" => "thewire",
			"guid" => $guid
		));
		$wire_post = json_decode($wire_posts)[0];

		$likes = elgg_get_annotations(array(
			'guid' => $wire_post->guid,
			'annotation_name' => 'likes'
		));
		$wire_post->likes = count($likes);

		$liked = elgg_get_annotations(array(
			'guid' => $wire_post->guid,
			'annotation_owner_guid' => $user_entity->guid,
			'annotation_name' => 'likes'
		));
		$wire_post->liked = count($liked) > 0;

		$replied = elgg_get_entities_from_metadata(array(
			"metadata_name" => "wire_thread",
			"metadata_value" => $thread_id,
			"type" => "object",
			"subtype" => "thewire",
			"owner_guid" => $user_entity->guid
		));
		$wire_post->replied = count($replied) > 0;

		$wire_post->thread_id = $thread_id;
		
		$wire_post->userDetails = get_user_block($wire_post->owner_guid);

		$wire_posts = $wire_post;
	}

	return $wire_posts;
}

function get_wireposts( $profileemail, $user, $limit, $offset ){
	$user_entity = is_numeric($profileemail) ? get_user($profileemail) : ( strpos($profileemail, '@') !== FALSE ? get_user_by_email($profileemail)[0] : get_user_by_username($profileemail) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$viewer = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$viewer ) return "Viewer user was not found. Please try a different GUID, username, or email address";
	if( !$viewer instanceof ElggUser ) return "Invalid viewer user. Please try a different GUID, username, or email address";

	$all_wire_posts = elgg_list_entities(array(
		'type' => 'object',
		'subtype' => 'thewire',
		'owner_guid' => $user_entity->guid,
		'limit' => $limit,
		'offset' => $offset
	));
	$wire_posts = json_decode($all_wire_posts);

	foreach($wire_posts as $wire_post){
		$likes = elgg_get_annotations(array(
			'guid' => $wire_post->guid,
			'annotation_name' => 'likes'
		));
		$wire_post->likes = count($likes);

		$liked = elgg_get_annotations(array(
			'guid' => $wire_post->guid,
			'annotation_owner_guid' => $viewer->guid,
			'annotation_name' => 'likes'
		));
		$wire_post->liked = count($liked) > 0;

		$replied = elgg_get_entities_from_metadata(array(
			"metadata_name" => "wire_thread",
			"metadata_value" => $wire_post->wire_thread,
			"type" => "object",
			"subtype" => "thewire",
			"owner_guid" => $viewer->guid
		));
		$wire_post->replied = count($replied) > 0;

		$wire_post->userDetails = get_user_block($wire_post->owner_guid);
	}

	return $wire_posts;
}

function reply_wire( $user, $message, $guid ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	if( trim($message) == "" ) return elgg_echo("thewire:blank");

	elgg_set_ignore_access(true);

	$new_wire = thewire_save_post($message, $user_entity->guid, ACCESS_PUBLIC, $guid);
	if( !$new_wire ) return elgg_echo("thewire:notsaved");

	return elgg_echo("thewire:posted");
}
