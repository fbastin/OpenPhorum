<?php
    
if(!defined("PHORUM_ADMIN")) return;

global $PHORUM;

if (empty($PHORUM["admin_token"])) $PHORUM["admin_token"] = "";

/*      For Dev Only          */
//  unset($PHORUM["phorum_mod_google_calendar"]);
//  phorum_db_update_settings(array("phorum_mod_google_calendar"=>$PHORUM["phorum_mod_google_calendar"]));
//  unset ($PHORUM["phorum_mod_google_calendar_cache"]);
//  phorum_db_update_settings(array("phorum_mod_google_calendar_cache"=>$PHORUM["phorum_mod_google_calendar_cache"]));
/*    End Dev Only          */

// Process the Google token if provided
if (!empty($_REQUEST["token"])) {
    // Get the functions to interact with Google
    include_once("./mods/google_calendar/settings_bin/gdata_functions.php");
    
    $token = $_REQUEST["token"];
    $PHORUM["phorum_mod_google_calendar"]["SessionToken"] = phorum_mod_google_calendar_gdata_upgrade_token($token);   
    phorum_db_update_settings(array("phorum_mod_google_calendar"=>$PHORUM["phorum_mod_google_calendar"]));
}

// Process calendar type change if provided
if (!empty($_REQUEST["cal_type"])) {
    $PHORUM["phorum_mod_google_calendar"]["calendar_type"] = $_REQUEST["cal_type"];
    phorum_db_update_settings(array("phorum_mod_google_calendar"=>$PHORUM["phorum_mod_google_calendar"]));
}

//save settings
if (!empty($_POST)) {
    include_once("./mods/google_calendar/settings_bin/settings_post.php");
    // Clear event listing cache data
    if (!empty($PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"])
        || !empty($PHORUM["phorum_mod_google_calendar_cache"]["seven_day_events_cache"])) {
        unset ($PHORUM["phorum_mod_google_calendar_cache"]["weekly_events_cache"]);
        unset ($PHORUM["phorum_mod_google_calendar_cache"]["seven_day_events_cache"]);
        phorum_db_update_settings(array("phorum_mod_google_calendar_cache"=>$PHORUM["phorum_mod_google_calendar_cache"]));
    }
}

// Set defaults
if (empty($PHORUM["phorum_mod_google_calendar"]["calendar_type"])) {
    $PHORUM["phorum_mod_google_calendar"]["calendar_type"] = "forums";
    $PHORUM["phorum_mod_google_calendar"]["show_event_in_message"] = 1;
    $PHORUM["phorum_mod_google_calendar"]["auto_event_listing"] = 1;
    $PHORUM["phorum_mod_google_calendar"]["event_listing_pages"]["index"] = 1;
    $PHORUM["phorum_mod_google_calendar"]["event_listing_pages"]["list"] = 1;
    $PHORUM["phorum_mod_google_calendar"]["maximum_daily_events"] = 5;
    include_once("./mods/google_calendar/settings_bin/event_div_fields.php");
    phorum_db_update_settings(array("phorum_mod_google_calendar"=>$PHORUM["phorum_mod_google_calendar"]));
}

// If the admin has not provided Google Calendar authentication, prompt for it.
if (empty($PHORUM["phorum_mod_google_calendar"]["SessionToken"])) {
    phorum_admin_error("You have not yet provided this module with authentication for your Google calendar account. "
        ."<a href=\"https://www.google.com/accounts/AuthSubRequest?next="
        .rawurlencode($PHORUM["admin_http_path"]."?module=modsettings&mod=google_calendar&phorum_admin_token=".$PHORUM["admin_token"])
        ."&scope=http%3A%2F%2Fwww.google.com%2Fcalendar%2Ffeeds&session=1\" style=\"color:#FF0000;\">Authenticate Now</a>");
}

//establish variables
$possible_pages = array (
    "index",    "read",     "moderation",
    "addon",    "control",  "subscribe",
    "login",    "list",     "pm",
    "post",     "profile",  "register",
    "report",   "search",
    );
asort($possible_pages);
$possible_calendar_colors = array(
    "REMOVE"  => "Remove",
    "#0D7813" => "#0D7813", "#1B887A" => "#1B887A", "#29527A" => "#29527A",
    "#2952A3" => "#2952A3", "#28754E" => "#28754E", "#4A716C" => "#4A716C",
    "#4E5D6C" => "#4E5D6C", "#5229A3" => "#5229A3", "#528800" => "#528800",
    "#5A6986" => "#5A6986", "#6E6E41" => "#6E6E41", "#705770" => "#705770",
    "#7A367A" => "#7A367A", "#865A5A" => "#865A5A", "#88880E" => "#88880E",
    "#8D6F47" => "#8D6F47", "#A32929" => "#A32929", "#AB8B00" => "#AB8B00",
    "#B1365F" => "#B1365F", "#B1440E" => "#B1440E", "#BE6D00" => "#BE6D00"
    );
$calendar_colors = array(
     "#A32929", "#B1365F", "#7A367A", "#5229A3", "#29527A", "#2952A3", "#1B887A", 
     "#28754E", "#0D7813", "#528800", "#88880E", "#AB8B00", "#BE6D00", "#B1440E", 
     "#865A5A", "#705770", "#4E5D6C", "#5A6986", "#4A716C", "#6E6E41", "#8D6F47"
     );
$calendar_types = array(
    "forums"     => "By Forums",
    "folders"    => "By Folders",
    "categories" => "By Categories",
    );
$type_indexes = array(
    "forums"     => 0,
    "folders"    => 1,
    "categories" => 2,
    );
$type_names = array(
    "forums"     => "Forum",
    "folders"    => "Folder",
    "categories" => "Category",
    );
$calendar_type = $PHORUM["phorum_mod_google_calendar"]["calendar_type"];

//group calendars into active and deleted
$calendars = array();
$deleted_calendars = array();
if (!empty($PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type])) {
    foreach ($PHORUM["phorum_mod_google_calendar"]["calendars"][$calendar_type] as $cal_id => $cal_data) {
        if (empty($cal_data["deleted"])) {
            $calendars[$cal_id] = $cal_data;
        } else {
            $deleted_calendars[$cal_id] = $cal_data;
        }
    }
}

