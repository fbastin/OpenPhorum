<?php
if (!defined("PHORUM")) return;

// (passed to us: $image_id)

// This is like a control panel page, for a single image
// No-one should be here except the person who owns the image
// So, first check the owner of the requested image and kick user out if it's someone else
// (TODO))

// first, get all info for the requested image
$image = mod_user_image_gallery_get_image_info ($image_id);

// ----------------------------------------------------------------------
// Setup template data.
// ----------------------------------------------------------------------

$PHORUM["DATA"]['IMAGE'] = $image;

// Override the default title and description.
$PHORUM['DATA']['HEADING'] = str_replace('%filename%', $image['filename'], $lang['heading_image_information']);
$PHORUM['DATA']['HTML_TITLE'] = htmlspecialchars(strip_tags($PHORUM['DATA']['HEADING']));
$PHORUM['DATA']['HTML_DESCRIPTION'] = '';

// throw out all of breadcrumb except home
$home_breadcrumb = $PHORUM['DATA']['BREADCRUMBS'][0];    // save home
$PHORUM['DATA']['BREADCRUMBS'] = array();                // destroy all
$PHORUM['DATA']['BREADCRUMBS'][0] = $home_breadcrumb;    // restore link to home

// now add our breadcrumb
$PHORUM['DATA']['BREADCRUMBS'][] = array(
          'URL'  => NULL,
          'TEXT' => $PHORUM['DATA']['HEADING']
);

$PHORUM['DATA']['URL']['return'] = phorum_get_url(PHORUM_CONTROLCENTER_URL, "panel=image_gallery");

$PHORUM['DATA']['MESSAGES'] = $messages;

// include phorum_get_template('user_image_gallery::info');   // displays template only
phorum_output('user_image_gallery::info');      // displays header, template, footer
?>