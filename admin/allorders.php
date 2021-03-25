<?php
include_once('../common.php');
include_once('../generalFunctions.php');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//echo "<pre>";print_r($_SESSION);die;
//include_once('../generalFunctions_dl_shark.php');
//include_once('../app_common_functions.php');
include_once (TPATH_CLASS . 'configuration.php');
require_once('../assets/libraries/pubnub/autoloader.php');
require_once ('../assets/libraries/SocketCluster/autoload.php');
if (!isset($generalobjAdmin)) {
	require_once(TPATH_CLASS . "class.general_admin.php");
	$generalobjAdmin = new General_admin();
}
$default_lang = $generalobj->get_default_lang();
$order_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$script = $order_type == 'processing' ? "Processing Orders" : "All Orders";
$eSystem = " AND eSystem = 'DeliverAll'";
if ($order_type == 'processing' && !$userObj->hasPermission('view-processing-orders')) {
	$userObj->redirect();
} else if ($order_type != 'processing' && !$userObj->hasPermission('view-all-orders')) {
	$userObj->redirect();
}
//data for select fields
$ssqlsc = " AND iServiceId IN(".$enablesevicescategory.")";
$sql = "select iCompanyId,vCompany,vEmail from company WHERE eStatus != 'Deleted' $eSystem $ssqlsc order by vCompany";
$db_company = $obj->MySQLSelect($sql);

$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);

$sql = "select iUserId,CONCAT(vName,' ',vLastName) AS riderName,vEmail from register_user WHERE eStatus != 'Deleted' order by vName";
$db_rider = $obj->MySQLSelect($sql);
//data for select fields

$sql = "select iOrderStatusId,vStatus,iStatusCode  from order_status";
$orderStatus = $obj->MySQLSelect($sql);

$processing_status_array = array('1', '2', '4', '5','12');
$all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12');

if (isset($_REQUEST['iStatusCode']) && $_REQUEST['iStatusCode'] != '') {
	$all_status_array = array($_REQUEST['iStatusCode']);
}
if ($order_type == 'processing') {
	$iStatusCode = '(' . implode(',', $processing_status_array) . ')';
} else {
	$iStatusCode = '(' . implode(',', $all_status_array) . ')';
}
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';

$ord = ' ORDER BY o.iOrderId DESC';
if ($sortby == 1) {
	if ($order == 0)
		$ord = " ORDER BY o.tOrderRequestDate ASC";
	else
		$ord = " ORDER BY o.tOrderRequestDate DESC";
}

if ($sortby == 2) {
	if ($order == 0)
		$ord = " ORDER BY riderName ASC";
	else
		$ord = " ORDER BY riderName DESC";
}

if ($sortby == 3) {
	if ($order == 0)
		$ord = " ORDER BY c.vCompany ASC";
	else
		$ord = " ORDER BY c.vCompany DESC";
}

if ($sortby == 4) {
	if ($order == 0)
		$ord = " ORDER BY driverName ASC";
	else
		$ord = " ORDER BY driverName DESC";
}

//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';

$searchOrderStatus = isset($_REQUEST['searchOrderStatus']) ? $_REQUEST['searchOrderStatus'] : '';

if ($startDate != '') {
	$ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
}
if ($endDate != '') {
	$ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
}
if ($serachTripNo != '') {
	$ssql .= " AND o.vOrderNo ='" . $serachTripNo . "'";
}
if ($searchCompany != '') {
	$ssql .= " AND c.iCompanyId ='" . $searchCompany . "'";
}
if ($searchDriver != '') {
	$ssql .= " AND d.iDriverId ='" . $searchDriver . "'";
}
if ($searchRider != '') {
	$ssql .= " AND o.iUserId ='" . $searchRider . "'";
}
if ($searchServiceType != '') {
	$ssql .= " AND sc.iServiceId ='" . $searchServiceType . "'";
}
if ($searchOrderStatus != '') {
	$ssql .= " AND o.iStatusCode ='" . $searchOrderStatus . "'";
}

$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
	$trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
}
if (!empty($promocode) && isset($promocode)) {
	$ssql .= " AND o.vCouponCode LIKE '" . $promocode . "' AND o.iStatusCode=6";
}
$ssql .= " AND c.iServiceId IN(".$enablesevicescategory.")";
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(o.iOrderId) AS Total FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId=o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceId=o.iServiceId WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.iStatusCode IN $iStatusCode $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
//total pages we going to have
$total_pages = ceil($total_results / $per_page);
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    //it will telles the current page
	$show_page = $_GET['page'];
	if ($show_page > 0 && $show_page <= $total_pages) {
		$start = ($show_page - 1) * $per_page;
		$end = $start + $per_page;
	}
}

// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
	$page = 1;
//Pagination End
$sql = "SELECT o.fTotalGenerateFare,o.iOrderId, o.fSubTotal,o.iServiceid,sc.vServiceName_" . $default_lang . " as vServiceName,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.vTimeZone,o.vOrderNo,o.iUserId,o.iUserAddressId,u.vCountry,o.dDeliveryDate,o.tOrderRequestDate,o.ePayWallet,o.ePaymentOption,o.tOrderRequestDate,o.fNetTotal,os.vStatus ,CONCAT(u.vName,' ',u.vLastName) AS riderName,o.iDriverId,o.iCompanyId, CONCAT(d.vName,' ',d.vLastName) AS driverName,c.vCompany,c.eAutoaccept,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = o.iOrderId) as TotalItem,CONCAT('<b>Phone: </b> +',u.vPhoneCode,' ',u.vPhone)  as user_phone,CONCAT('<b>Phone: </b> +',d.vCode,' ',d.vPhone) as driver_phone,CONCAT('<b>Phone: </b> +',c.vCode,' ',c.vPhone) as resturant_phone,o.eTakeaway FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.iStatusCode IN $iStatusCode $ssql $trp_ssql $ord LIMIT $start, $per_page";

$DBProcessingOrders = $obj->MySQLSelect($sql);
//echo "<pre>";print_r($DBProcessingOrders);die;
$endRecord = count($DBProcessingOrders);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
	if ($key != "tpages" && $key != 'page')
		$var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));
