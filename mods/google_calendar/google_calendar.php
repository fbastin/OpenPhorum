<?php

if(!defined("PHORUM")) return;

function phorum_mod_google_calendar_addon()
{
    global $PHORUM;

    //if there is no embedded calendar, no need to continue
    if (empty($PHORUM["phorum_mod_google_calendar"]["google_embed_code"])) return;
    
    // Check if the user has read access for the active forum.
    // Not really important to check, but good for some paranoia.
    if (!phorum_check_read_common()) { return; }
    
    // Build all our basic URLs.
    phorum_build_common_urls();
    
    $PHORUM["DATA"]["URL"]["GOOGLE_CALENDAR"] = phorum_get_url(PHORUM_ADDON_URL, "module=google_calendar");
        
    $PHORUM["DATA"]["PHORUM_PAGE"] = "search";
    
    // Override the default title and description.
    $PHORUM["DATA"]["HEADING"] = $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["CalendarTitle"];
    $PHORUM["DATA"]["HTML_TITLE"] = htmlspecialchars(strip_tags($PHORUM["DATA"]["HEADING"]));
    $PHORUM["DATA"]["HTML_DESCRIPTION"] = $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["CalendarDescription"];
    $PHORUM["DATA"]["BREADCRUMBS"][] = array(
        "URL"  => NULL,
        "TEXT" => $PHORUM["DATA"]["HEADING"]
    );
    
    $PHORUM["DATA"]["phorum_mod_google_calendar_google_embed_code"] = $PHORUM["phorum_mod_google_calendar"]["google_embed_code"];
    
    phorum_output("google_calendar::calendar_addon");
    
}

