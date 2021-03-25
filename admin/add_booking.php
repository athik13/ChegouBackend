<?php
include_once '../common.php';

if (manual_booking_extended_version()) {
    $bookstr = '';
    if (!empty($_REQUEST['booking_id'])) {
        $bookstr = '&booking_id=' . $_REQUEST['booking_id'];
        //$bookstr = '/'.$_REQUEST['booking_id'];
    }
    header("location: ../userbooking.php?userType1=admin" . $bookstr);
    //header("location: ../adminbooking".$bookstr);
    exit;
} 

if (!isset($generalobjAdmin)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('manage-manual-booking')) {
    $userObj->redirect();
}

if ($default_lang == "") {
    $default_lang = "EN";
}
$sql = "SELECT vValue,vName FROM `configurations` WHERE vName IN ('APP_DELIVERY_MODE','ENABLE_TOLL_COST','TOLL_COST_APP_ID','TOLL_COST_APP_CODE','CHILD_SEAT_ACCESSIBILITY_OPTION','WHEEL_CHAIR_ACCESSIBILITY_OPTION','HANDICAP_ACCESSIBILITY_OPTION')";
$APP_DELIVERY_MODE = $ENABLE_TOLL_COST = $TOLL_COST_APP_ID = $TOLL_COST_APP_CODE = $CHILD_SEAT_ACCESSIBILITY_OPTION = $WHEEL_CHAIR_ACCESSIBILITY_OPTION = $HANDICAP_ACCESSIBILITY_OPTION = "";
$configData = $obj->MySQLSelect($sql);
//echo "<pre>";print_r($configData);die;
for ($c = 0; $c < count($configData); $c++) {
    $configName = $configData[$c]['vName'];
    $$configName = $configData[$c]['vValue'];
    /* if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "APP_DELIVERY_MODE") {
$APP_DELIVERY_MODE = $configData[$c]['vValue'];
} else if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "ENABLE_TOLL_COST") {
$ENABLE_TOLL_COST = $configData[$c]['vValue'];
} else if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "TOLL_COST_APP_ID") {
$TOLL_COST_APP_ID = $configData[$c]['vValue'];
} else if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "TOLL_COST_APP_CODE") {
$TOLL_COST_APP_CODE = $configData[$c]['vValue'];
} else if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "CHILD_SEAT_ACCESSIBILITY_OPTION") {
$CHILD_SEAT_ACCESSIBILITY_OPTION = $configData[$c]['vValue'];
} else if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "WHEEL_CHAIR_ACCESSIBILITY_OPTION") {
$WHEEL_CHAIR_ACCESSIBILITY_OPTION = $configData[$c]['vValue'];
}else if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "HANDICAP_ACCESSIBILITY_OPTION") {
$HANDICAP_ACCESSIBILITY_OPTION = $configData[$c]['vValue'];
} */
}
//print_r($TOLL_COST_APP_CODE);die;
$script = "booking";
$tbl_name = 'cab_booking';

function converToTz($time, $toTz, $fromTz, $dateFormat = "Y-m-d H:i:s")
{
    $date = new DateTime($time, new DateTimeZone($fromTz));
    $date->setTimezone(new DateTimeZone($toTz));
    $time = $date->format($dateFormat);
    return $time;
}

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$iCabBookingId = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : '';
$action = ($iCabBookingId != '') ? 'Edit' : 'Add';
//For Country
$sql = "SELECT vCountryCode,vCountry from country where eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);
$sql = "select cn.vCountryCode,cn.vCountry,cn.vPhoneCode,cn.vTimeZone from country cn inner join configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
$db_con = $obj->MySQLSelect($sql);
$vPhoneCode = $generalobjAdmin->clearPhone($db_con[0]['vPhoneCode']);
$vRideCountry = isset($_REQUEST['vRideCountry']) ? $_REQUEST['vRideCountry'] : $db_con[0]['vCountryCode'];
$vTimeZone = isset($_REQUEST['vTimeZone']) ? $_REQUEST['vTimeZone'] : $db_con[0]['vTimeZone'];
$vCountry = $db_con[0]['vCountryCode'];
$address = $db_con[0]['vCountry']; // Google HQ
$prepAddr = str_replace(' ', '+', $address);
$vPhoneOrg = "";
/* $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
$output= json_decode($geocode);
$latitude = $output->results[0]->geometry->location->lat;
$longitude = $output->results[0]->geometry->location->lng; */

