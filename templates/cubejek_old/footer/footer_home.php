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
<div class="footer">
    <div class="footer-inner">
        <div class="footer-top">
            <div class="footer-col">
                <h4><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></h4>
                <p><?= $COMPANY_ADDRESS ?></p>
                <span>
                    <p><b><?= $langage_lbl['LBL_PHONE_FRONT_FOOTER']; ?> :</b>+<?= $SUPPORT_PHONE; ?></p>
                    <p><b><?= $langage_lbl['LBL_EMAIL_FRONT_FOOTER']; ?> :</b><a href="mailto:<?= $SUPPORT_MAIL;?>"><?= $SUPPORT_MAIL; ?></a></p>
                </span>
            </div>
            <div class="footer-col">
                <h4><?= $langage_lbl['LBL_COMPANY_FOOTER']; ?></h4>
                <ul>
                    <li><a href="contact-us"><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
                    <li><a href="about"><?= $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
                    <li><a href="help-center"><?= $langage_lbl['LBL_FOOTER_HOME_HELP_CENTER']; ?></a></li>
                    <li><a href="legal"><?= $langage_lbl['LBL_LEGAL']; ?></a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4><?= $langage_lbl['LBL_OTHER_PAGES_FOOTER']; ?></h4>
                <ul>
                    <li><a href="faq"><?= $langage_lbl['LBL_FAQs']; ?></a></li>
                    <li><a href="how-it-works"><?= $langage_lbl['LBL_HOW_IT_WORKS']; ?></a></li>
                    <li><a href="privacy-policy"><?= $langage_lbl['LBL_PRIVACY_POLICY_TEXT']; ?></a></li>
                    <li><a href="terms-condition"><?= $langage_lbl['LBL_FOOTER_TERMS_AND_CONDITION']; ?></a></li>
                </ul>
            </div>
            <div class="footer-col">
                <!--<h4>Subscribe Now</h4>
                <span><input name="" type="text" placeholder="Enter your E-mail Address" /><a href="#">Subscribe</a></span> -->
                <h4><?= $langage_lbl['LBL_FOLLOW_WITH_US_TXT']; ?></h4>
                <div class="social">
                    <?php if ((!empty($FB_LINK_FOOTER)) || (!empty($TWITTER_LINK_FOOTER)) || (!empty($LINKEDIN_LINK_FOOTER)) || (!empty($INSTAGRAM_LINK_FOOTER))) { ?>
                        <?php if (!empty($FB_LINK_FOOTER)) { ?>
                            <a href="<?php echo $FB_LINK_FOOTER; ?>" target="_blank" rel="nofollow"><img onmouseout="this.src = 'assets/img/home-new/fb.jpg'" onmouseover="this.src = 'assets/img/home-new/fb-hover.jpg'" src="assets/img/home-new/fb.jpg" alt=""></a>
                            <?php
                        }
                        if (!empty($TWITTER_LINK_FOOTER)) {
                            ?>
                            <a href="<?php echo $TWITTER_LINK_FOOTER; ?>" target="_blank" rel="nofollow"><img onmouseout="this.src = 'assets/img/home-new/twitter.jpg'" onmouseover="this.src = 'assets/img/home-new/twitter-hover.jpg'" src="assets/img/home-new/twitter.jpg" alt=""></a>
                            <?php
                        }
                        if (!empty($LINKEDIN_LINK_FOOTER)) {
                            ?>
                            <a href="<?php echo $LINKEDIN_LINK_FOOTER; ?>" target="_blank" rel="nofollow"><img onmouseout="this.src = 'assets/img/home-new/linkedin.jpg'" onmouseover="this.src = 'assets/img/home-new/linkedin-hover.jpg'" src="assets/img/home-new/linkedin.jpg" alt=""></a>
                            <?php
                        }
                        if (!empty($INSTAGRAM_LINK_FOOTER)) {
                            ?>
                            <a href="<?php echo $INSTAGRAM_LINK_FOOTER; ?>" target="_blank" rel="nofollow"><img onmouseout="this.src = 'assets/img/home-new/instagram.jpg'" onmouseover="this.src = 'assets/img/home-new/instagram-hover.jpg'" src="assets/img/home-new/instagram.jpg" alt=""></a>
                            <?php
                        }
                        if (!empty($GOOGLE_LINK_FOOTER)) {
                            ?>    
                            <a href="<?php echo $GOOGLE_LINK_FOOTER; ?>" target="_blank" rel="nofollow"><img onmouseout="this.src = 'assets/img/home-new/google.jpg'" onmouseover="this.src = 'assets/img/home-new/google-hover.jpg'" src="assets/img/home-new/google.jpg" alt=""></a>
                            <?php
                        }
                        if ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == "Yes") {
                            ?>   <a href="#" data-target="#newsletter" data-toggle="modal" class="MainNavText" id="MainNavHelp" ><img onmouseout="this.src = 'assets/img/home-new/subscribe.jpg'" onmouseover="this.src = 'assets/img/home-new/subscribe-hover.jpg'" src="assets/img/home-new/subscribe.jpg" alt=""></a>
                            <?php } ?>
                        <?php } ?>

                </div>

            </div>
        </div>
        <div class="footer-bottom-part">
            <p><span>&copy; <?= $COPYRIGHT_TEXT ?></span></p>
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
<? include_once 'newsletter.php';?>
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
<script>
    function change_lang(lang) {
        document.location = 'common.php?lang=' + lang;
    }
</script>
<script type="text/javascript">
    $(document).ready(function () {
        var errormessage;
        $(".custom-select-new1").each(function () {
            var selectedOption = $(this).find(":selected").text();
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'>" + selectedOption + "</em>");
        });
        $(".custom-select-new1").change(function () {
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        });
        $("#lang_box").hide();
        $("#lang_open").click(function () {
            $("#lang_box").slideToggle();
            $("#lang_box").toggleClass('active');
            if ($('.currency-all.active').length > 0) {
                $('#currency_box').slideUp();
                $('#currency_box').removeClass('acive');
            }
        });
        $('html').click(function (e) {
            $('#lang_box').slideUp();
            $('#lang_box').removeClass('acive');
        });
        $('#lang_open').click(function (e) {
            e.stopPropagation();
        });
        $("#currency_box").hide();
        $("#currency_open").click(function () {
            $("#currency_box").slideToggle();
            $("#currency_box").toggleClass('active');
            $('#lang_box').slideUp();
            $('#lang_box').removeClass('acive');
        });
        $('html').click(function (e) {
            $('#currency_box').slideUp();
            $('#currency_box').removeClass('acive');
        });
        $('#currency_open').click(function (e) {
            e.stopPropagation();
        });
    });
</script>				
<? include_once 'include/livechat.php'; ?>
