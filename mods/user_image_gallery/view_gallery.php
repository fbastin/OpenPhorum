<?php
if (!defined("PHORUM")) return;

// if galleries are set to private, beat it

// old method:
// if ( ! mod_user_image_gallery_visible () ):
//     phorum_redirect_by_url(phorum_get_url(PHORUM_LIST_URL));
//     exit();
// endif;

// new method:

if ( $PHORUM['mod_user_image_gallery']['gallery_visibility'] == 'nobody' ):

  // tell users galleries have been set to private
  phorum_output('user_image_gallery::private');

elseif ( $PHORUM['mod_user_image_gallery']['gallery_visibility'] == 'loggedin' and $PHORUM["user"]["user_id"] == 0 ):

  // tell users they must log-in to see galleries
  phorum_output('user_image_gallery::pleaselogin');

else:
  // begin "giant if"

// NOTE: If, in the future, people decide to let users view their own gallery when visibility is set to nobody, compare $PHORUM["user"]["user_id"] to $gallery_id


// preconditions: $gallery_id will be set to user id of person whose gallery we're viewing
//                $page will be set to page # to view

// Retrieve the active list of images for the user.
$user_images = phorum_api_file_list('image_g', $gallery_id, NULL);

// get username of user number $gallery_id
$PHORUM['DATA']['display_name'] = phorum_api_user_get_display_name($gallery_id, NULL, PHORUM_FLAG_HTML);

// copied (mainly) from cc_panel.php

// ----------------------------------------------------------------------
// Determine the list of available images for the requested gallery.
// ----------------------------------------------------------------------

$images = array();       // will hold information about each image to display

$info = $PHORUM['mod_user_image_gallery']['image_info'];        // an array

// run through the list twice -- the first time, just gather a list of valid image numbers
//  the second time, gather all the other information as well, but restrict yourself to those items which appear on the current page

$number_of_valid_images = 0;
$display_list = array();

// Notice that we step through the keys of $user_images, but we're looking at the array $info. There's two very good reasons for this, but I don't have time to explain them.

foreach (array_keys($user_images) as $id):
    if ($info[$id]['approved'] == MOD_USER_IMAGE_GALLERY_APPROVED):           // only display if approved by moderator
        $display_list[$number_of_valid_images] = $id;                 // start array at 0
        ++$number_of_valid_images;
    endif;    // end if ($info[$id]['approved'] == MOD_USER_IMAGE_GALLERY_APPROVED):
endforeach;

// now figure out which page we're on before continuing

// the below is an attempt at somewhat-formalizing the page number handling

// first, number of elements per page
$display_per_page = $PHORUM['mod_user_image_gallery']['thumbs_per_page'];

// second, total number of items to display
$total_items = $number_of_valid_images;

// third, from that calculate the total number of pages we shall need
$total_pages = ceil($total_items / $display_per_page);
if ($total_pages < 1):
  $total_pages = 1;
endif;

// fourth, get current page number requested
if(isset($PHORUM['args']['page'])){
    $current_page = (int)$PHORUM['args']['page'];
} else {
    $current_page = 1;
}
if ($current_page > $total_pages):
  $current_page = $total_pages;
endif;
if ($current_page < 1):
  $current_page = 1;
endif;

// fifth, calculate first and last element to be displayed (zero base)
$start = ($current_page - 1) * $display_per_page;
$end = $current_page * $display_per_page - 1;
if ( $end >= $total_items ):
  $end = $total_items - 1;
endif;

// at this point, we need to figure out paging stuff
// it looks like they're headed towards some sort of general page-number-handling routine, but never got to it
// I'd do it myself, but I don't have time.
// The following is modified from search.php and list.php:

// figure out paging

$url_template = phorum_get_url(PHORUM_ADDON_URL, 'module=user_image_gallery', 'view=gallery', 'user='.$gallery_id, 'page=%page_num%');

$PHORUM["DATA"]["CURRENTPAGE"] = $current_page;
$PHORUM["DATA"]["TOTALPAGES"] = $total_pages;
$PHORUM["DATA"]["URL"]["PAGING_TEMPLATE"] = $url_template;

if ($total_pages <= 5) {
    $start_page = 1;
} elseif ($total_pages - $current_page < 2) {
    $start_page = $total_pages - 4;
} elseif ($total_pages > 5 and $current_page > 3) {
    $start_page = $current_page - 2;
} else {
    $start_page = 1;
}
$end_page = $start_page + 4;
if ($end_page > $total_pages):
  $end_page = $total_pages;
endif;

for ( $pagenumber = $start_page; $pagenumber <= $end_page; ++$pagenumber ):
    $PHORUM["DATA"]["PAGES"][] = array
    ("pageno" => $pagenumber,
        "url" => str_replace ( '%page_num%', $pagenumber, $url_template ),
    );
endfor;

if ($start_page > 1) {
    $PHORUM['DATA']['URL']['FIRSTPAGE'] = str_replace ( '%page_num%', '1',                  $url_template );
}

if ($end_page < $total_pages) {
    $PHORUM['DATA']['URL']['LASTPAGE'] =  str_replace ( '%page_num%', (string)$total_pages, $url_template );
}

if ($current_page > 1) {
    $prevpage = $current_page - 1;
    $PHORUM['DATA']['URL']['PREVPAGE'] =  str_replace ( '%page_num%', (string)$prevpage,    $url_template );
}

