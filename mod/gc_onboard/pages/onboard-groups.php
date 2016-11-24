<?php
/*
* onboard-groups.php
* Group page for user to select groups
*/
gatekeeper();
elgg_push_context('groups-onboard');

$content = '<div class="clearfix" id="wb-cont">' . elgg_view('groups-steps/stepOne') . '</div><style>#wb-cont .btn-primary {background-color: #567784 !important;}</style>';
elgg_require_js('jquery.form');
$script = elgg_view('js/onboardMe');
// draw page
echo elgg_view_page(elgg_echo('groupmodule:title'), $content . $script);
?>
