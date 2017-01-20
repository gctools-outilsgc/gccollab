<?php

elgg_register_event_handler('init', 'system', 'gc_stats_init');

function gc_stats_init() {
	elgg_register_page_handler('stats', 'stats_page_handler');
}

function stats_page_handler($page) {
	$base = elgg_get_plugins_path() . 'gc_stats/pages/gc_stats';
	require_once "$base/index.php";
	return true;
}