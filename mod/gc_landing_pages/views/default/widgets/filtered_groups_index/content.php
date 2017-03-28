<?php
 
/**
 * Landing page widgets
 */

  $widget_groups = $vars['entity']->widget_groups;
  $widget_context_mode = $vars['entity']->widget_context_mode;
  if (!isset($widget_context_mode)) $widget_context_mode = 'search';
  elgg_set_context($widget_context_mode);
 
  if( !empty($widget_groups) ){
    $widget_datas = elgg_list_entities(array(
  		'type' => 'group',
  		'limit' => 0,
  		'full_view' => false,
  		'list_type_toggle' => false,
  		'pagination' => false,
      'guids' => $widget_groups
    ));
  }

  echo $widget_datas;
?>       