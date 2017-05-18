<?php
/**
 * Elgg etherpad widget
 *
 * @package etherpad
 */

$max = (int) $vars['entity']->max_display;

$options = array(
	'type' => 'object',
	'subtype' => 'etherpad',
	'container_guid' => $vars['entity']->owner_guid,
	'limit' => $max,
	'full_view' => FALSE,
	'pagination' => FALSE,
);
$content = elgg_list_entities($options);

echo $content;

if ($content) {
	$url = "etherpad/owner/" . elgg_get_page_owner_entity()->username;
	$more_link = elgg_view('output/url', array(
		'href' => $url,
		'text' => elgg_echo('etherpad:more'),
	));
	echo "<span class=\"elgg-widget-more\">$more_link</span>";
} else {
	echo elgg_echo('etherpad:none');
}
