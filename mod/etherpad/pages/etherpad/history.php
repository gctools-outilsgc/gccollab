<?php
/**
 * History of revisions of a pad
 *
 * @package ElggPad
 */

$pad_guid = get_input('guid');

$pad = get_entity($pad_guid);
if (!$pad) {

}

$container = $pad->getContainerEntity();
if (!$container) {

}

elgg_set_page_owner_guid($container->getGUID());

if (elgg_instanceof($container, 'group')) {
	elgg_push_breadcrumb($container->name, "etherpad/group/$container->guid/all");
} else {
	elgg_push_breadcrumb($container->name, "etherpad/owner/$container->username");
}
elgg_push_breadcrumb($pad->title, $pad->getURL());
elgg_push_breadcrumb(elgg_echo('etherpad:timeslider'));

$title = $pad->title . ": " . elgg_echo('etherpad:timeslider');

$content = elgg_view_entity($pad, array(
	'timeslider' => true,
	'full_view' => true,
));

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);