$monday = date('Y-m-d', strtotime('sunday this week -1 week'));
$sunday = date('Y-m-d', strtotime('saturday this week'));
$Pmonday = date('Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date('Y-m-d', strtotime('saturday this week -1 week'));
if ($action == 'cancel' && $hdn_del_id != '') {
	if(SITE_TYPE=='Demo') {
		$_SESSION['success'] = 2;
		$_SESSION['var_msg'] = $langage_lbl_admin['LBL_EDIT_DELETE_RECORD'];
		echo "<script>location.href='allorders.php?type=" . $order_type . "'</script>";
		exit;
	}
	$vCancelReason = isset($_REQUEST['cancel_reason']) ? $_REQUEST['cancel_reason'] : '';
	$fCancellationCharge = isset($_REQUEST['fCancellationCharge']) ? $_REQUEST['fCancellationCharge'] : '';
	$fDeliveryCharge = isset($_REQUEST['fDeliveryCharge']) ? $_REQUEST['fDeliveryCharge'] : '';
	$fRestaurantPayAmount = isset($_REQUEST['fRestaurantPayAmount']) ? $_REQUEST['fRestaurantPayAmount'] : '';
	$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
	$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
	$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
	$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
	$vIP = $generalobj->get_client_ip();
	$oSql = "SELECT fWalletDebit,iUserId,vOrderNo,ePaymentOption,fNetTotal FROM orders WHERE iOrderId = '" . $hdn_del_id . "'";
	$wallet_data = $obj->MySQLSelect($oSql);
	$sqld = "SELECT rd.vCurrencyPassenger,cu.vSymbol FROM register_user as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iUserId = '" . $iUserId . "'";
	$userCurData = $obj->MySQLSelect($sqld);
	$currencySymbol = $userCurData[0]['vSymbol'];
	$currencycode = $userCurData[0]['vCurrencyPassenger'];
	$userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $currencycode, '', 'true');
	if ($currencySymbol == "" || $currencySymbol == NULL) {
		$currencySymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
	}
	if ($currencycode == "" || $currencycode == NULL) {
		$userCurrencyRatio = get_value('currency', 'Ratio', 'eDefault', 'Yes', '', 'true');
	}
	$fCancellationChargeCur = $currencySymbol . $generalobj->setTwoDecimalPoint($fCancellationCharge * $userCurrencyRatio);
	$oSql = "SELECT fWalletDebit,iUserId,vOrderNo,ePaymentOption,fNetTotal,vCouponCode FROM orders WHERE iOrderId = '" . $hdn_del_id . "'";
	$wallet_data = $obj->MySQLSelect($oSql);
	if ($wallet_data[0]['fWalletDebit'] > 0) {
		$iUserId = $wallet_data[0]['iUserId'];
		$iBalance = $wallet_data[0]['fWalletDebit'];
		$vOrderNo = $wallet_data[0]['vOrderNo'];
		$eFor = 'Deposit';
		$eType = 'Credit';
		$tDescription = "#LBL_CREDITED_BOOKING_DL#" . $vOrderNo;
		$ePaymentStatus = 'Unsettelled';
		$dDate = Date('Y-m-d H:i:s');
		$eUserType = 'Rider';
		$generalobj->InsertIntoUserWallet($iUserId, $eUserType, $iBalance, $eType, $hdn_del_id, $eFor, $tDescription, $ePaymentStatus, $dDate);
	}
	
	//added by SP on 27-06-2020, promocode usage limit increase..bcz it is done only when order finished..so when cancel that order then other user use it..that is wrong so put it...
    $vCouponCode = $wallet_data[0]['vCouponCode'];
    //echo $vCouponCode;exit;
    if ($vCouponCode != '') {
        $sql = "SELECT iUsed, iUsageLimit from coupon WHERE vCouponCode = '" . $vCouponCode . "'";
        $coupon_result = $obj->MySQLSelect($sql);
        //print_R($coupon_result); exit;
        $noOfCouponUsed = $coupon_result[0]['iUsed'];
        $iUsageLimit = $coupon_result[0]['iUsageLimit'];
        $where = " vCouponCode = '" . $vCouponCode . "'";
        $data_coupon['iUsed'] = $noOfCouponUsed + 1;
        $obj->MySQLQueryPerform("coupon", $data_coupon, 'update', $where);
        ## Check Coupon Code Usage Limit , Send Email to Admin if Usage  Limit is over ##
        $UpdatedCouponUsedNo = $noOfCouponUsed + 1;
        if ($iUsageLimit == $UpdatedCouponUsedNo) {
            $maildata['vCouponCode'] = $vCouponCode;
            $maildata['iUsageLimit'] = $iUsageLimit;
            $maildata['COMPANY_NAME'] = $COMPANY_NAME;
            $mail = $generalobj->send_email_user('COUPON_LIMIT_COMPLETED_TO_ADMIN', $maildata);
        }
        ## Check Coupon Code Usage Limit , Send Email to Admin if Usage  Limit is over ##
    }
    //added by SP end
	
	
	$query = "UPDATE orders SET iStatusCode = '8' , eCancelledBy= 'Admin' ,fCancellationCharge = '" . $fCancellationCharge . "',fRestaurantPayAmount = '" . $fRestaurantPayAmount . "' ,vCancelReason='" . $vCancelReason . "' WHERE iOrderId = '" . $hdn_del_id . "'";
	$obj->sql_query($query);

	$lquery = "INSERT INTO `order_status_logs`(`iOrderId`, `iStatusCode`, `dDate`, `vIp`) VALUES ('" . $hdn_del_id . "','8',Now(),'" . $vIP . "')";
	$obj->sql_query($lquery);
    //if($wallet_data[0]['ePaymentOption'] != 'Card' &&  $wallet_data[0]['fNetTotal'] > 0 ){
	if ($fCancellationCharge > 0) {
		$query_trip_outstanding_amount = "INSERT INTO `trip_outstanding_amount`(`iOrderId`, `iTripId`, `iUserId`, `iDriverId`,`iCompanyId`,`fCancellationFare`,`fPendingAmount`) VALUES ('" . $hdn_del_id . "','" . $iTripId . "','" . $iUserId . "','" . $iDriverId . "','" . $iCompanyId . "','" . $fCancellationCharge . "','" . $fCancellationCharge . "')";
		$last_insert_id = $obj->MySQLInsert($query_trip_outstanding_amount);
		$sql = "SELECT * FROM currency WHERE eStatus = 'Active'";
		$db_curr = $obj->MySQLSelect($sql);
		$where = "iTripOutstandId = '" . $last_insert_id . "'";
		for ($i = 0; $i < count($db_curr); $i++) {
			$data_currency_ratio['fRatio_' . $db_curr[$i]['vName']] = $db_curr[$i]['Ratio'];
			$obj->MySQLQueryPerform("trip_outstanding_amount", $data_currency_ratio, 'update', $where);
		}
	}
	
	/* added by PM for Auto credit wallet driver on 25-01-2020 start */

	if (checkAutoCreditDriverModule()) {
		$data=array();
		$data['iOrderId']= $hdn_del_id;
		$data['fDeliveryCharge']= $fDeliveryCharge;				
		AutoCreditWalletDriver($data);
		
	}
	
	/* added by PM for Auto credit wallet driver on 25-01-2020 end */

	$query_driverPayment = "UPDATE trips SET  fDeliveryCharge ='" . $fDeliveryCharge . "' WHERE iOrderId = '" . $hdn_del_id . "'";
	$obj->sql_query($query_driverPayment);
	$uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";
    //$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    //$PUBNUB_DISABLED = $generalobj->getConfigurations("configurations", "PUBNUB_DISABLED");
    //$PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_PUBLISH_KEY");
    //$PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations", "PUBNUB_SUBSCRIBE_KEY");
    ## Send Notification To User  ##
	$MessageUser = "OrderCancelByAdmin";
	$sql = "SELECT ru.iUserId,ru.iGcmRegId,ru.eDeviceType,ru.tSessionId,ru.iAppVersion,ru.vLang,ord.vOrderNo,ord.iOrderId FROM orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $hdn_del_id . "'";
	$user_data_order = $obj->MySQLSelect($sql);
	$vLangCodeuser = $user_data_order[0]['vLang'];
	$vOrderNoUser = $user_data_order[0]['vOrderNo'];
	$iOrderIdUser = $user_data_order[0]['iOrderId'];
	$iUserIdNew = $user_data_order[0]['iUserId'];
	if ($vLangCodeuser == "" || $vLangCodeuser == NULL) {
		$vLangCodeuser = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
	}
    // $languageLabelsArrUser = getLanguageLabelsArr($vLangCodeuser, "1");
	$vTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
	$alertMsgUser = $langage_lbl_admin['LBL_CANCEL_ORDER_ADMIN_TXT'] . " #" . $vOrderNoUser . " " . $langage_lbl_admin['LBL_REASON_TXT'] . " " . $vTitleReasonMessage;
	$message_arrUser = array();
	$message_arrUser['Message'] = $MessageUser;
	$message_arrUser['iOrderId'] = $iOrderIdUser;
	$message_arrUser['vOrderNo'] = $vOrderNoUser;
	$message_arrUser['vTitle'] = $alertMsgUser;
	$message_arrUser['tSessionId'] = $user_data_order[0]['tSessionId'];
	$message_arrUser['eSystem'] = 'DeliverAll';
	$messageUser = json_encode($message_arrUser, JSON_UNESCAPED_UNICODE);
	if ($PUBNUB_DISABLED == "Yes") {
		$ENABLE_PUBNUB = "No";
	}
	$alertSendAllowedUser = true;
	/* For PubNub Setting */
	$iAppVersionUser = $user_data_order[0]['iAppVersion'];
	$eDeviceTypeUser = $user_data_order[0]['eDeviceType'];
	$iGcmRegIdUser = $user_data_order[0]['iGcmRegId'];
	$tSessionIdUser = $user_data_order[0]['tSessionId'];
	$registatoin_ids_User = $iGcmRegIdUser;
	/* For PubNub Setting Finished */
	$deviceTokens_arr_ios_user = $registation_ids_new_user = array();
	if ($alertSendAllowedUser == true) {
		if ($eDeviceTypeUser == "Android") {
			array_push($registation_ids_new_user, $iGcmRegIdUser);
			$Rmessage = array("message" => $messageUser);
			$result = send_notification($registation_ids_new_user, $Rmessage, 0);
		} else {
			array_push($deviceTokens_arr_ios_user, $iGcmRegIdUser);
			sendApplePushNotification(0, $deviceTokens_arr_ios_user, $messageUser, $alertMsgUser, 0);
		}
	}
	$channelNameUser = "PASSENGER_" . $iUserIdNew;
	if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
		$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
		$info = $pubnub->publish($channelNameUser, $messageUser);
	} else {
		publishEventMessage($channelNameUser, $messageUser);
	}
    ## Send Notification To User ## 
    ## Send Notification To Restaurant  ##
	$Message = "OrderCancelByAdmin";
	$sql = "select c.iCompanyId,c.iGcmRegId,c.eDeviceType,c.tSessionId,c.iAppVersion,c.vLang,o.vOrderNo from orders as o LEFT JOIN company as c ON o.iCompanyId=c.iCompanyId where o.iOrderId = '" . $hdn_del_id . "'";
	$Resdata_order = $obj->MySQLSelect($sql);

	$ResLangCode = $Resdata_order[0]['vLang'];
	$ResOrderNo = $Resdata_order[0]['vOrderNo'];
	$iCompanyId = $Resdata_order[0]['iCompanyId'];
	if ($ResLangCode == "" || $ResLangCode == NULL) {
		$ResLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
	}
	$ResTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
    //$ReslanguageLabelsArr = getLanguageLabelsArr($ResLangCode, "1");
	$ResAlertMsg = $langage_lbl_admin['LBL_CANCEL_ORDER_ADMIN_TXT'] . " #" . $ResOrderNo . " " . $langage_lbl_admin['LBL_REASON_TXT'] . " " . $ResTitleReasonMessage;
	$message_arr_res = array();
	$message_arr_res['Message'] = $Message;
	$message_arr_res['iOrderId'] = $hdn_del_id;
	$message_arr_res['vOrderNo'] = $ResOrderNo;
	$message_arr_res['eSystem'] = 'DeliverAll';
	$message_arr_res['vTitle'] = $ResAlertMsg;
	$message_arr_res['tSessionId'] = $Resdata_order[0]['tSessionId'];
	$restaurantmessage = json_encode($message_arr_res, JSON_UNESCAPED_UNICODE);
	if ($PUBNUB_DISABLED == "Yes") {
		$ENABLE_PUBNUB = "No";
	}

	$alertSendAllowed = true;
	/* For PubNub Setting */
	$iAppVersion = $Resdata_order[0]['iAppVersion'];
	$eDeviceType = $Resdata_order[0]['eDeviceType'];
	$iGcmRegId = $Resdata_order[0]['iGcmRegId'];
	$tSessionId = $Resdata_order[0]['tSessionId'];
	$registatoin_ids = $iGcmRegId;
	$restaurantdeviceTokens_arr_ios = $restuarantregistation_ids_new = array();
	/* For PubNub Setting Finished */
	$RestaurantchannelName = "COMPANY_" . $iCompanyId;
	if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
		$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
		$info = $pubnub->publish($RestaurantchannelName, $restaurantmessage);
	} else {
		publishEventMessage($RestaurantchannelName, $restaurantmessage);
	}

	if ($alertSendAllowed == true) {
		if ($eDeviceType == "Android") {
			array_push($restuarantregistation_ids_new, $iGcmRegId);
			$Rmessage = array("message" => $restaurantmessage);
			$result = send_notification($restuarantregistation_ids_new, $Rmessage, 0);
		} else {
			array_push($restaurantdeviceTokens_arr_ios, $iGcmRegId);
			sendApplePushNotification(2, $restaurantdeviceTokens_arr_ios, $restaurantmessage, $alertMsg, 0);
		}
	}
    ## Send Notification To Restaurant  ##
    ## Send Notification To Driver ##
	$query1 = "select * from order_status_logs where iOrderId = '" . $hdn_del_id . "' AND iStatusCode = '4'";
	$OrdersData = $obj->MySQLSelect($query1);
	if (count($OrdersData) > 0) {
		$Message = "OrderCancelByAdmin";
		$sql = "select d.iDriverId,d.iGcmRegId,d.eDeviceType,d.tSessionId,d.iAppVersion,d.vLang,o.vOrderNo from orders as o LEFT JOIN register_driver as d ON o.iDriverId=d.iDriverId where o.iOrderId = '" . $hdn_del_id . "'";
		$drv_data_order = $obj->MySQLSelect($sql);
		$drvLangCode = $drv_data_order[0]['vLang'];
		$drvOrderNo = $drv_data_order[0]['vOrderNo'];
		$iDriverId = $drv_data_order[0]['iDriverId'];

		$query1 = "UPDATE register_driver SET vTripStatus = 'Cancelled' WHERE iDriverId = '" . $iDriverId . "'";
		$obj->sql_query($query1);

		$query2 = "UPDATE trips SET iActive = 'Canceled' WHERE iOrderId = '" . $hdn_del_id . "'";
		$obj->sql_query($query2);

		if ($drvLangCode == "" || $drvLangCode == NULL) {
			$drvLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
		}

		$drvTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : '';
        //$drvlanguageLabelsArr = getLanguageLabelsArr($drvLangCode, "1");
		$drvAlertMsg = $langage_lbl_admin['LBL_CANCEL_ORDER_ADMIN_TXT'] . " #" . $drvOrderNo . " " . $langage_lbl_admin['LBL_REASON_TXT'] . " " . $drvTitleReasonMessage;
		$message_arr_res = array();
		$message_arr_res['Message'] = $Message;
		$message_arr_res['iOrderId'] = $hdn_del_id;
		$message_arr_res['vOrderNo'] = $drvOrderNo;
		$message_arr_res['eSystem'] = 'DeliverAll';
		$message_arr_res['vTitle'] = $drvAlertMsg;
		$message_arr_res['tSessionId'] = $drv_data_order[0]['tSessionId'];
		$drvmessage = json_encode($message_arr_res, JSON_UNESCAPED_UNICODE);
		if ($PUBNUB_DISABLED == "Yes") {
			$ENABLE_PUBNUB = "No";
		}
		$alertSendAllowed = true;
		/* For PubNub Setting */
		$iAppVersion = $drv_data_order[0]['iAppVersion'];
		$eDeviceType = $drv_data_order[0]['eDeviceType'];
		$iGcmRegId = $drv_data_order[0]['iGcmRegId'];
		$tSessionId = $drv_data_order[0]['tSessionId'];
		$registatoin_ids = $iGcmRegId;
		$drvdeviceTokens_arr_ios = $drvregistation_ids_new = array();
		/* For PubNub Setting Finished */
		$DriverchannelName = "DRIVER_" . $iDriverId;
		if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
			$pubnub = new Pubnub\Pubnub(array("publish_key" => $PUBNUB_PUBLISH_KEY, "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY, "uuid" => $uuid));
			$info = $pubnub->publish($DriverchannelName, $drvmessage);
		} else {
			publishEventMessage($DriverchannelName, $drvmessage);
		}
		if ($alertSendAllowed == true) {
			if ($eDeviceType == "Android") {
				array_push($drvregistation_ids_new, $iGcmRegId);
				$Dmessage = array("message" => $drvmessage);
				$result = send_notification($drvregistation_ids_new, $Dmessage, 0);
			} else {
				array_push($drvdeviceTokens_arr_ios, $iGcmRegId);
				sendApplePushNotification(1, $drvdeviceTokens_arr_ios, $drvmessage, $alertMsg, 0);
			}
		}
	}

    ## Send Notification To Driver ##

	$sql1 = "SELECT tOrderRequestDate,vOrderNo,iUserId,iDriverId,iCompanyId FROM orders WHERE iOrderId=" . $hdn_del_id;
	$bookind_detail = $obj->MySQLSelect($sql1);
	$tOrderRequestDateMail = $vOrderNoMail = "";
	if (count($bookind_detail) > 0) {
		$tOrderRequestDateMail = $bookind_detail[0]['tOrderRequestDate'];
		$vOrderNoMail = $bookind_detail[0]['vOrderNo'];
	}
	$sql2 = "SELECT vName,vLastName,vEmail,iDriverVehicleId,vPhone,vcode,vLang FROM register_driver WHERE iDriverId=" . $iDriverId;
	$driver_db = $obj->MySQLSelect($sql2);
	$vDriverEmail = $driverFullname = $vPhone = $vcode = $vLang = "";
	if (count($driver_db) > 0) {
		$vPhone = $driver_db[0]['vPhone'];
		$vcode = $driver_db[0]['vcode'];
		$vLang = $driver_db[0]['vLang'];
		$driverFullname = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
		$vDriverEmail = $driver_db[0]['vEmail'];
	}
	$SQL3 = "SELECT vName,vLastName,vEmail,iUserId,vPhone,vPhoneCode,vLang FROM register_user WHERE iUserId = '" . $iUserId . "'";
	$user_detail = $obj->MySQLSelect($SQL3);
	$vPhone1 = $vcode1 = $vLang1 = $vEmail1 = $userFullname = "";
	if (count($user_detail) > 0) {
		$vPhone1 = $user_detail[0]['vPhone'];
		$vcode1 = $user_detail[0]['vPhoneCode'];
		$vLang1 = $user_detail[0]['vLang'];
		$vEmail1 = $user_detail[0]['vEmail'];
		$userFullname = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
	}

	$sql4 = "select vCompany,vEmail,vPhone,vcode,vLang,vRestuarantLocation from company where iCompanyId='" . $iCompanyId . "'";
	$comapny_detail = $obj->MySQLSelect($sql4);
	$vLang2 = $default_lang;
	$vPhone = $vcode = $vCompany = $vEmail2 = $vSourceAddresss = "";
	if (count($comapny_detail) > 0) {
		$vPhone = $comapny_detail[0]['vPhone'];
		$vcode = $comapny_detail[0]['vcode'];
		$vLang2 = $comapny_detail[0]['vLang'];
		$vCompany = $comapny_detail[0]['vCompany'];
		$vEmail2 = $comapny_detail[0]['vEmail'];
		$vSourceAddresss = $comapny_detail[0]['vRestuarantLocation'];
	}

    //added by SP for emailissue on 3-7-2019 start
	$Data['ProjectName'] = $SITE_NAME;
	$Data['vOrderNo'] = $vOrderNoMail;
	$Data['MSG'] = $vCancelReason;
	$Data['Charge'] = $fCancellationChargeCur;

	if ($iDriverId != 0 && $iDriverId > 0) {
		$Data['vEmail'] = $vDriverEmail;
		$Data['UserName'] = $driverFullname;
		$return = $generalobj->send_email_user("MANUAL_CANCEL_ORDER_ADMIN_TO_DRIVER_COMPANY", $Data);
	}

	$Data['vEmail'] = $vEmail2;
	$Data['UserName'] = $vCompany;
	$return1 = $generalobj->send_email_user("MANUAL_CANCEL_ORDER_ADMIN_TO_DRIVER_COMPANY", $Data);

	$Data['vEmail'] = $vEmail1;
	$Data['UserName'] = $userFullname;
	$return1 = $generalobj->send_email_user("MANUAL_CANCEL_ORDER_ADMIN_TO_RIDER", $Data);

	/* added by SP for Mail on 3-7-2019 end */

	$Booking_Date = @date('d-m-Y', strtotime($tOrderRequestDateMail));
	$Booking_Time = @date('H:i:s', strtotime($tOrderRequestDateMail));

	$maildata['vDriver'] = $driverFullname;
	$maildata['dBookingdate'] = $Booking_Date;
	$maildata['dBookingtime'] = $Booking_Time;
	$maildata['vBookingNo'] = $vOrderNoMail;

	$maildata1['vRider'] = $userFullname;
	$maildata1['dBookingdate'] = $Booking_Date;
	$maildata1['dBookingtime'] = $Booking_Time;
	$maildata1['vBookingNo'] = $vOrderNoMail;

	$maildataCompany['vCompany'] = $vCompany;
	$maildataCompany['dBookingdate'] = $Booking_Date;
	$maildataCompany['dBookingtime'] = $Booking_Time;
	$maildataCompany['vBookingNo'] = $vOrderNoMail;
	if ($iDriverId != 0 && $iDriverId > 0) {
		$message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE_JOB_CANCEL", $maildata1, "", $vLang);
        //$return5 = $generalobj->sendUserSMS($vPhone,$vcode,$message_layout,"");
        //$result = $generalobj->sendSystemSms($vPhone,$PhoneCode,$message);
	}
	if ($iUserId != 0 && $iUserId > 0) {
		$message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_JOB_CANCEL", $maildata, "", $vLang1);
	}
    //$return4 = $generalobj->sendUserSMS($vPhone1,$vcode1,$message_layout,"");
    //$result = $generalobj->sendSystemSms($vPhone1,$PhoneCode,$message);
	if ($iCompanyId > 0) {
        //$message_layout = $generalobj->send_messages_user("COMPANY_SEND_MESSAGE_JOB_CANCEL", $maildataCompany, "", $vLang2);
		$message_layout = $generalobj->send_messages_user("SEND_MESSAGE_JOB_CANCEL_BY_ADMIN", $maildataCompany, "", $vLang2);
	}
    //$return6 = $generalobj->sendUserSMS($vPhone2,$vcode2,$message_layout,"");
    //$result = $generalobj->sendSystemSms($vPhone2,$PhoneCode,$message);
	echo "<script>location.href='allorders.php?type=" . $order_type . "'</script>";
}

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
############################################################## Get publishEventMessage ###############################################################################################################

