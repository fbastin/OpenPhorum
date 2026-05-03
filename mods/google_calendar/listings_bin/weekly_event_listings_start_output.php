<?php

if(!defined("PHORUM")) return;

$calendar_type = $PHORUM["phorum_mod_google_calendar"]["calendar_type"];
//no need to continue if there are no active calendars or the admin doesn't want to show this listing
if (empty($PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type])
    || empty($PHORUM["phorum_mod_google_calendar"]["show_event_listing"])) return;

$calendars = $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type];

//print"</div><pre>".htmlspecialchars(print_r($PHORUM["phorum_mod_google_calendar"],true))."</pre>";exit;

//run the automatic timezone mod if enabled
if (!empty($PHORUM["hooks"]["common"]) && in_array("automatic_timezones",$PHORUM["hooks"]["common"]["mods"])) {
    phorum_mod_automatic_timezones_common();
}

//set some date/time variables
$date_format = (!empty($PHORUM["short_date_time"])) ? $PHORUM["short_date_time"] : $PHORUM["long_date_time"];
$us_date_format = "%m/%d/%Y %I:%M%p";
$time_format = $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["TimeFormat"];
$week_start = (empty($PHORUM["phorum_mod_google_calendar"]["week_start"])) ? 0 : 1;

//get the start and end times to check
$curr_time = strtotime(phorum_date($us_date_format,time()));
$start_min_ts = $curr_time - ((date("w",$curr_time) - $week_start) * 86400) - (date("G", $curr_time) * 3600) - (date("i",$curr_time) * 60) - (date("s",$curr_time));
$start_max_ts = $start_min_ts + 518400 + 86399;
$start_min = date("Y-m-d",$start_min_ts)."T".date("H:i:s",$start_min_ts)."";
$start_max = date("Y-m-d",$start_max_ts)."T".date("H:i:s",$start_max_ts)."";
$user_start_min_ts = strtotime($start_min);

//prepare to gather the events
$event_listings = array();
$js_event_data = array();
$num_full_day = array();
include_once("./mods/google_calendar/event_bin/get_event_functions.php");

