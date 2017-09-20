<?php

// Unregister entity subtypes
use hypeJunction\GameMechanics\Badge;
use hypeJunction\GameMechanics\BadgeRule;
use hypeJunction\GameMechanics\Score;

// Register subtype classes
$subtypes = [
	Badge::SUBTYPE,
	BadgeRule::SUBTYPE,
	Score::SUBTYPE,
];

foreach ($subtypes as $subtype) {
	update_subtype('object', $subtype);
}
