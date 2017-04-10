<?php
/*
 * Profile Nudge start.php
 */

elgg_register_event_handler('init', 'system', 'gc_profile_nudge_init');

function gc_profile_nudge_init(){
    elgg_register_ajax_view('gc_profile_nudge/profile_nudge');

    if( elgg_is_logged_in() ){

    	$reminder_time = elgg_get_plugin_setting('reminder_time', 'gc_profile_nudge');
    	$in_days = $reminder_time * 86400; // 1 day = 86400 seconds

    	$last_profile_nudge = elgg_get_logged_in_user_entity()->last_profile_nudge;
	    $last_seen = ( isset($last_profile_nudge) ) ? $last_profile_nudge : 0;

	    if( (time() - $last_seen) > $in_days ){
	    	elgg_extend_view('page/elements/foot', 'gc_profile_nudge/include');
	    }
	}
}