//check each calendar for weekly events
foreach ($calendars as $cal_id => $cal_data) {
    //if a calendar has been deleted, do not gather its events
    if (!empty($cal_data["deleted"])) continue;
    //set a flag to indicate that empty or not there is at least one active calendar
    $calendars_to_show = 1;
    
    //grab the basic event data
    $temp_event_data = phorum_mod_google_calendar_get_weekly_events($cal_id, $cal_data["alternate_url"], $start_min, $start_max);
    
    //if no events were found, or there was an error gathering events, skip this calendar
    if (empty($temp_event_data) || !empty($temp_event_data["error"])) continue;
    
    //process the events for display
    foreach($temp_event_data as $key => $data) {
        //skip the event if there was an error
        if (!empty($data["error"])) continue;
        
        $gc_data = $data["message"]["meta"]["google_calendar"];
        
        //grab and format the start and end dates/times
        $formatted_start_datetime = phorum_date($date_format,strtotime($gc_data["StartTime"]));
        $formatted_end_datetime = phorum_date($date_format,strtotime($gc_data["EndTime"]));
        $us_formatted_start_datetime = phorum_date($us_date_format,strtotime($gc_data["StartTime"]));
        $us_formatted_end_datetime = phorum_date($us_date_format,strtotime($gc_data["EndTime"]));
        $start_time = strtotime($us_formatted_start_datetime);
        $end_time = strtotime($us_formatted_end_datetime);
        $formatted_start_time = phorum_date($time_format,strtotime($gc_data["StartTime"]));
        $formatted_end_time = phorum_date($time_format,strtotime($gc_data["EndTime"]));
        
        $curr_day = 0;
        //loop through each day to see if the current event occurs in that day
        for ($i=0;$i <= 518400; $i += 86400) {
            //event happens only in this day
            if ($start_time >= ($user_start_min_ts + $i) && $start_time <= ($user_start_min_ts + $i + 86340) && $end_time <= ($user_start_min_ts + $i + 86340)) {
                $event_data = array (
                    "title" => $data["title"],
                    "read_url" => $data["read_url"],
                    "start" => $formatted_start_datetime,
                    "end" => $formatted_end_datetime,
                    "color" => $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$cal_id]["color"],
                    "js_id" => "google_calendar_event_message_id_".$data["message"]["message_id"],
                    );
                // if the event is not a full day event, display the start time
                if ($end_time - $start_time != 86340)
                    $event_data["start_time"] = $formatted_start_time;
                $event_data["where"] = (!empty($gc_data["Where"])) ? preg_replace("/%where%/",$gc_data["Where"],$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["EventBubbleWhere"]) : ""; 
                $event_listings["in_day_temp"][$curr_day][$start_time][] = $event_data;
                //add event data for the javascript array
                $js_event_data[$data["message"]["message_id"]] = $event_data;
            //event starts before this day but ends in this day
            } elseif ($start_time < ($user_start_min_ts + $i) && $end_time > ($user_start_min_ts + $i) && $end_time < ($user_start_min_ts + $i + 86400)) {
                if (empty($num_full_day[$data["message"]["message_id"]])) {
                    $event_data = array (
                        "title" => $data["title"],
                        "read_url" => $data["read_url"],
                        "start" => $formatted_start_datetime,
                        "end" => $formatted_end_datetime,
                        "color" => $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$cal_id]["color"],
                        "js_id" => "google_calendar_event_message_id_".$data["message"]["message_id"],
                        );
                    $event_data["where"] = (!empty($gc_data["Where"])) ? preg_replace("/%where%/",$gc_data["Where"],$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["EventBubbleWhere"]) : ""; 
                    $event_listings["full_day_temp"][$data["message"]["message_id"]] = $event_data;
                    $js_event_data[$data["message"]["message_id"]] = $event_data;
                }
                //add the current day as one of the full days this event occurs
                $num_full_day[$data["message"]["message_id"]][] = $curr_day;
            //event starts in this day but ends after this day
            } elseif ($start_time >= ($user_start_min_ts + $i) && $start_time < ($user_start_min_ts + $i + 86400) && $end_time >= ($user_start_min_ts + $i + 86400)) {
                if (empty($num_full_day[$data["message"]["message_id"]])) {
                    $event_data = array (
                        "title" => $data["title"],
                        "read_url" => $data["read_url"],
                        "start" => $formatted_start_datetime,
                        "end" => $formatted_end_datetime,
                        "color" => $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$cal_id]["color"],
                        "js_id" => "google_calendar_event_message_id_".$data["message"]["message_id"],
                        );
                    $event_data["where"] = (!empty($gc_data["Where"])) ? preg_replace("/%where%/",$gc_data["Where"],$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["EventBubbleWhere"]) : ""; 
                    $event_listings["full_day_temp"][$data["message"]["message_id"]] = $event_data;
                    $js_event_data[$data["message"]["message_id"]] = $event_data;
                }
                $num_full_day[$data["message"]["message_id"]][] = $curr_day;
            //event starts before this day and ends after this day
            } elseif ($start_time < ($user_start_min_ts + $i) && $end_time >= ($user_start_min_ts + $i + 86400)) {
                if (empty($num_full_day[$data["message"]["message_id"]])) {
                    $event_data = array (
                        "title" => $data["title"],
                        "read_url" => $data["read_url"],
                        "start" => $formatted_start_datetime,
                        "end" => $formatted_end_datetime,
                        "color" => $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$cal_id]["color"],
                        "js_id" => "google_calendar_event_message_id_".$data["message"]["message_id"],
                        );
                    $event_data["where"] = (!empty($gc_data["Where"])) ? preg_replace("/%where%/",$gc_data["Where"],$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["EventBubbleWhere"]) : ""; 
                    $event_listings["full_day_temp"][$data["message"]["message_id"]] = $event_data;
                    $js_event_data[$data["message"]["message_id"]] = $event_data; 
                }
                $num_full_day[$data["message"]["message_id"]][] = $curr_day;
            }
            $curr_day ++;
        }
    }
}
// custom sort to keep full day events lining up properly across the week
// this sorts first by the number of days an event occurs, then by the message id
function full_day_sort($a, $b) {
    if (count($a) == count($b)) return 0;
    return (count($a) > count($b)) ? -1 : 1;
}
// run the custom sort above
uasort($num_full_day, "full_day_sort");

