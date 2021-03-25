<?php
session_start();
include_once 'common.php';
$generalobj->go_to_home();

if($_SESSION['sess_signin'] == 'admin') { // it is becoz when from admin comes it have sess lang en so that take it default lang..
    $_SESSION['sess_lang'] = $default_lang;
    $_SESSION['sess_signin'] = '';
    $type = $_REQUEST['type'];
    header("location: sign-in?type=".$type);exit;
}

$meta_arr = $generalobj->getsettingSeo(1);

$forpsw = isset($_REQUEST['forpsw']) ? $_REQUEST['forpsw'] : '';
$forgetPWd = isset($_REQUEST['forgetPWd']) ? $_REQUEST['forgetPWd'] : '';

$depart = '';
if (isset($_REQUEST['depart'])) {
    $_SESSION['sess_depart'] = $_REQUEST['depart'];
    $depart = $_SESSION['sess_depart'];
} else {
    if (isset($_REQUEST['depart'])) {
        unset($_SESSION['sess_depart']);
    }
}
//$_SESSION['sess_lang'] = 'EN';
$err_msg = "";
if (isset($_SESSION['sess_error_social'])) {
    $err_msg = $_SESSION['sess_error_social'];

    unset($_SESSION['sess_error_social']);
    unset($_SESSION['fb_user']);   //facebook
    unset($_SESSION['oauth_token']);  //twitter
    unset($_SESSION['oauth_token_secret']); //twitter
    unset($_SESSION['access_token']);  //google
}

$rider_email = $driver_email = $company_email = '';
//$host_system = 'doctor4';

if ($host_system == "carwash") {
    $rider_note = "If you have registered as a new user, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
} elseif ($host_system == "beautician" || $host_system == "beautician4" || $host_system == "carwash4" || $host_system == "dogwalking4" || $host_system == "towtruck4" || $host_system == "massage4" || $host_system == "ufxforall4" || $domain == "cubejek") {
    $rider_note = "If you have registered as a new user, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
} elseif ($host_system == "tutors") {
    $rider_note = "If you have registered as a new student, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
} elseif ($host_system == "doctor4") {
    $rider_note = "If you have registered as a new patient, use your registered Email Id and Password to view the detail of your Appointment.<br />To view the Standard Features of the Apps use below access detail";
} else {
    $rider_note = "If you have registered as a new Rider, use your registered Email Id and Password to view the detail of your Rides.<br />To view the Standard Features of the Apps use below access detail";
}

//if(SITE_TYPE == 'Demo') {
//commented following code bc not needed now as CD told
/* if(SITE_TYPE == 'Live') {
  $loginblockfooter = 1;
  $pwd = "123456";
  if ($host_system == "carwash") {
  $rider_email = "user@demo.com";
  $driver_email = "washer@demo.com";
  $driver_note = "If you have registered as a new Washer, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "beautician") {
  $rider_email = "user@demo.com";
  $driver_email = "beautician@demo.com";
  $driver_note = "If you have registered as a new beautician, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "massage4") {
  $company_email = "company@gmail.com";
  $driver_email = "massager@demo.com";
  $rider_email = "user@demo.com";
  $driver_note = "If you have registered as a new massage therapist, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "doctor4") {
  $company_email = "company@gmail.com";
  $driver_email = "doctor@demo.com";
  $rider_email = "patient@demo.com";
  $driver_note = "If you have registered as a new doctor, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "beautician4") {
  $company_email = "company@gmail.com";
  $driver_email = "beautician@demo.com";
  $rider_email = "user@demo.com";
  $driver_note = "If you have registered as a new beautician, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "carwash4") {
  $company_email = "company@gmail.com";
  $driver_email = "carwasher@demo.com";
  $rider_email = "user@demo.com";
  $driver_note = "If you have registered as a new car washer, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "dogwalking4") {
  $company_email = "company@gmail.com";
  $driver_email = "dogwalker@demo.com";
  $rider_email = "user@demo.com";
  $driver_note = "If you have registered as a new dog walker, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "towtruck4") {
  $company_email = "company@gmail.com";
  $driver_email = "provider@demo.com";
  $rider_email = "user@demo.com";
  $driver_note = "If you have registered as a new towing driver, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "tutors") {
  $rider_email = "student@demo.com";
  $driver_email = "tutor@demo.com";
  $driver_note = "If you have registered as a new Tutor, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "ufxforall") {
  $rider_email = "provider@demo.com";
  $driver_email = "user@demo.com";
  $driver_note = "If you have registered as a new provider, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } elseif ($host_system == "ufxforall4" || $domain == "cubejek") {
  $company_email = "company@gmail.com";
  $driver_email = "provider@demo.com";
  $rider_email = "user@demo.com";
  $driver_note = "If you have registered as a new provider, use your registered Email Id and Password to view the detail of your Jobs.<br />To view the Standard Features of the Apps use below access detail";
  } else {
  $rider_email = "rider@gmail.com";
  $company_email = "company@gmail.com";
  $driver_email = "driver@gmail.com";
  $driver_note = "If you have registered as a new Driver, use your registered Email Id and Password to view the detail of your Rides.<br />To view the Standard Features of the Apps use below access detail";
  }
  } else { */
