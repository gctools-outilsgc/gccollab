<?php

use hypeJunction\GameMechanics\Badge;

$guid = get_input('guid');
$entity = get_entity($guid);

if ($entity instanceof Badge && $entity->delete()) {
	return system_message(elgg_echo('mechanics:badge:delete:success'));
}

return register_error(elgg_echo('mechanics:badge:delete:error'));
