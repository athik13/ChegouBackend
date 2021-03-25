<?php
include_once('common.php');
include_once('generalFunctions.php');
$tbl_name = 'trips';
$script = "Trips";

//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-invoice_multi_delivery.php");
        exit;
}

$generalobj->check_member_login();
// $APP_DELIVERY_MODE = $generalobj->getConfigurations("configurations","APP_DELIVERY_MODE");
$APP_DELIVERY_MODE = "Multi";
if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "") {
    $HTTP_REFERER = $_SERVER['HTTP_REFERER'];
    $_SESSION['HTTP_REFERER'] = $HTTP_REFERER;
}
$_REQUEST['iTripId'] = base64_decode(base64_decode(trim($_REQUEST['iTripId'])));
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
/* Start original route */
$sql = "select tPlatitudes,tPlongitudes from trips_locations where iTripId = '" . $iTripId . "'";
$data_locations = $obj->MySQLSelect($sql);
$lat_array = explode(",", $data_locations[0]['tPlatitudes']);
$long_array = explode(",", $data_locations[0]['tPlongitudes']);
$total_ele = count($lat_array);
$inc = 1;
if ($total_ele > 200) {
    $inc = round($total_ele / 200);
}
// echo $inc=5;
for ($i = 0; $i < $total_ele; $i += $inc) {
    $latitudes[] = $lat_array[$i];
    $longitudes[] = $long_array[$i];
}
array_push($latitudes, $lat_array[$total_ele - 1]);
array_push($longitudes, $long_array[$total_ele - 1]);
/* End original route */
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> |<?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>  
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>
            <?php
            $sessionUser = $_SESSION['sess_user'];
            if ($sessionUser == 'driver') {
                $db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, $_SESSION['sess_iUserId'], 'Driver');
                $db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId, $_SESSION['sess_iUserId'], 'Driver');
            } else if ($sessionUser == 'rider') {
                $db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, $_SESSION['sess_iUserId'], 'Passenger');
                $db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId, $_SESSION['sess_iUserId'], 'Passenger');
            } else if ($sessionUser == 'organization') {
                $db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, $_SESSION['sess_iUserId'], 'Passenger', '', 'Yes');
                $db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId, $_SESSION['sess_iUserId'], 'Passenger', '', 'Yes');
            } else {
                $db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, '', 'Driver');
                $db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId, '', '');
            }
            // echo"<pre>";print_r($db_trip_data);die;
            ?>
            <div class="page-contant">
                <div class="page-contant-inner page-trip-detail">
                    <h2 class="header-page trip-detail"><?= $langage_lbl['LBL_Invoice']; ?>				
                        <a onClick="javascript:window.top.close();"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>					
