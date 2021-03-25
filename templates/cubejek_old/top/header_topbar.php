<?php
$eSystem = isset($_SESSION['sess_eSystem']) ? $_SESSION['sess_eSystem'] : '';

//echo $_SESSION['sess_user'];

if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'company') {
    $sql = "select * from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
}
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'organization') {
    $sql = "select * from organization where iOrganizationId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
}
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
    $sql = "select * from register_driver where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
}
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider') {
    $sql = "select * from register_user where iUserId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
}

$col_class = "";
if ($user != "") {
    $col_class = "top-inner-color";
}

if ($host_system == 'cubetaxiplus') {
    $logo = "logo.png";
} else if ($host_system == 'ufxforall') {
    $logo = "ufxforall-logo.png";
} else if ($host_system == 'uberridedelivery4') {
    $logo = "ride-delivery-logo.png";
} else if ($host_system == 'uberdelivery4') {
    $logo = "delivery-logo-only.png";
} else {
    $logo = "logo.png";
}

$sql = "select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
$db_lng_mst = $obj->MySQLSelect($sql);
$count_lang = count($db_lng_mst);
$langCodeArr = array();
for ($l = 0; $l < $count_lang; $l++) {
    $langCodeArr[$db_lng_mst[$l]['vCode']] = $db_lng_mst[$l]['vTitle'];
}
$currency = "SELECT iCurrencyId,eDefault,vName FROM currency WHERE eStatus='Active' ORDER BY iDispOrder ASC";
$db_cur_mst = $obj->MySQLSelect($currency);
$count_cur = count($db_cur_mst);
$languageText = "LANGUAGE";
if (isset($langCodeArr[$_SESSION['sess_lang']])) {
    $languageText = $langCodeArr[$_SESSION['sess_lang']];
}
?>

