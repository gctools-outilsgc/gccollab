<?php
/*
 * Exposes API endpoints for User entities
 */

elgg_ws_expose_function(
	"get.user",
	"get_user_data",
	array(
		"profileemail" => array('type' => 'string', 'required' => true),
		"user" => array('type' => 'string', 'required' => false)
	),
	'Retrieves a user\'s profile information based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.userexists",
	"get_user_exists",
	array(
		"user" => array('type' => 'string', 'required' => false)
	),
	'Retrieves whether a user exists based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.useractivity",
	"get_user_activity",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Retrieves a user\'s activity information based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.usergroups",
	"get_user_groups",
	array(
		"user" => array('type' => 'string', 'required' => true)
	),
	'Retrieves a user\'s group information based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.userposts",
	"get_user_posts",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"type" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Retrieves a user\'s post information based on post type and user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.usercolleagueposts",
	"get_user_colleague_posts",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"type" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'Retrieves a user\'s colleague\'s post information based on post type and user id',
	'POST',
	true,
	false
);

function build_date($month, $year){
	switch($month){
		case 1:
			$string = "01/";
			break;
		case 2:
			$string = "02/";
			break;
		case 3:
			$string = "03/";
			break;
		case 4:
			$string = "04/";
			break;
		case 5:
			$string = "05/";
			break;
		case 6:
			$string = "06/";
			break;
		case 7:
			$string = "07/";
			break;
		case 8:
			$string = "08/";
			break;
		case 9:
			$string = "09/";
			break;
		case 10:
			$string = "10/";
			break;
		case 11:
			$string = "11/";
			break;
		case 12:
			$string = "12/";
			break;
	}	
	return $string . $year;
}

