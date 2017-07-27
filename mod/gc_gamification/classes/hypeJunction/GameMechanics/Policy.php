<?php

namespace hypeJunction\GameMechanics;

use ElggData;
use ElggEntity;
use ElggUser;

class Policy {

	/**
	 * Get rule definitions
	 * @return array
	 */
	public static function getRules($type = '') {
		$rules = elgg_trigger_plugin_hook('get_rules', 'gm_score', null, array());

		if ($type && array_key_exists($type, $rules)) {
			return $rules[$type];
		} else {
			return $rules;
		}
	}

	/**
	 * Get user score total in a given time frame
	 *
	 * @param ElggUser $user       User entity
	 * @param int      $time_lower Lower time constraint
	 * @param int      $time_upper Upper time constraint
	 * @return int
	 */
	public static function getUserScore($user = null, $time_lower = null, $time_upper = null) {

		if (!elgg_instanceof($user, 'user')) {
			return 0;
		}

		$options = array(
			'types' => 'object',
			'subtypes' => Score::SUBTYPE,
			'container_guids' => $user->guid,
			'metadata_names' => 'annotation_value',
			'metadata_calculation' => 'sum',
			'metadata_created_time_lower' => $time_lower,
			'metadata_created_time_upper' => $time_upper,
		);

		return (int) elgg_get_metadata($options);
	}

	/**
	 * Get a list of users ordered by their total score
	 *
	 * @param int $time_lower Lower time constraint
	 * @param int $time_upper Upper time constraint
	 * @return ElggEntity[]|false
	 */
	public static function getLeaderboard($time_lower = null, $time_upper = null, $limit = 10, $offset = 0) {

		$options = array(
			'types' => 'user',
			'annotation_names' => 'gm_score',
			'annotation_created_time_lower' => $time_lower,
			'annotation_created_time_upper' => $time_upper,
			'limit' => $limit,
			'offset' => $offset,
		);

		return elgg_get_entities_from_annotation_calculation($options);
	}

	/**
	 * Get total score for a specified action rule
	 *
	 * @param ElggUser $user       User entity
	 * @param string   $rule       Rule name
	 * @param int      $time_lower Lower time constraint
	 * @param int      $time_upper Upper time constraint
	 * @return int
	 */
	public static function getUserActionTotal($user, $rule, $time_lower = null, $time_upper = null) {

		if (empty($rule) || !elgg_instanceof($user, 'user')) {
			return 0;
		}

		$dbprefix = elgg_get_config('dbprefix');
		$msn_id = elgg_get_metastring_id('rule');
		$msv_id = elgg_get_metastring_id($rule);

		$options = array(
			'type' => 'object',
			'subtype' => Score::SUBTYPE,
			'container_guid' => $user->guid,
			'metadata_names' => 'annotation_value',
			'metadata_calculation' => 'sum',
			'metadata_created_time_lower' => $time_lower,
			'metadata_created_time_upper' => $time_upper,
			'joins' => array(
				"JOIN {$dbprefix}metadata rulemd ON n_table.entity_guid = rulemd.entity_guid"
			),
			'wheres' => array(
				"(rulemd.name_id = $msn_id AND rulemd.value_id = $msv_id)"
			),
		);

		return (int) elgg_get_metadata($options);
	}

	/**
	 * Get the number of recurrences when user was awarded points for a given rule action on an object
	 *
	 * @param ElggUser $user       User entity
	 * @param string   $rule       Rule name
	 * @param int      $time_lower Lower time constraint
	 * @param int      $time_upper Upper time constraint
	 * @return int
	 */
	public static function getUserRecurTotal($user, $rule, $time_lower = null, $time_upper = null) {

		if (empty($rule) || !elgg_instanceof($user, 'user')) {
			return 0;
		}

		$options = array(
			'types' => 'object',
			'subtypes' => Score::SUBTYPE,
			'container_guids' => $user->guid,
			'created_time_lower' => $time_lower,
			'created_time_upper' => $time_upper,
			'metadata_name_value_pairs' => array(
				array('name' => 'rule', 'value' => $rule)
			),
			'count' => true,
		);

		return elgg_get_entities_from_metadata($options);
	}

