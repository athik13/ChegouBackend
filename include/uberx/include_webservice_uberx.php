<?php

############################################ Functions added ############################################

/* ini_set("display_errors", TRUE);
  error_reporting(E_ALL); */

function isProviderEligibleForScheduleJob($iDriverId) {
    global $SERVICE_PROVIDER_FLOW, $obj;

    if ($iDriverId == "") {
        return false;
    }

    $driverAvailabilityArr = $obj->MySQLSelect("SELECT iDriverTimingId FROM driver_manage_timing WHERE iDriverId = '" . $iDriverId . "' AND eStatus = 'Active' AND vAvailableTimes != ''");
    // print_r($driverAvailabilityArr);exit;
    if (count($driverAvailabilityArr) > 0) {
        return true;
    }

    return false;
}

function isProviderOnline($providerDataArr) {
    global $SERVICE_PROVIDER_FLOW, $obj;

    if ($SERVICE_PROVIDER_FLOW != "Provider") {
        return true;
    }

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    $providerAvailability = $providerDataArr['vAvailability'];

    if ($providerAvailability == "Available") {
        $vAvailability = $providerDataArr['vAvailability'];
        $vTripStatus = $providerDataArr['vTripStatus'];
        $tLocationUpdateDate = $providerDataArr['tLocationUpdateDate'];
        //echo $tLocationUpdateDate . " > " . $startDate . "<br>";
        if ($vAvailability == "Available" && $vTripStatus != "Active" && $tLocationUpdateDate > $str_date) {
            return true;
        }
    }

    return false;
}

function isProviderEligible($providerDataArr) {
    global $SERVICE_PROVIDER_FLOW, $obj;

    if ($SERVICE_PROVIDER_FLOW != "Provider") {
        return true;
    }

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    $providerAvailability = $providerDataArr['vAvailability'];

    if ($providerAvailability == "Available") {
        $vAvailability = $providerDataArr['vAvailability'];
        $vTripStatus = $providerDataArr['vTripStatus'];
        $tLocationUpdateDate = $providerDataArr['tLocationUpdateDate'];
        //echo $tLocationUpdateDate . " > " . $startDate . "<br>";
        if ($vAvailability == "Available" && $vTripStatus != "Active" && $tLocationUpdateDate > $str_date) {
            return true;
        }
    }

    /* $driverAvailabilityArr = $obj->MySQLSelect("SELECT count(iDriverTimingId) as TotalData FROM driver_manage_timing WHERE iDriverId = '".$providerDataArr['iDriverId']."' AND eStatus = 'Active'");
      //print_r($providerDataArr['iDriverId']);exit;
      if(count($driverAvailabilityArr) > 0 && $driverAvailabilityArr[0]['TotalData'] > 0){
      return true;
      } */

    return isProviderEligibleForScheduleJob($providerDataArr['iDriverId']);
}

function getOrderDetailsAsPerId($OrderDetails, $iVehicleTypeId) {
    for ($v = 0; $v < count($OrderDetails); $v++) {
        $iVehicleTypeId_tmp = $OrderDetails[$v]['iVehicleTypeId'];
        if ($iVehicleTypeId_tmp == $iVehicleTypeId) {
            return $OrderDetails[$v];
        }
    }
    return array();
}

//Added By HJ On 31-01-2019 For Get Vehicle Type Fare Details Start
function getVehicleTypeFareDetails($OrderDetails = array(), $iMemberId = "") {
    global $obj, $_REQUEST, $DEFAULT_DISTANCE_UNIT;

    if (empty($OrderDetails)) {
        $OrderDetails = isset($_REQUEST["OrderDetails"]) ? $_REQUEST["OrderDetails"] : '';
    }

    if (empty($iMemberId)) {
        $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    }

    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vCouponCode = isset($_REQUEST["vCouponCode"]) ? $_REQUEST["vCouponCode"] : '';

    if ($iMemberId == "") {
        $iMemberId = isset($_REQUEST["userId"]) ? $_REQUEST["userId"] : '';
    }

    if ($iMemberId == "") {
        $iMemberId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    }

    if ($iDriverId == "") {
        $iDriverId = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    }

    if ($iDriverId == "") {
        $iDriverId = isset($_REQUEST["DriverId"]) ? $_REQUEST["DriverId"] : '';
    }
    if ($iDriverId == "") {
        $iDriverId = isset($_REQUEST["SelectedDriverId"]) ? $_REQUEST["SelectedDriverId"] : '';
    }
    if ($iDriverId == "") {
        $iDriverId = isset($_REQUEST["driverIds"]) ? $_REQUEST["driverIds"] : '';
        if (!empty($iDriverId)) {
            $iDriverId = explode(",", $iDriverId);
            if (!empty($iDriverId) && count($iDriverId) > 0) {
                $iDriverId = $iDriverId[0];
            }
        }
    }

    if ($vCouponCode == "") {
        $vCouponCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    }
    // print_R(json_decode($OrderDetails,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)); exit;
    //$OrderDetails = json_decode(stripcslashes($OrderDetails), true);
    $OrderDetails = json_decode(preg_replace('/[[:cntrl:]]/', '\r\n', $OrderDetails), true);

    if (empty($OrderDetails)) {
        return array();
    }

    /* Tax Calculation */
    $TaxArr = getMemberCountryTax($iMemberId, "Passenger");
    $fTax1 = $TaxArr['fTax1'];
    $fTax2 = $TaxArr['fTax2'];
    /* Tax Calculation */

    $tableName = "register_user";
    $fieldName = "iUserId";

    /** To Get User Language Code And Currency * */
    $userData = $obj->MySQLSelect("SELECT ru.vCurrencyPassenger, ru.vLang, cu.vSymbol, cu.Ratio, co.eUnit FROM register_user as ru, currency as cu, country as co WHERE ru.iUserId='" . $iMemberId . "' AND cu.vName = ru.vCurrencyPassenger AND co.vCountryCode = ru.vCountry");
    if (empty($userData)) {
        return array();
    }
    $vCurrencyPassenger = "";
    $vCurrencyRatio = "";
    $vCurrencySymbol = "";
    $eUnit = "KMs";
    if (count($userData) > 0) {
        $lang = $userData[0]['vLang'];
        $vCurrencyPassenger = $userData[0]['vCurrencyPassenger'];
        $vCurrencyRatio = $userData[0]['Ratio'];
        $vCurrencySymbol = $userData[0]['vSymbol'];
        $eUnit = $userData[0]['eUnit'];
    }
    if ($lang == "") {
        $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    /** To Get User Language Code And Currency * */
    $languageLabelsArr = getLanguageLabelsArr($lang, "1");

    $iVehicleTypeId = $iVehicleCategoryIds = "";
    for ($t = 0; $t < count($OrderDetails); $t++) {
        if ($OrderDetails[$t]['fVehicleTypeQty'] < 1) {
            $OrderDetails[$t]['fVehicleTypeQty'] = 1;
        }
        $typeId = $OrderDetails[$t]['iVehicleTypeId'];
        $iVehicleTypeId .= "," . $typeId;
    }
    $iVehicleTypeId = trim($iVehicleTypeId, ",");
    // echo "IDs::".$iVehicleTypeId;exit;
    // $getVehicleTypeData = $obj->MySQLSelect("SELECT vt.iVehicleTypeId,vt.tTypeDesc,vt.iVehicleCategoryId,vt.vVehicleType_" . $lang . " AS vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.fMinHour, vt.iMaxQty, vt.iVehicleTypeId, vt.fTimeSlot,vt.fTimeSlotPrice, (SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentPriceType, (SELECT vcs.vCategory_".$lang." FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentCategoryName, IF((SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId)='Provider', (SELECT spa.fAmount from service_pro_amount as spa WHERE spa.iVehicleTypeId=vt.iVehicleTypeId AND spa.iDriverVehicleId =(SELECT dv.iDriverVehicleId FROM driver_vehicle as dv WHERE dv.iDriverId='" . $iDriverId . "' AND dv.eType='UberX')), NULL) as ProviderPrice, IF(vt.iLocationid != -1, (SELECT co.eUnit FROM country as co WHERE co.iCountryId = )) as LocationUnit FROM vehicle_type vt, vehicle_category as vc WHERE vt.iVehicleCategoryId >0 AND vt.iVehicleTypeId IN ($iVehicleTypeId) AND vt.eStatus='Active' AND vc.iVehicleCategoryId = vt.iVehicleCategoryId");

    if (!empty($vCouponCode)) {
        $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $vCouponCode . "' AND eStatus='Active'");
        $discountValue = 0;
        if (count($getCouponCode) > 0) {
            $discountValue = $getCouponCode[0]['fDiscount'];
            $discountValueType = $getCouponCode[0]['eType'];
            if ($discountValueType != "percentage") {
                $discountValue = $discountValue * $vCurrencyRatio;
            }
        }
    }

    $getVehicleTypeData = $obj->MySQLSelect("SELECT vt.iVehicleTypeId,vt.tTypeDesc,vt.iVehicleCategoryId,vt.vVehicleType_" . $lang . " AS vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.fMinHour, vt.iMaxQty, vt.iVehicleTypeId, vt.fTimeSlot,vt.fTimeSlotPrice, (SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentPriceType, (SELECT vcs.vCategory_" . $lang . " FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentCategoryName, (SELECT fWaitingFees FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentWaitingFees,(SELECT iWaitingFeeTimeLimit FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentWaitingTimeLimit,(SELECT fCommision FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentCommisionPer,(SELECT eMaterialCommision FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentMaterialCommisionEnable,(SELECT fCancellationFare FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentCancellationFare,(SELECT iCancellationTimeLimit FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentCancellationTimeLimit, vc.iParentId as ParentVehicleCategoryId, IF((SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId)='Provider', (SELECT spa.fAmount from service_pro_amount as spa WHERE spa.iVehicleTypeId=vt.iVehicleTypeId AND spa.iDriverVehicleId =(SELECT dv.iDriverVehicleId FROM driver_vehicle as dv WHERE dv.iDriverId='" . $iDriverId . "' AND dv.eType='UberX')), NULL) as ProviderPrice, IF(vt.iLocationid != -1, (SELECT co.eUnit FROM country as co, location_master as lm WHERE co.iCountryId = lm.iCountryId AND lm.iLocationid = vt.iLocationid), '" . $DEFAULT_DISTANCE_UNIT . "') as LocationUnit FROM vehicle_type vt, vehicle_category as vc WHERE vt.iVehicleCategoryId >0 AND vt.iVehicleTypeId IN ($iVehicleTypeId) AND vt.eStatus='Active' AND vc.iVehicleCategoryId = vt.iVehicleCategoryId");

    $vehicleTypeArr = $vehiclePriceTypeArr = $vehiclePriceTypeSaveArr = $vehicleCatNameArr = $vehiclePriceTypeArrItems = $vehiclePriceTypeArrCubex = array();
    //added by SP for new design $vehiclePriceTypeArrItems,$vehiclePriceTypeArrCubex here and in type getVehicleTypeFareDetails also on 10-9-2019
    $totalFareOfServices = 0;
    $totalCommissionOfServices = 0;
    $eFareTypeServices = "";
    $currentPriceTypeArrCounti = $currentPriceTypeArrCountCubex = 0;
    for ($v = 0; $v < count($getVehicleTypeData); $v++) {
        $tTypeDescription = "";
        $tTypeDesc = (array) json_decode($getVehicleTypeData[$v]['tTypeDesc']);
        if (isset($tTypeDesc['tTypeDesc_' . $lang]) && $tTypeDesc['tTypeDesc_' . $lang] != "") {
            $tTypeDescription = $tTypeDesc['tTypeDesc_' . $lang];
        }
        $getVehicleTypeData[$v]['tTypeDesc'] = $tTypeDescription;
        $vehicleTypeArr[$getVehicleTypeData[$v]['iVehicleTypeId']] = $getVehicleTypeData[$v];
        $iVehicleCategoryIds .= "," . $getVehicleTypeData[$v]['iVehicleCategoryId'];

        $OrderDetails_tmp = getOrderDetailsAsPerId($OrderDetails, $getVehicleTypeData[$v]['iVehicleTypeId']);
        if ($getVehicleTypeData[$v]['ProviderPrice'] != NULL) {
            $getVehicleTypeData[$v]['fFixedFare'] = $getVehicleTypeData[$v]['ProviderPrice'];
            $getVehicleTypeData[$v]['fPricePerHour'] = $getVehicleTypeData[$v]['ProviderPrice'];
        }

        unset($getVehicleTypeData[$v]['ProviderPrice']);
        // unset($getVehicleTypeData[$v]['ParentPriceType']);

        $currentPriceTypeArrCount = count($vehiclePriceTypeArr);

        $returnArr['ParentCategoryName'] = $getVehicleTypeData[$v]['ParentCategoryName'];

        $eFareTypeServices = $getVehicleTypeData[$v]['eFareType'];

        $vehiclePriceTypeSaveArr[$currentPriceTypeArrCount]['id'] = $getVehicleTypeData[$v]['iVehicleTypeId'];
        $vehiclePriceTypeSaveArr[$currentPriceTypeArrCount]['qty'] = $OrderDetails_tmp['fVehicleTypeQty'];
        $vehiclePriceTypeSaveArr[$currentPriceTypeArrCount]['eAllowQty'] = $getVehicleTypeData[$v]['eAllowQty'];
        $vehiclePriceTypeSaveArr[$currentPriceTypeArrCount]['MinimumHour'] = $getVehicleTypeData[$v]['eFareType'] == "Hourly" ? $getVehicleTypeData[$v]['fMinHour'] : 0;
        $vehiclePriceTypeSaveArr[$currentPriceTypeArrCount]['amount'] = $getVehicleTypeData[$v][$getVehicleTypeData[$v]['eFareType'] == "Fixed" ? 'fFixedFare' : ($getVehicleTypeData[$v]['eFareType'] == "Hourly" ? 'fPricePerHour' : 'iBaseFare' )];

        $vehiclePriceTypeSaveArr[$currentPriceTypeArrCount]['fCommision'] = round(((($vehiclePriceTypeSaveArr[$currentPriceTypeArrCount]['amount'] * $OrderDetails_tmp['fVehicleTypeQty']) * $getVehicleTypeData[$v]['fCommision']) / 100), 2);

        $returnArr['tripFareDetailsSaveArr']['ParentWaitingFees'] = $getVehicleTypeData[$v]['ParentWaitingFees'];
        $returnArr['tripFareDetailsSaveArr']['ParentWaitingTimeLimit'] = $getVehicleTypeData[$v]['ParentWaitingTimeLimit'];
        $returnArr['tripFareDetailsSaveArr']['ParentCommision'] = $getVehicleTypeData[$v]['ParentCommisionPer'];
        $returnArr['tripFareDetailsSaveArr']['ParentMaterialCommisionEnable'] = $getVehicleTypeData[$v]['ParentMaterialCommisionEnable'];
        $returnArr['tripFareDetailsSaveArr']['ParentCancellationFare'] = $getVehicleTypeData[$v]['ParentCancellationFare'];
        $returnArr['tripFareDetailsSaveArr']['ParentCancellationTimeLimit'] = $getVehicleTypeData[$v]['ParentCancellationTimeLimit'];
        $returnArr['tripFareDetailsSaveArr']['eFareTypeServices'] = $eFareTypeServices;
        $returnArr['tripFareDetailsSaveArr']['ParentPriceType'] = $getVehicleTypeData[$v]['ParentPriceType'];
        $returnArr['tripFareDetailsSaveArr']['ParentVehicleCategoryId'] = $getVehicleTypeData[$v]['ParentVehicleCategoryId'];

        $totalCommissionOfServices += $vehiclePriceTypeSaveArr[$currentPriceTypeArrCount]['fCommision'];

        if ($getVehicleTypeData[$v]['eFareType'] != "Fixed" && count($vehiclePriceTypeArr) == 0) {
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $getVehicleTypeData[$v]['vVehicleType'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['iVehicleTypeId'] = $getVehicleTypeData[$v]['iVehicleTypeId'];
            $currentPriceTypeArrCount = count($vehiclePriceTypeArr);
        }

        if ($getVehicleTypeData[$v]['eFareType'] == "Fixed") {
            $fFixedFare = round($getVehicleTypeData[$v]['fFixedFare'] * $vCurrencyRatio, 2);
            $fFixedFare = $fFixedFare * $OrderDetails_tmp['fVehicleTypeQty'];

            $totalFareOfServices = $totalFareOfServices + $fFixedFare;
            $totalFareOfServices_orig = $totalFareOfServices;
            $fFixedFare_formmated = $vCurrencySymbol . formatNum($fFixedFare);

            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $getVehicleTypeData[$v]['vVehicleType'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $fFixedFare_formmated;
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "x" . strval($OrderDetails_tmp['fVehicleTypeQty']);
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['iVehicleTypeId'] = $getVehicleTypeData[$v]['iVehicleTypeId'];
            
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Title'] = $getVehicleTypeData[$v]['vVehicleType'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Amount'] = $fFixedFare_formmated;
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Qty'] = "x " . strval($OrderDetails_tmp['fVehicleTypeQty']);
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['eFareType'] = $getVehicleTypeData[$v]['eFareType'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['iVehicleTypeId'] = $getVehicleTypeData[$v]['iVehicleTypeId'];
            $currentPriceTypeArrCounti = $currentPriceTypeArrCounti + 1;

            $fFixedFare1 = $fFixedFare + $fFixedFare1;
            $qty1 = $OrderDetails_tmp['fVehicleTypeQty'] + $qty1;


            if ($v == (count($getVehicleTypeData) - 1)) {

                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_SERVICE_COST']; //$getVehicleTypeData[$v]['vVehicleType'];
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($fFixedFare1);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "x " . strval($qty1);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                $currentPriceTypeArrCountCubex = $currentPriceTypeArrCountCubex + 1;

                if ($discountValue != 0) {

                    if ($discountValueType == "percentage") {
                        $discountValue = (round(($totalFareOfServices * $discountValue), 2) / 100);
                        $vDiscount = "- " . $vCurrencySymbol . formatNum($discountValue);
                    } else {
                        $discountValue = (round($discountValue > $totalFareOfServices ? $totalFareOfServices : $discountValue, 2));
                        $vDiscount = "- " . $vCurrencySymbol . formatNum($discountValue);
                    }

                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 1]['Title'] = $languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE'];
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 1]['Amount'] = $vDiscount;
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 1]['Qty'] = "";
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 1]['vVehicleCategory'] = "";

                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE'];
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vDiscount;
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = "";
                    $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);

                    //$final_price_formatted = $vCurrencySymbol . formatNum($totalFareOfServices - $discountValue);
                    if ($_REQUEST['type'] == 'getVehicleTypeFareDetails') {
                        $totalFareOfServices = $totalFareOfServices - $discountValue;
                    } else {
                        $totalFareOfServices = $totalFareOfServices;
                    }

                    // added for tax
                    if ($fTax1 > 0) {
                        $taxamount1 = round((($totalFareOfServices * $fTax1) / 100), 2);
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fTax1 . " % ";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($taxamount1);
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                        $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
                        if ($_REQUEST['type'] == 'getVehicleTypeFareDetails') {
                            $totalFareOfServices = $totalFareOfServices + $taxamount1;
                        }
                    }

                    if ($fTax2 > 0) {
                        $taxamount2 = round((($totalFareOfServices * $fTax2) / 100), 2);
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fTax2 . " % ";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($taxamount2);
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                        $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
                        if ($_REQUEST['type'] == 'getVehicleTypeFareDetails') {
                             $totalFareOfServices = $totalFareOfServices + $taxamount2;
                        }
                    }
                    // added for tax


                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_SUBTOTAL_TXT'];
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($totalFareOfServices);
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = "";
                    $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);

                    //$currentPriceTypeArrCountCubex = $currentPriceTypeArrCountCubex + 1;
                    //$fFixedFare_serv = $fFixedFare + $fFixedFare_serv;
                    //$vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_SERVICE_COST']; //$getVehicleTypeData[$v]['vVehicleType'];
                    //$vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $vCurrencySymbol . formatNum($fFixedFare_serv);
                    //$vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "x" . strval($OrderDetails_tmp['fVehicleTypeQty']);
                    //$vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];

                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 2]['Title'] = $languageLabelsArr['LBL_SUBTOTAL_TXT'];
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 2]['Amount'] = $vCurrencySymbol . formatNum($totalFareOfServices);
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 2]['Qty'] = "";
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 2]['vVehicleCategory'] = "";

                    if ($UserType == "Driver") {
                        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iDriverId . "'";
                        $currData = $obj->MySQLSelect($sqlp);
                        $vCurrency = $currData[0]['vName'];
                    } else {
                        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
                        $currData = $obj->MySQLSelect($sqlp);
                        $vCurrency = $currData[0]['vName'];
                    }

                    if ($currData[0]['eRoundingOffEnable'] == "Yes") {

                        $roundingOffTotal_fare_amountArr = getRoundingOffAmount($totalFareOfServices, $vCurrency);
                        $roundingOffTotal_fare_amount = $roundingOffTotal_fare_amountArr['finalFareValue'];

                        if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                            $roundingMethod = "";
                        } else {
                            $roundingMethod = "-";
                        }

                        $rounding_diff = $roundingMethod . ' ' . $vCurrencySymbol . " " . formatNum($roundingOffTotal_fare_amountArr['differenceValue']);
                        $totalFareOfServices_orig = $roundingOffTotal_fare_amount;
                        $totalFareOfServices = $vCurrencySymbol . " " . formatNum($roundingOffTotal_fare_amount);

                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 3]['Title'] = $languageLabelsArr['LBL_ROUNDING_DIFF_TXT'];
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 3]['Amount'] = $rounding_diff;
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 3]['Qty'] = "";
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 3]['vVehicleCategory'] = "";

                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_ROUNDING_DIFF_TXT'];
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $rounding_diff;
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = "";
                        $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);


                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 4]['Title'] = $languageLabelsArr['LBL_FINAL_TOTAL'];
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 4]['Amount'] = $totalFareOfServices;
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 4]['Qty'] = "";
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 4]['vVehicleCategory'] = "";

                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_FINAL_TOTAL'];
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $totalFareOfServices;
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = "";
                        $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
                    }
                } else {

                    // added for tax
                    if ($fTax1 > 0) {
                        $taxamount1 = round((($totalFareOfServices * $fTax1) / 100), 2);
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fTax1 . " % ";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($taxamount1);
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                        $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
                        if ($_REQUEST['type'] == 'getVehicleTypeFareDetails') {
                            $totalFareOfServices = $totalFareOfServices + $taxamount1;
                        }
                    }

                    if ($fTax2 > 0) {
                        $taxamount2 = round((($totalFareOfServices * $fTax2) / 100), 2);
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fTax2 . " % ";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($taxamount2);
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                        $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
                        if ($_REQUEST['type'] == 'getVehicleTypeFareDetails') {
                            $totalFareOfServices = $totalFareOfServices + $taxamount2;
                        }
                    }
                    // added for tax

                    if ($UserType == "Driver") {
                        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $iDriverId . "'";
                        $currData = $obj->MySQLSelect($sqlp);
                        $vCurrency = $currData[0]['vName'];
                    } else {
                        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iMemberId . "'";
                        $currData = $obj->MySQLSelect($sqlp);
                        $vCurrency = $currData[0]['vName'];
                    }

                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_SUBTOTAL_TXT'];
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($totalFareOfServices);
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
                    $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = "";
                    $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);

                    //$currentPriceTypeArrCountCubex = $currentPriceTypeArrCountCubex + 1;
                    //$fFixedFare_serv = $fFixedFare + $fFixedFare_serv;
                    //$vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_SERVICE_COST']; //$getVehicleTypeData[$v]['vVehicleType'];
                    //$vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $vCurrencySymbol . formatNum($fFixedFare_serv);
                    //$vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "x" . strval($OrderDetails_tmp['fVehicleTypeQty']);
                    //$vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];

                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 1]['Title'] = $languageLabelsArr['LBL_SUBTOTAL_TXT'];
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 1]['Amount'] = $vCurrencySymbol . formatNum($totalFareOfServices);
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 1]['Qty'] = "";
                    $vehiclePriceTypeArr[$currentPriceTypeArrCount + 1]['vVehicleCategory'] = "";

                    if ($currData[0]['eRoundingOffEnable'] == "Yes") {

                        $roundingOffTotal_fare_amountArr = getRoundingOffAmount($totalFareOfServices, $vCurrency);
                        $roundingOffTotal_fare_amount = $roundingOffTotal_fare_amountArr['finalFareValue'];

                        if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                            $roundingMethod = "";
                        } else {
                            $roundingMethod = "-";
                        }

                        $rounding_diff = $roundingMethod . ' ' . $vCurrencySymbol . " " . formatNum($roundingOffTotal_fare_amountArr['differenceValue']);
                        $totalFareOfServices_orig = $roundingOffTotal_fare_amount;
                        $totalFareOfServices = $vCurrencySymbol . " " . formatNum($roundingOffTotal_fare_amount);

                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 2]['Title'] = $languageLabelsArr['LBL_ROUNDING_DIFF_TXT'];
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 2]['Amount'] = $rounding_diff;
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 2]['Qty'] = "";
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 2]['vVehicleCategory'] = "";

                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_ROUNDING_DIFF_TXT'];
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $rounding_diff;
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = "";
                        $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);


                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 3]['Title'] = $languageLabelsArr['LBL_FINAL_TOTAL'];
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 3]['Amount'] = $totalFareOfServices;
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 3]['Qty'] = "";
                        $vehiclePriceTypeArr[$currentPriceTypeArrCount + 3]['vVehicleCategory'] = "";

                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_FINAL_TOTAL'];
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $totalFareOfServices;
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
                        $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = "";
                        $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
                    }
                }
            }
        } else if ($getVehicleTypeData[$v]['eFareType'] == "Hourly") {
            $fPricePerHour = round($getVehicleTypeData[$v]['fPricePerHour'] * $vCurrencyRatio, 2);
            $totalFareOfServices = $totalFareOfServices + $fPricePerHour;
            $totalFareOfServices_orig = $totalFareOfServices;
            $fPricePerHour_formatted = $vCurrencySymbol . formatNum($fPricePerHour);

            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Title'] = $getVehicleTypeData[$v]['vVehicleType'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Amount'] = $fPricePerHour_formatted;
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Qty'] = " ";
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['eFareType'] = $getVehicleTypeData[$v]['eFareType'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['iVehicleTypeId'] = $getVehicleTypeData[$v]['iVehicleTypeId'];
            $currentPriceTypeArrCounti = $currentPriceTypeArrCounti + 1;

            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_SERVICE_COST']; //$getVehicleTypeData[$v]['vVehicleType'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $fPricePerHour_formatted;
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $currentPriceTypeArrCountCubex = $currentPriceTypeArrCountCubex + 1;

            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_SERVICE_CHARGE_PER_HOUR'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $fPricePerHour_formatted;
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];

            $currentPriceTypeArrCount = count($vehiclePriceTypeArr);

            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_MIN_HOUR'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $getVehicleTypeData[$v]['fMinHour'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];

            $currentPriceTypeArrCount = count($vehiclePriceTypeArr);

            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_MIN_HOUR'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $getVehicleTypeData[$v]['fMinHour'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);


            // added for tax
            if ($fTax1 > 0) {
                $taxamount1 = round((($getVehicleTypeData[$v]['fMinHour'] * $fPricePerHour * $fTax1) / 100), 2);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fTax1 . " % ";
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($taxamount1);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
            }

            if ($fTax2 > 0) {
                $taxamount2 = round((($getVehicleTypeData[$v]['fMinHour'] * $fPricePerHour * $fTax2) / 100), 2);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fTax2 . " % ";
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($taxamount2);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
            }
            // added for tax

            $final_price_formatted = $vCurrencySymbol . formatNum(($getVehicleTypeData[$v]['fMinHour'] * $fPricePerHour) + $taxamount1 +$taxamount2);
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_ESTIMATED_CHARGE'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $final_price_formatted;
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['iVehicleTypeId'] = $getVehicleTypeData[$v]['iVehicleTypeId'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_ESTIMATED_CHARGE'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $final_price_formatted;
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
        } else if ($getVehicleTypeData[$v]['eFareType'] == "Regular") {

            $iBaseFare = round($getVehicleTypeData[$v]['iBaseFare'] * $vCurrencyRatio, 2);
            $totalFareOfServices = $totalFareOfServices + $iBaseFare;
            $iBaseFare_formatted = $vCurrencySymbol . formatNum($iBaseFare);

            $fPricePerMin = round($getVehicleTypeData[$v]['fPricePerMin'] * $vCurrencyRatio, 2);
            //$totalFareOfServices = $totalFareOfServices + $fPricePerMin;
            $fPricePerMin_formatted = $vCurrencySymbol . formatNum($fPricePerMin);

            if ($eUnit != "KMs" && $getVehicleTypeData[$v]['LocationUnit'] == "KMs") {
                $getVehicleTypeData[$v]['fPricePerKM'] = $getVehicleTypeData[$v]['fPricePerKM'] * 0.621371;
            } else if ($eUnit == "KMs" && $getVehicleTypeData[$v]['LocationUnit'] == "Miles") {
                $getVehicleTypeData[$v]['fPricePerKM'] = $getVehicleTypeData[$v]['fPricePerKM'] * 1.60934;
            }

            $fPricePerKM = round($getVehicleTypeData[$v]['fPricePerKM'] * $vCurrencyRatio, 2);
            //$totalFareOfServices = $totalFareOfServices + $fPricePerKM;
            $fPricePerKM_formatted = $vCurrencySymbol . formatNum($fPricePerKM);

            $iMinFare = round($getVehicleTypeData[$v]['iMinFare'] * $vCurrencyRatio, 2);
            //$totalFareOfServices = $totalFareOfServices + $iMinFare;
            $iMinFare_formatted = $vCurrencySymbol . formatNum($iMinFare);

            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_BASE_FARE_SMALL_TXT'];
            if ($iMinFare > $iBaseFare) {
                $totalFareOfServices = $totalFareOfServices + $iMinFare - $iBaseFare;
                $iBaseFare_formatted = $iMinFare_formatted;
                $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_MINIMUM_FARE'];
            }
            $totalFareOfServices_orig = $totalFareOfServices;
            
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $iBaseFare_formatted;
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['iVehicleTypeId'] = $getVehicleTypeData[$v]['iVehicleTypeId'];

            $currentPriceTypeArrCount = count($vehiclePriceTypeArr);

            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_MINIMUM_FARE'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $iBaseFare_formatted;
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);


            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_PRICE_PER_MINUTE'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $fPricePerMin_formatted;
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];


            $currentPriceTypeArrCount = count($vehiclePriceTypeArr);

            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_PRICE_PER_MINUTE'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $fPricePerMin_formatted;
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);

            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $eUnit == "KMs" ? $languageLabelsArr['LBL_PRICE_PER_KM'] : $languageLabelsArr['LBL_PRICE_PER_MILES'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $fPricePerKM_formatted;
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];

            $currentPriceTypeArrCount = count($vehiclePriceTypeArr);

            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $eUnit == "KMs" ? $languageLabelsArr['LBL_PRICE_PER_KM'] : $languageLabelsArr['LBL_PRICE_PER_MILES'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $fPricePerKM_formatted;
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);


            // added for tax
            if ($fTax1 > 0) {
                $taxamount1 = round((($totalFareOfServices * $fTax1) / 100), 2);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_TAX1_TXT'] . " @ " . $fTax1 . " % ";
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($taxamount1);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
            }

            if ($fTax2 > 0) {
                $taxamount2 = round((($totalFareOfServices * $fTax2) / 100), 2);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_TAX2_TXT'] . " @ " . $fTax2 . " % ";
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $vCurrencySymbol . formatNum($taxamount2);
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = " ";
                $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
                $currentPriceTypeArrCountCubex = count($vehiclePriceTypeArrCubex);
            }
            // added for tax

            $final_price_formatted = $vCurrencySymbol . formatNum($totalFareOfServices+$taxamount1+$taxamount2);
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Title'] = $languageLabelsArr['LBL_ESTIMATED_CHARGE'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Amount'] = $final_price_formatted;
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['Qty'] = "";
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $vehiclePriceTypeArr[$currentPriceTypeArrCount]['iVehicleTypeId'] = $getVehicleTypeData[$v]['iVehicleTypeId'];

            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Title'] = $languageLabelsArr['LBL_ESTIMATED_CHARGE'];
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Amount'] = $final_price_formatted;
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['Qty'] = "";
            $vehiclePriceTypeArrCubex[$currentPriceTypeArrCountCubex]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];

            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Title'] = $getVehicleTypeData[$v]['vVehicleType'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Amount'] = '';
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['Qty'] = " ";
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['vVehicleCategory'] = $getVehicleTypeData[$v]['ParentCategoryName'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['eFareType'] = $getVehicleTypeData[$v]['eFareType'];
            $vehiclePriceTypeArrItems[$currentPriceTypeArrCounti]['iVehicleTypeId'] = $getVehicleTypeData[$v]['iVehicleTypeId'];
            $currentPriceTypeArrCounti = $currentPriceTypeArrCounti + 1;
        }
    }

    $returnArr['eFareTypeServices'] = $eFareTypeServices;
    $returnArr['tripFareDetailsArr'] = $vehiclePriceTypeArr;
    $returnArr['vehiclePriceTypeArrItems'] = $vehiclePriceTypeArrItems;
    $returnArr['vehiclePriceTypeArrCubex'] = $vehiclePriceTypeArrCubex;
    $returnArr['tripFareDetailsSaveArr']['FareData'] = $vehiclePriceTypeSaveArr;
    $returnArr['tripFareDetailsSaveArr']['subTotal'] = $totalFareOfServices;
    $returnArr['tripFareDetailsSaveArr']['originalTotalCommissionOfServices'] = $totalCommissionOfServices;
    $returnArr['tripFareDetailsSaveArr']['originalFareTotal'] = round(($totalFareOfServices_orig / $vCurrencyRatio), 2);
    $returnArr['tripFareDetailsSaveArr']['eFareTypeServices'] = $eFareTypeServices;
    //print_r($returnArr);die;
    return $returnArr;
}

