<?php

/**
 * Landing page widgets
 */
 
	$object_type = 'blog';

	$num_items = $vars['entity']->num_items;
	if ( !isset($num_items) ) $num_items = 10;

	$widget_groups = $vars["entity"]->widget_groups;
	if ( !isset($widget_groups) ) $widget_groups = ELGG_ENTITIES_ANY_VALUE;

	$widget_tags = trim($vars['entity']->widget_tags);
  	if( $widget_tags ) $widget_tags = explode(',', $widget_tags);
	
	$site_categories = elgg_get_site_entity()->categories;
	$widget_categories = $vars['entity']->widget_categories;
	$widget_context_mode = $vars['entity']->widget_context_mode;
	if ( !isset($widget_context_mode) ) $widget_context_mode = 'search';
	
	elgg_set_context($widget_context_mode);

	if ($site_categories == NULL || $widget_categories == NULL) {
		$options = array(
			'type' => 'object',
			'subtype' => $object_type,
			'limit' => $num_items,
			'full_view' => false,
			'list_type_toggle' => false,
			'pagination' => false
		);

		if( !empty($widget_tags) ){
			$options['metadata_name'] = 'tags';
		    $options['metadata_values'] = $widget_tags;
		}

		if( !empty($widget_groups) && $widget_groups[0] != 0 ){
		    $options['container_guids'] = $widget_groups;
		}
	} else {
		$options = array(
			'type' => 'object',
			'subtype' => $object_type,
			'container_guids' => $widget_groups,
			'limit' => $num_items,
			'full_view' => false,
			'list_type_toggle' => false,
			'pagination' => false,
			'metadata_name' => 'universal_categories',
			'metadata_value' => $widget_categories
		);
	}

  	$widget_datas = ( isset($options['metadata_name']) ) ?  elgg_list_entities_from_metadata($options) : elgg_list_entities($options);
	
	echo $widget_datas;
?>

