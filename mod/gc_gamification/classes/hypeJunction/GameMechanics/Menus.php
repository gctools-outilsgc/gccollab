<?php

namespace hypeJunction\GameMechanics;

use ElggEntity;
use ElggMenuItem;

class Menus {

	/**
	 * Setup entity menu
	 *
	 * @param string         $hook	 "register"
	 * @param string         $type	 "menu:entity"
	 * @param ElggMenuItem[] $return Menu
	 * @param array          $params Hook params
	 * @return ElggMenuItem
	 */
	public static function setupEntitymenu($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		if (!$entity instanceof Badge) {
			return;
		}

		$return = array();

		if ($entity->canEdit()) {
			$return[] = ElggMenuItem::factory([
						'name' => 'edit',
						'text' => elgg_echo('edit'),
						'title' => elgg_echo('edit:this'),
						'href' => "points/badge/edit/{$entity->guid}",
						'priority' => 200,
			]);

			$return[] = ElggMenuItem::factory([
						'name' => 'delete',
						'text' => elgg_view_icon('delete'),
						'title' => elgg_echo('delete:this'),
						'href' => "action/badge/delete?guid={$entity->guid}",
						'confirm' => elgg_echo('deleteconfirm'),
						'priority' => 300,
			]);
		}

		if (!Reward::isClaimed($entity->guid) && Reward::isEligible($entity->guid)) {
			$return[] = ElggMenuItem::factory([
						'name' => 'claim',
						'text' => elgg_echo('mechanics:claim'),
						'href' => "action/badge/claim?guid={$entity->guid}",
						'is_action' => true,
						'confirm' => ($entity->points_cost > 0) ? elgg_echo('mechanics:claim:confirm', array($entity->points_cost)) : false,
						'priority' => 400,
			]);
		}

		return $return;
	}

	/**
	 * Setup owner block menu
	 *
	 * @param string         $hook	 "register"
	 * @param string         $type	 "menu:owner_block"
	 * @param ElggMenuItem[] $return Menu
	 * @param array          $params Hook params
	 * @return ElggMenuItem
	 */
	public static function setupOwnerBlockMenu($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		if (!elgg_instanceof($entity, 'user')) {
			return $return;
		}

		if ($entity->canEdit()) {
			$badges = Policy::getBadges(['count' => true]);
			if ($badges) {
				$return[] = ElggMenuItem::factory([
							'name' => 'badges',
							'text' => elgg_echo('mechanics:badges'),
							'href' => "points/owner/$entity->username"
				]);
			}
		}

		return $return;
	}

	/**
	 * Setup user hover menu
	 *
	 * @param string         $hook	 "register"
	 * @param string         $type	 "menu:user_hover"
	 * @param ElggMenuItem[] $return Menu
	 * @param array          $params Hook params
	 * @return ElggMenuItem
	 */
	public static function setupUserHoverMenu($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity ElggEntity */

		if (elgg_is_admin_logged_in()) {
			$return[] = ElggMenuItem::factory([
						'name' => 'gm_reset',
						'text' => elgg_echo('mechanics:admin:reset'),
						'href' => "action/points/reset?user_guid=$entity->guid",
						'is_action' => true,
						'rel' => 'confirm',
						'section' => 'admin'
			]);
		}

		if ($entity->canAnnotate(0, 'gm_score_award')) {
			$return[] = ElggMenuItem::factory([
						'name' => 'gm_score_award',
						'text' => elgg_echo('mechanics:admin:award'),
						'href' => "points/award/$entity->guid",
						'link_class' => 'elgg-lightbox',
						'data-colorbox-opts' => json_encode([
							'maxWidth' => '600px',
						]),
			]);
		}

		return $return;
	}

	/**
	 * Setup page menu
	 *
	 * @param string         $hook	 "register"
	 * @param string         $type	 "menu:page"
	 * @param ElggMenuItem[] $return Menu
	 * @param array          $params Hook params
	 * @return ElggMenuItem
	 */
	public static function setupPageMenu($hook, $type, $return, $params) {

		$return[] = ElggMenuItem::factory([
					'name' => 'gamemechanics',
					'parent_name' => 'appearance',
					'text' => elgg_echo('mechanics:badges:site'),
					'href' => 'points/badges',
					'priority' => 500,
					'contexts' => ['admin'],
					'section' => 'configure'
		]);

		return $return;
	}

	/**
	 * Setup site menu
	 *
	 * @param string         $hook	 "register"
	 * @param string         $type	 "menu:site"
	 * @param ElggMenuItem[] $return Menu
	 * @param array          $params Hook params
	 * @return ElggMenuItem
	 */
	public static function setupSiteMenu($hook, $type, $return, $params) {

		$return[] = ElggMenuItem::factory([
					'name' => 'leaderboard',
					'text' => elgg_echo('mechanics:leaderboard'),
					'href' => 'points/leaderboard',
		]);

		return $return;
	}

}
