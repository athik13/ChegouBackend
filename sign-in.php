<?php
include_once("common.php");
include_once('../include/config.php');

//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-sign-in.php");
        exit;
}
	
$generalobj->go_to_home();
$action = isset($_GET['action']) ? $_GET['action'] : '';
$script = "Login Main";
$meta_arr = $generalobj->getsettingSeo(1);
$lbl_cmp = "LBL_COMPANY_SIGNIN";
if ($APP_TYPE == "Ride-Delivery-UberX" && strtoupper(DELIVERALL) == "YES" && strtoupper(ONLYDELIVERALL) == "NO") {
    $lbl_cmp = "LBL_COMPANY";
} else if (($APP_TYPE == "Ride-Delivery-UberX" && strtoupper(DELIVERALL) == "NO") || $APP_TYPE == "Ride" || $APP_TYPE == "Delivery" || $APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery") {
    $lbl_cmp = "LBL_COMPANY_SIGNIN";
} else if ((strtoupper(ONLYDELIVERALL) == "YES" && !empty($service_categories_ids_arr) && count($service_categories_ids_arr) > 1) || (strtoupper(ONLYDELIVERALL) == "YES" && !empty($service_categories_ids_arr) && (count($service_categories_ids_arr) == 1 && !in_array(1, $service_categories_ids_arr)))) {
    $lbl_cmp = "LBL_STORE";
} else if (strtoupper(ONLYDELIVERALL) == "YES" && !empty($service_categories_ids_arr) && (count($service_categories_ids_arr) == 1 && in_array(1, $service_categories_ids_arr))) {
    $lbl_cmp = "LBL_RESTAURANT";
}

$IS_CORPORATE_PROFILE_ENABLED = isCorporateProfileEnable();

$lbl_sign_in_note3 = "LBL_SIGN_NOTE3";

