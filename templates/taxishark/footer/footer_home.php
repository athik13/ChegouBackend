<?php
$sql = "select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
$db_lng_mst = $obj->MySQLSelect($sql);
$count_lang = count($db_lng_mst);

if (isset($_POST['vNamenewsletter'])) {
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
}

?>
<footer>
    <div class="footer-top">
        <div class="footer-inner">
            <div class="footer-column">
			<h4><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></h4>
                <address><?= $COMPANY_ADDRESS ?></address>
                    <ul class="contact-data">
                        <li><label><?= $langage_lbl['LBL_PHONE_FRONT_FOOTER']; ?> : </label><a href="tel:+<?= $SUPPORT_PHONE; ?>" style="direction: ltr;"><?= $SUPPORT_PHONE; ?></a></li>
                        <li><label><?= $langage_lbl['LBL_EMAIL_FRONT_FOOTER']; ?> : </label><a href="mailto:<?= $SUPPORT_MAIL; ?>"><?= $SUPPORT_MAIL; ?></a></li>
                    </ul>
                </span>
            </div>
            <div class="footer-column">
                <ul>
                     <li><a href="how-it-works"><?= $langage_lbl['LBL_HOW_IT_WORKS']; ?></a></li>
                    <li><a href="trust-safty-insurance"><?= $langage_lbl['LBL_SAFETY_AND_INSURANCE']; ?></a></li>
                    <li><a href="terms-condition"><?= $langage_lbl['LBL_FOOTER_TERMS_AND_CONDITION']; ?></a></li>
                    
                </ul>
            </div>
            <div class="footer-column">
                <ul>
					<li><a href="about"><?= $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
                    <li><a href="contact-us"><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
                    <li><a href="help-center"><?= $langage_lbl['LBL_FOOTER_HOME_HELP_CENTER']; ?></a></li>
                    
					
                </ul>
            </div>
            <div class="footer-column">
                <ul>
                    <li><a href="privacy-policy"><?= $langage_lbl['LBL_PRIVACY_POLICY_TEXT']; ?></a></li>
                    <li><a href="faq"><?= $langage_lbl['LBL_FAQs']; ?></a></li>
                    <li><a href="legal"><?= $langage_lbl['LBL_LEGAL']; ?></a></li>
                </ul>
            </div>
            <div class="footer-column">
					<?php if ((!empty($FB_LINK_FOOTER)) || (!empty($TWITTER_LINK_FOOTER)) || (!empty($LINKEDIN_LINK_FOOTER)) || (!empty($GOOGLE_LINK_FOOTER)) || (!empty($INSTAGRAM_LINK_FOOTER)) || ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == "Yes")) { ?>
                     <ul class="social-media-list">
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
                            <li><a href="<?php echo $GOOGLE_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-youtube-play"></i></a></li>
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
                    </ul>
                <?php } ?>
                <div class="download-links">
                    <a href="<?= $IPHONE_APP_LINK ?>" target="_blank"><img src="assets/img/ios-store.png" alt=""></a>
                    <a href="<?= $ANDROID_APP_LINK ?>" target="_blank"><img src="assets/img/google-play_.png" alt=""></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-inner">
            &copy; <?= $COPYRIGHT_TEXT ?>
        </div>
    </div>
</footer>
<? include_once 'newsletter.php';?>
<script>
    function change_lang(lang) {
        document.location = 'common.php?lang=' + lang;
    }
</script>
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>

<? include_once 'include/livechat.php'; ?>