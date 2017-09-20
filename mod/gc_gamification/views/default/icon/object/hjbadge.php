<?php

/**
 * Badge icon view.
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['entity']     The entity the icon represents - uses getIconURL() method
 * @uses $vars['size']       topbar, tiny, small, medium (default), large, master
 * @uses $vars['href']       Optional override for link
 * @uses $vars['img_class']  Optional CSS class added to img
 * @uses $vars['link_class'] Optional CSS class for the link
 */
use hypeJunction\GameMechanics\Reward;

$entity = $vars['entity'];
$user = elgg_get_logged_in_user_entity();

$sizes = array('small', 'medium', 'large', 'tiny', 'master', 'topbar');
// Get size
if (!in_array($vars['size'], $sizes)) {
	$vars['size'] = "medium";
}

if (elgg_extract('icon_user_status', $vars, false)) {
	if (Reward::isClaimed($entity->guid, $user->guid)) {
		$class = "gm-badge gm-badge-claimed";
	} elseif (Reward::isEligible($entity->guid, $user->guid)) {
		$class = "gm-badge gm-badge-eligible";
	} else {
		$class = "gm-badge gm-badge-unclaimed";
	}
} else {
	$class = "gm-badge";
}

$class .= " gm-badge-{$vars['size']}";

$extra_class = elgg_extract('img_class', $vars, '');

if ($extra_class) {
	$class .= " $extra_class";
}

$title = $entity->title . ': ' . $entity->description;
$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8', false);

$url = (isset($vars['href'])) ? $vars['href'] : $entity->getURL();

$icon_sizes = elgg_get_config('icon_sizes');
$size = $vars['size'];

if (!isset($vars['width'])) {
	$vars['width'] = $size != 'master' ? $icon_sizes[$size]['w'] : null;
}
if (!isset($vars['height'])) {
	$vars['height'] = $size != 'master' ? $icon_sizes[$size]['h'] : null;
}

$img_params = array(
	'src' => $entity->getIconURL($vars['size']),
	'alt' => $title,
	'title' => $title,
	'class' => $class
);

if (!empty($vars['width'])) {
	$img_params['width'] = $vars['width'];
}

if (!empty($vars['height'])) {
	$img_params['height'] = $vars['height'];
}

$img = elgg_view('output/img', $img_params);

if ($url) {
	$params = array(
		'href' => $url,
		'text' => $img,
		'is_trusted' => true,
	);
	$class = elgg_extract('link_class', $vars, '');
	if ($class) {
		$params['class'] = $class;
		if (false !== strpos($class, 'elgg-lightbox')) {
			$params['data-colorbox-opts'] = json_encode(array(
				'maxWidth' => '600px',
			));
		}
	}

	echo elgg_view('output/url', $params);
} else {
	echo $img;
}
