<?php

use hypeJunction\GameMechanics\Policy;

$dependencies = elgg_extract('value', $vars);
if (!is_array($dependencies)) {
	$dependencies = array();
}

$value = array();
foreach ($dependencies as $dependency) {
	if (elgg_instanceof($dependency)) {
		$value[] = $dependency->guid;
	} else if (is_numeric($dependency)) {
		$value[] = (int) $dependency;
	}
}

$badges = Policy::getBadges();
$entity = elgg_extract('entity', $vars);

if (!$badges) {
	return;
}

foreach ($badges as $badge) {
	if ($badge->guid == $entity->guid) {
		continue;
	}

	$icon = elgg_view('output/img', array(
		'src' => $badge->getIconURL('small')
	));

	echo elgg_view_field([
		'#type' => 'checkbox',
		'#class' => 'gm-badge-dep-picker',
		'label' => $icon . $badge->title,
		'name' => 'dependencies[]',
		'value' => $badge->guid,
		'checked' => (in_array($badge->guid, $value))
	]);
}
