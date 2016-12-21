<?php

$english = array(

	// user validation
	'email:validate:subject' => "Please validate account for %s",
	'email:validate:body' => "Welcome to GCcollab, to complete your registration, please validate the account registered under %s by clicking on this link: %s ",

	// friend request & approval
	'friend_request:newfriend:subject' => "%s wants to be your colleague!", 
	'friend_request:newfriend:body' => "%s wants to be your colleague and is waiting for you to approve the request. Login now to approve the request! <br/>You can view your pending colleague requests : %s ",

	'friend_request:approve:subject' => "%s approved your colleague's request",
	'friend_request:approve:message' => "<a href='%s'>%s</a> approved your colleague's request",

	// inviting users who are not members of application
	'invitefriends:subject' => 'You have been invited to join %s',	
	'invitefriends:email_body' => "Join the professionnal networking and collaborative workspace for all public service. You can proceed to your %s registration through this link %s ",

	// password reset or forget password
	'email:changereq:subject' => "You have requested a password reset",
	'email:changereq:body' => "There was a request to have a password reset from this user's IP address:<code> %s </code> <br/>Please click on this link to have the password reset for %s's account: %s ",

);	
add_translation("en", $english);