<style>
<?php if (isset($data[0]['vBannerBgImage']) && (!empty($data[0]['vBannerBgImage']))) { ?>
        .top-part-inner-home{ background: url('<?= $tconfig["tsite_img"] . "/home-new/" . $data[0]['vBannerBgImage']; ?>') no-repeat scroll top center; }
<?php } else { ?>
        .top-part-inner-home{ background:url('<?= $tconfig["tsite_img"] . '/home-new/banner.jpg' ?>') no-repeat scroll top center;}
<?php } ?>
</style>
<div id="home-page">

    <!-- top part -->
    <?php if (isset($script) && $script == 'Home') { ?>

        <div class="lang-part-top">
            <div class="lang-part-top-inner">
                <div class="phone-part"> 
                    <ul>
                        <?php if (!empty($SUPPORT_PHONE)) { ?>
                            <li><img src="assets/img/home-new/phone.png" alt="" /> <?php echo $SUPPORT_PHONE ?></li>
                        <?php } ?>
                        <?php if (!empty($SUPPORT_MAIL)) { ?>
                            <li><img src="assets/img/home-new/mgs.png" alt="" /><a href="mailto:<?= $SUPPORT_MAIL;?>">
                                    <?php echo $SUPPORT_MAIL ?> </a></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="lang-part">
                    <div class="special-offer-left">
                        <!-- <form action="" method="post" class="se-in">
                            <select name="timepass" class="custom-select" id="Languageids">
                        <?php foreach ($db_lng_mst as $key => $value) { ?>
                                                        <option id="<?php echo $value['vCode']; ?>" value="<?php echo $value['vCode']; ?>" <? if ($_SESSION['sess_lang'] == $value['vCode']) { ?> selected="selected" <? } ?> ><?php echo ucfirst(strtolower($value['vTitle'])); ?></option>
                        <?php } ?>
                                            </select>
                                        </form> -->
                        <!--                        <div class="currency-hold">
                                                    <div class="currency" id="currency_open">
                                                        <b><a href="javascript:void(0);"><?php echo $_SESSION['sess_currency']; ?></a></b>
                                                    </div>
                                                    <div class="currency-all" id="currency_box">
                                                        <ul>
                        <?php
                        foreach ($db_cur_mst as $currKey => $currValue) {
                            $status_curr = "";
                            if ($_SESSION['sess_currency'] == $currValue['vName']) {

                                $status_curr = "active";
                            }
                            ?>
                                                                                                <li onclick="change_curr(this.id);" id="<?php echo $currValue['vName']; ?>"><a href="javascript:void(0);" class="<?php echo $status_curr; ?>"><?php echo ucfirst(strtolower($currValue['vName'])); ?></a></li>
                        <?php } ?>
                                                            <li><a href="contact-us"><?= $langage_lbl['LBL_CURRENCY_NOT_FIND']; ?></a></li>
                                                        </ul>
                                                    </div>
                                                </div>-->
                        <div class="lang-hold">
                            <div class="lang" id="lang_open">
                                <b><a href="javascript:void(0);"><?php echo $languageText; ?></a></b>
                            </div>
                            <div class="lang-all" id="lang_box">
                                <ul>
                                    <?php
                                    $srNo = 1;
                                    foreach ($db_lng_mst as $key => $value) {
                                        $totlLang = count($db_lng_mst);
                                        $status_lang = "";
                                        if ($_SESSION['sess_lang'] == $value['vCode']) {
                                            $status_lang = "active";
                                        }
                                        $addStyle = "";
                                        if ($totlLang == $srNo && SITE_TYPE != "Demo") {
                                            $addStyle = 'style="width:14.6%;"';
                                        }
                                        $srNo++;
                                        ?>
                                        <li <?= $addStyle; ?> onclick="change_lang(this.id);" id="<?php echo $value['vCode']; ?>"><a href="javascript:void(0);" class="<?php echo $status_lang; ?>"><?php echo ucfirst(strtolower($value['vTitle'])); ?></a></li>
                                    <?php } 
                                    if (SITE_TYPE == "Demo") { ?>
                                        <li><a href="contact-us"><?= $langage_lbl['LBL_LANG_NOT_FIND']; ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="top-part <?= $col_class; ?>">
            <div class="top-part-inner-home">
                <?php $logoName = strstr($_SERVER['SCRIPT_NAME'], '/') && strstr($_SERVER['SCRIPT_NAME'], '/index.php') ? 'logo.png' : 'logo-inner.png'; ?>
                <div class="menu-part">
                    <div class="menu-part-inner">
                        <?php if ($user == "") { ?>
                            <div class="logo">
                                <a href="index.php">
                                    <img src="assets/img/<?php echo $logo; ?>" alt="">
                                </a>
                                <span class="top-logo-link" ><a href="about" class="<?= (isset($script) && $script == 'About Us') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a><a href="contact-us" class="<?= (isset($script) && $script == 'Contact Us') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></span>
                            </div>
                            <?php if (isset($_REQUEST['edit_lbl'])) { ?>
                                <div class="menu">
                                    <ul>
                                        <li><a href="help-center" class="<?= (isset($script) && $script == 'Help Center') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_HELP_TXT']; ?></a></li>
                                        <li><a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'], '/sign-in') || strstr($_SERVER['SCRIPT_NAME'], '/login-new') ? 'active' : '' ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT']; ?></a></li>
                                    </ul>
                            </div>
                        <?php } else { ?>
                            <div class="menu">
                                <ul>
                                    <li><a href="help-center" class="<?= (isset($script) && $script == 'Help Center') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_HELP_TXT']; ?></a></li>
                                    <li><a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'], '/sign-in') || strstr($_SERVER['SCRIPT_NAME'], '/login-new') ? 'active' : '' ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT']; ?></a></li>
                                </ul>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <?php
                        if ($user != "") {
                            if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') && ($db_user[0]['vImgName'] == 'NONE' || $db_user[0]['vImgName'] == '')) {
                                $img_url = "assets/img/profile-user-img.png";
                            } else {
                                if ($_SESSION['sess_user'] == 'company') {
                                    $img_path = $tconfig["tsite_upload_images_compnay"];
                                    $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
                                } else if ($_SESSION['sess_user'] == 'driver') {
                                    $img_path = $tconfig["tsite_upload_images_driver"];
                                    $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
                                } else {
                                    $img_path = $tconfig["tsite_upload_images_passenger"];
                                    $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImgName'];
                                }
                            }
                            ?>

                        <?php } ?>
                        <div class="logo">
                            <a href="index.php"><img src="assets/img/<?php echo $logo; ?>" alt=""></a>

                            <span class="top-logo-link" >
                                <?php if ($user == 'rider') { ?>
                                    <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
                                        <a href="profile-user" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a><a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a>
                                    <?php } else { ?>
                                        <a href="profile-rider" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a><a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a>
                                    <? } ?>
                                <? } else if ($user == 'organization') { ?>
                                    <a href="organization-profile" class="<?= (isset($script) && $script == 'Organization-Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a>
                                    <a href="Organization-Logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a>
                                <?php } else { ?>
                                    <a href="profile" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a>
                                    <a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a>
                                <? } ?>
                            </span>
                        </div>
                        <div class="top-link-login-new">
                            <div class="user-part-login">
                                <b><img src="<?= $img_url ?>" alt=""></b>
                                <div class="top-logo-link-hold">
                                    <div class="top-link-login">
                                        <label><img src="assets/img/arrow-menu.png" alt=""></label>
                                        <ul>
                                            <?php if ($user == 'driver') { ?>
                                                <li><a href="profile" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>
                                                <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
                                            <?php } else if ($user == 'company') { ?>

                                                <li><a href="profile" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>

                                                <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>

                                            <?php } else if ($user == 'rider') {
                                                ?>
                                                <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
                                                    <li><a href="profile-user" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>
                                                <?php } else { ?>
                                                    <li><a href="profile-rider" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>
                                                <? } ?>
                                                <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php } ?>
                    <div style="clear:both;"></div>
                </div>
            </div>

            <div class="banner-part">
                <div class="banner-part-inner">
                    <div class="banner-part-left">
                        <?php if (isset($data[0]['vBannerLeftImg']) && (!empty($data[0]['vBannerLeftImg']))) { ?>
                            <img src="<?= $tconfig["tsite_img"] . "/home-new/" . $data[0]['vBannerLeftImg']; ?>" alt="" />
                        <?php } else { ?>
                            <img src="assets/img/home-new/mobile-app.png" alt="" />
                        <?php } ?>   
                    </div>
                    <div class="banner-part-right">
                        <h1><?php echo $data[0]['vBannerRightTitle'] ?><br/><?php echo $data[0]['vBannerRightTitleSmall'] ?></h1>
                        <p><?php echo $data[0]['tBannerRightContent'] ?></p>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <img src="assets/img/home-new/banner-bottom.png" alt="" class="banner-bottom"/>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div style="clear:both"></div>