	/**
	 * Get total score that was collected on an object by a given user with a given rule in given time frame
	 *
	 * @param ElggData $object     Object
	 * @param ElggUser $user       User entity
	 * @param string   $rule       Rule name
	 * @param int      $time_lower Lower time constraint
	 * @param int      $time_upper Upper time constraint
	 * @return int
	 */
	public static function getObjectTotal($object, $user = null, $rule = null, $time_lower = null, $time_upper = null) {

		if (!is_object($object)) {
			return 0;
		}

		$object_id = (isset($object->guid)) ? $object->guid : $object->id;
		$object_type = $object->getType();

		$dbprefix = elgg_get_config('dbprefix');

		$msn_id = elgg_get_metastring_id('object_ref');
		$msv_id = elgg_get_metastring_id("$object_type:$object_id");

		$options = array(
			'type' => 'object',
			'subtype' => Score::SUBTYPE,
			'container_guid' => $user->guid,
			'metadata_names' => 'annotation_value',
			'metadata_calculation' => 'sum',
			'metadata_created_time_lower' => $time_lower,
			'metadata_created_time_upper' => $time_upper,
			'joins' => array(
				"JOIN {$dbprefix}metadata objmd ON n_table.entity_guid = objmd.entity_guid"
			),
			'wheres' => array(
				"(objmd.name_id = $msn_id AND objmd.value_id = $msv_id)"
			),
		);

		if (!empty($rule)) {
			$msn_id = elgg_get_metastring_id('rule');
			$msv_id = elgg_get_metastring_id($rule);
			$options['joins'][] = "JOIN {$dbprefix}metadata rulemd ON n_table.entity_guid = rulemd.entity_guid";
			$options['wheres'][] = "(rulemd.name_id = $msn_id AND rulemd.value_id = $msv_id)";
		}

		return (int) elgg_get_metadata($options);
	}

	/**
	 * Get the number of recurrences when user was awarded points for a given rule action on an object
	 *
	 * @param ElggData $object     Object
	 * @param ElggUser $user       User entity
	 * @param string   $rule       Rule name
	 * @param int      $time_lower Lower time constraint
	 * @param int      $time_upper Upper time constraint
	 * @return int
	 */
	public static function getObjectRecurTotal($object, $user = null, $rule = null, $time_lower = null, $time_upper = null) {

		if (!is_object($object)) {
			return 0;
		}

		$object_id = (isset($object->guid)) ? $object->guid : $object->id;
		$object_type = $object->getType();

		$options = array(
			'types' => 'object',
			'subtypes' => Score::SUBTYPE,
			'container_guids' => $user->guid,
			'created_time_lower' => $time_lower,
			'created_time_upper' => $time_upper,
			'metadata_name_value_pairs' => array(
				array('name' => 'rule', 'value' => $rule),
				array('name' => 'object_ref', 'value' => "$object_type:$object_id")
			),
			'count' => true,
		);

		return elgg_get_entities_from_metadata($options);
	}

	/**
	 * Reward user with applicable badges
	 *
	 * @param ElggUser $user User entity
	 * @return boolean
	 */
	public static function rewardUser($user = null) {

		if (!$user) {
			$user = elgg_get_logged_in_user_entity();
		}

		$gmReward = Reward::rewardUser($user);

		$errors = $gmReward->getErrors();
		if ($errors) {
			foreach ($errors as $error) {
				register_error($error);
			}
		}

		$messages = $gmReward->getMessages();
		if ($messages) {
			foreach ($messages as $message) {
				system_message($message);
			}
		}

		$badges = $gmReward->getNewUserBadges();
		if (count($badges)) {
			foreach ($badges as $badge) {
				if ($user->guid == elgg_get_logged_in_user_guid()) {
					system_message(elgg_echo('mechanics:badge:claim:success', array($badge->title)));
				} else {
					// @todo: send notification instead?
				}
				elgg_create_river_item(array(
					'view' => 'framework/mechanics/river/claim',
					'action_type' => 'claim',
					'subject_guid' => $user->guid,
					'object_guid' => $badge->guid,
				));
			}
		}

		//error_log(print_r($gmReward->getLog(), true));

		return true;
	}

