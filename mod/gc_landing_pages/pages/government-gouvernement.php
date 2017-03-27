<?php

/**
 * Create landing page using custom_index_widgets index page
 */

elgg_set_context('gc_landing_pages');
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

$title = '<h1>'.elgg_echo('gc_landing_pages:menu').'</h1>';

$content = $title . elgg_view_layout($layout, array('area1' => $leftcolumn_widgets_view, 'area2' => $rightcolumn_widgets_view, 'layoutmode' => 'index_mode'));

echo elgg_view_page( elgg_echo('gc_landing_pages:menu'), $content );
