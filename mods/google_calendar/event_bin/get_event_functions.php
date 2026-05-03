<?php

if(!defined("PHORUM")) return;

//get events for the current week
function phorum_mod_google_calendar_get_weekly_events($cal_id, $base_url, $start_min, $start_max) {

    global $PHORUM;
    
    //get the time zone offset
    if (!empty($PHORUM["user_time_zone"]) && $PHORUM["user"]["tz_offset"] != -99) {
        //use the user's time zone offset if allowed/selected
        $tz_offset = $PHORUM["user"]["tz_offset"];
    } else {
        //otherwise use the server time zone offset
        $tz_offset = $PHORUM["tz_offset"];
    }
    
    //check if we have cached data which is not out of date
    if (!empty($PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"][$cal_id][$tz_offset]["timestamp"])
        && !empty($PHORUM["phorum_mod_google_calendar_cache"]["latest_event_ts"][$cal_id])
        && $PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"][$cal_id][$tz_offset]["timestamp"] 
            > $PHORUM["phorum_mod_google_calendar_cache"]["latest_event_ts"][$cal_id]
        && $PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"][$cal_id][$tz_offset]["start_min_timestamp"]
            == $start_min) {
        //if we do, just send the cache
        return $PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"][$cal_id][$tz_offset]["cache_data"];
    }
    
    $google_url = $base_url."?start-min=$start_min&start-max=$start_max&max-results=1000";
    
    $google_response = phorum_mod_google_calendar_gdata_get_events($google_url);
    
    // most likely google will respond with a redirect and a session id, so resend with the session id
    if ($gsession_pos = strpos($google_response,"gsessionid=")) {
        preg_match("/(?:gsessionid=)([^\"]+)/i",$google_response,$matches);
        $gsession_id = $matches[1];
        $google_url .= "&gsessionid=".$gsession_id;
        $google_response = phorum_mod_google_calendar_gdata_get_events($google_url);
    }

    // grab the event ids
    preg_match_all("/(?:<id>)([^<]+)/i",$google_response,$matches);
        //if there are no ids, something has gone horribly wrong
        if (empty($matches)) {
        $event_data["error"] = htmlspecialchars($google_response);
        //log the error if enabled
        if (function_exists('event_logging_writelog')) {
            $calendar_type = $PHORUM["phorum_mod_google_calendar"]["calendar_type"];
            $log_message = "Error retrieving weekly events for the ".$PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$cal_id]["name"]." ";
            if ($calendar_type == "forums") {
                $log_message .= "forum.";
            } elseif ($calendar_type == "folders") {
                $log_message .= "folder.";
            } elseif ($calendar_type == "categories") {
                $log_message .= "category.";
            }
            event_logging_writelog(array(
                "message"	=> $log_message,
                "details"   => "Google returned this error: ".htmlspecialchars($google_response),
            ));
        }
        
        return $event_data;
    }
    
    // grab the forum id to reset later
    $curr_forum_id = $PHORUM["forum_id"];
    
    //get the message data for each event's message
    foreach ($matches[1] as $key => $event_id) {
        //skip the calendar title
        if (empty($key)) continue;
        $message_data = phorum_db_interact(DB_RETURN_ASSOCS,"SELECT * FROM {$PHORUM["message_table"]} WHERE meta LIKE '%$event_id%'",NULL,0);
        //skip with error if a matching message cannot be found for an event
        if (empty($message_data)) {
            $event_data[$key]["google_event_id"] = $event_id;
            $event_data[$key]["error"] = "No message found.";
            continue;
        }
        $event_data[$key]["message"] = $message_data[0];
        $event_data[$key]["message"]["meta"] = unserialize($event_data[$key]["message"]["meta"]);
        //set the forum id for the message to generate a proper url
        $PHORUM["forum_id"] = $message_data[0]["forum_id"];
        //get the read url for each message
        $event_data[$key]["read_url"] = phorum_get_url(PHORUM_READ_URL, $message_data[0]["message_id"]);
    }
    
    // reset the global forum id
    $PHORUM["forum_id"] = $curr_forum_id;
    
    // grab the number of results from google's response
    preg_match("/(?:<openSearch:totalResults>)([^<]+)/i",$google_response,$matches);
    $num_events = (int)$matches[1];
    if ($num_events == 0) {
        //set the cache data
        phorum_mod_google_calendar_set_weekly_events_cache($cal_id,$num_events,$start_min,$tz_offset);
        return $num_events;
    }
    
    //grab the event titles from google's response
    preg_match_all("/(?:<title type='text'>)([^<]+)/i",$google_response,$matches);
    foreach ($matches[1] as $key => $title) {
        //skip the calendar title
        if (empty($key)) continue;
        $event_data[$key]["title"] = $title;
    }
    
    //set the cache data
    phorum_mod_google_calendar_set_weekly_events_cache($cal_id,$event_data,$start_min,$tz_offset);
    
    return $event_data;
    
}

