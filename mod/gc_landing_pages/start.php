<?php

/**
 * Landing page start.php
 */

elgg_register_event_handler('init', 'system', 'gc_landing_pages_init');

function gc_landing_pages_init(){
    $landingpages = elgg_get_plugin_setting('landingpages', 'gc_landing_pages');
    $landingpages = json_decode($landingpages, true);
    $context = array();

    foreach($landingpages as $landingpage){
        //Register landing page handler
        elgg_register_page_handler($landingpage['landing_url'], 'gc_landing_page_handler');

        $text = (get_current_language() == 'en') ? $landingpage['landing_en'] : $landingpage['landing_fr'];

        //Register the site menu link
        elgg_register_menu_item('site', array(
            'name' => $landingpage['landing_url'],
            'href' => elgg_get_site_url() . $landingpage['landing_url'],
            'text' => $text,
        ));

        $context[] = "gc_landing_pages-" . $landingpage['landing_url'];
    }

    // Register widgets for custom landing pages
    elgg_register_widget_type('filtered_activity_index', elgg_echo('gc_landing_pages:filtered_activity_index'), elgg_echo('gc_landing_pages:filtered_activity_index'), $context, true);

    if( elgg_is_active_plugin('blog') ){
        elgg_register_widget_type('filtered_blogs_index', elgg_echo('gc_landing_pages:filtered_blogs_index'), elgg_echo('gc_landing_pages:filtered_blogs_index'), $context, true);
    }
    if( elgg_is_active_plugin('groups') ){
        elgg_register_widget_type('filtered_groups_index', elgg_echo('gc_landing_pages:filtered_groups_index'), elgg_echo('gc_landing_pages:filtered_groups_index'), $context, true);
    }

    elgg_register_widget_type('filtered_spotlight_index', elgg_echo('gc_landing_pages:filtered_spotlight_index'), elgg_echo('gc_landing_pages:filtered_spotlight_index'), $context, true);

    if( elgg_is_active_plugin('thewire') ){
        elgg_register_widget_type('filtered_wire_index', elgg_echo('gc_landing_pages:filtered_wire_index'), elgg_echo('gc_landing_pages:filtered_wire_index'), $context, true);
    }
}

/*
 *  Custom Landing Page
 */
function gc_landing_page_handler($page, $url){
    $landingpages = elgg_get_plugin_setting('landingpages', 'gc_landing_pages');
    $landingpages = json_decode($landingpages, true);

    foreach($landingpages as $landingpage){
        if($landingpage['landing_url'] == $url){
            $page_name_en = $landingpage['landing_en'];
            $page_name_fr = $landingpage['landing_fr'];
        }
    }

    set_input('page_url', $url);
    set_input('page_name_en', $page_name_en);
    set_input('page_name_fr', $page_name_fr);

    @include (dirname ( __FILE__ ) . "/pages/landingpage.php");
    return true;
}

function gc_landing_pages_show_widget_area($areawidgets){
    if( is_array($areawidgets) && sizeof($areawidgets) > 0 ){
        foreach( $areawidgets as $widget ){
            /*Adding a check if widget is set to access ID 2 to show*/
            if( $widget->access_id == 1 && !elgg_is_logged_in() ){
            
            } else {
                if( $widget instanceof ElggWidget ){
                    $vars['entity'] = $widget;
                    
                    $handler = $widget->handler;
                    if (elgg_view_exists("widgets/$handler/content")) {
                        $content = elgg_view("widgets/$handler/content", $vars);
                    } else {
                        elgg_deprecated_notice("widgets use content as the display view", 1.8);
                        $content = elgg_view("widgets/$handler/view", $vars);
                    }
                    echo elgg_view_module('featured',  $widget->title, $content, array('class' => 'elgg-module-highlight'));
                } else {
                    echo $widget;
                }
            }
        }
    }
}

function gc_landing_pages_build_columns($area_widget_list, $widgettypes, $build_server_side = true){

    $column_widgets_view = array();
    $column_widgets_string = "";
    
    if( is_array($area_widget_list) && sizeof($area_widget_list) > 0 ){
        foreach( $area_widget_list as $widget ){
            if( $build_server_side ){
                $title = $widget->widget_title;
                if( !$title ){
                    $title = $widgettypes[$widget->handler]->name;
                }
                if( !$title ){
                    $title = $widget->handler;
                }
                $widget->title = $title;
                
                if( ($widget->guest_only == "yes" && !elgg_is_logged_in()) || $widget->guest_only == "no" || !isset($widget->guest_only) ){
                    $column_widgets_view[] = $widget;  
                }
                
            } else {
                if( !empty($column_widgets_string) ){
                    $column_widgets_string .= "::";
                }
                $column_widgets_string .= "{$widget->handler}::{$widget->getGUID()}";
            }
        }
        
        if( $build_server_side ){
            return $column_widgets_view;
        } else {
            return $column_widgets_string;
        }
    }
    return NULL;    
}