function publishEventMessage123($channelName, $message) {
	global $tconfig, $ENABLE_SOCKET_CLUSTER, $socketClsObj, $websocket;
	if ($ENABLE_SOCKET_CLUSTER == "Yes") {
		$optionsOrUri = ['secure' => false, 'host' => $tconfig['tsite_sc_host'], 'port' => $tconfig['tsite_host_sc_port'], 'path' => $tconfig['tsite_host_sc_path']];
		if (empty($socketClsObj)) {
			$optionsOrUri = ['secure' => false, 'host' => $tconfig['tsite_sc_host'], 'port' => $tconfig['tsite_host_sc_port'], 'path' => $tconfig['tsite_host_sc_path']];
			$websocket = \SocketCluster\WebSocket::factory($optionsOrUri);
			$socket = new \SocketCluster\SocketCluster($websocket);
			$socketClsObj = $socket;
		} else {
			$socket = $socketClsObj;
		}
		$dataCHK = $socket->publish($channelName, $message);
	}
	return true;
}

############################################################## Get publishEventMessage ###############################################################################################################
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
	<meta charset="UTF-8" />
	<title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_PROCESSING_ORDERS']; ?></title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<?php include_once('global_files.php'); ?>

</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 " >
	<!-- Main LOading -->
	<!-- MAIN WRAPPER -->
	<div id="wrap">
		<?php include_once('header.php'); ?>
		<?php include_once('left_menu.php'); ?>
		<!--PAGE CONTENT -->
		<div id="content">
			<div class="inner">
				<div id="add-hide-show-div">
					<div class="row">
						<div class="col-lg-12">
							<h2><?= $script; ?> </h2>
						</div>
					</div>
					<hr />
				</div>
				<?php include('valid_msg.php'); ?>
				<!--  Search Form Start  -->
				<form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
					<div class="Posted-date mytrip-page payment-report">
						<input type="hidden" name="action" value="search" />
						<input type="hidden" name="type" value="<?= $order_type; ?>" />
						<h3>Search <?= $langage_lbl_admin['LBL_PROCESSING_ORDERS']; ?> ...</h3>
						<span>
							<a onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
							<a onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
							<a onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
							<a onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
							<a onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
							<a onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
							<a onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
							<a onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
						</span> 
						<span>
							<input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default;background-color: #fff" />
							<input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default;background-color: #fff"/>
							<div class="col-lg-2 select001">
								<select class="form-control filter-by-text" name = "searchCompany" id="searchCompany" data-text="Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>">
									<option value="">Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></option>
									<?php foreach ($db_company as $dbc) { ?>
										<option value="<?= $dbc['iCompanyId']; ?>" <?php
										if ($searchCompany == $dbc['iCompanyId']) {
											echo "selected";
										}
										?>><?= $generalobjAdmin->clearCmpName($dbc['vCompany']); ?> - ( <?= $generalobjAdmin->clearEmail($dbc['vEmail']); ?> )</option>
									<?php } ?>
								</select>
							</div>
							<div class="col-lg-2 select001">
								<select class="form-control filter-by-text" name = "searchRider" data-text="Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>">
									<option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
									<?php foreach ($db_rider as $dbr) { ?>
										<option value="<?= $dbr['iUserId']; ?>" <?php
										if ($searchRider == $dbr['iUserId']) {
											echo "selected";
										}
										?>><?= $generalobjAdmin->clearName($dbr['riderName']); ?> - ( <?= $generalobjAdmin->clearEmail($dbr['vEmail']); ?> )</option>
									<?php } ?>
								</select>
							</div>
						</span>
					</div>
					<div class="mytrip-page payment-report payment-report1">
						<span>
							<div class="col-lg-2 select001" style="padding-right:15px;">
								<select class="form-control filter-by-text driver_container" name = 'searchDriver' data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>">
									<option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
									<?php foreach ($db_drivers as $dbd) { ?>
										<option value="<?= $dbd['iDriverId']; ?>" <?php
										if ($searchDriver == $dbd['iDriverId']) {
											echo "selected";
										}
										?>><?= $generalobjAdmin->clearName($dbd['driverName']); ?> - ( <?= $generalobjAdmin->clearEmail($dbd['vEmail']); ?> )</option>
									<?php } ?>
								</select>
							</div>
							<?php if (count($allservice_cat_data) > 1) { ?>
								<div class="col-lg-2 select001" style="padding-right:15px;">
									<select class="form-control filter-by-text" name = "searchServiceType" data-text="Select Serivce Type">
										<option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
										<?php foreach ($allservice_cat_data as $value) { ?>
											<option value="<?= $value['iServiceId']; ?>" <?php
											if ($searchServiceType == $value['iServiceId']) {
												echo "selected";
											}
											?>><?= $generalobjAdmin->clearName($value['vServiceName']); ?></option>
										<?php } ?>
									</select>
								</div>
							<?php } ?>
							<div class="col-lg-2" style="padding-right:15px;">
								<input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> Number" class="form-control search-trip001" value="<?= $serachTripNo; ?>"/>
							</div>

							
							<div class="col-lg-2 select001" >
								<select class="form-control filter-by-text" name = "searchOrderStatus" data-text="Select Order Status">
									<option value="">Select Order Status</option>
									<?php foreach ($orderStatus as $value) { ?>
										<option value="<?= $value['iStatusCode']; ?>" <?php
										if ($searchOrderStatus == $value['iStatusCode']) {
											echo "selected";
										}
										?>><?= $generalobjAdmin->clearName($value['vStatus']); ?></option>
									<?php } ?>
								</select>
							</div>
							

						</span>
					</div>
					<div class="tripBtns001">
						<b>
							<input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
							<input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'allorders.php?type=<?= $order_type ?>'"/>
						</b>
					</div>
				</form>
				<!-- Search Form End -->
				<div class="table-list">
					<div class="row">
						<div class="col-lg-12">
							<div class="table-responsive">
								<form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
									<table class="table table-striped table-bordered table-hover" >
										<thead>
											<tr>

												<?php if (count($allservice_cat_data) > 1) { ?>
													<th class="text-center">Serivce Type</th>
												<?php } ?>

												<th class="text-center"><?= $langage_lbl_admin['LBL_ORDER_NO_ADMIN']; ?>#</th>

												<th class="text-center"><a href="javascript:void(0);" onClick="Redirect(1,<?php
												if ($sortby == '1') {
													echo $order;
													} else {
														?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN_DL']; ?> <?php
														if ($sortby == 1) {
															if ($order == 0) {
																?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
															}
														} else {
															?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

															<th><a href="javascript:void(0);" onClick="Redirect(2,<?php
															if ($sortby == '2') {
																echo $order;
																} else {
																	?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> Name <?php
																	if ($sortby == 2) {
																		if ($order == 0) {
																			?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
																		}
																	} else {
																		?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

																		<th><a href="javascript:void(0);" onClick="Redirect(3,<?php
																		if ($sortby == '3') {
																			echo $order;
																			} else {
																				?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Name <?php
																				if ($sortby == 3) {
																					if ($order == 0) {
																						?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
																					}
																				} else {
																					?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

																					<th><a href="javascript:void(0);" onClick="Redirect(4,<?php
																					if ($sortby == '4') {
																						echo $order;
																						} else {
																							?>0<?php } ?>)">Delivery <?php
																							echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
																							if ($sortby == 4) {
																								if ($order == 0) {
																									?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
																								}
																							} else {
																								?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

																								<th class="text-right">Order Total</th>
																								<!--<th>Service Type</th>-->
																								<th class="text-center">Order Status</th>
																								<th class="text-center">Payment Mode</th>
																								<?php if ($userObj->hasPermission('cancel-orders')) { ?>
																									<th class="text-center">Action</th> </tr>
																								<?php } ?>
																							</thead>
																							<tbody>
																								<?php
																								if (!empty($DBProcessingOrders)) {
																									$systemTimeZone = date_default_timezone_get();
																									$db_records = $obj->MySQLSelect("SELECT iOrderId,count(CASE WHEN eStatus = 'Accept' THEN iDriverId END) as total_accept,max(tDate) as ttDate,count(iOrderId) as corder  FROM driver_request   WHERE 1 = 1 GROUP BY iOrderId ORDER BY  `tDate` DESC");
																									$orderDataArr = array();
																									for ($r = 0; $r < count($db_records); $r++) {
																										$orderDataArr[$db_records[$r]['iOrderId']] = $db_records[$r];
																									}
																									
																									for ($i = 0; $i < $endRecord; $i++) {
																										
																										$vTimeZone = $DBProcessingOrders[$i]['vTimeZone'];
																										if(empty($vTimeZone)){
																											$vTimeZone = $systemTimeZone;
																										}
																										$Ordersdate = $DBProcessingOrders[$i]['tOrderRequestDate'];
																										
																										if($vTimeZone != "undefined")
																										{
																											$Ordersdate = converToTz($Ordersdate, $systemTimeZone, $vTimeZone);
																										}
																										
																										

																										$futureDate = strtotime($Ordersdate) + (60 * 5);
																										$date = date('Y-m-d H:i:s');
																										$currentDate = strtotime($date);
																										$futurenewDate = date('Y-m-d H:i:s', strtotime($futureDate));
																										$iOrderId = $DBProcessingOrders[$i]['iOrderId'];
																										$iDriverId = $DBProcessingOrders[$i]['iDriverId'];
																										$iOrderStatusCode = $DBProcessingOrders[$i]['iStatusCode'];
																										$eAutoaccept = 'No';
																										if (isset($DBProcessingOrders[$i]['eAutoaccept'])) {
																											$eAutoaccept = $DBProcessingOrders[$i]['eAutoaccept'];
																										}
                                                        //Added By HJ On 13-02-2020 For Display Paymen Type Start
																										$paymentType = ucwords($DBProcessingOrders[$i]['ePaymentOption']);
																										if (isset($DBProcessingOrders[$i]['fNetTotal']) > 0 && $DBProcessingOrders[$i]['ePayWallet'] == "Yes") {
																											if (strtoupper($DBProcessingOrders[$i]['ePaymentOption']) == "CARD") {
                                                                //$paymentType = ucwords($langage_lbl_admin["LBL_CARD_CAPS"]) . "-" . ucwords($langage_lbl_admin['LBL_WALLET_TXT']);//commented by SP bc of solving issue to be fixed 1312
																												$paymentType = ucwords($langage_lbl_admin["LBL_CARD_CAPS"]);
																											} else if (strtoupper($DBProcessingOrders[$i]['ePaymentOption']) == "CASH") {
                                                                //$paymentType = ucwords($langage_lbl_admin["LBL_CASH_CAPS"]) . "-" . ucwords($langage_lbl_admin['LBL_WALLET_TXT']);//commented by SP bc of solving issue to be fixed 1312
																												$paymentType = ucwords($langage_lbl_admin["LBL_CASH_CAPS"]);
																											}
																										}
                                                        //Added By HJ On 13-02-2020 For Display Paymen Type End
																										?>
																										<tr class="gradeA">
																											<?php if (count($allservice_cat_data) > 1) { ?>
																												<td class="text-center"><?= $DBProcessingOrders[$i]['vServiceName']; ?></td>
																												
																											<?php } ?>
																											<?php if ($userObj->hasPermission('view-invoice')) { ?>
																												<td class="text-center">
																													<a href="order_invoice.php?iOrderId=<?= $DBProcessingOrders[$i]['iOrderId'] ?>" target="_blank"><?= $DBProcessingOrders[$i]['vOrderNo']; ?></a>
																													<?= $DBProcessingOrders[$i]['eTakeaway'] == 'Yes' ? '<br><span>'.$langage_lbl['LBL_TAKE_AWAY'].'</span>' : ''?>
																												</td>
																											<?php } else { ?>
																												<td class="text-center"><?= $DBProcessingOrders[$i]['vOrderNo']; ?></td>
																											<?php } ?>
																											<td class="text-center">
																												<?= $generalobjAdmin->DateTime($DBProcessingOrders[$i]['tOrderRequestDate'], 'yes') ?>
																											</td>
																											<td><?= $generalobjAdmin->clearName($DBProcessingOrders[$i]['riderName']); ?><br>
																												<?php
																												if (!empty($DBProcessingOrders[$i]['user_phone'])) {
																													echo $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['user_phone']);
																												}
																												?>
																											</td>
																											<td><?= $generalobjAdmin->clearCmpName($DBProcessingOrders[$i]['vCompany']); ?><br>
																												<?php
																												if (!empty($DBProcessingOrders[$i]['resturant_phone'])) {
																													echo $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['resturant_phone']);
																												}
																												?>
																											</td>
																											<td>
																												<?php
																												if (!empty($DBProcessingOrders[$i]['driverName'])) {
																													echo $generalobjAdmin->clearName($DBProcessingOrders[$i]['driverName'])."<br>";
																												}
																												?>

																												<?php
																												if (!empty($DBProcessingOrders[$i]['driver_phone'])) {
																													echo $generalobjAdmin->clearPhone($DBProcessingOrders[$i]['driver_phone']);
																												}
																												if ($ENABLE_AUTO_ACCEPT_STORE_ORDER == "Yes" && $eAutoaccept == "Yes") {
																													$currentdate = @date('Y-m-d H:i:s');
																													$total_accept = $corder = $cabbook = 0;
																													$checkdate = $tDate = "";
																													$vCountry = $DBProcessingOrders[$i]['vCountry'];
																													if ($iOrderStatusCode == 2 && $iDriverId <= 0) {
																														echo "<br>";
																														if (isset($orderDataArr[$iOrderId])) {
																															$tDate = $orderDataArr[$iOrderId]['ttDate'];
																															$corder = $orderDataArr[$iOrderId]['corder'];
																															$total_accept = $orderDataArr[$iOrderId]['total_accept'];
																														}
																														$checkdate = date('Y-m-d H:i:s', strtotime("+" . $RIDER_REQUEST_ACCEPT_TIME . " seconds", strtotime($tDate)));
																														if ($corder == 0) {
																															?> 
																															<button href="#" onclick="openDriverModal(this);" class="btn btn-info" data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button" >Assign to the <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></button>
																															<?php
																														} else {
																															$currentdate = date('Y-m-d H:i:s');
																															$time1 = strtotime($currentdate);
																															$time2 = strtotime($checkdate);
																															if ($total_accept == 0 && $time1 <= $time2) {
																																?>
																																<button href="#" onclick="openDriverModal(this);" class="btn btn-info break-line" data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button" >Please wait for <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> accept request</button>
																															<?php } else { ?>
																																<button href="#" onclick="openDriverModal(this);" class="btn btn-info" data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button"   >Assign to the <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></button>
																																<?php
																															}
																														}
																													}
																												}
																												?>	
																											</td>        
																											<td class="text-right"><?= $generalobj->formateNumAsPerCurrency($DBProcessingOrders[$i]['fTotalGenerateFare'], ''); ?></td>
																											<!--<td class="text-center"><?= $DBProcessingOrders[$i]['vServiceName'] ?></td>-->
																											<td class="text-center"><?= $DBProcessingOrders[$i]['vStatus'] ?></td>
																											<td class="text-center"><?= $paymentType; ?></td>
																											<td class="text-center">
																												<?php if (in_array($DBProcessingOrders[$i]['iStatusCode'], $processing_status_array)): ?>
																													<?php if ($userObj->hasPermission('cancel-orders')) { ?>
																														<a href="#"   data-target="#delete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class=" custom-order btn btn-info" data-toggle="modal" data-id="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">Cancel Order</a> 
																														<div id="delete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>" class="modal fade delete_form text-left" role="dialog">
																															<div class="modal-dialog">
																																<div class="modal-content">
																																	<div class="modal-header">
																																		<button type="button" class="close" data-dismiss="modal">x</button>
																																		<h4 class="modal-title">Cancel Order</h4>
																																	</div>
																																	<form role="form" name="delete_form" id="delete_form1" method="post" action="" class="margin0">
																																		<div class="modal-body">
																																			<div class="form-group col-lg-12" style="display: inline-block;">
																																				<label class="col-lg-4 control-label">Cancellation Reason<span class="red">*</span></label>
																																				<div class="col-lg-7">
																																					<textarea name="cancel_reason" id="cancel_reason<?= $DBProcessingOrders[$i]['iOrderId']; ?>" rows="4" cols="40" required="required"></textarea>
																																					<div class="cnl_error error red"></div>
																																				</div>
																																			</div>
																																			<div class="form-group col-lg-12" style="display: inline-block;">
																																				<label class="col-lg-4 control-label">Cancellation Charges To Apply For User<span class="red">*</span></label>
																																				<div class="col-lg-7">
																																					<input type="fCancellationCharge" name="fCancellationCharge" id="fCancellationCharge<?= $DBProcessingOrders[$i]['iOrderId']; ?>" required="required" value="<?= $DBProcessingOrders[$i]['iStatusCode'] == 12 ? 0 : $MIN_ORDER_CANCELLATION_CHARGES;  ?>" <?php if($DBProcessingOrders[$i]['iStatusCode'] == 12) {
																																						?> disabled="disabled" <?php } ?> >
																																						<div class="cancelcharge_error error red"></div>
																																					</div>
																																				</div>

                                <!-- <div class="form-group col-lg-12" style="display: inline-block;">
                                                                            
                               <label class="col-lg-4 control-label">Payment To Driver<span class="red">*</span></label>
                                                                           
                          <?php $payment_to_driver = $generalobjAdmin->getPaymentToDriver($DBProcessingOrders[$i]['iOrderId']); ?>
                                                                            
                          <div class="col-lg-7"> -->
                          	<input type="hidden" name="fDeliveryCharge" id="fDeliveryCharge" value="<?= $payment_to_driver; ?>">
                        <!-- <?php if ($payment_to_driver == 0): ?>
                              <?php else: ?>
                                           <?php $DBProcessingOrders[$i]['driverName']; ?>
                                                                               <?php endif; ?>
                                                            </div>
                                                        </div> -->

                     <!-- <div class="form-group col-lg-12" style="display: inline-block;">
                     	<label class="col-lg-4 control-label">Payment To Restaurant<span class="red">*</span></label>-->

                     	<?php $payment_to_restaurant = $generalobjAdmin->getPaymentToRestaurant($DBProcessingOrders[$i]['iOrderId']); ?>
                     	
                     	<!-- <div class="col-lg-7">  -->
                     		<input type="hidden" name="fRestaurantPayAmount" id="fRestaurantPayAmount"  value="<?= $payment_to_restaurant; ?>">
                                                                            <!--  </div>
                                                                            </div> -->


                                                                            <div class="form-group col-lg-12 col-md-offset-4">
                                                                      <!-- <p>Order Subtotal : <?php echo $generalobj->formateNumAsPerCurrency($DBProcessingOrders[$i]['fSubTotal'], ''); ?></p>
                                                                                                        
                                                                       <p>Restaurant Discount : 
                                                                                                <?php echo $generalobj->formateNumAsPerCurrency($DBProcessingOrders[$i]['fOffersDiscount'], ''); ?>
                                                                                                                
                                                                                                        </p>
                                                                                                        <p>Site Commision : 
                                                                                                <?php echo $generalobj->formateNumAsPerCurrency($DBProcessingOrders[$i]['fCommision'], ''); ?></p>
                                        
                                                                                                         <p>Delivery Charge : <?php echo $generalobj->formateNumAsPerCurrency($DBProcessingOrders[$i]['fDeliveryCharge'], ''); ?>
                                                                                                     </p> -->
                                                                                                     <p>Expected <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Payout: 
                                                                                                     	<?php if($DBProcessingOrders[$i]['iStatusCode'] == "12") { 
                                                                                                     		echo " -- ";
                                                                                                     	}else {
        //chk here is statuscode 1 then store payout amt not shown..so in braces it shown store is not confirmed order
                                                                                                     		if($DBProcessingOrders[$i]['iStatusCode']==1) {
                                                                                                     			echo "- (".$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." has not confirmed order)";    
                                                                                                     		} else {
                                                                                                     			echo $generalobj->formateNumAsPerCurrency($payment_to_restaurant,'');
                                                                                                     		}
                                                                                                     	} ?></p>
                                                                                                     	<?php if ($payment_to_driver > 0) { ?>
                                                                                                     		<p>Expected <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> 
                                                                                                     		Payout: <?php echo $generalobj->formateNumAsPerCurrency($payment_to_driver, ''); ?></p>
                                                                                                     	<?php } ?>
                                                                                                     	<p>Expected Site Commission: 
                                                                                                     		<?php if($DBProcessingOrders[$i]['iStatusCode'] == "12") { 
                                                                                                     			echo " -- ";
                                                                                                     		}else {
                                                                                                     			echo $generalobj->formateNumAsPerCurrency($DBProcessingOrders[$i]['fCommision'], ''); 
                                                                                                     		} ?></p>  
                                                                                                     	</div> 

                                                                                                     	<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $DBProcessingOrders[$i]['iOrderId']; ?>">

                                                                                                     	<input type="hidden" name="iUserId" id="iUserId" value="<?= $DBProcessingOrders[$i]['iUserId']; ?>">

                                                                                                     	<input type="hidden" name="iDriverId" id="iDriverId" value="<?= $DBProcessingOrders[$i]['iDriverId']; ?>">

                                                                                                     	<input type="hidden" name="iCompanyId" id="iCompanyId" value="<?= $DBProcessingOrders[$i]['iCompanyId']; ?>">
                                                                                                     	<input type="hidden" name="type" id="type" value="<?= $order_type; ?>">
                                                                                                     	<input type="hidden" name="action" id="action" value="cancel">
                                                                                                     	<div class="form-group col-lg-12">
                                                                                                     		<label class="control-label">Notes:</label>
                                                                                                     		<p>
                                                                                                     		1. Set the cancellation charges as per the Order and Delivery status. Also, the expected payouts shown here are just for the your review to check how much to pay if the order will be delivered.</p>

                                                                                                     		<p>2. If this order contains any wallet settlement then wallet amount will be refunded back to <?= $langage_lbl_admin['LBL_RIDER']; ?>'s wallet as soon as you mark this order as 'CANCEL'.</p>

                                                                                                     		<p> 3. cancellation charges is not applicable on status "Payment not initiated"
                                                                                                     		</p>
                                                                                                     	</div>
                                                                                                     </div>
                                                                                                     <div class="modal-footer">
                                                                                                     	<button type="submit" class="btn btn-info" id="cnl_booking1" onclick="return cancelBooking('<?= $DBProcessingOrders[$i]['iOrderId']; ?>');" title="Cancel Booking">Cancel Order</button>
                                                                                                     	<button type="button" class="btn btn-default" data-dismiss="modal" id="close_model">Close</button>
                                                                                                     </div>
                                                                                                 </form> 

                                                                                             </div>
                                                                                             <!-- Modal content-->	
                                                                                         </div>
                                                                                     </div>
                                                                                     <!-- Modal -->
                                                                                     <script>
                                                                                     	$('#delete_form<?= $DBProcessingOrders[$i]['iOrderId']; ?>').on('show.bs.modal', function () {
                                                                                     		$("#fCancellationCharge<?= $DBProcessingOrders[$i]['iOrderId']; ?>").val("<?= $DBProcessingOrders[$i]['iStatusCode'] == 12 ? 0 : $MIN_ORDER_CANCELLATION_CHARGES;  ?>");
                                                                                     		$("#fDeliveryCharge").val("<?= $payment_to_driver; ?>");
                                                                                     		$("#fRestaurantPayAmount").val("<?= $payment_to_restaurant; ?>");

                                                                                     		$(".cancelcharge_error").html("");
                                                                                     		$(".cnl_error").html("");
                                                                                     	});
                                                                                     </script> 	
                                                                                 <?php } ?>
                                                                                 <?php else : ?> 
                                                                                 	<?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                                                 		<a class="btn btn-primary" href="order_invoice.php?iOrderId=<?= $DBProcessingOrders[$i]['iOrderId'] ?>" target="_blank">
                                                                                 			<i class="icon-th-list icon-white"><b>View Invoice</b></i>
                                                                                 		</button>
                                                                                 	<?php } ?>

                                                                                 <?php endif; ?>	

                                                                             </td>
                                                                         </tr>
                                                                         <div class="clear"></div>
                                                                         <?php
                                                                     }
                                                                 } else {
                                                                 	?>
                                                                 	<tr class="gradeA">
                                                                 		<td colspan="10"> No Records Found.</td>
                                                                 	</tr>
                                                                 <?php } ?>
                                                             </tbody>
                                                         </table>
                                                     </form>
                                                     <?php include('pagination_n.php'); ?>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="clear"></div>
                                 </div>
                             </div>
                             <!--END PAGE CONTENT -->
                         </div>
                         <!--END MAIN WRAPPER -->
                         <div id="assign_driver_modal" class="modal fade dddelete_form text-left" role="dialog">
                         	<div class="modal-dialog">
                         		<div class="modal-content">
                         			<div class="modal-header">
                         				<button type="button" class="close" data-dismiss="modal">x</button>
                         				<h4 class="modal-title">Assign to the <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></h4>
                         			</div>
                         			<div class="map-popup" style="display:none" id="driver_popup"></div>
                         			<div class="modal-body">
                         				<form action="add_auto_assign_driver_order.php" method="post" class="clearfix">								
                         					<input type="hidden" name="iOrderId" id="iOrderIdManual" value="" >
                         					<input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
                         					<input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>" >
                         					<input type="hidden" name="order" id="order" value="<?= $order; ?>" >
                         					<input type="hidden" name="action" value="<?= $action; ?>" >
                         					<input type="hidden" name="backlink" value="<?= "allorders.php?type=" . $order_type; ?>" >
                         					<input type="hidden" name="searchCompany" value="<?= $searchCompany; ?>" >
                         					<input type="hidden" name="searchDriver" value="<?= $searchDriver; ?>" >
                         					<input type="hidden" name="searchRider" value="<?= $searchRider; ?>" >
                         					<input type="hidden" name="searchServiceType" value="<?= $searchServiceType; ?>" >
                         					<input type="hidden" name="serachTripNo" value="<?= $serachTripNo; ?>" >
                         					<input type="hidden" name="startDate" value="<?= $startDate; ?>" >
                         					<input type="hidden" name="endDate" value="<?= $endDate; ?>" >
                         					<input type="hidden" name="vStatus" value="<?= $vStatus; ?>" >
                         					<input type="hidden" name="vCountry" id="vCountryManual" value="" >
                         					<input type="hidden" name="searchOrderStatus" value="<?= $searchOrderStatus; ?>" >
                         					<div class="form-group col-lg-12">
                         						<span class="auto_assign001">
                         							<input type="radio" name="eAutoAssign" id="eAutoAssign" onclick="changedData('1');" checked value="Yes" >&nbsp;Auto Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                         						</span>

                         						<label class="optional">Or</label>
                         						<span class="auto_assign001">
                         							<input type="radio" name="eAutoAssign" onclick="changedData('2');" id="eAutoAssign1" value="No" >&nbsp;Manual Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                         						</span>	
                         					</div>
                         					<div class="form-group col-lg-12" style="display: inline-block;">
                         						<p id="driverSet001"></p></span>
                         						<ul id="driver_main_list" class="order_list_d" style="display:none;">
                         							<div class="" id="imageIcons" style="width:100%;">
                         								<div align="center">
                         									<img src="default.gif">
                         									<span>Retrieving <?= $langage_lbl_admin['LBL_DIVER']; ?> list.Please Wait...</span>                    
                         								</div>                                                           
                         							</div>
                         						</ul>
                         					</div>
                         					<input type="hidden" name="iDriverId" id="iDriverId" value="" class="form-control">
                         					<div class="form-group" style="display: inline-block;    margin-top: 10px;">
                         						<input type="submit" name="submit"   value="Assign to the <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" class="btn btn-primary form-control" >
                         					</div>
                         				</form>
                         			</div>
                         		</div>
                         	</div>
                         </div>
                         <form name="pageForm" id="pageForm" action="" method="post" >
                         	<input type="hidden" name="page" id="page" value="<?= $page; ?>">
                         	<input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
                         	<input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>" >
                         	<input type="hidden" name="order" id="order" value="<?= $order; ?>" >
                         	<input type="hidden" name="action" value="<?= $action; ?>" >
                         	<input type="hidden" name="searchCompany" value="<?= $searchCompany; ?>" >
                         	<input type="hidden" name="searchDriver" value="<?= $searchDriver; ?>" >
                         	<input type="hidden" name="searchRider" value="<?= $searchRider; ?>" >
                         	<input type="hidden" name="searchServiceType" value="<?= $searchServiceType; ?>" >
                         	<input type="hidden" name="searchOrderStatus" value="<?= $searchOrderStatus; ?>" >
                         	<input type="hidden" name="serachTripNo" value="<?= $serachTripNo; ?>" >
                         	<input type="hidden" name="startDate" value="<?= $startDate; ?>" >
                         	<input type="hidden" name="endDate" value="<?= $endDate; ?>" >
                         	<input type="hidden" name="vStatus" value="<?= $vStatus; ?>" >
                         	<input type="hidden" name="method" id="method" value="" >
                         </form>
                         <div data-backdrop="static" data-keyboard="false" class="modal fade" id="is_dltSngl_modal12" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                         	<div class="modal-dialog">
                         		<div class="modal-content">
                         			<div class="modal-header"><h4>Cancel Order ?</h4></div>
                         			<div class="modal-body"><p>Are you sure to Cancel this Order?</p></div>
                         			<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button><a class="btn btn-success btn-ok action_modal_submit" >Yes</a></div>
                         		</div>
                         	</div>
                         </div>
                         <?php include_once('footer.php'); ?>
                         <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
                         <link rel="stylesheet" href="css/select2/select2.min.css" />
                         <script src="js/plugins/select2.min.js"></script>
                         <script src="../assets/js/jquery-ui.min.js"></script>
                         <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
                         <script>
                         	function setDriverListing(vCountry, orderId) {
                         		iVehicleTypeId = '';
                         		keyword = '';
                         		eLadiesRide = 'No';
                         		eHandicaps = 'No';
                         		eType = '';
                         		$.ajax({
                         			type: "POST",
                         			url: "get_available_driver_list_order.php",
                         			dataType: "html",
                         			data: {vCountry: vCountry, type: '', iVehicleTypeId: iVehicleTypeId, keyword: keyword, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, AppeType: eType, orderId: orderId},
                         			success: function (dataHtml2) {
                         				$('#driver_main_list').html('');
                         				if (dataHtml2 != "") {
                         					$('#driver_main_list').show();
                         					$('#driver_main_list').html(dataHtml2);
                         					if ($("#eAutoAssign").is(':checked')) {
                                                            //$("input:radio").attr('disabled', 'disabled');
                                                        }
                                                    } else {
                                                    	$('#driver_main_list').html('<h4 style="margin:25px 0 0 15px">Sorry , No <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Found.</h4>');
                                                    	$('#driver_main_list').show();
                                                    }
                                                }, error: function (dataHtml2) {

                                                }
                                            });
                         	}
                         	function openDriverModal(elem) {
                         		var radioValue = $("input[name='eAutoAssign']:checked").val();
                         		if (radioValue == "No") {
                         			$("#eAutoAssign1").prop("checked", false);
                         			$("#eAutoAssign").prop("checked", true);
                         			changedData("1");
                         		}
                         		var orderId = $(elem).attr("data-id");
                         		var country = $(elem).attr("data-country");
                         		$("#iOrderIdManual").val(orderId);
                         		$("#vCountryManual").val(country);
                         		$('#assign_driver_modal').modal({
                         			show: 'true'
                         		});
                         	}
                         	function changedData(type) {
                         		var country = $("#vCountryManual").val();
                         		var orderId = $("#iOrderIdManual").val();
                         		if (type == "1") {
                         			$("#driver_main_list").hide();
                         			$("#driverSet001").hide();
                         		} else {
                         			$("#driver_main_list").show();
                         			$("#driverSet001").show();
                         			setDriverListing(country, orderId);
                         		}
                         	}
                         	$('#dp4').datepicker()
                         	.on('changeDate', function (ev) {
                         		if (ev.date.valueOf() < endDate.valueOf()) {
                         			$('#alert').show().find('strong').text('The start date can not be greater then the end date');
                         		} else {
                         			$('#alert').hide();
                         			startDate = new Date(ev.date);
                         			$('#startDate').text($('#dp4').data('date'));
                         		}
                         		$('#dp4').datepicker('hide');
                         	});
                         	$('#dp5').datepicker()
                         	.on('changeDate', function (ev) {
                         		if (ev.date.valueOf() < startDate.valueOf()) {
                         			$('#alert').show().find('strong').text('The end date can not be less then the start date');
                         		} else {
                         			$('#alert').hide();
                         			endDate = new Date(ev.date);
                         			$('#endDate').text($('#dp5').data('date'));
                         		}
                         		$('#dp5').datepicker('hide');
                         	});

                         	$(document).ready(function () {
                         		if ('<?= $startDate ?>' != '') {
                         			$("#dp4").val('<?= $startDate ?>');
                         			$("#dp4").datepicker('update', '<?= $startDate ?>');
                         		}
                         		if ('<?= $endDate ?>' != '') {
                         			$("#dp5").datepicker('update', '<?= $endDate; ?>');
                         			$("#dp5").val('<?= $endDate; ?>');
                         		}

                         	});

                         	function setRideStatus(actionStatus) {
                         		window.location.href = "trip.php?type=" + actionStatus;
                         	}
                         	function todayDate()
                         	{
                         		$("#dp4").val('<?= $Today; ?>');
                         		$("#dp5").val('<?= $Today; ?>');
                         	}
                         	function reset() {
                         		location.reload();

                         	}
                         	function yesterdayDate()
                         	{
                         		$("#dp4").val('<?= $Yesterday; ?>');
                         		$("#dp4").datepicker('update', '<?= $Yesterday; ?>');
                         		$("#dp5").datepicker('update', '<?= $Yesterday; ?>');
                         		$("#dp4").change();
                         		$("#dp5").change();
                         		$("#dp5").val('<?= $Yesterday; ?>');
                         	}
                         	function currentweekDate(dt, df)
                         	{
                         		$("#dp4").val('<?= $monday; ?>');
                         		$("#dp4").datepicker('update', '<?= $monday; ?>');
                         		$("#dp5").datepicker('update', '<?= $sunday; ?>');
                         		$("#dp5").val('<?= $sunday; ?>');
                         	}
                         	function previousweekDate(dt, df)
                         	{
                         		$("#dp4").val('<?= $Pmonday; ?>');
                         		$("#dp4").datepicker('update', '<?= $Pmonday; ?>');
                         		$("#dp5").datepicker('update', '<?= $Psunday; ?>');
                         		$("#dp5").val('<?= $Psunday; ?>');
                         	}
                         	function currentmonthDate(dt, df)
                         	{
                         		$("#dp4").val('<?= $currmonthFDate; ?>');
                         		$("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
                         		$("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
                         		$("#dp5").val('<?= $currmonthTDate; ?>');
                         	}
                         	function previousmonthDate(dt, df)
                         	{
                         		$("#dp4").val('<?= $prevmonthFDate; ?>');
                         		$("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
                         		$("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
                         		$("#dp5").val('<?= $prevmonthTDate; ?>');
                         	}
                         	function currentyearDate(dt, df)
                         	{
                         		$("#dp4").val('<?= $curryearFDate; ?>');
                         		$("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
                         		$("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
                         		$("#dp5").val('<?= $curryearTDate; ?>');
                         	}
                         	function previousyearDate(dt, df)
                         	{
                         		$("#dp4").val('<?= $prevyearFDate; ?>');
                         		$("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');
                         		$("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');
                         		$("#dp5").val('<?= $prevyearTDate; ?>');
                         	}
                         	$("#Search").on('click', function () {
                         		if ($("#dp5").val() < $("#dp4").val()) {
                         			alert("From date should be lesser than To date.")
                         			return false;
                         		} else {
                         			var action = $("#_list_form").attr('action');
                         			var formValus = $("#frmsearch").serialize();
                         			window.location.href = action + "?" + formValus;
                         		}
                         	});
                         	$(function () {
                         		$("select.filter-by-text").each(function () {
                         			$(this).select2({
                         				placeholder: $(this).attr('data-text'),
                         				allowClear: true
                                                                    }); //theme: 'classic'
                         		});
                         	});
                         	$('#searchCompany').change(function () {
                                                                var company_id = $(this).val(); //get the current value's option
                                                                $.ajax({
                                                                	type: 'POST',
                                                                	url: 'ajax_find_driver_by_company.php',
                                                                	data: {'company_id': company_id},
                                                                	cache: false,
                                                                	success: function (data) {
                                                                		$(".driver_container").html(data);
                                                                	}
                                                                });
                                                            });
                                                        </script>
                                                        <script type="text/javascript">
                                                        	$(document).ready(function ()
                                                        	{
                                                        		$('.custom-order').on('click', function () {
                                                        			var order_id = $(this).data('id');
                                                        			(function () {
                                                        				var template = null
                                                        				$('#delete_form' + order_id).on('show.bs.modal', function (event) {
                                                        					if (template == null) {
                                                        						template = $(this).html()
                                                        					} else {
                                                        						$(this).html(template)
                                                        					}
                                                        				});
                                                        			})();
                                                        		});
                                                        	});
                                                        	function cancelBooking(orderId) {
                                                        		var cancel_reason = $('#cancel_reason' + orderId).val();
                                                        		var cancelcharge = $('#fCancellationCharge' + orderId).val();
                                                        		if (cancel_reason == '') {
                                                        			$(".cnl_error").html("This Field is required.");
                                                        			return false;
                                                        		} else if (cancelcharge == '') {
                                                        			$(".cancelcharge_error").html("This Field is required.");
                                                        			return false;
                                                        		} else {
                                                        			var confierm = confirm("Are you sure to Cancel this Order?");
                                                        			if (confierm == true) {
                        $(".loader-default").show(); // Added By HJ On 20-08-2019 For Display Loader when cancel order
                        $(".cnl_error").html("");
                        $(".cancelcharge_error").html("");
                        $("#delete_form" + orderId).submit();
                    } else {
                    	return false;
                    }
                }
            }
        </script>
    </body>
    <!-- END BODY-->
    </html>