<?php } else { ?>
    <!-- top part -->
    <div class="top-part <?= $col_class; ?>" id="top-part">
        <div class="lang-part-top">
            <div class="lang-part-top-inner">
                <div class="phone-part"> 
                    <ul>
                        <?php if (!empty($SUPPORT_PHONE)) { ?>
                            <li><img src="assets/img/home-new/phone.png" alt="" /> <?php echo $SUPPORT_PHONE ?></li>
                        <?php } ?>
                        <?php if (!empty($SUPPORT_MAIL)) { ?>
                            <li><img src="assets/img/home-new/mgs.png" alt="" /><a href="#">
                                    <?php echo $SUPPORT_MAIL ?> </a></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="lang-part">
                    <div class="special-offer-left">
                        <!-- <form action="" method="post" class="se-in">
                            <select name="timepass" class="custom-select" id="Languageids">
                        <?php foreach ($db_lng_mst as $key => $value) { ?>
                                                        <option id="<?php echo $value['vCode']; ?>" value="<?php echo $value['vCode']; ?>" <? if ($_SESSION['sess_lang'] == $value['vCode']) { ?> selected="selected" <? } ?> ><?php echo ucfirst(strtolower($value['vTitle'])); ?></option>
                        <?php } ?>
                            </select>
                        </form> -->
                        <div class="lang-hold">
                            <div class="lang" id="lang_open">
                                <b><a href="javascript:void(0);">LINGUAGEM</a></b>
                            </div>
                            <div class="lang-all" id="lang_box">
                                <ul>
                                    <?php
                                    $srNo = 1;
                                    foreach ($db_lng_mst as $key => $value) {
                                        $totlLang = count($db_lng_mst);
                                        $status_lang = "";
                                        if ($_SESSION['sess_lang'] == $value['vCode']) {
                                            $status_lang = "active";
                                        }
                                        $addStyle = "";
                                        if ($totlLang == $srNo && SITE_TYPE != "Demo") {
                                            $addStyle = 'style="width:14.6%;"';
                                        }
                                        $srNo++;
                                        ?>
                                        <li <?= $addStyle; ?> onclick="change_lang(this.id);" id="<?php echo $value['vCode']; ?>"><a href="javascript:void(0);" class="<?php echo $status_lang; ?>"><?php echo ucfirst(strtolower($value['vTitle'])); ?></a></li>
                                    <?php } if (SITE_TYPE == "Demo") { ?>
                                        <li style="width:38%;"><a href="contact-us"><?= $langage_lbl['LBL_LANG_NOT_FIND']; ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="top-part-inner">
            <?php $logoName = strstr($_SERVER['SCRIPT_NAME'], '/') && strstr($_SERVER['SCRIPT_NAME'], '/index.php') ? 'logo.png' : 'logo-inner.png'; ?>
            <div class="menu-part">
                <div class="menu-part-inner">
                    <?php if ($user == "") { ?>
                        <div class="logo">
                            <a href="index.php">
                                <img src="assets/img/<?php echo $logo; ?>" alt="">
                            </a>
                            <span class="top-logo-link" ><a href="about" class="<?= (isset($script) && $script == 'About Us') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a><a href="contact-us" class="<?= (isset($script) && $script == 'Contact Us') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></span>
                        </div>
                        <?php if (isset($_REQUEST['edit_lbl'])) { ?>
                            <div class="menu">
                                <ul>
                                    <li><a href="help-center" class="<?= (isset($script) && $script == 'Help Center') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_HELP_TXT']; ?></a></li>
                                    <li><a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'], '/sign-in') || strstr($_SERVER['SCRIPT_NAME'], '/login-new') ? 'active' : '' ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT']; ?></a></li>
                                </ul>
                            </div>
                        <?php } else { ?>
                            <div class="menu">
                                <ul>
                                    <li><a href="help-center" class="<?= (isset($script) && $script == 'Help Center') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_HELP_TXT']; ?></a></li>
                                    <li><a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'], '/sign-in') || strstr($_SERVER['SCRIPT_NAME'], '/login-new') ? 'active' : '' ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT']; ?></a></li>
                                </ul>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <?php
                        if ($user != "") {
                            if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') && ($db_user[0]['vImgName'] == 'NONE' || $db_user[0]['vImgName'] == '')) {
                                $img_url = "assets/img/profile-user-img.png";
                            } else {


                                if ($_SESSION['sess_user'] == 'company') {
                                    $img_path = $tconfig["tsite_upload_images_compnay"];
                                    $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
                                } else if ($_SESSION['sess_user'] == 'organization') {

                                    $img_path = $tconfig["tsite_upload_images_organization"];
                                    $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
                                } else if ($_SESSION['sess_user'] == 'driver') {
                                    $img_path = $tconfig["tsite_upload_images_driver"];
                                    $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
                                } else {
                                    $img_path = $tconfig["tsite_upload_images_passenger"];
                                    $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImgName'];
                                }
                            }
                            ?>

                        <?php } ?>
                        <div class="logo">
                            <a href="index.php"><img src="assets/img/<?php echo $logo; ?>" alt=""></a>

                            <span class="top-logo-link" >
                                <?php if ($user == 'rider') { ?>

                                    <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
                                        <a href="profile-user" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a><a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a>
                                    <?php } else { ?>
                                        <a href="profile-rider" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a><a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a>
                                    <? } ?>

                                <? } else if ($user == 'organization') { ?>

                                    <a href="organization-profile" class="<?= (isset($script) && $script == 'Organization-Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a>
                                    <a href="Organization-Logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a>

                                <?php } else { ?>

                                    <?php if ($eSystem == "DeliverAll") { ?>	

                                        <a href="dashboard" class="<?= (isset($script) && $script == 'Dashboard') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a>

                                    <?php } else { ?>

                                        <a href="profile" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a>

                                    <?php } ?>

                                    <a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a>
                                <? } ?>
                            </span>
                        </div>

                        <div class="top-link-login-new">
                            <div class="user-part-login">


                                <?php if ($user != 'organization') { ?>
                                    <b><img src="<?= $img_url ?>" alt=""></b>
                                <?php } ?>

                                <div class="top-logo-link-hold">
                                    <div class="top-link-login">
                                        <label><img src="assets/img/arrow-menu.png" alt=""></label>
                                        <ul>
                                            <?php if ($user == 'driver') { ?>
                                                <li><a href="profile" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>
                                                <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
                                            <?php } else if ($user == 'company') { ?>

                                                <?php if ($eSystem == "DeliverAll") { ?>

                                                    <li><a href="dashboard" class="<?= (isset($script) && $script == 'Dashboard') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>


                                                <?php } else { ?>

                                                    <li><a href="profile" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>

                                                <?php } ?>

                                                <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>

                                            <?php } else if ($user == 'organization') { ?>

                                                <li><a href="organization-profile" class="<?= (isset($script) && $script == 'Organization-Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>

                                                <li><a href="Organization-Logout"><i class="fa fa-power-off" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>

                                            <?php } else if ($user == 'rider') { ?>

                                                <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
                                                    <li><a href="profile-user" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>
                                                <?php } else { ?>
                                                    <li><a href="profile-rider" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><i class="fa fa-user" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li>
                                                <? } ?>
                                                <li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>	
<?php } ?>
<div style="clear:both;"></div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".custom-select").each(function () {
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'></em>");
        });
        $(".custom-select").change(function () {
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        }).trigger('change');
    })
    jQuery(function () {
        jQuery("#Languageids").change(function () {
            var lang = $(this).val();
            location.href = 'common.php?lang=' + lang;
        });
    });
    function change_curr(currency) {
        var request = $.ajax({
            type: "POST",
            url: 'ajax_fpass_action.php',
            data: {
                action: 'changecurrency',
                q: currency,
            },
            dataType: 'json',
            beforeSend: function ()
            {
                //alert(id);
            },
            success: function (data)
            {
                location.reload();
            }
        });
        request.fail(function (jqXHR, textStatus) {
            alert("Request failed: " + textStatus);
        });
    }
</script>