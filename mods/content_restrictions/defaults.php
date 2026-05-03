<?php

    if(!defined("PHORUM")) return;

    if (! isset($GLOBALS['PHORUM']["mod_content_restrictions"])) {
         $GLOBALS['PHORUM']["mod_content_restrictions"] = array();
    }
    
    
     $settings["max_hyperlinks_unregistered"]      = (int) $_POST["max_hyperlinks_unregistered"];
	        $settings["registered_days_before_trust"]      = (int) $_POST["registered_days_before_trust"];
	        $settings["max_hyperlinks_registered_untrusted"]      = (int) $_POST["max_hyperlinks_registered_untrusted"];
	        $settings["max_hyperlinks_registered_trusted"]      = (int) $_POST["max_hyperlinks_registered_trusted"];
	        $settings["max_bytes_unregistered"]      = (int) $_POST["max_bytes_unregistered"];
	        $settings["max_bytes_registered_untrusted"]      = (int) $_POST["max_bytes_registered_untrusted"];
	        $settings["max_bytes_registered_trusted"]      = (int) $_POST["max_bytes_registered_trusted"];
        $settings["no_restriction_for_subscribers"]      = (int) $_POST["no_restriction_for_subscribers"];

    $mod_content_restrictions_default = array(
        "max_hyperlinks_unregistered" => 0,
        "registered_days_before_trust" => 0,
        "max_hyperlinks_registered_untrusted" => 0,
        "max_hyperlinks_registered_trusted" => 0,
        "max_bytes_unregistered" => 0,
        "max_bytes_registered_trusted" => 0,
        "max_bytes_registered_untrusted" => 0,
        "no_restriction_for_subscribers" => 0
    );

    foreach ($mod_content_restrictions_default as $var => $default) {
        if (! isset($GLOBALS["PHORUM"]["mod_content_restrictions"][$var])) {
            $GLOBALS["PHORUM"]["mod_content_restrictions"][$var] = $default;
        }
    }

?>
