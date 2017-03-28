<style type="text/css">
	table.depts     			{ width:100%; border-right:1px solid #ccc; border-bottom:1px solid #ccc; margin-top:10px; }
	table.depts th 				{ background:#eee; padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	table.depts td 				{ padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc; }
	.widgets_image_slider img 	{ width: 100%; }
</style>

<script type="text/javascript">
	$(function() {
		$("#select-landingpages").on('change', function() {
			if( $(this).val() !== "" ){
				window.location.href = window.location.href.replace(/[\?#].*|$/, "?page=" + $(this).val());
			}
		});

		$("a.add").click(function(e){
			e.preventDefault();

		    var landing_en = $("#add-landing-page-en").val();
		    var landing_fr = $("#add-landing-page-fr").val();
		    var landing_url = $("#add-landing-page-url").val();

		    if(landing_en !== "" && landing_fr !== "" && landing_url !== ""){
				var landingPagesArray = JSON.parse($("#landingpages").val());
		    	var landingPage = { 'landing_en': landing_en, 'landing_fr': landing_fr, 'landing_url': landing_url };
			    landingPagesArray.push(landingPage);
		    	$("#landingpages").val(JSON.stringify(landingPagesArray));

		    	$(".elgg-foot input[type=submit]").click();
		    }
		});

		$("a.delete").click(function(e){
			e.preventDefault();

			var landingPagesArray = JSON.parse($("#landingpages").val());
			var url = $(this).data('url');
			var found = "";

			$.each(landingPagesArray, function(key, value){
				if(value.landing_url == url){
					found = key;
				}
			});

			landingPagesArray.splice(found, 1);
		    $("#landingpages").val(JSON.stringify(landingPagesArray));

		    $(".elgg-foot input[type=submit]").click();
		});
	});
</script>

<?php

$context = 'gc_landing_pages-' . get_input('page');

elgg_set_plugin_setting('landingpages', $vars['entity']->landingpages, 'gc_landing_pages');
elgg_set_page_owner_guid(elgg_get_config('site_guid'));
$num_columns = elgg_extract('num_columns', $vars, 2);
$show_add_widgets = elgg_extract('show_add_widgets', $vars, true);
$exact_match = elgg_extract('exact_match', $vars, true);
$show_access = elgg_extract('show_access', $vars, true);
$owner = elgg_get_page_owner_entity(); 

echo '<table class="depts">';
echo '<tr> <th>'.elgg_echo('add').'</th> </tr>';
echo '<tr><td>';
echo elgg_echo('gc_landing_pages:title').' (EN): '.elgg_view('input/text', array('id' => 'add-landing-page-en')).'<br/>';
echo elgg_echo('gc_landing_pages:title').' (FR): '.elgg_view('input/text', array('id' => 'add-landing-page-fr')).'<br/>';
echo elgg_echo('gc_landing_pages:url').' : '.elgg_view('input/text', array('id' => 'add-landing-page-url')).'<br/>';
echo '<a class="add elgg-button elgg-button-submit btn btn-primary mtm" href="#">'.elgg_echo('add').'</a></td></tr>';
echo '</table>';

echo '<br />';

$landingpages = json_decode($vars['entity']->landingpages, true);

echo '<select id="select-landingpages"><option value="">Select a landing page</option>';
foreach($landingpages as $key => $landingpage){
	$selected = (get_input('page') == $landingpage['landing_url']) ? " selected" : "";
	echo '<option value="' . $landingpage['landing_url'] . '"' . $selected . '>' . $landingpage['landing_en'] . '</option>';
}
echo '</select>';

echo '<hr />';

echo '<div class="widget-area">';
	$widget_types = elgg_get_widget_types($context, true);
	$widgets = elgg_get_widgets($owner->guid, $context);

	echo elgg_view('output/confirmlink', array(
			'name' => 'delete',
			'text' => elgg_echo('gc_landing_pages:delete'),
			'data-url' => get_input('page'),
			'class' => 'delete elgg-button elgg-button-action mls mrs mbs pull-right',
		)
	);

	if( elgg_can_edit_widget_layout($context) ){
		if( $show_add_widgets ){
			$params = array('context' => $context);
			echo elgg_view('page/layouts/widgets/add_button', $params);
		}

		$params = array(
			'widgets' => $widgets,
			'context' => $context,
			'exact_match' => $exact_match
		);
		echo elgg_view('page/layouts/widgets/add_panel', $params);
	}

	echo '<div class="elgg-layout-widgets">';
	$widget_class = "elgg-col-1of{$num_columns}";
	for( $column_index = $num_columns; $column_index >= 1; $column_index-- ){
		if (isset($widgets[$column_index])) {
			$column_widgets = $widgets[$column_index];
		} else {
			$column_widgets = array();
		}

		echo "<div class=\"$widget_class elgg-widgets\" id=\"elgg-widget-col-$column_index\">";
		if( sizeof($column_widgets) > 0 ){
			foreach( $column_widgets as $widget ){
				if( array_key_exists($widget->handler, $widget_types) ){
					echo elgg_view_entity($widget, array('show_access' => $show_access));
				}
			}
		}
		echo '</div>';
	}
	echo '</div>';
echo '</div>';

echo elgg_view('input/text', array('type' => 'hidden', 'id' => 'landingpages', 'name' => 'params[landingpages]', 'value' => $vars['entity']->landingpages));
