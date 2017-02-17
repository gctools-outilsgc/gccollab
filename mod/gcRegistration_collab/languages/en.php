<?php

$english = array(

	'gcRegister:occupation' => "Occupation",
	'gcRegister:occupation:academic' => "Academic",
	'gcRegister:occupation:student' => "Student",
	'gcRegister:occupation:federal' => "Federal Government",
	'gcRegister:occupation:provincial' => "Provincial/Territorial Government",
	'gcRegister:occupation:municipal' => "Municipal Government",
	'gcRegister:occupation:international' => "International/Foreign Government",
	'gcRegister:occupation:community' => "Community/Non-profit",
	'gcRegister:occupation:business' => "Business",
	'gcRegister:occupation:media' => "Media",
	'gcRegister:occupation:other' => "Other",

	'gcRegister:occupation:university' => "University or College",
	'gcRegister:occupation:department' => "Departments/Agencies",
	'gcRegister:occupation:province' => "Province or Territory",

	'gcRegister:department' => 'Organization',
	'gcRegister:university' => 'University',
	'gcRegister:college' => 'College',
	'gcRegister:province' => 'Province/Territory',
	'gcRegister:ministry' => 'Ministry',

	// labels
    'gcRegister:form' => 'Registration form',
	'gcRegister:email' => 'Enter your e-mail',
	'gcRegister:email_secondary' => 'Confirm your e-mail',
	'gcRegister:username' => 'Your username (auto-generated)',
	'gcRegister:password_initial' => 'Password',
	'gcRegister:password_secondary' => 'Confirm your password',
	'gcRegister:display_name' => 'Display Name',
	'gcRegister:display_name_notice' => 'Please enter your first and last name, as you are known in the workplace/school. As per the Terms and Conditions, your display name must reflect your real name. Pseudonyms are not allowed.',
	'gcRegister:please_enter_email' => 'Please enter email',
	'gcRegister:please_enter_name' => 'Please enter display name',
	'gcRegister:department_name' => 'Enter your Department',
	'gcRegister:register' => 'Register',
	'gcRegister:other' => 'Please enter the name of your organization',

	// error messages on the form
	'gcRegister:failedMySQLconnection' => 'Unable to connect to the database',
	'gcRegister:invalid_email' => 'Invalid email',
	'gcRegister:invalid_email_link' => '<a href="#email">Invalid email</a>',
	'gcRegister:empty_field' => 'Empty field',
	'gcRegister:mismatch' => 'Mismatch',
	'gcRegister:make_selection' => 'Please make a selection',
	'gcRegister:EmptyPassword' => '<a href="#password1">The password fields cannot be empty</a>',
	'gcRegister:PasswordMismatch' => '<a href="#password1">Passwords must match</a>',
	'gcRegister:FederalNotSelected' => '<a href="#federal">Organization not selected</a>',
	'gcRegister:InstitutionNotSelected' => '<a href="#institution">Institution not selected</a>',
	'gcRegister:UniversityNotSelected' => '<a href="#university">University not selected</a>',
	'gcRegister:CollegeNotSelected' => '<a href="#college">College not selected</a>',
	'gcRegister:ProvincialNotSelected' => '<a href="#provincial">Province/Territory not selected</a>',
	'gcRegister:MinistryNotSelected' => '<a href="#ministry">Ministry not selected</a>',
	'gcRegister:OtherNotSelected' => '<a href="#other">Organization not entered</a>',

	// notice
	'gcRegister:email_notice' => '<h2 class="h2"></h2>',

	'gcRegister:terms_and_conditions' => 'I have read, understood, and agree to the <a href="/terms" target="_blank">Terms and Conditions of Use</a>.',
	'gcRegister:validation_notice' => '<b>NOTE:</b> You will be unable to login to GCconnex until you have received a validation email.',
	'gcRegister:tutorials_notice' => '<a href="http://www.gcpedia.gc.ca/wiki/Tutorials_on_GC2.0_Tools_/_Tutoriels_sur_les_outils_GC2.0/GCconnex">GCconnex tutorials</a>',
	
	// error messages that pop up
	'gcRegister:toc_error' => '<a href="#toc2">Terms and Conditions of Use must be accepted</a>',
	'gcRegister:email_in_use' => 'This email address has already been registered',
	'gcRegister:password_mismatch' => '<a href="#password">Passwords do not match</a>', 
	'gcRegister:password_too_short' => '<a href="#password">Password must contain minimum of 6 characters</a>',
	'gcRegister:email_mismatch' => '<a href="#email">Emails do not match</a>',
	'gcRegister:display_name_is_empty' => '<a href="#name">Display name cannot be empty</a>',
);

add_translation("en", $english);