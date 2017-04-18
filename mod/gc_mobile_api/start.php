<?php
/*
 * GC Mobile API start.php
 */

elgg_register_event_handler('init', 'system', 'gc_mobile_api_init');

function gc_mobile_api_init() {
	include elgg_get_plugins_path() . "gc_mobile_api/inc/functions.php";

	$models = array('blog', 'discussion', 'event', 'file', 'group', 'like', 'message', 'user', 'wire');
	foreach( $models as $model ){
		include elgg_get_plugins_path() . "gc_mobile_api/models/$model.php";
	}
}