//get a list of folders and forums
include_once("./mods/google_calendar/settings_bin/forum_functions.php");
$forums = phorum_mod_google_calendar_get_forums();

//setup the javascript
$content = "<script type=\"text/javascript\">\n".
    "var phorum_admin_token = \"".$PHORUM["admin_token"]."\";\n".
    "var full_help_doc_url = \"".$PHORUM["admin_http_path"]."?module=modsettings&mod=google_calendar&show_docs=1&phorum_admin_token=".$PHORUM["admin_token"]."\";\n".
    "var calendar_type_index = {$type_indexes[$calendar_type]};\n".
    "var global_type_name = \"".strtolower($type_names[$calendar_type])."\";\n";   
    $js_row_count = 8;
    if (!empty($PHORUM["phorum_mod_google_calendar"]["show_event_listing"])) {
        $js_row_count += 4;
    }
    if (!empty($PHORUM["phorum_mod_google_calendar"]["post_event_permission"])
        && ($PHORUM["phorum_mod_google_calendar"]["post_event_permission"] == 3
            || $PHORUM["phorum_mod_google_calendar"]["post_event_permission"] == 4)) {
        $phorum_groups = phorum_db_get_groups(0,TRUE);
        if (empty($phorum_groups)) $phorum_groups = "no_groups";
        $js_row_count +=1;
    }
    $content .= "var global_fixed_row_count = $js_row_count;\n";
    if (!empty($forums)) {
        $content .= "var forum_ids_by_name = new Array ();\n";
        foreach ($forums as $forum_id => $forum_data) {
            $content .= "forum_ids_by_name[\"{$forum_data["name"]}\"] = $forum_id;";
        }
    }
    $content .= file_get_contents("./mods/google_calendar/settings_bin/settings.js");
    $content .= "</script>\n";

