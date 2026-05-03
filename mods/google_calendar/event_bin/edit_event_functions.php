<?php

if(!defined("PHORUM")) return;

// update an event
function phorum_mod_google_calendar_edit_event ($event_data) {
    
    global $PHORUM;

    if (empty($event_data["google_calendar"]["event_id"])) {
        include_once("./mods/google_calendar/event_bin/new_event_functions.php");
        $event_data = phorum_mod_google_calendar_create_new_event($event_data);
        return $event_data;
    }
    $google_url = $event_data["google_calendar"]["edit_url"];
    
    $event_entry = phorum_mod_google_calendar_render_entry_edit_event($event_data);
    
    //send the entry to google
    $google_response = phorum_mod_google_calendar_gdata_edit_event($event_entry, $google_url);
    
    // most likely google will respond with a redirect and a session id, so resend with the session id
    if ($gsession_pos = strpos($google_response,"gsessionid=")) {
        preg_match("/(?:gsessionid=)([^\"]+)/i",$google_response,$matches);
        $gsession_id = $matches[1];
        $google_url .= "?gsessionid=".$gsession_id;
        $google_response = phorum_mod_google_calendar_gdata_edit_event($event_entry, $google_url);
    }
    
    // grab the event id from google's response
    preg_match("/(?:<id>)([^<]+)/i",$google_response,$matches);
        if (empty($matches)) {
        $event_data["google_calendar"]["error"] = htmlspecialchars($google_response);
        if (function_exists('event_logging_writelog')) {
            $log_message = "Error editing the calendar event for message ".$event_data["message_id"].".";
            event_logging_writelog(array(
                "message"	=> $log_message,
                "details"   => "Google returned this error: ".htmlspecialchars($google_response),
            ));
        }
        return $event_data;
    }
    $event_data["google_calendar"]["event_id"] = $matches[1];
    
    // grab the calendar's edit url from google's response
    preg_match("/(?:rel='edit')([^>]+)/i",$google_response,$matches);
    preg_match("/(?:href=')([^']+)/i",$matches[1],$matches);
    $event_data["google_calendar"]["edit_url"] = $matches[1];
    
    // grab the calendar's alternate url from google's response
    preg_match("/(?:rel='alternate')([^>]+)/i",$google_response,$matches);
    preg_match("/(?:href=')([^']+)/i",$matches[1],$matches);
    $event_data["google_calendar"]["alternate_url"] = $matches[1];
    
    // grab the calendar's self url from google's response
    preg_match("/(?:rel='self')([^>]+)/i",$google_response,$matches);
    preg_match("/(?:href=')([^']+)/i",$matches[1],$matches);
    $event_data["google_calendar"]["self_url"] = $matches[1];
    
    //grab the full xml for later use if need be
    $event_data["google_calendar"]["xml"] = $google_response;   
    
    //update the last event ts
    include_once("./mods/google_calendar/calendar_bin/calendar_timestamp_functions.php");
    phorum_mod_google_calendar_set_latest_event($event_data["google_calendar"]["calendar_id"]);
    
    if (function_exists('event_logging_writelog')) {
        $log_message = "Calendar event edited for message ".$event_data["message_id"]." by ".$event_data['author'].".";
        event_logging_writelog(array(
            "message"	=> $log_message,
        ));
    }
    
    return $event_data;
}

// Send xml entry to google to edit an event
function phorum_mod_google_calendar_gdata_edit_event($event_entry, $google_url) {
    
    global $PHORUM;
    
    // Create a cURL handle
    $ch = curl_init($google_url);
    
    // prepare the xml entry for HTTP PUT
    $put_data = tmpfile();
    fwrite($put_data, $event_entry);
    fseek($put_data, 0);
    
    // Set the cURL options
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILE, $put_data);
    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($event_entry));
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

//create the xml entry to edit an event
function phorum_mod_google_calendar_render_entry_edit_event($event_data){

    $event_entry = "<entry xmlns='http://www.w3.org/2005/Atom' xmlns:gd=\"http://schemas.google.com/g/2005\">
        <category scheme=\"http://schemas.google.com/g/2005#kind\" term=\"http://schemas.google.com/g/2005#event\"/>
        <id>".rawurlencode($event_data["google_calendar"]["event_id"])."</id>
        <published>{$event_data["google_calendar"]["timestamp"]}</published>
        <title>{$event_data["EventTitle"]}</title>
        <content>{$event_data["EventDescription"]}</content>
        <gd:when startTime='{$event_data["google_calendar"]["StartTime"]}' endTime='{$event_data["google_calendar"]["EndTime"]}'></gd:when>\n";
        if (!empty($event_data["google_calendar"]["where"])) $event_entry .= "<gd:where valueString='{$event_data["google_calendar"]["where"]}'/>\n";
        $event_entry .= "<gd:eventStatus value=\"http://schemas.google.com/g/2005#event.confirmed\"/>
        <gd:visibility value=\"http://schemas.google.com/g/2005#event.default\"/>
        <gd:transparency value=\"http://schemas.google.com/g/2005#event.transparent\"/>
        </entry>";
    
    return $event_entry;
}
    
?>
