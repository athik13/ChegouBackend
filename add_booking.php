<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
include_once(ROOT_PATH . "common.php");


$sql = "SELECT vValue,vName FROM `configurations` WHERE vName IN ('APP_DELIVERY_MODE','ENABLE_TOLL_COST','TOLL_COST_APP_ID','TOLL_COST_APP_CODE','CHILD_SEAT_ACCESSIBILITY_OPTION','WHEEL_CHAIR_ACCESSIBILITY_OPTION','HANDICAP_ACCESSIBILITY_OPTION')";
$APP_DELIVERY_MODE = $ENABLE_TOLL_COST = $TOLL_COST_APP_ID = $TOLL_COST_APP_CODE = $CHILD_SEAT_ACCESSIBILITY_OPTION = $WHEEL_CHAIR_ACCESSIBILITY_OPTION = $HANDICAP_ACCESSIBILITY_OPTION = "";
$configData = $obj->MySQLSelect($sql);
for ($c = 0; $c < count($configData); $c++) {
    if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "APP_DELIVERY_MODE") {
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
    } else if (isset($configData[$c]['vName']) && $configData[$c]['vName'] == "HANDICAP_ACCESSIBILITY_OPTION") {
        $HANDICAP_ACCESSIBILITY_OPTION = $configData[$c]['vValue'];
    }
}
$script = "booking";
$tbl_name = 'cab_booking';

function converToTzManual($time, $toTz, $fromTz, $dateFormat = "Y-m-d H:i:s") {
    $date = new DateTime($time, new DateTimeZone($fromTz));
    $date->setTimezone(new DateTimeZone($toTz));
    $time = $date->format($dateFormat);
    return $time;
}

$userType1 = isset($_REQUEST['userType1']) ? $_REQUEST['userType1'] : '';

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$iCabBookingId = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : '';
$action = ($iCabBookingId != '') ? 'Edit' : 'Add';

//For Country
$sql = "SELECT vCountryCode,vCountry from country where eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);

$sql = "select cn.vCountryCode,cn.vCountry,cn.vPhoneCode,cn.vTimeZone from country cn inner join 
	configurations c on c.vValue=cn.vCountryCode where c.vName='" . DEFAULT_COUNTRY_CODE_WEB . "'";
$db_con = $obj->MySQLSelect($sql);
$vPhoneCode = $generalobj->clearPhone($db_con[0]['vPhoneCode']);
$vRideCountry = isset($_REQUEST['vRideCountry']) ? $_REQUEST['vRideCountry'] : $db_con[0]['vCountryCode'];
$vTimeZone = isset($_REQUEST['vTimeZone']) ? $_REQUEST['vTimeZone'] : $db_con[0]['vTimeZone'];
$vCountry = $db_con[0]['vCountryCode'];

$address = $db_con[0]['vCountry']; // Google HQ
/* $prepAddr = str_replace(' ','+',$address);
  $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
  $output= json_decode($geocode);
  $latitude = $output->results[0]->geometry->location->lat;
  $longitude = $output->results[0]->geometry->location->lng; */

$dBooking_date = "";

$sql1 = "SELECT * FROM `package_type` WHERE eStatus='Active'";
$db_PackageType = $obj->MySQLSelect($sql1);

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
            $vPhone = $generalobj->clearPhone($value['vPhone']);
            $vName = $value['vName'];
            $vLastName = $generalobj->clearName(" " . $value['vLastName']);
            $vEmail = $generalobj->clearEmail($value['vEmail']);
            $vPhoneCode = $generalobj->clearPhone($value['vPhoneCode']);
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
            $etype = $value['eType'];
            $eFlatTrip = $value['eFlatTrip'];
            $fFlatTripPrice = $value['fFlatTripPrice'];
            $eTollSkipped = $value['eTollSkipped'];
            $fTollPrice = $value['fTollPrice'];
            $vTollPriceCurrencyCode = $value['vTollPriceCurrencyCode'];
            $dBooking_date = converToTzManual($dBookingDate, $vTimeZone, $systemTimeZone);
            if ($etype == 'Ride') {
                $eRideType = 'later';
            }

            if ($etype == 'Deliver') {
                $eDeliveryType = 'later';
            }
            $vCouponCode = $value['vCouponCode'];
        }
    }
}

if ($_SESSION['sess_user'] == 'rider' && $userType1 == 'rider') {
    $sess_iUserId = $_SESSION['sess_iUserId'];
    $rsql = "SELECT * FROM  `register_user` WHERE iUserId='" . $sess_iUserId . "'";
    $db_userdata = $obj->MySQLSelect($rsql);
    $vCountry = $db_userdata[0]['vCountry'];
    $vPhone = $generalobj->clearPhone($db_userdata[0]['vPhone']);
    $vName = $db_userdata[0]['vName'];
    $vLastName = $db_userdata[0]['vLastName'];
    $vEmail = $generalobj->clearEmail($db_userdata[0]['vEmail']);
    $vPhoneCode = $generalobj->clearPhone($db_userdata[0]['vPhoneCode']);
    $eAutoAssign = 'Yes';
    $eBookingFrom = 'User';
}

if ($_SESSION['sess_user'] == 'company' && $userType1 == 'company') {
    $sess_iCompanyId = $_SESSION['sess_iCompanyId'];
    $eBookingFrom = 'Company';
}

if ($eBookingFrom == '') {
    $eBookingFrom = 'Admin';
}
//Ride Vehicle data
$sql = "SELECT iVehicleTypeId,vVehicleType_" . $_SESSION['sess_lang'] . " AS vVehicleType,vLogo,vLogo1 FROM vehicle_type WHERE eType = 'Ride' ORDER BY iVehicleTypeId ASC";
$db_ride_vehicles = $obj->MySQLSelect($sql);

//Delivery Vehicle data
$sql = "SELECT iVehicleTypeId,vVehicleType_" . $_SESSION['sess_lang'] . " AS vVehicleType,vLogo,vLogo1 FROM vehicle_type WHERE eType = 'Deliver' ORDER BY iVehicleTypeId ASC";
$db_delivery_vehicles = $obj->MySQLSelect($sql);
?>

<div id="content">
    <div class="inner">
        <div class="row">
            <div class="col-lg-8">
                <? if ($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX") { ?>
                    <h1> Manual <?php echo $langage_lbl['LBL_TEXI_ADMIN']; ?> Dispatch </h1>
                <?php } else { ?>
                    <h1> <?php echo $langage_lbl['LBL_MANUAL_TAXI_DISPATCH']; ?> </h1>
                <?php } ?>
            </div>
            <div class="col-lg-4 helpbutton">
                <? if ($APP_TYPE != "UberX") { ?>
                    <h1 class="float-right"><a class="btn btn-primary how_it_work_btn" data-toggle="modal" data-target="#myModal"><i class="fa fa-question-circle" style="font-size: 18px;"></i> <?php echo $langage_lbl['LBL_DIS_HOW_IT_WORKS']; ?></a></h1>
                <? } else { ?>
                    <h1 class="float-right"><a class="btn btn-primary how_it_work_btn" data-toggle="modal" data-target="#myModalufx"><i class="fa fa-question-circle" style="font-size: 18px;"></i> <?php echo $langage_lbl['LBL_DIS_HOW_IT_WORKS']; ?></a></h1>

<? } ?>
            </div>
        </div>
        <hr />
        <form name="add_booking_form" id="add_booking_form" method="post" action="<?= $tconfig["tsite_url"] ?>booking/action_booking.php" >
            <div class="form-group" style="display: inline-block;">
                    <?php if ($success == "1") { ?>
                    <div class="alert alert-success alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                        <?php
                        echo ($vassign != "1") ? 'Booking Has Been Added Successfully.' : $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . ' Has Been Assigned Successfully.';
                        ?>
                    </div>
                    <br/>
<?php } ?>
<?php if ($success == 2) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                        "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you. </div>
                    <br/>
<?php } ?>
                    <?php if ($success == 0 && $var_msg != "") { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                    <?= $var_msg; ?>
                    </div>
                    <br/>
<?php } ?>
                <input type="hidden" name="previousLink" id="previousLink" value=""/>
                <input type="hidden" name="eBookingFrom" id="eBookingFrom" value="<?= $eBookingFrom ?>" />
                <input type="hidden" name="backlink" id="backlink" value="cabbooking.php"/>
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
                <input type="hidden" name="iCompanyId" id="iCompanyId" value="<?= $sess_iCompanyId; ?>">
                <?php if ($APP_TYPE != 'Ride-Delivery' && $APP_TYPE != 'Ride-Delivery-UberX' || ($APP_TYPE == 'Ride-Delivery' && $APP_DELIVERY_MODE == "Multi")) { ?>
                    <input type="hidden" value="<?= $etype ?>" id="eType" name="eType" />
<?php } ?>

                <div class="add-booking-form-taxi add-booking-form-taxi1 col-lg-12" id="add-booking-form-taxi1"> <span class="col0">
                        <select name="vCountry" id="vCountry" class="form-control form-control-select" onChange="changeCode(this.value, '<?php echo $iVehicleTypeId; ?>');setDriverListing();" required>
                            <!-- <option value="">Select Country</option> -->
                                    <? for ($i = 0; $i < count($db_code); $i++) { ?>
                                <option value="<?= $db_code[$i]['vCountryCode'] ?>" 
                                <?php if ($db_code[$i]['vCountryCode'] == $vCountry) {
                                    echo "selected";
                                } ?> >
    <?= $db_code[$i]['vCountry']; ?>
                                </option>
<? } ?>
                        </select>
                    </span> 
                    <span class="col6">
                        <input type="text" class="form-control add-book-input" name="vPhoneCode" id="vPhoneCode" value="<?= $vPhoneCode; ?>" readonly />
                    </span>
                    <span class="col2">
                        <input type="text" pattern="[0-9]{1,}" title="Enter Mobile Number." class="form-control add-book-input" name="vPhone"  id="vPhone" value="<?= $vPhone; ?>" placeholder="<?php echo $langage_lbl['LBL_ENTER_PHONE_NO_WEB']; ?>" onKeyUp="return isNumberKey(event)"  onblur="return isNumberKey(event)"  required  />
                    </span> 
                    <span class="col3">
                        <input type="text" class="form-control first-name1" name="vName"  id="vName" value="<?= $vName; ?>" placeholder="<?php echo $langage_lbl['LBL_YOUR_FIRST_NAME']; ?>" required />
                        <input type="text" class="form-control last-name1" name="vLastName"  id="vLastName" value="<?= $vLastName; ?>" placeholder="<?php echo $langage_lbl['LBL_YOUR_LAST_NAME']; ?>" required />
                    </span> 
                    <span class="col4" style="margin: 0px;">
                        <input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" class="form-control" name="vEmail" id="vEmail" value="<?= $vEmail; ?>" placeholder="<?php echo $langage_lbl['LBL_EMAIL_TEXT']; ?>" required >
                        <div id="emailCheck"></div>
                    </span>
                </div>
            </div>
            <div class="map-main-page-inner">
                <div class="content map-main-page-inner-tab" id="content_1">
                    <div class="form-group service-type">