// Create the color chooser div
$content .= "<div id='colordiv' style='display: none; position: absolute; background-color: #EEEEFA; padding: 2px; border: 1px solid Navy;'>".
    "<div style='padding: 2px;'>";
    $ci = 0;
    foreach ($calendar_colors as $color) {
        $content .= "<div id='$color' class='color-choice' style='background-color: $color;' onclick='choose_color(this)' onmouseover='this.style.border=\"solid 1px #000000\"' onmouseout='this.style.border=\"solid 1px #FFFFFF\"'>".
        "<img src='./images/trans.gif' alt='' border='0' width='10px' height='10px' /></div>";
        $ci++;
        if ($ci == 7 || $ci == 14) {
            $content .= "</div><div style='padding: 2px 0px 2px 2px;'>";
        }
    }
    $content .= "</div></div>";


// Create custom style definitions
$content .= "<style>\n.edit-link {font-weight: normal;font-size: 10px; cursor: pointer; display: inline; color: Navy; text-decoration: underline;}\n".
".color-choice {margin-top: 2px; margin-left: 2px; cursor: pointer; border: 1px solid #FFFFFF; display: inline;}\n".
    "</style>";

// show the full help docs link
$content .= "<a target=\"_blank\" href=\"http://trac.phorum.org/wiki/mods/google_calendar/installation_and_usage_guide\">Installation and Usage Guide</a>";
//Start the form table
$content .= "<form id='field_form' onSubmit='return confirm_submit()' action='./admin.php?module=modsettings&mod=google_calendar&phorum_admin_token=".$PHORUM["admin_token"]."' method='post'>\n".
    "<input type='hidden' name='phorum_admin_token' value='".$PHORUM["admin_token"]."' />\n".
    "<table border='0' cellspacing='2' cellpadding='2' class='input-form-table' width='100%' id='maintable'>\n".
    "<tr class='input-form-tr'><td colspan='3' class='input-form-td-break'>Google Calendar Settings</td></tr>\n".
    "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>How do you want to color-code calendar events:".
    "<a href='javascript:show_help(1);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th>\n".
	"<td colspan='2' class='input-form-td'><select id='calendar_type_id' name='calendar_type' onchange='submit_calendar_type(this)'";
    if (!empty($calendars)) $content .= " disabled='disabled'";
    $content .= ">\n";
    foreach ($calendar_types as $type => $description) {
        $content .= "<option value='$type'";
        if ($type == $calendar_type) $content .= " selected='selected'";
        $content .= ">$description</option>\n";
    }
    $content .= "</select>&nbsp;<div id='unlock_calendar_type_div' class='edit-link' onclick=\"unlock_calendar_type(this)\"";
    if (empty($calendars)) $content .= "style='display: none;'";
    $content .= ">Unlock</div><input type='hidden' id='hidden_calendar_type_id' name='calendar_type' value='$calendar_type'";
    if (empty($calendars)) $content .= " disabled='disabled'";
    $content .= "></td></tr>\n".
    "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>Who can post events:</th>\n".
	"<td colspan='2' class='input-form-td'><select name='post_event_permission'>\n".
    "<option value='0'";
    $selected_permission = (empty($PHORUM["phorum_mod_google_calendar"]["post_event_permission"])) ? 0 : $PHORUM["phorum_mod_google_calendar"]["post_event_permission"];
    if (empty($selected_permission)) $content .= " selected='selected'";
    $content .= ">Anyone who can create a new topic</option>\n".
    "<option value='1'";
    if ($selected_permission == 1) $content .= " selected='selected'";
    $content .= ">Moderators and Administrators</option>\n".
    "<option value='3'";
    if ($selected_permission == 3) $content .= " selected='selected'";
    $content .= ">Selected Groups, Moderators, and Administrators</option>\n".
    "<option value='4'";
    if ($selected_permission == 4) $content .= " selected='selected'";
    $content .= ">Selected Groups and Administrators</option>\n".
    "<option value='2'";
    if ($selected_permission == 2) $content .= " selected='selected'";
    $content .= ">Administrators only</option>\n".
    "</select></td></tr>";
    if (!empty($phorum_groups)) {
        $content .= "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>Which groups can post events:".
        "<a href='javascript:show_help(12);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th>\n".
        "<td colspan='2' class='input-form-td'";
        if ($phorum_groups == "no_groups") {
            $content .= " style='color: DarkRed; font-weight: bold;'>Please create a group to use this feature.";
        } else {
            $content .= "><select name='event_posting_groups[]' multiple='multiple' size='3'>";
            foreach ($phorum_groups as $group_id => $group_data) {
                $content .= "<option value=$group_id";
                if (!empty($PHORUM["phorum_mod_google_calendar"]["event_posting_groups"])
                    && array_search($group_id, $PHORUM["phorum_mod_google_calendar"]["event_posting_groups"]) !== FALSE)
                    $content .= " selected='selected'";
                $content .= ">$group_data[name]</option>";
            }
            $content .= "</select>";
        }
        $content .= "</td></tr>";
    }
    $content .= "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>".
    "Google's HTML code for embedding your calendar:".
    "<a href='javascript:show_help(2);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th>".
    "<td colspan='2' class='input-form-td'><textarea name='google_embed_code' cols='50'>";
    if (!empty($PHORUM["phorum_mod_google_calendar"]["google_embed_code"]))
        $content .= $PHORUM["phorum_mod_google_calendar"]["google_embed_code"];
    $content .= "</textarea></td></tr>".
    "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>Show event data in the message body:".
    "<a href='javascript:show_help(3);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th>\n".
	"<td colspan='2' class='input-form-td'><input type='checkbox' name='show_event_in_message'";
    if (!empty($PHORUM["phorum_mod_google_calendar"]["show_event_in_message"])) $content .= " checked='checked'";
    $content .= "></td></tr>".
    "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>Show a seven day event listing on the page:".
    "<a href='javascript:show_help(4);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th>\n".
	"<td colspan='2' class='input-form-td'><select name='show_event_listing'>\n".
    "<option value='0'";
    $selected_header = (empty($PHORUM["phorum_mod_google_calendar"]["show_event_listing"])) ? 0 : $PHORUM["phorum_mod_google_calendar"]["show_event_listing"];
    if (empty($selected_header)) $content .= " selected='selected'";
    $content .= ">Do not show an event listing.</option>\n".
    "<option value='1'";
    if ($selected_header == 1) $content .= " selected='selected'";
    $content .= ">Show a listing of the current week's events.</option>\n".
    "<option value='2'";
    if ($selected_header == 2) $content .= " selected='selected'";
    $content .= ">Show a listing of the next seven days' events.</option>\n".
    "</select></td></tr>";
    if (!empty($PHORUM["phorum_mod_google_calendar"]["show_event_listing"])) {
        $content .= "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>Show the event listing automatically:".
        "<a href='javascript:show_help(5);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th>\n".
        "<td colspan='2' class='input-form-td'><input type='checkbox' name='auto_event_listing'";
        if (!empty($PHORUM["phorum_mod_google_calendar"]["auto_event_listing"])) $content .= " checked='checked'";
        $content .= "></td></tr>".
        "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>Pages on which to show the event listing:</th>\n".
        "<td colspan='2' class='input-form-td'><table><tr>";
        $p = $pp = 0;
        $pcount = count($possible_pages);
        foreach ($possible_pages as $page) {
            $content .= "<td><input type='checkbox' name='event_listing_pages[$page]'";
            if (!empty($PHORUM["phorum_mod_google_calendar"]["event_listing_pages"][$page])) $content .= " checked='checked'";
            $content .= ">".ucfirst($page)."</td>";
            if ($pp != $pcount) {
                if ($p == 3) {
                    $content .= "</tr>";
                    $p = 0;
                } else {
                    $p ++;
                }
            }
        }
        $content .= "</tr></table></td></tr>".
        "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>Maximum number of events to show in a single day:".
        "<a href='javascript:show_help(6);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th>\n".
        "<td colspan='2' class='input-form-td'><input type='text' name='maximum_daily_events'";
        if (!empty($PHORUM["phorum_mod_google_calendar"]["maximum_daily_events"])) $content .= " value='{$PHORUM["phorum_mod_google_calendar"]["maximum_daily_events"]}'";
        $content .= "></td></tr>";
        if ($PHORUM["phorum_mod_google_calendar"]["show_event_listing"] == 1) {
            $content .= "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>The week starts on Monday:</th>\n".
            "<td colspan='2' class='input-form-td'><input type='checkbox' name='week_start'";
            if (!empty($PHORUM["phorum_mod_google_calendar"]["week_start"])) $content .= " checked='checked'";
            $content .= "></td></tr>";
        }
    }
    if ($calendar_type == "categories") {
        $content .= "<tr class='input-form-tr'><th valign='middle' align='left' class='input-form-th'>".
        "<div id='events_from_any_forum_question'";
        if (!empty($PHORUM["phorum_mod_google_calendar"]["events_from_any_forum"])) $content .= " style='display: none;'";
        $content .= ">Allow posting events from any forum/folder:</div>\n".
        "<div id='events_from_selected_forums_question'";
        if (empty($PHORUM["phorum_mod_google_calendar"]["events_from_any_forum"])) $content .= " style='display: none;'";
        $content .=">Allow posting events only from the selected forum(s)/folder(s):</div></th>\n".
        "<td colspan='2' class='input-form-td'>".
        "<div id='events_from_any_forum_answer'";
        if (!empty($PHORUM["phorum_mod_google_calendar"]["events_from_any_forum"])) $content .= " style='display: none;'";
        $content .= "><select id='events_from_any_forum_id' name='events_from_any_forum' onchange='toggle_events_from_any_forum(this)'>\n".
        "<option value='0'";
        if (empty($PHORUM["phorum_mod_google_calendar"]["events_from_any_forum"])) $content .= " selected='selected'";
        $content .= ">Yes</option>\n".
        "<option value='1'";
        if (!empty($PHORUM["phorum_mod_google_calendar"]["events_from_any_forum"])) $content .= " selected='selected'";
        $content .= ">No</option>\n".
        "</select></div>\n".
        "<div id='events_from_selected_forums_answer'";
        if (empty($PHORUM["phorum_mod_google_calendar"]["events_from_any_forum"])) $content .= " style='display: none;'";
        $content .="><select name='events_from_selected_forums[]' multiple='multiple'>\n".
        "<option value=\"\">Any forum/folder</option>\n";
        if (!empty($PHORUM["phorum_mod_google_calendar"]["events_from_selected_forums"])) $selected_forums = $PHORUM["phorum_mod_google_calendar"]["events_from_selected_forums"];
        foreach ($forums as $forum_id => $forum_data) {
            if (!empty($forum_data["folder_flag"])) {
                $content .= "<option value=\"{$forum_id}\" style=\"font-weight: bold; font-style:italic;\"";
                if (!empty($selected_forums) && in_array($forum_id, $selected_forums)) $content .= " selected='selected'";
                $content .= ">".$forum_data["name"]."</option>";
            } else {
                $content .= "<option value=\"{$forum_id}\"";
                if (!empty($selected_forums) && in_array($forum_id, $selected_forums)) $content .= " selected='selected'";
                $content .= ">".$forum_data["name"]."</option>";
            }
        }
        $content .= "</select></div></td></tr>";
    }
    $content .= "<tr class='input-form-tr'><td colspan='3' style='font-weight: bold; font-size: 14px;' height='28px' class='input-form-td-subbreak'>Color Codings {$calendar_types[$calendar_type]}</td></tr>\n".
    "<tr class='input-form-tr'><td width='60%' align='center' class='input-form-td'><b>{$type_names[$calendar_type]} Name</b>".
    "<!--a href='javascript:show_help(7);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a--></td>\n".
	"<th align='center' width='20%' class='input-form-th'>Color".
    "<a href='javascript:show_help(8);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th>\n".
	"<td align='center' width='20%' class='input-form-td'><b>Delete</b>".
    "<a href='javascript:show_help(9);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></td></tr>\n";
	

