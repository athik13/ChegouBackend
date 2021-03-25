<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
include_once(ROOT_PATH . "common.php");
include_once (ROOT_PATH . 'app_common_functions.php');
$tsiteUrl = $tconfig['tsite_url'];
$tsiteAdminUrl = $tconfig['tsite_url_main_admin'];
$pageName = 'sign-in';
$redirect = 0;
if (strtoupper($PACKAGE_TYPE) != "SHARK") {
    $redirect = 1;
}

$rideTypeEnabled = $deliveryTypeEnabled = $motoRideEnabled = $flyEnabled = "No";
$rideModuleAvailable = isRideModuleAvailable();
$deliveryModuleAvailable = isDeliveryModuleAvailable();
$flyModuleAvailable = checkFlyStationsModule();

if($rideModuleAvailable==1) {
    $rideData = $obj->MySQLSelect("SELECT * FROM `vehicle_category` WHERE `iParentId` = 0 AND `eStatus` = 'Active' AND `eCatType` = 'Ride'");
    if(count($rideData) > 0){
        $rideTypeEnabled = "Yes";
    }
    
    $motorideData = $obj->MySQLSelect("SELECT * FROM `vehicle_category` WHERE `iParentId` = 0 AND `eStatus` = 'Active' AND `eCatType` = 'MotoRide'");
    if(count($motorideData) > 0){
        $motoRideEnabled = "Yes";
    }
}

if($deliveryModuleAvailable==1) {
    $deliveryData = $obj->MySQLSelect("SELECT * FROM `vehicle_category` WHERE `iParentId` = 0 AND `eStatus` = 'Active' AND `eCatType` = 'MoreDelivery' AND eFor='DeliveryCategory'");
    if(count($deliveryData) > 0){
        $deliveryTypeEnabled = "Yes";
    }
}

if($flyModuleAvailable==1) {
    $flyData = $obj->MySQLSelect("SELECT * FROM `vehicle_category` WHERE `iParentId` = 0 AND `eStatus` = 'Active' AND `eCatType` = 'Fly'");
    if(count($flyData) > 0){
        $flyEnabled = "Yes";
    }
}

$userType1 = isset($_REQUEST['userType1']) ? $_REQUEST['userType1'] : '';
$navigatedPage = isset($_REQUEST['navigatedPage']) ? $_REQUEST['navigatedPage'] : '';
if ($userType1 != 'admin') {
    if ((isset($_SESSION['postDetail']) && !empty($_SESSION['postDetail']) && $_SESSION['postDetail']['user_type'] == 'company' && $_SESSION['sess_eSystem'] == 'General')) {
        if ($userType1 == 'rider') {
            if(!empty($navigatedPage)) $_SESSION['navigatedPage'] = $navigatedPage;
            $redirect = 1;
            $pageName = 'companybooking';
        }
    } elseif ((isset($_SESSION['sess_user']) && !empty($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider')) {
        if ($userType1 == 'company') {
            if(!empty($navigatedPage)) $_SESSION['navigatedPage'] = $navigatedPage;
                $redirect = 1;
                $pageName = 'userbooking';
        }
    } else {
        $redirect = 1;
    }
}
if ($redirect > 0) {
    header('Location:' . $tsiteUrl . $pageName);
    exit();
}
if ($userType1 == 'rider') {
    $data1 = $generalobj->getUserCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'], 0);
} else if ($userType1 == 'company') {
    $data1 = $generalobj->getCompanyCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'], 0);
} else if ($userType1 == 'admin') {
    if ($default_lang == "") {
        $default_lang = "EN";
    }
}

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
//$vPhoneCode = $generalobj->clearPhone($db_con[0]['vPhoneCode']);
$vPhoneCode = $db_con[0]['vPhoneCode'];
$vRideCountry = isset($_REQUEST['vRideCountry']) ? $_REQUEST['vRideCountry'] : $db_con[0]['vCountryCode'];
$vTimeZone = isset($_REQUEST['vTimeZone']) ? $_REQUEST['vTimeZone'] : $db_con[0]['vTimeZone'];
$vCountry = $db_con[0]['vCountryCode'];

$address = $db_con[0]['vCountry'];
// Google HQ
/* $prepAddr = str_replace(' ','+',$address);
  $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
  $output= json_decode($geocode);
  $latitude = $output->results[0]->geometry->location->lat;
  $longitude = $output->results[0]->geometry->location->lng; */

$dBooking_date = $vPhoneOrg = "";

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
                
            $vPhoneOrg = $value['vPhone'];
            //$vPhone = $generalobj->clearPhone($value['vPhone']);
            $vPhone = $value['vPhone'];
            $vName = $value['vName'];
            //$vLastName = $generalobj->clearName(" " . $value['vLastName']);
            $vLastName = $value['vLastName'];
            //$vEmail = $generalobj->clearEmail($value['vEmail']);
            $vEmail = $value['vEmail'];
            //$vPhoneCode = $generalobj->clearPhone($value['vPhoneCode']);
            $vPhoneCode = $value['vPhoneCode'];
            //$vCountry = $value['vCountry'];
            $vCountry = !empty($value['vRideCountry']) ? $value['vRideCountry'] : $value['vCountry'];
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
            $iToStationId= $value['iToStationId'];
            $iFromStationId= $value['iFromStationId'];
            
            //if ( (!empty($iFromStationId) && !empty($iToStationId)) && $etype == "Ride" ) {
            //    $etype = 'Fly';
            //    $navigatedPage = 'Fly';
            //}
    
            if ($etype == 'Ride') {
                $eRideType = 'later';
            }
            if ($etype == 'Deliver') {
                $eDeliveryType = 'later';
            }
            $vCouponCode = $value['vCouponCode'];
            
            $sql_type = "SELECT eIconType FROM `vehicle_type` WHERE iVehicleTypeId = $iVehicleTypeId LIMIT 0,1";
            $db_vehicleicondata = $obj->MySQLSelect($sql_type);
            if($etype == 'Ride' && ($db_vehicleicondata[0]['eIconType']=='Bike' || $db_vehicleicondata[0]['eIconType']=='Cycle')) {
                $etype = 'Moto';
            }
        }
    }
}

if ($_SESSION['sess_user'] == 'rider' && $userType1 == 'rider') {
    $sess_iUserId = $_SESSION['sess_iUserId'];
    $rsql = "SELECT * FROM  `register_user` WHERE iUserId='" . $sess_iUserId . "'";
    $db_userdata = $obj->MySQLSelect($rsql);
    $vCountry = $db_userdata[0]['vCountry'];
    //$vPhone = $generalobj->clearPhone($db_userdata[0]['vPhone']);
    $vPhoneOrg = $db_userdata[0]['vPhone'];
    $vPhone = $db_userdata[0]['vPhone'];
    $vName = $db_userdata[0]['vName'];
    $vLastName = $db_userdata[0]['vLastName'];
    //$vEmail = $generalobj->clearEmail($db_userdata[0]['vEmail']);
    $vEmail = $db_userdata[0]['vEmail'];
    //$vPhoneCode = $generalobj->clearPhone($db_userdata[0]['vPhoneCode']);
    $vPhoneCode = $db_userdata[0]['vPhoneCode'];
    $eAutoAssign = 'Yes';
    $eBookingFrom = 'User';

    $eGender = $db_userdata[0]['eGender'];
}

if ($_SESSION['sess_user'] == 'company' && $userType1 == 'company') {
    $sess_iCompanyId = $_SESSION['sess_iCompanyId'];
    $eBookingFrom = 'Company';
    $rsql = "SELECT tSessionId,iUserId FROM  `register_user` WHERE tSessionId != '' LIMIT 0,1";
    $db_userdata = $obj->MySQLSelect($rsql);
}

