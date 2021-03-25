<?php

/*
  Below included Licence file is set for your domain.
  Do not remove or change code of licence file.
  It will stop your website and apps.
  ## DO NOT MAKE CHANGED BELOW THIS LINE IN THIS FILE. IT MAY STOP YOUR WEBSITE OR APP OR MAKE THEM WORK DIFFERENTLY.
 */
include_once ('assets/libraries/server_configurations_params.php');
include_once ('assets/libraries/server_configurations.php');
include_once ('assets/libraries/modules_availibility.php');
include_once ('licence.php');
if(!empty($GOOGLE_ANALYTICS)) {
    $GOOGLE_ANALYTICS = "<!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src='https://www.googletagmanager.com/gtag/js?id=$GOOGLE_ANALYTICS'></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '$GOOGLE_ANALYTICS');
    </script>";
}
include_once("include/config.php");
include_once ('assets/libraries/site_variables.php');
if (strpos($_SERVER['REQUEST_URI'], "/" . SITE_ADMIN_URL) !== false) {
    include_once($tconfig['tpanel_path'] . '/' . SITE_ADMIN_URL . '/library/common_include.php');
    $userObj = new Admin\library\User();
    if (!in_array(basename($_SERVER['REQUEST_URI']), $exclude_login)) {
        $userObj->isLogin(true);
    }
}
//Added By HJ On 28-08-2019 For Solved Aug - Sheet Issue #269 Start
if ($MAINTENANCE_WEBSITE == "Yes") {
    //$tsite_url = $tconfig['tsite_url'];
    //echo "<pre>";print_r($_SERVER['REQUEST_URI']);die;
    if (strpos($_SERVER['REQUEST_URI'], 'admin') !== false) {
        //Admin Panel Running
    } else if (!isset($_REQUEST['maintanance'])) {
        header("Location:maintanance?maintanance=yes");
    }
}
//Added By HJ On 28-08-2019 For Solved Aug - Sheet Issue #269 End
?>