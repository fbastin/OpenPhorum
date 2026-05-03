<?php

if(!defined("PHORUM")) return;

// delete an event
function phorum_mod_google_calendar_delete_event ($message_data) {
    
    global $PHORUM;
    
    $google_url = $message_data["meta"]["google_calendar"]["edit_url"];
    
    //send the delete request to google
    $google_response = phorum_mod_google_calendar_gdata_delete_event($google_url);
    
    // most likely google will respond with a redirect and a session id, so resend with the session id
    if ($gsession_pos = strpos($google_response,"gsessionid=")) {
        preg_match("/(?:gsessionid=)([^\"]+)/i",$google_response,$matches);
        $gsession_id = $matches[1];
        $google_url .= "?gsessionid=".$gsession_id;
        $google_response = phorum_mod_google_calendar_gdata_delete_event($google_url);
    }
    
    // check google's response for errors
    if (!empty($google_response) 
        && !strpos($google_response,"xmlns='http://www.w3.org/2005/Atom'") 
        && !strpos($google_response,"does not exist in this feed.")) {
        $message_data["meta"]["google_calendar"]["error"] = htmlspecialchars($google_response);
        if (function_exists('event_logging_writelog')) {
            $log_message = "Error deleting the calendar event for message ".$message_data["message_id"].".";
            event_logging_writelog(array(
                "message"	=> $log_message,
                "details"   => "Google returned this error: ".htmlspecialchars($google_response),
            ));
        }
        return $message_data;
    }

    // update the last event ts
    include_once("./mods/google_calendar/calendar_bin/calendar_timestamp_functions.php");
    phorum_mod_google_calendar_set_latest_event($message_data["meta"]["google_calendar"]["calendar_id"]);
    
    // remove the event data from the message data
    unset($message_data["meta"]["google_calendar"]);
    
    if (function_exists('event_logging_writelog')) {
        $log_message = "Calendar event deleted for message ".$message_data["message_id"].".";
        event_logging_writelog(array(
            "message"	=> $log_message,
        ));
    }
    
    return $message_data;
}

// Send xml entry to google to delete an event
function phorum_mod_google_calendar_gdata_delete_event($google_url) {
    
    global $PHORUM;
    
    // Create a cURL handle
    $ch = curl_init($google_url);
    
    // Set the cURL options
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
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
?>
