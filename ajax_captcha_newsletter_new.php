<?php

include_once('common.php');
session_start();
$POST_CAPTCHA_NEWSLETTER = isset($_REQUEST['POST_CAPTCHA_NEWSLETTER']) ? $_REQUEST['POST_CAPTCHA_NEWSLETTER'] : '';
$SESS_CAPTCHA = $_SESSION['SESS_NEWSCAPTCHA'];
if ($POST_CAPTCHA_NEWSLETTER == $SESS_CAPTCHA) {
    $message = 'true';
} else {
    $message = 'false';
}
echo $message;
die;
?>