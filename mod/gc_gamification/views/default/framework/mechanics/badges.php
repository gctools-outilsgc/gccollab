<?php

use hypeJunction\GameMechanics\Policy;

$limit = get_input('limit', 0);
$offset = get_input('offset', 0);

$badge_types = Policy::getBadgeTypes();

if (elgg_is_admin_logged_in()) {
	$sortable = " elgg-state-sortable";
	elgg_require_js('framework/mechanics/sortable');
} else {
	unset($badge_types['surprise']);
}

$content = '';
foreach ($badge_types as $type => $name) {
	$badges = Policy::getBadgesByType($type, array(
		'limit' => $limit,
		'offset' => $offset,
	));
	if ($badges) {
		$list = elgg_view_entity_list($badges, array(
			'full_view' => false,
			'list_type' => 'gallery',
			'gallery_class' => 'gm-badge-gallery',
			'item_class' => 'gm-badge-item' . $sortable,
			'sortable' => (!empty($sortable)),
		));
		$content .= elgg_view_module('aside', elgg_echo('badge_type:value:' . $type), $list);
	}
}

if (!$content) {
	echo '<p>' . elgg_echo('mechanics:badges:empty') . '</p>';
} else {
	echo $content;
}