$dBooking_date = "";
$sql1 = "SELECT *,vName_$default_lang FROM `package_type` WHERE eStatus='Active'";
$db_PackageType = $obj->MySQLSelect($sql1);
if ($action == 'Edit') {
    $sql = "SELECT $tbl_name.*,$tbl_name.fNightPrice as NightSurge,$tbl_name.fPickUpPrice as PickSurge,register_user.vPhone,register_user.vName,register_user.vLastName,register_user.vEmail,register_user.vPhoneCode,register_user.vCountry FROM " . $tbl_name . " LEFT JOIN register_user on register_user.iUserId=" . $tbl_name . ".iUserId WHERE " . $tbl_name . ".iCabBookingId = '" . $iCabBookingId . "'";
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
            $vPhoneOrg = $value['vPhone'];
            $vPhone = $generalobjAdmin->clearPhone($value['vPhone']);
            $vName = $generalobjAdmin->clearName(" " . $value['vName']);
            $vLastName = $generalobjAdmin->clearName(" " . $value['vLastName']);
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
            $vPhoneCode = $generalobjAdmin->clearPhone($value['vPhoneCode']);
            $vCountry = $value['vCountry'];
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
            $eChildSeatAvailable = $value['eChildSeatAvailable'];
            $eWheelChairAvailable = $value['eWheelChairAvailable'];
            $etype = $value['eType'];
            $eFlatTrip = $value['eFlatTrip'];
            $fFlatTripPrice = $value['fFlatTripPrice'];
            $eTollSkipped = $value['eTollSkipped'];
            $fTollPrice = $value['fTollPrice'];
            $vTollPriceCurrencyCode = $value['vTollPriceCurrencyCode'];
            $dBooking_date = converToTz($dBookingDate, $vTimeZone, $systemTimeZone);
            if ($etype == 'Ride') {
                $eRideType = 'later';
            } else if ($etype == 'Deliver') {
                $eDeliveryType = 'later';
            }
        }
    }
}
//Driversubscription added by SP
$driversubs = 0;
if ($DRIVER_SUBSCRIPTION_ENABLE == 'Yes') {
    $driversubs = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME;?> | Manual Booking</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link rel="stylesheet" href="css/select2/select2.min.css" type="text/css" >
        <?php include_once 'global_files.php';?>
        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>&libraries=places" type="text/javascript"></script>
        <script type='text/javascript' src='../assets/map/gmaps.js'></script>
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <script type='text/javascript' src='../assets/js/bootbox.min.js'></script>
        <link rel="stylesheet" href="../assets/css/manualstyle.css" />
    </head>
    <body class="padTop53">
        <div id="wrap">
            <?php include_once 'header.php';?>
            <?php include_once 'left_menu.php';?>
            <div id="content">
                <div class="inner" style="min-height: 700px;">
                    <div class="row">
                        <div class="col-lg-8">
                            <?php if ($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX") {?>
                                <h1> Manual <?=$langage_lbl_admin['LBL_TEXI_ADMIN'];?> Dispatch </h1>
                            <?php } else {?>
                                <h1> <?=$langage_lbl_admin['LBL_MANUAL_TAXI_DISPATCH'];?> </h1>
                            <?php }?>
                        </div>
                        <div class="col-lg-4">
                            <?php //if ($APP_TYPE != "UberX") {  ?>
                            <h1 class="float-right"><a class="btn btn-primary how_it_work_btn" data-toggle="modal" data-target="#myModal"><i class="fa fa-question-circle" style="font-size: 18px;"></i> How it works?</a></h1>
                            <?php /* } else { ?>
<h1 class="float-right"><a class="btn btn-primary how_it_work_btn" data-toggle="modal" data-target="#myModalufx"><i class="fa fa-question-circle" style="font-size: 18px;"></i> How it works?</a></h1>

<?php } */?>
                        </div>
                    </div>
                    <hr />
                    <form name="add_booking_form" id="add_booking_form" method="post" action="action_booking.php" >
                        <div class="form-group" style="display: inline-block; width:100%;">
                            <?php if ($success == "1") {?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php
echo ($vassign != "1") ? $langage_lbl_admin['LBL_RECORD_INSERT_MSG'] : $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ' Has Been Assigned Successfully.';
    ?>
                                </div>
                                <br/>
                            <?php }?>
                            <?php if ($success == 2) {?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?=$langage_lbl_admin['LBL_EDIT_DELETE_RECORD'];?>
                                </div>
                                <br/>
                            <?php }?>
                            <?php if ($success == 0 && $var_msg != "") {?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?=$var_msg;?>
                                </div>
                                <br/>
                            <?php }?>
                            <input type="hidden" name="previousLink" id="previousLink" value=""/>
                            <input type="hidden" name="eBookingFrom" id="eBookingFrom" value="Admin" />
                            <input type="hidden" name="backlink" id="backlink" value="cab_booking.php"/>
                            <input type="hidden" name="distance" id="distance" value="<?=$vDistance;?>">
                            <input type="hidden" name="duration" id="duration" value="<?=$vDuration;?>">
                            <input type="hidden" name="from_lat_long" id="from_lat_long" value="<?=$from_lat_long;?>" >
                            <input type="hidden" name="from_lat" id="from_lat" value="<?=$from_lat;?>" >
                            <input type="hidden" name="from_long" id="from_long" value="<?=$from_long;?>" >
                            <input type="hidden" name="to_lat_long" id="to_lat_long" value="<?=$to_lat_long;?>" >
                            <input type="hidden" name="to_lat" id="to_lat" value="<?=$to_lat;?>" >
                            <input type="hidden" name="to_long" id="to_long" value="<?=$to_long;?>" >
                            <input type="hidden" name="fNightPrice" id="fNightPrice" value="<?=$fNightPrice;?>" >
                            <input type="hidden" name="fPickUpPrice" id="fPickUpPrice" value="<?=$fPickUpPrice;?>" >
                            <input type="hidden" name="eFlatTrip" id="eFlatTrip" value="<?=$eFlatTrip;?>" >
                            <input type="hidden" name="fFlatTripPrice" id="fFlatTripPrice" value="<?=$fFlatTripPrice;?>" >
                            <input type="hidden" value="1" id="location_found" name="location_found">
                            <input type="hidden" value="" id="user_type" name="user_type" >
                            <input type="hidden" value="<?=$iUserId;?>" id="iUserId" name="iUserId" >
                            <input type="hidden" value="<?=$eStatus;?>" id="eStatus" name="eStatus" >
                            <input type="hidden" value="<?=$vTimeZone;?>" id="vTimeZone" name="vTimeZone" >
                            <input type="hidden" value="<?=$vRideCountry;?>" id="vRideCountry" name="vRideCountry" >
                            <input type="hidden" value="<?=$iCabBookingId;?>" id="iCabBookingId" name="iCabBookingId" >
                            <input type="hidden" value="<?=$GOOGLE_SEVER_API_KEY_WEB;?>" id="google_server_key" name="google_server_key" >
                            <input type="hidden" value="" id="getradius" name="getradius" >
                            <input type="hidden" value="KMs" id="eUnit" name="eUnit" >
                            <input type="hidden" name="fTollPrice" id="fTollPrice" value="<?=$fTollPrice?>">
                            <input type="hidden" name="vTollPriceCurrencyCode" id="vTollPriceCurrencyCode" value="<?=$vTollPriceCurrencyCode?>">
                            <input type="hidden" name="eTollSkipped" id="eTollSkipped" value="<?=$eTollSkipped?>">
                            <?php if ($APP_TYPE != 'Ride-Delivery' && $APP_TYPE != 'Ride-Delivery-UberX' || ($APP_TYPE == 'Ride-Delivery' && $APP_DELIVERY_MODE == "Multi")) {?>
                                <input type="hidden" value="<?=$etype?>" id="eType" name="eType" />
                            <?php }?>

                            <div class="add-booking-form-taxi add-booking-form-taxi1 col-lg-12"> <span class="col0">
                                    <select name="vCountry" id="vCountry" class="form-control form-control-select" onChange="changeCode(this.value, '<?=$iVehicleTypeId;?>');setDriverListing();setDriversMarkers('test')" required>
                                        <!-- <option value="">Select Country</option> -->
                                        <?php for ($i = 0; $i < count($db_code); $i++) {?>
                                            <option value="<?=$db_code[$i]['vCountryCode']?>"
                                            <?php
if ($db_code[$i]['vCountryCode'] == $vCountry) {
    echo "selected";
}
    ?> >
                                                        <?=$db_code[$i]['vCountry'];?>
                                            </option>
                                        <?php }?>
                                    </select>
                                </span>
                                <span class="col6">
                                    <input type="text" class="form-control add-book-input" name="vPhoneCode" id="vPhoneCode" value="<?=$vPhoneCode;?>" readonly />
                                </span>
                                <span class="col2">
                                    <input type="hidden" value="<?=$vPhoneOrg;?>" id="vPhoneOrg">
                                    <input type="text" pattern="[0-9]{1,}" title="Enter Mobile Number." class="form-control add-book-input" name="vPhone"  id="vPhone" value="<?=$vPhone;?>" placeholder="Enter Phone Number" onKeyUp="return isNumberKey(event)"  onblur="return isNumberKey(event)"  required  />
                                </span>
                                <span class="col3">
                                    <input type="text" class="form-control first-name1" name="vName"  id="vName" value="<?=$vName;?>" placeholder="First Name" required />
                                    <input type="text" class="form-control last-name1" name="vLastName"  id="vLastName" value="<?=$vLastName;?>" placeholder="Last Name" required />
                                </span>
                                <span class="col4" style="margin: 0px;">
                                    <input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" class="form-control" name="vEmail" id="vEmail" value="<?=$vEmail;?>" placeholder="Email" required >
                                    <div id="emailCheck"></div>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
if (($APP_TYPE == 'Ride-Delivery')) {
    include_once '../include/ride-delivery/add_booking_admin1.php';
}

if ($APP_TYPE == 'Ride-Delivery-UberX') {
    include_once '../include/ride-delivery-uberx/add_booking_admin2.php';
}
?>
                        </div>

                        <div class="map-main-page-inner">
                            <div class="map-main-page-inner-tab">
                                <div class="col-lg-12 map-live-hs-mid">
                                    <?php
if ($APP_TYPE == 'Ride-Delivery') {
    include_once '../include/ride-delivery/add_booking_admin3.php';
}

if ($APP_TYPE == 'Delivery') {
    include_once '../include/delivery/add_booking_admin4.php';
}

if ($APP_TYPE == 'Ride-Delivery-UberX') {
    include_once '../include/ride-delivery-uberx/add_booking_admin5.php';
}
?>

                                </div>
                                <div class="col-lg-12 map-live-hs-mid">
                                    <span class="col5">
                                        <div class="drop-location">
                                            <input type="text" class="ride-location1 highalert txt_active form-control first-name1" name="vSourceAddresss"  id="from" value="<?=$vSourceAddresss;?>" placeholder="<?=ucfirst(strtolower($langage_lbl_admin['LBL_PICKUP_LOCATION_HEADER_TXT']));?>" required onpaste="checkrestrictionfrom('from');">
                                            <div class="vSourceAddresssLocation"></div>
                                        </div>

                                        <?php if ($APP_TYPE != "UberX") {?>
                                            <div class="drop-location">
                                                <input type="text" class="ride-location1 highalert txt_active form-control last-name1" name="tDestAddress"  id="to" value="<?=$tDestAddress;?>" placeholder="Drop Off Location" required onpaste="checkrestrictionto('to');">
                                                <div class="tDestAddressLocation"></div>
                                            </div>
                                        <?php }?>
                                    </span>

                                    <span>
                                        <select class="form-control form-control-select form-control14" name='iVehicleTypeId' id="iVehicleTypeId" required onChange="showAsVehicleType(this.value)">
                                            <option value="" >Select <?=$langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];?></option>
                                        </select>
                                    </span>
                                    <span class="service-pickup-type">
                                        <h3 ><?=$langage_lbl_admin['LBL_SELECT_YOUR_PICKUP_TYPE_WEB'];?></h3>
                                        <!-- For Ride Options -->
                                        <div class="radio-but-type rideShow">
                                            <b>
                                                <input id="r3_eRideType" name="eRideType" type="radio" value="now" <?php if ($eRideType != 'later') {?> checked="" <?php }?>>
                                                <label for="r3_eRideType"><?=$langage_lbl_admin['LBL_RIDE_NOW'];?></label>
                                            </b>
                                            <?php if ($RIDE_LATER_BOOKING_ENABLED == 'Yes') {?>
                                                <b>
                                                    <input id="r4_eRideType" name="eRideType" type="radio" value="later" <?php if ($eRideType == 'later') {?> checked="" <?php }?>>
                                                    <label for="r4_eRideType"><?=$langage_lbl_admin['LBL_RIDE_LATER'];?></label>
                                                </b>
                                            <?php }?>
                                        </div>

                                        <!-- For Delivery Options -->
                                        <div class="radio-but-type deliveryShow" style="display:none;">
                                            <b><input id="r3_eDeliveryType" name="eDeliveryType" type="radio" checked='checked' value="now" <?php if ($eDeliveryType != 'later') {?> checked="" <?php }?>>
                                                <label for="r3_eDeliveryType"><?=$langage_lbl_admin['LBL_DELIVER_NOW_WEB'];?></label>
                                            </b>

                                        <?php if ($RIDE_LATER_BOOKING_ENABLED == 'Yes') {?>
                                            <b>
                                                <input id="r4_eDeliveryType" name="eDeliveryType" type="radio" value="later" <?php if ($eDeliveryType == 'later') {?> checked="" <?php }?>>
                                                <label for="r4_eDeliveryType"><?=$langage_lbl_admin['LBL_DELIVER_LATER_WEB'];?></label>

                                            </b>
                                        <?php }?>

                                        </div>
                                    </span>
                                    <!-- <div class="form-group">
                                    <b>
                                            <input id="r3_eRideType" name="eRideType" type="radio" value="now" <?php if ($eRideType != 'later') {?> checked="" <?php }?>>
                                            <label for="r3_eRideType"><?=$langage_lbl_admin['LBL_RIDE_NOW'];?></label>
                                        </b>
                                        <b>
                                            <input id="r4_eRideType" name="eRideType" type="radio" value="later" <?php if ($eRideType == 'later') {?> checked="" <?php }?>>
                                            <label for="r4_eRideType"><?=$langage_lbl_admin['LBL_RIDE_LATER'];?></label>
                                        </b>
                                </div> -->
                                    <span class="dateSchedule" style="display:none">
                                        <?php if (date("Y-m-d H:i:s") > $dBooking_date) {?>
                                            <input type="text" class="form-control form-control14" name="dBooking_date"  id="datetimepicker4" value="<?=$dBooking_date;?>" placeholder="Select Date / Time" onBlur="getFarevalues('');<?php if ($APP_TYPE == "UberX") {?>setDriverListing();<?php }?>" required>
                                        <?php } else {?>
                                            <input type="text" class="form-control form-control14" name="dBooking_date"  id="datetimepicker" value="<?=$dBooking_date;?>" placeholder="Select Date / Time" onBlur="getFarevalues('');<?php if ($APP_TYPE == "UberX") {?>setDriverListing();<?php }?>" required disabled="">
                                        <?php }?>
                                    </span>
                                    <?php
