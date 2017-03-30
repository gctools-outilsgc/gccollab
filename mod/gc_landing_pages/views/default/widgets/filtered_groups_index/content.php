<?php
 
/**
 * Landing page widgets
 */

  $num_items = $vars['entity']->num_items;
  if ( !isset($num_items) ) $num_items = 10;

  $widget_groups = $vars['entity']->widget_groups;

  $widget_tags = trim($vars['entity']->widget_tags);
  if( $widget_tags ) $widget_tags = explode(',', $widget_tags);

  $widget_context_mode = $vars['entity']->widget_context_mode;
  if( !isset($widget_context_mode) ) $widget_context_mode = 'search';
  elgg_set_context($widget_context_mode);

  $options = array(
    'type' => 'group',
    'limit' => $num_items,
    'full_view' => false,
    'list_type_toggle' => false,
    'pagination' => false,
    'order_by' => 'e.last_action DESC'
  );

  if( !empty($widget_tags) ){
    $options['metadata_name'] = 'tags';
    $options['metadata_values'] = $widget_tags;
  }

  if( !empty($widget_groups) && $widget_groups[0] != 0 ){
    $options['guids'] = $widget_groups;
  }

  $widget_datas = ( isset($options['metadata_name']) ) ?  elgg_list_entities_from_metadata($options) : elgg_list_entities($options);
  
  echo $widget_datas;
?>       