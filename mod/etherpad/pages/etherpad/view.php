<?php
/**
 * View a single pad
 *
 * @package ElggPad
 */

$pad_guid = get_input('guid');
$pad = get_entity($pad_guid);
if (!$pad) {
	forward();
}

elgg_set_page_owner_guid($pad->getContainerGUID());

group_gatekeeper();

$container = elgg_get_page_owner_entity();
if (!$container) {
}

$title = $pad->title;

if (elgg_instanceof($container, 'group')) {
	elgg_push_breadcrumb($container->name, "etherpad/group/$container->guid/all");
} else {
	elgg_push_breadcrumb($container->name, "etherpad/owner/$container->username");
}
elgg_push_breadcrumb($title);

$content = elgg_view_entity($pad, array('full_view' => true));
if(in_array($pad->getSubtype(), array('etherpad', 'subpad')) ||
elgg_get_plugin_setting('show_comments', 'etherpad') == 'yes'){
	$content .= elgg_view_comments($pad);
}

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);
