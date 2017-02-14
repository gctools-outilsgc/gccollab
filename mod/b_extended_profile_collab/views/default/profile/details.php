<?php
/*
 * Author: Bryden Arndt
 * Date: 01/07/2015
 * Purpose: Display the profile details for the user profile in question.
 * Requires: gcconnex-profile.js should already be loaded at this point to handle edit/save/cancel toggles and other ajax requests related to profile sections
 * font-awesome css should be loaded already
 */

$user = elgg_get_page_owner_entity();
$profile_fields = elgg_get_config('profile_fields');

// display the username, title, phone, mobile, email, website, and user type
echo '<div class="panel-heading clearfix"><div class="pull-right clearfix">';
echo '<div class="gcconnex-profile-name">';

//edit button
if ($user->canEdit()) {
    $editAvatar = elgg_get_site_url(). 'avatar/edit/' . $user->username;
    echo '<button type="button" class="btn btn-primary gcconnex-edit-profile" data-toggle="modal" data-target="#editProfile" data-colorbox-opts = \'{"inline":true, "href":"#editProfile", "innerWidth": 800, "maxHeight": "80%"}\'>' . elgg_echo('gcconnex_profile:edit_profile') . ' <span class="wb-inv">' . elgg_echo('profile:contactinfo') . '</span></button>';
    // pop up or modal for the edit profile (name, occupation, title, ... etc)
    echo '<div class="modal" id="editProfile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog dialog-box">
                <div class="panel panel-custom">
                    <div class="panel-heading">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
    echo '<h2>' . elgg_echo('gcconnex_profile:basic:header') . '</h2>';
    echo '</div>';
    echo '<div class="panel-body">';
    echo '<div class="col-xs-12 mtm mbm"><a href='.$editAvatar.' class="btn btn-primary">'. elgg_echo('gcconnex_profile:profile:edit_avatar') .'</a></div>';

    // container for css styling, used to group profile content and display them seperately from other fields
    echo '<div class="basic-profile-standard-field-wrapper col-sm-6 col-xs-12">'; 

    // form that displays the user fields
    $fields = array('Name', 'user_type', 'Federal', 'Provincial', 'Institution', 'University', 'College', 'Job', 'Location', 'Phone', 'Mobile', 'Email', 'Website');

    foreach ($fields as $field) {

        // create a label and input box for each field on the basic profile (see $fields above)
        $field = strtolower($field);
        $value = $user->get($field);
        $value = htmlspecialchars_decode($value);

        echo "<div class='form-group col-xs-12 {$field}'>";

        // occupation input
        if (strcmp($field, 'user_type') == 0) {
            
            echo "<label for='{$field}' class='col-sm-4'>" . elgg_echo("gcconnex_profile:basic:{$field}")."</label>";
            echo '<div class="col-sm-8">';
            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => "gcconnex-basic-{$field}",
                'value' => $value,
                'options_values' => array('federal' => elgg_echo('gcconnex-profile-card:federal'), 'academic' => elgg_echo('gcconnex-profile-card:academic'), 'student' => elgg_echo('gcconnex-profile-card:student'), 'provincial' => elgg_echo('gcconnex-profile-card:provincial'), 'other' => elgg_echo('gcconnex-profile-card:other')),
            ));

            // jquery for the occupation dropdown - institution
    ?>

        <script>
            $(document).ready(function () {
                var user_occupation_top = $("#user_type").val();
                var user_institution_top = $("#institution").val();
                if (user_occupation_top == 'student' || user_occupation_top == 'academic') {
                    $(".job").hide();
                    $(".federal").hide();
                    $(".provincial").hide();
                    $(".ministry").hide();

                    if(user_institution_top == "university"){
                        $(".college").hide();
                    } else if (user_institution_top == "college"){
                        $(".university").hide();
                    }
                } else if (user_occupation_top == 'provincial') {
                    $(".federal").hide();
                    $(".institution").hide();
                    $(".university").hide();
                    $(".college").hide();

                    $(".ministry").hide();
                    var province = $("#provincial").val();
                    $('.' + province.replace(/\s+/g, '-').toLowerCase()).show();
                } else {
                    $(".provincial").hide();
                    $(".ministry").hide();
                    $(".institution").hide();
                    $(".university").hide();
                    $(".college").hide();
                }

                $("#user_type").change(function() {
                    var user_occupation = $(this).val();
                    var user_institution = $("#institution").val();
                    if (user_occupation == "federal") {
                        $(".institution").hide();
                        $(".university").hide();
                        $(".college").hide();
                        $(".provincial").hide();
                        $(".ministry").hide();

                        $(".job").show();
                        $(".federal").show();
                    } else if (user_occupation == "provincial") {
                        $(".institution").hide();
                        $(".university").hide();
                        $(".college").hide();
                        $(".federal").hide();

                        $(".job").show();
                        $(".provincial").show();

                        $('.ministry').hide();
                        var province = $("#provincial").val();
                        $('.' + province.replace(/\s+/g, '-').toLowerCase()).show();
                    } else if (user_occupation == "student" || user_occupation == "academic") {
                        $(".job").hide();
                        $(".federal").hide();
                        $(".provincial").hide();
                        $(".ministry").hide();

                        $(".institution").show();
                        $("." + user_institution).show();
                    }
                });

                $("#institution").change(function() {
                    if($(this).val() == "university"){
                        $(".university").show();
                        $(".college").hide();
                    } else if($(this).val() == "college"){
                        $(".college").show();
                        $(".university").hide();
                    }
                });

                $("#provincial").change(function() {
                    var province = $(this).val();
                    $('.ministry').hide();
                    $('.' + province.replace(/\s+/g, '-').toLowerCase()).show();
                });
            });
        </script>

    <?php

        // federal input field
        } else if ($field == 'federal') {

            echo "<label for='{$field}' class='col-sm-4'>" . elgg_echo("gcconnex_profile:basic:{$field}")."</label>";
            echo '<div class="col-sm-8">';

            $obj = elgg_get_entities(array(
                'type' => 'object',
                'subtype' => 'federal_departments',
            ));
            $departments = get_entity($obj[0]->guid);
            
            $federal_departments = array();
            if (get_current_language() == 'en'){
                $federal_departments = json_decode($departments->federal_departments_en, true);
            } else {
                $federal_departments = json_decode($departments->federal_departments_fr, true);
            }

            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => ' gcconnex-basic-' . $field,
                'value' => $value,
                'options_values' => $federal_departments,
            ));
        
        // provincial input field
        } else if ($field == 'provincial') {
            echo "<label for='{$field}' class='col-sm-4 {$field}'>" . elgg_echo("gcconnex_profile:basic:{$field}")."</label>";
            echo '<div class="col-sm-8">';

            $provObj = elgg_get_entities(array(
                'type' => 'object',
                'subtype' => 'provinces',
            ));
            $provs = get_entity($provObj[0]->guid);

            $provincial_departments = array();
            if (get_current_language() == 'en'){
                $provincial_departments = json_decode($provs->provinces_en, true);
            } else {
                $provincial_departments = json_decode($provs->provinces_fr, true);
            }

            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => ' gcconnex-basic-' . $field,
                'value' => $value,
                'options_values' => $provincial_departments,
            ));

            echo "</div></div>";

            $minObj = elgg_get_entities(array(
                'type' => 'object',
                'subtype' => 'ministries',
            ));
            $mins = get_entity($minObj[0]->guid);

            $ministries = array();
            if (get_current_language() == 'en'){
                $ministries = json_decode($mins->ministries_en, true);
            } else {
                $ministries = json_decode($mins->ministries_fr, true);
            }

            foreach($provincial_departments as $province => $name){
                $prov_value = ($user->get('provincial') == $province) ? $user->get('ministry'): "";
                $prov_id = str_replace(" ", "-", strtolower($province));
                echo '<div class="form-group col-xs-12 ministry ' . $prov_id . '"><label for="' . $prov_id . '" class="col-sm-4">' . elgg_echo('gcconnex_profile:basic:ministry') . '</label><div class="col-sm-8">';
                echo elgg_view('input/select', array(
                    'name' => 'ministry',
                    'id' => $prov_id . '-choices',
                    'class' => 'form-control gcconnex-basic-ministry',
                    'value' => $prov_value,
                    'options_values' => array_merge(array('default_invalid_value' => elgg_echo('gcRegister:make_selection')), $ministries[$province]),
                ));
                if($province != "Yukon"){ echo '</div></div>'; }
            }
            
        } else if (strcmp($field, 'institution') == 0) {

            echo "<label for='{$field}' class='col-sm-4'>" . elgg_echo("gcconnex_profile:basic:{$field}")."</label>";
            echo '<div class="col-sm-8">';

            /*
            // re-use the data that we've already put into the domain module
            $query = "SELECT ext, dept FROM email_extensions WHERE dept LIKE '%University%' OR dept LIKE '%College%' OR dept LIKE '%Institute%' OR dept LIKE '%Université%' OR dept LIKE '%Cégep%' OR dept LIKE '%Institut%'";
            $universities = get_data($query);
            $university_list = array();
            // this is bad programming but... reconstructing the array
            foreach ($universities as $university) {
                $university_list[$university->ext] = $university->dept;
            }

            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => "gcconnex-basic-{$field}",
                'value' => $value,
                'options_values' => $university_list, 
            ));
            */

            $institution_list = array("university" => elgg_echo('gcconnex-profile-card:university'), "college" => elgg_echo('gcconnex-profile-card:college'));

            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => "gcconnex-basic-{$field}",
                'value' => $value,
                'options_values' => $institution_list, 
            ));

        } else if (strcmp($field, 'university') == 0) {

            echo "<label for='{$field}' class='col-sm-4'>" . elgg_echo("gcconnex_profile:basic:{$field}")."</label>";
            echo '<div class="col-sm-8">';

            $uniObj = elgg_get_entities(array(
                'type' => 'object',
                'subtype' => 'universities',
            ));
            $unis = get_entity($uniObj[0]->guid);

            $universities = array();
            if (get_current_language() == 'en'){
                $universities = json_decode($unis->universities_en, true);
            } else {
                $universities = json_decode($unis->universities_fr, true);
            }

            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => "gcconnex-basic-{$field}",
                'value' => $value,
                'options_values' => $universities, 
            ));       

        } else if (strcmp($field, 'college') == 0) {

            echo "<label for='{$field}' class='col-sm-4'>" . elgg_echo("gcconnex_profile:basic:{$field}")."</label>";
            echo '<div class="col-sm-8">';

            $colObj = elgg_get_entities(array(
                'type' => 'object',
                'subtype' => 'colleges',
            ));
            $cols = get_entity($colObj[0]->guid);

            $colleges = array();
            if (get_current_language() == 'en'){
                $colleges = json_decode($cols->colleges_en, true);
            } else {
                $colleges = json_decode($cols->colleges_fr, true);
            }

            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => "gcconnex-basic-{$field}",
                'value' => $value,
                'options_values' => $colleges, 
            ));       

        } else if (strcmp($field, 'other') == 0) {

            echo "<label for='{$field}' class='col-sm-4'>" . elgg_echo("gcconnex_profile:basic:{$field}")."</label>";
            echo '<div class="col-sm-8">';

            $otherObj = elgg_get_entities(array(
                'type' => 'object',
                'subtype' => 'other',
            ));
            $others = get_entity($otherObj[0]->guid);

            $other = array();
            if (get_current_language() == 'en'){
                $other = json_decode($others->other_en, true);
            } else {
                $other = json_decode($others->other_fr, true);
            }

            echo elgg_view('input/text', array(
                'name' => $field,
                'id' => $field,
                'class' => "gcconnex-basic-{$field}",
                'value' => $value,
                'list' => $field . 'list'
            ));

            echo '<datalist id="otherlist">';
                foreach($other as $other_name){
                    echo '<option value="' . $other_name . '"></option>';
                }
            echo '</datalist>';

        } else {

            $params = array(
                'name' => $field,
                'id' => $field,
                'class' => 'gcconnex-basic-'.$field,
                'value' => $value,
            );

            // set up label and input field for the basic profile stuff
            echo "<label for='{$field}' class='col-sm-4'>" . elgg_echo("gcconnex_profile:basic:{$field}")."</label>";
            echo '<div class="col-sm-8">'; // field wrapper for css styling
			echo elgg_view("input/text", $params);

		} // input field

        echo '</div>'; //close div class = basic-profile-field
        echo '</div>'; //close div class = basic-profile-field-wrapper

    } // end for-loop

    echo '</div>'; // close div class="basic-profile-standard-field-wrapper"
    echo '<div class="basic-profile-social-media-wrapper col-sm-6 col-xs-12">'; // container for css styling, used to group profile content and display them seperately from other fields


	// pre-populate the social media fields and their prepended link for user profiles
    $fields = array(
        'Facebook' => "http://www.facebook.com/",
        'Google Plus' => "http://www.google.com/",
        'GitHub' => "https://github.com/",
        'Twitter' => "https://twitter.com/",
        'Linkedin' => "http://ca.linkedin.com/in/",
        'Pinterest' => "http://www.pinterest.com/",
        'Tumblr' => "https://www.tumblr.com/blog/",
        'Instagram' => "http://instagram.com/",
        'Flickr' => "http://flickr.com/",
        'Youtube' => "http://www.youtube.com/"
    );


    foreach ($fields as $field => $field_link) { // create a label and input box for each social media field on the basic profile

        echo '<div class="form-group social-media-field-wrapper">'; //field wrapper for css styling

        //echo '<div class="col-sm-4 social-media-label ' . $field . '-label">' . $field . ': </div>';
        $field = str_replace(' ', '-', $field); // create a css friendly version of the section name

        $field = strtolower($field);
        if ($field == "google-plus") { $field = "google"; }
        $value = $user->get($field);

        echo '<div class="input-group">'; // input wrapper for prepended link and input box, excludes the input label

        echo '<label for="' . $field . 'Input" class="input-group-addon clearfix">' . $field_link . "</label>"; // prepended link

        // setup the input for this field
        $placeholder = "test";
        if ($field == "facebook") { $placeholder = "User.Name"; }
        if ($field == "google") { $placeholder = "############"; }
        if ($field == "github") { $placeholder = "User"; }
        if ($field == "twitter") { $placeholder = "@user"; }
        if ($field == "linkedin") { $placeholder = "CustomURL"; }
        if ($field == "pinterest") { $placeholder = "Username"; }
        if ($field == "tumblr") { $placeholder = "Username"; }
        if ($field == "instagram") { $placeholder = "@user"; }
        if ($field == "flickr") { $placeholder = "Username"; }
        if ($field == "youtube") { $placeholder = "Username"; }

        $params = array(
            'name' => $field,
            'id' => $field . 'Input',
            'class' => 'editProfileFields gcconnex-basic-field gcconnex-social-media gcconnex-basic-' . $field,
            'placeholder' => $placeholder,
            'value' => $value
        );

        echo elgg_view("input/text", $params); // input field

        echo '</div>'; // close div class="input-group"

        echo '</div>'; // close div class = basic-profile-field-wrapper
    }

    echo '</div>'; // close div class="basic-profile-social-media-wrapper"


    echo '
    </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">' . elgg_echo('gcconnex_profile:cancel') . '</button>
                <button type="button" class="btn btn-primary save-profile">' . elgg_echo('gcconnex_profile:basic:save') . '</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';
}




