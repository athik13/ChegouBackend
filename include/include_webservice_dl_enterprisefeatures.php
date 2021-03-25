<?php

// ##########################################################################
/* For WayBill */

if ($type == "displayWayBill") {

    $driverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $iOrderId = isset($_REQUEST['iOrderId']) ? clean($_REQUEST['iOrderId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $vTimeZone = isset($_REQUEST['vTimeZone']) ? clean($_REQUEST['vTimeZone']) : '';
    $driver_detail = get_value('register_driver', 'vName,vLastName,vCurrencyDriver,vLang', 'iDriverId', $driverId);

    if ($iOrderId == '') {
        $sql = "SELECT iOrderId from trips WHERE iDriverId = '" . $driverId . "' ORDER BY iTripId DESC LIMIT 0,1";
        $tData = $obj->MySQLSelect($sql);
        $iOrderId = $tData[0]['iOrderId'];
    }

    $UserDetailsArr = getDriverCurrencyLanguageDetails($driverId, $iOrderId);
    $Ratio = $UserDetailsArr['Ratio'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $sql = "SELECT * from trips WHERE iOrderId = '" . $iOrderId . "' ORDER BY iTripId DESC LIMIT 0,1";
    $tripData = $obj->MySQLSelect($sql);
    if (count($tripData) > 0) {
        $passenger_detail = get_value('register_user', 'vName,vLastName,eHail', 'iUserId', $tripData[0]['iUserId']);
        $passengername = $passenger_detail[0]['vName'] . " " . $passenger_detail[0]['vLastName'];

        // # get fare details ##
        $vLang = $driver_detail[0]['vLang'];
        if ($vLang == "" || $vLang == NULL) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $orders = get_value('orders', '*', 'iOrderId', $iOrderId);
        $sql_request = "SELECT * FROM currency WHERE vName='" . $driver_detail[0]['vCurrencyDriver'] . "'";
        $drivercurrencydata = $obj->MySQLSelect($sql_request);
        $priceRatio = $drivercurrencydata[0]['Ratio'];
        $vCurrencySymbol = $drivercurrencydata[0]['vSymbol'];
        $fTripGenerateFare = $tripData[0]['fTripGenerateFare'];
        $fFlatTripPrice = $tripData[0]['fFlatTripPrice'];
        $fTripGenerateFare = round($fTripGenerateFare * $priceRatio, 2);
        $fFlatTripPrice = round($fFlatTripPrice * $priceRatio, 2);
        $cquery = "SELECT vCompany,vCaddress,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vPhone,vCode FROM company WHERE iCompanyId = '" . $orders[0]['iCompanyId'] . "'";
        $CompanyData = $obj->MySQLSelect($cquery);
        $vCompany = ($CompanyData[0]['vCompany'] != '') ? $CompanyData[0]['vCompany'] : "";
        $vRestuarantLocation = ($CompanyData[0]['vRestuarantLocation'] != '') ? $CompanyData[0]['vRestuarantLocation'] : "";
        $UserAddressArr = GetUserAddressDetail($orders[0]['iUserId'], "Passenger", $orders[0]['iUserAddressId']);
        $UserAdress = ucfirst($passengername) . "\n" . $UserAddressArr['UserAddress'];
        $fDeliveryCharge = $tripData[0]['fDeliveryCharge'];
        $fDeliveryCharge = round($fDeliveryCharge * $Ratio, 2);
        $order_fDeliveryCharge = $currencySymbol . " " . $fDeliveryCharge;
        $Rate = $order_fDeliveryCharge;

        // # get fare details ##
        $tripArr['DriverName'] = $driver_detail[0]['vName'] . " " . $driver_detail[0]['vLastName'];
        $tripArr['vOrderNo'] = $orders[0]['vOrderNo'];
        $serverTimeZone = date_default_timezone_get();
        //$convertorderdate = converToTz($orders[0]['tOrderRequestDate'], $serverTimeZone, $vTimeZone, "Y-m-d H:i:s");
        $convertorderdate = converToTz($orders[0]['tOrderRequestDate'], $vTimeZone, $serverTimeZone, "Y-m-d H:i:s");
        $tripArr['tOrderRequestDate_Org'] = $convertorderdate;
        $tripArr['tOrderRequestDate'] = date('d M, h:iA', strtotime($convertorderdate));
        $tripArr['ProjectName'] = $SITE_NAME;
        $tripArr['tSaddress'] = ucfirst($vCompany) . "\n" . $vRestuarantLocation;
        $tripArr['tDaddress'] = $UserAdress;
        $tripArr['PassengerName'] = ucwords($passengername);
        $tripArr['Licence_Plate'] = get_value('driver_vehicle', 'vLicencePlate', 'iDriverVehicleId', $tripData[0]['iDriverVehicleId'], '', 'true');
        //$tripArr['Rate'] = $Rate; // Commented By HJ On 24-12-2019 As Per Discuss Between DT and CD sir 
        $returnArr['Action'] = "1";
        $returnArr['message'] = $tripArr;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }


    setDataResponse($returnArr);
}

/* For WayBill */
// ##########################################################################
?>