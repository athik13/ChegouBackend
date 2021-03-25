<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if (!$userObj->hasPermission('manage-create-request')) {
    $userObj->redirect();
}

//if(($generalobj->checkCubexThemOn() == 'Yes' || $generalobj->checkCubeJekXThemOn() == 'Yes') && ENABLE_EXTENDED_VERSION_MANUAL_BOOKING == 'Yes') {
if(manual_booking_extended_version()) {
    header("location: ../adminbooking");
    exit;
}

//$APP_DELIVERY_MODE = $generalobj->getConfigurations("configurations", "APP_DELIVERY_MODE");
//$ENABLE_TOLL_COST = $generalobj->getConfigurations("configurations", "ENABLE_TOLL_COST");
//$TOLL_COST_APP_ID = $generalobj->getConfigurations("configurations", "TOLL_COST_APP_ID");
//$TOLL_COST_APP_CODE = $generalobj->getConfigurations("configurations", "TOLL_COST_APP_CODE");
$getConfigData = $obj->MySQLSelect("SELECT vValue,vName FROM configurations WHERE vName='APP_DELIVERY_MODE' OR vName='ENABLE_TOLL_COST' OR vName='TOLL_COST_APP_ID' OR vName='TOLL_COST_APP_CODE'");
//echo "<pre>";print_r($getConfigData);die;
$APP_DELIVERY_MODE = "Multi";
$ENABLE_TOLL_COST = "No";
$TOLL_COST_APP_ID = $TOLL_COST_APP_CODE = "";
for ($r = 0; $r < count($getConfigData); $r++) {
    if ($getConfigData[$r]['vName'] == "APP_DELIVERY_MODE") {
        $APP_DELIVERY_MODE = $getConfigData[$r]['vValue'];
    }
    if ($getConfigData[$r]['vName'] == "ENABLE_TOLL_COST") {
        $ENABLE_TOLL_COST = $getConfigData[$r]['vValue'];
    }
    if ($getConfigData[$r]['vName'] == "TOLL_COST_APP_ID") {
        $TOLL_COST_APP_ID = $getConfigData[$r]['vValue'];
    }
    if ($getConfigData[$r]['vName'] == "TOLL_COST_APP_CODE") {
        $TOLL_COST_APP_CODE = $getConfigData[$r]['vValue'];
    }
}

function converToTz($time, $toTz, $fromTz, $dateFormat = "Y-m-d H:i:s") {
    $date = new DateTime($time, new DateTimeZone($fromTz));
    $date->setTimezone(new DateTimeZone($toTz));
    $time = $date->format($dateFormat);
    return $time;
}

$script = "booking";
$tbl_name = 'cab_booking';

$iAdminId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
$iHotelBookingId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
$iGroupId = isset($_SESSION['sess_iGroupId']) ? $_SESSION['sess_iGroupId'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$iCabBookingId = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : '';
$action = ($iCabBookingId != '') ? 'Edit' : 'Add';

//For Country
$sql1 = "SELECT vCountryCode,vCountry from country where eStatus = 'Active'";
$db_countryData = $obj->MySQLSelect($sql1);

$sql = "SELECT c.vCountryCode,c.vCountryCode,c.vCountry,c.vTimeZone,c.vPhoneCode,a.vAddress,a.vAddressLat,a.vAddressLong FROM administrators as a LEFT JOIN country as c on c.vCountryCode=a.vCountry WHERE c.eStatus = 'Active' AND a.iGroupId = '" . $iGroupId . "' AND a.iAdminId = '" . $iAdminId . "'";
$db_code = $obj->MySQLSelect($sql);
$vPhoneCode = $generalobjAdmin->clearPhone($db_code[0]['vPhoneCode']);
$vRideCountry = isset($_REQUEST['vRideCountry']) ? $_REQUEST['vRideCountry'] : $db_code[0]['vCountryCode'];
$vTimeZone = isset($_REQUEST['vTimeZone']) ? $_REQUEST['vTimeZone'] : $db_code[0]['vTimeZone'];
$vCountry = $db_code[0]['vCountryCode'];
$address = $db_code[0]['vCountry']; // Google HQ
$vSourceAddresss = $db_code[0]['vAddress'];
$from_lat_long = '(' . $db_code[0]['vAddressLat'] . ', ' . $db_code[0]['vAddressLong'] . ')';
$from_lat = $db_code[0]['vAddressLat'];
$from_long = $db_code[0]['vAddressLong'];

if (count($db_code) < 0) {
    $sql = "select cn.vCountryCode,cn.vCountry,cn.vPhoneCode,cn.vTimeZone from country cn inner join configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
    $db_con = $obj->MySQLSelect($sql);
    $vPhoneCode = $generalobjAdmin->clearPhone($db_con[0]['vPhoneCode']);
    $vRideCountry = isset($_REQUEST['vRideCountry']) ? $_REQUEST['vRideCountry'] : $db_code[0]['vCountryCode'];
    $vTimeZone = isset($_REQUEST['vTimeZone']) ? $_REQUEST['vTimeZone'] : $db_code[0]['vTimeZone'];
    $vCountry = $db_con[0]['vCountryCode'];
    $address = $db_con[0]['vCountry']; // Google HQ
}
$vCountry_rider = $vCountry;
$prepAddr = str_replace(' ', '+', $address);
/* $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
  $output= json_decode($geocode);
  $latitude = $output->results[0]->geometry->location->lat;
  $longitude = $output->results[0]->geometry->location->lng; */

$dBooking_date = "";
//$ssql = "SELECT SUM(CASE WHEN rd.eStatus = 'Active' THEN 1 ELSE 0 END) allr,rd.vLatitude,rd.vLongitude FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' AND rd.eStatus='Active'";
$db_records = $obj->MySQLSelect("SELECT rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS fullname ,rd.vEmail,rd.iCompanyId, rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline, rd.vImage, rd.vCode, rd.vPhone, dv.vCarType,rd.tLocationUpdateDate FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' AND rd.eStatus='Active' AND (rd.vAvailability = 'Not Available' OR rd.vAvailability = 'Available') AND (rd.vTripStatus = 'Not Active' OR rd.vTripStatus = 'Cancelled' OR rd.vTripStatus = 'Finished')");
//$db_records = $obj->MySQLSelect($ssql);
$arrived = $active = $ontrip = $all = $available = 0;
$all = count($db_records);

function fetchtripstatustimeMAXinterval() {
    global $generalobjAdmin, $FETCH_TRIP_STATUS_TIME_INTERVAL;
    $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL);
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
    return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
}

$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$ssql = "SELECT  count(rd.iDriverId) as available FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' AND rd.vAvailability = 'Available' AND rd.vTripStatus != 'Active' AND rd.tLocationUpdateDate > '$str_date' AND rd.eStatus='Active'";
$db_record = $obj->MySQLSelect($ssql);
if (count($db_record) > 0) {
    $available = $db_record[0]['available'];
}
$available =0;
/* $sql1 = "SELECT * FROM `package_type` WHERE eStatus='Active'";
  $db_PackageType = $obj->MySQLSelect($sql1); */

