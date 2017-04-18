<?php
/*
 * GC Mobile API functions.php
 */

function get_user_block( $userid ){
	$user_entity = get_user( $userid );

	if( !$user_entity )
		return "User was not found. Please try a different GUID, username, or email address";

	if( !$user_entity instanceof ElggUser )
		return "Invalid user. Please try a different GUID, username, or email address";

	$user['user_id'] = $user_entity->guid;
	$user['username'] = $user_entity->username;
	$user['displayName'] = $user_entity->name;
	$user['email'] = $user_entity->email;
	$user['profileURL'] = $user_entity->getURL();
	$user['iconURL'] = $user_entity->geticon();
	$user['dateJoined'] = date("Y-m-d H:i:s", $user_entity->time_created);

	return $user;
}

function get_entity_comments( $guid ){
	$entity = get_entity($guid);
	
	$comments['count'] = $entity->countComments();
	$commentEntites = elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'comment',
		'container_guid' => $entity->guid,
		'order_by' => 'time_created asc'
	));

	$i = 0;
	foreach ($commentEntites as $comment) {
		$i++;
		$comments['comment_'.$i] = array('comment_user'=>get_userBlock($comment->getOwner()),'comment_text'=>$comment->description,'comment_date'=>date("Y-m-d H:i:s",$comment->time_created));

	}
	return $comments;
}
