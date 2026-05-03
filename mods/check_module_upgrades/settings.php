<?php

// Make sure that this script is loaded from the admin interface.
if(!defined("PHORUM_ADMIN")) return;

Global $PHORUM;

// Save settings in case this script is run after posting
// the settings form.
if(count($_POST)) 
{
    // Create the settings array for this module.
    $PHORUM["phorum_mod_check_module_upgrades"] = array(
	"enable_index_check"  	=> empty($_POST["enable_index_check"]) ? 0 : 1,
	);

    if(! phorum_db_update_settings(array("phorum_mod_check_module_upgrades"=>$PHORUM["phorum_mod_check_module_upgrades"]))) {
        $error="Database error while updating settings.";
    } else {
        echo "Settings Updated<br />";
    }
}

// We build the settings form by using the PhorumInputForm object. When
// creating your own settings screen, you'll only have to change the
// "mod" hidden parameter to the name of your own module.
include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "check_module_upgrades"); 

// Here we display an error in case one was set by saving 
// the settings before.
if (!empty($error)) {
    echo "$error<br />";
}

// This adds a break line to your form, with a description on it.
// You can use this to separate your form into multiple sections.
$frm->addbreak("Edit settings for the Check Module Upgrades module");
$frm->addrow("Show upgrade notification to forum admin on the index page (checked once per day): ", $frm->checkbox("enable_index_check", "1", "", $PHORUM["phorum_mod_check_module_upgrades"]["enable_index_check"]));
// We are done building the settings screen.
// By calling show(), the screen will be displayed.
$frm->show();
?>