<?php

$english = array(

	'gcRegister:occupation' => "Occupation",
	'gcRegister:occupation:student' => "Student",
	'gcRegister:occupation:academic' => "Academic",
	'gcRegister:occupation:public_servant' => "Public Servant",
	'gcRegister:occupation:university' => "University or College",
	'gcRegister:occupation:department' => "Department or Agency",

	// labels
    'gcRegister:form' => 'Registration form',
	'gcRegister:email_initial' => 'Enter your e-mail',
	'gcRegister:email_secondary' => 'Confirm your e-mail',
	'gcRegister:username' => 'Your username (auto-generated)',
	'gcRegister:password_initial' => 'Password',
	'gcRegister:password_secondary' => 'Confirm your Password',
	'gcRegister:display_name' => 'Display name',
	'gcRegister:display_name_notice' => 'Please enter your first and last name, as you are known in the workplace/school. As per the Terms and Conditions, your display name must reflect your real name. Pseudonyms are not allowed.',
	'gcRegister:please_enter_email' => 'Please enter email',
	'gcRegister:department_name' => 'Enter your Department',
	'gcRegister:register' => 'Register',

	// error messages on the form
	'gcRegister:failedMySQLconnection' => 'Unable to connect to the database',
	'gcRegister:invalid_email' => '<a href="#email_initial">Invalid email</a>',
    'gcRegister:invalid_email2' => 'Invalid email',
	'gcRegister:empty_field' => 'empty field',
	'gcRegister:mismatch' => 'mismatch',

	// notice
	'gcRegister:email_notice' => '

		<h2 class="h2"></h2>

	',
	'gcRegister:terms_and_conditions' => 'I have read, understood, and agree to the <a href="/terms">terms and conditions of use</a>.',
	'gcRegister:validation_notice' => '<b>NOTE:</b> You will be unable to login to GCconnex until you have received a validation email.',
	'gcRegister:tutorials_notice' => '<a href="http://www.gcpedia.gc.ca/wiki/Tutorials_on_GC2.0_Tools_/_Tutoriels_sur_les_outils_GC2.0/GCconnex">GCconnex tutorials</a>',
	
	// error messages that pop up
	'gcRegister:toc_error' => '<a href="#toc2">Terms and Conditions of Use must be accepted</a>',
	'gcRegister:email_in_use' => 'This email address has already been registered',
	'gcRegister:password_mismatch' => '<a href="#password">Passwords do not match</a>', 
	'gcRegister:password_too_short' => '<a href="#password">Password must contain minimum of 6 characters</a>',
	'gcRegister:email_mismatch' => '<a href="#email_initial">Emails do not match</a>',
	'gcRegister:display_name_is_empty' => '<a href="#name">Display name cannot be empty</a>',
	'gcRegister:department' => 'Organization',
    
);

add_translation("en", $english);