if ($action == 'Edit') {
    $sql = "SELECT $tbl_name.*,$tbl_name.fNightPrice as NightSurge,$tbl_name.fPickUpPrice as PickSurge,
	register_user.vPhone,register_user.vName,register_user.vLastName,register_user.vEmail,register_user.vPhoneCode,register_user.vCountry FROM " . $tbl_name . " LEFT JOIN register_user on register_user.iUserId=" . $tbl_name . ".iUserId WHERE " . $tbl_name . ".iCabBookingId = '" . $iCabBookingId . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;
    $systemTimeZone = date_default_timezone_get();
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iUserId = $value['iUserId'];
            $iDriverId = $value['iDriverId'];
            $vDistance = $value['vDistance'];
            $vDuration = $value['vDuration'];
            $dBookingDate = $value['dBooking_date'];
            $vSourceAddresss = $value['vSourceAddresss'];
            $tDestAddress = $value['tDestAddress'];
            $iVehicleTypeId = $value['iVehicleTypeId'];
            $vPhone = $generalobjAdmin->clearPhone($value['vPhone']);
            $vName = $value['vName'];
            $vLastName = $generalobjAdmin->clearName(" " . $value['vLastName']);
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
            $vPhoneCode = $generalobjAdmin->clearPhone($value['vPhoneCode']);
            $vCountry_rider = $value['vCountry'];
            $iPackageTypeId = $value['iPackageTypeId'];
            $tPackageDetails = $value['tPackageDetails'];
            $tDeliveryIns = $value['tDeliveryIns'];
            $tPickUpIns = $value['tPickUpIns'];
            $vReceiverName = $value['vReceiverName'];
            $vReceiverMobile = $value['vReceiverMobile'];
            $eStatus = $value['eStatus'];
            $from_lat_long = '(' . $value['vSourceLatitude'] . ', ' . $value['vSourceLongitude'] . ')';
            $from_lat = $value['vSourceLatitude'];
            $from_long = $value['vSourceLongitude'];
            $to_lat_long = '(' . $value['vDestLatitude'] . ', ' . $value['vDestLongitude'] . ')';
            $to_lat = $value['vDestLatitude'];
            $to_long = $value['vDestLongitude'];
            $eAutoAssign = $value['eAutoAssign'];
            $fPickUpPrice = $value['PickSurge'];
            $fNightPrice = $value['NightSurge'];
            $vRideCountry = $value['vRideCountry'];
            $vTimeZone = $value['vTimeZone'];
            $eFemaleDriverRequest = $value['eFemaleDriverRequest'];
            $eHandiCapAccessibility = $value['eHandiCapAccessibility'];
            $etype = $value['eType'];
            $eFlatTrip = $value['eFlatTrip'];
            $fFlatTripPrice = $value['fFlatTripPrice'];
            $eTollSkipped = $value['eTollSkipped'];
            $fTollPrice = $value['fTollPrice'];
            $vTollPriceCurrencyCode = $value['vTollPriceCurrencyCode'];
            $dBooking_date = converToTz($dBookingDate, $vTimeZone, $systemTimeZone);
            $vRiderRoomNubmer = $value['vRiderRoomNubmer'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | Create Request</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link rel="stylesheet" href="css/select2/select2.min.css" type="text/css" >
        <?php include_once('global_files.php'); ?>
        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
        <script type='text/javascript' src='../assets/map/gmaps.js'></script>
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <script type='text/javascript' src='../assets/js/bootbox.min.js'></script>
    </head>
    <style>
        .active.available {
            background: #78AC2C;
            color: #fff;
        }
    </style>
    <body class="padTop53">
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <div id="content">
                <div class="inner" style="min-height: 700px;">
                    <div class="row">
                        <div class="col-lg-8">
                            <h1> Create Request </h1>
                        </div>
                    </div>
                    <hr />
                    <form name="add_booking_form" id="add_booking_form" method="post" action="" >
                        <!-- hotel_booking.php -->
                        <div class="form-group" style="display: inline-block;">
                            <?php if ($success == "1") { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo ($vassign != "1") ? 'Booking has been added successfully.' : $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ' has been assigned successfully.';
                                    ?>
                                </div>
                                <br/>
                            <?php } ?>
                            <?php if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div>
                                <br/>
                            <?php } ?>
                            <?php if ($success == 0 && $var_msg != "") { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?= $var_msg; ?>
                                </div>
                                <br/>
                            <?php } ?>
                            <input type="hidden" name="iAdminId" id="iAdminId" value="<?= $iAdminId; ?>" />
                            <input type="hidden" name="iHotelBookingId" id="iHotelBookingId" value="<?= $iHotelBookingId; ?>" />
                            <input type="hidden" name="previousLink" id="previousLink" value=""/>
                            <input type="hidden" name="backlink" id="backlink" value="cab_booking.php"/>
                            <input type="hidden" name="distance" id="distance" value="<?= $vDistance; ?>">
                            <input type="hidden" name="duration" id="duration" value="<?= $vDuration; ?>">

                            <input type="hidden" name="from_lat_long" id="from_lat_long" value="<?= $from_lat_long; ?>" >
                            <input type="hidden" name="from_lat" id="from_lat" value="<?= $from_lat; ?>" >
                            <input type="hidden" name="from_long" id="from_long" value="<?= $from_long; ?>" >

                            <input type="hidden" name="to_lat_long" id="to_lat_long" value="<?= $to_lat_long; ?>" >
                            <input type="hidden" name="to_lat" id="to_lat" value="<?= $to_lat; ?>" >
                            <input type="hidden" name="to_long" id="to_long" value="<?= $to_long; ?>" >

                            <input type="hidden" name="fNightPrice" id="fNightPrice" value="<?= $fNightPrice; ?>" >
                            <input type="hidden" name="fPickUpPrice" id="fPickUpPrice" value="<?= $fPickUpPrice; ?>" >
                            <input type="hidden" name="eFlatTrip" id="eFlatTrip" value="<?= $eFlatTrip; ?>" >
                            <input type="hidden" name="fFlatTripPrice" id="fFlatTripPrice" value="<?= $fFlatTripPrice; ?>" >
                            <input type="hidden" value="1" id="location_found" name="location_found">
                            <input type="hidden" value="" id="user_type" name="user_type" >
                            <input type="hidden" value="<?= $iUserId; ?>" id="iUserId" name="iUserId" >
                            <input type="hidden" value="<?= $eStatus; ?>" id="eStatus" name="eStatus" >
                            <input type="hidden" value="<?= $vTimeZone; ?>" id="vTimeZone" name="vTimeZone" >
                            <input type="hidden" value="<?= $vRideCountry; ?>" id="vRideCountry" name="vRideCountry" >
                            <input type="hidden" value="<?= $iCabBookingId; ?>" id="iCabBookingId" name="iCabBookingId" >
                            <input type="hidden" value="<?= $GOOGLE_SEVER_API_KEY_WEB; ?>" id="google_server_key" name="google_server_key" >
                            <input type="hidden" value="" id="getradius" name="getradius" >
                            <input type="hidden" value="KMs" id="eUnit" name="eUnit" >
                            <input type="hidden" name="fTollPrice" id="fTollPrice" value="<?= $fTollPrice ?>">
                            <input type="hidden" name="vTollPriceCurrencyCode" id="vTollPriceCurrencyCode" value="<?= $vTollPriceCurrencyCode ?>">
                            <input type="hidden" name="eTollSkipped" id="eTollSkipped" value="<?= $eTollSkipped ?>">
                            <input type="hidden" name="eType" value="Ride">
                            <!-- <?php if ($APP_TYPE != 'Ride-Delivery' && $APP_TYPE != 'Ride-Delivery-UberX' || ($APP_TYPE == 'Ride-Delivery' && $APP_DELIVERY_MODE == "Multi")) { ?>
                                                                                                                            <input type="hidden" value="<?= $etype ?>" id="eType" name="eType" />
                            <?php } ?> -->

                            <input type="hidden" name="vCountry"  id="vCountry" value="<?= $vCountry_rider ?>">
                            <input type="hidden" name="eBookingFrom" id="eBookingFrom" value="Hotel" />

                            <div class="add-booking-form-taxi add-booking-form-taxi1 col-lg-12">
                                <span class="col0">
                                    <select name="vCountry" id="vCountry" class="form-control form-control-select" onChange="changeCode(this.value);" required>
                                        <!-- <option value="">Select Country</option> -->
                                        <? for ($i = 0; $i < count($db_countryData); $i++) { ?>
                                            <option value="<?= $db_countryData[$i]['vCountryCode'] ?>" 
                                            <?php
                                            if ($db_countryData[$i]['vCountryCode'] == $vCountry_rider) {
                                                echo "selected";
                                            }
                                            ?> >
                                                        <?= $db_countryData[$i]['vCountry']; ?>
                                            </option>
                                        <? } ?>
                                    </select>
                                </span>
                                <span class="col6">
                                    <input type="text" class="form-control add-book-input" name="vPhoneCode" id="vPhoneCode" value="<?= $vPhoneCode; ?>" readonly />
                                </span>
                                <span class="col2">
                                    <input type="text" pattern="[0-9]{1,}" title="Enter Mobile Number." class="form-control add-book-input" name="vPhone"  id="vPhone" value="<?= $vPhone; ?>" placeholder="Enter Phone Number" onKeyUp="return isNumberKey(event)"  onblur="return isNumberKey(event)"  required/>
                                </span> 
                                <span class="col3">
                                    <input type="text" class="form-control first-name1" name="vName"  id="vName" value="<?= $vName; ?>" placeholder="First Name" required />
                                    <input type="text" class="form-control last-name1" name="vLastName"  id="vLastName" value="<?= $vLastName; ?>" placeholder="Last Name" required />
                                </span> 
                                <span class="col4" style="margin: 0px;">
                                    <input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" class="form-control" name="vEmail" id="vEmail" value="<?= $vEmail; ?>" placeholder="Email" required>
                                    <div id="emailCheck"></div>
                                </span>

                                <span class="col0">
                                    <input type="text" class="form-control" name="vRiderRoomNubmer" id="vRiderRoomNubmer" value="<?= $vRiderRoomNubmer; ?>" placeholder="Room Number" required >
                                </span>

                            </div>
                            <div class="add-booking-form-taxi add-booking-form-taxi1 col-lg-12">


                            </div>

                            <div class="map-main-page-inner">
                                <div class="float-left">
                                    <div class="map-main-page-inner-tab-new">
                                        <div class="col-lg-12 map-live-hs-mid">
                                            <span class="col5">
                                                <input type="text" class="ride-location1 highalert txt_active form-control first-name1" name="vSourceAddresss"  id="from" value="<?= $vSourceAddresss; ?>" placeholder="<?= ucfirst(strtolower($langage_lbl_admin['LBL_PICKUP_LOCATION_HEADER_TXT'])); ?>" required onpaste="checkrestrictionfrom('from');" readonly="readonly" style="pointer-events:none;background-color:#eeeeee">

                                                <?php if ($APP_TYPE != "UberX") { ?>
							<div class="map_to_createrequest">
								<input type="text" class="ride-location1 highalert txt_active form-control last-name1" name="tDestAddress"  id="to" value="<?= $tDestAddress; ?>" placeholder="Drop Off Location" required onpaste="checkrestrictionto('to');">
							</div>
                                                <?php } ?>
                                            </span>
                                            <input type="hidden" name="dBooking_date" id="dBooking_date" value="" />
                                            <span>
                                                <select class="form-control form-control-select form-control14" name='iVehicleTypeId' id="iVehicleTypeId" required onChange="showAsVehicleType(this.value)">
                                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></option>
                                                </select>
                                            </span>
                                        </div>
                                        <?php if ($APP_TYPE != 'UberX') { ?>
                                            <div class="total-price">
                                                <div class="padding10">
                                                    <b>Fare Estimation</b>
                                                    <hr>
                                                    <ul id="estimatedata">

                                                    </ul>
                                                </div>
                                                <span>
                                                    Total Fare<b>
                                                        <em id="total_fare_price">0</em></b></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="book-now-reset add-booking-button">
                                        <span>
                                            <input type="submit" class="save button-submit finalsubmitbutton" name="submitbutton" id="submitbutton" value="Book Later">
                                            <?php if (count($db_data) == 0) { ?>
                                                <input type="submit" class="save button-submit finalsubmitbutton" name="submitbuttonNow" id="submitbuttonNow" value="Book Now">
                                            <?php } ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="map-page">

                                    <div class="map-color-code">
                                        <ul><li>
                                                <button type="button" id="availabledriver" class="btn btn-default col-sm-12 status_filter active available"  onClick="activeTab('Available')"> <img src="../assets/img/green.png"> <?= $langage_lbl['LBL_AVAILABLE']; ?>&nbsp;(<?php echo $available; ?>) </button>
                                            </li>
                                            <li>
                                                <button type="button" type="button" id="alldriver" class="btn btn-default col-sm-12 status_filter all" onClick="activeTab('')"><?php echo $langage_lbl['LBL_ALL']; ?>&nbsp;(<?php echo $all; ?>)</button>
                                            </li>
                                        </ul>
                                    </div>


                                    <div class="panel-heading location-map" style="background:none;">
                                        <div class="google-map-wrap">
                                            <div id="map-canvas" class="google-map" style="width:100%; height:500px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="newType" id="newType" value="">
                            <input type="hidden" name="submitbuttonvalue" id="submitbtnvalue" value="">
                            <div style="clear:both;"></div>
                    </form>

                    <div class="admin-notes">
                        <!-- <h4>Notes:</h4> -->
                        <!-- <ul>
                                <li>
                                        Administrator can Add / Edit <?php echo $langage_lbl['LBL_RIDER_RIDE_MAIN_SCREEN']; ?> later booking on this page.
                                </li>
                                <li>
                        <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> current availability is not connected with booking being made. Please confirm future avaialbility of <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> before doing booking.
                                </li>
                                <li>Adding booking from here will not send request to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> immediately.</li> 
                                <li>In case of "Auto Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" option selected, <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>(s) get automatic request before 8-12 minutes of actual booking time.</li>
                                <li>In case of "Auto Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" option not selected, <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>(s) get booking confirmation sms as well as reminder sms before 30 minutes of actual booking. <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> has to start the scheduled <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> by going to "Your <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?>" >> Upcoming section from <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> App.</li>
                                <li>In case of "Auto Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>", the competitive algorithm will be followed instead of one you have selected in settings.</li>
                        </ul> -->
                    </div>	
                </div>
                <!--END PAGE CONTENT -->

            </div>
            <? include_once('footer.php'); ?>
            <div style="clear:both;"></div>

            <!--Wallet Low Balance-->
            <div class="modal fade" id="usermodel" tabindex="-1" role="dialog" aria-labelledby="usermodel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                            <h4 class="modal-title" id="inactiveModalLabel">Low Wallet Balance </h4>
                        </div>
                        <div class="modal-body">
                            <p><span style="font-size: 15px;"> This <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> is having low balance in his wallet and is not able to accept cash ride. Would you still like to assign this <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>?</span></p>
                            <p><b style="font-size: 15px;"> Minimum Required Balance : </b><span style="font-size: 15px;"><?php echo $generalobj->symbol_currency() . " " . number_format($WALLET_MIN_BALANCE, 2); ?></span></p>
                            <p><b style="font-size: 15px;"> Available Balance : </b><span style="font-size: 15px;"><?php echo $generalobj->symbol_currency(); ?> <span id="usr-bal"></span></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button> 
                            <button type="button" class="btn btn-success btn-ok action_modal_submit" data-dismiss="modal" onClick="AssignDriver('');">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end Wallet Low Balance-->

            <!--user inactive/deleted-->
            <div class="modal fade" id="inactiveModal" tabindex="-1" role="dialog" aria-labelledby="inactiveModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                            <h4 class="modal-title" id="inactiveModalLabel"><?php echo $langage_lbl_admin['LBL_RIDER']; ?> Detail</h4>
                        </div>
                        <div class="modal-body">
                            <span style="font-size: 15px;"> <?php echo $langage_lbl_admin['LBL_RIDER']; ?> is inactive/deleted. Do you want to book a ride with <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>?</span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Continue</button>
                            <!-- <button type="button" class="btn btn-primary">Continue</button> -->
                        </div>
                    </div>
                </div>
            </div>
            <!--end user inactive/deleted-->

            <!--surcharge confirmation-->
            <div class="modal fade" id="surgemodel" tabindex="-1" role="dialog" aria-labelledby="surgemodel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                            <h4 class="modal-title" id="inactiveModalLabel">Confirm Surcharge</h4>
                        </div>
                        <div class="modal-body">
                            <p><span style="font-size: 15px;"> This trip is comes under the surcharge timing.surcharge will be applied as per below.</span></p>
                            <table style="font-size: 15px;" cellspacing="5" cellpadding="5">
                                <tr>
                                    <td width="100px"> <b>Surge Type </b></td>
                                    <td> : <span id="surge_type"></span> Surcharge</td>
                                </tr>
                                <tr>
                                    <td><b>Surge Factor</b></td>
                                    <td> : <span id="surge_factor"></span> X</td>
                                </tr>
                                <tr>
                                    <td><b>Surge Timing</b></td>
                                    <td> : <span id="surge_timing"></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button> -->
                            <button type="button" class="btn btn-success btn-ok action_modal_submit" data-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end surcharge confirmation-->

            <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
            <script type="text/javascript" src="js/moment.min.js"></script>
            <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
            <script type="text/javascript" src="js/plugins/select2.min.js"></script>
            <script>
                                var eType = "";
                                var APP_DELIVERY_MODE = '<?= $APP_DELIVERY_MODE ?>';
                                var ENABLE_TOLL_COST = "<?= $ENABLE_TOLL_COST ?>";
                                switch ("<?php echo $APP_TYPE; ?>") {
                                    case "Ride-Delivery":
                                        if (APP_DELIVERY_MODE == "Multi") {
                                            eType = 'Ride';
                                        } else {
                                            eType = $('input[name=eType]:checked').val();
                                        }
                                        break;
                                    case "Ride-Delivery-UberX":
                                        if (APP_DELIVERY_MODE == "Multi") {
                                            eType = 'Ride';
                                        } else {
                                            eType = $('input[name=eType]:checked').val();
                                        }
                                        break;
                                    case "Delivery":
                                        eType = 'Deliver';
                                        break;

                                    case "UberX":
                                        eType = 'UberX';
                                        break;

                                    default:
                                        eType = 'Ride';
                                }

                                function show_type(etype) {
                                    if (etype == 'Ride') {
                                        $('#ride-delivery-type').hide();
                                        $('#ride-type').show();
                                        $('.auto_assign001').show();
                                        $('#iPackageTypeId').removeAttr('required');
                                        $('#vReceiverMobile').removeAttr('required');
                                        $('#vReceiverName').removeAttr('required');
                                        $('#to').show();
                                        $('.total-price1').show();
                                    } else if (etype == 'Deliver') {
                                        $('#ride-delivery-type').show();
                                        $('#ride-type').hide();
                                        $('.auto_assign001').show();
                                        $('#iPackageTypeId').attr('required', 'required');
                                        $('#vReceiverMobile').attr('required', 'required');
                                        $('#vReceiverName').attr('required', 'required');
                                        $('#to').show();
                                        $('.total-price1').show();
                                    } else if (etype == 'UberX') {
                                        $('#ride-delivery-type').hide();
                                        $('#to').hide();
                                        $('#ride-type').hide();
                                        $('.auto_assign001').hide();
                                        $('#iPackageTypeId').removeAttr('required');
                                        $('#to').removeAttr('required');
                                        $('#vReceiverMobile').removeAttr('required');
                                        $('#vReceiverName').removeAttr('required');
                                        $('#to').removeAttr('required');
                                        $('.total-price1').hide();
                                    }
                                }
                                function activeTab(type) {
                                    $("#newType").val(type);
                                    var option = $("#option").val();
                                    if (type == 'Active') {
                                        title = 'Enroute to Pickup';
                                        classname = 'enroute';
                                    } else if (type == 'Arrived') {
                                        title = 'Reached Pickup';
                                        classname = 'reached';
                                    } else if (type == 'On Going Trip') {
                                        title = 'Journey Started';
                                        classname = 'tripstart';
                                    } else if (type == 'Available') {
                                        title = 'Available';
                                        classname = 'available';
                                    } else {
                                        title = 'All';
                                        classname = 'all';
                                    }
                                    $('.status_filter').removeClass('active');
                                    $('.status_filter.' + classname).addClass('active');
                                    $('.list_title').removeClass('enroute reached tripstart available').addClass(classname);
                                    $('.list_title').html(title);
                                    //setFilter('type', type, false);
                                    //setFilter('keyword', "", false);
                                    setDriversMarkers('live', type);
                                    if (type == 'Available')routeDirections();
                                }
                                function formatData(state) {
                                    if (!state.id) {
                                        return state.text;
                                    }
                                    var optimage = $(state.element).data('id');
                                    if (!optimage) {
                                        return state.text;
                                    } else {
                                        var $state = $(
                                                '<span class="userName"><img src="' + optimage + '" class="mpLocPic" /> ' + $(state.element).text() + '</span>'
                                                );
                                        return $state;
                                    }
                                }

                                $("#newSelect02").select2({
                                    templateResult: formatData,
                                    templateSelection: formatData
                                });
                                var eFlatTrip = 'No';
                                var eTypeQ11 = 'yes';
                                var map;
                                //var geocoder;
                                var circle;
                                var markers = [];
                                var driverMarkers = [];
                                var bounds = [];
                                var newLocations = "";
                                var autocomplete_from;
                                var autocomplete_to;
                                var eLadiesRide = 'No';
                                var eHandicaps = 'No';
                                //var geocoder = new google.maps.Geocoder();
                                var directionsService = new google.maps.DirectionsService(); // For Route Services on map
                                var directionsOptions = {// For Polyline Route line options on map
                                    polylineOptions: {
                                        strokeColor: '#FF7E00',
                                        strokeWeight: 5
                                    }
                                };
                                var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
                                var showsurgemodal = "Yes";

                                // use for marker in map			
                                function setDriversMarkers(flag, filterType) {
                                    newType = $("#newType").val();
                                    vType = $("#iVehicleTypeId").val();
                                    if ($("#eFemaleDriverRequest").is(":checked")) {
                                        eLadiesRide = 'Yes';
                                    } else {
                                        eLadiesRide = 'No';
                                    }
                                    if ($("#eHandiCapAccessibility").is(":checked")) {
                                        eHandicaps = 'Yes';
                                    } else {
                                        eHandicaps = 'No';
                                    }
                                    $.ajax({
                                        type: "POST",
                                        url: "get_map_drivers_list.php",
                                        dataType: "json",
                                        data: {type: newType, iVehicleTypeId: vType, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, type: filterType, createRequest: 'Yes'},
                                        success: function (dataHtml) {
                                            for (var i = 0; i < driverMarkers.length; i++) {
                                                driverMarkers[i].setMap(null);
                                            }
                                            newLocations = dataHtml.locations;
                                            var infowindow = new google.maps.InfoWindow();
                                            if (filterType == "") {
                                                $("#alldriver").text('<?= $langage_lbl['LBL_ALL']; ?>(' + newLocations.length + ')');
                                            }else{
                                                $("#availabledriver").text('<?= $langage_lbl['LBL_AVAILABLE']; ?>(' + newLocations.length + ')');
                                            }
                                            for (var i = 0; i < newLocations.length; i++) {
                                                if (newType == newLocations[i].location_type || newType == "") {
                                                    var str33 = newLocations[i].location_carType;
                                                    if (vType == "" || (str33 != null && str33.indexOf(vType) != -1)) {
                                                        newName = newLocations[i].location_name;
                                                        newOnlineSt = newLocations[i].location_online_status;
                                                        newLat = newLocations[i].google_map.lat;
                                                        newLong = newLocations[i].google_map.lng;
                                                        newDriverImg = newLocations[i].location_image;
                                                        newMobile = newLocations[i].location_mobile;
                                                        newDriverID = newLocations[i].location_ID;
                                                        newImg = newLocations[i].location_icon;
                                                        driverId = newLocations[i].location_driverId;
                                                        latlng = new google.maps.LatLng(newLat, newLong);
                                                        // bounds.push(latlng);
                                                        content = '<table><tr><td rowspan="4"><img src="' + newDriverImg + '" height="60" width="60"></td></tr><tr><td>&nbsp;&nbsp;Email: </td><td><b>' + newDriverID + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>';

                                                        var drivermarker = new google.maps.Marker({
                                                            map: map,
                                                            //animation: google.maps.Animation.DROP,
                                                            position: latlng,
                                                            icon: newImg
                                                        });
                                                        google.maps.event.addListener(drivermarker, 'click', (function (drivermarker, content, infowindow) {
                                                            return function () {
                                                                infowindow.setContent(content);
                                                                infowindow.open(map, drivermarker);
                                                            };
                                                        })(drivermarker, content, infowindow));
                                                        // alert(content);
                                                        driverMarkers.push(drivermarker);
                                                    }
                                                }
                                            }
                                            //var markers = [];//some array
                                            if (flag != 'test') {
                                                var bounds = new google.maps.LatLngBounds();
                                                for (var i = 0; i < driverMarkers.length; i++) {
                                                    bounds.extend(driverMarkers[i].getPosition());
                                                }
                                                //console.log(bounds);
                                                map.fitBounds(bounds);
                                                map.setZoom(13);
                                            }
                                            //setDriverListing(vType);
                                        },
                                        error: function (dataHtml) {

                                        }
                                    });
                                }

                                // use this function
                                function initialize() {
                                    var thePoint = new google.maps.LatLng('20.1849963', '64.4125062');
                                    var mapOptions = {
                                        zoom: 4,
                                        center: thePoint
                                    };
                                    map = new google.maps.Map(document.getElementById('map-canvas'),
                                            mapOptions);

                                    circle = new google.maps.Circle({radius: 25, center: thePoint});
                                    if (eType == "Deliver") {
                                        show_type(eType);
                                    }
                                    showVehicleCountryVise('<?php echo $vCountry ?>', '<?php echo $iVehicleTypeId; ?>', eType);

<?php if ($action == "Edit") { ?>
                                        callEditFundtion();
<?php } ?>

                                    setDriversMarkers('test', 'Available');

<?php if ($from_lat_long != '') { ?>
                                        show_locations();
<?php } ?>
                                }

                                $(document).ready(function () {
                                    google.maps.event.addDomListener(window, 'load', initialize);
                                    $("#eType").val(eType);
                                    $('input[type=radio][name=eType]').change(function () {
                                        eType = $('input[name=eType]:checked').val();
                                    });

                                });

                                function getAddress(mDlatitude, mDlongitude, addId) {
                                    var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);
                                    geocoder.geocode({'latLng': mylatlang},
                                            function (results, status) {
                                                if (status == google.maps.GeocoderStatus.OK) {
                                                    if (results[0]) {
                                                        $('#' + addId).val(results[0].formatted_address);
                                                    } else {
                                                        document.getElementById('#' + addId).value = "No results";
                                                    }
                                                } else {
                                                    document.getElementById('#' + addId).value = status;
                                                }
                                            });
                                }

                                function DeleteMarkers(newId) {
                                    // Loop through all the markers and remove
                                    for (var i = 0; i < markers.length; i++) {
                                        if (newId != '') {
                                            if (markers[i].id == newId) {
                                                markers[i].setMap(null);
                                            }
                                        } else {
                                            markers[i].setMap(null);
                                        }
                                    }
                                    if (newId == '') {
                                        markers = [];
                                    }
                                }
                                ;

                                function setMarker(postitions, valIcon) {
                                    var newIcon;
                                    if (valIcon == 'from_loc') {
                                        if (eType == 'UberX') {
                                            newIcon = '../webimages/upload/mapmarker/PinTo.png';
                                        } else {
                                            newIcon = '../webimages/upload/mapmarker/PinFrom.png';
                                        }
                                    } else if (valIcon == 'to_loc') {
                                        newIcon = '../webimages/upload/mapmarker/PinTo.png';
                                    } else {
                                        newIcon = '../webimages/upload/mapmarker/PinTo.png';
                                    }
                                    var marker = new google.maps.Marker({
                                        map: map,
                                        draggable: false,
                                        animation: google.maps.Animation.DROP,
                                        position: postitions,
                                        icon: newIcon
                                    });
                                    marker.id = valIcon;
                                    markers.push(marker);
                                    map.setCenter(marker.getPosition());
                                    map.setZoom(15);

                                    if (valIcon == "from_loc") {
                                        marker.addListener('dragend', function (event) {
                                            // console.log(event);
                                            // var lat = event.latLng.lat();
                                            // var lng = event.latLng.lng();
                                            // var myLatlongs = new google.maps.LatLng(lat, lng);
                                            // showsurgemodal = "No";
                                            // $("#from_lat").val(lat);
                                            // $("#from_long").val(lng);
                                            // $("#from_lat_long").val(myLatlongs);
                                            // getAddress(lat, lng, 'from');
                                            // routeDirections();
                                            var lat = event.latLng.lat();
                                            var lng = event.latLng.lng();
                                            var myLatlongs = new google.maps.LatLng(lat, lng);
                                            showsurgemodal = "No";
                                            var from_latold = $("#from_lat").val();
                                            var from_longold = $("#from_long").val();
                                            var from_lat_longold = $("#from_lat_long").val();
                                            var from_addressold = $("#from").val();
                                            $("#from_lat").val(lat);
                                            $("#from_long").val(lng);
                                            $("#from_lat_long").val(myLatlongs);
                                            getAddress(lat, lng, 'from','from',from_latold,from_longold,from_lat_longold,from_addressold);
                                            routeDirections();
                                        });
                                    }
                                    if (valIcon == 'to_loc') {
                                        marker.addListener('dragend', function (event) {
                                            // var lat = event.latLng.lat();
                                            // var lng = event.latLng.lng();
                                            // var myLatlongs1 = new google.maps.LatLng(lat, lng);
                                            // showsurgemodal = "No";
                                            // $("#to_lat").val(lat);
                                            // $("#to_long").val(lng);
                                            // $("#to_lat_long").val(myLatlongs1);
                                            // getAddress(lat, lng, 'to');
                                            // routeDirections();
                                            var lat = event.latLng.lat();
                                            var lng = event.latLng.lng();
                                            var myLatlongs1 = new google.maps.LatLng(lat, lng);
                                            showsurgemodal = "No";

                                            var to_latold = $("#to_lat").val();
                                            var to_longold = $("#to_long").val();
                                            var to_lat_longold = $("#to_lat_long").val();
                                            var to_addressold = $("#to").val();
                                            $("#to_lat").val(lat);
                                            $("#to_long").val(lng);
                                            $("#to_lat_long").val(myLatlongs1);
                                            getAddress(lat, lng, 'to','to',to_latold,to_longold,to_lat_longold,to_addressold);
                                            routeDirections();
                                        });
                                    }
                                    routeDirections();
                                }

                                function routeDirections() {
                                    directionsDisplay.setMap(null); // Remove Previous Route.

                                    if (($("#from").val() != "" && $("#from_lat_long").val() != "") && ($("#to").val() != "" && $("#to_lat_long").val() != "")) {
                                        var newFrom = $("#from_lat").val() + ", " + $("#from_long").val();
                                        if (eType == 'UberX') {
                                            var newTo = $("#from_lat").val() + ", " + $("#from_long").val();
                                        } else {
                                            var newTo = $("#to_lat").val() + ", " + $("#to_long").val();
                                        }

                                        //Make an object for setting route
                                        // var request = {
                                            // origin: newFrom, // From locations latlongs
                                            // destination: newTo, // To locations latlongs
                                            // travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
                                        // };
										var source_latitude = $("#from_lat").val();
										var source_longitude = $("#from_long").val();
										var dest_latitude = $("#to_lat").val();
										var dest_longitude = $("#to_long").val();
										var waypoint0 = newFrom;
										var waypoint1 = newTo;

                                        //Draw route from the object
                                        getReverseGeoDirectionCode(source_latitude,source_longitude,dest_latitude,dest_longitude,waypoint0,waypoint1,function(data_response){
                                            // if (status == google.maps.DirectionsStatus.OK) {
                                                // Check for allowed and disallowed.
                                                if (MAPS_API_REPLACEMENT_STRATEGY.toUpperCase() == 'NONE'){
												$("#distance").val(data_response.routes[0].legs[0].distance.value);
												$("#duration").val(data_response.routes[0].legs[0].duration.value);
												var points = data_response.routes[0].overview_polyline.points;
													var polyPoints = google.maps.geometry.encoding.decodePath(points);
													// var polyPoints = data_response;
														directionsDisplay.setMap(null);
                                                directionsDisplay.setMap(map);
                                                directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
														createPolyLine(new google.maps.Polyline({path: polyPoints,strokeColor: '#FF7E00',strokeWeight: 5}));
														points = '';
														data_response = [];
														polyPoints = '';
														temp_points = '';
											}else{
												// removePolyLine();
												$("#distance").val(data_response.distance);
												$("#duration").val(data_response.duration);
													var polyLinesArr = new Array();
													var i;
													if((data_response.data != 'undefined') && (data_response.data != undefined)){
														for (i = 0; i < (data_response.data).length; i++) {
															polyLinesArr.push({ lat: parseFloat(data_response.data[i].latitude), lng: parseFloat(data_response.data[i].longitude) });
														}
													var polyPoints = polyLinesArr;
														directionsDisplay.setMap(null);
														directionsDisplay.setMap(map);
														directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
														createPolyLine(new google.maps.Polyline({path: polyPoints,strokeColor: '#FF7E00',strokeWeight: 5}));
														data_response = [];
														polyPoints = '';
													}
                                                }

                                                var dist_fare = parseFloat($("#distance").val(), 10) / parseFloat(1000, 10);
                                                if ($("#eUnit").val() != 'KMs') {
                                                    dist_fare = dist_fare * 0.621371;
                                                }

                                                $('#dist_fare').text(dist_fare.toFixed(2));
                                                var time_fare = parseFloat($("#duration").val(), 10) / parseFloat(60, 10);
                                                $('#time_fare').text(time_fare.toFixed(2));
                                                var vehicleId = $('#iVehicleTypeId').val();
                                                var booking_date = $('#datetimepicker4').val();
                                                var vCountry = $('#vCountry').val();
                                                var tollcostval = $('#fTollPrice').val();
                                                var userId = $('#iUserId').val();
                                                var timeVal = parseFloat($("#duration").val(), 10) / parseFloat(60, 10);
                                                var distanceVal = parseFloat($("#distance").val(), 10) / parseFloat(1000, 10);
                                                $.ajax({
                                                    type: "POST",
                                                    url: 'ajax_estimate_by_vehicle_type.php',
                                                    dataType: 'json',
                                                    data: {'vehicleId': vehicleId, 'booking_date': booking_date, 'vCountry': vCountry, 'FromLatLong': newFrom, 'ToLatLong': newTo, 'timeduration': timeVal, 'distance': distanceVal, 'userId': userId},
                                                    success: function (dataHtml)
                                                    {
                                                        if (dataHtml != "") {
                                                            var estimateData = dataHtml.estimateArr;
                                                            var totalFare = dataHtml.totalFare;
                                                            var estimateHtml = "";
                                                            for (var i = 0; i < estimateData.length; i++) {
                                                                console.log(estimateData[i])
                                                                var eKey = estimateData[i]['key'];
                                                                var eVal = estimateData[i]['value']
                                                                estimateHtml += '<li><b>' + eKey + '</b> <em>' + eVal + '</em></li>';
                                                            }
                                                            $("#total_fare_price").text(totalFare);
                                                            $("#estimatedata").html(estimateHtml);
                                                        } else {
                                                            $('#minimum_fare_price,#base_fare_price,#dist_fare_price,#time_fare_price,#total_fare_price').text('0');
                                                        }
                                                    }
                                                });
                                            // } else {
                                                // alert("Directions request failed: " + status);
                                            // }
                                        });

<? if ($iVehicleTypeId != "") { ?>
                                            var iVehicleTypeId = '<?= $iVehicleTypeId ?>';
                                            getFarevalues(iVehicleTypeId);
                                            showAsVehicleType(iVehicleTypeId);
<? } ?>

                                    }
                                }

                                function show_locations() {
                                    if ($("#from").val() != "" && $("#to").val() == '') {
                                        DeleteMarkers('from_loc');
                                        var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());
                                        setMarker(latlng, 'from_loc');
                                    }
                                    if ($("#to").val() != "" && $("#from").val() == '') {
                                        DeleteMarkers('to_loc');
                                        var latlng_to = new google.maps.LatLng($("#to_lat").val(), $("#to_long").val());
                                        setMarker(latlng_to, 'to_loc');
                                    }
                                    if ($("#from").val() != '' && $("#to").val() != '') {
                                        from_to($("#from").val(), $("#to").val());
                                    }
                                }
								function createPolyLine(cus_polyline) {
                                if(typeof flightPath !== 'undefined'){
                                    flightPath.setMap(null);
                                    flightPath ='';
                                }

                                flightPath = cus_polyline;
                                flightPath.setMap(map);
                            }
                                function from_to(from, to) {
                                    //  clearThat();
                                    // DeleteMarkers('from_loc');
                                    // DeleteMarkers('to_loc');
                                    // if (from == '')
                                        // from = $('#from').val();

                                    // if (to == '')
                                        // to = $('#to').val();
                                    // $("#from_lat_long").val('');
                                    // $("#from_lat").val('');
                                    // $("#from_long").val('');
                                    // $("#to_lat_long").val('');
                                    // $("#to_lat").val('');
                                    // $("#to_long").val('');

                                    DeleteMarkers('from_loc');
                                        var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());

                                        setMarker(latlng, 'from_loc');
                                        DeleteMarkers('to_loc');
                                        var latlng_to = new google.maps.LatLng($("#to_lat").val(), $("#to_long").val());
                                        setMarker(latlng_to, 'to_loc');
                                    routeDirections();
                                    // alert('sasa');

                                    // routeDirections();
                                }

                                function callEditFundtion() {
                                    var from_lat = $('#from_lat').val();
                                    var from_lng = $('#from_long').val();

                                    var from = new google.maps.LatLng(from_lat, from_lng);

                                    if (from != '') {
                                        setMarker(from, 'from_loc');
                                    }

                                    var to_lat = $('#to_lat').val();
                                    var to_lng = $('#to_long').val();
                                    if (to_lat != 0 && to_lng != 0) {
                                        var to = new google.maps.LatLng(to_lat, to_lng);
                                        if (to != '') {
                                            setMarker(to, 'to_loc');
                                        }
                                    }

                                }

                                // default function call
                                $(function () {
                                    $('#datetimepicker4').datetimepicker({
                                        format: 'YYYY-MM-DD HH:mm:ss',
                                        ignoreReadonly: true,
                                        sideBySide: true,
                                    }).on('dp.change', function (e) {
                                        $('#datetimepicker4').data("DateTimePicker").minDate(moment().add(5, 'm'))
                                    });

								$('#from').keyup(function (e) {
										buildAutoComplete("from",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
											show_locations();
										});

									});
                                });
                                    // var from = document.getElementById('from');
                                    // autocomplete_from = new google.maps.places.Autocomplete(from);
                                    // google.maps.event.addListener(autocomplete_from, 'place_changed', function () {
                                        // var place = autocomplete_from.getPlace();
                                        // $("#from_lat_long").val(place.geometry.location);
                                        // $("#from_lat").val(place.geometry.location.lat());
                                        // $("#from_long").val(place.geometry.location.lng());
                                        // // remove disable from zoom level when from has value
                                        // $('#radius-id').prop('disabled', false);
                                        // if (from != '') {
                                            // checkrestrictionfrom('from');
                                        // }
                                        // show_locations();
                                    // });

                                $('#to').keyup(function (e) {
                                    buildAutoComplete("to",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
                                        show_locations();
                                    });
                                });
                                    // var from = document.getElementById('from');
                                    // autocomplete_from = new google.maps.places.Autocomplete(from);
                                    // google.maps.event.addListener(autocomplete_from, 'place_changed', function () {
                                        // var place = autocomplete_from.getPlace();
                                        // $("#from_lat_long").val(place.geometry.location);
                                        // $("#from_lat").val(place.geometry.location.lat());
                                        // $("#from_long").val(place.geometry.location.lng());
                                        // // remove disable from zoom level when from has value
                                        // $('#radius-id').prop('disabled', false);
                                        // if (from != '') {
                                            // checkrestrictionfrom('from');
                                        // }
                                        // show_locations();
                                    // });

                                    // var to = document.getElementById('to');
                                    // autocomplete_to = new google.maps.places.Autocomplete(to);
                                    // google.maps.event.addListener(autocomplete_to, 'place_changed', function () {
                                        // var place = autocomplete_to.getPlace();
                                        // $("#to_lat_long").val(place.geometry.location);
                                        // $("#to_lat").val(place.geometry.location.lat());
                                        // $("#to_long").val(place.geometry.location.lng());
                                        // if (to != '') {
                                            // checkrestrictionto('to');
                                        // }
                                        // show_locations();
                                    // });


                                });

                                // used this function for phone number
                                function isNumberKey(evt) {
                                    showPhoneDetail();
                                    var charCode = (evt.which) ? evt.which : evt.keyCode
                                    if (charCode > 31 && (charCode < 35 || charCode > 57)) {
                                        return false;
                                    } else {
                                        return true;
                                    }
                                }

                                // used this function for phone number
                                function showPhoneDetail() {
                                    var phone = $('#vPhone').val();
                                    var phoneCode = $('#vPhoneCode').val();
                                    if (phone != "" && phoneCode != "") {
                                        $.ajax({
                                            type: "POST",
                                            url: 'ajax_find_rider_by_number.php',
                                            data: {phone: phone, phoneCode: phoneCode},
                                            success: function (dataHtml)
                                            {
                                                if (dataHtml != "") {
                                                    $("#user_type").val('registered');
                                                    var result = dataHtml.split(':');
                                                    $('#vName').val(result[0]);
                                                    $('#vLastName').val(result[1]);
                                                    $('#vEmail').val(result[2]);
                                                    $('#iUserId').val(result[3]);
                                                    $('#eStatus').val(result[4]);
                                                    if (result[4] == "Inactive" || result[4] == "Deleted") {
                                                        $('#inactiveModal').modal('show');
                                                    }
                                                } else {
                                                    $("#user_type").val('');
                                                    $('#vName').val('');
                                                    $('#vLastName').val('');
                                                    $('#vEmail').val('');
                                                    $('#iUserId').val('');
                                                    $('#eStatus').val('');
                                                }
                                            }
                                        });
                                    } else {
                                        $("#user_type").val('');
                                        $('#vName').val('');
                                        $('#vLastName').val('');
                                        $('#vEmail').val('');
                                        $('#iUserId').val('');
                                        $('#eStatus').val('');
                                    }
                                }

                                // used for country code change
                                function changeCode(id) {
                                    $.ajax({
                                        type: "POST",
                                        url: 'change_code.php',
                                        dataType: 'json',
                                        data: {id: id, eUnit: 'yes'},
                                        success: function (dataHTML)
                                        {
                                            //console.log(dataHTML);
                                            document.getElementById("vPhoneCode").value = dataHTML.vPhoneCode;
                                            $("#change_eUnit").text(dataHTML.eUnit);
                                            showPhoneDetail();
                                        }
                                    });
                                }

                                $(document).ready(function () {
                                    var con = $("#vCountry").val();
                                    changeCode(con);
                                    if ($("#from").val() == "") {
                                        $('#radius-id').prop('disabled', 'disabled');
                                    } else {
                                        $('#radius-id').prop('disabled', false);
                                    }

                                });

                                function showVehicleCountryVise(countryId, vehicleId, eType) {
                                    $.ajax({
                                        type: "POST",
                                        url: "ajax_booking_details.php",
                                        dataType: "html",
                                        data: {countryId: countryId, type: 'getVehicles', iVehicleTypeId: vehicleId, eType: eType},
                                        success: function (dataHtml2) {
                                            $('#iVehicleTypeId').html(dataHtml2);
                                        }, error: function (dataHtml2) {

                                        }
                                    });
                                }

                                function getFarevalues(vehicleId) {
                                    var booking_date = $("#datetimepicker4").val();
                                    var vCountry = $('#vCountry').val();
                                    var userId = $('#iUserId').val();
                                    var tollcostval = $('#fTollPrice').val();
                                    if (vehicleId == "") {
                                        vehicleId = $("#iVehicleTypeId").val();
                                    }
                                    if (($("#from").val() != "") && ($("#to").val() != "")) {
                                        var FromLatLong = $("#from_lat").val() + ", " + $("#from_long").val();
                                        var ToLatLong = $("#to_lat").val() + ", " + $("#to_long").val();
                                    }
                                    var timeVal = parseFloat($("#duration").val(), 10) / parseFloat(60, 10);
                                    var distanceVal = parseFloat($("#distance").val(), 10) / parseFloat(1000, 10);
                                    if (vehicleId != "") {
                                        $.ajax({
                                            type: "POST",
                                            url: 'ajax_estimate_by_vehicle_type.php',
                                            dataType: 'json',
                                            data: {'vehicleId': vehicleId, 'booking_date': booking_date, 'vCountry': vCountry, 'FromLatLong': FromLatLong, 'ToLatLong': ToLatLong, 'timeduration': timeVal, 'distance': distanceVal, 'userId': userId},
                                            success: function (dataHtml)
                                            {
                                                if (dataHtml != "") {
                                                    var estimateData = dataHtml.estimateArr;
                                                    var totalFare = dataHtml.totalFare;
                                                    var estimateHtml = "";
                                                    for (var i = 0; i < estimateData.length; i++) {
                                                        console.log(estimateData[i])
                                                        var eKey = estimateData[i]['key'];
                                                        var eVal = estimateData[i]['value']
                                                        estimateHtml += '<li><b>' + eKey + '</b> <em>' + eVal + '</em></li>';
                                                    }
                                                    $("#total_fare_price").text(totalFare);
                                                    $("#estimatedata").html(estimateHtml);
                                                } else {
                                                    $('#minimum_fare_price,#base_fare_price,#dist_fare_price,#time_fare_price,#total_fare_price').text('0');
                                                }
                                            }
                                        });
                                    }
                                }

                                function showAsVehicleType(vType) {
                                    var type = $("#newType").val();
                                    for (var i = 0; i < driverMarkers.length; i++) {
                                        driverMarkers[i].setMap(null);
                                    }

                                    var infowindow = new google.maps.InfoWindow();
                                    for (var i = 0; i < newLocations.length; i++) {
                                        if (type == newLocations[i].location_type || type == "") {
                                            var str33 = newLocations[i].location_carType;
                                            if (vType == "" || (str33 != null && str33.indexOf(vType) != -1)) {
                                                newName = newLocations[i].location_name;
                                                newOnlineSt = newLocations[i].location_online_status;
                                                newLat = newLocations[i].google_map.lat;
                                                newLong = newLocations[i].google_map.lng;
                                                newDriverImg = newLocations[i].location_image;
                                                newMobile = newLocations[i].location_mobile;
                                                newDriverID = newLocations[i].location_ID;
                                                newImg = newLocations[i].location_icon;
                                                latlng = new google.maps.LatLng(newLat, newLong);
                                                // bounds.push(latlng);
                                                // alert(newImg);
                                                content = '<table><tr><td rowspan="4"><img src="' + newDriverImg + '" height="60" width="60"></td></tr><tr><td>&nbsp;&nbsp;Email: </td><td><b>' + newDriverID + '</b></td></tr><tr><td>&nbsp;&nbsp;Mobile: </td><td><b>+' + newMobile + '</b></td></tr></table>';
                                                var drivermarker = new google.maps.Marker({
                                                    map: map,
                                                    //animation: google.maps.Animation.DROP,
                                                    position: latlng,
                                                    icon: newImg
                                                });
                                                google.maps.event.addListener(drivermarker, 'click', (function (drivermarker, content, infowindow) {
                                                    return function () {
                                                        infowindow.setContent(content);
                                                        infowindow.open(map, drivermarker);
                                                    };
                                                })(drivermarker, content, infowindow));
                                                // alert(content);
                                                driverMarkers.push(drivermarker);
                                            }
                                        }
                                    }
                                    //var markers = [];//some array
                                    // var bounds = new google.maps.LatLngBounds();
                                    // for (var i = 0; i < driverMarkers.length; i++) {
                                    // bounds.extend(driverMarkers[i].getPosition());
                                    // }

                                    // map.fitBounds(bounds);
                                    //setDriverListing(vType);
                                    getFarevalues(vType);
                                }

                                setInterval(function () {
                                    var activeClass = document.getElementById('alldriver').className;
                                    var activeClassName = activeClass.includes("active");
                                    var typeName = "Available";
                                    if (activeClassName == true) {
                                        var typeName = "";
                                    }
                                    if (eTypeQ11 == 'yes') {
                                        setDriversMarkers('test', typeName);
                                        $("#driver_main_list").html('');
                                    }
                                }, 35000);
                                function setFormBook() {
                                    var statusVal = $('#vEmail').val();
                                    if (statusVal != '') {
                                        $.ajax({
                                            type: "POST",
                                            url: 'ajax_checkBooking_email.php',
                                            data: 'vEmail=' + statusVal,
                                            success: function (dataHtml)
                                            {
                                                var testEstatus = dataHtml.trim();
                                                if (testEstatus != 'Active' && testEstatus != '') {
                                                    if (confirm("The selected <?php echo $langage_lbl_admin['LBL_RIDER']; ?> account is in 'Inactive / Deleted' mode. Do you want to Active this <?php echo $langage_lbl_admin['LBL_RIDER']; ?>?'")) {
                                                        eTypeQ11 = 'no';
                                                        $("#add_booking_form").attr('action', 'action_booking.php');
                                                        $(".finalsubmitbutton").trigger("click");
                                                        // e.stopPropagation();
                                                        // e.preventDefault();
                                                        return false;
                                                    } else {
                                                        $("#vEmail").focus();
                                                        return false;
                                                    }
                                                } else {
                                                    eTypeQ11 = 'no';
                                                    $("#add_booking_form").attr('action', 'action_booking.php');
                                                    $(".finalsubmitbutton").trigger("click");
                                                    // e.stopPropagation();
                                                    // e.preventDefault();
                                                    return false;
                                                }
                                            }
                                        });
                                    } else {
                                        return false;
                                    }
                                }

                                function get_drivers_list(keyword) {
                                    vCountry = $("#vCountry").val();
                                    vType = $("#iVehicleTypeId").val();

                                    if ($("#eFemaleDriverRequest").is(":checked")) {
                                        eLadiesRide = 'Yes';
                                    } else {
                                        eLadiesRide = 'No';
                                    }

                                    if ($("#eHandiCapAccessibility").is(":checked")) {
                                        eHandicaps = 'Yes';
                                    } else {
                                        eHandicaps = 'No';
                                    }
                                    // $("#imageIcon").show();

                                    $.ajax({
                                        type: "POST",
                                        url: "get_available_driver_list.php",
                                        dataType: "html",
                                        data: {vCountry: vCountry, keyword: keyword, iVehicleTypeId: vType, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, AppeType: eType},
                                        success: function (dataHtml2) {
                                            $('#driver_main_list').show();
                                            if (dataHtml2 != "") {
                                                $('#driver_main_list').html(dataHtml2);
                                            } else {
                                                $('#driver_main_list').html('<h4 style="margin:25px 0 0 15px">Sorry , No <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Found.</h4>');
                                            }
                                            if ($("#eAutoAssign").is(':checked')) {
                                                $(".assign-driverbtn").attr('disabled', 'disabled');
                                            }
                                            $("#imageIcon").hide();
                                        }, error: function (dataHtml2) {

                                        }
                                    });
                                }

                                $("#eAutoAssign").on('change', function () {
                                    if ($(this).prop('checked')) {
                                        $("#iDriverId").val('');
                                        $("#iDriverId").attr('disabled', 'disabled');
                                        $(".assign-driverbtn").attr('disabled', 'disabled');
                                        $("#showdriverSet001").hide();
                                        $('#myModalautoassign').modal('show');
                                    } else {
                                        $("#iDriverId").removeAttr('disabled');
                                        $(".assign-driverbtn").removeAttr('disabled');
                                        $('#myModalautoassign').modal('hide');
                                    }
                                });
                                var bookId = '<?php echo $iCabBookingId; ?>';
                                if (bookId != "") {
                                    if ($("#eAutoAssign").prop('checked')) {
                                        $("#iDriverId").val('');
                                        $("#iDriverId").attr('disabled', 'disabled');
                                    } else {
                                        $("#iDriverId").removeAttr('disabled');
                                    }
                                }

                                $(document).ready(function () {
                                    var referrer;
                                    if ($("#previousLink").val() == "") {
                                        referrer = document.referrer;
                                    } else {
                                        referrer = $("#previousLink").val();
                                    }
                                    if (referrer == "") {
                                        referrer = "cab_booking.php";
                                    } else {
                                        $("#backlink").val(referrer);
                                    }
                                });

                                /*$('#datetimepicker4').keydown(function(e) {
                                 e.preventDefault();
                                 return false;
                                 });*/

                                // for checking restriction in from area
                                function checkrestrictionfrom(type) {
                                    if (($("#from").val() != "") || ($("#to").val() != "")) {
                                        $.ajax({
                                            type: "POST",
                                            url: 'checkForRestriction.php',
                                            dataType: 'html',
                                            data: {fromLat: $("#from_lat").val(), fromLong: $("#from_long").val(), type: type},
                                            success: function (dataHtml5)
                                            {
                                                if ($.trim(dataHtml5) != '') {
                                                    alert($.trim(dataHtml5));
                                                }
                                            },
                                            error: function (dataHtml5)
                                            {
                                            }
                                        });
                                    }
                                }

                                // for checking restriction in to area
                                function checkrestrictionto(type) {
                                    if (($("#from").val() != "") || ($("#to").val() != "")) {
                                        $.ajax({
                                            type: "POST",
                                            url: 'checkForRestriction.php',
                                            dataType: 'html',
                                            data: {toLat: $("#to_lat").val(), toLong: $("#to_long").val(), type: type},
                                            success: function (dataHtml5)
                                            {
                                                if ($.trim(dataHtml5) != '') {
                                                    alert($.trim(dataHtml5));
                                                }
                                            },
                                            error: function (dataHtml5)
                                            {
                                            }
                                        });
                                    }
                                }

                                $('#add_booking_form').on('keyup keypress', function (e) {
                                    var keyCode = e.keyCode || e.which;
                                    if (keyCode === 13) {
                                        e.preventDefault();
                                        return false;
                                    }
                                });

                                var ResponseDataArray = "";
                                $(".finalsubmitbutton").on("click", function (event) {
                                    var from_lat_long = $("#from_lat_long").val();
                                    var to_lat_long = $("#to_lat_long").val();
                                    // if (from_lat_long == '' || to_lat_long == '') {
                                    //     alert("Please Select Proper Address.");
                                    //     $("#from").focus();
                                    //     $("#to").focus();
                                    //     return false;
                                    // }
                                    var isvalidate = $("#add_booking_form")[0].checkValidity();
                                    var idClicked = event.target.id;
                                    if (isvalidate) {
                                        event.preventDefault();
                                        var country = $('select[name=vCountry]').val();
                                        var eTollEnabled = '';
                                        if (country != "") {
                                            $.ajax({
                                                type: "POST",
                                                url: 'ajax_check_toll.php',
                                                dataType: 'html',
                                                async: false,
                                                data: {vCountryCode: country},
                                                success: function (dataHtml5)
                                                {
                                                    eTollEnabled = dataHtml5;
                                                    return eTollEnabled;
                                                }
                                            });
                                        }
                                        if (eTollEnabled == 'Yes') {
                                            if (eType != 'UberX' && eFlatTrip != 'Yes') {
                                                if (ENABLE_TOLL_COST == 'Yes') {
                                                    $(".loader-default").show();
                                                    if (($("#from").val() != "" && $("#from_lat_long").val() != "") && ($("#to").val() != "" && $("#to_lat_long").val() != "")) {
                                                        var newFromtoll = $("#from_lat").val() + "," + $("#from_long").val();
                                                        var newTotoll = $("#to_lat").val() + "," + $("#to_long").val();
                                                        $.getJSON("https://tce.cit.api.here.com/2/calculateroute.json?app_id=<?= $TOLL_COST_APP_ID ?>&app_code=<?= $TOLL_COST_APP_CODE ?>&waypoint0=" + newFromtoll + "&waypoint1=" + newTotoll + "&mode=fastest;car", function (result) {
                                                            var tollCurrency = result.costs.currency;
                                                            var tollCost = result.costs.details.tollCost;
<?php if ($eTollSkipped == 'Yes') { ?>
                                                                var tollskip = 'Yes';
<?php } else { ?>
                                                                var tollskip = 'No';
<?php } ?>
                                                            $('#tollcost').text(tollCurrency + " " + tollCost);
                                                            if (tollCost != '0.0' && $.trim(tollCost) != "" && tollCost != '0') {
                                                                $(".loader-default").hide();
                                                                var modal = bootbox.dialog({
                                                                    message: $(".form-content").html(),
                                                                    title: "Toll Route",
                                                                    buttons: [
                                                                        {
                                                                            label: "Continue",
                                                                            className: "btn btn-primary",
                                                                            callback: function (result) {
                                                                                // alert("toll"+tollskip);
                                                                                $("#vTollPriceCurrencyCode").val(tollCurrency);
                                                                                $("#fTollPrice").val(tollCost);
                                                                                $("#eTollSkipped").val(tollskip);
                                                                                SubmitFormCheck(idClicked);
                                                                                //SubmitBookingForm();
                                                                            }
                                                                        },
                                                                        {
                                                                            label: "Close",
                                                                            className: "btn btn-default",
                                                                            callback: function () {
                                                                                // $('.finalsubmitbutton').prop('disabled', false);
                                                                            }
                                                                        }
                                                                    ],
                                                                    show: false,
                                                                    onEscape: function () {
                                                                        modal.modal("hide");
                                                                        //$('.finalsubmitbutton').prop('disabled', false);
                                                                    }
                                                                });
                                                                modal.on('shown.bs.modal', function () {
                                                                    modal.find('.modal-body').on('change', 'input[type="checkbox"]', function (e) {
                                                                        $(this).attr("checked", this.checked);
                                                                        //$(this).val(this.checked ? "Yes" : "No");
                                                                        if ($(this).is(':checked')) {
                                                                            tollskip = 'Yes';
                                                                        } else {
                                                                            tollskip = 'No';
                                                                        }
                                                                        //alert(tollskip);
                                                                    });
                                                                });
                                                                modal.modal("show");
                                                            } else {
                                                                $(".loader-default").hide();
                                                                SubmitFormCheck(idClicked);
                                                                //SubmitBookingForm();
                                                            }
                                                        }).fail(function (jqXHR, textStatus, errorThrown) {
                                                            //alert("Toll API Response: " + jqXHR.responseJSON.message);
                                                            $(".loader-default").hide();
                                                            SubmitFormCheck(idClicked);
                                                        });
                                                    } /*else {
                                                     $(".loader-default").hide();
                                                     SubmitBookingForm();
                                                     }*/
                                                } else {
                                                    //SubmitBookingForm();
                                                    SubmitFormCheck(idClicked);
                                                }
                                            } else {
                                                //SubmitBookingForm();
                                                SubmitFormCheck(idClicked);
                                            }
                                        } else {
                                            SubmitFormCheck(idClicked);
                                            /*var idClicked = event.target.id;
                                             if(idClicked == 'submitbutton'){
                                             $("#submitbtnvalue").val('Book Later');
                                             var modalnew = bootbox.dialog({
                                             message: BootboxContent,
                                             title: "Date And Time",
                                             buttons: [
                                             {
                                             label: "Apply",
                                             className: "btn btn-primary",
                                             callback: function(result) {
                                             var selectedDateTime = $('#datetimepicker4new').val();
                                             if(selectedDateTime == ''){
                                             $(".datetime").html('Please Select Date and Time.');
                                             return false;
                                             } else {
                                             confirm("Please make sure that the booking time is 20 minutes ahead from current time. So if your current time is 3:00 P.M then please select 3:20 P.M as booking time.  This gives a room to auto assign drivers properly.");
                                             $(".datetime").html('');
                                             $("#dBooking_date").val(selectedDateTime);
                                             SubmitBookingForm();
                                             }
                                             }
                                             },
                                             {
                                             label: "Close",
                                             className: "btn btn-default",
                                             callback: function() {
                                             }
                                             }
                                             ],
                                             show: false,
                                             onEscape: function() {
                                             modalnew.modal("hide");
                                             }
                                             });
                                             modalnew.modal("show");
                                             } else {
                                             $("#submitbtnvalue").val('Book Now');
                                             var today = js_yyyy_mm_dd_hh_mm_ss()
                                             $("#dBooking_date").val(today);
                                             $.ajax({
                                             type: "POST",
                                             url: 'hotel_booking.php',
                                             dataType: "json",
                                             async   : false,
                                             data: $("#add_booking_form").serialize(),
                                             success: function(response)
                                             {
                                             //console.log(response);
                                             ResponseDataArray = response;
                                             loadAvailableCab();
                                             return false;
                                             }
                                             });
                                             
                                             //$("#add_booking_form").submit();
                                             return true;
                                             }*/
                                        }
                                    }
                                });

                                function SubmitFormCheck(idClicked) {
                                    if (idClicked == 'submitbutton') {
                                        $("#submitbtnvalue").val('Book Later');
                                        var modalnew = bootbox.dialog({
                                            message: BootboxContent,
                                            title: "Date And Time",
                                            buttons: [
                                                {
                                                    label: "Apply",
                                                    className: "btn btn-primary",
                                                    callback: function (result) {
                                                        var selectedDateTime = $('#datetimepicker4new').val();
                                                        if (selectedDateTime == '') {
                                                            $(".datetime").html('Please Select Date and Time.');
                                                            return false;
                                                        } else {
                                                            confirm("Please make sure that the booking time is 20 minutes ahead from current time. So if your current time is 3:00 P.M then please select 3:20 P.M as booking time.  This gives a room to auto assign <?php echo strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']); ?> properly.");
                                                            $(".datetime").html('');
                                                            $("#dBooking_date").val(selectedDateTime);
                                                            SubmitBookingForm();
                                                        }
                                                    }
                                                },
                                                {
                                                    label: "Close",
                                                    className: "btn btn-default",
                                                    callback: function () {
                                                    }
                                                }
                                            ],
                                            show: false,
                                            onEscape: function () {
                                                modalnew.modal("hide");
                                            }
                                        });
                                        modalnew.modal("show");
                                    } else {
                                        $("#submitbtnvalue").val('Book Now');
                                        var today = js_yyyy_mm_dd_hh_mm_ss();
                                        $("#dBooking_date").val(today);
                                        $.ajax({
                                            type: "POST",
                                            url: 'hotel_booking.php',
                                            dataType: "json",
                                            async: false,
                                            data: $("#add_booking_form").serialize(),
                                            success: function (response)
                                            {
                                                ResponseDataArray = response;
                                                loadAvailableCab();
                                                return false;
                                            }
                                        });

                                        //$("#add_booking_form").submit();
                                        return true;
                                    }
                                }

                                function SubmitBookingForm() {
                                    $.ajax({
                                        type: "POST",
                                        url: 'hotel_booking.php',
                                        dataType: "html",
                                        async: false,
                                        data: $("#add_booking_form").serialize(),
                                        success: function (response)
                                        {
                                            //confirm(response);
                                            window.location.replace("cab_booking.php");
                                        }
                                    });
                                }

                                function showReTry() {
                                    setTimeout(function () {
                                        $('#req_try_again').show();
                                        $('.requesting-popup-sub').hide();
                                    }, 45000);
                                }

                                function loadAvailableCab() {
                                    var data = {
                                        "tSessionId": ResponseDataArray.tSessionId,
                                        "GeneralMemberId": ResponseDataArray.iUserId,
                                        "GeneralUserType": 'Passenger',
                                        "type": 'loadAvailableCab',
                                        "vTimeZone": ResponseDataArray.vTimeZone,
                                        "iUserId": ResponseDataArray.iUserId,
                                        "PassengerLat": ResponseDataArray.vSourceLatitude,
                                        "PassengerLon": ResponseDataArray.vSourceLongitude,
                                        "iVehicleTypeId": ResponseDataArray.iVehicleTypeId,
                                        "PickUpAddress": ResponseDataArray.vSourceAddresss,
                                        "eType": ResponseDataArray.eType,
                                        "eRental": ResponseDataArray.eRental,
                                        "eShowOnlyMoto": ResponseDataArray.eShowOnlyMoto,
                                        "isFromHotelPanel": "Yes"
                                    };

                                    data = $.param(data);
                                    $.ajax({
                                        type: "POST",
                                        dataType: "json",
                                        url: "../<?php echo HotelAPIUrl; ?>",
                                        data: data,
                                        async: false,
                                        success: function (response) {
                                            if (response.hasOwnProperty("AvailableCabList")) {
                                                var AvailableCabList = response.AvailableCabList;
                                                var AvailableDriverIds = [];
                                                $.each(AvailableCabList, function (key, value) {
                                                    AvailableDriverIds.push(value.iDriverId);
                                                });
                                                // sendrequestto driver
                                                SendRequestToDriver(AvailableDriverIds);
                                                return false;
                                            } else {
                                                var AvailableDriverIds = [];
                                                SendRequestToDriver(AvailableDriverIds);
                                            }
                                        }
                                    });
                                }

                                function SendRequestToDriver(AvailableDriverIds) {
                                    $("#request-loader001").show();
                                    $("#requ_title").show();
                                    var driverIds = AvailableDriverIds.join(",");
                                    if (driverIds != '') {
                                        var sendrequestparam = {
                                            "tSessionId": ResponseDataArray.tSessionId,
                                            "GeneralMemberId": ResponseDataArray.iUserId,
                                            "GeneralUserType": 'Passenger',
                                            "type": 'sendRequestToDrivers',
                                            "vTimeZone": ResponseDataArray.vTimeZone,
                                            "userId": ResponseDataArray.iUserId,
                                            "driverIds": driverIds,
                                            "CashPayment": 'true',
                                            "SelectedCarTypeID": ResponseDataArray.iVehicleTypeId,
                                            "PickUpLatitude": ResponseDataArray.vSourceLatitude,
                                            "PickUpLongitude": ResponseDataArray.vSourceLongitude,
                                            "PickUpAddress": ResponseDataArray.vSourceAddresss,
                                            "DestLatitude": ResponseDataArray.vDestLatitude,
                                            "DestLongitude": ResponseDataArray.vDestLongitude,
                                            "DestAddress": ResponseDataArray.tDestAddress,
                                            "eType": ResponseDataArray.eType,
                                            "fTollPrice": ResponseDataArray.fTollPrice,
                                            "vTollPriceCurrencyCode": ResponseDataArray.vTollPriceCurrencyCode,
                                            "eBookingFrom": ResponseDataArray.eBookingFrom,
                                            "iHotelBookingId": ResponseDataArray.iHotelBookingId,
                                            "eTollSkipped": ResponseDataArray.eTollSkipped,
                                            "iHotelId": ResponseDataArray.iHotelId,
                                            "isFromHotelPanel": "Yes"
                                        };
                                        sendrequestparam = $.param(sendrequestparam);
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            async: false,
                                            url: "../<?php echo HotelAPIUrl; ?>",
                                            data: sendrequestparam,
                                            success: function (response) {
                                                if (response.Action == '1') {
                                                    getAcceptedDriver001();
                                                } else {
                                                    alert(response.Message);
                                                    $("#request-loader001").hide();
                                                    $("#requ_title").hide();
                                                }
                                            }
                                        });

                                    } else {
                                        $("#request-loader001").hide();
                                        $("#requ_title").hide();
                                        alert('No <?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']; ?> available. Please Try again After Some Time.');
                                    }
                                }

                                function getAcceptedDriver001() {
                                    showReTry();
                                    interval3 = setInterval(function () {
                                        $.ajax({
                                            type: "POST",
                                            url: "../<?php echo HotelAPIUrl; ?>",
                                            async: false,
                                            data: {type: 'configPassengerTripStatus', "GeneralMemberId": ResponseDataArray.iUserId,
                                                "GeneralUserType": 'Passenger', iMemberId: ResponseDataArray.iUserId, vLatitude: ResponseDataArray.vSourceLatitude, vLongitude: ResponseDataArray.vSourceLongitude, vTimeZone: ResponseDataArray.vTimeZone, "tSessionId": ResponseDataArray.tSessionId},
                                            success: function (dataHtml3) {
                                                if (dataHtml3.trim() != "") {
                                                    var obj = jQuery.parseJSON(dataHtml3);
                                                    if (obj.message != null) {
                                                        var messagenew = jQuery.parseJSON(obj.message);
                                                        if (messagenew.Message == 'CabRequestAccepted') {
                                                            clearInterval(interval3);
                                                            $("#request-loader001").hide();
                                                            /*	 $("#driver-bottom-set001").show();
                                                             $("#driver-bottom-set001").html(dataHtml3);*/
                                                            $.ajax({
                                                                type: 'post',
                                                                dataType: "html",
                                                                async: false,
                                                                url: 'fetch_record.php', //Here you will fetch records 
                                                                data: {iMemberId: ResponseDataArray.iUserId, vLatitude: ResponseDataArray.vSourceLatitude, vLongitude: ResponseDataArray.vSourceLongitude, vTimeZone: ResponseDataArray.vTimeZone}, //Pass $id
                                                                success: function (data) {
                                                                    $('#driver-bottom-set001').html(data);//Show fetched data from database
                                                                }
                                                            });
                                                            // $('#driverData').modal('show');
                                                            $('#driverData').modal({
                                                                backdrop: 'static',
                                                                keyboard: false
                                                            });
                                                            $("#btnYes").on("click", function (e) {
                                                                window.location.replace("trip.php");
                                                            });
                                                            return false;
                                                        } else {
                                                            $('#driverData').modal('hide');
                                                        }
                                                    }
                                                }
                                            },
                                            error: function (dataHtml3) {
                                            }
                                        });
                                    }, 6000);
                                }
                                $(document).ready(function () {
                                    $('#retryBtn').click(function () {
                                        $('#req_try_again').hide();
                                        $('.requesting-popup-sub').show();
                                        setTimeout(function () {
                                            $('#req_try_again').show();
                                            $('.requesting-popup-sub').hide();
                                        }, 45000);
                                        loadAvailableCab();
                                    });
                                });

                                function cancellingRequestDriver() {
                                    $.ajax({
                                        type: "POST",
                                        url: "../<?php echo HotelAPIUrl; ?>",
                                        dataType: "json",
                                        async: false,
                                        data: {type: 'cancelCabRequest', iUserId: ResponseDataArray.iUserId, "GeneralMemberId": ResponseDataArray.iUserId, "GeneralUserType": 'Passenger', "tSessionId": ResponseDataArray.tSessionId},
                                        success: function (dataHtml2) {
                                            if (dataHtml2) {
                                                $("#request-loader001").hide();
                                                $('#add_booking_form').trigger("reset");
                                                window.location.replace("create_request.php");
                                            }
                                        },
                                        error: function (dataHtml2) {}
                                    });
                                    $("#request-loader001").hide();
                                    $("#cancelRequestDriver").hide();
                                }


                                function js_yyyy_mm_dd_hh_mm_ss() {
                                    now = new Date();
                                    year = "" + now.getFullYear();
                                    month = "" + (now.getMonth() + 1);
                                    if (month.length == 1) {
                                        month = "0" + month;
                                    }
                                    day = "" + now.getDate();
                                    if (day.length == 1) {
                                        day = "0" + day;
                                    }
                                    hour = "" + now.getHours();
                                    if (hour.length == 1) {
                                        hour = "0" + hour;
                                    }
                                    minute = "" + now.getMinutes();
                                    if (minute.length == 1) {
                                        minute = "0" + minute;
                                    }
                                    second = "" + now.getSeconds();
                                    if (second.length == 1) {
                                        second = "0" + second;
                                    }
                                    return year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
                                }

                                function BootboxContent() {
                                    var frm_str = '<form class="form" role="form" id="fromdatetime">'
                                            + '<div class="form-group">'
                                            + '<label for="date">Date & Time</label>'
                                            + ' <input type="text" class="form-control form-control14" name="dBooking_date_new"  id="datetimepicker4new" value="<?= $dBooking_date; ?>" placeholder="Select Date / Time" required><div class="datetime error" style="margin-top:5px;"></div>'
                                            + '</div>'
                                            + '</form>';

                                    var object = $('<div/>').html(frm_str).contents();

                                    object.find('#datetimepicker4new').datetimepicker({
                                        format: 'YYYY-MM-DD HH:mm:ss',
                                        ignoreReadonly: true,
                                        sideBySide: true,
                                    }).on('dp.change', function (e) {
                                        $('#datetimepicker4new').data("DateTimePicker").minDate(moment().add(5, 'm'))
                                    });

                                    object.find('#datetimepicker4new').keydown(function (e) {
                                        e.preventDefault();
                                        return false;
                                    });

                                    return object;
                                }

            </script>
            <style>
                #cancelRequestDriver{
                    display: none;
                }
                .send-requesting-popup img {
                    margin: 0 auto;
                    padding: 0px;
                    position: absolute;
                    bottom: 30px;
                    left: 0;
                    right: 0;
                }
                .requesting-popup {
                    margin: 0px;
                    padding: 7px 0 14px;
                    float: left;
                    width: 100%;
                    background: #ffa523;
                    position: relative;
                    top: 0;
                    z-index: 9999;
                }
                .requesting-popup p {
                    margin: 0 0 0 10px;
                    padding: 0px;
                    float: left;
                    width: 60%;
                    color: #FFFFFF;
                    font-size: 14px;
                    font-weight: normal;
                }
                .hide001 {
                    display: none;
                }
                .booking-confirmation-popup {
                    margin: 0px;
                    padding: 0px;
                    float: left;
                    width: 100%;
                    height: 100vh;
                    background: rgba(0, 0, 0, 0.5) none repeat scroll 0 0;
                    position: fixed;
                    top: 0;
                    bottom: 0;
                    z-index: 999;
                    left: 0px;
                }
                .req-001 {
                    margin: 0px;
                    padding: 12px 0;
                    float: left;
                    width: 100%;
                    background: #ffa523;
                    color: #FFFFFF;
                }
                .req-001 b {
                    margin: 0 0 0 10px;
                    padding: 0px;
                    float: left;
                    font-size: 19px;
                }
                .requesting-popup span {
                    margin: 10px 10px 0 0;
                    padding: 0px;
                    float: right;
                    width: auto;
                    color: #FFFFFF;
                }

                .requesting-popup span a {
                    margin: 0px;
                    padding: 8px 10px;
                    float: left;
                    background: #000000;
                    color: #FFFFFF;
                    text-transform: uppercase;
                    font-size: 15px;
                    border:#000;
                }

                .requesting-popup-old .requesting-popup-sub a {
                    margin: 0px;
                    padding: 8px 10px;
                    float: left;
                    background: #000000;
                    color: #FFFFFF;
                    text-transform: uppercase;
                    font-size: 15px;
                    border:#000;
                }

                .pulse-box em {
                    margin: 0 auto;
                    padding: 0px;
                    position: absolute;
                    top: 31%;
                    left: 33%;
                    right: 0;
                    z-index: 999;
                }
                .pulse-box {height:155px; width:155px; background:#efa233;  border-radius:50%; position: absolute;margin: auto;top: 8%;left: 0;right: 0;bottom: 0;}
                .pulse-box em{ margin:0 auto; padding:0px; position:absolute; top:31%; left:33%; right:0; z-index:999;}

                /* pulse in SVG */
                svg.pulse-svg {
                    overflow: visible;
                }
                svg.pulse-svg .first-circle, svg.pulse-svg .second-circle, svg.pulse-svg .third-circle {
                    fill:#e99803;
                    transform: scale(0.9);
                    transform-origin: center center;
                    animation: pulse-me 3s linear infinite;
                }
                svg.pulse-svg .first-circle {
                    animation-delay: 1s;
                }
                svg.pulse-svg .second-circle {
                    animation-delay: 2s;
                }
                svg.pulse-svg .third-circle {
                    animation-delay: 3s;
                }
                /* pulse in CSS */
                .pulse-css {
                    width: 65px;
                    height: 65px;
                    -webkit-border-radius: 25px;
                    -moz-border-radius: 25px;
                    border-radius: 25px;
                    background: tomato;
                    position: relative;
                }
                .pulse-css:before, .pulse-css:after {
                    content: '';
                    width: 65px;
                    height: 65px;
                    -webkit-border-radius: 25px;
                    -moz-border-radius: 25px;
                    border-radius: 25px;
                    background-color: tomato;
                    position: absolute;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    margin: auto;
                    transform: scale(0.9);
                    transform-origin: center center;
                    animation: pulse-me 3s linear infinite;
                }
                .pulse-css:after {
                    animation-delay: 2s;
                }

                @keyframes pulse-me {
                    0% {
                        transform:scale(0.9);
                        opacity: 0.2;
                    }
                    50% {
                        opacity: 0.5;
                    }
                    70% {
                        opacity: 0.09;
                    }
                    100% {
                        transform: scale(5);
                        opacity: 0;
                    }
                }
                .arriving-bottom-part {
                    margin: 0px;
                    padding: 15px 0 0;
                    float: left;
                    width: 100%;
                    background: #f2f2f4;
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                }
                .send-requesting-popup {
                    margin: 0px;
                    padding: 0px;
                    padding: 0px;
                    float: left;
                    width: 100%;
                    height: 100%;
                    position: fixed;
                    z-index: 999;
                    text-align: center;
                }
            </style>
    </body>
    <!-- END BODY-->
</html>


<div class="modal fade" id="driverData" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">x</span>
                </button> -->
                <h4 class="modal-title" id="myModalLabel">Alert</h4>
            </div>
            <div class="modal-body">
                <div id="driver-bottom-set001">	
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnYes" class="btn btn-secondary btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="booking-confirmation-popup hide001" id="request-loader001" >
    <div class="requesting-popup-old">
        <span id="requ_title" class="req-001"><b>Requesting ....</b>
            <span class="requesting-popup-sub" style="padding-right: 5px;float: right;"><a href="javascript:void(0);" id="cancelBtn" onClick="cancellingRequestDriver();">Cancel</a></span></span>
    </div>
    <div class="requesting-popup hide001" id="req_try_again">
        <p>No any <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> available to accept your trip request now. You may RETRY or CANCEL</p>
        <span><a href="javascript:void(0);" id="retryBtn">Retry</a></span>
        <span><a href="javascript:void(0);" id="cancelBtn" onClick="cancellingRequestDriver();">Cancel</a></span>
    </div>
    <div class="pulse-box"> <svg class="pulse-svg" width="155px" height="155px" viewBox="0 0 50 50" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <circle class="circle first-circle" fill="#e99803" cx="25" cy="25" r="25"></circle>
        <circle class="circle second-circle" fill="#e99803" cx="25" cy="25" r="25"></circle>
        <circle class="circle third-circle" fill="#e99803" cx="25" cy="25" r="25"></circle>
        <circle class="circle" fill="#e99803" cx="25" cy="25" r="25"></circle>
        <em><img src="images-new/confirmation-img.png" alt=""></em>
        </svg> </div>
    <!--div class="booking-confirmation-popup-inner">  
        <div class="ripple"><img class="confirmation-img" src="images-new/confirmation-img.png" alt="" /></div>
    </div-->
</div>

<div class="loader-default"></div>

<div class="form-content" style="display:none;">
    <p><?php echo $langage_lbl_admin['LBL_TOLL_PRICE_DESC']; ?></p>
    <form class="form" role="form" id="formtoll">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="eTollSkipped1" id="eTollSkipped1" value="Yes" <?php if ($eTollSkipped == 'Yes') echo 'checked'; ?>/> Ignore Toll Route
            </label>
        </div>
    </form>
    <p style="text-align: center;font-weight: bold;">
        <span>Total Fare <?php echo $generalobj->symbol_currency(); ?><b id="totalcost">0</b></span>+
        <span>Toll Price <b id="tollcost">0</b></span>
    </p>
</div>

<div class="modal fade" id="myModalautoassign" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Alert</h4>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px;"> Please make sure that the booking time is 20 minutes ahead from current time. So if your current time is 3:00 P.M then please select 3:20 P.M as booking time.  This gives a room to auto assign <?php echo strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']); ?> properly.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

