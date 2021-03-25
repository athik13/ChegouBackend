<?php

function getTripFare($Fare_data, $surgePrice) {
    global $generalobj, $obj, $ENABLE_WAITING_CHARGE_RENTAL, $ENABLE_WAITING_CHARGE_FLAT_TRIP, $SERVICE_PROVIDER_FLOW,$tripDetailsArr,$vehicleTypeDataArr;
    //$ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");
    if(isset($tripDetailsArr['trips_'.$Fare_data[0]['iTripId']])){
        $tripData = $tripDetailsArr['trips_'.$Fare_data[0]['iTripId']];
    }else{
        $tripData = get_value('trips', '*', 'iTripId', $Fare_data[0]['iTripId'], '', ''); //added by SP for fly stations on 20-08-2019
        $tripDetailsArr['trips_'.$Fare_data[0]['iTripId']] = $tripData;
    }
    //echo "<pre>";print_r($tripData);die;
    $iVehicleTypeId = $tripData[0]['iVehicleTypeId'];
    $eServiceLocation = $tripData[0]['eServiceLocation'];
    $eType = $tripData[0]['eType'];
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    //Added By HJ On 22-06-2020 For Optimize vehicle_type Table Query Start
    if(isset($vehicleTypeDataArr['vehicle_type'])){
        $vehicleTypeData = $vehicleTypeDataArr['vehicle_type'];
        $typeDataArr = array();
        for($h=0;$h<count($vehicleTypeData);$h++){
            $typeDataArr[$vehicleTypeData[$h]['iVehicleTypeId']] = $vehicleTypeData[$h]['iVehicleCategoryId'];
        }
        if(isset($typeDataArr[$iVehicleTypeId])){
            $iVehicleCategoryId =$typeDataArr[$iVehicleTypeId];
        }else{
            $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
        }
    }else {
        $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    }
    //Added By HJ On 22-06-2020 For Optimize vehicle_type Table Query End
    $categoryData = get_value($sql_vehicle_category_table_name, 'iParentId,ePriceType,eMaterialCommision, fCommision', 'iVehicleCategoryId', $iVehicleCategoryId, '', '');
    $iParentId = $categoryData[0]['iParentId'];
    $ePriceType = $categoryData[0]['ePriceType'];
    $eMaterialCommision = $categoryData[0]['eMaterialCommision'];
    $fMaterialCommisionPer = $categoryData[0]['fCommision'];

    $tVehicleTypeFareDataArr = array();
    if ($SERVICE_PROVIDER_FLOW == "Provider" && $eType == "UberX") {
        $orderDetails = $tripData[0]['tVehicleTypeData'];
        $passengerId = $tripData[0]['iUserId'];
        $tVehicleTypeFareDataArr = (array) json_decode($tripData[0]['tVehicleTypeFareData']);

        $ePriceType = $tVehicleTypeFareDataArr['ParentPriceType'];
        $eMaterialCommision = $tVehicleTypeFareDataArr['ParentMaterialCommisionEnable'];
        $fMaterialCommisionPer = $tVehicleTypeFareDataArr['ParentCommision'];
        //$fareDetails = getVehicleTypeFareDetails($orderDetails, $passengerId);
        //$saveFareDetails = $fareDetails['tripFareDetailsSaveArr'];
    } else if ($iParentId > 0) {
        $categoryData = get_value($sql_vehicle_category_table_name, 'ePriceType,eMaterialCommision, fCommision', 'iVehicleCategoryId', $iParentId, '', '');
        $ePriceType = $categoryData[0]['ePriceType'];
        $eMaterialCommision = $categoryData[0]['eMaterialCommision'];
        $fMaterialCommisionPer = $categoryData[0]['fCommision'];
    }
    //,eServiceLocation,tVehicleTypeData
    $iRentalPackageId = $Fare_data[0]['iRentalPackageId'];
    $eHailTrip = $Fare_data[0]['eHailTrip'];
    $eFlatTrip = $Fare_data[0]['eFlatTrip'];
    $fFlatTripPrice = $Fare_data[0]['fFlatTripPrice'];
    $waitingTime = $Fare_data[0]['waitingTime'];
    $iWaitingFeeTimeLimit = $Fare_data[0]['iWaitingFeeTimeLimit'];
    $fWaitingFees = $Fare_data[0]['fWaitingFees'] * $waitingTime;
    $fWaitingFees = round($fWaitingFees, 2);
    if ($waitingTime < $iWaitingFeeTimeLimit) {
        $fWaitingFees = 0;
    }
    if ($eFlatTrip == "Yes") {
        $Fare_data[0]['iBaseFare'] = $fFlatTripPrice;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerKM'] = 0;
    }
    if ($eHailTrip == "Yes" || ($iRentalPackageId > 0 && $ENABLE_WAITING_CHARGE_RENTAL != "Yes") || ($eFlatTrip == "Yes" && $ENABLE_WAITING_CHARGE_FLAT_TRIP != "Yes")) {
        $fWaitingFees = 0;
    }
    //$ePriceType=get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
    $fAmount = 0;
    if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
        $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
        $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $serviceProData = $obj->MySQLSelect($sqlServicePro);
        if (count($serviceProData) > 0) {
            $fAmount = $serviceProData[0]['fAmount'];
        }
    }
    if ($surgePrice >= 1) {
        if (isset($Fare_data[0]['iBaseFare'])) {
            $Fare_data[0]['iBaseFare'] = $Fare_data[0]['iBaseFare'] * $surgePrice;
        }
        if (isset($Fare_data[0]['fPricePerMin'])) {
            $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'] * $surgePrice;
        }
        if (isset($Fare_data[0]['fPricePerKM'])) {
            $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'] * $surgePrice;
        }
        if (isset($Fare_data[0]['iMinFare'])) {
            $Fare_data[0]['iMinFare'] = $Fare_data[0]['iMinFare'] * $surgePrice;
        }
    }

    $ufx_totalCommission = 0;
    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerKM'] = 0;
        if ($SERVICE_PROVIDER_FLOW == "Provider" && count($tVehicleTypeFareDataArr) > 0) {
            $Fare_data[0]['iBaseFare'] = $tVehicleTypeFareDataArr['originalFareTotal'];
            //$Fare_data[0]['iBaseFare'] = 100;
            $ufx_totalCommission = $tVehicleTypeFareDataArr['originalTotalCommissionOfServices'];
        } else {
            if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $fAmount != 0) {
                $Fare_data[0]['iBaseFare'] = $fAmount * $Fare_data[0]['iQty'];
            } else {
                $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'] * $Fare_data[0]['iQty'];
            }
        }
        //added by SP for fly stations on 20-08-2019 start
        if ($Fare_data[0]['eFly'] == 1) {
            include_once($tconfig["tpanel_path"] . "include/features/include_fly_stations.php");
            $Fare_data[0]['iBaseFare'] = getFareForFlyVehicles($iVehicleTypeId, $tripData[0]['iFromStationId'], $tripData[0]['iToStationId']);
        }
        //added by SP for fly stations on 20-08-2019 end
    } else if ($Fare_data[0]['eFareType'] == 'Hourly') {
        $Fare_data[0]['iBaseFare'] = $Fare_data[0]['fPricePerKM'] = 0;
        $Tripminutes = $Fare_data[0]['TripTimeMinutes'];
        $totalHour = $Fare_data[0]['TripTimeMinutes'] / 60;
        $Fare_data[0]['TripTimeMinutes'] = $totalHour;
        $fMinHour = $Fare_data[0]['fMinHour'];
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $fAmount != 0) {
            if ($totalHour > $fMinHour) {
                /* $miniminutes = $fMinHour * 60;
                  $TripTimehours = $Tripminutes / 60;
                  $tothours = intval($TripTimehours);
                  $extrahours = $TripTimehours - $tothours;
                  $extraminutes = $extrahours * 60;
                  $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                  $pricetimeslot = 60 / $fTimeSlot;
                  $pricepertimeslot = $fAmount / $pricetimeslot;
                  $fTimeSlotPrice = $pricepertimeslot;
                  $extratimeslot = ceil($extraminutes / $fTimeSlot);
                  $extraprice = $extratimeslot * $fTimeSlotPrice;
                  $Fare_data[0]['fPricePerMin'] = ($fAmount * $tothours) + $extraprice; */
                $Fare_data[0]['fPricePerMin'] = $fAmount * $totalHour;
            } else {
                $Fare_data[0]['fPricePerMin'] = $fAmount * $fMinHour;
            }
        } else {
            if ($totalHour > $fMinHour) {
                /* $miniminutes = $fMinHour * 60;
                  $TripTimehours = $Tripminutes / 60;
                  $tothours = intval($TripTimehours);
                  $extrahours = $TripTimehours - $tothours;
                  $extraminutes = $extrahours * 60;
                  $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                  $pricetimeslot = 60 / $fTimeSlot;
                  $pricepertimeslot = $Fare_data[0]['fPricePerHour'] / $pricetimeslot;
                  $fTimeSlotPrice = $pricepertimeslot;
                  $extratimeslot = ceil($extraminutes / $fTimeSlot);
                  $extraprice = $extratimeslot * $fTimeSlotPrice;
                  $Fare_data[0]['fPricePerMin'] = ($Fare_data[0]['fPricePerHour'] * $tothours) + $extraprice; */
                $Fare_data[0]['fPricePerMin'] = ($Fare_data[0]['fPricePerHour'] * $totalHour);
            } else {
                $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerHour'] * $fMinHour;
            }
            //$Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerHour'];
        }
    } else if ($Fare_data[0]['eFareType'] == 'Regular') {
        $Fare_data[0]['fPricePerMin'] = round($Fare_data[0]['fPricePerMin'] * $Fare_data[0]['TripTimeMinutes'], 2);
    }

    /* Add For Rental */
    $Minute_Fare = round($Fare_data[0]['fPricePerMin'], 2);
    $Distance_Fare = round($Fare_data[0]['fPricePerKM'] * $Fare_data[0]['TripDistance'], 2);
    $iBaseFare = round($Fare_data[0]['iBaseFare'], 2);
    if (strtoupper(PACKAGE_TYPE) != "STANDARD" && $Fare_data[0]['iRentalPackageId'] > 0) {
        $rentalFareDataArr = generateRentalFare($Fare_data, $Fare_data[0]['iRentalPackageId']);
        $Minute_Fare = $rentalFareDataArr['MINUTE_FARE'];
        $Distance_Fare = $rentalFareDataArr['DISTANCE_FARE'];
        $iBaseFare = $rentalFareDataArr['BASE_FARE'];
    }
    /* End Add For Rental */

    $fMaterialFee = round($Fare_data[0]['fMaterialFee'], 2);
    $fMiscFee = round($Fare_data[0]['fMiscFee'], 2);
    $fDriverDiscount = round($Fare_data[0]['fDriverDiscount'], 2);
    $fVisitFee = round($Fare_data[0]['fVisitFee'], 2);
    //  print_r($Fare_data);

    $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;
    // addon changes point1

    /* if ($eMaterialCommision == 'Yes') {
      $total_fare_for_commission_ufx = $iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee;
      } else {
      $total_fare_for_commission_ufx = $iBaseFare + $Minute_Fare + $Distance_Fare;
      } */

    $total_fare_for_commission_ufx = $iBaseFare + $Minute_Fare + $Distance_Fare;

    if ($SERVICE_PROVIDER_FLOW != "Provider") {
        $total_fare_for_commission_ufx = $total_fare_for_commission_ufx + $fMaterialFee + $fMiscFee;
    }

    $Commision_Fare = round((($total_fare_for_commission_ufx * $Fare_data[0]['fCommision']) / 100), 2);

    $Material_Commision_Fare = 0;
    if ($ufx_totalCommission > 0) {
        $Commision_Fare = $ufx_totalCommission;
        $total_fare_for_commission_ufx = $ufx_totalCommission;
        if ($eMaterialCommision == 'Yes' && $fMaterialCommisionPer > 0) {
            $Commision_Fare = $Commision_Fare + round(((($fMaterialFee + $fMiscFee - $fDriverDiscount) * $fMaterialCommisionPer) / 100), 2);
        }
        $Fare_data[0]['fCommision'] = $Commision_Fare;
    } else if ($eMaterialCommision == 'Yes' && $fMaterialCommisionPer > 0) {
        $Material_Commision_Fare = round(((($fMaterialFee + $fMiscFee - $fDriverDiscount) * $fMaterialCommisionPer) / 100), 2);
        $Commision_Fare = $Commision_Fare + $Material_Commision_Fare;
    }

    $result['FareOfMinutes'] = $Minute_Fare;
    $result['FareOfDistance'] = $Distance_Fare;
    $result['MaterialFareOfCommision'] = $Material_Commision_Fare;
    $result['FareOfCommision'] = $Commision_Fare;
    // $result['iBaseFare'] = $iBaseFare;
    $result['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
    $result['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
    $result['fCommision'] = $Fare_data[0]['fCommision'];
    $result['FinalFare'] = $total_fare;
    $result['FinalFare_UFX_Commission'] = $total_fare_for_commission_ufx;

    //added by SP for fly stations on 20-08-2019 start
    if ($Fare_data[0]['eFly'] == 1) {
        include_once($tconfig["tpanel_path"] . "include/features/include_fly_stations.php");
        $result['iBaseFare'] = getFareForFlyVehicles($iVehicleTypeId, $tripData[0]['iFromStationId'], $tripData[0]['iToStationId']);
    } else {
        $result['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
    }
    //$result['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
    //added by SP for fly stations on 20-08-2019 end

    $result['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
    $result['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
    $result['iMinFare'] = $Fare_data[0]['iMinFare'];
    $result['fMaterialFee'] = $fMaterialFee;
    $result['fMiscFee'] = $fMiscFee;
    $result['fVisitFee'] = $fVisitFee;
    $result['fWaitingFees'] = $fWaitingFees;
    //$result['tVehicleTypeFareData'] = json_encode($saveFareDetails);
    //print_r($result);die;
    return $result;
}

function calculateFare($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $couponCode = "", $tripId, $fMaterialFee = 0, $fMiscFee = 0, $fDriverDiscount = 0, $waitingTime = 0, $totalHoldTimeInMinutes_trip = 0, $personData = array()) {
    global $generalobj, $obj, $ENABLE_WAITING_CHARGE_RENTAL, $ENABLE_WAITING_CHARGE_FLAT_TRIP, $ENABLE_INTRANSIT_SHOPPING_SYSTEM, $ENABLE_AIRPORT_SURCHARGE_SECTION, $HOTEL_BOOKING_SERVICE_CHARGE, $SERVICE_PROVIDER_FLOW, $SYSTEM_PAYMENT_FLOW, $data_trips, $POOL_ENABLE,$vSystemDefaultCurrencyName,$tripDetailsArr;
    $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);
    // $defaultCurrency = ($obj->MySQLSelect("SELECT vName FROM currency WHERE eDefault='Yes'")[0]['vName']);
    //Added By HJ On 22-06-2020 For Optimization currency Table Query Start
    if (!empty($vSystemDefaultCurrencyName)) {
        $defaultCurrency = $vSystemDefaultCurrencyName;
    }else{
        $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    //Added By HJ On 22-06-2020 For Optimization currency Table Query End
    /* changes for rental */
    //Added By HJ On 24-06-2020 For Optimization trips Table Query Start
    if(isset($tripDetailsArr['trips_'.$tripId])){
        $data_trips = $tripDetailsArr['trips_'.$tripId];
    }else{
        $data_trips = $obj->MySQLSelect("select * from trips where iTripId='" . $tripId . "'");
        $tripDetailsArr['trips_'.$tripId] = $data_trips;
    }
    //Added By HJ On 24-06-2020 For Optimization currency Table Query End
    //echo "<pre>";print_r($data_trips);die;
    $eType = $data_trips[0]['eType'];
    $iDriverId = $data_trips[0]['iDriverId'];
    /* added for rental */
    $iRentalPackageId = $data_trips[0]['iRentalPackageId'];
    /* added for rental */

    //Added By HJ On 04-02-2019 For Get Vehicle Type Commission When Service Applied More Than One End
    $fPickUpPrice = $data_trips[0]['fPickUpPrice'];
    $fNightPrice = $data_trips[0]['fNightPrice'];
    $iQty = $data_trips[0]['iQty'];
    $eFareType = $data_trips[0]['eFareType'];
    $eFlatTrip = $data_trips[0]['eFlatTrip'];
    $fFlatTripPrice = $data_trips[0]['fFlatTripPrice'];
    $iWaitingFeeTimeLimit = $Fare_data[0]['iWaitingFeeTimeLimit'];
    $fWaitingFees = $Fare_data[0]['fWaitingFees'];
    $eHailTrip = $data_trips[0]['eHailTrip'];
    $eWalletDebitAllow = $data_trips[0]['eWalletDebitAllow'];
    $iOrganizationId = $data_trips[0]['iOrganizationId'];
    $ePaymentBy = $data_trips[0]['ePaymentBy'];
    $iHotelId = $data_trips[0]['iHotelId'];
    $fTripHoldPrice = $Commision_Fare_Hotel = 0;
    $fAirportPickupSurge = $data_trips[0]['fAirportPickupSurge'];
    $fAirportDropoffSurge = $data_trips[0]['fAirportDropoffSurge'];
    $tUserWalletBalance = $data_trips[0]['tUserWalletBalance'];
    $ePoolRide = $data_trips[0]['ePoolRide'];
    if (empty($tUserWalletBalance)) {
        $tUserWalletBalance = 0;
    }
    if ($eHailTrip == "Yes" || ($iRentalPackageId > 0 && $ENABLE_WAITING_CHARGE_RENTAL != "Yes") || ($eFlatTrip == "Yes" && $ENABLE_WAITING_CHARGE_FLAT_TRIP != "Yes")) {
        $fWaitingFees = 0;
    }
    // add for hotel web
    $eBookingFrom = $data_trips[0]['eBookingFrom'];
    $iHotelBookingId = $data_trips[0]['iHotelBookingId'];
    $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
    if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
        $AirportSurgePickupPrice = $fAirportPickupSurge > 1 ? $fAirportPickupSurge : 1;
        $AirportSurgeDropoffPrice = $fAirportDropoffSurge > 1 ? $fAirportDropoffSurge : 1;
    }
    $fVisitFee = $data_trips[0]['fVisitFee'];
    $tripTimeInMinutes = ($totalTimeInMinutes_trip != '') ? $totalTimeInMinutes_trip : 0;
    $fPricePerKM = getVehicleCountryUnit_PricePerKm($vehicleTypeID, $Fare_data[0]['fPricePerKM']);
    $fTollPrice = $data_trips[0]['fTollPrice'];
    $eTollSkipped = $data_trips[0]['eTollSkipped'];
    //$TaxArr = getMemberCountryTax($iUserId, "Passenger"); // Commented By HJ On 18-09-2019 Replace Of Below Line
    $TaxArr = getMemberCountryTax($iDriverId, "Driver"); // Added By HJ Get Driver Country Tax As Per Discuss With KS Sir On 18-09-2019
    $fTax1 = $TaxArr['fTax1'];
    $fTax2 = $TaxArr['fTax2'];
    //print_r($TaxArr);die;
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
    $Fare_data[0]['waitingTime'] = $waitingTime;
    $Fare_data[0]['iWaitingFeeTimeLimit'] = $iWaitingFeeTimeLimit;
    $Fare_data[0]['fWaitingFees'] = $fWaitingFees;
    $Fare_data[0]['eHailTrip'] = $eHailTrip;
    $Fare_data[0]['tVehicleTypeData'] = $data_trips[0]['tVehicleTypeData'];
    //added for rental
    $Fare_data[0]['iRentalPackageId'] = $iRentalPackageId;
    //print_r($Fare_data);die;
    $result = getTripFare($Fare_data, "1");
    $total_fare = $oneSeatCharge = $fTripGenerateFare = $result['FinalFare'];
    //$fTripGenerateFare = $result['FinalFare'];
    //$fTripGenerateFare_For_Commission = $result['FinalFare'];
    $fTripGenerateFare_For_Commission = $result['FinalFare_UFX_Commission'];
    //Added By HJ On 26-12-2018 For Calculate Extra Person Charge Amount Start
    $finalFare = $totalFare = 0;
    $fPoolPercentage = 1;
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $personData['finalFare'] = $finalFare;
        $personData['total_fare'] = $total_fare;
        $personData['fTripGenerateFare'] = $fTripGenerateFare;
        $personData['fTripGenerateFare_For_Commission'] = $fTripGenerateFare_For_Commission;
        $personData['vehicleTypeID'] = $vehicleTypeID;
        $fareDetailArray = calculateFareForShark($personData);
        if (count($fareDetailArray) > 0) {
            $fPoolPercentage = $fareDetailArray['fPoolPercentage'];
            $fTripGenerateFare = $fareDetailArray['fTripGenerateFare'];
            $total_fare = $fareDetailArray['total_fare'];
            $fTripGenerateFare_For_Commission = $fareDetailArray['fTripGenerateFare_For_Commission'];
            $totalFare = $fareDetailArray['totalFare'];
        }
    }
    //Added By HJ On 26-12-2018 For Calculate Extra Person Charge Amount End
    //$fSurgePriceDiff = round(($fTripGenerateFare * $surgePrice) - $fTripGenerateFare, 2);
    $fSurgePriceDiff = round(($fTripGenerateFare_For_Commission * $surgePrice) - $fTripGenerateFare_For_Commission, 2);
    $total_fare += $fSurgePriceDiff;
    $fTripGenerateFare += $fSurgePriceDiff;
    //$fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission + $fSurgePriceDiff;
    if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes' && $AirportSurgePickupPrice > 0) {
        /* Extra Airport Pickup surge */
        $fAirportPickupSurgeAmount = round(($fTripGenerateFare_For_Commission * $AirportSurgePickupPrice) - $fTripGenerateFare_For_Commission, 2);
        $total_fare += $fAirportPickupSurgeAmount;
        $fTripGenerateFare += $fAirportPickupSurgeAmount;
        /* Extra Airport Pickup surge */
        /* Extra Airport Pickup surge */
        $fAirportDropoffSurgeAmount = round(($fTripGenerateFare_For_Commission * $AirportSurgeDropoffPrice) - $fTripGenerateFare_For_Commission, 2);
        $total_fare += $fAirportDropoffSurgeAmount;
        $fTripGenerateFare += $fAirportDropoffSurgeAmount;
        /* Extra Airport Pickup surge */
    }
    $fTripGenerateFare_For_Commission += $fSurgePriceDiff;
    /* Waiting Fee  Calculation */
    //$fWaitingFees = $result['fWaitingFees'];
    $fWaitingFeesArrData = getTripWaitingFee($tripId);
    $WaitingFeeCommission = 0;
    if (is_array($fWaitingFeesArrData)) {
        $fWaitingFees_tmp = $fWaitingFeesArrData['WaitingFee'];
        $WaitingFeeCommission = $fWaitingFeesArrData['WaitingFeeCommission'];
        $fWaitingFees = $fWaitingFees_tmp;
    } else {
        $fWaitingFees = $fWaitingFeesArrData;
    }

    if ($fWaitingFees > 0) {
        $total_fare += $fWaitingFees;
        $fTripGenerateFare += $fWaitingFees;
        $fTripGenerateFare_For_Commission += $fWaitingFees;
    }

    /* Waiting Fee  Calculation */
    $fTripHoldFees = $fTripHoldPrice = 0;
    /* Added By HJ On 28-12-2018 For Trip Hold Fee Calculation Start In Transit */
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        if ($totalHoldTimeInMinutes_trip > 0 && $ENABLE_INTRANSIT_SHOPPING_SYSTEM == "Yes") {
            /* $fTripHoldFees = $Fare_data[0]['fTripHoldFees'];
              $fTripHoldPrice = round($Fare_data[0]['fTripHoldFees'] * $totalHoldTimeInMinutes_trip, 2); */
            $fTripHoldPrice = calculateTransitamount($Fare_data, $totalHoldTimeInMinutes_trip);
            $total_fare += $fTripHoldPrice;
            $fTripGenerateFare += $fTripHoldPrice;
            $fTripGenerateFare_For_Commission += $fTripHoldPrice;
        }
    }

    /* Added By HJ On 28-12-2018 For Trip Hold Fee Calculation End In Transit */

    $iMinFare = $result['iMinFare'];
    // make changes for rental
    if ($eFlatTrip == "No" && $iRentalPackageId == 0) {
        if ($iMinFare > $fTripGenerateFare) {
            $MinFareDiff = $iMinFare - $total_fare;
            $total_fare = $iMinFare;
            $fTripGenerateFare = $iMinFare;
            $fTripGenerateFare_For_Commission = $iMinFare;
        } else {
            $MinFareDiff = "0";
            if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
                if ($fAirportDropoffSurgeAmount > 0 || $fAirportPickupSurgeAmount > 0) {
                    $fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission + $fAirportDropoffSurgeAmount + $fAirportPickupSurgeAmount;
                }
            }
        }
    } else {
        if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
            if ($fAirportDropoffSurgeAmount > 0 || $fAirportPickupSurgeAmount > 0) {
                $fTripGenerateFare_For_Commission = $fTripGenerateFare_For_Commission + $fAirportDropoffSurgeAmount + $fAirportPickupSurgeAmount;
            }
        }
    }
    /* Toll Calculation */
    if ($fTollPrice > 0) {
        $total_fare += $fTollPrice;
        $fTripGenerateFare += $fTollPrice;
    }
    /* Toll Calculation */


    if ($SERVICE_PROVIDER_FLOW == "Provider" && $Fare_data[0]['eFareType'] == "Fixed" && $eType == "UberX") {
        $result['fCommision'] = $result['FareOfCommision'] + $WaitingFeeCommission;
    } else if ($SERVICE_PROVIDER_FLOW == "Provider" && $eType == "UberX") {
        $result['fCommision'] = round(((($fTripGenerateFare_For_Commission - $fWaitingFees) * $Fare_data[0]['fCommision']) / 100), 2);
        $result['fCommision'] = $result['fCommision'] + $result['MaterialFareOfCommision'] + $WaitingFeeCommission;
    } else {
        $result['fCommision'] = round((($fTripGenerateFare_For_Commission * $Fare_data[0]['fCommision']) / 100), 2);
    }

    //echo "Tot1:".$fTripGenerateFare_For_Commission;exit;
    ## Checking For Kiosk Hotel Commission ##
    if ($eBookingFrom == 'Kiosk') {
        $Ksql = "SELECT a.fHotelServiceCharge FROM administrators as a LEFT JOIN hotel as h on h.iAdminId=a.iAdminId WHERE h.iHotelId = '" . $iHotelId . "'";
        $dataKiosk = $obj->MySQLSelect($Ksql);

        $HOTEL_BOOKING_SERVICE_CHARGE = $dataKiosk[0]['fHotelServiceCharge'];
        if ($HOTEL_BOOKING_SERVICE_CHARGE > 0) {
            $iBaseFareFinal = $result['iBaseFare'];
            $Commision_Fare_Hotel = round((($iBaseFareFinal * $HOTEL_BOOKING_SERVICE_CHARGE) / 100), 2);
            $fTripGenerateFare += $Commision_Fare_Hotel;
            $total_fare += $Commision_Fare_Hotel;
        }
    }

    ## Checking For Kiosk Hotel Commission ##
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
    $Fare_data[0]['fDiscount'] = $Fare_data[0]['vDiscount'] = 0;
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
    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = 0;
    if ($eType == "Multi-Delivery") {
        $fOutStandingAmount = $data_trips[0]['fOutStandingAmount'];
    } else {
        $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    }
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $getUserOutstandingAmount = getUserOutstandingAmount($iUserId, "iTripId", $tripId);
        $fOutStandingAmount = $getUserOutstandingAmount['fPendingAmount'];
    }
    if ($fOutStandingAmount > 0) {
        $total_fare += $fOutStandingAmount;
        $fTripGenerateFare += $fOutStandingAmount;
    }
    /* Checking For Passenger Outstanding Amount */
    // add for hotel
    if ($eBookingFrom == 'Hotel') {
        $HOTEL_BOOKING_SERVICE_CHARGE = get_value('administrators', 'fHotelServiceCharge', 'iAdminId', $iHotelBookingId, '', 'true');
        if ($HOTEL_BOOKING_SERVICE_CHARGE > 0) {
            $iBaseFareFinal = $result['iBaseFare'];
            $Commision_Fare_Hotel = round((($iBaseFareFinal * $HOTEL_BOOKING_SERVICE_CHARGE) / 100), 2);
            $fTripGenerateFare += $Commision_Fare_Hotel;
            $total_fare += $Commision_Fare_Hotel;
        }
    }
    /* Tax Calculation */
    $result['fTax1'] = $result['fTax2'] = 0;
    if ($fTax1 > 0) {
        $fTaxAmount1 = round(((($fTripGenerateFare - $discountValue) * $fTax1) / 100), 2);
        $fTripGenerateFare += $fTaxAmount1;
        $total_fare += $fTaxAmount1;
        $result['fTax1'] = $fTaxAmount1;
    }
    if ($fTax2 > 0) {
        $total_fare_new = $fTripGenerateFare - $discountValue - $fTaxAmount1;
        $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
        $fTripGenerateFare += $fTaxAmount2;
        $total_fare += $fTaxAmount2;
        $result['fTax2'] = $fTaxAmount2;
    }
    /* Tax Calculation */
    /* Check debit wallet For Count Total Fare  Start */
    $user_wallet_debit_amount = 0;
    //$eWalletAdjustment = get_value('register_user', 'eWalletAdjustment', 'iUserId', $iUserId, '', 'true');
    //echo $total_fare;die;
    if ($eWalletDebitAllow == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        if ($user_available_balance > 0) {
            $totalCurrentActiveTripsArr = getCurrentActiveTripsTotal($iUserId);
            $totalCurrentActiveTripsIdsArr = $totalCurrentActiveTripsArr['ActiveTripIds'];
            $totalCurrentActiveOrderIdsArr = $totalCurrentActiveTripsArr['ActiveOrderIds'];
            $totalCurrentActiveTripsCount = $totalCurrentActiveTripsArr['TotalCount'];

            /*             * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */
            // Charge an amount that is autorized when trip was initially requested in case when multiple jobs are going on.
            if (($totalCurrentActiveTripsCount > 1 || in_array($tripId, $totalCurrentActiveTripsIdsArr) == false) && ($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
                $user_available_balance = $tUserWalletBalance;
            }
            /*             * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */
            //Added By HJ On 30-12-2018 For Calculate Pool Invoice As Per Discuss WIth QA Start
            $personData_wallet = array();
            $personData_wallet['ePoolRide'] = $personData['ePoolRide'];
            $personData_wallet['iPersonSize'] = $personData['iPersonSize'];
            $personData_wallet['POOL_ENABLE'] = $personData['POOL_ENABLE'];
            $personData_wallet['fPoolPercentage'] = $fPoolPercentage;
            $personData_wallet['fSurgePriceDiff'] = $fSurgePriceDiff;
            $personData_wallet['oneSeatCharge'] = $oneSeatCharge;
            $personData_wallet['iMinFare'] = $iMinFare;
            $personData_wallet['discountValue'] = $discountValue;
            $personData_wallet['fAirportPickupSurgeAmount'] = $fAirportPickupSurgeAmount;
            $personData_wallet['fAirportDropoffSurgeAmount'] = $fAirportDropoffSurgeAmount;
            $personData_wallet['fTax1'] = $fTax1;
            $personData_wallet['total_fare'] = $total_fare;
            $walletDeducted = 0;
            if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                $wallet_fare = GetWalletAmountPool($personData_wallet);
                $walletDeducted = 1;
                $total_fare = $wallet_fare - $user_available_balance;
            } else {
                /* $total_fare = 0;
                  $wallet_fare = $total_fare; */
                $wallet_fare = $total_fare; //it is done like this bc in standard package, wallet amount is not deducted
                $total_fare = 0;
            }
            /* if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes") {
              $totalOneSeatFare = $oneSeatCharge + $fSurgePriceDiff;
              if ($iPersonSize > 1) {
              $twoSeatCharge = ($oneSeatCharge + ($totalOneSeatFare * $fPoolPercentage / 100));
              $iMinFare = $iMinFare * $iPersonSize;
              if ($twoSeatCharge < $iMinFare) {
              $twoSeatCharge = $iMinFare;
              }
              $poolTaxAmount = (($twoSeatCharge - $discountValue) * $fTax1) / 100;
              $wallet_fare += $twoSeatCharge - $discountValue + $poolTaxAmount;
              } else {
              if ($totalOneSeatFare < $iMinFare) {
              $totalOneSeatFare = $iMinFare;
              }
              $poolTaxAmount = (($totalOneSeatFare - $discountValue) * $fTax1) / 100;
              $wallet_fare += $totalOneSeatFare - $discountValue + $poolTaxAmount;

              }
              } else {
              $wallet_fare = $total_fare;
              } */
            //Added By HJ On 30-12-2018 For Calculate Pool Invoice As Per Discuss WIth QA End
            if ($wallet_fare > $user_available_balance) {
                if ($walletDeducted == 0) {
                    $total_fare = $wallet_fare - $user_available_balance;
                }
                $user_wallet_debit_amount = $user_available_balance;
            } else {
                $user_wallet_debit_amount = $wallet_fare;
                $total_fare = 0;
            }
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
            $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING# " . $vRideNo;
            $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);
            //$obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
        }
    }
    /* Check debit wallet For Count Total Fare  End */
    if ($Fare_data[0]['eFareType'] == 'Fixed') {
        $Fare_data[0]['iBaseFare'] = 0;
    } else {
        $Fare_data[0]['iBaseFare'] = $result['iBaseFare'];
    }
    $total_fare = round($total_fare, 2);
    $finalFareData['total_fare'] = $total_fare;
    $finalFareData['iBaseFare'] = $result['iBaseFare'];
    $finalFareData['fPricePerMin'] = $result['FareOfMinutes'];
    $finalFareData['fPricePerKM'] = $result['FareOfDistance'];
    //$finalFareData['fCommision'] = $result['FareOfCommision'];
    //$finalFareData['fCommision'] = round((($fTripGenerateFare*$result['fCommision'])/100),2);
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
    $finalFareData['fTax1Percentage'] = $TaxArr['fTax1'];
    $finalFareData['fTax2Percentage'] = $TaxArr['fTax2'];
    $finalFareData['fWaitingFees'] = $fWaitingFees;
    $finalFareData['fTripHoldPrice'] = $fTripHoldPrice; // Added By HJ For Intransit Amount On 28-12-2018
    $finalFareData['fOutStandingAmount'] = $fOutStandingAmount;
    $finalFareData['Commision_Fare_Hotel'] = $Commision_Fare_Hotel;
    $finalFareData['fHotelBookingChargePercentage'] = $HOTEL_BOOKING_SERVICE_CHARGE;
    $finalFareData['fExtraPersonCharge'] = $totalFare; //Added By HJ On 26-12-2018 For Insert Extra Charge Amount
    $finalFareData['fAirportPickupSurgeAmount'] = $fAirportPickupSurgeAmount;
    $finalFareData['fAirportDropoffSurgeAmount'] = $fAirportDropoffSurgeAmount;
    $finalFareData['tVehicleTypeFareData'] = $result['tVehicleTypeFareData'];
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

