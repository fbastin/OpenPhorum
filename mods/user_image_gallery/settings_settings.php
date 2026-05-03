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

//print recursive_print ('$PHORUM', $PHORUM);

// SETTINGS MASTER LIST:
//                               // Limits for uploading images
// ['max_height']                // Maximum Height: pixels
// ['max_width']                 // Maximum Width: pixels
// ['max_filesize']              // Maximum File Size: KB
// ['max_images']                // Maximum number of images per user:
// ['max_total_filesize']        // Maximum total file size per user: KB
// ['multi_upload_blanks']       // Number of blanks on multiple-upload screen:
// ['file_types']                // Image types which a user can upload
//                               // Administration
// ['alert']                     // Alert on new image (list) ((not used))
// ['alertx']                    // Alert on new image (yes/no)
// ['suppress_until_approved']   // Approval required on new images  Yes
// ['allow_report_violation']    // Show "Report image violation" button
// ['alert_email']               // Email to send image notifications to:
// ['gallery_visibility']        // Gallery visibility
// ['allow_comments']            // Allow comments on images  Yes
// ['thumbnail_size']            // Thumbnail size: pixels
//                               // Display settings (Album)
// ['display_columns']           // Display columns:
// ['thumbs_per_page']           // Thumbs per Page:
//                               // Display settings (Control Panel)
// ['display_columns_cc']        // Display columns:
// ['thumbs_per_page_cc']        // Thumbs per Page:
//                               // Display settings (Moderation)
// ['display_columns_mi']        // Display columns:
// ['thumbs_per_page_mi']        // Thumbs per Page:

// save settings
// if (count($_POST))
if (isset($_POST['update_settings']))
{
    $PHORUM['mod_user_image_gallery']['max_height']              = (int)$_POST['max_height'];
    $PHORUM['mod_user_image_gallery']['max_width']               = (int)$_POST['max_width'];
    $PHORUM['mod_user_image_gallery']['max_filesize']            = (int)$_POST['max_filesize'];
    $PHORUM['mod_user_image_gallery']['max_images']              = (int)$_POST['max_images'];
    $PHORUM['mod_user_image_gallery']['max_total_filesize']      = (int)$_POST['max_total_filesize'];
    $PHORUM['mod_user_image_gallery']['multi_upload_blanks']     = (int)$_POST['multi_upload_blanks'];
    $PHORUM['mod_user_image_gallery']['file_types']              =      $_POST['file_types'];
    $PHORUM['mod_user_image_gallery']['alert']                   =      $_POST['alert'];
    $PHORUM['mod_user_image_gallery']['alertx']                  = (int)$_POST['alertx'];
    $PHORUM['mod_user_image_gallery']['suppress_until_approved'] = (int)$_POST['suppress_until_approved'];
    $PHORUM['mod_user_image_gallery']['allow_report_violation']  = (int)$_POST['allow_report_violation'];
    $PHORUM['mod_user_image_gallery']['alert_email']             =      $_POST['alert_email'];
    $PHORUM['mod_user_image_gallery']['gallery_visibility']      =      $_POST['gallery_visibility'];
    $PHORUM['mod_user_image_gallery']['allow_comments']          = (int)$_POST['allow_comments'];
    $PHORUM['mod_user_image_gallery']['thumbnail_size']          = (int)$_POST['thumbnail_size'];
    $PHORUM['mod_user_image_gallery']['display_columns']         = (int)$_POST['display_columns'];
    $PHORUM['mod_user_image_gallery']['thumbs_per_page']         = (int)$_POST['thumbs_per_page'];
    $PHORUM['mod_user_image_gallery']['display_columns_cc']      = (int)$_POST['display_columns_cc'];
    $PHORUM['mod_user_image_gallery']['thumbs_per_page_cc']      = (int)$_POST['thumbs_per_page_cc'];
    $PHORUM['mod_user_image_gallery']['display_columns_mi']      = (int)$_POST['display_columns_mi'];
    $PHORUM['mod_user_image_gallery']['thumbs_per_page_mi']      = (int)$_POST['thumbs_per_page_mi'];

    if ( $PHORUM['mod_user_image_gallery']['alert_email'] == '' ):
      $PHORUM['mod_user_image_gallery']['alertx']                  = 0;
      $PHORUM['mod_user_image_gallery']['suppress_until_approved'] = 0;
      $PHORUM['mod_user_image_gallery']['allow_report_violation']  = 0;
    endif;

    require_once('./mods/user_image_gallery/defaults.php');

    if(empty($error)) {
        phorum_db_update_settings(array(
          'mod_user_image_gallery' => $PHORUM['mod_user_image_gallery']
        ));
        phorum_admin_okmsg($langx['SettingsUpdated']);
    }
}

require_once('./mods/user_image_gallery/defaults.php');

include_once './include/admin/PhorumInputForm.php';
$frm = new PhorumInputForm ('', 'post', 'Save');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'user_image_gallery');

$frm->hidden('update_settings', '1');

