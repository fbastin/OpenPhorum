<?php

if(!defined("PHORUM")) return;

// restore a calendar
function phorum_mod_google_calendar_restore_calendar ($calendar_type, $data) {
    
    global $PHORUM;
    
    //format the calendar data as a google friendly xml entry
    $calendar_entry = phorum_mod_google_calendar_render_entry_edit_calendar($calendar_type, $data);
    $google_url = $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]]["edit_url"];
    
    //send the entry to google
    $google_response = phorum_mod_google_calendar_gdata_edit_calendar($calendar_entry, $google_url);
    
    // most likely google will respond with a redirect and a session id, so resend with the session id
    if ($gsession_pos = strpos($google_response,"gsessionid=")) {
        preg_match("/(?:gsessionid=)([^\"]+)/i",$google_response,$matches);
        $gsession_id = $matches[1];
        $google_url .= "?gsessionid=".$gsession_id;
        $google_response = phorum_mod_google_calendar_gdata_edit_calendar($calendar_entry, $google_url);
    }
    
    // grab the calendar id from google's response
    preg_match("/(?:<id>)([^<]+)/i",$google_response,$matches);
    // if there is no id, grab the error message and return
    if (empty($matches)) {
        $data["error"] = htmlspecialchars($google_response);
        if (function_exists('event_logging_writelog')) {
            $log_message = "Error deleting the Google calendar for the ".$PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]]["name"];
            if ($calendar_type == "categories") {
                $log_message .= " category";
            } elseif ($calendar_type == "forums") {
                $log_message .= " forum";
            } elseif ($calendar_type == "folders") {
                $log_message .= " folder";
            }
            $log_message .= ".";
            event_logging_writelog(array(
                "message"	=> $log_message,
                "details"   => "Google returned this error: ".htmlspecialchars($google_response),
            ));
        }
        return $data;
    }
    $data["google_calendar_id"] = $matches[1];
    
    // grab the calendar's edit url from google's response
    preg_match("/(?:rel='edit')([^>]+)/i",$google_response,$matches);
    preg_match("/(?:href=')([^']+)/i",$matches[1],$matches);
    $data["edit_url"] = $matches[1];
    
    //grab the full xml for later use if need be
    $data["google_xml"] = $google_response;
    
    //update the last event ts
    include_once("./mods/google_calendar/calendar_bin/calendar_timestamp_functions.php");
    phorum_mod_google_calendar_set_latest_event($data["calendar_id"]);
    
    if (function_exists('event_logging_writelog')) {
        $log_message = "Google calendar deleted for the ".$PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]]["name"];
        if ($calendar_type == "categories") {
            $log_message .= " category";
        } elseif ($calendar_type == "forums") {
            $log_message .= " forum";
        } elseif ($calendar_type == "folders") {
            $log_message .= " folder";
        }
        $log_message .= ".";
        event_logging_writelog(array(
            "message"	=> $log_message,
        ));
    }
    
    return $data;
}

// Send xml entry to google to restore a calendar
function phorum_mod_google_calendar_gdata_edit_calendar($calendar_entry, $google_url) {
    
    global $PHORUM;
    
    // Create a cURL handle
    $ch = curl_init($google_url);
    
    // prepare the xml entry for HTTP PUT
    $put_data = tmpfile();
    fwrite($put_data, $calendar_entry);
    fseek($put_data, 0);

    
    // Set the cURL options
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILE, $put_data);
    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($calendar_entry));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);  //set to true ?
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: AuthSub token=\"{$PHORUM["phorum_mod_google_calendar"]["SessionToken"]}\"",
        "Content-Type: application/atom+xml"
    ));
    
    //execute the HTTP request
    $result = curl_exec($ch);
    fclose($put_data);
    curl_close($ch);
    
    return $result;
}

// Create the xml entry to restore calendar
function phorum_mod_google_calendar_render_entry_edit_calendar ($calendar_type, $data) {
    
    global $PHORUM;
    $google_id = $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]]["google_calendar_id"];
    
    $calendar_entry = "<entry xmlns='http://www.w3.org/2005/Atom'\n".
        "xmlns:gd='http://schemas.google.com/g/2005'\n".
        "xmlns:gCal='http://schemas.google.com/gCal/2005'>\n".
        "<id>$google_id</id>\n".
        "<title type='text'>{$data["name"]}</title>\n".
        "<gCal:hidden value='false' />\n".
        "<gCal:selected value='true' />\n".
        "<gCal:color value='{$data["color"]}'></gCal:color>\n".
        "</entry>";
    return $calendar_entry;
}

// delete a calendar
function phorum_mod_google_calendar_delete_calendar ($calendar_type, $data) {
    
    global $PHORUM;
    
    $google_url = $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]]["edit_url"];
    
    //send the delete request to google
    $google_response = phorum_mod_google_calendar_gdata_delete_calendar($google_url);
    
    // most likely google will respond with a redirect and a session id, so resend with the session id
    if ($gsession_pos = strpos($google_response,"gsessionid=")) {
        preg_match("/(?:gsessionid=)([^\"]+)/i",$google_response,$matches);
        $gsession_id = $matches[1];
        $google_url .= "?gsessionid=".$gsession_id;
        $google_response = phorum_mod_google_calendar_gdata_delete_calendar($google_url);
    }
    
    // check google's response for errors
    if (!strpos($google_response,"xmlns='http://www.w3.org/2005/Atom'") && !strpos($google_response,"does not exist in this feed.")) {
        $data["error"] = htmlspecialchars($google_response);
    }

    return $data;
}

// Send xml entry to google to delete a calendar
function phorum_mod_google_calendar_gdata_delete_calendar($google_url) {
    
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
