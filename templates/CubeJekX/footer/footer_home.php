</div>
<?php
$sql = "select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
$db_lng_mst = $obj->MySQLSelect($sql);
$count_lang = count($db_lng_mst);

if (isset($_POST['vNamenewsletter'])) {
    if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $valiedRecaptch = $generalobj->checkRecaptchValied($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);

        if ($valiedRecaptch) {
            $vNamenewsletter = trim($_REQUEST['vNamenewsletter']);
            $vEmailnewsletter = trim($_REQUEST['vEmailnewsletter']);
            $eStatus = trim($_REQUEST['eStatus']);
            $remoteIp = $_SERVER['REMOTE_ADDR'];
            $dateTime = date("Y-m-d H:i:s");

            $chkUser = "SELECT * FROM `newsletter` WHERE vEmail = '" . $vEmailnewsletter . "' ";
            $chkUserCnt = $obj->MySQLSelect($chkUser);
            $fetchStatus = $chkUserCnt[0]['eStatus'];

            if (count($chkUserCnt) > 0) {

                if (($fetchStatus == "Unsubscribe") && ($eStatus == "Unsubscribe")) {
                    header("Location:thank-you.php?action=Alreadyunsubscribe");
                    exit;
                } if (($fetchStatus == "Subscribe") && ($eStatus == "Subscribe")) {
                    header("Location:thank-you.php?action=Alreadysubscribe");
                    exit;
                }
                if (($fetchStatus == "Subscribe") && ($eStatus == "Unsubscribe")) {
                    $maildata['EMAIL'] = $vEmailnewsletter;
                    $maildata['NAME'] = $vNamenewsletter;
                    $maildata['EMAILID'] = $SUPPORT_MAIL;
                    $maildata['PHONENO'] = $SUPPORT_PHONE;

                    $generalobj->send_email_user("MEMBER_NEWS_UNSUBSCRIBE_USER", $maildata);
                }
                if (($fetchStatus == "Unsubscribe") && ($eStatus == "Subscribe")) {
                    $maildata['EMAIL'] = $vEmailnewsletter;
                    $maildata['NAME'] = $vNamenewsletter;
                    $maildata['EMAILID'] = $SUPPORT_MAIL;
                    $maildata['PHONENO'] = $SUPPORT_PHONE;

                    $generalobj->send_email_user("MEMBER_NEWS_SUBSCRIBE_USER", $maildata);
                }

                $insert_query = "UPDATE newsletter SET vName='" . $vNamenewsletter . "', vIP='" . $remoteIp . "',tDate='" . $dateTime . "', eStatus = '" . $eStatus . "' WHERE vEmail='" . $vEmailnewsletter . "'";
            } else {

                if ((count($chkUserCnt) == 0) && $eStatus == 'Unsubscribe') {
                    header("Location:thank-you.php?action=Notsubscribe");
                    exit;
                }
                if ($eStatus == 'Subscribe') {
                    $maildata['EMAIL'] = $vEmailnewsletter;
                    $maildata['NAME'] = $vNamenewsletter;
                    $maildata['EMAILID'] = $SUPPORT_MAIL;
                    $maildata['PHONENO'] = $SUPPORT_PHONE;

                    $generalobj->send_email_user("MEMBER_NEWS_SUBSCRIBE_USER", $maildata);
                }

                $insert_query = "INSERT INTO newsletter SET vName='" . $vNamenewsletter . "',vEmail='" . $vEmailnewsletter . "',vIP='" . $remoteIp . "',tDate='" . $dateTime . "', eStatus = '" . $eStatus . "' ";
            }
            $obj->sql_query($insert_query);
            header("Location: thank-you.php?action=$eStatus");
            exit;
        } else {
            header("Location: thank-you.php?action=Recaptchafail");
            exit;
        }
    } else {
        //$obj->sql_query($insert_query);
        header("Location: thank-you.php?action=Recaptchafail");
        exit;
    }
}

//added by SP for pages orderby,active/inactive functionality start
$default_lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
$PagesData = $obj->MySQLSelect("SELECT iPageId,vPageTitle_$default_lang as pageTitle FROM `pages` WHERE iPageId IN (1,2,4,6,7,33,3) AND eStatus = 'Active' ");

