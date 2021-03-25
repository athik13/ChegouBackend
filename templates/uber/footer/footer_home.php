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
//added by SP for pages orderby,active/inactive functionality start
$default_lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
$PagesData = $obj->MySQLSelect("SELECT iPageId,vPageTitle_$default_lang as pageTitle FROM `pages` WHERE iPageId IN (1,2,3,4,6,7,33) AND eStatus = 'Active' order by iOrderBy Asc");
$pageCount = 0;
foreach ($PagesData as $key => $value) {
    if($value['iPageId']==1) {
        $displayPages[$pageCount] = '<a href="about">'.$value['pageTitle'].'</a>';
    } else if($value['iPageId']==2) {
        $displayPages[$pageCount] = '<a href="help-center">'.$value['pageTitle'].'</a>';
    } else if($value['iPageId']==3) {
        $displayPages[$pageCount] = '<a href="legal">'.$value['pageTitle'].'</a>';
    } else if($value['iPageId']==4) {
        $displayPages[$pageCount] = '<a href="terms-condition">'.$value['pageTitle'].'</a>';
    } else if($value['iPageId']==6) {
        $displayPages[$pageCount] = '<a href="how-it-works">'.$value['pageTitle'].'</a>';
    } else if($value['iPageId']==7) {
        $displayPages[$pageCount] = '<a href="trust-safty-insurance">'.$value['pageTitle'].'</a>';
    } else if($value['iPageId']==33) {
        $displayPages[$pageCount] = '<a href="privacy-policy">'.$value['pageTitle'].'</a>';
    }
    $pageCount++;
}
//added by SP for pages orderby,active/inactive functionality end
?>
<div class="footer">
    <div class="footer-top-part">
        <div class="footer-inner">
            <div class="footer-box1">
                <?php if ($count_lang > 1) { ?>
                    <div class="lang" id="lang_open">
                        <b><a href="javascript:void(0);"><?= $langage_lbl['LBL_LANGUAGE_SELECT']; ?></a></b>
                    </div>
                    <div class="lang-all" id="lang_box">
                        <ul>
                            <?php
                            foreach ($db_lng_mst as $key => $value) {
                                $status_lang = "";
                                if ($_SESSION['sess_lang'] == $value['vCode']) {
                                    $status_lang = "active";
                                }
                                ?>
                                <li onclick="change_lang(this.id);" id="<?php echo $value['vCode']; ?>"><a href="javascript:void(0);" class="<?php echo $status_lang; ?>"><?php echo ucfirst(strtolower($value['vTitle'])); ?></a></li>
                                <?php
                            }
                            if ($count_lang > 4) {
                                ?>
                                   <!--  <li><a href="contact-us" ><?= $langage_lbl['LBL_LANG_NOT_FIND']; ?></a></li> -->
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
                <?php if ((!empty($FB_LINK_FOOTER)) || (!empty($TWITTER_LINK_FOOTER)) || (!empty($LINKEDIN_LINK_FOOTER)) || (!empty($GOOGLE_LINK_FOOTER)) || (!empty($INSTAGRAM_LINK_FOOTER)) || ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == "Yes")) { ?>
                    <span>
                        <?php if (!empty($FB_LINK_FOOTER)) { ?>
                            <a href="<?php echo $FB_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-facebook"></i></a> 
                            <?php
                        }
                        if (!empty($TWITTER_LINK_FOOTER)) {
                            ?>
                            <a href="<?php echo $TWITTER_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-twitter"></i></a>
                            <?php
                        }
                        if (!empty($LINKEDIN_LINK_FOOTER)) {
                            ?>
                            <a href="<?php echo $LINKEDIN_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-linkedin"></i></a>
                            <?php
                        }
                        if (!empty($GOOGLE_LINK_FOOTER)) {
                            ?>
                            <a href="<?php echo $GOOGLE_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-google"></i></a>
                            <?php
                        }
                        if (!empty($INSTAGRAM_LINK_FOOTER)) {
                            ?>
                            <a href="<?php echo $INSTAGRAM_LINK_FOOTER; ?>" target="_blank"><i class="fa fa-instagram"></i></a>
                            <?php
                        }
                        if ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == "Yes") {
                            ?> 
                            <a href="#" data-target="#newsletter" data-toggle="modal" class="MainNavText" id="MainNavHelp" ><i class="fa fa-envelope"></i></a>
                        <?php } ?>
                    </span>
                <?php } ?>
            </div>
            <div class="footer-box2">
                <ul>
                    <li><?php echo $displayPages[0]; ?></li>
                    <li><?php echo $displayPages[1]; ?></li>
                    <li><?php echo $displayPages[2]; ?></li>
                    <li><?php echo $displayPages[3]; ?></li>
                </ul>
                <ul>
                    <li><a href="contact-us"><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
                    <li><a href="faq"><?= $langage_lbl['LBL_FAQs']; ?></a></li>
                    <li><?php echo $displayPages[4]; ?></li>
                    <li><?php echo $displayPages[5]; ?></li>
                    <li><?php echo $displayPages[6]; ?></li>
                </ul>
<!--                <ul>
                    <li><a href="how-it-works"><?= $langage_lbl['LBL_HOW_IT_WORKS']; ?></a></li>
                    <li><a href="trust-safty-insurance"><?= $langage_lbl['LBL_SAFETY_AND_INSURANCE']; ?></a></li>
                    <li><a href="terms-condition"><?= $langage_lbl['LBL_FOOTER_TERMS_AND_CONDITION']; ?></a></li>
                    <li><a href="privacy-policy"><?= $langage_lbl['LBL_PRIVACY_POLICY_TEXT']; ?></a></li>
                </ul>
                <ul>
                    <li><a href="about"><?= $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
                    <li><a href="contact-us"><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
                    <li><a href="help-center"><?= $langage_lbl['LBL_FOOTER_HOME_HELP_CENTER']; ?></a></li>
                    <li><a href="faq"><?= $langage_lbl['LBL_FAQs']; ?></a></li>
                    <li><a href="legal"><?= $langage_lbl['LBL_LEGAL']; ?></a></li>
                </ul>-->
            </div>
            <div class="footer-box3"> 
                <span>
                    <a href="<?= $IPHONE_APP_LINK ?>" target="_blank"><img src="assets/img/app-stor-img.png" alt=""></a>
                </span> 
                <span>
                    <a href="<?= $ANDROID_APP_LINK ?>" target="_blank"><img src="assets/img/google-play-img.png" alt=""></a>
                </span> 
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div class="footer-bottom-part"> 
        <div class="footer-inner">
            <span>&copy; <?= $COPYRIGHT_TEXT ?></span>
        </div>
        <div style=" clear:both;"></div>
    </div>
</div>
<? include_once 'newsletter.php';?>
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
<script type="text/javascript">
    function change_lang(lang) {
        document.location = 'common.php?lang=' + lang;
    }
    $(document).ready(function () {
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
        });
        $('html').click(function (e) {
            $('#lang_box').hide();
        });
        $('#lang_open').click(function (e) {
            e.stopPropagation();
        });
    });
</script>
<? include_once 'include/livechat.php'; ?>