/* function getVehicleTypeFareDetails($OrderDetails, $iMemberId, $couponCode, $UserType) {
  global $obj, $_REQUEST;
  //echo "<pre>";
  //ini_set("display_errors", 1);
  //error_reporting(E_ALL);
  $tripFareDetailsArr = $tripFareDetailsSaveArr = array();
  $OrderDetails = json_decode(stripcslashes($OrderDetails), true);
  if (count($OrderDetails) > 0) {
  $lang = "EN";
  $vCurrencyDriver = "USD";
  $tableName = "register_user";
  $fieldName = "iUserId";
  if ($UserType == "Driver") {
  $tableName = "register_driver";
  $fieldName = "iDriverId";
  }
  $userLangCode = $obj->MySQLSelect("SELECT vCurrencyDriver,vLang FROM " . $tableName . " WHERE $fieldName='$iMemberId'");
  if (count($userLangCode) > 0) {
  $lang = $userLangCode[0]['vLang'];
  $vCurrencyDriver = $userLangCode[0]['vCurrencyDriver'];
  }
  if ($lang == "") {
  $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
  }
  if ($vCurrencyDriver == "" || $vCurrencyDriver == NULL) {
  $vCurrencyDriver = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
  }
  $languageLabelsArr = getLanguageLabelsArr($lang, "1");
  $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyDriver);
  $priceRatio = $UserCurrencyData[0]['Ratio'];
  $vSymbol = $UserCurrencyData[0]['vSymbol'];
  $iVehicleTypeId = $iVehicleCategoryIds = "";
  for ($t = 0; $t < count($OrderDetails); $t++) {
  $typeId = $OrderDetails[$t]['iVehicleTypeId'];
  $iVehicleTypeId .= "," . $typeId;
  }
  $iVehicleTypeId = trim($iVehicleTypeId, ",");

  $getVehicleTypeData = $obj->MySQLSelect("SELECT vt.iVehicleTypeId,vt.tTypeDesc,vt.iVehicleCategoryId,vt.vVehicleType_" . $lang . " AS vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.fMinHour, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare,vt.fTimeSlot,vt.fTimeSlotPrice,vt.eAllowQty FROM vehicle_type vt WHERE iVehicleTypeId IN ($iVehicleTypeId) AND eStatus='Active'");




  $vehicleTypeArr = $vehiclePriceTypeArr = $vehicleCatNameArr = array();
  for ($v = 0; $v < count($getVehicleTypeData); $v++) {
  $tTypeDescription = "";
  $tTypeDesc = (array) json_decode($getVehicleTypeData[$v]['tTypeDesc']);
  if (isset($tTypeDesc['tTypeDesc_' . $lang]) && $tTypeDesc['tTypeDesc_' . $lang] != "") {
  $tTypeDescription = $tTypeDesc['tTypeDesc_' . $lang];
  }
  $getVehicleTypeData[$v]['tTypeDesc'] = $tTypeDescription;
  $vehicleTypeArr[$getVehicleTypeData[$v]['iVehicleTypeId']] = $getVehicleTypeData[$v];
  $iVehicleCategoryIds .= "," . $getVehicleTypeData[$v]['iVehicleCategoryId'];

  if ($getVehicleTypeData[$v]['ProviderPrice'] != NULL) {
  $getVehicleTypeData[$v]['fFixedFare'] = $getVehicleTypeData[$v]['ProviderPrice'];
  $getVehicleTypeData[$v]['fPricePerHour'] = $getVehicleTypeData[$v]['ProviderPrice'];
  }
  unset($getVehicleTypeData[$v]['ProviderPrice']);
  unset($getVehicleTypeData[$v]['ParentPriceType']);
  }
  if ($iVehicleCategoryIds != "") {
  $iVehicleCategoryIds = trim($iVehicleCategoryIds, ",");
  $getVehiclePriceType = $obj->MySQLSelect("SELECT VC.iVehicleCategoryId,VC.ePriceType,VC.vCategory_" . $lang . " AS vVehicleCategory,VC.iParentId,if(VC.iParentId >0,(SELECT vCategory_" . $lang . " FROM vehicle_category VC1 WHERE VC.iParentId=VC1.iVehicleCategoryId),'') AS vVehicleCategory FROM vehicle_category VC WHERE VC.iVehicleCategoryId IN ($iVehicleCategoryIds)");
  for ($c = 0; $c < count($getVehiclePriceType); $c++) {
  $vehiclePriceTypeArr[$getVehiclePriceType[$c]['iVehicleCategoryId']] = $getVehiclePriceType[$c]['ePriceType'];
  $vehicleCatNameArr[$getVehiclePriceType[$c]['iVehicleCategoryId']] = $getVehiclePriceType[$c]['vVehicleCategory'];
  }
  }
  $titleTxt = $languageLabelsArr['LBL_TITLE_TXT_ADMIN'];
  $qtyTxt = "Qty";
  $amountTxt = $languageLabelsArr['LBL_AMOUNT'];
  $totQty = $totAmt = $discountValue = 0;
  $discountValueType = "cash";
  $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eStatus='Active'");
  if (count($getCouponCode) > 0) {
  $discountValue = $getCouponCode[0]['fDiscount'];
  $discountValueType = $getCouponCode[0]['eType'];
  }
  //print_r($discountValueType);die;
  for ($t = 0; $t < count($OrderDetails); $t++) {
  $typeId = $OrderDetails[$t]['iVehicleTypeId'];
  $typeQty = $OrderDetails[$t]['fVehicleTypeQty'];
  ;
  if (isset($vehicleTypeArr[$typeId])) {
  $vehicleData = $vehicleTypeArr[$typeId];
  //print_r($vehicleData);die;
  $eFareType = $vehicleData['eFareType'];
  $eAllowQty = $vehicleData['eAllowQty'];
  $iVehicleCategoryId = $vehicleData['iVehicleCategoryId'];
  $iVehicleCategoryName = "";
  if (isset($vehicleCatNameArr[$iVehicleCategoryId])) {
  $iVehicleCategoryName = $vehicleCatNameArr[$iVehicleCategoryId];
  }
  $vVehicleType = $vehicleData['vVehicleType'];
  $fFixedFare_value = round($vehicleData['fFixedFare'] * $priceRatio, 2);
  $iBaseFare = round($vehicleData['iBaseFare'] * $priceRatio, 2);
  $vehicleData['iBaseFare'] = $vSymbol . formatNum($iBaseFare);
  $fPricePerMin = round($vehicleData['fPricePerMin'] * $priceRatio, 2);
  $vehicleData['fPricePerMin'] = $vSymbol . formatNum($fPricePerMin);
  $iMinFare = round($vehicleData['iMinFare'] * $priceRatio, 2);
  $vehicleData['iMinFare'] = $vSymbol . formatNum($iMinFare);
  $fPricePerKM = getVehicleCountryUnit_PricePerKm($vehicleData['iVehicleTypeId'], $vehicleData['fPricePerKM'], $iMemberId, $UserType);
  $fPricePerKMOrg = $fPricePerKM;
  $fPricePerKM = round($fPricePerKM * $priceRatio, 2);
  //print_r($vehicleData);die;
  $fareDetailsArr = $fareDetailsSaveArr = array();
  $fAmount = $allowServiceProviderAmt = $orgFAmount = 0;
  if (isset($vehiclePriceTypeArr[$iVehicleCategoryId]) && $vehiclePriceTypeArr[$iVehicleCategoryId] == "Provider") {
  $allowServiceProviderAmt = 1;
  }
  if ($UserType == "Driver" && $allowServiceProviderAmt == 1) {
  $serviceProData = $obj->MySQLSelect("SELECT SPA.fAmount FROM `service_pro_amount` SPA INNER JOIN driver_vehicle DV ON SPA.iDriverVehicleId=DV.iDriverVehicleId WHERE DV.iDriverId='" . $rows_driver_vehicle[0]['iDriverVehicleId'] . "' AND iVehicleTypeId='" . $typeId . "' AND DV.eStatus='Active'");
  if (count($serviceProData) > 0) {
  $fAmount = formatNum($serviceProData[0]['fAmount'] * $priceRatio);
  $orgFAmount = formatNum($serviceProData[0]['fAmount']);
  }
  }
  if ($eFareType == "Regular") {
  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $vVehicleType;
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amount'] = $fareDetailsSaveArr['amountWithCurrency'] = "";
  $fareDetailsSaveArr['eAllowQty'] = "No";
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;
  $regularBaseFare = $regularMinCharge = $iBaseFare;
  $regularOrgBaseFare = $regularOrgMinCharge = $iBaseFare;
  if ($fAmount > 0) {
  $regularBaseFare = $regularMinCharge = $fAmount;
  $regularOrgBaseFare = $regularOrgMinCharge = $orgFAmount;
  }
  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_BASE_FARE_SMALL_TXT'];
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($regularBaseFare * $typeQty);

  $fareDetailsSaveArr['amount'] = formatNum($regularOrgBaseFare * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;

  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_PRICE_MIN_TXT_ADMIN'];
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($fPricePerMin * $typeQty);
  $fareDetailsSaveArr['amount'] = formatNum($vehicleData['fPricePerMin'] * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;

  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_PRICE_PER_KM'];
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($fPricePerKM * $typeQty);
  $fareDetailsSaveArr['amount'] = formatNum($fPricePerKMOrg * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;

  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_MIN_CHARGE_TXT'];
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($regularMinCharge * $typeQty);
  $fareDetailsSaveArr['amount'] = formatNum($regularOrgMinCharge * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;
  $totAmt += formatNum($regularMinCharge * $typeQty);
  //$tripFareDetailsArr[4]['eDisplaySeperator'] = "Yes";
  } else if ($eFareType == "Hourly") {
  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr1[$titleTxt] = $fareDetailsSaveArr['title'] = $vVehicleType;
  $fareDetailsArr1[$qtyTxt] = $fareDetailsSaveArr['qty'] = $fareDetailsArr1[$amountTxt] = $fareDetailsSaveArr['amount'] = $fareDetailsSaveArr['amountWithCurrency'] = "";
  $fareDetailsSaveArr['eAllowQty'] = "No";
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr1['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr1;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;
  $hourlyBaseFare = $iBaseFare;
  $hourlyBaseFareOrg = $iBaseFare;
  $hourlyMinCharge = $iBaseFare + $iMinFare;
  $hourlyMinChargeOrg = $iBaseFare + $vehicleData['iMinFare'];
  if ($fAmount > 0) {
  $hourlyBaseFare = $fAmount;
  $hourlyBaseFareOrg = $orgFAmount;
  $hourlyMinCharge = $fAmount + $iMinFare;
  $hourlyMinChargeOrg = $orgFAmount + $vehicleData['iMinFare'];
  }
  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_MINIMUM'] . " " . $languageLabelsArr['LBL_HOUR'] . " (" . $vehicleData['fMinHour'] . " Hour)";
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($iMinFare * $typeQty);
  $fareDetailsSaveArr['amount'] = formatNum($vehicleData['iMinFare'] * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;

  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = 'Extra Price Slot (' . $vehicleData['fTimeSlot'] . ' Min)';
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($iMinFare * $typeQty);
  $fareDetailsSaveArr['amount'] = formatNum($vehicleData['iMinFare'] * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;

  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_BASE_FARE_SMALL_TXT'];
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($hourlyBaseFare * $typeQty);
  $fareDetailsSaveArr['amount'] = formatNum($hourlyBaseFareOrg * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;

  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_MIN_CHARGE_TXT'];
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($hourlyMinCharge * $typeQty);
  $fareDetailsSaveArr['amount'] = formatNum($hourlyMinChargeOrg * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;
  //echo "<pre>";
  //print_r($tripFareDetailsSaveArr);die;
  $totAmt += formatNum($hourlyMinCharge * $typeQty);
  //$tripFareDetailsArr[4]['eDisplaySeperator'] = "Yes";
  } else if ($eFareType == "Fixed") {
  $fareDetailsSaveArr['id'] = $typeId;
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $vVehicleType;
  $fareDetailsArr[$qtyTxt] = "x" . $typeQty;
  $fareDetailsSaveArr['qty'] = $typeQty;
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($fFixedFare_value * $typeQty);
  $fareDetailsSaveArr['amount'] = formatNum($vehicleData['fFixedFare'] * $typeQty);
  $fareDetailsSaveArr['eAllowQty'] = $eAllowQty;
  $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = $iVehicleCategoryName;
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;
  $totQty += $typeQty;
  $totAmt += formatNum($fFixedFare_value * $typeQty);
  //$tripFareDetailsArr[$t][1][$languageLabelsArr['LBL_FIXED_FARE_TXT_ADMIN']] = $vSymbol . " " . formatNum($fFixedFare_value * $typeQty);
  }
  }
  }
  if ($couponCode != '' && $discountValue != 0) {
  if ($discountValueType == "percentage") {
  $vDiscount = round($discountValue, 1) . ' ' . "%";
  $discountValue = round(($totAmt * $discountValue), 1) / 100;
  } else {
  if ($discountValue > $totAmt) {
  $vDiscount = round($totAmt, 1) . ' ' . $vSymbol;
  } else {
  $vDiscount = round($discountValue, 1) . ' ' . $vSymbol;
  }
  }
  }
  if ($discountValue > 0) {
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_PROMO_DISCOUNT_TITLE'];
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = "- " . $vSymbol . " " . formatNum($discountValue);
  $fareDetailsSaveArr['amount'] = formatNum($discountValue);
  $fareDetailsSaveArr['eAllowQty'] = "No";
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;
  if ($discountValue > $totAmt) {
  $totAmt = 0;
  } else {
  $totAmt = $totAmt - $discountValue;
  }
  }
  if ($eFareType == "Fixed") {
  $fareDetailsArr[$titleTxt] = $fareDetailsSaveArr['title'] = $languageLabelsArr['LBL_SUBTOTAL_TXT'];
  $fareDetailsArr[$qtyTxt] = $fareDetailsSaveArr['qty'] = $fareDetailsSaveArr['vVehicleCategory'] = $fareDetailsArr['vVehicleCategory'] = "";
  $fareDetailsArr[$amountTxt] = $fareDetailsSaveArr['amountWithCurrency'] = $vSymbol . " " . formatNum($totAmt);
  $fareDetailsSaveArr['amount'] = formatNum($totAmt);
  $fareDetailsSaveArr['eAllowQty'] = "No";
  $tripFareDetailsArr[] = $fareDetailsArr;
  $tripFareDetailsSaveArr[] = $fareDetailsSaveArr;
  }
  }

  $returnArr['tripFareDetailsArr'] = $tripFareDetailsArr;
  $returnArr['tripFareDetailsSaveArr'] = $tripFareDetailsSaveArr;
  $returnArr['subTotal'] = $totAmt;

  return $returnArr;
  } */

