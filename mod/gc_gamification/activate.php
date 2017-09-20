<?php

require_once __DIR__ . '/autoloader.php';

use hypeJunction\GameMechanics\Badge;
use hypeJunction\GameMechanics\BadgeRule;
use hypeJunction\GameMechanics\Score;

// Register subtype classes
$subtypes = [
	Badge::SUBTYPE => Badge::class,
	BadgeRule::SUBTYPE => BadgeRule::class,
	Score::SUBTYPE => Score::class,
];

foreach ($subtypes as $subtype => $class) {
	if (!update_subtype('object', $subtype, $class)) {
		add_subtype('object', $subtype, $class);
	}
}