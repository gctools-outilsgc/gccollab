<?php

	$user_fullname = get_input('name');
	$user_email = get_input('email');
	$user_department = get_input('depart');
	$user_reason = get_input('reason');
	$user_message = get_input('message');

	$site = elgg_get_site_entity();
	$helpdesk_email = elgg_get_plugin_setting('email', 'contactform');

	$subject = "{$user_reason} [$user_department]";
	$body = "{$user_fullname} <br/>";
	$body .= "{$user_email} <br/>";
	$body .= "{$user_department} <br/>";
	$body .= "{$user_reason} <br/>";
	$body .= "{$user_message} <br/>";

	$success = true;

	if (!$user_fullname) {
		register_error(elgg_echo('contactform:Errname'));
		$success = false;
	}

	if (!$user_email) {
		register_error(elgg_echo('contactform:Erremail'));
		$success = false;
	}

	if (!$user_department) {
		register_error(elgg_echo('contactform:Errdepart'));
		$success = false;
	}

	if (!$user_reason || strcmp('Select...', $user_reason) == 0 || strcmp('Choisir...', $user_reason) == 0) {
		register_error(elgg_echo('contactform:Errreason'));
		$success = false;
	}

	if (!$user_message) {
		register_error(elgg_echo('contactform:Errmess'));
		$success = false;
	}

	if (!\Beck24\ReCaptcha\validate_recaptcha()) {
        register_error(elgg_echo('elgg_recaptcha:message:fail'));
        $success = false;
    }

	if ($success === true) {
		// send email to jeffrey outram
		elgg_send_email($site->email, $helpdesk_email, $subject, $body);

		// send email to recipient to complate transaction
		elgg_send_email($site->email, $user_email, $subject, $body);

		system_message(elgg_echo('contactform:thankyoumsg'));
	}