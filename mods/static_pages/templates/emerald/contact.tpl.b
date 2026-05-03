<?php
session_start();
$_SESSION = array();

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/includes/simple-php-captcha.php";
include($path);
$_SESSION['captcha'] = simple_php_captcha();

?>

<div class="generic">

<?php
$path = $_SERVER['DOCUMENT_ROOT'];
include($path."/includes/contact.php");
$path = $_SERVER['SERVER_NAME'];
//echo '<img src="'. $path . $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA code">';
echo '<img src="http://'. $path. $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA code">';
echo $_SESSION['captcha']['code'];
echo $path.$_SESSION['captcha']['image_src'];
?>

</div>