<?php if (($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX')) { ?>
                            <h3 ><?php echo $langage_lbl['LBL_MYTRIP_TRIP_TYPE']; ?></h3>
                            <div class="radio-but">
                                <div class="add-booking-radiobut radio-inline">
                                    <input class="add-booking" id="r1" name="eType" type="radio" value="Ride" <?php if ($etype == 'Ride') {
        echo 'checked';
    } ?> onChange="show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);" checked="checked">
                                    <label for="r1"><?php echo $langage_lbl['LBL_RIDE']; ?></label></div>								
                                <div class="add-booking-radiobut radio-inline">
                                    <input id="r2" name="eType" type="radio" value="Deliver" <?php if ($etype == 'Deliver') {
        echo 'checked';
    } ?> onChange="show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);">
                                    <label for="r2"><?php echo $langage_lbl['LBL_DELIVERY']; ?></label></div> 
                            <? if ($APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                    <div class="add-booking-radiobut radio-inline">
                                        <input class="add-booking" id="r3" name="eType" type="radio" value="UberX" <?php if ($etype == 'UberX') {
                                    echo 'checked';
                                } ?> onChange="show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);">
                                        <label for="r3"><?php echo $langage_lbl['LBL_OTHER']; ?></label></div>
                                        <? } ?>
                            </div>	
                                    <?php } ?>
                    </div>
                    <div class="map-live-hs-mid">
<?php if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                            <div id="ride-delivery-type" style="display:none; margin-top:20px;">
                                <label class="delivery-option"  ><?php echo $langage_lbl['LBL_DELIVERY_OPTIONS_WEB']; ?> :</label>
                                <span>
                                    <select class="form-control form-control-select form-control14" name="iPackageTypeId"  id="iPackageTypeId">  
                                        <option value=""><?php echo $langage_lbl['LBL_SELECT_PACKAGE_TYPE']; ?></option>
    <? foreach ($db_PackageType as $val) { ?>
                                            <option value="<?= $val['iPackageTypeId'] ?>" <? if ($val['iPackageTypeId'] == $iPackageTypeId && $action == "Edit") { ?>selected<? } ?>><?= $val['vName']; ?></option>
    <? } ?>
                                    </select>
                                </span> 
                                <span>
                                    <input type="text" class="form-control form-control14" name="vReceiverName"  id="vReceiverName" value="<?= $vReceiverName; ?>" placeholder="Recipient's name" />
                                </span> 
                                <span>
                                    <input type="text" class="form-control form-control14" pattern="[0-9]{1,}" title="Enter Mobile Number." name="vReceiverMobile"  id="vReceiverMobile" value="<?= $vReceiverMobile; ?>" placeholder="Recipient's mobile" >
                                </span> 
                                <span> <input type="text" class="form-control form-control14" name="tPickUpIns"  id="tPickUpIns" value="<?= $tPickUpIns; ?>" placeholder="Pick up Ins"></span>
                                <span> <input type="text" class="form-control form-control14" name="tDeliveryIns"  id="tDeliveryIns" value="<?= $tDeliveryIns; ?>" placeholder="Delivery Ins"></span>
                                <span style="margin-bottom: 0px"> <input type="text" class="form-control form-control14" name="tPackageDetails"  id="tPackageDetails" value="<?= $tPackageDetails; ?>" placeholder="Package details"></span> 
                            </div>
<?php } ?>
                    </div>
                    <div class="map-live-hs-mid">
                        <span class="col5">
                            <div class="pickup-location">
                                <h3 ><?php echo $langage_lbl['LBL_PICK_UP_LOCATION']; ?></h3>
                                <input type="text" class="ride-location1 highalert txt_active form-control first-name1" name="vSourceAddresss"  id="from" value="<?= $vSourceAddresss; ?>" placeholder="<?= ucfirst(strtolower($langage_lbl['LBL_PICKUP_LOCATION_HEADER_TXT'])); ?>" required onpaste="checkrestrictionfrom('from');">
                            </div>

