<?php

use hypeJunction\GameMechanics\Badge;
use hypeJunction\GameMechanics\Reward;

$badge_guid = get_input('guid');
$badge = get_entity($badge_guid);

if (!elgg_instanceof($badge, 'object', Badge::SUBTYPE)) {
	return elgg_error_response(elgg_echo('mechanics:badge:claim:failure'));
}

$user = elgg_get_logged_in_user_entity();

if (Reward::claimBadge($badge->guid, $user->guid)) {
	elgg_create_river_item([
		'view' => 'framework/mechanics/river/claim',
		'action_type' => 'claim',
		'subject_guid' => $user->guid,
		'object_guid' => $badge->guid,
	]);

	return elgg_ok_response('', elgg_echo('mechanics:badge:claim:success', [$badge->title]));
}

return elgg_error_response(elgg_echo('mechanics:badge:claim:failure'));

