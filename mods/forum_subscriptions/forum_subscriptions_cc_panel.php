<?php
        
if(!defined("PHORUM")) return;

function phorum_mod_forum_subscriptions_functions_cc_panel($data) {
    
    global $PHORUM;

    // pull in the mail queue database functions
    require_once ("./mods/forum_subscriptions/db_functions.php");
    
    // process any post data
    if (!empty($_POST["forum_subscriptions_form"])) {
        if ($_POST["forum_subscriptions_form"] == "settings" 
            && !empty($PHORUM["phorum_mod_forum_subscriptions"]["allow_user_unsubscribe_self"])) {
            // process the settings form
            $PHORUM["DATA"]["PROFILE"]["phorum_mod_forum_subscriptions_user_unsubscribe_setting_self"] = $_POST["phorum_mod_forum_subscriptions_user_unsubscribe_setting_self"];
            phorum_api_user_save(array(
                "user_id"         => $PHORUM["user"]["user_id"],
                "phorum_mod_forum_subscriptions_user_unsubscribe_setting_self" => $PHORUM["DATA"]["PROFILE"]["phorum_mod_forum_subscriptions_user_unsubscribe_setting_self"]
                ));
            $PHORUM["DATA"]["OKMSG"] = $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["ForumSubscriptionsSettingsUpdated"];
        } elseif ($_POST["forum_subscriptions_form"] == "subscriptions") {
            // process the individual subscriptions
            if (!empty($_POST["phorum_mod_forum_subscriptions_forums"]) && count($_POST["phorum_mod_forum_subscriptions_forums"])) {
                foreach ($_POST["phorum_mod_forum_subscriptions_forums"] as $forum_id => $subscription) {
                    if ($subscription["sub_type"] == PHORUM_MOD_FORUM_SUB_SUBSCRIBE_NONE 
                        || $subscription["frequency"] == PHORUM_MOD_FORUM_SUB_FREQUENCY_NEVER) {
                        phorum_mod_forum_subscriptions_db_subscriptions_delete($PHORUM["user"]["user_id"],(int)$forum_id);
                    } else {
                        //if ($forum_id == 0) phorum_mod_forum_subscriptions_db_subscriptions_delete($PHORUM["user"]["user_id"]);
                        phorum_mod_forum_subscriptions_db_subscriptions_save($PHORUM["user"]["user_id"],(int)$forum_id,(int)$subscription["sub_type"],(int)$subscription["frequency"] );
                    }
                }
                $PHORUM["DATA"]["OKMSG"] = $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["ForumSubscriptionsUpdated"];
            }
        }
    }
    
    if (isset($PHORUM["args"]["fsub_forum"])) {
        if ($PHORUM["args"]["fsub_forum"] != 0) {
            $forum = phorum_db_get_forums($PHORUM["args"]["fsub_forum"]);
            
            if ($forum[$PHORUM["args"]["fsub_forum"]]["forum_id"] != $forum[$PHORUM["args"]["fsub_forum"]]["vroot"]) 
                $all_forums_subscription = phorum_mod_forum_subscriptions_db_subscriptions_get_subscriber($PHORUM["user"]["user_id"],$forum[$PHORUM["args"]["fsub_forum"]]["vroot"]);
        }
        if (empty($all_forums_subscription)) {
            phorum_mod_forum_subscriptions_db_subscriptions_save($PHORUM["user"]["user_id"],(int)$PHORUM["args"]["fsub_forum"],PHORUM_MOD_FORUM_SUB_SUBSCRIBE_ALL,$PHORUM["phorum_mod_forum_subscriptions"]["default_frequency"]);
            $PHORUM["DATA"]["OKMSG"] = $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["ForumSubscriptionsUpdated"];
        } else {
            $PHORUM["DATA"]["ERROR"] = $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["AlreadySubscribedToAllForums"];
        }
    }
    $data["template"] = "forum_subscriptions::cc_panel";
    $data["handled"] = true;
    if (!empty($PHORUM["phorum_mod_forum_subscriptions"]["allow_user_unsubscribe_self"]))
        $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["allow_user_unsubscribe_self"] = true;
    $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"] = phorum_mod_forum_subscriptions_get_user_forums();
    
    if (count($PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"])) {
        if (!empty($PHORUM["phorum_mod_forum_subscriptions"]["allow_daily_digests"]))
            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["allow_daily_digests"] = true;
        if (!empty($PHORUM["phorum_mod_forum_subscriptions"]["allow_weekly_digests"]))
            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["allow_weekly_digests"] = true;
        if (!empty($PHORUM["phorum_mod_forum_subscriptions"]["send_only_new_threads"])) {
            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["send_only_new_threads"] = true;
        } else {
            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["send_only_new_threads"] = false;
        }
    
        $subscriptions = phorum_mod_forum_subscriptions_db_subscriptions_get_subscriber($PHORUM["user"]["user_id"]);
    
        if (!empty($subscriptions)) {
            foreach ($PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"] as $key => $forum) {
                if ($forum["vroot"] == $forum["forum_id"]) {
                    if ($forum["forum_id"] != 0){
                        if (!empty($subscriptions[$forum["forum_id"]]) && $PHORUM["vroot"] != $forum["vroot"]) {
                            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"][$key]["name"] = $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"][$key]["name"] . ": " . $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["AllForums"];
                        } elseif ($PHORUM["vroot"] == $forum["vroot"]) {
                            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"][$key]["name"] = $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["AllForums"];
                        }
                    }
                    if (empty($subscriptions[$forum["forum_id"]]) && $PHORUM["vroot"] != $forum["vroot"])
                        $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"][$key]["vroot"] = $PHORUM["vroot"];
                }
                if (!empty($subscriptions[$forum["forum_id"]])) {
                    $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"][$key]["sub_type"] = $subscriptions[$forum["forum_id"]]["sub_type"];
                    $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS"][$key]["frequency"] = $subscriptions[$forum["forum_id"]]["frequency"];
                    if ($forum["vroot"] == $forum["forum_id"]) {
                        if (!empty($PHORUM["phorum_mod_forum_subscriptions"]["send_only_new_threads"]))
                            $subscriptions[$forum["forum_id"]]["sub_type"] = PHORUM_MOD_FORUM_SUB_SUBSCRIBE_NEW_THREAD;
                        $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["ALL_FORUMS"][$forum["forum_id"]]["sub_type"] = ($subscriptions[$forum["forum_id"]]["sub_type"] == PHORUM_MOD_FORUM_SUB_SUBSCRIBE_ALL) 
                            ? $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["ThreadsAndReplies"]
                            : $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["NewThreadsOnly"];
                        if ($subscriptions[$forum["forum_id"]]["frequency"] == PHORUM_MOD_FORUM_SUB_FREQUENCY_IMMEDIATE) {
                            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["ALL_FORUMS"][$forum["forum_id"]]["frequency"] = $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["Immediate"];
                        } elseif ($subscriptions[$forum["forum_id"]]["frequency"] == PHORUM_MOD_FORUM_SUB_FREQUENCY_DAILY) {
                            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["ALL_FORUMS"][$forum["forum_id"]]["frequency"] = $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["DailyDigests"];
                        } elseif ($subscriptions[$forum["forum_id"]]["frequency"] == PHORUM_MOD_FORUM_SUB_FREQUENCY_WEEKLY) {
                            $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["ALL_FORUMS"][$forum["forum_id"]]["frequency"] = $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["WeeklyDigests"];
                        }
                    }
                }
            }
        }
    } else {
        $PHORUM["DATA"]["PHORUM_MOD_FORUM_SUBSCRIPTIONS"]["FORUMS_EMPTY"] = true;
    }
    $PHORUM["DATA"]["TMP"]["MetaPanel"] = "options";
    
    return $data;
}

