<?php

$french = array(

	// user validation
	'email:validate:subject' => "Veuillez valider le compte de %s", 
	'email:validate:body' => "Bienvenue sur GCcollab. Afin de compléter votre inscription, veuillez valider votre compte enregistré sous le nom %s en cliquant sur le lien suivant : %s",

	// friend requests & approvals
	'friend_request:newfriend:subject' => "%s veut être votre collègue!",
	'friend_request:newfriend:body' => "%s souhaite être votre collègue et attend que vous approuviez sa demande.<br/> Affichez les demandes qui sont en attentes en cliquant ici : %s",

	'friend_request:approve:subject' => "%s a approuvé votre demande pour devenir votre collègue",
	'friend_request:approve:message' => "<a href='%s'>%s</a> a approuvé votre demande pour devenir collègue. ",

	// invite friends who are not members of application
	'invitefriends:subject' => 'Vous avez été invité à joindre %s',	
	'invitefriends:email_body' => "Joignez-vous à l'espace de travail collaboratif pour le réseautage professionnel pour l'ensemble de la fonction publique. Vous pouvez vous inscrire à %s en cliquant sur le lien suivant %s",

	// password reset or forget password
	'email:changereq:subject' => "",
	'email:changereq:body' => "",
);

add_translation("fr", $french);
