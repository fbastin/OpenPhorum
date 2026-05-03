<?php

function write_string_22 ($string, $sfile) {
	$len_str = strlen ($string);
	$fd = fopen(  $sfile, "w" );
	fwrite($fd, $string, $len_str);
	fclose( $fd ); 
}

if(!defined("PHORUM_CONTROL_CENTER")) return;

// if they are saving their disable setitngs
if (isset($_POST["disable_submit"])) {
        $user["mod_avatar"]["disable_avatar_display"] = ($_POST["disable_avatar_display"]==1) ? true : false;
        unset($user["password"]);
        unset($user["password_temp"]);
        phorum_user_save($user);
        $PHORUM["DATA"]["Message"] = $PHORUM["DATA"]["LANG"]["ChangesSaved"];
}

// if the user is saving their avatars, do this
elseif (isset($_POST["submit"])){
    // a default must be set
    if (!isset($_POST["default_avatar"])) {
        $PHORUM["DATA"]["Message"] = $PHORUM["DATA"]["LANG"]["mod_avatar"]["MissingDefaultAvatar"];
    }
    else {
        // for all the avatars they are saving, we will take ones with a label and add them to their usable avatars
        // we will also set a default
        $avatars = array();
        $filelist = phorum_db_get_user_file_list($PHORUM["user"]["user_id"]);
        foreach ($_POST["label"] as $avatarid => $label) {
            if (isset($filelist[$avatarid]) && (strlen($label) > 0)) {
                $default = ($_POST["default_avatar"] == $avatarid);
                $avatars[$avatarid] = array("avatarid" => $avatarid,
                    "label" => $label,
                    "default" => $default);
            }
        }
        $user = phorum_api_user_get($PHORUM["user"]["user_id"]);
        $user["mod_avatar"]["avatars"] = $avatars;
        $user["mod_avatar"]["default_avatar"] = $_POST["default_avatar"];
        if (isset($_POST["alternate_default_avatar"])){
            $user["mod_avatar"]["alternate_default_avatar"] = $_POST["alternate_default_avatar"];
        }
        unset($user["password"]);
        unset($user["password_temp"]);
        phorum_api_user_save($user);
        $PHORUM["DATA"]["Message"] = $PHORUM["DATA"]["LANG"]["ChangesSaved"];
    }
}


// show the list of avatars they can use
$fullfilelist = phorum_db_get_user_file_list($PHORUM["user"]["user_id"]);
$filelist = array();
foreach ($fullfilelist as $fileid => $file){
    $extension = strtolower(substr($file["filename"], strrpos($file["filename"], ".") + 1));
    if (in_array($extension, $PHORUM["mod_avatar"]["file_types"]) && mod_avatar_check_dimensions($fileid)){
        $default = false;
        $alternate = false;

        if (isset($PHORUM["user"]["mod_avatar"]["default_avatar"])){
            $default = ($fileid == $PHORUM["user"]["mod_avatar"]["default_avatar"]);
        }
        if (isset($PHORUM["user"]["mod_avatar"]["alternate_default_avatar"])){
            $alternate = ($fileid == $PHORUM["user"]["mod_avatar"]["alternate_default_avatar"]);
        }

        $filelist[$fileid] = array("fileid" => $fileid, 
           "filename" => $file["filename"], 
           "default" => $default,
           "alternate" => $alternate,
           "label" => $PHORUM["user"]["mod_avatar"]["avatars"][$fileid]["label"],
           "url" => phorum_get_url(PHORUM_FILE_URL, "file=" . $fileid)
        );
    }
}
$PHORUM['DATA']['mod_avatar'] = $PHORUM["mod_avatar"];
$PHORUM['DATA']['mod_avatar_filelist'] = $filelist;
$PHORUM['DATA']['mod_avatar']['valid_file_types'] = implode(", ", $PHORUM["mod_avatar"]["file_types"]);
$PHORUM['DATA']["mod_avatar"]["disable_avatar_display"] = $user["mod_avatar"]["disable_avatar_display"];
$template = "cc_mod_avatar";

/* 
This function is based on the picProcessor file created by pat on the phorum.org boards.
Thanks pat!

This function checks if an image is inside the maximum dimensions for avatars, and returns true if it is. */
function mod_avatar_check_dimensions($fileid)
{
    $PHORUM=$GLOBALS["PHORUM"];
    //error_reporting(0); // I don't mind what I don't see ;-)

    // getimagesize() requires a filename or a url, but we can cheat a little bit
    // admittadly, if url fopen is disabled, this will not work out very well
//	$filearg=(int)$PHORUM["args"]["file"];
    $file=phorum_db_file_get($fileid);
	$type=strtolower(substr($file["filename"], strrpos($file["filename"], ".")+1));
	$filecont22=base64_decode($file["file_data"]);
	$fname22="cache/".time.$file["filename"];
	write_string_22 ($filecont22, $fname22);
//    $filename = phorum_get_url(PHORUM_FILE_URL, "file=" . $fileid);
//    $imagedata = getimagesize($filename);
    $imagedata = getimagesize($fname22);
    // check if it's really an image just to make sure once again (security?)
    if (!$imagedata[2] || $imagedata[2] == 4 || $imagedata[2] == 5) {
        return false;
    }
	unlink($fname22); 
    return ($imagedata[0] <= $PHORUM["mod_avatar"]["max_width"] && $imagedata[1] <= $PHORUM["mod_avatar"]["max_height"]);   
}
?>