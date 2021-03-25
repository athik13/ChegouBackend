<?php

include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
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

////$generalobjAdmin->check_member_login();

if (isset($_POST['submitbuttonvalue'])) {
    $tbl_name = 'register_user';
    $tbl_name1 = 'cab_booking';

    $vName = isset($_POST['vName']) ? $_POST['vName'] : '';
    $vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
    $vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
    $vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
    $vPhoneCode = isset($_POST['vPhoneCode']) ? $_POST['vPhoneCode'] : '';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $eStatus = 'Active';
    $vPass = $generalobj->encrypt_bycrypt($vPassword);
    $eType = isset($_POST['eType']) ? $_POST['eType'] : '';
    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';

    $vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
    $vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
    $vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
    $vImgName = isset($_POST['vImgName']) ? $_POST['vImgName'] : '';
    $tPackageDetails = isset($_POST['tPackageDetails']) ? $_POST['tPackageDetails'] : '';
    $tDeliveryIns = isset($_POST['tDeliveryIns']) ? $_POST['tDeliveryIns'] : '';
    $tPickUpIns = isset($_POST['tPickUpIns']) ? $_POST['tPickUpIns'] : '';
    $vCurrencyPassenger = isset($_POST['vCurrencyPassenger']) ? $_POST['vCurrencyPassenger'] : '';
    $eFemaleDriverRequest = isset($_REQUEST["eFemaleDriverRequest"]) ? $_REQUEST["eFemaleDriverRequest"] : '';
    $eHandiCapAccessibility = isset($_REQUEST["eHandiCapAccessibility"]) ? $_REQUEST["eHandiCapAccessibility"] : '';

    $sql = "select vName from currency where eStatus='Active' AND eDefault='Yes'";
    $db_currency = $obj->MySQLSelect($sql);

    $sql1 = "select vCode from language_master where eStatus='Active' AND eDefault='Yes'";
    $db_language = $obj->MySQLSelect($sql1);

    $sql = "select cn.vCountry,cn.vPhoneCode from country cn inner join 
	configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
    $db_con = $obj->MySQLSelect($sql);

    $pickups = explode(',', $_POST['from_lat_long']); // from latitude-Longitude
    $dropoff = explode(',', $_POST['to_lat_long']); // To latitude-Longitude

    $vSourceLatitude = isset($pickups[0]) ? trim(str_replace("(", "", $pickups[0])) : '';
    $vSourceLongitude = isset($pickups[1]) ? trim(str_replace(")", "", $pickups[1])) : '';
    $vDestLatitude = isset($dropoff[0]) ? trim(str_replace("(", "", $dropoff[0])) : '';
    $vDestLongitude = isset($dropoff[1]) ? trim(str_replace(")", "", $dropoff[1])) : '';
    $vDistance = isset($_POST['distance']) ? (round(number_format($_POST['distance'] / 1000))) : '';
    $vDuration = isset($_POST['duration']) ? (round(number_format($_POST['duration'] / 60))) : '';
    $iUserId = isset($_POST['iUserId']) ? $_POST['iUserId'] : '';
    $dBooking_date = isset($_POST['dBooking_date']) ? $_POST['dBooking_date'] : '';
    $vSourceAddresss = isset($_POST['vSourceAddresss']) ? $_POST['vSourceAddresss'] : '';
    $tDestAddress = isset($_POST['tDestAddress']) ? $_POST['tDestAddress'] : '';
    $iVehicleTypeId = isset($_POST['iVehicleTypeId']) ? $_POST['iVehicleTypeId'] : '';
    $iCabBookingId = isset($_POST['iCabBookingId']) ? $_POST['iCabBookingId'] : '';
    $fNightPrice = isset($_POST['fNightPrice']) ? $_POST['fNightPrice'] : '1';
    $fPickUpPrice = isset($_POST['fPickUpPrice']) ? $_POST['fPickUpPrice'] : '1';
    $vTimeZone = isset($_POST['vTimeZone']) ? $_POST['vTimeZone'] : '';
    $vRideCountry = isset($_POST['vRideCountry']) ? $_POST['vRideCountry'] : '';
    $backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
    $iAdminId = isset($_POST['iAdminId']) ? $_POST['iAdminId'] : '';
    $vRiderRoomNubmer = isset($_POST['vRiderRoomNubmer']) ? $_POST['vRiderRoomNubmer'] : '';
    $eBookingFrom = isset($_POST['eBookingFrom']) ? $_POST['eBookingFrom'] : '';
    $iHotelBookingId = isset($_POST['iHotelBookingId']) ? $_POST['iHotelBookingId'] : '';

    $iHotelId = 0;
    $sql1 = "select iHotelId from hotel where iAdminId=".$iHotelBookingId." AND eStatus='Active'";
    $db_hotel = $obj->MySQLSelect($sql1);  
    if(isset($db_hotel) && !empty($db_hotel)){
        $iHotelId = $db_hotel[0]['iHotelId'];    
    } 
    

    $fWalletMinBalance = $WALLET_MIN_BALANCE;
    $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
    $fWalletBalance = $user_available_balance;

    $eFlatTrip = isset($_POST['eFlatTrip']) ? $_POST['eFlatTrip'] : 'No';
    $fFlatTripPrice = isset($_POST['fFlatTripPrice']) ? $_POST['fFlatTripPrice'] : 0;

    $iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
    $eAutoAssign = isset($_POST['eAutoAssign']) ? $_POST['eAutoAssign'] : 'No';
    if ($_POST['submitbuttonvalue'] == 'Book Later') {
        $eAutoAssign = 'Yes';
    }
    $eStatus1 = ($eAutoAssign == 'Yes') ? 'Pending' : 'Assign';
    $iPackageTypeId = isset($_POST['iPackageTypeId']) ? $_POST['iPackageTypeId'] : '0';
    $vReceiverName = isset($_POST['vReceiverName']) ? $_POST['vReceiverName'] : '';
    $vReceiverMobile = isset($_POST['vReceiverMobile']) ? $_POST['vReceiverMobile'] : '';
    $tPackageDetails = isset($_POST['tPackageDetails']) ? $_POST['tPackageDetails'] : '';
    $tDeliveryIns = isset($_POST['tDeliveryIns']) ? $_POST['tDeliveryIns'] : '';
    $tPickUpIns = isset($_POST['tPickUpIns']) ? $_POST['tPickUpIns'] : '';

    $SQL1 = "SELECT vValue FROM configurations WHERE vName = 'COMMISION_DEDUCT_ENABLE'";
    $config_data = $obj->MySQLSelect($SQL1);
    $eCommisionDeductEnable = $config_data[0]['vValue'];

    if (!empty($vEmail)) {
        $SQL1 = "SELECT vName,vLastName,vEmail,iUserId,tSessionId FROM $tbl_name WHERE vEmail = '$vEmail'";
        $email_exist = $obj->MySQLSelect($SQL1);
        $iUserId = $email_exist[0]['iUserId'];
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
			`vInviteCode` = '" . $vInviteCode . "'";
        $obj->sql_query($query);
        $iUserId = $obj->GetInsertId();
        if ($iUserId != "") {
            $maildata['EMAIL'] = $vEmail;
            $maildata['NAME'] = $vName . ' ' . $vLastName;
            $maildata['PASSWORD'] = '123456';
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER_FOR_MANUAL_BOOKING", $maildata);
        }
    } else {
        $SQL1 = "UPDATE $tbl_name SET eStatus='$eStatus',`vCountry` = '" . $vCountry . "' WHERE vEmail = '$vEmail'";
        $obj->sql_query($SQL1);
    }

    if (($iUserId == "" || $iUserId == "0" || $vSourceAddresss == "" || $tDestAddress == "") && $eType != "UberX") {
        $_SESSION['var_msg1'] = 'Booking details is not add/update because missing information';
        if ($iCabBookingId == "") {
            echo $_SESSION['var_msg1'];
            exit;
        } else {
            echo $_SESSION['var_msg1'];
            exit;
        }
    } else if (($iUserId == "" || $iUserId == "0" || $vSourceAddresss == "") && $eType == "UberX") {
        $_SESSION['var_msg2'] = 'Booking details is not add/update because missing information';
        if ($iCabBookingId == "") {
            echo $_SESSION['var_msg2'];
            exit;
        } else {
            echo $_SESSION['var_msg2'];
            exit;
        }
    }
    if ($_POST['submitbuttonvalue'] == 'Book Later') {
        $rand_num = rand(10000000, 99999999);
        $systemTimeZone = date_default_timezone_get();
        $dBookingDate = converToTz($dBooking_date, $systemTimeZone, $vTimeZone);
        $dBookingDate_new = date('Y-m-d H:i', strtotime($dBookingDate));

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
            $fTollPrice = $fTollPrice;
            $vTollPriceCurrencyCode = $vTollPriceCurrencyCode;
            $eTollSkipped = $eTollSkipped;
        } else {
            $fTollPrice = "0";
            $vTollPriceCurrencyCode = "";
            $eTollSkipped = "No";
        }

        $query1 = $q1 . " `" . $tbl_name1 . "` SET
                `iUserId` = '" . $iUserId . "',
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
				`vRiderRoomNubmer`= '" . $vRiderRoomNubmer . "',
				`eBookingFrom`= '" . $eBookingFrom . "',
				`iAdminId`= '" . $iAdminId . "',
				`iHotelBookingId`= '" . $iHotelBookingId . "',
                `iVehicleTypeId` = '" . $iVehicleTypeId . "'" . $whr;

        $obj->sql_query($query1);

        $sql = "select vName,vLastName,vEmail,iDriverVehicleId,vPhone,vcode,vLang from register_driver where iDriverId=" . $iDriverId;
        $driver_db = $obj->MySQLSelect($sql);

        $Data1['vRider'] = $email_exist[0]['vName'] . " " . $email_exist[0]['vLastName'];
        $Data1['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
        $Data1['vDriverMail'] = $driver_db[0]['vEmail'];
        $Data1['vRiderMail'] = $email_exist[0]['vEmail'];
        $Data1['vSourceAddresss'] = $vSourceAddresss;
        $Data1['tDestAddress'] = $tDestAddress;
        $Data1['dBookingdate'] = $dBooking_date;
        $Data1['vBookingNo'] = $rand_num;

        if ($edit == '1') {
            $sql = "select vBookingNo from cab_booking where `iCabBookingId` = '" . $iCabBookingId . "'";
            $cab_id = $obj->MySQLSelect($sql);
            $Data1['vBookingNo'] = $cab_id[0]['vBookingNo'];
        }

        $return = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER", $Data1);
        $return1 = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER", $Data1);

        // Start Send SMS
        $query = "SELECT vLicencePlate FROM driver_vehicle WHERE iDriverVehicleId=" . $driver_db[0]['iDriverVehicleId'];
        $db_driver_vehicles = $obj->MySQLSelect($query);

        $vPhone = $vPhone;
        $vcode = $db_con[0]['vPhoneCode'];
        $Booking_Date = @date('d-m-Y', strtotime($dBooking_date));
        $Booking_Time = @date('H:i:s', strtotime($dBooking_date));

        $query = "SELECT vPhoneCode,vLang FROM register_user WHERE iUserId=" . $iUserId;
        $db_user = $obj->MySQLSelect($query);



        $maillanguage = $db_user[0]['vLang'];

        $Pass_name = $vName . ' ' . $vLastName;
        $vcode = $db_user[0]['vPhoneCode'];
        $maildata['DRIVER_NAME'] = $Data1['vDriver'];
        $maildata['PLATE_NUMBER'] = $db_driver_vehicles[0]['vLicencePlate'];
        $maildata['BOOKING_DATE'] = $Booking_Date;
        $maildata['BOOKING_TIME'] = $Booking_Time;
        $maildata['BOOKING_NUMBER'] = $Data1['vBookingNo'];
        //Send sms to User

        //added by SP for sms functionality on 13-7-2019 start
        $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
        $PhoneCodeP = $passengerData[0]['vPhoneCode'];
        $DriverData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iDriverId AND r.vCountry = c.vCountryCode");
        $PhoneCodeD = $DriverData[0]['vPhoneCode'];

        $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE", $maildata, "", $maillanguage);
        //$return4 = $generalobj->sendUserSMS($vPhone, $vcode, $message_layout, "");
        $return4 = $generalobj->sendSystemSms($vPhone,$PhoneCodeP,$message_layout);
        //added by SP for sms functionality on 13-7-2019 start
        //Send sms to Driver

        $vPhone = $driver_db[0]['vPhone'];
        $vcode1 = $driver_db[0]['vcode'];
        $maillanguage1 = $driver_db[0]['vLang'];

        $maildata1['PASSENGER_NAME'] = $Pass_name;
        $maildata1['BOOKING_DATE'] = $Booking_Date;
        $maildata1['BOOKING_TIME'] = $Booking_Time;
        $maildata1['BOOKING_NUMBER'] = $Data1['vBookingNo'];

        $message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE", $maildata1, "", $maillanguage1);
        //$return5 = $generalobj->sendUserSMS($vPhone, $vcode1, $message_layout, "");
        $return5 = $generalobj->sendSystemSms($vPhone,$PhoneCodeD,$message_layout); //added by SP for sms functionality on 13-7-2019

        if ($iCabBookingId == "") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        echo $_SESSION['var_msg'];
        exit;

        /* if($return && $return1){
          $success = 1;
          $var_msg = "Booking Has Been Added Successfully.";
          header("location:cab_booking.php?success=1&vassign=$edit"); exit;
          } else {
          $error = 1;
          $var_msg = $langage_lbl['LBL_ERROR_OCCURED'];
          }
          header("location:cab_booking.php?success=1&vassign=$edit"); exit; */
    }
    if ($_POST['submitbuttonvalue'] == 'Book Now') {

        // add rating last trip if not given

        $Tquery = "SELECT iTripId FROM trips WHERE iUserId='" . $iUserId . "' AND iActive='Finished' ORDER BY iTripId DESC LIMIT 1";
        $GetLastTripRecord = $obj->MySQLSelect($Tquery);
        $LastTripId = $GetLastTripRecord[0]['iTripId'];

        $CSql = "SELECT iRatingId FROM ratings_user_driver WHERE iTripId ='" . $LastTripId . "' AND eUserType='Passenger'";
        $CheckRatingData = $obj->MySQLSelect($CSql);

        if (count($CheckRatingData) <= 0) {
            $Rquery = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $LastTripId . "','0',NOW(),'Passenger','')";
            $obj->sql_query($Rquery);
        }
        
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
        $dataArray['iHotelId'] = $iHotelId;

        $dataArray['iHotelBookingId'] = $iHotelBookingId;
        echo json_encode($dataArray);
        exit;
?>
        <?

    }
} else {
    header("location:cab_booking.php?success=1&vassign=$edit");
    exit;
}
        ?>
