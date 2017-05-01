<?php
/*
 * GC Mobile API functions.php
 */

function get_user_block( $userid ){
	$user_entity = is_numeric($userid) ? get_user($userid) : ( strpos($userid, '@') !== FALSE ? get_user_by_email($userid)[0] : get_user_by_username($userid) );

	if( !$user_entity )
		return "";

	if( !$user_entity instanceof ElggUser )
		return "";

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

function wire_filter( $text ){
	$site_url = elgg_get_site_url();

	$text = ''.$text;

	// email addresses
	$text = preg_replace('/(^|[^\w])([\w\-\.]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})/i', '$1<a href="mailto:$2@$3">$2@$3</a>', $text);

	// links
	$text = parse_urls($text);

	// usernames
	$text = preg_replace('/(^|[^\w])@([\p{L}\p{Nd}._]+)/u', '$1<a href="' . $site_url . 'thewire/owner/$2">@$2</a>', $text);

	// hashtags
	$text = preg_replace('/(^|[^\w])#(\w*[^\s\d!-\/:-@]+\w*)/', '$1<a href="' . $site_url . 'thewire/tag/$2">#$2</a>', $text);

	$text = trim($text);

	return $text;
}

function clean_text( $text ){
	return trim( preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode( html_entity_decode( strip_tags( $text ) ) ) ) ) );
}