if ($current_page < $total_pages) {
    $nextpage = $current_page + 1;
    $PHORUM['DATA']['URL']['NEXTPAGE'] =  str_replace ( '%page_num%', (string)$nextpage,    $url_template );
}

$PHORUM['DATA']['URL']['CURRENTPAGE'] =  str_replace ( '%page_num%', (string)$current_page, $url_template );

$return_url = $PHORUM['DATA']['URL']['CURRENTPAGE'];
$return_url_encoded = urlencode($return_url);
$PHORUM['DATA']['URL']['return_url'] = $return_url_encoded;

$PHORUM['DATA']['MULTIPLE_PAGES'] = true;
if ( $total_pages == 1 ):
  $PHORUM['DATA']['MULTIPLE_PAGES'] = false;
endif;

// get detailed information for each image (but just the ones on the current page)

for ($i = $start; $i <= $end; ++$i):
    $id = $display_list[$i];
    $images[$id] = $user_images[$id];         // set $images[$id]['file_id'], ['filename'], ['filesize'], and ['add_datetime'] all at once
    $dimensions = NULL;
    if (isset($info[$id]['width']) and isset($info[$id]['height'])):
        $dimensions = $info[$id]['width'] . '&nbsp;x&nbsp;' . $info[$id]['height'];
        if ( ($info[$id]['width'] >= $info[$id]['height']) and ($info[$id]['width'] > $PHORUM['mod_user_image_gallery']['thumbnail_size']) ):
          $images[$id]['adjustment'] = 'width="'.$PHORUM['mod_user_image_gallery']['thumbnail_size'].'"';
        elseif ( ($info[$id]['height'] > $info[$id]['width']) and ($info[$id]['height'] > $PHORUM['mod_user_image_gallery']['thumbnail_size']) ):
          $images[$id]['adjustment'] = 'height="'.$PHORUM['mod_user_image_gallery']['thumbnail_size'].'"';
        else:
          $images[$id]['adjustment'] = '';
        endif;
    endif;
    $images[$id]["dimensions"] = $dimensions;
    $images[$id]["dateadded"] = phorum_date($PHORUM["short_date_time"], $images[$id]["add_datetime"]);
    $images[$id]["moddate"] = phorum_date($PHORUM["short_date_time"], $info[$id]['mod_date']);
    $images[$id]["url"] = phorum_get_url(PHORUM_FILE_URL, "file=$id", 'modified='.$info[$id]['mod_date'], 'filename='.urlencode($images[$id]['filename']));
    $images[$id]["link"] = phorum_get_url(PHORUM_ADDON_URL, 'module=user_image_gallery', 'view=image', 'image='.$id, 'return_url='.$return_url_encoded, 'from=1' );
    if ( $info[$id]['title'] != '' ):
      $images[$id]['title'] = $info[$id]['title'];
    else:
      $images[$id]['title'] = $images[$id]['filename'];
    endif;
endfor;

// ----------------------------------------------------------------------
// Setup template data.
// ----------------------------------------------------------------------

$data['template'] = 'user_image_gallery::cc_panel';

$PHORUM["DATA"]["FILES"] = $images;
$PHORUM["DATA"]["NUMBER_OF_FILES"] = count($images);

$PHORUM["DATA"]["mod_user_image_gallery"] = $PHORUM['mod_user_image_gallery'];

$PHORUM["DATA"]["mod_user_image_gallery"]["disable_image_gallery_display"] =
    !empty($PHORUM['user']["mod_user_image_gallery"]["disable_image_gallery_display"]);

if ($PHORUM['mod_user_image_gallery']['max_filesize']) {
    $PHORUM["DATA"]["FILE_SIZE_LIMIT"] = str_replace(
        array(
            '%filesize%',
            '%width%',
            '%height%'
        ),
        array(
            phorum_filesize($PHORUM['mod_user_image_gallery']['max_filesize']*1024),
            $PHORUM['mod_user_image_gallery']["max_width"],
            $PHORUM['mod_user_image_gallery']["max_height"]
        ),
        $lang["FileSizeLimits"]
    );
}

if ($PHORUM['mod_user_image_gallery']["file_types"]) {
    $file_type_list = implode(", ",$PHORUM['mod_user_image_gallery']["file_types"]);
    $PHORUM["DATA"]["FILE_TYPE_LIMIT"] = str_replace(
        '%file_type_list%',
        $file_type_list,
        $lang["FileTypeLimits"]
    );
}

$PHORUM["DATA"]["LANG"]["mod_user_image_gallery"]["ImageLimit"] = str_replace(
    '%max_images%',
    $PHORUM['mod_user_image_gallery']['max_images'],
    $lang["ImageLimit"]
);

// $PHORUM["DATA"]["TOTAL_FILES"] = count($images);
// $PHORUM["DATA"]["TOTAL_FILE_SIZE"] = phorum_filesize($total_size);
//   total images and total file size aren't needed here -- and if they are, we will have to handle them differently

// get user name
// Override the default title and description.
$PHORUM['DATA']['HEADING'] = str_replace('%displayname%', $PHORUM['DATA']['display_name'], $lang['gallery_title']);
$PHORUM['DATA']['HTML_TITLE'] =
    htmlspecialchars(strip_tags($PHORUM['DATA']['HEADING']));
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

$PHORUM['DATA']['MESSAGES'] = $messages;

// include phorum_get_template('user_image_gallery::gallery');   // displays template only
phorum_output('user_image_gallery::gallery');      // displays header, template, footer

endif;   // end "giant if"
?>