<?php

require_once($CONFIG->pluginspath.'event_calendar/models/model.php');


$event_list = elgg_get_entities(array(
	'subtype' => 'event_calendar',
	'type' => 'object',
));

$today = date("F j, Y, g:i a");

echo "<div style='overflow-y:auto; height:200px;'>";
echo '<p><h4>'.elgg_echo('index_widget:event:today',array($today)).'</h4></p>';
echo "<br/>";
foreach ($event_list as $event) {

	$start_date = date('Y-m-d', $event->start_date);
	$end_date = date('Y-m-d', $event->end_date);

	$start_date = event_calendar_format_time($start_date, $event->start_time);
	$end_date = event_calendar_format_time($end_date, $event->end_time);
echo "
		<div style='padding-bottom:5px;'>
			<div id='event_title'><strong><a href='{$event->getURL()}'>{$event->title}</a></strong></div>
			<div id='event_date'>{$start_date} - {$end_date} EST</div>
			<div id='event_location'>{$event->venue}</div>
		</div>";
}
echo "</div>";

?>
