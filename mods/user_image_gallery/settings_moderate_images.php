<?php

if (!defined('PHORUM_ADMIN')) return;

require_once('./include/format_functions.php');
require_once('./mods/user_image_gallery/defaults.php');

$image_state_array_title = array(
                                  MOD_USER_IMAGE_GALLERY_APPROVED => $langx['state_images_approved'],
                                  MOD_USER_IMAGE_GALLERY_WAITING  => $langx['state_images_waiting'],
                                  MOD_USER_IMAGE_GALLERY_BANNED   => $langx['state_images_banned'],
                                );

$image_state_array_set = array(
                                MOD_USER_IMAGE_GALLERY_APPROVED => $langx['state_approved'],
                                MOD_USER_IMAGE_GALLERY_WAITING  => $langx['state_waiting'], 
                                MOD_USER_IMAGE_GALLERY_BANNED   => $langx['state_banned'],
                              );

if (isset($_POST['currently_viewing'])):
  $currently_viewing = (int)$_POST['currently_viewing'];
else:
  $currently_viewing = MOD_USER_IMAGE_GALLERY_WAITING;
endif;

require_once('./include/api/file_storage.php');

// save changes
if (isset($_POST['save_changes_moderate'])):

  foreach ($_POST['set_approval'] as $key => $value):     // $key = image number, $value = approval code number
    $PHORUM['mod_user_image_gallery']['image_info'][(int)$key]['approved'] = (int)$value;
  endforeach;
  // update settings
  phorum_db_update_settings(array(
    'mod_user_image_gallery' => $PHORUM['mod_user_image_gallery']
  ));
endif;

// set-up image info arrays and page number stuff

// get list of images from ALL users
$images = phorum_api_file_list('image_g', NULL, NULL);
$info = $PHORUM['mod_user_image_gallery']['image_info'];
// between these two arrays, we have all the info we need

// gather a list of only the ones we'll be looking at

$number_of_valid_images = 0;
$display_list = array();

foreach (array_keys($images) as $id):
    // if "approved" is not set properly, force to "waiting"
    if ( $info[$id]['approved'] != MOD_USER_IMAGE_GALLERY_APPROVED and
         $info[$id]['approved'] != MOD_USER_IMAGE_GALLERY_WAITING and
         $info[$id]['approved'] != MOD_USER_IMAGE_GALLERY_BANNED):
      $info[$id]['approved'] = MOD_USER_IMAGE_GALLERY_WAITING;
    endif;
    if ($info[$id]['approved'] == $currently_viewing):
        $display_list[$number_of_valid_images] = $id;                 // start array at 0
        ++$number_of_valid_images;
    endif;    // end if ($info[$id]['approved'] == $currently_viewing):
endforeach;

// now figure out which page we're on before continuing

// the below is an attempt at somewhat-formalizing the page number handling

// first, number of elements per page
$display_per_page = $PHORUM['mod_user_image_gallery']['thumbs_per_page_mi'];     // mi = moderate images
$display_cols = $PHORUM['mod_user_image_gallery']['display_columns_mi'];     // mi = moderate images

// second, total number of items to display
$total_items = $number_of_valid_images;

// third, from that calculate the total number of pages we shall need
$total_pages = ceil($total_items / $display_per_page);
if ($total_pages < 1):
  $total_pages = 1;
endif;

// fourth, get current page number requested
if(isset($_POST['page'])){
    $current_page = (int)$_POST['page'];
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

// when we get to that point, we will get detailed information for just the ones on the current page


include_once './include/admin/PhorumInputForm.php';

$frm = new PhorumInputForm ('', 'post', NULL);
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'user_image_gallery');
$frm->addbreak($langx['ModerateImagesTitle']);

$clickherebutton = '<input type="submit" value="' . $langx['click_here'] . '">';
$frm->addrow(str_replace('%clickhere%', $clickherebutton, $langx['return_to_module_settings']));

$frm->show();

// instead of using PhorumInputForm, fake-up this next part using bits copied from PhorumInputForm



?>
<table border="0" cellspacing="2" cellpadding="2" class="input-form-table" width="100%">
  <tr class="input-form-tr">
    <th valign="middle" align="left" class="input-form-th" nowrap="nowrap"><?php print $image_state_array_title[$currently_viewing]; ?></th>
    <td valign="middle" align="left" class="input-form-td">
      <form style="display: inline;" action="<?php print htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
<?php
          // add the admin token if we are in the admin and the token is available
          if(defined('PHORUM_ADMIN') && !empty($PHORUM['admin_token'])) {
              echo "<input type=\"hidden\" name=\"phorum_admin_token\" value=\"{$PHORUM['admin_token']}\">\n";
          }
?>
        <input type="hidden" name="module" value="modsettings">
        <input type="hidden" name="mod" value="user_image_gallery">
        <input type="hidden" name="moderate_images" value="1">
        Switch to: &nbsp;
<?php
print
$frm->select_tag(
  'currently_viewing',
  $image_state_array_title,
  $currently_viewing
)
?>
        <input type="submit" value="Go">
      </form>
    </td>
    <td valign="middle" align="left" class="input-form-td">
      <form style="display: inline;" action="<?php print htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
