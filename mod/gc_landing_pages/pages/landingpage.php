<?php

/**
 * Create generic landing page that will display customized widgets
 */

$page_url = get_input('page_url');
$page_name_en = get_input('page_name_en');
$page_name_fr = get_input('page_name_fr');
elgg_set_context('gc_landing_pages-' . $page_url);
elgg_set_page_owner_guid(elgg_get_config('site_guid'));

$widgettypes = elgg_get_widget_types();

$page_owner = elgg_get_page_owner_guid();
$layout = "index";

$customwidgets = elgg_get_widgets($page_owner, elgg_get_context());
$area1widgets = isset($customwidgets[1]) ? $customwidgets[1] : FALSE;
$area2widgets = isset($customwidgets[2]) ? $customwidgets[2] : FALSE;

if( empty($area1widgets) && empty($area2widgets) ){
    if( isset($vars['area3']) ) $vars['area1'] = $vars['area3'];
    if( isset($vars['area4']) ) $vars['area2'] = $vars['area4'];
}

$leftcolumn_widgets_view = gc_landing_pages_build_columns($area1widgets, $widgettypes);
$rightcolumn_widgets_view = gc_landing_pages_build_columns($area2widgets, $widgettypes);

$title = (get_current_language() == 'en') ? '<h1>'.$page_name_en.'</h1>' : '<h1>'.$page_name_fr.'</h1>';

$content = $title . elgg_view_layout($layout, array('area1' => $leftcolumn_widgets_view, 'area2' => $rightcolumn_widgets_view, 'layoutmode' => 'index_mode'));

if (get_current_language() == 'en'){
	echo elgg_view_page( $page_name_en, $content );
} else {
	echo elgg_view_page( $page_name_fr, $content );
}
