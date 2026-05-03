<?php

function mod_mobile(){

    global $PHORUM;

    if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    } else {
        return;
    }

    if(empty($PHORUM["mod_mobile"]["template"])) return;

    $words = explode("\n", $PHORUM["mod_mobile"]["ua_keywords"]);

    foreach($words as $w){
        $w = trim($w);
        if(!empty($w)){
            if(strpos($user_agent, $w) !== false){
                go_mobile();
                break;
            }
        }
    }
}

// Operations when on mobile
function go_mobile() {

    global $PHORUM;

    // Switch to mobile template
    phorum_switch_template($PHORUM["mod_mobile"]["template"]);

    // Check the requested page is supported by the template
    if (!empty($PHORUM["TMP"]["supported_pages"])) {
        $supported_pages = explode(",",$PHORUM["TMP"]["supported_pages"]);
        $supported_pages = array_merge($supported_pages, array("file","redirect"));
        if (!in_array(phorum_page, $supported_pages)) {
            $PHORUM["DATA"]["OKMSG"] = "This page is not supported on the mobile site";
            if($_SERVER["HTTP_REFERER"]){
                $PHORUM["DATA"]["URL"]["CLICKHERE"] = $_SERVER["HTTP_REFERER"];
            } else {
                $PHORUM["DATA"]["URL"]["CLICKHERE"] = $PHORUM["http_path"];
            }
          $PHORUM["DATA"]["CLICKHEREMSG"] = $PHORUM["LANG"]["Back"];
          phorum_output("message");
          exit();
        }
    }

    // Integrate mobile-specific translations
    foreach($PHORUM["DATA"]["LANG"]["mod_mobile"] as $k => $v){
        $PHORUM["DATA"]["LANG"][$k] = $v;
    }

    // Update misc settings
    $PHORUM["use_new_folder_style"] = true;
    $PHORUM["threaded_read"] = 0;
    $PHORUM["threaded_list"] = 0;
    $PHORUM["list_length_flat"] = 10;
    $PHORUM["read_length"] = 10;
    $PHORUM["cache_messages"] = 0;
    $PHORUM["reply_on_read_page"] = 0;
    $PHORUM["long_date_time"] = $PHORUM["short_date_time"];

    // Disable some hooks
    if(!empty($PHORUM["hooks"]["after_header"])){
        unset($PHORUM["hooks"]["after_header"]);
    }
    if(!empty($PHORUM["hooks"]["before_footer"])){
        unset($PHORUM["hooks"]["before_footer"]);
    }

    // Special handling for private messages
    if(phorum_page == "pm"){
        /**
         * create new URL for PM folders page for mobile templates to use
         */
        $PHORUM["DATA"]["MOD_MOBILE"]["URL"]["PM_SHOW_FOLDER_LIST"] = phorum_get_url(PHORUM_PM_URL, "folders=1");

        /**
         * Additionally, check the variable and set a template var if its set
         */
        if(!empty($PHORUM["args"]["folders"])){
            $PHORUM["DATA"]["PM_SHOW_FOLDERS"] = true;
        }
    }
}

?>
