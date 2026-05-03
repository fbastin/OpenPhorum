<?php

/* Available user fields
user_id, username, real_name, display_name, email, active, posts, admin, date_added, 
date_last_active, tz_offset, is_dst, user_language, user_template, CUSTOM PROFILE FIELDS
*/
// Make sure that this script is loaded from the admin interface.
if(!defined("PHORUM_ADMIN")) return;

global $PHORUM;
/*
//check debugger
if (isset($_REQUEST["debug"])) {

    $PHORUM["phorum_mod_admin_mass_email"]["enable_debugging"] = (int)$_REQUEST["debug"];
    phorum_db_update_settings(array("phorum_mod_admin_mass_email"=>$PHORUM["phorum_mod_admin_mass_email"]));
}

//check if each multiple email addresses should be debugged.
if (isset($_REQUEST["repeatdebug"])) {
    $PHORUM["phorum_mod_admin_mass_email"]["repeated_debugging"] = (int)$_REQUEST["repeatdebug"];
    phorum_db_update_settings(array("phorum_mod_admin_mass_email"=>$PHORUM["phorum_mod_admin_mass_email"]));
}    
*/

// Save settings in case this script is run after posting
// the settings form.
if(count($_POST) && empty($_REQUEST["phorum_mod_admin_mass_email_page"])) 
{
    $PHORUM["phorum_mod_admin_mass_email"]["allow_user_unsubscribe"] = empty($_POST["allow_user_unsubscribe"]) ? 0 : 1;
    $PHORUM["phorum_mod_admin_mass_email"]["enable_debugging"] = empty($_POST["enable_debugging"]) ? 0 : 1;
    $PHORUM["phorum_mod_admin_mass_email"]["repeated_debugging"] = empty($_POST["repeated_debugging"]) ? 0 : 1;
    $PHORUM["phorum_mod_admin_mass_email"]["disable_actual_emails"] = empty($_POST["disable_actual_emails"]) ? 0 : 1;
    
    phorum_db_update_settings(array("phorum_mod_admin_mass_email"=>$PHORUM["phorum_mod_admin_mass_email"]));
    phorum_admin_okmsg("Settings Updated");
}

// Apply default values for the settings.
if (!isset($PHORUM["phorum_mod_admin_mass_email"]["allow_user_unsubscribe"]) || $PHORUM["phorum_mod_admin_mass_email"]["allow_user_unsubscribe"] === "") {
    $PHORUM["phorum_mod_admin_mass_email"]["allow_user_unsubscribe"] = 0;
    $set_default = 1;
}
if (!empty($set_default)) {
    phorum_db_update_settings(array("phorum_mod_admin_mass_email"=>$PHORUM["phorum_mod_admin_mass_email"]));
}

foreach ($PHORUM["PROFILE_FIELDS"] as $key => $cstm_field) {
    if ($cstm_field["name"] == "phorum_mod_admin_mass_email_user_unsubscribe_setting") {
        if (isset($cstm_field["deleted"]) && $cstm_field["deleted"] == TRUE) {
            $user_unsubscribe = 2;
        } else {
            $user_unsubscribe = 1;
        }
    }
}

if (!isset($user_unsubscribe)) {
    include_once("./include/api/base.php");
    include_once("./include/api/custom_profile_fields.php");
    phorum_api_custom_profile_field_configure(array (
        'id'            => NULL,
        'name'          => 'phorum_mod_admin_mass_email_user_unsubscribe_setting',
        'length'        => 3,
        'html_disabled' => TRUE,
        'show_in_admin' => TRUE,
    ));
    $user_unsubscribe = 1;
}

if (!empty($PHORUM["phorum_mod_admin_mass_email"]["enable_debugging"])) {
  $debug_msg = "Debugging is enabled!";
  if (!empty($PHORUM["phorum_mod_admin_mass_email"]["repeated_debugging"])) {
    $debug_msg .= " Each message will be logged.";
  } else {
    $debug_msg .= " The first message will be logged.";
  }
  if (!empty($PHORUM["phorum_mod_admin_mass_email"]["disable_actual_emails"])) {
    $debug_msg .= " No actual emails will be sent.";
  }
  
  phorum_admin_okmsg($debug_msg);
}

//gather a list of user fields, including custom profile fields
//fields for possible future use:user_id,active,admin,tz_offset,is_dst,user_language,user_template
GLOBAL $user_fields;
$user_fields = explode (",","username,real_name,display_name,email,posts,date_added,date_last_active,user_id,active,admin,tz_offset,is_dst,user_language,user_template");
foreach ($PHORUM["PROFILE_FIELDS"] as $field_order => $field_data) {
    if (!is_array($field_data)) continue;
    if (!empty($field_data["deleted"])) continue;
    $user_fields[] = $field_data["name"];
}
if (empty($PHORUM["admin_token"])) $PHORUM["admin_token"] = "";

//create menu for top of page
$url_args = array(
    "module=modsettings",
    "mod=admin_mass_email",
    "phorum_admin_token=".$PHORUM["admin_token"],
    );
if (function_exists("phorum_admin_build_url")) {
    $email_url = phorum_admin_build_url($url_args);
    $url_args[] = "phorum_mod_admin_mass_email_page=settings";
    $settings_url = phorum_admin_build_url($url_args);
} else {
    $email_url = $PHORUM["http_path"]."/admin.php?".implode("&", $url_args);
    $settings_url = $email_url."&phorum_mod_admin_mass_email_page=settings";
}

$ame_menu = "<style>\n.menuon {\nfont-family: Lucida Sans Unicode, Lucida Grand, Verdana, Arial, Helvetica;\nfont-size: 12px;\nfont-weight: bold;\ncolor: White;\nbackground-color: Navy;\ncursor: pointer;\n}";
$ame_menu .= "\n.menuoff {\nfont-family: Lucida Sans Unicode, Lucida Grand, Verdana, Arial, Helvetica;\nfont-size: 12px;\nfont-weight: bold;\n}\n</style>";
$ame_menu .= "<script>function menuon(id) {\nif(id) {\nid.className='menuon'\n} else {\n}\n}\nfunction menuoff(id) {\nif(id) {\nid.className='menuoff'\n} else {\n}\n}\n";
if (empty($PHORUM["phorum_mod_admin_mass_email"]["allow_user_unsubscribe"])) {
    $ame_menu .= "tags_div = document.getElementsByTagName('div')\nfor (tag_div in tags_div) {\n  if (tags_div[tag_div].className=='PhorumAdminError') {\n    tags_div[tag_div].style.display='none'\n  }\n}\n";
}
if (!empty($_REQUEST["send_preview"])) {
    $ame_menu .= "document.getElementsByTagName('table')[1].style.visibility='hidden';";
}
$ame_menu .= "</script><table cellspacing='0px' cellpadding='3px' class='input-form-table' width='100%'>";
$ame_menu .= "\n<tr><td class='menuoff' id='ame_menu_send_page' width='115px' onmouseover=\"menuon(this)\" onmouseout=\"menuoff(this)\" onclick=\"window.open('$email_url', '_parent')\">Send Mass Email</td>";
$ame_menu .= "\n<td class='menuoff' width='10px'>|</td>";
$ame_menu .= "\n<td class='menuoff' id='ame_menu_settings' width='90px' onmouseover=\"menuon(this)\" onmouseout=\"menuoff(this)\" onclick=\"window.open('$settings_url', '_parent')\">Edit Settings</td>";
$ame_menu .= "\n<td>&nbsp;</td></tr></table>";

