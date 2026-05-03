<?php
// A simple helper script that will setup initial module
// settings in case one of these settings is missing.
// It also sets up some constants that we use.

// WARNING: Default values on checkboxes MUST be 0!

// ----------------------------------------------------------------------
// THIS FILE IS NOT MEANT FOR CHANGING MODULE SETTINGS.
// USE THE MODULE SETTINGS IN THE PHORUM ADMIN FOR THAT,
// UNLESS YOU KNOW WHAT YOU ARE DOING.
// ----------------------------------------------------------------------

if(!defined('PHORUM') && !defined('PHORUM_ADMIN')) return;

define('MOD_USER_IMAGE_GALLERY_INFO',    'information');
define('MOD_USER_IMAGE_GALLERY_WARNING', 'attention'  );

define('MOD_USER_IMAGE_GALLERY_APPROVED', 1);
define('MOD_USER_IMAGE_GALLERY_WAITING',  2);
define('MOD_USER_IMAGE_GALLERY_BANNED',   3);

if (! isset($GLOBALS['PHORUM']['mod_user_image_gallery'])) {
    $GLOBALS['PHORUM']['mod_user_image_gallery'] = array();
}

if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['max_height'             ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['max_height'             ] =  400; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['max_width'              ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['max_width'              ] =  400; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['max_filesize'           ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['max_filesize'           ] =  500; }  // in KB
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['max_images'             ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['max_images'             ] =   25; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['max_total_filesize'     ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['max_total_filesize'     ] = 5000; }  // in KB
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['multi_upload_blanks'    ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['multi_upload_blanks'    ] =   10; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['file_types'             ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['file_types'             ] = array('gif','png','jpg','jpeg'); }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['alert'                  ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['alert'                  ] = 'none'; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['alertx'                 ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['alertx'                 ] =    0; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['suppress_until_approved'])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['suppress_until_approved'] =    0; }  // 1=yes, 0=no
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['allow_report_violation' ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['allow_report_violation' ] =    0; }  // 1=yes, 0=no
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['alert_email'            ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['alert_email'            ] =   ''; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['gallery_visibility'     ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['gallery_visibility'     ] = 'everybody'; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['allow_comments'         ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['allow_comments'         ] =    0; }  // 1=yes, 0=no
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['thumbnail_size'         ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['thumbnail_size'         ] =  100; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['display_columns'        ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['display_columns'        ] =    5; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['thumbs_per_page'        ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['thumbs_per_page'        ] =   25; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['display_columns_cc'     ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['display_columns_cc'     ] =    5; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['thumbs_per_page_cc'     ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['thumbs_per_page_cc'     ] =   25; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['display_columns_mi'     ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['display_columns_mi'     ] =    5; }
if (empty($GLOBALS['PHORUM']['mod_user_image_gallery']['thumbs_per_page_mi'     ])) { $GLOBALS['PHORUM']['mod_user_image_gallery']['thumbs_per_page_mi'     ] =   25; }

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