// print_r($PagesData );
$privacyPages = [];
$pageCount = 0;
$helpCenter = '';
$aClass = '';
foreach ($PagesData as $key => $value) {
    if ($value['iPageId'] == 1) {
        $aClass = strpos($_SERVER['REQUEST_URI'], 'about') ? 'active' : '';
        $displayPages[$pageCount] = '<a href="about" class="' . $aClass . '">' . $value['pageTitle'] . '</a>';
    } else if ($value['iPageId'] == 2) {
        $aClass = strpos($_SERVER['REQUEST_URI'], 'help-center') ? 'active' : '';
        $helpCenter = '<a href="help-center" class="' . $aClass . '">' . $value['pageTitle'] . '</a>';
    } else if ($value['iPageId'] == 4) {
        $aClass = strpos($_SERVER['REQUEST_URI'], 'terms-condition') ? 'active' : '';
        $privacyPages[] = '<a href="terms-condition" class="' . $aClass . '">' . $value['pageTitle'] . '</a>';
    } else if ($value['iPageId'] == 6) {
        $aClass = strpos($_SERVER['REQUEST_URI'], 'how-it-works') ? 'active' : '';
        $displayPages[$pageCount] = '<a href="how-it-works" class="' . $aClass . '">' . $value['pageTitle'] . '</a>';
    } else if ($value['iPageId'] == 7) {
        $aClass = strpos($_SERVER['REQUEST_URI'], 'trust-safety-insurance') ? 'active' : '';
        $displayPages[$pageCount] = '<a href="trust-safety-insurance" class="' . $aClass . '">' . $value['pageTitle'] . '</a>';
    } else if ($value['iPageId'] == 3) {
        $aClass = strpos($_SERVER['REQUEST_URI'], 'legal') ? 'active' : '';
        $displayPages[$pageCount] = '<a href="legal" class="' . $aClass . '">' . $value['pageTitle'] . '</a>';
    } else if ($value['iPageId'] == 33) {
        $aClass = strpos($_SERVER['REQUEST_URI'], 'privacy-policy') ? 'active' : '';
        $privacyPages[] = '<a href="privacy-policy" class="' . $aClass . '">' . $value['pageTitle'] . '</a>';
    }
    $pageCount++;
}
//added by SP for pages orderby,active/inactive functionality end

$sql = "select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' AND vCode = '$default_lang'";
$db_lng_mst = $obj->MySQLSelect($sql);

$serviceArray = $serviceIdArray = array();
$serviceArray = json_decode(serviceCategories, true);
$serviceIdArray = array_column($serviceArray, 'iServiceId');
$become_restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {

    if (count($serviceIdArray) == 1 && $serviceIdArray[0]==1) {

        $become_restaurant = $langage_lbl['LBL_FOOTER_LINK_BECOME_RESTAURANT'];

        $link_store = "sign-up?type=restaurant";

    } else {

        $become_restaurant = $langage_lbl['LBL_FOOTER_LINK_BECOME_STORE'];

        $link_store = "sign-up?type=store";

    }
}


$link_user = "sign-up?type=user";
$link_driver =  "sign-up?type=driver";
$label_user_footer = $langage_lbl['LBL_FOOTER_LINK_BECOME_USER'];
$label_driver_footer = $langage_lbl['LBL_FOOTER_LINK_BECOME_DRIVER'];
$label_driver = $langage_lbl['LBL_SIGNUP_DRIVE'];
$label_user = $langage_lbl['LBL_SIGNUP_RIDE'];

if($generalobj->checkDeliveryXThemOn()=='Yes') {

    $link_driver =  "sign-up?type=carrier";
    $link_user = "sign-up?type=sender";
    $label_driver_footer = $langage_lbl['LBL_FOOTER_LINK_BECOME_CARRIER'];
    $label_user_footer = $langage_lbl['LBL_FOOTER_LINK_BECOME_SENDER'];
    $label_driver = $langage_lbl['LBL_SIGNUP_DELIVER'];
    $label_user = $langage_lbl['LBL_SIGNUP_SEND_PARCEL'];

} else if($generalobj->checkCubeJekXThemOn()=='Yes') {
    $link_driver =  "sign-up?type=provider";
    $label_driver_footer = $langage_lbl['LBL_FOOTER_LINK_BECOME_PROVIDER'];
    $label_driver = $langage_lbl['LBL_SIGN_UP_AS_PROVIDER'];
    $label_user = $langage_lbl['LBL_SIGN_UP_AS_USER'];

} else if($generalobj->checkCubexThemOn()=='Yes') {
   $link_driver =  "sign-up?type=provider";
   $label_driver_footer = $langage_lbl['LBL_FOOTER_LINK_BECOME_PROVIDER'];

} else if($generalobj->checkDeliverallXThemOn() == 'Yes') {
    $label_driver = $langage_lbl['LBL_SIGN_UP_AS_DRIVER'];
    $label_user = $langage_lbl['LBL_SIGN_UP_AS_USER'];
}

//$vcatdata = $generalobj->getSeviceCategoryDataForHomepage();

$tableName = $generalobj->getAppTypeWiseHomeTable();

$book_data = $obj->MySQLSelect("SELECT booking_ids FROM $tableName WHERE vCode = '" . $_SESSION['sess_lang'] . "'");