function get_user_data( $profileemail, $user ){
	$user_entity = is_numeric($profileemail) ? get_user($profileemail) : ( strpos($profileemail, '@') !== FALSE ? get_user_by_email($profileemail)[0] : get_user_by_username($profileemail) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	if( $user ){
		$viewer = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
	 	if( !$viewer ) return "Viewer user was not found. Please try a different GUID, username, or email address";
		if( !$viewer instanceof ElggUser ) return "Invalid viewer user. Please try a different GUID, username, or email address";
		
		$friends = $viewer->isFriendsWith($user_entity->guid);
	} else {
		$friends = true;
	}

	$user = array();

	$user['id'] = $user_entity->guid;
	$user['user_type'] = $user_entity->user_type;
	$user['username'] = $user_entity->username;
	$user['displayName'] = $user_entity->name;
	$user['email'] = $user_entity->email;
	$user['profileURL'] = $user_entity->getURL();
	$user['iconURL'] = $user_entity->geticon();
	$user['jobTitle'] = $user_entity->job;

	switch ($user_entity->user_type) {
		case "federal":
			$user['department'] = $user_entity->federal;
			break;
		case "student":
		case "academic":
			$institution = $user_entity->institution;
		    $user['department'] = ($institution == 'university') ? $user_entity->university : $user_entity->college;
			break;
		case "provincial":
			$user['department'] = $user_entity->provincial . ' / ' . $user_entity->ministry;
			break;
		default:
			$user['department'] = $user_entity->{$user_entity->user_type};
			break;
	}

	$user['telephone'] = $user_entity->phone;
	$user['mobile'] = $user_entity->mobile;
	$user['website'] = $user_entity->website;

	if( $user_entity->facebook )
		$user['links']['facebook'] = "http://www.facebook.com/".$user_entity->facebook;
	if( $user_entity->google )
		$user['links']['google'] = "http://www.google.com/".$user_entity->google;
	if( $user_entity->github )
		$user['links']['github'] = "https://github.com/".$user_entity->github;
	if( $user_entity->twitter )
		$user['links']['twitter'] = "https://twitter.com/".$user_entity->twitter;
	if( $user_entity->linkedin )
		$user['links']['linkedin'] = "http://ca.linkedin.com/in/".$user_entity->linkedin;
	if( $user_entity->pinterest )
		$user['links']['pinterest'] = "http://www.pinterest.com/".$user_entity->pinterest;
	if( $user_entity->tumblr )
		$user['links']['tumblr'] = "https://www.tumblr.com/blog/".$user_entity->tumblr;
	if( $user_entity->instagram )
		$user['links']['instagram'] = "http://instagram.com/".$user_entity->instagram;
	if( $user_entity->flickr )
		$user['links']['flickr'] = "http://flickr.com/".$user_entity->flickr;
	if( $user_entity->youtube )
		$user['links']['youtube'] = "http://www.youtube.com/".$user_entity->youtube;

	// About Me
	$aboutMeMetadata = elgg_get_metadata(array('guids'=>array($user['id']),'limit'=>0,'metadata_names'=>array('description')));
	
	if( $aboutMeMetadata[0]->access_id == ACCESS_PUBLIC || $aboutMeMetadata[0]->access_id == ACCESS_LOGGED_IN || ($friends && $aboutMeMetadata[0]->access_id == ACCESS_FRIENDS) ){
		$user['about_me'] = $aboutMeMetadata[0]->value;
	}
	
	// Education
	$educationEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'education',
		'type' => 'object',
		'limit' => 0
	));
	$i = 0;
	foreach( $educationEntity as $school ){
		if( $school->access_id == ACCESS_PUBLIC || $school->access_id == ACCESS_LOGGED_IN || ($friends && $school->access_id == ACCESS_FRIENDS) ){
			$user['education']['item_'.$i]['school_name'] = $school->school;
			
			$user['education']['item_'.$i]['start_date'] = build_date($school->startdate, $school->startyear);
			
			if($school->ongoing == "false"){
				$user['education']['item_'.$i]['end_date'] = build_date($school->enddate,$school->endyear);
			}else{
				$user['education']['item_'.$i]['end_date'] = "present/actuel";
			}
			$user['education']['item_'.$i]['degree'] = $school->degree;
			$user['education']['item_'.$i]['field_of_study'] = $school->field;
			$i++;
		}
	}

	// Experience
	$experienceEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'experience',
		'type' => 'object',
		'limit' => 0
	));
	usort($experienceEntity, "sortDate");
	$i = 0;
	foreach( $experienceEntity as $job ){
		if( $job->access_id == ACCESS_PUBLIC || $job->access_id == ACCESS_LOGGED_IN || ($friends && $job->access_id == ACCESS_FRIENDS) ){
			$jobMetadata = elgg_get_metadata(array(
				'guid' => $job->guid,
				'limit' => 0
			));
			//foreach ($jobMetadata as $data)
			//	$user['job'][$i++][$data->name] = $data->value;

			$user['experience']['item_'.$i]['job_title'] = $job->title;
			$user['experience']['item_'.$i]['organization'] = $job->organization;
			$user['experience']['item_'.$i]['start_date'] = build_date($job->startdate, $job->startyear);
			if ($job->ongoing == "false"){
				$user['experience']['item_'.$i]['end_date'] = build_date($job->enddate, $job->endyear);
			}else{
				$user['experience']['item_'.$i]['end_date'] = "present/actuel";
			}
			$user['experience']['item_'.$i]['responsibilities'] = $job->responsibilities;
			//$user['experience']['item_'.$i]['colleagues'] = $job->colleagues;
			$j = 0;
			if( is_array($job->colleagues) ){
				foreach( $job->colleagues as $friend ){
					$friendEntity = get_user($friend);
					$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["id"] = $friendEntity->guid;
					$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["username"] = $friendEntity->username;
	
					//get and store user display name
					$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["displayName"] = $friendEntity->name;
	
					//get and store URL for profile
					$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["profileURL"] = $friendEntity->getURL();
	
					//get and store URL of profile avatar
					$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["iconURL"] = $friendEntity->geticon();
					$j++;
				}
			} else if( !is_null($job->colleagues) ){
				$friendEntity = get_user($job->colleagues);
				$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["id"] = $friendEntity->guid;
				$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["username"] = $friendEntity->username;
	
				//get and store user display name
				$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["displayName"] = $friendEntity->name;
		
				//get and store URL for profile
				$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["profileURL"] = $friendEntity->getURL();
	
				//get and store URL of profile avatar
				$user['experience']['item_'.$i]['colleagues']['colleague_'.$j]["iconURL"] = $friendEntity->geticon();
					
			}
			$i++;
		}
	}

	// Skills
	if( $user_entity->skill_access == ACCESS_PUBLIC || $user_entity->skill_access == ACCESS_LOGGED_IN || ($friends && $user_entity->skill_access == ACCESS_FRIENDS) ){
		$skillsEntity = elgg_get_entities(array(
			'owner_guid'=>$user['id'],
			'subtype'=>'MySkill',
			'type' => 'object',
			'limit' => 0
		));
	}
	$i=0;
	foreach($skillsEntity as $skill){
		$user['skills']['item_'.$i]['skill'] = $skill->title;
		//$user['skills']['item_'.$i]['endorsements'] = $skill->endorsements;
		$j = 0;
		if( is_array($skill->endorsements) ){
			foreach( $skill->endorsements as $friend ){
				$friendEntity = get_user($friend);
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["id"] = $friendEntity->guid; 
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["username"] = $friendEntity->username;
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["displayName"] = $friendEntity->name;
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["profileURL"] = $friendEntity->getURL();
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["iconURL"] = $friendEntity->geticon();
				$j++;
			}
		} else if( !is_null($skill->endorsements) ){
			$friendEntity = get_user($skill->endorsements);
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["id"] = $friendEntity->guid; 
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["username"] = $friendEntity->username;
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["displayName"] = $friendEntity->name;
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["profileURL"] = $friendEntity->getURL();
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["iconURL"] = $friendEntity->geticon();
		}
		$i++;
	}

	// Language
	//$user['language']["format"] = "Written Comprehension / Written Expression / Oral Proficiency";
	/*$languageMetadata =  elgg_get_metadata(array(
		'guid'=>$user['id'],
		'limit'=>0,
		'metadata_name'=>'english'
		));
	if (!is_null($languageMetadata)){
		if($languageMetadata[0]->access_id == 2){
			$user['language']["format"] = "Written Comprehension / Written Expression / Oral Proficiency";
		}
		$i = 0;
		foreach($languageMetadata as $grade){
			if($grade->access_id == 2){
				
				if($i < 3)
					$user['language']["english"]['level'] .= $grade->value;
				if($i<2){
					$user['language']["english"]['level'].=" / ";
				}
				if($i == 3)
					$user['language']["english"]['expire'] = $grade->value;
			}
			$i++;
		}
	}
	$languageMetadata =  elgg_get_metadata(array(
		'guid'=>$user['id'],
		'limit'=>0,
		'metadata_name'=>'french'
		));
	if (!is_null($languageMetadata)){
		$i = 0;
		foreach($languageMetadata as $grade){
			if($grade->access_id == 2){
				if ($i<3)
					$user['language']["french"]['level'] .= $grade->value;
				if($i<2){
					$user['language']["french"]['level'] .= " / ";
				}
				if($i == 3)
					$user['language']["french"]['expire'] = $grade->value;
			}
			$i++;
		}
	}*/

	// Portfolio
	$portfolioEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'portfolio',
		'type' => 'object',
		'limit' => 0
	));
	$i = 0;
	foreach( $portfolioEntity as $portfolio ){
		if( $portfolio->access_id == ACCESS_PUBLIC || $portfolio->access_id == ACCESS_LOGGED_IN || ($friends && $portfolio->access_id == ACCESS_FRIENDS) ){
			$user['portfolio']['item_'.$i]['title'] = $portfolio->title;
			$user['portfolio']['item_'.$i]['link'] = $portfolio->link;
			if($portfolio->datestamped == "on")
				$user['portfolio']['item_'.$i]['date'] = $portfolio->publishdate;
			$user['portfolio']['item_'.$i]['description'] = $portfolio->description;
		}
	}

	$user['dateJoined'] = date("Y-m-d H:i:s", $user_entity->time_created);
	$user['lastActivity'] = date("Y-m-d H:i:s", $user_entity->last_action);
	$user['lastLogin'] = date("Y-m-d H:i:s", $user_entity->last_login);

	$options = array(
		'type' => 'object',
		'subtype' => 'thewire',
		'owner_guid' => $user_entity->guid,
		'limit' => 0
	);
	$wires = elgg_get_entities($options);
	$user['wires'] = count($wires);

	$options = array(
		'type' => 'object',
		'subtype' => 'blog',
		'owner_guid' => $user_entity->guid,
		'limit' => 0
	);
	$blogs = elgg_get_entities($options);
	$user['blogs'] = count($blogs);

	$colleagues = $user_entity->getFriends(array('limit' => 0));
	$user['colleagues'] = count($colleagues);

	/*
	$groupObj = elgg_list_entities_from_relationship(array(
	    'relationship'=> 'member', 
	    'relationship_guid'=> $user_entity->guid, 
	    'inverse_relationship'=> FALSE, 
	    'type'=> 'group', 
	    'limit'=> 0
	));
	$groups = json_decode($groupObj);
	foreach($groups as $object){
		$group = get_entity($object->guid);
		$object->iconURL = $group->geticon();
		$object->count = $group->getMembers(array('count' => true));
	}
	$user['groups'] = $groups;

	$all_activity = elgg_list_river(array(
		'subject_guid' => $user_entity->guid,
		'distinct' => false,
		'limit' => 10,
		'offset' => 0
	));

	$activity = json_decode($all_activity);
	foreach( $activity as $event ){
		$subject = get_user($event->subject_guid);
		$object = get_entity($event->object_guid);
		$event->userDetails = get_user_block($event->subject_guid);

		if( $object instanceof ElggUser ){
			$event->object = get_user_block($event->object_guid);
			$event->object['type'] = 'user';
		} else if( $object instanceof ElggWire ){
			$event->object['type'] = 'wire';
			$event->object['wire'] = $object->description;
		} else if( $object instanceof ElggGroup ){
			$event->object['type'] = 'group';
			$event->object['name'] = $object->name;
		} else if( $object instanceof ElggDiscussionReply ){
			$event->object['type'] = 'discussion-reply';
			$original_discussion = get_entity($object->container_guid);
			$event->object['name'] = $original_discussion->title;
			$event->object['description'] = $object->description;
		} else if( $object instanceof ElggFile ){
			$event->object['type'] = 'file';
			$event->object['name'] = $object->title;
			$event->object['description'] = $object->description;
		} else if( $object instanceof ElggObject ){
			$event->object['type'] = 'discussion-add';
			$group = get_entity($object->container_guid);
			$event->object['name'] = $object->title;
			$event->object['description'] = $object->description;
			$event->object['group'] = $group->name;
		} else {
			//@TODO handle any unknown events
		}
	}
	$user['activity'] = $activity;
	*/

	return $user;
}

