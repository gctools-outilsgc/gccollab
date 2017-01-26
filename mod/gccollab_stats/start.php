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

	if ($type === 'all') {
		$users = elgg_get_entities(array(
			'type' => 'user',
			'limit' => 0
		));

		if ($lang == 'fr'){
			$users_types = array('federal' => 'féderal', 'provincial' => 'provincial', 'academic' => 'milieu universitaire', 'student' => 'etudiant', 'public_servant' => 'public_servant');

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
	} 
    return $data;
}