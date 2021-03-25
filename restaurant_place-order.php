<?php

    include_once("common.php");
    //require_once ('assets/libraries/stripe/config.php');
    //require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
   // require_once ('assets/libraries/webview/stripe/init.php');
    $confirlAlert = 0;
    include_once ('include_generalFunctions_dl.php');
    check_type_wise_mr('restaurant_place-order');
    $script = "Restaurant menu";
    $vLang = "EN";
    if (isset($_SESSION['sess_lang']) && $_SESSION['sess_lang'] != "") {
        $vLang = $_SESSION['sess_lang'];
    }
    $fromOrder = "guest";
    if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
        $fromOrder = $_REQUEST['order'];
    }
    $userSession = "MANUAL_ORDER_" . strtoupper($fromOrder);
    $orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
    $orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
    $orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
    $orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
    $orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);
    $orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
    $orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);
    $lang = get_langcode($vLang);
    $_SESSION['sess_language'] = $vLang;
    global $intervalmins;
    $generalConfigPaymentArr = $generalobj->getGeneralVarAll_Payment_Array();
    $xenditSecretKey = $generalConfigPaymentArr['XENDIT_SECRET_KEY'];
    $xenditPublishKey = $generalConfigPaymentArr['XENDIT_PUBLIC_KEY'];
    $checkUser = check_user_mr();
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    //$meta = $generalobj->getStaticPage(1, $vLang);
    $iCompanyId = isset($_SESSION[$orderStoreIdSession]) ? $_SESSION[$orderStoreIdSession] : '';
    $iUserId = $iUserAddressId = $maualStoreOrderUser = "";
    $iServiceId = "1";
    if (isset($_SESSION[$orderUserIdSession])) {
        $iUserId = $_SESSION[$orderUserIdSession];
    }
    if (isset($_SESSION[$orderAddressIdSession])) {
        $iUserAddressId = $_SESSION[$orderAddressIdSession];
    }
    if (isset($_SESSION[$userSession])) {
        $maualStoreOrderUser = $_SESSION[$userSession];
    }
    if (isset($_SESSION[$orderServiceSession])) {
        $iServiceId = $_SESSION[$orderServiceSession];
    }
    $Datauser = $obj->MySQLSelect("SELECT tSessionId,iUserId FROM `register_user`  WHERE iUserId = '" . $iUserId . "'");
    if(empty($Datauser[0]['tSessionId']) ) {
        $Datauser = $obj->MySQLSelect("SELECT tSessionId,iUserId FROM `register_user`  WHERE tSessionId != '' LIMIT 1");
    }
    //Added By HJ On 27-04-2020 Solved Session Out Issue Start - When Session Id Blank
    if(isset($Datauser[0]['tSessionId']) && trim($Datauser[0]['tSessionId']) == "" || empty($Datauser[0]['tSessionId'])){
        $Data_update_passenger = array();
        $whereCondition = " iUserId = '$iUserId' ";
        $Data_update_passenger['tSessionId'] =$Datauser[0]['tSessionId']= session_id() . time();
        $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $whereCondition);
    }
    //Added By HJ On 27-04-2020 Solved Session Out Issue End - When Session Id Blank
    $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
    //print_r($iUserAddressId);die;
    $Ratio = 1;
    $currencySymbol = "$";
    if (isset($UserDetailsArr['Ratio']) && $UserDetailsArr['Ratio'] > 0) {
        $Ratio = $UserDetailsArr['Ratio'];
    }
    $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
    $user_available_balance = $generalobj->setTwoDecimalPoint($user_available_balance * $Ratio);
    if (isset($UserDetailsArr['currencySymbol']) && $UserDetailsArr['currencySymbol'] != "") {
        $currencySymbol = $UserDetailsArr['currencySymbol'];
    }
    //$vTimeZone = "Asia/Kolkata";
    $vTimeZone = date_default_timezone_get();
    //$APP_PAYMENT_MODE = 'Cash-Card';
    $languageArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $Dataua = $obj->MySQLSelect("SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'");
    $vServiceAddress = $vBuildingNo = $vLandmark = $vAddressType = $vLatitude = $vLongitude = "";
    if (count($Dataua) > 0) {
        $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
        $vBuildingNo = $Dataua[0]['vBuildingNo'];
        $vLandmark = $Dataua[0]['vLandmark'];
        $vAddressType = $Dataua[0]['vAddressType'];
        $vLatitude = $Dataua[0]['vLatitude'];
        $vLongitude = $Dataua[0]['vLongitude'];
        $vTimeZone = $Dataua[0]['vTimeZone'];
    }
    $sourceLocationArr = array($vLatitude, $vLongitude);
    $iToLocationId = GetUserGeoLocationId($sourceLocationArr);
    //$allowed_ans = checkAllowedAreaNew($sourceLocationArr, "No");
    $CheckNonVegFoodType = isset($_REQUEST["CheckNonVegFoodType"]) ? $_REQUEST["CheckNonVegFoodType"] : 'No';
    $fDeliverytime = 0;
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $searchword = isset($_REQUEST["searchword"]) ? $_REQUEST["searchword"] : '';
    $searchword = strtolower(trim($searchword));
    if ($searchword == "" || $searchword == null) {
        $searchword = "";
    }
    if ($CheckNonVegFoodType == "" || $CheckNonVegFoodType == null) {
        $CheckNonVegFoodType = "No";
    }
    $siteUrl = $tconfig['tsite_url'];
    // updatecompanylatlong($passengerLat,$passengerLon,$iCompanyId);
    $sqlr = "SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    $db_company = $obj->MySQLSelect($sqlr);
    //echo "<pre>";print_r($db_company);die;
    if (empty($db_company)) {
        header("location:store-listing?success=0&error=LBL_NO_RESTAURANT_FOUND_TXT&order=" . $fromOrder);
        exit;
    }
    //$restaurant_status_arr = calculate_restaurant_time_span($iCompanyId, $iUserId);
    //$restaurantstatus = $restaurant_status_arr['restaurantstatus'];
    $Recomendation_Arr = array();
    $CompanyDetails_Arr = getCompanyDetails($iCompanyId, $iUserId, $CheckNonVegFoodType, $searchword, $iServiceId, $vLatitude, $vLongitude);
    //echo "<pre>";print_r($CompanyDetails_Arr);die;
    $storeIdArr[] = $iCompanyId;
    $storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageArr);
    //echo "<pre>";print_r($storeDetails);die;
    $restaurantstatus = "Closed";
    $Restaurant_OrderPrepareTime = "0 mins";
    $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = "";
    if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])) {
        $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
    }
    if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'])) {
        $Restaurant_OrderPrepareTime = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'];
    }
    if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])) {
        $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
    }
    if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])) {
        $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
    }
    $db_company[0]['Restaurant_OfferMessage'] = $Restaurant_OfferMessage;
    $db_company[0]['Restaurant_OfferMessage_short'] = $Restaurant_OfferMessage_short;
    $db_company[0]['Restaurant_OrderPrepareTime'] = $Restaurant_OrderPrepareTime;
    $orderMinValue = 0;
    if (isset($storeDetails['restaurantMinOrdValue'][$iCompanyId])) {
        $orderMinValue = $storeDetails['restaurantMinOrdValue'][$iCompanyId];
    }
    //echo $orderMinValue;die;
    //$db_company[0]['fPricePerPerson'] = $CompanyDetails_Arr['fPricePerPersonWithCurrency'];
    $db_company[0]['fPackingCharge'] = $CompanyDetails_Arr['fPackingCharge'];
    $db_company[0]['fMinOrderValue'] = $orderMinValue;
    //$db_company[0]['fMinOrderValueDisplay'] = $CompanyDetails_Arr['fMinOrderValueDisplay'];
    //$db_company[0]['Restaurant_OfferMessage'] = $CompanyDetails_Arr['Restaurant_OfferMessage'];
    //$db_company[0]['Restaurant_OfferMessage_short'] = $CompanyDetails_Arr['Restaurant_OfferMessage_short'];
    //$db_company[0]['Restaurant_OrderPrepareTime'] = $CompanyDetails_Arr['Restaurant_OrderPrepareTime'];
    //$db_company[0]['monfritimeslot_TXT'] = $CompanyDetails_Arr['monfritimeslot_TXT'];
    //$db_company[0]['monfritimeslot_Time'] = $CompanyDetails_Arr['monfritimeslot_Time'];
    //$db_company[0]['satsuntimeslot_TXT'] = $CompanyDetails_Arr['satsuntimeslot_TXT'];
    //$db_company[0]['satsuntimeslot_Time'] = $CompanyDetails_Arr['satsuntimeslot_Time'];
    //$db_company[0]['eNonVegToggleDisplay'] = $CompanyDetails_Arr['eNonVegToggleDisplay'];
    //$db_company[0]['RatingCounts'] = $CompanyDetails_Arr['RatingCounts'];
    $db_company[0]['CompanyDetails'] = $CompanyDetails_Arr;
    $db_company[0]['MenuItemsDetails'] = $CompanyDetails_Arr['MenuItemsDataArr'];
    $db_company[0]['RegistrationDate'] = date("Y-m-d", strtotime($db_company[0]['tRegistrationDate'] . ' -1 day '));
    if ($db_company[0]['vImage'] != "") {
        $db_company[0]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $db_company[0]['iCompanyId'] . '/' . $db_company[0]['vImage'];
    }
    if ($db_company[0]['vCoverImage'] != "") {

        $db_company[0]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $db_company[0]['iCompanyId'] . '/' . $db_company[0]['vCoverImage'];

    }

    $vAvgRating = $db_company[0]['vAvgRating'];

    $db_company[0]['vAvgRating'] = ($vAvgRating > 0) ? number_format($db_company[0]['vAvgRating'], 1) : 0;

    //$Recomendation_Arr = getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, "Recommended");

    // $db_company[0]['Recomendation_Arr'] = $Recomendation_Arr;

    // $Bestseller_Arr = getRecommendedBestSellerMenuItems($iUserId,"BestSeller");

    $CompanyFoodData = $db_company[0]['CompanyDetails']['CompanyFoodData'];

    $_SESSION[$orderDataSession] = $CompanyFoodData;

    //$sql = "select vRestuarantLocationLat,vRestuarantLocationLong,iServiceId,iMaxItemQty,fMinOrderValue from `company` where iCompanyId = '" . $iCompanyId . "'";

    //$db_companydata = $obj->MySQLSelect($sql);

    $iMaxItemQty = $db_company[0]['iMaxItemQty'];

    $fMinOrderValue = $db_company[0]['fMinOrderValue'];

    $workAddress = $workAddress1 = $workAddress2 = '';

    //echo $_SESSION[$orderAddressIdSession];die;

    $addressCount = 0;

    if ($maualStoreOrderUser == 'user' || $maualStoreOrderUser == 'admin' || $maualStoreOrderUser == 'store') {

        $sql = "select register_user.vPhone,register_user.vPhoneCode,register_user.vName,register_user.vLastName,register_user.vEmail,register_user.iUserId,register_user.eStatus , CONCAT(register_user.vName, ' ', register_user.vLastName) AS vfullname from register_user where register_user.iUserId = '" . $iUserId . "' AND  register_user.eStatus='Active'";

        $register_user_data = $obj->MySQLSelect($sql);

        $sql = "select register_user.vName,register_user.vLastName,register_user.vEmail,register_user.iUserId,register_user.eStatus,ua.vServiceAddress,ua.iUserAddressId,ua.vBuildingNo,ua.vLandmark,ua.vAddressType,ua.vLatitude,ua.vLongitude from register_user INNER JOIN user_address as ua ON register_user.iUserId=ua.iUserId where register_user.iUserId = '" . $iUserId . "' AND  ua.eStatus='Active' ORDER BY  `ua`.`iUserAddressId` DESC";

        $db_model = $obj->MySQLSelect($sql);

        //$workAddress='<table class="table table-bordered address-table">';

        $workAddress1 = '<div class="address-wrap">';

        $workAddress .= '<input type="hidden"   name="iUserId" id="iUserId" value=' . $db_model[0]['iUserId'] . ' >';

        $vRestuarantLocationLat = $db_company[0]['vRestuarantLocationLat'];

        $vRestuarantLocationLong = $db_company[0]['vRestuarantLocationLong'];

        $iServiceId = $db_company[0]['iServiceId'];

        $iUserAddressId_data = '';

        //echo "<pre>";print_r($db_model);die;

        if (count($db_model) > 0) {

            for ($i = 0; $i < count($db_model); $i++) {

                $distance = distanceByLocation($db_model[$i]['vLatitude'], $db_model[$i]['vLongitude'], $vRestuarantLocationLat, $vRestuarantLocationLong, "K");

                //echo $distance."====".$LIST_RESTAURANT_LIMIT_BY_DISTANCE."<br>";

                if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {

                    continue;

                }

                $vBuildingNo = $db_model[$i]['vBuildingNo'];

                $vLandmark = $db_model[$i]['vLandmark'];

                $vAddressType = $db_model[$i]['vAddressType'];

                $vLatitude = $db_model[$i]['vLatitude'];

                $vLongitude = $db_model[$i]['vLongitude'];

                $iUserAddressId_data = $db_model[$i]['iUserAddressId'];

                $from_lat_long = '(' . $vLatitude . ', ' . $vLongitude . ')';

                $a = $b = '';

                if ($vBuildingNo != '') {

                    $a = ucfirst($vBuildingNo) . ", ";

                }

                if ($vLandmark != '') {

                    $b = ucfirst($vLandmark) . ", ";

                }

                $fulladdress = $a . "" . $b . "" . $db_model[$i]['vServiceAddress'];

                if (trim($db_model[$i]['vAddressType']) != "") {

                    $fulladdress .= " (" . $db_model[$i]['vAddressType'] . ")";

                }

                $radio_address_sel = '';

                $addresshide = 'hide';

                if (empty($iUserAddressId)) {

                    $Data = array();

                    $UserSelectedAddressArr = GetUserSelectedLastOrderAddressCompanyLocationWise($iUserId, "Passenger", '', '', $iCompanyId, '');

                    //echo "<pre>";print_r($UserSelectedAddressArr);die;

                    if (!empty($UserSelectedAddressArr)) {

                        $Data['UserSelectedAddressId'] = $UserSelectedAddressArr['UserSelectedAddressId'];

                        $_SESSION[$orderAddressIdSession] = $Data['UserSelectedAddressId'];

                        $iUserAddressId = $_SESSION[$orderAddressIdSession];

                        if ($db_model[$i]['iUserAddressId'] == $iUserAddressId) {
                            
                            $addresshide = 'show';

                            $radio_address_sel = "checked";

                            $workAddress1 = '<div class="address-wrap">';

                        }

                    }

                } else {

                    if ($db_model[$i]['iUserAddressId'] == $iUserAddressId) {

                        $addresshide = 'show';

                        $radio_address_sel = "checked";

                        $workAddress1 = '<div class="address-wrap">';

                    }

                }

                //$workAddress .= '<img src="' . $siteUrl . 'assets/img/cancel.svg" alt="Remove" onclick="removeAddress(' . $db_model[$i]['iUserAddressId'] . ');" class="close_ico" />';

                $workAddress .= '<div class="address-block ' . $addresshide . '"  id="address-id-' . $db_model[$i]['iUserAddressId'] . '" ><img src="' . $siteUrl . 'assets/img/cancel.svg" alt="Remove" onclick="removeAddress(' . $db_model[$i]['iUserAddressId'] . ');" class="close_ico" /><input type="radio" class="chkaddresssIds"  ' . $radio_address_sel . ' name="iUserAddressId" onclick="changelocation(' . $db_model[$i]['iUserAddressId'] . ',' . $db_model[$i]['vLatitude'] . ',' . $db_model[$i]['vLongitude'] . ')" id="iUserAddressId" value=' . $db_model[$i]['iUserAddressId'] . ' ><span><img src="' . $siteUrl . 'assets/img/locate.png" alt=""></span><div class="address-caption"><b>' . $langage_lbl['LBL_PROFILE_ADDRESS'] . '</b><address>' . $fulladdress . '</address><span class="appr-deliverytime"></span><a href="#">' . $langage_lbl['LBL_MANUAL_STORE_DELIVERY_HERE'] . '</a>';

                $addressCount++;

                $workAddress .= '</div></div>';

            }

        }

        $workAddress2 .= '</div>';

    }

    $workAddress4 = '<div class="address-block hide open-model" data-id="delivery-address-model"><span><img src="' . $siteUrl . 'assets/img/locate.png" alt=""></span><div class="address-caption" ><b>&nbsp;</b><img src="' . $siteUrl . 'assets/img/add-location.svg" /><a href="javascript:;" class="add-new" >' . $langage_lbl['LBL_ADD_NEW_ADDRESS_TXT'] . '</a></div></div>';

    //echo $workAddress4;

    //Added By HJ On 07-06-2019 For Get User Home and Work Address Start

    if ($iUserId > 0) {

        $getUserFavAddress = $obj->MySQLSelect("SELECT * FROM user_fave_address WHERE eStatus='Active' AND iUserId='" . $iUserId . "' AND eUserType='Passenger'");
        
        if (count($getUserFavAddress)) {

            for ($r = 0; $r < count($getUserFavAddress); $r++) {

                $addressType = $getUserFavAddress[$r]['eType'];

                $radio_address_sel = "";

                $addresshide = 'hide';

                if ($getUserFavAddress[$r]['iUserFavAddressId'] == $iUserAddressId) {

                    $addresshide = 'show';

                    $radio_address_sel = "checked";

                }

                //$workAddress .= '<img src="' . $siteUrl . 'assets/img/cancel.svg" alt="Remove" onclick="removeAddress(' . $getUserFavAddress[$r]['iUserFavAddressId'] . ');" class="close_ico" />';

                $addressCount++;

                $workAddress .= '<div class="address-block ' . $addresshide . '" id="address-id-' . $getUserFavAddress[$r]['iUserFavAddressId'] . '"><img src="' . $siteUrl . 'assets/img/cancel.svg" alt="Remove" onclick="removeAddress(' . $getUserFavAddress[$r]['iUserFavAddressId'] . ');" class="close_ico" /><input type="radio" class="chkaddresssIds" ' . $radio_address_sel . '  name="iUserAddressId" onclick="changelocation(' . $getUserFavAddress[$r]['iUserFavAddressId'] . ',' . $getUserFavAddress[$r]['vLatitude'] . ',' . $getUserFavAddress[$r]['vLongitude'] . ')" id="iUserAddressId" value=' . $getUserFavAddress[$r]['iUserFavAddressId'] . '><span><img src="' . $siteUrl . 'assets/img/locate.png" alt=""></span><div class="address-caption"><b>' . $addressType . ' ' . $langage_lbl['LBL_PROFILE_ADDRESS'] . '</b><address>' . $getUserFavAddress[$r]['vAddress'] . '</address><span class="appr-deliverytime"></span><a href="#">' . $langage_lbl['LBL_MANUAL_STORE_DELIVERY_HERE'] . '</a></div></div>';

            }

        }

    }

    //Added By HJ On 07-06-2019 For Get User Home and Work Address End

    $workAddress = $workAddress1 . $workAddress . $workAddress4 . $workAddress2;

    $msg = addslashes($languageArr['LBL_MAX_QTY_NOTE']) . ' ' . $iMaxItemQty . ' ' . addslashes($languageArr['LBL_TO_PROCEED']);

    $minordermsg = addslashes($languageArr['LBL_MINIMUM_ORDER_NOTE']) . ' ' . $fMinOrderValue;

    $deleteAddressMsg = addslashes($languageArr['LBL_DELETE_CONFIRM_MSG']);

    $confirmLabel = $languageArr['LBL_DELETE_CART_ITEM'];

    $couponDelMsg = addslashes($languageArr['LBL_DELETE_CONFIRM_COUPON_MSG']);

    $validCouponMsg = addslashes($languageArr['LBL_INVALID_COUPON_CODE']);

    $deliverAddresMsg = addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_DELIVERY_ADDRESS']);

    $finalMsg = addslashes($languageArr['LBL_NOTE_PLACE_ORDER_DEMO']);

    $pageHead = $SITE_NAME . " | " . $langage_lbl['LBL_CHECKOUT_ORDER_MANUAL_TXT'];

    

    $payButtonLabel = $langage_lbl['LBL_BTN_PAYMENT_TXT'];

    $changeDesign = 0;

    $payMethodLabel = $langage_lbl['LBL_PAYMENT'];

    if ($maualStoreOrderUser == "store" || $maualStoreOrderUser == "admin") {

        $changeDesign = 1;

        $payButtonLabel = $langage_lbl['LBL_ORDER_NOW'];

        $payMethodLabel = $langage_lbl['LBL_CASH_PAYMENT_TXT'];

    }