function phorum_mod_google_calendar_after_post ($data, $forum_id = NULL) {
    
    // if the message is not yet approved, we do not need to act on it
    if ($data["status"] <= 0 || empty($data["meta"]["google_calendar"]["add_event"])) return $data;

    GLOBAL $PHORUM;

    if ($PHORUM["DBCONFIG"]["type"] == "mysqli" &&
        !file_exists("./include/db/mysqli.php")) {
        $PHORUM["DBCONFIG"]["type"] = "mysql";
    }

    // Load the database layer and other necessary files
    include_once( "./include/db/{$PHORUM['DBCONFIG']['type']}.php" );
    include_once("./include/format_functions.php");
    include_once("./include/api/base.php");

    //run the automatic timezone mod if enabled
    if (!empty($PHORUM["hooks"]["common"]) && in_array("automatic_timezones",$PHORUM["hooks"]["common"]["mods"])) {
        phorum_mod_automatic_timezones_common();
    }
    
    //get the time zone offset
    if (!empty($PHORUM["user_time_zone"]) && $PHORUM["user"]["tz_offset"] != -99) {
        //use the user's time zone offset if allowed/selected
        $tz_offset = $PHORUM["user"]["tz_offset"];
    } else {
        //otherwise use the server time zone offset
        $tz_offset = $PHORUM["tz_offset"];
    }
    
    //get the timestamp for this message (removing the server offset)
    $offset_ts = $data["datestamp"] - ($PHORUM["tz_offset"] * 3600);
    
    //fill in the event data
    $event_data = array(
        "sitename"    => $PHORUM['title'],
        "forumname"   => strip_tags($PHORUM["DATA"]["NAME"]),
        "forum_id"    => $PHORUM['forum_id'],
        "message_id"  => $data['message_id'],
        "author"      => $data['author'],
        "subject"     => $data['subject'],
        "full_body"   => $data['body'],
        "plain_body"  => phorum_strip_body($data['body']),
        "read_url"    => phorum_get_url(PHORUM_READ_URL, $data['thread'], $data['message_id']),
        "remove_url"  => phorum_get_url(PHORUM_FOLLOW_URL, $data['thread'], "remove=1"),
        "noemail_url" => phorum_get_url(PHORUM_FOLLOW_URL, $data['thread'], "noemail=1"),
        "followed_threads_url" => phorum_get_url(PHORUM_CONTROLCENTER_URL, "panel=" . PHORUM_CC_SUBSCRIPTION_THREADS),
        "EventDescription" => $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["EventDescription"],
        "EventTitle"  => $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["EventTitle"],
    );
    
    //get the google_calendar data
    $event_data["google_calendar"] = $data["meta"]["google_calendar"];
    
    //format the date/time strings
    $event_data["google_calendar"]["timestamp"] = !empty($event_data["google_calendar"]["timestamp"])
        ? $event_data["google_calendar"]["timestamp"]
        : date("Y-m-d",$offset_ts)."T".date("H:i:s",$offset_ts)."Z";
    $pre_start_time = strtotime($event_data["google_calendar"]["start_year"]."-".sprintf("%02d",$event_data["google_calendar"]["start_month"]).
        "-".sprintf("%02d",$event_data["google_calendar"]["start_day"])."T".sprintf("%02d",$event_data["google_calendar"]["start_hour"]).
        ":".sprintf("%02d",$event_data["google_calendar"]["start_minute"]).":00") - ($tz_offset * 3600);
    $event_data["google_calendar"]["StartTime"] = date("Y-m-d",$pre_start_time)."T".date("H:i:s",$pre_start_time)."Z";
    $pre_end_time = strtotime($event_data["google_calendar"]["end_year"]."-".sprintf("%02d",$event_data["google_calendar"]["end_month"]).
        "-".sprintf("%02d",$event_data["google_calendar"]["end_day"])."T".sprintf("%02d",$event_data["google_calendar"]["end_hour"]).
        ":".sprintf("%02d",$event_data["google_calendar"]["end_minute"]).":00") - ($tz_offset * 3600);
    $event_data["google_calendar"]["EndTime"] = date("Y-m-d",$pre_end_time)."T".date("H:i:s",$pre_end_time)."Z";
    
    if (isset($_POST[PHORUM_SESSION_LONG_TERM])) {
       // strip any auth info from the read url
        $event_data["read_url"] = preg_replace("!,{0,1}" . PHORUM_SESSION_LONG_TERM . "=" . urlencode($_POST[PHORUM_SESSION_LONG_TERM]) . "!", "", $event_data["read_url"]);
        $event_data["remove_url"] = preg_replace("!,{0,1}" . PHORUM_SESSION_LONG_TERM . "=" . urlencode($_POST[PHORUM_SESSION_LONG_TERM]) . "!", "", $event_data["remove_url"]);
        $event_data["noemail_url"] = preg_replace("!,{0,1}" . PHORUM_SESSION_LONG_TERM . "=" . urlencode($_POST[PHORUM_SESSION_LONG_TERM]) . "!", "", $event_data["noemail_url"]);
        $event_data["followed_threads_url"] = preg_replace("!,{0,1}" . PHORUM_SESSION_LONG_TERM . "=" . urlencode($_POST[PHORUM_SESSION_LONG_TERM]) . "!", "", $event_data["followed_threads_url"]);
    }
    //process the title and description strings for any variable to be replaced and any xml characters to be escaped
    foreach(array_keys($event_data) as $key){
        if ($event_data[$key] === NULL || is_array($event_data[$key])) continue;
        $event_data["EventDescription"] = str_replace("%$key%", $event_data[$key], $event_data["EventDescription"]);
        $event_data["EventDescription"] = str_replace("<", "&#60;", $event_data["EventDescription"]);
        $event_data["EventTitle"] = str_replace("%$key%", $event_data[$key], $event_data["EventTitle"]);
        $event_data["EventTitle"] = str_replace("<", "&#60;", $event_data["EventTitle"]);
    }

    if (!empty($event_data["google_calendar"]["event_id"])) {
        //get the needed code for contacting google
        include_once("./mods/google_calendar/event_bin/edit_event_functions.php");
        //send the event to google
        $event_data = phorum_mod_google_calendar_edit_event ($event_data);
    } else {
        //get the needed code for contacting google
        include_once("./mods/google_calendar/event_bin/new_event_functions.php");
        //send the event to google
        $event_data = phorum_mod_google_calendar_create_new_event ($event_data, $forum_id);
    }
    
    //add the event info to the meta data of the message after getting the latest version of the message
    $newest_message_version = phorum_db_get_message($data["message_id"],"message_id",true);
    $update_data["meta"] = $newest_message_version["meta"];
    $update_data["meta"]["google_calendar"] = $event_data["google_calendar"];
    phorum_db_update_message($data["message_id"],$update_data);
    
    return $data;
}

