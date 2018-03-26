<?php
/*
 * Exposes API endpoints for Wire entities
 */

 elgg_ws_expose_function(
 	"post.wiretest",
 	"post_wire_test",
 	array(
 		"user" => array('type' => 'string', 'required' => true),
 		"message" => array('type' => 'string', 'required' => true),
		"image" => array('type' =>'string', 'required' => false, 'default' => ''),
 		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
 	),
 	'Posts a new wire post based on user id',
 	'POST',
 	true,
 	false
 );


 function post_wire_test($user, $message, $image, $lang)
 {
 	$user_entity = is_numeric($user) ? get_user($user) : (strpos($user, '@') !== false ? get_user_by_email($user)[0] : get_user_by_username($user));
 	if (!$user_entity) {
 		return "User was not found. Please try a different GUID, username, or email address";
 	}
 	if (!$user_entity instanceof ElggUser) {
 		return "Invalid user. Please try a different GUID, username, or email address";
 	}

 	if (trim($message) == "") {
 		return elgg_echo("thewire:blank");
 	}

 	if (!elgg_is_logged_in()) {
 		login($user_entity);
 	}

	$image_file = "";
	if ($image != "") {
		return $image;
		$image_data = base64_decode($image);
		$source = imagecreatefromstring($image_data);

	}

 	$new_wire = thewire_save_post($message, $user_entity->guid, ACCESS_PUBLIC, 0);
 	if (!$new_wire) {
 		return elgg_echo("thewire:notsaved");
 	}

 	return elgg_echo("thewire:posted");
 }
