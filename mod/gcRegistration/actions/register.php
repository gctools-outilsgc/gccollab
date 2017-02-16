<?php
/**
 * Elgg registration action
 *
 * @package Elgg.Core
 * @subpackage User.Account
 */


/***********************************************************************
 * MODIFICATION LOG
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 *
 * USER 		DATE 			DESCRIPTION
 * TLaw/ISal 	n/a 			GC Changes
 * CYu 			March 5 2014 	checks for email validity
 * CYu 			July 7 2014		force user to accept ToC
 * CYu 			Aug 15 2016 	
 ***********************************************************************/

elgg_make_sticky_form('register');

global $CONFIG;

// default code (core)
$username = get_input('username');
$email = get_input('email');
$password = get_input('password', null, false);
$password2 = get_input('password2', null, false);
$name = get_input('name');

$friend_guid = (int) get_input('friend_guid', 0);
$invitecode = get_input('invitecode');

$user_type = get_input('user_type');

//////////////////////////// Troy
$deptNum = get_input('department');


$department = get_input('department');
$institution = get_input('institution');

error_log("DEPARTMENT - {$department}");
error_log("INSTITUTION - {$institution}");


// TODO: check against the domain manager list...

$username = get_input('username');
$email = str_replace(' ','',trim(get_input('email')));
$password = trim(get_input('password', null, false));
$password2 = trim(get_input('password2', null, false));
$name = get_input('name');
$email2 = str_replace(' ','',get_input('email_initial'));
$toc = get_input('toc2');