$rider_email = $driver_email = $company_email = $driver_note = $rider_note = $pwd = '';
$loginblockfooter = 0;
//}
$db_forgot = $generalobj->getStaticPage(49, $_SESSION['sess_lang']);
if (empty($db_forgot['page_title'])) {
    $db_forgot = $generalobj->getStaticPage(49, 'EN');
}

//$sql_forgot = "SELECT * FROM pages WHERE vPageName = 'Forgot-Password'";
//$db_forgot = $obj->MySQLSelect($sql_forgot);

$bg_img = "login-bg.jpg";
$left_img = "login-img.jpg";

if (!empty($db_forgot['vImage1']))
    $bg_img = $db_forgot['vImage1'];

if (!empty($db_forgot['vImage']))
    $left_img = $db_forgot['vImage'];

//if(empty($template)) $template = 'Cubex';

$bg_forgot_image = "assets/img/apptype/$template/" . $bg_img;
//$bg_forgot_image = "assets/img/apptype/Cubex/login-bg_20190820103554.jpg";
$db_forgot_src = "assets/img/apptype/$template/" . $left_img;

$bg_login_image = "assets/img/apptype/$template/login-bg.jpg";
$db_login_src = "assets/img/apptype/$template/login-img.jpg";

$db_signin = $generalobj->getStaticPage(48, $_SESSION['sess_lang']);

$pagesubtitle = json_decode($db_signin[0]['pageSubtitle'], true);
$pagesubtitle_lang = $pagesubtitle["pageSubtitle_" . $_SESSION['sess_lang']];

if (empty($pagesubtitle_lang)) {
    $db_signin = $generalobj->getStaticPage(48, 'EN');
    $pagesubtitle = json_decode($db_signin[0]['pageSubtitle'], true);
    $pagesubtitle_lang = $pagesubtitle["pageSubtitle_" . 'EN'];
}
$loginpage_title = json_decode($db_signin['page_title'], true);
$loginpage_desc = json_decode($db_signin['page_desc'], true);

if (!empty($db_signin['vImage1']))
    $bg_login_image = "assets/img/apptype/$template/" . $db_signin['vImage1'];
if (!empty($db_signin['vImage']))
    $db_login_src = "assets/img/apptype/$template/" . $db_signin['vImage'];

$serviceArray = $serviceIdArray = array();
$serviceArray = json_decode(serviceCategories, true);
$serviceIdArray = array_column($serviceArray, 'iServiceId');

