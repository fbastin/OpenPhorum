<?php

if (!defined("PHORUM_ADMIN")) return;

$error="";

if (count($_POST)) {

    $PHORUM["mod_mobile"] = array(
        "template" => (string)$_POST["template"],
        "ua_keywords" => (string)$_POST["ua_keywords"]
    );

    phorum_db_update_settings(array(
        "mod_mobile" => $PHORUM["mod_mobile"]
    ));
    phorum_admin_okmsg("Mobile Template settings updated");
}

if(empty($PHORUM["mod_mobile"])){
    $PHORUM["mod_mobile"] = array(
        "template" => "",
        "ua_keywords" => "iPhone\niPod\nAndroid\nMobile Safari\nOpera Mini"
    );
}

include_once "./include/admin/PhorumInputForm.php";

$frm = new PhorumInputForm ("", "post", "Save");

$frm->hidden("module", "modsettings");

$frm->hidden("mod", "mod_mobile");

$frm->addbreak("Automatic Mobile Template");

$frm->addrow("Mobile Template", $frm->select_tag("template", phorum_get_template_info(), $PHORUM["mod_mobile"]["template"]));

$frm->addrow("Mobile User Agents", $frm->textarea("ua_keywords", $PHORUM["mod_mobile"]["ua_keywords"], $cols=60, $rows=10, "style=\"width: 100%;\""), "top");

$frm->show();


?>