############################################################## Display Trip Charge To Driver For UberX Trip ####################################################################################

function DisplayTripChargeForUberX($TripID) {
    global $obj, $generalobj, $tconfig, $SERVICE_PROVIDER_FLOW,$tripDetailsArr;
    $returnArr = array();
    $where = " iTripId = '" . $TripID . "'";
    //Added By HJ On 13-06-2020 For Optimization trips Table Query Start
    if(isset($tripDetailsArr["trips_".$TripID])){
        $tripData = $tripDetailsArr["trips_".$TripID];
    }else{
        $tripData = $obj->MySQLSelect("SELECT * from trips WHERE iTripId = '" . $TripID . "'");
        $tripDetailsArr["trips_".$TripID] = $tripData;
    }
    //Added By HJ On 13-06-2020 For Optimization trips Table Query End
    // echo "<pre>"; print_r($tripData); die;
    $eType = $tripData[0]['eType'];
    if ($eType == "UberX") {
        if ($SERVICE_PROVIDER_FLOW == "Provider" && isset($tripData[0]['tVehicleTypeFareData']) && $tripData[0]['tVehicleTypeFareData'] != "" && $tripData[0]['eFareType'] == 'Fixed') {

            $userData = $obj->MySQLSelect("SELECT rd.vCurrencyDriver, rd.vLang, cu.vSymbol, cu.Ratio, co.eUnit FROM register_driver as rd, currency as cu, country as co WHERE rd.iDriverId='" . $tripData[0]['iDriverId'] . "' AND cu.vName = rd.vCurrencyDriver AND co.vCountryCode = rd.vCountry");

            $priceRatio = $userData[0]['Ratio'];
            $vSymbol = $userData[0]['vSymbol'];

            $tVehicleTypeFareData = (array) json_decode($tripData[0]['tVehicleTypeFareData']);
            $tVehicleTypeFareData = (array) $tVehicleTypeFareData['FareData'];
            $totalFareOfServices = 0;
            for ($fd = 0; $fd < count($tVehicleTypeFareData); $fd++) {
                $eAllowQty = $tVehicleTypeFareData[$fd]->eAllowQty;
                $typeQty = $tVehicleTypeFareData[$fd]->qty;

                $typeAmount = $currencySymbol . formatNum($tVehicleTypeFareData[$fd]->amount * $priceRatio);

                if ($typeQty < 1) {
                    $typeQty = 1;
                }

                $amountOfService = $tVehicleTypeFareData[$fd]->amount;
                $amountOfService = $amountOfService * $typeQty;

                $totalFareOfServices = $totalFareOfServices + $amountOfService;
            }
            $totalFareOfServices = $totalFareOfServices * $priceRatio;
            $totalFareOfServices = round($totalFareOfServices, 2);
            $returnArr['TotalFareUberX'] = $vSymbol . ' ' . formatNum($totalFareOfServices);
            $returnArr['TotalFareUberXValue'] = $totalFareOfServices;
            $returnArr['UberXFareCurrencySymbol'] = $vSymbol;

            return $returnArr;
        }
        $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
        $iVehicleTypeId = $tripData[0]['iVehicleTypeId'];
        $fVisitFee = $tripData[0]['fVisitFee'];
        $startDate = $tripData[0]['tStartDate'];
        $endDateOfTrip = $tripData[0]['tEndDate'];
        $iQty = $tripData[0]['iQty'];
        $destination_lat = $tripData[0]['tEndLat'];
        $destination_lon = $tripData[0]['tEndLong'];
        //$endDateOfTrip=@date("Y-m-d H:i:s");
        /* $iVehicleCategoryId=get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId',$iVehicleTypeId,'','true');
          $iParentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId,'','true'); */
        $sql = "SELECT vc.iParentId from vehicle_category as vc LEFT JOIN vehicle_type as vt ON vc.iVehicleCategoryId=vt.iVehicleCategoryId WHERE vt.iVehicleTypeId = '" . $iVehicleTypeId . "'";
        $VehicleCategoryData = $obj->MySQLSelect($sql);
        $iParentId = $VehicleCategoryData[0]['iParentId'];
        if ($iParentId == 0) {
            $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
        } else {
            $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
        }
        //$ePriceType=get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
        $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
        if ($tripData[0]['eFareType'] == 'Hourly') {
            $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$TripID'";
            $db_tripTimes = $obj->MySQLSelect($sql22);
            $totalSec = 0;
            $iTripTimeId = '';
            foreach ($db_tripTimes as $dtT) {
                if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                    $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
                }
            }
            $totalTimeInMinutes_trip = @round(abs($totalSec) / 60, 2);
        } else {
            $totalTimeInMinutes_trip = @round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60, 2);
        }
        $totalHour = $totalTimeInMinutes_trip / 60;
        $tripDistance = calcluateTripDistance($TripID);
        $sourcePointLatitude = $tripData[0]['tStartLat'];
        $sourcePointLongitude = $tripData[0]['tStartLong'];
        if ($totalTimeInMinutes_trip <= 1) {
            $FinalDistance = $tripDistance;
        } else {
            $FinalDistance = checkDistanceWithGoogleDirections($tripDistance, $sourcePointLatitude, $sourcePointLongitude, $destination_lat, $destination_lon);
        }
        $tripDistance = $FinalDistance;
        $fPickUpPrice = $tripData[0]['fPickUpPrice'];
        $fNightPrice = $tripData[0]['fNightPrice'];
        $eFareType = get_value('trips', 'eFareType', 'iTripId', $TripID, '', 'true');
        $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
        $fAmount = 0;
        $Fare_data = getVehicleFareConfig("vehicle_type", $iVehicleTypeId);
        // echo "<pre>"; print_r($tripData); die;
        $fPricePerKM = getVehicleCountryUnit_PricePerKm($iVehicleTypeId, $Fare_data[0]['fPricePerKM']);

        $Minute_Fare = $Fare_data[0]['fPricePerMin'] * $totalTimeInMinutes_trip;
        $Distance_Fare = $fPricePerKM * $tripDistance;
        $iBaseFare = $Fare_data[0]['iBaseFare'];
        $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare;
        $fSurgePriceDiff = (($total_fare * $surgePrice) - $total_fare);
        $total_fare = $total_fare + $fSurgePriceDiff;
        $iMinFare = round($Fare_data[0]['iMinFare'] * $surgePrice, 2);
        if ($iMinFare > $total_fare) {
            $total_fare = $iMinFare;
        }
        $fMinHour = $Fare_data[0]['fMinHour'];
        if ($totalHour > $fMinHour) {
            $miniminutes = $fMinHour * 60;
            $TripTimehours = $totalTimeInMinutes_trip / 60;
            $tothours = intval($TripTimehours);
            $extrahours = $TripTimehours - $tothours;
            $extraminutes = $extrahours * 60;
        }
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);
            if (count($serviceProData) > 0) {
                $fAmount = $serviceProData[0]['fAmount'];
                if ($eFareType == "Fixed") {
                    $fAmount = $fAmount * $iQty;
                } else if ($eFareType == "Hourly") {
                    if ($totalHour > $fMinHour) {
                        $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                        $extraprice = 0;
                        if ($fTimeSlot > 0) {
                            $pricetimeslot = 60 / $fTimeSlot;
                            $pricepertimeslot = $fAmount / $pricetimeslot;
                            $fTimeSlotPrice = $pricepertimeslot;
                            $extraprice =0;
                            if($fTimeSlot >0){
                                $extratimeslot = ceil($extraminutes / $fTimeSlot);
                                $extraprice = $extratimeslot * $fTimeSlotPrice;
                            }else if($extraminutes > 0){
                                $extraprice = ($fAmount/60)*$extraminutes;
                            }
                        }
                        $fAmount = ($fAmount * $tothours) + $extraprice;
                    } else {
                        $fAmount = $fAmount * $fMinHour;
                    }
                    //$fAmount = $fAmount * $totalHour;
                } else {
                    $fAmount = $total_fare;
                }
            } else {
                if ($eFareType == "Fixed") {
                    $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
                } else if ($eFareType == "Hourly") {
                    if ($totalHour > $fMinHour) {
                        $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                        $pricetimeslot = 60 / $fTimeSlot;
                        $pricepertimeslot = $Fare_data[0]['fPricePerHour'] / $pricetimeslot;
                        $fTimeSlotPrice = $pricepertimeslot;
                        //$fTimeSlotPrice = $Fare_data[0]['fTimeSlotPrice'];
                        $extraprice =0;
                        if($fTimeSlot > 0){
                            $extratimeslot = ceil($extraminutes / $fTimeSlot);
                            $extraprice = $extratimeslot * $fTimeSlotPrice;
                        }else if($extraminutes > 0){
                            $extraprice = ($Fare_data[0]['fPricePerHour']/60)*$extraminutes;
                        }
                        $fAmount = round((($Fare_data[0]['fPricePerHour'] * $tothours) + $extraprice), 2);
                    } else {
                        $fAmount = round($Fare_data[0]['fPricePerHour'] * $fMinHour, 2);
                        // $fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour,2);
                    }
                } else {
                    $fAmount = $total_fare;
                }
            }
        } else {
            if ($eFareType == "Fixed") {
                $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
            } else if ($eFareType == "Hourly") {
                if ($totalHour > $fMinHour) {
                    $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                    $pricetimeslot = 60 / $fTimeSlot;
                    $pricepertimeslot = $Fare_data[0]['fPricePerHour'] / $pricetimeslot;
                    $fTimeSlotPrice = $pricepertimeslot;
                    //$fTimeSlotPrice = $Fare_data[0]['fTimeSlotPrice'];
                    $extraprice = 0;
                    if($fTimeSlot > 0){
                        $extratimeslot = ceil($extraminutes / $fTimeSlot);
                        $extraprice = $extratimeslot * $fTimeSlotPrice;
                    }else if($extraminutes > 0){
                        $extraprice = ($Fare_data[0]['fPricePerHour']/60)*$extraminutes;
                    }
                    $fAmount = round((($Fare_data[0]['fPricePerHour'] * $tothours) + $extraprice), 2);
                } else {
                    $fAmount = round($Fare_data[0]['fPricePerHour'] * $fMinHour, 2);
                    //$fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour,2);
                }
            } else {
                $fAmount = $total_fare;
            }
        }
        $final_display_charge = $fAmount + $fVisitFee;
        $returnArr['Action'] = "1";

        $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'], '', 'true');
        $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
        $currencySymbol = $currencySymbolRationDriver[0]['vSymbol'];
        $currencyRationDriver = $currencySymbolRationDriver[0]['Ratio'];
        $final_display_charge = $final_display_charge * $currencyRationDriver;
        $final_display_charge = round($final_display_charge, 2);
        //$final_display_charge = formatNum($final_display_charge);
        $returnArr['TotalFareUberX'] = $currencySymbol . ' ' . formatNum($final_display_charge);
        $returnArr['TotalFareUberXValue'] = $final_display_charge;
        $returnArr['UberXFareCurrencySymbol'] = $currencySymbol;
    } else {
        $returnArr['TotalFareUberX'] =$returnArr['TotalFareUberXValue']=$returnArr['UberXFareCurrencySymbol']= "";
    }
    return $returnArr;
}

