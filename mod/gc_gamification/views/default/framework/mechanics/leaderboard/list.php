<?php

use hypeJunction\GameMechanics\Policy;

$now = time();

$period = get_input('period', null);
switch ($period) {
	case 'year' :
		$time_lower = $now - 365 * 24 * 60 * 60;
		break;

	case 'month' :
		$time_lower = $now - 30 * 24 * 60 * 60;
		break;

	case 'week' :
		$time_lower = $now - 7 * 24 * 60 * 60;
		break;

	case 'day' :
		$time_lower = $now - 1 * 24 * 60 * 60;
		break;

	default :
		$time_lower = null;
		break;
}


$limit = get_input('limit', 10);
$offset = get_input('offset', 0);

$leaders = Policy::getLeaderBoard($time_lower, $now, $limit, $offset);

if (sizeof($leaders) > 0) {
	echo '<ul class="elgg-list">';
	foreach ($leaders as $leader) {
		$icon = elgg_view_entity_icon($leader, 'small');
		$link = elgg_view('output/url', array(
			'text' => $leader->name,
			'href' => $leader->getURL(),
		));
		$badges = elgg_list_entities_from_relationship(array(
			'relationship' => 'claimed',
			'relationship_guid' => $leader->guid,
			'inverse_relationship' => false,
			'limit' => 0,
			'full_view' => false,
			'list_type' => 'gallery',
			'icon_size' => 'small',
			'icon_user_status' => false,
			'gallery_class' => 'gm-badge-gallery',
			'item_class' => 'gm-badge-item',
		));
		$score = elgg_get_annotations(array(
			'annotation_calculation' => 'sum',
			'annotation_names' => 'gm_score',
			'guids' => $leader->guid,
			'annotation_created_time_lower' => $time_lower,
			'annotation_created_time_upper' => $time_upper,
		));
		if ((int) $score < 0) {
			$score_str = "<span class=\"gm-score-negative\">$score</span>";
		} else {
			$score_str = "<span class=\"gm-score-positive\">+$score</span>";
		}
		echo '<li class="elgg-item">';
		echo elgg_view_image_block($icon, $link . $badges, array(
			'image_alt' => $score_str
		));
		echo '</li>';
	}
	echo '</ul>';
} else {
	echo '<p>' . elgg_echo('mechanics:leaderboard:empty') . '</p>';
}
