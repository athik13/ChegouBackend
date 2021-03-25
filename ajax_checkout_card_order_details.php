<?php

include_once('common.php');

include_once ('include_config.php');

include_once (TPATH_CLASS . 'configuration.php');

require_once ('assets/libraries/pubnub/autoloader.php');

require_once ('assets/libraries/SocketCluster/autoload.php');

include_once (TPATH_CLASS . 'twilio/Services/Twilio.php');

include_once ('include_generalFunctions_dl.php');



//Z:\cubejekdev_cubejekx\assets\libraries\webview\stripe\init.php

//require_once ('assets/libraries/webview/stripe/init.php');

//require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');

//require_once('stripe-demo/stripe-php/init.php');

//$uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";

//ini_set('display_errors', 1);

//error_reporting(E_ALL);

$fromOrder = "guest";

if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {

    $fromOrder = $_REQUEST['fromorder'];

}



$userSession = "MANUAL_ORDER_" . strtoupper($fromOrder);

$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);

$orderUserSession = "MANUAL_ORDER_USER_" . strtoupper($fromOrder);

$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);

$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);

$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);

$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);

$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);

$orderCurrencyNameSession = "MANUAL_ORDER_CURRENCY_NAME_" . strtoupper($fromOrder);

$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);

$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);

$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_" . strtoupper($fromOrder);

$orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);

$orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);

$orderUserNameSession = "MANUAL_ORDER_USER_NAME_" . strtoupper($fromOrder);

$orderCompanyNameSession = "MANUAL_ORDER_COMPANY_NAME_" . strtoupper($fromOrder);

$orderUserEmailSession = "MANUAL_ORDER_USER_EMAIL_" . strtoupper($fromOrder);

$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);

$responce = $responced['OrderDetails'] = $OrderDetailss = array();

if (isset($_SESSION[$orderDetailsSession])) {

    $OrderDetailss = $_SESSION[$orderDetailsSession];

}

//echo "<pre>";print_r($_REQUEST);die;

for ($ig = 0; $ig < count($OrderDetailss); $ig++) {

    if ($OrderDetailss[$ig]['typeitem'] != 'remove') {

        $addoptions = array();

        $addoptions['iMenuItemId'] = $OrderDetailss[$ig]['iMenuItemId'];

        $addoptions['iFoodMenuId'] = $OrderDetailss[$ig]['iFoodMenuId'];

        $addoptions['vOptionId'] = $OrderDetailss[$ig]['vOptionId'];

        $addoptions['iQty'] = $OrderDetailss[$ig]['iQty'];

        $addoptions['vAddonId'] = $OrderDetailss[$ig]['vAddonId'];

        $addoptions['tInst'] = $OrderDetailss[$ig]['tInst'];

        $addoptions['typeitem'] = $OrderDetailss[$ig]['typeitem'];

        array_push($responced['OrderDetails'], $addoptions);

    }

}

$_SESSION[$orderDetailsSession] = $responced['OrderDetails'];

$_REQUEST["OrderDetails"] = json_encode($_SESSION[$orderDetailsSession]);

$OrderDetails = isset($_REQUEST["OrderDetails"]) ? $_REQUEST["OrderDetails"] : '';

$CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';

$paymentMethod = isset($_REQUEST["paymentMethod"]) ? $_REQUEST["paymentMethod"] : 'No';



$iAdminUserId_placedorder = $_SESSION[$orderUserSession];

$iServiceId = $_SESSION[$orderServiceSession];

$iUserId = $_SESSION[$orderUserIdSession];

$iUserAddressId = $_SESSION[$orderAddressIdSession];

$iCompanyId = isset($_SESSION[$orderStoreIdSession]) ? $_SESSION[$orderStoreIdSession] : '';

$fDeliverytime = 0;

$ePaymentOption = isset($_REQUEST["payment"]) ? $_REQUEST["payment"] : '';

$iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';



