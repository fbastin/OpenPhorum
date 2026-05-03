<?php

if (!defined("PHORUM")) return;

phorum_mod_forum_subscriptions_db_upgrade_db_1();

function phorum_mod_forum_subscriptions_db_upgrade_db_1() {
    
    global $PHORUM;
    
    $PHORUM["phorum_mod_forum_subscriptions"]["db_version"] = 1;
    phorum_db_update_settings(array("phorum_mod_forum_subscriptions"=>$PHORUM["phorum_mod_forum_subscriptions"]));
            
    if (function_exists('event_logging_writelog')) {
        $log_message = "Successfully upgraded the Forum Subscriptions module's database to version 1";
        event_logging_writelog(array(
            "message"    => $log_message
        ));
    }
}

?>
