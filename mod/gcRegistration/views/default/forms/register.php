<?php
/**
 * Elgg register form
 *
 * @package Elgg
 * @subpackage Core
 */

/***********************************************************************
 * MODIFICATION LOG
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 *
 * USER 		DATE 			DESCRIPTION
 * TLaw/ISal 	n/a 			GC Changes
 * CYu 			March 5 2014 	Second Email field for verification & code clean up & validate email addresses
 * CYu 			July 16 2014	clearer messages & code cleanup						
 * CYu 			Sept 19 2014 	adjusted textfield rules (no spaces for emails)
 * MBlondin 	Jan 25 2016 	Layout change
 * MBlondin 	Feb 08 2016 	Delete IE7 form
 * NickP        June 9 2016     Added function to the username generation ajax to provide link to password retrival if account already exists
 * CYu 			Aug 15 2016 	GCcollab - Student / Academic (w/Universities) & Public Servants
 *
 ***********************************************************************/

$password = $password2 = '';
$username = get_input('e');
$email = get_input('e');
$name = get_input('n');
$site_url = elgg_get_site_url();

/*if (elgg_is_sticky_form('register')) {
	extract(elgg_get_sticky_values('register'));
	elgg_clear_sticky_form('register');
}*/

$account_exist_message = elgg_echo('registration:userexists');



// Javascript
?>
<script type="text/javascript">
var frDepartmentsGeds = {};
var departmentList = new Array();
var enDepartments = {}; 

$(document).ready(function() {

	/* Javascript:
	 * If user chooses to be student or academic type, display 
	 * dropdown for list of universities or departments
	 */

	<?php
		// we will re-use data from the domain manager module
		$query = "SELECT ext, dept FROM email_extensions WHERE dept LIKE '%University%' OR dept LIKE '%College%' OR dept LIKE '%Institute%' OR dept LIKE '%Université%' OR dept LIKE '%Cégep%' OR dept LIKE '%Institut%'";
		$universities = get_data($query);
		
		$universities = json_encode($universities, true);
	?>

	var university_list = '<?php echo $universities; ?>';
	var deserialized_university_list = $.parseJSON(university_list);

	$.each(deserialized_university_list, function(key, val) {
		$('[name=institution]').append("<option value='"+val['ext']+"'> "+val['dept']+" </option>");
	});

	$("#user_type").change(function() {
		if ($(this).val() == 'student' || $(this).val() == 'academic') {
			$('#universities').show();
			$('#departments').hide();
		} else {
			$('#universities').hide();
			$('#departments').show();
		}
	});
	$('#universities').hide();



	/* Javascript:
	 * Department are taken from GEDs
	 */

	var searchObj = "{\"requestID\" : \"B01\", \"authorizationID\" : \"X4GCCONNEX\"}";
	var frDepartments;
        
	$.ajax({
		type: 'POST',
        contentType: "application/json",
        url: 'https://api.sage-geds.gc.ca',
        data: searchObj,
        dataType: 'json',
        success: function (feed) {
        	var departments = feed.requestResults.departmentList;
        	for ( i=0;i<departments.length; i++ ) {
        		enDepartments[departments[i].dn] = departments[i].desc;
        	}

        	frDepartments = gedsFrDept();
        	
        }, // feed - JS object - GEDS result
		error: function() {
			// do nothing
		},
	});
});


/*
 * Javascript/Ajaxy code to connect to the GEDs API
 */
function gedsFrDept() {
	var searchObj = "{\"requestID\" : \"B01\", \"authorizationID\" : \"X4GCCONNEX\"}";
	
	$.ajax({
    	type: 'POST',
        contentType: "application/json",
        url: 'https://api.sage-geds.gc.ca/fr/GAPI/',
        data: searchObj,
        dataType: 'json',
        success: function (feed) {
        	var departments = feed.requestResults.departmentList;
        	for (i=0; i<departments.length; i++) {
        		frDepartmentsGeds[departments[i].dn] = departments[i].desc;
        	}
        	
        }, complete: function() {  		
    		elgg.action('saveDept',{
    			data: {
    				listEn: JSON.stringify(enDepartments),
    				listFr: JSON.stringify(frDepartmentsGeds),	
    			},
    			success: function (feed) {
    				// do nothing
    			}
    		})
		},
    });
}


// make sure the email address given does not contain invalid characters
function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/; 
    return re.test(email);
}


