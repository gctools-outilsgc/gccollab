<?php
/*
 * Exposes API endpoints for Blog entities
 */

elgg_ws_expose_function(
	"get.blogpost",
	"get_blogpost",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => true)
	),
	'Retrieves a blog post & all replies based on user id and blog post id',
	'POST',
	true,
	false
);

function get_blogpost( $user, $guid ){
	$user_entity = is_numeric($user) ? get_user($user) : ( strpos($user, '@') !== FALSE ? get_user_by_email($user)[0] : get_user_by_username($user) );
 	if( !$user_entity ) return "User was not found. Please try a different GUID, username, or email address";
	if( !$user_entity instanceof ElggUser ) return "Invalid user. Please try a different GUID, username, or email address";

	$entity = get_entity( $guid );
	if( !$entity ) return "Blog was not found. Please try a different GUID";
	if( !$entity instanceof ElggBlog ) return "Invalid blog. Please try a different GUID";

	elgg_set_ignore_access(true);
	
	$blog_posts = elgg_list_entities(array(
	    'type' => 'object',
		'subtype' => 'blog',
		'guid' => $guid
	));
	$blog_post = json_decode($blog_posts)[0];

	$likes = elgg_get_annotations(array(
		'guid' => $blog_post->guid,
		'annotation_name' => 'likes'
	));
	$blog_post->likes = count($likes);

	$liked = elgg_get_annotations(array(
		'guid' => $blog_post->guid,
		'annotation_owner_guid' => $user_entity->guid,
		'annotation_name' => 'likes'
	));
	$blog_post->liked = count($liked) > 0;

	$blog_post->comments = get_entity_comments($blog_post->guid);

	$blog_post->userDetails = get_user_block($blog_post->owner_guid);

	return $blog_post;
}
