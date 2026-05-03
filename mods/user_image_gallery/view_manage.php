<?php
if (!defined("PHORUM")) return;

// (passed to us: $image_id, $additional)

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
$PHORUM["DATA"]['additional'] = $additional;

// Override the default title and description.
$PHORUM['DATA']['HEADING'] = 'Manage Image: ' . $image['filename'];
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

// set up pulldown for colors

$PHORUM['DATA']['COLOR_PULLDOWN'] = '
<select name="watermark_color">
  <option value="#000000">'.$lang['Black'] .'</option>
  <option value="#FFFFFF">'.$lang['White'] .'</option>
  <option value="#FF0000">'.$lang['Red']   .'</option>
  <option value="#00FF00">'.$lang['Green'] .'</option>
  <option value="#FFFF00">'.$lang['Yellow'].'</option>
  <option value="#0000FF">'.$lang['Blue']  .'</option>
  <option value="#FF00FF">'.$lang['Purple'].'</option>
  <option value="#00FFFF">'.$lang['Aqua']  .'</option>
</select>
';


// set up pulldown for fonts

$PHORUM['DATA']['FONT_PULLDOWN'] = '
<select name="watermark_font">
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
  <option value="5">5</option>
  <option value="./mods/user_image_gallery/gdf_fonts/04b20s8.gdf"                 >04b20s8</option>
  <option value="./mods/user_image_gallery/gdf_fonts/04b25.gdf"                   >04b25</option>
  <option value="./mods/user_image_gallery/gdf_fonts/04b.gdf"                     >04b</option>
  <option value="./mods/user_image_gallery/gdf_fonts/8x13iso.gdf"                 >8x13iso</option>
  <option value="./mods/user_image_gallery/gdf_fonts/almosnow.gdf"                >Almosnow</option>
  <option value="./mods/user_image_gallery/gdf_fonts/anonymous.gdf"               >Anonymous</option>
  <option value="./mods/user_image_gallery/gdf_fonts/anticlimax.gdf"              >Anticlimax</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-bold-20.gdf"           >Arial-bold-20</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-bold-24.gdf"           >Arial-bold-24</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-bold-36.gdf"           >Arial-bold-36</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-bold-48.gdf"           >Arial-bold-48</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-narrow-bold-28.gdf"    >Arial-narrow-bold-28</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-reg-14.gdf"            >Arial-reg-14</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-reg-16.gdf"            >Arial-reg-16</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-reg-18.gdf"            >Arial-reg-18</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-reg-20.gdf"            >Arial-reg-20</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-reg-30.gdf"            >Arial-reg-30</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-reg-32.gdf"            >Arial-reg-32</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-reg-36.gdf"            >Arial-reg-36</option>
  <option value="./mods/user_image_gallery/gdf_fonts/arial-reg-48.gdf"            >Arial-reg-48</option>
  <option value="./mods/user_image_gallery/gdf_fonts/atommicclock.gdf"            >Atommicclock</option>
  <option value="./mods/user_image_gallery/gdf_fonts/automatic.gdf"               >Automatic</option>
  <option value="./mods/user_image_gallery/gdf_fonts/azimov.gdf"                  >Azimov</option>
  <option value="./mods/user_image_gallery/gdf_fonts/backlash.gdf"                >Backlash</option>
  <option value="./mods/user_image_gallery/gdf_fonts/betsy.gdf"                   >Betsy</option>
  <option value="./mods/user_image_gallery/gdf_fonts/bettynoir.gdf"               >Bettynoir</option>
  <option value="./mods/user_image_gallery/gdf_fonts/bmcorrode.gdf"               >Bmcorrode</option>
  <option value="./mods/user_image_gallery/gdf_fonts/bodoblacksquares.gdf"        >Bodoblacksquares</option>
  <option value="./mods/user_image_gallery/gdf_fonts/bodoblacksquaresinv.gdf"     >Bodoblacksquaresinv</option>
  <option value="./mods/user_image_gallery/gdf_fonts/bubblebath.gdf"              >Bubblebath</option>
  <option value="./mods/user_image_gallery/gdf_fonts/caveman.gdf"                 >Caveman</option>
  <option value="./mods/user_image_gallery/gdf_fonts/checkbook.gdf"               >Checkbook</option>
  <option value="./mods/user_image_gallery/gdf_fonts/chinyen.gdf"                 >Chinyen</option>
  <option value="./mods/user_image_gallery/gdf_fonts/chowfun.gdf"                 >Chowfun</option>
  <option value="./mods/user_image_gallery/gdf_fonts/christmaxlightoutside.gdf"   >Christmaxlightoutside</option>
  <option value="./mods/user_image_gallery/gdf_fonts/cowboys.gdf"                 >Cowboys</option>
  <option value="./mods/user_image_gallery/gdf_fonts/crackman.gdf"                >Crackman</option>
  <option value="./mods/user_image_gallery/gdf_fonts/crass.gdf"                   >Crass</option>
  <option value="./mods/user_image_gallery/gdf_fonts/cry.gdf"                     >Cry</option>
  <option value="./mods/user_image_gallery/gdf_fonts/dimurph.gdf"                 >Dimurph</option>
  <option value="./mods/user_image_gallery/gdf_fonts/dingdongdaddy.gdf"           >Dingdongdaddy</option>
  <option value="./mods/user_image_gallery/gdf_fonts/dreamofme.gdf"               >Dreamofme</option>
  <option value="./mods/user_image_gallery/gdf_fonts/dreamofme_001.gdf"           >Dreamofme_001</option>
  <option value="./mods/user_image_gallery/gdf_fonts/DS-Digital-reg-20.gdf"       >DS-Digital-reg-20</option>
  <option value="./mods/user_image_gallery/gdf_fonts/dyno.gdf"                    >Dyno</option>
  <option value="./mods/user_image_gallery/gdf_fonts/ennobled.gdf"                >Ennobled</option>
  <option value="./mods/user_image_gallery/gdf_fonts/fareast.gdf"                 >Fareast</option>
  <option value="./mods/user_image_gallery/gdf_fonts/festival.gdf"                >Festival</option>
  <option value="./mods/user_image_gallery/gdf_fonts/fizzo.gdf"                   >Fizzo</option>
  <option value="./mods/user_image_gallery/gdf_fonts/FrankGothMedCond-14.gdf"     >FrankGothMedCond-14</option>
  <option value="./mods/user_image_gallery/gdf_fonts/gogobig.gdf"                 >Gogobig</option>
  <option value="./mods/user_image_gallery/gdf_fonts/gyropady.gdf"                >Gyropady</option>
  <option value="./mods/user_image_gallery/gdf_fonts/hootie.gdf"                  >Hootie</option>
  <option value="./mods/user_image_gallery/gdf_fonts/LCD-reg-20.gdf"              >LCD-reg-20</option>
  <option value="./mods/user_image_gallery/gdf_fonts/niserobopitcher.gdf"         >Niserobopitcher</option>
  <option value="./mods/user_image_gallery/gdf_fonts/scarabo.gdf"                 >Scarabo</option>
  <option value="./mods/user_image_gallery/gdf_fonts/sfballoon.gdf"               >Sfballoon</option>
  <option value="./mods/user_image_gallery/gdf_fonts/skaterdude.gdf"              >Skaterdude</option>
  <option value="./mods/user_image_gallery/gdf_fonts/sketchey.gdf"                >Sketchey</option>
  <option value="./mods/user_image_gallery/gdf_fonts/terminal6.gdf"               >Terminal6</option>
  <option value="./mods/user_image_gallery/gdf_fonts/terminal9.gdf"               >Terminal9</option>
  <option value="./mods/user_image_gallery/gdf_fonts/trisk.gdf"                   >Trisk</option>
  <option value="./mods/user_image_gallery/gdf_fonts/xtrusion.gdf"                >Xtrusion</option>
</select>
';

$PHORUM['DATA']['MESSAGES'] = $messages;

// include phorum_get_template('user_image_gallery::manage');   // displays template only
phorum_output('user_image_gallery::manage');      // displays header, template, footer
?>
