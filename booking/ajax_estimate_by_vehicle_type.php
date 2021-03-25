<?php
include_once("../common.php");
include_once ('../include_generalFunctions_shark.php');
include_once ('../app_common_functions.php');
include_once ('../include/include_webservice_enterprisefeatures.php');
if (checkFlyStationsModule(1)) {
    include_once ('../include/features/include_fly_stations.php');
}
//$generalobj->check_member_login();
$vehicleId = isset($_REQUEST['vehicleId']) ? $_REQUEST['vehicleId'] : '';
$varfrom = isset($_REQUEST['varfrom']) ? $_REQUEST['varfrom'] : '';
$booking_date = isset($_REQUEST['booking_date']) ? $_REQUEST['booking_date'] : '';
$vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
$FromLatLong = isset($_REQUEST['FromLatLong']) ? $_REQUEST['FromLatLong'] : '';
$iMemberId = isset($_REQUEST['iMemberId']) ? $_REQUEST['iMemberId'] : '';
$userType1 = isset($_REQUEST['userType1']) ? $_REQUEST['userType1'] : '';
$ToLatLong = isset($_REQUEST['ToLatLong']) ? $_REQUEST['ToLatLong'] : '';
$promoCode = isset($_REQUEST['promoCode']) ? $_REQUEST['promoCode'] : '';
$promocodeapplied = isset($_REQUEST['promocodeapplied']) ? $_REQUEST['promocodeapplied'] : '';

//this is for cubex only but not put in the condition bc this variable is used in qry and here condition is not put...
if ($userType1 == 'rider') {
    $table_name = 'register_user';
    $field = 'iUserId';
} else if ($userType1 == 'company') {
    $table_name = 'company';
    $field = 'iCompanyId';
}

$getMemberData = $obj->MySQLSelect("SELECT vLang,vTimeZone FROM " . $table_name . " WHERE $field='" . $iMemberId . "'");
$vTimeZone = "Asia/Kolkata";
$vLang = "EN";
if (count($getMemberData) > 0) {
    $vLang = $getMemberData[0]['vLang'];
    $vTimeZone = $getMemberData[0]['vTimeZone'];
}
date_default_timezone_set($vTimeZone);
//$vLang = get_value($table_name, 'vLang', $field, $iMemberId, '', 'true'); //get language code of driver

if ($vLang == "" || $vLang == NULL) {
    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
}

//if(empty($iMemberId)){
if($vLang != $_SESSION['sess_lang']){
    $DisplayFrontEstimate = "Yes";
    $vLang = $langcodefront = $_SESSION['sess_lang'];
}

if ($generalobj->checkXThemOn() == 'Yes') {
    if (!empty($promocodeapplied) && $promocodeapplied == 1 && !empty($promoCode)) {
        $promoCode = $promoCode;
    } else {
        $promoCode = '';
    }
}

if (!empty($FromLatLong) && !empty($ToLatLong)) {
    $pickUpLatLong = explode(",", $FromLatLong);
    $dropoffLatLong = explode(",", $ToLatLong);
    $pickuplocationarr = array($pickUpLatLong[0], $pickUpLatLong[1]);
    $dropofflocationarr = array($dropoffLatLong[0], $dropoffLatLong[1]);
}

$iFromStationId = isset($_REQUEST['iFromStationId']) ? $_REQUEST['iFromStationId'] : '';
$iToStationId = isset($_REQUEST['iToStationId']) ? $_REQUEST['iToStationId'] : '';

