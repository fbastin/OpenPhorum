<?php

if(!defined("PHORUM")) return;

function social_share_mod_css_register($data) {

    $data['register'][] = array(
        'module' => 'social_share',
        'where'  => 'after',
        'source' => 'file(mods/social_share/social_share.css)'
    );
    return $data;
}

function social_share_mod_thread($message) {
global $PHORUM;
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

	$url=urlencode($url);
	$subject = $message['subject'];
	$subject = preg_replace("/<img[^>]+\>/i", " ", $subject);

	if ( $PHORUM['social_share']['share_twitter'] == 1 )
		{
		print "<a class=\"icon icon-share-twitter\" href=\"http://twitter.com/intent/tweet?source=sharethiscom&text=".$subject."&url=".$url."\"";
		if ( $PHORUM['social_share']['link_new_window'] == 1 )
			print " target=\"_blank\"";
		print ">Tweet</a>";
		}
        if ( $PHORUM['social_share']['share_facebook'] == 1 )
                {
                print "<a class=\"icon icon-share-facebook\" href=\"https://www.facebook.com/sharer.php?u=".$url."\"";
                if ( $PHORUM['social_share']['link_new_window'] == 1 )
                       print " target=\"_blank\"";
                print ">Facebook</a>";
                }
        if ( $PHORUM['social_share']['share_google-plus'] == 1 )
                {
                print "<a class=\"icon icon-share-google-plus\" href=\"https://plus.google.com/share?url=".$url."\"";
                if ( $PHORUM['social_share']['link_new_window'] == 1 )
                        print " target=\"_blank\"";
                print ">Google+</a>";
                }
}
?>