function phorum_mod_google_calendar_after_approve($data) {

    if (PHORUM_APPROVE_MESSAGE || PHORUM_APPROVE_MESSAGE_TREE) {

        $data[0]["status"] = 2;
        
        phorum_mod_google_calendar_after_post($data[0]);

    }
    
    return $data;

}

function phorum_mod_google_calendar_after_edit($message) {

    if (!empty($_POST["google_calendar_add_event"])) {
        foreach ($_POST as $name => $data) {
            if (strpos($name,"oogle_calendar")) {
                $sname = str_replace("google_calendar_","",$name);
                $message["meta"]["google_calendar"][$sname] = $data;
            }
        }
    }
    
    //if we are in category mode
    if (!empty($message["meta"]["google_calendar"]["category"])) {
        
        global $PHORUM;
        
        //and the event category has been changed
        if ($message["meta"]["google_calendar"]["category"] != 
            $PHORUM["phorum_mod_google_calendar"]["temp_event_edit"][$message["message_id"]]["old_category"]) {

            //then first delete the old event
            include_once("./mods/google_calendar/event_bin/deleted_event_functions.php");
            $temp_data = phorum_mod_google_calendar_delete_event($message["meta"]);

            //and empty the old event id
            unset($message["meta"]["google_calendar"]["event_id"]);
            
            //and empty the temp data
            unset($PHORUM["phorum_mod_google_calendar"]["temp_event_edit"][$message["message_id"]]["old_category"]);
        }
    }
    
    phorum_mod_google_calendar_after_post($message);
    
    return $message;

}

function phorum_mod_google_calendar_after_header() {
    
    global $PHORUM;
    
    //pull in the requisite event listings javascript, if necessary
    if (!empty($PHORUM["phorum_mod_google_calendar"]["show_event_listing"]) &&
        !empty($PHORUM["phorum_mod_google_calendar"]["event_listing_pages"][phorum_page])) {
        include_once("./mods/google_calendar/listings_bin/event_listings_js.php");
        // pull in the event listing template if the admin has chosen to display
        // it automatically
        if (!empty($PHORUM["phorum_mod_google_calendar"]["auto_event_listing"]))
            include phorum_get_template("google_calendar::event_listing");
    }

}

function phorum_mod_google_calendar_before_delete($data) {
    
    if (!empty($data[3]["meta"]["google_calendar"]["event_id"])) {
        
        include_once("./mods/google_calendar/event_bin/deleted_event_functions.php");
        $data[3] = phorum_mod_google_calendar_delete_event($data[3]);
        
    }
    
    return $data;
    
}

function phorum_mod_google_calendar_before_edit($message) {

    // if the "add event" field has been unchecked but there was an event for 
    // this post, we will need to delete the event.
    if (empty($_POST["google_calendar_add_event"]) 
        && !empty($message["meta"]["google_calendar"]["event_id"])) {
        include_once("./mods/google_calendar/event_bin/deleted_event_functions.php");
        $message = phorum_mod_google_calendar_delete_event($message);
    }
    
    if (!empty($_POST["google_calendar_category"])) {
        global $PHORUM;
        $old_message = phorum_db_get_message($message["message_id"]);
        if (!empty($old_message["meta"]["google_calendar"]["category"])) {
            $PHORUM["phorum_mod_google_calendar"]["temp_event_edit"][$message["message_id"]]["old_category"] = $old_message["meta"]["google_calendar"]["category"];
            phorum_db_update_settings(array("phorum_mod_google_calendar"=>$PHORUM["phorum_mod_google_calendar"]));
        }
    }
    
    return $message;

}

