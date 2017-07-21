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
	echo "<div class='form-group'><label for='$throttle'>" . elgg_echo("mechanics:settings:$throttle") . "</label>";
	echo elgg_view('input/text', array(
        'id' => "$throttle",
        'name' => "params[$throttle]",
        'value' => $entity->$throttle
    ));
    echo "</div>";
}

echo "<div class='form-group'><label for='allow_negative_total'>" . elgg_echo("mechanics:settings:allow_negative_total") . "</label>";
echo "<div class='elgg-text-help'>" . elgg_echo('mechanics:settings:allow_negative_total:help') . "</div>";
echo elgg_view('input/select', array(
    'id' => "allow_negative_total",
    'name' => "params[allow_negative_total]",
    'value' => $entity->allow_negative_total,
    'options_values' => array(
		true => elgg_echo('option:yes'),
		false => elgg_echo('option:no')
	)
));
echo "</div>";

echo '<h3>' . elgg_echo('mechanics:settings:scoring_rules') . '</h3>';
echo '<div class="elgg-text-help">' . elgg_echo('mechanics:settings:scoring_rules:help') . '</div>';

$rules = \hypeJunction\GameMechanics\Policy::getRules('events');

foreach ($rules as $rule => $options) {
	echo "<div class='form-group'><label for='$rule'>" . $options['title'] . "</label>";
	echo elgg_view('input/text', array(
        'id' => "$rule",
        'name' => "params[$rule]",
        'value' => $entity->$rule,
		'maxlength' => '3'
    ));
	echo "</div>";
}