function get_user_exists( $user ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
	return ( $user_entity instanceof ElggUser );
}

function get_user_activity( $user, $limit, $offset ){ 
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$all_activity = elgg_list_river(array(
		'subject_guid' => $user_entity->guid,
		'distinct' => false,
		'limit' => $limit,
		'offset' => $offset
	));

	$activity = json_decode($all_activity);
	foreach( $activity as $event ){
		$subject = get_user($event->subject_guid);
		$object = get_entity($event->object_guid);
		$event->userDetails = get_user_block($event->subject_guid);

		if( $object instanceof ElggUser ){
			$event->object = get_user_block($event->object_guid);
			$event->object['type'] = 'user';
		} else if( $object instanceof ElggWire ){
			$event->object['type'] = 'wire';
			$event->object['wire'] = wire_filter($object->description);
					
			$thread_id = $object->wire_thread;
			$reshare = $object->getEntitiesFromRelationship(array("relationship" => "reshare", "limit" => 1))[0];

			$url = "";
			if( !empty( $reshare ) ){
				$url = $reshare->getURL();
			}

			$text = "";
			if ( !empty($reshare->title) ) {
				$text = $reshare->title;
			} else if ( !empty($reshare->name) ) {
				$text = $reshare->name;
			} else if ( !empty($reshare->description) ) {
				$text = elgg_get_excerpt($reshare->description, 140);
			}

			$event->shareURL = $url;
			$event->shareText = $text;
		} else if( $object instanceof ElggGroup ){
			$event->object['type'] = 'group';
			$event->object['name'] = $object->name;
		} else if( $object instanceof ElggDiscussionReply ){
			$event->object['type'] = 'discussion-reply';
			$original_discussion = get_entity($object->container_guid);
			$event->object['name'] = $original_discussion->title;
			$event->object['description'] = $object->description;
		} else if( $object instanceof ElggFile ){
			$event->object['type'] = 'file';
			$event->object['name'] = $object->title;
			$event->object['description'] = $object->description;
		} else if( $object instanceof ElggObject ){
			$event->object['type'] = 'discussion-add';
			$event->object['name'] = ( $object->title ) ? $object->title : $object->name;
			$event->object['description'] = $object->description;

			$other = get_entity($object->container_guid);
			if( $other instanceof ElggGroup ){
				if( !isset($event->object['type']) )
					$event->object['name'] = ( $other->title ) ? $other->title : $other->name;
			} else {
				if( !isset($event->object['type']) )
					$event->object['name'] = ( $other->title ) ? $other->title : $other->name;
			}
		} else {
			//@TODO handle any unknown events
			$event->object['name'] = $object->title;
			$event->object['description'] = $object->description;
		}
	}

	return $activity;
}