function phorum_mod_google_calendar_before_editor($message) {

    global $PHORUM;

    //pull the google_calendar data from the post/edited message and save it for the preview/edit
    if (!empty($_POST["google_calendar_add_event"])) {
        foreach ($_POST as $name => $data) {
            if (strpos($name,"oogle_calendar")) {
                $sname = str_replace("google_calendar_","",$name);
                $PHORUM["google_calendar_post_temp"][$sname] = $data;
            }
        }
    } elseif (!empty($message["meta"]["google_calendar"])) {
        $PHORUM["google_calendar_post_temp"] = $message["meta"]["google_calendar"];
    }
    
    return $message;
}

function phorum_mod_google_calendar_before_post($message) {
    
    //pull the google_calendar data from the post and save it as meta data
    if (!empty($_POST["google_calendar_add_event"])) {
        foreach ($_POST as $name => $data) {
            if (strpos($name,"oogle_calendar")) {
                $sname = str_replace("google_calendar_","",$name);
                $message["meta"]["google_calendar"][$sname] = $data;
            }
        }
        
    }
    
    return $message;
}

function phorum_mod_google_calendar_format($messages) {
    
   global $PHORUM;
    
    //if the admin has not enabled showing events in the message body, no need to continue
    if (empty($PHORUM["phorum_mod_google_calendar"]["show_event_in_message"])) return $messages;
    
    foreach ($messages as $message_id => $message_data) {
        
        //grab the google calendar data or skip if non-existent
        if (!empty($message_data["meta"]["google_calendar"]["event_id"]) ) {
            $gc_data = $message_data["meta"]["google_calendar"];
        } else {
            continue;
        }

        
        if ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "forums") { 
            //if we are in forum mode and the current forum does not have an active calendar associated with it, no need to continue
            if (!empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["forums"][$message_data["forum_id"]]["deleted"])
                || empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["forums"][$message_data["forum_id"]]))
                continue;
            $display_header = $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["show_start"];
        } elseif ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "folders") { 
            $forums = phorum_db_get_forums($message_data["forum_id"]);
            //if we are in folder mode and the current forum's folder does not have an active calendar associated with it, no need to continue
            if (!empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["folders"][$forums[$message_data["forum_id"]]["parent_id"]]["deleted"])
                || empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["folders"][$forums[$message_data["forum_id"]]["parent_id"]])) 
                continue;
            $display_header = $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["show_start"];
        } elseif ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "categories") {
            //if we are in category mode, prepare to display the category or skip if no category
            if (empty($gc_data["category"])) continue;
            $display_header = htmlspecialchars($PHORUM["phorum_mod_google_calendar"]["calendars"]["categories"][$gc_data["category"]]["name"]).
                " ".$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["show_start_with_category"];
        }
        
        //grab and format the start and end dates/times
        $time_format = (!empty($PHORUM["short_date_time"])) ? $PHORUM["short_date_time"] : $PHORUM["long_date_time"];
        $formatted_start_time = phorum_date($time_format,strtotime($gc_data["StartTime"]));
        $formatted_end_time = phorum_date($time_format,strtotime($gc_data["EndTime"]));
        
        //create the div to show calendar events in the message body
        $event_display = "<img src=\"".$PHORUM["http_path"]."/mods/google_calendar/images/calendar_view_month.png\"> ".
            $display_header.$formatted_start_time.
            $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["show_end"].$formatted_end_time;
        if (!empty($gc_data["where"]))
            $event_display .= " (".htmlspecialchars($gc_data["where"]).")";
        $event_display .= "<hr /><br />";
        
        if (!empty($messages[$message_id]["body"])) {
            $messages[$message_id]["body"] = $event_display.$messages[$message_id]["body"];
        } else {
            $messages[$message_id]["body"] = $event_display;
        }
    }
    
    return $messages;
    
}
function phorum_mod_google_calendar_hide($message_id) {
    
    $message = phorum_db_get_message($message_id,"message_id",true);
    
    //no need to continue if no event was created for this message
    if (empty($message["meta"]["google_calendar"]["event_id"])) return $message_id;
    
    //delete the google calendar event (google has no "hidden event" feature
    include_once("./mods/google_calendar/event_bin/deleted_event_functions.php");
    $temp_data = phorum_mod_google_calendar_delete_event($message["meta"]);

    //unset the event id so a new event can be created later
    $update_data["meta"] = $message["meta"];
    unset($update_data["meta"]["google_calendar"]["event_id"]);
    
    phorum_db_update_message($message_id, $update_data);
    
    return $message_id;
}
    