<?php
        // add the admin token if we are in the admin and the token is available
        if(defined('PHORUM_ADMIN') && !empty($PHORUM['admin_token'])) {
            echo "<input type=\"hidden\" name=\"phorum_admin_token\" value=\"{$PHORUM['admin_token']}\">\n";
        }
?>
        <input type="hidden" name="module" value="modsettings">
        <input type="hidden" name="mod" value="user_image_gallery">
        <input type="hidden" name="moderate_images" value="1">
        <input type="hidden" name="currently_viewing" value="<?php print $currently_viewing ?>">
<?php
        print $langx['go_to_page'] . ' &nbsp;';
        for ($i = 1; $i <= $total_pages; ++$i):
          if ($i == $current_page):
            print '&nbsp;<b>' . $i . '</b>&nbsp;' . "\n";
          else:
            print '<input type="submit" name="page" value="' . $i . '">' . "\n";
          endif;
        endfor;
?>
      </form>
    </td>
  </tr>
  <tr class="input-form-tr">
    <td class="input-form-td-break" align="center" colspan="3"></td>
  </tr>
</table>
<?php


// print recursive_print('$_POST', $_POST);  // debugg


$frm = new PhorumInputForm ('', 'post', 'Save changes');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'user_image_gallery');
$frm->hidden('moderate_images', '1');
$frm->hidden('save_changes_moderate', '1');
$frm->hidden('currently_viewing', $currently_viewing);
$frm->hidden('page', $current_page);

$o = '<table width="100%">' . "\n";
$currcol = 0;

// get detailed information for each image (but just the ones on the current page)


for ($i = $start; $i <= $end; ++$i):
    $id = $display_list[$i];

//    if ($info[$id]['approved'] == $currently_viewing):     // already taken care of by page number routine earlier

        if ($currcol == 0): $o .= '<tr>' . "\n"; endif;

        $dimensions = NULL;
        if (isset($info[$id]['width']) and isset($info[$id]['height'])) {
            $dimensions = $info[$id]['width'] . ' x ' . $info[$id]['height'];
            if ( ($info[$id]['width'] >= $info[$id]['height']) and ($info[$id]['width'] > $PHORUM['mod_user_image_gallery']['thumbnail_size']) ):
              $images[$id]['adjustment'] = 'width="'.$PHORUM['mod_user_image_gallery']['thumbnail_size'].'"';
            elseif ( ($info[$id]['height'] > $info[$id]['width']) and ($info[$id]['height'] > $PHORUM['mod_user_image_gallery']['thumbnail_size']) ):
              $images[$id]['adjustment'] = 'height="'.$PHORUM['mod_user_image_gallery']['thumbnail_size'].'"';
            else:
              $images[$id]['adjustment'] = '';
            endif;
        }
        $images[$id]["dimensions"] = $dimensions;

        $images[$id]["dateadded"] = phorum_date($PHORUM["short_date_time"], $images[$id]["add_datetime"]);
        $images[$id]["moddate"] = phorum_date($PHORUM["short_date_time"], $info[$id]['mod_date']);
        $images[$id]["url"] = phorum_get_url(PHORUM_FILE_URL, "file=$id", 'modified='.$info[$id]['mod_date'], 'filename='.urlencode($images[$id]['filename']));
        $images[$id]["link"] = phorum_get_url(PHORUM_ADDON_URL, 'module=user_image_gallery', 'view=image', 'image='.$id );

        $images[$id]['display_name'] = phorum_api_user_get_display_name($info[$id]['user_id'], NULL, PHORUM_FLAG_HTML);


        $o .= '<td align="center">' .
              $images[$id]['display_name'] .
              '<br />' .
              '<img src="' . $images[$id]['url'] . '" border=0 ' . $images[$id]['adjustment'] . '>' .
              '<br />';

// print 'About to: $frm->select_tag("set_approval[$id]", $image_state_array_set, $images[$id]["approved"] );<br>'."\n";
// print recursive_print ("\$id", $id);
// print recursive_print ("\$image_state_array_set", $image_state_array_set);
// print recursive_print ("\$info[$id]['approved']", $info[$id]["approved"]);

//        $o .= $frm->select_tag(
        $o .= $frm->radio_button(
                                'set_approval[' . $id . ']',
                                $image_state_array_set,
                                $info[$id]['approved'],
                                '<br />'
                              );

        $o .= '</td>' . "\n";

        ++$currcol;
        if ( $currcol == $display_cols ): 
          $o .= '</tr>' . "\n";
          $currcol = 0;
        endif;

        $total_size += $images[$id]["filesize"];

//    endif;    // end if ($info[$id]['approved'] == $currently_viewing):
endfor;

if ($currcol != 0):
    while ($currcol != 0):
        $o .= '<td>&nbsp;</td>' . "\n";
        ++$currcol;
        if ( $currcol == $display_cols ): 
          $o .= '</tr>' . "\n";
          $currcol = 0;
        endif;
    endwhile;
endif;

$o .= '</table>' . "\n";

$frm->addrow($o);

$frm->show();

