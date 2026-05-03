<?php

if(!defined("PHORUM_ADMIN")) return;

//get a list of all forums and folders
function phorum_mod_forum_subscriptions_get_forums() {
	
    global $PHORUM;
    
    $forum_picker = array();

    // build forum drop down data
    require_once('./include/api/forums.php');
    $forums = phorum_api_forums_get();
    
    foreach($forums as $forum){
        $tmp_forums[$forum["vroot"]][$forum["forum_id"]]["forum_id"] = $forum["forum_id"];
        $tmp_forums[$forum["vroot"]][$forum["forum_id"]]["parent"] = $forum["parent_id"];
        $tmp_forums[$forum["vroot"]][$forum["parent_id"]]["children"][] = $forum["forum_id"];
        $vroots[$forum["vroot"]] = $forum["vroot"];
        
        if (empty($forums[$forum["parent_id"]]["childcount"])) {
            $forums[$forum["parent_id"]]["childcount"] = 1;
        } else {
            $forums[$forum["parent_id"]]["childcount"]++;
        }
    }
    
    ksort($vroots);
    
    $order = array();
    
    foreach ($vroots as $vroot) {
        $stack = array();
        $curr_id = 0;
        while(count($tmp_forums[$vroot])){
            if(empty($seen[$curr_id])){
                if($curr_id!=0){
                    if ($forums[$curr_id]["active"]) {
                        $order[$curr_id] = $forums[$curr_id];
                    }
                    $seen[$curr_id] = true;
                }
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
    
    foreach($order as $forum)
    {
        if($forum["folder_flag"])
        {
            $url = phorum_get_url(PHORUM_INDEX_URL, $forum["forum_id"]);
        } else {
            $url = phorum_get_url(PHORUM_LIST_URL, $forum["forum_id"]);
        }

        $indent = count($forum["forum_path"]) - 2;
        
        if ($forum["vroot"] > 0 && $forum["vroot"] != $forum["forum_id"]) $indent += 1;
        
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
