<?php

include_once('../common.php');
include_once('../app_common_functions.php');

if(!empty($_SESSION['sess_iAdminUserId'])) {
	
} else {
    $generalobj->check_member_login();
}

/*function formatNum($number) {
    return strval(number_format($number, 2));
}

function converToTz($time, $toTz, $fromTz, $dateFormat = "Y-m-d H:i:s") {
    $date = new DateTime($time, new DateTimeZone($fromTz));
    $date->setTimezone(new DateTimeZone($toTz));
    $time = $date->format($dateFormat);
    return $time;
}

function get_currency($from_Currency, $to_Currency, $amount) {
    $forignalamount = $amount;
    $amount = urlencode($amount);
    $from_Currency = urlencode($from_Currency);
    $to_Currency = urlencode($to_Currency);
    //$url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
    $url = "https://finance.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
    $ch = curl_init();
    $timeout = 0;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt ($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $rawdata = curl_exec($ch);
    curl_close($ch);
    $data = explode('bld>', $rawdata);
    $data = explode($to_Currency, $data[1]);
    $ftollprice = round($data[0], 2);
    if ($ftollprice == 0 || $ftollprice == 0.00) {
        $ftollprice = $amount;
    }
    //return round($data[0], 2);
    return $ftollprice;
}
//put bc error while uberx booking
function getMemberCountryTax($iMemberId, $UserType = "Passenger") {
    global $generalobj, $obj;
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
    $fTax1 = 0;
    $fTax2 = 0;
    $sql = "SELECT COALESCE(co.fTax1, '0') as fTax1,COALESCE(co.fTax2, '0') as fTax2 FROM country as co LEFT JOIN $tblname as ru ON co.vCountryCode = ru.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'";
    $sqlcountryTax = $obj->MySQLSelect($sql);
    if (count($sqlcountryTax) > 0) {
        $fTax1 = $sqlcountryTax[0]['fTax1'];
        $fTax2 = $sqlcountryTax[0]['fTax2'];
    }
    $returnArr['fTax1'] = $fTax1;
    $returnArr['fTax2'] = $fTax2;
    return $returnArr;
} */

