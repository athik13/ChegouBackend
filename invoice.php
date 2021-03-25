<?php
include_once('common.php');
include_once('generalFunctions.php');
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
$tbl_name = 'trips';
$script = "Trips";


//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-invoice.php");
        exit;
}

$generalobj->check_member_login();
$abc = 'rider,driver,company,organization';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);

$APP_DELIVERY_MODE = $generalobj->getConfigurations("configurations", "APP_DELIVERY_MODE");
if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "") {
    $HTTP_REFERER = $_SERVER['HTTP_REFERER'];
    $_SESSION['HTTP_REFERER'] = $HTTP_REFERER;
}
$eUserType = $_SESSION['sess_user'];
$_REQUEST['iTripId'] = base64_decode(base64_decode(trim($_REQUEST['iTripId'])));
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';

// print_r($_SESSION);
// echo $iTripId;
// exit;
/* Start original route */
$sql = "select tPlatitudes,tPlongitudes from trips_locations where iTripId = '" . $iTripId . "' AND iTripId != ''";
$data_locations = $obj->MySQLSelect($sql);
$lat_array = explode(",", $data_locations[0]['tPlatitudes']);
$long_array = explode(",", $data_locations[0]['tPlongitudes']);

if(empty($data_locations)) {
    $sql = "select tStartLat,tStartLong,tEndLat,tEndLong from trips where iTripId = '" . $iTripId . "'";
    $data_locations = $obj->MySQLSelect($sql);
    $lat_array[0] = $data_locations[0]['tStartLat'];
    $lat_array[1] = $data_locations[0]['tEndLat'];
    $long_array[0] = $data_locations[0]['tStartLong'];
    $long_array[1] = $data_locations[0]['tEndLong'];
}

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

$orgDataArr = array();
$org_sql = "SELECT vCompany,iOrganizationId FROM organization ORDER BY iOrganizationId ASC";
$orgData = $obj->MySQLSelect($org_sql);
//echo "<pre>";
//print_r($_SESSION['sess_lang']);die;
for ($g = 0; $g < count($orgData); $g++) {
    $orgDataArr[$orgData[$g]['iOrganizationId']] = $orgData[$g]['vCompany'];
}
array_push($latitudes, $lat_array[$total_ele - 1]);
array_push($longitudes, $long_array[$total_ele - 1]);
/* End original route */
$getAllTrip = array();
$vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0; $r < count($getVehicleTypes); $r++) {
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
}

