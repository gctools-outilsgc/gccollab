<?php

$user = elgg_extract('entity', $vars, false);

if (!$user) {
	$user = elgg_get_page_owner_entity();
}

$badges = elgg_get_entities_from_relationship(array(
	'relationship' => 'claimed',
	'relationship_guid' => $user->guid,
	'inverse_relationship' => false,
	'limit' => 0
		));

echo elgg_view_entity_list($badges, array(
	'full_view' => false,
	'list_type' => 'gallery',
	'icon_size' => 'small',
	'icon_user_status' => false,
	'gallery_class' => 'gm-badge-gallery',
	'item_class' => 'gm-badge-item',
	'no_results' => elgg_echo('mechanics:user_badges:empty'),
));
