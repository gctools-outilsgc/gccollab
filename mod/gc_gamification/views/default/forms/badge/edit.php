<?php

use hypeJunction\GameMechanics\Policy;

$entity = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'file',
	'#label' => elgg_echo('label:hjbadge:icon'),
	'name' => 'icon',
	'value' => (isset($entity->icontime)),
	'required' => (!isset($entity->icontime)),
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('label:hjbadge:title'),
	'name' => 'title',
	'required' => true,
	'value' => elgg_extract('title', $vars, $entity->title),
]);

echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('label:hjbadge:description'),
	'name' => 'description',
	'value' => elgg_extract('description', $vars, $entity->description)
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('label:hjbadge:badge_type'),
	'name' => 'badge_type',
	'value' => elgg_extract('badge_type', $vars, $entity->badge_type),
	'options_values' => Policy::getBadgeTypes(),
]);

$rules = ($entity) ? Policy::getBadgeRules($entity->guid) : null;
echo elgg_view_field([
	'#type' => 'mechanics/rules',
	'#label' => elgg_echo('label:hjbadge:rules'),
	'value' => elgg_extract('rules', $vars, $rules)
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('label:hjbadge:points_required'),
	'name' => 'points_required',
	'value' => (int) elgg_extract('points_required', $vars, $entity->points_required)
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('label:hjbadge:points_cost'),
	'name' => 'points_cost',
	'value' => (int) elgg_extract('points_cost', $vars, $entity->points_cost),
]);

$dependecies = ($entity) ? Policy::getBadgeDependencies($entity->guid) : null;
echo elgg_view_field([
	'#type' => 'mechanics/dependencies',
	'#label' => elgg_echo('label:hjbadge:badges_required'),
	'entity' => $entity,
	'value' => elgg_extract('dependencies', $vars, $dependecies),
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $entity->guid,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'access_id',
	'value' => ($entity) ? $entity->access_id : ACCESS_PUBLIC,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
