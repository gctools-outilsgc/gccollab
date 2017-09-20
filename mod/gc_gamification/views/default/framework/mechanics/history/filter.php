<?php

$filter_context = elgg_extract('filter_context', $vars, false);

if ($filter_context !== 'history') {
	return;
}

$body = "<div class='form-group'><label for='limit'>" . elgg_echo('mechanics:leaderboard:limit') . "</label>";
$body .= elgg_view('input/select', array(
    'id' => "limit",
    'name' => "limit",
    'value' => get_input('limit', 10),
	'options' => array(5, 10, 25, 50, 100)
));
$body .= "</div>";

$body .= "<div class='form-group'><label for='period'>" . elgg_echo('mechanics:leaderboard:period') . "</label>";
$body .= elgg_view('input/select', array(
    'id' => "period",
    'name' => "period",
    'value' => get_input('period', 'all'),
	'options_values' => array(
		'all' => elgg_echo('mechanics:period:all'),
		'year' => elgg_echo('mechanics:period:year'),
		'month' => elgg_echo('mechanics:period:month'),
		'week' => elgg_echo('mechanics:period:week'),
		'day' => elgg_echo('mechanics:period:day'),
	)
));
$body .= "</div>";

$submit = elgg_view('input/submit', array(
    'value' => elgg_echo('mechanics:filter')
));

$form_body = elgg_view_module('aside', elgg_echo('mechanics:filter'), $body, [
	'footer' => $submit
		]);

echo elgg_view('input/form', array(
	'body' => $form_body,
	'action' => current_page_url(),
	'method' => 'GET',
	'disable_security' => true
));