function calculateFareEstimateAll($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $iUserId, $priceRatio, $startDate = "", $endDate = "", $couponCode = "", $surgePrice = 1, $fMaterialFee = 0, $fMiscFee = 0, $fDriverDiscount = 0, $DisplySingleVehicleFare = "", $eUserType = "Passenger", $iQty = 1, $SelectedCarTypeID = "", $isDestinationAdded = "Yes", $eFlatTrip = "No", $fFlatTripPrice = 0, $sourceLocationArr = array(), $destinationLocationArr = array(), $DisplayMultiDeliveryFare = "", $RideType = "Ride", $scheduleDate = "", $eFly = "", $iFromStationId = "", $iToStationId = "") {

    //                                          1                   2               3            4           5           6                7               8                 9                   10             11                12                  13                             14                   15              16                   17                      18                  19                     20                     21
    //added by SP for fly stations on 19-08-2019 add last three parameter
    global $generalobj, $obj, $tconfig, $APPLY_SURGE_ON_FLAT_FARE, $ENABLE_AIRPORT_SURCHARGE_SECTION, $countryCodeAdmin, $DisplayFrontEstimate, $langcodefront, $IS_RETURN_ARR_WITH_ORIG_AMT;
    $fpickupsurchargefare = $fdropoffsurchargefare = $fAirportPickupSurgeAmount = $fAirportDropoffSurgeAmount = $discountValue_orig = 0;

    if ($eUserType == 'Company') {
        $tableName = "company";
        $fiedls = "vCurrencyCompany AS currency,vLang";
        $userField = "iCompanyId";
        $eUnit = getMemberCountryUnit($iUserId, "Company");
        $TaxArr = getMemberCountryTax($iUserId, "Company");
    } else if ($eUserType == "Passenger") {
        $tableName = "register_user";
        $fiedls = "vCurrencyPassenger AS currency,vLang";
        $userField = "iUserId";
        $eUnit = getMemberCountryUnit($iUserId, "Passenger");
        $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    } else {
        $tableName = "register_driver";
        $fiedls = "vCurrencyDriver AS currency,vLang";
        $userField = "iDriverId";
        $eUnit = getMemberCountryUnit($iUserId, "Driver");
        $TaxArr = getMemberCountryTax($iUserId, "Driver");
    }
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    if ($eFly == 'Yes') {
        include_once($tconfig["tpanel_path"] . "include/features/include_fly_stations.php");
    }
    //print_r($tableName);die;
    $getUserData = $obj->MySQLSelect("SELECT $fiedls FROM " . $tableName . " WHERE $userField='" . $iUserId . "'");

    $vCurrencyPassenger = $userlangcode = "";
    if (count($getUserData) > 0) {
        $vCurrencyPassenger = $getUserData[0]['currency'];
        $userlangcode = $getUserData[0]['vLang'];
    }
    if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $vSymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    if (isset($_SESSION['sess_lang']) && trim($_SESSION['sess_lang']) != "") {
        $userlangcode = $_SESSION['sess_lang'];
    }
    //$eUnit = getMemberCountryUnit($iUserId,"Passenger");
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    if ($DisplayFrontEstimate == "Yes") {
        $languageLabelsArr = getLanguageLabelsArr($langcodefront, "1");
    }
    //Added By HJ On 23-09-2019 For Get Hotel Service Charge Data Start
    $HOTEL_BOOKING_SERVICE_CHARGE = 0;
    if ($bookingHotelId > 0 && strtolower($eBookingFrom) == 'hotel') {
        $getHotelCharges = $obj->MySQLSelect("SELECT fHotelServiceCharge FROM administrators WHERE iAdminId='" . $bookingHotelId . "'");
        if (count($getHotelCharges) > 0) {
            $HOTEL_BOOKING_SERVICE_CHARGE = $getHotelCharges[0]['fHotelServiceCharge'];
        }
    }
    //Added By HJ On 23-09-2019 For Get Hotel Service Charge Data End
    if ($DisplySingleVehicleFare == "") {
        $ssql = " AND eStatus='Active'";
        if ($SelectedCarTypeID != "") {
            $ssql .= " AND iVehicleTypeId IN ($SelectedCarTypeID) ";
        }
        // 06-01-2020 in front panel estimate add
        if ($DisplayFrontEstimate == "Yes") {
            $ssql .= " AND eType = '" . $RideType . "'";
        }
        // 06-01-2020 in front panel estimate add
        $sql_vehicle_type = "SELECT * FROM vehicle_type WHERE 1 " . $ssql;
        $Fare_data = $obj->MySQLSelect($sql_vehicle_type);
        $result = array();
        for ($i = 0; $i < count($Fare_data); $i++) {
            $fPickUpPrice = $fNightPrice = 1;
            $data_surgePrice = checkSurgePrice($Fare_data[$i]['iVehicleTypeId'], $scheduleDate);
            if ($data_surgePrice['Action'] == "0") {
                if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                    $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
                } else {
                    $fNightPrice = $data_surgePrice['SurgePriceValue'];
                }
            }
            $Fare_data[$i]['currencySymbol'] = $vSymbol;
            $Fare_data[$i]['TripTimeMinutes'] = $totalTimeInMinutes_trip;
            $Fare_data[$i]['TripDistance'] = $tripDistance;
            //$result = getTripFare($Fare_data[$i], $surgePrice);
            //added by SP for fly stations on 19-08-2019 start
            if ($eFly == 'Yes') {
                //$Fare_data[$i]['iBaseFare'] = getFareForFlyVehicles($Fare_data[$i]['iVehicleTypeId'], $iFromStationId, $iToStationId) * $priceRatio; //commented bc iBaseFare at line 808 is calculated by ratio so two times calculated
                $Fare_data[$i]['iBaseFare'] = getFareForFlyVehicles($Fare_data[$i]['iVehicleTypeId'], $iFromStationId, $iToStationId);
            }

            //added by SP for fly stations on 19-08-2019 end

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
                //$Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['fFixedFare'] * $Fare_data[$i]['iQty'];
                if ($eFly == 'Yes') {
                    $Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['iBaseFare'] * $iQty;
                    $iBaseFare = $Fare_data[$i]['iBaseFare'];
                } else {
                    $Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['fFixedFare'] * $iQty;
                }
            } else if ($Fare_data[$i]['eFareType'] == 'Hourly') {
                $Fare_data[$i]['iBaseFare'] = $Fare_data[$i]['fPricePerKM'] = 0;
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

            $eFlatTrip = "No";
            $fFlatTripPrice = 0;

            if ($isDestinationAdded == "Yes" && strtoupper(PACKAGE_TYPE) != "STANDARD") {
                $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $Fare_data[$i]['iVehicleTypeId']);
                $eFlatTrip = $data_flattrip['eFlatTrip'];
                $fFlatTripPrice = $data_flattrip['Flatfare'];
            }

            $Fare_data[$i]['eFlatTrip'] = $eFlatTrip;
            $Fare_data[$i]['fFlatTripPrice'] = $fFlatTripPrice;
            if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
                $fPickUpPrice = 1;
                $fNightPrice = 1;
            }
            $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
            $fAirportPickupSurgeAmount = $fAirportDropoffSurgeAmount = $fSurgePriceDiff = $SurgePriceFactor = $fpickupsurchargefare = 0;
            // add airport surge //
            if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
                    if (checkSharkPackage()) {
                        $GetVehicleIdfromGeoLocation = CheckSurgeAirportFromGeoLocation($sourceLocationArr, $destinationLocationArr, $Fare_data[$i]['iVehicleTypeId']);
                        $fpickupsurchargefare = $GetVehicleIdfromGeoLocation['fpickupsurchargefare'];
                        $fdropoffsurchargefare = $GetVehicleIdfromGeoLocation['fdropoffsurchargefare'];
                    }
                }
            }
            // END AIRPORT SURGE //
            if ($eFlatTrip == "No") {
                $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;
                $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
                $SurgePriceFactor = strval($surgePrice);

                if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
                    // airport pickup surge //
                    if ($fpickupsurchargefare > 1) {
                        $fAirportPickupSurgeAmount = round(($total_fare * $fpickupsurchargefare) - $total_fare, 2);
                        $pickupSurgePriceFactor = strval($fpickupsurchargefare);
                    }
                    // airport pickup surge //
                    // airport dropoff surge//
                    if ($fdropoffsurchargefare > 1) {
                        $fAirportDropoffSurgeAmount = round(($total_fare * $fdropoffsurchargefare) - $total_fare, 2);
                        $dropoffSurgePriceFactor = strval($fdropoffsurchargefare);
                    }
                    //airport dropoff surge//
                    $total_fare = $total_fare + $fSurgePriceDiff + $fAirportPickupSurgeAmount + $fAirportDropoffSurgeAmount;
                } else {
                    $total_fare = $total_fare + $fSurgePriceDiff;
                }

                $minimamfare = round($Fare_data[$i]['iMinFare'] * $priceRatio, 2);
                $fMinFareDiff = 0;
                if ($minimamfare > $total_fare) {
                    $fMinFareDiff = $minimamfare - $total_fare;
                    $total_fare = $minimamfare;
                    $Fare_data[$i]['FinalFare'] = $total_fare;
                }
            } else {
                $total_fare = round($fFlatTripPrice * $priceRatio, 2);
                $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
                $SurgePriceFactor = strval($surgePrice);
                if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
                    // airport pickup surge //
                    if ($fpickupsurchargefare > 1) {
                        $fAirportPickupSurgeAmount = round(($total_fare * $fpickupsurchargefare) - $total_fare, 2);
                        $pickupSurgePriceFactor = strval($fpickupsurchargefare);
                    }
                    // airport pickup surge //
                    // airport dropoff surge//
                    if ($fdropoffsurchargefare > 1) {
                        $fAirportDropoffSurgeAmount = round(($total_fare * $fdropoffsurchargefare) - $total_fare, 2);
                        $dropoffSurgePriceFactor = strval($fdropoffsurchargefare);
                    }
                    //airport dropoff surge//
                    $total_fare = $total_fare + $fSurgePriceDiff + $fAirportPickupSurgeAmount + $fAirportDropoffSurgeAmount;
                } else {
                    $total_fare = $total_fare + $fSurgePriceDiff;
                }

                $Fare_data[$i]['FinalFare'] = $total_fare;
                $fMinFareDiff = 0;
            }
            $Commision_Fare = round((($total_fare * $Fare_data[$i]['fCommision']) / 100), 2);
            $discountValue = $discountValue_orig = 0;
            $discountValueType = "cash";
            if ($couponCode != "") {
                //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data Start
                $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eStatus='Active'");
                if (count($getCouponCode) > 0) {
                    $discountValue = $getCouponCode[0]['fDiscount'];
                    $discountValueType = $getCouponCode[0]['eType'];
                    $discountValue_orig = $discountValue;
                }
                //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data End
                //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019 
                //$discountValue_orig = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
                //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
                if ($discountValueType == "percentage") {
                    $vDiscount = round($discountValue, 1) . ' ' . "%";
                    $discountValue = round(($total_fare * $discountValue), 1) / 100;
                } else {
                    $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                    $discountValue = round(($discountValue * $priceRatio), 2);
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
                    //$discountValue = $total_fare;
                }
                if ($Fare_data[0]['eFareType'] == "Regular") {
                    $Fare_data[0]['fDiscount'] = $discountValue;
                    $Fare_data[0]['vDiscount'] = $vDiscount;
                } else {
                    $Fare_data[0]['fDiscount'] = $Fare_data[0]['fDiscount_fixed'];
                    $Fare_data[0]['vDiscount'] = $vDiscount;
                }
            }
            /* Tax Calculation */
            $fTax1 = $TaxArr['fTax1'];
            $fTax2 = $TaxArr['fTax2'];
            if ($fTax1 > 0) {
                $fTaxAmount1 = round((($total_fare * $fTax1) / 100), 2);
                $total_fare = $total_fare + $fTaxAmount1;
                // $Fare_data[$i]['fTax1'] = $vSymbol . " " . number_format($fTaxAmount1, 2);
                //$Fare_data[$i]['fTax1'] = $generalobj->formateNumAsPerCurrency(number_format($fTaxAmount1, 2),'');
                $Fare_data[$i]['fTax1'] = $generalobj->formateNumAsPerCurrency($fTaxAmount1, $vCurrencyPassenger);
            }
            if ($fTax2 > 0) {
                $total_fare_new = $total_fare - $fTaxAmount1;
                $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
                $total_fare = $total_fare + $fTaxAmount2;
                // $Fare_data[$i]['fTax2'] = $vSymbol . " " . number_format($fTaxAmount2, 2);
                //$Fare_data[$i]['fTax2'] = $generalobj->formateNumAsPerCurrency(number_format($fTaxAmount2, 2),'');
                $Fare_data[$i]['fTax2'] = $generalobj->formateNumAsPerCurrency($fTaxAmount2, $vCurrencyPassenger);
            }
            /* Tax Calculation */
            // Added By HJ On 23-09-2019 For Calculate Hotel Service Charge Start Sheet Bug - 339
            if (strtolower($eBookingFrom) == 'hotel' && $HOTEL_BOOKING_SERVICE_CHARGE > 0) {
                $fHotelCommision = $generalobj->setTwoDecimalPoint((($iBaseFare * $HOTEL_BOOKING_SERVICE_CHARGE) / 100));
                $total_fare += $fHotelCommision;
            }
            // Added By HJ On 23-09-2019 For Calculate Hotel Service Charge End Sheet Bug - 339
            /** calculate fare * */
            $Fare_data[$i]['FareOfMinutes'] = $Minute_Fare;
            $Fare_data[$i]['FareOfDistance'] = $Distance_Fare;
            $Fare_data[$i]['FareOfCommision'] = $Commision_Fare;
            $Fare_data[$i]['fPricePerMin'] = $Fare_data[$i]['fPricePerMin'];
            $Fare_data[$i]['fPricePerKM'] = $Fare_data[$i]['fPricePerKM'];
            $Fare_data[$i]['fCommision'] = $Fare_data[$i]['fCommision'];
            $Fare_data[$i]['FinalFare'] = $total_fare;
            if ($eFly == 'Yes') {
                $Fare_data[$i]['iBaseFare'] = $iBaseFare;
            } else {
                $Fare_data[$i]['iBaseFare'] = ($Fare_data[$i]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
            }
            //$Fare_data[$i]['iBaseFare'] = ($Fare_data[$i]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;

            $Fare_data[$i]['iMinFare'] = round($Fare_data[$i]['iMinFare'] * $priceRatio, 2);

            if ($Fare_data[$i]['eFareType'] == "Regular") {
                //$Fare_data[$i]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
                // $Fare_data[$i]['total_fare'] = $vSymbol . " " . number_format($total_fare, 2);
                //$Fare_data[$i]['total_fare'] = $generalobj->formateNumAsPerCurrency(number_format($total_fare, 2),'');
                $Fare_data[$i]['total_fare'] = $generalobj->formateNumAsPerCurrency($total_fare, $vCurrencyPassenger);
            } else {
                // $Fare_data[$i]['total_fare'] = $vSymbol . " " . number_format($Fare_data[$i]['FinalFare'], 2);
                //$Fare_data[$i]['total_fare'] = $generalobj->formateNumAsPerCurrency(number_format($Fare_data[$i]['FinalFare'], 2),'');
                $Fare_data[$i]['total_fare'] = $generalobj->formateNumAsPerCurrency($Fare_data[$i]['FinalFare'], $vCurrencyPassenger);
            }
            // For calculation for rental vehiclefare
            $Fare_data[$i]['eRental'] = 'No';
            $Fare_data[$i]['eRental_total_fare'] = "";
            $Fare_data[$i]['eRental_total_fare_value'] = 0;

            if (strtoupper(PACKAGE_TYPE) != "STANDARD" && ENABLE_RENTAL_OPTION == 'Yes') {
                $data_calculation_arr = array();
                $data_calculation_arr['iVehicleTypeId'] = $Fare_data[$i]['iVehicleTypeId'];
                $data_calculation_arr['userlangcode'] = $userlangcode;
                $data_calculation_arr['couponCode'] = $couponCode;
                $data_calculation_arr['discountValue_orig'] = $discountValue_orig;
                $data_calculation_arr['discountValueType'] = $discountValueType;
                $data_calculation_arr['priceRatio'] = $priceRatio;
                $data_calculation_arr['vSymbol'] = $vSymbol;

                $rentalFareDataArr = generateEstimatedRentalFare($data_calculation_arr);
                $Fare_data[$i]['eRental'] = $rentalFareDataArr['eRental'];
                $Fare_data[$i]['eRental_total_fare'] = $rentalFareDataArr['eRental_total_fare'];
                $Fare_data[$i]['eRental_total_fare_value'] = $rentalFareDataArr['eRental_total_fare_value'];
            }

            // For calculation for rental vehiclefare
            // for kiosk
            $priceperkmnew = getVehiclePrice_ByUSerCountry($iUserId, $fPricePerKM);

            // $Fare_data[$i]['iBaseFare'] = $vSymbol . " " . number_format($Fare_data[$i]['iBaseFare'], 2);
            // $Fare_data[$i]['fPricePerMin'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fPricePerMin'] * $priceRatio, 1), 2);
            // $Fare_data[$i]['fPricePerKM'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fPricePerKM'] * $priceRatio, 1), 2);
            // $Fare_data[$i]['fCommision'] = $vSymbol . " " . number_format(round($Fare_data[$i]['fCommision'] * $priceRatio, 1), 2);

            $Fare_data[$i]['iBaseFare'] = $generalobj->formateNumAsPerCurrency($Fare_data[$i]['iBaseFare'], $vCurrencyPassenger);

            $fPricePerMinRound = round($Fare_data[$i]['fPricePerMin'] * $priceRatio, 1);
            $Fare_data[$i]['fPricePerMin'] = $generalobj->formateNumAsPerCurrency($fPricePerMinRound, $vCurrencyPassenger);
            $fPricePerKMRound = round($Fare_data[$i]['fPricePerKM'] * $priceRatio, 1);
            $Fare_data[$i]['fPricePerKM'] = $generalobj->formateNumAsPerCurrency($fPricePerKMRound, $vCurrencyPassenger);
            $fCommisionRound = round($Fare_data[$i]['fCommision'] * $priceRatio, 1);
            $Fare_data[$i]['fCommision'] = $generalobj->formateNumAsPerCurrency($fCommisionRound, $vCurrencyPassenger);
            // for kisok
            if ($eUnit == "Miles") {
                $DisplayDistanceTxt = $languageLabelsArr['LBL_ONE_MILE_TXT'];
            } else {
                $DisplayDistanceTxt = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
            }

            // $Fare_data[$i]['fPricePerKMKiosk'] = $vSymbol . " " . number_format(round($priceperkmnew * $priceRatio, 2), 2);
            $priceperkmnewRound = round($priceperkmnew * $priceRatio, 2);
            $Fare_data[$i]['fPricePerKMKiosk'] = $generalobj->formateNumAsPerCurrency($priceperkmnewRound, $vCurrencyPassenger);
            $Fare_data[$i]['fPricePerKMUnit'] = $DisplayDistanceTxt;

            if ($eUserType == "Driver") {
                $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iUserId . "'";
                $currData = $obj->MySQLSelect($sqlp);
                $vCurrency = $currData[0]['vName'];
            } else {
                $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iUserId . "'";
                $currData = $obj->MySQLSelect($sqlp);
                $vCurrency = $currData[0]['vName'];
            }

            if ($currData[0]['eRoundingOffEnable'] == "Yes") {

                $roundingOffTotal_fare_amountArr = getRoundingOffAmount($Fare_data[$i]['FinalFare'], $vCurrency);
                $roundingOffTotal_fare_amount = $roundingOffTotal_fare_amountArr['finalFareValue'];

                if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                    $roundingMethod = "";
                } else {
                    $roundingMethod = "-";
                }

                if ($Fare_data[$i]['eFareType'] == "Regular") {
                    //$Fare_data[$i]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
                    // $Fare_data[$i]['total_fare'] = $vSymbol . " " . number_format($roundingOffTotal_fare_amount, 2);
                    $Fare_data[$i]['total_fare'] = $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amount, $vCurrencyPassenger);
                } else {
                    $Fare_data[$i]['total_fare'] = $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amount, $vCurrencyPassenger);
                    // $Fare_data[$i]['total_fare'] = $vSymbol . " " . number_format($roundingOffTotal_fare_amount, 2);
                }

                //$Fare_data[$i]['total_rounding_fare_amount'] = $vSymbol . " " .$generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount / $priceRatio);
                //$Fare_data[$i]['rounding_diff'] = $roundingMethod.' '.$vSymbol . " " .$generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue'] / $priceRatio);
                // $Fare_data[$i]['total_rounding_fare_amount'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount);
                $Fare_data[$i]['total_rounding_fare_amount'] = $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amount, $vCurrencyPassenger);

                // $Fare_data[$i]['rounding_diff'] = $roundingMethod . ' ' . $vSymbol . " " . $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue']);
                $Fare_data[$i]['rounding_diff'] = $roundingMethod . ' ' . $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amountArr['differenceValue'], $vCurrencyPassenger);
            }
            //added by SP for rounding off currency wise on 26-8-2019 end
            //print_r($Fare_data);die;
        }
    } else {

        $Fare_data = getVehicleFareConfig("vehicle_type", $vehicleTypeID);
        $fPickUpPrice = $fNightPrice = 1;
        $data_surgePrice = checkSurgePrice($Fare_data[0]['iVehicleTypeId'], $scheduleDate);
        if ($data_surgePrice['Action'] == "0") {
            if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
            } else {
                $fNightPrice = $data_surgePrice['SurgePriceValue'];
            }
        }
        if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
            $fPickUpPrice = $fNightPrice = 1;
        }
        $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);

        // add airport surge //
        if (strtoupper(PACKAGE_TYPE) == "SHARK" && $ENABLE_AIRPORT_SURCHARGE_SECTION == "Yes") {
            if (checkSharkPackage()) {
                $GetVehicleIdfromGeoLocation = CheckSurgeAirportFromGeoLocation($sourceLocationArr, $destinationLocationArr, $Fare_data[0]['iVehicleTypeId']);
                $fpickupsurchargefare = $GetVehicleIdfromGeoLocation['fpickupsurchargefare'];
                $fdropoffsurchargefare = $GetVehicleIdfromGeoLocation['fdropoffsurchargefare'];
            }
        }
        // End airport surge //
        $totalTimeInMinutes_trip = $generalobj->setTwoDecimalPoint($totalTimeInMinutes_trip); //Added By HJ On 06-12-2019 For Solved Issue Of Sheet = #577 and #585
        $Fare_data[0]['currencySymbol'] = $vSymbol;
        $Fare_data[0]['TripTimeMinutes'] = $totalTimeInMinutes_trip;
        $Fare_data[0]['TripDistance'] = $tripDistance;

        //$result = getTripFare($Fare_data[0], $surgePrice);
        /** calculate fare * */
        //added by SP for fly stations on 19-08-2019 start
        if ($eFly == 'Yes') {
            //$Fare_data[0]['iBaseFare'] = getFareForFlyVehicles($Fare_data[0]['iVehicleTypeId'], $iFromStationId, $iToStationId) * $priceRatio;//commented bc iBaseFare at line 1125 is calculated by ratio so two times calculated
            $Fare_data[0]['iBaseFare'] = getFareForFlyVehicles($Fare_data[0]['iVehicleTypeId'], $iFromStationId, $iToStationId);
        }

        //added by SP for fly stations on 19-08-2019 end

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
            //$Fare_data[0]['iBaseFare'] = $Fare_data[0]['fFixedFare'] * $Fare_data[0]['iQty'];
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
        $iBaseFare_Ori = $iBaseFare;
        $Minute_Fare_Ori = round(($fPricePerMin * $totalTimeInMinutes_trip), 2);
        $Distance_Fare_Ori = round(($fPricePerKM * $tripDistance), 2);
        $iBaseFare = round($iBaseFare * $priceRatio, 2);
        $fMaterialFee = $fMiscFee = $fDriverDiscount = 0;
        if (isset($Fare_data[0]['fMaterialFee'])) {
            $fMaterialFee = round($Fare_data[0]['fMaterialFee'] * $priceRatio, 2);
        }
        if (isset($Fare_data[0]['fMiscFee'])) {
            $fMiscFee = round($Fare_data[0]['fMiscFee'] * $priceRatio, 2);
        }
        if (isset($Fare_data[0]['fDriverDiscount'])) {
            $fDriverDiscount = round($Fare_data[0]['fDriverDiscount'] * $priceRatio, 2);
        }
        $fVisitFee = round($Fare_data[0]['fVisitFee'] * $priceRatio, 2);

        if ($eFlatTrip == "No") {
            $total_fare = ($iBaseFare + $Minute_Fare + $Distance_Fare + $fMaterialFee + $fMiscFee + $fVisitFee) - $fDriverDiscount;
            $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
            $SurgePriceFactor = strval($surgePrice);

            if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
                // airport pickup surge //
                if ($fpickupsurchargefare > 1) {
                    $fAirportPickupSurgeAmount = round(($total_fare * $fpickupsurchargefare) - $total_fare, 2);
                    $pickupSurgePriceFactor = strval($fpickupsurchargefare);
                }
                // airport pickup surge //
                // airport dropoff surge//
                if ($fdropoffsurchargefare > 1) {
                    $fAirportDropoffSurgeAmount = round(($total_fare * $fdropoffsurchargefare) - $total_fare, 2);
                    $dropoffSurgePriceFactor = strval($fdropoffsurchargefare);
                }
                //airport dropoff surge//

                $total_fare = $total_fare + $fSurgePriceDiff + $fAirportPickupSurgeAmount + $fAirportDropoffSurgeAmount;
            } else {
                $total_fare = $total_fare + $fSurgePriceDiff;
            }

            $minimamfare = round($Fare_data[0]['iMinFare'] * $priceRatio, 2);
            $fMinFareDiff = 0;
            if ($minimamfare > $total_fare && $Fare_data[0]['iRentalPackageId'] == 0) {
                $fMinFareDiff = $minimamfare - $total_fare;
                $total_fare = $minimamfare;
                $Fare_data[0]['FinalFare'] = $total_fare;
            }
        } else {
            $total_fare = round($fFlatTripPrice * $priceRatio, 2);
            $fSurgePriceDiff = round(($total_fare * $surgePrice) - $total_fare, 2);
            $SurgePriceFactor = strval($surgePrice);
            // airport surge //
            if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
                // airport pickup surge //
                if ($fpickupsurchargefare > 1) {
                    $fAirportPickupSurgeAmount = round(($total_fare * $fpickupsurchargefare) - $total_fare, 2);
                    $pickupSurgePriceFactor = strval($fpickupsurchargefare);
                }
                // airport pickup surge //
                // airport dropoff surge//
                if ($fdropoffsurchargefare > 1) {
                    $fAirportDropoffSurgeAmount = round(($total_fare * $fdropoffsurchargefare) - $total_fare, 2);
                    $dropoffSurgePriceFactor = strval($fdropoffsurchargefare);
                }
                //airport dropoff surge//

                $total_fare = $total_fare + $fSurgePriceDiff + $fAirportPickupSurgeAmount + $fAirportDropoffSurgeAmount;
            } else {
                $total_fare = $total_fare + $fSurgePriceDiff;
            }
            // airport surge //
            $Fare_data[0]['FinalFare'] = $total_fare;
            $fMinFareDiff = $Minute_Fare = $Minute_Fare_Ori = $Distance_Fare = $Distance_Fare_Ori = 0;
            $iBaseFare_Ori = $fFlatTripPrice;
        }
        $Commision_Fare = round((($total_fare * $Fare_data[0]['fCommision']) / 100), 2);
        $Commision_Fare_Ori = round($Commision_Fare / $priceRatio, 2);
        ## Calculate for Discount ##
        //$fSurgePriceDiff = $farewithsurcharge - $minimamfare;
        $Generated_Fare = $total_fare;
        $discountValue = $discountValue_Ori = 0;
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
                $discountValue_Ori = round((round($total_fare / $priceRatio, 2) * $discountValue), 1) / 100;
            } else {
                $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                $discountValue = round(($discountValue * $priceRatio), 2);
                if ($discountValue > $total_fare) {
                    $vDiscount = round($total_fare, 1) . ' ' . $curr_sym;
                } else {
                    $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
                }
                $discountValue_Ori = $discountValue;
            }
            $total_fare = $total_fare - $discountValue;
            $Fare_data[0]['fDiscount_fixed'] = $discountValue;
            if ($total_fare < 0) {
                $total_fare = 0;
                //$discountValue = $total_fare;
            }
            if ($Fare_data[0]['eFareType'] == "Regular") {
                $Fare_data[0]['fDiscount'] = $discountValue;
                $Fare_data[0]['vDiscount'] = $vDiscount;
            } else {
                $Fare_data[0]['fDiscount'] = $Fare_data[0]['fDiscount_fixed'];
                $Fare_data[0]['vDiscount'] = $vDiscount;
            }
            $Fare_data[0]['fDiscount_Ori'] = round($discountValue_Ori, 2);
        }
        ## Calculate for Discount ##
        /* Tax Calculation */
        $fTax1 = $TaxArr['fTax1'];
        $fTax2 = $TaxArr['fTax2'];
        if ($fTax1 > 0) {
            $fTaxAmount1 = round((($total_fare * $fTax1) / 100), 2);
            $total_fare = $total_fare + $fTaxAmount1;
            // $Fare_data[0]['fTax1'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($fTaxAmount1);
            $Fare_data[0]['fTax1'] = $generalobj->formateNumAsPerCurrency($fTaxAmount1, $vCurrencyPassenger);
            $Fare_data[0]['fTax1_Display'] = $generalobj->formateNumAsPerCurrency($fTaxAmount1, $vCurrencyPassenger);
            // $Fare_data[0]['fTax1_Display'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($fTaxAmount1);
        }
        if ($fTax2 > 0) {
            $total_fare_new = $total_fare - $fTaxAmount1;
            $fTaxAmount2 = round((($total_fare_new * $fTax2) / 100), 2);
            $total_fare = $total_fare + $fTaxAmount2;
            // $Fare_data[0]['fTax2'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($fTaxAmount2);
            $Fare_data[0]['fTax2'] = $generalobj->formateNumAsPerCurrency($fTaxAmount2, $vCurrencyPassenger);
            // $Fare_data[0]['fTax2_Display'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($fTaxAmount2);
            $Fare_data[0]['fTax2_Display'] = $generalobj->formateNumAsPerCurrency($fTaxAmount2, $vCurrencyPassenger);
        }
        /* Tax Calculation */
        // Added By HJ On 23-09-2019 For Calculate Hotel Service Charge Start Sheet Bug - 339
        if (strtolower($eBookingFrom) == 'hotel' && $HOTEL_BOOKING_SERVICE_CHARGE > 0) {
            $fHotelCommision = $generalobj->setTwoDecimalPoint((($iBaseFare * $HOTEL_BOOKING_SERVICE_CHARGE) / 100));
            $total_fare += $fHotelCommision;
        }
        // Added By HJ On 23-09-2019 For Calculate Hotel Service Charge End Sheet Bug - 339
        /** calculate fare * */
        $Fare_data[0]['FareOfMinutes'] = $Minute_Fare;
        $Fare_data[0]['FareOfDistance'] = $Distance_Fare;
        $Fare_data[0]['FareOfCommision'] = $Commision_Fare;
        $Fare_data[0]['fPricePerMin'] = $Fare_data[0]['fPricePerMin'];
        $Fare_data[0]['fPricePerKM'] = $Fare_data[0]['fPricePerKM'];
        $Fare_data[0]['fCommision'] = $Fare_data[0]['fCommision'];
        $Fare_data[0]['FinalFare'] = $total_fare;

        //added by SP for fly stations on 20-08-2019 start
        /* if($eFly=='Yes') {
          $Fare_data[0]['iBaseFare'] = $iBaseFare;
          } else {
          $Fare_data[0]['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
          } */
        //added by SP for fly stations on 20-08-2019 end

        $Fare_data[0]['iBaseFare'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare;
        $Fare_data[0]['iMinFare'] = $generalobj->setTwoDecimalPoint($Fare_data[0]['iMinFare'] * $priceRatio);
        $Fare_data[0]['total_fare_amount'] = $tripTotalAmount = $generalobj->setTwoDecimalPoint($total_fare);
        if ($Fare_data[0]['eFareType'] == "Regular") {
            //$Fare_data[0]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
            // $Fare_data[0]['total_fare'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($total_fare);
            $Fare_data[0]['total_fare'] = $generalobj->formateNumAsPerCurrency($total_fare, $vCurrencyPassenger);
        }
        $Fare_data[0]['eRental'] = 'No';
        $Fare_data[0]['eRental_total_fare'] = "";
        $Fare_data[0]['eRental_total_fare_value'] = 0;

        if (strtoupper(PACKAGE_TYPE) != "STANDARD" && ENABLE_RENTAL_OPTION == 'Yes') {
            $data_calculation_arr = array();
            $data_calculation_arr['iVehicleTypeId'] = $Fare_data[0]['iVehicleTypeId'];
            $data_calculation_arr['userlangcode'] = $userlangcode;
            $data_calculation_arr['couponCode'] = $couponCode;
            $data_calculation_arr['discountValue_orig'] = $discountValue_orig;
            $data_calculation_arr['discountValueType'] = $discountValueType;
            $data_calculation_arr['priceRatio'] = $priceRatio;
            $data_calculation_arr['vSymbol'] = $vSymbol;

            $rentalFareDataArr = generateEstimatedRentalFare($data_calculation_arr);
            $Fare_data[0]['eRental'] = $rentalFareDataArr['eRental'];
            $Fare_data[0]['eRental_total_fare'] = $rentalFareDataArr['eRental_total_fare'];
            $Fare_data[0]['eRental_total_fare_value'] = $rentalFareDataArr['eRental_total_fare_value'];
        }

        $TotalGenratedFare = $Generated_Fare;
        $Fare_data[0]['fMinFareDiff'] = $fMinFareDiff;
        $Fare_data[0]['fTax1'] = $fTax1;
        $Fare_data[0]['fTax2'] = $fTax2;
        $Fare_data[0]['fSurgePriceDiff'] = $fSurgePriceDiff;
        $Fare_data[0]['TotalGenratedFare'] = $generalobj->setTwoDecimalPoint($TotalGenratedFare / $priceRatio);
        $Fare_data[0]['iFare_Ori'] = $generalobj->setTwoDecimalPoint($total_fare / $priceRatio);

        //added by SP for fly stations on 20-08-2019 start
        /* if($eFly=='Yes') {
          $Fare_data[0]['iBaseFare_AMT'] = $iBaseFare_Ori;
          } else {
          $Fare_data[0]['iBaseFare_AMT'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare_Ori;
          } */
        //added by SP for fly stations on 20-08-2019 end

        $Fare_data[0]['iBaseFare_AMT'] = ($Fare_data[0]['eFareType'] == 'Fixed') ? 0 : $iBaseFare_Ori;
        $Fare_data[0]['FareOfMinutes_Ori'] = $Minute_Fare_Ori;
        $Fare_data[0]['FareOfDistance_Ori'] = $Distance_Fare_Ori;
        $Fare_data[0]['fCommision_AMT'] = $Commision_Fare_Ori;
        $Fare_data[0]['fSurgePriceDiff_Ori'] = $generalobj->setTwoDecimalPoint($fSurgePriceDiff / $priceRatio);
        $Fare_data[0]['fTax1_Ori'] = $generalobj->setTwoDecimalPoint($fTax1 / $priceRatio);
        $Fare_data[0]['fTax2_Ori'] = $generalobj->setTwoDecimalPoint($fTax2 / $priceRatio);
        $Fare_data[0]['fTax1Percentage'] = $TaxArr['fTax1'];
        $Fare_data[0]['fTax2Percentage'] = $TaxArr['fTax2'];
        $Fare_data[0]['fMinFareDiff_Ori'] = $generalobj->setTwoDecimalPoint($fMinFareDiff / $priceRatio);
        $Fare_data[0]['fDiscount_Ori'] = ($Fare_data[0]['fDiscount_Ori'] > $Fare_data[0]['TotalGenratedFare']) ? $Fare_data[0]['TotalGenratedFare'] : $Fare_data[0]['fDiscount_Ori'];
        // $Fare_data[0]['iBaseFare'] = $vSymbol . " " . number_format($Fare_data[0]['iBaseFare'], 2);
        // $Fare_data[0]['fPricePerMin'] = $vSymbol . " " . number_format(round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1), 2);
        // $Fare_data[0]['fPricePerKM'] = $vSymbol . " " . number_format(round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1), 2);
        // $Fare_data[0]['fCommision'] = $vSymbol . " " . number_format(round($Fare_data[0]['fCommision'] * $priceRatio, 1), 2);


        $Fare_data[0]['iBaseFare'] = $generalobj->formateNumAsPerCurrency($Fare_data[0]['iBaseFare'], $vCurrencyPassenger);

        $fPricePerMinRound = round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1);
        $Fare_data[0]['fPricePerMin'] = $generalobj->formateNumAsPerCurrency($fPricePerMinRound, $vCurrencyPassenger);
        $fPricePerKMRound = round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1);
        $Fare_data[0]['fPricePerKM'] = $generalobj->formateNumAsPerCurrency($fPricePerKMRound, $vCurrencyPassenger);
        $fCommisionRound = round($Fare_data[0]['fCommision'] * $priceRatio, 1);
        $Fare_data[0]['fCommision'] = $generalobj->formateNumAsPerCurrency($fCommisionRound, $vCurrencyPassenger);

        //Commented By HJ On 07-08-2019 For Merged Query For Get Vehicle Type Data Start
        //$vVehicleType = get_value('vehicle_type', "vVehicleType_" . $userlangcode, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        //$vVehicleTypeLogo = get_value('vehicle_type', "vLogo", 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        //$iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        //$vVehicleFare = get_value('vehicle_type', 'fFixedFare', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
        //Commented By HJ On 07-08-2019 For Merged Query For Get Vehicle Type Data End
        //Added By HJ On 07-08-2019 For Merged Query For Get Vehicle Type Data Start
        //echo $vVehicleType."===".$vVehicleTypeLogo."==".$iVehicleCategoryId."==".$vVehicleFare."<br>";
        $getVehicleData = $obj->MySQLSelect("SELECT vVehicleType_" . $userlangcode . ",vLogo,iVehicleCategoryId,fFixedFare FROM vehicle_type WHERE iVehicleTypeId='" . $vehicleTypeID . "'");
        //echo "<pre>";print_r($getVehicleData);die;
        $vVehicleFare = $iVehicleCategoryId = 0;
        $vVehicleTypeLogo = $vVehicleType = "";
        if (count($getVehicleData) > 0) {
            $vVehicleType = $getVehicleData[0]['vVehicleType_' . $userlangcode];
            $vVehicleTypeLogo = $getVehicleData[0]['vLogo'];
            $iVehicleCategoryId = $getVehicleData[0]['iVehicleCategoryId'];
            $vVehicleFare = $getVehicleData[0]['fFixedFare'];
        }
        //Added By HJ On 07-08-2019 For Merged Query For Get Vehicle Type Data End
        $vVehicleCategoryData = get_value($sql_vehicle_category_table_name, 'vLogo,vCategory_' . $userlangcode . ' as vCategory', 'iVehicleCategoryId', $iVehicleCategoryId);
        $Fare_data[0]['vVehicleCategory'] = $vVehicleCategoryData[0]['vCategory'];
        $eType = $Fare_data[0]['eFareType'];
        $Fare_data_amounts = $Fare_data;
        $tripFareDetailsArr = array();

        //added by SP for rounding off currency wise on 26-8-2019 start
        //if($eUserType == "Driver"){
        //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_driver as rd LEFT JOIN country as co ON rd.vCountry = co.vCountryCode  WHERE rd.iDriverId = '" . $iUserId . "'";
        //    $countryData = $obj->MySQLSelect($sqlp);
        //    $vCountry = $countryData[0]['vCountryCode'];
        //}else{
        //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_user as ru LEFT JOIN country as co ON ru.vCountry = co.vCountryCode WHERE ru.iUserId = '" . $iUserId . "'";
        //    $countryData = $obj->MySQLSelect($sqlp);
        //    $vCountry = $countryData[0]['vCountryCode'];
        //}
        if ($eUserType == "Driver") {
            $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iUserId . "'";
            $currData = $obj->MySQLSelect($sqlp);
            $vCurrency = $currData[0]['vName'];
        } else {
            $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iUserId . "'";
            $currData = $obj->MySQLSelect($sqlp);
            $vCurrency = $currData[0]['vName'];
        }

        if (empty($iUserId)) { // for manual booking fare estimation
            $vCurrency = $vCurrencyPassenger;
            $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM currency AS cu WHERE cu.vName = '" . $vCurrency . "'";
            $currData = $obj->MySQLSelect($sqlp);
        }
        $eRoundingOffEnableFlag = "No";
        //$currData[0]['eRoundingOffEnable'] = "Yes";
        if ($currData[0]['eRoundingOffEnable'] == "Yes") {

            $roundingOffTotal_fare_amountArr = getRoundingOffAmount($Fare_data[0]['total_fare_amount'], $vCurrency);
            //$returnArr['roundingOffAmountArr'] = $roundingOffTotal_fare_amount;
            $roundingOffTotal_fare_amount = $roundingOffTotal_fare_amountArr['finalFareValue'];
            //$Fare_data[0]['total_fare'] = $roundingOffTotal_fare_amount;
            /* if ($Fare_data[0]['eFareType'] == "Regular") { 
              $Fare_data[0]['total_fare'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount);
              } */
            if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                $roundingMethod = "";
            } else {
                $roundingMethod = "-";
            }
            if ($Fare_data[0]['eFareType'] == "Regular") {
                //$Fare_data[0]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
                //$Fare_data[0]['total_fare'] = $vSymbol . " " .$generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount); //commented bc in subtotal after rounding amt shown
            }
            //$Fare_data[0]['total_rounding_fare_amount'] = $vSymbol . " " .$generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount / $priceRatio);
            //$Fare_data[0]['rounding_diff'] = $roundingMethod.' '.$vSymbol . " " .$generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue'] / $priceRatio);
            // $Fare_data[0]['total_rounding_fare_amount'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount);
            // $Fare_data[0]['rounding_diff'] = $roundingMethod . ' ' . $vSymbol . " " . $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue']);

            $Fare_data[0]['total_rounding_fare_amount'] = $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amount, $vCurrencyPassenger);

            $Fare_data[0]['rounding_diff'] = $roundingMethod . ' ' . $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amountArr['differenceValue'], $vCurrencyPassenger);
            $eRoundingOffEnableFlag = "Yes";
        }

        //added by SP for rounding off currency wise on 26-8-2019 end

        if ($eFlatTrip == "Yes") {
            $i = 0;
            $displayfare = round($fFlatTripPrice * $priceRatio, 2);
            // $displayfare = $vSymbol . " " . number_format($displayfare, 2);
            $displayfare = $generalobj->formateNumAsPerCurrency($displayfare, $vCurrencyPassenger);
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_FLAT_TRIP_FARE_TXT']] = $displayfare;
            $i++;
            if ($fSurgePriceDiff > 0) {

                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $generalobj->formateNumAsPerCurrency($fSurgePriceDiff, $vCurrencyPassenger);
                $i++;
            }

            // airport surge
            if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
                if ($fAirportPickupSurgeAmount > 0) {
                    // $tripFareDetailsArr[$i][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $pickupSurgePriceFactor] = $vSymbol . " " . formatNum($fAirportPickupSurgeAmount);

                    $tripFareDetailsArr[$i][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $pickupSurgePriceFactor] = $generalobj->formateNumAsPerCurrency($fAirportPickupSurgeAmount, $vCurrencyPassenger);
                    $i++;
                }
                if ($fAirportDropoffSurgeAmount > 0) {
                    // $tripFareDetailsArr[$i][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $dropoffSurgePriceFactor] = $vSymbol . " " . formatNum($fAirportDropoffSurgeAmount);
                    $tripFareDetailsArr[$i][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $dropoffSurgePriceFactor] = $generalobj->formateNumAsPerCurrency($fAirportDropoffSurgeAmount, $vCurrencyPassenger);
                    $i++;
                }
            }

            // airport surge
            // add for kiosk
            if ($fTax1 > 0) {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fTax1 . " % "] = $Fare_data[0]['fTax1_Display'];
                $i++;
            }
            if ($fTax2 > 0) {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fTax2 . " % "] = $Fare_data[0]['fTax2_Display'];
                $i++;
            }
            if ($fHotelCommision > 0) {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_HOTEL_SERVICE_CHARGE']] = $generalobj->formateNumAsPerCurrency($fHotelCommision, $vCurrencyPassenger);
                // $tripFareDetailsArr[$i][$languageLabelsArr['LBL_HOTEL_SERVICE_CHARGE']] = $vSymbol . $fHotelCommision;
                $i++;
            }
            if ($discountValue > 0) {

                // $farediscount = $vSymbol . " " . formatNum($Fare_data[0]['fDiscount']);
                $farediscount = $generalobj->formateNumAsPerCurrency($Fare_data[0]['fDiscount'], $vCurrencyPassenger);
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_DISCOUNT']] = "- " . $farediscount;
                $i++;
            }

            $tripFareDetailsArr[$i++]['eDisplaySeperator'] = "Yes";
            $tripFareDetailsArr[$i++][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $Fare_data[0]['total_fare'];
            if ($eRoundingOffEnableFlag == "Yes") {
                $tripFareDetailsArr[$i++][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = $Fare_data[0]['rounding_diff']; //added by SP for rounding off currency wise on 26-8-2019
                $tripFareDetailsArr[$i++]['eDisplaySeperator'] = "Yes";
                $tripFareDetailsArr[$i++][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = $Fare_data[0]['total_rounding_fare_amount']; //added by SP for rounding off currency wise on 09-11-2019
            }
            if ($eRoundingOffEnableFlag == "Yes") {
                $tripTotalAmount = $Fare_data[0]['total_rounding_fare_amount'];
                $tripFareDetailsArr[$i]['total_fare_amount'] = $generalobj->formateNumAsPerCurrency($Fare_data[0]['total_rounding_fare_amount'], $vCurrencyPassenger);
            } else {
                $tripTotalAmount = $Fare_data[0]['total_fare_amount'];
                //$tripFareDetailsArr[$i + 1]['total_fare_amount'] = $generalobj->setTwoDecimalPoint($Fare_data[0]['total_fare_amount']);   //commented bc after format number it set to 2 decimal point then it converted with value b4 comma 
                $tripFareDetailsArr[$i]['total_fare_amount'] = $generalobj->formateNumAsPerCurrency($Fare_data[0]['total_fare_amount'], $vCurrencyPassenger);
            }
            //$tripFareDetailsArr[$i]['total_fare_amount'] = $Fare_data[0]['total_fare_amount'];
            $Fare_data = $tripFareDetailsArr;
        } else {

            $i = 0;
            $countUfx = 0;
            // echo $eType;exit;
            if ($eType == "UberX") {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $Fare_data[0]['vVehicleCategory'] . "-" . $vVehicleType;
                $countUfx = 1;
            } else if ($RideType == "Multi-Delivery") {
                $tripFareDetailsArr[$i][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $vVehicleType;
                $countUfx = 1;
            }

            if ($eType == "Regular") {

                // $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vSymbol . " " . formatNum($iBaseFare);
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $generalobj->formateNumAsPerCurrency($iBaseFare, $vCurrencyPassenger);

                if ($countUfx == 1) {
                    $i++;
                }
                if ($eUnit == "Miles") {
                    $tripDistanceDisplay = $tripDistance * 0.621371;
                    $tripDistanceDisplay = round($tripDistanceDisplay, 2);
                    //$DisplayDistanceTxt = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
                    $LBL_MILE_DISTANCE_TXT = ($tripDistanceDisplay > 1) ? $languageLabelsArr['LBL_MILE_DISTANCE_TXT'] : $languageLabelsArr['LBL_ONE_MILE_TXT'];
                    $DisplayDistanceTxt = $LBL_MILE_DISTANCE_TXT;
                } else {
                    $tripDistanceDisplay = $tripDistance;
                    //$DisplayDistanceTxt = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
                    $LBL_KM_DISTANCE_TXT = ($tripDistanceDisplay > 1) ? $languageLabelsArr['LBL_DISPLAY_KMS'] : $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
                    $DisplayDistanceTxt = $LBL_KM_DISTANCE_TXT;
                }

                $tripDistanceDisplay = formatNum($tripDistanceDisplay);
                if ($isDestinationAdded == "Yes") {
                    // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $tripDistanceDisplay . " " . $DisplayDistanceTxt . ")"] = $vSymbol . " " . formatNum($Fare_data[0]['FareOfDistance']);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $tripDistanceDisplay . " " . $DisplayDistanceTxt . ")"] = $generalobj->formateNumAsPerCurrency($Fare_data[0]['FareOfDistance'], $vCurrencyPassenger);
                } else {
                    $priceperkm = getVehiclePrice_ByUSerCountry($iUserId, $fPricePerKM);

                    // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT']] = $vSymbol . " " . formatNum(round($priceperkm * $priceRatio, 2)) . "/" . strtolower($DisplayDistanceTxt);
                    // issue for format currency
                    //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT']] = $generalobj->formateNumAsPerCurrency((formatNum(round($priceperkm * $priceRatio, 2)) . "/" . strtolower($DisplayDistanceTxt)),'');
                    $priceperkmRound = round($priceperkm * $priceRatio, 2);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT']] = $generalobj->formateNumAsPerCurrency($priceperkmRound, $vCurrencyPassenger) . "/" . strtolower($DisplayDistanceTxt);
                }

                $i++;

                //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $totalTimeInMinutes_trip . ")"] = $vSymbol . formatNum($Fare_data[0]['FareOfMinutes']);
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
                    // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $tripDurationDisplay . ")"] = $vSymbol . " " . formatNum($Fare_data[0]['FareOfMinutes']);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $tripDurationDisplay . ")"] = $generalobj->formateNumAsPerCurrency($Fare_data[0]['FareOfMinutes'], $vCurrencyPassenger);
                } else {
                    // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT']] = $vSymbol . " " . formatNum(round($fPricePerMin * $priceRatio, 2)) . "/" . $languageLabelsArr['LBL_MIN_SMALL_TXT'];
                    $fPricePerMinRound = round($fPricePerMin * $priceRatio, 2);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TIME_TXT']] = $generalobj->formateNumAsPerCurrency($fPricePerMinRound, $vCurrencyPassenger) . "/" . $languageLabelsArr['LBL_MIN_SMALL_TXT'];
                }
                $i++;
            } else if ($eType == "Fixed") {

                //added by SP for fly stations on 20-08-2019 start
                if ($eFly == 'Yes') {
                    $vVehicleFare = getFareForFlyVehicles($vehicleTypeID, $iFromStationId, $iToStationId) * $priceRatio;
                }
                //added by SP for fly stations on 20-08-2019 end
                // $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = ($Fare_data[0]['iQty'] > 1) ? $Fare_data[0]['iQty'] . ' X ' . $vSymbol . " " . formatNum($vVehicleFare) : $vSymbol . " " . formatNum($vVehicleFare);

                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = ($Fare_data[0]['iQty'] > 1) ? $Fare_data[0]['iQty'] . ' X ' . $generalobj->formateNumAsPerCurrency($vVehicleFare, $vCurrencyPassenger) : $generalobj->formateNumAsPerCurrency($vVehicleFare, $vCurrencyPassenger);
                if ($countUfx == 1) {
                    $i++;
                }

                //added by SP for fly stations on 20-08-2019 start
                if ($eFly == 'Yes') {
                    $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format(round($total_fare, 2), 2);
                    $Fare_data[0]['total_fare_amount'] = $tripTotalAmount = number_format(round($total_fare, 2), 2);
                } else {
                    $total_fare = $vVehicleFare + $Fare_data[0]['fVisitFee'] - $Fare_data[0]['fDiscount_fixed'];
                    $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format(round($total_fare * $priceRatio, 2), 2);
                    $Fare_data[0]['total_fare_amount'] = $tripTotalAmount = number_format(round($total_fare * $priceRatio, 2), 2);
                }
                //added by SP for fly stations on 20-08-2019 end
                //$total_fare = $vVehicleFare + $Fare_data[0]['fVisitFee'] - $Fare_data[0]['fDiscount_fixed'];
                // $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format(round($total_fare * $priceRatio, 1), 2);
                // $Fare_data[0]['total_fare_amount'] = number_format(round($total_fare * $priceRatio, 1), 2);
                $totalfareround = round($total_fare * $priceRatio, 1);
                $Fare_data[0]['total_fare'] = $generalobj->formateNumAsPerCurrency($totalfareround, $vCurrencyPassenger);
                $Fare_data[0]['total_fare_amount'] = $tripTotalAmount = (number_format(round($total_fare * $priceRatio, 1), 2)); //here removed formateNumAsPerCurrency bc at end it already used so error when two time used..
            } else if ($eType == "Hourly") {
                // $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $totalTimeInMinutes_trip . ")"] = $vSymbol . " " . $Fare_data[0]['FareOfMinutes'];
                $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $totalTimeInMinutes_trip . ")"] = $generalobj->formateNumAsPerCurrency($Fare_data[0]['FareOfMinutes'], $vCurrencyPassenger);
                if ($countUfx == 1) {
                    $i++;
                }
            }

            $fVisitFee = $Fare_data[0]['fVisitFee'];
            if ($fVisitFee > 0) {
                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_VISIT_FEE']] = $vSymbol . " " . $fVisitFee;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_VISIT_FEE']] = $generalobj->formateNumAsPerCurrency($fVisitFee, $vCurrencyPassenger);
                $i++;
            }
            if ($fMaterialFee > 0) {
                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MATERIAL_FEE']] = $vSymbol . " " . $fMaterialFee;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MATERIAL_FEE']] = $generalobj->formateNumAsPerCurrency($fMaterialFee, $vCurrencyPassenger);
                $i++;
            }
            if ($fMiscFee > 0) {
                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MISC_FEE']] = $vSymbol . " " . $fMiscFee;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_MISC_FEE']] = $generalobj->formateNumAsPerCurrency($fMiscFee, $vCurrencyPassenger);
                $i++;
            }
            if ($fSurgePriceDiff > 0) {
                if ($isDestinationAdded == "Yes") {
                    //$normalfare = $total_fare-$fSurgePriceDiff+$discountValue-$fTaxAmount1-$fTaxAmount2;
                    //$normalfare = formatNum($normalfare * $priceRatio);
                    $tripFareDetailsArr[$i + 1]['eDisplaySeperator'] = "Yes";
                    $i++;
                    $normalfare = $iBaseFare + $Distance_Fare + $Minute_Fare;
                    $normalfare = formatNum($normalfare);
                    //added by SP for fly stations on 20-08-2019 start
                    if ($eFly != 'Yes') {
                        // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = $vSymbol . " " . $normalfare;
                        $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_NORMAL_FARE']] = $generalobj->formateNumAsPerCurrency($normalfare, $vCurrencyPassenger);
                        $i++;
                    }
                    //added by SP for fly stations on 20-08-2019 end
                }
                if ($isDestinationAdded == "Yes") {
                    //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $vSymbol." ".formatNum($fSurgePriceDiff * $priceRatio);
                    // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $vSymbol . " " . formatNum($fSurgePriceDiff);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = $generalobj->formateNumAsPerCurrency($fSurgePriceDiff, $vCurrencyPassenger);
                    $i++;
                } else {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SURGE']] = $SurgePriceFactor . " x";
                    $i++;
                }
            }

            // airport surge
            if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
                if ($fAirportPickupSurgeAmount > 0) {
                    // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $pickupSurgePriceFactor] = $vSymbol . " " . formatNum($fAirportPickupSurgeAmount);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $pickupSurgePriceFactor] = $generalobj->formateNumAsPerCurrency($fAirportPickupSurgeAmount, $vCurrencyPassenger);
                    $i++;
                }
                // dropoff
                if ($fAirportDropoffSurgeAmount > 0) {
                    // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $dropoffSurgePriceFactor] = $vSymbol . " " . formatNum($fAirportDropoffSurgeAmount);
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $dropoffSurgePriceFactor] = $generalobj->formateNumAsPerCurrency($fAirportDropoffSurgeAmount, $vCurrencyPassenger);
                    $i++;
                }
            }
            // airport surge

            if ($fMinFareDiff > 0 && $isDestinationAdded == "Yes") {
                //$minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[$i + 1][$vSymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = $vSymbol . " " . formatNum($fMinFareDiff);
                $Fare_data[0]['TotalMinFare'] = $minimamfare;
                $i++;
            }
            if ($fDriverDiscount > 0) {

                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = "- " . $vSymbol . " " . $fDriverDiscount;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = "- " . $generalobj->formateNumAsPerCurrency($fDriverDiscount, $vCurrencyPassenger);
                $i++;
            }
            if ($discountValue > 0) {

                //$farediscount = $vSymbol." ".number_format(round($Fare_data[0]['fDiscount'] * $priceRatio,1),2);
                // $farediscount = $vSymbol . " " . formatNum($Fare_data[0]['fDiscount']);
                $farediscount = $generalobj->formateNumAsPerCurrency($Fare_data[0]['fDiscount'], $vCurrencyPassenger);
                //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISCOUNT']] = "- " . $vSymbol . $Fare_data[0]['fDiscount'];
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISCOUNT']] = "- " . $farediscount;
                $i++;
            }
            if ($fTax1 > 0) {
                if ($isDestinationAdded == "Yes") {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fTax1 . " % "] = $Fare_data[0]['fTax1_Display'];
                    $i++;
                } else {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX1_TXT']] = " @ " . $fTax1 . " % ";
                    $i++;
                }
            }
            if ($fTax2 > 0) {
                if ($isDestinationAdded == "Yes") {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fTax2 . " % "] = $Fare_data[0]['fTax2_Display'];
                    $i++;
                } else {
                    $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_TAX2_TXT']] = " @ " . $fTax2 . " % ";
                    $i++;
                }
            }

            if ($fHotelCommision > 0) {
                // $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_HOTEL_SERVICE_CHARGE']] = $vSymbol . $fHotelCommision;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_HOTEL_SERVICE_CHARGE']] = $generalobj->formateNumAsPerCurrency($fHotelCommision, $vCurrencyPassenger);
                $i++;
            }

            if ($Fare_data[0]['eFareType'] == "Regular") {
                //$Fare_data[0]['total_fare'] = $vSymbol." ".number_format($total_fare,2);
                // $Fare_data[0]['total_fare'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($total_fare);
                $Fare_data[0]['total_fare'] = $generalobj->formateNumAsPerCurrency($total_fare, $vCurrencyPassenger);
            }
            if ($isDestinationAdded == "Yes") {
                $tripFareDetailsArr[$i + 1]['eDisplaySeperator'] = "Yes";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $Fare_data[0]['total_fare'];
                $i++;
            }

            //added by SP for rounding off currency wise on 26-8-2019 start
            /* $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = $Fare_data[0]['rounding_diff'];
              $i++;
              $tripFareDetailsArr[$i + 1]['eDisplaySeperator'] = "Yes";
              $i++;
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = $Fare_data[0]['total_rounding_fare_amount'];
              $i++; */

            //if($eUserType == "Driver"){
            //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_driver as rd LEFT JOIN country as co ON rd.vCountry = co.vCountryCode  WHERE rd.iDriverId = '" . $iUserId . "'";
            //    $countryData = $obj->MySQLSelect($sqlp);
            //    $vCountry = $countryData[0]['vCountryCode'];
            //}else{
            //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_user as ru LEFT JOIN country as co ON ru.vCountry = co.vCountryCode WHERE ru.iUserId = '" . $iUserId . "'";
            //    $countryData = $obj->MySQLSelect($sqlp);
            //    $vCountry = $countryData[0]['vCountryCode'];
            //}
            if ($eUserType == "Driver") {
                $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iUserId . "'";
                $currData = $obj->MySQLSelect($sqlp);
                $vCurrency = $currData[0]['vName'];
            } else {
                $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iUserId . "'";
                $currData = $obj->MySQLSelect($sqlp);
                $vCurrency = $currData[0]['vName'];
            }

            if (empty($iUserId)) { // for manual booking fare estimation
                $vCurrency = $vCurrencyPassenger;
                $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM currency AS cu WHERE cu.vName = '" . $vCurrency . "'";
                $currData = $obj->MySQLSelect($sqlp);
            }
            if ($currData[0]['eRoundingOffEnable'] == "Yes") {
                //$roundingOffTotal_fare_amountArr = getRoundingOffAmount($Fare_data[0]['total_fare_amount'],$vCurrency);
                $roundingOffTotal_fare_amountArr = getRoundingOffAmount($total_fare * $priceRatio, $vCurrency);
                //print_R($roundingOffTotal_fare_amountArr);
                //$returnArr['roundingOffAmountArr'] = $roundingOffTotal_fare_amount;
                $roundingOffTotal_fare_amount = $roundingOffTotal_fare_amountArr['finalFareValue'];
                //$Fare_data[0]['total_fare'] = $roundingOffTotal_fare_amount;
                /* if ($Fare_data[0]['eFareType'] == "Regular") { 
                  $Fare_data[0]['total_fare'] = $vSymbol . " " . $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount);
                  } */
                if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                    $roundingMethod = "";
                } else {
                    $roundingMethod = "-";
                }
                if ($Fare_data[0]['eFareType'] == "Regular") {

                    // $Fare_data[0]['total_fare'] = $vSymbol . " " . number_format($total_fare, 2);
                    $Fare_data[0]['total_fare'] = $generalobj->formateNumAsPerCurrency($total_fare, $vCurrencyPassenger);
                    //$Fare_data[0]['total_fare'] = $vSymbol . " " .$generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount);
                }
                //$Fare_data[0]['total_rounding_fare_amount'] = $vSymbol . " " .$generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount / $priceRatio);
                //$Fare_data[0]['rounding_diff'] = $roundingMethod.' '.$vSymbol . " " .$generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue'] / $priceRatio);
                // $Fare_data[0]['total_rounding_fare_amount'] = $vSymbol . " " . $generalobj->formatNum($roundingOffTotal_fare_amount);
                // $Fare_data[0]['rounding_diff'] = $roundingMethod . ' ' . $vSymbol . " " . $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue']);


                $Fare_data[0]['total_rounding_fare_amount'] = $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amount, $vCurrencyPassenger);
                $Fare_data[0]['rounding_diff'] = $roundingMethod . ' ' . $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amountArr['differenceValue'], $vCurrencyPassenger);

                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = $Fare_data[0]['rounding_diff'];
                $i++;
                $tripFareDetailsArr[$i + 1]['eDisplaySeperator'] = "Yes";
                $i++;
                $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = $Fare_data[0]['total_rounding_fare_amount'];
                $i++;
            }

            //$Fare_data = array_merge($Fare_data[0], $tripFareDetailsArr);
            //$tripFareDetailsArr[$i + 1]['total_fare_amount'] = $generalobj->setTwoDecimalPoint($Fare_data[0]['total_fare_amount']);
            //echo "<pre>";print_r($currData[0]['eRoundingOffEnable']);die;
            if ($currData[0]['eRoundingOffEnable'] == "Yes") {
                $tripTotalAmount = $roundingOffTotal_fare_amount;
                $tripFareDetailsArr[$i + 1]['total_fare_amount'] = $generalobj->formateNumAsPerCurrency($roundingOffTotal_fare_amount, $vCurrencyPassenger);
            } else {
                $tripTotalAmount = $Fare_data[0]['total_fare_amount'];
                //$tripFareDetailsArr[$i + 1]['total_fare_amount'] = $generalobj->setTwoDecimalPoint($Fare_data[0]['total_fare_amount']);   //commented bc after format number it set to 2 decimal point then it converted with value b4 comma 
                $tripFareDetailsArr[$i + 1]['total_fare_amount'] = $generalobj->formateNumAsPerCurrency($Fare_data[0]['total_fare_amount'], $vCurrencyPassenger);
            }
            //print_R($tripFareDetailsArr); exit;
            //print_R($tripFareDetailsArr); exit;
            //$tripFareDetailsArr[$i + 1]['total_fare_amount'] = $generalobj->setTwoDecimalPoint($Fare_data[0]['total_fare_amount']);
            //$tripFareDetailsArr[$i + 1]['total_fare_amount'] = $Fare_data[0]['total_rounding_fare_amount'];
            //added by SP for rounding off currency wise on 26-8-2019 end
            $Fare_data = $tripFareDetailsArr;
        }
    }
    //$Fare_data['fareamt'] = 35;
    //echo "<pre>";print_r($Fare_data);die;
    if ($DisplayMultiDeliveryFare == "Yes") {
        return $Fare_data_amounts;
    } else {
        //print_R($Fare_data); exit;
        //Added By HJ On 12-03-2020 For Get Origional Fare Amount For Use In app As Per Discuss with KS Sir Start
        if (strtoupper($IS_RETURN_ARR_WITH_ORIG_AMT) == "YES") {
            $retrunDataArr = array();
            $retrunDataArr['fare_data'] = $Fare_data;
            $retrunDataArr['org_fare_amount'] = $tripTotalAmount;
            return $retrunDataArr;
        }
        //Added By HJ On 12-03-2020 For Get Origional Fare Amount For Use In app As Per Discuss with KS Sir End
        return $Fare_data;
    }
}

