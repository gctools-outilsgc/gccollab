<?php

elgg_register_event_handler('init', 'system', 'gccollab_stats_init', 0);

function gccollab_stats_init() {
	elgg_register_page_handler('stats', 'stats_page_handler');
	elgg_register_plugin_hook_handler('public_pages', 'walled_garden', 'gccollab_stats_public_page');

	elgg_ws_expose_function(
        "member.stats",
        "get_member_data",
        array("type" => array('type' => 'string'), "lang" => array('type' => 'string')),
        'Exposes member data for use with dashboard',
        'GET',
        false,
        false
	);

	elgg_ws_expose_function(
        "site.stats",
        "get_site_data",
        array("type" => array('type' => 'string'), "lang" => array('type' => 'string')),
        'Exposes site data for use with dashboard',
        'GET',
        false,
        false
	);
}

function gccollab_stats_public_page($hook, $handler, $return, $params){
	$pages = array('stats');
	return array_merge($pages, $return);
}

function stats_page_handler($page) {
	$base = elgg_get_plugins_path() . 'gccollab_stats/pages/gccollab_stats';
	require_once "$base/index.php";
	return true;
}

function get_member_data($type, $lang) {
	if(!isset($lang)){ $lang = 'en'; }

	$data = array();
	ini_set("memory_limit", -1);
	elgg_set_ignore_access(true);

	if ($type === 'all') {
		$users = elgg_get_entities(array(
			'type' => 'user',
			'limit' => 0
		));

		if ($lang == 'fr'){
			$users_types = array('federal' => 'féderal', 'academic' => 'milieu universitaire', 'student' => 'étudiant', 'provincial' => 'provincial', 'municipal' => 'municipale', 'international' => 'international', 'ngo' => 'ngo', 'community' => 'collectivité', 'business' => 'entreprise', 'media' => 'média', 'retired' => 'retraité(e)', 'other' => 'autre');

			foreach($users as $key => $obj){
				$data[$users_types[$obj->user_type]]++;
			}
		} else {
			foreach($users as $key => $obj){
				$data[$obj->user_type]++;
			}
		}
	} else if ($type === 'federal') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'federal'),
			),
			'limit' => 0
		));

		if ($lang == 'fr'){
			$deptObj = elgg_get_entities(array(
			   	'type' => 'object',
			   	'subtype' => 'federal_departments',
			));
			$depts = get_entity($deptObj[0]->guid);
			$federal_departments = json_decode($depts->federal_departments_fr, true);

			foreach($users as $key => $obj){
				$data[$federal_departments[$obj->federal]]++;
			}
		} else {
			foreach($users as $key => $obj){
				$data[$obj->federal]++;
			}
		}
	} else if ($type === 'academic') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'academic'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data[$obj->institution]['total']++;
			if($obj->university) $data[$obj->institution][$obj->university]++;
			if($obj->college) $data[$obj->institution][$obj->college]++;
		}
	} else if ($type === 'student') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'student'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data[$obj->institution]['total']++;
			if($obj->university) $data[$obj->institution][$obj->university]++;
			if($obj->college) $data[$obj->institution][$obj->college]++;
		}
	} else if ($type === 'university') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'academic'),
				array('name' => 'institution', 'value' => 'university'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->university]++;
		}
	} else if ($type === 'college') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'academic'),
				array('name' => 'institution', 'value' => 'college'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->college]++;
		}
	} else if ($type === 'provincial') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'provincial'),
			),
			'limit' => 0
		));

		if ($lang == 'fr'){
			$provObj = elgg_get_entities(array(
			   	'type' => 'object',
			   	'subtype' => 'provinces',
			));
			$provs = get_entity($provObj[0]->guid);
			$provincial_departments = json_decode($provs->provinces_fr, true);

			$minObj = elgg_get_entities(array(
			   	'type' => 'object',
			   	'subtype' => 'ministries',
			));
			$mins = get_entity($minObj[0]->guid);
			$ministries = json_decode($mins->ministries_fr, true);

			foreach($users as $key => $obj){
				$data[$provincial_departments[$obj->provincial]]['total']++;
				$data[$provincial_departments[$obj->provincial]][$ministries[$obj->provincial][$obj->ministry]]++;
			}
		} else {
			foreach($users as $key => $obj){
				$data[$obj->provincial]['total']++;
				$data[$obj->provincial][$obj->ministry]++;
			}
		}
	} else if ($type === 'municipal') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'municipal')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->municipal]++;
		}
	} else if ($type === 'international') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'international')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->international]++;
		}
	} else if ($type === 'ngo') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'ngo')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->ngo]++;
		}
	} else if ($type === 'community') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'community')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->community]++;
		}
	} else if ($type === 'business') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'business')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->business]++;
		}
	} else if ($type === 'media') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'media')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->media]++;
		}
	} else if ($type === 'retired') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'retired')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->retired]++;
		}
	} else if ($type === 'other') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'other')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total']++;
			$data[$obj->other]++;
		}
	} 
    return $data;
}

function get_site_data($type, $lang) {
	if(!isset($lang)){ $lang = 'en'; }

	$data = array();
	ini_set("memory_limit", -1);
	elgg_set_ignore_access(true);

	if ($type === 'wireposts') {
		$wireposts = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'thewire',
			'limit' => 0
		));

		foreach($wireposts as $key => $obj){
			$user = get_user($obj->owner_guid);
			$data[] = array($obj->time_created, $obj->description, $user->name);
		}
	} else if ($type === 'blogposts') {
		$blogposts = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'blog',
			'limit' => 0
		));

		foreach($blogposts as $key => $obj){
			$user = get_user($obj->owner_guid);
			$data[] = array($obj->time_created, $obj->title, $obj->description, $user->name);
		}
	} else if ($type === 'comments') {
		$comments = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'comment',
			'limit' => 0
		));

		foreach($comments as $key => $obj){
			$user = get_user($obj->owner_guid);
			$data[] = array($obj->time_created, $obj->description, $user->name);
		}
	} else if ($type === 'groupscreated') {
		$groupscreated = elgg_get_entities(array(
			'type' => 'group',
			'limit' => 0
		));

		foreach($groupscreated as $key => $obj){
			$user = get_user($obj->owner_guid);
			$data[] = array($obj->time_created, $obj->name, $obj->description, $user->name);
		}
	} else if ($type === 'groupsjoined') {
		$dbprefix = elgg_get_config('dbprefix');
		$query = "SELECT * FROM {$dbprefix}entity_relationships WHERE relationship = 'member'";
		$groupsjoined = get_data($query);

		foreach($groupsjoined as $key => $obj){
			$user = get_user($obj->guid_one);
			$group = get_entity($obj->guid_two);
			$data[] = array($obj->time_created, $user->name, $group->name);
		}
	} else if ($type === 'likes') {
		$likes = elgg_get_annotations(array(
			'annotation_names' => array('likes'),
			'limit' => 0
		));

		foreach($likes as $key => $obj){
			$entity = get_entity($obj->entity_guid);
			$user = get_user($obj->owner_guid);
			$user_liked = ($entity->title != "" ? $entity->title : ($entity->name != "" ? $entity->name : $entity->description));
			$data[] = array($obj->time_created, $user->name, $user_liked);
		}
	} else if ($type === 'messages') {
		$messages = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'messages',
			'limit' => 0
		));

		foreach($messages as $key => $obj){
			if($obj->fromId && $obj->fromId !== 1){
				$user = get_user($obj->owner_guid);
				$data[] = array($obj->time_created, $user->name, $obj->title);
			}
		}
	} 
    return $data;
}