$total_row_count = 0;
$max_rows = (int)$PHORUM["phorum_mod_google_calendar"]["maximum_daily_events"] - 1;

$day_format = $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["event_listing_date_format"];
// loop through the days, sorting/gathering events and header info
$extra_row = 0;
for ($day = 0; $day <= 6; $day++) {
    $row = 0;
    // process the multi-day events
    if(!empty($event_listings["full_day_temp"])) {
        foreach ($num_full_day as $id => $num_data) {
            $temp_num_full_day = $num_full_day[$id];
            $last_day = end($temp_num_full_day);
            // if today is the first day of the multi-day event
            if (isset($num_full_day[$id][0]) && $num_full_day[$id][0] == $day) {
                // if a multi-day event has already filled the current row, 
                // increment.
                while (!empty($event_listings[$day]["rows"][$row])) $row++;
                // if we have not reached the maximum daily events threshold
                // add the event as a new row
                if ($row <= $max_rows) {
                    $event_data = $event_listings["full_day_temp"][$id];
                    // set the number of day columns to span
                    $event_data["colspan"] = count($num_full_day[$id]);
                    // set the event data for this event
                    $event_listings[$day]["rows"][] = $event_data;
                    // set the other multi-day cells for this row
                    for ($nfi = 1;$nfi < $event_data["colspan"]; $nfi++) {
                        $event_listings[$day+$nfi]["rows"][$row]["full_day_event"] = true;
                    }
                    // increase the row count for this day
                    $row ++;
                // if there are more events than allowed for a single day
                } else {
                    // flag that we have extra events in at least one day
                    $extra_events = 1;
                    // add the extra event data
                    $event_listings[$day]["extra_rows"][$extra_row] = $event_data;
                    // add the extra event data to each day of a multi-day event
                    for ($nfi = 1;$nfi < $event_data["colspan"]; $nfi++) {
                            $event_listings[$day+$nfi]["extra_rows"][$extra_row] = $event_data;
                    }
                    // increase the extra events row count
                    $extra_rows ++;
                }
            }
        }
        
    }
    
    // process the single day events
    if (!empty($event_listings["in_day_temp"][$day])) {
        // find the highest row for today, necessary for days in which
        // there are multi-day and empty rows, but no beginning multi-
        // day rows.
        if(!empty($event_listings[$day]["rows"])) end($event_listings[$day]["rows"]);
        //assign the next row number;
        $row = (!empty($event_listings[$day]["rows"])) ? key($event_listings[$day]["rows"]) + 1 : 1;
        // sort the single day events by starting time
        ksort($event_listings["in_day_temp"][$day]);
        foreach($event_listings["in_day_temp"][$day] as $start_time => $key) {
            foreach ($key as $i => $event_data) {
                // if we have not reached the maximum daily events threshold
                // add the event as a new row
                if ($row <= $max_rows) {
                    $event_listings[$day]["rows"][] = $event_data;
                    // find the highest row for today, necessary for days in which
                    // there are multi-day and empty rows, but no beginning multi-
                    // day rows.
                    end($event_listings[$day]["rows"]);
                    // increment the highest row count
                    $row = key($event_listings[$day]["rows"]) + 1;
                // if there are more events than allowed for a single day
                } else {
                    // flag that we have extra events in at least one day
                    $extra_events = 1;
                    // add the extra event data
                    $event_listings[$day]["extra_rows"][] = $event_data;
                }
            }
        }
    }
    
    $offset_day = $day + $week_start;
    // assign the event listing column headers
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listing_header"][] = 
        (!empty($PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["Days"][$offset_day]))
        ? $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["Days"][$offset_day]."&nbsp;(".date($day_format,$user_start_min_ts + ($day * 86400)).")"
        : date($day_format,$user_start_min_ts + ($day * 86400));
    // increase the highest row count for the week if necessary
    if ($row > $total_row_count) $total_row_count = $row;
}

