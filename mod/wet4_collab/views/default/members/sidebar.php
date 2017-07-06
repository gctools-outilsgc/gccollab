<?php
/**
 * Members sidebar
 *
 * GC_MODIFICATION
 * Description: Does a user search through the GSA
 * Author: GCTools Team
 */

$action = 'search?entity_type=user&entity_subtype=0&offset=0&search_type=entities';

$params = array(
	'method' => 'get',
	'disable_security' => true,
	'action' => $action
);

$body = elgg_view_form('members/search', $params);

echo elgg_view_module('aside', elgg_echo('members:search'), $body);
echo elgg_view('widgets/suggested_friends/content'); // add suggested colleagues to members sidebar