echo '</div>'; // close div class="gcconnex-profile-name"
//actions dropdown
if (elgg_get_page_owner_guid() != elgg_get_logged_in_user_guid()) {
    $menu = elgg_trigger_plugin_hook('register', "menu:user_hover", array('entity' => $user), array());
    $builder = new ElggMenuBuilder($menu);
    $menu = $builder->getMenu();
    $actions = elgg_extract('action', $menu, array());
    $admin = elgg_extract('admin', $menu, array());

    $profile_actions = '';

	// cyu - GCCON-151 : Add colleague in FR not there (inconsistent FR and EN menu layout) & other issues
    if (elgg_is_logged_in() && $actions) {
		$btn_friend_request = '';
        foreach ($actions as $action) {
			
			if (strcmp($action->getName(),'add_friend') == 0 || strcmp($action->getName(),'remove_friend') == 0) {
				if (!check_entity_relationship(elgg_get_logged_in_user_guid(),'friendrequest',$user->getGUID())) {
					if ($user->isFriend() && strcmp($action->getName(),'remove_friend') == 0) {
						$btn_friend_request = $action->getContent();
						$btn_friend_request_link = $action->getHref();
					}
					if (!$user->isFriend() && strcmp($action->getName(),'add_friend') == 0) {
						$btn_friend_request = $action->getContent(array('class' => 'asdfasdasfad'));
						$btn_friend_request_link = $action->getHref();
					}
				}
			} else {

				if (check_entity_relationship(elgg_get_logged_in_user_guid(),'friendrequest',$user->getGUID()) && strcmp($action->getName(),'friend_request') == 0) {
					$btn_friend_request_link = $action->getHref();
					$btn_friend_request = $action->getContent();
				} else
					$profile_actions .= '<li>' . $action->getContent(array('class' => 'gcconnex-basic-profile-actions')) . '</li>';
			}
        }
    }

    if(elgg_is_logged_in()) {
		echo "<button type='button' class='btn btn-primary' onclick='location.href=\"{$btn_friend_request_link}\"'>{$btn_friend_request}</button>"; // cyu - added button and removed from actions toggle

        echo $add . '<div class="btn-group"><button type="button" class="btn btn-custom mrgn-rght-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    ' . elgg_echo('profile:actions') . ' <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right clearfix">';
        echo $profile_actions;
        echo '</ul></div>';
    }
}

