<?php
/**
 * WET 4 Collab Theme plugin
 *
 * @package wet4Theme
 */

elgg_register_event_handler('init', 'system', 'wet4_collab_theme_init');

function wet4_collab_theme_init() {

	// theme specific CSS
	elgg_extend_view('css/elgg', 'wet4_theme/css');
	elgg_extend_view('css/elgg', 'wet4_theme/custom_css');

	//message preview
    elgg_register_ajax_view("messages/message_preview");
}
