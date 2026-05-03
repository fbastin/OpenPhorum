<?php

// Make sure that this script is loaded from the admin interface.
if(!defined("PHORUM_ADMIN")) return;

// Save settings in case this script is run after posting the settings form.
if(count($_POST)) 
{
	
		include_once("./include/email_functions.php");
    
    if(!empty($_POST["registration_notification_email_address"]) &&
       !phorum_valid_email($_POST["registration_notification_email_address"])){
    	$error="No valid email address entered!";
    } else {
    
	    // Create the settings array for this module.
	    $PHORUM["mod_registration_notification"] = array(
	        "registration_notification_email_address"  => $_POST["registration_notification_email_address"],
	    );
	
	
	    if(! phorum_db_update_settings(array("mod_registration_notification"=>$PHORUM["mod_registration_notification"]))) {
	        $error="Database error while updating settings.";
	    } else {
	        echo "<h3 style='color:green;'>Settings Updated</h3>";
	    }
	  }
}

$mail_users = phorum_api_user_list_moderators('',false,true);
foreach($mail_users as $m_user){
	$admins .= "<div>$m_user</div>";
}
	  
// Apply default values for the settings.
if (!isset($PHORUM["mod_registration_notification"]["registration_notification_email_address"]))
    $PHORUM["mod_registration_notification"]["registration_notification_email_address"] = "";

// We build the settings form by using the PhorumInputForm object. 
include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "registration_notification"); 

// Here we display an error in case one was set by saving the settings before.
if (!empty($error)) {
    echo "<h3 style='color:red;'>$error</h3>";
}

// This adds a break line to your form, with a description on it.
// You can use this to separate your form into multiple sections.
$frm->addbreak("Edit settings for the registration_notification module");

// This adds a text message to your form. You can use this to explain things to the user.
$frm->addmessage("This is the settings screen for the registration_notification module. Here you can specify a single email address for registration notification. When no email addres is specified, the registration notification email will be sent to all administrators.");

// This adds a row with a form field for entering the email address.
$frm->addrow("Single registration notification email address", $frm->text_box('registration_notification_email_address', $PHORUM["mod_registration_notification"]["registration_notification_email_address"], 50));

$frm->addmessage("<b>Current Administrators:</b>$admins");

// We are done building the settings screen.
// By calling show(), the screen will be displayed.
$frm->show();

?>
