<?php

// Get a session token from a one-time token
function phorum_mod_google_calendar_gdata_upgrade_token($token) {   
    $ch = curl_init("https://www.google.com/accounts/AuthSubSessionToken");   
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
    curl_setopt($ch, CURLOPT_FAILONERROR, true);   
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(   
        'Authorization: AuthSub token="' . trim($token) . '"'  
    ));   
    
    $result = curl_exec($ch);   
    curl_close($ch);   
    
    $splitStr = split("=", $result);   
    
    return trim($splitStr[1]);   
}

?>