/* $Dataua = $obj->MySQLSelect("SELECT vTimeZone  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'");

  //$vTimeZone = "Asia/Kolkata";

  $vTimeZone = date_default_timezone_get();

  if (count($Dataua) > 0) {

  $vTimeZone = !empty($Dataua[0]['vTimeZone']) ? $Dataua[0]['vTimeZone']: $vTimeZone;

  } else if ($iUserAddressId > 0) {

  $userFavAddress = $obj->MySQLSelect("SELECT vTimeZone FROM `user_fave_address`  WHERE iUserFavAddressId = '" . $iUserAddressId . "'");

  if (count($userFavAddress) > 0) {

  $vTimeZone = !empty($userFavAddress[0]['vTimeZone']) ? $userFavAddress[0]['vTimeZone'] : $vTimeZone;

  }

  }

  $vCouponCode = isset($_SESSION[$orderCouponSession]) ? $_SESSION[$orderCouponSession] : '';

  if ($CheckUserWallet == "" || $CheckUserWallet == NULL) {

  $CheckUserWallet = "No";

  }

  //echo "<pre>";print_r($iUserAddressId);die;

  //$fChangeAmount = isset($_REQUEST["changeAmount"]) ? $_REQUEST["changeAmount"] : '0';

  $vInstruction = isset($_REQUEST["Instruction"]) ? $_REQUEST["Instruction"] : '';

  $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);

  $Ratio = $UserDetailsArr['Ratio'];

  $currencySymbol = $UserDetailsArr['currencySymbol'];

  $vLang = $UserDetailsArr['vLang'];

  if (isset($_SESSION['sess_lang'])) {

  $vLang = $_SESSION['sess_lang'];

  }

  if ($vLang == "" || $vLang == NULL) {

  $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

  }

  $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);

  if ($CheckUserWallet == "" || $CheckUserWallet == NULL) {

  $CheckUserWallet = "No";

  }

  checkmemberemailphoneverification($iUserId, "Passenger");

  $iGcmRegId = get_value('register_user', 'iGcmRegId', 'iUserId', $iUserId, '', 'true');

  if (isset($vDeviceToken) && $vDeviceToken != "" && $vDeviceToken != $iGcmRegId) {

  $returnArr['Action'] = "0";

  $returnArr['message'] = "SESSION_OUT";

  echo json_encode($returnArr);

  exit;

  }

  $checkrestaurantstatusarr = calculate_restaurant_time_span($iCompanyId, $iUserId);

  $restaurantstatus = $checkrestaurantstatusarr['restaurantstatus'];

  if ($restaurantstatus == "closed") {

  $returnArr['Action'] = "0";

  $returnArr['message'] = $languageLabelsArr["LBL_RESTAURANTS_CLOSE_NOTE"];

  echo json_encode($returnArr);

  exit;

  }

  $isAllItemAvailableCheckArr = checkmenuitemavailability(json_decode(stripcslashes($OrderDetails), true));

  $isAllItemAvailable = $isAllItemAvailableCheckArr['isAllItemAvailable'];

  $isAllItemOptionsAvailable = $isAllItemAvailableCheckArr['isAllItemOptionsAvailable'];

  $isAllItemToppingssAvailable = $isAllItemAvailableCheckArr['isAllItemToppingssAvailable'];

  if ($isAllItemAvailable == "No") {

  $returnArr['Action'] = "0";

  $returnArr['message'] = $languageLabelsArr["LBL_MENU_ITEM_NOT_AVAILABLE_TXT"];

  echo json_encode($returnArr);

  exit;

  }

  if ($isAllItemOptionsAvailable == "No") {

  $returnArr['Action'] = "0";

  $returnArr['message'] = $languageLabelsArr["LBL_MENU_ITEM_OPTIONS_NOT_AVAILABLE_TXT"];

  echo json_encode($returnArr);

  exit;

  }

  if ($isAllItemToppingssAvailable == "No") {

  $returnArr['Action'] = "0";

  $returnArr['message'] = $languageLabelsArr["LBL_MENU_ITEM_ADDONS_NOT_AVAILABLE_TXT"];

  echo json_encode($returnArr);

  exit;

  }

  if ($ePaymentOption == "Card") {

  UpdateCardPaymentPendingOrder();

  }

  $sql = "SELECT vName,vLastName,vEmail from register_user WHERE iUserId = '" . $iUserId . "'";

  $user_detail = $obj->MySQLSelect($sql);

  $vName = $user_detail[0]['vName'];

  $vLastName = $user_detail[0]['vLastName'];

  $vUserEmail = $user_detail[0]['vEmail'];

  $sql = "select vCompany,fMaxOfferAmt,fTargetAmt,fOfferType,fOfferAppyType,fOfferAmt from `company` where iCompanyId = '" . $iCompanyId . "'";

  $db_companydata = $obj->MySQLSelect($sql);

  $vCompany = "";

  $fMaxOfferAmt = $fTargetAmt = $fOfferAmt = 0;

  $fOfferAppyType = "None";

  $fOfferType = "";

  if (count($db_companydata) > 0) {

  $vCompany = $db_companydata[0]['vCompany'];

  $fMaxOfferAmt = $db_companydata[0]['fMaxOfferAmt'];

  $fTargetAmt = $db_companydata[0]['fTargetAmt'];

  $fOfferType = $db_companydata[0]['fOfferType'];

  $fOfferAppyType = $db_companydata[0]['fOfferAppyType'];

  $fOfferAmt = $db_companydata[0]['fOfferAmt'];

  }

  // date_default_timezone_set('UTC');

  $Data_insert['iUserId'] = $iUserId;

  $Data_insert['iCompanyId'] = $iCompanyId;

  $Data_insert['iUserAddressId'] = $iUserAddressId;

  $Data_insert['vOrderNo'] = GenerateUniqueOrderNo();

  $Data_insert['tOrderRequestDate'] = @date("Y-m-d H:i:s");

  $Data_insert['dDeliveryDate'] = @date("Y-m-d H:i:s");

  $Data_insert['vUserEmail'] = $vUserEmail;

  $Data_insert['vName'] = $vName;

  $Data_insert['vLastName'] = $vLastName;

  $Data_insert['vCompany'] = $vCompany;

  $Data_insert['vCouponCode'] = trim($vCouponCode);

  $Data_insert['dDate'] = @date("Y-m-d H:i:s");

  $Data_insert['ePaymentOption'] = $ePaymentOption;

  $Data_insert['iStatusCode'] = 12;

  $Data_insert['dDeliveryDate'] = @date("Y-m-d H:i:s");

  $Data_insert['vInstruction'] = $vInstruction;

  $Data_insert['vTimeZone'] = $vTimeZone;

  $Data_insert['fMaxOfferAmt'] = $fMaxOfferAmt;

  $Data_insert['fTargetAmt'] = $fTargetAmt;

  $Data_insert['fOfferType'] = $fOfferType;

  $Data_insert['fOfferAppyType'] = $fOfferAppyType;

  $Data_insert['fOfferAmt'] = $fOfferAmt;

  $Data_insert['iServiceId'] = $iServiceId;

  $Data_insert['eCheckUserWallet'] = $CheckUserWallet;

  $user_available_balance_wallet = $generalobj->get_user_available_balance($iUserId, "Rider", true);

  $walletDataArr = array();

  if (is_array($user_available_balance_wallet)) {

  $walletDataArr = $user_available_balance_wallet;

  $user_available_balance_wallet = $walletDataArr['CurrentBalance'];

  $Data_insert['tUserWalletBalance'] = $walletDataArr['AutorizedWalletBalance'];

  }

  // payment method 2

  $Data_insert['ePayWallet'] = $CheckUserWallet;

  //$Data_insert['ePayWallet'] = 'No';

  // payment method 2

  $currencyList = get_value('currency', '*', 'eStatus', 'Active');

  for ($i = 0; $i < count($currencyList); $i++) {

  $currencyCode = $currencyList[$i]['vName'];

  $Data_insert['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];

  }

  $Datacheck = array();

  $eOrderplaced_by = 'User';

  if ($_SESSION[$userSession] == 'admin') {

  $eOrderplaced_by = 'Admin';

  } else if ($_SESSION[$userSession] == 'store') {

  $eOrderplaced_by = 'Store';

  } else if ($_SESSION[$userSession] == 'user') {

  $eOrderplaced_by = 'User';

  }

  $Data_insert['eOrderplaced_by'] = $eOrderplaced_by;

  $Data_insert['iAdminUserId_placedorder'] = $iAdminUserId_placedorder;

  //echo "<pre>";print_r($Data_insert);die;

  $iOrderId = $obj->MySQLQueryPerform("orders", $Data_insert, 'insert');



  $OrderDetails = json_decode(stripcslashes($OrderDetails), true);

  $OrderLogId = createOrderLog($iOrderId, $Data_insert['iStatusCode']);

  $OrderDetailsIdsArr = array();

  if (!empty($OrderDetails)) {

  //Added By HJ On 20-05-2019 For Optimize Code Start

  $optionPriceArr = getAllOptionAddonPriceArr();

  $ordItemPriceArr = getAllMenuItemPriceArr();

  //Added By HJ On 20-05-2019 For Optimize Code End

  $fTotalMenuItemBasePrice = $fTotalPricesum = $fTotalDiscountPricesum = 0;

  for ($j = 0; $j < count($OrderDetails); $j++) {

  $iQty = $OrderDetails[$j]['iQty'];

  //$fMenuItemPrice = FoodMenuItemBasicPrice($OrderDetails[$j]['iMenuItemId'], $iQty); //Commnent By HJ On 20-05-2019 For Optimize Below Code

  //Added By HJ On 09-05-2019 For Optimize Code Start

  $fMenuItemPrice = 0;

  if (isset($ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']]) && $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] > 0) {

  $fMenuItemPrice = $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] * $iQty;

  }

  //Added By HJ On 09-05-2019 For Optimize Code End

  //$vOptionPrice = GetFoodMenuItemOptionPrice($OrderDetails[$j]['vOptionId']); //Commnent By HJ On 20-05-2019 For Optimize Below Code

  //Added By HJ On 20-05-2019 For Optimize Code Start

  $vOptionPrice = 0;

  $explodeOption = explode(",", $OrderDetails[$j]['vOptionId']);

  for ($fd = 0; $fd < count($explodeOption); $fd++) {

  if (isset($optionPriceArr[$explodeOption[$fd]]) && $optionPriceArr[$explodeOption[$fd]] > 0) {

  $vOptionPrice += $optionPriceArr[$explodeOption[$fd]];

  }

  }

  //Added By HJ On 20-05-2019 For Optimize Code End

  $vOptionPrice = $vOptionPrice * $iQty;

  //$vAddonPrice = GetFoodMenuItemAddOnPrice($OrderDetails[$j]['vAddonId']); //Commnent By HJ On 20-05-2019 For Optimize Below Code

  //Added By HJ On 20-05-2019 For Optimize Code Start

  $vAddonPrice = 0;

  $explodeAddon = explode(",", $OrderDetails[$j]['vAddonId']);

  for ($df = 0; $df < count($explodeAddon); $df++) {

  if (isset($optionPriceArr[$explodeAddon[$df]]) && $optionPriceArr[$explodeAddon[$df]] > 0) {

  $vAddonPrice += $optionPriceArr[$explodeAddon[$df]];

  }

  }

  //Added By HJ On 20-05-2019 For Optimize Code End

  $vAddonPrice = $vAddonPrice * $iQty;

  $fTotalMenuItemBasePrice = $fTotalMenuItemBasePrice + $fMenuItemPrice + $vOptionPrice + $vAddonPrice;

  }

  $fTotalMenuItemBasePrice = round($fTotalMenuItemBasePrice, 2);

  for ($i = 0; $i < count($OrderDetails); $i++) {

  $Data = array();

  $Data['iOrderId'] = $iOrderId;

  $Data['iMenuItemId'] = isset($OrderDetails[$i]['iMenuItemId']) ? $OrderDetails[$i]['iMenuItemId'] : '';

  $Data['iFoodMenuId'] = isset($OrderDetails[$i]['iFoodMenuId']) ? $OrderDetails[$i]['iFoodMenuId'] : '';

  $Data['iQty'] = isset($OrderDetails[$i]['iQty']) ? $OrderDetails[$i]['iQty'] : '';

  $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($Data['iMenuItemId'], $iCompanyId, 1, $iUserId, "Calculate", $OrderDetails[$i]['vOptionId'], $OrderDetails[$i]['vAddonId'], $iServiceId);

  $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'];

  $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'];

  $fPrice = $MenuItemPriceArr['fPrice'];

  $TotOrders = $MenuItemPriceArr['TotOrders'];

  if ($fOfferType == "Flat" && $fOfferAppyType != "None" && $TotOrders == 0) {

  $Data['fOriginalPrice'] = $fOriginalPrice;

  $Data['fDiscountPrice'] = $MenuItemPriceArr['fOfferAmt'];

  $Data['fPrice'] = $fOriginalPrice;

  $fTotalDiscountPrice = $MenuItemPriceArr['fOfferAmt'];

  $Data['fTotalDiscountPrice'] = $fTotalDiscountPrice;

  } else {

  $Data['fOriginalPrice'] = $fOriginalPrice;

  $Data['fDiscountPrice'] = $fDiscountPrice;

  $Data['fPrice'] = $fPrice;

  $fTotalDiscountPrice = $fDiscountPrice * $Data['iQty'];

  $Data['fTotalDiscountPrice'] = $fTotalDiscountPrice;

  }

  if ($fTotalMenuItemBasePrice < $fTargetAmt && $fOfferAppyType != "None") {

  $Data['fOriginalPrice'] = $fOriginalPrice;

  $Data['fDiscountPrice'] = 0;

  $Data['fPrice'] = $fOriginalPrice;

  $Data['fTotalDiscountPrice'] = 0;

  }

  $Data['vOptionId'] = isset($OrderDetails[$i]['vOptionId']) ? $OrderDetails[$i]['vOptionId'] : '';

  //$Data['vOptionPrice'] = GetFoodMenuItemOptionPrice($Data['vOptionId']); //Commnent By HJ On 20-05-2019 For Optimize Below Code

  //Added By HJ On 20-05-2019 For Optimize Code Start

  $vOptionPrice1 = 0;

  $explodeOption = explode(",", $Data['vOptionId']);

  for ($fd = 0; $fd < count($explodeOption); $fd++) {

  if (isset($optionPriceArr[$explodeOption[$fd]]) && $optionPriceArr[$explodeOption[$fd]] > 0) {

  $vOptionPrice1 += $optionPriceArr[$explodeOption[$fd]];

  }

  }

  $Data['vOptionPrice'] = $vOptionPrice1;

  $Data['vAddonId'] = isset($OrderDetails[$i]['vAddonId']) ? $OrderDetails[$i]['vAddonId'] : '';

  //$Data['vAddonPrice'] = GetFoodMenuItemAddOnPrice($Data['vAddonId']); //Commnent By HJ On 20-05-2019 For Optimize Below Code

  //Added By HJ On 20-05-2019 For Optimize Code Start

  $vAddonPrice1 = 0;

  $explodeAddon = explode(",", $Data['vAddonId']);

  for ($df = 0; $df < count($explodeAddon); $df++) {

  if (isset($optionPriceArr[$explodeAddon[$df]]) && $optionPriceArr[$explodeAddon[$df]] > 0) {

  $vAddonPrice1 += $optionPriceArr[$explodeAddon[$df]];

  }

  }

  $Data['vAddonPrice'] = $vAddonPrice1;

  //Added By HJ On 20-05-2019 For Optimize Code End

  $Data['fPrice'] = $Data['fOriginalPrice'] - $Data['vOptionPrice'] - $Data['vAddonPrice'];

  $fSubTotal = $Data['fOriginalPrice'];

  $Data['fSubTotal'] = $fSubTotal;

  $fTotalPrice = $fSubTotal * $Data['iQty'];

  $Data['fTotalPrice'] = $fTotalPrice;

  $Data['dDate'] = @date("Y-m-d H:i:s");

  $Data['eAvailable'] = "Yes";

  $Data['tOptionIdOrigPrice'] = GetFoodMenuItemOptionIdPriceString($Data['vOptionId']);

  $Data['tAddOnIdOrigPrice'] = GetFoodMenuItemAddOnIdPriceString($Data['vAddonId']);

  $iOrderDetailId = $obj->MySQLQueryPerform("order_details", $Data, 'insert');

  array_push($OrderDetailsIdsArr, $iOrderDetailId);

  }

  }

  // payment method 2

  $Order_data = calculateOrderFare($iOrderId);

  //echo "<pre>";print_r($Order_data);die;

  $where = " iOrderId = '" . $iOrderId . "'";

  $Data_update_order['fSubTotal'] = $Order_data['fSubTotal'];

  $Data_update_order['fOffersDiscount'] = $Order_data['fOffersDiscount'];

  $Data_update_order['fPackingCharge'] = $Order_data['fPackingCharge'];

  $Data_update_order['fDeliveryCharge'] = $Order_data['fDeliveryCharge'];

  $Data_update_order['fTax'] = $Order_data['fTax'];

  $Data_update_order['fDiscount'] = $Order_data['fDiscount'];

  $Data_update_order['vDiscount'] = $Order_data['vDiscount'];

  $Data_update_order['fCommision'] = $Order_data['fCommision'];

  $Data_update_order['fNetTotal'] = $Order_data['fNetTotal'];

  $Data_update_order['fTotalGenerateFare'] = $Order_data['fTotalGenerateFare'];

  $Data_update_order['fOutStandingAmount'] = $Order_data['fOutStandingAmount'];

  $Data_update_order['fWalletDebit'] = $Order_data['fWalletDebit'];

  if ($Order_data['fNetTotal'] == 0) {

  $Data_update_order['ePaid'] = "Yes";

  }

  // payment method 2

  if ($ePaymentOption != 'Cash') {

  if ($SYSTEM_PAYMENT_FLOW == 'Method-2' && $eWalletIgnore == 'No') {

  $user_available_balance_wallet = $generalobj->get_user_available_balance($iUserId, "Rider");

  if ($user_available_balance_wallet < $Order_data['fNetTotal']) {

  $Data_update_order_new['iStatusCode'] = 12;

  $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order_new, 'update', $where);



  $returnArr['Action'] = "0";

  $returnArr['iOrderId'] = $iOrderId;

  $returnArr['message'] = $languageLabelsArr["LOW_WALLET_AMOUNT"];

  echo json_encode($returnArr);

  exit;

  }

  }

  }



  $user_wallet_debit_amount = 0;

  $fNetTotal = $Order_data['fNetTotal'];

  if ($CheckUserWallet == "Yes") {

  $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");

  if ($fNetTotal > $user_available_balance) {

  $fNetTotal = $fNetTotal - $user_available_balance;

  $user_wallet_debit_amount = $user_available_balance;

  } else {

  $user_wallet_debit_amount = $fNetTotal;

  $fNetTotal = 0;

  }

  //$Data_update_order['fNetTotal'] = $fNetTotal;

  }

  // payment method 2

  $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order, 'update', $where);



  if ($Order_Update_Id > 0) {

  if ($ePaymentOption == "Cash") {

  $CompanyMessage = "OrderRequested";

  $vLangCode = get_value('company', 'vLang', 'iCompanyId', $iCompanyId, '', 'true');

  if ($vLangCode == "" || $vLangCode == NULL) {

  $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

  }

  $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);

  $orderreceivelbl = $languageLabelsArr['LBL_NEW_ORDER_PLACED_TXT'] . $Data_insert['vOrderNo'];

  $alertMsg = $orderreceivelbl;

  if ($PUBNUB_DISABLED == "Yes") {

  $ENABLE_PUBNUB = "No";

  }

  $alertSendAllowed = true;

  // For PubNub Setting

  $tableName = "company";

  $iMemberId_VALUE = $iCompanyId;

  $iMemberId_KEY = "iCompanyId";

  $AppData = get_value($tableName, 'iAppVersion,eDeviceType,iGcmRegId,tSessionId', $iMemberId_KEY, $iMemberId_VALUE);

  $iAppVersion = $AppData[0]['iAppVersion'];

  $eDeviceType = $AppData[0]['eDeviceType'];

  $iGcmRegId = $AppData[0]['iGcmRegId'];

  $tSessionId = $AppData[0]['tSessionId'];

  $registatoin_ids = $iGcmRegId;

  $deviceTokens_arr_ios = array();

  $registation_ids_new = array();

  $message_arr['tSessionId'] = $tSessionId;

  $message_arr['vTitle'] = $alertMsg;

  $message_arr['Message'] = $CompanyMessage;

  $message_arr['MsgCode'] = strval(time() . mt_rand(1000, 9999));

  $message_arr['iOrderId'] = $iOrderId;

  $message_arr['eSystem'] = "DeliverAll";

  $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

  // For PubNub Setting Finished

  if ($alertSendAllowed == true) {

  if ($eDeviceType == "Android") {

  array_push($registation_ids_new, $iGcmRegId);

  $Rmessage = array(

  "message" => $message_pub

  );

  $result = send_notification($registation_ids_new, $Rmessage, 0);

  } else {

  array_push($deviceTokens_arr_ios, $iGcmRegId);

  sendApplePushNotification(2, $deviceTokens_arr_ios, $message_pub, $alertMsg, 0);

  }



  $data_CompanyRequest = array();

  $data_CompanyRequest['iCompanyId'] = $iCompanyId;

  $data_CompanyRequest['iOrderId'] = $iOrderId;

  $data_CompanyRequest['tMessage'] = $message_pub;

  $data_CompanyRequest['vMsgCode'] = $message_arr['MsgCode'];

  $data_CompanyRequest['dAddedDate'] = @date("Y-m-d H:i:s");

  $requestId = addToCompanyRequest2($data_CompanyRequest);

  }

  sleep(3);

  $pubnub = new Pubnub\Pubnub(array(

  "publish_key" => $PUBNUB_PUBLISH_KEY,

  "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,

  "uuid" => $PUBNUB_UUID

  //"uuid" => $uuid

  ));

  $channelName = "COMPANY_" . $iCompanyId;

  if ($PUBNUB_DISABLED == "Yes") {

  publishEventMessage($channelName, $message_pub);

  } else {

  $info = $pubnub->publish($channelName, $message_pub);

  }





  if ($ENABLE_SOCKET_CLUSTER == "Yes") {

  $channelName = "NEW_ORDER_PLACED";

  publishEventMessage($channelName, $alertMsg);

  }

  }

  } else {

  $returnArr['Action'] = "0";

  $returnArr['message'] = $languageLabelsArr["LBL_TRY_AGAIN_LATER_TXT"];

  echo json_encode($returnArr);

  exit;

  } */