//Display each calendar as a row.
if (!empty($calendars)) {
    $i=0;
    foreach ($calendars as $cal_id => $cal_data) {
        $content .= "<tr id=$i><td class='input-form-td' width='60%'>".
        "<input type='hidden' id='calendar_id_id_$i' name='current_calendar_id_$i' value='$cal_id' />\n".
        "<div id='calendar_name_display_$i' style='font-weight: bold;'>{$cal_data["name"]}\n".
        "<input type='hidden' name='current_calendar_name_$i' value=\"{$cal_data["name"]}\" />&nbsp;<div class='edit-link' onclick=\"enable_edit(this,$i,'name')\">Edit</div></div>\n";
        if ($calendar_type == "categories") {
            $content .= "<div id='calendar_name_edit_$i' style='display: none;'><input type='text' id='calendar_name_$i' name='current_calendar_name_$i' value='{$cal_data["name"]}' onchange='save_onchange_data(this)' /></div>";
        } else {
            $content .="<div id='calendar_name_edit_$i' style='display: none;'><select id='calendar_name_$i' name='current_calendar_name_$i' onchange='save_onchange_data(this)' />".
            "<option value=''> </option>";
            foreach ($forums as $forum_id => $forum_data) {
                if (!empty($forum_data["folder_flag"])) {
                    if ($calendar_type == "forums") {
                        $content .= "<optgroup label=\"".$forum_data["name"]."\">";
                    } else {
                        $content .= "<option value=\"{$forum_data["name"]}\"";
                        if ($forum_data["name"] == $cal_data["name"]) $content .= " selected='selected'";
                        $content .= ">".$forum_data["name"]."</option>";
                    }
                } else {
                    if ($calendar_type == "forums") {
                        $content .= "<option value=\"{$forum_data["name"]}\"";
                        if ($forum_data["name"] == $cal_data["name"]) $content .= " selected='selected'";
                        $content .= ">".$forum_data["name"]."</option>";
                    }
                }
            }
            $content .= "</div>";
        }
        $content .= "</td>\n".
        "<th align='center' class='input-form-th' width='20%'>\n".
        "<div class='edit-link' onclick=\"enable_edit(this,$i,'color')\" id='calendar_color_display_$i' style='text-align: center; font-weight: bold; background-color: {$cal_data["color"]};'><input type='hidden' id='hidden_calendar_color_$i' name='current_calendar_color_$i' value='{$cal_data["color"]}' />\n".
        "<img src='./images/trans.gif' alt='{$cal_data["color"]}' border='0' width='40px' height='13px' /></div></th>\n".
        "<td align='center' class='input-form-td' width='20%'><input type='checkbox' name='current_calendar_delete_$i' onchange='flag_unsaved_changes()' />\n".
        "</td></tr>\n";
        $i++;
        }
} else {
    $i=0;
}
	
