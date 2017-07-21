<?php

if (elgg_is_sticky_form('badge/edit')) {
	$values = elgg_get_sticky_values('badge/edit');
	$vars = array_merge($vars, $values);
}

echo elgg_view_form('badge/edit', array(
	'enctype' => 'multipart/form-data'
), $vars);