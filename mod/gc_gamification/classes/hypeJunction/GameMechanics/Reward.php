<?php

namespace hypeJunction\GameMechanics;

use ElggObject;
use ElggRelationship;
use ElggUser;
use stdClass;

class Reward {

	/**
	 * Cached list of site badges
	 * @var array
	 */
	static $rewards;

	/**
	 * Keeping track of user points
	 * @var object
	 */
	static $rewardees;

	/**
	 * Current rewardee
	 * @var ElggUser
	 */
	protected $user;

	/**
	 * New badges awarded to the user
	 * @var array 
	 */
	protected $new_user_badges;

	/**
	 * Error messages
	 * @var array
	 */
	protected $errors;

	/**
	 * Messages
	 * @var array
	 */
	protected $messages;

	/**
	 * Log
	 * @var array
	 */
	protected $log;

	public function __construct($user = null) {

		if (!$user) {
			$user = elgg_get_logged_in_user_entity();
		}

		$this->getBadges();
		$this->user = $user;
		$this->addRewardee($user);
	}

	/**
	 * Get new badges awarded to the user
	 */
	public function getNewUserBadges() {
		return $this->new_user_badges;
	}

	/**
	 * Award badges the user is eligible for
	 * @param ElggUser $user
	 */
	public static function rewardUser($user = null) {

		if (!$user) {
			$user = elgg_get_logged_in_user_entity();
		}

		$reward = new Reward($user);

		$reward->setLog("Rewarding user with $user->guid");

		foreach (self::$rewards as $guid => $badge) {

			// check if the user already has this badge
			if (self::isClaimed($guid, $user->guid)) {
				$reward->setLog("Badge {$badge->entity->title} is already claimed by the user");
				continue;
			}

			// uncovering the badge requires the user to spend points
			// user will need to opt in and claim the badge
			if ($badge->entity->points_cost > 0) {
				$reward->setLog("Badge {$badge->entity->title} requires the user to spend {$badge->entity->points_cost} points; user's action is required");
				continue;
			}

			if (!self::isEligible($guid, $user->guid)) {
				$reward->setLog("User does not meet criteria for badge {$badge->entity->title}");
			}

			if (self::claimBadge($guid, $user->guid)) {
				$reward->setLog("User has been awarded a new badge {$badge->entity->title}");
				$reward->new_user_badges[] = $badge->entity;
			}
		}

		return $reward;
	}

