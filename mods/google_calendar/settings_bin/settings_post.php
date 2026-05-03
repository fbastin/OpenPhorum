<?php

if(!defined("PHORUM_ADMIN")) return;

$current_post_fields = Array();
$new_post_fields = Array();
$deleted_post_fields = Array();
$other_post_fields = Array();
$errors = Array();

// group the post fields
foreach ($_POST as $field => $data) {
    if (strval(intval(substr($field,strrpos($field,"_")+1,4))) == substr($field,strrpos($field,"_")+1,4)) $post_id = (int)substr($field,strrpos($field,"_")+1,4);
    //group the current calendar data
    if (strpos($field,"current_") !== false) {
        if (strpos($field,"calendar_name") !== false) {
            $current_post_fields[$post_id]["name"] = $data;
        } elseif (strpos($field,"calendar_color") !== false) {
            $current_post_fields[$post_id]["color"] = $data;
        } elseif (strpos($field,"calendar_delete") !== false) {
            $current_post_fields[$post_id]["deleted"] = !empty($data) ? 1: 0;
        } elseif (strpos($field,"calendar_id") !== false) {
            $current_post_fields[$post_id]["calendar_id"] = $data;
        }
    
    //group the new calendar data
    } elseif (strpos($field,"new_") !== false) {
        if (strpos($field,"calendar_name") !== false) {
            $new_post_fields[$post_id]["name"] = $data;
        } elseif (strpos($field,"calendar_color") !== false) {
            $new_post_fields[$post_id]["color"] = $data;
        } elseif (strpos($field,"calendar_delete") !== false) {
            $new_post_fields[$post_id]["deleted"] = !empty($data) ? 1: 0;
        } elseif (strpos($field,"calendar_id") !== false) {
            $new_post_fields[$post_id]["calendar_id"] = $data;
        }
    
    //group the deleted calendar data
    } elseif (strpos($field,"deleted_") !== false) {
        if (strpos($field,"calendar_name") !== false) {
            $deleted_post_fields[$post_id]["name"] = $data;
        } elseif (strpos($field,"calendar_color") !== false) {
            $deleted_post_fields[$post_id]["color"] = $data;
        } elseif (strpos($field,"calendar_id") !== false) {
            $deleted_post_fields[$post_id]["calendar_id"] = $data;
        } elseif (strpos($field,"restore") !== false) {
            $deleted_post_fields[$post_id]["restore"] = !empty($data) ? 1: 0;
        } elseif (strpos($field,"fully_delete") !== false) {
            $deleted_post_fields[$post_id]["fully_delete"] = !empty($data) ? 1: 0;
        }
    //group the other post data
    } else {
        $other_post_fields[$field] = $data;
    }
}
$calendar_type = $other_post_fields["calendar_type"];

//set the unique category id if needed
if ($calendar_type == "categories" && empty($PHORUM["phorum_mod_google_calendar"]["unique_category_id"])) {
    $PHORUM["phorum_mod_google_calendar"]["unique_category_id"] = 0;
}

//include the needed google functions
include_once("./mods/google_calendar/settings_bin/gdata_functions.php");

// process the current data group
if (!empty($current_post_fields))
    include_once("./mods/google_calendar/calendar_bin/edit_calendar_functions.php");
foreach ($current_post_fields as $post_id => $data) {
    if (empty($data["name"])) {
        continue;
    } else {
        $stored_data = $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]];
        if ($stored_data["name"] != $data["name"] 
            || $stored_data["color"] != $data["color"]
            || !empty($data["deleted"])) 
            $data = phorum_mod_google_calendar_edit_calendar($calendar_type, $data);
        if (!empty($data["error"])) {
            $errors[] = $data;
        } else {
            $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]] =
                array_merge($PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]],$data);
        }
    }
}

//process the new data group
if (!empty($new_post_fields))
    include_once("./mods/google_calendar/calendar_bin/new_calendar_functions.php");
foreach ($new_post_fields as $post_id => $data) {
    if (empty($data["name"])) {
        continue;
    } else {
        if ($calendar_type == "categories") {
            $data["calendar_id"] = $PHORUM["phorum_mod_google_calendar"]["unique_category_id"];
            $PHORUM["phorum_mod_google_calendar"]["unique_category_id"] ++;
        }
        $data = phorum_mod_google_calendar_create_new_calendar($data);
        if (!empty($data["error"])) {
            $errors[] = $data;
        } else {
            $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]] = $data;
        }
    }
}

//process the deleted data group
if (!empty($deleted_post_fields))
    include_once("./mods/google_calendar/calendar_bin/deleted_calendar_functions.php");
foreach ($deleted_post_fields as $post_id => $data) {
    if (!empty($data["fully_delete"]) && $data["fully_delete"] == 1) {
        $data = phorum_mod_google_calendar_delete_calendar ($calendar_type, $data);
        if (!empty($data["error"])) {
            $errors[] = $data;
        } else {
            unset($PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]]);
        }
    } elseif (!empty($data["restore"]) && $data["restore"] == 1) {
        $data = phorum_mod_google_calendar_restore_calendar ($calendar_type, $data);
        if (!empty($data["error"])) {
            $errors[] = $data;
        } else {
            $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$data["calendar_id"]]["deleted"] = 0;
        }
    }
}

// special processing if changing from no event listing to showing one
// if so, we want to show it automatically
if (!empty($_POST["show_event_listing"]) && empty($PHORUM["phorum_mod_google_calendar"]["show_event_listing"]))
    $_POST["auto_event_listing"] = 1;

//process the other post data
foreach ($other_post_fields as $field => $data) {
    $PHORUM["phorum_mod_google_calendar"][$field] = $data;
}
//process the preset checkboxes, but only if the relevant form was submitted
if (empty($deleted_post_fields)) {
$PHORUM["phorum_mod_google_calendar"]["show_event_in_message"] = empty($_POST["show_event_in_message"]) ? 0 : 1;
$PHORUM["phorum_mod_google_calendar"]["week_start"] = empty($_POST["week_start"]) ? 0 : 1;
$PHORUM["phorum_mod_google_calendar"]["auto_event_listing"] = empty($_POST["auto_event_listing"]) ? 0 : 1;
}

//if "Any forum/folder" was selected from the list, that change the "events_from_any_forun" setting
if (empty($PHORUM["phorum_mod_google_calendar"]["events_from_selected_forums"][0])) $PHORUM["phorum_mod_google_calendar"]["events_from_any_forum"] = 0;

//sort the categories by name
if ($calendar_type == "categories") {
    $by_name = array();
    foreach ($PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type] as $cal_id => $cal_data) {
        $by_name[$cal_data["name"]] = $cal_id;
    }
    ksort($by_name);
    foreach ($by_name as $cal_name => $cal_id) {
        $calendars[$cal_id] = $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type][$cal_id];
    }
    $PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type] = $calendars;
}

//update the settings
phorum_db_update_settings(array("phorum_mod_google_calendar"=>$PHORUM["phorum_mod_google_calendar"]));
if (!empty($errors)) {
    $error_string = "Settings Updated with Errors";
    foreach ($errors as $data) {
        $error_string .= "<br/>".$data["name"].": ".$data["error"];
    }
    phorum_admin_error($error_string);
} else {
    phorum_admin_okmsg("Settings Updated");
}

?>