// we are always one row too high if there are no extra events
if (empty($extra_events)) $total_row_count --;
// create a row loop for the template
for ($i = 0; $i <= $total_row_count; $i++) {
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["row_count"][$i] = $i;
}
// loop through the days and fill in empty rows in each column
for ($day = 0; $day <= 6; $day++) {
    // if there are no events today, create an empty row to match the other days
    if (empty($event_listings[$day])) {
        for ($i = 0; $i <= $total_row_count; $i++) {
            $event_listings[$day]["rows"][$i]["no_event"] = true;
        }
    // of if the number of rows is short of the highest row count for the week
    } elseif (count($event_listings[$day]["rows"]) <= $total_row_count) {
        // loop through the expected row numbers
        for ($i = 0; $i <= $total_row_count; $i++) {
            // if the row is not populated, flag it as empty
            if (empty($event_listings[$day]["rows"][$i])) $event_listings[$day]["rows"][$i]["no_event"] = true;
        }
    }
    // if we have more events for this day than the allowed amount
    if (!empty($event_listings[$day]["extra_rows"])) {
        // flag the last row as the extra row
        $event_listings[$day]["rows"][$total_row_count]["extra_rows"] = true;
        // remove the no_event flag
        $event_listings[$day]["rows"][$total_row_count]["no_event"] = false;
        // add the more events link with the count of extra events
        $event_listings[$day]["rows"][$total_row_count]["MoreEvents"] = preg_replace("/%count%/",count($event_listings[$day]["extra_rows"]),$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["MoreEvents"]);
        // set the js_id for the <td>
        $event_listings[$day]["rows"][$total_row_count]["js_id"] = "google_calendar_more_events_day_id_".$day."MED";
        // add the relevant javascript for each row 
        foreach ($event_listings[$day]["extra_rows"] as $row => $event_data) {
            $js_extra_event_data[$day][$row] = $event_data;
        }
    }
            
}

//unset the temp data
unset($event_listings["in_day_temp"]);
unset($event_listings["full_day_temp"]);
//sort the days
ksort($event_listings);
//pass data to the templates
$PHORUM["DATA"]["phorum_mod_google_calendar"]["total_row_count"] = $total_row_count;
//set the EventListing title for a weekly event listing
$PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["EventListing"] = $PHORUM["DATA"]["LANG"]["phorum_mod_google_calendar"]["WeeklyEventListing"];

//no need to continue if there were no active calendars
if (empty($calendars_to_show)) {
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["calendars_to_show"] = false;
    return;
} else {
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["calendars_to_show"] = true;
}

// send the event listings to the template
$PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listings"] = $event_listings;
$PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listings_js"] = (!empty($js_event_data)) ? $js_event_data : "";
$PHORUM["DATA"]["phorum_mod_google_calendar"]["event_listings_js_extra"] = (!empty($js_extra_event_data)) ? $js_extra_event_data : "";

//check for a cookie in case the user has hidden the event listing div
if (!empty($_COOKIE["phorum_mod_google_calendar_hide_event_listing"])) {
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["toggle_event_listing_div"] = "block";
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["toggle_event_listing_div_opposite"] = "none";
} else {
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["toggle_event_listing_div"] = "none";
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["toggle_event_listing_div_opposite"] = "block";
}

//set the embedded calendar link if there is an embedded calendar
if (!empty($PHORUM["phorum_mod_google_calendar"]["google_embed_code"]))
    $PHORUM["DATA"]["phorum_mod_google_calendar"]["show_embedded_calendar"] = true;

?>
