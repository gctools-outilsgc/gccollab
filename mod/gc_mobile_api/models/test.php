<?php
/*
 * Exposes API endpoints for Opportunity entities
 */

elgg_ws_expose_function(
	"get.opportunitytest",
	"get_opportunity_test",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => true),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Retrieves a opportunity based on user id and opportunity id',
	'POST',
	true,
	false
);

elgg_ws_expose_function(
	"get.opportunitiestest",
	"get_opportunities_test",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"limit" => array('type' => 'int', 'required' => false, 'default' => 10),
		"offset" => array('type' => 'int', 'required' => false, 'default' => 0),
		"filters" => array('type' => 'string', 'required' => false, 'default' => ""),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Retrieves opportunities based on user id',
	'POST',
	true,
	false
);

function get_opportunity_test($user, $guid, $lang)
{
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
	if (!$entity) {
		return "Opportunity was not found. Please try a different GUID";
	}
	if (!elgg_instanceof($entity, 'object', 'mission')) {
		return "Invalid opportunity. Please try a different GUID";
	}



	$opportunities = elgg_list_entities(array(
		'type' => 'object',
		'subtype' => 'mission',
		'guid' => $guid
	));
	$opportunity = json_decode($opportunities)[0];

	$opportunity->title = gc_explode_translation($opportunity->title, $lang);

	$likes = elgg_get_annotations(array(
		'guid' => $opportunity->guid,
		'annotation_name' => 'likes'
	));
	$opportunity->likes = count($likes);

	$liked = elgg_get_annotations(array(
		'guid' => $opportunity->guid,
		'annotation_owner_guid' => $user_entity->guid,
		'annotation_name' => 'likes'
	));
	$opportunity->liked = count($liked) > 0;

	$opportunity->comments = get_entity_comments($opportunity->guid);

	$opportunity->userDetails = get_user_block($opportunity->owner_guid, $lang);
	$opportunity->description = gc_explode_translation($opportunity->description, $lang);

	$opportunityObj = get_entity($opportunity->guid);
	//$opportunity->programArea = elgg_echo('missions:program_area') . ": " . elgg_echo($opportunityObj->program_area); //This should work and translate to user lang but doesnt
	$opportunity->programArea = elgg_echo($opportunityObj->program_area);
	$opportunity->numOpportunities = $opportunityObj->number;
	$opportunity->idealStart = $opportunityObj->start_date;
	$opportunity->idealComplete = $opportunityObj->complete_date;
	$opportunity->deadline = $opportunityObj->deadline;
	$opportunity->oppVirtual = $opportunityObj->remotely;
	$opportunity->oppOnlyIn = $opportunityObj->openess;
	$opportunity->location = elgg_echo($opportunityObj->location);
	$opportunity->security = elgg_echo($opportunityObj->security);
	$opportunity->skills = $opportunityObj->key_skills;
	//$opportunity->languageRequirements =
	//$opportunity->schedulingRequirements = $opportunityObj->;
	//$opportunity->participants = $opportunityObj->;
	//$opportunity->applicants = $opportunityObj->;
	$opportunity->timezone = $opportunityObj->timezone;
	$opportunity->department = $opportunityObj->department;

	//Language metadata
	$unpacked_array = mm_unpack_mission($opportunityObj);
	$unpacked_language = '';
	if (! empty($unpacked_array['lwc_english']) || ! empty($unpacked_array['lwc_french'])) {
  	$unpacked_language .= '<b>' . elgg_echo('missions:written_comprehension') . ': </b>';
  	if (! empty($unpacked_array['lwc_english'])) {
      $unpacked_language .= '<span name="mission-lwc-english">' . elgg_echo('missions:formatted:english', array($unpacked_array['lwc_english'])) . '</span> ';
  	}
  	if (! empty($unpacked_array['lwc_french'])) {
      $unpacked_language .= '<span name="mission-lwc-french">' . elgg_echo('missions:formatted:french', array($unpacked_array['lwc_french'])) . '</span>';
  	}
		$unpacked_language .= '<br>';
	}
	if (! empty($unpacked_array['lwe_english']) || ! empty($unpacked_array['lwe_french'])) {
		$unpacked_language .= '<b>' . elgg_echo('missions:written_expression') . ': </b>';
		if (! empty($unpacked_array['lwe_english'])) {
	  	$unpacked_language .= '<span name="mission-lwe-english">' . elgg_echo('missions:formatted:english', array($unpacked_array['lwe_english'])) . '</span> ';
	 	}
	  if (! empty($unpacked_array['lwe_french'])) {
	  	$unpacked_language .= '<span name="mission-lwe-french">' . elgg_echo('missions:formatted:french', array($unpacked_array['lwe_french'])) . '</span>';
	  }
	  	$unpacked_language .= '<br>';
	}
	if (! empty($unpacked_array['lop_english']) || ! empty($unpacked_array['lop_french'])) {
		$unpacked_language .= '<b>' . elgg_echo('missions:oral_proficiency') . ': </b>';
		if (! empty($unpacked_array['lop_english'])) {
			$unpacked_language .= '<span name="mission-lop-english">' . elgg_echo('missions:formatted:english', array($unpacked_array['lop_english'])) . '</span> ';
		}
		if (! empty($unpacked_array['lop_french'])) {
			$unpacked_language .= '<span name="mission-lop-french">' . elgg_echo('missions:formatted:french', array($unpacked_array['lop_french'])) . '</span>';
		}
		$unpacked_language .= '<br>';
	}
	if (empty($unpacked_language)) {
		$unpacked_language = '<span name="no-languages">' . elgg_echo('missions:none_required') . '</span>';
	}
	$opportunity->languageRequirements = $unpacked_language;

	return $opportunity;
}

