<?php
    $exact_match = elgg_extract('exact_match', $vars, true);
    $show_access = elgg_extract('show_access', $vars, true);
    
    $context = elgg_get_context();
    $widget_types = elgg_get_widget_types($context, true);
    $area1widgets = $vars['area1'];
    $area2widgets = $vars['area2'];

    $community_url = get_input('community_url');
    $community_en = get_input('community_en');
    $community_fr = get_input('community_fr');
    $community_animator = get_input('community_animator');
    
    $title = (get_current_language() == 'fr' ? $community_fr : $community_en);
?>
<h1><?php echo $title; ?></h1>
<div class="widget-area">
    <?php
        $communities = json_decode(elgg_get_plugin_setting('communities', 'gc_communities'));
        foreach( $communities as $community ){
            if( $community->community_url == get_input('community_url') ){
                if( $community->community_animator == elgg_get_logged_in_user_entity()->username || elgg_is_admin_logged_in() ){
                    if( elgg_can_edit_widget_layout($context) ){
                        echo elgg_view('page/layouts/widgets/add_button', array(
                            'context' => $context
                        ));

                        echo elgg_view('page/layouts/widgets/add_panel', array(
                            'widgets' => $widgets,
                            'context' => $context,
                            'exact_match' => $exact_match
                        ));
                    }
                }
            }
        }
    ?>
    <div class="row elgg-layout-widgets">
        <div class="col-md-8 elgg-col-1of2 elgg-widgets" id="elgg-widget-col-1">
        	<?php
                if( count($area1widgets) > 0 ){
                    foreach( $area1widgets as $widget ){
                        if( array_key_exists($widget->handler, $widget_types) && $widget instanceof ElggWidget ){
                            echo elgg_view_entity($widget, array('show_access' => $show_access));
                        }
                    }
                }
            ?>
        </div>
        <div class="col-md-4 pull-right elgg-col-1of2 elgg-widgets" id="elgg-widget-col-2">
            <?php
                if( count($area2widgets) > 0 ){
                    foreach( $area2widgets as $widget ){
                        if( array_key_exists($widget->handler, $widget_types) && $widget instanceof ElggWidget ){
                            echo elgg_view_entity($widget, array('show_access' => $show_access));
                        }
                    }
                }
            ?>
        </div>
    </div>
</div>
