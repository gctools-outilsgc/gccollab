<?php

use hypeJunction\GameMechanics\Policy;

$user = elgg_extract('entity', $vars);
$size = elgg_extract('size', $vars);

$score = Policy::getUserScore($user);
$score_str = elgg_echo('mechanics:currentscore', array($score));

if ($status = $user->gm_status) {
	$badge = get_entity($status);
	$status_icon = elgg_view_entity_icon($badge, 'tiny');
	$status_str = elgg_echo('mechanics:currentstatus', array($badge->title));
}

echo elgg_view_image_block($status_icon, $score_str . '<br />' . $status_str);

