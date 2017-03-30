<?php

/**
 * Landing page widgets
 */

 	$widget = $vars['entity'];
 	
	$num_items = $widget->num_items;
	if ( !isset($num_items) ) $num_items = 10;
	
	$widget_title = $widget->widget_title;
	$widget_hashtag = $widget->widget_hashtag;
?>
<p>
	<?php echo elgg_echo('widget_manager:widgets:edit:custom_title'); ?>:
	<?php
		echo elgg_view('input/text', array(
			'name' => 'params[widget_title]',                        
			'value' => $widget_title
		));
	?>
</p>
<p>
	<?php echo elgg_echo('widget:numbertodisplay'); ?>	
	<?php
		echo elgg_view('input/dropdown', array(
			'name' => 'params[num_items]',
			'options_values' => array('1' => '1', '3' => '3', '5' => '5', '8' => '8', '10' => '10', '12' => '12', '15' => '15', '20' => '20', '30' => '30', '40' => '40', '50' => '50', '100' => '100'),
			'value' => $num_items
		));
	?>
</p>
<p>
	<?php echo elgg_echo('Hashtag'); ?>:
	<?php
		echo elgg_view('input/text', array(
			'name' => 'params[widget_hashtag]',                        
			'value' => $widget_hashtag
		));
	?>
</p>
