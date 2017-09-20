<?php

namespace hypeJunction\GameMechanics;

class Permissions {

	/**
	 * Check if current user can award points to the user
	 * Currently, only admins can award points
	 *
	 * @param string  $hook   "permissions_check:annotate"
	 * @param string  $type	  "all"
	 * @param boolean $return Current permission
	 * @param array   $params Additional params
	 * @return boolean
	 */
	public static function canAwardPoints($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		$user = elgg_extract('user', $params);
		$annotation_name = elgg_extract('annotation_name', $params);

		if ($annotation_name !== 'gm_score_award') {
			return $return;
		}

		if (!elgg_instanceof($entity, 'user')) {
			// Only users can be awarded points
			return false;
		}

		if ($entity->isAdmin()) {
			// Do not allow awards on admins
			return false;
		}

		return elgg_instanceof($user, 'user') && $user->isAdmin();
	}

	/**
	 * Do not allow comments on badges
	 *
	 * @param string $hook   "permissions_check:comment"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function canComment($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		if ($entity instanceof Badge || $entity instanceof BadgeRule || $entity instanceof Score) {
			return false;
		}
	}

}