	/**
	 * Get site badges
	 *
	 * @param array $options ege* option
	 * @return array|false
	 */
	public static function getBadges($options = array(), $getter = 'elgg_get_entities_from_metadata') {

		$defaults = array(
			'types' => 'object',
			'subtypes' => Badge::SUBTYPE,
			'order_by_metadata' => array(
				'name' => 'priority',
				'direction' => 'ASC',
				'as' => 'integer'
			),
		);

		$options = array_merge($defaults, $options);

		if (is_callable($getter)) {
			return $getter($options);
		}

		return elgg_get_entities($options);
	}

	/**
	 * Get badges of a given type
	 *
	 * @param string $type
	 * @param array  $options
	 * @param string $getter
	 * @return array|false
	 */
	public static function getBadgesByType($type = '', $options = array(), $getter = 'elgg_get_entities_from_metadata') {

		$options['metadata_name_value_pairs'] = array(
			'name' => 'badge_type',
			'value' => $type,
		);

		return get_badges($options, $getter);
	}

	/**
	 * Get types of badges
	 * @return array
	 */
	public static function getBadgeTypes() {

		$return = array(
			'status' => elgg_echo('badge_type:value:status'),
			'experience' => elgg_echo('badge_type:value:experience'),
			//'purchase' => elgg_echo('badge_type:value:purchase'),
			'surprise' => elgg_echo('badge_type:value:surprise')
		);

		$return = elgg_trigger_plugin_hook('mechanics:badge_types', 'object', null, $return);

		return $return;
	}

	/**
	 * Get badges that are required to uncover this badge
	 *
	 * @param int $badge_guid GUID of the badge
	 * @return array|false
	 */
	public static function getBadgeDependencies($badge_guid) {

		return elgg_get_entities_from_relationship(array(
			'types' => 'object',
			'subtypes' => Badge::SUBTYPE,
			'relationship' => 'badge_required',
			'relationship_guid' => $badge_guid,
			'inverse_relationship' => true
		));
	}

	/**
	 * Get badge rules
	 *
	 * @param int $badge_guid GUID of the badge
	 * @return array|false
	 */
	public static function getBadgeRules($badge_guid) {

		return elgg_get_entities_from_metadata(array(
			'type' => 'object',
			'subtype' => BadgeRule::SUBTYPE,
			'container_guid' => $badge_guid,
			'limit' => 10,
		));
	}

