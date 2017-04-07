<?php

//global $CONFIG;
//error_log($CONFIG->dbhost.' '.$CONFIG->dbuser.' '.$CONFIG->dbpass.' '.$CONFIG->dbname);

elgg_ws_expose_function("get.profile","get_api_profile", array("id" => array('type' => 'string')),
	'provide user GUID number and all profile information is returned',
               'GET', false, false);

elgg_ws_expose_function(
	"get.user",
	"get_user_data",
	array(
		"profileemail" => array('type' => 'string', 'required' => true),
		"user" => array('type' => 'string', 'required' => false)
	),
	'provides user information based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.useractivity",
	"get_useractivity",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'provides a user\'s activity information based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.usergroups",
	"get_usergroups",
	array(
		"user" => array('type' => 'string', 'required' => true)
	),
	'provides a user\'s group information based on user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.posts",
	"get_user_posts",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"type" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0)
	),
	'provides latest posts based on the post type and the user id',
	'POST',
	true,
	false
);

elgg_ws_expose_function("profile.update","profileUpdate", array("id" => array('type' => 'string'), "data" => array('type'=>'string')),
	'update a user profile based on id passed',
               'POST', true, false);



function get_api_profile($id){
	//global $CONFIG;
	//$string = "User was not found. Please try a different GUID, username, or email address";
	$user_entity = getUserFromID($id);
	if (!$user_entity)
		return "User was not found. Please try a different GUID, username, or email address";
	
	//$user['test'] = $CONFIG->view_types;

	$user['id'] = $user_entity->guid;

	$user['username'] = $user_entity->username;

	//get and store user display name
	$user['displayName'] = $user_entity->name;

	$user['email'] = $user_entity->email;

	//get and store URL for profile
	$user['profileURL'] = $user_entity->getURL();

	//get and store URL of profile avatar
	$user['iconURL'] = $user_entity->geticon();

	
	$user['jobTitle'] = $user_entity->job;

	$user['department'] = $user_entity->department;

	$user['telephone'] = $user_entity->phone;

	$user['mobile'] = $user_entity->mobile;

	$user['Website'] = $user_entity->website;

	if ($user_entity->facebook)
		$user['links']['facebook'] = "http://www.facebook.com/".$user_entity->facebook;
	if($user_entity->google)
		$user['links']['google'] = "http://www.google.com/".$user_entity->google;
	if($user_entity->github)
		$user['links']['github'] = "https://github.com/".$user_entity->github;
	if($user_entity->twitter)
		$user['links']['twitter'] = "https://twitter.com/".$user_entity->twitter;
	if($user_entity->linkedin)
		$user['links']['linkedin'] = "http://ca.linkedin.com/in/".$user_entity->linkedin;
	if($user_entity->pinterest)
		$user['links']['pinterest'] = "http://www.pinterest.com/".$user_entity->pinterest;
	if($user_entity->tumblr)
		$user['links']['tumblr'] = "https://www.tumblr.com/blog/".$user_entity->tumblr;
	if($user_entity->instagram)
		$user['links']['instagram'] = "http://instagram.com/".$user_entity->instagram;
	if($user_entity->flickr)
		$user['links']['flickr'] = "http://flickr.com/".$user_entity->flickr;
	if($user_entity->youtube)
		$user['links']['youtube'] = "http://www.youtube.com/".$user_entity->youtube;

	////////////////////////////////////////////////////////////////////////////////////
	//about me
	////////////////////////////////////////////////////////////////////////
	$aboutMeMetadata = elgg_get_metadata(array('guids'=>array($user['id']),'limit'=>0,'metadata_names'=>array('description')));
	
	if ($aboutMeMetadata[0]->access_id==2)
		$user['about_me'] = $aboutMeMetadata[0]->value;
	
	/////////////////////////////////////////////////////////////////////////////////
	//eductation
	//////////////////////////////////////////////////////////////////////
	$educationEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'education',
		'type' => 'object',
		'limit' => 0
		));
	$i=0;
	foreach ($educationEntity as $school){
		if($school->access_id==2){

			$user['education']['item_'.$i]['school_name'] = $school->school;
			
			$user['education']['item_'.$i]['start_date'] = buildDate($school->startdate, $school->startyear);
			
			if($school->ongoing == "false"){
				$user['education']['item_'.$i]['end_date'] = buildDate($school->enddate,$school->endyear);
			}else{
				$user['education']['item_'.$i]['end_date'] = "present/actuel";
			}
			$user['education']['item_'.$i]['degree'] = $school->degree;
			$user['education']['item_'.$i]['field_of_study'] = $school->field;
			$i++;
		}
	}
	////////////////////////////////////////////////////////
	//experience
	//////////////////////////////////////
	$experienceEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'experience',
		'type' => 'object',
		'limit' => 0
		));
	usort($experienceEntity, "sortDate");
	$i=0;
	foreach ($experienceEntity as $job){
		//$user['job'.$i++] = "test";
		if($job->access_id == 2){
			$jobMetadata = elgg_get_metadata(array(
				'guid' => $job->guid,
				'limit' => 0
				));
			//foreach ($jobMetadata as $data)
			//	$user['job'][$i++][$data->name] = $data->value;

			$user['experience']['item_'.$i]['job_title'] = $job->title;
			$user['experience']['item_'.$i]['organization'] = $job->organization;
			$user['experience']['item_'.$i]['start_date'] = buildDate($job->startdate, $job->startyear);
			if ($job->ongoing == "false"){
				$user['experience']['item_'.$i]['end_date'] = buildDate($job->enddate, $job->endyear);
			}else{
				$user['experience']['item_'.$i]['end_date'] = "present/actuel";
			}
			$user['experience']['item_'.$i]['responsibilities'] = $job->responsibilities;
			//$user['experience']['item_'.$i]['colleagues'] = $job->colleagues;
			$j = 0;
			if (is_array($job->colleagues)){
				foreach($job->colleagues as $friend){
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
			}elseif(!is_null($job->colleagues)){
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
	/////////////////////////////////////////////////////////
	//Skills
	///////////////////////////////////////////////////////
	elgg_set_ignore_access(true);
	if($user_entity->skill_access == ACCESS_PUBLIC)
	$skillsEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'MySkill',
		'type' => 'object',
		'limit' => 0
		));
	$i=0;
	foreach($skillsEntity as $skill){
		$user['skills']['item_'.$i]['skill'] = $skill->title;
		//$user['skills']['item_'.$i]['endorsements'] = $skill->endorsements;
		$j = 0;
		if(is_array($skill->endorsements)){
			foreach($skill->endorsements as $friend){
				$friendEntity = get_user($friend);
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["id"] = $friendEntity->guid; 
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["username"] = $friendEntity->username;
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["displayName"] = $friendEntity->name;
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["profileURL"] = $friendEntity->getURL();
				$user['skills']['item_'.$i]['endorsements']["user_".$j]["iconURL"] = $friendEntity->geticon();
				$j++;
			}
		}elseif(!is_null($skill->endorsements)){
			$friendEntity = get_user($skill->endorsements);
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["id"] = $friendEntity->guid; 
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["username"] = $friendEntity->username;
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["displayName"] = $friendEntity->name;
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["profileURL"] = $friendEntity->getURL();
			$user['skills']['item_'.$i]['endorsements']["user_".$j]["iconURL"] = $friendEntity->geticon();
		}
		$i++;
	}
	elgg_set_ignore_access(false);
	/////////////////////////////////////////////////////////////////////////////////////////
	//Language
	////////////////////////////////////////////////////////////////////
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
	//////////////////////////////////////////////////////////////////////////////////////
	//portfolio
	///////////////////////////////////////////////////////////////////
	$portfolioEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'portfolio',
		'type' => 'object',
		'limit' => 0
		));
	$i=0;
	foreach($portfolioEntity as $portfolio){
		if($grade->access_id == 2){
			$user['portfolio']['item_'.$i]['title'] = $portfolio->title;
			$user['portfolio']['item_'.$i]['link'] = $portfolio->link;
			if($portfolio->datestamped == "on")
				$user['portfolio']['item_'.$i]['date'] = $portfolio->publishdate;
			$user['portfolio']['item_'.$i]['description'] = $portfolio->description;
		}
	}

	$user['dateJoined'] = date("Y-m-d H:i:s",$user_entity->time_created);

	$user['lastActivity'] = date("Y-m-d H:i:s",$user_entity->last_action);

	$user['lastLogin'] = date("Y-m-d H:i:s",$user_entity->last_login);



	return $user;
}