function phorum_mod_google_calendar_move_thread($message_id) {
    
    global $PHORUM;
    
    //no need to process data if we are in category mode
    if ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "categories") return $message_id;
    
    $message = phorum_db_get_message($message_id,"message_id",true);
    
    //no need to process data if there is no calendar event
    if (empty($message["meta"]["google_calendar"]["add_event"])) return $message_id;
    
    if (!empty($message["meta"]["google_calendar"]["event_id"])) {
        //first delete the old event
        include_once("./mods/google_calendar/event_bin/deleted_event_functions.php");
        $temp_data = phorum_mod_google_calendar_delete_event($message["meta"]);
    
        //and empty the old event id
        unset($message["meta"]["google_calendar"]["event_id"]);
        
    }
    
    if ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "forums") {
        //then recreate the event in the new forum calendar
        phorum_mod_google_calendar_after_post($message, $message["forum_id"]);
    } else {
        //or recreate the event in the new folder calendar
        $forum = phorum_db_get_forums($message["forum_id"]);
        phorum_mod_google_calendar_after_post($message, $forum[$message["forum_id"]]["parent_id"]);
    }
    
    return $message_id;
}

function phorum_mod_google_calendar_start_output() {
    
    global $PHORUM;
    
    if (!empty($PHORUM["phorum_mod_google_calendar"]["google_embed_code"]))
        $PHORUM["DATA"]["URL"]["GOOGLE_CALENDAR"] = phorum_get_url(PHORUM_ADDON_URL, "module=google_calendar");
    
    // no need to continue if the admin does not want an events listing, or 
    // if we are not on a page on which the admin wants the calendar shown 
    if (empty($PHORUM["phorum_mod_google_calendar"]["show_event_listing"])
        || empty($PHORUM["phorum_mod_google_calendar"]["event_listing_pages"][phorum_page]))
        return;
    
    //pull in the requesite event listings code
    if ($PHORUM["phorum_mod_google_calendar"]["show_event_listing"] == 1) {
        include_once("./mods/google_calendar/listings_bin/weekly_event_listings_start_output.php");
    } elseif ($PHORUM["phorum_mod_google_calendar"]["show_event_listing"] == 2) {
        include_once("./mods/google_calendar/listings_bin/next_seven_event_listings_start_output.php");
    }
    
    return;
}

