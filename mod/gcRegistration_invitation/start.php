<?php

elgg_register_event_handler('init', 'system', 'gcRegistration_invitation_init');

function gcRegistration_invitation_init() {
	$action_path = elgg_get_plugins_path() . 'gcRegistration_invitation/actions/gcRegistration_invitation';
}