//Added By HJ On 25-02-2020 For Get Language Label Data As Per User Language Start

$UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);

$Ratio = $UserDetailsArr['Ratio'];

$currencySymbol = $UserDetailsArr['currencySymbol'];

$vLang = $UserDetailsArr['vLang'];

if (isset($_SESSION['sess_lang'])) {

    $vLang = $_SESSION['sess_lang'];

}

if ($vLang == "" || $vLang == NULL) {

    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

}

$languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);

//Added By HJ On 25-02-2020 For Get Language Label Data As Per User Language End

$pay_data = [];

$orderdata = $obj->MySQLSelect("select fNetTotal,vCompany,iUserId,tUserWalletBalance from orders where iOrderId = $iOrderId");

$fNetTotal = $orderdata[0]['fNetTotal'];

$vCompany = $orderdata[0]['vCompany'];

$iUserId = $orderdata[0]['iUserId'];

$tUserWalletBalance = $orderdata[0]['tUserWalletBalance'];

/* Check debit wallet For Count Total Fare  Start */

$user_wallet_debit_amount = 0;

if ($CheckUserWallet == "Yes") {

    $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");

    if ($user_available_balance > 0) {

        $totalCurrentActiveTripsArr = getCurrentActiveTripsTotal($iUserId);

        $totalCurrentActiveTripsIdsArr = $totalCurrentActiveTripsArr['ActiveTripIds'];

        $totalCurrentActiveOrderIdsArr = $totalCurrentActiveTripsArr['ActiveOrderIds'];

        $totalCurrentActiveTripsCount = $totalCurrentActiveTripsArr['TotalCount'];

        /*         * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */

        // Charge an amount that is autorized when trip was initially requested in case when multiple jobs are going on.

        if (($totalCurrentActiveTripsCount > 1 || in_array($iOrderId, $totalCurrentActiveOrderIdsArr) == false) && ($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {

            $user_available_balance = $tUserWalletBalance;

        }

        /*         * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */

    }

    if ($fNetTotal > $user_available_balance) {

        $fNetTotal = $fNetTotal - $user_available_balance;

        $user_wallet_debit_amount = $user_available_balance;

    } else {

        $user_wallet_debit_amount = $fNetTotal;

        $fNetTotal = 0;

    }

}

$generalConfigPaymentArr = $generalobj->getGeneralVarAll_Payment_Array();


if ($ePaymentOption == 'Card' && $fNetTotal > 0) {

    //$pay_data['tPaymentUserID'] = 'ch_1EI7fjHMmw2anrY62hw' . rand(10,1000);

    $pay_data['tPaymentUserID'] = 'REF_' . time();

    $pay_data['vPaymentUserStatus'] = "approved";

    //$pay_data['tPaymentDetails'] = '{"STRIPE_SECRET_KEY":"sk_test_S9nJKYA1qzl6LzKuFoSNhzc1","STRIPE_PUBLISH_KEY":"pk_test_w4Y4ZVaDVyfDDcyLvQacfNAz"}';

    $secretkey = $generalConfigPaymentArr['STRIPE_SECRET_KEY'];

    $publishkey = $generalConfigPaymentArr['STRIPE_PUBLISH_KEY'];

    $pay_data['tPaymentDetails'] = '{"STRIPE_SECRET_KEY":' . $secretkey . ',"STRIPE_PUBLISH_KEY":' . $publishkey . '}';

    $pay_data['iOrderId'] = $iOrderId;

    $pay_data['vPaymentMethod'] = $paymentMethod == 'flutterwave' ? 'flutterwave' : $APP_PAYMENT_METHOD;

    $pay_data['iUserId'] = $iUserId;

    $pay_data['eUserType'] = "Passenger";

    $pay_data['eEvent'] = "OrderPayment";

    $pay_data['iTripId'] = 0;

    //$pay_data['iAmountUser'] = $Order_data['fNetTotal'];

    $pay_data['iAmountUser'] = $fNetTotal;

    // $obj->MySQLQueryPerform("payments", $pay_data, 'insert');

}





unset($_SESSION[$orderDetailsSession]);

unset($_SESSION[$userSession]);

unset($_SESSION[$orderUserSession]);

unset($_SESSION[$orderServiceSession]);

unset($_SESSION[$orderUserIdSession]);

unset($_SESSION[$orderAddressIdSession]);

unset($_SESSION[$orderCouponSession]);

unset($_SESSION[$orderCouponNameSession]);



unset($_SESSION[$orderCurrencyNameSession]);

//unset($_SESSION['sess_currentpage_url_mr']);

unset($_SESSION[$orderLatitudeSession]);

unset($_SESSION[$orderLongitudeSession]);

unset($_SESSION[$orderAddressSession]);

unset($_SESSION[$orderDataSession]);



unset($_SESSION[$orderUserNameSession]);

unset($_SESSION[$orderCompanyNameSession]);

unset($_SESSION[$orderUserEmailSession]);

unset($_SESSION[$orderStoreIdSession]);

unset($_SESSION[$orderServiceNameSession]);





//unset($_SESSION["sess_user_mr"]);



if ($fNetTotal == 0) {

    $returnArr['Action'] = "3";

    $returnArr['message'] = '';

    $returnArr['iOrderId'] = $iOrderId;

    echo json_encode($returnArr);

    exit;

}


$_SESSION['pay_data'] = $pay_data;

//$currency = $_SESSION['sess_vCurrency'];

$sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";

$currencyData = $obj->MySQLSelect($sqlp);

$currency = $currencyData[0]['vName'];

$vSymbol = $currencyData[0]['vSymbol'];

$amount = $fNetTotal * 100;

$successUrl = $tconfig["tsite_url"] . 'myorder?order=' . base64_encode(base64_encode(trim($iOrderId))) . '&success=1';

$sql = "SELECT vName,vLastName,vEmail from register_user WHERE iUserId = '" . $iUserId . "'";
$user_detail = $obj->MySQLSelect($sql);
$vName = "John";
$vLastName = "Doe";
$vUserEmail = "test@gmail.com";
if (count($user_detail) > 0) {
    $vName = $user_detail[0]['vName'];
    $vLastName = $user_detail[0]['vLastName'];
    $vUserEmail = $user_detail[0]['vEmail'];
}

if ($paymentMethod == 'stripe' && $fNetTotal > 0) { // Handle Stripe Payment 

    \Stripe\Stripe::setApiKey($generalConfigPaymentArr['STRIPE_SECRET_KEY']);

    try {

        $sessionOrder = \Stripe\Checkout\Session::create([

                    'payment_method_types' => ['card'],

                    'line_items' => [[

                    'name' => $iOrderId . '-' . $vCompany,

                    'amount' => (int) $amount,

                    'currency' => $currency,

                    'description' => $languageLabelsArr["LBL_ORIGIONAL_AMOUNT_CHARGE_TXT"] . " " . $vSymbol . $fNetTotal,

                    'quantity' => 1

                        ]],

                    // 'success_url' => $tconfig["tsite_url"].'myorder?sid='.$sessionOrder->id.'&success=1',

                    'success_url' => $successUrl,

                    'cancel_url' => $tconfig["tsite_url"] . 'order-items?order=user&success=0',

        ]);

    } catch (Exception $e) {

        $error3 = $e->getMessage();

        $returnArr['Action'] = "0";

        $returnArr['message'] = $error3;

        echo json_encode($returnArr);

        exit;

    }





    $_SESSION['sessionOrderId'] = $sessionOrder->id;

    $returnArr['Action'] = "1";

    $returnArr['iOrderId'] = base64_encode(base64_encode(trim($iOrderId)));

    $successUrl = $tconfig["tsite_url"] . "admin/allorders.php?type=processing";

    $returnArr['sessionOrder'] = $sessionOrder->id;

    $returnArr['pay_data'] = $pay_data;

} else if ($paymentMethod == 'flutterwave' && $fNetTotal > 0) { // Handle Flutterave Payment

    $publicKeyFltr = $generalConfigPaymentArr['FLUTTERWAVE_PUBLIC_KEY'] ? $generalConfigPaymentArr['FLUTTERWAVE_PUBLIC_KEY'] : 'FLWPUBK-73e177eec0135215e2d60afab88a4290-X';

    $txref = "rave-" . base64_encode(base64_encode(trim($iOrderId))); // ensure you generate unique references per transaction.

    $PBFPubKey = $publicKeyFltr; // get your public key from the dashboard.

    //Added By HJ On 05-06-2020 For Solved Currency Related Issue Start
    $country = "NG";
    if(strtoupper($currency) == "KES"){
        $country = "KE";
    }else if(strtoupper($currency) == "ZAR"){
        $country = "ZA";
    }else if(strtoupper($currency) == "GHS"){
        $country = "GH";
    }else if(strtoupper($currency) == "TZS"){
        $country = "TZ";
    }
    //Added By HJ On 05-06-2020 For Solved Currency Related Issue End
    $apiUrl = $generalConfigPaymentArr['FLUTTERWAVE_API_URL']."v2/hosted/pay";// Added By HJ On 05-06-2020 For Dynamic
    $curl = curl_init();

    curl_setopt_array($curl, array(

        CURLOPT_URL => $apiUrl,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_CUSTOMREQUEST => "POST",

        CURLOPT_POSTFIELDS => json_encode([

            'amount' => $fNetTotal,

            'customer_email' => $vUserEmail,

            'currency' => $currency,
            'country' => $country,
            'txref' => $txref,

            'PBFPubKey' => $PBFPubKey,

            'redirect_url' => $successUrl,

            'payment_plan' => $iOrderId

        ]),

        CURLOPT_HTTPHEADER => [

            "content-type: application/json",

            "cache-control: no-cache"

        ],

    ));



    $response = curl_exec($curl);

    $err = curl_error($curl);



    if ($err) {

        // there was an error contacting the rave API

        die('Curl returned error: ' . $err);

    }



    $transaction = json_decode($response);



    if (!$transaction->data && !$transaction->data->link) {

        // print_r('API returned error: ' . $transaction->message);

        $returnArr['Action'] = "0";

        $returnArr['message'] = $languageLabelsArr["LBL_TRY_AGAIN_LATER_TXT"];

    }

    $returnArr['Action'] = "2";

    $returnArr['iOrderId'] = base64_encode(base64_encode(trim($iOrderId)));

    $returnArr['fltPaymentUrl'] = $transaction->data->link;

    $returnArr['pay_data'] = $pay_data;
} else if (strtolower($paymentMethod) == 'senangpay' && $fNetTotal > 0) {
    //echo $paymentMethod."====".$fNetTotal;die;
    $orderAmount = $fNetTotal * 100;
    $webViewUrl = $tsiteUrl . "senang_register.php?iUserId=" . $iUserId . "&UserType=Passenger&orderId=" . $iOrderId . "&amount=" . $orderAmount;
    //echo $webViewUrl;die;
    $returnArr['Action'] = "2";
    $returnArr['webViewUrl'] = $webViewUrl;
} else {

    $returnArr['Action'] = "0";

    $returnArr['message'] = $languageLabelsArr["LBL_TRY_AGAIN_LATER_TXT"];

}

echo json_encode($returnArr);

/* Session Creation with Stripe */

// exit;

?>