// auto fill function; will auto generate for display name
function fieldFill() {
	var dName = document.getElementById('email').value;
	if(dName.indexOf('@')!= false) {
		dName = dName.substring(0, dName.indexOf('@'));
	}
	if(dName.indexOf('.')!= false) {
		dName = dName.replace(/\./g,' ');
	}

	// gcchange - format display name (ie Mc'Larry John-Dean instead of mclarry john-dean)
	// gcchange - translated from php to javascript [php source is a note on the manual page of the ucfirst() function on php.net]
	function toProperCase(str) {
		var delim = new Array("'","-"," ");
		var append = '';
		var splitup = new Array();
		str = str.toLowerCase();
		for(var i = 0; i < delim.length; i++){
			if(str.search(delim[i]) != -1){
				append = '';
				splitup = str.split(delim[i]);
				for(var j = 0; j < splitup.length; j++){
					append += splitup[j].charAt(0).toUpperCase() + splitup[j].substr(1) + delim[i];
				}
				str = append.substring(0, append.length - 1);
			}
		}
		return str.charAt(0).toUpperCase() + str.substr(1);
	}
	
	$('.display_name').val(toProperCase(dName));
	name.value = dName;
}


function validForm() {
	var is_valid = false;
	
	$('input').each(function() {
		if ( $(this).attr('id') == "email_initial" || $(this).attr('id') == "email" || $(this).attr('id') == "username" || $(this).attr('id') == "password" || $(this).attr('id') == "password2" || $(this).attr('id') == "name") {
			var val = $(this).attr('value');

			if ( $(this).attr('value').length == 0)
				is_valid = false;
			else
				is_valid = true;
		}
	});
	return is_valid;
}


</script>







<!-- start of standard form -->
<div id="standard_version" class="row">

	<section class="col-md-6">
	<?php
		echo elgg_echo('gcRegister:email_notice') ;
		$js_disabled = false;
	?>
	</section>

	<!-- Registration Form -->
	<section class="col-md-6">
		<div class="panel panel-default">
			<header class="panel-heading"> <h3 class="panel-title"><?php echo elgg_echo('gcRegister:form'); ?></h3> </header>
			<div class="panel-body mrgn-lft-md">	

				<!-- Options for the following users: Students / Academics / Public Servants -->
				<div class="form-group">
					<label for="user_type" class="required"><span class="field-name"><?php echo elgg_echo('gcRegister:occupation'); ?></span></label>
					<font id="user_type_error" color="red"></font><br />
	    			<select id="user_type" name="user_type" class="form-control" >
	    				<option value="student"><?php echo elgg_echo('gcRegister:occupation:student'); ?></option>
						<option value="academic"><?php echo elgg_echo('gcRegister:occupation:academic'); ?></option>
						<option selected="selected" value="public_servant"><?php echo elgg_echo('gcRegister:occupation:public_servant'); ?></option>
	    			</select>
				</div>
	

				<!-- Departments or Universities (depending on occupation) -->
				<div class="form-group">

					<div id="universities">
						<label for="user_type" class="required"><span class="field-name"><?php echo elgg_echo('University'); ?></span></label>
						<select id="institution" name="institution" class="form-control">
							<option selected="selected" value="default_invalid_value"> <?php echo elgg_echo('gcRegister:occupation:university'); ?> </option>
						</select>
					</div>



<?php
	$obj = elgg_get_entities(array(
   		'type' => 'object',
   		'subtype' => 'dept_list',
   		'owner_guid' => elgg_get_logged_in_user_guid()
	));

	$provinces = array();
	if (get_current_language()=='en'){
		$departments = $obj[0]->deptsEn;
		$provinces['gov-ca'] = "Government of Canada";
		$provinces['pov-alb'] = 'Government of Alberta';
		$provinces['pov-bc'] = 'Government of British Columbia';
		$provinces['pov-man'] = 'Government of Manitoba';
		$provinces['pov-nb'] = 'Government of New Brunswick';
		$provinces['pov-nfl'] = 'Government of Newfoundland and Labrador';
		$provinces['pov-ns'] = 'Government of Nova Scotia';
		$provinces['pov-nwt'] = 'Government of Northwest Territories';
		$provinces['pov-nun'] = 'Government of Nunavut';
		$provinces['pov-ont'] = 'Government of Ontario';
		$provinces['pov-pei'] = 'Government of Prince Edward Island';
		$provinces['pov-que'] = 'Government of Quebec';
		$provinces['pov-sask'] = 'Government of Saskatchewan';
		$provinces['pov-yuk'] = 'Government of Yukon';
		$provinces['uni-ott'] = 'University of Ottawa';
	} else {
		$departments = $obj[0]->deptsFr;
		$provinces['gov-ca'] = "Gouvernement du Canada";
		$provinces['pov-alb'] = "Gouvernement de l'Alberta";
		$provinces['pov-bc'] = 'Gouvernement de la Colombie-Britannique';
		$provinces['pov-man'] = 'Gouvernement du Manitoba';
		$provinces['pov-nb'] = 'Gouvernement du Nouveau-Brunswick';
		$provinces['pov-nfl'] = 'Gouvernement de Terre-Neuve-et-Labrador';
		$provinces['pov-ns'] = 'Gouvernement de la Nouvelle-Écosse';
		$provinces['pov-nwt'] = 'Gouvernement du Territoires du Nord-Ouest';
		$provinces['pov-nun'] = 'Gouvernement du Nunavut';
		$provinces['pov-ont'] = "Gouvernement de l'Ontario";
		$provinces['pov-pei'] = "Gouvernement de l'Île-du-Prince-Édouard";
		$provinces['pov-que'] = 'Gouvernement du Québec';
		$provinces['pov-sask'] = 'Gouvernement de Saskatchewan';
		$provinces['pov-yuk'] = 'Gouvernement du Yukon';
		$provinces['uni-ott'] = "Universite d'Ottawa";
	}
	$departments = json_decode($departments, true);

	// default to invalid value, so it encourages users to select their university/college or department
	$select_department = elgg_view('input/select', array(
		'name' => 'department',
		'id' => 'department',
        'class' => 'form-control',
		'options_values' => array_merge(array('default_invalid_value' => elgg_echo('gcRegister:occupation:department')),$departments,$provinces),
	));