// if admin, display admin links
$admin_links = '';
if (elgg_is_admin_logged_in() && elgg_get_logged_in_user_guid() != elgg_get_page_owner_guid()) {
    $text = elgg_echo('admin:options');

    foreach ($admin as $menu_item) {
        $admin_links .= '<li>' . elgg_view('navigation/menu/elements/item', array('item' => $menu_item)) . '</li>';
    }

    echo '<div class="pull-right btn-group"><button type="button" class="btn btn-custom pull-right dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
	$text . '<span class="caret"></span>
                </button>
                <ul class="dropdown-menu pull-right clearfix">' . $admin_links . '</ul></div>';
}



echo '</div>'; //closes btn-group


echo '<h1 class="pull-left group-title">' . $user->name . '</h1>';
echo '</div>'; // close div class="panel-heading"



echo '<div class="row mrgn-lft-md mrgn-rght-sm">';
echo elgg_view('profile/owner_block');
echo '<div class="col-xs-9 col-md-8 clearfix"><div class="mrgn-lft-md">';

// if user is student or professor, display the correlated information
if (strcmp($user->user_type, 'student') == 0 || strcmp($user->user_type, 'academic') == 0 ) {
    echo '<h3 class="mrgn-tp-0">'.elgg_echo("gcconnex-profile-card:{$user->user_type}", array($user->user_type)).'</h3>';
    $institution = ($user->institution == "university") ? $user->university : $user->college;
    $job = ($user->job != "") ? $user->job : "";
    echo '<div class="gcconnex-profile-dept">' . ($institution != "default_invalid_value" ? $institution : $job) . '</div>';

// otherwise if user is provincial employee
} else if (strcmp($user->user_type, 'provincial') == 0 ) {
    $provObj = elgg_get_entities(array(
        'type' => 'object',
        'subtype' => 'provinces',
    ));
    $provs = get_entity($provObj[0]->guid);

    $provinces = array();
    if (get_current_language() == 'en'){
        $provinces = json_decode($provs->provinces_en, true);
    } else {
        $provinces = json_decode($provs->provinces_fr, true);
    }

    $minObj = elgg_get_entities(array(
        'type' => 'object',
        'subtype' => 'ministries',
    ));
    $mins = get_entity($minObj[0]->guid);

    $ministries = array();
    if (get_current_language() == 'en'){
        $ministries = json_decode($mins->ministries_en, true);
    } else {
        $ministries = json_decode($mins->ministries_fr, true);
    }

    echo '<h3 class="mrgn-tp-0">' . elgg_echo("gcconnex-profile-card:{$user->user_type}") . '</h3>';
    echo '<div class="gcconnex-profile-job">' . $user->job . '</div>';
    $provString = $provinces[$user->provincial];
    if($user->ministry && $user->ministry !== "default_invalid_value"){ $provString .= ' / ' . $ministries[$user->provincial][$user->ministry]; }
    echo '<div class="gcconnex-profile-dept">' . $provString . '</div>';
// otherwise if user is public servant
} else if(strcmp($user->user_type, 'federal') == 0 ) {
    $deptObj = elgg_get_entities(array(
        'type' => 'object',
        'subtype' => 'federal_departments',
    ));
    $depts = get_entity($deptObj[0]->guid);

    $federal_departments = array();
    if (get_current_language() == 'en'){
        $federal_departments = json_decode($depts->federal_departments_en, true);
    } else {
        $federal_departments = json_decode($depts->federal_departments_fr, true);
    }

    echo '<h3 class="mrgn-tp-0">' . $user->job . '</h3>';
    echo '<div class="gcconnex-profile-dept">' . $federal_departments[$user->federal] . '</div>';
}

