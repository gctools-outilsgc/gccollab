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

        $userType = $user->user_type;
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

                  if($user->guid == $l->guid || check_entity_relationship($user->guid, 'friend', $l->guid)){
                    unset($match['"'.$l->guid.'"']);
                  }
                }

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
              echo '<b>Sorry we were unable to find any colleage recomendations, fill out your profile more!</b>';
            }

            //output the student
            foreach($students as $f => $l){
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
            echo '<b>Sorry we were unable to find any colleage recomendations, fill out your profile more!</b>';
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

            //popular members in department
            $public_servant_count = elgg_get_entities_from_metadata(array(
              'type' => 'user',
              'limit' => 50,
              'count' => true,
              'metadata_name'  => 'department',
              'metadata_values'  => array($depart1, $depart2),
            ));

            if($public_servant_count > 10){
              $offset = rand(0, $public_servant_count - 10);
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

            //shuffle($public_servant);

            //if the search does not find anyone, grb 6 random ambassadors for the user
            if(count($public_servant) == 0){
              //echo '<b>Sorry we were unable to find any colleage recomendations, fill out your profile more!</b>';
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

        <?php
        echo elgg_view('input/submit', array(
                'value' => elgg_echo('onboard:welcome:next'),
                'id' => 'next',
            ));


        ?>

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
        elgg.get('ajax/view/welcome-steps/stepThree', {
            success: function (output) {
               // var oldHeight = $('#welcome-step').css('height');
                $('#welcome-step').html(output);
               // var newHeight = $('#welcome-step').children().css('height');
                //console.log('new:' + newHeight + ' old:' + oldHeight);
                //animateStep(oldHeight, newHeight);
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