	/**
	 * Check if the user meets all criteria to claim the badge
	 *
	 * @param int $badge_guid
	 * @param int $user_guid
	 * @return boolean
	 */
	public static function isEligible($badge_guid, $user_guid = null) {

		$user = get_entity($user_guid);
		if (!$user_guid) {
			$user = elgg_get_logged_in_user_entity();
		}

		if (!elgg_instanceof($user, 'user')) {
			return false;
		}
		
		if ($user->isAdmin()) {
			return true;
		}

		$badges = self::getBadges();

		$badge = $badges[$badge_guid];

		if (!$badge) {
			return false;
		}

		if (!isset(self::$rewardees[$user->guid])) {
			$rewardee = new stdClass();
			$rewardee->entity = $user;
		} else {
			$rewardee = self::$rewardees[$user->guid];
		}

		// Check if user has enough points to claim this badge
		$points_required = (int) $badge->entity->points_required;
		$points_cost = (int) $badge->entity->points_cost;

		if ($points_required || $points_cost) {
			if (!isset($rewardee->score)) {
				$rewardee->score = get_user_score($user);
				self::$rewardees[$user->guid] = $rewardee;
			}
			if ($points_required > 0 && $points_required > $rewardee->score) {
				return false;
			}
			if ($points_cost > 0 && ($points_required > $rewardee->score - $points_cost)) {
				return false;
			}
		}

		// Check if dependencies are fulfilled
		if (!isset($badge->dependencies)) {
			$badge->dependencies = get_badge_dependencies($badge_guid);
			self::$rewards[$badge_guid] = $badge;
		}

		if ($badge->dependencies) {
			foreach ($badge->dependencies as $dependency) {
				if (!self::isClaimed($dependency->guid, $user->guid)) {
					return false;
				}
			}
		}

		// Check if individual rules are fulfilled
		if (!isset($badge->rules)) {
			$badge->rules = get_badge_rules($badge_guid);
			self::$rewards[$badge_guid] = $badge;
		}

		foreach ($badge->rules as $rule) {
			$recurrences = get_user_recur_total($user, $rule->annotation_value);
			if ($recurrences < $rule->recurse) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the user has already claimed the badge
	 * @param int $badge_guid
	 * @param int $user_guid
	 * @return boolean
	 */
	public static function isClaimed($badge_guid, $user_guid = null) {
		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}
		return (check_entity_relationship($user_guid, 'claimed', $badge_guid) instanceof ElggRelationship);
	}

	/**
	 * 
	 * @param type $badge_guid
	 * @param type $user_guid
	 */
	public static function claimBadge($badge_guid, $user_guid = null) {

		$user = get_entity($user_guid);
		if (!$user) {
			$user = elgg_get_logged_in_user_entity();
		}

		$badges = self::getBadges();

		$badge = $badges[$badge_guid];

		if (!$badge) {
			return false;
		}

		if (!self::isEligible($badge_guid, $user->guid)) {
			return false;
		}

		if (add_entity_relationship($user->guid, 'claimed', $badge_guid)) {
			$points_cost = (int) $badge->entity->points_cost;
			if ($points_cost > 0 && !$user->isAdmin()) {
				// Add points and create a historical reference
				$id = create_annotation($user->guid, "gm_score", -$points_cost, '', $user->guid, ACCESS_PUBLIC);

				$history = new ElggObject();
				$history->subtype = Score::SUBTYPE;
				$history->owner_guid = $user->guid;
				$history->container_guid = $user->guid;
				$history->access_id = ACCESS_PRIVATE;
				$history->annotation_name = 'gm_score_history';
				$history->annotation_value = -$points_cost;
				$history->annotation_id = $id;

				$history->rule = 'badge:purchase';

				$history->event = 'purchase::object';
				$history->object_type = 'object';
				$history->object_id = $badge_guid;
				$history->object_ref = "{$history->object_type}:{$history->object_id}";

				$history->save();
			}

			if ($badge->entity->badge_type == 'status') {
				$user->gm_status = $badge_guid;
			}
			return true;
		}

		return false;
	}

	/**
	 * Award or deduct points outside of the event
	 * 
	 * @param int $amount
	 * @param string $note
	 * @param int $user_guid
	 * @return boolean
	 */
	public static function awardPoints($amount = 0, $note = '', $user_guid = null) {

		$user = get_entity($user_guid);
		if (!elgg_instanceof($user, 'user') || $user->isAdmin()) {
			return false;
		}

		$amount = (int) $amount;

		if ($amount === 0) {
			return false;
		}

		if (!create_annotation($user->guid, "gm_score_award", $amount, '', $user->guid, ACCESS_PUBLIC)) {
			return false;
		}

		$id = create_annotation($user->guid, "gm_score", $amount, '', $user->guid, ACCESS_PUBLIC);

		$history = new ElggObject();
		$history->subtype = Score::SUBTYPE;
		$history->owner_guid = $user->guid;
		$history->container_guid = $user->guid;
		$history->access_id = ACCESS_PRIVATE;
		$history->annotation_name = 'gm_score_history';
		$history->annotation_value = $amount;
		$history->annotation_id = $id;

		$history->rule = ($amount > 0) ? 'create:award' : 'create:penalty';

		$history->event = 'score_award::user'; // adding this so we can throttle in the future
		$history->object_type = 'user';
		$history->object_id = elgg_get_logged_in_user_guid();
		$history->object_ref = "{$history->object_type}:{$history->object_id}";

		$history->note = $note;
		
		return $history->save();
	}

	/**
	 * Get badges
	 * @return array
	 */
	protected function getBadges() {
		if (!isset(self::$rewards)) {
			$badges = get_badges();
			if ($badges) {
				foreach ($badges as $badge) {
					$ref = new stdClass();
					$ref->entity = $badge;
					self::$rewards[$badge->guid] = $ref;
				}
			}
		}

		return self::$rewards;
	}

	/**
	 * Add a rewardee to cache
	 * @param ElggUser $user
	 * @return object
	 */
	protected function addRewardee($user) {
		if (!isset(self::$rewardees[$user->guid])) {
			$rewardee = new stdClass();
			$rewardee->entity = $user;
			$rewardee->score = get_user_score($user);
			self::$rewardees[$user->guid] = $rewardee;
		}

		return self::$rewardees[$user->guid];
	}

	/**
	 * Set an error message
	 * @param string $error
	 */
	private function setError($error) {
		if (!isset($this->errors)) {
			$this->errors = array();
		}
		$this->errors[] = $error;
	}

	/**
	 * Get error messages
	 * @return array|false
	 */
	public function getErrors() {
		return (count($this->errors)) ? $this->errors : false;
	}

	/**
	 * Set a message
	 * @param string $message
	 */
	private function setMessage($message) {
		if (!isset($this->messages)) {
			$this->messages = array();
		}
		$this->messages[] = $message;
	}

	/**
	 * Get messages
	 * @return array|false
	 */
	public function getMessages() {
		return (count($this->messages)) ? $this->messages : false;
	}

	/**
	 * Log a message
	 * @param string $entry
	 */
	private function setLog($entry) {
		if (!isset($this->log)) {
			$this->log = array();
		}
		$this->log[] = $entry;
		elgg_log($entry, 'ERROR');
	}

	/**
	 * Get log messages
	 * @return array|false
	 */
	public function getLog() {
		return (count($this->log)) ? $this->log : false;
	}

}
