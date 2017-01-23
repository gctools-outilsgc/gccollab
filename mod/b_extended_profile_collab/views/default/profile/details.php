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
                'options_values' => array('federal' => elgg_echo('gcconnex-profile-card:federal'), 'academic' => elgg_echo('gcconnex-profile-card:academic'), 'student' => elgg_echo('gcconnex-profile-card:student'), 'provincial' => elgg_echo('gcconnex-profile-card:provincial')),
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
                    $(".job").hide();
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
                        $(".job").hide();
                        $(".federal").hide();

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

            $provincial_departments = array();
            if (get_current_language() == 'en'){
                $provincial_departments = array("Alberta" => "Alberta",
                "British Columbia" => "British Columbia",
                "Manitoba" => "Manitoba",
                "New Brunswick" => "New Brunswick",
                "Newfoundland and Labrador" => "Newfoundland and Labrador",
                "Northwest Territories" => "Northwest Territories",
                "Nova Scotia" => "Nova Scotia",
                "Nunavut" => "Nunavut",
                "Ontario" => "Ontario",
                "Prince Edward Island" => "Prince Edward Island",
                "Quebec" => "Quebec",
                "Saskatchewan" => "Saskatchewan",
                "Yukon" => "Yukon");
            } else {
                $provincial_departments = array("Alberta" => "Alberta",
                "British Columbia" => "Colombie-Britannique",
                "Prince Edward Island" => "Île-du-Prince-Édouard",
                "Manitoba" => "Manitoba",
                "New Brunswick" => "Nouveau-Brunswick",
                "Nova Scotia" => "Nouvelle-Écosse",
                "Nunavut" => "Nunavut",
                "Ontario" => "Ontario",
                "Quebec" => "Québec",
                "Saskatchewan" => "Saskatchewan",
                "Newfoundland and Labrador" => "Terre-Neuve-et-Labrador",
                "Northwest Territories" => "Territoires du Nord-Ouest",
                "Yukon" => "Yukon");
            }

            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => ' gcconnex-basic-' . $field,
                'value' => $value,
                'options_values' => $provincial_departments,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry alberta'>";
            echo "<label for='alberta' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $alberta_ministries = array("Advanced Education" => "Advanced Education",
            "Agriculture and Forestry" => "Agriculture and Forestry",
            "Corporate Human Resourcing" => "Corporate Human Resourcing",
            "Culture and Tourism" => "Culture and Tourism",
            "Economic Development and Trade" => "Economic Development and Trade",
            "Education" => "Education",
            "Energy" => "Energy",
            "Environment and Parks" => "Environment and Parks",
            "Health" => "Health",
            "Human Services" => "Human Services",
            "Indigenous Relations" => "Indigenous Relations",
            "Infrastructure" => "Infrastructure",
            "Justice and Solicitor General" => "Justice and Solicitor General",
            "Labour" => "Labour",
            "Municipal Affairs" => "Municipal Affairs",
            "Seniors and Housing" => "Seniors and Housing",
            "Service Alberta" => "Service Alberta",
            "Status of Women" => "Status of Women",
            "Transportation" => "Transportation",
            "Treasury Board and Finance" => "Treasury Board and Finance");

            $alberta_value = ($user->get('provincial') == 'Alberta') ? $user->get('ministry'): "";

            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'alberta',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $alberta_value,
                'options_values' => $alberta_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry british-columbia'>";
            echo "<label class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $bc_ministries = array("Aboriginal Relations & Reconciliation" => "Aboriginal Relations & Reconciliation",
            "Advanced Education" => "Advanced Education",
            "Agriculture" => "Agriculture",
            "Children & Family Development" => "Children & Family Development",
            "Community, Sport & Cultural Development" => "Community, Sport & Cultural Development",
            "Education" => "Education",
            "Energy & Mines" => "Energy & Mines",
            "Environment" => "Environment",
            "Finance" => "Finance",
            "Forests, Lands & Natural Resource Operations" => "Forests, Lands & Natural Resource Operations",
            "Health" => "Health",
            "International Trade" => "International Trade",
            "Jobs, Tourism & Skills Training" => "Jobs, Tourism & Skills Training",
            "Justice" => "Justice",
            "Natural Gas Development" => "Natural Gas Development",
            "Public Safety & Solicitor General" => "Public Safety & Solicitor General",
            "Small Business, Red Tape Reduction" => "Small Business, Red Tape Reduction",
            "Social Development & Social Innovation" => "Social Development & Social Innovation",
            "Technology, Innovation & Citizens' Services" => "Technology, Innovation & Citizens' Services",
            "Transportation & Infrastructure" => "Transportation & Infrastructure");

            $bc_value = ($user->get('provincial') == 'British Columbia') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'british-columbia',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $bc_value,
                'options_values' => $bc_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry manitoba'>";
            echo "<label for='manitoba' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $manitoba_ministries = array();
            if (get_current_language() == 'en'){
                $manitoba_ministries = array("Agriculture" => "Agriculture",
                "Civil Service Commission" => "Civil Service Commission",
                "Crown Services" => "Crown Services",
                "Education and Training" => "Education and Training",
                "Families" => "Families",
                "Finance" => "Finance",
                "Growth, Enterprise and Trade" => "Growth, Enterprise and Trade",
                "Health, Seniors and Active Living" => "Health, Seniors and Active Living",
                "Indigenous and Municipal Relations" => "Indigenous and Municipal Relations",
                "Infrastructure" => "Infrastructure",
                "Intergovernmental Affairs and International Relations" => "Intergovernmental Affairs and International Relations",
                "Justice" => "Justice",
                "Sport, Culture and Heritage" => "Sport, Culture and Heritage",
                "Sustainable Development" => "Sustainable Development");
            } else {
                $manitoba_ministries = array("Intergovernmental Affairs and International Relations" => "Affaires intergouvernementales et relations internationales",
                "Agriculture" => "Agriculture",
                "Civil Service Commission" => "Commission de la fonction publique",
                "Growth, Enterprise and Trade" => "Croissance, Entreprise et Commerce",
                "Sustainable Development" => "Développement durable",
                "Education and Training" => "Éducation et Formation",
                "Families" => "Familles",
                "Finance" => "Finances",
                "Infrastructure" => "Infrastructure",
                "Justice" => "Justice",
                "Indigenous and Municipal Relations" => "Relations avec les Autochtones et les municipalités",
                "Health, Seniors and Active Living" => "Santé, Aînés et Vie active",
                "Crown Services" => "Services de la Couronne",
                "Sport, Culture and Heritage" => "Sport, Culture et Patrimoine");
            }

            $manitoba_value = ($user->get('provincial') == 'Manitoba') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'manitoba',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $manitoba_value,
                'options_values' => $manitoba_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry new-brunswick'>";
            echo "<label for='new-brunswick' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $new_brunswick_ministries = array();
            if (get_current_language() == 'en'){
                $new_brunswick_ministries = array("Aboriginal Affairs" => "Aboriginal Affairs",
                "Agriculture, Aquaculture and Fisheries" => "Agriculture, Aquaculture and Fisheries",
                "Education and Early Childhood Development" => "Education and Early Childhood Development",
                "Emergency Measures Organization" => "Emergency Measures Organization",
                "Energy and Resource Development" => "Energy and Resource Development",
                "Environment and Local Government" => "Environment and Local Government",
                "Executive Council Office" => "Executive Council Office",
                "Finance" => "Finance",
                "Health" => "Health",
                "Intergovernmental Affairs" => "Intergovernmental Affairs",
                "Justice and Public Safety" => "Justice and Public Safety",
                "Office of the Attorney General" => "Office of the Attorney General",
                "Office of the Premier" => "Office of the Premier",
                "Opportunities New Brunswick" => "Opportunities New Brunswick",
                "Post-Secondary Education, Training and Labour" => "Post-Secondary Education, Training and Labour",
                "Regional Development Corporation" => "Regional Development Corporation",
                "Service New Brunswick" => "Service New Brunswick",
                "Social Development" => "Social Development",
                "Tourism, Heritage and Culture" => "Tourism, Heritage and Culture",
                "Transportation and Infrastructure" => "Transportation and Infrastructure",
                "Treasury Board" => "Treasury Board",
                "Women's Equality" => "Women's Equality");
            } else {
                $new_brunswick_ministries = array("Aboriginal Affairs" => "Affaires autochtones",
                "Intergovernmental Affairs" => "Affaires intergouvernementales",
                "Agriculture, Aquaculture and Fisheries" => "Agriculture, Aquaculture et Pêches",
                "Executive Council Office" => "Bureau du Conseil exécutif",
                "Office of the Attorney General" => "Cabinet du procureur général",
                "Office of the Premier" => "Cabinet du premier ministre",
                "Treasury Board" => "Conseil du Trésor",
                "Energy and Resource Development" => "Développement de l'énergie et des ressources",
                "Social Development" => "Développement social",
                "Education and Early Childhood Development" => "Éducation et Développement de la petite enfance",
                "Post-Secondary Education, Training and Labour" => "Éducation postsecondaire, Formation et Travail",
                "Women's Equality" => "Égalité des femmes",
                "Environment and Local Government" => "Environnement et Gouvernements locaux",
                "Finance" => "Finances",
                "Justice and Public Safety" => "Justice et Sécurité publique",
                "Opportunities New Brunswick" => "Opportunités Nouveau-Brunswick",
                "Emergency Measures Organization" => "Organisation des mesures d'urgence",
                "Health" => "Santé",
                "Service New Brunswick" => "Service Nouveau-Brunswick",
                "Regional Development Corporation" => "Société de développement régional",
                "Tourism, Heritage and Culture" => "Tourisme, Patrimoine et Culture",
                "Transportation and Infrastructure" => "Transports et Infrastructure");
            }

            $new_brunswick_value = ($user->get('provincial') == 'New Brunswick') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'new-brunswick',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $new_brunswick_value,
                'options_values' => $new_brunswick_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry newfoundland-and-labrador'>";
            echo "<label for='newfoundland-and-labrador' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $newfoundland_ministries = array("Advanced Education, Skills and Labour" => "Advanced Education, Skills and Labour",
            "Board of Commissioners of Public Utilities" => "Board of Commissioners of Public Utilities",
            "Business, Tourism, Culture and Rural Development" => "Business, Tourism, Culture and Rural Development",
            "Children, Seniors and Social Development" => "Children, Seniors and Social Development",
            "Commissioner for Legislative Standards" => "Commissioner for Legislative Standards",
            "Education and Early Childhood Development" => "Education and Early Childhood Development",
            "Electoral Districts Boundaries Commission" => "Electoral Districts Boundaries Commission",
            "Environment and Climate Change" => "Environment and Climate Change",
            "Executive Council" => "Executive Council",
            "Finance" => "Finance",
            "Fisheries, Forestry and Agrifoods" => "Fisheries, Forestry and Agrifoods",
            "Government Purchasing Agency" => "Government Purchasing Agency",
            "Health and Community Services" => "Health and Community Services",
            "Human Rights Commission" => "Human Rights Commission",
            "Justice and Public Safety" => "Justice and Public Safety",
            "Labour Relations Board" => "Labour Relations Board",
            "Multi-Materials Stewardship Board" => "Multi-Materials Stewardship Board",
            "Municipal Affairs" => "Municipal Affairs",
            "Natural Resources" => "Natural Resources",
            "Newfoundland and Labrador Film Development Corporation" => "Newfoundland and Labrador Film Development Corporation",
            "Newfoundland and Labrador Housing Corporation" => "Newfoundland and Labrador Housing Corporation",
            "Newfoundland and Labrador Hydro" => "Newfoundland and Labrador Hydro",
            "Newfoundland and Labrador Medical Care Plan - MCP" => "Newfoundland and Labrador Medical Care Plan - MCP",
            "Office of the Auditor General" => "Office of the Auditor General",
            "Office of the Chief Electoral Officer" => "Office of the Chief Electoral Officer",
            "Office of the Child and Youth Advocate" => "Office of the Child and Youth Advocate",
            "Office of the Citizens' Representative" => "Office of the Citizens' Representative",
            "Office of the Information and Privacy Commissioner" => "Office of the Information and Privacy Commissioner",
            "Provincial Information and Library Resources Board" => "Provincial Information and Library Resources Board",
            "Public Service Commission" => "Public Service Commission",
            "Research & Development Corporation" => "Research & Development Corporation",
            "Royal Newfoundland Constabulary" => "Royal Newfoundland Constabulary",
            "Service NL" => "Service NL",
            "Transportation and Works" => "Transportation and Works",
            "Workplace Health Safety and Compensation Commission" => "Workplace Health Safety and Compensation Commission",
            "Workplace Health, Safety and Compensation Review Division" => "Workplace Health, Safety and Compensation Review Division");

            $newfoundland_value = ($user->get('provincial') == 'Newfoundland and Labrador') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'newfoundland-and-labrador',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $newfoundland_value,
                'options_values' => $newfoundland_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry northwest-territories'>";
            echo "<label for='northwest-territories' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $northwest_territories_ministries = array();
            if (get_current_language() == 'en'){
                $northwest_territories_ministries = array("Aboriginal Affairs and Intergovernmental Relations" => "Aboriginal Affairs and Intergovernmental Relations",
                "Education, Culture and Employment" => "Education, Culture and Employment",
                "Environment and Natural Resources" => "Environment and Natural Resources",
                "Executive" => "Executive",
                "Finance" => "Finance",
                "Health and Social Services" => "Health and Social Services",
                "Human Resources" => "Human Resources",
                "Industry, Tourism and Investment" => "Industry, Tourism and Investment",
                "Justice" => "Justice",
                "Lands" => "Lands",
                "Legislative Assembly" => "Legislative Assembly",
                "Municipal and Community Affairs" => "Municipal and Community Affairs",
                "Public Works & Services" => "Public Works & Services",
                "Transportation" => "Transportation");
            } else {
                $northwest_territories_ministries = array("Aboriginal Affairs and Intergovernmental Relations" => "Ministère des Affaires autochtones et des Relations intergouvernementales",
                "Education, Culture and Employment" => "Ministère de l’Éducation, de la Culture et de la Formation",
                "Environment and Natural Resources" => "Ministère de l’Environnement et des Ressources naturelles",
                "Executive" => "Ministère de l’Exécutif",
                "Finance" => "Ministère des Finances",
                "Health and Social Services" => "Ministère de la Santé et des Services sociaux",
                "Human Resources" => "Ministère des Ressources humaines",
                "Industry, Tourism and Investment" => "Ministère de l’Industrie, du Tourisme et de l’Investissement",
                "Justice" => "Ministère de la Justice",
                "Lands" => "Ministère de l’Administration des terres",
                "Legislative Assembly" => "Assemblée législative des Territoires du Nord-Ouest",
                "Municipal and Community Affairs" => "Ministère des Affaires municipales et communautaires",
                "Public Works & Services" => "Ministère des Travaux publics et des Services",
                "Transportation" => "Ministère des Transports");
            }

            $northwest_territories_value = ($user->get('provincial') == 'Northwest Territories') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'northwest-territories',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $northwest_territories_value,
                'options_values' => $northwest_territories_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry nova-scotia'>";
            echo "<label for='nova-scotia' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $nova_scotia_ministries = array("Aboriginal Affairs" => "Aboriginal Affairs",
            "Acadian Affairs" => "Acadian Affairs",
            "African Nova Scotian Affairs" => "African Nova Scotian Affairs",
            "Agriculture" => "Agriculture",
            "Business" => "Business",
            "Communications Nova Scotia" => "Communications Nova Scotia",
            "Communities, Culture and Heritage" => "Communities, Culture and Heritage",
            "Community Services" => "Community Services",
            "Education and Early Childhood Development" => "Education and Early Childhood Development",
            "Energy" => "Energy",
            "Environment" => "Environment",
            "Executive Council Office" => "Executive Council Office",
            "Finance and Treasury Board" => "Finance and Treasury Board",
            "Fisheries and Aquaculture" => "Fisheries and Aquaculture",
            "Gaelic Affairs" => "Gaelic Affairs",
            "Health and Wellness" => "Health and Wellness",
            "Immigration" => "Immigration",
            "Intergovernmental Affairs" => "Intergovernmental Affairs",
            "Internal Services" => "Internal Services",
            "Justice" => "Justice",
            "Labour and Advanced Education" => "Labour and Advanced Education",
            "Municipal Affairs" => "Municipal Affairs",
            "Natural Resources" => "Natural Resources",
            "Public Service Commission" => "Public Service Commission",
            "Seniors" => "Seniors",
            "Service Nova Scotia" => "Service Nova Scotia",
            "Transportation and Infrastructure Renewal" => "Transportation and Infrastructure Renewal");

            $nova_scotia_value = ($user->get('provincial') == 'Nova Scotia') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'nova-scotia',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $nova_scotia_value,
                'options_values' => $nova_scotia_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry nunavut'>";
            echo "<label for='nunavut' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $nunavut_ministries = array();
            if (get_current_language() == 'en'){
                $nunavut_ministries = array("Community and Government Services" => "Community and Government Services",
                "Culture and Heritage" => "Culture and Heritage",
                "Economic Development and Transportation" => "Economic Development and Transportation",
                "Environment" => "Environment",
                "Education" => "Education",
                "Executive and Intergovernmental Affairs" => "Executive and Intergovernmental Affairs",
                "Family Services" => "Family Services",
                "Finance" => "Finance",
                "Health" => "Health",
                "Justice" => "Justice");
            } else {
                $nunavut_ministries = array("Culture and Heritage" => "Culture et Patrimoine",
                "Economic Development and Transportation" => "Développement économique et Transports",
                "Education" => "Éducation",
                "Environment" => "Environnement",
                "Executive and Intergovernmental Affairs" => "Exécutif et Affaires intergouvernementales",
                "Finance" => "Finances",
                "Justice" => "Justice",
                "Health" => "Santé",
                "Family Services" => "Services à la famille",
                "Community and Government Services" => "Services communautaires et gouvernementaux");
            }

            $nunavut_value = ($user->get('provincial') == 'Nunavut') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'nunavut',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $nunavut_value,
                'options_values' => $nunavut_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry ontario'>";
            echo "<label for='ontario' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $ontario_ministries = array();
            if (get_current_language() == 'en'){
                $ontario_ministries = array("Accessibility Directorate of Ontario" => "Accessibility Directorate of Ontario",
                "Advanced Education and Skills Development" => "Advanced Education and Skills Development",
                "Agriculture, Food and Rural Affairs" => "Agriculture, Food and Rural Affairs",
                "Attorney General" => "Attorney General",
                "Children and Youth Services" => "Children and Youth Services",
                "Citizenship and Immigration" => "Citizenship and Immigration",
                "Community and Social Services" => "Community and Social Services",
                "Community Safety and Correctional Services" => "Community Safety and Correctional Services",
                "Economic Development and Growth" => "Economic Development and Growth",
                "Education" => "Education",
                "Energy" => "Energy",
                "Environment and Climate Change" => "Environment and Climate Change",
                "Finance" => "Finance",
                "Francophone Affairs" => "Francophone Affairs",
                "Government and Consumer Services" => "Government and Consumer Services",
                "Health and Long-Term Care" => "Health and Long-Term Care",
                "Housing" => "Housing",
                "Infrastructure" => "Infrastructure",
                "International Trade" => "International Trade",
                "Labour" => "Labour",
                "Municipal Affairs" => "Municipal Affairs",
                "Natural Resources and Forestry" => "Natural Resources and Forestry",
                "Northern Development and Mines" => "Northern Development and Mines",
                "Research, Innovation and Science" => "Research, Innovation and Science",
                "Seniors" => "Seniors",
                "Tourism, Culture and Sport" => "Tourism, Culture and Sport",
                "Transportation" => "Transportation",
                "Treasury Board Secretariat" => "Treasury Board Secretariat");
            } else {
                $ontario_ministries = array("Seniors" => "Affaires des personnes âgées",
                "Francophone Affairs" => "Affaires francophones",
                "International Trade" => "Commerce international",
                "Accessibility Directorate of Ontario" => "Direction générale de l'accessibilité",
                "Advanced Education and Skills Development" => "L’Enseignement supérieur et de la formation professionnelle",
                "Research, Innovation and Science" => "La Recherche, de l’innovation et des sciences",
                "Infrastructure" => "Ministère de l’infrastructure",
                "Health and Long-Term Care" => "Ministère de la santé et des soins de longue durée",
                "Community Safety and Correctional Services" => "Ministère de la sécurité communautaire et des services correctionnels",
                "Agriculture, Food and Rural Affairs" => "Ministère de l'agriculture, de l'alimentation et des affaires rurales",
                "Education" => "Ministère de l'éducation",
                "Energy" => "Ministère de l'énergie",
                "Environment and Climate Change" => "Ministère de l'environnement et de l'action en matière de changement climatique",
                "Citizenship and Immigration" => "Ministère des affaires civiques et de l'immigration",
                "Municipal Affairs" => "Ministère des affaires municipales",
                "Finance" => "Ministère des finances",
                "Natural Resources and Forestry" => "Ministère des richesses naturelles et des forêts",
                "Children and Youth Services" => "Ministère des services à l'enfance et à la jeunesse",
                "Government and Consumer Services" => "Ministère des services gouvernementaux et des services aux consommateurs",
                "Community and Social Services" => "Ministère des services sociaux et communautaires",
                "Transportation" => "Ministère des transports",
                "Northern Development and Mines" => "Ministère du développement du nord et des mines",
                "Economic Development and Growth" => "Ministère du développement économique et de la croissance",
                "Housing" => "Ministère du logement",
                "Attorney General" => "Ministère du procureur général",
                "Tourism, Culture and Sport" => "Ministère du tourisme, de la culture et du sport",
                "Labour" => "Ministère du travail",
                "Treasury Board Secretariat" => "Secrétariat du conseil du trésor");
            }

            $ontario_value = ($user->get('provincial') == 'Ontario') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'ontario',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $ontario_value,
                'options_values' => $ontario_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry prince-edward-island'>";
            echo "<label for='prince-edward-island' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $pei_ministries = array();
            if (get_current_language() == 'en'){
                $pei_ministries = array("Agriculture and Fisheries" => "Agriculture and Fisheries",
                "Communities, Land and Environment" => "Communities, Land and Environment",
                "Economic Development and Tourism" => "Economic Development and Tourism",
                "Education, Early Learning and Culture" => "Education, Early Learning and Culture",
                "Family and Human Services" => "Family and Human Services",
                "Finance" => "Finance",
                "Health and Wellness" => "Health and Wellness",
                "Justice and Public Safety" => "Justice and Public Safety",
                "Transportation, Infrastructure and Energy" => "Transportation, Infrastructure and Energy",
                "Workforce and Advanced Learning" => "Workforce and Advanced Learning");
            } else {
                $pei_ministries = array("Agriculture and Fisheries" => "Agriculture et Pêches",
                "Communities, Land and Environment" => "Communautés, Terres et Environnement",
                "Economic Development and Tourism" => "Développement économique et Tourisme",
                "Education, Early Learning and Culture" => "Éducation, Développement préscolaire et Culture",
                "Finance" => "Finances",
                "Justice and Public Safety" => "Justice et Sécurité publique",
                "Workforce and Advanced Learning" => "Main-d’œuvre et Études supérieures",
                "Health and Wellness" => "Santé et Mieux-être",
                "Family and Human Services" => "Services à la famille et à la personne",
                "Transportation, Infrastructure and Energy" => "Transports, Infrastructure et Énergie");
            }

            $pei_value = ($user->get('provincial') == 'Prince Edward Island') ? $user->get('ministry'): "";

            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'prince-edward-island',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $pei_value,
                'options_values' => $pei_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry quebec'>";
            echo "<label for='quebec' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $quebec_ministries = array("Agence de la santé et des services sociaux de Chaudière-Appalaches" => "Agence de la santé et des services sociaux de Chaudière-Appalaches",
            "Agence de la santé et des services sociaux de la Capitale-Nationale" => "Agence de la santé et des services sociaux de la Capitale-Nationale",
            "Agence de la santé et des services sociaux de la Côte-Nord" => "Agence de la santé et des services sociaux de la Côte-Nord",
            "Agence de la santé et des services sociaux de la Gaspésie-Iles-de-la-Madeleine" => "Agence de la santé et des services sociaux de la Gaspésie-Iles-de-la-Madeleine",
            "Agence de la santé et des services sociaux de la Mauricie et du Centre-du-Québecc" => "Agence de la santé et des services sociaux de la Mauricie et du Centre-du-Québecc",
            "Agence de la santé et des services sociaux de l'Abitibi-Témiscamingue" => "Agence de la santé et des services sociaux de l'Abitibi-Témiscamingue",
            "Agence de la santé et des services sociaux de Lanaudière" => "Agence de la santé et des services sociaux de Lanaudière",
            "Agence de la santé et des services sociaux de Laval" => "Agence de la santé et des services sociaux de Laval",
            "Agence de la santé et des services sociaux de l'Estrie" => "Agence de la santé et des services sociaux de l'Estrie",
            "Agence de la santé et des services sociaux de l'Outaouais" => "Agence de la santé et des services sociaux de l'Outaouais",
            "Agence de la santé et des services sociaux de Montréal" => "Agence de la santé et des services sociaux de Montréal",
            "Agence de la santé et des services sociaux des Laurentides" => "Agence de la santé et des services sociaux des Laurentides",
            "Agence de la santé et des services sociaux du Bas-Saint-Laurent" => "Agence de la santé et des services sociaux du Bas-Saint-Laurent",
            "Agence de la santé et des services sociaux du Saguenay–Lac-Saint-Jean" => "Agence de la santé et des services sociaux du Saguenay–Lac-Saint-Jean",
            "Agence métropolitaine de transport" => "Agence métropolitaine de transport",
            "Aide financière aux études" => "Aide financière aux études",
            "Assemblée nationale du Québec" => "Assemblée nationale du Québec",
            "Autorité des marchés financiers" => "Autorité des marchés financiers",
            "Bibliothèque et Archives nationales du Québec" => "Bibliothèque et Archives nationales du Québec",
            "Bureau d'audiences publiques sur l'environnement" => "Bureau d'audiences publiques sur l'environnement",
            "Bureau de normalisation du Québec" => "Bureau de normalisation du Québec",
            "Bureau des infractions et amendes" => "Bureau des infractions et amendes",
            "Bureau du coroner" => "Bureau du coroner",
            "Bureau du forestier en chef" => "Bureau du forestier en chef",
            "Caisse de dépôt et placement du Québec" => "Caisse de dépôt et placement du Québec",
            "Centre de Conservation du Québec" => "Centre de Conservation du Québec",
            "Centre de gestion de l'équipement roulant" => "Centre de gestion de l'équipement roulant",
            "Centre de la francophonie des Amériques" => "Centre de la francophonie des Amériques",
            "Centre de recherche industrielle du Québec" => "Centre de recherche industrielle du Québec",
            "Centre de services partagés du Québec" => "Centre de services partagés du Québec",
            "Centre de toxicologie du Québec" => "Centre de toxicologie du Québec",
            "Centre d'étude sur la pauvreté et l'exclusion sociale" => "Centre d'étude sur la pauvreté et l'exclusion sociale",
            "Centre d'expertise des grands organismes" => "Centre d'expertise des grands organismes",
            "Centre d'expertise hydrique du Québec" => "Centre d'expertise hydrique du Québec",
            "Centre intégré de santé et de services sociaux de la Montérégie-Centre" => "Centre intégré de santé et de services sociaux de la Montérégie-Centre",
            "Centre intégré de santé et de services sociaux de la Montérégie–Centre" => "Centre intégré de santé et de services sociaux de la Montérégie–Centre",
            "Centre intégré de santé et de services sociaux de la Montérégie-Ouest" => "Centre intégré de santé et de services sociaux de la Montérégie-Ouest",
            "Centre intégré de santé et de services sociaux des Îles" => "Centre intégré de santé et de services sociaux des Îles",
            "Centre intégré universitaire de santé et de services sociaux du Nord-de-l'Île-de-Montréal" => "Centre intégré universitaire de santé et de services sociaux du Nord-de-l'Île-de-Montréal",
            "Centre intégré universitaire de santé et services sociaux de l'Est-de-l'Île-de-Montréal" => "Centre intégré universitaire de santé et services sociaux de l'Est-de-l'Île-de-Montréal",
            "Centre intégré universitaire du Centre-Est-de-l'Île-de-Montréal" => "Centre intégré universitaire du Centre-Est-de-l'Île-de-Montréal",
            "Centre intégré universitaire du Centre-Ouest-de-l'Île-de-Montréal" => "Centre intégré universitaire du Centre-Ouest-de-l'Île-de-Montréal",
            "Centre local de services communautaires" => "Centre local de services communautaires",
            "Centre régional de santé et de services sociaux de la Baie-James" => "Centre régional de santé et de services sociaux de la Baie-James",
            "Comité consultatif du travail et de la main-d'œuvre" => "Comité consultatif du travail et de la main-d'œuvre",
            "Comité consultatif sur l'accessibilité financière aux études" => "Comité consultatif sur l'accessibilité financière aux études",
            "Comité de déontologie policière" => "Comité de déontologie policière",
            "Comité pour la prestation des services de santé et des services sociaux aux personnes issues des communautés ethnoculturelles" => "Comité pour la prestation des services de santé et des services sociaux aux personnes issues des communautés ethnoculturelles",
            "Commissaire à la déontologie policière" => "Commissaire à la déontologie policière",
            "Commissaire à la lutte contre la corruption" => "Commissaire à la lutte contre la corruption",
            "Commissaire à la santé et au bien-être" => "Commissaire à la santé et au bien-être",
            "Commissaire à l'éthique et à la déontologie" => "Commissaire à l'éthique et à la déontologie",
            "Commissaire au lobbyisme" => "Commissaire au lobbyisme",
            "Commission consultative de l'enseignement privé" => "Commission consultative de l'enseignement privé",
            "Commission d'accès à l'information" => "Commission d'accès à l'information",
            "Commission de la capitale nationale du Québec" => "Commission de la capitale nationale du Québec",
            "Commission de la construction du Québec" => "Commission de la construction du Québec",
            "Commission de la fonction publique" => "Commission de la fonction publique",
            "Commission de la qualité de l'environnement Kativik" => "Commission de la qualité de l'environnement Kativik",
            "Commission de la représentation électorale" => "Commission de la représentation électorale",
            "Commission de l'éducation en langue anglaise" => "Commission de l'éducation en langue anglaise",
            "Commission de l'éthique de la science et de la technologie" => "Commission de l'éthique de la science et de la technologie",
            "Commission de protection du territoire agricole du Québec" => "Commission de protection du territoire agricole du Québec",
            "Commission de toponymie" => "Commission de toponymie",
            "Commission d'enquête sur l’octroi et la gestion des contrats publics dans l’industrie de la construction" => "Commission d'enquête sur l’octroi et la gestion des contrats publics dans l’industrie de la construction",
            "Commission des droits de la personne et des droits de la jeunesse" => "Commission des droits de la personne et des droits de la jeunesse",
            "Commission des normes, de l'équité, de la santé et de la sécurité du travail" => "Commission des normes, de l'équité, de la santé et de la sécurité du travail",
            "Commission des partenaires du marché du travail" => "Commission des partenaires du marché du travail",
            "Commission des services juridiques" => "Commission des services juridiques",
            "Commission des transports du Québec" => "Commission des transports du Québec",
            "Commission des valeurs mobilières du Québec (voir Autorité des marchés financiers)" => "Commission des valeurs mobilières du Québec (voir Autorité des marchés financiers)",
            "Commission d'évaluation de l'enseignement collégial" => "Commission d'évaluation de l'enseignement collégial",
            "Commission municipale du Québec" => "Commission municipale du Québec",
            "Commission québécoise des libérations conditionnelles" => "Commission québécoise des libérations conditionnelles",
            "Conseil consultatif de la lecture et du livre" => "Conseil consultatif de la lecture et du livre",
            "Conseil cri de la santé et des services sociaux de la Baie James" => "Conseil cri de la santé et des services sociaux de la Baie James",
            "Conseil de gestion de l'assurance parentale" => "Conseil de gestion de l'assurance parentale",
            "Conseil de la justice administrative" => "Conseil de la justice administrative",
            "Conseil de la magistrature du Québec" => "Conseil de la magistrature du Québec",
            "Conseil de l'Ordre du Québec" => "Conseil de l'Ordre du Québec",
            "Conseil des appellations réservées et des termes valorisants" => "Conseil des appellations réservées et des termes valorisants",
            "Conseil des arts et des lettres du Québec" => "Conseil des arts et des lettres du Québec",
            "Conseil du statut de la femme" => "Conseil du statut de la femme",
            "Conseil supérieur de la langue française" => "Conseil supérieur de la langue française",
            "Conseil supérieur de l'éducation" => "Conseil supérieur de l'éducation",
            "Conseils régionaux des partenaires du marché du travail" => "Conseils régionaux des partenaires du marché du travail",
            "Conservatoire de musique et d'art dramatique du Québec" => "Conservatoire de musique et d'art dramatique du Québec",
            "Corporation d'urgence-santé" => "Corporation d'urgence-santé",
            "Cour d'appel du Québec" => "Cour d'appel du Québec",
            "Cour du Québec" => "Cour du Québec",
            "Cour supérieure du Québec" => "Cour supérieure du Québec",
            "Curateur public du Québec" => "Curateur public du Québec",
            "Directeur de l'état civil" => "Directeur de l'état civil",
            "Directeur des poursuites criminelles et pénales" => "Directeur des poursuites criminelles et pénales",
            "Directeur général des élections du Québec" => "Directeur général des élections du Québec",
            "École nationale de police du Québec" => "École nationale de police du Québec",
            "École nationale des pompiers du Québec" => "École nationale des pompiers du Québec",
            "Emploi-Québec" => "Emploi-Québec",
            "Épargne Placements Québec" => "Épargne Placements Québec",
            "Financière agricole du Québec" => "Financière agricole du Québec",
            "Fondation de la faune du Québec" => "Fondation de la faune du Québec",
            "Fonds d'aide aux recours collectifs" => "Fonds d'aide aux recours collectifs",
            "Fonds de la recherche en santé du Québec" => "Fonds de la recherche en santé du Québec",
            "Fonds de recherche du Québec – Scientifique en chef" => "Fonds de recherche du Québec – Scientifique en chef",
            "Fonds québécois de la recherche sur la nature et les technologies" => "Fonds québécois de la recherche sur la nature et les technologies",
            "Fonds québécois de la recherche sur la société et la culture" => "Fonds québécois de la recherche sur la société et la culture",
            "Héma-Québec" => "Héma-Québec",
            "Hydro-Québec" => "Hydro-Québec",
            "Indemnisation des victimes d’actes criminels" => "Indemnisation des victimes d’actes criminels",
            "Institut de la statistique du Québec" => "Institut de la statistique du Québec",
            "Institut de tourisme et d'hôtellerie du Québec" => "Institut de tourisme et d'hôtellerie du Québec",
            "Institut national de santé publique du Québec" => "Institut national de santé publique du Québec",
            "Institut national des mines" => "Institut national des mines",
            "Institut national d'excellence en santé et en services sociaux" => "Institut national d'excellence en santé et en services sociaux",
            "Investissement Québec" => "Investissement Québec",
            "La Financière agricole du Québec - Développement international" => "La Financière agricole du Québec - Développement international",
            "Les Publications du Québec" => "Les Publications du Québec",
            "Ministère de l’Éducation et de l’Enseignement supérieur" => "Ministère de l’Éducation et de l’Enseignement supérieur",
            "Ministère de la Culture et des Communications" => "Ministère de la Culture et des Communications",
            "Ministère de la Famille" => "Ministère de la Famille",
            "Ministère de la Justice" => "Ministère de la Justice",
            "Ministère de la Santé et des Services sociaux" => "Ministère de la Santé et des Services sociaux",
            "Ministère de la Sécurité publique" => "Ministère de la Sécurité publique",
            "Ministère de l'Agriculture, des Pêcheries et de l'Alimentation" => "Ministère de l'Agriculture, des Pêcheries et de l'Alimentation",
            "Ministère de l'Économie, de la Science et de l'Innovation" => "Ministère de l'Économie, de la Science et de l'Innovation",
            "Ministère de l'Énergie et des Ressources naturelles" => "Ministère de l'Énergie et des Ressources naturelles",
            "Ministère de l'Immigration, de la Diversité et de l'Inclusion" => "Ministère de l'Immigration, de la Diversité et de l'Inclusion",
            "Ministère des Affaires municipales et de l'Occupation du territoire" => "Ministère des Affaires municipales et de l'Occupation du territoire",
            "Ministère des Finances" => "Ministère des Finances",
            "Ministère des Forêts, de la Faune et des Parcs" => "Ministère des Forêts, de la Faune et des Parcs",
            "Ministère des Relations internationales et de la Francophonie" => "Ministère des Relations internationales et de la Francophonie",
            "Ministère des Transports, de la Mobilité durable et de l'Électrification des transports" => "Ministère des Transports, de la Mobilité durable et de l'Électrification des transports",
            "Ministère du Conseil exécutif" => "Ministère du Conseil exécutif",
            "Ministère du Développement durable, de l'Environnement et de la Lutte contre les changements climatiques" => "Ministère du Développement durable, de l'Environnement et de la Lutte contre les changements climatiques",
            "Ministère du Tourisme" => "Ministère du Tourisme",
            "Ministère du Travail, de l'Emploi et de la Solidarité sociale" => "Ministère du Travail, de l'Emploi et de la Solidarité sociale",
            "Musée d'art contemporain de Montréal" => "Musée d'art contemporain de Montréal",
            "Musée de la civilisation" => "Musée de la civilisation",
            "Musée de la Place royale" => "Musée de la Place royale",
            "Musée de l'Amérique francophone" => "Musée de l'Amérique francophone",
            "Musée national des beaux-arts du Québec" => "Musée national des beaux-arts du Québec",
            "Office de la protection du consommateur" => "Office de la protection du consommateur",
            "Office de la Sécurité du revenu des chasseurs et piégeurs cris" => "Office de la Sécurité du revenu des chasseurs et piégeurs cris",
            "Office des personnes handicapées du Québec" => "Office des personnes handicapées du Québec",
            "Office des professions du Québec" => "Office des professions du Québec",
            "Office franco-québécois pour la jeunesse" => "Office franco-québécois pour la jeunesse",
            "Office Québec-Monde pour la jeunesse" => "Office Québec-Monde pour la jeunesse",
            "Office québécois de la langue française" => "Office québécois de la langue française",
            "Palais des congrès de Montréal" => "Palais des congrès de Montréal",
            "Protecteur du citoyen" => "Protecteur du citoyen",
            "RECYC-QUÉBEC" => "RECYC-QUÉBEC",
            "Régie de l'assurance maladie du Québec" => "Régie de l'assurance maladie du Québec",
            "Régie de l'assurance-dépôts du Québec (voir Autorité des marchés financiers)" => "Régie de l'assurance-dépôts du Québec (voir Autorité des marchés financiers)",
            "Régie de l'énergie" => "Régie de l'énergie",
            "Régie des alcools, des courses et des jeux" => "Régie des alcools, des courses et des jeux",
            "Régie des installations olympiques" => "Régie des installations olympiques",
            "Régie des marchés agricoles et alimentaires du Québec" => "Régie des marchés agricoles et alimentaires du Québec",
            "Régie du bâtiment du Québec" => "Régie du bâtiment du Québec",
            "Régie du Cinéma" => "Régie du Cinéma",
            "Régie du logement" => "Régie du logement",
            "Registraire des entreprises" => "Registraire des entreprises",
            "Registre des droits personnels et réels mobiliers" => "Registre des droits personnels et réels mobiliers",
            "Registre des lobbyistes" => "Registre des lobbyistes",
            "Registre foncier du Québec" => "Registre foncier du Québec",
            "Retraite Québec" => "Retraite Québec",
            "Revenu Québec" => "Revenu Québec",
            "Secrétariat à la condition féminine" => "Secrétariat à la condition féminine",
            "Secrétariat à la jeunesse" => "Secrétariat à la jeunesse",
            "Secrétariat à la politique linguistique" => "Secrétariat à la politique linguistique",
            "Secrétariat à l'accès à l'information et à la réforme des institutions démocratiques" => "Secrétariat à l'accès à l'information et à la réforme des institutions démocratiques",
            "Secrétariat aux affaires autochtones" => "Secrétariat aux affaires autochtones",
            "Secrétariat aux affaires intergouvernementales canadiennes" => "Secrétariat aux affaires intergouvernementales canadiennes",
            "Secrétariat aux aînés" => "Secrétariat aux aînés",
            "Secrétariat de l'Ordre national du Québec" => "Secrétariat de l'Ordre national du Québec",
            "Secrétariat du Conseil du trésor" => "Secrétariat du Conseil du trésor",
            "Secrétariat du travail" => "Secrétariat du travail",
            "Société de développement de la Baie-James" => "Société de développement de la Baie-James",
            "Société de développement des entreprises culturelles" => "Société de développement des entreprises culturelles",
            "Société de financement des infrastructures locales du Québec" => "Société de financement des infrastructures locales du Québec",
            "Société de la Place des Arts" => "Société de la Place des Arts",
            "Société de l'assurance automobile du Québec" => "Société de l'assurance automobile du Québec",
            "Société de télédiffusion du Québec (Télé-Québec)" => "Société de télédiffusion du Québec (Télé-Québec)",
            "Société des alcools du Québec" => "Société des alcools du Québec",
            "Société des établissements de plein air du Québec" => "Société des établissements de plein air du Québec",
            "Société des loteries du Québec (Loto-Québec)" => "Société des loteries du Québec (Loto-Québec)",
            "Société des traversiers du Québec" => "Société des traversiers du Québec",
            "Société d'habitation du Québec" => "Société d'habitation du Québec",
            "Société du Centre des congrès de Québec" => "Société du Centre des congrès de Québec",
            "Société du Grand Théâtre de Québec" => "Société du Grand Théâtre de Québec",
            "Société du Palais des congrès de Montréal" => "Société du Palais des congrès de Montréal",
            "Société du parc industriel et portuaire de Bécancour" => "Société du parc industriel et portuaire de Bécancour",
            "Société du Plan Nord" => "Société du Plan Nord",
            "Société québécoise des infrastructures" => "Société québécoise des infrastructures",
            "Société québécoise d'information juridique" => "Société québécoise d'information juridique",
            "Tribunal administratif des marchés financiers" => "Tribunal administratif des marchés financiers",
            "Tribunal administratif du Québec" => "Tribunal administratif du Québec",
            "Tribunal administratif du travail" => "Tribunal administratif du travail",
            "Tribunal des droits de la personne" => "Tribunal des droits de la personne",
            "Vérificateur général du Québec" => "Vérificateur général du Québec");

            $quebec_value = ($user->get('provincial') == 'Quebec') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'quebec',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $quebec_value,
                'options_values' => $quebec_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry saskatchewan'>";
            echo "<label for='saskatchewan' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $saskatchewan_ministries = array("Advanced Education" => "Advanced Education",
            "Agriculture" => "Agriculture",
            "Central Services" => "Central Services",
            "Economy" => "Economy",
            "Education" => "Education",
            "Energy and Resources" => "Energy and Resources",
            "Environment" => "Environment",
            "Finance" => "Finance",
            "Government Relations" => "Government Relations",
            "Health" => "Health",
            "Highways and Infrastructure" => "Highways and Infrastructure",
            "Justice" => "Justice",
            "Labour Relations and Workplace Safety" => "Labour Relations and Workplace Safety",
            "Parks, Culture and Sport" => "Parks, Culture and Sport",
            "Social Services" => "Social Services");

            $saskatchewan_value = ($user->get('provincial') == 'Saskatchewan') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'saskatchewan',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $saskatchewan_value,
                'options_values' => $saskatchewan_ministries,
            ));

            echo "</div></div>";
            echo "<div class='form-group col-xs-12 ministry yukon'>";
            echo "<label for='yukon' class='col-sm-4'>".elgg_echo("gcconnex_profile:basic:ministry")."</label>";
            echo '<div class="col-sm-8">';

            $yukon_ministries = array("Community Services" => "Community Services",
            "Economic Development" => "Economic Development",
            "Education" => "Education",
            "Energy, Mines and Resources" => "Energy, Mines and Resources",
            "Environment" => "Environment",
            "Executive Council Office" => "Executive Council Office",
            "Finance" => "Finance",
            "French Language Services Directorate" => "French Language Services Directorate",
            "Health and Social Services" => "Health and Social Services",
            "Highways and Public Works" => "Highways and Public Works",
            "Justice" => "Justice",
            "Public Service Commission" => "Public Service Commission",
            "Tourism and Culture" => "Tourism and Culture",
            "Women's Directorate" => "Women's Directorate");

            $yukon_value = ($user->get('provincial') == 'Yukon') ? $user->get('ministry'): "";
            
            echo elgg_view('input/select', array(
                'name' => 'ministry',
                'id' => 'yukon',
                'class' => 'form-control gcconnex-basic-ministry',
                'value' => $yukon_value,
                'options_values' => $yukon_ministries,
            ));
            
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

            $universities = array("Acadia University" => "Acadia University",
            "Algoma University" => "Algoma University",
            "Athabasca University" => "Athabasca University",
            "Bishop's University" => "Bishop's University",
            "Brandon University" => "Brandon University",
            "Brescia University College" => "Brescia University College",
            "Brock University" => "Brock University",
            "Campion College" => "Campion College",
            "Canada Mennonite University" => "Canada Mennonite University",
            "Cape Breton University" => "Cape Breton University",
            "Carleton University" => "Carleton University",
            "Concordia University" => "Concordia University",
            "Concordia University of Edmonton" => "Concordia University of Edmonton",
            "Dalhousie University" => "Dalhousie University",
            "Dominican University College" => "Dominican University College",
            "École de technolgie supérieure" => "École de technolgie supérieure",
            "École des Hautes Études Commerciales de Montréal (HEC Montréal)" => "École des Hautes Études Commerciales de Montréal (HEC Montréal)",
            "École nationale d'administration publique (ENAP)" => "École nationale d'administration publique (ENAP)",
            "École Polytechnique de Montréal" => "École Polytechnique de Montréal",
            "Emily Carr University of Art and Design" => "Emily Carr University of Art and Design",
            "First Nations University of Canada" => "First Nations University of Canada",
            "Glendon College (York University)" => "Glendon College (York University)",
            "Huron University College" => "Huron University College",
            "Institut national de la recherche scientifique" => "Institut national de la recherche scientifique",
            "King's University College" => "King's University College",
            "Kwantlen Polytechnic University" => "Kwantlen Polytechnic University",
            "Lakehead University" => "Lakehead University",
            "Laurentian University" => "Laurentian University",
            "Luther College" => "Luther College",
            "MacEwan University" => "MacEwan University",
            "McGill University" => "McGill University",
            "McMaster University" => "McMaster University",
            "Memorial University" => "Memorial University",
            "Mount Allison University" => "Mount Allison University",
            "Mount Royal University" => "Mount Royal University",
            "Mount Saint Vincent University" => "Mount Saint Vincent University",
            "Nipissing University" => "Nipissing University",
            "Nova Scotia College of Art and Design University" => "Nova Scotia College of Art and Design University",
            "Ontario College of Art and Design University" => "Ontario College of Art and Design University",
            "Queen's University" => "Queen's University",
            "Redeemer University College" => "Redeemer University College",
            "Royal Military College" => "Royal Military College",
            "Royal Roads University" => "Royal Roads University",
            "Ryerson University" => "Ryerson University",
            "Saint Mary's University" => "Saint Mary's University",
            "Saint Paul University" => "Saint Paul University",
            "Simon Fraser University" => "Simon Fraser University",
            "St. Francis Xavier University" => "St. Francis Xavier University",
            "St. Jerome's University" => "St. Jerome's University",
            "St. Paul's College" => "St. Paul's College",
            "St. Thomas More College" => "St. Thomas More College",
            "St. Thomas University" => "St. Thomas University",
            "Télé-université (TÉLUQ)" => "Télé-université (TÉLUQ)",
            "The King's University" => "The King's University",
            "Thompson Rivers University" => "Thompson Rivers University",
            "Trent University" => "Trent University",
            "Trinity Western University" => "Trinity Western University",
            "Université de Moncton" => "Université de Moncton",
            "Université de Montréal" => "Université de Montréal",
            "Université de Saint-Boniface" => "Université de Saint-Boniface",
            "Université de Sherbrooke" => "Université de Sherbrooke",
            "Université du Québec" => "Université du Québec",
            "Université du Québec à Chicoutimi" => "Université du Québec à Chicoutimi",
            "Université du Québec à Montréal" => "Université du Québec à Montréal",
            "Université du Québec à Rimouski" => "Université du Québec à Rimouski",
            "Université du Québec à Trois‑Rivières" => "Université du Québec à Trois‑Rivières",
            "Université du Québec en Abitibi‑Témiscamingue" => "Université du Québec en Abitibi‑Témiscamingue",
            "Université du Québec en Outaouais" => "Université du Québec en Outaouais",
            "Université Laval" => "Université Laval",
            "Université Sainte‑Anne" => "Université Sainte‑Anne",
            "University of Alberta" => "University of Alberta",
            "University of British Columbia" => "University of British Columbia",
            "University of Calgary" => "University of Calgary",
            "University of Guelph" => "University of Guelph",
            "University of King's College" => "University of King's College",
            "University of Lethbridge" => "University of Lethbridge",
            "University of Manitoba" => "University of Manitoba",
            "University of New Brunswick" => "University of New Brunswick",
            "University of Northern British Columbia" => "University of Northern British Columbia",
            "University of Ontario Institute of Technology" => "University of Ontario Institute of Technology",
            "University of Ottawa" => "University of Ottawa",
            "University of Prince Edward Island" => "University of Prince Edward Island",
            "University of Regina" => "University of Regina",
            "University of Saskatchewan" => "University of Saskatchewan",
            "University of St. Michael's College" => "University of St. Michael's College",
            "University of Sudbury" => "University of Sudbury",
            "University of the Fraser Valley" => "University of the Fraser Valley",
            "University of Toronto" => "University of Toronto",
            "University of Trinity College" => "University of Trinity College",
            "University of Victoria" => "University of Victoria",
            "University of Waterloo" => "University of Waterloo",
            "University of Western Ontario" => "University of Western Ontario",
            "University of Windsor" => "University of Windsor",
            "University of Winnipeg" => "University of Winnipeg",
            "Vancouver Island University" => "Vancouver Island University",
            "Victoria University" => "Victoria University",
            "Wilfrid Laurier University" => "Wilfrid Laurier University",
            "York University" => "York University");

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

            $colleges = array("Alberta College of Art and Design" => "Alberta College of Art and Design",
            "Algonquin College" => "Algonquin College",
            "Assiniboine Community College" => "Assiniboine Community College",
            "Aurora College" => "Aurora College",
            "Bow Valley College" => "Bow Valley College",
            "British Columbia Institute of Technology" => "British Columbia Institute of Technology",
            "Cambrian College of Applied Arts and Technology" => "Cambrian College of Applied Arts and Technology",
            "Camosun College" => "Camosun College",
            "Canadore College of Applied Arts and Technology" => "Canadore College of Applied Arts and Technology",
            "Capilano University" => "Capilano University",
            "Carlton Trail College" => "Carlton Trail College",
            "Cégep André-Laurendeau" => "Cégep André-Laurendeau",
            "Cégep de Chicoutimi" => "Cégep de Chicoutimi",
            "Cégep de Jonquière" => "Cégep de Jonquière",
            "Cégep de l’Abitibi-Témiscamingue" => "Cégep de l’Abitibi-Témiscamingue",
            "Cégep de la Gaspésie et des Îles" => "Cégep de la Gaspésie et des Îles",
            "Cégep de La Pocatière" => "Cégep de La Pocatière",
            "Cégep de Matane" => "Cégep de Matane",
            "Cégep de Rimouski" => "Cégep de Rimouski",
            "Cégep de Rivière-du-Loup" => "Cégep de Rivière-du-Loup",
            "Cégep de Sainte-Foy" => "Cégep de Sainte-Foy",
            "Cégep de Saint-Félicien" => "Cégep de Saint-Félicien",
            "Cégep de Saint-Laurent" => "Cégep de Saint-Laurent",
            "Cégep de Sept-Îles" => "Cégep de Sept-Îles",
            "Cégep de Sherbrooke" => "Cégep de Sherbrooke",
            "Cégep de Thetford" => "Cégep de Thetford",
            "Cégep de Trois-Rivières" => "Cégep de Trois-Rivières",
            "Cégep de Victoriaville" => "Cégep de Victoriaville",
            "Cégep Édouard-Montpetit" => "Cégep Édouard-Montpetit",
            "Cégep Garneau" => "Cégep Garneau",
            "Cégep Heritage College" => "Cégep Heritage College",
            "Cégep John Abbott College" => "Cégep John Abbott College",
            "Cégep Limoilou" => "Cégep Limoilou",
            "Cégep Marie-Victorin" => "Cégep Marie-Victorin",
            "Cégep régional de Lanaudière" => "Cégep régional de Lanaudière",
            "Cégep Saint-Jean-sur-Richelieu" => "Cégep Saint-Jean-sur-Richelieu",
            "Centennial College" => "Centennial College",
            "Centre for Nursing Studies" => "Centre for Nursing Studies",
            "Champlain Regional College" => "Champlain Regional College",
            "Collège Acadie Î.-P.-É." => "Collège Acadie Î.-P.-É.",
            "Collège André-Grasset" => "Collège André-Grasset",
            "Collège Boréal" => "Collège Boréal",
            "Collège communautaire du Nouveau-Brunswick" => "Collège communautaire du Nouveau-Brunswick",
            "Collège de Maisonneuve" => "Collège de Maisonneuve",
            "Collège Éducacentre" => "Collège Éducacentre",
            "Collège LaSalle" => "Collège LaSalle",
            "Collège Lionel-Groulx" => "Collège Lionel-Groulx",
            "Collège Mathieu" => "Collège Mathieu",
            "Collège Montmorency" => "Collège Montmorency",
            "Collège nordique francophone" => "Collège nordique francophone",
            "College of New Caledonia" => "College of New Caledonia",
            "College of the North Atlantic (CNA)" => "College of the North Atlantic (CNA)",
            "College of the Rockies" => "College of the Rockies",
            "Collège Shawinigan" => "Collège Shawinigan",
            "Conestoga College Institute of Technology and Advanced Learning" => "Conestoga College Institute of Technology and Advanced Learning",
            "Confederation College" => "Confederation College",
            "Cumberland College" => "Cumberland College",
            "Dalhousie Agricultural Campus of Dalhousie University" => "Dalhousie Agricultural Campus of Dalhousie University",
            "Douglas College" => "Douglas College",
            "Dumont Technical Institute" => "Dumont Technical Institute",
            "Durham College" => "Durham College",
            "École technique et professionnelle, Université de Saint-Boniface" => "École technique et professionnelle, Université de Saint-Boniface",
            "Emily Carr University of Art and Design" => "Emily Carr University of Art and Design",
            "Fanshawe College of Applied Arts and Technology" => "Fanshawe College of Applied Arts and Technology",
            "First Nations Technical Institute" => "First Nations Technical Institute",
            "Fleming College" => "Fleming College",
            "George Brown College" => "George Brown College",
            "Georgian College of Applied Arts and Technology" => "Georgian College of Applied Arts and Technology",
            "Grande Prairie Regional College" => "Grande Prairie Regional College",
            "Great Plains College" => "Great Plains College",
            "Holland College" => "Holland College",
            "Humber College Institute of Technology & Advanced Learning" => "Humber College Institute of Technology & Advanced Learning",
            "Institut de tourisme et d’hôtellerie du Québec" => "Institut de tourisme et d’hôtellerie du Québec",
            "Justice Institute of British Columbia" => "Justice Institute of British Columbia",
            "Kenjgewin Teg Educational Institute (KTEI)" => "Kenjgewin Teg Educational Institute (KTEI)",
            "Keyano College" => "Keyano College",
            "Kwantlen Polytechnic University" => "Kwantlen Polytechnic University",
            "La Cité" => "La Cité",
            "Lakeland College" => "Lakeland College",
            "Lambton College of Applied Arts and Technology" => "Lambton College of Applied Arts and Technology",
            "Langara College" => "Langara College",
            "Lethbridge College" => "Lethbridge College",
            "Loyalist College" => "Loyalist College",
            "Manitoba Institute of Trades and Technology" => "Manitoba Institute of Trades and Technology",
            "Marine Institute" => "Marine Institute",
            "Medicine Hat College" => "Medicine Hat College",
            "Michener Institute of Education at UHN" => "Michener Institute of Education at UHN",
            "Mohawk College" => "Mohawk College",
            "Native Education College" => "Native Education College",
            "New Brunswick College of Craft and Design" => "New Brunswick College of Craft and Design",
            "New Brunswick Community College" => "New Brunswick Community College",
            "Niagara College" => "Niagara College",
            "Nicola Valley Institute of Technology" => "Nicola Valley Institute of Technology",
            "NorQuest College" => "NorQuest College",
            "North Island College" => "North Island College",
            "North West College" => "North West College",
            "Northern Alberta Institute of Technology (NAIT)" => "Northern Alberta Institute of Technology (NAIT)",
            "Northern College" => "Northern College",
            "Northern Lakes College" => "Northern Lakes College",
            "Northern Lights College" => "Northern Lights College",
            "Northlands College" => "Northlands College",
            "Northwest Community College" => "Northwest Community College",
            "Nova Scotia Community College (NSCC)" => "Nova Scotia Community College (NSCC)",
            "Nunavut Arctic College" => "Nunavut Arctic College",
            "Okanagan College" => "Okanagan College",
            "Olds College" => "Olds College",
            "Parkland College" => "Parkland College",
            "Portage College" => "Portage College",
            "Red Deer College" => "Red Deer College",
            "Red River College of Applied Arts, Science and Technology" => "Red River College of Applied Arts, Science and Technology",
            "Saskatchewan Indian Institute of Technologies (SIIT)" => "Saskatchewan Indian Institute of Technologies (SIIT)",
            "Saskatchewan Polytechnic" => "Saskatchewan Polytechnic",
            "Sault College" => "Sault College",
            "Selkirk College" => "Selkirk College",
            "Seneca College of Applied Arts and Technology" => "Seneca College of Applied Arts and Technology",
            "Southeast College" => "Southeast College",
            "Southern Alberta Institute of Technology (SAIT)" => "Southern Alberta Institute of Technology (SAIT)",
            "St. Clair College" => "St. Clair College",
            "St. Lawrence College" => "St. Lawrence College",
            "TAV College" => "TAV College",
            "Université Sainte-Anne, Collège de l’Acadie" => "Université Sainte-Anne, Collège de l’Acadie",
            "University College of the North" => "University College of the North",
            "University of the Fraser Valley" => "University of the Fraser Valley",
            "Vancouver Community College" => "Vancouver Community College",
            "Vancouver Island University" => "Vancouver Island University",
            "Vanier College" => "Vanier College",
            "Yukon College" => "Yukon College");

            echo elgg_view('input/select', array(
                'name' => $field,
                'id' => $field,
                'class' => "gcconnex-basic-{$field}",
                'value' => $value,
                'options_values' => $colleges, 
            ));       

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
    $institution = ($user->institution == "university") ? $user->university: $user->college;
    echo '<div class="gcconnex-profile-dept">' . $institution . '</div>';

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
    echo '<div class="gcconnex-profile-dept">' . $provinces[$user->provincial] . ' / ' . $ministries[$user->provincial][$user->ministry] . '</div>';
// otherwise if user is public servant
} else if(strcmp($user->user_type, 'federal') == 0 ) {
    echo '<h3 class="mrgn-tp-0">' . $user->job . '</h3>';
    echo '<div class="gcconnex-profile-dept">' . $user->federal . '</div>';
} else {
    echo '<h3 class="mrgn-tp-0">' . $user->job . '</h3>';
    echo '<div class="gcconnex-profile-dept">' . $user->department . '</div>';
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