if ($_SESSION['SessionUserType'] == 'hotel') {
    $eBookingFrom = 'Hotel';
    $iAdminId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
    $iHotelBookingId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
    $iGroupId = isset($_SESSION['sess_iGroupId']) ? $_SESSION['sess_iGroupId'] : '';

    $sql = "SELECT c.vCountryCode,c.vCountryCode,c.vCountry,c.vTimeZone,c.vPhoneCode,a.vAddress,a.vAddressLat,a.vAddressLong FROM administrators as a LEFT JOIN country as c on c.vCountryCode=a.vCountry WHERE c.eStatus = 'Active' AND a.iGroupId = '" . $iGroupId . "' AND a.iAdminId = '" . $iAdminId . "'";
    $db_hoteldata = $obj->MySQLSelect($sql);
    //$vPhoneCode = $generalobjAdmin->clearPhone($db_code[0]['vPhoneCode']);
    $vPhoneCode = $db_hoteldata[0]['vPhoneCode'];
    $vRideCountry = isset($_REQUEST['vRideCountry']) ? $_REQUEST['vRideCountry'] : $db_hoteldata[0]['vCountryCode'];
    $vTimeZone = isset($_REQUEST['vTimeZone']) ? $_REQUEST['vTimeZone'] : $db_hoteldata[0]['vTimeZone'];
    $vCountry = $db_hoteldata[0]['vCountryCode'];
    $address = $db_hoteldata[0]['vCountry']; // Google HQ
    $vSourceAddresss = $db_hoteldata[0]['vAddress'];
    $from_lat_long = '(' . $db_hoteldata[0]['vAddressLat'] . ', ' . $db_hoteldata[0]['vAddressLong'] . ')';
    $from_lat = $db_hoteldata[0]['vAddressLat'];
    $from_long = $db_hoteldata[0]['vAddressLong'];
}
if ($eBookingFrom == '') {
    $eBookingFrom = 'Admin';
}
//Ride Vehicle data
$sql = "SELECT iVehicleTypeId,vVehicleType_" . $_SESSION['sess_lang'] . " AS vVehicleType,vLogo,vLogo1 FROM vehicle_type WHERE eType = 'Ride' AND eStatus = 'Active' ORDER BY iVehicleTypeId ASC";
$db_ride_vehicles = $obj->MySQLSelect($sql);

//Delivery Vehicle data
$sql = "SELECT iVehicleTypeId,vVehicleType_" . $_SESSION['sess_lang'] . " AS vVehicleType,vLogo,vLogo1 FROM vehicle_type WHERE eType = 'Deliver' AND eStatus = 'Active' ORDER BY iVehicleTypeId ASC";
$db_delivery_vehicles = $obj->MySQLSelect($sql);
$driversubs = 0;
if ($DRIVER_SUBSCRIPTION_ENABLE == 'Yes') {
    $driversubs = 1;
}

if (!empty($_REQUEST["navigatedPage"])) {
    $navigatedPage = $_REQUEST["navigatedPage"];
} else if (!empty($_SESSION["navigatedPage"])) {
    $navigatedPage = $_SESSION["navigatedPage"];
} else {
    $navigatedPage = 'Ride';
}
//unset($_SESSION["navigatedPage"]);

if ($generalobj->checkXThemOn() == 'Yes') {
    $cubexthemeon = 'Yes';
} else {
    $cubexthemeon = 'No';
}