function get_user_groups( $user ){ 
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$all_groups = elgg_list_entities_from_relationship(array(
	    'relationship' => 'member', 
	    'relationship_guid' => $user_entity->guid, 
	    'inverse_relationship' => FALSE, 
	    'type' => 'group', 
	    'limit' => 0
	));

	$groups = json_decode($all_groups);
	foreach($groups as $group){
		$groupObj = get_entity($group->guid);
		$group->iconURL = $groupObj->geticon();
		$group->count = $groupObj->getMembers(array('count' => true));
	}

	return $groups;
}

function get_user_posts( $user, $type, $limit, $offset ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";
	
	elgg_set_ignore_access(true);

	switch( $type ){
    	case "blog":
	        $blogs = elgg_list_entities(array(
	        	'type' => 'object',
				'subtype' => 'blog',
				'order_by' => 'e.last_action desc',
				'full_view' => false,
				'no_results' => elgg_echo('blog:none'),
				'preload_owners' => true,
				'preload_containers' => true,
				'limit' => $limit,
				'offset' => $offset
			));
			$data = json_decode($blogs);
			foreach( $data as $blog ){
				$likes = elgg_get_annotations(array(
					'guid' => $blog->guid,
					'annotation_name' => 'likes'
				));
				$blog->likes = count($likes);

				$liked = elgg_get_annotations(array(
					'guid' => $blog->guid,
					'annotation_owner_guid' => $user_entity->guid,
					'annotation_name' => 'likes'
				));
				$blog->liked = count($liked) > 0;

				$blog->userDetails = get_user_block($blog->owner_guid);

				$group = get_entity($blog->container_guid);
				$blog->group = $group->name;
				$blog->groupURL = $group->getURL();
			}
	        break;
	    case "wire":
	        $wires = elgg_list_entities(array(
	        	'type' => 'object',
				'subtype' => 'thewire',
				'order_by' => 'e.last_action desc',
				'full_view' => false,
				'no_results' => elgg_echo('wire:none'),
				'preload_owners' => true,
				'preload_containers' => true,
				'limit' => $limit,
				'offset' => $offset
			));
			$data = json_decode($wires);
			foreach( $data as $wire ){
				$wire_post = get_entity($wire->guid);
				$thread_id = $wire_post->wire_thread;

				$reshare = $wire_post->getEntitiesFromRelationship(array("relationship" => "reshare", "limit" => 1))[0];

				$url = "";
				if( !empty( $reshare ) ){
					$url = $reshare->getURL();
				}

				$text = "";
				if ( !empty($reshare->title) ) {
					$text = $reshare->title;
				} else if ( !empty($reshare->name) ) {
					$text = $reshare->name;
				} else if ( !empty($reshare->description) ) {
					$text = elgg_get_excerpt($reshare->description, 140);
				}

				$wire->shareURL = $url;
				$wire->shareText = $text;

				$likes = elgg_get_annotations(array(
					'guid' => $wire->guid,
					'annotation_name' => 'likes'
				));
				$wire->likes = count($likes);

				$liked = elgg_get_annotations(array(
					'guid' => $wire->guid,
					'annotation_owner_guid' => $user_entity->guid,
					'annotation_name' => 'likes'
				));
				$wire->liked = count($liked) > 0;

				$replied = elgg_get_entities_from_metadata(array(
					"metadata_name" => "wire_thread",
					"metadata_value" => $thread_id,
					"type" => "object",
					"subtype" => "thewire",
					'owner_guid' => $user_entity->guid
				));
				$wire->replied = count($replied) > 0;

				$wire->userDetails = get_user_block($wire->owner_guid);
				$wire->description = wire_filter($wire->description);
			}
	        break;
	    case "discussion":
	    	$discussions = elgg_list_entities(array(
	        	'type' => 'object',
				'subtype' => 'groupforumtopic',
				'order_by' => 'e.last_action desc',
				'full_view' => false,
				'no_results' => elgg_echo('discussion:none'),
				'preload_owners' => true,
				'preload_containers' => true,
				'limit' => $limit,
				'offset' => $offset
			));
			$data = json_decode($discussions);
			foreach( $data as $discussion ){
				$likes = elgg_get_annotations(array(
					'guid' => $discussion->guid,
					'annotation_name' => 'likes'
				));
				$discussion->likes = count($likes);

				$liked = elgg_get_annotations(array(
					'guid' => $discussion->guid,
					'annotation_owner_guid' => $user_entity->guid,
					'annotation_name' => 'likes'
				));
				$discussion->liked = count($liked) > 0;
				
				$discussion->userDetails = get_user_block($discussion->owner_guid);
			}
	        break;
	    case "newsfeed":
			$db_prefix = elgg_get_config('dbprefix');
		    
		    if( $user_entity ){
		        // check if user exists and has friends or groups
		        $hasfriends = $user_entity->getFriends();
		        $hasgroups = $user_entity->getGroups();
		        if( $hasgroups ){
		            // loop through group guids
		            $groups = $user_entity->getGroups(array('limit'=>0));
		            $group_guids = array();
		            foreach( $groups as $group ){
		                $group_guids[] = $group->getGUID();
		            }
		        }
		    }

		    $actionTypes = array('comment', 'create', 'join', 'update', 'friend', 'reply');

		    if( !$hasgroups && !$hasfriends ){
		        // no friends and no groups :(
		        $activity = '';
		    } else if( !$hasgroups && $hasfriends ){
		        // has friends but no groups
		        $optionsf['relationship_guid'] = $user_entity->guid;
		        $optionsf['relationship'] = 'friend';
		        $optionsf['pagination'] = true;

		        // turn off friend connections
		        // remove friend connections from action types
		        // load user's preference
		        $filteredItems = array($user_entity->colleagueNotif);
		        // filter out preference
		        $optionsf['action_types'] = array_diff( $actionTypes, $filteredItems );

		        $activity = json_decode(newsfeed_list_river($optionsf));
		    } else if( !$hasfriends && $hasgroups ){
		        // if no friends but groups
		        $guids_in = implode(',', array_unique(array_filter($group_guids)));
		        
		        // display created content and replies and comments
		        $optionsg['wheres'] = array("( oe.container_guid IN({$guids_in}) OR te.container_guid IN({$guids_in}) )");
		        $optionsg['pagination'] = true;
		        $activity = json_decode(newsfeed_list_river($optionsg));
		    } else {
		        // if friends and groups :3
		        // turn off friend connections
		        // remove friend connections from action types
		        // load user's preference
		        $filteredItems = array($user_entity->colleagueNotif);
		        // filter out preference
		        $optionsfg['action_types'] = array_diff( $actionTypes, $filteredItems );

		        $guids_in = implode(',', array_unique(array_filter($group_guids)));

		        // Groups + Friends activity query
		        // This query grabs new created content and comments and replies in the groups the user is a member of *** te.container_guid grabs comments and replies
		        $optionsfg['wheres'] = array(
		    		"( oe.container_guid IN({$guids_in})
		         	OR te.container_guid IN({$guids_in}) )
		        	OR rv.subject_guid IN (SELECT guid_two FROM {$db_prefix}entity_relationships WHERE guid_one=$user_entity->guid AND relationship='friend')"
		        );
		        $optionsfg['pagination'] = true;
		        $activity = json_decode(newsfeed_list_river($optionsfg));
		    }

		    foreach( $activity as $event ){
				$subject = get_user($event->subject_guid);
				$object = get_entity($event->object_guid);
				$event->userDetails = get_user_block($event->subject_guid);

				$likes = elgg_get_annotations(array(
					'guid' => $event->object_guid,
					'annotation_name' => 'likes'
				));
				$event->likes = count($likes);

				$liked = elgg_get_annotations(array(
					'guid' => $event->object_guid,
					'annotation_owner_guid' => $user_entity->guid,
					'annotation_name' => 'likes'
				));
				$event->liked = count($liked) > 0;

				if( $object instanceof ElggUser ){
					$event->object = get_user_block($event->object_guid);
					$event->object['type'] = 'user';
				} else if( $object instanceof ElggWire ){
					$event->object['type'] = 'wire';
					$event->object['wire'] = wire_filter($object->description);
					
					$thread_id = $object->wire_thread;
					$reshare = $object->getEntitiesFromRelationship(array("relationship" => "reshare", "limit" => 1))[0];

					$url = "";
					if( !empty( $reshare ) ){
						$url = $reshare->getURL();
					}

					$text = "";
					if ( !empty($reshare->title) ) {
						$text = $reshare->title;
					} else if ( !empty($reshare->name) ) {
						$text = $reshare->name;
					} else if ( !empty($reshare->description) ) {
						$text = elgg_get_excerpt($reshare->description, 140);
					}

					$event->shareURL = $url;
					$event->shareText = $text;
				} else if( $object instanceof ElggGroup ){
					$event->object['type'] = 'group';
					$event->object['name'] = $object->name;
					$event->object['url'] = $object->getURL();
				} else if( $object instanceof ElggDiscussionReply ){
					$event->object['type'] = 'discussion-reply';
					$original_discussion = get_entity($object->container_guid);
					$event->object['name'] = $original_discussion->title;
					$event->object['description'] = $object->description;
					$event->object['url'] = $original_discussion->getURL();
				} else if( $object instanceof ElggFile ){
					$event->object['type'] = 'file';
					$event->object['name'] = $object->title;
					$event->object['description'] = $object->description;
					$event->object['url'] = $object->getURL();
				} else if( $object instanceof ElggObject ){
					$event->object['type'] = 'discussion-add';

					$name = ( $object->title ) ? $object->title : $object->name;
					if( empty(trim($name)) ){
						$otherEntity = get_entity($object->container_guid);
						$name = ( $otherEntity->title ) ? $otherEntity->title : $otherEntity->name;
					}
					$event->object['name'] = $name;
					$event->object['url'] = $object->getURL();

					$event->object['description'] = $object->description;

					$other = get_entity($object->container_guid);
					if( $other instanceof ElggGroup ){
						if( !isset($event->object['type']) )
							$event->object['name'] = ( $other->title ) ? $other->title : $other->name;
					} else {
						if( !isset($event->object['type']) )
							$event->object['name'] = ( $other->title ) ? $other->title : $other->name;
					}
				} else {
					//@TODO handle any unknown events
					$event->object['name'] = $object->title;
					$event->object['description'] = $object->description;
					$event->object['url'] = $object->getURL();
				}
			}

	    	$data = $activity;
	        break;
	    default:
			$data = "Please use either 'blog', 'wire', 'discussion', or 'newsfeed' for the 'type' parameter";
			break;
	}

	return $data;
}

