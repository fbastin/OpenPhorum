<?php
/*
  * Admin settings:
    * Option of creating a user gallery when the user registers, or when they first access their account (or maybe give them an option asking if the user wants the user gallery)
    * Option on whether or not to moderate (pre-approve) uploaded images
    * Option of having the albums viewable by Public or Members Only
    * Maximum size for uploading an image
    * Maximum size for a user gallery
    * If option to do so is selected, moderators/administrator are alerted to new images, in order to keep an eye on the content.
    * thumbnails?
    * animated gifs???
*/

if (!defined('PHORUM_ADMIN')) return;

// $lang = $PHORUM["DATA"]["LANG"]["mod_user_image_gallery"];  //?

// Hey! Module settings.php files don't use language files! What the?

// I just went through all the work of putting all the strings into a language file. I'M NOT UNDOING THAT.


$language = $PHORUM['language'];
if ( ! file_exists('./mods/user_image_gallery/lang/' . $language . '.php') ):
  $language = 'english';
endif;
include_once('./mods/user_image_gallery/lang/' . $language . '.php');

// traditionally, the language strings are copied to a variable named $lang -- but $lang is already being used for something else right now,
//  so we'll be using $langx

$langx = $PHORUM["DATA"]["LANG"]["mod_user_image_gallery"];  


// this script performs two functions which are basically unrelated
// so, to avoid confusion, each function is located in a separate file

if (isset($_POST['moderate_images'])):
  require('mods/user_image_gallery/settings_moderate_images.php');
else:
  require('mods/user_image_gallery/settings_settings.php');
endif;
