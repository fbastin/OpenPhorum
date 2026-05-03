<?php
    define('phorum_page','sandbox');

    include_once('./common.php');
    include_once('./include/api/user.php');
    include_once('./include/format_functions.php');

    // set all our URL's
    phorum_build_common_urls();

    // include the correct template
    phorum_output('wiki::' . 'wiki');
?>
