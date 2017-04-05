<?php

/**
 * Custom index widgets
 */
 
 	$widget = $vars['entity'];
 	
	$display_avatar = $widget->display_avatar;
	if( !isset($display_avatar) ) $display_avatar = 'yes';
	
	$widget_users = $widget->widget_users;
	$widget_title = $widget->widget_title;
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
	<?php echo elgg_echo('user'); ?>: 
	<?php
		echo elgg_view('input/text', array(
			'name' => 'params[widget_users]',                        
			'value' => $widget_users
		));
	?>
</p>
<p>
	<?php echo elgg_echo('custom_index_widgets:display_avatar'); ?>
	<?php
		echo elgg_view('input/dropdown', array(
			'name' => 'params[display_avatar]',
			'options_values' => array('yes' => 'Yes', 'no' => 'No'),
			'value' => $display_avatar
		));
	?>
</p>