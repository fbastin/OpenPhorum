<?php

function mod_twitter_url(){
    global $PHORUM;
    $settings = isset($PHORUM["mod_twitter"]) ? $PHORUM["mod_twitter"] : array();

    if (!empty($settings["username"])) {
        $PHORUM["DATA"]["URL"]["TWITTER"] = "http://www.twitter.com/".$settings["username"];
    }
}

function mod_twitter_posts($message){

    global $PHORUM;
    
    // check that all settings are given:
    $settings = $PHORUM["mod_twitter"];
    
    // Ensure forum_list and new_posts exist
    if (!isset($settings['forum_list'])) $settings['forum_list'] = array();
    if (!isset($settings['new_posts'])) $settings['new_posts'] = 0;

    if(empty($settings['consumer_key']) ||
       empty($settings['consumer_secret']) ||
       empty($settings['user_token']) ||
       empty($settings['user_secret'])
       ) {
       // no error shown but just return
       
       return $message;
       
    }

    if(($settings["new_posts"]==0 || $message["parent_id"]==0) 
       && in_array($message['forum_id'],$settings["forum_list"])){

        // Format the read URL.
        $url = phorum_get_url(
            PHORUM_FOREIGN_READ_URL,
            $message['forum_id'], $message['thread'], $message['message_id']
        );

        // Strip auth data from the URL, if availble.
        if (isset($_POST[PHORUM_SESSION_LONG_TERM])) {
            $url = preg_replace(
                '!,?' . PHORUM_SESSION_LONG_TERM.'=' .
                urlencode($_POST[PHORUM_SESSION_LONG_TERM]).'!',
                '', $url
            );
        }

        // fetch a tiny URL.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'url='.urlencode($url));
        curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php');
        $response = curl_exec($ch);
        if ($response && preg_match('!http://tinyurl\.com/\w+!', $response)) {
            $url = $response;
        }
        curl_close($ch);
        


        $status = $message["subject"]." ".$url;

        require './mods/twitter/tmhOAuth/tmhOAuth.php';
        $tmhOAuth = new tmhOAuth(array(
          'consumer_key' => $settings['consumer_key'],
          'consumer_secret' => $settings['consumer_secret'],
          'user_token' => $settings['user_token'],
          'user_secret' => $settings['user_secret'],
        ));
        
        $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
          'status' => $status
        ));


    }

    return $message;

}

?>