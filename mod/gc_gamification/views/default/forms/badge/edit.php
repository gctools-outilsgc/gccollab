<?php

use hypeJunction\GameMechanics\Policy;

$entity = elgg_extract('entity', $vars);

echo "<div class='form-group'><label for='icon'>" . elgg_echo('label:hjbadge:icon') . "</label>";
echo elgg_view('input/file', array(
    'id' => "icon",
    'name' => "icon",
    'value' => (isset($entity->icontime)),
	'required' => (!isset($entity->icontime))
));
echo "</div>";

echo "<div class='form-group'><label for='title'>" . elgg_echo('label:hjbadge:title') . "</label>";
echo elgg_view('input/text', array(
    'id' => "title",
    'name' => "title",
	'value' => elgg_extract('title', $vars, $entity->title),
    'required' => true
));
echo "</div>";

echo "<div class='form-group'><label for='description'>" . elgg_echo('label:hjbadge:description') . "</label>";
echo elgg_view('input/longtext', array(
    'id' => "description",
    'name' => "description",
	'value' => elgg_extract('description', $vars, $entity->description)
));
echo "</div>";

echo "<div class='form-group'><label for='badge_type'>" . elgg_echo('label:hjbadge:badge_type') . "</label>";
echo elgg_view('input/select', array(
    'id' => "badge_type",
    'name' => "badge_type",
	'value' => elgg_extract('badge_type', $vars, $entity->badge_type),
	'options_values' => Policy::getBadgeTypes()
));
echo "</div>";

$rules = ($entity) ? Policy::getBadgeRules($entity->guid) : null;
echo "<div class='form-group'><label for='rules'>" . elgg_echo('label:hjbadge:rules') . "</label>";
echo elgg_view('mechanics/rules', array(
    'id' => "rules",
    'name' => "rules",
	'value' => elgg_extract('rules', $vars, $rules)
));
echo "</div>";

echo "<div class='form-group'><label for='title'>" . elgg_echo('label:hjbadge:points_required') . "</label>";
echo elgg_view('input/text', array(
    'id' => "points_required",
    'name' => "points_required",
	'value' => (int) elgg_extract('points_required', $vars, $entity->points_required)
));
echo "</div>";

echo "<div class='form-group'><label for='points_cost'>" . elgg_echo('label:hjbadge:points_cost') . "</label>";
echo elgg_view('input/text', array(
    'id' => "points_cost",
    'name' => "points_cost",
	'value' => (int) elgg_extract('points_cost', $vars, $entity->points_cost)
));
echo "</div>";

$dependecies = ($entity) ? Policy::getBadgeDependencies($entity->guid) : null;
echo "<div class='form-group'><label for='dependencies'>" . elgg_echo('label:hjbadge:badges_required') . "</label>";
echo elgg_view('mechanics/dependencies', array(
    'id' => "dependencies",
    'name' => "dependencies",
	'entity' => $entity,
	'value' => elgg_extract('dependencies', $vars, $dependecies)
));
echo "</div>";

echo elgg_view('input/hidden', array(
    'name' => "guid",
	'value' => $entity->guid
));

echo elgg_view('input/hidden', array(
    'name' => "access_id",
	'value' => ($entity) ? $entity->access_id : ACCESS_PUBLIC
));

echo elgg_view('input/submit', array(
    'value' => elgg_echo('save')
));