?>


					<div id="departments">
						<label for="user_type" class="required"><span class="field-name"><?php echo elgg_echo('Department'); ?></span></label>
						<!--<select id="department" name="department_name" class="form-control">
							<option value="default_invalid_value"> <?php echo elgg_echo('gcRegister:occupation:department'); ?> </option>
						</select>-->
						<?php echo $select_department ?>
					</div>

				</div>



				<!-- Initial Email -->
				<div class="form-group">
	    			<label for="email_initial" class="required"><span class="field-name"><?php echo elgg_echo('gcRegister:email_initial'); ?></span></label>	
	    			<font id="email_initial_error" color="red"></font><br />
	    			<input type="text" name="email_initial" id="email_initial" value='<?php echo $email ?>' class="form-control"/>
				</div>

				<div class="form-group">
					<label for="email" class="required"><span class="field-name"><?php echo elgg_echo('gcRegister:email_secondary'); ?></span></label>
	    			<font id="email_secondary_error" color="red"></font><br />
					<input id="email" class="form-control" type="text" value='<?php echo $email ?>' name="email" onBlur="" />
	    

	    		<script>	
	        		$('#email').blur(function () {
	            		elgg.action( 'register/ajax', {
							data: {
								args: document.getElementById('email').value
							},
							success: function (x) {

							    // auto-create username based on the email
							    $('.username_test').val(x.output);

					            // Nick - Testing here if the username already exists and add a feedback to the user
							    if (x.output == "<?php echo '> ' . elgg_echo('gcRegister:email_in_use'); ?>")
					                $('.already-registered-message span').html("<?php echo $account_exist_message; ?>").removeClass('hidden');
							    else
					                $('.already-registered-message span').addClass('hidden');

			    				generateDisplayName();

							    function generateDisplayName() {
	                				var dName = $('.username_test').val();
						            if (dName.indexOf('.') != false)
							            dName = dName.replace(/\./g,' ');
				            		$('.display_name').val(dName);
			    				}
							},   
						});
	        		});
	    		</script>

				</div> <!-- end form-group div -->
	    	<div class="return_message"></div>





			<!-- Username (auto-generate) -->
			<div class="form-group" style="display:none">
				<label for="username" class="required" ><span class="field-name"><?php echo elgg_echo('gcRegister:username'); ?></span> </label> 
			    <div class="already-registered-message mrgn-bttm-sm"><span class="label label-danger tags mrgn-bttm-sm"></span></div>
				t<?php
				echo elgg_view('input/text', array(
					'name' => 'username',
					'id' => 'username',
			        'class' => 'username_test form-control',
					'readonly' => 'readonly',
					'value' => $username,
				));
				?>
			</div>

			<!-- Password -->
			<div class="form-group">
				<label for="password" class="required"><span class="field-name"><span class="field-name"><?php echo elgg_echo('gcRegister:password_initial'); ?></span> </label>
				<font id="password_initial_error" color="red"></font><br />
				<?php
				echo elgg_view('input/password', array(
					'name' => 'password',
					'id' => 'password',
			        'class'=>'password_test form-control',
					'value' => $password,
				));
				?>
			</div>

			<!-- Secondary Password -->
			<div class="form-group">
				<label for="password2" class="required"><span class="field-name"><?php echo elgg_echo('gcRegister:password_secondary'); ?></span> </label>
			    <font id="password_secondary_error" color="red"></font><br />
				<?php
				echo elgg_view('input/password', array(
					'name' => 'password2',
					'value' => $password2,
					'id' => 'password2',
			        'class'=>'password2_test form-control',
				));
				?>
			</div>

			<!-- Display Name -->
			<div class="form-group">
				<label for="name" class="required"><span class="field-name"><?php echo elgg_echo('gcRegister:display_name'); ?></span>  </label>
				<?php
				echo elgg_view('input/text', array(
					'name' => 'name',
					'id' => 'name',
			        'class' => 'form-control display_name',
					'value' => $name,
				));
				?>
			</div>

		    <div class="alert alert-info"><?php echo elgg_echo('gcRegister:display_name_notice'); ?></div>
		    <div class="checkbox"> <label><input type="checkbox" value="1" name="toc2" id="toc2" /><?php echo elgg_echo('gcRegister:terms_and_conditions')?></label> </div>


