hypeGameMechanics
=================
![Elgg 2.3](https://img.shields.io/badge/Elgg-2.3-orange.svg?style=flat-square)

User points and game mechanics for Elgg

## Features

hypeGameMechanics allows your users to:

* Earn points actively for performing certain actions / activities on the site
* Earn points passively by receiving interactions on their content items (e.g. ratings, comments, likes)
* Claim badges when a set of defined criteria are met

## Acknowledgements

 * Upgrade to Elgg 1.12 was sponsored by Fernando Bacelar

## Introduction

This plugin is governed by a set of `rules` - conditions that describe an event (action).
Each rule has a unique name and defines a set of conditions that should be met
for the points to be awarded or deducted.

### Rule Definitions

A set of rules can be extended / modified via the ```'get_rules','gm_score'``` hook.

Each rule definition accepts the following parameters:

```php
$rules = array(
	'events' => array(
		// Adding a blog post
		'create:object:blog' => array(
			'title' => elgg_echo('mechanics:create:object:blog'),
			'description' => '',
			// Events, to which this rule applies
			'events' => array(
				'publish::object'
			),
			// Positive or negative number of points to apply
			// Rule will be skipped if this value is 0
			// The option to set the score will also appear in plugin settings
			'score' => 0,
			// Entity attribute that should be used to determine the object
			'object_guid_attr' => 'guid',
			// Entity attribute that should be used to determine the user
			// who should receive points
			'subject_guid_attr' => 'owner_guid',
			// Attributes to validate
			// name => value pairs, where name is an attribute or metadata,
			// and value is a value or an array of values
			'attributes' => array(
				'type' => 'object',
				'subtype' => 'blog',
			),
			// A list of callback functions to trigger to validate the
			// applicability of this event
			// Callback functions will receive Rule object as parameter
			'callbacks' => array(
			),
			// override global settings for this rule
			'settings' => array(
				'daily_max' => 0,
				'daily_action_max' => 0,
				'alltime_action_max' => 0,
				'daily_recur_max' => 0,
				'alltime_recur_max' => 0,
				'object_recur_max' => 1,
				'daily_object_max' => 0,
				'alltime_object_max' => 0,
				'action_object_max' => 0,
				'allow_negative_total' => true,
			),
		)
	)
);
```


### Throttling

Global settings are exposed in the plugin settings, but you also override those for individual rules.

* ```daily_max``` - maximum number of points the user can accumulate each day with all rules
* ```daily_action_max``` - maximum number of points the user can accumulate each day with a given rule
* ```alltime_action_max``` - maximum number of points the user can accumulate with a given rule
* ```daily_recur_max``` - maximum number of times the points can be collected each day with a given rule
* ```alltime_recur_max``` - maximum number of times the points can be collected with a given rule
* ```object_recur_max``` - maximum number of times the points can be collected for a single object with a given rule
This can be helpful to throttle rules that apply to multiple events, such as
```'create','object'``` and ```'publish','object'```. Another example would be
likes that only apply once to an object
* ```daily_object_max``` - maximum number of points the user can collect each day by performing actions on a single object
* ```alltime_object_max``` - maximum number of points the user can collect by performing actions on a single object
* ```action_object_max``` - maximum number of points the user can collect with a given rule on a single object
For example, you can limit the maximum number of points for commenting on an object

### Badges

Badges are rewards given to users upon fulfillment of predefined conditions.
Each badge can be conditioned with 4 criteria:
* A minimum number of points the user should have
* Up to 10 rule definitions with a number of recurrences for each rule
* A number of points a user should spend to uncover the badge
* Other badges that are required before a badge can be claimed

There are 3 types of badges:
* ```status``` -  status badges will define current user status on the site
* ```experience``` - experience badges will be displayed on the user profile to
symbolize achievements/contributions
* ```surprise``` - surprise badges will not be visible in the badge gallery

### Notes

* Administrators are exempt from point rules


## Examples

To understand rule definitions, review various preset rules in ```setup_scoring_rules()```

Here as some additional examples:

1. Award points when user updates their profile with a location

```php
$rules['events']['profileupdate:user:location'] = array(
	'title' => elgg_echo('mechanics:profileupdate:user:location'),
	'events' => array(
		'profileupdate::user'
	),
	'attributes' => array(
		'location' => Rule::NOT_EMPTY
	),
	'settings' => array(
		'object_recur_max' => 1
	)
);
```


## Screenshots

![alt text](https://raw.github.com/hypeJunction/hypeGameMechanics/master/screenshots/gallery.png "Badges gallery")
![alt text](https://raw.github.com/hypeJunction/hypeGameMechanics/master/screenshots/balance.png "Points balance")
![alt text](https://raw.github.com/hypeJunction/hypeGameMechanics/master/screenshots/leaderboard.png "Leaderboard")
![alt text](https://raw.github.com/hypeJunction/hypeGameMechanics/master/screenshots/badge_progress.png "Progress")
![alt text](https://raw.github.com/hypeJunction/hypeGameMechanics/master/screenshots/badge_awarded.png "Badge awarded")
![alt text](https://raw.github.com/hypeJunction/hypeGameMechanics/master/screenshots/penalty.png "Custom award")
![alt text](https://raw.github.com/hypeJunction/hypeGameMechanics/master/screenshots/admin_throttling.png "Admin settings")
![alt text](https://raw.github.com/hypeJunction/hypeGameMechanics/master/screenshots/admin_score.png "Scoring rules")