$tbl_name = 'register_user';
$tbl_name1 = 'cab_booking';
$sql = "select vName from currency where eStatus='Active' AND eDefault='Yes'";
$db_currency = $obj->MySQLSelect($sql);
$sql1 = "select vCode from language_master where eStatus='Active' AND eDefault='Yes'";
$db_language = $obj->MySQLSelect($sql1);
$sql = "select cn.vCountry,cn.vPhoneCode from country cn inner join configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
$db_con = $obj->MySQLSelect($sql);
//echo "<pre>";print_r($_POST);die;
if (isset($_POST['submitbtn'])) {
    //echo "<pre>";print_r($_POST);die;
    $vName = isset($_POST['vName']) ? $_POST['vName'] : '';
    $vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
    $vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
    $vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
    $vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
    $vPhoneCode = isset($_POST['vPhoneCode']) ? $_POST['vPhoneCode'] : '';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
    $eStatus = 'Active';
    $vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
    $vImgName = isset($_POST['vImgName']) ? $_POST['vImgName'] : '';
    $tPackageDetails = isset($_POST['tPackageDetails']) ? $_POST['tPackageDetails'] : '';
    $tDeliveryIns = isset($_POST['tDeliveryIns']) ? $_POST['tDeliveryIns'] : '';
    $tPickUpIns = isset($_POST['tPickUpIns']) ? $_POST['tPickUpIns'] : '';
    $vCurrencyPassenger = isset($_POST['vCurrencyPassenger']) ? $_POST['vCurrencyPassenger'] : '';
    $vPass = $generalobj->encrypt_bycrypt($vPassword);
    $eType = isset($_POST['eType']) ? $_POST['eType'] : 'Ride';

    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';
    $eFemaleDriverRequest = isset($_REQUEST["eFemaleDriverRequest"]) ? $_REQUEST["eFemaleDriverRequest"] : '';
    $eHandiCapAccessibility = isset($_REQUEST["eHandiCapAccessibility"]) ? $_REQUEST["eHandiCapAccessibility"] : '';
    $eChildSeatAvailable = isset($_REQUEST["eChildSeatAvailable"]) ? $_REQUEST["eChildSeatAvailable"] : '';
    $eBookingFrom = isset($_POST['eBookingFrom']) ? $_POST['eBookingFrom'] : 'Admin';

    $pickups = explode(',', $_POST['from_lat_long']); // from latitude-Longitude
    $dropoff = explode(',', $_POST['to_lat_long']); // To latitude-Longitude
    $vSourceLatitude = isset($pickups[0]) ? trim(str_replace("(", "", $pickups[0])) : '';
    $vSourceLongitude = isset($pickups[1]) ? trim(str_replace(")", "", $pickups[1])) : '';


    $vDestLatitude = isset($dropoff[0]) ? trim(str_replace("(", "", $dropoff[0])) : '';
    $vDestLongitude = isset($dropoff[1]) ? trim(str_replace(")", "", $dropoff[1])) : '';
    $vDistance = isset($_POST['distance']) ? (round(number_format($_POST['distance'] / 1000))) : '';
    $vDuration = isset($_POST['duration']) ? (round(number_format($_POST['duration'] / 60))) : '';
    $iUserId = isset($_POST['iUserId']) ? $_POST['iUserId'] : '';
    $iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
    $dBooking_date = isset($_POST['dBooking_date']) ? $_POST['dBooking_date'] : '';
    $vSourceAddresss = isset($_POST['vSourceAddresss']) ? $_POST['vSourceAddresss'] : '';
    $tDestAddress = isset($_POST['tDestAddress']) ? $_POST['tDestAddress'] : '';
    $eAutoAssign = isset($_POST['eAutoAssign']) ? $_POST['eAutoAssign'] : 'No';
    $eStatus1 = ($eAutoAssign == 'Yes') ? 'Pending' : 'Assign';

    $iPackageTypeId = isset($_POST['iPackageTypeId']) ? $_POST['iPackageTypeId'] : '0';
    $vReceiverName = isset($_POST['vReceiverName']) ? $_POST['vReceiverName'] : '';
    $vReceiverMobile = isset($_POST['vReceiverMobile']) ? $_POST['vReceiverMobile'] : '';

    $tPackageDetails = isset($_POST['tPackageDetails']) ? $_POST['tPackageDetails'] : '';
    $tDeliveryIns = isset($_POST['tDeliveryIns']) ? $_POST['tDeliveryIns'] : '';
    $tPickUpIns = isset($_POST['tPickUpIns']) ? $_POST['tPickUpIns'] : '';
    $iVehicleTypeId = isset($_POST['iVehicleTypeId']) ? $_POST['iVehicleTypeId'] : '';
    $iCabBookingId = isset($_POST['iCabBookingId']) ? $_POST['iCabBookingId'] : '';
    $fNightPrice = isset($_POST['fNightPrice']) ? $_POST['fNightPrice'] : '1';
    $fPickUpPrice = isset($_POST['fPickUpPrice']) ? $_POST['fPickUpPrice'] : '1';
    $vTimeZone = isset($_POST['vTimeZone']) ? $_POST['vTimeZone'] : '';
    $vRideCountry = isset($_POST['vRideCountry']) ? $_POST['vRideCountry'] : '';
    $backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

    $fWalletMinBalance = $WALLET_MIN_BALANCE;
    $fWalletBalance = 0;
    //Added By HJ On 14-12-2019 For Get User Detail When Site Type Is Demo B'coz In Demo Type Data Post in masking Start
    if(SITE_TYPE == "Demo" && $iUserId > 0){
        $getUserData = $obj->MySQLSelect("SELECT vPhone,vName,vLastName,vEmail,iUserId,tSessionId FROM ".$tbl_name." WHERE iUserId='".$iUserId."'");
        if(count($getUserData) > 0){
            $vPhone = $getUserData[0]['vPhone'];
            $vName = $getUserData[0]['vName'];
            $vLastName = $getUserData[0]['vLastName'];
            $vEmail = $getUserData[0]['vEmail'];
        }
    }
    //Added By HJ On 14-12-2019 For Get User Detail When Site Type Is Demo B'coz In Demo Type Data Post in masking End
    if ($iDriverId != "") {
        $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
        $fWalletBalance = $user_available_balance;
    }

    $eFlatTrip = isset($_POST['eFlatTrip']) ? $_POST['eFlatTrip'] : 'No';
    $fFlatTripPrice = isset($_POST['fFlatTripPrice']) ? $_POST['fFlatTripPrice'] : 0;

    $vCouponCode = isset($_POST['vCouponCode']) ? $_POST['vCouponCode'] : '';
    $vTripPaymentMode = isset($_POST['vTripPaymentMode']) ? $_POST['vTripPaymentMode'] : '';

    $iVehicleTypeIdNew = isset($_POST['iVehicleTypeId']) ? $_POST['iVehicleTypeId'] : '';

    $iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
    $eRideType = isset($_POST['eRideType']) ? $_POST['eRideType'] : '';
    
    $iFromStationId = isset($_POST['iFromStationId']) ? $_POST['iFromStationId'] : '';
    $iToStationId = isset($_POST['iToStationId']) ? $_POST['iToStationId'] : '';
    
    $iAdminId = isset($_POST['iAdminId']) ? $_POST['iAdminId'] : '';
    $vRiderRoomNubmer = isset($_POST['vRiderRoomNubmer']) ? $_POST['vRiderRoomNubmer'] : '';
    $iHotelBookingId = isset($_POST['iHotelBookingId']) ? $_POST['iHotelBookingId'] : '';

    $iHotelId = 0; $isFromHotelPanel = 'No';
    if($_SESSION['SessionUserType'] == 'hotel') {
        $isFromHotelPanel = 'Yes';
    if(!empty($iHotelBookingId)) {
        $sql1 = "select iHotelId from hotel where iAdminId=".$iHotelBookingId." AND eStatus='Active'";
        $db_hotel = $obj->MySQLSelect($sql1);  
        if(isset($db_hotel) && !empty($db_hotel)){
            $iHotelId = $db_hotel[0]['iHotelId'];    
        }
    }
    }
    
    if($eType == 'Fly') {
        $eFly = 'Yes';
    } else {
        $eFly = 'No';
    }
    if ($eType == 'Moto' || $eType == 'Fly') {
        $eType = 'Ride';
    } 
    
    if ($eType == 'Ride') {
        $iVehicleTypeId = isset($_POST['iDriverVehicleId_ride']) ? $_POST['iDriverVehicleId_ride'] : '';
        //$eRideType = isset($_POST['eRideType']) ? $_POST['eRideType'] : '';
    }
    if ($eType == 'Deliver') {
        $iVehicleTypeId = isset($_POST['iDriverVehicleId_delivery']) ? $_POST['iDriverVehicleId_delivery'] : '';
        $eDeliveryType = isset($_POST['eDeliveryType']) ? $_POST['eDeliveryType'] : '';
    }
    if ($iVehicleTypeId == '') {
        $iVehicleTypeId = $iVehicleTypeIdNew;
    }
    $systemTimeZone = date_default_timezone_get();
    $SQL1 = "SELECT vValue FROM configurations WHERE vName = 'COMMISION_DEDUCT_ENABLE'";
    $config_data = $obj->MySQLSelect($SQL1);
    $eCommisionDeductEnable = $config_data[0]['vValue'];
    if (!empty($vEmail)) {
        $SQL1 = "SELECT vName,vLastName,vEmail,iUserId,tSessionId FROM $tbl_name WHERE vEmail = '$vEmail'";
        $email_exist = $obj->MySQLSelect($SQL1);
        $iUserId = $email_exist[0]['iUserId'];

        $SQL3 = "UPDATE $tbl_name SET `eEmailVerified` = 'Yes',`ePhoneVerified` = 'Yes' WHERE vEmail = '$vEmail'";
        $obj->sql_query($SQL3);

        if ($email_exist[0]['tSessionId'] == '') {
            $SQL1 = "UPDATE $tbl_name SET `tSessionId` = '" . session_id() . time() . "' WHERE vEmail = '$vEmail'";
            $obj->sql_query($SQL1);

            $SQL2 = "SELECT vName,vLastName,vEmail,iUserId,tSessionId FROM $tbl_name WHERE vEmail = '$vEmail'";
            $email_exist = $obj->MySQLSelect($SQL2);
        }
    }
    //Added By HJ On 28-08-2019 For Get Country Timezone If Not Found Start
    if (trim($vTimeZone) == "") {
        $countryTimeZone = $obj->MySQLSelect("SELECT vTimeZone FROM country WHERE vPhoneCode='" . $vPhoneCode . "'");
        if (count($countryTimeZone) > 0) {
            $vTimeZone = $countryTimeZone[0]['vTimeZone'];
        }
    }
    //Added By HJ On 28-08-2019 For Get Country Timezone If Not Found End
    
     if ($iCabBookingId != "" && $iCabBookingId != '0') {
        $SQLti1 = "SELECT vTimeZone,eBookingFrom,dBooking_date FROM cab_booking WHERE iCabBookingId = '$iCabBookingId'";
        $time_data = $obj->MySQLSelect($SQLti1);
        $eBookingFrom = $time_data[0]['eBookingFrom'];
        if ($eBookingFrom != "Admin") {
            $vTimeZone = $time_data[0]['vTimeZone'];
        }
        $dBooking_date = $time_data[0]['dBooking_date'];
        $dBooking_date = converToTz($dBooking_date, $vTimeZone, $systemTimeZone);
    }
    
    if (count($email_exist) == 0 && $iCabBookingId == "" && !empty($vEmail)) {
        $eReftype = "Rider";
        $vRefCode = $generalobj->ganaraterefercode($eReftype);
        $vRefCodePara = "`vRefCode` = '" . $vRefCode . "',";
        $vPassword = $generalobj->encrypt_bycrypt('123456');
        $q = "INSERT INTO ";
        $where = '';
        $query = $q . " `" . $tbl_name . "` SET
			`vName` = '" . $vName . "',
			`vLastName` = '" . $vLastName . "',
			`vEmail` = '" . $vEmail . "',
			`vPassword` = '" . $vPassword . "',
			`vPhone` = '" . $vPhone . "',
			`vCountry` = '" . $vCountry . "',
			`vPhoneCode` = '" . $vPhoneCode . "',
            $vRefCodePara
			`eStatus` = '" . $eStatus . "',
			`vImgName` = '" . $vImgName . "',
			`vCurrencyPassenger` = '" . $db_currency[0]['vName'] . "',
			`vLang` = '" . $db_language[0]['vCode'] . "',
			`tRegistrationDate` = '" . date("Y-m-d H:i:s") . "',
			`tSessionId` = '" . session_id() . time() . "',
			`eEmailVerified` = 'Yes',
			`ePhoneVerified` = 'Yes',
			`vInviteCode` = '" . $vInviteCode . "'";
        $obj->sql_query($query);
        $iUserId = $obj->GetInsertId();
        if ($iUserId != "") {
            $maildata['EMAIL'] = $vEmail;
            $maildata['NAME'] = $vName . ' ' . $vLastName;
            $maildata['PASSWORD'] = '123456';
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER_FOR_MANUAL_BOOKING", $maildata);
        }

        $SQL1 = "SELECT vName,vLastName,vEmail,iUserId,tSessionId FROM $tbl_name WHERE vEmail = '$vEmail'";
        $email_exist = $obj->MySQLSelect($SQL1);
    } else {
        $SQL1 = "UPDATE $tbl_name SET eStatus='".$eStatus."' WHERE vEmail = '".$vEmail."'";
        $obj->sql_query($SQL1);
    }

    if (($iUserId == "" || $iUserId == "0" || $vSourceAddresss == "" || $tDestAddress == "") && $eType != "UberX") { 
        $var_msg = "Booking details is not add/update because missing information";
        if ($iCabBookingId == "") {
            header("location:add_booking.php?success=0&var_msg=" . $var_msg);
            exit;
        } else {
            header("location:add_booking.php?booking_id=" . $iCabBookingId . "success=0&var_msg=" . $var_msg);
            exit;
        }
    } else if (($iUserId == "" || $iUserId == "0" || $vSourceAddresss == "") && $eType == "UberX") {
        $var_msg = "Booking details is not add/update because missing information";
        if ($iCabBookingId == "") {
            header("location:add_booking.php?success=0&var_msg=" . $var_msg);
            exit;
        } else {
            header("location:add_booking.php?booking_id=" . $iCabBookingId . "success=0&var_msg=" . $var_msg);
            exit;
        }
    }
    if (($eType == 'Ride' && $eRideType == "later") || ($eType == 'Deliver' && $eDeliveryType == "later") || ($eType == 'UberX')) {
        $rand_num = rand(10000000, 99999999);
        //$systemTimeZone = date_default_timezone_get();
        $dBookingDate = converToTz($dBooking_date, $systemTimeZone, $vTimeZone);
        $dBookingDate_new = date('Y-m-d H:i', strtotime($dBookingDate));
        //$dBookingDate_new_mail = date('Y-m-d H:i A', strtotime($dBookingDate)); //added by SP for date format in mail from issue#332 on 03-10-2019
        $dBookingDate_new_mail = date("jS F, Y", strtotime($dBooking_date));
        $dBookingDate_new_mail_time = date("h:i A", strtotime($dBooking_date));
        $dBookingDate_new_mail_date = $dBookingDate_new_mail;
        $dBookingDate_new_mail = $dBookingDate_new_mail." ".$langage_lbl_admin['LBL_AT_TXT']." ".$dBookingDate_new_mail_time;

        $q1 = "INSERT INTO ";
        $whr = ",`vBookingNo`='" . $rand_num . "'";
        $edit = "";
        if ($iCabBookingId != "" && $iCabBookingId != '0') {
            $q1 = "UPDATE ";
            $whr = " WHERE `iCabBookingId` = '" . $iCabBookingId . "'";
            $edit = '1';
        }
        if ($APP_TYPE == 'UberX' && !empty($iDriverId)) {
            $eStatus1 = "Accepted";
        }
        if ($eTollSkipped == 'No' || $fTollPrice != "") {
            $fTollPrice_Original = $fTollPrice;
            $vTollPriceCurrencyCode = strtoupper($vTollPriceCurrencyCode);
            $default_currency = $db_currency[0]['vName'];
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
        $tVehicleTypeData = $tVehicleTypeFareData = '';
        if($eFlatTrip == '' || empty($eFlatTrip) ){
        	$eFlatTrip = 'No';
        }
        if ($eType == 'UberX') {
            include('../include/uberx/include_webservice_uberx.php');
            include_once ('../include_generalFunctions_shark.php');

            $tVehicleTypeData = '[{"iVehicleTypeId":"' . $iVehicleTypeId . '","fVehicleTypeQty":"1","tUserComment":""}]';


            //get detail tripFareDetailsSaveArr data
            $getVehicleTypeFareDetailsArr = getVehicleTypeFareDetails($tVehicleTypeData, $iUserId);

            //variable set tripFareDetailsSaveArr and data encode
            $tVehicleTypeFareData = $getVehicleTypeFareDetailsArr['tripFareDetailsSaveArr'];

            $tVehicleTypeFareData = json_encode($tVehicleTypeFareData);

            $query1 = $q1 . " `" . $tbl_name1 . "` SET
                `iUserId` = '" . $iUserId . "',
                `iCompanyId` = '" . $iCompanyId . "',
                `iDriverId` = '" . $iDriverId . "',
                `vSourceLatitude` = '" . $vSourceLatitude . "',
                `vSourceLongitude` = '" . $vSourceLongitude . "',
                `vDestLatitude` = '" . $vDestLatitude . "',
                `vDestLongitude` = '" . $vDestLongitude . "',
                `vDistance` = '" . $vDistance . "',
                `vDuration` = '" . $vDuration . "',
                `dBooking_date` = '" . $dBookingDate_new . "',
                `vSourceAddresss` = '" . $vSourceAddresss . "',
                `tPackageDetails` = '" . $tPackageDetails . "',
                `iPackageTypeId` = '" . $iPackageTypeId . "',
                `tDeliveryIns` = '" . $tDeliveryIns . "',
                `tPickUpIns` = '" . $tPickUpIns . "',
                `vReceiverName` = '" . $vReceiverName . "',
                `vReceiverMobile` = '" . $vReceiverMobile . "',
                `tDestAddress` = '" . $tDestAddress . "',
                `eType` = '" . $eType . "',
                `eStatus`='" . $eStatus1 . "',
                `eAutoAssign`='" . $eAutoAssign . "',
                `fPickUpPrice`='" . $fPickUpPrice . "',
                `fNightPrice`='" . $fNightPrice . "',
                `eCancelBy`='',
                `fWalletMinBalance`='" . $fWalletMinBalance . "',
                `fWalletBalance`='" . $fWalletBalance . "',
                `vRideCountry`='" . $vRideCountry . "',
                `vTimeZone`='" . $vTimeZone . "',
                `fTollPrice`='" . $fTollPrice . "',
                `vTollPriceCurrencyCode` = '" . $vTollPriceCurrencyCode . "',
                `eTollSkipped` = '" . $eTollSkipped . "',
                `eCommisionDeductEnable`='" . $eCommisionDeductEnable . "',
                `eFlatTrip`='" . $eFlatTrip . "',
                `fFlatTripPrice` = '" . $fFlatTripPrice . "',
                `eFemaleDriverRequest`= '" . $eFemaleDriverRequest . "',
                `eHandiCapAccessibility`= '" . $eHandiCapAccessibility . "',
                `eBookingFrom`= '" . $eBookingFrom . "',
                    `vCouponCode`= '" . $vCouponCode . "',
                `tVehicleTypeData`= '" . $tVehicleTypeData . "',
                `tVehicleTypeFareData`= '" . $tVehicleTypeFareData . "',
                `vRiderRoomNubmer`= '" . $vRiderRoomNubmer . "',
				`iAdminId`= '" . $iAdminId . "',
				`iHotelBookingId`= '" . $iHotelBookingId . "',
                `iVehicleTypeId` = '" . $iVehicleTypeId . "'" . $whr;
        } else {
            $query1 = $q1 . " `" . $tbl_name1 . "` SET
                `iUserId` = '" . $iUserId . "',
                `iCompanyId` = '" . $iCompanyId . "',
                `iDriverId` = '" . $iDriverId . "',
                `vSourceLatitude` = '" . $vSourceLatitude . "',
                `vSourceLongitude` = '" . $vSourceLongitude . "',
                `vDestLatitude` = '" . $vDestLatitude . "',
                `vDestLongitude` = '" . $vDestLongitude . "',
                `vDistance` = '" . $vDistance . "',
                `vDuration` = '" . $vDuration . "',
                `dBooking_date` = '" . $dBookingDate_new . "',
                `vSourceAddresss` = '" . $vSourceAddresss . "',
                `tPackageDetails` = '" . $tPackageDetails . "',
                `iPackageTypeId` = '" . $iPackageTypeId . "',
                `tDeliveryIns` = '" . $tDeliveryIns . "',
                `tPickUpIns` = '" . $tPickUpIns . "',
                `vReceiverName` = '" . $vReceiverName . "',
                `vReceiverMobile` = '" . $vReceiverMobile . "',
                `tDestAddress` = '" . $tDestAddress . "',
                `eType` = '" . $eType . "',
                `eStatus`='" . $eStatus1 . "',
                `eAutoAssign`='" . $eAutoAssign . "',
                `fPickUpPrice`='" . $fPickUpPrice . "',
                `fNightPrice`='" . $fNightPrice . "',
                `eCancelBy`='',
                `fWalletMinBalance`='" . $fWalletMinBalance . "',
                `fWalletBalance`='" . $fWalletBalance . "',
                `vRideCountry`='" . $vRideCountry . "',
                `vTimeZone`='" . $vTimeZone . "',
                `fTollPrice`='" . $fTollPrice . "',
                `vTollPriceCurrencyCode` = '" . $vTollPriceCurrencyCode . "',
                `eTollSkipped` = '" . $eTollSkipped . "',
                `eCommisionDeductEnable`='" . $eCommisionDeductEnable . "',
                `eFlatTrip`='" . $eFlatTrip . "',
                `fFlatTripPrice` = '" . $fFlatTripPrice . "',
                `eFemaleDriverRequest`= '" . $eFemaleDriverRequest . "',
                `eHandiCapAccessibility`= '" . $eHandiCapAccessibility . "',
                `eBookingFrom`= '" . $eBookingFrom . "',
                `iFromStationId` = '".$iFromStationId."',
                `iToStationId` = '".$iToStationId."',
                `vCouponCode`= '" . $vCouponCode . "',
                `vRiderRoomNubmer`= '" . $vRiderRoomNubmer . "',
				`iAdminId`= '" . $iAdminId . "',
				`iHotelBookingId`= '" . $iHotelBookingId . "',
                `iVehicleTypeId` = '" . $iVehicleTypeId . "'" . $whr;
        }
        /* `ePayType` = '".$vTripPaymentMode."', */
        $obj->sql_query($query1);
        $sql = "select vName,vLastName,vEmail,iDriverVehicleId,vPhone,vcode,vLang from register_driver where iDriverId=" . $iDriverId;
        $driver_db = $obj->MySQLSelect($sql);

        $Data1['vRider'] = $email_exist[0]['vName'] . " " . $email_exist[0]['vLastName'];
        $Data1['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
        $Data1['vDriverMail'] = $driver_db[0]['vEmail'];
        $Data1['vRiderMail'] = $email_exist[0]['vEmail'];
        $Data1['vSourceAddresss'] = $vSourceAddresss;
        $Data1['tDestAddress'] = $tDestAddress;
        $Data1['dBookingdate'] = $dBookingDate_new_mail;
        $Data1['vBookingNo'] = $rand_num;

        if ($edit == '1') {
            $sql = "select vBookingNo from cab_booking where `iCabBookingId` = '" . $iCabBookingId . "'";
            $cab_id = $obj->MySQLSelect($sql);
            $Data1['vBookingNo'] = $cab_id[0]['vBookingNo'];
        }
        //$Data1['vDistance']=$vDistance;
        //$Data1['vDuration']=$vDuration;
        //$return = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER", $Data1);
        //$return1 = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER", $Data1);

        if ($eType == 'UberX') {
            $return = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER_APP_SP", $Data1);
        } else {
            $return = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER", $Data1);
        }

        if ($eAutoAssign == 'Yes') {
            if ($eType == 'UberX') {
                $return1 = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_AUTOASSIGN_SP", $Data1);
            } else {
                $return1 = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_AUTOASSIGN", $Data1);
            }
        } else {
            if ($eType == 'UberX') {
                $return1 = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_SP", $Data1);
            } else {
                $return1 = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER", $Data1);
            }
        }
// echo "OUT";exit;
        // Start Send SMS
        $query = "SELECT vLicencePlate FROM driver_vehicle WHERE iDriverVehicleId=" . $driver_db[0]['iDriverVehicleId'];
        $db_driver_vehicles = $obj->MySQLSelect($query);

        $vPhone = $vPhone;
        $vcode = $db_con[0]['vPhoneCode'];
        $Booking_Date = @date('d-m-Y', strtotime($dBookingDate));
        $Booking_Time = @date('H:i:s', strtotime($dBookingDate));

        $query = "SELECT vPhoneCode,vLang FROM register_user WHERE iUserId=" . $iUserId;
        $db_user = $obj->MySQLSelect($query);

        $maillanguage = $db_user[0]['vLang'];

        $Pass_name = $vName . ' ' . $vLastName;
        $vcode = $db_user[0]['vPhoneCode'];
        $maildata['DRIVER_NAME'] = $Data1['vDriver'];
        $maildata['PLATE_NUMBER'] = $db_driver_vehicles[0]['vLicencePlate'];
        $maildata['BOOKING_DATE'] = $dBookingDate_new_mail_date;
        $maildata['BOOKING_TIME'] = $dBookingDate_new_mail_time;
        $maildata['BOOKING_NUMBER'] = $Data1['vBookingNo'];
        //Send sms to User
        //added by SP for sms functionality on 13-7-2019 start
        $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
        $PhoneCodeP = $passengerData[0]['vPhoneCode'];
        $DriverData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iDriverId AND r.vCountry = c.vCountryCode");
        $PhoneCodeD = $DriverData[0]['vPhoneCode'];
        //added by SP for sms functionality on 13-7-2019 end

        if ($eAutoAssign == 'Yes') {
            $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_AUTOASSIGN", $maildata, "", $maillanguage);
        } else {
            if ($eType == 'UberX') {
                $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_APP", $maildata, "", $maillanguage);
            } else {
                $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE", $maildata, "", $maillanguage);
            }
        }
        //$return4 = $generalobj->sendUserSMS($vPhone, $vcode, $message_layout, "");
        $return4 = $generalobj->sendSystemSms($vPhone, $PhoneCodeP, $message_layout); //added by SP for sms functionality on 13-7-2019
        //Send sms to Driver

        $vPhone = $driver_db[0]['vPhone'];
        $vcode1 = $driver_db[0]['vcode'];
        $maillanguage1 = $driver_db[0]['vLang'];

        $maildata1['PASSENGER_NAME'] = $Pass_name;
        $maildata1['BOOKING_DATE'] = $dBookingDate_new_mail_date;
        $maildata1['BOOKING_TIME'] = $dBookingDate_new_mail_time;
        $maildata1['BOOKING_NUMBER'] = $Data1['vBookingNo'];

        $message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE", $maildata1, "", $maillanguage1);
        //$return5 = $generalobj->sendUserSMS($vPhone, $vcode1, $message_layout, "");
        $return5 = $generalobj->sendSystemSms($vPhone, $PhoneCodeD, $message_layout); //added by SP for sms functionality on 13-7-2019

        if ($iCabBookingId == "") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl['LBL_MANUAL_BOOKING_ADDED_SUCESSFULLY'] . '.';
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl['LBL_MANUAL_BOOKING_UPDETED_SUCESSFULLY'] . '.';
        }
        echo "1";exit;
        $path = $_SERVER['SERVER_NAME'];
        header("location:" . $path . "/cabbooking.php");

        if ($return && $return1) {
            $success = 1;
            $var_msg = $langage_lbl['LBL_MANUAL_BOOKING_ADDED_SUCESSFULLY'] . '.';
            header("location:../cabbooking.php?success=1&vassign=$edit");
            exit;
        } else {
            $error = 1;
            $var_msg = $langage_lbl['LBL_ERROR_OCCURED'];
        }

        header("location:../cabbooking.php?success=1&vassign=$edit");
        exit;
    } else {
        $dataArray = array();
        $dataArray['tSessionId'] = $email_exist[0]['tSessionId'];
        $dataArray['iUserId'] = $iUserId;
        $dataArray['vTimeZone'] = $vTimeZone;
        $dataArray['iVehicleTypeId'] = $iVehicleTypeId;
        $dataArray['vSourceLatitude'] = $vSourceLatitude;
        $dataArray['vSourceLongitude'] = $vSourceLongitude;
        $dataArray['vSourceAddresss'] = $vSourceAddresss;
        $dataArray['vDestLatitude'] = $vDestLatitude;
        $dataArray['vDestLongitude'] = $vDestLongitude;
        $dataArray['tDestAddress'] = $tDestAddress;
        $dataArray['fTollPrice'] = $fTollPrice;
        $dataArray['vTollPriceCurrencyCode'] = $vTollPriceCurrencyCode;
        $dataArray['eTollSkipped'] = $eTollSkipped;
        $dataArray['eType'] = $eType;
        $dataArray['eBookingFrom'] = $eBookingFrom;
        $dataArray['eRental'] = 'No';
        $dataArray['eShowOnlyMoto'] = 'No';
        $dataArray['vCouponCode'] = $vCouponCode;
        
        $dataArray['iHotelBookingId'] = $iHotelBookingId;
        $dataArray['iHotelId'] = $iHotelId;
        $dataArray['isFromHotelPanel'] = $isFromHotelPanel;

        $dataArray['tPackageDetails'] = $tPackageDetails;
        $dataArray['iPackageTypeId'] = $iPackageTypeId;
        $dataArray['tDeliveryIns'] = $tDeliveryIns;
        $dataArray['tPickUpIns'] = $tPickUpIns;
        $dataArray['vReceiverName'] = $vReceiverName;
        $dataArray['vReceiverMobile'] = $vReceiverMobile;

        $dataArray['eFemaleDriverRequest'] = $eFemaleDriverRequest;
        $dataArray['eHandiCapAccessibility'] = $eHandiCapAccessibility;
        $dataArray['eChildSeatAvailable'] = $eChildSeatAvailable;
        $dataArray['iCompanyId'] = $iCompanyId;
        $dataArray['isFromAdminPanel'] = 'Yes';
        $dataArray['eFly'] = $eFly;
        $dataArray['iFromStationId'] = $iFromStationId;
        $dataArray['iToStationId'] = $iToStationId;
        /* $dataArray['ePayType']= $vTripPaymentMode;
          if($vTripPaymentMode == 'Cash') {
          $dataArray['CashPayment']= 'true';
          } else {
          $dataArray['CashPayment']= 'false';
          } */
        echo json_encode($dataArray);
        exit;
    }
} else {
    header("location:../cabbooking.php?success=1&vassign=$edit");
    exit;
}
?>
