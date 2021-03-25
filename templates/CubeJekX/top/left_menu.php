<?php
$curr_url = basename($_SERVER['PHP_SELF']);
//include 'common.php' ;
$user = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';
$eSystem = isset($_SESSION["sess_eSystem"]) ? $_SESSION["sess_eSystem"] : '';
$user = $sessionUer = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
} else if (isset($_SESSION['MANUAL_ORDER_USER'])) {
    $fromOrder = $_SESSION['MANUAL_ORDER_USER'];
}
$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_" . strtoupper($fromOrder);
if ($user == 'driver') {
    $sql = "select * from register_driver where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_data = $obj->sql_query($sql);
    if ($db_data[0]['vImage'] == "NONE" || $db_data[0]['vImage'] == '') {
        $db_data[0]['img'] = "";
    } else {

        $db_data[0]['img'] = $tconfig["tsite_upload_images_driver"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
    }

    $comp_sql = "select * from company where iCompanyId = '" . $db_data[0]['iCompanyId'] . "'";
    $compdb_data = $obj->sql_query($comp_sql);
}
if ($user == 'company') {
    $sql = "select * from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_data = $obj->sql_query($sql);

    if ($db_data[0]['vImage'] == "NONE" || $db_data[0]['vImage'] == '') {
        $db_data[0]['img'] = "";
    } else {
        $db_data[0]['img'] = $tconfig["tsite_upload_images_compnay"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
    }
}
//Added By HJ On 07-01-2019 For Display Organization Menu Start
if ($user == 'organization') {
    
}
//Added By HJ On 07-01-2019 For Display Organization Menu End
if ($user == 'rider') {
    $sql = "select * from register_user where iUserId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_data = $obj->sql_query($sql);
    if ($db_data[0]['vImgName'] != "NONE" && $db_data[0]['vImgName'] != '') {
        $db_data[0]['img'] = $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImgName'];
    } else {
        $db_data[0]['img'] = "";
    }
}  //echo "<pre>";print_r($db_data);echo "</pre>";

if ($host_system == 'cubetaxishark' || $host_system == 'cubetaxi5plus') {
    $logo = "menu-logo-cubetaxi.png";
} else if ($host_system == 'cubedelivery') {
    $logo = "menu-logo_delivery.png";
} else {
    $logo = "menu-logo.png";
}
$logopath = '';
if ($host_system == "cubegrocery") {
    $logopath = 'grocery/';
} else if ($host_system == "cubepharmacy") {
    $logopath = 'pharmacy/';
} else if ($host_system == "cubeherbs") {
    $logopath = 'herbs/';
}

$RideDeliveryIconArrStatus = $generalobj->CheckRideDeliveryFeatureDisableWeb();
$RideDeliveryBothFeatureDisable = $RideDeliveryIconArrStatus['RideDeliveryBothFeatureDisable'];
$manualOrderMenu = $langage_lbl['LBL_MANUAL_STORE_ORDER_TXT'];
if (isset($_SESSION[$orderServiceNameSession])) {
    $manualOrderMenu = $_SESSION[$orderServiceNameSession];
}
$manualOrderMenu = $langage_lbl['LBL_MANUAL_STORE_ORDER_TXT'];
$DONATION = $DRIVER_DESTINATION = $FAVOURITE_DRIVER = $FAVOURITE_STORE = $DRIVER_SUBSCRIPTION = $GOJEK_GOPAY = $MULTI_STOPOVER_POINTS = $MANUAL_STORE_ORDER_WEBSITE = $MANUAL_STORE_ORDER_STORE_PANEL = $MANUAL_STORE_ORDER_ADMIN_PANEL = "No"; // Added By HJ On 12-07-2019
$setupData = $obj->sql_query("select lAddOnConfiguration from setup_info");
if (isset($setupData[0]['lAddOnConfiguration'])) {
    $addOnData = json_decode($setupData[0]['lAddOnConfiguration'], true);
    foreach ($addOnData as $addOnKey => $addOnVal) {
        $$addOnKey = $addOnVal;
    }
}
$siteUrl = $tconfig['tsite_url'];
$ufxenableleft = 'No';
if ($generalobj->CheckUfxServiceAvailable() == 'Yes') {
    $ufxenableleft = 'Yes';
}
//Added By HJ On 16-05-2020 For Show/Hide Provider-Job Menu Based On Store Driver Start
$hideProviderJob = 1; // Hide Provider-job menu
if(isset($_SESSION['sess_iCompanyId']) && $_SESSION['sess_iCompanyId'] > 0){
    $driverCompanyId = $_SESSION['sess_iCompanyId'];
    $companyData = $obj->MySQLSelect("SELECT iServiceId FROM company WHERE iCompanyId='".$driverCompanyId."'");
    if(isset($companyData[0]['iServiceId']) && $companyData[0]['iServiceId'] > 0){
        $hideProviderJob = 0;
    }
    ///echo "<pre>";print_r($hideProviderJob);die;
}
//Added By HJ On 16-05-2020 For Show/Hide Provider-Job Menu Based On Store Driver End
$isStoreDriver = isStoreDriverAvailable(); // Added By HJ On 20-03-2020 For Check Manage Driver By Store Enable

$file_path = $tconfig["tpanel_path"] . "assets/img/apptype/$template/logo-side-menu.png";
if (file_exists($file_path)) {
    $logo = $siteUrl . "assets/img/apptype/$template/logo-side-menu.png";
} else {
    $logo = $siteUrl . "assets/img/apptype/$template/logo.png";
}
$cubeDeliverallOnly = isDeliverAllOnlySystem();
$onlyDeliverallModule = strtoupper(ONLYDELIVERALL);
$deliverallModule = strtoupper(DELIVERALL);
if($cubeDeliverallOnly > 0){
    $onlyDeliverallModule = "YES";
}
if ($_SESSION['sess_user'] != "") {
                    $onclickLogoURL = "profile";
                 }else{
                     $onclickLogoURL = "index.php";
                 }
?>
<ul class="user-menu">
    <li class="logo"><a href="<?=$onclickLogoURL;?>"><img src="<?php echo $logo; ?>" alt=""></a></li>
    <?php if ($user == "") { ?>
                <!--<li class="<?= (isset($script) && $script == 'How It Works') ? 'active' : ''; ?>"><a href="how-it-works"><?= $langage_lbl['LBL_HOW_IT_WORKS']; ?></a></li>-->
                <!--<li class="<?= (isset($script) && $script == 'Trust Safty Insurance') ? 'active' : ''; ?>"><a href="trust-safty-insurance"><?= $langage_lbl['LBL_SAFETY_AND_INSURANCE']; ?></a></li>-->
                <!--<li class="<?= (isset($script) && $script == 'Terms Condition') ? 'active' : ''; ?>"><a href="terms-condition"><?= $langage_lbl['LBL_FOOTER_TERMS_AND_CONDITION']; ?></a></li>-->
                <!--<li class="<?= (isset($script) && $script == 'Legal') ? 'active' : ''; ?>"><a href="legal"><?= $langage_lbl['LBL_LEGAL']; ?></a></li>-->
                <!--<li class="<?= (isset($script) && $script == 'Faq') ? 'active' : ''; ?>"><a href="faq"><?= $langage_lbl['LBL_FAQs']; ?></a></li>-->
        <?php
    } else {
        if ($user == 'driver') {
            ?>

            <li class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><a href="profile"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></span></a></li>
            <?php
            if ($APP_TYPE != 'UberX') {
                if ($RideDeliveryBothFeatureDisable == 'No') {
                    ?>
                    <li class="<?= (isset($script) && $script == 'Vehicle') ? 'active' : ''; ?>"><a href="vehicle"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_VEHICLES']; ?></span></a></li>
                    <?php
                }
            }
            if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $ufxenableleft == 'Yes' && $compdb_data[0]['iServiceId'] == 0) {
                ?>
                <li class="<?= (isset($script) && $script == 'My Availability') ? 'active' : ''; ?>"><a href="add_services.php?iDriverId=<?= base64_encode(base64_encode($_SESSION['sess_iUserId'])); ?>"><span><?= $langage_lbl['LBL_HEADER_MY_SERVICES']; ?></span></a></li>
                <li class="<?= (isset($script) && $script == 'My Services') ? 'active' : ''; ?>"><a href="add_availability.php"><span><?= $langage_lbl['LBL_HEADER_MY_AVAILABILITY']; ?></span></a></li>
                <?php
            }
            if ($onlyDeliverallModule == "NO" && $compdb_data[0]['iServiceId'] == 0) {
                if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $hideProviderJob > 0) { ?>
                    <li class="<?= (isset($script) && $script == 'Trips') ? 'active' : ''; ?>"><a href="provider-job"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_TRIPS_TEXT']; ?></span></a></li>
                <?php } else if($hideProviderJob > 0){ ?>
                    <li class="<?= (isset($script) && $script == 'Trips') ? 'active' : ''; ?>"><a href="driver-trip"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_TRIPS_TEXT']; ?></span></a></li>
                    <?php
                }
            }
            ?>
            <?php if ($deliverallModule == "YES") { ?>
                <li class="<?= (isset($script) && $script == 'Order') ? 'active' : ''; ?>"><a href="driver-order"><span><?= $langage_lbl['LBL_MY_DRIVER_ORDERS_TXT']; ?></span></a></li>
            <?php } ?>
            <?php if ($onlyDeliverallModule == "NO" && $myearnigMenuHide > 0) { ?>
                <li class="<?= (isset($script) && $script == 'Payment Request') ? 'active' : ''; ?>"><a href="payment-request"><span><?= $langage_lbl['LBL_HEADER_MY_EARN']; ?></span></a></li>
            <?php } ?>
            <?php
            if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
                if ($WALLET_ENABLE == 'Yes') {
                    ?> 
                    <li class="<?= (isset($script) && $script == 'Rider Wallet') ? 'active' : ''; ?>"><a href="provider_wallet"><span><?= $langage_lbl['LBL_RIDER_WALLET']; ?></span></a></li>
                    <?
                }
            } else {
                if ($WALLET_ENABLE == 'Yes') {
                    ?> 
                    <li class="<?= (isset($script) && $script == 'Rider Wallet') ? 'active' : ''; ?>"><a href="driver_wallet"><span><?= $langage_lbl['LBL_RIDER_WALLET']; ?></span></a></li>
                    <?
                }
            }
            if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $SERVICE_PROVIDER_FLOW == "Provider" && $onlyDeliverallModule == "NO") {
                ?>

                                                                                        <!--<li class="<?= (isset($script) && $script == 'Gallary') ? 'active' : ''; ?>"><a href="provider_images"><span><?= $langage_lbl['LBL_MANAGE_GALLARY']; ?></span></a></li>-->

            <? } ?>
            <li class="logout"><a href="logout"><span><?= $langage_lbl['LBL_LOGOUT']; ?></span></a></li>
        <?php } else if ($user == 'company') { ?>
            <?php if ($eSystem == "DeliverAll") { ?>
                <li class="<?= (isset($script) && $script == 'Dashboard') ? 'active' : ''; ?>"><a href="profile"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></span></a></li>
                <?php if ($isStoreDriver > 0) { ?>
                    <li class="<?= (isset($script) && $script == 'Driver') ? 'active' : ''; ?>"><a href="providerlist"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_DRIVER']; ?></span></a></li>
                <?php }?>
                <li class="<?= (isset($script) && $script == 'FoodMenu') ? 'active' : ''; ?>"><a href="food_menu.php"><span><?= $langage_lbl['LBL_FOOD_CATEGORY_LEFT_MENU']; ?></span></a></li>
                <li class="<?= (isset($script) && $script == 'MenuItems') ? 'active' : ''; ?>"><a href="menuitems.php"><span><?= $langage_lbl['LBL_MENU_ITEM_LEFT_MENU']; ?></span></a></li>
                <li class="<?= (isset($script) && $script == 'ProcessingOrder') ? 'active' : ''; ?>"><a href="processing-orders"><span><?= $langage_lbl['LBL_MY_PROCESSING_ORDERS_TXT']; ?></span></a></li>
                <li class="<?= (isset($script) && $script == 'Order') ? 'active' : ''; ?>"><a href="company-order"><span><?= $langage_lbl['LBL_MY_ORDERS_RESTAURANT_TXT']; ?></span></a></li>
                <?php if ($MANUAL_STORE_ORDER_WEBSITE == "Yes") { ?>
                    <li><a href="<?= $siteUrl; ?>user-order-information?order=store" target="_blank"><span><?= $manualOrderMenu; ?></span></a></li>
                <?php } ?>
                <li class="<?= (isset($script) && $script == 'Settings') ? 'active' : ''; ?>"><a href="settings"><span><?= $langage_lbl['LBL_SETTINGS']; ?></span></a></li>
            <?php } else { ?>
                <li class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><a href="profile"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></span></a></li>
                <? if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
                    <li class="<?= (isset($script) && $script == 'Driver') ? 'active' : ''; ?>"><a href="providerlist"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_DRIVER']; ?></span></a></li>
                <? } else { ?>
                    <li class="<?= (isset($script) && $script == 'Driver') ? 'active' : ''; ?>"><a href="driverlist"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_DRIVER']; ?></span></a></li>
                <? } ?>
                <? if ($PACKAGE_TYPE == "SHARK" && $onlyDeliverallModule == "NO") { ?>
                    <li class="<?= (isset($script) && $script == 'booking') ? 'active' : ''; ?>"><a href="companybooking"><span><?= $langage_lbl['LBL_MANUAL_TAXI_DISPATCH']; ?></span></a></li>
                    <? if ($RIDE_LATER_BOOKING_ENABLED == 'Yes') { ?><li class="<?= (isset($script) && $script == 'CabBooking') ? 'active' : ''; ?>"><a href="cabbooking.php"><span><?= $langage_lbl['LBL_RIDE_LATER_BOOKINGS_ADMIN']; ?></span></a></li>
                    <?
                    }
                }
                if ($APP_TYPE != 'UberX' && $RideDeliveryBothFeatureDisable == 'No') {
                    ?>
                    <li class="<?= (isset($script) && $script == 'Vehicle') ? 'active' : ''; ?>"><a href="vehicle"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_VEHICLES']; ?></span></a></li>
            <?php } if ($onlyDeliverallModule == "NO") { ?>
                    <li class="<?= (isset($script) && $script == 'Trips') ? 'active' : ''; ?>"><a href="company-trip"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_TRIPS']; ?></span></a></li>
            <?php } } ?>



            <li class="logout"><a href="logout"><span><?= $langage_lbl['LBL_LOGOUT']; ?></span></a></li>
            <!-- Left Menu For Organization Module -->

    <?php } else if ($user == 'organization') { ?>

            <li class="<?= (isset($script) && $script == 'Organization-Profile') ? 'active' : ''; ?>"><a href="organization-profile"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></span></a></li>

            <li class="<?= (isset($script) && $script == 'MyUsers') ? 'active' : ''; ?>"><a href="organization-user"><span><!-- <?= $langage_lbl['LBL_HEADER_TOPBAR_DRIVER']; ?> --><?= $langage_lbl['LBL_ORGANIZATION_USERS_WEB'] ?></span></a></li>

            <li class="<?= (isset($script) && $script == 'Organization-Users-Trips') ? 'active' : ''; ?>"><a href="users-trip"><?= $langage_lbl['LBL_ORGANIZATION_USER_TRIPS_WEB'] ?></a></li>

            <li class="<?= (isset($script) && $script == 'Trips') ? 'active' : ''; ?>"><a href="organization-trip"><span><!-- <?= $langage_lbl['LBL_HEADER_TOPBAR_TRIPS']; ?> --> <?= $langage_lbl['LBL_ORGANIZATION_TRIP_REPORT_WEB'] ?> </span></a></li>

            <li class="logout"><a href="logout"><span><?= $langage_lbl['LBL_LOGOUT']; ?></span></a></li>

            <!-- Left Menu For Organization Module -->		
        <?php } else if ($user == 'rider') { ?>
            <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
                <li class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><a href="<?php echo $tconfig['tsite_url']; ?>profile-rider"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></span></a></li>
            <?php } else { ?>
                <li class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><a href="<?php echo $tconfig['tsite_url']; ?>profile-rider"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></span></a></li>
            <? } ?>

            <?
            if ($onlyDeliverallModule == "NO") {
                if ($PACKAGE_TYPE == "SHARK") {
                    ?>
                    <li class="<?= (isset($script) && $script == 'booking') ? 'active' : ''; ?>"><a href="userbooking"><span><?= $langage_lbl['LBL_MANUAL_TAXI_DISPATCH']; ?></span></a></li>
                <? } ?>
                <li class="<?= (isset($script) && $script == 'Trips') ? 'active' : ''; ?>"><a href="<?php echo $tconfig['tsite_url']; ?>mytrip"><span><?= $langage_lbl['LBL_HEADER_TOPBAR_TRIPS']; ?></span></a></li>
            <?php } if ($deliverallModule == "YES") { ?>
                <li class="<?= (isset($script) && $script == 'Order') ? 'active' : ''; ?>"><a href="<?php echo $tconfig['tsite_url']; ?>myorder"><span><?= $langage_lbl['LBL_MY_ORDERS_TXT']; ?></span></a></li>
                <?php if ($MANUAL_STORE_ORDER_WEBSITE == "Yes") { ?>
                    <li class="<?= (isset($script) && $script == 'order-items') ? 'active' : ''; ?>"><a href="<?= $siteUrl; ?>order-items?order=user" target="_blank"><?= $manualOrderMenu; ?></a></li>

                    <?php
                }
            }
            ?>
                                                                              <!-- <li><a href="<?php echo $tconfig['tsite_url']; ?>mobi" ><b><img alt="" src="assets/img/my-taxi.png"></b><span><?= $langage_lbl['LBL_BOOK_A_RIDE']; ?></span></a></li> -->
            <?
            if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {
                if ($WALLET_ENABLE == 'Yes') {
                    ?> 
                    <li class="<?= (isset($script) && $script == 'Rider Wallet') ? 'active' : ''; ?>"><a href="<?php echo $tconfig['tsite_url']; ?>user_wallet"><span><?= $langage_lbl['LBL_RIDER_WALLET']; ?></span></a></li>
                    <?
                }
            } else {
                if ($WALLET_ENABLE == 'Yes') {
                    ?>
                    <li class="<?= (isset($script) && $script == 'Rider Wallet') ? 'active' : ''; ?>"><a href="<?php echo $tconfig['tsite_url']; ?>rider_wallet"><span><?= $langage_lbl['LBL_RIDER_WALLET']; ?></span></a></li>
                    <?php
                }
            }
            ?>
            <li class="logout"><a href="logout"><span><?= $langage_lbl['LBL_LOGOUT']; ?></span></a></li>
        <?php
    }
}
?>
</ul>
<div class="mix-content">