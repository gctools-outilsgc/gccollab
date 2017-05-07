<?php

/**
 * Create Community page that will display customized widgets
 */

$community_url = get_input('community_url');
$community_en = get_input('community_en');
$community_fr = get_input('community_fr');
$community_animator = get_input('community_animator');
elgg_set_context('gc_communities-' . $community_url);
elgg_set_page_owner_guid(elgg_get_config('site_guid'));

$customwidgets = elgg_get_widgets(elgg_get_page_owner_guid(), elgg_get_context());
$area1widgets = isset($customwidgets[1]) ? $customwidgets[1] : false;
$area2widgets = isset($customwidgets[2]) ? $customwidgets[2] : false;
$widgettypes = elgg_get_widget_types();

$leftcolumn_widgets_view = gc_communities_build_columns($area1widgets, $widgettypes);
$rightcolumn_widgets_view = gc_communities_build_columns($area2widgets, $widgettypes);

$content = elgg_view_layout("index", array('area1' => $leftcolumn_widgets_view, 'area2' => $rightcolumn_widgets_view));

if (get_current_language() == 'en'){
	echo elgg_view_page( $community_en, $content );
} else {
	echo elgg_view_page( $community_fr, $content );
}