$vcatdata_first = $generalobj->getSeviceCategoryDataForHomepage($book_data[0]['booking_ids'], 0, 1);

if (count($vcatdata_first) < 6) {

    $vcatdata_sec = $generalobj->getSeviceCategoryDataForHomepage($book_data[0]['booking_ids'], 1, 1);

    $vcatdata = array_merge($vcatdata_first, $vcatdata_sec);

}

$vcatdata = array_slice($vcatdata_first, 0, 6, true);

//$catquery = "SELECT vHomepageLogo,vCategory_".$_SESSION['sess_lang']." as vCatName FROM  `vehicle_category` WHERE iParentId = 0 and eStatus = 'Active' ORDER BY iDisplayOrderHomepage ASC LIMIT 0,5";

//$vcatdata = $obj->MySQLSelect($catquery);



 ?>
<!-- *************signup section start************* -->

<?php
if (isset($showSignRegisterLinks) && $showSignRegisterLinks == 1) {
    if ($_SESSION['sess_user'] == "") {
        ?>
        <div class="signup-row">
            <div class="signup-row-inner">
                <div class="signup-block driver">

                    <a href="<?= $link_driver ?>" data-name="<?= $label_driver ?>"><?= $label_driver ?><img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/right-arrow.svg" ?>" alt=""></a>

                </div>
                <div class="signup-block rider">

                    <a href="<?= $link_user ?>" data-name="<?= $label_user ?>"><?= $label_user ?> <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . "/right-arrow.svg" ?>" alt=""></a>

                </div>
            </div>
        </div>
    <?php }
}
?>

<!-- *************signup section end************* -->

<footer>
    <div class="footer-top">
        <div class="footer-inner">
            <div class="footer-column">
                <h4><?php echo $langage_lbl['LBL_FOOTER_SUPPORT']; ?></h4>
                <ul class="contact-data">

                    <li><i class="fa fa-question-circle"></i><?php echo $helpCenter; ?></li>
                    <li><i class="fa fa-globe"></i><a onclick="lang_open();"  href="javascript:void(0)"><?= $db_lng_mst[0]['vTitle'] ?></a></li>
                    <li><i class="fa fa-phone"></i><a style="direction: ltr;"><?= $SUPPORT_PHONE; ?></a></li>
                    <li><i class="fa fa-envelope"></i><a href="mailto:<?= $SUPPORT_MAIL; ?>"><?= $SUPPORT_MAIL; ?></a></li>
                    <li><i class="fa fa-map-marker address-icon"></i><address><?= $COMPANY_ADDRESS ?></address></li>
                </ul>
                <div class="download-links">
                    <?php if ($IPHONE_APP_LINK) { ?>
                        <a href="<?= $IPHONE_APP_LINK ?>" target="_blank"><img src="assets/img/ios-store.png" alt=""></a>
                    <?php } ?>

                    <?php if ($ANDROID_APP_LINK) { ?>    
                        <a href="<?= $ANDROID_APP_LINK ?>" target="_blank"><img src="assets/img/google-play_.png" alt=""></a>
<?php } ?>
                </div>
            </div>
            <div class="footer-column">
                <h4><?php echo $langage_lbl['LBL_SIGNUP']; ?></h4>
                <ul>
                    <li><a class="<?php

if (strpos($_SERVER['REQUEST_URI'], 'sign-up-rider') || strpos($_SERVER['REQUEST_URI'], 'sign-up?type=user') || strpos($_SERVER['REQUEST_URI'], 'sign-up?type=sender')) {

    echo 'active';
}

