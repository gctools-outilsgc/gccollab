<?php

/**
 *
 * User setting for Notification options. Displays different options for subscription settings, how to be notified and who or what to subscribe
 * @author Christine Yu <internalfire5@live.com>
 *
 */


$action_url = elgg_get_site_url();
if (elgg_get_config('https_login')) {
	$action_url = str_replace("http:", "https:", $action_url);
}
$action_url .= 'action/cp_notifications/usersettings/save';

<<<<<<< HEAD
$personal_notifications = array('likes','mentions','content'); // likes mentions content


/// PERSONAL NOTIFICATIONS (NOTIFY FOR LIKES, @MENTIONS AND MAYBE SHARES)
$content .= "<section id='notificationstable' cellspacing='0' cellpadding='4' width='100%' class='clearfix'>";
$content .= '<div class="col-sm-12 clearfix"> <h3 class="well">'.elgg_echo('cp_notifications:heading:personal_section').'</h3>';

$personal_notifications = array('likes','mentions','content', 'opportunities');
foreach ($personal_notifications as $label) {

	$chk_email = create_checkboxes($user->getGUID(), "cpn_{$label}_email", array("{$label}_email", "set_notify_off"), elgg_echo('cp_notifications:chkbox:email'));
	$chk_site = create_checkboxes($user->getGUID(), "cpn_{$label}_site", array("{$label}_site", "set_notify_off"), elgg_echo('cp_notifications:chkbox:site'), '', 'chkbox_site');
	$content .= '<div class="col-sm-8">'.elgg_echo("cp_notifications:personal_{$label}").'</div>';
	$content .= "<div class='col-sm-2'>{$chk_email}</div> <div class='col-sm-2'>{$chk_site}</div>";
}

$content .= '</div>';
$content .= '</section>';


 
/// SUBSCRIBE TO COLLEAGUE NOTIFICATIONS
$colleagues = $user->getFriends(array('limit' => false));
$subscribed_colleagues = elgg_get_plugin_user_setting('subscribe_colleague_picker', $user->getOwnerGUID(),'cp_notifications');


	$subbed_colleagues = elgg_get_entities_from_relationship(array(
		'relationship' => 'cp_subscribed_to_site_mail',
		'relationship_guid' => $user->guid,
		'type' => 'user',
		'limit' => false,
	));

	foreach($subbed_colleagues as $c) {
		$subbed_colleague_guids[] = $c->getGUID();
	}

$colleague_picker = elgg_view('input/friendspicker', array(
	'entities' => $colleagues, 
	'name' => 'params[subscribe_colleague_picker]', 
	'value' => $subbed_colleague_guids//json_decode($subscribed_colleagues,true),
=======
echo elgg_view_form('notifications/usersettings', array(
	'class' => 'elgg-form-alt',
	'action' => $action_url,
>>>>>>> connex/gcconnex
));

