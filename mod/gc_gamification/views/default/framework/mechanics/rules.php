<?php

use hypeJunction\GameMechanics\Policy;

$entity = elgg_extract('entity', $vars);
$user = elgg_get_logged_in_user_entity();

echo '<div class="clear"></div>';

$points_required = (int) $entity->points_required;
$points_cost = (int) $entity->points_cost;

if ($points_required || $points_cost) {
	$score = Policy::getUserScore($user);

	if ($points_cost > 0) {
		$reqs .= '<div class="elgg-warning">' . elgg_echo('mechanics:badge:pointscost', array($points_cost)) . '</div>';
	}

	if ($points_required > 0) {
		$label = '<label>' . elgg_echo('mechanics:pointsrequired') . " [$score / $points_required]" . '</label>';
		$progress = elgg_view('output/mechanics/progress', array(
			'value' => $score,
			'total' => $points_required
		));
		$reqs = '<div class="gm-rule-progress">' . $label . $progress . '</div>';
	}
}

$rules = Policy::getBadgeRules($entity->guid);
if ($rules) {
	foreach ($rules as $rule) {
		$recurrences = Policy::getUserRecurTotal($user, $rule->annotation_value);
		$label = '<label>' . elgg_echo("mechanics:$rule->annotation_value") . " [$recurrences / $rule->recurse]" . '</label>';
		$progress = elgg_view('output/mechanics/progress', array(
			'value' => $recurrences,
			'total' => $rule->recurse
		));
		$reqs .= '<div class="gm-rule-progress">' . $label . $progress . '</div>';
	}
}

if ($reqs) {
	echo elgg_view_module('aside', elgg_echo('mechanics:badge:requirements'), $reqs);
}

$badges_required = Policy::getBadgeDependencies($entity->guid);
if ($badges_required) {
	$list = elgg_view_entity_list($badges_required, array(
		'full_view' => false,
		'list_type' => 'gallery',
		'icon_size' => 'medium',
		'gallery_class' => 'gm-badge-gallery',
		'item_class' => 'gm-badge-item'
	));
	echo elgg_view_module('aside', elgg_echo('mechanics:badgesrequired'), $list);
}