$ufxEnable = 'No';
if (($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') && $generalobj->CheckUfxServiceAvailable() == 'Yes') {
    $ufxEnable = 'Yes';
}
//echo $iVehicleTypeId;exit;
//echo $etype;exit;

if ($action == 'Edit') {
    $navigatedPage = $etype;
    // added for delivery edit
    if($etype == "Deliver") {
        $navigatedPage = "Delivery";
    }
    if ((!empty($iFromStationId) && !empty($iToStationId)) && $etype == "Ride") {
        $etype = 'Fly';
        $navigatedPage = 'Fly';
    }
}

?>
<style>
.asset-map-image-marker { margin-left: -30px }
/* Arrow on bottom of container */
.asset-map-image-marker:after {}

/* Inner image container */
.asset-map-image-marker div.image {
	background-position: center center;
    background-size: cover;
    height: 53px;
    width: 53px;
    position: absolute;
    border-radius: 50%;
    top: 5px;
    left: 50%;
    margin-left: -27px;
}
.outer-image {
    width: 64px;
    height: 90px;
    background-image: url("<?= $tsiteUrl; ?>webimages/upload/mapmarker/UberX/marker.png");
    background-size: 100%;
    position: absolute;
    background-repeat: no-repeat;
}
    .divclearable {
        /*border: 1px solid #888;*/
        display: -moz-inline-stack;
        display: inline-block;
        zoom:1;
        *display:inline;	
        padding-right:5px;
        vertical-align:middle;
    }
    a.clearlink {
        background: url("<?= $tsiteUrl; ?>assets/img/cancle-red-new.png") no-repeat scroll 0 0 transparent;
        background-position: center center;
        cursor: pointer;
        display: -moz-inline-stack;
        display: none;
        zoom:1;
        /* *display:inline;	
         height: 12px;
         width: 12px;*/
        z-index: 2000;
        border: 0px solid;
        position: absolute;
        right: 0;
        bottom: 0;
        opacity: 0;
        width: 38px;
        height:38px;

    }
    #map-canvas {
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        position: absolute;
        /* top: 0; */
        /* left: 0; */
        /* right: 0; */
        /* bottom: 0; */
        /* pointer-events: none; */
        width: 100%;
        height: 100%;
        top: 0;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .pickup-schedule-img {
        position: absolute;
        right: 6px;
        bottom: 4px;
        cursor: pointer;
    }
    .bootbox.modal.fade
    {
        overflow-y: auto;
        box-shadow: none;
        border: none;
    }
    .bootbox.modal.fade, .bootbox .modal-dialog{
        background-color:transparent;
    }
    .modal-backdrop, .modal-backdrop.fade.in{
        display: block;
    }
</style>
<script>
    $(document).ready(function () {
		 $(".loader-default").show(); //loaded show at the page load in cx-add_booking.php..and after all jquery is load loader hide in the initialize function 
        $('.clearable').clearable();
<?php if ($APP_TYPE != 'Ride' || $APP_TYPE != 'Delivery' || $APP_TYPE != 'Ride-Delivery' || $APP_TYPE != 'Ride-Delivery-UberX') { ?>
            $('.service-pickup-type').hide();
            $(".dateSchedule").show();
            $('#datetimepicker4').attr('required', 'required');
<?php } ?>


    });
    jQuery.fn.clearable = function () {
        return this.each(function () {
            $(this).css({'border-width': '0px', 'outline': 'none'})
                    .wrap('<div id="sq" class="divclearable"></div>')
                    .parent()
                    .attr('class', $(this).attr('class') + ' divclearable')
                    .append('<a class="clearlink clearpromolink" href="javascript:"></a>');

            $('.clearlink')
                    .attr('title', 'Click to clear this textbox')
                    .click(function () {
                        $(this).prev().val('').focus();
                        alert("<?= $langage_lbl['LBL_PROMO_REMOVED'] ?>");
                        $("#promocode").val('');
                        $("#promocode").removeAttr('readonly');
                        if (eType == 'Ride') {
                            //var vehicleTypeId = $('input[name=iDriverVehicleId_ride]:checked').val();
                            var vehicleTypeId = $('input[name=iVehicleTypeId]:checked').val();
                        } else if (eType == 'Deliver') {
                            //var vehicleTypeId = $('input[name=iDriverVehicleId_delivery]:checked').val();
                            var vehicleTypeId = $('input[name=iVehicleTypeId]:checked').val();
                        } else {
                            //var vehicleTypeId = $('#iVehicleTypeId').find(":selected").val();
                            var vehicleTypeId = $('#iVehicleTypeId').find(":checked").val();
                        }
                        var bookingfrom = '<?php echo $eBookingFrom; ?>';
                        if (bookingfrom == 'Company') {
                            //var vehicleTypeId = $('#iVehicleTypeId').find(":selected").val();
                            var vehicleTypeId = $('#iVehicleTypeId').find(":checked").val();
                        }
                        showAsVehicleType(vehicleTypeId);
                        showVehicleCountryVise($('#vCountry option:selected').val(), vehicleTypeId, eType);

                        //$(".discount-block button").toggleClass('icon-apply icon-close');
                        $(".discount-block button").addClass('icon-apply');
                        $(".discount-block button").removeClass('icon-close');
                        $(".clearlink").hide();
                        $("#promocodeapplied").val('');
                    });
        });
    }
</script>


            <section class="booking-request">
                <form name="add_booking_form" id="add_booking_form" method="post" action="<?= $tsiteUrl; ?>booking/action_booking.php" >
                    <div class="form-group" style="display: inline-block; width:100%;">
                        <?php if ($success == "1") { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php
                                echo ($vassign != "1") ? $langage_lbl['LBL_DRIVER_TXT_ADMIN'] : $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . ' ' . $langage_lbl['LBL_MANUAL_BOOKING_DRIVER_TXT_ADMIN_ADDED_SUCESSFULLY'];
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
                        <input type="hidden" name="iFromStationId" id="iFromStationId" value="<?= $iFromStationId?>">
                        <input type="hidden" name="iToStationId" id="iToStationId" value="<?= $iToStationId?>">
                        <input type="hidden" name="ajax_vEmail" id="ajax_vEmail" value="<?= $_SESSION['ajax_vEmail'] ?>">
                        <?php if ($APP_TYPE != 'Ride-Delivery' && $APP_TYPE != 'Ride-Delivery-UberX' || ($APP_TYPE == 'Ride-Delivery' && $APP_DELIVERY_MODE == "Multi")) { ?>
                            <input type="hidden" value="<?= $etype ?>" id="eType" name="eType" />
                        <?php } ?>
                        <input type="hidden" value="<?= $eGender ?>" id="eGender" name="eGender" />
                        <input type="hidden" name="iAdminId" id="iAdminId" value="<?= $iAdminId; ?>" />
                        <input type="hidden" name="iHotelBookingId" id="iHotelBookingId" value="<?= $iHotelBookingId; ?>" />
                        <input type="hidden" name="userType1" value="<?= $userType1; ?>">
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
                                    <div id="map-canvas" class="google-map"></div>
                                </div>
                            </div>
                        </div>
                        <!--<div id="map-canvas" class="google-map"></div>-->
                        <div class="booking-request-inner">
                            <div class="booking-block">
                                <div class="vehicle-details-popup" id="vehicle-popup">
                                    <span class="close-icon">&#10005;</span>
                                    <div class="vehicle-caption">
                                        <i><img src="" alt="" id="vehicleImage" width="60" height="60"></i>
                                        <div class="car-identy">
                                            <strong id="vehicleName"></strong>
                                            <strong id="total_fare_price">0</strong>
                                        </div>
                                    </div>
                                    <?
                                    if ($APP_TYPE != 'UberX') {
                                        //if ($userType1 == 'company') {
                                        //    $class = 'total-price total-price1 new';
                                        //} else {
                                        //    $class = 'total-price total-price1';
                                        //}
                                        ?>
                                        <!-- popup -->
                                        <div class="map-popup" style="display:none" id="driver_popup"></div>
                                        <!-- popup end -->
                                        <div class="vehicle-data">
                                            <ul class="costlist" id="estimatedata">
                                                No Data Available
                                            </ul>
                                            <div class="ride-desribe"><p id="faretxt"></p></div>
                                            <?
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="vehicle-details-popup" id="stations-popup">
                                    <!--<span class="close-icon">&#10005;</span>-->
                                    <div class="vehicle-caption">
                                        <div class="car-identy">
                                            <strong>Stations</strong>
                                        </div>
                                    </div>
                                    <div class="vehicle-data">
                                        <ul class="costlist radio-list" id="stationdata">

                                        </ul>
                                    </div>
                                </div>


                                <div>
                                    <?php 
                                        $pickup_display = "";
                                        if($iHotelBookingId != "")
                                            {
                                                $pickup_display = 'style="display:none"';
                                            }
                                        ?>
                                    <div class="booking-header">
                                        <div class="booking-heading" <?php if($pickup_display != "") echo 'style="margin-bottom:0"' ?>>
                                            <?php if ($APP_TYPE == 'Ride') { ?>
                                                <input class="add-booking" id="r1" name="eType" type="hidden" value="Ride">
                                            <?php } else if ($APP_TYPE == 'Delivery') { ?>
                                                <input class="add-booking" id="r1" name="eType" type="hidden" value="Deliver">
                                            <?php } else if ($APP_TYPE == 'UberX') { ?>
                                                <input class="add-booking" id="r1" name="eType" type="hidden" value="UberX">
                                            <?php } else { ?>
                                                <label <?php echo $pickup_display ?>><?= $langage_lbl['LBL_SELECT_JOB_TYPE']; ?></label>
                                                <div class="radio-but" style="display:none">
                                                    <div class="add-booking-radiobut radio-inline">
                                                        <input class="add-booking" id="r1" name="eType" type="radio" value="Ride" <?php
                                                        if ($etype == 'Ride') {
                                                            echo 'checked';
                                                        }
                                                        ?> onChange="hide_locations_for_others(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);" checked="checked">
                                                        <label for="r1"><?php echo $langage_lbl['LBL_TAXI_RIDE']; ?></label>
                                                    </div>
                                                    <div class="add-booking-radiobut radio-inline">
                                                        <input id="r2" name="eType" type="radio" value="Deliver" <?php
                                                        if ($etype == 'Deliver') {
                                                            echo 'checked';
                                                        }
                                                        ?> onChange="hide_locations_for_others(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);">
                                                        <label for="r2"><?php echo $langage_lbl['LBL_DELIVERY']; ?></label>
                                                    </div>
                                                    <div class="add-booking-radiobut radio-inline">
                                                        <input id="r4" name="eType" type="radio" value="Fly" <?php
                                                        if ($etype == 'Fly') {
                                                            echo 'checked';
                                                        }
                                                        ?> onChange="hide_location_for_fly(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);">
                                                        <label for="r4"><?php echo $langage_lbl['LBL_HEADER_RDU_FLY_RIDE']; ?></label>
                                                    </div>
                                                    <div class="add-booking-radiobut radio-inline">
                                                        <input id="r5" name="eType" type="radio" value="Moto" <?php
                                                        if ($etype == 'Moto') {
                                                            echo 'checked';
                                                        }
                                                        ?> onChange="hide_locations_for_others(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);">
                                                        <label for="r5"><?php echo $langage_lbl['LBL_HEADER_RDU_MOTO_RIDE']; ?></label>
                                                    </div>
                                                    <? if ($ufxEnable == 'Yes') { ?>
                                                        <div class="add-booking-radiobut radio-inline other-service">
                                                            <input class="add-booking" id="r3" name="eType" type="radio" value="UberX" <?php
                                                            if ($etype == 'UberX') {
                                                                echo 'checked';
                                                            }
                                                            ?> onChange="hide_locations_for_others(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);">
                                                            <label for="r3"><?php echo $langage_lbl['LBL_OTHER']; ?></label>
                                                        </div>
                                                    <? } ?>
                                                </div>

                                                <select name="eType_design" id="eType_design" <?php echo $pickup_display; ?>>
                                                <? if($rideTypeEnabled=="Yes") { ?>
                                                    <option value="ride" <?php
                                                    if ($navigatedPage == 'Ride') {
                                                        echo 'selected';
                                                    }
                                                    ?> onChange="hide_locations_for_others(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);" selected="selected"><?php echo $langage_lbl['LBL_TAXI_RIDE']; ?></option>
                                                    
                                                <?php } if($_SESSION['SessionUserType'] != 'hotel') { ?>
                                                <? if($deliveryTypeEnabled=="Yes") { ?>
                                                    <option value="delivery" <?php
                                                    if ($navigatedPage == 'Delivery') {
                                                        echo 'selected';
                                                    }
                                                    ?> onChange="hide_locations_for_others(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);"><?php echo $langage_lbl['LBL_DELIVERY']; ?></option>
                                                    <? } if ($ufxEnable == 'Yes') { ?>
                                                        <option value="UberX" <?php
                                                        if ($navigatedPage == 'UberX') {
                                                            echo 'selected';
                                                        }
                                                        ?> onChange="hide_locations_for_others(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);"><?php echo $langage_lbl['LBL_OTHER']; ?></option>
                                                    <?php } if ($flyEnabled=="Yes") { ?>
                                                        <option value="Fly" <?php
                                                        if ($navigatedPage == 'Fly') {
                                                            echo 'selected';
                                                        }
                                                        ?> onChange="hide_location_for_fly(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);"><?php echo $langage_lbl['LBL_HEADER_RDU_FLY_RIDE']; ?></option>
                                                    <?php } if($motoRideEnabled=="Yes") { ?>
                                                    <option value="Moto" <?php
                                                    if ($navigatedPage == 'Moto') {
                                                        echo 'selected';
                                                    }
                                                    ?> onChange="hide_locations_for_others(),setDriverListing(), show_type(this.value), showVehicleCountryVise($('#vCountry option:selected').val(), '<?php echo $iVehicleTypeId; ?>', this.value);"><?php echo $langage_lbl['LBL_HEADER_RDU_MOTO_RIDE']; ?></option>

                                                    <?php } } ?> 

                                                </select>
<?php } ?>
                                        </div>
                                        <div class="pick-drop-location">
                                            
                                            <div class="form-group pickup" <?php echo $pickup_display;?>>
                                                <input type="text"  name="vSourceAddresss"  id="from" value="<?= $vSourceAddresss; ?>" placeholder="<?= ucfirst(strtolower($langage_lbl['LBL_PICKUP_LOCATION_HEADER_TXT'])); ?>" required onpaste="checkrestrictionfrom('from');setDriverListing();" <?php if ($_SESSION['SessionUserType'] == 'hotel') { ?>readonly="readonly" style="pointer-events:none;"<?php } ?>>
                                            </div>
<? if ($APP_TYPE != "UberX") { ?>
                                                <div class="form-group dest">
                                                    <input type="text" name="tDestAddress"  id="to" value="<?= $tDestAddress; ?>" placeholder="<?= ucfirst(strtolower($langage_lbl['LBL_DROP_OFF_LOCATION_TXT'])); ?>" required onpaste="checkrestrictionto('to');">
                                                </div>
<? } ?>
<?php if ($_SESSION['SessionUserType'] == 'hotel') { ?>
                                                <div class="form-group">
                                                    <input type="text" name="vRiderRoomNubmer" id="vRiderRoomNubmer" value="" placeholder="Room Number" required="">
                                                </div>
<?php } ?>
                                        </div>

                                    </div>


                                    <div class="booking-main">
                                        <ul class="stepper linear">

                                            <li class="step" id="stationdropdown_li" data-step="0"> 
                                                <div data-step-label="There's labels too!" class="step-title waves-effect waves-dark"><?php echo $langage_lbl['LBL_FLY_STATIONS']; ?></div>
                                                <div class="step-content">
                                                    <div class="section-block">
                                                        <div class="general-form">
                                                            <div class="form-group" id="stationdropdownfrom"><div class="vehicle-data"><p id="faretxt"><?php echo $langage_lbl['LBL_NO_FLY_STATIONS']; ?></p></div></div>
                                                        </div>
                                                    </div>
                                                    <div class="step-actions">
                                                        <button class="waves-effect waves-dark btn blue next-step" id="btn_fly_stations"><?php echo $langage_lbl['LBL_CHOOSE_CONTACT_CONT_TXT']; ?></button>
                                                    </div>
                                                </div>
                                            </li>
                                            <?php
                                                $li_display = ""; 
                                                if ($userType1 == 'rider') {
                                                    $li_display = 'style="display:none"';
                                                }
                                            ?>
                                            <li class="step" <?php echo $li_display ?> >
                                                <div data-step-label="There's labels too!" class="step-title waves-effect waves-dark" id="user_details_label"><?php echo $langage_lbl['LBL_RIDER_DETAILS']; ?></div>
                                                <div class="step-content">

                                                    <div class="section-block" id="user_details">
                                                        <div class="general-form">
                                                            <div class="form-group">
                                                                <select name="vCountry" id="vCountry" onChange="changeCode(this.value, '<?php echo $iVehicleTypeId; ?>');" required> <!--setDriverListing();setDriversMarkers();-->
                                                                    <? for ($i = 0; $i < count($db_code); $i++) { ?>
                                                                        <option value="<?= $db_code[$i]['vCountryCode'] ?>"
                                                                        <?php
                                                                        if ($db_code[$i]['vCountryCode'] == $vCountry) {
                                                                            echo "selected";
                                                                        }
                                                                        ?> >
                                                                        <?= $db_code[$i]['vCountry']; ?>
                                                                        </option>
                                                                        <? } ?>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="form-group phone-column ">
                                                                <label><?php echo $langage_lbl['LBL_ENTER_PHONE_NO_WEB']; ?></label>
                                                                <input type="hidden" value="<?= $vPhoneOrg; ?>" id="vPhoneOrg">
                                                                <input type="text" class="phonecode" name="vPhoneCode" id="vPhoneCode" value="<?= $vPhoneCode; ?>" readonly />
                                                                <input type="tel" pattern="[0-9]{1,}" title="Enter Mobile Number." name="vPhone"  id="vPhone" value="<?= $vPhone; ?>" onkeyup="return isNumberKey(event)"  onblur="return isNumberKey(event)"  required  />
                                                            </div>
                                                            <div class="form-group rederdetail">
                                                                <label><?php echo $langage_lbl['LBL_YOUR_FIRST_NAME']; ?></label>
                                                                <input type="text" name="vName"  id="vName" value="<?= $vName; ?>" required />
                                                            </div>
                                                            <div class="form-group rederdetail">
                                                                <label><?php echo $langage_lbl['LBL_YOUR_LAST_NAME']; ?></label>
                                                                <input type="text" name="vLastName"  id="vLastName" value="<?= $vLastName; ?>" required />
                                                            </div>
                                                            <div class="form-group rederdetail">
                                                                <label><?php echo $langage_lbl['LBL_EMAIL_TEXT']; ?></label>
                                                                <input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" name="vEmail" id="vEmail" value="<?= $vEmail; ?>" required >
                                                                <div id="emailCheck"></div>
                                                            </div>
                                                        </div>
                                                    </div>



                                                    <?php if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>    
                                                        <div class="section-block rider_hide" id="ride-delivery-type" style="display:none; margin-top:20px;">
                                                            <label><?php echo $langage_lbl['LBL_DELIVERY_OPTIONS_WEB']; ?></label>
                                                            <div class="general-form">
                                                                <div class="form-group">
                                                                    <select name="iPackageTypeId"  id="iPackageTypeId">
                                                                        <option value=""><?php echo $langage_lbl['LBL_SELECT_PACKAGE_TYPE']; ?></option>
                                                                        <? foreach ($db_PackageType as $val) { ?>
                                                                            <option value="<?= $val['iPackageTypeId'] ?>" <? if ($val['iPackageTypeId'] == $iPackageTypeId && $action == "Edit") { ?>selected<? } ?>><?= $val['vName']; ?></option>
                                                                        <? } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label><?php echo $langage_lbl['LBL_RECIPIENT_NAME_HEADER_TXT']; ?></label>
                                                                    <input type="text"  name="vReceiverName"  id="vReceiverName" value="<?= $vReceiverName; ?>" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label><?php echo $langage_lbl['LBL_RECIPIENT_EMAIL_TXT']; ?></label>
                                                                    <input type="text"  pattern="[0-9]{1,}" name="vReceiverMobile"  id="vReceiverMobile" value="<?= $vReceiverMobile; ?>">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label><?php echo $langage_lbl['LBL_PICK_UP_INS']; ?></label>
                                                                    <input type="text"  name="tPickUpIns"  id="tPickUpIns" value="<?= $tPickUpIns; ?>">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label><?php echo $langage_lbl['LBL_DELIVERY_INS']; ?></label>
                                                                    <input type="text"  name="tDeliveryIns" id="tDeliveryIns" value="<?= $tDeliveryIns; ?>">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label><?php echo $langage_lbl['LBL_PACKAGE_DETAILS']; ?></label>
                                                                    <input type="text"  name="tPackageDetails"  id="tPackageDetails" value="<?= $tPackageDetails; ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php } ?>

                                                    <div class="step-actions">
                                                        <button class="waves-effect waves-dark btn-flat previous-step" id="user_back"><?php echo strtoupper($langage_lbl['LBL_BACK']); ?></button>
                                                        <button class="waves-effect waves-dark btn blue next-step"><?php echo $langage_lbl['LBL_CHOOSE_CONTACT_CONT_TXT']; ?></button>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="step" data-step="2" id="vehicle_type">
                                                <div data-step-label="There's labels too!" class="step-title waves-effect waves-dark">
                                                    <label class="ride_vehicle"> <?php echo $langage_lbl['LBL_SELECT_TXT']." ".$langage_lbl['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></label>
                                                    <label class="uberx_service"><?php echo $langage_lbl['LBL_SELECT_TXT']." ". $langage_lbl['LBL_MYTRIP_TRIP_TYPE']; ?></label>
                                                </div>
                                                <div class="step-content">

                                                    <div class="section-block">
                                                        <!--<label id="ride_vehicle">Select <?php echo $langage_lbl['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></label>
                                                        <label id="uberx_service">Select <?php echo $langage_lbl['LBL_MYTRIP_TRIP_TYPE']; ?></label>-->

<? //if ($userType1 != 'rider') {   ?>

                                                        <span id="VehicleTypeSpan"></span>                        
                                                        <!--<ul id="iVehicleTypeId" class="ride_vehicle"></ul>-->
                                                        <!--<span class="uberx_service"><select class="form-control form-control-select form-control14 uberx_service" name='iVehicleTypeId' id="iVehicleTypeIdUberx" required onChange="showAsVehicleType(this.value)">-->
                                                        <!--    <option value="" >Select <?= $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?></option>-->
                                                        <!--</select></span>-->

                                                        <!--<ul id="iVehicleTypeId" class="uberx_service"></ul>-->


<? //}   ?>

                                                    </div>
                                                    <div class="step-actions">
                                                        <button class="waves-effect waves-dark btn-flat previous-step" id="vehicle_type_back"><?php echo strtoupper($langage_lbl['LBL_BACK']); ?></button>
                                                        <button class="waves-effect waves-dark btn blue next-step"><?php echo $langage_lbl['LBL_CHOOSE_CONTACT_CONT_TXT']; ?></button>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="step">
                                                <div data-step-label="There's labels too!" class="step-title waves-effect waves-dark">
                                                <label class="ride_vehicle"><?php echo $langage_lbl['LBL_SELECT_YOUR_PICKUP_TYPE_WEB']; ?></label>
                                                    <label class="uberx_service"><?php echo $langage_lbl['LBL_SELECT_DRIVER_DATETIME']; ?></label>
                                                <?php //echo $langage_lbl['LBL_SELECT_YOUR_PICKUP_TYPE_WEB']; ?></div>
                                                <div class="step-content">
                                                    <div class="section-block">
                                                        <!--<label><?php echo $langage_lbl['LBL_SELECT_YOUR_PICKUP_TYPE_WEB']; ?></label>-->

                                                        <div class="data-row rideShow">
                                                            <div class="radio-combo">
                                                                <div class="radio-main">
                                                                    <span class="radio-hold">
                                                                        <input id="r3_eRideType" name="eRideType" type="radio" value="now" <? if ($eRideType != 'later') { ?> checked="" <? } ?>>
                                                                        <span class="radio-button"></span>
                                                                    </span>
                                                                </div><label for="r3_eRideType"><?php echo $langage_lbl['LBL_BOOK_NOW_MANUAL_BOOKING']; //echo $langage_lbl['LBL_RIDE_NOW'];   ?></label>
                                                            </div>
                                                            <?php if ($RIDE_LATER_BOOKING_ENABLED == "Yes") { ?>
                                                                <div class="radio-combo">
                                                                    <div class="radio-main">
                                                                        <span class="radio-hold">
                                                                            <input id="r4_eRideType" name="eRideType" type="radio" value="later" <? if ($eRideType == 'later') { ?> checked="" <? } ?>>
                                                                            <span class="radio-button"></span>
                                                                        </span>
                                                                    </div><label for="r4_eRideType"><?php echo $langage_lbl['LBL_BOOK_LATER_MANUAL_BOOKING']; //echo $langage_lbl['LBL_RIDE_LATER'];   ?></label>
                                                                </div>
                                                            <?php } ?>
                                                        </div>

                                                        <div class="data-row deliveryShow" style="display:none;">
                                                            <div class="radio-combo">
                                                                <div class="radio-main">
                                                                    <span class="radio-hold">
                                                                        <input id="r3_eDeliveryType" name="eDeliveryType" type="radio" checked='checked' value="now" <? if ($eDeliveryType != 'later') { ?> checked="" <? } ?>>
                                                                        <span class="radio-button"></span>
                                                                    </span>
                                                                </div><label for="r3_eDeliveryType"><?php echo $langage_lbl['LBL_BOOK_NOW_MANUAL_BOOKING']; //echo $langage_lbl['LBL_DELIVER_NOW_WEB'];   ?></label>
                                                            </div>
                                                            <div class="radio-combo">
                                                                <div class="radio-main">
                                                                    <span class="radio-hold">
                                                                        <input id="r4_eDeliveryType" name="eDeliveryType" type="radio" value="later" <? if ($eDeliveryType == 'later') { ?> checked="" <? } ?>>
                                                                        <span class="radio-button"></span>
                                                                    </span>
                                                                </div><label for="r4_eDeliveryType"><?php echo $langage_lbl['LBL_BOOK_LATER_MANUAL_BOOKING']; //echo $langage_lbl['LBL_DELIVER_LATER_WEB'];   ?></label>
                                                            </div>
                                                        </div>

                                                        <span class="dateSchedule" style="display:none">
                                                            <?php if (date("Y-m-d H:i:s") > $dBooking_date) { ?>
                                                                <input type="text" class="form-control form-control14" name="dBooking_date"  id="datetimepicker4" value="<?= $dBooking_date; ?>" placeholder="<?php echo $langage_lbl['LBL_SELECT_DATETIME_WEB']; ?>" onBlur="getFarevalues('');<?php if ($APP_TYPE == "UberX") { ?>setDriverListing();<?php } ?>" required>
                                                                <b class="pickup-schedule-img"><img src="assets/img/calander.png"></b>
                                                            <?php } else { ?>
                                                                <input type="text" class="form-control form-control14" name="dBooking_date"  id="datetimepicker4" value="<?= $dBooking_date; ?>" placeholder="<?php echo $langage_lbl['LBL_SELECT_DATETIME_WEB']; ?>" onBlur="getFarevalues('');<?php if ($APP_TYPE == "UberX") { ?>setDriverListing();<?php } ?>" required disabled="">
                                                                <b class="pickup-schedule-img"><img src="assets/img/calander.png"></b>
<?php } ?>
                                                        </span>

                                                    </div>
                                                    <?php
                                                    $femalevar = 0;
                                                    if (isset($FEMALE_RIDE_REQ_ENABLE) && $FEMALE_RIDE_REQ_ENABLE == 'Yes') {
                                                        $femalevar = 1;
                                                        if (!empty($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider') {
                                                            if ($eGender == 'Female' || $eGender == '') {
                                                                $femalevar = 1;
                                                            } else {
                                                                $femalevar = 0;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <? if ($APP_TYPE != 'UberX') { ?>
    <?php if ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
        <?php if (($femalevar == 1) || (isset($HANDICAP_ACCESSIBILITY_OPTION) && $HANDICAP_ACCESSIBILITY_OPTION == "Yes") || (isset($CHILD_SEAT_ACCESSIBILITY_OPTION) && $CHILD_SEAT_ACCESSIBILITY_OPTION == "Yes") || (isset($WHEEL_CHAIR_ACCESSIBILITY_OPTION) && $WHEEL_CHAIR_ACCESSIBILITY_OPTION == "Yes")) { ?>       
                                                                <div class="section-block delivery_hide" id="ride-type" style="display:block;">
                                                                    <label>Other Options</label>
                                                                    <div class="data-row">
            <?php if ($femalevar == 1) { ?>
                                                                            <div class="check-combo" id="femalediv">
                                                                                <div class="check-main">
                                                                                    <span class="check-hold">
                                                                                        <input type="checkbox" name="eFemaleDriverRequest" id="eFemaleDriverRequest" value="Yes" <?php if ($eFemaleDriverRequest == 'Yes') echo 'checked'; ?> <?php if (!empty($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider') { ?>onClick="femaleriders();" <?php } ?>>
                                                                                        <span class="check-button"></span>
                                                                                    </span>
                                                                                </div><label for="eFemaleDriverRequest"><?php echo $langage_lbl['LBL_LADIES_ONLY_RIDE_WEB']; ?></label>
                                                                            </div>
            <?php } if (isset($HANDICAP_ACCESSIBILITY_OPTION) && $HANDICAP_ACCESSIBILITY_OPTION == "Yes") { ?>
                                                                            <div class="check-combo">
                                                                                <div class="check-main">
                                                                                    <span class="check-hold">
                                                                                        <input type="checkbox" name="eHandiCapAccessibility" id="eHandiCapAccessibility" value="Yes" <?php if ($eHandiCapAccessibility == 'Yes') echo 'checked'; ?>>
                                                                                        <span class="check-button"></span>
                                                                                    </span>
                                                                                </div><label for="eHandiCapAccessibility"><?php echo $langage_lbl['LBL_PREFER_HANDICAP_ACCESSBILITY_WEB']; ?></label>
                                                                            </div>
            <?php } if (isset($CHILD_SEAT_ACCESSIBILITY_OPTION) && $CHILD_SEAT_ACCESSIBILITY_OPTION == "Yes") { ?>
                                                                            <div class="check-combo">
                                                                                <div class="check-main">
                                                                                    <span class="check-hold">
                                                                                        <input type="checkbox" name="eChildSeatAvailable" id="eChildSeatAvailable" value="Yes" <?php if ($eChildSeatAvailable == 'Yes') echo 'checked'; ?>>
                                                                                        <span class="check-button"></span>
                                                                                    </span>
                                                                                </div><label for="eChildSeatAvailable"><?php echo $langage_lbl['LBL_CHILD_SEAT_ADD_VEHICLES']; ?></label>
                                                                            </div>
            <?php } if (isset($WHEEL_CHAIR_ACCESSIBILITY_OPTION) && $WHEEL_CHAIR_ACCESSIBILITY_OPTION == "Yes") { ?>
                                                                            <div class="check-combo">
                                                                                <div class="check-main">
                                                                                    <span class="check-hold">
                                                                                        <input type="checkbox" name="eWheelChairAvailable" id="eWheelChairAvailable" value="Yes" <?php if ($eWheelChairAvailable == 'Yes') echo 'checked'; ?>>
                                                                                        <span class="check-button"></span>
                                                                                    </span>
                                                                                </div><label for="eWheelChairAvailable"><?php echo $langage_lbl['LBL_WHEEL_CHAIR_ADD_VEHICLES']; ?></label>
                                                                            </div>
                                                                <?php } ?>
                                                                    </div>
                                                                </div>
                                                            <?php
                                                            }
                                                        }
                                                    }
                                                    ?>
<? if ($APP_TYPE != 'UberX') { ?>
                                                        <div class="data-row auto_assign001 autoassignbtn">
                                                            <div class="check-main">
                                                                <span class="check-hold">
                                                                    <input type="checkbox" name="eAutoAssign" id="eAutoAssign" value="Yes" <?php if ($eAutoAssign == 'Yes') echo 'checked'; ?>>
                                                                    <span class="check-button"></span>
                                                                </span>
                                                            </div><label for="eAutoAssign"><?php echo $langage_lbl['LBL_AUTO_ASSIGN_WEB']; ?> <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?></label>
                                                            <div class="aternate-login _new" data-name="OR"></div>
                                                        </div>

<? } ?>

                                                    <div class="data-row uberx_service"><br><br></div>

                                                    <div id="showdriverSet001" class="assigned_driver" style="display:none;"><strong>Assigned <?php echo $langage_lbl['LBL_DRIVER_TXT']; ?>: </strong><label id="driverSet001"></label></div>
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


                                                    <!--<div class="pickup-location pickup-location1" style="margin-bottom: 10px;">
                                                            <h3 ><?php echo $langage_lbl['LBL_DISCOUNT_CODE_WEB']; ?></h3>
                                                            <span class="form-group"><div><input name="vCouponCode" id="promocode" type="text" placeholder="<?php echo $langage_lbl['LBL_COUPON_CODE_WEB']; ?>" value="<?= $vCouponCode ?>" class="clearable clearpromotxt"></div></span>
                                                            <b class='promocode-btn002'><a href="javascript:void(0);" id="myButton" class="submit" onclick="checkPromoCode('<?php echo $eBookingFrom; ?>');"><?php echo $langage_lbl['LBL_APPLY']; ?></a></b>
                                
                                                        </div>-->

                                                    <div class="step-actions">
                                                        <!--<button class="waves-effect waves-dark btn blue next-step"><?php echo $langage_lbl['LBL_CHOOSE_CONTACT_CONT_TXT']; ?></button>-->
                                                        <button class="waves-effect waves-dark btn-flat previous-step"><?php echo strtoupper($langage_lbl['LBL_BACK']); ?></button>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="booking-footer">
                                    <input type="hidden" name="newType" id="newType" value="">
                                    <input type="hidden" name="submitbtn" id="submitbtn" value="submitform">
                                    <input type="submit" class="submitbtn" name="submitbutton" id="submitbutton" value="<?php echo $langage_lbl['LBL_BOOK']; ?>">
                                </div>
                            </div>
                        </div>
                </form>
            </section>

<!-- End: Footer Script -->
<div style="clear:both;"></div>
<!-- Card Modal -->
<div id="CardModal" class="custom-modal-main" role="dialog">
    <div class="custom-modal">
        <!-- Modal content-->
        <div class="">
            <form id="cardPayForm" class="cardDetialClass001" novalidate autocomplete="on" method="POST">
                <div class="model-header">
                    <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
                    <h4 class="modal-title"><?= $langage_lbl['LBL_CARD_PAYMENT_DETAILS']; ?></h4>
                    <i class="icon-close" data-dismiss="modal"></i>
                </div>
                <div class="model-body">
                    <input type="hidden" name="type" value="GenerateToken" >
                    <span><div class="form-group"><input name="cardNo" type="text" placeholder="Enter Your Card Number" class="cc-number"  data-stripe="number"/></div></span>
                    <span>
                        <div class="form-group"><input type="text" name="cardExp" class="add-car-mm cc-exp" placeholder="MM / YYYY" data-stripe="exp"/></div>
                        <div class="form-group"><input type="text" name="cardCvv" class="add-car-cvv cc-cvc" placeholder="cvv" data-stripe="cvc"/></div>
                        <h2 class="validation"></h2>
                </div>
                <div class="model-footer button-block">
                    <!--<b class='card-btn002'><a href="javascript:void(0);" class="btn btn-success submit gen-btn" id="validatePayCard"><?= $langage_lbl['LBL_ADD_CARD']; ?></a></b>-->
                    <button type="button" class="btn btn-success submit gen-btn" data-dismiss="modal" id="validatePayCard"><?= $langage_lbl['LBL_ADD_CARD']; ?></button>
                    <button type="button" class="btn btn-danger gen-btn" data-dismiss="modal"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- added by SP for block driver start -->
<div class="custom-modal-main" id="blockdrivermodel" tabindex="-1" role="dialog" aria-labelledby="blockdrivermodel" aria-hidden="true">
    <div class="custom-modal" role="document">
        <div class="">
            <div class="model-header">
                <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>-->
                <h4 class="modal-title" id="inactiveModalLabel">Block driver</h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">
                <p><span style="font-size: 15px;">Driver is block so you can not assign it.</span></p>
            </div>
            <div class="model-footer button-block">
                <button type="button" class="btn btn-default gen-btn" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>
<!-- added by SP for block driver end -->
<?php if ($driversubs == 1) { ?>
    <!--Wallet Low Balance-->
    <div class="custom-modal-main" id="usermodel">
        <div class="custom-modal" role="document">
            <div class="">
                <div class="model-header">
                    <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">
                    <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>-->
                    <h4 class="modal-title" id="inactiveModalLabel"><?= $langage_lbl['LBL_LOW_WALLET_BALANCE_WEB']; ?></h4>
                    <i class="icon-close" data-dismiss="modal"></i>
                </div>

                <div class="model-body">
                    <p><span style="font-size: 15px;"> This <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> is having low balance in his wallet and is not able to accept cash ride. Would you still like to assign this <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?>?</span></p>
                    <p><b style="font-size: 15px;"> <?= $langage_lbl['LBL_MINIMUM_REQUIRED_BALANCE_WEB']; ?> : </b><span style="font-size: 15px;"><?php echo $generalobj->symbol_currency() . " " . number_format($WALLET_MIN_BALANCE, 2); ?></span></p>
                    <p><b style="font-size: 15px;"> <?= $langage_lbl['LBL_AVAILABLE_BALANCE_WEB']; ?> : </b><span style="font-size: 15px;"><?php echo $data1['currencySymbol']; ?> <span id="usr-bal"></span></span></p>
                </div>
                <div class="model-footer button-block">
                    <button type="button" class="btn btn-default gen-btn" data-dismiss="modal"><?= $langage_lbl['LBL_NOT_NOW_WEB']; ?></button>
                    <button type="button" class="btn btn-success btn-ok action_modal_submit gen-btn" data-dismiss="modal" onClick="checkdriversubscription('');"><?= $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
                </div>
            </div>
        </div>
    </div>
    <!--end Wallet Low Balance-->

    <!-- Driversubscription added by SP  -->
    <div class="custom-modal-main" id="driversubscriptionmodel" tabindex="-1" role="dialog" aria-labelledby="driversubscriptionmodel" aria-hidden="true">
        <div class="custom-modal" role="document">
            <div class="">
                <div class="model-header">
                    <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">
                    <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>-->
                    <h4 class="modal-title" id="inactiveModalLabel">Driver Subscription</h4>
                    <i class="icon-close" data-dismiss="modal"></i>
                </div>
                <div class="model-body">
                    <p><span style="font-size: 15px;">Driver is not subscribe to any plan. Do you want to still assign it?</span></p>
                </div>
                <div class="model-footer button-block">
                    <button type="button" class="btn btn-default gen-btn" data-dismiss="modal">Not Now</button>
                    <button type="button" class="btn btn-success btn-ok action_modal_submit gen-btn" data-dismiss="modal" onClick="AssignDriver('');">OK</button>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <!--Wallet Low Balance-->
    <div class="custom-modal-main" id="usermodel">
        <div class="custom-modal" role="document">
            <div class="">
                <div class="model-header">
                    <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">
                    <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>-->
                    <h4 class="modal-title" id="inactiveModalLabel"><?= $langage_lbl['LBL_LOW_WALLET_BALANCE_WEB']; ?></h4>
                    <i class="icon-close" data-dismiss="modal"></i>
                </div>

                <div class="model-body">
                    <p><span style="font-size: 15px;"> This <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> is having low balance in his wallet and is not able to accept cash ride. Would you still like to assign this <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?>?</span></p>
                    <p><b style="font-size: 15px;"> <?= $langage_lbl['LBL_MINIMUM_REQUIRED_BALANCE_WEB']; ?> : </b><span style="font-size: 15px;"><?php echo $generalobj->symbol_currency() . " " . number_format($WALLET_MIN_BALANCE, 2); ?></span></p>
                    <p><b style="font-size: 15px;"> <?= $langage_lbl['LBL_AVAILABLE_BALANCE_WEB']; ?> : </b><span style="font-size: 15px;"><?php echo $data1['currencySymbol']; ?> <span id="usr-bal"></span></span></p>
                </div>
                <div class="model-footer button-block">
                    <button type="button" class="btn btn-default gen-btn" data-dismiss="modal"><?= $langage_lbl['LBL_NOT_NOW_WEB']; ?></button>
                    <button type="button" class="btn btn-success btn-ok action_modal_submit gen-btn" data-dismiss="modal" onClick="AssignDriver('');"><?= $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
                </div>
            </div>
        </div>
    </div>
    <!--end Wallet Low Balance-->
<?php } ?>
<!--user inactive/deleted-->
<div class="custom-modal-main" id="inactiveModal" tabindex="-1" role="dialog" aria-labelledby="inactiveModalLabel">
    <div class="custom-modal" role="document">
        <div class="">
            <div class="model-header">
            <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>-->
                <h4 class="modal-title" id="inactiveModalLabel"><?php echo $langage_lbl['LBL_USER_DETAIL']; ?></h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">
                <span style="font-size: 15px;"><?php echo $langage_lbl['LBL_USER_STATUS_IN_MANUAL_BOOKING_WEB']; ?>  </span>
            </div>
            <div class="model-footer button-block">
                <button type="button" class="btn btn-success gen-btn" data-dismiss="modal"><?= $langage_lbl['LBL_CONTINUE_BTN']; ?></button>
                <!-- <button type="button" class="btn btn-primary">Continue</button> -->
            </div>
        </div>
    </div>
</div>
<!--end user inactive/deleted-->

<!--surcharge confirmation-->
<div class="custom-modal-main" id="surgemodel" tabindex="-1" role="dialog" aria-labelledby="surgemodel" aria-hidden="true">
    <div class="custom-modal" role="document">
        <div class="">
            <div class="model-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>-->
                <h4 class="modal-title" id="inactiveModalLabel"><?php echo $langage_lbl['LBL_CONFIRM_SURCHARGE_WEB']; ?></h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">
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
            <div class="model-footer button-block">
                <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button> -->
                <button type="button" class="btn btn-success btn-ok action_modal_submit gen-btn" data-dismiss="modal" ><?php echo $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
            </div>
        </div>
    </div>
</div>

<!--end surcharge confirmation-->
<link rel="stylesheet" type="text/css" media="screen" href="<?= $tsiteAdminUrl; ?>css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="<?= $tsiteAdminUrl; ?>js/moment.min.js"></script>
<script type="text/javascript" src="<?= $tsiteAdminUrl; ?>js/bootstrap-datetimepicker.min.js"></script>
<link rel="stylesheet" href="<?= $tsiteAdminUrl; ?>css/select2/select2.min.css" type="text/css" >
<script type="text/javascript" src="<?= $tsiteAdminUrl; ?>js/plugins/select2.min.js"></script>
<script src="assets/js/modal_alert.js"></script>

<?php require_once("script_functions.php"); ?>
<?php require_once("functions.php"); ?>
<div class="custom-modal-main" id="femaleriders" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="custom-modal">
        <div class="model-header">
            <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_GENDER_TXT']; ?></h4>
            <!--<i class="icon-close" data-dismiss="modal"></i>-->
        </div>
        <div class="model-body">
            <div class="data-row rideShow">
                <div class="radio-combo">
                    <div class="radio-main">
                        <span class="radio-hold">
                            <input id="g1" name="eGender" type="radio" value="<?= $langage_lbl['LBL_MALE_TXT'] ?>" onclick="gender_select(this.value);">
                            <span class="radio-button"></span>
                        </span>
                    </div><label for="g1"><?php echo $langage_lbl['LBL_MALE_TXT']; ?></label>
                </div>
                <div class="radio-combo">
                    <div class="radio-main">
                        <span class="radio-hold">
                            <input id="g2" name="eGender" type="radio" value="<?= $langage_lbl['LBL_FEMALE_TXT'] ?>" onclick="gender_select(this.value);">
                            <span class="radio-button"></span>
                        </span>
                    </div><label for="g2"><?php echo $langage_lbl['LBL_FEMALE_TXT']; ?></label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="custom-modal-main" id="fareEstimateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="custom-modal">
        <div class="booking-passenger-fare-estimate">
            <div class="model-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
                <!--    <span aria-hidden="true">&times;</span>-->
                <!--</button>-->
                <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_FARE_ESTIMATE_TXT']; ?></h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">
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
        <span><?php echo $langage_lbl['LBL_Total_Fare']; ?> <?php //echo $data1['currencySymbol']; ?><b id="totalcost">0</b></span> +
        <span><?php echo $langage_lbl['LBL_TOLL_TXT']; ?> <b id="tollcost">0</b></span>
        <!-- following hidden variables are taken bc custom modal is used and in that response js variable is not used directly so taken from hidden variable.. -->
        <input type="hidden" name="idClicked" id="idClicked" value="">
        <input type="hidden" name="vTollPriceCurrencyCode_temp" id="vTollPriceCurrencyCode_temp" value="">
        <input type="hidden" name="fTollPrice_temp" id="fTollPrice_temp" value="">
        <input type="hidden" name="eTollSkipped_temp" id="eTollSkipped_temp" value="">
    </p>
</div>

<div class="custom-modal-main large-modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="custom-modal modal-large" style="min-width: 50%;">
        <div class="">
            <div class="model-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>-->
                <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_DIS_HOW_IT_WORKS']; ?></h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">
<?php if ($APP_TYPE == "Ride" || $APP_TYPE == "Delivery" || $APP_TYPE == "Ride-Delivery") { ?>
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
<?php } else if ($APP_TYPE == 'UberX') { ?>
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

<?php } else { ?>
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

<?php } ?>
            </div>
            <div class="model-footer button-block">
                <button type="button" class="btn btn-secondary gen-btn" data-dismiss="modal"><?php echo $langage_lbl['LBL_CLOSE_TXT']; ?></button>
            </div>
        </div>
    </div>
</div>

<!--<div class="modal fade" id="myModalufx" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
</div>-->

<div class="custom-modal-main" id="myModalautoassign" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="custom-modal">
        <div class="">
            <div class="model-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>-->
                <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_DIS_HOW_IT_WORKS']; ?></h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">
                <p style="font-size: 15px;"><?php echo $langage_lbl['LBL_MANUAL_ALERT_MESSAGE_TIME_WEB']; ?> </p>
            </div>
            <div class="model-footer button-block">
                <button type="button" class="btn btn-secondary btn-success gen-btn" data-dismiss="modal"><?php echo $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
            </div>
        </div>
    </div>
</div>

<div class="custom-modal-main" id="driverData" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="custom-modal">
        <div class="">
            <div class="model-header">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">x</span>
                </button> -->
                <h4 class="modal-title" id="myModalLabel"><?php echo $langage_lbl['LBL_ARRIVING_TXT']; ?></h4>
            </div>
            <div class="model-body">
                <div id="driver-bottom-set001">	
                </div>
            </div>
            <div class="model-footer button-block">
                <button type="button" id="btnYes" class="btn btn-secondary btn-success gen-btn" data-dismiss="modal"><?php echo $langage_lbl['LBL_BTN_OK_TXT']; ?></button>
            </div>
        </div>
    </div>
</div>
<div class="booking-confirmation-popup hide001" id="request-loader001" >
    <div class="requesting-popup-old">
        <span id="requ_title" class="req-001"><b><?php echo ucfirst(strtolower($langage_lbl['LBL_REQUESTING_TXT'])); ?> ....</b>
            <span class="requesting-popup-sub" style="padding-right: 5px;float: right;"><a href="javascript:void(0);" id="cancelBtn" onclick="cancellingRequestDriver();"><?php echo $langage_lbl['LBL_CANCEL_TXT']; ?></a></span></span>
    </div>
    <div class="requesting-popup hide001" id="req_try_again">
        <p><?php echo $langage_lbl['LBL_NO_DRIVER_AVALIABLE_TO_ACCEPT_WEB']; ?></p>
        <span><a href="javascript:void(0);" id="retryBtn"><?php echo $langage_lbl['LBL_RETRY_TXT']; ?></a></span>
        <span><a href="javascript:void(0);" id="cancelBtn" onclick="cancellingRequestDriver();"><?php echo $langage_lbl['LBL_CANCEL_TXT']; ?></a></span>
    </div>
    <div class="pulse-box"> <svg class="pulse-svg" width="155px" height="155px" viewBox="0 0 50 50" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <circle class="circle first-circle" fill="#333" cx="25" cy="25" r="25"></circle>
        <circle class="circle second-circle" fill="#333" cx="25" cy="25" r="25"></circle>
        <circle class="circle third-circle" fill="#333" cx="25" cy="25" r="25"></circle>
        <circle class="circle" fill="#000" cx="25" cy="25" r="25"></circle>
        <!--<em><img src="images-new/confirmation-img.png" alt=""></em>-->
        </svg> </div>
    <!--div class="booking-confirmation-popup-inner">  
        <div class="ripple"><img class="confirmation-img" src="images-new/confirmation-img.png" alt="" /></div>
    </div-->
</div>