$tsite_sc_host = $tconfig['tsite_sc_host'];
$tsite_host_sc_port = $tconfig['tsite_host_sc_port'];

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> |<?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>  
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
        <script src="<?php echo $tconfig['tsite_url']; ?>assets/js/channel_subscribe.js"></script>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/markerAnimate.js"></script>
        <script>
            var iTripId = '<?php echo $iTripId; ?>';
            var latlng;
            var locallat;
            var locallang;
            var map;
            var interval3;
            var marker = [];
            var myOptions = [];
            
            function initialize() { 
                directionsService2 = new google.maps.DirectionsService();
                directionsDisplay2 = new google.maps.DirectionsRenderer();
                $.ajax({
                    type: "POST",
                    url: "ajax_getdirver_detail.php",
                    dataType: "json",
                    data: {iTripId: iTripId},
                    success: function (driverdetail) {
                        if (driverdetail != 1) { 
                            $('.map-page').show();
                            var latdrv = driverdetail.vLatitude;
                            var longdrv = driverdetail.vLongitude;
                            latlng = new google.maps.LatLng(latdrv, longdrv);
                            locallat = new google.maps.LatLng(driverdetail.tStartLat, driverdetail.tStartLong);
                            locallang = new google.maps.LatLng(driverdetail.tEndLat, driverdetail.tEndLong);
                            fromLatlongs = driverdetail.tStartLat + ", " + driverdetail.tStartLong;
                            toLatlongs = driverdetail.tEndLat + ", " + driverdetail.tEndLong;
                            //toLatlongs = '23.0146207'+", "+'72.5284118';
                            myOptions = {
                                zoom: 16,
                                center: latlng,
                            }
                            map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
                            var overlay = new google.maps.OverlayView()
                            overlay.draw = function () {
                                this.getPanes().markerLayer.id = 'markerLayer'
                            }
                            marker = new google.maps.Marker({
                                position: latlng,
                                map: map,
                                //animation:google.maps.Animation.BOUNCE,
                                //icon: "webimages/upload/mapmarker/car_driver.png",
                                icon: {
                                    url: 'webimages/upload/mapmarker/source_marker.png',
                                    // This marker is 20 pixels wide by 32 pixels high.
                                    scaledSize: new google.maps.Size(50, 50),
                                    rotation: 90
                                },
                                id: 'marker'
                            });
                        } else {
                            $('.map-page').hide();
                        }
                    }
                });
            }
            google.maps.event.addDomListener(window, 'load', initialize);
        </script>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>
            <?php
            $ssql_trip = "";
            if($_SESSION['sess_user'] == "rider") {
               $ssql_trip = " where iTripId = '".$iTripId."' AND iUserId = '".$_SESSION['sess_iUserId']."'";
            } else if($_SESSION['sess_user'] == "driver") {
                $ssql_trip = " where iTripId = '".$iTripId."' AND iDriverId = '".$_SESSION['sess_iUserId']."'";
            } else if($_SESSION['sess_user'] == "company") {
                $ssql_trip = " t LEFT JOIN register_driver d ON t.iDriverId = d.iDriverId where t.iTripId = '".$iTripId."' AND d.iCompanyId = '".$_SESSION['sess_iUserId']."'"; 
            } else {
                $ssql_trip = " where iTripId = '".$iTripId."' AND iOrganizationId = '".$_SESSION['sess_iUserId']."'";
            }
            
            $user_trip_sql = "select * from trips ".$ssql_trip; 
            $user_trip_sql_data = $obj->MySQLSelect($user_trip_sql);
            
            if(!empty($user_trip_sql_data)){
                 if ($_SESSION['sess_user'] == 'driver') {
                    $db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, $_SESSION['sess_iUserId'], 'Driver');
                } else if ($_SESSION['sess_user'] == 'rider') {
                    $db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, $_SESSION['sess_iUserId'], 'Passenger');
                } else if ($_SESSION['sess_user'] == 'organization') {
                    $db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, $_SESSION['sess_iUserId'], 'Passenger', '', 'Yes');
                } else {
                    $db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, '', 'Driver');
                }
            }
           
  
            $organizationName = "";
            //Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected Start
            if (isset($db_trip_data['tVehicleTypeFareData']) && $db_trip_data['tVehicleTypeFareData'] != "") {
                $decodeTypeData = (array) json_decode($db_trip_data['tVehicleTypeFareData']);
                $decodeTypeData = $decodeTypeData['FareData'];
                $db_trip_data['vCategory'] = $db_trip_data['vVehicleCategory'] = $decodeTypeData[0]->vVehicleCategory;
            }
            //Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected End
            if (isset($orgDataArr[$db_trip_data['iOrganizationId']]) && $orgDataArr[$db_trip_data['iOrganizationId']] != "" && $db_trip_data['ePaymentBy'] == "Organization" && $eUserType == "rider") {
                $organizationName = $orgDataArr[$db_trip_data['iOrganizationId']];
            }
            ?>
            <div class="page-contant">
                <div class="page-contant-inner page-trip-detail clearfix">
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
                            if(!empty($iTripId)) {
                            ?>
                            <p><?= $langage_lbl['LBL_RATING_PAGE_HEADER_TXT']; ?> <strong><?= @date('h:i A', @strtotime($dBookingDate)); ?> <?= $langage_lbl['LBL_ON']; ?> <?= @date('d M Y', @strtotime($dBookingDate)); ?></strong></p>
                        <?php } } ?>
                    </h2>
                    <?php if (!empty($db_trip_data)) { ?>
                        <div class="trip-detail-page">
                            
                            <div class="trip-detail-page-inner clearfix">
                                <div class="trip-detail-page-left">
                                    <?php if ($db_trip_data['eType'] != 'UberX') { ?>
                                        <div class="trip-detail-map">
                                            <div id="map-canvas" class="gmap3" style="width:100%;height:300px;margin-bottom:10px;"></div>
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
                                            <?php if ($db_trip_data['eType'] != 'UberX') { ?> 
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
                                    if ($db_trip_data['eType'] == 'UberX') {
                                        $class_name = 'location-time location-time-second';
                                    } else {
                                        $class_name = 'location-time';
                                    }
                                    ?>
                                    <div class="<?php echo $class_name ?>">
                                        <ul>
                                            <li>
                                                <? if ($db_trip_data['eType'] == 'UberX') { ?>
                                                    <h4 style="font-weight: bold;"><?= $langage_lbl['LBL_MYTRIP_TRIP_TYPE']; ?></h4>
                                                <? } else { ?>
                                                    <h4 style="font-weight: bold;"><?php //$langage_lbl['LBL_INVOICE_Car']; ?><?php echo $db_trip_data['eIconType'];  ?></h4>
                                                <? } ?>
                                                <?php
                                                if (!empty($db_trip_data['vVehicleCategory'])) {
                                                    $printCategory = $db_trip_data['vVehicleCategory'];
                                                    if ($db_trip_data['vVehicleType'] != "") {
                                                        $printCategory .= "-" . $db_trip_data['vVehicleType'];
                                                    }
                                                } else {
                                                    $printCategory = $db_trip_data['carTypeName'];
                                                }
                                                $seriveJson = "";

                                                if (isset($db_trip_data['tVehicleTypeData']) && $db_trip_data['tVehicleTypeData'] != "" && $printCategory == "") {
                                                    $seriveJson = $db_trip_data['tVehicleTypeData'];
                                                    ?>
                                                    <button style="margin-top: 3px;" class="btn btn-success" data-trip="<?= $db_trip_data['vRideNo']; ?>" data-json='<?= $seriveJson; ?>' onclick="return showServiceModal(this);">
                                                        <i class="fa fa-certificate icon-white"><b> View Service</b></i>
                                                    </button>
                                                    <?php
                                                } else {
                                                    echo $printCategory;
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
                                                    <? echo $db_trip_data['TripTimeInMinutes']; ?>
                                                </li>
                                            <?php } ?> 
                                        </ul>
                                    </div>
                                </div>
                                <div class="trip-detail-page-right">
                                    <?php if ($_SESSION['sess_user'] == "company") { ?>
                                        <div class="driver-info" style="height: auto;">
                                            <h3 style=" margin: 30px 0;"><?= $generalobj->clearName($db_trip_data['PassengerDetails']['vName'] . ' ' . $db_trip_data['PassengerDetails']['vLastName']); ?> <?= $langage_lbl['LBL_RIDE_TXT_ADMIN']; ?> <?= $langage_lbl['LBL_WITH_TXT']; ?>  <?= $generalobj->clearName($db_trip_data['DriverDetails']['vName'] . ' ' . $db_trip_data['DriverDetails']['vLastName']); ?></h3>
                                        </div>
                                    <?php } else if ($_SESSION['sess_user'] == "driver") { ?>
                                        <div class="driver-info">
                                            <div class="driver-img">
                                                <span>
                                                    <? if ($db_trip_data['PassengerDetails']['vImgName'] != '' && file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'])) {
                                                        ?>
                                                        <img src = "<?= $tconfig["tsite_upload_images_passenger"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'] ?>" /><!-- style="height:150px;" -->
                                                    <? } else { ?>
                                                        <img src="assets/img/profile-user-img.png" alt="">
                                                    <? } ?>
                                                    </div>
                                                    <h3><?= $langage_lbl['LBL_You_ride_with']; ?> <?= $generalobj->clearName($db_trip_data['PassengerDetails']['vName'] . ' ' . $db_trip_data['PassengerDetails']['vLastName']); ?></h3>
                                                    <p><b><?= $langage_lbl['LBL_Rate_Your_Ride']; ?>:</b>
                                                        <?php
                                                        $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                                                        $db_trip_data['TripRating'] = '<span style="width: 65px; height: 13px; background: url(' . $tconfig['tsite_upload_images'] . 'star-rating-sprite.png) 0 0;">
								<span style="margin: 0;float:left;display: block; width: ' . $rating_width . '%; height: 13px; background: url(' . $tconfig['tsite_upload_images'] . 'star-rating-sprite.png) 0 -13px;"></span>
								</span>';
                                                        ?>
                                                        <?= $db_trip_data['TripRating']; ?></p>
                                            </div>
                                        <?php } else { ?>
                                            <div class="driver-info">
                                                <div class="driver-img">
                                                    <span>
                                                        <? if ($db_trip_data['DriverDetails']['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'])) {
                                                            ?>
                                                            <img src = "<?= $tconfig["tsite_upload_images_driver"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'] ?>" style="height:150px;"/>
                                                        <? } else { ?>
                                                            <img src="assets/img/profile-user-img.png" alt="">
                                                        <? } ?>
                                                        </div>
                                                        <h3><?= $langage_lbl['LBL_You_ride_with']; ?> <?= $generalobj->clearName($db_trip_data['DriverDetails']['vName'] . ' ' . $db_trip_data['DriverDetails']['vLastName']); ?></h3>
                                                        <div class="rating-row">
                                                            <label><?= $langage_lbl['LBL_Rate_Your_Ride']; ?>:</label>
                                                            <span><?php
                                                        $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                                                        $db_trip_data['TripRating'] = '<span style="display: block; width: 65px; height: 13px; background: url(' . $tconfig['tsite_upload_images'] . 'star-rating-sprite.png) 0 0;">
									<span style="margin: 0;float:left;display: block; width: ' . $rating_width . '%; height: 13px; background: url(' . $tconfig['tsite_upload_images'] . 'star-rating-sprite.png) 0 -13px;"></span>
									</span>';
                                                        ?>
                                                                <?= $db_trip_data['TripRating']; ?></span>
                                                        </div>
                                                </div>
                                            <?php } ?>
                                            <div class="fare-breakdown">
                                                <div class="fare-breakdown-inner">
                                                    <h3><?php echo $langage_lbl['LBL_FARE_BREAKDOWN_RIDE_NO_TXT']; ?>. <b><?= $db_trip_data['vRideNo']; ?></b></h3>
                                                    <ul>
                                                        <?php
                                                        //added by SP for rounding off currency wise on 26-8-2019 start
                                                        $roundoff = 0;
                                                        if (array_key_exists($langage_lbl['LBL_ROUNDING_DIFF_TXT'], $db_trip_data['FareDetailsArr']) && !empty($db_trip_data['FareDetailsArr'][$langage_lbl['LBL_ROUNDING_DIFF_TXT']])) {
                                                            $roundoff = 1;
                                                        }
                                                        //added by SP for rounding off currency wise on 26-8-2019 end
                                                        foreach ($db_trip_data['HistoryFareDetailsNewArr'] as $key => $value) {
                                                            foreach ($value as $k => $val) {
                                                                if ($k == $langage_lbl['LBL_EARNED_AMOUNT']) {
                                                                    continue;
                                                                } else if ($k == $langage_lbl['LBL_SUBTOTAL_TXT'] && $roundoff==0) { //added by SP for rounding off currency wise on 26-8-2019 
                                                                    continue; 
                                                                } else if($k == $langage_lbl['LBL_ROUNDING_DIFF_TXT'] && $roundoff==0) { //added by SP for rounding off currency wise on 26-8-2019 
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
                                                        <?php
                                                        if ($db_trip_data['ePayWallet'] == 'Yes' || $db_trip_data['fWalletDebit']  > 0) {
                                                            if (strtoupper($db_trip_data['vTripPaymentMode']) == "CARD") {
                                                                //$paymentMode = ucwords($langage_lbl['LBL_VIA_CARD_TXT']) . "-" . ucwords($langage_lbl['LBL_CARD_CAPS']); // Commented BY HJ On 24-02-2020 As Per Discuss with KS Sir
                                                                $paymentMode = ucwords($langage_lbl['LBL_VIA_CARD_TXT']);
                                                            } else if (strtoupper($db_trip_data['vTripPaymentMode']) == "CASH") {
                                                                //$paymentMode = ucwords($langage_lbl['LBL_VIA_CASH_TXT']) . "-" . ucwords($langage_lbl['LBL_CASH_CAPS']); // Commented BY HJ On 24-02-2020 As Per Discuss with KS Sir
                                                                $paymentMode = ucwords($langage_lbl['LBL_VIA_CASH_TXT']);
                                                            } else {
                                                                $paymentMode = $langage_lbl['LBL_PAY_BY_WALLET_TXT'];
                                                            }
                                                        } else {
                                                            if (strtoupper($db_trip_data['vTripPaymentMode']) == 'CASH')
                                                                $paymentMode = $langage_lbl['LBL_VIA_CASH_TXT'];
                                                            else if (strtoupper($db_trip_data['vTripPaymentMode']) == 'CARD')
                                                                $paymentMode = $langage_lbl['LBL_VIA_CARD_TXT'];
                                                            else if (strtoupper($db_trip_data['vTripPaymentMode']) == 'ORGANIZATION')
                                                                $paymentMode = "Pay By Organization";
                                                        }
                                                        ?>
                                                        <?php if ($_SESSION['sess_user'] == "driver" || $_SESSION['sess_user'] == "company") { ?>
                                                            <h4><?= $langage_lbl['LBL_TOTAL_EARNINGS_FRONT']; ?> (<?php echo $paymentMode; ?>)</h4>
                                                            <em><?= $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_EARNED_AMOUNT']]; ?></em>
                                                        <?php } else { ?>
                                                            <h4><?= $langage_lbl['LBL_Total_Fare']; ?> (<?php echo $paymentMode ?>)</h4>
                                                            <em><?= ($roundoff==1) ? $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT']] : $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_SUBTOTAL_TXT']]; ?></em><!-- //added by SP for rounding off currency wise on 26-8-2019  -->
                                                        <?php } ?>
                                                        <?php if ($organizationName != "") { ?><br><h4>Organization : <?php
                                                            echo $organizationName;
                                                        }
                                                        ?></h4>
                                                    </span>
                                                    <?php if (($db_trip_data['iActive'] == 'Finished' && $db_trip_data['eCancelled'] == "Yes") || ($db_trip_data['fCancellationFare'] > 0) || ($db_trip_data['iActive'] == 'Canceled' && $db_trip_data['fWalletDebit'] > 0)) {
                                                        ?>
                                                        <ul style="margin-top: 10px;">
                                                            <li style="border:dotted 2px #000000;background: none;">
                                                                <strong style="font-weight: bold;padding: 2px;" >
                                                                    <?
                                                                    if ($db_trip_data['eCancelledBy'] == 'Driver') {
                                                                        echo $langage_lbl['LBL_TRIP_CANCELLED_BY_DRIVER_ADMIN'];
                                                                        echo '<br/>';
                                                                        if (!empty($db_trip_data['vCancelReason'])) {
                                                                            echo $langage_lbl['LBL_CANCEL_REASON'] . ': ' . $db_trip_data['vCancelReason'];
                                                                        }
                                                                    } else if ($db_trip_data['eCancelledBy'] == 'Passenger') {
                                                                        echo $langage_lbl['LBL_TRIP_CANCELLED_BY_PASSANGER_ADMIN'];
                                                                        echo '<br/>';
                                                                        if (!empty($db_trip_data['vCancelReason'])) {
                                                                            echo $langage_lbl['LBL_CANCEL_REASON'] . ': ' . $db_trip_data['vCancelReason'];
                                                                        }
                                                                    } else {
                                                                        echo $langage_lbl['LBL_CANCELED_TRIP_ADMIN_TXT'];
                                                                    }
                                                                    ?>
                                                                </strong>
                                                            </li>
                                                        </ul>
                                                        <div style="clear:both;"></div>
                                                    <? } ?>	
                                                    <?php
                                                    if ($db_trip_data['fTipPrice'] != "" && $db_trip_data['fTipPrice'] != "0" && $db_trip_data['fTipPrice'] != "0.00") {
                                                        ?>
                                                        <ul><li><strong><?= $langage_lbl['LBL_TIP_RS_TXT']; ?></strong><b> <?= $db_trip_data['fTipPrice']; ?></b></li></ul>
                                                        <?
                                                    }
                                                    ?>
                                                    <div  style="clear:both;"></div><hr>
                                                    <?php
                                                    if (count($getAllTrip) > 0) {
                                                        $tableHaed = "Trips connected with this Pool Trip";
                                                        if (count($getAllTrip) == 1) {
                                                            $tableHaed = "Trip connected with this Pool Trip";
                                                        }
                                                        echo "<h4>" . $tableHaed . "</h4>";
                                                        ?>
                                                        <table border="1" width="100%">
                                                            <tr>
                                                                <th style="border: 1px solid #000000; width:20%;">#Sr No.</th>
                                                                <th style="border: 1px solid #000000; width:20%;">Booking No</th>
                                                                <th style="border: 1px solid #000000; width:20%;">Status</th>
                                                            </tr>
                                                            <?php
                                                            for ($t = 0; $t < count($getAllTrip); $t++) {
                                                                $link_page = "invoice.php";
                                                                $srNO = $t + 1;
                                                                ?>
                                                                <tr>
                                                                    <td width="10%"  style="border: 1px solid #000000;"><?= $srNO; ?></td>
                                                                    <td width="45%"  style="border: 1px solid #000000;"><a target="_blank" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($getAllTrip[$t]['iTripId'])) ?>"><strong><?= $getAllTrip[$t]['vRideNo']; ?></a></td>
                                                                    <td width="45%"  style="border: 1px solid #000000;"><?= $getAllTrip[$t]['iActive']; ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                        </table>
                                                    <?php } ?>
                                                    <div style="clear:both;"></div>
                                                    <?php if ($db_trip_data['eType'] == 'Deliver') { ?>
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
                                                                <?php
                                                            }
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
                                        <? if ($APP_DELIVERY_MODE == "Multi" && $db_trip_data['eType'] == 'Deliver') { ?>
                                            <div class="invoice-part-bottom invoice-part-bottom1">	
                                                <?php
                                                $sql1 = "SELECT * FROM trips_delivery_locations AS tdl WHERE iTripId = '" . $iTripId . "'";
                                                $db_trips_locations = $obj->MySQLSelect($sql1);
                                                $i = 1;
                                                if (!empty($db_trips_locations)) {
                                                    foreach ($db_trips_locations as $dtls) {
                                                        $class = (!empty($dtls['vSignImage'])) ? 'sign-img' : '';
                                                        ?>
                                                        <div class="col-sm-6 <?php echo $class; ?>"> 
                                                            <h3><?= $langage_lbl['LBL_RECIPIENT_LIST_TXT'] . '&nbsp;' . $i; ?></h3>
                                                            <table style="width:100%" class="deliverytable" cellpadding="5" cellspacing="0" border="0">
                                                                <tr>
                                                                    <td style="min-width: 150px;"><b><?= $langage_lbl['LBL_RECIPIENT_NAME_HEADER_TXT']; ?> </b></td>
                                                                    <td><?= $dtls['vReceiverName']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="min-width: 150px;"><b><?= $langage_lbl['LBL_DROP_OFF_LOCATION_RIDE_DETAIL']; ?> </b></td>
                                                                    <td><?= $dtls['tPickUpIns'] . "," . $dtls['tDaddress']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="min-width: 150px;"><b><?= $langage_lbl['LBL_DELIVERY_INS']; ?></b></td>
                                                                    <td><?= $dtls['tDeliveryIns']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="min-width: 150px;"><b><?= $langage_lbl['LBL_PACKAGE_DETAILS']; ?></b></td>
                                                                    <td><?= $dtls['tPackageDetails']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="min-width: 150px;"><b><?= $langage_lbl['LBL_DELIVERY_STATUS_TXT']; ?></b></td>
                                                                    <td><?= $dtls['iActive']; ?></td>
                                                                </tr>
                                                                <?php if (!empty($dtls['vSignImage'])) { ?>
                                                                    <tr>
                                                                        <td class="label_left"><b><?= $langage_lbl['LBL_RECEIVER_SIGN']; ?></b></td>
                                                                        <td class="detail_right">
                                                                            <?php
                                                                            if (file_exists($tconfig["tsite_upload_trip_signature_images_path"] . '/' . $dtls['vSignImage'])) {
                                                                                $img1 = $tconfig["tsite_upload_trip_signature_images"] . '/' . $dtls['vSignImage'];
                                                                            }
                                                                            ?>
                                                                            <img src="<?php echo $img1; ?>" align="left" style="width:150px;" >
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </table>
                                                        </div>
                                                        <?php
                                                        $i++;
                                                    }
                                                }
                                                ?>      				 
                                            </div>
                                        <? } ?>
                                    </div>
                                </div>
                                <?php
                            } else {
                                ?>
                                <div class="trip-detail-page">
                                    <div class="trip-detail-page-inner">
                                        <?= $langage_lbl['LBL_NO_INVOICE_FOUND_MSG']; ?>
                                    </div>
                                </div>
                            <?php } ?> 
                        </div>
                    </div>
                    
                    <?php include_once('footer/footer_home.php'); ?>
                    <div style="clear:both;"></div>
                </div>
                <!-- home page end-->
                <div  class="modal fade" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                    <div class="modal-dialog" >
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 id="servicetitle">
                                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                                    Service Details
                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                </h4>
                            </div>
                            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                                <div id="service_detail"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer Script -->
                <?php include_once('top/footer_script.php'); ?>
                <script src="assets/js/gmap3.js"></script>
                <script type="text/javascript">
                                                        var typeArr = '<?= json_encode($vehilceTypeArr, JSON_HEX_APOS); ?>';
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
                                                        <?php if(!empty($iTripId) || !empty($db_trip_data)) { ?>
                                                        $(document).ready(function () {
                                                            google.maps.event.addDomListener(window, 'load', initialize);
                                                        });
                                                    <?php } ?>
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
                                                        function showServiceModal(elem) {
                                                            var tripJson = JSON.parse($(elem).attr("data-json"));
                                                            var rideNo = $(elem).attr("data-trip");
                                                            var typeNameArr = JSON.parse(typeArr);
                                                            var serviceHtml = "";
                                                            var srno = 1;                                                            
                                                            for (var g = 0; g < tripJson.length; g++) {
                                                                serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?=$langage_lbl_admin['LBL_QTY_TXT']?>: <b>"+ [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
                                                                srno++;
                                                            }
                                                            $("#service_detail").html(serviceHtml);
                                                            $("#servicetitle").text("Service Details : " + rideNo);
                                                            $("#service_modal").modal('show');
                                                            return false;
                                                        }
                </script>
                <!-- End: Footer Script -->
                </body>
                </html>