// check form (incompleteness & validity)
if (elgg_get_config('allow_registration')) {
	try {
		// check if domain exists in database
		$emaildomain = explode('@',$email);
		$query = "SELECT count(*) AS num FROM email_extensions WHERE ext ='".$emaildomain[1]."'";
		
		$dept_exist = get_data($query);
		$emailgc = explode('.',$emaildomain[1]);
		$gcca = $emailgc[count($emailgc) - 2] .".".$emailgc[count($emailgc) - 1];
		$resulting_error = "";

		$wildcard_match = false;
		$wildcard_query = "SELECT ext FROM email_extensions WHERE ext LIKE '%*%'";
		$wildcard_emails = get_data($wildcard_query);
		
		if($wildcard_emails){
			foreach($wildcard_emails as $wildcard){
				$regex = str_replace(".", "\.", $wildcard->ext);
				$regex = str_replace("*", "[\w-.]+", $regex);
				$regex = "/^@" . $regex . "$/";
				if(preg_match($regex, "@".$emaildomain[1]) || strtolower(str_replace("*.", "", $wildcard->ext)) == strtolower($emaildomain[1])){
					$wildcard_match = true;
				}
			}
		}

		// check if toc is checked, user agrees to TOC
		if ($toc[0] != 1)
			$resulting_error .= elgg_echo('gcRegister:toc_error').'<br/>';
		
		// if domain doesn't exist in database, check if it's a gc.ca domain
		if ($dept_exist[0]->num <= 0 && strcmp($gcca, 'gc.ca') != 0 && !$wildcard_match)
			$resulting_error .= elgg_echo('gcRegister:invalid_email').'<br/>';

		// check if it's from a standard form type
		if (strcmp(get_input('form_type'), 'standard') != 0 && strcmp($email, $email2) != 0)
			$resulting_error .= elgg_echo('gcRegister:email_mismatch').'<br/>';

		// check if two passwords are not empty
		if (empty(trim($password)) || empty(trim($password2)))
			$resulting_error .= elgg_echo('RegistrationException:EmptyPassword').'<br/>';

		// check if two passwords match
		if (strcmp($password, $password2) != 0)
			$resulting_error .= elgg_echo('RegistrationException:PasswordMismatch').'<br/>';

		// check if the department or college/univerisity is filled
		if (strcmp($department, 'default_invalid_value') == 0 && strcmp($institution, 'default_invalid_value') == 0)
			$resulting_error .= elgg_echo('RegistrationException:DepartmentNotSelected').'<br/>';

		// if there are any registration error, throw an exception
		if (!empty($resulting_error))
			throw new RegistrationException($resulting_error);



		$guid = register_user($username, $password, $name, $email, false, $friend_guid, $invitecode);

		if ($guid) {
			$new_user = get_entity($guid);

			// condition whether or not we want to record the type of user (for gccollab)
			$new_user->user_type = $user_type; 

			// allow plugins to respond to self registration
			// note: To catch all new users, even those created by an admin, register for the create, user event instead. only passing vars that aren't in ElggUser.
			$params = array(
				'user' => $new_user,
				'password' => $password,
				'friend_guid' => $friend_guid,
				'invitecode' => $invitecode,
			);


			// @todo should registration be allowed no matter what the plugins return?
			if (!elgg_trigger_plugin_hook('register', 'user', $params, TRUE)) {
				$ia = elgg_set_ignore_access(true);
				$new_user->delete();
				elgg_set_ignore_access($ia);
				// @todo this is a generic messages. We could have plugins throw a RegistrationException, but that is very odd for the plugin hooks system.
				throw new RegistrationException(elgg_echo('registerbad'));
			}

			elgg_clear_sticky_form('register');
			system_message(elgg_echo("registerok", array(elgg_get_site_entity()->name)));

			// if exception thrown, this probably means there is a validation plugin that has disabled the user
			try {
				login($new_user);
			} catch (LoginException $e) {
				// do nothing
			}

			// if public servant and department is not empty, save to the user object
			if (strcmp($department,'default_invalid_value') != 0) {
				/////// Troy
				$obj = elgg_get_entities(array(
	   				'type' => 'object',
	   				'subtype' => 'dept_list',
	   				'owner_guid' => elgg_get_logged_in_user_guid()
				));

				$departmentsEn = json_decode($obj[0]->deptsEn, true);
				$provincesEn['pov-alb'] = 'Government of Alberta';
				$provincesEn['pov-bc'] = 'Government of British Columbia';
				$provincesEn['pov-man'] = 'Government of Manitoba';
				$provincesEn['pov-nb'] = 'Government of New Brunswick';
				$provincesEn['pov-nfl'] = 'Government of Newfoundland and Labrador';
				$provincesEn['pov-ns'] = 'Government of Nova Scotia';
				$provincesEn['pov-nwt'] = 'Government of Northwest Territories';
				$provincesEn['pov-nun'] = 'Government of Nunavut';
				$provincesEn['pov-ont'] = 'Government of Ontario';
				$provincesEn['pov-pei'] = 'Government of Prince Edward Island';
				$provincesEn['pov-que'] = 'Government of Quebec';
				$provincesEn['pov-sask'] = 'Government of Saskatchewan';
				$provincesEn['pov-yuk'] = 'Government of Yukon';
				$departmentsEn = array_merge($departmentsEn,$provincesEn);
				
				$departmentsFr = json_decode($obj[0]->deptsFr, true);
				$provincesFr['pov-alb'] = "Gouvernement de l'Alberta";
				$provincesFr['pov-bc'] = 'Gouvernement de la Colombie-Britannique';
				$provincesFr['pov-man'] = 'Gouvernement du Manitoba';
				$provincesFr['pov-nb'] = 'Gouvernement du Nouveau-Brunswick';
				$provincesFr['pov-nfl'] = 'Gouvernement de Terre-Neuve-et-Labrador';
				$provincesFr['pov-ns'] = 'Gouvernement de la Nouvelle-Écosse';
				$provincesFr['pov-nwt'] = 'Gouvernement du Territoires du Nord-Ouest';
				$provincesFr['pov-nun'] = 'Gouvernement du Nunavut';
				$provincesFr['pov-ont'] = "Gouvernement de l'Ontario";
				$provincesFr['pov-pei'] = "Gouvernement de l'Île-du-Prince-Édouard";
				$provincesFr['pov-que'] = 'Gouvernement du Québec';
				$provincesFr['pov-sask'] = 'Gouvernement de Saskatchewan';
				$provincesFr['pov-yuk'] = 'Gouvernement du Yukon';
				$departmentsFr = array_merge($departmentsEn,$provincesFr);
				
				if (get_current_language() == 'en') {
					$deptString = $departmentsEn[$deptNum]." / ".$departmentsFr[$deptNum];
				} else {
					$deptString = $departmentsFr[$deptNum]." / ".$departmentsEn[$deptNum];
				}
				
				$new_user->set('department', $deptString);
	            $new_user->last_department_verify = time();
	        
	        } else {

	        	$query = "SELECT dept FROM email_extensions WHERE ext = '{$institution}'";
	        	$institution_name = get_data($query);
	        	
	        	$new_user->set('institution', $institution_name[0]->dept);
	        	$new_user->last_department_verify = time();
	        }
			
			// Forward on success, assume everything else is an error...
			forward();
		} else {
			register_error(elgg_echo("registerbad"));
		}
	} catch (RegistrationException $r) {
		register_error($r->getMessage());
	}
} else {
	register_error(elgg_echo('registerdisabled'));
}

forward(REFERER);
