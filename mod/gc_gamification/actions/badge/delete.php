<?php

use hypeJunction\GameMechanics\Badge;

$guid = get_input('guid');
$entity = get_entity($guid);

if ($entity instanceof Badge && $entity->delete()) {
	return elgg_ok_response('', elgg_echo('mechanics:badge:delete:success'));
}

return elgg_error_response(elgg_echo('mechanics:badge:delete:error'));
