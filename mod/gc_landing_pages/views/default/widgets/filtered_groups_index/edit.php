<?php

/**
 * Landing page widgets
 */

  $widget_groups = $vars['entity']->widget_groups;
  $widget_title = $vars['entity']->widget_title;
	
	$guest_only = $vars['entity']->guest_only;
	if ( !isset($guest_only) ) $guest_only = "no";
	
	$box_style = $vars['entity']->box_style;
	if ( !isset($box_style) ) $box_style = "collapsable";
?>
<p>
  <?php echo elgg_echo('custom_index_widgets:widget_title'); ?>:
  <?php
    echo elgg_view('input/text', array(
      'name' => 'params[widget_title]',                       
      'value' => $widget_title
    ));
  ?>
</p>
<p>
  <?php echo elgg_echo('group'); ?>: 
  <?php
    $groups = elgg_get_entities(array('type' => 'group', 'limit' => 0));
    $group_list = array();
    $group_list[0] = elgg_echo('custom_index_widgets:widget_all_groups');
    if ($groups) {
      foreach ($groups as $group) {
        $group_list[$group->getGUID()] = $group->name;
      }
    }
    echo elgg_view('input/dropdown', array('name' => 'params[widget_groups]', 'options_values' => $group_list, 'value' => $widget_groups, 'multiple' => true));
  ?>
</p>
<p>
  <?php echo elgg_echo('custom_index_widgets:box_style'); ?>:
  <?php
    echo elgg_view('input/dropdown', array('name' => 'params[box_style]', 'options_values' => array('plain' => 'Plain', 'plain collapsable' => 'Plain and collapsable', 'collapsable' => 'Collapsable', 'standard' => 'No Collapsable'), 'value' => $box_style));
  ?>
</p>
<p>
  <?php echo elgg_echo('custom_index_widgets:guest_only'); ?>:
  <?php
    echo elgg_view('input/dropdown', array('name' => 'params[guest_only]', 'options_values' => array('yes' => 'yes', 'no' => 'no'), 'value' => $guest_only));
  ?>
</p>