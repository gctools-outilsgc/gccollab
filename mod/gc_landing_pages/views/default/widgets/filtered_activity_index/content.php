<?php 

/**
 * Custom index widgets
 */

	$widget_groups = $vars['entity']->widget_groups;
	$num_items = $vars['entity']->num_items;
	if (!isset($num_items)) $num_items = 10;
 
	$widget_datas = elgg_list_river(array("limit" => $num_items, "object_guids" => $widget_groups));
  
	echo $widget_datas;
?>        