$frm->addbreak($langx['User_Image_Gallery_module_settings']);

$clickherebutton = '<input type="submit" name="moderate_images" value="' . $langx['click_here'] . '">';
$frm->addrow(str_replace('%clickhere%', $clickherebutton, $langx['click_here_to_moderate_images']));

$frm->addbreak($langx['Limits_for_uploading_images_title']);

$frm->addrow($langx['Maximum_Height'], $frm->text_box('max_height', $PHORUM['mod_user_image_gallery']['max_height']) . $langx['_pixels']);
$frm->addrow($langx['Maximum_Width'], $frm->text_box('max_width', $PHORUM['mod_user_image_gallery']['max_width']) . $langx['_pixels']);
$frm->addrow($langx['Maximum_File_Size'], $frm->text_box('max_filesize', $PHORUM['mod_user_image_gallery']['max_filesize']) . $langx['_KB']);

$frm->addrow($langx['Maximum_number_of_images_per_user'], $frm->text_box('max_images', $PHORUM['mod_user_image_gallery']['max_images']));
$frm->addrow($langx['Maximum_total_file_size_per_user'], $frm->text_box('max_total_filesize', $PHORUM['mod_user_image_gallery']['max_total_filesize']) . $langx['_KB']);

$frm->addrow($langx['Number_of_blanks_on_multiple_upload_screen'], $frm->text_box('multi_upload_blanks', $PHORUM['mod_user_image_gallery']['multi_upload_blanks']));


$frm->addbreak($langx['Image_types_which_a_user_can_upload']);
$types=array('gif','jpg','jpeg','png');
foreach($types as $type){
    $checked = (@in_array($type, $PHORUM['mod_user_image_gallery']['file_types']))? 1 : 0;
    $frm->addrow($frm->checkbox('file_types[]', $type, '', $checked) . $type);
}


$frm->addbreak($langx['Administration_title']);

// $frm->addrow(
//   'Alert on new image',
//   $frm->select_tag(
//     'alert',
//     array(
//       'none'=>'None',
//       'admin_email'=>'Email to Admin',
//     ),
//     $PHORUM['mod_user_image_gallery']['alert']
//   )
// );

$frm->addrow($langx['Alert_on_new_image'], $frm->checkbox('alertx', '1', 'Yes', $PHORUM['mod_user_image_gallery']['alertx']));

$frm->addrow($langx['Approval_required_on_new_images'], $frm->checkbox('suppress_until_approved', '1', 'Yes', $PHORUM['mod_user_image_gallery']['suppress_until_approved']));

$frm->addrow($langx['Show_Report_image_violation_button'], $frm->checkbox('allow_report_violation', '1', 'Yes', $PHORUM['mod_user_image_gallery']['allow_report_violation']));

$frm->addrow($langx['Email_to_send_image_notifications_to'], $frm->text_box('alert_email', $PHORUM['mod_user_image_gallery']['alert_email']));

$frm->addrow($langx['Email_to_send_image_notifications_to_notice']);

$frm->addrow(
  $langx['Gallery_visibility'],
  $frm->select_tag(
    'gallery_visibility',
    array(
      'everybody' => $langx['gv_select_everybody'],
      'loggedin'  => $langx['gv_select_loggedin' ],
      'nobody'    => $langx['gv_select_nobody'   ],
    ),
    $PHORUM['mod_user_image_gallery']['gallery_visibility']
  )
);

$frm->addrow($langx['Allow_comments_on_images'], $frm->checkbox('allow_comments', '1', 'Yes', $PHORUM['mod_user_image_gallery']['allow_comments']));

$frm->addrow($langx['Thumbnail_size'], $frm->text_box('thumbnail_size', $PHORUM['mod_user_image_gallery']['thumbnail_size']) . $langx['_pixels']);


$frm->addbreak($langx['Display_settings_Album_title']);

$frm->addrow($langx['Display_columns'], $frm->text_box('display_columns', $PHORUM['mod_user_image_gallery']['display_columns']));
$frm->addrow($langx['Thumbs_per_Page'], $frm->text_box('thumbs_per_page', $PHORUM['mod_user_image_gallery']['thumbs_per_page']));


$frm->addbreak($langx['Display_settings_Control_Panel_title']);

$frm->addrow($langx['Display_columns'], $frm->text_box('display_columns_cc', $PHORUM['mod_user_image_gallery']['display_columns_cc']));
$frm->addrow($langx['Thumbs_per_Page'], $frm->text_box('thumbs_per_page_cc', $PHORUM['mod_user_image_gallery']['thumbs_per_page_cc']));


$frm->addbreak($langx['Display_settings_Moderation_title']);

$frm->addrow($langx['Display_columns'], $frm->text_box('display_columns_mi', $PHORUM['mod_user_image_gallery']['display_columns_mi']));
$frm->addrow($langx['Thumbs_per_Page'], $frm->text_box('thumbs_per_page_mi', $PHORUM['mod_user_image_gallery']['thumbs_per_page_mi']));



$frm->show();




























