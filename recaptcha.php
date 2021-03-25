<?php

include_once("common.php");
//Added By HJ On 10-12-2019 For Solved Mobile (Demo) server Bug Id = 8979 Start
$captchaLang = "EN";
if (isset($_SESSION['sess_lang']) && $_SESSION['sess_lang']) {
    $captchaLang = $_SESSION['sess_lang'];
}
//Added By HJ On 10-12-2019 For Solved Mobile (Demo) server Bug Id = 8979 End
//echo "<pre>";print_R($_SESSION);die;
$html = "<script src='https://www.google.com/recaptcha/api.js?explicit&hl=" . $captchaLang . "'></script>";
$html .= "<div class='g-recaptcha' data-sitekey='" . $GOOGLE_CAPTCHA_SITE_KEY . "'></div>";
echo $html;
?>