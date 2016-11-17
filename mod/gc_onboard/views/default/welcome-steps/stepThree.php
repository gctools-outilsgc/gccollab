<?php
/*
 * stepThree.php - Welcome
 *
 * Third step of welcome module. Gives user option to tour welcome to gcconnex group.
 */

//Nick - Change guid based on the group (1171 is my test group).
//Production Welcome Group = 19980634
//Pre Prod Welcome Group = 17265559
$welcomeGroup_guid = elgg_get_plugin_setting("tour_group", "gc_onboard");

if(!$welcomeGroup_guid){
    $welcomeGroup_guid = 19980634;
}

$group_entity = get_entity($welcomeGroup_guid);
?>
<div class="panel-heading clearfix">
    <h2 class="pull-left">
        <?php echo elgg_echo('onboard:welcome:three:title'); ?>
    </h2>
    <div class="pull-right">
        <?php echo elgg_view('page/elements/step_counter', array('current_step'=>4, 'total_steps'=>5));?>

    </div>
</div>
<div class="panel-body">
      <div class="col-sm-6">

        <?php
        echo elgg_view('widgets/featured_groups', array(
          'limit'=>1,
        ));
        ?>
      </div>
      <div class="col-sm-6">
        <?php
        echo elgg_view('widgets/featured_groups', array(
          'limit'=>1,
        ));
        ?>
      </div>
    <div class="mrgn-bttm-md mrgn-tp-md pull-right">
        <a id="skip" class="mrgn-lft-sm btn btn-default" href="#">
            <?php echo elgg_echo('onboard:welcome:one:skip'); ?>
        </a>
        <?php
/*
        echo elgg_view('output/url',array(
            'text'=>elgg_echo('onboard:welcome:three:tour'),
            'href'=>elgg_get_site_url().'groups/profile/'.$welcomeGroup_guid .'?first_tour=true',
            'class'=>'btn btn-primary',
        ));*/

        ?>

    </div>

    <script>



    //skip to next step
    $('#skip').on('click', function () {
        elgg.get('ajax/view/welcome-steps/stepFour', {
            success: function (output) {

                $('#welcome-step').html(output);

            }
        });
    });

    </script>



</div>