############################################################## Display Trip Charge To Driver For UberX Trip ####################################################################################
//Added By HJ On 24-01-2019 For Check Driver Vehicle Service Available Or Not Start

function getServiceProviderVehicleData($driverVehicles, $iVehicleTypeIds) {
    global $obj;
    //echo "<pre>";
    $mainArr = array();
    for ($d = 0; $d < count($driverVehicles); $d++) {
        $carTypeArr = explode(",", $driverVehicles[$d]['vCarType']);
        $driverId = $driverVehicles[$d]['iDriverId'];
        $explodeTypeIds = explode(",", $iVehicleTypeIds);
        for ($t = 0; $t < count($explodeTypeIds); $t++) {
            $resultStatusArr = array();
            //if ($driverId == "117") {
            $getVehicleType = $obj->MySQLSelect("SELECT iVehicleTypeId,iVehicleCategoryId FROM vehicle_type WHERE `iVehicleCategoryId`='" . $explodeTypeIds[$t] . "' AND eStatus='Active'");
            $typeArr = array();
            for ($v = 0; $v < count($getVehicleType); $v++) {
                $iVehicleCategoryId = $getVehicleType[$v]['iVehicleCategoryId'];
                $iVehicleTypeId = $getVehicleType[$v]['iVehicleTypeId'];
                $typeArr[$iVehicleCategoryId][] = $iVehicleTypeId;
            }
            foreach ($typeArr as $key => $value) {
                $result = !empty(array_intersect($value, $carTypeArr));
                //echo $result;die;
                $foundArr = array();
                for ($r = 0; $r < count($value); $r++) {
                    $typeId = $value[$r];
                    if (in_array($typeId, $carTypeArr)) {
                        $foundArr[] = 1;
                    }
                }
                $resultStatusArr[] = $result;
                //echo $result."<br>";
                /* if (in_array(1, $foundArr)) {
                  $resultStatusArr[] = 1;
                  } else {
                  $resultStatusArr[] = 0;
                  } */
            }
            if (in_array(1, $resultStatusArr)) {
                $mainArr[] = 1;
            } else {
                $mainArr[] = 0;
            }
        }
    }
    //echo "<pre>";print_r($mainArr);die;
    $status = "Success";
    if (in_array(0, $mainArr)) {
        $status = "Failed";
    }
    return $status;
}

//Added By HJ On 24-01-2019 For Check Driver Vehicle Service Available Or Not End
############################################ Functions added ############################################

