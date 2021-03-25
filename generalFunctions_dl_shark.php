<?php

function getTripFare($Fare_data, $surgePrice) {
    global $generalobj, $obj;

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    // $ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");
    $iVehicleTypeId = get_value('trips', 'iVehicleTypeId', 'iTripId', $Fare_data[0]['iTripId'], '', 'true');
    $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    $iParentId = get_value($sql_vehicle_category_table_name, 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    if ($iParentId == 0) {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }

    $eFlatTrip = $Fare_data[0]['eFlatTrip'];
    $fFlatTripPrice = $Fare_data[0]['fFlatTripPrice'];
    if ($eFlatTrip == "Yes") {
        $Fare_data[0]['iBaseFare'] = $fFlatTripPrice;
        $Fare_data[0]['fPricePerMin'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
    }

    // $ePriceType=get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
    $fAmount = 0;
    if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
        $iDriverVehicleId = get_value('trips', 'iDriverVehicleId', 'iTripId', $Fare_data[0]['iTripId'], '', 'true');
        $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $serviceProData = $obj->MySQLSelect($sqlServicePro);
        if (count($serviceProData) > 0) {
            $fAmount = $serviceProData[0]['fAmount'];
        }
    }

    if ($surgePrice >= 1) {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $surgePrice;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $surgePrice;
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $surgePrice;
        $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $surgePrice;
    }

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['fPricePerMin'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $fAmount != 0) {
            $Fare_data[0]['iBaseFare'] = $fAmount * $Fare_data[0]['iQty'];
        } else {
            $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'] * $Fare_data[0]['iQty'];
        }
    } else if ($Fare_data[0]['eFareType'] == 'Hourly') {
        $Fare_data[0]['iBaseFare'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
        $totalHour = $Fare_data[0]['TripTimeMinutes'] / 60;
        $Fare_data[0]['TripTimeMinutes'] = $totalHour;
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $fAmount != 0) {
            $Fare_data[0]['fPricePerMin'] = $fAmount;
        } else {
            $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerHour'];
        }
    }

    $Minute_Fare = round($Fare_data[0]['fPricePerMin'] * $Fare_data[0]['TripTimeMinutes'], 2);
    $Distance_Fare = round($Fare_data[0]['fPricePerKM'] * $Fare_data[0]['TripDistance'], 2);
    $iBaseFare = round($Fare_data[0]['iBaseFare'], 2);
    $fMaterialFee = round($Fare_data[0]['fMaterialFee'], 2);
    $fMiscFee = round($Fare_data[0]['fMiscFee'], 2);
    $fDriverDiscount = round($Fare_data[0]['fDriverDiscount'], 2);
    $fVisitFee = round($Fare_data[0]['fVisitFee'], 2);

    //  print_r($Fare_data);
    $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;

    // exit();
    $total_fare_for_commission_ufx = $iBaseFare + $Minute_Fare + $Distance_Fare;
    $Commision_Fare = round((($total_fare_for_commission_ufx * $Fare_data[0]['fCommision']) / 100), 2);
    $result['FareOfMinutes'] = $Minute_Fare;
    $result['FareOfDistance'] = $Distance_Fare;
    $result['FareOfCommision'] = $Commision_Fare;

    // $result['iBaseFare'] = $iBaseFare;
    $result['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
    $result['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
    $result['fCommision'] = $Fare_data[0]['fCommision'];
    $result['FinalFare'] = $total_fare;
    $result['FinalFare_UFX_Commission'] = $total_fare_for_commission_ufx;
    $result['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
    $result['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
    $result['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
    $result['iMinFare'] = $Fare_data[0]['iMinFare'];
    return $result;
}

function calculateFare($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $couponCode = "", $tripId, $fMaterialFee = 0, $fMiscFee = 0, $fDriverDiscount = 0) {
    global $generalobj, $obj;
    $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);

    // $defaultCurrency = ($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $sql = "select iDriverId,fPickUpPrice,fNightPrice,iQty,eFareType,eFlatTrip,fFlatTripPrice,fVisitFee,fTollPrice,eTollSkipped from trips where iTripId='" . $tripId . "'";
    $data_trips = $obj->MySQLSelect($sql);
    $fPickUpPrice = $data_trips[0]['fPickUpPrice'];
    $fNightPrice = $data_trips[0]['fNightPrice'];
    $iQty = $data_trips[0]['iQty'];
    $eFareType = $data_trips[0]['eFareType'];
    $eFlatTrip = $data_trips[0]['eFlatTrip'];
    $fFlatTripPrice = $data_trips[0]['fFlatTripPrice'];
    $iDriverId = $data_trips[0]['iDriverId'];
    /* if($eFlatTrip == "No"){
      $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
      }else{
      $surgePrice = 1;
      } */
    $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
    $fVisitFee = $data_trips[0]['fVisitFee'];
    $tripTimeInMinutes = ($totalTimeInMinutes_trip != '') ? $totalTimeInMinutes_trip : 0;
    $fPricePerKM = getVehicleCountryUnit_PricePerKm($vehicleTypeID, $Fare_data[0]['fPricePerKM']);
    $fTollPrice = $data_trips[0]['fTollPrice'];
    $eTollSkipped = $data_trips[0]['eTollSkipped'];
    //$TaxArr = getMemberCountryTax($iUserId, "Passenger"); // Commented By HJ On 18-09-2019 Replace Of Below Line
    $TaxArr = getMemberCountryTax($iDriverId, "Driver"); // Added By HJ Get Driver Country Tax As Per Discuss With KS Sir On 18-09-2019
    $fTax1 = $TaxArr['fTax1'];
    $fTax2 = $TaxArr['fTax2'];
    if ($eTollSkipped == "Yes") {
        $fTollPrice = 0;
    }

    $Fare_data[0]['TripTimeMinutes'] = $tripTimeInMinutes;
    $Fare_data[0]['TripDistance'] = $tripDistance;
    $Fare_data[0]['eFlatTrip'] = $eFlatTrip;
    $Fare_data[0]['fFlatTripPrice'] = $fFlatTripPrice;
    $Fare_data[0]['iTripId'] = $tripId;
    $Fare_data[0]['eFareType'] = $eFareType;
    $Fare_data[0]['iQty'] = $iQty;
    $Fare_data[0]['fVisitFee'] = $fVisitFee;
    $Fare_data[0]['fMaterialFee'] = $fMaterialFee;
    $Fare_data[0]['fMiscFee'] = $fMiscFee;
    $Fare_data[0]['fDriverDiscount'] = $fDriverDiscount;
    $Fare_data[0]['fPricePerKM'] = $fPricePerKM;
    $result = getTripFare($Fare_data, "1");

    // $resultArr_Orig = getTripFare($Fare_data,"1");
    $total_fare = $result['FinalFare'];
    $fTripGenerateFare = $result['FinalFare'];

    // $fTripGenerateFare_For_Commission = $result['FinalFare'];
    $fTripGenerateFare_For_Commission = $result['FinalFare_UFX_Commission'];
    $fSurgePriceDiff = round(($fTripGenerateFare * $surgePrice) - $fTripGenerateFare, 2);
    $total_fare = $total_fare + $fSurgePriceDiff;
    $fTripGenerateFare = $fTripGenerateFare + $fSurgePriceDiff;
    $iMinFare = $result['iMinFare'];
    if ($eFlatTrip == "No") {
        if ($iMinFare > $fTripGenerateFare) {
            $MinFareDiff = $iMinFare - $total_fare;
            $total_fare = $iMinFare;
            $fTripGenerateFare = $iMinFare;
            $fTripGenerateFare_For_Commission = $iMinFare;
        } else {
            $MinFareDiff = "0";
            $fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission + $fSurgePriceDiff;
        }
    } else {
        $fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission + $fSurgePriceDiff;
    }

    /* Tax Calculation */
    $result['fTax1'] = 0;
    $result['fTax2'] = 0;
    if ($fTax1 > 0) {
        $fTaxAmount1 = round((($fTripGenerateFare * $fTax1) / 100), 2);
        $fTripGenerateFare = $fTripGenerateFare + $fTaxAmount1;
        $total_fare = $total_fare + $fTaxAmount1;
        $result['fTax1'] = $fTaxAmount1;
    }

    if ($fTax2 > 0) {
        $total_fare_new = $fTripGenerateFare - $fTaxAmount1;
        $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
        $fTripGenerateFare = $fTripGenerateFare + $fTaxAmount2;
        $total_fare = $total_fare + $fTaxAmount2;
        $result['fTax2'] = $fTaxAmount2;
    }

    /* Tax Calculation */
    if ($fTollPrice > 0) {
        $total_fare = $total_fare + $fTollPrice;
        $fTripGenerateFare = $fTripGenerateFare + $fTollPrice;
    }

    // $result['fCommision'] = round((($fTripGenerateFare * $Fare_data[0]['fCommision']) / 100), 2);
    // $fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission+$fSurgePriceDiff;
    $result['fCommision'] = round((($fTripGenerateFare_For_Commission * $Fare_data[0]['fCommision']) / 100), 2);
    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data Start
        $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eStatus='Active'");
        if (count($getCouponCode) > 0) {
            $discountValue = $getCouponCode[0]['fDiscount'];
            $discountValueType = $getCouponCode[0]['eType'];
        }
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data End
        //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
        //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($total_fare * $discountValue), 1) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $total_fare) {
                $vDiscount = round($total_fare, 1) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
            }
        }

        $fare = $total_fare - $discountValue;
        if ($fare < 0) {
            $fare = 0;
            $discountValue = $total_fare;
        }

        $total_fare = $fare;
        $Fare_data[0]['fDiscount'] = $discountValue;
        $Fare_data[0]['vDiscount'] = $vDiscount;
    }

    /* Check Coupon Code Total Fare  End */
    /* Check debit wallet For Count Total Fare  Start */
    $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
    $user_wallet_debit_amount = 0;
    if ($total_fare > $user_available_balance) {
        $total_fare = $total_fare - $user_available_balance;
        $user_wallet_debit_amount = $user_available_balance;
    } else {
        $user_wallet_debit_amount = $total_fare;
        $total_fare = 0;
    }

    // Update User Wallet
    if ($user_wallet_debit_amount > 0) {
        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $tripId, '', 'true');
        $data_wallet['iUserId'] = $iUserId;
        $data_wallet['eUserType'] = "Rider";
        $data_wallet['iBalance'] = $user_wallet_debit_amount;
        $data_wallet['eType'] = "Debit";
        $data_wallet['dDate'] = date("Y-m-d H:i:s");
        $data_wallet['iTripId'] = $tripId;
        $data_wallet['eFor'] = "Booking";
        $data_wallet['ePaymentStatus'] = "Unsettelled";
        $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING_DL#" . " " . $vRideNo;
        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);

        // $obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
    }

    /* Check debit wallet For Count Total Fare  End */
    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = 0;
    } else {
        $Fare_data[0]['iBaseFare'] = $result['iBaseFare'];
    }

    $finalFareData['total_fare'] = $total_fare;
    $finalFareData['iBaseFare'] = $result['iBaseFare'];
    $finalFareData['fPricePerMin'] = $result['FareOfMinutes'];
    $finalFareData['fPricePerKM'] = $result['FareOfDistance'];

    // $finalFareData['fCommision'] = $result['FareOfCommision'];
    // $finalFareData['fCommision'] = round((($fTripGenerateFare*$result['fCommision'])/100),2);
    $finalFareData['fCommision'] = $result['fCommision'];
    $finalFareData['fDiscount'] = $Fare_data[0]['fDiscount'];
    $finalFareData['vDiscount'] = $Fare_data[0]['vDiscount'];
    $finalFareData['MinFareDiff'] = $MinFareDiff;
    $finalFareData['fSurgePriceDiff'] = $fSurgePriceDiff;
    $finalFareData['user_wallet_debit_amount'] = $user_wallet_debit_amount;
    $finalFareData['fTripGenerateFare'] = $fTripGenerateFare;
    $finalFareData['SurgePriceFactor'] = $surgePrice;
    $finalFareData['fTax1'] = $result['fTax1'];
    $finalFareData['fTax2'] = $result['fTax2'];
    return $finalFareData;
}

function calculateFareEstimate($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $surgePrice = 1) {
    global $generalobj, $obj;
    $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);

    // $defaultCurrency = ($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    if ($surgePrice > 1) {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $surgePrice;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $surgePrice;
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $surgePrice;
        $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $surgePrice;
    }

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'];
        $Fare_data[0]['fPricePerMin'] = 0;
        $Fare_data[0]['fPricePerKM'] = 0;
    }

    $resultArr = $generalobj->getFinalFare($Fare_data[0]['iBaseFare'], $Fare_data[0]['fPricePerMin'], $totalTimeInMinutes_trip, $Fare_data[0]['fPricePerKM'], $tripDistance, $Fare_data[0]['fCommision'], $priceRatio, $defaultCurrency, $startDate, $endDate);
    $resultArr['FinalFare'] = $resultArr['FinalFare'] - $resultArr['FareOfCommision']; // Temporary set: Remove addition of commision from above function
    $Fare_data[0]['total_fare'] = $resultArr['FinalFare'];
    if ($Fare_data[0]['iMinFare'] > $Fare_data[0]['total_fare']) {
        $Fare_data[0]['MinFareDiff'] = $Fare_data[0]['iMinFare'] - $Fare_data[0]['total_fare'];
        $Fare_data[0]['total_fare'] = $Fare_data[0]['iMinFare'];
    } else {
        $Fare_data[0]['MinFareDiff'] = "0";
    }

    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = 0;
    } else {
        $Fare_data[0]['iBaseFare'] = $resultArr['iBaseFare'];
    }

    $Fare_data[0]['fPricePerMin'] = $resultArr['FareOfMinutes'];
    $Fare_data[0]['fPricePerKM'] = $resultArr['FareOfDistance'];
    $Fare_data[0]['fCommision'] = $resultArr['FareOfCommision'];
    return $Fare_data;
}

function calculateFareEstimateAll($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $couponCode = "", $surgePrice = 1, $fMaterialFee = 0, $fMiscFee = 0, $fDriverDiscount = 0, $DisplySingleVehicleFare = "", $eUserType = "Passenger", $iQty = 1, $SelectedCarTypeID = "", $isDestinationAdded = "Yes", $eFlatTrip = "No", $fFlatTripPrice = 0, $sourceLocationArr = array(), $destinationLocationArr = array()) {

    global $generalobj, $obj, $tconfig, $APPLY_SURGE_ON_FLAT_FARE, $iServiceId;

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    if ($eUserType == "Passenger") {
        $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
        $userlangcode = get_value("register_user", "vLang", "iUserId", $iUserId, '', 'true');
        $eUnit = getMemberCountryUnit($iUserId, "Passenger");
        $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    } else {
        $vCurrencyPassenger = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iUserId, '', 'true');
        $userlangcode = get_value("register_driver", "vLang", "iDriverId", $iUserId, '', 'true');
        $eUnit = getMemberCountryUnit($iUserId, "Driver");
        $TaxArr = getMemberCountryTax($iUserId, "Driver");
    }

    if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }

    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $vSymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    // $eUnit = getMemberCountryUnit($iUserId,"Passenger");
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1", $iServiceId);
    if ($DisplySingleVehicleFare == "") {
        $ssql = "";
        if ($SelectedCarTypeID != "") {
            $ssql .= " AND iVehicleTypeId IN ($SelectedCarTypeID) ";
        }

        $sql_vehicle_type = "SELECT * FROM vehicle_type WHERE 1 " . $ssql;
        $Fare_data = $obj->MySQLSelect($sql_vehicle_type);
        $result = array();
        for ($i = 0; $i < count($Fare_data); $i++) {
            $fPickUpPrice = 1;
            $fNightPrice = 1;
            $data_surgePrice = checkSurgePrice($Fare_data[$i]['iVehicleTypeId'], "");
            if ($data_surgePrice['Action'] == "0") {
                if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                    $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
                } else {
                    $fNightPrice = $data_surgePrice['SurgePriceValue'];
                }
            }

            $Fare_data[$i]['TripTimeMinutes'] = $totalTimeInMinutes_trip;
            $Fare_data[$i]['TripDistance'] = $tripDistance;

            // $result = getTripFare($Fare_data[$i], $surgePrice);

            /** calculate fare * */
            $Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['iBaseFare'];
            $Fare_data[$i]['fPricePerMin'] = $Fare_data[$i]['fPricePerMin'];
            $Fare_data[$i]['fPricePerKM'] = getVehicleCountryUnit_PricePerKm($Fare_data[$i]['iVehicleTypeId'], $Fare_data[$i]['fPricePerKM']);
            $Fare_data[$i]['fPricePerKM'] = $Fare_data[$i]['fPricePerKM'];
            $Fare_data[$i]['iMinFare'] = $Fare_data[$i]['iMinFare'];
            $iBaseFare = $Fare_data[$i]['iBaseFare'];
            $fPricePerKM = $Fare_data[$i]['fPricePerKM'];
            $fPricePerMin = $Fare_data[$i]['fPricePerMin'];
            if ($Fare_data[$i]['eFareType'] == 'Fixed') {
                $Fare_data[$i]['fPricePerMin'] = 0;
                $Fare_data[$i]['fPricePerKM'] = 0;

                // $Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['fFixedFare'] * $Fare_data[$i]['iQty'];
                $Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['fFixedFare'] * $iQty;
            } else if ($Fare_data[$i]['eFareType'] == 'Hourly') {
                $Fare_data[$i]['iBaseFare'] = 0;
                $Fare_data[$i]['fPricePerKM'] = 0;
                $totalHour = $Fare_data[$i]['TripTimeMinutes'] / 60;
                $Fare_data[$i]['TripTimeMinutes'] = $totalHour;
                $Fare_data[$i]['fPricePerMin'] = $Fare_data[$i]['fPricePerHour'];
            }

            $Minute_Fare = round(($fPricePerMin * $totalTimeInMinutes_trip) * $priceRatio, 2);
            $Distance_Fare = round(($fPricePerKM * $tripDistance) * $priceRatio, 2);
            $iBaseFare = round($iBaseFare * $priceRatio, 2);
            $fMaterialFee = round($Fare_data[$i]['fMaterialFee'] * $priceRatio, 2);
            $fMiscFee = round($Fare_data[$i]['fMiscFee'] * $priceRatio, 2);
            $fDriverDiscount = round($Fare_data[$i]['fDriverDiscount'] * $priceRatio, 2);
            $fVisitFee = round($Fare_data[$i]['fVisitFee'] * $priceRatio, 2);
            if ($isDestinationAdded == "Yes") {
                $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $Fare_data[$i]['iVehicleTypeId']);
                $eFlatTrip = $data_flattrip['eFlatTrip'];
                $fFlatTripPrice = $data_flattrip['Flatfare'];
            } else {
                $eFlatTrip = "No";
                $fFlatTripPrice = 0;
            }

            $Fare_data[$i]['eFlatTrip'] = $eFlatTrip;
            $Fare_data[$i]['fFlatTripPrice'] = $fFlatTripPrice;
            if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
                $fPickUpPrice = 1;
                $fNightPrice = 1;
            }

            $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
            if ($eFlatTrip == "No") {
                $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;
                $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
                $SurgePriceFactor = strval($surgePrice);
                $total_fare = $total_fare + $fSurgePriceDiff;
                $minimamfare = round($Fare_data[$i]['iMinFare'] * $priceRatio, 2);
                if ($minimamfare > $total_fare) {
                    $fMinFareDiff = $minimamfare - $total_fare;
                    $total_fare = $minimamfare;
                    $Fare_data[$i]['FinalFare'] = $total_fare;
                } else {
                    $fMinFareDiff = 0;
                }
            } else {
                $total_fare = round($fFlatTripPrice * $priceRatio, 2);
                $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
                $SurgePriceFactor = strval($surgePrice);
                $total_fare = $total_fare + $fSurgePriceDiff;
                $Fare_data[$i]['FinalFare'] = $total_fare;
                $fMinFareDiff = 0;
            }

            $Commision_Fare = round((($total_fare * $Fare_data[$i]['fCommision']) / 100), 2);
            /* Tax Calculation */
            $fTax1 = $TaxArr['fTax1'];
            $fTax2 = $TaxArr['fTax2'];
            if ($fTax1 > 0) {
                $fTaxAmount1 = round((($total_fare * $fTax1) / 100), 2);
                $total_fare = $total_fare + $fTaxAmount1;
                $Fare_data[$i]['fTax1'] = $vSymbol . " " . number_format($fTaxAmount1, 2);
            }

            if ($fTax2 > 0) {
                $total_fare_new = $total_fare - $fTaxAmount1;
                $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
                $total_fare = $total_fare + $fTaxAmount2;
                $Fare_data[$i]['fTax1'] = $vSymbol . " " . number_format($fTaxAmount2, 2);
            }

            /* Tax Calculation */
            $discountValue = 0;
            $discountValueType = "cash";
            if ($couponCode != "") {
                //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data Start
                $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eStatus='Active'");
                if (count($getCouponCode) > 0) {
                    $discountValue = $getCouponCode[0]['fDiscount'];
                    $discountValueType = $getCouponCode[0]['eType'];
                }
                //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data End
                //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
                //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
                if ($discountValueType == "percentage") {
                    $vDiscount = round($discountValue, 1) . ' ' . "%";
                    $discountValue = round(($total_fare * $discountValue), 1) / 100;
                } else {
                    $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                    if ($discountValue > $total_fare) {
                        $vDiscount = round($total_fare, 1) . ' ' . $curr_sym;
                    } else {
                        $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
                    }
                }
                $total_fare = $total_fare - $discountValue;
                $Fare_data[0]['fDiscount_fixed'] = $discountValue;
                if ($total_fare < 0) {
                    $total_fare = 0;
                    // $discountValue = $total_fare;
                }

                if ($Fare_data[0]['eFareType'] == "Regular") {
                    $Fare_data[0]['fDiscount'] = $discountValue;
                    $Fare_data[0]['vDiscount'] = $vDiscount;
                } else {
                    $Fare_data[0]['fDiscount'] = $Fare_data[0]['fDiscount_fixed'];
                    $Fare_data[0]['vDiscount'] = $vDiscount;
                }
            }

            /** calculate fare * */
            $Fare_data[$i]['FareOfMinutes'] = $Minute_Fare;
            $Fare_data[$i]['FareOfDistance'] = $Distance_Fare;
            $Fare_data[$i]['FareOfCommision'] = $Commision_Fare;
            $Fare_data[$i]['fPricePerMin'] = $Fare_data[$i]['fPricePerMin'];
            $Fare_data[$i]['fPricePerKM'] = $Fare_data[$i]['fPricePerKM'];
            $Fare_data[$i]['fCommision'] = $Fare_data[$i]['fCommision'];
            $Fare_data[$i]['FinalFare'] = $total_fare;
            $Fare_data[$i]['iBaseFare'] = ($Fare_data[$i]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
            $Fare_data[$i]['iMinFare'] = round($Fare_data[$i]['iMinFare'] * $priceRatio, 2);
            if ($Fare_data[$i]['eFareType'] == "Regular") {

                // $Fare_data[$i]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
                $Fare_data[$i]['total_fare'] = $vSymbol . " " . number_format($total_fare, 2);
            } else {
                $Fare_data[$i]['total_fare'] = $vSymbol . " " . number_format($Fare_data[$i]['FinalFare'], 2);
            }

            $Fare_data[$i]['iBaseFare'] = $vSymbol . " " . number_format($Fare_data[$i]['iBaseFare'], 2);
            $Fare_data[$i]['fPricePerMin'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fPricePerMin'] * $priceRatio, 1), 2);
            $Fare_data[$i]['fPricePerKM'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fPricePerKM'] * $priceRatio, 1), 2);
            $Fare_data[$i]['fCommision'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fCommision'] * $priceRatio, 1), 2);
        }
    } else {
        $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);
        $fPickUpPrice = 1;
        $fNightPrice = 1;
        $data_surgePrice = checkSurgePrice($Fare_data[0]['iVehicleTypeId'], "");
        if ($data_surgePrice['Action'] == "0") {
            if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
            } else {
                $fNightPrice = $data_surgePrice['SurgePriceValue'];
            }
        }

        if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
            $fPickUpPrice = 1;
            $fNightPrice = 1;
        }

        $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
        $Fare_data[0]['TripTimeMinutes'] = $totalTimeInMinutes_trip;
        $Fare_data[0]['TripDistance'] = $tripDistance;

        // $result = getTripFare($Fare_data[0], $surgePrice);

        /** calculate fare * */
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'];
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
        $Fare_data[0]['fPricePerKM'] = getVehicleCountryUnit_PricePerKm($Fare_data[0]['iVehicleTypeId'], $Fare_data[0]['fPricePerKM']);
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
        $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'];
        $iBaseFare = $Fare_data[0]['iBaseFare'];
        $fPricePerKM = $Fare_data[0]['fPricePerKM'];
        $fPricePerMin = $Fare_data[0]['fPricePerMin'];
        if ($Fare_data[0]['eFareType'] == 'Fixed') {
            $Fare_data[0]['fPricePerMin'] = 0;
            $Fare_data[0]['fPricePerKM'] = 0;

            // $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'] * $Fare_data[0]['iQty'];
            $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'] * $iQty;
        } else if ($Fare_data[0]['eFareType'] == 'Hourly') {
            $Fare_data[0]['iBaseFare'] = 0;
            $Fare_data[0]['fPricePerKM'] = 0;
            $totalHour = $Fare_data[0]['TripTimeMinutes'] / 60;
            $Fare_data[0]['TripTimeMinutes'] = $totalHour;
            $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerHour'];
        }

        $Minute_Fare = round(($fPricePerMin * $totalTimeInMinutes_trip) * $priceRatio, 2);
        $Distance_Fare = round(($fPricePerKM * $tripDistance) * $priceRatio, 2);
        $iBaseFare = round($iBaseFare * $priceRatio, 2);
        $fMaterialFee = round($Fare_data[0]['fMaterialFee'] * $priceRatio, 2);
        $fMiscFee = round($Fare_data[0]['fMiscFee'] * $priceRatio, 2);
        $fDriverDiscount = round($Fare_data[0]['fDriverDiscount'] * $priceRatio, 2);
        $fVisitFee = round($Fare_data[0]['fVisitFee'] * $priceRatio, 2);
        if ($eFlatTrip == "No") {
            $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;
            $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
            $SurgePriceFactor = strval($surgePrice);
            $total_fare = $total_fare + $fSurgePriceDiff;
            $minimamfare = round($Fare_data[0]['iMinFare'] * $priceRatio, 2);
            if ($minimamfare > $total_fare) {
                $fMinFareDiff = $minimamfare - $total_fare;
                $total_fare = $minimamfare;
                $Fare_data[0]['FinalFare'] = $total_fare;
            } else {
                $fMinFareDiff = 0;
            }
        } else {
            $total_fare = round($fFlatTripPrice * $priceRatio, 2);
            $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
            $SurgePriceFactor = strval($surgePrice);
            $total_fare = $total_fare + $fSurgePriceDiff;
            $Fare_data[0]['FinalFare'] = $total_fare;
            $fMinFareDiff = 0;
            $Minute_Fare = 0;
            $Distance_Fare = 0;
        }

        $Commision_Fare = round((($total_fare * $Fare_data[0]['fCommision']) / 100), 2);
        /* Tax Calculation */
        $fTax1 = $TaxArr['fTax1'];
        $fTax2 = $TaxArr['fTax2'];
        if ($fTax1 > 0) {
            $fTaxAmount1 = round((($total_fare * $fTax1) / 100), 2);
            $total_fare = $total_fare + $fTaxAmount1;
            $Fare_data[0]['fTax1'] = $vSymbol . " " . number_format($fTaxAmount1, 2);
        }

        if ($fTax2 > 0) {
            $total_fare_new = $total_fare - $fTaxAmount1;
            $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
            $total_fare = $total_fare + $fTaxAmount2;
            $Fare_data[0]['fTax2'] = $vSymbol . " " . number_format($fTaxAmount2, 2);
        }

        /* Tax Calculation */

        ## Calculate for Discount ##
        // $fSurgePriceDiff = $farewithsurcharge - $minimamfare;
        $discountValue = 0;
        $discountValueType = "cash";
        if ($couponCode != "") {
            //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data Start
            $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eStatus='Active'");
            if (count($getCouponCode) > 0) {
                $discountValue = $getCouponCode[0]['fDiscount'];
                $discountValueType = $getCouponCode[0]['eType'];
            }
            //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data End
            //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
            //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
            if ($discountValueType == "percentage") {
                $vDiscount = round($discountValue, 1) . ' ' . "%";
                $discountValue = round(($total_fare * $discountValue), 1) / 100;
            } else {
                $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                if ($discountValue > $total_fare) {
                    $vDiscount = round($total_fare, 1) . ' ' . $curr_sym;
                } else {
                    $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
                }
            }

            $total_fare = $total_fare - $discountValue;
            $Fare_data[0]['fDiscount_fixed'] = $discountValue;
            if ($total_fare < 0) {
                $total_fare = 0;

                // $discountValue = $total_fare;
            }

            if ($Fare_data[0]['eFareType'] == "Regular") {
                $Fare_data[0]['fDiscount'] = $discountValue;
                $Fare_data[0]['vDiscount'] = $vDiscount;
            } else {
                $Fare_data[0]['fDiscount'] = $Fare_data[0]['fDiscount_fixed'];
                $Fare_data[0]['vDiscount'] = $vDiscount;
            }
        }

        ## Calculate for Discount ##

        /** calculate fare * */
        $Fare_data[0]['FareOfMinutes'] = $Minute_Fare;
        $Fare_data[0]['FareOfDistance'] = $Distance_Fare;
        $Fare_data[0]['FareOfCommision'] = $Commision_Fare;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
        $Fare_data[0]['fCommision'] = $Fare_data[0]['fCommision'];
        $Fare_data[0]['FinalFare'] = $total_fare;
        $Fare_data[0]['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
        $Fare_data[0]['iMinFare'] = round($Fare_data[0]['iMinFare'] * $priceRatio, 2);
        if ($Fare_data[0]['eFareType'] == "Regular") {

            // $Fare_data[0]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
            $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format($total_fare, 2);
        } else {
            $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format($Fare_data[0]['FinalFare'], 2);
        }

        $Fare_data[0]['iBaseFare'] = $vSymbol . " " . number_format($Fare_data[0]['iBaseFare'], 2);
        $Fare_data[0]['fPricePerMin'] = $vSymbol . " " . number_format(round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1), 2);
        $Fare_data[0]['fPricePerKM'] = $vSymbol . " " . number_format(round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1), 2);
        $Fare_data[0]['fCommision'] = $vSymbol . " " . number_format(round($Fare_data[0]['fCommision'] * $priceRatio, 1), 2);
        $vVehicleType = get_value('vehicle_type', "vVehicleType_" . $userlangcode, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $vVehicleTypeLogo = get_value('vehicle_type', "vLogo", 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $vVehicleCategoryData = get_value($sql_vehicle_category_table_name, 'vLogo,vCategory_' . $userlangcode . ' as vCategory', 'iVehicleCategoryId', $iVehicleCategoryId);
        $Fare_data[0]['vVehicleCategory'] = $vVehicleCategoryData[0]['vCategory'];
        $vVehicleFare = get_value('vehicle_type', 'fFixedFare', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $eType = $Fare_data[0]['eFareType'];
        $tripFareDetailsArr = array();

        // echo "<pre>"; print_r($Fare_data); die;
        if ($eFlatTrip == "Yes") {
            $i = 0;
            $displayfare = round($fFlatTripPrice * $priceRatio, 2);
            $displayfare = $vSymbol . " " . number_format($displayfare, 2);
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_FLAT_TRIP_FARE_TXT']] = $displayfare;
            $i++;
            if ($fSurgePriceDiff > 0) {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $vSymbol . " " . formatNum($fSurgePriceDiff);
                $i++;
            }

            if ($vDiscount > 0) {
                $farediscount = $vSymbol . " " . formatNum($Fare_data[0]['fDiscount']);
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = "- " . $farediscount;
                $i++;
            }

            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $Fare_data[0]['total_fare'];
            $Fare_data = $tripFareDetailsArr;
        } else {
            $i = 0;
            $countUfx = 0;
            if ($eType == "UberX") {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $Fare_data[0]['vVehicleCategory'] . "-" . $vVehicleType;
                $countUfx = 1;
            }

            if ($eType == "Regular") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vSymbol . " " . formatNum($iBaseFare);
                if ($countUfx == 1) {
                    $i++;
                }

                if ($eUnit == "Miles") {
                    $tripDistanceDisplay = $tripDistance * 0.621371;
                    $tripDistanceDisplay = round($tripDistanceDisplay, 2);

                    // $DisplayDistanceTxt = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
                    $LBL_MILE_DISTANCE_TXT = ($tripDistanceDisplay > 1) ? $languageLabelsArr['LBL_MILE_DISTANCE_TXT'] : $languageLabelsArr['LBL_ONE_MILE_TXT'];
                    $DisplayDistanceTxt = $LBL_MILE_DISTANCE_TXT;
                } else {
                    $tripDistanceDisplay = $tripDistance;

                    // $DisplayDistanceTxt = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
                    $LBL_KM_DISTANCE_TXT = ($tripDistanceDisplay > 1) ? $languageLabelsArr['LBL_DISPLAY_KMS'] : $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
                    $DisplayDistanceTxt = $LBL_KM_DISTANCE_TXT;
                }

                $tripDistanceDisplay = formatNum($tripDistanceDisplay);
                if ($isDestinationAdded == "Yes") {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $tripDistanceDisplay . " " . $DisplayDistanceTxt . ")"] = $vSymbol . " " . formatNum($Fare_data[0]['FareOfDistance']);
                } else {
                    $priceperkm = getVehiclePrice_ByUSerCountry($iUserId, $fPricePerKM);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT']] = $vSymbol . " " . formatNum($priceperkm) . "/" . strtolower($DisplayDistanceTxt);
                }

                $i++;

                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $totalTimeInMinutes_trip . ")"] = $vSymbol . formatNum($Fare_data[0]['FareOfMinutes']);
                $hours = floor($totalTimeInMinutes_trip / 60); // No. of mins/60 to get the hours and round down
                $mins = $totalTimeInMinutes_trip % 60; // No. of mins/60 - remainder (modulus) is the minutes
                $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
                $LBL_MINUTES_TXT = ($mins > 1) ? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
                if ($hours >= 1) {
                    $tripDurationDisplay = $hours . " " . $LBL_HOURS_TXT . ", " . $mins . " " . $LBL_MINUTES_TXT;
                } else {
                    $tripDurationDisplay = $totalTimeInMinutes_trip . " " . $LBL_MINUTES_TXT;
                }

                if ($isDestinationAdded == "Yes") {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $tripDurationDisplay . ")"] = $vSymbol . " " . formatNum($Fare_data[0]['FareOfMinutes']);
                } else {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT']] = $vSymbol . " " . formatNum($fPricePerMin) . "/" . $languageLabelsArr['LBL_MIN_SMALL_TXT'];
                }

                $i++;
            } else if ($eType == "Fixed") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = ($Fare_data[0]['iQty'] > 1) ? $Fare_data[0]['iQty'] . ' X ' . $vSymbol . " " . $vVehicleFare : $vSymbol . " " . $vVehicleFare;
                if ($countUfx == 1) {
                    $i++;
                }

                $total_fare = $vVehicleFare + $Fare_data[0]['fVisitFee'] - $Fare_data[0]['fDiscount_fixed'];
                $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format(round($total_fare * $priceRatio, 1), 2);
            } else if ($eType == "Hourly") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $totalTimeInMinutes_trip . ")"] = $vSymbol . " " . $Fare_data[0]['FareOfMinutes'];
                if ($countUfx == 1) {
                    $i++;
                }
            }

            $fVisitFee = $Fare_data[0]['fVisitFee'];
            if ($fVisitFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_VISIT_FEE']] = $vSymbol . " " . $fVisitFee;
                $i++;
            }

            if ($fMaterialFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MATERIAL_FEE']] = $vSymbol . " " . $fMaterialFee;
                $i++;
            }

            if ($fMiscFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MISC_FEE']] = $vSymbol . " " . $fMiscFee;
                $i++;
            }

            if ($fMinFareDiff > 0 && $isDestinationAdded == "Yes") {

                // $minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[$i + 1][$vSymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = $vSymbol . " " . formatNum($fMinFareDiff);
                $Fare_data[0]['TotalMinFare'] = $minimamfare;
                $i++;
            }

            if ($fSurgePriceDiff > 0) {
                if ($isDestinationAdded == "Yes") {
                    $normalfare = $total_fare - $fSurgePriceDiff + $vDiscount - $fTaxAmount1 - $fTaxAmount2;

                    // $normalfare = formatNum($normalfare * $priceRatio);
                    $normalfare = formatNum($normalfare);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = $vSymbol . " " . $normalfare;
                    $i++;
                }

                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $vSymbol." ".formatNum($fSurgePriceDiff * $priceRatio);
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $vSymbol . " " . formatNum($fSurgePriceDiff);
                $i++;
            }

            if ($fDriverDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = "- " . $vSymbol . " " . $fDriverDiscount;
                $i++;
            }

            if ($vDiscount > 0) {

                // $farediscount = $vSymbol." ".number_format(round($Fare_data[0]['fDiscount'] * $priceRatio,1),2);
                $farediscount = $vSymbol . " " . formatNum($Fare_data[0]['fDiscount']);

                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = "- " . $vSymbol . $Fare_data[0]['fDiscount'];
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = "- " . $farediscount;
                $i++;
            }

            if ($fTax1 > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fTax1 . " % "] = $Fare_data[0]['fTax1'];
                $i++;
            }

            if ($fTax2 > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fTax2 . " % "] = $Fare_data[0]['fTax2'];
                $i++;
            }

            if ($isDestinationAdded == "Yes") {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $Fare_data[0]['total_fare'];
            }

            // $Fare_data = array_merge($Fare_data[0], $tripFareDetailsArr);
            $Fare_data = $tripFareDetailsArr;
        }
    }

    return $Fare_data;
}

function getLanguageLabelsArr($lCode = '', $directValue = "", $iServiceId = "") {
    global $obj, $APP_TYPE, $vSystemDefaultLangCode;
    /* find default language of website set by admin */
    if (empty($vSystemDefaultLangCode)) {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);
        $vSystemDefaultLangCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }

    if (empty($lCode)) {
        $lCode = $vSystemDefaultLangCode;
    }
    // $lCode = "AR";
    $ssql_serviceLabels = "";
    if (strtoupper(DELIVERALL) == "YES" && $iServiceId > 0) {
        $ssql_serviceLabels = " UNION SELECT  `vLabel` , `vValue`, `vCode`  FROM  `language_label_" . $iServiceId . "` WHERE  `vCode` IN('" . $lCode . "', 'EN')";
    }
    $sql = "SELECT  `vLabel` , `vValue`, `vCode`  FROM  `language_label` WHERE  `vCode` IN('" . $lCode . "', 'EN') UNION SELECT `vLabel` , `vValue`, `vCode`  FROM  `language_label_other` WHERE  `vCode` IN('" . $lCode . "', 'EN') " . $ssql_serviceLabels;
    $all_label = $obj->MySQLSelect($sql);
    // echo "<PRE>";print_r($all_label);exit;
    $x = array();
    for ($i = 0; $i < count($all_label); $i++) {
        if ($all_label[$i]['vCode'] == "EN") {
            $vLabel = $all_label[$i]['vLabel'];
            $vValue = $all_label[$i]['vValue'];
            $x[$vLabel] = $vValue;
        }
    }

    for ($i = 0; $i < count($all_label); $i++) {
        if ($all_label[$i]['vCode'] == $lCode && !empty($all_label[$i]['vValue'])) {
            $vLabel = $all_label[$i]['vLabel'];
            $vValue = $all_label[$i]['vValue'];
            $x[$vLabel] = $vValue;
        }
    }

    $x['vCode'] = $lCode; // to check in which languge code it is loading
    // echo "<PRE>";print_r($x);exit;
    if ($directValue == "") {
        $returnArr['Action'] = "1";
        $returnArr['LanguageLabels'] = $x;
        return $returnArr;
    } else {
        return $x;
    }
}

/* function getLanguageLabelsArr($lCode = '', $directValue = "", $iServiceId = "") {
  global $obj;
  //find default language of website set by admin
  $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
  $default_label = $obj->MySQLSelect($sql);
  if ($lCode == '') {
  $lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
  }

  if (empty($iServiceId)) {
  $iServiceId = $_REQUEST["iServiceId"];
  }

  $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label`  WHERE  `vCode` = '" . $lCode . "' ";
  $all_language_label = $obj->MySQLSelect($sql);
  $x = array();
  for ($i = 0; $i < count($all_language_label); $i++) {
  $vLabel = $all_language_label[$i]['vLabel'];
  $vValue = $all_language_label[$i]['vValue'];
  $x[$vLabel] = $vValue;
  }

  // Check English labels
  $sql_en = "SELECT  `vLabel` , `vValue`  FROM  `language_label` WHERE  `vCode` = 'EN'";
  $all_label_en = $obj->MySQLSelect($sql_en);

  if (count($all_label_en) > 0) {
  for ($i = 0; $i < count($all_label_en); $i++) {
  $vLabel_tmp = $all_label_en[$i]['vLabel'];
  $vValue_tmp = $all_label_en[$i]['vValue'];

  if (isset($x[$vLabel_tmp]) || array_key_exists($vLabel_tmp, $x)) {
  if ($x[$vLabel_tmp] == "") {
  $x[$vLabel_tmp] = $vValue_tmp;
  }
  } else {
  $x[$vLabel_tmp] = $vValue_tmp;
  }
  }
  }

  // $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_".$iServiceId."` WHERE  `vCode` = '" . $lCode . "' UNION SELECT `vLabel` , `vValue`  FROM  `language_label_other` WHERE  `vCode` = '" . $lCode . "' ";
  $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_" . $iServiceId . "` WHERE  `vCode` = '" . $lCode . "'";
  $all_label = $obj->MySQLSelect($sql);
  for ($i = 0; $i < count($all_label); $i++) {
  $vLabel = $all_label[$i]['vLabel'];
  $vValue = $all_label[$i]['vValue'];
  $x[$vLabel] = $vValue;
  }

  // Check English labels
  $sql_en = "SELECT  `vLabel` , `vValue`  FROM  `language_label_" . $iServiceId . "` WHERE  `vCode` = 'EN'";
  $all_label_en = $obj->MySQLSelect($sql_en);

  if (count($all_label_en) > 0) {
  for ($i = 0; $i < count($all_label_en); $i++) {
  $vLabel_tmp = $all_label_en[$i]['vLabel'];
  $vValue_tmp = $all_label_en[$i]['vValue'];

  if (isset($x[$vLabel_tmp]) || array_key_exists($vLabel_tmp, $x)) {
  if ($x[$vLabel_tmp] == "") {
  $x[$vLabel_tmp] = $vValue_tmp;
  }
  } else {
  $x[$vLabel_tmp] = $vValue_tmp;
  }
  }
  }

  // $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_other`  WHERE  `vCode` = '" . $lCode . "' ";
  // $all_label = $obj->MySQLSelect($sql);
  // for ($i = 0; $i < count($all_label); $i++) {
  // $vLabel = $all_label[$i]['vLabel'];
  // $vValue = $all_label[$i]['vValue'];
  // $x[$vLabel] = $vValue;
  // }
  $x['vCode'] = $lCode; // to check in which languge code it is loading
  if ($directValue == "") {
  $returnArr['Action'] = "1";
  $returnArr['LanguageLabels'] = $x;
  return $returnArr;
  } else {
  return $x;
  }
  } */

function sendApplePushNotification($PassengerToDriver = 0, $deviceTokens, $message, $alertMsg, $filterMsg, $fromDepart = '') {

    // global $generalobj, $obj, $IPHONE_PEM_FILE_PASSPHRASE,$APP_MODE,$ENABLE_PUBNUB, $PARTNER_APP_IPHONE_PEM_FILE_NAME, $PASSENGER_APP_IPHONE_PEM_FILE_NAME;
    global $generalobj, $obj, $APP_MODE_TEMP_WEB;

    $sql = "select vValue,vName from configurations where vName in('IPHONE_PEM_FILE_PASSPHRASE','APP_MODE','ENABLE_PUBNUB','PARTNER_APP_IPHONE_PEM_FILE_NAME','PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PARTNER_APP_IPHONE_PEM_FILE_NAME','COMPANY_APP_IPHONE_PEM_FILE_NAME','PRO_COMPANY_APP_IPHONE_PEM_FILE_NAME','PRO_PASSENGER_DL_APP_IPHONE_PEM_FILE_NAME','PASSENGER_DL_APP_IPHONE_PEM_FILE_NAME')";
    $Data_config = $obj->MySQLSelect($sql);
    for ($i = 0; $i < count($Data_config); $i++) {
        $temp_val = $Data_config[$i]['vValue'];
        $temp_vName = $Data_config[$i]['vName'];
        $$temp_vName = trim($temp_val);
    }

    if ($message == "") {
        return "";
    }
    //Added By HJ On 09-08-2019 For Set Apple Push Notification Sound As Per Choosen From Admin Panel Start
    $dataArr = array();
    $getSoundData = getCustomeNotificationSound($dataArr);
    $notificationSound = "default";
    if ($PassengerToDriver == 1) {
        $notificationSound = $getSoundData['PROVIDER_NOTIFICATION'];
    } else if ($PassengerToDriver == 2) {
        $notificationSound = $getSoundData['STORE_NOTIFICATION'];
    } else {
        $notificationSound = $getSoundData['USER_NOTIFICATION'];
    }
    $explodeData = explode("_", $notificationSound);
    if (count($explodeData) > 1) {
        $notificationSound = $explodeData[1];
    }
    if (trim($notificationSound) == "") {
        $notificationSound = "default";
    }
    //Added By HJ On 09-08-2019 For Set Apple Push Notification Sound As Per Choosen From Admin Panel End
    //echo "<pre>";print_r($notificationSound);die;
    $passphrase = $IPHONE_PEM_FILE_PASSPHRASE;

    // $APP_MODE = $APP_MODE;
    // $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
    $APP_MODE_NOTIFICATION = $APP_MODE;
    if (!empty($APP_MODE_TEMP_WEB) && $APP_MODE_TEMP_WEB != "" && !is_null($APP_MODE_TEMP_WEB)) {
        $APP_MODE_NOTIFICATION = $APP_MODE_TEMP_WEB;
    }
    $prefix = "";
    $url_apns = 'ssl://gateway.sandbox.push.apple.com:2195';
    if ($APP_MODE_NOTIFICATION == "Production") {
        $prefix = "PRO_";
        $url_apns = 'ssl://gateway.push.apple.com:2195';
    }

    if ($PassengerToDriver == 1) {

        // $name = $generalobj->getConfigurations("configurations", $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME");    // send notification to driver
        $name1 = $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else if ($PassengerToDriver == 2) {

        // $name = $generalobj->getConfigurations("configurations", $prefix . "COMPANY_APP_IPHONE_PEM_FILE_NAME");    // send notification to company
        $name1 = $prefix . "COMPANY_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else {

        // $name = $generalobj->getConfigurations("configurations", $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME");  // send notification to passenger
        $name1 = $prefix . "PASSENGER_DL_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    }

    $ctx = stream_context_create();
    if ($fromDepart == 'admin') {
        $name = '../' . $name;
    }

    stream_context_set_option($ctx, 'ssl', 'local_cert', $name);
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    $fp = stream_socket_client($url_apns, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    /* 		 echo "deviceTokens => <pre>";
      print_r($deviceTokens);
      echo "<pre>"; print_r($fp); die; */

    if (!$fp) {

        if ($ENABLE_PUBNUB == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_SERVER_COMM_ERROR";
            $returnArr['ERROR'] = $err . $errstr . " " . PHP_EOL;
            setDataResponse($returnArr);

            // exit("Failed to connect: $err $errstr" . PHP_EOL);
        }
    }

    // Create the payload body
    if (is_array($alertMsg)) {
        for ($device = 0; $device < count($deviceTokens); $device++) {
            $body['aps'] = array(
                'alert' => $alertMsg[$device],
                'content-available' => 1,
                'body' => $message[$device],
                'sound' => $notificationSound
            );
            // Build the binary notification
            // Encode the payload as JSON
            $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
            //        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
            if ($filterMsg == 1) {
                $payload = stripslashes(preg_replace("/[\n\r]/", "", $payload));
            }
            if ($deviceTokens[$device] == "simulator_demo_1234") {
                continue;
            }
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokens[$device]) . pack('n', strlen($payload)) . $payload;
            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
        }
    } else {
        $body['aps'] = array(
            'alert' => $alertMsg,
            'content-available' => 1,
            'body' => $message,
            'sound' => $notificationSound
        );

        // Encode the payload as JSON
        $payload = json_encode($body, JSON_UNESCAPED_UNICODE);

        //        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
        if ($filterMsg == 1) {
            $payload = stripslashes(preg_replace("/[\n\r]/", "", $payload));
        }

        for ($device = 0; $device < count($deviceTokens); $device++) {

            // Build the binary notification
            if ($deviceTokens[$device] == "simulator_demo_1234") {
                continue;
            }
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokens[$device]) . pack('n', strlen($payload)) . $payload;

            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
        }
    }

    // Close the connection to the server
    fclose($fp);
}

function getOnlineDriverArr($sourceLat, $sourceLon, $address_data = array(), $DropOff = "No", $From_Autoassign = "No", $Check_Driver_UFX = "No", $Check_Date_Time = "", $destLat = "", $destLon = "", $iUserId = "") {
    global $generalobj, $obj, $RESTRICTION_KM_NEAREST_TAXI, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $LIST_DRIVER_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $COMMISION_DEDUCT_ENABLE, $WALLET_MIN_BALANCE, $RESTRICTION_KM_NEAREST_TAXI, $APP_TYPE, $vTimeZone, $intervalmins;
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $LIST_DRIVER_LIMIT_BY_DISTANCE = $From_Autoassign == "Yes" ? $LIST_RESTAURANT_LIMIT_BY_DISTANCE : $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLocationUpdateDate";
    $eDriverType = "All";
    if (isset($address_data['eDriverType'])) {
        $eDriverType = $address_data['eDriverType'];
    }
    if (isset($address_data['iCompanyId'])) {
        $iCompanyId = $address_data['iCompanyId'];
    }

    $sourceLocationArr = array($sourceLat, $sourceLon);
    $destinationLocationArr = array($destLat, $destLon);
    $ssql_available = "";
    $allowed_ans = $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';
    if ($Check_Driver_UFX == "No") {
        $ssql_available .= " AND vAvailability = 'Available' AND vTripStatus != 'Active' AND tLocationUpdateDate > '$str_date' ";
    }
    if (strtoupper($eDriverType) == "SITE") {
        $ssql_available .= " AND iCompanyId!='" . $iCompanyId . "'";
    } else if (strtoupper($eDriverType) == "PERSONAL") {
        $ssql_available .= " AND iCompanyId='" . $iCompanyId . "'";
    }
    $ssql_demo_driver_available = " ";
    ## Include Demo User's Driver ##
    if (SITE_TYPE == "Demo" && $iUserId != "") {
        $uemail = get_value('register_user', 'vEmail', 'iUserId', $iUserId, '', 'true');
        $uemail = explode("-", $uemail);
        $uemail = $uemail[1];
        if ($uemail != "") {
            $ssql_demo_driver_available .= " OR vEmail = '" . $uemail . "' ";
        }
    }
    ## Include Demo User's Driver ##

    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
        $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") )
		* cos( radians( ROUND(" . $vLatitude . ",8) ) )
			* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
			+ sin( radians(" . $sourceLat . ") )
			* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
			WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active' AND eIsBlocked = 'No')
			HAVING ( distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " $ssql_demo_driver_available ) ORDER BY `register_driver`.`" . $param . "` ASC";
        //echo $sql;die;
        $Data = $obj->MySQLSelect($sql);
        //echo "<pre>";print_r($Data);die;
        $newData = array();
        $j = 0;
        $driver_id_auto = "";
        $sql = "select GROUP_CONCAT(iVehicleTypeId)as VehicleTypeId from `vehicle_type` where eType = 'DeliverAll'";
        $db_deliverall_vehicle = $obj->MySQLSelect($sql);
        for ($i = 0; $i < count($Data); $i++) {
            $VehicleTypeId = $db_deliverall_vehicle[0]['VehicleTypeId'];
            $VehicleTypeIdArr = explode(",", $VehicleTypeId);
            $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
            $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
            $drivercartypeArr = explode(",", $vCarType);
            $vCarTypeArr = array_intersect($VehicleTypeIdArr, $drivercartypeArr);
            $vCarTypeArr = array_values($vCarTypeArr);
            $vCarType = $vCarTypeArr[0];
            $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');
            $Data[$i]['DeliveryVehicleType'] = $vCarType;
            $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon, "K");
            $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];
            if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
                $user_available_balance = $generalobj->get_user_available_balance($Data[$i]['iDriverId'], "Driver");
                //echo $WALLET_MIN_BALANCE."<==>".$user_available_balance;die;
                if ($WALLET_MIN_BALANCE > $user_available_balance) {
                    $Data[$i]['ACCEPT_CASH_TRIPS'] = "No";
                } else {
                    $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
                }
            } else {
                $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
            }
//print_r($Data);
            // if($fRadius > $distanceusercompany){
            if (($fRadius > $distanceusercompany) && $vCarType > 0) {
                $driver_id_auto .= $Data[$i]['iDriverId'] . ",";
                $newData[$j] = $Data[$i];
                $j++;
            }
        }

        $driver_id_auto = substr($driver_id_auto, 0, -1);
        //echo "<pre>";print_r($newData);die;
        // $returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = $driver_id_auto;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = "";
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }

    return $returnData;
}

function getNearRestaurantArr_old($sourceLat, $sourceLon, $iUserId, $fOfferType = "No", $searchword = "", $vAddress = "", $iServiceId = '') {
    global $generalobj, $obj, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $intervalmins, $ENABLE_FAVORITE_STORE_MODULE;
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    $sourceLocationArr = array($sourceLat, $sourceLon);
    $allowed_ans = checkAllowedAreaNew($sourceLocationArr, "No");
    $ssql = $having_ssql = $regDateCondition = "";
    if ($fOfferType == "Yes") {
        $ssql .= " AND ( company.fOfferType = 'Flat' OR company.fOfferType = 'Percentage' )";
    }

    if (SITE_TYPE == "Demo" && $searchword == "") {
        $having_ssql .= " OR company.eDemoDisplay = 'Yes'";
        // $ResCountry = ($vUserDeviceCountry == "IN")?"('IN')":"('IN','".$vUserDeviceCountry."')";
        // $ssql .=  "AND ( eDemoDisplay = 'Yes' OR eLock = 'No' )";
        if ($vAddress != "") {
            //$ssql .= " AND ( company.vRestuarantLocation like '%$vAddress%' OR company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        } else {
            //$ssql .= " AND ( company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        }
        $regDate = date("Y-m-d H:i:s");
        $regDate = strtotime(date("Y-m-d H:i:s", strtotime($regDate)) . "-1 months");
        $regDate = date("Y-m-d H:i:s", $regDate);
        //$regDateCondition = " AND tRegistrationDate >= '".$regDate."'";
    }

    $ssql_fav_q = "";

    if (checkFavStoreModule() && !empty($iUserId)) {
        $ssql_fav_q = getFavSelectQuery('', $iUserId);
        $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") ) 
        * cos( radians( vRestuarantLocationLat ) ) 
            * cos( radians( vRestuarantLocationLong ) - radians(" . $sourceLon . ") ) 
            + sin( radians(" . $sourceLat . ") ) 
            * sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $ssql_fav_q . " FROM `company`
            WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' AND iServiceId = '" . $iServiceId . "' $ssql
            HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") $regDateCondition ORDER BY `company`.`iCompanyId` ASC";
        $filterquery = getFavFilterCondition($sql);
    }

    if ($allowed_ans == 'Yes') {
        if (isset($filterquery) && !empty($filterquery)) {
            $sql = $filterquery;
        } else {
            $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") )
		* cos( radians( vRestuarantLocationLat ) )
			* cos( radians( vRestuarantLocationLong ) - radians(" . $sourceLon . ") ) 
			+ sin( radians(" . $sourceLat . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $ssql_fav_q . " FROM `company`
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' AND iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") $regDateCondition ORDER BY `company`.`iCompanyId` ASC";
        }
        $Data = $obj->MySQLSelect($sql);
        //echo "<pre>";print_R($Data);die;
        if (count($Data) > 0) {
            for ($i = 0; $i < count($Data); $i++) {
                $vAvgRating = $Data[$i]['vAvgRating'];
                $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
                $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
                $restaurant_status_arr = calculate_restaurant_time_span($Data[$i]['iCompanyId'], $iUserId);
                $Data[$i]['Restaurant_Status'] = $restaurant_status_arr['status'];
                $Data[$i]['Restaurant_Opentime'] = $restaurant_status_arr['opentime'];
                $Data[$i]['Restaurant_Closetime'] = $restaurant_status_arr['closetime'];
                $Data[$i]['restaurantstatus'] = $restaurant_status_arr['restaurantstatus']; // closed or open
                $Data[$i]['timeslotavailable'] = $restaurant_status_arr['timeslotavailable'];
                $CompanyDetailsArr = getCompanyDetails($Data[$i]['iCompanyId'], $iUserId, "No", "");
                $Data[$i]['Restaurant_Cuisine'] = $CompanyDetailsArr['Restaurant_Cuisine'];
                $Data[$i]['Restaurant_Cuisine_Id'] = $CompanyDetailsArr['Restaurant_Cuisine_Id'];
                if ($iServiceId == '1') {
                    $Data[$i]['Restaurant_PricePerPerson'] = $CompanyDetailsArr['Restaurant_PricePerPerson'];
                } else {
                    $Data[$i]['Restaurant_PricePerPerson'] = '';
                }
                $Data[$i]['Restaurant_OrderPrepareTime'] = $CompanyDetailsArr['Restaurant_OrderPrepareTime'];
                $Data[$i]['Restaurant_OfferMessage'] = $CompanyDetailsArr['Restaurant_OfferMessage'];
                $Data[$i]['Restaurant_OfferMessage_short'] = $CompanyDetailsArr['Restaurant_OfferMessage_short'];
                $Data[$i]['Restaurant_MinOrderValue'] = $CompanyDetailsArr['Restaurant_MinOrderValue'];
                $Data[$i]['Restaurant_MinOrderValue_Orig'] = $CompanyDetailsArr['Restaurant_MinOrderValue_Orig'];
                // $Data[$i]['CompanyFoodData'] =  $CompanyDetailsArr['CompanyFoodData'];
                $Data[$i]['CompanyFoodDataCount'] = $CompanyDetailsArr['CompanyFoodDataCount'];
                $Data[$i]['CompanyFoodData'] = array();
            }
        }
        return $Data;
    } else {
        $Data = array();
        return $Data;
    }
}

//Added By HJ On 04-11-2019 For Optimized Speed When Load Store Start
function getNearRestaurantArr($sourceLat, $sourceLon, $iUserId, $fOfferType = "No", $searchword = "", $vAddress = "", $iServiceId = '') {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    global $generalobj, $obj, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $intervalmins, $ENABLE_FAVORITE_STORE_MODULE, $tconfig;
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $currencycode = isset($_REQUEST["vGeneralCurrency"]) ? $_REQUEST["vGeneralCurrency"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Curren cy Code
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    $sourceLocationArr = array($sourceLat, $sourceLon);
    $allowed_ans = checkAllowedAreaNew($sourceLocationArr, "No");
    $ssql = $having_ssql = "";
    if ($fOfferType == "Yes") {
        $ssql .= " AND ( company.fOfferType = 'Flat' OR company.fOfferType = 'Percentage' ) AND company.fOfferAppyType != 'None'";
    }
    if (SITE_TYPE == "Demo" && $searchword == "") {
        $having_ssql .= " OR company.eDemoDisplay = 'Yes'";
        if ($vAddress != "") {
            //$ssql .= " AND ( company.vRestuarantLocation like '%$vAddress%' OR company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        } else {
            //$ssql .= " AND ( company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        }
    }
    $vLanguage = "EN";
    $ssql_fav_q = "";
    $priceRatio = 1;
    if (checkFavStoreModule() && !empty($iUserId)) {
        $ssql_fav_q = getFavSelectQuery('', $iUserId);

        $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") ) 
        * cos( radians( vRestuarantLocationLat ) ) 
            * cos( radians( vRestuarantLocationLong ) - radians(" . $sourceLon . ") ) 
            + sin( radians(" . $sourceLat . ") ) 
            * sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $ssql_fav_q . " FROM `company`
            WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' AND iServiceId = '" . $iServiceId . "' $ssql
            HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . " ) ORDER BY `company`.`iCompanyId` ASC";
        $filterquery = getFavFilterCondition($sql);
    }
    if ($iUserId != "" && $iUserId > 0) {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $priceRatio = $passengerData[0]['Ratio'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
    } else {
        //Added By HJ On 23-01-2020 For Get Currency Data Start
        if (($currencySymbol == "" || $currencySymbol == NULL) && $currencycode != "") {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE vName = '" . $currencycode . "'");
        } else {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'");
        }
        if (count($currencyData) > 0) {
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $priceRatio = $currencyData[0]['Ratio'];
        } else {
            $currencycode = "USD";
            $currencySymbol = "$";
            $priceRatio = 1.0000;
        }
        //Added By HJ On 23-01-2020 For Get Currency Data End
    }
    if ($allowed_ans == 'Yes') {
        $currencyArr = $currencySymbolArr = array();
        $getCurrencyRation = $obj->MySQLSelect("SELECT Ratio,iCurrencyId,vSymbol,vName FROM currency WHERE eStatus='Active'");
        for ($c = 0; $c < count($getCurrencyRation); $c++) {
            $currencyArr[$getCurrencyRation[$c]['vName']] = $getCurrencyRation[$c]['Ratio'];
            $currencySymbolArr[$getCurrencyRation[$c]['vName']] = $getCurrencyRation[$c]['vSymbol'];
        }
        if (isset($filterquery) && !empty($filterquery)) {
            $sql = $filterquery;
        } else {
            $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") )
		* cos( radians( vRestuarantLocationLat ) )
			* cos( radians( vRestuarantLocationLong ) - radians(" . $sourceLon . ") ) 
			+ sin( radians(" . $sourceLat . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $ssql_fav_q . " FROM `company`
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' AND iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
        }
        $Data = $obj->MySQLSelect($sql);
        //echo "<pre>";print_R($Data);die;
        $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
        $iToLocationId = GetUserGeoLocationId($sourceLocationArr);
        $LBL_PER_PERSON_TXT = $languageLabelsArr['LBL_PER_PERSON_TXT'];
        $LBL_MIN_ORDER_TXT = $languageLabelsArr['LBL_MIN_ORDER_TXT'];
        $LBL_NO_MIN_ORDER_TXT = $languageLabelsArr['LBL_NO_MIN_ORDER_TXT'];
        if (count($Data) > 0) {
            //Added By HJ On 09-05-2019 For Optimized Code start
            $storeIdArr = $favStoreArr = $storeBasicData = array();
            for ($c = 0; $c < count($Data); $c++) {
                $storeIdArr[] = $Data[$c]['iCompanyId'];
            }
            if (count($storeIdArr) > 0) {
                $storeIds = implode(",", $storeIdArr);
                $getStoreBasicData = $obj->MySQLSelect("SELECT timeslotavailable,iCompanyId,eStatus,vCountry,vFromSatSunTimeSlot1,vFromSatSunTimeSlot2,vToSatSunTimeSlot1,vToSatSunTimeSlot2,vFromMonFriTimeSlot1,vFromMonFriTimeSlot2,vToMonFriTimeSlot1,vToMonFriTimeSlot2,eAvailable,eLogout FROM `company` WHERE iCompanyId IN ($storeIds)");
                //echo "<pre>";print_r($getStoreBasicData);die;
                for ($g = 0; $g < count($getStoreBasicData); $g++) {
                    $storeBasicData[$getStoreBasicData[$g]['iCompanyId']][] = $getStoreBasicData[$g];
                }
            }
            //echo "<pre>";print_r($storeBasicData);die;
            $storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageLabelsArr);
            //Added By HJ On 09-05-2019 For Optimized Code End
            //echo "<pre>";print_r($storeDetails);die;
            for ($i = 0; $i < count($Data); $i++) {
                //echo "<pre>";print_r($Data[$i]);die;
                $iCompanyId = $Data[$i]['iCompanyId'];
                $vAvgRating = $Data[$i]['vAvgRating'];
                /* if (isset($Data[$i]['vCurrencyCompany']) && $Data[$i]['vCurrencyCompany'] != '') {
                  $currencycode = $Data[$i]['vCurrencyCompany'];
                  }
                  //if (isset($currencyArr[$currencycode]) && $priceRatio == "") { // Commented By HJ On 23-01-2020 For Solved Company Currency Issue
                  if (isset($currencyArr[$currencycode])) { // Added By HJ On 23-01-2020 For Solved Company Currency Issue
                  $priceRatio = $currencyArr[$currencycode];
                  $currencySymbol = $currencySymbolArr[$currencycode];
                  } */
                $Data[$i]['fMinOrderValue'] = $generalobj->setTwoDecimalPoint($Data[$i]['fMinOrderValue'] * $priceRatio);
                $Data[$i]['fPackingCharge'] = $generalobj->setTwoDecimalPoint($Data[$i]['fPackingCharge'] * $priceRatio);
                $Data[$i]['fTargetAmt'] = $generalobj->setTwoDecimalPoint($Data[$i]['fTargetAmt'] * $priceRatio);
                $Data[$i]['fOfferAmt'] = $generalobj->setTwoDecimalPoint($Data[$i]['fOfferAmt'] * $priceRatio);
                $Data[$i]['fMaxOfferAmt'] = $generalobj->setTwoDecimalPoint($Data[$i]['fMaxOfferAmt'] * $priceRatio);
                $Data[$i]['fPricePerPerson'] = $generalobj->setTwoDecimalPoint($Data[$i]['fPricePerPerson'] * $priceRatio);
                $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
                $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
                $restaurant_status_arr = calculate_restaurant_time_span($Data[$i]['iCompanyId'], $iUserId, $vLanguage, $languageLabelsArr, $storeBasicData);
                //echo "<pre>";print_r($restaurant_status_arr['timeslotavailable']);exit;
                $Data[$i]['Restaurant_Status'] = "Closed";
                $Data[$i]['Restaurant_Opentime'] = $Data[$i]['Restaurant_Closetime'] = $Data[$i]['Restaurant_Cuisine_Id'] = "";
                $Data[$i]['restaurantstatus'] = "closed"; // closed or open
                $Data[$i]['timeslotavailable'] = "No";
                if (isset($restaurant_status_arr['timeslotavailable'])) {
                    $Data[$i]['timeslotavailable'] = $restaurant_status_arr['timeslotavailable'];
                }
                if (isset($restaurant_status_arr['status'])) {
                    $Data[$i]['Restaurant_Status'] = $restaurant_status_arr['status'];
                    $Data[$i]['Restaurant_Opentime'] = $restaurant_status_arr['opentime'];
                    $Data[$i]['Restaurant_Closetime'] = $restaurant_status_arr['closetime'];
                    $Data[$i]['restaurantstatus'] = $restaurant_status_arr['restaurantstatus']; // closed or open
                }
                //$CompanyDetailsArr = getCompanyDetails($Data[$i]['iCompanyId'], $iUserId, "No", ""); // Commented By HJ On 09-05-2019 For Optimized Code
                //echo "<pre>";print_r($CompanyDetailsArr);die;
                $Data[$i]['Restaurant_Cuisine'] = $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = "";
                $Data[$i]['Restaurant_OrderPrepareTime'] = "0 mins";
                $Data[$i]['restaurantstatus'] = $restaurantstatus = "Closed";
                if (isset($storeDetails['companyCuisineArr'][$iCompanyId])) {
                    $Data[$i]['Restaurant_Cuisine'] = implode(", ", $storeDetails['companyCuisineArr'][$iCompanyId]);
                }
                if (isset($storeDetails['companyCuisineIdArr'][$iCompanyId])) {
                    $Data[$i]['Restaurant_Cuisine_Id'] = implode(",", $storeDetails['companyCuisineIdArr'][$iCompanyId]);
                }
                if (isset($storeDetails['restaurantPrepareTime'][$iCompanyId])) {
                    $Data[$i]['Restaurant_OrderPrepareTime'] = $storeDetails['restaurantPrepareTime'][$iCompanyId];
                }
                if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])) {
                    $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
                }
                if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])) {
                    $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
                }
                if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])) {
                    $Data[$i]['restaurantstatus'] = $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
                }
                $fPricePerPerson = $fMinOrderValue = 0;
                if (isset($storeDetails['restaurantPricePerPerson'][$iCompanyId])) {
                    $fPricePerPerson = $storeDetails['restaurantPricePerPerson'][$iCompanyId];
                }
                if (isset($storeDetails['restaurantMinOrdValue'][$iCompanyId])) {
                    $fMinOrderValue = $storeDetails['restaurantMinOrdValue'][$iCompanyId];
                }
                $Data[$i]['Restaurant_OfferMessage'] = $Restaurant_OfferMessage;
                $Data[$i]['Restaurant_OfferMessage_short'] = $Restaurant_OfferMessage_short;
                //$fPricePerPerson = $generalobj->setTwoDecimalPoint($fPricePerPerson * $priceRatio);
                $fPricePerPerson = $generalobj->setTwoDecimalPoint($fPricePerPerson);
                if ($iServiceId == 1) {
                    $Data[$i]['Restaurant_PricePerPerson'] = $currencySymbol . "" . $fPricePerPerson . " " . $LBL_PER_PERSON_TXT;
                } else {
                    $Data[$i]['Restaurant_PricePerPerson'] = '';
                }
                //$fMinOrderValue = $generalobj->setTwoDecimalPoint($fMinOrderValue * $priceRatio);
                $fMinOrderValue = $generalobj->setTwoDecimalPoint($fMinOrderValue);
                $Data[$i]['fMinOrderValueDisplay'] = $currencySymbol . " " . $fMinOrderValue;
                $Data[$i]['fMinOrderValue'] = $fMinOrderValue;
                $Data[$i]['Restaurant_MinOrderValue'] = ($fMinOrderValue > 0) ? $currencySymbol . $fMinOrderValue . " " . $LBL_MIN_ORDER_TXT : $LBL_NO_MIN_ORDER_TXT;
                $Data[$i]['Restaurant_MinOrderValue_Orig'] = ($fMinOrderValue > 0) ? $currencySymbol . $fMinOrderValue : $LBL_NO_MIN_ORDER_TXT;
                $Data[$i]['CompanyFoodDataCount'] = isset($CompanyDetailsArr['CompanyFoodDataCount']) ? $CompanyDetailsArr['CompanyFoodDataCount'] : '1'; // Remain
                $Data[$i]['CompanyFoodData'] = array();

                $vGeneralLang = !empty($_REQUEST['vGeneralLang']) ? $_REQUEST['vGeneralLang'] : "EN";
                $Data[$i]['Restaurant_Safety_Status'] = (!empty($Data[$i]['eSafetyPractices']) && ($Data[$i]['iServiceId'] == 1 || $Data[$i]['iServiceId'] == 2)) ? $Data[$i]['eSafetyPractices'] : "No";
                $safetyimg = "/webimages/icons/DefaultImg/ic_safety.png";
                $Data[$i]['Restaurant_Safety_Icon'] = (file_exists($tconfig["tpanel_path"] . $safetyimg)) ? $tconfig["tsite_url"] . $safetyimg : "";
                $time = time();
                $Data[$i]['Restaurant_Safety_URL'] = $tconfig["tsite_url"] . "safety-measures?time_data=" . $time . "&fromlang=" . $vGeneralLang;
            }
        }
        if (isset($_REQUEST['test'])) {
            //echo "<pre>";print_r($Data);die;
        }
        //echo "<pre>";print_r($Data);die;
        return $Data;
    } else {
        $Data = array();
        return $Data;
    }
}

//Added By HJ On 04-11-2019 For Optimized Speed When Load Store End
function checkSurgePrice($vehicleTypeID, $selectedDateTime = "", $iRentalPackageId = "0") {
    global $obj, $ENABLE_SURGE_CHARGE_RENTAL, $vTimeZone;
    if ($iRentalPackageId == "" || $iRentalPackageId == NULL) {
        $iRentalPackageId = 0;
    }
    $getVehicleData = $obj->MySQLSelect("SELECT ePickStatus,eNightStatus,tNightSurgeData FROM vehicle_type WHERE iVehicleTypeId='" . $vehicleTypeID . "'");
    $ePickStatus = $eNightStatus = "Active";
    $tNightSurgeData_PrevDay = "";
    if (count($getVehicleData) > 0) {
        $ePickStatus = $getVehicleData[0]['ePickStatus'];
        $eNightStatus = $getVehicleData[0]['eNightStatus'];
        $tNightSurgeData_PrevDay = $getVehicleData[0]['tNightSurgeData'];
    }
    //$ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    //$eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $fPickUpPrice = $fNightPrice = 1;
    if ($selectedDateTime == "") {
        $selectedTime = @date("Y-m-d H:i:s");
        $systemTimeZone = date_default_timezone_get();
        $currentDateTime = converToTz($selectedTime, $vTimeZone, $systemTimeZone);
        $currentTime = @date('H:i:s', strtotime($currentDateTime));
        $currentDay = @date('D', strtotime($currentDateTime));
        $PreviousDayDate = @date('Y-m-d', strtotime('-1 day'));
        $PreviousDay = @date('D', strtotime($PreviousDayDate));
        // $currentTime = @date("H:i:s");
        // $currentDay = @date("D");
    } else {
        // $currentTime = $selectedDateTime;
        $PreviousDayDate = @date('Y-m-d', strtotime($selectedDateTime . '-1 day'));
        $PreviousDay = @date('D', strtotime($PreviousDayDate));
        $currentTime = @date("H:i:s", strtotime($selectedDateTime));
        $currentDay = @date("D", strtotime($selectedDateTime));
    }
    ## Checking For Previous Day NightSurge Charge For 0-5 am ##
    if ($currentTime > "00:00:00" && $currentTime <= "05:00:00" && $eNightStatus == "Active" && ($iRentalPackageId == 0 || ($iRentalPackageId != 0 && $ENABLE_SURGE_CHARGE_RENTAL == "Yes"))) {
        $previousnightStartTime_str = "t" . $PreviousDay . "NightStartTime";
        $previousnightEndTime_str = "t" . $PreviousDay . "NightEndTime";
        $fpreviousNightPrice_str = "f" . $PreviousDay . "NightPrice";
        //$tNightSurgeData_PrevDay = get_value('vehicle_type', 'tNightSurgeData', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        //$tNightSurgeDataPrevDayArr = json_decode($tNightSurgeData_PrevDay, true);
        if (count($tNightSurgeDataPrevDayArr) > 0) {
            $nightStartTime_PrevDay = $tNightSurgeDataPrevDayArr[$previousnightStartTime_str];
            $nightEndTime_PrevDay = $tNightSurgeDataPrevDayArr[$previousnightEndTime_str];
            $fNightPrice_PrevDay = $tNightSurgeDataPrevDayArr[$fpreviousNightPrice_str];
            if ($nightStartTime_PrevDay > "00:00:00" && $nightEndTime_PrevDay <= "05:00:00" && $fNightPrice_PrevDay > 1) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
                $returnArr['SurgePrice'] = $fNightPrice_PrevDay . "X";
                $returnArr['SurgePriceValue'] = $fNightPrice_PrevDay;
                return $returnArr;
            }
        }
    }
    $returnArr['Action'] = "1";
    ## Checking For Previous Day NightSurge Charge For 0-5 am ##
    if ($ePickStatus == "Active" || $eNightStatus == "Active") {
        $startTime_str = "t" . $currentDay . "PickStartTime";
        $endTime_str = "t" . $currentDay . "PickEndTime";
        $price_str = "f" . $currentDay . "PickUpPrice";
        $pickStartTime = $pickEndTime = $nightStartTime = $nightEndTime = "00:00:00";
        $getVehData = $obj->MySQLSelect("SELECT $startTime_str,$endTime_str,$price_str FROM vehicle_type WHERE iVehicleTypeId='" . $vehicleTypeID . "'");
        if (count($getVehData) > 0) {
            $pickStartTime = $getVehData[0][$startTime_str];
            $pickEndTime = $getVehData[0][$endTime_str];
            $fPickUpPrice = $getVehData[0][$price_str];
        }
        //$pickStartTime = get_value('vehicle_type', $startTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        //$pickEndTime = get_value('vehicle_type', $endTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        //$fPickUpPrice = get_value('vehicle_type', $price_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        /* $nightStartTime = get_value('vehicle_type', 'tNightStartTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
          $nightEndTime = get_value('vehicle_type', 'tNightEndTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
          $fNightPrice = get_value('vehicle_type', 'fNightPrice', 'iVehicleTypeId', $vehicleTypeID, '', 'true'); */
        $nightStartTime_str = "t" . $currentDay . "NightStartTime";
        $nightEndTime_str = "t" . $currentDay . "NightEndTime";
        $fNightPrice_str = "f" . $currentDay . "NightPrice";
        $tNightSurgeData = get_value('vehicle_type', 'tNightSurgeData', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        $tNightSurgeDataArr = json_decode($tNightSurgeData, true);
        if (count($tNightSurgeDataArr) > 0) {
            $nightStartTime = $tNightSurgeDataArr[$nightStartTime_str];
            $nightEndTime = $tNightSurgeDataArr[$nightEndTime_str];
            $fNightPrice = $tNightSurgeDataArr[$fNightPrice_str];
        }
        $tempNightHour = "12:00:00";
        if ($currentTime > $pickStartTime && $currentTime < $pickEndTime && $ePickStatus == "Active") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PICK_SURGE_NOTE";
            $returnArr['SurgePrice'] = $fPickUpPrice . "X";
            $returnArr['SurgePriceValue'] = $fPickUpPrice;
        }

        // else if ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $eNightStatus == "Active") {
        else if ((($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime > $tempNightHour) || ($currentTime < $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime > $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour)) && $eNightStatus == "Active") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
            $returnArr['SurgePrice'] = $fNightPrice . "X";
            $returnArr['SurgePriceValue'] = $fNightPrice;
        }
    }
    return $returnArr;
}

function checkmemberphoneverification($iMemberId, $user_type = "Passenger") {
    global $obj, $DRIVER_EMAIL_VERIFICATION, $DRIVER_PHONE_VERIFICATION, $RIDER_EMAIL_VERIFICATION, $RIDER_PHONE_VERIFICATION, $COMPANY_EMAIL_VERIFICATION, $COMPANY_PHONE_VERIFICATION;
    if ($user_type == "Driver") {
        $PHONE_VERIFICATION = $DRIVER_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $ePhoneVerified = $driverData[0]['ePhoneVerified'];
    } else if ($user_type == "Company") {
        $PHONE_VERIFICATION = $COMPANY_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM company WHERE iCompanyId = '" . $iMemberId . "'";
        $companyData = $obj->MySQLSelect($sqld);
        $ePhoneVerified = $companyData[0]['ePhoneVerified'];
    } else {
        $PHONE_VERIFICATION = $RIDER_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_user WHERE iUserId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $ePhoneVerified = $driverData[0]['ePhoneVerified'];
    }
    $phone = $PHONE_VERIFICATION == "Yes" ? ($ePhoneVerified == "Yes" ? "true" : "false") : "true";

    if ($phone == "false") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_PHONE_VERIFY";
        setDataResponse($returnArr);
    }
}

function checkmemberemailphoneverification($iMemberId, $user_type = "Passenger") {
    global $obj, $DRIVER_EMAIL_VERIFICATION, $DRIVER_PHONE_VERIFICATION, $RIDER_EMAIL_VERIFICATION, $RIDER_PHONE_VERIFICATION, $COMPANY_EMAIL_VERIFICATION, $COMPANY_PHONE_VERIFICATION;
    if ($user_type == "Driver") {
        /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_EMAIL_VERIFICATION', '', 'true');
          $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_PHONE_VERIFICATION', '', 'true');
          $eEmailVerified = get_value('register_driver', 'eEmailVerified', 'iDriverId', $iMemberId, '', 'true');
          $ePhoneVerified = get_value('register_driver', 'ePhoneVerified', 'iDriverId', $iMemberId, '', 'true'); */
        $EMAIL_VERIFICATION = $DRIVER_EMAIL_VERIFICATION;
        $PHONE_VERIFICATION = $DRIVER_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $eEmailVerified = $driverData[0]['eEmailVerified'];
        $ePhoneVerified = $driverData[0]['ePhoneVerified'];
    } else if ($user_type == "Company") {
        /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_EMAIL_VERIFICATION', '', 'true');
          $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_PHONE_VERIFICATION', '', 'true');
          $eEmailVerified = get_value('register_driver', 'eEmailVerified', 'iDriverId', $iMemberId, '', 'true');
          $ePhoneVerified = get_value('register_driver', 'ePhoneVerified', 'iDriverId', $iMemberId, '', 'true'); */
        $EMAIL_VERIFICATION = $COMPANY_EMAIL_VERIFICATION;
        $PHONE_VERIFICATION = $COMPANY_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM company WHERE iCompanyId = '" . $iMemberId . "'";
        $companyData = $obj->MySQLSelect($sqld);
        $eEmailVerified = $companyData[0]['eEmailVerified'];
        $ePhoneVerified = $companyData[0]['ePhoneVerified'];
    } else {
        /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'RIDER_EMAIL_VERIFICATION', '', 'true');
          $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'RIDER_PHONE_VERIFICATION', '', 'true');
          $eEmailVerified = get_value('register_user', 'eEmailVerified', 'iUserId', $iMemberId, '', 'true');
          $ePhoneVerified = get_value('register_user', 'ePhoneVerified', 'iUserId', $iMemberId, '', 'true'); */
        $EMAIL_VERIFICATION = $RIDER_EMAIL_VERIFICATION;
        $PHONE_VERIFICATION = $RIDER_PHONE_VERIFICATION;
        $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_user WHERE iUserId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        //$eEmailVerified = $driverData[0]['eEmailVerified'];
        $eEmailVerified = "Yes";
        $ePhoneVerified = $driverData[0]['ePhoneVerified'];
    }

    $email = $EMAIL_VERIFICATION == "Yes" ? ($eEmailVerified == "Yes" ? "true" : "false") : "true";
    $phone = $PHONE_VERIFICATION == "Yes" ? ($ePhoneVerified == "Yes" ? "true" : "false") : "true";
    if ($email == "false" && $phone == "false") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_EMAIL_PHONE_VERIFY";
        setDataResponse($returnArr);
    } else if ($email == "true" && $phone == "false") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_PHONE_VERIFY";
        setDataResponse($returnArr);
    } else if ($email == "false" && $phone == "true") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_EMAIL_VERIFY";
        setDataResponse($returnArr);
    }
}

function sendemailphoneverificationcode($iMemberId, $user_type = "Passenger", $VerifyType) {
    global $generalobj, $obj, $iServiceId;
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    } else {
        $tblname = "register_driver";
        $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iDriverId';
        $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }

    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $str = "select * from send_message_templates where vEmail_Code='VERIFICATION_CODE_MESSAGE'";
    $res = $obj->MySQLSelect($str);
    $prefix = $res[0]['vBody_' . $vLangCode];

    // $prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];
    $emailmessage = "";
    $phonemessage = "";
    if ($VerifyType == "email" || $VerifyType == "both") {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);
        $Data_Mail['vEmailVarificationCode'] = $random = substr(number_format(time() * rand(), 0, '', ''), 0, 4);
        $Data_Mail['vEmail'] = isset($db_member[0]['vEmail']) ? $db_member[0]['vEmail'] : '';
        $vFirstName = isset($db_member[0]['vName']) ? $db_member[0]['vName'] : '';
        $vLastName = isset($db_member[0]['vLastName']) ? $db_member[0]['vLastName'] : '';
        $Data_Mail['vName'] = $vFirstName . " " . $vLastName;
        $Data_Mail['CODE'] = $Data_Mail['vEmailVarificationCode'];
        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail) {
            $emailmessage = $Data_Mail['vEmailVarificationCode'];
        } else {
            $emailmessage = "LBL_EMAIL_VERIFICATION_FAILED_TXT";
        }
    }

    if ($VerifyType == "phone" || $VerifyType == "both") {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);
        $mobileNo = $db_member[0]['vPhoneCode'] . $db_member[0]['vPhone'];
        $toMobileNum = "+" . $mobileNo;
        $verificationCode = mt_rand(1000, 9999);
        $message = $prefix . ' ' . $verificationCode;
        $result = sendEmeSms($toMobileNum, $message);
        if ($result == 0) {
            $phonemessage = "LBL_MOBILE_VERIFICATION_FAILED_TXT";
        } else {
            $phonemessage = $verificationCode;
        }
    }

    $returnArr['emailmessage'] = $emailmessage;
    $returnArr['phonemessage'] = $phonemessage;
    return $returnArr;
}

function getTripPriceDetails($iTripId, $iMemberId, $eUserType = "Passenger", $PAGE_MODE = "HISTORY") {
    global $obj, $generalobj, $tconfig, $iServiceId;
    $returnArr = array();
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    if ($eUserType == "Passenger") {
        $tblname = "register_user";
        $vLang = "vLang";
        $iUserId = "iUserId";
        $vCurrency = "vCurrencyPassenger";

        // $currencycode = get_value("trips", $vCurrency, "iTripId", $iTripId, '', 'true');
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $userlangcode = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
    } else {
        $tblname = "register_driver";
        $vLang = "vLang";
        $iUserId = "iDriverId";
        $vCurrency = "vCurrencyDriver";

        // $currencycode = get_value($tblname, $vCurrency, $iUserId, $iMemberId, '', 'true');
        $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $currencycode = $driverData[0]['vCurrencyDriver'];
        $userlangcode = $driverData[0]['vLang'];
        $currencySymbol = $driverData[0]['vSymbol'];
    }

    // $userlangcode = get_value($tblname, $vLang, $iUserId, $iMemberId, '', 'true');
    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1", $iServiceId);
    if ($currencycode == "" || $currencycode == NULL) {
        $sql = "SELECT vName,vSymbol from currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sql);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
    }

    // $sql = "SELECT * from trips WHERE iTripId = '" . $iTripId . "'";
    // $sql = "SELECT tr.*,vt.vVehicleType_".$userlangcode." as vVehicleType,vt.vLogo,vt.iVehicleCategoryId,vt.fFixedFare,vt.eIconType,COALESCE(vc.iParentId, '0') as iParentId,COALESCE(vc.ePriceType, '') as ePriceType,COALESCE(vc.vLogo, '') as vLogoVehicleCategory,COALESCE(vc.vCategory_".$userlangcode.", '') as vCategory from trips as tr LEFT JOIN  vehicle_type as vt ON tr.iVehicleTypeId = vt.iVehicleTypeId  LEFT JOIN ".sql_vehicle_category_table_name." as vc ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE tr.iTripId = '" . $iTripId . "'";
    $sql = "SELECT tr.*,vt.vVehicleType_" . $userlangcode . " as vVehicleType,vt.vLogo,vt.iVehicleCategoryId,vt.fFixedFare,vt.eIconType from trips as tr LEFT JOIN  vehicle_type as vt ON tr.iVehicleTypeId = vt.iVehicleTypeId WHERE tr.iTripId = '" . $iTripId . "'";
    $tripData = $obj->MySQLSelect($sql);
    $priceRatio = $tripData[0]['fRatio_' . $currencycode];
    $iActive = $tripData[0]['iActive'];

    // Convert Into Timezone
    $tripTimeZone = $tripData[0]['vTimeZone'];
    if ($tripTimeZone != "") {
        $serverTimeZone = date_default_timezone_get();
        $tripData[0]['tTripRequestDate'] = converToTz($tripData[0]['tTripRequestDate'], $tripTimeZone, $serverTimeZone);
        $tripData[0]['tDriverArrivedDate'] = converToTz($tripData[0]['tDriverArrivedDate'], $tripTimeZone, $serverTimeZone);
        if ($tripData[0]['tStartDate'] != "0000-00-00 00:00:00") {
            $tripData[0]['tStartDate'] = converToTz($tripData[0]['tStartDate'], $tripTimeZone, $serverTimeZone);
        }

        $tripData[0]['tEndDate'] = converToTz($tripData[0]['tEndDate'], $tripTimeZone, $serverTimeZone);
    }

    // Convert Into Timezone
    $returnArr = array_merge($tripData[0], $returnArr);
    if ($tripData[0]['iUserPetId'] > 0) {
        $petDetails_arr = get_value('user_pets', 'iPetTypeId,vTitle as PetName,vWeight as PetWeight, tBreed as PetBreed, tDescription as PetDescription', 'iUserPetId', $tripData[0]['iUserPetId'], '', '');
    } else {
        $petDetails_arr = array();
    }

    $iPackageTypeId = $tripData[0]['iPackageTypeId'];
    if ($iPackageTypeId != 0) {
        $returnArr['PackageType'] = get_value('package_type', 'vName', 'iPackageTypeId', $iPackageTypeId, '', 'true');
    }

    if (count($petDetails_arr) > 0) {
        $petTypeName = get_value('pet_type', 'vTitle_' . $userlangcode, 'iPetTypeId', $petDetails_arr[0]['iPetTypeId'], '', 'true');
        $returnArr['PetDetails']['PetName'] = $petDetails_arr[0]['PetName'];
        $returnArr['PetDetails']['PetWeight'] = $petDetails_arr[0]['PetWeight'];
        $returnArr['PetDetails']['PetBreed'] = $petDetails_arr[0]['PetBreed'];
        $returnArr['PetDetails']['PetDescription'] = $petDetails_arr[0]['PetDescription'];
        $returnArr['PetDetails']['PetTypeName'] = $petTypeName;
    } else {
        $returnArr['PetDetails']['PetName'] = '';
        $returnArr['PetDetails']['PetWeight'] = '';
        $returnArr['PetDetails']['PetBreed'] = '';
        $returnArr['PetDetails']['PetDescription'] = '';
        $returnArr['PetDetails']['PetTypeName'] = '';
    }

    /* User Wallet Information */
    $returnArr['UserDebitAmount'] = strval($tripData[0]['fWalletDebit']);
    /* User Wallet Information */
    /* $vVehicleType = get_value('vehicle_type', "vVehicleType_" . $userlangcode, 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
      $vVehicleTypeLogo = get_value('vehicle_type', "vLogo", 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
      $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
      $vVehicleCategoryData = get_value($sql_vehicle_category_table_name, 'iParentId,ePriceType,vLogo,vCategory_' . $userlangcode . ' as vCategory', 'iVehicleCategoryId', $iVehicleCategoryId);
      $vVehicleFare = get_value('vehicle_type','fFixedFare', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
      $iParentId = $vVehicleCategoryData[0]['iParentId']; */
    $iCancelReasonId = $tripData[0]['iCancelReasonId'];
    if ($iCancelReasonId > 0) {
        $vCancelReason = get_value('cancel_reason', "vTitle_" . $userlangcode, 'iCancelReasonId', $iCancelReasonId, '', 'true');
        $returnArr['vCancelReason'] = $vCancelReason;
    }
    $vVehicleType = $tripData[0]['vVehicleType'];
    $vVehicleTypeLogo = $tripData[0]['vLogo'];
    $iVehicleCategoryId = $tripData[0]['iVehicleCategoryId'];
    $vVehicleCategoryData[0]['vLogo'] = $tripData[0]['vLogoVehicleCategory'];
    $vVehicleCategoryData[0]['vCategory'] = $tripData[0]['vCategory'];
    $vVehicleFare = $tripData[0]['fFixedFare'];
    $iParentId = $tripData[0]['iParentId'];
    if ($iParentId == 0) {
        $ePriceType = $tripData[0]['ePriceType'];
    } else {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }

    // $eIconType = get_value('vehicle_type', "eIconType", 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
    $eIconType = $tripData[0]['eIconType'];
    $TripTime = date('h:iA', strtotime($tripData[0]['tTripRequestDate']));
    $tTripRequestDateOrig = $tripData[0]['tTripRequestDate'];

    // Convert Into Timezone
    // $tripTimeZone = $tripData[0]['vTimeZone'];
    // if($tripTimeZone != ""){
    // $serverTimeZone = date_default_timezone_get();
    // $tTripRequestDateOrig = converToTz($tTripRequestDateOrig,$tripTimeZone,$serverTimeZone);
    // }
    // Convert Into Timezone
    $tTripRequestDate = date('dS M Y \a\t h:i a', strtotime($tripData[0]['tTripRequestDate']));
    $tStartDate = $tripData[0]['tStartDate'];
    $tEndDate = $tripData[0]['tEndDate'];
    $totalTime = 0;
    if ($tStartDate != '' && $tStartDate != '0000-00-00 00:00:00' && $tEndDate != '' && $tEndDate != '0000-00-00 00:00:00') {
        if ($tripData[0]['eFareType'] == "Hourly") {

            // $hours 		=	0;
            // $minutes 	=	0;
            $totalSec = 0;
            $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$iTripId'";
            $db_tripTimes = $obj->MySQLSelect($sql22);
            foreach ($db_tripTimes as $dtT) {
                if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                    $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
                }
            }

            $years = floor($totalSec / (365 * 60 * 60 * 24));
            $months = floor(($totalSec - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
            $hours = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
            $minuts = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
            $seconds = floor(($totalSec - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minuts * 60));
            if ($days > 0) {
                $hours = ($days * 24) + $hours;
            }

            if ($hours > 0) {
                $totalTime = $hours . ':' . $minuts . ':' . $seconds;
            } else if ($minuts > 0) {
                $totalTime = $minuts . ':' . $seconds . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
            }

            if ($totalTime < 1) {
                $totalTime = $seconds . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
            }
        } else {
            $days = dateDifference($tStartDate, $tEndDate, '%a');
            $hours = dateDifference($tStartDate, $tEndDate, '%h');
            $minutes = dateDifference($tStartDate, $tEndDate, '%i');
            $seconds = dateDifference($tStartDate, $tEndDate, '%s');
            $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
            $LBL_MINUTES_TXT = ($minutes > 1) ? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
            $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);
            if ($days > 0) {
                $hours = ($days * 24) + $hours;
            }

            if ($hours > 0) {

                // $totalTime = $hours * 60;
                // $totalTime = $hours.':'.$minutes.':'.$seconds." " .$languageLabelsArr['LBL_HOUR'] ;
                $totalTime = $hours . ':' . $minutes . ':' . $seconds . " " . $LBL_HOURS_TXT;
            } else if ($minutes > 0) {

                // $totalTime = $totalTime + $minutes;
                // $totalTime = $minutes.':'.$seconds. " " . $languageLabelsArr['LBL_MINUTES_TXT'];
                $totalTime = $minutes . ':' . $seconds . " " . $LBL_MINUTES_TXT;
            }

            // $totalTime = $totalTime . ":" . $seconds . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
            if ($totalTime < 1) {
                $totalTime = $seconds . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
            }
        }
    }

    if ($totalTime == 0) {
        $totalTime = "0.00 " . $languageLabelsArr['LBL_MINUTE'];
    }

    $returnArr['carTypeName'] = $vVehicleType;
    $returnArr['carImageLogo'] = $vVehicleTypeLogo;
    if ($eUserType == "Passenger") {
        $TripRating = get_value('ratings_user_driver', 'vRating1', 'iTripId', $iTripId, ' AND eUserType="Driver"', 'true');
        $returnArr['vDriverImage'] = get_value('register_driver', 'vImage', 'iTripId', $tripData[0]['iDriverId'], '', 'true');

        // $driverDetailArr = get_value('register_driver', '*', 'iDriverId', $tripData[0]['iDriverId']);
        $eUnit = $tripData[0]['vCountryUnitRider'];
    } else {
        $TripRating = get_value('ratings_user_driver', 'vRating1', 'iTripId', $iTripId, ' AND eUserType="Passenger"', 'true');

        // $passgengerDetailArr = get_value('register_user', '*', 'iUserId', $tripData[0]['iUserId']);
        $eUnit = $tripData[0]['vCountryUnitDriver'];

        // $eUnit = $tripData[0]['vCountryUnitRider'];
    }

    if ($eUnit == "Miles") {
        $DisplayDistanceTxt = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
    } else {
        $DisplayDistanceTxt = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
    }

    if ($TripRating == "" || $TripRating == NULL) {
        $TripRating = "0";
    }

    $iFare = $tripData[0]['iFare'];

    // $iFare = $tripData[0]['iFare']+$tripData[0]['fTollPrice'];
    $fPricePerKM = $tripData[0]['fPricePerKM'] * $priceRatio;
    $iBaseFare = $tripData[0]['iBaseFare'] * $priceRatio;
    $fPricePerMin = $tripData[0]['fPricePerMin'] * $priceRatio;
    $fCommision = $tripData[0]['fCommision'];
    $fDistance = $tripData[0]['fDistance'];
    if ($eUnit == "Miles") {
        $fDistance = round($fDistance * 0.621371, 2);
    }

    $vDiscount = $tripData[0]['vDiscount']; // 50 $
    $fDiscount = $tripData[0]['fDiscount']; // 50
    $fMinFareDiff = $tripData[0]['fMinFareDiff'] * $priceRatio;
    $fWalletDebit = $tripData[0]['fWalletDebit'];
    $fSurgePriceDiff = $tripData[0]['fSurgePriceDiff'] * $priceRatio;
    $fTripGenerateFare = $tripData[0]['fTripGenerateFare'] * $priceRatio;
    $fPickUpPrice = $tripData[0]['fPickUpPrice'];
    $fNightPrice = $tripData[0]['fNightPrice'];
    $eFlatTrip = $tripData[0]['eFlatTrip'];
    $fFlatTripPrice = $tripData[0]['fFlatTripPrice'] * $priceRatio;
    $fTipPrice = $tripData[0]['fTipPrice'] * $priceRatio;
    $fVisitFee = $tripData[0]['fVisitFee'] * $priceRatio;
    $fMaterialFee = $tripData[0]['fMaterialFee'] * $priceRatio;
    $fMiscFee = $tripData[0]['fMiscFee'] * $priceRatio;
    $fDriverDiscount = $tripData[0]['fDriverDiscount'] * $priceRatio;
    $vVehicleFare = $vVehicleFare * $priceRatio;
    $fCancelPrice = $tripData[0]['fCancellationFare'] * $priceRatio;
    $fTollPrice = $tripData[0]['fTollPrice'] * $priceRatio;
    $fTax1 = $tripData[0]['fTax1'] * $priceRatio;
    $fTax2 = $tripData[0]['fTax2'] * $priceRatio;
    if ($fTollPrice > 0) {
        $eTollSkipped = $tripData[0]['eTollSkipped'];
    } else {
        $eTollSkipped = "Yes";
    }

    $tUserComment = $tripData[0]['tUserComment'];
    $returnArr['tUserComment'] = $tUserComment;
    $returnArr['vVehicleType'] = $vVehicleType;
    $returnArr['eIconType'] = $eIconType;
    $returnArr['vVehicleCategory'] = $vVehicleCategoryData[0]['vCategory'];
    $returnArr['TripTime'] = $TripTime;
    $returnArr['ConvertedTripRequestDate'] = $tTripRequestDate;
    $returnArr['FormattedTripDate'] = $tTripRequestDate;
    $returnArr['tTripRequestDateOrig'] = $tTripRequestDateOrig;
    $returnArr['tTripRequestDate'] = $tTripRequestDate;
    $returnArr['TripTimeInMinutes'] = $totalTime;
    $returnArr['TripRating'] = $TripRating;
    $returnArr['CurrencySymbol'] = $currencySymbol;
    $returnArr['TripFare'] = formatNum($iFare * $priceRatio);
    $returnArr['iTripId'] = $tripData[0]['iTripId'];
    $returnArr['vTripPaymentMode'] = $tripData[0]['vTripPaymentMode'];
    $returnArr['eType'] = $tripData[0]['eType'];
    if ($tripData[0]['eType'] == "UberX" && $tripData[0]['eFareType'] != "Regular") {
        $returnArr['tDaddress'] = "";
    }

    if ($tripData[0]['vBeforeImage'] != "") {
        $returnArr['vBeforeImage'] = $tconfig['tsite_upload_trip_images'] . $tripData[0]['vBeforeImage'];
    }

    if ($tripData[0]['eType'] == "UberX") {
        $returnArr['vLogoVehicleCategoryPath'] = $tconfig['tsite_upload_images_vehicle_category'] . "/" . $iVehicleCategoryId . "/";
        $returnArr['vLogoVehicleCategory'] = $vVehicleCategoryData[0]['vLogo'];
    } else {
        $returnArr['vLogoVehicleCategory'] = "";
        $returnArr['vLogoVehicleCategoryPath'] = "";
    }

    if ($tripData[0]['vAfterImage'] != "") {
        $returnArr['vAfterImage'] = $tconfig['tsite_upload_trip_images'] . $tripData[0]['vAfterImage'];
    }

    $originalFare = $iFare;
    if ($eUserType == "Passenger") {
        $iFare = $iFare;
    } else {

        // $iFare = $tripData[0]['fTripGenerateFare'] - $fCommision;
        // $iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $fCommision;
        // $iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $tripData[0]['fTollPrice'] - $fCommision;
        $iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $fCommision - $tripData[0]['fTax1'] - $tripData[0]['fTax2'];
    }

    $surgePrice = 1;
    if ($tripData[0]['fPickUpPrice'] > 1) {
        $surgePrice = $tripData[0]['fPickUpPrice'];
    } else {
        $surgePrice = $tripData[0]['fNightPrice'];
    }

    $SurgePriceFactor = strval($surgePrice);
    $returnArr['TripFareOfMinutes'] = formatNum($tripData[0]['fPricePerMin'] * $priceRatio);
    $returnArr['TripFareOfDistance'] = formatNum($tripData[0]['fPricePerKM'] * $priceRatio);
    $returnArr['iFare'] = formatNum($iFare * $priceRatio);
    $returnArr['iOriginalFare'] = formatNum($originalFare * $priceRatio);
    $returnArr['TotalFare'] = formatNum($iFare * $priceRatio);
    $returnArr['fPricePerKM'] = formatNum($fPricePerKM);
    $returnArr['iBaseFare'] = formatNum($iBaseFare);
    $returnArr['fPricePerMin'] = formatNum($fPricePerMin);
    $returnArr['fCommision'] = formatNum($fCommision * $priceRatio);
    $returnArr['fDistance'] = formatNum($fDistance);
    $returnArr['fDiscount'] = formatNum($fDiscount * $priceRatio);
    $returnArr['fMinFareDiff'] = formatNum($fMinFareDiff);
    $returnArr['fWalletDebit'] = formatNum($fWalletDebit * $priceRatio);
    $returnArr['fSurgePriceDiff'] = formatNum($fSurgePriceDiff);
    $returnArr['fTripGenerateFare'] = formatNum($fTripGenerateFare);
    $returnArr['fFlatTripPrice'] = formatNum($fFlatTripPrice);
    if ($eTollSkipped == "No") {
        $returnArr['fTollPrice'] = formatNum($fTollPrice);
    }

    if ($fTipPrice > 0) {
        $returnArr['fTipPrice'] = $currencySymbol . formatNum($fTipPrice);
    }

    $returnArr['SurgePriceFactor'] = $SurgePriceFactor;
    $returnArr['fVisitFee'] = formatNum($fVisitFee);
    $returnArr['fMaterialFee'] = formatNum($fMaterialFee);
    $returnArr['fMiscFee'] = formatNum($fMiscFee);
    $returnArr['fDriverDiscount'] = formatNum($fDriverDiscount);
    $returnArr['fCancelPrice'] = formatNum($fCancelPrice);
    $returnArr['fTax1'] = formatNum($fTax1);
    $returnArr['fTax2'] = formatNum($fTax2);
    $returnArr['eSystem'] = $tripData[0]['eSystem'];

    // echo "<pre>"; print_r($tripData); die;
    $iDriverId = $tripData[0]['iDriverId'];
    $driverDetails = get_value('register_driver', '*', 'iDriverId', $iDriverId);
    $driverDetails[0]['vImage'] = ($driverDetails[0]['vImage'] != "" && $driverDetails[0]['vImage'] != "NONE") ? $driverDetails[0]['vImage'] : "";
    $driverDetails[0]['vPhone'] = '+' . $driverDetails[0]['vCode'] . $driverDetails[0]['vPhone'];
    $returnArr['DriverDetails'] = $driverDetails[0];
    $iUserId = $tripData[0]['iUserId'];
    $passengerDetails = get_value('register_user', '*', 'iUserId', $iUserId);
    $passengerDetails[0]['vImgName'] = ($passengerDetails[0]['vImgName'] != "" && $passengerDetails[0]['vImgName'] != "NONE") ? $passengerDetails[0]['vImgName'] : "";
    $passengerDetails[0]['vPhone'] = '+' . $passengerDetails[0]['vPhoneCode'] . $passengerDetails[0]['vPhone'];
    $returnArr['PassengerDetails'] = $passengerDetails[0];
    if ($eUserType == "Passenger") {
        $returnArr['vImage'] = $driverDetails[0]['vImage'];
    } else {
        $returnArr['vImage'] = $passengerDetails[0]['vImgName'];
    }
    $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    //$fUserCountryTax1 = $TaxArr['fTax1'];
    //$fUserCountryTax2 = $TaxArr['fTax2'];
    $fUserCountryTax1 = $tripData[0]['fTax1Percentage'];
    $fUserCountryTax2 = $tripData[0]['fTax2Percentage'];
    $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
    $sql = "SELECT make.vMake, model.vTitle, dv.*  FROM `driver_vehicle` dv, make, model WHERE dv.iDriverVehicleId='" . $iDriverVehicleId . "' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId`";
    $vehicleDetailsArr = $obj->MySQLSelect($sql);
    $vehicleDetailsArr[0]['vModel'] = $vehicleDetailsArr[0]['vTitle'];

    // if ($eUserType == "Passenger" && $tripData[0]['eType'] == "UberX") {
    if ($tripData[0]['eType'] == "UberX") {

        // $ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");
        $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
        $fAmount = "0";
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $tripData[0]['iVehicleTypeId'] . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);
            $vehicleTypeData = get_value('vehicle_type', 'eFareType,fPricePerHour,fFixedFare', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId']);
            if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                $fAmount = $currencySymbol . $vehicleTypeData[0]['fFixedFare'];
            } else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                $fAmount = $currencySymbol . $vehicleTypeData[0]['fPricePerHour'] . "/hour";
            }

            if (count($serviceProData) > 0) {
                $fAmount = $serviceProData[0]['fAmount'];
                $vVehicleFare = $fAmount * $priceRatio;
                $vVehicleFare = formatNum($vVehicleFare);
                if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                    $fAmount = $currencySymbol . $fAmount;
                } else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                    $fAmount = $currencySymbol . $fAmount . "/hour";
                }
            }

            $vehicleDetailsArr[0]['fAmount'] = strval($fAmount);
        }
    }

    $returnArr['DriverCarDetails'] = $vehicleDetailsArr[0];
    if ($iActive == "Canceled" && $eUserType == "Driver") {
        $sql = "SELECT * from trip_outstanding_amount WHERE iTripId = '" . $iTripId . "'";
        $tripCanceledData = $obj->MySQLSelect($sql);
        $fcancelCommision = $tripCanceledData[0]['fCommision'];
        $fDriverPendingAmount = $tripCanceledData[0]['fDriverPendingAmount'];
        $ePaidByPassenger = $tripCanceledData[0]['ePaidByPassenger'];
        $ePaidToDriver = $tripCanceledData[0]['ePaidToDriver'];
        $returnArr['fCommision'] = formatNum($fcancelCommision * $priceRatio);
        $returnArr['iFare'] = formatNum($fDriverPendingAmount * $priceRatio);
    }
    if ($eUserType == "Passenger") {
        $tripFareDetailsArr = array();
        if ($eFlatTrip == "Yes" && $iActive != "Canceled") {
            $i = 0;
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_FLAT_TRIP_FARE_TXT']] = $currencySymbol . " " . $returnArr['fFlatTripPrice'];
            if ($fSurgePriceDiff > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }

            if ($fDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                $i++;
            }

            if ($fWalletDebit > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                $i++;
            }

            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare'] : "--";
        } elseif ($eFlatTrip == "Yes" && $iActive == "Canceled") {
            $tripFareDetailsArr[0][$languageLabelsArr['LBL_Total_Fare']] = $currencySymbol . " 0.00";
        } elseif ($fCancelPrice > 0) {
            $tripFareDetailsArr[0][$languageLabelsArr['LBL_CANCELLATION_FEE']] = $currencySymbol . $returnArr['fCancelPrice'];
            $tripFareDetailsArr[1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $currencySymbol . $returnArr['fCancelPrice'];
        } else {
            $i = 0;
            $countUfx = 0;
            if ($tripData[0]['eType'] == "UberX") {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $returnArr['vVehicleCategory'] . "-" . $returnArr['vVehicleType'];
                $countUfx = 1;
            }

            if ($tripData[0]['eFareType'] == "Regular") {

                // $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vVehicleType . " " . $currencySymbol . $returnArr['iBaseFare'];
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iBaseFare'] : "--";
                if ($countUfx == 1) {
                    $i++;
                }

                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $languageLabelsArr['LBL_KM_DISTANCE_TXT'] . ")"] = ($iActive != "Canceled")?$currencySymbol . $returnArr['TripFareOfDistance']:"--";
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $DisplayDistanceTxt . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                $i++;
            } else if ($tripData[0]['eFareType'] == "Fixed") {

                //  $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = $currencySymbol . ($fTripGenerateFare - $fSurgePriceDiff - $fMinFareDiff);
                $vVehicleFare = ($tripData[0]['iFare'] * $priceRatio) + $fDiscount + $fWalletDebit + $fDriverDiscount - $fVisitFee - $fMaterialFee - $fMiscFee - $fOutStandingAmount - $fWaitingFees - $fTax1 - $fTax2;
                $SERVICE_COST = ($tripData[0]['iQty'] > 1) ? $tripData[0]['iQty'] . ' X ' . $currencySymbol . formatNum($vVehicleFare) : $currencySymbol . formatNum($vVehicleFare);
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $SERVICE_COST : "--";
                if ($countUfx == 1) {
                    $i++;
                }
            } else if ($tripData[0]['eFareType'] == "Hourly") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                if ($countUfx == 1) {
                    $i++;
                }
            }

            if ($fVisitFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_VISIT_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fVisitFee'] : "--";
                $i++;
            }

            if ($fMaterialFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MATERIAL_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMaterialFee'] : "--";
                $i++;
            }

            if ($fMiscFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MISC_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMiscFee'] : "--";
                $i++;
            }

            if ($fDriverDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDriverDiscount'] : "--";
                $i++;
            }

            // print_r($tripFareDetailsArr);exit;
            // echo $tripData[0]['eFareType'];exit;
            if ($fSurgePriceDiff > 0) {
                $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff;
                if ($eTollSkipped == "No") {
                    $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fTollPrice;
                }

                $normalfare = formatNum($normalfare);
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }

            if ($fMinFareDiff > 0) {

                // $minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = $fTripGenerateFare;
                if ($eTollSkipped == "No") {
                    $minimamfare = $fTripGenerateFare - $fTollPrice;
                }

                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[$i + 1][$currencySymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = $currencySymbol . $returnArr['fMinFareDiff'];
                $returnArr['TotalMinFare'] = ($iActive != "Canceled") ? $minimamfare : "--";
                $i++;
            }

            if ($eTollSkipped == "No") {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TOLL_PRICE_TOTAL']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTollPrice'] : "--";
                $i++;
            }

            if ($fDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                $i++;
            }

            if ($fWalletDebit > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                $i++;
            }

            /* if ($fTipPrice > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIP_AMOUNT']] = ($iActive != "Canceled")?$currencySymbol . $returnArr['fTipPrice']:"--";
              $i++;
              } */
            if ($fTax1 > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax1'] : "--";
                $i++;
            }

            if ($fTax2 > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax2'] : "--";
                $i++;
            }

            $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare'] : "--";
        }

        $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";
        $returnArr['FareDetailsNewArr'] = $tripFareDetailsArr;
        $FareDetailsArr = array();
        foreach ($tripFareDetailsArr as $data) {
            $FareDetailsArr = array_merge($FareDetailsArr, $data);
        }

        $returnArr['FareDetailsArr'] = $FareDetailsArr;
        $returnArr['HistoryFareDetailsNewArr'] = $tripFareDetailsArr;
        if ($tripData[0]['eType'] == "UberX") {

            // if($fCancelPrice == 0){
            if ($iActive != "Canceled") {
                array_splice($returnArr['HistoryFareDetailsNewArr'], 0, 1);
            }

            if ($PAGE_MODE == "DISPLAY") {
                array_splice($returnArr['FareDetailsNewArr'], 0, 1);
            }
        }
    } else {
        $tripFareDetailsArr = array();
        if ($eFlatTrip == "Yes") {
            $i = 0;
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_FLAT_TRIP_FARE_TXT']] = $currencySymbol . " " . $returnArr['fFlatTripPrice'];
            if ($fSurgePriceDiff > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }

            if ($PAGE_MODE == "DISPLAY") {
                if ($fDiscount > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                    $i++;
                }

                if ($fWalletDebit > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                    $i++;
                }
            }
        } else {
            $i = 0;
            $countUfx = 0;
            if ($tripData[0]['eType'] == "UberX" && $PAGE_MODE == "HISTORY") {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $returnArr['vVehicleCategory'] . "-" . $returnArr['vVehicleType'];
                $countUfx = 1;
            }

            if ($tripData[0]['eFareType'] == "Regular") {

                // $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vVehicleType . " " . $currencySymbol . $returnArr['iBaseFare'];
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iBaseFare'] : "--";
                if ($countUfx == 1) {
                    $i++;
                }

                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $languageLabelsArr['LBL_KM_DISTANCE_TXT'] . ")"] = ($iActive != "Canceled")?$currencySymbol . $returnArr['TripFareOfDistance']:"--";
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $DisplayDistanceTxt . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                $i++;
            } else if ($tripData[0]['eFareType'] == "Fixed") {

                // $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = $currencySymbol . ($fTripGenerateFare - $fSurgePriceDiff - $fMinFareDiff);
                $SERVICE_COST = ($tripData[0]['iQty'] > 1) ? $tripData[0]['iQty'] . ' X ' . $currencySymbol . $vVehicleFare : $currencySymbol . $vVehicleFare;
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $SERVICE_COST : "--";
                if ($countUfx == 1) {
                    $i++;
                }
            } else if ($tripData[0]['eFareType'] == "Hourly") {
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                if ($countUfx == 1) {
                    $i++;
                }
            }

            if ($fVisitFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_VISIT_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fVisitFee'] : "--";
                $i++;
            }

            if ($fMaterialFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MATERIAL_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMaterialFee'] : "--";
                $i++;
            }

            if ($fMiscFee > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MISC_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMiscFee'] : "--";
                $i++;
            }

            if ($fDriverDiscount > 0) {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDriverDiscount'] : "--";
                $i++;
            }

            if ($fSurgePriceDiff > 0) {
                $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff;
                if ($eTollSkipped == "No") {
                    $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fTollPrice;
                }

                $normalfare = formatNum($normalfare);
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }

            if ($fMinFareDiff > 0) {

                // $minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = $fTripGenerateFare;
                if ($eTollSkipped == "No") {
                    $minimamfare = $fTripGenerateFare - $fTollPrice;
                }

                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[$i + 1][$currencySymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMinFareDiff'] : "--";
                $returnArr['TotalMinFare'] = $minimamfare;
                $i++;
            }

            if ($eTollSkipped == "No") {
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TOLL_PRICE_TOTAL']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTollPrice'] : "--";
                $i++;
            }

            if ($PAGE_MODE == "DISPLAY") {
                if ($fDiscount > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                    $i++;
                }

                if ($fWalletDebit > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                    $i++;
                }

                if ($fTax1 > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax1'] : "--";
                    $i++;
                }

                if ($fTax2 > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax2'] : "--";
                    $i++;
                }
            } else {
                if ($fTax1 > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? "-" . $currencySymbol . $returnArr['fTax1'] : "--";
                    $i++;
                }

                if ($fTax2 > 0) {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? "-" . $currencySymbol . $returnArr['fTax2'] : "--";
                    $i++;
                }
            }

            /* if ($fDiscount > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE']] = ($iActive != "Canceled")?"- " . $currencySymbol . $returnArr['fDiscount']:"--";
              $i++;
              }

              if ($fWalletDebit > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled")?"- " . $currencySymbol . $returnArr['fWalletDebit']:"--";
              $i++;
              } */
            /* if ($fTipPrice > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIP_AMOUNT']] = ($iActive != "Canceled")?$currencySymbol . $returnArr['fTipPrice']:"--";
              $i++;
              } */
        }

        $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";
        $returnArr['FareDetailsNewArr'] = $tripFareDetailsArr;
        $FareDetailsArr = array();
        foreach ($tripFareDetailsArr as $data) {
            $FareDetailsArr = array_merge($FareDetailsArr, $data);
        }

        $returnArr['FareDetailsArr'] = $FareDetailsArr;
        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_Commision']] = ($iActive != "Canceled") ? "-" . $currencySymbol . $returnArr['fCommision'] : "--";
        $i++;
        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_EARNED_AMOUNT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare'] : "--";
        $returnArr['HistoryFareDetailsNewArr'] = $tripFareDetailsArr;
        if ($tripData[0]['eType'] == "UberX" && $iActive != "Canceled") {
            array_splice($returnArr['HistoryFareDetailsNewArr'], 0, 1);
        }
    }

    $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";

    // passengertripfaredetails
    $HistoryFareDetailsArr = array();
    foreach ($tripFareDetailsArr as $inner) {
        $HistoryFareDetailsArr = array_merge($HistoryFareDetailsArr, $inner);
    }

    $returnArr['HistoryFareDetailsArr'] = $HistoryFareDetailsArr;

    // drivertripfarehistorydetails
    // echo "<pre>";print_r($returnArr);echo "<pre>";print_r($tripData);exit;
    return $returnArr;
}

function getUserRatingAverage($iMemberId, $eUserType = "Passenger") {
    global $obj, $generalobj;
    if ($eUserType == "Passenger") {
        $iUserId = "iDriverId";
        $checkusertype = "Passenger";
    } else if ($eUserType == "Company") {
        $iUserId = "iCompanyId";
        $checkusertype = "Company";
    } else {
        $iUserId = "iUserId";
        $checkusertype = "Driver";
    }

    $usertotaltrips = get_value("orders", "iOrderId", $iUserId, $iMemberId);
    if (count($usertotaltrips) > 0) {
        for ($i = 0; $i < count($usertotaltrips); $i++) {
            $iOrderId .= $usertotaltrips[$i]['iOrderId'] . ",";
        }

        $iOrderId_str = substr($iOrderId, 0, -1);

        // echo  $iTripId_str;exit;
        $sql = "SELECT count(iRatingId) as ToTalTrips, SUM(vRating1) as ToTalRatings from ratings_user_driver WHERE iOrderId IN (" . $iOrderId_str . ") AND eToUserType = '" . $checkusertype . "'";
        $result_ratings = $obj->MySQLSelect($sql);
        $ToTalTrips = $result_ratings[0]['ToTalTrips'];
        $ToTalRatings = $result_ratings[0]['ToTalRatings'];

        // $average_rating = round($ToTalRatings / $ToTalTrips, 2);
        $average_rating = round($ToTalRatings / $ToTalTrips, 1);
    } else {
        $average_rating = 0;
    }

    return $average_rating;
}

function deliverySmsToReceiver($iTripId) {
    global $obj, $generalobj, $tconfig;
    $sql = "SELECT * from trips WHERE iTripId = '" . $iTripId . "'";
    $tripData = $obj->MySQLSelect($sql);
    $SenderName = get_value("register_user", "vName,vLastName", "iUserId", $tripData[0]['iUserId']);
    $SenderName = $SenderName[0]['vName'] . " " . $SenderName[0]['vLastName'];
    $delivery_address = $tripData[0]['tDaddress'];
    $vDeliveryConfirmCode = $tripData[0]['vDeliveryConfirmCode'];
    $page_link = $tconfig['tsite_url'] . "trip_tracking.php?iTripId=" . $iTripId;
    $page_link = get_tiny_url($page_link);
    //$message_deliver = $SenderName . " has send you the parcel on below address." . $delivery_address . ". Upon Receiving the parcel, please provide below verification code to Delivery Driver. Verification Code: " . $vDeliveryConfirmCode . ". click on link below to track your parcel. " . $page_link;

    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $tripData[0]['iUserId'], '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }


    $dataArraySMSNew['SenderName'] = $SenderName;
    $dataArraySMSNew['deliveryAddress'] = $delivery_address;
    $dataArraySMSNew['vDeliveryConfirmCode'] = $vDeliveryConfirmCode;
    $dataArraySMSNew['pageLink'] = $page_link;


    $message_deliver = $generalobj->send_messages_user('DELIVER_SMS_TO_RECEIVER_THREE', $dataArraySMSNew, "", $vLangCode);

    // echo $message_deliver;exit;
    return $message_deliver;
}

function addToCompanyRequest2($data) {
    global $obj;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");
    $id = $obj->MySQLQueryPerform("company_request", $data, 'insert');
    return $id;
}

function UpdateDriverRequest2($iDriverId, $iUserId, $iTripId, $eStatus = "", $vMsgCode, $eAcceptAttempted = "No", $iOrderId) {
    global $obj;
    $sql = "SELECT * FROM `driver_request` WHERE iDriverId = '" . $iDriverId . "' AND iOrderId = '" . $iOrderId . "' AND iTripId = '0' AND vMsgCode='" . $vMsgCode . "'";
    $db_sql = $obj->MySQLSelect($sql);
    $request_count = count($db_sql);
    if ($request_count > 0) {
        $where = " iDriverRequestId = '" . $db_sql[0]['iDriverRequestId'] . "'";
        if ($eStatus != "") {
            $Data_Update['eStatus'] = $eStatus;
        }
        $Data_Update['tDate'] = @date("Y-m-d H:i:s");
        $Data_Update['iTripId'] = $iTripId;
        $Data_Update['eAcceptAttempted'] = $eAcceptAttempted;
        $id = $obj->MySQLQueryPerform("driver_request", $Data_Update, 'update', $where);
    }
    return $request_count;
}

function getDriverStatus($driverId = '') {
    global $generalobj, $obj, $iServiceId;
    $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $driverId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);

    // $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING_DL'];
    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $driverId . "' ) dl on dl.doc_masterid=dm.doc_masterid  
		where dm.doc_usertype='driver' and dm.status='Active' ";
    $db_document = $obj->MySQLSelect($sql1);
    if (count($db_document) > 0) {
        for ($i = 0; $i < count($db_document); $i++) {
            if ($db_document[$i]['doc_file'] == "") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "Please upload your " . $db_document[$i]['doc_name'];
                setDataResponse($returnArr);
            }

            if ($db_document[$i]['status'] != "Active") {
                $returnArr['Action'] = "0";
                if ($db_document[$i]['status'] == "Inactive") {
                    $returnArr['message'] = "Please activate your " . $db_document[$i]['doc_name'];
                    setDataResponse($returnArr);
                }

                if ($db_document[$i]['status'] == "Deleted") {
                    $returnArr['message'] = "Current status is deleted of your" . $db_document[$i]['doc_name'];
                    setDataResponse($returnArr);
                }
            }
        }
    }

    $sql = "SELECT iDriverVehicleId from driver_vehicle WHERE iDriverId = '" . $driverId . "'";
    $db_drv_vehicle = $obj->MySQLSelect($sql);
    if (count($db_drv_vehicle) == 0) {
        $returnArr['Action'] = "0"; // Check For Driver's vehicle added or not #
        $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
        setDataResponse($returnArr);
    } else {
        $DriverSelectedVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $driverId, '', 'true');
        if ($DriverSelectedVehicleId == 0) {
            $returnArr['Action'] = "0"; // Check Driver has selected  vehicle or not if #
            $returnArr['message'] = "LBL_SELECT_CAR_MESSAGE_TXT";
            setDataResponse($returnArr);
        } else {

            // Check For Driver's selected vehicle's document are upload or not #
            $sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $DriverSelectedVehicleId . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='car' and dm.status='Active'";
            $db_selected_vehicle = $obj->MySQLSelect($sql);
            if (count($db_selected_vehicle) > 0) {
                for ($i = 0; $i < count($db_selected_vehicle); $i++) {
                    if ($db_selected_vehicle[$i]['doc_file'] == "") {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = "Please upload your " . $db_selected_vehicle[$i]['doc_name'];
                        setDataResponse($returnArr);
                    }
                }
            }

            // Check For Driver's selected vehicle's document are upload or not #
            // Check For Driver's selected vehicle status #
            $DriverSelectedVehicleStatus = get_value('driver_vehicle', 'eStatus', 'iDriverVehicleId', $DriverSelectedVehicleId, '', 'true');
            if ($DriverSelectedVehicleStatus == "Inactive" || $DriverSelectedVehicleStatus == "Deleted") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_SELECTED_VEHICLE_NOT_ACTIVE";
                setDataResponse($returnArr);
            }

            // Check For Driver's selected vehicle status #
        }
    }

    $sql = "SELECT rd.eStatus as driverstatus,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='" . $driverId . "' AND cmp.iCompanyId=rd.iCompanyId";
    $Data = $obj->MySQLSelect($sql);
    if ($Data[0]['driverstatus'] != "active" || $Data[0]['cmpEStatus'] != "Active") {
        $returnArr['Action'] = "0";
        if ($Data[0]['cmpEStatus'] != "Active") {
            $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_COMPANY";
        } else if ($Data[0]['driverstatus'] == "Deleted") {
            $returnArr['message'] = "LBL_ACC_DELETE_TXT";
        } else {
            $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_DRIVER";
        }

        setDataResponse($returnArr);
    }
}

function TripCollectTip($iMemberId, $iTripId, $fAmount) {
    global $generalobj, $obj, $APP_PAYMENT_METHOD;
    $tbl_name = "register_user";
    $currencycode = "vCurrencyPassenger";
    $iUserId = "iUserId";
    $eUserType = "Rider";
    if ($iMemberId == "") {
        $iMemberId = get_value('trips', 'iUserId', 'iTripId', $iTripId, '', 'true');
    }

    $vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId, '', 'true');
    $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId, '', 'true');
    $vBrainTreeToken = get_value($tbl_name, 'vBrainTreeToken', $iUserId, $iMemberId, '', 'true');
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId, '', 'true');
    $currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $currencyratio = get_value('currency', 'Ratio', 'vName', $userCurrencyCode, '', 'true');
    $UserCardData = get_value($tbl_name, 'vPaymayaCustId,vPaymayaToken', $iUserId, $iMemberId);
    $vPaymayaCustId = $UserCardData[0]['vPaymayaCustId'];
    $vPaymayaToken = $UserCardData[0]['vPaymayaToken'];
    // $price = $fAmount*$currencyratio;
    $price = round($fAmount / $currencyratio, 2);
    $price_new = $price * 100;
    $price_new = round($price_new);
    if ((($vStripeCusId == "" || $vStripeToken == "") && $APP_PAYMENT_METHOD == "Stripe")) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    if ($vBrainTreeToken == "" && $APP_PAYMENT_METHOD == "Braintree") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    if ((($vPaymayaCustId == "" || $vPaymayaToken == "") && $APP_PAYMENT_METHOD == "Paymaya")) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }

    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $tDescription_stripe = "Amount debited";
    $tDescription = "#LBL_AMOUNT_DEBIT#";
    $ePaymentStatus = 'Unsettelled';
    $userAvailableBalance = $generalobj->get_user_available_balance($iMemberId, $eUserType);
    if ($userAvailableBalance > $price) {
        $where = " iTripId = '$iTripId'";
        $data['fTipPrice'] = $price;
        $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);
        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $tripId, '', 'true');
        $data_wallet['iUserId'] = $iMemberId;
        $data_wallet['eUserType'] = "Rider";
        $data_wallet['iBalance'] = $price;
        $data_wallet['eType'] = "Debit";
        $data_wallet['dDate'] = date("Y-m-d H:i:s");
        $data_wallet['iTripId'] = $iTripId;
        $data_wallet['eFor'] = "Booking";
        $data_wallet['ePaymentStatus'] = "Unsettelled";
        $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING#" . $vRideNo;
        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);

        // $returnArr["Action"] = "1";
        // setDataResponse($returnArr);
    } else if ($price > 0.51) {
        $Charge_Array = array(
            "iFare" => $price,
            "price_new" => $price_new,
            "currency" => $currencyCode,
            "vStripeCusId" => $vStripeCusId,
            "description" => $tDescription_stripe,
            "iTripId" => $iTripId,
            "eCancelChargeFailed" => "No",
            "vBrainTreeToken" => $vBrainTreeToken,
            "vRideNo" => $vRideNo,
            "iMemberId" => $iMemberId,
            "UserType" => "Passenger"
        );
        $ChargeidArr = ChargeCustomer($Charge_Array, "submitRating"); // function for charge customer
        $ChargeidArrId = $ChargeidArr['id'];
        $status = $ChargeidArr['status'];
        if ($status == "success") {
            $where = " iTripId = '$iTripId'";
            $data['fTipPrice'] = $price;
            $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);

            $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
            $data_payments['iTripId'] = $iTripId;
            $data_payments['eEvent'] = "TripTip";
            $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRANS_FAILED";
            setDataResponse($returnArr);
        }
        /* try{
          $charge_create = Stripe_Charge::create(array(
          "amount" => $price_new,
          "currency" => $currencyCode,
          "customer" => $vStripeCusId,
          "description" =>  $tDescription
          ));
          $details = json_decode($charge_create);
          $result = get_object_vars($details);
          //echo "<pre>";print_r($result);exit;
          if($result['status']=="succeeded" && $result['paid']=="1"){
          $where = " iTripId = '$iTripId'";
          $data['fTipPrice']= $price;
          $id = $obj->MySQLQueryPerform("trips",$data,'update',$where);
          //$returnArr["Action"] = "1";
          //setDataResponse($returnArr);
          }else{
          $returnArr['Action'] = "0";
          $returnArr['message']="LBL_TRANS_FAILED";
          setDataResponse($returnArr);
          }

          }catch(Exception $e){

          // echo "<pre>";print_r($e);exit;
          $error3 = $e->getMessage();
          $returnArr["Action"] = "0";
          $returnArr['message'] = $error3;

          // $returnArr['message']="LBL_TRANS_FAILED";
          setDataResponse($returnArr);
          } */
    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_REQUIRED_MINIMUM_AMOUT";
        $returnArr['minValue'] = strval(round(0.51 * $currencyratio, 2));
        setDataResponse($returnArr);
    }
    return $iTripId;
}

function checkFlatTripnew($Source_point_Address, $Destination_point_Address, $iVehicleTypeId) {
    global $generalobj, $obj;
    $returnArr = array();
    $sql = "SELECT ls.fFlatfare,lm1.vLocationName as vFromname,lm2.vLocationName as vToname, lm1.tLatitude as fromlat, lm1.tLongitude as fromlong, lm2.tLatitude as tolat, lm2.tLongitude as tolong FROM `location_wise_fare` ls left join location_master lm1 on ls.iFromLocationId = lm1.iLocationId left join location_master lm2 on ls.iToLocationId = lm2.iLocationId WHERE lm1.eFor = 'FixFare' AND lm1.eStatus = 'Active' AND ls.eStatus = 'Active' AND ls.iVehicleTypeId = '" . $iVehicleTypeId . "'";
    $location_data = $obj->MySQLSelect($sql);

    $polygon = array();
    foreach ($location_data as $key => $value) {
        $fromlat = explode(",", $value['fromlat']);
        $fromlong = explode(",", $value['fromlong']);
        $tolat = explode(",", $value['tolat']);
        $tolong = explode(",", $value['tolong']);
        for ($x = 0; $x < count($fromlat); $x++) {
            if (!empty($fromlat[$x]) || !empty($fromlong[$x])) {
                $from_polygon[$key][] = array(
                    $fromlat[$x],
                    $fromlong[$x]
                );
            }
        }

        for ($y = 0; $y < count($tolat); $y++) {
            if (!empty($tolat[$y]) || !empty($tolong[$y])) {
                $to_polygon[$key][] = array(
                    $tolat[$y],
                    $tolong[$y]
                );
            }
        }

        if (!empty($Source_point_Address) && !empty($Destination_point_Address)) {
            if (!empty($from_polygon[$key]) && !empty($to_polygon[$key])) {
                $from_source_addresss = contains($Source_point_Address, $from_polygon[$key]) ? 'IN' : 'OUT';
                $to_source_addresss = contains($Destination_point_Address, $to_polygon[$key]) ? 'IN' : 'OUT';

                $to_dest_addresss = contains($Destination_point_Address, $to_polygon[$key]) ? 'IN' : 'OUT';
                $from_dest_addresss = contains($Source_point_Address, $from_polygon[$key]) ? 'IN' : 'OUT';
                if (($from_source_addresss == "IN" && $to_source_addresss == "IN") || ($to_dest_addresss == "IN" && $from_dest_addresss == "IN")) {
                    $returnArr['Flatfare'] = $location_data[$key]['fFlatfare'];
                    $returnArr['eFlatTrip'] = "Yes";
                    return $returnArr;
                }
            }
        }
    }

    if (empty($returnArr)) {
        $returnArr['eFlatTrip'] = "No";
        $returnArr['Flatfare'] = 0;
    }

    return $returnArr;
}

############### Check FlatTrip Or Not  ###################################################################
############### Get User's  Country Details From TimeZone ####################################################################

function GetUserCounryDetail($iMemberId, $UserType = "Passenger", $vTimeZone, $vUserDeviceCountry = "") {
    global $generalobj, $obj, $tconfig, $DEFAULT_COUNTRY_CODE_WEB, $tconfig;
    $returnArr = array();
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vCountryfield = "vCountry";
        $iUserId = "iUserId";
    } else if ($UserType == "Driver") {
        $tblname = "register_driver";
        $vCountryfield = "vCountry";
        $iUserId = "iDriverId";
    } else {
        $tblname = "company";
        $vCountryfield = "vCountry";
        $iUserId = "iCompanyId";
    }

    $returnArr['vDefaultCountry'] = '';
    $returnArr['vDefaultCountryCode'] = '';
    $returnArr['vDefaultPhoneCode'] = '';
    $returnArr['vDefaultCountryImage'] = ''; //added by SP for country image related changes on 06-08-2019
    $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode FROM country WHERE vTimeZone = '" . $vTimeZone . "' AND eStatus = 'Active'";
    $sqlcountryCode = $obj->MySQLSelect($sql);

    if (!empty($sqlcountryCode) && count($sqlcountryCode) > 0) {
        $returnArr = $sqlcountryCode[0];
    } else {
        if ($vUserDeviceCountry != "") {
            $vUserDeviceCountry = strtoupper($vUserDeviceCountry);
            $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode FROM country WHERE vCountryCode = '" . $vUserDeviceCountry . "' AND eStatus = 'Active'";
            $sqlusercountryCode = $obj->MySQLSelect($sql);
            if (!empty($sqlusercountryCode) && count($sqlusercountryCode) > 0) {
                $returnArr = $sqlusercountryCode[0];
            } else {
                $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode FROM country WHERE vCountryCode = '" . $DEFAULT_COUNTRY_CODE_WEB . "'";
                $sqlcountryCode = $obj->MySQLSelect($sql);
                if (count($sqlcountryCode) > 0) {
                    $returnArr = $sqlcountryCode[0];
                }
            }
        } else {
            $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode FROM country WHERE vCountryCode = '" . $DEFAULT_COUNTRY_CODE_WEB . "'";
            $sqlcountryCode = $obj->MySQLSelect($sql);
            if (!empty($sqlcountryCode) && count($sqlcountryCode) > 0) {
                $returnArr = $sqlcountryCode[0];
            }
        }
    }

    //added by SP for getting user wise image on 6-9-2019
    $sqlc = "SELECT $vCountryfield as vCountry FROM $tblname WHERE $iUserId = '" . $iMemberId . "'";
    $datac = $obj->MySQLSelect($sqlc);

    $sqlcd = "SELECT vCountry as vCountry, vCountryCode as vCountryCode, vPhoneCode as vPhoneCode,vRImage,vSImage FROM country WHERE vCountryCode = '" . $datac[0]['vCountry'] . "'";
    $datacode = $obj->MySQLSelect($sqlcd);

    //$temp_image = checkimgexist("webimages/icons/country_flags/" . strtolower($datacode[0]['vCountryCode']) . "_r.png", '1');
    $temp_image = checkimgexist("webimages/icons/country_flags/" . $datacode[0]['vRImage'], '1');
    $returnArr['vRImageMember'] = $temp_image; //added by SP for country image related changes on 05-08-2019
    //$temp_image = checkimgexist("webimages/icons/country_flags/" . strtolower($datacode[0]['vCountryCode']) . "_s.png", '2');
    $temp_image = checkimgexist("webimages/icons/country_flags/" . $datacode[0]['vSImage'], '2');
    $returnArr['vSImageMember'] = $temp_image; //added by SP for country image related changes on 05-08-2019     

    $temp_image = checkimgexist("webimages/icons/country_flags/" . strtolower($returnArr['vDefaultCountryCode']) . "_r.png", '1');
    $returnArr['vRImage'] = $temp_image; //added by SP for country image related changes on 05-08-2019
    $temp_image = checkimgexist("webimages/icons/country_flags/" . strtolower($returnArr['vDefaultCountryCode']) . "_s.png", '2');
    $returnArr['vSImage'] = $temp_image; //added by SP for country image related changes on 05-08-2019
    if (empty($datac[0]['vCountry'])) { //added by SP for when country is not inserted for the particular user as like FB user then we will overwrite it as default country image on 04-10-2019 
        $returnArr['vRImageMember'] = $returnArr['vRImage'];
        $returnArr['vSImageMember'] = $returnArr['vSImage'];
    }

    $temp_image = checkimgexist("webimages/icons/country_flags/" . strtolower($returnArr['vDefaultCountryCode']) . "_s.png", "2");
    $returnArr['vDefaultCountryImage'] = $temp_image; //added by SP for country image related changes on 06-08-2019
    return $returnArr;
}

//added by SP when img missing for any country then given default image on 04-10-2019
function checkimgexist($img, $type) {
    global $tconfig;
    $country_temp = 'IN';
    $img_temp = $img;
    if (!file_exists($tconfig["tpanel_path"] . $img)) {
        if ($type == 1) {
            $img_temp = $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . strtolower($country_temp) . "_r.png";
        } else {
            $img_temp = $tconfig["tsite_url"] . "webimages/icons/DefaultImg/" . strtolower($country_temp) . "_s.png";
        }
    } else {
        $img_temp = $tconfig["tsite_url"] . $img;
    }
    return $img_temp;
}

############### Get User's  Country Details From TimeZone  ###################################################################
############### Get User  Country's Police Number   ###################################################################

function getMemberCountryPoliceNumber($iMemberId, $UserType = "Passenger", $vCountry) {
    global $generalobj, $obj, $SITE_POLICE_CONTROL_NUMBER;
    if ($vCountry != "") {
        if ($UserType == "Passenger") {
            $tblname = "register_user";
            $vCountryfield = "vCountry";
            $iUserId = "iUserId";
        } else if ($UserType == "Driver") {
            $tblname = "register_driver";
            $vCountryfield = "vCountry";
            $iUserId = "iDriverId";
        } else {
            $tblname = "company";
            $vCountryfield = "vCountry";
            $iUserId = "iCompanyId";
        }

        $sql = "SELECT co.vEmergencycode FROM country as co LEFT JOIN $tblname as rd ON co.vCountryCode = rd.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'";
        $db_sql = $obj->MySQLSelect($sql);
        $Country_Police_Number = $db_sql[0]['vEmergencycode'];
        if ($Country_Police_Number == "" || $Country_Police_Number == NULL) {
            $Country_Police_Number = $SITE_POLICE_CONTROL_NUMBER;
        }
    } else {
        $Country_Police_Number = $SITE_POLICE_CONTROL_NUMBER;
    }

    return $Country_Police_Number;
}

############### Get User  Country's Police Number   ###################################################################

function calculate_restaurant_time_span_old($iCompanyId, $iUserId) {
    global $obj, $generalobj, $tconfig, $vTimeZone, $iServiceId, $vCurrentTime;

    // date_default_timezone_set($vTimeZone);
    if ($vCurrentTime == "" || $vCurrentTime == NULL) {
        $vCurrentTime = @date("Y-m-d H:i:s");
    }

    $serverTimeZone = date_default_timezone_get();
    $returnArr = array();
    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $Datasql = $obj->MySQLSelect($sql);
    $eStatus = $Datasql[0]['eStatus'];
    $vCountry = $Datasql[0]['vCountry'];
    if ($vCountry == "" || $vCountry == NULL) {
        $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
    }

    //$vTimeZone = get_value('country', 'vTimeZone', 'vCountryCode', $vCountry, '', 'true');
    date_default_timezone_set($vTimeZone);
    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    //$day = date("l");
    $day = date('l', strtotime($vCurrentTime));
    if ($day == "Sunday" || $day == "Saturday") {
        $vFromTimeSlot1 = "vFromSatSunTimeSlot1";
        $vFromTimeSlot2 = "vFromSatSunTimeSlot2";
        $vToTimeSlot1 = "vToSatSunTimeSlot1";
        $vToTimeSlot2 = "vToSatSunTimeSlot2";
    } else {
        $vFromTimeSlot1 = "vFromMonFriTimeSlot1";
        $vFromTimeSlot2 = "vFromMonFriTimeSlot2";
        $vToTimeSlot1 = "vToMonFriTimeSlot1";
        $vToTimeSlot2 = "vToMonFriTimeSlot2";
    }

    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    if ($Datasql[0][$vFromTimeSlot1] == "00:00:00" && $Datasql[0][$vToTimeSlot1] == "00:00:00" && $Datasql[0][$vFromTimeSlot2] == "00:00:00" && $Datasql[0][$vToTimeSlot2] == "00:00:00") {
        $returnArr['status'] = "Closed";
        $returnArr['opentime'] = "";
        $returnArr['closetime'] = "";
        $returnArr['restaurantstatus'] = "closed";
    } else {
        /* $vFromTimeSlot1 = strtotime($Datasql[0]['vFromTimeSlot1']);
          $vToTimeSlot1 = strtotime($Datasql[0]['vToTimeSlot1']);
          $vFromTimeSlot2 = strtotime($Datasql[0]['vFromTimeSlot2']);
          $vToTimeSlot2 = strtotime($Datasql[0]['vToTimeSlot2']); */
        if ($Datasql[0][$vToTimeSlot1] < $Datasql[0][$vFromTimeSlot1]) {
            $endTime = strtotime($Datasql[0][$vToTimeSlot1]);
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime('+1 day', $endTime));
        } else {
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot1]));
        }

        if ($Datasql[0][$vToTimeSlot2] < $Datasql[0][$vFromTimeSlot2]) {
            $endTime2 = strtotime($Datasql[0][$vToTimeSlot2]);
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime('+1 day', $endTime2));
        } else {
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot2]));
        }

        //$date = @date("H:i");
        $date = @date("H:i", strtotime($vCurrentTime));
        // $currenttime = strtotime($date);
        $status = "closed";
        $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
        $opentime = "";
        $OpenAt = $languageLabelsArr['LBL_RESTAURANT_OPEN_TXT'];
        $closetime = "";
        $timeslotavailable = "No";
        if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1 || isBetween($vFromTimeSlot_2, $vToTimeSlot_2, $date) == 1) {
            $status = "open";
            $timeslotavailable = "Yes";
            $status_display = $languageLabelsArr['LBL_RESTAURANT_OPEN_STAUS_TXT'];
            $currentdate = @date("Y-m-d H:i:s");
            $enddate = @date("Y-m-d");
            if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1) {
                $enddate = $enddate . " " . $vToTimeSlot_1 . ":00";
            } else {
                $enddate = $enddate . " " . $vToTimeSlot_2 . ":00";
            }

            $datediff = strtotime($enddate) - strtotime($currentdate);
            if ($datediff < 900) {
                $closein = $languageLabelsArr['LBL_RESTAURANT_CLOSE_MINS_TXT'];
                $closemins = round($datediff / 60);
                $closetime = $closein . " " . $closemins . " " . $languageLabelsArr['LBL_MINS_SMALL'];
            }
        } else {
            $newdate = @date("Y-m-d");

            // $newdate = $newdate." ".$vFromTimeSlot_2.":00";
            if (isBetween($vFromTimeSlot_1, $vFromTimeSlot_1, $date) == 1) {
                $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
            } else {
                if ($vFromTimeSlot_1 < $vFromTimeSlot_2 && $vFromTimeSlot_1 > $date) {
                    $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                } else {
                    $newdate = ($vFromTimeSlot_2 == "00:00") ? $newdate . " " . $vFromTimeSlot_1 . ":00" : $newdate . " " . $vFromTimeSlot_2 . ":00";
                }
            }

            $currentdate = @date("Y-m-d H:i:s");
            $datediff = strtotime($newdate) - strtotime($currentdate);
            if ($datediff > 0) {
                $opentime = $OpenAt . " " . date("h:i a", strtotime($newdate));
            }
        }

        $eAvailable = $Datasql[0]['eAvailable'];
        $eLogout = $Datasql[0]['eLogout'];
        if ($eAvailable == "No" || $eLogout == "Yes" || $eStatus != "Active") {
            $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];

            // $opentime = "";
            $closetime = "";
            $status = "closed";
        }

        $returnArr['status'] = $status_display;
        $returnArr['opentime'] = $opentime;
        $returnArr['closetime'] = $closetime;
        $returnArr['restaurantstatus'] = $status;
        $returnArr['timeslotavailable'] = $timeslotavailable;
    }

    // echo "<pre>";print_r($returnArr);
    date_default_timezone_set($serverTimeZone);
    return $returnArr;
}

function calculate_restaurant_time_span($iCompanyId, $iUserId, $vLanguage = "", $languageLabelsArr = array(), $storeDetails = array()) {
    global $obj, $generalobj, $tconfig, $vTimeZone, $iServiceId, $vCurrentTime;
    // date_default_timezone_set($vTimeZone);
    if ($vCurrentTime == "" || $vCurrentTime == NULL) {
        $vCurrentTime = @date("Y-m-d H:i:s");
    }
    $serverTimeZone = date_default_timezone_get();
    $returnArr = array();
    if (isset($storeDetails[$iCompanyId])) {
        //echo "<pre>";print_r($storeDetails[$iCompanyId]);die;
        $Datasql = $storeDetails[$iCompanyId];
    } else {
        $Datasql = $obj->MySQLSelect("SELECT iCompanyId,eStatus,vCountry,vFromSatSunTimeSlot1,vFromSatSunTimeSlot2,vToSatSunTimeSlot1,vToSatSunTimeSlot2,vFromMonFriTimeSlot1,vFromMonFriTimeSlot2,vToMonFriTimeSlot1,vToMonFriTimeSlot2,eAvailable,eLogout FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'");
    }
    //echo "<pre>";print_r($Datasql);die;
    $eStatus = $Datasql[0]['eStatus'];
    $vCountry = $Datasql[0]['vCountry'];
    if ($vCountry == "" || $vCountry == NULL) {
        $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
    }
    //$vTimeZone = get_value('country', 'vTimeZone', 'vCountryCode', $vCountry, '', 'true');
    date_default_timezone_set($vTimeZone);
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = "EN";
        }
    }

    //$day = date("l");
    $vCurrentTime = @date("Y-m-d H:i:s");
    $day = date('l', strtotime($vCurrentTime));
    if ($day == "Sunday" || $day == "Saturday") {
        $vFromTimeSlot1 = "vFromSatSunTimeSlot1";
        $vFromTimeSlot2 = "vFromSatSunTimeSlot2";
        $vToTimeSlot1 = "vToSatSunTimeSlot1";
        $vToTimeSlot2 = "vToSatSunTimeSlot2";
    } else {
        $vFromTimeSlot1 = "vFromMonFriTimeSlot1";
        $vFromTimeSlot2 = "vFromMonFriTimeSlot2";
        $vToTimeSlot1 = "vToMonFriTimeSlot1";
        $vToTimeSlot2 = "vToMonFriTimeSlot2";
    }
    if (count($languageLabelsArr) == 0) {
        $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    }
    if ($Datasql[0][$vFromTimeSlot1] == "00:00:00" && $Datasql[0][$vToTimeSlot1] == "00:00:00" && $Datasql[0][$vFromTimeSlot2] == "00:00:00" && $Datasql[0][$vToTimeSlot2] == "00:00:00") {
        $returnArr['status'] = "Closed";
        $returnArr['opentime'] = "";
        $returnArr['closetime'] = "";
        $returnArr['restaurantstatus'] = "closed";
    } else {
        /* $vFromTimeSlot1 = strtotime($Datasql[0]['vFromTimeSlot1']);
          $vToTimeSlot1 = strtotime($Datasql[0]['vToTimeSlot1']);
          $vFromTimeSlot2 = strtotime($Datasql[0]['vFromTimeSlot2']);
          $vToTimeSlot2 = strtotime($Datasql[0]['vToTimeSlot2']); */
        if ($Datasql[0][$vToTimeSlot1] < $Datasql[0][$vFromTimeSlot1]) {
            $endTime = strtotime($Datasql[0][$vToTimeSlot1]);
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime('+1 day', $endTime));
        } else {
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot1]));
        }

        if ($Datasql[0][$vToTimeSlot2] < $Datasql[0][$vFromTimeSlot2]) {
            $endTime2 = strtotime($Datasql[0][$vToTimeSlot2]);
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime('+1 day', $endTime2));
        } else {
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot2]));
        }

        //$date = @date("H:i");
        $date = @date("H:i", strtotime($vCurrentTime));
        // $currenttime = strtotime($date);
        $status = "closed";
        $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
        $opentime = "";
        $OpenAt = $languageLabelsArr['LBL_RESTAURANT_OPEN_TXT'];
        $closetime = "";
        $timeslotavailable = "No";
        if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1 || isBetween($vFromTimeSlot_2, $vToTimeSlot_2, $date) == 1) {
            $status = "open";
            $timeslotavailable = "Yes";
            $status_display = $languageLabelsArr['LBL_RESTAURANT_OPEN_STAUS_TXT'];
            $currentdate = @date("Y-m-d H:i:s");
            $enddate = @date("Y-m-d");
            if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1) {
                $enddate = $enddate . " " . $vToTimeSlot_1 . ":00";
            } else {
                $enddate = $enddate . " " . $vToTimeSlot_2 . ":00";
            }

            $datediff = strtotime($enddate) - strtotime($currentdate);
            if ($datediff < 900) {
                $closein = $languageLabelsArr['LBL_RESTAURANT_CLOSE_MINS_TXT'];
                $closemins = round($datediff / 60);
                $closetime = $closein . " " . $closemins . " " . $languageLabelsArr['LBL_MINS_SMALL'];
            }
        } else {
            $newdate = @date("Y-m-d");

            // $newdate = $newdate." ".$vFromTimeSlot_2.":00";
            if (isBetween($vFromTimeSlot_1, $vFromTimeSlot_1, $date) == 1) {
                $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
            } else {
                if ($vFromTimeSlot_1 < $vFromTimeSlot_2 && $vFromTimeSlot_1 > $date) {
                    $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                } else {
                    $newdate = ($vFromTimeSlot_2 == "00:00") ? $newdate . " " . $vFromTimeSlot_1 . ":00" : $newdate . " " . $vFromTimeSlot_2 . ":00";
                }
            }

            $currentdate = @date("Y-m-d H:i:s");
            $datediff = strtotime($newdate) - strtotime($currentdate);
            if ($datediff > 0) {
                $opentime = $OpenAt . " " . date("h:i a", strtotime($newdate));
            }
        }

        $eAvailable = $Datasql[0]['eAvailable'];
        $eLogout = $Datasql[0]['eLogout'];
        if ($eAvailable == "No" || $eLogout == "Yes" || $eStatus != "Active") {
            $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];

            // $opentime = "";
            $closetime = "";
            $status = "closed";
        }

        $returnArr['status'] = $status_display;
        $returnArr['opentime'] = $opentime;
        $returnArr['closetime'] = $closetime;
        $returnArr['restaurantstatus'] = $status;
        $returnArr['timeslotavailable'] = $timeslotavailable;
    }

    // echo "<pre>";print_r($returnArr);
    date_default_timezone_set($serverTimeZone);
    return $returnArr;
}

function isBetween($from, $till, $input) {
    $f = DateTime::createFromFormat('!H:i', $from);
    $t = DateTime::createFromFormat('!H:i', $till);
    $i = DateTime::createFromFormat('!H:i', $input);
    if ($f > $t)
        $t->modify('+1 day');
    return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
}

function getCompanyDetails($iCompanyId, $iUserId, $CheckNonVegFoodType = "", $searchword = "", $iServiceId_new = "", $passengerLat = "", $passengerLon = "") {
    global $obj, $generalobj, $tconfig, $iServiceId, $ENABLE_FAVORITE_STORE_MODULE;
    $currencycode = isset($_REQUEST["vGeneralCurrency"]) ? $_REQUEST["vGeneralCurrency"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Currency Code
    if (!empty($iServiceId_new)) {
        $iServiceId = $iServiceId_new;
    }
    $Ratio = 1;
    $vLanguage = $currencySymbol = ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Currency Code
    if ($iUserId != "" && $iUserId > 0) {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
    } else {
        //Added By HJ On 23-01-2020 For Get Currency Data Start
        if (($currencySymbol == "" || $currencySymbol == NULL) && $currencycode != "") {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE vName = '" . $currencycode . "'");
        } else {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'");
        }
        if (count($currencyData) > 0) {
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        } else {
            $currencycode = "USD";
            $currencySymbol = "$";
            $Ratio = 1.0000;
        }
    }
    $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';
    if (!empty($vGeneralLang) && $vLanguage == "") {
        $vLanguage = $vGeneralLang;
    }
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    //Added By HJ On 23-01-2020 For Get Currency Data End
    //echo $currencySymbol;die;
    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    $LBL_PER_PERSON_TXT = $languageLabelsArr['LBL_PER_PERSON_TXT'];
    $ssql_fav_q = "";
    if (checkFavStoreModule() && !empty($iUserId)) {
        $ssql_fav_q = getFavSelectQuery($iCompanyId, $iUserId);
    }
    $DataCompany = $obj->MySQLSelect("SELECT * " . $ssql_fav_q . " FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'");
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration Start
    /* if (isset($DataCompany[0]['vCurrencyCompany']) && $DataCompany[0]['vCurrencyCompany'] != "") {
      $store_currency = $DataCompany[0]['vCurrencyCompany'];
      if (isset($currencyArr[$store_currency])) {
      $priceRatio = $currencyArr[$store_currency];
      $currencySymbol = $currencySymbolArr[$store_currency];
      $Ratio = $priceRatio;
      }
      } */

    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration End

    if ($iServiceId == 1) {
        if (isset($DataCompany[0]['fPricePerPerson'])) {
            $personprice = $DataCompany[0]['fPricePerPerson'];
            $PersonPrice = $generalobj->setTwoDecimalPoint($personprice * $Ratio);
            $returnArr['fPricePerPersonWithCurrency'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($PersonPrice);
        }

        $fPricePerPerson = $DataCompany[0]['fPricePerPerson'];
        $fPricePerPerson = $generalobj->setTwoDecimalPoint($fPricePerPerson * $Ratio);
        $fPricePerPerson = $currencySymbol . "" . $fPricePerPerson . " " . $LBL_PER_PERSON_TXT;
        $returnArr['Restaurant_PricePerPerson'] = $fPricePerPerson;
    } else {
        $returnArr['fPricePerPersonWithCurrency'] = $returnArr['Restaurant_PricePerPerson'] = '';
    }
    $CompanyTimeSlot = getCompanyTimeSlot($iCompanyId, $languageLabelsArr);
    $returnArr['monfritimeslot_TXT'] = $CompanyTimeSlot['monfritimeslot_TXT'];
    $returnArr['monfritimeslot_Time'] = $CompanyTimeSlot['monfritimeslot_Time_new'];
    $returnArr['satsuntimeslot_TXT'] = $CompanyTimeSlot['satsuntimeslot_TXT'];
    $returnArr['satsuntimeslot_Time'] = $CompanyTimeSlot['satsuntimeslot_Time_new'];
    $sql = "SELECT cu.cuisineName_" . $vLanguage . " as cuisineName,cu.cuisineId FROM cuisine as cu LEFT JOIN company_cuisine as ccu ON ccu.cuisineId=cu.cuisineId WHERE ccu.iCompanyId = '" . $iCompanyId . "' AND cu.eStatus = 'Active'";
    $db_cuisine = $obj->MySQLSelect($sql);
    $db_cuisine_str = $db_cuisine_id_str = $MaxDiscountAmount = "";
    if (count($db_cuisine) > 0) {
        for ($i = 0; $i < count($db_cuisine); $i++) {
            $db_cuisine_str .= $db_cuisine[$i]['cuisineName'] . ", ";
            $db_cuisine_id_str .= $db_cuisine[$i]['cuisineId'] . ",";
        }
        $db_cuisine_str = trim(trim($db_cuisine_str), ",");
        $db_cuisine_id_str = trim($db_cuisine_id_str, ",");
    }

    $returnArr['Restaurant_Cuisine'] = $db_cuisine_str;
    $returnArr['Restaurant_Cuisine_Id'] = $db_cuisine_id_str;
    $LBL_MINS_SMALL = $languageLabelsArr['LBL_MINS_SMALL'];
    $fPrepareTime = $DataCompany[0]['fPrepareTime'];
    $fPrepareTime = $fPrepareTime . " " . $LBL_MINS_SMALL;
    $returnArr['Restaurant_OrderPrepareTime'] = $fPrepareTime;
    $fOfferType = $DataCompany[0]['fOfferType'];
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferAmt = $DataCompany[0]['fOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    $fTargetAmt = $generalobj->setTwoDecimalPoint($fTargetAmt * $Ratio);
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fMaxOfferAmt = $generalobj->setTwoDecimalPoint($fMaxOfferAmt * $Ratio);
    $MaxDiscountAmount = "";
    if ($fMaxOfferAmt > 0) {
        $MaxDiscountAmount = " ( " . $languageLabelsArr['LBL_MAX_DISCOUNT_TXT'] . " " . $currencySymbol . "" . $fMaxOfferAmt . " )";
    }
    $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'];
    $ALL_ORDER_TXT = $languageLabelsArr['LBL_ALL_ORDER_TXT'];
    if ($fTargetAmt > 0) {
        $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'] . " " . $languageLabelsArr['LBL_ORDERS_ABOVE_TXT'] . " " . $currencySymbol . "" . $fTargetAmt . " ";
        $ALL_ORDER_TXT = "";
    }
    $offermsg = $offermsg_short = "";
    if ($fOfferType == "Percentage") {
        if ($fOfferAppyType == "First") {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . $languageLabelsArr['LBL_FIRST_ORDER_TXT'] . "" . $MaxDiscountAmount;
            $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
        } elseif ($fOfferAppyType == "All") {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT . " " . $MaxDiscountAmount;
            $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
        }
    } else {
        $fOfferAmt = $generalobj->setTwoDecimalPoint($fOfferAmt * $Ratio);
        $DiscountAmount = $currencySymbol . "" . $fOfferAmt;
        if ($fOfferAppyType == "First" && $fOfferAmt > 0) {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
            $offermsg_short = $offermsg;
        } else if ($fOfferAppyType == "All" && $fOfferAmt > 0) {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
            $offermsg_short = $offermsg;
        }
    }
    $returnArr['Restaurant_OfferMessage'] = $offermsg;
    $returnArr['Restaurant_OfferMessage_short'] = $offermsg_short;
    $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
    $fMinOrderValue = $generalobj->setTwoDecimalPoint($fMinOrderValue * $Ratio);
    $returnArr['fMinOrderValueDisplay'] = $currencySymbol . " " . $fMinOrderValue;
    $returnArr['fMinOrderValue'] = $fMinOrderValue;
    $returnArr['Restaurant_MinOrderValue'] = ($fMinOrderValue > 0) ? $currencySymbol . $fMinOrderValue . " " . $languageLabelsArr['LBL_MIN_ORDER_TXT'] : $languageLabelsArr['LBL_NO_MIN_ORDER_TXT'];
    $returnArr['Restaurant_MinOrderValue_Orig'] = ($fMinOrderValue > 0) ? $currencySymbol . $fMinOrderValue : '';
    $fPackingCharge = $DataCompany[0]['fPackingCharge'];
    $fPackingCharge = $generalobj->setTwoDecimalPoint($fPackingCharge * $Ratio);
    $returnArr['fPackingCharge'] = $fPackingCharge;

    // echo "<pre>";print_r($returnArr);
    ## Check NonVeg Item Available of Restaaurant ##
    $eNonVegToggleDisplay = "No";
    $sql = "SELECT eFoodType,mi.eStatus,mi.eAvailable,mi.iFoodMenuId FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active'";
    $db_foodtype_data = $obj->MySQLSelect($sql);
    $TotNonVegItems = $TotVegItems = 0;
    $foodItemCountArr = array();
    //echo "<pre>";print_r($db_foodtype_data);die;
    for ($r = 0; $r < count($db_foodtype_data); $r++) {
        $eFoodType = strtoupper($db_foodtype_data[$r]['eFoodType']);
        $iFoodMenuId = $db_foodtype_data[$r]['iFoodMenuId'];
        $eStatus = $db_foodtype_data[$r]['eStatus'];
        $eAvailable = $db_foodtype_data[$r]['eAvailable'];

        if ($eFoodType == "NONVEG") {
            $TotNonVegItems = $TotNonVegItems + 1;
        } else if ($eFoodType == "VEG") {
            $TotVegItems = $TotVegItems + 1;
        }
        if ($eStatus == "Active" && $eAvailable == "Yes") {
            if (isset($foodItemCountArr[$iFoodMenuId])) {
                $foodItemCountArr[$iFoodMenuId] += 1;
            } else {
                $foodItemCountArr[$iFoodMenuId] = 1;
            }
        }
    }

    /* $TotNonVegItems = $db_foodtype_data[0]['TotNonVegItems'];
      $sql = "SELECT count(mi.iMenuItemId) As TotVegItems FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active' AND mi.eFoodType = 'Veg'";
      $db_vegfoodtype_data = $obj->MySQLSelect($sql);
      $TotVegItems = $db_vegfoodtype_data[0]['TotVegItems']; */

    //if($CheckNonVegFoodType=='')
    if ($TotNonVegItems > 0 && $TotVegItems > 0) {
        $eNonVegToggleDisplay = "Yes";
    }
    $returnArr['eNonVegToggleDisplay'] = $eNonVegToggleDisplay;
    ## Check NonVeg Item Available of Restaaurant ##
    ## Get Company Rattings ##
    $rsql = "SELECT count(r.iRatingId) as totalratings FROM orders as o LEFT JOIN ratings_user_driver as r on r.iOrderId=o.iOrderId WHERE o.iCompanyId='" . $iCompanyId . "' AND r.eFromUserType='Passenger' AND r.eToUserType='Company'";
    $Rating_data = $obj->MySQLSelect($rsql);
    $ratingcounts = $Rating_data[0]['totalratings'];
    if ($ratingcounts <= 100) {
        $ratings = $ratingcounts . " " . $languageLabelsArr['LBL_RATING'];
    } else {
        $ratings = $ratingcounts . "+ " . $languageLabelsArr['LBL_RATING'];
    }
    $returnArr['RatingCounts'] = $ratings;
    ## End Get Company Rattings ##
    ## Get Company's menu details ##
    // $sql = "SELECT * FROM food_menu WHERE iCompanyId = '".$iCompanyId."' AND eStatus='Active' ORDER BY iDisplayOrder ASC";

    $sql = "SELECT fm.* FROM food_menu as fm WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' ORDER BY fm.iDisplayOrder ASC";
    $db_food_data = $obj->MySQLSelect($sql);
    $CompanyFoodData = $MenuItemsDataArr = array();
    if (count($db_food_data) > 0) {
        $ssql = "";

        //added by SP on 21-10-2019 for cubex design
        if ($CheckNonVegFoodType == 'Yes') {
            $ssql .= " AND (eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == 'No') {
            $ssql .= " AND (eFoodType = 'NonVeg'  OR eFoodType = 'Veg' OR eFoodType = '') ";
        }

        //old leave as it is bc if pass from app like this value then no problem in future..
        if ($CheckNonVegFoodType == "Veg") {
            $ssql .= " AND (eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "NonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "VegNonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "NonVegVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        }
        if ($searchword != "") {
            $ssql .= " AND LOWER(vItemType_" . $vLanguage . ") LIKE '%" . $searchword . "%' ";
        }
        $foodMenuIteIds = "";
        for ($h = 0; $h < count($db_food_data); $h++) {
            $foodMenuIteIds .= ",'" . $db_food_data[$h]['iFoodMenuId'] . "'";
        }
        $foodItemArr = $menuItemArr = $topingArr = array();
        if ($foodMenuIteIds != "") {
            $foodItems = trim($foodMenuIteIds, ",");
            //$sqlf = "SELECT eRecommended,iMenuItemId,iFoodMenuId,vItemType_" . $vLanguage . " as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName,prescription_required FROM menu_items WHERE iFoodMenuId IN ($foodItems) AND eStatus='Active' AND eAvailable = 'Yes' $ssql ORDER BY iDisplayOrder ASC"; //prescription_required added by SP

            $def_lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            $sqlf = "SELECT eRecommended,iMenuItemId,iFoodMenuId,IFNULL(NULLIF(vItemType_" . $vLanguage . ", ''),vItemType_" . $def_lang . ") as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName,prescription_required FROM menu_items WHERE iFoodMenuId IN ($foodItems) AND eStatus='Active' AND eAvailable = 'Yes' $ssql ORDER BY iDisplayOrder ASC"; //prescription_required added by SP

            $dbItemData = $obj->MySQLSelect($sqlf);
            //echo "<pre>";print_r($dbItemData);die;
            for ($d = 0; $d < count($dbItemData); $d++) {
                //Added By HJ On 17-10-2019 For Get Highlight Label Value Start
                $vHighlightNameLBL = $dbItemData[$d]['vHighlightName'];
                if (isset($languageLabelsArr[$dbItemData[$d]['vHighlightName']]) && $dbItemData[$d]['vHighlightName'] != "" && $dbItemData[$d]['vHighlightName'] != null) {
                    $vHighlightNameLBL = $languageLabelsArr[$dbItemData[$d]['vHighlightName']];
                }
                //echo $dbItemData[$d]['vHighlightName'];die;
                $dbItemData[$d]['vHighlightNameLBL'] = $dbItemData[$d]['vHighlightName'];
                $dbItemData[$d]['vHighlightName'] = $vHighlightNameLBL;
                //Added By HJ On 17-10-2019 For Get Highlight Label Value End
                //echo "<pre>";print_r($dbItemData);die;
                $foodItemArr[$dbItemData[$d]['iFoodMenuId']][] = $dbItemData[$d];
                $menuItemArr[] = $dbItemData[$d]['iMenuItemId'];
            }
        }
        //echo "<pre>";print_r($foodItemArr);die;
        if (count($menuItemArr) > 0) {
            $itemIds = implode(",", $menuItemArr);
            $topingArr = GetMenuItemOptionsTopping($itemIds, $currencySymbol, $Ratio, $vLanguage, $iServiceId, $iCompanyId);
            //echo "<pre>";print_r($topingArr);die;
            //echo $itemIds . "<br>";
            $customerTopingArr = getMenuCustomeAllToppings($itemIds, $currencySymbol, $Ratio, $vLanguage, 0);
            //echo "<pre>";print_r($customerTopingArr);die;
        }
        $itemimimgUrl = $tconfig["tsite_upload_images_menu_item"];
        //echo "<pre>";print_r($db_food_data);die;
        for ($i = 0; $i < count($db_food_data); $i++) {
            $iFoodMenuId = $db_food_data[$i]['iFoodMenuId'];
            if (isset($foodItemCountArr[$iFoodMenuId]) && $foodItemCountArr[$iFoodMenuId] > 0) {
                $vMenu = $db_food_data[$i]['vMenu_' . $vLanguage];
                $CompanyFoodData[$i]['iFoodMenuId'] = $iFoodMenuId;
                $CompanyFoodData[$i]['vMenu'] = $vMenu;
                $CompanyFoodData[$i]['vMenuItemCount'] = 0;
                if (isset($foodItemArr[$iFoodMenuId])) {
                    $db_item_data = $foodItemArr[$iFoodMenuId];
                    $CompanyFoodData[$i]['vMenuItemCount'] = count($db_item_data);
                    if (count($db_item_data) > 0) {
                        for ($j = 0; $j < count($db_item_data); $j++) {
                            $db_item_data[$j]['vCategoryName'] = '';
                            if (!empty($vMenu)) {
                                $db_item_data[$j]['vCategoryName'] = $vMenu;
                            }
                            $iMenuItemId = $db_item_data[$j]['iMenuItemId'];
                            $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, $iUserId, "Display", "", "", $iServiceId);
                            $fPrice = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fPrice'] * $Ratio);
                            $fOfferAmt = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fOfferAmt']);
                            $db_item_data[$j]['fOfferAmt'] = $fOfferAmt;
                            $db_item_data[$j]['fPrice'] = $generalobj->setTwoDecimalPoint($db_item_data[$j]['fPrice'] * $Ratio);
                            if ($fOfferAmt > 0) {
                                $fDiscountPrice = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fPrice'] * $Ratio);
                                $StrikeoutPrice = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fOriginalPrice'] * $Ratio);
                                $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
                                $db_item_data[$j]['fDiscountPrice'] = $fDiscountPrice;
                                $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
                                $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                            } else {
                                $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
                                $db_item_data[$j]['fDiscountPrice'] = $fPrice;
                                $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
                                $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                            } 
                            
                            //added by SP for offer amount
                            $StrikeoutPrice = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
                            $db_item_data[$j]['fToppingStrikeoutPrice'] = $StrikeoutPrice;
                            $db_item_data[$j]['fToppingStrikeoutPricewithsymbol'] = $currencySymbol . " " . $StrikeoutPrice;
                
                            //$MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, '', "Display", "", "", $iServiceId);
                            $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
                
                            $db_item_data[$j]['fToppingDiscountPrice'] = "";
                            $db_item_data[$j]['fToppingDiscountPricewithsymbol'] = "";
                            $db_item_data[$j]['isShownDiscountPrice'] = "No";
                            if ($fOfferAmt > 0) {
                                $fDiscountPrice = ($MenuItemPriceArr['fPrice'] * $Ratio);
                                //$fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt) / 100);
                                //$fDiscountPrice = $fDiscountPrice * $Ratio;
                                if (!empty($fDiscountPrice)) {
                                    $db_item_data[$j]['isShownDiscountPrice'] = "Yes";
                                    $fDiscountPrice = $generalobj->setTwoDecimalPoint($fDiscountPrice, 2);
                                    $db_item_data[$j]['fToppingDiscountPrice'] = $fDiscountPrice;
                                    $db_item_data[$j]['fToppingDiscountPricewithsymbol'] = $currencySymbol . " " . $fDiscountPrice;
                                }
                            }
            
                            $itemimgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $db_item_data[$j]['vImage'];
                            if ($db_item_data[$j]['vImage'] != "" && file_exists($itemimgpth)) {
                                $db_item_data[$j]['vImageName'] = $db_item_data[$j]['vImage'];
                                $db_item_data[$j]['vImage'] = $itemimimgUrl . '/' . $db_item_data[$j]['vImage'];
                            } else {
                                $db_item_data[$j]['vImageName'] = '';
                                $db_item_data[$j]['vImage'] = $itemimimgUrl . '/sample_image.png';
                            }
                            //$MenuItemOptionToppingArr = GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $iServiceId);
                            $MenuItemOptionToppingArr = $customeToppings = array();
                            if (isset($topingArr[$iMenuItemId])) {
                                $MenuItemOptionToppingArr = $topingArr[$iMenuItemId];
                            }
                            $db_item_data[$j]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
                            //Added By HJ On 25-01-2019 For Get Custome Topping Data Start
                            //$customeToppings = getMenuCustomeToppings($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, 0); //Commnted By HJ On 08-05-2019 For Optimize Code
                            if (isset($customerTopingArr[$iMenuItemId])) {
                                $customeToppings = $customerTopingArr[$iMenuItemId];
                            }
                            $db_item_data[$j]['MenuItemOptionToppingArr']['customItemArray'] = $customeToppings;
                            //Added By HJ On 25-01-2019 For Get Custome Topping Data End
                            // echo "<pre>";print_r($MenuItemOptionToppingArr);exit;
                            $CompanyFoodData[$i]['menu_items'][] = $db_item_data[$j];
                            array_push($MenuItemsDataArr, $db_item_data[$j]);
                        }
                    }
                }
            }
        }
    }

    $CompanyFoodData_New = array();
    $CompanyFoodData = array_values($CompanyFoodData);
    $CompanyFoodData_New = $CompanyFoodData;
    //echo "<pre>";print_r($CompanyFoodData);die;
    for ($i = 0; $i < count($CompanyFoodData); $i++) {
        //echo "<pre>";print_r($CompanyFoodData);die;
        $vMenuItemCount = $CompanyFoodData[$i]['vMenuItemCount'];
        if ($vMenuItemCount == 0) {
            unset($CompanyFoodData_New[$i]);
        }
    }

    $CompanyFoodData = array_values($CompanyFoodData_New);
    $returnArr['CompanyFoodData'] = $CompanyFoodData;
    $returnArr['CompanyFoodDataCount'] = count($CompanyFoodData);
    $returnArr['MenuItemsDataArr'] = array();
    if ($searchword != "") {
        $returnArr['MenuItemsDataArr'] = $MenuItemsDataArr;
    }
    $Recomendation_Arr = getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, "Recommended", $CheckNonVegFoodType, $searchword, $iServiceId, $vLanguage);
    //print_r($Recomendation_Arr);die;
    $returnArr['Recomendation_Arr'] = $Recomendation_Arr;
    ## Get Company's menu details ##

    return $returnArr;
}

function getCompanyOffer($iCompanyId, $iUserId, $fOfferAppyType, $fOfferType, $fOfferAmt, $fMaxOfferAmt) {
    global $obj, $generalobj, $tconfig;
    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $LBL_GET_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_GET_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $LBL_ALL_ORDER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_ALL_ORDER_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $LBL_FIRST_ORDER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_FIRST_ORDER_TXT', " and vCode='" . $vLanguage . "'", 'true');
    if ($fOfferType == "Percentage") {
        if ($fOfferAppyType == "First") {
            $offermsg = $LBL_GET_TXT . " " . $fOfferAmt . "% " . $LBL_FIRST_ORDER_TXT;
        } elseif ($fOfferAppyType == "All") {
            $offermsg = $LBL_GET_TXT . " " . $fOfferAmt . "% " . $LBL_ALL_ORDER_TXT;
        } else {
            $offermsg = "";
        }
    } else {
        
    }

    return $offermsg;
}

function getCompanyBySearchCuisine($iUserId, $SearchKeyword, $Restaurant_id_str = 0) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $vLanguage = "";
    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    }
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $langLabels = $obj->MySQLSelect("SELECT vValue,vLabel FROM language_label_other WHERE (vLabel='LBL_RESTAURANTS_TXT' || vLabel='LBL_RESTAURANT_TXT') AND vCode='" . $vLanguage . "'");
    $LBL_RESTAURANTS_TXT = "Restaurants";
    $LBL_RESTAURANT_TXT = "Restaurant";
    for ($l = 0; $l < count($langLabels); $l++) {
        $vLabel = $langLabels[$l]['vLabel'];
        $vValue = $langLabels[$l]['vValue'];
        if ($vLabel == "LBL_RESTAURANTS_TXT") {
            $LBL_RESTAURANTS_TXT = $vValue;
        }
        if ($vLabel == "LBL_RESTAURANT_TXT") {
            $LBL_RESTAURANT_TXT = $vValue;
        }
    }
    //$LBL_RESTAURANTS_TXT = get_value('language_label_other', 'vValue', 'vLabel', 'LBL_RESTAURANTS_TXT', " and vCode='" . $vLanguage . "'", 'true');
    //$LBL_RESTAURANT_TXT = get_value('language_label_other', 'vValue', 'vLabel', 'LBL_RESTAURANT_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $sql = "SELECT cuisineId, cuisineName_" . $vLanguage . " as cuisineName FROM cuisine WHERE eStatus='Active' AND cuisineName_" . $vLanguage . " LIKE '%" . $SearchKeyword . "%'";
    $CuisineDetail = $obj->MySQLSelect($sql);
    if (count($CuisineDetail) > 0) {
        $CuisineTotalRestaurant = $obj->MySQLSelect("SELECT count(iCompanyId) as TotalRestaurant,cuisineId FROM company_cuisine WHERE iCompanyId IN($Restaurant_id_str) GROUP BY cuisineId");
        $cuisineRestArr = array();
        for ($c = 0; $c < count($CuisineTotalRestaurant); $c++) {
            $cuisineRestArr[$CuisineTotalRestaurant[$c]['cuisineId']] = $CuisineTotalRestaurant[$c]['TotalRestaurant'];
        }
        //echo "<pre>";print_r($cuisineRestArr);die;
        for ($i = 0; $i < count($CuisineDetail); $i++) {
            $cuisineId = $CuisineDetail[$i]['cuisineId'];
            $cuisineName = $CuisineDetail[$i]['cuisineName'];
            //$sqlr = "SELECT count(iCompanyId) as TotalRestaurant,cuisineId FROM company_cuisine WHERE cuisineId = '" . $cuisineId . "' AND iCompanyId IN($Restaurant_id_str)";
            //$CuisineTotalRestaurant = $obj->MySQLSelect($sqlr);
            $TotalRestaurant = $CuisineTotalRestaurant[0]['TotalRestaurant'];
            $TotalRestaurant = 0;
            if (isset($cuisineRestArr[$cuisineId]) && $cuisineRestArr[$cuisineId] > 0) {
                $TotalRestaurant = $cuisineRestArr[$cuisineId];
            }
            if ($TotalRestaurant > 0) {
                $TotalRestaurantTxt = ($TotalRestaurant <= 1) ? $LBL_RESTAURANT_TXT : $LBL_RESTAURANTS_TXT;
                $returnArr[$i]['cuisineId'] = $cuisineId;
                $returnArr[$i]['cuisineName'] = $cuisineName;
                $returnArr[$i]['TotalRestaurant'] = $TotalRestaurant;
                $returnArr[$i]['TotalRestaurantWithLabel'] = $TotalRestaurant . " " . $TotalRestaurantTxt;
            }
        }
    }
    return $returnArr;
}

function getCompanyTimeSlot($iCompanyId, $languageLabelsArr) {
    global $obj, $generalobj, $tconfig;
    $sql = "SELECT vFromMonFriTimeSlot1,vToMonFriTimeSlot1,vFromMonFriTimeSlot2,vToMonFriTimeSlot2,vFromSatSunTimeSlot1,vToSatSunTimeSlot1,vFromSatSunTimeSlot2,vToSatSunTimeSlot2 FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompanyTime = $obj->MySQLSelect($sql);

    // print_R($DataCompanyTime);die;
    $vFromMonFriTimeSlot1 = substr($DataCompanyTime[0]['vFromMonFriTimeSlot1'], 0, -3);
    $vToMonFriTimeSlot1 = substr($DataCompanyTime[0]['vToMonFriTimeSlot1'], 0, -3);
    $vFromMonFriTimeSlot2 = substr($DataCompanyTime[0]['vFromMonFriTimeSlot2'], 0, -3);
    $vToMonFriTimeSlot2 = substr($DataCompanyTime[0]['vToMonFriTimeSlot2'], 0, -3);
    $vFromSatSunTimeSlot1 = substr($DataCompanyTime[0]['vFromSatSunTimeSlot1'], 0, -3);
    $vToSatSunTimeSlot1 = substr($DataCompanyTime[0]['vToSatSunTimeSlot1'], 0, -3);
    $vFromSatSunTimeSlot2 = substr($DataCompanyTime[0]['vFromSatSunTimeSlot2'], 0, -3);
    $vToSatSunTimeSlot2 = substr($DataCompanyTime[0]['vToSatSunTimeSlot2'], 0, -3);
    $vFromMonFriTimeSlotNew1 = date("g:i a", strtotime($vFromMonFriTimeSlot1));
    $vToMonFriTimeSlotNew1 = date("g:i a", strtotime($vToMonFriTimeSlot1));
    $vFromMonFriTimeSlotNew2 = date("g:i a", strtotime($vFromMonFriTimeSlot2));
    $vToMonFriTimeSlotNew2 = date("g:i a", strtotime($vToMonFriTimeSlot2));
    $vFromSatSunTimeSlotNew1 = date("g:i a", strtotime($vFromSatSunTimeSlot1));
    $vToSatSunTimeSlotNew1 = date("g:i a", strtotime($vToSatSunTimeSlot1));
    $vFromSatSunTimeSlotNew2 = date("g:i a", strtotime($vFromSatSunTimeSlot2));
    $vToSatSunTimeSlotNew2 = date("g:i a", strtotime($vToSatSunTimeSlot2));
    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 == "00:00" && $vFromMonFriTimeSlot2 == "00:00" && $vToMonFriTimeSlot2 == "00:00") {
        $monfritimeslot_TXT = "";
        $monfritimeslot_Time = "";
        $monfritimeslot_Time_new = "";
    }

    if ($vFromMonFriTimeSlot1 != "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }

    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }

    if ($vFromMonFriTimeSlot1 != "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 == "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }

    if ($vFromMonFriTimeSlot1 != "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 == "00:00" && $vToMonFriTimeSlot2 == "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1;
    }

    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 == "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }

    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 == "00:00" && $vFromSatSunTimeSlot2 == "00:00" && $vToSatSunTimeSlot2 == "00:00") {
        $satsuntimeslot_TXT = "";
        $satsuntimeslot_Time = "";
        $satsuntimeslot_Time_new = "";
    }

    if ($vFromSatSunTimeSlot1 != "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }

    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }

    if ($vFromSatSunTimeSlot1 != "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 == "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }

    if ($vFromSatSunTimeSlot1 != "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 == "00:00" && $vToSatSunTimeSlot2 == "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1;
    }

    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 == "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }

    $returnArr['monfritimeslot_TXT'] = $monfritimeslot_TXT;
    $returnArr['monfritimeslot_Time'] = $monfritimeslot_Time;
    $returnArr['monfritimeslot_Time_new'] = $monfritimeslot_Time_new;
    $returnArr['satsuntimeslot_TXT'] = $satsuntimeslot_TXT;
    $returnArr['satsuntimeslot_Time'] = $satsuntimeslot_Time;
    $returnArr['satsuntimeslot_Time_new'] = $satsuntimeslot_Time_new;
    return $returnArr;
}

function GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $iServiceId = '', $iCompanyId = '') {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $ispriceshow = '';
    if (isset($iServiceId) && !empty($iServiceId)) {
        $servFields = 'eType';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
                $ispriceshow = $ServiceCategoryData[0]['eType'];
            }
        }
    }
    if (!empty($ispriceshow)) {
        $sql = "SELECT mo.iOptionId,mo.vOptionName,IF(mo.eDefault='Yes' AND mo.eOptionType='Options',mi.fprice,mo.fprice) as fPrice,mo.eOptionType,mo.eDefault,mo.iMenuItemId, mi.fOfferAmt FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId = mi.iMenuItemId WHERE mo.iMenuItemId IN ($iMenuItemId) AND mo.eStatus = 'Active'";
        //$sql = "SELECT iOptionId,vOptionName,fPrice,eOptionType,eDefault FROM menuitem_options WHERE iMenuItemId = '" . $iMenuItemId . "' AND eStatus = 'Active'";
    } else {
        $sql = "SELECT iOptionId,vOptionName,fPrice,eOptionType,eDefault,iMenuItemId FROM menuitem_options WHERE iMenuItemId IN ($iMenuItemId) AND eStatus = 'Active'";
    }

    $db_options_data = $obj->MySQLSelect($sql);
    if (count($db_options_data) > 0) {
        for ($i = 0; $i < count($db_options_data); $i++) {
            $fPrice = $db_options_data[$i]['fPrice'];
            $iMenuItemId = $db_options_data[$i]['iMenuItemId'];
            $fUserPrices = $fPrice * $Ratio;
            $fUserPrice = round($fPrice * $Ratio, 2);
            //$fUserPrice = number_format($fPrice * $Ratio, 2);
            $fUserPriceWithSymbol = $currencySymbol . " " . number_format($fUserPrices, 2);
            $db_options_data[$i]['fUserPrice'] = strval($fUserPrice);
            $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;

            //added by SP for offer amount
            $StrikeoutPrice = $generalobj->setTwoDecimalPoint($fPrice * $Ratio, 2);
            $db_options_data[$i]['fToppingStrikeoutPrice'] = $StrikeoutPrice;
            $db_options_data[$i]['fToppingStrikeoutPricewithsymbol'] = $currencySymbol . " " . $StrikeoutPrice;

            $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, '', "Display", "", "", $iServiceId);
            $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);

            $db_options_data[$i]['fToppingDiscountPrice'] = "";
            $db_options_data[$i]['fToppingDiscountPricewithsymbol'] = "";
            if ($fOfferAmt > 0) {
                $fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt) / 100);
                $fDiscountPrice = $fDiscountPrice * $Ratio;
                if (!empty($fDiscountPrice)) {
                    $fDiscountPrice = $generalobj->setTwoDecimalPoint($fDiscountPrice, 2);
                    $db_options_data[$i]['fToppingDiscountPrice'] = $fDiscountPrice;
                    $db_options_data[$i]['fToppingDiscountPricewithsymbol'] = $currencySymbol . " " . $fDiscountPrice;
                }
            }

            if ($db_options_data[$i]['eOptionType'] == "Options") {
                $returnArr[$iMenuItemId]['options'][] = $db_options_data[$i];
            }

            if ($db_options_data[$i]['eOptionType'] == "Addon") {
                $returnArr[$iMenuItemId]['addon'][] = $db_options_data[$i];
            }
        }
    }

    //echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

function getUserCurrencyLanguageDetails($iUserId = "", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($iUserId != "") {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($iOrderId > 0) {
            $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
            $CurrencyData = $obj->MySQLSelect($sql);
            $Ratio = $CurrencyData[0]['Ratio'];
        }

        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }

    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    return $returnArr;
}

function getDriverCurrencyLanguageDetails($iDriverId = "", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($iDriverId != "") {
        $sqlp = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iDriverId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyDriver'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($iOrderId > 0) {
            $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
            $CurrencyData = $obj->MySQLSelect($sql);
            $Ratio = $CurrencyData[0]['Ratio'];
        }

        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }

    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    return $returnArr;
}

function getCompanyCurrencyLanguageDetails($iCompanyId = "", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($iCompanyId != "") {
        $sqlp = "SELECT co.vCurrencyCompany,co.vLang,cu.vSymbol,cu.Ratio FROM company as co LEFT JOIN currency as cu ON co.vCurrencyCompany = cu.vName WHERE iCompanyId = '" . $iCompanyId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyCompany'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($iOrderId > 0) {
            $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
            $CurrencyData = $obj->MySQLSelect($sql);
            $Ratio = $CurrencyData[0]['Ratio'];
        }

        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }

    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    return $returnArr;
}

function GetAllMenuItemOptionsTopping($iCompanyId, $currencySymbol, $Ratio, $vLanguage, $eFor = "", $iServiceId = "") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $returnArr['options'] = $returnArr['addon'] = $finalReturnArr = $subItemArr = array();
    $ssql = "";
    if ($eFor == "Display") {
        $ssql .= " AND mo.eStatus = 'Active' ";
    }
    $sql = "SELECT mo.*,mi.fOfferAmt,fm.iFoodMenuId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '" . $iCompanyId . "' AND mi.eStatus='Active' AND fm.eStatus = 'Active' AND mi.eAvailable = 'Yes'" . $ssql; //Comment
    //echo $sql;die;
    $db_options_data = $obj->MySQLSelect($sql); //Comment
    //echo "<pre>";print_r($db_options_data); exit;
    if (count($db_options_data) > 0) {
        for ($i = 0; $i < count($db_options_data); $i++) {
            $fPrice = $db_options_data[$i]['fPrice'];
            $eOptionType = $db_options_data[$i]['eOptionType'];
            $fUserPrice = $generalobj->setTwoDecimalPoint($fPrice * $Ratio);
            $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
            $db_options_data[$i]['fUserPrice'] = $fUserPrice;
            $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;

            //$fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
            
            
            //added by SP for offer amount
            $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($db_options_data[$i]['iMenuItemId'], $iCompanyId, 1, '', "Display", "", "", $iServiceId);
            $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
            
            $ispriceshow = '';
            if (isset($iServiceId) && !empty($iServiceId)) {
                $servFields = 'eType';
                $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
                if (!empty($ServiceCategoryData)) {
                    if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
                        $ispriceshow = $ServiceCategoryData[0]['eType'];
                    }
                }
            }
            
            $StrikeoutPrice = $generalobj->setTwoDecimalPoint($fPrice * $Ratio, 2);
            if (isset($ispriceshow) && !empty($ispriceshow)) {
                $fPrice_separate = 0;
                if ($fPrice == 0) {
                    $fPrice_separate = $MenuItemPriceArr['fPrice'];
                    $StrikeoutPrice = $generalobj->setTwoDecimalPoint($fPrice_separate * $Ratio, 2);
                    $db_options_data[$i]['fPrice'] = $fPrice_separate;
                    $fUserPrice = $generalobj->setTwoDecimalPoint($fPrice_separate * $Ratio);
                    $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
                    $db_options_data[$i]['fUserPrice'] = $fUserPrice;
                    $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
                }
            }
            
            $db_options_data[$i]['fToppingStrikeoutPrice'] = $StrikeoutPrice;
            $db_options_data[$i]['fToppingStrikeoutPricewithsymbol'] = $currencySymbol . " " . $StrikeoutPrice;

            $db_options_data[$i]['fToppingDiscountPrice'] = "";
            $db_options_data[$i]['fToppingDiscountPricewithsymbol'] = "";
            $db_options_data[$i]['isShownDiscountPrice'] = "No";
            
            if ($fOfferAmt > 0) {
                $fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt) / 100);
                $fDiscountPrice = $fDiscountPrice * $Ratio;
                if (!empty($fDiscountPrice)) {
                    $fDiscountPrice = $generalobj->setTwoDecimalPoint($fDiscountPrice, 2);
                    $db_options_data[$i]['fToppingDiscountPrice'] = $fDiscountPrice;
                    $db_options_data[$i]['fToppingDiscountPricewithsymbol'] = $currencySymbol . " " . $fDiscountPrice;
                    $db_options_data[$i]['isShownDiscountPrice'] = "Yes";
                }
            }
            //added by SP for offer amount
            //$MenuItemPriceArr = getMenuItemPriceByCompanyOffer($db_options_data[$i]['iMenuItemId'], $iCompanyId, 1, '', "Display", "", "", $iServiceId);
            //$fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
            ////$fOfferAmt = round($db_options_data[$i]['fOfferAmt'], 2);
            //
            //$StrikeoutPrice = $generalobj->setTwoDecimalPoint($fPrice * $Ratio, 2);
            //$db_options_data[$i]['StrikeoutPrice'] = $StrikeoutPrice;
            //$db_options_data[$i]['StrikeoutPricewithsymbol'] = $currencySymbol . " " . $StrikeoutPrice;
            //
            //$db_options_data[$i]['fDiscountPrice'] = "";
            //$db_options_data[$i]['fDiscountPricewithsymbol'] = "";
            //if ($fOfferAmt > 0) {
            //    $fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt) / 100);
            //    $fDiscountPrice = $fDiscountPrice * $Ratio;
            //    if (!empty($fDiscountPrice)) {
            //        $fDiscountPrice = $generalobj->setTwoDecimalPoint($fDiscountPrice, 2);
            //        $db_options_data[$i]['fDiscountPrice'] = $fDiscountPrice;
            //        $db_options_data[$i]['fDiscountPricewithsymbol'] = $currencySymbol . " " . $fDiscountPrice;
            //    }
            //}

            if ($eOptionType == "Options") {
                $returnArr['options'][] = $db_options_data[$i];
            } else if ($eOptionType == "Addon") {
                $returnArr['addon'][] = $db_options_data[$i];
            } else { // Added By HJ On 21-05-2019 For Get Custome Topping Data Start
                $eOptionType = $db_options_data[$i]['eOptionType'];
                $eOptionInputType = $db_options_data[$i]['eOptionInputType'];
                $vOptionMinSelection = $db_options_data[$i]['vOptionMinSelection'];
                $vOptionMaxSelection = $db_options_data[$i]['vOptionMaxSelection'];
                $iFoodMenuId = $db_options_data[$i]['iFoodMenuId'];
                $iMenuItemId = $db_options_data[$i]['iMenuItemId'];
                $fPrice = $db_options_data[$i]['fPrice'];
                $fUserPrice = $generalobj->setTwoDecimalPoint($fPrice * $Ratio);
                $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
                $db_options_data[$i]['fUserPrice'] = $fUserPrice;
                $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;

                //$subItemArr[$iMenuItemId]['eOptionType'] = $eOptionType;
                $subItemArr[$iMenuItemId]['iMenuItemId'] = $iMenuItemId;
                $subItemArr[$iMenuItemId]['iFoodMenuId'] = $iFoodMenuId;
                $subItemArr[$iMenuItemId]['eOptionInputType'] = $eOptionInputType;
                $subItemArr[$iMenuItemId]['vOptionMinSelection'] = $vOptionMinSelection;
                $subItemArr[$iMenuItemId]['vOptionMaxSelection'] = $vOptionMaxSelection;
                $subItemArr[$iMenuItemId]['subItemArr'][] = $db_options_data[$i];
                // Added By HJ On 21-05-2019 For Get Custome Topping Data End
            }
        }
    }
    //echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

function GetUserSelectedLastOrderAddressCompanyLocationWise($iUserId, $eUserType = "Passenger", $passengerLat, $passengerLon, $iCompanyId, $iUserAddressId = "") {
    global $obj, $generalobj, $tconfig, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $ToTalAddress = 0;
    if ($iUserId == "" || $iUserId == 0 || $iUserId == NULL) {
        return $ToTalAddress;
    }
    $UserType = "Driver";
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    }
    $ssql_user_address = "";
    if ($iUserAddressId != "" && $iUserAddressId > 0) {
        $ssql_user_address = " AND user_address.iUserAddressId = " . $iUserAddressId;
    }
    $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
    $db_companydata = $obj->MySQLSelect($sql);
    $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
    $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
    $sql_userAddress = "SELECT ROUND(( 6371 * acos( cos( radians(cmp.vRestuarantLocationLat) )
            * cos( radians( ROUND(user_address.vLatitude ,8) ) )
            * cos( radians( ROUND(user_address.vLongitude,8) ) - radians(cmp.vRestuarantLocationLong) )
            + sin( radians(cmp.vRestuarantLocationLat) )
            * sin( radians( ROUND(user_address.vLatitude,8) ) ) ) ),2) AS distance, user_address.*  FROM `user_address`, `company` as cmp
            WHERE (user_address.vLatitude != '' AND user_address.vLatitude != '' AND user_address.eStatus='Active' AND user_address.iUserId='" . $iUserId . "' AND cmp.iCompanyId = '" . $iCompanyId . "' " . $ssql_user_address . ")
            HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY user_address.iUserAddressId DESC LIMIT 0,1";
    $UserAddressData = $obj->MySQLSelect($sql_userAddress);

    if ((empty($UserAddressData) || count($UserAddressData) == 0) && !empty($ssql_user_address)) {
        $ssql_user_address = "";
        $sql_userAddress = "SELECT ROUND(( 6371 * acos( cos( radians(cmp.vRestuarantLocationLat) )
            * cos( radians( ROUND(user_address.vLatitude ,8) ) )
            * cos( radians( ROUND(user_address.vLongitude,8) ) - radians(cmp.vRestuarantLocationLong) )
            + sin( radians(cmp.vRestuarantLocationLat) )
            * sin( radians( ROUND(user_address.vLatitude,8) ) ) ) ),2) AS distance, user_address.*  FROM `user_address`, `company` as cmp
            WHERE (user_address.vLatitude != '' AND user_address.vLatitude != '' AND user_address.eStatus='Active' AND user_address.iUserId='" . $iUserId . "' AND cmp.iCompanyId = '" . $iCompanyId . "' " . $ssql_user_address . ")
            HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY user_address.iUserAddressId DESC LIMIT 0,1";
        $UserAddressData = $obj->MySQLSelect($sql_userAddress);
    }

    $UserSelectedAddressArr = array();
    if (count($UserAddressData) > 0) {
        $vAddressType = $UserAddressData[0]['vAddressType'];
        $vBuildingNo = $UserAddressData[0]['vBuildingNo'];
        $vLandmark = $UserAddressData[0]['vLandmark'];
        $vServiceAddress = $UserAddressData[0]['vServiceAddress'];
        $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
        $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
        $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
        $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
        $PickUpLatitude = $UserAddressData[0]['vLatitude'];
        $PickUpLongitude = $UserAddressData[0]['vLongitude'];
        $UserSelectedAddressArr['UserSelectedAddress'] = $PickUpAddress;
        $UserSelectedAddressArr['UserSelectedLatitude'] = $PickUpLatitude;
        $UserSelectedAddressArr['UserSelectedLongitude'] = $PickUpLongitude;
        $UserSelectedAddressArr['UserSelectedAddressId'] = $UserAddressData[0]['iUserAddressId'];
    }
    return $UserSelectedAddressArr;
}

function GenerateUniqueOrderNo() {
    global $generalobj, $obj, $tconfig;
    $random = substr(number_format(time() * rand(), 0, '', ''), 0, 10);
    $str = "select iOrderId from orders where vOrderNo ='" . $random . "'";
    $db_str = $obj->MySQLSelect($str);
    if (!empty($db_str) && count($db_str) > 0) {
        $Generateuniqueorderno = GenerateUniqueOrderNo();
    } else {
        $Generateuniqueorderno = $random;
    }

    return $Generateuniqueorderno;
}

function GenerateUniqueTripNo() {
    global $generalobj, $obj, $tconfig;
    $random = substr(number_format(time() * rand(), 0, '', ''), 0, 10);
    $str = "select iTripId from trips where vRideNo ='" . $random . "'";
    $db_str = $obj->MySQLSelect($str);
    if (!empty($db_str) && count($db_str) > 0) {
        $Generateuniqueorderno = GenerateUniqueTripNo();
    } else {
        $Generateuniqueorderno = $random;
    }

    return $Generateuniqueorderno;
}

function FoodMenuItemBasicPrice($iMenuItemId, $iQty = 1) {
    global $generalobj, $obj, $tconfig;
    $fPrice = 0;
    $str = "select fPrice from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
    $db_price = $obj->MySQLSelect($str);
    if (count($db_price) > 0) {
        $fPrice = $db_price[0]['fPrice'];
        $fPrice = $fPrice * $iQty;
    }

    return $fPrice;
}

function GetFoodMenuItemBasicPrice($iMenuItemId) {
    global $generalobj, $obj, $tconfig;
    $str = "select iFoodMenuId,fPrice,fOfferAmt from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
    $db_price = $obj->MySQLSelect($str);
    $fPrice = $db_price[0]['fPrice'];
    $fOfferAmt = $db_price[0]['fOfferAmt'];
    if ($fOfferAmt > 0) {
        $fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt) / 100);
    } else {
        $fDiscountPrice = $fPrice;
    }

    $fDiscountPrice = round($fDiscountPrice, 2);
    return $fDiscountPrice;
}

function GetFoodMenuItemOptionPrice($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    if ($iOptionId != "") {
        $str = "select iMenuItemId,fPrice from `menuitem_options` where iOptionId IN(" . $iOptionId . ")";
        $db_price = $obj->MySQLSelect($str);
        $fTotalPrice = 0;
        if (count($db_price) > 0) {
            for ($i = 0; $i < count($db_price); $i++) {
                $fPrice = $db_price[$i]['fPrice'];
                $fTotalPrice = $fTotalPrice + $fPrice;
            }
        }
    } else {
        $fTotalPrice = 0;
    }

    $fTotalPrice = round($fTotalPrice, 2);
    return $fTotalPrice;
}

function GetFoodMenuItemOptionIdPriceString($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    if ($iOptionId != "") {
        $vOptionIdArr = explode(",", $iOptionId);
        $OptionIdPriceString = "";
        if (count($vOptionIdArr) > 0) {
            for ($i = 0; $i < count($vOptionIdArr); $i++) {
                $OptionId = $vOptionIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $OptionIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }

            $OptionIdPriceString = substr($OptionIdPriceString, 0, -1);
        }
    } else {
        $OptionIdPriceString = "";
    }

    return $OptionIdPriceString;
}

function GetFoodMenuItemAddOnPrice($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    if ($vAddonId != "") {
        $str = "select iMenuItemId,fPrice from `menuitem_options` where iOptionId IN(" . $vAddonId . ")";
        $db_price = $obj->MySQLSelect($str);
        $fTotalPrice = 0;
        if (count($db_price) > 0) {
            for ($i = 0; $i < count($db_price); $i++) {
                $fPrice = $db_price[$i]['fPrice'];
                $fTotalPrice = $fTotalPrice + $fPrice;
            }
        }
    } else {
        $fTotalPrice = 0;
    }

    $fTotalPrice = round($fTotalPrice, 2);
    return $fTotalPrice;
}

function GetFoodMenuItemAddOnIdPriceString($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    if ($vAddonId != "") {
        $vAddonIdArr = explode(",", $vAddonId);
        $AddOnIdPriceString = "";
        if (count($vAddonIdArr) > 0) {
            for ($i = 0; $i < count($vAddonIdArr); $i++) {
                $OptionId = $vAddonIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $AddOnIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }

            $AddOnIdPriceString = substr($AddOnIdPriceString, 0, -1);
        }
    } else {
        $AddOnIdPriceString = "";
    }

    return $AddOnIdPriceString;
}

function DisplayFoodMenuItemAddOnIdPriceString($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    if ($vAddonId != "") {
        $vAddonIdArr = explode(",", $vAddonId);
        $AddOnIdPriceString = "";
        if (count($vAddonIdArr) > 0) {
            for ($i = 0; $i < count($vAddonIdArr); $i++) {
                $OptionId = $vAddonIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $AddOnIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }

            $AddOnIdPriceString = substr($AddOnIdPriceString, 0, -1);
        }
    } else {
        $AddOnIdPriceString = "";
    }

    return $AddOnIdPriceString;
}

function getOrderDetailTotalPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT SUM( `fTotalPrice` ) AS totalprice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $totalprice = $data[0]['totalprice'];
    if ($totalprice == "" || $totalprice == NULL) {
        $totalprice = 0;
    }

    return $totalprice;
}

function getOrderDeliveryCharge($iOrderId, $fSubTotal) {
    global $generalobj, $obj, $tconfig;
    $fDeliveryCharge = 0;
    $sql = "SELECT ord.iUserId,ord.iCompanyId,ua.vLatitude as passengerlat,ua.vLongitude as passengerlong,co.vRestuarantLocationLat as restaurantlat,co.vRestuarantLocationLong as restaurantlong,ord.eTakeaway FROM orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId LEFT JOIN company as co ON ord.iCompanyId=co.iCompanyId WHERE ord.iOrderId = '" . $iOrderId . "'";
    $data = $obj->MySQLSelect($sql);
    if (count($data) > 0) {

        if ($data[0]['eTakeaway'] == 'Yes') {
            $fDeliveryCharge = 0;
            return $fDeliveryCharge;
        }

        $User_Address_Array = array(
            $data[0]['passengerlat'],
            $data[0]['passengerlong']
        );
        $iLocationId = GetUserGeoLocationId($User_Address_Array);
        $checkAllLocation = 1;
        if ($iLocationId > 0) {
            $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '" . $iLocationId . "' AND eStatus='Active'";
            $data_location = $obj->MySQLSelect($sql);
            if (count($data_location) > 0) {
                $checkAllLocation = 0;
            }
        }
        if ($checkAllLocation == 1) {
            $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '0' AND eStatus='Active'";
            $data_location = $obj->MySQLSelect($sql);
        }
        if (count($data_location) > 0) {
            $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
            $distance = distanceByLocation($data[0]['passengerlat'], $data[0]['passengerlong'], $data[0]['restaurantlat'], $data[0]['restaurantlong'], "K");
            if ($distance < $iFreeDeliveryRadius && $iFreeDeliveryRadius >= 0 && !empty($iFreeDeliveryRadius)) { //when zero for free order and radius then do not allow free order
                $fDeliveryCharge = 0;
                return $fDeliveryCharge;
            }

            $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
            if ($fSubTotal > $fFreeOrderPriceSubtotal && $fFreeOrderPriceSubtotal != 0 && !empty($fFreeOrderPriceSubtotal)) { //when zero for free order and radius then do not allow free order
                $fDeliveryCharge = 0;
                return $fDeliveryCharge;
            }

            $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
            $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
            $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
            if ($fSubTotal >= $fOrderPriceValue) {
                $fDeliveryCharge = $fDeliveryChargeAbove;
                //$fDeliveryCharge = $fDeliveryChargeBelow;
                return $fDeliveryCharge;
            } else {
                $fDeliveryCharge = $fDeliveryChargeBelow;
                //$fDeliveryCharge = $fDeliveryChargeAbove;
                return $fDeliveryCharge;
            }
        } else {
            $fDeliveryCharge = 0;
            return $fDeliveryCharge;
        }
    }
}

function calculateOrderFare($iOrderId) {
    global $generalobj, $obj, $ADMIN_COMMISSION, $SYSTEM_PAYMENT_FLOW, $eWalletIgnore;
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $couponCode = $data_order[0]['vCouponCode'];
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iTripId = "";
    if (isset($data_order[0]['iTripId'])) {
        $iTripId = $data_order[0]['iTripId'];
    }
    $iStatusCode = $data_order[0]['iStatusCode'];
    $ePaymentOption = $data_order[0]['ePaymentOption'];
    $fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
    $fSubTotal = getOrderDetailTotalPrice($iOrderId);
    $fOffersDiscount = CalculateOrderDiscountPrice($iOrderId);
    $fDeliveryCharge = getOrderDeliveryCharge($iOrderId, $fSubTotal);
    if ($data_order[0]['eTakeAway'] == 'Yes') {
        $fDeliveryCharge = 0;
    }
    $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    $fTax = $TaxArr['fTax1'];
    if ($fTax > 0) {
        $ftaxamount = $fSubTotal - $fOffersDiscount + $fPackingCharge;
        $fTax = round((($ftaxamount * $fTax) / 100), 2);
    }

    if ($fSubTotal == 0) {
        $fPackingCharge = $fDeliveryCharge = $fTax = 0;
    }

    // $fTax = 0;
    // $fCommision = 0;
    $fNetTotal = $fSubTotal + $fPackingCharge + $fDeliveryCharge + $fTax;
    $fTotalGenerateFare = $fNetTotal;
    $fOrderFare_For_Commission = $fSubTotal - $fOffersDiscount + $fPackingCharge + $fTax;
    $fCommision = round((($fOrderFare_For_Commission * $ADMIN_COMMISSION) / 100), 2);
    if ($fOffersDiscount > 0) {
        $fNetTotal = $fNetTotal - $fOffersDiscount;
    }

    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = 0;
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    if ($fOutStandingAmount > 0) {
        $fNetTotal += $fOutStandingAmount;
        $fTotalGenerateFare += $fOutStandingAmount;
    }

    /* Checking For Passenger Outstanding Amount */
    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data Start
        $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eStatus='Active'");
        if (count($getCouponCode) > 0) {
            $discountValue = $getCouponCode[0]['fDiscount'];
            $discountValueType = $getCouponCode[0]['eType'];
        }
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data End
        //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
        //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
    }

    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $discountApplyOn = $fNetTotal - ($fDeliveryCharge + $fTax); // Added By sunita On 25-01-2020 As Per Discuss With chirag sir // Tax Minus From Coupon Code As Per Discuss With CD sir and KS Sir On 31-01-2020
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($discountApplyOn * $discountValue), 1) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $fNetTotal) {
                $vDiscount = round($fNetTotal, 1) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
            }
        }

        $fNetTotal = $fNetTotal - $discountValue;
        if ($fNetTotal < 0) {
            $fNetTotal = 0;

            // $discountValue = $fNetTotal;
        }

        $Order_data[0]['fDiscount'] = $discountValue;
        $Order_data[0]['vDiscount'] = $vDiscount;
    }

    /* Check Coupon Code Total Fare  End */
    /* Check debit wallet For Count Total Fare  Start */
    $CheckUserWallet = $data_order[0]['eCheckUserWallet'];
    $user_wallet_debit_amount = 0;
    if ($ePaymentOption == "Cash" && $CheckUserWallet == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal = 0;
        }

        // Update User Wallet
        if ($user_wallet_debit_amount > 0) {
            $vRideNo = $data_order[0]['vOrderNo'];
            $data_wallet['iUserId'] = $iUserId;
            $data_wallet['eUserType'] = "Rider";
            $data_wallet['iBalance'] = $user_wallet_debit_amount;
            $data_wallet['eType'] = "Debit";
            $data_wallet['dDate'] = date("Y-m-d H:i:s");
            $data_wallet['iTripId'] = $iTripId;
            $data_wallet['iOrderId'] = $iOrderId;
            $data_wallet['eFor'] = "Booking";
            $data_wallet['ePaymentStatus'] = "Unsettelled";
            $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING_DL#" . " " . $vRideNo;
            $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate'], $data_wallet['iOrderId']);

            // $obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
        }
    }
    /*     * ***** Checking wallet balance When method-2/method-3 ******* */
    if (($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') && $ePaymentOption != 'Cash') {
        $user_available_balance_wallet = $generalobj->get_user_available_balance($iUserId, "Rider", true);
        //Added By Hj On 07-08-2019 For Check User Authorized Amount As Per Discuss with KS Sir Start
        if (is_array($user_available_balance_wallet)) {
            $walletDataArr = $user_available_balance_wallet;
            $user_available_balance_wallet = $walletDataArr['CurrentBalance'];
        }
        //Added By Hj On 07-08-2019 For Check User Authorized Amount As Per Discuss with KS Sir End
        $user_wallet_debit_amount = 0;
        if ($fNetTotal > $user_available_balance_wallet) {
            if ($eWalletIgnore == 'Yes') {
                $fNetTotal = $fNetTotal - $user_available_balance_wallet;
                $user_wallet_debit_amount = $user_available_balance_wallet;
            }
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal = 0;
        }
        //echo $fNetTotal;die;
        // Update User Wallet
        if ($user_wallet_debit_amount > 0) {
            $vRideNo = $data_order[0]['vOrderNo'];
            $data_wallet['iUserId'] = $iUserId;
            $data_wallet['eUserType'] = "Rider";
            $data_wallet['iBalance'] = $user_wallet_debit_amount;
            $data_wallet['eType'] = "Debit";
            $data_wallet['dDate'] = date("Y-m-d H:i:s");
            $data_wallet['iTripId'] = $iTripId;
            $data_wallet['iOrderId'] = $iOrderId;
            $data_wallet['eFor'] = "Booking";
            $data_wallet['ePaymentStatus'] = "Unsettelled";
            $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING_DL#" . " " . $vRideNo;
            $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate'], $data_wallet['iOrderId']);
        }

        $where = " iOrderId = '" . $iOrderId . "'";
        $Data_update_order_new['iStatusCode'] = 1;
        $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order_new, 'update', $where);
        $OrderLogId = createOrderLog($iOrderId, $Data_update_order_new['iStatusCode']);
    }
    /*     * ***** Checking wallet balance When method-2/method-3 ******* */
    /* Check debit wallet For Count Total Fare  End */
    if ($fNetTotal < 0) {
        $fNetTotal = 0;
        $fTotalGenerateFare = 0;
    }

    $finalFareData['fSubTotal'] = $fSubTotal;
    $finalFareData['fOffersDiscount'] = $fOffersDiscount;
    $finalFareData['fPackingCharge'] = $fPackingCharge;
    $finalFareData['fDeliveryCharge'] = $fDeliveryCharge;
    $finalFareData['fTax'] = $fTax;
    $fDiscount = 0;
    $vDiscount = "";

    if (isset($Order_data[0]['fDiscount'])) {
        $fDiscount = $Order_data[0]['fDiscount'];
    }
    if (isset($Order_data[0]['vDiscount'])) {
        $vDiscount = $Order_data[0]['vDiscount']; //added by SP here changed from fDiscount to vDsiccount on 17-10-2019
    }
    $finalFareData['fDiscount'] = $fDiscount;
    $finalFareData['vDiscount'] = $vDiscount;
    $finalFareData['fCommision'] = $fCommision;
    $finalFareData['fNetTotal'] = $fNetTotal;
    $finalFareData['fTotalGenerateFare'] = $fTotalGenerateFare;
    $finalFareData['fOutStandingAmount'] = $fOutStandingAmount;
    $finalFareData['fWalletDebit'] = $user_wallet_debit_amount;
    $finalFareData['iStatusCode'] = $iStatusCode;
    return $finalFareData;
}

// // new added
function getPriceUserCurrency($iMemberId, $eUserType = "Passenger", $fPrice, $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
    }

    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $Ratio = $UserDetailsArr['Ratio'];
    $fPrice = $generalobj->setTwoDecimalPoint($fPrice * $Ratio);
    $fPricewithsymbol = $currencySymbol . " " . $fPrice;
    $returnArr['fPrice'] = $fPrice;
    $returnArr['fPricewithsymbol'] = $fPricewithsymbol;
    $returnArr['currencySymbol'] = $currencySymbol;
    return $returnArr;
}

function DisplayOrderDetailItemList($iOrderDetailId, $iMemberId, $eUserType = "Passenger", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $ssql = "";
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
    }

    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $Ratio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $def_lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    $sql = "select od.*, IFNULL(NULLIF(mi.vItemType_" . $vLang . ", ''),mi.vItemType_" . $def_lang . ") as MenuItem, mi.vImage from `order_details` as od LEFT JOIN  `menu_items` as mi ON od.iMenuItemId=mi.iMenuItemId where od.iOrderDetailId='" . $iOrderDetailId . "'";
    //$sql = "select od.*,mi.vItemType_" . $vLang . " as MenuItem,mi.vImage from `order_details` as od LEFT JOIN  `menu_items` as mi ON od.iMenuItemId=mi.iMenuItemId where od.iOrderDetailId='" . $iOrderDetailId . "'";
    $data_order_detail = $obj->MySQLSelect($sql);
    $MenuItem = $data_order_detail[0]['MenuItem'];
    $fPrice = $data_order_detail[0]['fOriginalPrice'];
    $vImage = $data_order_detail[0]['vImage'];
    $itemImgUrl = $tconfig["tsite_upload_images_menu_item"];
    // $fPrice = $data_order_detail[0]['fOriginalPrice']+$data_order_detail[0]['vOptionPrice']+$data_order_detail[0]['vAddonPrice'];
    $eAvailable = $data_order_detail[0]['eAvailable'];
    $fPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $fPrice, $iOrderId);
    $fPrice = $fPriceArr['fPricewithsymbol'];
    $vsymbol = $fPriceArr['currencySymbol'];
    $fPricewithoutsymbol = $fPriceArr['fPrice'];
    $fTotalprice = $fPricewithoutsymbol * $data_order_detail[0]['iQty'];
    $returnArr['iQty'] = $data_order_detail[0]['iQty'];
    $returnArr['MenuItem'] = $MenuItem;
    $returnArr['fPrice'] = $fPrice;
    //added by SP for default image on 15-10-2019
    if (!empty($vImage)) {
        $returnArr['vImage'] = $itemImgUrl . "/" . $vImage;
    } else {
        $returnArr['vImage'] = $tconfig["tsite_url"] . "webimages/upload/DefaultImg/items.png";
    }
    $returnArr['fTotPrice'] = $vsymbol . " " . formatnum($fTotalprice);
    $returnArr['eAvailable'] = $eAvailable;
    $returnArr['iOrderDetailId'] = $iOrderDetailId;
    //echo $iOrderId;die;
    if ($iOrderId > 0) {
        $sqlo = "select fOfferType,fOfferAppyType from `orders` where iOrderId = '" . $iOrderId . "'";
        $db_orderdata = $obj->MySQLSelect($sqlo);
        $fOfferType = $db_orderdata[0]['fOfferType'];
        $fOfferAppyType = $db_orderdata[0]['fOfferAppyType'];
        $TotalDiscountPrice = "";
        if (($fOfferAppyType == "None" && ($fOfferType == "Flat" || $fOfferType == "")) || $fOfferType == "Percentage") {
            $fTotalDiscountPrice = $data_order_detail[0]['fTotalDiscountPrice'];
            $TotalPrice = $data_order_detail[0]['fTotalPrice'];
            if ($fTotalDiscountPrice > 0) {
                $Strikeprice = ($TotalPrice - $fTotalDiscountPrice) * $Ratio;
                $TotalDiscountPrice = $vsymbol . " " . formatnum($Strikeprice);
            }
        }
        $returnArr['TotalDiscountPrice'] = $TotalDiscountPrice;
    }

    $vOptionId = $data_order_detail[0]['vOptionId'];
    if ($vOptionId != "") {
        $vOptionName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $vOptionId, '', 'true');
        $vOptionPrice = $data_order_detail[0]['vOptionPrice'];
        $vOptionPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $vOptionPrice, $iOrderId);
        $vOptionPrice = $vOptionPriceArr['fPricewithsymbol'];
        $returnArr['vOptionName'] = $vOptionName;
        $returnArr['vOptionPrice'] = $vOptionPrice;
    } else {
        $returnArr['vOptionName'] = "";
        $returnArr['vOptionPrice'] = "";
    }

    $tAddOnIdOrigPrice = $data_order_detail[0]['tAddOnIdOrigPrice'];
    if ($tAddOnIdOrigPrice != "") {
        $AddonItemsArr = array();
        $AddonItemsDetailArr = explode(",", $tAddOnIdOrigPrice);
        for ($i = 0; $i < count($AddonItemsDetailArr); $i++) {
            $AddonItemsStrArr = explode("#", $AddonItemsDetailArr[$i]);
            $AddonItemsId = $AddonItemsStrArr[0];
            $AddonItemsPrice = $AddonItemsStrArr[1];
            $AddonItemsPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $AddonItemsPrice, $iOrderId);
            $AddonItemPrice = $AddonItemsPriceArr['fPricewithsymbol'];
            $AddonItemName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $AddonItemsId, '', 'true');
            $AddonItemsArr[$i]['vAddOnItemName'] = $AddonItemName;
            $AddonItemsArr[$i]['AddonItemPrice'] = $AddonItemPrice;
        }

        $returnArr['AddOnItemArr'] = $AddonItemsArr;
    } else {
        $returnArr['AddOnItemArr'] = array();
    }

    return $returnArr;
}

function DisplayOrderDetailItemList_ForReorder($iOrderDetailId, $iMemberId, $eUserType = "Passenger", $iCompanyId, $iServiceId = "") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $ssql = "";
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId);
    }

    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $Ratio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $sql = "select od.*,mi.vItemType_" . $vLang . " as MenuItem,mi.vImage,mi.eFoodType from `order_details` as od LEFT JOIN  `menu_items` as mi ON od.iMenuItemId=mi.iMenuItemId where od.iOrderDetailId='" . $iOrderDetailId . "'";
    $data_order_detail = $obj->MySQLSelect($sql);
    $MenuItem = $data_order_detail[0]['MenuItem'];
    $iMenuItemId = $data_order_detail[0]['iMenuItemId'];

    // $fPrice = GetFoodMenuItemBasicPrice($data_order_detail[0]['iMenuItemId']);
    $fPrice = FoodMenuItemBasicPrice($data_order_detail[0]['iMenuItemId']);
    $eAvailable = $data_order_detail[0]['eAvailable'];
    $fPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $fPrice);
    $fPrice = $fPriceArr['fPrice'];
    $vsymbol = $fPriceArr['currencySymbol'];
    $fPricewithoutsymbol = $fPriceArr['fPrice'];
    $fTotalprice = $fPricewithoutsymbol * $data_order_detail[0]['iQty'];
    $returnArr['iQty'] = $data_order_detail[0]['iQty'];
    $returnArr['MenuItem'] = $MenuItem;
    $returnArr['iMenuItemId'] = $data_order_detail[0]['iMenuItemId'];
    $returnArr['eFoodType'] = $data_order_detail[0]['eFoodType'];
    $returnArr['iFoodMenuId'] = $data_order_detail[0]['iFoodMenuId'];
    $returnArr['fPrice'] = $fPrice;
    $returnArr['fTotPrice'] = $vsymbol . " " . $fTotalprice;
    
    //added by SP for offer amount
    //$StrikeoutPrice = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
    //$returnArr['fToppingStrikeoutPrice'] = $fPrice;
    //$returnArr['fToppingStrikeoutPricewithsymbol'] = $fPriceArr['fPricewithsymbol'];
    //$MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, $data_order_detail[0]['iQty'], '', "Display", "", "", $iServiceId);
    $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, '', "Display", "", "", $iServiceId);
    $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);

    $returnArr['fDiscountPrice'] = "";
    $returnArr['fDiscountPricewithsymbol'] = "";
    $returnArr['isShownDiscountPrice'] = "No";
    if ($fOfferAmt > 0) {
        $fDiscountPrice = ($MenuItemPriceArr['fPrice'] * $Ratio);
        //$fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt) / 100);
        //$fDiscountPrice = $fDiscountPrice * $Ratio;
        if (!empty($fDiscountPrice)) {
            $returnArr['isShownDiscountPrice'] = "Yes";
            $fDiscountPrice = $generalobj->setTwoDecimalPoint($fDiscountPrice, 2);
            $returnArr['fDiscountPrice'] = $fDiscountPrice;
            $returnArr['fDiscountPricewithsymbol'] = $currencySymbol . " " . $fDiscountPrice;
        }
    }
    
    $returnArr['eAvailable'] = $eAvailable;
    $returnArr['iOrderDetailId'] = $iOrderDetailId;
    $returnArr['vImage'] = "";
    if ($data_order_detail[0]['vImage'] != "") {
        $returnArr['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $data_order_detail[0]['vImage'];
    }

    $vOptionId = $data_order_detail[0]['vOptionId'];
    if ($vOptionId != "") {
        $vOptionName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $vOptionId, '', 'true');
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        $vOptionPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $vOptionPrice);
        $vOptionPrice = $vOptionPriceArr['fPrice'];
        $returnArr['vOptionId'] = $vOptionId;
        $returnArr['vOptionName'] = $vOptionName;
        $returnArr['vOptionPrice'] = $vOptionPrice;
    } else {
        $returnArr['vOptionId'] = "";
        $returnArr['vOptionName'] = "";
        $returnArr['vOptionPrice'] = "";
    }

    $tAddOnIdOrigPrice = $data_order_detail[0]['tAddOnIdOrigPrice'];
    if ($tAddOnIdOrigPrice != "") {
        $AddonItemsArr = array();
        $AddonItemsDetailArr = explode(",", $tAddOnIdOrigPrice);
        $AddonItemPrice_Total = 0;
        for ($i = 0; $i < count($AddonItemsDetailArr); $i++) {
            $AddonItemsStrArr = explode("#", $AddonItemsDetailArr[$i]);
            $AddonItemsId = $AddonItemsStrArr[0];
            $AddonItemsPrice = GetFoodMenuItemAddOnPrice($AddonItemsId);
            $AddonItemPrice_Total = $AddonItemPrice_Total + $AddonItemsPrice;
            $AddonItemsPriceArr_Total = getPriceUserCurrency($iMemberId, $eUserType, $AddonItemPrice_Total);
            $AddonItemPrice_Total = $AddonItemsPriceArr_Total['fPrice'];
            $AddonItemName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $AddonItemsId, '', 'true');
            $AddonItemsArr[$i]['vAddonId'] = $AddonItemsId;
            $AddonItemsArr[$i]['vAddOnItemName'] = $AddonItemName;
            $AddonItemsArr[$i]['AddonItemPrice'] = $AddonItemPrice_Total;
        }

        $returnArr['AddOnItemArr'] = $AddonItemsArr;
    } else {
        $returnArr['AddOnItemArr'] = array();
    }

    ## Return Selected  ##
    /* $returnArr['options'] = array();
      $returnArr['addon'] = array();
      $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '".$iCompanyId."' AND fm.eStatus = 'Active' AND mi.eAvailable = 'Yes' AND mi.iMenuItemId = '".$iMenuItemId."'";
      $db_options_data = $obj->MySQLSelect($sql);
      if(count($db_options_data) > 0){
      for($i=0;$i<count($db_options_data);$i++){
      $fPrice = $db_options_data[$i]['fPrice'];
      $fUserPrice = number_format($fPrice*$Ratio,2);
      $fUserPriceWithSymbol = $currencySymbol." ".$fUserPrice;
      $db_options_data[$i]['fUserPrice'] = $fUserPrice;
      $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
      if($db_options_data[$i]['eOptionType'] == "Options"){
      $returnArr['options'][] = $db_options_data[$i];
      }

      if($db_options_data[$i]['eOptionType'] == "Addon"){
      $returnArr['addon'][] = $db_options_data[$i];
      }
      }
      } */

    ## Get Menu Items Array ##
    $returnArr['menu_items'] = array();
    $sqlf = "SELECT iMenuItemId,iFoodMenuId,vItemType_" . $vLang . " as vItemType,vItemDesc_" . $vLang . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder FROM menu_items WHERE iMenuItemId = '" . $iMenuItemId . "'";
    $db_item_data = $obj->MySQLSelect($sqlf);
    if (count($db_item_data) > 0) {
        $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, $iMemberId, "Display", "", "", $iServiceId);
        $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
        $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
        $db_item_data[0]['fOfferAmt'] = $fOfferAmt;
        if ($fOfferAmt > 0) {
            $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
            $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
            $db_item_data[0]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
            $db_item_data[0]['fDiscountPrice'] = $fDiscountPrice;
            $db_item_data[0]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
            $db_item_data[0]['currencySymbol'] = $currencySymbol;
        } else {
            $db_item_data[0]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
            $db_item_data[0]['fDiscountPrice'] = $fPrice;
            $db_item_data[0]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
            $db_item_data[0]['currencySymbol'] = $currencySymbol;
        }

        if ($db_item_data[0]['vImage'] != "") {
            $db_item_data[0]['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $db_item_data[0]['vImage'];
        }
        $iMenuItemId = $db_item_data[0]['iMenuItemId'];
        //$MenuItemOptionToppingArr = GetMenuItemOptionsTopping($db_item_data[0]['iMenuItemId'], $currencySymbol, $Ratio, $vLang, $iServiceId);
        $MenuItemOptionToppingArr = array();
        if (isset($MenuItemOptionToppingArr[$iMenuItemId])) {
            $MenuItemOptionToppingArr = $MenuItemOptionToppingArr[$iMenuItemId];
        }
        //echo "<pre>";print_r($MenuItemOptionToppingArr);die;
        $db_item_data[0]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
        // echo "<pre>";print_r($MenuItemOptionToppingArr);exit;
        $returnArr['menu_items'] = $db_item_data[0];
    }

    ## Get Menu Items Array ##
    return $returnArr;
}

function GetUserGeoLocationId($Address_Array) {
    global $generalobj, $obj;
    $iLocationId = "0";
    if (!empty($Address_Array)) {
        $sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'UserDeliveryCharge'";
        $allowed_data = $obj->MySQLSelect($sqlaa);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array(
                            $latitude[$x],
                            $longitude[$x]
                        );
                    }
                }

                // print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $iLocationId = $val['iLocationId'];
                        break;
                    }
                }
            }
        }
    }

    return $iLocationId;
}

function getOrderFare($iOrderId, $eUserType = "Passenger", $IS_FROM_HISTORY = "No") {
    global $generalobj, $obj;
    $OrderFareDetailsArr = $OrderFareDetailsArrNew = array();
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($data_order[0]['iUserId'], $iOrderId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($data_order[0]['iDriverId'], $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($data_order[0]['iCompanyId'], $iOrderId);
    }

    $vSymbol = $UserDetailsArr['currencySymbol'];
    $priceRatio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $iServiceId = $data_order[0]['iServiceId'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $returnArr['subtotal'] = $data_order[0]['fSubTotal'] * $priceRatio;
    $returnArr['fOffersDiscount'] = $data_order[0]['fOffersDiscount'] * $priceRatio;
    $returnArr['fPackingCharge'] = $data_order[0]['fPackingCharge'] * $priceRatio;
    $returnArr['fDeliveryCharge'] = $data_order[0]['fDeliveryCharge'] * $priceRatio;
    $returnArr['fTax'] = $data_order[0]['fTax'] * $priceRatio;
    $returnArr['fTotalGenerateFare'] = $data_order[0]['fTotalGenerateFare'] * $priceRatio;
    $returnArr['fDiscount'] = $data_order[0]['fDiscount'] * $priceRatio;
    $returnArr['fCommision'] = $data_order[0]['fCommision'] * $priceRatio;
    $returnArr['fNetTotal'] = $data_order[0]['fNetTotal'] * $priceRatio;
    $returnArr['fWalletDebit'] = $data_order[0]['fWalletDebit'] * $priceRatio;
    $returnArr['fOutStandingAmount'] = $data_order[0]['fOutStandingAmount'] * $priceRatio;
    $returnArr['fDriverPaidAmount'] = $data_order[0]['fDriverPaidAmount'] * $priceRatio;
    $subtotal = formatNum($returnArr['subtotal']);
    $fOffersDiscount = formatNum($returnArr['fOffersDiscount']);
    $fPackingCharge = formatNum($returnArr['fPackingCharge']);
    $fDeliveryCharge = formatNum($returnArr['fDeliveryCharge']);
    $fTax = formatNum($returnArr['fTax']);
    $fTotalGenerateFare = formatNum($returnArr['fTotalGenerateFare']);
    $fDiscount = formatNum($returnArr['fDiscount']);
    $fCommision = formatNum($returnArr['fCommision']);
    $fWalletDebit = formatNum($returnArr['fWalletDebit']);
    $fOutStandingAmount = formatNum($returnArr['fOutStandingAmount']);
    $fNetTotal = formatNum($returnArr['fNetTotal']);
    $EarningAmount = $returnArr['fTotalGenerateFare'] - ($returnArr['fOffersDiscount'] + $returnArr['fDeliveryCharge'] + $returnArr['fCommision'] + $returnArr['fOutStandingAmount']);
    $arrindex = $arrindexNew = 0;
    $type = "";
    if (isset($_REQUEST['type']) && $_REQUEST['type'] != "") {
        $type = $_REQUEST['type'];
    }
    if ($eUserType == "Driver") {
        $tripsql = "SELECT fDeliveryCharge,eDriverPaymentStatus FROM trips WHERE iOrderId='" . $iOrderId . "'";
        $DataTrips = $obj->MySQLSelect($tripsql);
        if ($data_order[0]['iStatusCode'] == '7' || $data_order[0]['iStatusCode'] == '8') {
            if ($DataTrips[0]['eDriverPaymentStatus'] == 'Settelled') {
                $fDeliveryChargeDriver = $returnArr['fDriverPaidAmount'];
            } else {
                $fDeliveryChargeDriver = $DataTrips[0]['fDeliveryCharge'];
            }
        } else {
            $fDeliveryChargeDriver = $DataTrips[0]['fDeliveryCharge'];
        }

        $returnArr['fDeliveryChargeDriver'] = $fDeliveryChargeDriver * $priceRatio;
        $fDeliveryChargesDriver = formatNum($returnArr['fDeliveryChargeDriver']);
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_EARNING_APP']] = $vSymbol . " " . $fDeliveryChargesDriver;
        $arrindex++;

        //added by SP for cubex design on 15-10-2019
        if ($data_order[0]['fSubTotal'] > 0) {
            $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $vSymbol . " " . $subtotal;
            $arrindexNew++;
        }

        if ($data_order[0]['fOffersDiscount'] > 0) {
            $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fOffersDiscount;
            $arrindexNew++;
        }

        if ($data_order[0]['fPackingCharge'] > 0) {
            $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_PACKING_CHARGE']] = $vSymbol . " " . $fPackingCharge;
            $arrindexNew++;
        }

        if ($IS_FROM_HISTORY == "No") {
            if ($data_order[0]['fDeliveryCharge'] > 0) {
                $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = $vSymbol . " " . $fDeliveryCharge;
                $arrindexNew++;
            }
        }
        if ($data_order[0]['fOutStandingAmount'] > 0 && $type == "GetOrderDetailsRestaurant") {
            $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = $vSymbol . " " . $fOutStandingAmount;
            $arrindexNew++;
        }
        if ($data_order[0]['fTax'] > 0) {
            $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $vSymbol . " " . $fTax;
            $arrindexNew++;
        }
        if ($data_order[0]['fDiscount'] > 0) {
            $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fDiscount;
            $arrindexNew++;
        }
        if ($IS_FROM_HISTORY == "No") {
            // if($data_order[0]['fTotalGenerateFare'] > 0){
            // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']." ".$payment_str] = $vSymbol." ".$fTotalGenerateFare;

            if ($data_order[0]['fWalletDebit'] > 0) {
                $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "-" . $vSymbol . " " . $fWalletDebit;
                $arrindexNew++;
            }

            $OrderFareDetailsArrNew[$arrindexNew][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'] . " " . $payment_str] = $vSymbol . " " . $fNetTotal;
            $arrindexNew++;

            // }
        }
        //print_R($OrderFareDetailsArrNew); exit;
    } else if ($eUserType == "Company") {
        if ($data_order[0]['fSubTotal'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $vSymbol . " " . $subtotal;
            $arrindex++;
        }

        if ($data_order[0]['fOffersDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fOffersDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fPackingCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $vSymbol . " " . $fPackingCharge;
            $arrindex++;
        }

        if ($data_order[0]['fTax'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $vSymbol . " " . $fTax;
            $arrindex++;
        }

        if ($IS_FROM_HISTORY == "No") {

            // if($data_order[0]['fTotalGenerateFare'] > 0){
            // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']." ".$payment_str] = $vSymbol." ".$fTotalGenerateFare;
            $TotalDisplayAmount = $returnArr['subtotal'] - $returnArr['fOffersDiscount'] + $returnArr['fPackingCharge'] + $returnArr['fTax'];
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'] . " " . $payment_str] = $vSymbol . " " . formatnum($TotalDisplayAmount);
            $arrindex++;

            // }
        } else {
            if ($data_order[0]['fCommision'] > 0) {
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_Commision']] = "-" . $vSymbol . " " . $fCommision;
                $arrindex++;
            }

            if ($EarningAmount > 0) {
                $EarningAmount = formatNum($EarningAmount);
                if ($data_order[0]['iStatusCode'] == '7' || $data_order[0]['iStatusCode'] == '8') {
                    $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_EXPECTED_EARNING'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                } else {
                    $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_AMT_EARNED'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                }

                $arrindex++;
            }
        }

        /* if ($fNetTotal > 0) {
          $OrderFareDetailsArr[$arrindex]['SubTotal'] = $vSymbol.$fNetTotal;
          $arrindex++;
          } */
    } else {
        if ($data_order[0]['fSubTotal'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $vSymbol . " " . $subtotal;
            $arrindex++;
        }

        if ($data_order[0]['fOffersDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fOffersDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fPackingCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $vSymbol . " " . $fPackingCharge;
            $arrindex++;
        }

        if ($data_order[0]['fDeliveryCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = $vSymbol . " " . $fDeliveryCharge;
            $arrindex++;
        }

        if ($data_order[0]['fOutStandingAmount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = $vSymbol . " " . $fOutStandingAmount;
            $arrindex++;
        }

        if ($data_order[0]['fTax'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $vSymbol . " " . $fTax;
            $arrindex++;
        }

        if ($data_order[0]['fDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fWalletDebit'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "-" . $vSymbol . " " . $fWalletDebit;
            $arrindex++;
        }

        //added by SP for rounding off currency wise on 19-11-2019 start         
        $tripsql = "SELECT vCurrencyPassenger FROM trips WHERE iOrderId='" . $iOrderId . "'";
        $DataTrips = $obj->MySQLSelect($tripsql);

        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger, cu.ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $data_order[0]['iUserId'] . "'";
        $currData = $obj->MySQLSelect($sqlp);
        $vCurrency = $currData[0]['vName'];
        $samecur = ($DataTrips[0]['vCurrencyPassenger'] == $currData[0]['vCurrencyPassenger']) ? 1 : 0;

        if (isset($data_order[0]['fRoundingAmount']) && !empty($data_order[0]['fRoundingAmount']) && $data_order[0]['fRoundingAmount'] != 0 && $samecur == 1 && $currData[0]['eRoundingOffEnable'] == "Yes") {

            $roundingOffTotal_fare_amountArr['method'] = $data_order[0]['eRoundingType'];
            $roundingOffTotal_fare_amountArr['differenceValue'] = $data_order[0]['fRoundingAmount'];

            $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($fNetTotal, $data_order[0]['fRoundingAmount'], $data_order[0]['eRoundingType']); ////start

            if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                $roundingMethod = "";
            } else {
                $roundingMethod = "-";
            }
            $fNetTotal = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
            $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";

            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = $roundingMethod . " " . $currencySymbol . "" . $rounding_diff;
            $arrindex++;
        }
        //added by SP for rounding off currency wise on 19-11-2019 end

        if ($IS_FROM_HISTORY == "No") {

            // if($data_order[0]['fTotalGenerateFare'] > 0){
            // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']." ".$payment_str] = $vSymbol." ".$fTotalGenerateFare;
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'] . " " . $payment_str] = $vSymbol . " " . $fNetTotal;
            $arrindex++;

            // }
        } else {
            if ($data_order[0]['fCommision'] > 0) {
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_Commision']] = "-" . $vSymbol . " " . $fCommision;
                $arrindex++;
            }

            if ($EarningAmount > 0) {
                $EarningAmount = formatNum($EarningAmount);
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_AMT_EARNED'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                $arrindex++;
            }
        }


        /* if ($fNetTotal > 0) {
          $OrderFareDetailsArr[$arrindex]['SubTotal'] = $vSymbol.$fNetTotal;
          $arrindex++;
          } */
    }

    $arr[0] = $OrderFareDetailsArr;
    $arr[1] = $OrderFareDetailsArrNew;
    return $arr;
}

function DisplayOrderDetailList($iOrderId, $vTimeZone = 'Asia/Kolkata', $UserType = "Company", $IS_FROM_HISTORY = "No") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $sql = "SELECT o.iOrderId,o.vOrderNo,o.fNetTotal,o.iCompanyId,o.iServiceId,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.iStatusCode,o.ePaid,o.ePaymentOption,o.iUserAddressId,concat(ru.vName,' ',ru.vLastName) as UserName,ru.vPhone,ru.vPhoneCode,o.vInstruction,o.fRoundingAmount,o.eRoundingType FROM orders as o LEFT JOIN register_user as ru on ru.iUserId = o.iUserId WHERE o.iOrderId = '" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);

    if($UserType == "Passenger") {
        $lang = get_value("register_user",'vLang', 'iUserId',$db_order[0]['iUserId'],'','true');
    } else if($UserType == "Driver") {
        $lang = get_value("register_driver",'vLang', 'iDriverId',$db_order[0]['iDriverId'],'','true');
    } else if($UserType == "Company") {
        $lang = get_value("company",'vLang', 'iCompanyId',$db_order[0]['iCompanyId'],'','true');
    }

    // echo "<pre>";print_r($db_order);exit;
    if ($UserType == "Driver") {
        $query = "SELECT vImage,eImgSkip,iVehicleTypeId, vCurrencyDriver,vCurrencyPassenger FROM `trips` WHERE iOrderId = '" . $iOrderId . "'";
        $TripsData = $obj->MySQLSelect($query);
        $Vehiclefields = "iVehicleTypeId,vVehicleType";
        $VehicleTypeDataDriver = get_value('vehicle_type', $Vehiclefields, 'iVehicleTypeId', $TripsData[0]['iVehicleTypeId']);
        $ssql1 .= "AND eAvailable = 'Yes'";
    }
    //echo "<pre>";print_r($db_order);die;
    $StatusDisplay = getOrderStatus($iOrderId,$lang);
    $eConfirm = checkOrderStatus($iOrderId, "2");
    $eDecline = checkOrderStatus($iOrderId, "9");
    $query = "SELECT iOrderDetailId FROM order_details WHERE iOrderId = '" . $iOrderId . "' $ssql1";
    $orderDetailId = $obj->MySQLSelect($query);
    foreach ($db_order as $key => $value) {
        $ssql1 = $whereCond = $vUserImage = $vDriverImage = '';
        $userId = $value['iUserId'];
        $driverId = $value['iDriverId'];
        if ($UserType == "Passenger") {
            $iMemberId = $value['iUserId'];
            $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
        } else if ($UserType == "Driver") {
            $iMemberId = $value['iDriverId'];
            $ssql1 .= "AND eAvailable = 'Yes'";
            $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
        } else {
            $iMemberId = $value['iCompanyId'];
            $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
        }
        //Added By HJ On 28-12-2018 For Get User and Driver Image Name Start
        if ($userId > 0) {
            $whereCond = "iUserId='" . $userId . "'";
            $tableName = "register_user";
            $fieldName = "vImgName AS vUserImage";
            $getUserImage = $obj->MySQLSelect("SELECT $fieldName FROM " . $tableName . " WHERE $whereCond");
            if (count($getUserImage) > 0) {
                $vUserImage = $getUserImage[0]['vUserImage'];
            }
        }
        $returnArr[$key]['DriverName'] = "";
        if ($driverId > 0) {
            $whereCond = "iDriverId='" . $driverId . "'";
            $tableName = "register_driver";
            $fieldName = "vImage AS vDriverImage,vName,vLastName";
            $getDriverImage = $obj->MySQLSelect("SELECT $fieldName FROM " . $tableName . " WHERE $whereCond");
            if (count($getDriverImage) > 0) {
                $DriverName = $getDriverImage[0]['vName'] . " " . $getDriverImage[0]['vLastName'];
                $vDriverImage = $getDriverImage[0]['vUserImage'];
                $returnArr[$key]['DriverName'] = $DriverName;
            }
        }
        //Added By HJ On 28-12-2018 For Get User and Driver Image Name End
        //echo "<pre>";
        $vcurSymbol = $UserDetailsArr['currencySymbol'];
        $curpriceRatio = $UserDetailsArr['Ratio'];
        $vLangu = $UserDetailsArr['vLang'];
        $iServiceId = $db_order[0]['iServiceId'];
        $languageLabelsArr = getLanguageLabelsArr($vLangu, "1", $iServiceId);
        $returnArr[$key]['iOrderId'] = $iOrderId;
        $returnArr[$key]['iServiceId'] = $value['iServiceId'];
        $returnArr[$key]['iUserId'] = $value['iUserId'];
        $returnArr[$key]['iCompanyId'] = $value['iCompanyId'];
        $returnArr[$key]['vOrderNo'] = $value['vOrderNo'];
        $returnArr[$key]['iStatusCode'] = $value['iStatusCode'];
        $returnArr[$key]['vUserImage'] = $vUserImage;
        $returnArr[$key]['vDriverImage'] = $vDriverImage;
        $returnArr[$key]['vInstruction'] = trim($value['vInstruction']);
        if ($StatusDisplay == 'Refunded') {
            $StatusDisplay = 'Cancelled';
        }
        $servFields = 'iServiceId,vServiceName_' . $vLangu . ' as vServiceName';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $value['iServiceId']);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['vServiceName'])) {
                $returnArr[$key]['vServiceCategoryName'] = '';
            } else {
                $returnArr[$key]['vServiceCategoryName'] = $ServiceCategoryData[0]['vServiceName'];
            }
        } else {
            $returnArr[$key]['vServiceCategoryName'] = '';
        }

        $returnArr[$key]['vStatus'] = $StatusDisplay;
        $returnArr[$key]['UserName'] = $value['UserName'];
        $returnArr[$key]['UserPhone'] = '+' . $value['vPhoneCode'] . $value['vPhone'];
        $returnArr[$key]['ePaid'] = $value['ePaid'];
        $returnArr[$key]['ePaymentOption'] = $value['ePaymentOption'];
        $returnArr[$key]['eConfirm'] = $eConfirm;
        $returnArr[$key]['eDecline'] = $eDecline;
        $returnArr[$key]['vCompany'] = $returnArr[$key]['RestuarantPhone'] = $returnArr[$key]['vRestuarantLocation'] = $returnArr[$key]['vRestuarantImage'] = $returnArr[$key]['RestuarantLat'] = $returnArr[$key]['RestuarantLong'] = "";
        if (isset($value['iCompanyId']) && $value['iCompanyId'] > 0) {
            $restFields = 'vCompany,vCaddress,vRestuarantLocation ,vPhone,vImage,vCode,vRestuarantLocationLat,vRestuarantLocationLong';
            $CompanyData = get_value('company', $restFields, 'iCompanyId', $value['iCompanyId']);
            //echo "<pre>";print_r($CompanyData);die;
            $returnArr[$key]['vCompany'] = $CompanyData[0]['vCompany'];
            if ($UserType == 'Driver') {
                $returnArr[$key]['RestuarantLat'] = $CompanyData[0]['vRestuarantLocationLat'];
                $returnArr[$key]['RestuarantLong'] = $CompanyData[0]['vRestuarantLocationLong'];
                $returnArr[$key]['RestuarantPhone'] = '+' . $CompanyData[0]['vCode'] . $CompanyData[0]['vPhone'];
            }
            $returnArr[$key]['vRestuarantLocation'] = $CompanyData[0]['vRestuarantLocation'];
            $returnArr[$key]['vRestuarantImage'] = $CompanyData[0]['vImage'];
        }
        $UserAddressArr = GetUserAddressDetail($value['iUserId'], "Passenger", $value['iUserAddressId']);
        $returnArr[$key]['DeliveryAddress'] = $UserAddressArr['UserAddress'];
        if ($UserType == 'Driver') {
            $returnArr[$key]['UserAddress'] = $UserAddressArr['UserAddress'];
            $userFields = 'vLatitude,vLongitude';
            $userData = get_value('user_address', $userFields, 'iUserAddressId', $value['iUserAddressId']);
            $returnArr[$key]['UserLatitude'] = $userData[0]['vLatitude'];
            $returnArr[$key]['UserLongitude'] = $userData[0]['vLongitude'];
            $isPhotoUploaded = 'No';
            if (!empty($TripsData)) {
                if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'None') {
                    $isPhotoUploaded = 'No';
                } else if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'No') {
                    $isPhotoUploaded = 'Yes';
                } else if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'Yes') {
                    $isPhotoUploaded = 'Yes';
                } else {
                    $isPhotoUploaded = 'No';
                }

                if ($returnArr[$key]['iStatusCode'] == '5') {
                    $returnArr[$key]['PickedFromRes'] = 'Yes';
                } else {
                    $returnArr[$key]['PickedFromRes'] = 'No';
                }

                $SelectdVehicleTypeId = ($VehicleTypeDataDriver[0]['iVehicleTypeId'] != '') ? $VehicleTypeDataDriver[0]['iVehicleTypeId'] : "";
                $SelectdVehicleType = ($VehicleTypeDataDriver[0]['vVehicleType'] != '') ? $VehicleTypeDataDriver[0]['vVehicleType'] : "";
                $returnArr[$key]['iVehicleTypeId'] = $SelectdVehicleTypeId;
                $returnArr[$key]['vVehicleType'] = $SelectdVehicleType;
            }

            $returnArr[$key]['isPhotoUploaded'] = $isPhotoUploaded;
            $eUnit = getMemberCountryUnit($value['iDriverId'], "Driver");
            if ($eUnit == 'KMs') {
                $fDistance = distanceByLocation($userData[0]['vLatitude'], $userData[0]['vLongitude'], $CompanyData[0]['vRestuarantLocationLat'], $CompanyData[0]['vRestuarantLocationLong'], "K");
            } else {
                $fDistance = distanceByLocation($userData[0]['vLatitude'], $userData[0]['vLongitude'], $CompanyData[0]['vRestuarantLocationLat'], $CompanyData[0]['vRestuarantLocationLong'], "");
            }

            $returnArr[$key]['UserDistance'] = round($fDistance, 2) . " " . $eUnit;
        }

        $serverTimeZone = date_default_timezone_get();
        $date = converToTz($value['tOrderRequestDate'], $vTimeZone, $serverTimeZone, "Y-m-d H:i:s");
        $OrderTime = date('d M, Y h:i A', strtotime($date));
        $returnArr[$key]['tOrderRequestDate_Org'] = $date;
        $returnArr[$key]['tOrderRequestDate'] = $OrderTime;
        if ($value['iDriverId'] == '0') {
            $returnArr[$key]['DriverAssign'] = 'No';
        } else {
            $returnArr[$key]['DriverAssign'] = 'Yes';
        }

        //$query = "SELECT iOrderDetailId FROM order_details WHERE iOrderId = '" . $iOrderId . "' $ssql1";
        //$orderDetailId = $obj->MySQLSelect($query);
        $returnArr[$key]['TotalItems'] = strval(count($orderDetailId));
        if ($UserType == 'Driver') {
            $ePaid = $value['ePaid'];
            $ePaymentOption = $value['ePaymentOption'];
            $returnArr[$key]['vSymbol'] = $vcurSymbol;
            if ($ePaid == 'Yes' && $ePaymentOption == 'Card') {
                $returnArr[$key]['originalTotal'] = formatNum($value['fNetTotal'] * $curpriceRatio);
                $CardNetTotal = 0;
                $returnArr[$key]['SubTotal'] = $vcurSymbol . formatNum($CardNetTotal); // $languageLabelsArr['LBL_SUBTOTAL_APP_TXT']
            } else {
                $returnArr[$key]['SubTotal'] = $vcurSymbol . formatNum($value['fNetTotal'] * $curpriceRatio);

                //added by SP for rounding off currency wise on 19-11-2019 start
                $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, rd.vCurrencyDriver, cu.ratio FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iMemberId . "'";
                $currData = $obj->MySQLSelect($sqlp);
                $vCurrency = $currData[0]['vName'];
                $samecur = ($TripsData[0]['vCurrencyDriver'] == $currData[0]['vCurrencyDriver'] && $TripsData[0]['vCurrencyDriver'] == $TripsData[0]['vCurrencyPassenger']) ? 1 : 0;

                if (isset($db_order[0]['fRoundingAmount']) && !empty($db_order[0]['fRoundingAmount']) && $db_order[0]['fRoundingAmount'] != 0 && $samecur == 1 && $currData[0]['eRoundingOffEnable'] == "Yes") {
                    $roundingOffTotal_fare_amountArr['method'] = $db_order[0]['eRoundingType'];
                    $roundingOffTotal_fare_amountArr['differenceValue'] = $db_order[0]['fRoundingAmount'];

                    $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($value['fNetTotal'] * $curpriceRatio, $db_order[0]['fRoundingAmount'], $db_order[0]['eRoundingType']); ////start

                    if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                        $roundingMethod = "";
                    } else {
                        $roundingMethod = "-";
                    }

                    $fNetTotal = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
                    $returnArr[$key]['SubTotal'] = $vcurSymbol . formatNum($fNetTotal);
                }
                //added by SP for rounding off currency wise on 19-11-2019 end
            }
        }
        foreach ($orderDetailId as $k => $val) {
            $ItemLists[] = DisplayOrderDetailItemList($val['iOrderDetailId'], $iMemberId, $UserType, $iOrderId);
        }
        $all_data_new = array();
        if ($ItemLists != '') {
            foreach ($ItemLists as $k => $item) {
                $iQty = ($item['iQty'] != '') ? $item['iQty'] : '';
                $MenuItem = ($item['MenuItem'] != '') ? $item['MenuItem'] : '';
                $fTotPrice = ($item['fTotPrice'] != '') ? $item['fTotPrice'] : '';
                $TotalDiscountPrice = ($item['TotalDiscountPrice'] != '') ? $item['TotalDiscountPrice'] : '';
                $eAvailable = ($item['eAvailable'] != '') ? $item['eAvailable'] : '';
                $AddOnItemArr = ($item['AddOnItemArr'] != '') ? $item['AddOnItemArr'] : '';
                //echo "<pre>";print_r($AddOnItemArr);die;

                $iOrderDetailId = ($item['iOrderDetailId'] != '') ? $item['iOrderDetailId'] : '';
                $vImage = ($item['vImage'] != '') ? $item['vImage'] : '';
                $all_data_new[$k]['iOrderDetailId'] = $iOrderDetailId;
                $all_data_new[$k]['iQty'] = $iQty;
                $all_data_new[$k]['MenuItem'] = $MenuItem;
                $all_data_new[$k]['fTotPrice'] = $fTotPrice;
                $all_data_new[$k]['vImage'] = $vImage;
                $all_data_new[$k]['TotalDiscountPrice'] = $TotalDiscountPrice;
                $all_data_new[$k]['eAvailable'] = $eAvailable;
                $vOptionName = ($item['vOptionName'] != '') ? $item['vOptionName'] : '';
                $addonTitleArr = array();
                $addonTitle = '';
                if (!empty($AddOnItemArr)) {
                    foreach ($AddOnItemArr as $addonkey => $addonvalue) {
                        $addonTitleArr[] = $addonvalue['vAddOnItemName'];
                    }
                    $addonTitle = implode(",", $addonTitleArr);
                }
                if ($vOptionName != '' && $addonTitle == '') {
                    $all_data_new[$k]['SubTitle'] = $vOptionName;
                } else if ($vOptionName == '' && $addonTitle != '') {
                    $all_data_new[$k]['SubTitle'] = $addonTitle;
                } else if ($vOptionName != '' && $addonTitle != '') {
                    $all_data_new[$k]['SubTitle'] = $vOptionName . "," . $addonTitle;
                } else {
                    $all_data_new[$k]['SubTitle'] = '';
                }
                //Added By HJ On 05-02-2020 For Get Options and Topping Data Start
                $all_data_new[$k]['MenuItemToppings'] = $addonTitle;
                $all_data_new[$k]['MenuItemOptions'] = $vOptionName;
                //Added By HJ On 05-02-2020 For Get Options and Topping Data End
            }
        }

        $returnArr[$key]['itemlist'] = $all_data_new;
    }

    $orderData = getOrderFare($iOrderId, $UserType, $IS_FROM_HISTORY);
    $returnArr[$key]['FareDetailsArr'] = $orderData[0];
    $returnArr[$key]['FareDetailsNewArr'] = $orderData[1]; //added by SP for cubex design on 15-10-2019
    return $returnArr;
}

function getOrderStatus($iOrderId,$lang="EN") {
    global $generalobj, $obj;
    $sql = "SELECT os.vStatus_Track_$lang as vStatus_Track FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "'";
    $OrderStatus = $obj->MySQLSelect($sql);
    $vStatus = $OrderStatus[0]['vStatus_Track'];
    return $vStatus;
}

function createOrderLog($iOrderId, $iStatusCode) {
    global $generalobj, $obj;
    $sql = "SELECT * FROM order_status_logs WHERE iOrderId = '" . $iOrderId . "' AND iStatusCode = '" . $iStatusCode . "'";
    $OrderStatuslog = $obj->MySQLSelect($sql);
    if (count($OrderStatuslog) == 0) {
        $data['iOrderId'] = $iOrderId;
        $data['iStatusCode'] = $iStatusCode;
        $data['dDate'] = @date("Y-m-d H:i:s");
        $data['vIP'] = $generalobj->get_client_ip();
        $id = $obj->MySQLQueryPerform("order_status_logs", $data, 'insert');
    } else {
        $id = $OrderStatuslog[0]['iOrderLogId'];
    }

    return $id;
}

function UpdateCardPaymentPendingOrder() {
    global $generalobj, $obj;
    $currentdate = @date("Y-m-d H:i:s");
    $checkdate = date('Y-m-d H:i:s', strtotime("-120 minutes", strtotime($currentdate)));
    $sql = "SELECT iOrderId FROM orders WHERE dDeliveryDate < '" . $checkdate . "' AND iStatusCode = 12 AND ePaymentOption = 'Card'";
    $db_order = $obj->MySQLSelect($sql);
    if (count($db_order) > 0) {
        for ($i = 0; $i < count($db_order); $i++) {
            $iOrderId = $db_order[$i]['iOrderId'];
            $sql = "delete from order_details where iOrderId='" . $iOrderId . "'";
            $obj->sql_query($sql);
            $sqld = "delete from orders where iOrderId='" . $iOrderId . "'";
            $obj->sql_query($sqld);
        }
    }

    return true;
}

function checkOrderStatus($iOrderId, $iStatusCode) {
    global $generalobj, $obj;
    $orderexist = "No";
    $sql = "SELECT count(iOrderLogId) as TotOrderLogId from order_status_logs WHERE iOrderId ='" . $iOrderId . "' AND iStatusCode IN($iStatusCode)";
    $db_status = $obj->MySQLSelect($sql);
    $TotOrderLogId = $db_status[0]['TotOrderLogId'];
    if ($TotOrderLogId > 0) {
        $orderexist = "Yes";
    }

    return $orderexist;
}

function checkOrderRequestStatus($iOrderId) {
    global $generalobj, $obj, $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;
    $sql = "SELECT * from driver_request WHERE iOrderId ='" . $iOrderId . "'";
    $db_driver_request = $obj->MySQLSelect($sql);
    if (count($db_driver_request) > 0) {
        $sql = "SELECT iDriverId from orders WHERE iOrderId ='" . $iOrderId . "'";
        $db_order_driver = $obj->MySQLSelect($sql);
        $iDriverId = $db_order_driver[0]['iDriverId'];
        if ($iDriverId > 0) {
            $returnArr['Action'] = "1";
            $returnArr["message"] = "LBL_REQUEST_FAILED_TXT";
            $returnArr["message1"] = "DRIVER_ASSIGN";
        } else {
            $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL + 5;
            $currentdate = @date("Y-m-d H:i:s");
            $checkdate = date('Y-m-d H:i:s', strtotime("+" . $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL . " seconds", strtotime($currentdate)));
            $checkdate1 = date('Y-m-d H:i:s', strtotime("-" . $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL . " seconds", strtotime($currentdate)));
            $sql = "SELECT iDriverRequestId from driver_request WHERE iOrderId ='" . $iOrderId . "' AND ( dAddedDate > '" . $checkdate1 . "' AND dAddedDate < '" . $checkdate . "')";
            $db_status = $obj->MySQLSelect($sql);
            if (count($db_status) > 0) {
                $returnArr['Action'] = "0";
                $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
                $returnArr["message1"] = "REQ_PROCESS";
            } else {
                $returnArr['Action'] = "1";
                $returnArr["message"] = "LBL_REQUEST_FAILED_TXT";
                $returnArr["message1"] = "REQ_FAILED";
            }
        }
    } else {
        $returnArr['Action'] = "1";
        $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
        $returnArr["message1"] = "REQ_NOT_FOUND";
    }

    return $returnArr;
}

function get_day_name($timestamp) {
    $date = date('d M Y', $timestamp);
    if ($date == date('d M Y')) {
        $date = 'Today';
    } else if ($date == date('d M Y', strtotime("-1 days"))) {
        $date = 'Yesterday';
    }

    return $date;
}

function checkDistanceBetweenUserCompany($iUserAddressId, $iCompanyId) {
    global $generalobj, $obj, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $sql = "select vLatitude,vLongitude from `user_address` where iUserAddressId = '" . $iUserAddressId . "'";
    $db_userdata = $obj->MySQLSelect($sql);
    $passengeraddlat = $db_userdata[0]['vLatitude'];
    $passengeraddlong = $db_userdata[0]['vLongitude'];
    $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
    $db_companydata = $obj->MySQLSelect($sql);
    $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
    $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
    $distance = distanceByLocation($passengeraddlat, $passengeraddlong, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
    if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
        $returnArr['Action'] = "0";
        $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
        setDataResponse($returnArr);
    }
}

function getremainingtimeorderrequest($iOrderId) {
    global $generalobj, $obj, $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;
    $sql = "SELECT * from driver_request WHERE iOrderId ='" . $iOrderId . "' ORDER BY iDriverRequestId DESC LIMIT 0,1";
    $db_driver_request = $obj->MySQLSelect($sql);
    $datedifference = 0;
    if (count($db_driver_request) > 0) {
        $currentdate = @date("Y-m-d H:i:s");
        $currentdate = strtotime($currentdate);
        $dAddedDate = $db_driver_request[0]['dAddedDate'];
        $dAddedDate = strtotime($dAddedDate);
        $datedifference = $currentdate - $dAddedDate;
    }

    $Remaining_Time_In_Seconds = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL - $datedifference;
    $Remaining_Time_In_Seconds = $Remaining_Time_In_Seconds + 10;
    if ($datedifference > 30) {
        $Remaining_Time_In_Seconds = 0;
    }

    return $Remaining_Time_In_Seconds;
}

function getTotalOrderDetailItemsCount($iOrderId) {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT count(iOrderDetailId) as TotalOrderItems FROM order_details WHERE iOrderId = '" . $iOrderId . "'";
    $data = $obj->MySQLSelect($sql);
    $TotalOrderItems = $data[0]['TotalOrderItems'];
    if ($TotalOrderItems == "" || $TotalOrderItems == NULL) {
        $TotalOrderItems = 0;
    }

    return $TotalOrderItems;
}

function OrderTotalEarningForRestaurant($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone, $vSubFilterParam = '') {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();

    $vConvertFromDatec = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    if (!empty($vConvertToDate)) {
        $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    }
    $whereFilter = "";
    if ($vConvertFromDate != "") {
        $whereFilter = "DATE(tOrderRequestDate) = '" . $vConvertFromDate . "' AND ";
    }
    if ($vConvertFromDate != "" && $vConvertToDate != "") {
        $whereFilter = "(DATE(tOrderRequestDate) BETWEEN '$vConvertFromDatec' AND '$vConvertToDate') AND ";
    }

    $conditonalFields = 'iCompanyId';
    $UserDetailsArr = getCompanyCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];

    // $priceRatio = $UserDetailsArr['Ratio'];
    // Total earning calulated according to filter
    $enable_takeaway = 0;
    if (isTakeAwayEnable() && $UserType != 'Driver') {
        $enable_takeaway = 1;
    }
    $whereStatusCode = "AND  `iStatusCode` IN (6, 7, 8, 11)";
    if ($vSubFilterParam != "") {
        if ($vSubFilterParam == '6-1' && $enable_takeaway == 1) {
            $whereStatusCode = " AND ord.iStatusCode IN (6) AND ord.eTakeaway = 'Yes'";
        } else if ($vSubFilterParam == '6' && $enable_takeaway == 1) {
            $whereStatusCode = " AND ord.iStatusCode IN ($vSubFilterParam) AND ord.eTakeaway = 'No'";
        } else if ($vSubFilterParam == 8) {
            $whereStatusCode = "AND  `iStatusCode` IN (7, 8)";
        } else {
            $whereStatusCode = "AND  `iStatusCode` IN ($vSubFilterParam)";
        }
    }
    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fCommision, iStatusCode, fNetTotal, fOffersDiscount, fRatio_" . $currencycode . " as Ratio,fRestaurantPaidAmount,fDeliveryCharge FROM `orders` as ord WHERE $whereFilter $conditonalFields='$iGeneralUserId' $whereStatusCode";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    $TotalEarningFare = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $iStatusCode = $value['iStatusCode'];
        $fRestaurantPaidAmount = $value['fRestaurantPaidAmount'];
        if ($iStatusCode == '7' || $iStatusCode == '8') {
            $EarningFare = $fRestaurantPaidAmount;
        } else {
            $EarningFare = $value['fTotalGenerateFare'] - ($value['fCommision'] + $value['fOffersDiscount'] + $value['fDeliveryCharge']);
        }
        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $generalobj->setTwoDecimalPoint($ToTalEarning);
        // $TotalEarningFare = $ToTalEarning * $priceRatio;
    }

    return $TotalEarningFare;
}

function OrderTotalEarningForDriver($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone, $vSubFilterParam = '') {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();
    $vConvertFromDatec = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    if (!empty($vConvertToDate)) {
        $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    }
    $whereFilter = "";
    if ($vConvertFromDate != "") {
        $whereFilter = "DATE(tOrderRequestDate) = '" . $vConvertFromDate . "' AND ";
    }
    if ($vConvertFromDate != "" && $vConvertToDate != "") {
        $whereFilter = "(DATE(tOrderRequestDate) BETWEEN '$vConvertFromDatec' AND '$vConvertToDate') AND ";
    }
    $conditonalFields = 'iDriverId';
    $UserDetailsArr = getDriverCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];

    // $priceRatio = $UserDetailsArr['Ratio'];
    // Total earning calulated according to filter
    $enable_takeaway = 0;
    if (isTakeAwayEnable() && $UserType != 'Driver') {
        $enable_takeaway = 1;
    }
    $whereStatusCode = "AND  `iStatusCode` IN (6, 7, 8, 11)";
    if ($vSubFilterParam != "") {
        if ($vSubFilterParam == '6-1' && $enable_takeaway == 1) {
            $whereStatusCode = " AND ord.iStatusCode IN (6) AND ord.eTakeaway = 'Yes'";
        } else if ($vSubFilterParam == '6' && $enable_takeaway == 1) {
            $whereStatusCode = " AND ord.iStatusCode IN ($vSubFilterParam) AND ord.eTakeaway = 'No'";
        } else if ($vSubFilterParam == 8) {
            $whereStatusCode = "AND  `iStatusCode` IN (7, 8)";
        } else {
            $whereStatusCode = "AND  `iStatusCode` IN ($vSubFilterParam)";
        }
    }

    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fNetTotal, fCommision, iStatusCode, fRatio_" . $currencycode . " as Ratio,fDriverPaidAmount FROM `orders` WHERE $whereFilter $conditonalFields='$iGeneralUserId' $whereStatusCode";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $OrderId = $value['iOrderId'];
        $iStatusCode = $value['iStatusCode'];
        $fDriverPaidAmount = $value['fDriverPaidAmount'];
        $subquery = "SELECT fDeliveryCharge FROM trips WHERE iOrderId = '" . $OrderId . "'";
        $DriverCharge = $obj->MySQLSelect($subquery);
        if ($iStatusCode == '7' || $iStatusCode == '8') {
            $EarningFare = $fDriverPaidAmount;
        } else {
            $EarningFare = $DriverCharge[0]['fDeliveryCharge'];
        }

        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $generalobj->setTwoDecimalPoint($ToTalEarning);
    }

    return $TotalEarningFare;
}

function OrderTotalEarningForPassanger($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone) {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();

    $vConvertFromDatec = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    if (!empty($vConvertToDate)) {
        $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    }
    $whereFilter = "";
    if ($vConvertFromDate != "") {
        $whereFilter = "DATE(tOrderRequestDate) = '" . $vConvertFromDate . "' AND ";
    }
    if ($vConvertFromDate != "" && $vConvertToDate != "") {
        $whereFilter = "(DATE(tOrderRequestDate) BETWEEN '$vConvertFromDatec' AND '$vConvertToDate') AND ";
    }

    $conditonalFields = 'iUserId';
    $UserDetailsArr = getUserCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];

    // $priceRatio = $UserDetailsArr['Ratio'];
    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fCommision, fNetTotal, iStatusCode, fRatio_" . $currencycode . " as Ratio FROM `orders` WHERE $whereFilter $conditonalFields='$iGeneralUserId' AND  `iStatusCode` IN (6, 7, 8, 11, 9)";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $EarningFare = $value['fNetTotal'];
        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $generalobj->setTwoDecimalPoint($ToTalEarning);
    }

    return $TotalEarningFare;
}

########################### Get Total Order Discount Amount From order detail for menu item wise##########################

function getOrderDetailTotalDiscountPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT SUM( `fTotalDiscountPrice` ) AS TotalDiscountPrice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $TotalDiscountPrice = $data[0]['TotalDiscountPrice'];
    if ($TotalDiscountPrice == "" || $TotalDiscountPrice == NULL) {
        $TotalDiscountPrice = 0;
    }

    return $TotalDiscountPrice;
}

########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### Get Total Order Discount Amount From order detail for menu item wise##########################

function getOrderDetailSubTotalPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;

    // $sql = "SELECT SUM( `fOriginalPrice` * `iQty` ) AS TotalOriginalPrice FROM order_details WHERE iOrderId = '".$iOrderId."' AND eAvailable = 'Yes'";
    $sql = "SELECT SUM( `fTotalPrice` ) AS TotalPrice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $TotalPrice = $data[0]['TotalPrice'];
    if ($TotalPrice == "" || $TotalPrice == NULL) {
        $TotalPrice = 0;
    }

    return $TotalPrice;
}

########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### Calculate Order Discount Amount By Company Offer and menu item wise###########################

function CalculateOrderDiscountPrice($iOrderId) {
    global $obj, $generalobj, $tconfig;
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];

    // $fSubTotal = $data_order[0]['fSubTotal'];
    $fSubTotal = getOrderDetailSubTotalPrice($iOrderId);
    $iUserId = $data_order[0]['iUserId'];
    $TotOrders = 1;
    if ($iUserId > 0) {
        $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iStatusCode NOT IN(12)";
        $db_order = $obj->MySQLSelect($sql);
        $TotOrders = $db_order[0]['TotOrders'];
    }

    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferType = $DataCompany[0]['fOfferType'];
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    $fOfferAmt = $DataCompany[0]['fOfferAmt'];
    if ($fOfferAppyType == "None") {
        $TotalDiscountPrice = getOrderDetailTotalDiscountPrice($iOrderId);
    } else if ($fOfferAppyType == "All") {
        if ($fSubTotal >= $fTargetAmt) {
            if ($fOfferType == "Percentage") {
                $fDiscount = (($fSubTotal * $fOfferAmt) / 100);
                $fDiscount = $generalobj->setTwoDecimalPoint($fDiscount);
                $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscount;
                $TotalDiscountPrice = $fDiscount;
            } else {
                $fDiscount = $fOfferAmt;
                $fDiscount = $generalobj->setTwoDecimalPoint($fDiscount);
                //Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3793 Start
                if ($fDiscount > $fSubTotal) {
                    $fDiscount = $fSubTotal;
                }
                //Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3793 End
                $TotalDiscountPrice = $fDiscount;
            }
        } else {
            $TotalDiscountPrice = 0;
        }
    } else {
        if ($TotOrders <= 1) {
            if ($fSubTotal >= $fTargetAmt) {
                if ($fOfferType == "Percentage") {
                    $fDiscount = (($fSubTotal * $fOfferAmt) / 100);
                    $fDiscount = $generalobj->setTwoDecimalPoint($fDiscount);
                    $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscount;
                    $TotalDiscountPrice = $fDiscount;
                } else {
                    $fDiscount = $fOfferAmt;
                    $fDiscount = $generalobj->setTwoDecimalPoint($fDiscount);
                    //Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3793 Start
                    if ($fDiscount > $fSubTotal) {
                        $fDiscount = $fSubTotal;
                    }
                    //Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3793 End
                    $TotalDiscountPrice = $fDiscount;
                }
            } else {
                $TotalDiscountPrice = 0;
            }
        } else {
            $TotalDiscountPrice = getOrderDetailTotalDiscountPrice($iOrderId);
        }
    }

    return $generalobj->setTwoDecimalPoint($TotalDiscountPrice);
}

########################### Calculate Order Discount Amount By Company Offer and menu item wise###########################
########################### Get Menu Item Price By Restaurant Offer Wise##################################################

function getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, $iQty = 1, $iUserId = 0, $eFor = "Display", $vOptionId = "", $vAddonId = "", $iServiceId = "") {
    global $obj, $generalobj, $tconfig;
    $TotOrders = $fPrice = 0;
    if ($iUserId > 0) {
        $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iStatusCode NOT IN(12)";
        $db_order = $obj->MySQLSelect($sql);
        $TotOrders = $db_order[0]['TotOrders'];
    }
    $ispriceshow = '';
    if (isset($iServiceId) && !empty($iServiceId)) {
        $servFields = 'eType';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
                $ispriceshow = $ServiceCategoryData[0]['eType'];
            }
        }
    }
    $db_price = $obj->MySQLSelect("select iFoodMenuId,fPrice,fOfferAmt from menu_items where iMenuItemId ='" . $iMenuItemId . "'");
    if (isset($db_price[0]['fPrice']) && $db_price[0]['fPrice'] > 0) {
        $fPrice = $db_price[0]['fPrice'];
    }
    if (isset($ispriceshow) && !empty($ispriceshow)) {
        $fPrice = 0;
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        if ($vOptionPrice == 0) {
            $fPrice = $db_price[0]['fPrice'];
        }
    }
    if ($vOptionId != "") {
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        $fPrice += $vOptionPrice;
    }
    if ($vAddonId != "") {
        $vAddonPrice = GetFoodMenuItemAddOnPrice($vAddonId);
        $fPrice += $vAddonPrice;
    }

    $fPrice = $fPrice * $iQty;
    $fOriginalPrice = $fPrice;
    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferType = $DataCompany[0]['fOfferType'];
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    if ($fOfferAppyType == "None") {
        $fOfferAmt = $db_price[0]['fOfferAmt'];
        if ($fOfferAmt > 0) {
            $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
            $fDiscountPrice = round($fDiscountPrice, 2);
            $fPrice = $fPrice - $fDiscountPrice;
        } else {
            $fOfferAmt = 0;
            $fDiscountPrice = 0;
        }

        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    } else if ($fOfferAppyType == "All") {
        $fOfferAmt = $DataCompany[0]['fOfferAmt'];
        if ((($fTargetAmt == 0 || $fTargetAmt == "") && $eFor == "Display") || $eFor == "Calculate") {
            if ($fOfferType == "Percentage") {
                if ($fOfferAmt > 0) {
                    $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                    $fDiscountPrice = round($fDiscountPrice, 2);
                    $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscountPrice;
                    $fPrice = $fOriginalPrice - $fDiscountPrice;
                } else {
                    $fOfferAmt = 0;
                    $fDiscountPrice = 0;
                }
            } else {
                if ($eFor == "Calculate") {
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = $fOfferAmt * $iQty;
                        $fDiscountPrice = ($fDiscountPrice < 0) ? 0 : $fDiscountPrice;
                        $fPrice = $fOriginalPrice;
                    } else {
                        $fOfferAmt = 0;
                        $fDiscountPrice = 0;
                    }
                } else {
                    $fOfferAmt = 0;
                    $fDiscountPrice = 0;
                }
            }
        } else {
            $fOfferAmt = 0;
            $fDiscountPrice = 0;
        }

        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    } else {
        if ($TotOrders == 0) {
            $fOfferAmt = $DataCompany[0]['fOfferAmt'];
            if ((($fTargetAmt == 0 || $fTargetAmt == "") && $eFor == "Display") || $eFor == "Calculate") {
                if ($fOfferType == "Percentage") {
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                        $fDiscountPrice = round($fDiscountPrice, 2);

                        // $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0))?$fMaxOfferAmt:$fDiscountPrice;
                        $fPrice = $fOriginalPrice - $fDiscountPrice;
                    } else {
                        $fOfferAmt = 0;
                        $fDiscountPrice = 0;
                    }
                } else {
                    if ($eFor == "Calculate") {
                        if ($fOfferAmt > 0) {
                            $fDiscountPrice = $fOfferAmt;
                            $fDiscountPrice = ($fDiscountPrice < 0) ? 0 : $fDiscountPrice;
                            $fPrice = $fOriginalPrice;
                        } else {
                            $fOfferAmt = 0;
                            $fDiscountPrice = 0;
                        }
                    } else {
                        $fOfferAmt = 0;
                        $fDiscountPrice = 0;
                    }
                }
            } else {
                $fOfferAmt = 0;
                $fDiscountPrice = 0;
            }
        } else {
            $fOfferAmt = $db_price[0]['fOfferAmt'];
            if ($fOfferAmt > 0) {
                $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                $fDiscountPrice = round($fDiscountPrice, 2);
                $fPrice = $fOriginalPrice - $fDiscountPrice;
            } else {
                $fOfferAmt = 0;
                $fDiscountPrice = 0;
            }
        }

        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    }

    //echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

########################### Get Menu Item Price By Restaurant Offer Wise##################################################
############################# Get Menu Item Option / AddOn Name ##################################################################

function GetMenuItemOptionsToppingName($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    $vOptionName = "";
    if ($iOptionId != "") {
        $str = "select vOptionName from `menuitem_options` where iOptionId IN(" . $iOptionId . ")";
        $db_options_data = $obj->MySQLSelect($str);
        if (count($db_options_data) > 0) {
            for ($i = 0; $i < count($db_options_data); $i++) {
                $vOptionName .= $db_options_data[$i]['vOptionName'] . ", ";
            }
        }

        $vOptionName = substr($vOptionName, 0, -2);
    }

    return $vOptionName;
}

############################# Get Menu Item Option Name ##################################################################
############################# Get Order Status Code Text ##################################################################

function GetOrderStatusLogText($iOrderId, $UserType = "Passenger", $eRemoveDate = "No") {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT ord.iUserId,ord.iDriverId,ord.iCompanyId,ord.iStatusCode,ord.iServiceId,os.vStatus_Track,os.vStatus,osl.dDate FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode LEFT JOIN order_status_logs as osl ON osl.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "' ORDER BY osl.dDate DESC LIMIT 0,1";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iDriverId = $data_order[0]['iDriverId'];
    $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
    $Data_vehicle = $obj->MySQLSelect($sql);
    $drivername = $Data_vehicle[0]['driverName'];
    $iStatusCode = $data_order[0]['iStatusCode'];
    $dDate = $data_order[0]['dDate'];
    $vStatus = $data_order[0]['vStatus'];
    $iServiceId = $data_order[0]['iServiceId'];

    // $StatusDate = date('l, dS M Y',strtotime($dDate));
    $StatusDate = date('F d, Y h:iA', strtotime($dDate)); //h:iA
    if ($UserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
    } else if ($UserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iDriverId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId, $iOrderId);
    }

    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $Displaytext = "";
    if ($iStatusCode == "8") {
        $Displaytext = $languageLabelsArr['LBL_CANCELLED_ON'] . " " . $StatusDate;
        if ($eRemoveDate == "Yes") {
            $Displaytext = $languageLabelsArr['LBL_CANCELLED_ON'];
        }
    }

    if ($iStatusCode == "6") {
        $Displaytext = $languageLabelsArr['LBL_ORDER_DELIVERED_ON'] . " " . $StatusDate . " " . $languageLabelsArr['LBL_BY'] . " " . $drivername;
        if ($eRemoveDate == "Yes") {
            $Displaytext = $languageLabelsArr['LBL_ORDER_DELIVERED_ON'] . " " . $languageLabelsArr['LBL_BY'] . " " . $drivername;
        }
    }

    return $Displaytext;
}

############################# Get Order Status Code Text ##################################################################
############################# Check Menu Item Availability When Order Placed By User#######################################

function checkmenuitemavailability($OrderDetails = array()) {
    global $obj, $generalobj, $tconfig;
    $isAllItemAvailable = "Yes";
    $isAllItemOptionsAvailable = "Yes";
    $isAllItemToppingssAvailable = "Yes";
    if (count($OrderDetails) > 0) {
        for ($i = 0; $i < count($OrderDetails); $i++) {
            $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $str = "select eAvailable,eStatus from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
            $db_menu_item = $obj->MySQLSelect($str);
            $eStatus = $db_menu_item[0]['eStatus'];
            $eAvailable = $db_menu_item[0]['eAvailable'];
            if ($eAvailable == "No" || $eStatus != "Active") {
                $isAllItemAvailable = "No";
                break;
            }
        }

        for ($j = 0; $j < count($OrderDetails); $j++) {
            $vOptionId = $OrderDetails[$j]['vOptionId'];
            if ($vOptionId != "") {
                $str = "select eStatus from menuitem_options where iOptionId IN(" . $vOptionId . ")";
                $db_menu_item_option = $obj->MySQLSelect($str);
                $eStatus1 = $db_menu_item_option[0]['eStatus'];
                if ($eStatus1 != "Active") {
                    $isAllItemOptionsAvailable = "No";
                    break;
                }
            }
        }

        for ($k = 0; $k < count($OrderDetails); $k++) {
            $vAddonId = $OrderDetails[$k]['vAddonId'];
            if ($vAddonId != "") {
                $str = "select eStatus from menuitem_options where iOptionId IN(" . $vAddonId . ")";
                $db_menu_item_Addon = $obj->MySQLSelect($str);
                $eStatus2 = $db_menu_item_Addon[0]['eStatus'];
                if ($eStatus2 != "Active") {
                    $isAllItemToppingssAvailable = "No";
                    break;
                }
            }
        }
    }

    $returnArr['isAllItemAvailable'] = $isAllItemAvailable;
    $returnArr['isAllItemOptionsAvailable'] = $isAllItemOptionsAvailable;
    $returnArr['isAllItemToppingssAvailable'] = $isAllItemToppingssAvailable;
    return $returnArr;
}

############################# Check Menu Item Availability When Order Placed By User#######################
############# Get Text For Order Refund Or Cancelled ###############

function GetOrderStatusLogTextForCancelledSplit($iOrderId, $UserType = "Passenger") { //added by SP for cubex on 11-10-2019
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT ord.iUserId,ord.iDriverId,ord.iCompanyId,ord.fRefundAmount,ord.iStatusCode,ord.iServiceId,os.vStatus_Track,os.vStatus,osl.dDate,ord.fCancellationCharge,ord.fRestaurantPaidAmount,ord.fDriverPaidAmount FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode LEFT JOIN order_status_logs as osl ON osl.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "' ORDER BY osl.dDate DESC LIMIT 0,1";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iDriverId = $data_order[0]['iDriverId'];
    $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
    $Data_vehicle = $obj->MySQLSelect($sql);
    $drivername = $Data_vehicle[0]['driverName'];
    $iStatusCode = $data_order[0]['iStatusCode'];
    $dDate = $data_order[0]['dDate'];
    $vStatus = $data_order[0]['vStatus'];
    $fRefundAmount = $data_order[0]['fRefundAmount'];
    $fCancellationCharge = $data_order[0]['fCancellationCharge'];
    $fRestaurantPaidAmount = $data_order[0]['fRestaurantPaidAmount'];
    $fDriverPaidAmount = $data_order[0]['fDriverPaidAmount'];
    $iServiceId = $data_order[0]['iServiceId'];

    // $StatusDate = date('l, dS M Y',strtotime($dDate));
    $StatusDate = date('F d, Y h:iA', strtotime($dDate)); //h:iA
    if ($UserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
    } else if ($UserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iDriverId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId, $iOrderId);
    }

    $Ratio = $UserDetailsArr['Ratio'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $Displaytext = $Displaytext1 = "";
    if ($UserType == "Passenger") {
        if ($iStatusCode == "8") {
            $fCancellationChargeNew = $fCancellationCharge * $Ratio;
            $fCancellationCharge = formatNum($fCancellationChargeNew);
            $CancellationCharge = $currencySymbol . $fCancellationCharge;
            $CancellationChargeTxt = $languageLabelsArr["LBL_CANCELLATION_CHARGE"] . ":" . $CancellationCharge;
            $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"];
            $Displaytext1 = $CancellationChargeTxt;
        }

        if ($iStatusCode == "7") {

            // $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"];
            $fCancellationChargeNew = $fCancellationCharge * $Ratio;
            $fCancellationCharge = formatNum($fCancellationChargeNew);
            $CancellationCharge = $currencySymbol . $fCancellationCharge;
            $CancellationChargeTxt = $languageLabelsArr["LBL_CANCELLATION_CHARGE"] . ":" . $CancellationCharge;
            $fRefundAmountnew = $fRefundAmount * $Ratio;
            $fRefundAmount = formatNum($fRefundAmountnew);
            $RefundAmount = $currencySymbol . $fRefundAmount;
            $RefundAmountTxt = $languageLabelsArr["LBL_REFUND_APP_TXT"] . ":" . $RefundAmount;
            $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT_CUBEX"];
            $Displaytext1 = $CancellationChargeTxt . "\n" . $RefundAmountTxt;
        }
    } else if ($UserType == "Company") {
        if ($iStatusCode == "8" || $iStatusCode == "7") {
            $fRestaurantPaidAmountNew = $fRestaurantPaidAmount * $Ratio;
            $fRestaurantPaidAmount = formatNum($fRestaurantPaidAmountNew);
            $fRestaurantPaidAmount = $currencySymbol . $fRestaurantPaidAmount;
            if ($data_order[0]['fRestaurantPaidAmount'] > 0) {
                $fRestaurantPaidAmountTxt = $languageLabelsArr["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] . ":" . $fRestaurantPaidAmount;
            } else {
                $fRestaurantPaidAmountTxt = $languageLabelsArr["LBL_AMT_GENERATE_PENDING"];
            }

            if ($iStatusCode == "8") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"];
                $Displaytext1 = $fRestaurantPaidAmountTxt;
            } else if ($iStatusCode == "7") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT_CUBEX"];
                $Displaytext1 = $fRestaurantPaidAmountTxt;
            }
        }
    } else {
        if ($iStatusCode == "8" || $iStatusCode == "7") {
            $fDriverPaidAmountNew = $fDriverPaidAmount * $Ratio;
            $fDriverPaidAmount = formatNum($fDriverPaidAmountNew);
            $fDriverPaidAmount = $currencySymbol . $fDriverPaidAmount;
            if ($data_order[0]['fDriverPaidAmount'] > 0) {
                $fDriverPaidAmountTxt = $languageLabelsArr["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] . ":" . $fDriverPaidAmount;
            } else {
                $fDriverPaidAmountTxt = $languageLabelsArr["LBL_AMT_GENERATE_PENDING"];
            }

            if ($iStatusCode == "8") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"];
                $Displaytext1 = $fDriverPaidAmountTxt;
            } else if ($iStatusCode == "7") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT_CUBEX"];
                $Displaytext1 = $fDriverPaidAmountTxt;
            }
        }
    }

    $returnArr['Displaytext'] = $Displaytext;
    $returnArr['Displaytext1'] = $Displaytext1;
    return $returnArr;
}

function GetOrderStatusLogTextForCancelled($iOrderId, $UserType = "Passenger") {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT ord.iUserId,ord.iDriverId,ord.iCompanyId,ord.fRefundAmount,ord.iStatusCode,ord.iServiceId,os.vStatus_Track,os.vStatus,osl.dDate,ord.fCancellationCharge,ord.fRestaurantPaidAmount,ord.fDriverPaidAmount FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode LEFT JOIN order_status_logs as osl ON osl.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "' ORDER BY osl.dDate DESC LIMIT 0,1";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iDriverId = $data_order[0]['iDriverId'];
    $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
    $Data_vehicle = $obj->MySQLSelect($sql);
    $drivername = $Data_vehicle[0]['driverName'];
    $iStatusCode = $data_order[0]['iStatusCode'];
    $dDate = $data_order[0]['dDate'];
    $vStatus = $data_order[0]['vStatus'];
    $fRefundAmount = $data_order[0]['fRefundAmount'];
    $fCancellationCharge = $data_order[0]['fCancellationCharge'];
    $fRestaurantPaidAmount = $data_order[0]['fRestaurantPaidAmount'];
    $fDriverPaidAmount = $data_order[0]['fDriverPaidAmount'];
    $iServiceId = $data_order[0]['iServiceId'];

    // $StatusDate = date('l, dS M Y',strtotime($dDate));
    $StatusDate = date('F d, Y h:iA', strtotime($dDate)); //h:iA
    if ($UserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
    } else if ($UserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iDriverId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId, $iOrderId);
    }

    $Ratio = $UserDetailsArr['Ratio'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $Displaytext = "";
    if ($UserType == "Passenger") {
        if ($iStatusCode == "8") {
            $fCancellationChargeNew = $fCancellationCharge * $Ratio;
            $fCancellationCharge = formatNum($fCancellationChargeNew);
            $CancellationCharge = $currencySymbol . $fCancellationCharge;
            $CancellationChargeTxt = $languageLabelsArr["LBL_CANCELLATION_CHARGE"] . ":" . $CancellationCharge;
            $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $CancellationChargeTxt;
        }

        if ($iStatusCode == "7") {

            // $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"];
            $fCancellationChargeNew = $fCancellationCharge * $Ratio;
            $fCancellationCharge = formatNum($fCancellationChargeNew);
            $CancellationCharge = $currencySymbol . $fCancellationCharge;
            $CancellationChargeTxt = $languageLabelsArr["LBL_CANCELLATION_CHARGE"] . ":" . $CancellationCharge;
            $fRefundAmountnew = $fRefundAmount * $Ratio;
            $fRefundAmount = formatNum($fRefundAmountnew);
            $RefundAmount = $currencySymbol . $fRefundAmount;
            $RefundAmountTxt = $languageLabelsArr["LBL_REFUND_APP_TXT"] . ":" . $RefundAmount;
            $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $CancellationChargeTxt . "\n" . $RefundAmountTxt;
        }
    } else if ($UserType == "Company") {
        if ($iStatusCode == "8" || $iStatusCode == "7") {
            $fRestaurantPaidAmountNew = $fRestaurantPaidAmount * $Ratio;
            $fRestaurantPaidAmount = formatNum($fRestaurantPaidAmountNew);
            $fRestaurantPaidAmount = $currencySymbol . $fRestaurantPaidAmount;
            if ($data_order[0]['fRestaurantPaidAmount'] > 0) {
                $fRestaurantPaidAmountTxt = $languageLabelsArr["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] . ":" . $fRestaurantPaidAmount;
            } else {
                $fRestaurantPaidAmountTxt = $languageLabelsArr["LBL_AMT_GENERATE_PENDING"];
            }

            if ($iStatusCode == "8") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $fRestaurantPaidAmountTxt;
            } else if ($iStatusCode == "7") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $fRestaurantPaidAmountTxt;
            }
        }
    } else {
        if ($iStatusCode == "8" || $iStatusCode == "7") {
            $fDriverPaidAmountNew = $fDriverPaidAmount * $Ratio;
            $fDriverPaidAmount = formatNum($fDriverPaidAmount);
            $fDriverPaidAmount = $currencySymbol . $fDriverPaidAmount;
            if ($data_order[0]['fDriverPaidAmount'] > 0) {
                $fDriverPaidAmountTxt = $languageLabelsArr["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] . ":" . $fDriverPaidAmount;
            } else {
                $fDriverPaidAmountTxt = $languageLabelsArr["LBL_AMT_GENERATE_PENDING"];
            }

            if ($iStatusCode == "8") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $fDriverPaidAmountTxt;
            } else if ($iStatusCode == "7") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $fDriverPaidAmountTxt;
            }
        }
    }

    // $returnArr['Displaytext'] = $Displaytext;
    return $Displaytext;
}

############# ENd Text For Order Refund Or Cancelled ###############
############# Update Company LAt Long For Demo Mode ###############

function updatecompanylatlong($latitude, $longitude, $iCompanyId) {
    global $obj, $generalobj, $tconfig, $GOOGLE_SEVER_API_KEY_WEB;
    if (SITE_TYPE == "Demo") {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?key=" . $GOOGLE_SEVER_API_KEY_WEB . "&language=en&latlng=" . $latitude . "," . $longitude;
        $jsonfile = file_get_contents($url);
        $jsondata = json_decode($jsonfile);
        $location_Address = $jsondata->results[0]->formatted_address;
        $latitude_new = $jsondata->results[0]
                ->geometry
                ->location->lat;
        $longitude_new = $jsondata->results[0]
                ->geometry
                ->location->lng;
        if ($location_Address == "" || $location_Address == NULL) {
            $FilterArray = array(
                0.0015,
                0.0020,
                0.0025,
                0.0030,
                0.0035,
                0.0040
            );
            $k = array_rand($FilterArray);
            $num = $FilterArray[$k];
            $latitude_new = $latitude + $num;
            $longitude_new = $longitude + $num;
            $location_Address = getAddressFromLocation($latitude_new, $longitude_new, $GOOGLE_SEVER_API_KEY_WEB);
        }

        $where = " iCompanyId = '" . $iCompanyId . "'";
        $Data['vRestuarantLocation'] = $location_Address;
        $Data['vCaddress'] = $location_Address;
        $Data['vRestuarantLocationLat'] = $latitude_new;
        $Data['vRestuarantLocationLong'] = $longitude_new;
        $Data['eLock'] = "Yes";
        $id = $obj->MySQLQueryPerform("company", $Data, 'update', $where);
    }

    return $iCompanyId;
}

############# Update Company LAt Long For Demo Mode ###############
################# Display Recommended and Best Seller Menu Items#############################

function getRecommendedBestSellerMenuItems_old($iCompanyId, $iUserId, $DisplayType = "Recommended", $CheckNonVegFoodType = "No", $iServiceId = "") {
    global $obj, $generalobj, $tconfig;
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $returnArr = array();
    $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, 0);
    if (!empty($vLang)) {
        $vLanguage = $vLang;
    } else {
        $vLanguage = $UserDetailsArr['vLang'];
    }
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $currencycode = $UserDetailsArr['currencycode'];
    $Ratio = $UserDetailsArr['Ratio'];
    $ssql1 = "";
    if ($DisplayType == "Recommended") {
        $ssql1 .= " AND eRecommended = 'Yes' ";
    } else {
        $ssql1 .= " AND eBestSeller = 'Yes' ";
    }
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration Start
    $store_currency = get_value('company', 'store_currency', 'iCompanyId', $iCompanyId, '', 'true');
    $currencyArr = $currencySymbolArr = array();
    $getCurrencyRation = $obj->MySQLSelect("SELECT Ratio,iCurrencyId,vSymbol FROM currency WHERE eStatus='Active'");
    for ($c = 0; $c < count($getCurrencyRation); $c++) {
        $currencyArr[$getCurrencyRation[$c]['iCurrencyId']] = $getCurrencyRation[$c]['Ratio'];
        $currencySymbolArr[$getCurrencyRation[$c]['iCurrencyId']] = $getCurrencyRation[$c]['vSymbol'];
    }
    if (isset($currencyArr[$store_currency])) {
        $Ratio = $currencyArr[$store_currency];
        $currencySymbol = $currencySymbolArr[$store_currency];
    }
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration End
    $sql = "SELECT fm.* FROM food_menu as fm WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' ORDER BY fm.iDisplayOrder ASC";
    $db_food_data = $obj->MySQLSelect($sql);
    $MenuItemsDataArr = array();
    $itemimimgUrl = $tconfig["tsite_upload_images_menu_item"];
    if (count($db_food_data) > 0) {
        $ssql = "";

        //added by SP on 21-10-2019 for cubex design
        if ($CheckNonVegFoodType == 'Yes') {
            $ssql .= " AND (eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == 'No') {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        }

        //old leave as it is bc if pass from app like this value then no problem in future..
        if ($CheckNonVegFoodType == "Veg") {
            $ssql .= " AND (eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "NonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "VegNonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "NonVegVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        }

        if ($searchword != "") {
            $ssql .= " AND LOWER(mi.vItemType_" . $vLanguage . ") LIKE '%" . $searchword . "%' ";
        }
        $foodMenuIteIds = "";
        for ($h = 0; $h < count($db_food_data); $h++) {
            $foodMenuIteIds .= ",'" . $db_food_data[$h]['iFoodMenuId'] . "'";
        }
        $foodItemArr = $menuItemArr = $topingArr = $itemPriceArr = array();
        if ($foodMenuIteIds != "") {
            $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
            $foodItems = trim($foodMenuIteIds, ",");
            //$sqlf = "SELECT mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_" . $vLanguage . " as vItemType,mi.vItemDesc_" . $vLanguage . " as vItemDesc, mi.fPrice, mi.eFoodType, mi.fOfferAmt,mi.vImage, mi.iDisplayOrder, mi.vHighlightName FROM menu_items as mi WHERE mi.iFoodMenuId IN ($foodItems) AND mi.eStatus='Active' AND mi.eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC";
            $sqlf = "SELECT eRecommended,iMenuItemId,iFoodMenuId,vItemType_" . $vLanguage . " as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName,prescription_required FROM menu_items WHERE iFoodMenuId IN ($foodItems) AND eStatus='Active' AND eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC"; //prescription_required added by SP

            /* if($_REQUEST['test']==1) {
              echo $sqlf; exit;
              } */

            $dbItemData = $obj->MySQLSelect($sqlf);
            //echo "<pre>";print_r($dbItemData);die;
            for ($d = 0; $d < count($dbItemData); $d++) {
                //Added By HJ On 17-10-2019 For Get Highlight Label Value Start
                $vHighlightNameLBL = $dbItemData[$d]['vHighlightName'];
                if (isset($languageLabelsArr[$dbItemData[$d]['vHighlightName']]) && $dbItemData[$d]['vHighlightName'] != "" && $dbItemData[$d]['vHighlightName'] != null) {
                    $vHighlightNameLBL = $languageLabelsArr[$dbItemData[$d]['vHighlightName']];
                }
                $dbItemData[$d]['vHighlightNameLBL'] = $dbItemData[$d]['vHighlightName'];
                $dbItemData[$d]['vHighlightName'] = $vHighlightNameLBL;
                //Added By HJ On 17-10-2019 For Get Highlight Label Value End
                $foodItemArr[$dbItemData[$d]['iFoodMenuId']][] = $dbItemData[$d];
                $menuItemArr[] = $dbItemData[$d]['iMenuItemId'];
            }
        }
        //echo "<pre>";print_r($menuItemArr);die;
        if (count($menuItemArr) > 0) {
            $itemIds = implode(",", $menuItemArr);
            $topingArr = GetMenuItemOptionsTopping($itemIds, $currencySymbol, $Ratio, $vLanguage, $iServiceId, $iCompanyId);
            //$itemPriceArr = getMenuItemPriceByCompanyOffer($itemIds, $iCompanyId, 1, $iUserId, "Display", "", "");
            //echo "<pre>";print_r($itemPriceArr);die;
        }
        $itemimimgUrl = $tconfig["tsite_upload_images_menu_item"];
        for ($i = 0; $i < count($db_food_data); $i++) {
            $iFoodMenuId = $db_food_data[$i]['iFoodMenuId'];
            $vMenu = $db_food_data[$i]['vMenu_' . $vLanguage];
            //$sqlf = "SELECT mi.eRecommended,mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_" . $vLanguage . " as vItemType,mi.vItemDesc_" . $vLanguage . " as vItemDesc, mi.fPrice, mi.eFoodType, mi.fOfferAmt,mi.vImage, mi.iDisplayOrder, mi.vHighlightName FROM menu_items as mi WHERE mi.iFoodMenuId = '" . $iFoodMenuId . "' AND mi.eStatus='Active' AND mi.eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC";
            //$db_item_data = $obj->MySQLSelect($sqlf);
            if (isset($foodItemArr[$iFoodMenuId])) {
                $db_item_data = $foodItemArr[$iFoodMenuId];
                for ($j = 0; $j < count($db_item_data); $j++) {
                    $db_item_data[$j]['vCategoryName'] = '';
                    if (!empty($vMenu)) {
                        $db_item_data[$j]['vCategoryName'] = $vMenu;
                    }
                    $iMenuItemId = $db_item_data[$j]['iMenuItemId'];
                    $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, $iUserId, "Display", "", "", $iServiceId);
                    $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                    $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
                    $db_item_data[$j]['fOfferAmt'] = $fOfferAmt;
                    $db_item_data[$j]['fPrice'] = round($db_item_data[$j]['fPrice'] * $Ratio, 2);
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                        $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
                        $db_item_data[$j]['fDiscountPrice'] = $fDiscountPrice;
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    } else {
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['fDiscountPrice'] = $fPrice;
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    }

                    $itemimgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $db_item_data[$j]['vImage'];
                    if ($db_item_data[$j]['vImage'] != "" && file_exists($itemimgpth)) {
                        $db_item_data[$j]['vImageName'] = $db_item_data[$j]['vImage'];
                        $db_item_data[$j]['vImage'] = $itemimimgUrl . '/' . $db_item_data[$j]['vImage'];
                    } else {
                        $db_item_data[$j]['vImageName'] = '';
                        $db_item_data[$j]['vImage'] = $itemimimgUrl . '/sample_image.png';
                    }

                    //$MenuItemOptionToppingArr = GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $iServiceId);
                    $MenuItemOptionToppingArr = $customeToppings = array();
                    if (isset($topingArr[$iMenuItemId])) {
                        $MenuItemOptionToppingArr = $topingArr[$iMenuItemId];
                    }
                    $db_item_data[$j]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
                    array_push($MenuItemsDataArr, $db_item_data[$j]);
                }
            }
        }
    }

    /*   $sqlf = "SELECT mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_".$vLanguage." as vItemType,mi.vItemDesc_".$vLanguage." as vItemDesc,mi.fPrice,mi.eFoodType,mi.fOfferAmt,mi.vImage,mi.iDisplayOrder FROM menu_items as mi LEFT JOIN food_menu as f on f.iFoodMenuId=mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId WHERE mi.eStatus='Active' AND mi.eAvailable = 'Yes' AND f.iCompanyId = '".$restaId."'  $ssql ORDER BY RAND()";
      $db_item_data = $obj->MySQLSelect($sqlf);
      for($j=0;$j<count($db_item_data);$j++){
      $fPrice= round($db_item_data[$j]['fPrice']*$Ratio,2);
      $db_item_data[$j]['fPrice'] = formatNum($fPrice);
      if($db_item_data[$j]['vImage'] != ""){
      $db_item_data[$j]['vImage'] = $tconfig["tsite_upload_images_menu_item"]."/".$db_item_data[$j]['vImage'];
      }
      } */

    // $returnArr['Recomendation_Arr'] = $MenuItemsDataArr;
    // echo "<pre>";print_r($returnArr);exit;
    return $MenuItemsDataArr;
}

function getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, $DisplayType = "Recommended", $CheckNonVegFoodType = "No", $searchword, $iServiceId = "", $vLanguage = "") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    //Added By HJ On 23-01-2020 For Solved Currency Related Issues Start
    $currencySymbol = "";
    if ($iUserId > 0) {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, 0);
        if ($vLanguage == "") {
            $vLanguage = $UserDetailsArr['vLang'];
        }
        $currencySymbol = $UserDetailsArr['currencySymbol'];
        $currencycode = $UserDetailsArr['currencycode'];
        $Ratio = $UserDetailsArr['Ratio'];
    } else {
        //Added By HJ On 23-01-2020 For Get Currency Data Start
        $currencycode = isset($_REQUEST["vGeneralCurrency"]) ? $_REQUEST["vGeneralCurrency"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Curren cy Code
        if (($currencySymbol == "" || $currencySymbol == NULL) && $currencycode != "") {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE vName = '" . $currencycode . "'");
        } else {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'");
        }
        if (count($currencyData) > 0) {
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        } else {
            $currencycode = "USD";
            $currencySymbol = "$";
            $Ratio = 1.0000;
        }
        // Recomendation Arr 0.00 price issue Resolved START
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, 0);
        if ($vLanguage == "") {
            $vLanguage = $UserDetailsArr['vLang'];
        }
        // Recomendation Arr 0.00 price issue Resolved END
        //Added By HJ On 23-01-2020 For Get Currency Data End
    }
    //Added By HJ On 23-01-2020 For Solved Currency Related Issues End
    $ssql1 = "";
    if ($DisplayType == "Recommended") {
        $ssql1 .= " AND eRecommended = 'Yes' ";
    } else {
        $ssql1 .= " AND eBestSeller = 'Yes' ";
    }
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration Start
    $store_currency = get_value('company', 'store_currency', 'iCompanyId', $iCompanyId, '', 'true');
    $currencyArr = $currencySymbolArr = array();
    $getCurrencyRation = $obj->MySQLSelect("SELECT Ratio,iCurrencyId,vSymbol FROM currency WHERE eStatus='Active'");
    for ($c = 0; $c < count($getCurrencyRation); $c++) {
        $currencyArr[$getCurrencyRation[$c]['iCurrencyId']] = $getCurrencyRation[$c]['Ratio'];
        $currencySymbolArr[$getCurrencyRation[$c]['iCurrencyId']] = $getCurrencyRation[$c]['vSymbol'];
    }
    if (isset($currencyArr[$store_currency])) {
        $Ratio = $currencyArr[$store_currency];
        $currencySymbol = $currencySymbolArr[$store_currency];
    }
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration End
    $sql = "SELECT fm.* FROM food_menu as fm WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' ORDER BY fm.iDisplayOrder ASC";
    $db_food_data = $obj->MySQLSelect($sql);
    $MenuItemsDataArr = array();
    $itemimimgUrl = $tconfig["tsite_upload_images_menu_item"];
    if (count($db_food_data) > 0) {
        $ssql = "";
        //added by SP on 21-10-2019 for cubex design
        if ($CheckNonVegFoodType == 'Yes') {
            $ssql .= " AND (eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == 'No') {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        }
        if ($CheckNonVegFoodType == "Veg") {
            $ssql .= " AND eFoodType = 'Veg' ";
        } else if ($CheckNonVegFoodType == "NonVeg") {
            $ssql .= " AND eFoodType = 'NonVeg' ";
        } else if ($CheckNonVegFoodType == "VegNonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg') ";
        } else if ($CheckNonVegFoodType == "NonVegVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg') ";
        }
        if ($searchword != "") {
            $ssql .= " AND LOWER(vItemType_" . $vLanguage . ") LIKE '%" . $searchword . "%' ";
        }
        $foodMenuIteIds = "";
        for ($h = 0; $h < count($db_food_data); $h++) {
            $foodMenuIteIds .= ",'" . $db_food_data[$h]['iFoodMenuId'] . "'";
        }
        $foodItemArr = $menuItemArr = $topingArr = $itemPriceArr = array();
        if ($foodMenuIteIds != "") {
            $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
            //echo "<pre>";print_r($languageLabelsArr);die;
            $foodItems = trim($foodMenuIteIds, ",");
            //$sqlf = "SELECT mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_" . $vLanguage . " as vItemType,mi.vItemDesc_" . $vLanguage . " as vItemDesc, mi.fPrice, mi.eFoodType, mi.fOfferAmt,mi.vImage, mi.iDisplayOrder, mi.vHighlightName FROM menu_items as mi WHERE mi.iFoodMenuId IN ($foodItems) AND mi.eStatus='Active' AND mi.eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC";
            //$sqlf = "SELECT eRecommended,iMenuItemId,iFoodMenuId,vItemType_" . $vLanguage . " as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName,prescription_required FROM menu_items WHERE iFoodMenuId IN ($foodItems) AND eStatus='Active' AND eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC"; //prescription_required added by SP
            $def_lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            $sqlf = "SELECT eRecommended,iMenuItemId,iFoodMenuId,IFNULL(NULLIF(vItemType_" . $vLanguage . ", ''),vItemType_" . $def_lang . ") as vItemType, vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName,prescription_required FROM menu_items WHERE iFoodMenuId IN ($foodItems) AND eStatus='Active' AND eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC"; //prescription_required added by SP
            $dbItemData = $obj->MySQLSelect($sqlf);
            //echo "<pre>";print_r($dbItemData);die;
            for ($d = 0; $d < count($dbItemData); $d++) {
                //Added By HJ On 17-10-2019 For Get Highlight Label Value Start
                $vHighlightNameLBL = $dbItemData[$d]['vHighlightName'];
                if (isset($languageLabelsArr[$dbItemData[$d]['vHighlightName']]) && $dbItemData[$d]['vHighlightName'] != "" && $dbItemData[$d]['vHighlightName'] != null) {
                    $vHighlightNameLBL = $languageLabelsArr[$dbItemData[$d]['vHighlightName']];
                }
                $dbItemData[$d]['vHighlightNameLBL'] = $dbItemData[$d]['vHighlightName'];
                $dbItemData[$d]['vHighlightName'] = $vHighlightNameLBL;
                //Added By HJ On 17-10-2019 For Get Highlight Label Value End
                $foodItemArr[$dbItemData[$d]['iFoodMenuId']][] = $dbItemData[$d];
                $menuItemArr[] = $dbItemData[$d]['iMenuItemId'];
            }
        }
        //echo "<pre>";print_r($menuItemArr);die;
        if (count($menuItemArr) > 0) {
            $itemIds = implode(",", $menuItemArr);
            $topingArr = GetMenuItemOptionsTopping($itemIds, $currencySymbol, $Ratio, $vLanguage, $iServiceId, $iCompanyId);
            //$itemPriceArr = getMenuItemPriceByCompanyOffer($itemIds, $iCompanyId, 1, $iUserId, "Display", "", "");
            //echo "<pre>";print_r($itemPriceArr);die;
        }
        $itemimimgUrl = $tconfig["tsite_upload_images_menu_item"];
        for ($i = 0; $i < count($db_food_data); $i++) {
            $iFoodMenuId = $db_food_data[$i]['iFoodMenuId'];
            $vMenu = $db_food_data[$i]['vMenu_' . $vLanguage];
            //$sqlf = "SELECT mi.eRecommended,mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_" . $vLanguage . " as vItemType,mi.vItemDesc_" . $vLanguage . " as vItemDesc, mi.fPrice, mi.eFoodType, mi.fOfferAmt,mi.vImage, mi.iDisplayOrder, mi.vHighlightName FROM menu_items as mi WHERE mi.iFoodMenuId = '" . $iFoodMenuId . "' AND mi.eStatus='Active' AND mi.eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC";
            //$db_item_data = $obj->MySQLSelect($sqlf);
            if (isset($foodItemArr[$iFoodMenuId])) {
                $db_item_data = $foodItemArr[$iFoodMenuId];
                for ($j = 0; $j < count($db_item_data); $j++) {
                    $db_item_data[$j]['vCategoryName'] = '';
                    if (!empty($vMenu)) {
                        $db_item_data[$j]['vCategoryName'] = $vMenu;
                    }
                    $iMenuItemId = $db_item_data[$j]['iMenuItemId'];
                    $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, $iUserId, "Display", "", "", $iServiceId);
                    $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                    $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
                    $db_item_data[$j]['fOfferAmt'] = $fOfferAmt;
                    $db_item_data[$j]['fPrice'] = round($db_item_data[$j]['fPrice'] * $Ratio, 2);
                    $db_item_data[$j]['isShownDiscountPrice'] = "No";
                    if ($fOfferAmt > 0) {
                        $db_item_data[$j]['isShownDiscountPrice'] = "Yes";
                        $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                        $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
                        $db_item_data[$j]['fDiscountPrice'] = $fDiscountPrice;
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    } else {
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['fDiscountPrice'] = $fPrice;
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    }

                    $itemimgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $db_item_data[$j]['vImage'];
                    if ($db_item_data[$j]['vImage'] != "" && file_exists($itemimgpth)) {
                        $db_item_data[$j]['vImageName'] = $db_item_data[$j]['vImage'];
                        $db_item_data[$j]['vImage'] = $itemimimgUrl . '/' . $db_item_data[$j]['vImage'];
                    } else {
                        $db_item_data[$j]['vImageName'] = '';
                        $db_item_data[$j]['vImage'] = $itemimimgUrl . '/sample_image.png';
                    }

                    //$MenuItemOptionToppingArr = GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $iServiceId);
                    $MenuItemOptionToppingArr = $customeToppings = array();
                    if (isset($topingArr[$iMenuItemId])) {
                        $MenuItemOptionToppingArr = $topingArr[$iMenuItemId];
                    }
                    $db_item_data[$j]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
                    array_push($MenuItemsDataArr, $db_item_data[$j]);
                }
            }
        }
    }

    /*   $sqlf = "SELECT mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_".$vLanguage." as vItemType,mi.vItemDesc_".$vLanguage." as vItemDesc,mi.fPrice,mi.eFoodType,mi.fOfferAmt,mi.vImage,mi.iDisplayOrder FROM menu_items as mi LEFT JOIN food_menu as f on f.iFoodMenuId=mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId WHERE mi.eStatus='Active' AND mi.eAvailable = 'Yes' AND f.iCompanyId = '".$restaId."'  $ssql ORDER BY RAND()";
      $db_item_data = $obj->MySQLSelect($sqlf);
      for($j=0;$j<count($db_item_data);$j++){
      $fPrice= round($db_item_data[$j]['fPrice']*$Ratio,2);
      $db_item_data[$j]['fPrice'] = formatNum($fPrice);
      if($db_item_data[$j]['vImage'] != ""){
      $db_item_data[$j]['vImage'] = $tconfig["tsite_upload_images_menu_item"]."/".$db_item_data[$j]['vImage'];
      }
      } */

    // $returnArr['Recomendation_Arr'] = $MenuItemsDataArr;
    // echo "<pre>";print_r($returnArr);exit;
    return $MenuItemsDataArr;
}

################# Display Recommended and Best Seller Menu Items#############################
########################## Check Cancel Order Status ########################################

function checkCancelOrderStatus($iOrderId) {
    global $generalobj, $obj;
    $sql = "SELECT iStatusCode from orders WHERE iOrderId ='" . $iOrderId . "'";
    $db_status = $obj->MySQLSelect($sql);
    $iStatusCode = $db_status[0]['iStatusCode'];
    if ($iStatusCode == 8) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    return $iOrderId;
}

########################## Check Cancel Order Status ########################################

function getServiceCategoryCounts() {
    global $generalobj, $obj;
    $sqlN = "SELECT count(iServiceId) as TotalSerivce FROM service_categories WHERE eStatus='Active'";
    $datar = $obj->MySQLSelect($sqlN);
    $serviceCatCount = $datar[0]['TotalSerivce'];
    return $serviceCatCount;
}

############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  ###################################################################

function CheckUserSmsLimit($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT, $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS, $iServiceId;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCount,dSendverificationDate,vLang';
        $condfield = 'iUserId';
    } else if ($UserType == "Company") {
        $tblname = "company";
        $fields = 'vVerificationCount,dSendverificationDate,vLang';
        $condfield = 'iCompanyId';
    } else {
        $tblname = "register_driver";
        $fields = 'vVerificationCount,dSendverificationDate,vLang';
        $condfield = 'iDriverId';
    }

    $sql = "select $fields from $tblname where $condfield='" . $iMemberId . "'";
    $db_user = $obj->MySQLSelect($sql);
    $vVerificationCount = $db_user[0]['vVerificationCount'];
    $dSendverificationDate = $db_user[0]['dSendverificationDate'];
    if (($dSendverificationDate < $currentdate) && $dSendverificationDate != "0000-00-00 00:00:00") {
        $updateQuery = "UPDATE $tblname set dSendverificationDate='0000-00-00 00:00:00',vVerificationCount = 0 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
        $vVerificationCount = 0;
        $dSendverificationDate = "0000-00-00 00:00:00";
    }
    $vLang = $db_user[0]['vLang'];
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $currentdate = @date("Y-m-d H:i:s");
    $totalMinute = @round(abs(strtotime($dSendverificationDate) - strtotime($currentdate)) / 60);
    $totalSeconds = @round(abs(strtotime($dSendverificationDate) - strtotime($currentdate)));
    $hours = floor($totalSeconds / 3600);
    $mins =  floor(($totalSeconds / 60) % 60);
    $seconds = $totalSeconds % 60;
    
  /*  $hours = floor($totalMinute / 60); // No. of mins/60 to get the hours and round down
    $mins = $totalMinute % 60; // No. of mins/60 - remainder (modulus) is the minutes*/
    $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];

    // $LBL_MINUTES_TXT = ($mins > 1)? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
    $LBL_MINUTES_TXT = ($mins > 1) ? $languageLabelsArr['LBL_MINS_SMALL'] : $languageLabelsArr['LBL_MINUTE'];

    $LBL_SECONDS_TXT = $languageLabelsArr['LBL_SECONDS_TXT'];
    /*if ($hours >= 1) {
        $timeDurationDisplay = $hours . " " . $LBL_HOURS_TXT . " " . $mins . " " . $LBL_MINUTES_TXT;
    } else {
        $timeDurationDisplay = $mins . " " . $LBL_MINUTES_TXT;
    }*/

    if ($hours >= 1) {
        $timeDurationDisplay = $hours . " " . $LBL_HOURS_TXT . " " . $mins . " " . $LBL_MINUTES_TXT. " " . $seconds . " " . $LBL_SECONDS_TXT;
    } else if ($mins > 0) {
        $timeDurationDisplay = $mins . " " . $LBL_MINUTES_TXT . " " . $seconds . " " . $LBL_SECONDS_TXT;
    } else {
        $timeDurationDisplay = $seconds . " " . $LBL_SECONDS_TXT;
    }

    $message = $languageLabelsArr['LBL_SMS_MAXIMAM_LIMIT_TXT'] . " " . $timeDurationDisplay;
    if (($dSendverificationDate < $currentdate) && $dSendverificationDate != "0000-00-00 00:00:00") {
        $updateQuery = "UPDATE $tblname set dSendverificationDate='0000-00-00 00:00:00',vVerificationCount = 0 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
        $vVerificationCount = 0;
        $dSendverificationDate = "0000-00-00 00:00:00";
    }

    if ($vVerificationCount == $VERIFICATION_CODE_RESEND_COUNT) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $message;
        setDataResponse($returnArr);
    }

    return $iMemberId;
}

############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  ###################################################################
############################# Update  User's  SMS Resending Limit and Rest Verification count and date  ###################################################################

function UpdateUserSmsLimit($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT, $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCount,dSendverificationDate';
        $condfield = 'iUserId';
    } else if ($UserType == "Company") {
        $tblname = "company";
        $fields = 'vVerificationCount,dSendverificationDate';
        $condfield = 'iCompanyId';
    } else {
        $tblname = "register_driver";
        $fields = 'vVerificationCount,dSendverificationDate';
        $condfield = 'iDriverId';
    }

    $sql = "select $fields from $tblname where $condfield='" . $iMemberId . "'";
    $db_user = $obj->MySQLSelect($sql);
    $vVerificationCount = $db_user[0]['vVerificationCount'];
    $dSendverificationDate = $db_user[0]['dSendverificationDate'];
    $currentdate = @date("Y-m-d H:i:s");
    $checklastcount = $VERIFICATION_CODE_RESEND_COUNT - 1;
    if ($vVerificationCount == $checklastcount) {
        $minutes = $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION;
        $expire_stamp = date('Y-m-d H:i:s', strtotime("+" . $minutes . " minute"));
        $updateQuery = "UPDATE $tblname set dSendverificationDate='" . $expire_stamp . "',vVerificationCount = vVerificationCount+1 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
    } else {
        $vVerificationCount = $vVerificationCount + 1;
        if ($vVerificationCount > $VERIFICATION_CODE_RESEND_COUNT) {
            $vVerificationCount = $VERIFICATION_CODE_RESEND_COUNT;
        }

        $updateQuery = "UPDATE $tblname set vVerificationCount = '" . $vVerificationCount . "' WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
    }

    return $iMemberId;
}

############################# Update  User's  SMS Resending Limit and Rest Verification count and date  ###################################################################
############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  For Emergency Contact ###################################################################

function CheckUserSmsLimitForEmergency($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY, $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY, $iServiceId;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCountEmergency,dSendverificationDateEmergency,vLang';
        $condfield = 'iUserId';
    } else {
        $tblname = "register_driver";
        $fields = 'vVerificationCountEmergency,dSendverificationDateEmergency,vLang';
        $condfield = 'iDriverId';
    }
    $sql = "SELECT * FROM user_emergency_contact WHERE iUserId = '" . $iMemberId . "' AND eUserType='" . $UserType . "'";
    $dataArr = $obj->MySQLSelect($sql);
    if (count($dataArr) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ADD_EME_CONTACTS";
        $returnArr['message1'] = "ContactError";
        setDataResponse($returnArr);
    }

    $sql = "select $fields from $tblname where $condfield='" . $iMemberId . "'";
    $db_user = $obj->MySQLSelect($sql);
    $vVerificationCount = $db_user[0]['vVerificationCountEmergency'];
    $dSendverificationDate = $db_user[0]['dSendverificationDateEmergency'];
    $vLang = $db_user[0]['vLang'];
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $currentdate = @date("Y-m-d H:i:s");
    $totalMinute = @round(abs(strtotime($dSendverificationDate) - strtotime($currentdate)) / 60);
    $totalSeconds = abs(strtotime($dSendverificationDate) - strtotime($currentdate));
    $hours = floor($totalMinute / 60); // No. of mins/60 to get the hours and round down
    $mins = $totalMinute % 60; // No. of mins/60 - remainder (modulus) is the minutes
    $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];

    // $LBL_MINUTES_TXT = ($mins > 1)? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
    $LBL_MINUTES_TXT = ($mins > 1) ? $languageLabelsArr['LBL_MINS_SMALL'] : $languageLabelsArr['LBL_MINUTE'];
    if ($hours >= 1) {
        $timeDurationDisplay = $hours . " " . $LBL_HOURS_TXT . " " . $mins . " " . $LBL_MINUTES_TXT;
    } else {
        if ($mins > 1) {
            $timeDurationDisplay = $mins . " " . $LBL_MINUTES_TXT;
        } else {
            $timeDurationDisplay = $totalSeconds . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
        }
    }

    $message = $languageLabelsArr['LBL_SMS_MAXIMAM_LIMIT_TXT'] . " " . $timeDurationDisplay;
    if (($dSendverificationDate < $currentdate) && $dSendverificationDate != "0000-00-00 00:00:00") {
        $updateQuery = "UPDATE $tblname set dSendverificationDateEmergency='0000-00-00 00:00:00',vVerificationCountEmergency = 0 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
        $vVerificationCount = 0;
        $dSendverificationDate = "0000-00-00 00:00:00";
    }

    $totalSeconds1 = abs(strtotime($dSendverificationDate) - strtotime($currentdate));
    if ($totalSeconds1 < $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $message;
        $returnArr['message1'] = "SmsError";
        setDataResponse($returnArr);
    }

    if ($vVerificationCount == $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $message;
        $returnArr['message1'] = "SmsError";
        setDataResponse($returnArr);
    }

    return $iMemberId;
}

############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  For Emergency Contact###################################################################
########################### Charge Customer App Payment Method Wise ##############################################################

function ChargeCustomer($Data, $eChargeEvent = "CollectPayment") {
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    global $generalobj, $obj, $STRIPE_SECRET_KEY, $STRIPE_PUBLISH_KEY, $gateway, $BRAINTREE_TOKEN_KEY, $BRAINTREE_ENVIRONMENT, $BRAINTREE_MERCHANT_ID, $BRAINTREE_PUBLIC_KEY, $BRAINTREE_PRIVATE_KEY, $BRAINTREE_CHARGE_AMOUNT, $PAYMAYA_API_URL, $PAYMAYA_SECRET_KEY, $PAYMAYA_PUBLISH_KEY, $PAYMAYA_ENVIRONMENT_MODE, $OMISE_SECRET_KEY, $OMISE_PUBLIC_KEY, $ADYEN_MERCHANT_ACCOUNT, $ADYEN_USER_NAME, $ADYEN_PASSWORD, $ADYEN_API_URL, $XENDIT_PUBLIC_KEY, $XENDIT_SECRET_KEY, $APP_PAYMENT_METHOD, $SYSTEM_PAYMENT_ENVIRONMENT, $DEFAULT_CURRENCY_CONVERATION_CODE_RATIO, $DEFAULT_CURRENCY_CONVERATION_ENABLE, $DEFAULT_CURRENCY_CONVERATION_CODE, $FLUTTERWAVE_PUBLIC_KEY, $FLUTTERWAVE_SECRET_KEY; // Stripe,Braintree
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    $iOrderId = $Data['iOrderId'];
    $vOrderNo = $Data['vOrderNo'];
    $iFare = $Data['iFare'];
    $price_new = $Data['price_new'];
    $currency = $Data['currency'];
    if (strtoupper($DEFAULT_CURRENCY_CONVERATION_ENABLE) == 'YES' && !empty($DEFAULT_CURRENCY_CONVERATION_CODE_RATIO) && !empty($DEFAULT_CURRENCY_CONVERATION_CODE) && $DEFAULT_CURRENCY_CONVERATION_CODE_RATIO > 0) {
        $DefaultConverationRatio = $DEFAULT_CURRENCY_CONVERATION_CODE_RATIO;
        $price_new = $price_new / 100;
        $price_new = (round(($price_new * $DefaultConverationRatio), 2) * 100);
        $currency = $DEFAULT_CURRENCY_CONVERATION_CODE;
    }
    $vStripeCusId = $Data['vStripeCusId'];
    $description = $Data['description'];
    $iTripId = $Data['iTripId'];
    $eCancelChargeFailed = $Data['eCancelChargeFailed'];
    $vBrainTreeToken = $Data['vBrainTreeToken'];
    $vRideNo = $Data['vRideNo'];
    $iMemberId = $Data['iMemberId'];
    $UserType = $Data['UserType'];
    $vBrainTreeChargePrice = $price_new / 100;
    $vPaymayaChargePrice = $price_new / 100;
    $vXenditChargePrice = $price_new / 100;
    //$vAdyenChargePrice = $price_new/100;
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $vFlutterWaveChargePrice = $iFare;
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    $vAdyenChargePrice = $price_new;
    if ($UserType == "Passenger") {
        $tbl_name = "register_user";
        $iUserId = "iUserId";
        /* Updated By PM On 09-12-2019 For Flutterwave Code Start */
        $UserDetailPaymaya = get_value($tbl_name, 'vPaymayaCustId,vPaymayaToken,vAdyenToken,vName,vLastName,vEmail,vXenditAuthId,vXenditToken,vFlutterWaveToken,vCurrencyPassenger as vCurrency,vLang', $iUserId, $iMemberId);
        /* Updated By PM On 09-12-2019 For Flutterwave Code end */
    } else {
        $tbl_name = "register_driver";
        $iUserId = "iDriverId";
        /* Updated By PM On 09-12-2019 For Flutterwave Code Start */
        $UserDetailPaymaya = get_value($tbl_name, 'vPaymayaCustId,vPaymayaToken,vAdyenToken,vName,vLastName,vEmail,vXenditAuthId,vXenditToken,vFlutterWaveToken,vCurrencyDriver as vCurrency,vLang', $iUserId, $iMemberId);
        /* Updated By PM On 09-12-2019 For Flutterwave Code end */
    }


    if ($APP_PAYMENT_METHOD == "Stripe") {
        require_once ('assets/libraries/stripe/config.php');
        require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');

        try {

            if ($iFare < 0.51) {
                $currencycode = $UserDetailPaymaya[0]['vCurrency'];
                $currencyData = get_value('currency', 'Ratio,vSymbol', 'vName', $currencycode);
                if ($currencycode == "" || $currencycode == NULL) {
                    $sql = "SELECT vName,vSymbol,Ratio from currency WHERE eDefault = 'Yes'";
                    $currencyData = $obj->MySQLSelect($sql);
                    $currencycode = $currencyData[0]['vName'];
                }
                $currencySymbol = $currencyData[0]['vSymbol'];
                $currencyratio = $currencyData[0]['Ratio'];
                $vLangCode = $UserDetailPaymaya[0]['vLang'];
                if ($vLangCode == "" || $vLangCode == NULL) {
                    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                }
                $userLanguageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
                $returnArr["Action"] = "0";
                $minValue = $currencySymbol . " " . strval(round(0.51 * $currencyratio, 2));
                $returnArr['message'] = $userLanguageLabelsArr["LBL_REQUIRED_MINIMUM_AMOUT"] . " " . $minValue;
                echo json_encode($returnArr);
                exit;
            }

            if ($iFare > 0) {
                $charge_create = Stripe_Charge::create(array(
                            "amount" => $price_new,
                            "currency" => $currency,
                            "customer" => $vStripeCusId,
                            "description" => $description
                ));

                $details = json_decode($charge_create);
                $result = get_object_vars($details);
            }

            if ($iFare == 0 || ($result['status'] == "succeeded" && $result['paid'] == "1")) {

                $stripe_arr['STRIPE_SECRET_KEY'] = $STRIPE_SECRET_KEY;
                $stripe_arr['STRIPE_PUBLISH_KEY'] = $STRIPE_PUBLISH_KEY;
                $tPaymentDetails = json_encode($stripe_arr, JSON_UNESCAPED_UNICODE);
                $pay_data['tPaymentUserID'] = $iFare == 0 ? "" : $result['id'];
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iTripId'] = $iTripId;
                $pay_data['iAmountUser'] = $iFare;
                $pay_data['tPaymentDetails'] = $tPaymentDetails;
                $pay_data['iOrderId'] = $iOrderId;
                $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                $pay_data['iUserId'] = $iMemberId;
                $pay_data['eUserType'] = $UserType;

                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                $returnArr['status'] = "success";
            } else {
                $returnArr['status'] = "fail";
                if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                    $eCancelChargeFailed = "Yes";
                } else {

                    $where = " iOrderId = '$iOrderId'";
                    $data['iStatusCode'] = 11;
                    $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                    $OrderLogId = createOrderLog($iOrderId, "11");
                    $error3 = $e->getMessage();
                    $returnArr["Action"] = "0";
                    $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $returnArr['status'] = "fail";
            $error3 = $e->getMessage();
            if ($eChargeEvent == "cancelTrip") {
                $eCancelChargeFailed = 'Yes';
            } else {
                $where = " iOrderId = '$iOrderId'";
                $data['iStatusCode'] = 11;
                $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                $OrderLogId = createOrderLog($iOrderId, "11");
                $error3 = $e->getMessage();
                $returnArr["Action"] = "0";
                $returnArr['message'] = $error3;
                setDataResponse($returnArr);
            }
        }
    } else if ($APP_PAYMENT_METHOD == "Braintree") {
        require_once ('assets/libraries/braintree/lib/Braintree.php');
        try {
            if ($iFare > 0) {
                $charge_create = $gateway->transaction()
                        ->sale(['paymentMethodToken' => $vBrainTreeToken, 'amount' => $vBrainTreeChargePrice]);

                $status = $charge_create->success;
                $transactionid = $charge_create
                        ->transaction->id;
            }

            if ($iFare == 0 || $status == "1") {

                $braintree_arr['BRAINTREE_TOKEN_KEY'] = $BRAINTREE_TOKEN_KEY;
                $braintree_arr['BRAINTREE_ENVIRONMENT'] = $BRAINTREE_ENVIRONMENT;
                $braintree_arr['BRAINTREE_MERCHANT_ID'] = $BRAINTREE_MERCHANT_ID;
                $braintree_arr['BRAINTREE_PUBLIC_KEY'] = $BRAINTREE_PUBLIC_KEY;
                $braintree_arr['BRAINTREE_PRIVATE_KEY'] = $BRAINTREE_PRIVATE_KEY;
                $tPaymentDetails = json_encode($braintree_arr, JSON_UNESCAPED_UNICODE);
                $pay_data['tPaymentUserID'] = $iFare == 0 ? "" : $transactionid;
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iTripId'] = $iTripId;
                $pay_data['iAmountUser'] = $iFare;
                $pay_data['tPaymentDetails'] = $tPaymentDetails;
                $pay_data['iOrderId'] = $iOrderId;
                $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                $pay_data['iUserId'] = $iMemberId;
                $pay_data['eUserType'] = $UserType;

                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                $returnArr['status'] = "success";
            } else {
                $returnArr['status'] = "fail";
                if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                    $eCancelChargeFailed = "Yes";
                } else {
                    $where = " iOrderId = '$iOrderId'";
                    $data['iStatusCode'] = 11;
                    $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                    $OrderLogId = createOrderLog($iOrderId, "11");
                    $error3 = $e->getMessage();
                    $returnArr["Action"] = "0";
                    $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $returnArr['status'] = "fail";
            $error3 = $e->getMessage();
            if ($eChargeEvent == "cancelTrip") {
                $eCancelChargeFailed = 'Yes';
            } else {
                $where = " iOrderId = '$iOrderId'";
                $data['iStatusCode'] = 11;
                $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                $OrderLogId = createOrderLog($iOrderId, "11");
                $error3 = $e->getMessage();
                $returnArr["Action"] = "0";
                $returnArr['message'] = $error3;
                setDataResponse($returnArr);
            }
        }
    } else if ($APP_PAYMENT_METHOD == "Paymaya") {
        $vPaymayaCustId = $UserDetailPaymaya[0]['vPaymayaCustId'];
        $vPaymayaToken = $UserDetailPaymaya[0]['vPaymayaToken'];
        //$Ratio = get_value('currency', 'Ratio', 'vName', 'PHP', '', 'true');
        //$vPaymayaChargePrice = $vPaymayaChargePrice * $Ratio;
        //$vPaymayaChargePrice = round($vPaymayaChargePrice, 2);
        $postdata_charge = array(
            'totalAmount' => array(
                'amount' => $vPaymayaChargePrice,
                'currency' => $currency
            ),
            'requestReferenceNumber' => 'REF' . $vRideNo
        );
        $url = $PAYMAYA_API_URL . "/payments/v1/customers/" . $vPaymayaCustId . "/cards/" . $vPaymayaToken . "/payments";
        $result_charge = check_paymaya_api($url, $postdata_charge);
        $PaymentId = $result_charge['id'];
        $paymentstatus = $result_charge['status']; //PAYMENT_SUCCESS
        if ($vPaymayaChargePrice == 0 || $paymentstatus == 'PAYMENT_SUCCESS') {
            $paymaya_arr['PAYMAYA_API_URL'] = $PAYMAYA_API_URL;
            $paymaya_arr['PAYMAYA_SECRET_KEY'] = $PAYMAYA_SECRET_KEY;
            $paymaya_arr['PAYMAYA_PUBLISH_KEY'] = $PAYMAYA_PUBLISH_KEY;
            $paymaya_arr['PAYMAYA_ENVIRONMENT_MODE'] = $PAYMAYA_ENVIRONMENT_MODE;
            $tPaymentDetails = json_encode($paymaya_arr, JSON_UNESCAPED_UNICODE);
            $pay_data['tPaymentUserID'] = $PaymentId;
            $pay_data['vPaymentUserStatus'] = "approved";
            $pay_data['iTripId'] = $iTripId;
            $pay_data['iAmountUser'] = $iFare;
            $pay_data['tPaymentDetails'] = $tPaymentDetails;
            $pay_data['iOrderId'] = $iOrderId;
            $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
            $pay_data['iUserId'] = $iMemberId;
            $pay_data['eUserType'] = $UserType;
            $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
            $returnArr['status'] = "success";
        } else {
            $returnArr['status'] = "fail";
            if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                $eCancelChargeFailed = "Yes";
            } else {
                $where = " iOrderId = '$iOrderId'";
                $data['iStatusCode'] = 11;
                $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                $OrderLogId = createOrderLog($iOrderId, "11");
                $error3 = $e->getMessage();
                $returnArr["Action"] = "0";
                $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                setDataResponse($returnArr);
            }
        }
    } else if ($APP_PAYMENT_METHOD == "Omise") {
        require_once ('assets/libraries/omise/config.php');
        $UserDetailOmise = get_value($tbl_name, 'vOmiseCustId,vOmiseToken', $iUserId, $iMemberId);
        $vOmiseCustId = $UserDetailOmise[0]['vOmiseCustId'];
        $vOmiseToken = $UserDetailOmise[0]['vOmiseToken'];

        try {
            if ($iFare > 0) {
                $charge = OmiseCharge::create(array(
                            'amount' => $price_new,
                            'currency' => $currency,
                            'customer' => $vOmiseCustId,
                            'card' => $vOmiseToken
                ));
            }

            if ($iFare == 0 || ($charge['status'] == "successful" && $charge['paid'] == "1")) {

                $omise_arr['OMISE_SECRET_KEY'] = $OMISE_SECRET_KEY;
                $omise_arr['OMISE_PUBLIC_KEY'] = $OMISE_PUBLIC_KEY;
                $tPaymentDetails = json_encode($omise_arr, JSON_UNESCAPED_UNICODE);
                $pay_data['tPaymentUserID'] = $iFare == 0 ? "" : $charge['transaction'];
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iTripId'] = $iTripId;
                $pay_data['iAmountUser'] = $iFare;
                $pay_data['tPaymentDetails'] = $tPaymentDetails;
                $pay_data['iOrderId'] = $iOrderId;
                $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                $pay_data['iUserId'] = $iMemberId;
                $pay_data['eUserType'] = $UserType;

                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                $returnArr['status'] = "success";
            } else {
                $returnArr['status'] = "fail";
                if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                    $eCancelChargeFailed = "Yes";
                } else {
                    $where = " iOrderId = '$iOrderId'";
                    $data['iStatusCode'] = 11;
                    $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                    $OrderLogId = createOrderLog($iOrderId, "11");
                    $error3 = $e->getMessage();
                    $returnArr["Action"] = "0";
                    $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $returnArr['status'] = "fail";
            $error3 = $e->getMessage();
            if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                $eCancelChargeFailed = 'Yes';
            } else {
                $where = " iOrderId = '$iOrderId'";
                $data['iStatusCode'] = 11;
                $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                $OrderLogId = createOrderLog($iOrderId, "11");
                $error3 = $e->getMessage();
                $returnArr["Action"] = "0";
                $returnArr['message'] = $error3;
                setDataResponse($returnArr);
            }
        }
    } else if ($APP_PAYMENT_METHOD == "Adyen") {
        $vAdyenToken = $UserDetailPaymaya[0]['vAdyenToken'];
        $shopperReference = $UserDetailPaymaya[0]['vName'] . " " . $UserDetailPaymaya[0]['vLastName'];
        $shopperEmail = $UserDetailPaymaya[0]['vEmail'];
        $reference = rand(111111, 999999);
        $USERPWD = $ADYEN_USER_NAME . ":" . $ADYEN_PASSWORD;
        $result = array();
        // Pass the customer's authorisation code, email and amount
        $postdata = array(
            "selectedRecurringDetailReference" => $vAdyenToken,
            "recurring" => array(
                "contract" => "RECURRING"
            ),
            "merchantAccount" => $ADYEN_MERCHANT_ACCOUNT,
            "amount" => array(
                "value" => $vAdyenChargePrice,
                "currency" => $currency
            ),
            "reference" => $reference,
            "shopperEmail" => $shopperEmail,
            "shopperReference" => $shopperReference,
            "shopperInteraction" => "ContAuth"
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ADYEN_API_URL);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $USERPWD);
        curl_setopt($ch, CURLOPT_POST, count(json_encode($postdata)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-type: application/json"
        ));

        $request = curl_exec($ch); //echo "<pre>";print_r($request);exit;
        curl_close($ch);
        if ($request) {
            $result = json_decode($request, true);
            $resultCode = $result['resultCode']; //Authorised
            $authCode = $result['authCode'];

            if ($resultCode == "Authorised") {
                $Adyen_arr['ADYEN_MERCHANT_ACCOUNT'] = $ADYEN_MERCHANT_ACCOUNT;
                $Adyen_arr['ADYEN_USER_NAME'] = $ADYEN_USER_NAME;
                $Adyen_arr['ADYEN_PASSWORD'] = $ADYEN_PASSWORD;
                $Adyen_arr['ADYEN_API_URL'] = $ADYEN_API_URL;
                $tPaymentDetails = json_encode($Adyen_arr, JSON_UNESCAPED_UNICODE);
                $pay_data['tPaymentUserID'] = $authCode;
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iTripId'] = $iTripId;
                $pay_data['iAmountUser'] = $iFare;
                $pay_data['tPaymentDetails'] = $tPaymentDetails;
                $pay_data['iOrderId'] = $iOrderId;
                $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                $pay_data['iUserId'] = $iMemberId;
                $pay_data['eUserType'] = $UserType;
                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                $returnArr['status'] = "success";
            } else {
                $returnArr['status'] = "fail";
                if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                    $eCancelChargeFailed = "Yes";
                } else {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                    setDataResponse($returnArr);
                }
            }
        } else {
            $returnArr['status'] = "fail";
            if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                $eCancelChargeFailed = "Yes";
            } else {
                $where = " iOrderId = '$iOrderId'";
                $data['iStatusCode'] = 11;
                $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                $OrderLogId = createOrderLog($iOrderId, "11");
                //$error3 = $e->getMessage();
                $returnArr["Action"] = "0";
                $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                setDataResponse($returnArr);
            }
        }
    } else if ($APP_PAYMENT_METHOD == "Xendit") {
        require_once ('assets/libraries/xendit/config.php');
        require_once ('assets/libraries/xendit/src/XenditPHPClient.php');
        $options['secret_api_key'] = $XENDIT_SECRET_KEY;
        $xenditPHPClient = new XenditClient\XenditPHPClient($options);
        $external_id = substr(number_format(time() * rand(), 0, '', ''), 0, 15);
        if (strtoupper($DEFAULT_CURRENCY_CONVERATION_ENABLE) == 'YES' && !empty($DEFAULT_CURRENCY_CONVERATION_CODE_RATIO) && !empty($DEFAULT_CURRENCY_CONVERATION_CODE) && $DEFAULT_CURRENCY_CONVERATION_CODE_RATIO > 0) {
            $famount = round($vXenditChargePrice);
        } else {
            $IDRCurrencyRatio = get_value('currency', 'Ratio', 'vName', 'IDR', '', 'true');
            $famount = $iFare * $IDRCurrencyRatio;
            $famount = round($famount);
        }
        $vXenditAuthId = $UserDetailPaymaya[0]['vXenditAuthId'];
        $vXenditToken = $UserDetailPaymaya[0]['vXenditToken'];
        $response = $xenditPHPClient->captureCreditCardPayment($external_id, $vXenditToken, $famount);
        //print_r($response);die;
        //$resultCode = $response['status'];
        if (isset($response['status']) && $response['status'] == "CAPTURED") {
            $xendit_arr['XENDIT_SECRET_KEY'] = $XENDIT_SECRET_KEY;
            $xendit_arr['XENDIT_PUBLIC_KEY'] = $XENDIT_PUBLIC_KEY;
            $tPaymentDetails = json_encode($xendit_arr, JSON_UNESCAPED_UNICODE);
            $pay_data['tPaymentUserID'] = $response["id"];
            $pay_data['vPaymentUserStatus'] = "approved";
            $pay_data['iTripId'] = $iTripId;
            $pay_data['iAmountUser'] = $iFare;
            $pay_data['tPaymentDetails'] = $tPaymentDetails;
            $pay_data['iOrderId'] = $iOrderId;
            $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
            $pay_data['iUserId'] = $iMemberId;
            $pay_data['eUserType'] = $UserType;
            $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
            $returnArr['status'] = "success";
        } else {
            $returnArr['status'] = "fail";
            if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                $eCancelChargeFailed = "Yes";
            } else {
                $error3 = "LBL_CHARGE_COLLECT_FAILED";
                if (isset($response['message']) && $response['message'] != "") {
                    $error3 = $response['message'];
                }
                $where = " iOrderId = '$iOrderId'";
                $data['iStatusCode'] = 11;
                $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                $OrderLogId = createOrderLog($iOrderId, "11");
                //$error3 = $e->getMessage();
                $returnArr["Action"] = "0";
                $returnArr['message'] = $error3;
                setDataResponse($returnArr);
            }
        }
    } elseif ($APP_PAYMENT_METHOD == "Flutterwave") {

        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        $txRefId = "JHJ-" . time();
        $vFlutterWaveToken = $UserDetailPaymaya[0]['vFlutterWaveToken'];

        if ($vFlutterWaveToken != "") {
            $email = $UserDetailPaymaya[0]['vEmail'];
            $changedData = flutterwave_charge($txRefId, $vFlutterWaveToken, $currency, $vFlutterWaveChargePrice, $email);

            $paymentstatus = $changedData['status'];
            if ($vFlutterWaveChargePrice == 0 || $paymentstatus == 'success') {
                $payment_arr['FLUTTERWAVE_PUBLIC_KEY'] = $FLUTTERWAVE_PUBLIC_KEY;
                $payment_arr['FLUTTERWAVE_SECRET_KEY'] = $FLUTTERWAVE_SECRET_KEY;
                $tPaymentDetails = json_encode($payment_arr, JSON_UNESCAPED_UNICODE);
                $PaymentId = $changedData['data']['id'];
                $pay_data = array();
                $pay_data['tPaymentUserID'] = $PaymentId;
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iTripId'] = $iTripId;
                ;
                $pay_data['iOrderId'] = $iOrderId;
                $pay_data['iAmountUser'] = $iFare;
                $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                $pay_data['tPaymentDetails'] = $tPaymentDetails;
                $pay_data['iUserId'] = $iMemberId;
                $pay_data['eUserType'] = $UserType;
                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                $returnArr['status'] = "success";
            } else {
                $returnArr['status'] = "fail";
                if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                    $eCancelChargeFailed = "Yes";
                } else {
                    $error3 = "LBL_CHARGE_COLLECT_FAILED";
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                    $where = " iOrderId = '$iOrderId'";
                    $data['iStatusCode'] = 11;
                    $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                    $OrderLogId = createOrderLog($iOrderId, "11");

                    $returnArr["Action"] = "0";
                    $returnArr['message'] = $error3;
                    setDataResponse($returnArr);
                }
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
            setDataResponse($returnArr);
        }
        /* Added By PM On 09-12-2019 For Flutterwave Code end */
    }
    $returnArr['id'] = $id;
    $returnArr['eCancelChargeFailed'] = $eCancelChargeFailed;

    return $returnArr;
}

########################### Charge Customer App Payment Method Wise ##############################################################
//Added By HJ On 25-01-2019 For Get Custome Topping Data Start

function getMenuCustomeToppings($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $eFor) {
    global $obj, $generalobj, $tconfig;
    //ini_set("display_errors", 1);
    //echo "<pre>";
    //error_reporting(E_ALL);
    $returnArr = array();
    $ssql = "";
    //echo $eFor;die
    if ($eFor == 1) {
        $ssql = "";
        //$ssql .= " AND mo.eStatus = 'Active' ";
        // $languageLabelsArr = getLanguageLabelsArr($vLanguage,"1");
        $sql = "SELECT mo.*,fm.iFoodMenuId,co.iCompanyId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '" . $iMenuItemId . "' AND mi.eStatus = 'Active' AND `eOptionType` NOT IN ('Options',  'Addon') AND mo.eStatus='Active' AND mi.eAvailable = 'Yes'" . $ssql;
    } else {
        $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId WHERE mo.iMenuItemId = '" . $iMenuItemId . "' AND `eOptionType` NOT IN ('Options') AND mo.eStatus = 'Active'";
    }
    //echo $sql;die;
    $db_options_data = $obj->MySQLSelect($sql);
    for ($i = 0; $i < count($db_options_data); $i++) {
        $eOptionType = $db_options_data[$i]['eOptionType'];
        $eOptionInputType = $db_options_data[$i]['eOptionInputType'];
        $vOptionMinSelection = $db_options_data[$i]['vOptionMinSelection'];
        $vOptionMaxSelection = $db_options_data[$i]['vOptionMaxSelection'];
        $fPrice = $db_options_data[$i]['fPrice'];
        $fUserPrice = number_format($fPrice * $Ratio, 2);
        $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
        $db_options_data[$i]['fUserPrice'] = $fUserPrice;
        $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
        $returnArr[$eOptionType]['eOptionType'] = $eOptionType;
        $returnArr[$eOptionType]['iMenuItemId'] = $db_options_data[$i]['iMenuItemId'];
        $returnArr[$eOptionType]['iFoodMenuId'] = $db_options_data[$i]['iFoodMenuId'];
        $returnArr[$eOptionType]['eOptionInputType'] = $eOptionInputType;
        $returnArr[$eOptionType]['vOptionMinSelection'] = $vOptionMinSelection;
        $returnArr[$eOptionType]['vOptionMaxSelection'] = $vOptionMaxSelection;
        $returnArr[$eOptionType]['subItemArr'][] = $db_options_data[$i];
    }
    $finalReturnArr = array();
    foreach ($returnArr as $key => $val) {
        $finalReturnArr[] = $val;
    }
    //echo "<pre>";print_r($finalReturnArr);die;
    return $finalReturnArr;
}

//Added By HJ On 25-01-2019 For Get Custome Topping Data End
//Added By HJ On 25-01-2019 For Get Custome Topping Data End
function getMenuCustomeAllToppings($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $eFor) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $ssql = "";
    //echo $eFor;die
    if ($eFor == 1) {
        $ssql = "";
        //$ssql .= " AND mo.eStatus = 'Active' ";
        // $languageLabelsArr = getLanguageLabelsArr($vLanguage,"1");
        $sql = "SELECT mo.*,fm.iFoodMenuId,co.iCompanyId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId IN ($iMenuItemId) AND mi.eStatus = 'Active' AND `eOptionType` NOT IN ('Options',  'Addon') AND mo.eStatus='Active' AND mi.eAvailable = 'Yes'" . $ssql;
    } else {
        $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId WHERE mo.iMenuItemId IN ($iMenuItemId) AND `eOptionType` NOT IN ('Options',  'Addon') AND mo.eStatus = 'Active'";
    }
    //echo $sql;die;
    $db_options_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($db_options_data);die;
    $menuItemArr = array();
    for ($i = 0; $i < count($db_options_data); $i++) {
        $iMenuItemId = $db_options_data[$i]['iMenuItemId'];
        $eOptionType = $db_options_data[$i]['eOptionType'];
        $eOptionInputType = $db_options_data[$i]['eOptionInputType'];
        $vOptionMinSelection = $db_options_data[$i]['vOptionMinSelection'];
        $vOptionMaxSelection = $db_options_data[$i]['vOptionMaxSelection'];
        $fPrice = $db_options_data[$i]['fPrice'];
        $fUserPrice = number_format($fPrice * $Ratio, 2);
        $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
        $db_options_data[$i]['fUserPrice'] = $fUserPrice;
        $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
        //$returnArr = array();
        $returnArr[$iMenuItemId][$eOptionType]['eOptionType'] = $eOptionType;
        $returnArr[$iMenuItemId][$eOptionType]['iMenuItemId'] = $db_options_data[$i]['iMenuItemId'];
        $returnArr[$iMenuItemId][$eOptionType]['iFoodMenuId'] = $db_options_data[$i]['iFoodMenuId'];
        $returnArr[$iMenuItemId][$eOptionType]['eOptionInputType'] = $eOptionInputType;
        $returnArr[$iMenuItemId][$eOptionType]['vOptionMinSelection'] = $vOptionMinSelection;
        $returnArr[$iMenuItemId][$eOptionType]['vOptionMaxSelection'] = $vOptionMaxSelection;
        $returnArr[$iMenuItemId][$eOptionType]['subItemArr'][] = $db_options_data[$i];
        //$menuItemArr[$iMenuItemId][] =  $returnArr;
    }
    //echo "<pre>";print_r($returnArr);die;
    $finalReturnArr = array();
    foreach ($returnArr as $key => $val) {
        foreach ($val as $key1 => $val2) {
            //echo "<pre>";print_r($val2);die;
            $finalReturnArr[$key][] = $val2;
        }
    }
    //echo "<pre>";print_r($finalReturnArr);die;
    return $finalReturnArr;
}

//Added By HJ On 25-01-2019 For Get Custome Topping Data End
//Added By HJ On 09-05-2019 For Get All Option and Addon Price Array Start
function getAllOptionAddonPriceArr() {
    global $obj;
    $optionPriceArr = array();
    $getAllMenuOptionPrice = $obj->MySQLSelect("select iMenuItemId,fPrice,iOptionId from `menuitem_options`");
    for ($r = 0; $r < count($getAllMenuOptionPrice); $r++) {
        if (isset($optionPriceArr[$getAllMenuOptionPrice[$r]['iOptionId']])) {
            $optionPriceArr[$getAllMenuOptionPrice[$r]['iOptionId']] += $getAllMenuOptionPrice[$r]['fPrice'];
        } else {
            $optionPriceArr[$getAllMenuOptionPrice[$r]['iOptionId']] = $getAllMenuOptionPrice[$r]['fPrice'];
        }
    }
    return $optionPriceArr;
}

//Added By HJ On 09-05-2019 For Get All Option and Addon Price Array End
//Added By HJ On 09-05-2019 For Get All Menu Items Price Array Start
function getAllMenuItemPriceArr() {
    global $obj;
    $ordItemPriceArr = array();
    $getAllItemsPrice = $obj->MySQLSelect("select fPrice,iMenuItemId from menu_items");
    for ($ai = 0; $ai < count($getAllItemsPrice); $ai++) {
        $ordItemPriceArr[$getAllItemsPrice[$ai]['iMenuItemId']] = $getAllItemsPrice[$ai]['fPrice'];
    }
    return $ordItemPriceArr;
}

//Added By HJ On 09-05-2019 For Get All Menu Items Price Array End
function getStoreDetails($storeIds, $userId, $iToLocationId, $languageLabelsArr) {
    global $obj, $generalobj, $tconfig;
    //echo "<pre>";
    $vLanguage = "EN";
    $storePrepareTimeArr = array();
    //$languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    $storeData = getcuisinelist($storeIds, $userId, $languageLabelsArr);
    if (isset($storeData['latLangArr'])) {
        $storeLatLangArr = $storeData['latLangArr'];
        $LBL_MINS_SMALL = $languageLabelsArr['LBL_MINS_SMALL'];
        for ($e = 0; $e < count($storeIds); $e++) {
            //echo "<pre>";print_r($storeData);die;
            //restaurantPricePerPerson
            $fDeliverytime = "0";
            if ($iToLocationId != "0") {
                $restaurantlat = $restaurantlong = "";
                if (isset($storeLatLangArr[$storeIds[$e]])) {
                    $restaurantlat = $storeLatLangArr[$storeIds[$e]]['restaurantlat'];
                    $restaurantlong = $storeLatLangArr[$storeIds[$e]]['restaurantlong'];
                }
                $Rest_Address_Array = array($restaurantlat, $restaurantlong);
                $iLocationId = GetUserGeoLocationId($Rest_Address_Array);
                $sql = "SELECT * FROM  `delivery_charges` WHERE ";
                $iToLocationId1 = ltrim($iToLocationId, "0.,");
                $iLocationId = ltrim($iLocationId, "0.,");
                $iToLocationId2 = explode(",", $iToLocationId1);
                $iLocationId = explode(",", $iLocationId);
                $countuser = count($iToLocationId2);
                $countrest = count($iLocationId);
                $counttotal = $countrest * $countuser;
                $cott = $cot = 1;
                if ($countuser >= $countrest) {
                    for ($ui = 0; $ui < $countuser; $ui++) {
                        for ($ri = 0; $ri < $countrest; $ri++) {
                            $sql .= "  ( iToLocationId =  '" . $iToLocationId2[$ui] . "' AND iLocationId =  '" . $iLocationId[$ri] . "' AND eStatus =  'Active') ";
                            if ($cot != $counttotal) {
                                $sql .= " OR ";
                            }
                            $cot++;
                        }
                    }
                } else {
                    for ($rri = 0; $rri < $countrest; $rri++) {
                        for ($uui = 0; $uui < $countuser; $uui++) {
                            $sql .= "  ( iToLocationId =  '" . $iToLocationId2[$uui] . "' AND iLocationId =  '" . $iLocationId[$rri] . "' AND eStatus =  'Active') ";

                            if ($cott != $counttotal) {
                                $sql .= " OR ";
                            }
                            $cott++;
                        }
                    }
                }
                $sql .= " LIMIT 0,1";
                $datacharg = $obj->MySQLSelect($sql);
                if (count($datacharg)) {
                    $fDeliverytime = $datacharg[0]['fDeliverytime'];
                }
            }
            $storeData[$storeIds[$e]]['Restaurant_OrderPrepareTime'] = $fDeliverytime . " " . $LBL_MINS_SMALL;
            $storeData[$storeIds[$e]]['Restaurant_OrderPrepareTimeValue'] = $fDeliverytime;
            $storeData[$storeIds[$e]]['Restaurant_OrderPrepareTimePostfix'] = $LBL_MINS_SMALL;
        }
    }
    return $storeData;
}

function searchForTitle($title, $array) {
    foreach ($array as $key => $val) {
        if ($val['vTitle'] === $title) {
            return $key;
        }
    }
    return null;
}

function getDriverOptions($vLang, $iServiceId) {
    $langage_lbl = getLanguageLabelsArr($vLang, "1", $iServiceId);
    //echo "<pre>";print_r($langage_lbl);die;
    $optionArr = array();
    $optionArr[] = array("label" => "All", "value" => $langage_lbl['LBL_BOTH_DELIEVERY_DRIVERS']);
    $optionArr[] = array("label" => "Personal", "value" => $langage_lbl['LBL_PERSONAL_DELIVERY_DRIVER']);
    $optionArr[] = array("label" => "Site", "value" => $langage_lbl['LBL_SITE_DELIVERY_DRIVER']);
    //echo "<pre>";print_r($optionArr);die;
    return $optionArr;
}

// Added by HV on 12-06-2020 for Store KOT bill print
function getReceiptPdf($kotBillFormat, $html_content, $dir, $vLangCode, $iServiceId)
{
    global $tconfig;
    include_once ($tconfig['tpanel_path'].'assets/libraries/dompdf/autoload.php');
    
    $langage_lbl = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    
    /* Parse data and generate HTML for Receipt */
    $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">
        *{ font-family: DejaVu Sans, sans-serif; margin: 0; }
        span { font-size: 14px;}
        body {width: 236px; max-width: 236px !important;}
        hr {border: none;border-top: 1px dashed #000000;}
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .item { display: table; width: 236px}
        .item span { display: table-cell; line-height: 12px}
        .item:first-child { margin-top: 10px; }
        .pt-5 { padding-top: 5px }
        .pt-10 { padding-top: 10px }
        .pb-5 { padding-bottom: 5px }
        .pb-10 { padding-bottom: 10px }
        .mb-5 { margin-bottom: 5px }
        .mb-10 { margin-bottom: 10px }
        .mt-5 { margin-top: 5px }
        .mt-10 { margin-top: 10px }
        .w-60mm {width: 236px; max-width: 236px !important;}
        .float-left { float: left;}
        .float-right { float: right;}
        .clearfix {clear:both}
        .w-60mm span:nth-child(even) { word-wrap:break-word; width: 120px; text-align: '.(($dir == "ltr") ? "left" : "right").'}
        '.(($dir == "rtl") ? '.item span:nth-child(1) { width: 30px !important; } .item span:nth-child(2) { word-wrap: break-word; width: 170px !important; }' : '').'
    </style></head><body>';

    $align_keywords = array("CENTER", "LEFT", "RIGHT");
    $align = "";
    $start_tag = "";
    $last_tag = "";
    $new_line = 0;
    foreach ($kotBillFormat as $key => $value) 
    {
        $value = trim($value);
        if($value != "NL")
        {
            if(in_array($value, $align_keywords))
            {
                if($align == "")
                {
                    $align = $value;
                    if($align == "CENTER")
                    {
                        $start_tag = '<div class="w-60mm text-center">';    
                    }
                    else{
                        $start_tag = '<div class="w-60mm">';
                    }
                }
                else{
                    if($align != $value)
                    {
                        $align = $value;
                    }
                    if($new_line == 1)
                    {
                        $start_tag .= '</div><div class="clearfix"></div>';
                        $html .= $start_tag;
                        if($align == "CENTER")
                        {
                            $start_tag = '<div class="w-60mm text-center">';    
                        }
                        else{
                            $start_tag = '<div class="w-60mm">';
                        }
                        $new_line = 0;
                    }
                }
            }
            else{
                if($value == "BREAK_LINE")
                {
                    $html .= '<hr class="w-60mm" />';
                    $last_tag = $value;
                }
                else{
                    if($value == "ITEM_NAME")
                    {
                        if($dir == "rtl")
                        {
                            $align = ($align == "LEFT") ? "RIGHT" : "LEFT"; 
                        }
                        $align_opp = ($align == "LEFT") ? "RIGHT" : "LEFT";
                        $item_new_line = "";
                        $new_line_count = 1;
                        foreach ($html_content['ITEM_LIST'] as $sItem) 
                        {
                            $sItemExtra = "";
                            if($sItem['MenuItemToppings'] != "")
                            {
                                $sItemExtra .= $sItem['MenuItemToppings'];
                            }
                            if($sItem['MenuItemOptions'] != "")
                            {
                                $sItemExtra .= ($sItemExtra != "") ? ",".$sItem['MenuItemOptions'] : $sItem['MenuItemOptions'];
                            }
                            
                            $start_tag .= '<div class="item mb-10">';
                            if($dir == "rtl")
                            {
                                if($sItemExtra != "")
                                {
                                    $start_tag .= '<span class="text-'.strtolower($align_opp).'">'.$sItem['iQty'].' X </span><span class="text-'.strtolower($align).'">'.$sItem['MenuItem'].'<br><p>('.$sItemExtra.')</p></span>'.$item_new_line;
                                }
                                else{
                                    $start_tag .= '<span class="text-'.strtolower($align_opp).'">'.$sItem['iQty'].' X </span><span class="text-'.strtolower($align).'">'.$sItem['MenuItem'].'</span>'.$item_new_line;
                                }
                            }
                            else{
                                if($sItemExtra != "")
                                {
                                    $start_tag .= '<span class="text-'.strtolower($align).'">'.$sItem['MenuItem'].'<br><p>('.$sItemExtra.')</p></span><span class="text-'.strtolower($align_opp).'" style="text-align: right;"> X '.$sItem['iQty'].'</span>'.$item_new_line;
                                }
                                else{
                                    $start_tag .= '<span class="text-'.strtolower($align).'">'.$sItem['MenuItem'].'</span><span class="text-'.strtolower($align_opp).'" style="text-align: right"> X '.$sItem['iQty'].'</span>'.$item_new_line;
                                }
                            }
                            
                            $start_tag .= '</div><div class="clearfix"></div>';
                            $new_line_count++;
                        }
                    }
                    elseif ($value == "ITEM_QTY") {
                        // Ignored
                    }
                    else{
                        if($align == "CENTER")
                        {
                            $td_text = isset($html_content[$value]) ? $html_content[$value] : $value;
                            if($start_tag == "")
                            {
                                $start_tag = '<div class="w-60mm text-center">';
                            }
                            $start_tag .= '<span>'.$td_text.'</span>';  
                        }
                        else{
                            $align = ($dir == "ltr") ? "LEFT" : "RIGHT";
                            $align_alt = ($align == "LEFT") ? "LEFT" : "RIGHT";
                            $align_opp_alt = ($align_alt == "RIGHT") ? "LEFT" : "RIGHT";
                            $td_text = isset($html_content[$value]) ? $html_content[$value] : $value;
                            if($dir == "rtl")
                            {
                                $td_text = (stripos($td_text, ' :') !== false) ? ": ".(str_replace(':', '', $td_text)) : $td_text;
                            }
                            if($value == "QUANTITY_TITLE")
                            {
                                $td_text = $langage_lbl['LBL_QUANTITY_TXT'];
                                if($dir == "rtl")
                                {
                                    $start_tag .= '<span class="float-'.strtolower($align_opp_alt).'" style="text-align: left">'.$td_text.'&nbsp;</span>';   
                                }
                                else{
                                    $start_tag .= '<span class="float-'.strtolower($align_opp_alt).'" style="text-align: right">'.$td_text.'&nbsp;</span>';       
                                }
                            }
                            elseif ($value == "ITEM_TITLE") {
                                $td_text = $langage_lbl['LBL_ITEM'];
                                if($start_tag == "")
                                {
                                    $start_tag = '<div class="w-60mm">';
                                }

                                $start_tag .= '<span class="float-'.strtolower($align_alt).'">'.$td_text.'&nbsp;</span>';
                            }
                            else{
                                if($start_tag == "")
                                {
                                    $start_tag = '<div class="w-60mm">';
                                }

                                $start_tag .= '<span class="float-'.strtolower($align_alt).'">'.$td_text.'&nbsp;</span>';
                            }
                        }
                    }
                }
            }
        }
        else{
            if($last_tag != "BREAK_LINE")
            {
                $start_tag .= '</div><div class="clearfix"></div>';
                $html .= $start_tag;
                $start_tag = "";
            }
            else{
                $last_tag = "";
            }
            
            $new_line = 1;
            if($kotBillFormat[$key-1] == "NL")
            {
                $html .= "<br>";    
            }
        }       
    }

    $html .= $start_tag.'</div></body></html>';

    
    // Create PDF
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->set_paper(array(0,0,180,300));

    $GLOBALS['bodyHeight'] = 0;

    $dompdf->setCallbacks(
      array(
        'myCallbacks' => array(
          'event' => 'end_frame', 'f' => function ($infos) {
            $frame = $infos["frame"];
            if (strtolower($frame->get_node()->nodeName) === "body") {
                $padding_box = $frame->get_content_box();
                $GLOBALS['bodyHeight'] += $padding_box['h'];
            }
          }
        )
      )
    );

    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    // echo $html; exit;
    $dompdf->loadHtml($html);
    $dompdf->render();
    unset($dompdf);

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->set_paper(array(0,0,180,$GLOBALS['bodyHeight']+70));
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->render();
    // $output = $dompdf->stream("dompdf_out.pdf", array("Attachment" => false));

    $output = $dompdf->output();
    return base64_encode($output);
}
?>
