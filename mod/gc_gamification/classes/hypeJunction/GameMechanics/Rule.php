<?php

namespace hypeJunction\GameMechanics;

use ElggObject;
use ElggUser;
use stdClass;

class Rule {

	/**
	 * Unique name of the rule
	 * @var string
	 */
	protected $name;

	/**
	 * Elgg event that invoked the rule
	 * @var string "$event::$type"
	 */
	protected $event;

	/**
	 * Object of the rule
	 * @var object ElggEntity|ElggRelationship|ElggAnnotation|ElggMetadata
	 */
	protected $object;

	/**
	 * Original object of the rule
	 * @var object ElggRelationship|ElggAnnotation|ElggMetadata
	 */
	protected $extender;

	/**
	 * Description of the rule
	 * @var array
	 */
	protected $options;

	/**
	 * Number of positive or negative points if all conditions for this rule are met
	 * @var integer 
	 */
	protected $score;

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

	/**
	 * Flag to terminate events early
	 * @var type
	 */
	protected $terminate;

	/**
	 * Subject of the rule (logged in user)
	 * @var ElggUser
	 */
	protected $subject;

	/**
	 * Cache of current totals for the user
	 * @var object
	 */
	protected static $totals;

	/**
	 * Plugin settings cache
	 * @var object
	 */
	protected static $settings;

	/**
	 * Cache to prevent nested actions from creating multiple scores
	 * @var array
	 */
	protected static $eventThrottle;

	/**
	 * Magic keyword to check if attributes were set
	 */
	const NOT_EMPTY = '__NOT_EMPTY__';

	/**
	 * Create a new instance
	 */
	function __construct() {
		self::getSettings();
	}

	/**
	 * Apply rule conditions to the entity
	 *
	 * @param object $entity ElggEntity|ElggAnnotation|ElggMetadata|ElggRelationship
	 * @param array $options
	 * @param string $event
	 * @return Rule
	 */
	public static function applyRule($entity, $options, $event) {

		$as = access_get_show_hidden_status();
		access_show_hidden_entities(true);
		$ia = elgg_set_ignore_access(true);

		$Rule = new Rule();
		$Rule->setName($options['name']);
		$Rule->setOptions($options);

		if (isset($options['subject_guid_attr'])) {
			$attr = $options['subject_guid_attr'];
			$guid = $entity->$attr;
			$subject = get_entity($guid);
			if (elgg_instanceof($subject) && !elgg_instanceof($subject, 'user')) {
				$subject = $subject->getOwnerEntity();
			}
		}

		if (empty($subject)) {
			$subject = elgg_get_logged_in_user_entity();
		}

		if (!$subject instanceof \ElggUser) {
			$Rule->setLog('Subject is not a valid user entity; skipping');
			return $Rule;
		}

		if ($subject->isAdmin()) {
			$Rule->setLog('Subject is an admin; skipping');
			return $Rule;
		}

		$Rule->setSubject($subject);

		if (isset($options['object_guid_attr'])) {
			$attr = $options['object_guid_attr'];
			$guid = $entity->$attr;
			$object = get_entity($guid);
		}
		if (empty($object)) {
			$object = $entity;
		} else {
			$Rule->extender = $entity;
		}

		$Rule->setObject($object);
		$Rule->setEvent($event);

		$return = $Rule->apply();

		elgg_set_ignore_access($ia);
		access_show_hidden_entities($as);

		return $return;
	}

	/**
	 * Set the rule name
	 * @param string $name Unique name identifying the action
	 * @return string
	 */
	public function setName($name) {
		$this->name = $name;
		return $this->getName();
	}

	/**
	 * Get rule name
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get score that should apply for this
	 * @return int
	 */
	public function getScore() {
		if (!isset($this->score)) {
			$this->score = (int) elgg_get_plugin_setting($this->getName(), 'gc_gamification');
			if (is_null($this->score)) {
				$this->score = (int) $this->getOptions('score');
			}
		}
		return $this->score;
	}

	/**
	 * Set an object to which this rule is being applied
	 * @param object $entity ElggEntity|ElggMetadata|ElggAnnotation|ElggRelationship
	 * @return object
	 */
	public function setObject($entity) {
		$this->object = $entity;
		return $this->getObject();
	}

	/**
	 * Get an object to which this rule is being applied
	 * @return object
	 */
	public function getObject() {
		return $this->object;
	}

	/**
	 * Set the Elgg event that invoked this rule
	 * @param string $event "$event::$type"
	 * @return string
	 */
	public function setEvent($event = '') {
		$this->event = $event;
		return $this->getEvent();
	}

