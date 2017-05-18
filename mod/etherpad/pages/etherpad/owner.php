<?php
/**
 * List a user's or group's pads
 *
 * @package ElggPad
 */

$owner = elgg_get_page_owner_entity();
if (!$owner) {
	forward('etherpad/all');
}

// access check for closed groups
group_gatekeeper();

$title = elgg_echo('etherpad:owner', array($owner->name));

elgg_push_breadcrumb($owner->name);

elgg_register_title_button();

$content = elgg_list_entities(array(
	'types' => 'object',
	'subtypes' => array('etherpad', 'subpad'),
	'container_guid' => elgg_get_page_owner_guid(),
	'full_view' => false,
));
if (!$content) {
	$content = '<p>' . elgg_echo('etherpad:none') . '</p>';
}

$filter_context = '';
if (elgg_get_page_owner_guid() == elgg_get_logged_in_user_guid()) {
	$filter_context = 'mine';
}

$params = array(
	'filter_context' => $filter_context,
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('etherpad/sidebar'),
);

if (elgg_instanceof($owner, 'group')) {
	$params['filter'] = '';
}

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
