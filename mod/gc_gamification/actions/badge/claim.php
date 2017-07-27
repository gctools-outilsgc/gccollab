<?php

use hypeJunction\GameMechanics\Badge;
use hypeJunction\GameMechanics\Reward;

$badge_guid = get_input('guid');
$badge = get_entity($badge_guid);

if (!elgg_instanceof($badge, 'object', Badge::SUBTYPE)) {
	return register_error(elgg_echo('mechanics:badge:claim:failure'));
}

$user = elgg_get_logged_in_user_entity();

if (Reward::claimBadge($badge->guid, $user->guid)) {
	elgg_create_river_item([
		'view' => 'framework/mechanics/river/claim',
		'action_type' => 'claim',
		'subject_guid' => $user->guid,
		'object_guid' => $badge->guid,
	]);

	return system_message(elgg_echo('mechanics:badge:claim:success', array($badge->title)));
}

return register_error(elgg_echo('mechanics:badge:claim:failure'));

