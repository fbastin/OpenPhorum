<?php

// Make sure that this script is loaded from the admin interface.
if(!defined("PHORUM_ADMIN")) return;

// Save settings in case this script is run after posting
// the settings form.
if(count($_POST)) 
{
	$groupdata = array();

	if($_POST["new_group"]){
		// set the new group permission to approved
		$groupdata[$_POST["new_group"]] = $_POST['new_group_permission'];
	}

	if(isset($_POST["group_perm"])){
		foreach($_POST["group_perm"] as $group_id=>$perm){
			// as long as we aren't removing them from the group, accept other values
			if ($perm != "remove"){
				$groupdata[$group_id] = $perm;
			}
		}
	}

    if(! phorum_db_update_settings(array("mod_regtogroup"=>$groupdata))) {
        $error="Database error while updating settings.";
    } else {
    	$PHORUM['mod_regtogroup']=$groupdata;
        echo "Settings Updated<br />";
    }
}


// We build the settings form by using the PhorumInputForm object. When
// creating your own settings screen, you'll only have to change the
// "mod" hidden parameter to the name of your own module.
include_once "./include/admin/PhorumInputForm.php";
$frm = new PhorumInputForm ("", "post", "Save");
$frm->hidden("module", "modsettings");
$frm->hidden("mod", "register_to_group"); 

// Here we display an error in case one was set by saving 
// the settings before.
if (!empty($error)) {
    echo "$error<br />";
}

// This adds a break line to your form, with a description on it.
// You can use this to separate your form into multiple sections.
$frm->addbreak("Add groups and status for the group which new users should get");

$groups= phorum_db_get_groups(0, TRUE);
$current_groups = $PHORUM['mod_regtogroup'];

$arr=array("Add A Group...");
foreach($groups as $group_id=>$group){
	if(!isset($current_groups[$group_id]))
	$arr[$group_id]=$group["name"];
}

$group_options = array(
                    "remove" => "< Remove Group >",
    PHORUM_USER_GROUP_SUSPENDED => "Suspended",
    PHORUM_USER_GROUP_UNAPPROVED => "Unapproved",
    PHORUM_USER_GROUP_APPROVED => "Approved",
    PHORUM_USER_GROUP_MODERATOR => "Group Moderator");


if(count($arr)>1)
$frm->addrow("Add A Group", $frm->select_tag("new_group", $arr).$frm->select_tag("new_group_permission", $group_options, PHORUM_USER_GROUP_UNAPPROVED));

if(is_array($current_groups)){
	
	foreach($current_groups as $group_id => $group_perm){
		$group_info = phorum_db_get_groups($group_id);
		$frm->hidden("groups[$group_id]", "$group_id");
		$frm->addrow($group_info[$group_id]["name"], $frm->select_tag("group_perm[$group_id]", $group_options, $group_perm));
	}
}

// We are done building the settings screen.
// By calling show(), the screen will be displayed.
$frm->show();

?>