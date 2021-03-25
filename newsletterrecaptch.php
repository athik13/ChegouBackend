<?php
    include_once("common.php");
	$html = "<script src='https://www.google.com/recaptcha/api.js'></script>";
	$html .="<div class='g-recaptcha' data-sitekey='".$GOOGLE_CAPTCHA_SITE_KEY."'></div>";
	echo $html;

 ?>