function getLanguageLabelsArr($lCode = '', $directValue = "", $iServiceId = "") {
    global $obj, $APP_TYPE, $vSystemDefaultLangCode;
    /* find default language of website set by admin */
    //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
    if (empty($vSystemDefaultLangCode)) {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);
        $vSystemDefaultLangCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }
    //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
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

function sendApplePushNotification($PassengerToDriver = 0, $deviceTokens, $message, $alertMsg, $filterMsg, $fromDepart = '') {

    //global $generalobj, $obj, $IPHONE_PEM_FILE_PASSPHRASE,$APP_MODE,$ENABLE_PUBNUB, $PARTNER_APP_IPHONE_PEM_FILE_NAME, $PASSENGER_APP_IPHONE_PEM_FILE_NAME;
    global $generalobj, $obj, $APP_MODE_TEMP_WEB;
    $sql = "select vValue,vName from configurations where vName in('IPHONE_PEM_FILE_PASSPHRASE','APP_MODE','ENABLE_PUBNUB','PARTNER_APP_IPHONE_PEM_FILE_NAME','PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PARTNER_APP_IPHONE_PEM_FILE_NAME')";
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
    //$APP_MODE = $APP_MODE;
    //$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
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
        //$name = $generalobj->getConfigurations("configurations", $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME");
        $name1 = $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else if ($PassengerToDriver == 2) {
        //$name = $generalobj->getConfigurations("configurations", $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME");
        $name1 = $prefix . "KISOK_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else {
        //$name = $generalobj->getConfigurations("configurations", $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME");
        $name1 = $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    }
    $ctx = stream_context_create();
    if ($fromDepart == 'admin') {
        $name = '../' . $name;
    }
    stream_context_set_option($ctx, 'ssl', 'local_cert', $name);
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    $fp = stream_socket_client($url_apns, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
    //echo "deviceTokens => <pre>";
    //print_r($deviceTokens);
    //echo "<pre>"; print_r($fp); die;
    if (!$fp) {

        if ($ENABLE_PUBNUB == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_SERVER_COMM_ERROR";
            $returnArr['ERROR'] = $err . $errstr . " " . PHP_EOL;
            setDataResponse($returnArr);
            //exit("Failed to connect: $err $errstr" . PHP_EOL);
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

function getOnlineDriverArr($sourceLat, $sourceLon, $address_data = array(), $DropOff = "No", $From_Autoassign = "No", $Check_Driver_UFX = "No", $Check_Date_Time = "", $destLat = "", $destLon = "", $eType = "Ride", $eFemaleDriverRequestWeb = '', $SelectedDriverId = "") {
    global $generalobj, $obj, $RESTRICTION_KM_NEAREST_TAXI, $LIST_DRIVER_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $COMMISION_DEDUCT_ENABLE, $WALLET_MIN_BALANCE, $RESTRICTION_KM_NEAREST_TAXI, $APP_TYPE, $vTimeZone, $PROVIDER_AVAIL_LOC_CUSTOMIZE, $isFromHotelPanel, $FEMALE_RIDE_REQ_ENABLE, $intervalmins, $isFromAdminPanel, $iCompanyId, $SERVICE_PROVIDER_FLOW, $_REQUEST, $ENABLE_FAVORITE_DRIVER_MODULE, $SYSTEM_PAYMENT_FLOW;
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    //ini_set('display_errors', 1);
//error_reporting(E_ALL);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $LIST_DRIVER_LIMIT_BY_DISTANCE = ($From_Autoassign == "Yes" || $isFromHotelPanel == 'Yes' || $isFromAdminPanel == 'Yes') ? $RESTRICTION_KM_NEAREST_TAXI : $LIST_DRIVER_LIMIT_BY_DISTANCE;
    $vWorkLocationRadius = $RESTRICTION_KM_NEAREST_TAXI;
    /* $LIST_DRIVER_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", $From_Autoassign =="Yes" ?"RESTRICTION_KM_NEAREST_TAXI" : "LIST_DRIVER_LIMIT_BY_DISTANCE");     $DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");         $COMMISION_DEDUCT_ENABLE=$generalobj->getConfigurations("configurations","COMMISION_DEDUCT_ENABLE");        $WALLET_MIN_BALANCE=$generalobj->getConfigurations("configurations","WALLET_MIN_BALANCE");
      $APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE"); */
    //if($APP_TYPE == "UberX"){
    if ($eType == "UberX" && $PROVIDER_AVAIL_LOC_CUSTOMIZE == "Yes") {
        //$vLatitude = "COALESCE(NULLIF(vWorkLocationLatitude,''), vLatitude)"; //$vLongitude = "COALESCE(NULLIF(vWorkLocationLongitude,''), vLongitude)";
        //$vLatitude = "IF(register_driver.eSelectWorkLocation = 'Fixed',COALESCE(NULLIF(vWorkLocationLatitude,''), vLatitude),vLatitude)"; //$vLongitude = "IF(register_driver.eSelectWorkLocation = 'Fixed',COALESCE(NULLIF(vWorkLocationLongitude,''), vLongitude),vLongitude)";
        $vLatitude = "IF(register_driver.eSelectWorkLocation = 'Fixed',COALESCE(NULLIF(vWorkLocationLatitude,''), vLatitude),vLatitude)";
        $vLongitude = "IF(register_driver.eSelectWorkLocation = 'Fixed',COALESCE(NULLIF(vWorkLocationLongitude,''), vLongitude),vLongitude)";
    } else {
        $vLatitude = 'vLatitude';
        $vLongitude = 'vLongitude';
    }
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tSwitchOnline" : "tLocationUpdateDate";
    $sourceLocationArr = array($sourceLat,$sourceLon);
    $destinationLocationArr = array($destLat,$destLon);
    $eFly = isset($_REQUEST["eFly"]) && !empty($_REQUEST["eFly"]) ? $_REQUEST['eFly'] : 'No';
    if ($DropOff == "No") {
        $address_data['CheckAddress'] = $address_data['PickUpAddress'];
        //$allowed_ans = checkRestrictedArea($address_data,"No");
        $source_array = $sourceLocationArr;
        //$allowed_ans = checkRestrictedAreaNew($source_array,"No");
        $allowed_ans = checkAllowedAreaNew($source_array, "No");
        //print_r($allowed_ans);die;
        $allowed_ans_drop = "Yes";
    } else {
        $address_data['CheckAddress'] = $address_data['PickUpAddress'];
        //$allowed_ans = checkRestrictedArea($address_data,"No");
        $source_array = $sourceLocationArr;
        //$allowed_ans = checkRestrictedAreaNew($source_array,"No");
        $allowed_ans = checkAllowedAreaNew($source_array, "No");
        $address_data['CheckAddress'] = $address_data['DropOffAddress'];
        //$allowed_ans_drop = checkRestrictedArea($address_data,"Yes");
        $dest_array = $destinationLocationArr;
        //$allowed_ans_drop = checkRestrictedAreaNew($dest_array,"Yes");
        $allowed_ans_drop = checkAllowedAreaNew($dest_array, "Yes");
    }
    $ssql_available = "";
    if ($Check_Driver_UFX == "No") {
        $ssql_available .= " AND vAvailability = 'Available' AND vTripStatus != 'Active' AND tLocationUpdateDate > '$str_date' ";
    }
    // for hotel panel web
    if ($isFromHotelPanel == 'Yes') {
        if ($FEMALE_RIDE_REQ_ENABLE == 'Yes') {
            $ssql_available .= " AND eFemaleOnlyReqAccept = 'No' ";
        }
    }
    if ($isFromAdminPanel == 'Yes') {
        if ($FEMALE_RIDE_REQ_ENABLE == 'Yes' && $eFemaleDriverRequestWeb == 'Yes') {
            //$ssql_available .= " AND eFemaleOnlyReqAccept = 'Yes' ";
            $ssql_available .= " AND eGender = 'Female' ";
        } else {
            //$ssql_available .= " AND eFemaleOnlyReqAccept = 'No' ";
        }

        if ($iCompanyId != '' AND $iCompanyId > 0) {
            $ssql_available .= " AND iCompanyId = '" . $iCompanyId . "' ";
        }
    }

    if ($eFly == 'Yes' || $eType != 'Ride') {
        $ssql_available .= " AND eDestinationMode != 'Yes'";
    }

    $webserviceType = isset($_REQUEST["type"]) ? $_REQUEST["type"] : '';
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    $ssql_fav_q = "";
    if (checkFavDriverModule()) {
        include_once('include/features/include_fav_driver.php');
        $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
        //echo $iUserId."<br>";
        if (!empty($iUserId)) {
            $ssql_fav_q = getFavSelectQueryToLoadCabs($eType, $iUserId);
        }
    }
    //echo $ssql_fav_q;die;
    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {

        $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") )
            * cos( radians( ROUND(" . $vLatitude . ",8) ) )
            * cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
            + sin( radians(" . $sourceLat . ") )
            * sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.* " . $ssql_fav_q . "  FROM `register_driver`
            WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active' AND eIsBlocked = 'No')
            HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " ORDER BY `register_driver`.`" . $param . "` ASC";

        $Data = $obj->MySQLSelect($sql);
        //echo "<pre>";print_r($Data);die;
        $newData = $driverStatusArr =$companyDataArr= array();
        $j = 0;
        //Added By HJ On 10-06-2019 For Get All Driver Status Start
        $getDriverVehicleData = $obj->MySQLSelect("SELECT iDriverVehicleId,eStatus FROM driver_vehicle");
        for ($a = 0; $a < count($getDriverVehicleData); $a++) {
            $driverStatusArr[$getDriverVehicleData[$a]['iDriverVehicleId']] = $getDriverVehicleData[$a]['eStatus'];
        }
        //Added By HJ On 10-06-2019 For Get All Driver Status End
        //Added By HJ On 20-06-2020 For Optimized company Table Query Start
        $getCompanyData = $obj->MySQLSelect("SELECT iServiceId,iCompanyId FROM company");
        for($j=0;$j<count($getCompanyData);$j++){
           $companyDataArr[$getCompanyData[$j]['iCompanyId']]= $getCompanyData[$j]['iServiceId'];  
        }
        //Added By HJ On 20-06-2020 For Optimized company Table Query End
        for ($i = 0; $i < count($Data); $i++) {
            //added on 25-3 bc store driver not getting req of manual booking as discussed with HJ
            if (isStoreDriverAvailable()) {
                //$getcomData = $obj->MySQLSelect("SELECT iServiceId FROM company WHERE iCompanyId = " . $Data[$i]['iCompanyId']);
                $companyServiceId = 0;
                if(isset($companyDataArr[$Data[$i]['iCompanyId']])){
                    $companyServiceId = $companyDataArr[$Data[$i]['iCompanyId']];
                }
                if ($companyServiceId > 0) {
                    continue;
                }
            }
            if ($FEMALE_RIDE_REQ_ENABLE == "No") {
                $Data[$i]['eFemaleOnlyReqAccept'] = "No";
            }
            if ((isset($driverStatusArr[$Data[$i]['iDriverVehicleId']]) && $driverStatusArr[$Data[$i]['iDriverVehicleId']] == "Active") || $eType == "UberX") {
                if ($Data[$i]['tProfileDescription'] != "") {
                    // mb_internal_encoding("UTF-8");
                    // $Data[$i]['tProfileDescription'] = substr($Data[$i]['tProfileDescription'], 0, 50);
                    $Data[$i]['tProfileDescription'] = mb_substr($Data[$i]['tProfileDescription'], 0, 50);
                }

                $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];
                if (!isset($Data[$i]['eFavDriver'])) {
                    $Data[$i]['eFavDriver'] = "No";
                }
                $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
                if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
                    $user_available_balance = $generalobj->get_user_available_balance($Data[$i]['iDriverId'], "Driver");

                    ///echo $WALLET_MIN_BALANCE."===".$user_available_balance;die;
                    if ($WALLET_MIN_BALANCE > $user_available_balance) {
                        $Data[$i]['ACCEPT_CASH_TRIPS'] = "No";
                        //Added BY HJ On 23-09-2019 As Per Discuss with BM and GP Mam Start
                        $checkCondition = 0;
                        if ($SelectedDriverId != "" && $SelectedDriverId == $Data[$i]['iDriverId']) {
                            $checkCondition = 1;
                        }
                        if (($webserviceType == "sendRequestToDrivers" || $webserviceType == "ScheduleARide") && $cashPayment == 'true' && $eType == "UberX" && $checkCondition == 1) {
                            $returnArr = array();
                            $returnArr['Action'] = "0"; // code is invalid
                            $returnArr["message"] = "LBL_CHECK_PROVIDER_MIN_WALLET_BALANCE_TXT";
                            setDataResponse($returnArr);
                        }
                        //Added BY HJ On 23-09-2019 As Per Discuss with BM and GP Mam End
                    }
                }
                //Added By HJ On 05-06-2019 FOr Solved Bug - 6597 As Per Discuss With KS Sir Start
                if ($Data[$i]['ACCEPT_CASH_TRIPS'] == "No" && ($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
                    continue;
                }
                //Added By HJ On 05-06-2019 FOr Solved Bug - 6597 As Per Discuss With KS Sir End
                //if($APP_TYPE == "UberX"){
                if ($eType == "UberX") {
                    $eUnit = getMemberCountryUnit($Data[$i]['iDriverId'], "Driver");
                    /* if($Data[$i]['vWorkLocationRadius'] == "" || $Data[$i]['vWorkLocationRadius'] == "0" || $Data[$i]['vWorkLocationRadius'] == 0){
                      $Data[$i]['vWorkLocationRadius'] = $vWorkLocationRadius;
                      }else{
                      if($eUnit == "Miles"){
                      $Data[$i]['vWorkLocationRadius'] = round($Data[$i]['vWorkLocationRadius'] * 1.60934,2);
                      }
                      } */
                    if ($Data[$i]['eSelectWorkLocation'] == "Fixed" && $Data[$i]['vWorkLocationLatitude'] != "" && $Data[$i]['vWorkLocationLongitude'] != "" && $PROVIDER_AVAIL_LOC_CUSTOMIZE == "Yes") {
                        $Data[$i]['vLatitude'] = $Data[$i]['vWorkLocationLatitude'];
                        $Data[$i]['vLongitude'] = $Data[$i]['vWorkLocationLongitude'];
                    }
                    $Data[$i]['PROVIDER_RADIUS'] = $Data[$i]['vWorkLocationRadius'];
                    $sqlcount = "SELECT count(iRatingId) as TotalReview FROM ratings_user_driver as r LEFT JOIN trips as t ON r.iTripId=t.iTripId WHERE r.eUserType='Passenger' And t.iActive = 'Finished' AND t.iDriverId =  '" . $Data[$i]['iDriverId'] . "'";
                    $dbcount = $obj->MySQLSelect($sqlcount);
                    $Data[$i]['PROVIDER_RATING_COUNT'] = $dbcount[0]['TotalReview'];
                }

                //echo $Check_Driver_UFX;die;
                if ($Check_Driver_UFX == "Yes" && $Check_Date_Time != "") {
                    //$currentdate = date("Y-m-d H:i:s");
                    //$Check_Date_Time = date("Y-m-d H:i:s");
                    $systemTimeZone = date_default_timezone_get();
                    $Booking_Date_Time = converToTz($Check_Date_Time, $systemTimeZone, $vTimeZone);
                    $Checkday = date('l', strtotime($Check_Date_Time));
                    $hours = date('H', strtotime($Check_Date_Time));
                    $hr1 = $hours;
                    if ($hours == "12" || $hours == "00") {
                        $hr1 = "12";
                        $hr2 = "01";
                    } else {
                        $hr2 = $hr1 + 1;
                        $hr1 = str_pad($hr1, 2, '0', STR_PAD_LEFT);
                        $hr2 = str_pad($hr2, 2, '0', STR_PAD_LEFT);
                    }
                    $CheckHour = $hr1 . "-" . $hr2;
                    $sql = "SELECT * from driver_manage_timing WHERE iDriverId ='" . $Data[$i]['iDriverId'] . "' AND vDay = '" . $Checkday . "' AND vAvailableTimes LIKE '%" . $CheckHour . "%'";
                    $availdriver = $obj->MySQLSelect($sql);
                    if (count($availdriver) > 0) {
                        $sql_book = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $Data[$i]['iDriverId'] . "' AND dBooking_date = '" . $Booking_Date_Time . "' AND eStatus IN('Assign','Accepted')";
                        $availdriverbooking = $obj->MySQLSelect($sql_book);
                        if (count($availdriverbooking) == 0) {
                            $newData[$j] = $Data[$i];
                            $j++;
                        }
                    }
                } else {

                    if ($eType == "UberX" && isProviderEligible($Data[$i]) == false) {
                        continue;
                    }

                    if ($eType == "UberX") {
                        $Data[$i]['IS_PROVIDER_ONLINE'] = isProviderOnline($Data[$i]) ? "Yes" : "No";
                    }
                    // for hotel panel web
                    if (($isFromHotelPanel == 'No' || $isFromAdminPanel == 'No') || (($isFromHotelPanel == 'Yes' && $Data[$i]['ACCEPT_CASH_TRIPS'] == "Yes") || ($isFromAdminPanel == 'Yes' && $Data[$i]['ACCEPT_CASH_TRIPS'] == "Yes"))) {
                        $newData[$j] = $Data[$i];
                        $j++;
                    }
                }
            }
        }

        if (strtoupper(PACKAGE_TYPE) == "SHARK" && checkSharkPackage()) {
            $newData = getPoolDriverList($newData, $Check_Driver_UFX, $isFromHotelPanel, $vLatitude, $vLongitude, $param, $sourceLat, $str_date, $sourceLon, $destLat, $destLon); // For Get Pool Driver Details By Hasmukh On 05-12-2018
        }
        if (checkDriverDestinationModule() && $eFly != 'Yes') {
            include_once('include/features/include_destinations_driver.php');
            $newData = getDestionsDriverList($newData, $destLat, $destLon);
        }
        //$returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }
    return $returnData;
}

function checkSurgePrice($vehicleTypeID, $selectedDateTime = "", $iRentalPackageId = "0") {
    global $ENABLE_SURGE_CHARGE_RENTAL, $vTimeZone, $obj,$vehicleTypeDataArr;
    if ($iRentalPackageId == "" || $iRentalPackageId == NULL) {
        $iRentalPackageId = 0;
    }
    //$ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true'); //Commented By HJ On 16-07-2019 For Optimize
    //$eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true'); //Commented By HJ On 16-07-2019 For Optimize
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
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
    $startTime_str = "t" . $currentDay . "PickStartTime";
    $endTime_str = "t" . $currentDay . "PickEndTime";
    $price_str = "f" . $currentDay . "PickUpPrice";
    $ePickStatus = $eNightStatus = "Inactive";
    $tNightSurgeData_PrevDay = $tNightSurgeData = "";
    
    //Added By HJ On 20-06-2020 For Optimized vehicle_type Table Query Start
    if(isset($vehicleTypeDataArr['vehicle_type'])){
        $VehicleTypeData = $vehicleTypeDataArr['vehicle_type'];
    }else{
        $VehicleTypeData = $obj->MySQLSelect("SELECT * from vehicle_type");
        $vehicleTypeDataArr['vehicle_type'] = $VehicleTypeData;
    }
    $tripVehicleDataArr =$tripVehicleData= array();
    for($h=0;$h<count($VehicleTypeData);$h++){
        $tripVehicleDataArr[$VehicleTypeData[$h]['iVehicleTypeId']] = $VehicleTypeData[$h];
    }
    if(isset($tripVehicleDataArr[$vehicleTypeID])){
        $getVehicleData[] = $tripVehicleDataArr[$vehicleTypeID];
    }
    //Added By HJ On 20-06-2020 For Optimized vehicle_type Table Query End
    //$getVehicleData = $obj->MySQLSelect("SELECT eNightStatus,ePickStatus,tNightSurgeData,$startTime_str,$endTime_str,$price_str FROM vehicle_type WHERE iVehicleTypeId='" . $vehicleTypeID . "'");
    $pickStartTime = $pickEndTime = "00:00:00";
    $fPickUpPrice = 1;
    if (count($getVehicleData) > 0) {
        $ePickStatus = $getVehicleData[0]['ePickStatus'];
        $eNightStatus = $getVehicleData[0]['eNightStatus'];
        $tNightSurgeData_PrevDay = $tNightSurgeData = $getVehicleData[0]['tNightSurgeData'];
        $pickStartTime = $getVehicleData[0][$startTime_str];
        $pickEndTime = $getVehicleData[0][$endTime_str];
        $fPickUpPrice = $getVehicleData[0][$price_str];
    }
    //echo $eNightStatus;die;
    ## Checking For Previous Day NightSurge Charge For 0-5 am ##
    if ($currentTime > "00:00:00" && $currentTime <= "05:00:00" && $eNightStatus == "Active" && ($iRentalPackageId == 0 || ($iRentalPackageId != 0 && $ENABLE_SURGE_CHARGE_RENTAL == "Yes"))) {
        $previousnightStartTime_str = "t" . $PreviousDay . "NightStartTime";
        $previousnightEndTime_str = "t" . $PreviousDay . "NightEndTime";
        $fpreviousNightPrice_str = "f" . $PreviousDay . "NightPrice";
        //$tNightSurgeData_PrevDay = get_value('vehicle_type', 'tNightSurgeData', 'iVehicleTypeId', $vehicleTypeID, '', 'true'); //Commented By HJ On 16-07-2019 For Optimize
        $tNightSurgeDataPrevDayArr = json_decode($tNightSurgeData_PrevDay, true);

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

    ## Checking For Previous Day NightSurge Charge For 0-5 am ##
    /* added for rental */
    if (($ePickStatus == "Active" || $eNightStatus == "Active") && ($iRentalPackageId == 0 || ($iRentalPackageId != 0 && $ENABLE_SURGE_CHARGE_RENTAL == "Yes"))) {
        //$pickStartTime = get_value('vehicle_type', $startTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true'); //Commented By HJ On 16-07-2019 For Optimize
        //$pickEndTime = get_value('vehicle_type', $endTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true'); //Commented By HJ On 16-07-2019 For Optimize
        //$fPickUpPrice = get_value('vehicle_type', $price_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true'); //Commented By HJ On 16-07-2019 For Optimize
        /* $nightStartTime = get_value('vehicle_type', 'tNightStartTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
          $nightEndTime = get_value('vehicle_type', 'tNightEndTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
          $fNightPrice = get_value('vehicle_type', 'fNightPrice', 'iVehicleTypeId', $vehicleTypeID, '', 'true'); */
        $nightStartTime_str = "t" . $currentDay . "NightStartTime";
        $nightEndTime_str = "t" . $currentDay . "NightEndTime";
        $fNightPrice_str = "f" . $currentDay . "NightPrice";
        //$tNightSurgeData = get_value('vehicle_type', 'tNightSurgeData', 'iVehicleTypeId', $vehicleTypeID, '', 'true'); //Commented By HJ On 16-07-2019 For Optimize
        $tNightSurgeDataArr = json_decode($tNightSurgeData, true);
        $nightStartTime = $nightEndTime = "00:00:00";
        $fNightPrice = 1;
        if (count($tNightSurgeDataArr) > 0) {
            $nightStartTime = $tNightSurgeDataArr[$nightStartTime_str];
            $nightEndTime = $tNightSurgeDataArr[$nightEndTime_str];
            $fNightPrice = $tNightSurgeDataArr[$fNightPrice_str];
        }
        $tempNightHour = "12:00:00";
        if (($currentTime > $pickStartTime) && ($currentTime < $pickEndTime) && $ePickStatus == "Active" && $fPickUpPrice > 1) {

            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PICK_SURGE_NOTE";
            $returnArr['SurgePrice'] = $fPickUpPrice . "X";
            $returnArr['SurgePriceValue'] = $fPickUpPrice;
        }
        // else if ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $eNightStatus == "Active") {
        else if ((($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime > $tempNightHour) || ($currentTime < $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime > $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour)) && $eNightStatus == "Active" && $fNightPrice > 1) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
            $returnArr['SurgePrice'] = $fNightPrice . "X";
            $returnArr['SurgePriceValue'] = $fNightPrice;
        } else {
            $returnArr['Action'] = "1";
        }
    } else {
        $returnArr['Action'] = "1";
    }
    return $returnArr;
}

function checkmemberemailphoneverification($iMemberId, $user_type = "Passenger") {
    global $obj, $DRIVER_EMAIL_VERIFICATION, $DRIVER_PHONE_VERIFICATION, $RIDER_EMAIL_VERIFICATION, $RIDER_PHONE_VERIFICATION, $generalobj,$userDetailsArr;
    if ($user_type == "Driver") {
        /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_EMAIL_VERIFICATION', '', 'true');
          $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_PHONE_VERIFICATION', '', 'true');
          $eEmailVerified = get_value('register_driver', 'eEmailVerified', 'iDriverId', $iMemberId, '', 'true');
          $ePhoneVerified = get_value('register_driver', 'ePhoneVerified', 'iDriverId', $iMemberId, '', 'true'); */
        $EMAIL_VERIFICATION = $DRIVER_EMAIL_VERIFICATION;
        $PHONE_VERIFICATION = $DRIVER_PHONE_VERIFICATION;
        //Added By HJ On 20-06-2020 For Optimization register_driver Table Query Start
        if(isset($userDetailsArr["register_driver_".$iMemberId])){
            $driverData = $userDetailsArr["register_driver_".$iMemberId];
        }else{
            $driverData = $obj->MySQLSelect("SELECT * FROM register_driver WHERE iDriverId='".$iMemberId."' ");
            $userDetailsArr["register_driver_".$iMemberId] = $driverData;
        }
        //Added By HJ On 20-06-2020 For Optimization register_driver Table Query End
        //$sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
        //$driverData = $obj->MySQLSelect($sqld);
        $eEmailVerified = $driverData[0]['eEmailVerified'];
        $ePhoneVerified = $driverData[0]['ePhoneVerified'];
    } else {
        /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'RIDER_EMAIL_VERIFICATION', '', 'true');
          $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'RIDER_PHONE_VERIFICATION', '', 'true');
          $eEmailVerified = get_value('register_user', 'eEmailVerified', 'iUserId', $iMemberId, '', 'true');
          $ePhoneVerified = get_value('register_user', 'ePhoneVerified', 'iUserId', $iMemberId, '', 'true'); */
        $EMAIL_VERIFICATION = $RIDER_EMAIL_VERIFICATION;
        $PHONE_VERIFICATION = $RIDER_PHONE_VERIFICATION;
        //Added By HJ On 18-06-2020 For Optimization register_user Table Query Start
        if(isset($userDetailsArr["register_user_".$iMemberId])){
            $driverData = $userDetailsArr["register_user_".$iMemberId];
        }else{
           $driverData = $obj->MySQLSelect("SELECT * FROM register_user WHERE iUserId='".$iMemberId."'");
           $userDetailsArr["register_user_".$iMemberId] = $driverData;
        }
        //Added By HJ On 18-06-2020 For Optimization register_user Table Query End
        //$sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_user WHERE iUserId = '" . $iMemberId . "'";
        //$driverData = $obj->MySQLSelect($sqld);
        $eEmailVerified = $driverData[0]['eEmailVerified'];
        $ePhoneVerified = $driverData[0]['ePhoneVerified'];
    }
    $email = $EMAIL_VERIFICATION == "Yes" ? ($eEmailVerified == "Yes" ? "true" : "false") : "true";
    $phone = $PHONE_VERIFICATION == "Yes" ? ($ePhoneVerified == "Yes" ? "true" : "false") : "true";

    if ($generalobj->checkXThemOn() == "Yes") {
        if ($phone == "false") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "DO_PHONE_VERIFY";
            setDataResponse($returnArr);
        }
    } else {
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
}

function sendemailphoneverificationcode($iMemberId, $user_type = "Passenger", $VerifyType) {
    global $generalobj, $obj;
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

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $str = "select * from send_message_templates where vEmail_Code='VERIFICATION_CODE_MESSAGE'";
    $res = $obj->MySQLSelect($str);
    $prefix = $res[0]['vBody_' . $vLangCode];
    //$prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];
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
    global $obj, $generalobj, $tconfig, $DELIVERY_VERIFICATION_METHOD, $POOL_ENABLE, $ENABLE_INTRANSIT_SHOPPING_SYSTEM, $SERVICE_PROVIDER_FLOW, $APP_TYPE, $ENABLE_FAVORITE_DRIVER_MODULE, $parent_ufx_catid,$UserCurrencyLanguageDetailsArr,$DriverCurrencyLanguageDetailsArr,$vSystemDefaultLangCode,$vSystemDefaultCurrencyName,$vSystemDefaultCurrencySymbol,$userDetailsArr,$currencyAssociateArr,$generalTripRatingDataArr,$tripDetailsArr,$driverVehicleDataArr;
    $returnArr = array();
    //$iTripId = 1582;
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    if ($eUserType == "Passenger") {
        $tblname = "register_user";
        $vLang = "vLang";
        $iUserId = "iUserId";
        $vCurrency = "vCurrencyPassenger";
        $vPhoneCode = "vPhoneCode";
        //$currencycode = get_value("trips", $vCurrency, "iTripId", $iTripId, '', 'true');
        //echo "<pre>";print_r($UserCurrencyLanguageDetailsArr);die;
        //Added By HJ On 09-06-2020 For Optimization Start
        if (!empty($UserCurrencyLanguageDetailsArr) && count($UserCurrencyLanguageDetailsArr) > 0 && !empty($UserCurrencyLanguageDetailsArr[$tblname . '_' . $iMemberId])) {
            $passengerData =$userCurrency= array();
            $currencyData = $UserCurrencyLanguageDetailsArr[$tblname . '_' . $iMemberId];
            $userCurrency['vCurrencyPassenger'] = $currencyData['currencycode'];
            $userCurrency['vLang'] = $currencyData['vLang'];
            $userCurrency['vSymbol'] = $currencyData['currencySymbol'];
            $passengerData[] = $userCurrency;
            //echo "<pre>";print_r($passengerData);die;
        }else{
            $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iMemberId . "'";
            $passengerData = $obj->MySQLSelect($sqlp);
        }
        //Added By HJ On 09-06-2020 For Optimization End
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $userlangcode = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
    } else {
        $tblname = "register_driver";
        $vLang = "vLang";
        $iUserId = "iDriverId";
        $vCurrency = "vCurrencyDriver";
        $vPhoneCode = "vCode";
        //Added By HJ On 09-06-2020 For Optimization Start
        if (!empty($DriverCurrencyLanguageDetailsArr) && count($DriverCurrencyLanguageDetailsArr) > 0 && !empty($DriverCurrencyLanguageDetailsArr[$tblname . '_' . $iMemberId])) {
            $driverData =$driverCurrency= array();
            $currencyData = $DriverCurrencyLanguageDetailsArr[$tblname . '_' . $iMemberId];
            $driverCurrency['vCurrencyDriver'] = $currencyData['currencycode'];
            $driverCurrency['vLang'] = $currencyData['vLang'];
            $driverCurrency['vSymbol'] = $currencyData['currencySymbol'];
            $driverData[] = $driverCurrency;
            //echo "<pre>";print_r($passengerData);die;
        }else{
        //$currencycode = get_value($tblname, $vCurrency, $iUserId, $iMemberId, '', 'true');
            $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iMemberId . "'";
            $driverData = $obj->MySQLSelect($sqld);
        }
        //Added By HJ On 09-06-2020 For Optimization End
        $currencycode = $driverData[0]['vCurrencyDriver'];
        $userlangcode = $driverData[0]['vLang'];
        $currencySymbol = $driverData[0]['vSymbol'];
    }
    //$userlangcode = get_value($tblname, $vLang, $iUserId, $iMemberId, '', 'true');
    if ($userlangcode == "" || $userlangcode == NULL) {
        //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
        if (!empty($vSystemDefaultLangCode)) {
            $userlangcode = $vSystemDefaultLangCode;
        } else {
            $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
    }
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    if ($currencycode == "" || $currencycode == NULL) {
        //Added By HJ On 09-06-2020 For Optimization Start
        if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol)) {
            $currencycode = $vSystemDefaultCurrencyName;
            $currencySymbol = $vSystemDefaultCurrencySymbol;
        } else {
            $sql = "SELECT vName,vSymbol from currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sql);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
        }
        //Added By HJ On 09-06-2020 For Optimization End
    }
    $ssql_fav_q = "";
    if (checkFavDriverModule() && $eUserType == "Passenger") {
        include_once('include/features/include_fav_driver.php');
        $ssql_fav_q = getFavSelectQuery($iMemberId);
    }
    //$sql = "SELECT * from trips WHERE iTripId = '" . $iTripId . "'";
    if (strtoupper(APP_TYPE) != "RIDE") {
        $sql = "SELECT tr.*,vt.vVehicleType_" . $userlangcode . " as vVehicleType,vt.vRentalAlias_" . $userlangcode . " as vRentalVehicleTypeName,vt.fTripHoldFees,vt.vLogo,vt.iVehicleCategoryId,vt.iCancellationTimeLimit,vt.fFixedFare,vt.eIconType,COALESCE(vc.iParentId, '0') as iParentId,COALESCE(vc.ePriceType, '') as ePriceType,COALESCE(vc.vLogo, '') as vLogoVehicleCategory,COALESCE(vc.vCategory_" . $userlangcode . ", '') as vCategory " . $ssql_fav_q . " from trips as tr LEFT JOIN  vehicle_type as vt ON tr.iVehicleTypeId = vt.iVehicleTypeId  LEFT JOIN " . $sql_vehicle_category_table_name . " as vc ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE tr.iTripId = '" . $iTripId . "'";
    } else {
        $sql = "SELECT tr.*,vt.vVehicleType_" . $userlangcode . " as vVehicleType,vt.vRentalAlias_" . $userlangcode . " as vRentalVehicleTypeName,vt.fTripHoldFees,vt.vLogo,vt.iVehicleCategoryId,vt.iCancellationTimeLimit,vt.fFixedFare,vt.eIconType  " . $ssql_fav_q . "  from trips as tr LEFT JOIN  vehicle_type as vt ON tr.iVehicleTypeId = vt.iVehicleTypeId WHERE tr.iTripId = '" . $iTripId . "'";
    }
    $tripData = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($tripData);die;
    $priceRatio = $tripData[0]['fRatio_' . $currencycode];
    $iActive = $tripData[0]['iActive'];
    // Convert Into Timezone
    $tripTimeZone = $tripData[0]['vTimeZone'];
    $eType = $tripData[0]['eType'];

    if ($SERVICE_PROVIDER_FLOW == "Provider" && $tripData[0]['eType'] == "UberX" && $tripData[0]['eServiceLocation'] == "Driver") {
        $tripData[0]['tSaddress'] = $tripData[0]['vWorkLocation'];
    }
    $tripData[0]['vServiceTitle'] = $tripData[0]['vServiceDetailTitle'] = "";
    if (isset($tripData[0]['vVehicleType'])) {
        $tripData[0]['vServiceTitle'] = $tripData[0]['vVehicleType'];
    }
    if (isset($tripData[0]['vVehicleType'])) {
        $tripData[0]['vServiceDetailTitle'] = $tripData[0]['vVehicleType'];
    }
    if (isset($tripData[0]['eType']) && $tripData[0]['eType'] == "Ride") {
        if ($APP_TYPE != "Ride") {
            $tripData[0]['vServiceTitle'] = $eUserType == "Driver" ? $languageLabelsArr['LBL_RIDE'] : $tripData[0]['vVehicleType'];
            $tripData[0]['vServiceDetailTitle'] = $tripData[0]['vVehicleType'];
        }

        if ($tripData[0]['eHailTrip'] == "Yes") {
            $tripData[0]['vServiceTitle'] = $languageLabelsArr['LBL_HAIL'];
            $tripData[0]['vServiceDetailTitle'] = $languageLabelsArr['LBL_HAIL'] . " - " . $tripData[0]['vVehicleType'];
        }

        if ($tripData[0]['iRentalPackageId'] > 0) {
            $tripData[0]['vServiceTitle'] = $languageLabelsArr['LBL_RENTAL_CATEGORY_TXT'];
            $tripData[0]['vServiceDetailTitle'] = $languageLabelsArr['LBL_RENTAL_CATEGORY_TXT'] . " - " . $tripData[0]['vVehicleType'];
        }
    } else if ($tripData[0]['eType'] == "Delivery" || $tripData[0]['eType'] == "Deliver") {
        if ($APP_TYPE != "Delivery" || $APP_TYPE != "Deliver") {
            $tripData[0]['vServiceTitle'] = $eUserType == "Driver" ? $languageLabelsArr['LBL_DELIVERY'] : $tripData[0]['vVehicleType'];
            $tripData[0]['vServiceDetailTitle'] = $tripData[0]['vVehicleType'];
        }
    }

    if ($SERVICE_PROVIDER_FLOW == "Provider" && $tripData[0]['eType'] == "UberX" && !empty($tripData[0]['tVehicleTypeFareData'])) {
        $tVehicleTypeFareDataArr = (array) json_decode($tripData[0]['tVehicleTypeFareData']);

        $ParentVehicleCategoryId = isset($tVehicleTypeFareDataArr['ParentVehicleCategoryId']) ? $tVehicleTypeFareDataArr['ParentVehicleCategoryId'] : 0;
        if ($ParentVehicleCategoryId == 0) {
            $tVehicleTypeFareDataArr_fareArr = (array) ($tVehicleTypeFareDataArr['FareData']);
            if (count($tVehicleTypeFareDataArr_fareArr) > 0) {
                // $tVehicleTypeFareDataArr[0]['id'];
                $sql_parent_id = "SELECT (SELECT vcs.vCategory_" . $userlangcode . " FROM " . $sql_vehicle_category_table_name . " as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as vCategory FROM " . $sql_vehicle_category_table_name . " as vc, vehicle_type as vt WHERE vc.iVehicleCategoryId = vt.iVehicleCategoryId AND vt.iVehicleTypeId = '" . $tVehicleTypeFareDataArr_fareArr[0]->id . "'";

                $parent_data_arr = $obj->MySQLSelect($sql_parent_id);
                $tripData[0]['vCategory'] = $parent_data_arr[0]['vCategory'];
            }
        } else {
            $tripData[0]['vCategory'] = get_value($sql_vehicle_category_table_name, "vCategory_" . $userlangcode, "iVehicleCategoryId", $ParentVehicleCategoryId, '', 'true');
        }


        $tripData[0]['vServiceTitle'] = $tripData[0]['vCategory'];
        if ($tVehicleTypeFareDataArr['eFareTypeServices'] == "Fixed") {
            $tripData[0]['vServiceDetailTitle'] = $tripData[0]['vCategory'];
        } else {
            $tripData[0]['vServiceDetailTitle'] = $tripData[0]['vCategory'] . " - " . $tripData[0]['vVehicleType'];
        }
    } else if ($tripData[0]['eType'] == "UberX") {
        if (!empty($parent_ufx_catid) && $parent_ufx_catid > 0) {
            $sql_parent_id = "SELECT vc.vCategory_" . $userlangcode . " as vCategory FROM " . $sql_vehicle_category_table_name . " as vc, vehicle_type as vt WHERE vc.iVehicleCategoryId = vt.iVehicleCategoryId AND vt.iVehicleTypeId = '" . $tripData[0]['iVehicleTypeId'] . "'";
        } else {
            $sql_parent_id = "SELECT (SELECT vcs.vCategory_" . $userlangcode . " FROM " . $sql_vehicle_category_table_name . " as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as vCategory FROM " . $sql_vehicle_category_table_name . " as vc, vehicle_type as vt WHERE vc.iVehicleCategoryId = vt.iVehicleCategoryId AND vt.iVehicleTypeId = '" . $tripData[0]['iVehicleTypeId'] . "'";
        }
        $parent_data_arr = $obj->MySQLSelect($sql_parent_id);

        $tripData[0]['vServiceTitle'] = $parent_data_arr[0]['vCategory'];
        $tripData[0]['vServiceDetailTitle'] = $parent_data_arr[0]['vCategory'] . " - " . $tripData[0]['vVehicleType'];
    }

    if ($tripData[0]['vTripPaymentMode'] == 'Cash' && $tripData[0]['ePayWallet'] == 'Yes') {
        $tripData[0]['ePayWallet'] = "No";
    }

    ## Get Trip Delivery Details ##
    $TripDeliveryData =$tripDeliveryFieldArr= array();
    if ($eType == "Multi-Delivery") {
        $returnArr['ePaymentByReceiverForDelivery'] = "No";
        // Added By HJ On 18-06-2020 For Optimize trips_delivery_locations Table Query Start
        $totalTripDeliveryCount =0;
        if(isset($tripDetailsArr["trips_delivery_locations_".$iTripId])){
            $sqldeliverydata = $tripDetailsArr["trips_delivery_locations_".$iTripId];
            $totalTripDeliveryCount = count($sqldeliverydata);
        }else{
            $sqldeliverydata = $obj->MySQLSelect("SELECT * FROM `trips_delivery_locations` WHERE iTripId='" . $iTripId . "' ORDER BY  iTripDeliveryLocationId ASC");
            $tripDetailsArr["trips_delivery_locations_".$iTripId] = $sqldeliverydata;
            $totalTripDeliveryCount = count($sqldeliverydata);
        }
        if(count($sqldeliverydata) > 0){
            $dataFound = 0;
            for($d=0;$d<count($sqldeliverydata);$d++){
                $iActiveStatus = $sqldeliverydata[$d]['iActive'];
                if((strtoupper($iActiveStatus) == "ACTIVE" || strtoupper($iActiveStatus) == "ON GOING TRIP") && $dataFound == 0){
                    $dataFound = 1;
                    $TripDeliveryData[] = $sqldeliverydata[$d];
                }
            }
        }
        // Added By HJ On 18-06-2020 For Optimize trips_delivery_locations Table Query End
        // Added By HJ On 18-06-2020 For Optimize trip_delivery_fields Table Query Start
        if(isset($tripDetailsArr["trip_delivery_fields_".$iTripId])){
            $sqldeliveryFielddata = $tripDetailsArr["trip_delivery_fields_".$iTripId];
        }else{
            $sqldeliveryFielddata = $obj->MySQLSelect("SELECT * FROM trip_delivery_fields WHERE iTripId='".$iTripId."'");
            $tripDetailsArr["trip_delivery_fields_".$iTripId] = $sqldeliveryFielddata;
        }
        $tripDeliveryFieldArr = array();
        for($f=0;$f<count($sqldeliveryFielddata);$f++){
            $iTripDeliveryLocationId = $sqldeliveryFielddata[$f]['iTripDeliveryLocationId'];
            $iDeliveryFieldId = $sqldeliveryFielddata[$f]['iDeliveryFieldId'];
            $tripDeliveryFieldArr[$iTripDeliveryLocationId][$iDeliveryFieldId] = $sqldeliveryFielddata[$f];
        }
        // Added By HJ On 18-06-2020 For Optimize trip_delivery_fields Table Query End
        if (count($TripDeliveryData) > 0) {
            $iTripDeliveryLocationId = $TripDeliveryData[0]['iTripDeliveryLocationId'];
            // Added By HJ On 18-06-2020 For Optimize register_driver Table Query Start
            if(isset($userDetailsArr[$tblname."_".$iMemberId])){
                $vPhoneCode = $userDetailsArr[$tblname."_".$iMemberId][0][$vPhoneCode];
            }else{
                $vPhoneCode = get_value($tblname, $vPhoneCode, $iUserId, $iMemberId, '', 'true');
            }
            // Added By HJ On 18-06-2020 For Optimize register_driver Table Query End
            // Added By HJ On 18-06-2020 For Optimize trip_delivery_fields Table Query Start
            if(isset($tripDeliveryFieldArr[$iTripDeliveryLocationId][2])){
                $vReceiverName = $tripDeliveryFieldArr[$iTripDeliveryLocationId][2]['vValue'];
            }else{
                $vReceiverName = get_value('trip_delivery_fields', 'vValue', 'iDeliveryFieldId', '2', " and iTripDeliveryLocationId ='" . $iTripDeliveryLocationId . "'", 'true');
            }
            if(isset($tripDeliveryFieldArr[$iTripDeliveryLocationId][2])){
                $vReceiverMobile = $tripDeliveryFieldArr[$iTripDeliveryLocationId][3]['vValue'];
            }else{
                $vReceiverMobile = get_value('trip_delivery_fields', 'vValue', 'iDeliveryFieldId', '3', " and iTripDeliveryLocationId ='" . $iTripDeliveryLocationId . "'", 'true');
            }
            // Added By HJ On 18-06-2020 For Optimize trip_delivery_fields Table Query End
            $Data_Trip_Update = array();
            $Data_Trip_Update['iActive'] = $tripData[0]['iActive'] = $TripDeliveryData[0]['iActive'];
            $Data_Trip_Update['tEndLat'] = $tripData[0]['tEndLat'] = $TripDeliveryData[0]['tEndLat'];
            $Data_Trip_Update['tEndLong'] = $tripData[0]['tEndLong'] = $TripDeliveryData[0]['tEndLong'];
            $Data_Trip_Update['tDaddress'] = $tripData[0]['tDaddress'] = $TripDeliveryData[0]['tDaddress'];
            $Data_Trip_Update['vDeliveryConfirmCode'] = $tripData[0]['vDeliveryConfirmCode'] = "";
            if (isset($TripDeliveryData[0]['vDeliveryConfirmCode'])) {
                $Data_Trip_Update['vDeliveryConfirmCode'] = $tripData[0]['vDeliveryConfirmCode'] = $TripDeliveryData[0]['vDeliveryConfirmCode'];
            }
            $Data_Trip_Update['vReceiverName'] = $tripData[0]['vReceiverName'] = $vReceiverName;
            $Data_Trip_Update['vReceiverMobile'] = $tripData[0]['vReceiverMobile'] = "+" . $vPhoneCode . $vReceiverMobile;
            //Added By HJ On 08-01-2019 For Removed Country Code As Per Discuss With DT Start
            if ($eType == "Multi-Delivery") {
                $Data_Trip_Update['vReceiverMobile'] = $tripData[0]['vReceiverMobile'] = $vReceiverMobile;
            }
            //Added By HJ On 08-01-2019 For Removed Country Code As Per Discuss With DT End
            //$Data_Trip_Update['ePaymentByReceiver'] = $TripDeliveryData[0]['ePaymentByReceiver'];
            $where = " iTripId = '" . $iTripId . "'";
            $Data_Trip_Update_id = $obj->MySQLQueryPerform("trips", $Data_Trip_Update, 'update', $where);
            $returnArr['iTripDeliveryLocationId'] = $iTripDeliveryLocationId;
            $returnArr['ePaymentByReceiverForDelivery'] = $TripDeliveryData[0]['ePaymentByReceiver'];
            $returnArr['TotalTripDeliveryData'] = $totalTripDeliveryCount; // Added By HJ On 18-06-2020 For Optimize trips_delivery_locations Table Query
        }
        //$sql = "SELECT tdl.ePaymentBy,tdl.ePaymentByReceiver,tdl.iTripDeliveryLocationId FROM trips_delivery_locations as tdl WHERE tdl.`iTripId`='" . $iTripId . "' order by tdl.iTripDeliveryLocationId ASC";
        //$Data_tripLocations = $obj->MySQLSelect($sql);
        $Data_tripLocations = $sqldeliverydata; // Added By HJ On 18-06-2020 For Optimize trips_delivery_locations Table Query
        for ($j = 0; $j < count($Data_tripLocations); $j++) {
            if ($Data_tripLocations[0]['ePaymentBy'] == "Sender") {
                $returnArr['PaymentPerson'] = "Sender";
                break;
            } else if ($Data_tripLocations[0]['ePaymentBy'] == "Individual") {
                $returnArr['PaymentPerson'] = $languageLabelsArr['LBL_EACH_RECIPIENT'];
                break;
            } else {
                if ($Data_tripLocations[$j]['ePaymentByReceiver'] == "Yes") {
                    $ReceNo = $j + 1;
                    // Added By HJ On 18-06-2020 For Optimize trip_delivery_fields Table Query Start
                    if(isset($tripDeliveryFieldArr[$Data_tripLocations[$j]['iTripDeliveryLocationId']][2])){
                        $delFieldValue = $tripDeliveryFieldArr[$Data_tripLocations[$j]['iTripDeliveryLocationId']][3]['vValue'];
                    }else{
                        $db_trip_fields = $obj->MySQLSelect("select vValue from trip_delivery_fields where iTripId ='$iTripId' and iDeliveryFieldId = '2' and iTripDeliveryLocationId = '" . $Data_tripLocations[$j]['iTripDeliveryLocationId'] . "'");
                        $delFieldValue = $db_trip_fields[0]['vValue'];
                    }
                    // Added By HJ On 18-06-2020 For Optimize trip_delivery_fields Table Query End
                    $returnArr['PaymentPerson'] = $languageLabelsArr['LBL_RECEIPENT_TXT'] . $ReceNo . " (" . $delFieldValue . ")";
                    break;
                }
            }
        }
    }
    ## Get Trip Delivery Details end##
    
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
    if (count($TripDeliveryData) > 0) {
        $LBL_CURRENT_DELIVERY_NUMBER_TXT = $languageLabelsArr['LBL_CURRENT_DELIVERY_NUMBER'];
        $LBL_OUT_OF_TXT = $languageLabelsArr['LBL_OUT_OF_TXT'];
        $iRunningTripDeliveryNo = $tripData[0]['iRunningTripDeliveryNo'];
        $TotalTripDelivery = $totalTripDeliveryCount; // Added By HJ On 18-06-2020 For Optimize trips_delivery_locations Table Query
        if ($iActive == "Active") {
            $iRunningTripDeliveryNo = $iRunningTripDeliveryNo + 1;
        }
        if ($iRunningTripDeliveryNo > $TotalTripDelivery) {
            $iRunningTripDeliveryNo = $TotalTripDelivery;
        }
        if ($TotalTripDelivery > 1) {
            $Running_Delivery_Txt = $LBL_CURRENT_DELIVERY_NUMBER_TXT . " " . $iRunningTripDeliveryNo . " " . $LBL_OUT_OF_TXT . " " . $TotalTripDelivery;
        } else {
            $Running_Delivery_Txt = $LBL_CURRENT_DELIVERY_NUMBER_TXT;
        }
        $returnArr['Running_Delivery_Txt'] = $Running_Delivery_Txt;
        $returnArr['Running_Receipent_Detail'] = $languageLabelsArr['LBL_CURRENT_DELIVERY_NUMBER'] . " " . $iRunningTripDeliveryNo . " ( " . $vReceiverName . " )";
    }
    ## Check for trip sign verification for multideleivery ##
    if ($eType == "Multi-Delivery") {
        // $DELIVERY_VERIFICATION_METHOD = $generalobj->getConfigurations("configurations", "DELIVERY_VERIFICATION_METHOD");
        $IS_OPEN_SIGN_VERIFY = $IS_OPEN_FOR_SENDER = "No";
        $vTripStatus = $tripData[0]['iActive'];
        // Added By HJ On 18-06-2020 For Optimize register_driver Table Query Start
        if(isset($userDetailsArr["register_driver_".$tripData[0]['iDriverId']])){
            $vDriverTripStatus = $userDetailsArr["register_driver_".$tripData[0]['iDriverId']][0]['vTripStatus'];
            //echo "<pre>";print_r($vDriverTripStatus);die;
        }else{
            $vDriverTripStatus = get_value('register_driver', 'vTripStatus', 'iDriverId', $tripData[0]['iDriverId'], '', 'true');
        }
        // Added By HJ On 18-06-2020 For Optimize register_driver Table Query End
        $eSignVerification = $tripData[0]['eSignVerification'];
        if (($vTripStatus == "Active" && $vDriverTripStatus == "Arrived" && $eSignVerification == "No") || ($tripData[0]['ePaymentCollect_Delivery'] == "No" && $vDriverTripStatus == "Arrived")) {
            $IS_OPEN_SIGN_VERIFY = $IS_OPEN_FOR_SENDER = "Yes";
        }
        if ($IS_OPEN_SIGN_VERIFY == "No") {
            // Added By HJ On 18-06-2020 For Optimize trips_delivery_locations Table Query Start
            $TripDeliData = array();
            if(isset($tripDetailsArr["trips_delivery_locations_".$iTripId])){
                $sqldeliverydata = $tripDetailsArr["trips_delivery_locations_".$iTripId];
                $dataFound = 0;
                for($dj=0;$dj<count($sqldeliverydata);$dj++){
                    $iActiveStatus = $sqldeliverydata[$dj]['iActive'];
                    $eSignVerification = $sqldeliverydata[$dj]['eSignVerification'];
                    if((strtoupper($iActiveStatus) == "CANCELED" || strtoupper($iActiveStatus) == "FINISHED") && $dataFound == 0 && strtoupper($eSignVerification) == "NO"){
                        $dataFound = 1;
                        $TripDeliData[] = $sqldeliverydata[$dj];
                    }
                }
            }else{
                $TripDeliData = $obj->MySQLSelect("SELECT eSignVerification FROM `trips_delivery_locations` WHERE ( iActive='Canceled' OR iActive='Finished')  AND eSignVerification = 'No' AND iTripId='" . $iTripId . "' ORDER BY iTripDeliveryLocationId ASC LIMIT 0,1");
            }
            // Added By HJ On 18-06-2020 For Optimize trips_delivery_locations Table Query End
            $eSignVerification = "Yes";
            if (count($TripDeliData) > 0) {
                $eSignVerification = $TripDeliData[0]['eSignVerification'];
            }
            //echo $DELIVERY_VERIFICATION_METHOD;die;
            if ($eSignVerification == "No" && $DELIVERY_VERIFICATION_METHOD != "None") {
                //echo $DELIVERY_VERIFICATION_METHOD;die;
                $IS_OPEN_SIGN_VERIFY = "Yes";
                $IS_OPEN_FOR_SENDER = "No";
            }
        }

        $returnArr['IS_OPEN_SIGN_VERIFY'] = $IS_OPEN_SIGN_VERIFY;
        $returnArr['IS_OPEN_FOR_SENDER'] = $IS_OPEN_FOR_SENDER;
        //$DELIVERY_VERIFICATION_METHOD=$generalobj->getConfigurations("configurations","DELIVERY_VERIFICATION_METHOD");
        $vDeliveryConfirmCode = ($DELIVERY_VERIFICATION_METHOD == "Code") ? $tripData[0]['vDeliveryConfirmCode'] : "";
    } else {
        $returnArr['IS_OPEN_SIGN_VERIFY'] = $returnArr['IS_OPEN_FOR_SENDER'] = "No";
        $vDeliveryConfirmCode = "";
        if (isset($tripData[0]['vDeliveryConfirmCode'])) {
            $vDeliveryConfirmCode = $tripData[0]['vDeliveryConfirmCode'];
        }
    }
    ## Check for trip sign verification for multideleivery ##
    $tripData[0]['vDeliveryConfirmCode'] = $vDeliveryConfirmCode;
    // echo "<pre>";print_r($tripData);exit;
    $eTransit = "No";
    if (isset($tripData[0]['fTripHoldFees']) && $tripData[0]['fTripHoldFees'] > 0) {
        $eTransit = "Yes";
    }
    $tripData[0]['eTransit'] = $eTransit;
    $returnArr = array_merge($tripData[0], $returnArr);

    if ($tripData[0]['iUserPetId'] > 0) {
        $petDetails_arr = get_value('user_pets', 'iPetTypeId,vTitle as PetName,vWeight as PetWeight, tBreed as PetBreed, tDescription as PetDescription', 'iUserPetId', $tripData[0]['iUserPetId'], '', '');
    } else {
        $petDetails_arr = array();
    }
    /* Added For REntal */
    $iRentalPackageId = $tripData[0]['iRentalPackageId'];
    $returnArr['eRental'] = "";
    if ($iRentalPackageId > 0) {
        $returnArr['eRental'] = "Yes";
    }
    /* Added For REntal */
    $iPackageTypeId = $tripData[0]['iPackageTypeId'];
    if ($iPackageTypeId != 0) {
        $sqlnew = "SELECT vName_" . $userlangcode . " as vName FROM package_type WHERE iPackageTypeId='" . $iPackageTypeId . "'";
        $pkgdata = $obj->MySQLSelect($sqlnew);
        $returnArr['PackageType'] = $pkgdata[0]['vName'];
        // $returnArr['PackageType'] = get_value('package_type', 'vName', 'iPackageTypeId', $iPackageTypeId, '', 'true');
    }
    $returnArr['PetDetails']['PetName'] = $returnArr['PetDetails']['PetWeight'] = $returnArr['PetDetails']['PetBreed'] = $returnArr['PetDetails']['PetDescription'] = $returnArr['PetDetails']['PetTypeName'] = '';
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

    //echo $userlangcode."=======".$iCancelReasonId;exit;
    if (!empty($tripData[0]['vCancelComment'])) {
        $returnArr['vCancelReason'] = $tripData[0]['vCancelComment'];
    }
    if ($iCancelReasonId > 0) {
        $vCancelReason = get_value('cancel_reason', "vTitle_" . $userlangcode, 'iCancelReasonId', $iCancelReasonId, '', 'true');
        $returnArr['vCancelReason'] = $vCancelReason;
    }
    //Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected Start
    // if (isset($tripData[0]['tVehicleTypeFareData']) && $tripData[0]['tVehicleTypeFareData'] != "") {
    // $decodeTypeData = json_decode($tripData[0]['tVehicleTypeFareData']);
    // $tripData[0]['vCategory'] = $tripData[0]['vVehicleType'] = $decodeTypeData[0]->vVehicleCategory;
    // }
    //Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected End
    $vVehicleType = "";
    if (isset($tripData[0]['vVehicleType'])) {
        $vVehicleType = $tripData[0]['vVehicleType'];
    }
    $vRentalVehicleTypeName = $vVehicleTypeLogo = "";
    if (isset($tripData[0]['vRentalVehicleTypeName'])) {
        $vRentalVehicleTypeName = $tripData[0]['vRentalVehicleTypeName'];
    }
    if (isset($tripData[0]['vLogo'])) {
        $vVehicleTypeLogo = $tripData[0]['vLogo'];
    }
    $iVehicleCategoryId = $tripData[0]['iVehicleCategoryId'];
    $vVehicleCategoryData[0]['vLogo'] = $tripData[0]['vLogoVehicleCategory'];
    $vVehicleCategoryData[0]['vCategory'] = $tripData[0]['vCategory'];
    $vVehicleFare = $tripData[0]['fFixedFare'];
    $iParentId = $tripData[0]['iParentId'];
    $ePoolRide = $tripData[0]['ePoolRide'];
    if ($iParentId == 0) {
        $ePriceType = $tripData[0]['ePriceType'];
    } else {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    //$eIconType = get_value('vehicle_type', "eIconType", 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');
    $eIconType = "";
    if (isset($tripData[0]['eIconType'])) {
        $eIconType = $tripData[0]['eIconType'];
    }
    $TripTime = date('h:iA', strtotime($tripData[0]['tTripRequestDate']));
    $tTripRequestDateOrig = "";
    if (isset($tripData[0]['tTripRequestDate'])) {
        $tTripRequestDateOrig = $tripData[0]['tTripRequestDate'];
    }
    $tTripRequestDate = date('dS M Y \a\t h:i a', strtotime($tripData[0]['tTripRequestDate']));
    $tStartDate = $tripData[0]['tStartDate'];
    $tEndDate = $tripData[0]['tEndDate'];
    $tDriverArrivedDate = $tripData[0]['tDriverArrivedDate'];
    $iCancellationTimeLimit = $tripData[0]['iCancellationTimeLimit'];
    ## Checking Minutes For Waiting Fee ##
    $Vehicle_WaitingFeeTimeLimit = $tripData[0]['iWaitingFeeTimeLimit'];
    if (!empty($tripData[0]['tVehicleTypeFareData']) && $tripData[0]['eType'] == "UberX") {
        $tVehicleTypeFareDataArr = (array) json_decode($tripData[0]['tVehicleTypeFareData']);
        $Vehicle_WaitingFeeTimeLimit = $tVehicleTypeFareDataArr['ParentWaitingTimeLimit'];
    }
    //echo "Limit::".$tripData[0]['iWaitingFeeTimeLimit'];exit;
    $Vehicle_WaitingFeeTimeLimit = $Vehicle_WaitingFeeTimeLimit * 60;
    $waiting_time_diff = strtotime($tStartDate) - strtotime($tDriverArrivedDate) - $Vehicle_WaitingFeeTimeLimit;
    $waitingTime = ceil($waiting_time_diff / 60);
    //$waitingTime = $waitingTime - $iCancellationTimeLimit;
    if ($waitingTime > 1) {
        $waitingTime = $waitingTime . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
    } else {
        $waitingTime = $waitingTime . " " . $languageLabelsArr['LBL_MINUTE'];
    }
    ## Checking Minutes For Waiting Fee ##
    $totalTime = $runQuery = 0;
    if (($tStartDate != '' && $tStartDate != '0000-00-00 00:00:00' && $tEndDate != '' && $tEndDate != '0000-00-00 00:00:00') || $tripData[0]['eType'] == "Multi-Delivery") {
        if ($tripData[0]['eFareType'] == "Hourly") {
            // $hours       =   0;
            // $minutes     =   0;
            $totalSec = 0;
            $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$iTripId'";
            $db_tripTimes = $obj->MySQLSelect($sql22);
            $runQuery = 1;
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
            if ($tripData[0]['eType'] == "Multi-Delivery") {
                $triprtotaltime = secondsToTime($tripData[0]['fDuration'] * 60);
                $days = $triprtotaltime['d'];
                $hours = $triprtotaltime['h'];
                $minuts = $triprtotaltime['m'];
                $seconds = $triprtotaltime['s'];
            }
            if ($days > 0) {
                $hours = ($days * 24) + $hours;
            }
            $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
            if ($hours > 0) {
                $totalTime = $hours . ':' . $minuts . ':' . $seconds;
            } else if ($minuts > 0) {
                $LBL_MINUTES_TXT = ($minuts > 1) ? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
                $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
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
            if ($tripData[0]['eType'] == "Multi-Delivery") {
                $triprtotaltime = secondsToTime($tripData[0]['fDuration'] * 60);
                $days = $triprtotaltime['d'];
                $hours = $triprtotaltime['h'];
                $minutes = $triprtotaltime['m'];
                $seconds = $triprtotaltime['s'];
            }
            $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
            $LBL_MINUTES_TXT = ($minutes > 1) ? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
            $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);
            if ($days > 0) {
                $hours = ($days * 24) + $hours;
            }
            if ($hours > 0) {
                //$totalTime = $hours * 60;
                //$totalTime = $hours.':'.$minutes.':'.$seconds." " .$languageLabelsArr['LBL_HOUR'] ;
                $totalTime = $hours . ':' . $minutes . ':' . $seconds . " " . $LBL_HOURS_TXT;
            } else if ($minutes > 0) {
                //$totalTime = $totalTime + $minutes;
                //$totalTime = $minutes.':'.$seconds. "           " . $languageLabelsArr['LBL_MINUTES_TXT'];
                $totalTime = $minutes . ':' . $seconds . " " . $LBL_MINUTES_TXT;
            }
            //$totalTime = $totalTime . ":" . $seconds . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
            if ($totalTime < 1) {
                $totalTime = $seconds . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
            }
        }
    }

    $totalTime_hold = "";
    //Added By HJ On 28-12-2018 For Calculate In Transite Hold Time Start
    //if ($ENABLE_INTRANSIT_SHOPPING_SYSTEM == "Yes" && $tripData[0]['eType'] == "Ride") { //Comment By Hasmukh Because Applied Time Not Display
    if ($tripData[0]['eType'] == "Ride") {
        $totalSecTransite = 0;
        if ($runQuery == 0) {
            //Added By HJ On 17-06-2020 For Optimize trip_times Table Query Start
            if(isset($tripDetailsArr["trip_times_".$iTripId])){
                $db_tripTimes = array();
                $db_tripTimes = $tripDetailsArr["trip_times_".$iTripId];
            }else{
                $db_tripTimes = $obj->MySQLSelect("SELECT * FROM `trip_times` WHERE iTripId='".$iTripId."'");
                $tripDetailsArr["trip_times_".$iTripId] = $db_tripTimes;
            }
            //Added By HJ On 17-06-2020 For Optimize trip_times Table Query End
        }
        foreach ($db_tripTimes as $dtT) {
            if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                $totalSecTransite += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
            }
        }
        $yearsTransite = floor($totalSecTransite / (365 * 60 * 60 * 24));
        $monthsTransite = floor(($totalSecTransite - $yearsTransite * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $daysTransite = floor(($totalSecTransite - $yearsTransite * 365 * 60 * 60 * 24 - $monthsTransite * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        $hoursTransite = floor(($totalSecTransite - $yearsTransite * 365 * 60 * 60 * 24 - $monthsTransite * 30 * 60 * 60 * 24 - $daysTransite * 60 * 60 * 24) / (60 * 60));
        $minutsTransite = floor(($totalSecTransite - $yearsTransite * 365 * 60 * 60 * 24 - $monthsTransite * 30 * 60 * 60 * 24 - $daysTransite * 60 * 60 * 24 - $hoursTransite * 60 * 60) / 60);
        $secondsTransite = floor(($totalSecTransite - $yearsTransite * 365 * 60 * 60 * 24 - $monthsTransite * 30 * 60 * 60 * 24 - $daysTransite * 60 * 60 * 24 - $hoursTransite * 60 * 60 - $minutsTransite * 60));

        if ($daysTransite > 0) {
            $hoursTransite = ($daysTransite * 24) + $hoursTransite;
        }
        $LBL_HOURS_TXT = ($hoursTransite > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
        if ($hoursTransite > 0) {
            $totalTime_hold = $hoursTransite . ' ' . $LBL_HOURS_TXT;
        }
        if ($minutsTransite > 0) {
            $LBL_MINUTES_TXT = ($minutsTransite > 1) ? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
            $LBL_HOURS_TXT = ($hoursTransite > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
            $totalTime_hold = ($hoursTransite > 0) ? $hoursTransite . ":" . $minutsTransite . " " . $LBL_HOURS_TXT : $minutsTransite . " " . $LBL_MINUTES_TXT;
            if ($hoursTransite > 0) {
                $totalTime_hold = ($minutsTransite > 0) ? $hoursTransite . ":" . $minutsTransite . ":" . $secondsTransite . " " . $LBL_HOURS_TXT : $hoursTransite . " " . $LBL_HOURS_TXT;
            } else {
                $totalTime_hold = $minutsTransite . ":" . $secondsTransite . " " . $LBL_MINUTES_TXT;
            }
        }
        if ($totalTime_hold == "") {
            $totalTime_hold = "1 " . $languageLabelsArr['LBL_MINUTE'];
        }
    }
    //print_r($fTripHoldPrice);
    //die;
    //Added By HJ On 28-12-2018 For Calculate In Transite Hold Time End
    if ($iActive == "Canceled") {
        $totalTime = 0;
    }
    if ($totalTime == 0) {
        $totalTime = "0.00 " . $languageLabelsArr['LBL_MINUTE'];
    }
    $returnArr['carTypeName'] = $vVehicleType;
    if ($tripData[0]['iRentalPackageId'] > 0) {
        $returnArr['carTypeName'] = $vRentalVehicleTypeName;
    }
    $returnArr['carImageLogo'] = $vVehicleTypeLogo;
    $iDriverId = $tripData[0]['iDriverId'];
    $driverDetails= array();
    //Added By HJ On 13-06-2020 For Optimization ratings_user_driver Table Query Start
    $TripRating = "0";
    if($iTripId >0){
        if(isset($generalTripRatingDataArr['ratings_user_driver_'.$iTripId])){
            $getTripRateData = $generalTripRatingDataArr['ratings_user_driver_'.$iTripId];
            for($r=0;$r<count($getTripRateData);$r++){
                $rateUserType = $getTripRateData[$r]['eUserType'];
                if(strtoupper($rateUserType) == strtoupper($eUserType)){
                    $TripRating = $getTripRateData[$r]['vRating1'];
                }
            }
        }
    }
    //Added By HJ On 13-06-2020 For Optimization ratings_user_driver Table Query End
    if ($eUserType == "Passenger") {
        //$TripRating = get_value('ratings_user_driver', 'vRating1', 'iTripId', $iTripId, ' AND eUserType="Driver"', 'true'); Commented By HJ On 13-06-2020 For Optimization Query
        $driverDetails = get_value('register_driver', '*', 'iDriverId', $iDriverId);
        //$returnArr['vDriverImage'] = get_value('register_driver', 'vImage', 'iDriverId', $iDriverId, '', 'true');
        $returnArr['vDriverImage'] = $driverDetails[0]['vImage'];
        //$driverDetailArr = get_value('register_driver', '*', 'iDriverId', $iDriverId);
        $eUnit = $tripData[0]['vCountryUnitRider'];
    } else {
        //$TripRating = get_value('ratings_user_driver', 'vRating1', 'iTripId', $iTripId, ' AND eUserType="Passenger"', 'true'); Commented By HJ On 13-06-2020 For Optimization Query
        //$passgengerDetailArr = get_value('register_user', '*', 'iUserId', $tripData[0]['iUserId']);
        $eUnit = $tripData[0]['vCountryUnitDriver'];
        //$eUnit = $tripData[0]['vCountryUnitRider'];
    }
    $DisplayDistanceTxt = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
    if ($eUnit == "Miles") {
        $DisplayDistanceTxt = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
    }
    if ($TripRating == "" || $TripRating == NULL) {
        $TripRating = "0";
    }
    $iFare = $tripData[0]['iFare'];
    //$iFare = $tripData[0]['iFare']+$tripData[0]['fTollPrice'];
    $fPricePerKM = $tripData[0]['fPricePerKM'] * $priceRatio;
    $iBaseFare = $tripData[0]['iBaseFare'] * $priceRatio;
    $fPricePerMin = $tripData[0]['fPricePerMin'] * $priceRatio;
    $fCommision = $tripData[0]['fCommision'];
    $fDistance = $tripData[0]['fDistance'];
    if ($eUnit == "Miles") {
        $fDistance = round($fDistance * 0.621371, 2);
    }
    $fDistance = ($iActive != 'Canceled') ? $fDistance : 0;
    $vDiscount = $tripData[0]['vDiscount']; // 50 $
    $fDiscount = $tripData[0]['fDiscount']; // 50
    $fMinFareDiff = $tripData[0]['fMinFareDiff'] * $priceRatio;
    $fWalletDebit = $tripData[0]['fWalletDebit'];
    $extraPersonCharge = $tripData[0]['fExtraPersonCharge'] * $priceRatio;
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
    $fWaitingFees = $tripData[0]['fWaitingFees'] * $priceRatio;
    $fTripHoldPrice = $tripData[0]['fTripHoldPrice'] * $priceRatio; // Added By HJ For Intransit Amount On 28-12-2018
    //print_r($fTripHoldPrice);
    //die;
    if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes") {
        $fWaitingFees = 0;
    }
    $fTollPrice = $tripData[0]['fTollPrice'] * $priceRatio;
    $fTax1 = $tripData[0]['fTax1'] * $priceRatio;
    $fTax2 = $tripData[0]['fTax2'] * $priceRatio;
    $fOutStandingAmount = $tripData[0]['fOutStandingAmount'] * $priceRatio;
    $fAddedOutstandingamt = $tripData[0]['fAddedOutstandingamt'] * $priceRatio;
    //added for hotel
    $fHotelCommision = $tripData[0]['fHotelCommision'] * $priceRatio;
    // added for surge
    $fAirportPickupSurgeAmount = $tripData[0]['fAirportPickupSurgeAmount'] * $priceRatio;
    $fAirportDropoffSurgeAmount = $tripData[0]['fAirportDropoffSurgeAmount'] * $priceRatio;

    $eTollSkipped = "Yes";
    if ($fTollPrice > 0) {
        $eTollSkipped = $tripData[0]['eTollSkipped'];
    }
    $tUserComment = "";
    if (isset($tripData[0]['tUserComment'])) {
        $tUserComment = $tripData[0]['tUserComment'];
    }
    ### Organization Profile Details ###
    $returnArr['vProfileName'] = "";
    $returnArr['OrganizationName'] = "";
    if ($tripData[0]['iOrganizationId'] > 0) {
        $TripUserOrganizationProfileDetailsArr = getTripUserOrganizationProfileDetails($iTripId, $tripData[0]['iUserId'], $userlangcode, $tripData[0]['iUserProfileId'], $tripData[0]['iOrganizationId']);
        $returnArr['vProfileName'] = $TripUserOrganizationProfileDetailsArr[0]['vProfileName'];
        $returnArr['OrganizationName'] = $eUserType == "Passenger" ? $TripUserOrganizationProfileDetailsArr[0]['vCompany'] : $languageLabelsArr['LBL_ORGANIZATION'];
    }
    ### Organization Profile Details ###
    ### Display Trip Organization Reason ###
    $eTripReason = $tripData[0]['eTripReason'];
    $returnArr['vReasonTitle'] = "";
    if ($eTripReason == "Yes" && $eUserType == "Passenger") {
        $vReasonTitle = $tripData[0]['vReasonTitle'];
        $iTripReasonId = $tripData[0]['iTripReasonId'];
        if ($vReasonTitle != "") {
            $returnArr['vReasonTitle'] = $languageLabelsArr['LBL_ORGANIZATION'] . ": " . $returnArr['OrganizationName'] . "\n" . $languageLabelsArr['LBL_BUSINESS_PROFILE'] . ": " . $returnArr['vProfileName'] . "\n" . $languageLabelsArr['LBL_REASON'] . ": " . $vReasonTitle;
        }
        if ($iTripReasonId > 0) {
            $sql = "SELECT vReasonTitle from  trip_reason  WHERE iTripReasonId ='" . $iTripReasonId . "'";
            $tripreasons = $obj->MySQLSelect($sql);
            $vReasonTitle = "vReasonTitle_" . $userlangcode;
            $vReasonTitleArr = json_decode($tripreasons[0]['vReasonTitle'], true);
            $vReasonTitleTxt = $vReasonTitleArr[$vReasonTitle];
            $returnArr['vReasonTitle'] = $languageLabelsArr['LBL_ORGANIZATION'] . ": " . $returnArr['OrganizationName'] . "\n" . $languageLabelsArr['LBL_BUSINESS_PROFILE'] . ": " . $returnArr['vProfileName'] . "\n" . $languageLabelsArr['LBL_REASON'] . ": " . $vReasonTitleTxt;
        }
    }

    /* ==========================STOP OVER POINT START======================= */
    //if($eUserType != "Passenger"){
    if (checkStopOverPointModule() && !empty($iTripId) && $tripData[0]['eType'] == 'Ride' && ($tripData[0]['iActive'] != 'Finished' || $tripData[0]['iActive'] != 'Canceled')) {
        include_once('include/features/include_stop_over_point.php');
        $stop_over_point_data = getStopOverPointData($iTripId);
        $returnArr['stop_over_point_history'] = getDropOverPointHistory($iTripId);
        if (isset($stop_over_point_data['tDAddress']) && !empty($stop_over_point_data['tDAddress'])) {
            if ($eUserType != "Passenger") {
                $returnArr['tEndLat'] = $stop_over_point_data['tDestLatitude'];
                $returnArr['tEndLong'] = $stop_over_point_data['tDestLongitude'];
                $returnArr['tDaddress'] = $stop_over_point_data['tDAddress'];
            }
            $returnArr['iStopId'] = $stop_over_point_data['iStopId'];
            $returnArr['totalStopOverPoint'] = $stop_over_point_data['totalStopOverPoint'];
            $returnArr['currentStopOverPoint'] = $stop_over_point_data['currentStopOverPoint'];
            $returnArr['remaininStopOverPoint'] = $returnArr['stop_over_point_data']['remaininStopOverPoint'];
        }
    } else {
        $returnArr['stop_over_point_history'] = array();
    }
    //}
    /* ==========================STOP OVER POINT END======================= */
    ### Display Trip Organization Reason ###
    $returnArr['tUserComment'] = $tUserComment;
    $returnArr['vVehicleType'] = $vVehicleType;
    $returnArr['eIconType'] = $eIconType;
    $vCategoryName = "";
    if (isset($vVehicleCategoryData[0]['vCategory'])) {
        $vCategoryName = $vVehicleCategoryData[0]['vCategory'];
    }
    $returnArr['vVehicleCategory'] = $vCategoryName;
    $returnArr['TripTime'] = $TripTime;
    $returnArr['ConvertedTripRequestDate'] = $tTripRequestDate;
    $returnArr['FormattedTripDate'] = $tTripRequestDate;
    $returnArr['tTripRequestDateOrig'] = $tTripRequestDateOrig;
    $returnArr['tTripRequestDate'] = $tTripRequestDate;
    $returnArr['TripTimeInMinutes'] = $totalTime;
    $returnArr['TripRating'] = $TripRating;
    $returnArr['CurrencySymbol'] = $currencySymbol;
    $returnArr['TripFare'] = formatNum($iFare * $priceRatio);
    $returnArr['iTripId'] = "";
    if (isset($tripData[0]['iTripId'])) {
        $returnArr['iTripId'] = $tripData[0]['iTripId'];
    }
    $returnArr['vTripPaymentMode'] = "";
    if (isset($tripData[0]['vTripPaymentMode'])) {
        $returnArr['vTripPaymentMode'] = $tripData[0]['vTripPaymentMode'];
    }
    $returnArr['eType'] = "";
    if (isset($tripData[0]['eType'])) {
        $returnArr['eType'] = $tripData[0]['eType'];
    }
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
    $iFare_Detail_Earning =$iFare_Subtotal= 0;
    if ($eUserType == "Passenger") {
        $iFare = $iFare;
    } else {
        $iFare_Subtotal = $iFare;
        //$iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $fCommision - $tripData[0]['fTax1'] - $tripData[0]['fTax2'];
        if ($PAGE_MODE != 'Display') {
            $iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $fCommision - $tripData[0]['fTax1'] - $tripData[0]['fTax2'] - $tripData[0]['fOutStandingAmount'] - $tripData[0]['fAddedOutstandingamt'] - $tripData[0]['fHotelCommision'];
        } else {
            $iFare = $tripData[0]['fTripGenerateFare'] + $tripData[0]['fTipPrice'] - $fCommision - $tripData[0]['fTax1'] - $tripData[0]['fTax2'] - $tripData[0]['fOutStandingAmount'] - $tripData[0]['fAddedOutstandingamt'];
        }
        $iFare_Detail_Earning = $tripData[0]['fTripGenerateFare'] - $fCommision - $tripData[0]['fTax1'] - $tripData[0]['fTax2'] - $tripData[0]['fOutStandingAmount'] - $tripData[0]['fAddedOutstandingamt'] - $tripData[0]['fHotelCommision'];
        if ($tripData[0]['iActive'] == "Canceled") {
            $iFare_Detail_Earning = $tripData[0]['fTripGenerateFare'] - $fCommision;
        }
    }
    $surgePrice = $tripData[0]['fNightPrice'];
    if ($tripData[0]['fPickUpPrice'] > 1) {
        $surgePrice = $tripData[0]['fPickUpPrice'];
    }
    $SurgePriceFactor = strval($surgePrice);

    // added for airport surge
    $fAirportPickupSurge = strval($tripData[0]['fAirportPickupSurge']);
    $fAirportDropoffSurge = strval($tripData[0]['fAirportDropoffSurge']);

    // added for airport surge
    $returnArr['TripFareOfMinutes'] = formatNum($tripData[0]['fPricePerMin'] * $priceRatio);
    $returnArr['TripFareOfDistance'] = formatNum($tripData[0]['fPricePerKM'] * $priceRatio);
    $returnArr['iFare'] = formatNum($iFare * $priceRatio);
    $returnArr['iFare_Detail_Earning'] = formatNum($iFare_Detail_Earning * $priceRatio);
    $returnArr['iFare_Subtotal'] = formatNum($iFare_Subtotal * $priceRatio);
    $returnArr['iOriginalFare'] = formatNum($originalFare * $priceRatio);
    //$returnArr['iOriginalFare'] = round($originalFare * $priceRatio,2);
    //added by SP for rounding off currency wise on 26-8-2019 start
    if ($eUserType == "Passenger") {
        //Added By HJ On 09-06-2020 For Optimization Start
        $tblnameUser = "register_user";
        $currencyDataArr = array();
        if(isset($userDetailsArr[$tblnameUser."_".$iMemberId]) && count($userDetailsArr[$tblnameUser."_".$iMemberId]) > 0){
            $vCurrencyPassenger = $userDetailsArr[$tblnameUser."_".$iMemberId][0]['vCurrencyPassenger'];
            if(isset($currencyAssociateArr[$vCurrencyPassenger])){
                $currencyAssociateArr[$vCurrencyPassenger]['vCurrencyPassenger'] = $vCurrencyPassenger;
                $currencyDataArr[] = $currencyAssociateArr[$vCurrencyPassenger];
            }
        }
        //Added By HJ On 09-06-2020 For Optimization End
        if(count($currencyDataArr) > 0){
            $currData = $currencyDataArr;
        }else{
            $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable,cu.Ratio,ru.vCurrencyPassenger FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
            $currData = $obj->MySQLSelect($sqlp);
        }
        
        $vCurrency = $currData[0]['vName'];
        if (isset($tripData[0]['fRoundingAmount']) && !empty($tripData[0]['fRoundingAmount']) && $tripData[0]['fRoundingAmount'] != 0 && $tripData[0]['vCurrencyPassenger'] == $passengerData[0]['vCurrencyPassenger'] && $currData[0]['eRoundingOffEnable'] == "Yes") {

            //$roundingOffTotal_fare_amountArr['finalFareValue'] = formatNum(($iFare) - $tripData[0]['fRoundingAmount']);
            $roundingOffTotal_fare_amountArr['method'] = $tripData[0]['eRoundingType'];
            $roundingOffTotal_fare_amountArr['differenceValue'] = $tripData[0]['fRoundingAmount'];

            $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($iFare, $tripData[0]['fRoundingAmount'], $tripData[0]['eRoundingType'], $currData[0]['Ratio']); ////start
            //$returnArr['roundingOffAmountArr'] = $roundingOffTotal_fare_amount;
            $roundingMethod = "-";
            if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                $roundingMethod = "";
            }
            $roundingOffTotal_fare_amount = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
            $returnArr['TotalFare'] = formatNum($roundingOffTotal_fare_amount);
        } else {
            $returnArr['TotalFare'] = formatNum($iFare * $priceRatio);
        }
    } else {
        //Added By HJ On 09-06-2020 For Optimization register_driver Table Query Start
        $tblnameUser = "register_driver";
        $currencyDataArr = array();
        if(isset($userDetailsArr[$tblnameUser."_".$iMemberId]) && count($userDetailsArr[$tblnameUser."_".$iMemberId]) > 0){
            $vCurrencyDriver = $userDetailsArr[$tblnameUser."_".$iMemberId][0]['vCurrencyDriver'];
            if(isset($currencyAssociateArr[$vCurrencyDriver])){
                $currencyAssociateArr[$vCurrencyDriver]['vCurrencyDriver'] = $vCurrencyDriver;
                $currencyDataArr[] = $currencyAssociateArr[$vCurrencyDriver];
                //echo "<pre>";print_r($currencyDataArr);die;
            }
        }
        //Added By HJ On 09-06-2020 For Optimization register_driver Table Query End
        if(count($currencyDataArr) > 0){
            $currData = $currencyDataArr;
        }else{
            $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, rd.vCurrencyDriver, cu.Ratio FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iMemberId . "'";
            $currData = $obj->MySQLSelect($sqlp);
        }
        $returnArr['TotalFare'] = formatNum($iFare * $priceRatio);
    }
    //echo $returnArr['TotalFare'];exit;
    //$returnArr['TotalFare'] = formatNum($iFare * $priceRatio);
    //added by SP for rounding off currency wise on 26-8-2019 end

    $returnArr['fPricePerKM'] = formatNum($fPricePerKM);
    $returnArr['iBaseFare'] = formatNum($iBaseFare);
    $returnArr['fPricePerMin'] = formatNum($fPricePerMin);
    $returnArr['fCommision'] = formatNum($fCommision * $priceRatio);
    $returnArr['fDistance'] = formatNum($fDistance);
    $returnArr['fDiscount'] = formatNum($fDiscount * $priceRatio);
    $returnArr['fMinFareDiff'] = formatNum($fMinFareDiff);
    $returnArr['fWalletDebit'] = formatNum($fWalletDebit * $priceRatio);
    $returnArr['fWalletAmountAdjusted'] = $currencySymbol . " " . $returnArr['fWalletDebit'];
    $returnArr['fSurgePriceDiff'] = formatNum($fSurgePriceDiff);
    $returnArr['fTripGenerateFare'] = formatNum($fTripGenerateFare);
    $returnArr['TripGenerateFare'] = $currencySymbol . " " . formatNum($fTripGenerateFare);
    $returnArr['eChargeViewShow'] = "No";
    // added for airport surge
    $returnArr['fAirportPickupSurgeAmount'] = formatNum($fAirportPickupSurgeAmount);
    $returnArr['fAirportDropoffSurgeAmount'] = formatNum($fAirportDropoffSurgeAmount);


    if ($fTripGenerateFare > 0) {
        $returnArr['eChargeViewShow'] = "Yes";
    }
    $returnArr['eWalletAmtAdjusted'] = "No";
    if ($fWalletDebit > 0) {
        $returnArr['eWalletAmtAdjusted'] = "Yes";
    }
    $returnArr['fFlatTripPrice'] = formatNum($fFlatTripPrice);
    $returnArr['fWaitingFees'] = formatNum($fWaitingFees);
    $returnArr['fTripHoldPrice'] = formatNum($fTripHoldPrice); // Added By HJ For Intransit Amount On 28-12-2018
    $returnArr['fOutStandingAmount'] = formatNum($fOutStandingAmount);
    $returnArr['fAddedOutstandingamt'] = formatNum($fAddedOutstandingamt);
    //added for hotel
    $returnArr['fHotelCommision'] = formatNum($fHotelCommision);
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
    $returnArr['fWaitingFees'] = formatNum($fWaitingFees);
    $returnArr['fTax1'] = formatNum($fTax1);
    $returnArr['fTax2'] = formatNum($fTax2);
    $returnArr['eSystem'] = "";
    if (isset($tripData[0]['eSystem'])) {
        $returnArr['eSystem'] = $tripData[0]['eSystem'];
    }
    //echo "<pre>"; print_r($tripData); die;
    if(count($driverDetails) > 0){
        //Data Found
        //echo "<pre>"; print_r($driverDetails); die;
    }else{
        //Added By HJ On 17-06-2020 For Optimize register_driver_ Table Query Start
        if(isset($userDetailsArr["register_driver_".$iDriverId])){
            $driverDetails = $userDetailsArr["register_driver_".$iDriverId];
        }else{
            $driverDetails = get_value('register_driver', '*', 'iDriverId', $iDriverId);
        }
        //Added By HJ On 17-06-2020 For Optimize register_driver_ Table Query End
    }
    $driverDetails[0]['vImage'] = ($driverDetails[0]['vImage'] != "" && $driverDetails[0]['vImage'] != "NONE") ? "3_" . $driverDetails[0]['vImage'] : "";
    $driverDetails[0]['vPhone'] = '+' . $driverDetails[0]['vCode'] . $driverDetails[0]['vPhone'];
    $returnArr['DriverDetails'] = $driverDetails[0];
    $iUserId = $tripData[0]['iUserId'];
    
    //Added By HJ On 09-06-2020 For Optimization Start
    $tblName = "register_user";
    if(isset($userDetailsArr[$tblName."_".$iUserId]) && count($userDetailsArr[$tblName."_".$iUserId]) > 0){
        $passengerDetails = $userDetailsArr[$tblName."_".$iUserId];
    }else{
        $passengerDetails = get_value($tblName, '*', 'iUserId', $iUserId);
        $userDetailsArr[$tblName."_".$iUserId] = $passengerDetails;
    }
    //Added By HJ On 09-06-2020 For Optimization End
    $passengerDetails[0]['vImgName'] = ($passengerDetails[0]['vImgName'] != "" && $passengerDetails[0]['vImgName'] != "NONE") ? "3_" . $passengerDetails[0]['vImgName'] : "";
    $passengerDetails[0]['vPhone'] = '+' . $passengerDetails[0]['vPhoneCode'] . $passengerDetails[0]['vPhone'];
    $returnArr['PassengerDetails'] = $passengerDetails[0];

    if ($eUserType == "Passenger") {
        $returnArr['vImage'] = $driverDetails[0]['vImage'];
    } else {
        $returnArr['vImage'] = $passengerDetails[0]['vImgName'];
    }
    //$TaxArr = getMemberCountryTax($iUserId, "Passenger");
    //$fUserCountryTax1 = $TaxArr['fTax1'];
    //$fUserCountryTax2 = $TaxArr['fTax2'];
    $fUserCountryTax1 = $tripData[0]['fTax1Percentage'];
    $fUserCountryTax2 = $tripData[0]['fTax2Percentage'];
    $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
    //Added By HJ On 17-06-2020 For Optimize driver_vehicle Table Query Start
    if(isset($driverVehicleDataArr['driver_vehicle_'.$iDriverVehicleId])){
        $vehicleDetailsArr = $driverVehicleDataArr['driver_vehicle_'.$iDriverVehicleId];
    }else{
        $vehicleDetailsArr = $obj->MySQLSelect("SELECT ma.vMake,mo.vTitle,dv.* FROM driver_vehicle as dv LEFT JOIN make as ma ON dv.iMakeId = ma.iMakeId LEFT JOIN model as mo ON dv.iModelId = mo.iModelId WHERE dv.iDriverVehicleId = '" . $iDriverVehicleId . "'");
        $driverVehicleDataArr['driver_vehicle_'.$iDriverVehicleId] = $vehicleDetailsArr;
    }
    //Added By HJ On 17-06-2020 For Optimize driver_vehicle Table Query End
    $vehicleDetailsArr[0]['vModel'] = "";
    if (isset($vehicleDetailsArr[0]['vTitle'])) {
        $vehicleDetailsArr[0]['vModel'] = $vehicleDetailsArr[0]['vTitle'];
    }
    //if ($eUserType == "Passenger" && $tripData[0]['eType'] == "UberX") {
    if ($tripData[0]['eType'] == "UberX") {
        //$ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");
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
                //$vVehicleFare = formatNum($vVehicleFare);
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
                $tripFareDetailsArr[][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }
            // added for airport surge
            if ($fAirportPickupSurgeAmount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $fAirportPickupSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportPickupSurgeAmount'] : "--";
                $i++;
            }

            if ($fAirportDropoffSurgeAmount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $fAirportDropoffSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportDropoffSurgeAmount'] : "--";
                $i++;
            }

            if ($fWaitingFees > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_WAITING_FEE_TXT'] . " (" . $waitingTime . " )"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fWaitingFees'] : $currencySymbol . $returnArr['fWaitingFees'];
                $i++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice Start
            if ($fTripHoldPrice > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_INTRANSIT_TRIP_HOLD_FEE'] . " (" . $totalTime_hold . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTripHoldPrice'] : $currencySymbol . $returnArr['fTripHoldPrice'];
                $i++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice End
            if ($fDiscount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                $i++;
            }
            if ($fOutStandingAmount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fOutStandingAmount'] : "--";
                $i++;
            }

            if ($fOutStandingAmount == 0 && $fAddedOutstandingamt > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $generalobj->formateNumAsPerCurrency($returnArr['fAddedOutstandingamt'], $currencycode) : "--";
                $i++;
            }

            if ($extraPersonCharge > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_EXTRA_PERSON_CHARGE']] = ($iActive != "Canceled") ? $currencySymbol . $generalobj->setTwoDecimalPoint($extraPersonCharge) : "--";
                $i++;
            }

            if ($fTax1 > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax1'] : "--";
                $i++;
            }
            if ($fTax2 > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax2'] : "--";
                $i++;
            }
            if ($fHotelCommision > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_HOTEL_SERVICE_CHARGE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fHotelCommision'] : "--";
                $i++;
            }
            if ($fWalletDebit > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                $i++;
            }
            $tripFareDetailsArr[][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare'] : "--";
        } /* elseif($eFlatTrip == "Yes" && $iActive == "Canceled"){
          $tripFareDetailsArr[0][$languageLabelsArr['LBL_Total_Fare']] = $currencySymbol." 0.00";
          } */ elseif ($fCancelPrice > 0 || ($iActive == "Canceled" && $fWalletDebit > 0)) {
            if ($fWalletDebit > $CancelPrice) {
                $CancelPrice = $fWalletDebit + $fCancelPrice - $fWaitingFees - $fTripHoldPrice; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                $subtotal = formatNum($fCancelPrice);
            } else {
                $CancelPrice = $fCancelPrice - $fWalletDebit - $fTripHoldPrice; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                $subtotal = formatNum($fCancelPrice + $fWaitingFees + $fTripHoldPrice + $fWalletDebit);
            }
            $tripFareDetailsArr[][$languageLabelsArr['LBL_CANCELLATION_FEE']] = $currencySymbol . formatNum($CancelPrice);
            $ki = 0;
            if ($fWaitingFees > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_WAITING_FEE_TXT'] . " (" . $waitingTime . " )"] = $currencySymbol . $returnArr['fWaitingFees'];
                $ki++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice Start
            if ($fTripHoldPrice > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_INTRANSIT_TRIP_HOLD_FEE'] . " (" . $totalTime_hold . ")"] = $currencySymbol . $returnArr['fTripHoldPrice'];
                $ki++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice End
            if ($fWalletDebit > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "- " . $currencySymbol . $returnArr['fWalletDebit'];
                $ki++;
            }
            $tripFareDetailsArr[][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $currencySymbol . $subtotal;
        } else {
            $i = 0;
            $countUfx = 0;
            if ($tripData[0]['eType'] == "UberX") {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $returnArr['vVehicleCategory'] . "-" . $returnArr['vVehicleType'];
                $countUfx = 1;
            }
            if ($tripData[0]['eFareType'] == "Regular") {
                //$tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vVehicleType . " " . $currencySymbol . $returnArr['iBaseFare'];
                /* Changes For Rental */
                if ($tripData[0]['iRentalPackageId'] > 0) {
                    $rentalData = getRentalData($tripData[0]['iRentalPackageId']);
                    $tripData[0]['vPackageName'] = $rentalData[0]['vPackageName_' . $userlangcode];
                    $tripFareDetailsArr[][$tripData[0]['vPackageName'] . " " . $languageLabelsArr['LBL_RENTAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iBaseFare'] : "--";
                    if ($countUfx == 1) {
                        $i++;
                    }
                    $TripKilometer = getVehicleCountryUnit($tripData[0]['iVehicleTypeId'], $rentalData[0]['fKiloMeter']);
                    if ($eUnit == "Miles") {
                        $TripKilometer = round($TripKilometer * 0.621371, 2);
                    }
                    if ($fDistance > $TripKilometer) {
                        $extradistance = $fDistance - $TripKilometer;
                    } else {
                        $extradistance = 0;
                    }
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_ADDITIONSL_DISTANCE_TXT'] . " (" . $extradistance . " " . $DisplayDistanceTxt . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                    $i++;
                    $Extra_Time = calculateAdditionalTime($tripData[0]['tStartDate'], $tripData[0]['tEndDate'], $rentalData[0]['fHour'], $userlangcode);
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_ADDITIONAL_TIME_TXT'] . " (" . $Extra_Time . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                    $i++;
                } else {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iBaseFare'] : "--";
                    if ($countUfx == 1) {
                        $i++;
                    }
                    //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $languageLabelsArr['LBL_KM_DISTANCE_TXT'] . ")"] = ($iActive != "Canceled")?$currencySymbol . $returnArr['TripFareOfDistance']:"--";
                    if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes") {
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_DISTANCE_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                        $i++;
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_TIME_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                        $i++;
                    } else {
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $DisplayDistanceTxt . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                        $i++;
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                        $i++;
                    }
                }
                /* Changes For Rental */
            } else if ($tripData[0]['eFareType'] == "Fixed") {
                //  $tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = $currencySymbol . ($fTripGenerateFare - $fSurgePriceDiff - $fMinFareDiff);
                //Added By HJ On 04-01-2019 For Set Vehicle Type Wise Fare Details Start
                if (isset($tripData[0]['tVehicleTypeFareData']) && $tripData[0]['tVehicleTypeFareData'] != "" && $SERVICE_PROVIDER_FLOW == "Provider") {

                    $tVehicleTypeFareData = (array) json_decode($tripData[0]['tVehicleTypeFareData']);
                    $tVehicleTypeFareData = (array) $tVehicleTypeFareData['FareData'];

                    $iVehicleTypeIds_str = "";

                    for ($fd = 0; $fd < count($tVehicleTypeFareData); $fd++) {
                        $iVehicleTypeIds_str = $iVehicleTypeIds_str == "" ? $tVehicleTypeFareData[$fd]->id : $iVehicleTypeIds_str . "," . $tVehicleTypeFareData[$fd]->id;
                    }

                    $sql_vehicleTypeNames = "SELECT vt.vVehicleType_" . $userlangcode . " as vVehicleType, (SELECT vcs.vCategory_" . $userlangcode . " FROM " . $sql_vehicle_category_table_name . " as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentCategoryName FROM vehicle_type as vt, " . $sql_vehicle_category_table_name . " as vc WHERE vt.iVehicleTypeId IN ($iVehicleTypeIds_str) AND vc.iVehicleCategoryId = vt.iVehicleCategoryId";
                    $data_vehicleTypeNames = $obj->MySQLSelect($sql_vehicleTypeNames);
                    // print_r($data_vehicleTypeNames);exit;
                    $getCategoryName = "";

                    for ($fd = 0; $fd < count($tVehicleTypeFareData); $fd++) {
                        $eAllowQty = $tVehicleTypeFareData[$fd]->eAllowQty;
                        $typeQty = $tVehicleTypeFareData[$fd]->qty;
                        if ($typeQty < 1) {
                            $typeQty = 1;
                        }
                        $tVehicleTypeFareData[$fd]->amount = $tVehicleTypeFareData[$fd]->amount * $typeQty;
                        $typeAmount = $currencySymbol . formatNum($tVehicleTypeFareData[$fd]->amount * $priceRatio);

                        $iVehicleTypeIds_str = $iVehicleTypeIds_str == "" ? $tVehicleTypeFareData[$fd]->id : $iVehicleTypeIds_str . "," . $tVehicleTypeFareData[$fd]->id;

                        // $typeTitle = $tVehicleTypeFareData[$fd]->title;
                        $typeTitle = $data_vehicleTypeNames[$fd]['vVehicleType'];

                        $getCategoryName = $data_vehicleTypeNames[$fd]['ParentCategoryName'];

                        $qtyDisplay = "";
                        if ($eAllowQty == "Yes") {
                            $qtyDisplay = " (x" . $typeQty . ")";
                        }
                        if ($typeTitle != $languageLabelsArr['LBL_SUBTOTAL_TXT']) {
                            $tripFareDetailsArr[][$typeTitle . $qtyDisplay] = $typeAmount;
                            $i++;
                        }
                    }

                    $returnArr['vVehicleCategory'] = $getCategoryName;
                } else {
                    $vVehicleFare = ($tripData[0]['iFare'] * $priceRatio) + $fDiscount + $fWalletDebit + $fDriverDiscount - $fVisitFee - $fMaterialFee - $fMiscFee - $fOutStandingAmount - $fWaitingFees - $fTax1 - $fTax2 - $fTripHoldPrice; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                    $SERVICE_COST = ($tripData[0]['iQty'] > 1) ? $tripData[0]['iQty'] . ' X ' . $currencySymbol . formatNum($vVehicleFare) : $currencySymbol . formatNum($vVehicleFare);

                    //added by SP for fly stations on 20-08-2019 start
                    if (!empty($tripData[0]['iFromStationId']) && !empty($tripData[0]['iToStationId'])) {
                        if ($fSurgePriceDiff == 0) {
                            $SERVICE_COST = ($tripData[0]['iQty'] > 1) ? $tripData[0]['iQty'] . ' X ' . $currencySymbol . formatNum($vVehicleFare) : $currencySymbol . formatNum($vVehicleFare);
                            $tripFareDetailsArr[][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $SERVICE_COST : "--";
                            if ($countUfx == 1) {
                                $i++;
                            }
                        }
                    } else {
                        //added by SP for fly stations on 20-08-2019 end
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $SERVICE_COST : "--";
                        if ($countUfx == 1) {
                            $i++;
                        }
                    }
                }
                //Added By HJ On 04-01-2019 For Set Vehicle Type Wise Fare Details End
            } else if ($tripData[0]['eFareType'] == "Hourly") {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                if ($countUfx == 1) {
                    $i++;
                }
            }
            if ($extraPersonCharge > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_EXTRA_PERSON_CHARGE']] = ($iActive != "Canceled") ? $currencySymbol . $generalobj->setTwoDecimalPoint($extraPersonCharge) : "--";
                $i++;
            }

            if ($fSurgePriceDiff > 0) {
                //added by SP for fly stations on 20-08-2019 start
                if (!empty($tripData[0]['iFromStationId']) && !empty($tripData[0]['iToStationId'])) {
                    
                } else {
                    $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                    $i++;
                }
                //added by SP for fly stations on 20-08-2019 end
                //$tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                //$i++;
                $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fWaitingFees - $fOutStandingAmount - $fAddedOutstandingamt - $fHotelCommision - $fAirportPickupSurgeAmount - $fAirportDropoffSurgeAmount - $fTripHoldPrice - $fMaterialFee - $fMiscFee + $fDriverDiscount; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                if ($eTollSkipped == "No") {
                    $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fWaitingFees - $fOutStandingAmount - $fAddedOutstandingamt - $fTollPrice - $fHotelCommision - $fAirportPickupSurgeAmount - $fAirportDropoffSurgeAmount - $fTripHoldPrice; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                }
                $normalfare = formatNum($normalfare);
                //added by SP for fly stations on 20-08-2019 start
                if (!empty($tripData[0]['iFromStationId']) && !empty($tripData[0]['iToStationId'])) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                } else {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                }
                //added by SP for fly stations on 20-08-2019 end
                //$tripFareDetailsArr[][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                $i++;
                $tripFareDetailsArr[][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }

            if ($fVisitFee > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_VISIT_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fVisitFee'] : "--";
                $i++;
            }

            // added for airport surge
            if ($fSurgePriceDiff == 0 && ($fAirportPickupSurgeAmount > 0 || $fAirportDropoffSurgeAmount > 0)) {
                $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                $i++;
                $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fWaitingFees - $fOutStandingAmount - $fAddedOutstandingamt - $fHotelCommision - $fAirportPickupSurgeAmount - $fAirportDropoffSurgeAmount - $fTripHoldPrice;
                if ($eTollSkipped == "No") {
                    $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fWaitingFees - $fOutStandingAmount - $fAddedOutstandingamt - $fTollPrice - $fHotelCommision - $fAirportPickupSurgeAmount - $fAirportDropoffSurgeAmount - $fTripHoldPrice;
                }
                $normalfare = formatNum($normalfare);
                $tripFareDetailsArr[][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                $i++;
                if ($fAirportPickupSurgeAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $fAirportPickupSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportPickupSurgeAmount'] : "--";
                    $i++;
                }

                if ($fAirportDropoffSurgeAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $fAirportDropoffSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportDropoffSurgeAmount'] : "--";
                    $i++;
                }
            } else {
                if ($fAirportPickupSurgeAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $fAirportPickupSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportPickupSurgeAmount'] : "--";
                    $i++;
                }

                if ($fAirportDropoffSurgeAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $fAirportDropoffSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportDropoffSurgeAmount'] : "--";
                    $i++;
                }
            }
            if ($fMaterialFee > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_MATERIAL_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMaterialFee'] : "--";
                $i++;
            }

            if ($fMiscFee > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_MISC_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMiscFee'] : "--";
                $i++;
            }

            if ($fDriverDiscount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDriverDiscount'] : "--";
                $i++;
            }
            if ($fWaitingFees > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_WAITING_FEE_TXT'] . " (" . $waitingTime . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fWaitingFees'] : $currencySymbol . $returnArr['fWaitingFees'];
                $i++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice Start
            if ($fTripHoldPrice > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_INTRANSIT_TRIP_HOLD_FEE'] . " (" . $totalTime_hold . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTripHoldPrice'] : $currencySymbol . $returnArr['fTripHoldPrice'];
                $i++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice End
            if ($fMinFareDiff > 0) {
                //$minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = $fTripGenerateFare - $fOutStandingAmount - $fAddedOutstandingamt - $fTax1 - $fTax2 - $fHotelCommision;
                if ($eTollSkipped == "No") {
                    $minimamfare = $fTripGenerateFare - $fTollPrice - $fOutStandingAmount - $fAddedOutstandingamt - $fTax1 - $fTax2 - $fHotelCommision;
                }
                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[][$currencySymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = $currencySymbol . $returnArr['fMinFareDiff'];
                $returnArr['TotalMinFare'] = ($iActive != "Canceled") ? $minimamfare : "--";
                $i++;
            }
            if ($eTollSkipped == "No") {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_TOLL_PRICE_TOTAL']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTollPrice'] : "--";
                $i++;
            }
            if ($fDiscount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                $i++;
            }
            if ($fOutStandingAmount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fOutStandingAmount'] : "--";
                $i++;
            }
            if ($fOutStandingAmount == 0 && $fAddedOutstandingamt > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $generalobj->formateNumAsPerCurrency($returnArr['fAddedOutstandingamt'], $currencycode) : "--";
                $i++;
            }
            /* if ($fTipPrice > 0) {
              $tripFareDetailsArr[][$languageLabelsArr['LBL_TIP_AMOUNT']] = ($iActive != "Canceled")?$currencySymbol . $returnArr['fTipPrice']:"--";
              $i++;
              } */

            if ($fTax1 > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax1'] : "--";
                $i++;
            }
            if ($fTax2 > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax2'] : "--";
                $i++;
            }
            if ($fHotelCommision > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_HOTEL_SERVICE_CHARGE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fHotelCommision'] : "--";
                $i++;
            }
            if ($fWalletDebit > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                $i++;
            }

            $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
            $i++;
            $tripFareDetailsArr[][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare'] : $currencySymbol . $returnArr['fWaitingFees'];

            //added by SP for rounding off currency wise on 26-8-2019 start
            //if($userType == "Driver"){
            //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_driver as rd LEFT JOIN country as co ON rd.vCountry = co.vCountryCode  WHERE rd.iDriverId = '" . $iUserId . "'";
            //    $countryData = $obj->MySQLSelect($sqlp);
            //    $vCountry = $countryData[0]['vCountryCode'];
            //}else{
            //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_user as ru LEFT JOIN country as co ON ru.vCountry = co.vCountryCode WHERE ru.iUserId = '" . $iUserId . "'";
            //    $countryData = $obj->MySQLSelect($sqlp);
            //    $vCountry = $countryData[0]['vCountryCode'];
            //}

            /* if($eUserType == "Driver"){
              $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, rd.vCurrencyDriver FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iMemberId . "'";
              $currData = $obj->MySQLSelect($sqlp);
              $vCurrency = $currData[0]['vName'];
              $samecur = ($tripData[0]['vCurrencyDriver']==$passengerData[0]['vCurrencyDriver']) ? 1 : 0;
              } else {
              $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
              $currData = $obj->MySQLSelect($sqlp);
              $vCurrency = $currData[0]['vName'];
              $samecur = ($tripData[0]['vCurrencyPassenger']==$passengerData[0]['vCurrencyPassenger']) ? 1 : 0;
              }

              if($samecur==1 && $currData[0]['eRoundingOffEnable'] == "Yes" && isset($tripData[0]['fRoundingAmount']) && !empty($tripData[0]['fRoundingAmount']) && $tripData[0]['fRoundingAmount']!=0) {

              $roundingOffTotal_fare_amountArr['finalFareValue'] = formatNum(($returnArr['iFare']) - $tripData[0]['fRoundingAmount']);
              $roundingOffTotal_fare_amountArr['method'] = $tripData[0]['eRoundingType'];
              $roundingOffTotal_fare_amountArr['differenceValue'] = $tripData[0]['fRoundingAmount'];

              if($roundingOffTotal_fare_amountArr['method'] == "Addition"){
              $roundingMethod = "";
              }else{
              $roundingMethod = "-";
              }
              $roundingOffTotal_fare_amount = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
              $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";
              //$Fare_data[0]['total_fare'] = $roundingOffTotal_fare_amount;


              $i++;
              $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = ($iActive != "Canceled") ? $roundingMethod." ". $currencySymbol . "". $rounding_diff : $currencySymbol . $returnArr['fWaitingFees'];
              $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
              $i++;
              $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol."". $roundingOffTotal_fare_amount : $currencySymbol . $returnArr['fWaitingFees'];

              } */

            if ($eUserType == "Driver") {
                if(count($currData) > 0){
                    //Data Found From Global Array
                }else{
                    $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, rd.vCurrencyDriver, cu.ratio FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iMemberId . "'";
                    $currData = $obj->MySQLSelect($sqlp);
                }
                $vCurrency = $currData[0]['vName'];
                $samecur = ($tripData[0]['vCurrencyDriver'] == $driverData[0]['vCurrencyDriver'] && $tripData[0]['vCurrencyDriver'] == $tripData[0]['vCurrencyPassenger']) ? 1 : 0;
            } else {
                if(count($currData) > 0){
                    //Data Found From Global Array
                }else{
                   $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger, cu.Ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
                    $currData = $obj->MySQLSelect($sqlp); 
                }
                $vCurrency = $currData[0]['vName'];
                $samecur = ($tripData[0]['vCurrencyPassenger'] == $passengerData[0]['vCurrencyPassenger']) ? 1 : 0;
            }

            //if($currData[0]['eRoundingOffEnable'] == "Yes"){
            if (isset($tripData[0]['fRoundingAmount']) && !empty($tripData[0]['fRoundingAmount']) && $tripData[0]['fRoundingAmount'] != 0 && $samecur == 1 && $currData[0]['eRoundingOffEnable'] == "Yes") {

                //$roundingOffTotal_fare_amountArr = getRoundingOffAmount($returnArr['iFare'],$vCurrency);
                //$roundingOffTotal_fare_amountArr['finalFareValue'] = formatNum(($iFare) - $tripData[0]['fRoundingAmount']);
                $roundingOffTotal_fare_amountArr['method'] = $tripData[0]['eRoundingType'];
                $roundingOffTotal_fare_amountArr['differenceValue'] = $tripData[0]['fRoundingAmount'];

                $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($iFare, $tripData[0]['fRoundingAmount'], $tripData[0]['eRoundingType'], $currData[0]['Ratio']); ////start
                //$returnArr['roundingOffAmountArr'] = $roundingOffTotal_fare_amount;
                if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                    $roundingMethod = "";
                } else {
                    $roundingMethod = "-";
                }
                $roundingOffTotal_fare_amount = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
                $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";
                //$Fare_data[0]['total_fare'] = $roundingOffTotal_fare_amount;


                $i++;
                $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = ($iActive != "Canceled") ? $roundingMethod . " " . $currencySymbol . "" . $rounding_diff : $currencySymbol . $returnArr['fWaitingFees'];
                $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                $i++;
                $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . "" . formatNum($roundingOffTotal_fare_amount) : $currencySymbol . $returnArr['fWaitingFees'];
            }
            //added by SP for rounding off currency wise on 26-8-2019 end
        }
        /* if(isset($roundingOffTotal_fare_amount) ){
          $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $roundingOffTotal_fare_amount : "--";
          } else {
          $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";
          } */
        $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";

        $returnArr['FareDetailsNewArr'] = $tripFareDetailsArr;
        $FareDetailsArr = array();
        foreach ($tripFareDetailsArr as $data) {
            $FareDetailsArr = array_merge($FareDetailsArr, $data);
        }
        //print_r($tripFareDetailsArr);die;
        $returnArr['FareDetailsArr'] = $FareDetailsArr;
        $returnArr['HistoryFareDetailsNewArr'] = $tripFareDetailsArr;
        if ($tripData[0]['eType'] == "UberX") {
            //if($fCancelPrice == 0){
            if ($iActive != "Canceled") {
                array_splice($returnArr['HistoryFareDetailsNewArr'], 0, 1);
            }
            if ($PAGE_MODE == "DISPLAY") {
                array_splice($returnArr['FareDetailsNewArr'], 0, 1);
            }
        }
    } else {
        $tripFareDetailsArr = array();
        if ($eFlatTrip == "Yes" && $iActive != "Canceled") {
            $i = 0;
            $tripFareDetailsArr[$i][$languageLabelsArr['LBL_FLAT_TRIP_FARE_TXT']] = $currencySymbol . " " . $returnArr['fFlatTripPrice'];
            if ($fSurgePriceDiff > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }
            // added for airport surge
            if ($fAirportPickupSurgeAmount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $fAirportPickupSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportPickupSurgeAmount'] : "--";
                $i++;
            }

            if ($fAirportDropoffSurgeAmount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $fAirportDropoffSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportDropoffSurgeAmount'] : "--";
                $i++;
            }

            if ($PAGE_MODE == "DISPLAY") {
                if ($fWaitingFees > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_WAITING_FEE_TXT'] . " (" . $waitingTime . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fWaitingFees'] : "--";
                    $i++;
                }
                //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice Start
                if ($fTripHoldPrice > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_INTRANSIT_TRIP_HOLD_FEE'] . " (" . $totalTime_hold . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTripHoldPrice'] : "--";
                    $i++;
                }
                //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice End
                if ($fDiscount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                    $i++;
                }
                if ($fOutStandingAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fOutStandingAmount'] : "--";
                    $i++;
                }

                if ($fOutStandingAmount == 0 && $fAddedOutstandingamt > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $generalobj->formateNumAsPerCurrency($returnArr['fAddedOutstandingamt'], $currencycode) : "--";
                    $i++;
                }

                if ($fWalletDebit > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                    $i++;
                }
                // add hotel web
                if ($fHotelCommision > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_HOTEL_SERVICE_CHARGE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fHotelCommision'] : "--";
                    $i++;
                }

                if ($extraPersonCharge > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_EXTRA_PERSON_CHARGE']] = ($iActive != "Canceled") ? $currencySymbol . $generalobj->setTwoDecimalPoint($extraPersonCharge) : "--";
                    $i++;
                }

                if ($fTax1 > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax1'] : "--";
                    $i++;
                }
                if ($fTax2 > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax2'] : "--";
                    $i++;
                }
            } else {
                if ($fWaitingFees > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_WAITING_FEE_TXT'] . " (" . $waitingTime . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fWaitingFees'] : "--";
                    $i++;
                }
                //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice Start
                if ($fTripHoldPrice > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_INTRANSIT_TRIP_HOLD_FEE'] . " (" . $totalTime_hold . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTripHoldPrice'] : "--";
                    $i++;
                }
                //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice End
                /* if($fOutStandingAmount > 0) {
                  $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled")?"- ".$currencySymbol . $returnArr['fOutStandingAmount']:"--";
                  $i++;
                  } */
                if ($fOutStandingAmount > 0 && $tripData[0]['vTripPaymentMode'] == "Cash") {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fOutStandingAmount'] : "--";
                    $i++;
                    $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                    $i++;
                    $totfare_for_earn = $fTripGenerateFare - $fTax1 - $fTax2 - $fHotelCommision;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $totfare_for_earn : "--";
                    $i++;
                    //$tripFareDetailsArr[$i + 1]['eDisplaySeperator'] = "Yes"; $i++;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fOutStandingAmount'] : "--";
                    $i++;
                }

                if ($fOutStandingAmount == 0 && $fAddedOutstandingamt > 0 && $tripData[0]['vTripPaymentMode'] == "Cash") {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $generalobj->formateNumAsPerCurrency($returnArr['fAddedOutstandingamt'], $currencycode) : "--";
                    $i++;
                    $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                    $i++;
                    $totfare_for_earn = $fTripGenerateFare - $fTax1 - $fTax2 - $fHotelCommision;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $generalobj->formateNumAsPerCurrency($totfare_for_earn, $currencycode) : "--";
                    $i++;
                    //$tripFareDetailsArr[$i + 1]['eDisplaySeperator'] = "Yes"; $i++;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? "- " . $generalobj->formateNumAsPerCurrency($returnArr['fAddedOutstandingamt'], $currencycode) : "--";
                    $i++;
                }


                //added by SP for rounding off currency wise on 26-8-2019 start
                // rounding off total amount code starts
                //if($userType == "Driver"){
                //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_driver as rd LEFT JOIN country as co ON rd.vCountry = co.vCountryCode  WHERE rd.iDriverId = '" . $iUserId . "'";
                //    $countryData = $obj->MySQLSelect($sqlp);
                //    $vCountry = $countryData[0]['vCountryCode'];
                //}else{
                //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_user as ru LEFT JOIN country as co ON ru.vCountry = co.vCountryCode WHERE ru.iUserId = '" . $iUserId . "'";
                //    $countryData = $obj->MySQLSelect($sqlp);
                //    $vCountry = $countryData[0]['vCountryCode'];
                //}

                /* if($eUserType == "Driver"){
                  $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, rd.vCurrencyDriver FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iMemberId . "'";
                  $currData = $obj->MySQLSelect($sqlp);
                  $vCurrency = $currData[0]['vName'];
                  $samecur = ($tripData[0]['vCurrencyDriver']==$passengerData[0]['vCurrencyDriver']) ? 1 : 0;
                  } else {
                  $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
                  $currData = $obj->MySQLSelect($sqlp);
                  $vCurrency = $currData[0]['vName'];
                  $samecur = ($tripData[0]['vCurrencyPassenger']==$passengerData[0]['vCurrencyPassenger']) ? 1 : 0;
                  }

                  if($samecur==1 && $currData[0]['eRoundingOffEnable'] == "Yes" && isset($tripData[0]['fRoundingAmount']) && !empty($tripData[0]['fRoundingAmount']) && $tripData[0]['fRoundingAmount']!=0) {

                  $roundingOffTotal_fare_amountArr['finalFareValue'] = formatNum(($returnArr['iFare']) - $tripData[0]['fRoundingAmount']);
                  $roundingOffTotal_fare_amountArr['method'] = $tripData[0]['eRoundingType'];
                  $roundingOffTotal_fare_amountArr['differenceValue'] = $tripData[0]['fRoundingAmount'];

                  if($roundingOffTotal_fare_amountArr['method'] == "Addition"){
                  $roundingMethod = "";
                  }else{
                  $roundingMethod = "-";
                  }
                  $roundingOffTotal_fare_amount = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
                  $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";
                  //$Fare_data[0]['total_fare'] = $roundingOffTotal_fare_amount;


                  $i++;
                  $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = ($iActive != "Canceled") ? $roundingMethod." ". $currencySymbol . "". $rounding_diff : $currencySymbol . $returnArr['fWaitingFees'];
                  $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                  $i++;
                  $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol."". $roundingOffTotal_fare_amount : $currencySymbol . $returnArr['fWaitingFees'];

                  } else {
                  $returnArr['TotalFare'] = formatNum($iFare * $priceRatio);
                  } */


                if ($eUserType == "Driver") {
                    if(count($currData) > 0){
                        //Data Found From Global Array
                    }else{
                        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, rd.vCurrencyDriver,cu.ratio FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iMemberId . "'";
                        $currData = $obj->MySQLSelect($sqlp);
                    }
                    $vCurrency = $currData[0]['vName'];
                    $samecur = ($tripData[0]['vCurrencyDriver'] == $driverData[0]['vCurrencyDriver'] && $tripData[0]['vCurrencyDriver'] == $tripData[0]['vCurrencyPassenger']) ? 1 : 0;
                } else {
                    if(count($currData) > 0){
                        //Data Found From Global Array
                    }else{
                        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger,cu.ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
                        $currData = $obj->MySQLSelect($sqlp);
                    }
                    $vCurrency = $currData[0]['vName'];
                    $samecur = ($tripData[0]['vCurrencyPassenger'] == $passengerData[0]['vCurrencyPassenger']) ? 1 : 0;
                }

                //if($currData[0]['eRoundingOffEnable'] == "Yes"){
                if (isset($tripData[0]['fRoundingAmount']) && !empty($tripData[0]['fRoundingAmount']) && $tripData[0]['fRoundingAmount'] != 0 && $samecur == 1 && $currData[0]['eRoundingOffEnable'] == "Yes") {

                    $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($returnArr['iFare'], $tripData[0]['fRoundingAmount'], $tripData[0]['eRoundingType'], $currData[0]['Ratio']); ////start
                    //$roundingOffTotal_fare_amountArr = getRoundingOffAmount($returnArr['iFare'],$vCurrency);
                    //$returnArr['roundingOffAmountArr'] = $roundingOffTotal_fare_amount;
                    if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                        $roundingMethod = "";
                    } else {
                        $roundingMethod = "-";
                    }
                    $roundingOffTotal_fare_amount = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
                    $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";
                    //$Fare_data[0]['total_fare'] = $roundingOffTotal_fare_amount;


                    $i++;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = ($iActive != "Canceled") ? $roundingMethod . " " . $currencySymbol . "" . $rounding_diff : $currencySymbol . $returnArr['fWaitingFees'];
                    $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";

                    $i++;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . "" . $roundingOffTotal_fare_amount : $currencySymbol . $returnArr['fWaitingFees'];
                }

                //added by SP for rounding off currency wise on 26-8-2019 end
            }
        } elseif ($fCancelPrice > 0 || ($iActive == "Canceled" && $fWalletDebit > 0)) {
            if ($fWalletDebit > $CancelPrice) {
                $CancelPrice = $fWalletDebit + $fCancelPrice - $fWaitingFees - $fTripHoldPrice; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                $subtotal = formatNum($fCancelPrice);
            } else {
                $CancelPrice = $fCancelPrice - $fWalletDebit - $fTripHoldPrice; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                $subtotal = formatNum($fCancelPrice + $fWaitingFees + $fTripHoldPrice + $fWalletDebit);
            }
            $i = 0;
            $tripFareDetailsArr[][$languageLabelsArr['LBL_CANCELLATION_FEE']] = $currencySymbol . formatNum($CancelPrice);
            if ($fWaitingFees > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_WAITING_FEE_TXT'] . " (" . $waitingTime . ")"] = $currencySymbol . $returnArr['fWaitingFees'];
                $i++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice Start
            if ($fTripHoldPrice > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_INTRANSIT_TRIP_HOLD_FEE'] . " (" . $totalTime_hold . ")"] = $currencySymbol . $returnArr['fTripHoldPrice'];
                $i++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice End
            /* if($fWalletDebit > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "- " . $currencySymbol . $returnArr['fWalletDebit'];
              $i++;
              } */
            //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $currencySymbol.$subtotal;$i++;
        } else {
            $i = 0;
            $countUfx = 0;
            if ($tripData[0]['eType'] == "UberX" && $PAGE_MODE == "HISTORY") {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_VEHICLE_TYPE_SMALL_TXT']] = $returnArr['vVehicleCategory'] . "-" . $returnArr['vVehicleType'];
                $countUfx = 1;
            }
            if ($tripData[0]['eFareType'] == "Regular") {
                //$tripFareDetailsArr[][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vVehicleType . " " . $currencySymbol . $returnArr['iBaseFare'];
                /* Changes For Rental */
                if ($tripData[0]['iRentalPackageId'] > 0) {
                    $rentalData = getRentalData($tripData[0]['iRentalPackageId']);
                    $tripData[0]['vPackageName'] = $rentalData[0]['vPackageName_' . $userlangcode];
                    $tripFareDetailsArr[][$tripData[0]['vPackageName'] . " " . $languageLabelsArr['LBL_RENTAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iBaseFare'] : "--";
                    if ($countUfx == 1) {
                        $i++;
                    }
                    $TripKilometer = getVehicleCountryUnit($tripData[0]['iVehicleTypeId'], $rentalData[0]['fKiloMeter']);
                    if ($eUnit == "Miles") {
                        $TripKilometer = round($TripKilometer * 0.621371, 2);
                    }
                    if ($fDistance > $TripKilometer) {
                        $extradistance = $fDistance - $TripKilometer;
                    } else {
                        $extradistance = 0;
                    }
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_ADDITIONSL_DISTANCE_TXT'] . " (" . $extradistance . " " . $DisplayDistanceTxt . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                    $i++;
                    $Extra_Time = calculateAdditionalTime($tripData[0]['tStartDate'], $tripData[0]['tEndDate'], $rentalData[0]['fHour'], $userlangcode);
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_ADDITIONAL_TIME_TXT'] . " (" . $Extra_Time . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                    $i++;
                } else {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iBaseFare'] : "--";
                    if ($countUfx == 1) {
                        $i++;
                    }
                    //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $languageLabelsArr['LBL_KM_DISTANCE_TXT'] . ")"] = ($iActive != "Canceled")?$currencySymbol . $returnArr['TripFareOfDistance']:"--";
                    if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes") {
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_DISTANCE_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                        $i++;
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_TIME_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                        $i++;
                    } else {
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_DISTANCE_TXT'] . " (" . $returnArr['fDistance'] . " " . $DisplayDistanceTxt . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfDistance'] : "--";
                        $i++;
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                        $i++;
                    }
                }
                /* Changes For Rental */
            } else if ($tripData[0]['eFareType'] == "Fixed") {
                //$tripFareDetailsArr[$i + $countUfx][$languageLabelsArr['LBL_SERVICE_COST']] = $currencySymbol . ($fTripGenerateFare - $fSurgePriceDiff - $fMinFareDiff);
                //Added By HJ On 04-01-2019 For Set Vehicle Type Wise Fare Details Start
                if (isset($tripData[0]['tVehicleTypeFareData']) && $tripData[0]['tVehicleTypeFareData'] != "" && $SERVICE_PROVIDER_FLOW == "Provider") {

                    $tVehicleTypeFareData = (array) json_decode($tripData[0]['tVehicleTypeFareData']);
                    $tVehicleTypeFareData = (array) $tVehicleTypeFareData['FareData'];

                    $iVehicleTypeIds_str = "";

                    for ($fd = 0; $fd < count($tVehicleTypeFareData); $fd++) {
                        $iVehicleTypeIds_str = $iVehicleTypeIds_str == "" ? $tVehicleTypeFareData[$fd]->id : $iVehicleTypeIds_str . "," . $tVehicleTypeFareData[$fd]->id;
                    }

                    $sql_vehicleTypeNames = "SELECT vt.vVehicleType_" . $userlangcode . " as vVehicleType, (SELECT vcs.vCategory_" . $userlangcode . " FROM " . $sql_vehicle_category_table_name . " as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentCategoryName FROM vehicle_type as vt, " . $sql_vehicle_category_table_name . " as vc WHERE vt.iVehicleTypeId IN ($iVehicleTypeIds_str) AND vc.iVehicleCategoryId = vt.iVehicleCategoryId";
                    $data_vehicleTypeNames = $obj->MySQLSelect($sql_vehicleTypeNames);
                    // print_r($data_vehicleTypeNames);exit;
                    $getCategoryName = "";

                    for ($fd = 0; $fd < count($tVehicleTypeFareData); $fd++) {
                        $eAllowQty = $tVehicleTypeFareData[$fd]->eAllowQty;
                        $typeQty = $tVehicleTypeFareData[$fd]->qty;
                        if ($typeQty < 1) {
                            $typeQty = 1;
                        }
                        $tVehicleTypeFareData[$fd]->amount = $tVehicleTypeFareData[$fd]->amount * $typeQty;
                        $typeAmount = $currencySymbol . formatNum($tVehicleTypeFareData[$fd]->amount * $priceRatio);

                        $iVehicleTypeIds_str = $iVehicleTypeIds_str == "" ? $tVehicleTypeFareData[$fd]->id : $iVehicleTypeIds_str . "," . $tVehicleTypeFareData[$fd]->id;

                        // $typeTitle = $tVehicleTypeFareData[$fd]->title;
                        $typeTitle = $data_vehicleTypeNames[$fd]['vVehicleType'];

                        $getCategoryName = $data_vehicleTypeNames[$fd]['ParentCategoryName'];

                        $qtyDisplay = "";
                        if ($eAllowQty == "Yes") {
                            $qtyDisplay = " (x" . $typeQty . ")";
                        }
                        if ($typeTitle != $languageLabelsArr['LBL_SUBTOTAL_TXT']) {
                            $tripFareDetailsArr[][$typeTitle . $qtyDisplay] = $typeAmount;
                            $i++;
                        } else if ($PAGE_MODE != 'HISTORY') {
                            $i--;
                        }
                    }

                    //print_r($getCategoryName);exit;
                    $returnArr['vVehicleCategory'] = $getCategoryName;
                } else {
                    $vVehicleFare = ($tripData[0]['iFare'] * $priceRatio) + $fDiscount + $fWalletDebit + $fDriverDiscount - $fVisitFee - $fMaterialFee - $fMiscFee - $fOutStandingAmount - $fAddedOutstandingamt - $fWaitingFees - $fTax1 - $fTax2 - $fTripHoldPrice; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                    //added by SP for fly stations on 20-08-2019 start
                    if (!empty($tripData[0]['iFromStationId']) && !empty($tripData[0]['iToStationId'])) {
                        if ($fSurgePriceDiff == 0) {
                            $SERVICE_COST = ($tripData[0]['iQty'] > 1) ? $tripData[0]['iQty'] . ' X ' . $currencySymbol . formatNum($vVehicleFare) : $currencySymbol . formatNum($vVehicleFare);
                            $tripFareDetailsArr[][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $SERVICE_COST : "--";
                            if ($countUfx == 1) {
                                $i++;
                            }
                        }
                    } else {
                        //added by SP for fly stations on 20-08-2019 end
                        $SERVICE_COST = ($tripData[0]['iQty'] > 1) ? $tripData[0]['iQty'] . ' X ' . $currencySymbol . formatNum($vVehicleFare) : $currencySymbol . formatNum($vVehicleFare);
                        $tripFareDetailsArr[][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $SERVICE_COST : "--";
                        if ($countUfx == 1) {
                            $i++;
                        }
                    }
                }
                //Added By HJ On 04-01-2019 For Set Vehicle Type Wise Fare Details End
            } else if ($tripData[0]['eFareType'] == "Hourly") {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_TIME_TXT'] . " (" . $returnArr['TripTimeInMinutes'] . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['TripFareOfMinutes'] : "--";
                if ($countUfx == 1) {
                    $i++;
                }
            }

            if ($extraPersonCharge > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_EXTRA_PERSON_CHARGE']] = ($iActive != "Canceled") ? $currencySymbol . $generalobj->setTwoDecimalPoint($extraPersonCharge) : "--";
                $i++;
            }

            if ($fSurgePriceDiff > 0) {
                //added by SP for fly stations on 20-08-2019 start
                if (!empty($tripData[0]['iFromStationId']) && !empty($tripData[0]['iToStationId'])) {
                    
                } else {
                    $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                    $i++;
                }
                //added by SP for fly stations on 20-08-2019 end
                //$tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                //$i++;
                $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fWaitingFees - $fOutStandingAmount - $fAddedOutstandingamt - $fHotelCommision - $fTripHoldPrice - $fAirportPickupSurgeAmount - $fAirportDropoffSurgeAmount - $fMaterialFee - $fMiscFee + $fDriverDiscount; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                if ($eTollSkipped == "No") {
                    $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fWaitingFees - $fOutStandingAmount - $fAddedOutstandingamt - $fTollPrice - $fHotelCommision - $fTripHoldPrice - $fAirportPickupSurgeAmount - $fAirportDropoffSurgeAmount; // $fTripHoldPrice Variable For In Transite Amount By HJ On 28-12-2018
                }
                $normalfare = formatNum($normalfare);
                //added by SP for fly stations on 20-08-2019 start
                if (!empty($tripData[0]['iFromStationId']) && !empty($tripData[0]['iToStationId'])) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_SERVICE_COST']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                } else {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                }
                //added by SP for fly stations on 20-08-2019 end
                //$tripFareDetailsArr[][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                $i++;
                $tripFareDetailsArr[][$languageLabelsArr['LBL_SURGE'] . " x" . $SurgePriceFactor] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fSurgePriceDiff'] : "--";
                $i++;
            }

            if ($fVisitFee > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_VISIT_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fVisitFee'] : "--";
                $i++;
            }

            // added for airport surge
            if ($fSurgePriceDiff == 0 && ($fAirportPickupSurgeAmount > 0 || $fAirportDropoffSurgeAmount > 0)) {
                $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                $i++;
                $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fWaitingFees - $fOutStandingAmount - $fAddedOutstandingamt - $fHotelCommision - $fTripHoldPrice - $fAirportPickupSurgeAmount - $fAirportDropoffSurgeAmount;
                if ($eTollSkipped == "No") {
                    $normalfare = $fTripGenerateFare - $fSurgePriceDiff - $fTax1 - $fTax2 - $fMinFareDiff - $fWaitingFees - $fOutStandingAmount - $fAddedOutstandingamt - $fTollPrice - $fHotelCommision - $fTripHoldPrice - $fAirportPickupSurgeAmount - $fAirportDropoffSurgeAmount;
                }
                $normalfare = formatNum($normalfare);
                $tripFareDetailsArr[][$languageLabelsArr['LBL_NORMAL_FARE']] = ($iActive != "Canceled") ? $currencySymbol . $normalfare : "--";
                $i++;
                if ($fAirportPickupSurgeAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $fAirportPickupSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportPickupSurgeAmount'] : "--";
                    $i++;
                }

                if ($fAirportDropoffSurgeAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $fAirportDropoffSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportDropoffSurgeAmount'] : "--";
                    $i++;
                }
            } else {
                if ($fAirportPickupSurgeAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_PICK_SURGE'] . " x" . $fAirportPickupSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportPickupSurgeAmount'] : "--";
                    $i++;
                }

                if ($fAirportDropoffSurgeAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_AIRPORT_DROP_SURGE'] . " x" . $fAirportDropoffSurge] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fAirportDropoffSurgeAmount'] : "--";
                    $i++;
                }
            }
            if ($fMaterialFee > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_MATERIAL_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMaterialFee'] : "--";
                $i++;
            }

            if ($fMiscFee > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_MISC_FEE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMiscFee'] : "--";
                $i++;
            }

            if ($fDriverDiscount > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_PROVIDER_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDriverDiscount'] : "--";
                $i++;
            }
            if ($fWaitingFees > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_WAITING_FEE_TXT'] . " (" . $waitingTime . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fWaitingFees'] : $currencySymbol . $returnArr['fWaitingFees'];
                $i++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice Start
            if ($fTripHoldPrice > 0) {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_INTRANSIT_TRIP_HOLD_FEE'] . " (" . $totalTime_hold . ")"] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTripHoldPrice'] : $currencySymbol . $returnArr['fTripHoldPrice'];
                $i++;
            }
            //Added By HJ On 28-12-2018 For Dispay In Transite Data In Invoice End
            if ($fMinFareDiff > 0) {
                //$minimamfare = $iBaseFare + $fPricePerKM + $fPricePerMin + $fMinFareDiff;
                $minimamfare = $fTripGenerateFare - $fOutStandingAmount - $fAddedOutstandingamt - $fTax1 - $fTax2 - $fHotelCommision;
                if ($eTollSkipped == "No") {
                    $minimamfare = $fTripGenerateFare - $fTollPrice - $fOutStandingAmount - $fAddedOutstandingamt - $fTax1 - $fTax2 - $fHotelCommision;
                }
                $minimamfare = formatNum($minimamfare);
                $tripFareDetailsArr[][$currencySymbol . $minimamfare . " " . $languageLabelsArr['LBL_MINIMUM']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fMinFareDiff'] : "--";
                $returnArr['TotalMinFare'] = $minimamfare;
                $i++;
            }
            if ($eTollSkipped == "No") {
                $tripFareDetailsArr[][$languageLabelsArr['LBL_TOLL_PRICE_TOTAL']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTollPrice'] : "--";
                $i++;
            }

            if ($PAGE_MODE == "DISPLAY") {

                if ($fDiscount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_DISCOUNT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fDiscount'] : "--";
                    $i++;
                }
                if ($fOutStandingAmount > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fOutStandingAmount'] : "--";
                    $i++;
                }

                if ($fOutStandingAmount == 0 && $fAddedOutstandingamt > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $generalobj->formateNumAsPerCurrency($returnArr['fAddedOutstandingamt'], $currencycode) : "--";
                    $i++;
                }


                if ($fTax1 > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fUserCountryTax1 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax1'] : "--";
                    $i++;
                }
                if ($fTax2 > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fUserCountryTax2 . " % "] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fTax2'] : "--";
                    $i++;
                }
                if ($fWalletDebit > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fWalletDebit'] : "--";
                    $i++;
                }
                if ($fHotelCommision > 0) {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_HOTEL_SERVICE_CHARGE']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fHotelCommision'] : "--";
                    $i++;
                }
            } else {

                if ($fOutStandingAmount > 0 && $tripData[0]['vTripPaymentMode'] == "Cash") {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['fOutStandingAmount'] : "--";
                    $i++;
                    $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                    $i++;
                    $totfare_for_earn = $fTripGenerateFare - $fTax1 - $fTax2 - $fHotelCommision;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $totfare_for_earn : "--";
                    $i++;
                    //$tripFareDetailsArr[$i + 1]['eDisplaySeperator'] = "Yes"; $i++;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? "- " . $currencySymbol . $returnArr['fOutStandingAmount'] : "--";
                    $i++;
                }

                //if ($fOutStandingAmount == 0 && $fAddedOutstandingamt > 0 && $tripData[0]['vTripPaymentMode'] == "Cash") {
                if ($fOutStandingAmount == 0 && $fAddedOutstandingamt > 0 && $tripData[0]['vTripPaymentMode'] == "Cash") {
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? $generalobj->formateNumAsPerCurrency($returnArr['fAddedOutstandingamt'], $currencycode) : "--";
                    $i++;
                    $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                    $i++;
                    $totfare_for_earn = $fTripGenerateFare - $fTax1 - $fTax2 - $fHotelCommision;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $generalobj->formateNumAsPerCurrency($totfare_for_earn, $currencycode) : "--";
                    $i++;
                    //$tripFareDetailsArr[$i + 1]['eDisplaySeperator'] = "Yes"; $i++;
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = ($iActive != "Canceled") ? "- " . $generalobj->formateNumAsPerCurrency($returnArr['fAddedOutstandingamt'], $currencycode) : "--";
                    $i++;
                }
            }
            /* if ($fDiscount > 0) {
              $tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_DISCOUNT']] = ($iActive != "Canceled")?"- " . $currencySymbol . $returnArr['fDiscount']:"--";
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
        if ($PAGE_MODE == "DISPLAY") {
            $returnArr['FareDetailsNewArr'][]['eDisplaySeperator'] = "Yes";
            $i++;
            if ($eFlatTrip == "Yes" && $iActive != "Canceled") {
                $returnArr['FareDetailsNewArr'][][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare_Subtotal'] : "--";
            } elseif ($fCancelPrice > 0 || ($iActive == "Canceled" && $fWalletDebit > 0)) {
                $returnArr['FareDetailsNewArr'][][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = $currencySymbol . $subtotal;
            } else {
                $returnArr['FareDetailsNewArr'][][$languageLabelsArr['LBL_SUBTOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare_Subtotal'] : $currencySymbol . $returnArr['fWaitingFees'];
            }
            $i--;
        }
        //added by SP for rounding off currency wise on 26-8-2019 start
        //if($userType == "Driver"){
        //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_driver as rd LEFT JOIN country as co ON rd.vCountry = co.vCountryCode  WHERE rd.iDriverId = '" . $iUserId . "'";
        //    $countryData = $obj->MySQLSelect($sqlp);
        //    $vCountry = $countryData[0]['vCountryCode'];
        //}else{
        //    $sqlp = "SELECT co.vCountry,co.vCountryCode,co.eRoundingOffEnable FROM register_user as ru LEFT JOIN country as co ON ru.vCountry = co.vCountryCode WHERE ru.iUserId = '" . $iUserId . "'";
        //    $countryData = $obj->MySQLSelect($sqlp);
        //    $vCountry = $countryData[0]['vCountryCode'];
        //}

        /* if($eUserType == "Driver"){
          $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, rd.vCurrencyDriver FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iMemberId . "'";
          $currData = $obj->MySQLSelect($sqlp);
          $vCurrency = $currData[0]['vName'];
          $samecur = ($tripData[0]['vCurrencyDriver']==$passengerData[0]['vCurrencyDriver']) ? 1 : 0;
          } else {
          $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
          $currData = $obj->MySQLSelect($sqlp);
          $vCurrency = $currData[0]['vName'];
          $samecur = ($tripData[0]['vCurrencyPassenger']==$passengerData[0]['vCurrencyPassenger']) ? 1 : 0;
          }

          if($samecur==1 && $currData[0]['eRoundingOffEnable'] == "Yes" && isset($tripData[0]['fRoundingAmount']) && !empty($tripData[0]['fRoundingAmount']) && $tripData[0]['fRoundingAmount']!=0) {

          $roundingOffTotal_fare_amountArr['finalFareValue'] = formatNum(($returnArr['iFare_Subtotal']) - $tripData[0]['fRoundingAmount']);
          $roundingOffTotal_fare_amountArr['method'] = $tripData[0]['eRoundingType'];
          $roundingOffTotal_fare_amountArr['differenceValue'] = $tripData[0]['fRoundingAmount'];

          if($roundingOffTotal_fare_amountArr['method'] == "Addition"){
          $roundingMethod = "";
          }else{
          $roundingMethod = "-";
          }
          $roundingOffTotal_fare_amount = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
          $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";
          //$Fare_data[0]['total_fare'] = $roundingOffTotal_fare_amount;

          $i++;
          $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = ($iActive != "Canceled") ? $roundingMethod." ". $currencySymbol . "". $rounding_diff : $currencySymbol . $returnArr['fWaitingFees'];
          $i++;
          $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
          $i++;
          $tripFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol."". $roundingOffTotal_fare_amount : $currencySymbol . $returnArr['fWaitingFees'];

          } */

        if ($eUserType == "Driver") {
            if(count($currData) > 0){
                //Data Found From Global Array
            }else{
                $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, cu.ratio, rd.vCurrencyDriver FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iMemberId . "'";
                $currData = $obj->MySQLSelect($sqlp);
            }
            $vCurrency = $currData[0]['vName'];
            $samecur = ($tripData[0]['vCurrencyDriver'] == $driverData[0]['vCurrencyDriver'] && $tripData[0]['vCurrencyDriver'] == $tripData[0]['vCurrencyPassenger']) ? 1 : 0;
            if ($tripData[0]['eHailTrip'] == 'Yes') {
                $samecur = 1;
            }
        } else {
            if(count($currData) > 0){
                //Data Found From Global Array
            }else{
                $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, cu.ratio, ru.vCurrencyPassenger FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
                $currData = $obj->MySQLSelect($sqlp);
            }
            $vCurrency = $currData[0]['vName'];
            $samecur = ($tripData[0]['vCurrencyPassenger'] == $passengerData[0]['vCurrencyPassenger']) ? 1 : 0;
        }

        //if($currData[0]['eRoundingOffEnable'] == "Yes"){
        //print_R($returnArr); exit;
        //echo $returnArr['iFare_Subtotal']."aaa".$iFare_Subtotal; exit;
        if (isset($tripData[0]['fRoundingAmount']) && !empty($tripData[0]['fRoundingAmount']) && $tripData[0]['fRoundingAmount'] != 0 && $samecur == 1 && $currData[0]['eRoundingOffEnable'] == "Yes") {
            //remain when driver have inr currency with 4 digit like, 3000......
            $iFare_Subtotal_R = $iFare_Subtotal * $priceRatio;
            $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($iFare_Subtotal_R, $tripData[0]['fRoundingAmount'], $tripData[0]['eRoundingType']); ////start
            //print_R($roundingOffTotal_fare_amountArr); exit;
            //$roundingOffTotal_fare_amountArr = getRoundingOffAmount($returnArr['iFare_Subtotal'],$vCurrency);
            //$returnArr['roundingOffAmountArr'] = $roundingOffTotal_fare_amount;
            if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                $roundingMethod = "";
            } else {
                $roundingMethod = "-";
            }

            $roundingOffTotal_fare_amount = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? formatNum($roundingOffTotal_fare_amountArr['finalFareValue']) : "0.00";
            $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";
            //$Fare_data[0]['total_fare'] = $roundingOffTotal_fare_amount;

            $i++;
            $returnArr['FareDetailsNewArr'][][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = ($iActive != "Canceled") ? $roundingMethod . " " . $currencySymbol . $rounding_diff : $currencySymbol . $returnArr['fWaitingFees'];
            $i++;
            $returnArr['FareDetailsNewArr'][]['eDisplaySeperator'] = "Yes";
            $i++;
            $returnArr['FareDetailsNewArr'][][$languageLabelsArr['LBL_ROUNDING_NET_TOTAL_TXT']] = ($iActive != "Canceled") ? $currencySymbol . "" . $roundingOffTotal_fare_amount : $currencySymbol . $returnArr['fWaitingFees'];
        }
        //added by SP for rounding off currency wise on 26-8-2019 end



        $FareDetailsArr = array();
        foreach ($tripFareDetailsArr as $data) {
            $FareDetailsArr = array_merge($FareDetailsArr, $data);
        }
        $returnArr['FareDetailsArr'] = $FareDetailsArr;
        //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_Commision']] = ($iActive != "Canceled")?"-" . $currencySymbol . $returnArr['fCommision']:"--";
        if ($returnArr['fCommision'] > 0) {
            $tripFareDetailsArr[][$languageLabelsArr['LBL_Commision']] = "-" . $currencySymbol . $returnArr['fCommision'];
            $i++;
        }
        //$tripFareDetailsArr[$i + 1][$languageLabelsArr['LBL_EARNED_AMOUNT']] = $currencySymbol . $returnArr['iFare'];
        $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
        $i++;
        //$tripFareDetailsArr[][$languageLabelsArr['LBL_EARNED_AMOUNT']] = $currencySymbol . $returnArr['iFare_Detail_Earning'];
        $tripFareDetailsArr[][$languageLabelsArr['LBL_EARNED_AMOUNT']] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iFare_Detail_Earning'] : "--";
        $returnArr['HistoryFareDetailsNewArr'] = $tripFareDetailsArr;
        if ($tripData[0]['eType'] == "UberX" && $iActive != "Canceled") {
            array_splice($returnArr['HistoryFareDetailsNewArr'], 0, 1);
        }
    }
    //added by SP for rounding off currency wise on 26-8-2019 start
    //echo $roundingOffTotal_fare_amount;exit;
    if (isset($roundingOffTotal_fare_amount)) {
        $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $roundingOffTotal_fare_amount : "--";
    } else {
        $returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";
    }
    //$returnArr['FareSubTotal'] = ($iActive != "Canceled") ? $currencySymbol . $returnArr['iOriginalFare'] : "--";
    //added by SP for rounding off currency wise on 26-8-2019 end
    //passengertripfaredetails
    $HistoryFareDetailsArr = array();
    foreach ($tripFareDetailsArr as $inner) {
        $HistoryFareDetailsArr = array_merge($HistoryFareDetailsArr, $inner);
    }
    $returnArr['HistoryFareDetailsArr'] = $HistoryFareDetailsArr;
    //echo "<pre>";print_r($returnArr);die;
    //echo "<pre>";print_r(vVehicleCategory);die;
    $tVehicleTypeData = (array) json_decode($tripData[0]['tVehicleTypeData']);
    $returnArr['moreServices'] = "No";
    if (count($tVehicleTypeData) > 1) {
        $returnArr['moreServices'] = "Yes";
    } else if (!empty($tVehicleTypeData)) {
        $returnArr['moreServices'] = "Yes";
    }
    //added by SP for fly stations on 30-08-2019 start
    if (!empty($tripData[0]['iFromStationId']) && !empty($tripData[0]['iToStationId'])) {
        $returnArr['eFly'] = "Yes";
    } else {
        $returnArr['eFly'] = "No";
    }
    //added by SP for fly stations on 30-08-2019 end
    //drivertripfarehistorydetails
    //echo "<pre>";print_r($returnArr);echo "<pre>";exit;
    return $returnArr;
}

function getUserRatingAverage($iMemberId, $eUserType = "Passenger") {
    global $obj, $generalobj;
    if ($eUserType == "Passenger") {
        $iUserId = "iDriverId";
        $checkusertype = "Passenger";
    } else {
        $iUserId = "iUserId";
        $checkusertype = "Driver";
    }
    //$usertotaltrips = get_value("trips", "iTripId", $iUserId, $iMemberId);
    $sql = "SELECT iTripId from trips WHERE $iUserId = '" . $iMemberId . "' AND eHailTrip = 'No' AND (eBookingFrom != 'Hotel' OR eBookingFrom != 'Kiosk')";
    $usertotaltrips = $obj->MySQLSelect($sql);

    if (count($usertotaltrips) > 0) {
        for ($i = 0; $i < count($usertotaltrips); $i++) {
            $iTripId .= $usertotaltrips[$i]['iTripId'] . ",";
        }
        $iTripId_str = substr($iTripId, 0, -1);
        //echo  $iTripId_str;exit;
        $sql = "SELECT count(iRatingId) as ToTalTrips, SUM(vRating1) as ToTalRatings from ratings_user_driver WHERE iTripId IN (" . $iTripId_str . ") AND eUserType = '" . $checkusertype . "'";
        $result_ratings = $obj->MySQLSelect($sql);
        $ToTalTrips = $result_ratings[0]['ToTalTrips'];
        $ToTalRatings = $result_ratings[0]['ToTalRatings'];
        //$average_rating = round($ToTalRatings / $ToTalTrips, 2);
        $average_rating = round($ToTalRatings / $ToTalTrips, 1);
    } else {
        $average_rating = 0;
    }
    return $average_rating;
}

function UpdateDriverRequest2($iDriverId, $iUserId, $iTripId, $eStatus = "", $vMsgCode, $eAcceptAttempted = "No") {
    global $obj;
    $sql = "SELECT * FROM `driver_request` WHERE iDriverId = '" . $iDriverId . "' AND iUserId = '" . $iUserId . "' AND iTripId = '0' AND vMsgCode='" . $vMsgCode . "'";
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
    global $generalobj, $obj;
    $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $driverId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    //$userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
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
        $returnArr['Action'] = "0"; # Check For Driver's vehicle added or not #
        $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
        setDataResponse($returnArr);
    } else {
        $DriverSelectedVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $driverId, '', 'true');
        if ($DriverSelectedVehicleId == 0) {
            $returnArr['Action'] = "0"; # Check Driver has selected  vehicle or not if #
            $returnArr['message'] = "LBL_SELECT_CAR_MESSAGE_TXT";
            setDataResponse($returnArr);
        } else {
            # Check For Driver's selected vehicle's document are upload or not #
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
            # Check For Driver's selected vehicle's document are upload or not #
            # Check For Driver's selected vehicle status #
            $DriverSelectedVehicleStatus = get_value('driver_vehicle', 'eStatus', 'iDriverVehicleId', $DriverSelectedVehicleId, '', 'true');
            if ($DriverSelectedVehicleStatus == "Inactive" || $DriverSelectedVehicleStatus == "Deleted") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_SELECTED_VEHICLE_NOT_ACTIVE";
                setDataResponse($returnArr);
            }
            # Check For Driver's selected vehicle status #
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

function getVehicleCountryUnit($vehicleTypeID, $fPerKM) {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT,$vehicleTypeDataArr,$countryAssociateArr;
    
    //Added By HJ On 20-06-2020 For Optimized vehicle_type Table Query Start
    $tabelName = "vehicle_type";
    if(isset($vehicleTypeDataArr['vehicle_type'])){
        $vehicleTypeData = $vehicleTypeDataArr['vehicle_type'];
        $typeDataArr = array();
        for($h=0;$h<count($vehicleTypeData);$h++){
            $typeDataArr[$vehicleTypeData[$h]['iVehicleTypeId']] = $vehicleTypeData[$h]['iLocationid'];
        }
        if(isset($typeDataArr[$vehicleTypeID])){
            $iLocationid =$typeDataArr[$vehicleTypeID];
        }else{
            $iLocationid = get_value("vehicle_type", "iLocationid", "iVehicleTypeId", $vehicleTypeID, '', 'true');
        }
    }else{
        $iLocationid = get_value("vehicle_type", "iLocationid", "iVehicleTypeId", $vehicleTypeID, '', 'true');
    }
    //Added By HJ On 22-06-2020 For Optimize vehicle_type Table Query End
    $iCountryId = get_value("location_master", "iCountryId", "iLocationId", $iLocationid, '', 'true');
    if ($iLocationid == "-1") {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    } else {
        if(isset($countryAssociateArr[$iCountryId])){
            $eUnit = $countryAssociateArr[$iCountryId]['eUnit'];
        }else{
            $eUnit = get_value("country", "eUnit", "iCountryId", $iCountryId, '', 'true');
        }
    }
    if ($eUnit == "" || $eUnit == NULL) {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    }
    if ($eUnit == "Miles") {
        $KMvalue = $fPerKM * 1.60934;
    } else {
        $KMvalue = $fPerKM;
    }
    return round($KMvalue, 2);
}

/* changes for rental */

############### Get User's  Country Details From TimeZone ##################################################################

function GetUserCounryDetail($iMemberId, $UserType = "Passenger", $vTimeZone, $vUserDeviceCountry = "") {
    global $generalobj, $obj, $DEFAULT_COUNTRY_CODE_WEB, $tconfig,$country_data_retrieve,$userDetailsArr;
    $returnArr = array();
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vCountryfield = "vCountry";
        $iUserId = "iUserId";
    } else {
        $tblname = "register_driver";
        $vCountryfield = "vCountry";
        $iUserId = "iDriverId";
    }
    $returnArr['vDefaultCountry'] = $returnArr['vDefaultCountryCode'] = $returnArr['vDefaultPhoneCode'] = $returnArr['vDefaultCountryImage'] = '';
    //$vTimeZone = "Europe/Andorra";
    $countryCodeArr = $sqlcountryCode = $countryDataArr = array();
    for ($d = 0; $d < count($country_data_retrieve); $d++) {
        if (strtoupper($country_data_retrieve[$d]['eStatus']) == "ACTIVE") {
            $dataArr = array();
            $dataArr['vDefaultCountry'] = $dataArr['vCountry'] = $country_data_retrieve[$d]['vCountry'];
            $dataArr['vDefaultCountryCode'] = $dataArr['vCountryCode'] = $country_data_retrieve[$d]['vCountryCode'];
            $dataArr['vDefaultPhoneCode'] = $dataArr['vPhoneCode'] = $country_data_retrieve[$d]['vPhoneCode'];
            $dataArr['vRImage'] = $country_data_retrieve[$d]['vRImage'];
            $dataArr['vSImage'] = $country_data_retrieve[$d]['vSImage'];
            $dataArr['vTimeZone'] = $country_data_retrieve[$d]['vTimeZone'];
            $countryDataArr[$country_data_retrieve[$d]['vCountryCode']] = $dataArr;
        }
    }
    if ($vTimeZone != "") {
        foreach ($countryDataArr as $key => $val) {
            if (strtoupper($val['vTimeZone']) == strtoupper($vTimeZone)) {
                $sqlcountryCode[] = $val;
            }
        }
        //$sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode,vCountry as vCountry, vCountryCode as vCountryCode, vPhoneCode as vPhoneCode,vRImage,vSImage FROM country WHERE vTimeZone = '" . $vTimeZone . "' AND eStatus = 'Active'"; //here
        //$sqlcountryCode = $obj->MySQLSelect($sql);
    }
    //echo "<pre>";print_r($sqlcountryCode);die;
    if (!empty($sqlcountryCode) && count($sqlcountryCode) > 0) {
        $returnArr = $sqlcountryCode[0];
    } else {
        if ($vUserDeviceCountry != "") {
            $vUserDeviceCountry = strtoupper($vUserDeviceCountry);
            if (isset($countryDataArr[$vUserDeviceCountry])) {
                $sqlusercountryCode = array();
                $sqlusercountryCode[] = $countryDataArr[$vUserDeviceCountry];
            } else {
                $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode,vCountry as vCountry, vCountryCode as vCountryCode, vPhoneCode as vPhoneCode,vRImage,vSImage FROM country WHERE vCountryCode = '" . $vUserDeviceCountry . "' AND eStatus = 'Active'";
                $sqlusercountryCode = $obj->MySQLSelect($sql);
            }
            if (!empty($sqlusercountryCode) && count($sqlusercountryCode) > 0) {
                $returnArr = $sqlusercountryCode[0];
            } else {
                if (isset($countryDataArr[$DEFAULT_COUNTRY_CODE_WEB])) {
                    $sqlcountryCode = array();
                    $sqlcountryCode[] = $countryDataArr[$DEFAULT_COUNTRY_CODE_WEB];
                } else {
                    $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode,vCountry as vCountry, vCountryCode as vCountryCode, vPhoneCode as vPhoneCode,vRImage,vSImage FROM country WHERE  vCountryCode = '" . $DEFAULT_COUNTRY_CODE_WEB . "' AND eStatus = 'Active'";
                    $sqlcountryCode = $obj->MySQLSelect($sql);
                }
                if (count($sqlcountryCode) > 0) {
                    $returnArr = $sqlcountryCode[0];
                }
            }
        } else {
            if (isset($countryDataArr[$DEFAULT_COUNTRY_CODE_WEB])) {
                $sqlcountryCode = array();
                $sqlcountryCode[] = $countryDataArr[$DEFAULT_COUNTRY_CODE_WEB];
            } else {
                $sql = "SELECT vCountry as vDefaultCountry, vCountryCode as vDefaultCountryCode, vPhoneCode as vDefaultPhoneCode,vCountry as vCountry, vCountryCode as vCountryCode, vPhoneCode as vPhoneCode,vRImage,vSImage FROM country WHERE  vCountryCode = '" . $DEFAULT_COUNTRY_CODE_WEB . "' AND eStatus = 'Active'"; // here
                $sqlcountryCode = $obj->MySQLSelect($sql);
            }
            //echo "<pre>";print_r($sqlcountryCode);die;
            if (!empty($sqlcountryCode) && count($sqlcountryCode) > 0) {
                $returnArr = $sqlcountryCode[0];
            }
        }
    }

    //echo "<pre>";print_r($countryDataArr);die;
    //added by SP for getting user wise image on 6-9-2019
    $vCountry = $returnArr['vDefaultCountryCode'];
    //echo "<pre>";print_r($returnArr);die;
    //Added Bu HJ On 09-06-2020 For Optimization Start
    if ($iMemberId > 0) {
        if(isset($userDetailsArr[$tblname."_".$iMemberId]) && count($userDetailsArr[$tblname."_".$iMemberId]) > 0){
            $datac = $userDetailsArr[$tblname."_".$iMemberId];
            $vCountry = $datac[0][$vCountryfield];
        }else{
            $sqlc = "SELECT $vCountryfield as vCountry FROM $tblname WHERE $iUserId = '" . $iMemberId . "'";
            $datac = $obj->MySQLSelect($sqlc);
            $vCountry = $datac[0]['vCountry'];
        }
    }
    //Added Bu HJ On 09-06-2020 For Optimization End
    if (isset($countryDataArr[$vCountry])) {
        $datacode = array();
        $datacode[] = $countryDataArr[$vCountry];
    } else {
        $sqlcd = "SELECT vCountry as vCountry, vCountryCode as vCountryCode, vPhoneCode as vPhoneCode,vRImage,vSImage FROM country WHERE vCountryCode = '" . $vCountry . "' AND eStatus = 'Active'"; // here
        $datacode = $obj->MySQLSelect($sqlcd);
    }
    //$temp_image = checkimgexist("webimages/icons/country_flags/" . strtolower($datacode[0]['vCountryCode']) . "_r.png", '1');
    $temp_image = checkimgexist("webimages/icons/country_flags/" . $datacode[0]['vRImage'], '1');
    $returnArr['vRImageMember'] = $temp_image; //added by SP for country image related changes on 05-08-2019

    $temp_image = checkimgexist("webimages/icons/country_flags/" . $datacode[0]['vSImage'], '2');
    $returnArr['vSImageMember'] = $temp_image; //added by SP for country image related changes on 05-08-2019     

    $temp_image = checkimgexist("webimages/icons/country_flags/" . strtolower($returnArr['vDefaultCountryCode']) . "_r.png", '1');
    $returnArr['vRImage'] = $temp_image; //added by SP for country image related changes on 05-08-2019
    $temp_image = checkimgexist("webimages/icons/country_flags/" . strtolower($returnArr['vDefaultCountryCode']) . "_s.png", '2');
    $returnArr['vSImage'] = $temp_image; //added by SP for country image related changes on 05-08-2019
    if (empty($vCountry)) { //added by SP for when country is not inserted for the particular user as like FB user then we will overwrite it as default country image on 04-10-2019 
        $returnArr['vRImageMember'] = $returnArr['vRImage'];
        $returnArr['vSImageMember'] = $returnArr['vSImage'];
    }

    $temp_image = checkimgexist($tconfig["tsite_url"] . "webimages/icons/country_flags/" . strtolower($returnArr['vDefaultCountryCode']) . "_s.png", "2");
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

############### Get User's  Country Details From TimeZone  #################################################################
############### Get User  Country's Police Number   ###################################################################

function getMemberCountryPoliceNumber($iMemberId, $UserType = "Passenger", $vCountry) {
    global $generalobj, $obj, $SITE_POLICE_CONTROL_NUMBER,$userDetailsArr,$country_data_arr;
    if ($vCountry != "") {
        if ($UserType == "Passenger") {
            $tblname = "register_user";
            $vCountryfield = "vCountry";
            $iUserId = "iUserId";
        } else {
            $tblname = "register_driver";
            $vCountryfield = "vCountry";
            $iUserId = "iDriverId";
        }
        //Added By HJ On 09-06-2020 For Optimization Start
        if(isset($userDetailsArr[$tblname."_".$iMemberId]) && count($userDetailsArr[$tblname."_".$iMemberId]) > 0){
            $db_sql = array();
            if(isset($userDetailsArr[$tblname."_".$iMemberId][0][$vCountryfield]) && trim($userDetailsArr[$tblname."_".$iMemberId][0][$vCountryfield]) != ""){
                $memberCountryCode = $userDetailsArr[$tblname."_".$iMemberId][0][$vCountryfield];
                if(isset($country_data_arr[$memberCountryCode])){
                    $db_sql[] = $country_data_arr[$memberCountryCode];
                }
            }
        }
        //Added By HJ On 09-06-2020 For Optimization End
        if(count($db_sql) > 0){
            // Data Found From Global Array
        }else{
            $sql = "SELECT co.vEmergencycode FROM country as co LEFT JOIN $tblname as rd ON co.vCountryCode = rd.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'";
            $db_sql = $obj->MySQLSelect($sql);
        }
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
########################### Get Trip Waiting Fee    ###################################################################

function getTripWaitingFee($iTripId) {
    global $generalobj, $obj, $ENABLE_WAITING_CHARGE_RENTAL, $ENABLE_WAITING_CHARGE_FLAT_TRIP, $SERVICE_PROVIDER_FLOW,$tripDetailsArr,$vehicleTypeDataArr;
    if(isset($tripDetailsArr['trips_'.$iTripId])){
        $tripdata= $tripDetailsArr['trips_'.$iTripId];
    }else{
        $tripdata = $obj->MySQLSelect("SELECT * FROM trips WHERE iTripId='" . $iTripId . "'");
        $tripDetailsArr['trips_'.$iTripId] = $tripdata;
    }
    $startDate = $tripdata[0]['tStartDate'];
    if ($startDate == "0000-00-00 00:00:00") {
        $startDate = @date("Y-m-d H:i:s");
    }
    $eFlatTrip = $tripdata[0]['eFlatTrip'];
    $tDriverArrivedDate = $tripdata[0]['tDriverArrivedDate'];
    $waiting_time_diff = strtotime($startDate) - strtotime($tDriverArrivedDate);
    $waitingTime = ceil($waiting_time_diff / 60);
    $vehicleTypeID = $tripdata[0]['iVehicleTypeId'];
    $eHailTrip = $tripdata[0]['eHailTrip'];
    $eType = $tripdata[0]['eType'];
    $iRentalPackageId = $tripdata[0]['iRentalPackageId'];
    $fWaitingFees = 0;
    if ($eHailTrip == "Yes" || ($iRentalPackageId > 0 && $ENABLE_WAITING_CHARGE_RENTAL != "Yes") || ($eFlatTrip == "Yes" && $ENABLE_WAITING_CHARGE_FLAT_TRIP != "Yes")) {
        return 0;
    } else {
        if ($SERVICE_PROVIDER_FLOW == "Provider" && $tripdata[0]['tVehicleTypeData'] != "") {
            // $tripdata[0]['eFareType'] == "Fixed"
            $tVehicleTypeFareData = (array) json_decode($tripdata[0]['tVehicleTypeFareData']);
            $fWaitingFeesCommission = 0;

            $fWaitingFees = $tVehicleTypeFareData['ParentWaitingFees'];
            $iWaitingFeeTimeLimit = $tVehicleTypeFareData['ParentWaitingTimeLimit'];
            $parentCommision = $tVehicleTypeFareData['ParentCommision'];
            if ($waitingTime > $iWaitingFeeTimeLimit) {
                $waitingTime = $waitingTime - $iWaitingFeeTimeLimit;
                $fWaitingFees = $fWaitingFees * $waitingTime;
                $fWaitingFees = round($fWaitingFees, 2);
            } else {
                $fWaitingFees = 0;
            }
            $fWaitingFeesCommission = round((($fWaitingFees * $parentCommision) / 100), 2);

            $returnWaitingFeeArr['WaitingFee'] = $fWaitingFees;
            $returnWaitingFeeArr['WaitingFeeCommission'] = $fWaitingFeesCommission;

            return $returnWaitingFeeArr;
        } else {
            //Added By HJ On 22-06-2020 For Optimize vehicle_type Table Query Start
            if(isset($vehicleTypeDataArr['vehicle_type'])){
                $vehicleTypeData = $vehicleTypeDataArr['vehicle_type'];
                $typeDataArr = array();
                for($h=0;$h<count($vehicleTypeData);$h++){
                    $typeDataArr[$vehicleTypeData[$h]['iVehicleTypeId']] = $vehicleTypeData[$h]['fWaitingFees'];
                }
                if(isset($typeDataArr[$vehicleTypeID])){
                    $fWaitingFees =$typeDataArr[$vehicleTypeID];
                }else{
                    $tripvehicledata = $obj->MySQLSelect("SELECT fWaitingFees FROM vehicle_type WHERE iVehicleTypeId='" . $vehicleTypeID . "'");
                    $fWaitingFees = $tripvehicledata[0]['fWaitingFees'];
                }
            }else{
                $tripvehicledata = $obj->MySQLSelect("SELECT fWaitingFees FROM vehicle_type WHERE iVehicleTypeId='" . $vehicleTypeID . "'");
                $fWaitingFees = $tripvehicledata[0]['fWaitingFees'];
            }
            //Added By HJ On 22-06-2020 For Optimize vehicle_type Table Query End 
            $iWaitingFeeTimeLimit = $tripdata[0]['iWaitingFeeTimeLimit'];
            if ($waitingTime > $iWaitingFeeTimeLimit) {
                $waitingTime = $waitingTime - $iWaitingFeeTimeLimit;
                $fWaitingFees = $fWaitingFees * $waitingTime;
                $fWaitingFees = round($fWaitingFees, 2);
            } else {
                $fWaitingFees = 0;
            }
        }
    }
    return $fWaitingFees;
}

########################### Get Trip Waiting Fee    ###################################################################
########################### Update Trip Outstanding Amount Of Passenger################################################

function UpdateTripOutstandingAmount($iTripId, $ePaidByPassenger = "No", $ePaidToDriver = "No") {
    global $generalobj, $obj, $CREDIT_TO_WALLET_ENABLE;

    $sql = "SELECT iUserId,iDriverId,fCancellationFare,fWalletDebit,vTripPaymentMode,vRideNo,iVehicleTypeId, tVehicleTypeFareData,eCardFailed,iFare FROM trips WHERE iTripId='" . $iTripId . "'"; //added by SP Outstanding calculate if payment failed end on 27-7-2019 add eCardFailed
    $tripdata = $obj->MySQLSelect($sql);
    $iUserId = $tripdata[0]['iUserId'];
    $iDriverId = $tripdata[0]['iDriverId'];
    $fCancellationFare = $tripdata[0]['fCancellationFare'];
    $fWalletDebit = $tripdata[0]['fWalletDebit'];
    $vTripPaymentMode = $tripdata[0]['vTripPaymentMode'];
    $iVehicleTypeId = $tripdata[0]['iVehicleTypeId'];
    $eCardFailed = $tripdata[0]['eCardFailed']; //added by SP Outstanding calculate if payment failed end on 27-7-2019
    $iFare = $tripdata[0]['iFare'];
    $fCommision = $fPendingAmount = 0;
    if ($iVehicleTypeId > 0) {
        $fCommision = get_value('vehicle_type', 'fCommision', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    }
    if (!empty($tripdata[0]['tVehicleTypeFareData'])) {
        $tVehicleTypeFareDataArr = (array) json_decode($tripdata[0]['tVehicleTypeFareData']);
        $fCommision = $tVehicleTypeFareDataArr['ParentCommision'];
    }
    /* added by SP Outstanding calculate if payment failed start on 27-7-2019 */
    if ($eCardFailed == 'Yes') {
        $fCancellationFare = $iFare;
    }
    /* added by SP Outstanding calculate if payment failed end on 27-7-2019 */
    if ($fCancellationFare > 0) {
        $fPendingAmount = $fCancellationFare;
    }
    if ($fPendingAmount < 0) {
        $fPendingAmount = 0;
    }
    ## Calculate Driver's Commission and PendingAmount ##
    $DriverTotalAmount = $fWalletDebit + $fCancellationFare;
    $Site_Commision = round((($DriverTotalAmount * $fCommision) / 100), 2);
    $fDriverPendingAmount = $DriverTotalAmount - $Site_Commision;
    $fTripGenerateFare = $fWalletDebit + $fCancellationFare;
    $iFare = $fCancellationFare;
    ## Calculate Driver's Commission and PendingAmount ##
    $Data_trip_OutstandingAmount["iTripId"] = $iTripId;
    $Data_trip_OutstandingAmount["iUserId"] = $iUserId;
    $Data_trip_OutstandingAmount["iDriverId"] = $iDriverId;
    $Data_trip_OutstandingAmount["fWalletDebit"] = $fWalletDebit;
    $Data_trip_OutstandingAmount["fCancellationFare"] = $fCancellationFare;
    $Data_trip_OutstandingAmount["vTripPaymentMode"] = $vTripPaymentMode;
    $Data_trip_OutstandingAmount["ePaidByPassenger"] = $ePaidByPassenger;
    $Data_trip_OutstandingAmount["ePaidToDriver"] = $ePaidToDriver;
    $Data_trip_OutstandingAmount["fPendingAmount"] = $fPendingAmount;
    $Data_trip_OutstandingAmount["fCommision"] = $Site_Commision;
    $Data_trip_OutstandingAmount["fDriverPendingAmount"] = $fDriverPendingAmount;
    if ($ePaidByPassenger == "Yes") {
        $Data_trip_OutstandingAmount["vTripAdjusmentId"] = $iTripId;
    }
    $currencyList = get_value('currency', '*', 'eStatus', 'Active');
    for ($i = 0; $i < count($currencyList); $i++) {
        $currencyCode = $currencyList[$i]['vName'];
        $Data_trip_OutstandingAmount['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
    }
    $iTripOutstandId = $obj->MySQLQueryPerform("trip_outstanding_amount", $Data_trip_OutstandingAmount, 'insert');
    if ($iTripOutstandId > 0 && $ePaidByPassenger == "No" && $fPendingAmount > 0) {
        $updateQuery = "UPDATE register_user set fTripsOutStandingAmount = fTripsOutStandingAmount+'" . $fPendingAmount . "' WHERE iUserId = " . $iUserId;
        $obj->sql_query($updateQuery);
    }

    ### Debit  User Wallet & Credit Driver Wallet ###
    if ($eCardFailed == 'No') { //added by SP because when card failed at that time, it is checked otherwise two times debited from the user account on 30-07-2019
        if ($fWalletDebit > 0) {
            ### Debit  User Wallet  ###
            $vRideNo = $tripdata[0]['vRideNo'];
            $data_user_wallet['iUserId'] = $iUserId;
            $data_user_wallet['eUserType'] = "Rider";
            $data_user_wallet['iBalance'] = $fWalletDebit;
            $data_user_wallet['eType'] = "Debit";
            $data_user_wallet['dDate'] = @date("Y-m-d H:i:s");
            $data_user_wallet['iTripId'] = $iTripId;
            $data_user_wallet['eFor'] = "Booking";
            $data_user_wallet['ePaymentStatus'] = "Unsettelled";
            $data_user_wallet['tDescription'] = "#LBL_DEBITED_CANCELLED_BOOKING# " . " " . $vRideNo;
            $generalobj->InsertIntoUserWallet($data_user_wallet['iUserId'], $data_user_wallet['eUserType'], $data_user_wallet['iBalance'], $data_user_wallet['eType'], $data_user_wallet['iTripId'], $data_user_wallet['eFor'], $data_user_wallet['tDescription'], $data_user_wallet['ePaymentStatus'], $data_user_wallet['dDate']);
            ### Debit  User Wallet  ###
            ### Credit Driver Wallet ###
            if ($fCancellationFare == 0) {
                $fWalletDebit = $fWalletDebit - $Site_Commision;
            }
            $data_driver_wallet['iUserId'] = $iDriverId;
            $data_driver_wallet['eUserType'] = "Driver";
            $data_driver_wallet['iBalance'] = $fWalletDebit;
            $data_driver_wallet['eType'] = "Credit";
            $data_driver_wallet['dDate'] = @date("Y-m-d H:i:s");
            $data_driver_wallet['iTripId'] = $iTripId;
            $data_driver_wallet['eFor'] = "Deposit";
            $data_driver_wallet['ePaymentStatus'] = "Unsettelled";
            $data_driver_wallet['tDescription'] = "#LBL_AMOUNT_CANCELTRIP_CREDIT#" . " " . $vRideNo;
            $generalobj->InsertIntoUserWallet($data_driver_wallet['iUserId'], $data_driver_wallet['eUserType'], $data_driver_wallet['iBalance'], $data_driver_wallet['eType'], $data_driver_wallet['iTripId'], $data_driver_wallet['eFor'], $data_driver_wallet['tDescription'], $data_driver_wallet['ePaymentStatus'], $data_driver_wallet['dDate']);
            ### Credit Driver Wallet ###
        }
    }
    ### Debit  User Wallet & Credit Driver Wallet ###
    if ($ePaidByPassenger == "Yes" && $ePaidToDriver == "Yes") {
        $updateQuery = "UPDATE trips set iFare = '" . $iFare . "',fTripGenerateFare = '" . $fTripGenerateFare . "',ePaymentCollect = 'Yes',eDriverPaymentStatus = 'Settelled',fCommision = '" . $Site_Commision . "' WHERE iTripId = " . $iTripId;
        $obj->sql_query($updateQuery);
    } else {
        $updateQuery = "UPDATE trips set iFare = '" . $iFare . "',fTripGenerateFare = '" . $fTripGenerateFare . "',fCommision = '" . $Site_Commision . "' WHERE iTripId = " . $iTripId;
        $obj->sql_query($updateQuery);
    }
    return $iTripOutstandId;
}

########################### Update Trip Outstanding Amount Of Passenger##################################################
########################### Charge Customer App Payment Method Wise ##############################################################

function ChargeCustomer($Data, $eChargeEvent = "CollectPayment") {

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    global $generalobj, $obj, $STRIPE_SECRET_KEY, $STRIPE_PUBLISH_KEY, $gateway, $BRAINTREE_TOKEN_KEY, $BRAINTREE_ENVIRONMENT, $BRAINTREE_MERCHANT_ID, $BRAINTREE_PUBLIC_KEY, $BRAINTREE_PRIVATE_KEY, $BRAINTREE_CHARGE_AMOUNT, $PAYMAYA_API_URL, $PAYMAYA_SECRET_KEY, $PAYMAYA_PUBLISH_KEY, $PAYMAYA_ENVIRONMENT_MODE, $OMISE_SECRET_KEY, $OMISE_PUBLIC_KEY, $ADYEN_MERCHANT_ACCOUNT, $ADYEN_USER_NAME, $ADYEN_PASSWORD, $ADYEN_API_URL, $XENDIT_PUBLIC_KEY, $XENDIT_SECRET_KEY, $APP_PAYMENT_METHOD, $SYSTEM_PAYMENT_ENVIRONMENT, $DEFAULT_CURRENCY_CONVERATION_CODE_RATIO, $DEFAULT_CURRENCY_CONVERATION_ENABLE, $DEFAULT_CURRENCY_CONVERATION_CODE, $FLUTTERWAVE_PUBLIC_KEY, $FLUTTERWAVE_SECRET_KEY; // Stripe,Braintree
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */

    $iFare = $Data['iFare'];
    $price_new = $Data['price_new'];
    $currency = $Data['currency'];
    if (strtoupper($DEFAULT_CURRENCY_CONVERATION_ENABLE) == 'YES' && !empty($DEFAULT_CURRENCY_CONVERATION_CODE_RATIO) && !empty($DEFAULT_CURRENCY_CONVERATION_CODE) && $DEFAULT_CURRENCY_CONVERATION_CODE_RATIO > 0) {
        $DefaultConverationRatio = $DEFAULT_CURRENCY_CONVERATION_CODE_RATIO;
        $price_new = $price_new / 100;
        $price_new = (round(($price_new * $DefaultConverationRatio), 2) * 100);
        $currency = $DEFAULT_CURRENCY_CONVERATION_CODE;
    }
    //echo "<pre>";print_r($Data);die;
    $vStripeCusId = $Data['vStripeCusId'];
    $description = $Data['description'];
    $iTripId = $Data['iTripId'];
    $eCancelChargeFailed = $Data['eCancelChargeFailed'];
    $vBrainTreeToken = $Data['vBrainTreeToken'];
    $vRideNo = $Data['vRideNo'];
    $iMemberId = $Data['iMemberId'];
    $UserType = $Data['UserType'];
    $vBrainTreeChargePrice = $vPaymayaChargePrice = $vXenditChargePrice = $price_new / 100;

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $vFlutterWaveChargePrice = $iFare;
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    $vAdyenChargePrice = $price_new;
    if ($UserType == "Passenger") {
        $tbl_name = "register_user";
        $iUserId = "iUserId";
        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        $UserDetailPaymaya = get_value($tbl_name, 'vPaymayaCustId,vPaymayaToken,vAdyenToken,vName,vLastName,vEmail,vXenditAuthId,vXenditToken,vFlutterWaveToken,vCurrencyPassenger as vCurrency', $iUserId, $iMemberId);
        /* Added By PM On 09-12-2019 For Flutterwave Code End */
    } else {
        $tbl_name = "register_driver";
        $iUserId = "iDriverId";
        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        $UserDetailPaymaya = get_value($tbl_name, 'vPaymayaCustId,vPaymayaToken,vAdyenToken,vName,vLastName,vEmail,vXenditAuthId,vXenditToken,vFlutterWaveToken,vCurrencyDriver as vCurrency', $iUserId, $iMemberId);
        /* Added By PM On 09-12-2019 For Flutterwave Code End */
    }
    $returnArr['message1'] = "LBL_SKIP_SMALL"; //added by SP to show label at app when card is selected at that time skip is displayd otherwise collect cash on 30-07-2019
    //echo $vStripeCusId;die;

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
                $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
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
                    $transMsg = "LBL_CHARGE_COLLECT_FAILED";
                    if ($eChargeEvent == "submitRating") {
                        $transMsg = 'LBL_RETRY_FOR_TIP';
                    }
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $transMsg;
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $returnArr['status'] = "fail";
            $error3 = $e->getMessage();
            if ($eChargeEvent == "cancelTrip") {
                $eCancelChargeFailed = 'Yes';
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = $error3;
                //$returnArr['message']="LBL_CHARGE_COLLECT_FAILED";
                setDataResponse($returnArr);
            }
        }
    } else if ($APP_PAYMENT_METHOD == "Braintree") {
        require_once ('assets/libraries/braintree/lib/Braintree.php');
        $status = "0";
        try {
            if ($iFare > 0) {
                $charge_create = $gateway->transaction()->sale(['paymentMethodToken' => $vBrainTreeToken, 'amount' => $vBrainTreeChargePrice, 'options' => ['submitForSettlement' => true]]);
                $status = $charge_create->success;
                $transactionid = $charge_create->transaction->id;
                /* if ($charge_create->success) {
                  $result = $gateway->transaction()->submitForSettlement($transactionid, $vBrainTreeChargePrice);
                  if ($result->success) {
                  $status = 1;
                  $settledTransaction = $result->transaction;
                  $transactionid = $settledTransaction->id;
                  }
                  } */
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
                $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
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
                    $transMsg = "LBL_CHARGE_COLLECT_FAILED";
                    if ($eChargeEvent == "submitRating") {
                        $transMsg = 'LBL_RETRY_FOR_TIP';
                    }
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $transMsg;
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $returnArr['status'] = "fail";
            $error3 = $e->getMessage();
            if ($eChargeEvent == "cancelTrip") {
                $eCancelChargeFailed = 'Yes';
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = $error3;
                //$returnArr['message']="LBL_CHARGE_COLLECT_FAILED";
                setDataResponse($returnArr);
            }
        }
    } else if ($APP_PAYMENT_METHOD == "Paymaya") {
        $vPaymayaCustId = $UserDetailPaymaya[0]['vPaymayaCustId'];
        $vPaymayaToken = $UserDetailPaymaya[0]['vPaymayaToken'];
        // $Ratio = get_value('currency', 'Ratio', 'vName', $currency, '', 'true');
        // $vPaymayaChargePrice = $vPaymayaChargePrice * $Ratio;
        // $vPaymayaChargePrice = round($vPaymayaChargePrice, 2);
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
            $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
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
                $transMsg = "LBL_CHARGE_COLLECT_FAILED";
                if ($eChargeEvent == "submitRating") {
                    $transMsg = 'LBL_RETRY_FOR_TIP';
                }
                $returnArr['Action'] = "0";
                $returnArr['message'] = $transMsg;
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
                $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
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
                    $transMsg = "LBL_CHARGE_COLLECT_FAILED";
                    if ($eChargeEvent == "submitRating") {
                        $transMsg = 'LBL_RETRY_FOR_TIP';
                    }
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $transMsg;
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $returnArr['status'] = "fail";
            $error3 = $e->getMessage();
            if ($eChargeEvent == "cancelTrip") {
                $eCancelChargeFailed = 'Yes';
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = $error3;
                //$returnArr['message']="LBL_CHARGE_COLLECT_FAILED";
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
                $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
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
                    $transMsg = "LBL_CHARGE_COLLECT_FAILED";
                    if ($eChargeEvent == "submitRating") {
                        $transMsg = 'LBL_RETRY_FOR_TIP';
                    }
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $transMsg;
                    setDataResponse($returnArr);
                }
            }
        } else {
            $returnArr['status'] = "fail";
            if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                $eCancelChargeFailed = "Yes";
            } else {
                $transMsg = "LBL_CHARGE_COLLECT_FAILED";
                if ($eChargeEvent == "submitRating") {
                    $transMsg = 'LBL_RETRY_FOR_TIP';
                }
                $returnArr['Action'] = "0";
                $returnArr['message'] = $transMsg;
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
            $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
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
                $error3 = "LBL_CHARGE_COLLECT_FAILED";
                if ($eChargeEvent == "submitRating") {
                    $error3 = 'LBL_RETRY_FOR_TIP';
                }
                $returnArr['Action'] = "0";
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

            $paymentstatus = $changedData['status']; /* PAYMENT_SUCCESS */
            if ($vFlutterWaveChargePrice == 0 || $paymentstatus == 'success') {
                $payment_arr['FLUTTERWAVE_PUBLIC_KEY'] = $FLUTTERWAVE_PUBLIC_KEY;
                $payment_arr['FLUTTERWAVE_SECRET_KEY'] = $FLUTTERWAVE_SECRET_KEY;
                $tPaymentDetails = json_encode($payment_arr, JSON_UNESCAPED_UNICODE);
                $PaymentId = $changedData['data']['id'];
                $pay_data = array();
                $pay_data['tPaymentUserID'] = $PaymentId;
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iTripId'] = $iTripId;
                $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                $pay_data['tPaymentDetails'] = $tPaymentDetails;
                $pay_data['iAmountUser'] = $iFare;
                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                $returnArr['status'] = "success";
            } else {
                $returnArr['status'] = "fail";
                if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
                    $eCancelChargeFailed = "Yes";
                } else {
                    $transMsg = "LBL_CHARGE_COLLECT_FAILED";
                    if ($eChargeEvent == "submitRating") {
                        $transMsg = 'LBL_RETRY_FOR_TIP';
                    }
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $transMsg;
                    setDataResponse($returnArr);
                }
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
            setDataResponse($returnArr);
        }
        /* Added By PM On 09-12-2019 For Flutterwave Code End */
    }
    $returnArr['id'] = $id;
    $returnArr['eCancelChargeFailed'] = $eCancelChargeFailed;
    return $returnArr;
}

########################### Charge Customer App Payment Method Wise ##############################################################
############################## Display Price in Member's Currency    #############################################################

function getPriceUserCurrency($iMemberId, $eUserType = "Passenger", $fPrice) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
    } else {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
    }
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $Ratio = $UserDetailsArr['Ratio'];
    $fPrice = round(($fPrice * $Ratio), 2);
    $fPricewithsymbol = $currencySymbol . " " . formatnum($fPrice);
    $returnArr['fPrice'] = $fPrice;
    $returnArr['fPricewithsymbol'] = $fPricewithsymbol;
    $returnArr['currencySymbol'] = $currencySymbol;
    return $returnArr;
}

function getUserCurrencyLanguageDetails($iUserId = "") {
    global $obj, $generalobj, $tconfig, $UserCurrencyLanguageDetailsArr, $vSystemDefaultLangCode, $vSystemDefaultCurrencyName, $vSystemDefaultCurrencySymbol, $vSystemDefaultCurrencyRatio,$userDetailsArr,$currencyAssociateArr;
    $returnArr = array();
    $table_name = "default_lang_currency_data";
    //This Function Modified By HJ On 08-06-2020 For Optimization
    if ($iUserId != "") {
        $table_name = "register_user";
        if (!empty($UserCurrencyLanguageDetailsArr) && count($UserCurrencyLanguageDetailsArr) > 0 && !empty($UserCurrencyLanguageDetailsArr[$table_name . '_' . $iUserId])) {
            return $UserCurrencyLanguageDetailsArr[$table_name . '_' . $iUserId];
        }
        $passengerData = array();
        if(isset($userDetailsArr[$table_name."_".$iUserId]) && count($userDetailsArr[$table_name."_".$iUserId]) > 0){
            $vCurrencyPassenger = $userDetailsArr[$table_name."_".$iUserId][0]['vCurrencyPassenger'];
            if(isset($currencyAssociateArr[$vCurrencyPassenger])){
                $currencyAssociateArr[$vCurrencyPassenger]['vCurrencyPassenger'] = $vCurrencyPassenger;
                $currencyAssociateArr[$vCurrencyPassenger]['vLang'] = $userDetailsArr[$table_name."_".$iUserId][0]['vLang'];
                $passengerData[] = $currencyAssociateArr[$vCurrencyPassenger];
            }
        }
        if(count($passengerData) > 0){
            //Data Found From Global Array
        }else{
            $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM " . $table_name . " as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "' AND 1=1";
            $passengerData = $obj->MySQLSelect($sqlp);
        }
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
            if (!empty($vSystemDefaultLangCode)) {
                $vLanguage = $vSystemDefaultLangCode;
            } else {
                $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
        }
        if ($currencycode == "" || $currencycode == NULL) {
            if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol) && !empty($vSystemDefaultCurrencyRatio)) {
                $currencycode = $vSystemDefaultCurrencyName;
                $currencySymbol = $vSystemDefaultCurrencySymbol;
                $Ratio = $vSystemDefaultCurrencyRatio;
            } else {
                $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
                $currencyData = $obj->MySQLSelect($sqlp);
                $currencycode = $currencyData[0]['vName'];
                $currencySymbol = $currencyData[0]['vSymbol'];
                $Ratio = $currencyData[0]['Ratio'];
            }
        }
    } else {
        if (!empty($UserCurrencyLanguageDetailsArr) && count($UserCurrencyLanguageDetailsArr) > 0 && !empty($UserCurrencyLanguageDetailsArr[$table_name])) {
            return $UserCurrencyLanguageDetailsArr[$table_name];
        }
        //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
        if (!empty($vSystemDefaultLangCode)) {
            $vLanguage = $vSystemDefaultLangCode;
        } else {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
        if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol) && !empty($vSystemDefaultCurrencyRatio)) {
            $currencycode = $vSystemDefaultCurrencyName;
            $currencySymbol = $vSystemDefaultCurrencySymbol;
            $Ratio = $vSystemDefaultCurrencyRatio;
        } else {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    }
    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    if ($iUserId != "") {
        $UserCurrencyLanguageDetailsArr[$table_name . '_' . $iUserId] = $returnArr;
    } else {
        $UserCurrencyLanguageDetailsArr[$table_name] = $returnArr;
    }
    return $returnArr;
}

function getDriverCurrencyLanguageDetails($iDriverId = "") {
    global $obj, $generalobj, $tconfig, $DriverCurrencyLanguageDetailsArr, $vSystemDefaultLangCode, $vSystemDefaultCurrencyName, $vSystemDefaultCurrencySymbol, $vSystemDefaultCurrencyRatio,$userDetailsArr,$currencyAssociateArr;
    $returnArr = array();
    //This Function Modified By HJ On 08-06-2020 For Optimization
    $table_name = "default_lang_currency_data";
    if ($iDriverId != "") {
        $table_name = "register_driver";
        if (!empty($DriverCurrencyLanguageDetailsArr) && count($DriverCurrencyLanguageDetailsArr) > 0 && !empty($DriverCurrencyLanguageDetailsArr[$table_name . '_' . $iDriverId])) {
            return $DriverCurrencyLanguageDetailsArr[$table_name . '_' . $iDriverId];
        }
        $driverData = array();
        if(isset($userDetailsArr[$table_name."_".$iDriverId]) && count($userDetailsArr[$table_name."_".$iDriverId]) > 0){
            $vCurrencyDriver = $userDetailsArr[$table_name."_".$iDriverId][0]['vCurrencyDriver'];
            if(isset($currencyAssociateArr[$vCurrencyDriver])){
                $currencyAssociateArr[$vCurrencyDriver]['vCurrencyPassenger'] = $vCurrencyDriver;
                $currencyAssociateArr[$vCurrencyDriver]['vLang'] = $userDetailsArr[$table_name."_".$iDriverId][0]['vLang'];
                $driverData[] = $currencyAssociateArr[$vCurrencyPassenger];
            }
        }
        if(count($driverData) > 0){
            //Data Found From Global Array
        }else{
            $sqlp = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iDriverId . "'";
            $driverData = $obj->MySQLSelect($sqlp);
        }
        $currencycode = $driverData[0]['vCurrencyDriver'];
        $vLanguage = $driverData[0]['vLang'];
        $currencySymbol = $driverData[0]['vSymbol'];
        $Ratio = $driverData[0]['Ratio'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
            if (!empty($vSystemDefaultLangCode)) {
                $vLanguage = $vSystemDefaultLangCode;
            } else {
                $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
        }
        if ($currencycode == "" || $currencycode == NULL) {
            if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol) && !empty($vSystemDefaultCurrencyRatio)) {
                $currencycode = $vSystemDefaultCurrencyName;
                $currencySymbol = $vSystemDefaultCurrencySymbol;
                $Ratio = $vSystemDefaultCurrencyRatio;
            } else {
                $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
                $currencyData = $obj->MySQLSelect($sqlp);
                $currencycode = $currencyData[0]['vName'];
                $currencySymbol = $currencyData[0]['vSymbol'];
                $Ratio = $currencyData[0]['Ratio'];
            }
        }
    } else {
        if (!empty($DriverCurrencyLanguageDetailsArr) && count($DriverCurrencyLanguageDetailsArr) > 0 && !empty($DriverCurrencyLanguageDetailsArr[$table_name])) {
            return $DriverCurrencyLanguageDetailsArr[$table_name];
        }
        //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
        if (!empty($vSystemDefaultLangCode)) {
            $vLanguage = $vSystemDefaultLangCode;
        } else {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
        if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol) && !empty($vSystemDefaultCurrencyRatio)) {
            $currencycode = $vSystemDefaultCurrencyName;
            $currencySymbol = $vSystemDefaultCurrencySymbol;
            $Ratio = $vSystemDefaultCurrencyRatio;
        } else {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    }
    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    if ($iDriverId != "") {
        $DriverCurrencyLanguageDetailsArr[$table_name . '_' . $iDriverId] = $returnArr;
    } else {
        $DriverCurrencyLanguageDetailsArr[$table_name] = $returnArr;
    }
    return $returnArr;
}

########################### Display Price in Member's Currency    #############################################################
############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  ###################################################################

function CheckUserSmsLimit($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT, $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCount,dSendverificationDate,vLang';
        $condfield = 'iUserId';
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
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
    $currentdate = @date("Y-m-d H:i:s");
    $totalMinute = @round(abs(strtotime($dSendverificationDate) - strtotime($currentdate)) / 60);
    $totalSeconds = @round(abs(strtotime($dSendverificationDate) - strtotime($currentdate)));
    $hours = floor($totalSeconds / 3600);
    $mins = floor(($totalSeconds / 60) % 60);
    $seconds = $totalSeconds % 60;

    $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
    //$LBL_MINUTES_TXT = ($mins > 1)? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
    $LBL_MINUTES_TXT = ($mins > 1) ? $languageLabelsArr['LBL_MINS_SMALL'] : $languageLabelsArr['LBL_MINUTE'];

    $LBL_SECONDS_TXT = $languageLabelsArr['LBL_SECONDS_TXT'];

    if ($hours >= 1) {
        $timeDurationDisplay = $hours . " " . $LBL_HOURS_TXT . " " . $mins . " " . $LBL_MINUTES_TXT . " " . $seconds . " " . $LBL_SECONDS_TXT;
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

############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  ###############################
############################# Update  User's  SMS Resending Limit and Rest Verification count and date  ###############################

function UpdateUserSmsLimit($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT, $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCount,dSendverificationDate';
        $condfield = 'iUserId';
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

############################# Update  User's  SMS Resending Limit and Rest Verification count and date  #############################
############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  For Emergency Contact ###########################################

function CheckUserSmsLimitForEmergency($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY, $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY;
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
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
    $currentdate = @date("Y-m-d H:i:s");
    $totalMinute = @round(abs(strtotime($dSendverificationDate) - strtotime($currentdate)) / 60);
    $totalSeconds = abs(strtotime($dSendverificationDate) - strtotime($currentdate));
    $hours = floor($totalMinute / 60); // No. of mins/60 to get the hours and round down
    $mins = $totalMinute % 60; // No. of mins/60 - remainder (modulus) is the minutes
    $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];
    //$LBL_MINUTES_TXT = ($mins > 1)? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
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

############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  For Emergency Contact######################################
################################## Get User Timezone From lat long######################################

function getlatlongTimeZone($latitude, $longitude) {
    global $obj, $generalobj, $tconfig, $GOOGLE_SEVER_API_KEY_WEB;
    $time = time();
    $url = "https://maps.googleapis.com/maps/api/timezone/json?location=$latitude,$longitude&timestamp=$time&key=" . $GOOGLE_SEVER_API_KEY_WEB;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $responseJson = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($responseJson); //echo "<pre>"; print_r($response);exit;
    //var_dump($response);
    $timeZone = $response->timeZoneId;
    $errorMessage = $response->errorMessage;
    if ($errorMessage != "") {
        $timeZone = "";
    }
    return $timeZone;
}

############################################ Get User Timezone From lat long########################################################
############################# Get User Timezone Date From Pickup Address Or lat long################################################

function getPassengerTimeZoneDate($PickUpAddress, $latitude, $longitude, $scheduleDate = "") {
    global $obj, $generalobj, $tconfig, $GOOGLE_SEVER_API_KEY_WEB, $vTimeZone;
    $UserTimeZone = "";
    $UserTimeZoneDate = @date("Y-m-d H:i:s");
    if ($PickUpAddress != "") {
        $vAddress_arr = explode(",", $PickUpAddress);
        $vAddress = end($vAddress_arr);
        $vAddress = trim($vAddress);
        $sql = "SELECT vTimeZone FROM  `country` WHERE `vCountry` like '%$vAddress%' OR vCountryCodeISO_3 like '%$vAddress%'";
        $db_sql = $obj->MySQLSelect($sql);
        if (count($db_sql) == 1) {
            $UserTimeZone = $db_sql[0]['vTimeZone'];
        }
    }
    if ($UserTimeZone == "") {
        $UserTimeZone = getlatlongTimeZone($latitude, $longitude);
    }
    if ($UserTimeZone == "") {
        $UserTimeZone = $vTimeZone;
    }
    if ($scheduleDate == "") {
        $scheduleDate = @date("Y-m-d H:i:s");
    }
    if ($UserTimeZone != "") {
        $systemTimeZone = date_default_timezone_get();
        $UserTimeZoneDate = converToTz($scheduleDate, $UserTimeZone, $systemTimeZone);
    }
    return $UserTimeZoneDate;
}

###################### Get User Timezone Date From Pickup Address Or lat long#######################################################

function create_wallet_deduction($iUserId, $tripId) {
    global $generalobj, $obj;
    /* Check debit wallet For Count Total Fare  Start */
    $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
    $user_wallet_debit_amount = 0;
    // $total_fare = get_value('trips', 'fTripGenerateFare', 'iTripId', $tripId, '', 'true');
    $total_fare = get_value('trips', 'iFare', 'iTripId', $tripId, '', 'true');
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
        $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING# " . $vRideNo;
        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);
        //$obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
    }
    /* Check debit wallet For Count Total Fare  End */
    $returnArr['iFare'] = $total_fare;
    $returnArr['fWalletDebit'] = $user_wallet_debit_amount;
    return $returnArr;
}

function secondsToTime($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;
    // extract days
    $days = floor($inputSeconds / $secondsInADay);
    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);
    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);
    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);
    // return the final array
    $tottimearr = array(
        'd' => (int) $days,
        'h' => (int) $hours,
        'm' => (int) $minutes,
        's' => (int) $seconds,
    );
    return $tottimearr;
}

?>
