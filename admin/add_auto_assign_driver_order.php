<?php

include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

//echo "<pre>";print_r($_POST);die;
include_once ('../include_config.php');
include_once (TPATH_CLASS . 'configuration.php');
require_once ('../assets/libraries/pubnub/autoloader.php');
require_once ('../assets/libraries/SocketCluster/autoload.php');
include_once (TPATH_CLASS . 'twilio/Services/Twilio.php');
include_once ('../generalFunctions.php');
$uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";
$iServiceId = 1;
$vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
$languageLabelsArr = $generalobj->getLanguageLabelsArr($vLangCode, "1", $iServiceId);

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

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';
$tpages = isset($_REQUEST["tpages"]) ? $_REQUEST["tpages"] : '';
$sortby = isset($_REQUEST["sortby"]) ? $_REQUEST["sortby"] : '';
$order = isset($_REQUEST["order"]) ? $_REQUEST["order"] : '';
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
$searchCompany = isset($_REQUEST["searchCompany"]) ? $_REQUEST["searchCompany"] : '';
$searchDriver = isset($_REQUEST["searchDriver"]) ? $_REQUEST["searchDriver"] : '';
$searchRider = isset($_REQUEST["searchRider"]) ? $_REQUEST["searchRider"] : '';
$searchServiceType = isset($_REQUEST["searchServiceType"]) ? $_REQUEST["searchServiceType"] : '';
$startDate = isset($_REQUEST["startDate"]) ? $_REQUEST["startDate"] : '';
$endDate = isset($_REQUEST["endDate"]) ? $_REQUEST["endDate"] : '';
$vStatus = isset($_REQUEST["vStatus"]) ? $_REQUEST["vStatus"] : '';
//echo "<pre>";print_r($_REQUEST);die;
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$eAutoAssign = isset($_POST['eAutoAssign']) ? $_POST['eAutoAssign'] : 'No';
$eStatus1 = ($eAutoAssign == 'Yes') ? 'Pending' : 'Assign';
$iOrderId = isset($_POST['iOrderId']) ? $_POST['iOrderId'] : '';
$sql = 'select * from orders where iOrderId="' . $iOrderId . '" and iStatusCode="2"';
$db_order = $obj->MySQLSelect($sql);
//echo "<pre>";print_r($db_order);die;
if (count($db_order) == 0) {
    header("location:" . $backlink);
    exit;
}
//phpinfo();die;
//echo $eStatus1;die;
if ($eStatus1 == 'Pending') {
    $trip_status = "Requesting";
    $checkOrderRequestStatusArr = checkOrderRequestStatus($iOrderId);
    //echo "<pre>";print_r($checkOrderRequestStatusArr);die;
    $action = $checkOrderRequestStatusArr['Action'];
    if ($action == 0) {
        header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
        exit;
    }
    // checkmemberemailphoneverification($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];
    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,iGcmRegId,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);

    //echo "<pre>";print_r($UserSelectedAddressArr);exit;
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING_DL'];
    $alertMsg = $userwaitinglabel;
    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $DataArr = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude, $address_data, "Yes", "No", "No", "", $DestLatitude, $DestLongitude);
    $Data = $DataArr['DriverList'];
    $driver_id_auto = "";
    if (isset($DataArr['driver_id_auto'])) {
        $driver_id_auto = $DataArr['driver_id_auto'];
    }
    //echo "<pre>";print_r($isFullWalletCharge);die;
    $fWalletDebit = $db_order[0]['fWalletDebit'];
    $fNetTotal = $db_order[0]['fNetTotal'];
    $isFullWalletCharge = "No";
    if ($fWalletDebit > 0 && $fNetTotal == 0) {
        $isFullWalletCharge = "Yes";
    }
    //echo "<pre>";print_r($isFullWalletCharge);die;
    // # Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    if ($ePaymentOption == "Cash" && $isFullWalletCharge == "No") {
        $Data_new = array();
        $Data_new = $Data;
        for ($i = 0; $i < count($Data); $i++) {
            $isRemoveFromList = "No";
            $ACCEPT_CASH_TRIPS = $Data[$i]['ACCEPT_CASH_TRIPS'];
            if ($ACCEPT_CASH_TRIPS == "No") {
                $isRemoveFromList = "Yes";
            }
            if ($isRemoveFromList == "Yes") {
                unset($Data_new[$i]);
            }
        }
        $Data = array_values($Data_new);
        for ($j = 0; $j < count($Data); $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        $driver_id_auto = trim($driver_id_auto, ",");
    } else {
        for ($j = 0; $j < count($Data); $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        $driver_id_auto = trim($driver_id_auto, ",");
    }
    // # Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    // echo "<pre>";print_r($Data);exit;
    //$sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    //$passengerData = $obj->MySQLSelect($sqlp);
    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($iUserId);
    $final_message['iCompanyId'] = strval($iCompanyId);
    $final_message['iOrderId'] = strval($iOrderId);
    $passengerFName = $Data_cab_requestcompany[0]['vCompany'];
    $final_message['PName'] = $passengerFName;
    $final_message['PPicName'] = $Data_cab_requestcompany[0]['vImgName'];
    $final_message['PRating'] = $Data_cab_requestcompany[0]['vAvgRating'];
    $final_message['PPhone'] = $Data_cab_requestcompany[0]['vPhone'];
    $final_message['PPhoneC'] = $Data_cab_requestcompany[0]['vPhoneCode'];
    $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] = $alertMsg;
    $final_message['eSystem'] = "DeliverAll";
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion,vCountry FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND tLocationUpdateDate > '$str_date' AND vAvailability='Available' AND vCountry LIKE '" . $vCountry . "'";
    $result = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($result);die;
    if (count($result) == 0 || $driver_id_auto == "" || count($Data) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "NO_CARS";
        $_SESSION['messagealert'] = "NO_CARS";
        header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
        exit;
    }
    $where = "";
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }
    $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
    $destLoc = $DestLatitude . ',' . $DestLongitude;
    $alertSendAllowed = true;
    if ($alertSendAllowed == true) {
        $deviceTokens_arr_ios = $registation_ids_new = array();
        foreach ($result as $item) {
            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            } else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
            }
            $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $item['vLang'] . "'", 'true');
            $tSessionId = $item['tSessionId'];
            $final_message['tSessionId'] = $tSessionId;
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);

            // Add User Request
            $data_userRequest = array();
            $data_userRequest['iUserId'] = $iUserId;
            $data_userRequest['iDriverId'] = $item['iDriverId'];
            $data_userRequest['tMessage'] = $msg_encode;
            $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
            $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
            $requestId = addToUserRequest2($data_userRequest);

            // Add Driver Request
            $data_driverRequest = array();
            $data_driverRequest['iDriverId'] = $item['iDriverId'];
            $data_driverRequest['iRequestId'] = $requestId;
            $data_driverRequest['iUserId'] = $iUserId;
            $data_driverRequest['iTripId'] = 0;
            $data_driverRequest['iOrderId'] = $iOrderId;
            $data_driverRequest['eStatus'] = "Timeout";
            $data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
            $data_driverRequest['vStartLatlong'] = $sourceLoc;
            $data_driverRequest['vEndLatlong'] = $destLoc;
            $data_driverRequest['tStartAddress'] = $PickUpAddress;
            $data_driverRequest['tEndAddress'] = $DestAddress;
            $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
            addToDriverRequest2($data_driverRequest);
            // addToUserRequest($passengerId,$item['iDriverId'],$msg_encode,$final_message['MsgCode']);
            // addToDriverRequest($item['iDriverId'],$passengerId,0,"Timeout");
        }
        if (count($registation_ids_new) > 0) {
            // $Rmessage = array("message" => $message);
            $Rmessage = array(
                "message" => $msg_encode
            );
            $result = send_notificationweb($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            // sendApplePushNotification(1,$deviceTokens_arr_ios,$message,$alertMsg,1);
            $result = sendApplePushNotificationweb(1, $deviceTokens_arr_ios, $msg_encode, $alertMsg, 0, "admin");
            //print_r($result);die;
        }
    }
    sleep(3);
    // if($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
    // $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
    $pubnub = new Pubnub\Pubnub(array(
        "publish_key" => $PUBNUB_PUBLISH_KEY,
        "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
        "uuid" => $uuid
    ));
    $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
    $driverIds_arr = explode(",", $filter_driver_ids);
    //Added By HJ On 01-06-2019 For Get All Driver Data Start
    $driverTripData = $obj->MySQLSelect("SELECT tSessionId,vLang,iDriverId FROM register_driver");
    $driverArr = array();
    for ($d = 0; $d < count($driverTripData); $d++) {
        $driverArr[$driverTripData[$d]['iDriverId']] = $driverTripData[$d];
    }
    //Added By HJ On 01-06-2019 For Get All Driver Data End
    //echo "<pre>";print_r($driverArr);die;
    //$message = stripslashes(preg_replace("/[\n\r]/", "", $message));
    $deviceTokens_arr_ios = $registation_ids_new = array();
    for ($j = 0; $j < count($driverIds_arr); $j++) {
        //$user_available_balance = $generalobj->get_user_available_balance($driverIds_arr[$j], "Driver"); // Added By HJ On 01-06-2019 For Check Driver Min Wallet Balance
        //if ($user_available_balance >= $WALLET_MIN_BALANCE) {
        //$sqld = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,vLang FROM register_driver WHERE iDriverId = '" . $driverIds_arr[$j] . "'";
        //$driverTripData = $obj->MySQLSelect($sqld);
        $vLang = $vLangCode;
        $tSessionId = "";
        if (isset($driverArr[$driverIds_arr[$j]])) {
            $tSessionId = $driverArr[$driverIds_arr[$j]]['tSessionId'];
            $vLang = $driverArr[$driverIds_arr[$j]]['vLang'];
        }
        //echo $tSessionId;die;
        $final_message['tSessionId'] = $tSessionId;
        $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $vLang . "'", 'true');
        $final_message['vTitle'] = $alertMsg_db;
        $msg_encode_pub = json_encode($final_message, JSON_UNESCAPED_UNICODE);
        $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$j];
        if ($PUBNUB_DISABLED == "Yes") {
            publishEventMessage($channelName, $msg_encode_pub);
        } else {
            $info = $pubnub->publish($channelName, $msg_encode_pub);
        }
        //}
    }
    header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
    exit;
} else {
    $trip_status = "Requesting";
    $checkOrderRequestStatusArr = checkOrderRequestStatus($iOrderId);
    $action = $checkOrderRequestStatusArr['Action'];
    if ($action == 0) {
        header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
        exit;
    }
    // checkmemberemailphoneverification($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];
    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,iGcmRegId,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);
    // echo "<pre>";print_r($UserSelectedAddressArr);exit;
    //$vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING_DL'];
    $alertMsg = $userwaitinglabel;
    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $DataArr = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude, $address_data, "Yes", "No", "No", "", $DestLatitude, $DestLongitude);
    $Data = $DataArr['DriverList'];
    $fWalletDebit = $db_order[0]['fWalletDebit'];
    $fNetTotal = $db_order[0]['fNetTotal'];
    $isFullWalletCharge = "No";
    if ($fWalletDebit > 0 && $fNetTotal == 0) {
        $isFullWalletCharge = "Yes";
    }

    $driver_id_auto = isset($_POST['assign_driver']) ? $_POST['assign_driver'] : '';
    if ($driver_id_auto != '') {
        // # Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
        // echo "<pre>";print_r($Data);exit;
        //$sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        //$passengerData = $obj->MySQLSelect($sqlp);
        //echo "<pre>";print_r($Data_cab_requestcompany);die;
        $final_message['Message'] = "CabRequested";
        $final_message['sourceLatitude'] = strval($PickUpLatitude);
        $final_message['sourceLongitude'] = strval($PickUpLongitude);
        $final_message['PassengerId'] = strval($iUserId);
        $final_message['iCompanyId'] = strval($iCompanyId);
        $final_message['iOrderId'] = strval($iOrderId);
        $passengerFName = $Data_cab_requestcompany[0]['vCompany'];
        $final_message['PName'] = $passengerFName;
        $final_message['PPicName'] = $Data_cab_requestcompany[0]['vImgName'];
        $final_message['PRating'] = $Data_cab_requestcompany[0]['vAvgRating'];
        $final_message['PPhone'] = $Data_cab_requestcompany[0]['vPhone'];
        $final_message['PPhoneC'] = $Data_cab_requestcompany[0]['vPhoneCode'];
        $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
        $final_message['destLatitude'] = strval($DestLatitude);
        $final_message['destLongitude'] = strval($DestLongitude);
        $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
        $final_message['vTitle'] = $alertMsg;
        $final_message['eSystem'] = "DeliverAll";
        // $final_message['Time']= strval(date('Y-m-d'));
        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
        $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
        $sql = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND tLocationUpdateDate > '$str_date' AND vAvailability='Available'";
        $result = $obj->MySQLSelect($sql);
        //echo "Res:count:".count($result);exit;
        if (count($result) == 0 || $driver_id_auto == "") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "NO_CARS";
            $_SESSION['messagealert'] = "NO_CARS";
            header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
            exit;
        }
        // $where = " iUserId = '$passengerId'";
        $where = "";
        if ($PUBNUB_DISABLED == "Yes") {
            $ENABLE_PUBNUB = "No";
        }
        $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
        $destLoc = $DestLatitude . ',' . $DestLongitude;
        $alertSendAllowed = true;
        if ($alertSendAllowed == true) {
            $deviceTokens_arr_ios = $registation_ids_new = array();
            foreach ($result as $item) {
                if ($item['eDeviceType'] == "Android") {
                    array_push($registation_ids_new, $item['iGcmRegId']);
                } else {
                    array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
                }
                //$getLabelData =$obj->MySQLSelect("SELECT vValue FROM language_label_1 WHERE vLabel='LBL_TRIP_USER_WAITING_DL' AND vCode='".$item['vLang']."'");

                $alertMsg_db = get_value('language_label_1', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $item['vLang'] . "'", 'true');
                $tSessionId = $item['tSessionId'];
                $final_message['tSessionId'] = $tSessionId;
                $final_message['vTitle'] = $alertMsg_db;
                $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
                // Add User Request
                $data_userRequest = array();
                $data_userRequest['iUserId'] = $iUserId;
                $data_userRequest['iDriverId'] = $item['iDriverId'];
                $data_userRequest['tMessage'] = $msg_encode;
                $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
                $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
                $requestId = addToUserRequest2($data_userRequest);
                // Add Driver Request
                $data_driverRequest = array();
                $data_driverRequest['iDriverId'] = $item['iDriverId'];
                $data_driverRequest['iRequestId'] = $requestId;
                $data_driverRequest['iUserId'] = $iUserId;
                $data_driverRequest['iTripId'] = 0;
                $data_driverRequest['iOrderId'] = $iOrderId;
                $data_driverRequest['eStatus'] = "Timeout";
                $data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
                $data_driverRequest['vStartLatlong'] = $sourceLoc;
                $data_driverRequest['vEndLatlong'] = $destLoc;
                $data_driverRequest['tStartAddress'] = $PickUpAddress;
                $data_driverRequest['tEndAddress'] = $DestAddress;
                $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
                addToDriverRequest2($data_driverRequest);
                // addToUserRequest($passengerId,$item['iDriverId'],$msg_encode,$final_message['MsgCode']);
                // addToDriverRequest($item['iDriverId'],$passengerId,0,"Timeout");
            }
            //print_r($deviceTokens_arr_ios);die;

            if (count($registation_ids_new) > 0) {
                // $Rmessage = array("message" => $message);
                $Rmessage = array(
                    "message" => $msg_encode
                );
                $result = send_notificationweb($registation_ids_new, $Rmessage, 0);
            }
            if (count($deviceTokens_arr_ios) > 0) {
                //sendApplePushNotification(1,$deviceTokens_arr_ios,$message,$alertMsg,1);
                $result = sendApplePushNotificationweb(1, $deviceTokens_arr_ios, $msg_encode, $alertMsg, 0, "admin");
            }
        }
        sleep(3);
        // if($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
        // $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
        $pubnub = new Pubnub\Pubnub(array(
            "publish_key" => $PUBNUB_PUBLISH_KEY,
            "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
            "uuid" => $uuid
        ));
        $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
        $driverIds_arr = explode(",", $filter_driver_ids);
        //Added By HJ On 01-06-2019 For Get All Driver Data Start
        $driverTripData = $obj->MySQLSelect("SELECT tSessionId,vLang,iDriverId FROM register_driver");
        $driverArr = array();
        for ($d = 0; $d < count($driverTripData); $d++) {
            $driverArr[$driverTripData[$d]['iDriverId']] = $driverTripData[$d];
        }
        //Added By HJ On 01-06-2019 For Get All Driver Data End
        //echo "<pre>";print_r($driverArr);die;
        //$message = stripslashes(preg_replace("/[\n\r]/", "", $message));
        $deviceTokens_arr_ios = $registation_ids_new = array();
        //echo "<pre>";print_r($driverIds_arr);die;
        for ($i = 0; $i < count($driverIds_arr); $i++) {
            //$user_available_balance = $generalobj->get_user_available_balance($driverIds_arr[$j], "Driver"); // Added By HJ On 01-06-2019 For Check Driver Min Wallet Balance
            //if ($user_available_balance >= $WALLET_MIN_BALANCE) {
            //$sqld = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,vLang FROM register_driver WHERE iDriverId = '" . $driverIds_arr[$i] . "'";
            //$driverTripData = $obj->MySQLSelect($sqld);
            $vLang = $vLangCode;
            $tSessionId = "";
            if (isset($driverArr[$driverIds_arr[$i]])) {
                $tSessionId = $driverArr[$driverIds_arr[$i]]['tSessionId'];
                $vLang = $driverArr[$driverIds_arr[$i]]['vLang'];
            }
            /* For PubNub Setting Finished */
            $final_message['tSessionId'] = $tSessionId;
            $alertMsg_db = get_value('language_label_1', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $vLang . "'", 'true');
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode_pub = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$i];
            // $info = $pubnub->publish($channelName, $message);
            // $info = $pubnub->publish($channelName, $msg_encode_pub );
            if ($PUBNUB_DISABLED == "Yes") {
                publishEventMessage($channelName, $msg_encode_pub);
            } else {
                $info = $pubnub->publish($channelName, $msg_encode_pub);
            }
            //}
        }
    }
}
$returnArr['Action'] = "1";
/*   echo json_encode($returnArr); */
if ($returnArr['Action'] == '1') {
    header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
    exit;
} else {
    header("location:" . $backlink . "?tpages=" . $tpages . $var_filter);
    exit;
}
?>