function phorum_mod_google_calendar_tpl_editor_buttons () {
    
    global $PHORUM;
    
    // if this is not a new topic or it is a PM, no need to continue
    if (!empty($PHORUM["DATA"]["POSTING"]["parent_id"]) 
        || (defined("phorum_page") && phorum_page == "pm")) return;
    
    // check permissions to see if the current visitor can post an event
    if (!empty($PHORUM["phorum_mod_google_calendar"]["post_event_permission"])) {
        // no permission by default
        $can_post_event = false;
        // admin can always post events
        if (!empty($PHORUM["user"]["admin"])) $can_post_event = true;
        // if we are allowing moderators and admin
        if ($PHORUM["phorum_mod_google_calendar"]["post_event_permission"] == 1) {
            // and the user is a moderator, permission granted
            if (phorum_api_user_check_access(PHORUM_USER_ALLOW_MODERATE_MESSAGES)) 
                $can_post_event = true;
        // if we are allowing selected groups, moderators and admin
        } elseif ($PHORUM["phorum_mod_google_calendar"]["post_event_permission"] == 3) {
            // and the user is a moderator, permission granted
            if (phorum_api_user_check_access(PHORUM_USER_ALLOW_MODERATE_MESSAGES)) 
                $can_post_event = true;
            // or the user is in one of the permitted groups, permission granted
            if (!empty($PHORUM["phorum_mod_google_calendar"]["event_posting_groups"])
                && phorum_api_user_check_group_access(PHORUM_USER_GROUP_APPROVED, $PHORUM["phorum_mod_google_calendar"]["event_posting_groups"]) != array())
                $can_post_event = true;
        // if we are allowing selected groups and admin
        } elseif ($PHORUM["phorum_mod_google_calendar"]["post_event_permission"] == 4) {
            // and the user is in one of the permitted groups, permission granted
            if (!empty($PHORUM["phorum_mod_google_calendar"]["event_posting_groups"])
                && phorum_api_user_check_group_access(PHORUM_USER_GROUP_APPROVED, $PHORUM["phorum_mod_google_calendar"]["event_posting_groups"]) != array())
                $can_post_event = true;
        }
        // if permission is not granted, no need to continue
        if (!$can_post_event) return;
    }
    
    if ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "forums" 
        //if we are in forum mode and the current forum does not have an active calendar associated with it, do not allow posting events
        && (!empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["forums"][$PHORUM["DATA"]["FORUM_ID"]]["deleted"])
        || empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["forums"][$PHORUM["DATA"]["FORUM_ID"]]))) {
        return;
    } elseif ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "folders" 
        //if we are in folder mode and the current forum's folder does not have an active calendar associated with it, do not allow posting events
        && (!empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["folders"][$PHORUM["parent_id"]]["deleted"])
        || empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["folders"][$PHORUM["parent_id"]]))) {
        return;
    } elseif ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "categories") {
        //if we are in category mode and there are no categories, do not allow posting events
        if (empty($PHORUM["phorum_mod_google_calendar"]["calendars"]["categories"]) ||
            //or if posting is only allowed in certain forums/folders and the current forum or folder is not allowed, do not allow posting events
            (!empty($PHORUM["phorum_mod_google_calendar"]["events_from_any_forum"])
                && !in_array($PHORUM["DATA"]["FORUM_ID"], $PHORUM["phorum_mod_google_calendar"]["events_from_selected_forums"])
                && !in_array($PHORUM["parent_id"], $PHORUM["phorum_mod_google_calendar"]["events_from_selected_forums"]))) return;
        //check to see if there are any active categories.  if not, do not allow posting events
        foreach ($PHORUM["phorum_mod_google_calendar"]["calendars"]["categories"] as $cal_id => $cal_data) {
            if (empty($cal_data["deleted"])) $no_return = 1;
        }
        if (empty($no_return)) return;
    }
    
    $div_fields = $PHORUM["phorum_mod_google_calendar"]["event_div_fields"];
    
    //update the year dropdown selections for the current year
    $curr_year = (int)date("Y",time());
    $year_options = ""; 
    for ($i=($curr_year - 10);$i<=($curr_year + 10);$i++) {
        $year_options .= "<option value=\"$i\"";
        if ($i == $curr_year) $year_options .= " selected=\"selected\"";
        $year_options .= ">$i</option>";
    }
    
    $div_fields["start_year"] = "<select id=\"google_calendar_start_year_id\" name=\"google_calendar_start_year\" onchange=\"google_calendar_check_day_value_by_year(this)\">$year_options</select>";
    $div_fields["end_year"] = "<select id=\"google_calendar_end_year_id\" name=\"google_calendar_end_year\" onchange=\"google_calendar_check_day_value_by_year(this)\">$year_options</select>";
    
    
    //build the selection for categories if needed
    if ($PHORUM["phorum_mod_google_calendar"]["calendar_type"] == "categories") {
        $div_fields["category"] = "<div style=\"padding: 5px;\">".$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["category_selection"].
        " <select name=\"google_calendar_category\">";
        foreach ($PHORUM["phorum_mod_google_calendar"]["calendars"]["categories"] as $cal_id => $cal_data) {
            if (!empty($cal_data["deleted"])) continue;
            $div_fields["category"] .= "<option value=\"$cal_id\" style=\"color: #FFFFFF; font-weight: bold; background-color: {$cal_data["color"]};\"";
            if (!empty($post_data["google_calendar_category"]) && !empty($post_data["google_calendar_add_event"]) 
                && $post_data["google_calendar_category"] == $cal_id) $div_fields["category"] .= " selected=\"selected\"";
            $div_fields["category"] .= ">{$cal_data["name"]}</option>";
        }
        $div_fields["category"] .= "</select></div>";
    }
    
    //if there is post data and the calendar info was filled in, let's prefill it
    $post_data = !empty($PHORUM["google_calendar_post_temp"]) ? $PHORUM["google_calendar_post_temp"] : NULL;
    if (!empty($post_data) && !empty($post_data["add_event"])) {
        foreach ($post_data as $name => $data) {
            if ($name == "where") {
                $PHORUM["DATA"]["google_calendar_post_temp"]["google_calendar_where"] = $data;
            } elseif ($name == "add_event") {
                $PHORUM["DATA"]["google_calendar_post_temp"]["google_calendar_add_event"] = "checked=\"checked\"";
            } else {
                if (strpos($name,"year")) $div_fields[$name] = str_replace("selected=\"selected\"","",$div_fields[$name]);
                if (!empty($div_fields[$name])) {
                    $div_fields[$name] = str_replace(" selected=\"selected\"","",$div_fields[$name]);
                    $div_fields[$name] = str_replace("value=\"$data\"","value=\"$data\" selected=\"selected\"",$div_fields[$name]);
                }
            }
        }
    } else {
        //update the month and day dropdowns for the current date (if no prefill data)
        $curr_month = (int)date("n",time());
        $div_fields["start_month"] = str_replace("value=\"$curr_month\"","value=\"$curr_month\" selected=\"selected\"",$div_fields["start_month"]);
        $div_fields["end_month"] = str_replace("value=\"$curr_month\"","value=\"$curr_month\" selected=\"selected\"",$div_fields["end_month"]);
        $curr_day= (int)date("j",time());
        $div_fields["start_day"] = str_replace("value=\"$curr_day\"","value=\"$curr_day\" selected=\"selected\"",$div_fields["start_day"]);
        $div_fields["end_day"] = str_replace("value=\"$curr_day\"","value=\"$curr_day\" selected=\"selected\"",$div_fields["end_day"]);
        
    }
    //format the date (ie. MM/DD/YYYY, DD/MM/YYYY, etc)
    $date_format = explode(",",$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["date_format"]);
    $div_fields["start_date"] = $div_fields["end_date"] = "";
    $i = 0;
    foreach ($date_format as $key) {
        $sep = $i != 0 ? " / " : "";
        $i++;
        if ($key == "m") {
            $div_fields["start_date"] .= $sep.$div_fields["start_month"];
            $div_fields["end_date"] .= $sep.$div_fields["end_month"];
        } elseif ($key == "d") {
            $div_fields["start_date"] .= $sep.$div_fields["start_day"];
            $div_fields["end_date"] .= $sep.$div_fields["end_day"];
        } elseif ($key == "y") {
            $div_fields["start_date"] .= $sep.$div_fields["start_year"];
            $div_fields["end_date"] .= $sep.$div_fields["end_year"];
        }
    }
    $PHORUM["DATA"]["phorum_mod_google_calendar_event_div_fields"] = array_merge($PHORUM["phorum_mod_google_calendar"]["event_div_fields"],$div_fields);

    include phorum_get_template("google_calendar::event_div");
}

//print"</div><pre>".htmlspecialchars(print_r($PHORUM,true))."</pre>";exit;
?>