//get a list of all forums and folders
function phorum_mod_forum_subscriptions_get_user_forums($check_posts = FALSE) {
	
    global $PHORUM;
    
    $forum_picker = array();
    $vroots = array();
    
    // build forum drop down data
    require_once('./include/api/forums.php');
    if (empty($PHORUM["phorum_mod_forum_subscriptions"]["show_vroot_subscriptions"])) {
        $forums = phorum_api_forums_by_vroot($PHORUM["vroot"]);
    } else {
        $forums = phorum_api_forums_get();
    }
    
    $subscriptions = phorum_mod_forum_subscriptions_db_subscriptions_get_subscriber($PHORUM["user"]["user_id"]);
    
    if (empty($PHORUM["vroot"])) {
        $forums[0] = array(
            "name" => $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["AllForums"],
            "forum_id" => 0,
            "vroot" => 0,
            "folder_flag" => 1,
            "active" => 1,
            "parent_id" => 0,
            "forum_path" => array(0=>$PHORUM["DATA"]["LANG"]["forum_subscriptions"]["AllForums"]),
            );
    } elseif (!empty($PHORUM["phorum_mod_forum_subscriptions"]["show_vroot_subscriptions"])) {
        $site_name = phorum_db_interact(
            DB_RETURN_VALUE,
            "SELECT data
             FROM " . $PHORUM['settings_table'] . "
             WHERE name = 'title'
             LIMIT 1"
             );
        $suffix = (!empty($subscriptions[0])) 
            ? ": " . $PHORUM["DATA"]["LANG"]["forum_subscriptions"]["AllForums"] : "";
        $forums[0] = array(
            "name" => $site_name . $suffix,
            "forum_id" => 0,
            "vroot" => 0,
            "folder_flag" => 1,
            "active" => 1,
            "parent_id" => 0,
            "forum_path" => array(0=>$site_name . $suffix),
            );
    }
    
    foreach($forums as $forum){
        if ((empty($forum["folder_flag"]) 
            && !phorum_api_user_check_access(PHORUM_USER_ALLOW_READ, $forum["forum_id"]))
            || (!empty($PHORUM["phorum_mod_forum_subscriptions"]["ignore_selected_forums"])
                && !empty($PHORUM["phorum_mod_forum_subscriptions"]["forums_to_ignore"][$forum["forum_id"]]))
            || (!empty($PHORUM["phorum_mod_forum_subscriptions"]["show_only_subscriptions"]) 
                && empty($forum["folder_flag"])
                && empty($subscriptions[$forum["forum_id"]]))
            || (empty($subscriptions[$forum["forum_id"]])
                && empty($forum["folder_flag"])
                && $forum["vroot"] != $PHORUM["vroot"])
            ) continue;
        $tmp_forums[$forum["vroot"]][$forum["forum_id"]]["forum_id"] = $forum["forum_id"];
        $tmp_forums[$forum["vroot"]][$forum["forum_id"]]["parent"] = $forum["parent_id"];
        if ($forum["forum_id"] != $forum["vroot"])
            $tmp_forums[$forum["vroot"]][$forum["parent_id"]]["children"][] = $forum["forum_id"];
        $tmp_vroots[$forum["vroot"]] = $forum["vroot"];
        if (empty($forums[$forum["parent_id"]]["childcount"])) {
            $forums[$forum["parent_id"]]["childcount"] = 1;
        } else {
            $forums[$forum["parent_id"]]["childcount"]++;
        }
    }
    $order = array();
    
    if (count($tmp_vroots)) {
        
        $vroots = array();
        
        if (isset($tmp_vroots[$PHORUM["vroot"]])) {
            $vroots[] = $tmp_vroots[$PHORUM["vroot"]];
            unset($tmp_vroots[$PHORUM["vroot"]]);
        }
        
        ksort($tmp_vroots);
        
        $vroots = array_merge($vroots, $tmp_vroots);
        
        foreach ($vroots as $vroot) {
            $stack = array();
            $curr_id = $vroot;
            while(count($tmp_forums[$vroot])){
                if(empty($seen[$curr_id])){
                    //if($curr_id!=$vroot){
                        if ($forums[$curr_id]["active"]) {
                            $order[$curr_id] = $forums[$curr_id];
                        }
                        $seen[$curr_id] = true;
                    //}
                }
                array_unshift($stack, $curr_id);
                $data = $tmp_forums[$vroot][$curr_id];
                if(isset($data["children"])){
                    if(count($data["children"])){
                        $previous_id = $curr_id;
                        $i=0;
                        foreach ($data["children"] as $child_id) {
                            if (empty($forums[$child_id]["folder_flag"])) {
                                array_splice($tmp_forums[$vroot][$curr_id]["children"],$i,1);
                                $curr_id = $child_id;
                                break;
                            }
                            $i++;
                        }
        
                        if ($curr_id == $previous_id) {
                            $curr_id = array_shift($tmp_forums[$vroot][$curr_id]["children"]);
                        }
                    } else {
                        unset($tmp_forums[$vroot][$curr_id]);
                        array_shift($stack);
                        $curr_id = array_shift($stack);
                    }
                } else {
                    unset($tmp_forums[$vroot][$curr_id]);
                    array_shift($stack);
                    $curr_id = array_shift($stack);
                }
                if(!is_numeric($curr_id)) break;
            }
        }
    }
    $reverse_order = array_reverse($order, true);
    
    reset($reverse_order);
    while (list(, $forum) = each($reverse_order)) {
        if (!empty($forum["folder_flag"]) && empty($forum["childcount"]) && empty($subscriptions[$forum["forum_id"]])) {
            if (isset($forum["parent_id"])) { 
                $reverse_order[$forum["parent_id"]]["childcount"] -= 1;
            }
            unset($reverse_order[$forum["forum_id"]]);
        }
    }
    $order = array_reverse($reverse_order, true);
    
    foreach($order as $forum)
    {
        if($forum["folder_flag"])
        {
            if(empty($forum["childcount"]) && empty($subscriptions[$forum["forum_id"]])) continue;
            $url = phorum_get_url(PHORUM_INDEX_URL, $forum["forum_id"]);
        } else {
            $url = phorum_get_url(PHORUM_LIST_URL, $forum["forum_id"]);
        }

        $indent = count($forum["forum_path"]) - 2;
        
        if ($forum["vroot"] != $forum["forum_id"]) $indent += 1;
        
        if($indent < 0) $indent = 0;
        
        $forum_picker[$forum["forum_id"]] = array(
            "forum_id" => $forum["forum_id"],
            "parent_id" => $forum["parent_id"],
            "folder_flag" => $forum["folder_flag"],
            "vroot" => $forum["vroot"],
            "name" => $forum["name"],
            "stripped_name" => strip_tags($forum["name"]),
            "indent" => $indent,
            "indent_spaces" => str_repeat("&nbsp;", $indent*3),
            "url" => $url,
            "path" => $forum["forum_path"]
        );

    }
    
    return $forum_picker;
    
}
?>
