<?php

elgg_register_event_handler('init', 'system', 'gccollab_stats_init', 0);

function gccollab_stats_init() {
	elgg_register_page_handler('stats', 'stats_page_handler');
	elgg_register_plugin_hook_handler('public_pages', 'walled_garden', 'gccollab_stats_public_page');

	elgg_ws_expose_function(
        "member.stats",
        "get_member_data",
        array("type" => array('type' => 'string')),
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

function get_member_data($type) {
	$data = array();
	if ($type === 'all') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data[$obj->user_type]++;
		}
	} else if ($type === 'federal') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'federal'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data[$obj->federal]++;
		}
	} else if ($type === 'provincial') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'provincial'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data[$obj->provincial]['total']++;
			$data[$obj->provincial][$obj->ministry]++;
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