<?php
$systemTimeZone = date_default_timezone_get();
if ($db_trip_data['fCancellationFare'] > 0 && $db_trip_data['vTimeZone'] != "") {
    $dBookingDate = $endDate = converToTz($db_trip_data['tEndDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
} else if ($db_trip_data['tTripRequestDateOrig'] != "" && $db_trip_data['vTimeZone'] != "") {
    $dBookingDate = converToTz($db_trip_data['tTripRequestDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
    $endDate = converToTz($db_trip_data['tEndDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
} else {
    $dBookingDate = $db_trip_data['tTripRequestDateOrig'];
    $endDate = $db_trip_data['tEndDateOrig'];
}
if (!empty($db_trip_data)) {
    ?>
                            <p><?= $langage_lbl['LBL_RATING_PAGE_HEADER_TXT']; ?> <strong><?= @date('h:i A', @strtotime($dBookingDate)); ?> <?= $langage_lbl['LBL_ON']; ?> <?= @date('d M Y', @strtotime($dBookingDate)); ?></strong></p>
                        <?php } ?>
                    </h2>
                    <?php if (!empty($db_trip_data)) { ?>
                        <div class="trip-detail-page">
                            <div class="trip-detail-page-inner">
                                <div class="trip-detail-page-left">
                                    <?php if ($db_trip_data['eType'] != 'UberX' && ($APP_DELIVERY_MODE != "Multi" || $db_trip_data['eType'] == "Ride")) { ?>
                                        <div class="trip-detail-map">
                                            <div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
                                        </div>
                                    <?php } ?>
                                    <div class="map-address">
                                        <ul>
                                            <li> 
                                                <b><i aria-hidden="true" class="fa fa-map-marker fa-22 green-location"></i></b> 
                                                <span>
                                                    <h3><?= @date('h:i A', @strtotime($dBookingDate)); ?></h3>
                                                    <?= $db_trip_data['tSaddress']; ?>
                                                </span> 
                                            </li>
                                            <?php if ($db_trip_data['eType'] != 'UberX' && ($APP_DELIVERY_MODE != "Multi" || $db_trip_data['eType'] == "Ride")) { ?> 
                                                <li> 
                                                    <b><i aria-hidden="true" class="fa fa-map-marker fa-22 red-location"></i></b> 
                                                    <span>
                                                        <h3><?= @date('h:i A', @strtotime($endDate)); ?></h3>
                                                        <?= $db_trip_data['tDaddress']; ?>
                                                    </span> 
                                                </li>
                                            <?php } ?> 
                                        </ul>
                                    </div>
                                    <?php
                                    $class_name = 'location-time';
                                    if ($db_trip_data['eType'] == 'UberX') {
                                        $class_name = 'location-time location-time-second';
                                    }
                                    ?>
                                    <div class="<?php echo $class_name ?>">
                                        <ul>
                                            <li>
                                                <?php if ($db_trip_data['eType'] == 'UberX') { ?>
                                                    <h4 style="font-weight: bold;"><?= $langage_lbl['LBL_MYTRIP_TRIP_TYPE']; ?></h4>
                                                <?php } else { ?>
                                                    <h4 style="font-weight: bold;"><?= $langage_lbl['LBL_INVOICE_Car']; ?></h4>
                                                <?php } ?>
                                                <?php
                                                if (!empty($db_trip_data['vVehicleCategory'])) {
                                                    echo $db_trip_data['vVehicleCategory'] . "-" . $db_trip_data['vVehicleType'];
                                                } else {
                                                    echo $db_trip_data['carTypeName'];
                                                }
                                                ?>
                                            </li>
                                                <?php if ($db_trip_data['eType'] != 'UberX') { ?> 
                                                <li>
                                                    <h4 style="font-weight: bold;"><?= $langage_lbl['LBL_DISTANCE_TXT']; ?></h4>
        <?php ?>
        <?= $db_trip_data['fDistance'] . $db_trip_data['DisplayDistanceTxt']; ?> 
                                                </li>
                                                <li>
                                                    <h4 style="font-weight: bold;"><?= $langage_lbl['LBL_Trip_time']; ?></h4>
                                                <?php echo $db_trip_data['TripTimeInMinutes']; ?>
                                                </li>
                                    <?php } ?> 
                                        </ul>
                                    </div>
                                            <?php if ((!empty($db_trip_data['vSignImage']))) { ?>
                                        <div class="sign-block">
                                            <ul>
                                                <?php
                                                if (file_exists($tconfig["tsite_upload_trip_signature_images_path"] . '/' . $db_trip_data['vSignImage'])) {
                                                    $img1 = $tconfig["tsite_upload_trip_signature_images"] . '/' . $db_trip_data['vSignImage'];
                                                }
                                                ?>
                                                <li class="sign-ele">
                                                    <b><?php echo $langage_lbl['LBL_SENDER_SIGN']; ?></b>
                                                    <img src="<?php echo $img1; ?>" align="left">
                                                </li>
                                            </ul>
                                        </div>
    <?php } ?>
                                </div>
                                <div class="trip-detail-page-right">
                                    <?php if ($sessionUser == "company") { ?>
                                        <div class="driver-info" style="height: auto;">
                                            <h3 style=" margin: 30px 0;"><?= $generalobj->clearName($db_trip_data['PassengerDetails']['vName'] . ' ' . $db_trip_data['PassengerDetails']['vLastName']); ?> <?= $langage_lbl['LBL_RIDE_TXT_ADMIN']; ?> <?= $langage_lbl['LBL_WITH_TXT']; ?>  <?= $generalobj->clearName($db_trip_data['DriverDetails']['vName'] . ' ' . $db_trip_data['DriverDetails']['vLastName']); ?></h3>
                                        </div>
                                                <?php } else if ($sessionUser == "driver") { ?>
                                        <div class="driver-info">
                                            <div class="driver-img">
                                                <span>
                                                    <?php if ($db_trip_data['PassengerDetails']['vImgName'] != '' && file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'])) {
                                                        ?>
                                                        <img src = "<?= $tconfig["tsite_upload_images_passenger"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'] ?>" style="height:150px;"/>
        <?php } else { ?>
                                                        <img src="assets/img/profile-user-img.png" alt="">
                                                        <?php } ?>
                                                    </div>
                                                    <h3><?= $langage_lbl['LBL_You_ride_with']; ?> <?= $generalobj->clearName($db_trip_data['PassengerDetails']['vName'] . ' ' . $db_trip_data['PassengerDetails']['vLastName']); ?></h3>
                                                    <p><b><?= $langage_lbl['LBL_Rate_Your_Ride']; ?>:</b>
                                                        <?php
                                                        $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                                                        $db_trip_data['TripRating'] = '<span style="width: 65px; height: 13px; background: url(' . $tconfig['tsite_upload_images'] . 'star-rating-sprite.png) 0 0;">
								<span style="margin: 0;float:left;display: block;width: ' . $rating_width . '%; height: 13px; background: url(' . $tconfig['tsite_upload_images'] . 'star-rating-sprite.png) 0 -13px;"></span>
								</span>';
                                                        ?>
        <?= $db_trip_data['TripRating']; ?></p>
                                            </div>
                                                    <?php } else { ?>
                                            <div class="driver-info">
                                                <div class="driver-img">
                                                    <span>
                                                        <?php if ($db_trip_data['DriverDetails']['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'])) {
                                                            ?>
                                                            <img src = "<?= $tconfig["tsite_upload_images_driver"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'] ?>" style="height:150px;"/>
        <?php } else { ?>
                                                            <img src="assets/img/profile-user-img.png" alt="">
                                                            <?php } ?>
                                                        </div>
                                                        <h3><?= $langage_lbl['LBL_You_ride_with']; ?> <?= $generalobj->clearName($db_trip_data['DriverDetails']['vName'] . ' ' . $db_trip_data['DriverDetails']['vLastName']); ?></h3>
                                                        <p><b><?= $langage_lbl['LBL_Rate_Your_Ride']; ?>:</b>
                                                            <?php
                                                            $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                                                            $db_trip_data['TripRating'] = '<span style="display: block; width: 65px; height: 13px; background: url(' . $tconfig['tsite_upload_images'] . 'star-rating-sprite.png) 0 0;">
								<span style="margin: 0;float:left;display: block; width: ' . $rating_width . '%; height: 13px; background: url(' . $tconfig['tsite_upload_images'] . 'star-rating-sprite.png) 0 -13px;"></span>
								</span>';
                                                            ?>
        <?= $db_trip_data['TripRating']; ?></p>
                                                </div>
                                                        <?php } ?>
                                            <div class="fare-breakdown">
                                                <div class="fare-breakdown-inner">
                                                    <h3><?php echo $langage_lbl['LBL_FARE_BREAKDOWN_RIDE_NO_TXT']; ?>. <b><?= $db_trip_data['vRideNo']; ?></b></h3>
                                                    <ul>
                                                        <?php
					      //added by SP for rounding off currency wise on 8-11-2019 start
                                                        $roundoff = 0;
                                                        if (array_key_exists($langage_lbl['LBL_ROUNDING_DIFF_TXT'], $db_trip_data['FareDetailsArr']) && !empty($db_trip_data['FareDetailsArr'][$langage_lbl['LBL_ROUNDING_DIFF_TXT']])) {
                                                            $roundoff = 1;
                                                        }
                                                        //added by SP for rounding off currency wise on 8-11-2019 end
					      
                                                        foreach ($db_trip_data['HistoryFareDetailsNewArr'] as $key => $value) {
                                                            foreach ($value as $k => $val) {
                                                                if ($k == $langage_lbl['LBL_EARNED_AMOUNT']) {
                                                                    continue;
                                                                 } else if ($k == $langage_lbl['LBL_SUBTOTAL_TXT'] && $roundoff==0) { //added by SP for rounding off currency wise on 8-11-2019
                                                                    continue; 
                                                                } else if($k == $langage_lbl['LBL_ROUNDING_DIFF_TXT'] && $roundoff==0) { //added by SP for rounding off currency wise on 8-11-2019
                                                                    continue;
                                                                } else if ($k == "eDisplaySeperator") {
                                                                    echo '<li><hr style="border-top-color:#d1d1d1" /></li>';
                                                                } else {
                                                                    ?>
                                                                    <li><strong><?= $k; ?></strong><b><?php echo $val; ?></b></li>
                                                                <?php
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </ul>
                                                    <span>
                                                        <?php //$paymentMode = ($db_trip_data['vTripPaymentMode'] == 'Cash')? $langage_lbl['LBL_VIA_CASH_TXT']: $langage_lbl['LBL_VIA_CARD_TXT']?>

                                                        <?php
                                                        if ($db_trip_data['ePayWallet'] == 'Yes') {
                                                            $paymentMode = $langage_lbl['LBL_PAY_BY_WALLET_TXT'];
                                                        } else {
                                                            if ($db_trip_data['vTripPaymentMode'] == 'Cash')
                                                                $paymentMode = $langage_lbl['LBL_VIA_CASH_TXT'];
                                                            else if ($db_trip_data['vTripPaymentMode'] == 'Card')
                                                                $paymentMode = $langage_lbl['LBL_VIA_CASH_TXT'];
                                                            else if ($db_trip_data['vTripPaymentMode'] == 'Organization')
                                                                $paymentMode = "Pay By Organization";
                                                        }
                                                        ?>

                                                        <?php if ($sessionUser == "driver" || $sessionUser == "company") { ?>
                                                            <h4><?= $langage_lbl['LBL_TOTAL_EARNINGS_FRONT']; ?> (<?php echo $paymentMode ?>)</h4>
                                                            <em><?= $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_EARNED_AMOUNT']]; ?></em>
                                                        <?php } else { ?>
                                                            <h4><?= $langage_lbl['LBL_Total_Fare']; ?> (<?php echo $paymentMode ?>)</h4>
                                                            <!--<em><?= $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_SUBTOTAL_TXT']]; ?></em>-->
						<em><?= ($roundoff==1) ? $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT']] : $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_SUBTOTAL_TXT']]; ?></em><!-- //added by SP for rounding off currency wise on 8-11-2019  -->
                                                        <?php } ?>
                                                    </span>

                                                    <span style="border: 1px dashed #d1d1d1; padding: 4px;">
                                                            <?php
                                                            foreach ($db_reci_data as $key1 => $value1) {
                                                                foreach ($value1 as $key2 => $value2) {
                                                                    if (!empty($value2['ePaymentBy'])) {
                                                                        ?>
                                                                    <tr>
                                                                        <td><h4><?= $langage_lbl['LBL_PAYMENT_BY_TXT'] ?></h4></td>
                                                                        <?php
                                                                        if ($value2['ePaymentBy'] == "Sender") {
                                                                            ?><td><em><?= $langage_lbl['LBL_SENDER'] ?></em></td><?php
                                                    } else if ($value2['ePaymentBy'] == "Receiver") {
                                                                            ?><td><em><?= $value2['PaymentPerson']; ?></em></td><?php
                                                                    } else if ($value2['ePaymentBy'] == "Individual") {
                                                                        ?><td><em><?= $langage_lbl_admin['LBL_EACH_RECIPIENT']; ?></em></td><?php
                                                                    }
                                                                    ?>
                                                                    </tr>
                <?php
                break 2;
            }
        }
    }
    ?>
                                                    </span>

                                                                <?php if (($db_trip_data['iActive'] == 'Finished' && $db_trip_data['eCancelled'] == "Yes") || ($db_trip_data['fCancellationFare'] > 0) || ($db_trip_data['iActive'] == 'Canceled' && $db_trip_data['fWalletDebit'] > 0)) {
                                                                    ?>
                                                        <ul>
                                                            <li style="border:dotted 2px #000000;background: none;">
                                                                <strong style="font-weight: bold;padding: 2px;" >
                                                                    <?php
                                                                    if ($db_trip_data['eCancelledBy'] == 'Driver') {
                                                                        echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_DRIVER_ADMIN'];
                                                                        if (!empty($db_trip_data['vCancelReason'])) {
                                                                            echo 'Reason: ' . $db_trip_data['vCancelReason'];
                                                                        }
                                                                    } else if ($db_trip_data['eCancelledBy'] == 'Passenger') {
                                                                        echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_PASSANGER_ADMIN'];
                                                                        if (!empty($db_trip_data['vCancelReason'])) {
                                                                            echo 'Reason: ' . $db_trip_data['vCancelReason'];
                                                                        }
                                                                    } else {
                                                                        echo $langage_lbl_admin['LBL_CANCELED_TRIP_ADMIN_TXT'];
                                                                    }
                                                                    ?>
                                                                </strong>
                                                            </li>
                                                        </ul>
                                                        <div style="clear:both;"></div>
    <?php } ?>	
    <?php if ($sessionUser != "driver" && $sessionUser != "company") {
        $db_trip_data['fTipPrice'] = '222';
        if ($db_trip_data['fTipPrice'] != "" && $db_trip_data['fTipPrice'] != "0" && $db_trip_data['fTipPrice'] != "0.00") {
            ?>
                                                            <ul><li><strong><?= $langage_lbl['LBL_TIP_RS_TXT']; ?></strong><b> <?= $db_trip_data['fTipPrice']; ?></b></li></ul>
        <?php }
    } ?>

                                                    <div style="clear:both;"></div>
    <?php if ($db_trip_data['eType'] == 'Deliver' && $APP_DELIVERY_MODE != 'Multi') { ?>
                                                        <br>
                                                        <h3><?= $langage_lbl['LBL_DELIVERY_DETAILS']; ?></h3><hr/>

                                                        <ul style="border-bottom:none">
                                                            <li><strong><?= $langage_lbl['LBL_RECEIVER_NAME']; ?> </strong><b><?= $db_trip_data['vReceiverName']; ?></b></li>
                                                            <li><strong><?= $langage_lbl['LBL_RECEIVER_MOBILE']; ?> </strong><b><?= $db_trip_data['vReceiverMobile']; ?></b></li>
                                                            <li><strong><?= $langage_lbl['LBL_PICK_UP_INS']; ?> </strong><b><?= $db_trip_data['tPickUpIns']; ?></b></li>
                                                            <li><strong><?= $langage_lbl['LBL_DELIVERY_INS']; ?> </strong><b><?= $db_trip_data['tDeliveryIns']; ?></b></li>
                                                            <li><strong><?= $langage_lbl['LBL_PACKAGE_DETAILS']; ?></strong><b><?= $db_trip_data['tPackageDetails']; ?></b></li>
                                                            <li><strong><?= $langage_lbl['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?> </strong><b><?= $db_trip_data['vDeliveryConfirmCode']; ?></b></li>       

                                                        </ul>

    <?php } ?>

                                                    <div style="clear:both;"></div>
                                                        <?php
                                                        if ($db_trip_data['eType'] == 'UberX' && ($db_trip_data['vBeforeImage'] != '' || $db_trip_data['vAfterImage'] != '')) {
                                                            $img_path = $tconfig["tsite_upload_trip_images"];
                                                            ?> 
                                                        <h3><?php echo $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT']; ?></b></h3>                      

                                                        <div class="invoice-right-bottom-img">
                                                        <?php if ($db_trip_data['vBeforeImage'] != '') { ?>                     
                                                                <div class="col-sm-6">
                                                                    <h4> <?php echo $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN']; ?></h4>
                                                                    <b><a href="<?= $db_trip_data['vBeforeImage'] ?>" target="_blank" ><img src = "<?= $db_trip_data['vBeforeImage'] ?>" style="width:200px;" alt ="Before Images"/></a></b>
                                                                </div>
                                            <?php }
                                            if ($db_trip_data['vAfterImage'] != '') {
                                                ?>
                                                                <div class="col-sm-6">
                                                                    <h4><?php echo $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN']; ?></h4>
                                                                    <b><a href="<?= $db_trip_data['vAfterImage'] ?>" target="_blank" ><img src = "<?= $db_trip_data['vAfterImage'] ?>" style="width:200px;" alt ="After Images"/></a></b>
                                                                </div>
                                                <?php } ?>
                                                        </div>
    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
    <?php if ($APP_DELIVERY_MODE == "Multi") { ?>

                                            <div class="invoice-part-bottom invoice-part-bottom1">	

                                                        <?php
                                                        if (!empty($db_reci_data)) {
                                                            foreach ($db_reci_data as $key1 => $value1) {
                                                                $no = $key1 + 1;
                                                                $class = (!empty($value1['vSignImage'])) ? 'sign-img' : '';
                                                                ?>
                                                        <div class="col-sm-6 <?php echo $class; ?>"> 
                                                            <h3><?= $langage_lbl['LBL_RECIPIENT_LIST_TXT'] . '&nbsp;' . $no; ?></h3>
                                                            <table style="width:100%" class="deliverytable" cellpadding="5" cellspacing="0" border="0">
                <?php foreach ($value1 as $key2 => $value2) { ?>
                                                                    <tr>
                                                                        <td style="min-width: 150px;"><b><?= $value2['vFieldName']; ?> </b></td>
                                                                        <td><?= $value2['vValue']; ?> </td>
                                                                    </tr>
                                                                <?php } ?>
                                                                <?php if (!empty($value2['ePaymentBy']) && $value2['ePaymentBy'] == "Individual") { ?>
                                                                    <tr>
                                                                        <td style="min-width: 150px;"><b><?= $langage_lbl['LBL_AMOUNT_PAID_TXT']; ?></b></td>
                                                                        <td>
                                                                            <?= $value2['PaymentAmount']; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                <?php if (!empty($value2['Receipent_Signature'])) { ?>
                                                                    <tr>
                                                                        <td style="min-width: 150px;"><b><?= $langage_lbl['LBL_RECEIVER_SIGN']; ?></b></td>
                                                                        <td>
                                                                            <img width="100px" src="<?php echo $value2['Receipent_Signature']; ?>" align="left" >
                                                                        </td>
                                                                    </tr>
                                                    <?php } else if ($value2['vDeliveryConfirmCode'] != "") { ?>
                                                                    <tr>
                                                                        <td style="min-width: 150px;"><b><?= $langage_lbl['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?></b></td>
                                                                        <td >
                                                <?= $value2['vDeliveryConfirmCode'] ?>
                                                                        </td>
                                                                    </tr>
                                            <?php } ?>
                                                            </table>
                                                        </div>
                <?php
            }
        }
        ?>               				 
                                            </div>

                        <?php } ?>
                                    </div>
                                </div>
    <?php
} else {
    ?>
                                <div class="trip-detail-page">
                                    <div class="trip-detail-page-inner">
                                        We could not find INVOICE details for this Trip. Please click browser's back button and check again.
                                    </div>
                                </div>
<?php } ?> 
                        </div>
                    </div>
<?php include_once('footer/footer_home.php'); ?>
                    <div style="clear:both;"></div>
                </div>
                <!-- home page end-->
                <!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
                <script src="assets/js/gmap3.js"></script>
                <script type="text/javascript">
                            h = window.innerHeight;
                            $("#page_height").css('min-height', Math.round(h - 99) + 'px');
                            var arr1 = [];
                            var lats = [];
                            var longs = [];
                            var markers = [];
                            var map;
                            function initialize() {
                                var thePoint = new google.maps.LatLng('20.1849963', '64.4125062');
                                var mapOptions = {
                                    zoom: 4,
                                    center: thePoint
                                };
                                map = new google.maps.Map(document.getElementById('map-canvas'),
                                        mapOptions);

                                from_to_polyline();
                            }


                            var tPlatitudes = '<?= json_encode($latitudes) ?>';
                            lats = JSON.parse(tPlatitudes);
                            var tPlongitudes = '<?= json_encode($longitudes) ?>';
                            longs = JSON.parse(tPlongitudes);

                            var pts = [];
                            var bounds = new google.maps.LatLngBounds();
                            for (var i = 0; i < lats.length; i++) {
                                var latlongs = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
                                pts.push(latlongs);
                                var point = latlongs;
                                bounds.extend(point);

                                if (i == 0) {
                                    var start = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
                                } else if (i == lats.length - 1) {
                                    var end = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
                                }
                            }

                            var directionsService = new google.maps.DirectionsService();
                            var directionsOptions = {// For Polyline Route line options on map
                                polylineOptions: {
                                    path: pts,
                                    strokeColor: '#f35e2f',
                                    strokeOpacity: 1.0,
                                    strokeWeight: 4
                                }
                            };
                            var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);

                            function from_to() {
                                var request = {
                                    origin: start, // From locations latlongs
                                    destination: end, // To locations latlongs
                                    travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
                                };
                                directionsService.route(request, function (response, status) {
                                    directionsDisplay.setMap(map);
                                    directionsDisplay.setDirections(response);
                                });
                            }

                            $(document).ready(function () {
                                google.maps.event.addDomListener(window, 'load', initialize);
                            })

                            function from_to_polyline() {
                                DeleteMarkers('from_loc');
                                DeleteMarkers('to_loc');
                                setMarker(start, 'from_loc');
                                setMarker(end, 'to_loc');

                                var flightPath = '';
                                var flightPath = new google.maps.Polyline({
                                    path: pts,
                                    geodesic: true,
                                    strokeColor: '#f35e2f',
                                    strokeOpacity: 1.0,
                                    strokeWeight: 4
                                });
                                map.fitBounds(bounds);
                                flightPath.setMap(map);
                            }

                            function setMarker(postitions, valIcon) {
                                var newIcon;
                                if (valIcon == 'from_loc') {
                                    newIcon = 'webimages/upload/mapmarker/PinFrom.png';
                                } else if (valIcon == 'to_loc') {
                                    newIcon = 'webimages/upload/mapmarker/PinTo.png';
                                } else {
                                    newIcon = 'webimages/upload/mapmarker/PinTo.png';
                                }
                                marker = new google.maps.Marker({
                                    map: map,
                                    animation: google.maps.Animation.DROP,
                                    position: postitions,
                                    icon: newIcon
                                });
                                marker.id = valIcon;
                                markers.push(marker);
                            }

                            function DeleteMarkers(newId) {
                                for (var i = 0; i < markers.length; i++) {
                                    if (newId != '') {
                                        if (markers[i].id == newId) {
                                            markers[i].setMap(null);
                                        }
                                    } else {
                                        markers[i].setMap(null);
                                        markers = [];
                                    }
                                }
                            }
                            ;
                            /*
                             function from_to(){
                             
                             $("#map-canvas").gmap3({
                             getroute:{
                             options:{
                             origin:'<?= $db_trip_data['tStartLat'] . "," . $db_trip_data['tStartLong'] ?>',
                             destination:'<?= $db_trip_data['tEndLat'] . "," . $db_trip_data['tEndLong'] ?>',
                             travelMode: google.maps.DirectionsTravelMode.DRIVING
                             },
                             callback: function(results){
                             if (!results) return;
                             $(this).gmap3({
                             map:{
                             options:{
                             zoom: 13,
                             center: [-33.879, 151.235]
                             }
                             },
                             directionsrenderer:{
                             options:{
                             directions:results
                             }
                             }
                             });
                             }
                             }
                             });
                             }
                             from_to();*/
                </script>
                <!-- End: Footer Script -->
                </body>
                </html>
