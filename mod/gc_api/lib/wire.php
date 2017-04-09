<?php

elgg_ws_expose_function("get.wire","get_wire_post", array(
"query" => array('type' => 'string','required' => false, 'default' => ' '),
"limit" => array('type' => 'int','required' => false, 'default' => 15),
),'returns wire posts based on query',
'GET', false, false);

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

function get_wire_post($query, $limit){
	$posts = array();	
	$result = 'Nothing to return';
	$query = trim($query,' \"');
	//error_log($query);
	if ($query){
		$firstChar = $query[0];
		if ($firstChar === '@'){
		//	error_log('@');
			$user = get_user_by_username(substr($query, 1));
			if (!$user){
				return 'user does not exist';
			}
			$options = array(
				'subtype'=>'thewire',
				'type' => 'object',
				'owner_guid' => $user->guid,
				'limit' => $limit
			);
			$wire_posts = elgg_get_entities($options);
			if (!$wire_posts){
				////////////////////////////////////////////////////////
				//TODO: handle no wire posts by user.
				//return empty result may be valid, or error code, or string
			
				//return 
			}
		}else{
			$options = array(
				//'metadata_name' => 'tags',
				//'metadata_value' => $tag,
				//'metadata_case_sensitive' => false,
				'type' => 'object',
				'subtype' => 'thewire',
				'limit' => $limit,
				'joins' => array("JOIN " . elgg_get_config("dbprefix") . "objects_entity oe ON e.guid = oe.guid"),
				'wheres' => array("oe.description LIKE '%#" . sanitise_string($query) . "%'"),
			);
			$wire_posts = elgg_get_entities($options);
		}
		
	}else{
		$options = array(
			'subtype'=>'thewire',
			'type' => 'object',
			'limit' => $limit
		);	
		$wire_posts = elgg_get_entities($options);
	}
	if (!$wireposts){
		//return 'no wire posts with with #'.$query." tags. For usernames, please add '@' to front of query string";
	}
	$i = 0;
	foreach($wire_posts as $wp){
		//error_log(var_dump($wp));
        //Nick - added guid to the api
		$posts['post_'.$i]['guid'] = $wp->guid;
		$posts['post_'.$i]['text'] = thewire_filter($wp->description);
		$posts['post_'.$i]['time_created'] = $wp->time_created;
		$posts['post_'.$i]['time_since'] = time_elapsed_B(time()-$wp->time_created);
		$posts['post_'.$i]['user'] = get_userBlock($wp->owner_guid);
        
		$i++;
	}
	if ($posts){
		$result = null;
		$result['posts'] = $posts;
	}
	return $result;
}

function time_elapsed_B($secs){
    /*$bit = array(
        ' day'        => $secs / 86400 % 7,
        ' hour'        => $secs / 3600 % 24,
        ' minute'    => $secs / 60 % 60,
        ' second'    => $secs % 60
        );
        
    foreach($bit as $k => $v){
        if($v > 1)$ret = $v . $k . 's';
        if($v == 1)$ret = $v . $k;
        }
    //array_splice($ret, count($ret)-1, 0, 'and');
    $ret .= 'ago';
	 * 
    */
    if ($secs / 86400 % 7 >= 1){
    	$num = $secs / 86400 % 7;
    	$string = 'd';
    }else{
    	if ($secs / 3600 % 24 >= 1){
    		$num = $secs / 3600 % 24;
			$string = 'h';
    	}else{
    		if ($secs / 60 % 60 >= 1){
    			$num = $secs / 60 % 60;
				$string = 'm';
    		}else{
    			if ($secs % 60 >= 1){
    				$num = $secs % 60;
					$string = 's';
    			}
    		}
    	}
    }
	
    return $num.$string;
}

