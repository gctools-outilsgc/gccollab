<?php
/**
 * WET 4 Collab Theme plugin
 *
 * @package wet4Theme
 */

elgg_register_event_handler('init', 'system', 'wet4_collab_theme_init');

function wet4_collab_theme_init() {

	// theme specific CSS
	elgg_extend_view('css/elgg', 'wet4_theme/css');
	elgg_extend_view('css/elgg', 'wet4_theme/custom_css');

	//message preview
    elgg_register_ajax_view("messages/message_preview");
	
	elgg_register_plugin_hook_handler('register', 'menu:user_menu', 'remove_custom_colleagues_menu_item', 1);
	elgg_register_event_handler('pagesetup', 'system', 'add_custom_colleagues_menu_item', 1000);
}

function remove_custom_colleagues_menu_item($hook, $type, $return, $params) {
    // Remove Colleagues menu item
    foreach($return as $key => $item) {
        if ($item->getName() == 'Colleagues') {
            unset($return[$key]);
        }
    }
    return $return;
}

function add_custom_colleagues_menu_item() {
	$user = elgg_get_logged_in_user_entity();

    if( !empty($user) ){
		$options = array(
			"type" => "user",
			"count" => true,
			"relationship" => "friendrequest",
			"relationship_guid" => $user->getGUID(),
			"inverse_relationship" => true
		);

		$count = elgg_get_entities_from_relationship($options);

		$countTitle = " - ";
		$countBadge = "";
		if( $count > 0 ){
            //display 9+ instead of huge numbers in notif badge
            if( $count >= 10 ){
                $countTitle .= '9+';
            } else {
				$countTitle .= $count;
            }

           $countBadge = "<span class='notif-badge'>" . $count . "</span>";
        }

	    $params = array(
			"name" => "Colleaguess",
			"href" => "friends/" . $user->username,
			"text" => '<i class="fa fa-users mrgn-rght-sm mrgn-tp-sm fa-lg"></i>' . $countBadge,
			"title" => elgg_echo('userMenu:colleagues') . $countTitle . elgg_echo('friend_request') .'(s)',
	        "class" => '',
	        "item_class" => '',
			"priority" => '1'
		);

		elgg_register_menu_item("user_menu", $params);
	}
}