<? if ($APP_TYPE != "UberX") { ?>
                                <div class="pickup-location">
                                    <h3 class="tolabel" ><?= ucfirst(strtolower($langage_lbl['LBL_DROP_OFF_LOCATION_TXT'])); ?></h3>
                                    <input type="text" class="ride-location1 highalert txt_active form-control last-name1" name="tDestAddress"  id="to" value="<?= $tDestAddress; ?>" placeholder="<?= ucfirst(strtolower($langage_lbl['LBL_DROP_OFF_LOCATION_TXT'])); ?>" required onpaste="checkrestrictionto('to');">
                                </div>
                                    <? } ?>
                        </span>
                        <span>
                                    <? if ($userType1 != 'rider') { ?>
                                <div class="vehicle-type">
                                    <h3 ><?php echo $langage_lbl['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></h3>
                                    <select class="form-control form-control-select form-control14" name='iVehicleTypeId' id="iVehicleTypeId" required onChange="showAsVehicleType(this.value)">
                                        <option value="" >Select <?php echo $langage_lbl['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></option>
                                <?php /*
                                  $sql1 = "SELECT iVehicleTypeId, vVehicleType FROM `vehicle_type` WHERE 1";
                                  $db_carType = $obj->MySQLSelect($sql1);
                                  foreach ($db_carType as $db_car) {
                                  ?>
                                  <option value="<?php echo $db_car['iVehicleTypeId']; ?>" <?php if ($iVehicleTypeId == $db_car['iVehicleTypeId']) {
                                  echo "selected";
                                  } ?> ><?php echo $db_car['vVehicleType']; ?></option>
                                  <?php } */ ?>
                                    </select>
                                </div>
                                    <? } ?>
                                    <? if ($userType1 == 'rider') { ?>

                                <div class="vehicle-type">
                                    <h3 ><?php echo $langage_lbl['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></h3>
                                    <div class="radio-vehicle-type rideShow" > 
                                        <?php
                                        foreach ($db_ride_vehicles as $key => $vehilceval) {
                                            $msatt = $acls = "";
                                            if ($key == 0) { //For set first vehicle active
                                                $msatt = "hover_"; //For vehicle active
                                                $acls = "checked"; //For set first vehicle active
                                                $logo = $vehilceval['vLogo1'];
                                            } else {
                                                $logo = $vehilceval['vLogo'];
                                            }
                                            ?>
                                            <!-- Save Active images for all vehicles -->
                                            <input type="hidden" name="vehicle_image_hover_<?php echo $vehilceval['iVehicleTypeId']; ?>" id="vehicle_image_hover_<?php echo $vehilceval['iVehicleTypeId']; ?>" value='<?php echo $tconfig["tsite_upload_images_vehicle_type"] . "/" . $vehilceval["iVehicleTypeId"] . "/android/" . $vehilceval['vLogo1']; ?>' />

                                            <!-- Save In-active images for all vehicles -->
                                            <input type="hidden" name="vehicle_image_<?php echo $vehilceval['iVehicleTypeId']; ?>" id="vehicle_image_<?php echo $vehilceval['iVehicleTypeId']; ?>" value='<?php echo $tconfig["tsite_upload_images_vehicle_type"] . "/" . $vehilceval["iVehicleTypeId"] . "/android/" . $vehilceval['vLogo']; ?>' />
                                            <b class="vehicleImageIdClass_ride" id="<?php echo $vehilceval['iVehicleTypeId']; ?>"><input id="r5_<?php echo $vehilceval['iVehicleTypeId']; ?>" name="iDriverVehicleId_ride" type="radio" <?php echo $acls; ?> value="<?php echo $vehilceval['iVehicleTypeId']; ?>">
                                                <label for="r5_<?php echo $vehilceval['iVehicleTypeId']; ?>" style="display: block;"><em><img src="<?php echo $tconfig["tsite_upload_images_vehicle_type"] . "/" . $vehilceval['iVehicleTypeId'] . '/android/' . $logo; ?>" alt="" data-id="<?php echo $vehilceval['iVehicleTypeId']; ?>"  style="width: 60px;height: 60px;"></em><?php echo $vehilceval['vVehicleType']; ?><p style="float: right" class="tootltipclass"><img src="assets/img/question-icon.jpg" id="tooltip_<?= $vehilceval['iVehicleTypeId']; ?>" alt="Question" onClick="showEstimateFareDisplayFare(<?= $vehilceval['iVehicleTypeId']; ?>);"></p>
                                                </label>
                                            </b>
                                        <? } ?>
                                    </div>
                                    <!-- Delivery Vehicles -->
                                    <div class="radio-vehicle-type deliveryShow" style="display:none;"> 
    <?php
    for ($i = 0; $i < count($db_delivery_vehicles); $i++) {
        $msatt = $acls = "";
        if ($i == 0) {
            $msatt = "hover_";
            $acls = "checked";
            $logo = $db_delivery_vehicles[$i]['vLogo1'];
        } else {
            $logo = $db_delivery_vehicles[$i]['vLogo'];
        }
        ?>
                                            <!-- Save Active images for all vehicles -->
                                            <input type="hidden" name="vehicle_image_hover_<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>" id="vehicle_image_hover_<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>" value='<?php echo $tconfig["tsite_upload_images_vehicle_type"] . "/" . $db_delivery_vehicles[$i]["iVehicleTypeId"] . "/android/" . $db_delivery_vehicles[$i]['vLogo1']; ?>' />

                                            <!-- Save In-active images for all vehicles -->
                                            <input type="hidden" name="vehicle_image_<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>" id="vehicle_image_<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>" value='<?php echo $tconfig["tsite_upload_images_vehicle_type"] . "/" . $db_delivery_vehicles[$i]["iVehicleTypeId"] . "/android/" . $db_delivery_vehicles[$i]['vLogo']; ?>' />

                                            <!-- Vehicles selection and name -->
                                            <b class="vehicleImageIdClass_delivery" id="<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>"><input id="r5_<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>" name="iDriverVehicleId_delivery" type="radio" <?php echo $acls; ?> value="<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>">
                                                <label for="r5_<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>" style="display: block;"><em><img src="<?php echo $tconfig["tsite_upload_images_vehicle_type"] . "/" . $db_delivery_vehicles[$i]['iVehicleTypeId'] . '/android/' . $logo; ?>" alt="" data-id="<?php echo $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>"  style="width: 60px;height: 60px;"></em><?php echo $db_delivery_vehicles[$i]['vVehicleType']; ?><p style="float: right" class="tootltipclass"><img src="assets/img/question-icon.jpg" id="tooltip_<?= $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>" alt="Question" onClick="showEstimateFareDisplayFare(<?= $db_delivery_vehicles[$i]['iVehicleTypeId']; ?>);"></p></label></b>
    <?php } ?>
                                    </div>

                                    <div class="vehicle-type-ufx" style="display: none;">
                                        <select class="form-control form-control-select form-control14" name='iVehicleTypeId' id="iVehicleTypeId" onChange="showVehicleTypeAmount(this.value)">
                                            <option value="" >Select <?php echo $langage_lbl['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></option>
                                        </select>
                                        <div class="clear"></div>
                                        <div id="iVehicleTypeData" style="display: none;">
                                        </div>
                                    </div>
                                </div>

<? } ?>
                        </span>
                        <span class="service-pickup-type">
                            <h3 ><?php echo $langage_lbl['LBL_SELECT_YOUR_PICKUP_TYPE_WEB']; ?></h3>
                            <!-- For Ride Options -->
                            <div class="radio-but-type rideShow"> 
                                <b>
                                    <input id="r3_eRideType" name="eRideType" type="radio" value="now" <? if ($eRideType != 'later') { ?> checked="" <? } ?>>
                                    <label for="r3_eRideType"><?php echo $langage_lbl['LBL_RIDE_NOW']; ?></label>
                                </b> 
                                <b>
                                    <input id="r4_eRideType" name="eRideType" type="radio" value="later" <? if ($eRideType == 'later') { ?> checked="" <? } ?>>
                                    <label for="r4_eRideType"><?php echo $langage_lbl['LBL_RIDE_LATER']; ?></label>
                                </b>
                            </div>

                            <!-- For Delivery Options -->
                            <div class="radio-but-type deliveryShow" style="display:none;">
                                <b><input id="r3_eDeliveryType" name="eDeliveryType" type="radio" checked='checked' value="now" <? if ($eDeliveryType != 'later') { ?> checked="" <? } ?>>
                                    <label for="r3_eDeliveryType"><?php echo $langage_lbl['LBL_DELIVER_NOW_WEB']; ?></label>
                                </b><b>
                                    <input id="r4_eDeliveryType" name="eDeliveryType" type="radio" value="later" <? if ($eDeliveryType == 'later') { ?> checked="" <? } ?>>
                                    <label for="r4_eDeliveryType"><?php echo $langage_lbl['LBL_DELIVER_LATER_WEB']; ?></label></b>
                            </div>
                        </span>

                        <span class="dateSchedule" style="display:none"> 
                            <input type="text" class="form-control form-control14" name="dBooking_date"  id="datetimepicker4" value="<?= $dBooking_date; ?>" placeholder="<?php echo $langage_lbl['LBL_SELECT_DATETIME_WEB']; ?>" onBlur="getFarevalues('');<?php if ($APP_TYPE == "UberX") { ?>setDriverListing();<?php } ?>">
                        </span>
                        <!-- new added -->
                        <div class="pickup-location pickup-location1" style="margin-bottom: 10px;">
                            <h3 ><?php echo $langage_lbl['LBL_DISCOUNT_CODE_WEB']; ?></h3>
                            <span class="form-group"><div><input name="vCouponCode" id="promocode" type="text" placeholder="<?php echo $langage_lbl['LBL_COUPON_CODE_WEB']; ?>" value="<?= $vCouponCode ?>"></div></span>
                            <b class='promocode-btn002'><a href="javascript:void(0);" id="myButton" class="submit" onclick="checkPromoCode();"><?php echo $langage_lbl['LBL_APPLY']; ?></a></b>
                        </div>

                            <? if ($APP_TYPE != 'UberX') { ?>
                                <?php if ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                <div id="ride-type" style="display:block;">
                                    <span class="auto_assign001">
                                        <input type="checkbox" name="eFemaleDriverRequest" id="eFemaleDriverRequest" value="Yes" <?php if ($eFemaleDriverRequest == 'Yes') echo 'checked'; ?>>
                                        <p><?php echo $langage_lbl['LBL_LADIES_ONLY_RIDE_WEB']; ?></p>
                                    </span>
        <?php if (isset($HANDICAP_ACCESSIBILITY_OPTION) && $HANDICAP_ACCESSIBILITY_OPTION == "Yes") { ?>
                                        <span class="auto_assign001">
                                            <input type="checkbox" name="eHandiCapAccessibility" id="eHandiCapAccessibility" value="Yes" <?php if ($eHandiCapAccessibility == 'Yes') echo 'checked'; ?>>
                                            <p><?php echo $langage_lbl['LBL_PREFER_HANDICAP_ACCESSBILITY_WEB']; ?></p>
                                        </span>
                                <?php } if (isset($CHILD_SEAT_ACCESSIBILITY_OPTION) && $CHILD_SEAT_ACCESSIBILITY_OPTION == "Yes") { ?>
                                        <span class="auto_assign001">
                                            <input type="checkbox" name="eChildSeatAvailable" id="eChildSeatAvailable" value="Yes" <?php if ($eChildSeatAvailable == 'Yes') echo 'checked'; ?>>
                                            <p><?php echo $langage_lbl['LBL_CHILD_SEAT_ADD_VEHICLES']; ?></p>
                                        </span>
        <?php } if (isset($WHEEL_CHAIR_ACCESSIBILITY_OPTION) && $WHEEL_CHAIR_ACCESSIBILITY_OPTION == "Yes") { ?>
                                        <span class="auto_assign001">
                                            <input type="checkbox" name="eWheelChairAvailable" id="eWheelChairAvailable" value="Yes" <?php if ($eWheelChairAvailable == 'Yes') echo 'checked'; ?>>
                                            <p><?php echo $langage_lbl['LBL_WHEEL_CHAIR_ADD_VEHICLES']; ?></p>
                                        </span>
                                <?php } ?>
                                </div>
    <?php } ?>

                            <span class="autoassignbtn auto_assign001">
                                <input type="checkbox" name="eAutoAssign" id="eAutoAssign" value="Yes" <?php if ($eAutoAssign == 'Yes') echo 'checked'; ?>>
                                <p><?php echo $langage_lbl['LBL_AUTO_ASSIGN_WEB']; ?> <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?></p>
                            </span>
                            <span class="auto_assignOr">
                                <h3>OR</h3>
                            </span>
<?php } ?>
                        <span id="showdriverSet001" style="display:none;"><p class="margin-right5">Assigned <?php echo $langage_lbl['LBL_DRIVER_TXT']; ?>: </p><p id="driverSet001"></p></span>
                    </div>
                    <div class="driverlists">
                        <span class="add-booking1">
                            <input name="" type="text" placeholder="Type <?= $langage_lbl['LBL_DRIVER_PROVIDER']; ?> name to search from below list" id="name_keyWord" onKeyUp="get_drivers_list(this.value)">
                        </span>
                        <ul id="driver_main_list" style="">
                            <div class="" id="imageIcons" style="width:100%;">
                                <div align="center">                      
	                                                
                                    <img src="default.gif">                                                               
                                    <span><?php echo $langage_lbl['LBL_RETRIEVING_WEB']; ?> <?php echo $langage_lbl['LBL_DIVER']; ?> list.Please Wait...</span>                       
                                </div>                                                                                 
                            </div>
                        </ul>
                        <input type="text" name="iDriverId" id="iDriverId" value="" class="form-control height-1" required>
                    </div>
                    <!-- 	<div class="service-pickup-type">
                      <h3>Choose Your Payment Method</h3>
                      <div class="radio-but-type"> <b>
                             <input id="vTripPaymentMode_r01" name="vTripPaymentMode" checked type="radio" value="cash" onchange="cardDetailChange(this.value);">
                             <label for="vTripPaymentMode_r01">Cash</label>
                             </b> <b>
                             <input id="vTripPaymentMode_r02" name="vTripPaymentMode" type="radio" value="card" onchange="cardDetailChange(this.value);">
                             <label for="vTripPaymentMode_r02">Credit Card</label>
                             </b> 
                      </div>
                    </div>
                    <span class="cardno-main-detail002">
<?php if ($vUserCard != "") { ?>
                                        <p class='cardno-detail002' style="display:none;"> <b><?php echo $vUserCard; ?></b> <a href="javascript:void(0);" class="change-card002" onclick="showHideToggle('cardDetialClass001','toggle','class');">Click here to change</a></b>
<?php } ?>
                    </span> -->
                </div>
                <div class="map-page">
                    <div class="panel-heading location-map" style="background:none;">
                        <div class="google-map-wrap">
                            <div class="map-color-code">
                                <div>
                                    <label style="width: 20%;"><?php echo $langage_lbl['LBL_PROVIDER_DRIVER_AVAILABILITY']; ?> </label>
                                    <span class="select-map-availability"><select onChange="setNewDriverLocations(this.value)" id="newSelect02">
                                            <option value='' data-id=""><?php echo $langage_lbl['LBL_ALL']; ?></option>
                                            <option value="Available" data-id="img/green-icon.png"><?= $langage_lbl['LBL_AVAILABLE']; ?></option>
                                            <option value="Active" data-id="img/red.png"><?php echo $langage_lbl['LBL_ENROUTE_TO']; ?></option>
                                            <option value="Arrived" data-id="img/blue.png"><?php echo $langage_lbl['LBL_REACHED_PICKUP']; ?></option>
                                            <option value="On Going Trip" data-id="img/yellow.png"><?php echo $langage_lbl['LBL_JOURNEY_STARTED']; ?></option>
                                            <option value="Not Available" data-id="img/offline-icon.png"><?= $langage_lbl['LBL_OFFLINE']; ?></option>
                                        </select></span>
                                </div>
                                <div style="margin-top: 15px;">
                                    <label style="width: 20%;"><?php echo $langage_lbl['LBL_MAP_ZOOM_LEVEL_WEB']; ?></label>
                                    <span>
<?php $radius_driver = array(5, 10, 20, 30); ?>
                                        <select class="form-control form-control-select form-control14" name='radius-id' id="radius-id" onChange="play(this.value)" style="width: 40%;display: inline-block;">
                                            <option value=""> <?php echo $langage_lbl['LBL_SELECT_RADIUS']; ?> </option>
                <?php foreach ($radius_driver as $value) { ?>
                                                <option value="<?php echo $value ?>"><?php echo $value . $DEFAULT_DISTANCE_UNIT . ' Radius'; ?></option>
                <?php } ?>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <div id="map-canvas" class="google-map" style="width:100%; height:500px;"></div>
                        </div>
                    </div>
                </div>
<? if ($userType1 != 'rider') { ?>
                                <?
                                if ($APP_TYPE != 'UberX') {
                                    if ($userType1 == 'company') {
                                        $class = 'total-price total-price1 new';
                                    } else {
                                        $class = 'total-price total-price1';
                                    }
                                    ?>
                        <div class="<?= $class; ?>" > <h3><?php echo $langage_lbl['LBL_FARE_ESTIMATION_TXT']; ?></h3>
                            <hr>
                            <ul>
                                <li id="MinFare">
                                    <b><?php echo $langage_lbl['LBL_MINIMUM_FARE']; ?></b> :
        <?php echo $generalobj->symbol_currency(); ?>
                                    <em id="minimum_fare_price">0</em>
                                </li>
                                <li id="BaseFare">
                                    <b><?php echo $langage_lbl['LBL_BASE_FARE_SMALL_TXT']; ?></b> :
        <?php echo $generalobj->symbol_currency(); ?>
                                    <em id="base_fare_price">0</em>
                                </li>
                                <li id="FixFare" style="display:none">
                                    <b><?php echo $langage_lbl['LBL_FIX_FARE_WEB']; ?></b> :
        <?php echo $generalobj->symbol_currency(); ?>
                                    <em id="fix_fare_price">0</em>
                                </li>
                                <li id="DistanceFare">
                                    <b><?php echo $langage_lbl['LBL_DISTANCE_TXT']; ?> (<em id="dist_fare">0</em> <em id="change_eUnit"><? echo $DEFAULT_DISTANCE_UNIT; ?></em>)</b> :
        <?php echo $generalobj->symbol_currency(); ?>
                                    <em id="dist_fare_price">0</em>
                                </li>
                                <li id="TimeFare">
                                    <b><?php echo $langage_lbl['LBL_TIME_TXT']; ?> (<em id="time_fare">0</em> Minutes)</b> :
        <?php echo $generalobj->symbol_currency(); ?>
                                    <em id="time_fare_price">0</em>
                                </li>
                                <li id="fare_normal" style="display:none">
                                    <b><?php echo $langage_lbl['LBL_NORMAL_FARE']; ?></b> : <?php echo $generalobj->symbol_currency(); ?> 
                                    <em id="normal_fare_price">0</em>
                                </li>

                                <li id="fare_surge" style="display:none">
                                    <b><?php echo $langage_lbl['LBL_SURCHARGE_DIFF_TXT']; ?> (<em id="fare_surge_price">0</em> X)</b>
                                    : <?php echo $generalobj->symbol_currency(); ?> <em id="surge_fare_diff">0</em>
                                </li>
                                <li id="toll_price" style="display:none">
                                    <b><?php echo $langage_lbl['LBL_TOLL_TXT']; ?> </b>
                                    : <?php echo $generalobj->symbol_currency(); ?> <em id="toll_price_val"><?= $fTollPrice ?></em>
                                </li>
                            </ul>
                            <span><?php echo $langage_lbl['LBL_Total_Fare']; ?><b>
        <?php echo $generalobj->symbol_currency(); ?>
                                    <em id="total_fare_price">0</em></b></span> </div>
    <? }
}
?>

                <!-- popup -->
                <div class="map-popup" style="display:none" id="driver_popup"></div>
                <!-- popup end -->
            </div>
            <input type="hidden" name="newType" id="newType" value="">
            <input type="hidden" name="submitbtn" id="submitbtn" value="submitform">
            <div style="clear:both;"></div>
            <div class="book-now-reset add-booking-button"><span>
                    <input type="submit" class="save btn-info button-submit" name="submitbutton" id="submitbutton" value="<?php echo $langage_lbl['LBL_REQUEST_DRIVER_WEB']; ?>">
                    <!-- <input type="reset" class="save btn-info button-submit" name="reset" id="reset12" value="Reset" > -->
                </span></div>
        </form>
        <div style="clear:both;"></div>
        <div class="admin-notes">
            <h4>Notes:</h4>
            <ul>
                <li>
                    Administrator can Add / Edit <?php echo $langage_lbl['LBL_RIDER_RIDE_MAIN_SCREEN']; ?> later booking on this page.
                </li>
                <li>
<?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> current availability is not connected with booking being made. Please confirm future avaialbility of <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> before doing booking.
                </li>
                <li>Adding booking from here will not send request to <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> immediately.</li> 
                <li>In case of "Auto Assign <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?>" option selected, <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?>(s) get automatic request before 8-12 minutes of actual booking time.</li>
                <li>In case of "Auto Assign <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?>" option not selected, <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?>(s) get booking confirmation sms as well as reminder sms before 30 minutes of actual booking. <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> has to start the scheduled <?= $langage_lbl['LBL_TRIP_TXT_ADMIN']; ?> by going to "Your <?= $langage_lbl['LBL_TRIP_TXT_ADMIN']; ?>" >> Upcoming section from <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> App.</li>
                <li>In case of "Auto Assign <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?>", the competitive algorithm will be followed instead of one you have selected in settings.</li>
            </ul>
        </div>
    </div>
    <!--END PAGE CONTENT -->

</div>
<div style="clear:both;"></div>
<!-- Card Modal -->
<div id="CardModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <form id="cardPayForm" class="cardDetialClass001" novalidate autocomplete="on" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?= $langage_lbl['LBL_CARD_PAYMENT_DETAILS']; ?></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" value="GenerateToken" >
                    <span><div class="form-group"><input name="cardNo" type="text" placeholder="Enter Your Card Number" class="cc-number"  data-stripe="number"/></div></span>
                    <span>
                        <div class="form-group"><input type="text" name="cardExp" class="add-car-mm cc-exp" placeholder="MM / YYYY" data-stripe="exp"/></div>
                        <div class="form-group"><input type="text" name="cardCvv" class="add-car-cvv cc-cvc" placeholder="cvv" data-stripe="cvc"/></div>
                        <h2 class="validation"></h2>
                </div>
                <div class="modal-footer">
                    <b class='card-btn002'><a href="javascript:void(0);" class="btn btn-success submit" id="validatePayCard"><?= $langage_lbl['Add Card']; ?></a></b>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--Wallet Low Balance-->
<div class="modal fade" id="usermodel" tabindex="-1" role="dialog" aria-labelledby="usermodel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="inactiveModalLabel"><?= $langage_lbl['LBL_LOW_WALLET_BALANCE_WEB']; ?></h4>
            </div>
	
	
	 
            <div class="modal-body">
                <p><span style="font-size: 15px;"> This <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> is having low balance in his wallet and is not able to accept cash ride. Would you still like to assign this <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?>?</span></p>
                <p><b style="font-size: 15px;"> <?= $langage_lbl['LBL_MINIMUM_REQUIRED_BALANCE_WEB']; ?> : </b><span style="font-size: 15px;"><?php echo $generalobj->symbol_currency() . " " . number_format($WALLET_MIN_BALANCE, 2); ?></span></p>
                <p><b style="font-size: 15px;"> <?= $langage_lbl['LBL_AVAILABLE_BALANCE_WEB']; ?> : </b><span style="font-size: 15px;"><?php echo $generalobj->symbol_currency(); ?> <span id="usr-bal"></span></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $langage_lbl['LBL_NOT_NOW_WEB']; ?></button> 
                <button type="button" class="btn btn-success btn-ok action_modal_submit" data-dismiss="modal" onClick="AssignDriver('');"><?= $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
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
                <h4 class="modal-title" id="inactiveModalLabel"><?php echo $langage_lbl['LBL_USER_DETAIL']; ?></h4>
            </div>
            <div class="modal-body">
                <span style="font-size: 15px;"><?php echo $langage_lbl['LBL_USER_STATUS_IN_MANUAL_BOOKING_WEB']; ?>  </span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal"><?= $langage_lbl['LBL_CONTINUE_BTN']; ?></button>
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
                <h4 class="modal-title" id="inactiveModalLabel"><?php echo $langage_lbl['LBL_CONFIRM_SURCHARGE_WEB']; ?></h4>
            </div>
            <div class="modal-body">
                <p><span style="font-size: 15px;"><?php echo $langage_lbl['LBL_SURCHARGE_WEB']; ?>  <?php echo $langage_lbl['LBL_SURCHARGE_TRIP_UNDER_TIMING_WEB']; ?></span></p>
                <table style="font-size: 15px;" cellspacing="5" cellpadding="5">
                    <tr>
                        <td width="100px"> <b><?php echo $langage_lbl['LBL_SURGE_TYPE_WEB']; ?></b></td>
                        <td> : <span id="surge_type"></span><?php echo $langage_lbl['LBL_SURCHARGE_WEB']; ?> </td>
                    </tr>
                    <tr>
                        <td><?php echo $langage_lbl['LBL_SURGE_FACTOR_WEB']; ?><b></b></td>
                        <td> : <span id="surge_factor"></span> X</td>
                    </tr>
                    <tr>
                        <td><b><?php echo $langage_lbl['LBL_SURGE_TIMING_WEB']; ?></b></td>
                        <td> : <span id="surge_timing"></span></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button> -->
                <button type="button" class="btn btn-success btn-ok action_modal_submit" data-dismiss="modal" ><?php echo $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
            </div>
        </div>
    </div>
</div>
<!--end surcharge confirmation-->
<link rel="stylesheet" type="text/css" media="screen" href="<?= $tconfig["tsite_url_main_admin"]; ?>css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"]; ?>js/moment.min.js"></script>
<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"]; ?>js/bootstrap-datetimepicker.min.js"></script>
<link rel="stylesheet" href="<?= $tconfig["tsite_url_main_admin"]; ?>css/select2/select2.min.css" type="text/css" >
<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"]; ?>js/plugins/select2.min.js"></script>
<script>
                                    var eType = "";
                                    var APP_DELIVERY_MODE = '<?= $APP_DELIVERY_MODE ?>';
                                    var ENABLE_TOLL_COST = "<?= $ENABLE_TOLL_COST ?>";
                                    //alert("<?php echo $APP_TYPE; ?>");
                                    switch ("<?php echo $APP_TYPE; ?>") {
                                        case "Ride-Delivery":
                                            if (APP_DELIVERY_MODE == "Multi") {
                                                eType = 'Ride';
                                            } else {
                                                eType = $('input[name=eType]:checked').val();
                                            }
                                            break;
                                        case "Ride-Delivery-UberX":
                                            /*if(APP_DELIVERY_MODE == "Multi"){
                                             eType = 'Ride';
                                             } else {*/
                                            eType = $('input[name=eType]:checked').val();
                                            /*}*/
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
<? if ($userType1 != 'rider' && $userType1 != 'company') { ?>
                                                $('.auto_assign001').show();
<? } ?>
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
                                                $('#datetimepicker4').removeAttr('required');
                                            } else {
                                                $(".dateSchedule").show();
                                                $('#datetimepicker4').attr('required', 'required');
                                            }
                                        } else if (etype == 'Deliver') {
                                            $('#ride-delivery-type').show();
                                            $('#ride-type').hide();
<? if ($userType1 != 'rider') { ?>
                                                $('.auto_assign001').show();
<? } ?>
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
<? if ($userType1 != 'rider') { ?>
                                                $('.auto_assign001').hide();
<? } ?>

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
                                    var geocoder;
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
                                    var geocoder = new google.maps.Geocoder();
                                    var directionsService = new google.maps.DirectionsService(); // For Route Services on map
                                    var directionsOptions = {// For Polyline Route line options on map
                                        polylineOptions: {
                                            strokeColor: '#FF7E00',
                                            strokeWeight: 5
                                        }
                                    };
                                    var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
                                    var showsurgemodal = "Yes";

                                    function setDriverListing(iVehicleTypeId) {
                                        dBooking_date = $("#datetimepicker4").val();
                                        vCountry = $("#vCountry").val();
                                        keyword = $("#name_keyWord").val();
                                        eLadiesRide = 'No';
                                        eHandicaps = 'No';
                                        eChildSeat = 'No';
                                        eWheelChair = 'No';
                                        var sess_iCompanyId = '<?= $sess_iCompanyId; ?>';
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
                                            url: "<?= $tconfig["tsite_url"] ?>booking/get_available_driver_list.php",
                                            dataType: "html",
                                            data: {vCountry: vCountry, type: '', iVehicleTypeId: iVehicleTypeId, keyword: keyword, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, eChildSeat: eChildSeat, eWheelChair: eWheelChair, dBooking_date: dBooking_date, AppeType: eType, sess_iCompanyId: sess_iCompanyId},
                                            success: function (dataHtml2) {
                                                if (dataHtml2 != "") {
                                                    $('#driver_main_list').show();
                                                    $('#driver_main_list').html(dataHtml2);
                                                    if ($("#eAutoAssign").is(':checked')) {
                                                        $(".assign-driverbtn").attr('disabled', 'disabled');
                                                    }
                                                } else {
                                                    $('#driver_main_list').html('<h4 style="margin:25px 0 0 15px">Sorry , No <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> Found.</h4>');
                                                    $('#driver_main_list').show();
                                                }
                                            }, error: function (dataHtml2) {

                                            }
                                        });
                                    }

                                    function AssignDriver(driverId) {
                                        if (driverId == "") {
                                            driverId = $('#iDriverId_temp').val();
                                        }
                                        $('#iDriverId').val(driverId);
                                        $("#showdriverSet001").show();
                                        $("#driverSet001").html($('.driver_' + driverId).html());
                                    }

                                    function checkUserBalance(driverId) {
                                        $.ajax({
                                            type: "POST",
                                            url: "<?= $tconfig["tsite_url"] ?>booking/ajax_get_user_balance.php",
                                            data: "driverId=" + driverId + "&type=Driver",
                                            success: function (data) {
                                                data1 = data.split("|");
                                                var CDE = '<?= $COMMISION_DEDUCT_ENABLE ?>';
                                                var Min_Bal = '<?= $WALLET_MIN_BALANCE ?>';

                                                if (CDE == "Yes") {
                                                    if (parseFloat(data1[1]) < parseFloat(Min_Bal)) {
                                                        var amt = parseFloat(data1[1]).toFixed(2);
                                                        $("#usr-bal").text(amt);
                                                        $("#iDriverId_temp").val(driverId);
                                                        $("#usermodel").modal('show');
                                                        return false;
                                                    } else {
                                                        AssignDriver(driverId);
                                                        return false;
                                                    }
                                                } else {
                                                    AssignDriver(driverId);
                                                    return false;
                                                }
                                            }, error: function (dataHtml2) {

                                            }
                                        });
                                    }

                                    function setDriversMarkers(flag) {
                                        newType = $("#newType").val();
                                        vType = $("#iVehicleTypeId").val();
                                        var sess_iCompanyId = '<?= $sess_iCompanyId; ?>';
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

                                        $.ajax({
                                            type: "POST",
                                            url: "<?= $tconfig["tsite_url"] ?>booking/get_map_drivers_list.php",
                                            dataType: "json",
                                            data: {type: newType, iVehicleTypeId: vType, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, eChildSeat: eChildSeat, eWheelChair: eWheelChair, sess_iCompanyId: sess_iCompanyId},
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
                                                    map.fitBounds(bounds);
                                                    //map.setZoom(13);
                                                }
                                                setDriverListing(vType);
                                            },
                                            error: function (dataHtml) {

                                            }
                                        });
                                    }

                                    function initialize() {
                                        var thePoint = new google.maps.LatLng('-9.3726955', '-40.545069');
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
                                        showVehicleCountryVise('<?php echo $vCountry ?>', '<?php echo $iVehicleTypeId; ?>', eType);
<?php if ($action == "Edit") { ?>
                                            //callEditFundtion(); // now comment
                                            // show_locations();
                                            // showVehicleCountryVise('<?php echo $vCountry ?>','<?php echo $iVehicleTypeId; ?>');
<?php } ?>
                                        //setDriversMarkers('test');
                                        //alert('test');

<?php if ($from_lat_long != '') { ?>
                                            show_locations();
<?php } ?>
                                    }

                                    $(document).ready(function () {
                                        google.maps.event.addDomListener(window, 'load', initialize);
                                        setDriversMarkers('test');
                                        $("#eType").val(eType);
                                        $('input[type=radio][name=eType]').change(function () {
                                            eType = $('input[name=eType]:checked').val();
                                        });
                                    });

                                    function play(radius) {
                                        // return Math.round(14-Math.log(radius)/Math.LN2);
                                        var pt = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());
                                        map.setCenter(pt);
                                        var newRadius = Math.round(24 - Math.log(radius) / Math.LN2);
                                        newRadius = newRadius - 9;
                                        map.setZoom(newRadius);
                                    }
                                    function getAddress(mDlatitude, mDlongitude, addId) {
                                        var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);
                                        geocoder.geocode({'latLng': mylatlang},
                                                function (results, status) {
                                                    // console.log(results);
                                                    if (status == google.maps.GeocoderStatus.OK) {
                                                        if (results[0]) {
                                                            // document.getElementById(addId).value = results[0].formatted_address;
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
                                                newIcon = '<?= $tconfig["tsite_url"] ?>/webimages/upload/mapmarker/PinTo.png';
                                            } else {
                                                newIcon = '<?= $tconfig["tsite_url"] ?>/webimages/upload/mapmarker/PinFrom.png';
                                            }
                                        } else if (valIcon == 'to_loc') {
                                            newIcon = '<?= $tconfig["tsite_url"] ?>/webimages/upload/mapmarker/PinTo.png';
                                        } else {
                                            newIcon = '<?= $tconfig["tsite_url"] ?>/webimages/upload/mapmarker/PinTo.png';
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
                                                // console.log(event);
                                                var lat = event.latLng.lat();
                                                var lng = event.latLng.lng();
                                                var myLatlongs = new google.maps.LatLng(lat, lng);
                                                showsurgemodal = "No";

                                                $("#from_lat").val(lat);
                                                $("#from_long").val(lng);
                                                $("#from_lat_long").val(myLatlongs);
                                                getAddress(lat, lng, 'from');
                                                routeDirections();
                                            });
                                        }
                                        if (valIcon == 'to_loc') {
                                            marker.addListener('dragend', function (event) {
                                                var lat = event.latLng.lat();
                                                var lng = event.latLng.lng();
                                                var myLatlongs1 = new google.maps.LatLng(lat, lng);
                                                showsurgemodal = "No";

                                                $("#to_lat").val(lat);
                                                $("#to_long").val(lng);
                                                $("#to_lat_long").val(myLatlongs1);
                                                getAddress(lat, lng, 'to');
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
                                            var request = {
                                                origin: newFrom, // From locations latlongs
                                                destination: newTo, // To locations latlongs
                                                travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
                                            };

                                            //Draw route from the object
                                            directionsService.route(request, function (response, status) {
                                                if (status == google.maps.DirectionsStatus.OK) {
                                                    // Check for allowed and disallowed.
                                                    var response1 = JSON.stringify(response);
                                                    /*$.ajax({
                                                     type: "POST",
                                                     url: '<?= $tconfig["tsite_url"] ?>booking/checkForRestriction.php',
                                                     dataType: 'html',
                                                     data: {fromLat: $("#from_lat").val(),fromLong: $("#from_long").val(),toLat: $("#to_lat").val(),toLong: $("#to_long").val(),type:'both'},
                                                     success: function(dataHtml5)
                                                     {
                                                     if(dataHtml5 != ''){
                                                     alert(dataHtml5);
                                                     }
                                                     },
                                                     error: function(dataHtml5)
                                                     {
                                                     }
                                                     });*/

                                                    // console.log(response);
                                                    directionsDisplay.setMap(map);
                                                    directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
                                                    directionsDisplay.setDirections(response); // Set route
                                                    var route = response.routes[0];
                                                    for (var i = 0; i < route.legs.length; i++) {
                                                        $("#distance").val(route.legs[i].distance.value);
                                                        $("#duration").val(route.legs[i].duration.value);
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

                                                    $.ajax({
                                                        type: "POST",
                                                        url: '<?= $tconfig["tsite_url"] ?>booking/ajax_estimate_by_vehicle_type.php',
                                                        dataType: 'json',
                                                        data: {'vehicleId': vehicleId, 'booking_date': booking_date, 'vCountry': vCountry, 'FromLatLong': newFrom, 'ToLatLong': newTo},
                                                        success: function (dataHtml)
                                                        {
                                                            if (dataHtml != "") {
                                                                // var result = dataHtml.split(':');
                                                                var iBaseFare = parseFloat(dataHtml.iBaseFare).toFixed(2);
                                                                var fPricePerKM = parseFloat(dataHtml.fPricePerKM).toFixed(2);
                                                                var fPricePerMin = parseFloat(dataHtml.fPricePerMin).toFixed(2);
                                                                var iMinFare = parseFloat(dataHtml.iMinFare).toFixed(2);
                                                                var fPickUpPrice = parseFloat(dataHtml.fPickUpPrice).toFixed(2);
                                                                var fNightPrice = parseFloat(dataHtml.fNightPrice).toFixed(2);
                                                                var fSurgePrice = parseFloat(dataHtml.fSurgePrice).toFixed(2);
                                                                var SurgeType = dataHtml.SurgeType;
                                                                var Time = dataHtml.Time;
                                                                eFlatTrip = dataHtml.eFlatTrip;
                                                                var fFlatTripPrice = dataHtml.fFlatTripPrice;

                                                                if (eFlatTrip == 'Yes') {
                                                                    fFlatTripPrice = parseFloat(fFlatTripPrice).toFixed(2);
                                                                    $('#fix_fare_price').text(fFlatTripPrice);
                                                                    fPricePerMin = 0;
                                                                    fPricePerKM = 0;
                                                                    iMinFare = 0;
                                                                    $('#eFlatTrip').val(eFlatTrip);
                                                                    $('#fFlatTripPrice').val(fFlatTripPrice);
                                                                    $("#FixFare").show();
                                                                    $("#BaseFare").hide();
                                                                    $("#MinFare").hide();
                                                                    $("#DistanceFare").hide();
                                                                    $("#TimeFare").hide();
                                                                    $("#toll_price").hide();
                                                                } else {
                                                                    $('#eFlatTrip').val(eFlatTrip);
                                                                    $("#FixFare").hide();
                                                                    $("#BaseFare").show();
                                                                    $("#MinFare").show();
                                                                    $("#DistanceFare").show();
                                                                    $("#TimeFare").show();
                                                                }
                                                                var increased = parseInt($('#fTollPrice').val());
                                                                if (isNaN(increased) || increased <= 0) {
                                                                    $("#vTollPriceCurrencyCode").val('');
                                                                    $("#fTollPrice").val('0');
                                                                    $("#eTollSkipped").val('No');
                                                                }

                                                                $('#minimum_fare_price').text(iMinFare);
                                                                $('#base_fare_price').text(iBaseFare);
                                                                $('#dist_fare_price').text(parseFloat(fPricePerKM * $('#dist_fare').text()).toFixed(2));
                                                                /* var eunit = $("#eUnit").val();
                                                                 if(eunit == "Miles"){
                                                                 $('#dist_fare_price').text(parseFloat((fPricePerKM*($('#dist_fare').text()*1.6))).toFixed(2));
                                                                 } */
                                                                $('#time_fare_price').text(parseFloat(fPricePerMin * $('#time_fare').text()).toFixed(2));

                                                                /*	if($('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes'  && eType != 'UberX'){
                                                                 $("#toll_price").show();
                                                                 } else {
                                                                 $("#toll_price").hide();
                                                                 }*/
                                                                if (ENABLE_TOLL_COST == 'Yes') {
                                                                    if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                        $("#toll_price").show();
                                                                        $('#toll_price_val').text(tollcostval);
                                                                    } else {
                                                                        $("#toll_price").hide();
                                                                    }
                                                                }

                                                                if (eFlatTrip == 'Yes') {
                                                                    var totalPrice = (parseFloat($('#fix_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                                } else {
                                                                    if (ENABLE_TOLL_COST == 'Yes') {
                                                                        if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                            var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat(tollcostval) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                                        } else {
                                                                            var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                                        }
                                                                    } else {
                                                                        var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                                    }
                                                                }

                                                                if (fSurgePrice > 1) {
                                                                    if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                        var normalfare = parseFloat(totalPrice - tollcostval).toFixed(2);
                                                                    } else {
                                                                        var normalfare = totalPrice;
                                                                    }
                                                                    $('#normal_fare_price').text(normalfare);
                                                                    if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                        var totalfare = (totalPrice - tollcostval);
                                                                        var surgefare = parseFloat(totalfare * fSurgePrice).toFixed(2);
                                                                        var surgefarenew = (parseFloat(tollcostval) + parseFloat(surgefare)).toFixed(2);
                                                                        var totalPrice = surgefarenew;
                                                                    } else {
                                                                        var surgefare = parseFloat(totalPrice * fSurgePrice).toFixed(2);
                                                                        var totalPrice = surgefare;
                                                                    }
                                                                    $('#totalcost').text(surgefare);
                                                                    var difference = parseFloat(surgefare - normalfare).toFixed(2);
                                                                    if (SurgeType == "Night") {
                                                                        $("#fNightPrice").val(fSurgePrice);
                                                                        $("#fPickUpPrice").val(1);
                                                                    } else {
                                                                        $("#fNightPrice").val(1);
                                                                        $("#fPickUpPrice").val(fSurgePrice);

                                                                    }
                                                                    $("#surge_fare_diff").text(difference);
                                                                    $("#fare_surge_price").text(fSurgePrice);
                                                                    $("#fare_surge").show();

                                                                    /*if(showsurgemodal == "Yes"){
                                                                     $("#surge_factor").text(fSurgePrice);
                                                                     $("#surge_type").text(SurgeType);
                                                                     $("#surge_timing").text(Time);
                                                                     $("#surgemodel").modal('show');
                                                                     }*/

                                                                    if (eFlatTrip == 'Yes') {
                                                                        $("#fare_normal").hide();
                                                                    } else {
                                                                        $("#fare_normal").show();
                                                                    }
                                                                    //$("#fare_normal").show();

                                                                } else {
                                                                    $("#fare_surge").hide();
                                                                    $("#fare_normal").hide();
                                                                    $("#fNightPrice").val(1);
                                                                    $("#fPickUpPrice").val(1);
                                                                }
                                                                showsurgemodal = "Yes";

                                                                if (parseFloat(totalPrice) >= parseFloat($('#minimum_fare_price').text())) {
                                                                    $('#total_fare_price').text(totalPrice);
                                                                    if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                        $('#totalcost').text(totalPrice - tollcostval);
                                                                    } else {
                                                                        $('#totalcost').text(totalPrice);
                                                                    }
                                                                    $("#MinFare").hide();
                                                                } else {
                                                                    $('#total_fare_price').text($('#minimum_fare_price').text());
                                                                    $('#totalcost').text($('#minimum_fare_price').text());
                                                                    $("#MinFare").show();
                                                                }
                                                            } else {
                                                                $('#minimum_fare_price').text('0');
                                                                $('#base_fare_price').text('0');
                                                                $('#dist_fare_price').text('0');
                                                                $('#time_fare_price').text('0');
                                                                $('#total_fare_price').text('0');
                                                            }
                                                        }
                                                    });
                                                } else {
                                                    alert("Directions request failed: " + status);
                                                }
                                            });

<? if ($iVehicleTypeId != "") { ?>
                                                var iVehicleTypeId = '<?= $iVehicleTypeId ?>';
                                                //getFarevalues(iVehicleTypeId);
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
                                    function from_to(from, to) {
                                        //  clearThat();
                                        DeleteMarkers('from_loc');
                                        DeleteMarkers('to_loc');
                                        if (from == '')
                                            from = $('#from').val();

                                        if (to == '')
                                            to = $('#to').val();
                                        //alert("from_to" + from +"   to "+to);
                                        $("#from_lat_long").val('');
                                        $("#from_lat").val('');
                                        $("#from_long").val('');
                                        $("#to_lat_long").val('');
                                        $("#to_lat").val('');
                                        $("#to_long").val('');

                                        // var chks = document.getElementsByName('loc');
                                        // var waypts = [];
                                        if (from != '') {
                                            geocoder.geocode({'address': from}, function (results, status) {
                                                if (status == google.maps.GeocoderStatus.OK) {
                                                    if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                                                        // console.log(results[0].geometry.location);
                                                        $("#from_lat_long").val((results[0].geometry.location));
                                                        $("#from_lat").val(results[0].geometry.location.lat());
                                                        $("#from_long").val(results[0].geometry.location.lng());

                                                        setMarker(results[0].geometry.location, 'from_loc');
                                                    } else {
                                                        alert("No results found");
                                                    }
                                                } else {
                                                    var place19 = autocomplete_from.getPlace();
                                                    $("#from_lat_long").val(place19.geometry.location);
                                                }
                                            });
                                        }
                                        if (to != '') {
                                            geocoder.geocode({'address': to}, function (results, status) {
                                                if (status == google.maps.GeocoderStatus.OK) {
                                                    if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                                                        $("#to_lat_long").val((results[0].geometry.location));
                                                        $("#to_lat").val(results[0].geometry.location.lat());
                                                        $("#to_long").val(results[0].geometry.location.lng());
                                                        setMarker(results[0].geometry.location, 'to_loc');
                                                    } else {
                                                        alert("No results found");
                                                    }
                                                } else {
                                                    var place20 = autocomplete_to.getPlace();
                                                    $("#to_lat_long").val(place20.geometry.location);
                                                }
                                            });
                                        }
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

                                        // var fromLatlongs = $("#from_lat").val()+", "+$("#from_long").val();
                                        // var toLatlongs = $("#to_lat").val()+", "+$("#to_long").val();
                                    }

                                    $(function () {
                                        // newDate = new Date();
                                        // var today = new Date();
                                        // today.setHours(today.getHours() + 1);

                                        $('#datetimepicker4').datetimepicker({
                                            format: 'YYYY-MM-DD HH:mm:ss',
                                            minDate: moment(),
                                            ignoreReadonly: true,
                                            sideBySide: true,
                                            widgetPositioning: {
                                                vertical: 'top'
                                            }

                                        })/*.on('dp.change', function(e) {
                                         $('#datetimepicker4').data("DateTimePicker").minDate(moment().add(5,'m'))
                                         })*/;
                                        // date: new Date(1434544882775)


                                        var from = document.getElementById('from');
                                        autocomplete_from = new google.maps.places.Autocomplete(from);
                                        google.maps.event.addListener(autocomplete_from, 'place_changed', function () {
                                            var place = autocomplete_from.getPlace();
                                            $("#from_lat_long").val(place.geometry.location);
                                            $("#from_lat").val(place.geometry.location.lat());
                                            $("#from_long").val(place.geometry.location.lng());
                                            // remove disable from zoom level when from has value
                                            $('#radius-id').prop('disabled', false);
                                            if (from != '') {
                                                checkrestrictionfrom('from');
                                            }
                                            show_locations();
                                        });
                                        var to = document.getElementById('to');
                                        autocomplete_to = new google.maps.places.Autocomplete(to);
                                        google.maps.event.addListener(autocomplete_to, 'place_changed', function () {
                                            var place = autocomplete_to.getPlace();
                                            $("#to_lat_long").val(place.geometry.location);
                                            $("#to_lat").val(place.geometry.location.lat());
                                            $("#to_long").val(place.geometry.location.lng());
                                            if (to != '') {
                                                checkrestrictionto('to');
                                            }
                                            show_locations();
                                        });


                                    });


                                    function isNumberKey(evt) {
                                        showPhoneDetail();
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
                                            url: '<?= $tconfig["tsite_url"] ?>booking/change_code.php',
                                            dataType: 'json',
                                            data: {id: id, eUnit: 'yes'},
                                            success: function (dataHTML)
                                            {
                                                document.getElementById("vPhoneCode").value = dataHTML.vPhoneCode;
                                                document.getElementById("eUnit").value = dataHTML.eUnit;
                                                document.getElementById("vRideCountry").value = dataHTML.vCountryCode;
                                                document.getElementById("vTimeZone").value = dataHTML.vTimeZone;
                                                $("#change_eUnit").text(dataHTML.eUnit);
                                                var substr = <?php echo json_encode($radius_driver); ?>;
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
                                        changeCode(con, '<?php echo $iVehicleTypeId; ?>');
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
                                                url: "<?= $tconfig["tsite_url"] ?>booking/get_driver_detail_popup.php",
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
                                        /*alert(countryId);
                                         alert(eType);*/
                                        $.ajax({
                                            type: "POST",
                                            url: "<?= $tconfig["tsite_url"] ?>booking/ajax_booking_details.php",
                                            dataType: "html",
                                            data: {countryId: countryId, type: 'getVehicles', iVehicleTypeId: vehicleId, eType: eType},
                                            success: function (dataHtml2) {
                                                $('#iVehicleTypeId').html(dataHtml2);
                                                // $("#driver_popup").show("slide", {direction: "right"}, 700);
                                            }, error: function (dataHtml2) {

                                            }
                                        });
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
                                        var phone = $('#vPhone').val();
                                        var phoneCode = $('#vPhoneCode').val();
                                        if (phone != "" && phoneCode != "") {
                                            $.ajax({
                                                type: "POST",
                                                url: '<?= $tconfig["tsite_url"] ?>booking/ajax_find_rider_by_number.php',
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
<? if ($action == 'Add') { ?>
                                                            $("#promocode").val('');
<? } ?>

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
                                        var tollcostval = $('#fTollPrice').val();
                                        if (vehicleId == "") {
                                            vehicleId = $("#iVehicleTypeId").val();
                                        }
                                        if (($("#from").val() != "") && ($("#to").val() != "")) {
                                            var FromLatLong = $("#from_lat").val() + ", " + $("#from_long").val();
                                            var ToLatLong = $("#to_lat").val() + ", " + $("#to_long").val();
                                        }
                                        // alert(vehicleId);
                                        if (vehicleId != "") {
                                            $.ajax({
                                                type: "POST",
                                                url: '<?= $tconfig["tsite_url"] ?>booking/ajax_estimate_by_vehicle_type.php',
                                                dataType: 'json',
                                                data: {'vehicleId': vehicleId, 'booking_date': booking_date, 'vCountry': vCountry, 'FromLatLong': FromLatLong, 'ToLatLong': ToLatLong},
                                                success: function (dataHtml)
                                                {
                                                    if (dataHtml != "") {
                                                        // var result = dataHtml.split(':');
                                                        var iBaseFare = parseFloat(dataHtml.iBaseFare).toFixed(2);
                                                        var fPricePerKM = parseFloat(dataHtml.fPricePerKM).toFixed(2);
                                                        var fPricePerMin = parseFloat(dataHtml.fPricePerMin).toFixed(2);
                                                        var iMinFare = parseFloat(dataHtml.iMinFare).toFixed(2);
                                                        var fPickUpPrice = parseFloat(dataHtml.fPickUpPrice).toFixed(2);
                                                        var fNightPrice = parseFloat(dataHtml.fNightPrice).toFixed(2);
                                                        var fSurgePrice = parseFloat(dataHtml.fSurgePrice).toFixed(2);
                                                        var SurgeType = dataHtml.SurgeType;
                                                        var Time = dataHtml.Time;
                                                        eFlatTrip = dataHtml.eFlatTrip;
                                                        var fFlatTripPrice = dataHtml.fFlatTripPrice;

                                                        if (eFlatTrip == 'Yes') {
                                                            fFlatTripPrice = parseFloat(fFlatTripPrice).toFixed(2);
                                                            $('#fix_fare_price').text(fFlatTripPrice);
                                                            fPricePerMin = 0;
                                                            fPricePerKM = 0;
                                                            iMinFare = 0;
                                                            $('#eFlatTrip').val(eFlatTrip);
                                                            $('#fFlatTripPrice').val(fFlatTripPrice);
                                                            $("#FixFare").show();
                                                            $("#BaseFare").hide();
                                                            $("#MinFare").hide();
                                                            $("#DistanceFare").hide();
                                                            $("#TimeFare").hide();
                                                        } else {
                                                            $('#eFlatTrip').val(eFlatTrip);
                                                            $("#FixFare").hide();
                                                            $("#BaseFare").show();
                                                            $("#MinFare").show();
                                                            $("#DistanceFare").show();
                                                            $("#TimeFare").show();
                                                        }

                                                        $('#minimum_fare_price').text(iMinFare);
                                                        $('#base_fare_price').text(iBaseFare);
                                                        $('#dist_fare_price').text(parseFloat(fPricePerKM * $('#dist_fare').text()).toFixed(2));
                                                        /* var eunit = $("#eUnit").val();
                                                         if(eunit == "Miles"){
                                                         $('#dist_fare_price').text(parseFloat((fPricePerKM*($('#dist_fare').text()*1.6))).toFixed(2));
                                                         } */

                                                        $('#time_fare_price').text(parseFloat(fPricePerMin * $('#time_fare').text()).toFixed(2));
                                                        if (ENABLE_TOLL_COST == 'Yes') {
                                                            if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                $("#toll_price").show();
                                                                $('#toll_price_val').text(tollcostval);
                                                            } else {
                                                                $("#toll_price").hide();
                                                            }
                                                        }
                                                        if (eFlatTrip == 'Yes') {
                                                            var totalPrice = (parseFloat($('#fix_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                        } else {
                                                            if (ENABLE_TOLL_COST == 'Yes') {
                                                                if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                    var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat(tollcostval) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                                } else {
                                                                    var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                                }
                                                            } else {
                                                                var totalPrice = (parseFloat($('#base_fare_price').text()) + parseFloat($('#dist_fare_price').text()) + parseFloat($('#time_fare_price').text())).toFixed(2);
                                                            }
                                                        }

                                                        if (fSurgePrice > 1) {
                                                            if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                var normalfare = parseFloat(totalPrice - tollcostval).toFixed(2);
                                                            } else {
                                                                var normalfare = totalPrice;
                                                            }
                                                            $('#normal_fare_price').text(normalfare);
                                                            if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                var totalfare = (totalPrice - tollcostval);
                                                                var surgefare = parseFloat(totalfare * fSurgePrice).toFixed(2);
                                                                var surgefarenew = (parseFloat(tollcostval) + parseFloat(surgefare)).toFixed(2);
                                                                var totalPrice = surgefarenew;
                                                            } else {
                                                                var surgefare = parseFloat(totalPrice * fSurgePrice).toFixed(2);
                                                                var totalPrice = surgefare;
                                                            }
                                                            $('#totalcost').text(surgefare);
                                                            var difference = parseFloat(surgefare - normalfare).toFixed(2);
                                                            // console.log(normalfare+" "+surgefare+" "+difference);
                                                            if (SurgeType == "Night") {
                                                                $("#fNightPrice").val(fSurgePrice);
                                                                $("#fPickUpPrice").val(1);
                                                            } else {
                                                                $("#fNightPrice").val(1);
                                                                $("#fPickUpPrice").val(fSurgePrice);

                                                            }
                                                            $("#surge_fare_diff").text(difference);
                                                            $("#fare_surge_price").text(fSurgePrice);
                                                            $("#fare_surge").show();
                                                            if (showsurgemodal == "Yes") {
                                                                $("#surge_factor").text(fSurgePrice);
                                                                $("#surge_type").text(SurgeType);
                                                                $("#surge_timing").text(Time);
                                                                $("#surgemodel").modal('show');
                                                            }

                                                            if (eFlatTrip == 'Yes') {
                                                                $("#fare_normal").hide();
                                                            } else {
                                                                $("#fare_normal").show();
                                                            }

                                                            showsurgemodal = "Yes";
                                                        } else {
                                                            $("#fare_surge").hide();
                                                            $("#fare_normal").hide();
                                                            $("#fNightPrice").val(1);
                                                            $("#fPickUpPrice").val(1);
                                                        }

                                                        if (parseFloat(totalPrice) >= parseFloat($('#minimum_fare_price').text())) {
                                                            $('#total_fare_price').text(totalPrice);
                                                            if ($('#fTollPrice').val() > 0 && $('#eTollSkipped').val() == 'No' && eFlatTrip != 'Yes' && eType != 'UberX') {
                                                                $('#totalcost').text(totalPrice - tollcostval);
                                                            } else {
                                                                $('#totalcost').text(totalPrice);
                                                            }
                                                            $("#MinFare").hide();
                                                        } else {
                                                            $('#total_fare_price').text($('#minimum_fare_price').text());
                                                            $('#totalcost').text($('#minimum_fare_price').text());
                                                            $("#MinFare").show();
                                                        }

                                                    } else {
                                                        $('#minimum_fare_price').text('0');
                                                        $('#base_fare_price').text('0');
                                                        $('#dist_fare_price').text('0');
                                                        $('#time_fare_price').text('0');
                                                        $('#total_fare_price').text('0');
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
                                        //var markers = [];//some array
                                        // var bounds = new google.maps.LatLngBounds();
                                        // for (var i = 0; i < driverMarkers.length; i++) {
                                        // bounds.extend(driverMarkers[i].getPosition());
                                        // }

                                        // map.fitBounds(bounds);
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
                                                url: '<?= $tconfig["tsite_url"] ?>booking/ajax_checkBooking_email.php',
                                                data: 'vEmail=' + statusVal,
                                                success: function (dataHtml)
                                                {
                                                    var testEstatus = dataHtml.trim();
                                                    if (testEstatus != 'Active' && testEstatus != '') {
                                                        if (confirm("The selected user account is in 'Inactive / Deleted' mode. Do you want to Active this User ?'")) {
                                                            eTypeQ11 = 'no';
                                                            $("#add_booking_form").attr('action', '<?= $tconfig["tsite_url"] ?>booking/action_booking.php');
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
                                                        $("#add_booking_form").attr('action', '<?= $tconfig["tsite_url"] ?>booking/action_booking.php');
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
                                        var sess_iCompanyId = '<?= $sess_iCompanyId; ?>';
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
                                            url: "<?= $tconfig["tsite_url"] ?>booking/get_available_driver_list.php",
                                            dataType: "html",
                                            data: {vCountry: vCountry, keyword: keyword, iVehicleTypeId: vType, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, eChildSeat: eChildSeat, eWheelChair: eWheelChair, AppeType: eType, sess_iCompanyId: sess_iCompanyId},
                                            success: function (dataHtml2) {
                                                $('#driver_main_list').show();
                                                if (dataHtml2 != "") {
                                                    $('#driver_main_list').html(dataHtml2);
                                                } else {
                                                    $('#driver_main_list').html('<h4 style="margin:25px 0 0 15px">Sorry , No <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> Found.</h4>');
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
                                            //alert(referrer);
                                        } else {
                                            referrer = $("#previousLink").val();
                                        }
                                        if (referrer == "") {
                                            referrer = "<?= $tconfig["tsite_url"] ?>booking/cab_booking.php";
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
                                                url: '<?= $tconfig["tsite_url"] ?>booking/checkForRestriction.php',
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

                                    function checkrestrictionto(type) {
                                        if (($("#from").val() != "") || ($("#to").val() != "")) {
                                            $.ajax({
                                                type: "POST",
                                                url: '<?= $tconfig["tsite_url"] ?>booking/checkForRestriction.php',
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
                                        /*if(eType != 'UberX'){
                                         if(from_lat_long == ''){
                                         alert("Please Select Proper Address.");
                                         $( "#from" ).focus();
                                         return false;
                                         } else if ( to_lat_long == '') {
                                         alert("Please Select Proper Address.");
                                         $( "#to" ).focus();
                                         return false;
                                         } 
                                         } else {
                                         if(from_lat_long == ''){
                                         alert("Please Select Proper Address.");
                                         $( "#from" ).focus();
                                         return false;
                                         } 
                                         }*/
                                        var isvalidate = $("#add_booking_form")[0].checkValidity();
                                        if (eType == 'Ride') {
                                            var idClicked = $("input[name='eRideType']:checked").val();
                                        } else if (eType == 'Deliver') {
                                            var idClicked = $("input[name='eDeliveryType']:checked").val();
                                        } else if (eType == 'UberX') {
                                            var idClicked = 'later';
                                        }
                                        
                                        if (isvalidate) { 
                                            event.preventDefault();
                                            var country = $('select[name=vCountry]').val();
                                            var eTollEnabled = '';
                                            if (country != "") {
                                                $.ajax({
                                                    type: "POST",
                                                    url: '<?= $tconfig["tsite_url"] ?>booking/ajax_check_toll.php',
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
                                            //	$('#submitbutton').prop('disabled', true);
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
                                                                alert("Toll API Response: " + jqXHR.responseJSON.message);
                                                                $(".loader-default").hide();
                                                                //$("#add_booking_form").submit();
                                                                SubmitFormCheck(idClicked);
                                                            });

                                                        }
                                                    } else {
                                                        //$("#add_booking_form").submit();
                                                        SubmitFormCheck(idClicked);
                                                        return true;
                                                    }
                                                } else {
                                                    //$("#add_booking_form").submit();
                                                    SubmitFormCheck(idClicked);
                                                    return true;
                                                }
                                            } else {
                                                //$("#add_booking_form").submit();
                                                SubmitFormCheck(idClicked);
                                                return true;
                                            }
                                        }
                                    });
</script>

<?php require_once("booking/functions.php"); ?>
<div class="modal fade" id="fareEstimateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content booking-passenger-fare-estimate">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_FARE_ESTIMATE_TXT']; ?></h4>
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
    <p><?php echo $langage_lbl['LBL_TOLL_PRICE_DESC']; ?></p>
    <form class="form" role="form" id="formtoll">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="eTollSkipped1" id="eTollSkipped1" value="Yes" <?php if ($eTollSkipped == 'Yes') echo 'checked'; ?>/> <?php echo $langage_lbl['LBL_IGNORE_TOLL_ROUTE_WEB']; ?>
            </label>
        </div>
    </form>
    <p style="text-align: center;font-weight: bold;">
        <span><?php echo $langage_lbl['LBL_Total_Fare']; ?> <?php echo $generalobj->symbol_currency(); ?><b id="totalcost">0</b></span>+
        <span><?php echo $langage_lbl['LBL_TOLL_TXT']; ?> <b id="tollcost">0</b></span>
    </p>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_DIS_HOW_IT_WORKS']; ?></h4>
            </div>
            <div class="modal-body">
                <p><b>Flow </b>: Through "Manual Taxi Dispatch" Feature, you can book Rides for customers who ordered for a Ride by calling you. There will be customers who may not have iPhone or Android Phone or may not have app installed on their phone. In this case, they will call Taxi Company (your company) and order Ride which may be needed immediately or after some time later.</p>
                <p>- Here, you will fill their info in the form and dispatch a taxi for him.</p>
                <p>- If the customer is already registered with us, just enter his phone number and his info will be fetched from the database when "Get Details" button is clicked. Else fill the form.</p>
                <p>- Once the Trip detail is added, Fare estimate will be calculated based on Pick-Up Location, Drop-Off Location and Car Type.</p>
                <p>- Admin will need to communicate & confirm with Driver and then select him as Driver so the Ride can be allotted to him. </p>
                <p>- Clicking on "Book" Button, the Booking detail will be saved and will take Administrator to the "Ride Later Booking" Section. This page will show all such bookings.</p>
                <p>- Both Driver and Rider will receive the booking details through Email and SMS as soon as the form is submitted. Based on this booking details, Driver will pickup the rider at the scheduled time.</p>
                <p>- They both will get the reminder SMS and Email as well before 30 minutes of actual trip</p>
                <p>- The assigned Driver can see the upcoming Bookings from his App under "My Bookings" section.</p>
                <p>- Driver will have option to "Start Trip" when he reaches the Pickup Location at scheduled time or "Cancel Trip" if he cannot take the ride for some reason. If the Driver clicks on "Cancel Trip", a notification will be sent to Administrator so he can make alternate arrangements.</p>
                <p>- Upon clicking on "Start Trip", the ride will start in driver's App in regular way.</p>
                <p>&nbsp;</p>
                <p><b>Auto Assign Driver </b>: The "Ride Later" booking made from the mobile application has the "Driver Auto Assign" by default enabled. Furthermore Admin can also select this option while adding the booking manually from admin panel</p>
                <p>- Driver auto assignment process works as explained below</p>
                <p>- System will automatically sends the request to drivers who are online and available within pickup location radius</p>
                <p>- Driver(s) will get the 30 seconds dial screen request  before 8-12 minutes before the actual pickup time. This request is same like "Request Now" one.</p>
                <p>- If no driver(s) accepts the request then system will make a 2nd try after 4 minutes and sends the request again . At this point system also notifies the admin through email that no any drivers had accepted the request in first try.</p>
                <p>- Again If no driver(s) accepts the request then system will make a 3rd and last try after 4 minutes and send the request again. At this point system also notifies admin through email that no any drivers had accepted the request in 2nd try.</p>
                <p>- System makes the 3 trials and if in any trial, no drivers availabe in that area then it will inform administrator about unavailability of driver so administrator takes necessary action to contact that rider and arrange the taxi for him</p>
                <!--	<p><span><img src="images/mobile_app_booking.png"></img></span></p>-->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $langage_lbl['LBL_CLOSE_TXT']; ?></button>
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
                <h4 class="modal-title" id="myModalLabel"> <?php echo $langage_lbl['LBL_DIS_HOW_IT_WORKS']; ?></h4>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $langage_lbl['LBL_CLOSE_TXT']; ?></button>
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
                <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_DIS_HOW_IT_WORKS']; ?></h4>
            </div>
            <div class="modal-body">
                <p style="font-size: 15px;"><?php echo $langage_lbl['LBL_MANUAL_ALERT_MESSAGE_TIME_WEB']; ?> </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-success" data-dismiss="modal"><?php echo $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="driverData" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">x</span>
                </button> -->
                <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_DIS_HOW_IT_WORKS']; ?></h4>
            </div>
            <div class="modal-body">
                <div id="driver-bottom-set001">	
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnYes" class="btn btn-secondary btn-success" data-dismiss="modal"><?php echo $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
            </div>
        </div>
    </div>
</div>
<div class="booking-confirmation-popup hide001" id="request-loader001" >
    <div class="requesting-popup-old">
        <span id="requ_title" class="req-001"><b><?php  ucfirst(strtolower($langage_lbl['LBL_REQUESTING_TXT'])); ?> ....</b>
            <span class="requesting-popup-sub" style="padding-right: 5px;float: right;"><a href="javascript:void(0);" id="cancelBtn" onclick="cancellingRequestDriver();"><?php echo $langage_lbl['LBL_CANCEL_TXT']; ?></a></span></span>
    </div>
    <div class="requesting-popup hide001" id="req_try_again">
        <p><?php echo $langage_lbl['LBL_NO_DRIVER_AVALIABLE_TO_ACCEPT_WEB']; ?></p>
        <span><a href="javascript:void(0);" id="retryBtn"><?php echo $langage_lbl['LBL_RETRY_TXT']; ?></a></span>
        <span><a href="javascript:void(0);" id="cancelBtn" onclick="cancellingRequestDriver();"><?php echo $langage_lbl['LBL_CANCEL_TXT']; ?></a></span>
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
