<?php

// Make sure that this script is loaded from the admin interface.
if (!defined('PHORUM_ADMIN')) return;

$forum_list_global = phorum_get_forum_info(1,0);

// Save settings in case this script is run after posting
// the settings form.
if (count($_POST)) {

    $PHORUM["mod_twitter"] = array(
        "consumer_key"  => (string)$_POST["consumer_key"],
        "consumer_secret"  => (string)$_POST["consumer_secret"],
        "user_token"  => (string)$_POST["user_token"],
        "user_secret"  => (string)$_POST["user_secret"],
        "new_posts" => (bool)$_POST["new_posts"],
    );

    $PHORUM["mod_twitter"]["forum_list"] = array();
    foreach ($_POST["forum_list"] as $v => $k ) {
        array_push($PHORUM["mod_twitter"]["forum_list"],$v);
    }

    if (!phorum_db_update_settings(array('mod_twitter'=>$PHORUM['mod_twitter']))) {
        $error = 'Database error while updating settings.';
    } else {
        phorum_admin_okmsg('Settings Updated');
    }
}

// We build the settings form by using the PhorumInputForm object.
include_once './include/admin/PhorumInputForm.php';
$frm = new PhorumInputForm('', 'post', 'Save');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'twitter');

// Here we display an error in case one was set by saving
// the settings before.
if (!empty($error)){
    phorum_admin_error($error);
}

$frm->addbreak('Edit settings for the Twitter module');


$frm->addmessage('Please register an application with twitter at <a href="http://dev.twitter.com/apps" target="_blank">this page</a> and get your consumer key and secret from there.');

$row = $frm->addrow
    ( 'Consumer key ',
      $frm->text_box
          ( 'consumer_key',
            $PHORUM['mod_twitter']['consumer_key'],
            30 ) );


$row = $frm->addrow
    ( 'Consumer secret ',
      $frm->text_box
          ( 'consumer_secret',
            $PHORUM['mod_twitter']['consumer_secret'],
            30 ) );

$frm->addmessage('These can be retrieved from the application page above when you click on &quot;My Access token&quot;');

$row = $frm->addrow
    ( 'User token ',
      $frm->text_box
          ( 'user_token',
            $PHORUM['mod_twitter']['user_token'],
            30 ) );

$row = $frm->addrow
    ( 'User secret ',
      $frm->text_box
          ( 'user_secret',
            $PHORUM['mod_twitter']['user_secret'],
            30 ) );

$row = $frm->addrow
    ( "Type of Posts ",
      $frm->select_tag
          ( "new_posts",
            array( "All Posts", "News Topics" ),
            $PHORUM["akismet"]["check_reg"] ) );
$frm->addhelp
    ( $row,
      'Type of Posts',
      "Select if you want to send all posts or only new topics to Twitter." );
      

$checkboxes = '';
if(!isset($PHORUM["mod_twitter"]["forum_list"])) {
    $PHORUM["mod_twitter"]["forum_list"]=array();
}
foreach ($forum_list_global as $forum_list_id => $forum_list_path) {

    $enabled = in_array($forum_list_id,$PHORUM["mod_twitter"]["forum_list"]);

    if ($enabled === FALSE)
        $enabled = 0;
    else
        $enabled = 1;

    $checkboxes .= $frm->checkbox("forum_list[$forum_list_id]", "1", "", $enabled) . " $forum_list_path<br/>";

}

$frm->addrow("Twitter new posts for which forums?", $checkboxes);

// Show settings form
$frm->show();

?>
