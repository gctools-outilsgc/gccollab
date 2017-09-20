<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_entity($entity, array(
	'full_view' => true,
	'icon_size' => 'medium'
));
