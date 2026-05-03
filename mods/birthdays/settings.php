<?php
/*  mod_birthdays: Shows a list of users celebrates birthday today
    author: Jürgen Hansen (mail@jhansen.info)
          based on mod_forumstats by Thomas Seifert (thomas.seifert@mysnip.de)
    license: You are free to use, modify, or redistribute this code so long 
           as I am given credit for the original development somewhere.
                 This code comes with absolutly no warranty.
*/
    if(!defined("PHORUM_ADMIN")) return;

// set defaults
if (!isset($GLOBALS['PHORUM']["mod_birthdays"]["days_check"])) {
    $GLOBALS['PHORUM']["mod_birthdays"]["days_check"] = 30;
}
if (!isset($GLOBALS['PHORUM']["mod_birthdays"]["caching_enabled"])) {
    $GLOBALS['PHORUM']["mod_birthdays"]["caching_enabled"] = 0;
}

    print "<h1>Birthdays Module Settings</h1>";

    // save settings
    if(count($_POST)){
        if(!empty($_POST["show_pages"])){
        $PHORUM["mod_birthdays"]["show_pages"]=(isset($_POST["show_pages"])) ? $_POST["show_pages"] : array();
        $PHORUM["mod_birthdays"]["caching_enabled"]=(empty($_POST["caching_enabled"])) ?0 : 1;       
        $PHORUM["mod_birthdays"]["hide_box"]=(empty($_POST["hide_box"])) ?0 : 1;      
        if ( (int)$_POST["days_check"] > 365 ) { $_POST["days_check"] = 365; }
        $PHORUM["mod_birthdays"]["days_check"]=((int)$_POST["days_check"] > 0) ? (int)$_POST["days_check"] : 1;      
        } else {
            $error = "No pages selected.  Turn the module off instead.";
        }

        if(empty($error)){
            if(!phorum_db_update_settings(array("mod_birthdays"=>$PHORUM["mod_birthdays"]))){
                $error="Database error while updating settings.";
            } else {
                echo "Settings Updated<br />";
            }
        } 
    }

    include_once "./include/admin/PhorumInputForm.php";
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "birthdays");

    if (!empty($error)){
        echo "$error<br />";
    }
    $frm->addbreak("caching");
    $frm->addmessage("If you enable caching the data for the birthday list's data it will be read from the database only once per day and stored in a file. (Unless a user updates their profile, in which case a new cache is written.)  As this mod uses quite a lot of queries this will help performance hugely, especially if your database is busy.");
    $frm->addrow("Enable caching? (recommended)", $frm->checkbox("caching_enabled", "1", "",$PHORUM["mod_birthdays"]["caching_enabled"]));

    $frm->addbreak("number of days");
    $frm->addmessage("Specify how many days into the future you want to look for birthdays. (1-365)");
    $frm->addrow("Number of days to look for birthdays (default 30)", $frm->text_box("days_check", $PHORUM["mod_birthdays"]["days_check"]));

    $frm->addbreak("no birthdays");
    $frm->addmessage("Decide whether or not to hide the box when no birthdays to display.");
    $frm->addrow("Hide box?", $frm->checkbox("hide_box", "1", "",$PHORUM["mod_birthdays"]["hide_box"]));

    $frm->addbreak("pages to display");
    $frm->addmessage("Select the pages you would like to display the birthdaylist on, it will appear at the bottom of each selected page.");
    $pages=array("index","read","list","post","search","control");
    foreach($pages as $page){
        // $list[$forum_id]=$forum["name"];
        $checked = (@in_array($page, $PHORUM["mod_birthdays"]["show_pages"]))? 1 : 0;
        $frm->addrow($page, $frm->checkbox("show_pages[]", $page, "", $checked));
    }

    $frm->show();
?>
