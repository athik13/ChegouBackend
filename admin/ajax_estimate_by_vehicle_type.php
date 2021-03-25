<?php

include_once("../common.php");
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
include_once ('../include_generalFunctions_shark.php');
include_once ('../app_common_functions.php');
if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
    include_once ('../include/include_webservice_enterprisefeatures.php');
}
require_once(TPATH_CLASS . "class.general.php");
$general = new General();
//$Fare_data = calculateFareEstimateAll($time, $distance, $SelectedCar, $iUserId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", $userType, 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr, "", $eType);
$vehicleId = isset($_REQUEST['vehicleId']) ? $_REQUEST['vehicleId'] : '';
$varfrom = isset($_REQUEST['varfrom']) ? $_REQUEST['varfrom'] : '';
$booking_date = isset($_REQUEST['booking_date']) ? $_REQUEST['booking_date'] : '';
$vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
$FromLatLong = isset($_REQUEST['FromLatLong']) ? $_REQUEST['FromLatLong'] : '';
$ToLatLong = isset($_REQUEST['ToLatLong']) ? $_REQUEST['ToLatLong'] : '';
$iUserId = isset($_REQUEST['userId']) ? $_REQUEST['userId'] : 0;
$eBookingFrom = $_SESSION['SessionUserType'];
$bookingHotelId = $_SESSION['sess_iAdminUserId'];
if (!empty($FromLatLong) && !empty($ToLatLong)) {
    $pickUpLatLong = explode(",", $FromLatLong);
    $dropoffLatLong = explode(",", $ToLatLong);
    $pickuplocationarr = array($pickUpLatLong[0], $pickUpLatLong[1]);
    $dropofflocationarr = array($dropoffLatLong[0], $dropoffLatLong[1]);
}
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
    if (!empty($pickuplocationarr) && !empty($dropofflocationarr) && strtoupper(PACKAGE_TYPE) != "STANDARD") {
        $data_flattrip = checkFlatTripnew($pickuplocationarr, $dropofflocationarr, $vehicleId);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    }
    //Added By HJ On 13-05-2019 For Get Fare Estimate On Manual Booking Start
    //$iUserId = 0;
    $promoCode = "";
    $userType = 'Passenger';
    $isDestinationAdded = "Yes";
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST['eType'] : 'Ride';
    $time = isset($_REQUEST["timeduration"]) ? $_REQUEST['timeduration'] : '1';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST['distance'] : '1';
    if ($iUserId <= 0 || $iUserId == "") {
        $countryCodeAdmin = $vCountry;
    }
    $time = round(($time), 2);
    $distance = round(($distance), 2);
    //print_r($pickuplocationarr);
    //print_r($dropofflocationarr);
    //echo $time."==".$distance."==".$vehicleId."==".$iUserId."==".$promoCode."==".$userType."==".$isDestinationAdded."==".$eFlatTrip."==".$fFlatTripPrice."==".$dropofflocationarr."==".$eType."==".$booking_date."<br>";
    $Fare_data = calculateFareEstimateAll($time, $distance, $vehicleId, $iUserId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", $userType, 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $pickuplocationarr, $dropofflocationarr, "", $eType, $booking_date);
    //Added By HJ On 13-05-2019 For Get Fare Estimate On Manual Booking End
    //echo "<pre>";print_r($Fare_data);die;
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

    $sql = "select iBaseFare,fPricePerKM,fPricePerMin,iMinFare from vehicle_type where iVehicleTypeId = '" . $vehicleId . "' LIMIT 1";
    $db_model = $obj->MySQLSelect($sql);
    // echo "<pre>";print_r($db_model);exit;
    $APPLY_SURGE_ON_FLAT_FARE = $generalobj->getConfigurations("configurations", "APPLY_SURGE_ON_FLAT_FARE");
    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
        $fPickUpPrice = $fNightPrice = $surgeprice = 1;
    }
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
    if ($default_lang != 'EN') {
        $languageLabelsArr = $general->getLanguageLabelsArr("EN", "1");
        $subtotalLbl = $languageLabelsArr['LBL_SUBTOTAL_TXT'];
        $nettotalLbl = $languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT'];
    } else {
        $subtotalLbl = $langage_lbl_admin['LBL_SUBTOTAL_TXT'];
        $nettotalLbl = $langage_lbl_admin['LBL_ROUNDING_NET_TOTAL_TXT'];
    }
    
    $getSymbol = '';
    $roundoff = $totalnetFare = 0;
    
    $totalFareData = end($Fare_data);
    $totalFare = current(array_slice($totalFareData, -1));
    
    for ($r = 0; $r < count($Fare_data); $r++) {
        foreach ($Fare_data[$r] as $key => $val) {
            if ($getSymbol == "") {
                $getSymbol = explode(" ", $val);
            }
            if ($key == "total_fare_amount" || $key == "eDisplaySeperator") {
                if ($key == "total_fare_amount") {
                    $totalFare = $getSymbol[0] . " " . $val;
                }
            } else {
                $fareArr = array();
                $fareArr['key'] = $key;
                $fareArr['value'] = $val;
                $estimateArr[] = $fareArr;
            }
            if ($key == $subtotalLbl) {
                $totalFare = $val;
            }
            if($key==$langage_lbl_admin['LBL_ROUNDING_DIFF_TXT']) {
                $roundoff = 1;
            }
            if ($key == $nettotalLbl) {
                $totalnetFare = $val;
            }
        }
    }
    
    if($roundoff==1) {
        $totalFare = $totalnetFare;
    }
    $returnArr['estimateArr'] = $estimateArr;
    $returnArr['totalFare'] = $totalFare;
    //Added By HJ On 13-05-2019 For Get Fare Estimate On Manual Booking End
    //print_r($returnArr);die;
    echo json_encode($returnArr);
    exit;
}
?>