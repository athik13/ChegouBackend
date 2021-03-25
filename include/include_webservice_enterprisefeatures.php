<?php

############### Check FlatTrip Or Not  ###################################################################

function checkFlatTripnew($Source_point_Address, $Destination_point_Address, $iVehicleTypeId, $iRentalPackageId = "0") {
    global $generalobj, $obj;
    $returnArr = array();
    if (!empty($iRentalPackageId) && $iRentalPackageId > 0) {
        $returnArr['eFlatTrip'] = "No";
        $returnArr['Flatfare'] = 0;
        return $returnArr;
    }

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

function getCallMaskConfigNumber() {
    global $_REQUEST, $obj, $DEFAULT_COUNTRY_CODE_WEB, $generalobj, $tconfig, $CALLMASKING_ENABLED;

    $iTripId = isset($_REQUEST['iTripid']) ? $_REQUEST['iTripid'] : '';
    $GeneralDeviceType = isset($_REQUEST['GeneralDeviceType']) ? $_REQUEST['GeneralDeviceType'] : '';
    $UserType = isset($_REQUEST['UserType']) ? $_REQUEST['UserType'] : '';

    $iDriverId = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
    $vCountry = get_value('register_driver', 'vCountry', 'iDriverId', $iDriverId, '', true);
    if ($vCountry == "" || $vCountry == NULL) {
        $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
    }

    $checktrip = "SELECT tm.mask_number,tm.call_limit FROM trip_call_masking  as tm LEFT JOIN trips as t on t.iTripId =  tm.iTripid WHERE t.iTripId = '" . $iTripId . "' AND (t.iActive != 'Canceled' && t.iActive != 'Finished') ";
    $checkdata_exists = $obj->MySQLSelect($checktrip);
    if (count($checkdata_exists) > 0) {
        if (SITE_TYPE == "Demo") {
            if ($checkdata_exists[0]['call_limit'] <= 5) {
                $noOfCall_Limit = get_value('trip_call_masking', 'call_limit', 'iTripid', $iTripId, '', 'true');
                $where = " iTripid = '" . $iTripId . "'";
                $data_mask['call_limit'] = $noOfCall_Limit + 1;
                $obj->MySQLQueryPerform("trip_call_masking", $data_mask, 'update', $where);

                $returndata = array();
                $returnArr['Action'] = "1";
                $returnArr['message'] = strval($checkdata_exists[0]['mask_number']);
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "In our demo apps , You can make upto five masking calls.";
            }
        } else {
            $returndata = array();
            $returnArr['Action'] = "1";
            $returnArr['message'] = strval($checkdata_exists[0]['mask_number']);
        }
    } else {
        $sql = "SELECT rd.vCode as DriverPhoneCode, rd.vPhone as DriverPhone, ru.vPhoneCode as UserPhoneCode, ru.vPhone as RiderPhone FROM `trips` as t LEFT JOIN `register_user` as ru on ru.iUserId = t.iUserId LEFT JOIN `register_driver` as rd on rd.iDriverId= t.iDriverId  WHERE t.iTripId = " . $iTripId . " AND (t.iActive != 'Canceled' && t.iActive != 'Finished')";
        $getTripDetails = $obj->MySQLSelect($sql);
        $CALLMASKING_ENABLED = $CALLMASKING_ENABLED;
        if ($CALLMASKING_ENABLED == "Yes") {
            $check_query = "SELECT tm.mask_number FROM trip_call_masking  as tm LEFT JOIN trips as t on t.iTripId =  tm.iTripid WHERE (t.iActive != 'Canceled' && t.iActive != 'Finished')";
            $getTripmaskDetails = $obj->MySQLSelect($check_query);
            foreach ($getTripmaskDetails as $key => $value) {
                $all_masknumber[] = $value['mask_number'];
            }
            $alloted_maskingnumber = implode("', '", $all_masknumber);

            if (count($all_masknumber) > 0) {
                $alloted_maskingnumber = implode("', '", $all_masknumber);
            } else {
                $alloted_maskingnumber = 0;
            }

            $query = "SELECT masknum_id,mask_number FROM  `masking_numbers` WHERE vCountry = '" . $vCountry . "' AND `mask_number` NOT IN ('" . $alloted_maskingnumber . "') ORDER BY RAND() LIMIT 1";
            $random_masknumber = $obj->MySQLSelect($query);

            $data = array();
            if (!empty($random_masknumber) && !empty($getTripDetails)) {
                $data['iTripid'] = $iTripId;
                $data['DriverPhoneCode'] = $getTripDetails[0]['DriverPhoneCode'];
                $data['DriverPhone'] = $getTripDetails[0]['DriverPhone'];
                $data['UserPhoneCode'] = $getTripDetails[0]['UserPhoneCode'];
                $data['RiderPhone'] = $getTripDetails[0]['RiderPhone'];
                $data['mask_number'] = $random_masknumber[0]['mask_number'];
                $data['maskId'] = $random_masknumber[0]['masknum_id'];
                $data['call_limit'] = 1;

                $insert_masking_trip = $obj->MySQLQueryPerform('trip_call_masking', $data, 'insert');
                $iTripCallmaskid = $insert_masking_trip;
                if ($insert_masking_trip) {
                    $masknumber = get_value('trip_call_masking', 'mask_number', 'iTripCallmaskid', $iTripCallmaskid, '', 'true');

                    $returnArr['Action'] = "1";
                    //$returnArr['message'] = $masknumberarray;
                    $returnArr['message'] = strval($masknumber);
                } else {
                    if ($UserType == "Driver") {
                        $phonNum = '+' . $getTripDetails[0]['UserPhoneCode'] . $getTripDetails[0]['RiderPhone'];
                    } else {
                        $phonNum = '+' . $getTripDetails[0]['DriverPhoneCode'] . $getTripDetails[0]['DriverPhone'];
                    }
                    $returnArr['Action'] = "1";
                    $returnArr['message'] = $phonNum;
                }
                $i++;
            } else {
                if ($UserType == "Driver") {
                    $phonNum = '+' . $getTripDetails[0]['UserPhoneCode'] . $getTripDetails[0]['RiderPhone'];
                } else {
                    $phonNum = '+' . $getTripDetails[0]['DriverPhoneCode'] . $getTripDetails[0]['DriverPhone'];
                }
                $returnArr['Action'] = "1";
                $returnArr['message'] = $phonNum;
            }
        } else {
            if ($UserType == "Driver") {
                $phonNum = '+' . $getTripDetails[0]['UserPhoneCode'] . $getTripDetails[0]['RiderPhone'];
            } else {
                $phonNum = '+' . $getTripDetails[0]['DriverPhoneCode'] . $getTripDetails[0]['DriverPhone'];
            }
            $returnArr['Action'] = "1";
            $returnArr['message'] = $phonNum;
        }
    }

    return $returnArr;
}

############################ Ride Features #######################################

function checkHailRideEnable($vCarType) {
    global $obj;

    $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
    $db_cartype = $obj->MySQLSelect($sql);

    $enable_hail_flag = "No";
    if (count($db_cartype) > 0) {
        for ($j = 0; $j < count($db_cartype); $j++) {
            if ($db_cartype[$j]['eType'] == "Ride") {
                $enable_hail_flag = "Yes";
            }
        }
    }

    return ($enable_hail_flag == "Yes") ? "Yes" : "No";
}

################################################Get Rental Packages ################################################################

/* Added For Rental */

function calculateAdditionalTime($startDate, $endDate, $rentalTimeHours, $userlangcode) {
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    $TotalTimeInMinutes_trip = @round(abs(strtotime($startDate) - strtotime($endDate)) / 60, 2);
    $RentalDefineMinutes = $rentalTimeHours * 60;
    if ($TotalTimeInMinutes_trip > $RentalDefineMinutes) {
        $MinutesDiff = $TotalTimeInMinutes_trip - $RentalDefineMinutes;
        $AdditionTime = mediaTimeDeFormater($MinutesDiff, $userlangcode);
    } else {
        $AdditionTime = "0.00 " . " " . $languageLabelsArr['LBL_MINUTE'];
    }
    return $AdditionTime;
}

function mediaTimeDeFormater($minutes, $userlangcode) {
    $seconds = @round(abs($minutes * 60));
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    $ret = "";
    $hours = (string) floor($seconds / 3600);
    $secs = (string) $seconds % 60;
    $mins = (string) floor(($seconds - ($hours * 3600)) / 60);
    if (strlen($hours) == 1)
        $hours = "0" . $hours;
    if (strlen($secs) == 1)
        $secs = "0" . $secs;
    if (strlen($mins) == 1)
        $mins = "0" . $mins;
    if ($hours == 0) {
        $mint = "";
        $secondss = "";
        if ($mins > 01) {
            $mint = "$mins";
        } else {
            $mint = "$mins";
        }
        if ($secs > 01) {
            $secondss = "$secs";
        } else {
            $secondss = "$secs";
        }
        $LBL_MINUTES_TXT = ($mins > 1) ? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
        if ($mins > 0) {
            $ret = $mint . ":" . $secondss . " " . $LBL_MINUTES_TXT;
        } else {
            $ret = $secondss . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
        }
    } else {
        $mint = "";
        $secondss = "";
        if ($mins > 01) {
            $mint = "$mins";
        } else {
            $mint = "$mins";
        }
        if ($secs > 01) {
            $secondss = "$secs";
        } else {
            $secondss = "$secs";
        }
        if ($hours > 01) {
            $ret = $hours . ":" . $mint . ":" . $secondss . " " . $languageLabelsArr['LBL_HOURS_TXT'];
        } else {
            $ret = $hours . ":" . $mint . ":" . $secondss . " " . $languageLabelsArr['LBL_HOUR_TXT'];
        }
    }
    return $ret;
}

function generateEstimatedRentalFare($data_calculation_arr) {
    global $obj, $_REQUEST;

    $ssql_particularRentalId = "";
    if (!empty($_REQUEST["iRentalPackageId"]) && $_REQUEST["iRentalPackageId"] > 0) {
        $ssql_particularRentalId = " AND iRentalPackageId = '" . $_REQUEST["iRentalPackageId"] . "'";
    }
    $checkrentalquery = "SELECT iRentalPackageId,iVehicleTypeId,vPackageName_" . $data_calculation_arr['userlangcode'] . ",fPrice,fKiloMeter,fHour,fPricePerKM,fPricePerHour FROM `rental_package` WHERE iVehicleTypeId = '" . $data_calculation_arr['iVehicleTypeId'] . "'" . $ssql_particularRentalId . " ORDER BY `fPrice` ASC";
    $rental_data = $obj->MySQLSelect($checkrentalquery);
    $totalrental = count($rental_data);
    $eRental_total_fare_value = 0;
    if ($totalrental > 0) {
        $eRental = 'Yes';
        $fPrice = $rental_data[0]['fPrice'];
        if ($data_calculation_arr['couponCode'] != "") {
            $discountValue_rental = $data_calculation_arr['discountValue_orig'];
            if ($data_calculation_arr['discountValueType'] == "percentage") {
                $discountValue_rental = round(($fPrice * $data_calculation_arr['discountValue_orig']), 1) / 100;
            }
        }
        $total_rental_fare = $fPrice - $discountValue_rental;
        if ($total_rental_fare < 0) {
            $total_rental_fare = 0;
        }
        $eRental_total_fare_value = round($total_rental_fare * $data_calculation_arr['priceRatio'], 1);
        $eRental_total_fare = $data_calculation_arr['vSymbol'] . " " . number_format(round($total_rental_fare * $data_calculation_arr['priceRatio'], 1), 2);
    } else {
        $eRental = 'No';
        $eRental_total_fare = "";
    }

    $rentalFareDataArr['eRental'] = $eRental;
    $rentalFareDataArr['eRental_total_fare'] = $eRental_total_fare;
    $rentalFareDataArr['eRental_total_fare_value'] = $eRental_total_fare_value;

    return $rentalFareDataArr;
}

function generateRentalFare($Fare_data, $iRentalPackageId) {
    global $obj;

    $query = "SELECT `iRentalPackageId`, `iVehicleTypeId`, `vPackageName_EN`, `fPrice`, `fKiloMeter`, `fHour`, `fPricePerKM`, `fPricePerHour` FROM `rental_package` WHERE iRentalPackageId = " . $iRentalPackageId;
    $data_trip_pkg = $obj->MySQLSelect($query);
    $iBaseFare = round($data_trip_pkg[0]['fPrice'], 2);
    $RentalTripTimeMinutes = $Fare_data[0]['TripTimeMinutes'];
    $RentalOrgMinutes = $data_trip_pkg[0]['fHour'] * 60;
    if ($RentalTripTimeMinutes > $RentalOrgMinutes) {
        $extra_min = $RentalTripTimeMinutes - $RentalOrgMinutes;
        $Minute_Fare = round($data_trip_pkg[0]['fPricePerHour'] * $extra_min, 2);
    } else {
        $Minute_Fare = 0;
    }
    $TripKilometer = getVehicleCountryUnit($iVehicleTypeId, $data_trip_pkg[0]['fKiloMeter']);
    $rPricePerKM = getVehicleCountryUnit_PricePerKm($iVehicleTypeId, $data_trip_pkg[0]['fPricePerKM']);
    if ($Fare_data[0]['TripDistance'] > $TripKilometer) {
        $extradistance = $Fare_data[0]['TripDistance'] - $TripKilometer;
        $Distance_Fare = round($rPricePerKM * $extradistance, 2);
    }

    $data_arr['MINUTE_FARE'] = $Minute_Fare;
    $data_arr['DISTANCE_FARE'] = $Distance_Fare;
    $data_arr['BASE_FARE'] = $iBaseFare;

    return $data_arr;
}

function validateVehicleTypes($vehicleTypes) {
    global $_REQUEST;

    $eRental = isset($_REQUEST["eRental"]) ? $_REQUEST["eRental"] : 'No'; // Yes Or No

    if ($eRental == "" || $eRental == NULL) {
        $eRental = "No";
    }

    if ($eRental == "Yes") {
        $vehicleTypes_New = array();
        $vehicleTypes_New = $vehicleTypes;
        for ($i = 0; $i < count($vehicleTypes); $i++) {
            $isRemoveFromVehicleList = "Yes";
            $eRental = $vehicleTypes[$i]['eRental'];
            if ($eRental == "Yes") {
                $isRemoveFromVehicleList = "No";
            }
            if ($isRemoveFromVehicleList == "Yes") {
                unset($vehicleTypes_New[$i]);
            }
        }
        return array_values($vehicleTypes_New);
    }

    return $vehicleTypes;
}

function isRentalEnable($iVehicleTypeId) {
    global $obj;
    /* added for rental */
    $eRental = 'No';
    if (ENABLE_RENTAL_OPTION == 'Yes') {
        $checkrentalquery = "SELECT count(iRentalPackageId) as totalrental FROM  `rental_package` WHERE iVehicleTypeId = '" . $iVehicleTypeId . "'";
        $rental_data = $obj->MySQLSelect($checkrentalquery);
        if ($rental_data[0]['totalrental'] > 0) {
            $eRental = 'Yes';
        } else {
            $eRental = 'No';
        }
    }
    /* end added for rental */

    return $eRental;
}

function getRentalData($iRentalPackageId) {
    global $obj;
    $sql = "SELECT * FROM rental_package WHERE iRentalPackageId='" . $iRentalPackageId . "'";
    $rentalData = $obj->MySQLSelect($sql);
    return $rentalData;
}

function getRentalPrice_ByCountry($iUserId, $UserType, $vehicleTypeID, $fPricePerKM) {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT;
    if ($UserType == 'Passenger') {
        $vCountry = get_value("register_user", "vCountry", "iUserId", $iUserId, '', 'true');
    } else {
        $vCountry = get_value("register_driver", "vCountry", "iDriverId", $iUserId, '', 'true');
    }
    if ($vCountry == "") {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    } else {
        $eUnit = get_value("country", "eUnit", "vCountryCode", $vCountry, '', 'true');
    }
    if ($eUnit == "" || $eUnit == NULL) {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    }
    $iLocationid = get_value("vehicle_type", "iLocationid", "iVehicleTypeId", $vehicleTypeID, '', 'true');
    $iCountryId = get_value("location_master", "iCountryId", "iLocationId", $iLocationid, '', 'true');
    if ($iLocationid == "-1") {
        $eUnit_vehicle = $DEFAULT_DISTANCE_UNIT;
    } else {
        $eUnit_vehicle = get_value("country", "eUnit", "iCountryId", $iCountryId, '', 'true');
    }
    if ($eUnit_vehicle == "" || $eUnit_vehicle == NULL) {
        $eUnit_vehicle = $DEFAULT_DISTANCE_UNIT;
    }
    if ($eUnit == $eUnit_vehicle) {
        $PricePerKM = $fPricePerKM;
    } else {
        if ($eUnit == 'Miles' && $eUnit_vehicle == 'KMs') {
            $PricePerKM = $fPricePerKM * 0.621371;
        } else if ($eUnit == 'KMs' && $eUnit_vehicle == 'Miles') {
            $PricePerKM = $fPricePerKM * 0.621371;
        } else {
            $PricePerKM = $fPricePerKM * 1.60934;
        }
    }
    return $PricePerKM;
}

function getRentalKilometer_ByCountry($iUserId, $UserType, $vehicleTypeID, $fPricePerKM, $isReturnArr = false) {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT;
    if ($UserType == 'Passenger') {
        $vCountry = get_value("register_user", "vCountry", "iUserId", $iUserId, '', 'true');
    } else {
        $vCountry = get_value("register_driver", "vCountry", "iDriverId", $iUserId, '', 'true');
    }
    if ($vCountry == "") {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    } else {
        $eUnit = get_value("country", "eUnit", "vCountryCode", $vCountry, '', 'true');
    }
    if ($eUnit == "" || $eUnit == NULL) {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    }
    $iLocationid = get_value("vehicle_type", "iLocationid", "iVehicleTypeId", $vehicleTypeID, '', 'true');
    $iCountryId = get_value("location_master", "iCountryId", "iLocationId", $iLocationid, '', 'true');
    if ($iLocationid == "-1") {
        $eUnit_vehicle = $DEFAULT_DISTANCE_UNIT;
    } else {
        $eUnit_vehicle = get_value("country", "eUnit", "iCountryId", $iCountryId, '', 'true');
    }
    if ($eUnit_vehicle == "" || $eUnit_vehicle == NULL) {
        $eUnit_vehicle = $DEFAULT_DISTANCE_UNIT;
    }
    if ($eUnit == $eUnit_vehicle) {
        $PricePerKM = $fPricePerKM;
    } else {
        if ($eUnit == 'Miles' && $eUnit_vehicle == 'KMs') {
            $PricePerKM = $fPricePerKM * 0.621371;
        } else if ($eUnit == 'KMs' && $eUnit_vehicle == 'Miles') {
            $PricePerKM = $fPricePerKM * 0.621371;
        } else {
            $PricePerKM = $fPricePerKM * 1.60934;
        }
    }

    if ($isReturnArr) {
        $returnArr_km_data = array();
        $returnArr_km_data['AllowedDistance'] = strval($PricePerKM);
        $returnArr_km_data['DistanceUnit'] = $eUnit;

        return $returnArr_km_data;
    }

    return $PricePerKM;
}

/* End added for rental */
if ($type == "getRentalPackages") {
    global $generalobj, $obj;
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';
    $GeneralMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $couponCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';

    if ($UserType == 'Passenger') {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $GeneralMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $vLang = $passengerData[0]['vLang'];
        $vCurrency = $passengerData[0]['vCurrencyPassenger'];
        $vCurrencySymbol = $passengerData[0]['vSymbol'];
        $priceRatio = $passengerData[0]['Ratio'];
    } else {
        $sqlp = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $GeneralMemberId . "'";
        $DriverData = $obj->MySQLSelect($sqlp);
        $vLang = $DriverData[0]['vLang'];
        $vCurrency = $DriverData[0]['vCurrencyDriver'];
        $vCurrencySymbol = $DriverData[0]['vSymbol'];
        $priceRatio = $DriverData[0]['Ratio'];
    }
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    //$trackingMessage = $languageLabelsArr['LBL_PLEASE_CHECK_LAST_LOCATION_TRACKING_MESSAGE'];

    $sql = "SELECT iRentalPackageId,vPackageName_" . $vLang . " as vPackageName,fPrice,fKiloMeter,fHour,fPricePerKM,fPricePerHour FROM `rental_package` WHERE iVehicleTypeId = '$iVehicleTypeId' ORDER BY `fPrice` ASC ";
    $RentalPackagesData = $obj->MySQLSelect($sql);
    $totalcount = count($RentalPackagesData);
    for ($i = 0; $i < count($RentalPackagesData); $i++) {
        $fKiloMeter_data = getRentalKilometer_ByCountry($GeneralMemberId, $UserType, $iVehicleTypeId, $RentalPackagesData[$i]['fKiloMeter'], true);
        $fKiloMeter = $fKiloMeter_data['AllowedDistance'];

        $RentalPackagesData[$i]['fKiloMeter'] = formatNum($fKiloMeter);

        $RentalPackagesData[$i]['fKiloMeter_data'] = formatNum($fKiloMeter) . " " . ($fKiloMeter_data['DistanceUnit'] == "KMs" ? $languageLabelsArr['LBL_DISPLAY_KMS'] : $languageLabelsArr['LBL_MILE_DISTANCE_TXT']);


        $fPricePerKM = getRentalPrice_ByCountry($GeneralMemberId, $UserType, $iVehicleTypeId, $RentalPackagesData[$i]['fPricePerKM']);
        $RentalPackagesData[$i]['fPricePerKM'] = $vCurrencySymbol . formatNum($fPricePerKM * $priceRatio);
        $RentalPackagesData[$i]['fPricePerHour'] = $vCurrencySymbol . formatNum($RentalPackagesData[$i]['fPricePerHour'] * $priceRatio);
        //$RentalPackagesData[$i]['fPrice']= $vCurrencySymbol.formatNum($RentalPackagesData[$i]['fPrice'] * $priceRatio);
        $fPrice = $RentalPackagesData[$i]['fPrice'];
        ### Checking Promocode Discount ##
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
                $discountValue = round(($fPrice * $discountValue), 1) / 100;
            } else {
                $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                if ($discountValue > $fPrice) {
                    $vDiscount = round($fPrice, 1) . ' ' . $curr_sym;
                } else {
                    $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
                }
            }
            $fPrice = $fPrice - $discountValue;
            if ($fPrice < 0) {
                $fPrice = 0;
            }
        }
        $RentalPackagesData[$i]['fPrice'] = $vCurrencySymbol . formatNum($fPrice * $priceRatio);
        ### Checking Promocode Discount ##
    }
    if ($totalcount > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $RentalPackagesData;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $get_make = "SELECT m.vMake,mo.vTitle FROM driver_vehicle as dv LEFT JOIN make as m on m.iMakeId=dv.iMakeId LEFT JOIN model as mo on mo.iModelId=dv.iModelId WHERE dv.iMakeId > 0 AND FIND_IN_SET ('" . $iVehicleTypeId . "', dv.vRentalCarType) GROUP BY m.vMake LIMIT 0,3";
    $makemodaldata = $obj->MySQLSelect($get_make);
    $s = array();
    if (!empty($makemodaldata)) {
        foreach ($makemodaldata as $key => $value) {
            $s[] = $value['vMake'] . $value['vTitle'];
        }
        $returnArr['vehicle_list_title'] = implode(', ', $s);
    } else {
        $returnArr['vehicle_list_title'] = '';
    }
    $pageDesc = get_value('pages', 'tPageDesc_' . $vLang, 'iPageId', '46', '', 'true');
    $returnArr['page_desc'] = $pageDesc;
    setDataResponse($returnArr);
}
################################################ Get Rental Packages   ################################################################
###########################################################################
if ($type == "getDriverVehicleDetails") {
    $driverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $StartLatitude = isset($_REQUEST["StartLatitude"]) ? $_REQUEST["StartLatitude"] : '0.0';
    $EndLongitude = isset($_REQUEST["EndLongitude"]) ? $_REQUEST["EndLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';
    $time = round(($time / 60), 2);
    $distance = round(($distance / 1000), 2);
    $VehicleTypeIds = isset($_REQUEST["VehicleTypeIds"]) ? $_REQUEST["VehicleTypeIds"] : '';
    $isDestinationAdded = "No";
    if ($DestLatitude != "" && $DestLongitude != "") {
        $isDestinationAdded = "Yes";
    }
    /* added for rental */
    if ($userType == "Passenger") {
        $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $driverId, '', 'true');
        $vLang = get_value("register_user", "vLang", "iUserId", $driverId, '', 'true');
    } else {
        $vCurrencyPassenger = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
        $vLang = get_value("register_driver", "vLang", "iDriverId", $driverId, '', 'true');
    }
    /* end added for rental */
    //$vLang = get_value('register_driver', 'vLang', 'iDriverId', $driverId,'','true');
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    /* added for rental */
    if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $vSymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    /* end added for rental */
    $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $driverId, '', 'true');
    if ($iDriverVehicleId > 0) {
        $Fare_Data = array();
        $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
        $DriverVehicle_Arr = explode(",", $vCarType);
        //echo "<pre>";print_r($DriverVehicle_Arr);echo "<br />";
        //$sql11 = "SELECT vVehicleType_".$vLang." as vVehicleTypeName, iVehicleTypeId, vLogo, iPersonSize FROM `vehicle_type`  WHERE  iVehicleTypeId IN (".$vCarType.") AND eType='Ride'";
        if ($VehicleTypeIds != "") {
            $wherePool = "";
            if ($userType == "Driver") {
                $wherePool = " AND ePoolStatus='No'";
            }
            $sql11 = "SELECT  vVehicleType_" . $vLang . " as vVehicleTypeName,iVehicleTypeId,vRentalAlias_" . $vLang . " as vRentalVehicleTypeName,iVehicleTypeId, vLogo,vLogo1, iPersonSize FROM vehicle_type WHERE iVehicleTypeId IN (" . $VehicleTypeIds . ") AND eType='Ride' AND eStatus='Active' $wherePool";
        } else {
            $pickuplocationarr = array(
                $StartLatitude,
                $EndLongitude
            );
            $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
            $sql_vehicle = "SELECT iVehicleTypeId,ePoolStatus FROM vehicle_type WHERE iLocationid IN (" . $GetVehicleIdfromGeoLocation . ") AND eType='Ride' AND eStatus='Active'";
            $db_vehicle_location = $obj->MySQLSelect($sql_vehicle);
            $array_vehiclie_id = array();

            for ($i = 0; $i < count($db_vehicle_location); $i++) {
                if ($db_vehicle_location[$i]['ePoolStatus'] == "No" && $userType == "Driver") {
                    array_push($array_vehiclie_id, $db_vehicle_location[$i]['iVehicleTypeId']);
                }
            }
            //echo "<pre>";print_r($array_vehiclie_id);echo "<br />";
            $Vehicle_array_diff = array_values(array_intersect($DriverVehicle_Arr, $array_vehiclie_id));
            $VehicleTypeIds_Str = implode(",", $Vehicle_array_diff);
            if ($VehicleTypeIds_Str == "") {
                $VehicleTypeIds_Str = "0";
            }
            $sql11 = "SELECT  vVehicleType_" . $vLang . " as vVehicleTypeName,vRentalAlias_" . $vLang . " as vRentalVehicleTypeName,iVehicleTypeId, vLogo,vLogo1, iPersonSize FROM vehicle_type WHERE iVehicleTypeId IN (" . $VehicleTypeIds_Str . ") AND eType='Ride' AND eStatus='Active'";
        }
        $sql11 .= " And eFly!=1"; //added by SP for fly on 27-09-2019 for hail features fly vehicles do not shown
        $vCarType_Arr = $obj->MySQLSelect($sql11);
        $Fare_Data = array();
        if (count($vCarType_Arr) > 0) {
            for ($i = 0; $i < count($vCarType_Arr); $i++) {
                ######### Checking For Flattrip #########
                $eFlatTrip = "No";
                $fFlatTripPrice = 0;
                if ($isDestinationAdded == "Yes") {
                    $sourceLocationArr = array(
                        $StartLatitude,
                        $EndLongitude
                    );
                    $destinationLocationArr = array(
                        $DestLatitude,
                        $DestLongitude
                    );
                    $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $vCarType_Arr[$i]['iVehicleTypeId']);
                    $eFlatTrip = $data_flattrip['eFlatTrip'];
                    $fFlatTripPrice = $data_flattrip['Flatfare'];
                }
                $Fare_Data[$i]['eFlatTrip'] = $eFlatTrip;
                $Fare_Data[$i]['fFlatTripPrice'] = $fFlatTripPrice;
                ######### Checking For Flattrip #########

                $dropOffLocationArr = array(
                    $DestLatitude,
                    $DestLongitude
                );
                $IS_RETURN_ARR_WITH_ORIG_AMT = "Yes";
                $Fare_Single_Vehicle_Data = calculateFareEstimateAll($time, $distance, $vCarType_Arr[$i]['iVehicleTypeId'], $driverId, 1, "", "", "", 1, 0, 0, 0, "DisplySingleVehicleFare", "Driver", 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $pickuplocationarr, $dropOffLocationArr);
                $IS_RETURN_ARR_WITH_ORIG_AMT = "No";
                
                $Fare_Data[$i]['iVehicleTypeId'] = $vCarType_Arr[$i]['iVehicleTypeId'];
                $Fare_Data[$i]['vVehicleTypeName'] = $vCarType_Arr[$i]['vVehicleTypeName'];
                //$Fare_Data[$i]['vLogo'] = $vCarType_Arr[$i]['vLogo'];
                $Photo_Gallery_folder = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $vCarType_Arr[$i]['iVehicleTypeId'] . '/android/' . $vCarType_Arr[$i]['vLogo'];
                if ($vCarType_Arr[$i]['vLogo'] != "" && file_exists($Photo_Gallery_folder)) {
                    $Fare_Data[$i]['vLogo'] = $vCarType_Arr[$i]['vLogo'];
                } else {
                    $Fare_Data[$i]['vLogo'] = "";
                }
                $Photo_Gallery_folder_vLogo1 = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $vCarType_Arr[$i]['iVehicleTypeId'] . '/android/' . $vCarType_Arr[$i]['vLogo1'];
                if ($vCarType_Arr[$i]['vLogo1'] != "" && file_exists($Photo_Gallery_folder_vLogo1)) {
                    $Fare_Data[$i]['vLogo1'] = $vCarType_Arr[$i]['vLogo1'];
                } else {
                    $Fare_Data[$i]['vLogo1'] = "";
                }
                /* added for rental */
                $Fare_Data[$i]['eRental'] = 'No';
                if (ENABLE_RENTAL_OPTION == 'Yes') {
                    if ($vCarType_Arr[$i]['vRentalVehicleTypeName'] != '') {
                        $Fare_Data[$i]['vRentalVehicleTypeName'] = $vCarType_Arr[$i]['vRentalVehicleTypeName'];
                    } else {
                        $Fare_Data[$i]['vRentalVehicleTypeName'] = $vCarType_Arr[$i]['vVehicleTypeName'];
                    }
                    $checkrentalquery = "SELECT iRentalPackageId,iVehicleTypeId,vPackageName_" . $vLang . ",fPrice,fKiloMeter,fHour,fPricePerKM,fPricePerHour FROM  `rental_package` WHERE iVehicleTypeId = '" . $Fare_Data[$i]['iVehicleTypeId'] . "' ORDER BY `fPrice` ASC";
                    $rental_data = $obj->MySQLSelect($checkrentalquery);
                    if (count($rental_data) > 0) {
                        if ($userType == 'Driver') {
                            $rentquery = "SELECT `vRentalCarType` FROM `driver_vehicle` WHERE  iDriverVehicleId = '" . $iDriverVehicleId . "' AND FIND_IN_SET ('" . $Fare_Data[$i]['iVehicleTypeId'] . "', vRentalCarType)";
                            $rentalData_Arr = $obj->MySQLSelect($rentquery);
                            if (count($rentalData_Arr) > 0) {
                                $Fare_Data[$i]['eRental'] = 'Yes';
                                $Fare_Data[$i]['RentalSubtotal'] = $vSymbol . " " . number_format(round($rental_data[0]['fPrice'] * $priceRatio, 1), 2);
                            }
                        } else {
                            $Fare_Data[$i]['eRental'] = 'Yes';
                        }
                    }
                }
                /* End added for rental */
                $Fare_Data[$i]['iPersonSize'] = $vCarType_Arr[$i]['iPersonSize'];
                //$lastvalue = end($Fare_Single_Vehicle_Data);
                //$lastvalue1 = array_shift($lastvalue);
                
                $lastvalue1 = $Fare_Single_Vehicle_Data['org_fare_amount']; //ADDED BY sp BC two times currency symbol in total fare amount on 12-3-2020 so using $IS_RETURN_ARR_WITH_ORIG_AMT use org_fare_amount key
                $Fare_Data[$i]['SubTotal'] = $vSymbol . " " . $lastvalue1;
                $Fare_Data[$i]['VehicleFareDetail'] = $Fare_Single_Vehicle_Data;
                //array_push($Fare_Data, $Fare_Single_Vehicle_Data);
            }
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Fare_Data;
        //$returnArr['eFlatTrip'] = $eFlatTrip;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_VEHICLE_SELECTED";
    }

    setDataResponse($returnArr);
}
###########################################################################
#################################################Sign Up Kiosk Passenger #########################################################
/* For Generate Hail Trip */
if ($type == "StartHailTrip") {

    $driverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $DestAddress = isset($_REQUEST["DestAddress"]) ? $_REQUEST["DestAddress"] : '';
    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';

    /** As a part of socket cluster */
    $COUNT_PUBLISH_CHANNEL = isset($_REQUEST["COUNT_PUBLISH_CHANNEL"]) ? $_REQUEST["COUNT_PUBLISH_CHANNEL"] : '0';
    /** As a part of socket cluster */
    /* added for rental */
    $iRentalPackageId = isset($_REQUEST["iRentalPackageId"]) ? $_REQUEST["iRentalPackageId"] : '';

    $DriverMessage = "CabRequestAccepted";
    ### Checking For Pickup And DropOff Disallow ###
    $pickuplocationarr = array(
        $PickUpLatitude,
        $PickUpLongitude
    );
    $allowed_ans_pickup = checkAllowedAreaNew($pickuplocationarr, "No");
    if ($allowed_ans_pickup == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    $dropofflocationarr = array(
        $DestLatitude,
        $DestLongitude
    );
    $allowed_ans_dropoff = checkAllowedAreaNew($dropofflocationarr, "Yes");
    if ($allowed_ans_dropoff == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    ### Checking For Pickup And DropOff Disallow ###
    $sqldata = "SELECT iTripId FROM `trips` WHERE iActive='On Going Trip'  AND iDriverId='" . $driverId . "'";
    $TripData = $obj->MySQLSelect($sqldata);
    if (count($TripData) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_RESTART";
        setDataResponse($returnArr);
    }

    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $DataArr = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude, $address_data, "Yes", "No", "No", "", $DestLatitude, $DestLongitude);
    if ($DataArr['PickUpDisAllowed'] == "No" && $DataArr['DropOffDisAllowed'] == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICK_DROP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    if ($DataArr['PickUpDisAllowed'] == "Yes" && $DataArr['DropOffDisAllowed'] == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    if ($DataArr['PickUpDisAllowed'] == "No" && $DataArr['DropOffDisAllowed'] == "Yes") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    ## Check For PichUp/DropOff Location DisAllow Ends##
    if ($eTollSkipped == 'No' || $fTollPrice != "") {
        $fTollPrice_Original = $fTollPrice;
        $vTollPriceCurrencyCode = strtoupper($vTollPriceCurrencyCode);
        $default_currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        $sql = " SELECT round(($fTollPrice/(SELECT Ratio FROM currency where vName='" . $vTollPriceCurrencyCode . "'))*(SELECT Ratio FROM currency where vName='" . $default_currency . "' ) ,2)  as price FROM currency  limit 1";
        $result_toll = $obj->MySQLSelect($sql);
        $fTollPrice = $result_toll[0]['price'];
        if ($fTollPrice == 0) {
            $fTollPrice = get_currency($vTollPriceCurrencyCode, $default_currency, $fTollPrice_Original);
        }
    } else {
        $fTollPrice = "0";
        $vTollPriceCurrencyCode = "";
        $eTollSkipped = "No";
    }

    $sql = "SELECT * FROM `register_user` WHERE eHail = 'Yes' ORDER BY iUserId DESC";
    $hailpassenger = $obj->MySQLSelect($sql);

    if (count($hailpassenger) > 0) {
        $iUserId = $hailpassenger[0]['iUserId'];
        ## Update Trip Status ##
        $where = " iUserId='" . $iUserId . "'";
        $Data_passenger['iTripId'] = "0";
        $Data_passenger['vTripStatus'] = "NONE";
        $Data_passenger['vCallFromDriver'] = "";

        $sql = "UPDATE register_user set iTripId='0', vTripStatus = 'NONE', vCallFromDriver = '', eStatus = 'Active' WHERE iUserId='" . $iUserId . "'";
        $id = $obj->sql_query($sql);

        // $id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
        // echo "hello";exit;
        ## Update Trip Status ##
        // changed for rental
        $iTripID = GenerateHailTrip($iUserId, $driverId, $selectedCarTypeID, $PickUpLatitude, $PickUpLongitude, $PickUpAddress, $DestLatitude, $DestLongitude, $DestAddress, $fTollPrice, $vTollPriceCurrencyCode, $eTollSkipped, $iRentalPackageId);
    } else {
        $Data["vName"] = "Hail";
        $Data["vLastName"] = "Passenger";
        $Data["vEmail"] = "hailrider@demo.com";
        $Data["tDestinationLatitude"] = $DestLatitude;
        $Data["tDestinationLongitude"] = $DestLongitude;
        $Data["tDestinationAddress"] = $DestAddress;
        $Data["vLang"] = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $Data["eStatus"] = "Active";
        $Data["vCurrencyPassenger"] = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        $Data["tRegistrationDate"] = @date("Y-m-d H:i:s");
        $Data["eEmailVerified"] = "Yes";
        $Data["ePhoneVerified"] = "Yes";
        $Data['eDeviceType'] = "Ios";
        $Data['eType'] = "Ride";
        $Data['vCountry'] = $vCountryCode;
        $Data['tSessionId'] = session_id();
        $random = substr(md5(rand()), 0, 7);
        $Data['tDeviceSessionId'] = session_id() . time() . $random;
        $Data['eHail'] = "Yes";
        $id = $obj->MySQLQueryPerform("register_user", $Data, 'insert');
        if ($id > 0) {
            // changed for rental
            $iTripID = GenerateHailTrip($id, $driverId, $selectedCarTypeID, $PickUpLatitude, $PickUpLongitude, $PickUpAddress, $DestLatitude, $DestLongitude, $DestAddress, $fTollPrice, $vTollPriceCurrencyCode, $eTollSkipped, $iRentalPackageId);
            $iUserId = $id;
        }
    }
    //update insurance log
    // if($ENABLE_INSURANCE_TRIP_REPORT == "Yes"){
    $details_arr['iTripId'] = $iTripID;
    $details_arr['LatLngArr']['vLatitude'] = $PickUpLatitude;
    $details_arr['LatLngArr']['vLongitude'] = $PickUpLongitude;
    // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        update_driver_insurance_status($driverId, "Trip", $details_arr, "StartTrip");
    }
    // }
    //update insurance log
    #### Update Driver Request Status of Trip ####
    UpdateDriverRequest($driverId, $iUserId, $iTripID, "Accept");
    #### Update Driver Request Status of Trip ####
    $trip_status = "On Going Trip";
    $where = " iUserId = '$iUserId'";
    /* $Data_update_passenger['iTripId']=$iTripID;
      $Data_update_passenger['vTripStatus']=$trip_status; */
    $Data_update_passenger['iTripId'] = 0;
    $Data_update_passenger['vTripStatus'] = "NONE";
    $Data_update_passenger['vCallFromDriver'] = "";
    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);

    $where = " iDriverId = '$driverId'";
    $Data_update_driver['iTripId'] = $iTripID;
    $Data_update_driver['vTripStatus'] = $trip_status;
    $Data_update_driver['vAvailability'] = "Not Available";
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,vName,vLastName FROM `register_driver` WHERE iDriverId = '$driverId'";
    $Data_vehicle = $obj->MySQLSelect($sql);
    $message_arr = array();
    $message_arr['iDriverId'] = $driverId;
    $message_arr['Message'] = $DriverMessage;
    $message_arr['iTripId'] = strval($iTripID);
    $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
    $message_arr['iTripVerificationCode'] = get_value('trips', 'iVerificationCode', 'iTripId', $iTripID, '', 'true');
    $message_arr['eSystem'] = "";
    $message = json_encode($message_arr);
    if ($iTripID > 0) {
        /** As a part of socket cluster */
        if ($PUBNUB_DISABLED == "Yes") {
            $DRIVER_CURRENT_TIME = isset($_REQUEST["DRIVER_CURRENT_TIME"]) ? $_REQUEST["DRIVER_CURRENT_TIME"] : '';
            for ($i = 0; $i < $COUNT_PUBLISH_CHANNEL; $i++) {
                $PUBLISH_CHANNEL_tmp = isset($_REQUEST["PUBLISH_CHANNEL_" . $i]) ? $_REQUEST["PUBLISH_CHANNEL_" . $i] : '';
                if ($PUBLISH_CHANNEL_tmp != "") {
                    $pubMsgArr['iDriverId'] = $driverId;
                    $pubMsgArr['MessageType'] = "DriverStatusLocation";
                    $pubMsgArr['Latitude'] = $PickUpLatitude;
                    $pubMsgArr['Longitude'] = $PickUpLongitude;
                    $pubMsgArr['Time'] = $DRIVER_CURRENT_TIME;
                    $pubMsgArr['IsDriverOnline'] = "No";
                    $pubMsgArr['isForceLoad'] = "No";
                    $message_pub_sub = json_encode($pubMsgArr, JSON_UNESCAPED_UNICODE);
                    //  publishEventMessage($PUBLISH_CHANNEL_tmp, $message_pub_sub);
                }
            }
        }
        /** As a part of socket cluster */
        $returnArr['Action'] = "1";
        $data['iTripId'] = $iTripID;
        $data['tEndLat'] = $DestLatitude;
        $data['tEndLong'] = $DestLongitude;
        $data['tDaddress'] = $DestAddress;
        $data['PAppVersion'] = get_value('register_user', 'iAppVersion', 'iUserId', $iUserId, '', 'true');
        $data['eFareType'] = get_value('trips', 'eFareType', 'iTripId', $iTripID, '', 'true');
        $returnArr['APP_TYPE'] = $APP_TYPE;
        $returnArr['message'] = $data;
        setDataResponse($returnArr);
    } else {
        $data['Action'] = "0";
        $data['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($data);
    }
}
/* For Generate Hail Trip */
###########################################################################
##############################Check Vehicle eligble for hail ride #################################################
if ($type == "CheckVehicleEligibleForHail") {
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    if ($COMMISION_DEDUCT_ENABLE == 'Yes' && ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Cash-Card")) {
        $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
        $driverDetail = get_value('register_driver AS rd LEFT JOIN currency AS c ON c.vName=rd.vCurrencyDriver', 'rd.vCurrencyDriver,c.Ratio,c.vSymbol', 'rd.iDriverId', $iDriverId);
        $ratio = $driverDetail[0]['Ratio'];
        $currencySymbol = $driverDetail[0]['vSymbol'];

        $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
        if ($vLang == "" || $vLang == NULL) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);

        if ($WALLET_MIN_BALANCE > $user_available_balance) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "REQUIRED_MINIMUM_BALNCE";
            if ($APP_TYPE == "UberX") {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio), $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE_UBERX']);
            } else {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio), $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE_HAIL']);
            }
            setDataResponse($returnArr);
        }
    }

    $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    if ($iDriverVehicleId > 0) {
        $sql = "SELECT vCarType FROM driver_vehicle WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
        $vCarType = $obj->MySQLSelect($sql);
        $vehicleIds = explode(",", $vCarType[0]['vCarType']);
        $vehicleListIds = implode("','", $vehicleIds);
        $sql1 = "SELECT count(iVehicleTypeId) as total_ridevehicle FROM vehicle_type WHERE iVehicleTypeId IN ('" . $vehicleListIds . "') AND eType = 'Ride'";
        $Vehiclelist = $obj->MySQLSelect($sql1);
        if ($Vehiclelist[0]['total_ridevehicle'] > 0) {
            $returnArr['Action'] = "1";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_VEHICLE_ELIGIBLE_FOR_HAIL_RIDE_MSG";
        }
    }


    setDataResponse($returnArr);
}
##############################Check Vehicle eligble for hail ride Ends#################################################################
############################ Ride Features #######################################
##################################################################################################
if ($type == "displayWayBill") {
    global $DEFAULT_DISTANCE_UNIT;
    $driverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $iTripDeliveryLocationId = isset($_REQUEST['iTripDeliveryLocationId']) ? clean($_REQUEST['iTripDeliveryLocationId']) : '';

    $driver_detail = get_value('register_driver', 'vName,vLastName,vCurrencyDriver,vLang', 'iDriverId', $driverId);

    //added by SP for fly vehicles on 31-08-2019 start bc not generated waybill for the fly vehicles
    if (checkFlyStationsModule()) {
        $fly_sql = ' AND iFromStationId = 0 AND iToStationId = 0';
    } else {
        $fly_sql = '';
    }

    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $sql = "SELECT * from trips WHERE iDriverId = '" . $driverId . "' AND eType != 'UberX' " . $fly_sql . " ORDER BY iTripId DESC LIMIT 0,1";
        //$sql = "SELECT * from trips WHERE iDriverId = '".$driverId."' AND eFareType NOT IN('Fixed', 'Hourly') ORDER BY iTripId DESC LIMIT 0,1";
    } else {
        $sql = "SELECT * from trips WHERE iDriverId = '" . $driverId . "' " . $fly_sql . " ORDER BY iTripId DESC LIMIT 0,1";
    }
    //added by SP for fly vehicles on 31-08-2019 end

    $tripData = $obj->MySQLSelect($sql);

    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyDriver);

    if (count($tripData) > 0) {

        $eType = $tripData[0]['eType'];
        $vLang = $driver_detail[0]['vLang'];
        if ($vLang == "" || $vLang == NULL) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        /* -------------------------For multi delivery--------------------------------- */
        if ($eType == "Multi-Delivery") {
            $ssql = "";
            if ($iTripDeliveryLocationId != "") {
                $ssql = " and iTripDeliveryLocationId = '$iTripDeliveryLocationId'";
            }

            $iTripId = $tripData[0]['iTripId'];
            $sql = "SELECT tdl.* FROM trips_delivery_locations as tdl WHERE tdl.`iTripId`='" . $iTripId . "' $ssql order by tdl.iTripDeliveryLocationId ASC";
            $Data_tripLocations = $obj->MySQLSelect($sql);

            $sql = "select * from trip_delivery_fields where iTripId ='$iTripId' $ssql order by iDeliveryFieldId";
            $db_trip_fields = $obj->MySQLSelect($sql);

            $iUserId = get_value('trips', 'iUserId', 'iTripId', $iTripId, '', 'true');
            $vPhoneCode = get_value('register_user', 'vPhoneCode', 'iUserId', $iUserId, '', 'true');

            if (count($db_trip_fields) > 0) {
                for ($j = 0; $j < count($Data_tripLocations); $j++) {
                    $k = 0;
                    for ($i = 0; $i < count($db_trip_fields); $i++) {
                        if ($Data_tripLocations[$j]['iTripDeliveryLocationId'] == $db_trip_fields[$i]['iTripDeliveryLocationId']) {
                            if ($db_trip_fields[$i]['vValue'] != "") {
                                $sql = "select vFieldName_$vLang as vFieldName,eInputType,eRequired from delivery_fields where iDeliveryFieldId='" . $db_trip_fields[$i]['iDeliveryFieldId'] . "'";
                                $db_fields_data = $obj->MySQLSelect($sql);

                                $Data_Field[$j][$k]['vFieldName'] = $db_fields_data[0]['vFieldName'];
                                $Data_Field[$j][$k]['vValue'] = $db_trip_fields[$i]['vValue'];
                                if ($db_trip_fields[$i]['iDeliveryFieldId'] == "3") {
                                    $Data_Field[$j][$k]['vValue'] = "+" .$db_trip_fields[$i]['vValue'];
                                    /*if ($eType != "Multi-Delivery") {
                                        $Data_Field[$j][$k]['vValue'] = "+" . $vPhoneCode . $db_trip_fields[$i]['vValue'];
                                    }*/
                                    $mobileLength1 = strlen($db_trip_fields[$i]['vValue']) - 2;
                                    $maskMobileNo1 = substr($db_trip_fields[$i]['vValue'], 0, 2);
                                    $Data_Field[$j][$k]['vMaskValue'] = "+" . $maskMobileNo1 . str_repeat("X", $mobileLength1);
                                    //echo "<pre>";print_r($Data_Field);exit;
                                }

                                $Data_Field[$j][$k]['iDeliveryFieldId'] = $db_trip_fields[$i]['iDeliveryFieldId'];
                                $Data_Field[$j][$k]['eRequired'] = $db_fields_data[0]['eRequired'];

                                if ($db_trip_fields[$i]['iDeliveryFieldId'] == "2") {
                                    if ($Data_tripLocations[0]['ePaymentBy'] == "Receiver" && $Data_tripLocations[$j]['ePaymentByReceiver'] == "Yes") {
                                        $PaymentPerson = $db_trip_fields[$i]['vValue'];
                                        $ReceNo = $j + 1;
                                    }
                                    $vReceiverName = $db_trip_fields[$i]['vValue'];
                                }

                                //added by SP for giving currency symbol infront of price like material price on 15-10-2019
                                if ($db_trip_fields[$i]['iDeliveryFieldId'] == "7") {
                                    $fmatprice = round(($Data_Field[$j][$k]['vValue'] * $UserCurrencyData[0]['Ratio']), 2);
                                    $Data_Field[$j][$k]['vValue'] = $UserCurrencyData[0]['vSymbol'] . " " . formatnum($fmatprice);
                                }

                                if ($db_fields_data[0]['eInputType'] == "Select") {
                                    $sql = "SELECT vName_$vLang as vName FROM `package_type` where iDeliveryFieldId='" . $db_trip_fields[$i]['iDeliveryFieldId'] . "' and iPackageTypeId='" . $db_trip_fields[$i]['vValue'] . "'";
                                    $db_data = $obj->MySQLSelect($sql);

                                    $Data_Field[$j][$k]['vValue'] = $db_data[0]['vName'];
                                }
                                $k++;
                            }
                        }

                        if ($Data_tripLocations[0]['ePaymentBy'] == "Receiver" && $Data_tripLocations[$j]['ePaymentByReceiver'] == "Yes") {
                            if ($db_trip_fields[$i]['iDeliveryFieldId'] == "2") {
                                $PaymentPerson = $db_trip_fields[$i]['vValue'];
                                $ReceNo = $j + 1;
                            }
                        }
                    }
                    $Data_Field[$j][$k]['vFieldName'] = "Address";
                    $Data_Field[$j][$k]['tSaddress'] = $Data_tripLocations[$j]['tSaddress'];
                    $Data_Field[$j][$k]['tStartLat'] = $Data_tripLocations[$j]['tStartLat'];
                    $Data_Field[$j][$k]['tStartLong'] = $Data_tripLocations[$j]['tStartLong'];
                    $Data_Field[$j][$k]['tDaddress'] = $Data_tripLocations[$j]['tDaddress'];
                    $Data_Field[$j][$k]['tEndLat'] = $Data_tripLocations[$j]['tEndLat'];
                    $Data_Field[$j][$k]['tEndLong'] = $Data_tripLocations[$j]['tEndLong'];
                    $Data_Field[$j][$k]['ePaymentByReceiver'] = $Data_tripLocations[$j]['ePaymentByReceiver'];
                    $Data_Field[$j][$k]['iTripDeliveryLocationId'] = $Data_tripLocations[$j]['iTripDeliveryLocationId'];
                    $Data_Field[$j][$k]['iActive'] = $Data_tripLocations[$j]['iActive'];
                    $Data_Field[$j][$k]['tStartTime'] = $Data_tripLocations[$j]['tStartTime'];
                    $Data_Field[$j][$k]['tEndTime'] = $Data_tripLocations[$j]['tEndTime'];
                    $Data_Field[$j][$k]['vReceiverName'] = $vReceiverName;
                    $Data_Field[$j][$k]['eCancelled'] = $Data_tripLocations[$j]['eCancelled'];
                    $Data_Field[$j][$k]['vDeliveryConfirmCode'] = $Data_tripLocations[$j]['vDeliveryConfirmCode'];

                    $Receipent_Signature = "";
                    // if($Data_tripLocations[$j]['ePaymentByReceiver'] == 'Yes') {
                    // $returnArr['ePaymentBySender'] = 'No';
                    // $returnArr['PaymentPerson'] = $LBL_RECEIPENT_TXT.$ReceNo." (".$PaymentPerson." )";
                    // }
                    if ((file_exists($tconfig["tsite_upload_trip_signature_images_path"] . $tripData[0]['vSignImage'])) && $tripData[0]['vSignImage'] != "") {
                        $Receipent_Signature = $tconfig["tsite_upload_trip_signature_images"] . $tripData[0]['vSignImage'];
                    }
                    $Data_Field[$j][$k]['Receipent_Signature'] = $Receipent_Signature;
                }
            }

            $tripArr['Deliveries'] = $Data_Field;
            // echo "<pre>";print_r($Data_Field);exit;
        }
        /* -------------------------For multi delivery End--------------------------------- */

        $passenger_detail = get_value('register_user', 'vName,vLastName,eHail', 'iUserId', $tripData[0]['iUserId']);
        if ($passenger_detail[0]['eHail'] == "Yes") {
            $passengername = "--";
        } else {
            $passengername = $passenger_detail[0]['vName'] . " " . $passenger_detail[0]['vLastName'];
        }
        ## get fare details ##
        $vLang = $driver_detail[0]['vLang'];
        if ($vLang == "" || $vLang == NULL) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $vehicleTypes = get_value('vehicle_type', '*', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId']);
        /* $priceRatio=get_value('currency', 'Ratio', 'vName', $driver_detail[0]['vCurrencyDriver'],'','true');
          $vCurrencySymbol=get_value('currency', 'vSymbol', 'vName', $driver_detail[0]['vCurrencyDriver'],'','true'); */
        $sql_request = "SELECT * FROM currency WHERE vName='" . $driver_detail[0]['vCurrencyDriver'] . "'";
        $drivercurrencydata = $obj->MySQLSelect($sql_request);
        $priceRatio = $drivercurrencydata[0]['Ratio'];
        $vCurrencySymbol = $drivercurrencydata[0]['vSymbol'];
        $eFareType = $vehicleTypes[0]['eFareType'];
        $eFlatTrip = $tripData[0]['eFlatTrip'];
        $fTripGenerateFare = $tripData[0]['fTripGenerateFare'];
        $fFlatTripPrice = $tripData[0]['fFlatTripPrice'];
        $fPricePerKM = round($vehicleTypes[0]['fPricePerKM'] * $priceRatio, 2);
        $fPricePerMin = round($vehicleTypes[0]['fPricePerMin'] * $priceRatio, 2);
        $iBaseFare = round($vehicleTypes[0]['iBaseFare'] * $priceRatio, 2);
        $fCommision = round($vehicleTypes[0]['fCommision'] * $priceRatio, 2);
        $iMinFare = round($vehicleTypes[0]['iMinFare'] * $priceRatio, 2);
        $fFixedFare = round($vehicleTypes[0]['fFixedFare'] * $priceRatio, 2);
        $fPricePerHour = round($vehicleTypes[0]['fPricePerHour'] * $priceRatio, 2);
        $fTripGenerateFare = round($fTripGenerateFare * $priceRatio, 2);
        $fFlatTripPrice = round($fFlatTripPrice * $priceRatio, 2);
        $iRentalPackageId = $tripData[0]['iRentalPackageId'];

        if ($iRentalPackageId > 0) {
            $PackageData = getRentalData($iRentalPackageId);
            $fPrice = $vCurrencySymbol . " " . round($PackageData[0]['fPrice'] * $priceRatio, 2);
            $pkgName = $PackageData[0]['vPackageName_' . $vLang];
            $Rate = $pkgName . " @ " . $fPrice;
        } else {
            if ($eFareType == "Regular") {
                $eUnit = getMemberCountryUnit($driverId, "Driver");
                if ($DEFAULT_DISTANCE_UNIT == "Miles") {
                    if ($eUnit == 'Miles') {
                        $fPricePerKMNew = $fPricePerKM;
                        $LBL_MILE_DISTANCE_TXT = ($fPricePerKMNew > 1) ? $languageLabelsArr['LBL_MILE_DISTANCE_TXT'] : $languageLabelsArr['LBL_ONE_MILE_TXT'];
                        $DisplayDistanceTxt = $LBL_MILE_DISTANCE_TXT;
                    } else {
                        $fPricePerKMNew = $fPricePerKM * 1.60934;
                        $LBL_KM_DISTANCE_TXT = ($fPricePerKMNew > 1) ? $languageLabelsArr['LBL_DISPLAY_KMS'] : $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
                        $DisplayDistanceTxt = $LBL_KM_DISTANCE_TXT;
                    }
                }

                if ($DEFAULT_DISTANCE_UNIT == "KMs") {
                    if ($eUnit == 'KMs') {
                        $fPricePerKMNew = $fPricePerKM;
                        $LBL_KM_DISTANCE_TXT = ($fPricePerKMNew > 1) ? $languageLabelsArr['LBL_DISPLAY_KMS'] : $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
                        $DisplayDistanceTxt = $LBL_KM_DISTANCE_TXT;
                    } else {
                        $tripDistanceDisplay = $fPricePerKM * 0.621371;
                        $fPricePerKMNew = round($tripDistanceDisplay, 2);
                        $LBL_MILE_DISTANCE_TXT = ($fPricePerKMNew > 1) ? $languageLabelsArr['LBL_MILE_DISTANCE_TXT'] : $languageLabelsArr['LBL_ONE_MILE_TXT'];
                        $DisplayDistanceTxt = $LBL_MILE_DISTANCE_TXT;
                    }
                }

                $Rate = $vCurrencySymbol . " " . $iBaseFare . " " . $languageLabelsArr['LBL_BASE_FARE_SMALL_TXT'] . " + " . $vCurrencySymbol . " " . $fPricePerMin . " " . $languageLabelsArr['LBL_PRICE_PER_MINUTE_SMALL_TXT'] . " + " . $vCurrencySymbol . " " . $fPricePerKMNew . " " . $DisplayDistanceTxt;
            }
            if ($eFareType == "Fixed") {
                $Rate = $vCurrencySymbol . " " . $fFixedFare . " " . $languageLabelsArr['LBL_FIXED_FARE_TXT_ADMIN'];
            }
            if ($eFareType == "Hourly") {
                $Rate = $vCurrencySymbol . " " . $fPricePerHour . " " . $languageLabelsArr['LBL_PER_HOUR_SMALL_TXT'];
            }
            if ($eFlatTrip == "Yes") {
                if ($fTripGenerateFare > 0) {
                    //$Rate = $vCurrencySymbol." ".$fTripGenerateFare;
                    $Rate = $vCurrencySymbol . " " . $fFlatTripPrice;
                } else {
                    $Rate = $vCurrencySymbol . " " . $fFlatTripPrice;
                }
            }
        }
        ## get fare details ##
        $tripArr['DriverName'] = $driver_detail[0]['vName'] . " " . $driver_detail[0]['vLastName'];
        $tripArr['vRideNo'] = $tripData[0]['vRideNo'];
        $tripArr['tTripRequestDate'] = $tripData[0]['tTripRequestDate'];
        $tripArr['ProjectName'] = $SITE_NAME;
        $tripArr['tSaddress'] = $tripData[0]['tSaddress'];
        $tripArr['tDaddress'] = $tripData[0]['tDaddress'];
        $tripArr['PassengerName'] = $passengername;
        $tripArr['Licence_Plate'] = get_value('driver_vehicle', 'vLicencePlate', 'iDriverVehicleId', $tripData[0]['iDriverVehicleId'], '', 'true');
        $tripArr['PassengerCapacity'] = get_value('vehicle_type', 'iPersonSize', 'iVehicleTypeId', $tripData[0]['iVehicleTypeId'], '', 'true');

        // packagename changes
        $sql_request = "SELECT vName_" . $vLang . " as vName FROM package_type WHERE iPackageTypeId='" . $tripData[0]["iPackageTypeId"] . "'";
        $pkgdata = $obj->MySQLSelect($sql_request);

        $tripArr['PackageName'] = $pkgdata[0]['vName'];
        $tripArr['tPackageDetails'] = $tripData[0]['tPackageDetails'];
        $tripArr['vReceiverName'] = $tripData[0]['vReceiverName'];
        $tripArr['Rate'] = $Rate;
        $tripArr['eType'] = $tripData[0]['eType'];

        $returnArr['Action'] = "1";
        $returnArr['message'] = $tripArr;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    setDataResponse($returnArr);
}
############################################################################################
?>