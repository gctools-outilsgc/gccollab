<?php

use hypeJunction\GameMechanics\Reward;

$user = elgg_get_logged_in_user_entity();

$entity = elgg_extract('entity', $vars, false);
$full = elgg_extract('full_view', $vars, false);
$icon_size = elgg_extract('icon_size', $vars, 'medium');
$icon_user_status = elgg_extract('icon_user_status', $vars, true);
$sortable = elgg_extract('sortable', $vars, false);

if (!elgg_in_context('widgets') && !elgg_in_context('activity')) {
	$metadata = elgg_view_menu('entity', array(
		'entity' => $entity,
		'handler' => "points",
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz'
	));
}

$title = elgg_view('output/url', array(
	'text' => $entity->title,
	'href' => $entity->getURL(),
	'class' => 'elgg-lightbox',
	'data-colorbox-opts' => json_encode(array(
		'maxWidth' => '600px',
	)),
		));

$icon = elgg_view_entity_icon($entity, $icon_size, array(
	'link_class' => (!$sortable) ? 'elgg-lightbox' : '',
	'icon_user_status' => $icon_user_status,
		));

if ($full) {

	$content = elgg_view('output/longtext', array(
		'value' => $entity->description
	));

	if (Reward::isClaimed($entity->guid, $user->guid)) {
		$content .= '<div class="gm-badge-claimed-notice">' . elgg_echo('mechanics:alreadyclaimed') . '</div>';
	}

	$summary = elgg_view('object/elements/summary', array(
		'entity' => $entity,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => false,
		'content' => $content
	));

	if (elgg_is_xhr()) {
		echo elgg_view_title($entity->title);
	}
	echo elgg_view_image_block($icon, $summary, array(
		'class' => 'gm-badge-full',
	));

	echo elgg_view('framework/mechanics/rules', $vars);
} else {

	if (get_input('list_type', 'gallery') == 'gallery') {
		if ($icon_size == 'tiny' || $icon_size == 'small') {
			echo $icon;
		} else {
			echo elgg_view_module('aside', $title, $icon, array(
				'class' => 'gm-badge-module',
				'footer' => $metadata
			));
		}
	} else {
		$summary = elgg_view('object/elements/summary', array(
			'entity' => $entity,
			'title' => $title,
			'metadata' => $metadata,
			'subtitle' => elgg_get_excerpt($entity->description),
		));
		echo elgg_view_image_block($icon, $summary, array(
			'class' => 'gm-badge-summary',
		));
	}
}