function get_user_colleague_posts( $user, $type, $limit, $offset ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";
	
	elgg_set_ignore_access(true);

	switch( $type ){
    	case "blog":
	        $blogs = elgg_list_entities_from_relationship(array(
	        	'type' => 'object',
				'subtype' => 'blog',
				'order_by' => 'e.last_action desc',
				'full_view' => false,
				'no_results' => elgg_echo('blog:none'),
				'preload_owners' => true,
				'preload_containers' => true,
				'relationship' => 'friend',
				'relationship_guid' => $user_entity->guid,
				'relationship_join_on' => 'container_guid',
				'limit' => $limit,
				'offset' => $offset
			));
			$data = json_decode($blogs);
			foreach( $data as $blog ){
				$likes = elgg_get_annotations(array(
					'guid' => $blog->guid,
					'annotation_name' => 'likes'
				));
				$blog->likes = count($likes);

				$liked = elgg_get_annotations(array(
					'guid' => $blog->guid,
					'annotation_owner_guid' => $user_entity->guid,
					'annotation_name' => 'likes'
				));
				$blog->liked = count($liked) > 0;

				$blog->userDetails = get_user_block($blog->owner_guid);
			}
	        break;
	    case "wire":
	        $wires = elgg_list_entities_from_relationship(array(
	        	'type' => 'object',
				'subtype' => 'thewire',
				'order_by' => 'e.last_action desc',
				'full_view' => false,
				'no_results' => elgg_echo('wire:none'),
				'preload_owners' => true,
				'preload_containers' => true,
				'relationship' => 'friend',
				'relationship_guid' => $user_entity->guid,
				'relationship_join_on' => 'container_guid',
				'limit' => $limit,
				'offset' => $offset
			));
			$data = json_decode($wires);
			foreach( $data as $wire ){
				$wire_post = get_entity($wire->guid);
				$thread_id = $wire_post->wire_thread;

				$reshare = $wire_post->getEntitiesFromRelationship(array("relationship" => "reshare", "limit" => 1))[0];

				$url = "";
				if( !empty( $reshare ) ){
					$url = $reshare->getURL();
				}

				$text = "";
				if ( !empty($reshare->title) ) {
					$text = $reshare->title;
				} else if ( !empty($reshare->name) ) {
					$text = $reshare->name;
				} else if ( !empty($reshare->description) ) {
					$text = elgg_get_excerpt($reshare->description, 140);
				}

				$wire->shareURL = $url;
				$wire->shareText = $text;

				$likes = elgg_get_annotations(array(
					'guid' => $wire->guid,
					'annotation_name' => 'likes'
				));
				$wire->likes = count($likes);

				$liked = elgg_get_annotations(array(
					'guid' => $wire->guid,
					'annotation_owner_guid' => $user_entity->guid,
					'annotation_name' => 'likes'
				));
				$wire->liked = count($liked) > 0;

				$replied = elgg_get_entities_from_metadata(array(
					"metadata_name" => "wire_thread",
					"metadata_value" => $thread_id,
					"type" => "object",
					"subtype" => "thewire",
					'owner_guid' => $user_entity->guid
				));
				$wire->replied = count($replied) > 0;

				$wire->userDetails = get_user_block($wire->owner_guid);
				$wire->description = wire_filter($wire->description);
			}
	        break;
	    case "discussion":
	    	$discussions = elgg_list_entities_from_relationship(array(
	        	'type' => 'object',
				'subtype' => 'groupforumtopic',
				'order_by' => 'e.last_action desc',
				'full_view' => false,
				'no_results' => elgg_echo('discussion:none'),
				'preload_owners' => true,
				'preload_containers' => true,
				'relationship' => 'friend',
				'relationship_guid' => $user_entity->guid,
				'relationship_join_on' => 'container_guid',
				'limit' => $limit,
				'offset' => $offset
			));
			$data = json_decode($discussions);
			foreach( $data as $discussion ){
				$likes = elgg_get_annotations(array(
					'guid' => $discussion->guid,
					'annotation_name' => 'likes'
				));
				$discussion->likes = count($likes);

				$liked = elgg_get_annotations(array(
					'guid' => $discussion->guid,
					'annotation_owner_guid' => $user_entity->guid,
					'annotation_name' => 'likes'
				));
				$discussion->liked = count($liked) > 0;
				
				$discussion->userDetails = get_user_block($discussion->owner_guid);
			}
	        break;
	    case "newsfeed":
			$db_prefix = elgg_get_config('dbprefix');
		    
		    if( $user_entity ){
		        // check if user exists and has friends or groups
		        $hasfriends = $user_entity->getFriends();
		        $hasgroups = $user_entity->getGroups();
		        if( $hasgroups ){
		            // loop through group guids
		            $groups = $user_entity->getGroups(array('limit'=>0));
		            $group_guids = array();
		            foreach( $groups as $group ){
		                $group_guids[] = $group->getGUID();
		            }
		        }
		    }

		    $actionTypes = array('comment', 'create', 'join', 'update', 'friend', 'reply');

		    if( !$hasgroups && !$hasfriends ){
		        // no friends and no groups :(
		        $activity = '';
		    } else if( !$hasgroups && $hasfriends ){
		        // has friends but no groups
		        $optionsf['relationship_guid'] = $user_entity->guid;
		        $optionsf['relationship'] = 'friend';
		        $optionsf['pagination'] = true;

		        // turn off friend connections
		        // remove friend connections from action types
		        // load user's preference
		        $filteredItems = array($user_entity->colleagueNotif);
		        // filter out preference
		        $optionsf['action_types'] = array_diff( $actionTypes, $filteredItems );

		        $activity = json_decode(newsfeed_list_river($optionsf));
		    } else if( !$hasfriends && $hasgroups ){
		        // if no friends but groups
		        $guids_in = implode(',', array_unique(array_filter($group_guids)));
		        
		        // display created content and replies and comments
		        $optionsg['wheres'] = array("( oe.container_guid IN({$guids_in}) OR te.container_guid IN({$guids_in}) )");
		        $optionsg['pagination'] = true;
		        $activity = json_decode(newsfeed_list_river($optionsg));
		    } else {
		        // if friends and groups :3
		        // turn off friend connections
		        // remove friend connections from action types
		        // load user's preference
		        $filteredItems = array($user_entity->colleagueNotif);
		        // filter out preference
		        $optionsfg['action_types'] = array_diff( $actionTypes, $filteredItems );

		        $guids_in = implode(',', array_unique(array_filter($group_guids)));

		        // Groups + Friends activity query
		        // This query grabs new created content and comments and replies in the groups the user is a member of *** te.container_guid grabs comments and replies
		        $optionsfg['wheres'] = array(
		    		"( oe.container_guid IN({$guids_in})
		         	OR te.container_guid IN({$guids_in}) )
		        	OR rv.subject_guid IN (SELECT guid_two FROM {$db_prefix}entity_relationships WHERE guid_one=$user_entity->guid AND relationship='friend')"
		        );
		        $optionsfg['pagination'] = true;
		        $activity = json_decode(newsfeed_list_river($optionsfg));
		    }

		    foreach( $activity as $event ){
				$subject = get_user($event->subject_guid);
				$object = get_entity($event->object_guid);
				$event->userDetails = get_user_block($event->subject_guid);

				if( $object instanceof ElggUser ){
					$event->object = get_user_block($event->object_guid);
					$event->object['type'] = 'user';
				} else if( $object instanceof ElggWire ){
					$event->object['type'] = 'wire';
					$event->object['wire'] = wire_filter($object->description);

					$thread_id = $object->wire_thread;
					$reshare = $object->getEntitiesFromRelationship(array("relationship" => "reshare", "limit" => 1))[0];

					$url = "";
					if( !empty( $reshare ) ){
						$url = $reshare->getURL();
					}

					$text = "";
					if ( !empty($reshare->title) ) {
						$text = $reshare->title;
					} else if ( !empty($reshare->name) ) {
						$text = $reshare->name;
					} else if ( !empty($reshare->description) ) {
						$text = elgg_get_excerpt($reshare->description, 140);
					}

					$event->shareURL = $url;
					$event->shareText = $text;
				} else if( $object instanceof ElggGroup ){
					$event->object['type'] = 'group';
					$event->object['name'] = $object->name;
				} else if( $object instanceof ElggDiscussionReply ){
					$event->object['type'] = 'discussion-reply';
					$original_discussion = get_entity($object->container_guid);
					$event->object['name'] = $original_discussion->title;
					$event->object['description'] = $object->description;
				} else if( $object instanceof ElggFile ){
					$event->object['type'] = 'file';
					$event->object['name'] = $object->title;
					$event->object['description'] = $object->description;
				} else if( $object instanceof ElggObject ){
					$event->object['type'] = 'discussion-add';
					$event->object['name'] = ( $object->title ) ? $object->title : $object->name;
					$event->object['description'] = $object->description;

					$other = get_entity($object->container_guid);
					if( $other instanceof ElggGroup ){
						if( !isset($event->object['type']) )
							$event->object['name'] = ( $other->title ) ? $other->title : $other->name;
					} else {
						if( !isset($event->object['type']) )
							$event->object['name'] = ( $other->title ) ? $other->title : $other->name;
					}
				} else {
					//@TODO handle any unknown events
					$event->object['name'] = $object->title;
					$event->object['description'] = $object->description;
				}
			}

	    	$data = $activity;
	        break;
	    default:
			$data = "Please use either 'blog', 'wire', 'discussion', or 'newsfeed' for the 'type' parameter";
			break;
	}

	return $data;
}
