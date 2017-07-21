<?php

echo elgg_format_element('p', [], elgg_view('output/url', [
	'href' => 'points/badges',
	'text' => elgg_echo('mechanics:badges:site'),
	'class' => 'elgg-button elgg-button-action',
]));

$entity = elgg_extract('entity', $vars);

echo '<h3>' . elgg_echo('mechanics:settings:throttling') . '</h3>';
echo '<div class="elgg-text-help">' . elgg_echo('mechanics:settings:throttling:help') . '</div>';

$throttles = array(
	'daily_max',
	'daily_action_max',
	'alltime_action_max',
	'daily_recur_max',
	'alltime_recur_max',
	'object_recur_max',
	'daily_object_max',
	'alltime_object_max',
	'action_object_max',
);

foreach ($throttles as $throttle) {

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo("mechanics:settings:$throttle"),
		'value' => $entity->$throttle,
		'name' => "params[$throttle]"
	]);
}

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo("mechanics:settings:allow_negative_total"),
	'#help' => elgg_echo('mechanics:settings:allow_negative_total:help'),
	'value' => $entity->allow_negative_total,
	'name' => "params[allow_negative_total]",
	'options_values' => array(
		true => elgg_echo('option:yes'),
		false => elgg_echo('option:no')
	),
]);

echo '<h3>' . elgg_echo('mechanics:settings:scoring_rules') . '</h3>';
echo '<div class="elgg-text-help">' . elgg_echo('mechanics:settings:scoring_rules:help') . '</div>';

$rules = \hypeJunction\GameMechanics\Policy::getRules('events');

foreach ($rules as $rule => $options) {
	echo elgg_view_field([
		'#type' => 'text',
		'#label' => $options['title'],
		'value' => $entity->$rule,
		'name' => "params[$rule]",
		'maxlength' => '3'
	]);
}