?>" href="<?= $link_user ?>" target='new'><?php echo $label_user_footer; ?></a></li>

                    <li><a class="<?php

                           if (strpos($_SERVER['REQUEST_URI'], 'sign-up?type=provider') || strpos($_SERVER['REQUEST_URI'], 'sign-up?type=driver')) {

                               echo 'active';
                           }

                           ?>" href="<?= $link_driver ?>" target='new'><?php echo $label_driver_footer; ?></a></li>

                    <!--<li><a class="<?php

                        if (strpos($_SERVER['REQUEST_URI'], 'sign-up?type=company')) {
                            echo 'active';
                        }

                        ?>" href="sign-up?type=company" target='new'><?php echo $langage_lbl['LBL_FOOTER_LINK_BECOME_COMPANY']; ?></a></li>-->

                    <? if (!empty($become_restaurant)) { ?><li><a class="<?php

                        if (strpos($_SERVER['REQUEST_URI'], 'sign-up-restaurant') || strpos($_SERVER['REQUEST_URI'], 'sign-up?type=store') || strpos($_SERVER['REQUEST_URI'], 'sign-up?type=restaurant')) {

                            echo 'active';
                        }

                        ?>" href="<?= $link_store ?>" target='new'><?php echo $become_restaurant; ?></a></li><? } ?>

                    <? if(strtoupper($ENABLE_CORPORATE_PROFILE)=='YES') { ?><li><a class="<?php
                    if (strpos($_SERVER['REQUEST_URI'], 'sign-up-organization')) {
                        echo 'active';
                    }
                    ?>" href="sign-up-organization" target='new'><?php echo $langage_lbl['LBL_FOOTER_LINK_BECOME_ORG']; ?></a></li> <? } ?>
                </ul>
            </div>
            <div class="footer-column">
                <h4><?php echo $langage_lbl['LBL_QUICK_LINKS']; ?></h4>

                <ul>
<?php
foreach ($displayPages as $key => $displayPage) {
    echo '<li>' . $displayPage . '</li>';
}
?>
                    <li><a href="contact-us" class="<?php
if (strpos($_SERVER['REQUEST_URI'], 'contact-us')) {
    echo 'active';
}
?>"><?php echo $langage_lbl['LBL_CONTACT_US_TXT']; ?> </a></li>  
                    <li><a href="faq" class="<?php
                        if (strpos($_SERVER['REQUEST_URI'], 'faq')) {
                            echo 'active';
                        }
?>"><?php echo $langage_lbl['LBL_FAQs']; ?></a></li>

                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-inner">
            <span>&copy; <?= $COPYRIGHT_TEXT ?></span>
            <ul class="quicklinks">
                <?php echo!empty($privacyPages[0]) ? '<li>' . $privacyPages[0] . '<li>' : ''; ?>
                <?php echo!empty($privacyPages[1]) ? '<li>' . $privacyPages[1] . '<li>' : ''; ?>
            </ul>
            <ul class="social-media-list">
                <?php if ((!empty($FB_LINK_FOOTER)) || (!empty($TWITTER_LINK_FOOTER)) || (!empty($LINKEDIN_LINK_FOOTER)) || (!empty($GOOGLE_LINK_FOOTER)) || (!empty($INSTAGRAM_LINK_FOOTER)) || ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == "Yes")) { ?>            
                    <?php if (!empty($FB_LINK_FOOTER)) { ?>
                        <li><a href="<?php echo $FB_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-facebook"></i></a></li> 
                    <?php
                    }
                    if (!empty($TWITTER_LINK_FOOTER)) {
                        ?>
                        <li><a href="<?php echo $TWITTER_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-twitter"></i></a></li>
    <?php
    }
    if (!empty($LINKEDIN_LINK_FOOTER)) {
        ?>
                        <li><a href="<?php echo $LINKEDIN_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-linkedin"></i></a></li>
    <?php
    }
    if (!empty($GOOGLE_LINK_FOOTER)) {
        ?>
                        <li><a href="<?php echo $GOOGLE_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-google"></i></a></li>
    <?php
    }
    if (!empty($INSTAGRAM_LINK_FOOTER)) {
        ?>
                        <li><a href="<?php echo $INSTAGRAM_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-instagram"></i></a></li>
    <?php
    }
    if ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == "Yes") {
        ?> 
                        <li><a href="#" data-target="#newsletter" data-toggle="modal" class="MainNavText" id="MainNavHelp" ><i class="fa fa-envelope"></i></a></li>
    <?php } ?>
<?php } ?>		
            </ul>
        </div>
    </div>
    <div class="gotop">
        <img src="assets/img/apptype/<?php echo $template; ?>/up-arrow.svg" alt="">
    </div>
</footer>
<? //include_once 'newsletter.php';  ?> 
<? include_once 'newsletter.php'; ?>


<script>
    function change_lang(lang) {
        document.location = 'common.php?lang=' + lang;
    }
    function lang_open() {
        $('html, body').animate({
            scrollTop: $(wrapper).offset().top
        }, 500);
        setTimeout(function () {
            $(".header-right .lang > a").trigger('click');
        }, 500);
    }
    var pwShown = 0;
    function showHidePassword(inputId) {
        if (pwShown == 0) {
            pwShown = 1;
            $("#" + inputId).attr("type", "text");
        } else {
            pwShown = 0;
            $("#" + inputId).attr("type", "password");
        }
    }
    $("#eye").hide(); //Hide Password Eye Icon By HJ On 25-01-2020 As per Dicsucc with CD Sir
    
    $(".otherservice").click(function() {
        var catname = $(this).attr("data-name");
        $.cookie('ServiceName', catname);
    });
</script>
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
<!-- <script type="text/javascript" src="assets/js/script.js"></script>   -->
<?php if ($lang != 'en') { ?>

    <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->

    <? include_once('otherlang_validation.php');?>

<?php } ?>
<? include_once 'include/livechat.php'; ?>
</div>