if ($type == "getServiceCategoryTypes") {
    global $generalobj;

    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? clean($_REQUEST['iVehicleCategoryId']) : 0;
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $userId = isset($_REQUEST['userId']) ? clean($_REQUEST['userId']) : '';
    $eCheck = isset($_REQUEST['eCheck']) ? clean($_REQUEST['eCheck']) : 'No';

    $pickuplocationarr = array(
        $vLatitude,
        $vLongitude
    );
    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
    if ($eCheck == "" || $eCheck == NULL) {
        $eCheck = "No";
    }
    if ($eCheck == "Yes") {
        //$allowed_ans = checkRestrictedAreaNew($pickuplocationarr,"No");
        $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
        if ($allowed_ans == "Yes") {
            $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) AND iVehicleCategoryId = '" . $iVehicleCategoryId . "' ORDER BY iDisplayOrder ASC";
            $vehicleTypes = $obj->MySQLSelect($sql23);
            if (count($vehicleTypes) > 0) {
                $returnArr['Action'] = "1";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_SERVICES_AVAIL_FOR_JOB_LOC";
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_JOB_LOCATION_NOT_ALLOWED";
        }

        setDataResponse($returnArr);
    } else {
        if ($userId != "") {
            $sql1 = "SELECT vLang,vCurrencyPassenger FROM `register_user` WHERE iUserId='$userId'";
            $row = $obj->MySQLSelect($sql1);
            $lang = $row[0]['vLang'];
            if ($lang == "" || $lang == NULL) {
                //$lang = "EN";
                $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }

            $vCurrencyPassenger = $row[0]['vCurrencyPassenger'];
            if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
                $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
            }
            $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyPassenger);
            $priceRatio = $UserCurrencyData[0]['Ratio'];
            $vSymbol = $UserCurrencyData[0]['vSymbol'];

            $vehicleCategoryData = get_value('vehicle_category', "vCategoryTitle_" . $lang . " as vCategoryTitle, tCategoryDesc_" . $lang . " as tCategoryDesc", 'iVehicleCategoryId', $iVehicleCategoryId);
            $vCategoryTitle = $vehicleCategoryData[0]['vCategoryTitle'];
            $vCategoryDesc = $vehicleCategoryData[0]['tCategoryDesc'];
            $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.ePriceType, vt.iVehicleTypeId, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.fMinHour, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM vehicle_category as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vc.eStatus='Active'  AND vt.eStatus='Active' AND vt.iVehicleCategoryId='$iVehicleCategoryId' AND vt.iLocationid IN ($GetVehicleIdfromGeoLocation) ORDER BY vt.iDisplayOrder ASC";
            //AND vt.eType='UberX'
            $Data = $obj->MySQLSelect($sql2);
            if (!empty($Data)) {
                for ($i = 0; $i < count($Data); $i++) {
                    $Data[$i]['fFixedFare_value'] = round($Data[$i]['fFixedFare'] * $priceRatio, 2);
                    $fFixedFare = round($Data[$i]['fFixedFare'] * $priceRatio, 2);
                    $Data[$i]['fFixedFare'] = $vSymbol . formatNum($fFixedFare);
                    $Data[$i]['fPricePerHour_value'] = round($Data[$i]['fPricePerHour'] * $priceRatio, 2);
                    $fPricePerHour = round($Data[$i]['fPricePerHour'] * $priceRatio, 2);
                    $Data[$i]['fPricePerHour'] = $vSymbol . formatNum($fPricePerHour);
                    $Data[$i]['fPricePerKM'] = getVehicleCountryUnit_PricePerKm($Data[$i]['iVehicleTypeId'], $Data[$i]['fPricePerKM'], $userId, "Passenger");
                    $fPricePerKM = round($Data[$i]['fPricePerKM'] * $priceRatio, 2);
                    $Data[$i]['fPricePerKM'] = $vSymbol . formatNum($fPricePerKM);
                    $fPricePerMin = round($Data[$i]['fPricePerMin'] * $priceRatio, 2);
                    $Data[$i]['fPricePerMin'] = $vSymbol . formatNum($fPricePerMin);
                    $iBaseFare = round($Data[$i]['iBaseFare'] * $priceRatio, 2);
                    $Data[$i]['iBaseFare'] = $vSymbol . formatNum($iBaseFare);
                    $fCommision = round($Data[$i]['fCommision'] * $priceRatio, 2);
                    $Data[$i]['fCommision'] = $vSymbol . formatNum($fCommision);
                    $iMinFare = round($Data[$i]['iMinFare'] * $priceRatio, 2);
                    $Data[$i]['iMinFare'] = $vSymbol . formatNum($iMinFare);
                    $Data[$i]['vSymbol'] = $vSymbol;
                    $Data[$i]['vCategoryTitle'] = $vCategoryTitle;
                    $Data[$i]['vCategoryDesc'] = $vCategoryDesc;
                    $iParentId = $Data[$i]['iParentId'];
                    if ($iParentId == 0) {
                        $ePriceType = $Data[$i]['ePriceType'];
                    } else {
                        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
                    }
                    $Data[$i]['ePriceType'] = $ePriceType;
                    $Data[$i]['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ePriceType == "Provider" ? "Yes" : "No";
                    //$Data[$i]['ALLOW_SERVICE_PROVIDER_AMOUNT']= $Data[$i]['ePriceType'] == "Provider"? "Yes" :"No";
                }

                $returnArr['Action'] = "1";
                $returnArr['message'] = $Data;
                //$returnArr['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ALLOW_SERVICE_PROVIDER_AMOUNT;
                $returnArr['vCategoryTitle'] = $vCategoryTitle;
                $returnArr['vCategoryDesc'] = $vCategoryDesc;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_DATA_AVAIL";
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }

    setDataResponse($returnArr);
}
##########################################################################
if ($type == "getBanners") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    if ($iMemberId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iDriverId', $iDriverId, '', 'true');
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        //$banners = get_value('banners', 'vImage', 'vCode', $vLanguage, ' ORDER BY iDisplayOrder ASC');
        $sql = "SELECT vImage FROM `banners` WHERE vCode = '" . $vLanguage . "' AND eStatus = 'Active' ORDER BY iDisplayOrder ASC";
        $banners = $obj->MySQLSelect($sql);
        $data = array();
        $count = 0;
        for ($i = 0; $i < count($banners); $i++) {
            if ($banners[$i]['vImage'] != "") {
                $data[$count]['vImage'] = $tconfig["tsite_url"] . 'assets/img/images/' . $banners[$i]['vImage'];
                $count++;
            }
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $data;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
#########################################################################
/* if ($type == "getServiceCategories") {
  global $generalobj;

  $parentId = isset($_REQUEST['parentId']) ? clean($_REQUEST['parentId']) : 0;
  $userId = isset($_REQUEST['userId']) ? clean($_REQUEST['userId']) : '';
  if ($userId != "") {
  $sql1 = "SELECT vLang FROM `register_user` WHERE iUserId='$userId'";
  $row = $obj->MySQLSelect($sql1);
  $lang = $row[0]['vLang'];
  if ($lang == "") {
  $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
  }

  $vehicle_category_main = get_value('vehicle_category', 'vCategory_'.$lang , 'iVehicleCategoryId', $parentId , '', 'true');
  $sql2 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage, iDisplayOrder,vCategory_" . $lang . " as vCategory, eCatType, tBannerButtonText,iServiceId FROM vehicle_category WHERE eStatus='Active' AND iParentId='$parentId' ORDER BY iDisplayOrder,iVehicleCategoryId ASC";
  $Data = $obj->MySQLSelect($sql2);

  $DataCatTypeArr = array();

  $Datacategory = array();
  if ($parentId == 0) {
  if (count($Data) > 0) {
  $k = 0;
  for ($i = 0; $i < count($Data); $i++) {
  $BannerButtonText = "tBannerButtonText_" . $lang;
  $tBannerButtonTextArr = json_decode($Data[$i]['tBannerButtonText'], true);
  $tBannerButtonText = $tBannerButtonTextArr[$BannerButtonText];

  $sql3 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage, vCategory_" . $lang . " as vCategory, eCatType, tBannerButtonText, iServiceId FROM vehicle_category WHERE eStatus='Active' AND iParentId='" . $Data[$i]['iVehicleCategoryId'] . "' ORDER BY iDisplayOrder ASC";
  $Data2 = $obj->MySQLSelect($sql3);
  if (count($Data2) > 0) {
  for ($j = 0; $j < count($Data2); $j++) {
  if ($Data2[$j]['eCatType'] == "ServiceProvider") {
  $sql4 = "SELECT iVehicleTypeId FROM vehicle_type WHERE iVehicleCategoryId='" . $Data2[$j]['iVehicleCategoryId'] . "'";
  $Data3 = $obj->MySQLSelect($sql4);
  if (count($Data3) > 0) {
  $Datacategory[$k]['eCatType'] = $Data[$i]['eCatType'];
  $Datacategory[$k]['iVehicleCategoryId'] = $Data[$i]['iVehicleCategoryId'];
  $Datacategory[$k]['vCategory'] = $Data[$i]['vCategory'];
  $Datacategory[$k]['vCategoryBanner'] = $Data[$i]['vCategory'];
  $Datacategory[$k]['vLogo'] = $Data[$i]['vLogo'];
  $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/android/' . $Data[$i]['vLogo'];
  $Datacategory[$k]['eShowType'] = $Data[$i]['eShowType'];
  $Datacategory[$k]['iServiceId'] = $Data[$i]['iServiceId'];
  $Datacategory[$k]['tBannerButtonText'] = $tBannerButtonText;
  $Datacategory[$k]['vBannerImage'] = ($Data[$i]['vBannerImage'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/' . $Data[$i]['vBannerImage'] : "";

  $k++;
  }
  } else {
  $Datacategory[$k]['eCatType'] = $Data[$i]['eCatType'];
  $Datacategory[$k]['iVehicleCategoryId'] = $Data[$i]['iVehicleCategoryId'];
  $Datacategory[$k]['vCategory'] = $Data[$i]['vCategory'];
  $Datacategory[$k]['vCategoryBanner'] = $Data[$i]['vCategory'];
  $Datacategory[$k]['vLogo'] = $Data[$i]['vLogo'];
  $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/android/' . $Data[$i]['vLogo'];
  $Datacategory[$k]['eShowType'] = $Data[$i]['eShowType'];
  $Datacategory[$k]['iServiceId'] = $Data[$i]['iServiceId'];
  $Datacategory[$k]['tBannerButtonText'] = $tBannerButtonText;
  $Datacategory[$k]['vBannerImage'] = ($Data[$i]['vBannerImage'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/' . $Data[$i]['vBannerImage'] : "";

  $k++;
  }
  }
  }
  }
  }
  } else {

  if (count($Data) > 0) {
  $k = 0;
  for ($j = 0; $j < count($Data); $j++) {
  $BannerButtonText = "tBannerButtonText_" . $lang;
  $tBannerButtonTextArr = json_decode($Data[$j]['tBannerButtonText'], true);
  $tBannerButtonText = $tBannerButtonTextArr[$BannerButtonText];

  $sql4 = "SELECT iVehicleTypeId FROM vehicle_type WHERE iVehicleCategoryId='" . $Data[$j]['iVehicleCategoryId'] . "' ORDER BY iDisplayOrder ASC";
  $Data3 = $obj->MySQLSelect($sql4);
  if (count($Data3) > 0) {
  $Datacategory[$k]['eCatType'] = $Data[$j]['eCatType'];
  $Datacategory[$k]['iVehicleCategoryId'] = $Data[$j]['iVehicleCategoryId'];
  $Datacategory[$k]['vCategory'] = $Data[$j]['vCategory'];
  $Datacategory[$k]['vCategoryBanner'] = $Data[$j]['vCategory'];
  $Datacategory[$k]['vLogo'] = $Data[$j]['vLogo'];
  $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$j]['iVehicleCategoryId'] . '/android/' . $Data[$j]['vLogo'];
  $Datacategory[$k]['eShowType'] = "";
  $Datacategory[$k]['tBannerButtonText'] = $tBannerButtonText;
  $Datacategory[$k]['vBannerImage'] = "";
  $k++;
  }
  }
  }
  }

  $Datacategory1 = array_unique($Datacategory, SORT_REGULAR);
  $DatanewArr = array();
  foreach ($Datacategory1 as $inner) {
  array_push($DatanewArr, $inner);
  }

  $returnArr['Action'] = "1";
  $returnArr['vParentCategoryName'] = $vehicle_category_main;
  $returnArr['message'] = $DatanewArr;
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
  }
  setDataResponse($returnArr);
  } */
###################################################################################
if ($type == "getvehicleCategory") {
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? trim($_REQUEST['iVehicleCategoryId']) : 0;

    $languageCode = "";
    if ($iDriverId != "") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    }

    if ($languageCode == "" || $languageCode == NULL) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $ssql_category = "";
    $returnName = "vTitle";
    if ($iVehicleCategoryId != "" && ($iVehicleCategoryId == 0 || $iVehicleCategoryId == "0")) {
        $ssql_category = " and (select count(iVehicleCategoryId) from vehicle_category where iParentId=vc.iVehicleCategoryId AND eCatType='ServiceProvider' AND eStatus='Active') > 0";
        $returnName = "vCategory";
    }

    $per_page = 200;
    $sql_all = "SELECT COUNT(iVehicleCategoryId) As TotalIds FROM vehicle_category as vc WHERE vc.eStatus='Active' AND vc.eCatType='ServiceProvider' AND vc.iParentId='" . $iVehicleCategoryId . "'" . $ssql_category;
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);

    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    $sql = "SELECT vc.iVehicleCategoryId, vc.vCategory_" . $languageCode . " as '" . $returnName . "', vc.vLogo FROM vehicle_category as vc WHERE vc.eStatus='Active' AND vc.eCatType='ServiceProvider' AND vc.iParentId='" . $iVehicleCategoryId . "'" . $ssql_category . $limit;
    $vehicleCategoryDetail = $obj->MySQLSelect($sql);
    $vehicleCategoryData = array();
    if (count($vehicleCategoryDetail) > 0) {
        //Added By HJ On 11-07-2019 For Get Vehicle Type Data Start
        $Data3 = $obj->MySQLSelect("SELECT iVehicleTypeId,iVehicleCategoryId FROM vehicle_type WHERE eStatus='Active' ORDER BY iDisplayOrder ASC");
        $categoryArr = array();
        for ($vc = 0; $vc < count($Data3); $vc++) {
            $categoryArr[$Data3[$vc]['iVehicleCategoryId']][] = $Data3[$vc];
        }
        //Added By HJ On 11-07-2019 For Get Vehicle Type Data End
        //Added By HJ On 06-08-2019 For Check Vehicle Category's Vehicle Type Exists Or Not Start Discuss with KS Sir Start
        /* for ($sd = 0; $sd < count($vehicleCategoryDetail); $sd++) {
          $iVehicleCategoryId = $vehicleCategoryDetail[$sd]['iVehicleCategoryId'];
          //print_r($categoryArr[$iVehicleCategoryId]);die;
          if (empty($categoryArr[$iVehicleCategoryId])) {
          unset($vehicleCategoryDetail[$sd]);
          }
          }
          $vehicleCategoryDetail = array_values($vehicleCategoryDetail); */
        //Added By HJ On 06-08-2019 For Check Vehicle Category's Vehicle Type Exists Or Not Start Discuss with KS Sir End
        //echo "<pre>";print_R($categoryArr);die;
        $vehicleCategoryData = $vehicleCategoryDetail;
        if ($iVehicleCategoryId != "" && ($iVehicleCategoryId == 0 || $iVehicleCategoryId == "0")) {
            $i = 0;
            while (count($vehicleCategoryDetail) > $i) {
                $iVehicleCategoryId = $vehicleCategoryDetail[$i]['iVehicleCategoryId'];
                $sql = "SELECT vCategory_" . $languageCode . " as vTitle,iVehicleCategoryId, vLogo FROM `vehicle_category` WHERE iParentId='" . $iVehicleCategoryId . "' AND eCatType='ServiceProvider' AND eStatus='Active'";
                $subCategoryData = $obj->MySQLSelect($sql);
                //Added By HJ On 11-07-2019 For Check Category's Service Exists Or Not Start
                $subCatArr = array();
                for ($d = 0; $d < count($subCategoryData); $d++) {

                    //print_r($subCategoryData);die;
                    $subCategoryData[$d]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $subCategoryData[$d]['iVehicleCategoryId'] . '/android/' . $subCategoryData[$d]['vLogo'];
                    $subCategoryData[$d]['vLogo_BG_color'] = RANDOM_COLORS_ARR[array_rand(RANDOM_COLORS_ARR, 1)];
                    $subCategoryData[$d]['vLogo_TINT_color'] = "#FFFFFF";

                    $serviceArr = array();
                    if (isset($categoryArr[$subCategoryData[$d]['iVehicleCategoryId']])) {
                        $serviceArr = $categoryArr[$subCategoryData[$d]['iVehicleCategoryId']];
                    }
                    if (count($serviceArr) > 0) {
                        $subCatArr[] = $subCategoryData[$d];
                    }
                }
                if (count($subCatArr) > 0) {
                    $vehicleCategoryData[$i]['SubCategory'] = $subCatArr;
                } else {
                    unset($vehicleCategoryData[$i]);
                }
                //Added By HJ On 11-07-2019 For Check Category's Service Exists Or Not End
                $i++;
            }
        }
        if (count($vehicleCategoryData) > 0) {
            $returnArr['Action'] = "1";
            if ($TotalPages > $page) {
                $returnArr['NextPage'] = "" . ($page + 1);
            } else {
                $returnArr['NextPage'] = "0";
            }
            $returnArr['message'] = array_values($vehicleCategoryData);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_DATA_AVAIL";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "getServiceTypes") {
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $languageCode = "";
    if ($iDriverId != "") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    }
    if ($languageCode == "" || $languageCode == NULL) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql = "SELECT * FROM `register_driver` where iDriverId ='" . $iDriverId . "'";
    $db_driverdetail = $obj->MySQLSelect($sql);
    $vCountry = $db_driverdetail[0]['vCountry'];
    $languageLabelsArr = getLanguageLabelsArr($languageCode, "1", $iServiceId);
    $lbl_all = $languageLabelsArr['LBL_ALL'];
    $ssql = "";
    if ($vCountry != "") {
        $iCountryId = get_value('country', 'iCountryId', 'vCountryCode', $vCountry, '', 'true');
        $sql = "SELECT * FROM location_master WHERE eStatus='Active' AND iCountryId = '" . $iCountryId . "' AND eFor = 'VehicleType'";
        $db_country = $obj->MySQLSelect($sql);
        $country_str = "-1";
        if (count($db_country) > 0) {
            for ($i = 0; $i < count($db_country); $i++) {
                $country_str .= "," . $db_country[$i]['iLocationId'];
            }
        }
        $ssql .= " AND iLocationid IN ($country_str) ";
    }
    $sql2 = "SELECT iVehicleTypeId, vVehicleType_" . $languageCode . " as vTitle,eFareType,eAllowQty,iMaxQty,fFixedFare,fPricePerHour,iLocationid,fMinHour from vehicle_type where iVehicleCategoryId in($iVehicleCategoryId) AND eStatus = 'Active' " . $ssql;
    $vehicleDetail = $obj->MySQLSelect($sql2);
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $db_driverdetail[0]['iDriverId'], '', 'true');
    if ($vCurrencyDriver == "" || $vCurrencyDriver == NULL) {
        $vCurrencyDriver = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $vCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyDriver);
    $vCurrencySymbol = $vCurrencyData[0]['vSymbol'];
    $vCurrencyRatio = $vCurrencyData[0]['Ratio'];
    $iParentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    if ($iParentId == 0) {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    //$iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId',$iDriverId,'','true');
    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $query = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX' ORDER BY iDriverVehicleId DESC LIMIT 0,1";
        $result = $obj->MySQLSelect($query);
        $iDriverVehicleId = $result[0]['iDriverVehicleId'];
    } else {
        $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    }
    /* Added By PJ for get pending services status */
    $sql = 'SELECT iVehicleCategoryId FROM driver_service_request WHERE iDriverId = "' . $iDriverId . '" ';
    $ReqServices = $obj->MySQLSelect($sql);
    $requestedServices = [];
    foreach ($ReqServices as $key => $ReqService) {
        $requestedServices[] = $ReqService['iVehicleCategoryId'];
    }
    /* END pending services status */
    $sql = "SELECT vCarType FROM `driver_vehicle` where iDriverId ='" . $iDriverId . "' AND iDriverVehicleId = '" . $iDriverVehicleId . "'";
    $db_vCarType = $obj->MySQLSelect($sql);
    if (count($db_vCarType) > 0) {
        $vehicle_service_id = explode(",", $db_vCarType[0]['vCarType']);
        //print_R($vehicle_service_id);die;
        for ($i = 0; $i < count($vehicleDetail); $i++) {
            $sql3 = "SELECT * FROM `service_pro_amount` where iDriverVehicleId ='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $vehicleDetail[$i]['iVehicleTypeId'] . "'";
            $db_serviceproviderid = $obj->MySQLSelect($sql3);
            if (count($db_serviceproviderid) > 0) {
                $vehicleDetail[$i]['fAmount'] = strval($db_serviceproviderid[0]['fAmount']);
            } else {
                if ($vehicleDetail[$i]['eFareType'] == "Hourly") {
                    $vehicleDetail[$i]['fAmount'] = strval($vehicleDetail[$i]['fPricePerHour']);
                } else {
                    $vehicleDetail[$i]['fAmount'] = strval($vehicleDetail[$i]['fFixedFare']);
                }
            }
            // $vehicleDetail[$i]['iDriverVehicleId']=$db_driverdetail[0]['iDriverVehicleId'];
            $fAmount = round($vehicleDetail[$i]['fAmount'] * $vCurrencyRatio, 2);
            $vehicleDetail[$i]['fAmount'] = strval($fAmount);
            $vehicleDetail[$i]['ePriceType'] = $ePriceType;
            $vehicleDetail[$i]['vCurrencySymbol'] = $vCurrencySymbol;
            $data_service[$i] = $vehicleDetail[$i];
            if (in_array($data_service[$i]['iVehicleTypeId'], $vehicle_service_id)) {
                $vehicleDetail[$i]['VehicleServiceStatus'] = 'true';
            } else {
                $vehicleDetail[$i]['VehicleServiceStatus'] = 'false';
            }
            /* Added By PJ for get pending services status */
            if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
                if (in_array($data_service[$i]['iVehicleTypeId'], $vehicle_service_id)) {
                    $vehicleDetail[$i]['eServiceRequest'] = 'Active';
                } else if (in_array($data_service[$i]['iVehicleTypeId'], $requestedServices)) {
                    $vehicleDetail[$i]['eServiceRequest'] = 'Pending';
                } else {
                    $vehicleDetail[$i]['eServiceRequest'] = 'Inactive';
                }
                $vehicleDetail[$i]['VehicleServiceId'] = $data_service[$i]['iVehicleTypeId'];
            }
            if ($vehicleDetail[$i]['iLocationid'] == "-1") {
                $vehicleDetail[$i]['SubTitle'] = $lbl_all;
            } else {
                $sql = "SELECT vLocationName FROM location_master WHERE iLocationId = '" . $vehicleDetail[$i]['iLocationid'] . "'";
                $locationname = $obj->MySQLSelect($sql);
                $vehicleDetail[$i]['SubTitle'] = $locationname[0]['vLocationName'];
            }
        }
    }
    if (count($vehicleDetail) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['ENABLE_DRIVER_SERVICE_REQUEST_MODULE'] = $ENABLE_DRIVER_SERVICE_REQUEST_MODULE ? $ENABLE_DRIVER_SERVICE_REQUEST_MODULE : 'Feature Not Avialable.';
        $returnArr['message'] = $vehicleDetail;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "UpdateDriverServiceAmount") {
    $iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? $_REQUEST['iDriverVehicleId'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    $fAmount = isset($_REQUEST['fAmount']) ? $_REQUEST['fAmount'] : '';
    if ($iDriverVehicleId == "" || $iDriverVehicleId == 0 || $iDriverVehicleId == NULL) {
        $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    }

    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $query = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX'";
        $result = $obj->MySQLSelect($query);
        $iDriverVehicleId = $result[0]['iDriverVehicleId'];
    }

    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId, '', 'true');
    if ($vCurrencyDriver == "" || $vCurrencyDriver == NULL) {
        $vCurrencyDriver = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $vCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyDriver);
    $vCurrencyRatio = $vCurrencyData[0]['Ratio'];
    $Amount = $fAmount / $vCurrencyRatio;
    $Amount = round($Amount, 2);
    $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
    $serviceProData = $obj->MySQLSelect($sqlServicePro);
    if (count($serviceProData) > 0) {
        $updateQuery = "UPDATE service_pro_amount set fAmount='" . $Amount . "' WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $id = $obj->sql_query($updateQuery);
    } else {
        $Data["iDriverVehicleId"] = $iDriverVehicleId;
        $Data["iVehicleTypeId"] = $iVehicleTypeId;
        $Data["fAmount"] = $Amount;
        $id = $obj->MySQLQueryPerform("service_pro_amount", $Data, 'insert');
    }
    if ($id) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_SERVICE_AMOUT_UPDATED";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
###########################################################################
##############################Update Driver Manage Timing #################################################################
if ($type == "UpdateDriverManageTiming") {
    global $generalobj, $tconfig;
    $iDriverTimingId = isset($_REQUEST['iDriverTimingId']) ? $_REQUEST['iDriverTimingId'] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vAvailableTimes = isset($_REQUEST["vAvailableTimes"]) ? $_REQUEST["vAvailableTimes"] : ''; // 4-5,5-6,7-8,11-12,14-15
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : ''; // 2017-10-18
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $vDay = date('l', strtotime($scheduleDate));
    $dAddedDate = @date("Y-m-d H:i:s");
    $vAvailableTimes = CheckAvailableTimes($vAvailableTimes); // Convert to 04-05,05-06,07-08,11-12,14-15
    $action = ($iDriverTimingId != '') ? 'Edit' : 'Add';
    $Data_Update_Timing['iDriverId'] = $iDriverId;
    $Data_Update_Timing['vDay'] = $vDay;
    $Data_Update_Timing['vAvailableTimes'] = $vAvailableTimes;
    $Data_Update_Timing['dAddedDate'] = $dAddedDate;
    $Data_Update_Timing['eStatus'] = $eStatus;
    if ($action == "Add") {
        $insertid = $obj->MySQLQueryPerform("driver_manage_timing", $Data_Update_Timing, 'insert');
    } else {
        $where = " iDriverTimingId = '" . $iDriverTimingId . "'";
        $insertid = $obj->MySQLQueryPerform("driver_manage_timing", $Data_Update_Timing, 'update', $where);
    }
    if ($insertid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = getDriverDetailInfo($iDriverId);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
##############################Update Driver Manage Timing Ends#################################################################
###########################Display Availability##########################################################
if ($type == "DisplayAvailability") {
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $vDay = isset($_REQUEST['vDay']) ? clean($_REQUEST['vDay']) : '';
    //Added By HJ On 02-09-2019 For Get Current Day Name If Day Not Found Start
    if ($vDay == "") {
        $dAddedDate = @date("Y-m-d");
        $vDay = @date("l", strtotime($dAddedDate));
        $returnArr['vDay'] = $vDay;
    }
    //Added By HJ On 02-09-2019 For Get Current Day Name If Day Not Found End
    $sql = "select * from `driver_manage_timing` where iDriverId = '" . $iDriverId . "' AND vDay LIKE '" . $vDay . "' ORDER BY iDriverTimingId DESC";
    $db_data = $obj->MySQLSelect($sql);
    if (count($db_data) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_AVAILABILITY_FOUND";
    }

    setDataResponse($returnArr);
}
###########################Display Availability End######################################################
###########################Add/Update Availability ##########################################################
if ($type == "UpdateAvailability") {
    global $generalobj, $tconfig;
    $iDriverTimingId = isset($_REQUEST['iDriverTimingId']) ? $_REQUEST['iDriverTimingId'] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vDay = isset($_REQUEST["vDay"]) ? $_REQUEST["vDay"] : '';
    $vAvailableTimes = isset($_REQUEST["vAvailableTimes"]) ? $_REQUEST["vAvailableTimes"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $dAddedDate = @date("Y-m-d H:i:s");
    $vAvailableTimes = CheckAvailableTimes($vAvailableTimes); // Convert to 04-05,05-06,07-08,11-12,14-15
    $sql = "select iDriverTimingId from `driver_manage_timing` where iDriverId = '" . $iDriverId . "' AND vDay LIKE '" . $vDay . "'";
    $db_data = $obj->MySQLSelect($sql);
    //$action = ($iDriverTimingId != '')?'Edit':'Add';
    if (count($db_data) > 0) {
        $action = "Edit";
        $iDriverTimingId = $db_data[0]['iDriverTimingId'];
    } else {
        $action = "Add";
    }
    $Data_driver_timing['iDriverId'] = $iDriverId;
    $Data_driver_timing['vDay'] = $vDay;
    $Data_driver_timing['vAvailableTimes'] = $vAvailableTimes;
    $Data_driver_timing['dAddedDate'] = $dAddedDate;
    $Data_driver_timing['eStatus'] = $eStatus;
    if ($action == "Add") {
        $insertid = $obj->MySQLQueryPerform("driver_manage_timing", $Data_driver_timing, 'insert');
        $TimingId = $insertid;
    } else {
        $where = " iDriverTimingId = '" . $iDriverTimingId . "'";
        $insertid = $obj->MySQLQueryPerform("driver_manage_timing", $Data_driver_timing, 'update', $where);
        $TimingId = $iDriverTimingId;
    }
    if ($insertid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['TimingId'] = $insertid;
        $returnArr['message'] = "LBL_TIMESLOT_ADD_SUCESS_MSG";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
###########################Display Driver Day Availability##########################################################
if ($type == "DisplayDriverDaysAvailability") {
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $sql = "select vDay from `driver_manage_timing` where iDriverId = '" . $iDriverId . "' AND  vAvailableTimes <> '' ORDER BY iDriverTimingId DESC";
    $db_data = $obj->MySQLSelect($sql);
    if (count($db_data) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_data;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_AVAILABILITY_FOUND";
    }

    setDataResponse($returnArr);
}
###########################Display Driver Day Availability Ends##########################################################
###########################Check  Schedule Booking Time Availability##########################################################
if ($type == "CheckScheduleTimeAvailability") {
    global $generalobj, $tconfig;
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $systemTimeZone = date_default_timezone_get();
    // echo "hererrrrr:::".$systemTimeZone;exit;
    $currentdate = date("Y-m-d H:i:s");
    $currentdate = converToTz($currentdate, $vTimeZone, $systemTimeZone);
    $sdate = explode(" ", $scheduleDate);
    $shour = explode("-", $sdate[1]);
    $shour1 = $shour[0];
    $shour2 = $shour[1];
    if ($shour1 == "12" && $shour2 == "01") {
        $shour1 = 00;
    }
    $scheduleDate = $sdate[0] . " " . $shour1 . ":00:00";
    $datediff = strtotime($scheduleDate) - strtotime($currentdate);
    if ($datediff > 3600) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_SCHEDULE_TIME_NOT_AVAILABLE";
    }

    setDataResponse($returnArr);
}
############################Check  Schedule Booking Time Availability Ends#####################################################
################################################UBERX Driver Update worklocation address, lat, long########################################################
if ($type == "UpdateDriverWorkLocationUFX") {

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vWorkLocationLatitude = isset($_REQUEST["vWorkLocationLatitude"]) ? $_REQUEST["vWorkLocationLatitude"] : '';
    $vWorkLocationLongitude = isset($_REQUEST["vWorkLocationLongitude"]) ? $_REQUEST["vWorkLocationLongitude"] : '';
    $vWorkLocation = isset($_REQUEST["vWorkLocation"]) ? $_REQUEST["vWorkLocation"] : '';

    $where = " iDriverId='$iDriverId'";
    $Data_update_driver['vWorkLocationLatitude'] = $vWorkLocationLatitude;
    $Data_update_driver['vWorkLocationLongitude'] = $vWorkLocationLongitude;
    $Data_update_driver['vWorkLocation'] = $vWorkLocation;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    if ($id) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
################################################UBERX Driver Update worklocation address, lat, long########################################################
################################################UBERX Get Driver worklocation address, lat, long, worklocation radius########################################################
if ($type == "getDriverWorkLocationUFX") {

    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $sql = "SELECT vWorkLocation,vWorkLocationLatitude,vWorkLocationLongitude,vWorkLocationRadius,eSelectWorkLocation FROM `register_driver` WHERE iDriverId = '" . $iDriverId . "'";
    $Data = $obj->MySQLSelect($sql);

    if (count($Data) > 0) {
        $vWorkLocationRadius = $Data[0]['vWorkLocationRadius'];
        $vCountryUnitDriver = getMemberCountryUnit($iDriverId, "Driver");
        $Data[0]['vCountryUnitDriver'] = $vCountryUnitDriver;
        if ($vCountryUnitDriver == "Miles") {
            $Data[0]['vWorkLocationRadius'] = round($vWorkLocationRadius * 0.6213711, 2); // convert miles to km
            $vWorkLocationRadius = $Data[0]['vWorkLocationRadius'];
        }

        $radiusArr = array(
            5,
            10,
            15
        );
        if (!in_array($vWorkLocationRadius, $radiusArr)) {
            array_push($radiusArr, $vWorkLocationRadius);
        }

        $radusArr = array();
        for ($i = 0; $i < count($radiusArr); $i++) {
            $radusArr[$i]['value'] = $radiusArr[$i];
            $radusArr[$i]['eUnit'] = $vCountryUnitDriver;
            $radusArr[$i]['eSelected'] = ($vWorkLocationRadius == $radiusArr[$i]) ? "Yes" : "No";
        }
        $Data[0]['RadiusList'] = $radusArr;
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
################################################UBERX Get Driver worklocation address, lat, long, worklocation radius########################################################
################################################UBERX Driver Update selection of worklocation 'Dynamic', 'Fixed'########################################################
if ($type == "UpdateDriverWorkLocationSelectionUFX") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $eSelectWorkLocation = isset($_REQUEST["eSelectWorkLocation"]) ? $_REQUEST['eSelectWorkLocation'] : 'Dynamic';
    $vWorkLocation = isset($_REQUEST["vWorkLocation"]) ? $_REQUEST['vWorkLocation'] : '';
    $vWorkLocationLatitude = isset($_REQUEST["vWorkLocationLatitude"]) ? $_REQUEST['vWorkLocationLatitude'] : '';
    $vWorkLocationLongitude = isset($_REQUEST["vWorkLocationLongitude"]) ? $_REQUEST['vWorkLocationLongitude'] : '';

    $where = " iDriverId = '$iDriverId'";
    $tableName = "register_driver";

    $Data_update_driver['eSelectWorkLocation'] = $eSelectWorkLocation;
    if ($vWorkLocation != "" && $vWorkLocationLatitude != "" && $vWorkLocationLongitude != "") {
        $Data_update_driver['vWorkLocation'] = $vWorkLocation;
        $Data_update_driver['vWorkLocationLatitude'] = $vWorkLocationLatitude;
        $Data_update_driver['vWorkLocationLongitude'] = $vWorkLocationLongitude;
    }
    $id = $obj->MySQLQueryPerform($tableName, $Data_update_driver, 'update', $where);
    if ($id > 0) {
        $returnArr['Action'] = "1";

        $returnArr['message'] = getDriverDetailInfo($iDriverId);
        $returnArr['message1'] = "LBL_WORKLOCATION_UPDATE_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
################################################UBERX Driver Update selection of worklocation 'Dynamic', 'Fixed'########################################################
##############################Update Radius ##########################################################
if ($type == "UpdateRadius") {
    global $generalobj, $tconfig, $LIST_DRIVER_LIMIT_BY_DISTANCE;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vWorkLocationRadius = isset($_REQUEST["vWorkLocationRadius"]) ? $_REQUEST["vWorkLocationRadius"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $Data_register_driver['vWorkLocationRadius'] = $vWorkLocationRadius;
    $eUnit = getMemberCountryUnit($iDriverId, "Driver");
    if ($eUnit == "Miles") {
        $Data_register_driver['vWorkLocationRadius'] = round($vWorkLocationRadius * 1.60934, 2); // convert miles to km
        $LIST_DRIVER_LIMIT_BY_DISTANCE = round($LIST_DRIVER_LIMIT_BY_DISTANCE * 0.621371, 2);
    } else {
        $Data_register_driver['vWorkLocationRadius'] = round($vWorkLocationRadius, 2); // convert miles to km
        $LIST_DRIVER_LIMIT_BY_DISTANCE = round($LIST_DRIVER_LIMIT_BY_DISTANCE, 2);
    }

    $where = " iDriverId = '" . $iDriverId . "'";
    $updateid = $obj->MySQLQueryPerform("register_driver", $Data_register_driver, 'update', $where);
    if ($updateid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['UpdateId'] = $iDriverId;
        $returnArr['message'] = getDriverDetailInfo($iDriverId);
        $returnArr['message1'] = "LBL_INFO_UPDATED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
##############################Update Radius  End##########################################################
################################################CheckPendingBooking UBERX########################################################
if ($type == "CheckPendingBooking") {
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';

    $sql_book = "SELECT dBooking_date from cab_booking WHERE iCabBookingId ='" . $iCabBookingId . "'";
    $checkbooking = $obj->MySQLSelect($sql_book);
    $dBooking_date = $checkbooking[0]['dBooking_date'];

    $sql = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $iDriverId . "' AND dBooking_date = '" . $dBooking_date . "' AND eStatus = 'Accepted' AND iCabBookingId != '" . $iCabBookingId . "'";
    $pendingacceptdriverbooking = $obj->MySQLSelect($sql);

    if (count($pendingacceptdriverbooking) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PENDING_PLUS_ACCEPT_BOOKING_AVAIL_TXT";
        $returnArr['message1'] = "Accept";
    } else {
        $sql = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $iDriverId . "' AND dBooking_date = '" . $dBooking_date . "' AND eStatus = 'Pending' AND iCabBookingId != '" . $iCabBookingId . "'";
        $pendingdriverbooking = $obj->MySQLSelect($sql);
        if (count($pendingdriverbooking) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PENDING_BOOKING_AVAIL_TXT";
            $returnArr['message1'] = "Pending";
        } else {
            $returnArr['Action'] = "1";
        }
    }


    setDataResponse($returnArr);
}
################################################CheckPendingBooking UBERX########################################################
if ($type == 'displaytripcharges') {
    $TripID = isset($_REQUEST["TripID"]) ? $_REQUEST["TripID"] : '';
    $destination_lat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
    $destination_lon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
    $iTripTimeId = isset($_REQUEST["iTripTimeId"]) ? $_REQUEST["iTripTimeId"] : '';
    //$ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");
    $where = " iTripId = '" . $TripID . "'";
    $data_update['tEndDate'] = @date("Y-m-d H:i:s");
    $data_update['tEndLat'] = $destination_lat;
    $data_update['tEndLong'] = $destination_lon;
    //$obj->MySQLQueryPerform("trips",$data_update,'update',$where);
    if ($iTripTimeId != "") {
        $where = " iTripTimeId = '$iTripTimeId'";
        $Data_update['dPauseTime'] = $data_update['tEndDate'];
        $Data_update['iTripId'] = $TripID;
        //$id = $obj->MySQLQueryPerform("trip_times",$Data_update,'update',$where);
    }

    $sql = "SELECT * from trips WHERE iTripId = '" . $TripID . "'";
    $tripData = $obj->MySQLSelect($sql);
    $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
    $iVehicleTypeId = $tripData[0]['iVehicleTypeId'];
    $fVisitFee = $tripData[0]['fVisitFee'];
    $startDate = $tripData[0]['tStartDate'];
    $endDateOfTrip = $tripData[0]['tEndDate'];
    $iQty = $tripData[0]['iQty'];
    $destination_lat = $tripData[0]['tEndLat'];
    $destination_lon = $tripData[0]['tEndLong'];
    //$endDateOfTrip=@date("Y-m-d H:i:s");
    /* $iVehicleCategoryId=get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId',$iVehicleTypeId,'','true');
      $iParentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId,'','true'); */
    $sql = "SELECT vc.iParentId from vehicle_category as vc LEFT JOIN vehicle_type as vt ON vc.iVehicleCategoryId=vt.iVehicleCategoryId WHERE vt.iVehicleTypeId = '" . $iVehicleTypeId . "'";
    $VehicleCategoryData = $obj->MySQLSelect($sql);
    $iParentId = $VehicleCategoryData[0]['iParentId'];
    if ($iParentId == 0) {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    //$ePriceType=get_value('vehicle_category', 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";

    if ($tripData[0]['eFareType'] == 'Hourly') {
        $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$TripID'";
        $db_tripTimes = $obj->MySQLSelect($sql22);

        $totalSec = 0;
        $iTripTimeId = '';
        foreach ($db_tripTimes as $dtT) {
            if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
            }
        }
        $totalTimeInMinutes_trip = @round(abs($totalSec) / 60, 2);
    } else {
        $totalTimeInMinutes_trip = @round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60, 2);
    }
    $totalHour = $totalTimeInMinutes_trip / 60;
    $tripDistance = calcluateTripDistance($tripId);
    $sourcePointLatitude = $tripData[0]['tStartLat'];
    $sourcePointLongitude = $tripData[0]['tStartLong'];
    if ($totalTimeInMinutes_trip <= 1) {
        $FinalDistance = $tripDistance;
    } else {
        $FinalDistance = checkDistanceWithGoogleDirections($tripDistance, $sourcePointLatitude, $sourcePointLongitude, $destination_lat, $destination_lon);
    }
    $tripDistance = $FinalDistance;
    $fPickUpPrice = $tripData[0]['fPickUpPrice'];
    $fNightPrice = $tripData[0]['fNightPrice'];
    $eFareType = get_value('trips', 'eFareType', 'iTripId', $TripID, '', 'true');
    $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
    $fAmount = 0;
    $Fare_data = getVehicleFareConfig("vehicle_type", $iVehicleTypeId);
    //echo "<pre>"; print_r($Fare_data); die;
    $fPricePerKM = getVehicleCountryUnit_PricePerKm($iVehicleTypeId, $Fare_data[0]['fPricePerKM']);
    /* $Minute_Fare = round($Fare_data[0]['fPricePerMin'] * $totalTimeInMinutes_trip * $surgePrice,2);
      $Distance_Fare = round($fPricePerKM * $tripDistance * $surgePrice,2);
      $iBaseFare = round($Fare_data[0]['iBaseFare'] * $surgePrice,2);
      $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare; */
    $Minute_Fare = $Fare_data[0]['fPricePerMin'] * $totalTimeInMinutes_trip;
    $Distance_Fare = $fPricePerKM * $tripDistance;
    $iBaseFare = $Fare_data[0]['iBaseFare'];
    $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare;
    $fSurgePriceDiff = (($total_fare * $surgePrice) - $total_fare);
    $total_fare = $total_fare + $fSurgePriceDiff;

    $iMinFare = round($Fare_data[0]['iMinFare'] * $surgePrice, 2);
    if ($iMinFare > $total_fare) {
        $total_fare = $iMinFare;
    }

    $fMinHour = $Fare_data[0]['fMinHour'];
    if ($totalHour > $fMinHour) {
        $miniminutes = $fMinHour * 60;
        $TripTimehours = $totalTimeInMinutes_trip / 60;
        $tothours = intval($TripTimehours);
        $extrahours = $TripTimehours - $tothours;
        $extraminutes = $extrahours * 60;
    }

    if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {

        $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $serviceProData = $obj->MySQLSelect($sqlServicePro);

        if (count($serviceProData) > 0) {
            $fAmount = $serviceProData[0]['fAmount'];
            if ($eFareType == "Fixed") {
                $fAmount = $fAmount * $iQty;
            } else if ($eFareType == "Hourly") {

                if ($totalHour > $fMinHour) {
                    $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                    $pricetimeslot = 60 / $fTimeSlot;
                    $pricepertimeslot = $fAmount / $pricetimeslot;
                    $fTimeSlotPrice = $pricepertimeslot;
                    $extraprice = 0;
                    if($fTimeSlot > 0){
                        $extratimeslot = ceil($extraminutes / $fTimeSlot);
                        $extraprice = $extratimeslot * $fTimeSlotPrice;
                    }else if($extraminutes > 0){
                        $extraprice = ($fAmount/60)*$extraminutes;
                    }
                    $fAmount = ($fAmount * $tothours) + $extraprice;
                } else {
                    $fAmount = $fAmount * $fMinHour;
                    //$fAmount = $fAmount * $totalHour;
                }
            } else {
                $fAmount = $total_fare;
            }
        } else {
            if ($eFareType == "Fixed") {
                $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
            } else if ($eFareType == "Hourly") {
                if ($totalHour > $fMinHour) {
                    $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                    $pricetimeslot = 60 / $fTimeSlot;
                    $pricepertimeslot = $Fare_data[0]['fPricePerHour'] / $pricetimeslot;
                    $fTimeSlotPrice = $pricepertimeslot;
                    $extraprice =0;
                    if($fTimeSlot > 0){
                        //$fTimeSlotPrice = $Fare_data[0]['fTimeSlotPrice'];
                        $extratimeslot = ceil($extraminutes / $fTimeSlot);
                        $extraprice = $extratimeslot * $fTimeSlotPrice;
                    }else if($extraminutes > 0){
                        $extraprice = ($Fare_data[0]['fPricePerHour']/60)*$extraminutes;
                    }
                    $fAmount = round((($Fare_data[0]['fPricePerHour'] * $tothours) + $extraprice), 2);
                } else {
                    $fAmount = round($Fare_data[0]['fPricePerHour'] * $fMinHour, 2);
                    // $fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour,2);
                }
            } else {
                $fAmount = $total_fare;
            }
        }
    } else {
        if ($eFareType == "Fixed") {
            $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
        } else if ($eFareType == "Hourly") {
            if ($totalHour > $fMinHour) {
                $fTimeSlot = $Fare_data[0]['fTimeSlot'];

                $pricetimeslot = 60 / $fTimeSlot;
                $pricepertimeslot = $Fare_data[0]['fPricePerHour'] / $pricetimeslot;
                $fTimeSlotPrice = $pricepertimeslot;
                $extraprice = 0;
                if($fTimeSlot > 0){
                    //$fTimeSlotPrice = $Fare_data[0]['fTimeSlotPrice'];
                    $extratimeslot = ceil($extraminutes / $fTimeSlot);
                    $extraprice = $extratimeslot * $fTimeSlotPrice;
                }else if($extraminutes > 0){
                    $extraprice = ($Fare_data[0]['fPricePerHour']/60)*$extraminutes;
                }
                $fAmount = round((($Fare_data[0]['fPricePerHour'] * $tothours) + $extraprice), 2);
            } else {
                $fAmount = round($Fare_data[0]['fPricePerHour'] * $fMinHour, 2);
                //$fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour,2);
            }
        } else {
            $fAmount = $total_fare;
        }
    }

    $final_display_charge = $fAmount + $fVisitFee;
    $returnArr['Action'] = "1";
    /* $vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'],'','true');
      $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
      $returnArr['message']=$currencySymbolRationDriver[0]['vSymbol']." ".number_format(round($final_display_charge * $currencySymbolRationDriver[0]['Ratio'],1),2); */
    //$currencySymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes','',true);
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'], '', 'true');
    $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
    $currencySymbol = $currencySymbolRationDriver[0]['vSymbol'];
    $currencyRationDriver = $currencySymbolRationDriver[0]['Ratio'];
    $final_display_charge = $final_display_charge * $currencyRationDriver;
    $final_display_charge = round($final_display_charge, 2);
    //$final_display_charge = formatNum($final_display_charge);
    $returnArr['message'] = $currencySymbol . ' ' . formatNum($final_display_charge);
    $returnArr['FareValue'] = $final_display_charge;
    $returnArr['CurrencySymbol'] = $currencySymbol;

    setDataResponse($returnArr);
}
###########################################################################
##############################Add/Update User Address End##########################################################
if ($type == "GetUserStats") {
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $currDate = date('Y-m-d H:i:s');
    $ssql1 = " AND dBooking_date > '" . $currDate . "'";
    $sql = "select count(iCabBookingId) as Total_Pending from `cab_booking` where iDriverId != '' AND eStatus = 'Pending' AND iDriverId = '" . $iDriverId . "' " . $ssql1 . " ORDER BY iCabBookingId DESC";
    $db_data_pending = $obj->MySQLSelect($sql);
    $sql1 = "select count(iCabBookingId) as Total_Upcoming from `cab_booking` where  iDriverId != '' AND ( eStatus = 'Accepted' || eStatus = 'Assign' ) AND iDriverId='" . $iDriverId . "' " . $ssql1 . " ORDER BY iCabBookingId DESC";
    $db_data_assign = $obj->MySQLSelect($sql1);
    $sql2 = "SELECT vWorkLocationRadius as Radius FROM register_driver where iDriverId = '" . $iDriverId . "' ORDER BY iDriverId DESC ";
    $db_data_radius = $obj->MySQLSelect($sql2);
    // $radius = ($db_data_radius[0] != "") ?  $db_data_radius[0] : array("Radius"=>"0");
    $eUnit = getMemberCountryUnit($iDriverId, "Driver");
    if ($eUnit == "Miles") {
        $db_data_radius[0]['Radius'] = round($db_data_radius[0]['Radius'] * 0.621371);
    }
    $returnArr['Action'] = "1";
    $returnArr['Pending_Count'] = (count($db_data_pending) > 0 && empty($db_data_pending) == false) ? $db_data_pending[0]['Total_Pending'] : 0;
    $returnArr['Upcoming_Count'] = (count($db_data_assign) > 0 && empty($db_data_assign) == false) ? $db_data_assign[0]['Total_Upcoming'] : 0;
    $returnArr['Radius'] = count($db_data_radius) > 0 ? $db_data_radius[0]['Radius'] : 0;
    /* if (count($db_data_pending) > 0 || count($db_data_assign) > 0 || count($db_data_radius) > 0) {
      $returnArr['Action'] = "1";
      $returnArr['Pending_Count'] = $db_data_pending[0]['Total_Pending'];
      $returnArr['Upcoming_Count'] = $db_data_assign[0]['Total_Upcoming'];
      $returnArr['Radius'] = $radius['Radius'];
      } else {
      $returnArr['Action'] = "0";
      $returnArr['Message'] = "LBL_NO_DATA_FOUND";
      } */

    setDataResponse($returnArr);
}
//Added By HJ On 24-01-2019 For Get Provider Images Data Start For UFX
if ($type == "getProviderImages") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST['iDriverId'] : '';
    $SelectedCabType = isset($_REQUEST["SelectedCabType"]) ? $_REQUEST["SelectedCabType"] : '';
    $getImages = array();
    if ($SERVICE_PROVIDER_FLOW == "Provider" && $SelectedCabType = "UberX") {
        $getImages = $obj->MySQLSelect("SELECT * FROM provider_images WHERE eStatus='Active' AND iDriverId='" . $iDriverId . "'");
        for ($p = 0; $p < count($getImages); $p++) {
            $getImages[$p]['vImage'] = $tconfig['tsite_upload_provider_image'] . '/' . $getImages[$p]['vImage'];
        }
    }//Provider_Images
    $returnArr['Action'] = "1";
    $returnArr['message'] = $getImages;
    setDataResponse($returnArr);
}
//Added By HJ On 24-01-2019 For Get Provider Images Data End For UFX
##############################Display user status End##########################################################
if ($type == "configProviderImages") {
    global $generalobj;
    $action_type = isset($_REQUEST["action_type"]) ? $_REQUEST["action_type"] : 'ADD';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $iImageId = isset($_REQUEST["iImageId"]) ? $_REQUEST["iImageId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    if ($action_type == "ADD") {
        /* Code for Upload StartImage of trip Start */
        if ($image_name != "") {
            $Photo_Gallery_folder = $tconfig['tsite_upload_provider_image_path'];
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "jpg,jpeg,gif,png");
            $vImageName = $vFile[0];
            $Data_update_images['vImage'] = $vImageName;
        }
        $Data_update_images['iDriverId'] = $iDriverId;
        $Data_update_images['tAddedDate'] = @date("Y-m-d H:i:s");

        /* Code for Upload StartImage of trip End */
        $id = $obj->MySQLQueryPerform("provider_images", $Data_update_images, 'insert');
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_IMAGE_UPLOAD_SUCCESS_NOTE";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else if ($action_type == "DELETE" && $iImageId != "") {
        $Photo_Gallery_folder = $tconfig['tsite_upload_provider_image_path'];

        $OldImageName = get_value('provider_images', 'vImage', 'iImageId', $iImageId, '', 'true');

        if ($OldImageName != '') {
            unlink($Photo_Gallery_folder . $OldImageName);
        }

        $sql = "DELETE FROM provider_images WHERE `iImageId`='" . $iImageId . "'";
        $id = $obj->sql_query($sql);

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_IMAGE_DELETE_SUCCESS_NOTE";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
//Added By HJ On 24-01-2019 For Get Service Category Data Start For UFX
if ($type == "getDriverServiceCategories") {
    //ini_set("display_errors", 1);
    //error_reporting(E_ALL);
    //echo "<pre>";print_r($_REQUEST);die;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST['iDriverId'] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST['iMemberId'] : '';
    $SelectedCabType = isset($_REQUEST["SelectedCabType"]) ? $_REQUEST["SelectedCabType"] : '';
    $vSelectedLatitude = isset($_REQUEST["vSelectedLatitude"]) ? $_REQUEST["vSelectedLatitude"] : '';
    $vSelectedLongitude = isset($_REQUEST["vSelectedLongitude"]) ? $_REQUEST["vSelectedLongitude"] : '';
    $parentId = isset($_REQUEST["parentId"]) ? $_REQUEST["parentId"] : 0;
    $SelectedVehicleTypeId = isset($_REQUEST["SelectedVehicleTypeId"]) ? $_REQUEST["SelectedVehicleTypeId"] : '';
    if ($iMemberId == "") {
        $iMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST['GeneralMemberId'] : '';
    }
    $categoryArr = array();
    if ($parentId > 0 || $SelectedVehicleTypeId != "") {
        if ($SERVICE_PROVIDER_FLOW == "Provider" && $SelectedCabType = "UberX") {
            //$getDriveVehicleType = $obj->MySQLSelect("SELECT GROUP_CONCAT(vCarType)as typeIds FROM driver_vehicle WHERE `iDriverId`='" . $iDriverId . "' AND eStatus='Active' AND vCarType != '' GROUP BY iDriverId");
            $getDriveVehicleType = $obj->MySQLSelect("SELECT GROUP_CONCAT(trim(',' FROM vCarType))as typeIds FROM driver_vehicle WHERE `iDriverId`='" . $iDriverId . "' AND eStatus='Active' AND vCarType != '' GROUP BY iDriverId");
            if (count($getDriveVehicleType) > 0) {
                $userData = $obj->MySQLSelect("SELECT ru.vCurrencyPassenger, ru.vLang, cu.vSymbol, cu.Ratio, co.eUnit FROM register_user as ru, currency as cu, country as co WHERE ru.iUserId='" . $iMemberId . "' AND cu.vName = ru.vCurrencyPassenger AND co.vCountryCode = ru.vCountry");
                $lang = "EN";
                $vSymbol = "$";
                $priceRatio = 1;
                if (count($userData) > 0) {
                    $lang = $userData[0]['vLang'];
                    $priceRatio = $userData[0]['Ratio'];
                    $vSymbol = $userData[0]['vSymbol'];
                }
                $getMainCat = $obj->MySQLSelect("SELECT iVehicleCategoryId,vCategory_" . $lang . " AS catName,vCategoryTitle_" . $lang . " as vCategoryTitle FROM vehicle_category WHERE eStatus='Active'");
                $cateNameArr = $cateTitleArr = array();
                for ($n = 0; $n < count($getMainCat); $n++) {
                    $mainCatId = $getMainCat[$n]['iVehicleCategoryId'];
                    $cateNameArr[$mainCatId] = $getMainCat[$n]['catName'];
                    $cateTitleArr[$mainCatId] = $getMainCat[$n]['vCategoryTitle'];
                }
                $typeIds = str_replace(",,", ",", trim($getDriveVehicleType[0]['typeIds'], ",")); // Added By HJ On 06-12-2019 For Solved issue #588 Of Sheet
                if (($parentId == "" || $parentId == 0) && $SelectedVehicleTypeId != "") {
                    $tmpSelectedTypeIdArr = explode(",", $SelectedVehicleTypeId);
                    if (count($tmpSelectedTypeIdArr) > 0) {
                        $parentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $tmpSelectedTypeIdArr[0], '', 'true');
                    }
                }
                $ssql_parentCategoryIds = "";
                $parentCategoryIds_sql = "SELECT GROUP_CONCAT(  `iVehicleCategoryId` ) AS parentCategoryIds FROM  `vehicle_category` WHERE `iParentId` = '" . $parentId . "'";
                $parentCategoryIdsArr = $obj->MySQLSelect($parentCategoryIds_sql);
                if (count($parentCategoryIdsArr) > 0) {
                    $ssql_parentCategoryIds = " AND vt.iVehicleCategoryId IN (" . $parentCategoryIdsArr[0]['parentCategoryIds'] . ")";
                }
                // $getTypeIds = $obj->MySQLSelect("SELECT vt.tTypeDesc,vt.iVehicleCategoryId,vt.vVehicleType_" . $lang . " AS vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.fMinHour, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM vehicle_type vt WHERE iVehicleCategoryId >0 AND iVehicleTypeId IN ($typeIds) AND eStatus='Active' ".$ssql_parentCategoryIds);
                $pickuplocationarr = array($vSelectedLatitude, $vSelectedLongitude);
                $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
                //print_r($GetVehicleIdfromGeoLocation);die;
                $getTypeIds = $obj->MySQLSelect("SELECT vt.iVehicleTypeId,vt.tTypeDesc,vt.iVehicleCategoryId,vt.vVehicleType_" . $lang . " AS vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.fMinHour, vt.iMaxQty, vt.iVehicleTypeId, vt.fTimeSlot,vt.fTimeSlotPrice, (SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentPriceType, IF((SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId)='Provider', (SELECT spa.fAmount from service_pro_amount as spa WHERE spa.iVehicleTypeId=vt.iVehicleTypeId AND spa.iDriverVehicleId =(SELECT dv.iDriverVehicleId FROM driver_vehicle as dv WHERE dv.iDriverId='" . $iDriverId . "' AND dv.eType='UberX')), NULL) as ProviderPrice FROM vehicle_type vt, vehicle_category as vc WHERE vt.iVehicleCategoryId >0 AND vt.iVehicleTypeId IN ($typeIds) AND vt.iLocationid IN ($GetVehicleIdfromGeoLocation) AND vt.eStatus='Active' AND vc.iVehicleCategoryId = vt.iVehicleCategoryId " . $ssql_parentCategoryIds . "  ORDER BY vt.iDisplayOrder ASC");
                //echo "TotalCOunt::" . count($getTypeIds) . "<BR/>";
                //echo "<PRE>";print_r($getTypeIds);exit;
                for ($c = 0; $c < count($getTypeIds); $c++) {
                    $catId = $getTypeIds[$c]['iVehicleCategoryId'];
                    $tTypeDescription = "";
                    $tTypeDesc = (array) json_decode($getTypeIds[$c]['tTypeDesc']);
                    if (isset($tTypeDesc['tTypeDesc_' . $lang]) && $tTypeDesc['tTypeDesc_' . $lang] != "") {
                        $tTypeDescription = $tTypeDesc['tTypeDesc_' . $lang];
                    }
                    ########################################## Check Fare Of Provider ##########################################
                    if ($getTypeIds[$c]['ProviderPrice'] != NULL) {
                        $getTypeIds[$c]['fFixedFare'] = $getTypeIds[$c]['ProviderPrice'];
                        $getTypeIds[$c]['fPricePerHour'] = $getTypeIds[$c]['ProviderPrice'];
                    }
                    if ($getTypeIds[$c]['fMinHour'] == 0) {
                        $getTypeIds[$c]['fMinHour'] = 1;
                    }
                    // $getTypeIds[$c]['fPricePerHour'] = $getTypeIds[$c]['fPricePerHour'] * $getTypeIds[$c]['fMinHour'];
                    unset($getTypeIds[$c]['ProviderPrice']);
                    unset($getTypeIds[$c]['ParentPriceType']);
                    ########################################## Check Fare Of Provider ##########################################
                    $mainCatName = $cateNameArr[$catId];
                    $vCategoryTitle = $cateTitleArr[$catId];
                    $subTypeArr = array();
                    $subTypeArr['iVehicleCategoryId'] = $catId;
                    $subTypeArr['vCategory'] = $mainCatName;
                    $subTypeArr['iVehicleTypeId'] = $getTypeIds[$c]['iVehicleTypeId'];
                    $subTypeArr['iPersonSize'] = $getTypeIds[$c]['iPersonSize'];
                    $subTypeArr['eType'] = $getTypeIds[$c]['eType'];
                    $subTypeArr['eIconType'] = $getTypeIds[$c]['eIconType'];
                    $subTypeArr['eAllowQty'] = $getTypeIds[$c]['eAllowQty'];
                    $subTypeArr['fMinHour'] = $getTypeIds[$c]['fMinHour'];
                    $subTypeArr['iMaxQty'] = $getTypeIds[$c]['iMaxQty'];
                    $subTypeArr['vVehicleType'] = $getTypeIds[$c]['vVehicleType'];
                    $subTypeArr['eFareType'] = $getTypeIds[$c]['eFareType'];
                    $fFixedFare_value = $generalobj->setTwoDecimalPoint($getTypeIds[$c]['fFixedFare'] * $priceRatio);
                    $subTypeArr['fFixedFare_value'] = $fFixedFare_value;
                    $subTypeArr['fFixedFare'] = $vSymbol . formatNum($fFixedFare_value);
                    $fPricePerHour_value = $generalobj->setTwoDecimalPoint($getTypeIds[$c]['fPricePerHour'] * $priceRatio);
                    $subTypeArr['fPricePerHour_value'] = $fPricePerHour_value;
                    $subTypeArr['fPricePerHour'] = $vSymbol . formatNum($fPricePerHour_value);

                    $fPricePerKM = getVehicleCountryUnit_PricePerKm($getTypeIds[$c]['iVehicleTypeId'], $getTypeIds[$c]['fPricePerKM'], $iDriverId, "Passenger");
                    $fPricePerKM = $generalobj->setTwoDecimalPoint($fPricePerKM * $priceRatio);
                    $subTypeArr['fPricePerKM'] = $vSymbol . formatNum($fPricePerKM);
                    $fPricePerMin = $generalobj->setTwoDecimalPoint($getTypeIds[$c]['fPricePerMin'] * $priceRatio);
                    $subTypeArr['fPricePerMin'] = $vSymbol . formatNum($fPricePerMin);
                    $iBaseFare = $generalobj->setTwoDecimalPoint($getTypeIds[$c]['iBaseFare'] * $priceRatio);
                    $subTypeArr['iBaseFare'] = $vSymbol . formatNum($iBaseFare);
                    $fCommision = $generalobj->setTwoDecimalPoint($getTypeIds[$c]['fCommision'] * $priceRatio);
                    $subTypeArr['fCommision'] = $vSymbol . formatNum($fCommision);
                    $iMinFare = $generalobj->setTwoDecimalPoint($getTypeIds[$c]['iMinFare'] * $priceRatio);
                    $subTypeArr['iMinFare'] = $vSymbol . formatNum($iMinFare);
                    $subTypeArr['vSymbol'] = $vSymbol;
                    $subTypeArr['vCategoryTitle'] = $vCategoryTitle;
                    $subTypeArr['vCategoryDesc'] = $tTypeDescription;
                    $subTypeArr['vRating'] = "0.00";
                    //$subTypeArr['vLogo'] = $Data[$j]['vVehicleTypeImage'];
                    //$subTypeArr['vImage'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $subTypeArr['iVehicleTypeId'] . '/android/' . $getTypeIds[$c]['vVehicleTypeImage'];
                    $categoryArr[$catId]['vCategory'] = $mainCatName;
                    $categoryArr[$catId]['iVehicleCategoryId'] = $catId;
                    $categoryArr[$catId]['SubCategories'][] = $subTypeArr;
                }
            }
        }
    }
    //echo "<pre>";print_r($categoryArr);die;
    $categoryArr = array_values($categoryArr);
    $returnArr['Action'] = "1";
    $returnArr['message'] = $categoryArr;
    setDataResponse($returnArr);
}
//Added By HJ On 24-01-2019 For Get Service Category Data Start For UFX
//Added By HJ On 25-01-2019 For Get Service Details Start For UFX
if ($type == "getVehicleTypeDetails") {
    //ini_set("display_errors", 1);
    //error_reporting(E_ALL);
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST['iDriverId'] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST['iMemberId'] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST['iVehicleTypeId'] : '';
    $SelectedCabType = isset($_REQUEST["SelectedCabType"]) ? $_REQUEST["SelectedCabType"] : '';

    if ($iMemberId == "") {
        $iMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST['GeneralMemberId'] : '';
    }

    if ($SERVICE_PROVIDER_FLOW == "Provider" && $SelectedCabType = "UberX") {

        $userData = $obj->MySQLSelect("SELECT ru.vCurrencyPassenger, ru.vLang, cu.vSymbol, cu.Ratio, co.eUnit FROM register_user as ru, currency as cu, country as co WHERE ru.iUserId='" . $iMemberId . "' AND cu.vName = ru.vCurrencyPassenger AND co.vCountryCode = ru.vCountry");

        $lang = $userData[0]['vLang'];
        $priceRatio = $userData[0]['Ratio'];
        $vSymbol = $userData[0]['vSymbol'];

        $languageLabelsArr = getLanguageLabelsArr($lang, "1");

        /* $getVehicleTypeData = $obj->MySQLSelect("SELECT vt.iVehicleTypeId,vt.tTypeDesc,vt.iVehicleCategoryId,vt.vVehicleType_" . $lang . " AS vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.fMinHour, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare,vt.fTimeSlot,vt.fTimeSlotPrice, (SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentPriceType, IF(ParentPriceType='Provider', (SELECT spa.fAmount from service_pro_amount as spa WHERE spa.iVehicleTypeId=vt.iVehicleTypeId AND spa.iDriverVehicleId =4), NULL) as ProviderPrice FROM vehicle_type vt, vehicle_category as vc WHERE vt.iVehicleCategoryId >0 AND vt.iVehicleTypeId='" . $iVehicleTypeId . "' AND vt.eStatus='Active' AND vc.iVehicleCategoryId = vt.iVehicleCategoryId"); */
        $getVehicleTypeData = $obj->MySQLSelect("SELECT vt.iVehicleTypeId,vt.tTypeDesc,vt.iVehicleCategoryId,vt.vVehicleType_" . $lang . " AS vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.fMinHour, vt.iMaxQty, vt.iVehicleTypeId, vt.fTimeSlot,vt.fTimeSlotPrice, (SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as ParentPriceType, IF((SELECT vcs.ePriceType FROM vehicle_category as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId)='Provider', (SELECT spa.fAmount from service_pro_amount as spa WHERE spa.iVehicleTypeId=vt.iVehicleTypeId AND spa.iDriverVehicleId =(SELECT dv.iDriverVehicleId FROM driver_vehicle as dv WHERE dv.iDriverId='" . $iDriverId . "' AND dv.eType='UberX')), NULL) as ProviderPrice, IF(vt.iLocationid != -1, (SELECT co.eUnit FROM country as co, location_master as lm WHERE co.iCountryId = lm.iCountryId AND lm.iLocationid = vt.iLocationid), '" . $DEFAULT_DISTANCE_UNIT . "') as LocationUnit FROM vehicle_type vt, vehicle_category as vc WHERE vt.iVehicleCategoryId >0 AND vt.iVehicleTypeId='" . $iVehicleTypeId . "' AND vt.eStatus='Active' AND vc.iVehicleCategoryId = vt.iVehicleCategoryId");


        if (count($getVehicleTypeData) > 0) {
            for ($r = 0; $r < count($getVehicleTypeData); $r++) {
                $catId = $getVehicleTypeData[$r]['iVehicleCategoryId'];
                /* echo "<pre>";
                  print_r($getVehicleTypeData);exit; */
                ########################################## Check Fare Of Provider ##########################################
                if ($getVehicleTypeData[$r]['ProviderPrice'] != NULL) {
                    $getVehicleTypeData[$r]['fFixedFare'] = $getVehicleTypeData[$r]['ProviderPrice'];
                    $getVehicleTypeData[$r]['fPricePerHour'] = $getVehicleTypeData[$r]['ProviderPrice'];
                }
                unset($getVehicleTypeData[$r]['ProviderPrice']);
                unset($getVehicleTypeData[$r]['ParentPriceType']);
                ########################################## Check Fare Of Provider ##########################################

                $tTypeDescription = "";
                $tTypeDesc = (array) json_decode($getVehicleTypeData[$r]['tTypeDesc']);
                if (isset($tTypeDesc['tTypeDesc_' . $lang]) && $tTypeDesc['tTypeDesc_' . $lang] != "") {
                    $tTypeDescription = $tTypeDesc['tTypeDesc_' . $lang];
                }

                $fFixedFare_value = $generalobj->setTwoDecimalPoint($getVehicleTypeData[$r]['fFixedFare'] * $priceRatio);
                $getVehicleTypeData[$r]['fFixedFare_value'] = $fFixedFare_value;
                $getVehicleTypeData[$r]['fFixedFare'] = $vSymbol . formatNum($fFixedFare_value);
                if ($getVehicleTypeData[$r]['fMinHour'] == 0) {
                    $getVehicleTypeData[$r]['fMinHour'] = 1;
                }
                $getVehicleTypeData[$r]['fPricePerHourOrig'] = $getVehicleTypeData[$r]['fPricePerHour'];
                $getVehicleTypeData[$r]['fPricePerHour'] = $getVehicleTypeData[$r]['fPricePerHour'] * $getVehicleTypeData[$r]['fMinHour'];
                $fPricePerHour_value = $generalobj->setTwoDecimalPoint($getVehicleTypeData[$r]['fPricePerHour'] * $priceRatio);
                $fPricePerHourOrig_value = $generalobj->setTwoDecimalPoint($getVehicleTypeData[$r]['fPricePerHourOrig'] * $priceRatio);
                $getVehicleTypeData[$r]['fPricePerHour_value'] = $fPricePerHour_value;
                $getVehicleTypeData[$r]['fPricePerHour'] = $vSymbol . formatNum($fPricePerHour_value);
                $getVehicleTypeData[$r]['fPricePerHourOrig'] = $vSymbol . formatNum($fPricePerHourOrig_value);

                // echo "Unit:".$getVehicleTypeData[$r]['LocationUnit'];exit;
                if ($userData[0]['eUnit'] != "KMs" && $getVehicleTypeData[$r]['LocationUnit'] == "KMs") {
                    $getVehicleTypeData[$r]['fPricePerKM'] = $getVehicleTypeData[$r]['fPricePerKM'] * 0.621371;
                } else if ($userData[0]['eUnit'] == "KMs" && $getVehicleTypeData[$r]['LocationUnit'] == "Miles") {
                    $getVehicleTypeData[$r]['fPricePerKM'] = $getVehicleTypeData[$r]['fPricePerKM'] * 1.60934;
                }

                $fPricePerKM = $getVehicleTypeData[$r]['fPricePerKM'];
                $fPricePerKM = $generalobj->setTwoDecimalPoint($fPricePerKM * $priceRatio);
                $getVehicleTypeData[$r]['fPricePerKM'] = $vSymbol . formatNum($fPricePerKM);
                $fPricePerMin = $generalobj->setTwoDecimalPoint($getVehicleTypeData[$r]['fPricePerMin'] * $priceRatio);
                $getVehicleTypeData[$r]['fPricePerMin'] = $vSymbol . formatNum($fPricePerMin);
                $iBaseFare = $generalobj->setTwoDecimalPoint($getVehicleTypeData[$r]['iBaseFare'] * $priceRatio);
                $getVehicleTypeData[$r]['iBaseFare'] = $vSymbol . formatNum($iBaseFare);
                $fCommision = $generalobj->setTwoDecimalPoint($getVehicleTypeData[$r]['fCommision'] * $priceRatio);
                $getVehicleTypeData[$r]['fCommision'] = $vSymbol . formatNum($fCommision);
                $iMinFare = $generalobj->setTwoDecimalPoint($getVehicleTypeData[$r]['iMinFare'] * $priceRatio);
                $getVehicleTypeData[$r]['iMinFare'] = $vSymbol . formatNum($iMinFare);
                $getVehicleTypeData[$r]['vSymbol'] = $vSymbol;
                $getVehicleTypeData[$r]['vCategoryDesc'] = $tTypeDescription;
                $getVehicleTypeData[$r]['vRating'] = "0.00";
                unset($getVehicleTypeData[$r]['tTypeDesc']);
                $tripFareDetailsArr = array();
                $i = 0;
                if ($getVehicleTypeData[$r]['eFareType'] == "Regular") {
                    $tripFareDetailsArr[0][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vSymbol . " " . formatNum($iBaseFare);
                    $tripFareDetailsArr[1][$languageLabelsArr['LBL_PRICE_PER_MINUTE']] = $vSymbol . " " . formatNum($fPricePerMin);
                    $tripFareDetailsArr[2][$languageLabelsArr[$userData[0]['eUnit'] == "KMs" ? 'LBL_PRICE_PER_KM' : 'LBL_PRICE_PER_MILES']] = $vSymbol . " " . formatNum($fPricePerKM);
                    $tripFareDetailsArr[3]['eDisplaySeperator'] = "Yes";
                    $tripFareDetailsArr[4][$languageLabelsArr['LBL_ESTIMATED_CHARGE']] = $vSymbol . " " . formatNum($iBaseFare);
                } else if ($getVehicleTypeData[$r]['eFareType'] == "Hourly") {
                    $tmp_min_hour_charges = $vSymbol . " " . formatNum($fPricePerHour_value);
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_SERVICE_CHARGE_PER_HOUR']] = $getVehicleTypeData[$r]['fPricePerHourOrig'];
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_MINIMUM'] . " " . $languageLabelsArr['LBL_HOUR']] = $getVehicleTypeData[$r]['fMinHour'];
                    // $tripFareDetailsArr[1]['Extra Price Slot (' . $getVehicleTypeData[$r]['fTimeSlot'] . ' Min)'] = $vSymbol . " " . formatNum($getVehicleTypeData[$r]['fTimeSlotPrice']);
                    // $tripFareDetailsArr[2][$languageLabelsArr['LBL_BASE_FARE_SMALL_TXT']] = $vSymbol . " " . formatNum($iBaseFare);
                    $tripFareDetailsArr[]['eDisplaySeperator'] = "Yes";
                    // $tripFareDetailsArr[3][$languageLabelsArr['LBL_MIN_CHARGE_TXT']] = $vSymbol . " " . formatNum($iBaseFare + $iMinFare);
                    $tripFareDetailsArr[][$languageLabelsArr['LBL_ESTIMATED_CHARGE']] = $tmp_min_hour_charges;
                } else {
                    $tripFareDetailsArr[0][$languageLabelsArr['LBL_SERVICE_CHARGE']] = $vSymbol . " " . formatNum($fFixedFare_value);
                }
                $getVehicleTypeData[$r]['fareDetails'] = $tripFareDetailsArr;

                //echo "<pre>";print_r($getVehicleTypeData);die;
            }
            $getVehicleTypeData = $getVehicleTypeData[0];
        }

        if (($getVehicleTypeData['eAllowQty'] == "Yes" && $getVehicleTypeData['iMaxQty'] < 2) || $getVehicleTypeData['eFareType'] == "Regular" || $getVehicleTypeData['eFareType'] == "Hourly") {
            $getVehicleTypeData['eAllowQty'] = "No";
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $getVehicleTypeData;
        setDataResponse($returnArr);
    }
}
//Added By HJ On 25-01-2019 For Get Service Details End For UFX
//Added By HJ On 31-01-2019 For Get Vehicle Type Fare Details Start
if ($type == "getVehicleTypeFareDetails") {
    //echo "<pre>";
    //ini_set("display_errors", 1);
    //error_reporting(E_ALL);
    $OrderDetails = isset($_REQUEST['OrderDetails']) ? $_REQUEST['OrderDetails'] : array();
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $couponCode = isset($_REQUEST['vCouponCode']) ? clean($_REQUEST['vCouponCode']) : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : 0;
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '';
    $tripFareDetailsArr = array();
    //print_r($_REQUEST);die;
    /* if ($_REQUEST["CarWash"] == "1") { */
    $fareDetails = getVehicleTypeFareDetails();
    /* echo "<PRE>";
      print_r($fareDetails);exit;
      } else {
      $fareDetails = getVehicleTypeFareDetails($OrderDetails, $iMemberId, $couponCode, $UserType);
      } */

    // $fareDetails = getVehicleTypeFareDetails1($OrderDetails, $iMemberId, $couponCode, $UserType);
    if (isset($fareDetails['tripFareDetailsArr'])) {
        $tripFareDetailsArr = $fareDetails['tripFareDetailsArr'];
    }
    if (isset($fareDetails['vehiclePriceTypeArrItems'])) {
        $vehiclePriceTypeArrItems = $fareDetails['vehiclePriceTypeArrItems'];
    }
    if (isset($fareDetails['vehiclePriceTypeArrCubex'])) {
        $vehiclePriceTypeArrCubex = $fareDetails['vehiclePriceTypeArrCubex'];
    }
    $totalAddressCount = 0;
    $vServiceFullAddress = $vServiceAddressLatitude = $vServiceAddressLongitude = "";
    if ($iMemberId > 0) {
        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
        $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

        $ssql_address = "";
        if ($iUserAddressId != "") {
            $ssql_address = " AND iUserAddressId = " . $iUserAddressId;
        }
        if (!empty($iUserAddressId)) {
            $sql = "SELECT iUserAddressId FROM `user_address` WHERE iUserAddressId = '" . $iUserAddressId . "' AND eStatus='Active'";
            $data_user_address_data = $obj->MySQLSelect($sql);
            if (empty($data_user_address_data) || count($data_user_address_data) == 0) {
                $iUserAddressId = "";
            }
        }

        $getAddressCount = $obj->MySQLSelect("SELECT iUserAddressId,vServiceAddress,vAddressType,vBuildingNo,vLandmark,vLatitude,vLongitude FROM `user_address` WHERE iUserId='" . $iMemberId . "' AND eStatus='Active' " . $ssql_address . " AND vLatitude != '' AND vLongitude != '' AND vServiceAddress != '' ORDER BY iUserAddressId DESC");
        //echo "<pre>";
        //print_R($getAddressCount);die;
        if ($Check_Driver_UFX == "No") {
            $ssql_available .= " AND vAvailability = 'Available' AND vTripStatus != 'Active' AND tLocationUpdateDate > '$str_date' ";
        }
        $getLocaionData = $obj->MySQLSelect("SELECT eSelectWorkLocation,vLatitude,vLongitude,vWorkLocationLatitude,vWorkLocationLongitude,vWorkLocationRadius,vAvailability,vTripStatus,tLocationUpdateDate, eEnableServiceAtLocation FROM register_driver WHERE iDriverId='" . $iDriverId . "'");

        $eEnableServiceAtLocation = $getLocaionData[0]['eEnableServiceAtLocation'];

        $startDate = date("Y-m-d H:i:s");
        $isAvailabel = "No";
        for ($e = 0; $e < count($getLocaionData); $e++) {
            $vAvailability = $getLocaionData[$e]['vAvailability'];
            $vTripStatus = $getLocaionData[$e]['vTripStatus'];
            $tLocationUpdateDate = $getLocaionData[$e]['tLocationUpdateDate'];
            //echo $tLocationUpdateDate . " > " . $startDate . "<br>";
            if ($vAvailability == "Available" && $vTripStatus != "Active" && $tLocationUpdateDate > $str_date) {
                $isAvailabel = "Yes";
            }
        }
        if (count($getAddressCount) > 0) {
            $totalAddressCount = count($getAddressCount);
            if ($SERVICE_PROVIDER_FLOW == "Provider" && $iDriverId > 0) {
                if (count($getLocaionData) > 0) {
                    $vLatitude = $getLocaionData[0]['vLatitude'];
                    $vLongitude = $getLocaionData[0]['vLongitude'];
                    $eSelectWorkLocation = $getLocaionData[0]['eSelectWorkLocation'];
                    $vWorkLocationRadius = $RESTRICTION_KM_NEAREST_TAXI;
                    if (isset($getLocaionData[0]['vWorkLocationRadius']) && $getLocaionData[0]['vWorkLocationRadius'] > 0) {
                        $vWorkLocationRadius = $getLocaionData[0]['vWorkLocationRadius'];
                    }
                    if ($eSelectWorkLocation == "Fixed") {
                        $vLatitude = $getLocaionData[0]['vWorkLocationLatitude'];
                        $vLongitude = $getLocaionData[0]['vWorkLocationLongitude'];
                    }
                }
                $isRemoveAddressFromList = $addressArr = array();
                for ($r = 0; $r < count($getAddressCount); $r++) {
                    $userLat = $getAddressCount[$r]['vLatitude'];
                    $userLang = $getAddressCount[$r]['vLongitude'];
                    $distance = distanceByLocation($vLatitude, $vLongitude, $userLat, $userLang, "K");
                    if ($distance <= $vWorkLocationRadius) {
                        $isRemoveAddressFromList[] = 1;
                        $addressArr[] = $getAddressCount[$r];
                    }
                }
                if (in_array(1, $isRemoveAddressFromList)) {
                    $getAddressCount[$r]['eLocatonAvailable'] = $isRemoveAddressFromList;
                } else {
                    $getAddressCount = array();
                    //$totalAddressCount = 0; //commented bc when distance is greater than locationradius at that time if 0 given then no data in app, so commented so in app disable address are shown
                }
            }
            if (count($addressArr) > 0) {
                $getAddressCount = $addressArr;
            }
            if (count($getAddressCount) > 0) {
                //$totalAddressCount = $getAddressCount[0]['Total'];
                $iUserAddressId = $getAddressCount[0]['iUserAddressId'];
                $vServiceAddress = trim($getAddressCount[0]['vServiceAddress']);
                $vServiceAddressLatitude = trim($getAddressCount[0]['vLatitude']);
                $vServiceAddressLongitude = trim($getAddressCount[0]['vLongitude']);
                $vAddressType = trim($getAddressCount[0]['vAddressType']);
                $vBuildingNo = trim($getAddressCount[0]['vBuildingNo']);
                $vLandmark = trim($getAddressCount[0]['vLandmark']);
                $vServiceFullAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
                $vServiceFullAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
                $vServiceFullAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
                $vServiceFullAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
            }
        }
    }

    $returnArr['iUserAddressId'] = $iUserAddressId;
    $returnArr['vServiceAddress'] = $vServiceFullAddress;
    $returnArr['vServiceAddressLatitude'] = $vServiceAddressLatitude;
    $returnArr['vServiceAddressLongitude'] = $vServiceAddressLongitude;

    $returnArr['eEnableServiceAtProviderLocation'] = $fareDetails['eFareTypeServices'] == "Regular" ? "No" : $eEnableServiceAtLocation;
    if ($PROVIDER_AVAIL_LOC_CUSTOMIZE == 'No' && $returnArr['eEnableServiceAtProviderLocation'] != 'No') {
        $returnArr['eEnableServiceAtProviderLocation'] = 'No';
    }
    $returnArr['totalAddressCount'] = $totalAddressCount;
    $returnArr['vAvailability'] = $isAvailabel;
    $returnArr['vScheduleAvailability'] = isProviderEligibleForScheduleJob($iDriverId) == false ? "No" : "Yes";
    $returnArr['Action'] = "1";
    $returnArr['message'] = $tripFareDetailsArr;
    $returnArr['items'] = $vehiclePriceTypeArrItems;
    $returnArr['vehiclePriceTypeArrCubex'] = $vehiclePriceTypeArrCubex;

    //$returnArr['eEnableServiceAtProviderLocation'] = 'Yes';
    //print_r($returnArr);die;
    setDataResponse($returnArr);
}
//Added By HJ On 31-01-2019 For Get Vehicle Type Fare Details End
//Added By HJ On 01-02-2019 For Get Driver Special Instruction Start
if ($type == "getSpecialInstructionData") {

    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';

    if ($iTripId > 0) {
        $tableName = "trips";
        $whereCond = "iTripId ='" . $iTripId . "'";
    } else if ($iCabBookingId > 0) {
        $tableName = "cab_booking";
        $whereCond = "iCabBookingId ='" . $iCabBookingId . "'";
    } else {
        $tableName = "cab_request_now";
        $whereCond = "iCabRequestId ='" . $iCabRequestId . "'";
    }


    $lang = "";
    if ($UserType == "Driver") {
        $lang = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $lang = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    if ($lang == "") {
        $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $getData = $obj->MySQLSelect("SELECT tVehicleTypeFareData, tVehicleTypeData FROM " . $tableName . " WHERE $whereCond");
    $instructionArr = $vehicleTypeNameArr = $typeQtyArr = $typeNameArr = array();
    $iVehicleTypeIds = "";
    if (count($getData) > 0) {
		$getData[0]['tVehicleTypeFareData'] = preg_replace('/[[:cntrl:]]/', '\r\n', $getData[0]['tVehicleTypeFareData']);
        $tVehicleTypeFareData = (array) (json_decode($getData[0]['tVehicleTypeFareData']));
        $tVehicleTypeFareData = (array) $tVehicleTypeFareData['FareData'];
        //$tVehicleTypeData = (array) (json_decode($getData[0]['tVehicleTypeData']));
        //$k = preg_replace('/\r|\n/','\n',trim($Data_cab_request[0]['tVehicleTypeData']));
        $replacedata = preg_replace('/[[:cntrl:]]/', '\r\n', $getData[0]['tVehicleTypeData']);//apply this when from app enter key is used in special instruction
        $tVehicleTypeData = (array) (json_decode($replacedata));
        //Added By HJ On 10-12-2019 For Get User Comment From json Data Start
        if (isset($tVehicleTypeData[0]) && $tVehicleTypeData[0] != "") {
            $isJson = isJsonText($tVehicleTypeData[0]);
            if ($isJson > 0) {
                $tVehicleTypeData = (array) json_decode($tVehicleTypeData[0]);
            }
        }
        //Added By HJ On 10-12-2019 For Get User Comment From json Data End
        for ($h = 0; $h < count($tVehicleTypeData); $h++) {
            $typeQtyArr[$tVehicleTypeData[$h]->iVehicleTypeId] = $tVehicleTypeData[$h];
        }
        //echo "<pre>";print_r($typeQtyArr);die;
        $iVehicleTypeIds_str = "";
        for ($fd = 0; $fd < count($tVehicleTypeFareData); $fd++) {
            $iVehicleTypeIds_str = $iVehicleTypeIds_str == "" ? $tVehicleTypeFareData[$fd]->id : $iVehicleTypeIds_str . "," . $tVehicleTypeFareData[$fd]->id;
        }
		
		if((empty($tVehicleTypeFareData) || count($tVehicleTypeFareData) > 0) && !empty($getData[0]['iVehicleTypeId']) && empty($iVehicleTypeIds_str)){
			$iVehicleTypeIds_str = $getData[0]['iVehicleTypeId'];
		}
		
        $sql_vehicleTypeNames = "SELECT vt.vVehicleType_" . $lang . " as vVehicleType,iVehicleTypeId FROM vehicle_type as vt WHERE vt.iVehicleTypeId IN ($iVehicleTypeIds_str)";
        $data_vehicleTypeNames = $obj->MySQLSelect($sql_vehicleTypeNames);
        for ($k = 0; $k < count($data_vehicleTypeNames); $k++) {
            $typeNameArr[$data_vehicleTypeNames[$k]['iVehicleTypeId']] = $data_vehicleTypeNames[$k]['vVehicleType'];
        }
        //echo "<pre>";print_r($typeNameArr);die;
        for ($t = 0; $t < count($tVehicleTypeData); $t++) {
            $iVehicleTypeIds .= "," . $tVehicleTypeData[$t]->iVehicleTypeId;
            $commentDataArr = array();
            $qtyType = 0;
            $vTypeName = "";
            if (isset($typeQtyArr[$tVehicleTypeData[$t]->iVehicleTypeId])) {
                //print_r($typeQtyArr[$tVehicleTypeData[$t]->iVehicleTypeId]->fVehicleTypeQty);die;
                $qtyType = $typeQtyArr[$tVehicleTypeData[$t]->iVehicleTypeId]->fVehicleTypeQty;
            }
            if (isset($typeNameArr[$tVehicleTypeData[$t]->iVehicleTypeId])) {
                $vTypeName = $typeNameArr[$tVehicleTypeData[$t]->iVehicleTypeId];
            }
            $commentDataArr['iVehicleTypeId'] = $tVehicleTypeData[$t]->iVehicleTypeId;
            $commentDataArr['title'] = $vTypeName;
            for ($fd = 0; $fd < count($tVehicleTypeFareData); $fd++) {
                if ($tVehicleTypeData[$t]->iVehicleTypeId == $tVehicleTypeFareData[$fd]->id) {
                    if ($tVehicleTypeFareData[$fd]->eAllowQty == "Yes") {
                        $commentDataArr['Qty'] = "x" . $qtyType;
                    } else {
                        $commentDataArr['Qty'] = "";
                    }
                    break;
                }
            }
            $commentDataArr['comment'] = $tVehicleTypeData[$t]->tUserComment;
            $instructionArr[] = $commentDataArr;
        }
    }
    $returnArr['Action'] = "1";
    $returnArr['message'] = $instructionArr;
    //print_r($returnArr);die;
    setDataResponse($returnArr);
    //print_r($instructionArr);die;
}

//Added By HJ On 01-02-2019 For Get Driver Special Instruction End
function isJsonText($text_str) {
    //Commented By HJ On 08-11-2019 Because It's Not Work Proper For Check Json String Start
    //json_decode($text_str);
    //return (json_last_error() == JSON_ERROR_NONE);
    //Commented By HJ On 08-11-2019 Because It's Not Work Proper For Check Json String End
    return is_string($text_str) && is_array(json_decode($text_str, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false; // Added By HJ On 08-11-2019 After Comment Above
}

//Added By HJ On 05-02-2019 For Get Driver Availability For Later Booking Service Start
if ($type == "getDriverAvailability") {
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : '';
    $getAvalabilityData = array();
    if ($iDriverId > 0) {
        $getAvalabilityData = $obj->MySQLSelect("SELECT vDay,iDriverTimingId,vAvailableTimes FROM driver_manage_timing WHERE eStatus='Active' AND iDriverId='" . $iDriverId . "'");
    }
    $returnArr['Action'] = "1";
    $returnArr['message'] = $getAvalabilityData;
    setDataResponse($returnArr);
}

//Added By HJ On 05-02-2019 For Get Driver Availability For Later Booking Service End
if ($type == "getProviderServiceDescription") {
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : '';
    $getDescriptionData = "";
    if ($iDriverId > 0) {
        $getDescriptionData = get_value('register_driver', 'tProfileDescription', 'iDriverId', $iDriverId, '', 'true');
        $returnArr['Action'] = "1";
        $returnArr['message'] = $getDescriptionData;
    }

    if ($getDescriptionData == "") {
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
        $returnArr['Action'] = "0";
    }
    setDataResponse($returnArr);
}

if ($type == "configureProviderServiceLocation") {

    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : '';
    $eEnableServiceAtLocation = isset($_REQUEST['eEnableServiceAtLocation']) ? clean($_REQUEST['eEnableServiceAtLocation']) : 'No';

    if ($eEnableServiceAtLocation == "") {
        $eEnableServiceAtLocation = "No";
    }

    $where = "iDriverId = '$iDriverId'";

    $updateData['eEnableServiceAtLocation'] = $eEnableServiceAtLocation;


    $obj->MySQLQueryPerform("register_driver", $updateData, 'update', $where);

    $returnArr['Action'] = "1";
    $returnArr['message'] = getDriverDetailInfo($iDriverId);
    setDataResponse($returnArr);
}

function removeInvalidChars($text) {
    $regex = '/( [\x00-\x7F] | [\xC0-\xDF][\x80-\xBF] | [\xE0-\xEF][\x80-\xBF]{2} | [\xF0-\xF7][\x80-\xBF]{3} ) | ./x';
    return preg_replace($regex, '$1', $text);
}

function cleanString($val) {
    $non_displayables = array(
        '/%0[0-8bcef]/', # url encoded 00-08, 11, 12, 14, 15
        '/%1[0-9a-f]/', # url encoded 16-31
        '/[\x00-\x08]/', # 00-08
        '/\x0b/', # 11
        '/\x0c/', # 12
        '/[\x0e-\x1f]/', # 14-31
        '/x7F/'                     # 127
    );
    foreach ($non_displayables as $regex) {
        $val = preg_replace($regex, '', $val);
    }
    $search = array("\0", "\r", "\x1a", "\t", "\n");
    return $a = trim(str_replace($search, '', $val));
}

function json_clean_decode($json, $assoc = false, $depth = 512, $options = 0) {
    // search and remove comments like /* */ and //
    $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);
    if (version_compare(phpversion(), '5.4.0', '>=')) {
        return json_decode($json, $assoc, $depth, $options);
    } elseif (version_compare(phpversion(), '5.3.0', '>=')) {
        return json_decode($json, $assoc, $depth);
    } else {
        return json_decode($json, $assoc);
    }
}

?>
