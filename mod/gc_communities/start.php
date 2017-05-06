<?php

/**
 * Communities start.php
 */

elgg_register_event_handler('init', 'system', 'gc_communities_init');

function gc_communities_init(){
    $communities = json_decode(elgg_get_plugin_setting('communities', 'gc_communities'), true);
    $context = array();

    if( count($communities) > 0 ){
        $parent = new ElggMenuItem('communities', elgg_echo('gc_communities:communities') . '<span class="expicon glyphicon glyphicon-chevron-down"></span>', '#communities_menu');
        elgg_register_menu_item('site', $parent);

        foreach( $communities as $community ){
            $url = $community['community_url'];
            $text = (get_current_language() == 'fr') ? $community['community_fr'] : $community['community_en'];

            //Register Community page handler
            elgg_register_page_handler($url, 'gc_community_page_handler');

            //Register each Community page menu link
            elgg_register_menu_item('communities', array(
                'name' => $url,
                'href' => elgg_get_site_url() . $url,
                'text' => $text
            ));

            $parent->addChild( elgg_get_menu_item('communities', $url) );
            $parent->setLinkClass('item');

            $context[] = "gc_communities-" . $url;
        }
    }

    // Register plugin hooks
    elgg_register_plugin_hook_handler('permissions_check', 'object', 'gc_communities_permissions_hook');
    elgg_register_plugin_hook_handler('permissions_check', 'widget_layout', 'gc_communities_widget_permissions_hook');

    // Register widgets for custom Community pages
    elgg_register_widget_type('filtered_activity_index', elgg_echo('gc_communities:filtered_activity_index'), elgg_echo('gc_communities:filtered_activity_index'), $context, true);

    if( elgg_is_active_plugin('blog') ){
        elgg_register_widget_type('filtered_blogs_index', elgg_echo('gc_communities:filtered_blogs_index'), elgg_echo('gc_communities:filtered_blogs_index'), $context, true);
    }
    
    elgg_register_widget_type('filtered_discussions_index', elgg_echo('gc_communities:filtered_discussions_index'), elgg_echo('gc_communities:filtered_discussions_index'), $context, true);

    if( elgg_is_active_plugin('event_calendar') ){
        elgg_register_widget_type('filtered_events_index', elgg_echo('gc_communities:filtered_events_index'), elgg_echo('gc_communities:filtered_events_index'), $context, true);
    }
    
    elgg_register_widget_type('filtered_feed_index', elgg_echo('gc_communities:filtered_feed_index'), elgg_echo('gc_communities:filtered_feed_index'), $context, true);
    
    if( elgg_is_active_plugin('groups') ){
        elgg_register_widget_type('filtered_groups_index', elgg_echo('gc_communities:filtered_groups_index'), elgg_echo('gc_communities:filtered_groups_index'), $context, true);
    }

    elgg_register_widget_type('filtered_members_index', elgg_echo('gc_communities:filtered_members_index'), elgg_echo('gc_communities:filtered_members_index'), $context, true);

    elgg_register_widget_type('filtered_spotlight_index', elgg_echo('gc_communities:filtered_spotlight_index'), elgg_echo('gc_communities:filtered_spotlight_index'), $context, true);

    if( elgg_is_active_plugin('thewire') ){
        elgg_register_widget_type('filtered_wire_index', elgg_echo('gc_communities:filtered_wire_index'), elgg_echo('gc_communities:filtered_wire_index'), $context, true);
    }
}

function gc_communities_permissions_hook($hook, $entity_type, $returnvalue, $params) {
    $communities = json_decode(elgg_get_plugin_setting('communities', 'gc_communities'), true);
    $url = explode('gc_communities-', $params['entity']->context)[1];

    foreach( $communities as $community ){
        if( $community['community_url'] == $url ){
            $community_animator = $community['community_animator'];
        }
    }

    if( $community_animator == elgg_get_logged_in_user_entity()->username ){
        $returnvalue = true;
    }

    return $returnvalue;
}

function gc_communities_widget_permissions_hook($hook, $entity_type, $returnvalue, $params) {
    $communities = json_decode(elgg_get_plugin_setting('communities', 'gc_communities'), true);
    $url = explode('gc_communities-', $params['context'])[1];

    foreach( $communities as $community ){
        if( $community['community_url'] == $url ){
            $community_animator = $community['community_animator'];
        }
    }

    if( $community_animator == elgg_get_logged_in_user_entity()->username ){
        $returnvalue = true;
    }

    return $returnvalue;
}

function gc_community_page_handler($page, $url){
    $communities = json_decode(elgg_get_plugin_setting('communities', 'gc_communities'), true);

    foreach( $communities as $community ){
        if( $community['community_url'] == $url ){
            $community_en = $community['community_en'];
            $community_fr = $community['community_fr'];
            $community_animator = $community['community_animator'];
        }
    }

    set_input('community_url', $url);
    set_input('community_en', $community_en);
    set_input('community_fr', $community_fr);
    set_input('community_animator', $community_animator);

    @include (dirname ( __FILE__ ) . "/pages/community.php");
    return true;
}

function gc_communities_build_columns($area_widget_list, $widgettypes, $build_server_side = true){

    $column_widgets_view = array();
    $column_widgets_string = "";
    
    if( is_array($area_widget_list) && sizeof($area_widget_list) > 0 ){
        foreach( $area_widget_list as $widget ){
            if( $build_server_side ){
                $title = $widget->widget_title;

                if($widget->widget_title_en && get_current_language() == 'en'){
                    $title = $widget->widget_title_en;
                }

                if($widget->widget_title_fr && get_current_language() == 'fr'){
                    $title = $widget->widget_title_fr;
                }

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
