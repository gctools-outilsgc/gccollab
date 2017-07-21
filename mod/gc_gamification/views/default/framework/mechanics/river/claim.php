<?php

$object = $vars['item']->getObjectEntity();

echo elgg_view('river/elements/layout', array(
	'item' => $vars['item'],
	'attachments' => elgg_view_entity($object, array('full_view' => false)),
));