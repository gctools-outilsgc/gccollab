<?php

/**
 * Elgg autosubscribegroup plugin
 * Allows admins to select groups for new users to automatically join
 *
 * @package autosubscribegroups
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author RONNEL Jérémy
 * @author Mark Wooff <mark.wooff@tbs-sct.gc.ca>
 * @copyright (c) Elbee 2008
 * @link /www.notredeco.com
 *
 * for Elgg 1.9 onwards by iionly (iionly@gmx.de)
 */

/**
 * Init
 */
elgg_register_event_handler('init', 'system', 'gc_autosubscribegroup_init');

function gc_autosubscribegroup_init() {
	// Listen to user registration
	elgg_register_event_handler('create', 'user', 'gc_autosubscribegroup_join', 502);
	elgg_register_event_handler('create', 'group', 'gc_autosubscribegroup_create', 502);
}

/**
 * Autosubscribe new users upon registration
 */
function gc_autosubscribegroup_join($event, $object_type, $object) {

	if (($object instanceof ElggUser) && ($event == 'create') && ($object_type == 'user')) {
		//auto submit relationships between user & groups
		//retrieve groups ids from plugin
		$groups = elgg_get_plugin_setting('autogroups', 'gc_autosubscribegroup');
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

/**
 * Autosubscribe group admins upon group creation
 */
function gc_autosubscribegroup_create($event, $object_type, $object) {
	if (($object instanceof ElggGroup) && ($event == 'create') && ($object_type == 'group')) {
		//retrieve group id from plugin
		$admingroup = elgg_get_plugin_setting('admingroups', 'gc_autosubscribegroup');

		$ia = elgg_set_ignore_access(true);
		$groupEnt = get_entity($admingroup);
		$userEnt = get_user($object->owner_guid);
		elgg_set_ignore_access($ia);
		//if group exist : submit to group
		if ($groupEnt) {
			//join group succeed?
			if ($groupEnt->join($userEnt)) {
				// Remove any invite or join request flags
				elgg_delete_metadata(array('guid' => $userEnt->guid, 'metadata_name' => 'group_invite', 'metadata_value' => $groupEnt->guid, 'limit' => false));
				elgg_delete_metadata(array('guid' => $userEnt->guid, 'metadata_name' => 'group_join_request', 'metadata_value' => $groupEnt->guid, 'limit' => false));
			}
		}
	}
}