	/**
	 * Get the Elgg event that invoked this rule
	 * @return type
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * Set rule options
	 *
	 * @param array $options
	 * @uses $options['title']		Friendly title
	 * @uses $options['description']Description
	 * @uses $options['events']		Elgg events this rule applies
	 * @uses $options['attributes']	Attributes and metadata to validate
	 * @uses $options['settings']	Settings to override global throttling settings
	 * @uses $options['callbacks']	Custom callback functions to validate the applicability of the rule
	 * 
	 * @return array
	 */
	public function setOptions($options) {
		$this->options = $options;
		return $this->options;
	}

	/**
	 * Get options for this rule
	 * @param string $key Array key to return
	 * @return array
	 */
	public function getOptions($key = '') {
		return ($key) ? elgg_extract($key, $this->options, array()) : $this->options;
	}

	/**
	 * Apply the rule
	 * @return Rule
	 */
	protected function apply() {
		
		$name = $this->getName();
		$event = $this->event;

		$score = $this->getScore();
		
		$user = $this->getSubject();
		$object = $this->getObject();

		$object_type = $object->getType();
		$object_id = (isset($object->guid)) ? $object->guid : $object->id;

		$events = $this->getOptions('events');
		if (!is_array($events)) {
			$events = (array) $events;
		}

		// Check if current event applies to the rule
		if (!$name || !in_array($event, $events)) {
			return $this;
		}

		$this->setLog("Apply rule '$name' on '$event' to $object_type with id $object_id");

		if (!$score) {
			$this->setLog("Score is set to 0; skipping");
			return $this;
		}

		// Check throttling conditions
		if (!$this->validateThrottlingConditions()) {
			$this->setLog("Rule has been throttled; quitting");
			return $this;
		}

		// Validate object attributes and metadata
		if (!$this->validateAttributes()) {
			$this->setLog("Attributes can't validate; quitting");
			return $this;
		}

		// Validate custom conditions by calling callback functions
		if (!$this->validateCallbackConditions()) {
			$this->setLog("Callback validation failed; quitting");
			return $this;
		}

		// Validate that the score is not negative, or that we can proceed
		if (!$this->validateNegativeScore()) {
			$this->setLog("Negative score not allowed; quitting");
			return $this;
		}

		// Add points and create a historical reference
		$id = create_annotation($user->guid, "gm_score", $score, '', $user->guid, ACCESS_PUBLIC);

		$history = new ElggObject();
		$history->subtype = Score::SUBTYPE;
		$history->owner_guid = $user->guid;
		$history->container_guid = $user->guid;
		$history->access_id = ACCESS_PRIVATE;
		$history->annotation_name = 'gm_score_history';
		$history->annotation_value = $score;
		$history->annotation_id = $id;

		$history->rule = $name;

		$history->event = $this->event;
		$history->object_type = $object->getType();
		$history->object_id = (isset($object->guid)) ? $object->guid : $object->id;
		$history->object_ref = "{$history->object_type}:{$history->object_id}";

		$success = $history->save();

		if ($success) {
			$this->updateTotals();
			$this->rewardUser();
			$this->setLog("$score points applied");
			if ($user->guid == elgg_get_logged_in_user_guid()) {
				$rule_rel = elgg_echo("mechanics:{$name}");
				$reason = elgg_echo('mechanics:score:earned:reason', array(strtolower($rule_rel)));
				if ($score > 0) {
					$this->setMessage(elgg_echo('mechanics:score:earned:for', array($score, $reason)));
				} else {
					$this->setMessage(elgg_echo('mechanics:score:lost:for', array($score, $reason)));
				}
			} else {
				// @todo: send notification instead?
			}
		}

		return $this;
	}

	/**
	 * Reward user with applicable badges
	 * @return mixed
	 */
	public function rewardUser() {
		return Policy::rewardUser($this->getSubject());
	}

