<?php

if(!defined("PHORUM")) return;

//set a timestamp for the latest event in a calendar
function phorum_mod_google_calendar_set_latest_event($cal_id) {
    
    global $PHORUM;
    //save the timestamp of the last event created/updated/deleted etc.
    $PHORUM["phorum_mod_google_calendar_cache"]["latest_event_ts"][$cal_id] = time();
    phorum_db_update_settings(array("phorum_mod_google_calendar_cache"=>$PHORUM["phorum_mod_google_calendar_cache"]));
    
    return;
    
}

?>