// Get xml feed for events from google
function phorum_mod_google_calendar_gdata_get_events($google_url) {
    
    global $PHORUM;
    
    // Create a cURL handle
    $ch = curl_init($google_url);
    
    // Set the cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);  //set to true ?
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: AuthSub token=\"{$PHORUM["phorum_mod_google_calendar"]["SessionToken"]}\"",
    ));
    
    //execute the HTTP request
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

// set weekly events cache
function phorum_mod_google_calendar_set_weekly_events_cache($cal_id,$cache_data,$start_min,$tz_offset) {
    global $PHORUM;
    
    $PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"][$cal_id][$tz_offset]["timestamp"] = time();
    $PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"][$cal_id][$tz_offset]["start_min_timestamp"] = $start_min;
    // strip unnecessary data from the cache
    if (!empty($cache_data)) {
        foreach($cache_data as $key => $data) {
            if (isset($data["message"]["body"])) unset ($cache_data[$key]["message"]["body"]);
            if (isset($data["message"]["ip"])) unset ($cache_data[$key]["message"]["ip"]);
            if (isset($data["message"]["msgid"])) unset ($cache_data[$key]["message"]["msgid"]);
            if (isset($data["message"]["viewcount"])) unset ($cache_data[$key]["message"]["viewcount"]);
            if (isset($data["message"]["threadviewcount"])) unset ($cache_data[$key]["message"]["threadviewcount"]);
            if (isset($data["message"]["closed"])) unset ($cache_data[$key]["message"]["closed"]);
            if (isset($data["message"]["recent_message_id"])) unset ($cache_data[$key]["message"]["recent_message_id"]);
            if (isset($data["message"]["recent_user_id"])) unset ($cache_data[$key]["message"]["recent_user_id"]);
            if (isset($data["message"]["recent_author"])) unset ($cache_data[$key]["message"]["recent_author"]);
            if (isset($data["message"]["moved"])) unset ($cache_data[$key]["message"]["moved"]);
            if (isset($data["message"]["meta"]["message_ids"])) unset ($cache_data[$key]["message"]["meta"]["message_ids"]);
            if (isset($data["message"]["meta"]["message_ids_moderator"])) unset ($cache_data[$key]["message"]["meta"]["message_ids_moderator"]);
            if (isset($data["message"]["meta"]["google_calendar"]["event_id"])) unset ($cache_data[$key]["message"]["meta"]["google_calendar"]["event_id"]);
            if (isset($data["message"]["meta"]["google_calendar"]["edit_url"])) unset ($cache_data[$key]["message"]["meta"]["google_calendar"]["edit_url"]);
            if (isset($data["message"]["meta"]["google_calendar"]["alternate_url"])) unset ($cache_data[$key]["message"]["meta"]["google_calendar"]["alternate_url"]);
            if (isset($data["message"]["meta"]["google_calendar"]["self_url"])) unset ($cache_data[$key]["message"]["meta"]["google_calendar"]["self_url"]);
            if (isset($data["message"]["meta"]["google_calendar"]["xml"])) unset ($cache_data[$key]["message"]["meta"]["google_calendar"]["xml"]);
        }
    }
    $PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"][$cal_id][$tz_offset]["cache_data"] = $cache_data;
    
    // Only cache if the serialized cache does not exceed the TEXT size limit
    $test_data = serialize($PHORUM["phorum_mod_google_calendar_cache"]);
    if (strlen($test_data) < 65535) {
        phorum_db_update_settings(array("phorum_mod_google_calendar_cache"=>$PHORUM["phorum_mod_google_calendar_cache"]));
    }
    
    return;
}
?>