if ($booking_date == "") {
    $booking_date = date("Y-m-d H:i:s");
}
if ($vehicleId != '' && $booking_date != "") {
    global $generalobj;
    $fPickUpPrice = $fNightPrice = $surgeprice = "1";
    $surgetype = "None";
    $eFlatTrip = "No";
    $fFlatTripPrice = 0;
    ## Checking For Flat Trip ##
    if (!empty($pickuplocationarr) && !empty($dropofflocationarr)) {
        $data_flattrip = checkFlatTripnew($pickuplocationarr, $dropofflocationarr, $vehicleId);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    }
    //Added By HJ On 13-05-2019 For Get Fare Estimate On Manual Booking Start
    $isDestinationAdded = "Yes";
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST['eType'] : 'Ride';
    $eFly = '';
    if ($eType == 'Fly') {
        $eFly = 'Yes';
        $eType = 'Ride';
        $iFromLocationId = $iFromStationId;
        $iToLocationId = $iToStationId;
    }

    $time = isset($_REQUEST["timeduration"]) ? $_REQUEST['timeduration'] : '1';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST['distance'] : '1';
    $time = round(($time), 2);
    $distance = round(($distance), 2);
    $userType1 = ucfirst($userType1);
    /*if ($userType1 == 'Rider')
        $userType1 = 'Passenger';*/
    //$userType1 = 'Passenger';
    //print_r($pickuplocationarr);
    //print_r($dropofflocationarr);
    //echo $time."==".$distance."==".$vehicleId."==".$iMemberId."==".$promoCode."==".$userType1."==".$isDestinationAdded."==".$eFlatTrip."==".$fFlatTripPrice."==".$dropofflocationarr."==".$eType."==".$booking_date."<br>";
    if($userType1=='Admin') $iMemberId = 0;

    //added for admin country code on 11-01-2020
    if ($iMemberId <= 0 || $iMemberId == "") {
        $countryCodeAdmin = $vCountry;
    }

    //added for Company country code on 11-01-2020
    if ($userType1 == "Company") {
        $countryCodeAdmin = $vCountry;
    }

    if($userType1=='Rider' || $userType1=='Admin') $userType1 = 'Passenger';
    $Fare_data = calculateFareEstimateAll($time, $distance, $vehicleId, $iMemberId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", ucfirst($userType1), 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $pickuplocationarr, $dropofflocationarr, "", $eType, $booking_date, $eFly, $iFromLocationId, $iToLocationId);
    //Added By HJ On 13-05-2019 For Get Fare Estimate On Manual Booking End
    //print_r($Fare_data);die;
    ## Checking For Flat Trip ##
    $Data = $generalobj->checkSurgePrice($vehicleId, $booking_date);
    if ($Data['Action'] != "1") {
        $fPickUpPrice = $Data['fPickUpPrice'];
        $fNightPrice = $Data['fNightPrice'];
        $surgeprice = $Data['surgeprice'];
        $surgetype = $Data['surgetype'];
        if ($surgetype == "PickUp") {
            // $returnArr['PickStartTime'] = $Data['pickStartTime'];
            // $returnArr['PickEndTime'] = $Data['pickEndTime'];
            $returnArr['Time'] = $Data['pickStartTime'] . " To " . $Data['pickEndTime'];
        } else if ($surgetype == "Night") {
            // $returnArr['NightStartTime'] = $Data['nightStartTime'];
            // $returnArr['NightEndTime'] = $Data['nightEndTime'];
            $returnArr['Time'] = "From " . $Data['nightStartTime'] . " To " . $Data['nightEndTime'];
        }
    }


    $sql = "select vVehicleType_$vLang as vVehicleType ,iBaseFare,fPricePerKM,fPricePerMin,iMinFare,vLogo from vehicle_type where iVehicleTypeId = '" . $vehicleId . "' LIMIT 1";
    $db_model = $obj->MySQLSelect($sql);

    $APPLY_SURGE_ON_FLAT_FARE = $generalobj->getConfigurations("configurations", "APPLY_SURGE_ON_FLAT_FARE");
    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
        $fPickUpPrice = $fNightPrice = $surgeprice = 1;
    }
    if ($userType1 == 'rider' && !empty($iMemberId)) {
        $data = $generalobj->getUserCurrencyLanguageDetailsWeb($iMemberId, '');
        $db_model[0]['iBaseFare'] = ($data['Ratio'] * $db_model[0]['iBaseFare']);
        $getVehicleCountryUnit_PricePerKm = ($data['Ratio'] * getVehicleCountryUnit_PricePerKm($vehicleId, $db_model[0]['fPricePerKM']));
        $db_model[0]['fPricePerMin'] = ($data['Ratio'] * $db_model[0]['fPricePerMin']);
        $db_model[0]['iMinFare'] = ($data['Ratio'] * $db_model[0]['iMinFare']);
        $fPickUpPrice = $fPickUpPrice;
        $fNightPrice = ($fNightPrice);
        $surgeprice = ($surgeprice);
        $surgetype = $surgetype;
        $eFlatTrip = $eFlatTrip;
        $fFlatTripPrice = ($data['Ratio'] * $fFlatTripPrice);
    } else if ($userType1 == 'company' && !empty($iMemberId)) {
        $data = $generalobj->getCompanyCurrencyLanguageDetailsWeb($iMemberId, '');
        $db_model[0]['iBaseFare'] = ($data['Ratio'] * $db_model[0]['iBaseFare']);
        $getVehicleCountryUnit_PricePerKm = ($data['Ratio'] * getVehicleCountryUnit_PricePerKm($vehicleId, $db_model[0]['fPricePerKM']));
        $db_model[0]['fPricePerMin'] = ($data['Ratio'] * $db_model[0]['fPricePerMin']);
        $db_model[0]['iMinFare'] = ($data['Ratio'] * $db_model[0]['iMinFare']);
        $fPickUpPrice = $fPickUpPrice;
        $fNightPrice = ($fNightPrice);
        $surgeprice = ($surgeprice);
        $surgetype = $surgetype;
        $eFlatTrip = $eFlatTrip;
        $fFlatTripPrice = ($data['Ratio'] * $fFlatTripPrice);
    } else {
        $db_model[0]['iBaseFare'] = $db_model[0]['iBaseFare'];
        $getVehicleCountryUnit_PricePerKm = getVehicleCountryUnit_PricePerKm($vehicleId, $db_model[0]['fPricePerKM']);
        $db_model[0]['fPricePerMin'] = $db_model[0]['fPricePerMin'];
        $db_model[0]['iMinFare'] = $db_model[0]['iMinFare'];
        $fPickUpPrice = $fPickUpPrice;
        $fNightPrice = $fNightPrice;
        $surgeprice = $surgeprice;
        $surgetype = $surgetype;
        $eFlatTrip = $eFlatTrip;
        $fFlatTripPrice = $fFlatTripPrice;
    }
    $returnArr['vehicleName'] = $db_model[0]['vVehicleType'];
    $returnArr['iBaseFare'] = $db_model[0]['iBaseFare'];
    $returnArr['fPricePerKM'] = getVehicleCountryUnit_PricePerKm($vehicleId, $db_model[0]['fPricePerKM']);
    $returnArr['fPricePerMin'] = $db_model[0]['fPricePerMin'];
    $returnArr['iMinFare'] = $db_model[0]['iMinFare'];
    $returnArr['iBaseFare'] = $db_model[0]['iBaseFare'];
    $returnArr['fPickUpPrice'] = $fPickUpPrice;
    $returnArr['fNightPrice'] = $fNightPrice;
    $returnArr['fSurgePrice'] = $surgeprice;
    $returnArr['SurgeType'] = $surgetype;
    $returnArr['eFlatTrip'] = $eFlatTrip;
    $returnArr['fFlatTripPrice'] = $fFlatTripPrice;
    //Added By HJ On 13-05-2019 For Get Fare Estimate On Manual Booking Start
    $estimateArr = array();
    $totalFare = 0;
    $roundoff = $totalnetFare = 0;

    $totalFareData = end($Fare_data);
    $totalFare = current(array_slice($totalFareData, -1));

    for ($r = 0; $r < count($Fare_data); $r++) {
        foreach ($Fare_data[$r] as $key => $val) {
            if ($key == "total_fare_amount" || $key == "eDisplaySeperator") {
                
            } else {
                $fareArr = array();
                $fareArr['key'] = $key;
                $fareArr['value'] = $val;
                $estimateArr[] = $fareArr;
            }
            if ($key == $langage_lbl['LBL_SUBTOTAL_TXT']) {
                $totalFare = $val;
            }
            if ($key == $langage_lbl['LBL_ROUNDING_DIFF_TXT']) {
                $roundoff = 1;
            }
            if ($key == $langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT']) {
                $totalnetFare = $val;
            }
        }
    }
    if ($roundoff == 1) {
        $totalFare = $totalnetFare;
    }
    $returnArr['estimateArr'] = $estimateArr;
    $returnArr['totalFare'] = $totalFare;
    $Photo_Gallery_folder = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $vehicleId . '/android/' . $db_model[0]['vLogo'];
    //echo $Photo_Gallery_folder;exit;


    if ($db_model[0]['vLogo'] != "" && file_exists($Photo_Gallery_folder)) {
        $db_model[0]['vLogo'] = $tconfig["tsite_upload_images_vehicle_type"] . '/' . $vehicleId . '/android/' . $db_model[0]['vLogo'];
        //$logo = "<img src=".$db_model[0]['vLogo']." width='60' height='60'>";
        $logo = $db_model[0]['vLogo'];
    } else {
        $db_model[0]['vLogo'] = "";
        $logo = "";
        //$db_car['vLogo'] = $tconfig["tsite_url"]."/webimages/icons/DefaultImg/ic_car.png";
    }
    $returnArr['vehicleImage'] = $logo;
    //Added By HJ On 13-05-2019 For Get Fare Estimate On Manual Booking End

    if ($eFlatTrip != 'No' || $eFly == 'Yes') {
        $returnArr['faretxt'] = $langage_lbl['LBL_GENERAL_NOTE_FLAT_FARE_EST'];
    } else {
        $returnArr['faretxt'] = $langage_lbl['LBL_GENERAL_NOTE_FARE_EST'];
    }

    echo json_encode($returnArr);
    exit;
}
?>