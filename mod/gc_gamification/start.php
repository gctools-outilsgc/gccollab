<?php

use hypeJunction\GameMechanics\Media;
use hypeJunction\GameMechanics\Menus;
use hypeJunction\GameMechanics\Permissions;
use hypeJunction\GameMechanics\Policy;
use hypeJunction\GameMechanics\Router;

/**
 * Game Mechanics for Elgg
 *
 * @package hypeJunction
 * @subpackage GameMechanics
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 * @copyright Copyright (c) 2011-2017, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', function() {

	/* Routing */
	elgg_register_page_handler('points', [Router::class, 'pointsPageHandler']);
	elgg_register_plugin_hook_handler('entity:url', 'object', [Router::class, 'urlHandler']);

	/* Actions */
	elgg_register_action('badge/claim', __DIR__ . '/actions/badge/claim.php');
	elgg_register_action('badge/edit', __DIR__ . '/actions/badge/edit.php', 'admin');
	elgg_register_action('badge/delete', __DIR__ . '/actions/badge/delete.php', 'admin');
	elgg_register_action('badge/order', __DIR__ . '/actions/badge/order.php', 'admin');

	elgg_register_action('points/award', __DIR__ . '/actions/points/award.php');
	elgg_register_action('points/reset', __DIR__ . '/actions/points/reset.php', 'admin');

	/* Icons */
	elgg_register_plugin_hook_handler('entity:icon:url', 'object', [Media::class, 'setIconFile']);

	/* Rules and points */
	elgg_register_plugin_hook_handler('get_rules', 'gm_score', [Policy::class, 'setupRules']);
	elgg_register_event_handler('all', 'object', [Policy::class, 'applyEventRules'], 999);
	elgg_register_event_handler('all', 'group', [Policy::class, 'applyEventRules'], 999);
	elgg_register_event_handler('all', 'user', [Policy::class, 'applyEventRules'], 999);
	elgg_register_event_handler('all', 'annotation', [Policy::class, 'applyEventRules'], 999);
	elgg_register_event_handler('all', 'metadata', [Policy::class, 'applyEventRules'], 999);
	elgg_register_event_handler('all', 'relationship', [Policy::class, 'applyEventRules'], 999);
	elgg_register_plugin_hook_handler('permissions_check:annotate', 'all', [Permissions::class, 'canAwardPoints']);
	elgg_register_plugin_hook_handler('permissions_check:comment', 'object', [Permissions::class, 'canComment']);

	/* Menus */
	elgg_register_plugin_hook_handler('register', 'menu:entity', [Menus::class, 'setupEntityMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', [Menus::class, 'setupOwnerBlockMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', [Menus::class, 'setupUserHoverMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:page', [Menus::class, 'setupPageMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:site', [Menus::class, 'setupSiteMenu']);

	/* Views */
	elgg_extend_view('elgg.css', 'framework/mechanics/stylesheet.css');
	elgg_extend_view('admin.css', 'framework/mechanics/stylesheet.css');


	elgg_register_widget_type('hjmechanics', elgg_echo('mechanics:widget:badges'), elgg_echo('mechanics:widget:badges:description'));

	elgg_extend_view('framework/mechanics/sidebar', 'framework/mechanics/history/filter');
	elgg_extend_view('framework/mechanics/sidebar', 'framework/mechanics/leaderboard/filter');
});

elgg_register_event_handler('upgrade', 'system', function() {

	if (!elgg_is_admin_logged_in()) {
		return;
	}

	include_once __DIR__ . '/activate.php';

	$release = 1395099219;
	$old_release = elgg_get_plugin_setting('release', 'gc_gamification');

	if ($release > $old_release) {
		include_once __DIR__ . '/lib/upgrade.php';
		elgg_set_plugin_setting('release', $release, 'gc_gamification');
	}
});
