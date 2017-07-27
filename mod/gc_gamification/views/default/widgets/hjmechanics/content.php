<?php

$user = elgg_get_page_owner_entity();

echo elgg_view('framework/mechanics/user_score', array(
	'entity' => $user
));

echo elgg_view('framework/mechanics/user_badges', array(
	'entity' => $user
));
