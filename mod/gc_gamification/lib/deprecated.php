<?php

namespace hypeJunction\GameMechanics;

/**
 * Get rule definitions
 * @return array
 */
function get_scoring_rules($type = '') {
	return Policy::getRules($type);
}

/**
 * Get user score total in a given time frame
 *
 * @param ElggUser $user       User entity
 * @param int      $time_lower Lower time constraint
 * @param int      $time_upper Upper time constraint
 * @return int
 */
function get_user_score($user = null, $time_lower = null, $time_upper = null) {
	return Policy::getUserScore($user, $time_lower, $time_upper);
}

/**
 * Get a list of users ordered by their total score
 *
 * @param int $time_lower Lower time constraint
 * @param int $time_upper Upper time constraint
 * @return \ElggEntity[]|false
 */
function get_leaderboard($time_lower = null, $time_upper = null, $limit = 10, $offset = 0) {
	return Policy::getLeaderboard($time_lower, $time_upper, $limit, $offset);
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
function get_user_action_total($user, $rule, $time_lower = null, $time_upper = null) {
	return Policy::getUserActionTotal($user, $rule, $time_lower, $time_upper);
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
function get_user_recur_total($user, $rule, $time_lower = null, $time_upper = null) {

	return Policy::getUserRecurTotal($user, $rule, $time_lower, $time_upper);
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
function get_object_total($object, $user = null, $rule = null, $time_lower = null, $time_upper = null) {
	return Policy::getObjectRecurTotal($object, $user, $rule, $time_lower, $time_upper);
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
function get_object_recur_total($object, $user = null, $rule = null, $time_lower = null, $time_upper = null) {
	return Policy::getObjectRecurTotal($object, $user, $rule, $time_lower, $time_upper);
}

/**
 * Reward user with applicable badges
 *
 * @param ElggUser $user User entity
 * @return boolean
 */
function reward_user($user = null) {
	return Policy::rewardUser($user);
}

/**
 * Get site badges
 *
 * @param array $options ege* option
 * @return array|false
 */
function get_badges($options = array(), $getter = 'elgg_get_entities_from_metadata') {
	return Policy::getBadges($options, $getter);
}

/**
 * Get badges of a given type
 *
 * @param string $type
 * @param array $options
 * @param string $getter
 * @return array|false
 */
function get_badges_by_type($type = '', $options = array(), $getter = 'elgg_get_entities_from_metadata') {
	return Policy::getBadgesByType($type, $options, $getter);
}

/**
 * Get types of badges
 * @return array
 */
function get_badge_types() {
	return Policy::getBadgeTypes();
}

/**
 * Get badges that are required to uncover this badge
 *
 * @param int $badge_guid GUID of the badge
 * @return array|false
 */
function get_badge_dependencies($badge_guid) {
	return Policy::getBadgeDependencies($badge_guid);
}

/**
 * Get badge rules
 *
 * @param int $badge_guid GUID of the badge
 * @return array|false
 */
function get_badge_rules($badge_guid) {
	return Policy::getBadgeRules($badge_guid);
}
