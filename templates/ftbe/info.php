<?php

    if(!defined("PHORUM")) return;

    $name = 'FTBE';
    $version = '0.1';
    // uncomment this to hide this template from the user-select-box
    //$template_hide = 1;

// This routine is here in case you make a copy of this template and forget to change its name
$xx_dirtomatch = 'ftbe';   // change this to current dir
$xx_fullpath = __FILE__;
$xx_a = explode('/', $xx_fullpath);
$xx_count = count($xx_a);
if ( $xx_a[$xx_count-2] != $xx_dirtomatch ) {
  $name = '!ERROR: Change name in template ' . $xx_a[$xx_count-2];
  $version = '';
}

?>