//Added By HJ On 07-06-2019 For Get User Home and Work Address End

// $workAddress = $workAddress1 . $workAddress . $workAddress4 . $workAddress2;

$msg = addslashes($languageArr['LBL_MAX_QTY_NOTE']) . ' ' . $iMaxItemQty . ' ' . addslashes($languageArr['LBL_TO_PROCEED']);

$minordermsg = addslashes($languageArr['LBL_MINIMUM_ORDER_NOTE']) . ' ' . $fMinOrderValue;

$deleteAddressMsg = addslashes($languageArr['LBL_DELETE_CONFIRM_MSG']);

$confirmLabel = $languageArr['LBL_DELETE_CART_ITEM'];

$couponDelMsg = addslashes($languageArr['LBL_DELETE_CONFIRM_COUPON_MSG']);

$validCouponMsg = addslashes($languageArr['LBL_INVALID_COUPON_CODE']);

$deliverAddresMsg = addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_DELIVERY_ADDRESS']);

$finalMsg = addslashes($languageArr['LBL_NOTE_PLACE_ORDER_DEMO']);

$pageHead = $SITE_NAME . " | " . $langage_lbl['LBL_CHECKOUT_ORDER_MANUAL_TXT'];



$payButtonLabel = $langage_lbl['LBL_BTN_PAYMENT_TXT'];

$changeDesign = 0;

$payMethodLabel = $langage_lbl['LBL_PAYMENT'];

if ($maualStoreOrderUser == "store" || $maualStoreOrderUser == "admin") {

    $changeDesign = 1;

    $payButtonLabel = $langage_lbl['LBL_ORDER_NOW'];

    $payMethodLabel = $langage_lbl['LBL_CASH_PAYMENT_TXT'];

}

$json_lang = json_encode($languageArr);



$takeaway = 'No';
//take away is not for cash delivery so hide for admin and store
if(isTakeAwayEnable() && $db_company[0]['eTakeaway']=='Yes') {
    $takeaway = 'Yes';
    if($fromOrder=='admin' || $fromOrder=='store') { 
        $takeaway = 'No';
    }
    if($APP_PAYMENT_MODE=="Cash") {
        $takeaway = 'No';
    }
}
    

if(isDeliveryPreferenceEnable()) {

    $deliveryPrefSql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_EXTRACT(tTitle, '$.tTitle_".$vLang."')) as tTitle, JSON_UNQUOTE(JSON_EXTRACT(tDescription, '$.tDescription_".$vLang."')) as tDescription, ePreferenceFor, eImageUpload, iDisplayOrder, eContactLess, eStatus FROM delivery_preferences WHERE eStatus = 'Active' AND is_deleted = 0";



    $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);

}


$scSql = "SELECT eShowTerms FROM service_categories WHERE iServiceId = ".$iServiceId;
$scSqlData = $obj->MySQLSelect($scSql);
$eShowTerms = $scSqlData[0]['eShowTerms'];

$selServiceId = $iServiceId;

?>

<!DOCTYPE html>

