<?php
/*
 * Exposes API endpoints for User entities
 */

elgg_ws_expose_function(
	"login.user",
	"login_user",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"key" => array('type' => 'string', 'required' => true),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Logs in a user based on user id',
	'POST',
	false,
	false
);

function login_user( $user, $key, $lang ){
	$response = file_get_contents('https://api.gctools.ca/login.ashx?action=login&email=' . $user . '&key=' . $key);
	$json = json_decode($response);

	// if( $json->GCconnexAccess ){
	if( $json->GCcollabAccess ){
		$email = get_user_by_email($user)[0];

		if( $email ){
			login($email);
			forward('cometchat/cometchat_embedded.php');
		}
	} else {
		return "Invalid user key.";
	}
}
