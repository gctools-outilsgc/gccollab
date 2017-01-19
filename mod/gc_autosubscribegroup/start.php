<?php

/**
 * Elgg autosubscribegroup plugin
 * This plugin allows new users to get joined to groups automatically when they register.
 *
 * @package autosubscribegroups
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author RONNEL Jérémy
 * @copyright (c) Elbee 2008
 * @link /www.notredeco.com
 *
 * for Elgg 1.9 onwards by iionly (iionly@gmx.de)
 */

/**
 * Init
 *
 */
elgg_register_event_handler('init', 'system', 'gc_autosubscribegroup_init');

function gc_autosubscribegroup_init() {
	// Listen to user registration
	elgg_register_event_handler('create', 'user', 'gc_autosubscribegroup_join', 502);
}

/**
 * auto join group define in plugin settings
 *
 */
function gc_autosubscribegroup_join($event, $object_type, $object) {

	if (($object instanceof ElggUser) && ($event == 'create') && ($object_type == 'user')) {
		//auto submit relationships between user & groups
		//retrieve groups ids from plugin
		$groups = elgg_get_plugin_setting('systemgroups', 'gc_autosubscribegroup');
		$groups = split(',', $groups);

		//for each group ids
		foreach($groups as $groupId) {
			$ia = elgg_set_ignore_access(true);
			$groupEnt = get_entity($groupId);
			elgg_set_ignore_access($ia);
			//if group exist : submit to group
			if ($groupEnt) {
				//join group succeed?
				if ($groupEnt->join($object)) {
					// Remove any invite or join request flags
					elgg_delete_metadata(array('guid' => $object->guid, 'metadata_name' => 'group_invite', 'metadata_value' => $groupEnt->guid, 'limit' => false));
					elgg_delete_metadata(array('guid' => $object->guid, 'metadata_name' => 'group_join_request', 'metadata_value' => $groupEnt->guid, 'limit' => false));
				}
			}
		}
	}
}