if ($APP_TYPE == "Ride") {
    $lbl_sign_in_note3 = "LBL_SIGN_NOTE_RIDE";
} else if ($APP_TYPE == "Delivery") {
    $lbl_sign_in_note3 = "LBL_SIGN_NOTE_DELIVERY";
} else if ($APP_TYPE == "Ride-Delivery") {
    $lbl_sign_in_note3 = "LBL_SIGN_NOTE_RIDE_DELIVERY";
} else if ($APP_TYPE == "UberX") {
    $lbl_sign_in_note3 = "LBL_SIGN_NOTE_UBERX";
} else if ($APP_TYPE == "Ride-Delivery-UberX" && strtoupper(DELIVERALL) == "NO") {
    $lbl_sign_in_note3 = "LBL_SIGN_NOTE_RDU";
} else if ((strtoupper(ONLYDELIVERALL) == "YES" && !empty($service_categories_ids_arr) && count($service_categories_ids_arr) > 1) || (strtoupper(ONLYDELIVERALL) == "YES" && !empty($service_categories_ids_arr) && (count($service_categories_ids_arr) == 1 && !in_array(1, $service_categories_ids_arr)))) {
    $lbl_sign_in_note3 = "LBL_SIGN_NOTE_STORE";
} else if (strtoupper(ONLYDELIVERALL) == "YES" && !empty($service_categories_ids_arr) && (count($service_categories_ids_arr) == 1 && in_array(1, $service_categories_ids_arr))) {
    $lbl_sign_in_note3 = "LBL_SIGN_NOTE_RESTAURANT";
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <!--<title><?= $SITE_NAME ?> | Login Page</title>-->
        <title><?php echo $meta_arr['meta_title']; ?></title>
        <!-- Default Top Script and css -->
<?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
        <script>
            $(document).ready(function () {
                /**************this script for set equal height of element******************/
                $.fn.equalHeight = function () {
                    var maxHeight = 0;
                    return this.each(function (index, box) {
                        var boxHeight = $(box).height();
                        maxHeight = Math.max(maxHeight, boxHeight);
                    }).height(maxHeight);
                };
                function EQUAL_HEIGHT() {
                    $('[class*="sign-in"] p').equalHeight();
                    $(window).resize(function () {
                        $('[class*="sign-in"] p').css('height', 'auto');
                        $('[class*="sign-in"] p').equalHeight();
                    });
                }
                $(window).load(function () {
                    EQUAL_HEIGHT();
                })

                $(document).on('click', 'ul.TABSWITCH li', function () {
                    $('ul.TABSWITCH li').removeClass('active');
                    $(this).addClass('active');
                    var ID = $(this).attr('data-id');
                    $('.login-holder-main [class*="sign-in"]').removeClass('active');
                    $(document).find('#' + ID).addClass('active')
                })
            })

        </script>
    </head>
    <body>
        <div id="main-uber-page">
            <!-- Left Menu -->
<?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- home page -->

            <!-- Top Menu -->
<?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant">
                <div class="page-contant-inner">
                    <h2 class="header-page"><?= $langage_lbl['LBL_SIGN_IN_SIGN_IN_TXT']; ?></h2>
                    <!-- login in page -->
                    <div class="sign-in">
<?php
if ($tab == "true") {
    $tab_manage = '';
    if ($IS_CORPORATE_PROFILE_ENABLED == false) {
        $tab_manage = 'three_tabs';
    }
    ?>
                            <ul class="TABSWITCH <?= $tab_manage; ?>">
                                <li class="active" data-id="RIDER"><?= $langage_lbl['LBL_SIGNIN_RIDER']; ?></li>
                                <li data-id="DRIVER"><?= $langage_lbl['LBL_SIGNIN_DRIVER']; ?></li>
                                <li data-id="COMPANY"><?= $langage_lbl[$lbl_cmp]; ?></li>
                                <?php if ($IS_CORPORATE_PROFILE_ENABLED) { ?>
                                    <li data-id="ORG"><?= $langage_lbl['LBL_ORGANIZATION']; ?></li>
                                <?php } ?>
                            </ul>
                        <?php } ?>     
                        <?php $msg1 = $_REQUEST['msg1']; ?>

                        <? if (!empty($msg1)) { ?>
                        <div class="alert alert-danger alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            Invalid Tokens
                        </div><br/>
                        <? } ?>
                        <div class="login-holder-main">
                            <div class="sign-in-driver" id="COMPANY">
                                <h3><?= $langage_lbl[$lbl_cmp]; ?></h3>
                                <p><?= $langage_lbl[$lbl_sign_in_note3]; ?></p>
                                <span><a href="<?= $cjCompanyLogin; ?>"><?= $langage_lbl['LBL_SIGNIN_COMPNY_SIGNIN']; ?><img src="assets/img/arrow-white-right.png" alt="" /></a></span>
                            </div>
                            <div class="sign-in-driver" id="DRIVER">
                                <h3><?= $langage_lbl['LBL_SIGNIN_DRIVER']; ?></h3>
                                <p><?= $langage_lbl['LBL_SIGN_NOTE1']; ?></p>
                                <?php if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                    <span><a href="<?= $cjProviderLogin; ?>"><?= $langage_lbl['LBL_SIGNIN_DRIVERSIGNIN']; ?><img src="assets/img/arrow-white-right.png" alt="" /></a></span>
                                <?php } else { ?>
                                    <span><a href="<?= $cjDriverLogin; ?>"><?= $langage_lbl['LBL_SIGNIN_DRIVERSIGNIN']; ?><img src="assets/img/arrow-white-right.png" alt="" /></a></span>
                                <?php } ?>
                            </div>
                            <div class="sign-in-rider active" id="RIDER">
                                <h3><?= $langage_lbl['LBL_SIGNIN_RIDER']; ?></h3>
                                <p><?= $langage_lbl['LBL_SIGN_NOTE2']; ?></p>
                                <?php if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                    <span><a href="<?= $cjUserLogin; ?>"><?= $langage_lbl['LBL_SIGNIN_RIDER_SIGNIN']; ?><img src="assets/img/arrow-white-right.png" alt="" /></a></span>
                                <?php } else { ?>
                                    <span><a href="<?= $cjRiderLogin; ?>"><?= $langage_lbl['LBL_SIGNIN_RIDER_SIGNIN']; ?><img src="assets/img/arrow-white-right.png" alt="" /></a></span>
                                <?php } ?>
                            </div>
                            <?php if ($IS_CORPORATE_PROFILE_ENABLED) { ?>
                                <div class="sign-in-rider" id="ORG">
                                    <h3><?= $langage_lbl['LBL_ORGANIZATION']; ?></h3>
                                    <p><?= $langage_lbl['LBL_SIGN_NOTE4']; ?></p>

                                    <span><a href="<?= $cjOrganizationLogin; ?>"><?= $langage_lbl['LBL_ORGANIZATION_SIGNIN']; ?><img src="assets/img/arrow-white-right.png" alt="" /></a></span>
                                </div>
                            <?php } ?>  
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <!-- home page end-->
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <!-- footer part end -->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <!-- End: Footer Script -->
        <!-- Powered by V3Cube.com -->
    </body>
</html>