<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr';?>">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport" content="width=device-width,initial-scale=1">

        <title><?=$pageHead;?></title>

        <meta name="keywords" value="<?=$pageHead;?>"/>

        <meta name="description" value="<?=$pageHead;?>"/>

        <!-- Default Top Script and css -->

        <?php include_once("top/top_script.php"); ?>

        <?php include_once("store_css_include.php"); ?>

        <!--<link rel="stylesheet" href="assets/css/custom-order/screen.css?sdfds" />-->

        <script type="text/javascript" src="https://js.xendit.co/v1/xendit.min.js"></script>

        <?php include_once("top/validation.php"); ?>

        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places,address" type="text/javascript"></script>

        <link href="assets/css/apptype/<?= $template ?>/custom-order/OverlayScrollbars.css" rel="stylesheet">

        <script src="assets/js/custom-order/OverlayScrollbars.min.js"></script>

        <!--<script src="assets/js/custom-order/script-new.js"></script>

            End: Default Top Script and css-->

        <style type="text/css">

            .wallet-bal > div > span {

            font-size: 11px;

            }

            .delivery-pref {

            padding-bottom: 0;

            }

            .delivery-pref:last-child {

            padding-bottom: 20px

            }

            .wallet-bal > label > span {

            font-size: 12px;

            font-weight: normal;

            }

            .wallet-bal > label {

            cursor: pointer;

            }

        </style>

    </head>

    <body onLoad="loadingtime()">

        <div id="main-uber-page">

            <?php include_once("top/left_menu.php"); ?>

            <?php include_once("top/header_topbar.php"); ?>

            <div class="page-contant page-contant-av">

                <div class="product-model-overlay showitempopup" id="#myModal">

                    <div class="product-model">

                        <div class="product-model-left" ></div>

                        <div class="product-model-right">

                            <div class="close-icon">

                                <svg width="16" height="16" viewBox="0 0 14 14">

                                    <path fill="#fff" d="M7 5.586L2.05.636.636 2.05 5.586 7l-4.95 4.95 1.414 1.414L7 8.414l4.95 4.95 1.414-1.414L8.414 7l4.95-4.95L11.95.636 7 5.586z"></path>

                                </svg>

                            </div>

                            <div class="procutcarting-data">

                                <div class="pro-title-hold">

                                    <h4 id="menuitename" ></h4>

                                    <input id="id"  name="id" value="<?=$iCompanyId;?>" type="hidden">

                                    <span class="stock-cost" id="menuitePrice"></span>

                                    <span class="stock-cost" id="menuitemdesc"></span>

                                </div>

                                <div class="scroll-data">

                                    <div  id="optionsvalue"></div>

                                    <div class="special-instruct" id="special_instruct"></div>

                                </div>

                            </div>

                            <div class="increment-cart">

                                <div class="count-block" id="counter-block">

                                    <button class="plus" id="numbercart_minus">-</button>

                                    <span id="count-block"></span>

                                    <button class="minus" id="numbercart_plus" >+</button>

                                </div>

                                <div  id="leading"></div>

                                <button class="addCart-button" id="addtocart" name="addtocart"><?=$languageArr['LBL_ADD_ITEM'];?> </button>

                                <span id="subtotalchange"></span>

                            </div>

                        </div>

                    </div>

                </div>

                <input id="id"  name="id" value="<?=$iCompanyId;?>" type="hidden">

                <div class="heading-rest">

                    <h2><?=$langage_lbl['LBL_MANUAL_STORE_SECURE_CHECKOUT'];?></h2>

                </div>

                <div class="flex-row" id="flex-row-error" align="center">

                    <h3 style="color:#98441ef5;" id="error_message"></h3>

                </div>

                <div class="flex-row restaurantstatus-close" id="flex-row-error"  align="center" style="display:<?php

                    if (strtolower($restaurantstatus) == 'closed') {

                        echo 'block';

                    } else {

                        echo "none";

                    }

                    ?>;">

                    <h3 style="color:#98441ef5;"><?=$langage_lbl['LBL_RESTAURANTS_CLOSE_NOTE'];?></h3>

                </div>

                <div class="site-loader">

                    <img src="<?=$siteUrl;?>assets/img/loading.svg">

                </div>
                <div id="recaptcha-container"></div>
                <div class="rest-menu-place-main">

                    <div class="rest-menu-left">

                        <?php if (empty($_SESSION[$orderUserIdSession])) {?>

                        <div class="payment-block-row active">

                            <div class="payment-block-inner">

                                <p id="errmsg" style="display:none;" class="text-muted btn-block btn btn-danger btn-rect error-login-v"></p>

                                <p style="display:none;" class="btn-block btn btn-rect btn-success error-login-v" id="success" ></p>

                                <i class="user-icon"></i>

                                <div class="payment-head-block">

                                    <h3><?=$langage_lbl['LBL_MANUAL_STORE_ACCOUNT'];?></h3>

                                    <span><?=$langage_lbl['LBL_MANUAL_STORE_ACCOUNT_DESCRIPTION'];?></span>

                                </div>

                                <span class="login-caption"><?=$langage_lbl['LBL_MANUAL_STORE_ENTER_LOGIN_DETAIL_OR'];?> <a href="sign-up-user"><?=$langage_lbl['LBL_CREATE_ACCOUNT'];?></a></span>

                                <form action="store-order?order=<?=$fromOrder;?>" class="form-signin" method ="post" id="login_box" onSubmit="return chkValid('rider', '0');" >

                                    <b>

                                    <input type="hidden" name="action" value="rider"/>

                                    <input type="hidden" name="type_usr" value="Driver"/>

                                    <input type="hidden" name="fromorder" value="<?=$fromOrder;?>"/>

                                    <input name="vEmail" type="text" placeholder="<?=$langage_lbl['LBL_ENTER_EMAIL_ID_OR_MOBILE_TXT'];?>" class="login-input" id="vEmail" value="<?=(SITE_TYPE == 'Demo') ? (($action == 'rider') ? $rider_email : $driver_email) : '';?>" required /></b>

                                    <b>

                                    <input name="vPassword" type="password" placeholder="<?=$langage_lbl['LBL_PASSWORD_LBL_TXT'];?>" class="login-input" id="vPassword" value="<?=(SITE_TYPE == 'Demo') ? '123456' : ''?>" required />

                                    </b>

                                    <b>

                                    <input type="submit" class="submit-but" value="<?=$langage_lbl['LBL_SIGN_IN_TXT'];?>" />

                                    <a onClick="change_heading('forgot')"><?=$langage_lbl['LBL_FORGET_PASS_TXT'];?></a>

                                    </b> 

                                </form>

                                <form action="" method="post" class="form-signin" id="frmforget" onSubmit="return forgotPass();" style="display: none;">

                                    <input type="hidden" name="action" value="rider"/>

                                    <input type="hidden" name="type_usr" value="Driver"/>

                                    <input type="hidden" name="fromorder" value="<?=$fromOrder;?>"/>

                                    <b>

                                    <input name="femail" type="text" placeholder="<?=$langage_lbl['LBL_EMAIL_LBL_TXT'];?>" class="login-input" id="femail" value="" required />

                                    </b>

                                    <b>

                                    <input type="submit" class="submit-but" value="<?=$langage_lbl['LBL_Recover_Password'];?>" />

                                    <a onClick="change_heading('login')"><?=$langage_lbl['LBL_LOGIN'];?></a>

                                    </b>     

                                </form>

                                <!--<img class="payment-place-images" src="<?=$siteUrl;?>assets/img/online-order.png" alt="">-Commented By HJ On 07-06-2019 As Per Discuss With QA Mam Because Not Option For Changed this Image-->

                            </div>

                        </div>

                        <?php } else {?>

                        <?php if ($maualStoreOrderUser == 'user') {?>

                        <div class="payment-block-row passed">

                            <div class="payment-block-inner">

                                <i class="user-icon"></i>

                                <div class="payment-head-block">

                                    <h3><?=$langage_lbl['LBL_MANUAL_STORE_LOG_IN'];?><img src="<?=$siteUrl;?>assets/img/marked.png" alt="" ></h3>

                                </div>

                                <?php if (isset($register_user_data) && !empty($register_user_data)) {?>

                                <span class="login-caption">

                                    <stong><?=$register_user_data[0]['vfullname'];?></stong>

                                    <a href="javascript:void(0);"><?="+" . $register_user_data[0]['vPhoneCode'] . " " . $register_user_data[0]['vPhone'];?></a>

                                </span>

                                <?php }?>

                                <!--<img class="payment-place-images" src="<?=$siteUrl;?>assets/img/online-order.png" alt="">-Commented By HJ On 07-06-2019 As Per Discuss With QA Mam Because Not Option For Changed this Image-->

                            </div>

                        </div>

                        <?php }?>

                        <?php }?>

                        <?php if (empty($_SESSION[$orderUserIdSession])) {?>

                        <div class="payment-block-row">

                            <div class="payment-block-inner">

                                <i class="location-icon"></i>

                                <div class="payment-head-block">

                                    <div class="flex-head">

                                        <h3><?=$langage_lbl['LBL_DELIVERY_ADDRESS'];?></h3>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <?php } else if ($maualStoreOrderUser == 'user') {?>

                        <div class="payment-block-row delivery-address-passed passed">

                            <div class="payment-block-inner">

                                <i class="location-icon"></i>

                                <div class="payment-head-block">

                                    <div class="flex-head">

                                        <h3><?=$langage_lbl['LBL_DELIVERY_ADDRESS'];?><img src="<?=$siteUrl;?>assets/img/marked.png" class="deliveraddressIcon" alt=""></h3>

                                        <button class="edit-button change-address"><?=$langage_lbl['LBL_CHANGE'];?></button>

                                    </div>

                                    <span><?=$langage_lbl['LBL_MANUAL_STORE_MULTIPLE_ADDRESS_DESCRIPTION'];?></span>

                                </div>

                                <!-- <div class="address-wrap"> -->

                                <div class="delivery-address">

                                    <?=$workAddress;?>

                                </div>

                            </div>

                        </div>

                        <?php } else if ($maualStoreOrderUser == 'admin' || $maualStoreOrderUser == 'store') {?>

                            <div class="payment-block-row delivery-address-passed passed">

                                <div class="payment-block-inner">

                                    <i class="location-icon"></i>

                                    <div class="payment-head-block">

                                        <div class="flex-head">

                                            <h3><?=$langage_lbl['LBL_DELIVERY_ADDRESS'];?><img src="<?=$siteUrl;?>assets/img/marked.png" class="deliveraddressIcon" alt=""></h3>

                                            <button class="edit-button change-address"><?=$langage_lbl['LBL_CHANGE'];?></button>

                                        </div>

                                        <span><?=$langage_lbl['LBL_MANUAL_STORE_MULTIPLE_ADDRESS_DESCRIPTION'];?></span>

                                    </div>

                                    <!-- <div class="address-wrap"> -->

                                    <div class="delivery-address">

                                        <?=$workAddress;?>

                                    </div>

                                </div>

                            </div>

                        <?php } if(isDeliveryPreferenceEnable()) { ?>

                            <?php 

                                foreach ($deliveryPrefSqlData as $pkey => $delivery_pref) 

                                { 

                                    if(($APP_PAYMENT_MODE == "Cash" || $maualStoreOrderUser == 'admin' || $maualStoreOrderUser == 'store') && $delivery_pref['eContactLess'] == 'Yes') 

                                    {

                                        unset($deliveryPrefSqlData[$pkey]);

                                    }

                                }

                            ?>

                            <?php if(count($deliveryPrefSqlData) > 0) { ?>

                                <div class="payment-block-row passed delivery-pref-block">

                                    <div class="payment-block-inner">

                                        <i class="checklist-icon"></i>

                                        <div class="payment-head-block">

                                            <div class="flex-head">

                                                <h3><?=$langage_lbl['LBL_DELIVERY_PREF'];?>

                                                    <a href="javascript:void(0);" data-toggle="modal" data-target="#delivery_pref_modal">

                                                        <img src="<?=$siteUrl;?>assets/img/question-mark-new.png" class="deliveraddressIcon" alt="">

                                                    </a>

                                                </h3>

                                            </div>

                                        </div>

                                        <?php $cpref = 1; foreach ($deliveryPrefSqlData as $delivery_pref) { ?>

                                        <div class="wallet-bal delivery-pref <?= 'delivery-pref-'.$delivery_pref['ePreferenceFor'] ?>" style="<?= ($cpref == 1) ? 'margin-top: 15px' : '' ?>">



                                            <label>

                                                <span class="check-holder">

                                                <input type="checkbox" name="selectedPreferences[]" value="<?= $delivery_pref['iPreferenceId'] ?>" data-value="<?= $delivery_pref['eContactLess'] ?>" data-preferenceFor="<?= $delivery_pref['ePreferenceFor'] ?>">

                                                <span class="check-box"></span>

                                                </span>

                                                <b><?= $delivery_pref['tTitle'] ?></b>

                                                <div class="clearfix"></div>

                                                <span><?= $delivery_pref['tDescription'] ?></span>

                                            </label>

                                        </div>

                                        <?php $cpref++; } ?>

                                    </div>

                                </div>

                            <?php } ?>

                        <?php } ?>

                        

                        <?php if($takeaway=='Yes') { ?>

                            <div class="payment-block-row passed">

                                <div class="payment-block-inner">

                                    <i class="location-icon"></i>

                                    <div class="payment-head-block">

                                        <div class="flex-head">

                                            <h3><?=$langage_lbl['LBL_DELIVERY_TYPE'];?><img src="<?=$siteUrl;?>assets/img/marked.png" class="deliveraddressIcon" alt=""></h3>

                                        </div>

                                    </div>

                            

                                    <div class="payment-delivery-type">

                                        <div class="radio-combo">

                                            <div class="label-data-hold">

                                                <span class="radio-holder">

                                                    <input type="radio" name="eTakeAway" value="No" id="delivery-your-door" checked>

                                                    <span class="radio-box"></span>

                                                </span>

                                                <label for="delivery-your-door"><?= $langage_lbl['LBL_DELIVER_TO_YOUR_DOORS']; ?></label>

                                            </div>
                                            
                                            <div class="label-data-hold">

                                                <span class="radio-holder">

                                                    <input type="radio" name="eTakeAway" value="Yes" id="takeaway">

                                                    <span class="radio-box"></span>

                                                </span>

                                                <label for="takeaway"><?= $langage_lbl['LBL_TAKE_AWAY']; ?></label>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php } ?>



                        <input type="hidden" name="payment" id="payment" checked="checked">

                        <?php if (empty($_SESSION[$orderUserIdSession])) {?>

                        <div class="payment-block-row pay-card paymentbutton">

                            <div class="payment-block-inner">

                                <i class="pay-icon"></i>

                                <div class="payment-head-block">

                                    <h3><?=$payMethodLabel;?></h3>

                                </div>

                            </div>

                        </div>

                        <?php } else {?>

                        <div class="payment-block-row active pay-card paymentbutton">

                            <div class="payment-block-inner">

                                <i class="pay-icon"></i>

                                <div class="payment-head-block">

                                    <h3><?=$payMethodLabel;?><img src="<?=$siteUrl;?>assets/img/marked.png" class="PaymentIcon" style="display: none;" alt=""></h3>

                                    <?php if ($changeDesign == 1) {?>

                                    <span><?=$langage_lbl['LBL_MANUAL_STORE_PAYMENT_METHOD_TEXT'];?></span>

                                    <?php }?>

                                </div>

                                <div class="payment-option-data">

                                    <ul class="vaertical-tabs">

                                        <?php if ($maualStoreOrderUser == 'user') {

                                            if (strtoupper($APP_PAYMENT_MODE) == 'CASH' || strtoupper($APP_PAYMENT_MODE) == 'CASH-CARD') {?>

                                        <li data-payment="cash-data" class="pay-ico active" disable><?=$langage_lbl['LBL_PAY_VIA_CASH'];?></li>

                                        <?php } if (strtoupper($APP_PAYMENT_MODE)=='CARD' || strtoupper($APP_PAYMENT_MODE)=='CASH-CARD') { ?>

                                        <li data-payment="card-data" class="card-ico <? if(strtoupper($APP_PAYMENT_MODE)=='CARD') { ?> active<?php } ?>"><? if(strtoupper($SYSTEM_PAYMENT_FLOW)=='METHOD-1') { echo $langage_lbl['LBL_PAY_VIA_CARD']; } else { echo $langage_lbl['LBL_PAY_VIA_WALLET']; } ?></li>

                                        <?php

                                            }

                                            } else {

                                            ?>
                                            <li data-payment="cash-data" class="pay-ico active" disable><?=$langage_lbl['LBL_PAY_VIA_CASH'];?></li>
                                            <?php

                                            } ?>
                                    </ul>

                                    <div class="payment-tab-data">

                                        <?php if (strtoupper($APP_PAYMENT_MODE) == 'CASH' || strtoupper($APP_PAYMENT_MODE) == 'CASH-CARD' || $maualStoreOrderUser == "admin" || $maualStoreOrderUser == "store") {?>

                                        <div id="cash-data" class="pay-tab-data active">

                                            <img src="<?=$siteUrl;?>assets/img/pay-placeholder.png" alt="">

                                            <form>

                                                <?php if ($changeDesign == 0) {?>

                                                <div class="pay-cash-caption">

                                                    <strong><?=$langage_lbl['LBL_CASH_TXT'];?></strong>

                                                    <p><?=$langage_lbl['LBL_MANUAL_STORE_MENU_CASH_DESCRIPTION'];?></p>

                                                </div>

                                                <?php }?>

                                                    <input type="submit" value="<?= $payButtonLabel; ?>" class="submitplaceorder" id="submitplaceorder">

                                            </form>

                                        </div>

                                        <?php } if ($maualStoreOrderUser == 'user' && (strtoupper($APP_PAYMENT_MODE)=='CARD' || strtoupper($APP_PAYMENT_MODE)=='CASH-CARD')) { ?>

                                        <div id="card-data" class="pay-tab-data <? if(strtoupper($APP_PAYMENT_MODE)=='CARD') { ?> active<?php } ?>">

                                            <img src="<?= $siteUrl; ?>assets/img/apptype/<?= $template ?>/credit-card.png" alt="">

                                            <form>

                                                <div class="pay-cash-caption">

                                                    <strong><? if(strtoupper($SYSTEM_PAYMENT_FLOW)=='METHOD-1') { echo $langage_lbl['LBL_CARD']; } else { echo $langage_lbl['LBL_WALLET_TXT']; } ?></strong>

                                                    <span><?= $langage_lbl['LBL_MANUAL_STORE_MENU_CREDIT_CARDS_DESCRIPTION']; ?></span>

                                                </div>
                                                <input type="submit" value="<?=$payButtonLabel;?>" class="submitplaceorderCard" data-method="<?= strtolower($APP_PAYMENT_METHOD); ?>">

                                            </form>

                                        </div>

                                        <?php }?>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <?php }?>

                        <div class="select-card-type select-payment"  style="display:none" <?php

                            if (empty($_SESSION[$orderUserIdSession])) {

                                echo 'style="display:none"';

                            }

                            ?> <?php

                            if ($APP_PAYMENT_MODE != 'Card') {

                                echo 'style="display:none"';

                            }

                            ?>>

                            <h4><?=$langage_lbl['LBL_CARD_PAYMENT_DETAILS'];?></h4>

                            <table class="table table-bordered address-table">

                                <input type="hidden" name="iUserId" id="iUserId" value="15">

                                <tbody>

                                    <tr>

                                        <td width="50px" colspan=2><?=$langage_lbl['LBL_DELIVER_CARD_DESC_TXT'];?></td>

                                    </tr>

                                    <tr>

                                        <td width="50px"><span class="radio-holder"><input type="radio" checked="" name="selCardType" id="selCardType" value="0"><span class="radio-box"></span></span>

                                        </td>

                                        <td width="100%"><?=$langage_lbl['LBL_DELIVER_MANAGE_CARD'];?></td>

                                    </tr>

                                    <tr>

                                        <td width="50px"><span class="radio-holder"><input type="radio" name="selCardType" id="selCardType" value="1"><span class="radio-box"></span></span>

                                        </td>

                                        <td width="100%"><?=$langage_lbl['LBL_INSTANT_PAYMENT_PAGE_TITLE_TXT'];?></td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                    <ul id="main_list" class="order_list_d" style="display:none">

                        <div class="" id="imageIcons" style="width:100%;">

                            <div align="center">

                                <img src="default.gif">

                                <span>Retrieving <?=$langage_lbl['LBL_DIVER'];?> list.Please Wait...</span>

                            </div>

                        </div>

                    </ul>

                    <div class="rest-menu-place-right _PT0_">

                        <div class="checkout-block" >

                            <div id="block"></div>

                            <div id="checkout-block"></div>

                            <div id="checkout-save-block"></div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="product-model-overlay" id="#myModal">

                <div class="product-model">

                    <style type="text/css">

                        .credit-card-box .panel-title {

                        display: inline;

                        font-weight: bold;

                        }

                        .credit-card-box .form-control.error {

                        border-color: red;

                        outline: 0;

                        box-shadow: inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(255,0,0,0.6);

                        }

                        .credit-card-box label.error {

                        font-weight: bold;

                        color: red;

                        padding: 2px 8px;

                        margin-top: 2px;

                        }

                        .credit-card-box .payment-errors {

                        font-weight: bold;

                        color: red;

                        padding: 2px 8px;

                        margin-top: 2px;

                        }

                        .credit-card-box label {

                        display: block;

                        }

                        .submit-button {

                        background-color: #1ace9b;

                        color: #ffffff;

                        }

                        .overlay {

                        position: absolute;

                        top: 0;

                        left: 0;

                        height: 100%;

                        width: 100%;

                        background-color: rgba(0,0,0,0.5);

                        z-index: 10;

                        }

                        #three-ds-container {

                        width: 550px;

                        height: 450px;

                        line-height: 200px;

                        position: fixed;

                        top: 25%;

                        left: 40%;

                        margin-top: -100px;

                        margin-left: -150px;

                        background-color: #ffffff;

                        border-radius: 5px;

                        text-align: center;

                        z-index: 11; /* 1px higher than the overlay layer */

                        }

                        pre {

                        white-space: pre-wrap;

                        }

                        div.request {

                        width: 50%;

                        float: left;

                        }

                        pre.result {

                        width: 49%;

                        }

                        .promocodefullinput{

                        width: 100% !important;

                        }

                        .promocodehalfinput{

                        width: 283px !important;

                        }

                    </style>

                    <div class="main-part">

                        <div class="page-contant">

                            <div class="page-contant-inner">

                                <div class="card-form">

                                    <?php if ($userAmount != "") {?>

                                    <div class="our-work-new" style="background-color: #<?=$themeColor;?>;color: #<?=$textColor;?>;">

                                        <span class="our-text" style="font-size: 18px;padding: 10px;">Pay : <?=$userAmount;?></span>

                                    </div>

                                    <?php }?>

                                    <form action="" method="post" id="checkout">

                                        <input type="hidden" name="xendit_token">

                                        <div class="back-img" style="padding-top: 20px;"><img src="<?=$siteUrl;?>assets/img/custom-order/card.png"></div>

                                        <span style="color: red;" id="token_errors"></span>

                                        <input class="form-control" type="hidden" id="api-key" placeholder="API Key" value="<?=$xenditPublishKey;?>"/>

                                        <div class="our-work-new">

                                            <span class="our-text"><?=$langage_lbl['LBL_CARD_NUMBER_TXT'];?></span>

                                        </div>

                                        <label class="field" for="xenditcardnumber">

                                        <b class="class-box" >

                                        <input class="card-number" data-xendit="number" type="number" id="xenditcardnumber" size="20" autocomplete="off" maxlength="20" data-encrypted-name="number" placeholder="<?=$langage_lbl['LBL_CARD_NUMBER_TXT']?>" onKeyPress="return isNumber(event)" required=""/>

                                        </b>

                                        </label>

                                        <span id="cardType"></span>

                                        <div class="our-work-new">

                                            <span class="our-text"><?=$langage_lbl['LBL_CVV'];?></span>

                                        </div>

                                        <label class="field" for="xenditcvc">

                                        <b class="class-box">

                                        <input class="card-cvc" data-xendit="security_code" id="card-cvc" type="password" id="xenditcvc" size="4" maxlength="4" autocomplete="off" data-encrypted-name="<?=$langage_lbl['LBL_CVV']?>" min="1" max="999" placeholder="CVV" onkeypress="return isNumber(event)" required="" />

                                        </b>

                                        </label>

                                        <div class="our-work-new">

                                            <span class="our-text"><?=$langage_lbl['LBL_CARD_HOLDER_NAME_TXT'];?></span>

                                        </div>

                                        <label class="field" for="xenditholder">

                                        <b class="class-box">

                                        <input  type="text" data-xendit="holder_name" id="xenditholder" size="20" autocomplete="off" placeholder="<?=$langage_lbl['LBL_CARD_HOLDER_NAME_TXT']?>" data-encrypted-name="holderName" required="" />

                                        </b>

                                        </label>

                                        <div class="our-work-new">

                                            <span class="our-text"><?=$langage_lbl['LBL_EXPIRATION_DATE_TXT'];?></span>

                                        </div>

                                        <label class="field-a" for="xenditmonth">

                                        <b  class="class-box-a">

                                        <input class="card-expiry-month" max="12" data-xendit="expiration_month" inputmethod="numeric" type="number" onKeyUp="this.value = minmax(this.value, '', 12)"  id="xenditmonth" maxlength="2" size="2" autocomplete="off" onKeyPress="return isNumber(event)" data-encrypted-name="expiryMonth" placeholder="<?=$langage_lbl['LBL_EXP_MONTH_HINT_TXT'];?>" required=""  />

                                        </b>

                                        </label>

                                        <img class="float-work" src="<?=$siteUrl;?>assets/img/custom-order/line.jpg">

                                        <b  class="class-box-b">

                                        <input class="card-expiry-year" type="text" data-xendit="expiration_year" id="xendityear" maxlength="4" size="4" autocomplete="off" data-encrypted-name="expiryYear" onKeyPress="return isNumber(event)" placeholder="<?=$langage_lbl['LBL_EXP_YEAR_HINT_TXT'];?>" required=""/>

                                        </b>

                                        <div class="work-card">

                                            <div class="card-num-a">

                                                <button type="submit" class="submit button-num" id="create_token">Submit Payment</button>

                                            </div>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                        <div id="three-ds-container" style="display: none;">

                            <iframe height="450" width="550" id="sample-inline-frame" name="sample-inline-frame"> </iframe>

                        </div>

                    </div>

                </div>

            </div>

            <div class="product-model-overlay" id="new-card-model">

                <div class="product-model payment-block-row">

                    <form class="add-new-card-data" name="frmcreditcard" id="frmcreditcard" onSubmit="return false;">

                        <div class="close-icon">

                            <svg width="16" height="16" viewBox="0 0 14 14">

                                <path fill="#fff" d="M7 5.586L2.05.636.636 2.05 5.586 7l-4.95 4.95 1.414 1.414L7 8.414l4.95 4.95 1.414-1.414L8.414 7l4.95-4.95L11.95.636 7 5.586z"></path>

                            </svg>

                        </div>

                        <h3><?=$langage_lbl['LBL_MANUAL_STORE_ENTER_CREDIT_CARD_TEXT'];?></h3>

                        <div class="form-column-full">

                            <input type="text" placeholder="<?=$langage_lbl['LBL_CARD_NUMBER_TXT'];?>" name="elecardnumber" id="elecardnumber" maxlength="16" />

                        </div>

                        <div class="flex-row">

                            <div class="form-column-full card-validity"><input type="text" placeholder="<?=$langage_lbl['LBL_MANUAL_STORE_ACCOUNT_CREDIT_CARD_VALIDITY'];?>" name="elecardvalidity" id="elecardvalidity" maxlength="7"   /></div>

                            <div class="form-column-full"><input type="text" placeholder="<?=$langage_lbl['LBL_CVV'];?>" class="card-cvv" id="elecardcvv" name="elecardcvv" maxlength="4" / ></div>

                        </div>

                        <div class="form-column-full">

                            <input type="text" placeholder="<?=$langage_lbl['LBL_CARD_HOLDER_NAME_TXT'];?>" name="elecardcardname" id="elecardcardname"/>

                        </div>

                        <input type="submit" value="<?=$langage_lbl['LBL_BTN_PAYMENT_TXT'];?>" class="submitcreditcard" />

                        <span class="note-desc"></span>

                        <div class="text-align-center">

                            <img src="<?=$siteUrl;?>assets/img/payment-option.png" alt="">

                        </div>

                    </form>

                </div>

            </div>

            <!-- home page end-->

            <!-- footer part -->

            <?php include_once('footer/footer_home.php'); ?>

            <!-- End:contact page-->

            <div style="clear:both;"></div>

        </div>

        <!-- footer part end -->

        <!-- Footer Script -->

        <?php include_once('top/footer_script.php'); ?>

        <script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>

        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>

        <!-- End: Footer Script -->

        <div class="product-model-overlay" id="delivery-address-model">

            <div class="product-model payment-block-row">

                <form action="javascript:;" class="general-form-new add-new-card-data" name="deliveryaddressfrm" id="deliveryaddressfrm" method="post" class="clearfix">

                    <div class="close-icon">

                        <svg width="16" height="16" viewBox="0 0 14 14">

                            <path fill="#fff" d="M7 5.586L2.05.636.636 2.05 5.586 7l-4.95 4.95 1.414 1.414L7 8.414l4.95 4.95 1.414-1.414L8.414 7l4.95-4.95L11.95.636 7 5.586z"></path>

                        </svg>

                    </div>

                    <h3><?=$langage_lbl['LBL_MANUAL_STORE_ENTER_ADRESS_TEXT']?></h3>

                    <div class="form-column-full">

                        <input type="hidden" name="from_lat_long" id="from_lat_long" value="<?=$from_lat_long;?>" >

                        <input type="hidden" name="from_lat" id="from_lat" value="<?=$vLatitude;?>" >

                        <input type="hidden" name="from_long" id="from_long" value="<?=$vLongitude;?>" >

                    </div>

                    <div class="form-column-full">

                        <input type="text" name="vServiceAddress" id="vServiceAddress" autocomplete="off" placeholder="<?=$langage_lbl['LBL_DELIVERY_ADDRESS'];?>"  />

                        <div id="vlocation" class="vlocation"></div>

                        <input type="hidden" name="from_lat_long_frm" id="from_lat_long_frm" value="" >

                        <input type="hidden" name="from_lat_frm" id="from_lat_frm" value="" >

                        <input type="hidden" name="from_long_frm" id="from_long_frm" value="" >

                    </div>

                    <div class="form-column-full">

                        <input type="text" name="vBuildingNo" id="vBuildingNo" autocomplete="off" placeholder="<?=$langage_lbl['LBL_JOB_LOCATION_HINT_INFO'];?>"  />

                    </div>

                    <div class="form-column-full">

                        <input type="text" name="vLandmark" id="vLandmark" autocomplete="off" placeholder="<?=$langage_lbl['LBL_LANDMARK_HINT_INFO'];?>"/>

                    </div>

                    <div class="form-column-full">

                        <input type="text" name="vAddressType" id="vAddressType" autocomplete="off" placeholder="<?=$langage_lbl['LBL_ADDRESSTYPE_HINT_INFO'];?>"/>

                    </div>

                    <div class="flex-button-block">

                        <input type="submit" class="submitcreditcard" id="new_one" style="text-transform:inherit;" value="<?=$langage_lbl['LBL_SAVE_ADDRESS_TXT'];?>">

                        <input type="button" class="submitcreditcard" id="new_one" value="<?=$langage_lbl['LBL_CANCEL_TXT'];?>" onClick="canceladdress()">

                    </div>

                </form>

            </div>

        </div>

        <div class="custom-modal-main" id="custom-alert" style="display:none">

            <div class="custom-modal" role="document">

                <div class="">

                    <div class="model-header">

                        <input type="hidden" name="iDriverId_temp" id="iDriverId_temp">

                        <h4 class="modal-title" id="inactiveModalLabel">Low Wallet Balance</h4>

                        <i class="icon-close" data-dismiss="modal"></i>

                    </div>

                    <div class="model-body">

                    </div>

                    <div class="model-footer button-block">

                        <button type="button" class="btn btn-default gen-btn" data-dismiss="modal">Not Now</button>

                        <button type="button" class="btn btn-success btn-ok action_modal_submit gen-btn" data-dismiss="modal" onclick="AssignDriver('');">OK</button>

                    </div>

                </div>

            </div>

        </div>

        <!--<div id="usermodel">

            </div>-->



        <?php 

            if(isDeliveryPreferenceEnable()) 

            { 

                include_once('delivery_pref_modal.php');

            } 

        ?>

        <?php 
            if(isEnableTermsServiceCategories() && $eShowTerms == "Yes") 
            { 
                include_once('age_restriction_modal.php');
            } 
        ?>
    </body>

    <script src="https://js.stripe.com/v3/"></script>

    <script src="assets/js/modal_alert.js"></script>

    <script src="https://www.gstatic.com/firebasejs/6.3.3/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/6.3.3/firebase-auth.js"></script>
    <? include_once ('firebasephoneverify.php'); ?>
    <script>
        <?php if(isEnableTermsServiceCategories() && $eShowTerms == "Yes") { ?>

            $(document).ready(function () {
                if(getCookie('AGE_RESTRICTION_<?= $selServiceId ?>') == "")
                {
                    $('#age_restriction').prop('checked', false);
                    $('#restriction_modal').modal({backdrop: 'static',keyboard: false},'show');
                    $('#restriction_modal').addClass('custom-modal-main active')
                    $('body').css('overflow', 'hidden');
                }
            });
            
            if(getCookie("goBackUrl") == "")
            {
                document.cookie = "goBackUrl="+document.referrer;
            }

            $("body").on("contextmenu", function(e){
                if(getCookie('AGE_RESTRICTION_<?= $selServiceId ?>') != "")
                {
                    return true;
                }
                else{
                    return false;    
                }
            });

            $(document).keydown(function(e){
                if(e.which === 123){
                    if(getCookie('AGE_RESTRICTION_<?= $selServiceId ?>') != "")
                    {
                        return true;
                    }
                    else{
                        return false;    
                    }
                }
            });

            $('body').attr('unselectable','on')
                .css({'-moz-user-select':'-moz-none',
                   '-moz-user-select':'none',
                   '-o-user-select':'none',
                   '-khtml-user-select':'none',
                   '-webkit-user-select':'none',
                   '-ms-user-select':'none',
                   'user-select':'none'
                })
                .bind('selectstart', function(){ 
                    return false; 
                });

            $('#age_restriction_btn').click(function() {
                if($('#age_restriction').prop('checked') == false)
                {
                    $('.checkmark').addClass('check-error');
                    $('.check-required').show();
                    return false;
                }
                else{
                    $('.checkmark').removeClass('check-error');
                    $('.check-required').hide();
                    var date = new Date();
                    date.setTime(date.getTime() + (60 * 1000));
                    document.cookie = "AGE_RESTRICTION_<?= $selServiceId ?>="+date.toGMTString()+"; expires="+date.toGMTString();
                    removeRestrictionCss();
                    $('#restriction_modal').modal('hide');
                    $('body').css('overflow', 'auto');
                }
            });

            $('#age_restriction').click(function() {
                if($(this).prop('checked') == true)
                {
                    $('.checkmark').removeClass('check-error');
                    $('.check-required').hide();
                }
            });
            
            $('.rest-listing li a').click(function(e) {
                if(getCookie('AGE_RESTRICTION_<?= $selServiceId ?>') != "")
                {
                    return true;
                }
                else{
                    e.preventDefault();
                }
            });

            $('.rest-listing li a').on('contextmenu', function(e) {
                if(getCookie('AGE_RESTRICTION_<?= $selServiceId ?>') != "")
                {
                    return true;
                }
                else{
                    return false;
                }
            });

            function removeRestrictionCss()
            {
                $('body').attr('unselectable','on')
                    .css({'-moz-user-select':'',
                       '-moz-user-select':'',
                       '-o-user-select':'',
                       '-khtml-user-select':'',
                       '-webkit-user-select':'',
                       '-ms-user-select':'',
                       'user-select':''
                    })
                    .bind('selectstart', function(){ 
                        return true; 
                    });
            }

            function goBack()
            {
                if(getCookie('goBackUrl') != "")
                {
                    window.location.href = getCookie('goBackUrl');
                }
                else{
                    window.location.href = document.referrer;
                }
            }

            function getCookie(cname) {
                var name = cname + "=";
                var decodedCookie = decodeURIComponent(document.cookie);
                var ca = decodedCookie.split(';');
                for(var i = 0; i <ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }
        <?php } ?>

        $('#usershoppingcart').html('');

        $("#main_list").hide();

        var optionData = [];

        var optionscountervalue;

        var jsonaddon = [];

        var addoncountervalue;

        var otherData = [];

        var othercountervalue;

        var subresult;

        var subresultvalues;

        var subresultvaluesconter;

        var otherinputType;

        var deliveryAddressCount;

        var eWalletAmtApplied;

        

        var languagedata=[];

        var OptionMinSelection, OptionMaxSelection;

        var radioValueother;

        var eOptionInputType;

        var checkeddata = "No";

        var ItemId;

        var MenuId;

        var id;

        var result;

        var typed, no, qty;

        

        var restaurant_status = '<?=strtolower($restaurantstatus);?>';

        deliveryAddressCount = '<?=$iUserAddressId;?>';

        var siteType = '<?=SITE_TYPE;?>';

        var numbers;

        var price;

        var currencySymbol;

        var radioValue = '';

        var newnumber;

        

        var resultData;

        var innerDiv, resultDataall, payment, Action, message;

        innerDiv = document.createElement('div');

        innerDiv.id = 'block-2';

        innerDiv.className = 'block-2';

        var typed, no, qty, typeofitem;

        var verification_code;

        languagedata = <?php echo $json_lang; ?>;

        function showMenuTypes(id, typed, no)

        {

            var tInst = '';

            var oselected, tselected;

            var valsother = '';

            var Company = '<?=$iCompanyId;?>';

            var radioValue = '';

            var fromOrder = '<?=$fromOrder;?>';

            $.ajax({

                type: "POST",

                url: "ajax_load_model_cart.php",

                data: {id: id, Company: Company, typed: typed, no: no, fromorder: fromOrder},

                dataType: "json",

                success: function (dataHtml)

                {

                    if (dataHtml.counter == "1")

                    {

                        if (dataHtml.counter == "1")

                        {

                            result = dataHtml.responce;

                            isPriceShow = dataHtml.ispriceshow;

                            var vImage = result.vImage;

                            qty = parseInt(result.Qty);

                            typeofitem = result.type;

                            if (result.vImageName == '')

                            {

                                $('.product-model-left').addClass('hasPlaceHolder');

                                $('.product-model-left').css('background-image', 'url(' + vImage + ')');

                            } else {

                                $('.product-model-left').removeClass('hasPlaceHolder');

                                $('.product-model-left').css('background-image', 'url(' + vImage + ')');

                            }

                            optionData = result.options;

                            optionscountervalue = result.optionscounter;

                            jsonaddon = result.addon;

                            addoncountervalue = result.addoncounter;

                            othercountervalue = result.otherAddonscounter;

                            subresult = result.otherAddons;

                            $('#menuitename').html(result.vItemType);

                            var PriceData = "";

                            if (result.discountoption == 'Yes')

                            {

                                PriceData += result.LBL_PRICE_FOR_MENU_ITEM + ': ' + result.fdiscountedPrice + '&nbsp;&nbsp;<span style="text-decoration: line-through">' + result.fmainPrice + '</span> ';

                            } else {

                                PriceData += result.LBL_PRICE_FOR_MENU_ITEM + ': ' + result.fmainPrice;

                            }

                            $('#menuitePrice').html(PriceData);

                            var itemDescription = result.vItemDesc;

                            $('#menuitemdesc').html("");

                            if (itemDescription.trim() != "") {

                                //$('#menuitemdesc').html(result.LBL_DESCRIPTION + ": " + result.vItemDesc); // Removed Lable As Per Discuss with CD sir On 27-06-2019

                                $('#menuitemdesc').html(result.vItemDesc);

                            }

                            $('#optionsvalue').html("");

                            if (result.optionscounter > 0)

                            {

                                $('#optionsvalue').html('<div class="extra-det" ><div><strong>' + result.LBL_SELECT_OPTIONS + '</strong><span>(<?= addslashes($langage_lbl['LBL_MANUAL_STORE_CHOOSE_MIN_ONE']); ?>)</span></div><label><?= addslashes($langage_lbl['LBL_MANUAL_STORE_POPUP_REQUIRED']); ?></label></div>');

                                var resultData = "<ul class='what-extra'>";

                                resultData += '<span id="optionserror" style="margin-left: 10px; color: #fb0000; display:none;"></span>';

                                for (var i = 0; i < result.optionscounter; i++)

                                {

                                    oselected = result.options[i].selected;

                                    resultData += '<li>';

                                    resultData += '<div>';

                                    resultData += '<span class="radio-holder">';

                                    if (oselected == 'Yes')

                                    {

                                        resultData += '<input type="radio" name="options" checked id="options" onChange="options(this.value)" value="' + result.options[i].iOptionId + '"/>';

                                    } else {

                                        resultData += '<input type="radio" name="options" id="options" onChange="options(this.value)" value="' + result.options[i].iOptionId + '"/>';

                                    }

                                    resultData += '<input type="radio" name="options" id="options" onChange="options(this.value)" value="' + result.options[i].iOptionId + '"/>';

                                    resultData += '<span class="radio-box"></span>';

                                    resultData += '</span>';

                                    resultData += '<label for="1">' + result.options[i].vOptionName + '</label>';

                                    resultData += '</div>';

                                    resultData += '<div class="costing">' + result.options[i].fUserPriceWithSymbol + '</div>';

                                    resultData += '</li>';

                                }

                                resultData += '</ul>';

                                $('#optionsvalue').append(resultData);

                            }

                            if (result.addoncounter > 0)

                            {

                                $('#optionsvalue').append('<div class="extra-det"><div><strong>' + result.LBL_SELECT_TOPPING + '</strong></div></div>');

                                var resultAddonData = "<ul class='what-extra'>";

                                resultAddonData += '<span id="addonerror" style="margin-left: 10px; color: #fb0000; display:none;"></span>';

                                for (var ii = 0; ii < result.addoncounter; ii++)

                                {

                                    tselected = result.addon[ii].selected;

                                    resultAddonData += '<li>';

                                    resultAddonData += '<div>';

                                    resultAddonData += '<span class="check-holder">';

                                    if (tselected == 'Yes')

                                    {

                                        resultAddonData += '<input type="checkbox" id="addon" checked name="addon[]" onChange="addon(this.value)" value="' + result.addon[ii].iOptionId + '"/>';

                                    } else {

                                        resultAddonData += '<input type="checkbox" id="addon" name="addon[]" onChange="addon(this.value)" value="' + result.addon[ii].iOptionId + '"/>';

                                    }

                                    resultAddonData += '<span class="check-box"></span>';

                                    resultAddonData += '</span>';

                                    resultAddonData += '<label for="1">' + result.addon[ii].vOptionName + '</label>';

                                    resultAddonData += '</div>';

                                    resultAddonData += '<div class="costing">' + result.addon[ii].fUserPriceWithSymbol + '</div>';

                                    resultAddonData += '</li>';

                                }

                                resultAddonData += '</ul>';

                                $('#optionsvalue').append(resultAddonData);

                            }

                            $('#count-block').html('<input id="numbercart"  name="numbercart" value="' + qty + '" type="text" readonly>');

                            $('#count-block').append('<input id="typeofitem"  name="typeofitem" value="' + typeofitem + '" type="hidden" readonly><input id="no"  name="no" value="' + no + '" type="hidden" readonly>');

                            $('#subtotalchange').html(result.fDiscountPricewithsymbol);

                            $('#leading').html('<input id="price"  name="price" value="' + result.fDiscountPricest + '" type="hidden"/>');

                            $('#leading').append('<input id="currencySymbol"  name="currencySymbol" value="' + result.currencySymbol + '" type="hidden"/>');

                            $('.showitempopup').addClass('active');

                        }

                    }

                }

            });

        }

        function submitorder()

        {

            if (deliveryAddressCount <= 0 || deliveryAddressCount == "") {

                alert("<?=$deliverAddresMsg;?>");

                return false;

            }

            if (siteType == "Demo") {

                //Changes to remove coloum error for not showing checkout box bug

                alert('<?= str_replace("'", "", $finalMsg) ?>');

            }

            $('.submitplaceorder').addClass('disabled');

            $('.btncreditcard').addClass('disabled');

            $('.site-loader').addClass('active');

            $('.submitplaceorder').removeClass('disabled');

            $('.btncreditcard').removeClass('disabled');

            id = $('#id').val();

            Instruction = $('#Instruction').val();

            payment = $("#payment").val();

            var ePayWallet = 0;

            ePayWallet = $("#ePayWallet:checked").length;

            var CheckUserWallet = '';

            if (ePayWallet == 0) {

                CheckUserWallet = 'No';

                ePayWallet = "No";

            } else {

                CheckUserWallet = 'Yes';

                ePayWallet = "Yes";

            }

            var fromOrder = '<?=$fromOrder;?>';

        

            Instruction = $('#Instruction').val();

            payment = 'Card';

            var ePayWallet = 0;

            ePayWallet = $("#ePayWallet:checked").length;

            var CheckUserWallet = '';

            if (ePayWallet == 0) {

                CheckUserWallet = 'No';

            } else {

                CheckUserWallet = 'Yes';

            }

        

        

            var SYSTEM_PAYMENT_FLOW = "<?=$SYSTEM_PAYMENT_FLOW?>";

        

            /*ePayWallet = "No";

            if(SYSTEM_PAYMENT_FLOW!='Method-1') {

                CheckUserWallet = 'Yes';

                ePayWallet = 'Yes';

            }*/

            eWalletIgnore = "No";

            var fromOrder = '<?= $fromOrder; ?>';

            var orderdetails = '<?=json_encode($_SESSION["ORDER_DETAILS_" . strtoupper($fromOrder)])?>';

             console.log(orderdetails+"+++++");

            var couponcode = '<?php !empty($_SESSION[$orderCouponSession]) ? json_encode($_SESSION[$orderCouponSession]) : ''?>';

            var data = {

                "tSessionId": '<?=$Datauser[0]['tSessionId']?>',

                //"vDeviceToken": '<?=$Datauser[0]['vDeviceToken']?>',

                "GeneralMemberId": '<?= $Datauser[0]['iUserId'] ?>',

                "GeneralUserType": 'Passenger',

                "type": 'CheckOutOrderDetails',

                "iUserId": '<?=$iUserId?>',

                "iUserAddressId": '<?=$iUserAddressId?>',

                "iCompanyId": '<?=$iCompanyId?>',

                "vCouponCode": couponcode,

                "ePaymentOption": 'Cash',

                "OrderDetails": orderdetails,

                "vInstruction": $("#Instruction").val(),

                "iOrderId": '',

                "iServiceId":'<?=$iServiceId?>',

                "CheckUserWallet": CheckUserWallet,

                "eWalletIgnore": eWalletIgnore,

                "ePayWallet": ePayWallet,

                "fromOrder": fromOrder,

            };

        

            data = $.param(data);

        

            $.ajax({

                type: "POST",

                url: "<?=$tconfig["tsite_url"] . ManualBookingAPIUrl;?>",

                data: data,

                dataType: 'json',

                success: function (dataHtml)

                {

                        $('.site-loader').removeClass('active');

                        Action = dataHtml.Action;

                        message = dataHtml.message;

                        iOrderId = dataHtml.iOrderId;

                        if (Action == 1)

                        {

                            $('.pay-card').removeClass('tab-disable');

                            <?php if ($maualStoreOrderUser == 'admin') {?>

                                    window.location.href = "admin/allorders.php?type=processing";

                            <?php } else if ($maualStoreOrderUser == 'store') {?>

                                    window.location.href = "processing-orders";

                            <?php } else if ($maualStoreOrderUser == 'user') {?>

                                    window.location.href = "thanks.php?orderid=" + iOrderId;

                            <?php }?>

                        } else {

                            if (message != 'Restaurants is close.') {

                                $('.paymentbutton').addClass('tab-disable');

                            }

                            $('#error_message').show();

                            $('#error_message').html('<h3 style="color:#98441ef5;">' + message + '</h3>');

                        }

                }

            });

                /*$.ajax({

                    type: "POST",

                    url: "ajax_checkout_order_details.php",

                    data: {id: id, payment: payment, Instruction: Instruction, CheckUserWallet: CheckUserWallet, fromorder: fromOrder},

                    dataType: "JSON",

                    success: function (dataHtml)

                    {

                        $('.site-loader').removeClass('active');

                        Action = dataHtml.Action;

                        message = dataHtml.message;

                        iOrderId = dataHtml.iOrderId;

                        if (Action == 1)

                        {

                            $('.pay-card').removeClass('tab-disable');

                            <?php if ($maualStoreOrderUser == 'admin') {?>

                                                        window.location.href = "admin/allorders.php?type=processing";

                            <?php } else if ($maualStoreOrderUser == 'store') {?>

                                                        window.location.href = "processing-orders";

                            <?php } else if ($maualStoreOrderUser == 'user') {?>

                                                        window.location.href = "thanks.php?orderid=" + iOrderId;

                            <?php }?>

                        } else {

                            if (message != 'Restaurants is close.') {

                                $('.paymentbutton').addClass('tab-disable');

                            }

                            $('#error_message').show();

                            $('#error_message').html('<h3 style="color:#98441ef5;">' + message + '</h3>');

                        }

                    }

                });*/

        }

        

        var txtnote = '<?php echo $langage_lbl['LBL_VARIFICATION_CODE_SENT_TO_MOBILE'];?>';

        var MobileNo= '<?= $register_user_data[0]['vPhone']; ?>';

        var res = txtnote.replace("#MOBILE_NO#", MobileNo);

        var userphoneNumber;
        
        var newDiv;

        var verifysmscontent = "<div class='fetched-data'><div class='verifytxt' style='padding: 10px 0;'>"+res+"</div><form class='formverify' role='form' id='formverify'><div class='form-group'><label for='nom'>Verification Code</label>&nbsp;&nbsp;<input type='text' class='form-control' name ='verificationcode' id='verificationcode' required='required'></div><div id='errormsg' style='color:red;'></div></form></div>";

        

        // function submitorderCard(paymentMethod,eWalletIgnore = "No",payment = '')
        var adminSkip = "No";
        function submitorderCard(paymentMethod,eWalletIgnore,payment)

        {

            var selectedPreferences = [];

            $.each($("input[name='selectedPreferences[]']:checked"), function(){

                selectedPreferences.push($(this).val());

            });

        

            if(selectedPreferences.length > 0)

            {

                selectedPreferences = selectedPreferences.join(",");

            }

            else {

                selectedPreferences = "";

            }

        

        eWalletIgnore = eWalletIgnore || 'No';

        payment = payment || "";
        
        <?php if($takeaway == 'Yes') { ?>
            var eTakeAway = $('input[name="eTakeAway"]:checked').val();
        <?php } else { ?>
            var eTakeAway = 'No';
        <?php } ?>

            if (eTakeAway=='No') {
                if (deliveryAddressCount <= 0 || deliveryAddressCount == "") {
                    alert('<?=$deliverAddresMsg;?>');
                    return false;
                }
            }

            if (siteType == "Demo") {

                alert('<?=$finalMsg;?>');

            }

            $('.submitplaceorder').addClass('disabled');

            $('.btncreditcard').addClass('disabled');

            $('.site-loader').addClass('active');

            $('.submitplaceorder').removeClass('disabled');

            $('.btncreditcard').removeClass('disabled');

            id = $('#id').val();

            Instruction = $('#Instruction').val();

            //payment = 'Card';

            if(payment=='') {

                payment =$('#payment').val();

            }

            ePayWallet = "No";

            var ePayWallet = 0;

            ePayWallet = $("#ePayWallet:checked").length;

            var CheckUserWallet = walletchecked = '';

            if (ePayWallet == 0) {

                CheckUserWallet = 'No';

                walletchecked = 'No';

                ePayWallet = 'No';

            } else {

                CheckUserWallet = 'Yes';

                walletchecked = 'Yes';

                ePayWallet = 'Yes';

            }

            var SYSTEM_PAYMENT_FLOW = "<?= $SYSTEM_PAYMENT_FLOW ?>";

            /*if(SYSTEM_PAYMENT_FLOW!='Method-1' && payment!='Cash') {

                CheckUserWallet = 'Yes';

                ePayWallet = 'Yes';

            }*/

            var fromOrder = '<?= $fromOrder; ?>';

            var orderdetails = '<?= json_encode($_SESSION["ORDER_DETAILS_" . strtoupper($fromOrder)]) ?>';

           // console.log(orderdetails+"======");

            //var couponcode = '<?php !empty($_SESSION[$orderCouponSession]) ? json_encode($_SESSION[$orderCouponSession]) : '' ?>';

            var couponcode = $("#appliedCouponCode").val();

            var data = {

                "tSessionId": '<?=$Datauser[0]['tSessionId']?>',

                //"vDeviceToken": '<?=$Datauser[0]['vDeviceToken']?>',

                "GeneralMemberId": '<?= $Datauser[0]['iUserId'] ?>',

                "GeneralUserType": 'Passenger',

                "type": 'CheckOutOrderDetails',

                "iUserId": '<?=$iUserId?>',

                "iUserAddressId": '<?=$iUserAddressId?>',

                "iCompanyId": '<?=$iCompanyId?>',

                "vCouponCode": couponcode,

                "ePaymentOption": payment,

                "OrderDetails": orderdetails,

                "vInstruction": $("#Instruction").val(),

                "iOrderId": '',

                "iServiceId":'<?=$iServiceId?>',

                "CheckUserWallet": CheckUserWallet,

                "eWalletIgnore": eWalletIgnore,

                "ePayWallet": ePayWallet,

                "vTimeZone": '<?=$vTimeZone?>',

                "fromOrder": fromOrder,

                "eTakeAway": eTakeAway,

                "selectedprefrences": selectedPreferences,

                "adminSkip": adminSkip,
            };

        

            data = $.param(data);

           $.ajax({

                type: "POST",

                url: "<?=$tconfig["tsite_url"] . ManualBookingAPIUrl;?>",

                data: data,

                dataType: 'json',

                success: function (dataHtml)

                {

                    //languagedata = <?php echo $json_lang; ?>;

                    message = dataHtml.message;

                    if(message == 'DO_PHONE_VERIFY'){

                        var dataSms = {

                            "tSessionId": '<?= $Datauser[0]['tSessionId'] ?>',

                            "GeneralMemberId": '<?= $Datauser[0]['iUserId'] ?>',

                            "GeneralUserType": 'Passenger',

                            "MobileNo":'<?= $register_user_data[0]['vPhoneCode']. $register_user_data[0]['vPhone']; ?>',

                            "type": 'sendVerificationSMS',

                            "iMemberId": '<?= $iUserId ?>',

                            "UserType": 'Passenger',

                            "REQ_TYPE": 'DO_PHONE_VERIFY',

                            "vTimeZone": '<?= $vTimeZone ?>',

                        };

                        dataSms = $.param(dataSms);

                        $.ajax({

                            type: "POST",

                            url: "<?= $tconfig["tsite_url"] . ManualBookingAPIUrl; ?>",

                            data: dataSms,

                            dataType: 'json',

                            async: false,

                            success: function (dataHtmlSMS)

                            {

                              /* dataHtmlSMS.Action = 1;

                               dataHtmlSMS.message = dataHtmlSMS.message1test;*/

                               var verificationmethod = dataHtmlSMS.MOBILE_NO_VERIFICATION_METHOD;
                               if ( typeof verificationmethod !== 'undefined' && verificationmethod == 'Firebase' && dataHtmlSMS.Action == "1") {
                                    userphoneNumber = '+'+"<?= $register_user_data[0]['vPhoneCode']. $register_user_data[0]['vPhone']; ?>";
                                    var ReCaptchaElement = '<div id="recaptcha-container-new" style="margin-bottom: 10px"></div><div id="captcha_error" style="color:#ff0000"></div>';
                                    var verifysms_continue = '<p style="margin-bottom: 10px">"We need to verify your phone number ('+userphoneNumber+').</p>'+ReCaptchaElement;
                                    //newDiv = $('<div id="recaptcha-container"></div>');
                                    <?php if ($maualStoreOrderUser == 'admin' || $maualStoreOrderUser == 'store') { ?>
                                        var skipbutton = languagedata['LBL_SKIP_SMALL'];
                                    <? } else { ?>
                                        var skipbutton = "";
                                    <? } ?>
                                    show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],verifysms_continue,languagedata['LBL_CONTINUE_BTN'],languagedata['LBL_CANCEL_TXT'],skipbutton,function (btn_id) {
                                            if(btn_id==0) {
                                                  //$("body").append(newDiv);
                                                var recaptchaResponse = grecaptcha.getResponse(window.recaptchaWidgetId);
                                                if(recaptchaResponse != "")
                                                {
                                                    $("#captcha_error").html("Captcha verification required.").hide();
                                                    submitPhoneNumberAuth(userphoneNumber);
                                                } else {
                                                    $("#captcha_error").html("Captcha verification required.").show();
                                                }
                                                $(".custom-modal-first-div").addClass("active");
                                                return false;
                                            } else if(btn_id==1){
                                                $(".custom-modal-first-div").removeClass("active");
                                                $(".pay-card").removeClass("tab-disable");
                                                return false;
                                            } else if(btn_id==2){

                                                adminSkip = "Yes";
                                                verifyphonenumberskip();
        
                                            } else {
                                                alert("Please Verify Phone Number.");$(".pay-card").removeClass("tab-disable");return false;
                                            }
                                        });
                                    initReCaptcha();
                                   return false;
                                }
                                if(dataHtmlSMS.Action == "1") {

                                    $('.site-loader').removeClass('active');

                                    verification_code = dataHtmlSMS.message;

                                       // show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],verifysmscontent);
                                      <?php if ($maualStoreOrderUser == 'admin'|| $maualStoreOrderUser == 'store') { ?>
                                        var skipbutton = languagedata['LBL_SKIP_SMALL'];
                                        <? } else { ?>
                                            var skipbutton = "";
                                        <? } ?>
                                       show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],verifysmscontent,languagedata['LBL_BTN_VERIFY_TXT'],languagedata['LBL_CANCEL_TXT'],skipbutton,function (btn_id) {

                                            if(btn_id==0) {

                                                verifyphonenumber();

                                            } else if(btn_id==1){

                                                $(".custom-modal-first-div").removeClass("active");

                                                $(".pay-card").removeClass("tab-disable");

                                                return false;

                                            } else if(btn_id==2){
                                                adminSkip = "Yes";
                                                verifyphonenumberskip();
                                            }  else {

                                                alert("Please Verify Phone Number.");$(".pay-card").removeClass("tab-disable");return false;

                                            }

                                        },false);

        

                                        return false;

                                } else {

                                    var strmsg = dataHtmlSMS.message; 

                                    if(strmsg.match("^LBL_")){

                                        var verificationmsg = languagedata[strmsg];

                                    } else {

                                        var verificationmsg = strmsg;

                                    }

                                    $('.site-loader').removeClass('active');

                                    <?php if ($maualStoreOrderUser == 'admin' || $maualStoreOrderUser == 'store') { ?>
                                        var skipbutton = languagedata['LBL_SKIP_SMALL'];
                                    <? } else { ?>
                                        var skipbutton = "";
                                    <? } ?>
                
                                	show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],verificationmsg,languagedata['LBL_BTN_OK_TXT'],"",skipbutton,function (btn_id) {
                                        if(btn_id==2){
                                           adminSkip = "Yes";
                                           verifyphonenumberskip();
                                        } else {
                                            $(".pay-card").removeClass("tab-disable");
                                        }
                                    });
                           
                                   /* show_alert(languagedata['LBL_SIGNUP_PHONE_VERI'],verificationmsg,languagedata['LBL_BTN_OK_TXT'],"","",function (btn_id) {

                                                $(".pay-card").removeClass("tab-disable");

                                        });*/

                                    $(".pay-card").removeClass("tab-disable");

                                    return false;

                                }

                            }

                        });

                    }

        

                    if(payment=='Cash') {

                            $('.site-loader').removeClass('active');

                            Action = dataHtml.Action;

                            message = dataHtml.message;

                            iOrderId = dataHtml.iOrderId;

                            if (Action == 1)

                            {

                                $('.pay-card').removeClass('tab-disable');

                                <?php if ($maualStoreOrderUser == 'admin') {?>

                                    window.location.href = "admin/allorders.php?type=processing";

                                <?php } else if ($maualStoreOrderUser == 'store') {?>

                                    window.location.href = "processing-orders";

                                <?php } else if ($maualStoreOrderUser == 'user') {?>

                                    window.location.href = "thanks.php?orderid=" + iOrderId;

                                <?php }?>

                            } else {

                                if (message != 'Restaurants is close.') {

                                    $('.paymentbutton').addClass('tab-disable');

                                }

                                $('#error_message').show();

                                if(typeof languagedata[message]!=='undefined') {

                                   $('#error_message').html('<h3 style="color:#98441ef5;">' + languagedata[message] + '</h3>');
                                return false;
                                } else if(message=='DO_PHONE_VERIFY') {
                                   $('#error_message').html('');

                                return false;

                                } else {

                                   $('#error_message').html('<h3 style="color:#98441ef5;">' + message + '</h3>');

                                    return false;

                                }

                                //$('#error_message').html('<h3 style="color:#98441ef5;">' + message + '</h3>');

                            }

                    } else {

                            

                            if(dataHtml.Action==0) {

                                var msgshown = dataHtml.message;

                                if(msgshown == 'LOW_WALLET_AMOUNT') {

                                     if(typeof dataHtml.low_balance_content_msg==='undefined' || dataHtml.low_balance_content_msg=='') {

                                        alert(languagedata['LBL_WALLET_LOW_AMOUNT_MSG_TXT']);

                                     } else {

                                        var okbtn = '';

                                        if(dataHtml.IS_RESTRICT_TO_WALLET_AMOUNT=='No') {

                                            okbtn = languagedata['LBL_BTN_OK_TXT'];

                                        }

                                        show_alert(languagedata['LBL_LOW_WALLET_BALANCE'], dataHtml.low_balance_content_msg, okbtn, languagedata['LBL_CANCEL_TXT'], languagedata['LBL_ADD_MONEY'],function (btn_id) {

                                        if(btn_id==0) {

                                            submitorderCard(paymentMethod,"Yes","Cash");

                                        } else if(btn_id==1) {

                                            $(".site-loader").removeClass("active");

                                            return false;

                                        } else {

                                            alert("Please add money from app"); $(".site-loader").removeClass("active"); return false;

                                        }

                                        });

                                        //$("#usermodel").html(str);

        

                                         /*if(confirm(dataHtml.low_balance_content_msg)) {

                                            submitorderCard(paymentMethod,'Yes','Cash');

                                         } else {

                                            alert("bbbb");

                                         }*/

                                     }

                                     $('.site-loader').removeClass('active');

                                    return false;

                                }

                                if(msgshown != 'DO_PHONE_VERIFY'){

                                    if(typeof languagedata[msgshown]!=='undefined') {

                                        alert(languagedata[msgshown]);

                                        $('.site-loader').removeClass('active');

                                        return false;

                                    } else {

                                        alert(msgshown);

                                        $('.site-loader').removeClass('active');

                                        return false;

                                    }

                                }

                                $('.site-loader').removeClass('active');

                            } else {

                                $.ajax({

                                    type: "POST",

                                    url: "ajax_checkout_card_order_details.php",

                                    data: {id: id, payment: payment, Instruction: Instruction, CheckUserWallet: CheckUserWallet, fromorder: fromOrder, paymentMethod:paymentMethod,iOrderId: dataHtml.iOrderId},

                                    dataType: "JSON",

                                    success: function (dataHtml)

                                    {

                                        $('.site-loader').removeClass('active');

                                        Action = dataHtml.Action;

                                        message = dataHtml.message;

                                        iOrderId = dataHtml.iOrderId;

                                        sessionOrder = dataHtml.sessionOrder ? dataHtml.sessionOrder : '';

                                        fltPaymentUrl = dataHtml.fltPaymentUrl ? dataHtml.fltPaymentUrl : '';
                                        
                                        var webViewUrl = dataHtml.webViewUrl ? dataHtml.webViewUrl : '';
                                       // console.log(webViewUrl);
                                        //return false;

                                        if (Action == 1)

                                        {

                                            if(paymentMethod == 'stripe'){

                                                var stripe = Stripe("<?php echo $generalConfigPaymentArr['STRIPE_PUBLISH_KEY']; ?>");

                                                stripe.redirectToCheckout({

                                                    sessionId: sessionOrder

                                                    }).then(function (result) {

                                                });

                                            }

                                            $('.pay-card').removeClass('tab-disable');

        

                                        }else if (Action == 2)

                                        {

                                            if(paymentMethod == 'flutterwave'){

                                                window.location = fltPaymentUrl;

                                            } else {
                                                window.location = webViewUrl;
                                            }

                                            $('.pay-card').removeClass('tab-disable');

        

                                        }else if (Action == 3)

                                        {

        

                                             iOrderId = dataHtml.iOrderId;

                                             window.location.href = "thanks.php?orderid=" + iOrderId;

                                             if(SYSTEM_PAYMENT_FLOW=='Method-1' && payment=='Card') {

                                              var data = {

                                                    "tSessionId": '<?=$Datauser[0]['tSessionId']?>',

                                                    "GeneralMemberId": '<?= $Datauser[0]['iUserId'] ?>',

                                                    "GeneralUserType": 'Passenger',

                                                    "type": 'CaptureCardPaymentOrder',

                                                    "iUserId": '<?=$iUserId?>',

                                                    "iUserAddressId": '<?=$iUserAddressId?>',

                                                    "iCompanyId": '<?=$iCompanyId?>',

                                                    "vCouponCode": couponcode,

                                                    "ePaymentOption": payment,

                                                    "OrderDetails": orderdetails,

                                                    "vInstruction": $("#Instruction").val(),

                                                    "iOrderId": iOrderId,

                                                    "iServiceId":'<?=$iServiceId?>',

                                                    "CheckUserWallet": walletchecked,

                                                    "eWalletIgnore": eWalletIgnore,

                                                    "ePayWallet": ePayWallet,

                                                    "vTimeZone": '<?=$vTimeZone?>',

                                                    "payStatus": 'succeeded',

                                                };

        

                                                data = $.param(data);

                                               $.ajax({

                                                    type: "POST",

                                                    url: "<?=$tconfig["tsite_url"] . ManualBookingAPIUrl;?>",

                                                    data: data,

                                                    dataType: 'json',

                                                    success: function (dataHtml)

                                                    {

        

                                                        if(dataHtml.Action==1) {

                                                            $('.pay-card').removeClass('tab-disable');

                                                            <?php if ($maualStoreOrderUser == 'admin') {?>

                                                                 window.location.href = "admin/allorders.php?type=processing";

                                                            <?php } else if ($maualStoreOrderUser == 'store') {?>

                                                                window.location.href = "processing-orders";

                                                            <?php } else if ($maualStoreOrderUser == 'user') {?>

                                                                 window.location.href = "thanks.php?orderid=" + iOrderId;

                                                            <?php }?>

                                                        }

                                                    }

                                               });

                                             }

                                        } else {

                                            if (message != 'Restaurants is close.') {

                                                $('.paymentbutton').addClass('tab-disable');

                                            }

                                            alert(message);

                                            location.reload();

                                            //$('#error_message').show();

                                            //$('#error_message').html('<h3 style="color:#98441ef5;">' + message + '</h3>');

                                        }

                                    }

                                });

                            }

                    }

                }

            });

        }

        

        var OrderDetails, qtyDetails;

        function loadingtime()

        {

            if ($('#ePayWallet').is(':checked')) {

                var CheckUserWallet = 'Yes';

            } else {

                var CheckUserWallet = 'No';

            }

            id = $('#id').val();

            var paymentOption = $("#payment").val();

            var instruction = $("#Instruction").val();

            if (instruction == undefined) {

                instruction = "";

            } else {
                
                if (instruction.trim() != "") {

                    $('#clearnotebutton').show();
                    
                }

            }

            var fromOrder = '<?=$fromOrder;?>';

            <?php if($takeaway == 'Yes') { ?>
                var eTakeAway = $('input[name="eTakeAway"]:checked').val();
            <?php } else { ?>
                var eTakeAway = 'No';
            <?php } ?>

            $.ajax({

                type: "POST",

                url: "ajax_checkout_cart_to_restaurant.php",

                data: {idss: id, ePaymentOption: paymentOption, CheckUserWallet: CheckUserWallet, fromorder: fromOrder, eTakeAway: eTakeAway},

                dataType: "JSON",

                success: function (dataHtml)

                {

                    var resultbutton = '';

                    var sess_iUserId = '<?=$_SESSION[$orderUserIdSession];?>';

                    if (restaurant_status == "closed" || sess_iUserId == '')

                    {

                        $('.pay-card').addClass('tab-disable');

                    } else {

                        $('.pay-card').removeClass('tab-disable');

                    }

                    var resultbutton = '';

                    var sess_iUserId = '<?=$_SESSION[$orderUserIdSession];?>';

                    resultbutton += '<div class="rest-detail"><div class="rest-dp" style="background-image:url(' + dataHtml.vImage + ')"></div><strong>' + dataHtml.vCompany + '</strong>' + dataHtml.vCaddress + '</div>';

                    $('#block').html(resultbutton);

                    $('#checkout-block').html('');

                    if (dataHtml.Ordercounters > 0)

                    {

                        $('#cart-data').show();

                        var counters = dataHtml.Ordercounters;

                        resultData = '<div class="cart-data" id="cart-data" style="display:block;">';

                        OrderDetails = dataHtml.OrderDetailsItemsArr;

                        for (var i = 0; i < counters; i++)

                        {

                            if (OrderDetails[i].typeitem == 'new')

                            {

                                qtyDetails = OrderDetails[i].iQty;

                                resultData += '<div class="cart-data-row" id="opencart-modal' + i + '">';

                                if (typeof OrderDetails[i].eFoodType != 'undefined') {

                                    if (OrderDetails[i].eFoodType == "Veg") {

                                        var eFoodType = '<img src="<?= $siteUrl; ?>assets/img/cart-veg.jpg" alt="">';

                                    } else if (OrderDetails[i].eFoodType == "NonVeg") {

                                        var eFoodType = '<img src="<?= $siteUrl; ?>assets/img/cart-nonveg.jpg" alt="">';

        

                                    }

                                } else {

                                    var eFoodType = '';

                                }

                                resultData += '<div class="open-modal"  id="open-modal' + i + '" >' + eFoodType + '<span class="cart-item-name"  onclick=showMenuTypes(' + OrderDetails[i].iMenuItemId + ',"' + OrderDetails[i].type + '",' + i + ')>' + OrderDetails[i].vItemType + '</span><span class="cart-item" id="cart-option-modal' + i + '"  onclick=showMenuTypes(' + OrderDetails[i].iMenuItemId + ',"' + OrderDetails[i].type + '",' + i + ') >' + OrderDetails[i].optionaddonname + '</span><span class="edit_ele" onClick=showMenuTypes(' + OrderDetails[i].iMenuItemId + ',"' + OrderDetails[i].type + '",' + i + ')><?= addslashes($languageArr['LBL_EDIT']) ?></span><span class="remove_ele" onClick="remove_item(' + i + ','+OrderDetails[i].iMenuItemId+')"><?= addslashes($languageArr['LBL_REMOVE_TEXT']) ?></span> </div>';

                                resultData += '<div class="count-block complex"  id="number_update' + i + '"><button class="plus"  id="numbercart_minus_update"  onClick="numbercart_minus_update(' + qtyDetails + ',' + i + ')"></button><input  id="cart_id_update"  value="' + i + '" type="hidden"><input id="numbercart_update' + i + '" class="numbercart_update"  name="numbercart_update" value="' + qtyDetails + '" type="text" readonly><button class="minus" id="numbercart_plus_update" onClick="numbercart_plus_update(' + qtyDetails + ',' + i + ')";></button></div>';

                                resultData += '<div id="show_price_update' + i + '" ><span class="cart-item-cost" >' + OrderDetails[i].fPrice + '</span></div>';

                                resultData += '</div>';

                            }

                        }

                        resultData += '</div>';

                        $('#checkout-block').append(resultData);

                        $('#checkout-block').append('<div class="add-notes" id="add-notes" ><input type="text" oninput="checkMessage();" name="Instruction" id="Instruction" value="' + instruction + '" placeholder="<?= addslashes($langage_lbl['LBL_MANUAL_STORE_ADD_NOTES']) ?>"><img style="display:none;" id="clearnotebutton" src="<?= $siteUrl; ?>assets/img/cancel.svg" alt="Remove" onClick="clearSearchBox();" class="close_ico" /></div>');

        

        <?php

            $applyButtonEnable = "Yes";

            $appliedCouponCode = "";

            if (isset($_SESSION[$orderCouponSession]) && $_SESSION[$orderCouponSession] != "") {

                $applyButtonEnable = "No";

                $appliedCouponCode = $_SESSION[$orderCouponSession];

            }

            ?>

                        var couponhtml = '<div class="apply-promo"><input type="text" name="couponCode" id="couponCode" <?php if ($applyButtonEnable == "No") { ?>disabled="disabled" class="promocodehalfinput"<?php } else { ?>class="promocodefullinput" <?php } ?>  placeholder="<?php

            if ($applyButtonEnable == "Yes") {

                echo $langage_lbl['LBL_PROMO_CODE_ENTER_TITLE'];

            } else {

                echo $langage_lbl['LBL_APPLIED_PROMO_CODE'];

            }

            ?>"><input type="text" disabled name="appliedCouponCode" id="appliedCouponCode" value="<?= $appliedCouponCode; ?>"><label <?php if ($applyButtonEnable == "Yes") { ?>style="display:none;"<?php } else { ?>style="display:block;"<?php } ?> id="couponcodename"></label><img id="remove-promo" <?php if ($applyButtonEnable == "Yes") { ?>style="display:none;"<?php } else { ?>style="display:block;"<?php } ?> src="<?= $siteUrl; ?>assets/img/cancel.svg" alt="Remove" class="close_ico_clo"><button id="apply-promo" <?php if ($applyButtonEnable == "Yes") { ?>style="display:block;"<?php } else { ?>style="display:none;"<?php } ?> class="gen-buttons"><?= addslashes($langage_lbl['LBL_APPLY']) ?></button></div><span id="apply-promo-error"></span>';

                        resultDataall = '';

                        resultDataall = couponhtml;

                        resultDataall += '<ul class="sub-total">';

                        resultDataall += '<li><span>' + dataHtml.fsubTotallabel + '</span> <span class="price-val"  id="subtotal">' + dataHtml.fSubTotal + '</span></li>';

                        if (dataHtml.totalDiscount > 0) {

                            resultDataall += '<li><span>' + dataHtml.fTotalDiscountlabel + '</span> <span class="price-val"  id="discount">-' + dataHtml.fTotalDiscount + '</span></li>';

                        }

                        if (dataHtml.PackingCharge > 0) {

                            resultDataall += '<li><span>' + dataHtml.fPackinlabel + '</span> <span class="price-val"  id="packing">' + dataHtml.fPackingCharge + '</span></li>';

                        }

                        if (dataHtml.DeliveryCharge > 0) {

                            resultDataall += '<li><span>' + dataHtml.fDeliverylabel + '</span> <span class="price-val"  id="deliveryCharge">' + dataHtml.fDeliveryCharge + '</span></li>';

                        }

                        if (dataHtml.tax > 0) {

                            resultDataall += '<li><span>' + dataHtml.fTaxlabel + '</span> <span class="price-val"  id="tax">' + dataHtml.fTax + '</span></li>';

                        }

                        if (dataHtml.OutStandingAmount > 0) {

                            resultDataall += '<li><span>' + dataHtml.fOutStandinglabel + '</span> <span class="price-val"  id="outstanding">' + dataHtml.fOutStandingAmount + '</span></li>';

                        }

                        if (dataHtml.Discount_Val > 0) {

                            resultDataall += '<li><span>' + dataHtml.fDiscount_Vallabel + '</span> <span class="price-val"  id="promodiscount">-' + dataHtml.fDiscount + '</span></li>';

                        }

                        resultDataall += '</ul>';

                        innerDiv.innerHTML = resultDataall;

                        document.getElementById('add-notes').appendChild(innerDiv);

        <?php if ($user_available_balance != 0 && strtoupper($SYSTEM_PAYMENT_FLOW)=='METHOD-1' && $maualStoreOrderUser!='store' && $maualStoreOrderUser!='admin') { ?>

                            var walletcomponet = "";

                            walletcomponet += '<div class="wallet-bal"><div><span class="check-holder"><input type="checkbox" id="ePayWallet" name="ePayWallet"><span class="check-box"></span></span><label><?= addslashes($langage_lbl['LBL_WALLET_ADJUSTMENT']); ?></label><span><?= addslashes($langage_lbl['LBL_WALLET_BALANCE']); ?> <?= $currencySymbol . " " . number_format($user_available_balance, 2); ?></span></div>';

                            var walletcomponetprice = '<span>' + dataHtml.fWalletDebit + '</span>';

                            var walletcomponetlast = '</div>';

                            if (dataHtml.user_wallet_debit_amount > 0) {

                                walletcomponet = walletcomponet + walletcomponetprice + walletcomponetlast;

                            } else {

                                walletcomponet = walletcomponet + walletcomponetlast;

                            }

                            $('#checkout-block').append(walletcomponet);

        

        <?php }?>

                        var totalmaxmsg = '';

                        totalmaxmsg += ' <div id="total-row" style="color:red;" class="hide msgmaxquty"><?=$msg?></strong></div>';

                        var minordermsg = '';

                        //Changes to remove coloum error for not showing checkout box bug

                        minordermsg += ' <div id="total-row" style="color:red;" class="show msgminimumtotal"><?= str_replace("'", "", $minordermsg) ?></strong></div>';

                        var totalamountcomponet = '';

                        totalamountcomponet += '<div class="topay-row"><strong>' + dataHtml.fTotalGeneratelabel + '</strong><strong>' + dataHtml.fTotalGenerateFare + '</strong></div>';

                        $('#checkout-block').append(totalmaxmsg);

                        $('#checkout-block').append(minordermsg);

                        $('#checkout-block').append(totalamountcomponet);

                        if (dataHtml.GenerateFare > 0) {

                            //if(CheckUserWallet=='Yes') $('.card-ico').addClass('tab-disable');

                            //else $('.card-ico').removeClass('tab-disable');

                            //$('.pay-tab-data').removeClass('active');

                            //$('#cash-data').addClass('active');

                            //$('.pay-ico').addClass('active');

                            //$('.card-ico').removeClass('active');

                        } else {

                            //$('.card-ico').addClass('tab-disable');

                            //if(CheckUserWallet=='Yes') $('.card-ico').addClass('tab-disable');

                            //else $('.card-ico').removeClass('tab-disable');

                            //$('.pay-tab-data').removeClass('active');

                            //$('#cash-data').addClass('active');

                            //$('.pay-ico').addClass('active');

                           // $('.card-ico').removeClass('active');

                        }

                        var iMaxItemQty = <?=$iMaxItemQty;?>;

                        var fMinOrderValue = <?=$fMinOrderValue;?>;

                        var sess_iUserId = '<?=$_SESSION[$orderUserIdSession];?>';

                        if (restaurant_status == "closed")

                        {

                            $('.paymentbutton').addClass('tab-disable');

                            $('.msgmaxquty,.msgminimumtotal').removeClass('show').addClass('hide');

                            $('.restaurantstatus-close').show();

                        } else if (dataHtml.TotaliQty > iMaxItemQty) {

                            $('.msgmaxquty').removeClass('hide').addClass('show');

                            $('.msgminimumtotal').removeClass('show').addClass('hide');

                            $('.paymentbutton').addClass('tab-disable');

                        } else if (dataHtml.fFinalTotal < fMinOrderValue) {

                            $('.msgminimumtotal').removeClass('hide').addClass('show');

                            $('.msgmaxquty').removeClass('show').addClass('hide');

                            $('.pay-card').addClass('tab-disable');

                        } else {

                            $('.msgmaxquty,.msgminimumtotal').removeClass('show').addClass('hide');

                            $('.paymentbutton').removeClass('tab-disable');

                        }

                        if (eWalletAmtApplied == "Yes") {

                            $('#ePayWallet').prop("checked", true);

                        } else {

                            $('#ePayWallet').prop("checked", false);

                        }

                        if (dataHtml.Discount_Val > 0) {

                            $("#couponCode,#appliedCouponCode").removeClass('promocodefullinput').addClass('promocodehalfinput');

                            $('#couponCode').prop('placeholder', "<?= addslashes($langage_lbl['LBL_APPLIED_PROMO_CODE']); ?>");

                            $('#apply-promo,#couponCode').hide();

                            $('#remove-promo,#appliedCouponCode').show();

                        } else {

                            $("#couponCode,#appliedCouponCode").removeClass('promocodehalfinput').addClass('promocodefullinput');

                            $('#couponCode').prop('placeholder', "<?= addslashes($langage_lbl['LBL_PROMO_CODE_ENTER_TITLE']); ?>");

                            $('#apply-promo,#couponCode').show();

                            $('#remove-promo,#appliedCouponCode').hide();

                        }

                    } else {

                        window.location.href = 'store-listing?order=' + fromOrder;

                    }

                    if (instruction != "") {

                        $('#clearnotebutton').show();

                    }

                }

            });

        }

        $(document).on('click', '#apply-promo,#remove-promo', function ()

        {

            var couponCode = $('#couponCode').val();

            var id = $('#id').val();

            var clickedId = this.id;

            var fromOrder = '<?=$fromOrder;?>';

            if (clickedId == "remove-promo") {

                if (!confirm("<?=$couponDelMsg;?>")) {

                    return false;

                }

            } else {

                if (couponCode.trim() == "") {

                    alert("<?=$validCouponMsg;?>");

                    return false;

                }

            }

            $.ajax({

                type: "POST",

                url: "ajax_check_promocode_cart_to_restaurant.php",

                data: {couponCode: couponCode, fromorder: fromOrder},

                dataType: "JSON",

                success: function (dataHtml)

                {

                    loadingtime();

                    if (dataHtml.Action == 0) {

                        setTimeout(function () {

                            document.getElementById("apply-promo-error").style.color = "red";

                            if (clickedId != "remove-promo") {

                                $('#apply-promo-error').html(dataHtml.message);

                            } else {

                                $("#couponCode,#appliedCouponCode").removeClass('promocodehalfinput').addClass('promocodefullinput');

                            }

                            $('#appliedCouponCode').val(couponCode);

                            $('#apply-promo,#couponCode').show();

                            $('#remove-promo,#appliedCouponCode').hide();

                            //$("#apply-promo").text('<?=$langage_lbl['LBL_APPLY'];?>');

                            $('#couponCode').prop('disabled', false);

                            $('#couponCode').prop('placeholder', "<?=$langage_lbl['LBL_PROMO_CODE_ENTER_TITLE']?>");

                        }, 1000);

                    } else {

                        setTimeout(function () {

                            $("#couponCode,#appliedCouponCode").removeClass('promocodefullinput').addClass('promocodehalfinput');

                            document.getElementById("apply-promo-error").style.color = "green";

                            $('#apply-promo-error').html(dataHtml.message);

                            $('#appliedCouponCode').val(couponCode);

                            $('#apply-promo,#couponCode').hide();

                            $('#remove-promo,#appliedCouponCode').show();

                            //$("#apply-promo").text('<?=$langage_lbl['LBL_REMOVE_TEXT'];?>');

                            $('#couponCode').prop('disabled', true);

                            $('#couponCode').prop('placeholder', "<?=$langage_lbl['LBL_APPLIED_PROMO_CODE']?>");

                        }, 1000);

                    }

                    setTimeout(function () {

                        $('#apply-promo-error').fadeOut();

                    }, 8000);

        

                }

            });

        });

        function remove_item(ids,menuItemId)

        {

            var cart_id_update = ids;

            //id = $('#id').val();

            

            var fromOrder = '<?=$fromOrder;?>';

            $.ajax({

                type: "POST",

                url: "remove_item_cart_to_restaurant.php",

                data: {removeid: cart_id_update, id: menuItemId, fromorder: fromOrder},

                dataType: "json",

                success: function (data)

                {

                    $("#opencart-modal" + cart_id_update).hide();

                    loadingtime();

                }

            });

        }

        function numbercart_minus_update(ids, cart_id)

        {

            var minusnumber = 1;

            var cart_id_update = cart_id;

            numbers = ids;

            var fromOrder = '<?=$fromOrder;?>';

            if (numbers != 1) {

                newnumber = parseInt(numbers) - minusnumber;

                id = $('#id').val();

                $.ajax({

                    type: "POST",

                    url: "update_qty_item_cart_restaurant.php",

                    data: {id: id, cart_id_update: cart_id_update, numbercart_update: newnumber, fromorder: fromOrder},

                    dataType: "json",

                    success: function (data)

                    {

                        $("#number_update" + cart_id).html('<button class="plus" onClick="numbercart_minus_update(' + newnumber + ',' + cart_id + ')"></button><input  id="cart_id_update"  value="' + cart_id + '" type="hidden"><input id="numbercart_update' + cart_id + '" class="numbercart_update"  name="numbercart_update" value="' + newnumber + '" type="text" readonly><button class="minus" onClick="numbercart_plus_update(' + newnumber + ',' + cart_id + ')"></button>');

                        loadingtime();

                    }

                });

            }

        }

        function numbercart_plus_update(ids, cart_id)

        {

            var plusnumber = 1;

            var cart_id_update = cart_id;

            numbers = ids;

            newnumber = parseInt(numbers) + plusnumber;

            id = $('#id').val();

            var fromOrder = '<?=$fromOrder;?>';

            $.ajax({

                type: "POST",

                url: "update_qty_item_cart_restaurant.php",

                data: {id: id, cart_id_update: cart_id_update, numbercart_update: newnumber, fromorder: fromOrder},

                dataType: "json",

                success: function (data)

                {

                    $("#number_update" + cart_id).html('<button class="plus" onClick="numbercart_minus_update(' + newnumber + ',' + cart_id + ')"></button><input  id="cart_id_update"  value="' + cart_id + '" type="hidden"><input id="numbercart_update' + cart_id + '" class="numbercart_update"  name="numbercart_update" value="' + newnumber + '" type="text" readonly><button class="minus" onClick="numbercart_plus_update(' + newnumber + ',' + cart_id + ')"></button>');

                    loadingtime();

                }

            });

        }

        $('#numbercart_minus').on('click', function ()

        {

            var minusnumber = 1;

            numbers = $('#numbercart').val();

            price = $('#price').val();

            currencySymbol = $('#currencySymbol').val();

            if (numbers != 1) {

                newnumber = parseInt(numbers) - minusnumber;

                $("#numbercart").val(newnumber);

                grandtotal(newnumber, price, currencySymbol);

            }

        });

        function otheraddon(id)

        {

            var numbers = $('#numbercart').val();

            var price = $('#price').val();

            var currencySymbol = $('#currencySymbol').val();

            grandtotal(numbers, price, currencySymbol);

        }

        var valsother;

        var valsothertotal = '';

        var radioValueotherother;

        function grandtotal(numbers, price, currencySymbol)

        {

            price = parseFloat(price.replace(/,/g, ''));

            valsother = '';

            radioValueotherother = '';

            var radioValueother = '';

            var arr = new Array();

            arr = optionData;

            var arraddon = new Array();

            arraddon = jsonaddon;

            var newnumber = parseInt(numbers);

            var subprice = parseFloat(price) * newnumber;

            var OptionPrice;

            var OptionPriceother;

            var sumaddon = 0;

            var sumTotaladdon = 0;

            var itemtoptions = 0;

            var sumother = 0;

            var sumTotalother = 0;

            var suboptionother = 0;

            var totalsumother = 0;

            var suboption = 0;

            radioValue = $("input[name='options']:checked").val();

            if (radioValue || undefined)

            {

                document.getElementById("optionserror").style.display = "none";

                document.getElementById("optionserror").innerHTML = "";

            }

            if (optionscountervalue > 0)

            {

                for (var i = 0; i < optionscountervalue; i++) {

                    var iOptionId = arr[i].iOptionId;

                    if (radioValue == iOptionId)

                    {

                        OptionPrice = arr[i].fPrice;

                        suboption += (parseFloat(OptionPrice));

                    }

                }

            }

            var vals = '';

            if (addoncountervalue > 0)

            {

                var checkboxes = document.getElementsByName('addon[]');

                for (var i = 0, n = checkboxes.length; i < n; i++)

                {

                    if (checkboxes[i].checked)

                    {

                        vals += "," + checkboxes[i].value;

                    }

                }

                if (vals) {

                    //vals = vals.substring(1); //Commented By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue

                    vals = vals.replace(/(^,)|(,$)/g, ""); //Added By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue

                }

                for (var i = 0; i < addoncountervalue; i++) {

                    var iOptionIdaddon = arraddon[i].iOptionId;

        

                    if (vals.indexOf(iOptionIdaddon) !== -1) {

                        OptionPrice = arraddon[i].fPrice;

                        sumaddon += parseFloat(OptionPrice);

        

                    }

                }

            }

            if (isPriceShow != '' && isPriceShow == 'separate') {

                var subtotals = parseFloat(totalsumother) + parseFloat(suboption) + parseFloat(sumaddon);

                if (subtotals == 0) {

                    subtotals = price;

                }

            } else {

                var subtotals = price + parseFloat(totalsumother) + parseFloat(suboption) + parseFloat(sumaddon);

            }

            var full = (parseFloat(subtotals) * parseInt(numbers));

            var fullprice = currencySymbol + " " + full.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            $("#subtotalchange").html(fullprice);

        

        }

        function options(id) {

            numbers = $('#numbercart').val();

            price = $('#price').val();

            currencySymbol = $('#currencySymbol').val();

            grandtotal(numbers, price, currencySymbol);

        }

        function addon(id) {

            numbers = $('#numbercart').val();

            price = $('#price').val();

            currencySymbol = $('#currencySymbol').val();

            grandtotal(numbers, price, currencySymbol);

        }

        $('#numbercart_plus').on('click', function () {

            var plusnumber = 1;

            numbers = $('#numbercart').val();

            price = $('#price').val();

            currencySymbol = $('#currencySymbol').val();

            var newnumber = (plusnumber + parseInt(numbers));

            $("#numbercart").val(newnumber);

            grandtotal(newnumber, price, currencySymbol);

        });

        $("#addtocart").on('click', function ()

        {

            var counters;

            counters = 0;

            var totalcounters = 0;

            var onscounters = 0, onscs = 1;

            var addcounters = 0;

            var othecounters = 0;

            /*  var radioValueotherother='';

             var valsother = ''; */

            var no = $('#no').val();

            var typeofitem = $('#typeofitem').val();

            var id = $('#id').val();

            if (optionscountervalue > 0)

            {

                var radioValue = '';

                var radioValues = '';

                radioValues = document.getElementsByName('options');

                for (var i = 0, length = radioValues.length; i < length; i++)

                {

                    if (radioValues[i].checked)

                    {

                        onscs = 0;

                    }

                }

                if (onscs == '1')

                {

                    document.getElementById("optionserror").style.display = "block";

                    document.getElementById("optionserror").innerHTML = "Please select atleast one";

                    onscounters = 1;

                } else {

                    onscounters = 0;

                }

                radioValue = $("input[name='options']:checked").val();

            }

            if (addoncountervalue > 0)

            {

                var vals = '';

                var checkboxes = document.getElementsByName('addon[]');

                for (var i = 0, n = checkboxes.length; i < n; i++)

                {

                    if (checkboxes[i].checked)

                    {

                        vals += "," + checkboxes[i].value;

                    }

                }

                if (vals) {

                    //vals = vals.substring(1); //Commented By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue

                    vals = vals.replace(/(^,)|(,$)/g, ""); //Added By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue

                    addcounters = 0;

                }

            }

            if (othercountervalue > 0)

            {

                for (var iii = 0; iii < othercountervalue; iii++)

                {

                    subresultvalues = subresult[iii].values;

                    subresultvaluesconter = subresult[iii].values.length;

                    if (subresultvaluesconter > 0)

                    {

                        OptionMinSelection = subresult[iii].OptionMinSelection;

                        OptionMaxSelection = subresult[iii].OptionMaxSelection;

                        eOptionInputType = subresult[iii].eOptionInputType;

                        for (var iv = 0; iv < subresultvaluesconter; iv++)

                        {

                            var dynamiccounter = 0;

                            if (OptionMinSelection > 0)

                            {

                                if (eOptionInputType == 'checkbox')

                                {

                                    var checkboxesother = document.getElementsByName('othercheckbox' + iii + '[]');

                                    for (var i = 0, n = checkboxesother.length; i < n; i++)

                                    {

                                        if (checkboxesother[i].checked)

                                        {

                                            valsother += "," + checkboxesother[i].value;

                                            dynamiccounter++;

                                        }

                                    }

                                    if (valsother) {

                                        //valsother = valsother.substring(1); //Commented By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue

                                        valsother = valsother.replace(/(^,)|(,$)/g, ""); //Added By HJ On 07-06-2019 For Solved When Edit Item at that tiem wrong Calculation issue

                                    }

                                    if (dynamiccounter < OptionMinSelection || dynamiccounter > OptionMaxSelection)

                                    {

                                        document.getElementById("othercheckerror" + iii).style.display = "block";

                                        document.getElementById("othercheckerror" + iii).innerHTML = "Please select minimum " + OptionMinSelection + " and maximum " + OptionMaxSelection;

                                        othecounters = 1;

                                    } else {

                                        othecounters = 0;

                                    }

                                }

                                if (eOptionInputType == 'radio')

                                {

                                    radioValueother = $("input[name='otherradio" + iii + "']:checked").val();

                                    radioValueotherother += "," + radioValueother;

                                }

                            }

                        }

                    }

                }

            }

            totalcounters = othecounters + addcounters + onscounters;

            if (totalcounters == 0) {

                if (restaurant_status == 'closed') {

                    $('#flex-row-error').show();

                    $('.product-model-overlay').removeClass('active');

                    return true;

                }

                var id = $('#id').val();

                var inst = $('#inst').val();

                var numberss = $('#numbercart').val();

                var fromOrder = '<?=$fromOrder;?>';

                ItemId = result.ItemId;

                MenuId = result.MenuId;

                eFoodType = result.eFoodType;

                $.ajax({

                    type: "POST",

                    url: "update_item_cart_restaurant.php",

                    data: {MenuItemId: ItemId, FoodMenuId: MenuId, no: no, id: id, numbers: numberss, addon: vals, option: radioValue, addonother: valsother, optionother: radioValueotherother, inst: inst, eFoodType: eFoodType, fromorder: fromOrder},

                    dataType: "json",

                    success: function (dataHtml)

                    {

                        loadingtime();

                        $('.product-model-overlay').removeClass('active');

                    }

                });

            }

        });

        var restaurant_status = '<?=($restaurantstatus);?>';

        if (restaurant_status == 'closed') {

            $('#flex-row-error').show();

            $('.product-model-overlay').removeClass('active');

        }

        setTimeout(function () {

        }, 5000);

        function chacknew() {

            validator.resetForm();

            $('#new_oneadd,#riderAddresses').hide();

            $('#save_oneadd,#cancel_oneadd,.DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();

            $('#areatext').val(0);

            $('#vServiceAddress,#vBuildingNo,#vLandmark,#vAddressType,#from_lat_frm,#from_long_frm,#from_lat_long_frm').val('');

            return true;

        }

        function canceladdress() {

            validator.resetForm();

            $('.close-icon').trigger('click')

            $('#areatext').val(0);

            $("#from_lat_frm,#from_long_frm,#from_lat_long_frm,#vServiceAddress,#vBuildingNo,#vLandmark,#vAddressType").val('');

            //location.reload();

            return false;

        }

        var iCompanyIds = $('#id').val();

        var validator = $('#deliveryaddressfrm').validate({

            ignore: "",

            errorClass: 'help-block error',

            errorElement: 'span',

            errorPlacement: function (error, e) {

                e.parents('.form-column-full').append(error);

            },

            highlight: function (e) {

                $(e).closest('.form-column-full').removeClass('has-success has-error').addClass('has-error');

                $(e).closest('.form-column-full strong input').addClass('has-shadow-error');

                $(e).closest('.help-block').remove();

            },

            success: function (e) {

                e.prev('input').removeClass('has-shadow-error');

                e.closest('.newrow').removeClass('has-success has-error');

                e.closest('.help-block').remove();

                e.closest('.help-inline').remove();

            },

            rules: {

                from_lat_long_frm: {required: true, remote: {

                        url: 'ajax_check_address_store.php',

                        type: "post",

                        dataType: 'json',

                        data: {from_lat: function () {

                                return $('#from_lat_frm').val()

                            }, from_long: function () {

                                return  $('#from_long_frm').val()

                            }, iUserAddressId: '', iCompanyIds: iCompanyIds},

                        dataFilter: function (response) {

                            response = jQuery.parseJSON(response);

                            if (response.Action == 0)

                            {

                                return false;

                            } else {

                                return true;

                            }

                        },

                    }},

                vBuildingNo: {required: true},

                vLandmark: {required: true},

            },

            messages: {

                from_lat_long_frm: {required: '<?= addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_DELIVERY_ADDRESS']); ?>', remote: '<?= addslashes($languageArr['LBL_LOCATION_FAR_AWAY_TXT']); ?>'},

                vBuildingNo: {required: '<?= addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_BUIDINGNO']); ?>'},

                vLandmark: {required: '<?= addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_LANDMARK']); ?>'},

            },

            submitHandler: function (form) {

                var DeliveryAddress = $('#vServiceAddress').val();

                var BuildingNo = $('#vBuildingNo').val();

                var Landmark = $('#vLandmark').val();

                var vAddressType = $('#vAddressType').val();

                var from_lat = $('#from_lat_frm').val();

                var from_long = $('#from_long_frm').val();

                var from_lat_long = $('#from_lat_long_frm').val();

                var sess_iUserId = '<?= $_SESSION[$orderUserIdSession]; ?>';

                var iCompanyIds = $('#id').val();

                var fromOrder = '<?= $fromOrder; ?>';

                $("#delivery-address-model").hide();

                $('.site-loader').addClass('active');

                $.ajax({

                    type: "POST",

                    url: "ajax_add_delivery_address.php",

                    data: {DeliveryAddress: DeliveryAddress, BuildingNo: BuildingNo, Landmark: Landmark, vAddressType: vAddressType, from_lat: from_lat, from_long: from_long, from_lat_long: from_lat_long, sess_iUserId: sess_iUserId, iCompanyIds: iCompanyIds, type: 'adddeliveryaddress', fromorder: fromOrder},

                    success: function (dataHtml)

                    {

                        $('.delivery-address').html(dataHtml);

                        if (dataHtml != "") {

                            canceladdress();

                        } else if (dataHtml == "") {

                            alert("<?= addslashes($languageArr['LBL_LOCATION_FAR_AWAY_TXT']); ?>");

                        }

                        location.reload();

                    }

                });

                $('.site-loader').removeClass('active');

            },

        });

        var cardvalidator = $('#frmcreditcard').validate({

            ignore: "",

            errorClass: 'help-block error',

            errorElement: 'span',

            errorPlacement: function (error, e) {

                e.parents('.form-column-full').append(error);

            },

            highlight: function (e) {

                $(e).closest('.form-column-full').removeClass('has-success has-error').addClass('has-error');

                $(e).closest('.form-column-full strong input').addClass('has-shadow-error');

                $(e).closest('.help-block').remove();

            },

            success: function (e) {

                e.prev('input').removeClass('has-shadow-error');

                e.closest('.newrow').removeClass('has-success has-error');

                e.closest('.help-block').remove();

                e.closest('.help-inline').remove();

            },

            rules: {

                //elecardnumber: {required: true, creditcard: true}, Removed Card Validation As Per Discuss With CD Sir On 12-07-2019

                elecardnumber: {required: true},

                elecardvalidity: {required: true, lettersonly: true, maxlength: 7},

                elecardcvv: {required: true, digits: true, maxlength: 4},

                elecardcardname: {required: true},

            },

            messages: {

                //elecardnumber: {required: '<?=addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_CREDIT_CARD_NUMBER'])?>', creditcard: '<?=addslashes($languageArr['LBL_MANUAL_STORE_INVALID_CREDIT_CARD_NUMBER'])?>'}, Removed Card Validation As Per Discuss With CD Sir On 12-07-2019

                elecardnumber: {required: '<?=addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_CREDIT_CARD_NUMBER'])?>'},

                elecardvalidity: {required: '<?=addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_CREDIT_CARD_VALIDITY'])?>'},

                elecardcvv: {required: '<?=addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_CREDIT_CARD_CVV_NUMBER'])?>'},

                elecardcardname: {required: '<?=addslashes($languageArr['LBL_MANUAL_STORE_REQUIRED_CREDIT_CARD_NAME'])?>'},

            },

            submitHandler: function (form) {

                submitorder();

                $('.close-icon').trigger('click');

                return false;

            },

        

        });

        jQuery.validator.addMethod("lettersonly", function (value, element) {

            return this.optional(element) || /^[0-9 \/]+$/i.test(value);

        }, "Invalid expiration date");

        function initialize() {

            var bounds = new google.maps.LatLngBounds();

            var thePoint = new google.maps.LatLng(from_lat, from_long);

            bounds.extend(thePoint);

            var mapOptions = {

                zoom: 4,

                center: thePoint

            };

            map = new google.maps.Map(document.getElementById('map-canvas'),

                    mapOptions);

            map.fitBounds(bounds);

            zoomChangeBoundsListener = google.maps.event.addListenerOnce(map, 'bounds_changed', function (event) {

                if (this.getZoom()) {

                    this.setZoom(12);

                }

            });

            if (eType == "Deliver") {

                show_type(eType);

            }

        }

        // function getAddress(mDlatitude, mDlongitude, addId) {

            // var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);

            // geocoder.geocode({'latLng': mylatlang},

                    // function (results, status) {

                        // console.log(results);

                        // if (status == google.maps.GeocoderStatus.OK) {

                            // if (results[0]) {

                                // $('#' + addId).val(results[0].formatted_address);

                            // } else {

                                // document.getElementById('#' + addId).value = "No results";

                            // }

                        // } else {

                            // document.getElementById('#' + addId).value = status;

                        // }

                    // });

        // }

        

        // $(function () {

        

        //     var from = document.getElementById('vServiceAddress');

        //     autocomplete_from = new google.maps.places.Autocomplete(from);

        //     google.maps.event.addListener(autocomplete_from, 'place_changed', function () {

        //         var place = autocomplete_from.getPlace();

        //         $("#from_lat_long_frm").val(place.geometry.location);

        //         $("#from_lat_frm").val(place.geometry.location.lat());

        //         $("#from_long_frm").val(place.geometry.location.lng());

        //     });

        //     setTimeout(function () {

        //         $('.pac-container').appendTo(".vlocation");

        //     }, 3000);

        // });

        $(document).ready(function () {

            $('#vServiceAddress').keyup(function (e) {

                buildAutoComplete("vServiceAddress",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>"); // (orignal function)

                });

            });

        

        function removeAddress(addressId) {

            var sess_iUserId = '<?=$_SESSION[$orderUserIdSession];?>';

            var iCompanyIds = $('#id').val();

            var fromOrder = '<?=$fromOrder;?>';

            if (confirm("<?=$deleteAddressMsg;?>")) {

                $.ajax({

                    type: "POST",

                    url: "ajax_add_delivery_address.php",

                    data: {addressId: addressId, sess_iUserId: sess_iUserId, iCompanyIds: iCompanyIds, type: 'removeAddress', fromorder: fromOrder},

                    success: function (dataHtml)

                    {

                        $('.delivery-address').html(dataHtml);

                        console.log(addressId + "===" + deliveryAddressCount)

                        if (deliveryAddressCount == addressId) {

                            deliveryAddressCount = "";

                        }

                        canceladdress();

                    }

                });

            }

        }

        function changelocation(addressids, lat, log) {

            var fromOrder = '<?=$fromOrder;?>';

            $.ajax({

                type: "POST",

                url: "ajax_add_delivery_address.php",

                data: {addressids: addressids, type: 'changeaddress', fromorder: fromOrder},

                success: function (dataHtml)

                {

                    $('.address-block').removeClass('hide').removeClass('show').addClass('hide');

                    $('#address-id-' + addressids).addClass('show');

                    //$('.address-wrap').addClass('selected-address');

                    $('.deliveraddressIcon').show();

                    $('.delivery-address-passed').removeClass('active').addClass('passed');

                    deliveryAddressCount = addressids;

                    loadingtime();

                }

            });

        }

        function change_payment_type($type) {

            if ($type == 'Cash') {

                $('.select-card-type').hide();

            } else if ($type == 'Card') {

                $('.select-card-type').show();

            }

        }

    </script>

    <script>Xendit.setPublishableKey('<?=$xenditPublishKey;?>');</script>

    <script type="text/javascript">

        function minmax(value, min, max)

        {

            if (parseInt(value) < min || isNaN(parseInt(value)))

                return min;

            else if (parseInt(value) > max)

                return max;

            else

                return value;

        }

        function isNumber(evt) {

            evt = (evt) ? evt : window.event;

            var charCode = (evt.which) ? evt.which : evt.keyCode;

            if (charCode > 31 && (charCode < 48 || charCode > 57)) {

                return false;

            }

            return true;

        }

        $('#card-cvc').bind("cut copy paste", function (e) {

            e.preventDefault();

        });

        $(document).ready(function () {

            $('ul.vaertical-tabs li').click(function () {

                $('ul.vaertical-tabs li').removeClass('active');

                $(this).addClass('active');

                var DATAPAYMENT = $(this).attr('data-payment');

                $('.pay-tab-data').removeClass('active');

                $(document).find('#' + DATAPAYMENT + '').addClass('active');

            })

            $(document).on('click', '.open-model', function (e) {

                var DATAID = $(this).attr('data-id');

                $('.product-model-overlay').removeClass('active');

                $('#' + DATAID).addClass('active');

            });

            $(document).on('click', '.close-icon', function (e) {

                $(this).closest('.product-model-overlay').removeClass('active');

                return false;

            });

            loadingtime();

        });

        // function chkValid(login_type, iscompany = '')

        function chkValid(login_type, iscompany)

        {

        iscompany = iscompany || '';

            var id = document.getElementById("vEmail").value;

            var pass = document.getElementById("vPassword").value;

            var fromOrder = '<?=$fromOrder;?>';

            if (id == '' || pass == '')

            {

                document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_EMAIL_PASS_ERROR_MSG']);?>';

                document.getElementById("errmsg").style.display = '';

                return false;

            } else

            {

                var request = $.ajax({

                    type: "POST",

                    url: 'ajax_login_action.php',

                    data: $("#login_box").serialize(),

                    success: function (data)

                    {

                        jsonParseData = JSON.parse(data);

                        login_status = jsonParseData.login_status;

                        eSystem = jsonParseData.eSystem;

                        if (login_status == 1) {

                            document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_ACC_DELETE_TXT']);?>';

                            document.getElementById("errmsg").style.display = '';

                            return false;

                        } else if (login_status == 2) {

                            document.getElementById("errmsg").style.display = 'none';

                            window.location.reload();

                        } else if (login_status == 3) {

                            document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']);?>';

                            document.getElementById("errmsg").style.display = '';

                            return false;

                        } else if (login_status == 4) {

                            document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG']);?>';

                            document.getElementById("errmsg").style.display = '';

                            return false;

                        } else {

                            document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_INVALID_EMAIL_MOBILE_PASS_ERROR_MSG']);?>';

                            document.getElementById("errmsg").style.display = '';

                            return false;

                        }

                    }

                });

                request.fail(function (jqXHR, textStatus) {

                    alert("Request failed: " + textStatus);

                    return false;

                });

                return false;

        }

        }

        function change_heading(type)

        {

            $('.error-login-v').hide();

            if (type == 'forgot') {

        

                $('#frmforget').show();

                $('#login_box').hide();

                $('#label-id').text("<?=addslashes($langage_lbl['LBL_FORGOR_PASSWORD']);?>");

            } else {

                $('#frmforget').hide();

                $('#login_box').show();

                $('#label-id').text("<?=addslashes($langage_lbl['LBL_SIGN_IN_TXT']);?>");

            }

        }

        function forgotPass()

        {

            $('.error-login-v').hide();

            var id = document.getElementById("femail").value;

            var fromOrder = '<?=$fromOrder;?>';

            if (id == '')

            {

                document.getElementById("errmsg").style.display = '';

                document.getElementById("errmsg").innerHTML = '<?=addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR_TXT_IPHONE']);?>';

            } else {

                var request = $.ajax({

                    type: "POST",

                    url: 'ajax_fpass_action.php',

                    data: $("#frmforget").serialize(),

                    dataType: 'json',

                    beforeSend: function ()

                    {

                        //alert(id);

                    },

                    success: function (data)

                    {

                        if (data.status == 1)

                        {

                            change_heading('login');

                            document.getElementById("success").innerHTML = data.msg;

                            document.getElementById("success").style.display = '';

                        } else

                        {

                            document.getElementById("errmsg").innerHTML = data.msg;

                            document.getElementById("errmsg").style.display = '';

                        }

                    }

                });

                request.fail(function (jqXHR, textStatus) {

                    alert("Request failed: " + textStatus);

                });

            }

            return false;

        }

        $('.change-address').off('click').on('click', function () {

            $('.address-block').removeClass('hide').addClass('show');

            $('.address-wrap').removeClass('selected-address');

            if ($(".chkaddresssIds").length > 0) {

                $(".chkaddresssIds").prop("checked", false);

            }

            $('.deliveraddressIcon').hide();

            $('.delivery-address-passed').removeClass('passed').addClass('active');

        });

        $(document).on('click', '.submitplaceorder', function () {
            <?php if(isEnableTermsServiceCategories() && $eShowTerms == "Yes") { ?>
                if(getCookie('AGE_RESTRICTION_<?= $iServiceId ?>') != "")
                {
                    return true;
                }
                else{
                    $('#age_restriction').prop('checked', false);
                    $('#restriction_modal').modal({backdrop: 'static',keyboard: false},'show');
                    return false;    
                }
            <?php } ?>

            $('.pay-card').removeClass('active').addClass('passed');

            $('.PaymentIcon').show();

            $('#payment').val('Cash');

            var iCompanyIds = $('#id').val();

            $.ajax({

                url: 'ajax_check_address_store.php',

                type: "post",

                dataType: 'json',

                data: { iUserAddressId: '<?= $iUserAddressId ?>', iCompanyIds: iCompanyIds},

                success: function (response) {

                    response = JSON.parse(JSON.stringify(response));



                    if (response.Action == 0)

                    {

                        return false;

                    } else {

                        submitorderCard('','','Cash');

                        return true;



                    }

                }

            });

            // submitorderCard('','','Cash');

            //submitorder();

            return false;

        });

        

        $(document).on('click', '.submitplaceorderCard', function () {

            <?php if(isEnableTermsServiceCategories() && $eShowTerms == "Yes") { ?>
                if(getCookie('AGE_RESTRICTION_<?= $iServiceId ?>') != "")
                {
                    return true;
                }
                else{
                    $('#age_restriction').prop('checked', false);
                    $('#restriction_modal').modal({backdrop: 'static',keyboard: false},'show');
                    return false;    
                }
            <?php } ?>

            var paymentMethod = $(this).attr('data-method');

        

            $('.pay-card').removeClass('active').addClass('passed');

            $('.PaymentIcon').show();

            $('#payment').val('Card');

            submitorderCard(paymentMethod);

            return false;

        });

        

        $(document).on('click', '#ePayWallet', function ()

        {

            if ($(this).is(':checked')) {

                var CheckUserWallet = 'Yes';

                $('#ePayWallet').prop("checked", true);

                eWalletAmtApplied = CheckUserWallet;

            } else {

                var CheckUserWallet = 'No';

                $('#ePayWallet').prop("checked", false);

                eWalletAmtApplied = CheckUserWallet;

            }

            loadingtime();

        });

        function opencardMode() {

            $('#elecardvalidity,#elecardcvv,#elecardcardname,#elecardnumber').val('');

            $("#payment").val("Card");

            cardvalidator.resetForm();

            $('#payment').val('Card');

            return false;

        }

        function resetServiceCatagory() {

            var e = document.getElementById("servicename");

            var serviceId = e.options[e.selectedIndex].value;

            var serviceName = e.options[e.selectedIndex].text;

            var cartAmount = $("#subtotalamount").text();

            var cartTotItems = "<?=$confirlAlert;?>";

            var userType = '<?=$fromOrder;?>';

            if (cartTotItems > 0 || cartAmount.trim() != "") {

                if (confirm("<?=$confirmLabel;?>")) {

                    window.location.href = 'store-listing?sid=' + serviceId + '&order=' + userType;

                }

            } else {

                window.location.href = 'store-listing?sid=' + serviceId + '&order=' + userType;

            }

        }

        function clearSearchBox() {

            $("#Instruction").val("");

            $('#clearnotebutton').hide();

        }

        function checkMessage() {

            var noteTxt = $("#Instruction").val();

            $('#clearnotebutton').hide();

            if (noteTxt.trim() != "") {

                $('#clearnotebutton').show();

            }

        }

        function verifyphonenumber(){

            var verificationcodeinputval = $("#verificationcode").val();

            if(verificationcodeinputval != '' && verification_code == verificationcodeinputval){

                 $("#errormsg").hide();

                var dataSmsverify = {

                    "tSessionId": '<?= $Datauser[0]['tSessionId'] ?>',

                    "GeneralMemberId": '<?= $Datauser[0]['iUserId'] ?>',

                    "GeneralUserType": 'Passenger',

                    "MobileNo":'<?= $register_user_data[0]['vPhoneCode']. $register_user_data[0]['vPhone']; ?>',

                    "type": 'sendVerificationSMS',

                    "iMemberId": '<?= $iUserId ?>',

                    "UserType": 'Passenger',

                    "REQ_TYPE": 'PHONE_VERIFIED',

                    "vTimeZone": '<?= $vTimeZone ?>',

                };

                dataSmsverify = $.param(dataSmsverify);

                $.ajax({

                        type: "POST",

                        url: "<?= $tconfig["tsite_url"] . ManualBookingAPIUrl; ?>",

                        data: dataSmsverify,

                        dataType: 'json',

                        success: function (dataHtml)

                        {   

                            show_alert(languagedata["LBL_SIGNUP_PHONE_VERI"],languagedata[dataHtml.message],languagedata["LBL_BTN_OK_TXT"],"","",function (btn_id) {

                                if(btn_id == 0){

                                    $(".pay-card").removeClass("tab-disable");

                                    $("#flex-row-error").html(" ");

                                    var submitpaymentmethod = $("#payment").val();

                                    if(submitpaymentmethod == "Cash"){

                                        $(".pay-card").removeClass("active").addClass("passed");

                                        $(".PaymentIcon").show();

                                        $("#payment").val("Cash");

                                        submitorderCard("","","Cash");

                                        return false;

                                    } else {

                                        var paymentMethod = $(".submitplaceorderCard").attr("data-method");

                                        $(".pay-card").removeClass("active").addClass("passed");

                                        $(".PaymentIcon").show();

                                        $("#payment").val("Card");

                                        submitorderCard(paymentMethod);

                                        return false;

                                    }

                                }

                            });

                            return false;

                        }

                    });

            } else {

                if(verificationcodeinputval != ""){

                    $("#errormsg").html(languagedata['LBL_INVALID_VERIFICATION_CODE_ERROR']).show(); 

                    $(".pay-card").removeClass("tab-disable");

                    return false;

                } else {

                    $("#errormsg").html(languagedata['LBL_ENTER_VERIFICATION_CODE']).show();  

                    $(".pay-card").removeClass("tab-disable");

                    return false;

                }

            }

            return false;

        }

        function verifyphonenumberskip(){
             $(".pay-card").removeClass("tab-disable");

            $("#flex-row-error").html(" ");

            var submitpaymentmethod = $("#payment").val();

            if(submitpaymentmethod == "Cash"){

                $(".pay-card").removeClass("active").addClass("passed");

                $(".PaymentIcon").show();

                $("#payment").val("Cash");

                submitorderCard("","","Cash");

                return false;

            } else {

                var paymentMethod = $(".submitplaceorderCard").attr("data-method");

                $(".pay-card").removeClass("active").addClass("passed");

                $(".PaymentIcon").show();

                $("#payment").val("Card");

                submitorderCard(paymentMethod);

                return false;

            }
}

        $(document).on("click",".icon-close",function() {

           $('.pay-card').removeClass('tab-disable');

           return false;

        });

        

        setTimeout(fade_out, 1000);

        

        function fade_out() {

          $("#errormsg").fadeOut().empty();

        }

        

        //called when key is pressed in textbox

        $(document).on('keydown keyup input',  function (e) {

          $("#verificationcode").keypress(function (e) {

             //if the letter is not digit then display error and don't type anything

             if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

                //$("#errormsg").html("Digits Only allowed").show();

                return false;

            }

           });

          $("#errormsg").hide();

        });



         $(document).on('click','[data-dismiss="modal"]',function(e){

            e.preventDefault();

            $(this).closest('.custom-modal-main').removeClass('active');

            $('body').css('overflow', 'auto');

        });



        $(document).on('keydown', 'body', function(e){

            if (e.which==27){

               $('.custom-modal-main').removeClass('active');

               $('.modal-backdrop').remove();

               $('body').css('overflow', 'auto');

            }

        });



        $(document).on('click','[data-toggle="modal"]',function(e){

            e.preventDefault();

            var data_target = $(this).attr('data-target');

            $('.custom-modal-main').removeClass('active');

            $(document).find(data_target).addClass('active');

            $('body').css('overflow', 'hidden');

        });


        <?php if($APP_PAYMENT_MODE == "Cash-Card" && $maualStoreOrderUser == 'user') { ?>
        
        $('input[name="selectedPreferences[]"').click(function() {
            var eContactLessChecked = 0;
            $.each($("input[name='selectedPreferences[]']:checked"), function(){

                if($(this).data('value') == 'Yes')
                {
                    eContactLessChecked = 1;
                }

            });

            if(eContactLessChecked == 1 || $('input[name="eTakeAway"]:checked').val() == 'Yes')

            {


                $('[data-payment="cash-data"], #cash-data').removeClass('active');

                $('[data-payment="card-data"], #card-data').addClass('active');
                $('[data-payment="cash-data"]').hide();

            }

            else{

                //$('[data-payment="cash-data"], #cash-data').show();

                $('[data-payment="cash-data"], #cash-data').addClass('active');

                $('[data-payment="card-data"], #card-data').removeClass('active');
                $('[data-payment="cash-data"]').show();

            }

        });
        
        $('input[name="eTakeAway"]').click(function() {
            if($(this).val() == "Yes")
            {
                $('.delivery-pref-Provider, .delivery-address-passed').hide();
                $("[name='selectedPreferences[]']").prop('checked', false);

                $('[data-payment="cash-data"], #cash-data').removeClass('active');

                $('[data-payment="card-data"], #card-data').addClass('active');
                
                $('[data-payment="cash-data"]').hide();
            }
            else{
                $('.delivery-pref-Provider, .delivery-address-passed').show();

                $('[data-payment="cash-data"], #cash-data').addClass('active');

                $('[data-payment="card-data"], #card-data').removeClass('active');

                $('[data-payment="cash-data"]').show();
            }

            var delivery_pref_show = 0;
            $.each($(".delivery-pref"), function(){
                if($(this).css('display') != 'none')
                {
                    delivery_pref_show = 1;
                }
            });

            if(delivery_pref_show == 0)
            {
                $('.delivery-pref-block').hide();
            }
            else{
                $('.delivery-pref-block').show();
            }

            loadingtime();
        });
        <?php } elseif (($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Card") && $maualStoreOrderUser == 'user') { ?>
            $('input[name="eTakeAway"]').click(function() {
                if($(this).val() == "Yes")
                {
                    $('.delivery-pref-Provider, .delivery-address-passed').hide();
                    $("[name='selectedPreferences[]']").prop('checked', false);
                }
                else{
                    $('.delivery-pref-Provider, .delivery-address-passed').show();
                }

                var delivery_pref_show = 0;
                $.each($(".delivery-pref"), function(){
                    if($(this).css('display') != 'none')
                    {
                        delivery_pref_show = 1;
                    }
                });

                if(delivery_pref_show == 0)
                {
                    $('.delivery-pref-block').hide();
                }
                else{
                    $('.delivery-pref-block').show();
                }

                loadingtime();
            });
        <?php } else { ?>
            $('input[name="eTakeAway"]').click(function() {
                if($(this).val() == "Yes")
                {
                    $('.delivery-pref-Provider').hide();
                    $("[name='selectedPreferences[]']").prop('checked', false);
                }
                else{
                    $('.delivery-pref-Provider').show();
                }

                var delivery_pref_show = 0;
                $.each($(".delivery-pref"), function(){
                    if($(this).css('display') != 'none')
                    {
                        delivery_pref_show = 1;
                    }
                });

                if(delivery_pref_show == 0)
                {
                    $('.delivery-pref-block').hide();
                }
                else{
                    $('.delivery-pref-block').show();
                }

                loadingtime();
            });
        <?php } ?>
    </script>

</html>

