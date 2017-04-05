<?php 

/**
 * Landing page widgets
 */
 
 	$widget = $vars['entity'];
	$object_type = 'thewire';
	
	$num_items = $widget->num_items;
	$widget_hashtag = $widget->widget_hashtag;
	if ( !isset($num_items) ) $num_items = 10;
	elgg_set_context('search');
 	
 	$wire_ids = array();
	$wires = elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'thewire',
		'limit' => 0
	));
	foreach($wires as $wire){
		if(strpos($wire->description, $widget_hashtag) !== false){
			$wire_ids[] = $wire->guid;
		}
	}

	$widget_datas = elgg_list_entities(array(
		'type' => 'object',
		'subtype' => $object_type,
		'limit' => $num_items,
		'full_view' => false,
		'list_type_toggle' => false,
		'pagination' => false,
    	'guids' => $wire_ids
	));

	echo $widget_datas;
?>