	/**
	 * Validate object attributes
	 * @return boolean
	 */
	protected function validateAttributes() {

		$object = isset($this->extender) ? $this->extender : $this->getObject();
		$attributes = $this->getOptions('attributes');
		if (is_array($attributes)) {
			foreach ($attributes as $attribute => $expected_value) {
				switch ($attribute) {
					case 'type' :
						$value = $object->getType();
						break;

					case 'subtype' :
						$value = $object->getSubtype();
						break;

					default :
						$value = $object->$attribute;
						break;
				}

				if ($expected_value == self::NOT_EMPTY) {
					$result = (!empty($expected_value));
				} else {
					if (is_array($expected_value)) {
						$result = in_array($value, $expected_value);
					} else {
						$result = ($value == $expected_value);
					}
				}

				if ($result === false) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Validate the applicability of this rule via callback functions
	 * @return boolean
	 */
	protected function validateCallbackConditions() {

		$callbacks = $this->getOptions('callbacks');
		if (is_array($callbacks)) {
			foreach ($callbacks as $callback) {
				if (is_callable($callback)) {
					$result = call_user_func($callback, $this);
					if (!$result) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Check if scoring needs to be throttled
	 * @return boolean
	 */
	protected function validateThrottlingConditions() {

		$name = $this->getName();
		$score = $this->getScore();
		$totals = $this->calculateTotals();
		$subject = $this->getSubject();
		$action_totals = $totals->actions[$name][$subject->guid];

		$daily_max = $this->getSetting('daily_max');
		if ($daily_max && $action_totals->daily_total + $score > $daily_max) {
			$this->setLog("Daily max exceeded");
			return false;
		}


		$daily_action_max = $this->getSetting('daily_action_max');
		if ($daily_action_max && $action_totals->daily_action_total + $score > $daily_action_max) {
			$this->setLog("Daily action max exceeded");
			return false;
		}

		$alltime_action_max = $this->getSetting('alltime_action_max');
		if ($alltime_action_max && $action_totals->alltime_action_total + $score > $alltime_action_max) {
			$this->setLog("All time max for this action exceeded");
			return false;
		}

		$object_recur_max = $this->getSetting('object_recur_max');
		if ($object_recur_max && $action_totals->object_recur_total + 1 > $object_recur_max) {
			$this->setLog("Recurrences for this action on this object are exceeded");
			return false;
		}

		$daily_recur_max = $this->getSetting('daily_recur_max');
		if ($daily_recur_max && $action_totals->daily_recur_total + 1 > $daily_recur_max) {
			$this->setLog("Daily recurrences for this action exceeded");
			return false;
		}

		$alltime_recur_max = $this->getSetting('alltime_recur_max');
		if ($alltime_recur_max && $action_totals->alltime_recur_total + 1 > $alltime_recur_max) {
			$this->setLog("All time recurrences for this action exceeded");
			return false;
		}

		$action_object_max = $this->getSetting('action_object_max');
		if ($action_object_max && $action_totals->action_object_total + $score > $action_object_max) {
			$this->setLog("Action max for this object exceeded");
			return false;
		}

		$daily_object_max = $this->getSetting('daily_object_max');
		if ($daily_object_max && $action_totals->daily_object_total + $score > $daily_object_max) {
			$this->setLog("Daily max for this object exceeded");
			return false;
		}

		$alltime_object_max = $this->getSetting('alltime_object_max');
		if ($alltime_object_max && $action_totals->alltime_object_total + $score > $alltime_object_max) {
			$this->setLog("All time max for this object exceeded");
			return false;
		}

		return true;
	}

	/**
	 * Check if the score after action becomes negative
	 * @return boolean
	 */
	protected function validateNegativeScore() {

		$score = $this->getScore();
		$allow_negative_total = $this->getSetting('allow_negative_total');
		$totals = self::$totals;

		if ($totals->alltime_total + $score < 0) {
			if ($allow_negative_total === false) {
				$this->setError(elgg_echo('mechanics:negativereached'));
				$this->terminate = true; // Terminate and prevent event from completing
				return false;
			}
		}

		return true;
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

	/**
	 * Check if the Elgg event should be terminated
	 * @return boolean
	 */
	public function terminateEvent() {
		return ($this->terminate === true);
	}

	/**
	 * Set the subject user (user to receive the points)
	 * @param ElggUser $user
	 * @return ElggUser
	 */
	protected function setSubject($user = null) {
		if (!elgg_instanceof($user)) {
			$user = elgg_get_logged_in_user_entity();
		}
		$this->subject = $user;
		return $this->subject;
	}

	/**
	 * Get the subject user
	 * @return type
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Get plugin settings
	 * @return object
	 */
	private static function getSettings() {

		if (isset(self::$settings)) {
			return self::$settings;
		}

		$settings = new stdClass();

		// Total number of points a user can collect per day
		$settings->daily_max = (int) elgg_get_plugin_setting('daily_max', 'gc_gamification');

		// Total number of points a user can collect per action per day
		$settings->daily_action_max = (int) elgg_get_plugin_setting('daily_action_max', 'gc_gamification');

		// Total number of points a user can collect for a given action
		$settings->alltime_action_max = (int) elgg_get_plugin_setting('alltime_action_max', 'gc_gamification');

		// A number of recurring times that points can be collected for an action per day
		$settings->daily_recur_max = (int) elgg_get_plugin_setting('daily_recur_max', 'gc_gamification');

		// A number of recurring times that points can be collected for a given action
		$settings->alltime_recur_max = (int) elgg_get_plugin_setting('alltime_recur_max', 'gc_gamification');

		// A cumulative number of points that can be collected on an object per day
		$settings->daily_object_max = (int) elgg_get_plugin_setting('daily_object_max', 'gc_gamification');

		// A cumulative number of points that can be collected on an object
		$settings->alltime_object_max = (int) elgg_get_plugin_setting('alltime_object_max', 'gc_gamification');

		// A number of points that can be collected on an object by a single action
		$settings->action_object_max = (int) elgg_get_plugin_setting('action_object_max', 'gc_gamification');

		// Whether an action should be allowed to propagate if the number of points to become negative
		$settings->allow_negative_total = (bool) elgg_get_plugin_setting('allow_negative_total', 'gc_gamification');

		self::$settings = $settings;

		return self::$settings;
	}

	protected function getSetting($setting_name) {

		$global = self::getSettings();
		$settings = $this->getOptions('settings');

		if (isset($settings[$setting_name])) {
			return $settings[$setting_name];
		}

		return $global->$setting_name;
	}

	/**
	 * Calculate and cache totals
	 * @return object
	 */
	private function calculateTotals() {

		$name = $this->getName();
		$subject = $this->getSubject();

		$totals = (isset(self::$totals)) ? self::$totals : new stdClass();

		if (!isset($totals->alltime_total)) {
			$totals->alltime_total = self::getUserTotal();
		}
		if (!isset($totals->daily_total)) {
			$end = time();
			$totals->daily_total = self::getUserTotal($end - 86400, $end);
		}

		if (!isset($totals->actions)) {
			$totals->actions = array();
		}

		if (!isset($totals->actions[$name][$subject->guid])) {
			$action_totals = new stdClass();
			$end = time();

			$action_totals->daily_action_total = $this->getUserActionTotal($name, $end - 86400, $end);
			$action_totals->alltime_action_total = $this->getUserActionTotal($name);

			$action_totals->daily_recur_total = $this->getUserRecurTotal($name, $end - 86400, $end);
			$action_totals->alltime_recur_total = $this->getUserRecurTotal($name);
			$action_totals->object_recur_total = $this->getObjectRecurTotal($name);

			$action_totals->action_object_total = $this->getObjectTotal($name);
			$action_totals->daily_object_total = $this->getObjectTotal(null, $end - 86400, $end);
			$action_totals->alltime_object_total = $this->getObjectTotal(null);

			$totals->actions[$name][$subject->guid] = $action_totals;
		}

		self::$totals = $totals;
		return self::$totals;
	}

	/**
	 * Update totals cache on success
	 * @return array
	 */
	private function updateTotals() {

		$name = $this->getName();
		$subject = $this->getSubject();
		$score = $this->getScore();

		self::$totals->alltime_total += $score;
		self::$totals->daily_total += $score;

		$action_totals = self::$totals->actions[$name][$subject->guid];

		$action_totals->daily_action_total += $score;
		$action_totals->alltime_action_total += $score;

		$action_totals->daily_recur_total++;
		$action_totals->alltime_recur_total++;
		$action_totals->object_recur_total++;

		$action_totals->action_object_total += $score;
		$action_totals->daily_object_total += $score;
		$action_totals->alltime_object_total += $score;

		$totals->actions[$name][$subject->guid] = $action_totals;

		self::$totals->actions[$name][$subject->guid] = $action_totals;
		return self::$totals;
	}

	public function getUserTotal($time_lower = null, $time_upper = null) {
		return Policy::getUserScore($this->getSubject(), $time_lower, $time_upper);
	}

	public function getUserActionTotal($name, $time_lower = null, $time_upper = null) {
		return Policy::getUserActionTotal($this->getSubject(), $name, $time_lower, $time_upper);
	}

	public function getUserRecurTotal($name, $time_lower = null, $time_upper = null) {
		return Policy::getUserRecurTotal($this->getSubject(), $name, $time_lower, $time_upper);
	}

	public function getObjectTotal($name = null, $time_lower = null, $time_upper = null) {
		return Policy::getObjectTotal($this->getObject(), $this->getSubject(), $name, $time_lower, $time_upper);
	}

	public function getObjectRecurTotal($name, $time_lower = null, $time_upper = null) {
		return Policy::getObjectRecurTotal($this->getObject(), $this->getSubject(), $name, $time_lower, $time_upper);
	}

}