//display the current calendars footer.
$content .= "<tr><th colspan='3' align='left' class='input-form-th' style='padding: 4px'>\n".
    "<input type='button' value='Add New {$type_names[$calendar_type]}' class='input-form-submit' onclick='add_new_calendar(this,$i)'";

    // if we have not gotten an authentication token, do not allow new calendars to be created
    if (empty($PHORUM["phorum_mod_google_calendar"]["SessionToken"])) {
        $content .= " disabled='disabled'> <span style=\"color: DarkRed;\">You may not create new color codings until you have provided authentication.</span> "
            ."<a href=\"https://www.google.com/accounts/AuthSubRequest?next=".rawurlencode($PHORUM["admin_http_path"]."?module=modsettings&mod=google_calendar&phorum_admin_token=".$PHORUM["admin_token"])
            ."&scope=http%3A%2F%2Fwww.google.com%2Fcalendar%2Ffeeds&session=1\" style=\"color: DarkRed;\">Authenticate Now</a>";
    } else {
        $content .= ">";
    }
    $content .= "</th></tr>\n".
    "<tr class='input-form-td'>\n<td class='input-form-td-break' colspan='6' align='center'>\n".
    "<input type='submit' value='Submit' class='input-form-submit'></td></tr></table></form>\n";


// setup new blank row
$content .= "<div id='hidden_new_row_a' style='display: none;'><table border='0' cellSpacing='0' cellPadding='0' class='input-form-table' width='100%'><tr>\n".
	"<td id='hidden_new_col_1' class='input-form-td' width='60%'><input type='hidden' id='calendar_id_id_new_row_num' name='new_calendar_id_new_row_num' value='' />";
	if ($calendar_type == "categories") {
        $content .= "<input type='text' id='calendar_name_new_row_num' name='new_calendar_name_new_row_num' value='' onchange='save_onchange_data(this)' />";
	} else {
        $content .= "<select id='calendar_name_new_row_num' name='new_calendar_name_new_row_num' onchange='save_onchange_data(this)' />".
        "<option value=''> </option>";
        foreach ($forums as $forum_id => $forum_data) {
            if (!empty($forum_data["folder_flag"])) {
                if ($calendar_type == "forums") {
                    $content .= "<optgroup label=\"".$forum_data["name"]."\">";
                } else {
                    $content .= "<option value=\"{$forum_data["name"]}\">".$forum_data["name"]."</option>";
                }
            } else {
                if ($calendar_type == "forums") {
                    $content .= "<option value=\"{$forum_data["name"]}\">".$forum_data["name"]."</option>";
                }
            }
        }
        $content .= "</select>";
    }
    $content .= "</td>\n".
    "<th id='hidden_new_col_2' align='center' class='input-form-th' width='20%'>\n".
	"<div class='edit-link' onclick=\"enable_edit(this,new_row_num,'color')\" id='calendar_color_display_new_row_num' style='text-align: center; font-weight: bold; background-color: #A32929;'><input type='hidden' id='hidden_calendar_color_new_row_num' name='new_calendar_color_new_row_num' value='#A32929' />\n".
    "<img src='./images/trans.gif' alt='' border='0' width='40px' height='13px' /></div></th>\n".
    "<td id='hidden_new_col_3' align='center' class='input-form-td' width='20%'>&nbsp;</td></tr></table></div>\n";
	