	/**
	 * Check if the event qualifies for points and award them to the user
	 *
	 * @param string $event  Event type
	 * @param string $type   'object'|'user'|'group'|'relationship'|'annotation'|'metadata'
	 * @param mixed  $object Event object
	 * @return boolean
	 */
	public static function applyEventRules($event, $type, $object) {

		// Object
		if (is_object($object)) {
			$entity = $object;
		} else if (is_array($object)) {
			$entity = elgg_extract('entity', $object, null);
			if (!$entity) {
				$entity = elgg_extract('user', $object, null);
			}
			if (!$entity) {
				$entity = elgg_extract('group', $object, null);
			}
		}

		if (!is_object($entity)) {
			// Terminate early, nothing to act upon
			return true;
		}

		// Get rules associated with events
		$rules = get_scoring_rules('events');

		$event_name = "$event::$type";

		// Apply rules
		foreach ($rules as $rule_name => $rule_options) {

			if (!in_array($event_name, (array) $rule_options['events'])) {
				continue;
			}

			$rule_options['name'] = $rule_name;
			$Rule = Rule::applyRule($entity, $rule_options, $event_name);

			$errors = $Rule->getErrors();
			if ($errors) {
				foreach ($errors as $error) {
					register_error($error);
				}
			}

			$messages = $Rule->getMessages();
			if ($messages) {
				foreach ($messages as $message) {
					system_message($message);
				}
			}

			if ($Rule->terminateEvent()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Setup scoring rules
	 *
	 * @param string $hook	 "get_rules"
	 * @param string $type	 "gm_score"
	 * @param array  $return Rules
	 * @param array  $params Hook params
	 * @return array
	 *
	 */
	public static function setupRules($hook, $type, $return, $params) {

		$rules['events'] = array(
			/**
			 * Rule: publish a blog post
			 */
			'create:object:blog' => array(
				'title' => elgg_echo('mechanics:create:object:blog'),
				'events' => array(
					'publish::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'blog',
				),
				// override global settings
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: add a bookmark
			 */
			'create:object:bookmarks' => array(
				'title' => elgg_echo('mechanics:create:object:bookmarks'),
				'events' => array(
					'create::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'bookmarks',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: add a page
			 */
			'create:object:page' => array(
				'title' => elgg_echo('mechanics:create:object:page'),
				'events' => array(
					'create::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'page',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: add a top-level page
			 */
			'create:object:page_top' => array(
				'title' => elgg_echo('mechanics:create:object:page_top'),
				'events' => array(
					'create::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'page_top',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: add a file
			 */
			'create:object:file' => array(
				'title' => elgg_echo('mechanics:create:object:file'),
				'events' => array(
					'create::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'file',
				//'simletype' => array('image', 'document'),
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: add a wire post
			 */
			'create:object:thewire' => array(
				'title' => elgg_echo('mechanics:create:object:thewire'),
				'events' => array(
					'create::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'thewire',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: add a group discussion topic
			 */
			'create:object:groupforumtopic' => array(
				'title' => elgg_echo('mechanics:create:object:groupforumtopic'),
				'events' => array(
					'create::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'groupforumtopic',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: create a group
			 */
			'create:group:default' => array(
				'title' => elgg_echo('mechanics:create:group:default'),
				'events' => array(
					'create::group'
				),
				'attributes' => array(
					'type' => 'group',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: add a comment
			 */
			'create:annotation:comment' => array(
				'title' => elgg_echo('mechanics:create:annotation:comment'),
				'events' => array(
					'create::object'
				),
				'object_guid_attr' => 'container_guid',
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'comment',
				),
				'settings' => array(
					'object_recur_max' => 0,
				),
				'callbacks' => [
					function(Rule $rule) {
						if ($rule->getObject()->owner_guid == $rule->getSubject()->guid) {
							return false;
						}
						return true;
					},
				],
			),
			/**
			 * Rule: receive a comment
			 */
			'create:annotation:comment:reverse' => array(
				'title' => elgg_echo('mechanics:create:annotation:comment:reverse'),
				'events' => array(
					'create::object'
				),
				'object_guid_attr' => 'container_guid',
				'subject_guid_attr' => 'container_guid', // entity owner will be identified automatically
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'comment',
				),
				'settings' => array(
					'object_recur_max' => 0,
				),
				'callbacks' => [
					function(Rule $rule) {
						if ($rule->getObject()->owner_guid == $rule->getSubject()->guid) {
							return false;
						}
						return true;
					},
				],
			),
			/**
			 * Rule: add a reply to a discussion
			 */
			'create:annotation:group_topic_post' => array(
				'title' => elgg_echo('mechanics:create:annotation:group_topic_post'),
				'events' => array(
					'create::object'
				),
				'object_guid_attr' => 'container_guid',
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'discussion_reply',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: receiving a reply to a discussion
			 */
			'create:annotation:group_topic_post:reverse' => array(
				'title' => elgg_echo('mechanics:create:annotation:group_topic_post:reverse'),
				'events' => array(
					'create::object'
				),
				'object_guid_attr' => 'container_guid',
				'subject_guid_attr' => 'container_guid',
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'discussion_reply',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: liking something (annotation)
			 */
			'create:annotation:likes' => array(
				'title' => elgg_echo('mechanics:create:annotation:likes'),
				'events' => array(
					'create::annotation'
				),
				'object_guid_attr' => 'entity_guid',
				'attributes' => array(
					'type' => 'annotation',
					'name' => 'likes',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: receiving a like
			 */
			'create:annotation:likes:reverse' => array(
				'title' => elgg_echo('mechanics:create:annotation:likes:reverse'),
				'events' => array(
					'create::annotation'
				),
				'object_guid_attr' => 'entity_guid',
				'subject_guid_attr' => 'entity_guid',
				'attributes' => array(
					'type' => 'annotation',
					'name' => 'likes',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: adding a star rating (annotation)
			 */
			'create:annotation:starrating' => array(
				'title' => elgg_echo('mechanics:create:annotation:starrating'),
				'events' => array(
					'create::annotation'
				),
				'object_guid_attr' => 'entity_guid',
				'attributes' => array(
					'type' => 'annotation',
					'name' => 'starrating',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: receiving a starrating
			 */
			'create:annotation:starrating:reverse' => array(
				'title' => elgg_echo('mechanics:create:annotation:starrating:reverse'),
				'events' => array(
					'create::annotation'
				),
				'object_guid_attr' => 'entity_guid',
				'subject_guid_attr' => 'entity_guid',
				'attributes' => array(
					'type' => 'annotation',
					'name' => 'starrating',
				),
				'settings' => array(
					'object_recur_max' => 1,
				)
			),
			/**
			 * Rule: updating a blog post
			 */
			'update:object:blog' => array(
				'title' => elgg_echo('mechanics:update:object:blog'),
				'events' => array(
					'update::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'blog',
				),
				// override global settings
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: updating a bookmark
			 */
			'update:object:bookmarks' => array(
				'title' => elgg_echo('mechanics:update:object:bookmarks'),
				'events' => array(
					'update::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'bookmarks',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: updating a page
			 */
			'update:object:page' => array(
				'title' => elgg_echo('mechanics:update:object:page'),
				'events' => array(
					'update::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'page',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: updating a top-level page
			 */
			'update:object:page_top' => array(
				'title' => elgg_echo('mechanics:update:object:page_top'),
				'events' => array(
					'update::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'page_top',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: updating a file
			 */
			'update:object:file' => array(
				'title' => elgg_echo('mechanics:update:object:file'),
				'events' => array(
					'update::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'file',
				//'simletype' => array('image', 'document'),
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: updating a wire post
			 */
			'update:object:thewire' => array(
				'title' => elgg_echo('mechanics:update:object:thewire'),
				'events' => array(
					'update::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'thewire',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: updating a group discussion topic
			 */
			'update:object:groupforumtopic' => array(
				'title' => elgg_echo('mechanics:update:object:groupforumtopic'),
				'events' => array(
					'update::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'groupforumtopic',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: update a group
			 */
			'update:group:default' => array(
				'title' => elgg_echo('mechanics:update:group:default'),
				'events' => array(
					'update::group'
				),
				'attributes' => array(
					'type' => 'group',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: update a comment (annotation)
			 */
			'update:annotation:comment' => array(
				'title' => elgg_echo('mechanics:update:annotation:comment'),
				'events' => array(
					'update::annotation'
				),
				'object_guid_attr' => 'entity_guid',
				'attributes' => array(
					'type' => 'annotation',
					'name' => 'generic_comment',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: update a reply to a discussion (annotation)
			 */
			'update:annotation:group_topic_post' => array(
				'title' => elgg_echo('mechanics:update:annotation:group_topic_post'),
				'events' => array(
					'update::annotation'
				),
				'object_guid_attr' => 'entity_guid',
				'attributes' => array(
					'type' => 'annotation',
					'name' => 'group_topic_post',
				),
				'settings' => array(
					'object_recur_max' => 0,
				)
			),
			/**
			 * Rule: updating a star rating (annotation)
			 */
			'update:annotation:starrating' => array(
				'title' => elgg_echo('mechanics:update:annotation:starrating'),
				'events' => array(
					'update::annotation'
				),
				'object_guid_attr' => 'entity_guid',
				'attributes' => array(
					'type' => 'annotation',
					'name' => 'starrating',
				),
				'settings' => array(
				)
			),
			/**
			 * Rule: logging in
			 */
			'login:user:default' => array(
				'title' => elgg_echo('mechanics:login:user:default'),
				'events' => array(
					'login::user'
				),
				'attributes' => array(
				),
				'settings' => array(
					'daily_recur_max' => 1,
				)
			),
			/**
			 * Rule: updating profile
			 */
			'profileupdate:user:default' => array(
				'title' => elgg_echo('mechanics:profileupdate:user:default'),
				'events' => array(
					'profileupdate::user'
				),
				'attributes' => array(
				),
				'settings' => array(
				),
				'settings' => array(
					'alltime_recur_max' => 1,
				)
			),
			/**
			 * Rule: completing profile
			 */
			'profilecomplete:user:default' => array(
				'title' => elgg_echo('mechanics:profileupdate:user:default'),
				'events' => array(
					'profileupdate::user'
				),
				'attributes' => array(
				),
				'settings' => array(
				),
				'settings' => array(
					'alltime_recur_max' => 1,
				),
				'callbacks' => [
					function(Rule $rule) {
						if (!elgg_is_active_plugin('profile_manager')) {
							return false;
						}

						$completeness = profile_manager_profile_completeness($rule->getSubject());
						if ($completeness['percentage_completeness'] >= 100) {
							return true;
						}

						return false;
					},
				],
			),
			/**
			 * Rule: updating profile avatar
			 */
			'profileiconupdate:user:default' => array(
				'title' => elgg_echo('mechanics:profileiconupdate:user:default'),
				'events' => array(
					'profileiconupdate::user'
				),
				'attributes' => array(
				),
				'settings' => array(
				)
			),
			/**
			 * Rule: joining a group
			 */
			'join:group:user' => array(
				'title' => elgg_echo('mechanics:join:group:user'),
				'events' => array(
					'join::group'
				),
				'attributes' => array(
				),
				'settings' => array(
					'object_recur_max' => 1
				)
			),
			/**
			 * Rule: leaving a group
			 */
			'leave:group:user' => array(
				'title' => elgg_echo('mechanics:leave:group:user'),
				'events' => array(
					'leave::group'
				),
				'attributes' => array(
				),
				'settings' => array(
					'object_recur_max' => 1
				)
			),
			/**
			 * Rule: friending someone
			 */
			'create:relationship:friend' => array(
				'title' => elgg_echo('mechanics:create:relationship:friend'),
				'events' => array(
					'create::relationship'
				),
				'object_guid_attr' => 'guid_two',
				'subject_guid_attr' => 'guid_one',
				'attributes' => array(
					'relationship' => 'friend',
				),
				'settings' => array(
					'object_recur_max' => 1
				)
			),
			/**
			 * Rule: being friended by someone
			 */
			'create:relationship:friend:reverse' => array(
				'title' => elgg_echo('mechanics:create:relationship:friend:reverse'),
				'events' => array(
					'create::relationship'
				),
				'object_guid_attr' => 'guid_one',
				'subject_guid_attr' => 'guid_two',
				'attributes' => array(
					'relationship' => 'friend',
				),
				'settings' => array(
					'object_recur_max' => 1
				)
			),
			/**
			 * Rule: removing a friend
			 */
			'delete:relationship:friend' => array(
				'title' => elgg_echo('mechanics:create:relationship:friend'),
				'events' => array(
					'delete::relationship'
				),
				'object_guid_attr' => 'guid_two',
				'subject_guid_attr' => 'guid_one',
				'attributes' => array(
					'relationship' => 'friend',
				),
				'settings' => array(
					'object_recur_max' => 1
				)
			),
			/**
			 * Rule: wall post
			 */
			'create:object:hjwall' => array(
				'title' => elgg_echo('mechanics:create:object:hjwall'),
				'events' => array(
					'create::object'
				),
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'hjwall',
				),
				// override global settings
				'settings' => array(
					'object_recur_max' => 1,
				),
			),
			/**
			 * Rule: receive a wall post
			 */
			'create:object:hjwall:reverse' => array(
				'title' => elgg_echo('mechanics:create:object:hjwall:reverse'),
				'events' => array(
					'create::object'
				),
				'subject_guid_attr' => 'container_guid', // entity owner will be identified automatically
				'attributes' => array(
					'type' => 'object',
					'subtype' => 'hjwall',
				),
				'settings' => array(
					'object_recur_max' => 0,
				),
				'callbacks' => [
					function(Rule $rule) {
						if ($rule->getSubject()->guid == $rule->getObject()->container_guid) {
							return false;
						}
						return true;
					},
				],
			),
		);

		if (is_array($return)) {
			return array_merge_recursive($return, $rules);
		} else {
			return $rules;
		}
	}

}