function get_wirepost( $id, $guid, $thread ){
	$user = ( strpos($id, '@') !== FALSE ) ? get_user_by_email($id)[0] : getUserFromID($id);

 	if( !$user )
		return "User was not found. Please try a different GUID, username, or email address";

	if( !$user instanceof \ElggUser ){
		return "Invalid user. Please try a different GUID, username, or email address";
	}

	if( !$guid )
		return "Wire Post was not found. Please try a different GUID";

	$wire_post = get_entity($guid);
	$thread_id = $wire_post->wire_thread;

	if( $thread ){
		$wire_posts = elgg_list_entities_from_metadata(array(
			"metadata_name" => "wire_thread",
			"metadata_value" => $thread_id,
			"type" => "object",
			"subtype" => "thewire",
			"limit" => 0,
			'preload_owners' => true,
		));
		$wire_posts = json_decode($wire_posts);
		foreach($wire_posts as $object){
			$likes = elgg_get_annotations(array(
				'guid' => $object->guid,
				'annotation_name' => 'likes'
			));
			$object->likes = count($likes);

			$liked = elgg_get_annotations(array(
				'guid' => $object->guid,
				'annotation_owner_guid' => $user->guid,
				'annotation_name' => 'likes'
			));
			$object->liked = count($liked) > 0;

			$replied = elgg_get_entities_from_metadata(array(
				"metadata_name" => "wire_thread",
				"metadata_value" => $thread_id,
				"type" => "object",
				"subtype" => "thewire",
				'owner_guid' => $user->guid
			));
			$object->replied = count($replied) > 0;

			$owner = get_user($object->owner_guid);
			$object->displayName = $owner->name;
			$object->email = $owner->email;
			$object->profileURL = $owner->getURL();
			$object->iconURL = $owner->geticon();
			$object->thread_id = $wire_post->wire_thread;
		}
	} else {
		$wire_posts = elgg_list_entities(array(
			'guid' => $guid
		));
		$wire_posts = json_decode($wire_posts);
		foreach($wire_posts as $object){
			$likes = elgg_get_annotations(array(
				'guid' => $object->guid,
				'annotation_name' => 'likes'
			));
			$object->likes = count($likes);

			$liked = elgg_get_annotations(array(
				'guid' => $object->guid,
				'annotation_owner_guid' => $user->guid,
				'annotation_name' => 'likes'
			));
			$object->liked = count($liked) > 0;

			$replied = elgg_get_entities_from_metadata(array(
				"metadata_name" => "wire_thread",
				"metadata_value" => $thread_id,
				"type" => "object",
				"subtype" => "thewire",
				'owner_guid' => $user->guid
			));
			$object->replied = count($replied) > 0;

			$owner = get_user($object->owner_guid);
			$object->displayName = $owner->name;
			$object->email = $owner->email;
			$object->profileURL = $owner->getURL();
			$object->iconURL = $owner->geticon();
			$object->thread_id = $wire_post->wire_thread;
		}
	}

	return $wire_posts;
}

function get_wireposts( $id, $limit, $offset ){
	$user = ( strpos($id, '@') !== FALSE ) ? get_user_by_email($id)[0] : getUserFromID($id);

 	if( !$user )
		return "User was not found. Please try a different GUID, username, or email address";

	if( !$user instanceof \ElggUser ){
		return "Invalid user. Please try a different GUID, username, or email address";
	}

	$options = array(
		'type' => 'object',
		'subtype' => 'thewire',
		'owner_guid' => $user->guid,
		'limit' => $limit,
		'offset' => $offset
	);
	$wire_posts = elgg_list_entities($options);
	$wire_posts = json_decode($wire_posts);

	return $wire_posts;
}

function reply_wire( $id, $message, $guid ){
	$user = ( strpos($id, '@') !== FALSE ) ? get_user_by_email($id)[0] : getUserFromID($id);

 	if( !$user )
		return "User was not found. Please try a different GUID, username, or email address";

	if( !$message )
		return "A message must be sent to reply to the Wire Post";

	// Let's see if we can get a Wire Post with the specified GUID
	$entity = get_entity($guid);
	
	$access_id = ACCESS_PUBLIC;

	// make sure the post isn't blank
	if( empty($message) ){
		return elgg_echo("thewire:blank");
	}

	elgg_set_ignore_access(true);

	$new_wire = thewire_save_post($message, $user->guid, $access_id, $guid);
	if( !$new_wire ){
		return elgg_echo("thewire:notsaved");
	}

	return elgg_echo("thewire:posted");
}
