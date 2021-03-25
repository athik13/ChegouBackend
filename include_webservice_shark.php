<?php
/* ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL); */

date_default_timezone_set('Asia/Kuala_Lumpur');
ini_set('default_socket_timeout', 10);
ini_set('memory_limit', '-1');
@session_start();
$_SESSION['sess_hosttype'] = 'ufxall';
$inwebservice = "1";
//error_reporting(0);
//include_once('include_taxi_webservices.php');
$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

include_once ('include_config.php');

include_once (TPATH_CLASS . 'configuration.php');

$generalConfigPaymentArr = $generalobj->getGeneralVarAll_Payment_Array();
if(empty($isUfxAvailable)){
    $isUfxAvailable = $generalobj->CheckUfxServiceAvailable(); // Added By HJ On 04-06-2020 For Optimized Query
}
require_once ('assets/libraries/stripe/config.php');
require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
require_once ('assets/libraries/pubnub/autoloader.php');
require_once ('assets/libraries/SocketCluster/autoload.php');
require_once ('assets/libraries/class.ExifCleaning.php');
include_once (TPATH_CLASS . 'Imagecrop.class.php');
include_once (TPATH_CLASS . 'twilio/Services/Twilio.php');
include_once ('include_generalFunctions_shark.php');
include_once ('send_invoice_receipt.php');
include_once ('send_invoice_receipt_multi.php');
if (!empty($_REQUEST['USE_APP_COMMON_KS'])) {
    include_once ('app_common_functions_ks.php');
}
else {
    include_once ('app_common_functions.php');
}
$intervalmins = INTERVAL_SECONDS; // Added By HJ On 13-03-2020 Which is Defined In configuration_variables.php
$PHOTO_UPLOAD_SERVICE_ENABLE = "Yes";
$host_arr = array();
$host_arr = explode(".", $_SERVER["HTTP_HOST"]);
$host_system = $host_arr[0];
$parent_ufx_catid = "0";
if (isset($_REQUEST['UBERX_PARENT_CAT_ID']) && $_REQUEST['UBERX_PARENT_CAT_ID'] != "") {
    $parent_ufx_catid = $_REQUEST['UBERX_PARENT_CAT_ID'];
}
else {
    $parent_ufx_catid = "0";
}
if ($host_system == "beautician") {
    $PHOTO_UPLOAD_SERVICE_ENABLE = "Yes";
}
if ($host_system == "tutors") {
    $PHOTO_UPLOAD_SERVICE_ENABLE = "No";
}
$uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";
if ($APP_PAYMENT_METHOD == "Braintree") {
    require_once ('assets/libraries/braintree/lib/Braintree.php');
    $gateway = new Braintree_Gateway(['environment' => $BRAINTREE_ENVIRONMENT, 'merchantId' => $BRAINTREE_MERCHANT_ID, 'publicKey' => $BRAINTREE_PUBLIC_KEY, 'privateKey' => $BRAINTREE_PRIVATE_KEY]);
}
if (isset($_REQUEST['APP_TYPE']) && $_REQUEST['APP_TYPE'] != "") {
    $APP_TYPE = $_REQUEST['APP_TYPE'];
}
//$APP_TYPE = "Ride-Delivery-UberX";
/* creating objects */
$thumb = new thumbnail;
/* Get variables */
/* Paypal supported Currency Codes */
$currency_supported_paypal = array(
    'AUD',
    'BRL',
    'CAD',
    'CZK',
    'DKK',
    'EUR',
    'HKD',
    'HUF',
    'ILS',
    'JPY',
    'MYR',
    'MXN',
    'TWD',
    'NZD',
    'NOK',
    'PHP',
    'PLN',
    'GBP',
    'RUB',
    'SGD',
    'SEK',
    'CHF',
    'THB',
    'TRY',
    'USD'
);
$demo_site_msg = "Edit / Delete Record Feature has been disabled on the Demo Application. This feature will be enabled on the main script we will provide you.";
if ($type == '') {
    $type = isset($_REQUEST['function']) ? trim($_REQUEST['function']) : '';
}
$lang_label = array();
$lang_code = '';
$GeneralDeviceType = isset($_REQUEST['GeneralDeviceType']) ? trim($_REQUEST['GeneralDeviceType']) : '';
if ($_SERVER["HTTP_HOST"] == "192.168.1.131" || $_SERVER["HTTP_HOST"] == "www.mobileappsdemo.com" || $_SERVER["HTTP_HOST"] == "192.168.1.141") {
    if ($APPSTORE_MODE_IOS == "Review" /* && $GeneralDeviceType == "Ios" */) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "Configuration name 'APPSTORE_MODE_IOS' must be set to Development mode for 131 and MobileProjectsDemo";
        setDataResponse($returnArr);
    }
}
/* general fucntions */
if ($type != "generalConfigData" && $type != "signIn" && $type != "isUserExist" && $type != "signup" && $type != "LoginWithFB" && $type != "sendVerificationSMS" && $type != "countryList" && $type != "changelanguagelabel" && $type != "requestResetPassword" && $type != "UpdateLanguageLabelsValue" && $type != "staticPage" && $type != "sendContactQuery" && $type != "generateReviewModeLogin" && $type != "signup_kiosk_passanger" && $type != "getAdvertisementBanners" && $type != "insertBannereImpressionCount" && $type != "getNewsNotification" && $type != "getNearestFlyStations_booking" && $type != "getNearestFlyStationsSectionBooking" && $type != "getNearestFlyStations" && $type != "getFAQ" && $type != "getUserLanguagesAsPerServiceType" && $type != "uploadcompanydocument" && $type != "getAdvertisementBanners" && $type != "insertBannereImpressionCount" && $type != "getNewsNotification" && $type != "CheckPrescriptionRequired" && $type != "getPrescriptionImages" && isAllowFetchAPIDetails() == false) {
    $tSessionId = isset($_REQUEST['tSessionId']) ? trim($_REQUEST['tSessionId']) : '';
    $GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? trim($_REQUEST['GeneralMemberId']) : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    //print_r($_REQUEST);die;
    if ($tSessionId == "" || $GeneralMemberId == "" || $GeneralUserType == "") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        setDataResponse($returnArr);
    }
    else {
        if (strtolower($GeneralUserType) == "hotel" || strtolower($GeneralUserType) == "kiosk") {
            $tableName = "hotel";
            $userData = get_value($tableName, "iHotelId as iMemberId,tSessionId", "iHotelId", $GeneralMemberId);
            $userDetailsArr[$tableName."_".$GeneralMemberId] = $userData;
        }
        else {
            $tableName = "register_user";
            $tblField = "iUserId";
            if(strtoupper($GeneralUserType) == "DRIVER"){
                $tableName = "register_driver";
                $tblField = "iDriverId";
            }
            $userData = get_value($tableName, $GeneralUserType == "Driver" ? "*,iDriverId as iMemberId" : "*,iUserId as iMemberId", $tblField, $GeneralMemberId);
            $userDetailsArr[$tableName."_".$GeneralMemberId] = $userData;
        }
        //echo $userData[0]['tSessionId']."===".$tSessionId;die;
        if ($userData[0]['iMemberId'] != $GeneralMemberId || $userData[0]['tSessionId'] != $tSessionId) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "SESSION_OUT";
            setDataResponse($returnArr);
        }
    }
}
/* To Check App Version */
$appVersion = isset($_REQUEST['AppVersion']) ? trim($_REQUEST['AppVersion']) : '';
$Platform = isset($_REQUEST['Platform']) ? trim($_REQUEST['Platform']) : 'Android';
$vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
$vUserDeviceCountry = isset($_REQUEST["vUserDeviceCountry"]) ? $_REQUEST["vUserDeviceCountry"] : '';
// for hotel web
$isFromHotelPanel = isset($_REQUEST["isFromHotelPanel"]) ? $_REQUEST["isFromHotelPanel"] : 'No';
if ($appVersion != "") {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    if ($UserType == "Passenger") {
        $newAppVersion = $Platform == "IOS" ? $PASSENGER_IOS_APP_VERSION : $PASSENGER_ANDROID_APP_VERSION;
    }
    else if (strtolower($UserType) == "hotel" || strtolower($UserType) == "kiosk") {
        $newAppVersion = $Platform == "IOS" ? $KIOSK_IOS_APP_VERSION : $KIOSK_ANDROID_APP_VERSION;
    }
    else {
        //$newAppVersion = $generalobj->getConfigurations("configurations",$Platform == "IOS"? "DRIVER_IOS_APP_VERSION" : "DRIVER_ANDROID_APP_VERSION");
        $newAppVersion = $Platform == "IOS" ? $DRIVER_IOS_APP_VERSION : $DRIVER_ANDROID_APP_VERSION;
    }
    $appVersion = round($appVersion, 2);
    if ($newAppVersion != $appVersion && $newAppVersion > $appVersion) {
        $returnArr['Action'] = "0";
        $returnArr['isAppUpdate'] = "true";
        $returnArr['message'] = "LBL_NEW_UPDATE_MSG";
        setDataResponse($returnArr);
    }
}
function getPassengerDetailInfo($passengerID, $cityName = "", $LiveTripId = "") {
    global $generalobj, $obj, $demo_site_msg, $PHOTO_UPLOAD_SERVICE_ENABLE, $parent_ufx_catid, $generalSystemConfigDataArr, $tconfig, $vTimeZone, $vUserDeviceCountry, $_REQUEST, $intervalmins, $generalConfigPaymentArr, $ENABLE_RIDER_ADVERTISEMENT_BANNER, $ADVERTISEMENT_TYPE,$isUfxAvailable,$iServiceId,$vSystemDefaultCurrencyName,$vSystemDefaultCurrencySymbol,$Data_ALL_currency_Arr,$country_data_retrieve,$country_data_arr,$userDetailsArr,$generalTripRatingDataArr,$userAddressDataArr,$vehicleCategoryDataArr,$vSystemDefaultLangCode,$tripDetailsArr;
    $where = " iUserId = '" . $passengerID . "'";
    $tblName = "register_user";
    $data_version['iAppVersion'] = "2";
    $data_version['eLogout'] = 'No';
    $data_version['eDebugMode'] = isset($_REQUEST["IS_DEBUG_MODE"]) ? $_REQUEST["IS_DEBUG_MODE"] : "";
    $data_version['tApiFileName'] = pathinfo(__FILE__, PATHINFO_FILENAME);
    #################################### Generate Session For GeoAPI ########################################
    $generalobj->generateSessionForGeo($passengerID, "Passenger");
    #################################### Generate Session For GeoAPI ########################################
    #################################### Configure App Version Info ########################################
    $arr_app_version = array();
    $arr_app_version['AppVersionName'] = isset($_REQUEST['GeneralAppVersion']) ? $_REQUEST['GeneralAppVersion'] : "";
    $arr_app_version['AppVersionCode'] = isset($_REQUEST['GeneralAppVersionCode']) ? $_REQUEST['GeneralAppVersionCode'] : "";
    #################################### Configure App Version Info ########################################
    $data_version['tVersion'] = strval(json_encode($arr_app_version));
    $data_version['tDeviceData'] = isset($_REQUEST['DEVICE_DATA']) ? $_REQUEST['DEVICE_DATA'] : "";
    $obj->MySQLQueryPerform($tblName, $data_version, 'update', $where);
    $updateQuery = "UPDATE trip_status_messages SET eReceived='Yes' WHERE iUserId='" . $passengerID . "' AND eToUserType='Passenger'";
    $obj->sql_query($updateQuery);
    //Added By HJ On 09-06-2020 For Optimization Start
    /*if(isset($userDetailsArr[$tblName."_".$passengerID]) && count($userDetailsArr[$tblName."_".$passengerID]) > 0){
        $row = $userDetailsArr[$tblName."_".$passengerID];
    }else{*/
        $sql = "SELECT *,iUserId as iMemberId FROM ".$tblName." WHERE iUserId='$passengerID'";
        $row = $obj->MySQLSelect($sql);
        $userDetailsArr[$tblName."_".$passengerID] = $row;
    //}
    //Added By HJ On 09-06-2020 For Optimization End
    if ($LiveTripId != "") {
        $sql_livetrip = "SELECT iTripId,iActive,vTripPaymentMode,iVehicleTypeId,fPickUpPrice,fNightPrice,vCouponCode,eType FROM `trips` WHERE iTripId='" . $LiveTripId . "'";
        $userlivetripdetails = $obj->MySQLSelect($sql_livetrip);
        if (count($userlivetripdetails) > 0) {
            $row[0]['iTripId'] = $userlivetripdetails[0]['iTripId'];
            $row[0]['vTripStatus'] = $userlivetripdetails[0]['iActive'];
            $row[0]['vTripPaymentMode'] = $userlivetripdetails[0]['vTripPaymentMode'];
            $row[0]['iSelectedCarType'] = $userlivetripdetails[0]['iVehicleTypeId'];
            $row[0]['fPickUpPrice'] = $userlivetripdetails[0]['fPickUpPrice'];
            $row[0]['fNightPrice'] = $userlivetripdetails[0]['fNightPrice'];
            $row[0]['vCouponCode'] = $userlivetripdetails[0]['vCouponCode'];
            $row[0]['eType'] = $userlivetripdetails[0]['eType'];
        }
    }
    if (count($row) > 0) {
        ### Update Tripid - 0 and TripStatus - None For UberX Trip ###
        $currenttrip = $row[0]['iTripId'];
        if ($currenttrip > 0) {
            $sql = "SELECT * FROM `trips` WHERE iTripId = '" . $currenttrip . "'";
            $db_currenttrip = $obj->MySQLSelect($sql);
            //Added By HJ On 10-06-2020 For Optimization Start
            if(count($db_currenttrip) > 0){
                $tripDetailsArr["trips_".$currenttrip] = $db_currenttrip;
            }
            //Added By HJ On 10-06-2020 For Optimization End
            if (count($db_currenttrip) > 0) {
                $currenttriptype = $db_currenttrip[0]['eType'];
                $currenttripsystem = $db_currenttrip[0]['eSystem'];
                if (($currenttriptype == "UberX" || $currenttriptype == "Multi-Delivery") && $LiveTripId == "") {
                    $update_sql = "UPDATE ".$tblName." set iTripId = '0',vTripStatus = 'NONE' WHERE iUserId ='" . $passengerID . "'";
                    $result = $obj->sql_query($update_sql);
                    $row[0]['vTripStatus'] = "NONE";
                    $row[0]['iTripId'] = 0;
                }
                if ($currenttripsystem == "DeliverAll") {
                    $row[0]['vTripStatus'] = "NONE";
                    $row[0]['iTripId'] = 0;
                }
            }
        }
        //Added By HJ On 08-06-2020 For Optimization currency Table Query Start
        if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol)) {
            $row[0]['vFlutterwaveCurrency'] = $vSystemDefaultCurrencyName;
            $vFlutterwavevSymbol = $vSystemDefaultCurrencySymbol;
        }else{
            $defaultCurrencyDataArr = get_value('currency', 'vName,vSymbol', 'eDefault', 'Yes');
            /* Added By PM On 09-12-2019 For Flutterwave Code Start */
            $row[0]['vFlutterwaveCurrency'] = $defaultCurrencyDataArr[0]['vName'];
            $vFlutterwavevSymbol = $defaultCurrencyDataArr[0]['vSymbol'];
        }
        //Added By HJ On 08-06-2020 For Optimization currency Table Query End
        /* Added By PM On 09-12-2019 For Flutterwave Code End */
        ### Update Tripid - 0 and TripStatus - None For UberX Trip ###
        $page_link = $tconfig['tsite_url'] . "sign-up_rider.php?UserType=Rider&vRefCode=" . $row[0]['vRefCode'];
        $link = get_tiny_url($page_link);
        //$activation_text = '<a href="'.$link.'" target="_blank"> '.$link.' </a>';
        $activation_text = "<a href='" . $link . "' target='_blank'> '" . $link . "' </a>";
        $vLanguage = $row[0]['vLang'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
            if (!empty($vSystemDefaultLangCode)) {
                $vLanguage = $vSystemDefaultLangCode;
            } else {
                $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
        }
        $langLabels = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
        if(isset($langLabels['LBL_SHARE_CONTENT_PASSENGER']) && trim($langLabels['LBL_SHARE_CONTENT_PASSENGER']) != ""){
            $LBL_SHARE_CONTENT_PASSENGER = $langLabels['LBL_SHARE_CONTENT_PASSENGER'];
        }else{
            $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_SHARE_CONTENT_PASSENGER' AND vCode = '" . $vLanguage . "'";
            $db_label = $obj->MySQLSelect($sql);
            $LBL_SHARE_CONTENT_PASSENGER = $db_label[0]['vValue'];
        }
        $row[0]['INVITE_SHARE_CONTENT'] = $LBL_SHARE_CONTENT_PASSENGER . " " . $link;
		
        foreach($generalSystemConfigDataArr as $key => $value){
            if(is_null($generalSystemConfigDataArr[$key]) || empty($generalSystemConfigDataArr[$key])){
                $generalSystemConfigDataArr[$key] = "";
            }
        }
		
        $row[0] = array_merge($row[0], $generalSystemConfigDataArr); // Added By HJ On 18-03-2020 For Optimized Function
        if ($_REQUEST['APP_TYPE'] != "") {
            $row[0]['APP_TYPE'] = $_REQUEST['APP_TYPE'];
        }
        $row[0]['GOOGLE_ANALYTICS'] = "";
        $row[0]['SERVER_MAINTENANCE_ENABLE'] = $row[0]['MAINTENANCE_APPS'];
        //Added By HJ On 16-07-2019 For Check Empty and # Value Of Configuration Start
        if (isset($row[0]['LIVE_CHAT_LICENCE_NUMBER']) && ($row[0]['LIVE_CHAT_LICENCE_NUMBER'] == "" || strpos($row[0]['LIVE_CHAT_LICENCE_NUMBER'], '#') !== false)) {
            $row[0]['ENABLE_LIVE_CHAT'] = "No";
        }
        if (isset($row[0]['SINCH_APP_ENVIRONMENT_HOST']) && ($row[0]['SINCH_APP_ENVIRONMENT_HOST'] == "" || strpos($row[0]['SINCH_APP_ENVIRONMENT_HOST'], '#') !== false)) {
            $row[0]['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
        }
        if (isset($row[0]['SINCH_APP_KEY']) && ($row[0]['SINCH_APP_KEY'] == "" || strpos($row[0]['SINCH_APP_KEY'], '#') !== false)) {
            $row[0]['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
        }
        if (isset($row[0]['SINCH_APP_SECRET_KEY']) && ($row[0]['SINCH_APP_SECRET_KEY'] == "" || strpos($row[0]['SINCH_APP_SECRET_KEY'], '#') !== false)) {
            $row[0]['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
        }
        //Added By HJ On 16-07-2019 For Check Empty and # Value Of Configuration End
        //echo "<pre>";print_r($row[0]['ENABLE_LIVE_CHAT']);die;
        $RIDER_EMAIL_VERIFICATION = $row[0]["RIDER_EMAIL_VERIFICATION"];
        $RIDER_PHONE_VERIFICATION = $row[0]["RIDER_PHONE_VERIFICATION"];
        $REFERRAL_AMOUNT = $row[0]["REFERRAL_AMOUNT"];
        $REFERRAL_AMOUNT_ARR = getPriceUserCurrency($passengerID, "Passenger", $REFERRAL_AMOUNT);
        $REFERRAL_AMOUNT_USER = $REFERRAL_AMOUNT_ARR['fPricewithsymbol'];
        if(isset($langLabels['LBL_INVITE_FRIEND_SHARE_PREFIX_TXT']) && trim($langLabels['LBL_INVITE_FRIEND_SHARE_PREFIX_TXT']) != ""){
            $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT = $langLabels['LBL_INVITE_FRIEND_SHARE_PREFIX_TXT'];
        }else{
            $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_PREFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        }
        if(isset($langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT']) && trim($langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT']) != ""){
            $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT = $langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT'];
        }else{
            $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        }
        $row[0]['INVITE_DESCRIPTION_CONTENT'] = $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT . " " . $REFERRAL_AMOUNT_USER . " " . $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT;
        if ($RIDER_EMAIL_VERIFICATION == 'No') {
            $row[0]['eEmailVerified'] = "Yes";
        }
        if ($RIDER_PHONE_VERIFICATION == 'No') {
            $row[0]['ePhoneVerified'] = "Yes";
        }
        $row[0]['PACKAGE_TYPE'] = strtoupper(PACKAGE_TYPE);
        $lang_usr = $row[0]['vLang'];
        $sql = "select vBody_$lang_usr as Message from send_message_templates where vEmail_Code = 'VERIFICATION_CODE_MESSAGE'";
        $data_SMS = $obj->MySQLSelect($sql);
        $row[0]['SMS_BODY'] = $data_SMS[0]['Message'];
        ## Display Braintree Charge Message ##
        if(isset($langLabels['LBL_BRAINTREE_CHARGE_MSG_TXT']) && trim($langLabels['LBL_BRAINTREE_CHARGE_MSG_TXT']) != ""){
            $LBL_BRAINTREE_CHARGE_MSG_TXT = $langLabels['LBL_BRAINTREE_CHARGE_MSG_TXT'];
        }else{
            $db_label_braintree = $obj->MySQLSelect("SELECT * FROM `language_label` WHERE vLabel = 'LBL_BRAINTREE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'");
            $LBL_BRAINTREE_CHARGE_MSG_TXT = $db_label_braintree[0]['vValue'];
        }
        $BRAINTREE_CHARGE_AMOUNT = $row[0]['BRAINTREE_CHARGE_AMOUNT'];
        $BRAINTREE_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($passengerID, "Passenger", $BRAINTREE_CHARGE_AMOUNT);
        $BRAINTREE_CHARGE_AMOUNT_USER = $BRAINTREE_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        $msg = str_replace('####', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $row[0]['BRAINTREE_CHARGE_MESSAGE'] = $msg;
        ## Display Braintree Charge Message ##
        ## Display Adyen Charge Message ##
        if(isset($langLabels['LBL_ADYEN_CHARGE_MSG_TXT']) && trim($langLabels['LBL_ADYEN_CHARGE_MSG_TXT']) != ""){
            $LBL_ADYEN_CHARGE_MSG_TXT = $langLabels['LBL_ADYEN_CHARGE_MSG_TXT'];
        }else{
            $db_label_adyen = $obj->MySQLSelect("SELECT * FROM `language_label` WHERE vLabel = 'LBL_ADYEN_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'");
            $LBL_ADYEN_CHARGE_MSG_TXT = $db_label_adyen[0]['vValue'];
        }
        $ADEYN_CHARGE_AMOUNT = $row[0]['ADYEN_CHARGE_AMOUNT'];
        $ADEYN_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($passengerID, "Passenger", $ADEYN_CHARGE_AMOUNT);
        $ADEYN_CHARGE_AMOUNT_USER = $ADEYN_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        $msg = str_replace('####', $ADEYN_CHARGE_AMOUNT_USER, $LBL_ADYEN_CHARGE_MSG_TXT);
        $row[0]['ADEYN_CHARGE_MESSAGE'] = $msg;
        ## Display Adyen Charge Message ##
        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        if(isset($langLabels['LBL_FLUTTERWAVE_CHARGE_MSG_TXT']) && trim($langLabels['LBL_FLUTTERWAVE_CHARGE_MSG_TXT']) != ""){
            $LBL_FLUTTERWAVE_CHARGE_MSG_TXT = $langLabels['LBL_FLUTTERWAVE_CHARGE_MSG_TXT'];
        }else{
            $db_label_flutter = $obj->MySQLSelect("SELECT * FROM `language_label` WHERE vLabel = 'LBL_FLUTTERWAVE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'");
            $LBL_FLUTTERWAVE_CHARGE_MSG_TXT = $db_label_flutter[0]['vValue'];
        }
        $amountDataArr = $generalobj->getSupportedCurrencyAmt($row[0]['FLUTTERWAVE_CHARGE_AMOUNT'], $row[0]['vFlutterwaveCurrency']);
        $row[0]['vFlutterwaveCurrency'] = $amountDataArr['CURRENCY_CODE'];
        $FLUTTERWAVE_CHARGE_AMOUNT = $amountDataArr['AMOUNT'];
        $FLUTTERWAVE_CHARGE_AMOUNT_USER_ARR = $FLUTTERWAVE_CHARGE_AMOUNT;
        $FLUTTERWAVE_CHARGE_AMOUNT_USER = $vFlutterwavevSymbol . $FLUTTERWAVE_CHARGE_AMOUNT;
        $msg = str_replace('####', $FLUTTERWAVE_CHARGE_AMOUNT_USER, $LBL_FLUTTERWAVE_CHARGE_MSG_TXT);
        $row[0]['FLUTTERWAVE_CHARGE_AMOUNT'] = $FLUTTERWAVE_CHARGE_AMOUNT;
        $row[0]['FLUTTERWAVE_CHARGE_MESSAGE'] = $msg;
        /* Added By PM On 09-12-2019 For Flutterwave Code End */
        ## Check and update Device Session ID ##
        if ($row[0]['tDeviceSessionId'] == "") {
            $random = substr(md5(rand()) , 0, 7);
            $Update_Device_Session['tDeviceSessionId'] = session_id() . time() . $random;
            $Update_Device_Session_id = $obj->MySQLQueryPerform($tblName, $Update_Device_Session, 'update', $where);
            $row[0]['tDeviceSessionId'] = $Update_Device_Session['tDeviceSessionId'];
        }
        ## Check and update Device Session ID ##
        ## Check and update Session ID ##
        if ($row[0]['tSessionId'] == "") {
            $Update_Session['tSessionId'] = session_id() . time();
            $Update_Session_id = $obj->MySQLQueryPerform($tblName, $Update_Session, 'update', $where);
            $row[0]['tSessionId'] = $Update_Session['tSessionId'];
        }
        ## Check and update Session ID ##
        if ($row[0]['vImgName'] != "" && $row[0]['vImgName'] != "NONE") {
            $row[0]['vImgName'] = "3_" . $row[0]['vImgName'];
        }
        $row[0]['Passenger_Password_decrypt'] = "";
        if ($row[0]['eStatus'] != "Active") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            if ($row[0]['eStatus'] != "Deleted") {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            }
            setDataResponse($returnArr);
        }
        $TripStatus = $row[0]['vTripStatus'];
        $TripID = $row[0]['iTripId'];
        $eType = "";
        if ($TripID != "" && $TripID != NULL && $TripID != 0) {
            //Added By HJ On 10-06-2020 For Optimization Start
            if(isset($tripDetailsArr["trips_".$TripID]) && count($tripDetailsArr["trips_".$TripID]) > 0){
                $eType=  $tripDetailsArr["trips_".$TripID][0]['eType'];
            }else{
                $eType = get_value('trips', 'eType', 'iTripId', $TripID, '', 'true');
            }
            //Added By HJ On 10-06-2020 For Optimization End
        }
        if ($row[0]['APP_TYPE'] == "Ride-Delivery-UberX" || $row[0]['APP_TYPE'] == "Ride-Delivery") { // Changed By HJ On 02-04-2019 As Per Discuss With KS
            $ssql = " AND (eType = 'Ride' or eType = 'Deliver')";
        }
        else if ($row[0]['APP_TYPE'] == "Delivery") { // Added By HJ On 02-04-2019 As Per Discuss With KS
            $ssql = " AND eType = 'Deliver'";
        }
        $sqlcheckride = "SELECT iTripId,eType,iActive FROM `trips` WHERE (iActive='Active' OR iActive='On Going Trip') $ssql and iUserId = '" . $passengerID . "' AND eSystem = 'General' ORDER BY iTripId DESC LIMIT 0,1";
        $Tripcheckride = $obj->MySQLSelect($sqlcheckride);
        if (count($Tripcheckride) > 0) {
            $row[0]['vTripStatus'] = $Tripcheckride[0]['iActive'];
            $row[0]['iTripId'] = $Tripcheckride[0]['iTripId'];
            $row[0]['eType'] = $Tripcheckride[0]['eType'];
            $TripStatus = $row[0]['vTripStatus'];
            $TripID = $row[0]['iTripId'];
            $eType = $row[0]['eType'];
        }
        $sql1 = "SELECT iTripId FROM `trips` WHERE (iActive='Active' OR iActive='On Going Trip') and iUserId = '" . $passengerID . "' AND eSystem = 'General'";
        $check_trip = $obj->MySQLSelect($sql1);
        $row[0]['Allow_Edit_Profile'] = "Yes";
        if (count($Tripcheckride) > 0) {
            $row[0]['Allow_Edit_Profile'] = "No";
        }
        if ($LiveTripId == "" && $eType == "Multi-Delivery") {
            $row[0]['vTripStatus'] = "NONE";
            $row[0]['iTripId'] = "0";
            $TripStatus = $row[0]['vTripStatus'];
            $TripID = $row[0]['iTripId'];
        }
        if ($TripStatus != "NONE") {
            $TripID = $row[0]['iTripId'];
            if ($LiveTripId != "") {
                $TripID = $LiveTripId;
            }
            //Added By HJ On 13-06-2020 For Optimization Start
            $row_result_ratings_trip = array();
            if($TripID >0){
                if(isset($generalTripRatingDataArr['ratings_user_driver_'.$TripID])){
                    $getTripRateData = $generalTripRatingDataArr['ratings_user_driver_'.$TripID];
                }else{
                    $generalTripRatingDataArr = array();
                    $getTripRateData = $obj->MySQLSelect("SELECT * FROM `ratings_user_driver` WHERE iTripId='".$TripID."'");
                    $generalTripRatingDataArr['ratings_user_driver_'.$TripID] = $getTripRateData;
                    //echo "<pre>";print_r($generalTripRatingDataArr);die;
                }
                for($r=0;$r<count($getTripRateData);$r++){
                    $rateUserType = $getTripRateData[$r]['eUserType'];
                    if(strtoupper($rateUserType) == "PASSENGER"){
                        $row_result_ratings_trip[] =$getTripRateData[$r];
                    }
                }
                
            }
            //Added By HJ On 13-06-2020 For Optimization End
            //echo "<pre>";print_r($TripID);die;
            //echo "<pre>";print_r($generalTripRatingDataArr);die;
            $row_result_trips = getTripPriceDetails($TripID, $passengerID, "Passenger");
            //Added By HJ On 09-01-2020 For Get Driver Destination Mode Status Start
            $row[0]['DriverDetails'] = $row_result_trips['DriverDetails'];
            $row_result_trips['eDestinationMode'] = "No";
            if (isset($row_result_trips['DriverDetails']['eDestinationMode'])) {
                $row_result_trips['eDestinationMode'] = $row_result_trips['DriverDetails']['eDestinationMode'];
            }
            //Added By HJ On 09-01-2020 For Get Driver Destination Mode Status End
            $row[0]['TripDetails'] = $row_result_trips;
            $row_result_trips['DriverCarDetails']['make_title'] = $row_result_trips['DriverCarDetails']['model_title'] = "";
            if (isset($row_result_trips['DriverCarDetails']['vMake'])) {
                $row_result_trips['DriverCarDetails']['make_title'] = $row_result_trips['DriverCarDetails']['vMake'];
            }
            if (isset($row_result_trips['DriverCarDetails']['vTitle'])) {
                $row_result_trips['DriverCarDetails']['model_title'] = $row_result_trips['DriverCarDetails']['vTitle'];
            }
            $row[0]['DriverCarDetails'] = $row_result_trips['DriverCarDetails'];
            $sql = "SELECT vPaymentUserStatus FROM `payments` WHERE iTripId='$TripID'";
            $row_result_payments = $obj->MySQLSelect($sql);
            $row[0]['PaymentStatus_From_Passenger'] = "No Entry";
            if (count($row_result_payments) > 0) {
                $row[0]['PaymentStatus_From_Passenger'] = "Approved";
                if ($row_result_payments[0]['vPaymentUserStatus'] != 'approved') {
                    $row[0]['PaymentStatus_From_Passenger'] = "Not Approved";
                }
            }
            $row[0]['Ratings_From_Passenger'] = "No Entry";
            if (count($row_result_ratings_trip) > 0) {
                $count_row_rating = 0;
                $ContentWritten = "false";
                while (count($row_result_ratings_trip) > $count_row_rating) {
                    $UserType = $row_result_ratings_trip[$count_row_rating]['eUserType'];
                    $row[0]['Ratings_From_Passenger'] = "Not Done";
                    if ($UserType == "Passenger") {
                        $ContentWritten = "true";
                        $row[0]['Ratings_From_Passenger'] = "Done";
                    }
                    $count_row_rating++;
                }
            }
        }
        $sql = "SELECT ord.iOrderId,ord.iCompanyId,ord.iDriverId,ord.vOrderNo,ord.eTakeaway FROM `orders` as ord WHERE ord.iUserId='" . $passengerID . "' AND ord.iStatusCode = '6' AND (select count(iRatingId) from ratings_user_driver as rud where rud.iOrderId=ord.iOrderId AND rud.eFromUserType = 'Passenger' ) = 0  ORDER BY ord.iOrderId DESC LIMIT 0,1";
        $row_order = $obj->MySQLSelect($sql);
        //echo "<pre>";print_r($passengerID);die;
        $row[0]['Ratings_From_DeliverAll'] = "";
        if (count($row_order) > 0) {
            $LastOrderId = $row_order[0]['iOrderId'];
            $LastOrderNo = $row_order[0]['vOrderNo'];
            $LastOrderCompanyId = $row_order[0]['iCompanyId'];
            $LastOrderDriverId = $row_order[0]['iDriverId'];
            $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM register_driver WHERE iDriverId = '" . $LastOrderDriverId . "'";
            $result_driver = $obj->MySQLSelect($sql);
            //echo "<pre>";print_r($LastOrderDriverId);die;
            $sqlc = "SELECT vCompany AS CompanyName FROM company WHERE iCompanyId = '" . $LastOrderCompanyId . "'";
            $result_company = $obj->MySQLSelect($sqlc);
            $sql = "SELECT count(iRatingId) as TotalRating FROM `ratings_user_driver` WHERE iOrderId='" . $LastOrderId . "' AND eFromUserType = 'Passenger'";
            $row_result_ratings = $obj->MySQLSelect($sql);
            $TotalRating = $row_result_ratings[0]['TotalRating'];
            $row[0]['Ratings_From_DeliverAll'] = "Not Done";
            if ($TotalRating > 0) {
                $row[0]['Ratings_From_DeliverAll'] = "Done";
            }
            $row[0]['LastOrderId'] = $LastOrderId;
            $row[0]['LastOrderNo'] = $LastOrderNo;
            $row[0]['LastOrderCompanyId'] = $LastOrderCompanyId;
            $row[0]['LastOrderCompanyName'] = $result_company[0]['CompanyName'];
            $row[0]['LastOrderTakeaway'] = $row_order[0]['eTakeaway'];
            $row[0]['LastOrderDriverId'] = $LastOrderDriverId;
            $row[0]['LastOrderDriverName'] = "";
            if (isset($result_driver[0]['driverName']) && $result_driver[0]['driverName'] != "") {
                $row[0]['LastOrderDriverName'] = $result_driver[0]['driverName'];
            }
        }
        //Added By HJ On 13-06-2020 For Optimization user_address Table Query Start
        if(isset($userAddressDataArr['user_address_'.$passengerID])){
            $result_Address = $userAddressDataArr['user_address_'.$passengerID];
        }else{
            $userAddressDataArr = array();
            $result_Address = $obj->MySQLSelect("SELECT * from user_address WHERE iUserId = '" . $passengerID . "' AND eStatus = 'Active'");
            $userAddressDataArr['user_address_'.$passengerID] = $result_Address;
        }
        $totalAddressCount = 0;
        for($a=0;$a<count($result_Address);$a++){
            $addresUser = $result_Address[$a]['eUserType'];
            if(strtoupper($addresUser) == "RIDER"){
                $totalAddressCount += 1;
            }
        }
        //Added By HJ On 13-06-2020 For Optimization user_address Table Query End
        //print_r($result_Address);die;
        $row[0]['ToTalAddress'] = $totalAddressCount;
        $row[0]['DefaultCurrencySign'] = $row[0]["DEFAULT_CURRENCY_SIGN"];
        $row[0]['DefaultCurrencyCode'] = $row[0]["DEFAULT_CURRENCY_CODE"];
        $row[0]['FETCH_TRIP_STATUS_TIME_INTERVAL'] = fetchtripstatustimeinterval();
        $row[0]['ENABLE_TOLL_COST'] = $row[0]['APP_TYPE'] != "UberX" ? $row[0]['ENABLE_TOLL_COST'] : "No";
        /* Check Toll Enable For Passenger's Country */
        $usercountrycode = $row[0]['vCountry'];
        if ($usercountrycode != "") {
            //Added By HJ On 09-06-2020 For Optimization country Table Query Start
            if(isset($country_data_arr[$usercountrycode]['eEnableToll']) && trim($country_data_arr[$usercountrycode]['eEnableToll']) != ""){
                $eEnableToll = $country_data_arr[$usercountrycode]['eEnableToll'];
            }else{
                $user_country_toll = $obj->MySQLSelect("SELECT eEnableToll from country WHERE vCountryCode = '" . $usercountrycode . "'");
                $eEnableToll = $user_country_toll[0]['eEnableToll'];
            }
            //Added By HJ On 09-06-2020 For Optimization country Table Query End
            if ($eEnableToll != "") {
                $row[0]['ENABLE_TOLL_COST'] = ($eEnableToll == "Yes" && $row[0]['ENABLE_TOLL_COST'] == "Yes") ? "Yes" : "No";
            }
        }
        /* Check Toll Enable For Passenger's Country */
        if ($row[0]['APP_TYPE'] == "Ride" || $row[0]['APP_TYPE'] == "Ride-Delivery" || $row[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = $row[0]['FEMALE_RIDE_REQ_ENABLE'];
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = $row[0]['HANDICAP_ACCESSIBILITY_OPTION'];
        }
        else {
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = "No";
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";
            // $row[0]['ENABLE_TOLL_COST'] = "No";
            
        }
        if ($row[0]['APP_TYPE'] == "Ride" || $row[0]['APP_TYPE'] == "Ride-Delivery" || $row[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $row[0]['ENABLE_HAIL_RIDES'] = $row[0]['ENABLE_HAIL_RIDES'];
        }
        else {
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
        }
        if ($row[0]['APP_PAYMENT_MODE'] == "Card" || ONLYDELIVERALL == "Yes") {
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
        }
        $user_available_balance = $generalobj->get_user_available_balance_app_display($passengerID, "Rider");
        $row[0]['user_available_balance'] = strval($user_available_balance);
        $user_available_balance_arr = explode(" ", $user_available_balance);
        $row[0]['user_available_balance_amount'] = strval($user_available_balance_arr[1]);
        $user_available_balance_value = $generalobj->get_user_available_balance_app_display($passengerID, "Rider", 'Yes');
        $row[0]['user_available_balance_value'] = strval($user_available_balance_value);
        $row[0]['eWalletBalanceAvailable'] = 'Yes';
        if ($user_available_balance_value <= 0 || $user_available_balance_value <= 0.00) {
            $row[0]['eWalletBalanceAvailable'] = 'No';
        }
        if(!empty($_REQUEST['eSignUpType']) && $_REQUEST['eSignUpType'] == "kiosk") {
            $row[0]['eWalletBalanceAvailable'] = 'No';
        }
        $row[0]['PHOTO_UPLOAD_SERVICE_ENABLE'] = $row[0]['APP_TYPE'] == "UberX" ? $PHOTO_UPLOAD_SERVICE_ENABLE : "No";
        $row[0]['ENABLE_TIP_MODULE'] = $row[0]['ENABLE_TIP_MODULE'];
        $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
        //Added By Hasmukh On 21-12-2018 For Get Common Delivery Vehicle Category Data Start - Delivery Apps To Be Multi Delivery
        //Added By HJ On 13-06-2020 For Optimization Vehicle Category Table Query Start
        if(isset($vehicleCategoryDataArr[$sql_vehicle_category_table_name])){
            $getVehicleCatData = $vehicleCategoryDataArr[$sql_vehicle_category_table_name];
        }else{
            $getVehicleCatData = $obj->MySQLSelect("SELECT iServiceId,eDeliveryType,eSubCatType,eStatus,iParentId,eCatType,eFor,iVehicleCategoryId,vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name);
            $vehicleCategoryDataArr[$sql_vehicle_category_table_name] = $getVehicleCatData;
        }
        $getDataCategory =$vehicleCatNameArr = array();
        for($v=0;$v<count($getVehicleCatData);$v++){
            $vehicleeCatType = $getVehicleCatData[$v]['eCatType'];
            $iVehicleCategoryId = $getVehicleCatData[$v]['iVehicleCategoryId'];
            $iVehicleCategoryName = $getVehicleCatData[$v]['vCategory_'.$vLanguage];
            $vehicleeFor = $getVehicleCatData[$v]['eFor'];
            $vehicleCatNameArr[$iVehicleCategoryId] = $iVehicleCategoryName;
            if(strtoupper($vehicleeCatType) == "MOREDELIVERY" && strtoupper($vehicleeFor) == "DELIVERYCATEGORY"){
                $getDataCategory[] = $getVehicleCatData[$v];
            }
        }
        $vehicleCatName = "";
        if(isset($vehicleCatNameArr[$getDataCategory[0]['iVehicleCategoryId']])){
            $vehicleCatName =$vehicleCatNameArr[$getDataCategory[0]['iVehicleCategoryId']];
        }
        //Added By HJ On 13-06-2020 For Optimization Vehicle Category Table Query End
        $row[0]['DELIVERY_CATEGORY_ID'] = !empty($getDataCategory[0]['iVehicleCategoryId']) ? $getDataCategory[0]['iVehicleCategoryId'] : "";
        $row[0]['DELIVERY_CATEGORY_NAME'] = $vehicleCatName;
        //Commented By HJ On 13-06-2020 For Optimization Vehicle Category Table Query Start
        /*$getDataCategory = $obj->MySQLSelect("SELECT iVehicleCategoryId FROM " . $sql_vehicle_category_table_name . " WHERE `eCatType` =  'MoreDelivery' AND  `eFor` =  'DeliveryCategory'");
        $getCatName = $obj->MySQLSelect("SELECT vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " WHERE iVehicleCategoryId='" . $getDataCategory[0]['iVehicleCategoryId'] . "'");
        $vehicleCatName = "";
        if (count($getCatName) > 0) {
            $vehicleCatName = $getCatName[0]['vCategory_' . $vLanguage];
        }
        $row[0]['DELIVERY_CATEGORY_ID'] = !empty($getDataCategory[0]['iVehicleCategoryId']) ? $getDataCategory[0]['iVehicleCategoryId'] : "";
        $row[0]['DELIVERY_CATEGORY_NAME'] = $vehicleCatName;*/
        //Commented By HJ On 13-06-2020 For Optimization Vehicle Category Table Query End
        //Added By Hasmukh On 21-12-2018 For Get Common Delivery Vehicle Category Data End
        $host_arr = array();
        $host_arr = explode(".", $_SERVER["HTTP_HOST"]);
        $host_system = $host_arr[0];
        
        if ($_REQUEST['UBERX_PARENT_CAT_ID'] != "") {
            $parent_ufx_catid = $_REQUEST['UBERX_PARENT_CAT_ID'];
        }
        else {
            $parent_ufx_catid = "0";
        }
        if ($host_system == "carwash4") {
            $parent_ufx_catid = "1";
        }
        if ($host_system == "homecleaning4") {
            $parent_ufx_catid = "2";
        }
        if ($host_system == "doctor4") {
            $parent_ufx_catid = "3";
        }
        if ($host_system == "beautician4") {
            $parent_ufx_catid = "4";
        }
        if ($host_system == "massage4") {
            $parent_ufx_catid = "5";
        }
        if ($host_system == "tutors4") {
            $parent_ufx_catid = "7";
        }
        if ($host_system == "dogwalking4") {
            $parent_ufx_catid = "8";
        }
        if ($host_system == "towtruck4") {
            $parent_ufx_catid = "9";
        }
        if ($host_system == "plumbers4") {
            $parent_ufx_catid = "10";
        }
        if ($host_system == "electricians4") {
            $parent_ufx_catid = "11";
        }
        if ($host_system == "babysitting4") {
            $parent_ufx_catid = "12";
        }
        if ($host_system == "escorts4") {
            $parent_ufx_catid = "18";
        }
        if ($host_system == "fitnesscoach4") {
            $parent_ufx_catid = "13";
        }
        if ($host_system == "laundry4") {
            $parent_ufx_catid = "6";
        }
        if ($host_system == "snowplow4") {
            $parent_ufx_catid = "29";
        }
        if ($host_system == "securityguard4") {
            $parent_ufx_catid = "64";
        }
        
        $row[0]['UBERX_PARENT_CAT_ID'] = $parent_ufx_catid;
        //$row[0]['UBERX_PARENT_CAT_ID'] = 1;
        if (isset($row[0]['APP_TYPE']) && $row[0]['APP_TYPE'] == "UberX") {
            $row[0]['APP_DESTINATION_MODE'] = "None";
            $row[0]['ENABLE_TOLL_COST'] = "No";
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = "No";
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
            $row[0]['ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL'] = "5";
            $row[0]['ENABLE_CORPORATE_PROFILE'] = "No";
        }
        $row[0]['ENABLE_DELIVERY_MODULE'] = SITE_TYPE == "Demo" ? $row[0]['eDeliverModule'] : $row[0]['ENABLE_DELIVERY_MODULE'];
        $row[0]['PayPalConfiguration'] = $row[0]['ENABLE_DELIVERY_MODULE'] == "Yes" ? "Yes" : $row[0]['PAYMENT_ENABLED'];
        //Added By HJ On 08-06-2020 For Optimization currency Table Query Start
        $currencyNameArr =$defCurrencyValues= array();
        if(count($Data_ALL_currency_Arr) > 0){
            for($c=0;$c<count($Data_ALL_currency_Arr);$c++){
                if(strtoupper($Data_ALL_currency_Arr[$c]['eStatus']) == "ACTIVE"){
                    $defCurrencyValues[] = $Data_ALL_currency_Arr[$c];
                    $currencyNameArr[$Data_ALL_currency_Arr[$c]['vName']]  =$Data_ALL_currency_Arr[$c];
                }
            }
            $row[0]['CurrencyList'] = $defCurrencyValues;
        }else{
            $row[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        }
        //Added By HJ On 08-06-2020 For Optimization currency Table Query End
        $row[0]['SITE_TYPE'] = SITE_TYPE;
        $row[0]['RIIDE_LATER'] = RIIDE_LATER;
        $row[0]['PROMO_CODE'] = PROMO_CODE;
        $row[0]['DELIVERALL'] = DELIVERALL;
        $row[0]['ONLYDELIVERALL'] = ONLYDELIVERALL;
        $row[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
        //Added By HJ On 08-06-2020 For Optimization Start
        if(isset($currencyNameArr[$row[0]['vCurrencyPassenger']]['vSymbol']) && trim($currencyNameArr[$row[0]['vCurrencyPassenger']]['vSymbol']) != ""){
            $row[0]['CurrencySymbol'] = $currencyNameArr[$row[0]['vCurrencyPassenger']]['vSymbol'];
        }else{
            $row[0]['CurrencySymbol'] = get_value('currency', 'vSymbol', 'vName', $row[0]['vCurrencyPassenger'], '', 'true');
        }
        //Added By HJ On 08-06-2020 For Optimization End
        $eUnit = getMemberCountryUnit($passengerID, "Passenger");
        $row[0]['eUnit'] = $eUnit;
        $row[0]['SourceLocations'] = getusertripsourcelocations($passengerID, "SourceLocation");
        $row[0]['DestinationLocations'] = getusertripsourcelocations($passengerID, "DestinationLocation");
        $sql = "SELECT * FROM user_fave_address where iUserId = '" . $passengerID . "' AND eUserType = 'Passenger' AND eStatus = 'Active' ORDER BY iUserFavAddressId ASC";
        $db_passenger_fav_address = $obj->MySQLSelect($sql);
        $row[0]['UserFavouriteAddress'] = $db_passenger_fav_address;
        $usercountrydetailbytimezone = GetUserCounryDetail($passengerID, "Passenger", $vTimeZone, $vUserDeviceCountry);
        $row[0]['vDefaultCountry'] = $usercountrydetailbytimezone['vDefaultCountry'];
        $row[0]['vDefaultCountryCode'] = $usercountrydetailbytimezone['vDefaultCountryCode'];
        $row[0]['vDefaultPhoneCode'] = $usercountrydetailbytimezone['vDefaultPhoneCode'];
        $row[0]['vRCountryImage'] = $usercountrydetailbytimezone['vRImageMember']; //added by SP for country image related changes on 06-09-2019
        $row[0]['vSCountryImage'] = $usercountrydetailbytimezone['vSImageMember']; //added by SP for country image related changes on 06-09-2019
        //$row[0]['vRCountryImage'] = $usercountrydetailbytimezone['vRImage']; //added by SP for country image related changes on 05-08-2019
        //$row[0]['vSCountryImage'] = $usercountrydetailbytimezone['vSImage']; //added by SP for country image related changes on 05-08-2019
        //echo "<pre>";print_r($row['vSCountryImage']);die;
        $row[0]['vDefaultCountryImage'] = empty($row[0]['vSCountryImage']) ? $usercountrydetailbytimezone['vDefaultCountryImage'] : $row[0]['vSCountryImage']; //added by SP for country image related changes on 06-08-2019
        $row[0]['vPhoneCode'] = empty($row[0]['vPhoneCode']) ? $row[0]['vDefaultPhoneCode'] : $row[0]['vPhoneCode'];
        $row[0]['vCountry'] = empty($row[0]['vCountry']) ? $row[0]['vDefaultCountryCode'] : $row[0]['vCountry'];
        $SITE_POLICE_CONTROL_NUMBER = getMemberCountryPoliceNumber($passengerID, "Passenger", $row[0]['vCountry']);
        $row[0]['SITE_POLICE_CONTROL_NUMBER'] = $SITE_POLICE_CONTROL_NUMBER;
        $UserSelectedAddressArr = GetUserSelectedAddress($passengerID, "Passenger");
        $row[0]['UserSelectedAddress'] = $UserSelectedAddressArr['UserSelectedAddress'];
        $row[0]['UserSelectedLatitude'] = $UserSelectedAddressArr['UserSelectedLatitude'];
        $row[0]['UserSelectedLongitude'] = $UserSelectedAddressArr['UserSelectedLongitude'];
        $row[0]['UserSelectedAddressId'] = $UserSelectedAddressArr['UserSelectedAddressId'];
        $fOutStandingAmount =$fTripsOutStandingAmount= GetPassengerOutstandingAmount($passengerID);
        $row[0]['DISABLE_CASH_PAYMENT_OPTION'] = "No";
        $row[0]['fOutStandingAmount'] = 0;
        $row[0]['ServiceCategories'] = json_decode(serviceCategories, true);
        for($i = 0; $i < count($row[0]['ServiceCategories']); $i++){
            $item_tmp = $row[0]['ServiceCategories'][$i];
            if(is_null($item_tmp['tDescription']) || empty($item_tmp['tDescription'])){
               $row[0]['ServiceCategories'][$i]['tDescription'] = "";
            }
        }
        if ($fOutStandingAmount > 0) {
            $row[0]['DISABLE_CASH_PAYMENT_OPTION'] = "Yes";
            $getPriceUserCurrencyArr = getPriceUserCurrency($passengerID, "Passenger", $fOutStandingAmount);
            $row[0]['fOutStandingAmount'] = $getPriceUserCurrencyArr['fPricewithsymbol'];
        }
        /* As a part of Socket Cluster */
        $row[0]['MAX_ALLOW_TIME_INTERVAL_MILLI'] = (fetchtripstatustimeMAXinterval() + $intervalmins) * 1000;
        /* As a part of Socket Cluster */
        $vCurrencyPassenger = $row[0]['vCurrencyPassenger'];
        if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
            //Added By HJ On 08-06-2020 For Optimization currency Table Query Start
            if (!empty($vSystemDefaultCurrencyName)) {
                $vCurrencyPassenger = $vSystemDefaultCurrencyName;
            }else{
                $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
            }
            //Added By HJ On 08-06-2020 For Optimization currency Table Query End
        }
        
        if(isset($currencyNameArr[$vCurrencyPassenger]['vSymbol']) && trim($currencyNameArr[$vCurrencyPassenger]['vSymbol']) != ""){
            $CurrencySymbol = $currencyNameArr[$vCurrencyPassenger]['vSymbol'];
        }else{
            $CurrencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
        }
        if(isset($currencyNameArr[$vCurrencyPassenger]['Ratio']) && trim($currencyNameArr[$vCurrencyPassenger]['Ratio']) != ""){
            $Ratio = $currencyNameArr[$vCurrencyPassenger]['Ratio'];
        }else{
            $Ratio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
        }
        //$fTripsOutStandingAmount = GetPassengerOutstandingAmount($passengerID); // Commented By HJ On 09-06-2020 Because Already Above Got
        $fTripsOutStandingAmount = round(($fTripsOutStandingAmount * $Ratio) , 2);
        $row[0]['fOutStandingAmount'] = $fTripsOutStandingAmount;
        $row[0]['fOutStandingAmountWithSymbol'] = $CurrencySymbol . " " . $fTripsOutStandingAmount;
        $row[0]['MONGO_DB'] = $tconfig['tmongodb_databse'];
        $row[0]['MONGO_DB_CONNECTION_PORT'] = $tconfig['tmongodb_port'];
        $row[0]['SERVER_DEFAULT_TIMEZONE'] = date_default_timezone_get();
        $row[0]['ENABLE_MULTI_DELIVERY'] = ENABLE_MULTI_DELIVERY;
        $row[0]['tsite_upload_docs_file_extensions'] = $tconfig['tsite_upload_docs_file_extensions'];
        $row[0]['SC_CONNECT_URL'] = getSocketURL();
        $storeCatArr = json_decode(serviceCategories, true);
        $systemStoreEnable = checkSystemStoreSelection();
        $iserviceidstore = 0;
        if (count($storeCatArr) == 1) $iserviceidstore = $storeCatArr[0]['iServiceId'];
        if ($systemStoreEnable > 0) {
            for ($g = 0;$g < count($storeCatArr);$g++) {
                //echo "<pre>";print_r($storeCatArr);die;
                $storeData = $generalobj->getStoreDataForSystemStoreSelection($storeCatArr[$g]['iServiceId']);
                $iCompanyId = $storeData['iCompanyId'];
                $storeData['ispriceshow'] = $storeCatArr[$g]['iServiceId'];
                $storeCatArr[$g]['iCompanyId'] = $iCompanyId;
                $storeCatArr[$g]['STORE_DATA'] = $storeData;
                $storeCatArr[$g]['STORE_ID'] = $iCompanyId;
            }
            $companyData = $generalobj->getStoreDataForSystemStoreSelection($iserviceidstore);
            if (!empty($companyData[0]['iCompanyId'])) $row[0]['STORE_ID'] = $companyData[0]['iCompanyId'];
            else $row[0]['STORE_ID'] = $companyData['iCompanyId'];
        }
        $row[0]['ServiceCategories'] = $storeCatArr;
        for($i = 0; $i < count($row[0]['ServiceCategories']); $i++){
            $item_tmp = $row[0]['ServiceCategories'][$i];
            if(is_null($item_tmp['tDescription']) || empty($item_tmp['tDescription'])){
               $row[0]['ServiceCategories'][$i]['tDescription'] = "";
            }
        }
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not Start
        // Added By HJ On 04-06-2020 For Optimized country Table Query Start
        if(count($country_data_retrieve) > 0){
            $getCountryData = array();
            for($h=0;$h<count($country_data_retrieve);$h++){
                if(strtoupper($country_data_retrieve[$h]['eStatus']) == "ACTIVE"){
                    $getCountryData[] = $country_data_retrieve[$h]['iCountryId'];
                }
            }
            //echo "<pre>";print_r($getCountryData);die;
        }else{
            $getCountryData = $obj->MySQLSelect("SELECT iCountryId FROM country WHERE eStatus='Active'");
        }
        // Added By HJ On 04-06-2020 For Optimized country Table Query End
        $multiCountry = "No";
        if (count($getCountryData) > 1) {
            $multiCountry = "Yes";
        }
        $row[0]['showCountryList'] = $multiCountry;
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not End
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data Start
        if (checkSharkPackage() && $row[0]['eStatus'] == "Active" && ($row[0]['APP_TYPE'] != "Ride" || ($row[0]['APP_TYPE'] == "Ride" && $row[0]['vTripStatus'] != "On Going Trip" && $row[0]['vTripStatus'] != "Active"))) {
            $row[0]['advertise_banner_data'] = getAdvertisementBannersAsPerDevice($passengerID, "Passenger");
        }
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data End
        //Added By HJ On 08-06-2020 For Optimization configurations_payment Table Query Start
        if(isset($generalConfigPaymentArr['ENABLE_GOPAY']) && trim($generalConfigPaymentArr['ENABLE_GOPAY']) != ""){
            $EnableGopay = trim($generalConfigPaymentArr['ENABLE_GOPAY']);
        }else{
			$EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY', '', 'true');
            /* $EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY'); //added by SP for Gojek-gopay */
        }
        /* Added By HJ On 08-06-2020 For Optimization configurations_payment Table Query End */
       /*
		if (!empty($EnableGopay[0]['vValue'])) {
            $row[0]['ENABLE_GOPAY'] = $EnableGopay[0]['vValue'];
        }else {
            $row[0]['ENABLE_GOPAY'] = '';
        }  
		*/
		 
		if (!empty($EnableGopay)) {
            $row[0]['ENABLE_GOPAY'] = $EnableGopay;
        }else {
            $row[0]['ENABLE_GOPAY'] = '';
        } 
        if (checkDriverSubscriptionModule()) {
            $row[0]['DRIVER_SUBSCRIPTION_ENABLE'] = 'Yes';
        }
        else {
            $row[0]['DRIVER_SUBSCRIPTION_ENABLE'] = 'No';
        }
        $row[0]['RANDOM_COLORS_KEY_VAL_ARR'] = RANDOM_COLORS_KEY_VAL_ARR;
        //$row[0]['UFX_SERVICE_AVAILABLE'] = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
        $row[0]['UFX_SERVICE_AVAILABLE'] = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query
        $row[0]['AUTH_EMAIL_SYSTEM'] = AUTH_EMAIL_SYSTEM;
        $row[0]['ENABLE_CATEGORY_WISE_STORES'] = (isStoreCategoriesEnable() == 1) ? "Yes" : "No";
        $row[0]['ENABLE_TAKE_AWAY'] = (isTakeAwayEnable()) ? "Yes" : "No";
        $row[0]['CHECK_SYSTEM_STORE_SELECTION'] = ($systemStoreEnable > 0) ? "Yes" : "No";
        $row[0]['ENABLE_ADD_PROVIDER_FROM_STORE'] = ENABLE_ADD_PROVIDER_FROM_STORE;
        /* fetch value */
        return $row[0];
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}
function getDriverDetailInfo($driverId, $fromSignIN = 0) {
    global $generalobj, $obj, $demo_site_msg, $PHOTO_UPLOAD_SERVICE_ENABLE, $parent_ufx_catid, $generalSystemConfigDataArr, $vTimeZone, $tconfig, $vUserDeviceCountry, $intervalmins, $generalConfigPaymentArr, $POOL_ENABLE, $ENABLE_DRIVER_ADVERTISEMENT_BANNER, $ADVERTISEMENT_TYPE, $APP_TYPE, $_REQUEST, $MAX_DRIVER_DESTINATIONS,$isUfxAvailable,$vSystemDefaultCurrencyName,$vSystemDefaultCurrencySymbol,$tripDetailsArr,$generalTripRatingDataArr,$userAddressDataArr,$country_data_retrieve,$country_data_arr,$Data_ALL_currency_Arr,$driverVehicleDataArr;
    //echo "<pre>";print_r($generalConfigPaymentArr);die;
    ChangeDriverVehicleRideDeliveryFeatureDisable($driverId);
    $where = " iDriverId = '" . $driverId . "'";
    $data_version['iAppVersion'] = "2";
    $data_version['eLogout'] = 'No';
    $data_version['eDebugMode'] = isset($_REQUEST["IS_DEBUG_MODE"]) ? $_REQUEST["IS_DEBUG_MODE"] : "";
    $data_version['tApiFileName'] = pathinfo(__FILE__, PATHINFO_FILENAME);
    #################################### Generate Session For GeoAPI ########################################
    $generalobj->generateSessionForGeo($driverId, "Driver");
    #################################### Generate Session For GeoAPI ########################################
    #################################### Configure App Version Info ########################################
    $arr_app_version = array();
    $arr_app_version['AppVersionName'] = isset($_REQUEST['GeneralAppVersion']) ? $_REQUEST['GeneralAppVersion'] : "";
    $arr_app_version['AppVersionCode'] = isset($_REQUEST['GeneralAppVersionCode']) ? $_REQUEST['GeneralAppVersionCode'] : "";
    #################################### Configure App Version Info ########################################
    $data_version['tVersion'] = strval(json_encode($arr_app_version));
    $data_version['tDeviceData'] = isset($_REQUEST['DEVICE_DATA']) ? $_REQUEST['DEVICE_DATA'] : "";
    $obj->MySQLQueryPerform("register_driver", $data_version, 'update', $where);
    /* $sql = "SELECT iDriverVehicleId FROM register_driver WHERE iDriverId = '" . $driverId . "'";
    
      $db_drivervehicle_detail = $obj->MySQLSelect($sql);
    
      if(empty($db_drivervehicle_detail[0]['iDriverVehicleId']) || $db_drivervehicle_detail[0]['iDriverVehicleId']==0) {
    
      //$driver_vehicle
    
      } */
    $updateQuery = "UPDATE trip_status_messages SET eReceived='Yes' WHERE iDriverId='" . $driverId . "' AND eToUserType='Driver'";
    $obj->sql_query($updateQuery);
    $returnArr = array();
    $sql = "SELECT rd.*,cmp.eSystem,cmp.eStatus as cmpEStatus,(SELECT dv.vLicencePlate From driver_vehicle as dv WHERE rd.iDriverVehicleId != '' AND rd.iDriverVehicleId !='0' AND dv.iDriverVehicleId = rd.iDriverVehicleId) as vLicencePlateNo,rd.iDestinationCount FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='$driverId' AND cmp.iCompanyId=rd.iCompanyId";
    //$sql = "SELECT rd.* FROM `register_driver` as rd WHERE rd.iDriverId='$driverId'";
    $Data = $obj->MySQLSelect($sql);
    $Data[0]['eSystem_original'] = $Data[0]['eSystem'];
    //print_R($Data); exit;
    if (count($Data) > 0) {
        if (checkDriverDestinationModule()) {
            $iDestinationCount = $Data[0]['iDestinationCount'];
            if ($iDestinationCount >= $MAX_DRIVER_DESTINATIONS) {
                $Data[0]['DRIVER_DESTINATION_AVAILABLE'] = 'No';
            }
            else {
                $Data[0]['DRIVER_DESTINATION_AVAILABLE'] = 'Yes';
            }
        }
        //Added By HJ On 17-06-2020 For Optimization currency Table Query Start
        if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol)) {
            $Data[0]['vFlutterwaveCurrency'] = $vSystemDefaultCurrencyName;
            $vFlutterwavevSymbol = $vSystemDefaultCurrencySymbol;
        }else{
            $defaultCurrencyDataArr = get_value('currency', 'vName,vSymbol', 'eDefault', 'Yes');
            /* Added By PM On 09-12-2019 For Flutterwave Code Start */
            $Data[0]['vFlutterwaveCurrency'] = $defaultCurrencyDataArr[0]['vName'];
            $vFlutterwavevSymbol = $defaultCurrencyDataArr[0]['vSymbol'];
            /* Added By PM On 09-12-2019 For Flutterwave Code End */
        }
        //Added By HJ On 17-06-2020 For Optimization currency Table Query End
        
        $page_link = $tconfig['tsite_url'] . "sign-up.php?UserType=Driver&vRefCode=" . $Data[0]['vRefCode'];
        $link = get_tiny_url($page_link);
        //$activation_text = '<a href="'.$link.'" target="_blank"> '.$link.' </a>';
        $activation_text = "<a href='" . $link . "' target='_blank'> '" . $link . "' </a>";
        $vLanguage = $Data[0]['vLang'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
            if (!empty($vSystemDefaultLangCode)) {
                $vLanguage = $vSystemDefaultLangCode;
            } else {
                $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
        }
        $langLabels = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
        if(isset($langLabels['LBL_SHARE_CONTENT_DRIVER']) && trim($langLabels['LBL_SHARE_CONTENT_DRIVER']) != ""){
            $LBL_SHARE_CONTENT_DRIVER = $langLabels['LBL_SHARE_CONTENT_DRIVER'];
        }else{
            $db_label = $obj->MySQLSelect("SELECT * FROM `language_label` WHERE vLabel = 'LBL_SHARE_CONTENT_DRIVER' AND vCode = '" . $vLanguage . "'");
            $LBL_SHARE_CONTENT_DRIVER = $db_label[0]['vValue'];
        }
        $Data[0]['INVITE_SHARE_CONTENT'] = $LBL_SHARE_CONTENT_DRIVER . " " . $link;
	foreach($generalSystemConfigDataArr as $key => $value){
            if(is_null($generalSystemConfigDataArr[$key]) || empty($generalSystemConfigDataArr[$key])){
                $generalSystemConfigDataArr[$key] = "";
            }
        }
        $Data[0] = array_merge($Data[0], $generalSystemConfigDataArr); // Added By HJ On 18-03-2020 For Optimized Function
        if ($_REQUEST['APP_TYPE'] != "") {
            $Data[0]['APP_TYPE'] = $_REQUEST['APP_TYPE'];
        }
        $checkEditProfileStatus = $generalobj->getEditDriverProfileStatus($Data[0]['eStatus']); // Added By HJ On 13-11-2019 For Check Driver Profile Edit Status As Per Discuss With KS Sir
        //echo "<pre>";print_r($checkEditProfileStatus);die;
        $Data[0]['ENABLE_EDIT_DRIVER_PROFILE'] = $checkEditProfileStatus;
        $Data[0]['PACKAGE_TYPE'] = strtoupper(PACKAGE_TYPE);
        $Data[0]['GOOGLE_ANALYTICS'] = "";
        $Data[0]['SERVER_MAINTENANCE_ENABLE'] = $Data[0]['MAINTENANCE_APPS'];
        //Added By HJ On 16-07-2019 For Check Empty and # Value Of Configuration Start
        if (isset($Data[0]['LIVE_CHAT_LICENCE_NUMBER']) && ($Data[0]['LIVE_CHAT_LICENCE_NUMBER'] == "" || strpos($Data[0]['LIVE_CHAT_LICENCE_NUMBER'], '#') !== false)) {
            $Data[0]['ENABLE_LIVE_CHAT'] = "No";
        }
        if (isset($Data[0]['SINCH_APP_ENVIRONMENT_HOST']) && ($Data[0]['SINCH_APP_ENVIRONMENT_HOST'] == "" || strpos($Data[0]['SINCH_APP_ENVIRONMENT_HOST'], '#') !== false)) {
            $Data[0]['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
        }
        if (isset($Data[0]['SINCH_APP_KEY']) && ($Data[0]['SINCH_APP_KEY'] == "" || strpos($Data[0]['SINCH_APP_KEY'], '#') !== false)) {
            $Data[0]['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
        }
        if (isset($Data[0]['SINCH_APP_SECRET_KEY']) && ($Data[0]['SINCH_APP_SECRET_KEY'] == "" || strpos($Data[0]['SINCH_APP_SECRET_KEY'], '#') !== false)) {
            $Data[0]['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
        }
        //Added By HJ On 16-07-2019 For Check Empty and # Value Of Configuration End
        $DRIVER_EMAIL_VERIFICATION = $Data[0]["DRIVER_EMAIL_VERIFICATION"];
        $DRIVER_PHONE_VERIFICATION = $Data[0]["DRIVER_PHONE_VERIFICATION"];
        $REFERRAL_AMOUNT = $Data[0]["REFERRAL_AMOUNT"];
        $REFERRAL_AMOUNT_ARR = getPriceUserCurrency($driverId, "Driver", $REFERRAL_AMOUNT);
        $REFERRAL_AMOUNT_USER = $REFERRAL_AMOUNT_ARR['fPricewithsymbol'];
        if(isset($langLabels['LBL_INVITE_FRIEND_SHARE_PREFIX_TXT']) && trim($langLabels['LBL_INVITE_FRIEND_SHARE_PREFIX_TXT']) != ""){
            $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT = $langLabels['LBL_INVITE_FRIEND_SHARE_PREFIX_TXT'];
        }else{
            $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_PREFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        }
        if(isset($langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT']) && trim($langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT']) != ""){
            $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT = $langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT'];
        }else{
            $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        }
        if(isset($langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT']) && trim($langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT']) != ""){
            $LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT = $langLabels['LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT'];
        }else{
            $LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT', " and vCode='" . $vLanguage . "'", 'true');
        }
        $Data[0]['INVITE_DESCRIPTION_CONTENT'] = $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT . " " . $REFERRAL_AMOUNT_USER . " " . $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT;
        if ($DRIVER_EMAIL_VERIFICATION == 'No') {
            $Data[0]['eEmailVerified'] = "Yes";
        }
        if ($DRIVER_PHONE_VERIFICATION == 'No') {
            $Data[0]['ePhoneVerified'] = "Yes";
        }
        $lang_usr = $Data[0]['vLang'];
        $sql = "select vBody_$lang_usr as Message from send_message_templates where vEmail_Code = 'VERIFICATION_CODE_MESSAGE'";
        $data_SMS = $obj->MySQLSelect($sql);
        $Data[0]['SMS_BODY'] = $data_SMS[0]['Message'];
        ## Check and vWorkLocationRadius For UberX ##
        $eUnit = getMemberCountryUnit($driverId, "Driver");
        $Data[0]['eUnit'] = $eUnit;
        if ($Data[0]['vWorkLocationRadius'] == "" || $Data[0]['vWorkLocationRadius'] == "0" || $Data[0]['vWorkLocationRadius'] == 0) {
            $vWorkLocationRadius = $Data[0]['RESTRICTION_KM_NEAREST_TAXI'];
            $Update_Driver_radius['vWorkLocationRadius'] = $vWorkLocationRadius;
            $obj->MySQLQueryPerform("register_driver", $Update_Driver_radius, 'update', $where);
            $Data[0]['vWorkLocationRadius'] = $vWorkLocationRadius;
            if ($eUnit == "Miles") {
                $Data[0]['vWorkLocationRadius'] = round($vWorkLocationRadius * 0.621371, 2);
            }
            else {
                $Data[0]['vWorkLocationRadius'] = $vWorkLocationRadius;
            }
        }
        else {
            $vWorkLocationRadius = $Data[0]['vWorkLocationRadius'];
            $Data[0]['vWorkLocationRadius'] = $vWorkLocationRadius;
            if ($eUnit == "Miles") {
                $Data[0]['vWorkLocationRadius'] = round($vWorkLocationRadius * 0.621371, 2);
            }
        }
        ## Display Braintree Charge Message ##
        if(isset($langLabels['LBL_BRAINTREE_CHARGE_MSG_TXT']) && trim($langLabels['LBL_BRAINTREE_CHARGE_MSG_TXT']) != ""){
            $LBL_BRAINTREE_CHARGE_MSG_TXT = $langLabels['LBL_BRAINTREE_CHARGE_MSG_TXT'];
        }else{
            $db_label_braintree = $obj->MySQLSelect("SELECT * FROM `language_label` WHERE vLabel = 'LBL_BRAINTREE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'");
            $LBL_BRAINTREE_CHARGE_MSG_TXT = $db_label_braintree[0]['vValue'];
        }
        $BRAINTREE_CHARGE_AMOUNT = $Data[0]['BRAINTREE_CHARGE_AMOUNT'];
        $BRAINTREE_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($driverId, "Driver", $BRAINTREE_CHARGE_AMOUNT);
        $BRAINTREE_CHARGE_AMOUNT_USER = $BRAINTREE_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        //$msg = str_replace('##AMOUNT##', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $msg = str_replace('####', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $Data[0]['BRAINTREE_CHARGE_MESSAGE'] = $msg;
        ## Display Braintree Charge Message ##
        ## Display Adyen Charge Message ##
        if(isset($langLabels['LBL_ADYEN_CHARGE_MSG_TXT']) && trim($langLabels['LBL_ADYEN_CHARGE_MSG_TXT']) != ""){
            $LBL_ADYEN_CHARGE_MSG_TXT = $langLabels['LBL_ADYEN_CHARGE_MSG_TXT'];
        }else{
            $db_label_adyen = $obj->MySQLSelect("SELECT * FROM `language_label` WHERE vLabel = 'LBL_ADYEN_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'");
            $LBL_ADYEN_CHARGE_MSG_TXT = $db_label_adyen[0]['vValue'];
        }
        $ADEYN_CHARGE_AMOUNT = $Data[0]['ADYEN_CHARGE_AMOUNT'];
        $ADEYN_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($driverId, "Driver", $ADEYN_CHARGE_AMOUNT);
        $ADEYN_CHARGE_AMOUNT_USER = $ADEYN_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        $msg = str_replace('####', $ADEYN_CHARGE_AMOUNT_USER, $LBL_ADYEN_CHARGE_MSG_TXT);
        $Data[0]['ADEYN_CHARGE_MESSAGE'] = $msg;
        ## Display Adyen Charge Message ##
        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        ## Display Flutterwave Charge Message ##
        $FLUTTERWAVE_CHARGE_AMOUNT = $generalConfigPaymentArr['FLUTTERWAVE_CHARGE_AMOUNT'];
        if(isset($langLabels['LBL_FLUTTERWAVE_CHARGE_MSG_TXT']) && trim($langLabels['LBL_FLUTTERWAVE_CHARGE_MSG_TXT']) != ""){
           $LBL_FLUTTERWAVE_CHARGE_MSG_TXT =  $langLabels['LBL_FLUTTERWAVE_CHARGE_MSG_TXT'];
        }else{
            $db_label_flutter = $obj->MySQLSelect("SELECT * FROM `language_label` WHERE vLabel = 'LBL_FLUTTERWAVE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'");
            $LBL_FLUTTERWAVE_CHARGE_MSG_TXT = $db_label_flutter[0]['vValue'];
        }
        $amountDataArr = $generalobj->getSupportedCurrencyAmt($Data[0]['FLUTTERWAVE_CHARGE_AMOUNT'], $Data[0]['vFlutterwaveCurrency']);
        $Data[0]['vFlutterwaveCurrency'] = $amountDataArr['CURRENCY_CODE'];
        $FLUTTERWAVE_CHARGE_AMOUNT = $amountDataArr['AMOUNT'];
        $FLUTTERWAVE_CHARGE_AMOUNT_USER = $vFlutterwavevSymbol . $FLUTTERWAVE_CHARGE_AMOUNT;
        $Data[0]['FLUTTERWAVE_CHARGE_AMOUNT'] = $FLUTTERWAVE_CHARGE_AMOUNT;
        $msg = str_replace('####', $FLUTTERWAVE_CHARGE_AMOUNT_USER, $LBL_FLUTTERWAVE_CHARGE_MSG_TXT);
        $Data[0]['FLUTTERWAVE_CHARGE_MESSAGE'] = $msg;
        /* Added By PM On 09-12-2019 For Flutterwave Code End */
        ## Check and update Device Session ID ##
        if ($Data[0]['tDeviceSessionId'] == "") {
            $random = substr(md5(rand()) , 0, 7);
            $Update_Device_Session['tDeviceSessionId'] = session_id() . time() . $random;
            $Update_Device_Session_id = $obj->MySQLQueryPerform("register_driver", $Update_Device_Session, 'update', $where);
            $Data[0]['tDeviceSessionId'] = $Update_Device_Session['tDeviceSessionId'];
        }
        ## Check and update Device Session ID ##
        ## Check and update Session ID ##
        if ($Data[0]['tSessionId'] == "") {
            $Update_Session['tSessionId'] = session_id() . time();
            $Update_Session_id = $obj->MySQLQueryPerform("register_driver", $Update_Session, 'update', $where);
            $Data[0]['tSessionId'] = $Update_Session['tSessionId'];
        }
        ## Check and update Session ID ##
        //echo "<pre>";print_R($Data);die;
        $Data[0]['Driver_Password_decrypt'] = "";
        if ($Data[0]['vImage'] != "" && $Data[0]['vImage'] != "NONE") {
            $Data[0]['vImage'] = "3_" . $Data[0]['vImage'];
        }
        if (($Data[0]['iDriverVehicleId'] == '' || $Data[0]['iDriverVehicleId'] == NULL) && $Data[0]['APP_TYPE'] != "Ride-Delivery-UberX") {
            $sql = "SELECT iDriverVehicleId,vLicencePlate FROM  driver_vehicle WHERE `eStatus` = 'Active' AND `iDriverId` = '" . $driverId . "' ";
            $Data_vehicle = $obj->MySQLSelect($sql);
            $iDriver_VehicleId = $Data_vehicle[0]['iDriverVehicleId'];
            $vLicencePlate = $Data_vehicle[0]['vLicencePlate'];
            $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $driverId . "'";
            $obj->sql_query($sql);
            $Data[0]['iDriverVehicleId'] = $iDriver_VehicleId;
            //$vLicencePlate = get_value('driver_vehicle', 'vLicencePlate', 'iDriverVehicleId', $iDriver_VehicleId, '', 'true');
            $Data[0]['vLicencePlateNo'] = $vLicencePlate;
        }
        if ($Data[0]['iDriverVehicleId'] != '' && $Data[0]['iDriverVehicleId'] != '0') {
            //Added By HJ On 17-06-2020 For Optimize driver_vehicle Table Query Start
            if(isset($driverVehicleDataArr['driver_vehicle_'.$Data[0]['iDriverVehicleId']])){
                $DriverVehicle = $driverVehicleDataArr['driver_vehicle_'.$Data[0]['iDriverVehicleId']];
            }else{
                $DriverVehicle = $obj->MySQLSelect( "SELECT ma.vMake,mo.vTitle,dv.* FROM driver_vehicle as dv LEFT JOIN make as ma ON dv.iMakeId = ma.iMakeId LEFT JOIN model as mo ON dv.iModelId = mo.iModelId WHERE dv.iDriverVehicleId = '" . $Data[0]['iDriverVehicleId'] . "'");
                $driverVehicleDataArr['driver_vehicle_'.$Data[0]['iDriverVehicleId']] = $DriverVehicle;
            }
            //Added By HJ On 17-06-2020 For Optimize driver_vehicle Table Query End
            //echo "<pre>";print_r($DriverVehicle);die;
            $Data[0]['vMake'] = $DriverVehicle[0]['vMake'];
            $Data[0]['vModel'] = $DriverVehicle[0]['vTitle'];
            $vLicencePlate = $DriverVehicle[0]['vLicencePlate'];
            // added
            //$vLicencePlate = get_value('driver_vehicle', 'vLicencePlate', 'iDriverVehicleId', $Data[0]['iDriverVehicleId'], '', 'true');
            $Data[0]['vLicencePlateNo'] = $vLicencePlate;
        }
        if ($Data[0]['eStatus'] == "Deleted") {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = $Data[0]['eStatus'];
            $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            setDataResponse($returnArr);
        }
        $TripStatus = $Data[0]['vTripStatus'];
        $Data[0]['RegistrationDate'] = date("Y-m-d", strtotime($Data[0]['tRegistrationDate'] . ' -1 day '));
        if ($TripStatus != "NONE") {
            $TripID = $Data[0]['iTripId'];
            $row_result_trips = getTripPriceDetails($TripID, $driverId, "Driver");
            $Data[0]['TripDetails'] = $row_result_trips;
            $Data[0]['PassengerDetails'] = $row_result_trips['PassengerDetails'];
            $Data[0]['eSystem'] = $row_result_trips['eSystem'];
            //Added By HJ On 17-06-2020 For Optimize trip_times Table Query Start
            if(isset($tripDetailsArr["trip_times_".$TripID])){
                $db_tripTimes = $tripDetailsArr["trip_times_".$TripID];
            }else{
                $db_tripTimes = $obj->MySQLSelect("SELECT * FROM `trip_times` WHERE iTripId='".$TripID."'");
                $tripDetailsArr["trip_times_".$TripID] = $db_tripTimes;
            }
            //Added By HJ On 17-06-2020 For Optimize trip_times Table Query End
            $totalSec = 0;
            $timeState = 'Pause';
            $iTripTimeId = '';
            foreach ($db_tripTimes as $dtT) {
                if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                    $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
                }
                else {
                    $totalSec += strtotime(date('Y-m-d H:i:s')) - strtotime($dtT['dResumeTime']);
                    $iTripTimeId = $dtT['iTripTimeId'];
                    $timeState = 'Resume';
                }
            }
            // $diff = strtotime('2009-10-05 18:11:08') - strtotime('2009-10-05 18:07:13')
            $Data[0]['iTripTimeId'] = $iTripTimeId;
            $Data[0]['TotalSeconds'] = $totalSec;
            $Data[0]['TimeState'] = $timeState;
            if ($Data[0]['eSystem'] == "DeliverAll") {
                ############################# Food System Ratings From Driver  #############################
                $sql = "SELECT ord.iOrderId,ord.iCompanyId,ord.iUserId,ord.iUserAddressId,ord.fNetTotal,ord.vOrderNo FROM `orders` as ord WHERE ord.iDriverId='" . $driverId . "' AND ord.iStatusCode = '6' AND (select count(iRatingId) from ratings_user_driver as rud where rud.iOrderId=ord.iOrderId AND rud.eFromUserType = 'Driver' ) = 0  ORDER BY ord.iOrderId DESC LIMIT 0,1";
                $row_order = $obj->MySQLSelect($sql);
                $Data[0]['Ratings_From_Driver'] = "";
                if (count($row_order) > 0) {
                    $LastOrderId = $row_order[0]['iOrderId'];
                    $LastOrderCompanyId = $row_order[0]['iCompanyId'];
                    $LastOrderUserId = $row_order[0]['iUserId'];
                    $fNetTotal = $row_order[0]['fNetTotal'];
                    $iUserAddressId = $row_order[0]['iUserAddressId'];
                    $LastOrderNo = $row_order[0]['vOrderNo'];
                    $UserAddressArr = GetUserAddressDetail($LastOrderUserId, "Passenger", $iUserAddressId);
                    $UserAdress = ucfirst($UserAddressArr['UserAddress']);
                    $DriverDetailsArr = getDriverCurrencyLanguageDetails($driverId, $LastOrderId);
                    $vSymbol = $DriverDetailsArr['currencySymbol'];
                    $priceRatio = $DriverDetailsArr['Ratio'];
                    $fNetTotal = round(($fNetTotal * $priceRatio) , 2);
                    $sql = "SELECT CONCAT(vName,' ',vLastName) AS UserName FROM register_user WHERE iUserId = '" . $LastOrderUserId . "'";
                    $result_user = $obj->MySQLSelect($sql);
                    $sql = "SELECT count(iRatingId) as TotalRating FROM `ratings_user_driver` WHERE iOrderId='" . $LastOrderId . "' AND eFromUserType = 'Driver'";
                    $row_result_ratings = $obj->MySQLSelect($sql);
                    $TotalRating = $row_result_ratings[0]['TotalRating'];
                    $Data[0]['Ratings_From_Driver'] = "Not Done";
                    if ($TotalRating > 0) {
                        $Data[0]['Ratings_From_Driver'] = "Done";
                    }
                    $Data[0]['LastOrderId'] = $LastOrderId;
                    $Data[0]['LastOrderCompanyId'] = $LastOrderCompanyId;
                    $Data[0]['LastOrderUserId'] = $LastOrderUserId;
                    $Data[0]['LastOrderUserAddress'] = $UserAdress;
                    $Data[0]['LastOrderUserName'] = $result_user[0]['UserName'];
                    $Data[0]['LastOrderAmount'] = $vSymbol . " " . $fNetTotal;
                    $Data[0]['LastOrderNo'] = $LastOrderNo;
                }
                ############################# Food System Ratings From Driver  #############################
                
            } else {
                ############################# Ride System Ratings From Driver  #############################
                //Added By HJ On 13-06-2020 For Optimization ratings_user_driver Table Query Start
                $row_result_ratings = array();
                if($TripID >0){
                    if(isset($generalTripRatingDataArr['ratings_user_driver_'.$TripID])){
                        $getTripRateData = $generalTripRatingDataArr['ratings_user_driver_'.$TripID];
                        for($r=0;$r<count($getTripRateData);$r++){
                            $rateUserType = $getTripRateData[$r]['eUserType'];
                            if(strtoupper($rateUserType) == "DRIVER"){
                                $row_result_ratings[] = $getTripRateData[$r];
                            }
                        }
                    }else{
                        $row_result_ratings = $obj->MySQLSelect("SELECT iTripId,eUserType FROM `ratings_user_driver` WHERE iTripId='".$TripID."' AND eUserType='Driver'");
                    }
                }
                //Added By HJ On 13-06-2020 For Optimization ratings_user_driver Table Query End
                $Data[0]['Ratings_From_Driver'] = "No Entry";
                if (count($row_result_ratings) > 0) {
                    $count_row_rating = 0;
                    $ContentWritten = "false";
                    while (count($row_result_ratings) > $count_row_rating) {
                        $UserType = $row_result_ratings[$count_row_rating]['eUserType'];
                        $Data[0]['Ratings_From_Driver'] = "Not Done";
                        if ($UserType == "Driver") {
                            $ContentWritten = "true";
                            $Data[0]['Ratings_From_Driver'] = "Done";
                        }
                        $count_row_rating++;
                    }
                }
                if ($row_result_trips['eBookingFrom'] == 'Kiosk' && $row_result_trips['ePaymentCollect'] == 'Yes') {
                    $ContentWritten = "true";
                    $Data[0]['Ratings_From_Driver'] = "Done";
                }
            }
            ############################# Ride System Ratings From Driver  #############################
            $Data[0]['TotalFareUberX'] =$Data[0]['TotalFareUberXValue']=$Data[0]['UberXFareCurrencySymbol']= "0";
            //$isAvailable = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
            $isAvailable = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query
            if ((strtoupper(APP_TYPE) == "UBERX" || strtoupper(APP_TYPE) == "RIDE-DELIVERY-UBERX") && $isAvailable == "Yes") {
                include_once ('include/uberx/include_webservice_uberx.php');
                $UberX_Trip_Charge = DisplayTripChargeForUberX($TripID);
                $Data[0]['TotalFareUberX'] = is_nan($UberX_Trip_Charge['TotalFareUberX']) ? "" : $UberX_Trip_Charge['TotalFareUberX'];
                $Data[0]['TotalFareUberXValue'] = is_nan($UberX_Trip_Charge['TotalFareUberXValue']) ? "" : $UberX_Trip_Charge['TotalFareUberXValue'];
                $Data[0]['UberXFareCurrencySymbol'] = $UberX_Trip_Charge['UberXFareCurrencySymbol'];
            }
        }
        //Added By HJ On 17-06-2020 For Optimization user_address Table Query Start
        if(isset($userAddressDataArr['user_address_'.$driverId])){
            $result_Address = $userAddressDataArr['user_address_'.$driverId];
        }else{
            $userAddressDataArr = array();
            $result_Address = $obj->MySQLSelect("SELECT * from user_address WHERE iUserId = '" . $driverId . "' AND eStatus = 'Active'");
            $userAddressDataArr['user_address_'.$driverId] = $result_Address;
        }
        $totalAddressCount = 0;
        for($a=0;$a<count($result_Address);$a++){
            $addresUser = $result_Address[$a]['eUserType'];
            if(strtoupper($addresUser) == "DRIVER"){
                $totalAddressCount += 1;
            }
        }
        //Added By HJ On 17-06-2020 For Optimization user_address Table Query End
        $Data[0]['ToTalAddress'] = $totalAddressCount;
        $Data[0]['ABOUT_US_PAGE_DESCRIPTION'] = "";
        $Data[0]['DefaultCurrencySign'] = $Data[0]["DEFAULT_CURRENCY_SIGN"];
        $Data[0]['DefaultCurrencyCode'] = $Data[0]["DEFAULT_CURRENCY_CODE"];
        $Data[0]['SITE_TYPE'] = SITE_TYPE;
        $Data[0]['RIIDE_LATER'] = RIIDE_LATER;
        $Data[0]['DELIVERALL'] = DELIVERALL;
        $Data[0]['ONLYDELIVERALL'] = ONLYDELIVERALL;
        //Added By HJ On 01-05-2020 For Check Store Driver Start
        $isStoreDriver = "No";
        if (strtoupper($Data[0]['eSystem_original']) == "DELIVERALL" && strtoupper(ENABLE_ADD_PROVIDER_FROM_STORE) == "YES") {
            $Data[0]['ONLYDELIVERALL'] = "Yes";
            $isStoreDriver = "Yes";
            $Data[0]['INVITE_DESCRIPTION_CONTENT'] = $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT . " " . $REFERRAL_AMOUNT_USER . " " . $LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT;
        }
        $Data[0]['STORE_PERSONAL_DRIVER'] = $isStoreDriver;
        //Added By HJ On 01-05-2020 For Check Store Driver End
        $Data[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
        $Data[0]['vLicencePlateNo'] = is_null($Data[0]['vLicencePlateNo']) == false ? $Data[0]['vLicencePlateNo'] : '';
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not Start
        // Added By HJ On 17-06-2020 For Optimized country Table Query Start
        if(count($country_data_retrieve) > 0){
            $getCountryData = array();
            for($h=0;$h<count($country_data_retrieve);$h++){
                if(strtoupper($country_data_retrieve[$h]['eStatus']) == "ACTIVE"){
                    $getCountryData[] = $country_data_retrieve[$h]['iCountryId'];
                }
            }
            //echo "<pre>";print_r($getCountryData);die;
        }else{
            $getCountryData = $obj->MySQLSelect("SELECT iCountryId FROM country WHERE eStatus='Active'");
        }
        // Added By HJ On 17-06-2020 For Optimized country Table Query End
        $multiCountry = "No";
        if (count($getCountryData) > 1) {
            $multiCountry = "Yes";
        }
        $Data[0]['showCountryList'] = $multiCountry;
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not End
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data Start
        if (checkSharkPackage() && $Data[0]['eStatus'] == "active") {
            $Data[0]['advertise_banner_data'] = getAdvertisementBannersAsPerDevice($driverId, "Driver");
        }
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data End
        //Added By Hasmukh On 16-11-2018 For Check Trip Pool Status Start
        $Data[0]['FETCH_TRIP_STATUS_TIME_INTERVAL'] = fetchtripstatustimeinterval();
        //Added By Hasmukh On 16-11-2018 For Check Trip Pool Status End
        $Data[0]['ENABLE_TOLL_COST'] = $Data[0]['APP_TYPE'] != "UberX" ? $Data[0]['ENABLE_TOLL_COST'] : "No";
        /* Check Toll Enable For Driver's Country */
        $usercountrycode = $Data[0]['vCountry'];
        if ($usercountrycode != "") {
            //Added By HJ On 17-06-2020 For Optimization country Table Query Start
            if(isset($country_data_arr[$usercountrycode]['eEnableToll']) && trim($country_data_arr[$usercountrycode]['eEnableToll']) != ""){
                $eEnableToll = $country_data_arr[$usercountrycode]['eEnableToll'];
            }else{
                $user_country_toll = $obj->MySQLSelect("SELECT eEnableToll from country WHERE vCountryCode = '" . $usercountrycode . "'");
                $eEnableToll = $user_country_toll[0]['eEnableToll'];
            }
            //Added By HJ On 17-06-2020 For Optimization country Table Query End
            if ($eEnableToll != "") {
                $Data[0]['ENABLE_TOLL_COST'] = ($eEnableToll == "Yes" && $Data[0]['ENABLE_TOLL_COST'] == "Yes") ? "Yes" : "No";
            }
        }
        /* Check Toll Enable For Driver's Country */
        if ($Data[0]['APP_TYPE'] == "UberX") {
            $Data[0]['APP_DESTINATION_MODE'] = "None";
            $Data[0]['ENABLE_TOLL_COST'] = $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'] = $Data[0]['FEMALE_RIDE_REQ_ENABLE'] = $Data[0]['ENABLE_HAIL_RIDES'] = $Data[0]['CHILD_SEAT_ACCESSIBILITY_OPTION'] = $Data[0]['WHEEL_CHAIR_ACCESSIBILITY_OPTION'] = "No";
        }
        if ($Data[0]['APP_TYPE'] == "Ride" || $Data[0]['APP_TYPE'] == "Ride-Delivery" || $Data[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $Data[0]['FEMALE_RIDE_REQ_ENABLE'] = $Data[0]['FEMALE_RIDE_REQ_ENABLE'];
            $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'] = $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'];
            $Data[0]['CHILD_SEAT_ACCESSIBILITY_OPTION'] = $Data[0]['CHILD_SEAT_ACCESSIBILITY_OPTION'];
            $Data[0]['WHEEL_CHAIR_ACCESSIBILITY_OPTION'] = $Data[0]['WHEEL_CHAIR_ACCESSIBILITY_OPTION'];
            $CheckRideDeliveryFeatureDisable_Arr = CheckRideDeliveryFeatureDisable();
            $eShowRideVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowRideVehicles'];
            $eShowDeliveryVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliveryVehicles'];
            if ($eShowRideVehicles == 'No' && $eShowDeliveryVehicles == "No") {
                $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";
            }
        } else {
            $Data[0]['FEMALE_RIDE_REQ_ENABLE'] = $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'] = $Data[0]['CHILD_SEAT_ACCESSIBILITY_OPTION'] = $Data[0]['WHEEL_CHAIR_ACCESSIBILITY_OPTION'] = "No";
        }
        if ($Data[0]['APP_TYPE'] == "Ride" || $Data[0]['APP_TYPE'] == "Ride-Delivery" || $Data[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $Data[0]['ENABLE_HAIL_RIDES'] = $Data[0]['ENABLE_HAIL_RIDES'];
        } else {
            $Data[0]['ENABLE_HAIL_RIDES'] = "No";
        }
        if ($Data[0]['APP_PAYMENT_MODE'] == "Card" || ONLYDELIVERALL == "Yes") {
            $Data[0]['ENABLE_HAIL_RIDES'] = "No";
        }
        //$Data[0]['ENABLE_HAIL_RIDES'] = "Yes"; //Comment This Line Added For Testing By HJ On 30-12-2018
        $Data[0]['PHOTO_UPLOAD_SERVICE_ENABLE'] = $Data[0]['APP_TYPE'] == "UberX" ? $PHOTO_UPLOAD_SERVICE_ENABLE : "No";
        $Data[0]['ENABLE_DELIVERY_MODULE'] = SITE_TYPE == "Demo" ? $Data[0]['eDeliverModule'] : $Data[0]['ENABLE_DELIVERY_MODULE'];
        $Data[0]['PayPalConfiguration'] = $Data[0]['ENABLE_DELIVERY_MODULE'] == "Yes" ? "Yes" : $Data[0]['PAYMENT_ENABLED'];
        //Added By HJ On 17-06-2020 For Optimization currency Table Query Start
        $currencyNameArr =$defCurrencyValues= array();
        if(count($Data_ALL_currency_Arr) > 0){
            for($c=0;$c<count($Data_ALL_currency_Arr);$c++){
                if(strtoupper($Data_ALL_currency_Arr[$c]['eStatus']) == "ACTIVE"){
                    $defCurrencyValues[] = $Data_ALL_currency_Arr[$c];
                    $currencyNameArr[$Data_ALL_currency_Arr[$c]['vName']]  =$Data_ALL_currency_Arr[$c];
                }
            }
            $Data[0]['CurrencyList'] = $defCurrencyValues;
        }else{
            $Data[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        }
        //Added By HJ On 17-06-2020 For Optimization currency Table Query End
        //$Data[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        $Data[0]['UBERX_PARENT_CAT_ID'] = $parent_ufx_catid;
        $Data[0]['UBERX_SUB_CAT_ID'] = "0";
        /* DRIVER DESTINATIONS START */
        if (checkDriverDestinationModule() && !empty($driverId)) {
            include_once ('include/features/include_destinations_driver.php');
            $Data[0]['DestinationLocations'] = getDriverFiveDestination($driverId);
        }
        /* DRIVER DESTINATIONS END */
        /* As a part of Socket Cluster */
        $Data[0]['MAX_ALLOW_TIME_INTERVAL_MILLI'] = (fetchtripstatustimeMAXinterval() + $intervalmins) * 1000;
        /* As a part of Socket Cluster */
        $user_available_balance = $generalobj->get_user_available_balance_app_display($driverId, "Driver");
        $Data[0]['user_available_balance'] = strval($user_available_balance);
        $user_available_balance_value = $generalobj->get_user_available_balance_app_display($driverId, "Driver", 'Yes');
        $Data[0]['user_available_balance_value'] = strval($user_available_balance_value);
        $Data[0]['eWalletBalanceAvailable'] = 'Yes';
        if ($user_available_balance_value <= 0 || $user_available_balance_value <= 0.00) {
            $Data[0]['eWalletBalanceAvailable'] = 'No';
        }
        //Added By HJ On 17-06-2020 For Optimization currency Table Query Start
        $vCurrencyDriver = $Data[0]['vCurrencyDriver'];
        if(isset($currencyNameArr[$vCurrencyDriver]['vSymbol']) && trim($currencyNameArr[$vCurrencyDriver]['vSymbol']) != ""){
            $CurrencySymbol = $currencyNameArr[$vCurrencyDriver]['vSymbol'];
        }else{
            $CurrencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');
        }
        $Data[0]['CurrencySymbol'] = $CurrencySymbol;
        //Added By HJ On 17-06-2020 For Optimization currency Table Query End
        $str_date = @date('Y-m-d H:i:s', strtotime('-1 minutes'));
        $sql_request = "SELECT * FROM passenger_requests WHERE iDriverId='" . $driverId . "' AND dAddedDate > '" . $str_date . "' ";
        $data_requst = $obj->MySQLSelect($sql_request);
        $Data[0]['CurrentRequests'] = $data_requst;
        $sql = "SELECT * FROM user_fave_address where iUserId = '" . $driverId . "' AND eUserType = 'Driver' AND eStatus = 'Active' ORDER BY iUserFavAddressId ASC";
        $db_driver_fav_address = $obj->MySQLSelect($sql);
        $Data[0]['UserFavouriteAddress'] = $db_driver_fav_address;
        $usercountrydetailbytimezone = GetUserCounryDetail($driverId, "Driver", $vTimeZone, $vUserDeviceCountry);
        //echo "<pre>";print_r($usercountrydetailbytimezone);die;
        $Data[0]['vDefaultCountry'] = $usercountrydetailbytimezone['vDefaultCountry'];
        $Data[0]['vDefaultCountryCode'] = $usercountrydetailbytimezone['vDefaultCountryCode'];
        $Data[0]['vDefaultPhoneCode'] = $usercountrydetailbytimezone['vDefaultPhoneCode'];
        //echo "aaaa";exit;
        $Data[0]['vRCountryImage'] = $usercountrydetailbytimezone['vRImageMember']; //added by SP for country image related changes on 06-09-2019
        $Data[0]['vSCountryImage'] = $usercountrydetailbytimezone['vSImageMember']; //added by SP for country image related changes on 06-09-2019
        //$Data[0]['vRCountryImage'] = $usercountrydetailbytimezone['vRImage']; //added by SP for country image related changes on 05-08-2019
        //$Data[0]['vSCountryImage'] = $usercountrydetailbytimezone['vSImage']; //added by SP for country image related changes on 05-08-2019
        $Data[0]['vDefaultCountryImage'] = empty($Data[0]['vSCountryImage']) ? $usercountrydetailbytimezone['vDefaultCountryImage'] : $Data[0]['vSCountryImage']; //added by SP for country image related changes on 06-08-2019
        $Data[0]['vCode'] = empty($Data[0]['vCode']) ? $Data[0]['vDefaultPhoneCode'] : $Data[0]['vCode'];
        $Data[0]['vCountry'] = empty($Data[0]['vCountry']) ? $Data[0]['vDefaultCountryCode'] : $Data[0]['vCountry'];
        $SITE_POLICE_CONTROL_NUMBER = getMemberCountryPoliceNumber($driverId, "Driver", $Data[0]['vCountry']);
        $Data[0]['SITE_POLICE_CONTROL_NUMBER'] = $SITE_POLICE_CONTROL_NUMBER;
        $Data[0]['MONGO_DB'] = $tconfig['tmongodb_databse'];
        $Data[0]['MONGO_DB_CONNECTION_PORT'] = $tconfig['tmongodb_port'];
        $Data[0]['SERVER_DEFAULT_TIMEZONE'] = date_default_timezone_get();
        if ($Data[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $Data[0]['eShowVehicles'] =$Data[0]['eShowRideVehicles']=$Data[0]['eShowDeliveryVehicles']= "Yes";
            $checkridedelivery = CheckRideDeliveryFeatureDisable();
            $Data[0]['eShowRideVehicles'] = $checkridedelivery['eShowRideVehicles'];
            $Data[0]['eShowDeliveryVehicles'] = $checkridedelivery['eShowDeliveryVehicles'];
            $Data[0]['eShowDeliverAllVehicles'] = $checkridedelivery['eShowDeliverAllVehicles'];
            if ($Data[0]['eShowRideVehicles'] == "No" && $Data[0]['eShowDeliveryVehicles'] == "No" && ($Data[0]['eShowDeliverAllVehicles'] == "No" || DELIVERALL == "No")) {
                $Data[0]['eShowVehicles'] = "No";
            }
        }
        $Data[0]['ENABLE_MULTI_DELIVERY'] = ENABLE_MULTI_DELIVERY;
        $Data[0]['tsite_upload_docs_file_extensions'] = $tconfig['tsite_upload_docs_file_extensions'];
        $Data[0]['SC_CONNECT_URL'] = getSocketURL();
        if (checkDriverDestinationModule()) {
            $Data[0]['DriverDestinationData'] = getDriverDestination($driverId);
        }
        if (checkDriverSubscriptionModule()) {
            $Data[0]['DRIVER_SUBSCRIPTION_ENABLE'] = 'Yes';
        }
        else {
            $Data[0]['DRIVER_SUBSCRIPTION_ENABLE'] = 'No';
        }
        //Added By HJ On 08-06-2020 For Optimization configurations_payment Table Query Start
        if(isset($generalConfigPaymentArr['ENABLE_GOPAY']) && trim($generalConfigPaymentArr['ENABLE_GOPAY']) != ""){
            $EnableGopay = trim($generalConfigPaymentArr['ENABLE_GOPAY']);
        }else{
			$EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY', '', 'true');
            /* $EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY'); //added by SP for Gojek-gopay */
        }
        /* Added By HJ On 08-06-2020 For Optimization configurations_payment Table Query End */
       /*
		if (!empty($EnableGopay[0]['vValue'])) {
            $Data[0]['ENABLE_GOPAY'] = $EnableGopay[0]['vValue'];
        }else {
            $Data[0]['ENABLE_GOPAY'] = '';
        }  
		*/
		 
		if (!empty($EnableGopay)) {
            $Data[0]['ENABLE_GOPAY'] = $EnableGopay;
        }else {
            $Data[0]['ENABLE_GOPAY'] = '';
        } 
        //$Data[0]['UFX_SERVICE_AVAILABLE'] = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
        $Data[0]['UFX_SERVICE_AVAILABLE'] = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query
        $Data[0]['RANDOM_COLORS_KEY_VAL_ARR'] = RANDOM_COLORS_KEY_VAL_ARR;
        $Data[0]['AUTH_EMAIL_SYSTEM'] = AUTH_EMAIL_SYSTEM;
        $Data[0]['ENABLE_TAKE_AWAY'] = (isTakeAwayEnable()) ? "Yes" : "No";
        $Data[0]['ENABLE_ADD_PROVIDER_FROM_STORE'] = ENABLE_ADD_PROVIDER_FROM_STORE;
        //echo "<pre>";print_r($Data);die;
        return $Data[0];
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['eStatus'] = "";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}
/* If no type found */
if ($type == '') {
    $result['result'] = 0;
    $result['message'] = 'Required parameter missing.';
    setDataResponse($result);
}
// new added
if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
    include_once ('include/include_webservice_enterprisefeatures.php'); // other 5 feature
    
}
if (strtoupper(PACKAGE_TYPE) == "SHARK") {
    include_once ('include/include_webservice_sharkfeatures.php'); // for 22 feature
    
}
if (strtoupper(APP_TYPE) == "RIDE" || strtoupper(APP_TYPE) == "RIDE-DELIVERY" || strtoupper(APP_TYPE) == "RIDE-DELIVERY-UBERX") {
    include_once ('include/ride/include_webservice_ride.php');
}
if (strtoupper(APP_TYPE) == "UBERX" || strtoupper(APP_TYPE) == "RIDE-DELIVERY-UBERX") {
    //$isAvailable = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
    $isAvailable = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query
    if ($isAvailable == "Yes") {
        include_once ('include/uberx/include_webservice_uberx.php');
    }
}
if (strtoupper(APP_TYPE) == "DELIVERY" || strtoupper(APP_TYPE) == "RIDE-DELIVERY" || strtoupper(APP_TYPE) == "RIDE-DELIVERY-UBERX") {
    include_once ('include/delivery/include_webservice_delivery.php');
}
if (checkFavDriverModule()) {
    include_once ('include/features/include_fav_driver.php');
}
if (checkStopOverPointModule()) {
    include_once ('include/features/include_stop_over_point.php');
}
if (checkDriverDestinationModule()) {
    include_once ('include/features/include_destinations_driver.php');
}
/* For Gojek-gopay added by SP start */
if (checkGojekGopayModule()) {
    include_once ('include/features/include_gojek_gopay.php');
}
/* For Gojek-gopay added by SP end */
/* For DriverSubscription added by SP start */
if (checkDriverSubscriptionModule()) {
    include_once ('include/features/include_driver_subscription.php');
}
/* For DriverSubscription added by SP end */
/* For Donation  start  */
if (checkDonationModule()) {
    include_once ('include/features/include_donation.php');
}
/* For Donation  start  */
/* added by SP for fly stations on 13-08-2019 start */
if (checkFlyStationsModule()) {
    include_once ('include/features/include_fly_stations.php');
}
/* added by SP for fly stations on 13-08-2019 end */
/* added by PM for Auto credit wallet driver on 25-01-2020 start */
if (checkAutoCreditDriverModule()) {
    include_once ('include/features/include_auto_credit_driver.php');
}
/* added by PM for Auto credit wallet driver on 25-01-2020 end */
/* -------------- For Luggage Lable default and as per user's Prefered language ----------------------- */
######################################## General types For all App Type ######################################
if ($type == 'language_label') {
    $lCode = isset($_REQUEST['vCode']) ? clean(strtoupper($_REQUEST['vCode'])) : ''; // User's prefered language
    /* find default language of website set by admin */
    if ($lCode == '') {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);
        $lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }
    $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label`  WHERE  `vCode` = '" . $lCode . "' ";
    $all_label = $obj->MySQLSelect($sql);
    $x = array();
    for ($i = 0;$i < count($all_label);$i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $x[$vLabel] = $vValue;
    }
    $x['vCode'] = $lCode; // to check in which languge code it is loading
    setDataResponse($x);
}
##########################################################################
if ($type == 'generalConfigData') {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? trim($_REQUEST['GeneralMemberId']) : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    //echo $vLang;die;
    $check_label =$default_label=$defLangValues= array();
    if(count($Data_ALL_langArr) > 0){
        for($g=0;$g<count($Data_ALL_langArr);$g++){
            if(strtoupper($Data_ALL_langArr[$g]['eStatus']) == "ACTIVE"){
                $check_label[] = $Data_ALL_langArr[$g];
                $defLangValues[] = $Data_ALL_langArr[$g];
            }
            if(strtoupper($Data_ALL_langArr[$g]['eStatus']) == "ACTIVE" && strtoupper($Data_ALL_langArr[$g]['eDefault']) == "YES"){
                $default_label[] = $Data_ALL_langArr[$g];
            }
        }
    }
    if ($vLang != '') {
        $vLangCode = $defaultLangCode = $vLang;
        if(count($check_label) > 0){
            //Data Found
        }else{
            $sql = "SELECT `eDefault`,`vCode` FROM  `language_master` WHERE eStatus = 'Active'";
            $check_label = $obj->MySQLSelect($sql);
        }
        for($k=0;$k<count($check_label);$k++){
            if(strtoupper($vLang) == strtoupper($check_label[$k]['vCode'])){
                $vLangCode = $vLang = $check_label[$k]['vCode'];
            }
            if(strtoupper($check_label[$k]['eDefault']) == "YES"){
                //$defaultLangCode = $vLang = $check_label[$k]['vCode'];
                $defaultLangCode = $check_label[$k]['vCode'];
            }
        }
    } else {
        if(count($default_label) > 0){
            //Data Found here
        }else{
            $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
            $default_label = $obj->MySQLSelect($sql);
        }
        $vLang = "EN";
        if(count($default_label) > 0){
            $vLang = $default_label[0]['vCode'];
        }
    }
    $storeCatArr = json_decode(serviceCategories, true);
    //it is done bc when in table in desc field insert like [] then null value is shown so app crash so put the following code
    foreach($storeCatArr as $key=>$value) {
        if(is_null($value['tDescription']) || $value['tDescription']=='' || $value['tDescription']=='null' || empty($value['tDescription'])) {
            $storeCatArr[$key]['tDescription'] = '';
        }
    }
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $iserviceidstore = 0;
    if (count($storeCatArr) == 1) $iserviceidstore = $storeCatArr[0]['iServiceId'];
    $systemStoreEnable = checkSystemStoreSelection();
    if ($systemStoreEnable > 0) {
        for ($g = 0;$g < count($storeCatArr);$g++) {
            //echo "<pre>";print_r($storeCatArr);die;
            $storeData = $generalobj->getStoreDataForSystemStoreSelection($storeCatArr[$g]['iServiceId']);
            $iCompanyId = $storeData['iCompanyId'];
            $storeData['ispriceshow'] = $storeCatArr[$g]['iServiceId'];
            $storeCatArr[$g]['iCompanyId'] = $iCompanyId;
            $storeCatArr[$g]['STORE_DATA'] = $storeData;
            $storeCatArr[$g]['STORE_ID'] = $iCompanyId;
        }
        $companyData = $generalobj->getStoreDataForSystemStoreSelection($iserviceidstore);
        if (!empty($companyData[0]['iCompanyId'])) $DataArr['STORE_ID'] = $companyData[0]['iCompanyId'];
        else $DataArr['STORE_ID'] = $companyData['iCompanyId'];
        //$DataArr['STORE_ID'] = $companyData[0]['iCompanyId'];
    }
    $DataArr['ServiceCategories'] = $storeCatArr;
    $DataArr['LanguageLabels'] = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $DataArr['Action'] = "1";
    if(count($defLangValues) > 0){
        //Data Found
    }else{
        $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active'  ORDER BY iDispOrder ASC";
        $defLangValues = $obj->MySQLSelect($sql);
    }
    $DataArr['LIST_LANGUAGES'] = $defLangValues;
    
    for ($i = 0;$i < count($defLangValues);$i++) {
        if ($defLangValues[$i]['eDefault'] == "Yes") {
            $DataArr['DefaultLanguageValues'] = $defLangValues[$i];
        }
    }

    if ($vLang != "") {
        if(count($Data_ALL_langArr) > 0){
            $requireLangValues = array();
            for($g=0;$g<count($Data_ALL_langArr);$g++){
                if(strtoupper($Data_ALL_langArr[$g]['eStatus']) == "ACTIVE" && strtoupper($Data_ALL_langArr[$g]['vCode']) == strtoupper($vLang)){
                    $requireLangValues[] = $Data_ALL_langArr[$g];
                }
            }
        }else{
            $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE `vCode` = '" . $vLang . "'  ";
            $requireLangValues = $obj->MySQLSelect($sql);
        }
        $DataArr['DefaultLanguageValues'] = $requireLangValues[0];
    }
      
    $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
    $defCurrencyValues = $obj->MySQLSelect($sql);
    $DataArr['LIST_CURRENCY'] = $defCurrencyValues;
    for ($i = 0;$i < count($defCurrencyValues);$i++) {
        if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
            $DataArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
        }
    }
    $DataArr['UPDATE_TO_DEFAULT'] = 'No';
    if (!empty($vCurrency)) {
        $sql = "SELECT  iCurrencyId FROM  `currency` WHERE eStatus = 'Active' AND `vName` = '" . $vCurrency . "'";
        $check_currency = $obj->MySQLSelect($sql);
        if (count($check_currency) == 0) {
            $DataArr['UPDATE_TO_DEFAULT'] = 'Yes';
        }
    }
    if (empty($vCurrency)) {
        $DataArr['UPDATE_TO_DEFAULT'] = 'Yes';
    }
    $DataArr = array_merge($DataArr, $generalSystemConfigDataArr); // Added By HJ On 18-03-2020 For Optimized Function
    //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not Start
    //$getCountryData = $obj->MySQLSelect("SELECT iCountryId FROM country WHERE eStatus='Active'"); // Commented By HJ On 04-06-2020 For Optimized Query
    // Added By HJ On 04-06-2020 For Optimized Query Start
    $getCountryData = array();
    for($h=0;$h<count($country_data_retrieve);$h++){
        if(strtoupper($country_data_retrieve[$h]['eStatus']) == "ACTIVE"){
            $getCountryData[] = $country_data_retrieve[$h]['iCountryId'];
        }
    }
    // Added By HJ On 04-06-2020 For Optimized Query End
    $multiCountry = "No";
    if (count($getCountryData) > 1) {
        $multiCountry = "Yes";
    }
    $DataArr['showCountryList'] = $multiCountry;
    //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not End
    $DataArr['GOOGLE_ANALYTICS'] = $DataArr['FACEBOOK_IFRAME'] = "";
    if ($UserType == "Passenger") {
        $DataArr['LINK_FORGET_PASS_PAGE_PASSENGER'] = $tconfig["tsite_url"] . $LINK_FORGET_PASS_PAGE_PASSENGER;
        $DataArr['CONFIG_CLIENT_ID'] = $CONFIG_CLIENT_ID;
        $DataArr['FACEBOOK_LOGIN'] = $PASSENGER_FACEBOOK_LOGIN;
        $DataArr['GOOGLE_LOGIN'] = $PASSENGER_GOOGLE_LOGIN;
        $DataArr['TWITTER_LOGIN'] = $PASSENGER_TWITTER_LOGIN;
        $DataArr['LINKEDIN_LOGIN'] = $PASSENGER_LINKEDIN_LOGIN;
    }
    else {
        $DataArr['LINK_FORGET_PASS_PAGE_DRIVER'] = $tconfig["tsite_url"] . $LINK_FORGET_PASS_PAGE_DRIVER;
        $DataArr['LINK_SIGN_UP_PAGE_DRIVER'] = $tconfig["tsite_url"] . $LINK_SIGN_UP_PAGE_DRIVER;
        $DataArr['FACEBOOK_LOGIN'] = $DRIVER_FACEBOOK_LOGIN;
        $DataArr['GOOGLE_LOGIN'] = $DRIVER_GOOGLE_LOGIN;
        $DataArr['TWITTER_LOGIN'] = $DRIVER_TWITTER_LOGIN;
        $DataArr['LINKEDIN_LOGIN'] = $DRIVER_LINKEDIN_LOGIN;
    }
    $DataArr['SERVER_MAINTENANCE_ENABLE'] = $MAINTENANCE_APPS;
    //Added By HJ On 16-07-2019 For Check Empty and # Value Of Configuration Start
    if (isset($DataArr['LIVE_CHAT_LICENCE_NUMBER']) && ($DataArr['LIVE_CHAT_LICENCE_NUMBER'] == "" || strpos($DataArr['LIVE_CHAT_LICENCE_NUMBER'], '#') !== false)) {
        $DataArr['ENABLE_LIVE_CHAT'] = "No";
    }
    if (isset($DataArr['SINCH_APP_ENVIRONMENT_HOST']) && ($DataArr['SINCH_APP_ENVIRONMENT_HOST'] == "" || strpos($DataArr['SINCH_APP_ENVIRONMENT_HOST'], '#') !== false)) {
        $DataArr['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
    }
    if (isset($DataArr['SINCH_APP_KEY']) && ($DataArr['SINCH_APP_KEY'] == "" || strpos($DataArr['SINCH_APP_KEY'], '#') !== false)) {
        $DataArr['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
    }
    if (isset($DataArr['SINCH_APP_SECRET_KEY']) && ($DataArr['SINCH_APP_SECRET_KEY'] == "" || strpos($DataArr['SINCH_APP_SECRET_KEY'], '#') !== false)) {
        $DataArr['RIDE_DRIVER_CALLING_METHOD'] = "Normal";
    }
    //Added By HJ On 16-07-2019 For Check Empty and # Value Of Configuration End
    $DataArr['SITE_TYPE'] = SITE_TYPE;
    $usercountrydetailbytimezone = GetUserCounryDetail($GeneralMemberId, $UserType, $vTimeZone, $vUserDeviceCountry);
    //echo "<pre>";print_r($usercountrydetailbytimezone);die;
    $DataArr['vDefaultCountry'] = $usercountrydetailbytimezone['vDefaultCountry'];
    $DataArr['vDefaultCountryCode'] = $usercountrydetailbytimezone['vDefaultCountryCode'];
    $DataArr['vDefaultPhoneCode'] = $usercountrydetailbytimezone['vDefaultPhoneCode'];
    //$DataArr['vRCountryImage'] = $usercountrydetailbytimezone['vRImage']; //added by SP for country image related changes on 05-08-2019
    //$DataArr['vSCountryImage'] = $usercountrydetailbytimezone['vSImage']; //added by SP for country image related changes on 05-08-2019
    $DataArr['vRCountryImage'] = $usercountrydetailbytimezone['vRImageMember']; //added by SP for country image related changes on 06-09-2019
    $DataArr['vSCountryImage'] = $usercountrydetailbytimezone['vSImageMember']; //added by SP for country image related changes on 06-09-2019
    $DataArr['vDefaultCountryImage'] = empty($DataArr['vSCountryImage']) ? $usercountrydetailbytimezone['vDefaultCountryImage'] : $DataArr['vSCountryImage']; //added by SP for country image related changes on 06-08-2019
    $DataArr['OPEN_SETTINGS_URL_SCHEMA'] = "A###p####!!!!!###p####!!!!###@@@@#######-Pr###@@@!!!!###ef####s:r##@@@@#oo###t=Se####tt###i@@@##n##@@g#s";
    $DataArr['OPEN_LOCATION_SETTINGS_URL_SCHEMA'] = "A##@@@##p#!!!!##p###-#P###!!!##r##!!!!#ef#!!!##@@##s:###@@@####ro##@@###!!!!###o###@@@#t=P####riv####!!!###ac####y&###!!!##p###a##!!!#t##h=L###O##CA#@@#TI##O#@#N";
    $DataArr['SC_CONNECT_URL'] = getSocketURL();
    $DataArr['DELIVERALL'] = DELIVERALL;
    $DataArr['ONLYDELIVERALL'] = ONLYDELIVERALL;
    if($iserviceidstore > 0){
        $DataArr['ONLYDELIVERALL'] = "Yes";
    }
    $DataArr['ENABLE_MULTI_DELIVERY'] = ENABLE_MULTI_DELIVERY;
    $DataArr['ENABLE_CATEGORY_WISE_STORES'] = (isStoreCategoriesEnable() == 1) ? "Yes" : "No";
    $DataArr = getCustomeNotificationSound($DataArr); // Added By HJ On 06-08-2019 For Get Custome Sound Notification File Name
    $DataArr['ENABLE_ADD_PROVIDER_FROM_STORE'] = ENABLE_ADD_PROVIDER_FROM_STORE;
    $DataArr['CHECK_SYSTEM_STORE_SELECTION'] = ($systemStoreEnable > 0) ? "Yes" : "No";
    setDataResponse($DataArr);
}
############################ country_list #############################
if ($type == 'countryList') {
    global $lang_label, $obj, $tconfig, $generalobj;
    $returnArr = array();
    $counter = 0;
    for ($i = 0;$i < 26;$i++) {
        $cahracter = chr(65 + $i);
        $sql = "SELECT COU.* FROM country as COU WHERE COU.eStatus = 'Active' AND COU.vPhoneCode!='' AND COU.vCountryCode!='' AND COU.vCountry LIKE '$cahracter%' ORDER BY COU.vCountry";
        $db_rec = $obj->MySQLSelect($sql);
        if (count($db_rec) > 0) {
            $countryListArr = array();
            $subCounter = 0;
            for ($j = 0;$j < count($db_rec);$j++) {
                $countryListArr[$subCounter] = $db_rec[$j];
                // added by SP if image missing default image shown on 04-10-2019
                //$temp_image = checkimgexist("/webimages/icons/country_flags/" . strtolower($db_rec[$j]['vCountryCode']) . "_r.png", '1');
                $temp_image = checkimgexist("webimages/icons/country_flags/" . $db_rec[$j]['vRImage'], '1');
                $countryListArr[$subCounter]['vRImage'] = $temp_image;
                //$temp_image = checkimgexist("/webimages/icons/country_flags/" . strtolower($db_rec[$j]['vCountryCode']) . "_s.png", '2');
                $temp_image = checkimgexist("webimages/icons/country_flags/" . $db_rec[$j]['vSImage'], '2');
                $countryListArr[$subCounter]['vSImage'] = $temp_image;
                //$countryListArr[$subCounter]['vRImage'] = $tconfig["tsite_url"] . "/webimages/icons/country_flags/" . strtolower($db_rec[$j]['vCountryCode']) . "_r.png"; //added by SP for country image related changes on 30-07-2019
                //$countryListArr[$subCounter]['vSImage'] = $tconfig["tsite_url"] . "/webimages/icons/country_flags/" . strtolower($db_rec[$j]['vCountryCode']) . "_s.png"; //added by SP for country image related changes on 30-07-2019
                $subCounter++;
            }
            if (count($countryListArr) > 0) {
                $returnArr[$counter]['key'] = $cahracter;
                $returnArr[$counter]['TotalCount'] = count($countryListArr);
                $returnArr[$counter]['List'] = $countryListArr;
                $counter++;
            }
        }
    }
    $countryArr['Action'] = "1";
    $countryArr['totalValues'] = count($returnArr);
    $countryArr['CountryList'] = $returnArr;
    setDataResponse($countryArr);
}
###########################################################################
if ($type == "signup") {
    $fbid = isset($_REQUEST["vFbId"]) ? $_REQUEST["vFbId"] : '';
    $Fname = isset($_REQUEST["vFirstName"]) ? $_REQUEST["vFirstName"] : '';
    $Lname = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $email = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $email = strtolower($email);
    $phone_mobile = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $password = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $iGcmRegId = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $phoneCode = isset($_REQUEST["PhoneCode"]) ? $_REQUEST["PhoneCode"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $vInviteCode = isset($_REQUEST["vInviteCode"]) ? $_REQUEST["vInviteCode"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $user_type = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $eSignUpType = isset($_REQUEST["eSignUpType"]) ? $_REQUEST["eSignUpType"] : 'Normal';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    $vImageURL = isset($_REQUEST["vImageURL"]) ? $_REQUEST["vImageURL"] : '';
    if (SITE_TYPE == 'Demo') {
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $returnArr['Action'] = "0";
        $returnArr['message'] = strip_tags($languageLabelsArr["LBL_SIGNUP_DEMO_CONTENT"]);
        setDataResponse($returnArr);
    }
    if ($email == "" && $phone_mobile == "" && $fbid == "") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
    if ($vCurrency == '') {
        $vCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    if ($vLang == '') {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $phoneCode . "'";
    $CountryData = $obj->MySQLSelect($csql);
    $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    if ($eZeroAllowed == 'Yes') {
        $phone_mobile = $phone_mobile;
    }
    else {
        $first = substr($phone_mobile, 0, 1);
        if ($first == "0") {
            $phone_mobile = substr($phone_mobile, 1);
        }
    }
    if ($fbid != "") {
        if ($Lname == "" || $Lname == NULL) {
            $username = explode(" ", $Fname);
            if ($username[1] != "") {
                $Fname = $username[0];
                $Lname = $username[1];
            }
        }
    }
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $eRefType = "Rider";
        $Data_passenger['vPhoneCode'] = $phoneCode;
        $Data_passenger['vCurrencyPassenger'] = $vCurrency;
        $vImage = 'vImgName';
        $iMemberId = 'iUserId';
    }
    else {
        $tblname = "register_driver";
        $eRefType = "Driver";
        $Data_passenger['eDestinationMode'] = 'No';
        $Data_passenger['iDestinationCount'] = 0;
        $Data_passenger['tDestinationModifiedDate'] = date('Y-m-d H:i:s');
        $Data_passenger['vCode'] = $phoneCode;
        $Data_passenger['vCurrencyDriver'] = $vCurrency;
        $Data_passenger['iCompanyId'] = 1;
        $vImage = 'vImage';
        $iMemberId = 'iDriverId';
    }
    //$sql = "SELECT * FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$phone_mobile'!='',vPhone = '$phone_mobile',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    $check_passenger = $obj->MySQLSelect("SELECT * FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$fbid'!='',vFbId = '$fbid',0)");
    if (count($check_passenger) > 0) {
        $returnArr['Action'] = "0";
        if ($check_passenger[0]['eStatus'] == "Deleted") {
            $returnArr['message'] = "LBL_ACCOUNT_STATUS_DELETED_TXT";
            setDataResponse($returnArr);
        }
        if ($email == strtolower($check_passenger[0]['vEmail'])) {
            $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
            setDataResponse($returnArr);
        }
    }
    $Password_passenger = "";
    if ($password != "") {
        $Password_passenger = $generalobj->encrypt_bycrypt($password);
    }
    //Added By HJ On 31-12-2018 For Get LinkedIn Picture Data Start
    $socialData = array();
    if (isset($_REQUEST["socialData"])) {
        $socialData = (array)json_decode($_REQUEST["socialData"]);
    }
    if (isset($socialData['pictureUrls']) && $eSignUpType == 'LinkedIn') {
        $pictureUrls = $socialData['pictureUrls']->_total;
        if ($pictureUrls > 0) {
            $vImageURL = $socialData['pictureUrls']->values[0];
        }
        else {
            $vImageURL = $socialData['pictureUrl'];
        }
    }
    //Added By HJ On 31-12-2018 For Get LinkedIn Picture Data End
    $eSystem = "";
    if ($phone_mobile != "") {
        $checPhoneExist = $generalobj->checkMemberDataInfo($phone_mobile, "", $user_type, $CountryCode, "", $eSystem); //Added By HJ On 09-09-2019 For Chekc User Country and Mobile Number When Register
        
    }
    if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MOBILE_EXIST";
        setDataResponse($returnArr);
    }
    else if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 2) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
        setDataResponse($returnArr);
    }
    $check_inviteCode = "";
    $inviteSuccess = false;
    if ($vInviteCode != "") {
        $check_inviteCode = $generalobj->validationrefercode($vInviteCode);
        if ($check_inviteCode == "" || $check_inviteCode == "0" || $check_inviteCode == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_INVITE_CODE_INVALID";
            setDataResponse($returnArr);
        }
        else {
            $inviteRes = explode("|", $check_inviteCode);
            $Data_passenger['iRefUserId'] = $inviteRes[0];
            $Data_passenger['eRefType'] = $inviteRes[1];
            $inviteSuccess = true;
        }
    }
    $Data_passenger['vFbId'] = $fbid;
    $Data_passenger['vName'] = $Fname;
    $Data_passenger['vLastName'] = $Lname;
    $Data_passenger['vEmail'] = $email;
    $Data_passenger['vPhone'] = $phone_mobile;
    $Data_passenger['vPassword'] = $Password_passenger;
    $Data_passenger['iGcmRegId'] = $iGcmRegId;
    $Data_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
    $Data_passenger['vLang'] = $vLang;
    $Data_passenger['vCountry'] = $CountryCode;
    $Data_passenger['eDeviceType'] = $deviceType;
    $Data_passenger['vRefCode'] = $generalobj->ganaraterefercode($eRefType);
    $Data_passenger['dRefDate'] = @date('Y-m-d H:i:s');
    $Data_passenger['tRegistrationDate'] = @date('Y-m-d H:i:s');
    $Data_passenger['eSignUpType'] = $eSignUpType;
    if ($eSignUpType == "Facebook" || $eSignUpType == "Google") {
        $Data_passenger['eEmailVerified'] = "Yes";
    }
    $random = substr(md5(rand()) , 0, 7);
    $Data_passenger['tDeviceSessionId'] = session_id() . time() . $random;
    $Data_passenger['tSessionId'] = session_id() . time();
    if (SITE_TYPE == 'Demo') {
        $Data_passenger['eStatus'] = 'Active';
        //$Data_passenger['eEmailVerified'] = $Data_passenger['ePhoneVerified'] = 'Yes';
        //Added By HJ On 31-07-2019 For Enable Service At Location Feature Start
        if (strtolower($user_type) == 'driver') {
            $Data_passenger['eEnableServiceAtLocation'] = 'Yes';
        }
        //Added By HJ On 31-07-2019 For Enable Service At Location Feature End
        
    }
    $id = $obj->MySQLQueryPerform($tblname, $Data_passenger, 'insert');
    //Added By HJ On 31-07-2019 For Insert Default Corporate User When Add New User/Rider In Demo Copy Start
    if (strtolower($user_type) == "passenger" && SITE_TYPE == 'Demo') {
        insertCorporateUserProfile($id, $email); // Added By HJ On 31-07-2018 As Per Discuss With BM QA and CD Sir
        
    }
    //Added By HJ On 31-07-2019 For Insert Default Corporate User When Add New User/Rider In Demo Copy End
    ## Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
    if ($fbid != 0 || $fbid != "") {
        $UserImage = UploadUserImage($id, $user_type, $eSignUpType, $fbid, $vImageURL);
        if ($UserImage != "") {
            $where = " $iMemberId = '$id' ";
            $Data_update_image_member[$vImage] = $UserImage;
            $imageuploadid = $obj->MySQLQueryPerform($tblname, $Data_update_image_member, 'update', $where);
        }
    }
    ## Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
    $returnArr['changeLangCode'] = "Yes";
    $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $returnArr['vLanguageCode'] = $vLang;
    $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $vLang . "' ";
    $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
    $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
    $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
    $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
    $defLangValues = $obj->MySQLSelect($sql);
    $returnArr['LIST_LANGUAGES'] = $defLangValues;
    for ($i = 0;$i < count($defLangValues);$i++) {
        if ($defLangValues[$i]['eDefault'] == "Yes") {
            $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
        }
    }
    $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
    $defCurrencyValues = $obj->MySQLSelect($sql);
    $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
    for ($i = 0;$i < count($defCurrencyValues);$i++) {
        if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
            $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
        }
    }
    if (strtolower($user_type) == 'driver' && SITE_TYPE == 'Live') {
        if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
            $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` AND eType = 'UberX'";
            $result = $obj->MySQLSelect($query);
            $Drive_vehicle['iDriverId'] = $id;
            $Drive_vehicle['iCompanyId'] = "1";
            $Drive_vehicle['iMakeId'] = "3";
            $Drive_vehicle['iModelId'] = "1";
            $Drive_vehicle['iYear'] = Date('Y');
            $Drive_vehicle['vLicencePlate'] = "My Services";
            $Drive_vehicle['eStatus'] = "Active";
            $Drive_vehicle['eCarX'] = "Yes";
            $Drive_vehicle['eCarGo'] = "Yes";
            $Drive_vehicle['eType'] = "UberX";
            $Drive_vehicle['vCarType'] = "";
            $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
            if ($APP_TYPE == 'UberX') {
                $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                $obj->sql_query($sql);
            }
        }
    }
    if (strtolower($user_type) == 'driver' && SITE_TYPE == 'Demo') {
        //Added By HJ On 27-07-2019 For Add Money Into Wallet When Register Driver In Demo Mode Start - Discuss With CD and KS Sir
        $tDescription = '#LBL_AMOUNT_CREDIT_BY_USER#';
        $ePaymentStatus = 'Unsettelled';
        $dDate = Date('Y-m-d H:i:s');
        $generalobj->InsertIntoUserWallet($id, "Driver", 500, 'Credit', 0, "Deposit", $tDescription, $ePaymentStatus, $dDate);
        //Added By HJ On 27-07-2019 For Add Money Into Wallet When Register Driver In Demo Mode End - Discuss With CD and KS Sir
        $Drive_vehicle['iDriverId'] = $id;
        $Drive_vehicle['iCompanyId'] = "1";
        $Drive_vehicle['iMakeId'] = "3";
        $Drive_vehicle['iModelId'] = "1";
        $Drive_vehicle['iYear'] = Date('Y');
        $Drive_vehicle['eStatus'] = "Active";
        $Drive_vehicle['eCarX'] = "Yes";
        $Drive_vehicle['eCarGo'] = "Yes";
        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {
            $Drive_vehicle['vLicencePlate'] = "My Services";
            $Drive_vehicle['eType'] = "UberX";
            $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'UberX'";
            $result = $obj->MySQLSelect($query);
            $Drive_vehicle['vCarType'] = $result[0]['countId'];
            $Drive_vehicle['vRentalCarType'] = $result[0]['countId'];
            $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
            if ($APP_TYPE == 'UberX') {
                $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                $obj->sql_query($sql);
            }
            $days = array(
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday'
            );
            foreach ($days as $value) {
                $data_avilability['iDriverId'] = $id;
                $data_avilability['vDay'] = $value;
                $data_avilability['vAvailableTimes'] = '08-09,09-10,10-11,11-12,12-13,13-14,14-15,15-16,16-17,17-18,18-19,19-20,20-21,21-22';
                $data_avilability['dAddedDate'] = @date('Y-m-d H:i:s');
                $data_avilability['eStatus'] = 'Active';
                $data_avilability_add = $obj->MySQLQueryPerform('driver_manage_timing', $data_avilability, 'insert');
            }
            if ($APP_TYPE == 'Ride-Delivery-UberX') {
                $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'Ride' OR eType = 'Deliver'";
                //Added By HJ On 13-08-2019 For Get Deliverall Vehicle Id Start Discuss With KS Sir
                $getDeliverAll = $obj->MySQLSelect("SELECT iVehicleTypeId FROM `vehicle_type` WHERE eType = 'DeliverAll' ORDER BY iVehicleTypeId ASC LIMIT 0,1");
                //Added By HJ On 13-08-2019 For Get Deliverall Vehicle Id End Discuss With KS Sir
                $result_ride = $obj->MySQLSelect($query);
                if (isset($getDeliverAll[0]['iVehicleTypeId']) && $getDeliverAll[0]['iVehicleTypeId'] > 0) {
                    $deliverAll = $getDeliverAll[0]['iVehicleTypeId'];
                    $result_ride[0]['countId'] .= "," . $deliverAll;
                }
                $Drive_vehicle_ride['iDriverId'] = $id;
                $Drive_vehicle_ride['iCompanyId'] = "1";
                $Drive_vehicle_ride['iYear'] = "2014";
                $Drive_vehicle_ride['vLicencePlate'] = "CK201";
                $Drive_vehicle_ride['eStatus'] = "Active";
                $Drive_vehicle_ride['eCarX'] = "Yes";
                $Drive_vehicle_ride['eCarGo'] = "Yes";
                $Drive_vehicle_ride['eType'] = "Ride";
                $Drive_vehicle_delivery = $Drive_vehicle_ride;
                $Drive_vehicle_ride['iMakeId'] = "1";
                $Drive_vehicle_ride['iModelId'] = "1";
                $Drive_vehicle_ride['vCarType'] = $result_ride[0]['countId'];
                $Drive_vehicle_ride['vRentalCarType'] = $result_ride[0]['countId'];
                $iDriver_Ride_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle_ride, 'insert');
                $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_Ride_VehicleId . "' WHERE iDriverId='" . $id . "'";
                $obj->sql_query($sql);
                $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'Ride' OR eType = 'Deliver'";
                $result_delivery = $obj->MySQLSelect($query);
                //Added By HJ On 13-08-2019 For Get Deliverall Vehicle Id Start Discuss With KS Sir
                //$getDeliverAll = $obj->MySQLSelect("SELECT iVehicleTypeId FROM `vehicle_type` WHERE eType = 'DeliverAll' ORDER BY iVehicleTypeId ASC LIMIT 0,1");
                if (isset($getDeliverAll[0]['iVehicleTypeId']) && $getDeliverAll[0]['iVehicleTypeId'] > 0) {
                    $deliverAll = $getDeliverAll[0]['iVehicleTypeId'];
                    $result_delivery[0]['countId'] .= "," . $deliverAll;
                }
                //Added By HJ On 13-08-2019 For Get Deliverall Vehicle Id End Discuss With KS Sir
                $Drive_vehicle_delivery['iMakeId'] = "5";
                $Drive_vehicle_delivery['iModelId'] = "18";
                $Drive_vehicle_delivery['eType'] = "Delivery";
                $Drive_vehicle_delivery['vCarType'] = $result_delivery[0]['countId'];
                $Drive_vehicle_delivery['vRentalCarType'] = $result_delivery[0]['countId'];
                $iDriver_Delivery_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle_delivery, 'insert');
            }
        }
        else {
            $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType != 'DeliverAll'";
            $result = $obj->MySQLSelect($query);
            //Added By HJ On 13-08-2019 For Get Deliverall Vehicle Id Start Discuss With KS Sir
            $getDeliverAll = $obj->MySQLSelect("SELECT iVehicleTypeId FROM `vehicle_type` WHERE eType = 'DeliverAll' ORDER BY iVehicleTypeId ASC LIMIT 0,1");
            if (isset($getDeliverAll[0]['iVehicleTypeId']) && $getDeliverAll[0]['iVehicleTypeId'] > 0) {
                $deliverAll = $getDeliverAll[0]['iVehicleTypeId'];
                $result[0]['countId'] .= "," . $deliverAll;
            }
            //Added By HJ On 13-08-2019 For Get Deliverall Vehicle Id End Discuss With KS Sir
            $Drive_vehicle['iDriverId'] = $id;
            $Drive_vehicle['iCompanyId'] = "1";
            $Drive_vehicle['iMakeId'] = "5";
            $Drive_vehicle['iModelId'] = "18";
            $Drive_vehicle['iYear'] = "2014";
            $Drive_vehicle['vLicencePlate'] = "CK201";
            $Drive_vehicle['eStatus'] = "Active";
            $Drive_vehicle['eCarX'] = "Yes";
            $Drive_vehicle['eCarGo'] = "Yes";
            //$Drive_vehicle['eAddedDeliverVehicle'] = "Yes";
            $Drive_vehicle['vCarType'] = $result[0]['countId'];
            $Drive_vehicle['vRentalCarType'] = $result[0]['countId'];
            $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
            $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
            $obj->sql_query($sql);
        }
    }
    if ($id > 0) {
        if ($inviteSuccess == true) {
            $eFor = "Referrer";
            $tDescription = "Referral amount credited";
            $dDate = Date('Y-m-d H:i:s');
            $ePaymentStatus = "Unsettelled";
        }
        /* new added */
        $returnArr['Action'] = "1";
        if ($user_type == "Passenger") {
            $returnArr['message'] = getPassengerDetailInfo($id, "", "");
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($id);
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
        }
        $maildata['EMAIL'] = $email;
        $maildata['NAME'] = $Fname;
        //$maildata['PASSWORD'] = "Password: " . $password; //Commented By HJ On 11-01-2019 For Hide Password As Per Discuss With QA BM
        $maildata['SOCIALNOTES'] = '';
        if ($user_type == "Passenger") {
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
        }
        else {
            $generalobj->send_email_user("DRIVER_REGISTRATION_USER", $maildata);
            $generalobj->send_email_user("DRIVER_REGISTRATION_ADMIN", $maildata);
        }
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}
######################### isUserExist #############################
if ($type == "isUserExist") {
    $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '';
    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';
    $sql = "SELECT vEmail,vPhone,vFbId FROM register_user WHERE 1=1 AND IF('$Emid'!='',vEmail = '$Emid',0) OR IF('$Phone'!='',vPhone = '$Phone',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $returnArr['Action'] = "0";
        if ($Emid == $Data[0]['vEmail']) {
            $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
        }
        else if ($Phone == $Data[0]['vPhone']) {
            $returnArr['message'] = "LBL_MOBILE_EXIST";
        }
        else {
            $returnArr['message'] = "LBL_FACEBOOK_ACC_EXIST";
        }
    }
    else {
        $returnArr['Action'] = "1";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "signIn") {
    $Emid = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $Emid = strtolower($Emid);
    $Password_user = $userPassword = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $DeviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    $vCountry = isset($_REQUEST["vUserDeviceCountry"]) ? $_REQUEST["vUserDeviceCountry"] : '';
    if (SITE_TYPE == "Demo") {
        $tablename = ($UserType == 'Passenger') ? "register_user" : "register_driver";
        $iMemberId = ($UserType == 'Passenger') ? "iUserId" : "iDriverId";
        $iUserId = ($UserType == 'Passenger') ? "36" : "31";
        $Member_Currency = ($UserType == 'Passenger') ? "vCurrencyPassenger" : "vCurrencyDriver";
        $Member_Image = ($UserType == 'Passenger') ? "vImgName" : "vImage";
        $Data_Update_Member['vName'] = ($UserType == 'Passenger') ? "MAC" : "Mark";
        $Data_Update_Member['vLastName'] = ($UserType == 'Passenger') ? "ANDREW" : "Bruno";
        $Data_Update_Member['vEmail'] = ($UserType == 'Passenger') ? "rider@gmail.com" : "driver@gmail.com";
        $Password_User = $generalobj->encrypt_bycrypt("123456");
        $Data_Update_Member['vPassword'] = $Password_User;
        $Data_Update_Member['vCountry'] = ($UserType == 'Passenger') ? "US" : "US";
        $Data_Update_Member['vLang'] = ($UserType == 'Passenger') ? "EN" : "EN";
        $Data_Update_Member['eStatus'] = ($UserType == 'Passenger') ? "Active" : "active";
        $Data_Update_Member[$Member_Currency] = ($UserType == 'Passenger') ? "USD" : "USD";
        $Data_Update_Member[$Member_Image] = ($UserType == 'Passenger') ? "1504878922_81109.jpg" : "1505208397_54463.jpg";
        $where = " $iMemberId = '" . $iUserId . "'";
        $Update_Member_id = $obj->MySQLQueryPerform($tablename, $Data_Update_Member, 'update', $where);
    }
    //echo $Emid."==".$userPassword."==".$UserType;die;
    $passUserType = $UserType;
    if (strtoupper($UserType) == "KIOSK" || strtoupper($UserType) == "HOTEL") {
        $passUserType = "ADMIN";
    }
    $eSystem = "";
    $checkValid = $generalobj->checkMemberDataInfo($Emid, $userPassword, $passUserType, "", "", $eSystem); //Added By HJ On 09-09-2019 For Chekc User Country and Mobile Number When Login
    if (isset($checkValid['status']) && $checkValid['status'] == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WRONG_DETAIL";
        setDataResponse($returnArr);
    }
    else if (isset($checkValid['status']) && $checkValid['status'] == 2) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
        setDataResponse($returnArr);
    }
    $primaryField = "iHotelId";
    $primaryField1 = "h.iHotelId";
    if ($UserType == "Passenger") {
        $primaryField = "iUserId";
        $primaryField1 = "iUserId";
    }
    else if ($UserType == "Driver") {
        $primaryField = "iDriverId";
        $primaryField1 = "rd.iDriverId";
    }
    $whereCondition = "";
    if (isset($checkValid['USER_DATA'][$primaryField]) && $checkValid['USER_DATA'][$primaryField] > 0) {
        $whereCondition = " AND $primaryField1='" . $checkValid['USER_DATA'][$primaryField] . "'";
    }
    if ($UserType == "Passenger") {
        $sql = "SELECT iUserId,eStatus,vLang,vTripStatus,vLang,vPassword FROM `register_user` WHERE vEmail='$Emid' OR vPhone = '$Emid' $whereCondition";
        $Data = $obj->MySQLSelect($sql);
        $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
        $iCabRequestId = $Data_cabrequest[0]['iCabRequestId'];
        $eStatus_cab = $Data_cabrequest[0]['eStatus'];
        if (count($Data) > 0) {
            # Check For Valid password #
            $hash = $Data[0]['vPassword'];
            $checkValidPass = $generalobj->check_password($Password_user, $hash);
            if ($checkValidPass == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WRONG_DETAIL";
                setDataResponse($returnArr);
            }
            # Check For Valid password #
            if ($Data[0]['eStatus'] == "Active") {
                $iUserId_passenger = $Data[0]['iUserId'];
                $where = " iUserId = '$iUserId_passenger' ";
                if ($Data[0]['vLang'] == "" && $vLang == "") {
                    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                    $Data_update_passenger['vLang'] = $vLang;
                }
                if ($vLang != "") {
                    $Data_update_passenger['vLang'] = $vLang;
                    $Data[0]['vLang'] = $vLang;
                }
                if ($vCurrency != "") {
                    $Data_update_passenger['vCurrencyPassenger'] = $vCurrency;
                }
                if ($GCMID != '') {
                    $Data_update_passenger['iGcmRegId'] = $GCMID;
                    $Data_update_passenger['eDeviceType'] = $DeviceType;
                    $Data_update_passenger['tSessionId'] = session_id() . time();
                    $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                    if (SITE_TYPE == "Demo") {
                        $Data_update_passenger['tRegistrationDate'] = date('Y-m-d H:i:s');
                    }
                    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                }
                if ($eStatus_cab == "Requesting") {
                    $where1 = " iCabRequestId = '$iCabRequestId' ";
                    $Data_update_cab_now['eStatus'] = "Cancelled";
                    $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where1);
                }
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $iServiceId);
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defLangValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0;$i < count($defLangValues);$i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
                $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active'  ORDER BY iDispOrder ASC ";
                $defCurrencyValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
                for ($i = 0;$i < count($defCurrencyValues);$i++) {
                    if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                    }
                }
                $returnArr['Action'] = "1";
                $returnArr['message'] = getPassengerDetailInfo($Data[0]['iUserId'], '', "");
                $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
                $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues; //put bc naresh wants it in message..
                $generalobj->createUserLog($UserType, "No", $Data[0]['iUserId'], "Android");
                setDataResponse($returnArr);
            }
            else {
                $returnArr['Action'] = "0";
                if ($Data[0]['eStatus'] != "Deleted") {
                    $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }
                else {
                    $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }
                setDataResponse($returnArr);
            }
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            setDataResponse($returnArr);
        }
    }
    else if ($UserType == "Driver") {
        $sql = "SELECT rd.iDriverId,rd.eStatus,rd.vLang,rd.vPassword,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE ( rd.vEmail='$Emid' OR rd.vPhone = '$Emid' ) AND cmp.iCompanyId=rd.iCompanyId $whereCondition";
        $Data = $obj->MySQLSelect($sql);
        if (count($Data) > 0) {
            # Check For Valid password #
            $hash = $Data[0]['vPassword'];
            $checkValidPass = $generalobj->check_password($Password_user, $hash);
            if ($checkValidPass == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WRONG_DETAIL";
                setDataResponse($returnArr);
            }
            # Check For Valid password #
            if ($Data[0]['eStatus'] != "Deleted") {
                if ($GCMID != '') {
                    $iDriverId_driver = $Data[0]['iDriverId'];
                    $where = " iDriverId = '$iDriverId_driver' ";
                    if ($Data[0]['vLang'] == "" && $vLang == "") {
                        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                        $Data_update_driver['vLang'] = $vLang;
                    }
                    if ($vLang != "") {
                        $Data_update_driver['vLang'] = $vLang;
                        $Data[0]['vLang'] = $vLang;
                    }
                    if ($vCurrency != "") {
                        $Data_update_driver['vCurrencyDriver'] = $vCurrency;
                    }
                    $Data_update_driver['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                    $Data_update_driver['tSessionId'] = session_id() . time();
                    $Data_update_driver['iGcmRegId'] = $GCMID;
                    $Data_update_driver['eDeviceType'] = $DeviceType;
                    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
                }
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $iServiceId);
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defLangValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0;$i < count($defLangValues);$i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
                $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defCurrencyValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
                for ($i = 0;$i < count($defCurrencyValues);$i++) {
                    if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                    }
                }
                $returnArr['Action'] = "1";
                $returnArr['message'] = getDriverDetailInfo($Data[0]['iDriverId'], 1);
                $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
                $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues; //put bc naresh wants it in message..
                $generalobj->createUserLog($UserType, "No", $Data[0]['iDriverId'], "Android");
                setDataResponse($returnArr);
            }
            else {
                $returnArr['Action'] = "0";
                if ($Data[0]['eStatus'] != "Deleted") {
                    $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }
                else {
                    $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }
                setDataResponse($returnArr);
            }
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            setDataResponse($returnArr);
        }
    } else {
        // kiosk changes
        $sql = "SELECT h.iHotelId,a.eStatus,h.vLang,h.vImgName,a.vPassword FROM `hotel` as h LEFT JOIN administrators as a on a.iAdminId = h.iAdminId WHERE a.vEmail='$Emid' OR a.vContactNo = '$Emid' $whereCondition";
        $Data = $obj->MySQLSelect($sql);
        if (count($Data) > 0) {
            # Check For Valid password #
            $hash = $Data[0]['vPassword'];
            $checkValidPass = $generalobj->check_password($Password_user, $hash);
            if ($checkValidPass == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WRONG_DETAIL";
                setDataResponse($returnArr);
            }
            # Check For Valid password #
            if ($Data[0]['eStatus'] == "Active") {
                $iUserId_passenger = $Data[0]['iHotelId'];
                $where = " iHotelId = '$iUserId_passenger' ";
                if ($Data[0]['vLang'] == "" && $vLang == "") {
                    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                    $Data_update_passenger['vLang'] = $vLang;
                }
                if ($vLang != "") {
                    $Data_update_passenger['vLang'] = $vLang;
                    $Data[0]['vLang'] = $vLang;
                }
                if ($vCurrency != "") {
                    $Data_update_passenger['vCurrencyPassenger'] = $vCurrency;
                }
                if ($GCMID != '') {
                    $Data_update_passenger['iGcmRegId'] = $GCMID;
                    $Data_update_passenger['eDeviceType'] = $DeviceType;
                    $Data_update_passenger['tSessionId'] = session_id() . time();
                    $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                    if (SITE_TYPE == "Demo") {
                        $Data_update_passenger['tRegistrationDate'] = date('Y-m-d H:i:s');
                    }
                    $id = $obj->MySQLQueryPerform("hotel", $Data_update_passenger, 'update', $where);
                }
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defLangValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0;$i < count($defLangValues);$i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
                $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defCurrencyValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
                for ($i = 0;$i < count($defCurrencyValues);$i++) {
                    if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                    }
                }
                $returnArr['Action'] = "1";
                $returnArr['message'] = getHotelDetailInfo($Data[0]['iHotelId'], '');
                $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
                $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues; //put bc naresh wants it in message..
                $generalobj->createUserLog($UserType, "No", $Data[0]['iUserId'], "Android");
                setDataResponse($returnArr);
            }
            else {
                $returnArr['Action'] = "0";
                if ($Data[0]['eStatus'] != "Deleted") {
                    $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }
                else {
                    $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }
                setDataResponse($returnArr);
            }
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            setDataResponse($returnArr);
        }
    }
}
###########################################################################
if ($type == "getDetail") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLangCode = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $LiveTripId = isset($_REQUEST["LiveTripId"]) ? $_REQUEST["LiveTripId"] : '';
    $liveTrackingUrl = "";
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $liveTrackingUrl = getTrackingUrl($LiveTripId);
    }
    if ($UserType == "Passenger") {
        //Added By HJ On 09-06-2020 For Optimization Start
        $tblName = "register_user";
        if(isset($userDetailsArr[$tblName."_".$iUserId]) && count($userDetailsArr[$tblName."_".$iUserId]) > 0){
            $Data = $userDetailsArr[$tblName."_".$iUserId];
        }else{
            $sql = "SELECT *,iUserId as iMemberId FROM ".$tblName." WHERE iUserId='$iUserId'";
            $Data = $obj->MySQLSelect($sql);
            $userDetailsArr[$tblName."_".$iUserId] = $row;
        }  
        //Added By HJ On 09-06-2020 For Optimization End
        
        $sql_cab = "SELECT iCabRequestId,eStatus FROM cab_request_now WHERE iUserId = '" . $iUserId . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cab = $obj->MySQLSelect($sql_cab);
        $iCabRequestId = $Data_cab[0]['iCabRequestId'];
        $eStatus_cab = $Data_cab[0]['eStatus'];
        if (count($Data) > 0) {
            $iGCMregID = $Data[0]['iGcmRegId'];
            $vTripStatus = $Data[0]['vTripStatus'];
            $currencttripid = $Data[0]['iTripId'];
            // added by SK on 06-01-2020  For solve 662 (issue to be fixed sheet)
            if (strtoupper(PACKAGE_TYPE) == "SHARK" && empty($LiveTripId)) {
                $liveTrackingUrl = getTrackingUrl($currencttripid);
            }
            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['eStatus'] = "";
                $returnArr['message'] = "SESSION_OUT";
                setDataResponse($returnArr);
            }
            if ($Data[0]['vLang'] == "") {
                $where = " iUserId = '$iUserId' ";
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
                if (!empty($vSystemDefaultLangCode)) {
                    $vLang = $vSystemDefaultLangCode;
                } else {
                    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                }
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
                $Data_update_passenger['vLang'] = $vLang;
                $updateid = $obj->MySQLQueryPerform($tblName, $Data_update_passenger, 'update', $where);
                $Data[0]['vLang'] = $vLang;
            }
            if ($eStatus_cab == "Requesting") {
                $where = " iCabRequestId = '$iCabRequestId' ";
                $Data_update_cab_now['eStatus'] = "Cancelled";
                $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where);
            }
            $returnArr['changeLangCode'] = "No";
            if (($vLangCode != $Data[0]['vLang']) || $Data[0]['eChangeLang'] == "Yes") {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $iServiceId);
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
                if(isset($languageAssociateArr[$Data[0]['vLang']])){
                    $Data_checkLangCode = array();
                    $Data_checkLangCode[] = $languageAssociateArr[$Data[0]['vLang']];
                }else{
                    $Data_checkLangCode = $obj->MySQLSelect("SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ");
                }
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $where = " iUserId = '$iUserId' ";
                $Data_update_passenger_lang['eChangeLang'] = "No";
                $updateLangid = $obj->MySQLQueryPerform($tblName, $Data_update_passenger_lang, 'update', $where);
                $Data[0]['eChangeLang'] = "No";
                //echo "<pre>";print_r($Data_ALL_langArr);die;
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
                if(count($Data_ALL_langArr) > 0){
                    $defLangValues = array();
                    for($g=0;$g<count($Data_ALL_langArr);$g++){
                        if(strtoupper($Data_ALL_langArr[$g]['eStatus']) == "ACTIVE"){
                            $defLangValues[] = $Data_ALL_langArr[$g];
                        }
                    }
                }else{
                    $defLangValues = $obj->MySQLSelect("SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC");
                }
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0;$i < count($defLangValues);$i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
            }
            if(count($Data_ALL_currency_Arr) > 0){
                $defCurrencyValues = array();
                for($c=0;$c<count($Data_ALL_currency_Arr);$c++){
                    if(strtoupper($Data_ALL_currency_Arr[$c]['eStatus']) == "ACTIVE"){
                        $defCurrencyValues[] = $Data_ALL_currency_Arr[$c];
                    }
                }
            }else{
                $defCurrencyValues = $obj->MySQLSelect("SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ");
            }
            $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
            for ($i = 0;$i < count($defCurrencyValues);$i++) {
                if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                }
            }
            //added by SP on 12-09-2019 for ufx service available or not start
            //$UFX_SERVICE_AVAILABLE = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
            $UFX_SERVICE_AVAILABLE = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query
            $returnArr['UFX_SERVICE_AVAILABLE'] = $UFX_SERVICE_AVAILABLE;
            //added by SP on 12-09-2019 for ufx service available or not end
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($iUserId, '', $LiveTripId);
            $returnArr['message']['UFX_SERVICE_AVAILABLE'] = $UFX_SERVICE_AVAILABLE; // Added By HJ On 17-02-2020 As Per Discussion Between GP and KS Sir
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']); // Added By HJ On 06-08-2019 For Get Custome Sound Notification File Name
            $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues;
            // Tracking Url
            if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                $returnArr['message']['liveTrackingUrl'] = $liveTrackingUrl; // Added By HJ On 01-01-2019 For Live Tracking URL Share
                
            }
            else {
                $returnArr['message']['liveTrackingUrl'] = '';
            }
            $generalobj->createUserLog($UserType, "Yes", $iUserId, "Android");
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = "";
            $returnArr['message'] = "SESSION_OUT";
        }
        setDataResponse($returnArr);
    } else if ($UserType == "Driver") {
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            getDriverPoolTrips($iUserId);
        }
        //Added By HJ On 17-06-2020 For Optimization register_driver Table Query Start
        $tblName = "register_driver";
        if(isset($userDetailsArr[$tblName."_".$iUserId]) && count($userDetailsArr[$tblName."_".$iUserId]) > 0){
            $Data = $userDetailsArr[$tblName."_".$iUserId];
        }else{
            $Data = $obj->MySQLSelect("SELECT *,iDriverId as iMemberId FROM register_driver WHERE iDriverId='" . $driverId . "'");
            $userDetailsArr[$tblName."_".$iUserId] = $Data;
        }
        //Added By HJ On 17-06-2020 For Optimization register_driver Table Query End
        //$Data = $obj->MySQLSelect("SELECT iGcmRegId,vLang,eChangeLang,iTripId FROM `register_driver` WHERE iDriverId='$iUserId'"); //Commented By HJ On 17-06-2020 For Optimize register_driver Table Query
        if (count($Data) > 0) {
            $iGCMregID = $Data[0]['iGcmRegId'];
            $currencttripid = $Data[0]['iTripId'];
            $currencttripserviceid = "";
            $currencttriptype = "General";
            if ($currencttripid > 0) {
                //Added By HJ On 17-06-2020 For Optimize trips Table Query Start
                if(isset($tripDetailsArr["trips_".$currencttripid])){
                    $TripData = $tripDetailsArr["trips_".$currencttripid];
                }else{
                    $TripData = get_value("trips", '*', 'iTripId', $currencttripid);
                    $tripDetailsArr["trips_".$currencttripid] = $tripData;
                }
                //Added By HJ On 17-06-2020 For Optimize trips Table Query End
                $currencttripserviceid = $TripData[0]['iServiceId'];
                $currencttriptype = $TripData[0]['eSystem'];
            }
            if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                $liveTrackingUrl = getTrackingUrl($currencttripid);
            }
            if ($Data[0]['vLang'] == "") {
                $where = " iDriverId = '$iUserId' ";
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
                if (!empty($vSystemDefaultLangCode)) {
                    $vLang = $vSystemDefaultLangCode;
                } else {
                    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                }
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
                $Data_update_driver['vLang'] = $vLang;
                $updateid = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
            }
            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['eStatus'] = "";
                $returnArr['message'] = "SESSION_OUT";
                setDataResponse($returnArr);
            }
            $returnArr['changeLangCode'] = "No";
            if (($vLangCode != $Data[0]['vLang']) || $Data[0]['eChangeLang'] == "Yes" || $currencttriptype == "DeliverAll") {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $currencttripserviceid, $iServiceId);
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
                if(isset($languageAssociateArr[$Data[0]['vLang']])){
                    $Data_checkLangCode = array();
                    $Data_checkLangCode[] = $languageAssociateArr[$Data[0]['vLang']];
                }else{
                    $Data_checkLangCode = $obj->MySQLSelect("SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ");
                }
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
                
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $where = " iDriverId = '$iUserId' ";
                $Data_update_passenger_lang['eChangeLang'] = "No";
                $updateLangid = $obj->MySQLQueryPerform("register_driver", $Data_update_passenger_lang, 'update', $where);
                $Data[0]['eChangeLang'] = "No";
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
                if(count($Data_ALL_langArr) > 0){
                    $defLangValues = array();
                    for($g=0;$g<count($Data_ALL_langArr);$g++){
                        if(strtoupper($Data_ALL_langArr[$g]['eStatus']) == "ACTIVE"){
                            $defLangValues[] = $Data_ALL_langArr[$g];
                        }
                    }
                }else{
                    $defLangValues = $obj->MySQLSelect("SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC");
                }
                //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0;$i < count($defLangValues);$i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
            }
            if(count($Data_ALL_currency_Arr) > 0){
                $defCurrencyValues = array();
                for($c=0;$c<count($Data_ALL_currency_Arr);$c++){
                    if(strtoupper($Data_ALL_currency_Arr[$c]['eStatus']) == "ACTIVE"){
                        $defCurrencyValues[] = $Data_ALL_currency_Arr[$c];
                    }
                }
            }else{
                $defCurrencyValues = $obj->MySQLSelect("SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ");
            }
            $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
            for ($i = 0;$i < count($defCurrencyValues);$i++) {
                if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                }
            }
            $returnArr['Action'] = "1";
            // $returnArr['message'] = getDriverDetailInfo($iUserId);
            //added by SP on 12-09-2019 for ufx service available or not start
            //$UFX_SERVICE_AVAILABLE = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
            $UFX_SERVICE_AVAILABLE = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query
            $returnArr['UFX_SERVICE_AVAILABLE'] = $UFX_SERVICE_AVAILABLE;
            //added by SP on 12-09-2019 for ufx service available or not end
            $returnArr['message'] = getDriverDetailInfo($iUserId);
            $returnArr['message']['UFX_SERVICE_AVAILABLE'] = $UFX_SERVICE_AVAILABLE; // Added By HJ On 17-02-2020 As Per Discussion Between GP and KS Sir
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']); // Added By HJ On 06-08-2019 For Get Custome Sound Notification File Name
            $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues; // dont remove this code..i will put it 3rd time...problem in webservice
            if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                $returnArr['message']['liveTrackingUrl'] = $liveTrackingUrl; // Added By HJ On 01-01-2019 For Live Tracking URL Share
                
            } else {
                $returnArr['message']['liveTrackingUrl'] = "";
            }
            $generalobj->createUserLog($UserType, "Yes", $iUserId, "Android");
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = "";
            $returnArr['message'] = "SESSION_OUT";
        }
        //echo "<pre>";print_r($returnArr);die;
        setDataResponse($returnArr);
    }
    else {
        // kiosk changes
        $sql = "SELECT iGcmRegId,vImgName,vLang,eChangeLang,vVehicleTypeImg FROM `hotel` WHERE iHotelId='$iUserId'";
        $Data = $obj->MySQLSelect($sql);
        if (count($Data) > 0) {
            $iGCMregID = $Data[0]['iGcmRegId'];
            $vTripStatus = $Data[0]['vTripStatus'];
            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['eStatus'] = "";
                $returnArr['message'] = "SESSION_OUT";
                setDataResponse($returnArr);
            }
            if ($Data[0]['vLang'] == "") {
                $where = " iHotelId = '$iUserId' ";
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $Data_update_passenger['vLang'] = $vLang;
                $updateid = $obj->MySQLQueryPerform("hotel", $Data_update_passenger, 'update', $where);
                $Data[0]['vLang'] = $vLang;
            }
            $returnArr['changeLangCode'] = "No";
            if (($vLangCode != $Data[0]['vLang']) || $Data[0]['eChangeLang'] == "Yes") {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1");
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $where = " iHotelId = '$iUserId' ";
                $Data_update_passenger_lang['eChangeLang'] = "No";
                $updateLangid = $obj->MySQLQueryPerform("hotel", $Data_update_passenger_lang, 'update', $where);
                $Data[0]['eChangeLang'] = "No";
                $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ";
                $defLangValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0;$i < count($defLangValues);$i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
                $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ";
                $defCurrencyValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
                for ($i = 0;$i < count($defCurrencyValues);$i++) {
                    if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                    }
                }
            }
            $returnArr['Action'] = "1";
            $returnArr['message'] = getHotelDetailInfo($iUserId, '');
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']); // Added By HJ On 06-08-2019 For Get Custome Sound Notification File Name
            $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues;
            if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                $returnArr['message']['liveTrackingUrl'] = $liveTrackingUrl; // Added By HJ On 01-01-2019 For Live Tracking URL Share
                
            }
            else {
                $returnArr['message']['liveTrackingUrl'] = "";
            }
            $generalobj->createUserLog($UserType, "Yes", $iUserId, "Android");
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = "";
            $returnArr['message'] = "SESSION_OUT";
        }
        setDataResponse($returnArr);
    }
}
###########################################################################
if ($type == "LoginWithFB") {
    $fbid = isset($_REQUEST["iFBId"]) ? $_REQUEST["iFBId"] : '';
    $Fname = isset($_REQUEST["vFirstName"]) ? $_REQUEST["vFirstName"] : '';
    $Lname = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $email = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $vDeviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $eLoginType = isset($_REQUEST["eLoginType"]) ? $_REQUEST["eLoginType"] : 'Facebook';
    $user_type = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    $vImageURL = isset($_REQUEST["vImageURL"]) ? $_REQUEST["vImageURL"] : '';
    if ($fbid == "") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
    //$DeviceType = "Android";
    $DeviceType = $vDeviceType;
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $iMemberId = 'iUserId';
        $vCurrencyMember = "vCurrencyPassenger";
        $vImageFiled = 'vImgName';
        // $vcurrency = 'vCurrencyPassenger';
        
    }
    else {
        $tblname = "register_driver";
        $iMemberId = 'iDriverId';
        $vCurrencyMember = "vCurrencyDriver";
        $vImageFiled = 'vImage';
        // $vcurrency = 'vCurrencyDriver';
        
    }
    if ($user_type == "Passenger") {
        $sql = "SELECT iUserId as iUserId,eStatus,vFbId,vLang,vTripStatus,eSignUpType,vImgName as vImage FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    }
    else {
        $sql = "SELECT iDriverId as iUserId,eStatus,vFbId,vLang,vTripStatus,eSignUpType,vImage as vImage FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    }
    $Data = $obj->MySQLSelect($sql);
    if (isset($Data[0]['iUserId']) && $Data[0]['iUserId'] > 0 && $user_type == "Passenger") {
        /* $iCabRequestId= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$Data[0]['iUserId'],'','true');
        
          $eStatus_cab= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId,'','true'); */
        $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
        $eStatus_cab = "Active";
        $iCabRequestId = 0;
        if (count($Data_cabrequest) > 0) {
            $iCabRequestId = $Data_cabrequest[0]['iCabRequestId'];
            $eStatus_cab = $Data_cabrequest[0]['eStatus'];
        }
    }
    //Added By HJ On 31-12-2018 For Get LinkedIn Picture Data Start
    $socialData = array();
    if (isset($_REQUEST["socialData"])) {
        $socialData = (array)json_decode($_REQUEST["socialData"]);
        //$socialData = (array) json_decode('{"emailAddress":"shaarahicks@gmail.com","firstName":"Shaara","formattedName":"Shaara+Hicks","headline":"Student+at+Kadi+Sarva+Vishwavidyalaya,+Gandihnagar","id":"-Q3dtxeKkj","lastName":"Hicks","location":{"country":{"code":"in"},"name":"Ahmedabad+Area,+India"},"numConnections":0,"pictureUrl":"https:\/\/media.licdn.com\/dms\/image\/C5603AQEVyYuU1ulIsw\/profile-displayphoto-shrink_100_100\/0?e=1551916800&v=beta&t=Ked9RfczVixH4I8rKzcHmHu2BX_YRKgsGlaY6p-CUZc","pictureUrls":{"_total":1,"values":["https:\/\/media.licdn.com\/dms\/image\/C5604AQEV0lBvLEQvLg\/profile-originalphoto-shrink_450_600\/0?e=1551916800&v=beta&t=vtEjixa-2fQqZbXl2F5ONGEfpLlAKjrinIZUKwdsQa0"]},"publicProfileUrl":"http:\/\/www.linkedin.com\/in\/shaara-hicks-9a20a0177"}');
        
    }
    if (isset($socialData['pictureUrls']) && $eLoginType == 'LinkedIn') {
        $pictureUrls = $socialData['pictureUrls']->_total;
        if ($pictureUrls > 0) {
            $vImageURL = $socialData['pictureUrls']->values[0];
        }
        else {
            $vImageURL = $socialData['pictureUrl'];
        }
    }
    //Added By HJ On 31-12-2018 For Get LinkedIn Picture Data End
    if (count($Data) > 0) {
        if ($Data[0]['eStatus'] == "Active" || ($user_type == "Driver" && $Data[0]['eStatus'] != "Deleted")) {
            $iUserId_passenger = $Data[0]['iUserId'];
            //$where = " iUserId = '$iUserId_passenger' ";
            $where = " $iMemberId = '$iUserId_passenger' ";
            if ($Data[0]['vLang'] == "" && $vLang == "") {
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $Data_update_passenger['vLang'] = $vLang;
            }
            if ($vLang != "") {
                $Data_update_passenger['vLang'] = $vLang;
                $Data[0]['vLang'] = $vLang;
            }
            if ($vCurrency != "") {
                $Data_update_passenger[$vCurrencyMember] = $vCurrency;
            }
            ## Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
            //if ($fbid != 0 || $fbid != "") { // Commented By HJ On 07-03-2019 For Prevent Image Name Update Action If Already Exists Image
            if (isset($Data[0]['vImage']) && $Data[0]['vImage'] == "" && ($fbid != 0 || $fbid != "")) { // Added By HJ On 07-03-2019 For Prevent Image Name Update Action If Already Exists Image
                $userid = $Data[0]['iUserId'];
                $eSignUpType = $eLoginType;
                $UserImage = UploadUserImage($userid, $user_type, $eSignUpType, $fbid, $vImageURL);
                if ($UserImage != "") {
                    $where = " $iMemberId = '$userid' ";
                    $Data_update_image_member[$vImageFiled] = $UserImage;
                    $imageuploadid = $obj->MySQLQueryPerform($tblname, $Data_update_image_member, 'update', $where);
                }
            }
            ## Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
            if ($GCMID != '') {
                $Data_update_passenger['iGcmRegId'] = $GCMID;
                $Data_update_passenger['eDeviceType'] = $DeviceType;
                $Data_update_passenger['vFbId'] = $fbid;
                $Data_update_passenger['eSignUpType'] = $eLoginType;
                $Data_update_passenger['tSessionId'] = session_id() . time();
                $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                $id = $obj->MySQLQueryPerform($tblname, $Data_update_passenger, 'update', $where);
            }
            if ($user_type == "Passenger") {
                if ($eStatus_cab == "Requesting") {
                    $where1 = " iCabRequestId = '$iCabRequestId' ";
                    $Data_update_cab_now['eStatus'] = "Cancelled";
                    $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where1);
                }
            }
            $returnArr['changeLangCode'] = "Yes";
            $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $iServiceId);
            $returnArr['vLanguageCode'] = $Data[0]['vLang'];
            $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
            $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
            $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
            $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
            $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ";
            $defLangValues = $obj->MySQLSelect($sql);
            $returnArr['LIST_LANGUAGES'] = $defLangValues;
            for ($i = 0;$i < count($defLangValues);$i++) {
                if ($defLangValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                }
            }
            $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ";
            $defCurrencyValues = $obj->MySQLSelect($sql);
            $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
            for ($i = 0;$i < count($defCurrencyValues);$i++) {
                if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                }
            }
            $returnArr['Action'] = "1";
            if ($user_type == "Passenger") {
                $returnArr['message'] = getPassengerDetailInfo($Data[0]['iUserId'], '', "");
                $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
                $generalobj->createUserLog("Passenger", "No", $Data[0]['iUserId'], "Android");
            }
            else {
                $returnArr['message'] = getDriverDetailInfo($Data[0]['iUserId'], '');
                $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
                $generalobj->createUserLog("Driver", "No", $Data[0]['iUserId'], "Android");
            }
            setDataResponse($returnArr);
        }
        else {
            $returnArr['Action'] = "0";
            if ($Data[0]['eStatus'] != "Deleted") {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                $returnArr['eStatus'] = $Data[0]['eStatus'];
            }
            else {
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                $returnArr['eStatus'] = $Data[0]['eStatus'];
            }
            setDataResponse($returnArr);
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_REGISTER";
        setDataResponse($returnArr);
    }
}
###########################################################################
if ($type == 'staticPage') {
    $iPageId = isset($_REQUEST['iPageId']) ? clean($_REQUEST['iPageId']) : '';
    $languageCode = getUserLanguageCode();
    $pageDesc = get_value('pages', 'tPageDesc_' . $languageCode, 'iPageId', $iPageId, '', 'true');
    // $meta['page_desc']=strip_tags($pageDesc);
    $meta['page_desc'] = $pageDesc;
    setDataResponse($meta);
}
###########################################################################
if ($type == 'sendContactQuery') {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $UserId = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $subject = isset($_REQUEST["subject"]) ? $_REQUEST["subject"] : '';
    if ($UserType == 'Passenger') {
        $sql = "SELECT vName,vLastName,vPhone,vEmail FROM register_user WHERE iUserId=$UserId";
        $result_data = $obj->MySQLSelect($sql);
    }
    else if ($UserType == 'Driver') {
        $sql = "SELECT vName,vLastName,vPhone,vEmail FROM register_driver WHERE iDriverId=$UserId";
        $result_data = $obj->MySQLSelect($sql);
    }
    if ($UserId != "") {
        $Data['vFirstName'] = $result_data[0]['vName'];
        $Data['vLastName'] = $result_data[0]['vLastName'];
        $Data['vEmail'] = $result_data[0]['vEmail'];
        $Data['cellno'] = $result_data[0]['vPhone'];
        $Data['eSubject'] = $subject;
        $Data['tSubject'] = $message;
        $id = $generalobj->send_email_user("CONTACTUS", $Data);
    }
    else {
        $Data['vFirstName'] = "App User";
        $Data['vLastName'] = "";
        $Data['vEmail'] = "-";
        $Data['cellno'] = "-";
        $Data['eSubject'] = $subject;
        $Data['tSubject'] = $message;
        $id = $generalobj->send_email_user("CONTACTUSWITHOUTLOGIN", $Data);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_SENT_CONTACT_QUERY_SUCCESS_TXT";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_CONTACT_QUERY_TXT";
    }
    setDataResponse($returnArr);
}
############################# GetFAQ ######################################
if ($type == "getFAQ") {
    $status = "Active";
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }
    else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT * FROM `faq_categories` WHERE eStatus='$status' AND vCode='" . $languageCode . "' AND ( eCategoryType = 'General' OR eCategoryType = '" . $GeneralUserType . "' ) ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);
    $i = 0;
    if (count($Data) > 0) {
        $row = $Data;
        while (count($row) > $i) {
            $rows_questions = array();
            $iUniqueId = $row[$i]['iUniqueId'];
            $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer FROM `faqs` WHERE eStatus='$status' AND iFaqcategoryId='" . $iUniqueId . "' ORDER BY iDisplayOrder ASC";
            $row_questions = $obj->MySQLSelect($sql);
            $j = 0;
            while (count($row_questions) > $j) {
                $rows_questions[$j] = $row_questions[$j];
                $j++;
            }
            $row[$i]['Questions'] = $rows_questions;
            $i++;
        }
        //echo "<pre>";print_R($row);die;
        $returnData['Action'] = "1";
        $returnData['message'] = $row;
    }
    else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_FAQ_NOT_AVAIL";
    }
    setDataResponse($returnData);
}
###########################################################################
if ($type == 'getReceipt') {
    $iTripId = isset($_REQUEST['iTripId']) ? clean($_REQUEST['iTripId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : ''; //Passenger OR Driver
    $eType = get_value('trips', 'eType', 'iTripId', $iTripId, '', 'true');
    if ($eType == "Multi-Delivery") {
        $value = sendTripReceipt_Multi($iTripId);
    }
    else {
        $value = sendTripReceipt($iTripId);
    }
    if ($value == true || $value == "true" || $value == "1") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_CHECK_INBOX_TXT";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_RECEIPT_EMAIL_TXT";
    }
    setDataResponse($returnArr);
}
########################### Get Available Taxi ##############################
if ($type == "loadAvailableCab") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $passengerDestLat = isset($_REQUEST["DestLat"]) ? $_REQUEST["DestLat"] : '';
    $passengerDestLon = isset($_REQUEST["DestLong"]) ? $_REQUEST["DestLong"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';
    $geoCodeResult = isset($_REQUEST["currentGeoCodeResult"]) ? $_REQUEST["currentGeoCodeResult"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $eRental = isset($_REQUEST["eRental"]) ? $_REQUEST["eRental"] : 'No'; // Yes Or No
    $eShowOnlyMoto = isset($_REQUEST["eShowOnlyMoto"]) ? $_REQUEST["eShowOnlyMoto"] : 'No'; // Yes Or No
    // addon changes
    $sortby = isset($_REQUEST["sortby"]) ? $_REQUEST["sortby"] : 'eIsFeatured'; // nearby , rating, featured
    // for hotel web
    $isFromHotelPanel = isset($_REQUEST["isFromHotelPanel"]) ? $_REQUEST["isFromHotelPanel"] : 'No';
    //echo $LIST_DRIVER_LIMIT_BY_DISTANCE;die;
    $SelectedCabType = isset($_REQUEST["SelectedCabType"]) ? $_REQUEST["SelectedCabType"] : '';
    // for admin web
    $isFromAdminPanel = isset($_REQUEST["isFromAdminPanel"]) ? $_REQUEST["isFromAdminPanel"] : 'No';
    $eFemaleDriverRequestWeb = isset($_REQUEST["eFemaleDriverRequest"]) ? $_REQUEST["eFemaleDriverRequest"] : '';
    $eHandiCapAccessibilityWeb = isset($_REQUEST["eHandiCapAccessibility"]) ? $_REQUEST["eHandiCapAccessibility"] : '';
    //$eChildSeatAvailableWeb = isset($_REQUEST["eChildSeatAvailable"]) ? $_REQUEST["eChildSeatAvailable"] : 'No';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    $vCountryCode = isset($_REQUEST["vUserDeviceCountry"]) ? strtoupper($_REQUEST['vUserDeviceCountry']) : 'US';
    //added by SP for fly stations on 19-08-2019
    $eFly = isset($_REQUEST["eFly"]) ? $_REQUEST['eFly'] : 'No';
    $iFromLocationId = isset($_REQUEST["iFromStationId"]) ? $_REQUEST['iFromStationId'] : '';
    $iToLocationId = isset($_REQUEST["iToStationId"]) ? $_REQUEST['iToStationId'] : '';
    if ($eType == "" || $eType == NULL) {
        $eType = $SelectedCabType;
    }
    if ($eRental == "" || $eRental == NULL) {
        $eRental = "No";
    }
    if ($eShowOnlyMoto == "" || $eShowOnlyMoto == NULL) {
        $eShowOnlyMoto = "No";
    }
    if ($eType == "UberX" && $scheduleDate != "") {
        $Check_Driver_UFX = "Yes";
        $sdate = explode(" ", $scheduleDate);
        $shour = explode("-", $sdate[1]);
        $shour1 = $shour[0];
        $Check_Date_Time = $sdate[0] . " " . $shour1 . ":00:00";
    }
    else if ($eType == "UberX" && $SERVICE_PROVIDER_FLOW == "Provider") {
        $Check_Driver_UFX = "Yes";
        $Check_Date_Time = "";
    }
    else {
        $Check_Driver_UFX = "No";
        $Check_Date_Time = "";
    }
    $address_data['PickUpAddress'] = $PickUpAddress;
    $DataArr = getOnlineDriverArr($passengerLat, $passengerLon, $address_data, "No", "No", $Check_Driver_UFX, $Check_Date_Time, $passengerDestLat, $passengerDestLon, $eType, $eFemaleDriverRequestWeb);
    //echo "<pre>";print_r($DataArr);die;
    $Data = $DataArr['DriverList'];
    //echo "<pre>";print_r($Data);die;
    // addon changes
    ### Sorting Of Providers by  nearby , rating, featured ###
    if ($eType == "UberX") {
        if ($sortby == "" || $sortby == NULL) {
            $sortby = "eIsFeatured";
        }
        if ($sortby == "vAvgRating") {
            $sortfield = "vAvgRating";
            $sortorder = SORT_DESC;
        }
        else if ($sortby == "distance") {
            $sortfield = "distance";
            $sortorder = SORT_ASC;
        }
        else if ($sortby == 'IS_PROVIDER_ONLINE') {
            $sortfield = "IS_PROVIDER_ONLINE";
            $sortorder = SORT_DESC;
        }
        else if ($sortby == 'eFavDriver') {
            $sortfield = "eFavDriver";
            $sortorder = SORT_DESC;
        }
        else {
            $sortfield = "eIsFeatured";
            $sortorder = SORT_DESC;
        }
        foreach ($Data as $k => $v) {
            $Data_name[$sortfield][$k] = $v[$sortfield];
            $Data_name['vAvailability'][$k] = $v['vAvailability'];
        }
        array_multisort($Data_name[$sortfield], $sortorder, $Data);
    }
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    //array_multisort($Data_name['vAvailability'],SORT_DESC,$Data_name[$sortfield], $sortorder,$Data);
    ### Sorting Of Restaurants by relevance , rating, time, costlth, costhtl ###
    //$ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations","ALLOW_SERVICE_PROVIDER_AMOUNT");
    $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    $iParentId = get_value($sql_vehicle_category_table_name, 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    if ($iParentId == 0) {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    }
    else {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
    $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $vLang = $passengerData[0]['vLang'];
    $vCurrencyPassenger = $passengerData[0]['vCurrencyPassenger'];
    $vCurrencySymbol = $passengerData[0]['vSymbol'];
    $priceRatio = $passengerData[0]['Ratio'];
    if ($vLang == '') {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $i = 0;
    // for hotel panel web
    $Removal_drivers_Positions = "";
    $data_driver = array();
    //echo "<pre>";print_r($Data);die;
    while (count($Data) > $i) {
        if ($Data[$i]['vImage'] != "" && $Data[$i]['vImage'] != "NONE") {
            $Data[$i]['vImage'] = "3_" . $Data[$i]['vImage'];
        }
        $driverVehicleID = $Data[$i]['iDriverVehicleId'];
        if ($eType == "UberX") {
            $query = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '" . $Data[$i]['iDriverId'] . "' AND eType = 'UberX'";
            $result = $obj->MySQLSelect($query);
            if (count($result) > 0) {
                $driverVehicleID = $result[0]['iDriverVehicleId'];
            }
        }
        else {
            $driverVehicleID = $Data[$i]['iDriverVehicleId'];
        }
        $Data[$i]['iDriverVehicleId'] = $driverVehicleID;
        $ssql1 = "";
        if ($eHandiCapAccessibilityWeb == 'Yes') {
            $ssql1 .= " AND eHandiCapAccessibility='Yes'";
        }
        $sql = "SELECT dv.iDriverVehicleId, dv.iDriverId, dv.iCompanyId, dv.iMakeId, dv.iModelId, dv.iYear, dv.vLicencePlate, dv.vColour, dv.eStatus, dv.vInsurance, dv.vRegisteration, dv.vPermit, dv.eCarX, dv.eCarGo, dv.vCarType, dv.vRentalCarType, dv.eHandiCapAccessibility, dv.eType, dv.eAddedDeliverVehicle, dv.eWheelChairAvailable, make.vMake AS make_title, model.vTitle AS model_title FROM `driver_vehicle` dv, make, model WHERE dv.iMakeId = make.iMakeId AND dv.iModelId = model.iModelId AND iDriverVehicleId='$driverVehicleID' $ssql1";
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            ChildSheetAvailability();
        }
        $rows_driver_vehicle = $obj->MySQLSelect($sql);
        $fAmount = "";
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $SERVICE_PROVIDER_FLOW != "Provider") {
            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $rows_driver_vehicle[0]['iDriverVehicleId'] . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);
            $vehicleTypeData = get_value('vehicle_type', 'eFareType,fPricePerHour,fFixedFare,fMinHour', 'iVehicleTypeId', $iVehicleTypeId);
            if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                $fAmount = $vCurrencySymbol . formatNum($vehicleTypeData[0]['fFixedFare'] * $priceRatio);
            }
            else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                $fAmount = $vCurrencySymbol . formatNum($vehicleTypeData[0]['fPricePerHour'] * $priceRatio) . "/hour";
            }
            if (count($serviceProData) > 0) {
                $fAmount = formatNum($serviceProData[0]['fAmount'] * $priceRatio);
                if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                    $fAmount = $vCurrencySymbol . $fAmount;
                }
                else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                    $fAmount = $vCurrencySymbol . $fAmount . "/hour";
                }
            }
            $rows_driver_vehicle[0]['fAmount'] = $fAmount;
            $rows_driver_vehicle[0]['eFareType'] = $vehicleTypeData[0]['eFareType'];
            $rows_driver_vehicle[0]['fMinHour'] = $vehicleTypeData[0]['fMinHour'];
            $rows_driver_vehicle[0]['vCurrencySymbol'] = $vCurrencySymbol;
        }
        $Data[$i]['DriverCarDetails'] = array();
        if (isset($rows_driver_vehicle[0])) {
            $Data[$i]['DriverCarDetails'] = $rows_driver_vehicle[0];
        }
        $unsetArr = 0;
        // for hotel panel web
        if ($isFromHotelPanel == 'Yes' || $isFromAdminPanel == 'Yes') {
            $isRemoveDriverIntoList = "No";
            $DriverVehicleTypeArrNew = explode(",", $rows_driver_vehicle[0]['vCarType']);
            if (!in_array($iVehicleTypeId, $DriverVehicleTypeArrNew)) {
                $isRemoveDriverIntoList = "Yes";
            }
            if ($isRemoveDriverIntoList == "Yes") {
                unset($Data[$i]);
                $unsetArr = 1;
                $Data = array_values(array_filter($Data));
            }
        }
        //Added By HJ On 24-01-2019 For Check Driver Vehicle Service Available Or Not Start
        if ($SERVICE_PROVIDER_FLOW == "Provider" && $eType == "UberX") {
            $serviceStatus = getServiceProviderVehicleData($rows_driver_vehicle, $iVehicleTypeId);
            if ($serviceStatus == "Success" && $unsetArr == 0) {
                $data_driver[] = $Data[$i];
            }
        }
        else if ($unsetArr == 0) {
            $data_driver[] = $Data[$i];
        }
        //Added By HJ On 24-01-2019 For Check Driver Vehicle Service Available Or Not End
        $i++;
    }
    $where = " iUserId='" . $iUserId . "'";
    $data['vLatitude'] = $passengerLat;
    $data['vLongitude'] = $passengerLon;
    $data['vRideCountry'] = $vCountryCode;
    $data['tLastOnline'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("register_user", $data, 'update', $where);
    # Update User Location Date #
    Updateuserlocationdatetime($iUserId, "Passenger", $vTimeZone);
    # Update User Location Date #
    $returnArr['AvailableCabList'] = $data_driver;
    $returnArr['PassengerLat'] = $passengerLat;
    $returnArr['PassengerLon'] = $passengerLon;
    // kiosk changes
    $ssql = "";
    if (strtolower($_REQUEST['GeneralUserType']) == 'hotel' || strtolower($_REQUEST['GeneralUserType']) == 'kiosk') {
        $ssql .= " AND eType = 'Ride' AND ePoolStatus = 'No'";
    } else {
        if (!empty($eType)) {
            if ($eType == "Delivery" || $eType == "Deliver") {
                $ssql .= " AND eType = 'Deliver'";
            } else {
                $ssql .= " AND eType = '" . $eType . "'";
            }
        } else {
            if ($APP_TYPE == "Delivery") {
                $ssql .= " AND eType = 'Deliver'";
            } else if ($APP_TYPE == "Ride-Delivery") {
                $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
            } else if ($APP_TYPE == "Ride-Delivery-UberX") {
                $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
            } else {
                $ssql .= " AND eType = '" . $APP_TYPE . "'";
            }
        }
    }
    if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Delivery") {
        $CheckRideDelivery = CheckRideDeliveryFeatureDisable();
        //echo "<pre>";print_r($CheckRideDelivery);die;
        if ($eShowOnlyMoto == "Yes") {
            $ssql .= " AND (eIconType = 'Bike' OR eIconType = 'Cycle')";
        } else {
            if ($eRental == "No") {
                if (($eType == 'Ride' && $CheckRideDelivery['eMotoRideEnable'] == "No") || ($eRental == "Yes" && $CheckRideDelivery['eMotoRentalEnable'] == "No") || ($eType == 'Deliver' && $CheckRideDelivery['eMotoDeliveryEnable'] == "No")) {                      
                    $ssql .= "";
                } else {
                    $ssql .= " AND eIconType != 'Bike' AND eIconType != 'Cycle'";
                }
            } else {
                if ($CheckRideDelivery['eRentalEnable'] == "No" && $CheckRideDelivery['eMotoRentalEnable'] == "No") {
                    $ssql .= "";
                } else {
                    $ssql .= " AND eIconType != 'Bike' AND eIconType != 'Cycle'";
                }
            }
        }
    }
    //added by SP for fly stations on 19-08-2019
    if ($eFly == 'Yes') {
        $ssql .= " AND eFly = '1'";
    } else {
        $ssql .= " AND eFly = '0'";
    }
    $pickuplocationarr = array($passengerLat,$passengerLon);
    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $ssql = LoadAvailableCabVehicleQuery($ssql);
    }
    //Added By HJ On 30-12-2018 For Check POOL Status End
    //$sql23 = "SELECT * FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) $ssql AND eStatus = 'Active' ORDER BY ePoolStatus DESC,iDisplayOrder ASC";
    $sql23 = "SELECT * FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) $ssql AND eStatus = 'Active' ORDER BY iDisplayOrder ASC";
    $vehicleTypes = $obj->MySQLSelect($sql23);
    //added by SP for fly stations on 19-08-2019, its bc fly vehicles are shown only if price in location wise fare is entered
    if ($eFly == 'Yes') {
        //fly_location_wise_fare.iFromLocationId
        if (!empty($iFromLocationId)) $ssql .= " AND fly_location_wise_fare.iFromLocationId = $iFromLocationId AND fly_location_wise_fare.iToLocationId = $iToLocationId"; //becoz vehicles are shown of source location only..if enter iscon then show vehicles which have from station iscon, and also add for it destination
        $sql1 = "SELECT tCentroidLattitude,tCentroidLongitude FROM  `location_master` WHERE 1=1 AND eStatus = 'Active' AND iLocationId = $iFromLocationId AND eFor = 'FlyStation'";
        $db_data_vehicle = $obj->MySQLSelect($sql1);
        $latlong = array($db_data_vehicle[0]['tCentroidLattitude'],$db_data_vehicle[0]['tCentroidLongitude']);
        $vtype = GetVehicleTypeFromGeoLocation($latlong);
        $vtypeArr = explode(',', $vtype);
        $sql1to = "SELECT tCentroidLattitude,tCentroidLongitude FROM  `location_master` WHERE 1=1 AND eStatus = 'Active' AND iLocationId = $iToLocationId AND eFor = 'FlyStation'";
        $db_data_vehicleto = $obj->MySQLSelect($sql1to);
        $latlongto = array($db_data_vehicleto[0]['tCentroidLattitude'],$db_data_vehicleto[0]['tCentroidLongitude']);
        $vtypeto = GetVehicleTypeFromGeoLocation($latlongto);
        $vtypeArrto = explode(',', $vtypeto);
        $result = array_intersect($vtypeArr, $vtypeArrto);
        if (!empty($result)) {
            $ilocation_id = implode("','", $result);
        }
        //echo "SELECT DISTINCT(vehicle_type.iVehicleTypeId),vehicle_type.* FROM vehicle_type RIGHT JOIN fly_location_wise_fare ON vehicle_type.iVehicleTypeId = fly_location_wise_fare.iVehicleTypeId WHERE vehicle_type.iLocationid IN ('$ilocation_id') $ssql AND vehicle_type.eStatus = 'Active' AND fly_location_wise_fare.eStatus = 'Active'";exit;
        $vehicleTypes = $obj->MySQLSelect("SELECT DISTINCT(vehicle_type.iVehicleTypeId),vehicle_type.* FROM vehicle_type RIGHT JOIN fly_location_wise_fare ON vehicle_type.iVehicleTypeId = fly_location_wise_fare.iVehicleTypeId WHERE vehicle_type.iLocationid IN ('$ilocation_id') $ssql AND vehicle_type.eStatus = 'Active' AND fly_location_wise_fare.eStatus = 'Active'");
    }
    for ($i = 0;$i < count($vehicleTypes);$i++) {
        $Photo_Gallery_folder = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $vehicleTypes[$i]['iVehicleTypeId'] . '/android/' . $vehicleTypes[$i]['vLogo'];
        if ($vehicleTypes[$i]['vLogo'] != "" && file_exists($Photo_Gallery_folder)) {
            $vehicleTypes[$i]['vLogo'] = $vehicleTypes[$i]['vLogo'];
        }
        else {
            $vehicleTypes[$i]['vLogo'] = "";
        }
        $vehicleTypes[$i]['fPricePerKM'] = round($vehicleTypes[$i]['fPricePerKM'] * $priceRatio, 2);
        $vehicleTypes[$i]['fPricePerMin'] = round($vehicleTypes[$i]['fPricePerMin'] * $priceRatio, 2);
        $vehicleTypes[$i]['iBaseFare'] = round($vehicleTypes[$i]['iBaseFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['fCommision'] = round($vehicleTypes[$i]['fCommision'] * $priceRatio, 2);
        $vehicleTypes[$i]['iMinFare'] = round($vehicleTypes[$i]['iMinFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['FareValue'] = round($vehicleTypes[$i]['fFixedFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['vVehicleType'] = $vehicleTypes[$i]["vVehicleType_" . $vLang];
        /* Added For Rental */
        $vehicleTypes[$i]['eRental'] = 'No';
        if ($eType == "Ride" && strtoupper(PACKAGE_TYPE) != "STANDARD" && ENABLE_RENTAL_OPTION == "Yes") {
            $vehicleTypes[$i]['vRentalVehicleTypeName'] = $vehicleTypes[$i]["vRentalAlias_" . $vLang] != '' ? $vehicleTypes[$i]["vRentalAlias_" . $vLang] : $vehicleTypes[$i]["vVehicleType_" . $vLang];
            $vehicleTypes[$i]['eRental'] = isRentalEnable($vehicleTypes[$i]['iVehicleTypeId']);
        }
        /* End Added For Rental */
    }
    if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
        $vehicleTypes = validateVehicleTypes($vehicleTypes);
    }
    //echo "<pre>";print_r($vehicleTypes);die;
    if ($eType == "UberX") {
        $returnArr['VehicleTypes'] = array();
    }
    else {
        $returnArr['VehicleTypes'] = $vehicleTypes;
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "getDriverStates") {
    $driverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $ssql = "";
    /* if ($APP_TYPE == "Delivery") {
    
      $ssql .= " AND dm.eType = 'Delivery'";
    
      } else if ($APP_TYPE == "Ride-Delivery") {
    
      $ssql .= " AND ( dm.eType = 'Deliver' OR dm.eType = 'Ride')";
    
      } else if ($APP_TYPE == "Ride-Delivery-UberX") {
    
      $ssql .= " AND ( dm.eType = 'Deliver' OR dm.eType = 'Ride' OR dm.eType = 'UberX')";
    
      } else {
    
      $ssql .= " AND dm.eType = '" . $APP_TYPE . "'";
    
      } */
    $docUpload = 'Yes';
    $driverVehicleUpload = 'Yes';
    $driverStateActive = 'Yes';
    $driverVehicleDocumentUpload = 'Yes';
    $vCountry = get_value('register_driver', 'vCountry', 'iDriverId', $driverId, '', true);
    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status,dm.eType, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as docstatus FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $driverId . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='driver' and (dm.country='" . $vCountry . "' OR dm.country='All') and dm.status='Active' $ssql";
    $db_document = $obj->MySQLSelect($sql1);
    $docUpload = 'No';
    if (count($db_document) > 0) {
        if ($APP_TYPE == "Ride-Delivery-UberX") {
            $ride_document_array = array();
            $delivery_document_array = array();
            $uberx_document_array = array();
            for ($i = 0;$i < count($db_document);$i++) {
                if ($db_document[$i]['eType'] == "Ride") {
                    array_push($ride_document_array, $db_document[$i]);
                }
                if ($db_document[$i]['eType'] == "Delivery") {
                    array_push($delivery_document_array, $db_document[$i]);
                }
                if ($db_document[$i]['eType'] == "UberX") {
                    array_push($uberx_document_array, $db_document[$i]);
                }
            }
            $isAllDocumentUpload = false;
            for ($i = 0;$i < count($ride_document_array);$i++) {
                $isAllDocumentUpload = ($ride_document_array[$i]['doc_file'] != "") ? true : false;
            }
            if ($isAllDocumentUpload == false) {
                for ($i = 0;$i < count($delivery_document_array);$i++) {
                    $isAllDocumentUpload = ($delivery_document_array[$i]['doc_file'] != "") ? true : false;
                }
            }
            if ($isAllDocumentUpload == false) {
                for ($i = 0;$i < count($uberx_document_array);$i++) {
                    $isAllDocumentUpload = ($uberx_document_array[$i]['doc_file'] != "") ? true : false;
                }
            }
            $docUpload = ($isAllDocumentUpload == true) ? "Yes" : "No";
        }
        elseif ($APP_TYPE == "Ride-Delivery") {
            $ride_document_array = array();
            $delivery_document_array = array();
            for ($i = 0;$i < count($db_document);$i++) {
                if ($db_document[$i]['eType'] == "Ride") {
                    array_push($ride_document_array, $db_document[$i]);
                }
                if ($db_document[$i]['eType'] == "Delivery") {
                    array_push($delivery_document_array, $db_document[$i]);
                }
            }
            $isAllDocumentUpload = false;
            for ($i = 0;$i < count($ride_document_array);$i++) {
                $isAllDocumentUpload = ($ride_document_array[$i]['doc_file'] != "") ? true : false;
            }
            if ($isAllDocumentUpload == false) {
                for ($i = 0;$i < count($delivery_document_array);$i++) {
                    $isAllDocumentUpload = ($delivery_document_array[$i]['doc_file'] != "") ? true : false;
                }
            }
            $docUpload = ($isAllDocumentUpload == true) ? "Yes" : "No";
        }
        else {
            for ($i = 0;$i < count($db_document);$i++) {
                if ($db_document[$i]['doc_file'] == "") {
                    $docUpload = 'No';
                    break;
                }
                else {
                    $docUpload = 'Yes';
                }
            }
        }
    }
    else {
        $docUpload = 'Yes';
    }
    if ($APP_TYPE != 'UberX') {
        ## Count Driver Vehicle ##
        $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND eStatus != 'Deleted'";
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
        $returnArr['TotalVehicles'] = strval($TotalVehicles);
        ## Count Driver Vehicle ##
        $sql = "SELECT iDriverVehicleId from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND eStatus != 'Deleted'";
        $db_drv_vehicle = $obj->MySQLSelect($sql);
        if (count($db_drv_vehicle) == 0) {
            $driverVehicleUpload = 'No';
        }
        else if ($driverVehicleUpload != 'No') {
            $test = array();
            # Check For Driver's selected vehicle's document are upload or not #
            $sql = "SELECT dl.*,dv.iDriverVehicleId FROM `driver_vehicle` AS dv LEFT JOIN document_list as dl ON dl.doc_userid=dv.iDriverVehicleId WHERE dv.iDriverId='$driverId' AND dl.doc_usertype = 'car' AND dv.eStatus != 'Deleted' ";
            $db_selected_vehicle = $obj->MySQLSelect($sql);
            if (count($db_selected_vehicle) > 0) {
                for ($i = 0;$i < count($db_selected_vehicle);$i++) {
                    if ($db_selected_vehicle[$i]['doc_file'] == "") {
                        $test[] = '1';
                    }
                }
            }
            if (count($test) == count($db_selected_vehicle)) {
                $driverVehicleUpload = 'No';
            }
        }
    }
    else {
        $sql = "SELECT vCarType from driver_vehicle WHERE iDriverId = '" . $driverId . "'";
        $db_drv_vehicle = $obj->MySQLSelect($sql);
        $driverVehicleUpload = 'Yes';
        if ($db_drv_vehicle[0]['vCarType'] == "") {
            $driverVehicleUpload = 'No';
        }
    }
    $sql = "SELECT rd.eStatus as driverstatus,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='" . $driverId . "' AND cmp.iCompanyId=rd.iCompanyId";
    $Data = $obj->MySQLSelect($sql);
    if (strtolower($Data[0]['driverstatus']) != "active" || strtolower($Data[0]['cmpEStatus']) != "active") {
        $driverStateActive = 'No';
    }
    if ($APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery-UberX") {
        $sql = "select * from `driver_manage_timing` where iDriverId = '" . $driverId . "'";
        $db_driver_timing = $obj->MySQLSelect($sql);
        if (count($db_driver_timing) > 0) {
            $returnArr['IS_DRIVER_MANAGE_TIME_AVAILABLE'] = "Yes";
        }
        else {
            $returnArr['IS_DRIVER_MANAGE_TIME_AVAILABLE'] = "No";
        }
    }
    if ($driverStateActive == "Yes") {
        $docUpload = "Yes";
        $driverVehicleUpload = "Yes";
        $driverVehicleDocumentUpload = "Yes";
        $returnArr['IS_DRIVER_MANAGE_TIME_AVAILABLE'] = "Yes";
    }
    $returnArr['Action'] = "1";
    $returnArr['IS_DOCUMENT_PROCESS_COMPLETED'] = $docUpload;
    $returnArr['IS_VEHICLE_PROCESS_COMPLETED'] = $driverVehicleUpload;
    $returnArr['IS_VEHICLE_DOCUMENT_PROCESS_COMPLETED'] = $driverVehicleDocumentUpload;
    $returnArr['IS_DRIVER_STATE_ACTIVATED'] = $driverStateActive;
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "CheckPromoCode") {
    $validPromoCodesArr = getValidPromoCodes();
    if (!empty($validPromoCodesArr) && !empty($validPromoCodesArr['CouponList']) && count($validPromoCodesArr['CouponList']) > 0) {
        $returnArr['Action'] = "1"; // code is valid
        $returnArr["message"] = "LBL_SUCCESS_COUPON_CODE";
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0"; // code is invalid
        $returnArr["message"] = "LBL_INVALID_COUPON_CODE";
        setDataResponse($returnArr);
    }
}
###########################################################################
if ($type == 'estimateFare') {
    $sourceLocation = isset($_REQUEST["sourceLocation"]) ? $_REQUEST["sourceLocation"] : '';
    $destinationLocation = isset($_REQUEST["destinationLocation"]) ? $_REQUEST["destinationLocation"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';
    $sourceLocationArr = explode(",", $sourceLocation);
    $destinationLocationArr = explode(",", $destinationLocation);
    // changes for flattrip 29-01-2019
    $eType_vehicle = get_value('vehicle_type', 'eType', 'iVehicleTypeId', $SelectedCar, '', 'true');
    $sqlp = "SELECT ru.vCurrencyPassenger,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $vCurrencyPassenger = $passengerData[0]['vCurrencyPassenger'];
    $priceRatio = $passengerData[0]['Ratio'];
    // changes for flattrip 29-01-2019
    $eFlatTrip = "No";
    $fFlatTripPrice = 0;
    if ($eType_vehicle == 'Ride' && strtoupper(PACKAGE_TYPE) != "STANDARD") {
        $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $SelectedCar);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    }
    if ($eFlatTrip == "No") {
        $Fare_data = calculateFareEstimate($time, $distance, $SelectedCar, $iUserId, 1);
        $Fare_data[0]['Distance'] = $distance == NULL ? "0" : strval(round($distance, 2));
        $Fare_data[0]['Time'] = $time == NULL ? "0" : strval(round($time, 2));
        $Fare_data[0]['total_fare'] = number_format(round($Fare_data[0]['total_fare'] * $priceRatio, 1) , 2);
        $Fare_data[0]['iBaseFare'] = number_format(round($Fare_data[0]['iBaseFare'] * $priceRatio, 1) , 2);
        $Fare_data[0]['fPricePerMin'] = number_format(round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1) , 2);
        $Fare_data[0]['fPricePerKM'] = number_format(round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1) , 2);
        $Fare_data[0]['fCommision'] = number_format(round($Fare_data[0]['fCommision'] * $priceRatio, 1) , 2);
        $Fare_data[0]['eFlatTrip'] = "No";
        if ($Fare_data[0]['MinFareDiff'] > 0) {
            $Fare_data[0]['MinFareDiff'] = number_format(round($Fare_data[0]['MinFareDiff'] * $priceRatio, 1) , 2);
        }
        else {
            $Fare_data[0]['MinFareDiff'] = "0";
        }
        $Fare_data[0]['MinFareDiff'] = "0";
    }
    else {
        $Fare_data[0]['Distance'] = "0.00";
        $Fare_data[0]['Time'] = "0.00";
        $Fare_data[0]['total_fare'] = $fFlatTripPrice; //number_format(round($fFlatTripPrice * $priceRatio,1),2);
        $Fare_data[0]['iBaseFare'] = number_format(round($fFlatTripPrice * $priceRatio, 1) , 2);
        $Fare_data[0]['fPricePerMin'] = "0.00";
        $Fare_data[0]['fPricePerKM'] = "0.00";
        $Fare_data[0]['fCommision'] = number_format(round($fFlatTripPrice * $priceRatio, 1) , 2);
        $Fare_data[0]['eFlatTrip'] = "Yes";
        $Fare_data[0]['MinFareDiff'] = "0.00";
        $Fare_data[0]['Flatfare'] = $fFlatTripPrice;
    }
    $Fare_data[0]['Action'] = "1";
    setDataResponse($Fare_data[0]);
}
###########################################################################
if ($type == 'estimateFareNew') {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';
    $StartLatitude = isset($_REQUEST["StartLatitude"]) ? $_REQUEST["StartLatitude"] : '0.0';
    $EndLongitude = isset($_REQUEST["EndLongitude"]) ? $_REQUEST["EndLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $iQty = isset($_REQUEST["iQty"]) ? $_REQUEST["iQty"] : '1';
    $PromoCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    $SelectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    //added by SP for fly stations on 19-08-2019
    $eFly = isset($_REQUEST['eFly']) ? trim($_REQUEST['eFly']) : '';
    $iFromStationId = isset($_REQUEST['iFromStationId']) ? trim($_REQUEST['iFromStationId']) : '';
    $iToStationId = isset($_REQUEST['iToStationId']) ? trim($_REQUEST['iToStationId']) : '';
    $time = $generalobj->setTwoDecimalPoint($time / 60);
    $distance = $generalobj->setTwoDecimalPoint($distance / 1000);
    $isDestinationAdded = "No";
    if ($DestLatitude != "" && $DestLongitude != "") {
        $isDestinationAdded = "Yes";
    }
    $sourceLocationArr = array(
        $StartLatitude,
        $EndLongitude
    );
    $destinationLocationArr = array(
        $DestLatitude,
        $DestLongitude
    );
    // kiosk changes
    if (strtolower($GeneralUserType) == 'hotel' || strtolower($GeneralUserType) == 'kiosk') {
        $iMemberId = isset($_REQUEST['iMemberId']) ? $_REQUEST['iMemberId'] : '';
        $iUserId = $iMemberId;
    }
    $Fare_data = calculateFareEstimateAll($time, $distance, $SelectedCar, $iUserId, 1, "", "", $PromoCode, 1, 0, 0, 0, "", "Passenger", $iQty, $SelectedCarTypeID, $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr, '', '', '', $eFly, $iFromStationId, $iToStationId); //added by SP for fly stations on 19-08-2019 add 3 parameter
    $returnArr["Action"] = "1";
    $returnArr["message"] = $Fare_data;
    //$returnArr['eFlatTrip'] = $eFlatTrip;
    setDataResponse($returnArr);
}
###########################################################################
if ($type == 'getEstimateFareDetailsArr') {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';
    $StartLatitude = isset($_REQUEST["StartLatitude"]) ? $_REQUEST["StartLatitude"] : '0.0';
    $EndLongitude = isset($_REQUEST["EndLongitude"]) ? $_REQUEST["EndLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $promoCode = isset($_REQUEST['PromoCode']) ? clean($_REQUEST['PromoCode']) : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST['UserType'] : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    $isDestinationAdded = isset($_REQUEST['isDestinationAdded']) ? trim($_REQUEST['isDestinationAdded']) : 'Yes'; // Yes , No
    //added by SP for fly stations on 20-08-2019
    $eFly = isset($_REQUEST['eFly']) ? trim($_REQUEST['eFly']) : '';
    $iFromStationId = isset($_REQUEST['iFromStationId']) ? trim($_REQUEST['iFromStationId']) : '';
    $iToStationId = isset($_REQUEST['iToStationId']) ? trim($_REQUEST['iToStationId']) : '';
    if ($userType == "" || $userType == NULL) {
        $userType = $GeneralUserType;
    }
    if ($isDestinationAdded == "" || $isDestinationAdded == NULL) {
        $isDestinationAdded = "Yes";
    }
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST['eType'] : 'Ride';
    $details_arr = isset($_REQUEST["details_arr"]) ? $_REQUEST['details_arr'] : '';
    $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    $vSymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    $sourceLocationArr = array(
        $StartLatitude,
        $EndLongitude
    );
    $destinationLocationArr = array(
        $DestLatitude,
        $DestLongitude
    );
    ######### Checking For Flattrip #########
    $eFlatTrip = "No";
    $fFlatTripPrice = 0;
    if ($isDestinationAdded == "Yes" && $eType == "Ride" && strtoupper(PACKAGE_TYPE) != "STANDARD") {
        $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $SelectedCar);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    }
    ######### Checking For Flattrip #########
    $delivery_arr = json_decode($details_arr, true);
    if (!empty($delivery_arr)) {
        $distance = $time = 0;
        for ($i = 0;$i < count($delivery_arr);$i++) {
            $distance1 = $delivery_arr[$i]['distance'];
            $time1 = $delivery_arr[$i]['time'];
            $distance += $distance1;
            $time += $time1;
        }
    }
    // kiosk changes
    if (strtolower($GeneralUserType) == 'hotel' || strtolower($GeneralUserType) == 'kiosk') {
        $iMemberId = isset($_REQUEST['iMemberId']) ? $_REQUEST['iMemberId'] : '';
        $iUserId = $iMemberId;
        $userType = 'Passenger';
    }
    $curr_date = @date("Y-m-d");
    $time = round(($time / 60) , 2);
    $distance = round(($distance / 1000) , 2);
    $IS_RETURN_ARR_WITH_ORIG_AMT = "Yes"; //Added By HJ On 12-03-2020 For Get Origional Fare Amount For Use In app As Per Discuss with KS Sir
    $Fare_data = calculateFareEstimateAll($time, $distance, $SelectedCar, $iUserId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", $userType, 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr, "", $eType, "", $eFly, $iFromStationId, $iToStationId); //added by SP for fly stations on 20-08-2019 add 3 parameter
    $IS_RETURN_ARR_WITH_ORIG_AMT = "No"; //Added By HJ On 12-03-2020 For Get Origional Fare Amount For Use In app As Per Discuss with KS Sir
    //echo "<pre>";print_r($Fare_data);die;
    //Added By HJ On 12-03-2020 For Get Origional Fare Amount For Use In app As Per Discuss with KS Sir Start
    $total_fare_amount = str_replace(",", "", $Fare_data['org_fare_amount']);
    $Fare_data = $Fare_data['fare_data'];
    //Added By HJ On 12-03-2020 For Get Origional Fare Amount For Use In app As Per Discuss with KS Sir End
    array_pop($Fare_data);
    //echo "<pre>";print_r($total_fare_amount);die;
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    $returnArr["Action"] = "1";
    $returnArr["message"] = $Fare_data;
    $returnArr["distance_multi"] = $distance;
    $returnArr["time_multi"] = $time;
    $returnArr["vSymbol"] = $vSymbol;
    $returnArr["total_fare_amount"] = $total_fare_amount;
    $returnArr['fOutStandingAmount'] = $fOutStandingAmount;
    $returnArr['fOutStandingAmountWithSymbol'] = $vSymbol . " " . $fOutStandingAmount;
    //$getVehicleData = $obj->MySQLSelect("SELECT vLogo FROM vehicle_type WHERE iVehicleTypeId='" . $SelectedCar . "'");
    $getVehicleImage = get_value('vehicle_type', 'vLogo', 'iVehicleTypeId', $SelectedCar, '', 'true');
    $returnArr['vehicleImage'] = $tconfig['tsite_upload_images_vehicle_type'] . "/" . $SelectedCar . "/" . strtolower($_REQUEST['GeneralDeviceType']) . "/" . $getVehicleImage; //added by SP for new design on 10-9-2019
    if ($userType != "Driver") {
        $returnArr['message1'] = getPassengerDetailInfo($iUserId, "", "");
    }
    else {
        $returnArr['message1'] = getDriverDetailInfo($iUserId);
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "updateUserProfileDetail") {
    $vName = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : '';
    $vLastName = isset($_REQUEST["vLastName"]) ? stripslashes($_REQUEST["vLastName"]) : '';
    $vPhone = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST['iMemberId'] : '';
    $phoneCode = isset($_REQUEST["vPhoneCode"]) ? $_REQUEST['vPhoneCode'] : '';
    $vCountry = isset($_REQUEST["vCountry"]) ? $_REQUEST['vCountry'] : '';
    $currencyCode = isset($_REQUEST["CurrencyCode"]) ? $_REQUEST['CurrencyCode'] : '';
    $languageCode = isset($_REQUEST["LanguageCode"]) ? $_REQUEST['LanguageCode'] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST['UserType'] : 'Passenger';
    $vEmail = isset($_REQUEST["vEmail"]) ? $_REQUEST['vEmail'] : '';
    $tProfileDescription = isset($_REQUEST["tProfileDescription"]) ? $_REQUEST['tProfileDescription'] : '';
    $eSelectWorkLocation = isset($_REQUEST["eSelectWorkLocation"]) ? $_REQUEST['eSelectWorkLocation'] : 'Dynamic';
    $vInviteCode = isset($_REQUEST["vInviteCode"]) ? $_REQUEST['vInviteCode'] : '';
    //Added By HJ On 13-11-2019 For Check Provider Profile Edit Permission Start
    $driverData = array();
    if ($userType == "Driver") {
        $driverData = $obj->MySQLSelect("SELECT vLang,vCode,vPhone,vEmail,vInviteCode,eStatus,vName,vLastName FROM register_driver WHERE iDriverId = '" . $iMemberId . "'");
        $message = "LBL_EDIT_PROFILE_DISABLED";
        if (count($driverData) > 0) {
            $driverFname = $driverData[0]['vName'];
            $driverLname = $driverData[0]['vLastName'];
            $checkEditProfileStatus = $generalobj->getEditDriverProfileStatus($driverData[0]['eStatus']); // Added By HJ On 13-11-2019 For Check Driver Profile Edit Status As Per Discuss With KS Sir
            if (($driverFname != $vName || $driverLname != $vLastName) && $checkEditProfileStatus == "No") {
                $message = "LBL_PROFILE_EDIT_BLOCK_TXT";
                //$checkEditProfileStatus = "No";
                $returnArr['Action'] = "0";
                $returnArr['message'] = $message;
                setDataResponse($returnArr);
            }
            /* if ($checkEditProfileStatus == "No") {
            
              $returnArr['Action'] = "0";
            
              $returnArr['message'] = $message;
            
              setDataResponse($returnArr);
            
              } */
        }
        else if ($ENABLE_EDIT_DRIVER_PROFILE == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = $message;
            setDataResponse($returnArr);
        }
    }
    //Added By HJ On 13-11-2019 For Check Provider Profile Edit Permission End
    if ($vInviteCode != "") {
        $check_inviteCode = $generalobj->validationrefercode($vInviteCode);
        if ($check_inviteCode == "" || $check_inviteCode == "0" || $check_inviteCode == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_INVITE_CODE_INVALID";
            setDataResponse($returnArr);
        }
        else {
            $inviteRes = explode("|", $check_inviteCode);
            $iRefUserId = $inviteRes[0];
            $eRefType = $inviteRes[1];
        }
    }
    $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $phoneCode . "'";
    $CountryData = $obj->MySQLSelect($csql);
    $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    if ($eZeroAllowed == 'Yes') {
        $vPhone = $vPhone;
    }
    else {
        $first = substr($vPhone, 0, 1);
        if ($first == "0") {
            $vPhone = substr($vPhone, 1);
        }
    }
    $eSystem = "";
    if ($vPhone != "") {
        $checPhoneExist = $generalobj->checkMemberDataInfo($vPhone, "", $userType, $vCountry, $iMemberId, $eSystem); //Added By HJ On 09-09-2019 For Check User Country and Mobile Number When Register
        
    }
    //print_r($checPhoneExist);die;
    if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MOBILE_EXIST";
        setDataResponse($returnArr);
    }
    else if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 2) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
        setDataResponse($returnArr);
    }
    if ($userType != "Driver") {
        $vEmail_userId_check = get_value('register_user', 'iUserId', 'vEmail', $vEmail, '', 'true');
        //$vPhone_userId_check = get_value('register_user', 'iUserId', 'vPhone', $vPhone, '', 'true');
        $where = " iUserId = '$iMemberId'";
        $tableName = "register_user";
        $Data_update_User['vPhoneCode'] = $phoneCode;
        $Data_update_User['vCurrencyPassenger'] = $currencyCode;
        $currentLanguageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
        $sqlp = "SELECT vPhoneCode,vPhone,vEmail,vInviteCode FROM register_user WHERE iUserId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $vPhoneCode_orig = $passengerData[0]['vPhoneCode'];
        $vPhone_orig = $passengerData[0]['vPhone'];
        $vEmail_orig = $passengerData[0]['vEmail'];
        $UservInviteCode = $passengerData[0]['vInviteCode'];
    }
    else {
        $vEmail_userId_check = get_value('register_driver', 'iDriverId', 'vEmail', $vEmail, '', 'true');
        //$vPhone_userId_check = get_value('register_driver', 'iDriverId', 'vPhone', $vPhone, '', 'true');
        $where = " iDriverId = '$iMemberId'";
        $tableName = "register_driver";
        $Data_update_User['vCode'] = $phoneCode;
        $Data_update_User['vCurrencyDriver'] = $currencyCode;
        $Data_update_User['tProfileDescription'] = $tProfileDescription;
        //$Data_update_User['eSelectWorkLocation']=$eSelectWorkLocation;
        if (empty($driverData) || count($driverData) == 0) {
            $sqlp = "SELECT vLang,vCode,vPhone,vEmail,vInviteCode FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
            $driverData = $obj->MySQLSelect($sqlp);
        }
        $currentLanguageCode = $driverData[0]['vLang'];
        $vPhoneCode_orig = $driverData[0]['vCode'];
        $vPhone_orig = $driverData[0]['vPhone'];
        $vEmail_orig = $driverData[0]['vEmail'];
        $UservInviteCode = $driverData[0]['vInviteCode'];
    }
    // $currentLanguageCode = ($obj->MySQLSelect("SELECT vLang FROM ".$tableName." WHERE".$where)[0]['vLang']);
    if ($vEmail_userId_check != "" && $vEmail_userId_check != $iMemberId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
        setDataResponse($returnArr);
    }
    /* if ($vPhone_userId_check != "" && $vPhone_userId_check != $iMemberId) {
    
      $returnArr['Action'] = "0";
    
      $returnArr['message'] = "LBL_MOBILE_EXIST";
    
    
    
      setDataResponse($returnArr);
    
      } */
    if ($vPhone_orig != $vPhone || $vPhoneCode_orig != $phoneCode) {
        $Data_update_User['ePhoneVerified'] = "No";
    }
    if ($vEmail_orig != $vEmail) {
        $Data_update_User['eEmailVerified'] = "No";
    }
    $Data_update_User['vName'] = $vName;
    $Data_update_User['vLastName'] = $vLastName;
    $Data_update_User['vPhone'] = $vPhone;
    $Data_update_User['vCountry'] = $vCountry;
    $Data_update_User['vLang'] = $languageCode;
    if ($vEmail != "") {
        $Data_update_User['vEmail'] = $vEmail;
    }
    if ($UservInviteCode != "" && $vInviteCode != "") {
        $Data_update_User['iRefUserId'] = $iRefUserId;
        $Data_update_User['eRefType'] = $eRefType;
    }
    $id = $obj->MySQLQueryPerform($tableName, $Data_update_User, 'update', $where);
    $returnArr['changeLangCode'] = "No";
    if ($currentLanguageCode != $languageCode) {
        $returnArr['changeLangCode'] = "Yes";
        $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($languageCode, "1", $iServiceId);
        $returnArr['vLanguageCode'] = $languageCode;
        $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $languageCode . "' ";
        $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
        $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
        $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
        $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
        $defLangValues = $obj->MySQLSelect($sql);
        $returnArr['LIST_LANGUAGES'] = $defLangValues;
        for ($i = 0;$i < count($defLangValues);$i++) {
            if ($defLangValues[$i]['eDefault'] == "Yes") {
                $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
            }
        }
        $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
        $defCurrencyValues = $obj->MySQLSelect($sql);
        $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
        for ($i = 0;$i < count($defCurrencyValues);$i++) {
            if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
            }
        }
    }
    if ($userType != "Driver") {
        $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
    }
    else {
        $returnArr['message'] = getDriverDetailInfo($iMemberId);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "uploadImage") {
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $image_name = "123.jpg";
    if ($memberType == "Driver") {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_driver_path'] . "/" . $iMemberId . "/";
    }
    else {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger_path'] . "/" . $iMemberId . "/";
    }
    if (!is_dir($Photo_Gallery_folder)) mkdir($Photo_Gallery_folder, 0777);
    $vImageName = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);
    if ($vImageName != '') {
        if ($memberType == "Driver") {
            //$OldImageName = get_value('register_driver', 'vImage', 'iDriverId', $iMemberId, '', 'true');
            $getDriverData = $obj->MySQLSelect("SELECT vImage,eStatus,vName,vLastName FROM register_driver WHERE iDriverId = '" . $iMemberId . "'");
            $OldImageName = $getDriverData[0]['vImage'];
            $checkEditProfileStatus = $generalobj->getEditDriverProfileStatus($getDriverData[0]['eStatus']); // Added By HJ On 13-11-2019 For Check Driver Profile Edit Status As Per Discuss With KS Sir
            if ($OldImageName != "" && $checkEditProfileStatus == "No") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_EDIT_PROFILE_DISABLED";
                setDataResponse($returnArr);
            }
            $where = " iDriverId = '" . $iMemberId . "'";
            $Data_passenger['vImage'] = $vImageName;
            $id = $obj->MySQLQueryPerform("register_driver", $Data_passenger, 'update', $where);
        }
        else {
            $OldImageName = get_value('register_user', 'vImgName', 'iUserId', $iMemberId, '', 'true');
            $where = " iUserId = '" . $iMemberId . "'";
            $Data_passenger['vImgName'] = $vImageName;
            $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where);
        }
        unlink($Photo_Gallery_folder . $OldImageName);
        unlink($Photo_Gallery_folder . "1_" . $OldImageName);
        unlink($Photo_Gallery_folder . "2_" . $OldImageName);
        unlink($Photo_Gallery_folder . "3_" . $OldImageName);
        if ($id > 0) {
            $returnArr['Action'] = "1";
            if ($memberType == "Driver") {
                $returnArr['message'] = getDriverDetailInfo($iMemberId);
            }
            else {
                $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
            }
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
//Added By HJ On 26-09-2019 For Merged checkBookings & getRideHistory & getOngoingUserTrips Type also With Driver and Passanger Side Start
if ($type == "getMemberBookings") {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    //optimized Done By HJ On 18-09-2019
    //echo "<pre>";print_R($_REQUEST);die;
    $vSubFilterParam = "";
    getMemberBookingData($vSubFilterParam);
}
//Added By HJ On 26-09-2019 For Merged checkBookings & getRideHistory & getOngoingUserTrips Type also With Driver and Passanger Side End
####################### getRideHistory #############################
if ($type == "getRideHistory") {
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $vFilterParam = isset($_REQUEST["vFilterParam"]) ? $_REQUEST["vFilterParam"] : ''; // Ride , Deliver Or UberX
    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        //$vLanguage = "EN";
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    if ($vFilterParam == "" || $vFilterParam == NULL) {
        $vFilterParam = "";
    }
    $ssql = "";
    ##  App Type Filtering ##
    if ($vFilterParam != "") {
        if ($vFilterParam == "Deliver") {
            $ssql .= " AND tr.eType IN ('Deliver','Multi-Delivery') ";
        }
        else if ($vFilterParam == "eFly") {
            $ssql .= " AND tr.iFromStationId != '' AND tr.iToStationId != ''";
        }
        else {
            $ssql .= " AND tr.eType IN ('" . $vFilterParam . "') ";
        }
        if (checkFlyStationsModule() && $vFilterParam == 'Ride') {
            $ssql .= " AND tr.iFromStationId = '' AND tr.iToStationId = ''";
        }
    }
    ##  App Type Filtering ##
    if ($iTripId != "" && $iTripId > 0) {
        $ssql = " and tr.iTripId='" . $iTripId . "'";
    }
    $ssql_fav_q = "";
    if (checkFavDriverModule() && $UserType == "Passenger") {
        $ssql_fav_q = getFavSelectQuery($iUserId);
    }
    //echo $ssql_fav_q;die;
    $per_page = 10;
    $sql_all = "SELECT COUNT(tr.iTripId) As TotalIds FROM trips tr WHERE  tr.iUserId='$iUserId' AND (tr.iActive='Canceled' || tr.iActive='Finished') AND tr.eSystem = 'General' $ssql";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    $sql = "SELECT tr.* " . $ssql_fav_q . " FROM `trips` as tr WHERE tr.iUserId='$iUserId' AND (tr.iActive='Canceled' || tr.iActive='Finished') AND tr.eSystem = 'General' $ssql ORDER BY tr.iTripId DESC" . $limit;
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);
    $i = 0;
    if (count($Data) > 0) {
        while (count($Data) > $i) {
            $returnArr = getTripPriceDetails($Data[$i]['iTripId'], $iUserId, "Passenger");
            $sql = "SELECT count(iRatingId) AS Total FROM `ratings_user_driver` WHERE iTripId = '" . $Data[$i]['iTripId'] . "' and eUserType = '$UserType'";
            $rating_check = $obj->MySQLSelect($sql);
            $returnArr['is_rating'] = 'No';
            if ($rating_check[0]['Total'] > 0) {
                $returnArr['is_rating'] = 'Yes';
            }
            $Data[$i] = array_merge($Data[$i], $returnArr);
            if ($Data[$i]["eType"] == 'UberX' && $Data[$i]["eFareType"] != "Regular") {
                $Data[$i]['tDaddress'] = "";
            }
            /* Added For Rental */
            if ($Data[$i]['iRentalPackageId'] > 0) {
                $rentalData = getRentalData($Data[$i]['iRentalPackageId']);
                $Data[$i]['vPackageName'] = $rentalData[0]['vPackageName_' . $vLanguage];
            }
            else {
                $Data[$i]['vPackageName'] = "";
            }
            if ($Data[$i]['eType'] == 'Ride' && $Data[$i]['eFly'] == 'Yes') {
                $Data[$i]['eType'] = 'Fly'; //it is given direct bc from app side compare this and label is shown from there so in other lang not an issue
                
            }
            /* End Added For Rental */
            $i++;
        }
        $returnData['message'] = $Data;
        $returnData['AppTypeFilterArr'] = AppTypeFilterArr($iUserId, $UserType, $vLanguage);
        if ($TotalPages > $page) {
            $returnData['NextPage'] = "" . ($page + 1);
        }
        else {
            $returnData['NextPage'] = "0";
        }
        $returnData['Action'] = "1";
        setDataResponse($returnData);
    }
    else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_DATA_AVAIL";
        $returnData['AppTypeFilterArr'] = AppTypeFilterArr($iUserId, $UserType, $vLanguage);
        setDataResponse($returnData);
    }
}
###########################################################################
if ($type == "submitRating") {
    //$iGeneralUserId = isset($_REQUEST["iGeneralUserId"]) ? $_REQUEST["iGeneralUserId"] : ''; // for both driver or passenger
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : ''; // for both driver or passenger
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $rating = isset($_REQUEST["rating"]) ? $_REQUEST["rating"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; // Passenger or Driver
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
    $isCollectTip = isset($_REQUEST["isCollectTip"]) ? $_REQUEST["isCollectTip"] : '';
    if ($isCollectTip == "" || $isCollectTip == NULL) {
        $isCollectTip = "No";
    }
    if(isset($tripDetailsArr['trips_'.$tripID])){
        $getTripData = $tripDetailsArr['trips_'.$tripID];
    }else{
        $getTripData = $obj->MySQLSelect("SELECT * FROM trips WHERE iTripId='" . $tripID . "'");
        $tripDetailsArr['trips_'.$tripID] = $getTripData;
    }
    //$eType = get_value('trips', 'eType', 'iTripId', $tripID, '', 'true');
    $message = stripslashes($message);
    $sql = "SELECT * FROM `ratings_user_driver` WHERE iTripId = '$tripID' and eUserType = '$userType'";
    $row_check = $obj->MySQLSelect($sql);
    //$ENABLE_TIP_MODULE=$generalobj->getConfigurations("configurations","ENABLE_TIP_MODULE");
    if (count($row_check) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_TRIP_FINISHED_TXT";
        setDataResponse($returnArr);
    }
    else {
        $eType = $getTripData[0]['eType'];
        $iDriverId = $getTripData[0]['iDriverId']; 
        $iUserId = $getTripData[0]['iUserId']; 
        $vTripPaymentMode = $getTripData[0]['vTripPaymentMode']; 
        # Code For Tip Charge #
        if ($isCollectTip == "Yes" && $userType == "Passenger" && $eType == "Ride" && $fAmount > 0) {
            TripCollectTip($iMemberId, $tripID, $fAmount);
        }
        # Code For Tip Charge #
        if ($userType == "Passenger") {
            //$iDriverId = get_value('trips', 'iDriverId', 'iTripId', $tripID, '', 'true');
            $tableName = "register_driver";
            $where = "iDriverId='" . $iDriverId . "'";
            $iMemberId = $iDriverId;
            $eToUserType = "Driver";
            $eFromUserType = "Passenger";
            if (checkFavDriverModule()) {
                addUpdateFavDriver();
            }
        } else {
            $where_trip = " iTripId = '$tripID'";
            $Data_update_trips['eVerified'] = "Verified";
            $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where_trip);
            //$iUserId = get_value('trips', 'iUserId', 'iTripId', $tripID, '', 'true');
            //$iDriverId = get_value('trips', 'iDriverId', 'iTripId', $tripID, '', 'true');
            //$eType = get_value('trips', 'eType', 'iTripId', $tripID, '', 'true');
            $tableName = "register_user";
            $where = "iUserId='" . $iUserId . "'";
            $iMemberId = $iUserId;
            $eToUserType = "Passenger";
            $eFromUserType = "Driver";
        }
        /* Insert records into ratings table */
        $Data_update_ratings['iTripId'] = $tripID;
        $Data_update_ratings['vRating1'] = $rating;
        $Data_update_ratings['vMessage'] = $message;
        $Data_update_ratings['eUserType'] = $userType;
        $Data_update_ratings['eFromUserType'] = $eFromUserType;
        $Data_update_ratings['eToUserType'] = $eToUserType;
        $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert'); // Remove Comment
        /* Set average rating for passenger OR Driver */
        // Driver gives rating to passenger and passenger gives rating to driver
        $Data_update['vAvgRating'] = getUserRatingAverage($iMemberId, $userType);
        $id = $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);
        if ($userType == "Passenger") {
            if ($eType == "Multi-Delivery") {
                sendTripReceipt_Multi($tripID);
            }else {
                sendTripReceipt($tripID);
            }
        }else {
            if ($eType == "Multi-Delivery") {
                sendTripReceiptAdmin_Multi($tripID);
            }else {
                sendTripReceiptAdmin($tripID);
            }
        }
        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_TRIP_FINISHED_TXT";
            $returnArr['eType'] = $eType;
            //$vTripPaymentMode = get_value('trips', 'vTripPaymentMode', 'iTripId', $tripID, '', 'true');
            if ($vTripPaymentMode == "Card") {
                $returnArr['ENABLE_TIP_MODULE'] = $ENABLE_TIP_MODULE;
            }
            else {
                $returnArr['ENABLE_TIP_MODULE'] = "No";
            }
            setDataResponse($returnArr);
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    }
}
###########################################################################
if ($type == "updatePassword") {
    $user_id = isset($_REQUEST["UserID"]) ? $_REQUEST["UserID"] : '';
    $Upass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? clean($_REQUEST["UserType"]) : ''; // UserType = Driver/Passenger
    $CurrentPassword = isset($_REQUEST["CurrentPassword"]) ? $_REQUEST["CurrentPassword"] : '';
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vPassword = get_value('register_user', 'vPassword', 'iUserId', $user_id, '', 'true');
    }
    else {
        $tblname = "register_driver";
        $vPassword = get_value('register_driver', 'vPassword', 'iDriverId', $user_id, '', 'true');
    }
    # Check For Valid password #
    if ($CurrentPassword != "") {
        $hash = $vPassword;
        $checkValidPass = $generalobj->check_password($CurrentPassword, $hash);
        if ($checkValidPass == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_PASSWORD";
            setDataResponse($returnArr);
        }
    }
    # Check For Valid password #
    $updatedPassword = $generalobj->encrypt_bycrypt($Upass);
    $Data_update_user['vPassword'] = $updatedPassword;
    if ($UserType == "Passenger") {
        $where = " iUserId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_user, 'update', $where);
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($user_id, "", "");
            setDataResponse($returnArr);
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    }
    else {
        $where = " iDriverId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_user, 'update', $where);
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = getDriverDetailInfo($user_id);
            setDataResponse($returnArr);
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    }
}
############################Send Sms Twilio####################################
if ($type == 'sendVerificationSMS') {
    $mobileNo = isset($_REQUEST['MobileNo']) ? clean($_REQUEST['MobileNo']) : '';
    $mobileNo = str_replace('+', '', $mobileNo);
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    $REQ_TYPE = isset($_REQUEST["REQ_TYPE"]) ? $_REQUEST['REQ_TYPE'] : '';
    if ($REQ_TYPE == "DO_EMAIL_PHONE_VERIFY" || $REQ_TYPE == "DO_PHONE_VERIFY") {
        CheckUserSmsLimit($iMemberId, $userType);
    }
    //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
    $isdCode = $SITE_ISD_CODE;
    //$toMobileNum= "+".$mobileNo;
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    else {
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
    //$prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];
    $verificationCode_sms = mt_rand(1000, 9999);
    $verificationCode_email = mt_rand(1000, 9999);
    $message = $prefix . ' ' . $verificationCode_sms;
    if ($iMemberId == "" && $REQ_TYPE == "DO_PHONE_VERIFY") {
        $toMobileNum = "+" . $mobileNo;
    }
    else {
        $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
        $db_member = $obj->MySQLSelect($sql);
        $Data_Mail['vEmail'] = isset($db_member[0]['vEmail']) ? $db_member[0]['vEmail'] : '';
        $vFirstName = isset($db_member[0]['vName']) ? $db_member[0]['vName'] : '';
        $vLastName = isset($db_member[0]['vLastName']) ? $db_member[0]['vLastName'] : '';
        $Data_Mail['vName'] = $vFirstName . " " . $vLastName;
        $Data_Mail['CODE'] = $verificationCode_email;
        $mobileNo = $db_member[0]['vPhoneCode'] . $db_member[0]['vPhone'];
        $toMobileNum = "+" . $mobileNo;
    }
    /********************** Firebase SMS Verfication **********************************/
    $returnArr['MOBILE_NO_VERIFICATION_METHOD'] = $MOBILE_NO_VERIFICATION_METHOD;
    /********************** Firebase SMS Verfication **********************************/
    $emailmessage = "";
    $phonemessage = "";
    if ($REQ_TYPE == "DO_EMAIL_PHONE_VERIFY") {
        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail != true || $sendemail != "true" || $sendemail != "1") {
            $sendemail = 0;
        }
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
        if ($userType == "Passenger") {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iMemberId AND r.vCountry = c.vCountryCode");
        }
        else {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iMemberId AND r.vCountry = c.vCountryCode");
        }
        $PhoneCode = $passengerData[0]['vPhoneCode'];
        /********************** Firebase SMS Verfication **********************************/
        if(strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE"){
        $result = $generalobj->sendSystemSms($toMobileNum, $PhoneCode, $message);
        }else{
            $result = 1;
        }
        /********************** Firebase SMS Verfication **********************************/
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
        /* $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iMemberId AND r.vCountry = c.vCountryCode");
        
          $PhoneCode = $passengerData[0]['vPhoneCode'];
        
        
        
          $result = $generalobj->sendSystemSms($toMobileNum,$PhoneCode,$message); */
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
        /* $result = sendEmeSms($toMobileNum, $message);
        
          if ($result == 0) {
        
          $toMobileNum = "+" . $isdCode . $mobileNo;
        
          $result = sendEmeSms($toMobileNum, $message);
        
          } */
        if ($result == 1) {
            UpdateUserSmsLimit($iMemberId, $userType);
        }
        $returnArr['Action'] = "1";
        if ($sendemail == 0 && $result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ACC_VERIFICATION_FAILED";
        }
        else {
            $returnArr['message_sms'] = $result == 0 ? "LBL_MOBILE_VERIFICATION_FAILED_TXT" : $verificationCode_sms;
            $returnArr['eSMSFailed'] = "No";
            if ($returnArr['message_sms'] == "LBL_MOBILE_VERIFICATION_FAILED_TXT") {
                $returnArr['eSMSFailed'] = "Yes";
            }
            $returnArr['message_email'] = $sendemail == 0 ? "LBL_EMAIL_VERIFICATION_FAILED_TXT" : $verificationCode_email;
            $returnArr['eEmailFailed'] = "No";
            if ($returnArr['message_email'] == "LBL_EMAIL_VERIFICATION_FAILED_TXT") {
                $returnArr['eEmailFailed'] = "Yes";
            }
        }
        setDataResponse($returnArr);
    }
    else if ($REQ_TYPE == "DO_PHONE_VERIFY") {
        /* $result = sendEmeSms($toMobileNum, $message);
        
          if ($result == 0) {
        
          $toMobileNum = "+" . $isdCode . $mobileNo;
        
          $result = sendEmeSms($toMobileNum, $message);
        
          } */
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
        if ($userType == "Passenger") {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iMemberId AND r.vCountry = c.vCountryCode");
        }
        else {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iMemberId AND r.vCountry = c.vCountryCode");
        }
        $PhoneCode = $passengerData[0]['vPhoneCode'];
        /********************** Firebase SMS Verfication **********************************/
        if(strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE"){
        $result = $generalobj->sendSystemSms($toMobileNum, $PhoneCode, $message);
        }else{
            $result = 1;
        }
        /********************** Firebase SMS Verfication **********************************/
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
        /* $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iMemberId AND r.vCountry = c.vCountryCode");
        
          $PhoneCode = $passengerData[0]['vPhoneCode'];
        
        
        
          $result = $generalobj->sendSystemSms($toMobileNum,$PhoneCode,$message); */
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
        if ($result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_MOBILE_VERIFICATION_FAILED_TXT";
            setDataResponse($returnArr);
        }
        else {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $verificationCode_sms;
            UpdateUserSmsLimit($iMemberId, $userType);
            setDataResponse($returnArr);
        }
    }
    else if ($REQ_TYPE == "DO_EMAIL_VERIFY") {
        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail != true || $sendemail != "true" || $sendemail != "1") {
            $sendemail = 0;
        }
        if ($sendemail == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EMAIL_VERIFICATION_FAILED_TXT";
            setDataResponse($returnArr);
        }
        else {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $Data_Mail['CODE'];
            setDataResponse($returnArr);
        }
    }
    else if ($REQ_TYPE == "EMAIL_VERIFIED") {
        $where = " " . $condfield . " = '" . $iMemberId . "'";
        $Data['eEmailVerified'] = "Yes";
        $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);
        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_EMAIl_VERIFIED";
            if ($userType == 'Passenger') {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getPassengerDetailInfo($iMemberId, "", "");
            }
            else {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getDriverDetailInfo($iMemberId);
            }
            setDataResponse($returnArr);
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EMAIl_VERIFIED_ERROR";
            setDataResponse($returnArr);
        }
    }
    else if ($REQ_TYPE == "PHONE_VERIFIED") {
        $where = " " . $condfield . " = '" . $iMemberId . "'";
        $Data['ePhoneVerified'] = "Yes";
        $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);
        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_PHONE_VERIFIED";
            if ($userType == 'Passenger') {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getPassengerDetailInfo($iMemberId, "", "");
            }
            else {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getDriverDetailInfo($iMemberId);
            }
            setDataResponse($returnArr);
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PHONE_VERIFIED_ERROR";
            setDataResponse($returnArr);
        }
    }
    //  $returnArr['message'] =$verificationCode;
    // setDataResponse($returnArr);
    
}
###########################################################################
if ($type == "loadDriverFeedBack") {
    global $generalobj, $tconfig;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $SelectedCabType = isset($_REQUEST["SelectedCabType"]) ? $_REQUEST["SelectedCabType"] : '';
    $whereUberXTrip = "";
    if ($SERVICE_PROVIDER_FLOW == "Provider" && $SelectedCabType = "UberX") {
        $whereUberXTrip = " AND tr.eType='" . $SelectedCabType . "'";
    }
    $vAvgRating = get_value('register_driver', 'vAvgRating', 'iDriverId', $iDriverId, '', 'true');
    $per_page = 10;
    if (DELIVERALL == "Yes") {
        $sql_all = "SELECT COUNT(rate.iTripId) As TotalTripIds FROM ratings_user_driver as rate LEFT JOIN trips as tr ON tr.iTripId = rate.iTripId WHERE  tr.iDriverId='$iDriverId' AND tr.iActive='Finished' AND tr.eHailTrip='No' $whereUberXTrip UNION SELECT COUNT(o.iOrderId) As TotalTripIds FROM orders as o LEFT JOIN ratings_user_driver as rate on rate.iOrderId = o.iOrderId WHERE o.iDriverId='$iDriverId' AND o.iStatusCode='6' AND rate.eToUserType = '" . $UserType . "' ";
    }
    else {
        $sql_all = "SELECT COUNT(iTripId) As TotalIds FROM trips WHERE  iDriverId='$iDriverId' AND iActive='Finished' AND eHailTrip='No'";
    }
    $data_count_all = $obj->MySQLSelect($sql_all);
    $data_count_all[0]['TotalIds'] = $data_count_all[0]['TotalTripIds'] + $data_count_all[1]['TotalTripIds'];
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    if (DELIVERALL == "Yes") {
        $sql = "(SELECT rate.*,CONCAT(ru.vName,' ',ru.vLastName) as vName,ru.iUserId as passengerid,ru.vImgName FROM ratings_user_driver as rate LEFT JOIN trips as tr ON tr.iTripId = rate.iTripId  LEFT JOIN register_user as ru ON ru.iUserId = tr.iUserId WHERE tr.iDriverId='$iDriverId' AND tr.iActive='Finished' AND tr.eHailTrip='No' AND rate.eToUserType = '" . $UserType . "' ORDER BY tr.iTripId DESC" . $limit . ")

		UNION

		( SELECT rate.*,CONCAT(ru.vName,' ',ru.vLastName) as vName,ru.iUserId as passengerid,ru.vImgName FROM ratings_user_driver as rate LEFT JOIN orders as o ON o.iOrderId = rate.iOrderId  LEFT JOIN register_user as ru ON ru.iUserId = o.iUserId WHERE o.iDriverId='$iDriverId' AND o.iStatusCode='6' AND rate.eToUserType = '" . $UserType . "' ORDER BY o.iOrderId DESC" . $limit . ")";
    }
    else {
        $sql = "SELECT rate.*,CONCAT(ru.vName,' ',ru.vLastName) as vName,ru.iUserId as passengerid,ru.vImgName FROM ratings_user_driver as rate LEFT JOIN trips as tr ON tr.iTripId = rate.iTripId  LEFT JOIN register_user as ru ON ru.iUserId = tr.iUserId WHERE tr.iDriverId='$iDriverId' AND tr.iActive='Finished' AND tr.eHailTrip='No' AND rate.eUserType='Passenger' ORDER BY tr.iTripId DESC" . $limit;
    }
    $Data = $obj->MySQLSelect($sql);
    for ($i = 0;$i < count($Data);$i++) {
        $Data[$i]['vImage'] = $tconfig["tsite_upload_images_passenger"] . '/' . $Data[$i]['passengerid'] . '/3_' . $Data[$i]['vImgName'];
        $Data[$i]['tDateOrig'] = $Data[$i]['tDate'];
        $Data[$i]['tDate'] = $generalobj->DateTime($Data[$i]['tDate'], 14);
    }
    $totalNum = count($Data);
    ### Load Ratings for Food Trips ###
    if (count($Data) > 0) {
        $returnData['message'] = $Data;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        }
        else {
            $returnData['NextPage'] = "0";
        }
        $returnData['vAvgRating'] = strval($vAvgRating);
        $returnData['Action'] = "1";
        setDataResponse($returnData);
    }
    else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_FEEDBACK";
        setDataResponse($returnData);
    }
}
###########################################################################
if ($type == "loadEmergencyContacts") {
    global $generalobj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : 'Passenger';
    if ($UserType == "") {
        $UserType = $GeneralUserType;
    }
    $sql = "SELECT * FROM user_emergency_contact WHERE iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "'";
    $data = $obj->MySQLSelect($sql);
    if (count($data) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $data;
    }
    else {
        $returnData['Action'] = "0";
    }
    setDataResponse($returnData);
}
###########################################################################
if ($type == "addEmergencyContacts") {
    global $generalobj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '0';
    $vName = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $sql = "SELECT vPhone FROM user_emergency_contact WHERE iUserId = '" . $iUserId . "' AND vPhone='" . $Phone . "' AND eUserType='" . $UserType . "'";
    $Data_Exist = $obj->MySQLSelect($sql);
    if (count($Data_Exist) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_EME_CONTACT_EXIST";
    }
    else {
        $Data['vName'] = $vName;
        $Data['vPhone'] = $Phone;
        $Data['iUserId'] = $iUserId;
        $Data['eUserType'] = $UserType;
        $id = $obj->MySQLQueryPerform("user_emergency_contact", $Data, 'insert');
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_EME_CONTACT_LIST_UPDATE";
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "deleteEmergencyContacts") {
    global $generalobj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iEmergencyId = isset($_REQUEST["iEmergencyId"]) ? $_REQUEST["iEmergencyId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $sql = "DELETE FROM user_emergency_contact WHERE `iEmergencyId`='" . $iEmergencyId . "' AND `iUserId`='" . $iUserId . "' AND eUserType = '" . $UserType . "'";
    $id = $obj->sql_query($sql);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EME_CONTACT_LIST_UPDATE";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "sendAlertToEmergencyContacts") {
    global $generalobj, $obj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    //CheckUserSmsLimitForEmergency($iUserId, $UserType);
    $sql = "SELECT * FROM user_emergency_contact WHERE iUserId = '" . $iUserId . "' AND eUserType='" . $UserType . "'";
    $dataArr = $obj->MySQLSelect($sql);
    if ($iTripId == "" || $iTripId == "0") {
        $tableName = $UserType != "Driver" ? "register_user" : "register_driver";
        $iMemberId_KEY = $UserType != "Driver" ? "iUserId" : "iDriverId";
        $iTripId = get_value($tableName, 'iTripId', $iMemberId_KEY, $iUserId, '', 'true');
    }
    if (count($dataArr) > 0) {
        $sql = "SELECT tr.*,dv.vLicencePlate,CONCAT(rd.vName,' ',rd.vLastName) as vDriverName,rd.vPhone as DriverPhone,CONCAT(ru.vName,' ',ru.vLastName) as vPassengerName,ru.vPhone as PassengerPhone FROM trips as tr, register_driver as rd, register_user as ru, driver_vehicle as dv WHERE tr.iTripId = '" . $iTripId . "' AND rd.iDriverId = tr.iDriverId AND ru.iUserId = tr.iUserId AND dv.iDriverVehicleId = tr.iDriverVehicleId";
        $tripData = $obj->MySQLSelect($sql);
        //$tripData[0]['tStartDate'] = ($tripData[0]['tStartDate'] == '0000-00-00 00:00:00')? $tripData[0]['tTripRequestDate'] : $tripData[0]['tStartDate'];
        $tStartDate = ($tripData[0]['tStartDate'] == '0000-00-00 00:00:00') ? $tripData[0]['tTripRequestDate'] : $tripData[0]['tStartDate'];
        $systemTimeZone = date_default_timezone_get();
        $vTimeZone = $tripData[0]['vTimeZone'];
        $iDriverId = $tripData[0]['iDriverId'];
        $tStartDate = converToTz($tStartDate, $vTimeZone, $systemTimeZone);
        $tripData[0]['tStartDate'] = $tStartDate;
        $tTripRequestDate = $tripData[0]['tTripRequestDate'];
        $tTripRequestDate = converToTz($tTripRequestDate, $vTimeZone, $systemTimeZone);
        $tripData[0]['tTripRequestDate'] = $tTripRequestDate;
        $eType = $tripData[0]['eType'];
        $isdCode = $SITE_ISD_CODE;
        $dataArraySMS = array();
        $dataArraySMS['PassengerName'] = $tripData[0]['vPassengerName'];
        $dataArraySMS['PassengerPhone'] = $tripData[0]['PassengerPhone'];
        $dataArraySMS['SITE_NAME'] = $SITE_NAME;
        $dataArraySMS['TripRequestDate'] = date('dS M \a\t h:i a', strtotime($tripData[0]['tTripRequestDate']));
        $dataArraySMS['Saddress'] = $tripData[0]['tSaddress'];
        $dataArraySMS['DriverName'] = $tripData[0]['vDriverName'];
        $dataArraySMS['DriverPhone'] = $tripData[0]['DriverPhone'];
        $trackingURL = getGooglelocatiotionTrackingURL($iTripId, $iDriverId);
        $trackingFinalMessage = "." . $trackingURL;
        if (isset($trackingURL) && !empty($trackingURL)) {
            $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
            $trackingMessage = $languageLabelsArr['LBL_PLEASE_CHECK_LAST_LOCATION_TRACKING_MESSAGE'];
            //$trackingFinalMessage = ".".$trackingMessage.' '.$trackingURL;
            $trackingFinalMessage = $trackingURL;
        }
        $dataArraySMSNew['SITE_NAME'] = $SITE_NAME;
        $dataArraySMSNew['vDriverName'] = $tripData[0]['vDriverName'];
        $dataArraySMSNew['DriverPhone'] = $tripData[0]['DriverPhone'];
        $dataArraySMSNew['tStartDate'] = date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate']));
        $dataArraySMSNew['tSaddress'] = $tripData[0]['tSaddress'];
        $dataArraySMSNew['vPassengerName'] = $tripData[0]['vPassengerName'];
        $dataArraySMSNew['PassengerPhone'] = $tripData[0]['PassengerPhone'];
        $dataArraySMSNew['vLicencePlate'] = $tripData[0]['vLicencePlate'];
        $dataArraySMSNew['trackingURL'] = $trackingFinalMessage;
        if ($eType == "UberX") {
            if ($UserType == "Passenger") {
                $message = $generalobj->send_messages_user('EMERGENCY_SMS_FOR_USER_SP', $dataArraySMSNew, "", $vLangCode);
                // $message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the Job are: Job start time: ' . date('dS M \a\t h:i a', strtotime($tripData[0]['tTripRequestDate'])) . '. Job Address: ' . $tripData[0]['tSaddress'] . '. Service Provider name: ' . $tripData[0]['vDriverName'] . '. Service Provider number:(' . $tripData[0]['DriverPhone'] . ")".$trackingFinalMessage;
                
            }
            else {
                //$message = "Important: " . $tripData[0]['vDriverName'] . ' (' . $tripData[0]['DriverPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the Job are: Job start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Job Address: ' . $tripData[0]['tSaddress'] . '. User name: ' . $tripData[0]['vPassengerName'] . $tripData[0]['vPassengerName'];
                $message = $generalobj->send_messages_user('EMERGENCY_SMS_FOR_DRIVER_SP', $dataArraySMSNew, "", $vLangCode);
            }
        }
        else if ($eType == "Deliver") {
            if ($UserType == "Passenger") {
                //$message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the delivery are: Delivery start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Delivery Driver name: ' . $tripData[0]['vDriverName'] . '. Delivery Driver number:(' . $tripData[0]['DriverPhone'] . "). Delivery Driver's car number: " . $tripData[0]['vLicencePlate'].$trackingFinalMessage;
                $message = $generalobj->send_messages_user('EMERGENCY_SMS_FOR_USER_SP_DELIVER', $dataArraySMSNew, "", $vLangCode);
            }
            else {
                //$message = "Important: " . $tripData[0]['vDriverName'] . ' (' . $tripData[0]['DriverPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the delivery are: Delivery start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Sender name: ' . $tripData[0]['vPassengerName'] . '. Sender number:(' . $tripData[0]['PassengerPhone'] . "). Delivery Driver's car number: " . $tripData[0]['vLicencePlate'].$trackingFinalMessage;
                $message = $generalobj->send_messages_user('EMERGENCY_SMS_FOR_DRIVER_SP_DELIVER', $dataArraySMSNew, "", $vLangCode);
            }
        }
        else {
            if ($UserType == "Passenger") {
                //$message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the ride are: Trip start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Driver name: ' . $tripData[0]['vDriverName'] . '. Driver number:(' . $tripData[0]['DriverPhone'] . "). Driver's car number: " . $tripData[0]['vLicencePlate'].$trackingFinalMessage;
                $message = $generalobj->send_messages_user('EMERGENCY_SMS_FOR_USER_SP_RIDER', $dataArraySMSNew, "", $vLangCode);
            }
            else {
                //$message = "Important: " . $tripData[0]['vDriverName'] . ' (' . $tripData[0]['DriverPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the ride are: Trip start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Passenger name: ' . $tripData[0]['vPassengerName'] . '. Passenger number:(' . $tripData[0]['PassengerPhone'] . "). Driver's car number: " . $tripData[0]['vLicencePlate'].$trackingFinalMessage;
                $message = $generalobj->send_messages_user('EMERGENCY_SMS_FOR_DRIVER_SP_RIDER', $dataArraySMSNew, "", $vLangCode);
            }
        }
        for ($i = 0;$i < count($dataArr);$i++) {
            $phone = preg_replace("/[^0-9]/", "", $dataArr[$i]['vPhone']);
            $toMobileNum = "+" . $phone;
            //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
            if ($UserType == "Passenger") {
                $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
            }
            else {
                $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iUserId AND r.vCountry = c.vCountryCode");
            }
            $PhoneCode = $passengerData[0]['vPhoneCode'];
            $result = $generalobj->sendSystemSms($toMobileNum, $PhoneCode, $message);
            //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
            /* $toMobileNum = "+" . $phone;
            
            
            
              $result = sendEmeSms($toMobileNum, $message);
            
              if ($result == 0) {
            
              $toMobileNum = "+" . $isdCode . $phone;
            
              sendEmeSms($toMobileNum, $message);
            
              } */
        }
        UpdateUserSmsLimitForEmergency($iUserId, $UserType);
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EME_CONTACT_ALERT_SENT";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ADD_EME_CONTACTS";
        $returnArr['message1'] = "ContactError";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "CheckCard") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    //$iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $UserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : 'Passenger';
    if ($UserType == 'Driver') {
        $vStripeCusId = get_value('register_driver', 'vStripeCusId', 'iDriverId', $iUserId, '', 'true');
    }
    else {
        $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    if ($APP_PAYMENT_METHOD == "Stripe") {
        if ($vStripeCusId != "") {
            try {
                $customer = Stripe_Customer::retrieve($vStripeCusId);
                $sources = $customer->sources;
                $data = $sources->data;
                $cvc_check = $data[0]['cvc_check'];
                if ($cvc_check && $cvc_check == "pass") {
                    $returnArr['Action'] = "1";
                }
                else {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_INVALID_CARD";
                }
            }
            catch(Exception $e) {
                $error3 = $e->getMessage();
                $returnArr['Action'] = "0";
                $returnArr['message'] = $error3;
                //$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
                
            }
        }
        else /* if ($APP_PAYMENT_METHOD == "Braintree") */ {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }
    else if ($APP_PAYMENT_METHOD == "Flutterwave") {
        if ($UserType == 'Driver') {
            $vFlutterWaveToken = get_value('register_driver', 'vFlutterWaveToken', 'iDriverId', $iUserId, '', 'true');
        }
        else {
            $vFlutterWaveToken = get_value('register_user', 'vFlutterWaveToken', 'iUserId', $iUserId, '', 'true');
        }
        if ($vFlutterWaveToken != "") {
            $returnArr['Action'] = "1";
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }
    else {
        $returnArr['Action'] = "1";
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "getTransactionHistory") {
    global $generalobj;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $tripTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $ListType = isset($_REQUEST["ListType"]) ? $_REQUEST["ListType"] : 'All';
    if ($page == "0" || $page == 0) {
        $page = 1;
    }
    if ($UserType == "Passenger") {
        $UserType = "Rider";
    }
    $ssql = '';
    if ($ListType != "All") {
        $ssql .= " AND eType ='" . $ListType . "'";
    }
    $per_page = 10;
    $sql_all = "SELECT COUNT(iUserWalletId) As TotalIds FROM user_wallet WHERE  iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    $sql = "SELECT * from user_wallet where iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ORDER BY iUserWalletId DESC" . $limit;
    $Data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($Data);die;
    $totalNum = count($Data);
    $vSymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
    if ($UserType == 'Driver') {
        $UserData = get_value('register_driver', 'vCurrencyDriver,vLang', 'iDriverId', $iUserId);
        $uservSymbol = $UserData[0]['vCurrencyDriver'];
        $vLangCode = $UserData[0]['vLang'];
    }
    else {
        $UserData = get_value('register_user', 'vCurrencyPassenger,vLang', 'iUserId', $iUserId);
        $uservSymbol = $UserData[0]['vCurrencyPassenger'];
        $vLangCode = $UserData[0]['vLang'];
    }
    $userCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $uservSymbol, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $i = 0;
    if (count($Data) > 0) {
        $row = $Data;
        $prevbalance = 0;
        while (count($row) > $i) {
            if (!empty($row[$i]['tDescription'])) {
                $pat = '/\#([^\"]*?)\#/';
                preg_match($pat, $row[$i]['tDescription'], $tDescription_value);
                $tDescription_translate = $languageLabelsArr[$tDescription_value[1]];
                $row[$i]['tDescription'] = str_replace($tDescription_value[0], $tDescription_translate, $row[$i]['tDescription']);
            }
            // Convert Into Timezone
            if ($tripTimeZone != "") {
                $serverTimeZone = date_default_timezone_get();
                $row[$i]['dDate'] = converToTz($row[$i]['dDate'], $tripTimeZone, $serverTimeZone);
            }
            // Convert Into Timezone
            if ($row[$i]['eType'] == "Credit") {
                $row[$i]['currentbal'] = $prevbalance + $row[$i]['iBalance'];
            }
            else {
                $row[$i]['currentbal'] = $prevbalance - $row[$i]['iBalance'];
            }
            $prevbalance = $row[$i]['currentbal'];
            $row[$i]['dDateOrig'] = $row[$i]['dDate'];
            $row[$i]['dDate'] = date('d-M-Y', strtotime($row[$i]['dDate']));
            $row[$i]['currentbal'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['currentbal'], $uservSymbol);
            $row[$i]['iBalance'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['iBalance'], $uservSymbol);
            $i++;
        }
        $returnData['message'] = $row;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        }
        else {
            $returnData['NextPage'] = 0;
        }
        $user_available_balance = $generalobj->get_user_available_balance_app_display($iUserId, $UserType, '', 'Yes');
        $returnData['user_available_balance_default'] = $user_available_balance['DISPLAY_AMOUNT'];
        $returnData['user_available_balance'] = strval($user_available_balance['DISPLAY_AMOUNT']);
        $returnData["MemberBalance"] = strval($user_available_balance['DISPLAY_AMOUNT']);
        $returnData['user_available_balance'] = strval($user_available_balance['DISPLAY_AMOUNT']);
        $returnData['user_available_balance_amount'] = strval($user_available_balance['ORIG_AMOUNT']);
        $returnData['Action'] = "1";
        #echo "<pre>"; print_r($returnData); exit;
        if ($UserType == 'Driver') {
            $walletSql = "SELECT (SELECT SUM(iBalance) FROM `user_wallet` WHERE iUserId = '" . $iUserId . "' AND eFor = 'Referrer') as REFERRAL_AMOUNT, (SELECT SUM(iBalance) FROM `user_wallet` WHERE iUserId = '" . $iUserId . "' AND eType = 'Credit') as CREDIT_AMOUNT, (SELECT SUM(iBalance) FROM `user_wallet` WHERE iUserId = '" . $iUserId . "' AND eType = 'DEBIT') as DEBIT_AMOUNT";
            $walletSqlData = $obj->MySQLSelect($walletSql);
            $ref_deb_diff = $walletSqlData[0]['REFERRAL_AMOUNT'] - $walletSqlData[0]['DEBIT_AMOUNT'];
            $non_withdrawable_amount = ($ref_deb_diff < 0) ? 0 : $ref_deb_diff;
            $non_withdrawable_amount = number_format($non_withdrawable_amount, 2, '.', '');
            $withdrawable_amount = $walletSqlData[0]['CREDIT_AMOUNT'] + ($walletSqlData[0]['REFERRAL_AMOUNT'] - $walletSqlData[0]['DEBIT_AMOUNT']);
            $withdrawable_amount = ($ref_deb_diff > 0) ? ($withdrawable_amount - $ref_deb_diff) : $withdrawable_amount;
            $withdrawable_amount = number_format($withdrawable_amount, 2, '.', '');
            $sqld = "SELECT rd.vCurrencyDriver as vCurrency,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iUserId . "'";
            $db_currency = $obj->MySQLSelect($sqld);
            if ($vCurrency == "" || $vCurrency == null) {
                $sql = "SELECT vName,vSymbol,Ratio from currency WHERE eDefault = 'Yes'";
                $db_currency = $obj->MySQLSelect($sql);
            }
            $vCurrency = $db_currency[0]['vCurrency'];
            $vSymbol = $db_currency[0]['vSymbol'];
            $Ratio = $db_currency[0]['Ratio'];
            $returnData['WITHDRAWABLE_AMOUNT'] = $vSymbol . ' ' . ($withdrawable_amount * $Ratio);
            $returnData['ORIG_WITHDRAWABLE_AMOUNT'] = ($withdrawable_amount * $Ratio);
            $returnData['NON_WITHDRAWABLE_AMOUNT'] = $vSymbol . ' ' . ($non_withdrawable_amount * $Ratio);
            $returnData['ORIG_NON_WITHDRAWABLE_AMOUNT'] = ($withdrawable_amount * $Ratio);
            $vAccountNumber = get_value('register_driver', 'vAccountNumber', 'iDriverId', $iUserId);
            $returnData['vAccountNumber'] = ($vAccountNumber[0]['vAccountNumber'] != "") ? 'Yes' : 'No';
            $returnData['ACCOUNT_NO'] = ($vAccountNumber[0]['vAccountNumber'] != "") ? $vAccountNumber[0]['vAccountNumber'] : 'XXXXXXX';
        }
        setDataResponse($returnData);
    }
    else {
        $returnData['Action'] = "1";
        $returnData['message'] = "LBL_NO_TRANSACTION_AVAIL";
        $returnData['user_available_balance'] = $returnData['MemberBalance'] = $userCurrencySymbol . "0.00";
        setDataResponse($returnData);
    }
}
// ############################## Create withdrawl request #############################################
if ($type == "createWithdrawlRequest") {
    $iUserId = isset($_REQUEST['iMemberId']) ? $_REQUEST['iMemberId'] : '';
    $eUserType = isset($_REQUEST['UserType']) ? $_REQUEST['UserType'] : '';
    $sql = "SELECT * vBankAccountHolderName,vAccountNumber,vBankLocation,vBankName,vBIC_SWIFT_Code FROM register_driver WHERE iDriverId = " . $iUserId;
    $userData = $obj->MySQLSelect($sql);
    $vHolderName = $vBankName = $iBankAccountNo = $BICSWIFTCode = $vBankBranch = "";
    if (!empty($userData)) {
        $bank_details = $userData[0];
        $vHolderName = ($bank_details['vBankAccountHolderName'] != "") ? $bank_details['vBankAccountHolderName'] : '';
        $vBankName = ($bank_details['vBankName'] != "") ? $bank_details['vBankName'] : '';
        $iBankAccountNo = ($bank_details['vAccountNumber'] != "") ? $bank_details['vAccountNumber'] : '';
        $BICSWIFTCode = ($bank_details['vBIC_SWIFT_Code'] != "") ? $bank_details['vBIC_SWIFT_Code'] : '';
        $vBankBranch = ($bank_details['vBankLocation'] != "") ? $bank_details['vBankLocation'] : '';
    }
    if ($eUserType == 'Driver') {
        $tblname = 'register_driver';
        $usercurr = 'Driver';
        $where = "WHERE iDriverId = '" . $iUserId . "'";
    }
    else {
        $tblname = 'register_user';
        $usercurr = 'Passenger';
        $where = "WHERE iUserId = '" . $iUserId . "'";
    }
    $sql = "select vName, vLastName, vEmail, vCurrency" . $usercurr . " as sess_vCurrency, vPhone from " . $tblname . " " . $where;
    $db_user = $obj->MySQLSelect($sql);
    //$db_user[0]['sess_vCurrency'] = 'INR';
    $sql = "select vName, Ratio from currency where vName = '" . $db_user[0]['sess_vCurrency'] . "'";
    $db_currency = $obj->MySQLSelect($sql);
    $sql = "select vName, Ratio from currency where eDefault = 'Yes'";
    $db_currency_admin = $obj->MySQLSelect($sql);
    $User_Available_Balance = $generalobj->get_user_available_balance($iUserId, $UserType);
    $fAmount = $_REQUEST['amount'];
    //$fcheckamount = round($fAmount * $db_currency[0]['Ratio'],2);
    $fcheckamount = round($fAmount, 2); //changed by SP withdraw request  for different currency wrong msg shown bc user enters in his currency only so no need to multiplied it from issue#329 on 03-10-2019
    $withdrawalamtuser = $generalobj->get_currency_with_symbol($fAmount, $db_user[0]['sess_vCurrency']);
    $withdrawalamtadmin = $generalobj->get_currency_with_symbol($fcheckamount, $db_currency_admin[0]['vName']);
    $availableAmountOfUser = round($User_Available_Balance * $db_currency[0]['Ratio'], 2); // Added By HJ On 30-09-2019 For Solved Sheet Issue #http://mobileappsdemo.com/support-system/view.php?id=8131
    $walletSql = "SELECT (SELECT SUM(iBalance) FROM `user_wallet` WHERE iUserId = '" . $iUserId . "' AND eFor = 'Referrer') as REFERRAL_AMOUNT, (SELECT SUM(iBalance) FROM `user_wallet` WHERE iUserId = '" . $iUserId . "' AND eType = 'Credit') as CREDIT_AMOUNT, (SELECT SUM(iBalance) FROM `user_wallet` WHERE iUserId = '" . $iUserId . "' AND eType = 'DEBIT') as DEBIT_AMOUNT";
    $walletSqlData = $obj->MySQLSelect($walletSql);
    $ref_deb_diff = $walletSqlData[0]['REFERRAL_AMOUNT'] - $walletSqlData[0]['DEBIT_AMOUNT'];
    $withdrawable_amount = $walletSqlData[0]['CREDIT_AMOUNT'] + ($walletSqlData[0]['REFERRAL_AMOUNT'] - $walletSqlData[0]['DEBIT_AMOUNT']);
    $withdrawable_amount = ($ref_deb_diff > 0) ? ($withdrawable_amount - $ref_deb_diff) : $withdrawable_amount;
    $withdrawable_amount = number_format($withdrawable_amount, 2, '.', '');
    $withdrawable_amount = ($withdrawable_amount * $db_currency[0]['Ratio']);
    if ($fcheckamount > $availableAmountOfUser) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'LBL_WITHDRAW_AMT_VALIDATION_MSG';
        setDataResponse($returnArr);
    }
    else {
        /* Admin mail */
        $maildataadmin['User_Name'] = $db_user[0]['vName'] . " " . $db_user[0]['vLastName'];
        $maildataadmin['User_Phone'] = $db_user[0]['vPhone'];
        $maildataadmin['User_Email'] = $db_user[0]['vEmail'];
        $maildataadmin['Account_Name'] = $vHolderName;
        $maildataadmin['Bank_Name'] = $vBankName;
        $maildataadmin['Account_Number'] = $iBankAccountNo;
        $maildataadmin['BIC/SWIFT_Code'] = $BICSWIFTCode;
        $maildataadmin['Bank_Branch'] = $vBankBranch;
        $maildataadmin['Withdrawal_amount'] = $withdrawalamtadmin;
        $res = $generalobj->send_email_user("WITHDRAWAL_MONEY_REQUEST_Admin", $maildataadmin);
        //User Mail
        $maildata['User_Name'] = $db_user[0]['vName'] . " " . $db_user[0]['vLastName'];
        $maildata['Withdrawal_amount'] = $withdrawalamtuser;
        $maildata['User_Email'] = $db_user[0]['vEmail'];
        $generalobj->send_email_user("WITHDRAWAL_MONEY_REQUEST_USER", $maildata);
        $returnArr['Action'] = "1";
        $returnArr['message'] = 'LBL_WITHDRAW_AMT_SUCCESS_MSG';
        setDataResponse($returnArr);
    }
}
// ############################## Create withdrawl request End #########################################
if ($type == "getTransactionHistory1") {
    global $generalobj;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $tripTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $ListType = isset($_REQUEST["ListType"]) ? $_REQUEST["ListType"] : 'All';
    if ($page == "0" || $page == 0) {
        $page = 1;
    }
    if ($UserType == "Passenger") {
        $UserType = "Rider";
    }
    $ssql = '';
    if ($ListType != "All") {
        $ssql .= " AND eType ='" . $ListType . "'";
    }
    $per_page = 10;
    $sql_all = "SELECT COUNT(iUserWalletId) As TotalIds FROM user_wallet WHERE  iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    $sql = "SELECT * from user_wallet where iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ORDER BY iUserWalletId DESC" . $limit;
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);
    $vSymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
    if ($UserType == 'Driver') {
        $UserData = get_value('register_driver', 'vCurrencyDriver,vLang', 'iDriverId', $iUserId);
        $uservSymbol = $UserData[0]['vCurrencyDriver'];
        $vLangCode = $UserData[0]['vLang'];
    }
    else {
        $UserData = get_value('register_user', 'vCurrencyPassenger,vLang', 'iUserId', $iUserId);
        $uservSymbol = $UserData[0]['vCurrencyPassenger'];
        $vLangCode = $UserData[0]['vLang'];
    }
    $userCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $uservSymbol, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $i = 0;
    if (count($Data) > 0) {
        $row = $Data;
        $prevbalance = 0;
        while (count($row) > $i) {
            if (!empty($row[$i]['tDescription'])) {
                $pat = '/\#([^\"]*?)\#/';
                preg_match($pat, $row[$i]['tDescription'], $tDescription_value);
                $tDescription_translate = $languageLabelsArr[$tDescription_value[1]];
                $row[$i]['tDescription'] = str_replace($tDescription_value[0], $tDescription_translate, $row[$i]['tDescription']);
            }
            // Convert Into Timezone
            if ($tripTimeZone != "") {
                $serverTimeZone = date_default_timezone_get();
                $row[$i]['dDate'] = converToTz($row[$i]['dDate'], $tripTimeZone, $serverTimeZone);
            }
            // Convert Into Timezone
            if ($row[$i]['eType'] == "Credit") {
                $row[$i]['currentbal'] = $prevbalance + $row[$i]['iBalance'];
            }
            else {
                $row[$i]['currentbal'] = $prevbalance - $row[$i]['iBalance'];
            }
            $prevbalance = $row[$i]['currentbal'];
            $row[$i]['dDateOrig'] = $row[$i]['dDate'];
            $row[$i]['dDate'] = date('d-M-Y', strtotime($row[$i]['dDate']));
            $row[$i]['currentbal'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['currentbal'], $uservSymbol);
            $row[$i]['iBalance'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['iBalance'], $uservSymbol);
            $i++;
        }
        $returnData['message'] = $row;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        }
        else {
            $returnData['NextPage'] = 0;
        }
        $user_available_balance = $generalobj->get_user_available_balance_app_display($iUserId, $UserType);
        $returnData['user_available_balance_default'] = $user_available_balance;
        $returnData['user_available_balance'] = strval($user_available_balance);
        $returnData['Action'] = "1";
        #echo "<pre>"; print_r($returnData); exit;
        setDataResponse($returnData);
    }
    else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_TRANSACTION_AVAIL";
        $returnData['user_available_balance'] = $userCurrencySymbol . "0.00";
        setDataResponse($returnData);
    }
}
###########################displayDocList##########################################################
if ($type == "displayDocList") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    $doc_usertype = isset($_REQUEST['doc_usertype']) ? clean(strtolower($_REQUEST['doc_usertype'])) : 'driver';
    $eType = isset($_REQUEST['eType']) ? clean($_REQUEST['eType']) : ''; //  Ride, Delivery OR UberX only for APP_TYPE Ride-Delivery-UberX , and it is blank for another APP_TYPE
    $ssql = "";
    if ($doc_usertype == "vehicle") {
        $doc_usertype = "car";
    }
    $doc_userid = ($doc_usertype == 'car') ? $iDriverVehicleId : $iMemberId;
    $UserData = get_value('register_driver', 'vCountry,vLang', 'iDriverId', $iMemberId);
    $vCountry = $UserData[0]['vCountry'];
    $vLang = $UserData[0]['vLang'];
    $vDefaultLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    if ($vLang == '' || $vLang == NULL) {
        $vLang = $vDefaultLang;
    }
    //dm.doc_name_" . $vLang . " as doc_name
    if (strtoupper(APP_TYPE) == "RIDE" || strtoupper(APP_TYPE) == "DELIVERY" || strtoupper(APP_TYPE) == "UBERX") {
        //$ssql .= " AND eType='" . $eType . "'"; //its not needed now bc different table
    }
    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.ex_status ,dm.eType, IF(dm.doc_name_" . $vLang . "!='',dm.doc_name_" . $vLang . ",dm.doc_name_" . $vDefaultLang . ") doc_name,  dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $doc_userid . "' AND status != 'Deleted') dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='" . $doc_usertype . "' AND (dm.country='" . $vCountry . "' OR dm.country='All') and dm.status='Active' $ssql ";
    $db_vehicle = $obj->MySQLSelect($sql1);
    //echo "<pre>";print_R($db_vehicle); exit;
    if (count($db_vehicle) > 0) {
        //$Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc']."/".$iMemberId."/";
        if ($doc_usertype == "driver") {
            $Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc'] . "/" . $iMemberId . "/";
        }
        else {
            $Photo_Gallery_folder = $tconfig['tsite_upload_vehicle_doc_panel'] . "/" . $iDriverVehicleId . "/";
        }
        for ($i = 0;$i < count($db_vehicle);$i++) {
            $db_vehicle[$i]['vimage'] = "";
            if ($db_vehicle[$i]['doc_file'] != "") {
                $db_vehicle[$i]['vimage'] = $Photo_Gallery_folder . $db_vehicle[$i]['doc_file'];
            }
            ## Checking for expire date of document ##
            $ex_date = $db_vehicle[$i]['ex_date'];
            $todaydate = date('Y-m-d');
            if ($ex_date == "" || $ex_date == "0000-00-00" || $db_vehicle[$i]['ex_date'] == "0000-00-00" || $db_vehicle[$i]['ex_date'] <= "1970-01-01") {
                $expire_document = "No";
            }
            else {
                if (strtotime($ex_date) < strtotime($todaydate)) {
                    $expire_document = "Yes";
                }
                else {
                    $expire_document = "No";
                }
            }
            $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
            $exDocConfig = isExpiredDocumentEnable();
            if ($exDocConfig == true) {
                $db_vehicle[$i]['ex_date'] = ($db_vehicle[$i]['ex_date'] != "") ? $db_vehicle[$i]['ex_date'] : $todaydate;
                $db_vehicle[$i]['ex_date'] = date("d M, Y", strtotime($db_vehicle[$i]['ex_date']));
                $db_vehicle[$i]['exp_date'] = "";
                if ($ex_date != "0000-00-00") {
                    $expireLabel = $languageLabelsArr['LBL_EXPIRE_TXT'];
                    $newFormat = date("d M, Y (D)", strtotime($db_vehicle[$i]['ex_date']));
                    $db_vehicle[$i]['exp_date'] = $expireLabel . ": " . $newFormat;
                }
                $allowDate = date('Y-m-d', strtotime($db_vehicle[$i]['ex_date'] . ' - ' . $BEFORE_DAYS_ALLLOW_UPDATE_DOCS . ' days'));
                if (($db_vehicle[$i]['ex_date'] == '' || $todaydate >= $allowDate) || $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'No') {
                    $db_vehicle[$i]['allow_date_change'] = 'Yes';
                    $db_vehicle[$i]['doc_update_disable'] = '';
                }
                else {
                    $db_vehicle[$i]['allow_date_change'] = 'No';
                    $db_vehicle[$i]['doc_update_disable'] = $languageLabelsArr['LBL_DOC_UPDATE_DISABLE'];
                }
                $db_vehicle[$i]['EXPIRE_DOCUMENT'] = $expire_document;
            }
            else {
                $expireLabel = $languageLabelsArr['LBL_EXPIRE_TXT'];
                $newFormat = date("jS F Y", strtotime($db_vehicle[$i]['ex_date']));
                $db_vehicle[$i]['exp_date'] = $expireLabel . ": " . $newFormat;
                $db_vehicle[$i]['EXPIRE_DOCUMENT'] = $expire_document;
            }
            ## Checking for expire date of document ##
            
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle;
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DOC_AVAIL";
    }
    setDataResponse($returnArr);
}
###########################Add/Update Driver's Document and Vehilcle Document ##########################################################
if ($type == "uploaddrivedocument") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    //$doc_userid = isset($_REQUEST['doc_userid']) ? clean($_REQUEST['doc_userid']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $doc_usertype = isset($_REQUEST['doc_usertype']) ? clean(strtolower($_REQUEST['doc_usertype'])) : 'driver'; // vehicle OR driver
    $doc_masterid = isset($_REQUEST['doc_masterid']) ? clean($_REQUEST['doc_masterid']) : '';
    $doc_name = isset($_REQUEST['doc_name']) ? clean($_REQUEST['doc_name']) : '';
    $doc_id = isset($_REQUEST['doc_id']) ? clean($_REQUEST['doc_id']) : '';
    $doc_file = isset($_REQUEST['doc_file']) ? clean($_REQUEST['doc_file']) : '';
    $ex_date = isset($_REQUEST['ex_date']) ? clean($_REQUEST['ex_date']) : date("Y-m-d H:i:s");
    $ex_status = isset($_REQUEST['ex_status']) ? clean($_REQUEST['ex_status']) : '';
    $ex_date = date("Y-m-d", strtotime($ex_date));
    $Today = Date('Y-m-d');
    if ($doc_usertype == "vehicle") {
        $doc_usertype = "car";
    }
    //echo "<pre>";print_r($_FILES);die;
    $doc_userid = ($doc_usertype == 'car') ? $iDriverVehicleId : $iMemberId;
    $status = ($doc_usertype == 'car' || $doc_usertype == 'driver') ? "Active" : "Inactive";
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    //$image_name = "123.jpg";
    $action = ($doc_id != '') ? 'Edit' : 'Add';
    $addupdatemode = ($action == 'Add') ? 'insert' : 'update';
    if ($doc_file != "") {
        $vImageName = $doc_file;
    }
    else {
        if ($doc_usertype == "driver") {
            $Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc_path'] . "/" . $iMemberId . "/";
        }
        else {
            $Photo_Gallery_folder = $tconfig['tsite_upload_vehicle_doc'] . "/" . $iDriverVehicleId . "/";
        }
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = $tconfig['tsite_upload_docs_file_extensions']);
        $vImageName = $vFile[0];
    }
    if ($vImageName != '') {
        $Data_Update["doc_masterid"] = $doc_masterid;
        $Data_Update["doc_usertype"] = $doc_usertype;
        $Data_Update["doc_userid"] = $doc_userid;
        $Data_Update["edate"] = @date("Y-m-d H:i:s");
        $returnArr['doc_under_review'] = '';
        $exDocConfig = isExpiredDocumentEnable();
        if ($exDocConfig == true) {
            $exitingExpDate = 'SELECT dm.ex_status,dl.ex_date,dl.req_date FROM document_list AS dl LEFT JOIN document_master as dm ON dm.doc_masterid = dl.doc_masterid  WHERE doc_id = ' . $doc_id;
            $db_data1 = $obj->MySQLSelect($exitingExpDate);
            $allowDate = date('Y-m-d', strtotime($db_data1[0]['ex_date'] . ' - ' . $BEFORE_DAYS_ALLLOW_UPDATE_DOCS . ' days'));
            if ($Today >= $allowDate && $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'Yes' && $action != "Add" && $db_data1[0]['ex_status'] == 'yes') {
                $ex_date = $ex_date == $db_data1[0]['ex_date'] ? $db_data1[0]['req_date'] : $ex_date;
                $Data_Update["req_date"] = $ex_date;
                $Data_Update["req_file"] = $vImageName;
                $returnArr['doc_under_review'] = 'LBL_FOR_DOCS_UNDER_REVIEW';
            }
            else {
                $Data_Update["ex_date"] = $ex_date;
                $Data_Update["doc_file"] = $vImageName;
            }
        }
        else {
            $Data_Update["ex_date"] = $ex_date;
            $Data_Update["doc_file"] = $vImageName;
        }
        if ($action == "Add") {
            $Data_Update["status"] = $status;
            $id = $obj->MySQLQueryPerform("document_list", $Data_Update, 'insert');
        }
        else {
            $where = " doc_id = '" . $doc_id . "'";
            $id = $obj->MySQLQueryPerform("document_list", $Data_Update, 'update', $where);
        }
        $generalobj->save_log_data('0', $iMemberId, 'driver', $doc_name, $vImageName);
        if ($id > 0) {
            $sql_user = "SELECT rd.iDriverId,rd.vName,rd.vLastName,rd.vEmail as rdemail,c.vCompany,c.vEmail as cemail  FROM `register_driver` as rd join company as c on c.iCompanyId =rd.iCompanyId WHERE rd.iDriverId='" . $iMemberId . "'";
            $userdetails = $obj->MySQLSelect($sql_user);
            $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
            if ($doc_usertype == "driver") {
                $maildata['NAME'] = $userdetails[0]['vName'] . " " . $userdetails[0]['vLastName'] . " (" . $languageLabelsArr['LBL_DOCUMNET_UPLOAD_BY_DRIVER'] . ")";
                $maildata['DOCUMENTFOR'] = $languageLabelsArr['LBL_DOCUMNET_UPLOAD_BY_DRIVER'];
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $maildata['EMAIL'] = $userdetails[0]['rdemail'];
                $docname_SQL = "SELECT doc_name_" . $vLang . " as docname FROM document_master WHERE doc_masterid = '" . $doc_masterid . "'";
                $docname_data = $obj->MySQLSelect($docname_SQL);
                $maildata['DOCUMENTTYPE'] = $docname_data[0]['docname'];
                $generalobj->send_email_user("DOCCUMENT_UPLOAD_WEB", $maildata);
                $maildata['COMPANYEMAIL'] = $userdetails[0]['cemail'];
                $maildata['COMPANYNAME'] = $userdetails[0]['vCompany'];
                $generalobj->send_email_user("DOCCUMENT_UPLOAD_WEB_COMPANY", $maildata);
            }
            $returnArr['Action'] = "1";
            //$returnArr['message'] = getDriverDetailInfo($iMemberId);
            
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################Add/Update Driver's Document and Vehilcle Document Ends##########################################################
if ($type == 'changelanguagelabel') {
    $iHotelId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    $vLang = isset($_REQUEST['vLang']) ? clean($_REQUEST['vLang']) : '';
    $UpdatedLanguageLabels = getLanguageLabelsArr($vLang, "1", $iServiceId);
    if (strtolower($GeneralUserType) == "hotel" || strtolower($GeneralUserType) == "kiosk") {
        $Datahotel["vLang"] = $vLang;
        $where = " iHotelId = '" . $iHotelId . "'";
        $id = $obj->MySQLQueryPerform("hotel", $Datahotel, 'update', $where);
    }
    $lngData = get_value('language_master', 'vCode, vGMapLangCode, eDirectionCode as eType, vTitle', 'vCode', $vLang);
    $returnArr['Action'] = "1";
    $returnArr['message'] = $UpdatedLanguageLabels;
    $returnArr['vCode'] = $lngData[0]['vCode'];
    $returnArr['vGMapLangCode'] = $lngData[0]['vGMapLangCode'];
    $returnArr['eType'] = $lngData[0]['eType'];
    $returnArr['vTitle'] = $lngData[0]['vTitle'];
    setDataResponse($returnArr);
}
#####################################################################################
if ($type == "checkUserStatus") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    if ($UserType == "Passenger") {
        $condfield = 'iUserId';
    }
    else {
        $condfield = 'iDriverId';
    }
    if ($APP_TYPE == "UberX") {
        $sql = "SELECT iTripId FROM trips WHERE 1=1 AND $condfield = '" . $iMemberId . "' AND vTripPaymentMode != 'Cash' AND eType='UberX' AND (iActive=    'Active' OR iActive='On Going Trip')";
        $checkStatus = $obj->MySQLSelect($sql);
    }
    else {
        $sql = "SELECT iTripId FROM trips WHERE 1=1 AND $condfield = '" . $iMemberId . "' AND vTripPaymentMode != 'Cash' AND (eType='Ride' || eType='Deliver' || eType='Multi-Delivery') AND (iActive= 'Active' OR iActive='On Going Trip') order by iTripId DESC limit 1";
        $checkStatus = $obj->MySQLSelect($sql);
    }
    if (count($checkStatus) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'LBL_DIS_ALLOW_EDIT_CARD';
    }
    else {
        $returnArr['Action'] = "1";
    }
    setDataResponse($returnArr);
}
#######################################################################
if ($type == "callOnLogout") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vPassword = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $Data_logout = array();
    if ($userType == "Passenger") {
        $Data_logout['eLogout'] = 'Yes';
        $tableName = "register_user";
        $where = " iUserId='" . $iMemberId . "'";
        $id = $obj->MySQLQueryPerform($tableName, $Data_logout, 'update', $where);
    }
    else if ($userType == "Driver") {
        /** As a part of socket cluster */
        $COUNT_PUBLISH_CHANNEL = isset($_REQUEST["COUNT_PUBLISH_CHANNEL"]) ? $_REQUEST["COUNT_PUBLISH_CHANNEL"] : '0';
        $IS_DRIVER_ONLINE = isset($_REQUEST["IS_DRIVER_ONLINE"]) ? $_REQUEST["IS_DRIVER_ONLINE"] : '0';
        /** As a part of socket cluster */
        /** As a part of socket cluster */
        if ($PUBNUB_DISABLED == "Yes" && $IS_DRIVER_ONLINE == "Yes") {
            $DRIVER_CURRENT_TIME = isset($_REQUEST["DRIVER_CURRENT_TIME"]) ? $_REQUEST["DRIVER_CURRENT_TIME"] : '';
            $Latitude = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
            $Longitude = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';
            for ($i = 0;$i < $COUNT_PUBLISH_CHANNEL;$i++) {
                $PUBLISH_CHANNEL_tmp = isset($_REQUEST["PUBLISH_CHANNEL_" . $i]) ? $_REQUEST["PUBLISH_CHANNEL_" . $i] : '';
                if ($PUBLISH_CHANNEL_tmp != "") {
                    $pubMsgArr['iDriverId'] = $iMemberId;
                    $pubMsgArr['MessageType'] = "DriverStatusLocation";
                    $pubMsgArr['Latitude'] = $Latitude;
                    $pubMsgArr['Longitude'] = $Longitude;
                    $pubMsgArr['Time'] = $DRIVER_CURRENT_TIME;
                    $pubMsgArr['IsDriverOnline'] = "No";
                    $pubMsgArr['isForceLoad'] = "No";
                    $message_pub_sub = json_encode($pubMsgArr, JSON_UNESCAPED_UNICODE);
                    //  publishEventMessage($PUBLISH_CHANNEL_tmp, $message_pub_sub);
                    
                }
            }
        }
        /** As a part of socket cluster */
        $Data_logout['vAvailability'] = 'Not Available';
        $Data_logout['eLogout'] = 'Yes';
        $tableName = "register_driver";
        $where = " iDriverId='" . $iMemberId . "'";
        $id = $obj->MySQLQueryPerform($tableName, $Data_logout, 'update', $where);
        $curr_date = date('Y-m-d H:i:s');
        $selct_query = "select * from driver_log_report WHERE iDriverId = '" . $iMemberId . "' AND dLogoutDateTime = '0000-00-00 00:00:00' order by `iDriverLogId` desc limit 0,1";
        $get_data_log = $obj->sql_query($selct_query);
        if (count($get_data_log) > 0) {
            $update_sql = "UPDATE driver_log_report set dLogoutDateTime = '" . $curr_date . "' WHERE iDriverLogId ='" . $get_data_log[0]['iDriverLogId'] . "'";
            $result = $obj->sql_query($update_sql);
        }
    }
    else {
        $sql2 = "SELECT h.iHotelId,a.vPassword FROM `hotel` as h LEFT JOIN  administrators as a on a.iAdminId=h.iAdminId WHERE h.iHotelId='" . $iMemberId . "'";
        $result2 = $obj->MySQLSelect($sql2);
        $hash = $result2[0]['vPassword'];
        $checkValid = $generalobj->check_password($vPassword, $hash);
        if ($checkValid == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            setDataResponse($returnArr);
        }
        //Added BY HJ On 08-01-2020 For Solved 141 Mantis Bud #2850 Start
        $Data_logout = array();
        $Data_logout['eLogout'] = 'Yes';
        $tableName = "hotel";
        $where = " iHotelId='" . $iMemberId . "'";
        $id = $obj->MySQLQueryPerform($tableName, $Data_logout, 'update', $where);
        //Added BY HJ On 08-01-2020 For Solved 141 Mantis Bud #2850 End
        $returnArr['Action'] = "1";
        setDataResponse($returnArr);
    }
    if ($id) {
        $returnArr['Action'] = "1";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
##############################################################################
if ($type == "getCabRequestAddress") {
    //global $generalobj, $obj;
    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';
    $iDriverId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
    //$fields = "iCabRequestId,iVehicleTypeId,eType,tSourceAddress,tDestAddress,tUserComment,iRentalPackageId,ePayType,ePayWallet,fDistance,fTripGenerateFare,fDuration,iFare,fDiscount,fWalletDebit,eServiceLocation,tVehicleTypeData, iHotelBookingId,vSourceLatitude AS sourceLatitude,vSourceLongitude AS sourceLongitude,vDestLatitude AS destLatitude,vDestLongitude AS destLongitude";
    $fields = "iCabRequestId,iVehicleTypeId,eType,tSourceAddress,tDestAddress,tUserComment,iRentalPackageId,ePayType,ePayWallet,fDistance,fTripGenerateFare,fDuration,iFare,fDiscount,fWalletDebit,eServiceLocation,tVehicleTypeData, iHotelBookingId,vSourceLatitude AS sourceLatitude,vSourceLongitude AS sourceLongitude,vDestLatitude AS destLatitude,vDestLongitude AS destLongitude,iFromStationId,iToStationId"; //added by SP for fly stations on 27-09-2019
    //echo "<pre>";
    $Data_cab_request = get_value('cab_request_now', $fields, 'iCabRequestId', $iCabRequestId, '', '');
    //added by SP for fly stations on 27-09-2019 start
    //if($Data_cab_request[0]['iFromStationId']!='' && $Data_cab_request[0]['iToStationId']!='') {
    if (!empty($Data_cab_request[0]['iFromStationId']) && !empty($Data_cab_request[0]['iToStationId'])) {
        $Data_cab_request[0]['eFly'] = "Yes";
    }
    else {
        $Data_cab_request[0]['eFly'] = "No";
    }
    //added by SP for fly stations on 27-09-2019 end
    $eType = $Data_cab_request[0]['eType'];
    // changed for rental
    if ($Data_cab_request[0]['iRentalPackageId'] == 0) {
        $Data_cab_request[0]['iRentalPackageId'] = "";
    }
    $iRentalPackageId = $Data_cab_request[0]['iRentalPackageId'];
    // end changed for rental
    //if($eType == "UberX"){
    $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $eServiceLocation = $Data_cab_request[0]['eServiceLocation'];
    //$k = preg_replace('/\r|\n/','\n',trim($Data_cab_request[0]['tVehicleTypeData']));
    $replacedata = preg_replace('/[[:cntrl:]]/', '\r\n', $Data_cab_request[0]['tVehicleTypeData']); //apply this when from app enter key is used in special instruction
    $tVehicleTypeData = (array)(json_decode($replacedata));
    $Data_cab_request[0]['moreServices'] = "No";
    if (count($tVehicleTypeData) > 1) {
        $Data_cab_request[0]['moreServices'] = "Yes";
    }
    else if (!empty($tVehicleTypeData)) {
        $Data_cab_request[0]['moreServices'] = "Yes";
    }
    if ($eServiceLocation == "Driver") {
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $Data_cab_request[0]['tSourceAddress'] = $languageLabelsArr['LBL_AT_YOUR_LOCATION'];
    }
    // changed for rental
    if ($iRentalPackageId != '') {
        $fields = "iRentalPackageId,fPrice,vPackageName_" . $vLang . "";
        $Data_Rental = get_value('rental_package', $fields, 'iRentalPackageId', $iRentalPackageId, '', '');
        //$fPrice = $Data_Rental[0]['fPrice'];
        $PackageName = $Data_Rental[0]['vPackageName_' . $vLang];
        //$Data_cab_request[0]['fPrice'] = $fPrice;
        $Data_cab_request[0]['PackageName'] = $PackageName;
    }
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    // end changed for rental
    $iVehicleTypeId = $Data_cab_request[0]['iVehicleTypeId'];
    if ($iVehicleTypeId > 0) {
        $sqlv = "SELECT iVehicleCategoryId,vVehicleType_" . $vLang . " as vVehicleTypeName from vehicle_type WHERE iVehicleTypeId = '" . $iVehicleTypeId . "'";
        $tripVehicleData = $obj->MySQLSelect($sqlv);
        $iVehicleCategoryId = $tripVehicleData[0]['iVehicleCategoryId'];
        $vVehicleTypeName = $tripVehicleData[0]['vVehicleTypeName'];
        if ($iVehicleCategoryId != 0) {
            $vVehicleCategoryName = get_value($sql_vehicle_category_table_name, 'vCategory_' . $vLang, 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
            $vVehicleTypeName = $vVehicleCategoryName . "-" . $vVehicleTypeName;
        }
    }
    if (count($tVehicleTypeData) > 0) {
        $getMainCat = $obj->MySQLSelect("SELECT VC.vCategory_" . $vLang . " AS vVehicleCategory,VT.iVehicleCategoryId,if(VC.iParentId >0,(SELECT vCategory_" . $vLang . " FROM " . $sql_vehicle_category_table_name . " VC1 WHERE VC.iParentId=VC1.iVehicleCategoryId),'') AS vVehicleCategory FROM vehicle_type VT INNER JOIN " . $sql_vehicle_category_table_name . " VC ON VT.iVehicleCategoryId=VC.iVehicleCategoryId WHERE iVehicleTypeId='" . $tVehicleTypeData[0]->iVehicleTypeId . "'");
        if (count($getMainCat) > 0) {
            $vVehicleTypeName = $getMainCat[0]['vVehicleCategory'];
        }
    }
    if ($eType == "UberX") {
        $Data_cab_request[0]['SelectedTypeName'] = $vVehicleTypeName;
    }
    $Data_cab_request[0]['VehicleTypeName'] = $vVehicleTypeName;
    /* -------------------------------for multi delivery----------------------------------------- */
    if ($eType == "Multi-Delivery") {
        $sql = "select iTripDeliveryLocationId from trip_delivery_fields where iCabRequestId = '$iCabRequestId' group by iTripDeliveryLocationId";
        $db_trip_fields = $obj->MySQLSelect($sql);
        $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId, '', 'true');
        //$vCurrencyDriver = $data[0]['vCurrencyDriver'];
        if ($vCurrencyDriver == '' || $vCurrencyDriver == NULL) {
            $vCurrencyDriver = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        }
        $sql = "SELECT Ratio,vSymbol from currency WHERE vName= '" . $vCurrencyDriver . "'";
        $currencydata = $obj->MySQLSelect($sql);
        $priceRatio = $currencydata[0]['Ratio'];
        $vSymbol = $currencydata[0]['vSymbol'];
        $eUnit = getMemberCountryUnit($iDriverId, "Driver");
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $DisplayDistanceTxt = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
        if ($eUnit == "Miles") {
            $tripDistanceDisplay = $Data_cab_request[0]['fDistance'] * 0.621371;
            $tripDistanceDisplay = round($tripDistanceDisplay, 2);
            $DisplayDistanceTxt = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
        }
        else {
            $tripDistanceDisplay = $Data_cab_request[0]['fDistance'];
            $DisplayDistanceTxt = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
        }
        $tripDistanceDisplay = $tripDistanceDisplay . " " . $DisplayDistanceTxt;
        $hours = floor($Data_cab_request[0]['fDuration'] / 60); // No. of mins/60 to get the hours and round down
        $mins = $Data_cab_request[0]['fDuration'] % 60; // No. of mins/60 - remainder (modulus) is the minutes
        if ($hours >= 1) {
            $tripDurationDisplay = $hours . " " . $languageLabelsArr['LBL_HOURS_TXT'] . ", " . $mins . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
        }
        else {
            $tripDurationDisplay = $Data_cab_request[0]['fDuration'] . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
        }
        $Data_cab_request[0]['Total_Delivery'] = count($db_trip_fields);
        $fTripGenerateFare = (($Data_cab_request[0]['fTripGenerateFare'] - $Data_cab_request[0]['fDiscount'] - $Data_cab_request[0]['fWalletDebit']) * $priceRatio);
        $fTripGenerateFare = round($fTripGenerateFare, 2);
        $fTripGenerateFare = $vSymbol . " " . formatNum($fTripGenerateFare);
        $Data_cab_request[0]['fDuration'] = $tripDurationDisplay;
        $Data_cab_request[0]['fDistance'] = $tripDistanceDisplay;
        $Data_cab_request[0]['fTripGenerateFare'] = $fTripGenerateFare;
    }
    $Data_delivery = $Data_cab_request[0];
    // echo "<pre>";print_r($Data_delivery);exit;
    /* -------------------------------for multi delivery----------------------------------------- */
    if (!empty($Data_cab_request)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data_cab_request[0];
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
################################################Get Member Wallet Balance########################################################
if ($type == "GetMemberWalletBalance") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    if ($UserType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iMemberId = "iUserId";
        $eUserType = "Rider";
    }
    else {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iMemberId = "iDriverId";
        $eUserType = "Driver";
    }
    $userCurrencyCode = get_value($tbl_name, $currencycode, $iMemberId, $iUserId, '', 'true');
    $user_available_balance = $generalobj->get_user_available_balance_app_display($iUserId, $eUserType, '', 'Yes');
    $returnArr['Action'] = "1";
    $returnArr["MemberBalance"] = strval($user_available_balance['DISPLAY_AMOUNT']);
    $returnArr['user_available_balance'] = strval($user_available_balance['DISPLAY_AMOUNT']);
    $returnArr['user_available_balance_amount'] = strval($user_available_balance['ORIG_AMOUNT']);
    setDataResponse($returnArr);
}
################################Get Help Category #####################################################################
if ($type == "getHelpDetailCategoty") {
    $status = "Active";
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }
    else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT * FROM `help_detail_categories` WHERE eStatus='$status' AND vCode='" . $languageCode . "' AND eSystem = 'General' ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $arr_cat = array();
        for ($i = 0;$i < count($Data);$i++) {
            $arr_cat[$i]['iHelpDetailCategoryId'] = $Data[$i]['iHelpDetailCategoryId'];
            $arr_cat[$i]['vTitle'] = $Data[$i]['vTitle'];
            $arr_cat[$i]['vTitle'] = $Data[$i]['vTitle'];
            $arr_cat[$i]['iUniqueId'] = $Data[$i]['iUniqueId'];
            $iUniqueId = $Data[$i]['iUniqueId'];
            $sql_sub = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer,eShowDetail,iHelpDetailId FROM `help_detail` WHERE eStatus='$status'  AND iHelpDetailCategoryId='" . $iUniqueId . "' ORDER BY iDisplayOrder ASC ";
            $Data_sub = $obj->MySQLSelect($sql_sub);
            if (count($Data_sub) > 0) {
                $arr_helpdetail = array();
                for ($j = 0;$j < count($Data_sub);$j++) {
                    $arr_helpdetail[$j]['iHelpDetailId'] = $Data_sub[$j]['iHelpDetailId'];
                    $arr_helpdetail[$j]['vTitle'] = $Data_sub[$j]['vTitle'];
                    $arr_helpdetail[$j]['tAnswer'] = $Data_sub[$j]['tAnswer'];
                    $arr_helpdetail[$j]['eShowFrom'] = $Data_sub[$j]['eShowDetail'];
                }
                $arr_cat[$i]['subData'] = $arr_helpdetail;
            }
        }
        $returnData['Action'] = "1";
        $returnData['message'] = $arr_cat;
    }
    else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_HELP_DETAIL_NOT_AVAIL";
    }
    setDataResponse($returnData);
}
############################# End Get Help Category ################################################################
############################# getsubHelpdetail #####################################################################
if ($type == "getsubHelpdetail") {
    $status = "Active";
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $iUniqueId = isset($_REQUEST['iUniqueId']) ? clean($_REQUEST['iUniqueId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }
    else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer,eShowDetail,iHelpDetailId FROM `help_detail` WHERE eStatus='$status'  AND iHelpDetailCategoryId='" . $iUniqueId . "' ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $arr_helpdetail = array();
        for ($j = 0;$j < count($Data);$j++) {
            $arr_helpdetail[$j]['iHelpDetailId'] = $Data[$j]['iHelpDetailId'];
            $arr_helpdetail[$j]['vTitle'] = $Data[$j]['vTitle'];
            $arr_helpdetail[$j]['tAnswer'] = $Data[$j]['tAnswer'];
            $arr_helpdetail[$j]['eShowFrom'] = $Data[$j]['eShowDetail'];
        }
        $returnData['Action'] = "1";
        $returnData['message'] = $arr_helpdetail;
    }
    else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_HELP_DETAIL_NOT_AVAIL";
    }
    setDataResponse($returnData);
}
#############################End getsubHelpdetail #####################################################################
#############################Start getHelpDetail #####################################################################
if ($type == "getHelpDetail") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $iUniqueId = isset($_REQUEST['iUniqueId']) ? clean($_REQUEST['iUniqueId']) : '';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }
    else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer,iHelpDetailId, eShowDetail FROM `help_detail` WHERE eStatus='Active' AND eSystem = 'General' AND iHelpDetailCategoryId='" . $iUniqueId . "'";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $arr_helpdetail = array();
        for ($j = 0;$j < count($Data);$j++) {
            $arr_helpdetail[$j]['iHelpDetailId'] = $Data[$j]['iHelpDetailId'];
            $arr_helpdetail[$j]['vTitle'] = $Data[$j]['vTitle'];
            $arr_helpdetail[$j]['tAnswer'] = $Data[$j]['tAnswer'];
            $arr_helpdetail[$j]['eShowFrom'] = $Data[$j]['eShowDetail'];
        }
        $returnData['Action'] = "1";
        $returnData['message'] = $arr_helpdetail;
    }
    else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_HELP_DETAIL_NOT_AVAIL";
    }
    setDataResponse($returnData);
}
############################# End getHelpDetail #####################################################################
############################# Start submitTripHelpDetail ############################################################
if ($type == "submitTripHelpDetail") {
    global $generalobj, $obj;
    $TripId = isset($_REQUEST['TripId']) ? clean($_REQUEST['TripId']) : '';
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $iHelpDetailId = isset($_REQUEST['iHelpDetailId']) ? clean($_REQUEST['iHelpDetailId']) : '';
    $vComment = isset($_REQUEST['vComment']) ? clean($_REQUEST['vComment']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $current_date = date('Y-m-d H:i:s');
    if ($appType == "Driver") {
        $sql = "SELECT CONCAT(vName,' ',vLastName) as Name FROM `register_driver` WHERE iDriverId='" . $iMemberId . "'";
    }
    else {
        $sql = "SELECT CONCAT(vName,' ',vLastName) as Name FROM `register_user` WHERE iUserId='" . $iMemberId . "'";
    }
    $Data = $obj->MySQLSelect($sql);
    $Data_trip_help_detail['iTripId'] = $TripId;
    $Data_trip_help_detail['iUserId'] = $iMemberId;
    $Data_trip_help_detail['iHelpDetailId'] = $iHelpDetailId;
    $Data_trip_help_detail['vComment'] = $vComment;
    $Data_trip_help_detail['tDate'] = $current_date;
    $id = $obj->MySQLQueryPerform('trip_help_detail', $Data_trip_help_detail, 'insert');
    if ($id > 0) {
        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $TripId, '', 'true');
        $maildata['iTripId'] = $vRideNo;
        $maildata['NAME'] = $Data[0]['Name'];
        $maildata['vComment'] = $vComment;
        $maildata['Ddate'] = $current_date;
        $generalobj->send_email_user("RIDER_TRIP_HELP_DETAIL", $maildata);
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_COMMENT_ADDED_TXT";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
############################# End submitTripHelpDetail ############################################################
################################## Get Cancel Reason #############################################################################
if ($type == "GetCancelReasons") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : "";
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : "";
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : "";
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : "";
    $GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : "";
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
    }
    else {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
    }
    $vLang = $UserDetailsArr['vLang'];
    if ($iTripId != '') {
        $eType = get_value('trips', 'eType', 'iTripId', $iTripId, '', 'true');
    }
    else {
        $eType = get_value('cab_booking', 'eType', 'iCabBookingId', $iCabBookingId, '', 'true');
    }
    if ($eType != 'Multi-Delivery') {
        if ($iTripId != "") {
            $sql = "SELECT cr.vTitle_" . $vLang . " as vTitle,cr.iCancelReasonId FROM cancel_reason as cr LEFT JOIN trips as tr ON cr.eType=tr.eType WHERE cr.eStatus = 'Active' AND tr.iTripId = '" . $iTripId . "' AND (cr.eFor = '" . $GeneralUserType . "' OR cr.eFor='General')";
        }
        else {
            $sql = "SELECT cr.vTitle_" . $vLang . " as vTitle,cr.iCancelReasonId FROM cancel_reason as cr LEFT JOIN cab_booking as cb ON cr.eType=cb.eType WHERE cr.eStatus = 'Active' AND cb.iCabBookingId = '" . $iCabBookingId . "' AND (cr.eFor = '" . $GeneralUserType . "' OR cr.eFor='General')";
        }
    }
    else {
        $sql = "SELECT cr.vTitle_" . $vLang . " as vTitle,cr.iCancelReasonId FROM cancel_reason as cr WHERE cr.eType = 'Deliver' AND cr.eStatus = 'Active' AND (cr.eFor = '" . $GeneralUserType . "' OR cr.eFor='General')";
    }
    $CancelReasonData = $obj->MySQLSelect($sql);
    if (!empty($CancelReasonData)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $CancelReasonData;
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    setDataResponse($returnArr);
}
################################## Get Cancel Reason #############################################################################
// ##########################################Send Verification Email #########################################
if ($type == 'sendVerificationEmail') {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    else {
        $tblname = "register_driver";
        $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iDriverId';
        $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    }
    $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
    $db_member = $obj->MySQLSelect($sql);
    $vName = $db_member[0]['vName'] . " " . $db_member[0]['vLastName'];
    $vEmail = $db_member[0]['vEmail'];
    $dt = date("Y-m-d H:i:s");
    $random = substr(number_format(time() * rand() , 0, '', '') , 0, 20);
    $Data['vEmailVarificationCode'] = $random . strtotime($dt);
    $where = " " . $condfield . " = '" . $iMemberId . "'";
    $res = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);
    $Data_Mail['vEmail'] = $vEmail;
    $Data_Mail['vName'] = $vName;
    $Data_Mail['act_link'] = $tconfig['tsite_url'] . "verifymail.php?act=" . $Data['vEmailVarificationCode'] . "&iMemberId=" . $iMemberId . "&UserType=" . $userType;
    $sendemail = $generalobj->send_email_user("EMAIL_VERIFICATION_USER", $Data_Mail);
    if ($sendemail == 1) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EMAIl_VERIFICATION_SEND_TXT";
        $returnArr['act_link'] = $Data_Mail['act_link'];
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        $returnArr['act_link'] = $Data_Mail['act_link'];
    }
    setDataResponse($returnArr);
}
#############################Send Verification Email #####################################
###########################Call Masking##########################################################
if ($type == "getCallMaskNumber") {
    global $generalobj, $tconfig;
    $returnArr = array();
    $iTripId = isset($_REQUEST['iTripid']) ? $_REQUEST['iTripid'] : '';
    $UserType = isset($_REQUEST['UserType']) ? $_REQUEST['UserType'] : '';
    if (!empty($iTripId)) {
        if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
            $returnArr = getCallMaskConfigNumber();
        }
        else {
            $sql = "SELECT rd.vCode as DriverPhoneCode, rd.vPhone as DriverPhone, ru.vPhoneCode as UserPhoneCode, ru.vPhone as RiderPhone FROM `trips` as t LEFT JOIN `register_user` as ru on ru.iUserId = t.iUserId LEFT JOIN `register_driver` as rd on rd.iDriverId= t.iDriverId  WHERE t.iTripId = " . $iTripId . " AND (t.iActive != 'Canceled' && t.iActive != 'Finished')";
            $getTripDetails = $obj->MySQLSelect($sql);
            if ($UserType == "Driver") {
                $phonNum = '+' . $getTripDetails[0]['UserPhoneCode'] . $getTripDetails[0]['RiderPhone'];
            }
            else {
                $phonNum = '+' . $getTripDetails[0]['DriverPhoneCode'] . $getTripDetails[0]['DriverPhone'];
            }
            $returnArr['Action'] = "1";
            $returnArr['message'] = $phonNum;
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
############################call masking Ends##########################################################
#####################################Update User Wallet Adjustment Setting ###########################################################
if ($type == "UpdateUserWalletAdjustment") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    $eWalletAdjustment = isset($_REQUEST['eWalletAdjustment']) ? $_REQUEST['eWalletAdjustment'] : 'Yes'; // Yes Or No
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
    }
    else {
        $tblname = "register_driver";
        $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iDriverId';
    }
    $where = " " . $condfield . " = '" . $iMemberId . "'";
    $Data['eWalletAdjustment'] = $eWalletAdjustment;
    $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);
    if ($id) {
        $returnArr['Action'] = "1";
        if ($userType != "Driver") {
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($iMemberId);
        }
        //$returnArr['message']  = "LBL_INFO_UPDATED_TXT_MY_PROFILE";
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_EMAIl_VERIFIED_ERROR";
        setDataResponse($returnArr);
    }
}
#####################################Update User Wallet Adjustment Setting ###########################################################
#####################################DisplayCouponList ###########################################################
if ($type == "DisplayCouponList") {
    $validPromoCodesArr = getValidPromoCodes();
    ## Filter Of Coupon Data ##
    if (count($validPromoCodesArr['CouponList']) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $validPromoCodesArr['CouponList'];
        $returnArr['vCurrency'] = $validPromoCodesArr['vCurrency'];
        $returnArr['vSymbol'] = $validPromoCodesArr['vSymbol'];
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_RECORDS_FOUND1";
        $returnArr['vCurrency'] = $validPromoCodesArr['vCurrency'];
        $returnArr['vSymbol'] = $validPromoCodesArr['vSymbol'];
        setDataResponse($returnArr);
    }
}
#####################################DisplayCouponList ###########################################################
if ($type == "addMoneyUserWallet") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
    if ($eMemberType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iUserId = "iUserId";
        $eUserType = "Rider";
    }
    else {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iUserId = "iDriverId";
        $eUserType = "Driver";
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $UserCardData = get_value($tbl_name, 'vSenangToken,vStripeCusId,vStripeToken,vBrainTreeToken,vPaymayaCustId,vXenditToken,vPaymayaToken,vFlutterWaveToken,' . $currencycode . ' as currencycode', $iUserId, $iMemberId);
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    $vStripeCusId = $UserCardData[0]['vStripeCusId'];
    $vStripeToken = $UserCardData[0]['vStripeToken'];
    $userCurrencyCode = $UserCardData[0]['currencycode'];
    $vBrainTreeToken = $UserCardData[0]['vBrainTreeToken'];
    $vPaymayaCustId = $UserCardData[0]['vPaymayaCustId'];
    $vPaymayaToken = $UserCardData[0]['vPaymayaToken'];
    $vXenditToken = $UserCardData[0]['vXenditToken'];
    $vSenangToken = $UserCardData[0]['vSenangToken'];
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $vFlutterWaveToken = $UserCardData[0]['vFlutterWaveToken'];
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $userCurrencyCode, '', 'true');
    //$walletamount = round($fAmount / $userCurrencyRatio, 10);
    $walletamount = $fAmount / $userCurrencyRatio;
    //echo $walletamount."<br>".$walletamount1;exit;
    $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
    $currencyCode = $DefaultCurrencyData[0]['vName'];
    $currencyratio = $DefaultCurrencyData[0]['Ratio'];
    $price = $fAmount * $currencyratio;
    $price_new = $walletamount * 100;
    $price_new = round($price_new);
 
    if ($vSenangToken == "" && $APP_PAYMENT_METHOD == "Senangpay") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    
    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $iTripId = 0;
    $tDescription_stripe = "Amount credited";
    $tDescription = '#LBL_AMOUNT_CREDIT_BY_USER#';
    $ePaymentStatus = 'Unsettelled';
    $t_rand_nun = rand(1111111, 9999999);
    $Charge_Array = array(
        "iFare" => $walletamount,
        "price_new" => $price_new,
        "currency" => $currencyCode,
        "vStripeCusId" => $vStripeCusId,
        "description" => $tDescription_stripe,
        "iTripId" => 0,
        "eCancelChargeFailed" => "No",
        "vBrainTreeToken" => $vBrainTreeToken,
        "vRideNo" => $t_rand_nun,
        "iMemberId" => $iMemberId,
        "UserType" => $eMemberType
    );
    $ChargeidArr = ChargeCustomer($Charge_Array, "addMoneyUserWallet"); // function for charge customer
    $ChargeidArrId = $ChargeidArr['id'];
    $status = $ChargeidArr['status'];
    if ($status == "success") {
        $WalletId = $generalobj->InsertIntoUserWallet($iMemberId, $eUserType, $walletamount, 'Credit', 0, $eFor, $tDescription, $ePaymentStatus, $dDate);
        //$user_available_balance = $generalobj->get_user_available_balance($iMemberId,$eUserType);
        $user_available_balance = $generalobj->get_user_available_balance_app_display($iMemberId, $eUserType);
        $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
        $data_payments['iUserWalletId'] = $WalletId;
        $data_payments['eEvent'] = "Wallet";
        $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
        $returnArr["Action"] = "1";
        //$returnArr["MemberBalance"] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$userCurrencyCode));
        $returnArr["MemberBalance"] = strval($user_available_balance);
        $returnArr['message1'] = "LBL_WALLET_MONEY_CREDITED";
        if ($eMemberType != "Driver") {
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($iMemberId);
        }
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WALLET_MONEY_CREDITED_FAILED";
        setDataResponse($returnArr);
    }
}
########################Get Driver Bank Details############################
if ($type == "DriverBankDetails") {
    global $generalobj, $obj;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    $eDisplay = isset($_REQUEST["eDisplay"]) ? $_REQUEST["eDisplay"] : 'Yes';
    $vPaymentEmail = isset($_REQUEST["vPaymentEmail"]) ? $_REQUEST["vPaymentEmail"] : '';
    $vBankAccountHolderName = isset($_REQUEST["vBankAccountHolderName"]) ? $_REQUEST["vBankAccountHolderName"] : '';
    $vAccountNumber = isset($_REQUEST["vAccountNumber"]) ? $_REQUEST["vAccountNumber"] : '';
    $vBankLocation = isset($_REQUEST["vBankLocation"]) ? $_REQUEST["vBankLocation"] : '';
    $vBankName = isset($_REQUEST["vBankName"]) ? $_REQUEST["vBankName"] : '';
    $vBIC_SWIFT_Code = isset($_REQUEST["vBIC_SWIFT_Code"]) ? $_REQUEST["vBIC_SWIFT_Code"] : '';
    if ($eDisplay == "" || $eDisplay == NULL) {
        $eDisplay = "Yes";
    }
    $returnArr = array();
    if ($eDisplay == "Yes") {
        $Driver_Bank_Arr = get_value('register_driver', 'vPaymentEmail, vBankAccountHolderName, vAccountNumber, vBankLocation, vBankName, vBIC_SWIFT_Code', 'iDriverId', $iDriverId);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Driver_Bank_Arr[0];
        setDataResponse($returnArr);
    }
    else {
        $Data_Update['vPaymentEmail'] = $vPaymentEmail;
        $Data_Update['vBankAccountHolderName'] = $vBankAccountHolderName;
        $Data_Update['vAccountNumber'] = $vAccountNumber;
        $Data_Update['vBankLocation'] = $vBankLocation;
        $Data_Update['vBankName'] = $vBankName;
        $Data_Update['vBIC_SWIFT_Code'] = $vBIC_SWIFT_Code;
        $where = " iDriverId = '" . $iDriverId . "'";
        $obj->MySQLQueryPerform("register_driver", $Data_Update, 'update', $where);
        $returnArr['Action'] = "1";
        $returnArr['message'] = getDriverDetailInfo($iDriverId);
        setDataResponse($returnArr);
    }
}
########################Get Driver Bank Details############################
###############################################################################
if ($type == "getYearTotalEarnings") {
    global $generalobj, $obj;
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $year = isset($_REQUEST["year"]) ? $_REQUEST["year"] : @date('Y');
    if ($year == "") {
        $year = @date('Y');
    }
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId, '', 'true');
    $vCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');
    $vLangCode = get_value("register_driver", "vLang", "iDriverId", $iDriverId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $lngLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $start = @date('Y');
    $end = '1970';
    $year_arr = array();
    for ($j = $start;$j >= $end;$j--) {
        $year_arr[] = strval($j);
    }
    /* $Month_Array = array(
    
      '01' => 'Jan',
    
      '02' => 'Feb',
    
      '03' => 'Mar',
    
      '04' => 'Apr',
    
      '05' => 'May',
    
      '06' => 'Jun',
    
      '07' => 'Jul',
    
      '08' => 'Aug',
    
      '09' => 'Sep',
    
      '10' => 'Oct',
    
      '11' => 'Nov',
    
      '12' => 'Dec'
    
      ); */
    $Month_Array = array(
        '01' => $lngLabelsArr['LBL_JANUARY'],
        '02' => $lngLabelsArr['LBL_FEBRUARY'],
        '03' => $lngLabelsArr['LBL_MARCH'],
        '04' => $lngLabelsArr['LBL_APRIL'],
        '05' => $lngLabelsArr['LBL_MAY'],
        '06' => $lngLabelsArr['LBL_JUNE'],
        '07' => $lngLabelsArr['LBL_JULY'],
        '08' => $lngLabelsArr['LBL_AUGUST'],
        '09' => $lngLabelsArr['LBL_SEPTEMBER'],
        '10' => $lngLabelsArr['LBL_OCTOBER'],
        '11' => $lngLabelsArr['LBL_NOVEMBER'],
        '12' => $lngLabelsArr['LBL_DECEMBER']
    );
    $sql = "SELECT * FROM trips WHERE iDriverId='" . $iDriverId . "' AND tTripRequestDate LIKE '" . $year . "%' AND eSystem = 'General'";
    $tripData = $obj->MySQLSelect($sql);
    $totalEarnings = 0;
    //if(count($tripData) > 0){
    for ($i = 0;$i < count($tripData);$i++) {
        $iFare = $tripData[$i]['fTripGenerateFare'];
        $fCommision = $tripData[$i]['fCommision'];
        $priceRatio = $tripData[$i]['fRatio_' . $vCurrencyDriver];
        $totalEarnings += ($iFare - $fCommision) * $priceRatio;
    }
    $yearmontharr = array();
    $yearmontearningharr_Max = array();
    foreach ($Month_Array as $key => $value) {
        $tripyearmonthdate = $year . "-" . $key;
        $sql_Month = "SELECT * FROM trips WHERE iDriverId='" . $iDriverId . "' AND tTripRequestDate LIKE '" . $tripyearmonthdate . "%' AND eSystem = 'General'";
        $tripyearmonthData = $obj->MySQLSelect($sql_Month);
        $tripData_M = strval(count($tripyearmonthData));
        $yearmontearningharr = array();
        $totalEarnings_M = 0;
        for ($j = 0;$j < count($tripyearmonthData);$j++) {
            $iFare_M = $tripyearmonthData[$j]['fTripGenerateFare'];
            $fCommision_M = $tripyearmonthData[$j]['fCommision'];
            $priceRatio_M = $tripyearmonthData[$j]['fRatio_' . $vCurrencyDriver];
            $totalEarnings_M += ($iFare_M - $fCommision_M) * $priceRatio_M;
        }
        $yearmontearningharr_Max[] = $totalEarnings_M;
        $yearmontearningharr["CurrentMonth"] = $value;
        $yearmontearningharr["TotalEarnings"] = strval(round($totalEarnings_M < 0 ? 0 : $totalEarnings_M, 1));
        $yearmontearningharr["TripCount"] = strval(round($tripData_M, 1));
        array_push($yearmontharr, $yearmontearningharr);
    }
    foreach ($yearmontearningharr_Max as $key => $value) {
        if ($value >= $max) $max = $value;
    }
    $returnArr['Action'] = "1";
    $returnArr['TotalEarning'] = $vCurrencySymbol . " " . strval(round($totalEarnings, 1));
    $returnArr['TripCount'] = strval(count($tripData));
    $returnArr["CurrentYear"] = $year;
    $returnArr['MaxEarning'] = strval($max);
    $returnArr['YearMonthArr'] = $yearmontharr;
    $returnArr['YearArr'] = $year_arr;
    /* }else{
    
      $returnArr['Action'] = "0";
    
      } */
    setDataResponse($returnArr);
}
####################################################################################
if ($type == 'requestResetPassword') {
    global $generalobj, $obj, $tconfig;
    $Emid = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $userType = isset($_REQUEST["UserType"]) ? clean($_REQUEST["UserType"]) : ''; // UserType = Driver/Passenger
    if ($userType == "" || $userType == NULL) {
        $userType = "Passenger";
    }
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'h.iUserId as iMemberId, h.vPhone,h.vPhoneCode as vPhoneCode, h.vEmail, h.vName, h.vLastName, h.vPassword, h.vLang';
        $condfield = 'iUserId';
        $EncMembertype = base64_encode(base64_encode('rider'));
        $sql = "select $fields from $tblname as h where h.vEmail = '" . $Emid . "'";
    }
    else if ($userType == "Driver") {
        $tblname = "register_driver";
        $fields = 'h.iDriverId  as iMemberId, h.vPhone,h.vCode as vPhoneCode, h.vEmail, h.vName, h.vLastName,h.vPassword, h.vLang';
        $condfield = 'iDriverId';
        $EncMembertype = base64_encode(base64_encode('driver'));
        $sql = "select $fields from $tblname as h where h.vEmail = '" . $Emid . "'";
    }
    else {
        $tblname = "hotel";
        $fields = 'h.iHotelId  as iMemberId, a.vContactNo,a.vCode as vPhoneCode, a.vEmail, a.vFirstName, a.vLastName, a.vPassword, h.vLang';
        $condfield = 'iHotelId';
        $EncMembertype = base64_encode(base64_encode('hotel'));
        $sql = "select $fields from $tblname as h LEFT JOIN administrators as a on a.iAdminId=h.iAdminId where a.vEmail = '" . $Emid . "'";
    }
    //$sql = "select $fields from $tblname as h LEFT JOIN administrators as a on a.iAdminId=h.iAdminId where a.vEmail = '" . $Emid . "'"; // Commented By HJ On 02-01-2019 For Query Issue
    $db_member = $obj->MySQLSelect($sql);
    if (count($db_member) > 0) {
        $vLangCode = $db_member[0]['vLang'];
        if ($vLangCode == "" || $vLangCode == NULL) {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
        $clickherelabel = $languageLabelsArr['LBL_CLICKHERE_SIGNUP'];
        $milliseconds = time();
        $tempGenrateCode = substr($milliseconds, 1);
        $Today = Date('Y-m-d H:i:s');
        $today = base64_encode(base64_encode($Today));
        $type = $EncMembertype;
        $id = $generalobj->encrypt($db_member[0]["iMemberId"]);
        $newToken = $generalobj->RandomString(32);
        $url = $tconfig["tsite_url"] . 'reset_password.php?type=' . $type . '&id=' . $id . '&_token=' . $newToken;
        $activation_text = '<a href="' . $url . '" target="_blank"> ' . $clickherelabel . ' </a>';
        $maildata['EMAIL'] = $db_member[0]["vEmail"];
        $maildata['NAME'] = $db_member[0]["vFirstName"] . " " . $db_member[0]["vLastName"];
        $maildata['LINK'] = $activation_text;
        $status = $generalobj->send_email_user("CUSTOMER_RESET_PASSWORD", $maildata);
        if ($status == 1) {
            if ($tblname == "hotel") {
                $sql = "UPDATE $tblname as h LEFT JOIN administrators as a on a.iAdminId=h.iAdminId  set vPassword_token='" . $newToken . "' WHERE vEmail='" . $Emid . "' and a.eStatus != 'Deleted'";
            }
            else {
                $sql = "UPDATE $tblname set vPassword_token='" . $newToken . "' WHERE vEmail='" . $Emid . "' and eStatus != 'Deleted'";
            }
            $obj->sql_query($sql);
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_PASSWORD_SENT_TXT";
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ERROR_PASSWORD_MAIL";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WRONG_EMAIL_PASSWORD_TXT";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "SendTripMessageNotification") {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iFromMemberId = isset($_REQUEST["iFromMemberId"]) ? $_REQUEST["iFromMemberId"] : '';
    $iToMemberId = isset($_REQUEST['iToMemberId']) ? clean($_REQUEST['iToMemberId']) : '';
    $iTripId = isset($_REQUEST['iTripId']) ? clean($_REQUEST['iTripId']) : '';
    $tMessage = isset($_REQUEST['tMessage']) ? stripslashes($_REQUEST['tMessage']) : '';
    $Data['iTripId'] = $iTripId;
    $Data['iFromMemberId'] = $iFromMemberId;
    $Data['iToMemberId'] = $iToMemberId;
    $Data['tMessage'] = $tMessage;
    $Data['dAddedDate'] = @date("Y-m-d H:i:s");
    $Data['eStatus'] = "Unread";
    $Data['eUserType'] = $UserType;
    $id = $obj->MySQLQueryPerform('trip_messages', $Data, 'insert');
    if ($id > 0) {
        $returnArr['Action'] = "1";
        sendTripMessagePushNotification($iFromMemberId, $UserType, $iToMemberId, $iTripId, $tMessage);
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}
##############################################################
if ($type == "configDriverTripStatus") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $isSubsToCabReq = isset($_REQUEST["isSubsToCabReq"]) ? $_REQUEST["isSubsToCabReq"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $sql_driver = "SELECT vAvailability,vTripStatus,eStatus FROM `register_driver` WHERE iDriverId='" . $iMemberId . "'";
    $driverdetails = $obj->MySQLSelect($sql_driver);
    $vAvailability = $driverdetails[0]['vAvailability'];
    $vTripStatus = $driverdetails[0]['vTripStatus'];
    if ($iMemberId != "") {
        //if (!empty($isSubsToCabReq) && $isSubsToCabReq == 'true') {
        if ($vAvailability == "Available" && ($vTripStatus != "On Going Trip" && $vTripStatus != "Arrived" && $vTripStatus != "Active")) {
            $driver_update['tLastOnline'] = date('Y-m-d H:i:s');
            $driver_update['tOnline'] = date('Y-m-d H:i:s');
        }
        if (!empty($vLatitude) && !empty($vLongitude)) {
            $driver_update['vLatitude'] = $vLatitude;
            $driver_update['vLongitude'] = $vLongitude;
        }
        if (count($driver_update) > 0) {
            $where = " iDriverId = '" . $iMemberId . "'";
            $Update_driver = $obj->MySQLQueryPerform("register_driver", $driver_update, "update", $where);
            # Update User Location Date #
            Updateuserlocationdatetime($iMemberId, "Driver", $vTimeZone);
            # Update User Location Date #
            
        }
    }
    if ($iTripId != "") {
        $sql = "SELECT tMessage as msg, iStatusId FROM trip_status_messages WHERE iDriverId='" . $iMemberId . "' AND eToUserType='Driver' AND eReceived='No' ORDER BY iStatusId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    }
    else {
        $date = @date("Y-m-d");
        $sql = "SELECT passenger_requests.tMessage as msg  FROM passenger_requests LEFT JOIN driver_request ON  driver_request.iRequestId=passenger_requests.iRequestId  LEFT JOIN register_driver ON register_driver.iDriverId=passenger_requests.iDriverId where date_format(passenger_requests.dAddedDate,'%Y-%m-%d')= '" . $date . "' AND  passenger_requests.iDriverId=" . $iMemberId . " AND driver_request.eStatus='Timeout' AND driver_request.iDriverId='" . $iMemberId . "' AND register_driver.vTripStatus IN ('Not Active','NONE','Cancelled') ORDER BY passenger_requests.iRequestId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    }
    /* For DriverSubscription added by SP start */
    if ($vAvailability == "Available" && checkDriverSubscriptionModule()) {
        $returnSubStatus = 0;
        $sql = "SELECT count(iDriverSubscriptionPlanId) as cnt FROM driver_subscription_details WHERE iDriverId = $iMemberId";
        $getDriverSubscription = $obj->MySQLSelect($sql);
        if ($getDriverSubscription[0]['cnt'] > 0) {
            $returnSubStatus = checkDriverSubscribed($iMemberId);
            //$returnSubStatus = checkDriverPlanExpired($iMemberId);
            if ($returnSubStatus == 1) {
                $message_sub = "LBL_PENDING_MIXSUBSCRIPTION";
            }
            if ($returnSubStatus == 2) {
                $message_sub = "PENDING_SUBSCRIPTION";
            }
            if ($returnSubStatus == 1 || $returnSubStatus == 2) {
                //$returnArr['Action'] = "1";
                $returnArr['message_subscription'] = $message_sub;
                $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
                $curr_date = date('Y-m-d H:i:s');
                $selct_query = "select * from driver_log_report WHERE iDriverId = '" . $iMemberId . "' order by `iDriverLogId` desc limit 0,1";
                $get_data_log = $obj->sql_query($selct_query);
                $update_sql = "UPDATE driver_log_report set dLogoutDateTime = '" . $curr_date . "' WHERE iDriverLogId ='" . $get_data_log[0]['iDriverLogId'] . "'";
                $result = $obj->sql_query($update_sql);
                //update insurance log
                if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                    $details_arr['iTripId'] = "0";
                    $details_arr['LatLngArr']['vLatitude'] = $vLatitude;
                    $details_arr['LatLngArr']['vLongitude'] = $vLongitude;
                    // $details_arr['LatLngArr']['vLocation'] = "";
                    update_driver_insurance_status($iMemberId, "Available", $details_arr, "updateDriverStatus", "Offline");
                }
                $Data_update_driver['vAvailability'] = 'Not Available';
                $where = "iDriverId = '" . $iMemberId . "'";
                $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
            }
        }
    }
    /* For DriverSubscription added by SP end */
    $returnArr['Action'] = "0";
    if (!empty($msg)) {
        $returnArr['Action'] = "1";
        if ($iTripId != "") {
            //$updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iStatusId='".$msg[0]['iStatusId']."'";
            $updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iDriverId='" . $iMemberId . "'";
            $obj->sql_query($updateQuery);
            $returnArr['Action'] = "1";
            $returnArr['message'] = $msg[0]['msg'];
        }
        else {
            $driver_request['eStatus'] = "Received";
            $where = " iDriverId =" . $iMemberId . " and date_format(tDate,'%Y-%m-%d') = '" . $date . "' AND eStatus = 'Timeout' ";
            $obj->MySQLQueryPerform("driver_request", $driver_request, "update", $where);
            $returnArr['Action'] = "1";
            $dataArr = array();
            for ($i = 0;$i < count($msg);$i++) {
                $dataArr[$i] = $msg[$i]['msg'];
            }
            $returnArr['message'] = $dataArr;
        }
    }
    setDataResponse($returnArr);
}
######################################################################
if ($type == "configPassengerTripStatus") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $CurrentDriverIds = isset($_REQUEST["CurrentDriverIds"]) ? explode(',', $_REQUEST["CurrentDriverIds"]) : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    if ($CurrentDriverIds == "" && $iTripId != "") {
        $sql = "SELECT iDriverId FROM trips WHERE iTripId='" . $iTripId . "'";
        $data_requst = $obj->MySQLSelect($sql);
        $iDriverId = $data_requst[0]['iDriverId'];
        $CurrentDriverIds = (array)$iDriverId;
    }
    if ($iMemberId != "") {
        if (!empty($vLatitude) && !empty($vLongitude)) {
            $user_update['vLatitude'] = $vLatitude;
            $user_update['vLongitude'] = $vLongitude;
            $where = " iUserId = '" . $iMemberId . "'";
            $Update_driver = $obj->MySQLQueryPerform("register_user", $user_update, "update", $where);
            # Update User Location Date #
            Updateuserlocationdatetime($iMemberId, "Passenger", $vTimeZone);
            # Update User Location Date #
            
        }
    }
    $currDriver = array();
    if (!empty($CurrentDriverIds)) {
        $k = 0;
        foreach ($CurrentDriverIds as $cDriv) {
            $driverDetails = array();
            $driverDetails = get_value('register_driver', 'iDriverId,vLatitude,vLongitude', 'iDriverId', $cDriv);
            $currDriver[$k]['iDriverId'] = $driverDetails[0]['iDriverId'];
            $currDriver[$k]['vLatitude'] = $driverDetails[0]['vLatitude'];
            $currDriver[$k]['vLongitude'] = $driverDetails[0]['vLongitude'];
            $k++;
        }
    }
    $sql = "SELECT tMessage as msg, iStatusId FROM trip_status_messages WHERE iUserId='" . $iMemberId . "' AND eToUserType='Passenger' AND eReceived='No' ORDER BY iStatusId DESC LIMIT 1 ";
    $msg = $obj->MySQLSelect($sql);
    $returnArr['Action'] = "0";
    if (!empty($msg)) {
        //$updateQuery = "UPDATE trip_status_messages SET eReceived ='Yes' WHERE iStatusId='".$msg[0]['iStatusId']."'";
        $updateQuery = "UPDATE trip_status_messages SET eReceived ='Yes' WHERE iUserId='" . $iMemberId . "'";
        $obj->sql_query($updateQuery);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $msg[0]['msg'];
    }
    $returnArr['currentDrivers'] = $currDriver;
    setDataResponse($returnArr);
    //print_R($returnArr);exit;
    
}
###########################################################################
if ($type == "cancelCabRequest") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';
    if ($iCabRequestId == "") {
        // $data = get_value('cab_request_now', 'max(iCabRequestId),eStatus', 'iUserId',$iUserId);
        $sql = "SELECT iCabRequestId, eStatus FROM cab_request_now WHERE iUserId='" . $iUserId . "' ORDER BY iCabRequestId DESC LIMIT 1 ";
        $data = $obj->MySQLSelect($sql);
        $iCabRequestId = $data[0]['iCabRequestId'];
        $eStatus = $data[0]['eStatus'];
    }
    else {
        $data = get_value('cab_request_now', 'eStatus', 'iCabRequestId', $iCabRequestId, '', 'true');
        $eStatus = $data[0]['eStatus'];
    }
    if ($eStatus == "Requesting") {
        $where = " iCabRequestId='" . $iCabRequestId . "'";
        $Data_update_cab_request['eStatus'] = "Cancelled";
        $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_request, 'update', $where);
        if ($id) {
            //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 Start
            if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
                $obj->sql_query("UPDATE trip_outstanding_amount set iAuthoriseId = '0',eAuthoriseIdName = 'No' WHERE iUserId = '" . $iUserId . "' AND iAuthoriseId='" . $iCabRequestId . "' AND eAuthoriseIdName='iCabRequestId'");
            }
            //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 End
            $returnArr['Action'] = "1";
            $returnArr['message'] = "DO_RESET";
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_REQUEST_CANCEL_FAILED_TXT";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_RESTART";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "sendRequestToDrivers") {
    // echo "Before::".@date('Y-m-d H:i:s')."<BR/>";
    //echo "<pre>";print_r($_REQUEST);die;
    $driver_id_auto = isset($_REQUEST["driverIds"]) ? $_REQUEST["driverIds"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $passengerId = isset($_REQUEST["userId"]) ? $_REQUEST["userId"] : '';
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : 0;
    $eFemaleDriverRequest = isset($_REQUEST["eFemaleDriverRequest"]) ? $_REQUEST["eFemaleDriverRequest"] : '';
    $eHandiCapAccessibility = isset($_REQUEST["eHandiCapAccessibility"]) ? $_REQUEST["eHandiCapAccessibility"] : '';
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $DestAddress = isset($_REQUEST["DestAddress"]) ? $_REQUEST["DestAddress"] : '';
    $promoCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $iPackageTypeId = isset($_REQUEST["iPackageTypeId"]) ? $_REQUEST["iPackageTypeId"] : '';
    $vReceiverName = isset($_REQUEST["vReceiverName"]) ? $_REQUEST["vReceiverName"] : '';
    $vReceiverMobile = isset($_REQUEST["vReceiverMobile"]) ? $_REQUEST["vReceiverMobile"] : '';
    $tPickUpIns = isset($_REQUEST["tPickUpIns"]) ? $_REQUEST["tPickUpIns"] : '';
    $tDeliveryIns = isset($_REQUEST["tDeliveryIns"]) ? $_REQUEST["tDeliveryIns"] : '';
    $tPackageDetails = isset($_REQUEST["tPackageDetails"]) ? $_REQUEST["tPackageDetails"] : '';
    $vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST["iUserPetId"] : '0';
    $quantity = isset($_REQUEST["Quantity"]) ? $_REQUEST["Quantity"] : '';
    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';
    //echo "<pre>";print_r($_REQUEST);die;
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '0';
    $tUserComment = isset($_REQUEST["tUserComment"]) ? $_REQUEST["tUserComment"] : '';
    $eWalletDebitAllow = isset($_REQUEST["eWalletDebitAllow"]) ? $_REQUEST["eWalletDebitAllow"] : 'Yes';
    $vDistance = isset($_REQUEST["vDistance"]) ? $_REQUEST["vDistance"] : '';
    $vDuration = isset($_REQUEST["vDuration"]) ? $_REQUEST["vDuration"] : '';
    // payment flow 2 changes
    $eWalletIgnore = isset($_REQUEST["eWalletIgnore"]) ? $_REQUEST["eWalletIgnore"] : 'No';
    $ePayWallet = isset($_REQUEST["ePayWallet"]) ? $_REQUEST["ePayWallet"] : 'No';
    //echo $tUserComment;exit;
    $iTripReasonId = isset($_REQUEST["iTripReasonId"]) ? $_REQUEST["iTripReasonId"] : '';
    $vReasonTitle = isset($_REQUEST["vReasonTitle"]) ? $_REQUEST["vReasonTitle"] : ''; // For Other Reason
    // kiosk changes
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    $iHotelId = isset($_REQUEST["iHotelId"]) ? $_REQUEST["iHotelId"] : '0';
    /* added for rental */
    $iRentalPackageId = isset($_REQUEST["iRentalPackageId"]) ? $_REQUEST["iRentalPackageId"] : '';
    // add for hotel
    $eBookingFrom = isset($_REQUEST["eBookingFrom"]) ? $_REQUEST["eBookingFrom"] : '';
    $iHotelBookingId = isset($_REQUEST["iHotelBookingId"]) ? $_REQUEST["iHotelBookingId"] : '';
    $trip_status = "Requesting";
    $ePaymentBy = isset($_REQUEST["ePaymentBy"]) ? $_REQUEST["ePaymentBy"] : '';
    //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data Start
    $orderDetails = isset($_REQUEST['OrderDetails']) ? $_REQUEST['OrderDetails'] : '';
    $eServiceLocation = isset($_REQUEST['eServiceLocation']) ? $_REQUEST['eServiceLocation'] : 'Passanger';
    //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data End
    $total_del_dist = isset($_REQUEST["total_del_dist"]) ? $_REQUEST["total_del_dist"] : '';
    $total_del_time = isset($_REQUEST["total_del_time"]) ? $_REQUEST["total_del_time"] : '';
    //added by SP for fly stations on 19-08-2019 start
    $iFromStationId = isset($_REQUEST["iFromStationId"]) ? $_REQUEST["iFromStationId"] : '';
    $iToStationId = isset($_REQUEST["iToStationId"]) ? $_REQUEST["iToStationId"] : '';
    $orderDetails = preg_replace('/[[:cntrl:]]/', '\r\n', $orderDetails);
    if (!empty($iFromStationId) && !empty($iToStationId)) {
        $Data_update_passenger['iFromStationId'] = $iFromStationId;
        $Data_update_passenger['iToStationId'] = $iToStationId;
    }
    //added by SP for fly stations on 19-08-2019 end
    // check promocode valid or not
    /* $test = isset($_REQUEST["test"]) ? $_REQUEST["test"] : "";
    
      if($test == 1){
    
      $validPromoCodesArr = getValidPromoCodes();
    
      //print_r($validPromoCodesArr);die;
    
      if (( !empty($validPromoCodesArr) && !empty($validPromoCodesArr['CouponList']) && count($validPromoCodesArr['CouponList']) > 0) == false) {
    
      $returnArr = array();
    
      $returnArr['Action'] = "0";
    
      $returnArr['message'] = "LBL_INVALID_COUPON_CODE";
    
      setDataResponse($returnArr);
    
      }
    
      } */
    if (($iHotelId == '' || $iHotelId < 1) && (strtolower($GeneralUserType) == 'hotel' || strtolower($GeneralUserType) == 'kiosk')) {
        $GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? trim($_REQUEST['GeneralMemberId']) : '';
        $iHotelId = $GeneralMemberId;
    }
    if ($iHotelId > 0) {
        $sql_hotel_data = "SELECT administrators.vAddress,administrators.vFirstName, hotel.iAdminId FROM administrators, hotel WHERE administrators.iAdminId = hotel.iAdminId AND hotel.iHotelId = '" . $iHotelId . "' AND hotel.eStatus = 'Active' AND administrators.eStatus = 'Active'";
        $hotel_data = $obj->MySQLSelect($sql_hotel_data);
        if (empty($hotel_data) || count($hotel_data) == 0) {
            $returnArr = array();
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_HOTEL_DISABLED";
            setDataResponse($returnArr);
        }
        if (!empty($hotel_data)) {
            $PickUpAddress = $hotel_data[0]['vFirstName'] . " \n" . $hotel_data[0]['vAddress'];
        }
    }
    $vDuration = empty($vDuration) ? 0 : $vDuration;
    $vDistance = empty($vDistance) ? 0 : $vDistance;
    $vDuration = round(($vDuration / 60) , 2);
    $vDistance = round(($vDistance / 1000) , 2);
    if ($eWalletDebitAllow == "" || $eWalletDebitAllow == NULL) {
        $eWalletDebitAllow = "Yes";
    }
    if ($eSystemAppType == 'kiosk') {
        $eWalletDebitAllow = "No";
    }
    //Added By HJ On 01-02-2019 For Get Vehicle Type Total Fare Amount Start
    $TripData = $fareDetails = array();
    if ($SERVICE_PROVIDER_FLOW == "Provider" && $eType == "UberX") {
        $fareDetails = getVehicleTypeFareDetails();
        $typeDataArr = json_decode($orderDetails);
        if (!empty($fareDetails)) {
            $Data_update_passenger['tVehicleTypeFareData'] = json_encode($fareDetails['tripFareDetailsSaveArr']);
        }
        $selectedCarTypeID = 0;
        if (count($typeDataArr) == 1) {
            $selectedCarTypeID = $typeDataArr[0]->iVehicleTypeId;
        }
    }
    if (empty($eServiceLocation)) {
        $eServiceLocation = "Passenger";
    }
    ####### blocking code ################
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $BlockData = getBlockData("Passenger", $passengerId);
        if (!empty($BlockData) || $BlockData != "") {
            setDataResponse($BlockData);
        }
    }
    ####### blocking code ################
    $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $passengerId . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
    $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
    $iCabRequestId_cab_now = "";
    $eStatus_cab_now = "";
    if (count($Data_cabrequest) > 0) {
        $iCabRequestId_cab_now = $Data_cabrequest[0]['iCabRequestId'];
        $eStatus_cab_now = $Data_cabrequest[0]['eStatus'];
        if ($eStatus_cab_now == "Requesting") {
            $where_cab_now = " iCabRequestId = '$iCabRequestId_cab_now' ";
            $Data_update_cab_now['eStatus'] = "Cancelled";
            $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where_cab_now);
        }
    }
    checkmemberemailphoneverification($passengerId, "Passenger");
    ## check pickup addresss for UberX #
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    if ($eType == "") {
        $eType = $APP_TYPE == "Delivery" ? "Deliver" : $APP_TYPE;
    }
    $CheckSurgeAddress = $PickUpAddress;
    $Data_update_passenger['tSourceAddress'] = $PickUpAddress;
    if ($eType == "UberX") {
        $Data_update_passenger['tUserComment'] = $tUserComment;
        //$PickUpAddress=get_value('user_address', 'vServiceAddress', ' iUserAddressId',$iUserAddressId,'','true');
        if ($iUserAddressId != "" && $iUserAddressId > 0) {
            $Address = get_value('user_address', 'vAddressType,vBuildingNo,vLandmark,vServiceAddress,vLatitude,vLongitude', '   iUserAddressId', $iUserAddressId, '', '');
            $vAddressType = $Address[0]['vAddressType'];
            $vBuildingNo = $Address[0]['vBuildingNo'];
            $vLandmark = $Address[0]['vLandmark'];
            $vServiceAddress = $Address[0]['vServiceAddress'];
            $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
            $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
            $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
            $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
            $Data_update_passenger['tSourceAddress'] = $PickUpAddress;
            $Data_update_passenger['iUserAddressId'] = $iUserAddressId;
            $PickUpLatitude = $Address[0]['vLatitude'];
            $PickUpLongitude = $Address[0]['vLongitude'];
            $CheckSurgeAddress = $vServiceAddress;
        }
        else {
            if ($eServiceLocation == "Driver" && $SERVICE_PROVIDER_FLOW == "Provider") {
                $provider_data = get_value('register_driver', 'eSelectWorkLocation,vWorkLocationLatitude,vWorkLocationLongitude,vWorkLocation,vLatitude,vLongitude,vLang', 'iDriverId', $driver_id_auto);
                if ($provider_data[0]['eSelectWorkLocation'] == "Dynamic") {
                    $PickUpLatitude = $provider_data[0]['vLatitude'];
                    $PickUpLongitude = $provider_data[0]['vLongitude'];
                    $PickUpAddress = "";
                }
                else {
                    $PickUpLatitude = $provider_data[0]['vWorkLocationLatitude'];
                    $PickUpLongitude = $provider_data[0]['vWorkLocationLongitude'];
                    $PickUpAddress = $provider_data[0]['vWorkLocation'];
                }
            }
            $Data_update_passenger['tSourceAddress'] = $PickUpAddress;
        }
    }
    ## check pickup addresss for UberX #
    ### Checking For Pickup And DropOff Disallow ###
    $pickuplocationarr = array($PickUpLatitude,$PickUpLongitude);
    $allowed_ans_pickup = checkAllowedAreaNew($pickuplocationarr, "No");
    if ($allowed_ans_pickup == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    if ($DestLatitude != "" && $DestLongitude != "") {
        $dropofflocationarr = array($DestLatitude,$DestLongitude);
        $allowed_ans_dropoff = checkAllowedAreaNew($dropofflocationarr, "Yes");
        if ($allowed_ans_dropoff == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
            setDataResponse($returnArr);
        }
    }
    ### Checking For Pickup And DropOff Disallow ###
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    //Added By HJ On 17-06-2020 For Optimize language_master Table Query Start
    if (empty($vSystemDefaultLangCode)) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $vSystemDefaultLangCode = $vLangCode;
    }
    else {
        $vLangCode = $vSystemDefaultLangCode;
    }
    //Added By HJ On 17-06-2020 For Optimize language_master Table Query End
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING'];
    $alertMsg = $languageLabelsArr['LBL_DELIVERY_SENDER_WAITING'];
    if ($eType == "UberX") {
        $alertMsg = $languageLabelsArr['LBL_USER_WAITING'];
    }
    else if ($eType == "Ride") {
        $alertMsg = $userwaitinglabel;
    }
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $DropOff = "No";
    if ($DestLatitude != "" && $DestLongitude != "") {
        $DropOff = "Yes";
    }
    $DataArr = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude, $address_data, $DropOff, "No", "No", "", $DestLatitude, $DestLongitude, $eType, "", $driver_id_auto);
    //echo "<pre>";print_r($DataArr);die;
    $Data = $DataArr['DriverList'];
    $driver_id_auto_tmp = "";
    foreach ($Data as $driver_item_tmp) {
        $driverDataIdsArr_tmp[] = $driver_item_tmp['iDriverId'];
    }
    $driverIdsArr_tmp = explode(",", $driver_id_auto);
    foreach ($driverIdsArr_tmp as $driverId_tmp) {
        if (in_array($driverId_tmp, $driverDataIdsArr_tmp)) {
            $driver_id_auto_tmp = $driver_id_auto_tmp == "" ? $driverId_tmp : $driver_id_auto_tmp . "," . $driverId_tmp;
        }
    }
    $driver_id_auto = $driver_id_auto_tmp;
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
    //Added By HJ On 20-06-2020 For Optimization register_user Table Query Start
    if(isset($userDetailsArr["register_user_".$passengerId])){
        $UserDetail = $userDetailsArr["register_user_".$passengerId];
    }else{
       $UserDetail = $obj->MySQLSelect("SELECT * FROM register_user WHERE iUserId='".$passengerId."'");
       $userDetailsArr["register_user_".$passengerId] = $UserDetail;
    }
    if(count($UserDetail) > 0){
        $vCurrencyPassenger = $UserDetail[0]['vCurrencyPassenger'];
        if(isset($currencyAssociateArr[$vCurrencyPassenger])){
            $userCurrencyData = $currencyAssociateArr[$vCurrencyPassenger];
            //echo "<pre>";print_r($userCurrencyData);die;
            $UserDetail[0]['Ratio'] = $userCurrencyData['Ratio'];
            $UserDetail[0]['vSymbol'] = $userCurrencyData['vSymbol'];
        }
    }
    //Added By HJ On 20-06-2020 For Optimization register_user Table Query End
    //$UserDetail = get_value('register_user AS ru LEFT JOIN currency AS c ON c.vName=ru.vCurrencyPassenger', 'ru.vCurrencyPassenger,c.Ratio,c.vSymbol, ru.iGcmRegId,ru.vName,ru.vLastName,ru.vImgName,ru.vFbId,ru.vAvgRating,ru.vPhone,ru.vPhoneCode,ru.fTripsOutStandingAmount, ru.vLang', 'ru.iUserId', $passengerId);
    $passengerData = array();
    if(count($UserDetail) > 0){
        $passengerData = $UserDetail;
    }
    $userLanguageCode = $passengerData[0]['vLang'];
    if (empty($userLanguageCode)) {
        $userLanguageCode = $vLangCode;
        $userLanguageLabelsArr = $languageLabelsArr;
    }
    else {
        if ($userLanguageCode == $vLangCode) {
            $userLanguageLabelsArr = $languageLabelsArr;
        } else {
            $userLanguageLabelsArr = getLanguageLabelsArr($userLanguageCode, "1", $iServiceId);
        }
    }
    $iGcmRegId = $passengerData[0]['iGcmRegId'];
    if ($vDeviceToken != "" && $vDeviceToken != $iGcmRegId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        setDataResponse($returnArr);
    }
    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($passengerId);
    //added by SP for fly stations on 27-09-2019 start
    $final_message['eFly'] = "No";
    if (!empty($iFromStationId) && !empty($iToStationId)) {
        $final_message['eFly'] = "Yes";
    }
    //added by SP for fly stations on 27-09-2019 end
    $passengerFName = $passengerData[0]['vName'];
    $passengerLName = $passengerData[0]['vLastName'];
    $final_message['PName'] = $passengerFName . " " . $passengerLName;
    $final_message['PPicName'] = $passengerData[0]['vImgName'];
    $final_message['PFId'] = $passengerData[0]['vFbId'];
    $final_message['PRating'] = $passengerData[0]['vAvgRating'];
    $final_message['PPhone'] = $passengerData[0]['vPhone'];
    $final_message['PPhoneC'] = $passengerData[0]['vPhoneCode'];
    $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
    $final_message['REQUEST_TYPE'] = $eType;
    // packagename changes
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] = $alertMsg;
    $final_message['iTripId'] = $iCabRequestId_cab_now;
    $final_message['iHotelId'] = $iHotelId;
    $final_message['eSystem'] = "";
    //$final_message['Time']= strval(date('Y-m-d'));
    $ALLOW_SERVICE_PROVIDER_AMOUNT = "No";
    $vVehicleTypeName = $eFareType = "";
    $fAmount = 0;
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    if ($eType == "UberX") {
        if ($quantity < 1) {
            $quantity = 1;
        }
        $vVehicleTypeName = "";
        $eFareType = "";
        $minHour_ufx = - 1;
        if (isset($fareDetails['originalFareTotal']) && !empty($fareDetails['originalFareTotal'])) {
            $iPrice = $fareDetails['originalFareTotal'];
            $vVehicleTypeName = $fareDetails['ParentCategoryName'];
            $eFareType = $fareDetails['eFareTypeServices'];
        }
        else if ($selectedCarTypeID > 0) {
            $sqlv = "SELECT vt.iVehicleCategoryId,vt.vVehicleType_" . $vLangCode . " as vVehicleTypeName,vc.iParentId,vt.eFareType,vt.ePickStatus,vt.eNightStatus,vt.fFixedFare, vt.iBaseFare, vt.iMinFare, vt.fMinHour, vc.ePriceType From vehicle_type as vt LEFT JOIN " . $sql_vehicle_category_table_name . " as vc ON  vc.iVehicleCategoryId = vt.iVehicleCategoryId WHERE vt.iVehicleTypeId = '" . $selectedCarTypeID . "'";
            $tripVehicleData = $obj->MySQLSelect($sqlv);
            $iVehicleCategoryId = $tripVehicleData[0]['iVehicleCategoryId'];
            $vVehicleTypeName = $tripVehicleData[0]['vVehicleTypeName'];
            $eFareType = $tripVehicleData[0]['eFareType'];
            $iParentId = $tripVehicleData[0]['iParentId'];
            if ($iParentId == 0) {
                $ePriceType = $tripVehicleData[0]['ePriceType'];
            }
            else {
                $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
            }
            $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
            if ($eFareType != "Regular") {
                if ($eFareType == "Fixed") {
                    $fAmount = $tripVehicleData[0]['fFixedFare'] * $quantity;
                }
                else {
                    $minHour_ufx = $tripVehicleData[0]['fMinHour'];
                }
                //Added By HJ On 01-02-2019 For Get Vehicle Type Total Fare Amount Start
                $checkProviderAmt = 1;
                if (isset($fareDetails['originalFareTotal']) && $fareDetails['originalFareTotal'] > 0) {
                    $iPrice = $fareDetails['originalFareTotal'];
                    $vVehicleTypeName = $fareDetails['ParentCategoryName'];
                    $checkProviderAmt = 0;
                }
                //Added By HJ On 01-02-2019 For Get Vehicle Type Total Fare Amount End
                if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $checkProviderAmt == 1) {
                    $sql12 = "SELECT iDriverVehicleId FROM  `driver_vehicle` WHERE iDriverId = '" . $driver_id_auto . "' AND eType='UberX'";
                    $drivervehicleData = $obj->MySQLSelect($sql12);
                    $iDriverVehicleId = $drivervehicleData[0]['iDriverVehicleId'];
                    $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $selectedCarTypeID . "'";
                    $serviceProData = $obj->MySQLSelect($sqlServicePro);
                    if (count($serviceProData) > 0) {
                        $fAmount = $serviceProData[0]['fAmount'] * $quantity;
                    }
                    else {
                        $fAmount = $iPrice;
                    }
                    $iPrice = $fAmount;
                }
            }
            else {
                $iBaseFare = round($tripVehicleData[0]['iBaseFare'], 2);
                $iMinFare = round($tripVehicleData[0]['iMinFare'], 2);
                $fAmount = ($iMinFare > $iBaseFare) ? $iMinFare : $iBaseFare;
            }
            $iPrice = $fAmount;
            // added for payment method 2 //
            if ($iVehicleCategoryId != 0) {
                $vVehicleCategoryName = get_value($sql_vehicle_category_table_name, 'vCategory_' . $vLangCode, 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
                $vVehicleTypeName = $vVehicleCategoryName . "-" . $vVehicleTypeName;
            }
        }
        $final_message['SelectedTypeName'] = $vVehicleTypeName;
        $final_message['eFareType'] = $eFareType;
    }
    $ePoolStatus = "No";
    $vehiclePesonSize = 1;
    if ($eType == "Ride" && strtoupper(PACKAGE_TYPE) == "SHARK") {
        poolVariableGetForSendRequest();
    }
    //Added By Hasmukh On
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        BookForSomeOneElse();
    }
    
    $ePickStatus = $tripVehicleData[0]['ePickStatus'];
    $eNightStatus = $tripVehicleData[0]['eNightStatus'];
    $fPickUpPrice = $fNightPrice = 1;
    $sourceLocationArr = array($PickUpLatitude,$PickUpLongitude);
    $destinationLocationArr = array($DestLatitude,$DestLongitude);
    $data_flattrip['eFlatTrip'] = "No";
    $data_flattrip['Flatfare'] = 0;
    if ($eType == 'Ride' && strtoupper(PACKAGE_TYPE) != "STANDARD") {
        $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $selectedCarTypeID, $iRentalPackageId);
    }
    if ($eType != "UberX" || ($eType == "UberX" && !empty($eFareType) && $eFareType == "Regular")) {
        $data_surgePrice = checkSurgePrice($selectedCarTypeID, "", $iRentalPackageId);
        if ($data_surgePrice['Action'] == "0") {
            if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
            }
            else {
                $fNightPrice = $data_surgePrice['SurgePriceValue'];
            }
        }
    }
    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
        $fPickUpPrice = $fNightPrice = 1;
    }
    // add airport surge //
    $fpickupsurchargefare = $fdropoffsurchargefare = 0;
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes' && $eType != "UberX") {
            $pickuplocationarr = array($PickUpLatitude,$PickUpLongitude);
            $dropofflocationarr = array($DestLatitude,$DestLongitude);
            $GetVehicleIdfromGeoLocation = CheckSurgeAirportFromGeoLocation($pickuplocationarr, $dropofflocationarr, $selectedCarTypeID);
            $fpickupsurchargefare = $GetVehicleIdfromGeoLocation['fpickupsurchargefare'];
            $fdropoffsurchargefare = $GetVehicleIdfromGeoLocation['fdropoffsurchargefare'];
            //$airportsurgetype = $AIRPORT_SURGE_ADD_OR_OVERRIDE;
            
        }
    }
    $Data_update_passenger['fAirportPickupSurge'] = $fpickupsurchargefare;
    $Data_update_passenger['fAirportDropoffSurge'] = $fdropoffsurchargefare;
    // end airport surge //
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $sql_driver_status_chk = "SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion FROM register_driver WHERE tLocationUpdateDate > '$str_date' AND iDriverId IN (" . $driver_id_auto . ")";
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        reGenerateRequestQueryForDriver();
    }
    $result_driverData = $obj->MySQLSelect($sql_driver_status_chk);
    // echo "Res:count:".count($result);exit;
    if (count($result_driverData) == 0 || $driver_id_auto == "" || count($Data) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "NO_CARS";
        setDataResponse($returnArr);
    }
    $tripPaymentMode = "Card";
    if ($cashPayment == 'true') {
        $tripPaymentMode = "Cash";
    }
    $where = "";
    $Data_update_passenger['ePayType'] = $tripPaymentMode;
    $Data_update_passenger['fTollPrice'] = "0";
    $Data_update_passenger['vTollPriceCurrencyCode'] = "";
    $Data_update_passenger['eTollSkipped'] = "No";
    //Added By HJ On 26-10-2019 For Insert toll Data As Per App Data Discuss With KS Start
    if ((empty($fTollPrice) || $fTollPrice <= 0) && $eTollSkipped == "Yes") {
        $Data_update_passenger['eTollSkipped'] = $eTollSkipped;
    }
    //Added By HJ On 26-10-2019 For Insert toll Data As Per App Data Discuss With KS End
    //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data Start
    $Data_update_passenger['eServiceLocation'] = $eServiceLocation;
    //$Data_update_passenger['tVehicleTypeData'] = stripcslashes($orderDetails); // Commented By HJ On 23-11-2019 For Soled \n data issue
    $Data_update_passenger['tVehicleTypeData'] = $orderDetails; // Added By HJ On 23-11-2019 For Soled \n data issue
    // echo "Before::".@date('Y-m-d H:i:s')."<BR/>";
    //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data End
    if (($eTollSkipped == 'No' || $fTollPrice != "") && $eType != "Multi-Delivery" && $fTollPrice > 0) {
        $fTollPrice_Original = $fTollPrice;
        $vTollPriceCurrencyCode = strtoupper($vTollPriceCurrencyCode);
        $default_currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        $sql = " SELECT round(($fTollPrice/(SELECT Ratio FROM currency where vName='" . $vTollPriceCurrencyCode . "'))*(SELECT Ratio FROM currency where vName='" . $default_currency . "' ) ,2)  as price FROM currency  limit 1";
        $result_toll = $obj->MySQLSelect($sql);
        $fTollPrice = $result_toll[0]['price'];
        if ($fTollPrice == 0) {
            $fTollPrice = get_currency($vTollPriceCurrencyCode, $default_currency, $fTollPrice_Original);
        }
        $Data_update_passenger['fTollPrice'] = $fTollPrice;
        $Data_update_passenger['vTollPriceCurrencyCode'] = $vTollPriceCurrencyCode;
        $Data_update_passenger['eTollSkipped'] = $eTollSkipped;
    }
    // echo "Before::".@date('Y-m-d H:i:s')."<BR/>";exit;
    $Data_update_passenger['iUserId'] = $passengerId;
    $Data_update_passenger['tMsgCode'] = $final_message['MsgCode'];
    $Data_update_passenger['eStatus'] = 'Requesting';
    $Data_update_passenger['vSourceLatitude'] = $PickUpLatitude;
    $Data_update_passenger['vSourceLongitude'] = $PickUpLongitude;
    $Data_update_passenger['vDestLatitude'] = $DestLatitude;
    $Data_update_passenger['vDestLongitude'] = $DestLongitude;
    $Data_update_passenger['tDestAddress'] = $DestAddress;
    $Data_update_passenger['iVehicleTypeId'] = $selectedCarTypeID;
    $Data_update_passenger['fPickUpPrice'] = $fPickUpPrice;
    $Data_update_passenger['fNightPrice'] = $fNightPrice;
    $Data_update_passenger['eType'] = $eType;
    $Data_update_passenger['iPackageTypeId'] = $eType == "Deliver" ? $iPackageTypeId : '';
    $Data_update_passenger['vReceiverName'] = $eType == "Deliver" ? $vReceiverName : '';
    $Data_update_passenger['vReceiverMobile'] = $eType == "Deliver" ? $vReceiverMobile : '';
    $Data_update_passenger['tPickUpIns'] = $eType == "Deliver" ? $tPickUpIns : '';
    $Data_update_passenger['tDeliveryIns'] = $eType == "Deliver" ? $tDeliveryIns : '';
    $Data_update_passenger['tPackageDetails'] = $eType == "Deliver" ? $tPackageDetails : '';
    $Data_update_passenger['vCouponCode'] = $promoCode;
    $Data_update_passenger['iQty'] = $quantity;
    $Data_update_passenger['vRideCountry'] = $vCountryCode;
    $Data_update_passenger['eFemaleDriverRequest'] = $eFemaleDriverRequest;
    $Data_update_passenger['eHandiCapAccessibility'] = $eHandiCapAccessibility;
    $Data_update_passenger['vTimeZone'] = $vTimeZone;
    $Data_update_passenger['dAddedDate'] = date("Y-m-d H:i:s");
    $Data_update_passenger['eFlatTrip'] = $data_flattrip["eFlatTrip"];
    $Data_update_passenger['fFlatTripPrice'] = $data_flattrip["Flatfare"];
    /* added for rental */
    $Data_update_passenger['iRentalPackageId'] = $iRentalPackageId;
    $Data_update_passenger['fDistance'] = $vDistance;
    $Data_update_passenger['fDuration'] = $vDuration;
    $Data_update_passenger['iHotelId'] = $iHotelId;
    ###### payment method 2 #########
    $Data_update_passenger['ePayWallet'] = $ePayWallet;
    //echo "<pre>";print_r($Data_update_passenger);die;
    ######### payment method 2 #########
    if (!empty($iHotelId) && $iHotelId != '0') {
        if (isset($eBookingFrom) && !empty($eBookingFrom)) {
            $Data_update_passenger['eBookingFrom'] = $eBookingFrom;
        }
        else {
            $Data_update_passenger['eBookingFrom'] = 'Kiosk';
        }
        //$Data_update_passenger['eBookingFrom'] = 'Kiosk';
        // $iHotelId
        $iHotelAdminId = "";
        if (!empty($iHotelBookingId)) {
            $iHotelAdminId = $iHotelBookingId;
        }
        else {
            if (!empty($hotel_data)) {
                $iHotelAdminId = $hotel_data[0]['iAdminId'];
            }
            else {
                $Asql = "SELECT a.iAdminId FROM hotel as h LEFT JOIN administrators as a on a.iAdminId=h.iAdminId WHERE h.iHotelId='" . $iHotelId . "'";
                $resultadmin = $obj->MySQLSelect($Asql);
                $iHotelAdminId = $resultadmin[0]['iAdminId'];
            }
        }
        $Data_update_passenger['iHotelBookingId'] = $iHotelAdminId;
    }
    else {
        // add for hotel web
        $Data_update_passenger['eBookingFrom'] = $eBookingFrom;
        $Data_update_passenger['iHotelBookingId'] = $iHotelBookingId;
    }
    if ($eType == "Multi-Delivery") {
        $Data_update_passenger['fDuration'] = $total_del_time;
        $Data_update_passenger['fDistance'] = $total_del_dist;
    }
    $Data_update_passenger['eWalletDebitAllow'] = $eWalletDebitAllow;
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        GetSendRequestToDriverParam();
    }
    if ($iTripReasonId != "" || $vReasonTitle != "") {
        $Data_update_passenger['iTripReasonId'] = $iTripReasonId;
        $Data_update_passenger['vReasonTitle'] = $vReasonTitle;
        $Data_update_passenger['eTripReason'] = "Yes";
    }
    // Payment Method 2 Flow Start
    $isDestinationAdded = "No";
    if ($DestLatitude != "" && $DestLongitude != "") {
        $isDestinationAdded = "Yes";
    }
    // get user wallet balance
    $user_available_balance_wallet = $generalobj->get_user_available_balance($passengerId, "Rider", true);
    //print_R($user_available_balance_wallet);
    $walletDataArr = array();
    if (is_array($user_available_balance_wallet)) {
        $walletDataArr = $user_available_balance_wallet;
        //print_R($walletDataArr); exit;
        $user_available_balance_wallet = $walletDataArr['CurrentBalance'];
        $Data_update_passenger['tUserWalletBalance'] = $walletDataArr['AutorizedWalletBalance'];
    }
    //print_R($user_available_balance_wallet); exit;
    $ratio = $UserDetail[0]['Ratio'];
    $currency_vSymbol = $UserDetail[0]['vSymbol'];
    $user_available_balance_wallet = $user_available_balance_wallet * $ratio;
    /*     * * Checking User's wallet balance respect to 'Method-2,3' Payment flow ** */
    //Added By HJ On 10-06-2019 For Get User Out Standing Amount For Payment Method 2 Or 3 Start
    $userOutStandingAmt = 0;
    $outstandingIds = "";
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $getUserOutstandingAmount = getUserOutstandingAmount($passengerId, "iCabRequestId");
        $userOutStandingAmt = $getUserOutstandingAmount['fPendingAmount'];
        $outstandingIds = $getUserOutstandingAmount['iTripOutstandId'];
    }
    //Added By HJ On 10-06-2019 For Get User Out Standing Amount For Payment Method 2 Or 3 End
    if ($cashPayment == 'false' && (($iHotelId == '' || $iHotelId < 1) && strtoupper($GeneralUserType) != 'HOTEL') && ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
        $fareAmount = 0;
        //print_r($fareAmount);die;
        if (isset($fareDetails['tripFareDetailsSaveArr']) && count($fareDetails['tripFareDetailsSaveArr']) > 0) {
            $minHour = - 1;
            if (!empty($fareDetails['tripFareDetailsSaveArr']['eFareTypeServices']) && $fareDetails['tripFareDetailsSaveArr']['eFareTypeServices'] == "Hourly" && count($fareDetails['tripFareDetailsSaveArr']['FareData']) > 0) {
                $minHour = $fareDetails['tripFareDetailsSaveArr']['FareData'][0]['MinimumHour'];
            }
            $fareAmount = $fareDetails['tripFareDetailsSaveArr']['subTotal'];
            //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir Start
            if ($promoCode != "") {
                $discValue = calculateCouponCodeValue($promoCode, $fareAmount, $ratio);
                if ($discValue > 0) {
                    $fareAmount = $fareAmount - $discValue;
                }
            }
            //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir End
            if ($minHour > 0) {
                $fareAmount = $fareAmount * $minHour;
            }
        }
        else if ($selectedCarTypeID > 0 && $isDestinationAdded == "Yes") {
            $Fare_data_New = calculateFareEstimateAll($vDuration, $vDistance, $selectedCarTypeID, $passengerId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", $isDestinationAdded, $data_flattrip['eFlatTrip'], $data_flattrip["Flatfare"], $sourceLocationArr, $destinationLocationArr, "Yes", $eType);
            //echo "<pre>";print_r($Fare_data_New);die;
            $fareAmount = $Fare_data_New[0]['total_fare_amount'];
            if ($Fare_data_New[0]['eRental'] == "Yes" && $Fare_data_New[0]['eRental_total_fare_value'] > 0 && !empty($_REQUEST["iRentalPackageId"]) && $_REQUEST["iRentalPackageId"] > 0) {
                $fareAmount = $Fare_data_New[0]['eRental_total_fare_value'];
                //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir Start
                if ($promoCode != "") {
                    $discValue = calculateCouponCodeValue($promoCode, $fareAmount, $ratio);
                    if ($discValue > 0) {
                        $fareAmount = $fareAmount - $discValue;
                    }
                }
                //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir End
                
            }
            //Added By HJ On 07-08-2019 For Calculate Pool Person Wise Fare Amount By Defined Pool Pecentage Start
            $fPoolPercentage = 1;
            if (isset($Fare_data_New[0]['fPoolPercentage']) && $Fare_data_New[0]['fPoolPercentage'] > 0) {
                $fPoolPercentage = $Fare_data_New[0]['fPoolPercentage'];
            }
            if ($ePoolStatus == "Yes" && !empty($_REQUEST['iPersonSize']) && $_REQUEST['iPersonSize'] > 1 && $fPoolPercentage > 1) {
                $extraSeatCharge = $fareAmount * $fPoolPercentage / 100;
                //$totalSeatCharge = $extraSeatCharge * $_REQUEST['iPersonSize'];
                $fareAmount += $extraSeatCharge;
            }
            //Added By HJ On 07-08-2019 For Calculate Pool Person Wise Fare Amount By Defined Pool Pecentage End
            if (!empty($Data_update_passenger['fTollPrice']) && $Data_update_passenger['fTollPrice'] > 0 && !empty($Data_update_passenger['eTollSkipped']) && strtoupper($Data_update_passenger['eTollSkipped']) == "NO") {
                $fareAmount += $Data_update_passenger['fTollPrice'];
            }
        }
        else if ($selectedCarTypeID > 0 && $isDestinationAdded != "Yes" && $eType == 'Ride') {
            $Fare_data_New = calculateFareEstimateAll($vDuration, $vDistance, $selectedCarTypeID, $passengerId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", $isDestinationAdded, $data_flattrip['eFlatTrip'], $data_flattrip["Flatfare"], $sourceLocationArr, $destinationLocationArr, "Yes", $eType);
            //echo "<pre>";print_r($Fare_data_New);die;
            $fareAmount = $Fare_data_New[0]['total_fare_amount'];
            if ($Fare_data_New[0]['eRental'] == "Yes" && $Fare_data_New[0]['eRental_total_fare_value'] > 0 && !empty($_REQUEST["iRentalPackageId"]) && $_REQUEST["iRentalPackageId"] > 0) {
                $Fare_data_New[0]['fBufferAmount'] = 0;
                $fareAmount = $Fare_data_New[0]['eRental_total_fare_value'];
                //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir Start
                if ($promoCode != "") {
                    $discValue = calculateCouponCodeValue($promoCode, $fareAmount, $ratio);
                    if ($discValue > 0) {
                        $fareAmount = $fareAmount - $discValue;
                    }
                }
                //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir End
                
            }
            $fBufferAmount = $Fare_data_New[0]['fBufferAmount'];
            if ($fBufferAmount > 0) {
                $fBufferAmount = $fBufferAmount * $ratio;
            }
            $fareAmount = $fareAmount + $fBufferAmount;
            if (!empty($Data_update_passenger['fTollPrice']) && $Data_update_passenger['fTollPrice'] > 0 && !empty($Data_update_passenger['eTollSkipped']) && strtoupper($Data_update_passenger['eTollSkipped']) == "NO") {
                $fareAmount = $fareAmount + $Data_update_passenger['fTollPrice'];
            }
        }
        else if ($selectedCarTypeID > 0 && $eType == 'UberX') {
            //echo "<pre>";print_r($selectedCarTypeID);die;
            $fareAmount = $iPrice * $ratio;
            if (!empty($minHour_ufx) && $minHour_ufx > 0) {
                $fareAmount = $fareAmount * $minHour_ufx;
            }
        }
        $fareAmount = $generalobj->setTwoDecimalPoint($fareAmount + $userOutStandingAmt); // Added By HJ On 10-06-2019 For Payment Flow 2/3
        if ($user_available_balance_wallet < $fareAmount && $eWalletIgnore == 'No') {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LOW_WALLET_AMOUNT";
            if (!empty($walletDataArr) && count($walletDataArr) > 0) {
                $auth_wallet_amount = strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['TotalAuthorizedAmount']) ? $walletDataArr['TotalAuthorizedAmount'] : 0) * $ratio));
                $returnArr['AUTH_AMOUNT'] = $auth_wallet_amount > 0 ? ($currency_vSymbol . ' ' . $generalobj->setTwoDecimalPoint($auth_wallet_amount)) : "";
                $returnArr['AUTH_AMOUNT_VALUE'] = $auth_wallet_amount > 0 ? $generalobj->setTwoDecimalPoint($auth_wallet_amount) : "";
                $returnArr['ORIGINAL_WALLET_BALANCE'] = $currency_vSymbol . ' ' . $generalobj->setTwoDecimalPoint((isset($walletDataArr['WalletBalance']) ? $generalobj->setTwoDecimalPoint($walletDataArr['WalletBalance']) : 0) * $ratio);
                $returnArr['ORIGINAL_WALLET_BALANCE_VALUE'] = strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['WalletBalance']) ? $generalobj->setTwoDecimalPoint($walletDataArr['WalletBalance']) : 0) * $ratio));
            }
            $returnArr['CURRENT_JOB_EST_CHARGE'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint($fareAmount));
            $returnArr['CURRENT_JOB_EST_CHARGE_VALUE'] = strval($generalobj->setTwoDecimalPoint($fareAmount));
            $returnArr['WALLET_AMOUNT_NEEDED'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint($fareAmount - $user_available_balance_wallet));
            $returnArr['WALLET_AMOUNT_NEEDED_VALUE'] = strval($generalobj->setTwoDecimalPoint($fareAmount - $user_available_balance_wallet));
            if (!empty($walletDataArr) && count($walletDataArr) > 0 && $auth_wallet_amount > 0) {
                $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AUTH_AMT'];
                $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                    $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                }
                if (!empty($returnArr['AUTH_AMOUNT'])) {
                    $content_msg_low_balance = str_replace("###", $returnArr['AUTH_AMOUNT'], $content_msg_low_balance);
                }
                $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
            }
            else {
                $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AMT'];
                $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                    $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                }
                if (!empty($returnArr['CURRENT_JOB_EST_CHARGE'])) {
                    $content_msg_low_balance = str_replace("###", $returnArr['CURRENT_JOB_EST_CHARGE'], $content_msg_low_balance);
                }
                $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
            }
            if ($SYSTEM_PAYMENT_FLOW == 'Method-3') {
                $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "Yes";
            }
            else {
                $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "No";
            }
            //echo "<pre>";print_r($returnArr);die;
            setDataResponse($returnArr);
        }
        $Data_update_passenger['tEstimatedCharge'] = $fareAmount / $ratio;
    }
    //echo "<pre>";print_r($DataArr);die;
    /*     * * Checking User's wallet balance respect to 'Method-2,3' Payment flow ** */
    // Payment Method 2 / Method 3 Flow End
    //echo $eType;die;
    if ($eType == "Multi-Delivery") {
        $data_fare = calculateFareEstimateAllMultiDelivery($total_del_time, $total_del_dist, $selectedCarTypeID, $passengerId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", "", $data_flattrip['eFlatTrip'], $data_flattrip["Flatfare"], "", "", "Yes", $eType, "", $ePaymentBy, $eWalletDebitAllow);
        //echo "<pre>";print_r($data_fare);die;
        if (!empty($Data_update_passenger['fTollPrice']) && $Data_update_passenger['fTollPrice'] > 0 && !empty($Data_update_passenger['eTollSkipped']) && strtoupper($Data_update_passenger['eTollSkipped']) == "NO") {
            $data_fare[0]['TotalGenratedFare'] = $data_fare[0]['TotalGenratedFare'] + $Data_update_passenger['fTollPrice'] + $userOutStandingAmt; // $userOutStandingAmt Added By HJ On 10-06-2019 For Payment Flow 2/3
            
        }
        /*         * * Checking User's wallet balance respect to 'Method-2,3' Payment flow ** */
        if (($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') && (($iHotelId == '' || $iHotelId < 1) && strtoupper($GeneralUserType) != 'HOTEL') && $cashPayment == 'false') {
            if ($user_available_balance_wallet < ($data_fare[0]['TotalGenratedFare'] * $ratio) && $eWalletIgnore == 'No') {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LOW_WALLET_AMOUNT";
                if (!empty($walletDataArr) && count($walletDataArr) > 0) {
                    $auth_wallet_amount = strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['TotalAuthorizedAmount']) ? $walletDataArr['TotalAuthorizedAmount'] : 0) * $ratio));
                    $returnArr['AUTH_AMOUNT'] = $auth_wallet_amount > 0 ? ($currency_vSymbol . ' ' . $auth_wallet_amount) : "";
                    $returnArr['AUTH_AMOUNT_VALUE'] = $auth_wallet_amount > 0 ? $auth_wallet_amount : "";
                    $returnArr['ORIGINAL_WALLET_BALANCE'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $ratio));
                    $returnArr['ORIGINAL_WALLET_BALANCE_VALUE'] = strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $ratio));
                }
                $returnArr['CURRENT_JOB_EST_CHARGE'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint(($data_fare[0]['TotalGenratedFare'] * $ratio)));
                $returnArr['CURRENT_JOB_EST_CHARGE_VALUE'] = strval($generalobj->setTwoDecimalPoint(($data_fare[0]['TotalGenratedFare'] * $ratio)));
                $returnArr['WALLET_AMOUNT_NEEDED'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint(($data_fare[0]['TotalGenratedFare'] * $ratio) - $user_available_balance_wallet));
                $returnArr['WALLET_AMOUNT_NEEDED_VALUE'] = strval($generalobj->setTwoDecimalPoint(($data_fare[0]['TotalGenratedFare'] * $ratio) - $user_available_balance_wallet));
                //echo $data_fare[0]['TotalGenratedFare']."===========".$user_available_balance_wallet."+++++++++";
                //print_R($returnArr); exit;
                if (!empty($walletDataArr) && count($walletDataArr) > 0 && $auth_wallet_amount > 0) {
                    $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AUTH_AMT'];
                    $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                    if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                        $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                    }
                    if (!empty($returnArr['AUTH_AMOUNT'])) {
                        $content_msg_low_balance = str_replace("###", $returnArr['AUTH_AMOUNT'], $content_msg_low_balance);
                    }
                    $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                    $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
                }
                else {
                    $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AMT'];
                    $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                    if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                        $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                    }
                    if (!empty($returnArr['CURRENT_JOB_EST_CHARGE'])) {
                        $content_msg_low_balance = str_replace("###", $returnArr['CURRENT_JOB_EST_CHARGE'], $content_msg_low_balance);
                    }
                    $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                    $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
                }
                if ($SYSTEM_PAYMENT_FLOW == 'Method-3') {
                    $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "Yes";
                }
                else {
                    $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "No";
                }
                setDataResponse($returnArr);
            }
            $Data_update_passenger['tEstimatedCharge'] = $data_fare[0]['TotalGenratedFare'];
        }
        //echo "<pre>";print_r($data_fare);die;
        /*         * * Checking User's wallet balance respect to 'Method-2,3' Payment flow ** */
        $fTripGenerateFare = $data_fare[0]['TotalGenratedFare'];
        $Data_update_passenger['iFare'] = $data_fare[0]['iFare_Ori'];
        $Data_update_passenger['iBaseFare'] = $data_fare[0]['iBaseFare_AMT'];
        $Data_update_passenger['fPricePerMin'] = $data_fare[0]['FareOfMinutes_Ori'];
        $Data_update_passenger['fPricePerKM'] = $data_fare[0]['FareOfDistance_Ori'];
        $Data_update_passenger['fCommision'] = $data_fare[0]['fCommision_AMT'];
        $Data_update_passenger['fSurgePriceDiff'] = $data_fare[0]['fSurgePriceDiff_Ori'];
        $Data_update_passenger['fTax1'] = $data_fare[0]['fTax1_Ori'];
        $Data_update_passenger['fTax2'] = $data_fare[0]['fTax2_Ori'];
        $Data_update_passenger['fTax1Percentage'] = $data_fare[0]['fTax1Percentage'];
        $Data_update_passenger['fTax2Percentage'] = $data_fare[0]['fTax2Percentage'];
        $Data_update_passenger['fOutStandingAmount'] = $data_fare[0]['fOutStandingAmount'];
        $Data_update_passenger['fDiscount'] = $data_fare[0]['fDiscount'];
        $Data_update_passenger['vDiscount'] = $data_fare[0]['vDiscount'];
        $Data_update_passenger['fMinFareDiff'] = $data_fare[0]['fMinFareDiff_Ori'];
        $Data_update_passenger['fTripGenerateFare'] = $fTripGenerateFare;
        $Data_update_passenger['fWalletDebit'] = $data_fare[0]['fWalletDebit'];
        //added by SP for rounding off on 7-11-2019 for multi delivery
        if ($tripPaymentMode == 'Cash') {
            $Data_update_passenger['fRoundingAmount'] = $data_fare[0]['fRoundingAmount'];
            $Data_update_passenger['eRoundingType'] = $data_fare[0]['eRoundingType'];
        }
    }
    if (!empty($Data_update_passenger['tEstimatedCharge']) && $Data_update_passenger['tUserWalletBalance'] > $Data_update_passenger['tEstimatedCharge'] && ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
        $Data_update_passenger['tUserWalletBalance'] = $Data_update_passenger['tEstimatedCharge'];
    }
    $Data_update_passenger['tTotalDistance'] = !empty($Data_update_passenger['fDistance']) ? $Data_update_passenger['fDistance'] : $vDistance;
    $Data_update_passenger['tTotalDuration'] = !empty($Data_update_passenger['fDuration']) ? $Data_update_passenger['fDuration'] : $vDuration;
    $delivery_arr = isset($_REQUEST["delivery_arr"]) ? $_REQUEST["delivery_arr"] : '';
    $details_arr = json_decode($delivery_arr, true);
    //echo "<pre>";print_r($_REQUEST);
    //echo "<br><br>";
    //echo "<pre>";print_r($details_arr);exit;
    $insert_id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_passenger, 'insert');
    // $insert_id = mysql_insert_id();
    $final_message['iCabRequestId'] = $insert_id;
    //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 Start
    if ($userOutStandingAmt > 0 && $outstandingIds != "") {
        $obj->sql_query("UPDATE trip_outstanding_amount set eAuthoriseIdName='iCabRequestId',iAuthoriseId = '" . $insert_id . "' WHERE iTripOutstandId IN ($outstandingIds)");
    }
    //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 End
    /* ------------------------multi delivery details------------------- */
    $delivery_arr = isset($_REQUEST["delivery_arr"]) ? $_REQUEST["delivery_arr"] : '';
    // echo "dd".$delivery_arr;//exit;
    if ($delivery_arr != "" && $eType == "Multi-Delivery") {
        $details_arr = json_decode($delivery_arr, true);
        // echo "<pre>";print_r($details_arr);exit;
        $j = 0;
        $last_key = end(array_keys($details_arr));
        foreach ($details_arr as $key123 => $values1) {
            $i = 0;
            $insert_did = array();
            foreach ($values1 as $key => $value) {
                // echo "==>".$key."<br>";
                if ($key == "vReceiverAddress" || $key == "vReceiverLatitude" || $key == "vReceiverLongitude" || $key == "ePaymentByReceiver") {
                    $Data_trip_locations[$key] = $value;
                    if ($key == "vReceiverLatitude") {
                        $Old_end_lat = $Data_trip_locations['tEndLat'];
                        $Data_trip_locations['tEndLat'] = $value;
                    }
                    else if ($key == "vReceiverLongitude") {
                        $Old_end_long = $Data_trip_locations['tEndLong'];
                        $Data_trip_locations['tEndLong'] = $value;
                    }
                    else if ($key == "vReceiverAddress") {
                        $Old_end_address = $Data_trip_locations['tDaddress'];
                        $Data_trip_locations['tDaddress'] = $value;
                    }
                    else if ($key == "ePaymentByReceiver") {
                        $Data_trip_locations['ePaymentByReceiver'] = $value;
                    }
                    if (($ePaymentBy == "Sender" || $ePaymentBy == "Receiver") && $key123 != 0) {
                        $Data_trip_locations['tStartLat'] = $Old_end_lat;
                        $Data_trip_locations['tStartLong'] = $Old_end_long;
                        $Data_trip_locations['tSaddress'] = $Old_end_address;
                    }
                    else {
                        $Data_trip_locations['tStartLat'] = $PickUpLatitude;
                        $Data_trip_locations['tStartLong'] = $PickUpLongitude;
                        $Data_trip_locations['tSaddress'] = $PickUpAddress;
                    }
                }
                else {
                    $Data_delivery['iDeliveryFieldId'] = $key;
                    $Data_delivery['iCabRequestId'] = $insert_id;
                    $Data_delivery['vValue'] = $value;
                    $insert_did[] = $obj->MySQLQueryPerform("trip_delivery_fields", $Data_delivery, 'insert');
                }
            }
            $Data_trip_locations['iCabBookingId'] = $insert_id;
            $Data_trip_locations['ePaymentBy'] = $ePaymentBy;
            $insert_dfid = $obj->MySQLQueryPerform("trips_delivery_locations", $Data_trip_locations, 'insert');
            $delivery_ids = implode("','", $insert_did);
            $where = " iTripDeliveryFieldId in ('" . $delivery_ids . "')";
            $data_update['iTripDeliveryLocationId'] = $insert_dfid;
            $obj->MySQLQueryPerform("trip_delivery_fields", $data_update, 'update', $where);
            if ($last_key == $key123) {
                $where = " iCabRequestId='" . $insert_id . "'";
                $data_update_cab['vDestLatitude'] = $Data_trip_locations['tEndLat'];
                $data_update_cab['vDestLongitude'] = $Data_trip_locations['tEndLong'];
                $data_update_cab['tDestAddress'] = $Data_trip_locations['tDaddress'];
                $obj->MySQLQueryPerform("cab_request_now", $data_update_cab, 'update', $where);
            }
        }
    }
    /* ------------------------multi delivery details end------------------- */
    if (checkStopOverPointModule()) {
        setStopOverPointLocation($insert_id);
    }
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }
    $alertSendAllowed = true;
    $labelsStoreArr = array();
    $labelsStoreArr[$userLanguageCode]['LBL_TRIP_USER_WAITING'] = $userLanguageLabelsArr['LBL_TRIP_USER_WAITING'];
    $labelsStoreArr[$userLanguageCode]['LBL_USER_WAITING'] = $userLanguageLabelsArr['LBL_USER_WAITING'];
    $labelsStoreArr[$userLanguageCode]['LBL_DELIVERY_SENDER_WAITING'] = $userLanguageLabelsArr['LBL_DELIVERY_SENDER_WAITING'];
    ### GCM ####
    if ($alertSendAllowed == true) {
        $deviceTokens_arr_ios = $msg_encode_ios = $registation_ids_new = $alertMsg_arr_ios = array();
        foreach ($result_driverData as $item) {
            //$alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING'," and vCode='".$item['vLang']."'",'true');
            if ($eType == "Ride") {
                if (!empty($labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'])) {
                    $alertMsg_db = $labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'];
                }
                else {
                    $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                    $labelsStoreArr[$item['vLang']]['LBL_TRIP_USER_WAITING'] = $alertMsg_db;
                }
            }
            elseif ($eType == "UberX") {
                if (!empty($labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'])) {
                    $alertMsg_db = $labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'];
                }
                else {
                    $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_USER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                    $labelsStoreArr[$item['vLang']]['LBL_USER_WAITING'] = $alertMsg_db;
                }
            }
            else {
                if (!empty($labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'])) {
                    $alertMsg_db = $labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'];
                }
                else {
                    $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_DELIVERY_SENDER_WAITING', " and vCode='" . $item['vLang'] . "'", 'true');
                    $labelsStoreArr[$item['vLang']]['LBL_DELIVERY_SENDER_WAITING'] = $alertMsg_db;
                }
            }
            $tSessionId = $item['tSessionId'];
            // packagename changes
            if ($eType == "Deliver" || $eType == "Delivery") {
                $sql_request = "SELECT vName_" . $item['vLang'] . " as vName FROM package_type WHERE iPackageTypeId='" . $iPackageTypeId . "'";
                $pkgdata = $obj->MySQLSelect($sql_request);
                $final_message['PACKAGE_TYPE'] = ($eType == "Deliver" || $eType == "Delivery") ? $pkgdata[0]['vName'] : '';
            }
            else {
                $final_message['PACKAGE_TYPE'] = "";
            }
            $final_message['tSessionId'] = $tSessionId;
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            }
            else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
                array_push($alertMsg_arr_ios, $alertMsg_db);
                array_push($msg_encode_ios, $msg_encode);
            }
            // Add User Request
            $data_userRequest = $data_driverRequest = array();
            $data_userRequest['iUserId'] = $passengerId;
            $data_userRequest['iDriverId'] = $item['iDriverId'];
            $data_userRequest['tMessage'] = $msg_encode;
            $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
            $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
            $requestId = addToUserRequest2($data_userRequest);
            // Add Driver Request
            $data_driverRequest['iDriverId'] = $item['iDriverId'];
            $data_driverRequest['iRequestId'] = $requestId;
            $data_driverRequest['iUserId'] = $passengerId;
            $data_driverRequest['iTripId'] = 0;
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
            $final_message['tSessionId'] = "";
            $final_message['vTitle'] = $alertMsg;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            // $Rmessage = array("message" => $message);
            $Rmessage = array(
                "message" => $msg_encode
            );
            send_notification($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode_ios, $alertMsg_arr_ios, 0);
        }
    }
    ### GCM ####
    $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
    $driverIds_arr = explode(",", $filter_driver_ids);
    $message = stripslashes(preg_replace("/[\n\r]/", "", $message));
    $IOS_data_pubsub = array();
    $IOS_data_Count = 0;
    $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
    $destLoc = $DestLatitude . ',' . $DestLongitude;
    //echo "<pre>";print_r($DataArr);die;
    for ($i = 0;$i < count($driverIds_arr);$i++) {
        $data_found = false;
        foreach ($result_driverData as $item_driver) {
            if ($item_driver['iDriverId'] == $driverIds_arr[$i]) {
                $data_found = true;
                $iAppVersion = $item_driver['iAppVersion'];
                $eDeviceType = $item_driver['eDeviceType'];
                $vDeviceToken = $item_driver['iGcmRegId'];
                $tSessionId = $item_driver['tSessionId'];
                $vLang = $item_driver['vLang'];
                break;
            }
        }
        if ($data_found == false) {
            $sqld = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,vLang FROM register_driver WHERE iDriverId = '" . $driverIds_arr[$i] . "'";
            $driverTripData = $obj->MySQLSelect($sqld);
            $iAppVersion = $driverTripData[0]['iAppVersion'];
            $eDeviceType = $driverTripData[0]['eDeviceType'];
            $vDeviceToken = $driverTripData[0]['iGcmRegId'];
            $tSessionId = $driverTripData[0]['tSessionId'];
            $vLang = $driverTripData[0]['vLang'];
        }
        /* For PubNub Setting Finished */
        $final_message['tSessionId'] = $tSessionId;
        if ($eType == "Ride") {
            if (!empty($labelsStoreArr[$vLang]['LBL_TRIP_USER_WAITING'])) {
                $alertMsg_db = $labelsStoreArr[$vLang]['LBL_TRIP_USER_WAITING'];
            }
            else {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING', " and vCode='" . $vLang . "'", 'true');
                $labelsStoreArr[$vLang]['LBL_TRIP_USER_WAITING'] = $alertMsg_db;
            }
        }
        else if ($eType == "UberX") {
            if (!empty($labelsStoreArr[$vLang]['LBL_USER_WAITING'])) {
                $alertMsg_db = $labelsStoreArr[$vLang]['LBL_USER_WAITING'];
            }
            else {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_USER_WAITING', " and vCode='" . $vLang . "'", 'true');
                $labelsStoreArr[$vLang]['LBL_USER_WAITING'] = $alertMsg_db;
            }
        }
        else {
            if (!empty($labelsStoreArr[$vLang]['LBL_DELIVERY_SENDER_WAITING'])) {
                $alertMsg_db = $labelsStoreArr[$vLang]['LBL_DELIVERY_SENDER_WAITING'];
            }
            else {
                $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_DELIVERY_SENDER_WAITING', " and vCode='" . $vLang . "'", 'true');
                $labelsStoreArr[$vLang]['LBL_DELIVERY_SENDER_WAITING'] = $alertMsg_db;
            }
        }
        if ($eType == "Deliver" || $eType == "Delivery") {
            // packagename changes
            $sql_request = "SELECT vName_" . $vLang . " as vName FROM package_type WHERE iPackageTypeId='" . $iPackageTypeId . "'";
            $pkgdata = $obj->MySQLSelect($sql_request);
            $final_message['PACKAGE_TYPE'] = ($eType == "Deliver" || $eType == "Delivery") ? $pkgdata[0]['vName'] : '';
        }
        else {
            $final_message['PACKAGE_TYPE'] = "";
        }
        $final_message['vTitle'] = $alertMsg_db;
        $msg_encode_pub = json_encode($final_message, JSON_UNESCAPED_UNICODE);
        $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$i];
        // $info = $pubnub->publish($channelName, $message);
        // echo "before".date('Y-m-d H:i:s');
        if ($eDeviceType == "Android") {
            publishEventMessage($channelName, $msg_encode_pub);
            //  echo "after".date('Y-m-d H:i:s');
            
        }
        else {
            $IOS_data_pubsub[$IOS_data_Count]['ChannelName'] = $channelName;
            $IOS_data_pubsub[$IOS_data_Count]['PublishMsg'] = $msg_encode_pub;
            $IOS_data_Count++;
        }
    }
    if (count($IOS_data_pubsub) > 0) {
        //sleep(5);
        for ($i = 0;$i < count($IOS_data_pubsub);$i++) {
            // echo "after".date('Y-m-d H:i:s');
            publishEventMessage($IOS_data_pubsub[$i]['ChannelName'], $IOS_data_pubsub[$i]['PublishMsg']);
            // echo "after".date('Y-m-d H:i:s');
            
        }
    }
    $returnArr['Action'] = "1";
    // echo "Before::".@date('Y-m-d H:i:s')."<BR/>";exit;
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "cancelTrip") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    $eConfirmByUser = isset($_REQUEST['eConfirmByUser']) ? $_REQUEST['eConfirmByUser'] : 'No';
    $iCancelReasonId = isset($_REQUEST["iCancelReasonId"]) ? $_REQUEST["iCancelReasonId"] : '';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    if ($eConfirmByUser == "" || $eConfirmByUser == NULL) {
        $eConfirmByUser = "No";
    }
    //$eWalletDebitAllow = get_value('trips', 'eWalletDebitAllow', 'iTripId', $iTripId, '', 'true'); // Commented By HJ On 05-06-2019 For Optimized Code
    //$TripType = get_value('trips', 'eType', 'iTripId', $iTripId, '', 'true'); // Commented By HJ On 05-06-2019 For Optimized Code
    $tripCancelData = get_value('trips AS tr LEFT JOIN vehicle_type AS vt ON vt.iVehicleTypeId=tr.iVehicleTypeId', 'tr.vCouponCode,tr.vTripPaymentMode,tr.iUserId,tr.iFare,tr.vRideNo,tr.tStartDate,tr.tTripRequestDate,tr.tDriverArrivedDate,tr.eType,tr.ePaymentBy,tr.iOrganizationId, tr.tUserWalletBalance,vt.fCancellationFare,vt.iCancellationTimeLimit,vt.iWaitingFeeTimeLimit,tr.ePoolRide,tr.tVehicleTypeFareData,tr.eWalletDebitAllow,tr.eType', 'iTripId', $iTripId);
    $old_iActive = $tripCancelData[0]['iActive'];
    $eWalletDebitAllow = "No";
    $TripType = "Ride";
    if (count($tripCancelData) > 0) {
        $eWalletDebitAllow = $tripCancelData[0]['eWalletDebitAllow'];
        $TripType = $tripCancelData[0]['eType'];
    }
    //print_r($eWalletDebitAllow);die;
    if ($TripType == 'Multi-Delivery') {
        $sql1 = "SELECT count(`iTripDeliveryLocationId`) AS Total FROM trips_delivery_locations WHERE iTripId='" . $iTripId . "' AND (iActive = 'Finished' OR iActive = 'On Going Trip')";
        $totalRunning = $obj->MySQLSelect($sql1);
        if ($totalRunning[0]['Total'] > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NOT_CANCELLED_TRIP_TXT";
            setDataResponse($returnArr);
        }
    }
    $iOrganizationId = $tripCancelData[0]['iOrganizationId'];
    $ePaymentBy = $tripCancelData[0]['ePaymentBy'];
    $ePoolRide = $tripCancelData[0]['ePoolRide'];
    $tUserWalletBalance = $tripCancelData[0]['tUserWalletBalance'];
    if ($iUserId == "" || $iUserId == NULL || $iUserId == 0) {
        $iUserId = $tripCancelData[0]['iUserId'];
    }
    $tStartDate = $tripCancelData[0]['tStartDate'];
    $tTripRequestDate = $tripCancelData[0]['tTripRequestDate'];
    $tDriverArrivedDate = $tripCancelData[0]['tDriverArrivedDate'];
    if ($userType != "Driver") {
        $currentDate = @date("Y-m-d H:i:s");
    }
    else {
        $currentDate = @date("Y-m-d H:i:s");
        $tTripRequestDate = $tDriverArrivedDate;
        if ($tTripRequestDate == "0000-00-00 00:00:00") {
            $tTripRequestDate = @date("Y-m-d H:i:s");
        }
    }
    $fCancellationFare = 0;
    if ($ePaymentBy == "Organization") {
        $fCancellationFare = $tripCancelData[0]['fCancellationFare'];
    }
    if ($tDriverArrivedDate == "0000-00-00 00:00:00") {
        $fWaitingFees = 0;
    }
    else {
        $fWaitingFees = getTripWaitingFee($iTripId);
    }
    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo, tr.eType,tr.fWalletDebit FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
    $result = $obj->MySQLSelect($sql);
    $eType = $result[0]['eType'];
    $fWaitingFees = 0; // As per discussion now waiting fee is not charge when cancel trip
    $eCancelChargeFailed = "No";
    $totalMinute = @round(abs(strtotime($currentDate) - strtotime($tTripRequestDate)) / 60, 2);
    //Added By HJ On 05-02-2019 For Calculate Cancellation Charge When Select Multiple Service Type Start
    $chkTimeLimit = $totalMinute >= $tripCancelData[0]['iCancellationTimeLimit'];
    $fCancellationFareAmt = $tripCancelData[0]['fCancellationFare'];
    if ($SERVICE_PROVIDER_FLOW == "Provider" && $eType == "UberX") {
        $tVehicleTypeFareData = (array)json_decode($tripCancelData[0]['tVehicleTypeFareData']);
        $fCancellationFareAmt = $chkTimeLimit = 0;
        if ($totalMinute >= $tVehicleTypeFareData['ParentCancellationTimeLimit']) {
            $timeCheckArr[] = 1;
            $fCancellationFareAmt += $tVehicleTypeFareData['ParentCancellationFare'];
        }
        if (in_array(1, $timeCheckArr)) {
            $chkTimeLimit = 1;
        }
    }
    $user_wallet_debit_amount = 0;
    //$chkTimeLimit = 0;
    //Added By HJ On 05-02-2019 For Calculate Cancellation Charge When Select Multiple Service Type End
    if ($chkTimeLimit == 1) {
        ## Display Trip cancellation charge message to user ##
        if ($eConfirmByUser == "No" && $userType != "Driver" && $fCancellationFareAmt > 0) {
            $Cancel_Amount_ARR = getPriceUserCurrency($iUserId, "Passenger", $fCancellationFareAmt);
            $Cancel_Amount = $Cancel_Amount_ARR['fPricewithsymbol'];
            $TripType = $tripCancelData[0]['eType'];
            $vLangCode = get_value("register_user", "vLang", "iUserId", $iUserId, '', 'true');
            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            $lngLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
            if ($TripType == "Ride") {
                $cancelMsg_db = $lngLabelsArr['LBL_CANCELTRIP_RIDE_CHARGE_PREFIX_TXT'] . " " . $Cancel_Amount . " " . $lngLabelsArr['LBL_CANCELTRIP_RIDE_CHARGE_POSTFIX_TXT'];
            }
            elseif ($TripType == "UberX") {
                $cancelMsg_db = $lngLabelsArr['LBL_CANCELTRIP_SERVICE_CHARGE_PREFIX_TXT'] . " " . $Cancel_Amount . " " . $lngLabelsArr['LBL_CANCELTRIP_SERVICE_CHARGE_POSTFIX_TXT'];
            }
            else {
                $cancelMsg_db = $lngLabelsArr['LBL_CANCELTRIP_DELIVER_CHARGE_PREFIX_TXT'] . " " . $Cancel_Amount . " " . $lngLabelsArr['LBL_CANCELTRIP_DELIVER_CHARGE_POSTFIX_TXT'];
            }
            $returnArr['Action'] = "0";
            $returnArr['message'] = $cancelMsg_db;
            $returnArr['isCancelChargePopUpShow'] = "Yes";
            $returnArr['CancelChargeAmount'] = $Cancel_Amount;
            setDataResponse($returnArr);
        }
        if ($ePaymentBy == "Passenger") {
            ## Display Trip cancellation charge message to user ##
            $fCancellationFare = $fCancellationFareAmt;
            $fCancellationFare = $fCancellationFare + $fWaitingFees;
            $vTripPaymentMode = $tripCancelData[0]['vTripPaymentMode'];
            /* Check debit wallet For Cancel Charge */
            if ($fCancellationFare > 0 && $eWalletDebitAllow == "Yes") {
                $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
                if ($user_available_balance > 0) {
                    $totalCurrentActiveTripsArr = getCurrentActiveTripsTotal($iUserId);
                    $totalCurrentActiveTripsIdsArr = $totalCurrentActiveTripsArr['ActiveTripIds'];
                    $totalCurrentActiveOrderIdsArr = $totalCurrentActiveTripsArr['ActiveOrderIds'];
                    $totalCurrentActiveTripsCount = $totalCurrentActiveTripsArr['TotalCount'];
                    /*                     * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */
                    // Charge an amount that is autorized when trip was initially requested in case when multiple jobs are going on.
                    if (($totalCurrentActiveTripsCount > 1 || in_array($iTripId, $totalCurrentActiveTripsIdsArr) == false) && ($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
                        $user_available_balance = $tUserWalletBalance;
                    }
                    /*                     * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */
                }
                if ($fCancellationFare > $user_available_balance) {
                    $fCancellationFare = $fCancellationFare - $user_available_balance;
                    $user_wallet_debit_amount = $user_available_balance;
                }
                else {
                    $user_wallet_debit_amount = $fCancellationFare;
                    $fCancellationFare = 0;
                    $updateQuery = "UPDATE trips set fWalletDebit = '" . $user_wallet_debit_amount . "' WHERE iTripId = " . $iTripId;
                    $obj->sql_query($updateQuery);
                    $iTripOutstandId = UpdateTripOutstandingAmount($iTripId, "Yes", "Yes");
                }
            }
            /* Check debit wallet For Cancel Charge */
            if ($vTripPaymentMode == "Card" && $fCancellationFare > 0 && $SYSTEM_PAYMENT_FLOW != "Method-2" && $SYSTEM_PAYMENT_FLOW != "Method-3") {
                //$vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true'); // Commented By HJ On 05-06-2019 For Optimized Code
                //$vBrainTreeToken = get_value('register_user', 'vBrainTreeToken', 'iUserId', $iUserId, '', 'true'); // Commented By HJ On 05-06-2019 For Optimized Code
                $getUserData = $obj->MySQLSelect("SELECT vStripeCusId,vBrainTreeToken FROM register_user WHERE iUserId='" . $iUserId . "'");
                $vStripeCusId = $vBrainTreeToken = "";
                if (count($getUserData) > 0) {
                    $vStripeCusId = $getUserData[0]['vStripeCusId'];
                    $vBrainTreeToken = $getUserData[0]['vBrainTreeToken'];
                }
                $currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
                $price_new = $fCancellationFare * 100;
                $description = "Payment received for cancelled trip number:" . $tripCancelData[0]['vRideNo'];
                $Charge_Array = array(
                    "iFare" => $fCancellationFare,
                    "price_new" => $price_new,
                    "currency" => $currency,
                    "vStripeCusId" => $vStripeCusId,
                    "description" => $description,
                    "iTripId" => $iTripId,
                    "eCancelChargeFailed" => $eCancelChargeFailed,
                    "vBrainTreeToken" => $vBrainTreeToken,
                    "vRideNo" => $tripCancelData[0]['vRideNo'],
                    "iMemberId" => $iUserId,
                    "UserType" => "Passenger"
                );
                $ChargeidArr = ChargeCustomer($Charge_Array, "cancelTrip"); // function for charge customer
                $ChargeidArrId = $ChargeidArr['id'];
                $eCancelChargeFailed = $ChargeidArr['eCancelChargeFailed'];
                $status = $ChargeidArr['status'];
                if ($status == "success") {
                    $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
                    $data_payments['iTripId'] = $iTripId;
                    $data_payments['eEvent'] = "Trip";
                    $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
                }
                else {
                    $eCancelChargeFailed = 'Yes';
                }
            }
            if (($vTripPaymentMode == "Cash" && $fCancellationFare > 0) || (($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == "Method-3") && $fCancellationFare > 0)) {
                $eCancelChargeFailed = 'Yes';
            }
        }
    }
    $active_status = "Canceled";
    $message = "TripCancelledByDriver";
    if ($userType != "Driver") {
        $message = "TripCancelled";
    }
    $couponCode = $tripCancelData[0]['vCouponCode'];
    if ($couponCode != '') {
        $noOfCouponUsed = get_value('coupon', 'iUsed', 'vCouponCode', $couponCode, '', 'true');
        $where = " vCouponCode = '" . $couponCode . "'";
        $data_coupon['iUsed'] = $noOfCouponUsed - 1;
        $obj->MySQLQueryPerform("coupon", $data_coupon, 'update', $where);
    }
    $statusUpdate_user = "Not Assigned";
    $trip_status = "Cancelled";
    $fWalletDebit = $result[0]['fWalletDebit'];
    /* For PubNub Setting */
    $tableName = $userType != "Driver" ? "register_driver" : "register_user";
    $iMemberId_VALUE = $userType != "Driver" ? $iDriverId : $iUserId;
    $iMemberId_KEY = $userType != "Driver" ? "iDriverId" : "iUserId";
    $AppData = get_value($tableName, 'iAppVersion,eDeviceType,eLogout,vLang', $iMemberId_KEY, $iMemberId_VALUE);
    $iAppVersion = $AppData[0]['iAppVersion'];
    $eLogout = $AppData[0]['eLogout'];
    $eDeviceType = $AppData[0]['eDeviceType'];
    $alertMsg = "Trip canceled";
    //$vLangCode=get_value($tableName, 'vLang', $iMemberId_KEY,$iMemberId_VALUE,'','true');
    $vLangCode = $AppData[0]['vLang'];
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    if ($iCancelReasonId != "") {
        $driverReason = get_value('cancel_reason', "vTitle_" . $vLangCode, 'iCancelReasonId', $iCancelReasonId, '', 'true');
    }
    if ($userType == "Driver") {
        if ($eType == "Ride") {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_TRIP_CANCEL_DRIVER'] . ' ' . $driverReason . ' ' . $languageLabelsArr['LBL_CANCEL_TRIP_BY_DRIVER_MSG_SUFFIX'];
        }
        elseif ($eType == "Deliver") {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_DELIVERY_CANCEL_DRIVER'] . ' ' . $driverReason . ' ' . $languageLabelsArr['LBL_CANCEL_DELIVERY_BY_DRIVER_MSG_SUFFIX'];
        }
        else {
            $jobnotitle = "#" . $result[0]['vRideNo'];
            $jobnomsg = str_replace('####', $jobnotitle, $languageLabelsArr['LBL_PREFIX_JOB_CANCEL_PROVIDER']);
            $usercanceltriplabel = $jobnomsg . ' ' . $driverReason . ' ' . $languageLabelsArr['LBL_CANCEL_UBERX_BOOKING_BY_DRIVER_MSG_SUFFIX'];
        }
    }
    else {
        if ($eType == "Ride") {
            $usercanceltriplabel = $languageLabelsArr['LBL_PASSENGER_CANCEL_TRIP_TXT'];
            //Added By Hasmukh On 07-12-2018 For Get Pool Trip's Passenger Name Start
            if ($POOL_ENABLE == "Yes" && $ePoolRide == "Yes") {
                $riderName = "";
                $getPassenger = $obj->MySQLSelect("SELECT vName,vLastName FROM register_user WHERE iUserId='" . $iUserId . "'");
                if (isset($getPassenger[0]['vName']) && $getPassenger[0]['vName'] != "") {
                    $riderName = $getPassenger[0]['vName'] . " " . $getPassenger[0]['vLastName'];
                }
                $usercanceltriplabel = $riderName . " " . $languageLabelsArr['LBL_PASSENGER_CANCEL_TRIP_TXT'];
            }
            //Added By Hasmukh On 07-12-2018 For Get Pool Trip's Passenger Name End
            
        }
        elseif ($eType == "Deliver") {
            $usercanceltriplabel = $languageLabelsArr['LBL_SENDER_CANCEL_DELIVERY_TXT'];
        }
        else {
            $usercanceltriplabel = $languageLabelsArr['LBL_USER_CANCEL_JOB_TXT'];
        }
    }
    $alertMsg = $usercanceltriplabel;
    $message_arr = array();
    $message_arr['Message'] = $message;
    if ($userType == "Driver") {
        $message_arr['Reason'] = $driverReason;
        $message_arr['isTripStarted'] = "false";
    }
    $message_arr['iTripId'] = $iTripId;
    $message_arr['iDriverId'] = $iDriverId;
    $message_arr['iUserId'] = $iUserId;
    $message_arr['driverName'] = $result[0]['driverName'];
    $message_arr['vRideNo'] = $result[0]['vRideNo'];
    $message_arr['eType'] = $result[0]['eType'];
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['eSystem'] = "";
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
    #####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $iDriverId;
    $DataTripMessages['iTripId'] = $iTripId;
    $DataTripMessages['iUserId'] = $iUserId;
    $DataTripMessages['eFromUserType'] = "Driver";
    $DataTripMessages['eToUserType'] = "Passenger";
    if ($userType != "Driver") {
        $DataTripMessages['eFromUserType'] = "Passenger";
        $DataTripMessages['eToUserType'] = "Driver";
    }
    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
    ################################################################
    $where = " iTripId = '$iTripId'";
    $Data_update_trips['iActive'] = $active_status;
    $Data_update_trips['tEndDate'] = @date("Y-m-d H:i:s");
    $Data_update_trips['fWaitingFees'] = $fWaitingFees;
    $Data_update_trips['fWalletDebit'] = $user_wallet_debit_amount;
    if ($tStartDate == "0000-00-00 00:00:00") {
        $Data_update_trips['tStartDate'] = @date("Y-m-d H:i:s");
    }
    if ($tDriverArrivedDate == "0000-00-00 00:00:00") {
        $Data_update_trips['tDriverArrivedDate'] = @date("Y-m-d H:i:s");
    }
    //if($vTripPaymentMode == "Card" && $fCancellationFare > 0){
    if ($fCancellationFare > 0) {
        $Data_update_trips['eCancelChargeFailed'] = $eCancelChargeFailed;
        $Data_update_trips['fCancellationFare'] = $fCancellationFare;
    }
    $Data_update_trips['eCancelledBy'] = $userType;
    //if ($userType == "Driver") { // Commented By HJ On 26-03-2019 As Per Discuss With BM QA Bug - 6430
    $Data_update_trips['vCancelReason'] = ($iCancelReasonId > 0) ? "" : $driverReason;
    $Data_update_trips['vCancelComment'] = $driverComment;
    $Data_update_trips['eCancelled'] = "Yes";
    //} // Commented By HJ On 26-03-2019 As Per Discuss With BM QA Bug - 6430
    if ($eType == "Multi-Delivery") {
        $Data_update_trips['fTripGenerateFare'] = $Data_update_trips['iFare'] = $Data_update_trips['iBaseFare'] = $Data_update_trips['fCommision'] = 0;
        if ($fWalletDebit > 0) {
            $dDate = Date('Y-m-d H:i:s');
        }
        //$Data_update_trips['fWalletDebit'] = 0;
        $Data_update_trips['fDiscount'] = $Data_update_trips['fSurgePriceDiff'] = 0;
        $Data_update_trips['vCouponCode'] = "";
        $endtime = @date("Y-m-d H:i:s");
        $updateQuery = "UPDATE trips_delivery_locations set iActive='Canceled',tEndTime='" . $endtime . "'  WHERE iTripId = '" . $iTripId . "'";
        $obj->sql_query($updateQuery);
    }
    $Data_update_trips['iCancelReasonId'] = $iCancelReasonId;
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);
    //update insurance log
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $details_arr['iTripId'] = $iTripId;
        $details_arr['LatLngArr']['vLatitude'] = $vLatitude;
        $details_arr['LatLngArr']['vLongitude'] = $vLongitude;
        // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
        $allow_blank_latlong = "No";
        if ($userType != "Driver") {
            $allow_blank_latlong = "Yes";
        }
        update_driver_insurance_status($iDriverId, "Trip", $details_arr, "cancelTrip", "", $allow_blank_latlong);
    }
    //update insurance log
    ## Update Passenger OutStanding Amount ##
    if ($eCancelChargeFailed == "Yes" && $fCancellationFare > 0 && $ePaymentBy == "Passenger" && $iOrganizationId == 0) {
        $iTripOutstandId = UpdateTripOutstandingAmount($iTripId, "No", "No");
    }
    if ($eCancelChargeFailed == "No" && $vTripPaymentMode == "Card" && $fCancellationFare > 0 && $ePaymentBy == "Passenger" && $iOrganizationId == 0) {
        $iTripOutstandId = UpdateTripOutstandingAmount($iTripId, "Yes", "No");
        /* Added By PM On 25-01-2020 For wallet credit to driver Start */
        if (checkAutoCreditDriverModule()) {
            $Data['iUserId'] = $iUserId;
            $Data['iTripId'] = $iTripId;
            AutoCreditWalletDriver($Data, "cancelTrip", 0);
        }
        /* Added By PM On 25-01-2020 For wallet credit to driver End */
    }
    ## Update Passenger OutStanding Amount ##
    ## Update Organization OutStanding Amount ##
    if ($totalMinute >= $tripCancelData[0]['iCancellationTimeLimit'] && $fCancellationFare > 0 && $iOrganizationId > 0 && strtoupper(PACKAGE_TYPE) == "SHARK") {
        $iTripOutstandId = UpdateOrganizationTripOutstandingAmount($iTripId, "No", "No");
    }
    ## Update Organization OutStanding Amount ##
    $where = " iUserId = '$iUserId'";
    $Data_update_passenger['vCallFromDriver'] = $statusUpdate_user;
    if ($APP_TYPE == "Ride-Delivery-UberX" && $eType != "UberX") {
        $Data_update_passenger['vTripStatus'] = $trip_status;
    }
    else if ($eType != "UberX") {
        $Data_update_passenger['vTripStatus'] = $trip_status;
    }
    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
    $where = " iDriverId='$iDriverId'";
    // $Data_update_driver['iTripId']=$statusUpdate_user;
    $Data_update_driver['vTripStatus'] = $trip_status;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    $fOutStandingAmount = get_value('trips', 'fOutStandingAmount', 'iTripId', $iTripId, '', 'true');
    //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 Start
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $obj->sql_query("UPDATE trip_outstanding_amount set eAuthoriseIdName='No',iAuthoriseId = '0' WHERE iAuthoriseId='" . $iTripId . "' AND eAuthoriseIdName='iTripId'");
    }
    //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 End
    if ($fOutStandingAmount > 0) {
        $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'No',vTripAdjusmentId = '' WHERE iUserId = '" . $iUserId . "' AND vTripAdjusmentId IN($iTripId)";
        $obj->sql_query($updateQury);
        $updateQuery1 = "UPDATE register_user set fTripsOutStandingAmount = fTripsOutStandingAmount+'" . $fOutStandingAmount . "' WHERE iUserId = " . $iUserId;
        $obj->sql_query($updateQuery1);
        $updateQuery2 = "UPDATE trips set fOutStandingAmount = 0 WHERE iTripId = " . $iTripId;
        $obj->sql_query($updateQuery2);
    }
    if ($userType != "Driver") {
        $sql = "SELECT iGcmRegId,eDeviceType,tLocationUpdateDate FROM register_driver WHERE iDriverId IN (" . $iDriverId . ")";
    }
    else {
        $sql = "SELECT iGcmRegId,eDeviceType,tLocationUpdateDate FROM register_user WHERE iUserId IN (" . $iUserId . ")";
    }
    $result = $obj->MySQLSelect($sql);
    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();
    foreach ($result as $item) {
        if ($item['eDeviceType'] == "Android") {
            array_push($registation_ids_new, $item['iGcmRegId']);
        }
        else {
            array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
        }
    }
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $alertSendAllowed = true;
    if ($eLogout == "Yes") {
        $alertSendAllowed = false;
    }
    if ($alertSendAllowed == true) {
        if (count($registation_ids_new) > 0) {
            $Rmessage = array(
                "message" => $message
            );
            $result = send_notification($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            if ($userType == "Driver") {
                sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
            }
            else {
                sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0);
            }
        }
    }
    ### Code For Pubnub ###
    if ($userType != "Driver") {
        $channelName = "DRIVER_" . $iDriverId;
        $tSessionId = get_value("register_driver", 'tSessionId', "iDriverId", $iDriverId, '', 'true');
    }
    else {
        $channelName = "PASSENGER_" . $iUserId;
        $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $iUserId, '', 'true');
    }
    $message_arr['tSessionId'] = $tSessionId;
    $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
    if (count($deviceTokens_arr_ios) > 0) {
        sleep(5);
    }
    publishEventMessage($channelName, $message_pub);
    ### Code For Pubnub ###
    // Code for Check last logout date is update in driver_log_report
    $driverId_log = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
    $query = "SELECT * FROM driver_log_report WHERE iDriverId = '" . $driverId_log . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
    $db_driver = $obj->MySQLSelect($query);
    if (count($db_driver) > 0) {
        $driver_lastonline = @date("Y-m-d H:i:s");
        $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
        $obj->sql_query($updateQuery);
    }
    // Code for Check last logout date is update in driver_log_report Ends
    //getTripChatDetails($iTripId);
    $returnArr['Action'] = "1";
    $eType = $tripCancelData[0]['eType'];
    if ($eType == "Ride") {
        $label = "LBL_SUCCESS_TRIP_CANCELED";
    }
    elseif ($eType == "UberX") {
        $label = "LBL_SUCCESS_BOOKING_CANCELED";
    }
    else {
        $label = "LBL_SUCCESS_DELIVERY_CANCELED";
    }
    if ($userType == "Passenger") {
        $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
        $returnArr['message1'] = $label;
    }
    else {
        $returnArr['message1'] = $label;
    }
    if ($old_iActive != 'Active' || $fCancellationFare > 0) {
        if ($userType == "Passenger") {
            if ($eType == "Multi-Delivery") {
                sendTripReceipt_Multi($iTripId);
            }
            else {
                sendTripReceipt($iTripId);
            }
        }
        else {
            if ($eType == "Multi-Delivery") {
                sendTripReceiptAdmin_Multi($iTripId);
            }
            else {
                sendTripReceiptAdmin($iTripId);
            }
        }
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "GenerateTrip") {
    $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';
    $Source_point_latitude = isset($_REQUEST["start_lat"]) ? $_REQUEST["start_lat"] : '';
    $Source_point_longitude = isset($_REQUEST["start_lon"]) ? $_REQUEST["start_lon"] : '';
    $Source_point_Address = isset($_REQUEST["sAddress"]) ? $_REQUEST["sAddress"] : '';
    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $vMsgCode = isset($_REQUEST["vMsgCode"]) ? $_REQUEST["vMsgCode"] : '';
    $setCron = isset($_REQUEST["setCron"]) ? $_REQUEST["setCron"] : 'No';
    $ride_type = isset($_REQUEST["ride_type"]) ? $_REQUEST["ride_type"] : '';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $REQUEST_TYPE = isset($_REQUEST["REQUEST_TYPE"]) ? $_REQUEST["REQUEST_TYPE"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    ####### blocking code ################
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $BlockData = getBlockData("Driver", $driver_id);
        if (!empty($BlockData) || $BlockData != "") {
            setDataResponse($BlockData);
        }
    }
    ####### blocking code ################
    if ($ride_type != "Multi-Delivery" && $ride_type != "UberX" && $driver_id > 0) {
        $sqldata = "SELECT iTripId,ePoolRide FROM `trips` WHERE ( iActive='On Going Trip' OR iActive='Active' ) AND iDriverId='" . $driver_id . "' AND ePoolRide='No'";
        $TripData = $obj->MySQLSelect($sqldata);
        if (count($TripData) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_DRIVER_NOT_ACCEPT_TRIP";
            setDataResponse($returnArr);
        }
    }
    #### Update Driver Request Status of Trip ####
    UpdateDriverRequest2($driver_id, $passenger_id, $iTripId, "", $vMsgCode, "Yes");
    #### Update Driver Request Status of Trip ####
    /* For DriverSubscription added by SP start */
    if (checkDriverSubscriptionModule()) {
        $returnSubStatus = 0;
        $returnSubStatus = checkDriverSubscribed($driver_id);
        if ($returnSubStatus == 1) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PENDING_MIXSUBSCRIPTION";
            setDataResponse($returnArr);
        }
        else if ($returnSubStatus == 2) {
            $returnArr['Action'] = "0";
            //$returnArr['message'] = "PENDING_SUBSCRIPTION";
            $returnArr['message'] = "LBL_PENDING_SUBSCRIPTION_TEXT";
            setDataResponse($returnArr);
        }
    }
    /*$apcValue = $driver_id;
    
    $apcKey = $iCabBookingId;
    
    if ($iCabRequestId != "") {
    
        $apcKey = $iCabRequestId;
    
    }
    
    $lockValue = $generalobj->generateAPC($apcKey, $apcValue);
    
    if ($lockValue > 0) {
    
        if (empty($REQUEST_TYPE)) {
    
            if (!empty($iCabRequestId)) {
    
                $sql_RequestData = "SELECT eType FROM cab_request_now WHERE iUserId='$passenger_id' and iCabRequestId = '$iCabRequestId'";
    
                $cabRequestData = $obj->MySQLSelect($sql_RequestData);
    
                if (!empty($cabRequestData) && count($cabRequestData) > 0) {
    
                    $REQUEST_TYPE = $cabRequestData[0]['eType'];
    
                }
    
            } else if (!empty($iCabBookingId)) {
    
                $cabBookingData = get_value('cab_booking', 'eType', 'iCabBookingId', $iCabBookingId);
    
                if (!empty($cabBookingData) && count($cabBookingData) > 0) {
    
                    $REQUEST_TYPE = $cabBookingData[0]['eType'];
    
                }
    
            }
    
        }
    
        $returnArr['Action'] = "0";
    
        $returnArr['message'] = empty($REQUEST_TYPE) ? "LBL_DRIVER_NOT_ACCEPT_TRIP" : ($REQUEST_TYPE == "Ride" ? "LBL_FAIL_ASSIGN_TO_PASSENGER_TXT" : ($REQUEST_TYPE == "Deliver" || $REQUEST_TYPE == "Delivery") ? "LBL_FAIL_ASSIGN_TO_PASSENGER_DELIVERY_TXT" : "LBL_FAIL_ASSIGN_TO_PASSENGER_UFX_TXT");
    
        setDataResponse($returnArr);
    
    }*/
    /*     * ******* Create Service Lock ********* */
    $isServiceLock = $generalobj->checkServiceLock($iCabRequestId, $iCabBookingId, false, false, $driver_id);
    if ($isServiceLock) {
        if (empty($REQUEST_TYPE)) {
            if (!empty($iCabRequestId)) {
                $sql_RequestData = "SELECT eType FROM cab_request_now WHERE iUserId='$passenger_id' and iCabRequestId = '$iCabRequestId'";
                $cabRequestData = $obj->MySQLSelect($sql_RequestData);
                if (!empty($cabRequestData) && count($cabRequestData) > 0) {
                    $REQUEST_TYPE = $cabRequestData[0]['eType'];
                }
            }
            else if (!empty($iCabBookingId)) {
                $cabBookingData = get_value('cab_booking', 'eType', 'iCabBookingId', $iCabBookingId);
                if (!empty($cabBookingData) && count($cabBookingData) > 0) {
                    $REQUEST_TYPE = $cabBookingData[0]['eType'];
                }
            }
        }
        $returnArr['Action'] = "0";
        $returnArr['message'] = empty($REQUEST_TYPE) ? "LBL_DRIVER_NOT_ACCEPT_TRIP" : ($REQUEST_TYPE == "Ride" ? "LBL_FAIL_ASSIGN_TO_PASSENGER_TXT" : ($REQUEST_TYPE == "Deliver" || $REQUEST_TYPE == "Delivery") ? "LBL_FAIL_ASSIGN_TO_PASSENGER_DELIVERY_TXT" : "LBL_FAIL_ASSIGN_TO_PASSENGER_UFX_TXT");
        setDataResponse($returnArr);
    }
    /*     * ******* Create Service Lock ********* */
    $eType_cabbooking = "";
    if ($iCabBookingId != "") {
        $bookingData = get_value('cab_booking', 'iUserId,vSourceLatitude,vSourceLongitude,vSourceAddresss,eType,dBooking_date,eStatus,ePayType', 'iCabBookingId', $iCabBookingId);
        $passenger_id = $bookingData[0]['iUserId'];
        $Source_point_latitude = $bookingData[0]['vSourceLatitude'];
        $Source_point_longitude = $bookingData[0]['vSourceLongitude'];
        $Source_point_Address = $bookingData[0]['vSourceAddresss'];
        $eType_cabbooking = $bookingData[0]['eType'];
        ## Check Timing For Later Booking ##
        $additional_mins = $BOOKING_LATER_ACCEPT_BEFORE_INTERVAL;
        $additional_mins_into_secs = $additional_mins * 60;
        $dBooking_date = $bookingData[0]['dBooking_date'];
        $currDate = date('Y-m-d H:i:s');
        //$currDate = date("Y-m-d H:i:s", strtotime($currDate . "-".$additional_mins." minutes"));
        $datediff = abs(strtotime($dBooking_date) - strtotime($currDate));
        $eStatusnew = $bookingData[0]['eStatus'];
        if ($datediff > $additional_mins_into_secs) {
            $vDriverLangCode = get_value('register_driver', 'vLang', 'iDriverId', $driver_id, '', 'true');
            $mins = get_value('language_label', 'vValue', 'vLabel', 'LBL_MINUTES_TXT', " and vCode='" . $vDriverLangCode . "'", 'true');
            $hrs = get_value('language_label', 'vValue', 'vLabel', 'LBL_HOURS_TXT', " and vCode='" . $vDriverLangCode . "'", 'true');
            $LBL_RIDE_LATER_START_VALIDATION_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_RIDE_LATER_START_VALIDATION_TXT', " and vCode='" . $vDriverLangCode . "'", 'true');
            if ($additional_mins <= 60) {
                $beforetext = $additional_mins . " " . $mins;
                $message = str_replace('####', $beforetext, $LBL_RIDE_LATER_START_VALIDATION_TXT);
            }
            else if ($eStatusnew == 'Cancel') {
                $LBL_MANAUL_BOOKING_CANCELLED_MSG = get_value('language_label', 'vValue', 'vLabel', 'LBL_MANAUL_BOOKING_CANCELLED_MSG', " and vCode='" . $vDriverLangCode . "'", 'true');
                $message = $LBL_MANAUL_BOOKING_CANCELLED_MSG;
                $returnArr['DO_RELOAD'] = "Yes";
            }
            else {
                $hours = floor($additional_mins / 60);
                $beforetext = $hours . " " . $hrs;
                $message = str_replace('####', $beforetext, $LBL_RIDE_LATER_START_VALIDATION_TXT);
            }
            //Added BY HJ On 20-01-2020 For Removed Lock File For UFX Request Start
            $serviceId = empty($iCabRequestId) ? $iCabBookingId : $iCabRequestId;
            $fileName = $isDeliverAll == true ? "Order_" . $iCabRequestId : (empty($iCabRequestId) ? "CabBooking_" . $iCabBookingId : "CabRequest_" . $iCabRequestId);
            /*added 26-05-2020*/
            $fileName .= "_" . $_SERVER['HTTP_HOST'];
            unlink($tconfig['tpanel_path'] . "webimages/lockFile/" . md5($fileName) . ".txt");
            //Added BY HJ On 20-01-2020 For Removed Lock File For UFX Request End
            $returnArr['Action'] = "0";
            $returnArr['message'] = $message;
            setDataResponse($returnArr);
        }
        ## Check Timing For Later Booking ##
        
        //added by SP for checking wallet balance in uberx when driver accept request of user of book later start
        $ePayType_cabbooking = $bookingData[0]['ePayType'];
        //$Data1['ACCEPT_CASH_TRIPS'] = "Yes";
        if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
            $user_available_balance = $generalobj->get_user_available_balance($driver_id, "Driver");

            ///echo $WALLET_MIN_BALANCE."===".$user_available_balance;die;
            if ($WALLET_MIN_BALANCE > $user_available_balance) {
                //$Data1['ACCEPT_CASH_TRIPS'] = "No";
                //Added BY HJ On 23-09-2019 As Per Discuss with BM and GP Mam Start
                
                if ($ePayType_cabbooking == "Cash" && $eType_cabbooking == "UberX") {
                    $returnArr = array();
                    $returnArr['Action'] = "0"; // code is invalid
                    $returnArr["message"] = "LBL_CHECK_PROVIDER_MIN_WALLET_BALANCE_TXT";
                    echo json_encode($returnArr);
                    exit;
                }
                //Added BY HJ On 23-09-2019 As Per Discuss with BM and GP Mam End
            }
        }
        //added by SP for checking wallet balance in uberx when driver accept request of user of book later end
        
    }
    $DriverMessage = "CabRequestAccepted";
    $TripRideNO = rand(10000000, 99999999);
    $TripVerificationCode = generateCommonRandom();
    $Active = "Active";
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $passenger_id, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vGMapLangCode = get_value('language_master', 'vGMapLangCode', 'vCode', $vLangCode, '', 'true');
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $tripdriverarrivlbl = $languageLabelsArr['LBL_DRIVER_ARRIVING'];
    $reqestId = $vBookSomeOneName = $vBookSomeOneNumber = "";
    $outstandingId = $iCabRequestId;
    $trip_status_chkField = "iCabRequestId";
    /* added for rental */
    $ePoolRide = $eBookForSomeOneElse = "No";
    $iPersonSize = $poolParentId = 0;
    if ($iCabRequestId != "") {
        $sql = "SELECT eStatus,ePayType,iVehicleTypeId,iCabBookingId,vSourceLatitude,vSourceLongitude,tSourceAddress,vDestLatitude,vDestLongitude,tDestAddress,iRentalPackageId,vCouponCode,eType,iPackageTypeId,vReceiverName,vReceiverMobile,tPickUpIns,tDeliveryIns,tPackageDetails,fPickUpPrice,fNightPrice,iQty,vRideCountry,fTollPrice,vTollPriceCurrencyCode,eTollSkipped,vTimeZone,iUserAddressId,tUserComment,eFlatTrip,fFlatTripPrice,eWalletDebitAllow,eBookingFrom,iFare,iBaseFare,fPricePerMin,fPricePerKM,fCommision,fSurgePriceDiff,fTax1,fTax2,fTax1Percentage,fTax2Percentage,fOutStandingAmount,fDistance,fDuration,ePaymentByReceiver,fMinFareDiff,fTripGenerateFare,fDiscount,vDiscount,fWalletDebit,iTripReasonId,vReasonTitle,eTripReason,ePayWallet,eServiceLocation,tVehicleTypeData,tVehicleTypeFareData, tTotalDuration, tTotalDistance, tEstimatedCharge, tUserWalletBalance, iFromStationId, iToStationId,vBookSomeOneNumber,eBookForSomeOneElse,vBookSomeOneName,iUserId,fRoundingAmount,eRoundingType FROM cab_request_now WHERE iUserId='$passenger_id' and iCabRequestId = '$iCabRequestId'"; //added by SP for fly stations on 19-08-2019
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            reDefineGenerateTripQuery();
        }
        $check_row = $obj->MySQLSelect($sql);
        if (count($check_row) > 0) {
            $eStatus = $check_row[0]['eStatus'];
            $eType = $check_row[0]['eType'];
            if ($eType_cabbooking != "") {
                $eType = $eType_cabbooking;
            }
            $reqestId = $iCabRequestId;
            $trip_status_chkField = "iCabRequestId";
        }
    }
    else {
        $sql = "select eStatus,eType from cab_booking where iCabBookingId = '$iCabBookingId'";
        $cab_data = $obj->MySQLSelect($sql);
        $eStatus = $cab_data[0]['eStatus'];
        $eType = $cab_data[0]['eType'];
        $reqestId = $outstandingId = $iCabBookingId;
        $trip_status_chkField = "iCabBookingId";
    }
    if ($eType == "Ride") {
        $requestcancelbyuser = "LBL_CAR_REQUEST_CANCELLED_TXT";
        $failassigntopassenger = "LBL_FAIL_ASSIGN_TO_PASSENGER_TXT";
        $useronanothertrip = "LBL_USER_ON_ANOTHER_TRIP";
    } else if ($eType == "Deliver") {
        $requestcancelbyuser = "LBL_CAR_REQUEST_CANCELLED_DELIVERY_TXT";
        $failassigntopassenger = "LBL_FAIL_ASSIGN_TO_PASSENGER_DELIVERY_TXT";
        $useronanothertrip = "LBL_USER_ON_ANOTHER_TRIP";
    } else {
        $requestcancelbyuser = "LBL_CAR_REQUEST_CANCELLED_UFX_TXT";
        $failassigntopassenger = "LBL_FAIL_ASSIGN_TO_PASSENGER_UFX_TXT";
        $useronanothertrip = "LBL_USER_ON_ANOTHER_TRIP";
    }
    if ($eStatus == "Completed") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $failassigntopassenger;
        setDataResponse($returnArr);
    }
    else {
        if ($APP_TYPE != "UberX") {
            $sql = "select iTripId,vTripStatus from register_user where iUserId='$passenger_id'";
            $user_data = $obj->MySQLSelect($sql);
            $iTripId = $user_data[0]['iTripId'];
            if ($iTripId != "" && $iTripId != 0) {
                $status_trip = get_value("trips", 'iActive', "iTripId", $iTripId, '', 'true');
                $trip_etype = get_value("trips", 'eType', "iTripId", $iTripId, '', 'true');
                $cab_id = get_value("trips", $trip_status_chkField, "iTripId", $iTripId, '', 'true');
                $iOrderId_chk_ext = get_value("trips", 'iOrderId', "iTripId", $iTripId, '', 'true');
                // added for multiple trips (deliverall)
                if (($status_trip == "Active" || $status_trip == "On Going Trip") && ($trip_etype != "UberX" && $trip_etype != "Multi-Delivery") && $ride_type != "Multi-Delivery" && $iOrderId_chk_ext == 0 /* && DELIVERALL == "No" */) {
                    if ($reqestId == $cab_id) {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = $failassigntopassenger;
                        setDataResponse($returnArr);
                    } else {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = $useronanothertrip;
                        setDataResponse($returnArr);
                    }
                }
            }
        }
    }
    if ($eStatus == "Requesting" || (($eStatus == "Assign" || $eStatus == "Accepted") && $iCabBookingId != "" && $iCabRequestId == "")) {
        if ($iCabRequestId != "") {
            $where = " iCabRequestId = '$iCabRequestId'";
            $Data_update_cab_request['eStatus'] = 'Complete';
            $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_request, 'update', $where);
        }
        $sql = "SELECT vCurrencyPassenger,iAppVersion,iUserPetId,CONCAT(vName,' ',vLastName) AS bookerName FROM `register_user` WHERE iUserId = '$passenger_id'";
        $Data_passenger_detail = $obj->MySQLSelect($sql);
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019
        $sqlCodeData = $obj->MySQLSelect("SELECT c.vPhoneCode FROM  `register_user` AS r,  `country` AS c WHERE r.iUserId = $passenger_id AND r.vCountry = c.vCountryCode");
        $Data_passenger_detail[0]['vPhoneCode'] = $sqlCodeData[0]['vPhoneCode'];
        if ($APP_TYPE == "Ride-Delivery-UberX" && $eType == "UberX") {
            $sql = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '$driver_id' AND eType = 'UberX'";
            $Data_vehicle_uberx = $obj->MySQLSelect($sql);
            $CAR_id_driver = $Data_vehicle_uberx[0]['iDriverVehicleId'];
            $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,vName,vLastName,vPhone,vCode,vImage,vAvgRating,vWorkLocation,vWorkLocationLatitude,vWorkLocationLongitude,vWorkLocationRadius,eSelectWorkLocation,vLang FROM `register_driver` WHERE iDriverId = '$driver_id'";
            $Data_vehicle = $obj->MySQLSelect($sql);
        }
        else {
            $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,vName,vLastName,vPhone,vCode,vImage,vAvgRating,vWorkLocation,vWorkLocationLatitude,vWorkLocationLongitude,vWorkLocationRadius,eSelectWorkLocation,vLang FROM `register_driver` WHERE iDriverId = '$driver_id'";
            $Data_vehicle = $obj->MySQLSelect($sql);
            $CAR_id_driver = $Data_vehicle[0]['iDriverVehicleId'];
        }
        // Changed for rental
        // add for hotel web
        if ($iCabBookingId != "") {
            $sql_booking = "SELECT vSourceLatitude, vSourceLongitude,vSourceAddresss,vDestLatitude,vDestLongitude,tDestAddress,ePayType,iVehicleTypeId,iRentalPackageId,eType,iPackageTypeId,vReceiverName,vReceiverMobile,tPickUpIns,tDeliveryIns,tPackageDetails,fPickUpPrice,fNightPrice,iUserPetId,vCouponCode,iQty,vRideCountry,fTollPrice,vTollPriceCurrencyCode,eTollSkipped, vTimeZone,iUserAddressId,tUserComment,eFlatTrip,fFlatTripPrice,eWalletDebitAllow,eBookingFrom,iFare,iBaseFare,fPricePerMin,fPricePerKM,fCommision,fSurgePriceDiff,fTax1,fTax2,fTax1Percentage,fTax2Percentage,fOutStandingAmount,vDistance, vDuration, ePaymentByReceiver, fMinFareDiff, fTripGenerateFare,fDiscount,vDiscount,fWalletDebit,iTripReasonId,vReasonTitle,eTripReason,ePayWallet,eServiceLocation,tVehicleTypeData,tVehicleTypeFareData, vWorkLocation, vWorkLocationLatitude, vWorkLocationLongitude, eSelectWorkLocation, tTotalDuration, tTotalDistance, tEstimatedCharge, tUserWalletBalance, iFromStationId, iToStationId,vBookSomeOneNumber,eBookForSomeOneElse,vBookSomeOneName,iUserId FROM cab_booking WHERE iCabBookingId='$iCabBookingId'"; //added by SP for fly stations on 19-08-2019
            if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                reDefineGenerateTripQuery();
            }
            $data_booking = $obj->MySQLSelect($sql_booking);
            $bokingData = $data_booking; //Added By HJ On 22-03-2019 For Get Data OF Trip
            $iSelectedCarType = $data_booking[0]['iVehicleTypeId'];
            $iRentalPackageId = $data_booking[0]['iRentalPackageId'];
            $vTripPaymentMode = $data_booking[0]['ePayType'];
            $tDestinationLatitude = $data_booking[0]['vDestLatitude'];
            $tDestinationLongitude = $data_booking[0]['vDestLongitude'];
            $tDestinationAddress = $data_booking[0]['tDestAddress'];
            $fPickUpPrice = $data_booking[0]['fPickUpPrice'];
            $fNightPrice = $data_booking[0]['fNightPrice'];
            $Source_point_latitude = $data_booking[0]['vSourceLatitude'];
            $Source_point_longitude = $data_booking[0]['vSourceLongitude'];
            $Source_point_Address = $data_booking[0]['vSourceAddresss'];
            $eType = $data_booking[0]['eType'];
            $iPackageTypeId = $data_booking[0]['iPackageTypeId'];
            $vReceiverName = $data_booking[0]['vReceiverName'];
            $vReceiverMobile = $data_booking[0]['vReceiverMobile'];
            $tPickUpIns = $data_booking[0]['tPickUpIns'];
            $tDeliveryIns = $data_booking[0]['tDeliveryIns'];
            $tPackageDetails = $data_booking[0]['tPackageDetails'];
            $iUserPetId = $data_booking[0]['iUserPetId'];
            $vCouponCode = $data_booking[0]['vCouponCode'];
            $iQty = $data_booking[0]['iQty'];
            $vRideCountry = $data_booking[0]['vRideCountry'];
            $fTollPrice = $data_booking[0]['fTollPrice'];
            $vTollPriceCurrencyCode = $data_booking[0]['vTollPriceCurrencyCode'];
            $eTollSkipped = $data_booking[0]['eTollSkipped'];
            $vTimeZone = $data_booking[0]['vTimeZone'];
            $iUserAddressId = $data_booking[0]['iUserAddressId'];
            $tUserComment = $data_booking[0]['tUserComment'];
            $eFlatTrip = $data_booking[0]['eFlatTrip'];
            $fFlatTripPrice = $data_booking[0]['fFlatTripPrice'];
            #######to use in multidelivery#######
            $fTripGenerateFare = $data_booking[0]['fTripGenerateFare'];
            $iFare = $data_booking[0]['iFare'];
            $eWalletDebitAllow = $data_booking[0]['eWalletDebitAllow'];
            // add for hotel web
            $eBookingFrom = $data_booking[0]['eBookingFrom'];
            $iBaseFare = $data_booking[0]['iBaseFare'];
            $fPricePerMin = $data_booking[0]['fPricePerMin'];
            $fPricePerKM = $data_booking[0]['fPricePerKM'];
            $fCommision = $data_booking[0]['fCommision'];
            $fSurgePriceDiff = $data_booking[0]['fSurgePriceDiff'];
            $fTax1 = $data_booking[0]['fTax1'];
            $fTax2 = $data_booking[0]['fTax2'];
            $fTax1Percentage = $data_booking[0]['fTax1Percentage'];
            $fTax2Percentage = $data_booking[0]['fTax2Percentage'];
            $fOutStandingAmount = $data_booking[0]['fOutStandingAmount'];
            $fDiscount = $data_booking[0]['fDiscount'];
            $vDiscount = $data_booking[0]['vDiscount'];
            $fDistance = $data_booking[0]['vDistance'];
            $fDuration = $data_booking[0]['vDuration'];
            $fMinFareDiff = $data_booking[0]['fMinFareDiff'];
            $ePaymentByReceiver = $data_booking[0]['ePaymentByReceiver'];
            $fWalletDebit = $data_booking[0]['fWalletDebit'];
            $iTripReasonId = $data_booking[0]['iTripReasonId'];
            $vReasonTitle = $data_booking[0]['vReasonTitle'];
            $eTripReason = $data_booking[0]['eTripReason'];
            #######to use in multidelivery#######
            // Related to New Service Provider Flow
            $vWorkLocation = $data_booking[0]['vWorkLocation'];
            $vWorkLocationLatitude = $data_booking[0]['vWorkLocationLatitude'];
            $vWorkLocationLongitude = $data_booking[0]['vWorkLocationLongitude'];
            $eSelectWorkLocation = $data_booking[0]['eSelectWorkLocation'];
            // payment method 2
            $ePayWallet = $data_booking[0]['ePayWallet'];
            $tTotalDuration = $data_booking[0]['tTotalDuration'];
            $tTotalDistance = $data_booking[0]['tTotalDistance'];
            $tEstimatedCharge = $data_booking[0]['tEstimatedCharge'];
            $tUserWalletBalance = $data_booking[0]['tUserWalletBalance'];
            //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data Start
            $tVehicleTypeData = $data_booking[0]['tVehicleTypeData'];
            $tVehicleTypeFareData = $data_booking[0]['tVehicleTypeFareData'];
            $eServiceLocation = $data_booking[0]['eServiceLocation'];
            //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data End
            //added by SP for fly stations on 19-08-2019 start
            $iFromStationId = $data_booking[0]['iFromStationId'];
            $iToStationId = $data_booking[0]['iToStationId'];
            //added by SP for fly stations on 19-08-2019 end
            //$fRoundingAmount = $data_booking[0]['fRoundingAmount'];
            //$eRoundingType = $data_booking[0]['eRoundingType'];
            
        }
        else {
            $bokingData = $check_row; //Added By HJ On 22-03-2019 For Get Data OF Trip
            $iSelectedCarType = $check_row[0]['iVehicleTypeId'];
            $iRentalPackageId = $check_row[0]['iRentalPackageId'];
            $vTripPaymentMode = $check_row[0]['ePayType'];
            $tDestinationLatitude = $check_row[0]['vDestLatitude'];
            $tDestinationLongitude = $check_row[0]['vDestLongitude'];
            $tDestinationAddress = $check_row[0]['tDestAddress'];
            $fPickUpPrice = $check_row[0]['fPickUpPrice'];
            $fNightPrice = $check_row[0]['fNightPrice'];
            $Source_point_latitude = $check_row[0]['vSourceLatitude'];
            $Source_point_longitude = $check_row[0]['vSourceLongitude'];
            $Source_point_Address = $check_row[0]['tSourceAddress'];
            $eType = $check_row[0]['eType'];
            $iPackageTypeId = $check_row[0]['iPackageTypeId'];
            $vReceiverName = $check_row[0]['vReceiverName'];
            $vReceiverMobile = $check_row[0]['vReceiverMobile'];
            $tPickUpIns = $check_row[0]['tPickUpIns'];
            $tDeliveryIns = $check_row[0]['tDeliveryIns'];
            $tPackageDetails = $check_row[0]['tPackageDetails'];
            $iUserPetId = $Data_passenger_detail[0]['iUserPetId'];
            $vCouponCode = $check_row[0]['vCouponCode'];
            $iQty = $check_row[0]['iQty'];
            $eFlatTrip = $check_row[0]['eFlatTrip'];
            $fFlatTripPrice = $check_row[0]['fFlatTripPrice'];
            $vRideCountry = $check_row[0]['vRideCountry'];
            $fTollPrice = $check_row[0]['fTollPrice'];
            $vTollPriceCurrencyCode = $check_row[0]['vTollPriceCurrencyCode'];
            $eTollSkipped = $check_row[0]['eTollSkipped'];
            $vTimeZone = $check_row[0]['vTimeZone'];
            $iUserAddressId = $check_row[0]['iUserAddressId'];
            $tUserComment = $check_row[0]['tUserComment'];
            $iCabBookingId = $check_row[0]['iCabBookingId'];
            #######to use in multidelivery#######
            $fTripGenerateFare = $check_row[0]['fTripGenerateFare'];
            $iFare = $check_row[0]['iFare'];
            $eWalletDebitAllow = $check_row[0]['eWalletDebitAllow'];
            //  add for hotel web
            $eBookingFrom = $check_row[0]['eBookingFrom'];
            $iBaseFare = $check_row[0]['iBaseFare'];
            $fPricePerMin = $check_row[0]['fPricePerMin'];
            $fPricePerKM = $check_row[0]['fPricePerKM'];
            $fCommision = $check_row[0]['fCommision'];
            $fSurgePriceDiff = $check_row[0]['fSurgePriceDiff'];
            $fTax1 = $check_row[0]['fTax1'];
            $fTax2 = $check_row[0]['fTax2'];
            $fTax1Percentage = $check_row[0]['fTax1Percentage'];
            $fTax2Percentage = $check_row[0]['fTax2Percentage'];
            //$fOutStandingAmount = $check_row[0]['fOutStandingAmount'];
            $fOutStandingAmount = GetPassengerOutstandingAmount($passenger_id);
            $fDiscount = $check_row[0]['fDiscount'];
            $vDiscount = $check_row[0]['vDiscount'];
            $fDistance = $check_row[0]['fDistance'];
            $fDuration = $check_row[0]['fDuration'];
            $fMinFareDiff = $check_row[0]['fMinFareDiff'];
            $ePaymentByReceiver = $check_row[0]['ePaymentByReceiver'];
            $fWalletDebit = $check_row[0]['fWalletDebit'];
            $iTripReasonId = $check_row[0]['iTripReasonId'];
            $vReasonTitle = $check_row[0]['vReasonTitle'];
            $eTripReason = $check_row[0]['eTripReason'];
            #######to use in multidelivery#######
            // payment method 2
            $ePayWallet = $check_row[0]['ePayWallet'];
            $tTotalDuration = $check_row[0]['tTotalDuration'];
            $tTotalDistance = $check_row[0]['tTotalDistance'];
            $tEstimatedCharge = $check_row[0]['tEstimatedCharge'];
            $tUserWalletBalance = $check_row[0]['tUserWalletBalance'];
            //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data Start
            $tVehicleTypeData = $check_row[0]['tVehicleTypeData'];
            $tVehicleTypeFareData = $check_row[0]['tVehicleTypeFareData'];
            $eServiceLocation = $check_row[0]['eServiceLocation'];
            //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data End
            //added by SP for fly stations on 19-08-2019 start
            $iFromStationId = $check_row[0]['iFromStationId'];
            $iToStationId = $check_row[0]['iToStationId'];
            //added by SP for fly stations on 19-08-2019 end
            //added by SP for rounding off for multi delivery on 7-11-2019
            $fRoundingAmount = $check_row[0]['fRoundingAmount'];
            $eRoundingType = $check_row[0]['eRoundingType'];
        }
        $Data_trips['vRideNo'] = $TripRideNO;
        $Data_trips['iUserId'] = $passenger_id;
        $Data_trips['iDriverId'] = $driver_id;
        $Data_trips['tTripRequestDate'] = @date("Y-m-d H:i:s");
        $Data_trips['tStartLat'] = $Source_point_latitude;
        $Data_trips['tStartLong'] = $Source_point_longitude;
        $Data_trips['tSaddress'] = $Source_point_Address;
        $Data_trips['iActive'] = $Active;
        $Data_trips['iDriverVehicleId'] = $CAR_id_driver;
        $Data_trips['iVerificationCode'] = $TripVerificationCode;
        $Data_trips['iVehicleTypeId'] = $iSelectedCarType;
        $Data_trips['iRentalPackageId'] = $iRentalPackageId;
        $Data_trips['tUserWalletBalance'] = $tUserWalletBalance;
        //added by SP for fly stations on 19-08-2019 start
        //if($iFromStationId!='' && $iToStationId!='' && $iFromStationId!=0 && $iToStationId!=0) {
        if (!empty($iFromStationId) && !empty($iToStationId)) {
            $Data_trips['iActive'] = 'Arrived';
            //$where_fly = " iDriverId = '".$driver_id."'";
            //$Data_update_driver_fly['vTripStatus'] = 'Arrived';
            //$obj->MySQLQueryPerform("register_driver", $Data_update_driver_fly, 'update', $where_fly);
            
        }
        $Data_trips['iFromStationId'] = $iFromStationId;
        $Data_trips['iToStationId'] = $iToStationId;
        //added by SP for fly stations on 19-08-2019 end
        $tVehicleTypeDataCnt = $VehicleData = array();
        $eIconType = "Car";
        if ($iSelectedCarType > 0) {
            //$VehicleData = get_value('vehicle_type', 'eFareType,fVisitFee,eIconType,iWaitingFeeTimeLimit,vVehicleType', 'iVehicleTypeId', $iSelectedCarType);
            $sqlv = "SELECT eFareType,fVisitFee,eIconType,iWaitingFeeTimeLimit,vVehicleType_" . $vLangCode . " as vVehicleType From vehicle_type WHERE iVehicleTypeId = '" . $iSelectedCarType . "'";
            $VehicleData = $obj->MySQLSelect($sqlv);
            $Data_trips['eFareType'] = $VehicleData[0]['eFareType'];
            $Data_trips['fVisitFee'] = $VehicleData[0]['fVisitFee'];
            $Data_trips['iWaitingFeeTimeLimit'] = $VehicleData[0]['iWaitingFeeTimeLimit'];
            $eIconType = $VehicleData[0]['eIconType'];
        } else {
            $tVehicleTypeDataCnt = (array)json_decode($tVehicleTypeData);
            if (count($tVehicleTypeDataCnt > 1)) {
                $Data_trips['eFareType'] = "Fixed";
                $Data_trips['fVisitFee'] = $Data_trips['iWaitingFeeTimeLimit'] = 0;
            }
        }
        if ($eBookingFrom == 'kiosk') {
            $eWalletDebitAllow = "No";
        }
        $Data_trips['vTripPaymentMode'] = $vTripPaymentMode;
        $Data_trips['tEndLat'] = $tDestinationLatitude;
        $Data_trips['tEndLong'] = $tDestinationLongitude;
        $Data_trips['tDaddress'] = $tDestinationAddress;
        $Data_trips['fPickUpPrice'] = $fPickUpPrice;
        $Data_trips['fNightPrice'] = $fNightPrice;
        $Data_trips['iQty'] = $iQty;
        $Data_trips['eType'] = $eType;
        $Data_trips['iPackageTypeId'] = $iPackageTypeId;
        $Data_trips['vReceiverName'] = $vReceiverName;
        $Data_trips['vReceiverMobile'] = $vReceiverMobile;
        $Data_trips['tPickUpIns'] = $tPickUpIns;
        $Data_trips['tDeliveryIns'] = $tDeliveryIns;
        $Data_trips['tPackageDetails'] = $tPackageDetails;
        $Data_trips['iUserPetId'] = $iUserPetId;
        $Data_trips['vCountryUnitRider'] = getMemberCountryUnit($passenger_id, "Passenger");
        $Data_trips['vCountryUnitDriver'] = getMemberCountryUnit($driver_id, "Driver");
        $Data_trips['fTollPrice'] = $fTollPrice;
        $Data_trips['vTollPriceCurrencyCode'] = $vTollPriceCurrencyCode;
        $Data_trips['eTollSkipped'] = $eTollSkipped;
        $Data_trips['vTimeZone'] = $vTimeZone;
        $Data_trips['iUserAddressId'] = $iUserAddressId;
        $Data_trips['tUserComment'] = $tUserComment;
        $Data_trips['iCabBookingId'] = $iCabBookingId;
        $Data_trips['iCabRequestId'] = $iCabRequestId;
        $Data_trips['eFlatTrip'] = $eFlatTrip;
        $Data_trips['fFlatTripPrice'] = $fFlatTripPrice;
        $Data_trips['eWalletDebitAllow'] = $eWalletDebitAllow;
        // add for hotel web
        $Data_trips['eBookingFrom'] = $eBookingFrom;
        // payment method 2
        $Data_trips['ePayWallet'] = $ePayWallet;
        $Data_trips['tEstimatedCharge'] = $tEstimatedCharge;
        $Data_trips['tTotalDuration'] = $tTotalDuration;
        $Data_trips['tTotalDistance'] = $tTotalDistance;
        // payment method 2
        //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data Start
        //$Data_trips['tVehicleTypeData'] = $tVehicleTypeData; // Commented By HJ On 23-11-2019 For Soled \n data issue
        $Data_trips['tVehicleTypeData'] = $Data_trips['tVehicleTypeFareData'] = "";
        if (count($tVehicleTypeData) > 0 && !empty($tVehicleTypeData)) {
            $tVehicleTypeData_json = json_decode($tVehicleTypeData, true);
            //$Data_trips['tVehicleTypeData'] = str_replace("\\'", "'", json_encode($tVehicleTypeData, JSON_UNESCAPED_UNICODE)); // Added By HJ On 23-11-2019 For Soled \n data issue
            $Data_trips['tVehicleTypeData'] = $generalobj->getJsonFromAnArr($tVehicleTypeData_json);
            $Data_trips['tVehicleTypeFareData'] = $tVehicleTypeFareData;
        }
        //Added By HJ On 19-06-2020 For Solved Null Issue Start
        if($Data_trips['tVehicleTypeData'] == null || $Data_trips['tVehicleTypeData'] == "null"){
            $Data_trips['tVehicleTypeData'] = "";
        }
        if($Data_trips['tVehicleTypeFareData'] == null || $Data_trips['tVehicleTypeFareData'] == "null"){
            $Data_trips['tVehicleTypeFareData'] = "";
        }
        //Added By HJ On 19-06-2020 For Solved Null Issue End
        $Data_trips['eServiceLocation'] = $eServiceLocation;
        //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data End
        $sql = "SELECT dv.vLicencePlate,ma.vMake,mo.vTitle FROM driver_vehicle as dv LEFT JOIN make as ma ON dv.iMakeId = ma.iMakeId LEFT JOIN model as mo ON dv.iModelId = mo.iModelId WHERE dv.iDriverVehicleId = '" . $CAR_id_driver . "'";
        $DriverVehicle = $obj->MySQLSelect($sql);
        $DriverVehicleMake = $DriverVehicle[0]['vMake'];
        $DriverVehicleModel = $DriverVehicle[0]['vTitle'];
        $DriverVehicleLicencePlate = $DriverVehicle[0]['vLicencePlate'];
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            reDefineGenerateTripParam();
        }
        if ($ride_type == "Multi-Delivery") {
            $Data_trips['fTripGenerateFare'] = $fTripGenerateFare;
            $Data_trips['vVerificationMethod'] = $DELIVERY_VERIFICATION_METHOD;
            $Data_trips['iFare'] = $iFare;
            $Data_trips['iBaseFare'] = $iBaseFare;
            $Data_trips['fPricePerMin'] = $fPricePerMin;
            $Data_trips['fPricePerKM'] = $fPricePerKM;
            $Data_trips['fCommision'] = $fCommision;
            $Data_trips['fSurgePriceDiff'] = $fSurgePriceDiff;
            $Data_trips['fTax1'] = $fTax1;
            $Data_trips['fTax2'] = $fTax2;
            $Data_trips['fTax1Percentage'] = $fTax1Percentage;
            $Data_trips['fTax2Percentage'] = $fTax2Percentage;
            $Data_trips['fOutStandingAmount'] = $fOutStandingAmount;
            $Data_trips['fDistance'] = $fDistance;
            $Data_trips['fDuration'] = $fDuration;
            $Data_trips['fMinFareDiff'] = $fMinFareDiff;
            $Data_trips['ePaymentByReceiver'] = $ePaymentByReceiver;
            $Data_trips['eFareGenerated'] = "Yes";
            $Data_trips['fWalletDebit'] = $fWalletDebit;
            //added by SP for rounding off on 07-11-2019 for multi delivery
            $Data_trips['fRoundingAmount'] = $fRoundingAmount;
            $Data_trips['eRoundingType'] = $eRoundingType;
        }
        if ($ride_type != "Multi-Delivery") {
            $fOutStandingAmount = GetPassengerOutstandingAmount($passenger_id);
            $Data_trips['fOutStandingAmount'] = $fOutStandingAmount;
        }
        $Data_trips['iTripReasonId'] = $iTripReasonId;
        $Data_trips['vReasonTitle'] = $vReasonTitle;
        $Data_trips['eTripReason'] = $eTripReason;
        $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
        if ($eType == "UberX") {
            if ($iSelectedCarType > 0) {
                $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iSelectedCarType, '', 'true');
                $imageuploaddata = get_value($sql_vehicle_category_table_name, 'eBeforeUpload, eAfterUpload', 'iVehicleCategoryId', $iVehicleCategoryId);
                $Data_trips['eBeforeUpload'] = $imageuploaddata[0]['eBeforeUpload'];
                $Data_trips['eAfterUpload'] = $imageuploaddata[0]['eAfterUpload'];
            }
            else {
                //Added By HJ On On 01-02-2019 For Check Photo Upload Status When Multiple Service Found Start
                $eBeforeUploadArr = $eAfterUploadArr = array();
                for ($r = 0;$r < count($tVehicleTypeDataCnt);$r++) {
                    $typeIdSel = $tVehicleTypeDataCnt[$r]->iVehicleTypeId;
                    $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $typeIdSel, '', 'true');
                    $imageuploaddata = get_value($sql_vehicle_category_table_name, 'eBeforeUpload, eAfterUpload', 'iVehicleCategoryId', $iVehicleCategoryId);
                    $eBeforeUploadArr[] = $imageuploaddata[0]['eBeforeUpload'];
                    $eAfterUploadArr[] = $imageuploaddata[0]['eAfterUpload'];
                }
                $eBeforeUpload = $eAfterUpload = "No";
                if (in_array("Yes", $eBeforeUploadArr)) {
                    $eBeforeUpload = "Yes";
                }
                if (in_array("Yes", $eAfterUploadArr)) {
                    $eAfterUpload = "Yes";
                }
                $Data_trips['eBeforeUpload'] = $eBeforeUpload;
                $Data_trips['eAfterUpload'] = $eAfterUpload;
                //Added By HJ On On 01-02-2019 For Check Photo Upload Status When Multiple Service Found End
                
            }
        }
        if ($vCouponCode != '') {
            $Data_trips['vCouponCode'] = $vCouponCode;
            $Data_trips['fDiscount'] = $fDiscount;
            $Data_trips['vDiscount'] = $vDiscount;
            $sql = "SELECT iUsed, iUsageLimit from coupon WHERE vCouponCode = '" . $vCouponCode . "'";
            $coupon_result = $obj->MySQLSelect($sql);
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
        // Related to New Service Provider Flow
        if ($eType == "UberX" && $SERVICE_PROVIDER_FLOW == "Provider" && $Data_trips['eServiceLocation'] == "Driver") {
            if ($Data_trips['iCabBookingId'] > 0) {
                $Data_trips['vWorkLocationLatitude'] = $vWorkLocationLatitude;
                $Data_trips['vWorkLocationLongitude'] = $vWorkLocationLongitude;
                $Data_trips['vWorkLocation'] = $vWorkLocation;
                $Data_trips['eSelectWorkLocation'] = $eSelectWorkLocation;
            }
            else {
                if ($Data_vehicle[0]['eSelectWorkLocation'] == "Dynamic") {
                    $Data_trips['vWorkLocationLatitude'] = $vLatitude;
                    $Data_trips['vWorkLocationLongitude'] = $vLongitude;
                    $url_driver_geo = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $vLatitude . "," . $vLongitude . "&key=" . $GoogleServerKey . "&language=" . $Data_vehicle[0]['vLang'];
                    try {
                        $jsonfile_driver_geo = file_get_contents($url_driver_geo);
                        $jsondata_driver_geo = json_decode($jsonfile_driver_geo);
                        $source_address_driver_geo = $jsondata_driver_geo->results[0]->formatted_address;
                        $Data_trips['vWorkLocation'] = $source_address_driver_geo;
                    }
                        catch(ErrorException $ex) {
                    }
                }
                else {
                    $Data_trips['vWorkLocation'] = $Data_vehicle[0]['vWorkLocation'];
                    $Data_trips['vWorkLocationLatitude'] = $Data_vehicle[0]['vWorkLocationLatitude'];
                    $Data_trips['vWorkLocationLongitude'] = $Data_vehicle[0]['vWorkLocationLongitude'];
                }
                $Data_trips['eSelectWorkLocation'] = $Data_vehicle[0]['eSelectWorkLocation'];
            }
            $Data_trips['tStartLat'] = $Data_trips['vWorkLocationLatitude'];
            $Data_trips['tStartLong'] = $Data_trips['vWorkLocationLongitude'];
            $Data_trips['tSaddress'] = $Data_trips['vWorkLocation'];
        }
        $currencyList = get_value('currency', '*', 'eStatus', 'Active');
        for ($i = 0;$i < count($currencyList);$i++) {
            $currencyCode = $currencyList[$i]['vName'];
            $Data_trips['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
        }
        $Data_trips['vCurrencyPassenger'] = $Data_passenger_detail[0]['vCurrencyPassenger'];
        $Data_trips['vCurrencyDriver'] = $Data_vehicle[0]['vCurrencyDriver'];
        $Data_trips['fRatioPassenger'] = get_value('currency', 'Ratio', 'vName', $Data_passenger_detail[0]['vCurrencyPassenger'], '', 'true');
        $Data_trips['fRatioDriver'] = get_value('currency', 'Ratio', 'vName', $Data_vehicle[0]['vCurrencyDriver'], '', 'true');
        $iTripId = $obj->MySQLQueryPerform("trips", $Data_trips, 'insert');
        //Added By HJ On 22-03-2019 For Send SMS of Book FOr Someone Else Process Start
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            $Data_vehicle[0]['DriverVehicleLicencePlate'] = $DriverVehicleLicencePlate;
            sendSMSBookForSomeOneElse($vTripPaymentMode, $bokingData, $iTripId, $Data_passenger_detail, $VehicleData, $DriverVehicleMake, $DriverVehicleModel, $Data_vehicle);
        }
        //Added By HJ On 22-03-2019 For Send SMS of Book FOr Someone Else Process End
        //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 Start
        if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
            $obj->sql_query("UPDATE trip_outstanding_amount set eAuthoriseIdName='iTripId',iAuthoriseId = '" . $iTripId . "' WHERE iAuthoriseId='" . $outstandingId . "' AND eAuthoriseIdName='" . $trip_status_chkField . "'");
        }
        //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 End
        $trip_status = "Active";
        //if(($iFromStationId!='' && $iToStationId!='') || $iFromStationId!=0 && $iToStationId!=0) {
        if (!empty($iFromStationId) && !empty($iToStationId)) {
            $trip_status = 'Arrived';
        }
        ###For multidelivery trip id update in trip locations#######
        if ($ride_type == "Multi-Delivery") {
            $field_name = ($iCabBookingId != "" && $iCabBookingId > 0) ? "iCabBookingId" : "iCabRequestId";
            $field_value = ($iCabBookingId != "" && $iCabBookingId > 0) ? $iCabBookingId : $iCabRequestId;
            $where = " iCabBookingId='" . $field_value . "'";
            $data_update_loc['iTripId'] = $iTripId;
            $obj->MySQLQueryPerform("trips_delivery_locations", $data_update_loc, 'update', $where);
            $where1 = " $field_name='" . $field_value . "'";
            $data_update_field['iTripId'] = $iTripId;
            $obj->MySQLQueryPerform("trip_delivery_fields", $data_update_field, 'update', $where1);
            $generalobj->Update_Price_Individual($iTripId);
        }
        ############For multidelivery#######
        ###For Stopoverpoint iTripId update in trips_stopoverpoint_location######
        if (checkStopOverPointModule() && !empty($iCabRequestId) && !empty($iTripId)) {
            updateTripIds($iCabRequestId, $iTripId);
        }
        ############End Stopoverpoint#######
        ############For Outstanding Amount#######
        if ($fOutStandingAmount > 0 && $ride_type == "Multi-Delivery") {
            $updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $passenger_id;
            $obj->sql_query($updateQuery);
            //$updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes' WHERE iUserId = ".$iUserId;
            $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes',vTripAdjusmentId = '" . $iTripId . "' WHERE iUserId = '" . $passenger_id . "' AND ePaidByPassenger = 'No'";
            $obj->sql_query($updateQury);
        }
        ############For Outstanding Amount#######
        if ($iCabRequestId != "") {
            $where1 = " iCabRequestId = '$iCabRequestId'";
            $Data_update_cab_request['iTripId'] = $iTripId;
            $Data_update_cab_request['iDriverId'] = $driver_id;
            $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_request, 'update', $where1);
        }
        #### Update Driver Request Status of Trip ####
        UpdateDriverRequest2($driver_id, $passenger_id, $iTripId, "Accept", $vMsgCode, "No");
        #### Update Driver Request Status of Trip ####
        if ($iCabBookingId > 0) {
            $where = " iCabBookingId = '$iCabBookingId'";
            $data_update_booking['iTripId'] = $iTripId;
            $data_update_booking['eStatus'] = "Completed";
            $data_update_booking['iDriverId'] = $driver_id;
            $obj->MySQLQueryPerform("cab_booking", $data_update_booking, 'update', $where);
        }
        $where = " iUserId = '$passenger_id'";
        if ($APP_TYPE == "Ride-Delivery-UberX" && $eType != "UberX") {
            $Data_update_passenger['iTripId'] = $iTripId;
            $Data_update_passenger['vTripStatus'] = $trip_status;
        }
        else if ($eType != "UberX") {
            $Data_update_passenger['iTripId'] = $iTripId;
            $Data_update_passenger['vTripStatus'] = $trip_status;
        }
        $Data_update_passenger['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
        $Data_update_passenger['vVerificationCountEmergency'] = 0;
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
        $where = " iDriverId = '$driver_id'";
        $Data_update_driver['iTripId'] = $iTripId;
        $Data_update_driver['vTripStatus'] = $trip_status;
        $Data_update_driver['vRideCountry'] = $vRideCountry;
        $Data_update_driver['vAvailability'] = "Not Available";
        $Data_update_driver['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
        $Data_update_driver['vVerificationCountEmergency'] = 0;
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
        //update insurance log
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            $details_arr['iTripId'] = $iTripId;
            $details_arr['LatLngArr']['vLatitude'] = $vLatitude;
            $details_arr['LatLngArr']['vLongitude'] = $vLongitude;
            // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
            update_driver_insurance_status($driver_id, "Accept", $details_arr, "GenerateTrip");
        }
        //update insurance log
        if ($eType == "Deliver") {
            $drivername = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
            $tripdriverarrivlbl = $languageLabelsArr['LBL_CARRIER'] . " " . $drivername . " " . $languageLabelsArr['LBL_DRIVER_IS_ARRIVING'];
            $alertMsg = $tripdriverarrivlbl;
        }
        else if ($eType == "Ride") {
            $alertMsg = $tripdriverarrivlbl; // HeRE
            
        }
        else {
            $drivername = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
            $tripdriverarrivlbl = $languageLabelsArr['LBL_PROVIDER'] . " " . $drivername . " " . $languageLabelsArr['LBL_DRIVER_IS_ARRIVING'];
            $alertMsg = $tripdriverarrivlbl;
        }
        //Added By HJ On 04-12-2019 For Chnaged Message For Fly ride Start
        if (!empty($iFromStationId) && !empty($iToStationId)) {
            $alertMsg = $languageLabelsArr['LBL_FLY_ARRIVED'];
        }
        //Added By HJ On 04-12-2019 For Chnaged Message For Fly ride End
        $message_arr = array();
        $message_arr['iDriverId'] = $driver_id;
        $message_arr['Message'] = $DriverMessage;
        $message_arr['iTripId'] = strval($iTripId);
        $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
        if ($iCabBookingId > 0) {
            $message_arr['iCabBookingId'] = $iCabBookingId;
            $message_arr['iBookingId'] = $iCabBookingId;
        }
        $message_arr['eType'] = $eType;
        $message_arr['iTripVerificationCode'] = $TripVerificationCode;
        $message_arr['driverName'] = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
        $message_arr['driverPhone'] = "+" . $Data_vehicle[0]['vCode'] . " " . $Data_vehicle[0]['vPhone'];
        $driver_img_path = "";
        if ($Data_vehicle[0]['vImage'] != "" && file_exists($tconfig["tsite_upload_images_driver_path"] . "/" . $driver_id . "/2_" . $Data_vehicle[0]['vImage'])) {
            $driver_img_path = $tconfig["tsite_upload_images_driver"] . "/" . $driver_id . "/2_" . $Data_vehicle[0]['vImage'];
        }
        $message_arr['driverImage'] = get_tiny_url($driver_img_path);
        $message_arr['DriverVehicleLicencePlate'] = $DriverVehicleLicencePlate;
        $message_arr['DriverVehicleMakeModel'] = $DriverVehicleMake;
        $message_arr['driverRating'] = $Data_vehicle[0]['vAvgRating'];
        $message_arr['vRideNo'] = $TripRideNO;
        $message_arr['vTitle'] = $alertMsg;
        $message_arr['eSystem'] = "";
        $TripsHotelData = get_value('trips', 'iHotelId,eBookingFrom', 'iTripId', $iTripId);
        if (!empty($TripsHotelData)) {
            $eBookingFrom = $TripsHotelData[0]['eBookingFrom'];
            if ($eBookingFrom == 'Kiosk') {
                $iHotelId = $TripsHotelData[0]['iHotelId'];
                if (!empty($iHotelId) && $iHotelId > 0) {
                    $HotelData = get_value('hotel', 'vPickupFrom', 'iHotelId', $iHotelId);
                    $message_arr['vPickupFrom'] = $HotelData[0]['vPickupFrom'];
                }
            }
        }
        $message = json_encode($message_arr);
        #####################Add Status Message#########################
        $DataTripMessages['tMessage'] = $message;
        $DataTripMessages['iDriverId'] = $driver_id;
        $DataTripMessages['iTripId'] = $iTripId;
        $DataTripMessages['iUserId'] = $passenger_id;
        $DataTripMessages['eFromUserType'] = "Driver";
        $DataTripMessages['eToUserType'] = "Passenger";
        $DataTripMessages['eReceived'] = "No";
        $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
        $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
        ################################################################
        if ($setCron == 'Yes') {
            $passengerDetail = get_value('register_user', 'vName,vLastName,vPhone,vPhoneCode', 'iUserId', $passenger_id);
            $passengerName = $passengerDetail[0]['vName'] . ' ' . $passengerDetail[0]['vLastName'];
            $vPhoneCode = $passengerDetail[0]['vPhoneCode'];
            $vPhone = $passengerDetail[0]['vPhone'];
            $driverName = $Data_vehicle[0]['vName'] . ' ' . $Data_vehicle[0]['vLastName'];
            $messageEmail['details'] = '<p>Dear Administrator,</p>

                <p>Driver ( ' . $driverName . ' ) is assigned successfully for the following manual booking.</p>

                <p>Name: ' . $passengerName . ',</p>

                <p>Contact Number: +' . $vPhoneCode . $vPhone . '</p>';
            $mail = $generalobj->send_email_user('CRON_BOOKING_EMAIL', $messageEmail);
            $where_cabid2 = " iCabBookingId = '" . $iCabBookingId . "'";
            $Data_update2['eAssigned'] = 'Yes';
            $Data_update2['iDriverId'] = $driver_id;
            $id = $obj->MySQLQueryPerform("cab_booking", $Data_update2, 'update', $where_cabid2);
        }
        if ($iTripId > 0) {
            $alertSendAllowed = true;
            /* For PubNub Setting */
            $tableName = "register_user";
            $iMemberId_VALUE = $passenger_id;
            $iMemberId_KEY = "iUserId";
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            /* For PubNub Setting Finished */
            $sql = "SELECT iGcmRegId,eDeviceType,eIs_Kiosk FROM register_user WHERE iUserId='$passenger_id'";
            $result = $obj->MySQLSelect($sql);
            $registatoin_ids = $result[0]['iGcmRegId'];
            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();
            $alertSendAllowed = true;
            if ($alertSendAllowed == true) {
                if ($result[0]['eDeviceType'] == "Android") {
                    array_push($registation_ids_new, $result[0]['iGcmRegId']);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                }
                else {
                    if ($result[0]['eIs_Kiosk'] == "Yes") {
                        array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                        sendApplePushNotification(2, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }
                    else {
                        array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }
                }
            }
            ###### PUBNUB #########
            $channelName = "PASSENGER_" . $passenger_id;
            $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $passenger_id, '', 'true');
            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
            if (count($deviceTokens_arr_ios) > 0) {
                sleep(5);
            }
            publishEventMessage($channelName, $message_pub);
            ###### PUBNUB #########
            $returnArr['Action'] = "1";
            $data['iTripId'] = $iTripId;
            $data['tEndLat'] = $tDestinationLatitude;
            $data['tEndLong'] = $tDestinationLongitude;
            $data['tDaddress'] = $tDestinationAddress;
            $data['PAppVersion'] = $Data_passenger_detail[0]['iAppVersion'];
            $data['eFareType'] = $Data_trips['eFareType'];
            $data['vVehicleType'] = $eIconType;
            //$returnArr['APP_TYPE'] = $generalobj->getConfigurations("configurations","APP_TYPE");
            $returnArr['APP_TYPE'] = $APP_TYPE;
            $returnArr['message'] = $data;
            if ($iCabBookingId != "") {
                $passengerData = get_value('register_user', 'vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode,iAppVersion', 'iUserId', $passenger_id);
                $returnArr['sourceLatitude'] = $Source_point_latitude;
                $returnArr['sourceLongitude'] = $Source_point_longitude;
                $returnArr['PassengerId'] = $passenger_id;
                $returnArr['PName'] = $passengerData[0]['vName'] . ' ' . $passengerData[0]['vLastName'];
                $returnArr['PPicName'] = $passengerData[0]['vImgName'];
                $returnArr['PFId'] = $passengerData[0]['vFbId'];
                $returnArr['PRating'] = $passengerData[0]['vAvgRating'];
                $returnArr['PPhone'] = $passengerData[0]['vPhone'];
                $returnArr['PPhoneC'] = $passengerData[0]['vPhoneCode'];
                $returnArr['PAppVersion'] = $passengerData[0]['iAppVersion'];
                $returnArr['TripId'] = strval($iTripId);
                $returnArr['DestLocLatitude'] = $tDestinationLatitude;
                $returnArr['DestLocLongitude'] = $tDestinationLongitude;
                $returnArr['DestLocAddress'] = $tDestinationAddress;
                $returnArr['vVehicleType'] = $eIconType;
            }
            if ($Data_trips['eBookingFrom'] == 'Kiosk') {
                $maildata1 = array();
                $maildata1['VEHICLE_TYPE'] = $VehicleData[0]['vVehicleType'];
                $maildata1['CAR_NUMBER'] = "(" . $DriverVehicleMake . " " . $DriverVehicleModel . ")";
                $maildata1['DRIVER_NAME'] = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
                $maildata1['DRIVER_NUMBER'] = "(+" . $Data_vehicle[0]['vCode'] . " " . $Data_vehicle[0]['vPhone'] . ")";
                $maildata1['vRideNo'] = $Data_trips['vRideNo'];
                //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
                $passengerData = $obj->MySQLSelect("SELECT r.vName,r.vLastName,r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $passenger_id AND r.vCountry = c.vCountryCode");
                $PhoneCode = $passengerData[0]['vPhoneCode'];
                $PPhone = $passengerData[0]['vPhone'];
                $message_layout = $generalobj->send_messages_user("BOOKING_IN_KIOSK", $maildata1, "", $vLangCode);
                $result = $generalobj->sendSystemSms($PPhone, $PhoneCode, $message_layout);
                //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
                /* $passengerData = get_value('register_user', 'vName,vLastName,vPhone,vPhoneCode', 'iUserId', $passenger_id);
                
                  $phoneEBook = preg_replace("/[^0-9]/", "", $PPhone);
                
                  $vBookNumber = "+" . $phoneEBook;
                
                  $message_layout = $generalobj->send_messages_user("BOOKING_IN_KIOSK", $maildata1, "", $vLangCode);
                
                  $result = sendEmeSms($vBookNumber, $message_layout);
                
                  if ($result == 0) {
                
                  $phoneEBook = preg_replace("/[^0-9]/", "", $PPhone);
                
                  $vBookNumber = "+" . $PPhoneC . $phoneEBook;
                
                  $result = sendEmeSms($vBookNumber, $message_layout);
                
                  } */
            }
            setDataResponse($returnArr);
        }
        else {
            $data['Action'] = "0";
            $data['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($data);
        }
    } else {
        if ($eStatus == "Complete") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = $failassigntopassenger;
        } else if ($eStatus == "Cancel") {
            $returnArr['Action'] = "0";
            $vDriverLangCode = get_value('register_driver', 'vLang', 'iDriverId', $driver_id, '', 'true');
            $LBL_MANAUL_BOOKING_CANCELLED_MSG = get_value('language_label', 'vValue', 'vLabel', 'LBL_MANAUL_BOOKING_CANCELLED_MSG', " and vCode='" . $vDriverLangCode . "'", 'true');
            $returnArr['message'] = $LBL_MANAUL_BOOKING_CANCELLED_MSG;
        }
        else if ($eStatus == "Cancelled") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = $requestcancelbyuser;
        }
        $returnArr['DO_RELOAD'] = "Yes";
        setDataResponse($returnArr);
    }
}
###########################################################################
if ($type == "DriverArrived") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    if ($iDriverId != '') {
        //Added By HJ On 24-06-2020 For Optimization register_driver Table Query Start
        if(isset($userDetailsArr['register_driver_'.$iDriverId])){
            $driverData = $userDetailsArr['register_driver_'.$iDriverId];
        }else{
            $driverData = $obj->MySQLSelect("SELECT *,iDriverId as iMemberId FROM register_driver WHERE iDriverId='".$iDriverId."'"); 
            $userDetailsArr['register_driver_'.$iDriverId] = $driverData;
        }
        //Added By HJ On 24-06-2020 For Optimization register_driver Table Query End
        $vTripStatus = $driverData[0]['vTripStatus'];
        //$vTripStatus = get_value('register_driver', 'vTripStatus', 'iDriverId', $iDriverId, '', 'true');
        if ($vTripStatus == "Cancelled") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "DO_RESTART";
            setDataResponse($returnArr);
        }
        $where = " iDriverId = '$iDriverId'";
        $Data_update_driver['vTripStatus'] = 'Arrived';
        $Data_update_driver['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
        $Data_update_driver['vVerificationCountEmergency'] = 0;
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
        if ($id > 0) {
            //Added By HJ On 24-06-2020 For Optimization trips Table Query Start
            $driverTripId = $driverData[0]['iTripId'];
            if(isset($tripDetailsArr['trips_'.$driverTripId])){
                $data_trips = $tripDetailsArr['trips_'.$driverTripId];
            }else{
                $data_trips = $obj->MySQLSelect("select * from trips where iTripId='" . $driverTripId . "'");
                $tripDetailsArr['trips_'.$driverTripId] = $data_trips;
            }
            $result = $driverData;
            $result[0]['driverName'] = $result[0]['vName']." ".$result[0]['vLastName'];
            $result[0]['vRideNo'] = $data_trips[0]['vRideNo'];
            $result[0]['tEndLat'] = $data_trips[0]['tEndLat'];
            $result[0]['tEndLong'] = $data_trips[0]['tEndLong'];
            $result[0]['tDaddress'] = $data_trips[0]['tDaddress'];
            $result[0]['iUserId'] = $data_trips[0]['iUserId'];
            $result[0]['eType'] = $data_trips[0]['eType'];
            $result[0]['eTollSkipped'] = $data_trips[0]['eTollSkipped'];
            $result[0]['eBeforeUpload'] = $data_trips[0]['eBeforeUpload'];
            $result[0]['eAfterUpload'] = $data_trips[0]['eAfterUpload'];
            $result[0]['ePoolRide'] = $data_trips[0]['ePoolRide'];
            $result[0]['eServiceLocation'] = $data_trips[0]['eServiceLocation'];
            //Added By HJ On 24-06-2020 For Optimization trips Table Query End
            //$sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo, tr.tEndLat,tr.tEndLong,tr.tDaddress,tr.iUserId,tr.eType,rd.iTripId,tr.eTollSkipped,tr.eBeforeUpload,tr.eAfterUpload,tr.ePoolRide, tr.eServiceLocation FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
            //$result = $obj->MySQLSelect($sql);
            //echo "<pre>";print_r($result);die;
            $returnArr['Action'] = "1";
            /* move down to up */
            $tableName = "register_user";
            $iMemberId_VALUE = $result[0]['iUserId'];
            $iMemberId_KEY = "iUserId";
            //Added By HJ On 24-06-2020 For Optimization register_user Table Query Start
            if(isset($userDetailsArr[$tableName.'_'.$iMemberId_VALUE])){
                $AppData = $userDetailsArr[$tableName.'_'.$iMemberId_VALUE];
            }else{
                $AppData = $obj->MySQLSelect("SELECT *,iUserId as iMemberId FROM ".$tableName." WHERE iUserId='".$iMemberId_VALUE."'"); 
                $userDetailsArr[$tableName.'_'.$iMemberId_VALUE] = $userData;
            }
            //Added By HJ On 24-06-2020 For Optimization register_user Table Query End
            //$AppData = get_value($tableName, 'iAppVersion,eDeviceType,iGcmRegId,vLang', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            $iGcmRegId = $AppData[0]['iGcmRegId'];
            $vLangCode = $AppData[0]['vLang'];
            $tSessionId = $AppData[0]['tSessionId'];
            //echo $tSessionId;die;
            /* For PubNub Setting Finished */
            if ($vLangCode == "" || $vLangCode == NULL) {
                //Added By HJ On 24-06-2020 For Optimize language_master Table Query Start
                if (!empty($vSystemDefaultLangCode)) {
                    $vLangCode = $vSystemDefaultLangCode;
                } else {
                    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                }
                //Added By HJ On 24-06-2020 For Optimize language_master Table Query End
            }
            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
            $driverArrivedLblValue = $languageLabelsArr['LBL_DRIVER_ARRIVED_NOTIMSG'];
            $driverArrivedLblValue_delivery = $languageLabelsArr['LBL_CARRIER_ARRIVED_NOTIMSG'];
            $driverArrivedLblValue_ride = $languageLabelsArr['LBL_DRIVER_ARRIVED_TXT'];
            //$tSessionId = get_value("register_user", 'tSessionId', "iUserId", $result[0]['iUserId'], '', 'true');
            $message = "";
            $message_arr['Message'] = "DriverArrived";
            $message_arr['MsgType'] = "DriverArrived";
            $message_arr['iDriverId'] = $iDriverId;
            $message_arr['driverName'] = $result[0]['driverName'];
            $message_arr['tSessionId'] = $tSessionId;
            $message_arr['vRideNo'] = $result[0]['vRideNo'];
            $message_arr['iTripId'] = $result[0]['iTripId'];
            $message_arr['eType'] = $result[0]['eType'];
            $eType = $result[0]['eType'];
            $alertMsg = $driverArrivedLblValue_ride;
            if ($eType == "UberX") {
                if ($result[0]['eServiceLocation'] == "Driver") {
                    $driverArrivedLblValue = $languageLabelsArr['LBL_USER_ARRIVED_PROVIDER_LOC_NOTIMSG'];
                }
                $alertMsg = $languageLabelsArr['LBL_PROVIDER'] . ' ' . $result[0]['driverName'] . ' ' . $driverArrivedLblValue . $result[0]['vRideNo'];
            } else if ($eType == "Deliver") {
                $alertMsg = $languageLabelsArr['LBL_CARRIER'] . ' ' . $result[0]['driverName'] . ' ' . $driverArrivedLblValue_delivery;
            } else if ($eType == "Multi-Delivery") {
                $alertMsg = $languageLabelsArr['LBL_CARRIER'] . ' ' . $result[0]['driverName'] . ' ' . $driverArrivedLblValue . $result[0]['vRideNo'];
            }
            $message_arr['vTitle'] = $alertMsg;
            $message_arr['eSystem'] = "";
            /* move down to up */
            if (isset($result[0]['iTripId']) && $result[0]['iTripId'] != "") {
                if (strtoupper(PACKAGE_TYPE) == "SHARK") {
                    configureTripForArriveStatus();
                } else {
                    $where1 = " iTripId = '" . $result[0]['iTripId'] . "'";
                    $DataUpdate_trips_arrive['tDriverArrivedDate'] = date('Y-m-d H:i:s');
                    $obj->MySQLQueryPerform("trips", $DataUpdate_trips_arrive, 'update', $where1);
                }
                $tripUserId = $result[0]['iUserId'];
                $where_user = " iUserId = '$tripUserId'";
                $Data_update_passenger['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
                $Data_update_passenger['vVerificationCountEmergency'] = 0;
                $User_Update_id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where_user);
            }
            $data['DLatitude'] =$data['DLongitude']=$data['DAddress']= "0";
            if ($result[0]['tEndLat'] != '' && $result[0]['tEndLong'] != '') {
                $data['DLatitude'] = $result[0]['tEndLat'];
                $data['DLongitude'] = $result[0]['tEndLong'];
                $data['DAddress'] = $result[0]['tDaddress'];
            }
            $data['eTollSkipped'] = $result[0]['eTollSkipped'];
            $data['eBeforeUpload'] = $result[0]['eBeforeUpload'];
            $data['eAfterUpload'] = $result[0]['eAfterUpload'];
            $returnArr['message'] = $data;
            $deviceTokens_arr_ios = $registation_ids_new = array();
            $message = json_encode($message_arr);
            $alertSendAllowed = true;
            if ($alertSendAllowed == true) {
                if ($eDeviceType == "Android") {
                    array_push($registation_ids_new, $iGcmRegId);
                    $Rmessage = array("message" => $message);
                    send_notification($registation_ids_new, $Rmessage, 0);
                }else if ($eDeviceType != "Android") {
                    array_push($deviceTokens_arr_ios, $iGcmRegId);
                    if ($message != "") {
                        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }
                }
            }
            ####  PUBNUB ##########
            $channelName = "PASSENGER_" . $result[0]['iUserId'];
            //$tSessionId = get_value("register_user", 'tSessionId', "iUserId", $result[0]['iUserId'], '', 'true');
            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
            if (count($deviceTokens_arr_ios) > 0) {
                sleep(5);
            }
            publishEventMessage($channelName, $message_pub);
            //}
            ####  PUBNUB ##########
            #####################Add Status Message#########################
            $DataTripMessages['tMessage'] = $message;
            $DataTripMessages['iDriverId'] = $iDriverId;
            $DataTripMessages['iTripId'] = $result[0]['iTripId'];
            $DataTripMessages['iUserId'] = $result[0]['iUserId'];
            $DataTripMessages['eFromUserType'] = "Driver";
            $DataTripMessages['eToUserType'] = "Passenger";
            $DataTripMessages['eReceived'] = "No";
            $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
            $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
            ################################################################
            
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            // echo "UpdateFailed";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    //print_r($returnArr);die;
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "StartTrip") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $TripID = isset($_REQUEST["TripID"]) ? $_REQUEST["TripID"] : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $iTripDeliveryLocationId = isset($_REQUEST["iTripDeliveryLocationId"]) ? $_REQUEST["iTripDeliveryLocationId"] : '';
    if ($image_object) {
        ExifCleaning::adjustImageOrientation($image_object);
    }
    $startDateOfTrip = @date("Y-m-d H:i:s");
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $tripstartlabel = $languageLabelsArr['LBL_DRIVER_START_NOTIMSG'];
    $tripstartlabel_ride = $languageLabelsArr['LBL_START_TRIP_DIALOG_TXT'];
    $tripstartlabel_delivery = $languageLabelsArr['LBL_START_DELIVERY_DIALOG_TXT'];
    $message = "TripStarted";
    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
    $result22 = $obj->MySQLSelect($sql);
    //$verificationCode = rand(10000000, 99999999);
    $verificationCode = generateCommonRandom();
    $TripData = get_value('trips', 'eType,fVisitFee,eFareType,iRunningTripDeliveryNo,tStartDate', 'iTripId', $TripID);
    $eType = $TripData[0]['eType'];
    $tStartDate = $TripData[0]['tStartDate'];
    $fVisitFee = $TripData[0]['fVisitFee'];
    $eFareType = $TripData[0]['eFareType'];
    $iRunningTripDeliveryNo = $TripData[0]['iRunningTripDeliveryNo'];
    $iRunningTripDeliveryNo = $iRunningTripDeliveryNo + 1;
    if ($DELIVERY_VERIFICATION_METHOD != "Code" && $eType == "Deliver") {
        $message_verificationCode = "";
    }
    else {
        //$verificationCode = rand ( 10000000 , 99999999 );
        $message_verificationCode = $verificationCode;
    }
    if ($eType == "UberX") {
        $alertMsg = $languageLabelsArr['LBL_PROVIDER'] . ' ' . $result22[0]['driverName'] . ' ' . $tripstartlabel . $result22[0]['vRideNo'];
    }
    elseif ($eType == "Ride") {
        $alertMsg = $tripstartlabel_ride;
    }
    else {
        $alertMsg = $tripstartlabel_delivery;
    }
    $sql = "SELECT iGcmRegId,eDeviceType,iTripId,tLocationUpdateDate,eLogout,tSessionId FROM register_user WHERE iUserId='$iUserId'";
    $result = $obj->MySQLSelect($sql);
    $message_arr = array();
    $message_arr['Message'] = $message;
    $message_arr['iDriverId'] = $iDriverId;
    $message_arr['iTripId'] = $TripID;
    $message_arr['driverName'] = $result22[0]['driverName'];
    $message_arr['tSessionId'] = $result[0]['tSessionId'];
    $message_arr['vRideNo'] = $result22[0]['vRideNo'];
    if ($eType == "Deliver") {
        $message_arr['VerificationCode'] = strval($verificationCode);
    } else if ($eType == "Multi-Delivery") {
        $msgarr = gettexttripdeliverydetails($TripID, "Passenger", "Starttrip");
        $message_arr['iTripDeliveryLocationId'] = $msgarr['iTripDeliveryLocationId'];
        $message_arr['vTitle'] = $msgarr['Delivery_Start_Txt'];
        $alertMsg = $msgarr['Delivery_Start_Txt'];
        $message_arr['VerificationCode'] = ($DELIVERY_VERIFICATION_METHOD == "Code") ? strval($verificationCode) : "";
    } else {
        $message_arr['VerificationCode'] = "";
    }
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['eType'] = $eType;
    $message_arr['eSystem'] = "";
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
    #####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $iDriverId;
    $DataTripMessages['iTripId'] = $TripID;
    $DataTripMessages['iUserId'] = $iUserId;
    $DataTripMessages['eFromUserType'] = "Driver";
    $DataTripMessages['eToUserType'] = "Passenger";
    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
    ################################################################
    //Update passenger Table
    $where = " iUserId = '$iUserId'";
    if ($APP_TYPE == "Ride-Delivery-UberX" && $eType != "UberX" && $eType != "Multi-Delivery") { // $eType != "Multi-Delivery" Condition Added By HJ On 26-12-2019 As Per Discuss with KS Sir For Solved Sheet Bug - 687
        $Data_update_passenger['vTripStatus'] = 'On Going Trip';
    }
    else if ($eType != "Multi-Delivery" && $eType != "UberX") {
        $Data_update_passenger['vTripStatus'] = 'On Going Trip';
    }
    $Data_update_passenger['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
    $Data_update_passenger['vVerificationCountEmergency'] = 0;
    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
    //Update Driver Table
    $where = " iDriverId = '$iDriverId'";
    $Data_update_driver['vTripStatus'] = 'On Going Trip';
    $Data_update_driver['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
    $Data_update_driver['vVerificationCountEmergency'] = 0;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    $TRIP_CONTINUE = "No";
    if ($eType == "Multi-Delivery") {
        $sql = "SELECT count(iTripDeliveryLocationId) AS TotalTripDelivery FROM `trips_delivery_locations` WHERE iActive='Finished'  AND iTripId='" . $TripID . "'";
        $TotalTripDeliveryData = $obj->MySQLSelect($sql);
        $TotalTripDelivery = $TotalTripDeliveryData[0]['TotalTripDelivery'];
        if ($TotalTripDelivery > 0) {
            $TRIP_CONTINUE = "Yes";
        }
    }
    //update insurance log
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        if ($TRIP_CONTINUE == "No") {
            $details_arr['iTripId'] = $TripID;
            $details_arr['LatLngArr']['vLatitude'] = $vLatitude;
            $details_arr['LatLngArr']['vLongitude'] = $vLongitude;
            // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
            update_driver_insurance_status($iDriverId, "Trip", $details_arr, "StartTrip");
        }
    }
    //update insurance log
    # Check Active trip delivery and update Status of Trip Delivery #
    if ($eType == "Multi-Delivery") {
        $sqldeliverydata = "SELECT * FROM `trips_delivery_locations` WHERE iActive='Active'  AND iTripId='" . $TripID . "' ORDER BY iTripDeliveryLocationId ASC LIMIT 0,1";
        $TripDeliveryData = $obj->MySQLSelect($sqldeliverydata);
        if (count($TripDeliveryData) > 0) {
            $iTripDeliveryLocationId = $TripDeliveryData[0]['iTripDeliveryLocationId'];
            $Data_update_trip_delivery['iActive'] = 'On Going Trip';
            $Data_update_trip_delivery['tStartTime'] = $startDateOfTrip;
            $Data_update_trip_delivery['vDeliveryConfirmCode'] = $verificationCode;
            $where_trip_delivery = " iTripDeliveryLocationId = '$iTripDeliveryLocationId'";
            $Data_update_trip_delivery_id = $obj->MySQLQueryPerform("trips_delivery_locations", $Data_update_trip_delivery, 'update', $where_trip_delivery);
        }
    }
    # Check Active trip delivery and update Status of Trip Delivery #
    // $Curr_TripID=$result[0]['iTripId'];
    $where = " iTripId = '$TripID'";
    $Data_update_trips['iActive'] = 'On Going Trip';
    if ($tStartDate == "0000-00-00 00:00:00") {
        $Data_update_trips['tStartDate'] = $startDateOfTrip;
    }
    $Data_update_trips['iRunningTripDeliveryNo'] = $iRunningTripDeliveryNo;
    /* Code for Upload StartImage of trip Start */
    if ($image_name != "") {
        //$Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path']."/".$TripID."/";
        $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path'];
        if (!is_dir($Photo_Gallery_folder)) mkdir($Photo_Gallery_folder, 0777);
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
        $vImageName = $vFile[0];
        $Data_update_trips['vBeforeImage'] = $vImageName;
    }
    /* Code for Upload StartImage of trip End */
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['fVisitFee'] = $fVisitFee;
        /* For PubNub Setting */
        $tableName = "register_user";
        $iMemberId_VALUE = $iUserId;
        $iMemberId_KEY = "iUserId";
        $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        /* For PubNub Setting Finished */
        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
        $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
        //$alertSendAllowed = false;
        $alertSendAllowed = true;
        //$message = $alertMsg;
        $tLocUpdateDate = date("Y-m-d H:i:s", strtotime($result[0]['tLocationUpdateDate']));
        if ($tLocUpdateDate < $compare_date) {
            $alertSendAllowed = true;
        }
        $alertSendAllowed = true;
        if ($result[0]['eLogout'] == "Yes") {
            $alertSendAllowed = false;
        }
        $deviceTokens_arr = array();
        if ($alertSendAllowed == true) {
            array_push($deviceTokens_arr, $result[0]['iGcmRegId']);
            if ($result[0]['eDeviceType'] == "Android") {
                $Rmessage = array(
                    "message" => $message
                );
                send_notification($deviceTokens_arr, $Rmessage, 0);
            }
            else {
                sendApplePushNotification(0, $deviceTokens_arr, $message, $alertMsg, 0);
            }
        }
        #######    Pubnub ######################
        $channelName = "PASSENGER_" . $iUserId;
        $tSessionId = $result[0]['tSessionId'];
        $message_arr['tSessionId'] = $tSessionId;
        $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
        if ($result[0]['eDeviceType'] == "Ios") {
            sleep(5);
        }
        publishEventMessage($channelName, $message_pub);
        ########    Pubnub ######################
        // Send SMS to receiver if trip type is delivery.
        if ($eType == "Deliver" || $eType == "Multi-Delivery") {
            $vPhoneCode = get_value('register_user', 'vPhoneCode', 'iUserId', $iUserId, '', 'true');
            $receiverMobile = get_value('trips', 'vReceiverMobile', 'iTripId', $TripID, '', 'true');
            $receiverMobile1 = "+" . $receiverMobile;
            $where_trip_update = " iTripId = '$TripID'";
            $data_delivery['vDeliveryConfirmCode'] = $verificationCode;
            $obj->MySQLQueryPerform("trips", $data_delivery, 'update', $where_trip_update);
            if ($eType == "Multi-Delivery") {
                $sql = "SELECT iTripDeliveryLocationId FROM `trips_delivery_locations` WHERE iTripId = '$TripID'";
                $Data_delivery_locations = $obj->MySQLSelect($sql);
                $sql = "select vValue from trip_delivery_fields where iDeliveryFieldId='3' and iTripId = '$TripID' and iTripDeliveryLocationId='$iTripDeliveryLocationId'";
                $Data_delivery_values = $obj->MySQLSelect($sql);
                $receiverMobile = $Data_delivery_values[0]['vValue'];
                //$receiverMobile1 = "+".$vPhoneCode.$Data_delivery_values[0]['vValue'];
                $receiverMobile1 = "+" . $Data_delivery_values[0]['vValue'];
            }
            $message_deliver = deliverySmsToReceiver($TripID, $iTripDeliveryLocationId);
            /* $result = sendEmeSms($receiverMobile1, $message_deliver);
            
              if ($result == 0) {
            
              //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
            
              $receiverMobile3 = "+" . $vPhoneCode . $receiverMobile;
            
              $result1 = sendEmeSms($receiverMobile3, $message_deliver);
            
              if ($result1 == 0) {
            
              $isdCode = $SITE_ISD_CODE;
            
              $receiverMobile = "+" . $isdCode . $receiverMobile;
            
              sendEmeSms($receiverMobile, $message_deliver);
            
              }
            
              } */
            //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
            $PhoneCode = $passengerData[0]['vPhoneCode'];
            $result = $generalobj->sendSystemSms($receiverMobile1, $PhoneCode, $message_deliver);
            //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
            $returnArr['message'] = $verificationCode;
            $returnArr['SITE_TYPE'] = SITE_TYPE;
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $returnArr['iTripTimeId'] = '';
    if ($eFareType == 'Hourly') {
        $dTime = date('Y-m-d H:i:s');
        $Data_update['dResumeTime'] = $dTime;
        $Data_update['iTripId'] = $TripID;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'insert');
        $returnArr['iTripTimeId'] = $id;
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "ProcessEndTrip") {
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    //echo $tripId;die;
    $userId = isset($_REQUEST["PassengerId"]) ? $_REQUEST["PassengerId"] : '';
    $driverId = isset($_REQUEST["DriverId"]) ? $_REQUEST["DriverId"] : '';
    $latitudes = isset($_REQUEST["latList"]) ? $_REQUEST["latList"] : '';
    $longitudes = isset($_REQUEST["lonList"]) ? $_REQUEST["lonList"] : '';
    $tripDistance = isset($_REQUEST["TripDistance"]) ? $_REQUEST["TripDistance"] : '0';
    $dAddress = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
    // $currentCity= isset($_REQUEST["currentCity"]) ? $_REQUEST["currentCity"] : '';
    $destination_lat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
    $destination_lon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
    $isTripCanceled = isset($_REQUEST["isTripCanceled"]) ? $_REQUEST["isTripCanceled"] : '';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $fMaterialFee = isset($_REQUEST["fMaterialFee"]) ? $_REQUEST["fMaterialFee"] : '';
    $fMiscFee = isset($_REQUEST["fMiscFee"]) ? $_REQUEST["fMiscFee"] : '';
    $fDriverDiscount = isset($_REQUEST["fDriverDiscount"]) ? $_REQUEST["fDriverDiscount"] : '';
    $iTripTimeId = isset($_REQUEST["iTripTimeId"]) ? $_REQUEST["iTripTimeId"] : '';
    $iCancelReasonId = isset($_REQUEST["iCancelReasonId"]) ? $_REQUEST["iCancelReasonId"] : '';
    //Added By HJ On 22-06-2020 For Optimize register_driver Table Query Start
    if(isset($userDetailsArr['register_driver_'.$driverId])){
        $vCurrencyDriver = $userDetailsArr['register_driver_'.$driverId][0]['vCurrencyDriver'];
    }else{
        $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    }
    if(isset($currencyAssociateArr[$vCurrencyDriver])){
        $DriverRation = $currencyAssociateArr[$vCurrencyDriver]['Ratio'];
        $currencySymbolDriver = $currencyAssociateArr[$vCurrencyDriver]['vSymbol'];
    }else{
        $DriverRation = $vSystemDefaultCurrencyRatio;
        $currencySymbolDriver = $vSystemDefaultCurrencySymbol;
        $getCurrencyData = $obj->MySQLSelect("SELECT Ratio,vSymbol FROM currency WHERE vName='".$vCurrencyDriver."'");
        if(count($getCurrencyData) > 0){
            $DriverRation = $getCurrencyData[0]['Ratio'];
            $currencySymbolDriver = $getCurrencyData[0]['vSymbol'];
        }
    }
    //Added By HJ On 22-06-2020 For Optimize register_driver Table Query End
    if ($image_object) {
        ExifCleaning::adjustImageOrientation($image_object);
    }
    //$exifDATA = exif_read_data($image_object, 0, true);
    ## Check Service End For UberX Trip ###
    //Added By HJ On 22-06-2020 For Optimize trips Table Query Start
    if(isset($tripDetailsArr['trips_'.$tripId])){
        $UberXTripData = $tripDetailsArr['trips_'.$tripId];
    } else {
        $UberXTripData = $obj->MySQLSelect("SELECT *,fRatio_" . $vCurrencyDriver . " as fRatioDriver FROM trips WHERE iTripId='".$tripId."'");
        $tripDetailsArr['trips_'.$tripId] = $UberXTripData;
    }
    //Added By HJ On 22-06-2020 For Optimize trips Table Query End
    //$UberXTripData = get_value('trips', 'eServiceEnd,eType,tEndDate,vCancelReason,vCancelComment,eCancelled,eCancelledBy,tEndLat,tEndLong,tDaddress,vAfterImage,eFareType,iCancelReasonId,ePoolRide,iPersonSize,iVehicleTypeId,fPoolDuration,fPoolDistance,eServiceLocation,tVehicleTypeData,tStartLat,tStartLong', 'iTripId', $tripId);
    $eType = $UberXTripData[0]['eType'];
    $eServiceEnd = $UberXTripData[0]['eServiceEnd'];
    $UberXtripEndDate = $UberXTripData[0]['tEndDate'];
    $ePoolRide = $UberXTripData[0]['ePoolRide'];
    $iPersonSize = $UberXTripData[0]['iPersonSize'];
    $iVehicleTypeId = $UberXTripData[0]['iVehicleTypeId'];
    //Added By HJ On 22-07-2019 For Get Pickup Airport Sircharge Value Start
    $tStartLat = $UberXTripData[0]['tStartLat'];
    $tStartLong = $UberXTripData[0]['tStartLong'];
    //Added By HJ On 22-07-2019 For Get Pickup Airport Sircharge Value End
    /* ============STARTSTOPPOINT============= */
    /* stop over point drop and check other stop */
    if (checkStopOverPointModule() && $eType == 'Ride') {
        addActualStopOverPoint();
        dropStopOverPoint();
    }
    /* ============ENDSTOPPOINT============= */
    // add airport surge //
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes' && $eType != "UberX") {
            $Data_update_trips = $pickuplocationarr = array();
            $dropofflocationarr = array($destination_lat,$destination_lon);
            $pickuplocationarr = array($tStartLat,$tStartLong); // Added By HJ On 22-07-2019 As Per Discuss With KS
            $GetVehicleIdfromGeoLocation = CheckSurgeAirportFromGeoLocation($pickuplocationarr, $dropofflocationarr, $iVehicleTypeId);
            //print_r($GetVehicleIdfromGeoLocation);die;
            $fdropoffsurchargefare = $fpickupsurchargefare = 0;
            if (isset($GetVehicleIdfromGeoLocation['fdropoffsurchargefare']) && $GetVehicleIdfromGeoLocation['fdropoffsurchargefare'] > 0) {
                $fdropoffsurchargefare = $GetVehicleIdfromGeoLocation['fdropoffsurchargefare'];
            }
            $Data_update_trips['fAirportDropoffSurge'] = $fdropoffsurchargefare;
            // Added By HJ On 22-07-2019 As Per Discuss With KS Start
            if (isset($GetVehicleIdfromGeoLocation['fpickupsurchargefare']) && $GetVehicleIdfromGeoLocation['fpickupsurchargefare'] > 0) {
                $fpickupsurchargefare = $GetVehicleIdfromGeoLocation['fpickupsurchargefare'];
            }
            $Data_update_trips['fAirportPickupSurge'] = $fpickupsurchargefare;
            //print_r($Data_update_trips);die;
            // Added By HJ On 22-07-2019 As Per Discuss With KS End
            $where = " iTripId='" . $tripId . "'";
            $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);
        }
    }
    // end airport surge //
    if ($eServiceEnd == "No" && $eType == "UberX") {
        if ($latitudes != '' && $longitudes != '') {
            processTripsLocations($tripId, $latitudes, $longitudes);
        }
        $where = " iTripId='" . $tripId . "'";
        if ($isTripCanceled == "true") {
            $Data_Trip_UberX['vCancelReason'] = $driverReason;
            $Data_Trip_UberX['vCancelComment'] = $driverComment;
            $Data_Trip_UberX['eCancelled'] = "Yes";
            $Data_Trip_UberX['eCancelledBy'] = "Driver";
            $Data_Trip_UberX['iCancelReasonId'] = $iCancelReasonId;
        }
        $Data_Trip_UberX['eServiceEnd'] = "Yes";
        $Data_Trip_UberX['tEndDate'] = @date("Y-m-d H:i:s");
        $Data_Trip_UberX['tEndLat'] = $destination_lat;
        $Data_Trip_UberX['tEndLong'] = $destination_lon;
        $Data_Trip_UberX['tDaddress'] = $dAddress;
        /* Code for Upload AfterImage of trip Start */
        if ($image_name != "") {
            //$Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path']."/".$TripID."/";
            $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path'];
            if (!is_dir($Photo_Gallery_folder)) mkdir($Photo_Gallery_folder, 0777);
            $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
            $vImageName = $vFile[0];
            $Data_Trip_UberX['vAfterImage'] = $vImageName;
        }
        /* Code for Upload AfterImage of trip End */
        $obj->MySQLQueryPerform("trips", $Data_Trip_UberX, 'update', $where);
        $eFareType = $UberXTripData[0]['eFareType'];
        if ($eFareType == "Hourly") {
            if ($iTripTimeId == "" || $iTripTimeId == NULL) {
                $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$tripId' ORDER BY iTripTimeId DESC LIMIT 0,1";
                $db_tripTimes = $obj->MySQLSelect($sql22);
                $dPauseTime = $db_tripTimes[0]['dPauseTime'];
                if ($dPauseTime == "0000-00-00 00:00:00") {
                    $iTripTimeId = $db_tripTimes[0]['iTripTimeId'];
                }
            }
            if ($iTripTimeId != "") {
                $where_triptime = " iTripTimeId = '$iTripTimeId'";
                $Data_update['dPauseTime'] = @date("Y-m-d H:i:s");
                $Data_update['iTripId'] = $tripId;
                $Trip_Time_id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'update', $where_triptime);
            }
        }
        $returnArr['Action'] = "1";
        setDataResponse($returnArr);
    }
    if ($eServiceEnd == "Yes" && $eType == "UberX") {
        $dAddress = $UberXTripData[0]['tDaddress'];
        $driverReason = $UberXTripData[0]['vCancelReason'];
        $driverComment = $UberXTripData[0]['vCancelComment'];
        $destination_lat = $UberXTripData[0]['tEndLat'];
        $destination_lon = $UberXTripData[0]['tEndLong'];
        $dAddress = $UberXTripData[0]['tDaddress'];
        $image_name = $UberXTripData[0]['vAfterImage'];
        $eCancelled = $UberXTripData[0]['eCancelled'];
        $iCancelReasonId = $UberXTripData[0]['iCancelReasonId'];
        $isTripCanceled = "";
        if ($eCancelled == "Yes") {
            $isTripCanceled = "true";
        }
    }
    ## Check Service End For UberX Trip ###
    $fMaterialFee = $generalobj->setTwoDecimalPoint($fMaterialFee / $DriverRation);
    $fMiscFee = $generalobj->setTwoDecimalPoint($fMiscFee / $DriverRation);
    $fDriverDiscount = $generalobj->setTwoDecimalPoint($fDriverDiscount / $DriverRation);
    //$eType = get_value('trips', 'eType', 'iTripId', $tripId, '', 'true');
    //$eFareGenerated = get_value('trips', 'eFareGenerated', 'iTripId', $tripId, '', 'true');
    if (count($UberXTripData) > 0) {
        $eType = $UberXTripData[0]['eType'];
        $eFareGenerated = $UberXTripData[0]['eFareGenerated'];
    }
    $TRIP_CONTINUE = "No";
    if ($eType == "Multi-Delivery") {
        // Added By HJ On 23-06-2020 For Optimize trips_delivery_locations Table Query Start
        $totalTripDeliveryCount =0;
        if(isset($tripDetailsArr["trips_delivery_locations_".$tripId])){
            $sqldeliverydata = $tripDetailsArr["trips_delivery_locations_".$tripId];
            //$totalTripDeliveryCount = count($sqldeliverydata);
        }else{
            $sqldeliverydata = $obj->MySQLSelect("SELECT * FROM `trips_delivery_locations` WHERE iTripId='" . $tripId . "' ORDER BY  iTripDeliveryLocationId ASC");
            $tripDetailsArr["trips_delivery_locations_".$tripId] = $sqldeliverydata;
            //$totalTripDeliveryCount = count($sqldeliverydata);
        }
        $TripDeliveryData =$tripDeliveryFieldArr= array();
        //echo "<pre>";print_r($sqldeliverydata);die;
        if(count($sqldeliverydata) > 0){
            $dataFound = 0;
            for($d=0;$d<count($sqldeliverydata);$d++){
                $iActiveStatus = $sqldeliverydata[$d]['iActive'];
                if((strtoupper($iActiveStatus) == "ACTIVE" || strtoupper($iActiveStatus) == "ON GOING TRIP") && $dataFound == 0){
                    $totalTripDeliveryCount +=1;
                }
                if((strtoupper($iActiveStatus) == "ON GOING TRIP") && $dataFound == 0){
                    $dataFound = 1;
                    $TripDeliveryData[] = $sqldeliverydata[$d];
                }
            }
        }
        // Added By HJ On 23-06-2020 For Optimize trips_delivery_locations Table Query End
        //$sqldeliverydata = "SELECT * FROM `trips_delivery_locations` WHERE iActive='On Going Trip' AND iTripId='" . $tripId . "' ORDER BY iTripDeliveryLocationId ASC LIMIT 0,1";
        //$TripDeliveryData = $obj->MySQLSelect($sqldeliverydata);
        //echo "<pre>";print_r($TripDeliveryData);die;
        if (count($TripDeliveryData) > 0) {
            $iTripDeliveryLocationId = $TripDeliveryData[0]['iTripDeliveryLocationId'];
            $Data_update_trip_delivery['iActive'] = ($isTripCanceled == 'true') ? 'Canceled' : 'Finished';
            $Data_update_trip_delivery['tEndTime'] = @date("Y-m-d H:i:s");
            $where_trip_delivery = " iTripDeliveryLocationId = '$iTripDeliveryLocationId'";
            $Data_update_trip_delivery_id = $obj->MySQLQueryPerform("trips_delivery_locations", $Data_update_trip_delivery, 'update', $where_trip_delivery);
        }
        //$TotalTripDeliveryData = $obj->MySQLSelect("SELECT count(iTripDeliveryLocationId) AS TotalTripDelivery FROM `trips_delivery_locations` WHERE iActive IN ('On Going Trip','Active')  AND iTripId='" . $tripId . "'");
        //$TotalTripDelivery = $TotalTripDeliveryData[0]['TotalTripDelivery'];
        $TotalTripDelivery = $totalTripDeliveryCount;
        if ($TotalTripDelivery > 0) {
            $TRIP_CONTINUE = "Yes";
        }
    }
    # Check Ongoing trip delivery and update Status of Trip Delivery #
    $Active = "On Going Trip";
    if ($TRIP_CONTINUE == "No") {
        $Active = "Finished";
    }
    if(isset($userDetailsArr['register_user_'.$userId])){
        $getUserData = $userDetailsArr['register_user_'.$userId];
    }else{
        $getUserData = $obj->MySQLSelect("SELECT * FROM register_user WHERE iUserId='" . $userId . "'");
        $userDetailsArr['register_user_'.$userId] = $getUserData;
    }
    //$vLangCode = get_value('register_user', 'vLang', 'iUserId', $userId, '', 'true');
    $tSessionId = $vLangCode = "";
    if (count($getUserData) > 0) {
        $vLangCode = $getUserData[0]['vLang'];
        $tSessionId = $getUserData[0]['tSessionId'];
    }
    if ($vLangCode == "" || $vLangCode == NULL) {
        //Added By HJ On 22-06-2020 For Optimize language_master Table Query Start
        if (!empty($vSystemDefaultLangCode)) {
            $vLangCode = $vSystemDefaultLangCode;
        } else {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        //Added By HJ On 22-06-2020 For Optimize language_master Table Query End
    }
    //Added By HJ On 24-06-2020 For Optimize language label Table Query Start
    if(isset($languageLabelDataArr[$vLangCode])){
        $languageLabelsArr = $languageLabelDataArr[$vLangCode];
    }else{
        $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
        $languageLabelDataArr[$vLangCode] = $languageLabelsArr;
    }
    //Added By HJ On 24-06-2020 For Optimize language label Table Query End
    //$languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $tripcancelbydriver = $languageLabelsArr['LBL_TRIP_CANCEL_BY_DRIVER'];
    $tripfinish = $languageLabelsArr['LBL_DRIVER_END_NOTIMSG'];
    $tripfinish_ride = $languageLabelsArr['LBL_TRIP_FINISH'];
    $tripfinish_delivery = $languageLabelsArr['LBL_DELIVERY_FINISH'];
    $message_arr = array();
    $message_arr['ShowTripFare'] = "true";
    $message = "TripEnd";
    if ($isTripCanceled == "true") {
        $message = "TripCancelledByDriver";
    }
    if ($iCancelReasonId != "") {
        $driverReason = get_value('cancel_reason', "vTitle_" . $vLangCode, 'iCancelReasonId', $iCancelReasonId, '', 'true');
    }
    if(isset($userDetailsArr['register_driver_'.$driverId])){
        $result22 = $userDetailsArr['register_driver_'.$driverId];
    }else{
        $result22 = $obj->MySQLSelect("SELECT *,iDriverId as iMemberId FROM register_driver iDriverId='".$driverId."'");
    }
    if(count($result22) > 0){
        if(isset($tripDetailsArr['trips_'.$result22[0]['iTripId']])){
            $tripData = $tripDetailsArr['trips_'.$result22[0]['iTripId']];
        }else{
            $tripData = $obj->MySQLSelect("SELECT * FROM trips iTripId='".$result22[0]['iTripId']."'");
        }
        if(count($tripData) > 0){
            $result22[0]['vRideNo']= $tripData[0]['vRideNo'];
            $result22[0]['driverName']= $result22[0]['vName']." ".$result22[0]['vLastName'];
        }
    }
    //$sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName,rd.vCode,rd.vLang,tr.vRideNo FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $driverId . "'";
    //$result22 = $obj->MySQLSelect($sql);
    if ($isTripCanceled == "true") {
        // $alertMsg = $tripcancelbydriver;
        if ($eType == "UberX") {
            $usercanceltriplabel = $result22[0]['driverName'] . ':' . $result22[0]['vRideNo'] . '-' . $languageLabelsArr['LBL_PREFIX_JOB_CANCEL_DRIVER'] . ' ' . $driverReason;
        } else if ($eType == "Ride") {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_TRIP_CANCEL_DRIVER'] . ' ' . $driverReason;
        } else {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_DELIVERY_CANCEL_DRIVER'] . ' ' . $driverReason;
        }
        $alertMsg = $usercanceltriplabel;
    } else {
        $alertMsg = $tripfinish_delivery;
        if ($eType == "UberX") {
            //$alertMsg = $tripfinish;
            $alertMsg = $result22[0]['driverName'] . " " . $tripfinish . " " . $result22[0]['vRideNo'];
        } else if ($eType == "Ride") {
            $alertMsg = $tripfinish_ride;
        }
    }
    $message_arr['Message'] = $message;
    $message_arr['iTripId'] = $tripId;
    $message_arr['iDriverId'] = $driverId;
    $message_arr['driverName'] = $result22[0]['driverName'];
    $message_arr['tSessionId'] = $tSessionId;
    $message_arr['vRideNo'] = $result22[0]['vRideNo'];
    if ($isTripCanceled == "true") {
        $message_arr['Reason'] = $driverReason;
        $message_arr['isTripStarted'] = "true";
    }
    if ($eType == "Multi-Delivery") {
        $msgarr = gettexttripdeliverydetails($tripId, "Passenger", "Processendtrip");
        $message_arr['iTripDeliveryLocationId'] = $msgarr['iTripDeliveryLocationId'];
        $message_arr['vTitle'] = $msgarr['Delivery_End_Txt'];
        $message_arr['Is_Last_Delivery'] = $msgarr['Is_Last_Delivery'];
        $alertMsg = $msgarr['Delivery_End_Txt'];
    } else {
        $message_arr['iTripDeliveryLocationId'] = 0;
        $message_arr['vTitle'] = $alertMsg;
        $message_arr['Is_Last_Delivery'] = "Yes";
    }
    // $message_arr['vTitle'] = $alertMsg;
    $message_arr['eType'] = $eType;
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE); 
    #####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $driverId;
    $DataTripMessages['iTripId'] = $tripId;
    $DataTripMessages['iUserId'] = $userId;
    $DataTripMessages['eFromUserType'] = "Driver";
    $DataTripMessages['eToUserType'] = "Passenger";
    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
    ################################################################
    $couponCode = trim($UberXTripData[0]['vCouponCode']);
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
    }
    if ($latitudes != '' && $longitudes != '' && $eType != "UberX") {
        processTripsLocations($tripId, $latitudes, $longitudes);
    }
    //Added By HJ On 22-06-2020 For Optimize trips Table Query Start
    if(isset($tripDetailsArr['trips_'.$tripId])){
        $trip_start_data_arr = $tripDetailsArr['trips_'.$tripId];
    } else {
        $trip_start_data_arr = $obj->MySQLSelect("SELECT *,fRatio_" . $vCurrencyDriver . " as fRatioDriver FROM trips WHERE iTripId='".$tripId."'");
        $tripDetailsArr['trips_'.$tripId] = $trip_start_data_arr;
    }
    //Added By HJ On 22-06-2020 For Optimize trips Table Query End
    //echo "<pre>";print_r($trip_start_data_arr);die;
    $tripDistance = calcluateTripDistance($tripId);
    
    $sourcePointLatitude = $trip_start_data_arr[0]['tStartLat'];
    $sourcePointLongitude = $trip_start_data_arr[0]['tStartLong'];
    $startDate = $trip_start_data_arr[0]['tStartDate'];
    $tDriverArrivedDate = $trip_start_data_arr[0]['tDriverArrivedDate'];
    $waiting_time_diff = strtotime($startDate) - strtotime($tDriverArrivedDate);
    $waitingTime = floor($waiting_time_diff / 60);
    $vehicleTypeID = $trip_start_data_arr[0]['iVehicleTypeId'];
    $eFareType = $trip_start_data_arr[0]['eFareType'];
    $eType = $trip_start_data_arr[0]['eType'];
    $eFlatTrip = $trip_start_data_arr[0]['eFlatTrip'];
    $fFlatTripPrice = $trip_start_data_arr[0]['fFlatTripPrice'];
    $eHailTrip = $trip_start_data_arr[0]['eHailTrip'];
    //$endDateOfTrip=@date("Y-m-d H:i:s");
    $endDateOfTrip = $trip_start_data_arr[0]['tEndDate'];
    if ($endDateOfTrip == "0000-00-00 00:00:00" || $eType == "Multi-Delivery") {
        $endDateOfTrip = @date("Y-m-d H:i:s");
    }
    $totalHoldTimeInMinutes_trip = 0;
    if ($eType == "UberX") {
        $endDateOfTrip = $UberXtripEndDate;
    }
    if ($TRIP_CONTINUE == "No") {
        //update insurance log
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            $details_arr['iTripId'] = $tripId;
            $details_arr['LatLngArr']['vLatitude'] = $destination_lat;
            $details_arr['LatLngArr']['vLongitude'] = $destination_lon;
            // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
            update_driver_insurance_status($driverId, "Trip", $details_arr, "ProcessEndTrip");
        }
        //update insurance log
    }
    /* --------Added By HJ ON 28-12-2018 for hold time calculatation Start ------ */
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        if ($eType == "Ride" && $ENABLE_INTRANSIT_SHOPPING_SYSTEM == "Yes") {
            $totalHoldTimeInMinutes_trip = InTransitMinutes($tripId);
        }
    }
    /* --------Added By HJ ON 28-12-2018 for hold time calculatation End ------ */
    if ($eFareType == 'Hourly') {
        $db_tripTimes = $obj->MySQLSelect("SELECT * FROM `trip_times` WHERE iTripId='".$tripId."'");
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
    if ($totalTimeInMinutes_trip <= 1) {
        $FinalDistance = $tripDistance;
        $FGDTime = $FGDDistance = 0;
    } else {
        $FinalDistanceArr = checkDistanceWithGoogleDirections($tripDistance, $sourcePointLatitude, $sourcePointLongitude, $destination_lat, $destination_lon, "0", "", true);
        $FinalDistance = $FinalDistanceArr['Distance'];
        $FGDTime = $FinalDistanceArr['Time'];
        $FGDDistance = $FinalDistanceArr['GDistance'];
    }
    $tripDistance = $FinalDistance;
    $where = " iTripId = '" . $tripId . "'";
    if ($eFareGenerated == "No") {
        if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes") {
            $totalTimeInMinutes_trip = $UberXTripData[0]['fPoolDuration'];
            $tripDistance = $UberXTripData[0]['fPoolDistance'];
        }
        $personData = array();
        $personData['iPersonSize'] = $iPersonSize;
        $personData['ePoolRide'] = $ePoolRide;
        $personData['POOL_ENABLE'] = $POOL_ENABLE;
        $Fare_data = calculateFare($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $userId, 1, $startDate, $endDateOfTrip, $couponCode, $tripId, $fMaterialFee, $fMiscFee, $fDriverDiscount, $waitingTime, $totalHoldTimeInMinutes_trip, $personData);
        //echo "<pre>";print_r($Fare_data);die;
    }
    //echo "<pre>";print_r($Fare_data);die;
    $Data_update_trips['tEndDate'] = $endDateOfTrip;
    //added by SP for fly stations on 31-08-2019 start bc when fly type then no need to replace following add to the current location bc user can not land in between trip
    if (empty($trip_start_data_arr[0]['iFromStationId']) && empty($trip_start_data_arr[0]['iToStationId'])) {
        $Data_update_trips['tEndLat'] = $destination_lat;
        $Data_update_trips['tEndLong'] = $destination_lon;
        $Data_update_trips['tDaddress'] = $dAddress;
    }
    //added by SP for fly stations on 31-08-2019 end
    $Data_update_trips['iActive'] = $Active;
    if ($eFareGenerated == "No") {
        $Data_update_trips['iFare'] = $Fare_data['total_fare'];
        if(isset($userDetailsArr['register_user_'.$userId])){
            $userData = $userDetailsArr['register_user_'.$userId];
        }else{
            $userData = $obj->MySQLSelect("SELECT * FROM register_user WHERE iUserId='".$userId."'");
            $userDetailsArr['register_user_'.$userId] = $userData;
        }
        if(count($userData) > 0){
            $vCurrencyPassenger = $userData[0]['vCurrencyPassenger'];
            $currData[] = $currencyAssociateArr[$vCurrencyPassenger];
        }else{
            $currData = $obj->MySQLSelect("SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, cu.Ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE r9u.iUserId = '" . $userId . "'");
        }
        //Added By HJ On 22-06-2020 For Optimize register_user Table Query End
        $vCurrency = $currData[0]['vName'];
        if(isset($userDetailsArr['register_driver_'.$driverId])){
            $driverData = $userDetailsArr['register_driver_'.$driverId];
        }else{
            $driverData = $obj->MySQLSelect("SELECT * FROM register_driver WHERE iDriverId='".$driverId."'");
            $userDetailsArr['register_driver_'.$driverId] = $userData;
        }
        if(count($driverData) > 0){
            $vCurrencyDriver = $driverData[0]['vCurrencyDriver'];
            $currDatad[] = $currencyAssociateArr[$vCurrencyDriver];
        }else{
            $currDatad = $obj->MySQLSelect("SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $driverId . "'");
        }
        $vCurrencyd = $currDatad[0]['vName'];
        if (($trip_start_data_arr[0]['vTripPaymentMode'] == "Cash")) {
            if ($eHailTrip == 'Yes') {
                if ($currDatad[0]['eRoundingOffEnable'] == "Yes") {
                    //&& $trip_start_data_arr[0]['vCurrencyPassenger']==$trip_start_data_arr[0]['vCurrencyDriver']
                    $roundingOffTotal_fare_amountArr = getRoundingOffAmount($Fare_data['total_fare'] * $DriverRation, $vCurrencyd);
                    $Data_update_trips['vCurrencyPassenger'] = $trip_start_data_arr[0]['vCurrencyDriver'];
                    $roundingOffTotal_fare_amount = $roundingOffTotal_fare_amountArr['finalFareValue'];
                    $eRoundingType = "Substraction";
                    if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                        $eRoundingType = "Addition";
                    }
                    $fRoundingAmount = $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue']);
                    //$Data_update_trips['iFare'] = $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount / $priceRatio);
                    $Data_update_trips['fRoundingAmount'] = $fRoundingAmount;
                    $Data_update_trips['eRoundingType'] = $eRoundingType;
                }
            } else {
                if ($currData[0]['eRoundingOffEnable'] == "Yes") {
                    //&& $trip_start_data_arr[0]['vCurrencyPassenger']==$trip_start_data_arr[0]['vCurrencyDriver']
                    if(isset($currencyAssociateArr[$vCurrency])){
                        $userCurrencyRatio = $currencyAssociateArr[$vCurrency]['Ratio'];
                    }else{
                        $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $vCurrency, '', 'true');
                    }
                    $roundingOffTotal_fare_amountArr = getRoundingOffAmount($Fare_data['total_fare'] * $userCurrencyRatio, $vCurrency);
                    //$userCurrencyRatio = $currData[0]['Ratio'];
                    $roundingOffTotal_fare_amount = $roundingOffTotal_fare_amountArr['finalFareValue'];
                    $eRoundingType = "Substraction";
                    if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                        $eRoundingType = "Addition";
                    }
                    $fRoundingAmount = $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue']);
                    //$Data_update_trips['iFare'] = $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amount / $priceRatio);
                    $Data_update_trips['fRoundingAmount'] = $fRoundingAmount;
                    $Data_update_trips['eRoundingType'] = $eRoundingType;
                }
            }
        }
        //added by SP for rounding off currency wise on 26-8-2019 end
        /*         * ** System Payment Flow Method-2 - Changing payment mode to cash if method-2 >> no need to change for method -3. For method -3 this must be goes into outstanding amount. **** */
        if (($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == "Method-3") && $Fare_data['total_fare'] > 0) {
            $Data_update_trips['vTripPaymentMode'] = "Cash";
        }
        /*         * ** System Payment Flow Method-2 **** */
        // $Data_update_trips['tVehicleTypeFareData'] = $Fare_data['tVehicleTypeFareData'];
        $Data_update_trips['fDistance'] = $tripDistance;
        $Data_update_trips['fDuration'] = $totalTimeInMinutes_trip;
        $Data_update_trips['fPricePerMin'] = $Fare_data['fPricePerMin'];
        $Data_update_trips['fPricePerKM'] = $Fare_data['fPricePerKM'];
        $Data_update_trips['iBaseFare'] = $Fare_data['iBaseFare'];
        $Data_update_trips['fCommision'] = $Fare_data['fCommision'];
        $Data_update_trips['fDiscount'] = $Fare_data['fDiscount'];
        $Data_update_trips['vDiscount'] = $Fare_data['vDiscount'];
        $Data_update_trips['fMinFareDiff'] = $Fare_data['MinFareDiff'];
        $Data_update_trips['fSurgePriceDiff'] = $Fare_data['fSurgePriceDiff'];
        $Data_update_trips['fWalletDebit'] = $Fare_data['user_wallet_debit_amount'];
        $Data_update_trips['fTripGenerateFare'] = $Fare_data['fTripGenerateFare'];
        $Data_update_trips['fMaterialFee'] = $fMaterialFee;
        $Data_update_trips['fMiscFee'] = $fMiscFee;
        $Data_update_trips['fDriverDiscount'] = $fDriverDiscount;
        $Data_update_trips['fTax1'] = $Fare_data['fTax1'];
        $Data_update_trips['fTax2'] = $Fare_data['fTax2'];
        $Data_update_trips['fTripHoldPrice'] = $Fare_data['fTripHoldPrice']; // Added By HJ For Insert Intransit Amount On 28-12-2018
        $Data_update_trips['fTax1Percentage'] = $Fare_data['fTax1Percentage'];
        $Data_update_trips['fTax2Percentage'] = $Fare_data['fTax2Percentage'];
        $Data_update_trips['fOutStandingAmount'] = $Fare_data['fOutStandingAmount'];
        $Data_update_trips['fExtraPersonCharge'] = $Fare_data['fExtraPersonCharge']; //Added By HJ On 26-12-2018 For Insert Extra Charge Amount
        // added for airport
        $Data_update_trips['fAirportPickupSurgeAmount'] = $Fare_data['fAirportPickupSurgeAmount'];
        $Data_update_trips['fAirportDropoffSurgeAmount'] = $Fare_data['fAirportDropoffSurgeAmount'];
    }
    $Data_update_trips['fGDtime'] = $FGDTime;
    $Data_update_trips['fGDdistance'] = $FGDDistance;
    $Data_update_trips['fHotelCommision'] = $Fare_data['Commision_Fare_Hotel'];
    $Data_update_trips['fHotelBookingChargePercentage'] = $Fare_data['fHotelBookingChargePercentage'];
    $Data_update_trips['fWaitingFees'] = 0;
    if ($eHailTrip == "No") {
        $Data_update_trips['fWaitingFees'] = $Fare_data['fWaitingFees'];
    }
    if ($ePoolRide == "Yes" && $POOL_ENABLE == "Yes") {
        $Data_update_trips['fWaitingFees'] = 0;
    }
    if ($isTripCanceled == "true") {
        $Data_update_trips['vCancelReason'] = $driverReason;
        $Data_update_trips['vCancelComment'] = $driverComment;
        $Data_update_trips['eCancelled'] = "Yes";
        $Data_update_trips['eCancelledBy'] = "Driver";
    }
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        finalizeProcessEndTrip();
    }
    /* Code for Upload AfterImage of trip Start */
    if ($image_name != "") {
        //$Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path']."/".$TripID."/";
        if (!empty($image_object)) {
            $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path'];
            if (!is_dir($Photo_Gallery_folder)) mkdir($Photo_Gallery_folder, 0777);
            $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
            $vImageName = $vFile[0];
            $image_name = $vImageName;
        }
        $Data_update_trips['vAfterImage'] = $image_name;
    }
    /* Code for Upload AfterImage of trip End */
    $Data_update_trips['iCancelReasonId'] = $iCancelReasonId;
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);
    $trip_status = "On Going Trip";
    $vCallFromDriver = '';
    $trip_status_driver = "Arrived";
    if ($TRIP_CONTINUE == "No") {
        $trip_status = $trip_status_driver = "Not Active";
        $vCallFromDriver = 'Not Assigned';
    }
    // $trip_status    = "Not Active";
    $where = " iUserId = '$userId'";
    if ($APP_TYPE == "Ride-Delivery-UberX" && $eType != "UberX") {
        $Data_update_passenger['iTripId'] = $tripId;
        $Data_update_passenger['vTripStatus'] = $trip_status;
    }
    else if ($eType != "UberX") {
        $Data_update_passenger['iTripId'] = $tripId;
        $Data_update_passenger['vTripStatus'] = $trip_status;
    }
    $Data_update_passenger['vCallFromDriver'] = 'Not Assigned';
    $Data_update_passenger['fTripsOutStandingAmount'] = '0';
    $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
    $where = " iDriverId = '$driverId'";
    $Data_update_driver['iTripId'] = $tripId;
    $Data_update_driver['vTripStatus'] = $trip_status_driver;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    ## Update User Outstanding Amount ##
    //$updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $userId;
    //$obj->sql_query($updateQuery);
    //$updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes' WHERE iUserId = ".$iUserId;
    $outStandingSql = "";
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $outStandingSql = " AND eAuthoriseIdName='iTripId' AND iAuthoriseId='" . $tripId . "'";
    }
    /* Added By PM On 25-01-2020 For wallet credit to driver Start */
    if (checkAutoCreditDriverModule() && $vTripPaymentMode == "Card") {
        $updateQury = "UPDATE trip_outstanding_amount set eBillGenerated = 'Yes',vTripPaymentMode = '" . $vTripPaymentMode . "', vTripAdjusmentId = '" . $tripId . "' WHERE iUserId = '" . $userId . "' AND ePaidByPassenger = 'No' $outStandingSql";
    } else {
        $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes', eBillGenerated = 'Yes',vTripAdjusmentId = '" . $tripId . "' WHERE iUserId = '" . $userId . "' AND ePaidByPassenger = 'No' $outStandingSql";
        $obj->sql_query($updateQury);
    }
    /* Added By PM On 25-01-2020 For wallet credit to driver End */
    ## Update User Outstanding Amount ##
    if ($id > 0) {
        /* For PubNub Setting */
        $tableName = "register_user";
        $iMemberId_VALUE = $userId;
        $iMemberId_KEY = "iUserId";
        //Added By HJ On 22-06-2020 For Optimize register_user Table Query Start
        if(isset($userDetailsArr[$tableName."_".$iMemberId_VALUE])){
            $AppData = $userDetailsArr[$tableName."_".$iMemberId_VALUE];
        }else{
            $AppData = get_value($tableName, '*', $iMemberId_KEY, $iMemberId_VALUE);
            $userDetailsArr[$tableName."_".$iMemberId_VALUE] = $AppData;
        }
        //Added By HJ On 22-06-2020 For Optimize register_user Table Query End
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        $eLogout = $AppData[0]['eLogout'];
        $tLocationUpdateDate = $AppData[0]['tLocationUpdateDate'];
        $iGcmRegId = $AppData[0]['iGcmRegId'];
        $tSessionId = $AppData[0]['tSessionId'];
        /* For PubNub Setting Finished */
        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
        $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
        //$alertSendAllowed = false;
        $alertSendAllowed = true;
        //$message = $alertMsg;
        $tLocUpdateDate = date("Y-m-d H:i:s", strtotime($tLocationUpdateDate));
        if ($tLocUpdateDate < $compare_date) {
            $alertSendAllowed = true;
        }
        $alertSendAllowed = true;
        if ($eLogout == "Yes") {
            $alertSendAllowed = false;
        }
        $deviceTokens_arr = array();
        //if ($eType != "Multi-Delivery") {
            if ($alertSendAllowed == true) {
                array_push($deviceTokens_arr, $iGcmRegId);
                if ($eDeviceType == "Android") {
                    $Rmessage = array("message" => json_decode($message, true));
                    send_notification($deviceTokens_arr, $Rmessage, 0);
                }
                else {
                    sendApplePushNotification(0, $deviceTokens_arr, json_decode($message, true) , $alertMsg, 0);
                }
            }
            ########## Pubnub #####################
            $channelName = "PASSENGER_" . $userId;
            //$tSessionId = get_value("register_user", 'tSessionId', "iUserId", $userId, '', 'true');
            $message_arr['tSessionId'] = $tSessionId;
            $message_arr['eSystem'] = "";
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
            if ($eDeviceType == "Ios") {
                sleep(5);
            }
            publishEventMessage($channelName, $message_pub);
            ########## Pubnub #####################
        //}
        $returnArr['Action'] = "1";
        $returnArr['iTripsLocationsID'] = $id;
        if ($TRIP_CONTINUE == "No") {
            $returnArr['TotalFare'] = round($Fare_data['total_fare'] * $trip_start_data_arr[0]['fRatioDriver'], 1);
            $returnArr['Discount'] = round($Fare_data['fDiscount'] * $trip_start_data_arr[0]['fRatioDriver'], 1);
        } else {
            $returnArr['TotalFare'] = round($trip_start_data_arr[0]['iFare'] * $trip_start_data_arr[0]['fRatioDriver'], 1);
            $returnArr['Discount'] = round($trip_start_data_arr[0]['fDiscount'] * $trip_start_data_arr[0]['fRatioDriver'], 1);
        }
        $returnArr['CurrencySymbol'] = $currencySymbolDriver;
        $returnArr['tripStartTime'] = $startDate;
        $returnArr['TripPaymentMode'] = $trip_start_data_arr[0]['vTripPaymentMode'];
        $returnArr['Discount'] = round($Fare_data['fDiscount'] * $trip_start_data_arr[0]['fRatioDriver'], 1);
        $returnArr['Message'] = "Data Updated";
        $returnArr['FormattedTripDate'] = date('dS M Y \a\t h:i a', strtotime($startDate));
        $generalobj->get_benefit_amount($tripId);
        // Code for Check last logout date is update in driver_log_report
        $query = "SELECT * FROM driver_log_report WHERE iDriverId = '" . $driverId . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
        $db_driver = $obj->MySQLSelect($query);
        if (count($db_driver) > 0) {
            $driver_lastonline = @date("Y-m-d H:i:s");
            $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
            $obj->sql_query($updateQuery);
        }
        // Code for Check last logout date is update in driver_log_report Ends
        /* ---------------------------Multi delivery values--------------------------- */
        if ($eType == "Multi-Delivery") {
            $returnArr['ePaymentByReceiverForDelivery'] = "No";
            // Added By HJ On 24-06-2020 For Optimize trips_delivery_locations Table Query Start
            $totalTripDeliveryCount =0;
            if(isset($tripDetailsArr["trips_delivery_locations_".$tripId])){
                $sqldeliverydata = $tripDetailsArr["trips_delivery_locations_".$tripId];
                $totalTripDeliveryCount = count($sqldeliverydata);
            }else{
                $sqldeliverydata = $obj->MySQLSelect("SELECT * FROM `trips_delivery_locations` WHERE iTripId='" . $tripId . "' ORDER BY  iTripDeliveryLocationId ASC");
                $tripDetailsArr["trips_delivery_locations_".$tripId] = $sqldeliverydata;
                $totalTripDeliveryCount = count($sqldeliverydata);
            }
            $TripDeliveryData =$tripDeliveryFieldArr= array();
            //echo "<pre>";print_r($sqldeliverydata);die;
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
            // Added By HJ On 24-06-2020 For Optimize trips_delivery_locations Table Query End
            //$sqldeliverydata = "SELECT * FROM `trips_delivery_locations` WHERE ( iActive='Active' OR iActive='On Going Trip')  AND iTripId='" . $tripId . "' ORDER BY iTripDeliveryLocationId ASC LIMIT 0,1";
            //$TripDeliveryData = $obj->MySQLSelect($sqldeliverydata);
            if (count($TripDeliveryData) > 0) {
                $iTripDeliveryLocationId = $TripDeliveryData[0]['iTripDeliveryLocationId'];
                $vPhoneCode = $result22[0]['vCode'];
                $vLang = $result22[0]['vLang'];
                if ($vLang == "" || $vLang == NULL) {
                    //Added By HJ On 24-06-2020 For Optimize language_master Table Query Start
                    if (!empty($vSystemDefaultLangCode)) {
                        $vLang = $vSystemDefaultLangCode;
                    } else {
                        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                    }
                    //Added By HJ On 24-06-2020 For Optimize language_master Table Query End
                }
                // Added By HJ On 24-06-2020 For Optimize trip_delivery_fields Table Query Start
                if(isset($tripDetailsArr["trip_delivery_fields_".$tripId])){
                    $sqldeliveryFielddata = $tripDetailsArr["trip_delivery_fields_".$tripId];
                }else{
                    $sqldeliveryFielddata = $obj->MySQLSelect("SELECT * FROM trip_delivery_fields WHERE iTripId='".$tripId."'");
                    $tripDetailsArr["trip_delivery_fields_".$tripId] = $sqldeliveryFielddata;
                }
                $tripDeliveryFieldArr = array();
                for($f=0;$f<count($sqldeliveryFielddata);$f++){
                    $iTripDeliveryLocationId = $sqldeliveryFielddata[$f]['iTripDeliveryLocationId'];
                    $iDeliveryFieldId = $sqldeliveryFielddata[$f]['iDeliveryFieldId'];
                    $tripDeliveryFieldArr[$iTripDeliveryLocationId][$iDeliveryFieldId] = $sqldeliveryFielddata[$f];
                }
                // Added By HJ On 24-06-2020 For Optimize trip_delivery_fields Table Query End
                // Added By HJ On 24-06-2020 For Optimize trip_delivery_fields Table Query Start
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
                // Added By HJ On 24-06-2020 For Optimize trip_delivery_fields Table Query End
                //$vReceiverName = get_value('trip_delivery_fields', 'vValue', 'iDeliveryFieldId', '2', " and iTripDeliveryLocationId ='" . $TripDeliveryData[0]['iTripDeliveryLocationId'] . "'", 'true');
                //$vReceiverMobile = get_value('trip_delivery_fields', 'vValue', 'iDeliveryFieldId', '3', " and iTripDeliveryLocationId ='" . $TripDeliveryData[0]['iTripDeliveryLocationId'] . "'", 'true');
                //$sql = "SELECT count(iTripDeliveryLocationId) AS TotalTripDelivery FROM trips_delivery_locations WHERE iTripId='" . $tripId . "'";
                //$TotalTripDeliveryData = $obj->MySQLSelect($sql);
                //$TotalTripDelivery = $TotalTripDeliveryData[0]['TotalTripDelivery'];
                $TotalTripDelivery = $totalTripDeliveryCount;
                //Added By HJ On 24-06-2020 For Optimize language label Table Query Start
                if(isset($languageLabelDataArr[$vLang])){
                    $languageLabelsArr = $languageLabelDataArr[$vLang];
                }else{
                    $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
                    $languageLabelDataArr[$vLang] = $languageLabelsArr;
                }
                //Added By HJ On 24-06-2020 For Optimize language label Table Query End
                if(isset($languageLabelsArr['LBL_CURRENT_DELIVERY_NUMBER'])){
                    $LBL_CURRENT_DELIVERY_NUMBER_TXT = $languageLabelsArr['LBL_CURRENT_DELIVERY_NUMBER'];
                }else{
                    $LBL_CURRENT_DELIVERY_NUMBER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_CURRENT_DELIVERY_NUMBER', " and vCode ='" . $vLang . "'", 'true');
                }
                if(isset($languageLabelsArr['LBL_OUT_OF_TXT'])){
                    $LBL_OUT_OF_TXT = $languageLabelsArr['LBL_OUT_OF_TXT'];
                }else{
                    $LBL_OUT_OF_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_OUT_OF_TXT', " and vCode ='" . $vLang . "'", 'true');
                }
                if(isset($languageLabelsArr['LBL_CURRENT_DELIVERY_NUMBER'])){
                    $LBL_CURRENT_DELIVERY_NUMBER = $languageLabelsArr['LBL_CURRENT_DELIVERY_NUMBER'];
                }else{
                    $LBL_CURRENT_DELIVERY_NUMBER = get_value('language_label', 'vValue', 'vLabel', 'LBL_CURRENT_DELIVERY_NUMBER', " and vCode ='" . $vLang . "'", 'true');
                }
                $iRunningTripDeliveryNo = $trip_start_data_arr[0]['iRunningTripDeliveryNo'];
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
                $returnArr['Running_Receipent_Detail'] = $LBL_CURRENT_DELIVERY_NUMBER . " " . $iRunningTripDeliveryNo . " ( " . $vReceiverName . " )";
                $returnArr['iTripDeliveryLocationId'] = $TripDeliveryData[0]['iTripDeliveryLocationId'];
            }
            $IS_OPEN_SIGN_VERIFY = $IS_OPEN_FOR_SENDER = "No";
            $vTripStatus = $trip_start_data_arr[0]['iActive'];
            $vTripDriverId = $trip_start_data_arr[0]['iDriverId'];
            //Added By HJ On 24-06-2020 For Optimize regis Table Query Start
            if(isset($userDetailsArr['register_driver_'.$vTripDriverId])){
                $vDriverTripStatus = $userDetailsArr['register_driver_'.$vTripDriverId][0]['vTripStatus'];
            }else{
                $vDriverTripStatus = get_value('register_driver', 'vTripStatus', 'iDriverId', $vTripDriverId, '', 'true');
            }
            //Added By HJ On 24-06-2020 For Optimize regis Table Query End
            $eSignVerification = $trip_start_data_arr[0]['eSignVerification'];
            if (($vTripStatus == "Active" && $vDriverTripStatus == "Arrived" && $eSignVerification == "No") || ($trip_start_data_arr[0]['ePaymentCollect_Delivery'] == "No" && $vDriverTripStatus == "Arrived")) {
                $IS_OPEN_SIGN_VERIFY = $IS_OPEN_FOR_SENDER = "Yes";
            }
            if ($IS_OPEN_SIGN_VERIFY == "No") {
                $sqldelivdata = "SELECT eSignVerification FROM `trips_delivery_locations` WHERE ( iActive='Canceled' OR iActive='Finished')  AND eSignVerification = 'No' AND iTripId='" . $tripId . "' ORDER BY iTripDeliveryLocationId ASC LIMIT 0,1";
                $TripDeliData = $obj->MySQLSelect($sqldelivdata);
                $eSignVerification = $TripDeliData[0]['eSignVerification'];
                if ($eSignVerification == "No" && $DELIVERY_VERIFICATION_METHOD != "None") {
                    $IS_OPEN_SIGN_VERIFY = "Yes";
                    $IS_OPEN_FOR_SENDER = "No";
                }
            }
            $returnArr['IS_OPEN_SIGN_VERIFY'] = $IS_OPEN_SIGN_VERIFY;
            $returnArr['IS_OPEN_FOR_SENDER'] = $IS_OPEN_FOR_SENDER;
        } else {
            $returnArr['IS_OPEN_SIGN_VERIFY'] = $returnArr['IS_OPEN_FOR_SENDER'] = "No";
            // $vDeliveryConfirmCode = $tripData[0]['vDeliveryConfirmCode'];
        }
        /* ---------------------------Multi delivery values end--------------------------- */
    }else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    //getTripChatDetails($tripId);
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "CollectPayment") {
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $isCollectCash = isset($_REQUEST["isCollectCash"]) ? $_REQUEST["isCollectCash"] : '';
    // for hotel panel web
    //Added By HJ On 26-06-2020 For Optimize trips Table Query Start
    if(isset($tripDetailsArr['trips_'.$iTripId])){
        $tripData = $tripDetailsArr['trips_'.$iTripId];
    }else{
        $tripData = $obj->MySQLSelect("SELECT * FROM trips WHERE iTripId='$iTripId'");
        $tripDetailsArr['trips_'.$iTripId] = $tripData;
    }
    //Added By HJ On 26-06-2020 For Optimize trips Table Query End
    $vTripPaymentMode = $tripData[0]['vTripPaymentMode'];
    $data['vTripPaymentMode'] = $vTripPaymentMode;
    $iUserId = $tripData[0]['iUserId'];
    //$iFare = $tripData[0]['iFare']+$tripData[0]['fTollPrice'];
    $iFare = $tripData[0]['iFare'];
    $vRideNo = $tripData[0]['vRideNo'];
    $eHailTrip = $tripData[0]['eHailTrip'];
    $eBookingFrom = $tripData[0]['eBookingFrom'];
    $eType = $tripData[0]['eType'];
    $ePaymentCollect_Delivery = $tripData[0]['ePaymentCollect_Delivery'];
    $totalTax = $tripData[0]['fTax1'] + $tripData[0]['fTax2'];
    //Added By HJ On 26-06-2020 For Optimize language_master Table Query Start
    if (!empty($vSystemDefaultLangCode)) {
        $vLangCode = $vSystemDefaultLangCode;
    } else {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    //Added By HJ On 26-06-2020 For Optimize language_master Table Query End
    //Added By HJ On 26-06-2020 For Optimize register_user Table Query Start
    if(isset($userDetailsArr['register_user_'.$iUserId])){
        $userData == $userDetailsArr['register_user_'.$iUserId];
    }else{
        $userData = $obj->MySQLSelect("SELECT * FROM register_user WHERE iUserId='$iUserId'");
        $userDetailsArr['register_user_'.$iUserId] = $userData;
    }
    //Added By HJ On 26-06-2020 For Optimize register_user Table Query End
    $vStripeCusId = $userData[0]['vStripeCusId'];
    $$vBrainTreeToken = $userData[0]['vBrainTreeToken'];
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $returnArr['message1'] = "LBL_COLLECT_CASH"; //added by SP to show label at app when card is selected at that time skip is displayd otherwise collect cash on 30-07-2019
    if ($vTripPaymentMode == "Card" && $isCollectCash == "" && ($eType != "Multi-Delivery" || ($ePaymentCollect_Delivery == "No" && $eType == "Multi-Delivery"))) {
        //$vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');
        //$vBrainTreeToken = get_value('register_user', 'vBrainTreeToken', 'iUserId', $iUserId, '', 'true');
        $price_new = $iFare * 100;
        //Added By HJ On 26-06-2020 For Optimization currency Table Query Start
        if (!empty($vSystemDefaultCurrencyName)) {
            $currency = $vSystemDefaultCurrencyName;
        }else{
            $currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        }
        //Added By HJ On 26-06-2020 For Optimization currency Table Query End
        //$currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        $description = $languageLabelsArr['LBL_TRIP_PAYMENT_RECEIVED'] . " " . $vRideNo;
        $Charge_Array = array("iFare" => $iFare,"price_new" => $price_new,"currency" => $currency,"vStripeCusId" => $vStripeCusId,"description" => $description,"iTripId" => $iTripId,"eCancelChargeFailed" => "No","vBrainTreeToken" => $vBrainTreeToken,"vRideNo" => $vRideNo,"iMemberId" => $iUserId,"UserType" => "Passenger");
        if ($iFare != 0) { // added by SP for if card payment and total payment using wallet adjestment then ifare 0 so no need to charge it on 30-07-2019
            $ChargeidArr = ChargeCustomer($Charge_Array, "CollectPayment"); // function for charge customer
            $ChargeidArrId = $ChargeidArr['id'];
            $status = $ChargeidArr['status'];
            if ($status == "success") {
                $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
                $data_payments['iTripId'] = $iTripId;
                $data_payments['eEvent'] = "Trip";
                $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
            }
        }
        $data['vTripPaymentMode'] = "Card";
        $data['eCardFailed'] = 'No';
    }
    else if ($vTripPaymentMode == "Card" && $isCollectCash == "true") {
        /* added by SP Outstanding calculate if payment failed start on 27-7-2019 */
        //$data['vTripPaymentMode'] = "Cash";
        $fOutStandingAmount = $iFare;
        $where = " iTripId = '$iTripId'";
        $data_out['eCardFailed'] = 'Yes';
        $idOut = $obj->MySQLQueryPerform("trips", $data_out, 'update', $where);
        UpdateTripOutstandingAmount($iTripId, "No", "No");
        $fOutStandingAmount = get_value('trips', 'fOutStandingAmount', 'iTripId', $iTripId, '', 'true');
        if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
            $obj->sql_query("UPDATE trip_outstanding_amount set eAuthoriseIdName='No',iAuthoriseId = '0' WHERE iAuthoriseId='" . $iTripId . "' AND eAuthoriseIdName='iTripId'");
        }
        if ($fOutStandingAmount > 0) {
            $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'No',vTripAdjusmentId = '' WHERE iUserId = '" . $iUserId . "' AND vTripAdjusmentId IN($iTripId)";
            $obj->sql_query($updateQury);
            $updateQuery1 = "UPDATE register_user set fTripsOutStandingAmount = fTripsOutStandingAmount+'" . $fOutStandingAmount . "' WHERE iUserId = " . $iUserId;
            $obj->sql_query($updateQuery1);
            $updateQuery3 = "UPDATE trips set fAddedOutstandingamt = '" . $fOutStandingAmount . "' WHERE iTripId = " . $iTripId;
            $obj->sql_query($updateQuery3);
            $updateQuery2 = "UPDATE trips set fOutStandingAmount = 0 WHERE iTripId = " . $iTripId;
            $obj->sql_query($updateQuery2);
        }
        $UserData = $obj->MySQLSelect("select concat(vName,' ',vLastName) as username, vEmail from register_user where `iUserId`= $iUserId");
        $Data1['username'] = $UserData[0]['username'];
        $Data1['vEmail'] = $UserData[0]['vEmail'];
        $Data1['TripNo'] = $vRideNo;
        $sendMailfromDriver = $generalobj->send_email_user("TRANSACTION_FAILED_OUTSTANDING_AMT", $Data1);
        /* added by SP Outstanding calculate if payment failed end on 27-7-2019 */
    }
    $where = " iTripId = '$iTripId'";
    if ($eType == "Multi-Delivery" && $ePaymentCollect_Delivery == "No" && $isCollectCash == "true") {
        $data['ePaymentCollect_Delivery'] = "Yes";
    }
    else {
        $data['ePaymentCollect'] = "Yes";
        $data['ePaymentCollect_Delivery'] = $ePaymentCollect_Delivery = "No";
    }
    $iBalance = 0;
    $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);
    $fWalletDebit = $tripData[0]['fWalletDebit'];
    $fDiscount = $tripData[0]['fDiscount'];
    $discountValue = $generalobj->setTwoDecimalPoint($fWalletDebit + $fDiscount);
    $walletamountofcreditcard = $tripData[0]['fTripGenerateFare'];
    $driverId = $tripData[0]['iDriverId'];
            
        
    if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
        #Deduct Amount From Driver's Wallet Acount#
        $vTripPaymentMode = $data['vTripPaymentMode'];
        if ($vTripPaymentMode == "Cash" && $isCollectCash == "" && ($eType != "Multi-Delivery" || ($ePaymentCollect_Delivery == "No" && $eType == "Multi-Delivery"))) {
            $vRideNo = $tripData[0]['vRideNo'];
            //$iBalance = $tripData[0]['fCommision'];
            //for hotel panel web
            $iBalance = $tripData[0]['fCommision'] + $tripData[0]['fOutStandingAmount'] + $tripData[0]['fHotelCommision'] + $totalTax;
            $eFor = "Withdrawl";
            $eType = "Debit";
            //$tDescription = 'Debited for booking#'.$vRideNo;
            //$tDescription = '#LBL_DEBITED_BOOKING# ' . $vRideNo;
            $tDescription = '#LBL_DEBITED_SITE_EARNING_BOOKING#' . $vRideNo;
            $ePaymentStatus = 'Settelled';
            $dDate = Date('Y-m-d H:i:s');
            //$iBalance = $iBalance - $discountValue;
            //$discountValue = 0;
            $totalUserFare = $iFare;
            // Added By HJ On 18-12-2019 For Prevent Duplication Issue Dicuss with KS Sir Start
            $getPaymentStatus = $obj->MySQLSelect("SELECT eUserType,ePaymentStatus,iUserWalletId,eType FROM user_wallet WHERE iTripId='" . $iTripId . "'");
            $walletArr = array();
            for ($h = 0;$h < count($getPaymentStatus);$h++) {
                $walletArr[$getPaymentStatus[$h]['eType']][$getPaymentStatus[$h]['eUserType']] = $getPaymentStatus[$h]['eType'];
            }
            // Added By HJ On 18-12-2019 For Prevent Duplication Issue Dicuss with KS Sir End
            if ($discountValue > 0) {
                $eFor_credit = "Deposit";
                $eType_credit = "Credit";
                $tDescription_credit = '#LBL_CREDITED_BOOKING# ' . $vRideNo;
                //$tDescription_credit = 'Credited for booking#'.$vRideNo;
                if (!isset($walletArr[$eType_credit]['Driver'])) {
                    $generalobj->InsertIntoUserWallet($driverId, "Driver", $discountValue, $eType_credit, $iTripId, $eFor_credit, $tDescription_credit, $ePaymentStatus, $dDate); // Commet
                    
                }
                $totalUserFare += $discountValue;
            }
            /* added by PM for Auto credit wallet driver on 25-01-2020 start */
            if (checkAutoCreditDriverModule()) {
                $Where = " iTripId = '$iTripId'";
                $Data_update_driver_paymentstatus = array();
                $Data_update_driver_paymentstatus['eDriverPaymentStatus'] = "Settelled";
                $Update_Payment_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus, 'update', $Where); // Added By HJ On 08-08-2019 As Per Discuss With KS and BM mam
                if (!isset($walletArr[$eType]['Driver'])) {
                    $generalobj->InsertIntoUserWallet($driverId, "Driver", $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate);
                }
            }
            else {
                $Where = " iTripId = '$iTripId'";
                $Data_update_driver_paymentstatus = array();
                if ($totalUserFare >= $iBalance) {
                    $Data_update_driver_paymentstatus['eDriverPaymentStatus'] = "Settelled";
                    if ($walletamountofcreditcard > $totalUserFare) {
                        $Data_update_driver_paymentstatus['eDriverPaymentStatus'] = "Unsettelled";
                    }
                    $Update_Payment_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus, 'update', $Where); // Added By HJ On 08-08-2019 As Per Discuss With KS and BM mam
                    if (!isset($walletArr[$eType]['Driver'])) {
                        $generalobj->InsertIntoUserWallet($driverId, "Driver", $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate);
                    }
                }
            }
            /* added by PM for Auto credit wallet driver on 25-01-2020 end */
            /* if ($iBalance > 0) {
            
              //Added By HJ On 09-09-2019 For Update eDriverPaymentStatus Status Settelled As Per Discuss With KS and BM Start
            
              $Data_update_driver_paymentstatus['eDriverPaymentStatus'] = "Settelled";
            
              $Update_Payment_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus, 'update', $Where);
            
              //Added By HJ On 09-09-2019 For Update eDriverPaymentStatus Status Settelled As Per Discuss With KS and BM End
            
              $generalobj->InsertIntoUserWallet($driverId, "Driver", $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate);
            
              } */
            ///$Update_Payment_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus, 'update', $Where); // Commented By HJ On 08-08-2019 As Per Discuss With KS and BM mam
            $returnArr['message1'] = "LBL_COLLECT_CASH"; //added by SP to show label at app when card is selected at that time skip is displayd otherwise collect cash on 30-07-2019
            
        }
        #Deduct Amount From Driver's Wallet Acount#
        
    }
    /* added by PM for Auto credit wallet driver on 25-01-2020 start */
    if (checkAutoCreditDriverModule()) {
        $Data = array();
        $Data['ePaymentStatus'] = $ePaymentStatus;
        $Data['isCollectCash'] = $isCollectCash;
        $Data['iUserId'] = $iUserId;
        $Data['iTripId'] = $iTripId;
        $Data['iBalance'] = $iBalance;
        AutoCreditWalletDriver($Data, "CollectPayment", 0);
    }
    /* added by PM for Auto credit wallet driver on 25-01-2020 end  */
    if ($id > 0) {
        $returnArr['Action'] = "1";
        // Rating entry if trip is hail
        if ($eHailTrip == "Yes") {
            $Data_update_ratings['iTripId'] = $iTripId;
            $Data_update_ratings['vRating1'] = "0.0";
            $Data_update_ratings['vMessage'] = "";
            $Data_update_ratings['eUserType'] = "Driver";
            $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
            $Data_update_ratings['eUserType'] = "Passenger";
            $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
            $eType_Trip = get_value('trips', 'eType', 'iTripId', $iTripId, '', 'true');
            if ($eType_Trip == "Multi-Delivery") {
                sendTripReceiptAdmin_Multi($iTripId);
            }
            else {
                sendTripReceiptAdmin($iTripId);
            }
        }
        if ($eBookingFrom == 'Admin' || $eBookingFrom == 'User' || $eBookingFrom == 'Hotel' || $eBookingFrom == 'Company') {
            $Data_update_ratings['iTripId'] = $iTripId;
            $Data_update_ratings['vRating1'] = "0.0";
            $Data_update_ratings['vMessage'] = "";
            $Data_update_ratings['eUserType'] = "Driver";
            $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
            $Data_update_ratings['eUserType'] = "Passenger";
            $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
        }
        if ($eBookingFrom == 'Kiosk') {
            $Data_update_ratings['iTripId'] = $iTripId;
            $Data_update_ratings['vRating1'] = "0.0";
            $Data_update_ratings['vMessage'] = "";
            $Data_update_ratings['eUserType'] = "Driver";
            $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
            $Data_update_ratings['eUserType'] = "Passenger";
            $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
            $vEmailUser = get_value('register_user', 'vRecipientEmail', 'iUserId', $iUserId, '', 'true'); //added by SP for kiosk change in vRecipientEmail
            if ($vEmailUser != '') {
                sendTripReceipt($iTripId);
            }
            sendTripReceiptHotel($iTripId);
            //sendTripReceiptAdmin($iTripId);
            
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "GenerateCustomer") {
    $Data = array();
    $Data = $_REQUEST;
    $returnArr = GenerateCustomer($Data);
    ###############################    Stripe Request Param  #####################################
    /* $iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    
      $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    
      $vStripeToken     = isset($_REQUEST["vStripeToken"]) ? $_REQUEST["vStripeToken"] : '';
    
      $CardNo     = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : ''; */
    ###############################    Stripe Request Param  #####################################
    ###############################    Braintree Request Param  #####################################
    /* $iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    
      $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    
      $CardNo     = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : '';
    
      $paymentMethodNonce = isset($_REQUEST["paymentMethodNonce"]) ? $_REQUEST["paymentMethodNonce"] : ''; */
    ###############################    Braintree Request Param  #####################################
    ###############################    Paymaya Request Param  #####################################
    /* $iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    
      $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    
      $vPaymayaToken     = isset($_REQUEST["vPaymayaToken"]) ? $_REQUEST["vPaymayaToken"] : '';
    
      $CardNo     = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : ''; */
    ###############################    Paymaya Request Param  #####################################
    ###############################    Omise Request Param  #####################################
    /* $iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    
      $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    
      $vOmiseToken     = isset($_REQUEST["vOmiseToken"]) ? $_REQUEST["vOmiseToken"] : '';
    
      $CardNo     = isset($_REQUEST["CardNo"]) ? $_REQUEST["CardNo"] : ''; */
    ###############################    Omise Request Param  #####################################
    ###############################    Xendit Request Param  #####################################
    /* $iUserId     = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    
      $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';  //Passenger,Driver
    
      $vXenditToken     = isset($_REQUEST["vXenditToken"]) ? $_REQUEST["vXenditToken"] : ''; */
    ###############################    Xendit Request Param  #####################################
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "ScheduleARide") {
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $pickUpLocAdd = isset($_REQUEST["pickUpLocAdd"]) ? $_REQUEST["pickUpLocAdd"] : '';
    $pickUpLatitude = isset($_REQUEST["pickUpLatitude"]) ? $_REQUEST["pickUpLatitude"] : '';
    $pickUpLongitude = isset($_REQUEST["pickUpLongitude"]) ? $_REQUEST["pickUpLongitude"] : '';
    $destLocAdd = isset($_REQUEST["destLocAdd"]) ? $_REQUEST["destLocAdd"] : '';
    $destLatitude = isset($_REQUEST["destLatitude"]) ? $_REQUEST["destLatitude"] : '';
    $destLongitude = isset($_REQUEST["destLongitude"]) ? $_REQUEST["destLongitude"] : '';
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : 0;
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $iPackageTypeId = isset($_REQUEST["iPackageTypeId"]) ? $_REQUEST["iPackageTypeId"] : '';
    $vReceiverName = isset($_REQUEST["vReceiverName"]) ? $_REQUEST["vReceiverName"] : '';
    $vReceiverMobile = isset($_REQUEST["vReceiverMobile"]) ? $_REQUEST["vReceiverMobile"] : '';
    $tPickUpIns = isset($_REQUEST["tPickUpIns"]) ? $_REQUEST["tPickUpIns"] : '';
    $tDeliveryIns = isset($_REQUEST["tDeliveryIns"]) ? $_REQUEST["tDeliveryIns"] : '';
    $tPackageDetails = isset($_REQUEST["tPackageDetails"]) ? $_REQUEST["tPackageDetails"] : '';
    $vCouponCode = isset($_REQUEST["PromoCode"]) ? $_REQUEST["PromoCode"] : '';
    $iUserPetId = isset($_REQUEST["iUserPetId"]) ? $_REQUEST["iUserPetId"] : '';
    $cashPayment = isset($_REQUEST["CashPayment"]) ? $_REQUEST["CashPayment"] : '';
    $quantity = isset($_REQUEST["Quantity"]) ? $_REQUEST["Quantity"] : '';
    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';
    $HandicapPrefEnabled = isset($_REQUEST["HandicapPrefEnabled"]) ? $_REQUEST["HandicapPrefEnabled"] : '';
    $PreferFemaleDriverEnable = isset($_REQUEST["PreferFemaleDriverEnable"]) ? $_REQUEST["PreferFemaleDriverEnable"] : '';
    $iDriverId = isset($_REQUEST["SelectedDriverId"]) ? $_REQUEST["SelectedDriverId"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '0';
    $tUserComment = isset($_REQUEST["tUserComment"]) ? $_REQUEST["tUserComment"] : '';
    $total_del_dist = isset($_REQUEST["total_del_dist"]) ? $_REQUEST["total_del_dist"] : '';
    $total_del_time = isset($_REQUEST["total_del_time"]) ? $_REQUEST["total_del_time"] : '';
    $eWalletDebitAllow = isset($_REQUEST["eWalletDebitAllow"]) ? $_REQUEST["eWalletDebitAllow"] : '';
    $iRentalPackageId = isset($_REQUEST["iRentalPackageId"]) ? $_REQUEST["iRentalPackageId"] : '';
    $vDistance = isset($_REQUEST["vDistance"]) ? $_REQUEST["vDistance"] : '';
    $vDuration = isset($_REQUEST["vDuration"]) ? $_REQUEST["vDuration"] : '';
    $iTripReasonId = isset($_REQUEST["iTripReasonId"]) ? $_REQUEST["iTripReasonId"] : '';
    $vReasonTitle = isset($_REQUEST["vReasonTitle"]) ? $_REQUEST["vReasonTitle"] : ''; // For Other Reason
    ######### added payment flow 2 ############
    $eWalletIgnore = isset($_REQUEST["eWalletIgnore"]) ? $_REQUEST["eWalletIgnore"] : '';
    $ePayWallet = isset($_REQUEST["ePayWallet"]) ? $_REQUEST["ePayWallet"] : '';
    //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data Start
    $orderDetails = isset($_REQUEST['OrderDetails']) ? $_REQUEST['OrderDetails'] : array();
    $eServiceLocation = isset($_REQUEST['eServiceLocation']) ? $_REQUEST['eServiceLocation'] : 'Passanger';
    //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data End
    ########## added payment flow 2 ##############
    $orderDetails = preg_replace('/[[:cntrl:]]/', '\r\n', $orderDetails);
    $vDuration = empty($vDuration) ? 0 : $vDuration;
    $vDistance = empty($vDistance) ? 0 : $vDistance;
    $vDuration = round(($vDuration / 60) , 2);
    $vDistance = round(($vDistance / 1000) , 2);
    //added by SP for fly stations on 19-08-2019 start
    $iFromStationId = isset($_REQUEST["iFromStationId"]) ? $_REQUEST["iFromStationId"] : '';
    $iToStationId = isset($_REQUEST["iToStationId"]) ? $_REQUEST["iToStationId"] : '';
    //if($iFromStationId!='' && $iToStationId!='') {
    if (!empty($iFromStationId) && !empty($iToStationId)) {
        $Data['iFromStationId'] = $iFromStationId;
        $Data['iToStationId'] = $iToStationId;
    }
    //added by SP for fly stations on 19-08-2019 end
    ####### blocking code ################
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $BlockData = getBlockData("Passanger", $iUserId);
        if (!empty($BlockData) || $BlockData != "") {
            setDataResponse($BlockData);
        }
    }
    if (empty($eServiceLocation)) {
        $eServiceLocation = "Passenger";
    }
    if ($APP_TYPE == "Ride-Delivery-UberX") {
        $sqldata = "SELECT iTripId FROM `trips` WHERE iActive='On Going Trip'  AND iUserId='" . $iUserId . "' AND eType != 'UberX' AND eType != 'Multi-Delivery'";
        $TripData = $obj->MySQLSelect($sqldata);
        if (count($TripData) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ONGOING_TRIP_USER_TXT";
            setDataResponse($returnArr);
        }
    }
    $action = ($iCabBookingId != "") ? 'Edit' : 'Add';
    if ($eType == "") {
        $eType = $APP_TYPE == "Delivery" ? "Deliver" : $APP_TYPE;
    }
    $paymentMode = "Card";
    if ($cashPayment == 'true') {
        $paymentMode = "Cash";
    }
    checkmemberemailphoneverification($iUserId, "Passenger");
    ## Check Pickup Address For UberX##
    $fAmount = 0;
    $ALLOW_SERVICE_PROVIDER_AMOUNT = "No";
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    if ($eType == "UberX") {
        if ($quantity < 1) {
            $quantity = 1;
        }
        $minHour_ufx = - 1;
        $Data['tUserComment'] = $tUserComment;
        //Added By HJ On 01-02-2019 For Get Vehicle Type Total Fare Amount Start
        $fareDetails = array();
        if ($SERVICE_PROVIDER_FLOW == "Provider") {
            $fareDetails = getVehicleTypeFareDetails();
            if (!empty($fareDetails)) {
                $Data['tVehicleTypeFareData'] = json_encode($fareDetails['tripFareDetailsSaveArr']);
            }
            $typeDataArr = json_decode($orderDetails);
            $iVehicleTypeId = 0;
            if (count($typeDataArr) == 1) {
                $iVehicleTypeId = $typeDataArr[0]->iVehicleTypeId;
            }
        }
        //Added By HJ On 01-02-2019 For Get Vehicle Type Total Fare Amount End
        // added for payment method 2 //
        if (isset($fareDetails['originalFareTotal']) && !empty($fareDetails['originalFareTotal'])) {
            $iPrice = $fareDetails['originalFareTotal'];
        }
        else if ($iVehicleTypeId > 0) {
            $sqlv = "SELECT vt.iVehicleCategoryId,vc.iParentId,vt.eFareType,vt.ePickStatus,vt.eNightStatus,vt.fFixedFare, vt.iBaseFare, vt.iMinFare, vt.fMinHour,vc.ePriceType From vehicle_type as vt LEFT JOIN " . $sql_vehicle_category_table_name . " as vc ON  vc.iVehicleCategoryId = vt.iVehicleCategoryId WHERE vt.iVehicleTypeId = '" . $iVehicleTypeId . "'";
            $tripVehicleData = $obj->MySQLSelect($sqlv);
            $iVehicleCategoryId = $tripVehicleData[0]['iVehicleCategoryId'];
            $vVehicleTypeName = $tripVehicleData[0]['vVehicleTypeName'];
            $eFareType = $tripVehicleData[0]['eFareType'];
            $iParentId = $tripVehicleData[0]['iParentId'];
            if ($iParentId == 0) {
                $ePriceType = $tripVehicleData[0]['ePriceType'];
            }
            else {
                $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
            }
            $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
            if ($eFareType != "Regular") {
                if ($eFareType == "Fixed") {
                    $fAmount = $tripVehicleData[0]['fFixedFare'] * $quantity;
                }
                else {
                    $minHour_ufx = $tripVehicleData[0]['fMinHour'];
                }
                //Added By HJ On 01-02-2019 For Get Vehicle Type Total Fare Amount Start
                $checkProviderAmt = 1;
                if (isset($fareDetails['originalFareTotal']) && $fareDetails['originalFareTotal'] > 0) {
                    $iPrice = $fareDetails['originalFareTotal'];
                    $vVehicleTypeName = $fareDetails['ParentCategoryName'];
                    $checkProviderAmt = 0;
                }
                //Added By HJ On 01-02-2019 For Get Vehicle Type Total Fare Amount End
                if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes" && $checkProviderAmt == 1) {
                    $sql12 = "SELECT iDriverVehicleId FROM  `driver_vehicle` WHERE iDriverId = '" . $iDriverId . "' AND eType='UberX'";
                    $drivervehicleData = $obj->MySQLSelect($sql12);
                    $iDriverVehicleId = $drivervehicleData[0]['iDriverVehicleId'];
                    $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
                    $serviceProData = $obj->MySQLSelect($sqlServicePro);
                    if (count($serviceProData) > 0) {
                        $fAmount = $serviceProData[0]['fAmount'] * $quantity;
                    }
                    else {
                        $fAmount = $iPrice;
                    }
                    $iPrice = $fAmount;
                }
            }
            else {
                $iBaseFare = round($tripVehicleData[0]['iBaseFare'], 2);
                $iMinFare = round($tripVehicleData[0]['iMinFare'], 2);
                $fAmount = ($iMinFare > $iBaseFare) ? $iMinFare : $iBaseFare;
            }
            $iPrice = $fAmount;
        }
        // added for payment method 2 //
        if ($iUserAddressId != "") {
            //$pickUpLocAdd=get_value('user_address', 'vServiceAddress', '  iUserAddressId',$iUserAddressId,'','true');
            $Address = get_value('user_address', 'vAddressType,vBuildingNo,vLandmark,vServiceAddress,vLatitude,vLongitude', '   iUserAddressId', $iUserAddressId, '', '');
            $vAddressType = $Address[0]['vAddressType'];
            $vBuildingNo = $Address[0]['vBuildingNo'];
            $vLandmark = $Address[0]['vLandmark'];
            $vServiceAddress = $Address[0]['vServiceAddress'];
            $pickUpLocAdd = ($vAddressType != "") ? $vAddressType . "\n" : "";
            $pickUpLocAdd .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
            $pickUpLocAdd .= ($vLandmark != "") ? $vLandmark . "\n" : "";
            $pickUpLocAdd .= ($vServiceAddress != "") ? $vServiceAddress : "";
            $Data['vSourceAddresss'] = $pickUpLocAdd;
            $Data['iUserAddressId'] = $iUserAddressId;
            $pickUpLatitude = $Address[0]['vLatitude'];
            $pickUpLongitude = $Address[0]['vLongitude'];
        }
        else {
            $Data['vSourceAddresss'] = $pickUpLocAdd;
        }
        $eAutoAssign = 'No';
    }
    else {
        $Data['vSourceAddresss'] = $pickUpLocAdd;
        $eAutoAssign = 'Yes';
    }
    ### Checking For Pickup And DropOff Disallow ###
    $pickuplocationarr = array(
        $pickUpLatitude,
        $pickUpLongitude
    );
    $dropofflocationarr = array(
        $destLatitude,
        $destLongitude
    );
    $allowed_ans_pickup = checkAllowedAreaNew($pickuplocationarr, "No");
    if ($allowed_ans_pickup == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    if ($destLatitude != "" && $destLongitude != "") {
        $allowed_ans_dropoff = checkAllowedAreaNew($dropofflocationarr, "Yes");
        if ($allowed_ans_dropoff == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
            setDataResponse($returnArr);
        }
    }
    ### Checking For Pickup And DropOff Disallow ###
    ## Check Pickup Address For UberX##
    ## Check For PichUp/DropOff Location DisAllow ##
    $address_data['PickUpAddress'] = $pickUpLocAdd;
    $address_data['DropOffAddress'] = $destLocAdd;
    $DropOff = "No";
    if ($destLatitude != "" && $destLongitude != "") {
        $DropOff = "Yes";
    }
    $DataArr = getOnlineDriverArr($pickUpLatitude, $pickUpLongitude, $address_data, $DropOff, "No", "No", "", $destLatitude, $destLongitude, $eType, "", $iDriverId);
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
    if ($eType == "UberX") {
        $sdate = explode(" ", $scheduleDate);
        $shour = explode("-", $sdate[1]);
        $shour1 = $shour[0];
        $shour2 = $shour[1];
        if ($shour1 == "12" && $shour2 == "01") {
            $shour1 = 00;
        }
        $scheduleDate = $sdate[0] . " " . $shour1 . ":00:00";
        $currentdate = date("Y-m-d H:i:s");
        $datediff = strtotime($scheduleDate) - strtotime($currentdate);
    }
    $Booking_Date_Time = $scheduleDate;
    $systemTimeZone = date_default_timezone_get();
    // echo "hererrrrr:::".$scheduleDate;exit;
    $scheduleDate = converToTz($scheduleDate, $systemTimeZone, $vTimeZone);
    if ($iVehicleTypeId > 0) {
        $SurchargeDetail = get_value('vehicle_type', 'ePickStatus,eNightStatus', 'iVehicleTypeId', $iVehicleTypeId);
        $ePickStatus = $SurchargeDetail[0]['ePickStatus'];
        $eNightStatus = $SurchargeDetail[0]['eNightStatus'];
    }
    $fPickUpPrice = $fNightPrice = 1;
    ## Checking For Flat Trip ##
    $eFlatTrip = "No";
    $fFlatTripPrice = 0;
    if ($eType == "Ride" && strtoupper(PACKAGE_TYPE) != "STANDARD") {
        $data_flattrip = checkFlatTripnew($pickuplocationarr, $dropofflocationarr, $iVehicleTypeId, $iRentalPackageId);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    }
    ## Checking For Flat Trip ##
    if ($eType != "UberX") {
        $data_surgePrice = checkSurgePrice($iVehicleTypeId, $Booking_Date_Time, $iRentalPackageId);
        if ($data_surgePrice['Action'] == "0") {
            if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
            }
            else {
                $fNightPrice = $data_surgePrice['SurgePriceValue'];
            }
        }
    }
    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
        $fPickUpPrice = $fNightPrice = 1;
    }
    // add airport surge //
    $fpickupsurchargefare = $fdropoffsurchargefare = 0;
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes' && $eType != "UberX") {
            $pickuplocationarr = array(
                $pickUpLatitude,
                $pickUpLongitude
            );
            $dropofflocationarr = array(
                $destLatitude,
                $destLongitude
            );
            $GetVehicleIdfromGeoLocation = CheckSurgeAirportFromGeoLocation($pickuplocationarr, $dropofflocationarr, $iVehicleTypeId);
            $fpickupsurchargefare = $GetVehicleIdfromGeoLocation['fpickupsurchargefare'];
            $fdropoffsurchargefare = $GetVehicleIdfromGeoLocation['fdropoffsurchargefare'];
            //$airportsurgetype = $AIRPORT_SURGE_ADD_OR_OVERRIDE;
            
        }
    }
    $Data_update_passenger['fAirportPickupSurge'] = $fpickupsurchargefare;
    $Data_update_passenger['fAirportDropoffSurge'] = $fdropoffsurchargefare;
    // end airport surge //
    if ($eTollSkipped == 'No' || $fTollPrice != "") {
        $fTollPrice_Original = $fTollPrice;
        $vTollPriceCurrencyCode = strtoupper($vTollPriceCurrencyCode);
        $default_currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        $sql = " SELECT round(($fTollPrice/(SELECT Ratio FROM currency where vName='" . $vTollPriceCurrencyCode . "'))*(SELECT Ratio FROM currency where vName='" . $default_currency . "' ) ,2)  as price FROM currency  limit 1";
        $result = $obj->MySQLSelect($sql);
        $fTollPrice = $result[0]['price'];
        if ($fTollPrice == 0) {
            $fTollPrice = get_currency($vTollPriceCurrencyCode, $default_currency, $fTollPrice_Original);
        }
        $Data['fTollPrice'] = $fTollPrice;
        $Data['vTollPriceCurrencyCode'] = $vTollPriceCurrencyCode;
        $Data['eTollSkipped'] = $eTollSkipped;
    }
    else {
        $Data['fTollPrice'] = "0";
        $Data['vTollPriceCurrencyCode'] = "";
        $Data['eTollSkipped'] = "No";
    }
    $rand_num = rand(10000000, 99999999);
    /* $Booking_Date = @date('d-m-Y',strtotime($scheduleDate));
    
      $Booking_Time = @date('H:i:s',strtotime($scheduleDate)); */
    $Booking_Date = @date('d-m-Y', strtotime($Booking_Date_Time));
    $Booking_Time = @date('H:i:s', strtotime($Booking_Date_Time));
    $Data['iUserId'] = $iUserId;
    $Data['vSourceLatitude'] = $pickUpLatitude;
    $Data['vSourceLongitude'] = $pickUpLongitude;
    $Data['vDestLatitude'] = $destLatitude;
    $Data['vDestLongitude'] = $destLongitude;
    //$Data['vSourceAddresss']=$pickUpLocAdd;
    $Data['tDestAddress'] = $destLocAdd;
    $Data['ePayType'] = $paymentMode;
    $Data['iVehicleTypeId'] = $iVehicleTypeId;
    $Data['dBooking_date'] = date('Y-m-d H:i', strtotime($scheduleDate));
    $Data['eCancelBy'] = "";
    $Data['fPickUpPrice'] = $fPickUpPrice;
    $Data['fNightPrice'] = $fNightPrice;
    $Data['eType'] = $eType;
    $Data['iUserPetId'] = $iUserPetId;
    $Data['iQty'] = $quantity;
    $Data['vCouponCode'] = $vCouponCode;
    $Data['eAutoAssign'] = $eAutoAssign;
    $Data['vRideCountry'] = $vCountryCode;
    $Data['iDriverId'] = $iDriverId;
    $Data['vTimeZone'] = $vTimeZone;
    $Data['eFemaleDriverRequest'] = $PreferFemaleDriverEnable;
    $Data['eHandiCapAccessibility'] = $HandicapPrefEnabled;
    $Data['eFlatTrip'] = $eFlatTrip;
    $Data['fFlatTripPrice'] = $fFlatTripPrice;
    $Data['eWalletDebitAllow'] = $eWalletDebitAllow;
    /* added for rental */
    $Data['iRentalPackageId'] = $iRentalPackageId;
    $Data['vDistance'] = $vDistance;
    $Data['vDuration'] = $vDuration;
    $Data['tTotalDistance'] = !empty($Data['vDistance']) ? $Data['vDistance'] : $vDistance;
    $Data['tTotalDuration'] = !empty($Data['vDuration']) ? $Data['vDuration'] : $vDuration;
    ################## Book For Someone Else ############################
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        BookForSomeOneElse();
    }
    ################## Book For Someone Else ############################
    // Payment Method 2 Flow Start
    $Data['ePayWallet'] = $ePayWallet;
    //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data Start
    $Data['eServiceLocation'] = $eServiceLocation;
    //$Data['tVehicleTypeData'] = stripcslashes($orderDetails); // Commented By HJ On 23-11-2019 For Soled \n data issue
    $Data['tVehicleTypeData'] = $orderDetails; // Added By HJ On 23-11-2019 For Soled \n data issue
    //Added By HJ On 01-02-2019 For Store Service Related Vehicle Type Data End
    $isDestinationAdded = "No";
    if ($destLatitude != "" && $destLongitude != "") {
        $isDestinationAdded = "Yes";
    }
    $user_available_balance_wallet = $generalobj->get_user_available_balance($iUserId, "Rider", true);
    $walletDataArr = array();
    if (is_array($user_available_balance_wallet)) {
        $walletDataArr = $user_available_balance_wallet;
        $user_available_balance_wallet = $walletDataArr['CurrentBalance'];
        $Data['tUserWalletBalance'] = $walletDataArr['AutorizedWalletBalance'];
    }
    $UserDetail = get_value('register_user AS ru LEFT JOIN currency AS c ON c.vName=ru.vCurrencyPassenger', 'ru.vCurrencyPassenger,ru.vLang,c.Ratio,c.vSymbol', 'ru.iUserId', $iUserId);
    $userLanguageCode = $UserDetail[0]['vLang'];
    $ratio = $UserDetail[0]['Ratio'];
    $currency_vSymbol = $UserDetail[0]['vSymbol'];
    $userLanguageLabelsArr = getLanguageLabelsArr($userLanguageCode, "1", $iServiceId);
    $user_available_balance_wallet = $user_available_balance_wallet * $ratio;
    //Added By HJ On 10-06-2019 For Get User Out Standing Amount For Payment Method 2 Or 3 Start
    $userOutStandingAmt = 0;
    $outstandingIds = "";
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $getUserOutstandingAmount = getUserOutstandingAmount($iUserId, "iCabBookingId");
        $userOutStandingAmt = $getUserOutstandingAmount['fPendingAmount'];
        $outstandingIds = $getUserOutstandingAmount['iTripOutstandId'];
    }
    //Added By HJ On 10-06-2019 For Get User Out Standing Amount For Payment Method 2 Or 3 End
    /*     * ****** Checking wallet balance for system payment floe for method-2/method-3 ********** */
    if (($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') && $cashPayment == 'false') {
        $fareAmount = 0;
        if (isset($fareDetails['tripFareDetailsSaveArr']) && count($fareDetails['tripFareDetailsSaveArr']) > 0) {
            $minHour = - 1;
            if (!empty($fareDetails['tripFareDetailsSaveArr']['eFareTypeServices']) && $fareDetails['tripFareDetailsSaveArr']['eFareTypeServices'] == "Hourly" && count($fareDetails['tripFareDetailsSaveArr']['FareData']) > 0) {
                $minHour = $fareDetails['tripFareDetailsSaveArr']['FareData'][0]['MinimumHour'];
            }
            $fareAmount = $fareDetails['tripFareDetailsSaveArr']['subTotal'];
            //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir Start
            if ($vCouponCode != "") {
                $discValue = calculateCouponCodeValue($vCouponCode, $fareAmount, $ratio);
                if ($discValue > 0) {
                    $fareAmount = $fareAmount - $discValue;
                }
            }
            //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir End
            if ($minHour > 0) {
                $fareAmount = $fareAmount * $minHour;
            }
        }
        else if ($iVehicleTypeId > 0 && $isDestinationAdded == "Yes") {
            $Fare_data_New = calculateFareEstimateAll($vDuration, $vDistance, $iVehicleTypeId, $iUserId, 1, "", "", $vCouponCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $pickuplocationarr, $dropofflocationarr, "Yes", $eType, $scheduleDate);
            $fareAmount = $Fare_data_New[0]['total_fare_amount'];
            if ($Fare_data_New[0]['eRental'] == "Yes" && $Fare_data_New[0]['eRental_total_fare_value'] > 0 && !empty($_REQUEST["iRentalPackageId"]) && $_REQUEST["iRentalPackageId"] > 0) {
                $fareAmount = $Fare_data_New[0]['eRental_total_fare_value'];
                //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir Start
                if ($vCouponCode != "") {
                    $discValue = calculateCouponCodeValue($vCouponCode, $fareAmount, $ratio);
                    if ($discValue > 0) {
                        $fareAmount = $fareAmount - $discValue;
                    }
                }
                //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir End
                
            }
            if (!empty($Data['fTollPrice']) && $Data['fTollPrice'] > 0 && !empty($Data['eTollSkipped']) && strtoupper($Data['eTollSkipped']) == "NO") {
                $fareAmount = $fareAmount + $Data['fTollPrice'];
            }
        }
        else if ($iVehicleTypeId > 0 && $isDestinationAdded != "Yes" && $eType == 'Ride') {
            $Fare_data_New = calculateFareEstimateAll($vDuration, $vDistance, $iVehicleTypeId, $iUserId, 1, "", "", $vCouponCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $pickuplocationarr, $dropofflocationarr, "Yes", $eType, $scheduleDate);
            $fareAmount = $Fare_data_New[0]['total_fare_amount'];
            if ($Fare_data_New[0]['eRental'] == "Yes" && $Fare_data_New[0]['eRental_total_fare_value'] > 0 && !empty($_REQUEST["iRentalPackageId"]) && $_REQUEST["iRentalPackageId"] > 0) {
                $Fare_data_New[0]['fBufferAmount'] = 0;
                $fareAmount = $Fare_data_New[0]['eRental_total_fare_value'];
                //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir Start
                if ($vCouponCode != "") {
                    $discValue = calculateCouponCodeValue($vCouponCode, $fareAmount, $ratio);
                    if ($discValue > 0) {
                        $fareAmount = $fareAmount - $discValue;
                    }
                }
                //Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir End
                
            }
            $fBufferAmount = $Fare_data_New[0]['fBufferAmount'];
            if ($fBufferAmount > 0) {
                $fBufferAmount = $fBufferAmount * $ratio;
            }
            $fareAmount = $fareAmount + $fBufferAmount;
        }
        else if ($iVehicleTypeId > 0 && $eType == 'UberX') {
            $fareAmount = $iPrice * $ratio;
            if (!empty($minHour_ufx) && $minHour_ufx > 0) {
                $fareAmount = $fareAmount * $minHour_ufx;
            }
        }
        $fareAmount = $generalobj->setTwoDecimalPoint($fareAmount + $userOutStandingAmt); // Added By HJ On 10-06-2019 For Payment Flow 2/3
        if ($user_available_balance_wallet < $fareAmount && $eWalletIgnore == 'No') {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LOW_WALLET_AMOUNT";
            if (!empty($walletDataArr) && count($walletDataArr) > 0) {
                $auth_wallet_amount = strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['TotalAuthorizedAmount']) ? $walletDataArr['TotalAuthorizedAmount'] : 0) * $ratio));
                $returnArr['AUTH_AMOUNT'] = $auth_wallet_amount > 0 ? ($currency_vSymbol . ' ' . $auth_wallet_amount) : "";
                $returnArr['AUTH_AMOUNT_VALUE'] = $auth_wallet_amount > 0 ? $auth_wallet_amount : "";
                $returnArr['ORIGINAL_WALLET_BALANCE'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $ratio));
                $returnArr['ORIGINAL_WALLET_BALANCE_VALUE'] = strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $ratio));
            }
            $returnArr['CURRENT_JOB_EST_CHARGE'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint($fareAmount));
            $returnArr['CURRENT_JOB_EST_CHARGE_VALUE'] = strval($generalobj->setTwoDecimalPoint($fareAmount));
            $returnArr['WALLET_AMOUNT_NEEDED'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint($fareAmount - $user_available_balance_wallet));
            $returnArr['WALLET_AMOUNT_NEEDED_VALUE'] = strval($generalobj->setTwoDecimalPoint($fareAmount - $user_available_balance_wallet));
            if (!empty($walletDataArr) && count($walletDataArr) > 0 && $auth_wallet_amount > 0) {
                $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AUTH_AMT'];
                $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                    $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                }
                if (!empty($returnArr['AUTH_AMOUNT'])) {
                    $content_msg_low_balance = str_replace("###", $returnArr['AUTH_AMOUNT'], $content_msg_low_balance);
                }
                $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
            }
            else {
                $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AMT'];
                $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                    $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                }
                if (!empty($returnArr['CURRENT_JOB_EST_CHARGE'])) {
                    $content_msg_low_balance = str_replace("###", $returnArr['CURRENT_JOB_EST_CHARGE'], $content_msg_low_balance);
                }
                $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
            }
            if ($SYSTEM_PAYMENT_FLOW == 'Method-3') {
                $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "Yes";
            }
            else {
                $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "No";
            }
            setDataResponse($returnArr);
        }
        $Data['tEstimatedCharge'] = $fareAmount / $ratio;
    }
    /*     * ****** Checking wallet balance for system payment floe for method-2/method-3 ********** */
    // Payment Method 2 Flow End
    if ($eType == "Deliver") {
        $Data['iPackageTypeId'] = $iPackageTypeId;
        $Data['vReceiverName'] = $vReceiverName;
        $Data['vReceiverMobile'] = $vReceiverMobile;
        $Data['tPickUpIns'] = $tPickUpIns;
        $Data['tDeliveryIns'] = $tDeliveryIns;
        $Data['tPackageDetails'] = $tPackageDetails;
    }
    if ($eType == "Multi-Delivery") {
        $data_fare = calculateFareEstimateAllMultiDelivery($total_del_time, $total_del_dist, $iVehicleTypeId, $iUserId, 1, "", "", $vCouponCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", "", $eFlatTrip, $fFlatTripPrice, "", "", "Yes", $eType, $scheduleDate, "Sender", $eWalletDebitAllow);
        if (!empty($Data['fTollPrice']) && $Data['fTollPrice'] > 0 && !empty($Data['eTollSkipped']) && strtoupper($Data['eTollSkipped']) == "NO") {
            $data_fare[0]['TotalGenratedFare'] = $data_fare[0]['TotalGenratedFare'] + $Data['fTollPrice'] + $userOutStandingAmt; // $userOutStandingAmt Added By HJ On 10-06-2019 For Payment Flow 2/3
            
        }
        /*         * ****** Checking wallet balance for system payment floe for method-2/method-3 ********** */
        if (($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') && $cashPayment == 'false') {
            if ($user_available_balance_wallet < ($data_fare[0]['TotalGenratedFare'] * $ratio) && $eWalletIgnore == 'No') {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LOW_WALLET_AMOUNT";
                if (!empty($walletDataArr) && count($walletDataArr) > 0) {
                    $auth_wallet_amount = strval($generalobj->setTwoDecimalPoint($generalobj->setTwoDecimalPoint((isset($walletDataArr['TotalAuthorizedAmount']) ? $walletDataArr['TotalAuthorizedAmount'] : 0) * $ratio)));
                    $returnArr['AUTH_AMOUNT'] = $auth_wallet_amount > 0 ? ($currency_vSymbol . ' ' . $auth_wallet_amount) : "";
                    $returnArr['AUTH_AMOUNT_VALUE'] = $auth_wallet_amount > 0 ? $auth_wallet_amount : "";
                    $returnArr['ORIGINAL_WALLET_BALANCE'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $ratio));
                    $returnArr['ORIGINAL_WALLET_BALANCE_VALUE'] = strval($generalobj->setTwoDecimalPoint((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $ratio));
                }
                $returnArr['CURRENT_JOB_EST_CHARGE'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint(($data_fare[0]['TotalGenratedFare'] * $ratio)));
                $returnArr['CURRENT_JOB_EST_CHARGE_VALUE'] = strval($generalobj->setTwoDecimalPoint(($data_fare[0]['TotalGenratedFare'] * $ratio)));
                $returnArr['WALLET_AMOUNT_NEEDED'] = $currency_vSymbol . ' ' . strval($generalobj->setTwoDecimalPoint(($data_fare[0]['TotalGenratedFare'] * $ratio) - $user_available_balance_wallet));
                $returnArr['WALLET_AMOUNT_NEEDED_VALUE'] = strval($generalobj->setTwoDecimalPoint(($data_fare[0]['TotalGenratedFare'] * $ratio) - $user_available_balance_wallet));
                if (!empty($walletDataArr) && count($walletDataArr) > 0 && $auth_wallet_amount > 0) {
                    $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AUTH_AMT'];
                    $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                    if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                        $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                    }
                    if (!empty($returnArr['AUTH_AMOUNT'])) {
                        $content_msg_low_balance = str_replace("###", $returnArr['AUTH_AMOUNT'], $content_msg_low_balance);
                    }
                    $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                    $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
                }
                else {
                    $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AMT'];
                    $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                    if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                        $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                    }
                    if (!empty($returnArr['CURRENT_JOB_EST_CHARGE'])) {
                        $content_msg_low_balance = str_replace("###", $returnArr['CURRENT_JOB_EST_CHARGE'], $content_msg_low_balance);
                    }
                    $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                    $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
                }
                if ($SYSTEM_PAYMENT_FLOW == 'Method-3') {
                    $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "Yes";
                }
                else {
                    $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "No";
                }
                setDataResponse($returnArr);
            }
            $Data['tEstimatedCharge'] = $data_fare[0]['TotalGenratedFare'];
        }
        /*         * ****** Checking wallet balance for system payment floe for method-2/method-3 ********** */
        // echo "<pre>";print_r($data_fare);exit;
        $fTripGenerateFare = $data_fare[0]['TotalGenratedFare'];
        $Data['iFare'] = $data_fare[0]['iFare_Ori'];
        $Data['iBaseFare'] = $data_fare[0]['iBaseFare_AMT'];
        $Data['fPricePerMin'] = $data_fare[0]['FareOfMinutes_Ori'];
        $Data['fPricePerKM'] = $data_fare[0]['FareOfDistance_Ori'];
        $Data['fCommision'] = $data_fare[0]['fCommision_AMT'];
        $Data['fSurgePriceDiff'] = $data_fare[0]['fSurgePriceDiff_Ori'];
        $Data['fTax1'] = $data_fare[0]['fTax1_Ori'];
        $Data['fTax2'] = $data_fare[0]['fTax2_Ori'];
        $Data['fDiscount'] = $data_fare[0]['fDiscount_Ori'];
        $Data['vDiscount'] = $data_fare[0]['vDiscount'];
        $Data['fMinFareDiff'] = $data_fare[0]['fMinFareDiff_Ori'];
        $Data['fTripGenerateFare'] = $fTripGenerateFare;
        $Data['vDistance'] = $total_del_dist;
        $Data['vDuration'] = $total_del_time;
        //added by SP for rounding off on 7-11-2019 for multi delivery
        $Data['fRoundingAmount'] = $data_fare[0]['fRoundingAmount'];
        $Data['eRoundingType'] = $data_fare[0]['eRoundingType'];
    }
    if (!empty($Data['tEstimatedCharge']) && $Data['tUserWalletBalance'] > $Data['tEstimatedCharge'] && ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
        $Data['tUserWalletBalance'] = $Data['tEstimatedCharge'];
    }
    if ($eType == "UberX" && $SERVICE_PROVIDER_FLOW == "Provider" && $eServiceLocation == "Driver" && $action == "Add") {
        $provider_data = get_value('register_driver', 'eSelectWorkLocation,vWorkLocationLatitude,vWorkLocationLongitude,vWorkLocation,vLatitude,vLongitude,vLang', 'iDriverId', $iDriverId);
        if ($provider_data[0]['eSelectWorkLocation'] == "Dynamic") {
            $Data['vWorkLocationLatitude'] = $provider_data[0]['vLatitude'];
            $Data['vWorkLocationLongitude'] = $provider_data[0]['vLongitude'];
            $url_driver_geo = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $provider_data[0]['vLatitude'] . "," . $provider_data[0]['vLongitude'] . "&key=" . $GOOGLE_SEVER_API_KEY_WEB . "&language=" . $provider_data[0]['vLang'];
            try {
                $jsonfile_driver_geo = file_get_contents($url_driver_geo);
                $jsondata_driver_geo = json_decode($jsonfile_driver_geo);
                $source_address_driver_geo = $jsondata_driver_geo->results[0]->formatted_address;
                $Data['vWorkLocation'] = $source_address_driver_geo;
            }
            catch(ErrorException $ex) {
            }
        }
        else {
            $Data['vWorkLocation'] = $provider_data[0]['vWorkLocation'];
            $Data['vWorkLocationLatitude'] = $provider_data[0]['vWorkLocationLatitude'];
            $Data['vWorkLocationLongitude'] = $provider_data[0]['vWorkLocationLongitude'];
        }
        $Data['vSourceLatitude'] = $Data['vWorkLocationLatitude'];
        $Data['vSourceLongitude'] = $Data['vWorkLocationLongitude'];
        $Data['vSourceAddresss'] = $Data['vWorkLocation'];
        $Data['eSelectWorkLocation'] = $provider_data[0]['eSelectWorkLocation'];
    }
    if ($action == "Add") {
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            GetScheduleParam();
        }
        if ($iTripReasonId != "" || $vReasonTitle != "") {
            $Data['iTripReasonId'] = $iTripReasonId;
            $Data['vReasonTitle'] = $vReasonTitle;
            $Data['eTripReason'] = "Yes";
        }
        $Data['vBookingNo'] = $rand_num;
        $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'insert');
    }
    else {
        $Data['eStatus'] = "Pending";
        $Data['iCancelByUserId'] = "";
        $Data['vCancelReason'] = "";
        $where = " iCabBookingId = '" . $iCabBookingId . "'";
        $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'update', $where);
    }
    //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 Start
    if ($userOutStandingAmt > 0) {
        $obj->sql_query("UPDATE trip_outstanding_amount set eAuthoriseIdName='iCabBookingId',iAuthoriseId = '" . $id . "' WHERE iTripOutstandId IN ($outstandingIds)");
    }
    //Added By HJ On 11-06-2019 For Manage User Out Standing Record For Payment Method 2 Or 3 End
    if ($id > 0) {
        $returnArr["Action"] = "1";
        if ($eType == "UberX") {
            $returnArr['message'] = "LBL_BOOKING_SUCESS_NOTE";
        }
        else {
            $returnArr['message'] = $eType == "Deliver" ? "LBL_DELIVERY_BOOKED" : "LBL_RIDE_BOOKED";
        }
        /* $sql = "SELECT concat(vName,' ',vLastName) as senderName,vEmail,vPhone,vPhoneCode,vLang from  register_user  WHERE iUserId ='" . $iUserId . "'";
        
          $userdetail = $obj->MySQLSelect($sql);
        
          $sql = "SELECT concat(vName,' ',vLastName) as drivername,vEmail,vPhone,vcode,iDriverVehicleId,vLang from  register_driver  WHERE iDriverId ='" . $iDriverId . "'";
        
          $driverdetail = $obj->MySQLSelect($sql); */
        //added by SP for sms functionality on 15-7-2019 start
        $userdetail = $obj->MySQLSelect("SELECT concat(r.vName,' ',r.vLastName) as senderName,r.vEmail,r.vPhone,r.vLang,c.vPhoneCode FROM `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
        $driverdetail = $obj->MySQLSelect("SELECT concat(r.vName,' ',r.vLastName) as drivername,r.vEmail,r.iDriverVehicleId,r.vLang,r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iDriverId AND r.vCountry = c.vCountryCode");
        //added by SP for sms functionality on 15-7-2019 end
        $userPhoneNo = $userdetail[0]['vPhone'];
        $userPhoneCode = $userdetail[0]['vPhoneCode'];
        $UserLang = $userdetail[0]['vLang'];
        $DriverPhoneNo = $driverdetail[0]['vPhone'];
        //$DriverPhoneCode = $driverdetail[0]['vcode'];
        $DriverPhoneCode = $driverdetail[0]['vPhoneCode'];
        $DriverLang = $driverdetail[0]['vLang'];
        $Data1['vRider'] = $userdetail[0]['senderName'];
        $Data1['vDriver'] = $driverdetail[0]['drivername'];
        $Data1['vDriverMail'] = $driverdetail[0]['vEmail'];
        $Data1['vRiderMail'] = $userdetail[0]['vEmail'];
        $Data1['vSourceAddresss'] = $pickUpLocAdd;
        //added by SP to change date format in mail on 07-10-2019 start
        //$Data1['dBookingdate'] = date('Y-m-d H:i', strtotime($Booking_Date_Time));
        $dBookingDate_new_mail = date("jS F, Y", strtotime($Booking_Date_Time));
        $dBookingDate_new_mail_time = date("h:i A", strtotime($Booking_Date_Time));
        $Data1['dBookingdate'] = $dBookingDate_new_mail . " " . $userLanguageLabelsArr['LBL_AT_TXT'] . " " . $dBookingDate_new_mail_time;
        //added by SP to change date format in mail on 07-10-2019 end
        if ($action == "Add") {
            $Data1['vBookingNo'] = $rand_num;
        }
        else {
            $BookingNo = get_value('cab_booking', 'vBookingNo', 'iCabBookingId', $iCabBookingId, '', 'true');
            $Data1['vBookingNo'] = $BookingNo;
        }
        $query = "SELECT vLicencePlate FROM driver_vehicle WHERE iDriverVehicleId=" . $iVehicleTypeId;
        $db_driver_vehicles = $obj->MySQLSelect($query);
        if ($eType == "UberX") {
            $sendMailfromDriver = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER_APP_SP", $Data1);
        }
        else {
            $sendMailfromDriver = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER_APP", $Data1);
            $sendMailfromUser = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_APP", $Data1);
        }
        $maildata['DRIVER_NAME'] = $Data1['vDriver'];
        //$maildata['PLATE_NUMBER'] = $db_driver_vehicles[0]['vLicencePlate'];
        $maildata['BOOKING_DATE'] = $Booking_Date;
        $maildata['BOOKING_TIME'] = $Booking_Time;
        $maildata['BOOKING_NUMBER'] = $Data1['vBookingNo'];
        $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_APP", $maildata, "", $UserLang);
        $UsersendMessage = $generalobj->sendSystemSms($userPhoneNo, $userPhoneCode, $message_layout); //added by SP for sms functionality on 15-7-2019
        /* $UsersendMessage = $generalobj->sendUserSMS($userPhoneNo, $userPhoneCode, $message_layout, "");
        
          if ($UsersendMessage == 0) {
        
          //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
        
          $isdCode = $SITE_ISD_CODE;
        
          $userPhoneCode = $isdCode;
        
          $UsersendMessage = $generalobj->sendUserSMS($userPhoneNo, $userPhoneCode, $message_layout, "");
        
          } */
        if ($eType == "UberX") {
            $maildata1['PASSENGER_NAME'] = $Data1['vRider'];
            $maildata1['BOOKING_DATE'] = $Booking_Date;
            $maildata1['BOOKING_TIME'] = $Booking_Time;
            $maildata1['BOOKING_NUMBER'] = $Data1['vBookingNo'];
            $DRIVER_SMS_TEMPLATE = ($eType == "UberX") ? "DRIVER_SEND_MESSAGE_SP" : "DRIVER_SEND_MESSAGE";
            $message_layout = $generalobj->send_messages_user($DRIVER_SMS_TEMPLATE, $maildata1, "", $DriverLang);
            $DriversendMessage = $generalobj->sendSystemSms($DriverPhoneNo, $DriverPhoneCode, $message_layout); //added by SP for sms functionality on 15-7-2019
            /* $DriversendMessage = $generalobj->sendUserSMS($DriverPhoneNo, $DriverPhoneCode, $message_layout, "");
            
              if ($DriversendMessage == 0) {
            
              //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
            
              $isdCode = $SITE_ISD_CODE;
            
              $DriverPhoneCode = $isdCode;
            
              $UsersendMessage = $generalobj->sendUserSMS($DriverPhoneNo, $DriverPhoneCode, $message_layout, "");
            
              } */
        }
    }
    else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == 'displayFare') {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $tableName = $userType != "Driver" ? "register_user" : "register_driver";
    $iMemberId_KEY = $userType != "Driver" ? "iUserId" : "iDriverId";
    if ($iTripId == "") {
        $iTripId = get_value($tableName, 'iTripId', $iMemberId_KEY, $iMemberId, '', 'true');
    }
    //Added By HJ On 23-01-2020 For Optimized Code Start
    $getTripData = $obj->MySQLSelect("SELECT vTripPaymentMode,eType FROM trips WHERE iTripId='" . $iTripId . "'");
    //echo "<pre>";print_r($getTripData);die;
    $vTripPaymentMode = "Cash";
    $eType = "Ride";
    if (count($getTripData) > 0) {
        $vTripPaymentMode = $getTripData[0]['vTripPaymentMode'];
        $eType = $getTripData[0]['eType'];
    }
    //$vTripPaymentMode = get_value('trips', 'vTripPaymentMode', 'iTripId', $iTripId, '', 'true');
    //$eType = get_value('trips', 'eType', 'iTripId', $iTripId, '', 'true');
    //Added By HJ On 23-01-2020 For Optimized Code End
    //echo $ENABLE_TIP_MODULE;die;
    if ($vTripPaymentMode == "Card" && $eType == "Ride") {
        $result_fare['ENABLE_TIP_MODULE'] = $ENABLE_TIP_MODULE;
    }
    else {
        $result_fare['ENABLE_TIP_MODULE'] = "No";
    }
    $result_fare['FormattedTripDate'] = date('dS M Y \a\t h:i a', strtotime($result_fare[0]['tStartDate']));
    $result_fare['PayPalConfiguration'] = "No";
    $result_fare['DefaultCurrencyCode'] = "USD";
    $result_fare['PaypalFare'] = strval($result_fare[0]['TotalFare']);
    $result_fare['PaypalCurrencyCode'] = $vCurrencyCode;
    $result_fare['APP_TYPE'] = $APP_TYPE;
    $result_fare['APP_DESTINATION_MODE'] = $APP_DESTINATION_MODE;
    $result_fare['SYSTEM_PAYMENT_FLOW'] = $SYSTEM_PAYMENT_FLOW;
    $returnArr = gettrippricedetails($iTripId, $iMemberId, $userType, "DISPLAY");
    //echo "<pre>";print_r($returnArr);die;
    $result_fare = array_merge($result_fare, $returnArr);
    $returnArr['Action'] = "0";
    if (count($returnArr) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $result_fare;
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "getDriverRideHistory") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vFilterParam = isset($_REQUEST["vFilterParam"]) ? $_REQUEST["vFilterParam"] : ''; // Ride , Deliver Or UberX
    $date = isset($_REQUEST["date"]) ? $_REQUEST["date"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $date = $date . " " . "12:01:00";
    $date = date("Y-m-d H:i:s", strtotime($date));
    $serverTimeZone = date_default_timezone_get();
    $date = converToTz($date, $serverTimeZone, $vTimeZone, "Y-m-d");
    if ($vFilterParam == "" || $vFilterParam == NULL) {
        $vFilterParam = "";
    }
    ##  App Type Filtering ##
    $ssql = "";
    if ($vFilterParam != "") {
        if ($vFilterParam == "Deliver") {
            $ssql .= " AND tr.eType IN ('Deliver','Multi-Delivery') ";
        }
        else if ($vFilterParam == "eFly") { //added by SP for fly stations on 31-08-2019
            $ssql .= " AND tr.iFromStationId != '' AND tr.iToStationId != ''";
        }
        else {
            $ssql .= " AND tr.eType IN ('" . $vFilterParam . "') ";
        }
        if (checkFlyStationsModule() && $vFilterParam == 'Ride') {
            $ssql .= " AND tr.iFromStationId = '' AND tr.iToStationId = ''";
        }
    }
    ##  App Type Filtering ##
    $DriverDetail = get_value('register_driver', 'vCurrencyDriver,vLang', 'iDriverId', $iDriverId);
    $vCurrencyDriver = $DriverDetail[0]['vCurrencyDriver'];
    $vLanguage = $DriverDetail[0]['vLang'];
    $currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT ru.vImgName as vUserImage, tr.*, ru.vName,ru.vLastName FROM trips as tr,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.eSystem = 'General' AND tr.tTripRequestDate LIKE '" . $date . "%' AND ( tr.iActive='Finished' OR (tr.iActive='Canceled' AND (tr.fCancellationFare > 0 OR tr.fWalletDebit > 0)))   AND ru.iUserId=tr.iUserId $ssql ORDER By tr.iTripId DESC";
    $tripData = $obj->MySQLSelect($sql);
    $totalEarnings = $avgRating = 0;
    if (count($tripData) > 0) {
        for ($i = 0;$i < count($tripData);$i++) {
            /* added for rental */
            if ($tripData[$i]['iRentalPackageId'] > 0) {
                $tripData[$i]['eRental'] = "Yes";
            }
            else {
                $tripData[$i]['eRental'] = "";
            }
            /* End added for rental */
            $tripData[$i]['vImage'] = $tripData[$i]['vUserImage'];
            $iActive = $tripData[$i]['iActive'];
            if ($iActive == "Finished") {
                $iFare = $tripData[$i]['fTripGenerateFare'];
            }
            else {
                $iFare = $tripData[$i]['fCancellationFare'] + $tripData[$i]['fWalletDebit'];
            }
            //$iFare = $tripData[$i]['fTripGenerateFare'];
            $fCommision = $tripData[$i]['fCommision'];
            $fDiscount = $tripData[$i]['fDiscount'];
            $fTipPrice = $tripData[$i]['fTipPrice'];
            $fTollPrice = $tripData[$i]['fTollPrice'];
            $fTax1 = $tripData[$i]['fTax1'];
            $fTax2 = $tripData[$i]['fTax2'];
            $iActive = $tripData[$i]['iActive'];
            $fOutStandingAmount = $tripData[$i]['fOutStandingAmount'];
            // hotel panel changes
            $fHotelCommision = $tripData[$i]['fHotelCommision'];
            //$vRating1 = $tripData[$i]['vRating1'];
            $priceRatio = $tripData[$i]['fRatio_' . $vCurrencyDriver];
            $sql = "SELECT vRating1, vMessage FROM ratings_user_driver WHERE iTripId = '" . $tripData[$i]['iTripId'] . "' AND eUserType='Passenger'";
            $tripData_rating = $obj->MySQLSelect($sql);
            if (count($tripData_rating) > 0) {
                $tripData[$i]['vRating1'] = $tripData_rating[0]['vRating1'];
                $tripData[$i]['vMessage'] = $tripData_rating[0]['vMessage'];
                $vRating1 = $tripData_rating[0]['vRating1'];
            }
            else {
                $tripData[$i]['vRating1'] = "0";
                $tripData[$i]['vMessage'] = "";
                $vRating1 = 0;
            }
            // hotel panel changes
            if (($iFare == "" || $iFare == 0) && $fDiscount > 0) {
                $incValue = ($fDiscount - $fCommision - $fTax1 - $fTax2 - $fOutStandingAmount - $fHotelCommision) + $fTipPrice;
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            }
            else if ($iFare != "" && $iFare > 0 && $iActive != "Canceled") {
                $incValue = ($iFare - $fCommision - $fTax1 - $fTax2 - $fOutStandingAmount - $fHotelCommision) + $fTipPrice;
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            }
            else if ($iFare != "" && $iFare > 0 && $iActive == "Canceled") {
                $incValue = ($iFare - $fCommision - $fTax1 - $fTax2) + $fTipPrice;
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            }
            $avgRating = $avgRating + $vRating1;
            $returnArr = getTripPriceDetails($tripData[$i]['iTripId'], $iDriverId, "Driver", "HISTORY");
            $tripData[$i] = array_merge($tripData[$i], $returnArr);
            $eType = $tripData[$i]['eType'];
            $iVehicleTypeId = $tripData[$i]['iVehicleTypeId'];
            $eFareType = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
            if ($eType == 'UberX' && $eFareType != "Regular") {
                $tripData[$i]['tDaddress'] = "";
            }
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $tripData;
        ## Checking For Cancel Trip ##
        $sqlc = "SELECT tr.*, ru.vName,ru.vLastName,ru.vImgName as vUserImage FROM trips as tr,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '" . $date . "%' AND tr.iActive='Canceled' AND ru.iUserId=tr.iUserId $ssql ORDER By tr.iTripId DESC";
        $tripcancelData = $obj->MySQLSelect($sqlc);
        if (count($tripcancelData) > 0) {
            for ($j = 0;$j < count($tripcancelData);$j++) {
                $returnArr_cancel = getTripPriceDetails($tripcancelData[$j]['iTripId'], $iDriverId, "Driver");
                $tripcancelData[$j] = array_merge($tripcancelData[$j], $returnArr_cancel);
                $tripcancelData[$j]['vImage'] = $tripcancelData[$j]['vUserImage'];
            }
            $returnArr['message1'] = $tripcancelData;
        }
        ## Checking For Cancel Trip ##
        
    }
    else {
        ## Checking For Cancel Trip ##
        $sqlc = "SELECT ru.vImgName as vUserImage,tr.*, ru.vName,ru.vLastName FROM trips as tr,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '" . $date . "%' AND tr.iActive='Canceled' AND ru.iUserId=tr.iUserId $ssql ORDER By tr.iTripId DESC";
        $tripcancelData = $obj->MySQLSelect($sqlc);
        if (count($tripcancelData) > 0) {
            for ($j = 0;$j < count($tripcancelData);$j++) {
                $returnArr_cancel = getTripPriceDetails($tripcancelData[$j]['iTripId'], $iDriverId, "Driver");
                $tripcancelData[$j] = array_merge($tripcancelData[$j], $returnArr_cancel);
                $tripcancelData[$j]['vImage'] = $tripcancelData[$j]['vUserImage'];
            }
            //echo "<pre>";print_r($tripcancelData);exit;
            $returnArr['message1'] = $tripcancelData;
            $returnArr['Action'] = "1";
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_DATA_AVAIL";
        }
        ## Checking For Cancel Trip ##
        
    }
    $returnArr['TotalEarning'] = strval(formatnum($totalEarnings));
    $returnArr['TripDate'] = date('l, dS M Y', strtotime($date));
    $returnArr['TripCount'] = strval(count($tripData));
    $returnArr['AvgRating'] = strval(getMemberAverageRating($iDriverId, "Driver", $date));
    $returnArr['CurrencySymbol'] = $currencySymbol;
    $returnArr['AppTypeFilterArr'] = AppTypeFilterArr($iDriverId, "Driver", $vLanguage);
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "checkBookings") {
    global $generalobj;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $bookingType = isset($_REQUEST["bookingType"]) ? $_REQUEST["bookingType"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $dataType = isset($_REQUEST["DataType"]) ? $_REQUEST["DataType"] : '';
    $vFilterParam = isset($_REQUEST["vFilterParam"]) ? $_REQUEST["vFilterParam"] : ''; // Ride , Deliver Or UberX
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    if ($UserType == "Passenger") {
        $vLang = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    }
    else {
        $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    }
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
    if ($vFilterParam == "" || $vFilterParam == NULL) {
        $vFilterParam = "";
    }
    ##  App Type Filtering ##
    $ssql3 = "";
    $ssql4 = "";
    if ($vFilterParam != "") {
        if ($vFilterParam == "Deliver") {
            $ssql3 .= " AND eType IN ('Deliver','Multi-Delivery') ";
            $ssql4 .= " AND cb.eType IN ('Deliver','Multi-Delivery') ";
        }
        else if ($vFilterParam == "eFly") { //added by SP for fly stations on 31-08-2019
            $ssql3 .= " AND iFromStationId != '' AND iToStationId != ''";
            $ssql4 .= " AND cb.iFromStationId != '' AND cb.iToStationId != ''";
        }
        else {
            $ssql3 .= " AND eType IN ('" . $vFilterParam . "') ";
            $ssql4 .= " AND cb.eType IN ('" . $vFilterParam . "') ";
        }
        if (checkFlyStationsModule() && $vFilterParam == 'Ride') {
            $ssql3 .= " AND iFromStationId = '' AND iToStationId = ''";
            $ssql4 .= " AND cb.iFromStationId = '' AND cb.iToStationId = ''";
        }
    }
    ##  App Type Filtering ##
    $per_page = 10;
    $additional_mins = $BOOKING_LATER_ACCEPT_AFTER_INTERVAL;
    $currDate = date('Y-m-d H:i:s');
    $currDate = date("Y-m-d H:i:s", strtotime($currDate . "-" . $additional_mins . " minutes"));
    $ssql1 = " AND dBooking_date > '" . $currDate . "'";
    $ssql2 = " AND cb.dBooking_date > '" . $currDate . "'";
    if ($UserType == "Driver") {
        if ($dataType == "PENDING") {
            $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Pending' AND iDriverId='" . $iDriverId . "'" . $ssql1 . $ssql3;
        }
        else {
            $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND ( eStatus = 'Accepted' || eStatus = 'Assign' ) AND iDriverId='" . $iDriverId . "'" . $ssql1 . $ssql3;
        }
    }
    else {
        $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE  iUserId='$iUserId' AND  ( eStatus = 'Assign' OR eStatus = 'Pending' OR eStatus = 'Accepted' OR eStatus = 'Declined' OR eStatus = 'Cancel') AND eCancelBy != 'Rider' $ssql1.$ssql3";
    }
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    if ($UserType == "Driver") {
        if ($dataType == "PENDING") {
            $sql = "SELECT cb.*, IF(cb.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLang . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = cb.iVehicleTypeId), '') as vVehicleType FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  cb.eStatus = 'Pending' AND cb.iDriverId='" . $iDriverId . "' $ssql2 $ssql4 ORDER BY cb.iCabBookingId DESC" . $limit;
        }
        else {
            $sql = "SELECT cb.*, IF(cb.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLang . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = cb.iVehicleTypeId), '') as vVehicleType FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  ( cb.eStatus = 'Accepted' || cb.eStatus = 'Assign' )  AND cb.iDriverId='" . $iDriverId . "' $ssql2 $ssql4 ORDER BY cb.iCabBookingId DESC" . $limit;
        }
    }
    else {
        $sql = "SELECT cb.*, IF(cb.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLang . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = cb.iVehicleTypeId), '') as vVehicleType FROM `cab_booking` as cb  WHERE cb.iUserId='$iUserId' AND ( cb.eStatus = 'Assign' OR cb.eStatus = 'Pending' OR eStatus = 'Accepted' OR eStatus = 'Declined'  OR eStatus = 'Cancel' ) AND eCancelBy != 'Rider' $ssql2  $ssql4 ORDER BY cb.iCabBookingId DESC" . $limit;
    }
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    if (count($Data) > 0) {
        $iVehicleTypeIds_str_ufx = $iVehicleCategoryIds_str_ufx = "";
        for ($i = 0;$i < count($Data);$i++) {
            //Added By HJ On 22-03-2019 For Get Cancelled Reason Start
            $iCancelReasonId = $Data[$i]['iCancelReasonId'];
            $vCancelReason = $Data[$i]['vCancelReason'];
            if ($iCancelReasonId > 0) {
                $vCancelReason = get_value('cancel_reason', "vTitle_" . $vLang, 'iCancelReasonId', $iCancelReasonId, '', 'true');
            }
            $Data[$i]['vCancelReason'] = $vCancelReason;
            //Added By HJ On 22-03-2019 For Get Cancelled Reason End
            $Data[$i]['dBooking_dateOrig'] = $Data[$i]['dBooking_date'];
            // Convert Into Timezone
            $tripTimeZone = $Data[0]['vTimeZone'];
            if ($tripTimeZone != "") {
                $serverTimeZone = date_default_timezone_get();
                $Data[$i]['dBooking_dateOrig'] = converToTz($Data[$i]['dBooking_dateOrig'], $tripTimeZone, $serverTimeZone);
            }
            // Convert Into Timezone
            $Data[$i]['dBooking_date'] = date('dS M Y \a\t h:i a', strtotime($Data[$i]['dBooking_date']));
            //added by SP for fly stations on 30-08-2019 start
            if (!empty($Data[$i]['iFromStationId']) && !empty($Data[$i]['iToStationId'])) {
                $Data[$i]['eFly'] = "Yes";
            }
            else {
                $Data[$i]['eFly'] = "No";
            }
            if ($Data[$i]['eType'] == 'Ride' && $Data[$i]['eFly'] == 'Yes') {
                $Data[$i]['eType'] = 'Fly';
            }
            //added by SP for fly stations on 30-08-2019 end
            $eType = $Data[$i]['eType'];
            //$iVehicleTypeId = $Data[$i]['iVehicleTypeId'];
            $Data[$i]['moreServices'] = "No";
            $Data[$i]['vServiceTitle'] = $Data[$i]['vVehicleType'];
            $Data[$i]['vServiceDetailTitle'] = $Data[$i]['vVehicleType'];
            if ($eType == "Ride") {
                if ($APP_TYPE != "Ride") {
                    $Data[$i]['vServiceTitle'] = $languageLabelsArr['LBL_RIDE'];
                    $Data[$i]['vServiceDetailTitle'] = $Data[$i]['vVehicleType'];
                }
                if ($Data[$i]['iRentalPackageId'] > 0) {
                    $Data[$i]['vServiceTitle'] = $languageLabelsArr['LBL_RENTAL_CATEGORY_TXT'];
                    $Data[$i]['vServiceDetailTitle'] = $languageLabelsArr['LBL_RENTAL_CATEGORY_TXT'] . " - " . $Data[$i]['vVehicleType'];
                }
            }
            else if ($eType == "Delivery" || $eType == "Deliver") {
                if ($APP_TYPE != "Delivery" || $APP_TYPE != "Deliver") {
                    $Data[$i]['vServiceTitle'] = $languageLabelsArr['LBL_DELIVERY'];
                    $Data[$i]['vServiceDetailTitle'] = $Data[$i]['vVehicleType'];
                }
            }
            if ($eType == 'UberX') {
                if ($Data[$i]['tVehicleTypeData'] != "" /* && $iVehicleTypeId == 0 */) {
                    $tVehicleTypeDataArr = (array)json_decode($Data[$i]['tVehicleTypeData']);
                    $Data[$i]['moreServices'] = "Yes";
                    if (!empty($Data[$i]['tVehicleTypeFareData'])) {
                        $tVehicleTypeFareDataArr = (array)json_decode($Data[$i]['tVehicleTypeFareData']);
                        $ParentVehicleCategoryId = isset($tVehicleTypeFareDataArr['ParentVehicleCategoryId']) ? $tVehicleTypeFareDataArr['ParentVehicleCategoryId'] : 0;
                        if ($ParentVehicleCategoryId == 0) {
                            $tVehicleTypeFareDataArr_fareArr = (array)($tVehicleTypeFareDataArr['FareData']);
                            if (count($tVehicleTypeFareDataArr_fareArr) > 0) {
                                $sql_parent_id = "SELECT (SELECT vcs.vCategory_" . $vLang . " FROM " . $sql_vehicle_category_table_name . " as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as vCategory FROM " . $sql_vehicle_category_table_name . " as vc, vehicle_type as vt WHERE vc.iVehicleCategoryId = vt.iVehicleCategoryId AND vt.iVehicleTypeId = '" . $tVehicleTypeFareDataArr_fareArr[0]->id . "'";
                                $parent_data_arr = $obj->MySQLSelect($sql_parent_id);
                                $Data[$i]['vCategory'] = $parent_data_arr[0]['vCategory'];
                            }
                        }
                        else {
                            $Data[$i]['ParentVehicleCategoryId'] = $ParentVehicleCategoryId;
                            $iVehicleCategoryIds_str_ufx = $iVehicleCategoryIds_str_ufx == "" ? $ParentVehicleCategoryId : $iVehicleCategoryIds_str_ufx . "," . $ParentVehicleCategoryId;
                        }
                        $Data[$i]['eFareTypeServices'] = $tVehicleTypeFareDataArr['eFareTypeServices'];
                    }
                    else {
                        if (count($tVehicleTypeDataArr) > 0) {
                            $tmpTVehicleTypeDataArr = (array)$tVehicleTypeDataArr[0];
                            $iVehicleTypeId = $tmpTVehicleTypeDataArr['iVehicleTypeId'];
                        }
                    }
                }
                $DisplayBookingDetails = array();
                $DisplayBookingDetails = DisplayBookingDetails($Data[$i]['iCabBookingId']);
                $Data[$i]['tDestAddress'] = "";
                $Data[$i]['selectedtime'] = $DisplayBookingDetails['selectedtime'];
                $Data[$i]['selecteddatetime'] = $DisplayBookingDetails['selecteddatetime'];
                $Data[$i]['SelectedFareType'] = $DisplayBookingDetails['SelectedFareType'];
                $Data[$i]['SelectedQty'] = $DisplayBookingDetails['SelectedQty'];
                $Data[$i]['SelectedPrice'] = strval($DisplayBookingDetails['SelectedPrice']);
                $Data[$i]['SelectedCurrencySymbol'] = $DisplayBookingDetails['SelectedCurrencySymbol'];
                $Data[$i]['SelectedCurrencyRatio'] = $DisplayBookingDetails['SelectedCurrencyRatio'];
                $Data[$i]['SelectedVehicle'] = $DisplayBookingDetails['SelectedVehicle'];
                $Data[$i]['SelectedCategory'] = $DisplayBookingDetails['SelectedCategory'];
                $Data[$i]['vVehicleType'] = $DisplayBookingDetails['SelectedVehicle'];
                $Data[$i]['vVehicleCategory'] = $DisplayBookingDetails['SelectedCategory'];
                $Data[$i]['SelectedCategoryId'] = $DisplayBookingDetails['SelectedCategoryId'];
                $Data[$i]['SelectedCategoryTitle'] = $DisplayBookingDetails['SelectedCategoryTitle'];
                $Data[$i]['SelectedCategoryDesc'] = $DisplayBookingDetails['SelectedCategoryDesc'];
                $Data[$i]['SelectedAllowQty'] = $DisplayBookingDetails['SelectedAllowQty'];
                $Data[$i]['SelectedPriceType'] = $DisplayBookingDetails['SelectedPriceType'];
                $Data[$i]['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $DisplayBookingDetails['ALLOW_SERVICE_PROVIDER_AMOUNT'];
                if ($SERVICE_PROVIDER_FLOW == "Provider" && $Data[$i]['eType'] == "UberX" && $Data[$i]['eServiceLocation'] == "Driver") {
                    $Data[$i]['vSourceAddresss'] = $Data[$i]['vWorkLocation'];
                }
            }
            /* added for rental */
            if ($Data[$i]['iRentalPackageId'] > 0) {
                $rentalData = getRentalData($Data[$i]['iRentalPackageId']);
                $Data[$i]['vPackageName'] = $rentalData[0]['vPackageName_' . $vLang];
            }
            else {
                $Data[$i]['vPackageName'] = "";
            }
            /* end added for rental */
        }
        if (!empty($iVehicleCategoryIds_str_ufx)) {
            $sql_parent_cat_name = "SELECT vcs.vCategory_" . $vLang . " as vCategory, vcs.iVehicleCategoryId FROM " . $sql_vehicle_category_table_name . " as vcs WHERE vcs.iVehicleCategoryId IN ($iVehicleCategoryIds_str_ufx)";
            $parent_data_arr = $obj->MySQLSelect($sql_parent_cat_name);
            $parent_cat_arr = array();
            if (count($parent_data_arr) > 0) {
                for ($i = 0;$i < count($parent_data_arr);$i++) {
                    $parent_cat_arr[$parent_data_arr[$i]['iVehicleCategoryId']] = $parent_data_arr[$i]['vCategory'];
                }
                for ($i = 0;$i < count($Data);$i++) {
                    if ($Data[$i]['eType'] == "UberX") {
                        if (!empty($parent_cat_arr[$Data[$i]['ParentVehicleCategoryId']])) {
                            $Data[$i]['vServiceTitle'] = $parent_cat_arr[$Data[$i]['ParentVehicleCategoryId']];
                        }
                        if (!empty($Data[$i]['ParentVehicleCategoryId']) && !empty($Data[$i]['eFareTypeServices']) && $Data[$i]['eFareTypeServices'] == "Fixed" && !empty($parent_cat_arr[$Data[$i]['ParentVehicleCategoryId']])) {
                            $Data[$i]['vServiceDetailTitle'] = $parent_cat_arr[$Data[$i]['ParentVehicleCategoryId']];
                        }
                        else if (!empty($parent_cat_arr[$Data[$i]['ParentVehicleCategoryId']])) {
                            $Data[$i]['vServiceDetailTitle'] = $parent_cat_arr[$Data[$i]['ParentVehicleCategoryId']] . " - " . $Data[$i]['vVehicleType'];
                        }
                    }
                }
            }
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;
        if ($TotalPages > $page) {
            $returnArr['NextPage'] = $page + 1;
        }
        else {
            $returnArr['NextPage'] = "0";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    $returnArr['AppTypeFilterArr'] = AppTypeFilterArr($iUserId, $UserType, $vLang);
    setDataResponse($returnArr);
}
#############################Check Source Location and get Vehicle Deteails#################################################################
if ($type == "CheckSourceLocationState") {
    global $generalobj, $tconfig;
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $CurrentCabGeneralType = isset($_REQUEST["CurrentCabGeneralType"]) ? $_REQUEST["CurrentCabGeneralType"] : '';
    $APP_TYPE = $CurrentCabGeneralType;
    if ($APP_TYPE == "Delivery" || $APP_TYPE == "Deliver") {
        $ssql .= " AND eType = 'Deliver'";
    }
    else if ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Deliver") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
    }
    else if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Ride-Deliver-UberX") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride' OR eType = 'UberX')";
    }
    else {
        $ssql .= " AND eType = '" . $APP_TYPE . "'";
    }
    $pickuplocationarr = array(
        $PickUpLatitude,
        $PickUpLongitude
    );
    $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
    if ($allowed_ans == "No") {
        $returnArr['Action'] = "1";
        setDataResponse($returnArr);
    }
    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
    $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) $ssql ORDER BY iVehicleTypeId ASC";
    $vehicleTypes = $obj->MySQLSelect($sql23);
    $Vehicle_Str = "";
    if (count($vehicleTypes) > 0) {
        for ($i = 0;$i < count($vehicleTypes);$i++) {
            $Vehicle_Str .= $vehicleTypes[$i]['iVehicleTypeId'] . ",";
        }
        $Vehicle_Str = substr($Vehicle_Str, 0, -1);
    }
    $selectedCarTypeID_Arr = explode(",", $selectedCarTypeID);
    $Vehicle_Str_Arr = explode(",", $Vehicle_Str);
    if ($selectedCarTypeID_Arr === array_intersect($selectedCarTypeID_Arr, $Vehicle_Str_Arr) && $Vehicle_Str_Arr === array_intersect($Vehicle_Str_Arr, $selectedCarTypeID_Arr)) {
        $returnArr['Action'] = "0";
    }
    else {
        $returnArr['Action'] = "1";
    }
    setDataResponse($returnArr);
}
#############################Check Source Location and get Vehicle Deteails#################################################################
###########################################################################
if ($type == "cancelBooking") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $Reason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    $iCancelReasonId = isset($_REQUEST["iCancelReasonId"]) ? $_REQUEST["iCancelReasonId"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $sqldata = "SELECT eStatus FROM `cab_booking` WHERE iCabBookingId ='" . $iCabBookingId . "'";
    $BookingData = $obj->MySQLSelect($sqldata);
    $BookingStatus = $BookingData[0]['eStatus'];
    if ($BookingStatus == "Cancel") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_MANAUL_BOOKING_CANCELLED_MSG";
        $returnArr['DO_RELOAD'] = "Yes";
        setDataResponse($returnArr);
    }
    $where = " iCabBookingId = '$iCabBookingId'";
    $data_update_booking['eStatus'] = "Cancel";
    $data_update_booking['vCancelReason'] = $Reason;
    $data_update_booking['iCancelByUserId'] = $iUserId;
    $data_update_booking['dCancelDate'] = @date("Y-m-d H:i:s");
    $data_update_booking['eCancelBy'] = $userType == "Driver" ? $userType : "Rider";
    $data_update_booking['iCancelReasonId'] = $iCancelReasonId;
    $id = $obj->MySQLQueryPerform("cab_booking", $data_update_booking, 'update', $where);
    $sql = "select cb.iUserId, cb.iDriverId,cb.vBookingNo,cb.eType,concat(rd.vName,' ',rd.vLastName) as DriverName,concat(ru.vName,' ',ru.vLastName) as RiderName,ru.vEmail as vRiderMail,ru.vPhone as RiderPhone,ru.vPhoneCode as RiderPhoneCode,rd.vPhone as DriverPhone,rd.vCode as DriverPhoneCode,rd.vEmail as vDriverMail,rd.vLang as driverlang, ru.vLang as riderlang ,cb.vSourceAddresss,cb.tDestAddress,cb.dBooking_date,cb.vCancelReason,cb.dCancelDate from cab_booking cb

        left join register_driver rd on rd.iDriverId = cb.iDriverId

        left join register_user ru on ru.iUserId = cb.iUserId where cb.iCabBookingId = '$iCabBookingId'"; //added by SP cb.iUserId, cb.iDriverId for getting phoneocde in country table on 15-7-2019
    $data_cab = $obj->MySQLSelect($sql);
    $RiderPhoneNo = $data_cab[0]['RiderPhone'];
    $RiderPhoneCode = $data_cab[0]['RiderPhoneCode'];
    $UserLang = $data_cab[0]['riderlang'];
    $DriverPhoneNo = $data_cab[0]['DriverPhone'];
    $DriverPhoneCode = $data_cab[0]['DriverPhoneCode'];
    $DriverLang = $data_cab[0]['driverlang'];
    $eType = $data_cab[0]['eType'];
    $Data['vBookingNo'] = $data_cab[0]['vBookingNo'];
    $Data['DriverName'] = $data_cab[0]['DriverName'];
    $Data['RiderName'] = $data_cab[0]['RiderName'];
    $Data['vDriverMail'] = $data_cab[0]['vDriverMail'];
    $Data['vRiderMail'] = $data_cab[0]['vRiderMail'];
    $Data['vSourceAddresss'] = $data_cab[0]['vSourceAddresss'];
    $Data['tDestAddress'] = $data_cab[0]['tDestAddress'];
    $Data['dBookingdate'] = date('Y-m-d H:i', strtotime($data_cab[0]['dBooking_date']));
    $Data['vCancelReason'] = $Reason;
    $Data['dCancelDate'] = $data_cab[0]['dCancelDate'];
    //Added By HJ On 12-08-2019 For Reset User Outstanding Amount As Per Discuss With BM Mam Bug - 6730 Start
    if ($iCabBookingId > 0) {
        $whereCabId = "iAuthoriseId='" . $iCabBookingId . "'";
        $outstanding_update['eAuthoriseIdName'] = "No";
        $outstanding_update['iAuthoriseId'] = "0";
        $obj->MySQLQueryPerform("trip_outstanding_amount", $outstanding_update, 'update', $whereCabId);
    }
    //Added By HJ On 12-08-2019 For Reset User Outstanding Amount As Per Discuss With BM Mam Bug - 6730 End
    if ($userType == "Driver") {
        $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN", $Data);
    }
    if ($userType != "Driver") {
        $generalobj->send_email_user("MANUAL_CANCEL_TRIP_DRIVER", $Data);
        $DriverData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = '" . $data_cab[0]['iDriverId'] . "' AND r.vCountry = c.vCountryCode");
        $UserPhoneCode = $DriverData[0]['vPhoneCode'];
        $message_layout = $generalobj->send_messages_user('BOOKING_CANCEL_BYRIDER_MESSAGE', $Data, "", $UserLang);
        $UsersendMessage = $generalobj->sendSystemSms($DriverPhoneNo, $UserPhoneCode, $message_layout);
    }
    if ($APP_TYPE == "UberX" || $eType == "UberX") {
        $USER_EMAIL_TEMPLATE = ($userType == "Driver") ? "MANUAL_BOOKING_CANCEL_BYDRIVER_SP" : "MANUAL_BOOKING_CANCEL_BYRIDER_SP";
        $generalobj->send_email_user($USER_EMAIL_TEMPLATE, $Data);
        $UserPhoneNo = ($userType == "Driver") ? $RiderPhoneNo : $DriverPhoneNo;
        //$UserPhoneNo = ($userType == "Driver") ? $DriverPhoneNo : $RiderPhoneNo; //added by SP for sms functionality on 15-7-2019, before its wrongly taken so i have changed it
        $UserPhoneCode = ($userType == "Driver") ? $RiderPhoneCode : $DriverPhoneCode;
        $USER_SMS_TEMPLATE = ($userType == "Driver") ? "BOOKING_CANCEL_BYDRIVER_MESSAGE_SP" : "BOOKING_CANCEL_BYRIDER_MESSAGE_SP";
        $message_layout = $generalobj->send_messages_user($USER_SMS_TEMPLATE, $Data, "", $UserLang);
        //added by SP for sms functionality on 15-7-2019 start
        if ($userType == "Driver") {
            $DriverData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = '" . $data_cab[0]['iDriverId'] . "' AND r.vCountry = c.vCountryCode");
            //$UserPhoneCode = $DriverData[0]['vPhoneCode'];
            $DriverPhoneCode = $DriverData[0]['vPhoneCode'];
        }
        else {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM `register_user` AS r, `country` AS c WHERE r.iUserId = '" . $data_cab[0]['iUserId'] . "' AND r.vCountry = c.vCountryCode");
            //$UserPhoneCode = $passengerData[0]['vPhoneCode'];
            $RiderPhoneCode = $passengerData[0]['vPhoneCode'];
        }
        $UserPhoneCode = ($userType == "Driver") ? $RiderPhoneCode : $DriverPhoneCode;
        $UsersendMessage = $generalobj->sendSystemSms($UserPhoneNo, $UserPhoneCode, $message_layout);
        //added by SP for sms functionality on 15-7-2019 end
        /* $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
        
          if ($UsersendMessage == 0) {
        
          $isdCode = $SITE_ISD_CODE;
        
          $UserPhoneCode = $isdCode;
        
          $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
        
          } */
    }
    if ($id) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_BOOKING_CANCELED";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "DeclineTripRequest") {
    $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    $vMsgCode = isset($_REQUEST["vMsgCode"]) ? $_REQUEST["vMsgCode"] : '';
    $sql = "SELECT iDriverRequestId,eAcceptAttempted FROM `driver_request` WHERE iDriverId = '" . $driver_id . "' AND iUserId = '" . $passenger_id . "' AND iTripId = '0' AND vMsgCode='" . $vMsgCode . "' AND eAcceptAttempted = 'No'";
    $db_sql = $obj->MySQLSelect($sql);
    if (count($db_sql) > 0) {
        $request_count = UpdateDriverRequest2($driver_id, $passenger_id, "0", "Decline", $vMsgCode, "No");
    }
    else {
        $request_count = 0;
    }
    echo $request_count;
}
#####################################Generate Review Mode Login For IOS###########################################################
if ($type == "generateReviewModeLogin") {
    $iGcmRegId = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    if ($APPSTORE_MODE_IOS == "Review") {
        $sql = "SELECT iUserId from register_user WHERE eReviewModeLogin = 'Yes'";
        $ReviewModeClient = $obj->MySQLSelect($sql);
        if (count($ReviewModeClient) == 0) {
            $eReftype = "Rider";
            $currenttime = time();
            $vCountry = "US";
            $sql = "SELECT vPhoneCode,vTimeZone FROM country WHERE vCountryCode = '" . $vCountry . "'";
            $db_country_code = $obj->MySQLSelect($sql);
            $vPhoneCode = $db_country_code[0]['vPhoneCode'];
            $vTimeZone = $db_country_code[0]['vTimeZone'];
            $vPhone = "9876543210";
            $DataR['vRefCode'] = $generalobj->ganaraterefercode($eReftype);
            $DataR['iRefUserId'] = '';
            $DataR['eRefType'] = '';
            $DataR['vName'] = "";
            $DataR['vLang'] = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            $DataR['vLastName'] = "";
            $DataR['vPassword'] = $generalobj->encrypt_bycrypt($currenttime);
            $DataR['vEmail'] = "review" . $currenttime . "@demo.com";
            $DataR['vPhone'] = $vPhone;
            $DataR['vCountry'] = $vCountry;
            $DataR['vPhoneCode'] = $vPhoneCode;
            $DataR['vZip'] = '121212';
            $DataR['vInviteCode'] = "";
            $DataR['vCurrencyPassenger'] = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
            $DataR['dRefDate'] = @date('Y-m-d H:i:s');
            $DataR['tRegistrationDate'] = @date('Y-m-d H:i:s');
            $DataR['eDeviceType'] = "Ios";
            $DataR['eSignUpType'] = "Normal";
            $DataR['eStatus'] = 'Active';
            $DataR['eEmailVerified'] = 'Yes';
            $DataR['ePhoneVerified'] = 'Yes';
            $DataR['eReviewModeLogin'] = 'Yes';
            $random = substr(md5(rand()) , 0, 7);
            $DataR['tDeviceSessionId'] = session_id() . time() . $random;
            $DataR['tSessionId'] = session_id() . time();
            $DataR['iGcmRegId'] = $iGcmRegId;
            $id = $obj->MySQLQueryPerform("register_user", $DataR, 'insert');
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($id, "", "");
            setDataResponse($returnArr);
        }
        else {
            $iUserId = $ReviewModeClient[0]['iUserId'];
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
            setDataResponse($returnArr);
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "You are not allow to skip login";
        setDataResponse($returnArr);
    }
}
#####################################Generate Review Mode Login For IOS###########################################################
############################Send Sms Twilio END################################
if ($type == "updateDriverStatus") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Status_driver = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';
    $isUpdateOnlineDate = isset($_REQUEST["isUpdateOnlineDate"]) ? $_REQUEST["isUpdateOnlineDate"] : '';
    $isOnlineSwitchPressed = isset($_REQUEST["isOnlineSwitchPressed"]) ? $_REQUEST["isOnlineSwitchPressed"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';
    $iGCMregID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    //echo $Status_driver;die;
    //echo "<pre>";print_r($Status_driver);die;
    ############################ blocking code ##############################
    //if (strtoupper(PACKAGE_TYPE) == "SHARK" && $isOnlineSwitchPressed != "true" && $Status_driver != "Available") { //Commented By HJ On 11-07-2019 As Per Discuss With DT (Says : No any solution of this case). Bug - 6673
    if (strtoupper(PACKAGE_TYPE) == "SHARK" && $Status_driver == "Available") {
        $BlockData = getBlockData("Driver", $iDriverId);
        //echo "<pre>";print_r($BlockData);die;
        if (!empty($BlockData) || $BlockData != "") {
            setDataResponse($BlockData);
        }
    }
    ############################ blocking code ##############################
    /* For DriverSubscription added by SP start */
    if ($Status_driver == "Available" && checkDriverSubscriptionModule()) {
        $returnSubStatus = 0;
        $returnSubStatus = checkDriverSubscribed($iDriverId);
        if ($returnSubStatus == 1) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PENDING_MIXSUBSCRIPTION";
            setDataResponse($returnArr);
        }
        else if ($returnSubStatus == 2) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "PENDING_SUBSCRIPTION";
            setDataResponse($returnArr);
        }
    }
    /* For DriverSubscription added by SP end */
    /* Added By PJ for Chcek DOC expiry validation */
    $dDocStatusCheck = $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED;
    if ($dDocStatusCheck == 'Yes') {
        $sql1 = "SELECT DISTINCT(rd.iDriverId) as driverid

        FROM document_list as doc Left Join company as cmp  ON doc.doc_userid = cmp.iCompanyId 

        Left Join register_driver as rd  ON doc.doc_userid = rd.iDriverId 

        Left Join document_master as dm  ON doc.doc_masterid = dm.doc_masterid 

        WHERE doc.ex_date < CURDATE() AND doc.ex_date != '0000-00-00' AND  dm.status !='Deleted' AND rd.iDriverId IS NOT NULL ";
        $data_ex_docs = $obj->MySQLSelect($sql1);
        $docExpDrivers = [];
        foreach ($data_ex_docs as $key => $data_ex_doc) {
            $docExpDrivers[] = $data_ex_doc['driverid'];
        }
        if (in_array($iDriverId, $docExpDrivers)) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_DRIVER_DOC_EXPIRED";
            setDataResponse($returnArr);
        }
    }
    /* End Chcek DOC expiry validation */
    if ($Status_driver == "Available") {
        checkmemberemailphoneverification($iDriverId, "Driver");
    }
    if ($iDriverId == '') {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
    $driverDetail = get_value('register_driver AS rd LEFT JOIN currency AS c ON c.vName=rd.vCurrencyDriver', 'rd.vCurrencyDriver,c.Ratio,c.vSymbol,vLang,iGcmRegId', 'rd.iDriverId', $iDriverId);
    $vCurrencyDriver = "USD";
    $ratio = 1;
    $currencySymbol = "$";
    $GCMID = "";
    //echo $iDriverId;die;
    if (count($driverDetail) > 0) {
        $vLang = $driverDetail[0]['vLang'];
        $vCurrencyDriver = $driverDetail[0]['vCurrencyDriver'];
        $ratio = $driverDetail[0]['Ratio'];
        $currencySymbol = $driverDetail[0]['vSymbol'];
        $GCMID = $driverDetail[0]['iGcmRegId'];
    }
    //$GCMID = get_value('register_driver', 'iGcmRegId', 'iDriverId', $iDriverId, '', 'true');
    //echo $GCMID."=====".$iGCMregID;die;
    if ($GCMID != "" && $iGCMregID != "" && $GCMID != $iGCMregID) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        setDataResponse($returnArr);
    }
    $returnArr['Enable_Hailtrip'] = $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
    if ($COMMISION_DEDUCT_ENABLE == 'Yes' && ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Cash-Card") && $Status_driver == "Available") {
        //$vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
        if ($vLang == "" || $vLang == NULL) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
        //$WALLET_MIN_BALANCE=$generalobj->getConfigurations("configurations","WALLET_MIN_BALANCE");
        //echo $WALLET_MIN_BALANCE."===".$user_available_balance;die;
        if ($WALLET_MIN_BALANCE > $user_available_balance) {
            // $returnArr['Action'] = "0";
            $returnArr['message'] = "REQUIRED_MINIMUM_BALNCE";
            if ($APP_TYPE == "UberX") {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio) , $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE_UBERX']);
            }
            else {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio) , $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE']);
            }
            if ($APP_PAYMENT_MODE == "Cash") {
                if ($Status_driver == "Available") {
                    $returnArr['Action'] = "0";
                    setDataResponse($returnArr);
                }
            }
        }
        $returnArr['Enable_Hailtrip'] = $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "Yes";
    }
    if ($COMMISION_DEDUCT_ENABLE == 'No' && ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Cash-Card") && $APP_TYPE != "UberX") {
        $returnArr['Enable_Hailtrip'] = $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "Yes";
    }
    if ($COMMISION_DEDUCT_ENABLE == 'No' && $APP_PAYMENT_MODE == "Card" && $APP_TYPE == "Ride") {
        $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "Yes";
    }
    if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
        $returnArr['Enable_Hailtrip'] = "No";
    }
    $ssql = "";
    $CheckRideDeliveryFeatureDisable_Arr = CheckRideDeliveryFeatureDisable();
    //$eShowRideVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowRideVehicles'];
    //$eShowDeliveryVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliveryVehicles'];
    $RideDeliveryBothFeatureDisable = $CheckRideDeliveryFeatureDisable_Arr['RideDeliveryBothFeatureDisable'];
    $sql = "SELECT make.vMake, model.vTitle, dv.*, rd.iDriverVehicleId as iSelectedVehicleId,rd.iDestinationCount,rd.tDestinationModifiedDate,rd.tOnline FROM `driver_vehicle` dv, make, model, register_driver as rd WHERE dv.iDriverId='$iDriverId' AND rd.iDriverId='$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'" . $ssql;
    //echo $sql."<br>";
    $Data_Car = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($Data_Car);die;
    //echo count($Data_Car);die;
    if (count($Data_Car) > 0) {
        if (count($Data_Car) == 1 && $Data_Car[0]['eType'] == "UberX") {
            $returnArr['Enable_Hailtrip'] = $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
            if ($APP_TYPE != "UberX") {
                $RideDeliveryBothFeatureDisable = "Yes";
                $returnArr['message'] = ($RideDeliveryBothFeatureDisable == "No") ? "LBL_PROVIDER_NO_SERVICE_ENABLE_TXT" : "LBL_ONLY_OTHER_SERVICE_ENABLE_TXT";
            }
        }
        else {
            $status = "CARS_NOT_ACTIVE";
            $i = 0;
            //echo "<pre>";print_R($Data_Car);die;
            $selectedVehicle = 0;
            while (count($Data_Car) > $i) {
                $eStatus = $Data_Car[$i]['eStatus'];
                if ($eStatus == "Active") {
                    $status = "CARS_AVAIL";
                }
                if ($Data_Car[0]['iSelectedVehicleId'] == $Data_Car[$i]['iDriverVehicleId']) {
                    $selectedVehicle = 1;
                }
                if (($Data_Car[0]['iSelectedVehicleId'] == $Data_Car[$i]['iDriverVehicleId']) && $returnArr['Enable_Hailtrip'] == "Yes") {
                    if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
                        $returnArr['Enable_Hailtrip'] = checkHailRideEnable($Data_Car[$i]['vCarType']);
                    }
                }
                if (($Data_Car[0]['iSelectedVehicleId'] == $Data_Car[$i]['iDriverVehicleId']) && $returnArr['ENABLE_DRIVER_DESTINATIONS'] == "Yes") {
                    $returnArr['ENABLE_DRIVER_DESTINATIONS'] = $ENABLE_DRIVER_DESTINATIONS;
                }
                //added by SP for fly vehicle do not enable hail and end of day trip  on 27-09-2019 start
                if ($Data_Car[0]['iSelectedVehicleId'] == $Data_Car[$i]['iDriverVehicleId']) {
                    $vCarType = $Data_Car[$i]['vCarType'];
                    if ($vCarType != "") {
                        /* $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
                        
                          $db_cartype = $obj->MySQLSelect($sql);
                        
                          print_R($db_cartype); exit; */
                        $sql = "SELECT count(iVehicleTypeId) as Totalrec,SUM(CASE WHEN eType='Ride' AND eFly=1 THEN 1 ELSE 0 END) Totalfly,SUM(CASE WHEN eType!='DeliverAll'  THEN 1 ELSE 0 END) TotalRide  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
                        $db_cartype = $obj->MySQLSelect($sql);
                        if (count($db_cartype) > 0 && $db_cartype[0]['Totalfly'] > 0 && $db_cartype[0]['TotalRide'] == $db_cartype[0]['Totalfly']) {
                            $returnArr['Enable_Hailtrip'] = "No";
                            $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
                        }
                    }
                }
                //added by SP for fly vehicle do not enable hail and end of day trip  on 27-09-2019 end
                $i++;
            }
            //if ($selectedVehicle == 0 && $status == "CARS_AVAIL" && $Status_driver == "Available") {
            if ($selectedVehicle == 0 && $status == "CARS_NOT_ACTIVE" && $Status_driver == "Available") {//CARS_NOT_ACTIVE added bc when vehicle is not selected but it is active then also it shows vehicle is not active..
                //echo "<pre>";print_R($Data_Car);die;
                $returnArr = array();
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_SELECTED_VEHICLE_NOT_ACTIVE";
                setDataResponse($returnArr);
            }
            //echo $ENABLE_DRIVER_DESTINATIONS;die;
            //echo $status;die;
            if ($status == "CARS_AVAIL" && ($Data_Car[0]['iSelectedVehicleId'] == "0" || $Data_Car[0]['iSelectedVehicleId'] == "") && $Status_driver == "Available") {
                // echo "SELECT_CAR";
                if ($APP_TYPE == "Ride-Delivery-UberX") {
                    $sql = "SELECT vCarType from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX' AND `eStatus`='Active'";
                    $db_cartype = $obj->MySQLSelect($sql);
                    //echo "<pre>";print_r($db_cartype);die;
                    $vCarType = $db_cartype[0]['vCarType'];
                    if ($vCarType == "") {
                        $returnArr['Action'] = "0";
                        $returnArr['Enable_Hailtrip'] = $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
                        //$returnArr['message']="LBL_PROVIDER_NO_SERVICE_ENABLE_TXT";
                        $returnArr['message'] = ($RideDeliveryBothFeatureDisable == "No") ? "LBL_PROVIDER_NO_SERVICE_ENABLE_TXT" : "LBL_ONLY_OTHER_SERVICE_ENABLE_TXT";
                        //$returnArr['Enable_Hailtrip'] = "Yes"; //Comment This Line Added For Testing By HJ On 30-12-2018
                        setDataResponse($returnArr);
                    }
                    else {
                        $returnArr['Enable_Hailtrip'] = $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
                        //$returnArr['UberX_message']="LBL_PROVIDER_OTHER_SERVICE_ENABLE_TXT";
                        $returnArr['UberX_message'] = ($RideDeliveryBothFeatureDisable == "No") ? "LBL_PROVIDER_OTHER_SERVICE_ENABLE_TXT" : "LBL_ONLY_OTHER_SERVICE_ENABLE_TXT";
                    }
                }
                else {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_SELECT_CAR_MESSAGE_TXT";
                    setDataResponse($returnArr);
                }
            }
            else if ($status == "CARS_NOT_ACTIVE") {
                // echo "CARS_NOT_ACTIVE";
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
                $returnArr['Enable_Hailtrip'] = "No";
                $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
                setDataResponse($returnArr);
            }
        }
    }
    else {
        if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "UberX") {
            $sql = "SELECT vCarType from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX' AND `eStatus`='Active'";
            $db_cartype = $obj->MySQLSelect($sql);
            //echo "<pre>";print_r($db_cartype);die;
            $vCarType = $db_cartype[0]['vCarType'];
            if ($vCarType == "" && count($db_cartype) > 0) {
                $returnArr['Action'] = "0";
                $returnArr['Enable_Hailtrip'] = "No";
                $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
                if ($APP_TYPE == "UberX") {
                    $returnArr['message'] = "LBL_NO_SERVICE_AVAIL";
                }
                else {
                    $returnArr['message'] = "LBL_PROVIDER_NO_SERVICE_ENABLE_TXT";
                }
                setDataResponse($returnArr);
            }
        }
        if ($Status_driver == "Available") { // Added By HJ On 02-12-2019 For Solved Sheet Bug = 567 As Per Discuss With KS Sir
            // echo "NO_CARS_AVAIL";
            $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND ( eStatus = 'Inactive' OR eStatus = 'Deleted')";
            $db_Total_vehicle = $obj->MySQLSelect($sql);
            $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
            $returnArr['Action'] = "0";
            if ($TotalVehicles == 0) {
                $returnArr['Enable_Hailtrip'] = $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
                $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
            }
            else {
                $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
            }
            setDataResponse($returnArr);
        }
    }
    $where = " iDriverId='" . $iDriverId . "'";
    if ($Status_driver != '') {
        $Data_update_driver['vAvailability'] = $Status_driver;
    }
    //$time = date("H:i:");
    if (checkDriverDestinationModule()) {
        $iDestinationCount = $Data_Car[0]['iDestinationCount'];
        $tDestinationModifiedDate = $Data_Car[0]['tDestinationModifiedDate'];
        $tOnline = $Data_Car[0]['tOnline'];
        $resetData = date('Y-m-d') . ' ' . $DRIVER_DESTINATIONS_RESET_TIME . ':00';
        // $resetData = converToTz($resetData, date_default_timezone_get(), $vTimeZone);
        // $today1 = date("Y-m-d H:i:s");
        // $today1 = converToTz($today1, date_default_timezone_get(), $vTimeZone);
        // $tDestinationModifiedDate = converToTz($tDestinationModifiedDate, date_default_timezone_get(), $vTimeZone);
        $DRIVER_DESTINATIONS_RESET_TIME_ARR = explode(":", $DRIVER_DESTINATIONS_RESET_TIME);
        $DRIVER_DESTINATIONS_RESET_TIME_HOUR = $DRIVER_DESTINATIONS_RESET_TIME_ARR[0];
        $DRIVER_DESTINATIONS_RESET_TIME_MINITE = $DRIVER_DESTINATIONS_RESET_TIME_ARR[1];
        if ((!empty($tDestinationModifiedDate) && $tOnline >= $resetData && $tDestinationModifiedDate < $resetData)) {
            $where_driver_registration_data = "iDriverId='" . $iDriverId . "'";
            //$Data_update_driver_registration_data1['eDestinationMode'] = 'No';
            $Data_update_driver_registration_data1['iDestinationCount'] = 0;
            //$Data_update_driver_registration_data1['tDestinationModifiedDate'] = date('Y-m-d H:i:s',strtotime('+'.$DRIVER_DESTINATIONS_RESET_TIME_HOUR.' hour +'.$DRIVER_DESTINATIONS_RESET_TIME_MINITE.' minutes',time()));
            $Data_update_driver_registration_data1['tDestinationModifiedDate'] = date('Y-m-d H:i:s');
            $data = $obj->MySQLQueryPerform('register_driver', $Data_update_driver_registration_data1, 'update', $where_driver_registration_data);
        }
        if ($iDestinationCount >= $MAX_DRIVER_DESTINATIONS) {
            $returnArr['DRIVER_DESTINATION_AVAILABLE'] = 'No';
        }
        else {
            $returnArr['DRIVER_DESTINATION_AVAILABLE'] = 'Yes';
        }
    }
    if ($latitude_driver != '' && $longitude_driver != '') {
        $Data_update_driver['vLatitude'] = $latitude_driver;
        $Data_update_driver['vLongitude'] = $longitude_driver;
    }
    if ($Status_driver == "Available") {
        //$Data_update_driver['tOnline'] = @date("Y-m-d H:i:s");
        // insert as online
        // Code for Check last logout date is update in driver_log_report
        $query = "SELECT * FROM driver_log_report WHERE dLogoutDateTime = '0000-00-00 00:00:00' AND iDriverId = '" . $iDriverId . "' ORDER BY iDriverLogId DESC LIMIT 0,1";
        $db_driver = $obj->MySQLSelect($query);
        if (count($db_driver) > 0) {
            $sql = "SELECT tLastOnline FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
            $db_drive_lastonline = $obj->MySQLSelect($sql);
            $driver_lastonline = $db_drive_lastonline[0]['tLastOnline'];
            $updateQuery = "UPDATE driver_log_report set dLogoutDateTime='" . $driver_lastonline . "' WHERE iDriverLogId = " . $db_driver[0]['iDriverLogId'];
            $obj->sql_query($updateQuery);
        }
        // Code for Check last logout date is update in driver_log_report Ends
        $vIP = $generalobj->get_client_ip();
        $curr_date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `driver_log_report` (`iDriverId`,`dLoginDateTime`,`vIP`) VALUES ('" . $iDriverId . "','" . $curr_date . "','" . $vIP . "')";
        $insert_log = $obj->sql_query($sql);
        //update insurance log
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            $details_arr['iTripId'] = "0";
            $details_arr['LatLngArr']['vLatitude'] = $latitude_driver;
            $details_arr['LatLngArr']['vLongitude'] = $longitude_driver;
            // $details_arr['LatLngArr']['vLocation'] = "";
            update_driver_insurance_status($iDriverId, "Available", $details_arr, "updateDriverStatus", "Online");
        }
        //update insurance log
        
    }
    if ($Status_driver == "Not Available") {
        // update as offline
        $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
        $curr_date = date('Y-m-d H:i:s');
        $selct_query = "select * from driver_log_report WHERE iDriverId = '" . $iDriverId . "' order by `iDriverLogId` desc limit 0,1";
        $get_data_log = $obj->sql_query($selct_query);
        $update_sql = "UPDATE driver_log_report set dLogoutDateTime = '" . $curr_date . "' WHERE iDriverLogId ='" . $get_data_log[0]['iDriverLogId'] . "'";
        $result = $obj->sql_query($update_sql);
        //update insurance log
        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            $details_arr['iTripId'] = "0";
            $details_arr['LatLngArr']['vLatitude'] = $latitude_driver;
            $details_arr['LatLngArr']['vLongitude'] = $longitude_driver;
            // $details_arr['LatLngArr']['vLocation'] = "";
            update_driver_insurance_status($iDriverId, "Available", $details_arr, "updateDriverStatus", "Offline");
        }
        //update insurance log
        
    }
    if (($isUpdateOnlineDate == "true" && $Status_driver == "Available") || ($isUpdateOnlineDate == "" && $Status_driver == "") || $isUpdateOnlineDate == "true") {
        $Data_update_driver['tOnline'] = @date("Y-m-d H:i:s");
        $Data_update_driver['tLastOnline'] = @date("Y-m-d H:i:s");
    }
    if ($isOnlineSwitchPressed == "true" && $Status_driver == "Available") {
        $Data_update_driver['tSwitchOnline'] = @date("Y-m-d H:i:s");
    }
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    # Update User Location Date #
    Updateuserlocationdatetime($iDriverId, "Driver", $vTimeZone);
    # Update User Location Date #
    if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "UberX") {
        $isExistUberXServices = "Yes";
        $sql = "SELECT vCarType from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX'";
        $db_cartype = $obj->MySQLSelect($sql);
        $vCarType = $db_cartype[0]['vCarType'];
        if ($vCarType == "") {
            $isExistUberXServices = "No";
        }
        $returnArr['isExistUberXServices'] = $isExistUberXServices;
    }
    if ($ENABLE_HAIL_RIDES == "No" || ONLYDELIVERALL == "Yes") {
        $returnArr['Enable_Hailtrip'] = "No";
    }
    if ($ENABLE_DRIVER_DESTINATIONS == "No" || ONLYDELIVERALL == "Yes") {
        $returnArr['ENABLE_DRIVER_DESTINATIONS'] = "No";
    }
    if ($APP_TYPE != "Ride-Delivery-UberX" && $APP_TYPE != "UberX") {
        $returnArr['isExistUberXServices'] = "No";
    }
    if (strtoupper($returnArr['isExistUberXServices']) == "YES") {
        //$returnArr['isExistUberXServices'] = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
        $returnArr['isExistUberXServices'] = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query
    }
    if ($id) {
        $returnArr['Action'] = "1";
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}
###########################################################################
if ($type == "UpdateCustomerToken") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $vPaymayaToken = isset($_REQUEST["vPaymayaToken"]) ? $_REQUEST["vPaymayaToken"] : '';
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $vFlutterWaveToken = isset($_REQUEST["vFlutterWaveToken"]) ? trim($_REQUEST["vFlutterWaveToken"]) : ''; // Added By HJ On 23-07-2019 For Store Token
    $txref = isset($_REQUEST["txref"]) ? trim($_REQUEST["txref"]) : ''; // Added By HJ On 23-07-2019 For Verify Transaction
    $mpessa = isset($_REQUEST["mpessa"]) ? trim($_REQUEST["mpessa"]) : ''; // Added By HJ On 05-08-2019 For Update User Wallet By Mpessa Method
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    if ($eMemberType == "Passenger") {
        $tbl_name = "register_user";
        $vEmail = "vEmail";
        $iMemberId = "iUserId";
        $eUserType = "Rider";
    }
    else {
        $tbl_name = "register_driver";
        $vEmail = "vEmail";
        $iMemberId = "iDriverId";
        $eUserType = "Driver";
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $where = " $iMemberId = '$iUserId'";
    $updateData = array();
    $updateQuery = $id = $chargeAmtFlag = $chargeAmt = 0;
    if ($vPaymayaToken != "" && $APP_PAYMENT_METHOD == "Paymaya") {
        $updateQuery = 1;
        $updateData['vPaymayaToken'] = $vPaymayaToken;
        $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
    }
    if ($txref != "" && $APP_PAYMENT_METHOD == "Flutterwave") {
        if ($txref != "") {
            $verifiedData = flutterwave_verify($txref);
            if (isset($verifiedData['token']) && $verifiedData['token'] != "") {
                $updateQuery = $chargeAmtFlag = 1;
                $updateData['vFlutterWaveToken'] = $verifiedData['token'];
                $updateData['vCreditCard'] = $verifiedData['card'];
                $chargeAmt = $verifiedData['chargedAmt'];
                $chargedCurrency = $verifiedData['chargedCurrency'];
            }
        }
        $dDate = Date('Y-m-d H:i:s');
        $eFor = 'Deposit';
        $iTripId = 0;
        $tDescription = '#LBL_AMOUNT_CREDIT#';
        $ePaymentStatus = 'Unsettelled';
        /* Added By HJ On 23-07-2019 For Store Token Start  */
        if ($vFlutterWaveToken != "") {
            $updateQuery = 1;
            $updateData['vFlutterWaveToken'] = $vFlutterWaveToken;
        }
        if ($eUserType == 'Rider') {
            $fieldname = 'ru.iUserId';
            $tablename = 'register_user as ru';
            $getfields = 'ru.vCurrencyPassenger as currency, cu.ratio, cu.vSymbol, ru.vEmail, CONCAT( ru.vName,  " ", ru.vLastName ) AS username';
            $onfields = "ON ru.vCurrencyPassenger = cu.vName";
        }
        else {
            $fieldname = 'rd.iDriverId';
            $tablename = 'register_driver as rd';
            $getfields = 'rd.vCurrencyDriver as currency, cu.ratio, cu.vSymbol, rd.vEmail, CONCAT( rd.vName,  " ", rd.vLastName ) AS username';
            $onfields = "ON rd.vCurrencyDriver = cu.vName";
        }
        if ($updateQuery > 0) {
            $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
            if ($chargeAmtFlag > 0 && $chargeAmt > 0) {
                $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $chargeAmt, 'Credit', 0, $eFor, $tDescription, $ePaymentStatus, $dDate);
            }
        }
        /* Added By HJ On 05-08-2019 For Handle Mpessa SDK Response Start */
        if ($mpessa != "") {
            $responseData = json_decode($mpessa, true);
            $status = $responseData['status'];
            $chargecode = $iFareAmt = 0;
            $PaymentId = "";
            if (isset($responseData['payload']['data']['chargecode']) && $responseData['payload']['data']['chargecode'] == "00" && $status == "success") {
                $chargecode = 1;
                $PaymentId = $responseData['payload']['data']['txid'];
                $iFareAmt = $responseData['payload']['data']['chargedamount'];
            }
            if (isset($responseData['data']['chargeResponseCode']) && $responseData['data']['chargeResponseCode'] == "00") {
                $chargecode = 1;
                $PaymentId = $responseData['data']['id'];
                $iFareAmt = $responseData['data']['charged_amount'];
            }
            if ($chargecode > 0 && $PaymentId != "" && $iFareAmt > 0) {
                $pay_data = array();
                $pay_data['tPaymentUserID'] = $PaymentId;
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iTripId'] = $iTripId;
                $pay_data['iUserId'] = $iUserId;
                $pay_data['eUserType'] = $eUserType;
                $pay_data['iAmountUser'] = $iFareAmt;
                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $iFareAmt, 'Credit', 0, $eFor, $tDescription, $ePaymentStatus, $dDate);
            }
        }
        /* Added By HJ On 05-08-2019 For Handle Mpessa SDK Response End */
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    if ($eMemberType == "Passenger") {
        $profileData = getPassengerDetailInfo($iUserId, "", "");
    }
    else {
        $profileData = getDriverDetailInfo($iUserId);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $profileData;
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
######################################## General types For all App Type ######################################
###################### getAssignedDriverLocation ##########################
if ($type == "getDriverLocations") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $sql = "SELECT vLatitude, vLongitude,vTripStatus FROM `register_driver` WHERE iDriverId='$iDriverId'";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) == 1) {
        $returnArr['Action'] = "1";
        $returnArr['vLatitude'] = $Data[0]['vLatitude'];
        $returnArr['vLongitude'] = $Data[0]['vLongitude'];
        $returnArr['vTripStatus'] = $Data[0]['vTripStatus'];
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'Not Found';
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "LoadAvailableCars") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $CheckRideDeliveryFeatureDisable_Arr = CheckRideDeliveryFeatureDisable();
    $eShowRideVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowRideVehicles'];
    $eShowDeliveryVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliveryVehicles'];
    $eShowDeliverAllVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliverAllVehicles'];
    $eShowVehicles = "Yes";
    if ($eShowRideVehicles == 'No' && $eShowDeliveryVehicles == 'No' && ($eShowDeliverAllVehicles == 'No' || DELIVERALL == 'No')) {
        $eShowVehicles = "No";
    }
    $ssql = " AND dv.eType != 'UberX'";
    $sql = "SELECT register_driver.iDriverVehicleId as DriverSelectedVehicleId,make.vMake, model.vTitle, dv.* FROM `driver_vehicle` dv, make, model,register_driver WHERE dv.iDriverId='$iDriverId' AND register_driver.iDriverId = '$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'" . $ssql . " Order By dv.iDriverVehicleId desc";
    $Data_Car = $obj->MySQLSelect($sql);
    if (count($Data_Car) > 0) {
        $sql = "SELECT count(dv.iDriverVehicleId) as TotalVehicles from driver_vehicle as dv WHERE iDriverId = '" . $iDriverId . "'" . $ssql;
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
        if (count($Data_Car) == 1 && $Data_Car[0]['eType'] == "UberX" && $TotalVehicles == 1 && $APP_TYPE == "Ride-Delivery-UberX") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = ($eShowVehicles == "No") ? "LBL_ONLY_OTHER_SERVICE_ENABLE_TXT" : "LBL_PROVIDER_OTHER_SERVICE_ENABLE_TXT";
            setDataResponse($returnArr);
        }
        else {
            $status = "CARS_NOT_ACTIVE";
            $i = 0;
            while (count($Data_Car) > $i) {
                $eStatus = $Data_Car[$i]['eStatus'];
                if ($eStatus == "Active") {
                    $status = "CARS_AVAIL";
                }
                $i++;
            }
            if ($status == "CARS_NOT_ACTIVE") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
                setDataResponse($returnArr);
            }
            // $returnArr['carList'] = $Data_Car;
            $db_vehicle_new = $Data_Car;
            for ($i = 0;$i < count($Data_Car);$i++) {
                $eType = $Data_Car[$i]['eType'];
                if ($eType == "UberX") {
                    unset($db_vehicle_new[$i]);
                }
            }
            $db_vehicle_new = array_values($db_vehicle_new);
            if (count($db_vehicle_new) == 0) {
                $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND eStatus = 'Inactive'";
                $db_tot_vehicle = $obj->MySQLSelect($sql);
                $TotalVehicles = $db_tot_vehicle[0]['TotalVehicles'];
                $returnArr['Action'] = "0";
                if ($TotalVehicles > 0) {
                    $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
                }
                else {
                    $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
                }
                setDataResponse($returnArr);
            }
            for ($i = 0;$i < count($db_vehicle_new);$i++) {
                $vCarType = $db_vehicle_new[$i]['vCarType'];
                $enable_hail_flag = "No";
                if ($vCarType != "") {
                    $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
                    $db_cartype = $obj->MySQLSelect($sql);
                    if (count($db_cartype) > 0) {
                        for ($j = 0;$j < count($db_cartype);$j++) {
                            $eVehicleType = $db_cartype[$j]['eType'];
                            if ($eVehicleType == "Ride") {
                                $enable_hail_flag = "Yes";
                            }
                        }
                    }
                    //added by SP for fly vehicle do not enable hail and end of day trip  on 27-09-2019 start
                    $sql = "SELECT count(iVehicleTypeId) as Totalrec,SUM(CASE WHEN eType='Ride' AND eFly=1 THEN 1 ELSE 0 END) Totalfly,SUM(CASE WHEN eType!='DeliverAll'  THEN 1 ELSE 0 END) TotalRide  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
                    $db_cartype = $obj->MySQLSelect($sql);
                    if (count($db_cartype) > 0 && $db_cartype[0]['Totalfly'] > 0 && $db_cartype[0]['TotalRide'] == $db_cartype[0]['Totalfly']) {
                        $enable_hail_flag = "No";
                    }
                    //added by SP for fly vehicle do not enable hail and end of day trip  on 27-09-2019 end
                    
                }
                $db_vehicle_new[$i]['Enable_Hailtrip'] = ($enable_hail_flag == "Yes" && $APP_PAYMENT_MODE != "Card") ? "Yes" : "No";
            }
            $returnArr['Action'] = "1";
            $returnArr['message'] = $db_vehicle_new;
            setDataResponse($returnArr);
        }
    }
    else {
        $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND ( eStatus = 'Inactive' OR eStatus = 'Deleted')";
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
        $returnArr['Action'] = "0";
        if ($TotalVehicles == 0) {
            $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
        }
        else {
            $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
        }
        setDataResponse($returnArr);
    }
}
########################### Set Driver CarID ############################
if ($type == "SetDriverCarID") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Data['iDriverVehicleId'] = isset($_REQUEST["iDriverVehicleId"]) ? $_REQUEST["iDriverVehicleId"] : '';
    /* For DriverSubscription added by SP start */
    //$sql = "SELECT iDriverVehicleId from register_driver WHERE iDriverId = '" . $iDriverId . "'";
    //$db_driver_vehicle = $obj->MySQLSelect($sql);
    //if(!empty($db_driver_vehicle[0]['iDriverVehicleId'])) {
    if (checkDriverSubscriptionModule()) {
        $returnSubStatus = 0;
        $returnSubStatus = checkDriverSubscribed($iDriverId, $Data['iDriverVehicleId']);
        if ($returnSubStatus == 1) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PENDING_MIXSUBSCRIPTION";
            setDataResponse($returnArr);
        }
        if ($returnSubStatus == 2) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "PENDING_SUBSCRIPTION";
            setDataResponse($returnArr);
        }
    }
    //}
    /* For DriverSubscription added by SP end */
    $where = " iDriverId = '" . $iDriverId . "'";
    $sql = $obj->MySQLQueryPerform("register_driver", $Data, 'update', $where);
    if ($sql > 0) {
        $returnArr['Action'] = "1";
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}
############################################################################
if ($type == "updateDriverLocations") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $where = " iDriverId='$iDriverId'";
    $Data_update_driver['vLatitude'] = $latitude_driver;
    $Data_update_driver['vLongitude'] = $longitude_driver;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    # Update User Location Date #
    Updateuserlocationdatetime($iDriverId, "Driver", $vTimeZone);
    # Update User Location Date #
    if ($id) {
        $returnArr['Action'] = "1";
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "updateTripLocations") {
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $latitudes = isset($_REQUEST['latList']) ? $_REQUEST['latList'] : '';
    $longitudes = isset($_REQUEST['lonList']) ? $_REQUEST['lonList'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    if ($iDriverId != "" && $tripId == "") {
        $iTripId = get_value('register_driver', 'iTripId', 'iDriverId', $iDriverId, '', 'true');
        if ($iTripId != "") {
            $tripId = $iTripId;
        }
    }
    if ($tripId != '' && $latitudes != '' && $longitudes != '') {
        $latitudes = preg_replace("/[^0-9,.-]/", "", $latitudes);
        $longitudes = preg_replace("/[^0-9,.-]/", "", $longitudes);
        $id = processTripsLocations($tripId, $latitudes, $longitudes);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
    }
    else {
        $returnArr['Action'] = "0";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "checkSurgePrice") {
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';
    $selectedTime = isset($_REQUEST["SelectedTime"]) ? $_REQUEST["SelectedTime"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    /* added for rental */
    $iRentalPackageId = isset($_REQUEST["iRentalPackageId"]) ? $_REQUEST["iRentalPackageId"] : '';
    $ePool = isset($_REQUEST["ePool"]) ? $_REQUEST["ePool"] : 'No';
    // changes for flattrip 29-01-2019
    $eType_vehicle = get_value('vehicle_type', 'eType', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');
    if ($eType_vehicle == "UberX" && $SERVICE_PROVIDER_FLOW == "Provider" && $selectedTime != "") {
        $sdate = explode(" ", $selectedTime);
        $shour = explode("-", $sdate[1]);
        $shour1 = $shour[0];
        $shour2 = $shour[1];
        if ($shour1 == "12" && $shour2 == "01") {
            $shour1 = 00;
        }
        $selectedTime = $sdate[0] . " " . $shour1 . ":00:00";
    }
    ######### Checking For Flattrip #########
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $iUserId = "iUserId";
        $vCurrency = "vCurrencyPassenger";
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $priceRatio = $passengerData[0]['Ratio'];
        $vLangCode = $passengerData[0]['vLang'];
    }
    else {
        $tblname = "register_driver";
        $iUserId = "iDriverId";
        $vCurrency = "vCurrencyDriver";
        $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $currencycode = $driverData[0]['vCurrencyDriver'];
        $currencySymbol = $driverData[0]['vSymbol'];
        $priceRatio = $driverData[0]['Ratio'];
        $vLangCode = $driverData[0]['vLang'];
    }
    if ($currencycode == "" || $currencycode == NULL) {
        $sql = "SELECT vName,vSymbol,Ratio from currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sql);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $priceRatio = $currencyData[0]['Ratio'];
    }
    ######### Checking For Flattrip #########
    $isDestinationAdded = "No";
    if ($DestLatitude != "" && $DestLongitude != "") {
        $isDestinationAdded = "Yes";
    }
    // changes for flattrip 29-01-2019
    $eFlatTrip = "No";
    $fFlatTripPrice = 0;
    if ($isDestinationAdded == "Yes" && $eType_vehicle == 'Ride') {
        $sourceLocationArr = array(
            $PickUpLatitude,
            $PickUpLongitude
        );
        $destinationLocationArr = array(
            $DestLatitude,
            $DestLongitude
        );
        if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
            $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $selectedCarTypeID, $iRentalPackageId);
            $eFlatTrip = $data_flattrip['eFlatTrip'];
            $fFlatTripPrice = $data_flattrip['Flatfare'];
        }
    }
    $SurgePriceValue = 1;
    if ($APP_TYPE == "UberX") {
        $data['Action'] = "1";
    }
    else {
        /* changed for rental */
        $data = checkSurgePrice($selectedCarTypeID, $selectedTime, $iRentalPackageId);
        if ($data['Action'] == "0") {
            $SurgePriceValue = $data['SurgePriceValue'];
        }
    }
    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $eFlatTrip == "Yes") {
        $SurgePriceValue = 1;
        $data['Action'] = "1";
    }
    
    if ($APP_PAYMENT_MODE != "Cash") {
        $data['ShowPayNow'] = "Yes";
    }
    $fFlatTripPrice = $generalobj->setTwoDecimalPoint($fFlatTripPrice * $priceRatio);
    $fSurgePriceDiff = $generalobj->setTwoDecimalPoint(($fFlatTripPrice * $SurgePriceValue) - $fFlatTripPrice);
    $fFlatTripPrice = $fFlatTripPrice + $fSurgePriceDiff;
    $data['eFlatTrip'] = $eFlatTrip;
    $data['fFlatTripPrice'] = $fFlatTripPrice;
    $data['fFlatTripPricewithsymbol'] = $currencySymbol . " " . $fFlatTripPrice;
    $data['fOutStandingAmount'] = 0.00;
    $data['fOutStandingAmountWithSymbol'] = $currencySymbol . " 0.00";
    if ($UserType == "Passenger") {
        $fOutStandingAmount = GetPassengerOutstandingAmount($iMemberId);
        $fOutStandingAmount = $generalobj->setTwoDecimalPoint($fOutStandingAmount * $priceRatio);
        $data['fOutStandingAmount'] = $fOutStandingAmount;
        $data['fOutStandingAmountWithSymbol'] = $currencySymbol . " " . $fOutStandingAmount;
    }
    //Added By HJ On 10-06-2019 For Get User Out Standing Amount For Payment Method 2 Or 3 Start
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $getUserOutstandingAmount = getUserOutstandingAmount($iMemberId, "iCabRequestId");
        $fOutStandingAmount = $generalobj->setTwoDecimalPoint($getUserOutstandingAmount['fPendingAmount'] * $priceRatio);
        $data['fOutStandingAmount'] = $fOutStandingAmount;
        $data['fOutStandingAmountWithSymbol'] = $currencySymbol . " " . $fOutStandingAmount;
    }
    //Added By HJ On 10-06-2019 For Get User Out Standing Amount For Payment Method 2 Or 3 End
    
    //added by SP on 11-06-2020 for outstanding restriction start...
    if ($UserType == "Passenger") {
        
        $data['ShowAdjustTripBtn'] = "Yes";
        
        if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
            $outStandingSql = " AND eAuthoriseIdName='No' AND iAuthoriseId ='0'";
        }
        
        if ($vLangCode == "" || $vLangCode == NULL) {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
        
        if ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Card") {
            
            $sql = "SELECT count(iTripId) as counttrip FROM trip_outstanding_amount WHERE iUserId='" . $iMemberId . "' AND iUserId > 0 AND ePaidByPassenger = 'No' $outStandingSql";
            $counttripData = $obj->MySQLSelect($sql);
            if($counttripData[0]['counttrip']>=$OUTSTANDING_ALLOW_TRIP_COUNT) {
                $data['ShowAdjustTripBtn'] = "No";
                if ($APP_PAYMENT_MODE == "Cash") {
                    $data['outstanding_restriction_label'] = str_replace("##",$data['fOutStandingAmountWithSymbol'],$languageLabelsArr["LBL_OUTSTANDING_RESTRICTION_MSG"]);
                    $data['ShowAdjustTripBtn'] = $data['ShowPayNow'] = "No";
                }
            }
        }
        
        if ($APP_PAYMENT_MODE == "Cash-Card") {                
            $ePaymentmethod = isset($_REQUEST["ePaymentmethod"]) ? $_REQUEST["ePaymentmethod"] : '';
            
            if($ePaymentmethod=="cash") {
                $sql = "SELECT count(iTripId) as counttrip FROM trip_outstanding_amount WHERE iUserId='" . $iMemberId . "' AND iUserId > 0 AND ePaidByPassenger = 'No' AND vTripPaymentMode = 'Cash' $outStandingSql";
                $counttripData = $obj->MySQLSelect($sql);
                if($counttripData[0]['counttrip']>=$OUTSTANDING_ALLOW_TRIP_COUNT) {
                    $data['ShowAdjustTripBtn'] = "No";
                    $data['ShowPayNow'] = "Yes";
                    $data['outstanding_restriction_label'] = str_replace("##",$data['fOutStandingAmountWithSymbol'],$languageLabelsArr["LBL_OUTSTANDING_RESTRICTION_MSG_CASH"]);
                    $data['ShowAdjustTripBtn'] = $data['ShowPayNow'] = "No";
                }
            }
            
            if($ePaymentmethod=="card") {
                $sql = "SELECT count(iTripId) as counttrip FROM trip_outstanding_amount WHERE iUserId='" . $iMemberId . "' AND iUserId > 0 AND ePaidByPassenger = 'No' AND vTripPaymentMode = 'Card' $outStandingSql";
                $counttripData = $obj->MySQLSelect($sql);
                if($counttripData[0]['counttrip']>=$OUTSTANDING_ALLOW_TRIP_COUNT) {
                    $data['ShowAdjustTripBtn'] = "Yes";
                    $data['ShowPayNow'] = "No";
                    $data['outstanding_restriction_label'] = str_replace("##",$data['fOutStandingAmountWithSymbol'],$languageLabelsArr["LBL_OUTSTANDING_RESTRICTION_MSG_CARD"]);
                }
            }
        }
    }
    //added by SP on 11-06-2020 for outstanding restriction end...
    
    //Added By HJ On 31-12-2018 For Check Pool Status Start As Per Discuss With KS Sir
    if ($ePool == "Yes") {
        $data['fFlatTripPrice'] = $data['fOutStandingAmount'] = 0.00;
        $data['fFlatTripPricewithsymbol'] = $data['fOutStandingAmountWithSymbol'] = $currencySymbol . " 0.00";
    }
    //Added By HJ On 31-12-2018 For Check Pool Status End As Per Discuss With KS Sir
    setDataResponse($data);
}
if ($type == "loadPassengersLocation") {
    global $generalobj, $obj;
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $radius = isset($_REQUEST["Radius"]) ? $_REQUEST["Radius"] : '';
    $sourceLat = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $sourceLon = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';
    $str_date = @date('Y-m-d H:i:s', strtotime('-5 minutes'));
    // register_user table
    $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") )

        * cos( radians( vLatitude ) )

        * cos( radians( vLongitude ) - radians(" . $sourceLon . ") )

        + sin( radians(" . $sourceLat . ") )

        * sin( radians( vLatitude ) ) ) ),2) AS distance, register_user.*  FROM `register_user`

        WHERE (vLatitude != '' AND vLongitude != '' AND eStatus='Active' AND tLastOnline > '$str_date')

        HAVING distance < " . $radius . " ORDER BY `register_user`.iUserId ASC";
    $Data = $obj->MySQLSelect($sql);
    $storeuser = $storetrip = array();
    foreach ($Data as $value) {
        $dataofuser = array(
            "Type" => 'Online',
            "Latitude" => $value['vLatitude'],
            "Longitude" => $value['vLongitude'],
            "iUserId" => $value['iUserId']
        );
        array_push($storeuser, $dataofuser);
    }
    // trip table
    if (SITE_TYPE == 'Demo') {
        $sql_trip = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") )

            * cos( radians( tStartLat ) )

            * cos( radians( tStartLong ) - radians(" . $sourceLon . ") )

            + sin( radians(" . $sourceLat . ") )

            * sin( radians( tStartLat ) ) ) ),2) AS distance, trips.*  FROM `trips`

            WHERE (tStartLat != '' AND tStartLong != '' AND tTripRequestDate >= DATE_SUB(CURDATE(), INTERVAL 2500 HOUR))

            HAVING distance < " . $radius . " ORDER BY `trips`.iTripId DESC";
    }
    else {
        $sql_trip = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") )

            * cos( radians( tStartLat ) )

            * cos( radians( tStartLong ) - radians(" . $sourceLon . ") )

            + sin( radians(" . $sourceLat . ") )

            * sin( radians( tStartLat ) ) ) ),2) AS distance, trips.*  FROM `trips`

            WHERE (tStartLat != '' AND tStartLong != '' AND tTripRequestDate >= DATE_SUB(CURDATE(), INTERVAL 24 HOUR))

            HAVING distance < " . $radius . " ORDER BY `trips`.iTripId DESC";
    }
    $Dataoftrips = $obj->MySQLSelect($sql_trip);
    foreach ($Dataoftrips as $value1) {
        $valuetrip = array(
            "Type" => 'History',
            "Latitude" => $value1['tStartLat'],
            "Longitude" => $value1['tStartLong'],
            "iTripId" => $value1['iTripId']
        );
        array_push($storetrip, $valuetrip);
    }
    $finaldata = array_merge($storeuser, $storetrip);
    if (count($finaldata) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $finaldata;
    }
    else {
        $returnData['Action'] = "0";
    }
    setDataResponse($returnData);
}
###################################################################
if ($type == "getUserVehicleDetails") {
    global $generalobj;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $user_type = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    $vCountry = '';
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }
    else {
        $tblname = "register_driver";
        $driveData = get_value('register_driver', 'vLang,vCountry', 'iDriverId', $iMemberId);
        $vLangCode = $driveData[0]['vLang'];
        $vCountry = $driveData[0]['vCountry'];
    }
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $lbl_all = $languageLabelsArr['LBL_ALL'];
    //$APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    $isMultipleVehicleCategoryAvailable = false;
    $ssql = "";
    if ($APP_TYPE == "Delivery") {
        $ssql .= " AND eType = 'Deliver'";
    }
    else if ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Delivery-UberX") {
        
        $isMultipleVehicleCategoryAvailable = true;
        $ssql .= " AND eType != 'UberX'";
        ### Checking Vehicles For Ride , Delivery Icons and Banners Availability ##
        $CheckRideDeliveryFeatureDisable_Arr = CheckRideDeliveryFeatureDisable();
        $eShowRideVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowRideVehicles'];
        $eShowDeliveryVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliveryVehicles'];
        $eShowDeliverAllVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliverAllVehicles'];
        if ($eShowRideVehicles == "No") {
            $ssql .= " AND eType != 'Ride'";
        }
        if ($eShowDeliveryVehicles == "No") {
            $ssql .= " AND eType != 'Deliver'";
        }
        if ($eShowDeliverAllVehicles == "No") {
            $ssql .= " AND eType != 'DeliverAll'";
        }
        ### Checking Vehicles For Ride , Delivery Icons and Banners Availability ##
        
    }
    else {
        $ssql .= " AND eType = '" . $APP_TYPE . "'";
    }
    
    if ($vCountry != "") {
        $iCountryId = get_value('country', 'iCountryId', 'vCountryCode', $vCountry, '', 'true');
        //$ssql.= " AND (iCountryId = '".$iCountryId."' OR iCountryId = '-1' OR iCountryId = '0')";
        $sql = "SELECT * FROM location_master WHERE eStatus='Active' AND iCountryId = '" . $iCountryId . "' AND eFor = 'VehicleType'";
        $db_country = $obj->MySQLSelect($sql);
        $country_str = "-1";
        if (count($db_country) > 0) {
            for ($i = 0;$i < count($db_country);$i++) {
                $country_str .= "," . $db_country[$i]['iLocationId'];
            }
        }
        $ssql .= " AND iLocationid IN ($country_str) ";
    }
    //Added By HJ On 30-12-2018 For Check POOL Status Start
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $ssql = regenerateQueryForPool($ssql);
    }
    //added by SP for fly on 6-9-2019
    if (checkFlyStationsModule()) {
        $fly = 'Yes';
    }
    else {
        $fly = 'No';
    }
    if ($fly != 'Yes') {
        $ssql .= " AND eFly='0'";
    }
    //Added By HJ On 30-12-2018 For Check POOL Status End
    $sql = "SELECT iVehicleTypeId,vVehicleType_" . $vLangCode . " as vVehicleType,iLocationid,iCountryId,iStateId,iCityId,eType,eFly FROM `vehicle_type` WHERE 1" . $ssql . " AND eStatus = 'Active'";
    $db_vehicletype = $obj->MySQLSelect($sql);
    if ($APP_TYPE == 'UberX') {
        $sql = "SELECT vCarType FROM `driver_vehicle` where iDriverId ='" . $iMemberId . "'";
        $db_vCarType = $obj->MySQLSelect($sql);
        if (count($db_vehicletype) > 0 && count($db_vCarType) > 0) {
            $vehicle_service_id = explode(",", $db_vCarType[0]['vCarType']);
            for ($i = 0;$i < count($db_vehicletype);$i++) {
                if (in_array($db_vehicletype[$i]['iVehicleTypeId'], $vehicle_service_id)) {
                    $db_vehicletype[$i]['VehicleServiceStatus'] = 'true';
                }
                else {
                    $db_vehicletype[$i]['VehicleServiceStatus'] = 'false';
                }
            }
        }
    } else {
        if (count($db_vehicletype) > 0) {
            $j = 0;
            for ($i = 0;$i < count($db_vehicletype);$i++) {
                //added by SP for fly stations on 6-9-2019, its bc fly vehicles are shown only if price in location wise fare is entered
                if ($fly == 'Yes' && $db_vehicletype[$i]['eFly'] == '1') {
                    //echo $fly."====".$db_vehicletype[$i]['eFly']."====".$db_vehicletype[$i]['vVehicleType']."<br>";
                    //echo $db_vehicletype[$i]['iVehicleTypeId']."<br>";
                    //if($db_vehicletype[$i]['iVehicleTypeId']=="496" || $db_vehicletype[$i]['iVehicleTypeId']=="505") {
                    //echo "SELECT DISTINCT(vehicle_type.iVehicleTypeId),vehicle_type.iVehicleTypeId,vehicle_type.vVehicleType_" . $vLangCode . " as vVehicleType,vehicle_type.iLocationid,vehicle_type.iCountryId,vehicle_type.iStateId,vehicle_type.iCityId,vehicle_type.eType,vehicle_type.eFly FROM vehicle_type RIGHT JOIN fly_location_wise_fare ON vehicle_type.iVehicleTypeId = fly_location_wise_fare.iVehicleTypeId WHERE 1 $ssql AND vehicle_type.eStatus = 'Active' AND fly_location_wise_fare.eStatus = 'Active' AND vehicle_type.iVehicleTypeId = ".$db_vehicletype[$i]['iVehicleTypeId'];exit;
                    //}
                    $db_vehicletype_fly = $obj->MySQLSelect("SELECT DISTINCT(vehicle_type.iVehicleTypeId),vehicle_type.iVehicleTypeId,vehicle_type.vVehicleType_" . $vLangCode . " as vVehicleType,vehicle_type.iLocationid,vehicle_type.iCountryId,vehicle_type.iStateId,vehicle_type.iCityId,vehicle_type.eType,vehicle_type.eFly FROM vehicle_type RIGHT JOIN fly_location_wise_fare ON vehicle_type.iVehicleTypeId = fly_location_wise_fare.iVehicleTypeId WHERE 1 $ssql AND vehicle_type.eStatus = 'Active' AND fly_location_wise_fare.eStatus = 'Active' AND vehicle_type.iVehicleTypeId = " . $db_vehicletype[$i]['iVehicleTypeId']);
                    //if(empty($db_vehicletype_fly)) break;
                    if (empty($db_vehicletype_fly)) {
                        //echo $fly."====".$db_vehicletype[$i]['eFly']."====".$db_vehicletype[$i]['vVehicleType']."<br>";die;
                        continue;
                    }
                }
                $db_vehicletype1[$j]['iVehicleTypeId'] = $db_vehicletype[$i]['iVehicleTypeId'];
                $db_vehicletype1[$j]['vVehicleType'] = $db_vehicletype[$i]['vVehicleType'];
                $db_vehicletype1[$j]['iLocationid'] = $db_vehicletype[$i]['iLocationid'];
                $db_vehicletype1[$j]['iCountryId'] = $db_vehicletype[$i]['iCountryId'];
                $db_vehicletype1[$j]['iStateId'] = $db_vehicletype[$i]['iStateId'];
                $db_vehicletype1[$j]['iCityId'] = $db_vehicletype[$i]['iCityId'];
                $db_vehicletype1[$j]['eType'] = $db_vehicletype[$i]['eType'];
                $db_vehicletype1[$j]['eFly'] = $db_vehicletype[$i]['eFly'];
                if ($db_vehicletype[$j]['iLocationid'] == "-1") {
                    $db_vehicletype1[$j]['SubTitle'] = $lbl_all;
                }
                else {
                    $getLocationname = $obj->MySQLSelect("SELECT vLocationName FROM location_master WHERE iLocationId = '" . $db_vehicletype[$i]['iLocationid'] . "'");
                    $vLocationName = "";
                    if (isset($getLocationname[0]['vLocationName'])) {
                        $vLocationName = $getLocationname[0]['vLocationName'];
                    }
                    $db_vehicletype1[$j]['SubTitle'] = $vLocationName;
                }
                if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
                    $db_vehicletype1[$j]['eRental'] = isRentalEnable($db_vehicletype[$i]['iVehicleTypeId']);
                }
                else {
                    $db_vehicletype1[$j]['eRental'] = 'No';
                }
                //added by SP for fly on 6-9-2019
                if ($db_vehicletype[$i]['eType'] == 'Ride' && $db_vehicletype[$i]['eFly'] == '1' && $fly == 'Yes') {
                    $db_vehicletype1[$j]['eType'] = 'Fly';
                }
                $j++;
            }
        }
    }
    $sql1 = "select * from make where eStatus = 'Active' ORDER BY vMake ASC ";
    $make = $obj->MySQLSelect($sql1);
    $start = @date('Y');
    $end = '1970';
    $year = array();
    for ($j = $start;$j >= $end;$j--) {
        $year[] = strval($j);
    }
    $carlist = $makemodalArr = array();
    if (count($make) > 0) {
        $j = 0;
        /* for ($i = 0; $i < count($make); $i++) {
        
          //$ModelArr['List']=get_value('model', '*', 'iMakeId', $make[$i]['iMakeId']);
        
          $sql = "SELECT  * FROM  `model` WHERE iMakeId = '" . $make[$i]['iMakeId'] . "' AND `eStatus` = 'Active' ORDER BY vTitle ASC ";
        
          $db_model = $obj->MySQLSelect($sql);
        
          if (count($db_model) > 0) {
        
          $ModelArr['List'] = $db_model;
        
          $carlist[$j]['iMakeId'] = $make[$i]['iMakeId'];
        
          $carlist[$j]['vMake'] = $make[$i]['vMake'];
        
          $carlist[$j]['vModellist'] = $ModelArr['List'];
        
          $j++;
        
          }
        
          } */
        $db_model = $obj->MySQLSelect("SELECT  * FROM  `model` WHERE  `eStatus` = 'Active' ORDER BY vTitle ASC");
        for ($h = 0;$h < count($db_model);$h++) {
            $makemodalArr[$db_model[$h]['iMakeId']][] = $db_model[$h];
        }
        for ($i = 0;$i < count($make);$i++) {
            $db_model_data = array();
            if (isset($makemodalArr[$make[$i]['iMakeId']])) {
                $db_model_data = $makemodalArr[$make[$i]['iMakeId']];
            }
            if (count($db_model_data) > 0) {
                $ModelArr['List'] = $db_model_data;
                $carlist[$j]['iMakeId'] = $make[$i]['iMakeId'];
                $carlist[$j]['vMake'] = $make[$i]['vMake'];
                $carlist[$j]['vModellist'] = $ModelArr['List'];
                $j++;
            }
        }
        $data['year'] = $year;
        $data['carlist'] = $carlist;
        $data['IS_SHOW_VEHICLE_TYPE'] = $isMultipleVehicleCategoryAvailable == true ? "Yes" : "No";
        //echo "<pre>";print_r($db_vehicletype1);die;
        $data['vehicletypelist'] = $db_vehicletype1;
        if (count($db_vehicletype) == 0) {
            $returnArr['message1'] = "LBL_EDIT_VEHI_RESTRICTION_TXT";
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $data;
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    //echo "<pre>";print_r($returnArr);exit;
    setDataResponse($returnArr);
}
###########################Add/Edit Driver Vehicle#######################################################
if ($type == "UpdateDriverVehicle") {
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? $_REQUEST['iDriverVehicleId'] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iMakeId = isset($_REQUEST["iMakeId"]) ? $_REQUEST["iMakeId"] : '';
    $iModelId = isset($_REQUEST["iModelId"]) ? $_REQUEST["iModelId"] : '';
    $iYear = isset($_REQUEST["iYear"]) ? $_REQUEST["iYear"] : '';
    $vLicencePlate = isset($_REQUEST["vLicencePlate"]) ? $_REQUEST["vLicencePlate"] : '';
    $eCarX = isset($_REQUEST["eCarX"]) ? $_REQUEST["eCarX"] : '';
    $eCarGo = isset($_REQUEST["eCarGo"]) ? $_REQUEST["eCarGo"] : '';
    $vColour = isset($_REQUEST["vColor"]) ? $_REQUEST["vColor"] : '';
    $vCarType = isset($_REQUEST["vCarType"]) ? $_REQUEST["vCarType"] : '';
    /* added for rental */
    $vRentalCarType = isset($_REQUEST["vRentalCarType"]) ? $_REQUEST["vRentalCarType"] : '';
    $handiCap = isset($_REQUEST["HandiCap"]) ? $_REQUEST["HandiCap"] : 'No';
    $iVehicleCategoryId = isset($_REQUEST["iVehicleCategoryId"]) ? $_REQUEST["iVehicleCategoryId"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride'; //'Ride', 'Delivery', 'UberX'
    $eAddedDeliverVehicle = isset($_REQUEST["eAddedDeliverVehicle"]) ? $_REQUEST["eAddedDeliverVehicle"] : 'No';
    $driverstatusQuery = "SELECT eStatus FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
    $iDriverStatus = $obj->MySQLSelect($driverstatusQuery);
    $eStatus = $iDriverStatus[0]['eStatus'];
    if ($eType == "UberX") {
        ## Check message if driver is online ##
        $db_available = $obj->MySQLSelect("select vAvailability from `register_driver` where iDriverId = '" . $iDriverId . "'");
        //print_R($db_available);die;
        if (isset($db_available[0]['vAvailability']) && $db_available[0]['vAvailability'] == "Available") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CHANGE_SERVICE_AFTER_OFFLINE_TXT";
            setDataResponse($returnArr);
        }
        ## Check message if driver is online ##
        $query = "SELECT iDriverVehicleId FROM `driver_vehicle` WHERE iDriverId = '" . $iDriverId . "' AND eType = 'UberX'";
        $result = $obj->MySQLSelect($query);
        if (count($result) > 0) {
            $iDriverVehicleId = $result[0]['iDriverVehicleId'];
        }
        else {
            $iDriverVehicleId = 0;
        }
    }
    else {
        ## Check message if driver is online ##
        $sql = "select vAvailability from `register_driver` where iDriverId = '" . $iDriverId . "'";
        $db_available = $obj->MySQLSelect($sql);
        $vAvailability = $db_available[0]['vAvailability'];
        if ($vAvailability == "Available") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_RESTRICT_VEHICLE_UPDATE";
            setDataResponse($returnArr);
        }
        ## Check message if driver is online ##
        
    }
    $action = ($iDriverVehicleId != 0) ? 'Edit' : 'Add';
    if ($action == "Add") {
        $eStatus = "inactive";
    }
    if (strtoupper($eStatus) != 'INACTIVE') {
        if ($action == "Edit" && $ENABLE_EDIT_DRIVER_VEHICLE == "No" && $eType != "UberX") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EDIT_VEHICLE_DISABLED";
            setDataResponse($returnArr);
        }
        else if ($eType == "UberX" && $action == "Edit" && $ENABLE_EDIT_DRIVER_SERVICE == "No") { // Added By HJ On 10-08-2019 For Check Permission As Per Discuss With KS
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EDIT_SERVICE_DISABLED";
            setDataResponse($returnArr);
        }
    }
    $sql = "select iCompanyId from `register_driver` where iDriverId = '" . $iDriverId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
    $Data_Driver_Vehicle['iDriverId'] = $iDriverId;
    $Data_Driver_Vehicle['iCompanyId'] = $iCompanyId;
    if (SITE_TYPE == "Demo") {
        $Data_Driver_Vehicle['eStatus'] = "Active";
    }
    else {
        if ($action == "Add") {
            //$Data_Driver_Vehicle['eStatus'] = $eStatus;
            if (strtoupper($eStatus) == 'ACTIVE') {
                $Data_Driver_Vehicle['eStatus'] = 'Active';
            }
            else if (strtoupper($eStatus) == 'INACTIVE') {
                $Data_Driver_Vehicle['eStatus'] = 'Inactive';
            }
            else {
                $Data_Driver_Vehicle['eStatus'] = $eStatus;
            }
        }
        else {
            //$Data_Driver_Vehicle['eStatus'] = "Active"; // Commented By HJ On 22-05-2019 As Per Discuss With BM Mam and KS Sir
            
        }
    }
    ## Update Vehicle Type For UberX ##
    if (($APP_TYPE == "UberX" || $eType == "UberX") && $action == "Edit") {
        $sql = "select vCarType from `driver_vehicle` where iDriverVehicleId = '" . $iDriverVehicleId . "'";
        $vCarTypeData = $obj->MySQLSelect($sql);
        $vCarTypeData = explode(",", $vCarTypeData[0]['vCarType']);
        $sql = "select iVehicleTypeId from `vehicle_type` where iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
        $db_vehicategoryid = $obj->MySQLSelect($sql);
        $array_vehiclie_id = array();
        for ($i = 0;$i < count($db_vehicategoryid);$i++) {
            array_push($array_vehiclie_id, $db_vehicategoryid[$i]['iVehicleTypeId']);
        }
        $arraydiff = array_diff($vCarTypeData, $array_vehiclie_id);
        $sssql2 = "";
        if (count($arraydiff) > 0) {
            $sssql2 = implode(",", $arraydiff);
        }
        if ($vCarType != "") {
            $vCarType = $vCarType . "," . $sssql2;
            if ($sssql2 == "") {
                $vCarType = substr($vCarType, 0, -1);
            }
        }
        else {
            $vCarType = $sssql2;
        }
    }
    /* --------------------------------------- */
    /* Insert a service Request as a Pending Request  */
    if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $eType == "UberX" && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') {
        if ($eStatus != 'inactive') {
            $newCarTypeData = explode(',', $_REQUEST['vCarType']);
            $remainingCats = array_diff($newCarTypeData, $vCarTypeData);
            foreach ($remainingCats as $key => $catVal) {
                if (!empty($catVal)) {
                    $tbl_dsr = 'driver_service_request';
                    $sql = "SELECT iDriverId from driver_service_request where iDriverId = '" . $iDriverId . "' AND iVehicleCategoryId = '" . $catVal . "'";
                    $existRequest = $obj->MySQLSelect($sql);
                    if (count($existRequest) == 0) {
                        $q = "INSERT INTO ";
                        $wheredrs = '';
                        $query = $q . " `" . $tbl_dsr . "` SET      

                            `iVehicleCategoryId` = '" . $catVal . "',

                            `iDriverId` = '" . $iDriverId . "',

                            `cRequestStatus` = 'Pending'" . $wheredrs;
                        $obj->sql_query($query);
                    }
                }
            }
            if (!empty($remainingCats)) {
                $sql = 'SELECT vEmail, vName ,vLastName ,vCode ,vPhone FROM  register_driver WHERE iDriverId = ' . $iDriverId . '';
                $existRequestdb = $obj->MySQLSelect($sql);
                /* Send Email to Driver */
                $getMaildata['name'] = $existRequestdb[0]['vName'] . " " . $existRequestdb[0]['vLastName'];
                $getMaildata['email'] = $existRequestdb[0]['vEmail'];
                $getMaildata['phone'] = "+" . $existRequestdb[0]['vCode'] . " " . $existRequestdb[0]['vPhone'];
                $mail = $generalobj->send_email_user('SERVICE_REQUEST_FROM_PROVIDER', $getMaildata);
            }
        }
    }
    /* End Request as a Pending Request  */
    /* ------------------------------ */
    ## Update Vehicle Type For UberX ##
    $Data_Driver_Vehicle['eCarX'] = $eCarX;
    $Data_Driver_Vehicle['eCarGo'] = $eCarGo;
    $Data_Driver_Vehicle['vCarType'] = $vCarType;
    /* added for rental */
    $Data_Driver_Vehicle['vRentalCarType'] = $vRentalCarType;
    $Data_Driver_Vehicle['eHandiCapAccessibility'] = $handiCap;
    if (strtoupper(PACKAGE_TYPE) == "SHARK") {
        $Data_Driver_Vehicle = ChildSeatAvailable($Data_Driver_Vehicle);
    }
    $Data_Driver_Vehicle['eType'] = $eType;
    $Data_Driver_Vehicle['eAddedDeliverVehicle'] = $eAddedDeliverVehicle;
    if ($iMakeId != "") {
        $Data_Driver_Vehicle['iMakeId'] = $iMakeId;
    }
    if ($iModelId != "") {
        $Data_Driver_Vehicle['iModelId'] = $iModelId;
    }
    if ($iYear != "") {
        $Data_Driver_Vehicle['iYear'] = $iYear;
    }
    $Data_Driver_Vehicle['vColour'] = $vColour;
    if ($vLicencePlate != "") {
        $Data_Driver_Vehicle['vLicencePlate'] = $vLicencePlate;
    }
    if ($APP_TYPE == 'UberX' || $eType == 'UberX') {
        $Data_Driver_Vehicle['iCompanyId'] = "1";
        $Data_Driver_Vehicle['iMakeId'] = "3";
        $Data_Driver_Vehicle['iModelId'] = "1";
        $Data_Driver_Vehicle['iYear'] = Date('Y');
        $Data_Driver_Vehicle['vLicencePlate'] = "My Services";
        $Data_Driver_Vehicle['eStatus'] = "Active";
        $Data_Driver_Vehicle['eCarX'] = "Yes";
        $Data_Driver_Vehicle['eCarGo'] = "Yes";
    }
    if ($action == "Add") {
        $id = $obj->MySQLQueryPerform("driver_vehicle", $Data_Driver_Vehicle, 'insert');
    }
    else {
        //echo "<pre>";print_r($Data_Driver_Vehicle);die;
        $where = " iDriverVehicleId = '" . $iDriverVehicleId . "'";
        $id = $obj->MySQLQueryPerform("driver_vehicle", $Data_Driver_Vehicle, 'update', $where);
    }
    /* ------------------------------ */
    /* This is for Reverse operation for new added services as it should be approve first */
    if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $eType == "UberX" && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') {
        if ($eStatus != 'inactive') {
            $sql = "SELECT vCarType from driver_vehicle where iDriverVehicleId = '" . $iDriverVehicleId . "'";
            $existRequest = $obj->MySQLSelect($sql);
            $existServices = explode(',', $existRequest[0]['vCarType']);
            $fnlServices = implode(',', array_diff($existServices, $remainingCats));
            // $Data_Driver_Vehicle['vCarType'] = $fnlServices;
            $sqlu = 'UPDATE driver_vehicle SET vCarType = "' . $fnlServices . '" WHERE iDriverVehicleId = "' . $iDriverVehicleId . '"';
            $existingServices = $obj->sql_query($sqlu);
        }
    }
    /* Request Service for Activation */
    /* ------------------------------ */
    if ($id > 0) {
        $returnArr['Action'] = "1";
        if ($eType == "UberX") {
            $returnArr['message'] = ($action == 'Add') ? 'LBL_SERVICE_ADD_SUCCESS_NOTE' : 'LBL_SERVICE_UPDATE_SUCCESS';
        }
        else {
            $returnArr['message'] = ($action == 'Add') ? 'LBL_VEHICLE_ADD_SUCCESS_NOTE' : 'LBL_VEHICLE_UPDATE_SUCCESS';
        }
        $returnArr['VehicleInsertId'] = $id;
        $returnArr['VehicleStatus'] = $Data_Driver_Vehicle['eStatus'];
        ### Send Email Code ###
        $sql = "SELECT vEmail,vName,vLastName FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
        $db_driver_detail = $obj->MySQLSelect($sql);
        $sql1 = "SELECT mo.vTitle,m.vMake from make as m LEFT JOIN model as mo on mo.iMakeId = m.iMakeId where m.iMakeId = '" . $iMakeId . "'";
        $db_make_data = $obj->MySQLSelect($sql1);
        if ($action == "Add" && $eType != "UberX") {
            $maildata['EMAIL'] = $db_driver_detail[0]['vEmail'];
            $maildata['NAME'] = $db_driver_detail[0]['vName'] . " " . $db_driver_detail[0]['vLastName'];
            $maildata['MAKE'] = $db_make_data[0]['vMake'];
            $maildata['MODEL'] = $db_make_data[0]['vTitle'];
            $maildata['DETAIL'] = "You can active this Vehicle by clicking below link<br>

             <p><a href='" . $tconfig["tsite_url"] . "admin/vehicle_add_form.php?id=$id'>Active this Vehicle</a></p>";
            $generalobj->send_email_user("VEHICLE_BOOKING_ADMIN", $maildata);
        }
        if ($action == "Add" && $eType == "UberX") {
        }
        ### Send Email Code ###
        
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################Add/Edit Driver Vehicle End#######################################################
################################Delete Driver Vehicle #######################################################
if ($type == 'deletedrivervehicle') {
    global $generalobj, $tconfig, $obj;
    $returnArr = array();
    $iMemberCarId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    //$iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    //Added By HJ On 19-07-2019 For Check Driver Vehicle Availability As Per Discuss With KS Start
    $getDriverData = $obj->MySQLSelect("SELECT iDriverVehicleId,vAvailability FROM register_driver WHERE iDriverId='" . $iDriverId . "'");
    $iDriverVehicleId = 0;
    $vAvailability = "Not Available";
    if (count($getDriverData) > 0) {
        $iDriverVehicleId = $getDriverData[0]['iDriverVehicleId'];
        $vAvailability = $getDriverData[0]['vAvailability'];
    }
    //added by SP when vehicle is active then it can not be deleted on 02-08-2019
    /* $getDriverVehicleData = $obj->MySQLSelect("SELECT eStatus FROM driver_vehicle WHERE iDriverVehicleId='" . $iDriverVehicleId . "'");
    
      if ($getDriverVehicleData[0]['eStatus'] == 'Active') {
    
      $returnArr['Action'] = 0;
    
      $returnArr['message'] = "LBL_ACTIVE_VEHICLE_NOT_DELETE";
    
      setDataResponse($returnArr);
    
      } */
    //added by SP when vehicle is active then it can not be deleted on 28-09-2019
    if ($iMemberCarId == $iDriverVehicleId) {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_ACTIVE_VEHICLE_NOT_DELETE";
        setDataResponse($returnArr);
    }
    $getTripData = $obj->MySQLSelect("SELECT iTripId FROM trips WHERE iActive NOT IN ('Canceled','Finished') AND iDriverVehicleId='" . $iDriverVehicleId . "'");
    if (count($getTripData) > 0) {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_DELETE_VEHICLE_RESTRICT_SERVICES_NOTE";
        setDataResponse($returnArr);
    }
    if ($iDriverVehicleId == $iMemberCarId && $vAvailability == "Available") {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_DELETE_VEHICLE_RESTRICT_NOTE";
        setDataResponse($returnArr);
    }
    //Added By HJ On 19-07-2019 For Check Driver Vehicle Availability As Per Discuss With KS End
    $sql = "UPDATE driver_vehicle set eStatus='Deleted' WHERE iDriverVehicleId='" . $iMemberCarId . "' AND iDriverId = '" . $iDriverId . "'";
    $db_sql = $obj->sql_query($sql);
    if ($obj->GetAffectedRows() > 0) {
        $returnArr['Action'] = 1;
        $returnArr['message'] = "LBL_DELETE_VEHICLE";
    }
    else {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################displaydrivervehicles##########################################################
if ($type == "displaydrivervehicles") {
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride'; //'Ride', 'Delivery', 'UberX'
    $ssql = "";
    if ($eType == "UberX") {
        $ssql .= " AND dv.eType = 'UberX'";
    }
    else {
        $ssql .= " AND dv.eType != 'UberX'";
    }
    $sql = "select iCompanyId from `register_driver` where iDriverId = '" . $iMemberId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
    if ($APP_TYPE == 'UberX') {
        $sql = "SELECT *,eChildSeatAvailable AS eChildAccessibility,eWheelChairAvailable AS eWheelChairAccessibility FROM driver_vehicle where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $iMemberId . "' and eStatus != 'Deleted'";
        $db_vehicle = $obj->MySQLSelect($sql);
    }
    else {
        $sql = "SELECT m.vTitle, mk.vMake,dv.*,eChildSeatAvailable AS eChildAccessibility,eWheelChairAvailable AS eWheelChairAccessibility,case WHEN (dv.vInsurance='' OR dv.vPermit='' OR dv.vRegisteration='') THEN 'TRUE' ELSE 'FALSE' END as 'VEHICLE_DOCUMENT' FROM driver_vehicle as dv JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON dv.iMakeId=mk.iMakeId where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $iMemberId . "' and dv.eStatus != 'Deleted' $ssql Order By dv.iDriverVehicleId desc";
        $db_vehicle = $obj->MySQLSelect($sql);
        $db_vehicle_new = $db_vehicle;
        for ($i = 0;$i < count($db_vehicle);$i++) {
            $vCarType = $db_vehicle[$i]['vCarType'];
            $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
            $db_cartype = $obj->MySQLSelect($sql);
            $k = 0;
            if (count($db_cartype) > 0) {
                for ($j = 0;$j < count($db_cartype);$j++) {
                    $eType = $db_cartype[$j]['eType'];
                    if ($eType == "UberX") {
                        //unset($db_vehicle_new[$i]);
                        
                    }
                }
            }
        }
    }
    $db_vehicle_new = array_values($db_vehicle_new);
    if (count($db_vehicle_new) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle_new;
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_VEHICLES_FOUND";
    }
    setDataResponse($returnArr);
}
###########################Display Driver's Vehicle Listing End##########################################################
###########################Add/Update User's Vehicle Listing End##########################################################
if ($type == "UpdateUserVehicleDetails") {
    global $generalobj, $tconfig;
    $iUserVehicleId = isset($_REQUEST['iUserVehicleId']) ? $_REQUEST['iUserVehicleId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iMakeId = isset($_REQUEST["iMakeId"]) ? $_REQUEST["iMakeId"] : '';
    $iModelId = isset($_REQUEST["iModelId"]) ? $_REQUEST["iModelId"] : '';
    $iYear = isset($_REQUEST["iYear"]) ? $_REQUEST["iYear"] : '';
    $vLicencePlate = isset($_REQUEST["vLicencePlate"]) ? $_REQUEST["vLicencePlate"] : '';
    $vColour = isset($_REQUEST["vColour"]) ? $_REQUEST["vColour"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Inactive';
    //$vImage = isset($_REQUEST["vImage"]) ? $_REQUEST["vImage"] : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger_vehicle'] . "/" . $iUserVehicleId . "/"; // /webimages/upload/uservehicle
    // echo $Photo_Gallery_folder."===";
    if (!is_dir($Photo_Gallery_folder)) mkdir($Photo_Gallery_folder, 0777);
    $action = ($iUserVehicleId != '') ? 'Edit' : 'Add';
    $Data_User_Vehicle['iUserId'] = $iUserId;
    $Data_User_Vehicle['iMakeId'] = $iMakeId;
    $Data_User_Vehicle['iModelId'] = $iModelId;
    $Data_User_Vehicle['iYear'] = $iYear;
    $Data_User_Vehicle['vLicencePlate'] = $vLicencePlate;
    $Data_User_Vehicle['eStatus'] = $eStatus;
    $Data_User_Vehicle['vColour'] = $vColour;
    //$Data_User_Vehicle['vImage']=$vImage;
    if ($action == "Add") {
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_User_Vehicle, 'insert');
        $updateimageid = $id;
    }
    else {
        $where = " iUserVehicleId = '" . $iUserVehicleId . "'";
        $updateimageid = $iUserVehicleId;
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_User_Vehicle, 'update', $where);
    }
    if ($image_name != "") {
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
        $vImageName = $vFile[0];
        $Data_passenger["vImage"] = $vImageName;
        $where_image = " iUserVehicleId = '" . $updateimageid . "'";
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_passenger, 'update', $where_image);
    }
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
#################################################################################
if ($type == "displayuservehicles") {
    global $generalobj, $tconfig;
    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    $sql = "SELECT m.vTitle, mk.vMake,uv.*  FROM user_vehicle as uv JOIN model m ON uv.iModelId=m.iModelId JOIN make mk ON uv.iMakeId=mk.iMakeId where iUserId = '" . $iUserId . "' and uv.eStatus != 'Deleted'";
    $db_vehicle = $obj->MySQLSelect($sql);
    if (count($db_vehicle) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle;
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "No Vehicles Found";
    }
    setDataResponse($returnArr);
}
##################################################################################
############################## Get DriverDetail ###################################
if ($type == "getDriverDetail") {
    $Did = isset($_REQUEST["DriverAutoId"]) ? $_REQUEST["DriverAutoId"] : '';
    $GCMID = isset($_REQUEST["GCMID"]) ? $_REQUEST["GCMID"] : '';
    $sql = "SELECT iGcmRegId FROM `register_driver` WHERE iDriverId='$Did'";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $iGCMregID = $Data[0]['iGcmRegId'];
        if ($GCMID != '') {
            if ($iGCMregID != $GCMID) {
                $where = " iDriverId = '$Did' ";
                $Data_update_driver['iGcmRegId'] = $GCMID;
                $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
            }
        }
    }
    setDataResponse(getDriverDetailInfo($Did));
}
###########################################################################
if ($type == "getOngoingUserTrips") {
    global $generalobj, $obj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    $Data1 = array();
    if ($iUserId != "") {
        if ($APP_TYPE == "UberX" || strtoupper(PACKAGE_TYPE) == "STANDARD") {
            $sql1 = "SELECT rd.iDriverId,rd.vImage as driverImage,concat(rd.vName,' ',rd.vLastName) as driverName, rd.vCode ,rd.vPhone as driverMobile ,rd.vLatitude as driverLatitude,rd.vLongitude as driverLongitude,if(tr.eType = 'Multi-Delivery',tr.iActive,rd.vTripStatus) as driverStatus, rd.vAvgRating as driverRating, tr.`vRideNo`, tr.tSaddress,tr.iTripId, tr.iVehicleTypeId, tr.tTripRequestDate, tr.eFareType, tr.vTimeZone, tr.eType, tr.eServiceLocation, tr.vWorkLocation, tr.vWorkLocationLatitude, tr.vWorkLocationLongitude, tr.eSelectWorkLocation, tr.tVehicleTypeData, tr.tVehicleTypeFareData, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLangCode . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLangCode . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(tr.tVehicleTypeFareData, '$.ParentVehicleCategoryId'))), '') as ParentCategoryName from trips as tr  LEFT JOIN register_driver as rd ON rd.iDriverId=tr.iDriverId WHERE tr.iActive != 'Canceled' AND if(tr.vVerificationMethod='None' or tr.vVerificationMethod='',tr.iActive != 'Finished','1=1') AND tr.iUserId='" . $iUserId . "' AND tr.eSystem = 'General' AND (tr.eType = 'UberX' or tr.eType = 'Multi-Delivery') GROUP BY tr.iTripId ORDER BY tr.iTripId DESC";
        }
        else {
            $sql1 = "SELECT rd.iDriverId,rd.vImage as driverImage,concat(rd.vName,' ',rd.vLastName) as driverName, rd.vCode ,rd.vPhone as driverMobile ,rd.vLatitude as driverLatitude,rd.vLongitude as driverLongitude,if(tr.eType = 'Multi-Delivery',tr.iActive,rd.vTripStatus) as driverStatus, rd.vAvgRating as driverRating, tr.`vRideNo`, tr.tSaddress,tr.iTripId, tr.iVehicleTypeId, tr.tTripRequestDate, tr.eFareType, tr.vTimeZone, tr.eType, tr.eServiceLocation, tr.vWorkLocation, tr.vWorkLocationLatitude, tr.vWorkLocationLongitude, tr.eSelectWorkLocation, tr.tVehicleTypeData, tr.tVehicleTypeFareData, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLangCode . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLangCode . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(tr.tVehicleTypeFareData, '$.ParentVehicleCategoryId'))), '') as ParentCategoryName from trips as tr  LEFT JOIN register_driver as rd ON rd.iDriverId=tr.iDriverId LEFT JOIN trips_delivery_locations as tdl ON tdl.iTripId=tr.iTripId WHERE tr.iActive != 'Canceled' AND if(tr.vVerificationMethod='None' or tr.vVerificationMethod='',tr.iActive != 'Finished','1=1') AND tr.iUserId='" . $iUserId . "' AND tr.eSystem = 'General' AND (tr.eType = 'UberX' or tr.eType = 'Multi-Delivery') AND if(tr.eType = 'Multi-Delivery',tdl.eSignVerification = 'No','1=1') GROUP BY tr.iTripId ORDER BY tr.iTripId DESC";
        }
        $Data1 = $obj->MySQLSelect($sql1);
        if (count($Data1) > 0) {
            $iVehicleCategoryIds_str_ufx = "";
            for ($i = 0;$i < count($Data1);$i++) {
                $Data1[$i]['moreServices'] = "No";
                if ($SERVICE_PROVIDER_FLOW == "Provider" && $Data1[$i]['eType'] == "UberX" && $Data1[$i]['eServiceLocation'] == "Driver") {
                    $Data1[$i]['tSaddress'] = $Data1[$i]['vWorkLocation'];
                }
                $Data1[$i]['SelectedTypeName'] = $Data1[$i]['vVehicleType'];
                $Data1[$i]['vServiceTitle'] = $Data1[$i]['vVehicleType'];
                $Data1[$i]['vServiceDetailTitle'] = $Data1[$i]['vVehicleType'];
                if ($Data1[$i]['eType'] == "UberX" && !empty($Data1[$i]['tVehicleTypeFareData'])) {
                    $Data1[$i]['moreServices'] = "Yes";
                    $tVehicleTypeFareDataArr = (array)json_decode($Data1[$i]['tVehicleTypeFareData']);
                    $ParentVehicleCategoryId = isset($tVehicleTypeFareDataArr['ParentVehicleCategoryId']) ? $tVehicleTypeFareDataArr['ParentVehicleCategoryId'] : 0;
                    if ($ParentVehicleCategoryId == 0) {
                        $tVehicleTypeFareDataArr_fareArr = (array)($tVehicleTypeFareDataArr['FareData']);
                        if (count($tVehicleTypeFareDataArr_fareArr) > 0) {
                            $sql_parent_id = "SELECT (SELECT vcs.vCategory_" . $vLang . " FROM " . $sql_vehicle_category_table_name . " as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as vCategory FROM " . $sql_vehicle_category_table_name . " as vc, vehicle_type as vt WHERE vc.iVehicleCategoryId = vt.iVehicleCategoryId AND vt.iVehicleTypeId = '" . $tVehicleTypeFareDataArr_fareArr[0]->id . "'";
                            $parent_data_arr = $obj->MySQLSelect($sql_parent_id);
                            $Data1[$i]['vCategory'] = $parent_data_arr[0]['vCategory'];
                        }
                    }
                    else {
                        $Data1[$i]['ParentVehicleCategoryId'] = $ParentVehicleCategoryId;
                        $iVehicleCategoryIds_str_ufx = $iVehicleCategoryIds_str_ufx == "" ? $ParentVehicleCategoryId : $iVehicleCategoryIds_str_ufx . "," . $ParentVehicleCategoryId;
                    }
                    $Data1[$i]['eFareTypeServices'] = $tVehicleTypeFareDataArr['eFareTypeServices'];
                    if (!empty($Data1[$i]['ParentVehicleCategoryId']) && $tVehicleTypeFareDataArr['eFareTypeServices'] == "Fixed") {
                        $Data1[$i]['vServiceDetailTitle'] = $Data1[$i]['ParentCategoryName'];
                    }
                    else {
                        $Data1[$i]['vServiceDetailTitle'] = $parent_cat_arr[$Data1[$i]['ParentCategoryName']] . " - " . $Data1[$i]['vVehicleType'];
                    }
                }
                // Convert Into Timezone
                $tripTimeZone = $Data1[$i]['vTimeZone'];
                if ($tripTimeZone != "") {
                    $serverTimeZone = date_default_timezone_get();
                    $Data1[$i]['tTripRequestDate'] = converToTz($Data1[$i]['tTripRequestDate'], $tripTimeZone, $serverTimeZone);
                }
                // Convert Into Timezone
                $Data1[$i]['dDateOrig'] = $Data1[$i]['tTripRequestDate'];
                /* ---------------------Multi delivery start--------------------------- */
                if ($Data1[$i]['eType'] == "Multi-Delivery") {
                    $sql = "SELECT tdl.iTripDeliveryLocationId,tdl.eSignVerification,tr.iActive, tr.eServiceLocation from trips as tr LEFT JOIN trips_delivery_locations as tdl ON tdl.iTripId=tr.iTripId

                                    WHERE tr.iTripId='" . $Data1[$i]['iTripId'] . "' AND tr.eSystem = 'General' GROUP BY tr.iTripId ORDER BY tr.iTripId DESC";
                    $Data2 = $obj->MySQLSelect($sql);
                    for ($j = 0;$j < count($Data2);$j++) {
                        if ($Data2[$j]['eSignVerification'] == "No") {
                            $Data1[$i]['iTripDeliveryLocationId'] = $Data2[$j]['iTripDeliveryLocationId'];
                            $Data1[$i]['eSignVerification'] = $Data2[$j]['eSignVerification'];
                        }
                    }
                }
                /* ---------------------Multi delivery end ---------------------------- */
            }
            $returnArr['Action'] = "1";
            $returnArr['SERVER_TIME'] = date('Y-m-d H:i:s');
            $returnArr['message'] = $Data1;
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_DATA_AVAIL";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "getTripDeliveryLocations") {
    global $generalobj, $obj;
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userType = isset($_REQUEST["userType"]) ? $_REQUEST["userType"] : 'Passenger';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    date_default_timezone_set($vTimeZone); // Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3784
    $Data = array();
    if ($iTripId != "") {
        if ($userType != 'Passenger') {
            $sql = "SELECT ru.iUserId,ru.vimgname as riderImage,concat(ru.vName,' ',ru.vLastName) as riderName, ru.vPhoneCode ,ru.vPhone as riderMobile,ru.vTripStatus as driverStatus, ru.vAvgRating as riderRating,tr.* from trips as tr LEFT JOIN register_user as ru ON ru.iUserId=tr.iUserId WHERE tr.iTripId = '" . $iTripId . "'";
            $dataUser = $obj->MySQLSelect($sql);
            $Data['driverDetails'] = $dataUser[0];
            $iMemberId = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
            $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
        }
        else {
            $sql = "SELECT rd.iDriverId,rd.vImage as driverImage,concat(rd.vName,' ',rd.vLastName) as driverName, rd.vCode ,rd.vPhone as driverMobile,rd.vTripStatus as driverStatus, rd.vAvgRating as driverRating,tr.* from trips as tr LEFT JOIN register_driver as rd ON rd.iDriverId=tr.iDriverId WHERE tr.iTripId = '" . $iTripId . "'";
            $dataUser = $obj->MySQLSelect($sql);
            $Data['driverDetails'] = $dataUser[0];
            $iMemberId = get_value('trips', 'iUserId', 'iTripId', $iTripId, '', 'true');
            $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
        }
        if ($vLangCode == "" || $vLangCode == NULL) {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
        $lbl_at = $languageLabelsArr['LBL_AT_GENERAL'];
        $lbl_minago = $languageLabelsArr['LBL_MIN_AGO'];
        if ($userType == "Driver") {
            $Driver_Acceprt_Delivery_Request = $languageLabelsArr['LBL_DRIVER1_ACCEPTED_DELIVERY_REQUEST_TXT'];
            $Driver_Arrived_Pick_Location = $languageLabelsArr[$dataUser[0]['eServiceLocation'] == "Driver" ? 'LBL_USER_ARRIVED_SERVICE_LOCATION' : 'LBL_DRIVER1_ARRIVED_PICK_LOCATION_TXT'];
            $Driver_Start_job = $languageLabelsArr['LBL_PROVIDER1_START_JOB_TXT'];
            $Driver_Finished_job = $languageLabelsArr['LBL_PROVIDER1_FINISHED_JOB_TXT'];
        }
        else {
            $Driver_Acceprt_Delivery_Request = $languageLabelsArr[$dataUser[0]['eServiceLocation'] == "Driver" ? 'LBL_DRIVER_ACCEPTED_SPECIFIED_LOC_REQUEST' : 'LBL_DRIVER_ACCEPTED_DELIVERY_REQUEST_TXT'];
            $Driver_Arrived_Pick_Location = $languageLabelsArr[$dataUser[0]['eServiceLocation'] == "Driver" ? 'LBL_USER_ARRIVED_SERVICE_LOCATION_TXT' : 'LBL_DRIVER_ARRIVED_PICK_LOCATION_TXT'];
            if ($dataUser[0]['eType'] == "Multi-Delivery") {
                $Driver_Acceprt_Delivery_Request = $languageLabelsArr['LBL_DELIVERY_DRIVER_ACCEPT_REQUEST_MULTI'];
                $Driver_Arrived_Pick_Location = $languageLabelsArr['LBL_DELIVERY_DRIVER_ARRIVED_MULTI'];
            }
            $Driver_Start_job = $languageLabelsArr['LBL_PROVIDER_START_JOB_TXT'];
            $Driver_Finished_job = $languageLabelsArr['LBL_PROVIDER_FINISHED_JOB_TXT'];
        }
        $testBool = 1;
        if (count($dataUser) > 0) {
            if ($SERVICE_PROVIDER_FLOW == "Provider" && $dataUser[0]['eType'] == "UberX" && $dataUser[0]['eServiceLocation'] == "Driver") {
                $dataUser[0]['tSaddress'] = $dataUser[0]['vWorkLocation'];
                $Data['driverDetails']['tSaddress'] = $dataUser[0]['vWorkLocation'];
            }
            $Data['States'] = array();
            $Data_tTripRequestDate = $dataUser[0]['tTripRequestDate'];
            $Data_tTripRequestDate_convert = $dataUser[0]['tTripRequestDate'];
            $Data_tDriverArrivedDate = $dataUser[0]['tDriverArrivedDate'];
            $Data_tDriverArrivedDate_convert = $dataUser[0]['tDriverArrivedDate'];
            $Data_dDeliveredDate = $dataUser[0]['dDeliveredDate'];
            $Data_dDeliveredDate_convert = $dataUser[0]['dDeliveredDate'];
            $Data_tStartDate = $dataUser[0]['tStartDate'];
            $Data_tStartDate_convert = $dataUser[0]['tStartDate'];
            $Data_tEndDate = $dataUser[0]['tEndDate'];
            $Data_tEndDate_convert = $dataUser[0]['tEndDate'];
            if (!empty($vTimeZone)) {
                if ($Data_tTripRequestDate != "" && $Data_tTripRequestDate != "0000-00-00 00:00:00") {
                    $Data_tTripRequestDate_convert = converToTz($Data_tTripRequestDate_convert, $vTimeZone, date_default_timezone_get());
                }
                if ($Data_tDriverArrivedDate != "" && $Data_tDriverArrivedDate != "0000-00-00 00:00:00") {
                    $Data_tDriverArrivedDate_convert = converToTz($Data_tDriverArrivedDate_convert, $vTimeZone, date_default_timezone_get());
                }
                if ($Data_dDeliveredDate != "" && $Data_dDeliveredDate != "0000-00-00 00:00:00") {
                    $Data_dDeliveredDate_convert = converToTz($Data_dDeliveredDate_convert, $vTimeZone, date_default_timezone_get());
                }
                if ($Data_tStartDate != "" && $Data_tStartDate != "0000-00-00 00:00:00") {
                    $Data_tStartDate_convert = converToTz($Data_tStartDate_convert, $vTimeZone, date_default_timezone_get());
                }
                if ($Data_tEndDate != "" && $Data_tEndDate != "0000-00-00 00:00:00") {
                    $Data_tEndDate_convert = converToTz($Data_tEndDate_convert, $vTimeZone, date_default_timezone_get());
                }
            }
            $i = 0;
            if ($Data_tTripRequestDate != "" && $Data_tTripRequestDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = 'Provider accepted the request.';
                if ($userType != 'Passenger') {
                    $msg = 'You accepted the request.';
                }
                $Data['States'][$i]['text'] = $Driver_Acceprt_Delivery_Request;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tTripRequestDate));
                $Data['States'][$i]['dateOrig'] = $Data_tTripRequestDate_convert;
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tTripRequestDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Accept";
                $i++;
            }
            else {
                $testBool = 0;
            }
            if ($Data_tDriverArrivedDate != "" && $Data_tDriverArrivedDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = "Provider arrived to your location.";
                if ($userType != 'Passenger') {
                    $msg = "You arrived to user's location.";
                }
                $Data['States'][$i]['text'] = $Driver_Arrived_Pick_Location;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tDriverArrivedDate));
                $Data['States'][$i]['dateOrig'] = $Data_tDriverArrivedDate_convert;
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tDriverArrivedDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Arrived";
                $i++;
            }
            else {
                $testBool = 0;
            }
            if ($Data_tStartDate != "" && $Data_tStartDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = 'Provider has started the job.';
                if ($userType != 'Passenger') {
                    $msg = 'You started the job.';
                }
                $Data['States'][$i]['text'] = $Driver_Start_job;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tStartDate));
                $Data['States'][$i]['dateOrig'] = $Data_tStartDate_convert;
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tStartDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Onway";
                $i++;
            }
            else {
                $testBool = 0;
            }
            if ($Data_tEndDate != "" && $Data_tEndDate != "0000-00-00 00:00:00" && $testBool == 1 && $dataUser[0]['iActive'] == "Finished") {
                $msg = 'Provider has completed the job.';
                if ($userType != 'Passenger') {
                    $msg = 'You completed the job.';
                }
                $Data['States'][$i]['text'] = $Driver_Finished_job;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tEndDate));
                $Data['States'][$i]['dateOrig'] = $Data_tEndDate_convert;
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tEndDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Delivered";
                $i++;
            }
        }
        else {
            $Data['States'] = array();
        }
        if (count($Data) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $Data;
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_DRIVER_FOUND";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_TRIP_FOUND";
    }
    setDataResponse($returnArr);
}
############################################################################
if ($type == "SetTimeForTrips") {
    global $generalobj, $obj;
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $iTripTimeId = isset($_REQUEST["iTripTimeId"]) ? $_REQUEST["iTripTimeId"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $tripData = $obj->MySQLSelect("SELECT iUserId,eType,eBookingFrom FROM `trips` WHERE iTripId='$iTripId'");
    $eBookingFrom = $tripData[0]['eBookingFrom'];
    if (($iUserId == "" || $eType == "") && $iTripId > 0) {
        if (count($tripData) > 0) {
            $eType = $tripData[0]['eType'];
            $iUserId = $tripData[0]['iUserId'];
        }
    }
    $dTime = date('Y-m-d H:i:s');
    if ($iTripTimeId == '') {
        $Data_update['dResumeTime'] = $dTime;
        $Data_update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'insert');
        $returnArr['Action'] = "1";
        $returnArr['message'] = $id;
        $alertMsgCode = 1;
        $alertMsg = "Your current trip is put on hold by driver.";
    }
    else {
        $where = " iTripTimeId = '$iTripTimeId'";
        $Data_update['dPauseTime'] = $dTime;
        $Data_update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'update', $where);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $iTripTimeId;
        $alertMsgCode = 2;
        $alertMsg = "Your trip is resumed.";
    }
    $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$iTripId'";
    $db_tripTimes = $obj->MySQLSelect($sql22);
    if ($eType == "Ride") {
        $deviceTokens_arr_ios = $registation_ids_new = array();
        $alertSendAllowed = true;
        $getUserData = $obj->MySQLSelect("SELECT iAppVersion,eDeviceType,iGcmRegId,vLang,tSessionId FROM register_user WHERE iUserId='" . $iUserId . "'");
        if (count($getUserData) > 0) {
            $iGcmRegId = $getUserData[0]['iGcmRegId'];
            $eDeviceType = $getUserData[0]['eDeviceType'];
            $vLang = $getUserData[0]['vLang'];
            if ($vLang == "" || $vLang == NULL) {
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
            $alertMsg = $languageLabelsArr['LBL_TRIP_RESUMED_TXT'];
            if ($alertMsgCode == 1) {
                $alertMsg = $languageLabelsArr['LBL_TRIP_PUT_ON_HOLD_TXT'];
            }
            $message = $alertMsg;
            if ($alertSendAllowed == true && $eBookingFrom != 'Kiosk') {
                if ($eDeviceType == "Android") {
                    array_push($registation_ids_new, $iGcmRegId);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                }
                else if ($eDeviceType != "Android") {
                    array_push($deviceTokens_arr_ios, $iGcmRegId);
                    if ($message != "") {
                        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }
                }
            }
        }
    }
    $totalSec = 0;
    $timeState = 'Pause';
    $iTripTimeId = '';
    foreach ($db_tripTimes as $dtT) {
        if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
            $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
        }
        else {
            $totalSec += strtotime(date('Y-m-d H:i:s')) - strtotime($dtT['dResumeTime']);
        }
    }
    $returnArr['totalTime'] = $totalSec;
    setDataResponse($returnArr);
}
###########################################################################
######################################################################
if ($type == "UpdateBookingStatus") {
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $vCancelReason = isset($_REQUEST['vCancelReason']) ? $_REQUEST['vCancelReason'] : '';
    $iCancelReasonId = isset($_REQUEST['iCancelReasonId']) ? $_REQUEST['iCancelReasonId'] : '0';
    $eConfirmByProvider = isset($_REQUEST['eConfirmByProvider']) ? $_REQUEST['eConfirmByProvider'] : 'No';
    $dataType = isset($_REQUEST["DataType"]) ? $_REQUEST["DataType"] : '';
    if ($eConfirmByProvider == "" || $eConfirmByProvider == NULL) {
        $eConfirmByProvider = "No";
    }
    //echo "<pre>";print_r($_REQUEST);die;
    $sqldata = "SELECT eStatus,eType,ePayType FROM `cab_booking` WHERE iCabBookingId ='" . $iCabBookingId . "'";
    $BookingData = $obj->MySQLSelect($sqldata);
    $BookingStatus = $BookingData[0]['eStatus'];
    $APP_TYPE = $BookingData[0]['eType'];
    if ($BookingStatus == "Cancel") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_MANAUL_BOOKING_CANCELLED_MSG";
        $returnArr['DO_RELOAD'] = "Yes";
        setDataResponse($returnArr);
    }
    
    //added by SP for checking wallet balance in uberx when driver accept request of user of book later start
    if($eStatus == "Accepted" && $eConfirmByProvider=='No'){ 
     $ePayType_cabbooking = $BookingData[0]['ePayType'];
     //$Data1['ACCEPT_CASH_TRIPS'] = "Yes";
     if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
         $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
 
         ///echo $WALLET_MIN_BALANCE."===".$user_available_balance;die;
         if ($WALLET_MIN_BALANCE > $user_available_balance) {
             //$Data1['ACCEPT_CASH_TRIPS'] = "No";
             //Added BY HJ On 23-09-2019 As Per Discuss with BM and GP Mam Start
             
             if ($ePayType_cabbooking == "Cash" && $APP_TYPE == "UberX") {
                 $returnArr = array();
                 $returnArr['Action'] = "0"; // code is invalid
                 $returnArr["message"] = "LBL_CHECK_PROVIDER_MIN_WALLET_BALANCE_TXT";
                 echo json_encode($returnArr);
                 exit;
             }
             //Added BY HJ On 23-09-2019 As Per Discuss with BM and GP Mam End
         }
     }
    }
    //added by SP for checking wallet balance in uberx when driver accept request of user of book later end
    
    
    ############################################################### CheckPendingBooking UBERX  For same Time booking (Accept , Pending)###########################################################
    if ($APP_TYPE == "UberX") {
        $sql_book = "SELECT dBooking_date from cab_booking WHERE iCabBookingId ='" . $iCabBookingId . "'";
        $checkbooking = $obj->MySQLSelect($sql_book);
        $dBooking_date = $checkbooking[0]['dBooking_date'];
        $sql = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $iDriverId . "' AND dBooking_date = '" . $dBooking_date . "' AND eStatus = 'Accepted' AND iCabBookingId != '" . $iCabBookingId . "'";
        $pendingacceptdriverbooking = $obj->MySQLSelect($sql);
        if (count($pendingacceptdriverbooking) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PENDING_PLUS_ACCEPT_BOOKING_AVAIL_TXT";
            $returnArr['message1'] = "Accept";
            setDataResponse($returnArr);
        }
        else {
            $sql = "SELECT iCabBookingId from cab_booking WHERE iDriverId ='" . $iDriverId . "' AND dBooking_date = '" . $dBooking_date . "' AND eStatus = 'Pending' AND iCabBookingId != '" . $iCabBookingId . "'";
            $pendingdriverbooking = $obj->MySQLSelect($sql);
            if (count($pendingdriverbooking) > 0 && $eConfirmByProvider == "No") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_PENDING_BOOKING_AVAIL_TXT";
                $returnArr['message1'] = "Pending";
                $returnArr['BookingFound'] = "Yes";
                setDataResponse($returnArr);
            }
        }
    }
    /* For DriverSubscription added by SP start */
    if (checkDriverSubscriptionModule() && $eStatus != 'Declined') {
        $returnSubStatus = 0;
        $returnSubStatus = checkDriverSubscribed($iDriverId);
        if ($returnSubStatus == 1) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PENDING_MIXSUBSCRIPTION";
            setDataResponse($returnArr);
        }
        else if ($returnSubStatus == 2) {
            $returnArr['Action'] = "0";
            //$returnArr['message'] = "PENDING_SUBSCRIPTION";
            $returnArr['message'] = "LBL_PENDING_SUBSCRIPTION_TEXT";
            setDataResponse($returnArr);
        }
    }
    /* For DriverSubscription added by SP end */
    ############################################################### CheckPendingBooking UBERX ###########################################################
    ### Checking For booking timing availablity when driver accept booking ###
    if ($eConfirmByProvider == "No" && $eStatus == "Accepted" && $APP_TYPE == "UberX") {
        $sql = "SELECT dBooking_date from cab_booking WHERE iCabBookingId ='" . $iCabBookingId . "'";
        $bookingdate = $obj->MySQLSelect($sql);
        $dBooking_date = $bookingdate[0]['dBooking_date'];
        $additional_mins = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;
        $FromDate = date("Y-m-d H:i:s", strtotime($dBooking_date . "-" . $additional_mins . " minutes"));
        $ToDate = date("Y-m-d H:i:s", strtotime($dBooking_date . "+" . $additional_mins . " minutes"));
        $sql = "SELECT iCabBookingId from cab_booking WHERE (dBooking_date BETWEEN '" . $FromDate . "' AND '" . $ToDate . "') AND iCabBookingId != '" . $iCabBookingId . "' AND eStatus = 'Accepted' AND iDriverId = '" . $iDriverId . "'";
        $checkbookingdate = $obj->MySQLSelect($sql);
        if (count($checkbookingdate) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['BookingFound'] = "Yes";
            $returnArr['message'] = "LBL_PROVIDER_JOB_FOUND_TXT";
            setDataResponse($returnArr);
        }
    }
    ### Checking For booking timing availablity when driver accept booking ###
    $where = " iCabBookingId = '$iCabBookingId' ";
    $Data['eStatus'] = $eStatus;
    $Data['vCancelReason'] = $vCancelReason;
    $Data['iCancelReasonId'] = $iCancelReasonId;
    $Update_Booking_id = $obj->MySQLQueryPerform("cab_booking", $Data, 'update', $where);
    if ($Update_Booking_id) {
        $sql = "SELECT cb.*,concat(ru.vName,' ',ru.vLastName) as UserName,ru.vEmail,ru.vPhone,ru.vPhoneCode,ru.vLang as userlang,concat(rd.vName,' ',rd.vLastName) as DriverName from cab_booking as cb LEFT JOIN register_user as ru ON ru.iUserId=cb.iUserId LEFT JOIN register_driver as rd ON rd.iDriverId=cb.iDriverId WHERE cb.iCabBookingId ='" . $iCabBookingId . "'";
        $bookingdetail = $obj->MySQLSelect($sql);
        $UserPhoneNo = $bookingdetail[0]['vPhone'];
        $UserPhoneCode = $bookingdetail[0]['vPhoneCode'];
        $UserLang = $bookingdetail[0]['userlang'];
        $Data1['vRider'] = $bookingdetail[0]['UserName'];
        $Data1['vDriver'] = $bookingdetail[0]['DriverName'];
        $Data1['vRiderMail'] = $bookingdetail[0]['vEmail'];
        $Data1['vBookingNo'] = $bookingdetail[0]['vBookingNo'];
        $Data1['dBookingdate'] = date('Y-m-d H:i', strtotime($bookingdetail[0]['dBooking_date']));
        if ($eStatus == "Accepted") {
            $returnArr['message'] = "LBL_JOB_ACCEPTED";
            $sendMailtoUser = $generalobj->send_email_user("MANUAL_BOOKING_ACCEPT_BYDRIVER_SP", $Data1);
        }
        else if ($eStatus == "Declined") {
            $returnArr['message'] = "LBL_JOB_DECLINED";
            $sendMailtoUser = $generalobj->send_email_user("MANUAL_BOOKING_DECLINED_BYDRIVER_SP", $Data1);
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($iDriverId);
        }
        if ($eStatus == "Accepted" || $eStatus == "Declined") {
            $USER_SMS_TEMPLATE = ($eStatus == "Accepted") ? "BOOKING_ACCEPT_BYDRIVER_MESSAGE_SP" : "BOOKING_DECLINED_BYDRIVER_MESSAGE_SP";
            $message_layout = $generalobj->send_messages_user($USER_SMS_TEMPLATE, $Data1, "", $UserLang);
            //added by SP for sms functionality on 15-7-2019 start
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM `register_user` AS r, `country` AS c WHERE r.iUserId = '" . $bookingdetail[0]['iUserId'] . "' AND r.vCountry = c.vCountryCode");
            $PhoneCodeP = $passengerData[0]['vPhoneCode'];
            $UsersendMessage = $generalobj->sendSystemSms($UserPhoneNo, $PhoneCodeP, $message_layout);
            //added by SP for sms functionality on 15-7-2019 end
            /* $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
            
              if ($UsersendMessage == 0) {
            
              $isdCode = $SITE_ISD_CODE;
            
              $UserPhoneCode = $isdCode;
            
              $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
            
              } */
        }
        $returnArr['Action'] = "1";
        if ($eStatus == "Accepted") {
            $returnArr['message'] = "LBL_JOB_ACCEPTED";
        }
        else if ($eStatus == "Declined" && $dataType == "PENDING") {
            $returnArr['message'] = "LBL_JOB_DECLINED";
        }
        else if ($eStatus == "Declined" && $dataType != "PENDING") {
            $returnArr['message'] = "LBL_BOOKING_CANCELED";
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($iDriverId);
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################Display User Address##########################################################
if ($type == "DisplayUserAddress") {
    global $generalobj, $tconfig;
    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    $eUserType = isset($_REQUEST['eUserType']) ? clean($_REQUEST['eUserType']) : 'Passenger';
    /* Food App Param */
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '0';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '0';
    /* Food App Param */
    if ($eUserType == "Passenger") {
        $eUserType = "Rider";
    }
    if ($iCompanyId > 0) {
        /* Food App Code */
        $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
        $db_companydata = $obj->MySQLSelect($sql);
        $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
        $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
        $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = '" . $eUserType . "' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
        $db_userdata = $obj->MySQLSelect($sql);
        $db_userdata_new = array();
        $db_userdata_new = $db_userdata;
        if (count($db_userdata) > 0) {
            for ($i = 0;$i < count($db_userdata);$i++) {
                $isRemoveAddressFromList = "No";
                $passengeraddlat = $db_userdata[$i]['vLatitude'];
                $passengeraddlong = $db_userdata[$i]['vLongitude'];
                $distance = distanceByLocation($passengerLat, $passengerLon, $passengeraddlat, $passengeraddlong, "K");
                if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
                $distancewithcompany = distanceByLocation($passengerLat, $passengerLon, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
                if ($distancewithcompany > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
                if ($isRemoveAddressFromList == "Yes") {
                    unset($db_userdata_new[$i]);
                }
            }
            $db_userdata = array_values($db_userdata_new);
            if (count($db_userdata) > 0) {
                $returnArr['Action'] = "1";
                $returnArr['message'] = $db_userdata;
            }
            else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_USER_ADDRESS_FOUND";
            }
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_USER_ADDRESS_FOUND";
        }
        setDataResponse($returnArr);
        /* Food App Code */
    }
    else {
        /* Cubejek App Code */
        $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = '" . $eUserType . "' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
        $db_userdata = $obj->MySQLSelect($sql);
        if ($SERVICE_PROVIDER_FLOW == "Provider" && $iDriverId > 0) {
            $getLocaionData = $obj->MySQLSelect("SELECT eSelectWorkLocation,vLatitude,vLongitude,vWorkLocationLatitude,vWorkLocationLongitude,vWorkLocationRadius FROM register_driver WHERE iDriverId='" . $iDriverId . "'");
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
            for ($r = 0;$r < count($db_userdata);$r++) {
                $userLat = $db_userdata[$r]['vLatitude'];
                $userLang = $db_userdata[$r]['vLongitude'];
                $distance = distanceByLocation($vLatitude, $vLongitude, $userLat, $userLang, "K");
                $isRemoveAddressFromList = "No";
                if ($distance <= $vWorkLocationRadius) {
                    $isRemoveAddressFromList = "Yes";
                }
                $db_userdata[$r]['eLocationAvailable'] = $isRemoveAddressFromList;
            }
        }
        if (count($db_userdata) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $db_userdata;
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_USER_ADDRESS_FOUND";
        }
        setDataResponse($returnArr);
        /* Cubejek App Code */
    }
}
###########################Display User Address End######################################################
###########################Add/Update User Address ##########################################################
if ($type == "UpdateUserAddressDetails") {
    global $generalobj, $tconfig;
    $iUserAddressId = isset($_REQUEST['iUserAddressId']) ? $_REQUEST['iUserAddressId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : ''; // FoodApp Param
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger';
    $vServiceAddress = isset($_REQUEST["vServiceAddress"]) ? $_REQUEST["vServiceAddress"] : '';
    $vBuildingNo = isset($_REQUEST["vBuildingNo"]) ? $_REQUEST["vBuildingNo"] : '';
    $vLandmark = isset($_REQUEST["vLandmark"]) ? $_REQUEST["vLandmark"] : '';
    $vAddressType = isset($_REQUEST["vAddressType"]) ? $_REQUEST["vAddressType"] : '';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $iSelectVehicalId = isset($_REQUEST["iSelectVehicalId"]) ? $_REQUEST["iSelectVehicalId"] : '';
    $IsProceed = "Yes";
    if ($iSelectVehicalId == "" || $iSelectVehicalId == NULL) {
        $IsProceed = "Yes";
    }
    if ($iSelectVehicalId != "") {
        $pickuplocationarr = array(
            $vLatitude,
            $vLongitude
        );
        //$allowed_ans = checkRestrictedAreaNew($pickuplocationarr,"No");
        $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
        if ($allowed_ans == "Yes") {
            $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
            $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) ORDER BY iVehicleTypeId ASC";
            $vehicleTypes = $obj->MySQLSelect($sql23);
            $Vehicle_Str = "";
            if (count($vehicleTypes) > 0) {
                for ($i = 0;$i < count($vehicleTypes);$i++) {
                    $Vehicle_Str .= $vehicleTypes[$i]['iVehicleTypeId'] . ",";
                }
                $Vehicle_Str = substr($Vehicle_Str, 0, -1);
            }
            $Vehicle_Str_Arr = explode(",", $Vehicle_Str);
            if (in_array($iSelectVehicalId, $Vehicle_Str_Arr)) {
                $IsProceed = "Yes";
            }
            else {
                $IsProceed = "No";
            }
        }
        else {
            $IsProceed = "No";
        }
    }
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    }
    else {
        $UserType = "Driver";
    }
    $dAddedDate = @date("Y-m-d H:i:s");
    $action = ($iUserAddressId != '') ? 'Edit' : 'Add';
    ## Checking Distance Between Company and User Address For Food App ##
    if ($iCompanyId > 0) {
        $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
        $db_companydata = $obj->MySQLSelect($sql);
        $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
        $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
        $distance = distanceByLocation($vLatitude, $vLongitude, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
        if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
            $returnArr['Action'] = "0";
            $returnArr["message"] = "LBL_LOCATION_FAR_AWAY_TXT";
            setDataResponse($returnArr);
        }
    }
    ## Checking Distance Between Company and User Address For Food App ##
    $Data_User_Address['iUserId'] = $iUserId;
    $Data_User_Address['eUserType'] = $UserType;
    $Data_User_Address['vServiceAddress'] = $vServiceAddress;
    $Data_User_Address['vBuildingNo'] = $vBuildingNo;
    $Data_User_Address['vLandmark'] = $vLandmark;
    $Data_User_Address['vAddressType'] = $vAddressType;
    $Data_User_Address['vLatitude'] = $vLatitude;
    $Data_User_Address['vLongitude'] = $vLongitude;
    $Data_User_Address['dAddedDate'] = $dAddedDate;
    $Data_User_Address['vTimeZone'] = $vTimeZone;
    $Data_User_Address['eStatus'] = $eStatus;
    if ($action == "Add") {
        $insertid = $obj->MySQLQueryPerform("user_address", $Data_User_Address, 'insert');
        $AddressId = $insertid;
    }
    else {
        $where = " iUserAddressId = '" . $iUserAddressId . "'";
        $insertid = $obj->MySQLQueryPerform("user_address", $Data_User_Address, 'update', $where);
        $AddressId = $iUserAddressId;
    }
    if ($insertid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['AddressId'] = $insertid;
        $returnArr['message1'] = "LBL_ADDRSS_ADD_SUCCESS";
        $returnArr['IsProceed'] = $IsProceed;
        if ($eUserType == "Passenger") {
            $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
##############################Add/Update User Address End##########################################################
##############################Delete User Address #################################################################
if ($type == "DeleteUserAddressDetail") {
    global $generalobj, $tconfig;
    $iUserAddressId = isset($_REQUEST['iUserAddressId']) ? $_REQUEST['iUserAddressId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger';
    /* Food App Param */
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    /* Food App Param */
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    }
    else {
        $UserType = "Driver";
    }
    $sql = "Update user_address set eStatus = 'Deleted' WHERE `iUserAddressId`='" . $iUserAddressId . "' AND `iUserId`='" . $iUserId . "' AND eUserType = '" . $UserType . "'";
    $id = $obj->MySQLSelect($sql);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message1'] = "LBL_USER_ADDRESS_DELETED_TXT";
        if ($eUserType == "Passenger") {
            $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
            if ($passengerLat != "" && $passengerLon != "") {
                $returnArr['ToTalAddress'] = GetTotalUserAddress($iUserId, "Passenger", $passengerLat, $passengerLon, 0);
            }
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
            if ($passengerLat != "" && $passengerLon != "") {
                $returnArr['ToTalAddress'] = GetTotalUserAddress($iUserId, "Passenger", $passengerLat, $passengerLon, 0);
            }
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
##############################Delete User Address Ends#################################################################
#############################Display  Schedule Booking Details######################################################################
if ($type == "DisplayScheduleBookingDetail") {
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    //$APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    //$APP_TYPE = "UberX";
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    if ($iCabBookingId != "") {
        $sql = "SELECT * from cab_booking WHERE iCabBookingId = '" . $iCabBookingId . "'";
        $bookingData = $obj->MySQLSelect($sql);
        if ($eUserType == "Passenger") {
            $tableName = "register_driver";
            $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, CONCAT(vName," ",vLastName) as vName,vAvgRating,vImage as Imgname,vLang';
            $condfield = 'iDriverId';
            $UserId = $bookingData[0]['iDriverId'];
            $Photo_Gallery_folder_path = $tconfig['tsite_upload_images_driver_path'] . "/" . $UserId . "/";
            $Photo_Gallery_folder = $tconfig['tsite_upload_images_driver'] . "/" . $UserId . "/";
            $vCurrency = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $bookingData[0]['iUserId'], '', 'true');
        }
        else {
            $tableName = "register_user";
            $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, CONCAT(vName," ",vLastName) as vName,vAvgRating,vImgName as Imgname,vLang';
            $condfield = 'iUserId';
            $UserId = $bookingData[0]['iUserId'];
            $Photo_Gallery_folder_path = $tconfig['tsite_upload_images_passenger_path'] . "/" . $UserId . "/";
            $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger'] . "/" . $UserId . "/";
            $vCurrency = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $bookingData[0]['iDriverId'], '', 'true');
        }
        $sql = "select $fields from $tableName where $condfield = '" . $UserId . "'";
        $db_member = $obj->MySQLSelect($sql);
        $lang = $db_member[0]['vLang'];
        if ($lang == "" || $lang == NULL) {
            $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $db_member[0]['vLang'] = $lang;
        if ($vCurrency == "" || $vCurrency == NULL) {
            $vCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        }
        $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrency);
        $priceRatio = $UserCurrencyData[0]['Ratio'];
        $vSymbol = $UserCurrencyData[0]['vSymbol'];
        $db_member[0]['vSymbol'] = $vSymbol;
        $imgpath = $Photo_Gallery_folder_path . "2_" . $db_member[0]['Imgname'];
        if ($db_member[0]['Imgname'] != "" && file_exists($imgpath)) {
            $db_member[0]['Imgname'] = $Photo_Gallery_folder . "2_" . $db_member[0]['Imgname'];
        }
        else {
            $db_member[0]['Imgname'] = "";
        }
        $vehicleDetailsArr = array();
        $iVehicleTypeId = $bookingData[0]['iVehicleTypeId'];
        $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.vCategoryTitle_" . $lang . " as vCategoryTitle, vc.tCategoryDesc_" . $lang . " as tCategoryDesc, vc.ePriceType, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM " . $sql_vehicle_category_table_name . " as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vt.iVehicleTypeId='" . $iVehicleTypeId . "'";
        $Data = $obj->MySQLSelect($sql2);
        $iParentId = $Data[0]['iParentId'];
        if ($iParentId == 0) {
            $ePriceType = $Data[0]['ePriceType'];
        }
        else {
            $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
        }
        $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
        if ($Data[0]['eFareType'] == "Fixed") {
            //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fFixedFare'];
            $fAmount = $Data[0]['fFixedFare'];
        }
        else if ($Data[0]['eFareType'] == "Hourly") {
            //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fPricePerHour']."/hour";
            $fAmount = $Data[0]['fPricePerHour'];
        }
        else {
            $vDistance = $bookingData[0]['vDistance'];
            $vDuration = $bookingData[0]['vDuration'];
            $Minute_Fare = round($Data[0]['fPricePerMin'] * $vDuration, 2);
            $Distance_Fare = round($Data[0]['fPricePerKM'] * $vDistance, 2);
            $iBaseFare = round($Data[0]['iBaseFare'], 2);
            $fAmount = $iBaseFare + $Minute_Fare + $Distance_Fare;
        }
        $iPrice = $fAmount;
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);
            if (count($serviceProData) > 0) {
                $fAmount = $serviceProData[0]['fAmount'];
            }
            else {
                $fAmount = $iPrice;
            }
            $iPrice = $fAmount;
        }
        $iPrice = $iPrice * $priceRatio;
        $iPrice = round($iPrice, 2);
        $vehicleDetailsArr['fAmount'] = $vSymbol . " " . $iPrice;
        $vehicleDetailsArr['ePriceType'] = $ePriceType;
        $vehicleDetailsArr['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ALLOW_SERVICE_PROVIDER_AMOUNT;
        $returnArr['Action'] = "1";
        $returnArr['MemberDetails'] = $db_member;
        $returnArr['VehicleDetails'] = $vehicleDetailsArr;
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
#############################Display  Schedule Booking Details Ends#################################################################
#############################Check Restriction For Pickup and DropOff Location For Delivery#########################################
if ($type == "Checkpickupdropoffrestriction") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $DestLatitude = isset($_REQUEST["DestLatitude"]) ? $_REQUEST["DestLatitude"] : '';
    $DestLongitude = isset($_REQUEST["DestLongitude"]) ? $_REQUEST["DestLongitude"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $CheckType = isset($_REQUEST["CheckType"]) ? $_REQUEST["CheckType"] : 'Pickup'; // Pickup Or Drop
    if ($CheckType == "" || $CheckType == NULL) {
        $CheckType = "Pickup";
    }
    $pickuplocationarr = array(
        $PickUpLatitude,
        $PickUpLongitude
    );
    $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
    $dropofflocationarr = array(
        $DestLatitude,
        $DestLongitude
    );
    $allowed_ans_drop = checkAllowedAreaNew($dropofflocationarr, "Yes");
    $returnArr['Action'] = "1";
    if ($allowed_ans == "No" && $allowed_ans_drop == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICK_DROP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    if ($allowed_ans == "Yes" && $allowed_ans_drop == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    if ($allowed_ans == "No" && $allowed_ans_drop == "Yes") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_PICKUP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }
    setDataResponse($returnArr);
}
#############################Check Restriction For Pickup and DropOff Location For Delivery#########################################
#############################Check Restriction For Pickup and DropOff Location For UberX#########################################
if ($type == "Checkuseraddressrestriction") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '';
    $iSelectVehicalId = isset($_REQUEST["iSelectVehicalId"]) ? $_REQUEST["iSelectVehicalId"] : '';
    $sql = "SELECT vLatitude,vLongitude FROM user_address WHERE iUserAddressId='" . $iUserAddressId . "'";
    $address_data = $obj->MySQLSelect($sql);
    if (count($address_data) > 0) {
        $StartLatitude = $address_data[0]['vLatitude'];
        $EndLongitude = $address_data[0]['vLongitude'];
        $pickuplocationarr = array(
            $StartLatitude,
            $EndLongitude
        );
        //$allowed_ans = checkRestrictedAreaNew($pickuplocationarr,"No");
        $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
        if ($allowed_ans == "Yes") {
            $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
            $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) ORDER BY iVehicleTypeId ASC";
            $vehicleTypes = $obj->MySQLSelect($sql23);
            $Vehicle_Str = "";
            if (count($vehicleTypes) > 0) {
                for ($i = 0;$i < count($vehicleTypes);$i++) {
                    $Vehicle_Str .= $vehicleTypes[$i]['iVehicleTypeId'] . ",";
                }
                $Vehicle_Str = substr($Vehicle_Str, 0, -1);
            }
            $Vehicle_Str_Arr = explode(",", $Vehicle_Str);
            if (in_array($iSelectVehicalId, $Vehicle_Str_Arr)) {
                $returnArr['Action'] = "1";
            }
            else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_SERVICES_AVAIL_FOR_JOB_LOC";
            }
        }
        else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_JOB_LOCATION_NOT_ALLOWED";
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_JOB_LOCATION_NOT_ALLOWED";
    }
    setDataResponse($returnArr);
}
#############################Check Restriction For Pickup and DropOff Location For UberX#########################################
#################################### Add/Update User Favourite Address ########################################################
if ($type == "UpdateUserFavouriteAddress") {
    global $generalobj, $tconfig;
    $iUserFavAddressId = isset($_REQUEST['iUserFavAddressId']) ? $_REQUEST['iUserFavAddressId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger'; // Passenger , Driver
    $vAddress = isset($_REQUEST["vAddress"]) ? $_REQUEST["vAddress"] : '';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Home'; // Home,Work
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $dAddedDate = @date("Y-m-d H:i:s");
    $action = ($iUserFavAddressId != '') ? 'Edit' : 'Add';
    $Data_User_Address['iUserId'] = $iUserId;
    $Data_User_Address['eUserType'] = $eUserType;
    $Data_User_Address['vAddress'] = $vAddress;
    $Data_User_Address['vLatitude'] = $vLatitude;
    $Data_User_Address['vLongitude'] = $vLongitude;
    $Data_User_Address['eType'] = $eType;
    $Data_User_Address['dAddedDate'] = $dAddedDate;
    $Data_User_Address['vTimeZone'] = $vTimeZone;
    $Data_User_Address['eStatus'] = $eStatus;
    if ($action == "Add") {
        $insertid = $obj->MySQLQueryPerform("user_fave_address", $Data_User_Address, 'insert');
        $AddressId = $insertid;
    }
    else {
        $where = " iUserFavAddressId = '" . $iUserFavAddressId . "'";
        $insertid = $obj->MySQLQueryPerform("user_fave_address", $Data_User_Address, 'update', $where);
        $AddressId = $iUserAddressId;
    }
    if ($insertid > 0) {
        $returnArr['Action'] = "1";
        $returnArr['AddressId'] = $insertid;
        $returnArr['message1'] = "LBL_ADDRSS_ADD_SUCCESS";
        if ($eUserType == "Passenger") {
            $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
#################################### Add/Update User Favourite Address ########################################################
##############################Delete User Favourite Address #################################################################
if ($type == "DeleteUserFavouriteAddress") {
    global $generalobj, $tconfig;
    $iUserFavAddressId = isset($_REQUEST['iUserFavAddressId']) ? $_REQUEST['iUserFavAddressId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger';
    $sql = "DELETE FROM user_fave_address WHERE `iUserFavAddressId`='" . $iUserFavAddressId . "'";
    $id = $obj->MySQLSelect($sql);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message1'] = "LBL_USER_ADDRESS_DELETED_TXT";
        if ($eUserType == "Passenger") {
            $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
        }
        else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
        }
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
##############################Delete User Favourite Address Ends###############################################################
################################################Get Member Wallet Balance########################################################
################################################UpdateBooking Date  Of Ride, Delivery ######################################
if ($type == "UpdateBookingDateRideDelivery") {
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';
    $eConfirmByUser = isset($_REQUEST['eConfirmByUser']) ? $_REQUEST['eConfirmByUser'] : 'No';
    if ($eConfirmByUser == "" || $eConfirmByUser == NULL) {
        $eConfirmByUser = "No";
    }
    if (empty($iCabBookingId)) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
    $sql = "SELECT * from  cab_booking  WHERE iCabBookingId ='" . $iCabBookingId . "'";
    $bookingdetail = $obj->MySQLSelect($sql);
    if ($bookingdetail[0]['eType'] == "UberX" && $SERVICE_PROVIDER_FLOW == "Provider") {
        $sdate = explode(" ", $scheduleDate);
        $shour = explode("-", $sdate[1]);
        $shour1 = $shour[0];
        $shour2 = $shour[1];
        if ($shour1 == "12" && $shour2 == "01") {
            $shour1 = 00;
        }
        $scheduleDate = $sdate[0] . " " . $shour1 . ":00:00";
    }
    $Booking_Date_Time = $scheduleDate;
    $systemTimeZone = date_default_timezone_get();
    // echo "hererrrrr:::".$systemTimeZone;exit;
    $scheduleDate = converToTz($scheduleDate, $systemTimeZone, $vTimeZone);
    $fPickUpPrice = 1;
    $fNightPrice = 1;
    $iVehicleTypeId = $bookingdetail[0]['iVehicleTypeId'];
    $iUserId = $bookingdetail[0]['iUserId'];
    $vSourceAddresss = $bookingdetail[0]['vSourceAddresss'];
    //added for rental
    $iRentalPackageId = $bookingdetail[0]['iRentalPackageId'];
    $vDistance = $bookingdetail[0]['vDistance'];
    $vDuration = $bookingdetail[0]['vDuration'];
    $iDriverId = $bookingdetail[0]['iDriverId'];
    $vCouponCode = $bookingdetail[0]['vCouponCode'];
    $isDestinationAdded = "Yes";
    $eFlatTrip = $bookingdetail[0]['eFlatTrip'];
    $fFlatTripPrice = $bookingdetail[0]['fFlatTripPrice'];
    $sourceLocationArr = array(
        $bookingdetail[0]['vSourceLatitude'],
        $bookingdetail[0]['vSourceLongitude']
    );
    $destinationLocationArr = array(
        $bookingdetail[0]['vDestLatitude'],
        $bookingdetail[0]['vDestLongitude']
    );
    $currentdate = date("Y-m-d H:i:s");
    $dBooking_date = $bookingdetail[0]['dBooking_date'];
    $datediff = strtotime($dBooking_date) - strtotime($currentdate);
    if ($datediff < 1800) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_RE_SCHEDULE_BOOK_RESTRICTION";
        setDataResponse($returnArr);
    }
    $eFareType = "";
    if ($bookingdetail[0]['eType'] == "UberX" && !empty($bookingdetail[0]['tVehicleTypeData']) && $SERVICE_PROVIDER_FLOW == "Provider") {
        $tVehicleTypeDataArr = (array)json_decode($bookingdetail[0]['tVehicleTypeData']);
        if (count($tVehicleTypeDataArr) > 0) {
            $eFareType = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $tVehicleTypeDataArr[0]->iVehicleTypeId, '', 'true');
        }
    }
    //added for rental
    if ($bookingdetail[0]['eType'] != "UberX" || ($bookingdetail[0]['eType'] == "UberX" && $bookingdetail[0]['eFareType'] == "Regular")) {
        $data_surgePrice = checkSurgePrice($iVehicleTypeId, $Booking_Date_Time, $iRentalPackageId);
        $surgePrice = 1;
        if ($data_surgePrice['Action'] == "0") {
            if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
                $surgePrice = $fPickUpPrice;
            }
            else {
                $fNightPrice = $data_surgePrice['SurgePriceValue'];
                $surgePrice = $fNightPrice;
            }
            if ($eConfirmByUser == "No") {
                $Fare_data = calculateFareEstimateAll($vDuration, $vDistance, $iVehicleTypeId, $iUserId, 1, "", "", $vCouponCode, $surgePrice, 0, 0, 0, "DisplySingleVehicleFare", $GeneralUserType, 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr);
                $HistoryFareDetailsArr = array();
                foreach ($Fare_data as $inner) {
                    $HistoryFareDetailsArr = array_merge($HistoryFareDetailsArr, $inner);
                }
                $total_fare = end($HistoryFareDetailsArr);
                $data_surgePrice["total_fare"] = $total_fare;
                setDataResponse($data_surgePrice);
            }
        }
    }
    $where = " iCabBookingId = '" . $iCabBookingId . "'";
    $Data['fPickUpPrice'] = $fPickUpPrice;
    $Data['fNightPrice'] = $fNightPrice;
    $Data['dBooking_date'] = date('Y-m-d H:i:s', strtotime($scheduleDate));
    if ($bookingdetail[0]['eType'] == "UberX" && !empty($bookingdetail[0]['tVehicleTypeData']) && $SERVICE_PROVIDER_FLOW == "Provider" && ($bookingdetail[0]['eStatus'] == "Declined" || $bookingdetail[0]['eStatus'] == "Cancel")) {
        $Data['eStatus'] = "Pending";
        $Data['vCancelReason'] = "";
        $Data['iCancelReasonId'] = "";
        $Data['vFailReason'] = "";
        $Data['eCancelBy'] = "";
        $Data['iCancelByUserId'] = "";
    }
    $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'update', $where);
    if ($id > 0) {
        $returnArr["Action"] = "1";
        //$returnArr['message']= $APP_TYPE == "Ride" ?"LBL_RIDE_BOOKED":"LBL_DELIVERY_BOOKED";
        $returnArr["message"] = "LBL_INFO_UPDATED_TXT";
        $sql = "SELECT concat(vName,' ',vLastName) as senderName,vEmail,vPhone,vPhoneCode,vLang from  register_user  WHERE iUserId ='" . $iUserId . "'";
        $userdetail = $obj->MySQLSelect($sql);
        $Data1['vRider'] = $userdetail[0]['senderName'];
        $Data1['vRiderMail'] = $userdetail[0]['vEmail'];
        $Data1['vSourceAddresss'] = $vSourceAddresss;
        $Data1['dBookingdate'] = $generalobj->DateTime($Booking_Date_Time, 7);
        //$Data1['dBookingdate']=date('Y-m-d H:i', strtotime($Booking_Date_Time));
        $Data1['vBookingNo'] = $bookingdetail[0]['vBookingNo'];
        if ($bookingdetail[0]['eType'] == "UberX") {
            $sql = "SELECT concat(vName,' ',vLastName) as drivername,vEmail,vPhone,vcode,iDriverVehicleId,vLang from  register_driver  WHERE iDriverId ='" . $iDriverId . "'";
            $driverdetail = $obj->MySQLSelect($sql);
            $DriverPhoneNo = $driverdetail[0]['vPhone'];
            $Booking_Date = @date('d-m-Y', strtotime($Booking_Date_Time));
            $Booking_Time = @date('H:i:s', strtotime($Booking_Date_Time));
            $maildata1['PASSENGER_NAME'] = $Data1['vRider'];
            $maildata1['BOOKING_DATE'] = $Booking_Date;
            $maildata1['BOOKING_TIME'] = $Booking_Time;
            $maildata1['BOOKING_NUMBER'] = $Data1['vBookingNo'];
            $DRIVER_SMS_TEMPLATE = ($bookingdetail[0]['eType'] == "UberX") ? "DRIVER_SEND_MESSAGE_SP" : "DRIVER_SEND_MESSAGE";
            $message_layout = $generalobj->send_messages_user($DRIVER_SMS_TEMPLATE, $maildata1, "", $DriverLang);
            $DriversendMessage = $generalobj->sendUserSMS($DriverPhoneNo, $DriverPhoneCode, $message_layout, "");
            if ($DriversendMessage == 0) {
                //$isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
                $isdCode = $SITE_ISD_CODE;
                $DriverPhoneCode = $isdCode;
                $UsersendMessage = $generalobj->sendUserSMS($DriverPhoneNo, $DriverPhoneCode, $message_layout, "");
            }
        }
        $sendMailToAdmin = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_RESCEDULE_ADMIN_APP", $Data1);
        $sendMailToUser = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_RESCEDULE_APP", $Data1);
    }
    else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
################################################UpdateBooking Date  Of Ride, Delivery
################################################Charge Passenger's Outstanding Amount From Credit Card
if ($type == "ChargePassengerOutstandingAmount") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $riderData = $obj->MySQLSelect("SELECT ru.vStripeCusId,ru.vStripeToken,ru.vCurrencyPassenger,ru.vSenangToken,ru.vPaymayaCustId,ru.vFlutterWaveToken,ru.vPaymayaToken,ru.vXenditToken, ru.vCurrencyPassenger, ru.vLang, cu.vSymbol, cu.Ratio, co.eUnit FROM register_user as ru, currency as cu, country as co WHERE ru.iUserId='" . $iMemberId . "' AND cu.vName = ru.vCurrencyPassenger AND co.vCountryCode = ru.vCountry");
    /* Added By PM On 09-12-2019 For Flutterwave Code End */
    // $sqld = "SELECT vStripeCusId,vStripeToken,vCurrencyPassenger,vBrainTreeToken,vPaymayaCustId,vPaymayaToken,vXenditToken FROM register_user WHERE iUserId = '" . $iMemberId . "'";
    // $riderData = $obj->MySQLSelect($sqld);
    $vStripeCusId = $riderData[0]['vStripeCusId'];
    $vStripeToken = $riderData[0]['vStripeToken'];
    $vSenangToken = $riderData[0]['vSenangToken'];
    $vPaymayaCustId = $riderData[0]['vPaymayaCustId'];
    $vPaymayaToken = $riderData[0]['vPaymayaToken'];
    $vXenditToken = $riderData[0]['vXenditToken'];
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $vFlutterWaveToken = $riderData[0]['vFlutterWaveToken'];
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    //$fTripsOutStandingAmount = GetPassengerOutstandingAmount($iMemberId, $iOrganizationId, $ePaymentBy);
    $fTripsOutStandingAmount = GetPassengerOutstandingAmount($iMemberId);
    $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
    $currencyCode = $DefaultCurrencyData[0]['vName'];
    $currencyratio = $DefaultCurrencyData[0]['Ratio'];
    $price_new = $fTripsOutStandingAmount * $currencyratio;
    $price_new = round($price_new * 100, 2);
    $tDescription = "Amount charge for trip oustanding balance";
    $t_rand_nun = rand(1111111, 9999999);
    //echo $fTripsOutStandingAmount;die;
    if ($fTripsOutStandingAmount == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
        $returnArr['message1'] = "LBL_OUTSTANDING_AMOUT_ALREADY_PAID_TXT";
        setDataResponse($returnArr);
    }
    ######## payment flow 2 ###############
    /*     * ******* Create a charge from user's wallet when System payment flow is method-2/method-3 ********* */
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $languageLabelsArr = getLanguageLabelsArr($riderData[0]['vLang'], "1", $iServiceId);
        $user_available_balance_wallet = $generalobj->get_user_available_balance($iMemberId, "Rider", true);
        //Added By HJ On 06-06-2019 For Check User Wallet Amount Start
        if (is_array($user_available_balance_wallet)) {
            $user_available_balance_wallet = $user_available_balance_wallet['CurrentBalance'];
        }
        //echo $user_available_balance_wallet * $riderData[0]['Ratio'];die;
        //Added By HJ On 06-06-2019 For Check User Wallet Amount End
        if ($user_available_balance_wallet < $fTripsOutStandingAmount) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LOW_WALLET_AMOUNT";
            $walletNeedAmt = $fTripsOutStandingAmount - $user_available_balance_wallet;
            $content_msg_low_balance = $languageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_WALLET_AMT'];
            $content_msg_low_balance = str_replace("#####", $riderData[0]['vSymbol'] . ' ' . $generalobj->setTwoDecimalPoint($walletNeedAmt * $riderData[0]['Ratio']) , $content_msg_low_balance);
            $content_msg_low_balance = str_replace("####", $riderData[0]['vSymbol'] . ' ' . $generalobj->setTwoDecimalPoint($user_available_balance_wallet * $riderData[0]['Ratio']) , $content_msg_low_balance);
            $content_msg_low_balance = str_replace("##", "\n", $content_msg_low_balance);
            $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
            if ($SYSTEM_PAYMENT_FLOW == 'Method-3') {
                $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "Yes";
            }
            else {
                $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "No";
            }
            setDataResponse($returnArr);
        }
        else {
            $user_wallet_debit_amount = $fTripsOutStandingAmount;
            if ($user_wallet_debit_amount > 0) {
                $data_wallet['iUserId'] = $iMemberId;
                $data_wallet['eUserType'] = "Rider";
                $data_wallet['iBalance'] = $user_wallet_debit_amount;
                $data_wallet['eType'] = "Debit";
                $data_wallet['dDate'] = date("Y-m-d H:i:s");
                $data_wallet['iTripId'] = 0;
                $data_wallet['eFor'] = "Booking";
                $data_wallet['ePaymentStatus'] = "Unsettelled";
                $data_wallet['tDescription'] = "#LBL_DEBITED_FOR_OUTSTANDING#";
                $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);
            }
            $updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $iMemberId;
            $obj->sql_query($updateQuery);
            $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes' WHERE iUserId = '" . $iMemberId . "' AND iOrganizationId = '" . $iOrganizationId . "' AND ePaymentBy = '" . $ePaymentBy . "'";
            $obj->sql_query($updateQury);
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
            $returnArr['message1'] = "LBL_OUTSTANDING_AMOUT_PAID_TXT";
            setDataResponse($returnArr);
        }
    }
    /*     * ******* Create a charge from user's wallet when System payment flow is method-2/method-3 ********* */
    ######## payment flow 2 ###############
    
    if ($vSenangToken == "" && $APP_PAYMENT_METHOD == "Senangpay") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    
    $Charge_Array = array(
        "iFare" => $fTripsOutStandingAmount,
        "price_new" => $price_new,
        "currency" => $currencyCode,
        "vStripeCusId" => $vStripeCusId,
        "description" => $tDescription,
        "iTripId" => 0,
        "eCancelChargeFailed" => "No",
        "vBrainTreeToken" => $vBrainTreeToken,
        "vRideNo" => $t_rand_nun,
        "iMemberId" => $iMemberId,
        "UserType" => "Passenger"
    );
    $ChargeidArr = ChargeCustomer($Charge_Array, "ChargePassengerOutstandingAmount"); // function for charge customer
    $ChargeidArrId = $ChargeidArr['id'];
    $status = $ChargeidArr['status'];
    if ($status == "success") {
        $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
        $data_payments['eEvent'] = "OutStanding";
        $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
        $updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $iMemberId;
        $obj->sql_query($updateQuery);
        // $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes' WHERE iUserId = " . $iMemberId;
        $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes' WHERE iUserId = '" . $iMemberId . "' AND iOrganizationId = '" . $iOrganizationId . "' AND ePaymentBy = '" . $ePaymentBy . "'";
        $obj->sql_query($updateQury);
        /* Added By PM On 25-01-2020 For wallet credit to driver Start */
        if (checkAutoCreditDriverModule()) {
            $Data['iUserId'] = $iUserId;
            $Data['iTripId'] = $iTripId;
            AutoCreditWalletDriver($Data, "ChargePassengerOutstandingAmount", 0);
        }
        /* Added By PM On 25-01-2020 For wallet credit to driver End */
        $returnArr['Action'] = "1";
        $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
        $returnArr['message1'] = "LBL_OUTSTANDING_AMOUT_PAID_TXT";
        setDataResponse($returnArr);
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
        setDataResponse($returnArr);
    }
}
################################################Charge Passenger's Outstanding Amount From Credit Card #################
################################################Get Passenger's Outstanding Amount############################################
if ($type == "getOutstandingAmount") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger
    $fTripsOutStandingAmount = GetPassengerOutstandingAmount($iMemberId);
    $fTripsOutStandingAmount_ARR = getPriceUserCurrency($iMemberId, "Passenger", $fTripsOutStandingAmount);
    $returnArr['Action'] = "1";
    $returnArr['message']['fOutStandingAmount'] = $fTripsOutStandingAmount_ARR['fPrice'];
    $returnArr['message']['fOutStandingAmountWithSymbol'] = $fTripsOutStandingAmount_ARR['fPricewithsymbol'];
    setDataResponse($returnArr);
}
################################################Get Passenger's Outstanding Amount############################################
####################################Service Category##################################
if ($type == "getServiceCategories") {
    global $generalobj;
    $parentId = isset($_REQUEST['parentId']) ? clean($_REQUEST['parentId']) : 0;
    $userId = isset($_REQUEST['userId']) ? clean($_REQUEST['userId']) : '';
    $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    if ($userId != "") {
        $lang = $vGeneralLang;
        if ($lang == '') {
            $sql1 = "SELECT vLang FROM `register_user` WHERE iUserId='$userId'";
            $row = $obj->MySQLSelect($sql1);
            $lang = $row[0]['vLang'];
            if ($lang == "") {
                $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
        }
        $vehicle_category_main = get_value($sql_vehicle_category_table_name, 'vCategory_' . $lang, 'iVehicleCategoryId', $parentId, '', 'true');
        $ssql = $ssql1 = $ssql2 = $ssql3 = '';
        //$ufxEnable = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
        $ufxEnable = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query
        if ($APP_TYPE == "UberX") {
            $ssql = " AND eCatType='ServiceProvider'";
        }
        /* $serviceArray = $serviceIdArray = array();
        
          $serviceArray = json_decode(serviceCategories,true);
        
          $serviceIdArray = array_column($serviceArray, 'iServiceId');
        
          $service_id_arr = implode(',',$serviceIdArray);
        
        
        
          if(DELIVERALL=='Yes') {
        
          $ssql3 .= " OR iServiceId IN ($service_id_arr)";
        
          }
        
        
        
          if($generalobj->checkXThemOn() == 'Yes') {
        
          //$ssql .= " AND (iServiceId IN (1) OR eCatType IN ('Ride', 'MotoRide', 'Fly', 'Donation') OR (eFor = 'DeliveryCategory' AND eCatType = 'MoreDelivery') $ssql2 $ssql3)  $ssql1";
        
          if($generalobj->checkCubeJekXThemOn() == 'Yes') {
        
          $ssql4 = " OR eCatType = 'MoreDelivery'";
        
          } else {
        
          $ssql4 = " OR (eFor = 'DeliveryCategory' AND eCatType = 'MoreDelivery')";
        
          }
        
          $ssql .= " AND (iServiceId IN (1) OR eCatType IN ('Ride', 'MotoRide', 'Fly', 'Donation')  $ssql4 $ssql2 $ssql3)  $ssql1";
        
          } */
        if ($generalobj->checkCubexThemOn() == 'Yes') {
            $ssql1 = $ssql2 = $ssql3 = '';
            if ($ufxEnable != 'Yes') {
                $ssql1 .= " AND eCatType!='ServiceProvider'";
            }
            else {
                $ssql2 .= " OR eCatType='ServiceProvider'";
            }
            $ssql .= " AND (iServiceId IN ($enablesevicescategory) OR eCatType IN ('Ride', 'MotoRide', 'Fly', 'Donation') OR (eFor = 'DeliveryCategory' AND eCatType = 'MoreDelivery')  $ssql2 )  $ssql1";
        }
        if ($generalobj->checkCubeJekXThemOn() == 'Yes') {
            if ($ufxEnable != 'Yes') {
                $ssql .= " AND eCatType!='ServiceProvider'";
            }
        }
        if (!checkFlyStationsModule()) {
            $ssql .= " AND eCatType!='Fly'";
        }
        if (checkDonationModule()) {
            $sql2 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage, iDisplayOrder,vCategory_" . $lang . " as vCategory, eCatType, eSubCatType, tBannerButtonText,iServiceId, eDeliveryType FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND iParentId='$parentId' " . $ssql . " ORDER BY iDisplayOrder,iVehicleCategoryId ASC";
        }
        else {
            $sql2 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage, iDisplayOrder,vCategory_" . $lang . " as vCategory, eCatType, eSubCatType, tBannerButtonText,iServiceId, eDeliveryType FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND eCatType!='Donation' AND iParentId='$parentId' " . $ssql . " ORDER BY iDisplayOrder,iVehicleCategoryId ASC";
        }
        $Data = $obj->MySQLSelect($sql2);
        //$Data3 = $obj->MySQLSelect("SELECT iVehicleTypeId,iVehicleCategoryId FROM vehicle_type WHERE eStatus='Active' ORDER BY iDisplayOrder ASC");
        $categoryArr = $Datacategory = array();
        //for ($vc = 0; $vc < count($Data3); $vc++) {
        //$categoryArr[$Data3[$vc]['iVehicleCategoryId']][] = $Data3[$vc];
        //}
        //echo "<pre>";print_r($categoryArr);die;
        $deliverAll_serviceArr = array();
        if(strtoupper(DELIVERALL) == "YES"){
            $scsql = "select iServiceId,eShowTerms from service_categories";
            $scsqlData = $obj->MySQLSelect($scsql);
            foreach ($scsqlData as $scValue) {
                $deliverAll_serviceArr[$scValue['iServiceId']] = $scValue['eShowTerms'];
            }
        }

        if ($parentId == 0) {
            if (count($Data) > 0) {
                $k = 0;
                for ($i = 0;$i < count($Data);$i++) {
                    $BannerButtonText = "tBannerButtonText_" . $lang;
                    $tBannerButtonTextArr = json_decode($Data[$i]['tBannerButtonText'], true);
                    $tBannerButtonText = $tBannerButtonTextArr[$BannerButtonText];
                    if ($Data[$i]['eCatType'] == "ServiceProvider" || $Data[$i]['eCatType'] == "MoreDelivery") {
                        $sql3 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage, vCategory_" . $lang . " as vCategory, eCatType, eSubCatType, tBannerButtonText, iServiceId, eDeliveryType FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND iParentId='" . $Data[$i]['iVehicleCategoryId'] . "' ORDER BY iDisplayOrder ASC";
                        $Data2 = $obj->MySQLSelect($sql3);
                        if (count($Data2) > 0) {
                            for ($j = 0;$j < count($Data2);$j++) {
                                if ($Data2[$j]['eCatType'] == "ServiceProvider") {
                                    $sql4 = "SELECT iVehicleTypeId FROM vehicle_type WHERE eStatus='Active' AND iVehicleCategoryId='" . $Data2[$j]['iVehicleCategoryId'] . "'";
                                    $Data3 = $obj->MySQLSelect($sql4);
                                    //$Data3 = array();
                                    if (isset($categoryArr[$Data[$j]['iVehicleCategoryId']])) {
                                        //$Data3 = $categoryArr[$Data[$j]['iVehicleCategoryId']];
                                        
                                    }
                                    if (count($Data3) > 0) {
                                        $Datacategory[$k]['eCatType'] = $Data[$i]['eCatType'];
                                        $Datacategory[$k]['eSubCatType'] = $Data[$i]['eSubCatType'];
                                        $Datacategory[$k]['eDeliveryType'] = $Data[$i]['eDeliveryType'];
                                        $Datacategory[$k]['iVehicleCategoryId'] = $Data[$i]['iVehicleCategoryId'];
                                        $Datacategory[$k]['vCategory'] = $Data[$i]['vCategory'];
                                        $Datacategory[$k]['vCategoryBanner'] = $Data[$i]['vCategory'];
                                        $Datacategory[$k]['vLogo'] = $Data[$i]['vLogo'];
                                        $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/android/' . $Data[$i]['vLogo'];
                                        $Datacategory[$k]['eShowType'] = $Data[$i]['eShowType'];
                                        $Datacategory[$k]['iServiceId'] = $Data[$i]['iServiceId'];
                                        $Datacategory[$k]['tBannerButtonText'] = $tBannerButtonText;
                                        $Datacategory[$k]['vBannerImage'] = ($Data[$i]['vBannerImage'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/' . $Data[$i]['vBannerImage'] : "";
                                        $Datacategory[$k]['eShowTerms'] = "No";
                                        if(strtoupper(DELIVERALL) == "YES" && $Data[$i]['iServiceId'] > 0){
                                            $Datacategory[$k]['eShowTerms'] = $deliverAll_serviceArr[$Data[$i]['iServiceId']];
                                        }
                                        $k++;
                                    }
                                }
                                else {
                                    $Datacategory[$k]['eCatType'] = $Data[$i]['eCatType'];
                                    $Datacategory[$k]['eSubCatType'] = $Data[$i]['eSubCatType'];
                                    $Datacategory[$k]['eDeliveryType'] = $Data[$i]['eDeliveryType'];
                                    $Datacategory[$k]['iVehicleCategoryId'] = $Data[$i]['iVehicleCategoryId'];
                                    $Datacategory[$k]['vCategory'] = $Data[$i]['vCategory'];
                                    $Datacategory[$k]['vCategoryBanner'] = $Data[$i]['vCategory'];
                                    $Datacategory[$k]['vLogo'] = $Data[$i]['vLogo'];
                                    $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/android/' . $Data[$i]['vLogo'];
                                    $Datacategory[$k]['eShowType'] = $Data[$i]['eShowType'];
                                    $Datacategory[$k]['iServiceId'] = $Data[$i]['iServiceId'];
                                    $Datacategory[$k]['tBannerButtonText'] = $tBannerButtonText;
                                    $Datacategory[$k]['vBannerImage'] = ($Data[$i]['vBannerImage'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/' . $Data[$i]['vBannerImage'] : "";
                                    $Datacategory[$k]['eShowTerms'] = "No";
                                    if(strtoupper(DELIVERALL) == "YES" && $Data[$i]['iServiceId'] > 0){
                                        $Datacategory[$k]['eShowTerms'] = $deliverAll_serviceArr[$Data[$i]['iServiceId']];
                                    }
                                    $k++;
                                }
                            }
                        }
                    }
                    else {
                        $Datacategory[$k]['eCatType'] = $Data[$i]['eCatType'];
                        $Datacategory[$k]['eSubCatType'] = $Data[$i]['eSubCatType'];
                        $Datacategory[$k]['eDeliveryType'] = $Data[$i]['eDeliveryType'];
                        $Datacategory[$k]['iVehicleCategoryId'] = $Data[$i]['iVehicleCategoryId'];
                        $Datacategory[$k]['vCategory'] = $Data[$i]['vCategory'];
                        $Datacategory[$k]['vCategoryBanner'] = $Data[$i]['vCategory'];
                        $Datacategory[$k]['vLogo'] = $Data[$i]['vLogo'];
                        $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/android/' . $Data[$i]['vLogo'];
                        $Datacategory[$k]['eShowType'] = $Data[$i]['eShowType'];
                        $Datacategory[$k]['iServiceId'] = $Data[$i]['iServiceId'];
                        $Datacategory[$k]['tBannerButtonText'] = $tBannerButtonText;
                        $Datacategory[$k]['vBannerImage'] = ($Data[$i]['vBannerImage'] != "") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/' . $Data[$i]['vBannerImage'] : "";
                        $Datacategory[$k]['eShowTerms'] = "No";
                        if(strtoupper(DELIVERALL) == "YES" && $Data[$i]['iServiceId'] > 0){
                            $Datacategory[$k]['eShowTerms'] = $deliverAll_serviceArr[$Data[$i]['iServiceId']];
                        }
                        $k++;
                    }
                }
            }
        }
        else {
            if (count($Data) > 0) {
                $k = 0;
                for ($j = 0;$j < count($Data);$j++) {
                    $BannerButtonText = "tBannerButtonText_" . $lang;
                    $tBannerButtonTextArr = json_decode($Data[$j]['tBannerButtonText'], true);
                    $tBannerButtonText = $tBannerButtonTextArr[$BannerButtonText];
                    $sql4 = "SELECT iVehicleTypeId FROM vehicle_type WHERE eStatus='Active' AND iVehicleCategoryId='" . $Data[$j]['iVehicleCategoryId'] . "' ORDER BY iDisplayOrder ASC";
                    $Data3 = $obj->MySQLSelect($sql4);
                    //$Data3 = array();
                    if (isset($categoryArr[$Data[$j]['iVehicleCategoryId']])) {
                        //$Data3 = $categoryArr[$Data[$j]['iVehicleCategoryId']];
                        
                    }
                    if (count($Data3) > 0) {
                        $Datacategory[$k]['eCatType'] = $Data[$j]['eCatType'];
                        $Datacategory[$k]['eSubCatType'] = $Data[$j]['eSubCatType'];
                        $Datacategory[$k]['eDeliveryType'] = $Data[$j]['eDeliveryType'];
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
        if ($vehicle_category_main != '') {
            $returnArr['vParentCategoryName'] = $vehicle_category_main;
        }
        else {
            $returnArr['vParentCategoryName'] = '';
        }
        //$returnArr['message'] = array_reverse($DatanewArr);
        for ($i = 0;$i < count($DatanewArr);$i++) {
            $vLogo_image_tmp = $DatanewArr[$i]['vLogo_image'];
            $vBannerImage_tmp = $DatanewArr[$i]['vBannerImage'];
            $vLogo_image_tmp_orig_name_arr = explode("/", $DatanewArr[$i]['vLogo_image']);
            $vBannerImage_tmp_orig_name_arr = explode("/", $DatanewArr[$i]['vBannerImage']);
            $isFileExist = false;
            if (!empty($vBannerImage_tmp_orig_name_arr) && count($vBannerImage_tmp_orig_name_arr) > 0) {
                $vBannerImage_tmp_orig_name = $vBannerImage_tmp_orig_name_arr[count($vBannerImage_tmp_orig_name_arr) - 1];
                $isFileExist = file_exists($tconfig['tsite_upload_images_vehicle_category_path'] . '/' . $DatanewArr[$i]['iVehicleCategoryId'] . '/' . $vBannerImage_tmp_orig_name);
            }
            $isFileExist_1 = false;
            if (!empty($vLogo_image_tmp_orig_name_arr) && count($vLogo_image_tmp_orig_name_arr) > 0) {
                $vLogo_image_tmp_orig_name = $vLogo_image_tmp_orig_name_arr[count($vLogo_image_tmp_orig_name_arr) - 1];
                $isFileExist_1 = file_exists($tconfig['tsite_upload_images_vehicle_category_path'] . '/' . $DatanewArr[$i]['iVehicleCategoryId'] . '/android/' . $vLogo_image_tmp_orig_name);
            }
            if (empty($vBannerImage_tmp) || !$isFileExist) {
                $DatanewArr[$i]['vBannerImage'] = $tconfig["tsite_url"] . "webimages/icons/DefaultImg/15529086332815.png";
            }
            if (empty($vLogo_image_tmp) || !$isFileExist_1) {
                $DatanewArr[$i]['vLogo_image'] = $tconfig["tsite_url"] . "webimages/icons/DefaultImg/service_categories.png";
            }
        }
        $returnArr['message'] = $DatanewArr;
        $sql_banners = "SELECT vImage FROM banners WHERE vCode= '" . $lang . "' AND vImage != '' AND eStatus = 'Active' AND (iServiceId = '0' OR iServiceId = '') ORDER BY iDisplayOrder ASC";
        //$sql_banners = "SELECT vImage FROM banners WHERE vImage != '' AND eStatus = 'Active' AND (iServiceId = '0' OR iServiceId = '') ORDER BY iDisplayOrder ASC";
        $Data_banners = $obj->MySQLSelect($sql_banners);
        $dataOfBanners = array();
        $count = 0;
        for ($i = 0;$i < count($Data_banners);$i++) {
            if (isset($Data_banners[$i]['vImage']) && $Data_banners[$i]['vImage'] != "") {
                $dataOfBanners[$count]['vImage'] = $tconfig["tsite_url"] . 'assets/img/images/' . $Data_banners[$i]['vImage'];
                $count++;
            }
        }
        $returnArr['BANNER_DATA'] = $dataOfBanners;
    }
    else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    $returnArr['MORE_ICON'] = $tconfig["tsite_url"] . "webimages/icons/DefaultImg/ic_more.png";
    setDataResponse($returnArr);
}
###########################################################
if ($type == "getServiceCategoryDetails") {
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? clean($_REQUEST['iVehicleCategoryId']) : 0;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    //$iVehicleCategoryId = 178;
    $returnArr = array();
    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    if ($iMemberId != "") {
        $sql1 = "SELECT vLang FROM `register_user` WHERE iUserId='$iMemberId'";
        $row = $obj->MySQLSelect($sql1);
        $lang = $row[0]['vLang'];
        if ($lang == "") {
            $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
        
        $sql_inactive = "SELECT eStatus FROM " . $sql_vehicle_category_table_name . " WHERE iVehicleCategoryId='" . $iVehicleCategoryId . "'";
        $Data_inactive = $obj->MySQLSelect($sql_inactive);
        if($Data_inactive[0]['eStatus']=='Inactive') {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_SERVICE_INACTIVE_MSG";
                setDataResponse($returnArr);
        }
        
        $sql2 = "SELECT iVehicleCategoryId,iParentId,eDetailPageView, iServiceId,vCategory_" . $lang . " as vCategory, tCategoryDesc_" . $lang . " as tCategoryDesc, tBannerButtonText, eCatType FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND iVehicleCategoryId='" . $iVehicleCategoryId . "'";
        $Data = $obj->MySQLSelect($sql2);
        //Added BY HJ On 21-12-2019 For Get Parent Category Data As Per Optimization Isseue Start
        $getCatData = $obj->MySQLSelect("SELECT iParentId,iVehicleCategoryId, vLogo, eShowType,vBannerImage, iServiceId, vCategory_" . $lang . " as vCategory, tCategoryDesc_" . $lang . " as tCategoryDesc, eCatType, tBannerButtonText, eDeliveryType FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' ORDER BY iDisplayOrder,iVehicleCategoryId ASC");
        $catDataArr = array();
        for ($f = 0;$f < count($getCatData);$f++) {
            $catDataArr[$getCatData[$f]['iParentId']][] = $getCatData[$f];
        }
        //Added BY HJ On 21-12-2019 For Get Parent Category Data As Per Optimization Isseue End
        //echo "<pre>";print_r($catDataArr);die;
        if (count($Data) > 0) {
            $returnArr['iServiceId'] = $Data[0]['iServiceId'];
            $returnArr['vCategory'] = $Data[0]['vCategory'];
            $returnArr['tCategoryDesc'] = $Data[0]['tCategoryDesc'];
            $returnArr['eDetailPageView'] = $Data[0]['eDetailPageView'];
            $returnArr['eCatType'] = $Data[0]['eCatType'];
            $Datacategory = array();
            //$sql3 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage, iServiceId, vCategory_" . $lang . " as vCategory, tCategoryDesc_" . $lang . " as tCategoryDesc, eCatType, tBannerButtonText, eDeliveryType FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND iParentId='" . $Data[0]['iVehicleCategoryId'] . "' ORDER BY iDisplayOrder,iVehicleCategoryId ASC";
            //echo $sql3;die;
            //$Data2 = $obj->MySQLSelect($sql3);
            //echo "<pre>";print_r($Data2);die;
            $Data2 = array();
            if (isset($catDataArr[$Data[0]['iVehicleCategoryId']])) {
                $Data2 = $catDataArr[$Data[0]['iVehicleCategoryId']];
            }
            if (count($Data2) > 0) {
                $allSubCategories = array();
                $allSubCategoriesCount = 0;
                $j = 0;
                for ($i = 0;$i < count($Data2);$i++) {
                    $BannerButtonText = "tBannerButtonText_" . $lang;
                    $tBannerButtonTextArr = json_decode($Data2[$i]['tBannerButtonText'], true);
                    $tBannerButtonText = $tBannerButtonTextArr[$BannerButtonText];
                    $Datasubcategory = array();
                    if ($returnArr['eDetailPageView'] == "Icon") {
                        //$sql4 = "SELECT iVehicleCategoryId, vLogo, eShowType,vBannerImage,iServiceId, vCategory_" . $lang . " as vCategory, tCategoryDesc_" . $lang . " as tCategoryDesc, eCatType, tBannerButtonText,eDeliveryType FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND iParentId='" . $Data2[$i]['iVehicleCategoryId'] . "' ORDER BY iDisplayOrder,iVehicleCategoryId ASC";
                        //$Data3 = $obj->MySQLSelect($sql4);
                        $Data3 = array();
                        if (isset($catDataArr[$Data2[$i]['iVehicleCategoryId']])) {
                            $Data3 = $catDataArr[$Data2[$i]['iVehicleCategoryId']];
                        }
                        if (count($Data3) > 0) {
                            $k = 0;
                            for ($l = 0;$l < count($Data3);$l++) {
                                $BannerButtonText_sub = "tBannerButtonText_" . $lang;
                                $tBannerButtonTextArr_sub = json_decode($Data3[$l]['tBannerButtonText'], true);
                                $tBannerButtonText_sub = $tBannerButtonTextArr_sub[$BannerButtonText_sub];
                                $Datasubcategory[$k]['eCatType'] = $Data3[$l]['eCatType'];
                                $Datasubcategory[$k]['iServiceId'] = $Data3[$l]['iServiceId'];
                                $Datasubcategory[$k]['vCategory'] = $Data3[$l]['vCategory'];
                                $Datasubcategory[$k]['tCategoryDesc'] = $Data3[$l]['tCategoryDesc'];
                                $Datasubcategory[$k]['eDeliveryType'] = $Data3[$l]['eDeliveryType'];
                                $Datasubcategory[$k]['vImage'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data3[$l]['iVehicleCategoryId'] . '/android/' . $Data3[$l]['vLogo'];
                                $Datasubcategory[$k]['eShowTerms'] = "No";
                            
                                if($Data3[$l]['iServiceId'] > 0)
                                {
                                    $sql = "SELECT eShowTerms FROM service_categories WHERE iServiceId = ".$Data3[$l]['iServiceId'];
                                    $sqlData = $obj->MySQLSelect($sql);
                                    $Datasubcategory[$k]['eShowTerms'] = $sqlData[0]['eShowTerms'];
                                }
                                $k++;
                            }
                            $Datacategory[$j]['SubCategory'] = $Datasubcategory;
                            //added by SP this 3 put here bc when it put general then when subcategory not get at that time main cat also not shown, so it will be put in else part also on 15-10-2019
                            $Datacategory[$j]['vCategory'] = $Data2[$i]['vCategory'];
                            $Datacategory[$j]['tCategoryDesc'] = $Data2[$i]['tCategoryDesc'];
                            $Datacategory[$j]['eDeliveryType'] = $Data2[$i]['eDeliveryType'];
                        }
                    }
                    else {
                        $Datacategory[$j]['vCategory'] = $Data2[$i]['vCategory'];
                        $Datacategory[$j]['tCategoryDesc'] = $Data2[$i]['tCategoryDesc'];
                        $Datacategory[$j]['eDeliveryType'] = $Data2[$i]['eDeliveryType'];
                        $Datacategory[$j]['eCatType'] = $Data2[$i]['eCatType'];
                        $Datacategory[$j]['iServiceId'] = $Data2[$i]['iServiceId'];
                        $Datacategory[$j]['vImage'] = ($Data2[$i]['vBannerImage'] != "" && $Data2[$i]['eShowType'] == "Banner") ? $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data2[$i]['iVehicleCategoryId'] . '/' . $Data2[$i]['vBannerImage'] : "";
                        $Datacategory[$j]['tBannerButtonText'] = $tBannerButtonText;
                        $Datacategory[$j]['eShowTerms'] = "No";
                            
                        if($Data2[$i]['iServiceId'] > 0)
                        {
                            $sql = "SELECT eShowTerms FROM service_categories WHERE iServiceId = ".$Data2[$i]['iServiceId'];
                            $sqlData = $obj->MySQLSelect($sql);
                            $Datacategory[$j]['eShowTerms'] = $sqlData[0]['eShowTerms'];
                        }
                        $allSubCategories[$allSubCategoriesCount] = $Datacategory[$j];
                        $allSubCategoriesCount++;
                    }
                    $j++;
                }
                $returnArr['Action'] = "1";
                if ($returnArr['eDetailPageView'] == "Icon") {
                    $returnArr['message'] = $Datacategory;
                }
                else {
                    $returnArr['message'] = $allSubCategories;
                }
            }
        }
    }
    //echo "<pre>";print_r($returnArr);die;
    setDataResponse($returnArr);
}
//Added By HJ On 27-08-2019 For Get Trip Details Start
if ($type == "getMemberTripDetails") {
    $iTripId = isset($_REQUEST['iTripId']) ? clean($_REQUEST['iTripId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    $vGeneralLang = isset($_REQUEST['vGeneralLang']) ? clean($_REQUEST['vGeneralLang']) : 'EN';
    $tripData = $returnArr = array();
    //$iTripId = 301;
    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_NOT_FOUND";
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    $labelsArr = getLanguageLabelsArr($vGeneralLang, "1", $iServiceId);
    if (!empty($iTripId)) {
        if ($UserType == "Passenger") {
            $tableName = " register_driver as rd";
            $fieldName = "rd.iDriverId";
            $fieldName_trip = "tr.iDriverId";
            $memberId = $tripData[0]['iDriverId'];
            $fields = " CONCAT(rd.vName, ' ', rd.vLastName) as vName, rd.vImage as vImage, rd.vLang as vLang, rd.iDriverId as iMemberId,rd.iDriverId, rd.vAvgRating as vAvgRating";
        }
        else {
            $tableName = " register_user as ru";
            $fieldName_trip = "tr.iUserId";
            $fieldName = "ru.iUserId";
            $fields = " CONCAT(ru.vName, ' ', ru.vLastName) as vName, vImgName as vImage, ru.vLang as vLang, ru.iUserId as iMemberId,ru.iUserId, ru.vAvgRating as vAvgRating";
        }
        $tripData = $obj->MySQLSelect("SELECT tr.tTripRequestDate, tr.eType, tr.vRideNo, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vGeneralLang . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData = '', '0', tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as vServiceName, " . $fields . " FROM trips as tr, " . $tableName . " WHERE tr.iTripId = '" . $iTripId . "' AND " . $fieldName . " = " . $fieldName_trip . "");
        //echo "<pre>";print_r($tripData);die;
        if (!empty($tripData) && count($tripData) > 0) {
            $vImageFolder = "Passenger";
            if ($UserType == "Passenger") {
                $vImageFolder = "Driver";
            }
            if (empty($tripData[0]['vServiceName'])) {
                if ($tripData[0]['eType'] == "Ride") {
                    $tripData[0]['vServiceName'] = $labelsArr['LBL_RIDE'];
                }
                else if ($tripData[0]['eType'] == "Delivery" || $tripData[0]['eType'] == "Deliver" || $tripData[0]['eType'] == "Multi-Delivery") {
                    $tripData[0]['vServiceName'] = $labelsArr['LBL_DELIVERY'];
                }
                else {
                    $tripData[0]['vServiceName'] = $labelsArr['LBL_OTHER'];
                }
            }
            $tripData[0]['vImage'] = $tconfig["tsite_url"] . "/webimages/upload/" . $vImageFolder . "/" . $tripData[0]['iMemberId'] . "/" . $tripData[0]['vImage'];
            $returnArr['Action'] = "1";
            $returnArr['message'] = $tripData[0];
        }
    }
    setDataResponse($returnArr);
}
//Added By HJ On 27-08-2019 For Get Trip Details End
if ($type == "fetchAPIDetails") {
    fetchAPIDetails();
}
$obj->MySQLClose();
?>