<?php

use hypeJunction\GameMechanics\Policy;

$filter_context = elgg_extract('filter_context', $vars, 'leaderboard');

$tabs = [
	'leaderboard' => [
		'text' => elgg_echo('mechanics:leaderboard'),
		'href' => "points/leaderboard",
		'selected' => ($filter_context == 'leaderboard'),
		'priority' => 100,
	],
	'badges' => [
		'text' => elgg_echo('mechanics:badges:site'),
		'href' => "points/badges",
		'selected' => ($filter_context == 'badges'),
		'priority' => 200,
	],
];

if (elgg_is_logged_in()) {
	$user = elgg_get_logged_in_user_entity();
	$tabs['owner'] = [
		'text' => elgg_echo('mechanics:badges:mine'),
		'href' => "points/owner/$user->username",
		'selected' => ($filter_context == 'owner'),
		'priority' => 300
	];
	
	$tabs['history'] = [
		'text' => elgg_echo('mechanics:history'),
		'href' => "points/history/$user->username",
		'selected' => ($filter_context == 'history'),
		'priority' => 400,
	];
}

if (!elgg_is_admin_logged_in()) {
	$badges = Policy::getBadges(['count' => true]);
	if (empty($badges)) {
		unset($tabs['owner']);
		unset($tabs['badges']);
	}
}

foreach ($tabs as $name => $tab) {
	$tab['name'] = $name;
	elgg_register_menu_item('filter', $tab);
}

echo elgg_view_menu('filter', array(
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz'
));



