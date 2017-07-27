<?php

use hypeJunction\GameMechanics\Policy;
use hypeJunction\GameMechanics\Score;

$now = time();
$user = elgg_extract('user', $vars, elgg_get_page_owner_entity());

$period = get_input('period', null);
switch ($period) {
	case 'year' :
		$time_lower = $now - 365 * 24 * 60 * 60;
		break;

	case 'month' :
		$time_lower = $now - 30 * 24 * 60 * 60;
		break;

	case 'week' :
		$time_lower = $now - 7 * 24 * 60 * 60;
		break;

	case 'day' :
		$time_lower = $now - 1 * 24 * 60 * 60;
		break;

	default :
		$time_lower = null;
		break;
}

$total = Policy::getUserScore($user, $time_lower, $now);

$limit = get_input('limit', 10);
$offset = get_input('offset', 0);

$options = array(
	'types' => 'object',
	'subtypes' => Score::SUBTYPE,
	'limit' => $limit,
	'offset' => $offset,
	'container_guid' => $user->guid,
	'created_time_lower' => $time_lower,
	'created_time_upper' => $now,
	'count' => true,
	'wheres' => array(),
);

$score = elgg_echo('mechanics:currentscore', array($total));
$list = elgg_list_entities($options);

echo elgg_view_module('aside', $score, $list);
