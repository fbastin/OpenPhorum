<?php
/*
mod_avatar : Allow users to have an avatar. This requires template support.
author     : Chris Eaton (tridus@hiredgoons.ca)
             Special thanks to pat!
             Updated to work with Phorum 5.1+ and some
             (security) fixes by Maurice Makaay
version    : 3.0.0
license    : You are free to use, modify, or redistribute this code so long
             as I am given credit for the original development somewhere.
			 This code comes with absolutly no warranty.

			 It would be nice (but not required) of you to email me if you
             find this module useful. :-)
*/
if(!defined("PHORUM")) return;


if (@$PHORUM["DATA"]["mod_avatar"]["enable_namedposting"] == true) {
    include("./../namedposting/namedposting.php");
}



// setup the list of files that can be used as an avatar, 
// used when configuring avatars
function mod_avatar_setup_file_list()
{
	global $PHORUM;

    $PHORUM['mod_avatar']['enabled'] = true;

    // Only useful for logged in users.
	if(!$PHORUM["DATA"]["LOGGEDIN"]) return;

    $PHORUM['mod_avatar']['url'] = phorum_get_url(PHORUM_CONTROLCENTER_URL, "panel=mod_avatar");
    $PHORUM['DATA']['mod_avatar']['url'] = phorum_get_url(PHORUM_CONTROLCENTER_URL, "panel=mod_avatar");
    $PHORUM['DATA']['mod_avatar']['enable_namedposting'] = $PHORUM['mod_avatar']['enable_namedposting'];

    // We need to figure out what to do, based on what page we are on.
    // On these pages, we should show the avatars for the user.
    if (phorum_page == "read" || phorum_page == "post") 
    {
        // Find the user for the current posting mode and get
        // the avatars for that user.
        $userid = $PHORUM["user"]["user_id"];
		$messageid = 0;
        if (phorum_page == "post" && isset($PHORUM['args'][2])) {
			$messageid = $PHORUM['args'][2];
            $message = phorum_db_get_message($messageid);
            $userid = $message["user_id"];
        } elseif (phorum_page == "post" && isset($_POST["message_id"])) {
			$messageid = $_POST["message_id"];
            $message = phorum_db_get_message($messageid);
            $userid = $message["user_id"];
        }

        // No user found? Then return.
        if (! $userid) return;
        $user = phorum_api_user_get($userid, false);
        if (! $user) return;
      
        // Put the available avatars in the template data.
        $PHORUM['DATA']['mod_avatar'] = $PHORUM["mod_avatar"];
        $PHORUM['DATA']['mod_avatar_files'] = $user["mod_avatar"]["avatars"];

        // If mod_namedposting is enabled, call it to get alternate avatar info.
        if (@$PHORUM["DATA"]["mod_avatar"]["enable_namedposting"] == true) {
            mod_namedposting_setup_avatar();
        }

        // We only show avatar options in case we have avatars for the user.
        $PHORUM['DATA']['mod_avatar']['show'] = 
            (isset($PHORUM['DATA']['mod_avatar_files']) &&
             count($PHORUM['DATA']['mod_avatar_files']) > 0);

        // On the moderation and edit pages, we want to set the default
        // to be whatever avatar they picked.
        if ($messageid > 0 && isset($PHORUM['DATA']['mod_avatar_files'])) {
            foreach ($PHORUM['DATA']['mod_avatar_files'] as $id => $avatar) {
                $PHORUM['DATA']['mod_avatar_files'][$id]["default"] = 
                    (isset($message["meta"]["mod_avatar"]) &&
                     $id == $message["meta"]["mod_avatar"]);
            }
        }
    }

    $PHORUM['DATA']['mod_avatar']['enabled'] = true;
}

// Insert the pull down list with avatars inside the posting form.
function mod_avatar_tpl_editor_after_subject()
{
    $PHORUM = $GLOBALS["PHORUM"];

    if (! isset($PHORUM["DATA"]["mod_avatar"]["show"]) ||
        ! $PHORUM["DATA"]["mod_avatar"]["show"]) return;
    ?>
    <tr>
      <td>
        <?php print $PHORUM["DATA"]["LANG"]["mod_avatar"]["Avatar"]?>:&nbsp;
      </td>
      <td>
        <select name="mod_avatar">
          <option value="0">&lt; <?php print $PHORUM["DATA"]["LANG"]["mod_avatar"]["NoAvatar"]?> &gt;</option><?php
        
          foreach ($PHORUM["DATA"]["mod_avatar_files"] as $id => $data) {
            print '<option value="' . $data["avatarid"] . '" ';
            if ($data["default"]) print 'selected="selected" ';
            print '>' . htmlspecialchars($data["label"]) . '</option>'; 
          } ?>
        </select>
      </td>
     </tr> <?php
}

function mod_avatar_post_message($message){
	$PHORUM=$GLOBALS["PHORUM"];
    if (isset($_POST["mod_avatar"]) && $_POST["mod_avatar"] >= 1){ 
// If they are trying to set an avatar, make sure the file is in 
// their avatar list. 

$message["meta"]["mod_avatar"] = $_POST["mod_avatar"]; 

} 
else { 
unset($message["meta"]["mod_avatar"]); 
} 

    return $message;
}

function mod_avatar_read($messages){
	GLOBAL $PHORUM;

    // if the user doesn't want to show avatars, we can disable them here
    $disable = false;
    if (isset($PHORUM["user"]["mod_avatar"]["disable_avatar_display"])) {
        $disable = $PHORUM["user"]["mod_avatar"]["disable_avatar_display"];
    }

    foreach ($messages as $messageid => $message) {
        // if the user has avatars disabled, don't display them
        if ($disable){
            $messages[$messageid]["mod_avatar"] = false;
        }
        elseif (isset($message["meta"]["mod_avatar"])) {
            // first we should check if the avatar still exists
            // if it does, no problem
            $filelist = phorum_db_get_user_file_list($message["user_id"]);
            if (isset($filelist[$message["meta"]["mod_avatar"]])) {
                $messages[$messageid]["mod_avatar"] = phorum_get_url(PHORUM_FILE_URL, "file=" . $message["meta"]["mod_avatar"]);
            }
            // if not, we will show the users default one instead (if they have one of those)
            else {
                $user = phorum_user_get($message["user_id"], false);
                if (isset($user["mod_avatar"]["default_avatar"]) && isset($filelist[$user["mod_avatar"]["default_avatar"]])) {
                    $messages[$messageid]["meta"]["mod_avatar"] = $user["mod_avatar"]["default_avatar"];
                    // update the post so we don't have to constantly do this extra work
                    $messagetemp = phorum_db_get_message($messageid);
                    $messagetemp["meta"]["mod_avatar"] = $user["mod_avatar"]["default_avatar"];
                    phorum_db_update_message($messageid, $messagetemp);
                }
                else {
                    $messages[$messageid]["mod_avatar"] = false;
                }
            }
        }
        else {
            $messages[$messageid]["mod_avatar"] = false;
        }
    }
    return $messages;
}

// profile hook
function mod_avatar_profile($profile)
{
    if (isset($profile["mod_avatar"]["default_avatar"])) {
        $profile["mod_avatar_url"] = phorum_get_url(PHORUM_FILE_URL, "file=" . $profile["mod_avatar"]["default_avatar"]);
    }
    return $profile;
}

?>
