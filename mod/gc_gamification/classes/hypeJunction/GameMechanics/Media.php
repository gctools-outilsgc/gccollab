<?php

namespace hypeJunction\GameMechanics;

class Media {

	/**
	 * Override the default entity icon file
	 *
	 * @param string    $hook   "entity:icon:file"
	 * @param string    $type   "object"
	 * @param ElggIcon $icon   Icon file
	 * @param array     $params Hook params
	 * @return ElggIcon
	 */
	public static function setIconFile($hook, $type, $icon, $params) {

		$entity = elgg_extract('entity', $params);
		$size = elgg_extract('size', $params, 'medium');

		$icon->owner_guid = $entity->owner_guid;
		$icon->setFilename("icons/{$entity->guid}{$size}.jpg");

		return $icon;
	}

}
