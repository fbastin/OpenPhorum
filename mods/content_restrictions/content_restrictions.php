<?php
	
	if (!defined("PHORUM")) return;

function phorum_mod_content_restrictions_check_post($args)
{
    global $PHORUM;

    $settings  = $PHORUM['mod_content_restrictions'];
    $lang      = $PHORUM['DATA']['LANG']['mod_content_restrictions'];
    
    list ($message, $error) = $args;
    // Return if another module already set an error.
	if (!empty($error)) return $args;
	
	// first check to see if we should exempt premium subscribers from this module's restrictions
	if ((int)$settings['no_restriction_for_subscribers']){
		// is the user a subscriber?
		if ((int)$PHORUM["user"]["subscriber"]){
			return ($args);
		}
	}
	
	
	// when did the user register?
	$tsregistered = (int)$PHORUM["user"]["date_added"];
	
	// get restrictions to test against based on user registration
	if (!$tsregistered){ // user is UNREGISTERED
		$max_hyperlinks = $settings["max_hyperlinks_unregistered"];
		$max_message_length = (int)$settings["max_bytes_unregistered"];
		$hyperlink_error_field = "max_hyperlinks_unregistered";
		$max_length_error_field = "max_bytes_unregistered";
	}
	else{  // user is REGISTERED
		// Timestamp from which the user is considered trusted.
		$tsvaliddate = $tsregistered + (int)$settings['registered_days_before_trust'] * 60 * 60 * 24; // add configured time to user's reg date
		// if admin hasn't set a trusted days value assume user is trusted, otherwise check to see if they are old enough to be trusted
		if (!(int)$settings['registered_days_before_trust'] || time() >= $tsvaliddate){ 
			// user is trusted
			$max_hyperlinks = (int)$settings["max_hyperlinks_registered_trusted"];
			$max_message_length = (int)$settings["max_bytes_registered_trusted"];
			$hyperlink_error_field = "max_hyperlinks_registered_trusted";
			$max_length_error_field = "max_bytes_registered_trusted";
		}
		else{
			// user is untrusted
			$max_hyperlinks = (int)$settings["max_hyperlinks_registered_untrusted"];
			$max_message_length = (int)$settings["max_bytes_registered_untrusted"];
			$hyperlink_error_field = "max_hyperlinks_registered_untrusted";
			$max_length_error_field = "max_bytes_registered_untrusted";
		}
	}
	
	// RESTRICTION CHECKING
	// ********************
	// MAX LENGTH OF MESSAGE
	if ($max_message_length > 0){ // only check if admin has entered a value
		if (strlen($message["body"]) > $max_message_length){
			$error_msg = $lang[$max_length_error_field];
			return array($message, $error_msg);
		}
	}
	
	// HYPERLINKS
	if ($max_hyperlinks != 0){ // only check if admin has entered a value
		// first count and remove all bbcode [url] tags and everything between them
		$parsed_message = preg_replace('@\[url(.*?)\[/url\]@', '', $message["body"],-1,$num_bbcodelinks);
		//$debug_1 = $parsed_message;

		// now count and remove all remaining http*://*. strings to catch http and https links
		$parsed_message = preg_replace('/http(.*?)\:\/\/(.*?)\./i', '', $parsed_message, -1, $num_hyperlinks);
		//$debug_2 = $parsed_message;

		// now count and remove all remaining www.* strings
		$parsed_message = preg_replace('/www(.*?)\./i', '', $parsed_message, -1, $num_wwwlinks);
		//$debug_3 = $parsed_message;

		// count all mailto.* strings
		preg_match_all('@\[email(.*?)\[/email\]@', $parsed_message, $matches);
		$num_mailtolinks = count($matches[0]);
		//$debug_4 = $parsed_message;

		// now try and detect raw email addresses
		$pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';
		// preg_match_all returns an associative array
		preg_match_all($pattern, $parsed_message, $emailmatches);
		$num_raw_email_addresses = count($emailmatches[0]);

		$total_num_links = $num_hyperlinks + $num_bbcodelinks + $num_wwwlinks + $num_mailtolinks + $num_raw_email_addresses;
		if ($total_num_links > $max_hyperlinks) {
			$error_msg = $lang[$hyperlink_error_field];
			$error_msg = str_replace("%" . $hyperlink_error_field. "%", $settings[$hyperlink_error_field], $error_msg);
			$error_msg .= " (registered '$tsregistered'<br/>num_hyperlinks = '$num_hyperlinks' | num_bbcodelinks = '$num_bbcodelinks' | num_wwwlinks = '$num_wwwlinks' | num_mailtolinks = '$num_mailtolinks' | num_raw_email_addresses = $num_raw_email_addresses | subscriber = '" . (int)$PHORUM["user"]["subscriber"] . ")";
			//$error_msg .= "<br/>debug_1 = $debug_1";
			//$error_msg .= "<br/>debug_2 = $debug_2";
			//$error_msg .= "<br/>debug_3 = $debug_3";
			return array($message, $error_msg);
		}	
	}	
	
	return $args; // fallback - return what we were sent, unchanged.

/*
    // ----------------------------------------------------------------------
    // Check denying of images and/or markup code
    // ----------------------------------------------------------------------

    // We only need to run this code if either deny_images or deny_markup
    // is in use.
    if (!isset($user['error']) && (
          !empty($settings['deny_images']) ||
          !empty($settings['deny_markup'])
        )) {

        // Format the signature.
        include_once('./include/format_functions.php');
        $formatted = phorum_format_messages(array(0 => array(
            'author'  => '',
            'email'   => '',
            'subject' => '',
            'body'    => $user['signature']
        )));
        $signature = $formatted[0]['body'];

        // Remove newlines for better matching.
        $signature = str_replace("\n", "", $signature);

        // Check for images in the signature.
        if (!empty($settings['deny_images']) &&
            preg_match('/<\s*img\s/', $signature)) {
            $user['error'] = $lang['deny_images'];
        }

        // Check for markup code in the signature.
        if (!isset($user['error']) && !empty($settings['deny_markup']))
        {
            $stripped = strip_tags($signature, '<br>');
            if ($signature != $stripped)
            {
                // The user has used markup. Check if the users are allowed
                // to use markup after being signed up for a certain amount
                // of time.
                if (!empty($settings['markup_user_registered_days']))
                {
                    // Registration timestamp.
                    $tsregistered = $PHORUM["DATA"]["PROFILE"]["date_added"];

                    // Timestamp from which the user is allowed to use markup.
                    $tsvaliddate =
                      $tsregistered +
                      $settings['markup_user_registered_days'] * 60 * 60 * 24;

                    if (time() <= $tsvaliddate)
                    {
                        // Format the error to show user when they can start
                        // using markup.
                        $format = $PHORUM['short_date_time'];
                        $user['error'] = str_replace(
                            '%date%', phorum_date($format, $tsvaliddate),
                            $lang['markup_user_registered_days']
                        );
                    }
                }
                else
                {
                    $user['error'] = $lang['deny_markup'];
                }
            }
        }
    }

*/	
}

?>
