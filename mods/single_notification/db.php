<?php

if(!defined("PHORUM")) return;

global $PHORUM;

# The database scheme version, which is used to handle
# installation and upgrades from the module.
define("SNOTIFY_DB_VERSION", 1);

# The table name for storing single_notify information.
$PHORUM['single_notify_table'] =
    "{$PHORUM["DBCONFIG"]["table_prefix"]}_single_notify";

# Check if an installation or upgrade of the database scheme is needed.
function mod_single_notification_db_init()
{
    global $PHORUM;

    $layerpath = "./mods/single_notification/db/{$PHORUM["DBCONFIG"]["type"]}";

    // Allow db layers to provide an initialization script of their own.
    // The main goal for this script is to allow a db layer to override the
    // $PHORUM['single_notify_table'] variable.
    if (file_exists("$layerpath/db.php")) require_once("$layerpath/db.php");

    $version = isset($PHORUM["mod_single_notify_installed"])
        ? $PHORUM["mod_single_notify_installed"] : 0;

    while ($version < SNOTIFY_DB_VERSION)
    {
        // Initialize the settings array that we will be saving.
        $version++;
        $settings = array( "mod_single_notify_installed" => $version );

        $sqlfile = "$layerpath/$version.php";

        if (! file_exists($sqlfile)) {
            print "<b>Unexpected situation on installing " .
                  "the Single Notification module</b>: unable to find the database " .
                  "scheme setup script " . htmlspecialchars($sqlfile);
            return false;
        }

        $sqlqueries = array();
        include($sqlfile);

        if (count($sqlqueries) == 0) {
            print "<b>Unexpected situation on installing " .
                  "the Single Notification module</b>: could not read any SQL " .
                  "queries from file " . htmlspecialchars($sqlfile);
            return false;
        }
        $err = phorum_db_run_queries($sqlqueries);
        if ($err) {
            print "<b>Unexpected situation on installing " .
                  "the Single Notification module</b>: running the " .
                  "install queries from file " . htmlspecialchars($sqlfile) .
                  " failed: " . htmlspecialchars($err);
            return false;
        }

        // Save our settings.
        if (!phorum_db_update_settings($settings)) {
            print "<b>Unexpected situation on installing " .
                  "the Single Notification module</b>: updating the " .
                  "mod_single_notify_installed setting failed";
            return false;
        }
    }

    return true;
}

function mod_single_notification_db_add($emails,$forum,$thread) {
	global $PHORUM;
	
	$inserts=array();
	foreach($emails as $email) {
		$inserts[]="('$email',$forum,$thread)";
	}
	
	$query = "INSERT INTO {$PHORUM['single_notify_table']} (email,forum_id,thread_id) VALUES";
	$query.=implode(",",$inserts);
	
    phorum_db_interact(
        DB_RETURN_RES, 
        $query,
        NULL, DB_MASTERQUERY
    );	
}

function mod_single_notification_db_del($email,$forum,$thread=0) {
	global $PHORUM;
	
	
	$query = "DELETE FROM {$PHORUM['single_notify_table']} WHERE email = '$email' AND forum_id = $forum";
	if($thread > 0) {
		$query.=" AND thread_id = $thread";
	}
    phorum_db_interact(
        DB_RETURN_RES, 
        $query,
        NULL, DB_MASTERQUERY
    );	
}

function mod_single_notification_db_check($emails,$forum,$thread) {
	global $PHORUM;
	
	if(!count($emails)) {
		return array();
	}
	
	$emails_str = implode("','",$emails);
	
	$query="SELECT email FROM {$PHORUM['single_notify_table']} WHERE email IN('$emails_str') AND forum_id = $forum AND thread_id = $thread";
	
    $records = phorum_db_interact(
        DB_RETURN_ASSOCS,
        $query
    );
    
    return $records;
	
}
?>