<?php
			// view to extend to add more fields to the registration form
			echo elgg_view('register/extend', $vars);

			// Add captcha hook
			echo elgg_view('input/captcha', $vars);
			echo '<div class="elgg-foot">';
			echo elgg_view('input/hidden', array('name' => 'friend_guid', 'value' => $vars['friend_guid']));
			echo elgg_view('input/hidden', array('name' => 'invitecode', 'value' => $vars['invitecode']));

			// note: disable
			echo elgg_view('input/submit', array(
			    'name' => 'submit',
			    'value' => elgg_echo('gcRegister:register'),
			    'id' => 'submit',
			    'class'=>'submit_test btn-primary',));
			    //'onclick' => 'return check_fields2();'));
			echo '</div>';
		  //echo '<center>'.elgg_echo('gcRegister:tutorials_notice').'</center>';
			echo '<br/>';
?>
	            
		</div>
	</div>
</section>


<script>
/*
	$('#email_initial').on("keydown",function(e) {
		return e.which !== 32;
	});

	$('#email').on("keydown",function(e) {
		return e.which !== 32;
	});
*/

	// check if the initial email input is empty, then proceed to validate email
    $('#email_initial').on("focusout", function() {
    	var val = $(this).attr('value');
        if ( val === '' ) {
        	var c_err_msg = '<?php echo elgg_echo('gcRegister:empty_field') ?>';
            document.getElementById('email_initial_error').innerHTML = c_err_msg;
        
        } else if ( val !== '' ) {
            document.getElementById('email_initial_error').innerHTML = '';
            
            if (!validateEmail(val)) {
            	var c_err_msg = '<?php echo elgg_echo('gcRegister:invalid_email') ?>';
            	document.getElementById('email_initial_error').innerHTML = c_err_msg;
            }
        }

        var val_2 = $('#email').attr('value');
        if (val_2 == val) {

	        	document.getElementById('email_secondary_error').innerHTML = '';
	        
        }
    });
	

	    $('#email').on("focusout", function() {

	    	var val = $(this).attr('value');
		    if ( val === '' ) {
		    	var c_err_msg = "<?php echo elgg_echo('gcRegister:empty_field') ?>";
		        document.getElementById('email_secondary_error').innerHTML = c_err_msg;
		    }
		    else if ( val !== '' ) {
		        document.getElementById('email_secondary_error').innerHTML = '';

		        var val2 = $('#email_initial').attr('value');
		        if (val2.toLowerCase() != val.toLowerCase())
		        {
		        	var c_err_msg = "<?php echo elgg_echo('gcRegister:mismatch') ?>";
		        	document.getElementById('email_secondary_error').innerHTML = c_err_msg;
		        }
		    }
		});

	    $('.password_test').on("focusout", function() {

	    	var val = $(this).val();
		    if ( val === '' ) {
		    	var c_err_msg = "<?php echo elgg_echo('gcRegister:empty_field') ?>";
		        document.getElementById('password_initial_error').innerHTML = c_err_msg;
		    }
		    else if ( val !== '' ) {
		        document.getElementById('password_initial_error').innerHTML = '';
		    }

	        var val_2 = $('#password2').attr('value');
	        
            if (val_2 == val) {

		        	document.getElementById('password_secondary_error').innerHTML = '';
		        
            } else if (val_2 !== '' && val_2 != val) {
                var c_err_msg = "<?php echo elgg_echo('gcRegister:mismatch') ?>";
		        	document.getElementById('password_secondary_error').innerHTML = c_err_msg;
            }
		});	
	    
	    $('#password2').on("focusout", function() {

	    	var val = $(this).val();
		    if ( val === '' ) {
		    	var c_err_msg = "<?php echo elgg_echo('gcRegister:empty_field') ?>";
		        document.getElementById('password_secondary_error').innerHTML = c_err_msg;
		    }
		    else if ( val !== '' ) {
		        document.getElementById('password_secondary_error').innerHTML = '';
		        
		        var val2 = $('.password_test').val();
		        if (val2 != val)
		        {
		        	var c_err_msg = "<?php echo elgg_echo('gcRegister:mismatch') ?>";
		        	document.getElementById('password_secondary_error').innerHTML = c_err_msg;
		        }
		    }
		});
    
</script>

</div>
