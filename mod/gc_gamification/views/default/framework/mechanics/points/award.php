<?php

if (elgg_is_sticky_form('points/award')) {
	$values = elgg_get_sticky_values('points/award');
	$vars = array_merge($vars, $values);
}

echo elgg_view_form('points/award', array(), $vars);