if ($APP_TYPE != 'UberX') {
    if ($APP_TYPE == 'Ride') {
        include_once '../include/ride/add_booking_admin6.php';
    }

    if ($APP_TYPE == 'Ride-Delivery') {
        include_once '../include/ride-delivery/add_booking_admin7.php';
    }

    if ($APP_TYPE == 'Ride-Delivery-UberX') {
        include_once '../include/ride-delivery-uberx/add_booking_admin8.php';
    }
    ?>

                                        <span class="auto_assign001 autoassignbtn">
                                            <input type="checkbox" name="eAutoAssign" id="eAutoAssign" value="Yes" <?php if ($eAutoAssign == 'Yes') {
        echo 'checked';
    }
    ?>>
                                            <p>Auto Assign <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?></p>
                                        </span>
                                        <span class="auto_assignOr">
                                            <h3>OR</h3>
                                        </span>
                                    <?php }?>
                                    <span id="showdriverSet001" style="display:none;"><p class="margin-right5">Assigned <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>: </p><p id="driverSet001"></p></span>
                                </div>
                                <div class="driverlists">
                                    <span class="add-booking1">
                                        <input name="" type="text" placeholder="Type <?=$langage_lbl_admin['LBL_DRIVER_PROVIDER'];?> name to search from below list" id="name_keyWord" onKeyUp="get_drivers_list(this.value)">
                                    </span>
                                    <ul id="driver_main_list" style="">
                                        <div class="" id="imageIcons" style="width:100%;">
                                            <div align="center">
                                                <img src="default.gif">
                                                <span>Retrieving <?=$langage_lbl_admin['LBL_DIVER'];?> list.Please Wait...</span>
                                            </div>
                                        </div>
                                    </ul>
                                    <input type="text" name="iDriverId" id="iDriverId" value="" required   class="form-control height-1" >
                                </div>
                            </div>
                            <div class="map-page">
                                <div class="panel-heading location-map" style="background:none;">
                                    <div class="google-map-wrap">
                                        <div class="map-color-code">
                                            <div>
                                                <label style="width: 20%;"><?=$langage_lbl_admin['LBL_PROVIDER_DRIVER_AVAILABILITY'];?> </label>
                                                <span class="select-map-availability"><select onChange="setNewDriverLocations(this.value)" id="newSelect02">
                                                        <option value='' data-id=""><?=$langage_lbl_admin['LBL_ALL'];?></option>
                                                        <option value="Available" data-id="img/green-icon.png"><?=$langage_lbl_admin['LBL_AVAILABLE'];?></option>
                                                        <option value="Active" data-id="img/red.png"><?=$langage_lbl_admin['LBL_ENROUTE_TO'];?></option>
                                                        <option value="Arrived" data-id="img/blue.png"><?=$langage_lbl_admin['LBL_REACHED_PICKUP'];?></option>
                                                        <option value="On Going Trip" data-id="img/yellow.png"><?=$langage_lbl_admin['LBL_JOURNEY_STARTED'];?></option>
                                                        <option value="Not Available" data-id="img/offline-icon.png"><?=$langage_lbl_admin['LBL_OFFLINE'];?></option>
                                                    </select></span>
                                            </div>
                                            <div style="margin-top: 15px;">
                                                <label style="width: 20%;">Map Zoom Level</label>
                                                <span>
                                                    <?php $radius_driver = array(5, 10, 20, 30);?>
                                                    <select class="form-control form-control-select form-control14" name='radius-id' id="radius-id" onChange="play(this.value)" style="width: 40%;display: inline-block;">
                                                        <option value=""> Select Radius </option>
                                                        <?php foreach ($radius_driver as $value) {?>
                                                            <option value="<?=$value?>"><?=$value . $DEFAULT_DISTANCE_UNIT . ' Radius';?></option>
                                                        <?php }?>
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                        <div id="map-canvas" class="google-map" style="width:100%; height:500px;"></div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($APP_TYPE != 'UberX') {?>
                                <div class="total-price total-price1" > <b>Fare Estimation</b>
                                    <hr>
                                    <ul id="estimatedata">

                                    </ul>
                                    <span>Total Fare<b>
                                            <em id="total_fare_price">0</em></b></span> </div>
                            <?php }?>

                            <!-- popup -->
                            <div class="map-popup" style="display:none" id="driver_popup"></div>
                            <!-- popup end -->
                        </div>
                        <input type="hidden" name="newType" id="newType" value="">
                        <input type="hidden" name="submitbtn" id="submitbtn">
                        <div style="clear:both;"></div>
                        <div class="book-now-reset add-booking-button"><span>
                                <input type="submit" class="save btn-info button-submit" name="submitbutton" id="submitbutton" value="Book">
                                <input type="reset" class="save btn-info button-submit" name="reset" id="reset12" value="Reset" >
                            </span></div>
                    </form>

                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>
                                Administrator can Add / Edit <?=$langage_lbl_admin['LBL_RIDER_RIDE_MAIN_SCREEN'];?> later booking on this page.
                            </li>
                            <li>
                                <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> current availability is not connected with booking being made. Please confirm future availability of <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> before doing booking.
                            </li>
                            <li>Adding booking from here will not send request to <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> immediately.</li>
                            <li>In case of "Auto Assign <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>" option selected, <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>(s) get automatic request before 8-12 minutes of actual booking time.</li>
                            <li>In case of "Auto Assign <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>" option not selected, <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>(s) get booking confirmation sms as well as reminder sms before 30 minutes of actual booking. <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> has to start the scheduled <?=$langage_lbl_admin['LBL_TRIP_TXT_ADMIN'];?> by going to "Your <?=$langage_lbl_admin['LBL_TRIP_TXT_ADMIN'];?>" >> Upcoming section from <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> App.</li>
                            <li>In case of "Auto Assign <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>", the competitive algorithm will be followed instead of one you have selected in settings.</li>
                        </ul>
                    </div>

                </div>
                <!--END PAGE CONTENT -->

            </div>
            <?php include_once 'footer.php';?>
            <div style="clear:both;"></div>

            <!-- added by SP for block driver start -->
            <div class="modal fade" id="blockdrivermodel" tabindex="-1" role="dialog" aria-labelledby="blockdrivermodel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                            <h4 class="modal-title" id="inactiveModalLabel">Block driver</h4>
                        </div>
                        <div class="modal-body">
                            <p><span style="font-size: 15px;">Driver is block so you can not assign it.</span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- added by SP for block driver end -->

            <?php if ($driversubs == 1) {?>
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
                                <p><span style="font-size: 15px;"> This <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> is having low balance in his wallet and is not able to accept cash ride. Would you still like to assign this <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>?</span></p>
                                <p><b style="font-size: 15px;"> Minimum Required Balance : </b><span style="font-size: 15px;"><?=$generalobj->symbol_currency() . " " . number_format($WALLET_MIN_BALANCE, 2);?></span></p>
                                <p><b style="font-size: 15px;"> Available Balance : </b><span style="font-size: 15px;"><?=$generalobj->symbol_currency();?> <span id="usr-bal"></span></span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button>
                                <button type="button" class="btn btn-success btn-ok action_modal_submit" data-dismiss="modal" onClick="checkdriversubscription('');">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end Wallet Low Balance-->

                <!-- Driversubscription added by SP  -->
                <div class="modal fade" id="driversubscriptionmodel" tabindex="-1" role="dialog" aria-labelledby="driversubscriptionmodel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                                <h4 class="modal-title" id="inactiveModalLabel">Driver Subscription</h4>
                            </div>
                            <div class="modal-body">
                                <p><span style="font-size: 15px;">Driver is not subscribe to any plan. Do you want to still assign it?</span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button>
                                <button type="button" class="btn btn-success btn-ok action_modal_submit" data-dismiss="modal" onClick="AssignDriver('');">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else {?>
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
                                <p><span style="font-size: 15px;"> This <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> is having low balance in his wallet and is not able to accept cash ride. Would you still like to assign this <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>?</span></p>
                                <p><b style="font-size: 15px;"> Minimum Required Balance : </b><span style="font-size: 15px;"><?=$generalobj->symbol_currency() . " " . number_format($WALLET_MIN_BALANCE, 2);?></span></p>
                                <p><b style="font-size: 15px;"> Available Balance : </b><span style="font-size: 15px;"><?=$generalobj->symbol_currency();?> <span id="usr-bal"></span></span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button>
                                <button type="button" class="btn btn-success btn-ok action_modal_submit" data-dismiss="modal" onClick="AssignDriver('');">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end Wallet Low Balance-->
            <?php }?>

            <!--user inactive/deleted-->
            <div class="modal fade" id="inactiveModal" tabindex="-1" role="dialog" aria-labelledby="inactiveModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                            <h4 class="modal-title" id="inactiveModalLabel">User Detail</h4>
                        </div>
                        <div class="modal-body">
                            <span style="font-size: 15px;"> User is inactive/deleted. Do you want to book a ride with user?</span>
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
                            <button type="button" class="btn btn-success btn-ok action_modal_submit" data-dismiss="modal" onClick="">OK</button>
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
                                var APP_DELIVERY_MODE = '<?=$APP_DELIVERY_MODE?>';
                                var ENABLE_TOLL_COST = "<?=$ENABLE_TOLL_COST?>";
                                // alert(APP_DELIVERY_MODE);
                                switch ("<?=$APP_TYPE;?>") {
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
                                        $('.deliveryShow').hide();
                                        $('.rideShow').show();
                                        $('.vehicle-type-ufx').hide();
                                        $('.tolabel').show();
                                        $('.service-pickup-type').show();
                                        if ($('input[name=eRideType]:checked').val() == 'now') {
                                            $(".dateSchedule").hide();
                                            $(".autoassignbtn").hide();
                                            $('#datetimepicker4').removeAttr('required');
                                        } else {
                                            $(".autoassignbtn").show();
                                            $(".dateSchedule").show();
                                            $('#datetimepicker4').attr('required', 'required');
                                        }
                                    } else if (etype == 'Deliver') {
                                        $('#ride-delivery-type').show();
                                        $('#ride-type').hide();
                                        $('.auto_assign001').show();
                                        $('#iPackageTypeId').attr('required', 'required');
                                        $('#vReceiverMobile').attr('required', 'required');
                                        $('#vReceiverName').attr('required', 'required');
                                        $('#to').show();
                                        $('.total-price1').show();
                                        $('.deliveryShow').show();
                                        $('.rideShow').hide();
                                        $('.vehicle-type-ufx').hide();
                                        $('.tolabel').show();
                                        $('.service-pickup-type').show();

                                        if ($('input[name=eDeliveryType]:checked').val() == 'now') { // Check if Delivery now or later
                                            $(".dateSchedule").hide(); // Hide date option
                                            $('#datetimepicker4').removeAttr('required');
                                        } else {
                                            $(".dateSchedule").show(); // Show date option
                                            $('#datetimepicker4').attr('required', 'required');
                                        }
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
                                        $('.deliveryShow').hide();
                                        $('.rideShow').hide();
                                        $('.vehicle-type-ufx').show();
                                        $('.tolabel').hide();
                                        $('.service-pickup-type').hide();
                                        $(".dateSchedule").show();
                                        $('#datetimepicker4').attr('required', 'required');
                                    }
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
                                // var geocoder;
                                var circle;
                                var markers = [];
                                var driverMarkers = [];
                                var bounds = [];
                                var newLocations = "";
                                var autocomplete_from;
                                var autocomplete_to;
                                var eLadiesRide = 'No';
                                var eHandicaps = 'No';
                                var eChildSeat = 'No';
                                var eWheelChair = 'No';
                                // var geocoder = new google.maps.Geocoder();
                                var directionsService = new google.maps.DirectionsService(); // For Route Services on map
                                var directionsOptions = {// For Polyline Route line options on map
                                    polylineOptions: {
                                        strokeColor: '#FF7E00',
                                        strokeWeight: 5
                                    }
                                };
                                var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
                                var showsurgemodal = "Yes";
                                var driversubs = <?=$driversubs;?>;

                                function setDriverListing(iVehicleTypeId) {
                                    dBooking_date = $("#datetimepicker4").val();
                                    vCountry = $("#vCountry").val();
                                    keyword = $("#name_keyWord").val();
                                    eLadiesRide = 'No';
                                    eHandicaps = 'No';
                                    eChildSeat = 'No';
                                    eWheelChair = 'No';
                                    if ($("#eFemaleDriverRequest").is(":checked")) {
                                        eLadiesRide = 'Yes';
                                    }
                                    if ($("#eHandiCapAccessibility").is(":checked")) {
                                        eHandicaps = 'Yes';
                                    }
                                    if ($("#eChildSeatAvailable").is(":checked")) {
                                        eChildSeat = 'Yes';
                                    }
                                    if ($("#eWheelChairAvailable").is(":checked")) {
                                        eWheelChair = 'Yes';
                                    }
                                    // alert(eLadiesRide);
                                    $.ajax({
                                        type: "POST",
                                        url: "get_available_driver_list.php",
                                        dataType: "html",
                                        data: {vCountry: vCountry, type: '', iVehicleTypeId: iVehicleTypeId, keyword: keyword, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, eChildSeat: eChildSeat, eWheelChair: eWheelChair, dBooking_date: dBooking_date, AppeType: eType},
                                        success: function (dataHtml2) {
                                            if (dataHtml2 != "") {
                                                $('#driver_main_list').show();
                                                $('#driver_main_list').html(dataHtml2);
                                                if ($("#eAutoAssign").is(':checked')) {
                                                    $(".assign-driverbtn").attr('disabled', 'disabled');
                                                }
                                            } else {
                                                $('#driver_main_list').html('<h4 style="margin:25px 0 0 15px">Sorry , No <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> Found.</h4>');
                                                $('#driver_main_list').show();
                                            }
                                        }, error: function (dataHtml2) {

                                        }
                                    });
                                }

                                function AssignDriver(driverId) {
                                    if ($("#iVehicleTypeId").val() != '') {
                                        if (driverId == "") {
                                            driverId = $('#iDriverId_temp').val();
                                        }
                                        $('#iDriverId').val(driverId);
                                        $("#showdriverSet001").show();
                                        $("#driverSet001").html($('.driver_' + driverId).html());
                                    } else {
                                        var messagesText = "Please Select Vehicle Type.";
                                        if (eType == "UberX") {
                                            messagesText = "Please Select Service Type.";
                                        }
                                        alert(messagesText);
                                        return false;
                                    }
                                }

                                /* Driversubscription added by SP */
                                function checkdriversubscription(driverId) {
                                    if (driverId == "") {
                                        driverId = $('#iDriverId_temp').val();
                                    }
                                    $.ajax({
                                        type: "POST",
                                        url: "ajax_driver_subscription.php",
                                        data: "driverId=" + driverId + "&type=Driver",
                                        success: function (data) {
                                            if (data != '' && data == 1) {
                                                $('#iDriverId_temp').val(driverId);
                                                $("#driversubscriptionmodel").modal('show');
                                                return false;
                                            } else {
                                                AssignDriver(driverId);
                                            }
                                        }
                                    });
                                }
                                /* Driversubscription added by SP */

                                function checkUserBalance(driverId) {
                                    //added by SP for block driver not send request
                                    $.ajax({
                                        type: "POST",
                                        url: "ajax_check_block_driver.php",
                                        data: "driverId=" + driverId,
                                        success: function (data) {
                                            if (data == 'Yes') {
                                                $("#blockdrivermodel").modal('show');
                                                return false;
                                            } else {
                                                $.ajax({
                                                    type: "POST",
                                                    url: "ajax_get_user_balance.php",
                                                    data: "driverId=" + driverId + "&type=Driver",
                                                    success: function (data) {
                                                        data1 = data.split("|");
                                                        var CDE = '<?=$COMMISION_DEDUCT_ENABLE?>';
                                                        var Min_Bal = '<?=$WALLET_MIN_BALANCE?>';
                                                        if (CDE == "Yes") {
                                                            if (parseFloat(data1[1]) < parseFloat(Min_Bal)) {
                                                                var amt = parseFloat(data1[1]).toFixed(2);
                                                                $("#usr-bal").text(amt);
                                                                $("#iDriverId_temp").val(driverId);
                                                                $("#usermodel").modal('show');
                                                                return false;
                                                            } else {
                                                                /* Driversubscription added by SP */
                                                                if (driversubs == 1) {
                                                                    checkdriversubscription(driverId);
                                                                } else {
                                                                    AssignDriver(driverId);
                                                                }
                                                                return false;
                                                            }
                                                        } else {
                                                            /* Driversubscription added by SP */
                                                            if (driversubs == 1) {
                                                                checkdriversubscription(driverId);
                                                            } else {
                                                                AssignDriver(driverId);
                                                            }
                                                            return false;
                                                        }
                                                    }, error: function (dataHtml2) {

                                                    }
                                                });
                                            }
                                        }
                                    });
                                }

                                function setDriversMarkers(flag) {
                                    newType = $("#newType").val();
                                    vType = $("#iVehicleTypeId").val();
                                    vCountry = $("#vCountry").val();
                                    eLadiesRide = "No";
                                    eHandicaps = "No";
                                    eChildSeat = "No";
                                    eWheelChair = "No";
                                    if ($("#eFemaleDriverRequest").is(":checked")) {
                                        eLadiesRide = 'Yes';
                                    }
                                    if ($("#eHandiCapAccessibility").is(":checked")) {
                                        eHandicaps = 'Yes';
                                    }
                                    if ($("#eChildSeatAvailable").is(":checked")) {
                                        eChildSeat = 'Yes';
                                    }
                                    if ($("#eWheelChairAvailable").is(":checked")) {
                                        eWheelChair = 'Yes';
                                    }
                                    dBooking_date = $("#datetimepicker4").val();
                                    $.ajax({
                                        type: "POST",
                                        /*url: "get_map_drivers_list.php",
                                        dataType: "json",
                                        data: {vCountry: vCountry, type: newType, iVehicleTypeId: vType, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, eChildSeat: eChildSeat, eWheelChair: eWheelChair},*/
                                        url: "get_available_driver_list.php",
                                        dataType: "json",
                                        data: {map:1, vCountry: vCountry, type: '', iVehicleTypeId: vType, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, eChildSeat: eChildSeat, eWheelChair: eWheelChair, dBooking_date: dBooking_date, AppeType: eType},
                                        success: function (dataHtml) {
                                            for (var i = 0; i < driverMarkers.length; i++) {
                                                driverMarkers[i].setMap(null);
                                            }
                                            newLocations = dataHtml.locations;
                                            var infowindow = new google.maps.InfoWindow();
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
                                                        //console.log(newLat);console.log(newLong);

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
                                            if (flag != 'test') {
                                                var bounds = new google.maps.LatLngBounds();
                                                for (var i = 0; i < driverMarkers.length; i++) {
                                                    bounds.extend(driverMarkers[i].getPosition());
                                                }
                                                //console.log(bounds);
                                                //map.fitBounds(bounds);
                                                map.setZoom(13);
                                            }
                                            setDriverListing(vType);
                                        },
                                        error: function (dataHtml) {

                                        }
                                    });
                                }


                                function initialize() {

                                    var thePoint = new google.maps.LatLng('20.1849963', '64.4125062');
                                    var mapOptions = {
                                        zoom: 4,
                                        center: thePoint
                                    };
                                    map = new google.maps.Map(document.getElementById('map-canvas'),
                                            mapOptions);

                                    circle = new google.maps.Circle({radius: 25, center: thePoint});
                                    // map.fitBounds(circle.getBounds());
                                    if (eType == "Deliver" || eType == "Ride") {
                                        show_type(eType);
                                    }
                                    showVehicleCountryVise('<?=$vCountry?>', '<?=$iVehicleTypeId;?>', eType);
<?php if ($action == "Edit") {?>
                                        callEditFundtion();
                                        // show_locations();
                                        // showVehicleCountryVise('<?=$vCountry?>','<?=$iVehicleTypeId;?>');
<?php }?>
                                    //setDriversMarkers('test');
                                    //alert('test');
                                }

                                $(document).ready(function () {
                                        $('#from').keyup(function (e) {
                                                buildAutoComplete("from",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
                                                    show_locations();
                                                }); // (orignal function)

                                            });

                                            $('#to').keyup(function (e) {
                                                buildAutoComplete("to",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
                                                    show_locations();
                                                }); // (orignal function)

                                            });
                                    //eType = $('input[name=eType]:checked').val(); //its because in deliver edit and multi delivery then etype ride..but now there is no option for edit in multi delivery for deliver
                                    google.maps.event.addDomListener(window, 'load', initialize);
                                    setDriversMarkers('test');
                                    $("#eType").val(eType);
                                    $('input[type=radio][name=eType]').change(function () {
                                        eType = $('input[name=eType]:checked').val();
                                    });
                                    show_type(eType);
                                });

                                function play(radius) {
                                    // return Math.round(14-Math.log(radius)/Math.LN2);
                                    var pt = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());
                                    map.setCenter(pt);
                                    var newRadius = Math.round(24 - Math.log(radius) / Math.LN2);
                                    newRadius = newRadius - 9;
                                    map.setZoom(newRadius);
                                }
                                function getAddress(mDlatitude, mDlongitude, addId,setLatLongField,oldlat,oldlong,oldlatlong,oldAddress) {
                                    var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);
                                    var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);
                                var result = getReverseGeoCode(addId,setLatLongField,"<?=$_SESSION['sess_lang'];?>",mDlatitude, mDlongitude, oldlat, oldlong, oldlatlong, oldAddress, function(latitude, longitude, address){
                                    show_locations();
                                    // geocoder.geocode({'latLng': mylatlang},
                                    //         function (results, status) {
                                    //             // console.log(results);
                                    //             if (status == google.maps.GeocoderStatus.OK) {
                                    //                 if (results[0]) {
                                    //                     // document.getElementById(addId).value = results[0].formatted_address;
                                    //                     $('#' + addId).val(results[0].formatted_address);
                                    //                 } else {
                                    //                     document.getElementById('#' + addId).value = "No results";
                                    //                 }
                                    //             } else {
                                    //                 document.getElementById('#' + addId).value = status;
                                    //             }
                                    //         });
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
                                        draggable: true,
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
                                            // var lat = event.latLng.lat();
                                            // var lng = event.latLng.lng();
                                            // var myLatlongs = new google.maps.LatLng(lat, lng);
                                            // showsurgemodal = "No";

                                            // $("#from_lat").val(lat);
                                            // $("#from_long").val(lng);
                                            // $("#from_lat_long").val(myLatlongs);
                                            // getAddress(lat, lng, 'from');
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

                                function createPolyLine(cus_polyline) {
                                    if(typeof flightPath !== 'undefined'){
                                        flightPath.setMap(null);
                                        flightPath ='';
                                    }
                                    flightPath = cus_polyline;
                                    flightPath.setMap(map);
                                }

                                function routeDirections() {
                                    if (directionsDisplay != null) {
                                        directionsDisplay.setMap(null);
                                        directionsDisplay = null;
                                    }
                                    var directionsDisplay = new google.maps.DirectionsRenderer();
                                    if (($("#from").val() != "" && $("#from_lat_long").val() != "") && ($("#to").val() != "" && $("#to_lat_long").val() != "")) {
                                        var newFrom = $("#from_lat").val() + ", " + $("#from_long").val();
                                        if (eType == 'UberX') {
                                            var newTo = $("#from_lat").val() + ", " + $("#from_long").val();
                                        } else {
                                            var newTo = $("#to_lat").val() + ", " + $("#to_long").val();
                                        }
                                        //Make an object for setting route
                                        var request = {
                                            origin: newFrom, // From locations latlongs
                                            destination: newTo, // To locations latlongs
                                            travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
                                        };

                                        var source_latitude = $("#from_lat").val();
                                        var source_longitude = $("#from_long").val();
                                        var dest_latitude = $("#to_lat").val();
                                        var dest_longitude = $("#to_long").val();
                                        var waypoint0 = newFrom;
                                        var waypoint1 = newTo;
                                        //Draw route from the object
                                        getReverseGeoDirectionCode(source_latitude,source_longitude,dest_latitude,dest_longitude,waypoint0,waypoint1,function(data_response){
                                            directionsDisplay.setMap(null);
                                            if (MAPS_API_REPLACEMENT_STRATEGY.toUpperCase() == 'NONE'){
                                                    $("#distance").val(data_response.routes[0].legs[0].distance.value);
                                                    $("#duration").val(data_response.routes[0].legs[0].duration.value);
                                                    var points = data_response.routes[0].overview_polyline.points;
                                                    var polyPoints = google.maps.geometry.encoding.decodePath(points);
                                                            directionsDisplay.setMap(null);
                                                            directionsDisplay.setMap(map);
                                                            directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
                                                            createPolyLine(new google.maps.Polyline({path: polyPoints,strokeColor: '#FF7E00',strokeWeight: 5}));
                                                            temp_points = polyPoints = points = '';
                                                            data_response = [];

                                        }else{
                                            // directionsDisplay.setMap(null);
                                            directionsDisplay.setDirections({routes: []});
                                            var DePoly = '';
                                            var polyPoints = '';
                                            var flightPath = '';
                                            $("#distance").val(data_response.distance);
                                            $("#duration").val(data_response.duration);
                                            var polyLinesArr = new Array();
                                            var i;
											if((data_response.data != 'undefined') && (data_response.data != undefined)){
                                                for (i = 0; i < data_response.data.length; i++) {
                                                    polyLinesArr.push({ lat: parseFloat(data_response.data[i].latitude), lng: parseFloat(data_response.data[i].longitude)});
                                                    }
											}
                                            polyPoints = polyLinesArr;
											directionsDisplay.setMap(null);
											// directionsDisplay.setMap(map);
											directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
											createPolyLine(new google.maps.Polyline({path: polyPoints,strokeColor: '#FF7E00',strokeWeight: 5}));

                                    }
                                            var dist_fare = parseFloat($("#distance").val(), 10) / parseFloat(1000, 10);
                                            // alert(dist_fare);
                                            if ($("#eUnit").val() != 'KMs') {
                                                dist_fare = dist_fare * 0.621371;
                                            }
                                            // alert(dist_fare);
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
                                    });
                                        // directionsService.route(request, function (response, status) {
                                        //     if (status == google.maps.DirectionsStatus.OK) {
                                        //         // Check for allowed and disallowed.
                                        //         var response1 = JSON.stringify(response);
                                        //         directionsDisplay.setMap(map);
                                        //         directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
                                        //         directionsDisplay.setDirections(response); // Set route
                                        //         var route = response.routes[0];
                                        //         for (var i = 0; i < route.legs.length; i++) {
                                        //             $("#distance").val(route.legs[i].distance.value);
                                        //             $("#duration").val(route.legs[i].duration.value);
                                        //         }

                                        //         var dist_fare = parseFloat($("#distance").val(), 10) / parseFloat(1000, 10);
                                        //         // alert(dist_fare);
                                        //         if ($("#eUnit").val() != 'KMs') {
                                        //             dist_fare = dist_fare * 0.621371;
                                        //         }
                                        //         // alert(dist_fare);
                                        //         $('#dist_fare').text(dist_fare.toFixed(2));
                                        //         var time_fare = parseFloat($("#duration").val(), 10) / parseFloat(60, 10);
                                        //         $('#time_fare').text(time_fare.toFixed(2));
                                        //         var vehicleId = $('#iVehicleTypeId').val();
                                        //         var booking_date = $('#datetimepicker4').val();
                                        //         var vCountry = $('#vCountry').val();
                                        //         var tollcostval = $('#fTollPrice').val();
                                        //         var userId = $('#iUserId').val();
                                        //         var timeVal = parseFloat($("#duration").val(), 10) / parseFloat(60, 10);
                                        //         var distanceVal = parseFloat($("#distance").val(), 10) / parseFloat(1000, 10);
                                        //         $.ajax({
                                        //             type: "POST",
                                        //             url: 'ajax_estimate_by_vehicle_type.php',
                                        //             dataType: 'json',
                                        //             data: {'vehicleId': vehicleId, 'booking_date': booking_date, 'vCountry': vCountry, 'FromLatLong': newFrom, 'ToLatLong': newTo, 'timeduration': timeVal, 'distance': distanceVal, 'userId': userId},
                                        //             success: function (dataHtml)
                                        //             {
                                        //                 if (dataHtml != "") {
                                        //                     var estimateData = dataHtml.estimateArr;
                                        //                     var totalFare = dataHtml.totalFare;
                                        //                     var estimateHtml = "";
                                        //                     for (var i = 0; i < estimateData.length; i++) {
                                        //                         console.log(estimateData[i])
                                        //                         var eKey = estimateData[i]['key'];
                                        //                         var eVal = estimateData[i]['value']
                                        //                         estimateHtml += '<li><b>' + eKey + '</b> <em>' + eVal + '</em></li>';
                                        //                     }
                                        //                     $("#total_fare_price").text(totalFare);
                                        //                     $("#estimatedata").html(estimateHtml);
                                        //                 } else {
                                        //                     $('#minimum_fare_price,#base_fare_price,#dist_fare_price,#time_fare_price,#total_fare_price').text('0');
                                        //                 }
                                        //             }
                                        //         });
                                        //     } else {
                                        //         alert("Directions request failed: " + status);
                                        //     }
                                        // });

<?php if ($iVehicleTypeId != "") {?>
                                            var iVehicleTypeId = '<?=$iVehicleTypeId?>';
                                            getFarevalues(iVehicleTypeId);
                                            showAsVehicleType(iVehicleTypeId);
<?php }?>

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
                                function from_to(from, to) {
                                    DeleteMarkers('from_loc');
                                        var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());
                                        setMarker(latlng, 'from_loc');
                                        DeleteMarkers('to_loc');
                                        var latlng_to = new google.maps.LatLng($("#to_lat").val(), $("#to_long").val());
                                        setMarker(latlng_to, 'to_loc');
                                    routeDirections();
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

                                $(function () {
                                    var today = new Date();
                                    $('#datetimepicker4').datetimepicker({
                                        format: 'YYYY-MM-DD HH:mm:ss',
                                        minDate: moment(),
                                        ignoreReadonly: true,
                                        sideBySide: true,
                                    }).on('dp.change', function (e) {
                                        $('#datetimepicker4').data("DateTimePicker").minDate(formatDate(today))
                                    });

                                    // var from = document.getElementById('from');
                                    // autocomplete_from = new google.maps.places.Autocomplete(from);
                                    // google.maps.event.addListener(autocomplete_from, 'place_changed', function () {

                                    //     //setTimeout(function(){
                                    //     var place = autocomplete_from.getPlace();
                                    //     ///}, 1000);

                                    //     $("#from_lat_long").val(place.geometry.location);
                                    //     $("#from_lat").val(place.geometry.location.lat());
                                    //     $("#from_long").val(place.geometry.location.lng());
                                    //     // remove disable from zoom level when from has value
                                    //     $('#radius-id').prop('disabled', false);
                                    //     // routeDirections();
                                    //     if (from != '') {
                                    //         checkrestrictionfrom('from');
                                    //     }
                                    //     show_locations();
                                    // });
                                    setTimeout(function () {
                                        var dtaa = $(".pac-container:eq(0)");
                                        $(".vSourceAddresssLocation").html(dtaa);
                                    }, 1000);
                                    // var to = document.getElementById('to');
                                    // autocomplete_to = new google.maps.places.Autocomplete(to);
                                    // google.maps.event.addListener(autocomplete_to, 'place_changed', function () {
                                    //     var place = autocomplete_to.getPlace();
                                    //     $("#to_lat_long").val(place.geometry.location);
                                    //     $("#to_lat").val(place.geometry.location.lat());
                                    //     $("#to_long").val(place.geometry.location.lng());
                                    //     // routeDirections();
                                    //     if (to != '') {
                                    //         checkrestrictionto('to');
                                    //     }
                                    //     show_locations();
                                    // });
                                    setTimeout(function () {
                                        var dtaa = $(".pac-container:eq(1)");
                                        $(".tDestAddressLocation").html(dtaa);
                                    }, 1000);
                                });
                                function formatDate(date) {
                                    var d = new Date(date),
                                            month = '' + (d.getMonth() + 1),
                                            day = '' + d.getDate(),
                                            year = d.getFullYear();

                                    if (month.length < 2)
                                        month = '0' + month;
                                    if (day.length < 2)
                                        day = '0' + day;

                                    return [year, month, day].join('-');
                                }
                                function isNumberKey(evt) {
<?php if ($action != "Edit") {?>
                                        showPhoneDetail();
<?php }?>
                                    var charCode = (evt.which) ? evt.which : evt.keyCode
                                    if (charCode > 31 && (charCode < 35 || charCode > 57)) {
                                        return false;
                                    } else {
                                        return true;
                                    }
                                }
                                function changeCode(id, vehicleId) {
                                    // alert(id);
                                    $.ajax({
                                        type: "POST",
                                        url: 'change_code.php',
                                        dataType: 'json',
                                        data: {id: id, eUnit: 'yes'},
                                        success: function (dataHTML)
                                        {
                                            document.getElementById("vPhoneCode").value = dataHTML.vPhoneCode;
                                            document.getElementById("eUnit").value = dataHTML.eUnit;
                                            document.getElementById("vRideCountry").value = dataHTML.vCountryCode;
                                            document.getElementById("vTimeZone").value = dataHTML.vTimeZone;
                                            $("#change_eUnit").text(dataHTML.eUnit);
                                            var substr = <?=json_encode($radius_driver);?>;
                                            substr.forEach(function (item) {
                                                $('#radius-id option[value="' + item + '"]').text(item + " " + dataHTML.eUnit + ' Radius');
                                            });
                                            showPhoneDetail();
                                            showVehicleCountryVise(id, vehicleId, eType);
                                        }
                                    });
                                }
                                $(document).ready(function () {
                                    var con = $("#vCountry").val();
                                    changeCode(con, '<?=$iVehicleTypeId;?>');
                                    if ($("#from").val() == "") {
                                        $('#radius-id').prop('disabled', 'disabled');
                                    } else {
                                        $('#radius-id').prop('disabled', false);
                                    }
                                });
                                $('#from').on('change', function () {
                                    if (this.value == '') {
                                        $('#radius-id').prop('disabled', 'disabled');
                                    } else {
                                        $('#radius-id').prop('disabled', false);
                                    }
                                });
                                function showPopupDriver(driverId) {
                                    if ($("#driver_popup").is(":visible") && $('#driver_popup ul').attr('class') == driverId) {
                                        $("#driver_popup").hide("slide", {direction: "right"}, 700);
                                    } else {
                                        //alert(driverId);
                                        $("#driver_popup").hide();
                                        $.ajax({
                                            type: "POST",
                                            url: "get_driver_detail_popup.php",
                                            dataType: "html",
                                            data: {driverId: driverId},
                                            success: function (dataHtml2) {
                                                $('#driver_popup').html(dataHtml2);
                                                $("#driver_popup").show("slide", {direction: "right"}, 700);
                                            }, error: function (dataHtml2) {

                                            }
                                        });
                                    }
                                }
                                function showVehicleCountryVise(countryId, vehicleId, eType) {
                                    var countryId = $("#vCountry").val(); // Added By HJ On 06-11-2019 For Solved Bug - 447 Of Sheet
                                    //added by SP for get vehicles/services according to the pickup location on 02-08-2019 start
                                    var from_lat = from_long = '';
                                    if ($("#from_lat").val() != '') {
                                        var from_lat = $("#from_lat").val();
                                        var from_long = $("#from_long").val();
                                    }
                                    //added by SP for get vehicles/services according to the pickup location on 02-08-2019 end
                                    $.ajax({
                                        type: "POST",
                                        url: "ajax_booking_details.php",
                                        dataType: "html",
                                        data: {countryId: countryId, type: 'getVehicles', iVehicleTypeId: vehicleId, eType: eType, from_lat: from_lat, from_long: from_long}, //added by SP for get vehicles/services according to the pickup location on 02-08-2019
                                        success: function (dataHtml2) {
                                            $('#iVehicleTypeId').html(dataHtml2);
                                            // $("#driver_popup").show("slide", {direction: "right"}, 700);
                                        }, error: function (dataHtml2) {

                                        }
                                    });
                                    $("#total_fare_price").text('0.00');
                                    $("#estimatedata").html("");
                                }
                                $(document).mouseup(function (e)
                                {
                                    var container = $("#driver_popup");
                                    var container1 = $("#driver_main_list");
                                    if (!container.is(e.target) && !container1.is(e.target) // if the target of the click isn't the container...
                                            && container.has(e.target).length === 0 && container1.has(e.target).length === 0) // ... nor a descendant of the container
                                    {
                                        container.hide("slide", {direction: "right"}, 700);
                                    }
                                });
                                function showPhoneDetail() {
                                    var phone = $("#vPhoneOrg").val();
<?php if ($action == 'Add' || phone == "") {?>
                                        var phone = $('#vPhone').val();
<?php }?>
                                    var phoneCode = $('#vPhoneCode').val();
                                    if (phone != "" && phoneCode != "") {
                                        $.ajax({
                                            type: "POST",
                                            url: 'ajax_find_rider_by_number.php',
                                            data: {phone: phone, phoneCode: phoneCode},
                                            success: function (dataHtml)
                                            {
                                                if ($.trim(dataHtml) != "") {
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
<?php if ($action == 'Edit') {?>
                                                        $('#vPhone').attr('readonly', true);
<?php }?>
                                                    $('#vName,#vLastName,#vEmail').attr('readonly', true);
                                                } else {
                                                    $("#user_type,#vName,#vLastName,#vEmail,#iUserId,#eStatus").val('');
                                                    $('#vName,#vLastName,#vEmail,#vPhone').attr('readonly', false);
                                                }
                                                var maskPhone = $('#vPhone').val();
                                                $('#vPhoneOrg').val(maskPhone);
<?php if ($action == 'Edit') {?>
                                                    $("#vCountry").attr('disabled', 'disabled');
<?php }?>
                                            }
                                        });
                                    } else {
                                        $("#user_type,#vName,#vLastName,#vEmail,#iUserId,#eStatus").val('');
                                    }
                                }

                                function setNewDriverLocations(type) {
                                    // alert(type);
                                    $("#newType").val(type);
                                    vType = $("#iVehicleTypeId").val();
                                    for (var i = 0; i < driverMarkers.length; i++) {
                                        driverMarkers[i].setMap(null);
                                    }
                                    //console.log(newLocations);
                                    //return false;
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
                                    setDriverListing(vType);
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
                                    // alert(vehicleId);
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
                                        // setDriverListing(vehicleId);
                                        // getDriversList(vehicleId);
                                    }
                                }

                                function showAsVehicleType(vType) {
                                    var type = $("#newType").val();
                                    for (var i = 0; i < driverMarkers.length; i++) {
                                        driverMarkers[i].setMap(null);
                                    }
                                    //console.log(newLocations);
                                    //return false;
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
                                    setDriverListing(vType);
                                    getFarevalues(vType);
                                }

                                setInterval(function () {
                                    if (eTypeQ11 == 'yes') {
                                        setDriversMarkers('test');
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
                                                    if (confirm("The selected user account is in 'Inactive / Deleted' mode. Do you want to Active this User ?'")) {
                                                        eTypeQ11 = 'no';
                                                        $("#add_booking_form").attr('action', 'action_booking.php');
                                                        $("#submitbutton").trigger("click");
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
                                                    $("#submitbutton").trigger("click");
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
                                    eLadiesRide = 'No';
                                    eHandicaps = 'No';
                                    eChildSeat = "No";
                                    eWheelChair = "No";
                                    if ($("#eFemaleDriverRequest").is(":checked")) {
                                        eLadiesRide = 'Yes';
                                    }
                                    if ($("#eHandiCapAccessibility").is(":checked")) {
                                        eHandicaps = 'Yes';
                                    }
                                    if ($("#eChildSeatAvailable").is(":checked")) {
                                        eChildSeat = 'Yes';
                                    }
                                    if ($("#eWheelChairAvailable").is(":checked")) {
                                        eWheelChair = 'Yes';
                                    }
                                    $.ajax({
                                        type: "POST",
                                        url: "get_available_driver_list.php",
                                        dataType: "html",
                                        data: {vCountry: vCountry, keyword: keyword, iVehicleTypeId: vType, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, eChildSeat: eChildSeat, eWheelChair: eWheelChair, AppeType: eType},
                                        success: function (dataHtml2) {
                                            $('#driver_main_list').show();
                                            if (dataHtml2 != "") {
                                                $('#driver_main_list').html(dataHtml2);
                                            } else {
                                                $('#driver_main_list').html('<h4 style="margin:25px 0 0 15px">Sorry , No <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> Found.</h4>');
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
                                var bookId = '<?=$iCabBookingId;?>';
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
                                        //alert(referrer);
                                    } else {
                                        referrer = $("#previousLink").val();
                                    }
                                    if (referrer == "") {
                                        referrer = "cab_booking.php";
                                    } else {
                                        $("#backlink").val(referrer);
                                    }
                                    // $(".back_link").attr('href',referrer);
                                });

                                $('#datetimepicker4').keydown(function (e) {
                                    e.preventDefault();
                                    return false;
                                });

                                $('#eFemaleDriverRequest').click(function () {
                                    if ($(this).is(':checked'))
                                        setDriversMarkers('true');
                                    else
                                        setDriversMarkers('true');
                                });

                                $('#eHandiCapAccessibility').click(function () {
                                    if ($(this).is(':checked'))
                                        setDriversMarkers('true');
                                    else
                                        setDriversMarkers('true');
                                });
                                $('#eChildSeatAvailable').click(function () {
                                    if ($(this).is(':checked'))
                                        setDriversMarkers('true');
                                    else
                                        setDriversMarkers('true');
                                });
                                $('#eWheelChairAvailable').click(function () {
                                    if ($(this).is(':checked'))
                                        setDriversMarkers('true');
                                    else
                                        setDriversMarkers('true');
                                });
                                $('#reset12').click(function () {
                                    window.location.reload(true);
                                    /*$('#newSelect02').prop('selectedIndex',0);
                                     $("#newSelect02").val("").trigger("change");
                                     setDriverListing();*/
                                });

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
                                        showVehicleCountryVise('<?=$vCountry?>', '<?=$iVehicleTypeId;?>', eType); //added by SP for get vehicles/services according to the pickup location, put here because vehicle list change when from location entered on 02-08-2019
                                    }
                                }

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
                                $("#submitbutton").on("click", function (event) {
                                    var from_lat_long = $("#from_lat_long").val();
                                    var to_lat_long = $("#to_lat_long").val();
                                    if (eType == 'Ride') {
                                        var idClicked = $("input[name='eRideType']:checked").val();
                                    } else if (eType == 'Deliver') {
                                        var idClicked = $("input[name='eDeliveryType']:checked").val();
                                    } else if (eType == 'UberX') {
                                        var idClicked = 'later';
                                    }
                                    var isvalidate = $("#add_booking_form")[0].checkValidity();
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
                                        //alert(eTollEnabled);
                                        //	$('#submitbutton').prop('disabled', true);
                                        if (eTollEnabled == 'Yes') {
                                            if (eType != 'UberX' && eFlatTrip != 'Yes') {
                                                if (ENABLE_TOLL_COST == 'Yes') {
                                                    $(".loader-default").show();
                                                    if (($("#from").val() != "" && $("#from_lat_long").val() != "") && ($("#to").val() != "" && $("#to_lat_long").val() != "")) {
                                                        var newFromtoll = $("#from_lat").val() + "," + $("#from_long").val();
                                                        var newTotoll = $("#to_lat").val() + "," + $("#to_long").val();
                                                        $.getJSON("https://tce.cit.api.here.com/2/calculateroute.json?app_id=<?=$TOLL_COST_APP_ID?>&app_code=<?=$TOLL_COST_APP_CODE?>&waypoint0=" + newFromtoll + "&waypoint1=" + newTotoll + "&mode=fastest;car", function (result) {
                                                            var tollCurrency = result.costs.currency;
                                                            var tollCost = result.costs.details.tollCost;
<?php if ($eTollSkipped == 'Yes') {?>
                                                                var tollskip = 'Yes';
<?php } else {?>
                                                                var tollskip = 'No';
<?php }?>
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
                                                                                //$("#add_booking_form").submit();
                                                                                SubmitFormCheck(idClicked);
                                                                                return true;
                                                                            }
                                                                        },
                                                                        {
                                                                            label: "Close",
                                                                            className: "btn btn-default",
                                                                            callback: function () {
                                                                                // $('#submitbutton').prop('disabled', false);
                                                                            }
                                                                        }
                                                                    ],
                                                                    show: false,
                                                                    onEscape: function () {
                                                                        modal.modal("hide");
                                                                        //$('#submitbutton').prop('disabled', false);
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
                                                                //$("#add_booking_form").submit();
                                                                SubmitFormCheck(idClicked);
                                                                return true;
                                                            }
                                                        }).fail(function (jqXHR, textStatus, errorThrown) {
                                                            //alert("Toll API Response: " + jqXHR.responseJSON.message);
                                                            $(".loader-default").hide();
                                                            //$("#add_booking_form").submit();
                                                            SubmitFormCheck(idClicked);
                                                        });
                                                    } /*else {
                                                     $(".loader-default").hide();
                                                     $("#add_booking_form").submit();
                                                     return true;
                                                     }*/
                                                } else {
                                                    SubmitFormCheck(idClicked);
                                                    //$("#add_booking_form").submit();
                                                    return true;
                                                }
                                            } else {
                                                SubmitFormCheck(idClicked);
                                                //$("#add_booking_form").submit();
                                                return true;
                                            }
                                        } else {
                                            SubmitFormCheck(idClicked);
                                            //                                            $("#add_booking_form").submit();
                                            return true;
                                        }
                                    }
                                });
            </script>
            <?php require_once "functions.php";?>
    </body>
    <!-- END BODY-->
</html>
<div class="loader-default"></div>
<div class="form-content" style="display:none;">
    <p><?=$langage_lbl_admin['LBL_TOLL_PRICE_DESC'];?></p>
    <form class="form" role="form" id="formtoll">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="eTollSkipped1" id="eTollSkipped1" value="Yes" <?php if ($eTollSkipped == 'Yes') {
    echo 'checked';
}
?>/> Ignore Toll Route
            </label>
        </div>
    </form>
    <p style="text-align: center;font-weight: bold;">
        <span>Total Fare <?=$generalobj->symbol_currency();?><b id="totalcost">0</b></span>+
        <span>Toll Price <b id="tollcost">0</b></span>
    </p>
</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"> How It Works?</h4>
            </div>
            <div class="modal-body">
                <?php if ($APP_TYPE == "Ride" || $APP_TYPE == "Delivery" || $APP_TYPE == "Ride-Delivery") {?>
                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Through &quot;Manual Booking&quot; Feature, do schedule booking for the users. There will be users who may not have iPhone or Android Phone or may not have the app installed on their phone. In this case, they will call you and give the details for the booking.</span></span></span></span></p>

                    <p style="margin-bottom:9px"><br />
                        <span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">STEPS:</span></span></span></span></p>

                    <ol>
                        <li><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Enter User details.</span></span></span></span></li>
                        <li><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Enter Trip details.</span></span></span></span></li>
                        <li><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Select preferences if needed.</span></span></span></span></li>
                        <li style="margin-bottom:13px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Select Booking Method.</span></span></span></span></li>
                    </ol>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><b><span style="color:#333333">&nbsp;Select Specific Driver:</span></b><span style="color:#333333">&nbsp;Admin will need to communicate &amp; confirm with driver and then allocate the trip booking.</span></span></span></span></p>

                    <p style="margin-left:24px; margin-bottom:13px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Clicking on &quot;Book&quot; Button, the Booking detail will be saved and will take Administrator to the &quot;Trip Later Booking&quot; Section. This page will show all such bookings.</span><br />
                                    <br />
                                    <span lang="EN-US" style="color:#333333">The Assigned provider list will be change as per the selection of Vehicle type and other preferences. The provider list will get the filter out whose vehicle is registered with the specific vehicle type and have the preferences.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Both driver and user will&nbsp;receive the booking details through Email and SMS as soon as the form is submitted. Based on these booking details, the driver will pick up the user at the scheduled time.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">They both will get the reminder SMS and Email as well before 30 minutes of the actual trip</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The assigned Driver can see the upcoming Bookings from his App under &quot;My Bookings&quot; section.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The driver will have the option to &quot;Start Trip&quot; when he reaches the Pickup Location at the scheduled time or &quot;Cancel Trip&quot; if he cannot make the trip for some reason. If the Driver clicks on &quot;Cancel Trip&quot;, a notification will be sent to Administrator so he can make alternate arrangements.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Upon clicking on &quot;Start Trip&quot;, the trip will start in driver&#39;s App in a regular way.</span></span></span></span></p>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif">&nbsp;</span></span></span></p>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><b><span style="color:#333333">Auto Assign Driver:</span></b><span style="color:#333333">&nbsp;In the case where you want to send the booking request to the drivers automatically before schedule time by the system then select this option which sends a request to all the nearby drivers from the entered source location at the specified booking time.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Driver auto-assign process works as explained below.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The system will automatically send the request to drivers who are online and available within the pickup location radius</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Driver(s) will get the 30 seconds dial screen request before 8-12 minutes before the actual pickup time. This request is the same like &quot;Request Now&quot; one.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">If no driver(s) accepts the request then the system will make a 2nd try after 4 minutes and sends the request again. At this point system also notifies the admin through email that no drivers had accepted the request in the first try.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Again If no driver(s) accepts the request then the system will make a 3rd and last try after 4 minutes and send the request again. At this point system also notifies admin through email that no drivers had accepted the request in 2nd try.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The system makes the 3 trials and if in any trial, no drivers available in that area then it will inform administrator about the unavailability of the driver so administrator takes necessary action to contact that trip and arrange the taxi for him.</span></span></span></span></p>
                <?php } else if ($APP_TYPE == 'UberX') {?>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Through &quot;Manual Booking&quot; Feature, do schedule booking for the users. There will be users who may not have iPhone or Android Phone or may not have the app installed on their phone. In this case, they will call you and give the details for the booking.</span></span></span></span></p>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">STEPS:</span></span></span></span></p>

                    <ol>
                        <li><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Enter User details.</span></span></span></span></li>
                        <li><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Enter Job details.</span></span></span></span></li>
                        <li style="margin-bottom:13px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Select Provider.</span></span></span></span></li>
                    </ol>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Admin will need to communicate &amp; confirm with the provider and then allocate the job.</span></span></span></span></p>

                    <p style="margin-bottom:13px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Clicking on &quot;Book&quot; Button, the booking details will be saved and will take Administrator to the &quot;Job Later Booking&quot; Section. This page will show all such bookings.</span><br />
                                    <br />
                                    <span lang="EN-US" style="color:#333333">The provider list will be change as per the selection of Job type and availability of the provider, as per the location radius range.</span></span></span></span></p>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Both provider and user will&nbsp;receive the booking details through Email and SMS as soon as the form is submitted. Based on these booking details, the provider will do the Job for the user at the scheduled time.</span></span></span></span></p>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">They both will get the reminder SMS and Email as well before 30 minutes of the actual job.</span></span></span></span></p>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The assigned provider can see the upcoming bookings from his App under &quot;My Bookings&quot; section.</span></span></span></span></p>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The provider will have the option to &quot;Begin Job&quot; or &quot;Cancel Job&quot; if he cannot make the Job for some reason. Upon clicking on &quot;Begin Job&quot;, the job will start in provider&#39;s App in a regular way.</span></span></span></span></p>



                <?php } else {?>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Through &quot;Manual Booking&quot; Feature, do schedule booking for the users. There will be users who may not have iPhone or Android Phone or may not have the app installed on their phone. In this case, they will call you and give the details for the booking.</span></span></span></span></p>

                    <p style="margin-bottom:9px"><br />
                        <span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333"><b>STEPS:</b></span></span></span></span></p>

                    <ol>
                        <li><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Enter User details.</span></span></span></span></li>
                        <li><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Enter Trip/Job details.</span></span></span></span></li>
                        <li style="margin-bottom:13px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">Select Driver/ Service Provider.</span></span></span></span></li>
                    </ol>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><b><span style="color:#333333">&nbsp;Select Specific Driver:</span></b><span style="color:#333333">&nbsp;Admin will need to communicate &amp; confirm with driver and then allocate the trip/job booking.</span></span></span></span></p>

                    <p style="margin-left:24px; margin-bottom:13px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Clicking on &quot;Book&quot; Button, the booking details will be saved and will take Administrator to the &quot;Job Later Booking&quot; Section. This page will show all such bookings.</span><br />
                                    <br />
                                    <span lang="EN-US" style="color:#333333">For the Ride/ Delivery Service: The Assigned provider list will be change as per the selection of Vehicle type and other preferences. The provider list will get the filter out whose vehicle is registered with the specific vehicle type and have the preferences if any.</span></span></span></span></p>

                    <p style="margin-left:24px; margin-bottom:13px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span lang="EN-US" style="color:#333333">For the Other Services: The provider list will be change as per the selection of Trip/Job type and availability of the provider/driver.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Both provider and user will&nbsp;receive the booking details through Email and SMS as soon as the form is submitted. Based on these booking details, the provider will do the Trip/Job for the user at the scheduled time.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">They both will get the reminder SMS and Email as well before 30 minutes of the actual job.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The assigned provider can see the upcoming bookings from his App under &quot;My Bookings&quot; section.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The provider will have the option to &quot;Start Trip/ Job&quot; or &quot;Cancel Trip/Job&quot; if he cannot make the Trip/Job for some reason. Upon clicking on &quot;Start Trip/ Job&quot;, it will start in provider&#39;s App in a regular way.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px">&nbsp;</p>

                    <p style="margin-bottom:9px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><b><span style="color:#333333">Auto Assign Driver:</span></b><span style="color:#333333">&nbsp;In the case where you want to send the booking request to the drivers automatically before schedule time by the system then select this option which sends a request to all the nearby drivers from the entered source location at the specified booking time.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Driver auto-assign process works as explained below. Also, this method is user only for the Ride and Delivery Service.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The system will automatically send the request to drivers who are online and available within the pickup location radius</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Driver(s) will get the 30 seconds dial screen request before 8-12 minutes before the actual pickup time. This request is the same like &quot;Request Now&quot; one.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">If no driver(s) accepts the request then the system will make a 2nd try after 4 minutes and sends the request again. At this point system also notifies the admin through email that no drivers had accepted the request in the first try.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">Again If no driver(s) accepts the request then the system will make a 3rd and last try after 4 minutes and send the request again. At this point system also notifies admin through email that no drivers had accepted the request in 2nd try.</span></span></span></span></p>

                    <p style="margin-bottom:9px; margin-left:24px"><span style="font-size:11pt"><span style="line-height:normal"><span style="font-family:Calibri,sans-serif"><span style="color:#333333">The system makes the 3 trials and if in any trial, no drivers available in that area then it will inform administrator about the unavailability of the driver so administrator takes necessary action to contact that trip and arrange the taxi for him.</span></span></span></span></p>

                <?php }?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
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
                <p style="font-size: 15px;"> Please make sure that the booking time is 20 minutes ahead from current time. So if your current time is 3:00 P.M then please select 3:20 P.M as booking time.  This gives a room to auto assign <?=strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']);?> properly.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fareEstimateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content booking-passenger-fare-estimate">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?=$langage_lbl_admin['LBL_FARE_ESTIMATE_TXT'];?></h4>
            </div>
            <div class="modal-body">
                <div class="base-fare-part showAfterDestination" id="showAfterDestination">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="loader-default" style="display:none;"></div>
<div class="form-content" style="display:none;">
    <p><?=$langage_lbl_admin['LBL_TOLL_PRICE_DESC'];?></p>
    <form class="form" role="form" id="formtoll">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="eTollSkipped1" id="eTollSkipped1" value="Yes" <?php if ($eTollSkipped == 'Yes') {
    echo 'checked';
}
?>/> <?=$langage_lbl_admin['LBL_IGNORE_TOLL_ROUTE_WEB'];?>
            </label>
        </div>
    </form>
    <p style="text-align: center;font-weight: bold;">
        <span><?=$langage_lbl_admin['LBL_Total_Fare'];?> <?=$data1['currencySymbol'];?><b id="totalcost">0</b></span>+
        <span><?=$langage_lbl_admin['LBL_TOLL_TXT'];?> <b id="tollcost">0</b></span>
    </p>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?="aaaa" . $langage_lbl_admin['LBL_DIS_HOW_IT_WORKS'];?></h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=$langage_lbl_admin['LBL_CLOSE_TXT'];?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModalufx" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"> <?=$langage_lbl_admin['LBL_DIS_HOW_IT_WORKS'];?></h4>
            </div>
            <div class="modal-body">
                <p><b>Flow </b>: Through "Manual Booking" Feature, you can book providers for users who ordered for a Service by calling you. There will be users who may not have iPhone or Android Phone or may not have app installed on their phone. In this case, they will call Company (your company) and order service which may be needed immediately or after some time later.</p>
                <p>- Here, you will fill their info in the form and dispatch a service provider for them.</p>
                <p>- If the user is already registered with us, just enter his phone number and his info will be fetched from the database when "Get Details" button is clicked. Else fill the form.</p>
                <p>- Once the Job detail is added, estimate will be calculated based on Service or Service provider selected.</p>
                <p>- Admin will need to communicate & confirm with provider and then select him as provider so the Job can be allotted to him. </p>
                <p>- Clicking on "Book Now" Button, the Booking detail will be saved and will take Administrator to the "Scheduled Booking" Section. This page will show all such bookings.</p>
                <p>- Both Provider and User will receive the booking details through Email and SMS as soon as the form is submitted. Based on this booking details, Provider will go to user's location at the scheduled time.</p>
                <p>- They both will get the reminder SMS and Email as well before 30 minutes of actual job</p>
                <p>- The assigned provider can see the upcoming Bookings from his App under "My Jobs" section.</p>
                <p>- Provider will have option to "Start Job" when he reaches the Job Location at scheduled time or "Cancel Job" if he cannot take the job for some reason. If the provider clicks on "Cancel Job", a notification will be sent to Administrator so he can make alternate arrangements.</p>
                <p>- Upon clicking on "Start Job", the service  will start in provider's App in regular way.</p>
                <p>&nbsp;</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=$langage_lbl_admin['LBL_CLOSE_TXT'];?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModalautoassign" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?=$langage_lbl_admin['LBL_DIS_HOW_IT_WORKS'];?></h4>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px;"><?=$langage_lbl_admin['LBL_MANUAL_ALERT_MESSAGE_TIME_WEB'];?> </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-success" data-dismiss="modal"><?=$langage_lbl_admin['LBL_BTN_OK_TXT'];?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="driverData" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"><?=$langage_lbl_admin['LBL_DIS_HOW_IT_WORKS'];?></h4>
            </div>
            <div class="modal-body">
                <div id="driver-bottom-set001">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnYes" class="btn btn-secondary btn-success" data-dismiss="modal"><?=$langage_lbl_admin['LBL_BTN_OK_TXT'];?></button>
            </div>
        </div>
    </div>
</div>
<div class="booking-confirmation-popup hide001" id="request-loader001" >
    <div class="requesting-popup-old">
        <span id="requ_title" class="req-001"><b><?=ucfirst(strtolower($langage_lbl_admin['LBL_REQUESTING_TXT']));?> ....</b>
            <span class="requesting-popup-sub" style="padding-right: 5px;float: right;"><a href="javascript:void(0);" id="cancelBtn" onclick="cancellingRequestDriver();"><?=$langage_lbl_admin['LBL_CANCEL_TXT'];?></a></span></span>
    </div>
    <div class="requesting-popup hide001" id="req_try_again">
        <p><?=$langage_lbl_admin['LBL_NO_DRIVER_AVALIABLE_TO_ACCEPT_WEB'];?></p>
        <span><a href="javascript:void(0);" id="retryBtn"><?=$langage_lbl_admin['LBL_RETRY_TXT'];?></a></span>
        <span><a href="javascript:void(0);" id="cancelBtn" onclick="cancellingRequestDriver();"><?=$langage_lbl_admin['LBL_CANCEL_TXT'];?></a></span>
    </div>
    <div class="pulse-box"> <svg class="pulse-svg" width="155px" height="155px" viewBox="0 0 50 50" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <circle class="circle first-circle" fill="#e99803" cx="25" cy="25" r="25"></circle>
        <circle class="circle second-circle" fill="#e99803" cx="25" cy="25" r="25"></circle>
        <circle class="circle third-circle" fill="#e99803" cx="25" cy="25" r="25"></circle>
        <circle class="circle" fill="#e99803" cx="25" cy="25" r="25"></circle>
        <!--<em><img src="images-new/confirmation-img.png" alt=""></em>-->
        </svg> </div>
    <!--div class="booking-confirmation-popup-inner">
        <div class="ripple"><img class="confirmation-img" src="images-new/confirmation-img.png" alt="" /></div>
    </div-->
</div>