<?php
/**
 * Members plugin initialization
 *
 * To adding a list page, handle the hook (members:list, <page_name>) and return the HTML for the list.
 *
 * To alter the navigation tabs, use the hook (members:config, tabs) which receives the array used to build them.
 */

elgg_register_event_handler('init', 'system', 'members_init');

/**
 * Initialize page handler and site menu item
 */
function members_init() {
	elgg_register_page_handler('members', 'members_page_handler');

	$item = new ElggMenuItem('members', elgg_echo('members'), 'members');
	elgg_register_menu_item('site', $item);

	$list_types = array('newest', 'popular', 'online', 'type');

	foreach ($list_types as $type) {
		elgg_register_plugin_hook_handler('members:list', $type, "members_list_$type");
		elgg_register_plugin_hook_handler('members:config', 'tabs', "members_nav_$type");
	}
}

/**
 * Returns content for the "popular" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "popular"
 * @param string|null $returnvalue list content (null if not set)
 * @param array       $params      array with key "options"
 * @return string
 */
function members_list_popular($hook, $type, $returnvalue, $params) {
	if ($returnvalue !== null) {
		return;
	}

	$options = $params['options'];
	$options['relationship'] = 'friend';
	$options['inverse_relationship'] = false;
	return elgg_list_entities_from_relationship_count($options);
}

/**
 * Returns content for the "newest" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "newest"
 * @param string|null $returnvalue list content (null if not set)
 * @param array       $params      array with key "options"
 * @return string
 */
function members_list_newest($hook, $type, $returnvalue, $params) {
	if ($returnvalue !== null) {
		return;
	}
	return elgg_list_entities($params['options']);
}

/**
 * Returns content for the "online" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "online"
 * @param string|null $returnvalue list content (null if not set)
 * @param array       $params      array with key "options"
 * @return string
 */
function members_list_online($hook, $type, $returnvalue, $params) {
	if ($returnvalue !== null) {
		return;
	}
	return get_online_users();
}

/**
 * Returns content for the "type" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "type"
 * @param string|null $returnvalue list content (null if not set)
 * @param array       $params      array with key "options"
 * @return string
 */
function members_list_type($hook, $type, $returnvalue, $params) {
	if ($returnvalue !== null) {
		return;
	}

	$type = get_input('type');

	$user_types = array('' => elgg_echo('gcRegister:make_selection'), 'academic' => elgg_echo('gcRegister:occupation:academic'), 'student' => elgg_echo('gcRegister:occupation:student'), 'federal' => elgg_echo('gcRegister:occupation:federal'), 'provincial' => elgg_echo('gcRegister:occupation:provincial'), 'municipal' => elgg_echo('gcRegister:occupation:municipal'), 'international' => elgg_echo('gcRegister:occupation:international'), 'ngo' => elgg_echo('gcRegister:occupation:ngo'), 'community' => elgg_echo('gcRegister:occupation:community'), 'business' => elgg_echo('gcRegister:occupation:business'), 'media' => elgg_echo('gcRegister:occupation:media'), 'retired' => elgg_echo('gcRegister:occupation:retired'), 'other' => elgg_echo('gcRegister:occupation:other'));
	
	$data = "<label class='mtm' for='by_type'>" . elgg_echo('gcRegister:membertype') . "</label>" . elgg_view('input/dropdown', array('id' => 'by_type', 'class' => 'mbm', 'name' => 'by_type', 'options_values' => $user_types, 'value' => $type));

	$data .= '<script>$(function() {
			$("#by_type").on("change", function() {
				if( $(this).val() !== "" ){
					window.location.href = window.location.href.replace(/[\?#].*|$/, "?type=" + $(this).val());
				}
			});
		});</script>';

	$data .= elgg_list_entities_from_metadata(array(
        'type' => 'user', 
        'metadata_name' => 'user_type', 
        'metadata_value' => $type, 
        'pagination' => true,
        'limit' => 10
    ));

	return $data;
}

/**
 * Appends "popular" tab to the navigation
 *
 * @param string $hook        "members:config"
 * @param string $type        "tabs"
 * @param array  $returnvalue array that build navigation tabs
 * @param array  $params      unused
 * @return array
 */
function members_nav_popular($hook, $type, $returnvalue, $params) {
	$returnvalue['popular'] = array(
		'title' => elgg_echo('sort:popular'),
		'url' => "members/popular",
	);
	return $returnvalue;
}

/**
 * Appends "newest" tab to the navigation
 *
 * @param string $hook        "members:config"
 * @param string $type        "tabs"
 * @param array  $returnvalue array that build navigation tabs
 * @param array  $params      unused
 * @return array
 */
function members_nav_newest($hook, $type, $returnvalue, $params) {
	$returnvalue['newest'] = array(
		'title' => elgg_echo('sort:newest'),
		'url' => "members",
	);
	return $returnvalue;
}

/**
 * Appends "online" tab to the navigation
 *
 * @param string $hook        "members:config"
 * @param string $type        "tabs"
 * @param array  $returnvalue array that build navigation tabs
 * @param array  $params      unused
 * @return array
 */
function members_nav_online($hook, $type, $returnvalue, $params) {
	$returnvalue['online'] = array(
		'title' => elgg_echo('members:label:online'),
		'url' => "members/online",
	);
	return $returnvalue;
}

/**
 * Appends "type" tab to the navigation
 *
 * @param string $hook        "members:config"
 * @param string $type        "tabs"
 * @param array  $returnvalue array that build navigation tabs
 * @param array  $params      unused
 * @return array
 */
function members_nav_type($hook, $type, $returnvalue, $params) {
	$returnvalue['type'] = array(
		'title' => elgg_echo('members:label:type'),
		'url' => "members/type",
	);
	return $returnvalue;
}

/**
 * Members page handler
 *
 * @param array $page url segments
 * @return bool
 */
function members_page_handler($page) {
	$base = elgg_get_plugins_path() . 'members/pages/members';

	if (empty($page[0])) {
		$page[0] = 'newest';
	}

	$vars = array();
	$vars['page'] = $page[0];

	if ($page[0] == 'search') {
		require_once "$base/search.php";
	} else {
		require_once "$base/index.php";
	}
	return true;
}