//display the email creation page by default
if (empty($_REQUEST["phorum_mod_admin_mass_email_page"])) {

//create a list of user fields to be used throughout this form
    foreach ($user_fields as $key => $field_name) {
        if ($field_name != strtolower($field_name)) {
            $temp_fields[$key] = $field_name;
            $user_fields[$key] = strtolower($field_name);
        }
    }
    asort($user_fields);
    if (!empty($temp_fields)) {
        foreach ($temp_fields as $key => $field_name) {
            $user_fields[$key] = $field_name;
        }
    }
    $field_options = "";
    
    foreach ($user_fields as $user_field) {
        $field_options .= "<option>$user_field</option>";
    }
    
    $subject_fill = (empty($send_data["message"]["subject"])) ? "" : str_replace("\"","&quot;",$send_data["message"]["subject"]);
    $body_fill = (empty($send_data["message"]["body"])) ? "" : $send_data["message"]["body"];
    
//create a list of operators to be used throught this form
    $operators = "<option> = </option><option> < </option><option> > </option><option> <= </option><option> >= </option><option>contains</option>";
    $content = "<script type='text/javascript'>";
    $content .= file_get_contents("./mods/admin_mass_email/admin_mass_email.js");
    $content .= file_get_contents("./mods/admin_mass_email/AnchorPosition.js");
    $content .= file_get_contents("./mods/admin_mass_email/date.js");
    $content .= file_get_contents("./mods/admin_mass_email/PopupWindow.js");
    $content .= file_get_contents("./mods/admin_mass_email/calendar.js");
    $content .= "var dateselect = new CalendarPopup('calpopupdiv');dateselect.setCssPrefix('CalStyle');</script>
    <style> .hidden_conditional_div div { position: relative; background-color: #EEEEEE; padding: 3px 5px 3px 5px; }
    .CalStylecpYearNavigation, .CalStylecpMonthNavigation { background-color:#6677DD; text-align:center; vertical-align:middle; text-decoration:none; color:#FFFFFF; font-weight:bold;    }
    .CalStylecpDayColumnHeader, .CalStylecpYearNavigation, .CalStylecpMonthNavigation, .CalStylecpCurrentMonthDate, .CalStylecpCurrentMonthDateDisabled, .CalStylecpOtherMonthDate,
    .CalStylecpOtherMonthDateDisabled, .CalStylecpCurrentDate, .CalStylecpCurrentDateDisabled, .CalStylecpTodayText, .CalStylecpTodayTextDisabled,
    .CalStylecpText { font-family:arial; font-size:8pt; }
    TD.CalStylecpDayColumnHeader { text-align:right; border:solid thin #6677DD; border-width:0 0 1 0; }
    .CalStylecpCurrentMonthDate, .CalStylecpOtherMonthDate, .CalStylecpCurrentDate { text-align:right; text-decoration:none; }
    .CalStylecpCurrentMonthDateDisabled, .CalStylecpOtherMonthDateDisabled, .CalStylecpCurrentDateDisabled { color:#D0D0D0; text-align:right; text-decoration:line-through; }
    .CalStylecpCurrentMonthDate { color:#6677DD; font-weight:bold; }
    .CalStylecpCurrentDate { color: #FFFFFF; font-weight:bold; }
    .CalStylecpOtherMonthDate { color:#808080; }
    TD.CalStylecpCurrentDate { color:#FFFFFF; background-color: #6677DD; border-width:1; border:solid thin #000000; }
    TD.CalStylecpCurrentDateDisabled { border-width:1; border:solid thin #FFAAAA; }
    TD.CalStylecpTodayText, TD.CalStylecpTodayTextDisabled { border:solid thin #6677DD; border-width:1 0 0 0; }
    A.CalStylecpTodayText, SPAN.CalStylecpTodayTextDisabled { height:20px; }
    A.CalStylecpTodayText { color:#6677DD; font-weight:bold; }
    SPAN.CalStylecpTodayTextDisabled { color:#D0D0D0; }
    .CalStylecpBorder { border:solid thin #6677DD; }
    </style><form style='display: inline;' action='./admin.php' method='post' enctype='multipart/form-data' id='ame_email_id'>
    <input type='hidden' name='phorum_admin_token' value='".$PHORUM['admin_token']."'>
    <input type='hidden' name='module' value='modsettings'><input type='hidden' name='mod' value='admin_mass_email'><input type='hidden' name='phorum_mod_admin_mass_email_page' value='send'>
    <table border='0' cellspacing='2' cellpadding='2' class='input-form-table' width='100%' id='maintable'>
    <tr class='input-form-tr'><td class='input-form-td-break' colspan='2'>Send Mass Email</td></tr>";
//From address
    $content .= "<tr class='input-form-tr'><th valign='top' align='left' class='input-form-th'>Send email from:</th><td valign='middle' align='left'  class='input-form-td'> 
    <select id=\"phorum_mod_admin_mass_email_sender_key\" name=\"sender_key\" onchange=\"sender_key_change(this)\"><option value=0>Default ($PHORUM[system_email_from_address])</option><option value=1>Custom Name/Address</option></select>
    <div id=\"custom_from\" style=\"display:none;\">Custom from name: <input name=\"sender_name\" type='text' size='25'><br />Custom from email address: <input name=\"sender_address\" type='text' size='50'></div>
    </td></tr>";
//Recipients    
    $content .= "<tr class='input-form-tr'><th valign='bottom' align='left' class='input-form-th'>Send email to:</th><td valign='middle' align='left'  class='input-form-td'> 
    <select id=\"phorum_mod_admin_mass_email_recipient_key\" name=\"recipient_key\" onchange=\"recipient_key_change(this)\"><option value=0>All Users</option><option value=1>Selected Users</option><option value=3>Conditional Users</option><option value=2>Selected Groups</option></select> ";
    $phorum_groups = phorum_db_get_groups(0,TRUE);
    $phorum_users = phorum_db_user_get_list(0);
    $content .= "<div id=\"selected_users\" style=\"display:none;\">: <select name='users[]' multiple='multiple'>";
    foreach ($phorum_users as $user_id => $user_info) {
        $content .= "<option value=$user_id>$user_info[username]</option>";
        }
    $content .= "</select></div>
        <div id=\"selected_groups\" style=\"display:none;\">: <select name='groups[]' multiple='multiple'>";
    foreach ($phorum_groups as $group_id => $group_data) {
        $content .= "<option value=$group_id>$group_data[name]</option>";
        }
    $content .= "</select></div>";
//Conditional Recipients    
    $content .= "<div id=\"conditional_users\" style=\"display:none; margin: 4px 0 0 0;\"><select name='conditional_user_field_1'>$field_options</select> (Not <input type='checkbox' name='conditional_not_1' />)
        <select name='conditional_operator_1'>$operators</select> <input type='text' id='conditional_needle_id_1' name='conditional_needle_1' 
        onchange=\"javascript:document.getElementById('conditional_needle_isdate_id_1').value='0'\" /> 
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('conditional_needle_id_1'),document.getElementById('conditional_needle_isdate_id_1'),'a_conditional_needle_1','MM/dd/yyyy');\" NAME='a_conditional_needle_1' ID='a_conditional_needle_1'>Date</a></div>
        <input type='hidden' id='conditional_needle_isdate_id_1' name='conditional_needle_isdate_1' value='0' />
        <div id='addcondition2' style='display:block; font-size:9px;'><a href='javascript:addCondition(2);'>Add Another Condition</a></div>
        </div>
        <div id=\"condition2\" style=\"display:none; margin: 4px 0 0 0; padding: 4px 0 0 0; border-top: 2px solid Navy;\">
        <select name='conditional_andor_2'><option>And</option><option>Or</option></select> 
        <select name='conditional_user_field_2'>$field_options</select> (Not <input type='checkbox' name='conditional_not_2' />)
        <select name='conditional_operator_2'>$operators</select> <input type='text' id='conditional_needle_id_2' name='conditional_needle_2' 
        onchange=\"javascript:document.getElementById('conditional_needle_isdate_id_2').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('conditional_needle_id_2'),document.getElementById('conditional_needle_isdate_id_2'),'a_conditional_needle_2','MM/dd/yyyy');\" NAME='a_conditional_needle_2' ID='a_conditional_needle_2'>Date</a></div>
        <input type='hidden' id='conditional_needle_isdate_id_2' name='conditional_needle_isdate_2' value='0' />
        <div id='addcondition3' style='display:block; font-size:9px;'><a href='javascript:addCondition(3);'>Add Another Condition</a></div>
        </div>
        <div id=\"condition3\" style=\"display:none; margin: 4px 0 0 0; padding: 4px 0 0 0; border-top: 2px solid Navy;\">
        <select name='conditional_andor_3'><option>And</option><option>Or</option></select> 
        <select name='conditional_user_field_3'>$field_options</select> (Not <input type='checkbox' name='conditional_not_3' />)
        <select name='conditional_operator_3'>$operators</select> <input type='text' name='conditional_needle_3' id='conditional_needle_id_3' /> 
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('conditional_needle_id_3'),document.getElementById('conditional_needle_isdate_id_3'),'a_conditional_needle_3','MM/dd/yyyy');\" NAME='a_conditional_needle_3' ID='a_conditional_needle_3'>Date</a></div>
        <input type='hidden' id='conditional_needle_isdate_id_3' name='conditional_needle_isdate_3' value='0' />
        </div></td></tr>";
/* Email Subject */    
    $content .= "<tr class='input-form-tr'><th valign='top' align='left' class='input-form-th'>Email Subject:</th><td valign='middle' align='left'  class='input-form-td'>";
    $content .= get_conditional_content("subject",$field_options,$operators);    
    $content .= "<div style='margin: 5px 0 0 0;'><input id='subject_id' name='subject' type='text' value=\"$subject_fill\" size='60'/></div></td></tr>";
/* End Subject */

/* Email Body */
    $content .= "<tr class='input-form-tr'><th valign='top' align='left' class='input-form-th'>Email Message:</th><td valign='middle' align='left'  class='input-form-td'>";
    $content .= get_conditional_content("body",$field_options,$operators);
    $content .= "<div style='margin: 5px 0 0 0;'><textarea id='body_id' name='body' rows='10' cols='66'>$body_fill</textarea></div></td></tr>";
/* End Email Body */

//Attachments
    $content .= "<tr class='input-form-tr'><th valign='top' align='left' class='input-form-th'>Attachment(s):</th><td valign='middle' align='left'  class='input-form-td'>
        <input type='file' name='importfilename1' size='35' /> <div id='addattachment2' style='display:inline;font-size:9px;'><a href='javascript:addAttachment(2);'>Add Another Attachment</a></div>
        <div id='attachment2' style='display:none;'><input type='file' name='importfilename2' size='35' /> <div id='addattachment3' style='display:inline;font-size:9px;'><a href='javascript:addAttachment(3);'>Add Another Attachment</a></div></div>
        <div id='attachment3' style='display:none;'><input type='file' name='importfilename3' size='35' /></div>
        </td></tr>";
//Submit form
$content .= "<tr class='input-form-tr'><td class='input-form-td-break' align='center' colspan='2'>User to preview:&nbsp;<select name='preview_user'>";
    foreach ($phorum_users as $user_id => $user_info) {
        $content .= "<option value=$user_id>$user_info[username]</option>";
        }
    $content .= "</select>&nbsp;&nbsp;<input name='send_preview' type='submit' onclick='submit_email_form(2)' value='Preview' class='input-form-submit'>&nbsp;&nbsp;<input type='submit' value='Send' onclick='submit_email_form(1)' class='input-form-submit'></td></tr>
    </table></form><DIV ID='calpopupdiv' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></DIV>";
//Display menu and form
    echo $ame_menu;
    echo $content;


//display the email settings page when requested
} else if ($_REQUEST["phorum_mod_admin_mass_email_page"] == "settings") {
    // We build the settings form by using the PhorumInputForm object. When
    // creating your own settings screen, you'll only have to change the
    // "mod" hidden parameter to the name of your own module.
    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "admin_mass_email"); 
    
    // Here we display an error in case one was set by saving 
    // the settings before.
    if (!empty($error)) {
        echo "$error<br />";
    }
    
    // This adds a break line to your form, with a description on it.
    // You can use this to separate your form into multiple sections.
    $frm->addbreak("Edit settings for the Admin Mass Email module");
    if ($user_unsubscribe == 2) {
        $frm->addmessage("Please add the deleted custom profile field named \"phorum_mod_admin_mass_email_user_unsubscribe_setting\" if you would like to allow users to choose to stop receiving admin emails.");
    } else {
        $frm->addrow("Allow users to choose to stop receiving admin emails (set in the Control Center): ", $frm->checkbox("allow_user_unsubscribe", "1", "", $PHORUM["phorum_mod_admin_mass_email"]["allow_user_unsubscribe"]));
    }
  $frm->addsubbreak("Debugging options (only enable if you have problems sending emails)");
  $frm->addrow("Debug email: ", $frm->checkbox("enable_debugging", "1", "", $PHORUM["phorum_mod_admin_mass_email"]["enable_debugging"]));
    $frm->addrow("If debugging, log each email sent: ", $frm->checkbox("repeated_debugging", "1", "", $PHORUM["phorum_mod_admin_mass_email"]["repeated_debugging"]));
  $frm->addrow("If debugging, do not actually send any emails: ", $frm->checkbox("disable_actual_emails", "1", "", $PHORUM["phorum_mod_admin_mass_email"]["disable_actual_emails"]));
  
  // We are done building the settings screen.
    // By calling show(), the screen will be displayed.
    echo $ame_menu;
    $frm->show();
        
//send the email when requested
} else if ($_REQUEST["phorum_mod_admin_mass_email_page"] == "send") {
    
    $debug_i = (!empty($PHORUM["phorum_mod_admin_mass_email"]["enable_debugging"])) ? 1 : 0;
        
    include_once("./mods/admin_mass_email/admin_mass_email.php");
        
    if (empty($_REQUEST["step"])) {
        //build the message data
        $pre_subject = !empty($_REQUEST["subject"]) ? $_REQUEST["subject"] : "";
        $pre_subject = str_replace("\\\"","\"",$pre_subject);
        $pre_subject = str_replace("\\'","'",$pre_subject);
        $pre_subject = str_replace("\\\\","\\",$pre_subject);
        $pre_body = !empty($_REQUEST["body"]) ? $_REQUEST["body"] : "";
        $pre_body = str_replace("\\\"","\"",$pre_body);
        $pre_body = str_replace("\\'","'",$pre_body);
        $pre_body = str_replace("\\\\","\\",$pre_body);
        
        $send_data = array(
            "sender" => array(
                "key" => $_REQUEST["sender_key"],
                "name" => !empty($_REQUEST["sender_name"]) ? $_REQUEST["sender_name"] : null,
                "email" => !empty($_REQUEST["sender_address"]) ? $_REQUEST["sender_address"] : null
                ),
            "recipients" => array(
                "key" => $_REQUEST["recipient_key"] != 2 ? 1 : $_REQUEST["recipient_key"],
                "users" => !empty($_REQUEST["users"]) ? $_REQUEST["users"] : null,
                "groups" => !empty($_REQUEST["groups"]) ? $_REQUEST["groups"] : null
                ),
            "message" => array(
                "subject" => $pre_subject,
                "body" => $pre_body,
                "attachments" => array()
                )
            );
        
        if ($send_data["recipients"]["key"] == 2) {
            $group_ids = $send_data["recipients"]["groups"];
            
            if ($debug_i == 1) {
                if (function_exists('event_logging_writelog')) {
                    $testgroups = implode(",",$group_ids);
                    event_logging_writelog(array(
                        "message"    => "groups:\n\n".$testgroups,
                    ));
                }
            }
            // Get the list of all users in the groups selected above.
            $sql = "select user_id from {$PHORUM['user_group_xref_table']} where ";
            if(count($group_ids)) {
                $sql.=" group_id in (".implode(",", $group_ids).")";
            } else {
                $sql.=" group_id=$group_id";
            }
            
            $res = phorum_db_interact(DB_RETURN_ASSOCS, $sql, "user_id");
            
            foreach ($res as $row) {
                $send_data["recipients"]["users"][] = $row["user_id"];
            }
        } else {
            //get a list of user ids
            if ($_REQUEST["recipient_key"] == 0 || $_REQUEST["recipient_key"] == 3) {
                include_once("./include/api/base.php");
                include_once("./include/api/user.php");
                $raw_users = phorum_api_user_list();
                $raw_user_ids = array ();
                foreach ($raw_users as $user_id => $user_info) {
                    $raw_user_ids[] = $user_id;
                }
            }
            //send all user ids
            if ($_REQUEST["recipient_key"] == 0) {
                $send_data["recipients"]["users"] = $raw_user_ids;
                //find user ids based on submitted conditions
            } elseif ($_REQUEST["recipient_key"] == 3) {
                $condition_not_1 = (empty($_REQUEST["conditional_not_1"])) ? "" : "!";
                if ($_REQUEST["conditional_operator_1"] == "contains") {
                    $condition = "if (".$condition_not_1."strstr(\$user_info[\"".$_REQUEST["conditional_user_field_1"]."\"],\"".$_REQUEST["conditional_needle_1"]."\")";
                } else {
                    $condition = "if (\$user_info['".$_REQUEST["conditional_user_field_1"]."'] ";
                    $condition_operator_1 = ($_REQUEST["conditional_operator_1"] == "=" && empty($condition_not_1)) ? "==" : $_REQUEST["conditional_operator_1"];
                    $condition .= $condition_not_1.$condition_operator_1." ";
                    $condition_needle_1 = ($_REQUEST["conditional_needle_isdate_1"] == 1) ? strtotime($_REQUEST["conditional_needle_1"]) : $_REQUEST["conditional_needle_1"];
                    $condition .= (is_numeric($condition_needle_1)) ? $condition_needle_1 : "\"$condition_needle_1\"";  
                }
                //check for second condition
                if (!empty($_REQUEST["conditional_needle_2"])) {
                    $condition .= ($_REQUEST["conditional_andor_2"] == "AND") ? " && " : " || ";
                    $condition_not_2 = (empty($_REQUEST["conditional_not_2"])) ? "" : "!";
                    if ($_REQUEST["conditional_operator_2"] == "contains") {
                        $condition .= $condition_not_2."strstr(\$user_info[\"".$_REQUEST["conditional_user_field_2"]."\"],\"".$_REQUEST["conditional_needle_2"]."\")";
                    } else {
                        $condition .= "\$user_info['".$_REQUEST["conditional_user_field_2"]."'] ";
                        $condition_operator_2 = ($_REQUEST["conditional_operator_2"] == "=" && empty($condition_not_2)) ? "==" : $_REQUEST["conditional_operator_2"];
                        $condition .= $condition_not_2.$condition_operator_2." ";
                        $condition_needle_2 = ($_REQUEST["conditional_needle_isdate_2"] == 1) ? strtotime($_REQUEST["conditional_needle_2"]) : $_REQUEST["conditional_needle_2"];
                        $condition .= (is_numeric($condition_needle_2)) ? $condition_needle_2 : "\"$condition_needle_2\"";  
                    }
                }
                //check for third condition
                if (!empty($_REQUEST["conditional_needle_3"])) {
                    $condition .= ($_REQUEST["conditional_andor_3"] == "AND") ? " && " : " || ";
                    $condition_not_3 = (empty($_REQUEST["conditional_not_3"])) ? "" : "!";
                    if ($_REQUEST["conditional_operator_3"] == "contains") {
                        $condition .= $condition_not_3."strstr(\$user_info[\"".$_REQUEST["conditional_user_field_3"]."\"],\"".$_REQUEST["conditional_needle_3"]."\")";
                    } else {
                        $condition .= "\$user_info['".$_REQUEST["conditional_user_field_3"]."'] ";
                        $condition_operator_3 = ($_REQUEST["conditional_operator_3"] == "=" && empty($condition_not_3)) ? "==" : $_REQUEST["conditional_operator_3"];
                        $condition .= $condition_not_3.$condition_operator_3." ";
                        $condition_needle_3 = ($_REQUEST["conditional_needle_isdate_3"] == 1) ? strtotime($_REQUEST["conditional_needle_3"]) : $_REQUEST["conditional_needle_3"];
                        $condition .= (is_numeric($condition_needle_3)) ? $condition_needle_3 : "\"$condition_needle_3\"";  
                    }
                }
                $condition .= ") \$send_data[\"recipients\"][\"users\"][] = \$key;";
                if ($debug_i == 1) {
                    if (function_exists('event_logging_writelog')) {
                        event_logging_writelog(array(
                            "message" => "Conditional Sending",
                            "details" => "Conditional php script:\n\n".$condition,
                        ));
                    }
                }
                //run all users through the list of conditions
                $send_data["recipients"]["users"] = array();
                $user_count = count($raw_user_ids);
                
                if ($user_count <= 500) {
                    $full_users = phorum_api_user_get($raw_user_ids,TRUE);
                    foreach ($full_users as $key => $user_info) {
                        eval($condition);
                    }
                } else {
                    $i = 1;
                    $ii = 1;
                    $temp_user_ids = array();
                    while($i <= 500) {
                        if ($ii > $user_count) break;
                        $temp_user_ids[] = array_shift($raw_user_ids);
                        if ($i == 499) {
                            $partial_users = phorum_api_user_get($temp_user_ids,TRUE);
                            foreach ($partial_users as $key => $user_info) {
                                eval($condition);
                            }
                            $i = 0;
                            $temp_user_ids = array();
                        }
                        $i += 1;
                        $ii += 1;
                    }
                }
            }
        }
        if ($debug_i == 1) {
            if (function_exists('event_logging_writelog')) {
                $testusers = implode(",",$send_data["recipients"]["users"]);
                event_logging_writelog(array(
                    "message" => "Recipient User_ids",
                    "details" => "user_ids:\n\n".$testusers,
                ));
            }
        }
            
        //check for attachments
        if (!empty($_FILES["importfilename1"]["name"])) {
            $send_data["message"]["attachments"]["importfilename1"] = $_FILES["importfilename1"]["name"];
        }
        if (!empty($_FILES["importfilename2"]["name"])) {
            $send_data["message"]["attachments"]["importfilename2"] = $_FILES["importfilename2"]["name"];
        }
        if (!empty($_FILES["importfilename3"]["name"])) {
            $send_data["message"]["attachments"]["importfilename3"] = $_FILES["importfilename3"]["name"];
        }
        
        //process the attachments
        if (isset($send_data['message']['attachments'])) {
            include_once("./include/api/file_storage.php");
            foreach ($send_data['message']['attachments'] as $formname => $attachment) {
                $file_data = file_get_contents($_FILES[$formname]["tmp_name"]);
                if (!empty($PHORUM["hooks"]["send_mail"]) 
                    && in_array("smtp_mail",$PHORUM["hooks"]["send_mail"]["mods"]) 
                    && empty($_REQUEST["send_preview"])
                    && file_exists("./mods/smtp_mail/phpmailer/class.phpmailer.php")) {
                    $f_contents = $file_data;
                } else {
                    $encoded_data = base64_encode($file_data);
                    $f_contents = chunk_split($encoded_data);
                }
                $mime_type = phorum_api_file_get_mimetype($attachment);
                $send_data['message']['attachments'][$attachment] = array ("mime_type" => $mime_type,"f_contents" => $f_contents);
                unset($send_data['message']['attachments'][$formname]);
            }
        }    
        
        //show a preview email if requested    
        if (!empty($_REQUEST["send_preview"])) {
            $content = "<div>$ame_menu</div><div id='preview_div' style='position:absolute; left:8px; visibility:visible;'>
                 <table border='0' cellspacing='2' cellpadding='2' class='input-form-table' id='maintable'>
                <tr class='input-form-tr'><td class='input-form-td-break' colspan='2'>";

            $preview_send_data = $send_data;
            unset($preview_send_data["recipients"]);
            $preview_send_data["recipients"]["users_batch"][] = $_REQUEST["preview_user"];
            $preview_send_data["recipients"]["key"] = 1;
            $content .= phorum_mod_admin_mass_email_send($preview_send_data, TRUE);
            unset($send_data);
            $content .= "</td></tr></table></div>";
            echo $content;
        }
        
        if (!empty($send_data)) {
            
            //prepare some values for possible batch sending
            $stored_send_data_stamp = $PHORUM["user"]["user_id"]."_".time();
            
            $batch = 0;
            
        }
        
    } elseif ($_REQUEST["step"] == 1) {
        
        //step 1 is the batch sending step, gather the temporary send data and batch info
        $stored_send_data_stamp = $_REQUEST["ssdstamp"];
        
        $send_data = $PHORUM["phorum_mod_admin_mass_email"]["temp_send_data"][$stored_send_data_stamp];
        
        $batch = (int) $_REQUEST['batch'];
        
    } else {
        
        //we have finished the batch sending, show the final results
        $stored_send_data_stamp = $_REQUEST["ssdstamp"];
        $batchsize = 5; //(empty($PHORUM["phorum_mod_admin_mass_email"]["temp_send_status"][$stored_send_data_stamp])) ? 50 : 5;
        $message_count = $_REQUEST['message_count'];
        $batch = (int) $_REQUEST['batch'];

        //grab the status info for the sent emails and prepare to show it
        if (!empty($PHORUM["phorum_mod_admin_mass_email"]["temp_send_status"][$stored_send_data_stamp]))
            $status_data = $PHORUM["phorum_mod_admin_mass_email"]["temp_send_status"][$stored_send_data_stamp];
        
        if (empty($status_data)) {
            $content = "<p style='color:#008800;'><b>$_REQUEST[message_count] messages were sent successfully.</b></p>";
        } else {
            $content = "<p style='color:#990000;'><b>Your messages generated the following error(s):</b></p>";
            if (is_array($status_data)) {
                foreach ($status_data as $key => $error_msg) {
                    $content .= $error_msg."<br />";
                }
            } else {
                $content .= $status_data."<br />";
            }
        }
        
        //clear out the temporary send data used for the batches
        unset($PHORUM["phorum_mod_admin_mass_email"]["temp_send_status"][$stored_send_data_stamp]);
        unset($PHORUM["phorum_mod_admin_mass_email"]["temp_send_data"][$stored_send_data_stamp]);
        phorum_db_update_settings(array("phorum_mod_admin_mass_email"=>$PHORUM["phorum_mod_admin_mass_email"]));
        
        //show the final results
        echo $ame_menu;
        $perc = floor((($batch+1) * $batchsize) / $message_count * 100);
        if ($perc > 100) $perc = 100; ?>
        
        <p><strong>Sending emails to the selected users/groups.</strong><br/>
        <strong>This might take a while ...</strong></p><br/>
        <table><tr><td>
        <div style="height:20px;width:300px; border:1px solid black">
        <div style="height:20px;width:<?php print $perc ?>%;background-color:green">
        </div></div></td><td style="padding-left:10px">
        <?php 
            $update_count = min(($batch+1)*$batchsize, $message_count);
            print "$update_count messages of $message_count sent" ?>
        </td></tr></table> <?php
        echo $content;
    }
    
    if (!empty($send_data)) {
        
        $batchsize = 5; //(empty($send_data["message"]["attachments"])) ? 50 : 5;

        //send without batching if the recipient count is <= the batch size
        if (count($send_data["recipients"]["users"]) <= $batchsize && empty($batch)) {
            $send_data["recipients"]["users_batch"] = $send_data["recipients"]["users"];
            
            $status_data = phorum_mod_admin_mass_email_send($send_data);
            
            if (empty($status_data)) {
                $content = "<p style='color:#008800;'><b>Your message(s) were sent successfully.</b></p>";
            } else {
                $content = "<p style='color:#990000;'><b>Your message(s) generated the following error(s):</b></p>";
                if (is_array($status_data)) {
                    foreach ($status_data as $key => $error_msg) {
                        $content .= $error_msg."<br />";
                    }
                } else {
                    $content .= $status_data."<br />";
                }
            }
            echo $ame_menu;
            echo $content;
        } else {
            //create and send current batch
            $message_count = isset($_REQUEST['message_count']) 
                ? (int) $_REQUEST['message_count']
                : count($send_data["recipients"]["users"]);
            
            //clear the previous batch    
            if (!empty($send_data["recipients"]["users_batch"])) unset ($send_data["recipients"]["users_batch"]); 
            
            //create the batch based on batch size and clear the current batch from the master list.
            $all_users =  $send_data["recipients"]["users"];
            for($i=0;$i<$batchsize;$i++) {
                $send_data["recipients"]["users_batch"][] = array_shift($all_users);
            }
            $send_data["recipients"]["users"] = $all_users;
            
            $status_data = phorum_mod_admin_mass_email_send($send_data);
            
            //store the temporary send and status data for batch sending
            $PHORUM["phorum_mod_admin_mass_email"]["temp_send_data"][$stored_send_data_stamp] = $send_data;
            if (!empty($status_data)) {
                $PHORUM["phorum_mod_admin_mass_email"]["temp_send_status"][$stored_send_data_stamp] =
                    (!empty($PHORUM["phorum_mod_admin_mass_email"]["temp_send_status"][$stored_send_data_stamp]))
                    ? array_merge($PHORUM["phorum_mod_admin_mass_email"]["temp_send_status"][$stored_send_data_stamp], $status_data)
                    : $status_data;
            }
            
            phorum_db_update_settings(array("phorum_mod_admin_mass_email"=>$PHORUM["phorum_mod_admin_mass_email"]));
            
            //show the current status of the batch sending
            echo $ame_menu;
            $perc = floor((($batch+1) * $batchsize) / $message_count * 100);
            if ($perc > 100) $perc = 100; ?>
            
            <p><strong>Sending emails to the selected users/groups.</strong><br/>
            <strong>This might take a while ...</strong></p><br/>
            <table><tr><td>
            <div style="height:20px;width:300px; border:1px solid black">
            <div style="height:20px;width:<?php print $perc ?>%;background-color:green">
            </div></div></td><td style="padding-left:10px">
            <?php 
                $update_count = min(($batch+1)*$batchsize, $message_count);
                print "$update_count messages of $message_count sent" ?>
            </td></tr></table> <?php
            
            //redirect the page to send the next batch
            $redir = ($update_count != $message_count) 
                ? $PHORUM["admin_http_path"] . 
                    '?module=modsettings&mod=admin_mass_email' .
                    '&phorum_admin_token=' . $PHORUM["admin_token"] .
                    '&phorum_mod_admin_mass_email_page=send' . 
                    '&batch=' . ($batch + 1) . 
                    '&step=1' .
                    '&message_count='.$message_count .
                    '&ssdstamp='.$stored_send_data_stamp
                : $PHORUM["admin_http_path"] . 
                    '?module=modsettings&mod=admin_mass_email' .
                    '&phorum_admin_token=' . $PHORUM["admin_token"] .
                    '&phorum_mod_admin_mass_email_page=send' . 
                    '&batch=' . ($batch) . 
                    '&step=2' .
                    '&message_count='.$message_count .
                    '&ssdstamp='.$stored_send_data_stamp;
                    ?>
                
                    <script type="text/javascript">
                    window.onload = function () {
                    document.location.href = '<?php print addslashes($redir) ?>';
                    }
                    </script> <?php
        }
    }
}

function get_conditional_content($field,$field_options,$operators) {
    $content = "<div style='margin: 0; padding: 1px;'><input type='button' value='Add User Field' onclick='show_user_fields(\"".$field."\")' />
        <input type='button' value='Add Conditional Content' onclick='show_conditional_content(\"".$field."\")' /></div>";
//User Fields for field
    $content .= "<div id='".$field."_user_fields_div' style='background-color: Navy; position: absolute; visibility: hidden; margin: 0; padding: 5px;' onmouseover='cancel_closetimer()' onmouseout=\"set_closetimer('".$field."')\">
        <select id='".$field."_user_field' name='".$field."_user_field' multiple='multiple' onchange=\"add_user_field('".$field."',this)\" size='7'>$field_options</select></div>";
//Conditional Content for field
    $content .= "<div class='hidden_conditional_div' id='".$field."_conditional_content_div' style='background-color: Navy; position: absolute; visibility: hidden; margin: 0; padding: 5px;'>
        <div id='".$field."_conditional_content_div_1' style='padding: 0px;'>
        <div>If User Field:</div><div><select id='".$field."_conditional_user_field_id_1' name='".$field."_conditional_user_field'>$field_options</select></div>
        <div>(Not <input type='checkbox' id='".$field."_conditional_not_id_1' name='".$field."_conditional_not' />)
        <select id='".$field."_conditional_operator_id_1' name='".$field."_conditional_operator'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_1' name='".$field."_conditional_needle' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_1').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_1'),document.getElementById('".$field."_conditional_needle_isdate_id_1'),'".$field."_a_conditional_needle_id_1','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_1' ID='".$field."_a_conditional_needle_id_1'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_1' name='".$field."_conditional_needle_isdate_1' value='0' /></div>";
//First AndOr
    $content .= "<div id='".$field."_conditional_add_andor_2' style='text-align: center; font-size:9px; padding-top: 0px;'><a href='javascript:conditional_andor(\"".$field."\",\"AND\",2)'>And</a>&nbsp;/&nbsp;<a href='javascript:conditional_andor(\"".$field."\",\"OR\",2)'>Or</a></div>
        <div id='".$field."_conditional_andor1_2' style='display: none; border-top: 1px solid Navy;'>
        <div id='".$field."_conditional_AND1_2' style='display: none; padding: 0px'>And</div>
        <div id='".$field."_conditional_OR1_2' style='display: none; padding: 0px'>Or</div>&nbsp;If User Field: <input type='hidden' id='".$field."_conditional_andor_enabled_id_2' name='".$field."_conditional_andor_enabled_2'  value='0'/>
        <div id='remove_".$field."_conditional_andor_id' style='display:inline;font-size:9px;'><a href='javascript:remove_conditional_andor(\"".$field."\",2);'>Remove</a></div></div>
        <div id='".$field."_conditional_andor2_2' style='display: none;'><select id='".$field."_conditional_user_field_id_2' name='".$field."_conditional_user_field_2'>$field_options</select></div>
        <div id='".$field."_conditional_andor3_2' style='display: none;'>(Not <input type='checkbox' id='".$field."_conditional_not_id_2' name='".$field."_conditional_not_2' />)
        <select id='".$field."_conditional_operator_id_2' name='".$field."_conditional_operator_2'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_2' name='".$field."_conditional_needle_2' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_2').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_2'),document.getElementById('".$field."_conditional_needle_isdate_id_2'),'".$field."_a_conditional_needle_id_2','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_2' ID='".$field."_a_conditional_needle_id_2'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_2' name='".$field."_conditional_needle_isdate_2' value='0' /></div>";
//Second AndOr
    $content .= "<div id='".$field."_conditional_add_andor_3' style='display: none; text-align: center; font-size:9px; padding-top: 0px;'><a href='javascript:conditional_andor(\"".$field."\",\"AND\",3)'>And</a>&nbsp;/&nbsp;<a href='javascript:conditional_andor(\"".$field."\",\"OR\",3)'>Or</a></div>
        <div id='".$field."_conditional_andor1_3' style='display: none; border-top: 1px solid Navy;'>
        <div id='".$field."_conditional_AND1_3' style='display: none; padding: 0px'>And</div>
        <div id='".$field."_conditional_OR1_3' style='display: none; padding: 0px'>Or</div>&nbsp;If User Field: <input type='hidden' id='".$field."_conditional_andor_enabled_id_3' name='".$field."_conditional_andor_enabled_3'  value='0'/>
        <div style='display:inline;font-size:9px;'><a href='javascript:remove_conditional_andor(\"".$field."\",3);'>Remove</a></div></div>
        <div id='".$field."_conditional_andor2_3' style='display: none;'><select id='".$field."_conditional_user_field_id_3' name='".$field."_conditional_user_field_3'>$field_options</select></div>
        <div id='".$field."_conditional_andor3_3' style='display: none;'>(Not <input type='checkbox' id='".$field."_conditional_not_id_3' name='".$field."_conditional_not_3' />)
        <select id='".$field."_conditional_operator_id_3' name='".$field."_conditional_operator_3'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_3' name='".$field."_conditional_needle_3' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_3').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_3'),document.getElementById('".$field."_conditional_needle_isdate_id_3'),'".$field."_a_conditional_needle_id_3','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_3' ID='".$field."_a_conditional_needle_id_3'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_3' name='".$field."_conditional_needle_isdate_3' value='0' /></div>";
//Show Content
    $content .= "<div style='border-top: 1px solid Navy;'>Then Show: <input type='text' id='".$field."_conditional_content_id' name='".$field."_conditional_content' size='51' /></div></div>";
//First ElseIf Conditional Content for field
    $content .= "<div id='".$field."_conditional_content_div_elseif_1' style='display: none; padding: 0px; border-top: 3px solid Navy;'>
        <div>Else If User Field:<input type='hidden' id='".$field."_conditional_elseif_enabled_id_1' name='".$field."_conditional_elseif_enabled_1'  value='0'/><div id='remove_".$field."_conditional_id_elseif_1' style='display:inline; font-size:9px;'><a href='javascript:remove_conditional_elseif(\"".$field."\",1);'>Remove</a></div></div>
        <div><select id='".$field."_conditional_user_field_id_elseif_1_1' name='".$field."_conditional_user_field_elseif_1'>$field_options</select></div>
        <div>(Not <input type='checkbox' id='".$field."_conditional_not_id_elseif_1_1' name='".$field."_conditional_not_elseif_1' />)
        <select id='".$field."_conditional_operator_id_elseif_1_1' name='".$field."_conditional_operator_elseif_1'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_elseif_1_1' name='".$field."_conditional_needle_elseif_1' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_elseif_1_1').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_elseif_1_1'),document.getElementById('".$field."_conditional_needle_isdate_id_elseif_1_1'),'".$field."_a_conditional_needle_id_elseif_1_1','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_elseif_1_1' ID='".$field."_a_conditional_needle_id_elseif_1_1'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_elseif_1_1' name='".$field."_conditional_needle_isdate_elseif_1_1' value='0' /></div>";
//First ElseIf First AndOr
    $content .= "<div id='".$field."_conditional_add_andor_elseif_1_2' style='text-align: center; font-size:9px; padding-top: 0px;'><a href='javascript:conditional_andor_elseif(\"".$field."\",1,\"AND\",2)'>And</a>&nbsp;/&nbsp;<a href='javascript:conditional_andor_elseif(\"".$field."\",1,\"OR\",2)'>Or</a></div>
        <div id='".$field."_conditional_andor1_elseif_1_2' style='display: none; border-top: 1px solid Navy;'>
        <div id='".$field."_conditional_AND1_elseif_1_2' style='display: none; padding: 0px'>And</div>
        <div id='".$field."_conditional_OR1_elseif_1_2' style='display: none; padding: 0px'>Or</div>&nbsp;If User Field: <input type='hidden' id='".$field."_conditional_andor_enabled_id_elseif_1_2' name='".$field."_conditional_andor_enabled_elseif_1_2'  value='0'/>
        <div id='remove_".$field."_conditional_andor_id_elseif_1' style='display:inline;font-size:9px;'><a href='javascript:remove_conditional_andor_elseif(\"".$field."\",1,2);'>Remove</a></div></div>
        <div id='".$field."_conditional_andor2_elseif_1_2' style='display: none;'><select id='".$field."_conditional_user_field_id_elseif_1_2' name='".$field."_conditional_user_field_elseif_1_2'>$field_options</select></div>
        <div id='".$field."_conditional_andor3_elseif_1_2' style='display: none;'>(Not <input type='checkbox' id='".$field."_conditional_not_id_elseif_1_2' name='".$field."_conditional_not_elseif_1_2' />)
        <select id='".$field."_conditional_operator_id_elseif_1_2' name='".$field."_conditional_operator_elseif_1_2'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_elseif_1_2' name='".$field."_conditional_needle_elseif_1_2' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_elseif_1_2').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_elseif_1_2'),document.getElementById('".$field."_conditional_needle_isdate_id_elseif_1_2'),'".$field."_a_conditional_needle_id_elseif_1_2','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_elseif_1_2' ID='".$field."_a_conditional_needle_id_elseif_1_2'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_elseif_1_2' name='".$field."_conditional_needle_isdate_elseif_1_2' value='0' /></div>";
//First ElseIf Second AndOr
    $content .= "<div id='".$field."_conditional_add_andor_elseif_1_3' style='display: none; text-align: center; font-size:9px; padding-top: 0px;'><a href='javascript:conditional_andor_elseif(\"".$field."\",1,\"AND\",3)'>And</a>&nbsp;/&nbsp;<a href='javascript:conditional_andor_elseif(\"".$field."\",1,\"OR\",3)'>Or</a></div>
        <div id='".$field."_conditional_andor1_elseif_1_3' style='display: none; border-top: 1px solid Navy;'>
        <div id='".$field."_conditional_AND1_elseif_1_3' style='display: none; padding: 0px'>And</div>
        <div id='".$field."_conditional_OR1_elseif_1_3' style='display: none; padding: 0px'>Or</div>&nbsp;If User Field: <input type='hidden' id='".$field."_conditional_andor_enabled_id_elseif_1_3' name='".$field."_conditional_andor_enabled_elseif_1_3'  value='0'/>
        <div style='display:inline;font-size:9px;'><a href='javascript:remove_conditional_andor_elseif(\"".$field."\",1,3);'>Remove</a></div></div>
        <div id='".$field."_conditional_andor2_elseif_1_3' style='display: none;'><select id='".$field."_conditional_user_field_id_elseif_1_3' name='".$field."_conditional_user_field_elseif_1_3'>$field_options</select></div>
        <div id='".$field."_conditional_andor3_elseif_1_3' style='display: none;'>(Not <input type='checkbox' id='".$field."_conditional_not_id_elseif_1_3' name='".$field."_conditional_not_elseif_1_3' />)
        <select id='".$field."_conditional_operator_id_elseif_1_3' name='".$field."_conditional_operator_elseif_1_3'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_elseif_1_3' name='".$field."_conditional_needle_elseif_1_3' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_elseif_1_3').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_elseif_1_3'),document.getElementById('".$field."_conditional_needle_isdate_id_elseif_1_3'),'".$field."_a_conditional_needle_id_elseif_1_3','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_elseif_1_3' ID='".$field."_a_conditional_needle_id_elseif_1_3'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_elseif_1_3' name='".$field."_conditional_needle_isdate_elseif_1_3' value='0' /></div>";
//First ElseIf Show Content
    $content .= "<div style='border-top: 1px solid Navy;'>Then Show: <input type='text' id='".$field."_conditional_content_id_elseif_1' name='".$field."_conditional_content_elseif_1' size='51' /></div></div>";
//Second ElseIf Conditional Cont(ent for field
    $content .= "<div id='".$field."_conditional_content_div_elseif_2' style='display: none; padding: 0px; border-top: 3px solid Navy;'>
        <div>Else If User Field:<input type='hidden' id='".$field."_conditional_elseif_enabled_id_2' name='".$field."_conditional_elseif_enabled_2'  value='0'/><div id='remove_".$field."_conditional_id_elseif_2' style='display:inline;font-size:9px;'><a href='javascript:remove_conditional_elseif(\"".$field."\",2);'>Remove</a></div></div>
        <div><select id='".$field."_conditional_user_field_id_elseif_2_1' name='".$field."_conditional_user_field_elseif_2'>$field_options</select></div>
        <div>(Not <input type='checkbox' id='".$field."_conditional_not_id_elseif_2_1' name='".$field."_conditional_not_elseif_2' />)
        <select id='".$field."_conditional_operator_id_elseif_2_1' name='".$field."_conditional_operator_elseif_2'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_elseif_2_1' name='".$field."_conditional_needle_elseif_2' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_elseif_2_1').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_elseif_2_1'),document.getElementById('".$field."_conditional_needle_isdate_id_elseif_2_1'),'".$field."_a_conditional_needle_id_elseif_2_1','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_elseif_2_1' ID='".$field."_a_conditional_needle_id_elseif_2_1'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_elseif_2_1' name='".$field."_conditional_needle_isdate_elseif_2_1' value='0' /></div>";
//Second ElseIf First AndOr
    $content .= "<div id='".$field."_conditional_add_andor_elseif_2_2' style='text-align: center; font-size:9px; padding-top: 0px;'><a href='javascript:conditional_andor_elseif(\"".$field."\",2,\"AND\",2)'>And</a>&nbsp;/&nbsp;<a href='javascript:conditional_andor_elseif(\"".$field."\",2,\"OR\",2)'>Or</a></div>
        <div id='".$field."_conditional_andor1_elseif_2_2' style='display: none; border-top: 1px solid Navy;'>
        <div id='".$field."_conditional_AND1_elseif_2_2' style='display: none; padding: 0px'>And</div>
        <div id='".$field."_conditional_OR1_elseif_2_2' style='display: none; padding: 0px'>Or</div>&nbsp;If User Field: <input type='hidden' id='".$field."_conditional_andor_enabled_id_elseif_2_2' name='".$field."_conditional_andor_enabled_elseif_2_2'  value='0'/>
        <div id='remove_".$field."_conditional_andor_id_elseif_2' style='display:inline;font-size:9px;'><a href='javascript:remove_conditional_andor_elseif(\"".$field."\",2,2);'>Remove</a></div></div>
        <div id='".$field."_conditional_andor2_elseif_2_2' style='display: none;'><select id='".$field."_conditional_user_field_id_elseif_2_2' name='".$field."_conditional_user_field_elseif_2_2'>$field_options</select></div>
        <div id='".$field."_conditional_andor3_elseif_2_2' style='display: none;'>(Not <input type='checkbox' id='".$field."_conditional_not_id_elseif_2_2' name='".$field."_conditional_not_elseif_2_2' />)
        <select id='".$field."_conditional_operator_id_elseif_2_2' name='".$field."_conditional_operator_elseif_2_2'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_elseif_2_2' name='".$field."_conditional_needle_elseif_2_2' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_elseif_2_2').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_elseif_2_2'),document.getElementById('".$field."_conditional_needle_isdate_id_elseif_2_2'),'".$field."_a_conditional_needle_id_elseif_2_2','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_elseif_2_2' ID='".$field."_a_conditional_needle_id_elseif_2_2'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_elseif_2_2' name='".$field."_conditional_needle_isdate_elseif_2_2' value='0' /></div>";
//Second ElseIf Second AndOr
    $content .= "<div id='".$field."_conditional_add_andor_elseif_2_3' style='display: none; text-align: center; font-size:9px; padding-top: 0px;'><a href='javascript:conditional_andor_elseif(\"".$field."\",2,\"AND\",3)'>And</a>&nbsp;/&nbsp;<a href='javascript:conditional_andor_elseif(\"".$field."\",2,\"OR\",3)'>Or</a></div>
        <div id='".$field."_conditional_andor1_elseif_2_3' style='display: none; border-top: 1px solid Navy;'>
        <div id='".$field."_conditional_AND1_elseif_2_3' style='display: none; padding: 0px'>And</div>
        <div id='".$field."_conditional_OR1_elseif_2_3' style='display: none; padding: 0px'>Or</div>&nbsp;If User Field: <input type='hidden' id='".$field."_conditional_andor_enabled_id_elseif_2_3' name='".$field."_conditional_andor_enabled_elseif_2_3'  value='0'/>
        <div style='display:inline;font-size:9px;'><a href='javascript:remove_conditional_andor_elseif(\"".$field."\",2,3);'>Remove</a></div></div>
        <div id='".$field."_conditional_andor2_elseif_2_3' style='display: none;'><select id='".$field."_conditional_user_field_id_elseif_2_3' name='".$field."_conditional_user_field_elseif_2_3'>$field_options</select></div>
        <div id='".$field."_conditional_andor3_elseif_2_3' style='display: none;'>(Not <input type='checkbox' id='".$field."_conditional_not_id_elseif_2_3' name='".$field."_conditional_not_elseif_2_3' />)
        <select id='".$field."_conditional_operator_id_elseif_2_3' name='".$field."_conditional_operator_elseif_2_3'>$operators</select> <input type='text' id='".$field."_conditional_needle_id_elseif_2_3' name='".$field."_conditional_needle_elseif_2_3' size='39' 
        onchange=\"javascript:document.getElementById('".$field."_conditional_needle_isdate_id_elseif_2_3').value='0'\" />
        <div style='display:inline; font-size:9px;'><a href=\"javascript:dateselect.select(document.getElementById('".$field."_conditional_needle_id_elseif_2_3'),document.getElementById('".$field."_conditional_needle_isdate_id_elseif_2_3'),'".$field."_a_conditional_needle_id_elseif_2_3','MM/dd/yyyy');\" NAME='".$field."_a_conditional_needle_id_elseif_2_3' ID='".$field."_a_conditional_needle_id_elseif_2_3'>Date</a></div>
        <input type='hidden' id='".$field."_conditional_needle_isdate_id_elseif_2_3' name='".$field."_conditional_needle_isdate_elseif_2_3' value='0' /></div>";
//Second ElseIf Show Content
    $content .= "<div style='border-top: 1px solid Navy;'>Then Show: <input type='text' id='".$field."_conditional_content_id_elseif_2' name='".$field."_conditional_content_elseif_2' size='51' /></div></div>";
//Add ElseIf
    $content .= "<div id='".$field."_conditional_add_elseif_else' style='text-align: center;'>
    <div id='".$field."_conditional_add_elseif_1' style='font-size:9px; padding-top: 0px; display: inline;'><a href='javascript:conditional_elseif(\"".$field."\",1)'>Else If</a></div> 
    <div id='".$field."_conditional_add_elseif_2' style='font-size:9px; padding-top: 0px; display: none;'><a href='javascript:conditional_elseif(\"".$field."\",2)'>Else If</a></div>";
//Add Else & Content
    $content .= "<div id='".$field."_conditional_add_else' style='font-size:9px; padding-top: 0px; display: inline;'><a href='javascript:conditional_else(\"".$field."\")'>Else</a></div></div>
        <div id='".$field."_conditional_else_div' style='display: none; border-top: 3px solid Navy;'>Else Show: <input type='hidden' id='".$field."_conditional_else_enabled_id' name='".$field."_conditional_else_enabled' value='0'/>
        <input type='text' id='".$field."_conditional_else_content_id' name='".$field."_conditional_else_content' size='40' /> <div style='display:inline;font-size:9px;'><a href='javascript:remove_conditional_else(\"".$field."\");'>Remove</a></div></div>";
//Cancel,Clear,Submit conditional content
    $content .= "<div style='text-align: center; padding-bottom: 5px; border-top: 3px solid Navy;'><input type='button' value='Cancel' onclick='conditional_reset(\"".$field."\",1,1,1)' />&nbsp;&nbsp;&nbsp;<input type='button' value='Reset' onclick='conditional_reset(\"".$field."\",1,1,0)' />&nbsp;&nbsp;&nbsp;<input type='button' value='Add to ";
    if ($field == "body") {
        $content .= "Message";
    } elseif ($field == "subject") {
        $content .= "Subject";
    }
    $content .= "' onclick='submit_conditional_content(\"".$field."\")' /></div></div>";

    return $content;
}

?>