echo '<div class="gcconnex-profile-location">' . $user->location . '</div>';


echo '<div class="gcconnex-profile-contact-info">';

if ($user->phone != null)
    echo '<p class="mrgn-bttm-sm"><i class="fa fa-phone fa-lg"></i> ' . $user->phone . '</p>';

if ($user->mobile != null)
    echo '<p class="mrgn-bttm-sm"><i class="fa fa-mobile fa-lg"></i> ' . $user->mobile . '</p>';

if ($user->email != null)
    echo '<p class="mrgn-bttm-sm"><i class="fa fa-envelope fa-lg"></i> <a href="mailto:' . $user->email . '">' . $user->email . '</a></p>';

if ($user->website != null) {
    echo '<p class="mrgn-bttm-sm"><i class="fa fa-globe fa-lg"></i> ';
    echo elgg_view('output/url', array(
        'href' => $user->website,
        'text' => $user->website
    ));
    echo '</p>';
}

echo '</div></div>'; // close div class="gcconnex-profile-contact-info"



// pre-populate the social media links that we may or may not display depending on whether the user has entered anything for each one..
$social = array('facebook', 'google', 'github', 'twitter', 'linkedin', 'pinterest', 'tumblr', 'instagram', 'flickr', 'youtube');

echo '<div class="gcconnex-profile-social-media-links mrgn-bttm-sm mrgn-lft-md">';
foreach ($social as $media) {
    if ($link = $user->get($media)) {
        if ($media == 'facebook')   { $link = "http://www.facebook.com/" . $link; $class = "fa-facebook";}
        if ($media == 'google')     { $link = "http://plus.google.com/" . $link; $class = "fa-google-plus";}
        if ($media == 'github')     { $link = "https://github.com/" . $link; $class = "fa-github";}
        if ($media == 'twitter')    { $link = "https://twitter.com/" . $link; $class = "fa-twitter";}
        if ($media == 'linkedin')   { $link = "http://ca.linkedin.com/in/" . $link; $class = "fa-linkedin";}
        if ($media == 'pinterest')  { $link = "http://www.pinterest.com/" . $link; $class = "fa-pinterest";}
        if ($media == 'tumblr')     { $link = "https://www.tumblr.com/blog/" . $link; $class = "fa-tumblr";}
        if ($media == 'instagram')  { $link = "http://instagram.com/" . $link; $class = "fa-instagram";}
        if ($media == 'flickr')     { $link = "http://flickr.com/" . $link; $class = "fa-flickr"; }
        if ($media == 'youtube')    { $link = "http://www.youtube.com/" . $link; $class = "fa-youtube";}

        echo '<a href="' . $link . '" target="_blank"><i class="socialMediaIcons fa ' . $class . ' fa-2x"></i></a>';
    }
}
echo '</div>'; // close div class="gcconnex-profile-social-media-links"
echo '</div>';
echo '</div>'; //closes row class




$user = elgg_get_page_owner_entity();

// grab the actions and admin menu items from user hover
$menu = elgg_trigger_plugin_hook('register', "menu:user_hover", array('entity' => $user), array());
$builder = new ElggMenuBuilder($menu);
$menu = $builder->getMenu();
$actions = elgg_extract('action', $menu, array());
$admin = elgg_extract('admin', $menu, array());

$profile_actions = '';
if (elgg_is_logged_in() && $actions) {
    $profile_actions = '<ul class="elgg-menu profile-action-menu mvm">';
    foreach ($actions as $action) {
        $profile_actions .= '<li>' . $action->getContent(array('class' => 'elgg-button elgg-button-action')) . '</li>';
    }
    $profile_actions .= '</ul>';
}

// content links
$content_menu_title = elgg_echo('gcconnex_profile:user_content');
$content_menu = elgg_view_menu('owner_block', array(
    'entity' => elgg_get_page_owner_entity(),
    'class' => 'profile-content-menu',
));