function get_user_data( $profileemail, $id ){
	$user_entity = ( strpos($profileemail, '@') !== FALSE ) ? get_user_by_email($profileemail)[0] : getUserFromID($profileemail);
	if( !$user_entity )
		return "User profile was not found. Please try a different GUID, username, or email address";

	if( $id ){
		$viewer = ( strpos($id, '@') !== FALSE ) ? get_user_by_email($id)[0] : getUserFromID($id);
		
		if( !$viewer )
			return "Viewer profile was not found. Please try a different GUID, username, or email address";
		
		$friends = $viewer->isFriendsWith($user_entity->guid);
	} else {
		$friends = true;
	}

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

	////////////////////////////////////////////////////////////////////////////////////
	//about me
	////////////////////////////////////////////////////////////////////////
	$aboutMeMetadata = elgg_get_metadata(array('guids'=>array($user['id']),'limit'=>0,'metadata_names'=>array('description')));
	
	if( $aboutMeMetadata[0]->access_id == ACCESS_PUBLIC || $aboutMeMetadata[0]->access_id == ACCESS_LOGGED_IN || ($friends && $aboutMeMetadata[0]->access_id == ACCESS_FRIENDS) ){
		$user['about_me'] = $aboutMeMetadata[0]->value;
	}
	
	/////////////////////////////////////////////////////////////////////////////////
	//education
	//////////////////////////////////////////////////////////////////////
	$educationEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'education',
		'type' => 'object',
		'limit' => 0
	));
	$i=0;
	foreach( $educationEntity as $school ){
		if( $school->access_id == ACCESS_PUBLIC || $school->access_id == ACCESS_LOGGED_IN || ($friends && $school->access_id == ACCESS_FRIENDS) ){
			$user['education']['item_'.$i]['school_name'] = $school->school;
			
			$user['education']['item_'.$i]['start_date'] = buildDate($school->startdate, $school->startyear);
			
			if($school->ongoing == "false"){
				$user['education']['item_'.$i]['end_date'] = buildDate($school->enddate,$school->endyear);
			}else{
				$user['education']['item_'.$i]['end_date'] = "present/actuel";
			}
			$user['education']['item_'.$i]['degree'] = $school->degree;
			$user['education']['item_'.$i]['field_of_study'] = $school->field;
			$i++;
		}
	}
	////////////////////////////////////////////////////////
	//experience
	//////////////////////////////////////
	$experienceEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'experience',
		'type' => 'object',
		'limit' => 0
	));
	usort($experienceEntity, "sortDate");
	$i=0;
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
			$user['experience']['item_'.$i]['start_date'] = buildDate($job->startdate, $job->startyear);
			if ($job->ongoing == "false"){
				$user['experience']['item_'.$i]['end_date'] = buildDate($job->enddate, $job->endyear);
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
	/////////////////////////////////////////////////////////
	//Skills
	///////////////////////////////////////////////////////
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
	/////////////////////////////////////////////////////////////////////////////////////////
	//Language
	////////////////////////////////////////////////////////////////////
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
	//////////////////////////////////////////////////////////////////////////////////////
	//portfolio
	///////////////////////////////////////////////////////////////////
	$portfolioEntity = elgg_get_entities(array(
		'owner_guid'=>$user['id'],
		'subtype'=>'portfolio',
		'type' => 'object',
		'limit' => 0
	));
	$i=0;
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
		'owner_guids' => array($user_entity->guid),
		'limit' => 0
	);
	$wires = elgg_get_entities($options);
	$user['wires'] = count($wires);

	$options = array(
		'type' => 'object',
		'subtype' => 'blog',
		'owner_guids' => array($user_entity->guid),
		'limit' => 0
	);
	$blogs = elgg_get_entities($options);
	$user['blogs'] = count($blogs);

	$colleagues = $user_entity->getFriends(array('limit' => 0));
	$user['colleagues'] = count($colleagues);

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

		$num_members = $group->getMembers(array('count' => true));
		$object->count = $num_members;
	}
	$user['groups'] = $groups;

	$activityObj = elgg_list_river(array(
		'subject_guid' => $user_entity->guid,
		'distinct' => false,
		'limit' => 10,
		'offset' => 0
	));

	$activity = json_decode($activityObj);
	foreach($activity as $event){
		$subject = get_user($event->subject_guid);
		$object = get_entity($event->object_guid);
		$event->displayName = $subject->name;
		$event->profileURL = $subject->getURL();
		$event->iconURL = $subject->geticon();

		if( $object instanceof ElggUser ){
			$event->object['type'] = 'user';
			$event->object['name'] = $object->name;
			$event->object['profileURL'] = $object->getURL();
			$event->object['iconURL'] = $object->geticon();
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

	return $user;
}

function get_useractivity( $id, $limit, $offset ){ 
	$user_entity = ( strpos($id, '@') !== FALSE ) ? get_user_by_email($id)[0] : getUserFromID($id);
	if( !$user_entity )
		return "User profile was not found. Please try a different GUID, username, or email address";

	$activity = elgg_list_river(array(
		'subject_guid' => $user_entity->guid,
		'distinct' => false,
		'limit' => $limit,
		'offset' => $offset
	));

	$data = json_decode($activity);
	foreach($data as $event){
		$subject = get_user($event->subject_guid);
		$object = get_entity($event->object_guid);
		$event->displayName = $subject->name;
		$event->profileURL = $subject->getURL();
		$event->iconURL = $subject->geticon();

		if( $object instanceof ElggUser ){
			$event->object['type'] = 'user';
			$event->object['name'] = $object->name;
			$event->object['profileURL'] = $object->getURL();
			$event->object['iconURL'] = $object->geticon();
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

	return $data;
}

function get_usergroups( $id ){ 
	$user_entity = ( strpos($id, '@') !== FALSE ) ? get_user_by_email($id)[0] : getUserFromID($id);
	if( !$user_entity )
		return "User profile was not found. Please try a different GUID, username, or email address";

	$groups = elgg_list_entities_from_relationship(array(
	    'relationship'=> 'member', 
	    'relationship_guid'=> $user_entity->guid, 
	    'inverse_relationship'=> FALSE, 
	    'type'=> 'group', 
	    'limit'=> 0
	));

	$data = json_decode($groups);
	foreach($data as $object){
		$group = get_entity($object->guid);
		$object->iconURL = $group->geticon();

		$num_members = $group->getMembers(array('count' => true));
		$object->count = $num_members;
	}

	return $data;
}

function get_user_posts( $id, $type, $limit, $offset ){
	$user = ( strpos($id, '@') !== FALSE ) ? get_user_by_email($id)[0] : getUserFromID($id);

	if( !$user )
		return "User was not found. Please try a different GUID, username, or email address";
	login($user);

	$db_prefix = elgg_get_config('dbprefix');

	switch( $type ){
    	case "blog":
	        $data = elgg_list_entities(array(
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
			$data = json_decode($data);
			foreach($data as $object){
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

				$owner = get_user($object->owner_guid);
				$object->displayName = $owner->name;
				$object->email = $owner->email;
				$object->profileURL = $owner->getURL();
				$object->iconURL = $owner->geticon();
			}
	        break;
	    case "wire":
	        $data = elgg_list_entities(array(
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
			$data = json_decode($data);
			foreach($data as $object){
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

				$owner = get_user($object->owner_guid);
				$object->displayName = $owner->name;
				$object->email = $owner->email;
				$object->profileURL = $owner->getURL();
				$object->iconURL = $owner->geticon();
			}
	        break;
	    case "discussion":
	    	$data = elgg_list_entities(array(
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
			$data = json_decode($data);
			foreach($data as $object){
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
				
				$owner = get_user($object->owner_guid);
				$object->displayName = $owner->name;
				$object->email = $owner->email;
				$object->profileURL = $owner->getURL();
				$object->iconURL = $owner->geticon();
			}
	        break;
	    case "newsfeed":
		    if( $user ){
		        //check if user exists and has friends or groups
		        $hasfriends = $user->getFriends();
		        $hasgroups = $user->getGroups();
		        if( $hasgroups ){
		            //loop through group guids
		            $groups = $user->getGroups(array('limit'=>0,)); //increased limit from 10 groups to all
		            $group_guids = array();
		            foreach( $groups as $group ){
		                $group_guids[] = $group->getGUID();
		            }
		        }
		    }

		    $actionTypes = array('comment', 'create', 'join', 'update', 'friend', 'reply');

		    if( !$hasgroups && !$hasfriends ){
		        //no friends and no groups :(
		        $activity = '';
		    } else if( !$hasgroups && $hasfriends ){
		        //has friends but no groups
		        $optionsf['relationship_guid'] = elgg_get_logged_in_user_guid();
		        $optionsf['relationship'] = 'friend';
		        $optionsf['pagination'] = true;

		        //turn off friend connections
		        //remove friend connections from action types
		        //load user's preference
		        $filteredItems = array($user->colleagueNotif);
		        //filter out preference
		        $optionsf['action_types'] = array_diff( $actionTypes, $filteredItems );

		        $activity = json_decode(newsfeed_list_river($optionsf));
		    } else if( !$hasfriends && $hasgroups ){
		        //if no friends but groups
		        $guids_in = implode(',', array_unique(array_filter($group_guids)));
		        
		        //display created content and replies and comments
		        $optionsg['wheres'] = array("( oe.container_guid IN({$guids_in})
		         OR te.container_guid IN({$guids_in}) )");
		        $optionsg['pagination'] = true;
		        $activity = json_decode(newsfeed_list_river($optionsg));
		    } else {
		        //if friends and groups :3
		        //turn off friend connections
		        //remove friend connections from action types
		        //load user's preference
		        $filteredItems = array($user->colleagueNotif);
		        //filter out preference
		        $optionsfg['action_types'] = array_diff( $actionTypes, $filteredItems );

		        $guids_in = implode(',', array_unique(array_filter($group_guids)));
		        
		        //Groups + Friends activity query
		        //This query grabs new created content and comments and replies in the groups the user is a member of *** te.container_guid grabs comments and replies
		        $optionsfg['wheres'] = array(
		    "( oe.container_guid IN({$guids_in})
		         OR te.container_guid IN({$guids_in}) )
		        OR rv.subject_guid IN (SELECT guid_two FROM {$db_prefix}entity_relationships WHERE guid_one=$user->guid AND relationship='friend')
		        ");
		        $optionsfg['pagination'] = true;
		        $activity = json_decode(newsfeed_list_river($optionsfg));
		    }

		    foreach($activity as $event){
				$subject = get_user($event->subject_guid);
				$object = get_entity($event->object_guid);
				$event->displayName = $subject->name;
				$event->profileURL = $subject->getURL();
				$event->iconURL = $subject->geticon();

				if( $object instanceof ElggUser ){
					$event->object['type'] = 'user';
					$event->object['name'] = $object->name;
					$event->object['profileURL'] = $object->getURL();
					$event->object['iconURL'] = $object->geticon();
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

	    	$data = $activity;
	        break;
	    default:
			$data = "Please use either 'blog', 'wire', 'discussion', or 'newsfeed' for the 'type' parameter";
			break;
	}

	// logout();
	return $data;
}

function profileUpdate($id, $data){
	global $CONFIG;
	$response['error'] = 0;
	$user_entity = getUserFromID($id);
	if (!$user_entity){
		$response['error'] = 1;
		$response['message'] = 'Invalid user id, username, or email';
		return $response;
		//return "Not a valid user";
	}
	
	if ($data == ''){
		$response['error'] = 2;
		$response['message'] = 'data must be a string representing a JSON object.';
		return $response;
	}
	$userDataObj = json_decode($data, true);
	if (json_last_error() !== 0){
		$response['error'] = 2;
		$response['message'] = 'invalid JSON - data was unable to be parsed';
		return $response;
		//return "invalid JSON format of data";
	}
	
	//error_log(json_encode($userDataObj));
	/*
{ 
	"name": {
		"firstName": "Troy",
		"lastName": "Lawson"
	},
	"title": {
		"en": "GCconnex King",
		"fr": "le King"
	},
	"classification": {
		"group": "CS",
		"level": "03"
	},
	"department":{
		"en": "Treasury Board of Canada Secretariat",
		"fr":	"Secrétariat Conseil du Trésor du Canada"
	},
	"branch":{
		"en": "Information Management and Technology Directorate",
		"fr": "Direction générale de la gestion d'information et de la technologie"
	},
	"sector":{
		"en": "Corporate Services Sector",
		"fr": "Secteur des services ministériels"
	},
	"location":{
		"en": {
			"street": "140 O'Connor St",
			"city": "Ottawa",
			"province": "Ontario",
			"postalCode": "K1A 0R5",
			"country": "Canada",
			"building": "L'Esplanade Laurier",
			"floor": "6",
			"officeNum": "06062"
		},
		"fr": {
			"street": "140, rue O'Connor",
			"city": "Ottawa",
			"province": "Ontario",
			"postalCode": "K1A 0R5",
			"country": "Canada",
			"building": "L'Esplanade Laurier",
			"floor": "6",
			"officeNum": "06062"			
		}
	},
	"phone": "613-979-0315",
	"mobile": "613-979-0315",
	"email": "Troy.Lawson@tbs-sct.gc.ca",
	"secondLanguage": {
		"firstLang": "en",
		"secondLang": {
			"lang": "fr",
			"writtenComp": {
				"level": "B",
				"expire": "2016-12-29"
			},
			"writtenExpression": {
				"level": "C",
				"expire": "2016-12-29"
			},
			"oral": {
				"level": "B",
				"expire": "2016-12-29"
			}
			
		}
	}
}
	*
	*
	*/
	foreach ($userDataObj as $field => $value){
		//error_log('in loop');
		switch($field){
			case 'name':
			elgg_set_ignore_access(true);
			
				//error_log(json_encode($value));
				$nameData = json_decode(json_encode($value), true);

				if (!isset($nameData["firstName"])||!isset($nameData["lastName"])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing first or last name';
						return $response;

				}
				if (!isset($nameData["firstName"])&&!isset($nameData["lastName"])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing first or last name';
						return $response;

				}

				$name = $nameData["firstName"].' '.$nameData["lastName"];
				//error_log($name);
				//$user_entity->set('name', $name);
				$owner = get_entity($id);
				if (elgg_strlen($name) > 50) {
					register_error(elgg_echo('user:name:fail'));

				} elseif ($owner->name != $name) {
										
					$user=get_user($user_entity->guid);
					$user->name= $name;
					$user_entity->save();
					
				}
				elgg_set_ignore_access(false);
				break;
			case 'title':
				
				$titleData = json_decode(json_encode($value), true);
				if (!isset($titleData['fr'])||!isset($titleData['en'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing french or english title';
						return $response;

				}
				if (!isset($titleData['fr'])&&!isset($titleData['en'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing french and english title';
						return $response;

				}
				if ($user_entity->language === 'fr'){
					$user_entity->set('job', $titleData['fr'].' / '.$titleData['en']);
				}
				else{
					$user_entity->set('job', $titleData['en'].' / '.$titleData['fr']);
				}
				
				break;
			case 'classification':
				//error_log(json_encode($value));
				$classificationData = json_decode(json_encode($value), true);
				if (!isset($classificationData['group'])||!isset($classificationData['level'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing classification group or level';
						return $response;

				}
				if (!isset($classificationData['group'])&&!isset($classificationData['level'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing classification group and level';
						return $response;

				}
				$user_entity->set('classification', json_encode($value));
				break;
			case 'department':
				$deptData = json_decode(json_encode($value), true);
				if (!isset($deptData['fr'])||!isset($deptData['en'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing french or english department';
						return $response;

				}
				if (!isset($deptData['fr'])&&!isset($deptData['en'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - department format';
						return $response;

				}

				$obj = elgg_get_entities(array(
   					'type' => 'object',
   					'subtype' => 'dept_list',
   					'owner_guid' => elgg_get_logged_in_user_guid()
				));
				$deptListEn = json_decode($obj[0]->deptsEn, true);
				$provinces = array();
				$provinces['pov-alb'] = 'Government of Alberta';
				$provinces['pov-bc'] = 'Government of British Columbia';
				$provinces['pov-man'] = 'Government of Manitoba';
				$provinces['pov-nb'] = 'Government of New Brunswick';
				$provinces['pov-nfl'] = 'Government of Newfoundland and Labrador';
				$provinces['pov-ns'] = 'Government of Nova Scotia';
				$provinces['pov-nwt'] = 'Government of Northwest Territories';
				$provinces['pov-nun'] = 'Government of Nunavut';
				$provinces['pov-ont'] = 'Government of Ontario';
				$provinces['pov-pei'] = 'Government of Prince Edward Island';
				$provinces['pov-que'] = 'Government of Quebec';
				$provinces['pov-sask'] = 'Government of Saskatchewan';
				$provinces['pov-yuk'] = 'Government of Yukon';
				$deptAndProvincesEn = array_merge($deptListEn,$provinces);


				$deptListFr = json_decode($obj[0]->deptsFr, true);
				$provinces = array();
				$provinces['pov-alb'] = 'Government of Alberta';
				$provinces['pov-bc'] = 'Government of British Columbia';
				$provinces['pov-man'] = 'Government of Manitoba';
				$provinces['pov-nb'] = 'Government of New Brunswick';
				$provinces['pov-nfl'] = 'Government of Newfoundland and Labrador';
				$provinces['pov-ns'] = 'Government of Nova Scotia';
				$provinces['pov-nwt'] = 'Government of Northwest Territories';
				$provinces['pov-nun'] = 'Government of Nunavut';
				$provinces['pov-ont'] = 'Government of Ontario';
				$provinces['pov-pei'] = 'Government of Prince Edward Island';
				$provinces['pov-que'] = 'Government of Quebec';
				$provinces['pov-sask'] = 'Government of Saskatchewan';
				$provinces['pov-yuk'] = 'Government of Yukon';
				$deptAndProvincesFr = array_merge($deptListFr,$provinces);

				if(!in_array($deptData['en'], $deptAndProvincesEn)){
						$response['error'] = 5;
						$response['message'] = 'invalid english department name. valid names: '.json_encode($deptAndProvincesEn);
						return $response;
				}

				if(!in_array($deptData['fr'], $deptAndProvincesFr)){
						$response['error'] = 5;
						$response['message'] = 'invalid french department name. valid names: '.json_encode($deptAndProvincesFr);
						return $response;
				}
				//error_log(json_encode($value));
				
				if ($user_entity->language === 'fr'){
					$user_entity->set('department', $deptData['fr'].' / '.$deptData['en']);
				}
				else{
					$user_entity->set('department', $deptData['en'].' / '.$deptData['fr']);
				}


				break;
			case 'branch':
				$branchData = json_decode(json_encode($value), true);
				if (!isset($branchData['en'])||!isset($branchData['fr'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing english or french branch name';
						return $response;

				}
				if (!isset($branchData['en'])&&!isset($branchData['fr'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing english and french branch name';
						return $response;

				}
				$user_entity->set('branch', json_encode($value));
				break;
			case 'sector':
				$sectorData = json_decode(json_encode($value), true);
				if (!isset($sectorData['en'])||!isset($sectorData['fr'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing english or french sector name';
						return $response;

				}
				if (!isset($sectorData['en'])&&!isset($sectorData['fr'])){
						$response['error'] = 4;
						$response['message'] = 'invalid data format - missing english and french sector name';
						return $response;

				}
				$user_entity->set('sector', json_encode($value));
				break;
			case 'location':
				if (!isset($value['en'])){
						$response['error'] = 4;
						$response['message'] = 'missing english location data';
						return $response;

				}
				$locationData = json_decode(json_encode($value['en']), true);
				if(!isset($locationData['street'])||!isset($locationData['city'])||!isset($locationData['province'])||!isset($locationData['postalCode'])||!isset($locationData['country'])||!isset($locationData['building'])||!isset($locationData['floor'])||!isset($locationData['officeNum'])){
						$response['error'] = 4;
						$response['message'] = 'missing location data';
						return $response;
				}
				if(!isset($locationData['street'])&&!isset($locationData['city'])&&!isset($locationData['province'])&&!isset($locationData['postalCode'])&&!isset($locationData['country'])&&!isset($locationData['building'])&&!isset($locationData['floor'])&&!isset($locationData['officeNum'])){
						$response['error'] = 4;
						$response['message'] = 'invalid location data';
						return $response;
				}
				if (!isset($value['fr'])){
						$response['error'] = 4;
						$response['message'] = 'missing french location data';
						return $response;

				}
				$locationData = json_decode(json_encode($value['fr']), true);
				if(!isset($locationData['street'])||!isset($locationData['city'])||!isset($locationData['province'])||!isset($locationData['postalCode'])||!isset($locationData['country'])||!isset($locationData['building'])||!isset($locationData['floor'])||!isset($locationData['officeNum'])){
						$response['error'] = 4;
						$response['message'] = 'missing location data';
						return $response;
				}
				if(!isset($locationData['street'])&&!isset($locationData['city'])&&!isset($locationData['province'])&&!isset($locationData['postalCode'])&&!isset($locationData['country'])&&!isset($locationData['building'])&&!isset($locationData['floor'])&&!isset($locationData['officeNum'])){
						$response['error'] = 4;
						$response['message'] = 'invalid location data';
						return $response;
				}
				$user_entity->set('addressString', json_encode($value["en"]));
				$user_entity->set('addressStringFr', json_encode($value["fr"]));
				break;
			case 'phone':
				
				$user_entity->set('phone', $value);
				break;
			case 'mobile':
				
				$user_entity->set('mobile', $value);
				break;
			case 'email':
				
				elgg_set_ignore_access(true);
				$connection = mysqli_connect($CONFIG->dbhost, $CONFIG->dbuser, $CONFIG->dbpass, $CONFIG->dbname)or die(mysqli_error($connection));
				//error_log($CONFIG->dbhost.' '.$CONFIG->dbuser.' '.$CONFIG->dbpass.' '.$CONFIG->dbname);
				mysqli_select_db($connection,$CONFIG->dbname);
				$emaildomain = explode('@',$value);
				$query = "SELECT count(*) AS num FROM email_extensions WHERE ext ='".$emaildomain[1]."'";
			
				$result = mysqli_query($connection, $query)or die(mysqli_error($connection));
				$result = mysqli_fetch_array($result);
		
				$emailgc = explode('.',$emaildomain[1]);
				$gcca = $emailgc[count($emailgc) - 2] .".".$emailgc[count($emailgc) - 1];
		
				mysqli_close($connection);

				$resulting_error = "";

				//if ($toc[0] != 1)
				//{
				//throw new RegistrationException(elgg_echo('gcRegister:toc_error'));
				//	$resulting_error .= elgg_echo('gcRegister:toc_error').'<br/>';
				//}
				//error_log('num - '.is_null($result));
				// if domain doesn't exist in database, check if it's a gc.ca domain
				if ($result['num'][0] <= 0) 
				{
					if ($gcca !== 'gc.ca')
						//throw new RegistrationException(elgg_echo('gcRegister:email_error'));
						$resulting_error .= elgg_echo('gcRegister:invalid_email');
			
				}


				if ($resulting_error !== "")
				{
					//throw new RegistrationException($resulting_error);
					///error_log($resulting_error);
						$response['error'] = 3;
						$response['message'] = 'invalid email or email domain - must be a valid Government of Canada email address';
						return $response;
				}
				$user_entity->set('email', $value);
				$user_entity->save();
				
				elgg_set_ignore_access(false);
				break;
			case 'secondLanguage':
				
				$user_entity->set('english', $value["ENG"]);
				$user_entity->set('french', $value["FRA"]);
            	$user_entity->set('officialLanguage', $value["firstLanguage"]);

				break;
		}
	}
	
	$user_entity->save();
	return 'success';
}

function getUserFromID($id){
	if (is_numeric($id)){
		$user_entity = get_user($id);
		//$string = $user_entity->username;
	}
	else{
		if (strpos($id, '@')){
			$user_entity = get_user_by_email($id);
			if (is_array($user_entity)){
				if (count($user_entity)>1)
					//$string = "Found more than 1 user, please use username or GUID";
					return "Found more than 1 user, please use username or GUID";
				else{
					$user_entity = $user_entity[0];
					//$string = $user_entity->username;
				}
			}
		}else{
			$user_entity = get_user_by_username($id);
			//$string = $user_entity->username;
		}
		
		
	}
	return $user_entity;
}

function buildDate($month, $year){
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
	return $string.$year;

}