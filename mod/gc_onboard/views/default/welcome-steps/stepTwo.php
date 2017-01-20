<?php
/*
 * stepTwo.php - Welcome
 *
 * Second step of welcome module. Matches colleagues based on department from ambassador group and popular members.
 */

 elgg_load_library('elgg:onboarding');
?>

<div class="panel-heading clearfix">
    <h2 class="pull-left">
        <?php echo elgg_echo('onboard:welcome:two:title'); ?>
    </h2>
    <div class="pull-right">
        <?php echo elgg_view('page/elements/step_counter', array('current_step'=>3, 'total_steps'=>5));?>

    </div>
</div>
<div class="panel-body">
    <p>
        <?php echo elgg_echo('onboard:welcome:two:description'); ?>
    </p>
    <div class="clearfix wb-eqht" style="">
        <?php

        //get user entity
        $user = elgg_get_logged_in_user_entity();

        //get user type to decide method of recommending people
        $userType = $user->user_type;
        echo "xxx: " . $userType . "<br>";
        echo "yyy: " . $user->institution . "<br>";
        echo "zzz: " . $user->university . "<br>";
        echo "111: " . $user->job . "<br>";

/////STUDENTS
        if($userType =='student'){

            ///OPTION 1///

            //random offset so we do not recommend the same people all the time
            $student_count = elgg_get_entities_from_metadata(array(
              'type' => 'user',
              'count' => true,
              'metadata_name_value_pairs' => array(array('name' => 'user_type', 'value' => $userType), array('name' => 'institution', 'value' => $user->institution)),
            ));

            if($student_count > 10){
              $offset = rand(0, $student_count - 10);
            } else {
              $offset = 0;
            }

            //get students from the same institution
            $students = elgg_get_entities_from_metadata(array(
              'type' => 'user',
              'offset' => $offset,
              'metadata_name_value_pairs' => array(array('name' => 'user_type', 'value' => $userType), array('name' => 'institution', 'value' => $user->institution)),
            ));

            //set keys for array as the user's guid
            //remvoe friends or if user is returned
            foreach($students as $f => $l){
              $students['"'.$l->guid.'"'] = $l;
              unset($students[$f]);
              //remove friend or logged in user
              if($user->guid == $l->guid || check_entity_relationship($user->guid, 'friend', $l->guid)){
                unset($students['"'.$l->guid.'"']);
              }
            }

            ///OPTION 2///

            //If we dont have six users to recommend, expand search to academics from same institution
            if(count($students) < 6){
              $academics = elgg_get_entities_from_metadata(array(
                'type' => 'user',
                'metadata_name_value_pairs' => array(array('name' => 'user_type', 'value' => 'academic'), array('name' => 'institution', 'value' => $user->institution)),
              ));

              //set keys for array as the guid and remove people who are already friends
              foreach($academics as $f => $l){ $academics['"'.$l->guid.'"'] = $l; unset($academics[$f]); if(check_entity_relationship($user->guid, 'friend', $l->guid)){ unset($academics[$l->guid]); } }
              //combine students with academics
              $students = array_merge($students, $academics);
            }

            ///OPTION 3///

            //If we still dont have 6 users, use user's skills to recommend people from the site
            if(count($students) < 6){
              //retrieve users based on similiar skills
              $match = user_skill_match();

              if($match){
                foreach($match as $k => $l){
                  $match['"'.$l->guid.'"'] = $l;
                  unset($match[$k]);

                  //remove self or friends from retrieved list
                  if($user->guid == $l->guid || check_entity_relationship($user->guid, 'friend', $l->guid)){
                    unset($match['"'.$l->guid.'"']);
                  }
                }

                //get feed back string which shows how they were matched
                $status = $_SESSION['candidate_search_feedback'];

                //combine students with skill matched users
                $students = array_merge($students, $match);
              }
            }

            //we only need six different people to display so lets split the array of users to have only 6
            $students = array_slice($students, 0, 6);

            //make display order random
            shuffle($students);

            if(count($students) == 0){
              echo elgg_echo('onboard:welcome:two:noresults');
            }

            //output the student
            foreach($students as $f => $l){
              $htmloutput = '';
              $site_url = elgg_get_site_url();
              $userGUID=$l->guid;
              $job=$l->job;
              $institution = $l->institution;

              $htmloutput=$htmloutput.'<div style="height:200px; margin-top:25px;" class="col-xs-4 text-center hght-inhrt  onboard-coll">';
              $htmloutput.= elgg_view_entity_icon($l, 'medium', array('use_hover' => false, 'use_link' => false, 'class' => 'elgg-avatar-wet4-sf'));

              $htmloutput=$htmloutput.'<h4 class="h4 mrgn-tp-sm mrgn-bttm-sm"><span class="text-primary">'.$l->getDisplayName().'</span></h4>';
              if(($l->user_type == 'student' || $l->user_type == 'academic') && $institution == $user->institution){
                $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.elgg_echo('gcRegister:occupation:'.$l->user_type).'</p>';
                $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$institution.'</p>';
              } else {
                if($l->department){
                  $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$l->department.'</p>';
                } else if($institution){
                  $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$institution.'</p>';
                }
                $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$status[$l->guid].'</p>';
              }
              //changed connect button to send a friend request we should change the wording
              $htmloutput=$htmloutput.'<a href="#" class="add-friend btn btn-primary mrgn-tp-sm" onclick="addFriendOnboard('.$userGUID.')" id="'.$userGUID.'">'.elgg_echo('friend:add').'</a>';
              $htmloutput=$htmloutput.'</div>';

              echo $htmloutput . '';
            }
/////ACADEMICS
        } else if($userType =='academic') {

          ///OPTION 1///

          //random offset so we do not recommend the same people all the time
          $academic_count = elgg_get_entities_from_metadata(array(
            'type' => 'user',
            'count' => true,
            'metadata_name_value_pairs' => array(array('name' => 'user_type', 'value' => $userType), array('name' => 'institution', 'value' => $user->institution)),
          ));

          if($academic_count > 10){
            $offset = rand(0, $academic_count - 10);
          } else {
            $offset = 0;
          }

          //get academics from the same institution
          $academics = elgg_get_entities_from_metadata(array(
            'type' => 'user',
            'offset' => $offset,
            'metadata_name_value_pairs' => array(array('name' => 'user_type', 'value' => $userType), array('name' => 'institution', 'value' => $user->institution)),
          ));

          //set keys for array as the user's guid
          //remvoe friends or if user is returned
          foreach($academics as $f => $l){
            $academics['"'.$l->guid.'"'] = $l;
            unset($academics[$f]);
            //remove friend or logged in user
            if($user->guid == $l->guid || check_entity_relationship($user->guid, 'friend', $l->guid)){
              unset($academics['"'.$l->guid.'"']);
            }
          }

          ///OPTION 2///

          //If we dont have six users to recommend, expand search to academics from same institution
          if(count($academics) < 6){
            $students = elgg_get_entities_from_metadata(array(
              'type' => 'user',
              'metadata_name_value_pairs' => array(array('name' => 'user_type', 'value' => 'student'), array('name' => 'institution', 'value' => $user->institution)),
            ));

            //set keys for array as the guid and remove people who are already friends
            foreach($students as $f => $l){ $students['"'.$l->guid.'"'] = $l; unset($students[$f]); if(check_entity_relationship($user->guid, 'friend', $l->guid)){ unset($students['"'.$l->guid.'"']); } }
            //combine students with academics
            $academics = array_merge($academics, $students);
          }

          ///OPTION 3///

          //If we still dont have 6 users, use user's skills to recommend people from the site
          if(count($academics) < 6){
            //retrieve users based on similiar skills
            $match = user_skill_match();

            if($match){
              foreach($match as $k => $v){
                $match['"'.$v->guid.'"'] = $v;
                unset($match[$k]);

                if($user->guid == $v->guid || check_entity_relationship($user->guid, 'friend', $v->guid)){
                  unset($match['"'.$v->guid.'"']);
                }
              }

              $status = $_SESSION['candidate_search_feedback'];

              //combine students with academics
              $academics = array_merge($academics, $match);
            }
          }

          //we only need six different people to display so lets split the array of users to have only 6
          $academics = array_slice($academics, 0, 6);

          //make display order random
          shuffle($academics);

          if(count($academics) == 0){
              echo elgg_echo('onboard:welcome:two:noresults');
          }

          //output the student
          foreach($academics as $f => $l){
            $htmloutput = '';
            $site_url = elgg_get_site_url();
            $userGUID=$l->guid;
            $job=$l->job;
            $institution = $l->institution;

            $htmloutput=$htmloutput.'<div style="height:200px; margin-top:25px;" class="col-xs-4 text-center hght-inhrt  onboard-coll">';

            //EW - change to render icon so new ambassador badges can be shown
            $htmloutput.= elgg_view_entity_icon($l, 'medium', array('use_hover' => false, 'use_link' => false, 'class' => 'elgg-avatar-wet4-sf'));

            $htmloutput=$htmloutput.'<h4 class="h4 mrgn-tp-sm mrgn-bttm-sm"><span class="text-primary">'.$l->getDisplayName().'</span></h4>';
            if(($l->user_type == 'student' || $l->user_type == 'academic') && $institution == $user->institution){
              $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.elgg_echo('gcRegister:occupation:'.$l->user_type).'</p>';
              $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$institution.'</p>';
            } else {
              if($l->department){
                $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$l->department.'</p>';
              } else if($institution){
                $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$institution.'</p>';
              }
              $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$status[$l->guid].'</p>';
            }
            //changed connect button to send a friend request we should change the wording
            $htmloutput=$htmloutput.'<a href="#" class="add-friend btn btn-primary mrgn-tp-sm" onclick="addFriendOnboard('.$userGUID.')" id="'.$userGUID.'">'.elgg_echo('friend:add').'</a>';
            $htmloutput=$htmloutput.'</div>';

            echo $htmloutput . '';
          }
/////PUBLIC SERVANTS
        } else {

            //get user's department
            $depart = elgg_get_logged_in_user_entity()->department;

            //explode to look for opposite version (e.g. english / french, french / english)
            $departSeperate = explode(' / ', $depart);

            $depart1 = $departSeperate[0]  . ' / ' . $departSeperate[1];
            $depart2 = $departSeperate[1]  . ' / ' . $departSeperate[0];

            $federal_departments = array();
            if (get_current_language() == 'en'){
                $federal_departments = array("Aboriginal Business Canada" => "Aboriginal Business Canada",
                "Administrative Tribunals Support Service of Canada" => "Administrative Tribunals Support Service of Canada",
                "Agriculture and Agri-Food Canada" => "Agriculture and Agri-Food Canada",
                "Atlantic Canada Opportunities Agency" => "Atlantic Canada Opportunities Agency",
                "Atlantic Pilotage Authority Canada" => "Atlantic Pilotage Authority Canada",
                "Atomic Energy of Canada Limited" => "Atomic Energy of Canada Limited",
                "Auditor General of Canada (Office of the)" => "Auditor General of Canada (Office of the)",
                "Bank of Canada" => "Bank of Canada",
                "Blue Water Bridge Canada" => "Blue Water Bridge Canada",
                "Business Development Bank of Canada" => "Business Development Bank of Canada",
                "Canada Agricultural Review Tribunal" => "Canada Agricultural Review Tribunal",
                "Canada Agriculture and Food Museum" => "Canada Agriculture and Food Museum",
                "Canada Aviation and Space Museum" => "Canada Aviation and Space Museum",
                "Canada Border Services Agency" => "Canada Border Services Agency",
                "Canada Centre for Inland Waters" => "Canada Centre for Inland Waters",
                "Canada Council for the Arts" => "Canada Council for the Arts",
                "Canada Deposit Insurance Corporation" => "Canada Deposit Insurance Corporation",
                "Canada Development Investment Corporation" => "Canada Development Investment Corporation",
                "Canada Economic Development for Quebec Regions" => "Canada Economic Development for Quebec Regions",
                "Canada Employment Insurance Commission" => "Canada Employment Insurance Commission",
                "Canada Firearms Centre" => "Canada Firearms Centre",
                "Canada Gazette" => "Canada Gazette",
                "Canada Industrial Relations Board" => "Canada Industrial Relations Board",
                "Canada Lands Company Limited" => "Canada Lands Company Limited",
                "Canada Mortgage and Housing Corporation" => "Canada Mortgage and Housing Corporation",
                "Canada Pension Plan Investment Board" => "Canada Pension Plan Investment Board",
                "Canada Post" => "Canada Post",
                "Canada Research Chairs" => "Canada Research Chairs",
                "Canada Revenue Agency" => "Canada Revenue Agency",
                "Canada School of Public Service" => "Canada School of Public Service",
                "Canada Science and Technology Museum Corporation" => "Canada Science and Technology Museum Corporation",
                "Canadian Air Transport Security Authority" => "Canadian Air Transport Security Authority",
                "Canadian Army" => "Canadian Army",
                "Canadian Cadet Organizations" => "Canadian Cadet Organizations",
                "Canadian Centre for Occupational Health and Safety" => "Canadian Centre for Occupational Health and Safety",
                "Canadian Coast Guard" => "Canadian Coast Guard",
                "Canadian Commercial Corporation" => "Canadian Commercial Corporation",
                "Canadian Conservation Institute" => "Canadian Conservation Institute",
                "Canadian Cultural Property Export Review Board" => "Canadian Cultural Property Export Review Board",
                "Canadian Dairy Commission" => "Canadian Dairy Commission",
                "Canadian Environmental Assessment Agency" => "Canadian Environmental Assessment Agency",
                "Canadian Food Inspection Agency" => "Canadian Food Inspection Agency",
                "Canadian Forces Housing Agency" => "Canadian Forces Housing Agency",
                "Canadian General Standards Board" => "Canadian General Standards Board",
                "Canadian Grain Commission" => "Canadian Grain Commission",
                "Canadian Heritage" => "Canadian Heritage",
                "Canadian Heritage Information Network" => "Canadian Heritage Information Network",
                "Canadian Human Rights Commission" => "Canadian Human Rights Commission",
                "Canadian Institutes of Health Research" => "Canadian Institutes of Health Research",
                "Canadian Intellectual Property Office" => "Canadian Intellectual Property Office",
                "Canadian Intergovernmental Conference Secretariat" => "Canadian Intergovernmental Conference Secretariat",
                "Canadian International Trade Tribunal" => "Canadian International Trade Tribunal",
                "Canadian Judicial Council" => "Canadian Judicial Council",
                "Canadian Museum for Human Rights" => "Canadian Museum for Human Rights",
                "Canadian Museum of Contemporary Photography" => "Canadian Museum of Contemporary Photography",
                "Canadian Museum of History" => "Canadian Museum of History",
                "Canadian Museum of Immigration at Pier 21" => "Canadian Museum of Immigration at Pier 21",
                "Canadian Museum of Nature" => "Canadian Museum of Nature",
                "Canadian Northern Economic Development Agency" => "Canadian Northern Economic Development Agency",
                "Canadian Nuclear Safety Commission" => "Canadian Nuclear Safety Commission",
                "Canadian Pari-Mutuel Agency" => "Canadian Pari-Mutuel Agency",
                "Canadian Police College" => "Canadian Police College",
                "Canadian Race Relations Foundation" => "Canadian Race Relations Foundation",
                "Canadian Radio-Television and Telecommunications Commission" => "Canadian Radio-Television and Telecommunications Commission",
                "Canadian Security Intelligence Service" => "Canadian Security Intelligence Service",
                "Canadian Space Agency" => "Canadian Space Agency",
                "Canadian Tourism Commission" => "Canadian Tourism Commission",
                "Canadian Trade Commissioner Service" => "Canadian Trade Commissioner Service",
                "Canadian Transportation Agency" => "Canadian Transportation Agency",
                "Canadian War Museum" => "Canadian War Museum",
                "Chief Electoral Officer (Office of the)" => "Chief Electoral Officer (Office of the)",
                "Civilian Review and Complaints Commission for the RCMP" => "Civilian Review and Complaints Commission for the RCMP",
                "Clerk of the Privy Council" => "Clerk of the Privy Council",
                "Commissioner for Federal Judicial Affairs Canada (Office of the)" => "Commissioner for Federal Judicial Affairs Canada (Office of the)",
                "Commissioner of Lobbying of Canada (Office of the)" => "Commissioner of Lobbying of Canada (Office of the)",
                "Commissioner of Official Languages (Office of the)" => "Commissioner of Official Languages (Office of the)",
                "Communications Research Centre Canada" => "Communications Research Centre Canada",
                "Communications Security Establishment Canada" => "Communications Security Establishment Canada",
                "Communications Security Establishment Commissioner (Office of the)" => "Communications Security Establishment Commissioner (Office of the)",
                "Competition Bureau Canada" => "Competition Bureau Canada",
                "Competition Tribunal" => "Competition Tribunal",
                "Conflict of Interest and Ethics Commissioner (Office of the)" => "Conflict of Interest and Ethics Commissioner (Office of the)",
                "Copyright Board Canada" => "Copyright Board Canada",
                "CORCAN" => "CORCAN",
                "Correctional Investigator Canada" => "Correctional Investigator Canada",
                "Correctional Service Canada" => "Correctional Service Canada",
                "Courts Administration Service" => "Courts Administration Service",
                "Currency Museum" => "Currency Museum",
                "Defence Construction Canada" => "Defence Construction Canada",
                "Defence Research and Development Canada" => "Defence Research and Development Canada",
                "Democratic Institutions" => "Democratic Institutions",
                "Elections Canada" => "Elections Canada",
                "Employment and Social Development Canada" => "Employment and Social Development Canada",
                "Environment and Climate Change Canada" => "Environment and Climate Change Canada",
                "Environmental Protection Review Canada" => "Environmental Protection Review Canada",
                "Export Development Canada" => "Export Development Canada",
                "Farm Credit Canada" => "Farm Credit Canada",
                "Farm Products Council of Canada" => "Farm Products Council of Canada",
                "Federal Bridge Corporation" => "Federal Bridge Corporation",
                "Federal Court of Appeal" => "Federal Court of Appeal",
                "Federal Court of Canada" => "Federal Court of Canada",
                "Federal Economic Development Agency for Southern Ontario" => "Federal Economic Development Agency for Southern Ontario",
                "Federal Economic Development Initiative for Northern Ontario (FedNor)" => "Federal Economic Development Initiative for Northern Ontario (FedNor)",
                "Federal Ombudsman for Victims Of Crime (Office of the)" => "Federal Ombudsman for Victims Of Crime (Office of the)",
                "Finance Canada (Department of)" => "Finance Canada (Department of)",
                "Financial Consumer Agency of Canada" => "Financial Consumer Agency of Canada",
                "Financial Transactions and Reports Analysis Centre of Canada" => "Financial Transactions and Reports Analysis Centre of Canada",
                "Fisheries and Oceans Canada" => "Fisheries and Oceans Canada",
                "Freshwater Fish Marketing Corporation" => "Freshwater Fish Marketing Corporation",
                "Geographical Names Board of Canada" => "Geographical Names Board of Canada",
                "Geomatics Canada" => "Geomatics Canada",
                "Global Affairs Canada" => "Global Affairs Canada",
                "Governor General of Canada" => "Governor General of Canada",
                "Great Lakes Pilotage Authority Canada" => "Great Lakes Pilotage Authority Canada",
                "Health Canada" => "Health Canada",
                "Historic Sites and Monuments Board of Canada" => "Historic Sites and Monuments Board of Canada",
                "Human Rights Tribunal of Canada" => "Human Rights Tribunal of Canada",
                "Immigration and Refugee Board of Canada" => "Immigration and Refugee Board of Canada",
                "Immigration, Refugees and Citizenship Canada" => "Immigration, Refugees and Citizenship Canada",
                "Indian Oil and Gas Canada" => "Indian Oil and Gas Canada",
                "Indian Residential Schools Truth and Reconciliation Commission" => "Indian Residential Schools Truth and Reconciliation Commission",
                "Indigenous and Northern Affairs Canada" => "Indigenous and Northern Affairs Canada",
                "Industrial Technologies Office" => "Industrial Technologies Office",
                "Information Commissioner (Office of the)" => "Information Commissioner (Office of the)",
                "Infrastructure Canada" => "Infrastructure Canada",
                "Innovation, Science and Economic Development Canada" => "Innovation, Science and Economic Development Canada",
                "Intergovernmental Affairs (Department of)" => "Intergovernmental Affairs (Department of)",
                "International Development Research Centre" => "International Development Research Centre",
                "Jacques Cartier and Champlain Bridges Inc." => "Jacques Cartier and Champlain Bridges Inc.",
                "Justice Canada (Department of)" => "Justice Canada (Department of)",
                "Labour Program" => "Labour Program",
                "Laurentian Pilotage Authority Canada" => "Laurentian Pilotage Authority Canada",
                "Leader of the Government in the House of Commons" => "Leader of the Government in the House of Commons",
                "Library and Archives Canada" => "Library and Archives Canada",
                "Marine Atlantic" => "Marine Atlantic",
                "Measurement Canada" => "Measurement Canada",
                "Military Grievances External Review Committee" => "Military Grievances External Review Committee",
                "Military Police Complaints Commission of Canada" => "Military Police Complaints Commission of Canada",
                "National Arts Centre" => "National Arts Centre",
                "National Battlefields Commission" => "National Battlefields Commission",
                "National Capital Commission" => "National Capital Commission",
                "National Defence" => "National Defence",
                "National Defence and the Canadian Forces Ombudsperson (Office of the)" => "National Defence and the Canadian Forces Ombudsperson (Office of the)",
                "National Energy Board" => "National Energy Board",
                "National Film Board" => "National Film Board",
                "National Gallery of Canada" => "National Gallery of Canada",
                "National Museum of Science and Technology" => "National Museum of Science and Technology",
                "National Research Council Canada" => "National Research Council Canada",
                "National Search and Rescue Secretariat" => "National Search and Rescue Secretariat",
                "National Seniors Council" => "National Seniors Council",
                "Natural Resources Canada" => "Natural Resources Canada",
                "Natural Sciences and Engineering Research Canada" => "Natural Sciences and Engineering Research Canada",
                "Northern Pipeline Agency Canada" => "Northern Pipeline Agency Canada",
                "Occupational Health and Safety Tribunal Canada" => "Occupational Health and Safety Tribunal Canada",
                "Pacific Pilotage Authority Canada" => "Pacific Pilotage Authority Canada",
                "Parks Canada" => "Parks Canada",
                "Parliament of Canada" => "Parliament of Canada",
                "Parole Board of Canada" => "Parole Board of Canada",
                "Passport Canada" => "Passport Canada",
                "Patented Medicine Prices Review Board Canada" => "Patented Medicine Prices Review Board Canada",
                "Polar Knowledge Canada" => "Polar Knowledge Canada",
                "PPP Canada" => "PPP Canada",
                "Prime Minister of Canada" => "Prime Minister of Canada",
                "Privacy Commissioner (Office of the)" => "Privacy Commissioner (Office of the)",
                "Privy Council Office" => "Privy Council Office",
                "Procurement Ombudsman (Office of the)" => "Procurement Ombudsman (Office of the)",
                "Public Health Agency of Canada" => "Public Health Agency of Canada",
                "Public Prosecution Service of Canada" => "Public Prosecution Service of Canada",
                "Public Safety Canada" => "Public Safety Canada",
                "Public Sector Integrity Commissioner of Canada (Office of the)" => "Public Sector Integrity Commissioner of Canada (Office of the)",
                "Public Sector Pension Investment Board" => "Public Sector Pension Investment Board",
                "Public Servants Disclosure Protection Tribunal Canada" => "Public Servants Disclosure Protection Tribunal Canada",
                "Public Service Commission of Canada" => "Public Service Commission of Canada",
                "Public Service Labour Relations and Employment Board" => "Public Service Labour Relations and Employment Board",
                "Public Services and Procurement Canada" => "Public Services and Procurement Canada",
                "Receiver General for Canada" => "Receiver General for Canada",
                "Registry of the Specific Claims Tribunal of Canada" => "Registry of the Specific Claims Tribunal of Canada",
                "Ridley Terminals Inc." => "Ridley Terminals Inc.",
                "Royal Canadian Air Force" => "Royal Canadian Air Force",
                "Royal Canadian Mint" => "Royal Canadian Mint",
                "Royal Canadian Mounted Police" => "Royal Canadian Mounted Police",
                "Royal Canadian Mounted Police External Review Committee" => "Royal Canadian Mounted Police External Review Committee",
                "Royal Canadian Navy" => "Royal Canadian Navy",
                "Royal Military College of Canada" => "Royal Military College of Canada",
                "Secretary to the Governor General (Office of the)" => "Secretary to the Governor General (Office of the)",
                "Security Intelligence Review Committee" => "Security Intelligence Review Committee",
                "Seniors" => "Seniors",
                "Service Canada" => "Service Canada",
                "Shared Services Canada" => "Shared Services Canada",
                "Ship-Source Oil Pollution Fund" => "Ship-Source Oil Pollution Fund",
                "Social Sciences and Humanities Research Council of Canada" => "Social Sciences and Humanities Research Council of Canada",
                "Social Security Tribunal of Canada" => "Social Security Tribunal of Canada",
                "Sport Canada" => "Sport Canada",
                "Standards Council of Canada" => "Standards Council of Canada",
                "Statistics Canada" => "Statistics Canada",
                "Status of Women Canada" => "Status of Women Canada",
                "Superintendent of Bankruptcy Canada (Office of the)" => "Superintendent of Bankruptcy Canada (Office of the)",
                "Superintendent of Financial Institutions Canada (Office of the)" => "Superintendent of Financial Institutions Canada (Office of the)",
                "Supreme Court of Canada" => "Supreme Court of Canada",
                "Tax Court of Canada" => "Tax Court of Canada",
                "Taxpayers' Ombudsman (Office of the)" => "Taxpayers' Ombudsman (Office of the)",
                "Telefilm Canada" => "Telefilm Canada",
                "Translation Bureau" => "Translation Bureau",
                "Transport Canada" => "Transport Canada",
                "Transportation Appeal Tribunal of Canada" => "Transportation Appeal Tribunal of Canada",
                "Transportation Safety Board of Canada" => "Transportation Safety Board of Canada",
                "Treasury Board of Canada Secretariat" => "Treasury Board of Canada Secretariat",
                "Veterans Affairs Canada" => "Veterans Affairs Canada",
                "Veterans' Ombudsman (Office of the)" => "Veterans' Ombudsman (Office of the)",
                "Veterans Review and Appeal Board Canada" => "Veterans Review and Appeal Board Canada",
                "VIA Rail Canada Inc." => "VIA Rail Canada Inc.",
                "Virtual Museum of Canada" => "Virtual Museum of Canada",
                "Western Economic Diversification Canada" => "Western Economic Diversification Canada");
            } else {
                $federal_departments = array("Canadian Air Transport Security Authority" => "Administration canadienne de la sûreté du transport aérien",
                "Atlantic Pilotage Authority Canada" => "Administration de pilotage de l'Atlantique Canada",
                "Great Lakes Pilotage Authority Canada" => "Administration de pilotage des Grands Lacs Canada",
                "Laurentian Pilotage Authority Canada" => "Administration de pilotage des Laurentides Canada",
                "Pacific Pilotage Authority Canada" => "Administration de pilotage du Pacifique Canada",
                "Northern Pipeline Agency Canada" => "Administration du pipe-line du Nord Canada",
                "Indigenous and Northern Affairs Canada" => "Affaires autochtones et du Nord Canada",
                "Intergovernmental Affairs (Department of)" => "Affaires intergouvernementales",
                "Global Affairs Canada" => "Affaires mondiales Canada",
                "Canadian Northern Economic Development Agency" => "Agence canadienne de développement économique du Nord",
                "Canadian Environmental Assessment Agency" => "Agence canadienne d'évaluation environnementale",
                "Canadian Food Inspection Agency" => "Agence canadienne d'inspection des aliments",
                "Canadian Pari-Mutuel Agency" => "Agence canadienne du pari mutuel",
                "Financial Consumer Agency of Canada" => "Agence de la consommation en matière financière du Canada",
                "Public Health Agency of Canada" => "Agence de la santé publique du Canada",
                "Canadian Forces Housing Agency" => "Agence de logement des Forces canadiennes",
                "Atlantic Canada Opportunities Agency" => "Agence de promotion économique du Canada atlantique",
                "Canada Border Services Agency" => "Agence des services frontaliers du Canada",
                "Canada Revenue Agency" => "Agence du revenu du Canada",
                "Federal Economic Development Agency for Southern Ontario" => "Agence fédérale de développement économique pour le Sud de l'Ontario",
                "Canadian Space Agency" => "Agence spatiale canadienne",
                "Agriculture and Agri-Food Canada" => "Agriculture et Agroalimentaire Canada",
                "Seniors" => "Aînés",
                "Veterans Affairs Canada" => "Anciens Combattants Canada",
                "Canadian Army" => "Armée canadienne",
                "Royal Canadian Air Force" => "Aviation royale canadienne",
                "Business Development Bank of Canada" => "Banque de développement du Canada",
                "Bank of Canada" => "Banque du Canada",
                "Library and Archives Canada" => "Bibliothèque et Archives Canada",
                "Competition Bureau Canada" => "Bureau de la concurrence Canada",
                "Transportation Safety Board of Canada" => "Bureau de la sécurité des transports du Canada",
                "Translation Bureau" => "Bureau de la traduction",
                "Procurement Ombudsman (Office of the)" => "Bureau de l'ombudsman de l'approvisionnement",
                "Taxpayers' Ombudsman (Office of the)" => "Bureau de l'ombudsman des contribuables",
                "Federal Ombudsman for Victims Of Crime (Office of the)" => "Bureau de l'ombudsman fédéral des victimes d'actes criminels",
                "Communications Security Establishment Commissioner (Office of the)" => "Bureau du commissaire du Centre de la sécurité des télécommunications",
                "Privy Council Office" => "Bureau du Conseil privé",
                "Chief Electoral Officer (Office of the)" => "Bureau du directeur général des élections",
                "Secretary to the Governor General (Office of the)" => "Bureau du secrétaire du gouverneur général",
                "Superintendent of Bankruptcy Canada (Office of the)" => "Bureau du surintendant des faillites Canada",
                "Superintendent of Financial Institutions Canada (Office of the)" => "Bureau du surintendant des institutions financières Canada",
                "Auditor General of Canada (Office of the)" => "Bureau du vérificateur général du Canada",
                "Ship-Source Oil Pollution Fund" => "Caisse d'indemnisation des dommages dus à la pollution par les hydrocarbures causée par les navires",
                "Canada Centre for Inland Waters" => "Centre canadien des eaux intérieures",
                "Canadian Centre for Occupational Health and Safety" => "Centre canadien d'hygiène et de sécurité au travail",
                "Financial Transactions and Reports Analysis Centre of Canada" => "Centre d'analyse des opérations et déclarations financières du Canada",
                "Communications Security Establishment Canada" => "Centre de la sécurité des télécommunications Canada",
                "International Development Research Centre" => "Centre de recherches pour le développement international",
                "Communications Research Centre Canada" => "Centre de recherches sur les communications Canada",
                "Canada Firearms Centre" => "Centre des armes à feu Canada",
                "National Arts Centre" => "Centre national des arts",
                "Canada Research Chairs" => "Chaires de recherche du Canada",
                "Canadian Police College" => "Collège canadien de police",
                "Royal Military College of Canada" => "Collège militaire royal du Canada",
                "Security Intelligence Review Committee" => "Comité de surveillance des activités de renseignement de sécurité",
                "Royal Canadian Mounted Police External Review Committee" => "Comité externe d'examen de la Gendarmerie royale du Canada",
                "Military Grievances External Review Committee" => "Comité externe d'examen des griefs militaires",
                "Commissioner for Federal Judicial Affairs Canada (Office of the)" => "Commissariat à la magistrature fédérale Canada",
                "Privacy Commissioner (Office of the)" => "Commissariat à la protection de la vie privée au Canada",
                "Information Commissioner (Office of the)" => "Commissariat à l'information au Canada",
                "Public Sector Integrity Commissioner of Canada (Office of the)" => "Commissariat à l'intégrité du secteur public du Canada",
                "Commissioner of Lobbying of Canada (Office of the)" => "Commissariat au lobbying du Canada",
                "Conflict of Interest and Ethics Commissioner (Office of the)" => "Commissariat aux conflits d'intérêts et à l'éthique",
                "Commissioner of Official Languages (Office of the)" => "Commissariat aux langues officielles",
                "Canadian Nuclear Safety Commission" => "Commission canadienne de sûreté nucléaire",
                "Canadian Human Rights Commission" => "Commission canadienne des droits de la personne",
                "Canadian Grain Commission" => "Commission canadienne des grains",
                "Canadian Cultural Property Export Review Board" => "Commission canadienne d'examen des exportations de biens culturels",
                "Canadian Dairy Commission" => "Commission canadienne du lait",
                "Canadian Tourism Commission" => "Commission canadienne du tourisme",
                "Civilian Review and Complaints Commission for the RCMP" => "Commission civile d'examen et de traitement des plaintes relatives à la GRC",
                "National Capital Commission" => "Commission de la capitale nationale",
                "Public Service Commission of Canada" => "Commission de la fonction publique du Canada",
                "Canada Employment Insurance Commission" => "Commission de l'assurance-emploi du Canada",
                "Immigration and Refugee Board of Canada" => "Commission de l'immigration et du statut de réfugié du Canada",
                "Canada Agricultural Review Tribunal" => "Commission de révision agricole du Canada",
                "Geographical Names Board of Canada" => "Commission de toponymie du Canada",
                "Indian Residential Schools Truth and Reconciliation Commission" => "Commission de vérité et de réconciliation relative aux pensionnats indiens",
                "National Battlefields Commission" => "Commission des champs de bataille nationaux",
                "Parole Board of Canada" => "Commission des libérations conditionnelles du Canada",
                "Historic Sites and Monuments Board of Canada" => "Commission des lieux et monuments historiques du Canada",
                "Public Service Labour Relations and Employment Board" => "Commission des relations de travail et de l'emploi dans la fonction publique",
                "Military Police Complaints Commission of Canada" => "Commission d'examen des plaintes concernant la police militaire du Canada",
                "Copyright Board Canada" => "Commission du droit d'auteur Canada",
                "Status of Women Canada" => "Condition féminine Canada",
                "Canadian Judicial Council" => "Conseil canadien de la magistrature",
                "Standards Council of Canada" => "Conseil canadien des normes",
                "Canada Industrial Relations Board" => "Conseil canadien des relations industrielles",
                "Canadian Radio-Television and Telecommunications Commission" => "Conseil de la radiodiffusion et des télécommunications canadiennes",
                "Natural Sciences and Engineering Research Canada" => "Conseil de recherches en sciences et en génie Canada",
                "Social Sciences and Humanities Research Council of Canada" => "Conseil de recherches en sciences humaines du Canada",
                "Canada Council for the Arts" => "Conseil des arts du Canada",
                "Farm Products Council of Canada" => "Conseil des produits agricoles du Canada",
                "Patented Medicine Prices Review Board Canada" => "Conseil d'examen du prix des médicaments brevetés Canada",
                "National Research Council Canada" => "Conseil national de recherches Canada",
                "National Seniors Council" => "Conseil national des aînés",
                "Defence Construction Canada" => "Construction de Défense Canada",
                "CORCAN" => "CORCAN",
                "Canadian Commercial Corporation" => "Corporation commerciale canadienne",
                "Canada Development Investment Corporation" => "Corporation de développement des investissements du Canada",
                "Tax Court of Canada" => "Cour canadienne de l'impôt",
                "Federal Court of Appeal" => "Cour d'appel fédérale",
                "Federal Court of Canada" => "Cour fédérale",
                "Supreme Court of Canada" => "Cour suprême du Canada",
                "National Defence" => "Défense nationale",
                "Canada Economic Development for Quebec Regions" => "Développement économique Canada pour les régions du Québec",
                "Western Economic Diversification Canada" => "Diversification de l'économie de l'Ouest Canada",
                "Canada School of Public Service" => "École de la fonction publique du Canada",
                "Elections Canada" => "Élections Canada",
                "Employment and Social Development Canada" => "Emploi et Développement social Canada",
                "Atomic Energy of Canada Limited" => "Énergie atomique du Canada, Limitée",
                "Correctional Investigator Canada" => "Enquêteur correctionnel Canada",
                "Aboriginal Business Canada" => "Entreprise autochtone Canada",
                "Environment and Climate Change Canada" => "Environnement et Changement climatique Canada",
                "Export Development Canada" => "Exportation et développement Canada",
                "Farm Credit Canada" => "Financement agricole Canada",
                "Finance Canada (Department of)" => "Finances Canada, Ministère des",
                "Canadian Race Relations Foundation" => "Fondation canadienne des relations raciales",
                "Canadian Coast Guard" => "Garde côtière canadienne",
                "Canada Gazette" => "Gazette du Canada",
                "Royal Canadian Mounted Police" => "Gendarmerie royale du Canada",
                "Geomatics Canada" => "Géomatique Canada",
                "Governor General of Canada" => "Gouverneur général du Canada",
                "Registry of the Specific Claims Tribunal of Canada" => "Greffe du Tribunal des revendications particulières du Canada",
                "Clerk of the Privy Council" => "Greffier du Conseil privé",
                "Immigration, Refugees and Citizenship Canada" => "Immigration, Réfugiés, et Citoyenneté Canada",
                "Infrastructure Canada" => "Infrastructure Canada",
                "Federal Economic Development Initiative for Northern Ontario (FedNor)" => "Initiative fédérale de développement économique pour le Nord de l'Ontario (FedNor)",
                "Innovation, Science and Economic Development Canada" => "Innovation, Sciences et Développement économique Canada",
                "Canadian Conservation Institute" => "Institut canadien de conservation",
                "Democratic Institutions" => "Institutions démocratiques",
                "Canadian Institutes of Health Research" => "Instituts de recherche en santé du Canada",
                "Public Sector Pension Investment Board" => "Investissement des régimes de pensions du secteur public",
                "Justice Canada (Department of)" => "Justice Canada, Ministère de la",
                "Leader of the Government in the House of Commons" => "Leader du gouvernement à la Chambre des communes",
                "Marine Atlantic" => "Marine Atlantique",
                "Royal Canadian Navy" => "Marine royale canadienne",
                "Measurement Canada" => "Mesures Canada",
                "Royal Canadian Mint" => "Monnaie royale canadienne",
                "Canadian War Museum" => "Musée canadien de la guerre",
                "Canadian Museum of Nature" => "Musée canadien de la nature",
                "Canadian Museum of Contemporary Photography" => "Musée canadien de la photographie contemporaine",
                "Canadian Museum of History" => "Musée canadien de l'histoire",
                "Canadian Museum of Immigration at Pier 21" => "Musée canadien de l'immigration du Quai 21",
                "Canadian Museum for Human Rights" => "Musée canadien pour les droits de la personne",
                "Currency Museum" => "Musée de la Banque du Canada",
                "Canada Agriculture and Food Museum" => "Musée de l'agriculture et de l'alimentation du Canada",
                "Canada Aviation and Space Museum" => "Musée de l'aviation et de l'espace du Canada",
                "National Gallery of Canada" => "Musée des beaux-arts du Canada",
                "Virtual Museum of Canada" => "Musée virtuel du Canada",
                "National Museum of Science and Technology" => "Musées des sciences et de la technologie du Canada",
                "Freshwater Fish Marketing Corporation" => "Office de commercialisation du poisson d'eau douce",
                "Canadian Intellectual Property Office" => "Office de la propriété intellectuelle du Canada",
                "Canadian General Standards Board" => "Office des normes générales du Canada",
                "Industrial Technologies Office" => "Office des technologies industrielles",
                "Canadian Transportation Agency" => "Office des transports du Canada",
                "Canada Pension Plan Investment Board" => "Office d'investissement du régime de pensions du Canada",
                "National Energy Board" => "Office national de l'énergie",
                "National Film Board" => "Office national du film",
                "National Defence and the Canadian Forces Ombudsperson (Office of the)" => "Ombudsman de la Défense nationale et des Forces canadiennes",
                "Veterans' Ombudsman (Office of the)" => "Ombudsman des vétérans",
                "Canadian Cadet Organizations" => "Organisations de cadets du Canada",
                "Parks Canada" => "Parcs Canada",
                "Parliament of Canada" => "Parlement du Canada",
                "Passport Canada" => "Passeport Canada",
                "Canadian Heritage" => "Patrimoine canadien",
                "Fisheries and Oceans Canada" => "Pêches et Océans Canada",
                "Indian Oil and Gas Canada" => "Pétrole et gaz des Indiens du Canada",
                "Blue Water Bridge Canada" => "Pont Blue Water Canada",
                "Jacques Cartier and Champlain Bridges Inc." => "Ponts Jacques-Cartier et Champlain Inc.",
                "Canada Post" => "Postes Canada",
                "PPP Canada" => "PPP Canada",
                "Prime Minister of Canada" => "Premier ministre du Canada",
                "Labour Program" => "Programme du travail",
                "Receiver General for Canada" => "Receveur général du Canada",
                "Defence Research and Development Canada" => "Recherche et développement pour la Défense Canada",
                "Canadian Heritage Information Network" => "Réseau canadien d'information sur le patrimoine",
                "Natural Resources Canada" => "Ressources naturelles Canada",
                "Environmental Protection Review Canada" => "Révision de la protection de l'environnement Canada",
                "Ridley Terminals Inc." => "Ridley Terminals Inc.",
                "Health Canada" => "Santé Canada",
                "Polar Knowledge Canada" => "Savoir polaire Canada",
                "Canadian Intergovernmental Conference Secretariat" => "Secrétariat des conférences intergouvernementales canadiennes",
                "Treasury Board of Canada Secretariat" => "Secrétariat du Conseil du Trésor du Canada",
                "National Search and Rescue Secretariat" => "Secrétariat national recherche et sauvetage",
                "Public Safety Canada" => "Sécurité publique Canada",
                "Courts Administration Service" => "Service administratif des tribunaux judiciaires",
                "Service Canada" => "Service Canada",
                "Administrative Tribunals Support Service of Canada" => "Service canadien d'appui aux tribunaux administratifs",
                "Canadian Security Intelligence Service" => "Service canadien du renseignement de sécurité",
                "Correctional Service Canada" => "Service correctionnel Canada",
                "Canadian Trade Commissioner Service" => "Service des délégués commerciaux du Canada",
                "Public Prosecution Service of Canada" => "Service des poursuites pénales du Canada",
                "Shared Services Canada" => "Services partagés Canada",
                "Public Services and Procurement Canada" => "Services publics et Approvisionnement Canada",
                "Canada Mortgage and Housing Corporation" => "Société canadienne d'hypothèques et de logement",
                "Canada Deposit Insurance Corporation" => "Société d'assurance-dépôts du Canada",
                "Canada Science and Technology Museum Corporation" => "Société des musées de sciences et technologies du Canada",
                "Federal Bridge Corporation" => "Société des ponts fédéraux",
                "Canada Lands Company Limited" => "Société immobilière du Canada Limitée",
                "Sport Canada" => "Sport Canada",
                "Statistics Canada" => "Statistique Canada",
                "Telefilm Canada" => "Téléfilm Canada",
                "Transport Canada" => "Transports Canada",
                "Canadian International Trade Tribunal" => "Tribunal canadien du commerce extérieur",
                "Transportation Appeal Tribunal of Canada" => "Tribunal d'appel des transports du Canada",
                "Competition Tribunal" => "Tribunal de la concurrence",
                "Public Servants Disclosure Protection Tribunal Canada" => "Tribunal de la protection des fonctionnaires divulgateurs Canada",
                "Social Security Tribunal of Canada" => "Tribunal de la sécurité sociale du Canada",
                "Occupational Health and Safety Tribunal Canada" => "Tribunal de santé et sécurité au travail Canada",
                "Veterans Review and Appeal Board Canada" => "Tribunal des anciens combattants (révision et appel) Canada",
                "Human Rights Tribunal of Canada" => "Tribunal des droits de la personne du Canada",
                "VIA Rail Canada Inc." => "VIA Rail Canada Inc.");
            }

            //popular members in department
            $public_servant_count = elgg_get_entities_from_metadata(array(
              'type' => 'user',
              'limit' => 50,
              'count' => true,
              'metadata_name'  => 'department',
              'metadata_values'  => array($depart1, $depart2),
            ));

            if($public_servant_count > 10){
              $offset = rand(0, $public_servant_count - 6);
            } else {
              $offset = 0;
            }

            //popular members in department
            $public_servant = elgg_get_entities_from_metadata(array(
              'type' => 'user',
              'limit' => 50,
              'offset' => $offset,
              'metadata_name'  => 'department',
              'metadata_values'  => array($depart1, $depart2),
            ));

            //set guids as key for each array items
            foreach($public_servant as $f => $l){
              $public_servant['"'.$l->guid.'"'] = $l;
              unset($public_servant[$f]);
               if($user->guid == $l->guid || check_entity_relationship($user->guid, 'friend', $l->guid)){
                 unset($public_servant['"'.$l->guid.'"']);
               }
             }

            if(count($public_servant) < 6){
              //retrieve users based on similiar skills
              $match = user_skill_match();

              if($match){
                foreach($match as $k => $v){
                  $match['"'.$v->guid.'"'] = $v;
                  unset($match[$k]);

                  if($user->guid == $v->guid || check_entity_relationship($user->guid, 'friend', $v->guid)){
                    unset($match['"'.$v->guid.'"']);
                  }
                }

                $status = $_SESSION['candidate_search_feedback'];

                //combine students with academics
                $public_servant = array_merge($public_servant, $match);
              }
            }

            $public_servant = array_splice($public_servant, 0, 6);

            shuffle($public_servant);

            //if the search does not find anyone, grb 6 random ambassadors for the user
            if(count($public_servant) == 0){
              echo elgg_echo('onboard:welcome:two:noresults');
            }

            foreach($public_servant as $f => $l){

                    $htmloutput = '';
                    $site_url = elgg_get_site_url();
                    $userGUID=$l->guid;
                    $job=$l->job;

                    $htmloutput=$htmloutput.'<div style="height:200px; margin-top:25px;" class="col-xs-4 text-center hght-inhrt  onboard-coll">'; // suggested friend link to profile

                    //EW - change to render icon so new ambassador badges can be shown
                    $htmloutput.= elgg_view_entity_icon($l, 'medium', array('use_hover' => false, 'use_link' => false, 'class' => 'elgg-avatar-wet4-sf'));

                    $htmloutput=$htmloutput.'<h4 class="h4 mrgn-tp-sm mrgn-bttm-sm"><span class="text-primary">'.$l->getDisplayName().'</span></h4>';
                    if($l->department == $user->department){ // Nick - Adding department if no job, if none add a space
                        $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$l->department.'</p>';
                    }else{
                      if($institution){
                        $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$institution.'</p>';
                      }
                      $htmloutput=$htmloutput.'<p class="small mrgn-tp-0 job-length">'.$status[$l->guid].'</p>';
                    }

                    //changed connect button to send a friend request we should change the wording
                    $htmloutput=$htmloutput.'<a href="#" class="add-friend btn btn-primary mrgn-tp-sm" onclick="addFriendOnboard('.$userGUID.')" id="'.$userGUID.'">'.elgg_echo('friend:add').'</a>';
                    $htmloutput=$htmloutput.'</div>';

                    echo $htmloutput . '';

            }
        }
            ?>
    </div>

    <div class="mrgn-bttm-md mrgn-tp-lg pull-right">
      <a id="next" class="btn btn-primary" href="#">
          <?php echo elgg_echo('onboard:welcome:next'); ?>
      </a>

    </div>

    <script>



        function addFriendOnboard(guid) {
            var button = $('#' + guid);
            //check if button has id
            if (button.attr('id') != '') {

                //change to loading spinner
                button.html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i><span class="sr-only">Loading...</span>').removeClass('btn-primary add-friend').addClass('btn-default');
                var id = $(this).attr('id');

                //do the elgg friend request action
                elgg.action('friends/add', {
                    data: {
                        friend: guid,
                    },
                    success: function (wrapper) {
                        if (wrapper.output) {
                            //alert(wrapper.output.sum);
                        } else {
                            // the system prevented the action from running
                        }

                        //show that the request was sent
                        button.html("<?php echo elgg_echo('friend_request:friend:add:pending'); ?>");
                        //remove id to disabe sending request again
                        button.attr('id', '');
                    }
                });
            }
        }


    //skip to next step
    $('#next').on('click', function () {
      $(this).html('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i><span class="sr-only">Loading...</span>');
        elgg.get('ajax/view/welcome-steps/stepThree', {
            success: function (output) {

                $('#welcome-step').html(output);

            }
        });
    });

    </script>
    <style>
        .min-height-cs {
            min-height: 20px;
        }

        .job-length {
            white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
        }

        .onboard-coll {

            max-width:285px;

        }
    </style>


</div>
