<?php
/*	mod_avatar: Allow users to have an avatar. This requires template support.
	author: Chris Eaton (tridus@hiredgoons.ca)
	version: 3.0.0
	license: You are free to use, modify, or redistribute this code so long as I am given credit for the original development somewhere.
			 This code comes with absolutly no warranty.

			 It would be nice (but not required) of you to email me if you find this module useful. :-)
*/
    if(!defined("PHORUM_ADMIN")) return;

	// save settings
    if(count($_POST)){
        if(!(empty($_POST["max_height"]) || empty($_POST["max_width"]) || empty($_POST["file_types"]))){
            $PHORUM["mod_avatar"]["max_height"]=$_POST["max_height"];
            $PHORUM["mod_avatar"]["max_width"]=$_POST["max_width"];
            $PHORUM["mod_avatar"]["file_types"]=$_POST["file_types"];
			$PHORUM["mod_avatar"]["enable_namedposting"]= ($_POST["enable_namedposting"]==1) ? true : false;
		} else {
			$error = "You did not define all necessary fields.";
		}

        if(empty($error)){
            if(!phorum_db_update_settings(array("mod_avatar"=>$PHORUM["mod_avatar"]))){
                $error="Database error while updating settings.";
            } else {
                echo "Settings Updated<br />";
            }
        } 
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "avatar");
    $frm->addbreak("Avatar Module Settings (2.1.1)");

	$frm->addmessage("To use this module, 'mod_avatar' must be created as a Custom Profile Field, and 'file uploads' must be enabled in Phorum's 'General Settings'. A user will then be allowed to upload files to his account, which he can use as avatars. Be sure to read the included readme.txt file for more information on the necessary template changes for this module.");

    $frm->addbreak();
	$frm->addmessage("Maximum Dimensions of the Avatar. Images larger then this will not be usable as avatars.");
	$frm->addrow("Maximum Height (recommended 100): ", $frm->text_box("max_height", $PHORUM["mod_avatar"]["max_height"]));
	$frm->addrow("Maximum Width (recommended 100): ", $frm->text_box("max_width", $PHORUM["mod_avatar"]["max_width"]));

	$frm->addbreak();
	$frm->addmessage("Image Types which a user can use as an avatar.");
    $types=array("gif","jpg","png","bmp","swf");
    foreach($types as $type){
		$checked = (@in_array($type, $PHORUM["mod_avatar"]["file_types"]))? 1 : 0;
		$frm->addrow($type, $frm->checkbox("file_types[]", $type, "", $checked));
    }  

	$frm->addbreak();
	$frm->addmessage("Enable support for dual default avatars. This requires mod_namedposting version 1.2 or later to be installed.");
	$checked = ($PHORUM["mod_avatar"]["enable_namedposting"]) ? 1 : 0;
	$frm->addrow("Enable dual default avatar support: ", $frm->checkbox("enable_namedposting", "1", "", $checked));
    $frm->show();
?>
