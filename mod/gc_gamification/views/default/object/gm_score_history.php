<?php

$entity = elgg_extract('entity', $vars);

$score = $entity->annotation_value;
if ((int)$score < 0) {
	$score_str = "<span class=\"gm-score-negative\">$score</span>";
} else {
	$score_str = "<span class=\"gm-score-positive\">+$score</span>";
}


$rule = $entity->rule;
$rule_str = elgg_echo('mechanics:' . $rule);

switch ($entity->object_type) {
	case 'user' :
	case 'group' :
	case 'object' :
		$object = get_entity($entity->object_id);
		if ($object) {
			$rule_str .= ' - ' . elgg_view('output/url', array(
				'text' => $object->getDisplayName() ? : elgg_echo('untitled'),
				'href' => $object->getURL(),
			));
		}
		break;

	case 'annotation' :
		$object = elgg_get_annotation_from_id($entity->object_id);
		if ($object) {
			$rule_str .= ' - ' . elgg_get_excerpt($annotation->value, 100);
		}
		break;
}

if ($entity->note) {
	$rule_str .= elgg_view('output/longtext', array(
		'value' => $entity->note
	));
}

$time_str = elgg_view_friendly_time($entity->time_created);

echo elgg_view_image_block($score_str, $rule_str, array(
	'image_alt' => $time_str,
	'class' => 'gm-score-line-item',
));

