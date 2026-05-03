<?php

// This module sends a notification email to all administrator(s) by default
// (or to a single email address, see settings) after a new user has registered.

if(!defined("PHORUM")) return;

function phorum_mod_registration_notification ($userdata) {

	$PHORUM=$GLOBALS["PHORUM"];
	  
	if($PHORUM["registration_control"] == PHORUM_REGISTER_VERIFY_MODERATOR ||
     $PHORUM["registration_control"] == PHORUM_REGISTER_VERIFY_BOTH) {

		include_once("./include/email_functions.php");
		
	  if(empty($PHORUM["mod_registration_notification"]["registration_notification_email_address"])){
	  	$mail_users = phorum_api_user_list_moderators($PHORUM['forum_id'],false,true);
	  }else {
	  	$mail_users  =  array($PHORUM["mod_registration_notification"]["registration_notification_email_address"]);
	  };
	
	  $mail_data = array(
	      "mailmessage" => $PHORUM["DATA"]["LANG"]["registration_notification"]["Message"],
	      "mailsubject" => $PHORUM["DATA"]["LANG"]["registration_notification"]["Subject"],
	      "useremail"   => $userdata["email"],
	      "forumtitle"  => $PHORUM["title"],
	      "username"    => $userdata["username"],
	      "login_url" 	=> phorum_get_url(PHORUM_LOGIN_ACTION_URL)
	  );
	        
	  phorum_email_user($mail_users, $mail_data);  
	  
  }
  
  return $userdata;
}

?>
