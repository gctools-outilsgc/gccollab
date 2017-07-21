<?php

use hypeJunction\GameMechanics\Score;

$user_guid = get_input('user_guid');

if (!$user = get_entity($user_guid)) {
	return elgg_error_response(elgg_echo('mechanics:action:reset:error'));
}

$options = array(
	'annotation_owner_guids' => array($user->guid),
	'annotation_names' => array('gm_score'),
	'limit' => 0
);

elgg_delete_annotations($options);

$gm_score_history = elgg_get_entities_from_metadata(array(
	'type' => 'object',
	'subtype' => Score::SUBTYPE,
	'owner_guid' => $user->guid,
	'limit' => 0,
		));

$success = $error = 0;

foreach ($gm_score_history as $gmsh) {
	//system_message("$gm_score_history->guid");
	if ($gmsh->delete()) {
		$success++;
	} else {
		$error++;
	}
}

return elgg_ok_response('', elgg_echo('mechanics:action:reset:success', array($success, $error)));