//show deleted calendars
if (!empty($deleted_calendars)) {
    $content .= "<form id='deleted_field_form' onSubmit='return deleted_confirm_submit()' action='./admin.php?module=modsettings&mod=google_calendar&phorum_admin_token=".$PHORUM["admin_token"]."' method='post'>\n".
	"<input type='hidden' name='calendar_type' value='$calendar_type' />\n".
  "<input type='hidden' name='phorum_admin_token' value='".$PHORUM["admin_token"]."' />\n".
    "<table border='0' cellSpacing='2' cellPadding='2' class='input-form-table' width='100%' id='deleted_maintable'>\n".
	"<tr class='input-form-tr'><td colspan='4' class='input-form-td-break'>Deleted ".ucfirst($type_names[$calendar_type])." Color Codings</td></tr>\n".
	"<tr class='input-form-tr'><td align='center' class='input-form-td' width='50%'><b>Field Name</b></td>\n".
	"<th align='center' class='input-form-th' width='20%'>Color</th>\n".
	"<td align='center' class='input-form-td' width='15%'><b>Restore</b>".
    "<a href='javascript:show_help(10);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></td>\n".
	"<th align='center' class='input-form-th' width='15%'>Fully Delete".
    "<a href='javascript:show_help(11);'><img class='question' alt='Help' title='Help' border='0' src='./images/qmark.gif' height='16' width='16' /></a></th></tr>\n";

//display each calendar as a row
    $d = 0;
    foreach ($deleted_calendars as $cal_id => $cal_data) {
        $content .= "<tr id=$d class='input-form-tr'>\n".
        "<td align='left' class='input-form-td' width='40%'><b>$cal_data[name]</b>\n".
        "<input type='hidden' name='deleted_calendar_name_$d' value='$cal_data[name]' /></td>\n".
        "<th align='center' class='input-form-th' width='20%'>\n".
        "<div class='edit-link' style='cursor: default; text-align: center; background-color: {$cal_data["color"]};'><input type='hidden' id='hidden_deleted_calendar_color_$d' name='deleted_calendar_color_$d' value='{$cal_data["color"]}' />".
        "<img src='./images/trans.gif' alt='{$cal_data["color"]}' border='0' width='40px' height='13px' /></div></th>\n".
        "<td align='center' class='input-form-td' width='15%'><input type='checkbox' id='restore_".$d."' name='deleted_restore_$d' onchange='deleted_onchange_data(this)'</td>\n".
        "<th align='center' class='input-form-th' width='15%'><input type='checkbox' id='fully_delete_".$d."' name='deleted_fully_delete_$d' onchange='deleted_onchange_data(this)' />\n".
        "<input type='hidden' name='deleted_calendar_id_$d' value='$cal_id' /></th></tr>";
        $d++;
    }

//display the deleted calendars footer
$content.="<tr class='input-form-td'>\n<td class='input-form-td-break' colspan='4' align='center'>\n".
	"<input type='submit' value='Submit' class='input-form-submit'></td></tr></table></form>";
	}

echo $content;
					
?>