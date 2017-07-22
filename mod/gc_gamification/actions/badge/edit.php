<?php

use hypeJunction\GameMechanics\Badge;
use hypeJunction\GameMechanics\BadgeRule;
use hypeJunction\GameMechanics\Policy;
use Symfony\Component\HttpFoundation\File\UploadedFile;

elgg_make_sticky_form('badge/edit');

$guid = get_input('guid');
$title = get_input('title', '');
$access_id = get_input('access_id', ACCESS_PUBLIC);
$description = get_input('description', '');
$badge_type = get_input('badge_type', '');
$rules = get_input('rules', array());
$dependencies = get_input('dependencies', array());
$points_required = get_input('points_required', 0);
$points_cost = get_input('points_cost', 0);

if (!$title) {
	return register_error(elgg_echo('mechanics:badge:edit:error_empty_title'));
}

$icon_uploaded = (!empty($_FILES['icon']['type']) && substr_count($_FILES['icon']['type'], 'image/'));

$entity = get_entity($guid);
$site = elgg_get_site_entity();

if (!elgg_instanceof($entity)) {
	$new = true;

	$entity = new Badge();
	$entity->owner_guid = $site->guid;
	$entity->container_guid = $site->guid;

	$entity->priority = '';
}

$entity->title = $title;
$entity->description = $description;
$entity->access_id = $access_id;

$entity->badge_type = $badge_type;
$entity->points_required = $points_required;
$entity->points_cost = $points_cost;

if (!$entity->save()) {
	return register_error(elgg_echo('mechanics:badge:edit:error'));
}

// Badge icon must be provided for new badges
if ($icon_uploaded) {

	$icon_sizes = elgg_get_config('icon_sizes');

	$prefix = "badge/" . $entity->guid;

	$filehandler = new ElggFile();
	$filehandler->owner_guid = $entity->guid;
	$filehandler->setFilename($prefix . ".jpg");
	$filehandler->open("write");
	$filehandler->write(get_uploaded_file('icon'));
	$filehandler->close();
	$filename = $filehandler->getFilenameOnFilestore();

	$sizes = array('tiny', 'small', 'medium', 'large', 'master');

	$thumbs = array();
	foreach ($sizes as $size) {
		$thumbs[$size] = get_resized_image_from_existing_file(
			$filename,
			$icon_sizes[$size]['w'],
			$icon_sizes[$size]['h'],
			$icon_sizes[$size]['square']
		);
	}

	if ($thumbs['tiny']) { // just checking if resize successful
		$thumb = new ElggFile();
		$thumb->owner_guid = $entity->guid;
		$thumb->setMimeType('image/jpeg');

		foreach ($sizes as $size) {
			$thumb->setFilename("{$prefix}{$size}.jpg");
			$thumb->open("write");
			$thumb->write($thumbs[$size]);
			$thumb->close();
		}

		$entity->icontime = time();
	}
} else {
	return register_error(elgg_echo('mechanics:badge:edit:error_upload'));
}

for ($i = 0; $i < 10; $i++) {

	$guid = (int) $rules['guid'][$i];
	$name = $rules['name'][$i];
	$recurse = (int) $rules['recurse'][$i];

	if ($name && $recurse) {
		$badge_rule = new BadgeRule($guid);
		$badge_rule->owner_guid = $entity->owner_guid;
		$badge_rule->container_guid = $entity->guid;
		$badge_rule->access_id = $entity->access_id;
		$badge_rule->annotation_name = 'badge_rule';
		$badge_rule->annotation_value = $name;
		$badge_rule->recurse = (int) $recurse;
		$badge_rule->save();
	} else if ($guid) {
		$redundant = get_entity($guid);
		$redundant->delete();
	}
}

$current_dependency_guids = array();
$current_dependencies = Policy::getBadgeDependencies($entity->guid);
if ($current_dependencies) {
	foreach ($current_dependencies as $cd) {
		$current_dependency_guids[] = $cd->guid;
	}
}

if (is_array($dependencies)) {
	$future_dependency_guids = array_filter($dependencies);
} else {
	$future_dependency_guids = array();
}

$to_remove = array_diff($current_dependency_guids, $future_dependency_guids);
$to_add = array_diff($future_dependency_guids, $current_dependency_guids);

foreach ($to_remove as $dep_guid) {
	remove_entity_relationship($dep_guid, 'badge_required', $entity->guid);
}

foreach ($to_add as $dep_guid) {
	add_entity_relationship($dep_guid, 'badge_required', $entity->guid);
}

elgg_clear_sticky_form('badge/edit');

if ($new) {
	$msg = elgg_echo('mechanics:badge:create:success');
} else {
	$msg = elgg_echo('mechanics:badge:edit:success');
}

system_message($msg);

forward($entity->getUrl());