function get_opportunities_test($user, $limit, $offset, $filters, $lang)
{
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

	$filter_data = json_decode($filters);
	if (!empty($filter_data)) {
		$params = array(
			'type' => 'object',
			'subtype' => 'mission',
			'limit' => $limit,
			'offset' => $offset
		);

		if ($filter_data->type) {
			$params['metadata_name'] = 'job_type';
			$params['metadata_value'] = $filter_data->type;
		}

		if ($filter_data->name) {
			$db_prefix = elgg_get_config('dbprefix');
			$params['joins'] = array("JOIN {$db_prefix}objects_entity oe ON e.guid = oe.guid");
			$params['wheres'] = array("(oe.title LIKE '%" . $filter_data->name . "%' OR oe.description LIKE '%" . $filter_data->name . "%')");
		}

		if ($filter_data->mine) {
			$all_opportunities = elgg_list_entities_from_relationship($params);
		} else {
			$all_opportunities = elgg_list_entities_from_metadata($params);
		}
	} else {
		$all_opportunities = elgg_list_entities(array(
			'type' => 'object',
			'subtype' => 'mission',
			'limit' => $limit,
			'offset' => $offset
		));
	}

	$opportunities = json_decode($all_opportunities);

	foreach ($opportunities as $opportunity) {
		$opportunity->title = gc_explode_translation($opportunity->title, $lang);

		$likes = elgg_get_annotations(array(
			'guid' => $opportunity->guid,
			'annotation_name' => 'likes'
		));
		$opportunity->likes = count($likes);

		$liked = elgg_get_annotations(array(
			'guid' => $opportunity->guid,
			'annotation_owner_guid' => $user_entity->guid,
			'annotation_name' => 'likes'
		));
		$opportunity->liked = count($liked) > 0;

		$opportunityObj = get_entity($opportunity->guid);
		$opportunity->owner = ($opportunityObj->getOwnerEntity() == $user_entity);
		$opportunity->iconURL = $opportunityObj->getIconURL();

		$opportunity->userDetails = get_user_block($opportunity->owner_guid, $lang);
		$opportunity->description = clean_text(gc_explode_translation($opportunity->description, $lang));
	}

	return $opportunities;
}