$become_restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {
    if (count($serviceIdArray) == 1 && $serviceIdArray[0]==1) {
        $become_restaurant = $langage_lbl['LBL_RESTAURANT_TXT'];
    } else {
        $become_restaurant = $langage_lbl['LBL_STORE'];
    }
}
$hotelPanel = isHotelPanelEnable();
$cubeDeliverallOnly = isDeliverAllOnlySystem(); // Added By HJ On 16-06-2020 For Custome Setup CubejekX Deliverall
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
        <title><?php echo $meta_arr['meta_title']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
    </head>
    <body id="wrapper">
        <!-- home page -->
        <!-- home page -->
        <?php if ($template != 'taxishark') { ?>
            <div id="main-uber-page">
            <?php } ?>
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- First Section -->
            <?php include_once("top/header.php"); ?>
            <!-- End: First Section -->
            <?php //include_once("cx-sign-in-middle.php"); ?>
            <div class="login-main parallax-window" style="background-image:url(<?php echo $bg_login_image; ?>)">
                <div class="login-inner">
                    <div class="login-block">
                        <div class="login-block-heading login-newblock">
                            <label id="loginlabel" class="loginlabel"><?= $langage_lbl['LBL_LOGIN_AS'] ?></label>
                            <label id="forgotlabel" style="display:none"><?= $db_forgot['page_title']; ?></label>
                            <div class="tabholder login-tabholder">
                                <ul class="tab-switch login-tab-switch"> 
                                    <li <?php if ($_REQUEST['type'] == 'user' || $_REQUEST['type'] == 'rider' || $_REQUEST['type'] == 'sender' || $_REQUEST['type'] == '') { ?>class="active" <?php } ?> data-id="user" data-desc="<?= $loginpage_title['user'] ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_SIGNIN_RIDER'] ?></a></li>
                                    <li <?php if ($_REQUEST['type'] == 'provider' || $_REQUEST['type'] == 'driver' || $_REQUEST['type'] == 'carrier') { ?>class="active" <?php } ?> data-id="provider" data-desc="<?= $loginpage_title['provider'] ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_SIGNIN_DRIVER'] ?></a></li>
                                    <? if(strtoupper(ONLYDELIVERALL) != "YES" && $cubeDeliverallOnly == false) { ?><li <?php if ($_REQUEST['type'] == 'company') { ?>class="active" <?php } ?> data-id="company" data-desc="<?= $loginpage_title['company'] ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_COMPANY_SIGNIN'] ?></a></li><? } ?>
                                    <? if (!empty($become_restaurant)) { ?><li <?php if ($_REQUEST['type'] == 'restaurant' || $_REQUEST['type'] == 'store') { ?>class="active" <?php } ?> data-id="restaurant" data-desc="<?= $loginpage_title['restaurant'] ?>"><a href="JavaScript:void(0);"><?= $become_restaurant ?></a></li>
                                    <? } if(strtoupper($ENABLE_CORPORATE_PROFILE)=='YES') { ?><li <?php if ($_REQUEST['type'] == 'organization') { ?>class="active" <?php } ?> data-id="organization" data-desc="<?= $loginpage_title['org'] ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_ORGANIZATION'] ?></a></li>
                                    <?php } if($hotelPanel > 0) { ?><li <?php if ($_REQUEST['type'] == 'hotel') { ?>class="active" <?php } ?> data-id="hotel" data-desc="<?= $loginpage_title['hotel'] ?>"><a href="JavaScript:void(0);"><?= $langage_lbl['LBL_HOTEL_LOGIN'] ?></a></li><?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="login-left">
                            <img src="<?php echo $db_login_src; ?>" alt="">
                            <div class="login-caption active" id="user">
                                <?= $loginpage_desc['user']; ?>
                            </div>
                            <div class="login-caption" id="provider">
                                <?= $loginpage_desc['provider']; ?>
                            </div>
                            <div class="login-caption" id="company">
                                <?= $loginpage_desc['company']; ?>
                            </div>
                            <div class="login-caption" id="restaurant">
                                <?= $loginpage_desc['restaurant']; ?>
                            </div>
                            <div class="login-caption" id="organization">
                                <?= $loginpage_desc['org']; ?>
                            </div>
                            <div class="login-caption" id="hotel">
                                <?= $loginpage_desc['hotel']; ?>
                            </div>
                        </div>
                        <div class="login-right" id="login_div">
                            <div class="login-data-inner">
                                <h1><?= $pagesubtitle_lang ?></h1>
                                <p><?= $loginpage_title['user']; ?></p>
                                <div class="form-err">
                                    <span style="display:none;" id="msg_close" class="msg_close error-login-v">&#10005;</span>
                                    <p id="errmsg" style="display:none;" class="text-muted btn-block btn btn-danger btn-rect error-login-v"></p>
                                    <p style="display:none;background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="success" ></p>
                                </div>
                                <?php
                                if ($action == 'rider') {
                                    $action_url = 'mytrip.php';
                                } else if ($action == 'driver' && $iscompany != "1") {
                                    //$action_url = 'profile.php';
                                    //$action_url = 'driver-profile';
                                    $action_url = 'profile';
                                } else {
                                    $action_url = 'dashboard.php';
                                }
                                ?>
                                <form action="<?php echo $action_url; ?>" onSubmit="return chkValid();" id="login_box" name="login_form">
                                    <input type="hidden" name="action" class="action" value="rider" />
                                    <input type="hidden" name="action_url" id="action_url" value="dashboard.php" />
                                    <input type="hidden" name="iscompany" class="iscompany" value="0" />
                                    <input type="hidden" name="type_usr" id="type_usr" value="Driver"/>
                                    <div class="form-group">
                                        <label class="hotelshow" style="display:none"><?= $langage_lbl['LBL_EMAIL']; ?></label>
                                        <label class="hotelhide"><?= $langage_lbl['LBL_EMAIL_MOBILE_NO_TXT_MSG']; ?></label>
                                        <input tabindex="1" type="text" name="vEmail" id="vEmail" value="" required/>
                                        
                                    </div>
                                    <div class="mobile-info" style="margin: -15px 0 20px 0; font-size: 11px;"><?= $langage_lbl['LBL_SIGN_IN_MOBILE_EMAIL_HELPER']; ?></div>
                                    <div class="form-group">
                                        <div class="relative_ele">
                                            <label><?= $langage_lbl['LBL_PASSWORD_LBL_TXT']; ?></label>
                                            <input tabindex="2" type="password" name="vPassword" id="vPassword" value="<?= (SITE_TYPE == 'Demo') ? '123456' : '' ?>" />
                                            <!--<button type="button" onclick="showHidePassword('vPassword')" id="eye"><img src="assets/img/eye.png" alt="eye"/></button>-->
                                        </div>
                                    </div>
                                    <div class="button-block">
                                        <div class="btn-hold">
                                            <input tabindex="3" type="submit" value="<?= $langage_lbl['LBL_LOGIN']; ?>"/>
                                            <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                        </div>
                                        <a href="javascript:void(0)" onClick="change_heading('forgot');" tabindex="4" class="hotelhide"><?= $langage_lbl['LBL_FORGET_PASS_TXT']; ?></a>
                                    </div>
                                    <div class="member-txt hotelhide">
                                        <?= $langage_lbl['LBL_DONT_HAVE_AN_ACCOUNT'] ?> <a href="sign-up-rider" tabindex="5" id="signinlink"><?= $langage_lbl['LBL_SIGNUP'] ?></a>
                                    </div>
                                    <?php if ($DRIVER_GOOGLE_LOGIN == "Yes" || $DRIVER_FACEBOOK_LOGIN == "Yes" || $DRIVER_LINKEDIN_LOGIN == "Yes") { ?>
                                        <span id="driver-social" style="display:none">
                                            <div class="aternate-login" data-name="OR"></div>
                                            <div class="soc-login-row">
                                                <label><?= $langage_lbl['LBL_LOGIN_WITH_SOCIAL_ACC']; ?></label>
                                                <ul class="social-list">
                                                    <?php if ($DRIVER_FACEBOOK_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="facebook/driver" tabindex="6"><img src="assets/img/page/facebook-new.png" alt="Facebook"></a></li>
                                                    <?php } if ($DRIVER_LINKEDIN_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="linkedin/driver" tabindex="7"><img src="assets/img/page/linkedin-new.png" alt="Linkedin"></a></li>
                                                    <?php } if ($DRIVER_GOOGLE_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="google/driver" tabindex="8"><img src="assets/img/page/google-new.png" alt="Google Plus"></a></li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </span>
                                    <?php } if ($PASSENGER_FACEBOOK_LOGIN == "Yes" || $PASSENGER_GOOGLE_LOGIN == "Yes" || $PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                        <span id="rider-social">
                                            <div class="aternate-login" data-name="OR"></div>
                                            <div class="soc-login-row">
                                                <label><?= $langage_lbl['LBL_LOGIN_WITH_SOCIAL_ACC']; ?></label>
                                                <ul class="social-list">
                                                    <?php if ($PASSENGER_FACEBOOK_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="facebook-rider/rider" tabindex="6"><img src="assets/img/page/facebook-new.png" alt="Facebook"></a></li>
                                                    <?php } if ($PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="linkedin-rider/rider" tabindex="7"><img src="assets/img/page/linkedin-new.png" alt="Linkedin"></a></li>
                                                    <?php } if ($PASSENGER_GOOGLE_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="google/rider" tabindex="8"><img src="assets/img/page/google-new.png" alt="Google Plus"></a></li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </span>
                                    <?php } ?>
                                </form>
                            </div>
                        </div>
                        <div class="login-right" id="forgot_div" style="display:none">
                            <div class="login-data-inner">
                                <h1 id="forgot-user-label"></h1>
                                <span id="forgot_div_desc"><?= $db_forgot['page_desc']; ?></span>
                                <div class="form-err">
                                    <span id="msg_closef" style="display:none;" class="msg_close error-login-v">&#10005;</span>
                                    <p id="errmsgf" style="display:none;" class="text-muted btn-block btn btn-danger btn-rect error-login-v"></p>
                                    <p style="display:none;background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="successf" ></p>
                                </div>
                                <form action="" method="post" class="form-signin" id="frmforget" onSubmit="return forgotPass();">
                                    <input type="hidden" name="action" class="action" value="rider">
                                    <input type="hidden" name="iscompany" class="iscompany" value="0">
                                    <div class="form-group">
                                        <label><?= $langage_lbl['LBL_EMAIL_TEXT']; ?></label>
                                        <input type="email" name="femail" tabindex="1" id="femail" required />
                                    </div>
                                    <div class="button-block">
                                        <div class="btn-hold">
                                            <input type="submit" id="btn_submit" tabindex="2" value="<?= $langage_lbl['LBL_Recover_Password']; ?>"/>
                                            <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                        </div>
                                    </div>
                                    <div class="aternate-login" data-name="OR"></div>
                                    <div class="member-txt">
                                        <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?> <a href="javascript:void(0)" onClick="change_heading('login');"><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="login-block-footer" <?php if ($loginblockfooter == 0) { ?>style="display:none"<?php } ?>>
                            <div class="note-holder active user" id="user">
                                <b><i class="fa fa-sticky-note"></i>Note :</b>
                                <p><?= $rider_note ?></p>
            
                                <b><i class="fa fa-user"></i>Rider :</b> 
                                <p>Username: <?= $rider_email ?><br>
                                    Password: 123456</p>
                            </div>
                            <div class="note-holder provider">
                                <b><i class="fa fa-sticky-note"></i>Note :</b>
                                <p><?= $driver_note ?></p>
            
                                <b><i class="fa fa-user"></i>Provider :</b> 
                                <p>Username: <?= $driver_email ?><br>
                                    Password: 123456</p>
                            </div>
                            <div class="note-holder company">
                                <b><i class="fa fa-sticky-note"></i>Note :</b>
                                <p>If you have registered as a new driver, use your registered Email Id and Password to view the detail of your Rides.
                                    To view the Standard Features of the Apps use below access details.</p>
            
                                <b><i class="fa fa-building"></i>Company :</b>
                                <p>Username: <?= $company_email ?><br>
                                    Password: 123456</p>
                            </div>
                            <!--<div class="note-holder organization">
                                <b><i class="fa fa-sticky-note"></i>Note :</b>
                                <p>If you have registered as a new Rider, use your registered Email Id and Password to view the detail of your Rides.
                                To view the Standard Features of the Apps use below access details.</p>
            
                                <b><i class="fa fa-sitemap"></i>Organization :</b> 
                                <p>Username: organization@gmail.com<br>
                                Password: 123456</p>
                            </div>-->
                        </div>
                    </div>
                </div>
            </div>
            <!-- home page end-->
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>

            <div style="clear:both;"></div>
            <?php if ($template != 'taxishark') { ?>
            </div>
        <?php } ?>
        <!-- footer part end -->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <!-- End: Footer Script -->
        
        <script>
        $("document").ready(function () {
            type = '<?php echo $_REQUEST['type'] ?>';
            if (type != '') {
                if (type == 'restaurant' || type == 'store') {
                    $('.tab-switch li[data-id="restaurant"]').get(0).click();
                } else if (type == 'rider' || type == 'user') {
                    $('.tab-switch li[data-id="user"]').get(0).click();
                } else if (type == 'provider' || type == 'driver' || type == 'carrier') {
                    $('.tab-switch li[data-id="provider"]').get(0).click();
                } else {
                    $('.tab-switch li[data-id="' + type + '"]').get(0).click();
                }
            }
        });
        
        $(".tab-switch li").on("click", function () {
                var dataId = $(this).attr("data-id");
                if(dataId != "hotel")
                {
                    $('.mobile-info').show();
                }
                else{
                    $('.mobile-info').hide();   
                }
                if (dataId == 'restaurant1') {
    
                    $("#signinlink").attr("href", "sign-up?type=restaurant");
                } else {
                    $("#signinlink").attr("href", "sign-up?type=" + dataId);
                }
                if (dataId == 'user') {
                    action_dataId = 'rider';
                    action_url = 'mytrip.php';
                    $(".iscompany").val(0);
                    $("#type_usr").val('Rider');
    
                    $("#rider-social").show();
                    $("#driver-social, #company-social").hide();
    
                    $("#vEmail").val('<?php echo $rider_email; ?>');
                    $("#vPassword").val('<?php echo $pwd; ?>');
    
                    <?php if ($loginblockfooter == 1) { ?>
                        $(".login-block-footer").show();
                    <?php } ?>
    
                } else if (dataId == 'provider') {
                    action_dataId = 'driver';
                    action_url = 'profile';
                    //action_url = 'profile.php';
                    $(".iscompany").val(0);
                    $("#type_usr").val('Driver');
    
                    $("#rider-social").hide();
                    $("#driver-social").show();
    
                    $("#vEmail").val('<?php echo $driver_email; ?>');
                    $("#vPassword").val('<?php echo $pwd; ?>');
    
                    <?php if ($loginblockfooter == 1) { ?>
                        $(".login-block-footer").show();
                    <?php } ?>
    
                } else if (dataId == 'company' || dataId == 'restaurant') {
                    action_dataId = 'driver';
                    action_url = 'dashboard.php';
                    $(".iscompany").val(1);
                    $("#type_usr").val('Company');
    
                    $("#rider-social,#driver-social").hide();
                    //$("#company-social").show();
    
                    $("#vEmail").val('<?php echo $company_email; ?>');
                    $("#vPassword").val('<?php echo $pwd; ?>');
    
                    <?php if ($loginblockfooter == 1) { ?>
                        $(".login-block-footer").show();
                    <?php } ?>
    
                } else if (dataId == 'organization') {
                    action_dataId = 'organization';
                    action_url = 'organization-profile';
                    $(".iscompany").val(0);
                    $("#type_usr").val('organization');
    
                    $("#rider-social,#driver-social").hide();
                    //$("#company-social").show();
    
                    $("#vEmail,#vPassword").val('');
    
                    $(".login-block-footer").hide();
    
    
                } else if (dataId == 'hotel') {
                    action_dataId = 'hotel';
                    action_url = 'dashboard.php';
                    $("#type_usr").val('hotel');
                    $("#rider-social,#driver-social").hide();
                    $("#vEmail").val('<?php echo $company_email; ?>');
                    $("#vPassword").val('<?php echo $pwd; ?>');
    
                    <?php if ($loginblockfooter == 1) { ?>
                        $(".login-block-footer").show();
                    <?php } ?>
    
                } else {
                    action_dataId = 'rider';
                    action_url = 'dashboard.php';
    
                }
                if (dataId == 'hotel') {
                    $(".hotelhide").hide();
                    $(".hotelshow").show();
                } else {
                    $(".hotelhide").show();
                    $(".hotelshow").hide();
                }
                $(".action").val(action_dataId);
                $("#action_url").val(action_url);
                //errmsg
                document.getElementById("errmsg").innerHTML = '';
                document.getElementById("errmsg").style.display = 'None';
                document.getElementById("msg_close").style.display = 'None';
            });
    
        $(document).ready(function () {
    
            /* $("#login_box").validate({
             rules: {
             email: {
             required: true,
             email: true
             },
             password: {
             required: true,
             minlength: 8
             },
             password_confirm: {
             required: true,
             minlength: 8,
             equalTo: "#password"
             }
             },
             messages: {
             email: "Please enter a valid email address",
             password: {
             required: "Please provide a password",
             minlength: "Your password must be at least 8 characters long",
             },
             password_confirm: {
             required: "Please provide a password",
             minlength: "Your password must be at least 8 characters long",
             equalTo: "Please enter the same password as above"
             }
             }
             });*/
            var err_msg = '<?= $err_msg ?>';
            if (err_msg != "") {
                document.getElementById("errmsg").innerHTML = err_msg;
                document.getElementById("errmsg").style.display = '';
                document.getElementById("msg_close").style.display = '';
                
                return false;
            }
            //alert('<?php echo $rider_email; ?>');
            $("#vEmail").val('<?php echo $rider_email; ?>');
            $("#vPassword").val('<?php echo $pwd; ?>');
    
            
        });
        function parallaxReinit() {
            setTimeout(function () {
                $('.parallax-window').parallax('destroy');
                $('.parallax-window').parallax();
            }, 100);
        }
        function chkValid()
        {
            parallaxReinit();
            login_type = $(".action").val();
            iscompany = $(".iscompany").val();
            var id = document.getElementById("vEmail").value;
            var pass = document.getElementById("vPassword").value;
            var selTabType = $("#type_usr").val();
            if (id == '' || pass == '')
            {
                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_EMAIL_PASS_ERROR_MSG']); ?>';
                document.getElementById("errmsg").style.display = '';
                document.getElementById("msg_close").style.display = '';
                return false;
            } else
            {
                if (login_type == 'organization') {
                    url = 'ajax_organization_login_action.php';
                } else {
                    url = 'ajax_login_action.php';
                }
    
                var request = $.ajax({
                    type: "POST",
                    url: url,
                    data: $("#login_box").serialize(),
    
                    success: function (data)
                    {
    
                        jsonParseData = JSON.parse(data);
                        login_status = jsonParseData.login_status;
                        eSystem = jsonParseData.eSystem;
                        if (login_status == 1) {
                            document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ACC_DELETE_TXT']); ?>';
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
    
                        } else if (login_status == 2) {
    
                            document.getElementById("errmsg").style.display = 'none';
                            document.getElementById("msg_close").style.display = 'none';
                            departType = '<?php echo $depart; ?>';
                            if (login_type == 'rider' && departType == 'mobi')
                                window.location = "mobi";
                            else if (login_type == 'driver' && iscompany == "1" && eSystem == "DeliverAll")
                                //window.location = "dashboard.php";
                                window.location = "profile"; // Redirect to company profile page if logged in as store
                            else if (login_type == 'driver' && iscompany == "1")
                                window.location = "profile";
                            else if (login_type == 'driver')
                                // window.location = "profile.php";
                                //window.location = "driver-profile"; // New Profile design URL
                                window.location = "profile"; // New Profile design URL
                            else if (login_type == 'rider') {
                                var url = getCookie('ManualBookingURL');
                                if (url != null) {
                                    setCookie('ManualBookingURL', "");
                                    window.location = url;
                                } else {
    <? if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
                                        window.location = "profile-user";
                                        //window.location = "user-profile"; // New User Profile design URL
    <? } else { ?>
                                        window.location = "profile-rider";
    <? } ?>
                                }
                            } else if (login_type == 'organization') {
                                window.location = "organization-profile";
                            } else if (login_type == 'hotel') {
                                window.location = "admin/dashboard.php";
                            }
                            return true; // success registration
                        } else if (login_status == 3) {
                            if (selTabType == "hotel") {
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_PASS_ERROR_MSG']); ?>';
                            } else {
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']); ?>';
                            }
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
                            
                        } else if (login_status == 4) {
                            document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG']); ?>';
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
                        } else {
                            if (selTabType == "hotel") {
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_PASS_ERROR_MSG']); ?>';
                            } else {
                                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']); ?>';
                            }
                            document.getElementById("errmsg").style.display = '';
                            document.getElementById("msg_close").style.display = '';
                        }
                        /*if ($('#errmsg').html() != '') {
                         setTimeout(function () {
                         $('#errmsg').fadeOut();
                         }, 2000);
                         }*/
                    }
                });
    
                request.fail(function (jqXHR, textStatus) {
                    alert("Request failed: " + textStatus);
                    return false;
                });
                return false;
            }
            /*if ($('#errmsg').html() != '') {
             setTimeout(function () {
             $('#errmsg').fadeOut();
             }, 2000);
             }*/
        }
        function setCookie(key, value) {
            var expires = new Date();
            expires.setTime(expires.getTime() + (1 * 24 * 60 * 60 * 1000));
            document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
        }
    
        function getCookie(key) {
            var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
            return keyValue ? keyValue[2] : null;
        }
    
        function change_heading(type)
        {
            $('.error-login-v').hide();
            if (type == 'forgot') {
    
                $('#forgot_div').show();
    
                $("#frmforget .form-group, #frmforget .button-block").show();
    
                $('#login_div').hide();
    
                $('.login-caption, .login-block-footer .note-holder').removeClass('active');
                $('.login-block-footer, .tab-switch').hide();
    
                $('.login-block-heading').addClass('forget_label');
    
                lbl = $('ul.tab-switch li.active a').text();
    
                //$('.forget_label label').text(lbl);
    
                $("#forgot-user-label").text(lbl);
                $("#forgot_div_desc").html("<?php echo $generalobj->getProperDataValue($db_forgot['page_desc']); ?>"); //getProperDataValue used bc when in desc-editor inline css applied with double quotes then here string will be broken so put it discussed with KS
                //$(".login-main").attr("data-image-src","<?php echo $bg_forgot_image; ?>");
                //$(".parallax-slider").attr("src", "<?php echo $bg_forgot_image; ?>");
                $(".login-main").css("background-image", "url(<?php echo $bg_forgot_image; ?>)");
                $(".login-left img").attr("src", "<?php echo $db_forgot_src; ?>");
    
                $('.login-block').addClass('for-forgot');
    
                $("#forgotlabel").show();
                $("#loginlabel").hide();
    
                $('.tab-switch').removeClass('login-tab-switch');
                $('.tabholder').removeClass('login-tabholder');
    
            } else {
                $('#forgot_div').hide();
                $('#login_div').show();
    
                $('.login-block').removeClass('for-forgot');
    
    <?php if ($loginblockfooter == 1) { ?>
                    $(".login-block-footer").show();
    <?php } ?>
    
                $('.tab-switch').show();
    
                $('ul.tab-switch li.active').trigger("click"); //its bc active tab data appears when again comeback to signin from diff tab
    
                $('.login-block-heading').removeClass('forget_label');
                //$('.login-block-heading label').text('Login As');
    
                //$(".login-main").attr("data-image-src","<?php echo $bg_login_image; ?>");
                //$(".parallax-slider").attr("src", "<?php echo $bg_login_image; ?>");
                $(".login-main").css("background-image", "url(<?php echo $bg_login_image; ?>)");
                $(".login-left img").attr("src", "<?php echo $db_login_src; ?>");
    
                $("#loginlabel").show();
                $("#forgotlabel").hide();
    
                $('.tab-switch').addClass('login-tab-switch');
                $('.tabholder').addClass('login-tabholder');
            }
        }
    
        function forgotPass()
        {
            $('.error-login-v').hide();
            $("#btn_submit").val("<?= $langage_lbl['LBL_PLEASE_WAIT'] ?> ...").attr('disabled','disabled');
            var site_type = '<? echo SITE_TYPE; ?>';
            var id = document.getElementById("femail").value;
            if (id == '')
            {
                document.getElementById("errmsg").style.display = '';
                document.getElementById("msg_close").style.display = '';
                document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR_TXT_IPHONE']); ?>';
            } else {
                var request = $.ajax({
                    type: "POST",
                    url: 'ajax_fpass_action.php',
                    data: $("#frmforget").serialize(),
                    dataType: 'json',
                    beforeSend: function ()
                    {
                        //alert(id);
                    },
                    success: function (data)
                    {
                        if (data.status == 1)
                        {
                            //change_heading('login');
                            document.getElementById("successf").innerHTML = data.msg;
                            document.getElementById("successf").style.display = '';
                            $("#frmforget .form-group, #frmforget .button-block").hide();
                        } else
                        {
                            document.getElementById("errmsgf").innerHTML = data.msg;
                            document.getElementById("errmsgf").style.display = '';
                            document.getElementById("msg_closef").style.display = '';
                        }
    
                    }
                });
    
                request.fail(function (jqXHR, textStatus) {
                    alert("Request failed: " + textStatus);
                });
    
    
            }
            return false;
        }
        </script>
    </body>
</html>
