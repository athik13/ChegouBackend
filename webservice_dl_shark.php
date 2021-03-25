<?php


date_default_timezone_set('Asia/Kuala_Lumpur');

ini_set('default_socket_timeout', 10);
ini_set('memory_limit', '-1');
//ini_set('display_errors', TRUE);
@session_start();
$_SESSION['sess_hosttype'] = 'ufxall';
$inwebservice = "1";
error_reporting(0);
//ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL);
// include_once('include_taxi_webservices.php');
include_once ('include_config.php');
include_once (TPATH_CLASS . 'configuration.php');
$generalConfigPaymentArr = $generalobj->getGeneralVarAll_Payment_Array();
require_once ('assets/libraries/stripe/config.php');
require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
require_once ('assets/libraries/pubnub/autoloader.php');
require_once ('assets/libraries/SocketCluster/autoload.php');
require_once ('assets/libraries/class.ExifCleaning.php');
include_once (TPATH_CLASS . 'Imagecrop.class.php');
include_once (TPATH_CLASS . 'twilio/Services/Twilio.php');
include_once ('generalFunctions_dl_shark.php');
include_once ('send_invoice_receipt.php');
include_once ('app_common_functions.php');
$intervalmins = INTERVAL_SECONDS; // Added By HJ On 13-03-2020 Which is Defined In configuration_variables.php 
$PHOTO_UPLOAD_SERVICE_ENABLE = "Yes";
$host_arr = array();
$host_arr = explode(".", $_SERVER["HTTP_HOST"]);
$host_system = $host_arr[0];

if ($_REQUEST['UBERX_PARENT_CAT_ID'] != "") {
    $parent_ufx_catid = $_REQUEST['UBERX_PARENT_CAT_ID'];
} else {
    $parent_ufx_catid = "0";
}

$uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";
if ($_REQUEST['APP_TYPE'] != "") {
    $APP_TYPE = $_REQUEST['APP_TYPE'];
}
if ($APP_PAYMENT_METHOD == "Braintree") {
    require_once ('assets/libraries/braintree/lib/Braintree.php');
    $gateway = new Braintree_Gateway(['environment' => $BRAINTREE_ENVIRONMENT, 'merchantId' => $BRAINTREE_MERCHANT_ID, 'publicKey' => $BRAINTREE_PUBLIC_KEY, 'privateKey' => $BRAINTREE_PRIVATE_KEY]);
}
/* creating objects */
$thumb = new thumbnail;
/* Get variables */
$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
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

if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
    include_once('include/include_webservice_dl_enterprisefeatures.php');
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
if ($type != "generalConfigData" && $type != "signIn" && $type != "isUserExist" && $type != "signup" && $type != "LoginWithFB" && $type != "sendVerificationSMS" && $type != "countryList" && $type != "changelanguagelabel" && $type != "requestResetPassword" && $type != "UpdateLanguageLabelsValue" && $type != "staticPage" && $type != "sendContactQuery" && $type != "loadAvailableRestaurants" && $type != "getCuisineList" && $type != "loadSearchRestaurants" && $type != "GetRestaurantDetails" && $type != "signup_company" && $type != "GetItemOptionAddonDetails" && $type != "getBanners" && $type != "getServiceCategories" && $type != "CheckOutOrderEstimateDetails" && $type != "getFAQ" && $type != "getUserLanguagesAsPerServiceType" && $type != "uploadcompanydocument" && $type != "getAdvertisementBanners" && $type != "insertBannereImpressionCount" && $type != "getNewsNotification" && $type != "CheckPrescriptionRequired" && isAllowFetchAPIDetails() == false) {
    $tSessionId = isset($_REQUEST['tSessionId']) ? trim($_REQUEST['tSessionId']) : '';
    $GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? trim($_REQUEST['GeneralMemberId']) : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    if ($tSessionId == "" || $GeneralMemberId == "" || $GeneralUserType == "") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        setDataResponse($returnArr);
    } else {
        if ($GeneralUserType == "Company") {
            $userData = get_value("company", "iCompanyId as iMemberId,tSessionId", "iCompanyId", $GeneralMemberId);
        } else {
            $userData = get_value($GeneralUserType == "Driver" ? "register_driver" : "register_user", $GeneralUserType == "Driver" ? "iDriverId as iMemberId,tSessionId" : "iUserId as iMemberId,tSessionId", $GeneralUserType == "Driver" ? "iDriverId" : "iUserId", $GeneralMemberId);
        }
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
$iServiceId = isset($_REQUEST["iServiceId"]) ? $_REQUEST["iServiceId"] : '';
$vUserDeviceCountry = isset($_REQUEST["vUserDeviceCountry"]) ? $_REQUEST["vUserDeviceCountry"] : '';
$vCurrentTime = isset($_REQUEST["vCurrentTime"]) ? $_REQUEST["vCurrentTime"] : '';

if ($appVersion != "") {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    if ($UserType == "Passenger") {
        $newAppVersion = $Platform == "IOS" ? $PASSENGER_IOS_APP_VERSION : $PASSENGER_ANDROID_APP_VERSION;
    } else if ($UserType == "Company") {
        $newAppVersion = $Platform == "IOS" ? $COMPANY_IOS_APP_VERSION : $COMPANY_ANDROID_APP_VERSION;
    } else if ($UserType == "Hotel") {
        $newAppVersion = $Platform == "IOS" ? $KIOSK_IOS_APP_VERSION : $KIOSK_ANDROID_APP_VERSION;
    } else {
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
    global $generalobj, $obj, $demo_site_msg, $PHOTO_UPLOAD_SERVICE_ENABLE, $parent_ufx_catid, $generalSystemConfigDataArr, $tconfig, $vTimeZone, $vUserDeviceCountry, $_REQUEST, $intervalmins, $generalConfigPaymentArr, $ENABLE_RIDER_ADVERTISEMENT_BANNER, $ADVERTISEMENT_TYPE, $RIDER_REQUEST_ACCEPT_TIME;

    $where = " iUserId = '" . $passengerID . "'";
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
    $obj->MySQLQueryPerform("register_user", $data_version, 'update', $where);

    $updateQuery = "UPDATE trip_status_messages SET eReceived='Yes' WHERE iUserId='" . $passengerID . "' AND eToUserType='Passenger'";
    $obj->sql_query($updateQuery);

    $sql = "SELECT * FROM `register_user` WHERE iUserId='$passengerID'";
    $row = $obj->MySQLSelect($sql);

    if ($LiveTripId != "") {
        $sql_livetrip = "SELECT iTripId,iActive,vTripPaymentMode,iVehicleTypeId,fPickUpPrice,fNightPrice,vCouponCode,eType FROM `trips` WHERE iTripId='" . $LiveTripId . "'";
        $userlivetripdetails = $obj->MySQLSelect($sql_livetrip);
        $row[0]['iTripId'] = $userlivetripdetails[0]['iTripId'];
        $row[0]['vTripStatus'] = $userlivetripdetails[0]['iActive'];
        $row[0]['vTripPaymentMode'] = $userlivetripdetails[0]['vTripPaymentMode'];
        $row[0]['iSelectedCarType'] = $userlivetripdetails[0]['iVehicleTypeId'];
        $row[0]['fPickUpPrice'] = $userlivetripdetails[0]['fPickUpPrice'];
        $row[0]['fNightPrice'] = $userlivetripdetails[0]['fNightPrice'];
        $row[0]['vCouponCode'] = $userlivetripdetails[0]['vCouponCode'];
        $row[0]['eType'] = $userlivetripdetails[0]['eType'];

        // echo "<pre>";print_r($userlivetripdetails);exit;
    }

    if (count($row) > 0) {

        ### Update Tripid - 0 and TripStatus - None For UberX Trip ###
        $currenttrip = $row[0]['iTripId'];
        if ($currenttrip > 0) {
            $sql = "SELECT eType,eSystem FROM `trips` WHERE iTripId = '" . $currenttrip . "'";
            $db_currenttrip = $obj->MySQLSelect($sql);
            if (count($db_currenttrip) > 0) {
                $currenttriptype = $db_currenttrip[0]['eType'];
                $currenttripsystem = $db_currenttrip[0]['eSystem'];
                if (($currenttriptype == "UberX" || $currenttriptype == "Multi-Delivery") && $LiveTripId == "") {
                    $update_sql = "UPDATE register_user set iTripId = '0',vTripStatus = 'NONE' WHERE iUserId ='" . $passengerID . "'";
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

        $defaultCurrencyDataArr = get_value('currency', 'vName,vSymbol', 'eDefault', 'Yes');

        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        $row[0]['vFlutterwaveCurrency'] = $defaultCurrencyDataArr[0]['vName'];
        $vFlutterwavevSymbol = $defaultCurrencyDataArr[0]['vSymbol'];
        /* Added By PM On 09-12-2019 For Flutterwave Code End */

        ### Update Tripid - 0 and TripStatus - None For UberX Trip ###
        $page_link = $tconfig['tsite_url'] . "sign-up_rider.php?UserType=Rider&vRefCode=" . $row[0]['vRefCode'];
        $link = get_tiny_url($page_link);
        //$activation_text = '<a href="'.$link.'" target="_blank"> '.$link.' </a>';
        $activation_text = "<a href='" . $link . "' target='_blank'> '" . $link . "' </a>";
        $vLanguage = $row[0]['vLang'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_SHARE_CONTENT_PASSENGER' AND vCode = '" . $vLanguage . "'";
        $db_label = $obj->MySQLSelect($sql);
        $LBL_SHARE_CONTENT_PASSENGER = $db_label[0]['vValue'];
        $row[0]['INVITE_SHARE_CONTENT'] = $LBL_SHARE_CONTENT_PASSENGER . " " . $link;
		
		foreach($generalSystemConfigDataArr as $key => $value){
            if(is_null($generalSystemConfigDataArr[$key]) || empty($generalSystemConfigDataArr[$key])){
                $generalSystemConfigDataArr[$key] = "";
            }
        }
		
        $row[0] = array_merge($row[0], $generalSystemConfigDataArr); // Added By HJ On 18-03-2020 For Optimized Function
        $row[0]['PACKAGE_TYPE'] = strtoupper(PACKAGE_TYPE);

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
        $RIDER_EMAIL_VERIFICATION = $row[0]["RIDER_EMAIL_VERIFICATION"];
        $RIDER_PHONE_VERIFICATION = $row[0]["RIDER_PHONE_VERIFICATION"];
        $REFERRAL_AMOUNT = $row[0]["REFERRAL_AMOUNT"];
        $REFERRAL_AMOUNT_ARR = getPriceUserCurrency($passengerID, "Passenger", $REFERRAL_AMOUNT);
        $REFERRAL_AMOUNT_USER = $REFERRAL_AMOUNT_ARR['fPricewithsymbol'];
        $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_PREFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        $row[0]['INVITE_DESCRIPTION_CONTENT'] = $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT . " " . $REFERRAL_AMOUNT_USER . " " . $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT;
        if ($RIDER_EMAIL_VERIFICATION == 'No') {
            $row[0]['eEmailVerified'] = "Yes";
        }

        if ($RIDER_PHONE_VERIFICATION == 'No') {
            $row[0]['ePhoneVerified'] = "Yes";
        }
        $lang_usr = $row[0]['vLang'];
        $sql = "select vBody_$lang_usr as Message from send_message_templates where vEmail_Code = 'VERIFICATION_CODE_MESSAGE'";
        $data_SMS = $obj->MySQLSelect($sql);
        $row[0]['SMS_BODY'] = $data_SMS[0]['Message'];
        ## Display Braintree Charge Message ##
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_BRAINTREE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'";
        $db_label_braintree = $obj->MySQLSelect($sql);
        $LBL_BRAINTREE_CHARGE_MSG_TXT = $db_label_braintree[0]['vValue'];
        $BRAINTREE_CHARGE_AMOUNT = $row[0]['BRAINTREE_CHARGE_AMOUNT'];
        $BRAINTREE_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($passengerID, "Passenger", $BRAINTREE_CHARGE_AMOUNT);
        $BRAINTREE_CHARGE_AMOUNT_USER = $BRAINTREE_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        //$msg = str_replace('##AMOUNT##', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $msg = str_replace('####', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $row[0]['BRAINTREE_CHARGE_MESSAGE'] = $msg;
        ## Display Braintree Charge Message ##
        ## Display Adyen Charge Message ##
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_ADYEN_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'";
        $db_label_adyen = $obj->MySQLSelect($sql);
        $LBL_ADYEN_CHARGE_MSG_TXT = $db_label_adyen[0]['vValue'];
        $ADEYN_CHARGE_AMOUNT = $row[0]['ADYEN_CHARGE_AMOUNT'];
        $ADEYN_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($passengerID, "Passenger", $ADEYN_CHARGE_AMOUNT);
        $ADEYN_CHARGE_AMOUNT_USER = $ADEYN_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        $msg = str_replace('####', $ADEYN_CHARGE_AMOUNT_USER, $LBL_ADYEN_CHARGE_MSG_TXT);
        $row[0]['ADEYN_CHARGE_MESSAGE'] = $msg;
        ## Display Adyen Charge Message ##

        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        ## Display Flutterwave Charge Message ##
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_FLUTTERWAVE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'";
        $db_label_adyen = $obj->MySQLSelect($sql);
        $LBL_FLUTTERWAVE_CHARGE_MSG_TXT = $db_label_adyen[0]['vValue'];

        $amountDataArr = $generalobj->getSupportedCurrencyAmt($row[0]['FLUTTERWAVE_CHARGE_AMOUNT'], $row[0]['vFlutterwaveCurrency']);
        $row[0]['vFlutterwaveCurrency'] = $amountDataArr['CURRENCY_CODE'];

        $FLUTTERWAVE_CHARGE_AMOUNT = $amountDataArr['AMOUNT'];

        $FLUTTERWAVE_CHARGE_AMOUNT_USER_ARR = $FLUTTERWAVE_CHARGE_AMOUNT;
        $FLUTTERWAVE_CHARGE_AMOUNT_USER = $vFlutterwavevSymbol . $FLUTTERWAVE_CHARGE_AMOUNT;
        $msg = str_replace('####', $FLUTTERWAVE_CHARGE_AMOUNT_USER, $LBL_FLUTTERWAVE_CHARGE_MSG_TXT);
        $row[0]['FLUTTERWAVE_CHARGE_AMOUNT'] = $FLUTTERWAVE_CHARGE_AMOUNT;
        $row[0]['FLUTTERWAVE_CHARGE_MESSAGE'] = $msg;
        /* Added By PM On 09-12-2019 For Flutterwave Code Start */

        ## Check and update Device Session ID ##
        if ($row[0]['tDeviceSessionId'] == "") {
            $random = substr(md5(rand()), 0, 7);
            $Update_Device_Session['tDeviceSessionId'] = session_id() . time() . $random;
            $Update_Device_Session_id = $obj->MySQLQueryPerform("register_user", $Update_Device_Session, 'update', $where);
            $row[0]['tDeviceSessionId'] = $Update_Device_Session['tDeviceSessionId'];
        }

        ## Check and update Device Session ID ##
        ## Check and update Session ID ##
        if ($row[0]['tSessionId'] == "") {
            $Update_Session['tSessionId'] = session_id() . time();
            $Update_Session_id = $obj->MySQLQueryPerform("register_user", $Update_Session, 'update', $where);
            $row[0]['tSessionId'] = $Update_Session['tSessionId'];
        }

        ## Check and update Session ID ##
        if ($row[0]['vImgName'] != "" && $row[0]['vImgName'] != "NONE") {
            $row[0]['vImgName'] = "3_" . $row[0]['vImgName'];
        }

        // $row[0]['Passenger_Password_decrypt']= $generalobj->decrypt($row[0]['vPassword']);
        $row[0]['Passenger_Password_decrypt'] = "";
        if ($row[0]['eStatus'] != "Active") {
            $returnArr['Action'] = "0";
            if ($row[0]['eStatus'] != "Deleted") {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            } else {
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            }

            setDataResponse($returnArr);
        }

        $TripStatus = $row[0]['vTripStatus'];
        $TripID = $row[0]['iTripId'];

        $eType = "";
        if ($TripID != "" && $TripID != NULL && $TripID != 0) {
            $eType = get_value('trips', 'eType', 'iTripId', $TripID, '', 'true');
        }

        if ($row[0]['APP_TYPE'] == "Ride-Delivery-UberX" || $row[0]['APP_TYPE'] == "Ride-Delivery") { // Changed By HJ On 02-04-2019 As Per Discuss With KS
            $ssql = " AND (eType = 'Ride' or eType = 'Deliver')";
        } else if ($row[0]['APP_TYPE'] == "Delivery") { // Added By HJ On 02-04-2019 As Per Discuss With KS
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
            // $TripID = $row[0]['iTripId'];
            if ($LiveTripId != "") {
                $TripID = $LiveTripId;
            } else {
                $TripID = $row[0]['iTripId'];
            }
            $row_result_trips = getTripPriceDetails($TripID, $passengerID, "Passenger");
            //echo "<pre>";print_r($row_result_trips);die;
            $row[0]['TripDetails'] = $row_result_trips;
            $row[0]['DriverDetails'] = $row_result_trips['DriverDetails'];
            $row_result_trips['DriverCarDetails']['make_title'] = $row_result_trips['DriverCarDetails']['vMake'];
            $row_result_trips['DriverCarDetails']['model_title'] = $row_result_trips['DriverCarDetails']['vTitle'];
            $row[0]['DriverCarDetails'] = $row_result_trips['DriverCarDetails'];
            $sql = "SELECT vPaymentUserStatus FROM `payments` WHERE iTripId='$TripID'";
            $row_result_payments = $obj->MySQLSelect($sql);
            if (count($row_result_payments) > 0) {
                if ($row_result_payments[0]['vPaymentUserStatus'] != 'approved') {
                    $row[0]['PaymentStatus_From_Passenger'] = "Not Approved";
                } else {
                    $row[0]['PaymentStatus_From_Passenger'] = "Approved";
                }
            } else {
                $row[0]['PaymentStatus_From_Passenger'] = "No Entry";
            }

            $sql = "SELECT iTripId,eUserType FROM `ratings_user_driver` WHERE iTripId='$TripID'";
            $row_result_ratings_trip = $obj->MySQLSelect($sql);

            if (count($row_result_ratings_trip) > 0) {

                $count_row_rating = 0;
                $ContentWritten = "false";
                while (count($row_result_ratings_trip) > $count_row_rating) {
                    $UserType = $row_result_ratings_trip[$count_row_rating]['eUserType'];
                    if ($UserType == "Passenger") {
                        $ContentWritten = "true";
                        $row[0]['Ratings_From_Passenger'] = "Done";
                    } else if ($ContentWritten == "false") {
                        $row[0]['Ratings_From_Passenger'] = "Not Done";
                    }
                    $count_row_rating++;
                }
            } else {
                $row[0]['Ratings_From_Passenger'] = "No Entry";
            }
        }

        $sql = "SELECT ord.iOrderId,ord.iCompanyId,ord.iDriverId,ord.vOrderNo,ord.eTakeaway FROM `orders` as ord WHERE ord.iUserId='" . $passengerID . "' AND ord.iStatusCode = '6' AND (select count(iRatingId) from ratings_user_driver as rud where rud.iOrderId=ord.iOrderId AND rud.eFromUserType = 'Passenger' ) = 0  ORDER BY ord.iOrderId DESC LIMIT 0,1";
        $row_order = $obj->MySQLSelect($sql);
        if (count($row_order) > 0) {
            $LastOrderId = $row_order[0]['iOrderId'];
            $LastOrderNo = $row_order[0]['vOrderNo'];
            $LastOrderCompanyId = $row_order[0]['iCompanyId'];
            $LastOrderDriverId = $row_order[0]['iDriverId'];
            $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM register_driver WHERE iDriverId = '" . $LastOrderDriverId . "'";
            $result_driver = $obj->MySQLSelect($sql);
            $sqlc = "SELECT vCompany AS CompanyName FROM company WHERE iCompanyId = '" . $LastOrderCompanyId . "'";
            $result_company = $obj->MySQLSelect($sqlc);
            $sql = "SELECT count(iRatingId) as TotalRating FROM `ratings_user_driver` WHERE iOrderId='" . $LastOrderId . "' AND eFromUserType = 'Passenger'";
            $row_result_ratings = $obj->MySQLSelect($sql);
            $TotalRating = $row_result_ratings[0]['TotalRating'];
            if ($TotalRating > 0) {
                $row[0]['Ratings_From_DeliverAll'] = "Done";
            } else {
                $row[0]['Ratings_From_DeliverAll'] = "Not Done";
            }

            $row[0]['LastOrderId'] = $LastOrderId;
            $row[0]['LastOrderNo'] = $LastOrderNo;
            $row[0]['LastOrderCompanyId'] = $LastOrderCompanyId;
            $row[0]['LastOrderCompanyName'] = $result_company[0]['CompanyName'];
            $row[0]['LastOrderTakeaway'] = $row_order[0]['eTakeaway'];
            $row[0]['LastOrderDriverId'] = $LastOrderDriverId;
            $row[0]['LastOrderDriverName'] = $result_driver[0]['driverName'];
        } else {
            $row[0]['Ratings_From_DeliverAll'] = "";
        }

        $sql = "SELECT count(iUserAddressId) as ToTalAddress from user_address WHERE iUserId = '" . $passengerID . "' AND eUserType = 'Rider' AND eStatus = 'Active'";
        $result_Address = $obj->MySQLSelect($sql);
        $row[0]['ToTalAddress'] = $result_Address[0]['ToTalAddress'];

        // $row[0]['PayPalConfiguration']=$generalobj->getConfigurations("configurations","PAYMENT_ENABLED");
        $row[0]['DefaultCurrencySign'] = $row[0]["DEFAULT_CURRENCY_SIGN"];
        $row[0]['DefaultCurrencyCode'] = $row[0]["DEFAULT_CURRENCY_CODE"];
        $row[0]['FETCH_TRIP_STATUS_TIME_INTERVAL'] = fetchtripstatustimeinterval();
        $row[0]['ENABLE_TOLL_COST'] = $row[0]['APP_TYPE'] != "UberX" ? $row[0]['ENABLE_TOLL_COST'] : "No";
        /* Check Toll Enable For Passenger's Country */
        $usercountrycode = $row[0]['vCountry'];
        if ($usercountrycode != "") {
            $sqlc = "SELECT eEnableToll from country WHERE vCountryCode = '" . $usercountrycode . "'";
            $user_country_toll = $obj->MySQLSelect($sqlc);
            $eEnableToll = $user_country_toll[0]['eEnableToll'];
            if ($eEnableToll != "") {
                $row[0]['ENABLE_TOLL_COST'] = ($eEnableToll == "Yes" && $row[0]['ENABLE_TOLL_COST'] == "Yes") ? "Yes" : "No";
            }
        }
        /* Check Toll Enable For Passenger's Country */
        if ($row[0]['APP_TYPE'] == "Ride" || $row[0]['APP_TYPE'] == "Ride-Delivery" || $row[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = $row[0]['FEMALE_RIDE_REQ_ENABLE'];
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = $row[0]['HANDICAP_ACCESSIBILITY_OPTION'];
        } else {
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = "No";
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";

            // $row[0]['ENABLE_TOLL_COST'] = "No";
        }

        if ($row[0]['APP_TYPE'] == "Ride" || $row[0]['APP_TYPE'] == "Ride-Delivery" || $row[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $row[0]['ENABLE_HAIL_RIDES'] = $row[0]['ENABLE_HAIL_RIDES'];
        } else {
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
        }

        if ($row[0]['APP_PAYMENT_MODE'] == "Card") {
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
        }

        // $user_available_balance = $generalobj->get_user_available_balance($passengerID,"Rider");
        // $row[0]['user_available_balance'] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$row[0]['vCurrencyPassenger']));
        $user_available_balance = $generalobj->get_user_available_balance_app_display($passengerID, "Rider");
        $row[0]['user_available_balance'] = strval($user_available_balance);
        $user_available_balance_arr = explode(" ", $user_available_balance);
        $row[0]['user_available_balance_amount'] = strval($user_available_balance_arr[1]);

        $user_available_balance_value = $generalobj->get_user_available_balance_app_display($passengerID, "Rider", 'Yes');
        $row[0]['user_available_balance_value'] = strval($user_available_balance_value);
        if ($user_available_balance_value <= 0 || $user_available_balance_value <= 0.00) {
            $row[0]['eWalletBalanceAvailable'] = 'No';
        } else {
            $row[0]['eWalletBalanceAvailable'] = 'Yes';
        }
        // $row[0]['PHOTO_UPLOAD_SERVICE_ENABLE']=$PHOTO_UPLOAD_SERVICE_ENABLE;
        $row[0]['PHOTO_UPLOAD_SERVICE_ENABLE'] = $row[0]['APP_TYPE'] == "UberX" ? $PHOTO_UPLOAD_SERVICE_ENABLE : "No";
        $row[0]['ENABLE_TIP_MODULE'] = $row[0]['ENABLE_TIP_MODULE'];
        $host_arr = array();
        $host_arr = explode(".", $_SERVER["HTTP_HOST"]);
        $host_system = $host_arr[0];
        if ($_REQUEST['UBERX_PARENT_CAT_ID'] != "") {
            $parent_ufx_catid = $_REQUEST['UBERX_PARENT_CAT_ID'];
        } else {
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
        if ($row[0]['APP_TYPE'] == "UberX") {
            $row[0]['APP_DESTINATION_MODE'] = "None";
            $row[0]['ENABLE_TOLL_COST'] = "No";
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = "No";
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
            $row[0]['ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL'] = "5";
            $row[0]['ENABLE_CORPORATE_PROFILE'] = "No";
        } else {

            // $row[0]['APP_DESTINATION_MODE'] = "Strict";
        }
        // $row[0]['ENABLE_DELIVERY_MODULE']=$generalobj->getConfigurations("configurations","ENABLE_DELIVERY_MODULE");
        $row[0]['ENABLE_DELIVERY_MODULE'] = SITE_TYPE == "Demo" ? $row[0]['eDeliverModule'] : $row[0]['ENABLE_DELIVERY_MODULE'];
        $row[0]['PayPalConfiguration'] = $row[0]['ENABLE_DELIVERY_MODULE'] == "Yes" ? "Yes" : $row[0]['PAYMENT_ENABLED'];
        // if($row[0]['ENABLE_DELIVERY_MODULE'] == "Yes"){
        // $row[0]['PayPalConfiguration'] = "Yes";
        // }
        $row[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        $row[0]['SITE_TYPE'] = SITE_TYPE;
        $row[0]['RIIDE_LATER'] = RIIDE_LATER;
        $row[0]['PROMO_CODE'] = PROMO_CODE;
        $row[0]['DELIVERALL'] = DELIVERALL;
        $row[0]['ONLYDELIVERALL'] = ONLYDELIVERALL;
        $row[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
        $row[0]['CurrencySymbol'] = get_value('currency', 'vSymbol', 'vName', $row[0]['vCurrencyPassenger'], '', 'true');
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

        $row[0]['vRCountryImage'] = $usercountrydetailbytimezone['vRImageMember']; //added by SP for country image related changes on 05-08-2019
        $row[0]['vSCountryImage'] = $usercountrydetailbytimezone['vSImageMember']; //added by SP for country image related changes on 05-08-2019

        $row[0]['vDefaultCountryImage'] = empty($row[0]['vSCountryImage']) ? $usercountrydetailbytimezone['vDefaultCountryImage'] : $row[0]['vSCountryImage']; //added by SP for country image related changes on 06-08-2019
        $row[0]['vDefaultPhoneCode'] = $usercountrydetailbytimezone['vDefaultPhoneCode'];
        $row[0]['vPhoneCode'] = empty($row[0]['vPhoneCode']) ? $row[0]['vDefaultPhoneCode'] : $row[0]['vPhoneCode'];
        $row[0]['vCountry'] = empty($row[0]['vCountry']) ? $row[0]['vDefaultCountryCode'] : $row[0]['vCountry'];

        $SITE_POLICE_CONTROL_NUMBER = getMemberCountryPoliceNumber($passengerID, "Passenger", $row[0]['vCountry']);
        $row[0]['SITE_POLICE_CONTROL_NUMBER'] = $SITE_POLICE_CONTROL_NUMBER;
        $UserSelectedAddressArr = GetUserSelectedAddress($passengerID, "Passenger");
        $row[0]['UserSelectedAddress'] = $UserSelectedAddressArr['UserSelectedAddress'];
        $row[0]['UserSelectedLatitude'] = $UserSelectedAddressArr['UserSelectedLatitude'];
        $row[0]['UserSelectedLongitude'] = $UserSelectedAddressArr['UserSelectedLongitude'];
        $row[0]['UserSelectedAddressId'] = $UserSelectedAddressArr['UserSelectedAddressId'];
        $fOutStandingAmount = GetPassengerOutstandingAmount($passengerID);
        $row[0]['DISABLE_CASH_PAYMENT_OPTION'] = "No";
        $row[0]['fOutStandingAmount'] = 0;
        if ($fOutStandingAmount > 0) {
            $row[0]['DISABLE_CASH_PAYMENT_OPTION'] = "Yes";
            $getPriceUserCurrencyArr = getPriceUserCurrency($passengerID, "Passenger", $fOutStandingAmount);
            $row[0]['fOutStandingAmount'] = $getPriceUserCurrencyArr['fPricewithsymbol'];
        }

        $row[0]['ServiceCategories'] = json_decode(serviceCategories, true);
        
        for($i = 0; $i < count($row[0]['ServiceCategories']); $i++){
            $item_tmp = $row[0]['ServiceCategories'][$i];
            if(is_null($item_tmp['tDescription']) || empty($item_tmp['tDescription'])){
               $row[0]['ServiceCategories'][$i]['tDescription'] = "";
            }
        }

        /* As a part of Socket Cluster */
        $row[0]['MAX_ALLOW_TIME_INTERVAL_MILLI'] = (fetchtripstatustimeMAXinterval() + $intervalmins) * 1000;
        $row[0]['RIDER_REQUEST_ACCEPT_TIME'] = $RIDER_REQUEST_ACCEPT_TIME;
        /* As a part of Socket Cluster */
        $vCurrencyPassenger = $row[0]['vCurrencyPassenger'];
        if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
            $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        }
        $CurrencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
        $Ratio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
        $fTripsOutStandingAmount = GetPassengerOutstandingAmount($passengerID);
        $fTripsOutStandingAmount = round(($fTripsOutStandingAmount * $Ratio), 2);
        $row[0]['fOutStandingAmount'] = $fTripsOutStandingAmount;
        $row[0]['fOutStandingAmountWithSymbol'] = $CurrencySymbol . " " . $fTripsOutStandingAmount;
        $row[0]['MONGO_DB'] = $tconfig['tmongodb_databse'];
        $row[0]['MONGO_DB_CONNECTION_PORT'] = $tconfig['tmongodb_port'];
        $row[0]['SERVER_DEFAULT_TIMEZONE'] = date_default_timezone_get();
        if ($row[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $RideDeliveryIconArr = getGeneralVarAll_IconBanner();
            for ($i = 0; $i < count($RideDeliveryIconArr); $i++) {
                $eImageType = $RideDeliveryIconArr[$i]['eImageType'];
                $vName = $RideDeliveryIconArr[$i]['vName'];
                $vValue = $RideDeliveryIconArr[$i]['vValue'];
                $$vName = $vValue;
                if ($eImageType == "No") {
                    $row[0][$vName] = $$vName;
                } else {
                    $row[0][$vName] = ($$vName != "") ? $tconfig['tsite_upload_images_vehicle_category'] . "/" . $$vName : "";
                }
            }
            if (ENABLE_RENTAL_OPTION == 'No') {
                $row[0]['RENTAL_SHOW_SELECTION'] = "None";
                $row[0]['RENTAL_GRID_ICON_NAME'] = "";
                $row[0]['RENTAL_BANNER_IMG_NAME'] = "";
                $row[0]['MOTO_RENTAL_SHOW_SELECTION'] = "None";
                $row[0]['MOTO_RENTAL_GRID_ICON_NAME'] = "";
                $row[0]['MOTO_RENTAL_BANNER_IMG_NAME'] = "";
            }
            $row[0]['ENABLE_MULTI_DELIVERY'] = ENABLE_MULTI_DELIVERY;
            /* $row[0]['RIDE_GRID_ICON_NAME']= ($row[0]['RIDE_GRID_ICON_NAME'] != "")?$tconfig['tsite_upload_images_vehicle_category']."/".$row[0]['RIDE_GRID_ICON_NAME']:""; */
        }
        $row[0]['tsite_upload_docs_file_extensions'] = $tconfig['tsite_upload_docs_file_extensions'];
        $row[0]['SC_CONNECT_URL'] = getSocketURL();
        $row[0]['DELIVERY_SHOW_SELECTION'] = "None";
        $row[0]['MOTO_DELIVERY_SHOW_SELECTION'] = "None";
        $row[0]['ENABLE_ADD_PROVIDER_FROM_STORE'] = ENABLE_ADD_PROVIDER_FROM_STORE;
        $storeCatArr = json_decode(serviceCategories, true);
        $systemStoreEnable = checkSystemStoreSelection();
        if ($systemStoreEnable > 0) {
            for ($g = 0; $g < count($storeCatArr); $g++) {
                //echo "<pre>";print_r($storeCatArr);die;
                $storeData = $generalobj->getStoreDataForSystemStoreSelection($storeCatArr[$g]['iServiceId']);
                $iCompanyId = $storeData['iCompanyId'];
                $storeData['ispriceshow'] = $storeCatArr[$g]['iServiceId'];
                $storeCatArr[$g]['iCompanyId'] = $iCompanyId;
                $storeCatArr[$g]['STORE_DATA'] = $storeData;
                $storeCatArr[$g]['STORE_ID'] = $iCompanyId;
            }
            $companyData = $generalobj->getStoreDataForSystemStoreSelection(0);
            $row[0]['STORE_ID'] = $companyData[0]['iCompanyId'];
        }
        $row[0]['ServiceCategories'] = $storeCatArr;
        for($i = 0; $i < count($row[0]['ServiceCategories']); $i++){
            $item_tmp = $row[0]['ServiceCategories'][$i];
            if(is_null($item_tmp['tDescription']) || empty($item_tmp['tDescription'])){
               $row[0]['ServiceCategories'][$i]['tDescription'] = "";
            }
        }
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not Start
        $getCountryData = $obj->MySQLSelect("SELECT iCountryId FROM country WHERE eStatus='Active'");
        $multiCountry = "No";
        if (count($getCountryData) > 1) {
            $multiCountry = "Yes";
        }
        $row[0]['showCountryList'] = $multiCountry;
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not End
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data Start
        if (checkSharkPackage() && $row[0]['eStatus'] == "Active") {
            $row[0]['advertise_banner_data'] = getAdvertisementBannersAsPerDevice($passengerID, "Passenger");
        }
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data End
        $EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY'); //added by SP for Gojek-gopay
        if (!empty($EnableGopay[0]['vValue'])) {
            $row[0]['ENABLE_GOPAY'] = $EnableGopay[0]['vValue'];
        } else {
            $row[0]['ENABLE_GOPAY'] = '';
        }
        $row[0]['RANDOM_COLORS_KEY_VAL_ARR'] = RANDOM_COLORS_KEY_VAL_ARR;

        $row[0]['UFX_SERVICE_AVAILABLE'] = $generalobj->CheckUfxServiceAvailable();
        $row[0]['AUTH_EMAIL_SYSTEM'] = AUTH_EMAIL_SYSTEM;
        $row[0]['ENABLE_CATEGORY_WISE_STORES'] = (isStoreCategoriesEnable()) ? "Yes" : "No";
        $row[0]['CHECK_SYSTEM_STORE_SELECTION'] = ($systemStoreEnable > 0) ? "Yes" : "No";
        $row[0]['ENABLE_TAKE_AWAY'] = (isTakeAwayEnable()) ? "Yes" : "No";

        /* fetch value */
        return $row[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}

function getDriverDetailInfo($driverId, $fromSignIN = 0) {

    global $generalobj, $obj, $demo_site_msg, $PHOTO_UPLOAD_SERVICE_ENABLE, $parent_ufx_catid, $generalSystemConfigDataArr, $vTimeZone, $tconfig, $vUserDeviceCountry, $intervalmins, $generalConfigPaymentArr, $ENABLE_DRIVER_ADVERTISEMENT_BANNER, $ADVERTISEMENT_TYPE, $RIDER_REQUEST_ACCEPT_TIME;
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
    $updateQuery = "UPDATE trip_status_messages SET eReceived='Yes' WHERE iDriverId='" . $driverId . "' AND eToUserType='Driver'";
    $obj->sql_query($updateQuery);
    $returnArr = array();

    $sql = "SELECT rd.*,cmp.eSystem,cmp.eStatus as cmpEStatus,(SELECT dv.vLicencePlate From driver_vehicle as dv WHERE rd.iDriverVehicleId != '' AND rd.iDriverVehicleId !='0' AND dv.iDriverVehicleId = rd.iDriverVehicleId) as vLicencePlateNo FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='$driverId' AND cmp.iCompanyId=rd.iCompanyId";

    //$sql = "SELECT rd.* FROM `register_driver` as rd WHERE rd.iDriverId='$driverId'";
    $Data = $obj->MySQLSelect($sql);
    $Data[0]['eSystem_original'] = $Data[0]['eSystem'];
    if (count($Data) > 0) {

        $defaultCurrencyDataArr = get_value('currency', 'vName,vSymbol', 'eDefault', 'Yes');

        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        $Data[0]['vFlutterwaveCurrency'] = $defaultCurrencyDataArr[0]['vName'];
        $vFlutterwavevSymbol = $defaultCurrencyDataArr[0]['vSymbol'];
        /* Added By PM On 09-12-2019 For Flutterwave Code end */

        $page_link = $tconfig['tsite_url'] . "sign-up.php?UserType=Driver&vRefCode=" . $Data[0]['vRefCode'];
        $link = get_tiny_url($page_link);

        // $activation_text = '<a href="'.$link.'" target="_blank"> '.$link.' </a>';
        $activation_text = "<a href='" . $link . "' target='_blank'> '" . $link . "' </a>";
        $vLanguage = $Data[0]['vLang'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_SHARE_CONTENT_DRIVER' AND vCode = '" . $vLanguage . "'";
        $db_label = $obj->MySQLSelect($sql);
        $LBL_SHARE_CONTENT_DRIVER = $db_label[0]['vValue'];
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
        $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_PREFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        $LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_POSTFIX_ORDER_TXT', " and vCode='" . $vLanguage . "'", 'true');
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
            } else {
                $Data[0]['vWorkLocationRadius'] = $vWorkLocationRadius;
            }
        } else {
            $vWorkLocationRadius = $Data[0]['vWorkLocationRadius'];
            if ($eUnit == "Miles") {
                $Data[0]['vWorkLocationRadius'] = round($vWorkLocationRadius * 0.621371, 2);
            } else {
                $Data[0]['vWorkLocationRadius'] = $vWorkLocationRadius;
            }
        }
        ## Display Braintree Charge Message ##
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_BRAINTREE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'";
        $db_label_braintree = $obj->MySQLSelect($sql);
        $LBL_BRAINTREE_CHARGE_MSG_TXT = $db_label_braintree[0]['vValue'];
        $BRAINTREE_CHARGE_AMOUNT = $Data[0]['BRAINTREE_CHARGE_AMOUNT'];
        $BRAINTREE_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($driverId, "Driver", $BRAINTREE_CHARGE_AMOUNT);
        $BRAINTREE_CHARGE_AMOUNT_USER = $BRAINTREE_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        //$msg = str_replace('##AMOUNT##', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $msg = str_replace('####', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $Data[0]['BRAINTREE_CHARGE_MESSAGE'] = $msg;
        ## Display Braintree Charge Message ##
        ## Display Adyen Charge Message ##
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_ADYEN_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'";
        $db_label_adyen = $obj->MySQLSelect($sql);
        $LBL_ADYEN_CHARGE_MSG_TXT = $db_label_adyen[0]['vValue'];
        $ADEYN_CHARGE_AMOUNT = $Data[0]['ADYEN_CHARGE_AMOUNT'];
        $ADEYN_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($passengerID, "Passenger", $ADEYN_CHARGE_AMOUNT);
        $ADEYN_CHARGE_AMOUNT_USER = $ADEYN_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        $msg = str_replace('####', $ADEYN_CHARGE_AMOUNT_USER, $LBL_ADYEN_CHARGE_MSG_TXT);
        $Data[0]['ADEYN_CHARGE_MESSAGE'] = $msg;
        ## Display Adyen Charge Message ##

        /* Added By PM On 09-12-2019 For Flutterwave Code Start */
        ## Display Flutterwave Charge Message ## 
        $FLUTTERWAVE_CHARGE_AMOUNT = $generalConfigPaymentArr['FLUTTERWAVE_CHARGE_AMOUNT'];
        ## Display Flutterwave Charge Message ## 
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_FLUTTERWAVE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'";
        $db_label_adyen = $obj->MySQLSelect($sql);
        $LBL_FLUTTERWAVE_CHARGE_MSG_TXT = $db_label_adyen[0]['vValue'];

        $amountDataArr = $generalobj->getSupportedCurrencyAmt($Data[0]['FLUTTERWAVE_CHARGE_AMOUNT'], $Data[0]['vFlutterwaveCurrency']);
        $Data[0]['vFlutterwaveCurrency'] = $amountDataArr['CURRENCY_CODE'];

        $FLUTTERWAVE_CHARGE_AMOUNT = $amountDataArr['AMOUNT'];

        $FLUTTERWAVE_CHARGE_AMOUNT_USER = $vFlutterwavevSymbol . $FLUTTERWAVE_CHARGE_AMOUNT;
        $Data[0]['FLUTTERWAVE_CHARGE_AMOUNT'] = $FLUTTERWAVE_CHARGE_AMOUNT;
        $msg = str_replace('####', $FLUTTER_WAVE_CHARGE_AMOUNT_USER, $LBL_FLUTTERWAVE_CHARGE_MSG_TXT);
        $Data[0]['FLUTTERWAVE_CHARGE_MESSAGE'] = $msg;
        /* Added By PM On 09-12-2019 For Flutterwave Code Start */

        ## Check and update Device Session ID ##
        if ($Data[0]['tDeviceSessionId'] == "") {
            $random = substr(md5(rand()), 0, 7);
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
        // $Data[0]['Driver_Password_decrypt']= $generalobj->decrypt($Data[0]['vPassword']);
        $Data[0]['Driver_Password_decrypt'] = "";
        if ($Data[0]['vImage'] != "" && $Data[0]['vImage'] != "NONE") {
            $Data[0]['vImage'] = "3_" . $Data[0]['vImage'];
        }

        if (($Data[0]['iDriverVehicleId'] == '' || $Data[0]['iDriverVehicleId'] == NULL) && $Data[0]['APP_TYPE'] != "Ride-Delivery-UberX") {
            $sql = "SELECT iDriverVehicleId FROM  driver_vehicle WHERE `eStatus` = 'Active' AND `iDriverId` = '" . $driverId . "' ";
            $Data_vehicle = $obj->MySQLSelect($sql);
            $iDriver_VehicleId = $Data_vehicle[0]['iDriverVehicleId'];
            $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $driverId . "'";
            $obj->sql_query($sql);
            $Data[0]['iDriverVehicleId'] = $iDriver_VehicleId;
            $vLicencePlate = get_value('driver_vehicle', 'vLicencePlate', 'iDriverVehicleId', $iDriver_VehicleId, '', 'true');
            $Data[0]['vLicencePlateNo'] = $vLicencePlate;
        }

        if ($Data[0]['iDriverVehicleId'] != '' && $Data[0]['iDriverVehicleId'] != '0') {
            /* $data_vehicle_arr=  get_value('driver_vehicle', 'iMakeId, iModelId', 'iDriverVehicleId', $Data[0]['iDriverVehicleId']);
              $Data[0]['vMake'] = get_value('make', 'vMake', 'iMakeId', $data_vehicle_arr[0]['iMakeId'],'','true');
              $Data[0]['vModel'] = get_value('model', 'vTitle', 'iModelId', $data_vehicle_arr[0]['iModelId'],'','true'); */
            $sql = "SELECT ma.vMake,mo.vTitle FROM driver_vehicle as dv LEFT JOIN make as ma ON dv.iMakeId = ma.iMakeId LEFT JOIN model as mo ON dv.iModelId = mo.iModelId WHERE dv.iDriverVehicleId = '" . $Data[0]['iDriverVehicleId'] . "'";
            $DriverVehicle = $obj->MySQLSelect($sql);
            $Data[0]['vMake'] = $DriverVehicle[0]['vMake'];
            $Data[0]['vModel'] = $DriverVehicle[0]['vTitle'];

            // added
            $vLicencePlate = get_value('driver_vehicle', 'vLicencePlate', 'iDriverVehicleId', $Data[0]['iDriverVehicleId'], '', 'true');
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
            $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$TripID'";
            $db_tripTimes = $obj->MySQLSelect($sql22);
            $totalSec = 0;
            $timeState = 'Pause';
            $iTripTimeId = '';
            foreach ($db_tripTimes as $dtT) {
                if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                    $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
                } else {
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
                    $fNetTotal = round(($fNetTotal * $priceRatio), 2);

                    $sql = "SELECT CONCAT(vName,' ',vLastName) AS UserName FROM register_user WHERE iUserId = '" . $LastOrderUserId . "'";
                    $result_user = $obj->MySQLSelect($sql);

                    $sql = "SELECT count(iRatingId) as TotalRating FROM `ratings_user_driver` WHERE iOrderId='" . $LastOrderId . "' AND eFromUserType = 'Driver'";
                    $row_result_ratings = $obj->MySQLSelect($sql);
                    $TotalRating = $row_result_ratings[0]['TotalRating'];
                    if ($TotalRating > 0) {
                        $Data[0]['Ratings_From_Driver'] = "Done";
                    } else {
                        $Data[0]['Ratings_From_Driver'] = "Not Done";
                    }
                    $Data[0]['LastOrderId'] = $LastOrderId;
                    $Data[0]['LastOrderCompanyId'] = $LastOrderCompanyId;
                    $Data[0]['LastOrderUserId'] = $LastOrderUserId;
                    $Data[0]['LastOrderUserAddress'] = $UserAdress;
                    $Data[0]['LastOrderUserName'] = $result_user[0]['UserName'];
                    $Data[0]['LastOrderAmount'] = $vSymbol . " " . $fNetTotal;
                    $Data[0]['LastOrderNo'] = $LastOrderNo;
                } else {
                    $Data[0]['Ratings_From_Driver'] = "";
                }
                ############################# Food System Ratings From Driver  #############################
            } else {
                ############################# Ride System Ratings From Driver  #############################
                $sql = "SELECT iTripId,eUserType FROM `ratings_user_driver` WHERE iTripId='$TripID'";
                $row_result_ratings = $obj->MySQLSelect($sql);

                if (count($row_result_ratings) > 0) {

                    $count_row_rating = 0;
                    $ContentWritten = "false";
                    while (count($row_result_ratings) > $count_row_rating) {

                        $UserType = $row_result_ratings[$count_row_rating]['eUserType'];

                        if ($UserType == "Driver") {
                            $ContentWritten = "true";
                            $Data[0]['Ratings_From_Driver'] = "Done";
                        } else if ($ContentWritten == "false") {
                            $Data[0]['Ratings_From_Driver'] = "Not Done";
                        }

                        $count_row_rating++;
                    }
                } else {

                    $Data[0]['Ratings_From_Driver'] = "No Entry";
                }
            }
            ############################# Ride System Ratings From Driver  #############################

            $Data[0]['TotalFareUberX'] = "0";
            $Data[0]['TotalFareUberXValue'] = "0";
            $Data[0]['UberXFareCurrencySymbol'] = "";
        }

        ### Driver Order Detail Summury ##
        // $sql = "SELECT iOrderId,iCompanyId,iUserId,iUserAddressId,fNetTotal,vOrderNo FROM `orders` WHERE iDriverId='".$driverId."' AND iStatusCode = '6' ORDER BY iOrderId DESC LIMIT 0,1";
        $sql = "SELECT ord.iOrderId,ord.iCompanyId,ord.iUserId,ord.iUserAddressId,ord.fNetTotal,ord.vOrderNo FROM `orders` as ord WHERE ord.iDriverId='" . $driverId . "' AND ord.iStatusCode = '6' AND (select count(iRatingId) from ratings_user_driver as rud where rud.iOrderId=ord.iOrderId AND rud.eFromUserType = 'Driver' ) = 0  ORDER BY ord.iOrderId DESC LIMIT 0,1";
        $row_order = $obj->MySQLSelect($sql);
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
            $fNetTotal = round(($fNetTotal * $priceRatio), 2);
            $sql = "SELECT count(iRatingId) as TotalRating FROM `ratings_user_driver` WHERE iOrderId='" . $LastOrderId . "' AND eFromUserType = 'Driver'";
            $row_result_ratings = $obj->MySQLSelect($sql);
            $TotalRating = $row_result_ratings[0]['TotalRating'];
            if ($TotalRating > 0) {
                $Data[0]['Ratings_From_Driver'] = "Done";
            } else {
                $Data[0]['Ratings_From_Driver'] = "Not Done";
            }

            $sql = "SELECT CONCAT(vName,' ',vLastName) AS UserName FROM register_user WHERE iUserId = '" . $LastOrderUserId . "'";
            $result_user = $obj->MySQLSelect($sql);
            $Data[0]['LastOrderId'] = $LastOrderId;
            $Data[0]['LastOrderCompanyId'] = $LastOrderCompanyId;
            $Data[0]['LastOrderUserId'] = $LastOrderUserId;
            $Data[0]['LastOrderUserAddress'] = $UserAdress;
            $Data[0]['LastOrderUserName'] = $result_user[0]['UserName'];
            $Data[0]['LastOrderAmount'] = $vSymbol . " " . $fNetTotal;
            $Data[0]['LastOrderNo'] = $LastOrderNo;
        } else {
            $Data[0]['Ratings_From_Driver'] = "";
        }
        ### Driver Order Detail Summury ##
        $sql = "SELECT count(iUserAddressId) as ToTalAddress from user_address WHERE iUserId = '" . $driverId . "' AND eUserType = 'Driver' AND eStatus = 'Active'";
        $result_Address = $obj->MySQLSelect($sql);
        $Data[0]['ToTalAddress'] = $result_Address[0]['ToTalAddress'];
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
        $Data[0]['FETCH_TRIP_STATUS_TIME_INTERVAL'] = fetchtripstatustimeinterval();
        $Data[0]['ENABLE_TOLL_COST'] = $Data[0]['APP_TYPE'] != "UberX" ? $Data[0]['ENABLE_TOLL_COST'] : "No";
        /* Check Toll Enable For Driver's Country */
        $usercountrycode = $Data[0]['vCountry'];
        if ($usercountrycode != "") {
            $sqlc = "SELECT eEnableToll from country WHERE vCountryCode = '" . $usercountrycode . "'";
            $user_country_toll = $obj->MySQLSelect($sqlc);
            $eEnableToll = $user_country_toll[0]['eEnableToll'];
            if ($eEnableToll != "") {
                $Data[0]['ENABLE_TOLL_COST'] = ($eEnableToll == "Yes" && $Data[0]['ENABLE_TOLL_COST'] == "Yes") ? "Yes" : "No";
            }
        }
        /* Check Toll Enable For Driver's Country */
        if ($Data[0]['APP_TYPE'] == "UberX") {
            $Data[0]['APP_DESTINATION_MODE'] = "None";
            $Data[0]['ENABLE_TOLL_COST'] = "No";
            $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";
            $Data[0]['FEMALE_RIDE_REQ_ENABLE'] = "No";
            $Data[0]['ENABLE_HAIL_RIDES'] = "No";
        } else {

            // $Data[0]['APP_DESTINATION_MODE'] = "Strict";
        }

        if ($Data[0]['APP_TYPE'] == "Ride" || $Data[0]['APP_TYPE'] == "Ride-Delivery" || $Data[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $Data[0]['FEMALE_RIDE_REQ_ENABLE'] = $Data[0]['FEMALE_RIDE_REQ_ENABLE'];
            $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'] = $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'];
        } else {
            $Data[0]['FEMALE_RIDE_REQ_ENABLE'] = "No";
            $Data[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";
        }

        if ($Data[0]['APP_TYPE'] == "Ride" || $Data[0]['APP_TYPE'] == "Ride-Delivery" || $Data[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $Data[0]['ENABLE_HAIL_RIDES'] = $Data[0]['ENABLE_HAIL_RIDES'];
        } else {
            $Data[0]['ENABLE_HAIL_RIDES'] = "No";
        }

        if ($Data[0]['APP_PAYMENT_MODE'] == "Card") {
            $Data[0]['ENABLE_HAIL_RIDES'] = "No";
        }

        $Data[0]['PHOTO_UPLOAD_SERVICE_ENABLE'] = $Data[0]['APP_TYPE'] == "UberX" ? $PHOTO_UPLOAD_SERVICE_ENABLE : "No";
        $Data[0]['ENABLE_DELIVERY_MODULE'] = SITE_TYPE == "Demo" ? $Data[0]['eDeliverModule'] : $Data[0]['ENABLE_DELIVERY_MODULE'];
        $Data[0]['PayPalConfiguration'] = $Data[0]['ENABLE_DELIVERY_MODULE'] == "Yes" ? "Yes" : $Data[0]['PAYMENT_ENABLED'];

        // $Data[0]['CurrencyList']=($obj->MySQLSelect("SELECT * FROM currency"));
        $Data[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        $Data[0]['UBERX_PARENT_CAT_ID'] = $parent_ufx_catid;
        $Data[0]['UBERX_SUB_CAT_ID'] = "0";
        /* As a part of Socket Cluster */
        $Data[0]['MAX_ALLOW_TIME_INTERVAL_MILLI'] = (fetchtripstatustimeMAXinterval() + $intervalmins) * 1000;
        $Data[0]['RIDER_REQUEST_ACCEPT_TIME'] = $RIDER_REQUEST_ACCEPT_TIME;
        /* As a part of Socket Cluster */
        // $user_available_balance = $generalobj->get_user_available_balance($driverId,"Driver");
        // $Data[0]['user_available_balance'] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$Data[0]['vCurrencyDriver']));
        $user_available_balance = $generalobj->get_user_available_balance_app_display($driverId, "Driver");
        $Data[0]['user_available_balance'] = strval($user_available_balance);
        $user_available_balance_value = $generalobj->get_user_available_balance_app_display($driverId, "Driver", 'Yes');
        $Data[0]['user_available_balance_value'] = strval($user_available_balance_value);
        if ($user_available_balance_value <= 0 || $user_available_balance_value <= 0.00) {
            $Data[0]['eWalletBalanceAvailable'] = 'No';
        } else {
            $Data[0]['eWalletBalanceAvailable'] = 'Yes';
        }
        $Data[0]['CurrencySymbol'] = get_value('currency', 'vSymbol', 'vName', $Data[0]['vCurrencyDriver'], '', 'true');
        $str_date = @date('Y-m-d H:i:s', strtotime('-1 minutes'));
        $sql_request = "SELECT * FROM passenger_requests WHERE iDriverId='" . $driverId . "' AND dAddedDate > '" . $str_date . "' ";
        $data_requst = $obj->MySQLSelect($sql_request);
        $Data[0]['CurrentRequests'] = $data_requst;
        $sql = "SELECT * FROM user_fave_address where iUserId = '" . $driverId . "' AND eUserType = 'Driver' AND eStatus = 'Active' ORDER BY iUserFavAddressId ASC";
        $db_driver_fav_address = $obj->MySQLSelect($sql);
        $Data[0]['UserFavouriteAddress'] = $db_driver_fav_address;
        $usercountrydetailbytimezone = GetUserCounryDetail($driverId, "Driver", $vTimeZone, $vUserDeviceCountry);
        $Data[0]['vDefaultCountry'] = $usercountrydetailbytimezone['vDefaultCountry'];
        $Data[0]['vDefaultCountryCode'] = $usercountrydetailbytimezone['vDefaultCountryCode'];
        $Data[0]['vDefaultPhoneCode'] = $usercountrydetailbytimezone['vDefaultPhoneCode'];

        $Data[0]['vRCountryImage'] = $usercountrydetailbytimezone['vRImageMember']; //added by SP for country image related changes on 05-08-2019
        $Data[0]['vSCountryImage'] = $usercountrydetailbytimezone['vSImageMember']; //added by SP for country image related changes on 05-08-2019

        $Data[0]['vDefaultCountryImage'] = empty($Data[0]['vSCountryImage']) ? $usercountrydetailbytimezone['vDefaultCountryImage'] : $Data[0]['vSCountryImage']; //added by SP for country image related changes on 06-08-2019
        $Data[0]['vCode'] = empty($Data[0]['vCode']) ? $Data[0]['vDefaultPhoneCode'] : $Data[0]['vCode'];
        $Data[0]['vCountry'] = empty($Data[0]['vCountry']) ? $Data[0]['vDefaultCountryCode'] : $Data[0]['vCountry'];

        $SITE_POLICE_CONTROL_NUMBER = getMemberCountryPoliceNumber($driverId, "Driver", $Data[0]['vCountry']);
        $Data[0]['SITE_POLICE_CONTROL_NUMBER'] = $SITE_POLICE_CONTROL_NUMBER;
        $Data[0]['MONGO_DB'] = $tconfig['tmongodb_databse'];
        $Data[0]['MONGO_DB_CONNECTION_PORT'] = $tconfig['tmongodb_port'];
        $Data[0]['SERVER_DEFAULT_TIMEZONE'] = date_default_timezone_get();
        if ($Data[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $checkridedelivery = CheckRideDeliveryFeatureDisable();
            //echo "<pre>";print_r($checkridedelivery);die;
            $Data[0]['eShowRideVehicles'] = $checkridedelivery['eShowRideVehicles'];
            $Data[0]['eShowDeliveryVehicles'] = $checkridedelivery['eShowDeliveryVehicles'];
            $RideDeliveryIconArr = getGeneralVarAll_IconBanner();
            for ($i = 0; $i < count($RideDeliveryIconArr); $i++) {
                $vName = $RideDeliveryIconArr[$i]['vName'];
                $vValue = $RideDeliveryIconArr[$i]['vValue'];
                $$vName = $vValue;
                $Data[0][$vName] = $$vName;
            }
            $Data[0]['ENABLE_MULTI_DELIVERY'] = ENABLE_MULTI_DELIVERY;
        }
        $Data[0]['ENABLE_ADD_PROVIDER_FROM_STORE'] = ENABLE_ADD_PROVIDER_FROM_STORE;
        $Data[0]['tsite_upload_docs_file_extensions'] = $tconfig['tsite_upload_docs_file_extensions'];
        $Data[0]['SC_CONNECT_URL'] = getSocketURL();
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not Start
        $getCountryData = $obj->MySQLSelect("SELECT iCountryId FROM country WHERE eStatus='Active'");
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
        $EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY'); //added by SP for Gojek-gopay
        if (!empty($EnableGopay[0]['vValue'])) {
            $Data[0]['ENABLE_GOPAY'] = $EnableGopay[0]['vValue'];
        } else {
            $Data[0]['ENABLE_GOPAY'] = '';
        }
        $Data[0]['RANDOM_COLORS_KEY_VAL_ARR'] = RANDOM_COLORS_KEY_VAL_ARR;

        $Data[0]['UFX_SERVICE_AVAILABLE'] = $generalobj->CheckUfxServiceAvailable();
        $Data[0]['AUTH_EMAIL_SYSTEM'] = AUTH_EMAIL_SYSTEM;
        $Data[0]['ENABLE_TAKE_AWAY'] = (isTakeAwayEnable()) ? "Yes" : "No";

        return $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['eStatus'] = "";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}

function getCompanyDetailInfo($iCompanyId, $fromSignIN = 0) {
    global $generalobj, $obj, $demo_site_msg, $PHOTO_UPLOAD_SERVICE_ENABLE, $parent_ufx_catid, $generalSystemConfigDataArr, $vTimeZone, $tconfig, $vUserDeviceCountry, $ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER, $ADVERTISEMENT_TYPE, $SITE_NAME, $THERMAL_PRINT_ENABLE, $SHOW_CITY_FIELD;
    $where = " iCompanyId = '" . $iCompanyId . "'";
    $data_version['iAppVersion'] = "2";
    $data_version['eLogout'] = 'No';
    $data_version['eDebugMode'] = isset($_REQUEST["IS_DEBUG_MODE"]) ? $_REQUEST["IS_DEBUG_MODE"] : "";
    $data_version['tApiFileName'] = pathinfo(__FILE__, PATHINFO_FILENAME);

    #################################### Generate Session For GeoAPI ########################################
    $generalobj->generateSessionForGeo($iCompanyId, "Company");
    #################################### Generate Session For GeoAPI ########################################
    #################################### Configure App Version Info ########################################
    $arr_app_version = array();
    $arr_app_version['AppVersionName'] = isset($_REQUEST['GeneralAppVersion']) ? $_REQUEST['GeneralAppVersion'] : "";
    $arr_app_version['AppVersionCode'] = isset($_REQUEST['GeneralAppVersionCode']) ? $_REQUEST['GeneralAppVersionCode'] : "";
    #################################### Configure App Version Info ########################################
    $data_version['tVersion'] = strval(json_encode($arr_app_version));
    $data_version['tDeviceData'] = isset($_REQUEST['DEVICE_DATA']) ? $_REQUEST['DEVICE_DATA'] : "";
    $obj->MySQLQueryPerform("company", $data_version, 'update', $where);
    $returnArr = array();
    $vLangCode = isset($_REQUEST['vLang']) ? $_REQUEST['vLang'] : "";
    if ($vLangCode != NULL && $vLangCode != "") {
        $check_lng = get_value('language_master', 'vTitle', 'vCode', $vLangCode, '', 'true');
        if ($check_lng != NULL) {
            $languageCode = $vLangCode;
        }
    }

    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql = "SELECT * FROM `company` WHERE iCompanyId='" . $iCompanyId . "'";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
		foreach($generalSystemConfigDataArr as $key => $value){
            if(is_null($generalSystemConfigDataArr[$key]) || empty($generalSystemConfigDataArr[$key])){
                $generalSystemConfigDataArr[$key] = "";
            }
        }
        $Data[0] = array_merge($Data[0], $generalSystemConfigDataArr); // Added By HJ On 18-03-2020 For Optimized Function
        $Data[0]['restaurantAddressAdded'] = "No";
        if ($Data[0]['vRestuarantLocation'] != "" && $Data[0]['vRestuarantLocationLat'] != "" && $Data[0]['vRestuarantLocationLong'] != "") {
            $Data[0]['restaurantAddressAdded'] = "Yes";
        }

        if ($_REQUEST['APP_TYPE'] != "") {
            $Data[0]['APP_TYPE'] = $_REQUEST['APP_TYPE'];
        }

        $Data[0]['PACKAGE_TYPE'] = strtoupper(PACKAGE_TYPE);

        $Data[0]['GOOGLE_ANALYTICS'] = "";
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
        $COMPANY_EMAIL_VERIFICATION = $Data[0]["COMPANY_EMAIL_VERIFICATION"];
        $COMPANY_PHONE_VERIFICATION = $Data[0]["COMPANY_PHONE_VERIFICATION"];
        if ($COMPANY_EMAIL_VERIFICATION == 'No') {
            $Data[0]['eEmailVerified'] = "Yes";
        }

        if ($COMPANY_PHONE_VERIFICATION == 'No') {
            $Data[0]['ePhoneVerified'] = "Yes";
        }

        // # Check and update Device Session ID ##
        if ($Data[0]['tDeviceSessionId'] == "") {
            $random = substr(md5(rand()), 0, 7);
            $Update_Device_Session['tDeviceSessionId'] = session_id() . time() . $random;
            $Update_Device_Session_id = $obj->MySQLQueryPerform("company", $Update_Device_Session, 'update', $where);
            $Data[0]['tDeviceSessionId'] = $Update_Device_Session['tDeviceSessionId'];
        }

        // # Check and update Device Session ID ##
        // # Check and update Session ID ##
        if ($Data[0]['tSessionId'] == "") {
            $Update_Session['tSessionId'] = session_id() . time();
            $Update_Session_id = $obj->MySQLQueryPerform("company", $Update_Session, 'update', $where);
            $Data[0]['tSessionId'] = $Update_Session['tSessionId'];
        }

        // # Check and update Session ID ##
        if ($Data[0]['eStatus'] == "Deleted") {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = $Data[0]['eStatus'];
            $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            setDataResponse($returnArr);
        }

        $Data[0]['RegistrationDate'] = date("Y-m-d", strtotime($Data[0]['tRegistrationDate'] . ' -1 day '));
        $Data[0]['ABOUT_US_PAGE_DESCRIPTION'] = "";
        $Data[0]['DefaultCurrencySign'] = $Data[0]["DEFAULT_CURRENCY_SIGN"];
        $Data[0]['DefaultCurrencyCode'] = $Data[0]["DEFAULT_CURRENCY_CODE"];
        $Data[0]['SITE_TYPE'] = SITE_TYPE;
        $Data[0]['SITE_NAME'] = $SITE_NAME;

        $Data[0]['DELIVERALL'] = DELIVERALL;
        $Data[0]['ONLYDELIVERALL'] = ONLYDELIVERALL;
        $Data[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
        $Data[0]['FETCH_TRIP_STATUS_TIME_INTERVAL'] = fetchtripstatustimeinterval();
        $Data[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        $Data[0]['CurrencySymbol'] = get_value('currency', 'vSymbol', 'vName', $Data[0]['vCurrencyCompany'], '', 'true');
        $usercountrydetailbytimezone = GetUserCounryDetail($iCompanyId, "Company", $vTimeZone, $vUserDeviceCountry);
        $Data[0]['vDefaultCountry'] = $usercountrydetailbytimezone['vDefaultCountry'];
        $Data[0]['vDefaultCountryCode'] = $usercountrydetailbytimezone['vDefaultCountryCode'];
        $Data[0]['vDefaultPhoneCode'] = $usercountrydetailbytimezone['vDefaultPhoneCode'];

        //$Data[0]['vRCountryImage'] = $usercountrydetailbytimezone['vRImage']; //added by SP for country image related changes on 05-08-2019
        //$Data[0]['vSCountryImage'] = $usercountrydetailbytimezone['vSImage']; //added by SP for country image related changes on 05-08-2019
        $Data[0]['vRCountryImage'] = $usercountrydetailbytimezone['vRImageMember']; //added by SP for country image related changes on 05-08-2019
        $Data[0]['vSCountryImage'] = $usercountrydetailbytimezone['vSImageMember']; //added by SP for country image related changes on 05-08-2019

        $Data[0]['vDefaultCountryImage'] = empty($Data[0]['vSCountryImage']) ? $usercountrydetailbytimezone['vDefaultCountryImage'] : $Data[0]['vSCountryImage']; //added by SP for country image related changes on 06-08-2019
        $Data[0]['vCode'] = empty($Data[0]['vCode']) ? $Data[0]['vDefaultPhoneCode'] : $Data[0]['vCode'];
        $Data[0]['vCountry'] = empty($Data[0]['vCountry']) ? $Data[0]['vDefaultCountryCode'] : $Data[0]['vCountry'];

        $SITE_POLICE_CONTROL_NUMBER = getMemberCountryPoliceNumber($iCompanyId, "Company", $Data[0]['vCountry']);
        $Data[0]['SITE_POLICE_CONTROL_NUMBER'] = $SITE_POLICE_CONTROL_NUMBER;
        $Data[0]['SC_CONNECT_URL'] = getSocketURL();

        $Data[0]['THERMAL_PRINT_ENABLE'] = $THERMAL_PRINT_ENABLE;
        $Data[0]['THERMAL_PRINT_ALLOWED'] = $Data[0]['eThermalPrintEnable'];
        $Data[0]['AUTO_PRINT'] = $Data[0]['eThermalAutoPrint'];
        unset($Data[0]['eThermalPrintEnable']);
        unset($Data[0]['eThermalAutoPrint']);

        $meta = $generalobj->getStaticPage(47, $languageCode);
        if (isset($meta) && !empty($meta) && $languageCode != NULL) {
            $kotBillFormat = $meta[0]['tPageDesc_' . $languageCode];
            $kotBillFormat = strip_tags($kotBillFormat);
            $Data[0]['KOT_BILL_FORMAT'] = $kotBillFormat;
        } else {
            $Data[0]['KOT_BILL_FORMAT'] = '';
        }

        $Data[0]['SC_CONNECT_URL'] = getSocketURL();
        $Data[0]['tsite_upload_docs_file_extensions'] = $tconfig['tsite_upload_docs_file_extensions'];
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not Start
        $getCountryData = $obj->MySQLSelect("SELECT iCountryId FROM country WHERE eStatus='Active'");
        $multiCountry = "No";
        if (count($getCountryData) > 1) {
            $multiCountry = "Yes";
        }
        $Data[0]['showCountryList'] = $multiCountry;
        //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not End

        $Data[0]['SHOW_CITY_FIELD'] = $SHOW_CITY_FIELD; //city field shown or not
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data Start
        if (checkSharkPackage() && $Data[0]['eStatus'] == "Active") {
            $Data[0]['advertise_banner_data'] = getAdvertisementBannersAsPerDevice($iCompanyId, "Company");
        }
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data End
        $EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY'); //added by SP for Gojek-gopay
        if (!empty($EnableGopay[0]['vValue'])) {
            $Data[0]['ENABLE_GOPAY'] = $EnableGopay[0]['vValue'];
        } else {
            $Data[0]['ENABLE_GOPAY'] = '';
        }
        $Data[0]['RANDOM_COLORS_KEY_VAL_ARR'] = RANDOM_COLORS_KEY_VAL_ARR;
        $Data[0]['AUTH_EMAIL_SYSTEM'] = AUTH_EMAIL_SYSTEM;
        $Data[0]['ENABLE_TAKE_AWAY'] = (isTakeAwayEnable()) ? "Yes" : "No";
        $Data[0]['ENABLE_ADD_PROVIDER_FROM_STORE'] = ENABLE_ADD_PROVIDER_FROM_STORE;

        return $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['eStatus'] = "";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}

/* If no type found */
if (strtoupper(PACKAGE_TYPE) == "SHARK") {
    include_once('include/include_webservice_sharkfeatures.php'); // for 22 feature
}

//add fav store files feature
if (checkFavStoreModule()) {
    include_once('include/features/include_fav_store.php');
}

/* For Gojek-gopay added by SP start */
if (checkGojekGopayModule()) {
    include_once('include/features/include_gojek_gopay.php');
}
/* For Gojek-gopay added by SP end */


/* added by PM for Auto credit wallet driver on 25-01-2020 start */
if (checkAutoCreditDriverModule()) {
    include_once('include/features/include_auto_credit_driver.php');
}
/* added by PM for Auto credit wallet driver on 25-01-2020 end */

if ($type == '') {
    $result['result'] = 0;
    $result['message'] = 'Required parameter missing.';
    setDataResponse($result);
}

/* -------------- For Luggage Lable default and as per user's Prefered language ----------------------- */

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
    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $x[$vLabel] = $vValue;
    }

    $x['vCode'] = $lCode; // to check in which languge code it is loading
    setDataResponse($x);
}

// #########################################################################
// # NEW WEBSERVICE START ##
// #########################################################################
// #########################################################################
if ($type == 'generalConfigData') {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? trim($_REQUEST['GeneralMemberId']) : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $storeCatArr = json_decode(serviceCategories, true);
    
    //it is done bc when in table in desc field insert like [] then null value is shown so app crash so put the following code
    foreach($storeCatArr as $key=>$value) {
        if(is_null($value['tDescription']) || $value['tDescription']=='' || $value['tDescription']=='null' || empty($value['tDescription'])) {
            $storeCatArr[$key]['tDescription'] = '';
        }
    }
    
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    //eType
    //echo "<pre>";print_r($storeCatArr);die;
    $iserviceidstore = 0;
    if (count($storeCatArr) == 1) $iserviceidstore = $storeCatArr[0]['iServiceId'];
    $systemStoreEnable = checkSystemStoreSelection();
    if ($systemStoreEnable > 0) {
        for ($g = 0; $g < count($storeCatArr); $g++) {
            //echo "<pre>";print_r($storeCatArr);die;
            $storeData = $generalobj->getStoreDataForSystemStoreSelection($storeCatArr[$g]['iServiceId']);
            $iCompanyId = $storeData['iCompanyId'];
            $storeData['ispriceshow'] = $storeCatArr[$g]['iServiceId'];
            $storeCatArr[$g]['iCompanyId'] = $iCompanyId;
            $storeCatArr[$g]['STORE_DATA'] = $storeData;
            $storeCatArr[$g]['STORE_ID'] = $iCompanyId;
            //echo "<pre>";print_r($storeCatArr[$g]);die;
        }
        $companyData = $generalobj->getStoreDataForSystemStoreSelection(0);
        $DataArr['STORE_ID'] = $companyData[0]['iCompanyId'];
    }
    $DataArr['ServiceCategories'] = $storeCatArr;
    if ($vLang != '') {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `vCode` = '" . $vLang . "' ";
        $check_label = $obj->MySQLSelect($sql);

        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);

        $vLang = (isset($check_label[0]['vCode']) && $check_label[0]['vCode']) ? $check_label[0]['vCode'] : $default_label[0]['vCode'];
    } else {
        $sql = "SELECT `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);
        $vLang = $default_label[0]['vCode'];
    }
    $DataArr['LanguageLabels'] = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $DataArr['Action'] = "1";
    $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC";
    $defLangValues = $obj->MySQLSelect($sql);
    $DataArr['LIST_LANGUAGES'] = $defLangValues;
    for ($i = 0; $i < count($defLangValues); $i++) {
        if ($defLangValues[$i]['eDefault'] == "Yes") {
            $DataArr['DefaultLanguageValues'] = $defLangValues[$i];
        }
    }
    if ($vLang != "") {
        $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `vCode` = '" . $vLang . "' ";
        $requireLangValues = $obj->MySQLSelect($sql);
        $DataArr['DefaultLanguageValues'] = $requireLangValues[0];
    }
    $ssqlc = " ORDER BY `iDispOrder` ASC";
    if ($UserType == "Company") {
        // $ssqlc .= " AND `eDefault` = 'Yes' ";
    }
    $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' $ssqlc";
    $defCurrencyValues = $obj->MySQLSelect($sql);
    $DataArr['LIST_CURRENCY'] = $defCurrencyValues;
    for ($i = 0; $i < count($defCurrencyValues); $i++) {
        if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
            $DataArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
        }
    }
    //Added By KP On 15-10-2019 For Get Active Currency Functionality Start
    $DataArr['UPDATE_TO_DEFAULT'] = 'No';
    if (!empty($vCurrency)) {
        $sql = "SELECT  iCurrencyId FROM  `currency` WHERE eStatus = 'Active' AND `vName` = '" . $vCurrency . "'";
        $check_currency = $obj->MySQLSelect($sql);
        if (count($check_currency) == 0) {
            $DataArr['UPDATE_TO_DEFAULT'] = 'Yes';
        }
    }
    //Added By KP On 15-10-2019 For Get Active Currency Functionality End
    if (empty($vCurrency)) {
        $DataArr['UPDATE_TO_DEFAULT'] = 'Yes';
    }
    $DataArr = array_merge($DataArr, $generalSystemConfigDataArr); // Added By HJ On 18-03-2020 For Optimized Function
    //Added By HJ On 16-07-2019 For Check Multiple Country Exists Or Not Start
    $getCountryData = $obj->MySQLSelect("SELECT iCountryId FROM country WHERE eStatus='Active'");
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
    } else {
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
    $DataArr['ENABLE_CATEGORY_WISE_STORES'] = (isStoreCategoriesEnable() == 1) ? "Yes" : "No";
    $DataArr = getCustomeNotificationSound($DataArr); // Added By HJ On 06-08-2019 For Get Custome Sound Notification File Name

    $DataArr['CHECK_SYSTEM_STORE_SELECTION'] = ($systemStoreEnable > 0) ? "Yes" : "No";
    setDataResponse($DataArr);
}

// ########################### country_list #############################
if ($type == 'countryList') {
    global $lang_label, $obj, $tconfig, $generalobj;
    $returnArr = array();
    $counter = 0;
    for ($i = 0; $i < 26; $i++) {
        $cahracter = chr(65 + $i);
        $sql = "SELECT COU.* FROM country as COU WHERE COU.eStatus = 'Active' AND COU.vPhoneCode!='' AND COU.vCountryCode!='' AND COU.vCountry LIKE '$cahracter%' ORDER BY COU.vCountry";
        $db_rec = $obj->MySQLSelect($sql);
        if (count($db_rec) > 0) {
            $countryListArr = array();
            $subCounter = 0;
            for ($j = 0; $j < count($db_rec); $j++) {
                $countryListArr[$subCounter] = $db_rec[$j];
                // added by SP if image missing default image shown on 04-10-2019 
                //$temp_image = checkimgexist("/webimages/icons/country_flags/" . strtolower($db_rec[$j]['vCountryCode']) . "_r.png", '1');
                //$countryListArr[$subCounter]['vRImage'] = $temp_image;
                //$temp_image = checkimgexist("/webimages/icons/country_flags/" . strtolower($db_rec[$j]['vCountryCode']) . "_s.png", '2');
                //$countryListArr[$subCounter]['vSImage'] = $temp_image;

                $temp_image = checkimgexist("webimages/icons/country_flags/" . $db_rec[$j]['vRImage'], '1');
                $countryListArr[$subCounter]['vRImage'] = $temp_image;

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

// ##########################################################################
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
    } else {
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
    } else {
        $tblname = "register_driver";
        $eRefType = "Driver";
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
        $socialData = (array) json_decode($_REQUEST["socialData"]);
    }
    if (isset($socialData['pictureUrls']) && $eSignUpType == 'LinkedIn') {
        $pictureUrls = $socialData['pictureUrls']->_total;
        if ($pictureUrls > 0) {
            $vImageURL = $socialData['pictureUrls']->values[0];
        } else {
            $vImageURL = $socialData['pictureUrl'];
        }
    }
    $eSystem = "";
    //Added By HJ On 31-12-2018 For Get LinkedIn Picture Data End
    if ($phone_mobile != "") {
        $checPhoneExist = $generalobj->checkMemberDataInfo($phone_mobile, "", $user_type, $CountryCode, "", $eSystem); //Added By HJ On 09-09-2019 For Chekc User Country and Mobile Number When Register
    }
    if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MOBILE_EXIST";
        setDataResponse($returnArr);
    } else if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 2) {
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
        } else {
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

    // $Data_passenger['vPhoneCode']=$phoneCode;
    $Data_passenger['vCountry'] = $CountryCode;
    $Data_passenger['eDeviceType'] = $deviceType;
    $Data_passenger['vRefCode'] = $generalobj->ganaraterefercode($eRefType);

    // $Data_passenger['vCurrencyPassenger']=$vCurrency;
    $Data_passenger['dRefDate'] = @date('Y-m-d H:i:s');
    $Data_passenger['tRegistrationDate'] = @date('Y-m-d H:i:s');
    $Data_passenger['eSignUpType'] = $eSignUpType;
    if ($eSignUpType == "Facebook" || $eSignUpType == "Google") {
        $Data_passenger['eEmailVerified'] = "Yes";
    }

    $random = substr(md5(rand()), 0, 7);
    $Data_passenger['tDeviceSessionId'] = session_id() . time() . $random;
    $Data_passenger['tSessionId'] = session_id() . time();
    if (SITE_TYPE == 'Demo') {
        $Data_passenger['eStatus'] = 'Active';
        $Data_passenger['eEmailVerified'] = 'Yes';
        $Data_passenger['ePhoneVerified'] = 'Yes';
    }

    $id = $obj->MySQLQueryPerform($tblname, $Data_passenger, 'insert');

    // # Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
    if ($fbid != 0 || $fbid != "") {
        $UserImage = UploadUserImage($id, $user_type, $eSignUpType, $fbid, $vImageURL);
        if ($UserImage != "") {
            $where = " $iMemberId = '$id' ";
            $Data_update_image_member[$vImage] = $UserImage;
            $imageuploadid = $obj->MySQLQueryPerform($tblname, $Data_update_image_member, 'update', $where);
        }
    }

    // # Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
    // $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
    // $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);
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
    for ($i = 0; $i < count($defLangValues); $i++) {
        if ($defLangValues[$i]['eDefault'] == "Yes") {
            $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
        }
    }

    $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
    $defCurrencyValues = $obj->MySQLSelect($sql);
    $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
    for ($i = 0; $i < count($defCurrencyValues); $i++) {
        if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
            $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
        }
    }
    if (SITE_TYPE == 'Demo') {
        $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`";
        $result = $obj->MySQLSelect($query);
        $Drive_vehicle['iDriverId'] = $id;
        $Drive_vehicle['iCompanyId'] = "1";
        $Drive_vehicle['iMakeId'] = "5";
        $Drive_vehicle['iModelId'] = "9";
        $Drive_vehicle['iYear'] = "2014";
        $Drive_vehicle['vLicencePlate'] = "CK201";
        $Drive_vehicle['eStatus'] = "Active";
        $Drive_vehicle['eCarX'] = "Yes";
        $Drive_vehicle['eCarGo'] = "Yes";
        $Drive_vehicle['vCarType'] = $result[0]['countId'];
        $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
        $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
        $obj->sql_query($sql);
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
        } else {
            $returnArr['message'] = getDriverDetailInfo($id);
        }
        $maildata['EMAIL'] = $email;
        $maildata['NAME'] = $Fname;
        //$maildata['PASSWORD'] = "Password: " . $password; //Commented By HJ On 11-01-2019 For Hide Password As Per Discuss With QA BM
        $maildata['SOCIALNOTES'] = '';
        if ($user_type == "Passenger") {
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
        } else {
            $generalobj->send_email_user("DRIVER_REGISTRATION_USER", $maildata);
            $generalobj->send_email_user("DRIVER_REGISTRATION_ADMIN", $maildata);
        }
        setDataResponse($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}

// ######################## isUserExist #############################
if ($type == "isUserExist") {
    $Emid = isset($_REQUEST["Email"]) ? $_REQUEST["Email"] : '';
    $Phone = isset($_REQUEST["Phone"]) ? $_REQUEST["Phone"] : '';
    $fbid = isset($_REQUEST["fbid"]) ? $_REQUEST["fbid"] : '';
    /* if($fbid != ''){
      $sql    = "SELECT vEmail,vPhone,vFbId FROM `register_user` WHERE vEmail = '$Emid' OR vPhone = '$Phone' OR vFbId = '$fbid'";
      }else{
      $sql    = "SELECT vEmail,vPhone,vFbId FROM `register_user` WHERE vEmail = '$Emid' OR vPhone = '$Phone'";
      } */
    $sql = "SELECT vEmail,vPhone,vFbId FROM register_user WHERE 1=1 AND IF('$Emid'!='',vEmail = '$Emid',0) OR IF('$Phone'!='',vPhone = '$Phone',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $returnArr['Action'] = "0";
        if ($Emid == $Data[0]['vEmail']) {
            $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
        } else if ($Phone == $Data[0]['vPhone']) {
            $returnArr['message'] = "LBL_MOBILE_EXIST";
        } else {
            $returnArr['message'] = "LBL_FACEBOOK_ACC_EXIST";
        }
    } else {
        $returnArr['Action'] = "1";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "signIn") {
    $Emid = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $Emid = strtolower($Emid);
    //echo '<pe>';print_R($_REQUEST);die;
    $Password_user = $userPassword = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $DeviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; // Passenger, Driver , Company
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    // $Password_user = $generalobj->encrypt($Password_user);
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
    $passUserType = $UserType;
    if (strtoupper($UserType) == "KIOSK" || strtoupper($UserType) == "HOTEL") {
        $passUserType = "ADMIN";
    }
    $eSystem = "";
    if ($UserType == "Company") {
        $eSystem = "DeliverAll";
    }
    //echo $Emid."===".$userPassword."==".$passUserType;die;
    $checkValid = $generalobj->checkMemberDataInfo($Emid, $userPassword, $passUserType, '', "", $eSystem); //Added By HJ On 09-09-2019 For Chekc User Country and Mobile Number When Login
    if (isset($checkValid['status']) && $checkValid['status'] == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WRONG_DETAIL";
        setDataResponse($returnArr);
    } else if (isset($checkValid['status']) && $checkValid['status'] == 2) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
        setDataResponse($returnArr);
    }
    $primaryField = "iCompanyId";
    $primaryField1 = "iCompanyId";
    if ($UserType == "Passenger") {
        $primaryField = "iUserId";
        $primaryField1 = "iUserId";
    } else if ($UserType == "Driver") {
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
        /* $iCabRequestId= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$Data[0]['iUserId'],'','true');
          $eStatus_cab= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId,'','true'); */
        $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
        $iCabRequestId = $Data_cabrequest[0]['iCabRequestId'];
        $eStatus_cab = $Data_cabrequest[0]['eStatus'];
        if (count($Data) > 0) {
            // Check For Valid password #
            $hash = $Data[0]['vPassword'];
            $checkValidPass = $generalobj->check_password($Password_user, $hash);
            if ($checkValidPass == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WRONG_DETAIL";
                setDataResponse($returnArr);
            }
            // Check For Valid password #
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
                //print_r($Data_update_passenger);die;
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
                for ($i = 0; $i < count($defLangValues); $i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
                $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defCurrencyValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_CURRENCY'] = $defCurrencyValues;

                for ($i = 0; $i < count($defCurrencyValues); $i++) {
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
            } else {
                $returnArr['Action'] = "0";
                if ($Data[0]['eStatus'] != "Deleted") {
                    $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                } else {
                    $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }

                setDataResponse($returnArr);
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";

            setDataResponse($returnArr);
        }
    } else if ($UserType == "Driver") {

        // $sql = "SELECT rd.iDriverId,rd.eStatus,rd.vLang,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE ( rd.vEmail='$Emid' OR rd.vPhone = '$Emid' )  AND rd.vPassword='$Password_user' AND cmp.iCompanyId=rd.iCompanyId";
        $sql = "SELECT rd.iDriverId,rd.eStatus,rd.vLang,rd.vPassword FROM `register_driver` as rd WHERE ( rd.vEmail='$Emid' OR rd.vPhone = '$Emid' ) $whereCondition";
        $Data = $obj->MySQLSelect($sql);
        if (count($Data) > 0) {

            // Check For Valid password #
            $hash = $Data[0]['vPassword'];
            $checkValidPass = $generalobj->check_password($Password_user, $hash);
            if ($checkValidPass == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WRONG_DETAIL";

                setDataResponse($returnArr);
            }

            // Check For Valid password #
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
                for ($i = 0; $i < count($defLangValues); $i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }

                $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defCurrencyValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
                for ($i = 0; $i < count($defCurrencyValues); $i++) {
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
            } else {
                $returnArr['Action'] = "0";
                if ($Data[0]['eStatus'] != "Deleted") {
                    $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                } else {
                    $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                    $returnArr['eStatus'] = $Data[0]['eStatus'];
                }
                setDataResponse($returnArr);
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";

            setDataResponse($returnArr);
        }
    } else {
        $sql = "SELECT iServiceId,iCompanyId,eStatus,vLang,vPassword FROM `company` WHERE ( vEmail='$Emid' OR vPhone = '$Emid' ) AND eSystem = 'DeliverAll' $whereCondition";
        $Data = $obj->MySQLSelect($sql);
        if (count($Data) > 0) {
            // Check For Valid password #
            $hash = $Data[0]['vPassword'];
            $checkValidPass = $generalobj->check_password($Password_user, $hash);
            if ($checkValidPass == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WRONG_DETAIL";
                setDataResponse($returnArr);
            }
            // Check For Valid password #
            if ($Data[0]['eStatus'] != "Deleted") {
                if ($GCMID != '') {
                    $iCompanyId = $Data[0]['iCompanyId'];
                    $where = " iCompanyId = '$iCompanyId' ";
                    if ($Data[0]['vLang'] == "" && $vLang == "") {
                        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                        $Data_update_company['vLang'] = $vLang;
                    }

                    if ($vLang != "") {
                        $Data_update_company['vLang'] = $vLang;
                        $Data[0]['vLang'] = $vLang;
                    }

                    if ($vCurrency != "") {
                        $Data_update_company['vCurrencyCompany'] = $vCurrency;
                    }

                    $Data_update_company['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                    $Data_update_company['tSessionId'] = session_id() . time();
                    $Data_update_company['iGcmRegId'] = $GCMID;
                    $Data_update_company['eDeviceType'] = $DeviceType;
                    $id = $obj->MySQLQueryPerform("company", $Data_update_company, 'update', $where);
                }

                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $Data[0]['iServiceId']); //added by SP on 2-7-2019 when signin get serviceid from table not from the request becoz a signin not updated
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defLangValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0; $i < count($defLangValues); $i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }

                $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defCurrencyValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
                for ($i = 0; $i < count($defCurrencyValues); $i++) {
                    if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                    }
                }

                $returnArr['Action'] = "1";
                $returnArr['message'] = getCompanyDetailInfo($Data[0]['iCompanyId'], 1);
                $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
                $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues; //put bc naresh wants it in message..

                $vCompanyLang = $vLang;
                if (isset($Data[0]['vLang']) && $Data[0]['vLang'] != "") {
                    $vCompanyLang = $Data[0]['vLang'];
                }
                $returnArr['message']['driverOptionArr'] = getDriverOptions($vCompanyLang, $iServiceId);

                $generalobj->createUserLog($UserType, "No", $Data[0]['iCompanyId'], "Android");
                setDataResponse($returnArr);
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                $returnArr['eStatus'] = $Data[0]['eStatus'];
                setDataResponse($returnArr);
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_DETAIL";
            setDataResponse($returnArr);
        }
    }
}

// ##########################################################################
if ($type == "getDetail") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $GCMID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLangCode = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    if ($UserType == "Passenger") {
        $sql = "SELECT iGcmRegId,vTripStatus,vLang,eChangeLang FROM `register_user` WHERE iUserId='$iUserId'";
        $Data = $obj->MySQLSelect($sql);
        /* $iCabRequestId= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$iUserId,'','true');
          $eStatus_cab= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId,'','true'); */
        $sql_cab = "SELECT iCabRequestId,eStatus FROM cab_request_now WHERE iUserId = '" . $iUserId . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cab = $obj->MySQLSelect($sql_cab);
        $iCabRequestId = $Data_cab[0]['iCabRequestId'];
        $eStatus_cab = $Data_cab[0]['eStatus'];
        if (count($Data) > 0) {

            // # Check and update Session ID ##
            /* $where = " iUserId = '".$iUserId."'";
              $Update_Session['tSessionId'] = session_id().time();
              $Update_Session_id = $obj->MySQLQueryPerform("register_user", $Update_Session, 'update', $where); */

            // # Check and update Session ID ##
            $iGCMregID = $Data[0]['iGcmRegId'];
            $vTripStatus = $Data[0]['vTripStatus'];

            // if($GCMID!=''){
            // if($iGCMregID != $GCMID){
            // $where = " iUserId = '$iUserId' ";
            // $Data_update_passenger['iGcmRegId']=$GCMID;
            // $Data_update_passenger['eDeviceType']=$deviceType;
            // $id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
            // }
            // }
            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['eStatus'] = "";
                $returnArr['message'] = "SESSION_OUT";

                setDataResponse($returnArr);
            }

            if ($Data[0]['vLang'] == "") {
                $where = " iUserId = '$iUserId' ";
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $Data_update_passenger['vLang'] = $vLang;
                $updateid = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where);
                $Data[0]['vLang'] = $vLang;
            }

            if ($eStatus_cab == "Requesting") {
                $where = " iCabRequestId = '$iCabRequestId' ";
                $Data_update_cab_now['eStatus'] = "Cancelled";
                $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_now, 'update', $where);
            }

            if (($vLangCode != $Data[0]['vLang']) || $Data[0]['eChangeLang'] == "Yes") {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $iServiceId);
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $where = " iUserId = '$iUserId' ";
                $Data_update_passenger_lang['eChangeLang'] = "No";
                $updateLangid = $obj->MySQLQueryPerform("register_user", $Data_update_passenger_lang, 'update', $where);
                $Data[0]['eChangeLang'] = "No";
                $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defLangValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0; $i < count($defLangValues); $i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
            } else {
                $returnArr['changeLangCode'] = "No";
            }
            $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
            $defCurrencyValues = $obj->MySQLSelect($sql);
            $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
            for ($i = 0; $i < count($defCurrencyValues); $i++) {
                if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                }
            }
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($iUserId, '', "");
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']); // Added By HJ On 06-08-2019 For Get Custome Sound Notification File Name
            $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues;
            $generalobj->createUserLog($UserType, "Yes", $iUserId, "Android");
        } else {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = "";
            $returnArr['message'] = "SESSION_OUT";
        }

        setDataResponse($returnArr);
    } else if ($UserType == "Driver") {
        $sql = "SELECT iGcmRegId,vLang,eChangeLang FROM `register_driver` WHERE iDriverId='$iUserId'";
        $Data = $obj->MySQLSelect($sql);
        if (count($Data) > 0) {
            $iGCMregID = $Data[0]['iGcmRegId'];

            // # Check and update Session ID ##
            /* $where = " iDriverId = '$iUserId' ";
              $Update_Session['tSessionId'] = session_id().time();
              $Update_Session_id = $obj->MySQLQueryPerform("register_driver", $Update_Session, 'update', $where); */

            // # Check and update Session ID ##
            if ($Data[0]['vLang'] == "") {
                $where = " iDriverId = '$iUserId' ";
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $Data_update_driver['vLang'] = $vLang;
                $updateid = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
            }

            // if($GCMID!=''){
            // if($iGCMregID!=$GCMID){
            // $where = " iDriverId = '$iUserId' ";
            // $Data_update_driver['iGcmRegId']=$GCMID;
            // $id = $obj->MySQLQueryPerform("register_driver",$Data_update_driver,'update',$where);
            // }
            // }
            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['eStatus'] = "";
                $returnArr['message'] = "SESSION_OUT";
                setDataResponse($returnArr);
            }

            if (($vLangCode != $Data[0]['vLang']) || $Data[0]['eChangeLang'] == "Yes") {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $iServiceId);
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $where = " iDriverId = '$iUserId' ";
                $Data_update_passenger_lang['eChangeLang'] = "No";
                $updateLangid = $obj->MySQLQueryPerform("register_driver", $Data_update_passenger_lang, 'update', $where);
                $Data[0]['eChangeLang'] = "No";
                $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defLangValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0; $i < count($defLangValues); $i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
            } else {
                $returnArr['changeLangCode'] = "No";
            }

            $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
            $defCurrencyValues = $obj->MySQLSelect($sql);
            $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
            for ($i = 0; $i < count($defCurrencyValues); $i++) {
                if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                }
            }
            $returnArr['Action'] = "1";
            $returnArr['message'] = getDriverDetailInfo($iUserId);
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']); // Added By HJ On 06-08-2019 For Get Custome Sound Notification File Name
            $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues;
            $generalobj->createUserLog($UserType, "Yes", $iUserId, "Android");
        } else {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = "";
            $returnArr['message'] = "SESSION_OUT";
        }

        setDataResponse($returnArr);
    } else {
        $sql = "SELECT iGcmRegId,vLang,eChangeLang FROM `company` WHERE iCompanyId='$iUserId'";
        $Data = $obj->MySQLSelect($sql);
        if (count($Data) > 0) {
            $iGCMregID = $Data[0]['iGcmRegId'];

            // # Check and update Session ID ##
            /* $where = " iDriverId = '$iUserId' ";
              $Update_Session['tSessionId'] = session_id().time();
              $Update_Session_id = $obj->MySQLQueryPerform("register_driver", $Update_Session, 'update', $where); */

            // # Check and update Session ID ##
            if ($Data[0]['vLang'] == "") {
                $where = " iCompanyId = '$iUserId' ";
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                $Data_update_driver['vLang'] = $vLang;
                $updateid = $obj->MySQLQueryPerform("company", $Data_update_driver, 'update', $where);
            }

            // if($GCMID!=''){
            // if($iGCMregID!=$GCMID){
            // $where = " iDriverId = '$iUserId' ";
            // $Data_update_driver['iGcmRegId']=$GCMID;
            // $id = $obj->MySQLQueryPerform("register_driver",$Data_update_driver,'update',$where);
            // }
            // }
            if ($GCMID != "" && $GCMID != $iGCMregID) {
                $returnArr['Action'] = "0";
                $returnArr['eStatus'] = "";
                $returnArr['message'] = "SESSION_OUT";
                setDataResponse($returnArr);
            }

            if (($vLangCode != $Data[0]['vLang']) || $Data[0]['eChangeLang'] == "Yes") {
                $returnArr['changeLangCode'] = "Yes";
                $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($Data[0]['vLang'], "1", $iServiceId);
                $returnArr['vLanguageCode'] = $Data[0]['vLang'];
                $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $Data[0]['vLang'] . "' ";
                $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
                $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
                $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
                $where = " iCompanyId = '$iUserId' ";
                $Data_update_passenger_lang['eChangeLang'] = "No";
                $updateLangid = $obj->MySQLQueryPerform("company", $Data_update_passenger_lang, 'update', $where);
                $Data[0]['eChangeLang'] = "No";
                $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
                $defLangValues = $obj->MySQLSelect($sql);
                $returnArr['LIST_LANGUAGES'] = $defLangValues;
                for ($i = 0; $i < count($defLangValues); $i++) {
                    if ($defLangValues[$i]['eDefault'] == "Yes") {
                        $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                    }
                }
            } else {
                $returnArr['changeLangCode'] = "No";
            }
            $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
            $defCurrencyValues = $obj->MySQLSelect($sql);
            $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
            for ($i = 0; $i < count($defCurrencyValues); $i++) {
                if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                }
            }
            $returnArr['Action'] = "1";
            $returnArr['message'] = getCompanyDetailInfo($iUserId);
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']); // Added By HJ On 06-08-2019 For Get Custome Sound Notification File Name
            $returnArr['message']['LIST_CURRENCY'] = $defCurrencyValues;
            $vCompanyLang = $vLangCode;
            if (isset($Data[0]['vLang']) && $Data[0]['vLang'] != "") {
                $vCompanyLang = $Data[0]['vLang'];
            }
            $returnArr['message']['driverOptionArr'] = getDriverOptions($vCompanyLang, $iServiceId);
            //echo "<pre>";print_r($returnArr);die;
            $generalobj->createUserLog($UserType, "Yes", $iUserId, "Android");
        } else {
            $returnArr['Action'] = "0";
            $returnArr['eStatus'] = "";
            $returnArr['message'] = "SESSION_OUT";
        }

        setDataResponse($returnArr);
    }
}

// ##########################################################################
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
    // $DeviceType = "Android";
    $DeviceType = $vDeviceType;
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $iMemberId = 'iUserId';
        $vCurrencyMember = "vCurrencyPassenger";
        $vImageFiled = 'vImgName';
    } else {
        $tblname = "register_driver";
        $iMemberId = 'iDriverId';
        $vCurrencyMember = "vCurrencyDriver";
        $vImageFiled = 'vImage';
    }

    if ($user_type == "Passenger") {
        $sql = "SELECT iUserId as iUserId,eStatus,vFbId,vLang,vTripStatus,eSignUpType,vImgName as vImage  FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    } else {
        $sql = "SELECT iDriverId as iUserId,eStatus,vFbId,vLang,vTripStatus,eSignUpType,vImage as vImage FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    }

    /* if($email != ''){
      $sql = "SELECT iUserId,eStatus,vFbId,vLang,vTripStatus FROM `register_user` WHERE vEmail='$email' OR vFbId='$fbid'";
      }else{
      $sql = "SELECT iUserId,eStatus,vFbId,vLang,vTripStatus FROM `register_user` WHERE vFbId='$fbid'";
      } */
    $Data = $obj->MySQLSelect($sql);
    if ($user_type == "Passenger") {
        /* $iCabRequestId= get_value('cab_request_now', 'max(iCabRequestId)', 'iUserId',$Data[0]['iUserId'],'','true');
          $eStatus_cab= get_value('cab_request_now', 'eStatus', 'iCabRequestId',$iCabRequestId,'','true'); */
        $sql_cabrequest = "SELECT iCabRequestId,eStatus FROM `cab_request_now` WHERE iUserId='" . $Data[0]['iUserId'] . "' ORDER BY iCabRequestId DESC LIMIT 0,1";
        $Data_cabrequest = $obj->MySQLSelect($sql_cabrequest);
        $iCabRequestId = $Data_cabrequest[0]['iCabRequestId'];
        $eStatus_cab = $Data_cabrequest[0]['eStatus'];
    }
    //Added By HJ On 31-12-2018 For Get LinkedIn Picture Data Start 
    $socialData = array();
    if (isset($_REQUEST["socialData"])) {
        $socialData = (array) json_decode($_REQUEST["socialData"]);
        //$socialData = (array) json_decode('{"emailAddress":"shaarahicks@gmail.com","firstName":"Shaara","formattedName":"Shaara+Hicks","headline":"Student+at+Kadi+Sarva+Vishwavidyalaya,+Gandihnagar","id":"-Q3dtxeKkj","lastName":"Hicks","location":{"country":{"code":"in"},"name":"Ahmedabad+Area,+India"},"numConnections":0,"pictureUrl":"https:\/\/media.licdn.com\/dms\/image\/C5603AQEVyYuU1ulIsw\/profile-displayphoto-shrink_100_100\/0?e=1551916800&v=beta&t=Ked9RfczVixH4I8rKzcHmHu2BX_YRKgsGlaY6p-CUZc","pictureUrls":{"_total":1,"values":["https:\/\/media.licdn.com\/dms\/image\/C5604AQEV0lBvLEQvLg\/profile-originalphoto-shrink_450_600\/0?e=1551916800&v=beta&t=vtEjixa-2fQqZbXl2F5ONGEfpLlAKjrinIZUKwdsQa0"]},"publicProfileUrl":"http:\/\/www.linkedin.com\/in\/shaara-hicks-9a20a0177"}');
    }
    if (isset($socialData['pictureUrls']) && $eLoginType == 'LinkedIn') {
        $pictureUrls = $socialData['pictureUrls']->_total;
        if ($pictureUrls > 0) {
            $vImageURL = $socialData['pictureUrls']->values[0];
        } else {
            $vImageURL = $socialData['pictureUrl'];
        }
    }
    //Added By HJ On 31-12-2018 For Get LinkedIn Picture Data End
    if (count($Data) > 0) {
        if ($Data[0]['eStatus'] == "Active" || ($user_type == "Driver" && $Data[0]['eStatus'] != "Deleted")) {
            $iUserId_passenger = $Data[0]['iUserId'];

            // $where = " iUserId = '$iUserId_passenger' ";
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

            // # Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
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

            // # Upload Image of Member if SignUp from Google, Facebook Or Twitter ##
            if ($GCMID != '') {
                $Data_update_passenger['iGcmRegId'] = $GCMID;
                $Data_update_passenger['eDeviceType'] = $DeviceType;
                $Data_update_passenger['vFbId'] = $fbid;
                $Data_update_passenger['eSignUpType'] = $eLoginType;
                $Data_update_passenger['tSessionId'] = session_id() . time();
                $Data_update_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
                /* if($Data[0]['vFbId'] =='' || $Data[0]['vFbId'] == "0"){
                  $Data_update_passenger['vFbId']=$fbid;
                  } */
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
            $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
            $defLangValues = $obj->MySQLSelect($sql);
            $returnArr['LIST_LANGUAGES'] = $defLangValues;
            for ($i = 0; $i < count($defLangValues); $i++) {
                if ($defLangValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
                }
            }

            $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
            $defCurrencyValues = $obj->MySQLSelect($sql);
            $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
            for ($i = 0; $i < count($defCurrencyValues); $i++) {
                if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                    $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
                }
            }

            $returnArr['Action'] = "1";
            if ($user_type == "Passenger") {
                $returnArr['message'] = getPassengerDetailInfo($Data[0]['iUserId'], '', "");
                $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
                $generalobj->createUserLog("Passenger", "No", $Data[0]['iUserId'], "Android");
            } else {
                $returnArr['message'] = getDriverDetailInfo($Data[0]['iUserId'], '');
                $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
                $generalobj->createUserLog("Driver", "No", $Data[0]['iUserId'], "Android");
            }

            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            /* if($Data[0]['eStatus'] !="Deleted"){
              $returnArr['message'] ="LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
              }else{
              $returnArr['message'] ="LBL_ACC_DELETE_TXT";
              } */
            if ($Data[0]['eStatus'] != "Deleted") {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
                $returnArr['eStatus'] = $Data[0]['eStatus'];
            } else {
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
                $returnArr['eStatus'] = $Data[0]['eStatus'];
            }

            setDataResponse($returnArr);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_REGISTER";
        setDataResponse($returnArr);
    }
}

// ########################## Get Available Taxi ##############################
if ($type == "loadAvailableCab") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';
    $PickUpAddress = isset($_REQUEST["PickUpAddress"]) ? $_REQUEST["PickUpAddress"] : '';
    $geoCodeResult = isset($_REQUEST["currentGeoCodeResult"]) ? $_REQUEST["currentGeoCodeResult"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $scheduleDate = isset($_REQUEST["scheduleDate"]) ? $_REQUEST["scheduleDate"] : '';

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    // $APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    // $address_data = fetch_address_geocode($PickUpAddress,$geoCodeResult);
    if ($APP_TYPE == "UberX" && $scheduleDate != "") {
        $Check_Driver_UFX = "Yes";
        $sdate = explode(" ", $scheduleDate);
        $shour = explode("-", $sdate[1]);
        $shour1 = $shour[0];
        $Check_Date_Time = $sdate[0] . " " . $shour1 . ":00:00";
    } else {
        $Check_Driver_UFX = "No";
        $Check_Date_Time = "";
    }

    $address_data['PickUpAddress'] = $PickUpAddress;
    $DataArr = getOnlineDriverArr($passengerLat, $passengerLon, $address_data, "No", "No", $Check_Driver_UFX, $Check_Date_Time);
    $Data = $DataArr['DriverList'];

    // $ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations","ALLOW_SERVICE_PROVIDER_AMOUNT");
    $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
    $iParentId = get_value($sql_vehicle_category_table_name, 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    if ($iParentId == 0) {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }

    // $ePriceType=get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
    /* $vLang=get_value('register_user', 'vLang', 'iUserId', $iUserId,'','true');
      $vCurrencyPassenger=get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId,'','true');
      $vCurrencySymbol=get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger,'','true');
      $priceRatio=get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger,'','true'); */
    $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $vLang = $passengerData[0]['vLang'];
    $vCurrencyPassenger = $passengerData[0]['vCurrencyPassenger'];
    $vCurrencySymbol = $passengerData[0]['vSymbol'];
    $priceRatio = $passengerData[0]['Ratio'];
    $i = 0;
    while (count($Data) > $i) {
        if ($Data[$i]['vImage'] != "" && $Data[$i]['vImage'] != "NONE") {
            $Data[$i]['vImage'] = "3_" . $Data[$i]['vImage'];
        }

        $driverVehicleID = $Data[$i]['iDriverVehicleId'];
        $sql = "SELECT dv.*, make.vMake AS make_title, model.vTitle model_title FROM `driver_vehicle` dv, make, model WHERE dv.iMakeId = make.iMakeId AND dv.iModelId = model.iModelId AND iDriverVehicleId='$driverVehicleID'";
        $rows_driver_vehicle = $obj->MySQLSelect($sql);
        $fAmount = "";
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $rows_driver_vehicle[0]['iDriverVehicleId'] . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);
            $vehicleTypeData = get_value('vehicle_type', 'eFareType,fPricePerHour,fFixedFare', 'iVehicleTypeId', $iVehicleTypeId);
            if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                $fAmount = $vCurrencySymbol . formatNum($vehicleTypeData[0]['fFixedFare'] * $priceRatio);
            } else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                $fAmount = $vCurrencySymbol . formatNum($vehicleTypeData[0]['fPricePerHour'] * $priceRatio) . "/hour";
            }

            if (count($serviceProData) > 0) {
                $fAmount = formatNum($serviceProData[0]['fAmount'] * $priceRatio);
                if ($vehicleTypeData[0]['eFareType'] == "Fixed") {
                    $fAmount = $vCurrencySymbol . $fAmount;
                } else if ($vehicleTypeData[0]['eFareType'] == "Hourly") {
                    $fAmount = $vCurrencySymbol . $fAmount . "/hour";
                }
            }

            $rows_driver_vehicle[0]['fAmount'] = $fAmount;
            $rows_driver_vehicle[0]['vCurrencySymbol'] = $vCurrencySymbol;
        }

        $Data[$i]['DriverCarDetails'] = $rows_driver_vehicle[0];
        $i++;
    }

    $where = " iUserId='" . $iUserId . "'";
    $data['vLatitude'] = $passengerLat;
    $data['vLongitude'] = $passengerLon;
    $data['vRideCountry'] = $vCountryCode;
    $data['tLastOnline'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("register_user", $data, 'update', $where);

    // Update User Location Date #
    Updateuserlocationdatetime($iUserId, "Passenger", $vTimeZone);

    // Update User Location Date #
    $returnArr['AvailableCabList'] = $Data;
    $returnArr['PassengerLat'] = $passengerLat;
    $returnArr['PassengerLon'] = $passengerLon;
    if ($APP_TYPE == "Delivery") {
        $ssql .= " AND eType = 'Deliver'";
    } else if ($APP_TYPE == "Ride-Delivery") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
    } else if ($APP_TYPE == "Ride-Delivery-UberX") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride' OR eType = 'UberX')";
    } else {
        $ssql .= " AND eType = '" . $APP_TYPE . "'";
    }

    $pickuplocationarr = array(
        $passengerLat,
        $passengerLon
    );
    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);

    // $sql23 = "SELECT * FROM `vehicle_type` WHERE (iCityId='".$cityId."' OR iCityId = '-1') AND (iStateId='".$stateId."' OR iStateId = '-1') AND (iCountryId='".$countryId."' OR iCountryId = '-1') ORDER BY iVehicleTypeId ASC";
    $sql23 = "SELECT * FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) $ssql ORDER BY iVehicleTypeId ASC";
    $vehicleTypes = $obj->MySQLSelect($sql23);

    // $vehicleTypes = get_value('vehicle_type', '*', '', '',' ORDER BY iVehicleTypeId ASC');
    for ($i = 0; $i < count($vehicleTypes); $i++) {
        $Photo_Gallery_folder = $tconfig["tsite_upload_images_vehicle_type_path"] . '/' . $vehicleTypes[$i]['iVehicleTypeId'] . '/android/' . $vehicleTypes[$i]['vLogo'];
        if ($vehicleTypes[$i]['vLogo'] != "" && file_exists($Photo_Gallery_folder)) {
            $vehicleTypes[$i]['vLogo'] = $vehicleTypes[$i]['vLogo'];
        } else {
            $vehicleTypes[$i]['vLogo'] = "";
        }

        $vehicleTypes[$i]['fPricePerKM'] = round($vehicleTypes[$i]['fPricePerKM'] * $priceRatio, 2);
        $vehicleTypes[$i]['fPricePerMin'] = round($vehicleTypes[$i]['fPricePerMin'] * $priceRatio, 2);
        $vehicleTypes[$i]['iBaseFare'] = round($vehicleTypes[$i]['iBaseFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['fCommision'] = round($vehicleTypes[$i]['fCommision'] * $priceRatio, 2);
        $vehicleTypes[$i]['iMinFare'] = round($vehicleTypes[$i]['iMinFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['FareValue'] = round($vehicleTypes[$i]['fFixedFare'] * $priceRatio, 2);
        $vehicleTypes[$i]['vVehicleType'] = $vehicleTypes[$i]["vVehicleType_" . $vLang];
    }

    if ($APP_TYPE == "UberX") {
        $returnArr['VehicleTypes'] = array();
    } else {
        $returnArr['VehicleTypes'] = $vehicleTypes;
    }
    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "getDriverStates") {
    $driverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $docUpload = 'Yes';
    $driverVehicleUpload = 'Yes';
    $driverStateActive = 'Yes';
    $driverVehicleDocumentUpload = 'Yes';

    // $APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    $vCountry = get_value('register_driver', 'vCountry', 'iDriverId', $driverId, '', true);
    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $driverId . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='driver' and (dm.country='" . $vCountry . "' OR dm.country='All') and dm.status='Active' ";
    $db_document = $obj->MySQLSelect($sql1);
    if (count($db_document) > 0) {
        for ($i = 0; $i < count($db_document); $i++) {
            if ($db_document[$i]['doc_file'] == "") {
                $docUpload = 'No';
            }
        }
    } else {
        $docUpload = 'No';
    }

    if ($APP_TYPE != 'UberX') {

        // # Count Driver Vehicle ##
        $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND eStatus != 'Deleted'";
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
        $returnArr['TotalVehicles'] = strval($TotalVehicles);

        // # Count Driver Vehicle ##
        $sql = "SELECT iDriverVehicleId from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND eStatus != 'Deleted'";
        $db_drv_vehicle = $obj->MySQLSelect($sql);
        if (count($db_drv_vehicle) == 0) {
            $driverVehicleUpload = 'No';
        } else if ($driverVehicleUpload != 'No') {
            $test = array();

            // Check For Driver's selected vehicle's document are upload or not #
            $sql = "SELECT dl.*,dv.iDriverVehicleId FROM `driver_vehicle` AS dv LEFT JOIN document_list as dl ON dl.doc_userid=dv.iDriverVehicleId WHERE dv.iDriverId='$driverId' AND dl.doc_usertype = 'car' AND dv.eStatus != 'Deleted' ";
            $db_selected_vehicle = $obj->MySQLSelect($sql);
            if (count($db_selected_vehicle) > 0) {
                for ($i = 0; $i < count($db_selected_vehicle); $i++) {
                    if ($db_selected_vehicle[$i]['doc_file'] == "") {
                        $test[] = '1';
                    }
                }
            }

            if (count($test) == count($db_selected_vehicle)) {
                $driverVehicleUpload = 'No';
            }

            // # Checking For All document's are upload or not for all vehicle's of driver ##
            /* $sql1= "SELECT doc_masterid FROM document_master where doc_usertype ='car' and ( country='".$vCountry."' OR country='All') and status='Active'";
              $db_vehicle_document_master = $obj->MySQLSelect($sql1);
              if(count($db_vehicle_document_master) > 0){
              for($i=0;$i<count($db_vehicle_document_master);$i++){
              $doc_masterid = $db_vehicle_document_master[$i]['doc_masterid'];
              $sql = "SELECT iDriverVehicleId from driver_vehicle WHERE iDriverId = '".$driverId."' AND eStatus != 'Deleted'";
              $db_driver_Total_vehicle = $obj->MySQLSelect($sql);
              if(count($db_driver_Total_vehicle) > 0){
              for($j=0;$j<count($db_driver_Total_vehicle);$j++){
              $iDriverVehicleId = $db_driver_Total_vehicle[$j]['iDriverVehicleId'];
              $sql = "SELECT doc_id from document_list WHERE doc_masterid = '".$doc_masterid."' AND doc_usertype = 'car' AND doc_userid = '".$iDriverVehicleId."'";
              $db_driver_vehicle_document_upload = $obj->MySQLSelect($sql);
              if(count($db_driver_vehicle_document_upload) == 0){
              $driverVehicleDocumentUpload = "No";
              break;
              }
              }
              }else{
              $driverVehicleDocumentUpload = "No";
              }
              }
              } */

            // # Checking For All document's are upload or not for all vehicle's of driver ##
        }
    } else {
        $sql = "SELECT vCarType from driver_vehicle WHERE iDriverId = '" . $driverId . "'";
        $db_drv_vehicle = $obj->MySQLSelect($sql);
        if ($db_drv_vehicle[0]['vCarType'] == "") {
            $driverVehicleUpload = 'No';
        } else {
            $driverVehicleUpload = 'Yes';
        }
    }

    $sql = "SELECT rd.eStatus as driverstatus,cmp.eStatus as cmpEStatus FROM `register_driver` as rd,`company` as cmp WHERE rd.iDriverId='" . $driverId . "' AND cmp.iCompanyId=rd.iCompanyId";
    $Data = $obj->MySQLSelect($sql);
    if (strtolower($Data[0]['driverstatus']) != "active" || strtolower($Data[0]['cmpEStatus']) != "active") {
        $driverStateActive = 'No';
    }

    if ($APP_TYPE == "UberX") {
        $sql = "select * from `driver_manage_timing` where iDriverId = '" . $driverId . "'";
        $db_driver_timing = $obj->MySQLSelect($sql);
        if (count($db_driver_timing) > 0) {
            $returnArr['IS_DRIVER_MANAGE_TIME_AVAILABLE'] = "Yes";
        } else {
            $returnArr['IS_DRIVER_MANAGE_TIME_AVAILABLE'] = "No";
        }
    }

    if ($driverStateActive == "Yes") {
        $docUpload = "Yes";
        $driverVehicleUpload = "Yes";
        $driverVehicleDocumentUpload = "Yes";
    }

    $returnArr['Action'] = "1";
    $returnArr['IS_DOCUMENT_PROCESS_COMPLETED'] = $docUpload;
    $returnArr['IS_VEHICLE_PROCESS_COMPLETED'] = $driverVehicleUpload;
    $returnArr['IS_VEHICLE_DOCUMENT_PROCESS_COMPLETED'] = $driverVehicleDocumentUpload;
    $returnArr['IS_DRIVER_STATE_ACTIVATED'] = $driverStateActive;

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "CheckPromoCode") {
    $validPromoCodesArr = getValidPromoCodes();
    if (!empty($validPromoCodesArr) && !empty($validPromoCodesArr['CouponList']) && count($validPromoCodesArr['CouponList']) > 0) {
        $returnArr['Action'] = "1"; // code is valid
        $returnArr["message"] = "LBL_SUCCESS_COUPON_CODE";
        setDataResponse($returnArr);
    } else {
        $returnArr['Action'] = "0"; // code is invalid
        $returnArr["message"] = "LBL_INVALID_COUPON_CODE";
        setDataResponse($returnArr);
    }
}

// ##########################################################################
if ($type == 'estimateFare') {
    $sourceLocation = isset($_REQUEST["sourceLocation"]) ? $_REQUEST["sourceLocation"] : '';
    $destinationLocation = isset($_REQUEST["destinationLocation"]) ? $_REQUEST["destinationLocation"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $distance = isset($_REQUEST["distance"]) ? $_REQUEST["distance"] : '';
    $time = isset($_REQUEST["time"]) ? $_REQUEST["time"] : '';
    $SelectedCar = isset($_REQUEST["SelectedCar"]) ? $_REQUEST["SelectedCar"] : '';
    $sourceLocationArr = explode(",", $sourceLocation);
    $destinationLocationArr = explode(",", $destinationLocation);
    /* $vCurrencyPassenger=get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId,'','true');
      $priceRatio=get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger,'','true'); */
    $sqlp = "SELECT ru.vCurrencyPassenger,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $vCurrencyPassenger = $passengerData[0]['vCurrencyPassenger'];
    $priceRatio = $passengerData[0]['Ratio'];
    $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $SelectedCar);
    $eFlatTrip = $data_flattrip['eFlatTrip'];
    $fFlatTripPrice = $data_flattrip['Flatfare'];
    if ($eFlatTrip == "No") {
        $Fare_data = calculateFareEstimate($time, $distance, $SelectedCar, $iUserId, 1);
        $Fare_data[0]['Distance'] = $distance == NULL ? "0" : strval(round($distance, 2));
        $Fare_data[0]['Time'] = $time == NULL ? "0" : strval(round($time, 2));
        $Fare_data[0]['total_fare'] = number_format(round($Fare_data[0]['total_fare'] * $priceRatio, 1), 2);
        $Fare_data[0]['iBaseFare'] = number_format(round($Fare_data[0]['iBaseFare'] * $priceRatio, 1), 2);
        $Fare_data[0]['fPricePerMin'] = number_format(round($Fare_data[0]['fPricePerMin'] * $priceRatio, 1), 2);
        $Fare_data[0]['fPricePerKM'] = number_format(round($Fare_data[0]['fPricePerKM'] * $priceRatio, 1), 2);
        $Fare_data[0]['fCommision'] = number_format(round($Fare_data[0]['fCommision'] * $priceRatio, 1), 2);
        $Fare_data[0]['eFlatTrip'] = "No";
        if ($Fare_data[0]['MinFareDiff'] > 0) {
            $Fare_data[0]['MinFareDiff'] = number_format(round($Fare_data[0]['MinFareDiff'] * $priceRatio, 1), 2);
        } else {
            $Fare_data[0]['MinFareDiff'] = "0";
        }

        $Fare_data[0]['MinFareDiff'] = "0";
    } else {
        $Fare_data[0]['Distance'] = "0.00";
        $Fare_data[0]['Time'] = "0.00";
        $Fare_data[0]['total_fare'] = $data_flattrip['Flatfare']; //number_format(round($fFlatTripPrice * $priceRatio,1),2);
        $Fare_data[0]['iBaseFare'] = number_format(round($fFlatTripPrice * $priceRatio, 1), 2);
        $Fare_data[0]['fPricePerMin'] = "0.00";
        $Fare_data[0]['fPricePerKM'] = "0.00";
        $Fare_data[0]['fCommision'] = number_format(round($fFlatTripPrice * $priceRatio, 1), 2);
        $Fare_data[0]['eFlatTrip'] = "Yes";
        $Fare_data[0]['MinFareDiff'] = "0.00";
        $Fare_data[0]['Flatfare'] = $data_flattrip['Flatfare'];
    }

    $Fare_data[0]['Action'] = "1";
    setDataResponse($Fare_data[0]);
}

// ##########################################################################
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
    $time = round(($time / 60), 2);
    $distance = round(($distance / 1000), 2);
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

    // ######## Checking For Flattrip #########
    /* if($isDestinationAdded == "Yes"){
      $sourceLocationArr = array($StartLatitude,$EndLongitude);
      $destinationLocationArr = array($DestLatitude,$DestLongitude);
      $data_flattrip = checkFlatTripnew($sourceLocationArr,$destinationLocationArr);
      $eFlatTrip = $data_flattrip['eFlatTrip'];
      $fFlatTripPrice = $data_flattrip['Flatfare'];
      }else{
      $eFlatTrip = "No";
      $fFlatTripPrice = 0;
      } */

    // ######## Checking For Flattrip #########
    // $Fare_data=calculateFareEstimateAll($time,$distance,$SelectedCar,$iUserId,1);
    $Fare_data = calculateFareEstimateAll($time, $distance, $SelectedCar, $iUserId, 1, "", "", $PromoCode, 1, 0, 0, 0, "", "Passenger", $iQty, $SelectedCarTypeID, $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $sourceLocationArr, $destinationLocationArr);
    $returnArr["Action"] = "1";
    $returnArr["message"] = $Fare_data;

    // $returnArr['eFlatTrip'] = $eFlatTrip;

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "updateUserProfileDetail") {
    $vName = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : '';
    $vLastName = isset($_REQUEST["vLastName"]) ? stripslashes($_REQUEST["vLastName"]) : '';
    $vPhone = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST['iMemberId'] : '';
    $phoneCode = isset($_REQUEST["vPhoneCode"]) ? $_REQUEST['vPhoneCode'] : '';
    $vCountry = isset($_REQUEST["vCountry"]) ? $_REQUEST['vCountry'] : '';
    $currencyCode = isset($_REQUEST["CurrencyCode"]) ? $_REQUEST['CurrencyCode'] : '';
    $languageCode = isset($_REQUEST["LanguageCode"]) ? $_REQUEST['LanguageCode'] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST['UserType'] : 'Passenger'; // Passenger, Driver, Company
    $vEmail = isset($_REQUEST["vEmail"]) ? $_REQUEST['vEmail'] : '';
    $tProfileDescription = isset($_REQUEST["tProfileDescription"]) ? $_REQUEST['tProfileDescription'] : '';
    if ($userType == "" || $userType == NULL) {
        $userType = "Passenger";
    }
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
                $checkEditProfileStatus = "No";
            }
            if ($checkEditProfileStatus == "No") {
                $returnArr['Action'] = "0";
                $returnArr['message'] = $message;
                setDataResponse($returnArr);
            }
        } else if ($ENABLE_EDIT_DRIVER_PROFILE == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = $message;
            setDataResponse($returnArr);
        }
    }

    //echo "<pre>";print_r($_REQUEST);die;
    //Added By HJ On 10-08-2019 For Check Provider Profile Edit Permission Start
    /* if ($ENABLE_EDIT_DRIVER_PROFILE == "No" && $userType == "Driver") {
      $returnArr['Action'] = "0";
      $returnArr['message'] = "LBL_EDIT_PROFILE_DISABLED";
      setDataResponse($returnArr);
      } */
    //Added By HJ On 10-08-2019 For Check Provider Profile Edit Permission End
    $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $phoneCode . "'";
    $CountryData = $obj->MySQLSelect($csql);
    $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    if ($eZeroAllowed == 'Yes') {
        $vPhone = $vPhone;
    } else {
        $first = substr($vPhone, 0, 1);
        if ($first == "0") {
            $vPhone = substr($vPhone, 1);
        }
    }
    $eSystem = "";
    if ($vPhone != "") {
        if (strtolower($userType) == "company") {
            $companyData = $obj->MySQLSelect("SELECT vLang,vCode,vPhone,vEmail,eSystem FROM company WHERE iCompanyId = '" . $iMemberId . "'");
            if (count($companyData) > 0) {
                $eSystem = $companyData[0]['eSystem'];
            }
        }
        $checPhoneExist = $generalobj->checkMemberDataInfo($vPhone, "", $userType, $vCountry, $iMemberId, $eSystem); //Added By HJ On 09-09-2019 For Chekc User Country and Mobile Number When Register
    }
    if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MOBILE_EXIST";
        setDataResponse($returnArr);
    } else if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 2) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
        setDataResponse($returnArr);
    }
    if ($userType == "Passenger") {
        $vEmail_userId_check = get_value('register_user', 'iUserId', 'vEmail', $vEmail, '', 'true');
        $vPhone_userId_check = get_value('register_user', 'iUserId', 'vPhone', $vPhone, '', 'true');
        $where = " iUserId = '$iMemberId'";
        $tableName = "register_user";
        $Data_update_User['vPhoneCode'] = $phoneCode;
        $Data_update_User['vCurrencyPassenger'] = $currencyCode;
        $currentLanguageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');

        $sqlp = "SELECT vPhoneCode,vPhone,vEmail FROM register_user WHERE iUserId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $vPhoneCode_orig = $passengerData[0]['vPhoneCode'];
        $vPhone_orig = $passengerData[0]['vPhone'];
        $vEmail_orig = $passengerData[0]['vEmail'];
    } else if ($userType == "Driver") {
        $vEmail_userId_check = get_value('register_driver', 'iDriverId', 'vEmail', $vEmail, '', 'true');
        //$vPhone_userId_check = get_value('register_driver', 'iDriverId', 'vPhone', $vPhone, '', 'true');
        $where = " iDriverId = '$iMemberId'";
        $tableName = "register_driver";
        $Data_update_User['vCode'] = $phoneCode;
        $Data_update_User['vCurrencyDriver'] = $currencyCode;
        $Data_update_User['tProfileDescription'] = $tProfileDescription;

        if (empty($driverData) || count($driverData) == 0) {
            $sqlp = "SELECT vLang,vCode,vPhone,vEmail,vInviteCode FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
            $driverData = $obj->MySQLSelect($sqlp);
        }
        // $sqlp = "SELECT vLang,vCode,vPhone,vEmail FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
        // $companyData = $obj->MySQLSelect($sqlp);
        $currentLanguageCode = $driverData[0]['vLang'];
        $vPhoneCode_orig = $driverData[0]['vCode'];
        $vPhone_orig = $driverData[0]['vPhone'];
        $vEmail_orig = $driverData[0]['vEmail'];
    } else {
        if (count($companyData) == 0) {
            $companyData = $obj->MySQLSelect("SELECT vLang,vCode,vPhone,vEmail FROM company WHERE iCompanyId = '" . $iMemberId . "'");
        }
        $checkEmial = $obj->MySQLSelect("SELECT iCompanyId FROM company WHERE vEmail = '" . $vEmail . "' AND eSystem='" . $eSystem . "'");
        if (count($checkEmial) > 0) {
            $vEmail_userId_check = $checkEmial[0]['iCompanyId'];
        }
        //$vEmail_userId_check = get_value('company', 'iCompanyId', 'vEmail', $vEmail, '', 'true');
        //$vPhone_userId_check = get_value('company', 'iCompanyId', 'vPhone', $vPhone, '', 'true');
        $where = " iCompanyId = '$iMemberId'";
        $tableName = "company";
        $Data_update_User['vCode'] = $phoneCode;
        $Data_update_User['vCurrencyCompany'] = $currencyCode;

        $currentLanguageCode = $companyData[0]['vLang'];
        $vPhoneCode_orig = $companyData[0]['vCode'];
        $vPhone_orig = $companyData[0]['vPhone'];
        $vEmail_orig = $companyData[0]['vEmail'];
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

    if ($vEmail != "") {
        $Data_update_User['vEmail'] = $vEmail;
    }

    if ($userType == "Company") {
        $Data_update_User['vCompany'] = $vName;
        $Data_update_User['vPhone'] = $vPhone;
        $Data_update_User['vCountry'] = $vCountry;
        $Data_update_User['vLang'] = $languageCode;
        if ($vPhone_orig != $vPhone || $vPhoneCode_orig != $phoneCode || $vEmail_orig != $vEmail) {
            $Data_update_User['eAvailable'] = "No";
        }
    } else {
        $Data_update_User['vName'] = $vName;
        $Data_update_User['vLastName'] = $vLastName;
        $Data_update_User['vPhone'] = $vPhone;
        $Data_update_User['vCountry'] = $vCountry;
        $Data_update_User['vLang'] = $languageCode;
    }

    $id = $obj->MySQLQueryPerform($tableName, $Data_update_User, 'update', $where);
    if ($currentLanguageCode != $languageCode) {
        $returnArr['changeLangCode'] = "Yes";
        $returnArr['UpdatedLanguageLabels'] = getLanguageLabelsArr($languageCode, "1", $iServiceId);
        $returnArr['vLanguageCode'] = $languageCode;
        /* $returnArr['langType'] = get_value('language_master', 'eDirectionCode', 'vCode',$languageCode,'','true');
          $returnArr['vGMapLangCode'] = get_value('language_master', 'vGMapLangCode', 'vCode',$languageCode,'','true'); */
        $sql_LangCode = "SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $languageCode . "' ";
        $Data_checkLangCode = $obj->MySQLSelect($sql_LangCode);
        $returnArr['langType'] = $Data_checkLangCode[0]['eDirectionCode'];
        $returnArr['vGMapLangCode'] = $Data_checkLangCode[0]['vGMapLangCode'];
        $sql = "SELECT vCode, vGMapLangCode, eDirectionCode as eType, vTitle,vCurrencyCode,vCurrencySymbol,eDefault  FROM  `language_master` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
        $defLangValues = $obj->MySQLSelect($sql);
        $returnArr['LIST_LANGUAGES'] = $defLangValues;
        for ($i = 0; $i < count($defLangValues); $i++) {
            if ($defLangValues[$i]['eDefault'] == "Yes") {
                $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
            }
        }
    } else {
        $returnArr['changeLangCode'] = "No";
    }

    $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
    $defCurrencyValues = $obj->MySQLSelect($sql);
    $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
    for ($i = 0; $i < count($defCurrencyValues); $i++) {
        if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
            $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
        }
    }
    if ($userType == "Passenger") {
        $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
    } else if ($userType == "Driver") {
        $returnArr['message'] = getDriverDetailInfo($iMemberId);
    } else {
        $returnArr['message'] = getCompanyDetailInfo($iMemberId);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "uploadImage") {
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $image_name = "123.jpg";
    //echo "<pre>";print_r($_FILES);die;
    if ($memberType == "Driver") {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_driver_path'] . "/" . $iMemberId . "/";
    } else {
        $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger_path'] . "/" . $iMemberId . "/";
    }

    // echo $Photo_Gallery_folder."===";
    if (!is_dir($Photo_Gallery_folder))
        mkdir($Photo_Gallery_folder, 0777);

    // echo $tconfig["tsite_upload_images_member_size1"];exit;
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
        } else {
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
            } else {
                $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
            }
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

// ###################### getRideHistory #############################
if ($type == "getRideHistory") {
    global $generalobj;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    $per_page = 10;
    $sql_all = "SELECT COUNT(iTripId) As TotalIds FROM trips WHERE  iUserId='$iUserId' AND (iActive='Canceled' || iActive='Finished')";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    // $sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$iUserId' AND tr.eType='$eType' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
    $sql = "SELECT tr.* FROM `trips` as tr WHERE tr.iUserId='$iUserId' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
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

            $i++;
        }

        $returnData['message'] = $Data;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = "" . ($page + 1);
        } else {
            $returnData['NextPage'] = "0";
        }

        $returnData['Action'] = "1";

        setDataResponse($returnData);
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_DATA_AVAIL";
        setDataResponse($returnData);
    }
}

// ##########################################################################
if ($type == 'staticPage') {
    $iPageId = isset($_REQUEST['iPageId']) ? clean($_REQUEST['iPageId']) : '';

    $languageCode = getUserLanguageCode();
    $pageDesc = get_value('pages', 'tPageDesc_' . $languageCode, 'iPageId', $iPageId, '', 'true');
    // $meta['page_desc']=strip_tags($pageDesc);
    $meta['page_desc'] = $pageDesc;
    setDataResponse($meta);
}
// ##########################################################################
if ($type == 'sendContactQuery') {
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $UserId = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $subject = isset($_REQUEST["subject"]) ? $_REQUEST["subject"] : '';
    if ($UserType == 'Passenger') {
        $sql = "SELECT vName,vLastName,vPhone,vEmail FROM register_user WHERE iUserId=$UserId";
        $result_data = $obj->MySQLSelect($sql);
    } else if ($UserType == 'Driver') {
        $sql = "SELECT vName,vLastName,vPhone,vEmail FROM register_driver WHERE iDriverId=$UserId";
        $result_data = $obj->MySQLSelect($sql);
    } else if ($UserType == 'Company') {
        $sql = "SELECT vCompany,vPhone,vEmail FROM company WHERE iCompanyId=$UserId";
        $result_data = $obj->MySQLSelect($sql);
    }

    if ($UserId != "") {
        //$Data['vFirstName'] = $result_data[0]['vName'];
        //$Data['vLastName'] = $result_data[0]['vLastName'];
        if ($UserType == 'Company') {
            $Data['vFirstName'] = $result_data[0]['vCompany'];
            $Data['vLastName'] = "";
        } else {
            $Data['vFirstName'] = $result_data[0]['vName'];
            $Data['vLastName'] = $result_data[0]['vLastName'];
        }
        $Data['vEmail'] = $result_data[0]['vEmail'];
        $Data['cellno'] = $result_data[0]['vPhone'];
        $Data['eSubject'] = $subject;
        $Data['tSubject'] = $message;
        $id = $generalobj->send_email_user("CONTACTUS", $Data);
    } else {
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
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_CONTACT_QUERY_TXT";
    }

    setDataResponse($returnArr);
}

// ############################ GetFAQ ######################################
if ($type == "getFAQ") {
    $status = "Active";
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
    $vLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }

    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    if ($vLang != "") {
        $languageCode = $vLang;
    }

    $sql = "SELECT * FROM `faq_categories` WHERE eStatus='$status' AND vCode='" . $languageCode . "' AND ( eCategoryType = 'General' OR eCategoryType = '" . $GeneralUserType . "' ) ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);
    $i = 0;
    if (count($Data) > 0) {
        $row = $Data;
        while (count($row) > $i) {
            $rows_questions = array();
            $iUniqueId = $row[$i]['iUniqueId'];
            $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer FROM `faqs` WHERE eStatus='$status' AND iFaqcategoryId='" . $iUniqueId . "'";
            $row_questions = $obj->MySQLSelect($sql);
            $j = 0;
            while (count($row_questions) > $j) {
                $rows_questions[$j] = $row_questions[$j];
                $j++;
            }

            $row[$i]['Questions'] = $rows_questions;
            $i++;
        }

        $returnData['Action'] = "1";
        $returnData['message'] = $row;
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_FAQ_NOT_AVAIL";
    }


    setDataResponse($returnData);
}

if ($type == 'updateStoreAddress') {
    $iCompanyId = isset($_REQUEST['iCompanyId']) ? clean($_REQUEST['iCompanyId']) : '';
    $Address = isset($_REQUEST['Address']) ? clean($_REQUEST['Address']) : '';
    $Latitude = isset($_REQUEST['Latitude']) ? clean($_REQUEST['Latitude']) : '';
    $Longitude = isset($_REQUEST['Longitude']) ? clean($_REQUEST['Longitude']) : '';

    $sql = "SELECT vCompany  FROM company WHERE iCompanyId=$iCompanyId";
    $result_data = $obj->MySQLSelect($sql);

    $where = " iCompanyId = '" . $iCompanyId . "'";
    $Data_company['vRestuarantLocation'] = $Address;
    $Data_company['vRestuarantLocationLat'] = $Latitude;
    $Data_company['vRestuarantLocationLong'] = $Longitude;

    if (count($result_data) > 0 && $Address != "" && $Latitude != "" && $Longitude != "") {
        $id = $obj->MySQLQueryPerform("company", $Data_company, 'update', $where);

        $returnArr['Action'] = "1";
        $returnArr['restaurantAddressAdded'] = "Yes";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['restaurantAddressAdded'] = "No";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == 'getReceipt') {
    $iTripId = isset($_REQUEST['iTripId']) ? clean($_REQUEST['iTripId']) : '';
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : ''; //Passenger OR Driver
    $value = sendTripReceipt($iTripId);
    if ($value == true || $value == "true" || $value == "1") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_CHECK_INBOX_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_RECEIPT_EMAIL_TXT";
    }



    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "cancelCabRequest") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';
    if ($iCabRequestId == "") {

        // $data = get_value('cab_request_now', 'max(iCabRequestId),eStatus', 'iUserId',$iUserId);
        $sql = "SELECT iCabRequestId, eStatus FROM cab_request_now WHERE iUserId='" . $iUserId . "' ORDER BY iCabRequestId DESC LIMIT 1 ";
        $data = $obj->MySQLSelect($sql);
        $iCabRequestId = $data[0]['iCabRequestId'];
        $eStatus = $data[0]['eStatus'];
    } else {
        $data = get_value('cab_request_now', 'eStatus', 'iCabRequestId', $iCabRequestId, '', 'true');
        $eStatus = $data[0]['eStatus'];
    }

    if ($eStatus == "Requesting") {
        $where = " iCabRequestId='$iCabRequestId'";
        $Data_update_cab_request['eStatus'] = "Cancelled";
        $id = $obj->MySQLQueryPerform("cab_request_now", $Data_update_cab_request, 'update', $where);
        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "DO_RESET";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_REQUEST_CANCEL_FAILED_TXT";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "DO_RESTART";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "sendRequestToDrivers") {
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $eDriverType = isset($_REQUEST["eDriverType"]) ? trim($_REQUEST["eDriverType"]) : '';
    $trip_status = "Requesting";
    $checkOrderRequestStatusArr = checkOrderRequestStatus($iOrderId);
    $action = $checkOrderRequestStatusArr['Action'];
    if ($action == 0) {
        setDataResponse($checkOrderRequestStatusArr);
    }
    $sql = "select * from orders WHERE iOrderId='" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);
    // checkmemberemailphoneverification($passengerId,"Passenger");
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];
    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,eDriverOption";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    if ($eDriverType == "") {
        $eDriverType = $Data_cab_requestcompany[0]['eDriverOption'];
    }
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);
    // echo "<pre>";print_r($UserSelectedAddressArr);exit;
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
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
    $address_data['eDriverType'] = $eDriverType;
    $address_data['iCompanyId'] = $iCompanyId;

    $DataArr = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude, $address_data, "Yes", "No", "No", "", $DestLatitude, $DestLongitude, $iUserId);
    //echo "<pre>";print_r($DataArr);die;
    $Data = $DataArr['DriverList'];
    $driver_id_auto = $DataArr['driver_id_auto'];

    $fWalletDebit = $db_order[0]['fWalletDebit'];
    $fNetTotal = $db_order[0]['fNetTotal'];
    $isFullWalletCharge = "No";
    if ($fWalletDebit > 0 && $fNetTotal == 0) {
        $isFullWalletCharge = "Yes";
    }
    //echo $isFullWalletCharge;die;
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
        $driver_id_auto = "";
        for ($j = 0; $j < count($Data); $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        //$driver_id_auto = substr($driver_id_auto, 0, -1);
        $driver_id_auto = trim($driver_id_auto, ",");
    }

    // # Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    // echo "<pre>";print_r($Data);exit;
    $sqlp = "SELECT iGcmRegId,vCompany,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);

    // $iGcmRegId=get_value('register_user', 'iGcmRegId', 'iUserId',$passengerId,'','true');
    $iGcmRegId = $passengerData[0]['iGcmRegId'];
    if ($vDeviceToken != "" && $vDeviceToken != $iGcmRegId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        setDataResponse($returnArr);
    }

    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($iUserId);
    $final_message['iCompanyId'] = strval($iCompanyId);
    $final_message['iOrderId'] = strval($iOrderId);
    $passengerFName = $passengerData[0]['vCompany'];
    $final_message['PName'] = $passengerFName;
    $final_message['PPicName'] = $passengerData[0]['vImgName'];
    $final_message['PRating'] = $passengerData[0]['vAvgRating'];
    $final_message['PPhone'] = $passengerData[0]['vPhone'];
    $final_message['PPhoneC'] = $passengerData[0]['vPhoneCode'];
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

//echo "Res:count:".count($result)."vvvvvv".$driver_id_auto."ggggg".count($Data);exit;
    if (count($result) == 0 || $driver_id_auto == "" || count($Data) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "NO_CARS";
        setDataResponse($returnArr);
    }

    // $where = " iUserId = '$passengerId'";
    $where = "";


    $alertSendAllowed = true;

    ### GCM ####
    if ($alertSendAllowed == true) {
        $deviceTokens_arr_ios = $registation_ids_new = $alertMsg_arr_ios = $msg_encode_ios = array();
        foreach ($result as $item) {
            //$alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $item['vLang'] . "'", 'true'); // Commented By HJ On 11-01-2019 As Per Discuss With KS Sir Then Added Below Code FOr Get Label Value
            //Added By HJ On 11-01-2019 For Get Language Label Value Start
            $alertMsg_db = $languageLabelsArr['LBL_TRIP_USER_WAITING_DL'];
            if ($alertMsg_db == "") {
                $alertMsg_db = "Restaurant is waiting for you";
            }
            //Added By HJ On 11-01-2019 For Get Language Label Value End
            $tSessionId = $item['tSessionId'];
            $final_message['tSessionId'] = $tSessionId;
            $final_message['vTitle'] = $alertMsg_db;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);

            if ($item['eDeviceType'] == "Android") {
                array_push($registation_ids_new, $item['iGcmRegId']);
            } else {
                array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
                array_push($alertMsg_arr_ios, $alertMsg_db);
                array_push($msg_encode_ios, $msg_encode);
            }

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
            $final_message['tSessionId'] = "";
            $final_message['vTitle'] = $alertMsg;
            $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);
            // $Rmessage = array("message" => $message);
            $Rmessage = array(
                "message" => $msg_encode
            );
            $result = send_notification($registation_ids_new, $Rmessage, 0);
        }
        if (count($deviceTokens_arr_ios) > 0) {
            sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode_ios, $alertMsg_arr_ios, 0);
        }
    }
    ### GCM ####
    //sleep(3);
    // if($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
    // $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
    /* $pubnub = new Pubnub\Pubnub(array(
      "publish_key" => $PUBNUB_PUBLISH_KEY,
      "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
      "uuid" => $uuid
      )); */
    $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
    $driverIds_arr = explode(",", $filter_driver_ids);
    $message = stripslashes(preg_replace("/[\n\r]/", "", $message));
    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();

    $IOS_data_pubsub = array();
    $IOS_data_Count = 0;

    $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
    $destLoc = $DestLatitude . ',' . $DestLongitude;
    for ($i = 0; $i < count($driverIds_arr); $i++) {
        $sqld = "SELECT iAppVersion,eDeviceType,iGcmRegId,tSessionId,vLang FROM register_driver WHERE iDriverId = '" . $driverIds_arr[$i] . "'";
        $driverTripData = $obj->MySQLSelect($sqld);
        $iAppVersion = $driverTripData[0]['iAppVersion'];
        $eDeviceType = $driverTripData[0]['eDeviceType'];
        $vDeviceToken = $driverTripData[0]['iGcmRegId'];
        $tSessionId = $driverTripData[0]['tSessionId'];
        $vLang = $driverTripData[0]['vLang'];
        /* For PubNub Setting Finished */
        $final_message['tSessionId'] = $tSessionId;
        //$alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $vLang . "'", 'true');
        $alertMsg_db = $languageLabelsArr['LBL_TRIP_USER_WAITING_DL'];
        //echo "<pre>";
        //print_r($languageLabelsArr);die;
        $final_message['vTitle'] = $alertMsg_db;
        $msg_encode_pub = json_encode($final_message, JSON_UNESCAPED_UNICODE);
        $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$i];

        if ($eDeviceType == "Android") {
            publishEventMessage($channelName, $msg_encode_pub);
        } else {
            $IOS_data_pubsub[$IOS_data_Count]['ChannelName'] = $channelName;
            $IOS_data_pubsub[$IOS_data_Count]['PublishMsg'] = $msg_encode_pub;
            $IOS_data_Count++;
        }
    }

    if (count($IOS_data_pubsub) > 0) {
        sleep(3);
        for ($i = 0; $i < count($IOS_data_pubsub); $i++) {
            publishEventMessage($IOS_data_pubsub[$i]['ChannelName'], $IOS_data_pubsub[$i]['PublishMsg']);
        }
    }

    // }
    $returnArr['Action'] = "1";

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "cancelTrip") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $driverComment = isset($_REQUEST["Comment"]) ? $_REQUEST["Comment"] : '';
    $driverReason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';
    //echo $driverComment; exit;
    if ($userType != "Driver") {
        $vTripStatus = get_value('register_user', 'vTripStatus', 'iUserId', $iUserId, '', 'true');
        if ($vTripStatus != "Cancelled" && $vTripStatus != "Active" && $vTripStatus != "Arrived") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "DO_RESTART";
            setDataResponse($returnArr);
        }
    }

    $tripCancelData = get_value('trips AS tr LEFT JOIN vehicle_type AS vt ON vt.iVehicleTypeId=tr.iVehicleTypeId', 'tr.vCouponCode,tr.vTripPaymentMode,tr.iUserId,tr.iFare,tr.vRideNo,tr.tTripRequestDate,vt.fCancellationFare,vt.iCancellationTimeLimit', 'iTripId', $iTripId);
    $currentDate = @date("Y-m-d H:i:s");
    $tTripRequestDate = $tripCancelData[0]['tTripRequestDate'];
    $fCancellationFare = 0;
    $eCancelChargeFailed = "No";
    $totalMinute = @round(abs(strtotime($currentDate) - strtotime($tTripRequestDate)) / 60, 2);
    if ($totalMinute >= $tripCancelData[0]['iCancellationTimeLimit'] && $userType != "Driver") {
        $fCancellationFare = $tripCancelData[0]['fCancellationFare'];
        $vTripPaymentMode = $tripCancelData[0]['vTripPaymentMode'];
        if ($vTripPaymentMode == "Card" && $fCancellationFare > 0) {
            $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $tripCancelData[0]['iUserId'], '', 'true');
            $currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
            $price_new = $fCancellationFare * 100;
            $description = "Payment received for cancelled trip number:" . $tripCancelData[0]['vRideNo'];
            try {
                if ($fCancellationFare > 0) {
                    $charge_create = Stripe_Charge::create(array(
                                "amount" => $price_new,
                                "currency" => $currency,
                                "customer" => $vStripeCusId,
                                "description" => $description
                    ));
                    $details = json_decode($charge_create);
                    $result = get_object_vars($details);
                    if ($fCancellationFare == 0 || ($result['status'] == "succeeded" && $result['paid'] == "1")) {
                        $pay_data['tPaymentUserID'] = $result['id'];
                        $pay_data['vPaymentUserStatus'] = "approved";
                        $pay_data['iTripId'] = $iTripId;
                        $pay_data['iAmountUser'] = $fCancellationFare;
                        $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                    } else {
                        $eCancelChargeFailed = 'Yes';
                    }
                }
            } catch (Exception $e) {
                $error3 = $e->getMessage();
                $eCancelChargeFailed = 'Yes';
            }
        }
    }

    $active_status = "Canceled";
    if ($userType != "Driver") {
        $message = "TripCancelled";
    } else {
        $message = "TripCancelledByDriver";
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
    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo, tr.eType FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
    $result = $obj->MySQLSelect($sql);
    /* For PubNub Setting */
    $tableName = $userType != "Driver" ? "register_driver" : "register_user";
    $iMemberId_VALUE = $userType != "Driver" ? $iDriverId : $iUserId;
    $iMemberId_KEY = $userType != "Driver" ? "iDriverId" : "iUserId";
    /* $iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
      $eLogout=get_value($tableName, 'eLogout', $iMemberId_KEY,$iMemberId_VALUE,'','true');
      $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
    $AppData = get_value($tableName, 'iAppVersion,eDeviceType,eLogout,vLang', $iMemberId_KEY, $iMemberId_VALUE);
    $iAppVersion = $AppData[0]['iAppVersion'];
    $eLogout = $AppData[0]['eLogout'];
    $eDeviceType = $AppData[0]['eDeviceType'];
    /* For PubNub Setting Finished */
    /* $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
      $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
      $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
      $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY"); */
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }

    $alertMsg = "Trip canceled";

    // $vLangCode=get_value($tableName, 'vLang', $iMemberId_KEY,$iMemberId_VALUE,'','true');
    $vLangCode = $AppData[0]['vLang'];
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    if ($userType == "Driver") {
        $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_TRIP_CANCEL_DRIVER'] . ' ' . $driverReason . ' ' . $languageLabelsArr['LBL_CANCEL_TRIP_BY_DRIVER_MSG_SUFFIX'];
    } else {
        $usercanceltriplabel = $languageLabelsArr['LBL_PASSENGER_CANCEL_TRIP_TXT'];
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
    $message_arr['eSystem'] = "DeliverAll";
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    // ####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $iDriverId;
    $DataTripMessages['iTripId'] = $iTripId;
    $DataTripMessages['iUserId'] = $iUserId;
    if ($userType != "Driver") {
        $DataTripMessages['eFromUserType'] = "Passenger";
        $DataTripMessages['eToUserType'] = "Driver";
    } else {
        $DataTripMessages['eFromUserType'] = "Driver";
        $DataTripMessages['eToUserType'] = "Passenger";
    }

    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');

    // ###############################################################
    $where = " iTripId = '$iTripId'";
    $Data_update_trips['iActive'] = $active_status;
    $Data_update_trips['tEndDate'] = @date("Y-m-d H:i:s");
    //if ($vTripPaymentMode == "Card" && $fCancellationFare > 0)
    if ($fCancellationFare > 0) {
        $Data_update_trips['eCancelChargeFailed'] = $eCancelChargeFailed;
        $Data_update_trips['fCancellationFare'] = $fCancellationFare;
    }

    $Data_update_trips['eCancelledBy'] = $userType;
    //if ($userType == "Driver") { // Commented By HJ On 26-03-2019 As Per Discuss With BM QA Bug - 6430
    $Data_update_trips['vCancelReason'] = $driverReason;
    $Data_update_trips['vCancelComment'] = $driverComment;
    $Data_update_trips['eCancelled'] = "Yes";
    //} // Commented By HJ On 26-03-2019 As Per Discuss With BM QA Bug - 6430
    $Data_update_trips['iCancelReasonId'] = $iCancelReasonId;
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);
    //update insurance log
    if (strtoupper($PACKAGE_TYPE) == "SHARK") {
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
    $where = " iUserId = '$iUserId'";
    /* $Data_update_passenger['vCallFromDriver'] = $statusUpdate_user;
      $Data_update_passenger['vTripStatus'] = $trip_status;
      $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where); */
    $where = " iDriverId='$iDriverId'";

    // $Data_update_driver['iTripId']=$statusUpdate_user;
    $Data_update_driver['vTripStatus'] = $trip_status;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    if ($userType != "Driver") {
        $sql = "SELECT iGcmRegId,eDeviceType,tLocationUpdateDate FROM register_driver WHERE iDriverId IN (" . $iDriverId . ")";
    } else {
        $sql = "SELECT iGcmRegId,eDeviceType,tLocationUpdateDate FROM register_user WHERE iUserId IN (" . $iUserId . ")";
    }

    $result = $obj->MySQLSelect($sql);
    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();
    foreach ($result as $item) {
        if ($item['eDeviceType'] == "Android") {
            array_push($registation_ids_new, $item['iGcmRegId']);
        } else {
            array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
        }
    }

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    if ($ENABLE_PUBNUB == "Yes" && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {

        // $message = $alertMsg;
        $tLocUpdateDate = date("Y-m-d H:i:s", strtotime($result[0]['tLocationUpdateDate']));
        if ($tLocUpdateDate < $compare_date) {
            $alertSendAllowed = true;
        }
    } else {
        $alertSendAllowed = true;
    }

    if ($eLogout == "Yes") {
        $alertSendAllowed = false;
    }

    // $alertSendAllowed = false;
    $alertSendAllowed = true;
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
            } else {
                sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0);
            }
        }
    }

    ### Code For Pubnub ###
    //sleep(3);
    // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""/*  && $iAppVersion > 1 && $eDeviceType == "Android" */){
    // $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
    /* $pubnub = new Pubnub\Pubnub(array(
      "publish_key" => $PUBNUB_PUBLISH_KEY,
      "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
      "uuid" => $uuid
      )); */
    if ($userType != "Driver") {
        $channelName = "DRIVER_" . $iDriverId;
        $tSessionId = get_value("register_driver", 'tSessionId', "iDriverId", $iDriverId, '', 'true');
    } else {
        $channelName = "PASSENGER_" . $iUserId;
        $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $iUserId, '', 'true');
    }

    $message_arr['tSessionId'] = $tSessionId;
    $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    if (count($deviceTokens_arr_ios) > 0) {
        sleep(3);
    }

    // $info = $pubnub->publish($channelName, $message_pub);
    /* if ($PUBNUB_DISABLED == "Yes") {
      publishEventMessage($channelName, $message_pub);
      } else {
      $info = $pubnub->publish($channelName, $message_pub);
      } */
    publishEventMessage($channelName, $message_pub);
    // }
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
    // getTripChatDetails($iTripId);
    $returnArr['Action'] = "1";

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "addDestination") {

    // $userId     = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $Latitude = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $Longitude = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';
    $Address = isset($_REQUEST["Address"]) ? $_REQUEST["Address"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';

    // $iDriverId     = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iTripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $eConfirmByUser = isset($_REQUEST['eConfirmByUser']) ? $_REQUEST['eConfirmByUser'] : 'No';
    $eTollConfirmByUser = isset($_REQUEST['eTollConfirmByUser']) ? $_REQUEST['eTollConfirmByUser'] : 'No';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';
    if ($eConfirmByUser == "" || $eConfirmByUser == NULL) {
        $eConfirmByUser = "No";
    }

    if ($eTollConfirmByUser == "" || $eTollConfirmByUser == NULL) {
        $eTollConfirmByUser = "No";
    }

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
    } else {
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

    $dropofflocationarr = array(
        $Latitude,
        $Longitude
    );
    $ChangeAddress = "No";
    $sql_trip = "SELECT iUserId,iDriverId,tStartLat,tStartLong,tEndLat as TripEndLat,tEndLong as TripEndLong,fPickUpPrice,fNightPrice,iVehicleTypeId from trips WHERE iTripId='" . $iTripId . "'";
    $data_trip = $obj->MySQLSelect($sql_trip);
    $userId = $data_trip[0]['iUserId'];
    $iDriverId = $data_trip[0]['iDriverId'];
    $TripEndLat = $data_trip[0]['TripEndLat'];
    $TripEndLong = $data_trip[0]['TripEndLong'];
    $tStartLat = $data_trip[0]['tStartLat'];
    $tStartLong = $data_trip[0]['tStartLong'];
    $fPickUpPrice = $data_trip[0]['fPickUpPrice'];
    $fNightPrice = $data_trip[0]['fNightPrice'];
    $iVehicleTypeId = $data_trip[0]['iVehicleTypeId'];
    if ($TripEndLat != "" && $TripEndLong != "") {
        $ChangeAddress = "Yes";
    }

    $allowed_ans = checkAllowedAreaNew($dropofflocationarr, "Yes");
    if ($allowed_ans == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";

        setDataResponse($returnArr);
    }

    if ($userType != "Driver") {

        // $sql = "SELECT ru.iTripId,tr.iDriverId,rd.vTripStatus as driverStatus,rd.iGcmRegId as regId,rd.eDeviceType as deviceType FROM register_user as ru,trips as tr,register_driver as rd WHERE ru.iUserId='$userId' AND tr.iTripId=ru.iTripId AND rd.iDriverId=tr.iDriverId";
        $sql = "SELECT rd.vTripStatus as driverStatus,rd.iGcmRegId as regId,rd.eDeviceType as deviceType,rd.vLatitude as tDriverLatitude,rd.vLongitude as tDriverLongitude FROM register_driver as rd WHERE rd.iDriverId='" . $iDriverId . "'";
    } else {

        // $sql = "SELECT rd.iTripId,rd.vTripStatus as driverStatus,ru.iGcmRegId as regId,ru.eDeviceType as deviceType FROM trips as tr,register_driver as rd ,register_user as ru WHERE ru.iUserId='$userId' AND rd.iDriverId='$iDriverId'";
        $sql = "SELECT rd.vTripStatus as driverStatus,ru.iGcmRegId as regId,ru.eDeviceType as deviceType,rd.vLatitude as tDriverLatitude,rd.vLongitude as tDriverLongitude FROM register_driver as rd ,register_user as ru WHERE ru.iUserId='$userId' AND rd.iDriverId='$iDriverId'";
    }

    $data = $obj->MySQLSelect($sql);
    if (count($data) > 0) {
        $driverStatus = $data[0]['driverStatus'];

        // ######## Checking For Flattrip #########
        $sourceLocationArr = array(
            $tStartLat,
            $tStartLong
        );
        $destinationLocationArr = array(
            $Latitude,
            $Longitude
        );
        $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $iVehicleTypeId);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
        if ($eFlatTrip == "Yes") {
            $data_surgePrice = checkSurgePrice($iVehicleTypeId, "");
            $SurgePriceValue = 1;
            $SurgePrice = "";
            if ($data_surgePrice['Action'] == "0") {
                if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                    $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
                } else {
                    $fNightPrice = $data_surgePrice['SurgePriceValue'];
                }

                $SurgePriceValue = $data_surgePrice['SurgePriceValue'];
                $SurgePrice = $data_surgePrice['SurgePrice'];
            }

            if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
                $fPickUpPrice = 1;
                $fNightPrice = 1;
                $SurgePriceValue = 1;
                $SurgePrice = "";
            }

            if ($eConfirmByUser == "No" && $eFlatTrip == "Yes") {
                $TripPrice = round($fFlatTripPrice * $priceRatio, 2);

                // $fSurgePriceDiff = round(($TripPrice * $SurgePriceValue) - $TripPrice, 2);
                // $TripPrice = $TripPrice+$fSurgePriceDiff;
                $returnArr['Action'] = "0";
                $returnArr['message'] = "Yes";
                $returnArr['eFlatTrip'] = $eFlatTrip;
                $returnArr['SurgePrice'] = ""; // $SurgePrice
                $returnArr['SurgePriceValue'] = ""; // $SurgePriceValue
                $returnArr['fFlatTripPrice'] = $TripPrice;
                if ($SurgePriceValue > 1) {
                    $returnArr['fFlatTripPricewithsymbol'] = $currencySymbol . " " . $TripPrice . " (" . $LBL_AT_TXT . " " . $SurgePrice . ")";
                } else {
                    $returnArr['fFlatTripPricewithsymbol'] = $currencySymbol . " " . $TripPrice;
                }

                setDataResponse($returnArr);
            }

            $Data_trips['fTollPrice'] = "0";
            $Data_trips['vTollPriceCurrencyCode'] = "";
            $Data_trips['eTollSkipped'] = "No";
        } else {
            $eFlatTrip = "No";
            $fFlatTripPrice = 0;

            // ######## Checking For TollPrice #########
            /* if($eTollSkipped=='No' || ($fTollPrice != "" && $fTollPrice > 0))
              {
              $fTollPrice_Original = $fTollPrice;
              $vTollPriceCurrencyCode = strtoupper($vTollPriceCurrencyCode);
              $default_currency = get_value('currency', 'vName', 'eDefault', 'Yes','','true');
              $sql=" SELECT round(($fTollPrice/(SELECT Ratio FROM currency where vName='".$vTollPriceCurrencyCode."'))*(SELECT Ratio FROM currency where vName='".$default_currency."' ) ,2)  as price FROM currency  limit 1";
              $result_toll = $obj->MySQLSelect($sql);
              $fTollPrice = $result_toll[0]['price'];
              if($fTollPrice == 0){
              $fTollPrice = get_currency($vTollPriceCurrencyCode,$default_currency,$fTollPrice_Original);
              }

              $Data_trips['fTollPrice']=$fTollPrice;
              $Data_trips['vTollPriceCurrencyCode']=$vTollPriceCurrencyCode;
              $Data_trips['eTollSkipped']=$eTollSkipped;
              if($eTollConfirmByUser == "No"  && $fTollPrice > 0){
              $returnArr['Action']="0";
              setDataResponse($returnArr);

              }
              }else{
              $Data_trips['fTollPrice']="0";
              $Data_trips['vTollPriceCurrencyCode']="";
              $Data_trips['eTollSkipped']="No";
              } */

            // ######## Checking For TollPrice #########
        }

        // ######## Checking For Flattrip #########
        $where_trip = " iTripId = '" . $iTripId . "'";
        $Data_trips['tEndLat'] = $Latitude;
        $Data_trips['tEndLong'] = $Longitude;
        $Data_trips['tDaddress'] = $Address;
        $Data_trips['eFlatTrip'] = $eFlatTrip;
        $Data_trips['fFlatTripPrice'] = $fFlatTripPrice;
        $Data_trips['fPickUpPrice'] = $fPickUpPrice;
        $Data_trips['fNightPrice'] = $fNightPrice;
        $id = $obj->MySQLQueryPerform("trips", $Data_trips, 'update', $where_trip);

        // # Insert Into trip Destination ###
        $Data_trip_destination['iTripId'] = $iTripId;
        $Data_trip_destination['tDaddress'] = $Address;
        $Data_trip_destination['tEndLat'] = $Latitude;
        $Data_trip_destination['tEndLong'] = $Longitude;
        $Data_trip_destination['tDriverLatitude'] = $data[0]['tDriverLatitude'];
        $Data_trip_destination['tDriverLongitude'] = $data[0]['tDriverLongitude'];
        $Data_trip_destination['eUserType'] = $userType;
        $Data_trip_destination['dAddedDate'] = @date("Y-m-d H:i:s");
        $Data_trip_destination_id = $obj->MySQLQueryPerform('trip_destinations', $Data_trip_destination, 'insert');

        // # Insert Into trip Destination ###
        if ($driverStatus == "Active") {
            $where_passenger = " iUserId = '$userId'";
            $Data_passenger['tDestinationLatitude'] = $Latitude;
            $Data_passenger['tDestinationLongitude'] = $Longitude;
            $Data_passenger['tDestinationAddress'] = $Address;
            $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where_passenger);
        } else {
            /* $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
              $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
              $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
              $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY"); */
            if ($PUBNUB_DISABLED == "Yes") {
                $ENABLE_PUBNUB = "No";
            }

            /* if($userType !="Driver"){
              $alertMsg = "Destination is added by passenger.";
              }else{
              $alertMsg = "Destination is added by driver.";
              } */
            /* For PubNub Setting */
            $tableName = $userType != "Driver" ? "register_driver" : "register_user";
            $iMemberId_VALUE = $userType != "Driver" ? $iDriverId : $userId;
            $iMemberId_KEY = $userType != "Driver" ? "iDriverId" : "iUserId";
            /* $iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
              $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType,vLang,tSessionId', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            $tSessionId = $AppData[0]['tSessionId'];
            /* For PubNub Setting Finished */

            // $vLangCode=get_value($tableName, 'vLang', $iMemberId_KEY,$iMemberId_VALUE,'','true');
            $vLangCode = $AppData[0]['vLang'];
            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }

            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
            if ($ChangeAddress == "No") {
                $lblValue = $userType == "Driver" ? "LBL_DEST_ADD_BY_DRIVER" : "LBL_DEST_ADD_BY_PASSENGER";
            } else {
                $lblValue = $userType == "Driver" ? "LBL_DEST_EDIT_BY_DRIVER" : "LBL_DEST_EDIT_BY_PASSENGER";
            }

            $alertMsg = $languageLabelsArr[$lblValue];
            $message = "DestinationAdded";
            $message_arr = array();
            $message_arr['Message'] = $message;
            $message_arr['DLatitude'] = $Latitude;
            $message_arr['DLongitude'] = $Longitude;
            $message_arr['DAddress'] = $Address;
            $message_arr['vTitle'] = $alertMsg;
            $message_arr['iTripId'] = $iTripId;
            $message_arr['iDriverId'] = $iDriverId;
            $message_arr['eType'] = $APP_TYPE;
            $message_arr['eFlatTrip'] = $eFlatTrip;
            $message_arr['time'] = strval(time());
            $message_arr['eSystem'] = "DeliverAll";
            $message = json_encode($message_arr);
            $alertSendAllowed = true;

            // ####################Add Status Message#########################
            $DataTripMessages['tMessage'] = $message;
            $DataTripMessages['iDriverId'] = $iDriverId;
            $DataTripMessages['iTripId'] = $iTripId;
            $DataTripMessages['iUserId'] = $userId;
            if ($userType != "Driver") {
                $DataTripMessages['eFromUserType'] = "Passenger";
                $DataTripMessages['eToUserType'] = "Driver";
            } else {
                $DataTripMessages['eFromUserType'] = "Driver";
                $DataTripMessages['eToUserType'] = "Passenger";
            }

            $DataTripMessages['eReceived'] = "No";
            $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
            $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');

            // ###############################################################
            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();
            if ($alertSendAllowed == true) {
                if ($data[0]['deviceType'] == "Android" /* && $ENABLE_PUBNUB != "Yes" */) {
                    array_push($registation_ids_new, $data[0]['regId']);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                } else if ($data[0]['deviceType'] != "Android") {
                    array_push($deviceTokens_arr_ios, $data[0]['regId']);
                    /* if($ENABLE_PUBNUB == "Yes"){
                      $message = "";
                      } */
                    if ($message != "") {
                        if ($userType == "Driver") {
                            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                        } else {
                            sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                        }
                    }
                }
            }
            //sleep(3);
            ##### Pubnub Notification ######
            // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""/*  && $iAppVersion > 1 && $eDeviceType == "Android" */){
            // $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
            /* $pubnub = new Pubnub\Pubnub(array(
              "publish_key" => $PUBNUB_PUBLISH_KEY,
              "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
              "uuid" => $uuid
              )); */
            if ($userType != "Driver") {
                $channelName = "DRIVER_" . $iDriverId;

                // $tSessionId=get_value("register_driver", 'tSessionId', "iDriverId",$iDriverId,'','true');
            } else {
                $channelName = "PASSENGER_" . $userId;

                // $tSessionId=get_value("register_user", 'tSessionId', "iUserId",$userId,'','true');
            }

            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

            if (count($deviceTokens_arr_ios) > 0) {
                sleep(3);
            }

            // $info = $pubnub->publish($channelName, $message_pub);
            /* if ($PUBNUB_DISABLED == "Yes") {
              publishEventMessage($channelName, $message_pub);
              } else {
              $info = $pubnub->publish($channelName, $message_pub);
              } */
            publishEventMessage($channelName, $message_pub);
            // }
            ##### Pubnub Notification ######
        }

        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ##################### getAssignedDriverLocation ##########################
if ($type == "getDriverLocations") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $sql = "SELECT vLatitude, vLongitude,vTripStatus FROM `register_driver` WHERE iDriverId='$iDriverId'";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) == 1) {
        $returnArr['Action'] = "1";
        $returnArr['vLatitude'] = $Data[0]['vLatitude'];
        $returnArr['vLongitude'] = $Data[0]['vLongitude'];
        $returnArr['vTripStatus'] = $Data[0]['vTripStatus'];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'Not Found';
    }


    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == 'displayFare') {
    global $currency_supported_paypal, $generalobj;
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $tableName = $userType != "Driver" ? "register_user" : "register_driver";
    $iMemberId_KEY = $userType != "Driver" ? "iUserId" : "iDriverId";
    if ($iTripId == "") {
        $iTripId = get_value($tableName, 'iTripId', $iMemberId_KEY, $iMemberId, '', 'true');
    }

    // $ENABLE_TIP_MODULE=$generalobj->getConfigurations("configurations","ENABLE_TIP_MODULE");
    $vTripPaymentMode = get_value('trips', 'vTripPaymentMode', 'iTripId', $iTripId, '', 'true');
    if ($vTripPaymentMode == "Card") {
        $result_fare['ENABLE_TIP_MODULE'] = $ENABLE_TIP_MODULE;
    } else {
        $result_fare['ENABLE_TIP_MODULE'] = "No";
    }

    $result_fare['FormattedTripDate'] = date('dS M Y \a\t h:i a', strtotime($result_fare[0]['tStartDate']));
    $result_fare['PayPalConfiguration'] = "No";
    $result_fare['DefaultCurrencyCode'] = "USD";
    $result_fare['PaypalFare'] = strval($result_fare[0]['TotalFare']);
    $result_fare['PaypalCurrencyCode'] = $vCurrencyCode;

    // $result_fare['APP_TYPE'] = $generalobj->getConfigurations("configurations","APP_TYPE");
    $result_fare['APP_TYPE'] = $APP_TYPE;
    /* if($result_fare['APP_TYPE'] == "UberX"){
      $result_fare['APP_DESTINATION_MODE'] = "None";
      }else{
      $result_fare['APP_DESTINATION_MODE'] = "Strict";
      } */
    $result_fare['APP_DESTINATION_MODE'] = $APP_DESTINATION_MODE;

    // $result_fare['APP_DESTINATION_MODE'] = $generalobj->getConfigurations("configurations","APP_DESTINATION_MODE");
    $returnArr = gettrippricedetails($iTripId, $iMemberId, $userType, "DISPLAY");
    $result_fare = array_merge($result_fare, $returnArr);
    if (count($returnArr) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $result_fare;
    } else {
        $returnArr['Action'] = "0";
    }

    // echo "<pre>" ; print_r($returnArr); exit;

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "submitRating") {

    // $iGeneralUserId = isset($_REQUEST["iGeneralUserId"]) ? $_REQUEST["iGeneralUserId"] : ''; // for both driver or passenger
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : ''; // for both driver or passenger
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $rating = isset($_REQUEST["rating"]) ? $_REQUEST["rating"] : '';
    $message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : '';
    $rating1 = isset($_REQUEST["rating1"]) ? $_REQUEST["rating1"] : '';
    $message1 = isset($_REQUEST["message1"]) ? $_REQUEST["message1"] : '';
    $eFromUserType = isset($_REQUEST["eFromUserType"]) ? $_REQUEST["eFromUserType"] : 'Passenger'; // Passenger or Driver
    $eToUserType = isset($_REQUEST["eToUserType"]) ? $_REQUEST["eToUserType"] : 'Company'; // Passenger or Driver
    $message = stripslashes($message);
    $iMemberProfileId = $iMemberId;

    $sql = "SELECT * FROM `ratings_user_driver` WHERE iOrderId = '$iOrderId' and eFromUserType = '$eFromUserType' AND eToUserType = '$eToUserType'";
    $row_check = $obj->MySQLSelect($sql);
    if (count($row_check) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_TRIP_FINISHED_TXT_DL";
        setDataResponse($returnArr);
    } else {
        if ($eFromUserType == "Passenger") {
            $OrderData = get_value('orders', 'iDriverId,iCompanyId', 'iOrderId', $iOrderId);
            $iDriverId = $OrderData[0]['iDriverId'];
            $iCompanyId = $OrderData[0]['iCompanyId'];
            $tableName = "register_driver";
            $where = "iDriverId='" . $iDriverId . "'";
            $iMemberId = $iDriverId;
            $tableName1 = "company";
            $where1 = "iCompanyId='" . $iCompanyId . "'";
            $iMemberId1 = $iCompanyId;
            /* Insert records into ratings table */
            $Data_update_ratings['iTripId'] = $tripID;
            $Data_update_ratings['iOrderId'] = $iOrderId;
            $Data_update_ratings['vRating1'] = $rating;
            $Data_update_ratings['vMessage'] = $message;
            $Data_update_ratings['eFromUserType'] = $eFromUserType;
            $Data_update_ratings['eToUserType'] = $eToUserType;
            $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
            $Data_update['vAvgRating'] = getUserRatingAverage($iMemberId1, "Company");
            $Company_Rating_id = $obj->MySQLQueryPerform($tableName1, $Data_update, 'update', $where1);
            $Data_update_ratings1['iOrderId'] = $iOrderId;
            $Data_update_ratings1['vRating1'] = $rating1;
            $Data_update_ratings1['vMessage'] = $message1;
            $Data_update_ratings1['eFromUserType'] = $eFromUserType;
            $Data_update_ratings1['eToUserType'] = "Driver";
            $Driver_Rating_insert_id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings1, 'insert');
            $Data_update1['vAvgRating'] = getUserRatingAverage($iMemberId, $eFromUserType);
            $Driver_Rating_update_id = $obj->MySQLQueryPerform($tableName, $Data_update1, 'update', $where);
        } else {
            $iUserId = get_value('orders', 'iUserId', 'iOrderId', $iOrderId, '', 'true');
            $tableName = "register_user";
            $where = "iUserId='" . $iUserId . "'";
            $iMemberId = $iUserId;
            /* Insert records into ratings table */
            $Data_update_ratings['iTripId'] = $tripID;
            $Data_update_ratings['iOrderId'] = $iOrderId;
            $Data_update_ratings['vRating1'] = $rating;
            $Data_update_ratings['vMessage'] = $message;
            $Data_update_ratings['eFromUserType'] = $eFromUserType;
            $Data_update_ratings['eToUserType'] = $eToUserType;
            $id = $obj->MySQLQueryPerform("ratings_user_driver", $Data_update_ratings, 'insert');
            $Data_update['vAvgRating'] = getUserRatingAverage($iMemberId, $eFromUserType);
            $Passenger_Rating_update_id = $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);
        }

        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_TRIP_FINISHED_TXT_DL";
            if ($eFromUserType == "Passenger") {
                $returnArr['message1'] = getPassengerDetailInfo($iMemberProfileId, "", "");
            } else {
                $returnArr['message1'] = getDriverDetailInfo($iMemberProfileId, "");
            }

            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }

        if ($eFromUserType == "Passenger") {

            // sendTripReceipt($tripID);
        } else {

            // sendTripReceiptAdmin($tripID);
        }
    }
}

// ##########################################################################
if ($type == "updatePassword") {
    $user_id = isset($_REQUEST["UserID"]) ? $_REQUEST["UserID"] : '';
    $Upass = isset($_REQUEST["pass"]) ? $_REQUEST["pass"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? clean($_REQUEST["UserType"]) : ''; // UserType = Driver/Passenger
    $CurrentPassword = isset($_REQUEST["CurrentPassword"]) ? $_REQUEST["CurrentPassword"] : '';
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vPassword = get_value('register_user', 'vPassword', 'iUserId', $user_id, '', 'true');
    } else if ($UserType == "Company") {
        $tblname = "company";
        $vPassword = get_value('company', 'vPassword', 'iCompanyId', $user_id, '', 'true');
    } else {
        $tblname = "register_driver";
        $vPassword = get_value('register_driver', 'vPassword', 'iDriverId', $user_id, '', 'true');
    }

    // Check For Valid password #
    if ($CurrentPassword != "") {
        $hash = $vPassword;
        $checkValidPass = $generalobj->check_password($CurrentPassword, $hash);
        if ($checkValidPass == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WRONG_PASSWORD";
            setDataResponse($returnArr);
        }
    }

    // Check For Valid password #
    // $updatedPassword = $generalobj->encrypt($Upass);
    $updatedPassword = $generalobj->encrypt_bycrypt($Upass);
    $Data_update_user['vPassword'] = $updatedPassword;
    if ($UserType == "Passenger") {
        $where = " iUserId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_user, 'update', $where);
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($user_id, "", "");

            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    } else if ($UserType == "Company") {
        $where = " iCompanyId = '$user_id'";
        $id = $obj->MySQLQueryPerform("company", $Data_update_user, 'update', $where);
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = getCompanyDetailInfo($user_id, "");
            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    } else {
        $where = " iDriverId = '$user_id'";
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_user, 'update', $where);
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = getDriverDetailInfo($user_id);
            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    }
}

// ###########################Send Sms Twilio####################################
if ($type == 'sendVerificationSMS') {
    $mobileNo = isset($_REQUEST['MobileNo']) ? clean($_REQUEST['MobileNo']) : '';
    $mobileNo = str_replace('+', '', $mobileNo);
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    $REQ_TYPE = isset($_REQUEST["REQ_TYPE"]) ? $_REQUEST['REQ_TYPE'] : '';
    if ($REQ_TYPE == "DO_EMAIL_PHONE_VERIFY" || $REQ_TYPE == "DO_PHONE_VERIFY") {
        CheckUserSmsLimit($iMemberId, $userType);
    }

    // $isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
    $isdCode = $SITE_ISD_CODE;

    // $toMobileNum= "+".$mobileNo;
    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
        $condfield = 'iUserId';
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    } else if ($userType == "Company") {
        $tblname = "company";
        $fields = 'iCompanyId, vPhone,vCode as vPhoneCode, vEmail, vCompany as vName';
        $condfield = 'iCompanyId';
        $vLangCode = get_value('company', 'vLang', 'iCompanyId', $iMemberId, '', 'true');
    } else {
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

    // $prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];
    $verificationCode_sms = generateCommonRandom();
    $verificationCode_email = generateCommonRandom();
    $message = $prefix . ' ' . $verificationCode_sms;
    if ($iMemberId == "" && $REQ_TYPE == "DO_PHONE_VERIFY") {
        $toMobileNum = "+" . $mobileNo;
    } else {
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

        /* $result = sendEmeSms($toMobileNum, $message);
          if ($result == 0) {
          $toMobileNum = "+" . $isdCode . $mobileNo;
          $result = sendEmeSms($toMobileNum, $message);
          } */
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
        if ($userType == "Passenger") {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iMemberId AND r.vCountry = c.vCountryCode");
        } else if ($userType == "Company") {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `company` AS r, `country` AS c WHERE r.iCompanyId = $iMemberId AND r.vCountry = c.vCountryCode");
        } else {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iMemberId AND r.vCountry = c.vCountryCode");
        }

        $PhoneCode = $passengerData[0]['vPhoneCode'];

        // $result = $generalobj->sendSystemSms($toMobileNum, $PhoneCode, $message); //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
		
		/********************** Firebase SMS Verfication **********************************/
		if(strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE"){
			$result = $generalobj->sendSystemSms($toMobileNum, $PhoneCode, $message);
		}else{
			$result = 1;
		}
		/********************** Firebase SMS Verfication **********************************/

        if ($result == 1) {
            UpdateUserSmsLimit($iMemberId, $userType);
        }

        $returnArr['Action'] = "1";
        if ($sendemail == 0 && $result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ACC_VERIFICATION_FAILED";
        } else {
            $returnArr['message_sms'] = $result == 0 ? "LBL_MOBILE_VERIFICATION_FAILED_TXT" : $verificationCode_sms;
            if ($returnArr['message_sms'] == "LBL_MOBILE_VERIFICATION_FAILED_TXT") {
                $returnArr['eSMSFailed'] = "Yes";
            } else {
                $returnArr['eSMSFailed'] = "No";
            }

            $returnArr['message_email'] = $sendemail == 0 ? "LBL_EMAIL_VERIFICATION_FAILED_TXT" : $verificationCode_email;
            if ($returnArr['message_email'] == "LBL_EMAIL_VERIFICATION_FAILED_TXT") {
                $returnArr['eEmailFailed'] = "Yes";
            } else {
                $returnArr['eEmailFailed'] = "No";
            }
        }

        setDataResponse($returnArr);
    } else if ($REQ_TYPE == "DO_PHONE_VERIFY") {
        /* $result = sendEmeSms($toMobileNum, $message);
          if ($result == 0) {
          $toMobileNum = "+" . $isdCode . $mobileNo;
          $result = sendEmeSms($toMobileNum, $message);
          } */
        //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
        if ($userType == "Passenger") {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iMemberId AND r.vCountry = c.vCountryCode");
        } else if ($userType == "Company") {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `company` AS r, `country` AS c WHERE r.iCompanyId = $iMemberId AND r.vCountry = c.vCountryCode");
        } else {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iMemberId AND r.vCountry = c.vCountryCode");
        }

        $PhoneCode = $passengerData[0]['vPhoneCode'];

        // $result = $generalobj->sendSystemSms($toMobileNum, $PhoneCode, $message); //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end
		
		/********************** Firebase SMS Verfication **********************************/
		if(strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE"){
			$result = $generalobj->sendSystemSms($toMobileNum, $PhoneCode, $message);
		}else{
			$result = 1;
		}
		/********************** Firebase SMS Verfication **********************************/

        if ($result == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_MOBILE_VERIFICATION_FAILED_TXT";
            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "1";
            $returnArr['message'] = strval($verificationCode_sms);
            UpdateUserSmsLimit($iMemberId, $userType);
            setDataResponse($returnArr);
        }
    } else if ($REQ_TYPE == "DO_EMAIL_VERIFY") {
        $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
        if ($sendemail != true || $sendemail != "true" || $sendemail != "1") {
            $sendemail = 0;
        }

        if ($sendemail == 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EMAIL_VERIFICATION_FAILED_TXT";
            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "1";
            $returnArr['message'] = strval($Data_Mail['CODE']);
            setDataResponse($returnArr);
        }
    } else if ($REQ_TYPE == "EMAIL_VERIFIED") {
        $where = " " . $condfield . " = '" . $iMemberId . "'";
        $Data['eEmailVerified'] = "Yes";
        $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);
        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_EMAIl_VERIFIED";
            if ($userType == 'Passenger') {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getPassengerDetailInfo($iMemberId, "", "");
            } else if ($userType == 'Company') {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getCompanyDetailInfo($iMemberId);
            } else {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getDriverDetailInfo($iMemberId);
            }

            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_EMAIl_VERIFIED_ERROR";
            setDataResponse($returnArr);
        }
    } else if ($REQ_TYPE == "PHONE_VERIFIED") {
        $where = " " . $condfield . " = '" . $iMemberId . "'";
        $Data['ePhoneVerified'] = "Yes";
        $id = $obj->MySQLQueryPerform($tblname, $Data, 'update', $where);
        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_PHONE_VERIFIED";
            if ($userType == 'Passenger') {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getPassengerDetailInfo($iMemberId, "", "");
            } else if ($userType == 'Company') {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getCompanyDetailInfo($iMemberId);
            } else {
                $returnArr['userDetails']['Action'] = "1";
                $returnArr['userDetails']['message'] = getDriverDetailInfo($iMemberId);
            }

            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_PHONE_VERIFIED_ERROR";
            setDataResponse($returnArr);
        }
    }

    //	$returnArr['message'] =$verificationCode;
    // setDataResponse($returnArr);
}

// ###########################Send Sms Twilio END################################
// ##########################################################################
if ($type == "updateDriverStatus") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Status_driver = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';
    $isUpdateOnlineDate = isset($_REQUEST["isUpdateOnlineDate"]) ? $_REQUEST["isUpdateOnlineDate"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';
    $iGCMregID = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    //echo "<pre>";print_r($_REQUEST);die;
    if ($PACKAGE_TYPE == "SHARK" && $Status_driver == "Available") {
        $BlockData = getBlockData("Driver", $iDriverId);
        if (!empty($BlockData) || $BlockData != "") {
            setDataResponse($BlockData);
        }
    }

    /* $sql = "SELECT eIsBlocked,iDriverId FROM register_driver WHERE iDriverId='$iDriverId' ";
      $Data_Driver = $obj->MySQLSelect($sql);
      $eIsBlocked = $Data_Driver[0]['eIsBlocked'];

      if ($eIsBlocked == 'Yes' && $Status_driver == "Available") {
      $returnArr['Action'] = "0";
      $returnArr['isShowContactUs'] = "Yes";
      $returnArr['message'] = "LBL_DRIVER_BLOCK";
      setDataResponse($returnArr);
      } */

    if ($Status_driver == "Available") {
        checkmemberemailphoneverification($iDriverId, "Driver");
    }
    if ($iDriverId == '') {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }

    $GCMID = get_value('register_driver', 'iGcmRegId', 'iDriverId', $iDriverId, '', 'true');
    if ($GCMID != "" && $iGCMregID != "" && $GCMID != $iGCMregID) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        setDataResponse($returnArr);
    }

    $returnArr['Enable_Hailtrip'] = "No";

    // $COMMISION_DEDUCT_ENABLE=$generalobj->getConfigurations("configurations","COMMISION_DEDUCT_ENABLE");
    // if($COMMISION_DEDUCT_ENABLE == 'Yes' && ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Cash-Card")) {
    if ($COMMISION_DEDUCT_ENABLE == 'Yes' && $Status_driver == "Available") {
        $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
        if ($vLang == "" || $vLang == NULL) {
            $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
        $driverDetail = get_value('register_driver AS rd LEFT JOIN currency AS c ON c.vName=rd.vCurrencyDriver', 'rd.vCurrencyDriver,c.Ratio,c.vSymbol', 'rd.iDriverId', $iDriverId);
        $vCurrencyDriver = $driverDetail[0]['vCurrencyDriver'];
        $ratio = $driverDetail[0]['Ratio'];
        $currencySymbol = $driverDetail[0]['vSymbol'];

        // $WALLET_MIN_BALANCE=$generalobj->getConfigurations("configurations","WALLET_MIN_BALANCE");
        if ($WALLET_MIN_BALANCE > $user_available_balance) {

            // $returnArr['Action'] = "0";
            $returnArr['message'] = "REQUIRED_MINIMUM_BALNCE";
            if ($APP_TYPE == "UberX") {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio), $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE_UBERX']);
            } else {
                $returnArr['Msg'] = str_replace('####', $currencySymbol . ($WALLET_MIN_BALANCE * $ratio), $languageLabelsArr['LBL_REQUIRED_MINIMUM_BALNCE']);
            }

            if ($APP_PAYMENT_MODE == "Cash") {
                if ($Status_driver == "Available") {
                    $returnArr['Action'] = "0";
                    setDataResponse($returnArr);
                }
            }
        }

        $returnArr['Enable_Hailtrip'] = "Yes";
    }

    if ($COMMISION_DEDUCT_ENABLE == 'No' && ($APP_PAYMENT_MODE == "Cash" || $APP_PAYMENT_MODE == "Cash-Card")) {
        $returnArr['Enable_Hailtrip'] = "Yes";
    }

    // getDriverStatus($iDriverId);
    // $APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    $ssql = "";
    $sql = "SELECT make.vMake, model.vTitle, dv.*, rd.iDriverVehicleId as iSelectedVehicleId FROM `driver_vehicle` dv, make, model, register_driver as rd WHERE dv.iDriverId='$iDriverId' AND rd.iDriverId='$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'" . $ssql;
    $Data_Car = $obj->MySQLSelect($sql);
    if (count($Data_Car) > 0) {
        $status = "CARS_NOT_ACTIVE";
        $i = 0;
        while (count($Data_Car) > $i) {
            $eStatus = $Data_Car[$i]['eStatus'];
            if ($eStatus == "Active") {
                $status = "CARS_AVAIL";
            }

            $i++;
        }

        if ($status == "CARS_AVAIL" && ($Data_Car[0]['iSelectedVehicleId'] == "0" || $Data_Car[0]['iSelectedVehicleId'] == "")) {

            // echo "SELECT_CAR";
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_SELECT_CAR_MESSAGE_TXT";
            setDataResponse($returnArr);
        } else if ($status == "CARS_NOT_ACTIVE") {

            // echo "CARS_NOT_ACTIVE";
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
            setDataResponse($returnArr);
        }
    } else if ($Status_driver == "Available") { // Added By HJ On 02-12-2019 For Solved Sheet Bug = 567 As Per Discuss With KS Sir
        // echo "NO_CARS_AVAIL";
        $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $iDriverId . "' AND ( eStatus = 'Inactive' OR eStatus = 'Deleted')";
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
        $returnArr['Action'] = "0";
        if ($TotalVehicles == 0) {
            $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
        } else {
            $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
        }
        setDataResponse($returnArr);
    }

    $where = " iDriverId='" . $iDriverId . "'";
    if ($Status_driver != '') {
        $Data_update_driver['vAvailability'] = $Status_driver;
    }

    if ($latitude_driver != '' && $longitude_driver != '') {
        $Data_update_driver['vLatitude'] = $latitude_driver;
        $Data_update_driver['vLongitude'] = $longitude_driver;
    }

    if ($Status_driver == "Available") {
        $Data_update_driver['tOnline'] = @date("Y-m-d H:i:s");

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
        if (strtoupper($PACKAGE_TYPE) == "SHARK") {
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
        if (strtoupper($PACKAGE_TYPE) == "SHARK") {
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

    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    // Update User Location Date #
    Updateuserlocationdatetime($iDriverId, "Driver", $vTimeZone);

    // Update User Location Date #
    if ($id) {
        $returnArr['Action'] = "1";
        setDataResponse($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}

// ##########################################################################
if ($type == "LoadAvailableCars") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';

    $ssql = " AND dv.eType != 'UberX'";

    $sql = "SELECT register_driver.iDriverVehicleId as DriverSelectedVehicleId,make.vMake, model.vTitle, dv.* FROM `driver_vehicle` dv, make, model,register_driver WHERE dv.iDriverId='$iDriverId' AND register_driver.iDriverId = '$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active' $ssql";


    $Data_Car = $obj->MySQLSelect($sql);

    if (count($Data_Car) > 0) {
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
        for ($i = 0; $i < count($Data_Car); $i++) {
            $vCarType = $Data_Car[$i]['vCarType'];
            $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
            $db_cartype = $obj->MySQLSelect($sql);
            $k = 0;
            if (count($db_cartype) > 0) {
                for ($j = 0; $j < count($db_cartype); $j++) {
                    $eType = $db_cartype[$j]['eType'];
                    if ($eType == "UberX") {

                        // unset($db_vehicle_new[$i]);
                    }
                }
            }
        }

        $db_vehicle_new = array_values($db_vehicle_new);

        // setDataResponse($returnArr);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle_new;
        setDataResponse($returnArr);
    } else {
        $sql = "SELECT count(iDriverVehicleId) as TotalVehicles from driver_vehicle WHERE iDriverId = '" . $driverId . "' AND ( eStatus = 'Inactive' OR eStatus = 'Deleted')";
        $db_Total_vehicle = $obj->MySQLSelect($sql);
        $TotalVehicles = $db_Total_vehicle[0]['TotalVehicles'];
        $returnArr['Action'] = "0";
        if ($TotalVehicles == 0) {
            $returnArr['message'] = "LBL_NO_CAR_AVAIL_TXT";
        } else {
            $returnArr['message'] = "LBL_INACTIVE_CARS_MESSAGE_TXT";
        }

        setDataResponse($returnArr);
    }
}

// ########################## Set Driver CarID ############################
if ($type == "SetDriverCarID") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Data['iDriverVehicleId'] = isset($_REQUEST["iDriverVehicleId"]) ? $_REQUEST["iDriverVehicleId"] : '';
    $where = " iDriverId = '" . $iDriverId . "'";
    $sql = $obj->MySQLQueryPerform("register_driver", $Data, 'update', $where);
    if ($sql > 0) {
        $returnArr['Action'] = "1";
        setDataResponse($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}

// ##########################################################################
if ($type == "GenerateTrip") {
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $Source_point_latitude = isset($_REQUEST["tSourceLat"]) ? $_REQUEST["tSourceLat"] : '';
    $Source_point_longitude = isset($_REQUEST["tSourceLong"]) ? $_REQUEST["tSourceLong"] : '';
    $Source_point_Address = isset($_REQUEST["tSourceAddress"]) ? $_REQUEST["tSourceAddress"] : '';
    $Dest_point_latitude = isset($_REQUEST["tDestLatitude"]) ? $_REQUEST["tDestLatitude"] : '';
    $Dest_point_longitude = isset($_REQUEST["tDestLongitude"]) ? $_REQUEST["tDestLongitude"] : '';
    $Dest_point_Address = isset($_REQUEST["tDestAddress"]) ? $_REQUEST["tDestAddress"] : '';
    $GoogleServerKey = isset($_REQUEST["GoogleServerKey"]) ? $_REQUEST["GoogleServerKey"] : '';
    $vMsgCode = isset($_REQUEST["vMsgCode"]) ? $_REQUEST["vMsgCode"] : '';
    $setCron = isset($_REQUEST["setCron"]) ? $_REQUEST["setCron"] : 'No';
    //Added By HJ On 13-02-2020 For Get Start and End Lat and Lang Data Start
    if ($Source_point_latitude == "") {
        $Source_point_latitude = isset($_REQUEST["start_lat"]) ? $_REQUEST["start_lat"] : '';
    }
    if ($Source_point_longitude == "") {
        $Source_point_longitude = isset($_REQUEST["start_lon"]) ? $_REQUEST["start_lon"] : '';
    }
    if ($Source_point_Address == "") {
        $Source_point_Address = isset($_REQUEST["sAddress"]) ? $_REQUEST["sAddress"] : '';
    }
    if ($Dest_point_latitude == "") {
        $Dest_point_latitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    }
    if ($Dest_point_longitude == "") {
        $Dest_point_longitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    }
    //Added By HJ On 13-02-2020 For Get Start and End Lat and Lang Data End
    if (isset($_REQUEST['test'])) {
        //echo $Source_point_Address."===".$Source_point_latitude."===".$Source_point_longitude."====".$Dest_point_latitude."===".$Dest_point_longitude;die;
    }
    if ($PACKAGE_TYPE == "SHARK") {
        $BlockData = getBlockData("Driver", $driver_id);
        if (!empty($BlockData) || $BlockData != "") {
            setDataResponse($BlockData);
        }
    }

    // $APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    if ($iDriverId > 0) {
        $sqldata = "SELECT iTripId FROM `trips` WHERE ( iActive='On Going Trip' OR iActive='Active' ) AND iDriverId='" . $iDriverId . "'";
        $TripData = $obj->MySQLSelect($sqldata);
        if (count($TripData) > 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_DRIVER_NOT_ACCEPT_TRIP";
            setDataResponse($returnArr);
        }
    }

    $sqld = "SELECT iTripId FROM `trips` WHERE iOrderId ='" . $iOrderId . "'";

    $TripOrderData = $obj->MySQLSelect($sqld);
    if (count($TripOrderData) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_SAME_ORDER_TRIP_EXIST_TXT";
        setDataResponse($returnArr);
    }
    //Added By HJ On 30-07-2019 For Check Driver Lock Start
    /* $isDriverLock = $generalobj->checkServiceLock($iDriverId, "", true, true);
      $fileName = "Driver_" . $iDriverId;
      $file_name = md5($fileName) . ".txt";
      $driverLockFilePath = $tconfig["tpanel_path"] . "webimages/lockFile/" . $file_name;
      if ($isDriverLock) {
      $returnArr['Action'] = "0";
      $returnArr['message'] = "LBL_DRIVER_NOT_ACCEPT_TRIP";
      //unLinkFile($driverLockFilePath);
      setDataResponse($returnArr);
      } */
    //Added By HJ On 30-07-2019 For Check Driver Lock End
    // ### Update Driver Request Status of Trip ####
    UpdateDriverRequest2($driver_id, $passenger_id, $iTripId, "", $vMsgCode, "Yes", $iOrderId);

    /*     * ******* Create Service Lock ********* */
    $isServiceLock = $generalobj->checkServiceLock($iOrderId, "", true, false);
    if ($isServiceLock) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_SAME_ORDER_TRIP_EXIST_TXT";
        //unLinkFile($driverLockFilePath);
        setDataResponse($returnArr);
    }
    /*     * ******* Create Service Lock ********* */

    /*     * ******* Create Service Lock Added By HJ On 10-07-2019 End********* */

    // ### Update Driver Request Status of Trip ####
    $sql = "select * from orders WHERE iOrderId='" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iServiceId = $db_order[0]['iServiceId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $vOrderNo = $db_order[0]['vOrderNo'];
    // payment method 2
    $ePayWallet = $db_order[0]['ePayWallet'];
    // payment method 2

    $DriverMessage = "CabRequestAccepted";
    $TripRideNO = GenerateUniqueTripNo();
    $TripVerificationCode = generateCommonRandom();
    $Active = "Active";
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $vGMapLangCode = get_value('language_master', 'vGMapLangCode', 'vCode', $vLangCode, '', 'true');
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $tripdriverarrivlbl = $languageLabelsArr['LBL_DRIVER_ARRIVING'];
    $reqestId = "";
    $trip_status_chkField = "iCabRequestId";
    if ($iOrderId > 0) {
        if ($iDriverId != "") {
            $where = " iOrderId = '$iOrderId'";
            $Data_update_order_driver['iDriverId'] = $iDriverId;
            $Data_update_order_driver['iStatusCode'] = "4";
            $obj->MySQLQueryPerform("orders", $Data_update_order_driver, 'update', $where);
            $Order_Status_id = createOrderLog($iOrderId, "4");
        }

        $sql = "SELECT vCurrencyPassenger,iAppVersion,iUserPetId FROM `register_user` WHERE iUserId = '$iUserId'";
        $Data_passenger_detail = $obj->MySQLSelect($sql);
        $sql = "select GROUP_CONCAT(iVehicleTypeId)as VehicleTypeId from `vehicle_type` where eType = 'DeliverAll'";
        $db_deliverall_vehicle = $obj->MySQLSelect($sql);
        $VehicleTypeId = $db_deliverall_vehicle[0]['VehicleTypeId'];
        $VehicleTypeIdArr = explode(",", $VehicleTypeId);
        $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,vName,vLastName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
        $Data_vehicle = $obj->MySQLSelect($sql);
        $CAR_id_driver = $Data_vehicle[0]['iDriverVehicleId'];
        $DriverCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $CAR_id_driver, '', 'true');
        $drivercartypeArr = explode(",", $DriverCarType);
        $vCarTypeArr = array_intersect($VehicleTypeIdArr, $drivercartypeArr);
        $vCarTypeArr = array_values($vCarTypeArr);
        $vCarType = $vCarTypeArr[0];
        $fDeliveryCharge = get_value('vehicle_type', 'fDeliveryChargeCancelOrder', 'iVehicleTypeId', $vCarType, '', 'true');
        $Data_trips['iOrderId'] = $iOrderId;
        $Data_trips['fDeliveryCharge'] = $fDeliveryCharge;
        $Data_trips['vRideNo'] = $TripRideNO;
        $Data_trips['iUserId'] = $iUserId;
        $Data_trips['iDriverId'] = $iDriverId;
        $Data_trips['iCompanyId'] = $iCompanyId;
        $Data_trips['iServiceId'] = $iServiceId;
        $Data_trips['tTripRequestDate'] = @date("Y-m-d H:i:s");
        $Data_trips['iDriverVehicleId'] = $CAR_id_driver;
        $Data_trips['tStartLat'] = $Source_point_latitude;
        $Data_trips['tStartLong'] = $Source_point_longitude;
        $Data_trips['tSaddress'] = $Source_point_Address;
        $Data_trips['tEndLat'] = $Dest_point_latitude;
        $Data_trips['tEndLong'] = $Dest_point_longitude;
        $Data_trips['tDaddress'] = $Dest_point_Address;
        $Data_trips['iActive'] = $Active;
        $Data_trips['iVerificationCode'] = $TripVerificationCode;
        $Data_trips['iVehicleTypeId'] = $vCarType;
        $Data_trips['vTripPaymentMode'] = $db_order[0]['ePaymentOption'];
        $Data_trips['fTripGenerateFare'] = $db_order[0]['fNetTotal'];
        $Data_trips['vCountryUnitRider'] = getMemberCountryUnit($iUserId, "Passenger");
        $Data_trips['vCountryUnitDriver'] = getMemberCountryUnit($iDriverId, "Driver");
        $Data_trips['vTimeZone'] = $vTimeZone;
        $Data_trips['iUserAddressId'] = $iUserAddressId;
        $Data_trips['eSystem'] = "DeliverAll";

        // payment method 2
        $Data_trips['ePayWallet'] = $ePayWallet;
        //payment method 2
        $currencyList = get_value('currency', '*', 'eStatus', 'Active');
        for ($i = 0; $i < count($currencyList); $i++) {
            $currencyCode = $currencyList[$i]['vName'];
            $Data_trips['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
        }

        $Data_trips['vCurrencyPassenger'] = $Data_passenger_detail[0]['vCurrencyPassenger'];
        $Data_trips['vCurrencyDriver'] = $Data_vehicle[0]['vCurrencyDriver'];
        $id = $obj->MySQLQueryPerform("trips", $Data_trips, 'insert');
        $iTripId = $id;
        $trip_status = "Active";

        // ### Update Driver Request Status of Trip ####
        UpdateDriverRequest2($iDriverId, $iUserId, $iTripId, "Accept", $vMsgCode, "No", $iOrderId);

        // ### Update Driver Request Status of Trip ####
        $where = " iUserId = '$iUserId'";
        /* $Data_update_passenger['iTripId'] = $iTripId;
          $Data_update_passenger['vTripStatus'] = $trip_status;
          $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where); */
        $where = " iDriverId = '$iDriverId'";
        $Data_update_driver['iTripId'] = $iTripId;
        $Data_update_driver['vTripStatus'] = $trip_status;
        $Data_update_driver['vRideCountry'] = $vRideCountry;
        $Data_update_driver['vAvailability'] = "Not Available";
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
        //update insurance log
        if (strtoupper($PACKAGE_TYPE) == "SHARK") {
            $details_arr['iTripId'] = $iTripId;
            $details_arr['LatLngArr']['vLatitude'] = $vLatitude;
            $details_arr['LatLngArr']['vLongitude'] = $vLongitude;
            // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
            update_driver_insurance_status($iDriverId, "Accept", $details_arr, "GenerateTrip");
        }
        //update insurance log
        /* if($eType == "Deliver"){
          $drivername = $Data_vehicle[0]['vName']." ".$Data_vehicle[0]['vLastName'];
          $tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_DRIVER_TXT']." ".$drivername." ".$languageLabelsArr['LBL_DRIVER_IS_ARRIVING'];
          } */
        $drivername = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
        $tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_EXECUTIVE_TXT'] . " " . $drivername . " " . $languageLabelsArr['LBL_DELIVERY_ON_WAY_TXT'] . " #" . $vOrderNo;
        $alertMsg = $tripdriverarrivlbl;
        $message_arr = array();
        $message_arr['iDriverId'] = $iDriverId;
        $message_arr['Message'] = $DriverMessage;
        $message_arr['iTripId'] = strval($iTripId);
        $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
        $message_arr['iTripVerificationCode'] = $TripVerificationCode;
        $message_arr['driverName'] = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];
        $message_arr['vRideNo'] = $TripRideNO;
        $message_arr['iOrderId'] = $iOrderId;
        $message_arr['vTitle'] = $alertMsg;
        $message_arr['eSystem'] = "DeliverAll";
        $message = json_encode($message_arr);

        // ####################Add Status Message#########################
        $DataTripMessages['tMessage'] = $message;
        $DataTripMessages['iDriverId'] = $iDriverId;
        $DataTripMessages['iTripId'] = $iTripId;
        $DataTripMessages['iOrderId'] = $iOrderId;
        $DataTripMessages['iUserId'] = $iUserId;
        $DataTripMessages['eFromUserType'] = "Driver";
        $DataTripMessages['eToUserType'] = "Passenger";
        $DataTripMessages['eReceived'] = "No";
        $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
        $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');

        // ###############################################################
        if ($iTripId > 0) {
            $alertSendAllowed = true;
            /* For PubNub Setting */
            $tableName = "register_user";
            $iMemberId_VALUE = $iUserId;
            $iMemberId_KEY = "iUserId";
            /* $iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
              $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            /* For PubNub Setting Finished */
            $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$iUserId'";
            $result = $obj->MySQLSelect($sql);
            $registatoin_ids = $result[0]['iGcmRegId'];
            $deviceTokens_arr_ios = $registation_ids_new = array();

            $sql = "SELECT iGcmRegId,eDeviceType,iAppVersion,tSessionId FROM company WHERE iCompanyId='$iCompanyId'";
            $result_company = $obj->MySQLSelect($sql);
            $registatoin_ids_company = $result_company[0]['iGcmRegId'];
            $deviceTokens_arr_ios_company = $registation_ids_new_company = array();
            if ($alertSendAllowed == true) {
                if ($result[0]['eDeviceType'] == "Android") {
                    array_push($registation_ids_new, $result[0]['iGcmRegId']);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                } else {

                    // $alertMsg = "Driver is arriving";
                    // $alertMsg = $tripdriverarrivlbl;
                    array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                    sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                }

                if ($result_company[0]['eDeviceType'] == "Android") {
                    array_push($registation_ids_new_company, $result_company[0]['iGcmRegId']);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $resultc = send_notification($registation_ids_new_company, $Rmessage, 0);
                } else {
                    array_push($deviceTokens_arr_ios_company, $result_company[0]['iGcmRegId']);
                    sendApplePushNotification(2, $deviceTokens_arr_ios_company, $message, $alertMsg, 0);
                }
            }

            $channelName = "PASSENGER_" . $iUserId;
            $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $iUserId, '', 'true');
            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

            if (count($deviceTokens_arr_ios) > 0 || count($deviceTokens_arr_ios_company) > 0) {
                sleep(3);
            }

            publishEventMessage($channelName, $message_pub);

            $channelName_company = "COMPANY_" . $iCompanyId;
            $message_arr['tSessionId'] = $result_company[0]['tSessionId'];
            $message_pub_company = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

            publishEventMessage($channelName_company, $message_pub_company);
            // $info_company = $pubnub->publish($channelName_company, $message_pub_company);
            // }
            /* else{
              $alertSendAllowed = true;
              } */
            ###### PUBNUB #########
            $returnArr['Action'] = "1";
            $data['iTripId'] = $iTripId;
            $data['tEndLat'] = $Dest_point_latitude;
            $data['tEndLong'] = $Dest_point_longitude;
            $data['tDaddress'] = $Dest_point_Address;
            $data['PAppVersion'] = $Data_passenger_detail[0]['iAppVersion'];
            $data['eFareType'] = $Data_trips['eFareType'];
            $data['vVehicleType'] = $eIconType;
            $returnArr['APP_TYPE'] = $APP_TYPE;
            $returnArr['message'] = $data;
            if ($iOrderId != "") {
                $passengerData = get_value('register_user', 'vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode,iAppVersion', 'iUserId', $iUserId);
                $returnArr['sourceLatitude'] = $Source_point_latitude;
                $returnArr['sourceLongitude'] = $Source_point_longitude;
                $returnArr['PassengerId'] = $iUserId;
                $returnArr['PName'] = $passengerData[0]['vName'] . ' ' . $passengerData[0]['vLastName'];
                $returnArr['PPicName'] = $passengerData[0]['vImgName'];
                $returnArr['PFId'] = $passengerData[0]['vFbId'];
                $returnArr['PRating'] = $passengerData[0]['vAvgRating'];
                $returnArr['PPhone'] = $passengerData[0]['vPhone'];
                $returnArr['PPhoneC'] = $passengerData[0]['vPhoneCode'];
                $returnArr['PAppVersion'] = $passengerData[0]['iAppVersion'];
                $returnArr['TripId'] = strval($iTripId);
                $returnArr['DestLocLatitude'] = $Dest_point_latitude;
                $returnArr['DestLocLongitude'] = $Dest_point_longitude;
                $returnArr['DestLocAddress'] = $Dest_point_Address;
                $returnArr['vVehicleType'] = $eIconType;
            }
            //unLinkFile($driverLockFilePath);
            setDataResponse($returnArr);
        } else {
            $data['Action'] = "0";
            $data['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            //unLinkFile($driverLockFilePath);
            setDataResponse($data);
        }

        /* }else{
          $returnArr['Action'] = "0";
          $returnArr['message']="LBL_CAR_REQUEST_CANCELLED_TXT_DL";
          setDataResponse($returnArr);
          } */
    } else {
        if ($eStatus == "Complete") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_FAIL_ASSIGN_TO_PASSENGER_TXT";
        } else if ($eStatus == "Cancelled") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CAR_REQUEST_CANCELLED_TXT_DL";
        }
        //unLinkFile($driverLockFilePath);
        setDataResponse($returnArr);
    }
}

// ##########################################################################
if ($type == "DriverArrived") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    if ($iDriverId != '') {
        $vTripStatus = get_value('register_driver', 'vTripStatus', 'iDriverId', $iDriverId, '', 'true');
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
            $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo, tr.tEndLat,tr.tEndLong,tr.tDaddress,tr.iUserId,tr.eType,rd.iTripId,tr.eTollSkipped,tr.eBeforeUpload,tr.eAfterUpload FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
            $result = $obj->MySQLSelect($sql);
            // echo "<pre>"; print_r($result);  die;
            $returnArr['Action'] = "1";
            if ($result[0]['iTripId'] != "") {
                // Update Trip Table
                $where1 = " iTripId = '" . $result[0]['iTripId'] . "'";
                $Data_update_trips['tDriverArrivedDate'] = date('Y-m-d H:i:s');
                $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where1);
                $tripUserId = $result[0]['iUserId'];
                $where_user = " iUserId = '$tripUserId'";
                $Data_update_passenger['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
                $Data_update_passenger['vVerificationCountEmergency'] = 0;
                $User_Update_id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where_user);
            }
            $data['DLatitude'] = $data['DLongitude'] = $data['DAddress'] = "0";
            if ($result[0]['tEndLat'] != '' && $result[0]['tEndLong'] != '') {
                $data['DLatitude'] = $result[0]['tEndLat'];
                $data['DLongitude'] = $result[0]['tEndLong'];
                $data['DAddress'] = $result[0]['tDaddress'];
            }
            $data['eTollSkipped'] = $result[0]['eTollSkipped'];
            $data['eBeforeUpload'] = $result[0]['eBeforeUpload'];
            $data['eAfterUpload'] = $result[0]['eAfterUpload'];
            $returnArr['message'] = $data;
            // echo "UpdateSuccess";
            /* $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
              $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
              $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
              $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY"); */
            if ($PUBNUB_DISABLED == "Yes") {
                $ENABLE_PUBNUB = "No";
            }
            /* For PubNub Setting */
            $tableName = "register_user";
            $iMemberId_VALUE = $result[0]['iUserId'];
            $iMemberId_KEY = "iUserId";
            /* $iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
              $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');
              $iGcmRegId=get_value($tableName, 'iGcmRegId', $iMemberId_KEY,$iMemberId_VALUE,'','true');
              $vLangCode=get_value($tableName, 'vLang', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType,iGcmRegId,vLang', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            $iGcmRegId = $AppData[0]['iGcmRegId'];
            $vLangCode = $AppData[0]['vLang'];
            /* For PubNub Setting Finished */
            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }

            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
            $driverArrivedLblValue = $languageLabelsArr['LBL_DRIVER_ARRIVED_NOTIMSG'];
            $driverArrivedLblValue_delivery = $languageLabelsArr['LBL_CARRIER_ARRIVED_NOTIMSG'];
            $driverArrivedLblValue_ride = $languageLabelsArr['LBL_DRIVER_ARRIVED_TXT'];
            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();
            $message = "";
            $message_arr['Message'] = "DriverArrived";
            $message_arr['MsgType'] = "DriverArrived";
            $message_arr['iDriverId'] = $iDriverId;
            $message_arr['driverName'] = $result[0]['driverName'];
            $message_arr['vRideNo'] = $result[0]['vRideNo'];
            $message_arr['iTripId'] = $result[0]['iTripId'];
            $message_arr['eType'] = $result[0]['eType'];
            $eType = $result[0]['eType'];
            if ($eType == "UberX" || $eType == "Deliver") {
                $alertMsg = $languageLabelsArr['LBL_DELIVERY_DRIVER_TXT'] . ' ' . $result[0]['driverName'] . ' ' . $driverArrivedLblValue . $result[0]['vRideNo'];
            } else {
                $alertMsg = $driverArrivedLblValue_ride;
            }

            $message_arr['vTitle'] = $alertMsg;
            $message_arr['eSystem'] = "DeliverAll";
            $message = json_encode($message_arr);
            $alertSendAllowed = true;

            // ####################Add Status Message#########################
            $DataTripMessages['tMessage'] = $message;
            $DataTripMessages['iDriverId'] = $iDriverId;
            $DataTripMessages['iTripId'] = $result[0]['iTripId'];
            $DataTripMessages['iUserId'] = $result[0]['iUserId'];
            $DataTripMessages['eFromUserType'] = "Driver";
            $DataTripMessages['eToUserType'] = "Passenger";
            $DataTripMessages['eReceived'] = "No";
            $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
            $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');

            // ###############################################################
            if ($alertSendAllowed == true) {
                if ($eDeviceType == "Android") {
                    array_push($registation_ids_new, $iGcmRegId);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                } else if ($eDeviceType != "Android") {
                    /* if($ENABLE_PUBNUB == "Yes"){
                      $message = "";
                      } */
                    array_push($deviceTokens_arr_ios, $iGcmRegId);
                    if ($message != "") {
                        sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                    }
                }
            }
            //sleep(3);
            ####  PUBNUB ##########
            // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""/*  && $iAppVersion > 1 && $eDeviceType == "Android" */){
            // $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
            /* $pubnub = new Pubnub\Pubnub(array(
              "publish_key" => $PUBNUB_PUBLISH_KEY,
              "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
              "uuid" => $uuid
              )); */
            $channelName = "PASSENGER_" . $result[0]['iUserId'];
            $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $result[0]['iUserId'], '', 'true');
            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

            if (count($deviceTokens_arr_ios) > 0) {
                sleep(3);
            }
            // $info = $pubnub->publish($channelName, $message_pub);
            publishEventMessage($channelName, $message_pub);
            /* if ($PUBNUB_DISABLED == "Yes") {
              publishEventMessage($channelName, $message_pub);
              } else {
              $info = $pubnub->publish($channelName, $message_pub);
              } */
            ####  PUBNUB ##########
            // }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

            // echo "UpdateFailed";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ###########################################################################
if ($type == "updateDriverLocations") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $latitude_driver = isset($_REQUEST["latitude"]) ? $_REQUEST["latitude"] : '';
    $longitude_driver = isset($_REQUEST["longitude"]) ? $_REQUEST["longitude"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $where = " iDriverId='$iDriverId'";
    $Data_update_driver['vLatitude'] = $latitude_driver;
    $Data_update_driver['vLongitude'] = $longitude_driver;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);

    // Update User Location Date #
    Updateuserlocationdatetime($iDriverId, "Driver", $vTimeZone);

    // Update User Location Date #
    if ($id) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
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
    } else {
        $returnArr['Action'] = "0";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "StartTrip") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $TripID = isset($_REQUEST["TripID"]) ? $_REQUEST["TripID"] : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
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
    $message = "TripStarted";
    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName, tr.vRideNo FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $iDriverId . "'";
    $result22 = $obj->MySQLSelect($sql);
    //$verificationCode = rand(10000000, 99999999);
    $verificationCode = generateCommonRandom();
    /* $eType =get_value('trips', 'eType', 'iTripId',$TripID,'','true');
      $fVisitFee = get_value('trips', 'fVisitFee', 'iTripId', $TripID,'','true');
      $eFareType = get_value('trips', 'eFareType', 'iTripId', $TripID,'','true'); */
    $TripData = get_value('trips', 'eType,fVisitFee,eFareType,tStartDate', 'iTripId', $TripID);
    $eType = $TripData[0]['eType'];
    $tStartDate = $TripData[0]['tStartDate'];
    $fVisitFee = $TripData[0]['fVisitFee'];
    $eFareType = $TripData[0]['eFareType'];
    if ($eType == "UberX") {
        $alertMsg = $languageLabelsArr['LBL_DELIVERY_DRIVER_TXT'] . ' ' . $result22[0]['driverName'] . ' ' . $tripstartlabel . $result22[0]['vRideNo'];
    } else {
        $alertMsg = $tripstartlabel_ride;
    }

    $message_arr = array();
    $message_arr['Message'] = $message;
    $message_arr['iDriverId'] = $iDriverId;
    $message_arr['iTripId'] = $TripID;
    $message_arr['driverName'] = $result22[0]['driverName'];
    $message_arr['vRideNo'] = $result22[0]['vRideNo'];
    if ($eType == "Deliver") {
        $message_arr['VerificationCode'] = strval($verificationCode);
    } else {
        $message_arr['VerificationCode'] = "";
    }

    $message_arr['vTitle'] = $alertMsg;
    $message_arr['eType'] = $eType;
    $message_arr['eSystem'] = "DeliverAll";
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    // ####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $iDriverId;
    $DataTripMessages['iTripId'] = $TripID;
    $DataTripMessages['iUserId'] = $iUserId;
    $DataTripMessages['eFromUserType'] = "Driver";
    $DataTripMessages['eToUserType'] = "Passenger";
    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');

    // ###############################################################
    // Update passenger Table
    $where = " iUserId = '$iUserId'";
    /* $Data_update_passenger['vTripStatus'] = 'On Going Trip';
      $Data_update_passenger['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
      $Data_update_passenger['vVerificationCountEmergency'] = 0;
      $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where); */

    // Update Driver Table
    $where = " iDriverId = '$iDriverId'";
    $Data_update_driver['vTripStatus'] = 'On Going Trip';
    $Data_update_driver['dSendverificationDateEmergency'] = "0000-00-00 00:00:00";
    $Data_update_driver['vVerificationCountEmergency'] = 0;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    //update insurance log
    if (strtoupper($PACKAGE_TYPE) == "SHARK") {
        $details_arr['iTripId'] = $TripID;
        $details_arr['LatLngArr']['vLatitude'] = $vLatitude;
        $details_arr['LatLngArr']['vLongitude'] = $vLongitude;
        // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
        update_driver_insurance_status($iDriverId, "Trip", $details_arr, "StartTrip");
    }
    //update insurance log
    $sql = "SELECT iGcmRegId,eDeviceType,iTripId,tLocationUpdateDate,eLogout,tSessionId FROM register_user WHERE iUserId='$iUserId'";
    $result = $obj->MySQLSelect($sql);

    // $Curr_TripID=$result[0]['iTripId'];
    $where = " iTripId = '$TripID'";
    $Data_update_trips['iActive'] = 'On Going Trip';
    if ($tStartDate == "0000-00-00 00:00:00") {
        $Data_update_trips['tStartDate'] = $startDateOfTrip;
    }
    /* Code for Upload StartImage of trip Start */
    if ($image_name != "") {

        // $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path']."/".$TripID."/";
        $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path'];
        if (!is_dir($Photo_Gallery_folder))
            mkdir($Photo_Gallery_folder, 0777);
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "bmp,pdf,doc,docx,jpg,jpeg,gif,png,xls,xlsx,csv");
        $vImageName = $vFile[0];
        $Data_update_trips['vBeforeImage'] = $vImageName;
    }

    /* Code for Upload StartImage of trip End */
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['fVisitFee'] = $fVisitFee;
        /* $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
          $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
          $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
          $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY"); */
        if ($PUBNUB_DISABLED == "Yes") {
            $ENABLE_PUBNUB = "No";
        }

        /* For PubNub Setting */
        $tableName = "register_user";
        $iMemberId_VALUE = $iUserId;
        $iMemberId_KEY = "iUserId";
        /* $iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
          $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
        $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        /* For PubNub Setting Finished */
        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
        $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

        // $alertSendAllowed = false;
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
            } else {
                sendApplePushNotification(0, $deviceTokens_arr, $message, $alertMsg, 0);
            }
        }

        //sleep(3);
        #######    Pubnub ######################
        // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""/*  && $iAppVersion > 1 && $eDeviceType == "Android" */){
        // $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
        /* $pubnub = new Pubnub\Pubnub(array(
          "publish_key" => $PUBNUB_PUBLISH_KEY,
          "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
          "uuid" => $uuid
          )); */
        $channelName = "PASSENGER_" . $iUserId;

        // $tSessionId=get_value("register_user", 'tSessionId', "iUserId",$iUserId,'','true');
        $tSessionId = $result[0]['tSessionId'];
        $message_arr['tSessionId'] = $tSessionId;
        $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

        // $info = $pubnub->publish($channelName, $message_pub);
        if ($result[0]['eDeviceType'] == "Ios") {
            sleep(3);
        }

        /* if ($PUBNUB_DISABLED == "Yes") {
          publishEventMessage($channelName, $message_pub);
          } else {
          $info = $pubnub->publish($channelName, $message_pub);
          } */
        publishEventMessage($channelName, $message_pub);
        // $message = $alertMsg;
        $tLocUpdateDate = date("Y-m-d H:i:s", strtotime($result[0]['tLocationUpdateDate']));
        if ($tLocUpdateDate < $compare_date) {
            $alertSendAllowed = true;
        }
        #######    Pubnub ######################
        // $alertSendAllowed = true;
        // }
        /* else{
          $alertSendAllowed = true;
          } */

        // Send SMS to receiver if trip type is delivery.
        if ($eType == "Deliver") {
            $vPhoneCode = get_value('register_user', 'vPhoneCode', 'iUserId', $iUserId, '', 'true');
            $receiverMobile = get_value('trips', 'vReceiverMobile', 'iTripId', $TripID, '', 'true');
            $receiverMobile1 = "+" . $receiverMobile;
            $where_trip_update = " iTripId = '$TripID'";
            $data_delivery['vDeliveryConfirmCode'] = $verificationCode;
            $obj->MySQLQueryPerform("trips", $data_delivery, 'update', $where);

            // $message_deliver = "SMS format goes here. Your verification code is ".$verificationCode." Please give this code to driver to end delivery process.";
            $message_deliver = deliverySmsToReceiver($TripID);
            $result = sendEmeSms($receiverMobile1, $message_deliver);
            if ($result == 0) {

                // $isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
                $receiverMobile3 = "+" . $vPhoneCode . $receiverMobile;
                $result1 = sendEmeSms($receiverMobile3, $message_deliver);
                if ($result1 == 0) {
                    $isdCode = $SITE_ISD_CODE;
                    $receiverMobile = "+" . $isdCode . $receiverMobile;
                    sendEmeSms($receiverMobile, $message_deliver);
                }
            }

            $returnArr['message'] = $verificationCode;
            $returnArr['SITE_TYPE'] = SITE_TYPE;
        }
    } else {
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

// ##########################################################################
if ($type == "ProcessEndTrip") {
    global $generalobj;
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
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
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    $DriverRation = get_value('currency', 'Ratio', 'vName', $vCurrencyDriver, '', 'true');
    if ($image_object) {
        ExifCleaning::adjustImageOrientation($image_object);
    }

    // $exifDATA = exif_read_data($image_object, 0, true);
    // echo "EXIFData::<BR/>";
    // print_r($exifDATA);exit;
    // $currencyRatio = get_value('currency', 'Ratio', 'eDefault', 'Yes','','true');
    $fMaterialFee = round($fMaterialFee / $DriverRation, 2);
    $fMiscFee = round($fMiscFee / $DriverRation, 2);
    $fDriverDiscount = round($fDriverDiscount / $DriverRation, 2);
    $eType = get_value('trips', 'eType', 'iTripId', $tripId, '', 'true');
    $Active = "Finished";
    $vLangCode = get_value('register_user', 'vLang', 'iUserId', $userId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    // ## Checking For Fixlocation Trip ###
    /* $sqlt = "SELECT tStartLat,tStartLong,eFlatTrip,iVehicleTypeId FROM trips WHERE iTripId = '".$tripId."'";
      $flattrip = $obj->MySQLSelect($sqlt);
      $FlatTrip = $flattrip[0]['eFlatTrip'];
      if($FlatTrip == "Yes"){
      $pickuplocationarr_flattrip = array($flattrip[0]['tStartLat'],$flattrip[0]['tStartLong']);
      $dropofflocationarr_flattrip = array($destination_lat,$destination_lon);
      $data_flattrip_check = checkFlatTripnew($pickuplocationarr_flattrip,$dropofflocationarr_flattrip,$flattrip[0]['iVehicleTypeId']);
      $EndFlatTrip = $data_flattrip_check['eFlatTrip'];
      if($EndFlatTrip == "No"){
      $wheretrip = " iTripId = '" . $tripId . "'";
      $Data_update_flattrips['eFlatTrip'] = "No";
      $Data_update_flattrips['fFlatTripPrice'] = 0;
      $Flat_Trip_id = $obj->MySQLQueryPerform("trips",$Data_update_flattrips,'update',$wheretrip);
      }
      } */

    // ## Checking For Fixlocation Trip ###
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $tripcancelbydriver = $languageLabelsArr['LBL_TRIP_CANCEL_BY_DRIVER'];
    $tripfinish = $languageLabelsArr['LBL_DRIVER_END_NOTIMSG'];
    $tripfinish_ride = $languageLabelsArr['LBL_TRIP_FINISH'];
    $tripfinish_delivery = $languageLabelsArr['LBL_DELIVERY_FINISH'];
    $message_arr = array();
    $message_arr['ShowTripFare'] = "true";
    if ($isTripCanceled == "true") {
        $message = "TripCancelledByDriver";
    } else {
        $message = "TripEnd";
    }

    if ($iCancelReasonId != "") {
        $driverReason = get_value('cancel_reason', "vTitle_" . $vLangCode, 'iCancelReasonId', $iCancelReasonId, '', 'true');
    }
    $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS driverName,tr.vRideNo FROM trips as tr,register_driver as rd WHERE tr.iTripId=rd.iTripId AND rd.iDriverId = '" . $driverId . "'";
    $result22 = $obj->MySQLSelect($sql);
    if ($isTripCanceled == "true") {

        // $alertMsg = $tripcancelbydriver;
        if ($eType == "UberX") {
            $usercanceltriplabel = $result22[0]['driverName'] . ':' . $result22[0]['vRideNo'] . '-' . $languageLabelsArr['LBL_PREFIX_TRIP_CANCEL_DRIVER'] . ' ' . $driverReason;
        } else {
            $usercanceltriplabel = $languageLabelsArr['LBL_PREFIX_TRIP_CANCEL_DRIVER'] . ' ' . $driverReason;
        }

        $alertMsg = $usercanceltriplabel;
    } else {
        if ($eType == "UberX") {

            // $alertMsg = $tripfinish;
            $alertMsg = $result22[0]['driverName'] . " " . $tripfinish . " " . $result22[0]['vRideNo'];
        } else {
            $alertMsg = $tripfinish_ride;
        }
    }

    $message_arr['Message'] = $message;
    $message_arr['iTripId'] = $tripId;
    $message_arr['iDriverId'] = $driverId;
    $message_arr['driverName'] = $result22[0]['driverName'];
    $message_arr['vRideNo'] = $result22[0]['vRideNo'];
    if ($isTripCanceled == "true") {
        $message_arr['Reason'] = $driverReason;
        $message_arr['isTripStarted'] = "true";
    }

    $message_arr['vTitle'] = $alertMsg;
    $message_arr['eType'] = $eType;
    $message_arr['eSystem'] = "DeliverAll";
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    // ####################Add Status Message#########################
    $DataTripMessages['tMessage'] = $message;
    $DataTripMessages['iDriverId'] = $driverId;
    $DataTripMessages['iTripId'] = $tripId;
    $DataTripMessages['iUserId'] = $userId;
    $DataTripMessages['eFromUserType'] = "Driver";
    $DataTripMessages['eToUserType'] = "Passenger";
    $DataTripMessages['eReceived'] = "No";
    $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
    $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');

    // ###############################################################
    $couponCode = get_value('trips', 'vCouponCode', 'iTripId', $tripId, '', 'true');
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
        /* $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode,'','true');
          $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode,'','true'); */
        //$CouponData = get_value('coupon', 'fDiscount,eType', 'vCouponCode', $couponCode); //Commented By HJ On 18-01-2019
        //$discountValue = $CouponData[0]['fDiscount']; //Commented By HJ On 18-01-2019
        //$discountValueType = $CouponData[0]['eType']; //Commented By HJ On 18-01-2019
    }
    if ($latitudes != '' && $longitudes != '') {
        processTripsLocations($tripId, $latitudes, $longitudes);
    }

    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    $currencySymbolDriver = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');
    $sql = "SELECT tStartDate,tEndDate,tDriverArrivedDate,iVehicleTypeId,tStartLat,tStartLong,eFareType,fRatio_" . $vCurrencyDriver . " as fRatioDriver, vTripPaymentMode,fPickUpPrice,fNightPrice, eType, fTollPrice,eFlatTrip,fFlatTripPrice,eHailTrip,iOrganizationId,ePaymentBy FROM trips WHERE iTripId='$tripId'";
    $trip_start_data_arr = $obj->MySQLSelect($sql);
    $tripDistance = calcluateTripDistance($tripId);
    $sourcePointLatitude = $trip_start_data_arr[0]['tStartLat'];
    $sourcePointLongitude = $trip_start_data_arr[0]['tStartLong'];
    $startDate = $trip_start_data_arr[0]['tStartDate'];
    $vehicleTypeID = $trip_start_data_arr[0]['iVehicleTypeId'];
    $eFareType = $trip_start_data_arr[0]['eFareType'];
    $eType = $trip_start_data_arr[0]['eType'];
    $eFlatTrip = $trip_start_data_arr[0]['eFlatTrip'];
    $fFlatTripPrice = $trip_start_data_arr[0]['fFlatTripPrice'];

    // $endDateOfTrip=@date("Y-m-d H:i:s");
    $endDateOfTrip = $trip_start_data_arr[0]['tEndDate'];
    if ($endDateOfTrip == "0000-00-00 00:00:00" || $eType == "Multi-Delivery") {
        $endDateOfTrip = @date("Y-m-d H:i:s");
    }

    if ($eFareType == 'Hourly') {
        $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$tripId'";
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

    if ($totalTimeInMinutes_trip <= 1) {
        $FinalDistance = $tripDistance;
        $FGDTime = 0;
        $FGDDistance = 0;
    } else {

        // $FinalDistance=checkDistanceWithGoogleDirections($tripDistance,$sourcePointLatitude,$sourcePointLongitude,$destination_lat,$destination_lon);
        $FinalDistanceArr = checkDistanceWithGoogleDirections($tripDistance, $sourcePointLatitude, $sourcePointLongitude, $destination_lat, $destination_lon, "0", "", true);
        $FinalDistance = $FinalDistanceArr['Distance'];
        $FGDTime = $FinalDistanceArr['Time'];
        $FGDDistance = $FinalDistanceArr['GDistance'];
    }

    $tripDistance = $FinalDistance;
    $Fare_data = calculateFare($totalTimeInMinutes_trip, $tripDistance, $vehicleTypeID, $userId, 1, $startDate, $endDateOfTrip, $couponCode, $tripId, $fMaterialFee, $fMiscFee, $fDriverDiscount);
    $where = " iTripId = '" . $tripId . "'";
    $Data_update_trips['tEndDate'] = $endDateOfTrip;
    $Data_update_trips['tEndLat'] = $destination_lat;
    $Data_update_trips['tEndLong'] = $destination_lon;
    $Data_update_trips['tDaddress'] = $dAddress;
    $Data_update_trips['iFare'] = $Fare_data['total_fare'];
    $Data_update_trips['iActive'] = $Active;
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
    $Data_update_trips['fTax1Percentage'] = $Fare_data['fTax1Percentage'];
    $Data_update_trips['fTax2Percentage'] = $Fare_data['fTax2Percentage'];
    $Data_update_trips['fGDtime'] = $FGDTime;
    $Data_update_trips['fGDdistance'] = $FGDDistance;
    if ($isTripCanceled == "true") {
        $Data_update_trips['vCancelReason'] = $driverReason;
        $Data_update_trips['vCancelComment'] = $driverComment;
        $Data_update_trips['eCancelled'] = "Yes";
        $Data_update_trips['eCancelledBy'] = "Driver";
    }

    /* Code for Upload AfterImage of trip Start */
    if ($image_name != "") {

        // $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path']."/".$TripID."/";
        $Photo_Gallery_folder = $tconfig['tsite_upload_trip_images_path'];
        if (!is_dir($Photo_Gallery_folder))
            mkdir($Photo_Gallery_folder, 0777);
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "bmp,pdf,doc,docx,jpg,jpeg,gif,png,xls,xlsx,csv");
        $vImageName = $vFile[0];
        $Data_update_trips['vAfterImage'] = $vImageName;
    }

    /* Code for Upload AfterImage of trip End */
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);
    //update insurance log
    if (strtoupper($PACKAGE_TYPE) == "SHARK") {
        $details_arr['iTripId'] = $tripId;
        $details_arr['LatLngArr']['vLatitude'] = $destination_lat;
        $details_arr['LatLngArr']['vLongitude'] = $destination_lon;
        // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
        update_driver_insurance_status($driverId, "Trip", $details_arr, "ProcessEndTrip");
    }
    //update insurance log
    $trip_status = "Not Active";
    $where = " iUserId = '$userId'";
    /* $Data_update_passenger['iTripId'] = $tripId;
      $Data_update_passenger['vTripStatus'] = $trip_status;
      $Data_update_passenger['vCallFromDriver'] = 'Not Assigned';
      $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where); */
    $where = " iDriverId = '$driverId'";
    $Data_update_driver['iTripId'] = $tripId;
    $Data_update_driver['vTripStatus'] = $trip_status;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $where);
    if ($id > 0) {
        /* $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
          $PUBNUB_DISABLED = $generalobj->getConfigurations("configurations","PUBNUB_DISABLED");
          $PUBNUB_PUBLISH_KEY = $generalobj->getConfigurations("configurations","PUBNUB_PUBLISH_KEY");
          $PUBNUB_SUBSCRIBE_KEY = $generalobj->getConfigurations("configurations","PUBNUB_SUBSCRIBE_KEY"); */
        if ($PUBNUB_DISABLED == "Yes") {
            $ENABLE_PUBNUB = "No";
        }

        /* For PubNub Setting */
        $tableName = "register_user";
        $iMemberId_VALUE = $userId;
        $iMemberId_KEY = "iUserId";
        /* $iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
          $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true');
          $eLogout=get_value($tableName, 'eLogout', $iMemberId_KEY,$iMemberId_VALUE,'','true');
          $tLocationUpdateDate=get_value($tableName, 'tLocationUpdateDate', $iMemberId_KEY,$iMemberId_VALUE,'','true');
          $iGcmRegId=get_value($tableName, 'iGcmRegId', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
        $AppData = get_value($tableName, 'iAppVersion,eDeviceType,eLogout,tLocationUpdateDate,iGcmRegId', $iMemberId_KEY, $iMemberId_VALUE);
        $iAppVersion = $AppData[0]['iAppVersion'];
        $eDeviceType = $AppData[0]['eDeviceType'];
        $eLogout = $AppData[0]['eLogout'];
        $tLocationUpdateDate = $AppData[0]['tLocationUpdateDate'];
        $iGcmRegId = $AppData[0]['iGcmRegId'];
        /* For PubNub Setting Finished */
        $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
        $compare_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

        // $alertSendAllowed = false;
        $alertSendAllowed = true;

        // $message = $alertMsg;
        $tLocUpdateDate = date("Y-m-d H:i:s", strtotime($tLocationUpdateDate));
        if ($tLocUpdateDate < $compare_date) {
            $alertSendAllowed = true;
        }

        if ($eLogout == "Yes") {
            $alertSendAllowed = false;
        }

        $deviceTokens_arr = array();
        if ($alertSendAllowed == true) {
            array_push($deviceTokens_arr, $iGcmRegId);
            if ($eDeviceType == "Android") {
                $Rmessage = array(
                    "message" => $message
                );
                send_notification($deviceTokens_arr, $Rmessage, 0);
            } else {
                sendApplePushNotification(0, $deviceTokens_arr, $message, $alertMsg, 0);
            }
        }

        //sleep(3);
        ########## Pubnub #####################
        // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "" /* && $iAppVersion > 1 && $eDeviceType == "Android" */){
        // $pubnub = new Pubnub\Pubnub($PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY);
        /* $pubnub = new Pubnub\Pubnub(array(
          "publish_key" => $PUBNUB_PUBLISH_KEY,
          "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
          "uuid" => $uuid
          )); */
        $channelName = "PASSENGER_" . $userId;
        $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $userId, '', 'true');
        $message_arr['tSessionId'] = $tSessionId;
        $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

        if ($eDeviceType == "Ios") {
            sleep(3);
        }
        // $info = $pubnub->publish($channelName, $message_pub);
        /* if ($PUBNUB_DISABLED == "Yes") {
          publishEventMessage($channelName, $message_pub);
          } else {
          $info = $pubnub->publish($channelName, $message_pub);
          } */
        publishEventMessage($channelName, $message_pub);

        // $alertSendAllowed = true;
        // }
        /* else{
          $alertSendAllowed = true;
          } */
        ########## Pubnub #####################
        $returnArr['Action'] = "1";
        $returnArr['iTripsLocationsID'] = $id;

        // $returnArr['TotalFare']=round($Fare_data[0]['total_fare'] * $trip_start_data_arr[0]['fRatioDriver']);
        $returnArr['TotalFare'] = round($Fare_data['total_fare'] * $trip_start_data_arr[0]['fRatioDriver'], 1);

        // $returnArr['CurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE vName='".$trip_start_data_arr[0]['vCurrencyDriver']."' ")[0]['vSymbol']);
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
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    // getTripChatDetails($tripId);
    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "CollectPayment") {
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $isCollectCash = isset($_REQUEST["isCollectCash"]) ? $_REQUEST["isCollectCash"] : '';
    $sql = "SELECT vTripPaymentMode,iUserId,iDriverId,iFare,vRideNo,fWalletDebit,fTripGenerateFare,fDiscount,fCommision,fTollPrice,eHailTrip FROM trips WHERE iTripId='$iTripId'";
    $tripData = $obj->MySQLSelect($sql);
    $vTripPaymentMode = $tripData[0]['vTripPaymentMode'];
    $data['vTripPaymentMode'] = $vTripPaymentMode;
    $iUserId = $tripData[0]['iUserId'];

    // $iFare = $tripData[0]['iFare']+$tripData[0]['fTollPrice'];
    $iFare = $tripData[0]['iFare'];
    $vRideNo = $tripData[0]['vRideNo'];
    $eHailTrip = $tripData[0]['eHailTrip'];
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    if ($vTripPaymentMode == "Card" && $isCollectCash == "") {
        $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');
        $vBrainTreeToken = get_value('register_user', 'vBrainTreeToken', 'iUserId', $iUserId, '', 'true');
        $price_new = $iFare * 100;
        $currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        $description = $languageLabelsArr['LBL_TRIP_PAYMENT_RECEIVED_DL'] . " " . $vRideNo;
        try {
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
                $pay_data['tPaymentUserID'] = $iFare == 0 ? "" : $result['id'];
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iTripId'] = $iTripId;
                $pay_data['iAmountUser'] = $iFare;
                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
                setDataResponse($returnArr);
            }
        } catch (Exception $e) {
            $error3 = $e->getMessage();
            $returnArr['Action'] = "0";
            $returnArr['message'] = $error3;
            setDataResponse($returnArr);
        }
        $data['vTripPaymentMode'] = "Card";
    } else if ($vTripPaymentMode == "Card" && $isCollectCash == "true") {
        // echo "else if";exit;
        $data['vTripPaymentMode'] = "Cash";
    }

    // echo "out";exit;
    $where = " iTripId = '$iTripId'";
    $data['ePaymentCollect'] = "Yes";
    $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);
    $fWalletDebit = $tripData[0]['fWalletDebit'];
    $fDiscount = $tripData[0]['fDiscount'];
    $discountValue = $fWalletDebit + $fDiscount;

    // $discountValue = $tripData[0]['fDiscount'];
    // $walletamountofcreditcard = $tripData[0]['fTripGenerateFare']+$tripData[0]['fTollPrice'];
    $walletamountofcreditcard = $tripData[0]['fTripGenerateFare'];
    $driverId = $tripData[0]['iDriverId'];

    // $COMMISION_DEDUCT_ENABLE=$generalobj->getConfigurations("configurations","COMMISION_DEDUCT_ENABLE");
    if ($COMMISION_DEDUCT_ENABLE == 'Yes') {

        // Deduct Amount From Driver's Wallet Acount#
        $vTripPaymentMode = $data['vTripPaymentMode'];
        if ($vTripPaymentMode == "Cash") {
            $vRideNo = $tripData[0]['vRideNo'];
            $iBalance = $tripData[0]['fCommision'];
            $eFor = "Withdrawl";
            $eType = "Debit";
            $iTripId = $iTripId;

            // $tDescription = 'Debited for booking#'.$vRideNo;
            $tDescription = '#LBL_DEBITED_BOOKING_DL#' . ' ' . $vRideNo;
            $ePaymentStatus = 'Settelled';
            $dDate = Date('Y-m-d H:i:s');
            if ($discountValue > 0) {
                $eFor_credit = "Deposit";
                $eType_credit = "Credit";
                $tDescription_credit = '#LBL_CREDITED_BOOKING_DL# ' . $vRideNo;

                // $tDescription_credit = 'Credited for booking#'.$vRideNo;
                $generalobj->InsertIntoUserWallet($driverId, "Driver", $discountValue, $eType_credit, $iTripId, $eFor_credit, $tDescription_credit, $ePaymentStatus, $dDate);
            }

            $generalobj->InsertIntoUserWallet($driverId, "Driver", $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate);
            $Where = " iTripId = '$iTripId'";
            $Data_update_driver_paymentstatus['eDriverPaymentStatus'] = "Settelled";
            $Update_Payment_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus, 'update', $Where);
        }

        /* else{
          $vRideNo = $tripData[0]['vRideNo'];
          $iBalance = $walletamountofcreditcard-$tripData[0]['fCommision'];
          $eFor = "Deposit";
          $eType = "Credit";
          $iTripId = $iTripId;
          $tDescription = ' Amount '.$iBalance.' Credited into your account for booking no#'.$vRideNo;
          $ePaymentStatus = 'Settelled';
          $dDate =   Date('Y-m-d H:i:s');
          $generalobj->InsertIntoUserWallet($driverId,"Driver",$iBalance,$eType,$iTripId,$eFor,$tDescription,$ePaymentStatus,$dDate);
          $Where = " iTripId = '$iTripId'";
          $Data_update_driver_paymentstatus['eDriverPaymentStatus']="Settelled";
          $Update_Payment_Id = $obj->MySQLQueryPerform("trips",$Data_update_driver_paymentstatus,'update',$Where);
          } */

        // Deduct Amount From Driver's Wallet Acount#
    }

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
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
// ##########################################################################
if ($type == "addMoneyUserWallet") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
    if ($eMemberType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iUserId = "iUserId";
        $eUserType = "Rider";
    } else {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iUserId = "iDriverId";
        $eUserType = "Driver";
    }

    /* $vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId,'','true');
      $vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId,'','true');
      $userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId,'','true'); */

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $UserCardData = get_value($tbl_name, 'vStripeCusId,vStripeToken,vBrainTreeToken,vPaymayaCustId,vXenditToken,vFlutterWaveToken,vPaymayaToken,' . $currencycode . ' as currencycode', $iUserId, $iMemberId);
    /* Added By PM On 09-12-2019 For Flutterwave Code End */

    $vStripeCusId = $UserCardData[0]['vStripeCusId'];
    $vStripeToken = $UserCardData[0]['vStripeToken'];
    $userCurrencyCode = $UserCardData[0]['currencycode'];
    $vBrainTreeToken = $UserCardData[0]['vBrainTreeToken'];
    $vPaymayaCustId = $UserCardData[0]['vPaymayaCustId'];
    $vPaymayaToken = $UserCardData[0]['vPaymayaToken'];
    $vXenditToken = $UserCardData[0]['vXenditToken'];

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $vFlutterWaveToken = $UserCardData[0]['vFlutterWaveToken'];
    /* Added By PM On 09-12-2019 For Flutterwave Code end */

    $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $userCurrencyCode, '', 'true');
    $walletamount = round($fAmount / $userCurrencyRatio, 2);
    /* $currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes','','true');
      $currencyratio = get_value('currency', 'Ratio', 'vName', $currencyCode,'','true'); */
    $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
    $currencyCode = $DefaultCurrencyData[0]['vName'];
    $currencyratio = $DefaultCurrencyData[0]['Ratio'];
    $price = $fAmount * $currencyratio;
    $price_new = $walletamount * 100;
    $price_new = round($price_new);
    if ((($vStripeCusId == "" || $vStripeToken == "") && $APP_PAYMENT_METHOD == "Stripe")) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    if ($vBrainTreeToken == "" && $APP_PAYMENT_METHOD == "Braintree") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    if ((($vPaymayaCustId == "" || $vPaymayaToken == "") && $APP_PAYMENT_METHOD == "Paymaya")) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    if ($vXenditToken == "" && $APP_PAYMENT_METHOD == "Xendit") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    if ($vFlutterWaveToken == "" && $APP_PAYMENT_METHOD == "Flutterwave") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code end */

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

        // $user_available_balance = $generalobj->get_user_available_balance($iMemberId,$eUserType);
        $user_available_balance = $generalobj->get_user_available_balance_app_display($iMemberId, $eUserType);
        $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
        $data_payments['iUserWalletId'] = $WalletId;
        $data_payments['eEvent'] = "Wallet";
        $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
        $returnArr["Action"] = "1";

        // $returnArr["MemberBalance"] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$userCurrencyCode));
        $returnArr["MemberBalance"] = strval($user_available_balance);
        $returnArr['message1'] = "LBL_WALLET_MONEY_CREDITED";
        if ($eMemberType != "Driver") {
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
        } else {
            $returnArr['message'] = getDriverDetailInfo($iMemberId);
        }

        setDataResponse($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WALLET_MONEY_CREDITED_FAILED";
        setDataResponse($returnArr);
    }
    /* try{
      $charge_create = Stripe_Charge::create(array(
      "amount" => $price_new,
      "currency" => $currencyCode,
      "customer" => $vStripeCusId,
      "description" =>  $tDescription
      ));
      $details = json_decode($charge_create);
      $result = get_object_vars($details);
      //echo "<pre>";print_r($result);exit;
      if($result['status']=="succeeded" && $result['paid']=="1"){
      $generalobj->InsertIntoUserWallet($iMemberId,$eUserType,$walletamount,'Credit',0,$eFor,$tDescription,$ePaymentStatus,$dDate);
      $user_available_balance = $generalobj->get_user_available_balance($iMemberId,$eUserType);
      $returnArr["Action"] = "1";
      $returnArr["MemberBalance"] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$userCurrencyCode));
      $returnArr['message1']= "LBL_WALLET_MONEY_CREDITED";
      if($eMemberType != "Driver"){
      $returnArr['message'] = getPassengerDetailInfo($iMemberId,"");
      }else{
      $returnArr['message'] = getDriverDetailInfo($iMemberId);
      }

      setDataResponse($returnArr);
      }else{
      $returnArr['Action'] = "0";
      $returnArr['message']="LBL_WALLET_MONEY_CREDITED_FAILED";
      setDataResponse($returnArr);
      }

      }catch(Exception $e){
      // echo "<pre>";print_r($e);exit;
      $error3 = $e->getMessage();
      $returnArr["Action"] = "0";
      $returnArr['message'] = $error3;

      // $returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
      setDataResponse($returnArr);
      } */
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
    } else {
        $tbl_name = "register_driver";
        $vEmail = "vEmail";
        $iMemberId = "iDriverId";
        $eUserType = "Driver";
    }

    $updateData = array();
    $updateQuery = $id = $chargeAmtFlag = $chargeAmt = 0;
    $where = " $iMemberId = '$iUserId'";
    /* Updated By PM On 09-12-2019 For Flutterwave Code Start */
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
        if ($vFlutterWaveToken != "") {
            $updateQuery = 1;
            $updateData['vFlutterWaveToken'] = $vFlutterWaveToken;
        }

        if ($eUserType == 'Rider') {
            $fieldname = 'ru.iUserId';
            $tablename = 'register_user as ru';
            $getfields = 'ru.vCurrencyPassenger as currency, cu.ratio, cu.vSymbol, ru.vEmail, CONCAT( ru.vName,  " ", ru.vLastName ) AS username';
            $onfields = "ON ru.vCurrencyPassenger = cu.vName";
        } else {
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
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code end */
    if ($eMemberType == "Passenger") {
        $profileData = getPassengerDetailInfo($iUserId, "", "");
    } else {
        $profileData = getDriverDetailInfo($iUserId);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $profileData;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

###########################################################################
###########################################################################
if ($type == "CheckCard") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $UserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : '';


    if ($APP_PAYMENT_METHOD == "Stripe") {

        $vStripeCusId = get_value('register_user', 'vStripeCusId', 'iUserId', $iUserId, '', 'true');

        if ($vStripeCusId != "") {

            try {
                $customer = Stripe_Customer::retrieve($vStripeCusId);
                $sources = $customer->sources;
                $data = $sources->data;

                $cvc_check = $data[0]['cvc_check'];

                if ($cvc_check && $cvc_check == "pass") {
                    $returnArr['Action'] = "1";
                } else {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_INVALID_CARD";
                }
            } catch (Exception $e) {
                $error3 = $e->getMessage();
                $returnArr['Action'] = "0";
                $returnArr['message'] = $error3;
                //$returnArr['message']="LBL_TRY_AGAIN_LATER_TXT";
            }
        } else if ($APP_PAYMENT_METHOD == "Braintree") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else if ($APP_PAYMENT_METHOD == "Flutterwave") {
        if ($UserType == 'Driver') {
            $vFlutterWaveToken = get_value('register_driver', 'vFlutterWaveToken', 'iDriverId', $iUserId, '', 'true');
        } else {
            $vFlutterWaveToken = get_value('register_user', 'vFlutterWaveToken', 'iUserId', $iUserId, '', 'true');
        }

        if ($vFlutterWaveToken != "") {

            $returnArr['Action'] = "1";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else {
        $returnArr['Action'] = "1";
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code End */

    setDataResponse($returnArr);
}

###########################################################################
if ($type == "getDriverRideHistory") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $date = isset($_REQUEST["date"]) ? $_REQUEST["date"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $date = $date . " " . "12:01:00";
    $date = date("Y-m-d H:i:s", strtotime($date));
    $serverTimeZone = date_default_timezone_get();
    $date = converToTz($date, $serverTimeZone, $vTimeZone, "Y-m-d");
    /* $vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId,'','true');
      $vLanguage=get_value('register_driver', 'vLang', 'iDriverId',$iDriverId,'','true'); */
    $DriverDetail = get_value('register_driver', 'vCurrencyDriver,vLang', 'iDriverId', $iDriverId);
    $vCurrencyDriver = $DriverDetail[0]['vCurrencyDriver'];
    $vLanguage = $DriverDetail[0]['vLang'];

    // $currencySymbol=get_value('currency', 'vSymbol', 'eDefault', 'Yes','','true');
    // $priceRatio=1;
    // $fRatioDriver = get_value('currency', 'Ratio', 'vName', $vCurrencyDriver,'','true');
    $currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    // $sql = "SELECT tr.*, rate.vRating1, rate.vMessage,ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,ratings_user_driver as rate,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '".$date."%' AND tr.iActive='Finished' AND rate.iTripId = tr.iTripId AND rate.eUserType='Passenger' AND ru.iUserId=tr.iUserId";
    $sql = "SELECT tr.*, ru.vName,ru.vLastName,ru.vImgName as vImage FROM trips as tr,register_user as ru WHERE tr.iDriverId='$iDriverId' AND tr.tTripRequestDate LIKE '" . $date . "%' AND tr.iActive='Finished' AND ru.iUserId=tr.iUserId ORDER By tr.iTripId DESC";
    $tripData = $obj->MySQLSelect($sql);
    $totalEarnings = 0;
    $avgRating = 0;
    if (count($tripData) > 0) {
        for ($i = 0; $i < count($tripData); $i++) {

            // $iFare = $tripData[$i]['fTripGenerateFare']-$tripData[$i]['fTollPrice'];
            $iFare = $tripData[$i]['fTripGenerateFare'];

            // $iFare = $tripData[$i]['fTripGenerateFare'];
            $fCommision = $tripData[$i]['fCommision'];
            $fDiscount = $tripData[$i]['fDiscount'];
            $fTipPrice = $tripData[$i]['fTipPrice'];
            $fTollPrice = $tripData[$i]['fTollPrice'];
            $fTax1 = $tripData[$i]['fTax1'];
            $fTax2 = $tripData[$i]['fTax2'];

            // $vRating1 = $tripData[$i]['vRating1'];
            $priceRatio = $tripData[$i]['fRatio_' . $vCurrencyDriver];
            $sql = "SELECT vRating1, vMessage FROM ratings_user_driver WHERE iTripId = '" . $tripData[$i]['iTripId'] . "' AND eUserType='Passenger'";
            $tripData_rating = $obj->MySQLSelect($sql);
            if (count($tripData_rating) > 0) {
                $tripData[$i]['vRating1'] = $tripData_rating[0]['vRating1'];
                $tripData[$i]['vMessage'] = $tripData_rating[0]['vMessage'];
                $vRating1 = $tripData_rating[0]['vRating1'];
            } else {
                $tripData[$i]['vRating1'] = "0";
                $tripData[$i]['vMessage'] = "";
                $vRating1 = 0;
            }

            if (($iFare == "" || $iFare == 0) && $fDiscount > 0) {
                $incValue = ($fDiscount - $fCommision - $fTax1 - $fTax2) + $fTipPrice;
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            } else if ($iFare != "" && $iFare > 0) {
                $incValue = ($iFare - $fCommision - $fTax1 - $fTax1) + $fTipPrice;
                $totalEarnings = $totalEarnings + ($incValue * $priceRatio);
            }

            $avgRating = $avgRating + $vRating1;
            $returnArr = getTripPriceDetails($tripData[$i]['iTripId'], $iDriverId, "Driver");
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
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    $returnArr['TotalEarning'] = strval(round($totalEarnings, 2));
    $returnArr['TripDate'] = date('l, dS M Y', strtotime($date));
    $returnArr['TripCount'] = strval(count($tripData));

    // $returnArr['AvgRating'] = strval(round(count($tripData) == 0? 0 : ($avgRating/count($tripData)),2));
    $returnArr['AvgRating'] = strval(getMemberAverageRating($iDriverId, "Driver", $date));
    $returnArr['CurrencySymbol'] = $currencySymbol;
    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "loadDriverFeedBack") {
    global $generalobj, $tconfig;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vAvgRating = get_value('register_driver', 'vAvgRating', 'iDriverId', $iDriverId, '', 'true');
    $per_page = 10;
    $sql_all = "SELECT COUNT(o.iOrderId) As TotalIds FROM orders as o LEFT JOIN ratings_user_driver as rate on rate.iOrderId = o.iOrderId WHERE o.iDriverId='$iDriverId' AND o.iStatusCode='6' AND rate.eToUserType = '" . $UserType . "'";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    $sql = "SELECT rate.*,CONCAT(ru.vName,' ',ru.vLastName) as vName,ru.iUserId as passengerid,ru.vImgName FROM ratings_user_driver as rate LEFT JOIN orders as o ON o.iOrderId = rate.iOrderId  LEFT JOIN register_user as ru ON ru.iUserId = o.iUserId WHERE o.iDriverId='$iDriverId' AND o.iStatusCode='6' AND rate.eToUserType = '" . $UserType . "' ORDER BY o.iOrderId DESC" . $limit;
    $Data = $obj->MySQLSelect($sql);
    for ($i = 0; $i < count($Data); $i++) {
        $Data[$i]['vImage'] = $tconfig["tsite_upload_images_passenger"] . '/' . $Data[$i]['passengerid'] . '/3_' . $Data[$i]['vImgName'];
        $Data[$i]['tDateOrig'] = $Data[$i]['tDate'];
        $Data[$i]['tDate'] = $generalobj->DateTime($Data[$i]['tDate'], 14);
    }

    $totalNum = count($Data);
    if (count($Data) > 0) {
        $returnData['message'] = $Data;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        } else {
            $returnData['NextPage'] = "0";
        }

        $returnData['vAvgRating'] = strval($vAvgRating);
        $returnData['Action'] = "1";

        setDataResponse($returnData);
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_FEEDBACK";
        setDataResponse($returnData);
    }
}

// ##########################################################################
if ($type == "loadEmergencyContacts") {
    global $generalobj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : 'Passenger';
    if ($UserType == "") {
        $UserType = $GeneralUserType;
    }

    // $data = get_value('user_emergency_contact', '*', 'iUserId', $iUserId);
    // $data = get_value('user_emergency_contact', '*', 'eUserType', $UserType,'','true');
    $sql = "SELECT * FROM user_emergency_contact WHERE iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "'";
    $data = $obj->MySQLSelect($sql);
    if (count($data) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $data;
    } else {
        $returnData['Action'] = "0";
    }


    setDataResponse($returnData);
}

// ##########################################################################
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
    } else {
        $Data['vName'] = $vName;
        $Data['vPhone'] = $Phone;
        $Data['iUserId'] = $iUserId;
        $Data['eUserType'] = $UserType;
        $id = $obj->MySQLQueryPerform("user_emergency_contact", $Data, 'insert');
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_EME_CONTACT_LIST_UPDATE";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    }


    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "deleteEmergencyContacts") {
    global $generalobj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iEmergencyId = isset($_REQUEST["iEmergencyId"]) ? $_REQUEST["iEmergencyId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $sql = "DELETE FROM user_emergency_contact WHERE `iEmergencyId`='" . $iEmergencyId . "' AND `iUserId`='" . $iUserId . "' AND eUserType = '" . $UserType . "'";
    $id = $obj->sql_query($sql);

    // echo "ID:".$id;exit;
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EME_CONTACT_LIST_UPDATE";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "sendAlertToEmergencyContacts") {
    global $generalobj, $obj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '0';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    CheckUserSmsLimitForEmergency($iUserId, $UserType);
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
        $tripData[0]['tStartDate'] = ($tripData[0]['tStartDate'] == '0000-00-00 00:00:00') ? $tripData[0]['tTripRequestDate'] : $tripData[0]['tStartDate'];

        // $isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
        $isdCode = $SITE_ISD_CODE;
        if ($APP_TYPE == "UberX") {
            if ($UserType == "Passenger") {
                $message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the Job are: Job start time: ' . date('dS M \a\t h:i a', strtotime($tripData[0]['tTripRequestDate'])) . '. Job Address: ' . $tripData[0]['tSaddress'] . '. Service Provider name: ' . $tripData[0]['vDriverName'] . '. Service Provider number:(' . $tripData[0]['DriverPhone'] . ")";
            } else {
                $message = "Important: " . $tripData[0]['vDriverName'] . ' (' . $tripData[0]['DriverPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the Job are: Job start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Job Address: ' . $tripData[0]['tSaddress'] . '. User name: ' . $tripData[0]['vPassengerName'] . '. User number:(' . $tripData[0]['PassengerPhone'] . ")";
            }
        } else {
            if ($UserType == "Passenger") {
                $message = "Important: " . $tripData[0]['vPassengerName'] . ' (' . $tripData[0]['PassengerPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the ride are: Trip start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Driver name: ' . $tripData[0]['vDriverName'] . '. Driver number:(' . $tripData[0]['DriverPhone'] . "). Driver's car number: " . $tripData[0]['vLicencePlate'];
            } else {
                $message = "Important: " . $tripData[0]['vDriverName'] . ' (' . $tripData[0]['DriverPhone'] . ') has reached out to you via ' . $SITE_NAME . ' SOS. Please reach out to him/her urgently. The details of the ride are: Trip start time: ' . date('dS M Y \a\t h:i a', strtotime($tripData[0]['tStartDate'])) . '. Pick up from: ' . $tripData[0]['tSaddress'] . '. Passenger name: ' . $tripData[0]['vPassengerName'] . '. Passenger number:(' . $tripData[0]['PassengerPhone'] . "). Driver's car number: " . $tripData[0]['vLicencePlate'];
            }
        }

        for ($i = 0; $i < count($dataArr); $i++) {
            $phone = preg_replace("/[^0-9]/", "", $dataArr[$i]['vPhone']);
            $toMobileNum = "+" . $phone;

            //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 start
            if ($UserType == "Passenger") {
                $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
            } else if ($userType == "Company") {
                $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `company` AS r, `country` AS c WHERE r.iCompanyId = $iUserId AND r.vCountry = c.vCountryCode");
            } else {
                $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iUserId AND r.vCountry = c.vCountryCode");
            }

            $PhoneCode = $passengerData[0]['vPhoneCode'];

            $result = $generalobj->sendSystemSms($toMobileNum, $PhoneCode, $message);
            //added by SP for sms functionality change, to get phonecode use this one on 12-7-2019 end

            /* $result = sendEmeSms($toMobileNum, $message);
              if ($result == 0) {
              $toMobileNum = "+" . $isdCode . $phone;
              sendEmeSms($toMobileNum, $message);
              } */
        }

        UpdateUserSmsLimitForEmergency($iUserId, $UserType);
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_EME_CONTACT_ALERT_SENT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ADD_EME_CONTACTS";
        $returnArr['message1'] = "ContactError";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
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
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';

    // $timeZone =  isset($_REQUEST["TimeZone"]) ? $_REQUEST["TimeZone"] : '';
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

    // $eAutoAssign    = 'Yes';
    $iDriverId = isset($_REQUEST["SelectedDriverId"]) ? $_REQUEST["SelectedDriverId"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '0';
    $tUserComment = isset($_REQUEST["tUserComment"]) ? $_REQUEST["tUserComment"] : '';
    $action = ($iCabBookingId != "") ? 'Edit' : 'Add';

    if ($PACKAGE_TYPE == "SHARK") {
        $BlockData = getBlockData("Passanger", $iUserId);
        if (!empty($BlockData) || $BlockData != "") {
            setDataResponse($BlockData);
        }
    }
    /* $sql = "SELECT eIsBlocked,iUserId FROM register_user WHERE iUserId='$iUserId' ";
      $Data_Rider = $obj->MySQLSelect($sql);
      $eIsBlocked = $Data_Rider[0]['eIsBlocked'];

      if ($eIsBlocked == 'Yes') {
      $returnArr['Action'] = "0";
      $returnArr['isShowContactUs'] = "Yes";
      $returnArr['message'] = "LBL_RIDER_BLOCK";
      setDataResponse($returnArr);
      exit;
      } */


    // $paymentMode =  isset($_REQUEST["paymentMode"]) ? $_REQUEST["paymentMode"] : 'Cash'; // Cash OR Card
    // $paymentMode = "Cash";
    // $paymentMode = $eType == "Deliver" ?"Card":"Cash";
    if ($cashPayment == 'true') {
        $paymentMode = "Cash";
    } else {
        $paymentMode = "Card";
    }

    checkmemberemailphoneverification($iUserId, "Passenger");

    // # Check Pickup Address For UberX##
    // $APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    if ($APP_TYPE == "UberX") {
        $Data['tUserComment'] = $tUserComment;
        if ($iUserAddressId != "") {

            // $pickUpLocAdd=get_value('user_address', 'vServiceAddress', '	iUserAddressId',$iUserAddressId,'','true');
            $Address = get_value('user_address', 'vAddressType,vBuildingNo,vLandmark,vServiceAddress,vLatitude,vLongitude', '	iUserAddressId', $iUserAddressId, '', '');
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
        } else {
            $Data['vSourceAddresss'] = $pickUpLocAdd;
        }

        $eAutoAssign = 'No';
    } else {
        $Data['vSourceAddresss'] = $pickUpLocAdd;
        $eAutoAssign = 'Yes';
    }

    // ## Checking For Pickup And DropOff Disallow ###
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

    // ## Checking For Pickup And DropOff Disallow ###
    // # Check Pickup Address For UberX##
    // # Check For PichUp/DropOff Location DisAllow ##
    $address_data['PickUpAddress'] = $pickUpLocAdd;
    $address_data['DropOffAddress'] = $destLocAdd;
    $DataArr = getOnlineDriverArr($pickUpLatitude, $pickUpLongitude, $address_data, "Yes", "No", "No", "", $destLatitude, $destLongitude);
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

    // # Check For PichUp/DropOff Location DisAllow Ends##
    if ($APP_TYPE == "UberX") {
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
        /* if($datediff < 3600){
          $returnArr['Action'] = "0";
          $returnArr['message'] = "LBL_SCHEDULE_TIME_NOT_AVAILABLE";
          setDataResponse($returnArr);
          } */
    }

    $Booking_Date_Time = $scheduleDate;
    $systemTimeZone = date_default_timezone_get();

    // echo "hererrrrr:::".$systemTimeZone;exit;
    $scheduleDate = converToTz($scheduleDate, $systemTimeZone, $vTimeZone);

    // $pickUpDateTime = convertTimeZone("2016-29-14 15:29:41","Asia/Calcutta");
    // date_default_timezone_set($timeZone);
    // echo gmdate('Y-m-d H:i', strtotime($scheduleDate));exit;
    // echo "hererrrrr:::".$pickUpDateTime;exit;
    /* $ePickStatus=get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId',$iVehicleTypeId,'','true');
      $eNightStatus=get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId',$iVehicleTypeId,'','true'); */
    $SurchargeDetail = get_value('vehicle_type', 'ePickStatus,eNightStatus', 'iVehicleTypeId', $iVehicleTypeId);
    $ePickStatus = $SurchargeDetail[0]['ePickStatus'];
    $eNightStatus = $SurchargeDetail[0]['eNightStatus'];
    $fPickUpPrice = 1;
    $fNightPrice = 1;

    // # Checking For Flat Trip ##
    $data_flattrip = checkFlatTripnew($pickuplocationarr, $dropofflocationarr, $iVehicleTypeId);
    $eFlatTrip = $data_flattrip['eFlatTrip'];
    $fFlatTripPrice = $data_flattrip['Flatfare'];

    // # Checking For Flat Trip ##
    $data_surgePrice = checkSurgePrice($selectedCarTypeID, $scheduleDate);
    if ($data_surgePrice['Action'] == "0") {
        if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
            $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
        } else {
            $fNightPrice = $data_surgePrice['SurgePriceValue'];
        }
    }

    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
        $fPickUpPrice = 1;
        $fNightPrice = 1;
    }

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
    } else {
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

    // $Data['vSourceAddresss']=$pickUpLocAdd;
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

    // $Data['fTollPrice']=$fTollPrice;
    // $Data['vTollPriceCurrencyCode']=$vTollPriceCurrencyCode;
    // $Data['eTollSkipped']=$eTollSkipped;
    $Data['iDriverId'] = $iDriverId;
    $Data['vTimeZone'] = $vTimeZone;
    $Data['eFemaleDriverRequest'] = $PreferFemaleDriverEnable;
    $Data['eHandiCapAccessibility'] = $HandicapPrefEnabled;
    $Data['eFlatTrip'] = $eFlatTrip;
    $Data['fFlatTripPrice'] = $fFlatTripPrice;
    if ($eType == "Deliver") {
        $Data['iPackageTypeId'] = $iPackageTypeId;
        $Data['vReceiverName'] = $vReceiverName;
        $Data['vReceiverMobile'] = $vReceiverMobile;
        $Data['tPickUpIns'] = $tPickUpIns;
        $Data['tDeliveryIns'] = $tDeliveryIns;
        $Data['tPackageDetails'] = $tPackageDetails;
    }

    if ($action == "Add") {
        $Data['vBookingNo'] = $rand_num;
        $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'insert');
    } else {
        $Data['eStatus'] = "Pending";
        $Data['iCancelByUserId'] = "";
        $Data['vCancelReason'] = "";
        $where = " iCabBookingId = '" . $iCabBookingId . "'";
        $id = $obj->MySQLQueryPerform("cab_booking", $Data, 'update', $where);
    }

    if ($id > 0) {
        $returnArr["Action"] = "1";
        if ($APP_TYPE == "UberX") {
            $returnArr['message'] = "LBL_BOOKING_SUCESS_NOTE";
        } else {
            $returnArr['message'] = $eType == "Deliver" ? "LBL_DELIVERY_BOOKED" : "LBL_RIDE_BOOKED_DL";
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

        // $Data1['tDestAddress']=$destLocAdd;
        // $Data1['dBookingdate']=date('Y-m-d H:i', strtotime($scheduleDate));
        $Data1['dBookingdate'] = date('Y-m-d H:i', strtotime($Booking_Date_Time));
        if ($action == "Add") {
            $Data1['vBookingNo'] = $rand_num;
        } else {
            $BookingNo = get_value('cab_booking', 'vBookingNo', 'iCabBookingId', $iCabBookingId, '', 'true');
            $Data1['vBookingNo'] = $BookingNo;
        }

        $query = "SELECT vLicencePlate FROM driver_vehicle WHERE iDriverVehicleId=" . $iVehicleTypeId;
        $db_driver_vehicles = $obj->MySQLSelect($query);
        if ($APP_TYPE == "UberX") {
            $sendMailfromDriver = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER_APP_SP", $Data1);
        } else {
            $sendMailfromDriver = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_DRIVER_APP", $Data1);
            $sendMailfromUser = $generalobj->send_email_user("MANUAL_TAXI_DISPATCH_RIDER_APP", $Data1);
        }

        if ($APP_TYPE != "UberX") {
            $maildata['DRIVER_NAME'] = $Data1['vDriver'];

            // $maildata['PLATE_NUMBER'] = $db_driver_vehicles[0]['vLicencePlate'];
            $maildata['BOOKING_DATE'] = $Booking_Date;
            $maildata['BOOKING_TIME'] = $Booking_Time;
            $maildata['BOOKING_NUMBER'] = $Data1['vBookingNo'];
            $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_APP", $maildata, "", $UserLang);
            $UsersendMessage = $generalobj->sendSystemSms($userPhoneNo, $userPhoneCode, $message_layout); //added by SP for sms functionality on 15-7-2019
            /* $UsersendMessage = $generalobj->sendUserSMS($userPhoneNo, $userPhoneCode, $message_layout, "");
              if ($UsersendMessage == 0) {

              // $isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
              $isdCode = $SITE_ISD_CODE;
              $userPhoneCode = $isdCode;
              $UsersendMessage = $generalobj->sendUserSMS($userPhoneNo, $userPhoneCode, $message_layout, "");
              } */
        }

        $maildata1['PASSENGER_NAME'] = $Data1['vRider'];
        $maildata1['BOOKING_DATE'] = $Booking_Date;
        $maildata1['BOOKING_TIME'] = $Booking_Time;
        $maildata1['BOOKING_NUMBER'] = $Data1['vBookingNo'];
        $DRIVER_SMS_TEMPLATE = ($APP_TYPE == "UberX") ? "DRIVER_SEND_MESSAGE_SP" : "DRIVER_SEND_MESSAGE";
        $message_layout = $generalobj->send_messages_user($DRIVER_SMS_TEMPLATE, $maildata1, "", $DriverLang);
        $DriversendMessage = $generalobj->sendSystemSms($DriverPhoneNo, $DriverPhoneCode, $message_layout); //added by SP for sms functionality on 15-7-2019
        /* $DriversendMessage = $generalobj->sendUserSMS($DriverPhoneNo, $DriverPhoneCode, $message_layout, "");
          if ($DriversendMessage == 0) {

          // $isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
          $isdCode = $SITE_ISD_CODE;
          $DriverPhoneCode = $isdCode;
          $UsersendMessage = $generalobj->sendUserSMS($DriverPhoneNo, $DriverPhoneCode, $message_layout, "");
          } */
    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "checkBookings") {
    global $generalobj;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $bookingType = isset($_REQUEST["bookingType"]) ? $_REQUEST["bookingType"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $dataType = isset($_REQUEST["DataType"]) ? $_REQUEST["DataType"] : '';

    // $APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    $per_page = 10;
    $additional_mins = $BOOKING_LATER_ACCEPT_AFTER_INTERVAL;
    $currDate = date('Y-m-d H:i:s');
    $currDate = date("Y-m-d H:i:s", strtotime($currDate . "-" . $additional_mins . " minutes"));
    $ssql1 = " AND dBooking_date > '" . $currDate . "'";
    $ssql2 = " AND cb.dBooking_date > '" . $currDate . "'";
    if ($UserType == "Driver") {
        if ($APP_TYPE == "UberX") {
            if ($dataType == "PENDING") {
                $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Pending' AND iDriverId='" . $iDriverId . "'" . $ssql1;
            } else {
                $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Accepted' AND iDriverId='" . $iDriverId . "'" . $ssql1;
            }
        } else {
            $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE iDriverId != '' AND eStatus = 'Assign' AND iDriverId='" . $iDriverId . "'" . $ssql1;
        }
    } else {
        $sql_all = "SELECT COUNT(iCabBookingId) As TotalIds FROM cab_booking WHERE  iUserId='$iUserId' AND  ( eStatus = 'Assign' OR eStatus = 'Pending' OR eStatus = 'Accepted' OR eStatus = 'Declined' OR eStatus = 'Cancel') $ssql1";
    }

    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    if ($UserType == "Driver") {
        if ($APP_TYPE == "UberX") {
            if ($dataType == "PENDING") {
                $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  cb.eStatus = 'Pending' AND cb.iDriverId='$iDriverId' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
            } else {
                $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  cb.eStatus = 'Accepted' AND cb.iDriverId='$iDriverId' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
            }
        } else {
            $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iDriverId != '' AND  cb.eStatus = 'Assign' AND cb.iDriverId='$iDriverId' $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
        }
    } else {
        $sql = "SELECT cb.* FROM `cab_booking` as cb  WHERE cb.iUserId='$iUserId' AND ( cb.eStatus = 'Assign' OR cb.eStatus = 'Pending' OR eStatus = 'Accepted' OR eStatus = 'Declined'  OR eStatus = 'Cancel' ) $ssql2 ORDER BY cb.iCabBookingId DESC" . $limit;
    }

    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);
    if (count($Data) > 0) {
        for ($i = 0; $i < count($Data); $i++) {
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
            $eType = $Data[$i]['eType'];
            $iVehicleTypeId = $Data[$i]['iVehicleTypeId'];
            $eFareType = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $iVehicleTypeId, '', 'true');
            $Data[$i]['eFareType'] = $eFareType;
            if ($eType == 'UberX') {
                $Data[$i]['tDestAddress'] = "";
                $DisplayBookingDetails = array();
                $DisplayBookingDetails = DisplayBookingDetails($Data[$i]['iCabBookingId']);
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
            }
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;
        if ($TotalPages > $page) {
            $returnArr['NextPage'] = $page + 1;
        } else {
            $returnArr['NextPage'] = "0";
        }
    } else {
        $returnArr['Action'] = "0";

        // $returnArr['message']= ($bookingType == "Ride" || $bookingType == "UberX")?"LBL_NO_BOOKINGS_AVAIL":"LBL_NO_DELIVERY_AVAIL";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "cancelBooking") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $Reason = isset($_REQUEST["Reason"]) ? $_REQUEST["Reason"] : '';

    // $APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $where = " iCabBookingId = '$iCabBookingId'";
    $data_update_booking['eStatus'] = "Cancel";
    $data_update_booking['vCancelReason'] = $Reason;
    $data_update_booking['iCancelByUserId'] = $iUserId;
    $data_update_booking['dCancelDate'] = @date("Y-m-d H:i:s");
    $data_update_booking['eCancelBy'] = $userType == "Driver" ? $userType : "Rider";
    $id = $obj->MySQLQueryPerform("cab_booking", $data_update_booking, 'update', $where);
    $sql = "select cb.iUserId, cb.iDriverId,cb.vBookingNo,concat(rd.vName,' ',rd.vLastName) as DriverName,concat(ru.vName,' ',ru.vLastName) as RiderName,ru.vEmail as vRiderMail,ru.vPhone as RiderPhone,ru.vPhoneCode as RiderPhoneCode,rd.vPhone as DriverPhone,rd.vCode as DriverPhoneCode,rd.vEmail as vDriverMail,rd.vLang as driverlang, ru.vLang as riderlang ,cb.vSourceAddresss,cb.tDestAddress,cb.dBooking_date,cb.vCancelReason,cb.dCancelDate from cab_booking cb
		left join register_driver rd on rd.iDriverId = cb.iDriverId
		left join register_user ru on ru.iUserId = cb.iUserId where cb.iCabBookingId = '$iCabBookingId'"; //added by SP cb.iUserId, cb.iDriverId for getting phoneocde in country table on 15-7-2019
    $data_cab = $obj->MySQLSelect($sql);
    $RiderPhoneNo = $data_cab[0]['RiderPhone'];
    $RiderPhoneCode = $data_cab[0]['RiderPhoneCode'];
    $UserLang = $data_cab[0]['riderlang'];
    $DriverPhoneNo = $data_cab[0]['DriverPhone'];
    $DriverPhoneCode = $data_cab[0]['DriverPhoneCode'];
    $DriverLang = $data_cab[0]['driverlang'];
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
    if ($userType == "Driver") {
        $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN", $Data);
    }

    if ($APP_TYPE == "UberX") {
        $USER_EMAIL_TEMPLATE = ($userType == "Driver") ? "MANUAL_BOOKING_CANCEL_BYDRIVER_SP" : "MANUAL_BOOKING_CANCEL_BYRIDER_SP";
        $generalobj->send_email_user($USER_EMAIL_TEMPLATE, $Data);
        //$UserPhoneNo = ($userType == "Driver") ? $RiderPhoneNo : $DriverPhoneNo;
        $UserPhoneNo = ($userType == "Driver") ? $DriverPhoneNo : $RiderPhoneNo;
        $UserPhoneCode = ($userType == "Driver") ? $RiderPhoneCode : $DriverPhoneCode;
        $USER_SMS_TEMPLATE = ($userType == "Driver") ? "BOOKING_CANCEL_BYDRIVER_MESSAGE_SP" : "BOOKING_CANCEL_BYRIDER_MESSAGE_SP";
        $message_layout = $generalobj->send_messages_user($USER_SMS_TEMPLATE, $Data, "", $UserLang);

        //added by SP for sms functionality on 15-7-2019 start
        if ($userType == "Driver") {
            $DriverData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = '" . $data_cab[0]['iDriverId'] . "' AND r.vCountry = c.vCountryCode");
            $UserPhoneCode = $DriverData[0]['vPhoneCode'];
        } else {
            $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM `register_user` AS r, `country` AS c WHERE r.iUserId = '" . $data_cab[0]['iUserId'] . "' AND r.vCountry = c.vCountryCode");
            $UserPhoneCode = $passengerData[0]['vPhoneCode'];
        }

        $UsersendMessage = $generalobj->sendSystemSms($UserPhoneNo, $UserPhoneCode, $message_layout);
        //added by SP for sms functionality on 15-7-2019 end

        /* $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
          if ($UsersendMessage == 0) {

          // $isdCode= $generalobj->getConfigurations("configurations","SITE_ISD_CODE");
          $isdCode = $SITE_ISD_CODE;
          $UserPhoneCode = $isdCode;
          $UsersendMessage = $generalobj->sendUserSMS($UserPhoneNo, $UserPhoneCode, $message_layout, "");
          } */
    }

    if ($id) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_BOOKING_CANCELED";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "loadPackageTypes") {
    $vehicleTypes = get_value('package_type', '*', 'eStatus', 'Active');
    if (count($vehicleTypes) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $vehicleTypes;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
if ($type == "loadDeliveryDetails") {
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $sql = "SELECT tr.vReceiverName,tr.vReceiverMobile,tr.tPickUpIns,tr.tDeliveryIns,tr.tPackageDetails,pt.vName as packageType,concat(ru.vName,' ',ru.vLastName) as senderName, concat('+',ru.vPhoneCode,'',ru.vPhone) as senderMobile, concat('" . $tconfig['tsite_upload_images_passenger'] . "/',ru.iUserId,'/',ru.vImgName) as vImage from trips as tr, register_user as ru, package_type as pt WHERE ru.iUserId = tr.iUserId AND tr.iTripId = '" . $iTripId . "' AND pt.iPackageTypeId = tr.iPackageTypeId";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0 && $iTripId != "") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
// ##########################################################################
if ($type == "checkFlatTrip") {
    $Source_point_Address = isset($_REQUEST["sAddress"]) ? $_REQUEST["sAddress"] : '';
    $Dest_point_Address = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';
    $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    $sourceLocation = isset($_REQUEST["sourceLocation"]) ? $_REQUEST["sourceLocation"] : '';
    $destinationLocation = isset($_REQUEST["destinationLocation"]) ? $_REQUEST["destinationLocation"] : '';
    $Source_point_AddressArr = explode(",", $sourceLocation);
    $Dest_point_AddressArr = explode(",", $destinationLocation);
    $data = checkFlatTripnew($Source_point_AddressArr, $Dest_point_AddressArr, $iVehicleTypeId);
    $fFlatTripPrice = $data['Flatfare'];
    $data['passenger_price'] = $currencySymbol . " " . number_format(($fFlatTripPrice * $priceRatio), 2);

    setDataResponse($data);
}

// ##########################################################################
// ##########################################################################
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

    // $user_available_balance = $generalobj->get_user_available_balance($iUserId,$UserType);
    // $sql = "SELECT tripRate.vRating1 as TripRating,tr.* FROM `trips` as tr,`ratings_user_driver` as tripRate  WHERE  tr.iUserId='$iUserId' AND tripRate.iTripId=tr.iTripId AND tripRate.eUserType='$UserType' AND (tr.iActive='Canceled' || tr.iActive='Finished') ORDER BY tr.iTripId DESC" . $limit;
    $sql = "SELECT * from user_wallet where iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ORDER BY iUserWalletId DESC" . $limit;
    $Data = $obj->MySQLSelect($sql);
    $totalNum = count($Data);
    $vSymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
    if ($UserType == 'Driver') {
        /* $uservSymbol = get_value('register_driver', 'vCurrencyDriver', 'iDriverId',$iUserId,'','true');
          $vLangCode = get_value('register_driver', 'vLang', 'iDriverId',$iUserId,'','true'); */
        $UserData = get_value('register_driver', 'vCurrencyDriver,vLang', 'iDriverId', $iUserId);
        $uservSymbol = $UserData[0]['vCurrencyDriver'];
        $vLangCode = $UserData[0]['vLang'];
    } else {
        /* $uservSymbol = get_value('register_user', 'vCurrencyPassenger', 'iUserId',$iUserId,'','true');
          $vLangCode = get_value('register_user', 'vLang', 'iUserId',$iUserId,'','true'); */
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
            } else {
                $row[$i]['currentbal'] = $prevbalance - $row[$i]['iBalance'];
            }

            $prevbalance = $row[$i]['currentbal'];
            $row[$i]['dDateOrig'] = $row[$i]['dDate'];
            $row[$i]['dDate'] = date('d-M-Y', strtotime($row[$i]['dDate']));

            // $row[$i]['currentbal'] = $vSymbol.$row[$i]['currentbal'];
            // $row[$i]['iBalance'] = $vSymbol.$row[$i]['iBalance'];
            $row[$i]['currentbal'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['currentbal'], $uservSymbol);
            $row[$i]['iBalance'] = $generalobj->userwalletcurrency($row[$i]['fRatio_' . $uservSymbol], $row[$i]['iBalance'], $uservSymbol);
            $i++;
        }

        // $returnData['message'] = array_reverse($row);
        $returnData['message'] = $row;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        } else {
            $returnData['NextPage'] = 0;
        }

        // $returnData['user_available_balance_default']=$vSymbol.$user_available_balance;
        // $returnData['user_available_balance'] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$uservSymbol));
        $user_available_balance = $generalobj->get_user_available_balance_app_display($iUserId, $UserType, '', 'Yes');
        $returnData['user_available_balance_default'] = $user_available_balance['DISPLAY_AMOUNT'];
        $returnData['user_available_balance'] = strval($user_available_balance['DISPLAY_AMOUNT']);

        $returnData["MemberBalance"] = strval($user_available_balance['DISPLAY_AMOUNT']);
        $returnData['user_available_balance'] = strval($user_available_balance['DISPLAY_AMOUNT']);
        $returnData['user_available_balance_amount'] = strval($user_available_balance['ORIG_AMOUNT']);
        $returnData['Action'] = "1";

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
            $returnData['NON_WITHDRAWABLE_AMOUNT'] = $vSymbol . ' ' . ($non_withdrawable_amount * $Ratio);

            $vAccountNumber = get_value('register_driver', 'vAccountNumber', 'iDriverId', $iUserId);
            $returnData['ACCOUNT_NO'] = ($vAccountNumber[0]['vAccountNumber'] != "") ? $vAccountNumber[0]['vAccountNumber'] : 'XXXXXXX';
        }
        setDataResponse($returnData);
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_TRANSACTION_AVAIL";
        $returnData['user_available_balance'] = $userCurrencySymbol . "0.00";
        setDataResponse($returnData);
    }
}

// ##########################################################################
// ########################### UBER-For-X ################################

if ($type == "getServiceCategories") {
    global $generalobj;
    $parentId = isset($_REQUEST['parentId']) ? clean($_REQUEST['parentId']) : 0;
    $userId = isset($_REQUEST['userId']) ? clean($_REQUEST['userId']) : '';
    if ($userId != "") {
        $sql1 = "SELECT vLang FROM `register_user` WHERE iUserId='$userId'";
        $row = $obj->MySQLSelect($sql1);
        $lang = $row[0]['vLang'];
    }
    if ($lang == "") {
        // $lang = "EN";
        $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    if ($parentId == "" || $parentId == NULL) {
        $parentId = 0;
    }

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    $sql2 = "SELECT iVehicleCategoryId, vLogo,vCategory_" . $lang . " as vCategory,eStatus FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND iParentId='$parentId' ";
    $Data = $obj->MySQLSelect($sql2);
    //Added By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code Start
    $Data3 = $obj->MySQLSelect("SELECT iVehicleTypeId,iVehicleCategoryId FROM vehicle_type WHERE eStatus='Active' ORDER BY iDisplayOrder ASC");
    $categoryArr = $Datacategory = array();
    for ($vc = 0; $vc < count($Data3); $vc++) {
        $categoryArr[$Data3[$vc]['iVehicleCategoryId']][] = $Data3[$vc];
    }
    //Added By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code End
    //print_r($Data);die;
    $Datacategory = array();
    if ($parentId == 0) {
        if (count($Data) > 0) {
            $k = 0;
            for ($i = 0; $i < count($Data); $i++) {
                $sql3 = "SELECT iVehicleCategoryId, vLogo,vCategory_" . $lang . " as vCategory FROM " . $sql_vehicle_category_table_name . " WHERE eStatus='Active' AND iParentId='" . $Data[$i]['iVehicleCategoryId'] . "'";
                $Data2 = $obj->MySQLSelect($sql3);
                if (count($Data2) > 0) {
                    for ($j = 0; $j < count($Data2); $j++) {
                        //Removed By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code Start
                        //$sql4 = "SELECT iVehicleTypeId FROM vehicle_type WHERE eStatus='Active' AND iVehicleCategoryId='" . $Data2[$j]['iVehicleCategoryId'] . "'";
                        //$Data3 = $obj->MySQLSelect($sql4);
                        //Removed By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code End
                        //Added By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code Start
                        $Data3 = array();
                        if (isset($categoryArr[$Data[$j]['iVehicleCategoryId']])) {
                            $Data3 = $categoryArr[$Data[$j]['iVehicleCategoryId']];
                        }
                        //Added By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code End
                        if (count($Data3) > 0) {
                            $Datacategory[$k]['iVehicleCategoryId'] = $Data[$i]['iVehicleCategoryId'];
                            $Datacategory[$k]['vLogo'] = $Data[$i]['vLogo'];
                            $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$i]['iVehicleCategoryId'] . '/android/' . $Data[$i]['vLogo'];
                            $Datacategory[$k]['vCategory'] = $Data[$i]['vCategory'];
                            $Datacategory[$k]['eStatus'] = $Data[$i]['eStatus'];
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
                //Removed By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code Start
                //$sql4 = "SELECT iVehicleTypeId FROM vehicle_type WHERE eStatus='Active' AND iVehicleCategoryId='" . $Data[$j]['iVehicleCategoryId'] . "'";
                //$Data3 = $obj->MySQLSelect($sql4);
                //Removed By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code End
                //Added By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code Start
                $Data3 = array();
                if (isset($categoryArr[$Data[$j]['iVehicleCategoryId']])) {
                    $Data3 = $categoryArr[$Data[$j]['iVehicleCategoryId']];
                }
                //Added By HJ On 11-07-2019 For Get All Vehicle Type For Optimized Code End
                if (count($Data3) > 0) {
                    $Datacategory[$k]['iVehicleCategoryId'] = $Data[$j]['iVehicleCategoryId'];
                    $Datacategory[$k]['vLogo'] = $Data[$j]['vLogo'];
                    $Datacategory[$k]['vLogo_image'] = $tconfig['tsite_upload_images_vehicle_category'] . '/' . $Data[$j]['iVehicleCategoryId'] . '/android/' . $Data[$j]['vLogo'];
                    $Datacategory[$k]['vCategory'] = $Data[$j]['vCategory'];
                    $Datacategory[$k]['eStatus'] = $Data[$j]['eStatus'];
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
    $returnArr['message'] = array_reverse($DatanewArr);
    setDataResponse($returnArr);
}

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

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
    if ($eCheck == "" || $eCheck == NULL) {
        $eCheck = "No";
    }
    if ($eCheck == "Yes") {
        // $allowed_ans = checkRestrictedAreaNew($pickuplocationarr,"No");
        $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
        if ($allowed_ans == "Yes") {
            $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) AND iVehicleCategoryId = '" . $iVehicleCategoryId . "' ORDER BY iVehicleTypeId ASC";
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
                $lang = "EN";
            }
            $vCurrencyPassenger = $row[0]['vCurrencyPassenger'];
            if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
                $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
            }
            $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyPassenger);
            $priceRatio = $UserCurrencyData[0]['Ratio'];
            $vSymbol = $UserCurrencyData[0]['vSymbol'];
            $vehicleCategoryData = get_value($sql_vehicle_category_table_name, "vCategoryTitle_" . $lang . " as vCategoryTitle, tCategoryDesc_" . $lang . " as tCategoryDesc", 'iVehicleCategoryId', $iVehicleCategoryId);
            $vCategoryTitle = $vehicleCategoryData[0]['vCategoryTitle'];
            $vCategoryDesc = $vehicleCategoryData[0]['tCategoryDesc'];
            $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.ePriceType, vt.iVehicleTypeId, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM " . $sql_vehicle_category_table_name . " as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vc.eStatus='Active' AND vt.eStatus='Active' AND vt.iVehicleCategoryId='$iVehicleCategoryId' AND vt.iLocationid IN ($GetVehicleIdfromGeoLocation)";
            // AND vt.eType='UberX'
            $Data = $obj->MySQLSelect($sql2);
            if (!empty($Data)) {
                for ($i = 0; $i < count($Data); $i++) {
                    $Data[$i]['fFixedFare_value'] = round($Data[$i]['fFixedFare'] * $priceRatio, 2);
                    $fFixedFare = round($Data[$i]['fFixedFare'] * $priceRatio, 2);
                    $Data[$i]['fFixedFare'] = $vSymbol . formatNum($fFixedFare);
                    $Data[$i]['fPricePerHour_value'] = round($Data[$i]['fPricePerHour'] * $priceRatio, 2);
                    $fPricePerHour = round($Data[$i]['fPricePerHour'] * $priceRatio, 2);
                    $Data[$i]['fPricePerHour'] = $vSymbol . formatNum($fPricePerHour);
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
                        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
                    }
                    $Data[$i]['ePriceType'] = $ePriceType;
                    $Data[$i]['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ePriceType == "Provider" ? "Yes" : "No";
                    // $Data[$i]['ALLOW_SERVICE_PROVIDER_AMOUNT']= $Data[$i]['ePriceType'] == "Provider"? "Yes" :"No";
                }
                $returnArr['Action'] = "1";
                $returnArr['message'] = $Data;
                // $returnArr['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ALLOW_SERVICE_PROVIDER_AMOUNT;
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

if ($type == "getBanners") {
    global $generalobj;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    if ($iMemberId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    }
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    // $banners= get_value('banners', 'vImage', 'vCode',$vLanguage,' ORDER BY iDisplayOrder ASC');
    $sql = "SELECT vImage FROM `banners` WHERE vCode = '" . $vLanguage . "' AND eStatus = 'Active' AND iServiceId = '" . $iServiceId . "' ORDER BY iDisplayOrder ASC";
    $banners = $obj->MySQLSelect($sql);
    $data = array();
    $count = 0;
    for ($i = 0; $i < count($banners); $i++) {
        if ($banners[$i]['vImage'] != "") {
            $data[$count]['vImage'] = $tconfig["tsite_url"] . 'assets/img/images/' . urlencode($banners[$i]['vImage']);
            $count++;
        }
    }

    if (empty($data)) {
        $data = '';
    }

    $returnArr['Action'] = "1";
    $returnArr['message'] = $data;
    /* }else{
      $returnArr['Action']="0";
      $returnArr['message'] ="LBL_TRY_AGAIN_LATER_TXT";
      } */
    setDataResponse($returnArr);
}

if ($type == "getUserVehicleDetails") {
    global $generalobj;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $user_type = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    $vCountry = '';
    if ($user_type == "Passenger") {
        $tblname = "register_user";
        $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    } else {
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
    $sql = "SELECT iVehicleTypeId,vVehicleType_" . $vLangCode . " as vVehicleType,iLocationid,iCountryId,iStateId,iCityId,eType FROM `vehicle_type` WHERE eType = 'DeliverAll'";
    $db_vehicletype = $obj->MySQLSelect($sql);
    $sql1 = "select * from make where eStatus = 'Active' ORDER BY vMake ASC ";
    $make = $obj->MySQLSelect($sql1);
    $start = @date('Y');
    $end = '1970';
    $year = array();
    for ($j = $start; $j >= $end; $j--) {
        $year[] = strval($j);
    }

    $carlist = array();
    if (count($make) > 0) {
        for ($i = 0; $i < count($make); $i++) {
            $sql = "SELECT  * FROM  `model` WHERE iMakeId = '" . $make[$i]['iMakeId'] . "' AND `eStatus` = 'Active' ORDER BY vTitle ASC ";
            $db_model = $obj->MySQLSelect($sql);
            $ModelArr['List'] = $db_model;
            $carlist[$i]['iMakeId'] = $make[$i]['iMakeId'];
            $carlist[$i]['vMake'] = $make[$i]['vMake'];
            $carlist[$i]['vModellist'] = $ModelArr['List'];
        }

        $data['year'] = $year;
        $data['carlist'] = $carlist;
        $data['vehicletypelist'] = $db_vehicletype;
        if (count($db_vehicletype) == 0) {
            $returnArr['message1'] = "LBL_EDIT_VEHI_RESTRICTION_TXT";
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $data;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ##########################Add/Edit Driver Vehicle#######################################################
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

    // $eStatus = ($generalobj->getConfigurations("configurations", "VEHICLE_AUTO_ACTIVATION") == 'Yes') ? 'Active' : 'Inactive';
    $vCarType = isset($_REQUEST["vCarType"]) ? $_REQUEST["vCarType"] : '';
    $handiCap = isset($_REQUEST["HandiCap"]) ? $_REQUEST["HandiCap"] : 'No';
    $iVehicleCategoryId = isset($_REQUEST["iVehicleCategoryId"]) ? $_REQUEST["iVehicleCategoryId"] : '';
    $action = ($iDriverVehicleId != 0) ? 'Edit' : 'Add';
    if ($action == "Add") {
        $eStatus = "Inactive";
    }
    $sql = "select iCompanyId,iDriverVehicleId,vAvailability from `register_driver` where iDriverId = '" . $iDriverId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $SelctediDriverVehicleId = $iCompanyId = 0;
    $vAvailability = "Not Available";
    if (count($db_usr) > 0) {
        $SelctediDriverVehicleId = $db_usr[0]['iDriverVehicleId'];
        $vAvailability = $db_usr[0]['vAvailability'];
        $iCompanyId = $db_usr[0]['iCompanyId'];
    }
    if ($action == "Edit" && $ENABLE_EDIT_DRIVER_VEHICLE == "No" && $APP_TYPE != "UberX") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_EDIT_VEHICLE_DISABLED";
        setDataResponse($returnArr);
    } else if ($APP_TYPE == "UberX" && $action == "Edit" && $ENABLE_EDIT_DRIVER_SERVICE == "No") { // Added By HJ On 10-08-2019 For Check Permission As Per Discuss With KS
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_EDIT_SERVICE_DISABLED";
        setDataResponse($returnArr);
    }
    if ($action == "Edit" && $iDriverVehicleId == $SelctediDriverVehicleId && $vAvailability == "Available") {
        //$SelctediDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_DELETE_VEHICLE_RESTRICT_NOTE";
        setDataResponse($returnArr);
    }
    //$sql = "select iCompanyId from `register_driver` where iDriverId = '" . $iDriverId . "'";
    //$db_usr = $obj->MySQLSelect($sql);
    //$iCompanyId = $db_usr[0]['iCompanyId'];
    $Data_Driver_Vehicle['iDriverId'] = $iDriverId;
    $Data_Driver_Vehicle['iCompanyId'] = $iCompanyId;
    if (SITE_TYPE == "Demo") {
        $Data_Driver_Vehicle['eStatus'] = "Active";
    } else {
        if ($action == "Add") {
            $Data_Driver_Vehicle['eStatus'] = $eStatus;
        }
    }

    $Data_Driver_Vehicle['eCarX'] = $eCarX;
    $Data_Driver_Vehicle['eCarGo'] = $eCarGo;
    $Data_Driver_Vehicle['vCarType'] = $vCarType;
    $Data_Driver_Vehicle['eHandiCapAccessibility'] = $handiCap;
    if ($iMakeId != "") {
        $Data_Driver_Vehicle['iMakeId'] = $iMakeId;
    }

    if ($iModelId != "") {
        $Data_Driver_Vehicle['iModelId'] = $iModelId;
    }

    if ($iYear != "") {
        $Data_Driver_Vehicle['iYear'] = $iYear;
    }

    if ($vColour != "") {
        $Data_Driver_Vehicle['vColour'] = $vColour;
    }

    if ($vLicencePlate != "") {
        $Data_Driver_Vehicle['vLicencePlate'] = $vLicencePlate;
    }

    // $Data_Driver_Vehicle['vColour'] = $vColour;
    // $Data_Driver_Vehicle['vLicencePlate'] = $vLicencePlate;
    if ($action == "Add") {
        $id = $obj->MySQLQueryPerform("driver_vehicle", $Data_Driver_Vehicle, 'insert');
    } else {
        $where = " iDriverVehicleId = '" . $iDriverVehicleId . "'";
        $id = $obj->MySQLQueryPerform("driver_vehicle", $Data_Driver_Vehicle, 'update', $where);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = ($action == 'Add') ? 'LBL_VEHICLE_ADD_SUCCESS_NOTE' : 'LBL_VEHICLE_UPDATE_SUCCESS';
        $returnArr['VehicleInsertId'] = $id;
        $returnArr['VehicleStatus'] = $Data_Driver_Vehicle['eStatus'];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ##########################Add/Edit Driver Vehicle End#######################################################
// ###############################Delete Driver Vehicle###############################################################
if ($type == 'deletedrivervehicle') {
    global $generalobj, $tconfig, $obj;
    $returnArr = array();
    $iMemberCarId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';

    // getLanguageCode($iMemberId); //create array of language_label
    //$iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    //Added By HJ On 19-07-2019 For Check Driver Vehicle Availability As Per Discuss With KS Start
    $getDriverData = $obj->MySQLSelect("SELECT iDriverVehicleId,vAvailability FROM register_driver WHERE iDriverId='" . $iDriverId . "'");
    $iDriverVehicleId = 0;
    $vAvailability = "Not Available";
    if (count($getDriverData) > 0) {
        $iDriverVehicleId = $getDriverData[0]['iDriverVehicleId'];
        $vAvailability = $getDriverData[0]['vAvailability'];
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
    // $sql = "DELETE FROM driver_vehicle WHERE iDriverVehicleId='" . $iMemberCarId . "' AND iDriverId='" . $iDriverId . "'";
    $sql = "UPDATE driver_vehicle set eStatus='Deleted' WHERE iDriverVehicleId='" . $iMemberCarId . "' AND iDriverId = '" . $iDriverId . "'";
    $db_sql = $obj->sql_query($sql);

    // if (mysql_affected_rows() > 0) {
    if ($obj->GetAffectedRows() > 0) {
        $returnArr['Action'] = 1;
        $returnArr['message'] = "LBL_DELETE_VEHICLE";
    } else {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ##########################displayDocList##########################################################
if ($type == "displayDocList") {
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    $doc_usertype = isset($_REQUEST['doc_usertype']) ? clean(strtolower($_REQUEST['doc_usertype'])) : 'driver';
    if ($doc_usertype == "vehicle") {
        $doc_usertype = "car";
    }

    $doc_userid = ($doc_usertype == 'car') ? $iDriverVehicleId : $iMemberId;

    // $APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    /* $vCountry = get_value('register_driver', 'vCountry', 'iDriverId', $iMemberId,'',true);
      $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId,'',true); */
    $UserData = get_value('register_driver', 'vCountry,vLang', 'iDriverId', $iMemberId);
    $vCountry = $UserData[0]['vCountry'];
    $vLang = $UserData[0]['vLang'];
    if ($vLang == '' || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name_" . $vLang . " as doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $doc_userid . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='" . $doc_usertype . "' AND (dm.country='" . $vCountry . "' OR dm.country='All') and dm.status='Active' ";
    $db_vehicle = $obj->MySQLSelect($sql1);
    if (count($db_vehicle) > 0) {

        // $Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc']."/".$iMemberId."/";
        if ($doc_usertype == "driver") {
            $Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc'] . "/" . $iMemberId . "/";
        } else {
            $Photo_Gallery_folder = $tconfig['tsite_upload_vehicle_doc_panel'] . "/" . $iDriverVehicleId . "/";
        }

        for ($i = 0; $i < count($db_vehicle); $i++) {
            if ($db_vehicle[$i]['doc_file'] != "") {
                $db_vehicle[$i]['vimage'] = $Photo_Gallery_folder . $db_vehicle[$i]['doc_file'];
            } else {
                $db_vehicle[$i]['vimage'] = "";
            }

            // # Checking for expire date of document ##
            $ex_date = $db_vehicle[$i]['ex_date'];
            $todaydate = date('Y-m-d');
            if ($ex_date == "" || $ex_date == "0000-00-00" || $db_vehicle[$i]['ex_date'] == "0000-00-00" || $db_vehicle[$i]['ex_date'] == "1970-01-01") {
                $expire_document = "No";
            } else {
                if (strtotime($ex_date) < strtotime($todaydate)) {
                    $expire_document = "Yes";
                } else {
                    $expire_document = "No";
                }
            }
            $db_vehicle[$i]['exp_date'] = "";
            if ($ex_date != "0000-00-00") {
                $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
                $expireLabel = $languageLabelsArr['LBL_EXPIRE_TXT'];
                //$newFormat = date("jS F Y", strtotime($db_vehicle[$i]['ex_date']));
                $newFormat = date("d M, Y (D)", strtotime($db_vehicle[$i]['ex_date']));
                $db_vehicle[$i]['exp_date'] = $expireLabel . ": " . $newFormat;
            }
            $db_vehicle[$i]['EXPIRE_DOCUMENT'] = $expire_document;

            // # Checking for expire date of document ##
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DOC_AVAIL";
    }


    setDataResponse($returnArr);
}

// ###################################################################################################
// ##########################displaydrivervehicles##########################################################
if ($type == "displaydrivervehicles") {
    global $generalobj, $tconfig;

    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $eType = isset($_REQUEST["eType"]) ? $_REQUEST["eType"] : 'Ride'; //'Ride', 'Delivery', 'UberX'
    $ssql = "";

    if ($eType == "UberX") {
        $ssql .= " AND dv.eType = 'UberX'";
    } else {
        $ssql .= " AND dv.eType != 'UberX'";
    }

    $sql = "select iCompanyId from `register_driver` where iDriverId = '" . $iMemberId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
    if ($APP_TYPE == 'UberX') {
        $sql = "SELECT *,eChildSeatAvailable AS eChildAccessibility,eWheelChairAvailable AS eWheelChairAccessibility FROM driver_vehicle where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $iMemberId . "' and eStatus != 'Deleted'";
        $db_vehicle = $obj->MySQLSelect($sql);
    } else {

        $sql = "SELECT m.vTitle, mk.vMake,dv.*,eChildSeatAvailable AS eChildAccessibility,eWheelChairAvailable AS eWheelChairAccessibility,case WHEN (dv.vInsurance='' OR dv.vPermit='' OR dv.vRegisteration='') THEN 'TRUE' ELSE 'FALSE' END as 'VEHICLE_DOCUMENT' FROM driver_vehicle as dv JOIN model m ON dv.iModelId=m.iModelId JOIN make mk ON dv.iMakeId=mk.iMakeId where iCompanyId = '" . $iCompanyId . "' and iDriverId = '" . $iMemberId . "' and dv.eStatus != 'Deleted' $ssql Order By dv.iDriverVehicleId desc";

        $db_vehicle = $obj->MySQLSelect($sql);
        $db_vehicle_new = $db_vehicle;
        for ($i = 0; $i < count($db_vehicle); $i++) {
            $vCarType = $db_vehicle[$i]['vCarType'];
            $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
            $db_cartype = $obj->MySQLSelect($sql);
            $k = 0;
            if (count($db_cartype) > 0) {
                for ($j = 0; $j < count($db_cartype); $j++) {
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
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_VEHICLES_FOUND";
    }

    setDataResponse($returnArr);
}
// ##########################Display Driver's Vehicle Listing End##########################################################
// ##########################Add/Update Driver's Document and Vehilcle Document ##########################################################
if ($type == "uploaddrivedocument") {
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? clean($_REQUEST['iDriverVehicleId']) : '';
    //echo "<pre>";print_r($_FILES);die;
    // $doc_userid = isset($_REQUEST['doc_userid']) ? clean($_REQUEST['doc_userid']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $doc_usertype = isset($_REQUEST['doc_usertype']) ? clean(strtolower($_REQUEST['doc_usertype'])) : 'driver'; // vehicle OR driver
    $doc_masterid = isset($_REQUEST['doc_masterid']) ? clean($_REQUEST['doc_masterid']) : '';
    $doc_name = isset($_REQUEST['doc_name']) ? clean($_REQUEST['doc_name']) : '';
    $doc_id = isset($_REQUEST['doc_id']) ? clean($_REQUEST['doc_id']) : '';
    $doc_file = isset($_REQUEST['doc_file']) ? clean($_REQUEST['doc_file']) : '';
    $ex_date = isset($_REQUEST['ex_date']) ? clean($_REQUEST['ex_date']) : '';
    $ex_status = isset($_REQUEST['ex_status']) ? clean($_REQUEST['ex_status']) : '';
    if ($doc_usertype == "vehicle") {
        $doc_usertype = "car";
    }

    $doc_userid = ($doc_usertype == 'car') ? $iDriverVehicleId : $iMemberId;
    $status = ($doc_usertype == 'car') ? "Active" : "Inctive";
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $action = ($doc_id != '') ? 'Edit' : 'Add';
    $addupdatemode = ($action == 'Add') ? 'insert' : 'update';
    if ($doc_file != "") {
        $vImageName = $doc_file;
    } else {
        if ($doc_usertype == "driver") {
            $Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc_path'] . "/" . $iMemberId . "/";
        } else {
            $Photo_Gallery_folder = $tconfig['tsite_upload_vehicle_doc'] . "/" . $iDriverVehicleId . "/";
        }

        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }

        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "bmp,pdf,doc,docx,jpg,jpeg,gif,png,xls,xlsx,csv");
        $vImageName = $vFile[0];
    }

    if ($vImageName != '') {
        $Data_Update["doc_masterid"] = $doc_masterid;
        $Data_Update["doc_usertype"] = $doc_usertype;
        $Data_Update["doc_userid"] = $doc_userid;
        $Data_Update["ex_date"] = $ex_date;
        $Data_Update["doc_file"] = $vImageName;
        $Data_Update["edate"] = @date("Y-m-d H:i:s");
        if ($action == "Add") {
            $Data_Update["status"] = $status;
            $id = $obj->MySQLQueryPerform("document_list", $Data_Update, 'insert');
        } else {
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

            // $returnArr['message'] = getDriverDetailInfo($iMemberId);
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

// ##########################Add/Update Driver's Document and Vehilcle Document Ends##########################################################
// ##########################Add/Update User's Vehicle Listing End##########################################################
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

    // $vImage = isset($_REQUEST["vImage"]) ? $_REQUEST["vImage"] : '';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $Photo_Gallery_folder = $tconfig['tsite_upload_images_passenger_vehicle'] . "/" . $iUserVehicleId . "/"; // /webimages/upload/uservehicle
    // echo $Photo_Gallery_folder."===";
    if (!is_dir($Photo_Gallery_folder))
        mkdir($Photo_Gallery_folder, 0777);
    $action = ($iUserVehicleId != '') ? 'Edit' : 'Add';
    $Data_User_Vehicle['iUserId'] = $iUserId;
    $Data_User_Vehicle['iMakeId'] = $iMakeId;
    $Data_User_Vehicle['iModelId'] = $iModelId;
    $Data_User_Vehicle['iYear'] = $iYear;
    $Data_User_Vehicle['vLicencePlate'] = $vLicencePlate;
    $Data_User_Vehicle['eStatus'] = $eStatus;
    $Data_User_Vehicle['vColour'] = $vColour;

    // $Data_User_Vehicle['vImage']=$vImage;
    if ($action == "Add") {
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_User_Vehicle, 'insert');
        $updateimageid = $id;
    } else {
        $where = " iUserVehicleId = '" . $iUserVehicleId . "'";
        $updateimageid = $iUserVehicleId;
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_User_Vehicle, 'update', $where);
    }

    if ($image_name != "") {
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "bmp,pdf,doc,docx,jpg,jpeg,gif,png,xls,xlsx,csv");
        $vImageName = $vFile[0];
        $Data_passenger["vImage"] = $vImageName;
        $where_image = " iUserVehicleId = '" . $updateimageid . "'";
        $id = $obj->MySQLQueryPerform("user_vehicle", $Data_passenger, 'update', $where_image);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

if ($type == "displayuservehicles") {
    global $generalobj, $tconfig;
    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    $sql = "SELECT m.vTitle, mk.vMake,uv.*  FROM user_vehicle as uv JOIN model m ON uv.iModelId=m.iModelId JOIN make mk ON uv.iMakeId=mk.iMakeId where iUserId = '" . $iUserId . "' and uv.eStatus != 'Deleted'";
    $db_vehicle = $obj->MySQLSelect($sql);
    if (count($db_vehicle) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "No Vehicles Found";
    }


    setDataResponse($returnArr);
}


if ($type == 'changelanguagelabel') {
    $vLang = isset($_REQUEST['vLang']) ? clean($_REQUEST['vLang']) : '';
    $UpdatedLanguageLabels = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $lngData = get_value('language_master', 'vCode, vGMapLangCode, eDirectionCode as eType, vTitle', 'vCode', $vLang);
    $returnArr['Action'] = "1";
    $returnArr['message'] = $UpdatedLanguageLabels;
    $returnArr['vCode'] = $lngData[0]['vCode'];
    $returnArr['vGMapLangCode'] = $lngData[0]['vGMapLangCode'];
    $returnArr['eType'] = $lngData[0]['eType'];
    $returnArr['vTitle'] = $lngData[0]['vTitle'];

    setDataResponse($returnArr);
}

if ($type == 'displaytripcharges') {
    $TripID = isset($_REQUEST["TripID"]) ? $_REQUEST["TripID"] : '';
    $destination_lat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
    $destination_lon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
    $iTripTimeId = isset($_REQUEST["iTripTimeId"]) ? $_REQUEST["iTripTimeId"] : '';

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    // $ALLOW_SERVICE_PROVIDER_AMOUNT = $generalobj->getConfigurations("configurations", "ALLOW_SERVICE_PROVIDER_AMOUNT");
    $where = " iTripId = '" . $TripID . "'";
    $data_update['tEndDate'] = @date("Y-m-d H:i:s");
    $data_update['tEndLat'] = $destination_lat;
    $data_update['tEndLong'] = $destination_lon;
    $obj->MySQLQueryPerform("trips", $data_update, 'update', $where);
    if ($iTripTimeId != "") {
        $where = " iTripTimeId = '$iTripTimeId'";
        $Data_update['dPauseTime'] = $data_update['tEndDate'];
        $Data_update['iTripId'] = $TripID;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'update', $where);
    }

    $sql = "SELECT * from trips WHERE iTripId = '" . $TripID . "'";
    $tripData = $obj->MySQLSelect($sql);

    // echo "<pre>"; print_r($tripData); die;
    $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
    $iVehicleTypeId = $tripData[0]['iVehicleTypeId'];
    $fVisitFee = $tripData[0]['fVisitFee'];
    $startDate = $tripData[0]['tStartDate'];
    $endDateOfTrip = $tripData[0]['tEndDate'];
    $iQty = $tripData[0]['iQty'];

    // $endDateOfTrip=@date("Y-m-d H:i:s");
    /* $iVehicleCategoryId=get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId',$iVehicleTypeId,'','true');
      $iParentId = get_value($sql_vehicle_category_table_name, 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId,'','true'); */
    $sql = "SELECT vc.iParentId from " . $sql_vehicle_category_table_name . " as vc LEFT JOIN vehicle_type as vt ON vc.iVehicleCategoryId=vt.iVehicleCategoryId WHERE vt.iVehicleTypeId = '" . $iVehicleTypeId . "'";
    $VehicleCategoryData = $obj->MySQLSelect($sql);
    $iParentId = $VehicleCategoryData[0]['iParentId'];
    if ($iParentId == 0) {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }

    // $ePriceType=get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
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

    // echo "<pre>"; print_r($tripData); die;
    $fPricePerKM = getVehicleCountryUnit_PricePerKm($iVehicleTypeId, $Fare_data[0]['fPricePerKM']);
    $Minute_Fare = round($Fare_data[0]['fPricePerMin'] * $totalTimeInMinutes_trip * $surgePrice, 2);
    $Distance_Fare = round($fPricePerKM * $tripDistance * $surgePrice, 2);
    $iBaseFare = round($Fare_data[0]['iBaseFare'] * $surgePrice, 2);
    $iMinFare = round($Fare_data[0]['iMinFare'] * $surgePrice, 2);
    $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare;
    if ($iMinFare > $total_fare) {
        $total_fare = $iMinFare;
    }

    if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
        $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $serviceProData = $obj->MySQLSelect($sqlServicePro);
        if (count($serviceProData) > 0) {
            $fAmount = $serviceProData[0]['fAmount'];
            if ($eFareType == "Fixed") {
                $fAmount = $fAmount * $iQty;
            } else if ($eFareType == "Hourly") {
                $fAmount = $fAmount * $totalHour;
            } else {
                $fAmount = $total_fare;
            }
        } else {
            if ($eFareType == "Fixed") {
                $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
            } else if ($eFareType == "Hourly") {
                $fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour, 2);
            } else {
                $fAmount = $total_fare;
            }
        }
    } else {
        if ($eFareType == "Fixed") {
            $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
        } else if ($eFareType == "Hourly") {
            $fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour, 2);
        } else {
            $fAmount = $total_fare;
        }
    }

    $final_display_charge = $fAmount + $fVisitFee;
    $returnArr['Action'] = "1";
    /* $vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'],'','true');
      $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
      $returnArr['message']=$currencySymbolRationDriver[0]['vSymbol']." ".number_format(round($final_display_charge * $currencySymbolRationDriver[0]['Ratio'],1),2); */

    // $currencySymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes','',true);
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'], '', 'true');
    $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
    $currencySymbol = $currencySymbolRationDriver[0]['vSymbol'];
    $currencyRationDriver = $currencySymbolRationDriver[0]['Ratio'];
    $final_display_charge = $final_display_charge * $currencyRationDriver;
    $final_display_charge = round($final_display_charge, 2);

    // $final_display_charge = formatNum($final_display_charge);
    $returnArr['message'] = $currencySymbol . ' ' . $final_display_charge;
    $returnArr['FareValue'] = $final_display_charge;
    $returnArr['CurrencySymbol'] = $currencySymbol;

    setDataResponse($returnArr);
}

// ########################## UBER-For-X ######################################
if ($type == "checkUserStatus") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';

    // $APP_TYPE = $generalobj->getConfigurations("configurations", "APP_TYPE");
    if ($UserType == "Passenger") {

        // $tblname = "register_user";
        // $fields = 'iUserId as iMemberId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName,vPassword, vLang';
        $condfield = 'iUserId';
    } else {

        // $tblname = "register_driver";
        // $fields = 'iDriverId  as iMemberId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName,vPassword, vLang';
        $condfield = 'iDriverId';
    }

    if ($APP_TYPE == "UberX") {
        $sql = "SELECT iTripId FROM trips WHERE 1=1 AND $condfield = '" . $iMemberId . "' AND vTripPaymentMode != 'Cash' AND eType='UberX' AND (iActive=	'Active' OR iActive='On Going Trip')";
        $checkStatus = $obj->MySQLSelect($sql);
    } else {
        $sql = "SELECT iTripId FROM trips WHERE 1=1 AND $condfield = '" . $iMemberId . "' AND vTripPaymentMode != 'Cash' AND (eType='Ride' || eType='Deliver' || eType='Multi-Delivery') AND (iActive=	'Active' OR iActive='On Going Trip') order by iTripId DESC limit 1";
        $checkStatus = $obj->MySQLSelect($sql);
    }

    if (count($checkStatus) > 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = 'LBL_DIS_ALLOW_EDIT_CARD_DL';
    } else {
        $returnArr['Action'] = "1";
    }


    setDataResponse($returnArr);
}

// ########################################################################
// # NEW WEBSERVICE END ##
// ############################# Get DriverDetail ###################################
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

// ##########################################################################
if ($type == "setVehicleTypes") {

    $langCodesArr = get_value('language_master', 'vCode', '', '');
    for ($i = 0; $i < count($langCodesArr); $i++) {
        $currLngCode = $langCodesArr[$i]['vCode'];
        $vVehicleType = $langCodesArr[$i]['vVehicleType'];
        $fieldName = "vVehicleType_" . $currLngCode;
        $suffixName = $i == 0 ? "vVehicleType" : "vVehicleType_" . $langCodesArr[$i - 1]['vCode'];
        $sql = "ALTER TABLE vehicle_type ADD " . $fieldName . " VARCHAR(50) AFTER" . " " . $suffixName;
        $id = $obj->sql_query($sql);
    }

    $vehicleTypesArr = get_value('vehicle_type', 'vVehicleType,iVehicleTypeId', '', '');
    for ($j = 0; $j < count($vehicleTypesArr); $j++) {
        $vVehicleType = $vehicleTypesArr[$j]['vVehicleType'];
        $iVehicleTypeId = $vehicleTypesArr[$j]['iVehicleTypeId'];
        echo "vVehicleType:" . $vVehicleType . "<BR/>";
        for ($k = 0; $k < count($langCodesArr); $k++) {
            $currLngCode = $langCodesArr[$k]['vCode'];
            $fieldName = "vVehicleType_" . $currLngCode;
            $suffixName = $k == 0 ? "vVehicleType" : "vVehicleType_" . $langCodesArr[$k - 1]['vCode'];

            // $sql = "ALTER TABLE vehicle_type ADD ".$fieldName." VARCHAR(50) AFTER"." ".$suffixName;
            // $id= $obj->sql_query($sql);
            echo $sql = "UPDATE `vehicle_type` SET " . $fieldName . " = '" . $vVehicleType . "' WHERE iVehicleTypeId = '$iVehicleTypeId'";
            echo "<br/>";
            $id1 = $obj->sql_query($sql);
            echo "<br/>" . $id1;
        }
    }

    // echo $sql = "UPDATE `vehicle_type` SET ".$fieldName." = ".$vVehicleType;
    // $id1= $obj->sql_query($sql);
    // echo "<br/>".$id;
}

if ($type == "DeclineTripRequest") {

    // $passenger_id = isset($_REQUEST["PassengerID"]) ? $_REQUEST["PassengerID"] : '';
    $driver_id = isset($_REQUEST["DriverID"]) ? $_REQUEST["DriverID"] : '';
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $vMsgCode = isset($_REQUEST["vMsgCode"]) ? $_REQUEST["vMsgCode"] : '';
    $sql = "SELECT iDriverRequestId,eAcceptAttempted FROM `driver_request` WHERE iDriverId = '" . $driver_id . "' AND iOrderId = '" . $iOrderId . "' AND iTripId = '0' AND vMsgCode='" . $vMsgCode . "' AND eAcceptAttempted = 'No'";
    $db_sql = $obj->MySQLSelect($sql);
    if (count($db_sql) > 0) {
        $request_count = UpdateDriverRequest2($driver_id, $passenger_id, "0", "Decline", $vMsgCode, "No", $iOrderId);
    } else {
        $request_count = 0;
    }

    echo $request_count;
}

// ##########################################################################
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
        $sql1 = "SELECT rd.iDriverId,rd.vImage as driverImage,concat(rd.vName,' ',rd.vLastName) as driverName, rd.vCode ,rd.vPhone as driverMobile ,rd.vLatitude as driverLatitude,rd.vLongitude as driverLongitude,rd.vTripStatus as driverStatus, rd.vAvgRating as driverRating, tr.`vRideNo`, tr.tSaddress,tr.iTripId, tr.iVehicleTypeId,tr.tTripRequestDate,tr.eFareType,tr.vTimeZone from trips as tr LEFT JOIN register_driver as rd ON rd.iDriverId=tr.iDriverId	WHERE tr.iActive != 'Canceled' AND iActive != 'Finished' AND iUserId='" . $iUserId . "' ORDER BY tr.iTripId DESC";
        $Data1 = $obj->MySQLSelect($sql1);
        if (count($Data1) > 0) {
            for ($i = 0; $i < count($Data1); $i++) {
                $iVehicleCategoryId = get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId', $Data1[$i]['iVehicleTypeId'], '', 'true');
                $vVehicleTypeName = get_value('vehicle_type', 'vVehicleType_' . $vLangCode, 'iVehicleTypeId', $Data1[$i]['iVehicleTypeId'], '', 'true');
                if ($iVehicleCategoryId != 0) {
                    $vVehicleCategoryName = get_value($sql_vehicle_category_table_name, 'vCategory_' . $vLangCode, 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
                    $vVehicleTypeName = $vVehicleCategoryName . "-" . $vVehicleTypeName;
                }

                $Data1[$i]['SelectedTypeName'] = $vVehicleTypeName;

                // Convert Into Timezone
                $tripTimeZone = $Data1[$i]['vTimeZone'];
                if ($tripTimeZone != "") {
                    $serverTimeZone = date_default_timezone_get();
                    $Data1[$i]['tTripRequestDate'] = converToTz($Data1[$i]['tTripRequestDate'], $tripTimeZone, $serverTimeZone);
                }

                // Convert Into Timezone
                $Data1[$i]['dDateOrig'] = $Data1[$i]['tTripRequestDate'];
            }

            $returnArr['Action'] = "1";
            $returnArr['SERVER_TIME'] = date('Y-m-d H:i:s');
            $returnArr['message'] = $Data1;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_ONGOING_TRIPS_AVAIL";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_ONGOING_TRIPS_AVAIL";
    }

    setDataResponse($returnArr);
}

if ($type == "getTripDeliveryLocations") {
    global $generalobj, $obj;
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $userType = isset($_REQUEST["userType"]) ? $_REQUEST["userType"] : 'Passenger';
    $Data = array();
    if ($iTripId != "") {
        if ($userType != 'Passenger') {
            $sql = "SELECT ru.iUserId,ru.vimgname as riderImage,concat(ru.vName,' ',ru.vLastName) as riderName, ru.vPhoneCode ,ru.vPhone as riderMobile,ru.vTripStatus as driverStatus, ru.vAvgRating as riderRating, tr.* from trips as tr 
				LEFT JOIN register_user as ru ON ru.iUserId=tr.iUserId
				WHERE tr.iTripId = '" . $iTripId . "'";
            $dataUser = $obj->MySQLSelect($sql);
            $Data['driverDetails'] = $dataUser[0];
            $iMemberId = get_value('trips', 'iDriverId', 'iTripId', $iTripId, '', 'true');
            $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
        } else {
            $sql = "SELECT rd.iDriverId,rd.vImage as driverImage,concat(rd.vName,' ',rd.vLastName) as driverName, rd.vCode ,rd.vPhone as driverMobile,rd.vTripStatus as driverStatus, rd.vAvgRating as driverRating, tr.* from trips as tr 
				LEFT JOIN register_driver as rd ON rd.iDriverId=tr.iDriverId
				WHERE tr.iTripId = '" . $iTripId . "'";
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
            $Driver_Arrived_Pick_Location = $languageLabelsArr['LBL_DRIVER1_ARRIVED_PICK_LOCATION_TXT'];
            $Driver_Start_job = $languageLabelsArr['LBL_PROVIDER1_START_JOB_TXT'];
            $Driver_Finished_job = $languageLabelsArr['LBL_PROVIDER1_FINISHED_JOB_TXT'];
        } else {
            $Driver_Acceprt_Delivery_Request = $languageLabelsArr['LBL_DRIVER_ACCEPTED_DELIVERY_REQUEST_TXT'];
            $Driver_Arrived_Pick_Location = $languageLabelsArr['LBL_DRIVER_ARRIVED_PICK_LOCATION_TXT'];
            $Driver_Start_job = $languageLabelsArr['LBL_PROVIDER_START_JOB_TXT'];
            $Driver_Finished_job = $languageLabelsArr['LBL_PROVIDER_FINISHED_JOB_TXT'];
        }

        $testBool = 1;
        if (count($dataUser) > 0) {
            $Data['States'] = array();
            $Data_tTripRequestDate = $dataUser[0]['tTripRequestDate'];
            $Data_tDriverArrivedDate = $dataUser[0]['tDriverArrivedDate'];
            $Data_dDeliveredDate = $dataUser[0]['dDeliveredDate'];
            $Data_tStartDate = $dataUser[0]['tStartDate'];
            $Data_tEndDate = $dataUser[0]['tEndDate'];
            $i = 0;
            if ($Data_tTripRequestDate != "" && $Data_tTripRequestDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = 'Provider accepted the request.';
                if ($userType != 'Passenger') {
                    $msg = 'You accepted the request.';
                }

                $Data['States'][$i]['text'] = $Driver_Acceprt_Delivery_Request;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tTripRequestDate));
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tTripRequestDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Accept";
                $i++;
            } else {
                $testBool = 0;
            }

            if ($Data_tDriverArrivedDate != "" && $Data_tDriverArrivedDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = "Provider arrived to your location.";
                if ($userType != 'Passenger') {
                    $msg = "You arrived to user's location.";
                }

                $Data['States'][$i]['text'] = $Driver_Arrived_Pick_Location;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tDriverArrivedDate));
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tDriverArrivedDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Arrived";
                $i++;
            } else {
                $testBool = 0;
            }

            if ($Data_tStartDate != "" && $Data_tStartDate != "0000-00-00 00:00:00" && $testBool == 1) {
                $msg = 'Provider has started the job.';
                if ($userType != 'Passenger') {
                    $msg = 'You started the job.';
                }

                $Data['States'][$i]['text'] = $Driver_Start_job;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tStartDate));
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tStartDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Onway";
                $i++;
            } else {
                $testBool = 0;
            }

            if ($Data_tEndDate != "" && $Data_tEndDate != "0000-00-00 00:00:00" && $testBool == 1 && $dataUser[0]['iActive'] == "Finished") {
                $msg = 'Provider has completed the job.';
                if ($userType != 'Passenger') {
                    $msg = 'You completed the job.';
                }

                $Data['States'][$i]['text'] = $Driver_Finished_job;
                $Data['States'][$i]['time'] = date("h:i A", strtotime($Data_tEndDate));
                $Data['States'][$i]['timediff'] = @round(abs(strtotime($Data_tEndDate) - strtotime(date("Y-m-d H:i:s"))) / 60, 0) . " " . $lbl_minago;
                $Data['States'][$i]['type'] = "Delivered";
                $i++;
            }
        } else {
            $Data['States'] = array();
        }

        if (count($Data) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $Data;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_DRIVER_FOUND";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_TRIP_FOUND_DL";
    }

    setDataResponse($returnArr);
}

if ($type == "SetTimeForTrips") {
    global $generalobj, $obj;
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $iTripTimeId = isset($_REQUEST["iTripTimeId"]) ? $_REQUEST["iTripTimeId"] : '';
    $dTime = date('Y-m-d H:i:s');
    if ($iTripTimeId == '') {
        $Data_update['dResumeTime'] = $dTime;
        $Data_update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'insert');
        $returnArr['Action'] = "1";
        $returnArr['message'] = $id;
    } else {
        $where = " iTripTimeId = '$iTripTimeId'";
        $Data_update['dPauseTime'] = $dTime;
        $Data_update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("trip_times", $Data_update, 'update', $where);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $id;
    }

    $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$iTripId'";
    $db_tripTimes = $obj->MySQLSelect($sql22);
    $totalSec = 0;
    $timeState = 'Pause';
    $iTripTimeId = '';
    foreach ($db_tripTimes as $dtT) {
        if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
            $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
        } else {
            $totalSec += strtotime(date('Y-m-d H:i:s')) - strtotime($dtT['dResumeTime']);
        }
    }

    $returnArr['totalTime'] = $totalSec;

    setDataResponse($returnArr);
}

if ($type == "getYearTotalEarnings") {
    global $generalobj, $obj;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $year = isset($_REQUEST["year"]) ? $_REQUEST["year"] : @date('Y');
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    if ($year == "") {
        $year = @date('Y');
    }

    if ($UserType == 'Driver') {
        $vCurrency = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iMemberId, '', 'true');
        $vCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrency, '', 'true');
    } else {
        $vCurrency = get_value('company', 'vCurrencyCompany', 'iCompanyId', $iMemberId, '', 'true');
        $vCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrency, '', 'true');
    }

    $vLangCode = get_value("register_driver", "vLang", "iDriverId", $iDriverId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $lngLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);

    $start = @date('Y');
    $end = '1970';
    $year_arr = array();
    for ($j = $start; $j >= $end; $j--) {
        $year_arr[] = strval($j);
    }

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

    if ($UserType == 'Driver') {
        $sql = "SELECT * FROM trips WHERE iDriverId='" . $iMemberId . "' AND tTripRequestDate LIKE '" . $year . "%' AND eSystem = 'DeliverAll'";
    } else {
        $sql = "SELECT * FROM orders WHERE iCompanyId='" . $iMemberId . "' AND iStatusCode = '6' AND tOrderRequestDate LIKE '" . $year . "%'";
    }

    $tripData = $obj->MySQLSelect($sql);
    $totalEarnings = 0;
    for ($i = 0; $i < count($tripData); $i++) {
        if ($UserType == 'Driver') {
            $iFare = $tripData[$i]['fDeliveryCharge'];
        } else {
            $iFare = $tripData[$i]['fTotalGenerateFare'] - $tripData[$i]['fOffersDiscount'] - $tripData[$i]['fDeliveryCharge'] - $tripData[$i]['fCommision'];
        }

        $priceRatio = $tripData[$i]['fRatio_' . $vCurrency];
        $totalEarnings += $iFare * $priceRatio;
    }

    $yearmontharr = array();
    $yearmontearningharr_Max = array();
    foreach ($Month_Array as $key => $value) {
        $tripyearmonthdate = $year . "-" . $key;
        if ($UserType == 'Driver') {
            $sql_Month = "SELECT * FROM trips WHERE iDriverId='" . $iMemberId . "' AND tTripRequestDate LIKE '" . $tripyearmonthdate . "%' AND eSystem = 'DeliverAll'";
        } else {
            $sql_Month = "SELECT * FROM orders WHERE iCompanyId='" . $iMemberId . "' AND iStatusCode = '6' AND tOrderRequestDate LIKE '" . $tripyearmonthdate . "%'";
        }

        $tripyearmonthData = $obj->MySQLSelect($sql_Month);
        $tripData_M = strval(count($tripyearmonthData));
        $yearmontearningharr = array();
        $totalEarnings_M = 0;
        for ($j = 0; $j < count($tripyearmonthData); $j++) {
            if ($UserType == 'Driver') {
                $iFare_M = $tripyearmonthData[$j]['fDeliveryCharge'];
            } else {
                $iFare_M = $tripyearmonthData[$j]['fTotalGenerateFare'] - $tripyearmonthData[$j]['fOffersDiscount'] - $tripyearmonthData[$j]['fDeliveryCharge'] - $tripyearmonthData[$j]['fCommision'];
            }

            $priceRatio_M = $tripyearmonthData[$j]['fRatio_' . $vCurrency];
            $totalEarnings_M += $iFare_M * $priceRatio_M;
        }

        $yearmontearningharr_Max[] = $totalEarnings_M;
        $yearmontearningharr["CurrentMonth"] = $value;
        $yearmontearningharr["TotalEarnings"] = strval(round($totalEarnings_M < 0 ? 0 : $totalEarnings_M, 1));
        $yearmontearningharr["OrderCount"] = strval(round($tripData_M, 1));
        array_push($yearmontharr, $yearmontearningharr);
    }

    foreach ($yearmontearningharr_Max as $key => $value) {
        if ($value >= $max) {
            $max = $value;
        }
    }

    $returnArr['Action'] = "1";
    $returnArr['TotalEarning'] = $vCurrencySymbol . " " . strval(round($totalEarnings, 1));
    $returnArr['OrderCount'] = strval(count($tripData));
    $returnArr["CurrentYear"] = $year;
    $returnArr['MaxEarning'] = strval($max);
    $returnArr['YearMonthArr'] = $yearmontharr;
    $returnArr['YearArr'] = $year_arr;
    setDataResponse($returnArr);
}

/* For Forgot Password */

if ($type == 'requestResetPassword') {
    global $generalobj, $obj, $tconfig;
    $Emid = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $userType = isset($_REQUEST["UserType"]) ? clean($_REQUEST["UserType"]) : ''; // UserType = Driver/Passenger
    if ($userType == "" || $userType == NULL) {
        $userType = "Passenger";
    }

    if ($userType == "Passenger") {
        $tblname = "register_user";
        $fields = 'iUserId as iMemberId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName, vPassword, vLang';
        $condfield = 'iUserId';
        $EncMembertype = base64_encode(base64_encode('rider'));
    } else if ($userType == "Company") {
        $tblname = "company";
        $fields = 'iCompanyId  as iMemberId, vPhone,vCode as vPhoneCode, vEmail, vCompany, vPassword, vLang';
        $condfield = 'iCompanyId';
        $EncMembertype = base64_encode(base64_encode('company'));
    } else {
        $tblname = "register_driver";
        $fields = 'iDriverId  as iMemberId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName,	vPassword, vLang';
        $condfield = 'iDriverId';
        $EncMembertype = base64_encode(base64_encode('driver'));
    }

    $sql = "select $fields from $tblname where vEmail = '" . $Emid . "'";
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
        $maildata['NAME'] = $db_member[0]["vName"] . " " . $db_member[0]["vLastName"];
        $maildata['LINK'] = $activation_text;
        $status = $generalobj->send_email_user("CUSTOMER_RESET_PASSWORD", $maildata);
        if ($status == 1) {
            $sql = "UPDATE $tblname set vPassword_token='" . $newToken . "' WHERE vEmail='" . $Emid . "' and eStatus != 'Deleted'";
            $obj->sql_query($sql);
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_PASSWORD_SENT_TXT";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_ERROR_PASSWORD_MAIL";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_WRONG_EMAIL_PASSWORD_TXT";
    }


    setDataResponse($returnArr);
}

/* For Forgot Password */
// ##########################################################################
/* For Driver Vehicle Details */

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

    $vLang = get_value('register_driver', 'vLang', 'iDriverId', $driverId, '', 'true');
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $driverId, '', 'true');
    if ($iDriverVehicleId > 0) {
        $Fare_Data = array();
        $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
        $DriverVehicle_Arr = explode(",", $vCarType);

        // echo "<pre>";print_r($DriverVehicle_Arr);echo "<br />";
        // $sql11 = "SELECT vVehicleType_".$vLang." as vVehicleTypeName, iVehicleTypeId, vLogo, iPersonSize FROM `vehicle_type`  WHERE  iVehicleTypeId IN (".$vCarType.") AND eType='Ride'";
        if ($VehicleTypeIds != "") {
            $sql11 = "SELECT  vVehicleType_" . $vLang . " as vVehicleTypeName,iVehicleTypeId, vLogo,vLogo1, iPersonSize FROM vehicle_type WHERE iVehicleTypeId IN (" . $VehicleTypeIds . ") AND eType='Ride' AND eStatus='Active'";
        } else {
            $pickuplocationarr = array(
                $StartLatitude,
                $EndLongitude
            );
            $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
            $sql_vehicle = "SELECT iVehicleTypeId FROM vehicle_type WHERE iLocationid IN (" . $GetVehicleIdfromGeoLocation . ") AND eType='Ride' AND eStatus='Active'";
            $db_vehicle_location = $obj->MySQLSelect($sql_vehicle);
            $array_vehiclie_id = array();
            for ($i = 0; $i < count($db_vehicle_location); $i++) {
                array_push($array_vehiclie_id, $db_vehicle_location[$i]['iVehicleTypeId']);
            }

            // echo "<pre>";print_r($array_vehiclie_id);echo "<br />";
            $Vehicle_array_diff = array_values(array_intersect($DriverVehicle_Arr, $array_vehiclie_id));
            $VehicleTypeIds_Str = implode(",", $Vehicle_array_diff);
            if ($VehicleTypeIds_Str == "") {
                $VehicleTypeIds_Str = "0";
            }

            $sql11 = "SELECT  vVehicleType_" . $vLang . " as vVehicleTypeName,iVehicleTypeId, vLogo,vLogo1, iPersonSize FROM vehicle_type WHERE iVehicleTypeId IN (" . $VehicleTypeIds_Str . ") AND eType='Ride' AND eStatus='Active'";
        }

        $vCarType_Arr = $obj->MySQLSelect($sql11);
        $Fare_Data = array();
        if (count($vCarType_Arr) > 0) {
            for ($i = 0; $i < count($vCarType_Arr); $i++) {

                // ######## Checking For Flattrip #########
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
                } else {
                    $eFlatTrip = "No";
                    $fFlatTripPrice = 0;
                }

                $Fare_Data[$i]['eFlatTrip'] = $eFlatTrip;
                $Fare_Data[$i]['fFlatTripPrice'] = $fFlatTripPrice;

                // ######## Checking For Flattrip #########
                $Fare_Single_Vehicle_Data = calculateFareEstimateAll($time, $distance, $vCarType_Arr[$i]['iVehicleTypeId'], $driverId, 1, "", "", "", 1, 0, 0, 0, "DisplySingleVehicleFare", "Driver", 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice);
                $Fare_Data[$i]['iVehicleTypeId'] = $vCarType_Arr[$i]['iVehicleTypeId'];
                $Fare_Data[$i]['vVehicleTypeName'] = $vCarType_Arr[$i]['vVehicleTypeName'];

                // $Fare_Data[$i]['vLogo'] = $vCarType_Arr[$i]['vLogo'];
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

                $Fare_Data[$i]['iPersonSize'] = $vCarType_Arr[$i]['iPersonSize'];
                $lastvalue = end($Fare_Single_Vehicle_Data);
                $lastvalue1 = array_shift($lastvalue);
                $Fare_Data[$i]['SubTotal'] = $lastvalue1;
                $Fare_Data[$i]['VehicleFareDetail'] = $Fare_Single_Vehicle_Data;

                // array_push($Fare_Data, $Fare_Single_Vehicle_Data);
            }
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $Fare_Data;

        // $returnArr['eFlatTrip'] = $eFlatTrip;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_VEHICLE_SELECTED";
    }


    setDataResponse($returnArr);
}

/* For Driver Vehicle Details */

// ##########################################################################
if ($type == "updateuserPref") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $eFemaleOnly = isset($_REQUEST['eFemaleOnly']) ? clean($_REQUEST['eFemaleOnly']) : 'No';
    $where = " iDriverId = '$iMemberId'";
    $Data_update_User['eFemaleOnlyReqAccept'] = $eFemaleOnly;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_User, 'update', $where);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = getDriverDetailInfo($iMemberId);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
// ##########################################################################
if ($type == "updateUserGender") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $eGender = isset($_REQUEST['eGender']) ? clean($_REQUEST['eGender']) : '';
    if ($userType == "Driver") {
        $where = " iDriverId = '$iMemberId'";
        $Data_update_User['eGender'] = $eGender;
        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_User, 'update', $where);
    } else {
        $where = " iUserId = '$iMemberId'";
        $Data_update_User['eGender'] = $eGender;
        $id = $obj->MySQLQueryPerform("register_user", $Data_update_User, 'update', $where);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        if ($userType != "Driver") {
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
        } else {
            $returnArr['message'] = getDriverDetailInfo($iMemberId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
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
    $DriverMessage = "CabRequestAccepted";

    // ## Checking For Pickup And DropOff Disallow ###
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

    // ## Checking For Pickup And DropOff Disallow ###
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

    // # Check For PichUp/DropOff Location DisAllow Ends##
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

        // # Update Trip Status ##
        $where = " iUserId='" . $iUserId . "'";
        $Data_passenger['iTripId'] = "0";
        $Data_passenger['vTripStatus'] = "NONE";
        $Data_passenger['vCallFromDriver'] = "";
        $sql = "UPDATE register_user set iTripId='0', vTripStatus = 'NONE', vCallFromDriver = '', eStatus = 'Active' WHERE iUserId='" . $iUserId . "'";
        $id = $obj->sql_query($sql);

        // $id = $obj->MySQLQueryPerform("register_user",$Data_update_passenger,'update',$where);
        // echo "hello";exit;
        // # Update Trip Status ##
        $iTripID = GenerateHailTrip($iUserId, $driverId, $selectedCarTypeID, $PickUpLatitude, $PickUpLongitude, $PickUpAddress, $DestLatitude, $DestLongitude, $DestAddress, $fTollPrice, $vTollPriceCurrencyCode, $eTollSkipped);
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
            $iTripID = GenerateHailTrip($id, $driverId, $selectedCarTypeID, $PickUpLatitude, $PickUpLongitude, $PickUpAddress, $DestLatitude, $DestLongitude, $DestAddress, $fTollPrice, $vTollPriceCurrencyCode, $eTollSkipped);
            $iUserId = $id;
        }
    }

    // ### Update Driver Request Status of Trip ####
    UpdateDriverRequest($driverId, $iUserId, $iTripID, "Accept");

    // ### Update Driver Request Status of Trip ####
    $trip_status = "On Going Trip";
    $where = " iUserId = '$iUserId'";
    /* $Data_update_passenger['iTripId']=$iTripID;
      $Data_update_passenger['vTripStatus']=$trip_status; */
    /* $Data_update_passenger['iTripId'] = 0;
      $Data_update_passenger['vTripStatus'] = "NONE";
      $Data_update_passenger['vCallFromDriver'] = "";
      $id = $obj->MySQLQueryPerform("register_user", $Data_update_passenger, 'update', $where); */
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
    $message = json_encode($message_arr);
    if ($iTripID > 0) {
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

// ##########################################################################
/* For Sending Trip Message and Notification  */
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

        // $message = sendTripMessagePushNotification($iFromMemberId,$UserType,$iToMemberId,$iTripId,$tMessage);
        // if($message == 1){
        // $returnArr['Action'] ="1";
        // }else{
        // $returnArr['Action'] ="0";
        // $returnArr['message'] ="LBL_TRY_AGAIN_LATER_TXT";
        // }
        sendTripMessagePushNotification($iFromMemberId, $UserType, $iToMemberId, $iTripId, $tMessage);

        setDataResponse($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";

        setDataResponse($returnArr);
    }
}
/* For Sending Trip Message and Notification  */
// ############################################################################

if ($type == "pushNotificationGCM") {
    $deviceToken = $_REQUEST['Token'];
    $registation_ids_new = array();
    array_push($registation_ids_new, $deviceToken);
    $Rmessage = array(
        "message" => $_REQUEST['message']
    );
    $result = send_notification($registation_ids_new, $Rmessage, 0);
    echo "<pre>";
    print_r($result);
    exit;
}

if ($type == "configDriverTripStatus") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vLatitude = isset($_REQUEST["vLatitude"]) ? $_REQUEST["vLatitude"] : '';
    $vLongitude = isset($_REQUEST["vLongitude"]) ? $_REQUEST["vLongitude"] : '';
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $isSubsToCabReq = isset($_REQUEST["isSubsToCabReq"]) ? $_REQUEST["isSubsToCabReq"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    if ($iMemberId != "") {
        if (!empty($isSubsToCabReq) && $isSubsToCabReq == 'true') {
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

            // Update User Location Date #
            Updateuserlocationdatetime($iMemberId, "Driver", $vTimeZone);

            // Update User Location Date #
        }
    }

    if ($iTripId != "") {
        $sql = "SELECT tMessage as msg, iStatusId FROM trip_status_messages WHERE iDriverId='" . $iMemberId . "' AND eToUserType='Driver' AND eReceived='No' ORDER BY iStatusId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    } else {
        $date = @date("Y-m-d");
        $sql = "SELECT passenger_requests.tMessage as msg  FROM passenger_requests LEFT JOIN driver_request ON  driver_request.iRequestId=passenger_requests.iRequestId  LEFT JOIN register_driver ON register_driver.iDriverId=passenger_requests.iDriverId where date_format(passenger_requests.dAddedDate,'%Y-%m-%d')= '" . $date . "' AND  passenger_requests.iDriverId=" . $iMemberId . " AND driver_request.eStatus='Timeout' AND driver_request.iDriverId='" . $iMemberId . "' AND register_driver.vTripStatus IN ('Not Active','NONE','Cancelled') ORDER BY passenger_requests.iRequestId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    }

    $returnArr['Action'] = "0";
    if (!empty($msg)) {
        $returnArr['Action'] = "1";
        if ($iTripId != "") {

            // $updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iStatusId='".$msg[0]['iStatusId']."'";
            $updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iDriverId='" . $iMemberId . "'";
            $obj->sql_query($updateQuery);
            $returnArr['Action'] = "1";
            $returnArr['message'] = $msg[0]['msg'];
        } else {
            $driver_request['eStatus'] = "Received";
            $where = " iDriverId =" . $iMemberId . " and date_format(tDate,'%Y-%m-%d') = '" . $date . "' AND eStatus = 'Timeout' ";
            $obj->MySQLQueryPerform("driver_request", $driver_request, "update", $where);

            // $updatequery = "update driver_request set eStatus='Received' where iDriverId='".$iMemberId."' AND   date_format(tDate,'%Y-%m-%d') = '" . $date . "'  AND eStatus = 'Timeout'";
            // $obj->sql_query($updateQuery);
            $returnArr['Action'] = "1";
            $dataArr = array();
            for ($i = 0; $i < count($msg); $i++) {
                $dataArr[$i] = $msg[$i]['msg'];
            }

            $returnArr['message'] = $dataArr;
        }
    }



    setDataResponse($returnArr);
}

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
        $CurrentDriverIds = (array) $iDriverId;
    }

    if ($iMemberId != "") {
        if (!empty($vLatitude) && !empty($vLongitude)) {
            $user_update['vLatitude'] = $vLatitude;
            $user_update['vLongitude'] = $vLongitude;
            $where = " iUserId = '" . $iMemberId . "'";
            $Update_driver = $obj->MySQLQueryPerform("register_user", $user_update, "update", $where);

            // Update User Location Date #
            Updateuserlocationdatetime($iMemberId, "Passenger", $vTimeZone);

            // Update User Location Date #
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

        // $updateQuery = "UPDATE trip_status_messages SET eReceived ='Yes' WHERE iStatusId='".$msg[0]['iStatusId']."'";
        $updateQuery = "UPDATE trip_status_messages SET eReceived ='Yes' WHERE iUserId='" . $iMemberId . "'";
        $obj->sql_query($updateQuery);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $msg[0]['msg'];
    }

    $returnArr['currentDrivers'] = $currDriver;

    setDataResponse($returnArr);
}

if ($type == "callOnLogout") {
    global $generalobj, $obj;
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $Data_logout = array();
    if ($userType == "Passenger") {
        $Data_logout['eLogout'] = 'Yes';
        $tableName = "register_user";
        $where = " iUserId='" . $iMemberId . "'";
        $id = $obj->MySQLQueryPerform($tableName, $Data_logout, 'update', $where);
    } else if ($userType == "Company") {
        $Data_logout['eAvailable'] = 'No';
        $Data_logout['eLogout'] = 'Yes';
        $tableName = "company";
        $where = " iCompanyId='" . $iMemberId . "'";
        $id = $obj->MySQLQueryPerform($tableName, $Data_logout, 'update', $where);
    } else {
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

    if ($id) {
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

if ($type == "getCabRequestAddress") {
    global $generalobj, $obj;
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $iDriverId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
    $fields = "iUserId,iCompanyId,iStatusCode,iUserAddressId";
    $Data_cab_request = get_value('orders', $fields, 'iOrderId', $iOrderId, '', '');
    $iCompanyId = $Data_cab_request[0]['iCompanyId'];
    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId, '', '');
    $iUserAddressId = $Data_cab_request[0]['iUserAddressId'];
    $userfields = "vServiceAddress,vBuildingNo,vLatitude,vLongitude";
    $Data_cab_requestuser = get_value('user_address', $userfields, 'iUserAddressId', $iUserAddressId, '', '');
    if (!empty($Data_cab_requestcompany)) {
        $vRestuarantLocation = ($Data_cab_requestcompany[0]['vRestuarantLocation'] != '') ? $Data_cab_requestcompany[0]['vRestuarantLocation'] : '';
        $vRestuarantLocationLat = ($Data_cab_requestcompany[0]['vRestuarantLocationLat'] != '') ? $Data_cab_requestcompany[0]['vRestuarantLocationLat'] : '';
        $vRestuarantLocationLong = ($Data_cab_requestcompany[0]['vRestuarantLocationLong'] != '') ? $Data_cab_requestcompany[0]['vRestuarantLocationLong'] : '';
        if (!empty($Data_cab_requestuser[0]['vBuildingNo'])) {
            $tDestAddress = $Data_cab_requestuser[0]['vBuildingNo'] . ", " . $Data_cab_requestuser[0]['vServiceAddress'];
        } else {
            $tDestAddress = $Data_cab_requestuser[0]['vServiceAddress'];
        }

        $UserAddressArr = GetUserAddressDetail($Data_cab_request[0]['iUserId'], "Passenger", $iUserAddressId);
        $vLatitude = ($Data_cab_requestuser[0]['vLatitude'] != '') ? $Data_cab_requestuser[0]['vLatitude'] : '';
        $vLongitude = ($Data_cab_requestuser[0]['vLongitude'] != '') ? $Data_cab_requestuser[0]['vLongitude'] : '';
    }

    $Data_cab_request[0]['tSourceAddress'] = $vRestuarantLocation;
    $Data_cab_request[0]['tSourceLat'] = $Data_cab_request[0]['sourceLatitude'] = $vRestuarantLocationLat;
    $Data_cab_request[0]['tSourceLong'] = $Data_cab_request[0]['sourceLongitude'] = $vRestuarantLocationLong;
    $Data_cab_request[0]['tDestAddress'] = $UserAddressArr['UserAddress'];
    $Data_cab_request[0]['tDestLatitude'] = $Data_cab_request[0]['destLatitude'] = $vLatitude;
    $Data_cab_request[0]['tDestLongitude'] = $Data_cab_request[0]['destLongitude'] = $vLongitude;
    $Data_cab_request[0]['eType'] = "DeliverAll"; // Added By HJ On 23-09-2019 As Per Discuss With CS
    if (!empty($Data_cab_request)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data_cab_request[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ##########################################################################
// #######################Get Driver Bank Details############################
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
    } else {
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

// #######################Get Driver Bank Details############################
// #######################Get Driver Bank Details############################
if ($type == "CompanyBankDetails") {
    global $generalobj, $obj;
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : "";
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Company';
    $vPaymentEmail = isset($_REQUEST["vPaymentEmail"]) ? $_REQUEST["vPaymentEmail"] : '';
    $vAcctHolderName = isset($_REQUEST["vAcctHolderName"]) ? $_REQUEST["vAcctHolderName"] : '';
    $vAcctNo = isset($_REQUEST["vAcctNo"]) ? $_REQUEST["vAcctNo"] : '';
    $vBankLocation = isset($_REQUEST["vBankLocation"]) ? $_REQUEST["vBankLocation"] : '';
    $vBankName = isset($_REQUEST["vBankName"]) ? $_REQUEST["vBankName"] : '';
    $vSwiftCode = isset($_REQUEST["vSwiftCode"]) ? $_REQUEST["vSwiftCode"] : '';
    $CALL_TYPE = isset($_REQUEST["CALL_TYPE"]) ? $_REQUEST["CALL_TYPE"] : "Display"; // Display , Update
    $returnArr = array();
    if ($CALL_TYPE == "Display") {
        $sqlc = "SELECT vPaymentEmail,vAcctHolderName,vAcctNo,vBankLocation,vBankName,vSwiftCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        $result_company = $obj->MySQLSelect($sqlc);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $result_company[0];
        setDataResponse($returnArr);
    } else {
        $Data_Update['vPaymentEmail'] = $vPaymentEmail;
        $Data_Update['vAcctHolderName'] = $vAcctHolderName;
        $Data_Update['vAcctNo'] = $vAcctNo;
        $Data_Update['vBankLocation'] = $vBankLocation;
        $Data_Update['vBankName'] = $vBankName;
        $Data_Update['vSwiftCode'] = $vSwiftCode;
        $where = " iCompanyId = '" . $iCompanyId . "'";
        $Company_Update_id = $obj->MySQLQueryPerform("company", $Data_Update, 'update', $where);
        if ($Company_Update_id) {
            $returnArr['Action'] = "1";
            //$returnArr['message'] = "LBL_INFO_UPDATED_TXT";
            $returnArr['message'] = getCompanyDetailInfo($iCompanyId, 1);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER";
        }


        setDataResponse($returnArr);
    }
}

// #######################Get Driver Bank Details############################
if ($type == "getvehicleCategory") {
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? trim($_REQUEST['iVehicleCategoryId']) : 0;
    $languageCode = $ssql_category = "";
    if ($iDriverId != "") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    }
    if ($languageCode == "" || $languageCode == NULL) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    $returnName = "vTitle";
    if ($iVehicleCategoryId != "" && ($iVehicleCategoryId == 0 || $iVehicleCategoryId == "0")) {
        $ssql_category = " and (select count(iVehicleCategoryId) from " . $sql_vehicle_category_table_name . " where iParentId=vc.iVehicleCategoryId AND eStatus='Active') > 0";
        $returnName = "vCategory";
    }
    $per_page = 10;
    $sql_all = "SELECT COUNT(iVehicleCategoryId) As TotalIds FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.eStatus='Active' AND vc.iParentId='" . $iVehicleCategoryId . "'" . $ssql_category;
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    $sql = "SELECT vc.iVehicleCategoryId, vc.vCategory_" . $languageCode . " as '" . $returnName . "' FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.eStatus='Active' AND vc.iParentId='" . $iVehicleCategoryId . "'" . $ssql_category . $limit;
    $vehicleCategoryDetail = $obj->MySQLSelect($sql);
    $vehicleCategoryData = array();
    if (count($vehicleCategoryDetail) > 0) {
        $Data3 = $obj->MySQLSelect("SELECT iVehicleTypeId,iVehicleCategoryId FROM vehicle_type WHERE eStatus='Active' ORDER BY iDisplayOrder ASC");
        $categoryArr = array();
        for ($vc = 0; $vc < count($Data3); $vc++) {
            $categoryArr[$Data3[$vc]['iVehicleCategoryId']][] = $Data3[$vc];
        }
        //echo "<pre>";print_R($categoryArr);die;
        $vehicleCategoryData = $vehicleCategoryDetail;
        if ($iVehicleCategoryId != "" && ($iVehicleCategoryId == 0 || $iVehicleCategoryId == "0")) {
            $i = 0;
            while (count($vehicleCategoryDetail) > $i) {
                $iVehicleCategoryId = $vehicleCategoryDetail[$i]['iVehicleCategoryId'];
                $sql = "SELECT vCategory_" . $languageCode . " as vTitle,iVehicleCategoryId FROM `" . $sql_vehicle_category_table_name . "` WHERE iParentId='" . $iVehicleCategoryId . "' AND eStatus='Active'";
                $subCategoryData = $obj->MySQLSelect($sql);
                //Added By HJ On 11-07-2019 For Check Category's Service Exists Or Not Start
                $subCatArr = array();
                for ($d = 0; $d < count($subCategoryData); $d++) {
                    //print_r($subCategoryData);die;
                    $serviceArr = array();
                    if (isset($categoryArr[$subCategoryData[$d]['iVehicleCategoryId']])) {
                        $serviceArr = $categoryArr[$subCategoryData[$d]['iVehicleCategoryId']];
                    }
                    if (count($serviceArr) > 0) {
                        $subCatArr[] = $subCategoryData[$d];
                    }
                }
                $vehicleCategoryData[$i]['SubCategory'] = $subCatArr;
                //Added By HJ On 11-07-2019 For Check Category's Service Exists Or Not End
                $i++;
            }
        }
        $returnArr['Action'] = "1";
        if ($TotalPages > $page) {
            $returnArr['NextPage'] = "" . ($page + 1);
        } else {
            $returnArr['NextPage'] = "0";
        }
        $returnArr['message'] = $vehicleCategoryData;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
// ##########################################################################
// ##########################################################################
if ($type == "getServiceTypes") {
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $languageCode = "";

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

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

    $sql2 = "SELECT iVehicleTypeId, vVehicleType_" . $languageCode . " as vTitle,eFareType,eAllowQty,iMaxQty,fFixedFare,fPricePerHour,iLocationid from vehicle_type where iVehicleCategoryId in($iVehicleCategoryId)" . $ssql;
    $vehicleDetail = $obj->MySQLSelect($sql2);
    $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $db_driverdetail[0]['iDriverId'], '', 'true');
    if ($vCurrencyDriver == "" || $vCurrencyDriver == NULL) {
        $vCurrencyDriver = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }

    $vCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyDriver);
    $vCurrencySymbol = $vCurrencyData[0]['vSymbol'];
    $vCurrencyRatio = $vCurrencyData[0]['Ratio'];
    $iParentId = get_value($sql_vehicle_category_table_name, 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    if ($iParentId == 0) {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
    } else {
        $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
    }

    $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
    $sql = "SELECT vCarType FROM `driver_vehicle` where iDriverId ='" . $iDriverId . "' AND iDriverVehicleId = '" . $iDriverVehicleId . "'";
    $db_vCarType = $obj->MySQLSelect($sql);
    if (count($db_vCarType) > 0) {
        $vehicle_service_id = explode(",", $db_vCarType[0]['vCarType']);
        for ($i = 0; $i < count($vehicleDetail); $i++) {
            $sql3 = "SELECT * FROM `service_pro_amount` where iDriverVehicleId ='" . $db_driverdetail[0]['iDriverVehicleId'] . "' AND iVehicleTypeId='" . $vehicleDetail[$i]['iVehicleTypeId'] . "'";
            $db_serviceproviderid = $obj->MySQLSelect($sql3);
            if (count($db_serviceproviderid) > 0) {
                $vehicleDetail[$i]['fAmount'] = $db_serviceproviderid[0]['fAmount'];
            } else {
                if ($vehicleDetail[$i]['eFareType'] == "Hourly") {
                    $vehicleDetail[$i]['fAmount'] = $vehicleDetail[$i]['fPricePerHour'];
                } else {
                    $vehicleDetail[$i]['fAmount'] = $vehicleDetail[$i]['fFixedFare'];
                }
            }

            // $vehicleDetail[$i]['iDriverVehicleId']=$db_driverdetail[0]['iDriverVehicleId'];
            $fAmount = round($vehicleDetail[$i]['fAmount'] * $vCurrencyRatio, 2);
            $vehicleDetail[$i]['fAmount'] = $fAmount;
            $vehicleDetail[$i]['ePriceType'] = $ePriceType;
            $vehicleDetail[$i]['vCurrencySymbol'] = $vCurrencySymbol;
            $data_service[$i] = $vehicleDetail[$i];
            if (in_array($data_service[$i]['iVehicleTypeId'], $vehicle_service_id)) {
                $vehicleDetail[$i]['VehicleServiceStatus'] = 'true';
            } else {
                $vehicleDetail[$i]['VehicleServiceStatus'] = 'false';
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
        $returnArr['message'] = $vehicleDetail;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    setDataResponse($returnArr);
}

// ##########################################################################
// ##########################################################################
if ($type == "UpdateDriverServiceAmount") {
    $iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
    $iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? $_REQUEST['iDriverVehicleId'] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Driver';
    $fAmount = isset($_REQUEST['fAmount']) ? $_REQUEST['fAmount'] : '';
    if ($iDriverVehicleId == "" || $iDriverVehicleId == 0 || $iDriverVehicleId == NULL) {
        $iDriverVehicleId = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $iDriverId, '', 'true');
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

// ##########################################################################
// ##########################################################################
if ($type == "UpdateBookingStatus") {
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $vCancelReason = isset($_REQUEST['vCancelReason']) ? $_REQUEST['vCancelReason'] : '';
    $eConfirmByProvider = isset($_REQUEST['eConfirmByProvider']) ? $_REQUEST['eConfirmByProvider'] : 'No';
    if ($eConfirmByProvider == "" || $eConfirmByProvider == NULL) {
        $eConfirmByProvider = "No";
    }

    // ############################################################## CheckPendingBooking UBERX  For same Time booking (Accept , Pending)###########################################################
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
        } else {
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

    // ############################################################## CheckPendingBooking UBERX ###########################################################
    // ## Checking For booking timing availablity when driver accept booking ###
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

    // ## Checking For booking timing availablity when driver accept booking ###
    $where = " iCabBookingId = '$iCabBookingId' ";
    $Data['eStatus'] = $eStatus;
    $Data['vCancelReason'] = $vCancelReason;
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
        } else if ($eStatus == "Declined") {
            $returnArr['message'] = "LBL_JOB_DECLINED";
            $sendMailtoUser = $generalobj->send_email_user("MANUAL_BOOKING_DECLINED_BYDRIVER_SP", $Data1);
        } else {
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
        } else if ($eStatus == "Declined") {
            $returnArr['message'] = "LBL_JOB_DECLINED";
        } else {
            $returnArr['message'] = getDriverDetailInfo($iDriverId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ##########################################################################
// ##########################################################################
// ##########################Display User Address##########################################################
if ($type == "DisplayUserAddress") {
    global $generalobj, $tconfig;
    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    $eUserType = isset($_REQUEST['eUserType']) ? clean($_REQUEST['eUserType']) : 'Passenger';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    if ($eUserType == "Passenger") {
        $eUserType = "Rider";
    }

    $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = '" . $eUserType . "' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
    $db_userdata = $obj->MySQLSelect($sql);
    if (count($db_userdata) > 0) {
        $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
        $db_companydata = $obj->MySQLSelect($sql);
        $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
        $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
        $distancewithcompany = distanceByLocation($passengerLat, $passengerLon, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
        for ($i = 0; $i < count($db_userdata); $i++) {
            $isRemoveAddressFromList = "No";
            $eLocationAvailable = "Yes";
            $addressLatitude = $db_userdata[$i]['vLatitude'];
            $addressLongitude = $db_userdata[$i]['vLongitude'];
            $distance = distanceByLocation($vRestuarantLocationLat, $vRestuarantLocationLong, $addressLatitude, $addressLongitude, "K");
            if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                $isRemoveAddressFromList = "Yes";
            }
            if ($iCompanyId > 0) {
                if ($distancewithcompany > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }
            if ($isRemoveAddressFromList == "Yes") {
                $eLocationAvailable = "No";
            }
            $db_userdata[$i]['eLocationAvailable'] = $eLocationAvailable;
        }
        //$db_userdata = array_values($db_userdata_new);
        if (count($db_userdata) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $db_userdata;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_USER_ADDRESS_FOUND";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_USER_ADDRESS_FOUND";
    }

    setDataResponse($returnArr);
}

// ##########################Display User Address End######################################################
// ##########################Add/Update User Address ##########################################################
if ($type == "UpdateUserAddressDetails") {
    global $generalobj, $tconfig;
    $iUserAddressId = isset($_REQUEST['iUserAddressId']) ? $_REQUEST['iUserAddressId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
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

        // $allowed_ans = checkRestrictedAreaNew($pickuplocationarr,"No");
        $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
        if ($allowed_ans == "Yes") {
            $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
            $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) ORDER BY iVehicleTypeId ASC";
            $vehicleTypes = $obj->MySQLSelect($sql23);
            $Vehicle_Str = "";
            if (count($vehicleTypes) > 0) {
                for ($i = 0; $i < count($vehicleTypes); $i++) {
                    $Vehicle_Str .= $vehicleTypes[$i]['iVehicleTypeId'] . ",";
                }
                //$Vehicle_Str = substr($Vehicle_Str, 0, -1);
                $Vehicle_Str = trim($Vehicle_Str, ",");
            }

            $Vehicle_Str_Arr = explode(",", $Vehicle_Str);
            if (in_array($iSelectVehicalId, $Vehicle_Str_Arr)) {
                $IsProceed = "Yes";
            } else {
                $IsProceed = "No";
            }
        } else {
            $IsProceed = "No";
        }
    }

    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $dAddedDate = @date("Y-m-d H:i:s");
    $action = ($iUserAddressId != '') ? 'Edit' : 'Add';

    // # Checking Distance Between Company and User Address ##
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

    // # Checking Distance Between Company and User Address ##
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
    } else {
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
        } else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// #############################Add/Update User Address End##########################################################
// #############################Delete User Address #################################################################
if ($type == "DeleteUserAddressDetail") {
    global $generalobj, $tconfig;
    $iUserAddressId = isset($_REQUEST['iUserAddressId']) ? $_REQUEST['iUserAddressId'] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $sql = "Update user_address set eStatus = 'Deleted' WHERE `iUserAddressId`='" . $iUserAddressId . "' AND `iUserId`='" . $iUserId . "' AND eUserType = '" . $UserType . "'";
    $id = $obj->MySQLSelect($sql);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message1'] = "LBL_USER_ADDRESS_DELETED_TXT";
        if ($eUserType == "Passenger") {
            $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
            $returnArr['ToTalAddress'] = GetTotalUserAddress($iUserId, "Passenger", $passengerLat, $passengerLon, 0);
        } else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
            $returnArr['ToTalAddress'] = GetTotalUserAddress($iUserId, "Driver", $passengerLat, $passengerLon, 0);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// #############################Delete User Address Ends#################################################################
// #############################Update Driver Manage Timing #################################################################
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

// #############################Update Driver Manage Timing Ends#################################################################
// ##########################Display Availability##########################################################
if ($type == "DisplayAvailability") {
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $vDay = isset($_REQUEST['vDay']) ? clean($_REQUEST['vDay']) : '';
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

// ##########################Display Availability End######################################################
// ##########################Add/Update Availability ##########################################################
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

    // $action = ($iDriverTimingId != '')?'Edit':'Add';
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

// #############################Add/Update User Address End##########################################################
// ===================Display user status=========================
if ($type == "GetUserStats") {
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $currDate = date('Y-m-d H:i:s');
    $ssql1 = " AND dBooking_date > '" . $currDate . "'";
    $sql = "select count(iCabBookingId) as Total_Pending from `cab_booking` where iDriverId != '' AND eStatus = 'Pending' AND iDriverId = '" . $iDriverId . "' " . $ssql1 . " ORDER BY iCabBookingId DESC";
    $db_data_pending = $obj->MySQLSelect($sql);
    $sql1 = "select count(iCabBookingId) as Total_Upcoming from `cab_booking` where  iDriverId != '' AND eStatus = 'Accepted' AND iDriverId='" . $iDriverId . "' " . $ssql1 . " ORDER BY iCabBookingId DESC";
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

// #############################Display user status End##########################################################
// #############################Update Radius ##########################################################
if ($type == "UpdateRadius") {
    global $generalobj, $tconfig;
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $vWorkLocationRadius = isset($_REQUEST["vWorkLocationRadius"]) ? $_REQUEST["vWorkLocationRadius"] : '';
    $eStatus = isset($_REQUEST["eStatus"]) ? $_REQUEST["eStatus"] : 'Active';
    $Data_register_driver['vWorkLocationRadius'] = $vWorkLocationRadius;
    $eUnit = getMemberCountryUnit($iDriverId, "Driver");
    if ($eUnit == "Miles") {
        $Data_register_driver['vWorkLocationRadius'] = round($vWorkLocationRadius * 1.60934, 2); // convert miles to km
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

// #############################Update Radius  End##########################################################
// ##########################Display Driver Day Availability##########################################################
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

// ##########################Display Driver Day Availability Ends##########################################################
// ##########################Check  Schedule Booking Time Availability##########################################################
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

// ###########################Check  Schedule Booking Time Availability Ends##########################################################
// ############################Display  Schedule Booking Details######################################################################
if ($type == "DisplayScheduleBookingDetail") {
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : 'Passenger';
    $iCabBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    // $APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    // $APP_TYPE = "UberX";
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
        } else {
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
        } else {
            $db_member[0]['Imgname'] = "";
        }

        $vehicleDetailsArr = array();
        $iVehicleTypeId = $bookingData[0]['iVehicleTypeId'];
        $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.vCategoryTitle_" . $lang . " as vCategoryTitle, vc.tCategoryDesc_" . $lang . " as tCategoryDesc, vc.ePriceType, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM " . $sql_vehicle_category_table_name . " as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vt.iVehicleTypeId='" . $iVehicleTypeId . "'";
        $Data = $obj->MySQLSelect($sql2);
        $iParentId = $Data[0]['iParentId'];
        if ($iParentId == 0) {
            $ePriceType = $Data[0]['ePriceType'];
        } else {
            $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
        }

        $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
        if ($Data[0]['eFareType'] == "Fixed") {

            // $fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fFixedFare'];
            $fAmount = $Data[0]['fFixedFare'];
        } else if ($Data[0]['eFareType'] == "Hourly") {

            // $fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fPricePerHour']."/hour";
            $fAmount = $Data[0]['fPricePerHour'];
        } else {
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
            } else {
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
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ############################Display  Schedule Booking Details Ends#################################################################
// ############################Check Source Location and get Vehicle Deteails#################################################################
if ($type == "CheckSourceLocationState") {
    global $generalobj, $tconfig;
    $PickUpLatitude = isset($_REQUEST["PickUpLatitude"]) ? $_REQUEST["PickUpLatitude"] : '0.0';
    $PickUpLongitude = isset($_REQUEST["PickUpLongitude"]) ? $_REQUEST["PickUpLongitude"] : '0.0';
    $selectedCarTypeID = isset($_REQUEST["SelectedCarTypeID"]) ? $_REQUEST["SelectedCarTypeID"] : '';

    // $APP_TYPE = $generalobj->getConfigurations("configurations","APP_TYPE");
    $CurrentCabGeneralType = isset($_REQUEST["CurrentCabGeneralType"]) ? $_REQUEST["CurrentCabGeneralType"] : '';
    $APP_TYPE = $CurrentCabGeneralType;
    if ($APP_TYPE == "Delivery" || $APP_TYPE == "Deliver") {
        $ssql .= " AND eType = 'Deliver'";
    } else if ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Deliver") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride')";
    } else if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Ride-Deliver-UberX") {
        $ssql .= " AND ( eType = 'Deliver' OR eType = 'Ride' OR eType = 'UberX')";
    } else {
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

    // $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) AND iVehicleTypeId IN ($selectedCarTypeID) ORDER BY iVehicleTypeId ASC";
    $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) $ssql ORDER BY iVehicleTypeId ASC";
    $vehicleTypes = $obj->MySQLSelect($sql23);
    $Vehicle_Str = "";
    if (count($vehicleTypes) > 0) {
        for ($i = 0; $i < count($vehicleTypes); $i++) {
            $Vehicle_Str .= $vehicleTypes[$i]['iVehicleTypeId'] . ",";
        }
        //$Vehicle_Str = substr($Vehicle_Str, 0, -1);
        $Vehicle_Str = substr($Vehicle_Str, ",");
    }

    $selectedCarTypeID_Arr = explode(",", $selectedCarTypeID);
    $Vehicle_Str_Arr = explode(",", $Vehicle_Str);
    if ($selectedCarTypeID_Arr === array_intersect($selectedCarTypeID_Arr, $Vehicle_Str_Arr) && $Vehicle_Str_Arr === array_intersect($Vehicle_Str_Arr, $selectedCarTypeID_Arr)) {
        $returnArr['Action'] = "0";
    } else {
        $returnArr['Action'] = "1";
    }


    setDataResponse($returnArr);
}

// ############################Check Source Location and get Vehicle Deteails#################################################################
// ############################Check Restriction For Pickup and DropOff Location For Delivery#########################################
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

// ############################Check Restriction For Pickup and DropOff Location For Delivery#########################################
// ############################Check Restriction For Pickup and DropOff Location For UberX#########################################
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

        // $allowed_ans = checkRestrictedAreaNew($pickuplocationarr,"No");
        $allowed_ans = checkAllowedAreaNew($pickuplocationarr, "No");
        if ($allowed_ans == "Yes") {
            $GetVehicleIdfromGeoLocation = GetVehicleTypeFromGeoLocation($pickuplocationarr);
            $sql23 = "SELECT iVehicleTypeId FROM `vehicle_type` WHERE iLocationid IN ($GetVehicleIdfromGeoLocation) ORDER BY iVehicleTypeId ASC";
            $vehicleTypes = $obj->MySQLSelect($sql23);
            $Vehicle_Str = "";
            if (count($vehicleTypes) > 0) {
                for ($i = 0; $i < count($vehicleTypes); $i++) {
                    $Vehicle_Str .= $vehicleTypes[$i]['iVehicleTypeId'] . ",";
                }
                //$Vehicle_Str = substr($Vehicle_Str, 0, -1);
                $Vehicle_Str = trim($Vehicle_Str, ",");
            }

            $Vehicle_Str_Arr = explode(",", $Vehicle_Str);
            if (in_array($iSelectVehicalId, $Vehicle_Str_Arr)) {
                $returnArr['Action'] = "1";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_SERVICES_AVAIL_FOR_JOB_LOC";
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_JOB_LOCATION_NOT_ALLOWED";
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_JOB_LOCATION_NOT_ALLOWED";
    }


    setDataResponse($returnArr);
}

// ############################Check Restriction For Pickup and DropOff Location For UberX#########################################
// ################################### Add/Update User Favourite Address ##########################################################
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
    } else {
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
        } else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// ################################### Add/Update User Favourite Address ##########################################################
// #############################Delete User Favourite Address #################################################################
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
        } else {
            $returnArr['message'] = getDriverDetailInfo($iUserId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }


    setDataResponse($returnArr);
}

// #############################Delete User Favourite Address Ends#################################################################
// #########################################################
// #############################Check Vehicle eligble for hail ride #################################################
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

// #############################Check Vehicle eligble for hail ride Ends#################################################################
// ###############################################Get Member Wallet Balance########################################################
if ($type == "GetMemberWalletBalance") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    if ($UserType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iMemberId = "iUserId";
        $eUserType = "Rider";
    } else {
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

// ###############################################Get Member Wallet Balance########################################################
// ###############################################CheckPendingBooking UBERX########################################################
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

// ###############################################CheckPendingBooking UBERX########################################################
// ###############################################UBERX Driver Update worklocation address, lat, long########################################################
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

// ###############################################UBERX Driver Update worklocation address, lat, long########################################################
// ###############################Get Help Category #####################################################################
if ($type == "getHelpDetailCategoty") {
    $status = "Active";
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $eSystem = isset($_REQUEST['eSystem']) ? clean($_REQUEST['eSystem']) : 'General';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }

    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    //added by SP to merge the type getsubHelpdetail here for cubex on 15-10-2019
    $sql = "SELECT * FROM `help_detail_categories` WHERE eStatus='$status' AND vCode='" . $languageCode . "' AND eSystem='DeliverAll' ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $arr_cat = array();
        for ($i = 0; $i < count($Data); $i++) {
            $arr_cat[$i]['iHelpDetailCategoryId'] = $Data[$i]['iHelpDetailCategoryId'];
            $arr_cat[$i]['vTitle'] = $Data[$i]['vTitle'];
            $arr_cat[$i]['eSystem'] = $Data[$i]['eSystem'];
            $arr_cat[$i]['iUniqueId'] = $Data[$i]['iUniqueId'];

            $iUniqueId = $Data[$i]['iUniqueId'];
            $sql_sub = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer,eShowDetail,iHelpDetailId FROM `help_detail` WHERE eStatus='$status'  AND iHelpDetailCategoryId='" . $iUniqueId . "' ORDER BY iDisplayOrder ASC ";
            $Data_sub = $obj->MySQLSelect($sql_sub);

            if (count($Data_sub) > 0) {
                $arr_helpdetail = array();
                for ($j = 0; $j < count($Data_sub); $j++) {
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
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_HELP_DETAIL_NOT_AVAIL";
    }

    setDataResponse($returnData);
}

// ############################ End Get Help Category ################################################################
// ############################ getsubHelpdetail #####################################################################
if ($type == "getsubHelpdetail") {
    $status = "Active";
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $iUniqueId = isset($_REQUEST['iUniqueId']) ? clean($_REQUEST['iUniqueId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }

    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer,eShowDetail,iHelpDetailId FROM `help_detail` WHERE eStatus='$status' AND iHelpDetailCategoryId='" . $iUniqueId . "' ORDER BY iDisplayOrder ASC ";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $arr_helpdetail = array();
        for ($j = 0; $j < count($Data); $j++) {
            $arr_helpdetail[$j]['iHelpDetailId'] = $Data[$j]['iHelpDetailId'];
            $arr_helpdetail[$j]['vTitle'] = $Data[$j]['vTitle'];
            $arr_helpdetail[$j]['tAnswer'] = $Data[$j]['tAnswer'];
            $arr_helpdetail[$j]['eShowFrom'] = $Data[$j]['eShowDetail'];
        }

        $returnData['Action'] = "1";
        $returnData['message'] = $arr_helpdetail;
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_HELP_DETAIL_NOT_AVAIL";
    }

    setDataResponse($returnData);
}

// ############################End getsubHelpdetail #####################################################################
// ############################Start getHelpDetail #####################################################################
if ($type == "getHelpDetail") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $iUniqueId = isset($_REQUEST['iUniqueId']) ? clean($_REQUEST['iUniqueId']) : '';
    $eSystem = isset($_REQUEST['eSystem']) ? clean($_REQUEST['eSystem']) : 'General';
    $languageCode = "";
    if ($appType == "Driver") {
        $languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
    } else {
        $languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
    }

    if ($languageCode == "") {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "SELECT vTitle_" . $languageCode . " as vTitle,tAnswer_" . $languageCode . " as tAnswer,iHelpDetailId, eShowDetail FROM `help_detail` WHERE eStatus='Active' AND (eSystem='DeliverAll' || eSystem='General') AND iHelpDetailCategoryId='" . $iUniqueId . "'";
    $Data = $obj->MySQLSelect($sql);
    if (count($Data) > 0) {
        $arr_helpdetail = array();
        for ($j = 0; $j < count($Data); $j++) {
            $arr_helpdetail[$j]['iHelpDetailId'] = $Data[$j]['iHelpDetailId'];
            $arr_helpdetail[$j]['vTitle'] = $Data[$j]['vTitle'];
            $arr_helpdetail[$j]['tAnswer'] = $Data[$j]['tAnswer'];
            $arr_helpdetail[$j]['eShowFrom'] = $Data[$j]['eShowDetail'];
        }
        $returnData['Action'] = "1";
        $returnData['message'] = $arr_helpdetail;
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_HELP_DETAIL_NOT_AVAIL";
    }

    setDataResponse($returnData);
}

// ############################ End getHelpDetail #####################################################################
// ############################ Start submitTripHelpDetail ############################################################
if ($type == "submitTripHelpDetail") {
    global $generalobj, $obj;
    $iOrderId = isset($_REQUEST['iOrderId']) ? clean($_REQUEST['iOrderId']) : '';
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $iHelpDetailId = isset($_REQUEST['iHelpDetailId']) ? clean($_REQUEST['iHelpDetailId']) : '';
    $vComment = isset($_REQUEST['vComment']) ? clean($_REQUEST['vComment']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : '';
    $current_date = date('Y-m-d H:i:s');
    if ($appType == "Driver") {
        $sql = "SELECT CONCAT(vName,' ',vLastName) as Name FROM `register_driver` WHERE iDriverId='" . $iMemberId . "'";
    } else {
        $sql = "SELECT CONCAT(vName,' ',vLastName) as Name FROM `register_user` WHERE iUserId='" . $iMemberId . "'";
    }

    $Data = $obj->MySQLSelect($sql);
    $Data_trip_help_detail['iOrderId'] = $iOrderId;
    $Data_trip_help_detail['iUserId'] = $iMemberId;
    $Data_trip_help_detail['iHelpDetailId'] = $iHelpDetailId;
    $Data_trip_help_detail['vComment'] = $vComment;
    $Data_trip_help_detail['tDate'] = $current_date;
    $id = $obj->MySQLQueryPerform('trip_help_detail', $Data_trip_help_detail, 'insert');
    if ($id > 0) {
        $vOrderNo = get_value('orders', 'vOrderNo', 'iOrderId', $iOrderId, '', 'true');
        $maildata['iTripId'] = $vOrderNo;
        $maildata['NAME'] = $Data[0]['Name'];
        $maildata['vComment'] = $vComment;
        $maildata['Ddate'] = $current_date;
        $generalobj->send_email_user("USER_ORDER_HELP_DETAIL", $maildata);
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_COMMENT_ADDED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ############################ End submitTripHelpDetail ############################################################
// ############################ Check Available Restaurants ############################################################
if ($type == "loadAvailableRestaurants") {
    //echo "<pre>";print_r($_REQUEST);die;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $fOfferType = isset($_REQUEST["fOfferType"]) ? $_REQUEST["fOfferType"] : ''; // Yes Or No
    $cuisineId = isset($_REQUEST["cuisineId"]) ? $_REQUEST["cuisineId"] : ''; // 1,2,3
    $orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"] : ''; // 1,2,3
    $iCategoryId = isset($_REQUEST["iCategoryId"]) ? $_REQUEST["iCategoryId"] : ''; // 1,2,3
    $vUserDeviceCountry = isset($_REQUEST["vUserDeviceCountry"]) ? $_REQUEST["vUserDeviceCountry"] : '';
    $vUserDeviceCountry = strtoupper($vUserDeviceCountry);
    $vAddress = isset($_REQUEST["vAddress"]) ? $_REQUEST["vAddress"] : '';
    $sortby = isset($_REQUEST["sortby"]) ? $_REQUEST["sortby"] : 'relevance'; // relevance , rating, time, costlth, costhtl
    $searchword = isset($_REQUEST["searchword"]) ? $_REQUEST["searchword"] : '';
    $searchword = strtolower($searchword);
    if ($searchword == "" || $searchword == NULL) {
        $searchword = "";
    }
    $cuisineId_arr = array();
    if ($cuisineId != "") {
        $cuisineId_arr = explode(",", $cuisineId);
    }
    if ($vAddress != "") {
        $vAddress_arr = explode(",", $vAddress);
        $vAddress = end($vAddress_arr);
        $vAddress = trim($vAddress);
    }

    ## Update Demo User's Lat Long As per User's Location ##
    if (SITE_TYPE == "Demo" && $iUserId != "") {
        $uemail = get_value('register_user', 'vEmail', 'iUserId', $iUserId, '', 'true');
        $uemail = explode("-", $uemail);
        $uemail = $uemail[1];

        if ($uemail != "") {
            $sql = "SELECT GROUP_CONCAT(iCompanyId)as companyId FROM company WHERE vEmail LIKE '%$uemail%' AND iServiceId = $iServiceId";
            $db_rec = $obj->MySQLSelect($sql);
            $usercompanyId = $db_rec[0]['companyId'];

            if ($usercompanyId != "") {
                $vLatitude = 'vRestuarantLocationLat';
                $vLongitude = 'vRestuarantLocationLong';

                $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $passengerLat . ") )
      		  * cos( radians( ROUND(" . $vLatitude . ",8) ) )
      			* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $passengerLon . ") )
      			+ sin( radians(" . $passengerLat . ") )
      			* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, company.*  FROM `company`
      			WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' ) AND iCompanyId IN ($usercompanyId) AND eStatus = 'Active' AND iServiceId = $iServiceId
      			HAVING distance < " . $USER_STORE_RANGE . " ORDER BY distance ASC LIMIT 0,1";
                $Data = $obj->MySQLSelect($sql);

                if (count($Data) == 0) {
                    $sql = "SELECT iCompanyId FROM company WHERE iCompanyId IN ($usercompanyId) AND eStatus = 'Active' AND iServiceId = '" . $iServiceId . "' AND eStoreLocationUpdate = 'NO' LIMIT 0,1";
                    $CompanyData = $obj->MySQLSelect($sql);
                    $CurrentDate = date("Y-m-d H:i:s");
                    if (count($CompanyData) > 0) {
                        $updateCompanyId = $CompanyData[0]['iCompanyId'];
                        $updateQuery = "UPDATE company SET vRestuarantLocationLat='" . $passengerLat . "', vRestuarantLocationLong = '" . $passengerLon . "', eStoreLocationUpdate = 'Yes', eStoreLocationUpdateDateTime = '" . $CurrentDate . "' WHERE iCompanyId = '" . $updateCompanyId . "'";
                        $obj->sql_query($updateQuery);
                    } else {
                        $sql = "SELECT iCompanyId FROM company WHERE iCompanyId IN ($usercompanyId) AND eStatus = 'Active' AND iServiceId = '" . $iServiceId . "' AND eStoreLocationUpdate = 'Yes' ORDER BY eStoreLocationUpdateDateTime ASC LIMIT 0,1";
                        $NewCompanyData = $obj->MySQLSelect($sql);
                        $newupdateCompanyId = $NewCompanyData[0]['iCompanyId'];
                        $updateQuery = "UPDATE company SET vRestuarantLocationLat='" . $passengerLat . "', vRestuarantLocationLong = '" . $passengerLon . "',  eStoreLocationUpdateDateTime = '" . $CurrentDate . "' WHERE iCompanyId = '" . $newupdateCompanyId . "'";
                        $obj->sql_query($updateQuery);
                    }
                }
            }
        }
    }
    ## Update Demo User's Lat Long As per User's Location ##
    $Data = getNearRestaurantArr($passengerLat, $passengerLon, $iUserId, $fOfferType, $searchword, $vAddress, $iServiceId);
    $totalsearchcuisinerestaurants = 0;
    $Data = array_values($Data);
    $dataNewArr = array();
    for ($c = 0; $c < count($Data); $c++) {
        $iCompanyId = $Data[$c]['iCompanyId'];
        if ($Data[$c]['vImage'] != "") {
            $Data[$c]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/3_' . $Data[$c]['vImage'];
        }
        //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
        if (isset($Data[$c]['vDemoStoreImage']) && $Data[$c]['vDemoStoreImage'] != "" && SITE_TYPE == "Demo") {
            $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $Data[$c]['vDemoStoreImage'];
            if (file_exists($demoImgPath)) {
                $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $Data[$c]['vDemoStoreImage'];
                $Data[$c]['vImage'] = $demoImgUrl;
            }
        }
        //echo "<pre>";print_r($Data[$c]['vImage']);die;
        //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
        if ($Data[$c]['vCoverImage'] != "") {
            $Data[$c]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/' . $Data[$c]['vCoverImage'];
        }

        $isRemoveRestaurantIntoList = "No";
        // # Checking For Selected Cuisine ##
        $Restaurant_Cuisine_Id_str = $Data[$c]['Restaurant_Cuisine_Id'];
        $Restaurant_Cuisine_Id_arr = explode(",", $Restaurant_Cuisine_Id_str);
        $match_cusisine_result_arr = array_intersect($cuisineId_arr, $Restaurant_Cuisine_Id_arr);

        if (count($match_cusisine_result_arr) == 0 && count($cuisineId_arr) > 0) {
            $isRemoveRestaurantIntoList = "Yes";
        }

        // # Checking For Selected Cuisine ##
        // # Checking For Search Keyword ##
        $vCompany = strtolower($Data[$c]['vCompany']);
        $Restaurant_Cuisine = strtolower($Data[$c]['Restaurant_Cuisine']);
        if (((!preg_match("/$searchword/i", $vCompany)) && (!preg_match("/$searchword/i", $Restaurant_Cuisine))) && $searchword != "") {
            $isRemoveRestaurantIntoList = "Yes";
        }

        // # Checking For Search Keyword ##
        // # Getting Nos of restaurants matching with cuisine searchtext ##
        if (preg_match("/$searchword/i", $Restaurant_Cuisine) && $searchword != "") {
            $totalsearchcuisinerestaurants = $totalsearchcuisinerestaurants + 1;
        }

        // # Getting Nos of restaurants matching with cuisine searchtext ##
        // # Checking For Food Menu Available for Company Or Not ##
        $CompanyFoodDataCount = $Data[$c]['CompanyFoodDataCount'];
        if ($CompanyFoodDataCount == 0) {
            $isRemoveRestaurantIntoList = "Yes";
        }

        // # Checking For Food Menu Available for Company Or Not ##
        if ($isRemoveRestaurantIntoList != "Yes") {
            $dataNewArr[] = $Data[$c];
        }
    }

    if ($cuisineId != "") {
        $Data = $dataNewArr;
    } else {
        
    }
    $Data_Filter = $Data;
    $Data = array_values($Data_Filter);
    //echo "<pre>";print_r($Data_Filter);
    // ## Sorting Of Restaurants by relevance , rating, time, costlth, costhtl ###
    if ($sortby == "" || $sortby == NULL) {
        $sortby = "relevance";
    }
    if ($sortby == "rating") {
        $sortfield = "vAvgRatingOrig";
        $sortorder = SORT_DESC;
    } elseif ($sortby == "time") {
        $sortfield = "fPrepareTime";
        $sortorder = SORT_ASC;
    } elseif ($sortby == "costlth") {
        $sortfield = "fPricePerPerson";
        $sortorder = SORT_ASC;
    } elseif ($sortby == "costhtl") {

        $sortfield = "fPricePerPerson";
        $sortorder = SORT_DESC;
    } else {
        $sortfield = "restaurantstatus";
        $sortorder = SORT_DESC;
    }
    foreach ($Data as $k => $v) {
        $Data_name[$sortfield][$k] = $v[$sortfield];
        $Data_name['restaurantstatus'][$k] = $v['restaurantstatus'];
    }

    array_multisort($Data_name['restaurantstatus'], SORT_DESC, $Data_name[$sortfield], $sortorder, $Data);
    // ## Sorting Of Restaurants by relevance , rating, time, costlth, costhtl ###
    // ## Sorting Of Demo User Restaurant To Display First ###
    $searchbydemousercompany = "No";
    if (SITE_TYPE == "Demo" && $iUserId != "") {
        $useremail = get_value('register_user', 'vEmail', 'iUserId', $iUserId, '', 'true');
        $useremail = explode("-", $useremail);
        if (count($useremail) > 0) {
            $searchbydemousercompany = "Yes";
            $useremail = $useremail[1];
            for ($k = 0; $k < count($Data); $k++) {
                $companyemail = $Data[$k]['vEmail'];
                if (preg_match("/$useremail/", $companyemail)) {
                    $Data[$k]['eDemoUserCompany'] = "Yes";
                } else {
                    $Data[$k]['eDemoUserCompany'] = "No";
                }
            }
        }
    }

    if ($searchbydemousercompany == "Yes") {

        function cmp($a, $b) {
            if ($a["eDemoUserCompany"] == $b["eDemoUserCompany"]) {
                return 0;
            }
            return ($a["eDemoUserCompany"] < $b["eDemoUserCompany"]) ? 1 : -1;
        }

        usort($Data, "cmp");

        $newData = array();
        $newData = $Data;
        for ($j = 0; $j < count($Data); $j++) {
            if ($Data[$j]['eDemoUserCompany'] == "Yes") {
                if ($j != 0) {
                    //unset($newData[$j]);
                }
            }
        }
        $Data = array_values($newData);
    }
    // ## Sorting Of Demo User Restaurant To Display First ###
    // ## Checking For Pagination ###


    $Data_new = array_values($Data);

    if ($iCategoryId != "") {
        $s_sctSql = "select iCategoryId,eType,iServiceId from store_categories where iCategoryId = " . $iCategoryId;
        $s_sctSqlData = $obj->MySQLSelect($s_sctSql);

        $Data = array();
        foreach ($Data_new as $dkey => $dvalue) {

            if ($s_sctSqlData[0]['eType'] == 'newly_open' && $s_sctSqlData[0]['iServiceId'] == $iServiceId) {
                $date1 = date('Y-m-d H:i:s');
                $date2 = $dvalue['tRegistrationDate'];

                $diff = strtotime($date2) - strtotime($date1);
                $diff_days = abs(round($diff / 86400));

                $sctSql = "select iDaysRange from store_categories where eType='newly_open' AND iServiceId=" . $iServiceId;
                $sctDaysRange = ($sctSql_data[0]['iDaysRange'] != "") ? $sctSql_data[0]['iDaysRange'] : 30;
                if ($diff_days <= $sctDaysRange) {
                    $Data[] = $dvalue;
                }
            } else if ($s_sctSqlData[0]['eType'] == 'offers' && $s_sctSqlData[0]['iServiceId'] == $iServiceId) {
                if ($dvalue['fOfferAppyType'] != "None") {
                    $Data[] = $dvalue;
                }
            } else if ($s_sctSqlData[0]['eType'] == 'list_all' && $s_sctSqlData[0]['iServiceId'] == $iServiceId) {
                $Data[] = $dvalue;
            } else {
                $storCattagsSql = "select iCategoryId from store_category_tags where iCompanyId = " . $dvalue['iCompanyId'] . " AND iCategoryId = " . $iCategoryId;
                $storCattagsData = $obj->MySQLSelect($storCattagsSql);
                if (count($storCattagsData)) {
                    $Data[] = $dvalue;
                }
            }
        }
    }


    $Data_new = array_values($Data);
    $per_page = 5;
    $totalStore = count($Data); //Added By HJ On 18-01-2020 As Per Discuss Between CS and KS Sir
    $TotalPages = ceil(count($Data) / $per_page);
    $pagecount = $page - 1;
    $start_limit = $pagecount * $per_page;
    $Data = array_slice($Data_new, $start_limit, $per_page);
    //$Data = $Data_new;
    $ispriceshow = '';
    $servFields = 'eType';
    $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
    if (!empty($ServiceCategoryData)) {
        if (!empty($ServiceCategoryData[0]['eType'])) {
            $ispriceshow = $ServiceCategoryData[0]['eType'];
        }
    }

    // ## Checking For Pagination ###
    $returnArr['totalStore'] = $totalStore; //Added By HJ On 18-01-2020 As Per Discuss Between CS and KS Sir
    if (!empty($Data)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;
        if ($TotalPages > $page) {
            $returnArr['NextPage'] = $page + 1;
        } else {
            $returnArr['NextPage'] = "0";
        }

        $storeCatIserviceId = $iServiceId;
        /* $storeCatIserviceId = 1;
          if(count($service_categories_ids_arr) == 1)
          {
          $storeCatIserviceId = $service_categories_ids_arr[0];
          }
          else {

          } */

        if (isStoreCategoriesEnable() && $iCategoryId == "") {

            //lang code same as staticpage type as told by KS..
            $vLangCode = "";

            $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';
            $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';

            if (!empty($vGeneralLang)) {
                $vLangCode = $vGeneralLang;
            } else if (!empty($vLang)) {
                $vLangCode = $vLang;
            } else if (!empty($iUserId)) {
                $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
            }
            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            // Store Categories
            $returnArr['CategoryWiseStores'] = array();


            foreach ($Data_new as $dkey => $dvalue) {
                $storCattagsSql = "select iCategoryId from store_category_tags where iCompanyId = " . $dvalue['iCompanyId'];

                $storCattagsData = $obj->MySQLSelect($storCattagsSql);

                if (count($storCattagsData)) {
                    foreach ($storCattagsData as $sctvalue) {
                        $store_cat_sql = "select iCategoryId,JSON_UNQUOTE(JSON_EXTRACT(tCategoryName, '$.tCategoryName_" . $vLangCode . "')) as tCategoryName,JSON_UNQUOTE(JSON_EXTRACT(tCategoryDescription, '$.tCategoryDescription_" . $vLangCode . "')) as tCategoryDescription,tCategoryImage,iDisplayOrder,eType from store_categories where iCategoryId = " . $sctvalue['iCategoryId'] . " AND eStatus = 'Active'";
                        $store_cat_sql_data = $obj->MySQLSelect($store_cat_sql);

                        foreach ($store_cat_sql_data as $sctdata) {
                            $sctName = $sctdata['tCategoryName'];
                            $sctDesc = $sctdata['tCategoryDescription'];
                            $sctDataId = $sctdata['iCategoryId'];
                            $tCategoryImage = $sctdata['tCategoryImage'];
                            $eType = $sctdata['eType'];

                            if (count($returnArr['CategoryWiseStores']) > 0) {
                                $getTitlekey = searchForTitle($sctName, $returnArr['CategoryWiseStores']);
                                if ($getTitlekey > -1) {
                                    if (count($returnArr['CategoryWiseStores'][$getTitlekey]['subData']) < 12) {
                                        $returnArr['CategoryWiseStores'][$getTitlekey]['subData'][] = $dvalue;
                                    }
                                } else {
                                    $returnArr['CategoryWiseStores'][] = array(
                                        'iCategoryId' => $sctDataId,
                                        'vTitle' => $sctName,
                                        'vDescription' => ($sctDesc != "") ? $sctDesc : "",
                                        'vCategoryImage' => ($tCategoryImage != '') ? ($tconfig['tsite_upload_images_store_categories'] . "/" . $tCategoryImage) : "",
                                        'iDisplayOrder' => $sctdata['iDisplayOrder'],
                                        'eType' => $eType,
                                        'subData' => array($dvalue)
                                    );
                                }
                            } else {
                                $returnArr['CategoryWiseStores'][] = array(
                                    'iCategoryId' => $sctDataId,
                                    'vTitle' => $sctName,
                                    'vDescription' => ($sctDesc != "") ? $sctDesc : "",
                                    'vCategoryImage' => ($tCategoryImage != '') ? ($tconfig['tsite_upload_images_store_categories'] . "/" . $tCategoryImage) : "",
                                    'iDisplayOrder' => $sctdata['iDisplayOrder'],
                                    'eType' => $eType,
                                    'subData' => array($dvalue)
                                );
                            }
                        }
                    }
                }

                // Offers - Stores/Restaurants
                if ($dvalue['fOfferAppyType'] != "None") {
                    $sctSql = "select iCategoryId,JSON_UNQUOTE(JSON_EXTRACT(tCategoryName, '$.tCategoryName_" . $vLangCode . "')) as tCategoryName,JSON_UNQUOTE(JSON_EXTRACT(tCategoryDescription, '$.tCategoryDescription_" . $vLangCode . "')) as tCategoryDescription,tCategoryImage,iDisplayOrder,eType from store_categories where eType = 'offers' AND iServiceId = " . $storeCatIserviceId . " AND eStatus = 'Active'";

                    $sctSql_data = $obj->MySQLSelect($sctSql);
                    $sctNameOffer = $sctSql_data[0]['tCategoryName'];
                    $sctDescOffer = $sctSql_data[0]['tCategoryDescription'];
                    $sctDataId = $sctSql_data[0]['iCategoryId'];

                    $getTitlekey = searchForTitle($sctNameOffer, $returnArr['CategoryWiseStores']);
                    if ($getTitlekey > -1) {
                        if (count($returnArr['CategoryWiseStores'][$getTitlekey]['subData']) < 12) {
                            $returnArr['CategoryWiseStores'][$getTitlekey]['subData'][] = $dvalue;
                        }
                    } else {

                        $returnArr['CategoryWiseStores'][] = array(
                            'iCategoryId' => $sctDataId,
                            'vTitle' => $sctNameOffer,
                            'vDescription' => ($sctDescOffer != "") ? $sctDescOffer : "",
                            'vCategoryImage' => ($sctSql_data[0]['tCategoryImage'] != "") ? ($tconfig['tsite_upload_images_store_categories'] . "/" . $sctSql_data[0]['tCategoryImage']) : "",
                            'iDisplayOrder' => $sctSql_data[0]['iDisplayOrder'],
                            'eType' => $sctSql_data[0]['eType'],
                            'subData' => array($dvalue)
                        );
                    }
                }

                // Newly Open Stores/Restaurants
                $date1 = date('Y-m-d H:i:s');
                $date2 = $dvalue['tRegistrationDate'];

                $diff = strtotime($date2) - strtotime($date1);
                $diff_days = abs(round($diff / 86400));

                $sctSql = "select iCategoryId,JSON_UNQUOTE(JSON_EXTRACT(tCategoryName, '$.tCategoryName_" . $vLangCode . "')) as tCategoryName,JSON_UNQUOTE(JSON_EXTRACT(tCategoryDescription, '$.tCategoryDescription_" . $vLangCode . "')) as tCategoryDescription,tCategoryImage,iDisplayOrder,iDaysRange,eType from store_categories where eType = 'newly_open' AND iServiceId = " . $storeCatIserviceId . " AND eStatus = 'Active'";
                $sctSql_data = $obj->MySQLSelect($sctSql);
                $sctNameNew = $sctSql_data[0]['tCategoryName'];
                $sctDescNew = $sctSql_data[0]['tCategoryDescription'];
                $sctDataId = $sctSql_data[0]['iCategoryId'];

                $sctDaysRange = ($sctSql_data[0]['iDaysRange'] != "") ? $sctSql_data[0]['iDaysRange'] : 30;
                if ($diff_days <= $sctDaysRange) {
                    $getTitlekey = searchForTitle($sctNameNew, $returnArr['CategoryWiseStores']);
                    if ($getTitlekey > -1) {
                        if (count($returnArr['CategoryWiseStores'][$getTitlekey]['subData']) < 12) {
                            $returnArr['CategoryWiseStores'][$getTitlekey]['subData'][] = $dvalue;
                        }
                    } else {

                        $returnArr['CategoryWiseStores'][] = array(
                            'iCategoryId' => $sctDataId,
                            'vTitle' => $sctNameNew,
                            'vDescription' => ($sctDescNew != "") ? $sctDescNew : "",
                            'vCategoryImage' => ($sctSql_data[0]['tCategoryImage'] != "") ? ($tconfig['tsite_upload_images_store_categories'] . "/" . $sctSql_data[0]['tCategoryImage']) : "",
                            'iDisplayOrder' => $sctSql_data[0]['iDisplayOrder'],
                            'eType' => $sctSql_data[0]['eType'],
                            'subData' => array($dvalue)
                        );
                    }
                }
            }

            // All Stores/Restaurants
            $storCatAllsql = "select iCategoryId,JSON_UNQUOTE(JSON_EXTRACT(tCategoryName, '$.tCategoryName_" . $vLangCode . "')) as tCategoryName,JSON_UNQUOTE(JSON_EXTRACT(tCategoryDescription, '$.tCategoryDescription_" . $vLangCode . "')) as tCategoryDescription,tCategoryImage,iDisplayOrder,eType from store_categories where eType = 'list_all' AND iServiceId = " . $storeCatIserviceId;
            $storCatAlldata = $obj->MySQLSelect($storCatAllsql);
            $sctNameAll = $storCatAlldata[0]['tCategoryName'];
            $sctDescAll = $storCatAlldata[0]['tCategoryDescription'];
            $sctDataId = $storCatAlldata[0]['iCategoryId'];
            $returnArr['CategoryWiseStores'][] = array(
                'iCategoryId' => $sctDataId,
                'vTitle' => $sctNameAll,
                'vDescription' => ($sctDescAll != "") ? $sctDescAll : "",
                'vCategoryImage' => ($storCatAlldata[0]['tCategoryImage'] != "") ? ($tconfig['tsite_upload_images_store_categories'] . "/" . $storCatAlldata[0]['tCategoryImage']) : "",
                'iDisplayOrder' => $storCatAlldata[0]['iDisplayOrder'],
                'eType' => $storCatAlldata[0]['eType'],
                'subData' => $Data
            );

            usort($returnArr['CategoryWiseStores'], function ($a, $b) {
                return $a["iDisplayOrder"] - $b["iDisplayOrder"];
            });

            foreach ($returnArr['CategoryWiseStores'] as $catkey => $catvalue) {

                if ($returnArr['CategoryWiseStores'][$catkey]['eType'] == "list_all") {
                    if ($totalStore > 6) {
                        $returnArr['CategoryWiseStores'][$catkey]['IS_SHOW_ALL'] = "Yes";
                    } else {
                        $returnArr['CategoryWiseStores'][$catkey]['IS_SHOW_ALL'] = "No";
                    }
                } else {
                    $countSubData = count($returnArr['CategoryWiseStores'][$catkey]['subData']);
                    if ($countSubData > 6) {
                        $returnArr['CategoryWiseStores'][$catkey]['IS_SHOW_ALL'] = "Yes";
                    } else {
                        $returnArr['CategoryWiseStores'][$catkey]['IS_SHOW_ALL'] = "No";
                    }
                }

                if ($returnArr['CategoryWiseStores'][$catkey]['vTitle'] != $sctNameAll) {
                    shuffle($returnArr['CategoryWiseStores'][$catkey]['subData']);

                    $shuffled_arr = $returnArr['CategoryWiseStores'][$catkey]['subData'];
                    $movetolast = array();
                    foreach ($shuffled_arr as $mkey => $mvalue) {
                        if (strtolower($mvalue['restaurantstatus']) == 'closed') {
                            $movetolast[] = $shuffled_arr[$mkey];
                            unset($shuffled_arr[$mkey]);
                        }
                    }

                    $returnArr['CategoryWiseStores'][$catkey]['subData'] = array_merge($shuffled_arr, $movetolast);
                }

                if($returnArr['CategoryWiseStores'][$catkey]['iCategoryId'] == "")
                {
                    unset($returnArr['CategoryWiseStores'][$catkey]);
                }
            }
        }

        // setDataResponse($returnArr['CategoryWiseStores']);
        $returnArr['totalsearchcuisinerestaurants'] = $totalsearchcuisinerestaurants;
        $returnArr['ispriceshow'] = $ispriceshow;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_RESTAURANT_FOUND_TXT";
        if (checkFavStoreModule() && !empty($iUserId)) {
            $eFavStore = isset($_REQUEST['eFavStore']) ? clean($_REQUEST['eFavStore']) : ''; // No=> 'Not 
        }
        if ((!empty($fOfferType) && strtoupper($fOfferType) == "YES") || !empty($cuisineId) || (!empty($eFavStore) && strtoupper($eFavStore) == "YES")) {
            $returnArr['message1'] = "LBL_NO_RECORDS_FOUND1";
        }
    }

    //getBanners type start
    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    }
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    // $banners= get_value('banners', 'vImage', 'vCode',$vLanguage,' ORDER BY iDisplayOrder ASC');
    $sql = "SELECT vImage FROM `banners` WHERE vCode = '" . $vLanguage . "' AND eStatus = 'Active' AND iServiceId = '" . $iServiceId . "' ORDER BY iDisplayOrder ASC";
    $banners = $obj->MySQLSelect($sql);
    $bdata = array();
    $count = 0;
    for ($i = 0; $i < count($banners); $i++) {
        if ($banners[$i]['vImage'] != "") {
            $bdata[$count]['vImage'] = $tconfig["tsite_url"] . 'assets/img/images/' . urlencode($banners[$i]['vImage']);
            $count++;
        }
    }

    $returnArr['banner_data'] = !empty($bdata) ? $bdata : '';
    //getBanners type end
    //getCuisineList type start
    $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';

    //$vLanguage = "";
    if (!empty($vGeneralLang)) {
        $vLanguage = $vGeneralLang;
    } /* else if ($iUserId != "") {
      $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
      } */ //commented bc in getbanners section it will taken this

    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $ssqllast = " `company`.`iCompanyId` ASC ";
    $Restaurant_Cuisine_Id_Arr = $db_cuisine_list = $db_cuisine_list_new = $languageLabelsArr = array();
    $Restaurant_Cuisine_Id_str = "";
    $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $passengerLat . ") ) * cos( radians( vRestuarantLocationLat ) ) * cos( radians( vRestuarantLocationLong ) - radians(" . $passengerLon . ") ) + sin( radians(" . $passengerLat . ") ) * sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.iCompanyId FROM `company` WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' AND iServiceId = '" . $iServiceId . "' HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY " . $ssqllast . "";
    $cData = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($Data);die;
    $storeIdArr = array();
    for ($r = 0; $r < count($cData); $r++) {
        $storeIdArr[] = $cData[$r]['iCompanyId'];
    }
    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    $allStoreData = getcuisinelist($storeIdArr, $iUserId, $languageLabelsArr, $iServiceId);
    //echo "<pre>";print_r($allStoreData);die;
    $Data_Company = $allStoreData['companyCuisineArr'];
    $offerMsgArr = $allStoreData['offerMsgArr'];
    $companyCuisineIdArr = $allStoreData['companyCuisineIdArr'];
    //$Data_Company = getNearRestaurantArr($passengerLat, $passengerLon, $iUserId, "No", "", "", $iServiceId);
    //echo "<pre>"; print_r($Data_Company); die;
    $isOfferApply = "No";
    if (count($Data_Company) > 0) {
        foreach ($Data_Company as $companyId => $cuisinArr) {
            $Restaurant_OfferMessage = "";
            if (isset($offerMsgArr[$companyId]['Restaurant_OfferMessage'])) {
                $Restaurant_OfferMessage = trim($offerMsgArr[$companyId]['Restaurant_OfferMessage']);
            }
            $restCuisineArr = array();
            if (isset($companyCuisineIdArr[$companyId])) {
                $restCuisineArr = $companyCuisineIdArr[$companyId];
            }
            if ($Restaurant_OfferMessage != "") {
                $isOfferApply = "Yes";
            }
            for ($d = 0; $d < count($restCuisineArr); $d++) {
                $Restaurant_Cuisine_Id_str .= $restCuisineArr[$d] . ",";
            }
        }
        //$Restaurant_Cuisine_Id_str = substr($Restaurant_Cuisine_Id_str, 0, -1);
        $Restaurant_Cuisine_Id_str = trim($Restaurant_Cuisine_Id_str, ",");
        $Restaurant_Cuisine_Id_Arr = explode(",", $Restaurant_Cuisine_Id_str);
    }
    $Restaurant_Cuisine_Id_Arr = array_unique($Restaurant_Cuisine_Id_Arr);
    //added by SP vImage for cubex on 12-10-2019 
    $defaultImage = $tconfig["tsite_url"] . "webimages/upload/DefaultImg/food_service.png";
    //Added By HJ On 31-10-2019 For Set Cuisine Default Icon as Per Service Category Start Bug - 1382 141 Mantis
    if ($iServiceId != 1) {
        $defaultImage = $tconfig["tsite_url"] . "webimages/upload/DefaultImg/other_services.png";
    }
    //Added By HJ On 31-10-2019 For Set Cuisine Default Icon as Per Service Category End Bug - 1382 141 Mantis
    //$sql = "SELECT cuisineId,cuisineName_" . $vLanguage . " as cuisineName,eStatus,CONCAT('aa',vImage) as vImage  FROM cuisine WHERE iServiceId = '" . $iServiceId . "' AND eStatus = 'Active' ORDER BY cuisineName ASC";
    $sql = "SELECT cuisineId,cuisineName_$vLanguage as cuisineName,eStatus,CASE WHEN vImage != '' THEN CONCAT('" . $tconfig['tsite_upload_images_menu_item_type'] . "/',vImage) ELSE '" . $defaultImage . "' END AS vImage FROM cuisine WHERE iServiceId = '" . $iServiceId . "' AND eStatus = 'Active' ORDER BY cuisineName ASC";
    $db_cuisine_list = $obj->MySQLSelect($sql);
    if (count($db_cuisine_list) > 0) {
        for ($i = 0; $i < count($db_cuisine_list); $i++) {
            $isRemoveCuisineList = "No";
            $cuisineId = $db_cuisine_list[$i]['cuisineId'];
            if (!in_array($cuisineId, $Restaurant_Cuisine_Id_Arr)) {
                $isRemoveCuisineList = "Yes";
            }
            if ($isRemoveCuisineList == "Yes") {
                unset($db_cuisine_list_new[$i]);
            }
        }
    }
    //added by SP for cubex to add all in cuisine list so when click on it show all restaurant on 15-10-2019 
    $db_cuisine_list_new1[0]['cuisineId'] = '';
    $db_cuisine_list_new1[0]['cuisineName'] = $languageLabelsArr['LBL_ALL'];
    $db_cuisine_list_new1[0]['eStatus'] = $languageLabelsArr['LBL_ACTIVE'];
    $db_cuisine_list_new1[0]['vImage'] = $defaultImage;
    //$allCuisines = count($db_cuisine_list_new);
    $db_cuisine_list_new = array_merge($db_cuisine_list_new1, $db_cuisine_list);
    $db_cuisine_list_new = array_values($db_cuisine_list_new);
    $db_cuisine_list = $db_cuisine_list_new;
    if (count($db_cuisine_list) == 0) {
        $db_cuisine_list = "";
    }
    $countryArr['Action'] = "1";
    $countryArr['totalValues'] = count($db_cuisine_list);
    $countryArr['isOfferApply'] = $isOfferApply;
    $countryArr['CuisineList'] = $db_cuisine_list;
    $returnArr['getCuisineList'] = $countryArr;
    //getCuisineList type end
    //echo "<pre>"; print_r($returnArr);die;
    setDataResponse($returnArr);
}




// ############################ Check Available Restaurants ##############################################################
// ############################################### Cuisine list ##########################################################
if ($type == 'getCuisineList') {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '0';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $iServiceId = isset($_REQUEST["iServiceId"]) ? $_REQUEST["iServiceId"] : '';
    $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';

    $vLanguage = "";
    if (!empty($vGeneralLang)) {
        $vLanguage = $vGeneralLang;
    } else if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    }

    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $ssqllast = " `company`.`iCompanyId` ASC ";
    $Restaurant_Cuisine_Id_Arr = $db_cuisine_list = $db_cuisine_list_new = $languageLabelsArr = array();
    $Restaurant_Cuisine_Id_str = "";
    $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $passengerLat . ") ) * cos( radians( vRestuarantLocationLat ) ) * cos( radians( vRestuarantLocationLong ) - radians(" . $passengerLon . ") ) + sin( radians(" . $passengerLat . ") ) * sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.iCompanyId FROM `company` WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' AND iServiceId = '" . $iServiceId . "' HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY " . $ssqllast . "";
    $Data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($Data);die;
    $storeIdArr = array();
    for ($r = 0; $r < count($Data); $r++) {
        $storeIdArr[] = $Data[$r]['iCompanyId'];
    }
    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    $allStoreData = getcuisinelist($storeIdArr, $iUserId, $languageLabelsArr, $iServiceId);
    //echo "<pre>";print_r($allStoreData);die;
    $Data_Company = $allStoreData['companyCuisineArr'];
    $offerMsgArr = $allStoreData['offerMsgArr'];
    $companyCuisineIdArr = $allStoreData['companyCuisineIdArr'];
    //$Data_Company = getNearRestaurantArr($passengerLat, $passengerLon, $iUserId, "No", "", "", $iServiceId);
    //echo "<pre>"; print_r($Data_Company); die;
    $isOfferApply = "No";
    if (count($Data_Company) > 0) {
        foreach ($Data_Company as $companyId => $cuisinArr) {
            $Restaurant_OfferMessage = "";
            if (isset($offerMsgArr[$companyId]['Restaurant_OfferMessage'])) {
                $Restaurant_OfferMessage = trim($offerMsgArr[$companyId]['Restaurant_OfferMessage']);
            }
            $restCuisineArr = array();
            if (isset($companyCuisineIdArr[$companyId])) {
                $restCuisineArr = $companyCuisineIdArr[$companyId];
            }
            if ($Restaurant_OfferMessage != "") {
                $isOfferApply = "Yes";
            }
            for ($d = 0; $d < count($restCuisineArr); $d++) {
                $Restaurant_Cuisine_Id_str .= $restCuisineArr[$d] . ",";
            }
        }
        //$Restaurant_Cuisine_Id_str = substr($Restaurant_Cuisine_Id_str, 0, -1);
        $Restaurant_Cuisine_Id_str = trim($Restaurant_Cuisine_Id_str, ",");
        $Restaurant_Cuisine_Id_Arr = explode(",", $Restaurant_Cuisine_Id_str);
    }
    $Restaurant_Cuisine_Id_Arr = array_unique($Restaurant_Cuisine_Id_Arr);
    //added by SP vImage for cubex on 12-10-2019 
    $defaultImage = $tconfig["tsite_url"] . "webimages/upload/DefaultImg/food_service.png";
    //Added By HJ On 31-10-2019 For Set Cuisine Default Icon as Per Service Category Start Bug - 1382 141 Mantis
    if ($iServiceId != 1) {
        $defaultImage = $tconfig["tsite_url"] . "webimages/upload/DefaultImg/other_services.png";
    }
    //Added By HJ On 31-10-2019 For Set Cuisine Default Icon as Per Service Category End Bug - 1382 141 Mantis
    //$sql = "SELECT cuisineId,cuisineName_" . $vLanguage . " as cuisineName,eStatus,CONCAT('aa',vImage) as vImage  FROM cuisine WHERE iServiceId = '" . $iServiceId . "' AND eStatus = 'Active' ORDER BY cuisineName ASC";
    $sql = "SELECT cuisineId,cuisineName_" . $vLanguage . " as cuisineName,eStatus,CASE WHEN vImage != '' THEN CONCAT('" . $tconfig['tsite_upload_images_menu_item_type'] . "/',vImage) ELSE '" . $defaultImage . "' END AS vImage FROM cuisine WHERE iServiceId = '" . $iServiceId . "' AND eStatus = 'Active' ORDER BY cuisineName ASC";
    $db_cuisine_list = $obj->MySQLSelect($sql);
    if (count($db_cuisine_list) > 0) {
        for ($i = 0; $i < count($db_cuisine_list); $i++) {
            $isRemoveCuisineList = "No";
            $cuisineId = $db_cuisine_list[$i]['cuisineId'];
            if (!in_array($cuisineId, $Restaurant_Cuisine_Id_Arr)) {
                $isRemoveCuisineList = "Yes";
            }
            if ($isRemoveCuisineList == "Yes") {
                unset($db_cuisine_list_new[$i]);
            }
        }
    }
    //added by SP for cubex to add all in cuisine list so when click on it show all restaurant on 15-10-2019 
    $db_cuisine_list_new1[0]['cuisineId'] = '';
    $db_cuisine_list_new1[0]['cuisineName'] = $languageLabelsArr['LBL_ALL'];
    $db_cuisine_list_new1[0]['eStatus'] = $languageLabelsArr['LBL_ACTIVE'];
    $db_cuisine_list_new1[0]['vImage'] = $defaultImage;
    //$allCuisines = count($db_cuisine_list_new);
    $db_cuisine_list_new = array_merge($db_cuisine_list_new1, $db_cuisine_list);
    $db_cuisine_list_new = array_values($db_cuisine_list_new);
    $db_cuisine_list = $db_cuisine_list_new;
    if (count($db_cuisine_list) == 0) {
        $db_cuisine_list = "";
    }
    $countryArr['Action'] = "1";
    $countryArr['totalValues'] = count($db_cuisine_list);
    $countryArr['isOfferApply'] = $isOfferApply;
    $countryArr['CuisineList'] = $db_cuisine_list;
    setDataResponse($countryArr);
}

// ############################################### Cuisine list ##########################################################
// ############################ Check Search Restaurants ##############################################################
if ($type == "loadSearchRestaurants") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $searchword = isset($_REQUEST["searchword"]) ? $_REQUEST["searchword"] : '';
    $searchword = strtolower(trim($searchword));
    $vAddress = isset($_REQUEST["vAddress"]) ? $_REQUEST["vAddress"] : '';
    if ($searchword == "" || $searchword == NULL) {
        $searchword = "";
    }
    if ($vAddress != "") {
        $vAddress_arr = explode(",", $vAddress);
        $vAddress = end($vAddress_arr);
        $vAddress = trim($vAddress);
    }
    $Data = getNearRestaurantArr($passengerLat, $passengerLon, $iUserId, "No", "", $vAddress, $iServiceId);
    for ($i = 0; $i < count($Data); $i++) {
        if ($Data[$i]['vImage'] != "") {
            $Data[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $Data[$i]['iCompanyId'] . '/3_' . $Data[$i]['vImage'];
        }
        if ($Data[$i]['vCoverImage'] != "") {
            $Data[$i]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $Data[$i]['iCompanyId'] . '/' . $Data[$i]['vCoverImage'];
        }
        //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
        if (isset($Data[$i]['vDemoStoreImage']) && $Data[$i]['vDemoStoreImage'] != "" && SITE_TYPE == "Demo") {
            $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $Data[$i]['vDemoStoreImage'];
            if (file_exists($demoImgPath)) {
                $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $Data[$i]['vDemoStoreImage'];
                $Data[$i]['vImage'] = $demoImgUrl;
            }
        }
        //echo "<pre>";print_r($Data[$i]['vImage']);die;
        //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
        $TotalCompanyFoodDataCount = $Data[$i]['CompanyFoodDataCount'];
        if ($TotalCompanyFoodDataCount > 0) {
            $Restaurant_id_str .= $Data[$i]['iCompanyId'] . ",";
        }
    }
    //$Restaurant_id_str = substr($Restaurant_id_str, 0, -1);
    $Restaurant_id_str = trim($Restaurant_id_str, ",");
    $cuisineId_arr = getCompanyBySearchCuisine($iUserId, $searchword, $Restaurant_id_str);
    $Data_Filter = $Data;
    for ($i = 0; $i < count($Data); $i++) {
        $isRemoveRestaurantIntoList = "No";
        // # Checking For Search Keyword ##
        $vCompany = strtolower($Data[$i]['vCompany']);
        if ((!preg_match("/$searchword/i", $vCompany)) && $searchword != "") {
            $isRemoveRestaurantIntoList = "Yes";
        }
        // # Checking For Search Keyword ##
        // # Checking For Food Menu Available for Company Or Not ##
        $CompanyFoodDataCount = $Data[$i]['CompanyFoodDataCount'];
        if ($CompanyFoodDataCount == 0) {
            $isRemoveRestaurantIntoList = "Yes";
        }
        // # Checking For Food Menu Available for Company Or Not ##
        if ($isRemoveRestaurantIntoList == "Yes") {
            unset($Data_Filter[$i]);
        }
    }
    $ispriceshow = '';
    $servFields = 'eType';
    $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
    if (!empty($ServiceCategoryData)) {
        if (!empty($ServiceCategoryData[0]['eType'])) {
            $ispriceshow = $ServiceCategoryData[0]['eType'];
        }
    }
    // echo "<pre>";print_r($Data_Filter);
    $Data = array_values($Data_Filter);
    // ## Sorting Of Restaurants by relevance  ###
    foreach ($Data as $k => $v) {
        $Data_name['restaurantstatus'][$k] = $v['restaurantstatus'];
    }
    array_multisort($Data_name['restaurantstatus'], SORT_DESC, $Data);
    // ## Sorting Of Restaurants by relevance  ###
    if ((!empty($Data) || !empty($cuisineId_arr)) && $searchword != "") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $Data;
        $returnArr['message_cusine'] = $cuisineId_arr;
        $returnArr['ispriceshow'] = $ispriceshow;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_RESTAURANT_FOUND_TXT";
    }
    setDataResponse($returnArr);
}

// ############################ Check Search Restaurants ##############################################################
// ############################ Get Restaurant Details   ##############################################################
if ($type == "GetRestaurantDetails") {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    $CheckNonVegFoodType = isset($_REQUEST["CheckNonVegFoodType"]) ? $_REQUEST["CheckNonVegFoodType"] : 'No';
    $searchword = isset($_REQUEST["searchword"]) ? $_REQUEST["searchword"] : '';
    $searchword = strtolower(trim($searchword));
    if ($searchword == "" || $searchword == NULL) {
        $searchword = "";
    }
    if ($CheckNonVegFoodType == "" || $CheckNonVegFoodType == NULL) {
        $CheckNonVegFoodType = "No";
    }
    $ssql_fav_q = "";
    if (checkFavStoreModule() && !empty($iUserId)) {
        $data = addUpdateFavStore();
        $ssql_fav_q = getFavSelectQuery($iCompanyId, $iUserId);
    }
    $db_company = $obj->MySQLSelect("SELECT * " . $ssql_fav_q . " FROM company WHERE iCompanyId = '" . $iCompanyId . "'");
    if (count($db_company) > 0) {
        $iCompanyId = $db_company[0]['iCompanyId'];
        $CompanyDetails_Arr = getCompanyDetails($iCompanyId, $iUserId, $CheckNonVegFoodType, $searchword, $iServiceId);
        //echo "<pre>";print_r($CompanyDetails_Arr);die;
        $db_company[0]['fPricePerPerson'] = "";
        if ($iServiceId == '1') {
            $db_company[0]['fPricePerPerson'] = isset($CompanyDetails_Arr['fPricePerPersonWithCurrency']) ? $CompanyDetails_Arr['fPricePerPersonWithCurrency'] : '$ 1.00';
        }
        //$db_favorite = $obj->MySQLSelect("select eIsFavourite from favorite_store where iCompanyId = '" . $iCompanyId . "' AND  iUserId = '" . $iUserId . "' AND  eIsFavourite = 'Yes'");
        $db_favorite = $obj->MySQLSelect("select eFavStore AS eIsFavourite from store_favorites where iCompanyId = '" . $iCompanyId . "' AND  iUserId = '" . $iUserId . "' AND  eFavStore = 'Yes'");
        if (count($db_favorite) > 0) {
            $db_company[0]['eIsFavourite'] = 'Yes';
        } else {
            $db_company[0]['eIsFavourite'] = 'No';
        }
        $db_company[0]['fPackingCharge'] = isset($CompanyDetails_Arr['fPackingCharge']) ? $CompanyDetails_Arr['fPackingCharge'] : 0;
        $db_company[0]['fMinOrderValue'] = isset($CompanyDetails_Arr['fMinOrderValue']) ? $CompanyDetails_Arr['fMinOrderValue'] : 1;
        $db_company[0]['fMinOrderValueDisplay'] = isset($CompanyDetails_Arr['fMinOrderValueDisplay']) ? $CompanyDetails_Arr['fMinOrderValueDisplay'] : '';
        $db_company[0]['Restaurant_OfferMessage'] = isset($CompanyDetails_Arr['Restaurant_OfferMessage']) ? $CompanyDetails_Arr['Restaurant_OfferMessage'] : '';
        $db_company[0]['Restaurant_OfferMessage_short'] = isset($CompanyDetails_Arr['Restaurant_OfferMessage_short']) ? $CompanyDetails_Arr['Restaurant_OfferMessage_short'] : '';
        $db_company[0]['Restaurant_OrderPrepareTime'] = isset($CompanyDetails_Arr['Restaurant_OrderPrepareTime']) ? $CompanyDetails_Arr['Restaurant_OrderPrepareTime'] : '0 mins';
        $db_company[0]['monfritimeslot_TXT'] = isset($CompanyDetails_Arr['monfritimeslot_TXT']) ? $CompanyDetails_Arr['monfritimeslot_TXT'] : '';
        $db_company[0]['monfritimeslot_Time'] = isset($CompanyDetails_Arr['monfritimeslot_Time']) ? $CompanyDetails_Arr['monfritimeslot_Time'] : '';
        $db_company[0]['satsuntimeslot_TXT'] = isset($CompanyDetails_Arr['satsuntimeslot_TXT']) ? $CompanyDetails_Arr['satsuntimeslot_TXT'] : '';
        $db_company[0]['satsuntimeslot_Time'] = isset($CompanyDetails_Arr['satsuntimeslot_Time']) ? $CompanyDetails_Arr['satsuntimeslot_Time'] : '';
        $db_company[0]['eNonVegToggleDisplay'] = isset($CompanyDetails_Arr['eNonVegToggleDisplay']) ? $CompanyDetails_Arr['eNonVegToggleDisplay'] : 'No';
        $db_company[0]['RatingCounts'] = isset($CompanyDetails_Arr['RatingCounts']) ? $CompanyDetails_Arr['RatingCounts'] : '';
        $db_company[0]['CompanyDetails'] = $CompanyDetails_Arr;
        $db_company[0]['MenuItemsDetails'] = isset($CompanyDetails_Arr['MenuItemsDataArr']) ? $CompanyDetails_Arr['MenuItemsDataArr'] : array();
        $db_company[0]['RegistrationDate'] = date("Y-m-d", strtotime($db_company[0]['tRegistrationDate'] . ' -1 day '));
        if ($db_company[0]['vImage'] != "") {
            $db_company[0]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/3_' . $db_company[0]['vImage'];
        }
        if ($db_company[0]['vCoverImage'] != "") {
            $db_company[0]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/' . $db_company[0]['vCoverImage'];
        }
        //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
        if (isset($db_company[0]['vDemoStoreImage']) && $db_company[0]['vDemoStoreImage'] != "" && SITE_TYPE == "Demo") {
            $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $db_company[0]['vDemoStoreImage'];
            if (file_exists($demoImgPath)) {
                $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $db_company[0]['vDemoStoreImage'];
                $db_company[0]['vImage'] = $demoImgUrl;
            }
        }
        //echo "<pre>";print_r($Data[$i]['vImage']);die;
        //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
        $vAvgRating = $db_company[0]['vAvgRating'];
        $db_company[0]['vAvgRating'] = ($vAvgRating > 0) ? number_format($db_company[0]['vAvgRating'], 1) : 0;
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_company[0];

        $sql = "SELECT vImage FROM `banners` WHERE vCode = '" . $db_company[0]['vLang'] . "' AND eStatus = 'Active' AND iServiceId = '" . $iServiceId . "' ORDER BY iDisplayOrder ASC";
        $banners = $obj->MySQLSelect($sql);
        $dataOfBanners = array();
        $count = 0;
        for ($i = 0; $i < count($banners); $i++) {
            if ($banners[$i]['vImage'] != "") {
                $dataOfBanners[$count]['vImage'] = $tconfig["tsite_url"] . 'assets/img/images/' . urlencode($banners[$i]['vImage']);
                $count++;
            }
        }
        if (empty($dataOfBanners)) {
            $dataOfBanners = '';
        }
        $returnArr['BANNER_DATA'] = $dataOfBanners;

        //$sql_banners = "SELECT vImage FROM banners WHERE vCode= '" . $db_company[0]['vLang'] . "' AND vImage != '' AND eStatus = 'Active' AND (iServiceId = '0' OR iServiceId = '') ORDER BY iDisplayOrder ASC";
        //$Data_banners = $obj->MySQLSelect($sql_banners);
        //$dataOfBanners = array();
        //$count = 0;
        //for ($i = 0; $i < count($Data_banners); $i++) {
        //    if (isset($Data_banners[$i]['vImage']) && $Data_banners[$i]['vImage'] != "") {
        //        $dataOfBanners[$count]['vImage'] = $tconfig["tsite_url"] . 'assets/img/images/' . $Data_banners[$i]['vImage'];
        //        $count++;
        //    }
        //}
        //$returnArr['BANNER_DATA'] = $dataOfBanners;

        $vGeneralLang = !empty($_REQUEST['vGeneralLang']) ? $_REQUEST['vGeneralLang'] : "EN";
        $returnArr['Restaurant_Safety_Status'] = (!empty($db_company[0]['eSafetyPractices']) && ($db_company[0]['iServiceId'] == 1 || $db_company[0]['iServiceId'] == 2)) ? $db_company[0]['eSafetyPractices'] : "No";
        $safetyimg = "/webimages/icons/DefaultImg/ic_safety.png";
        $returnArr['Restaurant_Safety_Icon'] = (file_exists($tconfig["tpanel_path"] . $safetyimg)) ? $tconfig["tsite_url"] . $safetyimg : "";
        $time = time();
        $returnArr['Restaurant_Safety_URL'] = $tconfig["tsite_url"] . "safety-measures?time_data=" . $time . "&fromlang=" . $vGeneralLang;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_RESTAURANT_FOUND_TXT";
    }

    //echo '<pre>';print_R($CompanyDetails_Arr); exit;
    //Commented BY HJ ON 14-05-3019 For Removed Pagination Data Start As Per Disucss with GP Mam and KL Sir
    /* if (isset($CompanyDetails_Arr['CompanyFoodData']) && !empty($CompanyDetails_Arr['CompanyFoodData'])) {
      $Data_new = array_values($CompanyDetails_Arr['CompanyFoodData']);
      $Data_new = $CompanyDetails_Arr['CompanyFoodData'];
      $per_page = 5;
      $TotalPages = ceil(count($CompanyDetails_Arr['CompanyFoodData']) / $per_page);
      $pagecount = $page - 1;
      $start_limit = $pagecount * $per_page;
      $Data = array_slice($Data_new, $start_limit, $per_page);
      $CompanyDetails_Arr['CompanyFoodData'] = $Data;
      if ($page > 1) {
      $CompanyDetails_Arr['Recomendation_Arr'] = array();
      }
      } */
    //Commented BY HJ ON 14-05-3019 For Removed Pagination Data End As Per Disucss with GP Mam and KL Sir
    //Commented BY HJ ON 14-05-3019 For Removed Pagination Data Start As Per Disucss with GP Mam and KL Sir
    /* if ((!empty($db_company))) {
      $returnArr['Action'] = "1";
      $returnArr['message'] = $db_company[0];
      if (isset($CompanyDetails_Arr['CompanyFoodData']) && !empty($CompanyDetails_Arr['CompanyFoodData'])) {
      if ($TotalPages > $page) {
      $returnArr['NextPage'] = $page + 1;
      } else {
      $returnArr['NextPage'] = "0";
      }
      } else {
      $returnArr['NextPage'] = "0";
      }
      } else {
      $returnArr['Action'] = "0";
      $returnArr['message'] = "LBL_NO_RESTAURANT_FOUND_TXT";
      } */
    //Commented BY HJ ON 14-05-3019 For Removed Pagination Data End As Per Disucss with GP Mam and KL Sir
    // echo '<pre>';
    // print_r($returnArr);
    // exit;
    setDataResponse($returnArr);
}

// ############################ Get Restaurant Details   ##############################################################
// ################################## Restaurant Signup ###############################################################
if ($type == "signup_company") {
    $vCompany = isset($_REQUEST["vCompany"]) ? $_REQUEST["vCompany"] : '';
    $email = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $email = strtolower($email);
    $phone_mobile = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $password = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '';
    $iGcmRegId = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $phoneCode = isset($_REQUEST["PhoneCode"]) ? $_REQUEST["PhoneCode"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $vCurrency = isset($_REQUEST["vCurrency"]) ? $_REQUEST["vCurrency"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $user_type = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Company';
    $eSignUpType = isset($_REQUEST["eSignUpType"]) ? $_REQUEST["eSignUpType"] : 'Normal';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    $vImageURL = isset($_REQUEST["vImageURL"]) ? $_REQUEST["vImageURL"] : '';
    $Data = array();

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
    } else {
        $first = substr($phone_mobile, 0, 1);
        if ($first == "0") {
            $phone_mobile = substr($phone_mobile, 1);
        }
    }
    $eSystem = "DeliverAll";
    if ($phone_mobile != "") {
        $checPhoneExist = $generalobj->checkMemberDataInfo($phone_mobile, "", $user_type, $CountryCode, "", $eSystem); //Added By HJ On 09-09-2019 For Chekc User Country and Mobile Number When Register
    }
    if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MOBILE_EXIST";
        setDataResponse($returnArr);
    } else if (isset($checPhoneExist['status']) && $checPhoneExist['status'] == 2) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
        setDataResponse($returnArr);
    }
    // $sql    = "SELECT * FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$phone_mobile'!='',vPhone = '$phone_mobile',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
    $sql = "SELECT * FROM company WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0)";
    $check_passenger = $obj->MySQLSelect($sql);
    // $Password_passenger = $generalobj->encrypt($password);
    if ($password != "") {
        $Password_passenger = $generalobj->encrypt_bycrypt($password);
    } else {
        $Password_passenger = "";
    }
    //if (count($check_passenger) > 0) {
    if (isset($check_passenger[0]['vEmail']) && strtolower($check_passenger[0]['vEmail']) == $email) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ALREADY_REGISTERED_TXT";
        echo json_encode($returnArr);
        exit;
    } else {
        $Data['vCompany'] = $vCompany;
        $Data['vEmail'] = $email;
        $Data['vPhone'] = $phone_mobile;
        $Data['vPassword'] = $Password_passenger;
        $Data['iGcmRegId'] = $iGcmRegId;
        $Data['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
        $Data['vLang'] = $vLang;
        $Data['vCode'] = $phoneCode;
        $Data['vCountry'] = $CountryCode;
        $Data['eDeviceType'] = $deviceType;
        $Data['vCurrencyCompany'] = $vCurrency;
        $Data['tRegistrationDate'] = @date('Y-m-d H:i:s');
        $Data['eSignUpType'] = $eSignUpType;
        $Data['iServiceId'] = $iServiceId;
        $Data['eSystem'] = $eSystem;
        $Data['eStatus'] = "Inactive";
        if ($eSignUpType == "Facebook" || $eSignUpType == "Google") {
            $Data['eStatus'] = "Active";
        }
        $random = substr(md5(rand()), 0, 7);
        $Data['tDeviceSessionId'] = session_id() . time() . $random;
        $Data['tSessionId'] = session_id() . time();
        $Data['vTimeZone'] = get_value('country', 'vTimeZone', 'vCountryCode', $CountryCode, '', 'true');
        if (SITE_TYPE == 'Demo') {
            $Data['eStatus'] = 'Active';
        }
        $id = $obj->MySQLQueryPerform("company", $Data, 'insert');
        // $sql_checkLangCode = "SELECT  vCode FROM  language_master WHERE `eStatus` = 'Active' AND `eDefault` = 'Yes' ";
        // $Data_checkLangCode = $obj->MySQLSelect($sql_checkLangCode);
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
        for ($i = 0; $i < count($defLangValues); $i++) {
            if ($defLangValues[$i]['eDefault'] == "Yes") {
                $returnArr['DefaultLanguageValues'] = $defLangValues[$i];
            }
        }
        $sql = "SELECT iCurrencyId,vName, vSymbol,iDispOrder, eDefault,Ratio,fThresholdAmount,eStatus  FROM  `currency` WHERE  `eStatus` = 'Active' ORDER BY iDispOrder ASC ";
        $defCurrencyValues = $obj->MySQLSelect($sql);
        $returnArr['LIST_CURRENCY'] = $defCurrencyValues;
        for ($i = 0; $i < count($defCurrencyValues); $i++) {
            if ($defCurrencyValues[$i]['eDefault'] == "Yes") {
                $returnArr['DefaultCurrencyValues'] = $defCurrencyValues[$i];
            }
        }
        if ($id > 0) {
            /* new added */
            $returnArr['Action'] = "1";
            $returnArr['message'] = getCompanyDetailInfo($id);
            $returnArr['message'] = getCustomeNotificationSound($returnArr['message']);
            $returnArr['message']['driverOptionArr'] = getDriverOptions($vLang, $iServiceId); //Added By HJ On 19-06-2020 As Per Discuss With NM
            $maildata['EMAIL'] = $email;
            $maildata['NAME'] = $vCompany;
            $pass_txt = ($returnArr['UpdatedLanguageLabels']['LBL_PASSWORD'] != "") ? $returnArr['UpdatedLanguageLabels']['LBL_PASSWORD'] : "Password";
            //$maildata['PASSWORD'] = $pass_txt . ": " . $password; //Commented By HJ On 11-01-2019 For Hide Password As Per Discuss With QA BM
            $generalobj->send_email_user("STORE_REGISTRATION_USER", $maildata);
            $generalobj->send_email_user("STORE_REGISTRATION_ADMIN", $maildata);
            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    }
}

// ################################## Restaurant Signup ###############################################################
// ############################ Get Option and AddOn Details ##############################################################
if ($type == "GetItemOptionAddonDetails") {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    //Added By HJ On 23-01-2020 For Solved Currency Related Issues Start
    $currencySymbol = "";
    if ($iUserId > 0) {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
        $currencySymbol = $UserDetailsArr['currencySymbol'];
        $Ratio = $UserDetailsArr['Ratio'];
        $vLang = $UserDetailsArr['vLang'];
    } else {
        $currencycode = isset($_REQUEST["vGeneralCurrency"]) ? $_REQUEST["vGeneralCurrency"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Currency Code
        $vLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Language Code
        if (($currencySymbol == "" || $currencySymbol == NULL) && $currencycode != "") {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE vName = '" . $currencycode . "'");
        } else {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'");
        }
        if (count($currencyData) > 0) {
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        } else {
            $currencycode = "USD";
            $currencySymbol = "$";
            $priceRatio = 1.0000;
        }
        if ($vLang == "") {
            $vLang = "EN";
        }
    }
    //Added By HJ On 23-01-2020 For Solved Currency Related Issues End
    $GetAllMenuItemOptionsTopping_Arr = GetAllMenuItemOptionsTopping($iCompanyId, $currencySymbol, $Ratio, $vLang, "Display", $iServiceId);
    if ((!empty($GetAllMenuItemOptionsTopping_Arr))) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $GetAllMenuItemOptionsTopping_Arr;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}

// ############################ Get Option and AddOn Details ##############################################################
// ############################ Start Get All Order Details Restaurant #######################################################
if ($type == "GetAllOrderDetailsRestaurant") {
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    $OrderType = isset($_REQUEST["OrderType"]) ? $_REQUEST["OrderType"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : "";
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $per_page = 10;
    if ($OrderType == 'NEW') {
        $statusCode = "1";
    } else if ($OrderType == 'DISPATCHED') {
        $statusCode = "5";
    } else {
        $statusCode = "2,4";
    }
    $sql_all = "SELECT COUNT(iOrderId) As TotalIds FROM orders WHERE  iCompanyId='" . $iCompanyId . "' AND iStatusCode IN ($statusCode)";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $totOrdCount = 0;
    if (isset($data_count_all[0]['TotalIds']) && $data_count_all[0]['TotalIds'] > 0) {
        $totOrdCount = $data_count_all[0]['TotalIds'];
    }
    $TotalPages = ceil($totOrdCount / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    $sql = "SELECT o.vOrderNo,o.iOrderId,o.tOrderRequestDate,o.eTakeaway FROM orders as o LEFT JOIN order_details as od ON od.iOrderId = o.iOrderId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode WHERE o.iCompanyId = '" . $iCompanyId . "' AND o.iStatusCode IN ($statusCode) GROUP BY o.iOrderId ORDER BY o.iOrderId DESC" . $limit;
    $db_orders = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($db_orders);die;
    //Added By HJ On 09-05-2019 For Optimize Code Start
    $orderIds = "";
    for ($r = 0; $r < count($db_orders); $r++) {
        $orderIds .= ",'" . $db_orders[$r]['iOrderId'] . "'";
    }
    $orderDataCountArr = array();
    if ($orderIds != "") {
        $orderIds = trim($orderIds, ",");
        $orderData = $obj->MySQLSelect("SELECT COUNT(od.iOrderDetailId) as total,od.iOrderId FROM order_details as od WHERE od.iOrderId IN ($orderIds) GROUP BY od.iOrderId");
        for ($g = 0; $g < count($orderData); $g++) {
            $orderDataCountArr[$orderData[$g]['iOrderId']] = $orderData[$g]['total'];
        }
    }
    //Added By HJ On 09-05-2019 For Optimize Code End
    //echo "<pre>";print_r($orderDataCountArr);die;
    if (!empty($db_orders)) {
        foreach ($db_orders as $key => $value) {
            $serverTimeZone = date_default_timezone_get();
            $date = converToTz($value['tOrderRequestDate'], $vTimeZone, $serverTimeZone, "Y-m-d H:i:s");
            $OrderTime = date('h:iA', strtotime($date));
            $db_orders[$key]['tOrderRequestDate_Org'] = $date;
            $db_orders[$key]['tOrderRequestDateFormatted'] = date('d M, h:iA', strtotime($date));
            $db_orders[$key]['tOrderRequestDate'] = $OrderTime;
            //Commented By HJ On 24-05-2019 For Optimize Code Start
            /* $order_query = "SELECT COUNT(od.iOrderDetailId) as total FROM order_details as od LEFT JOIN  orders as o on o.iOrderId = od.iOrderId WHERE o.iCompanyId = '" . $iCompanyId . "' AND od.iOrderId = '" . $value['iOrderId'] . "' ";
              $orderData = $obj->MySQLSelect($order_query);
              $db_orders[$key]['TotalItems'] = $orderData[0]['total']; */
            //Commented By HJ On 24-05-2019 For Optimize Code End
            $totOrdItems = 0;
            if (isset($orderDataCountArr[$value['iOrderId']])) {
                $totOrdItems = $orderDataCountArr[$value['iOrderId']];
            }
            $db_orders[$key]['TotalItems'] = $totOrdItems;
            $db_orders[$key]['eTakeaway'] = !empty($value['eTakeaway']) ? $value['eTakeaway'] : "No";

            $eConfirm = checkOrderStatus($value['iOrderId'], "2");
            $db_orders[$key]['eConfirm'] = $eConfirm;
            if ($eConfirm == 'Yes' && $statusCode == 1) {
                unset($db_orders[$key]);
            }
        }
        $db_orders = array_values($db_orders);
    }
    if ($TotalPages > $page) {
        $returnArr['NextPage'] = "" . ($page + 1);
    } else {
        $returnArr['NextPage'] = "0";
    }
    //echo "<pre>";
    $getOrderCount = $obj->MySQLSelect("SELECT COUNT(iOrderId) As TotalIds,iStatusCode FROM orders WHERE  iCompanyId='" . $iCompanyId . "' AND iStatusCode IN ('1','2','4','5') GROUP BY iStatusCode");
    $newOrderCount = $dispatchOrderCount = $processOrderCount = 0;
    for ($r = 0; $r < count($getOrderCount); $r++) {
        $iStatusCode = $getOrderCount[$r]['iStatusCode'];
        $TotalIds = $getOrderCount[$r]['TotalIds'];
        if ($iStatusCode == 1) {
            $newOrderCount += $TotalIds;
        } else if ($iStatusCode == 5) {
            $dispatchOrderCount += $TotalIds;
        } else if ($iStatusCode == 2 || $iStatusCode == 4) {
            $processOrderCount += $TotalIds;
        }
    }
    $returnArr['TotalOrders'] = strval($totOrdCount);
    $returnArr['TotalOrdersNewCount'] = strval($newOrderCount);
    $returnArr['TotalOrdersDispatchCount'] = strval($dispatchOrderCount);

    if ((!empty($db_orders))) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_orders;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_ORDERS_FOUND_TXT";
    }
    setDataResponse($returnArr);
}

// ############################ End Get All Order Details For Restaurant #####################################################
// ######################## Get Single Order Details #####################################################################
if ($type == "GetOrderDetailsRestaurant") {
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : "";
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : "Asia/Kolkata";
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Company';
    $GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : 'Company';
    $IS_FROM_HISTORY = isset($_REQUEST["IS_FROM_HISTORY"]) ? $_REQUEST["IS_FROM_HISTORY"] : 'No';
    if ($IS_FROM_HISTORY == "" || $IS_FROM_HISTORY == NULL) {
        $IS_FROM_HISTORY = "No";
    }
    if ($UserType == "" || $UserType == NULL) {
        $UserType = "Company";
    }
    $db_orders = DisplayOrderDetailList($iOrderId, $vTimeZone, $UserType, $IS_FROM_HISTORY);
    ///echo '<pre>';print_r($db_orders);die;
    $storeImgUrl = $tconfig["tsite_upload_images_compnay"];
    if (!empty($db_orders)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_orders[0];
        $iCompanyId = $db_orders[0]['iCompanyId'];
        $iServiceId = $db_orders[0]['iServiceId'];
        $UserDetails_Arr = getUserCurrencyLanguageDetails($db_orders[0]['iUserId'], $iOrderId);
        $GetAllMenuItemOptionsTopping_Arr = GetAllMenuItemOptionsTopping($iCompanyId, $UserDetails_Arr['currencySymbol'], $UserDetails_Arr['Ratio'], $UserDetails_Arr['vLang'], "", $iServiceId);
        $checkOrderRequestStatusArr = checkOrderRequestStatus($iOrderId);
        $action = $checkOrderRequestStatusArr['Action'];
        $AssignStatus = $checkOrderRequestStatusArr['message1'];
        $orderexist = "No";
        if ($AssignStatus == "DRIVER_ASSIGN") {
            $orderexist = checkOrderStatus($iOrderId, "5");
        }
        $ispriceshow = '';
        $servFields = 'eType';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['eType'])) {
                $ispriceshow = $ServiceCategoryData[0]['eType'];
            }
        }
        $returnArr['ispriceshow'] = $ispriceshow;
        $DisplayReorder = checkOrderStatus($iOrderId, "6");
        $REQUEST_REMAINS_SEC = getremainingtimeorderrequest($iOrderId);
        $returnArr['message']['AssignStatus'] = $AssignStatus;
        $returnArr['message']['eOrderPickedByDriver'] = $orderexist;
        $returnArr['message']['REQUEST_REMAINS_SEC'] = $REQUEST_REMAINS_SEC;
        $returnArr['message']['options'] = $GetAllMenuItemOptionsTopping_Arr['options'];
        $returnArr['message']['addon'] = $GetAllMenuItemOptionsTopping_Arr['addon'];
        $returnArr['message']['DisplayReorder'] = $DisplayReorder;
        $returnArr['message']['currencySymbol'] = $UserDetails_Arr['currencySymbol'];
        $returnArr['message']['OrderStatustext'] = GetOrderStatusLogText($iOrderId, $UserType);
        $returnArr['message']['OrderStatusValue'] = str_replace("on", "", GetOrderStatusLogText($iOrderId, $UserType, "Yes"));
        $returnArr['message']['OrderMessage'] = GetOrderStatusLogTextForCancelled($iOrderId, $UserType);

        //added by SP for cubex on 11-10-2019 start
        $logText = GetOrderStatusLogTextForCancelledSplit($iOrderId, $UserType);
        $vStatusNew = $returnArr['message']['vStatus'];

        $sql = "select ru.vLang,ord.eTakeaway, ru.eDriverOption from orders as ord LEFT JOIN company as ru ON ord.iCompanyId=ru.iCompanyId where ord.iOrderId = '" . $iOrderId . "'";
        $data_order_company = $obj->MySQLSelect($sql);
        //echo "<pre>";print_r($data_order_company);die;
        $vLangCode = $data_order_company[0]['vLang'];
        if ($vLangCode == "" || $vLangCode == NULL) {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
        $key_arr = array("#STORE#", "#DriverName#");
        if ($UserType == 'Company') {
            $val_arr1 = $languageLabelsArr['LBL_YOU_TEXT'];
        } else {
            $val_arr1 = $languageLabelsArr['LBL_RESTAURANT_TXT_ADMIN'];
        }
        $val_arr = array($val_arr1, $db_orders[0]['DriverName']);
        $vStatusNew = str_replace($key_arr, $val_arr, $vStatusNew);
        $isOpenDriverSelection = "No";
        $isStoreDriverOption = isStoreDriverAvailable();
        if (isset($data_order_company[0]['eDriverOption']) && strtoupper($data_order_company[0]['eDriverOption']) == "ALL" && $isStoreDriverOption > 0) {
            $isOpenDriverSelection = "Yes";
        }
        $returnArr['message']['isOpenDriverSelection'] = $isOpenDriverSelection;
        $returnArr['message']['vStatus'] = $vStatusNew;
        $returnArr['message']['vStatusNew'] = (!empty($logText['Displaytext'])) ? $logText['Displaytext'] : $vStatusNew;
        $returnArr['message']['CancelOrderMessage'] = $logText['Displaytext1'];
        //added by SP for cubex on 11-10-2019 end

        $sqlc = "select fMinOrderValue,vImage,vAvgRating from `company` where iCompanyId='" . $iCompanyId . "'";
        $data_company_detail = $obj->MySQLSelect($sqlc);
        $fMinOrderValue = $data_company_detail[0]['fMinOrderValue'];
        $fMinOrderValueArr = getPriceUserCurrency($db_orders[0]['iUserId'], "Passenger", $fMinOrderValue);
        $fMinOrderValue = $fMinOrderValueArr['fPrice'];
        $returnArr['message']['fMinOrderValue'] = $fMinOrderValue;
        $returnArr['message']['companyImage'] = $storeImgUrl . "/" . $iCompanyId . "/" . $data_company_detail[0]['vImage'];
        $returnArr['message']['vAvgRating'] = $data_company_detail[0]['vAvgRating'];
        //Added BY HJ On 26-06-2019 For Display In App Start
        //echo "<pre>";print_r($returnArr);die;
        $vInstruction = "";
        if (isset($db_orders[0]['vInstruction']) && $db_orders[0]['vInstruction'] != "") {
            $vInstruction = $db_orders[0]['vInstruction'];
        }
        $returnArr['message']['vInstruction'] = $vInstruction;
        //Added BY HJ On 26-06-2019 For Display In App End
        if ($DisplayReorder == "Yes") {
            $query = "SELECT * FROM order_details WHERE iOrderId = '" . $iOrderId . "'";
            $orderDetails = $obj->MySQLSelect($query);
            $DataReorder = array();
            for ($i = 0; $i < count($orderDetails); $i++) {
                $DataReorder[$i] = DisplayOrderDetailItemList_ForReorder($orderDetails[$i]['iOrderDetailId'], $db_orders[0]['iUserId'], "Passenger", $db_orders[0]['iCompanyId'], $iServiceId);
            }
            $returnArr['message']['DataReorder'] = $DataReorder;
        }
        //Get Prescription Images from orderid done by sneha start
        $getImages = $obj->MySQLSelect("Select * from prescription_images WHERE order_id = '" . $iOrderId . "'");
        foreach ($getImages as $key => $value) {
            $prescriptionimage[] = $tconfig['tsite_upload_prescription_image'] . '/' . $value['vImage'];
        }

        if (!empty($prescriptionimage)) {
            $returnArr['message']['PrescriptionImages'] = $prescriptionimage;
        } else {
            $returnArr['message']['PrescriptionImages'] = "";
        }
        //Get Prescription Images from orderid done by sneha end

        $returnArr['message']['eTakeAway'] = !empty($data_order_company[0]['eTakeaway']) ? $data_order_company[0]['eTakeaway'] : "No";
        $returnArr['message']['eTakeAwayPickedUpNote'] = "";
        if ($DisplayReorder == 'Yes') {
            $returnArr['message']['eTakeAwayPickedUpNote'] = str_replace('#RESTAURANT_NAME#', $db_orders[0]['vCompany'], $languageLabelsArr['LBL_TAKE_AWAY_ORDER_NOTE']);
        }

        if ($returnArr['message']['eTakeAway'] == 'Yes') {
            $returnArr['message']['vStatusNew'] = $languageLabelsArr['LBL_OREDR_PICKED_UP_TXT'];
        }

        $returnArr['DeliveryPreferences']['Enable'] = (isDeliveryPreferenceEnable() == true) ? 'Yes' : 'No';

        if (isDeliveryPreferenceEnable()) {
            $selectedPrefSql = "SELECT selectedPreferences, vImageDeliveryPref FROM orders WHERE iOrderId = " . $iOrderId;
            $selectedPrefData = $obj->MySQLSelect($selectedPrefSql);

            $selectedPrefIds = "";
            if ($selectedPrefData[0]['selectedPreferences'] != "") {
                $selectedPrefIds = $selectedPrefData[0]['selectedPreferences'];
            }
            $ssql = "";
            if (strtolower($GeneralUserType) == 'company') {
                $ssql .= " WHERE ePreferenceFor = 'Store' AND iPreferenceId IN (" . $selectedPrefIds . ")";
            } elseif (strtolower($GeneralUserType) == 'driver') {
                $ssql .= " WHERE ePreferenceFor = 'Provider' AND iPreferenceId IN (" . $selectedPrefIds . ")";
            } elseif (strtolower($GeneralUserType) == 'passenger') {
                $ssql .= " WHERE iPreferenceId IN (" . $selectedPrefIds . ")";
            }

            $deliveryPrefSql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_EXTRACT(tTitle, '$.tTitle_" . $vLangCode . "')) as tTitle, JSON_UNQUOTE(JSON_EXTRACT(tDescription, '$.tDescription_" . $vLangCode . "')) as tDescription, ePreferenceFor, eImageUpload, iDisplayOrder, eContactLess, eStatus FROM delivery_preferences " . $ssql;


            $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);

            $returnArr['DeliveryPreferences']['vTitle'] = ($ePreferenceFor == "Store") ? $languageLabelsArr['LBL_USER_PREF'] : $languageLabelsArr['LBL_DELIVERY_PREF'];

            $returnArr['DeliveryPreferences']['vImageDeliveryPref'] = "";
            if (strtolower($GeneralUserType) != 'passenger') {
                $returnArr['DeliveryPreferences']['isContactLessDeliverySelected'] = 'No';
                $returnArr['DeliveryPreferences']['isPreferenceImageUploadRequired'] = 'No';

                foreach ($deliveryPrefSqlData as $dvalue) {
                    if ($dvalue['eContactLess'] == "Yes") {
                        $returnArr['DeliveryPreferences']['isContactLessDeliverySelected'] = 'Yes';
                    }

                    if ($dvalue['eImageUpload'] == "Yes") {
                        $returnArr['DeliveryPreferences']['isPreferenceImageUploadRequired'] = 'Yes';
                    }
                }
            }

            if (strtolower($GeneralUserType) == 'passenger' || strtolower($GeneralUserType) == 'driver') {
                if ($selectedPrefData[0]['vImageDeliveryPref'] != "") {
                    $returnArr['DeliveryPreferences']['vImageDeliveryPref'] = $tconfig['tsite_upload_order_delivery_pref_images'] . $selectedPrefData[0]['vImageDeliveryPref'];
                }
            }

            $returnArr['DeliveryPreferences']['Data'] = $deliveryPrefSqlData;
            if ((strtolower($GeneralUserType) == 'company' || strtolower($GeneralUserType) == 'driver' || strtolower($GeneralUserType) == 'passenger') && $selectedPrefIds == "") {
                $returnArr['DeliveryPreferences']['Enable'] = 'No';
                unset($returnArr['DeliveryPreferences']['vTitle']);
                unset($returnArr['DeliveryPreferences']['Data']);
                unset($returnArr['DeliveryPreferences']['isContactLessDeliverySelected']);
                unset($returnArr['DeliveryPreferences']['isPreferenceImageUploadRequired']);
                unset($returnArr['DeliveryPreferences']['vImageDeliveryPref']);
            } else {
                if (count($returnArr['DeliveryPreferences']['Data']) == 0) {
                    $returnArr['DeliveryPreferences']['Enable'] = 'No';
                    unset($returnArr['DeliveryPreferences']['vTitle']);
                    unset($returnArr['DeliveryPreferences']['Data']);
                    unset($returnArr['DeliveryPreferences']['isContactLessDeliverySelected']);
                    unset($returnArr['DeliveryPreferences']['isPreferenceImageUploadRequired']);
                    unset($returnArr['DeliveryPreferences']['vImageDeliveryPref']);
                }
            }
        }


        // $vLangCode = 'AR';
        $eDirectionCode = get_value('language_master', 'eDirectionCode', 'vCode', $vLangCode, '', 'true');

        $meta = $generalobj->getStaticPage(47, $vLangCode);
        $kotBillFormat = $meta[0]['tPageDesc_' . $vLangCode];
        $kotBillFormat = strip_tags($kotBillFormat);
        $kotBillFormat = explode('#', $kotBillFormat);
        $kotBillFormat = array_map('trim', $kotBillFormat);
        $kotBillFormat = array_values(array_filter($kotBillFormat));
        
        $html_content = array(
            'COMPANY_NAME'      => '<span style="font-size: 20px"><b>'.$db_orders[0]['vCompany'].'</b></span>',
            'ORDER_DATETIME'    => $db_orders[0]['tOrderRequestDate'],
            'ORDER_NO'          => $db_orders[0]['vOrderNo'],
            'ORDER_VIA'         => PROJECT_SITE_NAME,
            'CUSTOMER_NAME'     => $db_orders[0]['UserName'],
            'ITEM_LIST'         => $db_orders[0]['itemlist']
        );
        
        $receiptPdfData = getReceiptPdf($kotBillFormat, $html_content, $eDirectionCode, $vLangCode, $db_orders[0]['iServiceId']);
        $returnArr['message']['tReceiptData'] = $receiptPdfData;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_ORDERS_FOUND_TXT";
    }
    //echo "<pre>";  print_r($returnArr);die;
    setDataResponse($returnArr);
}

// ######################## End Get Single Order Details ###################################################################
// ######################## Update Single Order Details ###################################################################
if ($type == "UpdateOrderDetailsRestaurant") {
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : "";
    $iOrderDetailId = isset($_REQUEST["iOrderDetailId"]) ? $_REQUEST["iOrderDetailId"] : "";
    $eAvailable = isset($_REQUEST["eAvailable"]) ? $_REQUEST["eAvailable"] : "Yes";
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : "";
    $where = " iOrderDetailId = '" . $iOrderDetailId . "'";
    $Data_update_order_details['eAvailable'] = $eAvailable;
    $OrderDetail_Update_Id = $obj->MySQLQueryPerform("order_details", $Data_update_order_details, 'update', $where);
    $Order_data = calculateOrderFare($iOrderId);
    $where = " iOrderId = '" . $iOrderId . "'";
    $Data_update_order['fSubTotal'] = $Order_data['fSubTotal'];
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
    $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order, 'update', $where);
    if ($Order_Update_Id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_ORDER_DETAILS_UPDATE";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_ORDERS_FOUND_TXT";
    }

    setDataResponse($returnArr);
}

// ######################## End Update Single Order Details ###############################################################
// ######################## Get Cancel Reason #############################################################################
if ($type == "GetCancelReasons") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : "";
    $eUserType = isset($_REQUEST["eUserType"]) ? $_REQUEST["eUserType"] : "";
    $iTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : "";
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId);
    }

    $vLang = $UserDetailsArr['vLang'];
    $sql = "SELECT vTitle_" . $vLang . " as vTitle,iCancelReasonId FROM cancel_reason WHERE eStatus = 'Active' AND eType = 'DeliverAll' AND (eFor = '" . $GeneralUserType . "' OR eFor='General')";
    $CancelReasonData = $obj->MySQLSelect($sql);
    if (!empty($CancelReasonData)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $CancelReasonData;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }

    setDataResponse($returnArr);
}

// ######################## End Get Cancel Reason #########################################################################
// ######################## Start Order Decline ######################################################
if ($type == "DeclineOrder") {
    $iMemberId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : "";
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : "";
    $vCancelReason = isset($_REQUEST["vCancelReason"]) ? $_REQUEST["vCancelReason"] : "";
    $iReasonId = isset($_REQUEST["iCancelReasonId"]) ? $_REQUEST["iCancelReasonId"] : "";
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : "";
    if ($UserType == 'Driver') {
        $eCancelledBy = 'Driver';
    } else if ($UserType == 'Passenger') {
        $eCancelledBy = 'Passenger';
    } else {
        $eCancelledBy = 'Company';
        $UserType = 'Company'; //added by SP for emailissue on 3-7-2019
    }

    if (!empty($iOrderId)) {
        $sql = "select ru.iUserId, ord.iStatusCode, ord.vOrderNo from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "' AND ord.iStatusCode='8'";
        $data_order = $obj->MySQLSelect($sql);
        if (count($data_order) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_CANCEL_ORDER_ADMIN_TXT";
            $returnArr['DO_RESTART'] = "Yes";
            setDataResponse($returnArr);
        }
    }

    $where = " iOrderId = '" . $iOrderId . "'";
    $Data_update_order['iCancelledById'] = $iMemberId;
    $Data_update_order['eCancelledBy'] = $eCancelledBy;
    $Data_update_order['iReasonId'] = $iReasonId;
    $Data_update_order['vCancelReason'] = $vCancelReason;
    $Data_update_order['iStatusCode'] = '9';
    $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order, 'update', $where);
    $id = createOrderLog($iOrderId, "9");

    // # Send Notification To User ##
    $Message = "OrderDeclineByRestaurant";
    $sql = "select ru.iUserId, ru.iGcmRegId, ru.eDeviceType, ru.tSessionId, ru.vEmail, ru.iAppVersion, ru.vLang, ord.vOrderNo, ord.fWalletDebit,ord.vCouponCode, CONCAT(ru.vName,' ',ru.vLastName) as vUserName from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "'";
    //$sql = "select ru.iUserId, ru.iGcmRegId, ru.eDeviceType, ru.tSessionId, ru.iAppVersion, ru.vLang, ord.vOrderNo from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $vLangCode = $data_order[0]['vLang'];
    $vOrderNo = $data_order[0]['vOrderNo'];
    $iUserId = $data_order[0]['iUserId'];
    $vUserName = $data_order[0]['vUserName'];
    $vEmail = $data_order[0]['vEmail'];
    $fWalletDebit = $data_order[0]['fWalletDebit'];

    ### Insert Wallet Amount into user's account ####
    if ($fWalletDebit > 0) {
        $eUserType = 'Rider';
        $iBalance = $fWalletDebit;
        $eType = 'Credit';
        $eFor = 'Deposit';
        $tDescription = "#LBL_CREDITED_BOOKING_DL#" . $vOrderNo;
        $ePaymentStatus = 'Unsettelled';
        $dDate = Date('Y-m-d H:i:s');
        $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $iBalance, $eType, 0, $eFor, $tDescription, $ePaymentStatus, $dDate);
    }
    ### Insert Wallet Amount into user's account ####
    
      //added by SP on 27-06-2020, promocode usage limit increase..bcz it is done only when order finished..so when cancel that order then other user use it..that is wrong so put it...
    $vCouponCode = $data_order[0]['vCouponCode'];
    if ($vCouponCode != '') {
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
    //added by SP end
    
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $sql = "select vTitle_" . $vLangCode . " as vTitle FROM cancel_reason where iCancelReasonId = '" . $iReasonId . "'";
    $db_sql = $obj->MySQLSelect($sql);
    $vTitle = $db_sql[0]['vTitle'];

    // $vTitle = get_value('cancel_reason', 'vTitle_'.$vLangCode.' as vTitle', 'iCancelReasonId', $iReasonId,'','true');
    $vTitleReasonMessage = ($vCancelReason != "") ? $vCancelReason : $vTitle;
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $alertMsg = $languageLabelsArr['LBL_DECLINE_ORDER_APP_TXT'] . " #" . $vOrderNo . " " . $languageLabelsArr['LBL_REASON_TXT'] . " " . $vTitleReasonMessage;
    $message_arr = array();
    $message_arr['Message'] = $Message;
    $message_arr['iOrderId'] = $iOrderId;
    $message_arr['vOrderNo'] = $vOrderNo;
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['tSessionId'] = $data_order[0]['tSessionId'];
    ;
    $message_arr['eSystem'] = "DeliverAll";
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }

    $alertSendAllowed = true;
    /* For PubNub Setting */
    $tableName = "register_user";
    $iMemberId_VALUE = $iUserId;
    $iMemberId_KEY = "iUserId";
    $iAppVersion = $data_order[0]['iAppVersion'];
    $eDeviceType = $data_order[0]['eDeviceType'];
    $iGcmRegId = $data_order[0]['iGcmRegId'];
    $tSessionId = $data_order[0]['tSessionId'];
    $registatoin_ids = $iGcmRegId;
    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();
    /* For PubNub Setting Finished */
    if ($alertSendAllowed == true) {
        if ($eDeviceType == "Android") {
            array_push($registation_ids_new, $iGcmRegId);
            $Rmessage = array(
                "message" => $message
            );
            $result = send_notification($registation_ids_new, $Rmessage, 0);
        } else {
            array_push($deviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
        }
    }

    //sleep(3);
    // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
    /* $pubnub = new Pubnub\Pubnub(array(
      "publish_key" => $PUBNUB_PUBLISH_KEY,
      "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
      "uuid" => $uuid
      )); */
    $channelName = "PASSENGER_" . $iUserId;

    if ($eDeviceType == "Ios") {
        sleep(3);
    }
    // $info = $pubnub->publish($channelName, $message);
    /* if ($PUBNUB_DISABLED == "Yes") {
      publishEventMessage($channelName, $message);
      } else {
      $info = $pubnub->publish($channelName, $message);
      } */
    publishEventMessage($channelName, $message);

    // }
    // # Send Notification To User ##
    if ($Order_Update_Id > 0) {

        if ($UserType == "Company") {
            $sql_cmp = "select vCompany from company where iCompanyId = '" . $iMemberId . "'"; //added by SP for mailissues wrong query
            $data_cmp = $obj->MySQLSelect($sql_cmp);
            $cmpname = $data_cmp[0]['vCompany'];

            $decline_arr['UserName'] = $vUserName;
            $decline_arr['CompanyName'] = $cmpname;
            $decline_arr['vOrderNo'] = $vOrderNo;
            $decline_arr['MSG'] = $languageLabelsArr['LBL_REASON_TXT'] . " " . $vTitleReasonMessage; //added by SP for mailissues wrong reason

            $decline_arr_user['vEmail'] = $vEmail;
            $decline_arr_user['UserName'] = $vUserName;
            $decline_arr_user['CompanyName'] = $cmpname;
            $decline_arr_user['vOrderNo'] = $vOrderNo;
            $decline_arr_user['MSG'] = $languageLabelsArr['LBL_REASON_TXT'] . " " . $vTitleReasonMessage; //added by SP for mailissues wrong reason

            $generalobj->send_email_user("COMPANY_DECLINE_ORDER_TO_USER", $decline_arr_user);
            $generalobj->send_email_user("COMPANY_DECLINE_ORDER", $decline_arr);
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_ORDER_DECLINE_BY_RESTAURANT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_ORDERS_FOUND_TXT";
    }

    setDataResponse($returnArr);
}

// ######################## End Order Decline ######################################################################
// ######################## Confirm Order By Restaurant ############################################################
if ($type == "ConfirmOrderByRestaurant") {
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : "";
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : "";
    $ePickedUp = isset($_REQUEST["ePickedUp"]) ? $_REQUEST["ePickedUp"] : "No";

    if (!empty($iOrderId)) {
        $sql = "select ru.iUserId, ord.iStatusCode, ord.vOrderNo from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "' AND ord.iStatusCode='8'";
        $data_order = $obj->MySQLSelect($sql);
        if (count($data_order) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_CANCEL_ORDER_ADMIN_TXT";
            $returnArr['DO_RESTART'] = "Yes";
            setDataResponse($returnArr);
        }
    }
    $Data_update_order = array();
    $where = " iOrderId = '" . $iOrderId . "'";
    if ($ePickedUp == "Yes") {
        $Data_update_order['iStatusCode'] = '6';
    } else {
        $Data_update_order['iStatusCode'] = '2';
    }

    if ($ePickedUp == "Yes") {
        $Data_update_order['ePaid'] = "Yes";
        $Data_update_order['dDeliveryDate'] = @date("Y-m-d H:i:s");
        $Data_update_order['iStatusCode'] = '6';
        $Order_Status_id = createOrderLog($iOrderId, "6");
    }

    $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order, 'update', $where);
    if ($ePickedUp == "No") {
        $id = createOrderLog($iOrderId, "2");
    }
    // # Send Notification To User ##
    if ($ePickedUp == "Yes") {
        $generalobj->orderemaildataDelivered($iOrderId, "Passenger"); //added by HV on 28-03-2020 to send email when user picks up (For takeaway)

        $generalobj->get_benefit_amount_takeaway($iOrderId); //added by HV on 07-05-2020 to send email when takeaway order is completed
    }
    $sql = "select ru.iUserId,ru.iGcmRegId,ru.eDeviceType,ru.tSessionId,ru.iAppVersion,ru.vLang,ord.vOrderNo,ord.eTakeaway,ord.iCompanyId from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $vLangCode = $data_order[0]['vLang'];
    $vOrderNo = $data_order[0]['vOrderNo'];
    $iUserId = $data_order[0]['iUserId'];
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);

    if ($ePickedUp == "Yes") {
        $Message = "OrderDelivered";
        $db_companyname = $obj->MySQLSelect("select vCompany from company where iCompanyId = " . $data_order[0]['iCompanyId']);
        $alertMsg = str_replace('#RESTAURANT_NAME#', $db_companyname[0]['vCompany'], $languageLabelsArr['LBL_TAKE_AWAY_ORDER_NOTE']);
    } else {
        $Message = "OrderConfirmByRestaurant";
        // $alertMsg = $languageLabelsArr['LBL_CONFIRM_ORDER_BY_RESTAURANT_APP_TXT'];
        $alertMsg = str_replace('#STORE_TITLE#', $languageLabelsArr['LBL_STORE'], $languageLabelsArr['LBL_STORE_CONFIRM_ORDER']);
    }
    $message_arr = array();
    $message_arr['Message'] = $Message;
    $message_arr['iOrderId'] = $iOrderId;
    $message_arr['vOrderNo'] = $vOrderNo;
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['tSessionId'] = $data_order[0]['tSessionId'];
    $message_arr['eTakeaway'] = $data_order[0]['eTakeaway'];
    $message_arr['eSystem'] = "DeliverAll";

    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }

    $alertSendAllowed = true;
    /* For PubNub Setting */
    $tableName = "register_user";
    $iMemberId_VALUE = $iUserId;
    $iMemberId_KEY = "iUserId";
    $iAppVersion = $data_order[0]['iAppVersion'];
    $eDeviceType = $data_order[0]['eDeviceType'];
    $iGcmRegId = $data_order[0]['iGcmRegId'];
    $tSessionId = $data_order[0]['tSessionId'];
    $registatoin_ids = $iGcmRegId;
    /* For PubNub Setting Finished */

    $deviceTokens_arr_ios = array();
    $registation_ids_new = array();

    if ($alertSendAllowed == true) {
        if ($eDeviceType == "Android") {
            array_push($registation_ids_new, $iGcmRegId);
            $Rmessage = array(
                "message" => $message
            );
            $result = send_notification($registation_ids_new, $Rmessage, 0);
        } else {
            array_push($deviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
        }
    }

    //sleep(3);
    // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
    /* $pubnub = new Pubnub\Pubnub(array(
      "publish_key" => $PUBNUB_PUBLISH_KEY,
      "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
      "uuid" => $uuid
      )); */
    $channelName = "PASSENGER_" . $iUserId;
    if ($eDeviceType == "Ios") {
        sleep(3);
    }
    /* if ($PUBNUB_DISABLED == "Yes") {
      publishEventMessage($channelName, $message);
      } else {
      $info = $pubnub->publish($channelName, $message);
      } */
    publishEventMessage($channelName, $message);
    // }
    // # Send Notification To User ##
    if ($Order_Update_Id > 0) {
        $returnArr['Action'] = "1";
        if ($ePickedUp == "Yes") {
            $returnArr['message'] = "LBL_PICKUP"; //label remain to put
        } else {
            $returnArr['message'] = "LBL_CONFIRM_ORDER_BY_RESTAURANT";
        }
        $generalobj->orderemaildata($iOrderId, 'Passenger');
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_ORDERS_FOUND_TXT";
    }

    setDataResponse($returnArr);
}

// ######################## End Accept Order By Restaurant #############################################
// Driver app Types
// ######################## Get Live Task Details #####################################################
if ($type == "GetLiveTaskDetailDriver") {
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : "";
    $returnArrDataNew = array();
    $sql = "SELECT iUserId,iDriverId,iCompanyId,vOrderNo,iUserAddressId,iStatusCode,ePaid,ePaymentOption FROM orders where iOrderId = '" . $iOrderId . "'";
    $returnArrData = $obj->MySQLSelect($sql);
    $query = "SELECT vImage,eImgSkip,iVehicleTypeId FROM `trips` WHERE iOrderId = '" . $iOrderId . "'";
    $TripsData = $obj->MySQLSelect($query);
    $Vehiclefields = "iVehicleTypeId,vVehicleType";
    $VehicleTypeDataDriver = get_value('vehicle_type', $Vehiclefields, 'iVehicleTypeId', $TripsData[0]['iVehicleTypeId']);
    $SelectdVehicleTypeId = ($VehicleTypeDataDriver[0]['iVehicleTypeId'] != '') ? $VehicleTypeDataDriver[0]['iVehicleTypeId'] : "";
    $SelectdVehicleType = ($VehicleTypeDataDriver[0]['vVehicleType'] != '') ? $VehicleTypeDataDriver[0]['vVehicleType'] : "";
    $returnArrDataNew['iVehicleTypeId'] = $SelectdVehicleTypeId;
    $returnArrDataNew['vVehicleType'] = $SelectdVehicleType;
    if (!empty($returnArrData)) {
        $returnArrData = $returnArrData[0];
        $iUserId = $returnArrData['iUserId'];
        $iUserAddressId = $returnArrData['iUserAddressId'];
        $iCompanyId = $returnArrData['iCompanyId'];
        $isPhotoUploaded = 'No';
        if (!empty($TripsData)) {
            if ($returnArrData['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'None') {
                $isPhotoUploaded = 'No';
            } else if ($returnArrData['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'No') {
                $isPhotoUploaded = 'Yes';
            } else if ($returnArrData['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'Yes') {
                $isPhotoUploaded = 'Yes';
            }
        }

        $returnArrDataNew['isPhotoUploaded'] = $isPhotoUploaded;
        $cquery = "SELECT iCompanyId,vImage,vCompany,vCaddress AS vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vPhone,vCode FROM company WHERE iCompanyId = '" . $iCompanyId . "'"; // Get vCaddress As Store Location as per Discuss With NM On 26-10-2019
        $CompanyData = $obj->MySQLSelect($cquery);
        if (!empty($CompanyData)) {
            if ($returnArrData['iStatusCode'] == '5') {
                $returnArrDataNew['PickedFromRes'] = 'Yes';
            } else {
                $returnArrDataNew['PickedFromRes'] = 'No';
            }

            $returnArrDataNew['iCompanyId'] = $iCompanyId;
            $returnArrDataNew['vRestuarantImage'] = ($CompanyData[0]['vImage'] != '') ? $CompanyData[0]['vImage'] : "";
            $returnArrDataNew['vOrderNo'] = $returnArrData['vOrderNo'];
            $returnArrDataNew['vCompany'] = ($CompanyData[0]['vCompany'] != '') ? $CompanyData[0]['vCompany'] : "";
            $returnArrDataNew['vRestuarantLocation'] = ($CompanyData[0]['vRestuarantLocation'] != '') ? $CompanyData[0]['vRestuarantLocation'] : "";
            $returnArrDataNew['vRestuarantLocationLat'] = ($CompanyData[0]['vRestuarantLocationLat'] != '') ? $CompanyData[0]['vRestuarantLocationLat'] : "";
            $returnArrDataNew['vRestuarantLocationLong'] = ($CompanyData[0]['vRestuarantLocationLong'] != '') ? $CompanyData[0]['vRestuarantLocationLong'] : "";
            if ($CompanyData[0]['vCode'] != '') {
                $returnArrDataNew['vPhoneRestaurant'] = '+' . $CompanyData[0]['vCode'] . $CompanyData[0]['vPhone'];
            } else {
                $returnArrDataNew['vPhoneRestaurant'] = $CompanyData[0]['vPhone'];
            }
        }

        $uQuery = "SELECT concat(ru.vName,' ',ru.vLastName) as UserName,ru.vPhone,ru.vPhoneCode,ua.vLatitude,ua.vLongitude FROM register_user as ru LEFT JOIN user_address as ua on ua.iUserId = ru.iUserId WHERE ru.iUserId = '" . $iUserId . "' AND ua.iUserAddressId = '" . $iUserAddressId . "'  AND ua.eUserType = 'Rider'";
        $UserData = $obj->MySQLSelect($uQuery);
        //Added By HJ On 09-01-2020 For Solved 141 Mantis Bug #2799 Start
        if (count($UserData) == 0 || empty($UserData)) {
            $uQuery = "SELECT concat(ru.vName,' ',ru.vLastName) as UserName,ru.vPhone,ru.vPhoneCode,ua.vLatitude,ua.vLongitude FROM register_user as ru LEFT JOIN user_fave_address as ua on ua.iUserId = ru.iUserId WHERE ru.iUserId = '" . $iUserId . "' AND ua.iUserFavAddressId = '" . $iUserAddressId . "'  AND ua.eUserType = 'Passenger'";
            //echo $uQuery;die;
            $UserData = $obj->MySQLSelect($uQuery);
            //echo "<pre>";print_R($UserData);die;
        }
        //Added By HJ On 09-01-2020 For Solved 141 Mantis Bug #2799 End
        ///echo "<pre>";print_R($iUserId);die;
        if (!empty($UserData)) {
            $returnArrDataNew['UserName'] = $UserData[0]['UserName'];
            $UserAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);
            //echo "<pre>";print_R($UserAddressArr);die;
            $returnArrDataNew['UserAdress'] = $UserAddressArr['UserAddress'];
            $returnArrDataNew['vLatitude'] = $UserData[0]['vLatitude'];
            $returnArrDataNew['vLongitude'] = $UserData[0]['vLongitude'];
            if ($UserData[0]['vPhone'] != '') {
                $returnArrDataNew['vPhoneUser'] = '+' . $UserData[0]['vPhoneCode'] . $UserData[0]['vPhone'];
            } else {
                $returnArrDataNew['vPhoneUser'] = $UserData[0]['vPhone'];
            }
        } else {
            $returnArrDataNew['UserName'] = $returnArrDataNew['UserAdress'] = '';
        }
    }
    //echo "<pre>";
    //print_R($returnArrDataNew);die;
    if (!empty($returnArrDataNew)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $returnArrDataNew;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_ORDERS_FOUND_TXT";
    }

    setDataResponse($returnArr);
}

// ######################## End Get Live Task Details ###################################################################
// ############################ Check Out Order Details ###################################################################
if ($type == "CheckOutOrderDetails") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '';
    $vCouponCode = isset($_REQUEST["vCouponCode"]) ? $_REQUEST["vCouponCode"] : '';
    $ePaymentOption = isset($_REQUEST["ePaymentOption"]) ? $_REQUEST["ePaymentOption"] : '';
    $vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $OrderDetails = isset($_REQUEST["OrderDetails"]) ? $_REQUEST["OrderDetails"] : '';
    $vInstruction = isset($_REQUEST["vInstruction"]) ? $_REQUEST["vInstruction"] : '';
    $CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';
    $selectedpreferences = isset($_REQUEST["selectedprefrences"]) ? $_REQUEST["selectedprefrences"] : '';
    $eTakeAway = isset($_REQUEST["eTakeAway"]) ? $_REQUEST["eTakeAway"] : 'No';
    // payment method-2
    $OrderDetails = json_decode(stripcslashes($OrderDetails), true);
    //Added By HJ On 19-03-2020 For Get User Order Details Start - (For Solved SGO Bug)
    if (isset($_REQUEST['fromOrder']) && trim($_REQUEST['fromOrder']) != "") {
        $fromOrder = trim($_REQUEST['fromOrder']);
        $orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
        $OrderDetails = $_SESSION[$orderDetailsSession];
    }
    //Added By HJ On 19-03-2020 For Get User Order Details End - (For Solved SGO Bug)
    //echo "<pre>";print_r($OrderDetails);die;
    $eWalletIgnore = isset($_REQUEST["eWalletIgnore"]) ? $_REQUEST["eWalletIgnore"] : 'No';
    $ePayWallet = isset($_REQUEST["ePayWallet"]) ? $_REQUEST["ePayWallet"] : 'No';
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';

    $adminSkip= isset($_REQUEST["adminSkip"]) ? $_REQUEST["adminSkip"] : 'No';
    // payment method-2
    if ($CheckUserWallet == "" || $CheckUserWallet == NULL) {
        $CheckUserWallet = "No";
    }

    if($adminSkip != "Yes"){
        checkmemberemailphoneverification($iUserId, "Passenger");
    }

    $iGcmRegId = get_value('register_user', 'iGcmRegId', 'iUserId', $iUserId, '', 'true');
    if ($vDeviceToken != "" && $vDeviceToken != $iGcmRegId) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "SESSION_OUT";
        setDataResponse($returnArr);
    }

    ## Update Demo User's Restaurants Lat Long As per User's Location ##
    if (SITE_TYPE == "Demo" && $iUserId != "" && $iUserAddressId != "") {
        $uemail = get_value('register_user', 'vEmail', 'iUserId', $iUserId, '', 'true');
        $uemail = explode("-", $uemail);
        $uemail = $uemail[1];
        if ($uemail != "") {
            $sql = "SELECT GROUP_CONCAT(iCompanyId)as companyId FROM company WHERE vEmail LIKE '%$uemail%'";
            $db_rec = $obj->MySQLSelect($sql);
            $usercompanyId = $db_rec[0]['companyId'];

            $sql = "SELECT vLatitude,vLongitude,vServiceAddress FROM user_address WHERE iUserAddressId = '" . $iUserAddressId . "'";
            $db_sql = $obj->MySQLSelect($sql);
            $passengerLat = $db_sql[0]['vLatitude'];
            $passengerLon = $db_sql[0]['vLongitude'];
            $vServiceAddress = $db_sql[0]['vServiceAddress'];

            /* $distance = 100;
              $earthRadius = 6371;
              $bearing = 0;
              $passengerLat = asin(sin($passengerLat1) * cos($distance / $earthRadius) + cos($passengerLat1) * sin($distance / $earthRadius) * cos($bearing));
              $passengerLon = $passengerLon1 + atan2(sin($bearing) * sin($distance / $earthRadius) * cos($passengerLat1), cos($distance / $earthRadius) - sin($passengerLat1) * sin($passengerLat));
             */

            $updateQuery = "UPDATE company SET vRestuarantLocationLat='" . $passengerLat . "', vRestuarantLocationLong = '" . $passengerLon . "', vRestuarantLocation = '" . $vServiceAddress . "' WHERE iCompanyId = '" . $iCompanyId . "'";
            $obj->sql_query($updateQuery);
        }
    }
    ## Update Demo User's Restaurants Lat Long As per User's Location ##

    $checkrestaurantstatusarr = calculate_restaurant_time_span($iCompanyId, $iUserId);
    $restaurantstatus = $checkrestaurantstatusarr['restaurantstatus'];
    if ($restaurantstatus == "closed") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_RESTAURANTS_CLOSE_NOTE";
        setDataResponse($returnArr);
    }

    $isAllItemAvailableCheckArr = checkmenuitemavailability($OrderDetails);
    $isAllItemAvailable = $isAllItemAvailableCheckArr['isAllItemAvailable'];
    $isAllItemOptionsAvailable = $isAllItemAvailableCheckArr['isAllItemOptionsAvailable'];
    $isAllItemToppingssAvailable = $isAllItemAvailableCheckArr['isAllItemToppingssAvailable'];
    if ($isAllItemAvailable == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MENU_ITEM_NOT_AVAILABLE_TXT";
        setDataResponse($returnArr);
    }

    if ($isAllItemOptionsAvailable == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MENU_ITEM_OPTIONS_NOT_AVAILABLE_TXT";
        setDataResponse($returnArr);
    }

    if ($isAllItemToppingssAvailable == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_MENU_ITEM_ADDONS_NOT_AVAILABLE_TXT";
        setDataResponse($returnArr);
    }

    if ($ePaymentOption == "Card") {
        UpdateCardPaymentPendingOrder();
    }

    /** To Get User Language Code And Currency * */
    $user_detail = $obj->MySQLSelect("SELECT ru.vCurrencyPassenger, ru.vName, ru.vLastName, ru.vEmail, ru.vLang, cu.vSymbol, cu.Ratio, co.eUnit FROM register_user as ru, currency as cu, country as co WHERE ru.iUserId='" . $iUserId . "' AND cu.vName = ru.vCurrencyPassenger AND co.vCountryCode = ru.vCountry");


    $userLanguageCode = $user_detail[0]['vLang'];
    if (empty($userLanguageCode)) {
        $userLanguageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $userLanguageLabelsArr = getLanguageLabelsArr($userLanguageCode, "1", $iServiceId);

    $user_currency_symbol = $user_detail[0]['vSymbol'];
    $user_currency_ratio = $user_detail[0]['Ratio'];
    $vCountry = $user_detail[0]['vCountry'];
    // $sql = "SELECT vName,vLastName,vEmail from register_user WHERE iUserId = '" . $iUserId . "'";
    // $user_detail = $obj->MySQLSelect($sql);
    $vName = $user_detail[0]['vName'];
    $vLastName = $user_detail[0]['vLastName'];
    $vUserEmail = $user_detail[0]['vEmail'];
    $sql = "select vCompany,fMaxOfferAmt,fTargetAmt,fOfferType,fOfferAppyType,fOfferAmt,iServiceId,eAutoaccept,vLang,vCountry from `company` where iCompanyId = '" . $iCompanyId . "'";
    $db_companydata = $obj->MySQLSelect($sql);
    $vCompany = $db_companydata[0]['vCompany'];
    $fMaxOfferAmt = $db_companydata[0]['fMaxOfferAmt'];
    $fTargetAmt = $db_companydata[0]['fTargetAmt'];
    $fOfferType = $db_companydata[0]['fOfferType'];
    $fOfferAppyType = $db_companydata[0]['fOfferAppyType'];
    $fOfferAmt = $db_companydata[0]['fOfferAmt'];
    $iServiceId = $db_companydata[0]['iServiceId'];
    $eAutoaccept = $db_companydata[0]['eAutoaccept'];
    $vLangCompany = $db_companydata[0]['vLang'];
    //Added By HJ On 03-09-2019 For Get Store Service Id When Place Order Start
    $storeServiceId = $iServiceId;
    if (isset($db_companydata[0]['iServiceId']) && $db_companydata[0]['iServiceId'] > 0) {
        $storeServiceId = $db_companydata[0]['iServiceId'];
    }
    //Added By HJ On 03-09-2019 For Get Store Service Id When Place Order End
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
    $Data_insert['eTakeAway'] = $eTakeAway;

    /*     * ***** Changes for System payment flow method-2/method-3 ****** */
    if ($ePaymentOption == "Cash") {
        $Data_insert['iStatusCode'] = 1;
    } else if (($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') && $ePaymentOption != 'Cash') {
        $Data_insert['iStatusCode'] = 1;
    } else {
        $Data_insert['iStatusCode'] = 12;
    }
    /*     * ***** Changes for System payment flow method-2/method-3 ****** */

    //$Data_insert['iStatusCode'] = ($ePaymentOption == "Cash") ? 1 : 12;
    $Data_insert['dDeliveryDate'] = @date("Y-m-d H:i:s");
    $Data_insert['vInstruction'] = $vInstruction;
    $Data_insert['vTimeZone'] = $vTimeZone;
    $Data_insert['fMaxOfferAmt'] = $fMaxOfferAmt;
    $Data_insert['fTargetAmt'] = $fTargetAmt;
    $Data_insert['fOfferType'] = $fOfferType;
    $Data_insert['fOfferAppyType'] = $fOfferAppyType;
    $Data_insert['fOfferAmt'] = $fOfferAmt;
    $Data_insert['iServiceId'] = $storeServiceId;
    $Data_insert['eCheckUserWallet'] = $CheckUserWallet;

    $user_available_balance_wallet = $generalobj->get_user_available_balance($iUserId, "Rider", true, 'order');

    $walletDataArr = array();

    if (is_array($user_available_balance_wallet)) {
        $walletDataArr = $user_available_balance_wallet;
        $user_available_balance_wallet = $walletDataArr['CurrentBalance'];
        //$Data_insert['tUserWalletBalance'] = $walletDataArr['AutorizedWalletBalance']; // Commented By HJ On 10-01-2020 For Solved Payment Flow 2 Related Issyes Start
    }

    // payment method 2
    $Data_insert['ePayWallet'] = $ePayWallet;
    // payment method 2
    $currencyList = get_value('currency', '*', 'eStatus', 'Active');
    for ($i = 0; $i < count($currencyList); $i++) {
        $currencyCode = $currencyList[$i]['vName'];
        $Data_insert['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
    }
    // payment method 2
    if ($iOrderId == "" || $iOrderId == NULL) {
        $Data_insert['selectedPreferences'] = $selectedpreferences;
        $iOrderId = $obj->MySQLQueryPerform("orders", $Data_insert, 'insert');
        $OrderLogId = createOrderLog($iOrderId, $Data_insert['iStatusCode']);
        $OrderDetailsIdsArr = array();
        if (!empty($OrderDetails)) {
            $fTotalMenuItemBasePrice = 0;
            //Added By HJ On 1-05-2019 For Optimize Code Start
            $optionPriceArr = getAllOptionAddonPriceArr();
            $ordItemPriceArr = getAllMenuItemPriceArr();
            //echo "<pre>";print_r($ordItemPriceArr);die;
            //Added By HJ On 1-05-2019 For Optimize Code End
            for ($j = 0; $j < count($OrderDetails); $j++) {
                $iQty = $OrderDetails[$j]['iQty'];
                //$fMenuItemPrice = FoodMenuItemBasicPrice($OrderDetails[$j]['iMenuItemId'], $iQty); //Commnent By HJ On 25-05-2019 For Optimize Below Code
                //Added By HJ On 09-05-2019 For Optimize Code Start
                $fMenuItemPrice = 0;
                if (isset($ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']]) && $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] > 0) {
                    $fMenuItemPrice = $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] * $iQty;
                }
                //echo $fMenuItemPrice;die;
                //Added By HJ On 09-05-2019 For Optimize Code End
                //$vOptionPrice = GetFoodMenuItemOptionPrice($OrderDetails[$j]['vOptionId']); //Commnent By HJ On 25-05-2019 For Optimize Below Code
                //Added By HJ On 1-05-2019 For Optimize Code Start
                $vOptionPrice1 = 0;
                $explodeOption = explode(",", $OrderDetails[$j]['vOptionId']);
                for ($fd = 0; $fd < count($explodeOption); $fd++) {
                    if (isset($optionPriceArr[$explodeOption[$fd]]) && $optionPriceArr[$explodeOption[$fd]] > 0) {
                        $vOptionPrice1 += $optionPriceArr[$explodeOption[$fd]];
                    }
                }
                //Added By HJ On 1-05-2019 For Optimize Code End
                $vOptionPrice = $vOptionPrice1 * $iQty;
                //$vAddonPrice = GetFoodMenuItemAddOnPrice($OrderDetails[$j]['vAddonId']); //Commnent By HJ On 25-05-2019 For Optimize Below Code
                //Added By HJ On 1-05-2019 For Optimize Code Start
                $vAddonPrice1 = 0;
                $explodeAddon = explode(",", $OrderDetails[$j]['vAddonId']);
                for ($df = 0; $df < count($explodeAddon); $df++) {
                    if (isset($optionPriceArr[$explodeAddon[$df]]) && $optionPriceArr[$explodeAddon[$df]] > 0) {
                        $vAddonPrice1 += $optionPriceArr[$explodeAddon[$df]];
                    }
                }
                //Added By HJ On 1-05-2019 For Optimize Code End
                $vAddonPrice = $vAddonPrice1 * $iQty;
                $fTotalMenuItemBasePrice = $fTotalMenuItemBasePrice + $fMenuItemPrice + $vOptionPrice + $vAddonPrice;
            }
            $fTotalMenuItemBasePrice = $generalobj->setTwoDecimalPoint($fTotalMenuItemBasePrice);
            for ($i = 0; $i < count($OrderDetails); $i++) {
                $Data = array();
                $Data['iOrderId'] = $iOrderId;
                $Data['iMenuItemId'] = isset($OrderDetails[$i]['iMenuItemId']) ? $OrderDetails[$i]['iMenuItemId'] : '';
                $Data['iFoodMenuId'] = isset($OrderDetails[$i]['iFoodMenuId']) ? $OrderDetails[$i]['iFoodMenuId'] : '';
                // $Data['fPrice'] = GetFoodMenuItemBasicPrice($Data['iMenuItemId']);
                $Data['iQty'] = isset($OrderDetails[$i]['iQty']) ? $OrderDetails[$i]['iQty'] : '';
                $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($Data['iMenuItemId'], $iCompanyId, 1, $iUserId, "Calculate", $OrderDetails[$i]['vOptionId'], $OrderDetails[$i]['vAddonId'], $storeServiceId);
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
                //$Data['vOptionPrice'] = GetFoodMenuItemOptionPrice($Data['vOptionId']); //Commnent By HJ On 25-05-2019 For Optimize Below Code
                //Added By HJ On 1-05-2019 For Optimize Code Start
                $vOptionPrice2 = 0;
                $explodeOption = explode(",", $Data['vOptionId']);
                for ($fd = 0; $fd < count($explodeOption); $fd++) {
                    if (isset($optionPriceArr[$explodeOption[$fd]]) && $optionPriceArr[$explodeOption[$fd]] > 0) {
                        $vOptionPrice2 += $optionPriceArr[$explodeOption[$fd]];
                    }
                }
                $Data['vOptionPrice'] = $vOptionPrice2;
                //Added By HJ On 1-05-2019 For Optimize Code End

                $Data['vAddonId'] = isset($OrderDetails[$i]['vAddonId']) ? $OrderDetails[$i]['vAddonId'] : '';
                //$Data['vAddonPrice'] = GetFoodMenuItemAddOnPrice($Data['vAddonId']); //Commnent By HJ On 25-05-2019 For Optimize Below Code
                //Added By HJ On 1-05-2019 For Optimize Code Start
                $vAddonPrice2 = 0;
                $explodeAddon = explode(",", $Data['vAddonId']);
                for ($df = 0; $df < count($explodeAddon); $df++) {
                    if (isset($optionPriceArr[$explodeAddon[$df]]) && $optionPriceArr[$explodeAddon[$df]] > 0) {
                        $vAddonPrice2 += $optionPriceArr[$explodeAddon[$df]];
                    }
                }
                $Data['vAddonPrice'] = $vAddonPrice2;
                //Added By HJ On 1-05-2019 For Optimize Code End
                $Data['fPrice'] = $Data['fOriginalPrice'] - $Data['vOptionPrice'] - $Data['vAddonPrice'];

                // $fSubTotal = $Data['fOriginalPrice']+$Data['vOptionPrice']+$Data['vAddonPrice'];
                $fSubTotal = $Data['fOriginalPrice'];
                $Data['fSubTotal'] = $fSubTotal;
                $fTotalPrice = $fSubTotal * $Data['iQty'];
                $Data['fTotalPrice'] = $fTotalPrice;
                $Data['dDate'] = @date("Y-m-d H:i:s");
                $Data['eAvailable'] = "Yes";
                //$Data['tOptionIdOrigPrice'] = GetFoodMenuItemOptionIdPriceString($Data['vOptionId']); //Commnent By HJ On 25-05-2019 For Optimize Below Code
                //Added By HJ On 09-05-2019 For Optimize Code Start
                $OptionIdPriceString = "";
                if ($Data['vOptionId'] != "") {
                    $vOptionIdArr = explode(",", $Data['vOptionId']);
                    if (count($vOptionIdArr) > 0) {
                        for ($p = 0; $p < count($vOptionIdArr); $p++) {
                            $fPriceOption = 0;
                            if (isset($optionPriceArr[$vOptionIdArr[$p]])) {
                                $fPriceOption = $optionPriceArr[$vOptionIdArr[$p]];
                            }
                            $OptionIdPriceString .= $vOptionIdArr[$p] . "#" . $fPriceOption . ",";
                        }
                    }
                }
                //echo "<pre>";print_r($OptionIdPriceString);die;
                $Data['tOptionIdOrigPrice'] = trim($OptionIdPriceString, ",");
                //Added By HJ On 09-05-2019 For Optimize Code End
                //$Data['tAddOnIdOrigPrice'] = GetFoodMenuItemAddOnIdPriceString($Data['vAddonId']); //Commnent By HJ On 25-05-2019 For Optimize Below Code
                //Added By HJ On 09-05-2019 For Optimize Code Start
                $AddOnIdPriceString = "";
                if ($Data['vAddonId'] != "") {
                    $vAddonIdArr = explode(",", $Data['vAddonId']);
                    if (count($vAddonIdArr) > 0) {
                        for ($a = 0; $a < count($vAddonIdArr); $a++) {
                            $fPriceOption = 0;
                            if (isset($optionPriceArr[$vAddonIdArr[$a]])) {
                                $fPriceOption = $optionPriceArr[$vAddonIdArr[$a]];
                            }
                            $AddOnIdPriceString .= $vAddonIdArr[$a] . "#" . $fPriceOption . ",";
                        }
                    }
                }
                //echo "<pre>";print_r($AddOnIdPriceString);die;
                $Data['tAddOnIdOrigPrice'] = trim($AddOnIdPriceString, ",");
                //Added By HJ On 09-05-2019 For Optimize Code End
                // $Data['tOptionAddonAttribute'] = isset($OrderDetails[$i]['tOptionAddonAttribute']) ? $OrderDetails[$i]['tOptionAddonAttribute'] : '';
                // payment method 2
                //echo "<pre>";print_r($Data);die;
                $iOrderDetailId = $obj->MySQLQueryPerform("order_details", $Data, 'insert');
                array_push($OrderDetailsIdsArr, $iOrderDetailId);
            }
        }
    }
    // payment method 2
    $Order_data = calculateOrderFare($iOrderId);

    $where = " iOrderId = '" . $iOrderId . "'";
    $Data_update_order['vInstruction'] = $vInstruction;
    $Data_update_order['fSubTotal'] = $Order_data['fSubTotal'];
    $Data_update_order['fOffersDiscount'] = $Order_data['fOffersDiscount'];
    $Data_update_order['fPackingCharge'] = $Order_data['fPackingCharge'];
    $Data_update_order['fDeliveryCharge'] = ($eTakeAway == 'Yes') ? 0 : $Order_data['fDeliveryCharge'];
    $Data_update_order['fTax'] = $Order_data['fTax'];
    $Data_update_order['fDiscount'] = $Order_data['fDiscount'];
    $Data_update_order['vDiscount'] = $Order_data['vDiscount'];
    $Data_update_order['fCommision'] = $Order_data['fCommision'];
    $Data_update_order['fNetTotal'] = $Order_data['fNetTotal'];
    $Data_update_order['fTotalGenerateFare'] = $Order_data['fTotalGenerateFare'];
    $Data_update_order['fOutStandingAmount'] = $Order_data['fOutStandingAmount'];
    $Data_update_order['fWalletDebit'] = $Order_data['fWalletDebit'];


    //added by SP on 15-11-2019 for rounding off start
    $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, cu.Ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iUserId . "'";
    $currData = $obj->MySQLSelect($sqlp);
    $vCurrency = $currData[0]['vName'];

    if ($currData[0]['eRoundingOffEnable'] == "Yes") {

        $roundingOffTotal_fare_amountArr = getRoundingOffAmount($Order_data['fNetTotal'] * $user_currency_ratio, $vCurrency);
        $roundingOffTotal_fare_amount = $roundingOffTotal_fare_amountArr['finalFareValue'];

        if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
            $eRoundingType = "Addition";
        } else {
            $eRoundingType = "Substraction";
        }

        $fRoundingAmount = $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue']);

        $Data_update_order['fRoundingAmount'] = $fRoundingAmount;
        $Data_update_order['eRoundingType'] = $eRoundingType;
    }


    ###########################

    if ($Order_data['fNetTotal'] == 0) {
        $Data_update_order['ePaid'] = "Yes";
    }
    //echo "<pre>";print_r($Data_update_order);die;
    // payment method 2
    /*     * ******** Checking Wallet balance when system payment method-2/method-3 ******* */
    if ($ePaymentOption != 'Cash' && ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') && $eWalletIgnore == 'No') {
        if ($user_available_balance_wallet < $Order_data['fNetTotal']) {
            $Data_update_order_new['iStatusCode'] = 12;
            $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order_new, 'update', $where);
            $returnArr['Action'] = "0";
            $returnArr['iOrderId'] = $iOrderId;
            $returnArr['message'] = "LOW_WALLET_AMOUNT";
            $fareAmount = $Order_data['fNetTotal'] * $user_currency_ratio;
            $user_available_balance_wallet = $user_available_balance_wallet * $user_currency_ratio;
            if (!empty($walletDataArr) && count($walletDataArr) > 0) {
                $auth_wallet_amount = strval((isset($walletDataArr['TotalAuthorizedAmount']) ? $walletDataArr['TotalAuthorizedAmount'] : 0) * $user_currency_ratio);
                $returnArr['AUTH_AMOUNT'] = $auth_wallet_amount > 0 ? ($user_currency_symbol . ' ' . $auth_wallet_amount) : "";
                $returnArr['AUTH_AMOUNT_VALUE'] = $auth_wallet_amount > 0 ? $auth_wallet_amount : "";
                $returnArr['ORIGINAL_WALLET_BALANCE'] = $user_currency_symbol . ' ' . strval((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $user_currency_ratio);
                $returnArr['ORIGINAL_WALLET_BALANCE_VALUE'] = strval((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $user_currency_ratio);
            }
            $returnArr['CURRENT_JOB_EST_CHARGE'] = $user_currency_symbol . ' ' . strval($fareAmount);
            $returnArr['CURRENT_JOB_EST_CHARGE_VALUE'] = strval($fareAmount);
            $returnArr['WALLET_AMOUNT_NEEDED'] = $user_currency_symbol . ' ' . strval($fareAmount - $user_available_balance_wallet);
            $returnArr['WALLET_AMOUNT_NEEDED_VALUE'] = strval($fareAmount - $user_available_balance_wallet);
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
            } else {
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
            } else {
                $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "No";
            }

            setDataResponse($returnArr);
        }
    }
    $tEstimatedCharge = $fareAmount / $user_currency_ratio;
    if (!empty($tEstimatedCharge) && $Data_insert['tUserWalletBalance'] > $tEstimatedCharge && ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
        $Data_insert['tUserWalletBalance'] = $tEstimatedCharge;
    }
    /*     * ******** Checking Wallet balance when system payment method-2/method-3 ******* */
    // payment method 2
    $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order, 'update', $where);
    if ($Order_Update_Id > 0) {
        if ($ePaymentOption == "Cash") {
            $CompanyMessage = "OrderRequested";
            $vLangCode = get_value('company', 'vLang', 'iCompanyId', $iCompanyId, '', 'true');
            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }

            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $storeServiceId);

            $orderreceivelbl = $languageLabelsArr['LBL_NEW_ORDER_PLACED_TXT'] . $Data_insert['vOrderNo'];
            $alertMsg = $orderreceivelbl;
            if ($PUBNUB_DISABLED == "Yes") {
                $ENABLE_PUBNUB = "No";
            }

            $alertSendAllowed = true;
            /* For PubNub Setting */
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
            /* For PubNub Setting Finished */
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

            //sleep(3);
            // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
            /* $pubnub = new Pubnub\Pubnub(array(
              "publish_key" => $PUBNUB_PUBLISH_KEY,
              "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
              "uuid" => $uuid
              )); */
            $channelName = "COMPANY_" . $iCompanyId;
            if ($eDeviceType == "Ios") {
                sleep(3);
            }
            // $info = $pubnub->publish($channelName, $message_pub);
            /* if ($PUBNUB_DISABLED == "Yes") {
              publishEventMessage($channelName, $message_pub);
              } else {
              $info = $pubnub->publish($channelName, $message_pub);
              } */
            publishEventMessage($channelName, $message_pub);
            // }
            /* else{
              $alertSendAllowed = true;
              } */
        }

        $pres_update = setorderid_for_prescription($iUserId, $iOrderId);
        $returnArr['Action'] = "1";
        $returnArr['iOrderId'] = $iOrderId;
        //Added By HJ On 13-11-2019 B'coz Code Moved In CaptureCardPaymentOrder Type As Per Discuss With KS Sir Start For Only Cash Payment Mode
        //$eAutoaccept = get_value('company', 'eAutoaccept', 'iCompanyId', $iCompanyId, '', 'true');
        if ($eAutoaccept == "Yes" && $ENABLE_AUTO_ACCEPT_STORE_ORDER == "Yes" && $ePaymentOption == "Cash") { // If Store have enable and Admin Side Enable Setting
            if ($iStatusCode != "2") {
                $returnArr1 = ConfirmOrderByRestaurantcall($iCompanyId, $iOrderId); // For Auto Accept order From Store
            }
            if ($vCountry == "") {
                $vCountry = $db_companydata[0]['vCountry'];
            }
            sendAutoRequestToDriver($iOrderId, $vCountry); // For Send Request to Drivers
        }
        //Added By HJ On 13-11-2019 B'coz Code Moved In CaptureCardPaymentOrder Type As Per Discuss With KS Sir End For Only Cash Payment Mode
        $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
        setDataResponse($returnArr);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}

// ############################ Check Out Order Details ###########################################################################
// ############################# Capture Card Paymant of Order ####################################################################
if ($type == "CaptureCardPaymentOrder") {
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $ePaymentOption = isset($_REQUEST["ePaymentOption"]) ? $_REQUEST["ePaymentOption"] : '';
    $vStripeToken = isset($_REQUEST["vStripeToken"]) ? $_REQUEST["vStripeToken"] : '';
    $CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $payStatus = isset($_REQUEST["payStatus"]) ? $_REQUEST["payStatus"] : '';
    $vPayMethod = isset($_REQUEST["vPayMethod"]) ? $_REQUEST["vPayMethod"] : ''; // Instant,Manual
    if ($payStatus != "succeeded" && $payStatus != "") {
        $payStatus = "Failed";
    }
    //echo "<pre>";print_r($_REQUEST);die;
    $ChargeidArrId = 0;
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $vFlutterWaveToken = isset($_REQUEST["vFlutterWaveToken"]) ? trim($_REQUEST["vFlutterWaveToken"]) : '';
    $txref = isset($_REQUEST["txref"]) ? trim($_REQUEST["txref"]) : '';
    /* Added By PM On 09-12-2019 For Flutterwave Code end */
    $vStripeCusId = $vCountry = "";
    $IsChargeCustomer = "No";
    //echo "<pre>";print_r($_REQUEST);die;

    /* Updated By PM On 09-12-2019 For Flutterwave Code Start */
    if (($vStripeToken == "" || $vStripeToken == NULL) && $APP_PAYMENT_METHOD == "Stripe") {
        if (!empty($iUserId)) {
            $Squery = "SELECT vStripeToken,vStripeCusId,vCreditCard,vBrainTreeToken,vCountry FROM register_user WHERE iUserId = '" . $iUserId . "'";
            $Userdata = $obj->MySQLSelect($Squery);
            $vCountry = $Userdata[0]['vCountry'];
            if ($vStripeToken == "" || $vStripeToken == NULL) {
                $vStripeToken = $Userdata[0]['vStripeToken'];
                $vStripeCusId = $Userdata[0]['vStripeCusId'];
                $vCreditCard = $Userdata[0]['vCreditCard'];
                $IsChargeCustomer = ($vCreditCard != "") ? "Yes" : "No";
            }
        }
    }
    if (($vFlutterWaveToken == "" || $vFlutterWaveToken == NULL) && $APP_PAYMENT_METHOD == "Flutterwave") {
        if (!empty($iUserId)) {
            $Squery = "SELECT vFlutterWaveToken,vCreditCard FROM register_user WHERE iUserId = '" . $iUserId . "'";
            $Userdata = $obj->MySQLSelect($Squery);
            $vFlutterWaveToken = $Userdata[0]['vFlutterWaveToken'];
            $vCreditCard = $Userdata[0]['vCreditCard'];
            $IsChargeCustomer = ($vCreditCard != "") ? "Yes" : "No";
        }
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code end */
    /* Updated By MK On 16-04-2020 For Braintree Tokenization Code Start */
    if ($APP_PAYMENT_METHOD == "Braintree") {
        if (!empty($iUserId)) {
            $Squery = "SELECT vBrainTreeToken,vCreditCard,vBrainTreeCustId FROM register_user WHERE iUserId = '" . $iUserId . "'";
            $Userdata = $obj->MySQLSelect($Squery);
            $vBrainTreeToken = $Userdata[0]['vBrainTreeToken'];
            $vCreditCard = $Userdata[0]['vCreditCard'];
            $IsChargeCustomer = ($vCreditCard != "") ? "Yes" : "No";
        }
    }
    /* Updated By MK On 16-04-2020 For Braintree Tokenization Code End */

    if ($vPayMethod == "Instant") {
        $IsChargeCustomer = "No";
    }
    if ($CheckUserWallet == "" || $CheckUserWallet == NULL) {
        $CheckUserWallet = "No";
    }
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $iUserId = $data_order[0]['iUserId'];
    $fNetTotal = $data_order[0]['fNetTotal'];
    $tUserWalletBalance = $data_order[0]['tUserWalletBalance'];
    /* Check debit wallet For Count Total Fare  Start */
    $user_wallet_debit_amount = 0;
    $full_adjustment = 0;
    if ($CheckUserWallet == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        if ($user_available_balance > 0) {
            $totalCurrentActiveTripsArr = getCurrentActiveTripsTotal($iUserId);
            $totalCurrentActiveTripsIdsArr = $totalCurrentActiveTripsArr['ActiveTripIds'];
            $totalCurrentActiveOrderIdsArr = $totalCurrentActiveTripsArr['ActiveOrderIds'];
            $totalCurrentActiveTripsCount = $totalCurrentActiveTripsArr['TotalCount'];
            /*             * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */
            // Charge an amount that is autorized when trip was initially requested in case when multiple jobs are going on.
            if (($totalCurrentActiveTripsCount > 1 || in_array($iOrderId, $totalCurrentActiveOrderIdsArr) == false) && ($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == 'Method-3')) {
                $user_available_balance = $tUserWalletBalance;
            }
            /*             * ******** Replace current wallet balance of user when System payment flow is Method-2/Method-3 ***** */
        }
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
        } else {
            $user_wallet_debit_amount = $fNetTotal;
            $fNetTotal = 0;
            $full_adjustment = 1;
        }
    }

    /* Check debit wallet For Count Total Fare  Start */
    $vOrderNo = $data_order[0]['vOrderNo'];
    $iCompanyId = $data_order[0]['iCompanyId'];
    if ($ePaymentOption == "Card") {
        $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
        $currencyCode = $DefaultCurrencyData[0]['vName'];
        $currencyratio = $DefaultCurrencyData[0]['Ratio'];
        $price_new = round($fNetTotal * $currencyratio, 2);
        $price_new = $price_new * 100;
        $tDescription = "Amount charge for order no" . $vOrderNo;
        $ChargeidArrId = 0;
        //try {
        if ($fNetTotal > 0 && $payStatus == "") {
            if ($IsChargeCustomer == "Yes") {
                /* $charge_create = Stripe_Charge::create(array("amount" => $price_new,"currency" => $currencyCode,"customer" => $vStripeCusId,"description" => $tDescription));  */
                $Charge_Array = array(
                    "iFare" => $fNetTotal,
                    "price_new" => $price_new,
                    "currency" => $currencyCode,
                    "vStripeCusId" => $vStripeCusId,
                    "description" => $tDescription,
                    "iTripId" => 0,
                    "eCancelChargeFailed" => "No",
                    "vBrainTreeToken" => $Userdata[0]['vBrainTreeToken'],
                    "vRideNo" => $vOrderNo,
                    "iMemberId" => $iUserId,
                    "UserType" => "Passenger",
                    "iOrderId" => $iOrderId,
                    "vOrderNo" => $vOrderNo
                );
                $result = ChargeCustomer($Charge_Array, "CollectPayment"); // function for charge customer
                //echo "<pre>";print_r($result);die;
                $ChargeidArrId = $result['id'];
                $status = $result['status'];
                if ($status == "success") {
                    $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
                    $data_payments['eEvent'] = "OrderPayment";
                    $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
                }
            } else {

                /* Added By PM On 09-12-2019 For Flutterwave Code Start */
                if ($APP_PAYMENT_METHOD == "Flutterwave") {
                    $updateData = array();
                    $updateQuery = $id = $chargeAmtFlag = $chargeAmt = 0;
                    $txid = "";
                    if ($txref != "") {
                        $verifiedData = flutterwave_verify($txref);

                        if (isset($verifiedData['token']) && $verifiedData['token'] != "") {
                            $updateQuery = $chargeAmtFlag = 1;
                            $updateData['vFlutterWaveToken'] = $verifiedData['token'];
                            $updateData['vCreditCard'] = $verifiedData['card'];
                            $chargeAmt = $verifiedData['chargedAmt'];
                            $paymentstatus = $verifiedData['status'];
                            $txid = $verifiedData['txid'];
                        }
                    } else {
                        $fNetTotal = round($fNetTotal, 2);

                        $supportedAmountData = $generalobj->getSupportedCurrencyAmt($fNetTotal, $currencyCode);

                        $returnArr['Action'] = "1";
                        $returnArr['vOrderTotal'] = $fNetTotal;
                        $returnArr['vAmountData'] = $supportedAmountData;
                        setDataResponse($returnArr);
                    }

                    if ($updateQuery > 0) {

                        if ($chargeAmtFlag > 0 && $chargeAmt > 0 && $paymentstatus == "success") {
                            $result['id'] = $txid;
                            $result['status'] = "succeeded";
                            $result['paid'] == "1";
                            $payStatus = "succeeded";

                            $returnArr['Action'] = "1";
                            $returnArr['status'] = "succeeded";
                        }
                    } else {
                        $returnArr['Action'] = "0";
                        $returnArr['status'] = "fail";
                        $returnArr['message'] = "Payment verify Failed";
                        setDataResponse($returnArr);
                    }
                } else {
                    $REFERRAL_AMOUNT_ARR = getPriceUserCurrency($iUserId, "Passenger", $fNetTotal);
                    $REFERRAL_AMOUNT_USER = $REFERRAL_AMOUNT_ARR['fPricewithsymbol'];
                    $eSystem = isset($_REQUEST["eSystem"]) ? $_REQUEST["eSystem"] : '';
                    $themeColor = isset($_REQUEST["AppThemeColor"]) ? $_REQUEST["AppThemeColor"] : '000000';
                    $textColor = isset($_REQUEST["AppThemeTxtColor"]) ? $_REQUEST["AppThemeTxtColor"] : 'FFFFFF';
                    $GeneralAppVersion = $appVersion;
                    $returnUrl = isset($_REQUEST['returnUrl']) ? trim($_REQUEST['returnUrl']) : 'webservice_shark.php';
                    $extraPara = "&ePaymentOption=" . $ePaymentOption . "&CheckUserWallet=" . $CheckUserWallet . "&eSystem=" . $eSystem . "&vStripeToken=" . $vStripeToken . "&type=" . $type . "&Platform=" . $Platform . "&tSessionId=" . $tSessionId . "&GeneralMemberId=" . $GeneralMemberId . "&GeneralUserType=" . $GeneralUserType . "&GeneralDeviceType=" . $GeneralDeviceType . "&GeneralAppVersion=" . $GeneralAppVersion . "&vTimeZone=" . $vTimeZone . "&vUserDeviceCountry=" . $vUserDeviceCountry . "&iServiceId=" . $iServiceId . "&vCurrentTime=" . $vCurrentTime . "&returnUrl=" . $returnUrl . "&vPayMethod=" . $vPayMethod . "&AppThemeColor=" . $themeColor . "&AppThemeTxtColor=" . $textColor;
                    $getWayUrl = $tconfig['tsite_url'] . "assets/libraries/webview/payment_configuration.php?iUserId=" . $iUserId . "&iOrderId=" . $iOrderId . "&amount=" . $price_new . "&ccode=" . $currencyCode . "&userAmount=" . $REFERRAL_AMOUNT_USER . "&vOrderNo=" . $vOrderNo . $extraPara;

                    //header('Location: '.$getWayUrl); // Comment Here For app
                    $returnArr = array();
                    $returnArr['Action'] = "1";
                    $returnArr['message'] = $getWayUrl;
                    setDataResponse($returnArr);
                }
                /* Added By PM On 09-12-2019 For Flutterwave Code End */
                /* try
                  {
                  $charge_create = Stripe_Charge::create(array(
                  "amount" => $price_new,
                  "currency" => $currencyCode,
                  "source" => $vStripeToken,
                  "description" => $tDescription
                  ));
                  }
                  catch(Exception $e)
                  {
                  $where = " iOrderId = '$iOrderId'";
                  $data['iStatusCode'] = 11;
                  $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
                  $OrderLogId = createOrderLog($iOrderId, "11");
                  $error3 = $e->getMessage();
                  $returnArr["Action"] = "0";
                  $returnArr['message'] = $error3;
                  setDataResponse($returnArr);
                  } */
            }
            /* $details = json_decode($charge_create);
              $result = get_object_vars($details); */
        }
        if (isset($result['status']) && $result['status'] == "succeeded" && $result['paid'] == "1" || $status == "success" || $payStatus == "succeeded" || $fNetTotal == 0) {
            //if ($fNetTotal == 0 || ($result['status'] == "succeeded" && $result['paid'] == "1") || $status == "success" || $payStatus == "succeeded") {
            //echo $payStatus;die;
            $where = " iOrderId = '$iOrderId'";
            $iTransactionId = 0;
            if (isset($result) && $result != "") {
                $iTransactionId = $result['id'];
                if ($fNetTotal == 0) {
                    $iTransactionId = 0;
                }
            }
            $data['iTransactionId'] = $iTransactionId;
            $data['ePaid'] = "Yes";
            $data['iStatusCode'] = 1;
            $data['fNetTotal'] = $fNetTotal;
            $data['fWalletDebit'] = $user_wallet_debit_amount;
            $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
            $OrderLogId = createOrderLog($iOrderId, "1");
            $returnArr["Action"] = "1";

            //Added By HJ On 13-11-2019 As Per Discuss With KS Sir For Auto accept Store and Send Request To Driver Auto Process Start
            $db_companydata = $obj->MySQLSelect("select eAutoaccept,vCountry from `company` where iCompanyId = '" . $iCompanyId . "'");
            if (isset($db_companydata[0]['eAutoaccept']) && $db_companydata[0]['eAutoaccept'] == "Yes" && $ENABLE_AUTO_ACCEPT_STORE_ORDER == "Yes") { // If Store have enable and Admin Side Enable Setting
                //echo $iOrderId;die;
                $returnArr1 = ConfirmOrderByRestaurantcall($iCompanyId, $iOrderId); // For Auto Accept order From Store
                if ($vCountry == "") {
                    $vCountry = $db_companydata[0]['vCountry'];
                }
                sendAutoRequestToDriver($iOrderId, $vCountry); // For Send Request to Drivers
            }
            //Added By HJ On 13-11-2019 As Per Discuss With KS Sir For Auto accept Store and Send Request To Driver Auto Process End
            ## Insert Into Payment Table ##
            if ($ChargeidArrId == 0) {

                /* Added By PM On 09-12-2019 For Flutterwave Code Start */
                if ($APP_PAYMENT_METHOD == "Stripe") {
                    $payment_arr['STRIPE_SECRET_KEY'] = $STRIPE_SECRET_KEY;
                    $payment_arr['STRIPE_PUBLISH_KEY'] = $STRIPE_PUBLISH_KEY;
                }
                if ($APP_PAYMENT_METHOD == "Flutterwave") {
                    $payment_arr['FLUTTERWAVE_SECRET_KEY'] = $FLUTTERWAVE_SECRET_KEY;
                    $payment_arr['FLUTTERWAVE_PUBLIC_KEY'] = $FLUTTERWAVE_PUBLIC_KEY;
                }

                if ($APP_PAYMENT_METHOD == "Braintree") {
                    $payment_arr['BRAINTREE_TOKEN_KEY'] = $BRAINTREE_TOKEN_KEY;
                    $payment_arr['BRAINTREE_ENVIRONMENT'] = $BRAINTREE_ENVIRONMENT;
                    $payment_arr['BRAINTREE_MERCHANT_ID'] = $BRAINTREE_MERCHANT_ID;
                    $payment_arr['BRAINTREE_PUBLIC_KEY'] = $BRAINTREE_PUBLIC_KEY;
                    $payment_arr['BRAINTREE_PRIVATE_KEY'] = $BRAINTREE_PRIVATE_KEY;
                }
                $tPaymentDetails = json_encode($payment_arr, JSON_UNESCAPED_UNICODE);

                /* Added By PM On 09-12-2019 For Flutterwave Code End */

                $pay_data['tPaymentUserID'] = $iTransactionId;
                $pay_data['vPaymentUserStatus'] = "approved";
                $pay_data['iAmountUser'] = $fNetTotal;
                $pay_data['tPaymentDetails'] = $tPaymentDetails;
                $pay_data['iOrderId'] = $iOrderId;
                $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                $pay_data['iUserId'] = $iUserId;
                $pay_data['eUserType'] = "Passenger";
                $pay_data['eEvent'] = "OrderPayment";

                $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
            }
            ## Insert Into Payment Table ##
            // Update User Wallet
            if ($user_wallet_debit_amount > 0 && $CheckUserWallet == "Yes") {
                $vRideNo = $data_order[0]['vOrderNo'];
                $data_wallet['iUserId'] = $iUserId;
                $data_wallet['eUserType'] = "Rider";
                $data_wallet['iBalance'] = $user_wallet_debit_amount;
                $data_wallet['eType'] = "Debit";
                $data_wallet['dDate'] = date("Y-m-d H:i:s");
                $data_wallet['iTripId'] = 0;
                $data_wallet['iOrderId'] = $iOrderId;
                $data_wallet['eFor'] = "Booking";
                $data_wallet['ePaymentStatus'] = "Unsettelled";
                $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING_DL#" . " " . $vRideNo;
                $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate'], $data_wallet['iOrderId']);
                // $obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
            }

            // Update User Wallet
            $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes',vOrderAdjusmentId = '" . $vOrderNo . "',ePaidByWallet='Yes' WHERE iUserId = '" . $iUserId . "' AND ePaidByPassenger = 'No'";
            $obj->sql_query($updateQury);
        } else {
            $where = " iOrderId = '$iOrderId'";
            $data['iStatusCode'] = 11;
            $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
            $OrderLogId = createOrderLog($iOrderId, "11");
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
            setDataResponse($returnArr);
        }

        $data['ePaymentOption'] = "Card";
        //}
    } else if ($ePaymentOption == "Cash") {
        $data['ePaymentOption'] = "Cash";
        $data['ePaid'] = "No";
    }

    $where = " iOrderId = '$iOrderId'";
    $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
    $OrderLogId = createOrderLog($iOrderId, "1");

    // # Send Notification To Company ##
    $CompanyMessage = "OrderRequested";
    $vLangCode = get_value('company', 'vLang', 'iCompanyId', $iCompanyId, '', 'true');
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $orderreceivelbl = $languageLabelsArr['LBL_NEW_ORDER_PLACED_TXT'] . " " . $vOrderNo;
    $alertMsg = $orderreceivelbl;
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }

    $alertSendAllowed = true;
    /* For PubNub Setting */
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
    /* For PubNub Setting Finished */
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

    //sleep(3);
    // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != ""){
    /* $pubnub = new Pubnub\Pubnub(array(
      "publish_key" => $PUBNUB_PUBLISH_KEY,
      "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
      "uuid" => $uuid
      )); */
    $channelName = "COMPANY_" . $iCompanyId;

    if ($eDeviceType == "Ios") {
        sleep(3);
    }
    // $info = $pubnub->publish($channelName, $message_pub);
    /* if ($PUBNUB_DISABLED == "Yes") {
      publishEventMessage($channelName, $message_pub);
      } else {
      $info = $pubnub->publish($channelName, $message_pub);
      } */
    publishEventMessage($channelName, $message_pub);
    /* if ($eDeviceType != "Android") {
      array_push($deviceTokens_arr_ios, $iGcmRegId);
      } */

    // }
    /* else{
      $alertSendAllowed = true;
      } */
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    if ($vPayMethod == "Instant" && $APP_PAYMENT_METHOD != "Flutterwave") {

        if ($payStatus == "" && $fNetTotal == 0 && $CheckUserWallet == "Yes" && $full_adjustment == 1) { //its used when wallet amt > order amt
            $returnArr['Action'] = "1";
            $returnArr['full_wallet_adjustment'] = "Yes";
            setDataResponse($returnArr);
        }

        /* Added By PM On 09-12-2019 For Flutterwave Code End */
        if ($payStatus == "succeeded") {
            $successUrl = $tconfig['tsite_url'] . "assets/libraries/webview/result.php?success=1";
            header('Location: ' . $successUrl);
            ?>
            <?php
        } else if ($payStatus == "Failed") {
            $failedUrl = $tconfig['tsite_url'] . "assets/libraries/webview/result.php?success=0";
            //header('Location: ' . $failedUrl);
            ?> 
            <script>window.location.replace("<?php echo $successUrl; ?>");
            </script>
            <?php
        }
    }
    // # Send Notification To Company ##
    $returnArr['Action'] = "1";
    $returnArr['iOrderId'] = $iOrderId;
    $returnArr['message'] = getPassengerDetailInfo($iUserId, "", "");
    setDataResponse($returnArr);
}

// ############################# Capture Card Paymant of Order ####################################################################
// ############################# Check Out Order Details ###########################################################################
// ############################ Calculate Order Estimate Amount ###################################################################
if ($type == "CheckOutOrderEstimateDetails") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    $iUserAddressId = isset($_REQUEST["iUserAddressId"]) ? $_REQUEST["iUserAddressId"] : '';
    $couponCode = isset($_REQUEST["vCouponCode"]) ? $_REQUEST["vCouponCode"] : '';
    $ePaymentOption = isset($_REQUEST["ePaymentOption"]) ? $_REQUEST["ePaymentOption"] : '';
    $vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $OrderDetails = isset($_REQUEST["OrderDetails"]) ? $_REQUEST["OrderDetails"] : '';
    $vInstruction = isset($_REQUEST["vInstruction"]) ? $_REQUEST["vInstruction"] : '';
    $passengerLat = isset($_REQUEST["PassengerLat"]) ? $_REQUEST["PassengerLat"] : '';
    $passengerLon = isset($_REQUEST["PassengerLon"]) ? $_REQUEST["PassengerLon"] : '';
    $CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';
    $eTakeAway = isset($_REQUEST["eTakeAway"]) ? $_REQUEST["eTakeAway"] : 'No';
    if ($CheckUserWallet == "" || $CheckUserWallet == NULL) {
        $CheckUserWallet = "No";
    }
    $Data = array();
    $restaurantnotavailable = 0;
    if ($eTakeAway == 'No') {
        if (!empty($iUserAddressId)) {
            $sql = "SELECT iUserAddressId FROM `user_address` WHERE iUserAddressId = '" . $iUserAddressId . "' AND eStatus='Active'";
            $data_user_address_data = $obj->MySQLSelect($sql);
            if (empty($data_user_address_data) || count($data_user_address_data) == 0) {
                $iUserAddressId = "";
            }
        }

        if (count($iUserId) > 0) {
            $UserSelectedAddressArr = GetUserSelectedLastOrderAddressCompanyLocationWise($iUserId, "Passenger", $passengerLat, $passengerLon, $iCompanyId, $iUserAddressId);
            if (!empty($UserSelectedAddressArr)) {
                $Data['UserSelectedAddress'] = $UserSelectedAddressArr['UserSelectedAddress'];
                $Data['UserSelectedLatitude'] = $UserSelectedAddressArr['UserSelectedLatitude'];
                $Data['UserSelectedLongitude'] = $UserSelectedAddressArr['UserSelectedLongitude'];
                $Data['UserSelectedAddressId'] = $UserSelectedAddressArr['UserSelectedAddressId'];
            }
        }
        //added by SP for selected user address remove if restaurant is not in that location on 12-08-2019

        if ($iUserId > 0) {
            $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = 'Rider' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
            $db_userdata = $obj->MySQLSelect($sql);
            $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
            $db_companydata = $obj->MySQLSelect($sql);
            $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
            $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
            $distancewithcompany = distanceByLocation($passengerLat, $passengerLon, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
            for ($i = 0; $i < count($db_userdata); $i++) {
                $isRemoveAddressFromList = "No";
                $eLocationAvailable = "Yes";
                $addressLatitude = $db_userdata[$i]['vLatitude'];
                $addressLongitude = $db_userdata[$i]['vLongitude'];
                $distance = distanceByLocation($vRestuarantLocationLat, $vRestuarantLocationLong, $addressLatitude, $addressLongitude, "K");
                if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
                if ($iCompanyId > 0) {
                    if ($distancewithcompany > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                        $isRemoveAddressFromList = "Yes";
                    }
                }
                if ($isRemoveAddressFromList == "Yes") {
                    $eLocationAvailable = "No";
                }
                $db_userdata[$i]['eLocationAvailable'] = $eLocationAvailable;
                if ($eLocationAvailable == 'Yes') {
                    $restaurantnotavailable = 1;
                }
            }
        } else {
            $restaurantnotavailable = -1;
        }
    }
    //added by SP for selected user address remove if restaurant is not in that location on 12-08-2019
    // # Checking Distance Between Company and User Address ##
    //Added By HJ On 23-01-2020 For Solved Currency Related Issues Start
    $currencySymbol = "";
    $currencycode = isset($_REQUEST["vGeneralCurrency"]) ? $_REQUEST["vGeneralCurrency"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Curren cy Code
    if ($iUserId > 0) {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
        if (count($UserDetailsArr) > 0) {
            $Ratio = $UserDetailsArr['Ratio'];
            $currencySymbol = $UserDetailsArr['currencySymbol'];
            $vLang = $UserDetailsArr['vLang'];
        }
    } else {
        if (($currencySymbol == "" || $currencySymbol == NULL) && $currencycode != "") {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE vName = '" . $currencycode . "'");
        } else {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'");
        }
        if (count($currencyData) > 0) {
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        } else {
            $currencycode = "USD";
            $currencySymbol = "$";
            $priceRatio = 1.0000;
        }
    }
    if ($vLang == "" || $vLang == NULL) {
        $vLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Language Code
    }
    //Added By HJ On 23-01-2020 For Solved Currency Related Issues End
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $sql = "select vImage,vCaddress,vCompany,fMaxOfferAmt,fTargetAmt,fOfferType,fOfferAppyType,iMaxItemQty,fOfferAmt,iServiceId,vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPackingCharge,eTakeaway from `company` where iCompanyId = '" . $iCompanyId . "'";
    $db_companydata = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($db_companydata);die;
    $iServiceId = $db_companydata[0]['iServiceId'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $vCompany = $db_companydata[0]['vCompany'];
    $vCaddress = $db_companydata[0]['vCaddress'];
    $vCompanyImage = $db_companydata[0]['vImage'];
    $fMaxOfferAmt = $db_companydata[0]['fMaxOfferAmt'];
    $fMaxOfferAmt = $generalobj->setTwoDecimalPoint($fMaxOfferAmt * $Ratio);
    $fTargetAmt = $db_companydata[0]['fTargetAmt'];
    $fTargetAmt = $generalobj->setTwoDecimalPoint($fTargetAmt * $Ratio);
    $fOfferAppyType = $db_companydata[0]['fOfferAppyType'];
    $fOfferType = $db_companydata[0]['fOfferType'];
    $iMaxItemQty = $db_companydata[0]['iMaxItemQty'];
    //Added By HJ On 15-05-2020 As Per Discuss With KS Start
    if (strtoupper($APP_PAYMENT_MODE) == "CASH") {
        $db_companydata[0]['eTakeaway'] = "No";
    }else if (strtoupper($APP_PAYMENT_MODE) == "CARD" || strtoupper($APP_PAYMENT_MODE) == "CASH-CARD") {
        $db_companydata[0]['eTakeaway'] = "Yes";
    }
    //Added By HJ On 15-05-2020 As Per Discuss With KS End
    $couponCode = trim($couponCode);
    if ($couponCode != "") {
        $validPromoCodesArr = getValidPromoCodes();
        if (empty($validPromoCodesArr) || empty($validPromoCodesArr['CouponList']) || count($validPromoCodesArr['CouponList']) == 0) {
            $returnArr['Action'] = "0"; // code is invalid
            $returnArr["message"] = "LBL_INVALID_COUPON_CODE";
            setDataResponse($returnArr);
        }
    }
    $ispriceshow = '';
    if (isset($iServiceId) && !empty($iServiceId)) {
        $servFields = 'eType';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
                $ispriceshow = $ServiceCategoryData[0]['eType'];
            }
        }
    }
    $OrderDetails = json_decode(stripcslashes($OrderDetails), true);
    $OrderDetailsItemsArr = array();
    if (!empty($OrderDetails)) {
        $fFinalTotal = $fTotalDiscount = $fTotalMenuItemBasePrice = $fFinalDiscountPercentage = 0;
        //Added By HJ On 09-05-2019 For Optimize Code Start
        $optionPriceArr = getAllOptionAddonPriceArr();
        $ordItemPriceArr = getAllMenuItemPriceArr();
        //Added By HJ On 09-05-2019 For Optimize Code End
        for ($j = 0; $j < count($OrderDetails); $j++) {
            $iQty = $OrderDetails[$j]['iQty'];
            //$fMenuItemPrice = FoodMenuItemBasicPrice($OrderDetails[$j]['iMenuItemId'], $iQty); //Commnent By HJ On 09-05-2019 For Optimize Below Code
            $fMenuItemPrice = 0;
            if (isset($ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']]) && $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] > 0) {
                $fMenuItemPrice = $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] * $iQty;
            }
            //$vOptionPrice = GetFoodMenuItemOptionPrice($OrderDetails[$j]['vOptionId']); //Commnent By HJ On 09-05-2019 For Optimize Below Code
            //Added By HJ On 09-05-2019 For Optimize Code Start
            $vOptionPrice = 0;
            $explodeOption = explode(",", $OrderDetails[$j]['vOptionId']);
            for ($fd = 0; $fd < count($explodeOption); $fd++) {
                if (isset($optionPriceArr[$explodeOption[$fd]]) && $optionPriceArr[$explodeOption[$fd]] > 0) {
                    $vOptionPrice += $optionPriceArr[$explodeOption[$fd]];
                }
            }
            //Added By HJ On 09-05-2019 For Optimize Code End
            $vOptionPrice = $vOptionPrice * $iQty;
            //$vAddonPrice = GetFoodMenuItemAddOnPrice($OrderDetails[$j]['vAddonId']); //Commnent By HJ On 09-05-2019 For Optimize Below Code
            //Added By HJ On 09-05-2019 For Optimize Code Start
            $vAddonPrice = 0;
            $explodeAddon = explode(",", $OrderDetails[$j]['vAddonId']);
            for ($df = 0; $df < count($explodeAddon); $df++) {
                if (isset($optionPriceArr[$explodeAddon[$df]]) && $optionPriceArr[$explodeAddon[$df]] > 0) {
                    $vAddonPrice += $optionPriceArr[$explodeAddon[$df]];
                }
            }
            //Added By HJ On 09-05-2019 For Optimize Code End
            $vAddonPrice = $vAddonPrice * $iQty;
            if (isset($ispriceshow) && !empty($ispriceshow)) {
                if ($vOptionPrice == 0) {
                    $vOptionPrice = $vOptionPrice + $fMenuItemPrice;
                }
                $fTotalMenuItemBasePrice = $fTotalMenuItemBasePrice + $vOptionPrice + $vAddonPrice;
            } else {
                $fTotalMenuItemBasePrice = $fTotalMenuItemBasePrice + $vOptionPrice + $vAddonPrice + $fMenuItemPrice;
            }
        }

        if ($db_companydata[0]['fMaxOfferAmt'] > 0 && $fOfferType == "Percentage" && $fOfferAppyType != "None") {
            $fFinalDiscountPercentage = (($fTotalMenuItemBasePrice * $db_companydata[0]['fOfferAmt']) / 100);
        }

        $fTotalMenuItemBasePrice = $generalobj->setTwoDecimalPoint($fTotalMenuItemBasePrice * $Ratio);
        $fFinalDiscountPercentage = $generalobj->setTwoDecimalPoint($fFinalDiscountPercentage * $Ratio);
        $itemDataArr = array();
        $itemData = $obj->MySQLSelect("SELECT iMenuItemId,vItemType_$vLang,vImage FROM menu_items");
        for ($s = 0; $s < count($itemData); $s++) {
            $itemDataArr[$itemData[$s]['iMenuItemId']] = $itemData[$s];
        }
        $itemImageUrl = $tconfig["tsite_upload_images_menu_item"];
        for ($i = 0; $i < count($OrderDetails); $i++) {
            $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $iFoodMenuId = $OrderDetails[$i]['iFoodMenuId'];
            $vOptionId = $OrderDetails[$i]['vOptionId'];
            $vAddonId = $OrderDetails[$i]['vAddonId'];
            $iQty = $OrderDetails[$i]['iQty'];
            //$vItemType = get_value('menu_items', 'vItemType_' . $vLang, 'iMenuItemId', $iMenuItemId, '', 'true');
            $vItemType = $vImage = "";
            if (isset($itemDataArr[$iMenuItemId])) {
                $vItemType = $itemDataArr[$iMenuItemId]['vItemType_' . $vLang];
                $vImage = $itemDataArr[$iMenuItemId]['vImage'];
            }
            //echo $vImage;die;
            $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, "1", $iUserId, "Calculate", $vOptionId, $vAddonId, $iServiceId);
            //echo "<pre>";print_R($MenuItemPriceArr);die;
            $TotOrders = $MenuItemPriceArr['TotOrders'];
            if ($fOfferType == "Flat" && $fOfferAppyType != "None" && $TotOrders == 0) {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = $generalobj->setTwoDecimalPoint($fOriginalPrice);
                $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $Ratio;
                $fDiscountPrice = $generalobj->setTwoDecimalPoint($fDiscountPrice);
                $fPrice = $fOriginalPrice;
                $fOfferAmt = 0;
            } else {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = $generalobj->setTwoDecimalPoint($fOriginalPrice);
                $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $iQty * $Ratio;
                $fDiscountPrice = $generalobj->setTwoDecimalPoint($fDiscountPrice);
                $fPrice = $MenuItemPriceArr['fPrice'] * $iQty * $Ratio;
                $fPrice = $generalobj->setTwoDecimalPoint($fPrice);
                $fOfferAmt = $MenuItemPriceArr['fOfferAmt'];
                $fOfferAmt = $generalobj->setTwoDecimalPoint($fOfferAmt);

                if ($fOfferType == "Flat" && $fOfferAppyType == "All") {
                    $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $Ratio;
                    $fDiscountPrice = $generalobj->setTwoDecimalPoint($fDiscountPrice);
                    $fPrice = $fOriginalPrice;
                    $fOfferAmt = 0;
                }
            }

            if ($fTotalMenuItemBasePrice < $fTargetAmt && $fOfferAppyType != "None") {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = $generalobj->setTwoDecimalPoint($fOriginalPrice);
                $fDiscountPrice = $fOfferAmt = 0;
                $fPrice = $fOriginalPrice;
            }

            $fTotalPrice = $fOriginalPrice;
            $fTotalPrice = $generalobj->setTwoDecimalPoint($fTotalPrice);
            $fFinalTotal = $fFinalTotal + $fTotalPrice;
            if ($fOfferType == "Flat" && $fOfferAppyType != "None" && $TotOrders == 0) {
                $fTotalDiscount = $fDiscountPrice;
            } else if ($fOfferType == "Percentage" && $fOfferAppyType != "None") {
                $fTotalDiscount += $fDiscountPrice;
            } else {
                $fTotalDiscount += $fDiscountPrice;
            }
            /* if ($fMaxOfferAmt > 0 && $fOfferType == "Percentage" && $fOfferAppyType != "None") {
              $fTotalDiscount = ($fTotalDiscount > $fMaxOfferAmt) ? $fMaxOfferAmt : $fTotalDiscount;
              $fPrice = ($fFinalDiscountPercentage > $fMaxOfferAmt) ? $fOriginalPrice : $fPrice;
              $fOfferAmt = ($fFinalDiscountPercentage > $fMaxOfferAmt) ? 0 : $fOfferAmt;
              } */
            $OrderDetailsItemsArr[$i]['iMenuItemId'] = $iMenuItemId;
            $OrderDetailsItemsArr[$i]['iFoodMenuId'] = $iFoodMenuId;
            $OrderDetailsItemsArr[$i]['vItemType'] = $vItemType;
            $OrderDetailsItemsArr[$i]['iQty'] = $iQty;
            $OrderDetailsItemsArr[$i]['fOfferAmt'] = $fOfferAmt;
            $OrderDetailsItemsArr[$i]['fOriginalPrice'] = formatnum($fOriginalPrice);
            $OrderDetailsItemsArr[$i]['fPrice'] = formatnum($fPrice);
            $imageUrl = "";
            if ($vImage != "") {
                $imageUrl = $itemImageUrl . "/" . $vImage;
            }
            $OrderDetailsItemsArr[$i]['vImage'] = $imageUrl;
            $optionaddonname = "";
            if ($vOptionId != "") {
                $optionname = GetMenuItemOptionsToppingName($vOptionId);
                $optionaddonname = $optionname;
            }

            if ($vAddonId != "") {
                $addonname = GetMenuItemOptionsToppingName($vAddonId);
                if ($optionaddonname != "") {
                    $optionaddonname .= ", " . $addonname;
                } else {
                    $optionaddonname = $addonname;
                }
            }
            $OrderDetailsItemsArr[$i]['optionaddonname'] = $optionaddonname;
        }
        //echo "<pre>";print_R($OrderDetailsItemsArr);die;
        $Data['OrderDetailsItemsArr'] = $OrderDetailsItemsArr;
        //$fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
        $fPackingCharge = 0;
        if (isset($db_companydata[0]['fPackingCharge']) && $db_companydata[0]['fPackingCharge'] > 0) {
            $fPackingCharge = $generalobj->setTwoDecimalPoint($db_companydata[0]['fPackingCharge'] * $Ratio);
        }

        // # Calculate Order Delivery Charge ##
        $fDeliveryCharge = 0;
        if ($eTakeAway == 'No') {
            if (isset($Data['UserSelectedLatitude']) && isset($Data['UserSelectedLongitude'])) {
                //$sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong FROM company WHERE iCompanyId	= '" . $iCompanyId . "'";
                //$datac = $obj->MySQLSelect($sql);
                if (count($db_companydata) > 0) {
                    $User_Address_Array = array($Data['UserSelectedLatitude'], $Data['UserSelectedLongitude']);
                    $iLocationId = GetUserGeoLocationId($User_Address_Array);
                    //Added By HJ On 02-01-2019 For Get All Location Delivery Charge Start As Per Discuss With CD Sir
                    $checkAllLocation = 1;
                    if ($iLocationId > 0) {
                        $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '" . $iLocationId . "' AND eStatus='Active'";
                        $data_location = $obj->MySQLSelect($sql);
                        if (count($data_location) > 0) {
                            $checkAllLocation = 0;
                        }
                    }
                    if ($checkAllLocation == 1) {
                        $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '0' AND eStatus='Active'";
                        $data_location = $obj->MySQLSelect($sql);
                    }
                    $fDeliveryCharge = 0;
                    if (count($data_location) > 0) {
                        //Added By HJ On 02-01-2019 For Get All Location Delivery Charge End As Per Discuss With CD Sir
                        $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
                        $distance = distanceByLocation($Data['UserSelectedLatitude'], $Data['UserSelectedLongitude'], $db_companydata[0]['restaurantlat'], $db_companydata[0]['restaurantlong'], "K");
                        $checkedDlCharge = 0;
                        //if ($distance < $iFreeDeliveryRadius && $iFreeDeliveryRadius >= 0) {
                        if ($distance < $iFreeDeliveryRadius && $iFreeDeliveryRadius >= 0 && !empty($iFreeDeliveryRadius)) { //when zero for free order and radius then do not allow free order
                            $fDeliveryCharge = 0;
                            $checkedDlCharge = 1;
                        }
                        $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
                        $fFreeOrderPriceSubtotal = $generalobj->setTwoDecimalPoint($fFreeOrderPriceSubtotal * $Ratio);
                        //added by SP 27-06-2019 for delivery charge blank then it does not count as free delivery
                        //if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0) {
                        if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0 && !empty($fFreeOrderPriceSubtotal)) { //when zero for free order and radius then do not allow free order
                            if ($fFinalTotal > $fFreeOrderPriceSubtotal && $checkedDlCharge == 0) {
                                $fDeliveryCharge = 0;
                                $checkedDlCharge = 1;
                            }
                        }
                        $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
                        $fOrderPriceValue = $generalobj->setTwoDecimalPoint($fOrderPriceValue * $Ratio);
                        $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];

                        $fDeliveryChargeAbove = $generalobj->setTwoDecimalPoint($fDeliveryChargeAbove * $Ratio);
                        $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
                        $fDeliveryChargeBelow = $generalobj->setTwoDecimalPoint($fDeliveryChargeBelow * $Ratio);
                        if ($checkedDlCharge == 0) {
                            if ($fFinalTotal >= $fOrderPriceValue) {
                                $fDeliveryCharge = $fDeliveryChargeAbove;
                                //$fDeliveryCharge = $fDeliveryChargeBelow;
                            } else {
                                $fDeliveryCharge = $fDeliveryChargeBelow;
                                //$fDeliveryCharge = $fDeliveryChargeAbove;
                            }
                        }
                    }
                }
            }
        }

        // # Calculate Order Delivery Charge ##
        $TaxArr = getMemberCountryTax($iUserId, "Passenger");
        $fTax = $TaxArr['fTax1'];
        if ($fTax > 0) {
            $ftaxamount = $fFinalTotal - $fTotalDiscount + $fPackingCharge;
            $fTax = $generalobj->setTwoDecimalPoint((($ftaxamount * $fTax) / 100));
        }
        $fCommision = $ADMIN_COMMISSION;
        $fNetTotal = $fFinalTotal + $fPackingCharge + $fDeliveryCharge + $fTax - $fTotalDiscount;
        $fTotalGenerateFare = $fNetTotal;
        $fOrderFare_For_Commission = $fFinalTotal;
        $fCommision = $generalobj->setTwoDecimalPoint((($fOrderFare_For_Commission * $fCommision) / 100));
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
            //$discountValue = $generalobj->setTwoDecimalPoint($discountValue * $Ratio);
            //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019

            if ($discountValueType == "percentage")
                $discountValue = $generalobj->setTwoDecimalPoint($discountValue);
            else
                $discountValue = $generalobj->setTwoDecimalPoint($discountValue * $Ratio);
        }
        if ($couponCode != '' && $discountValue != 0) {
            if ($discountValueType == "percentage") {
                $discountApplyOn = $fNetTotal - $fDeliveryCharge - $fTax; // Added By HJ On 27-06-2019 As Per Discuss With BM Mam
                //echo $discountApplyOn."====="$fNetTotal."aaaa".$fDeliveryCharge."=====";
                $vDiscount = $generalobj->setTwoDecimalPoint($discountValue, 1) . ' ' . "%";
                $discountValue = $generalobj->setTwoDecimalPoint($discountApplyOn * $discountValue) / 100;
                //echo $discountValue;
            } else {
                $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                if ($discountValue > $fNetTotal) {
                    $vDiscount = $generalobj->setTwoDecimalPoint($fNetTotal) . ' ' . $curr_sym;
                } else {
                    $vDiscount = $generalobj->setTwoDecimalPoint($discountValue) . ' ' . $curr_sym;
                }
            }
            $fNetTotal = $fNetTotal - $discountValue;
            if ($fNetTotal < 0) {
                $fNetTotal = $fTotalGenerateFare = 0;
                // $discountValue = $fNetTotal;
            }
            $fTotalGenerateFare = $fNetTotal;
            $Order_data[0]['fDiscount'] = $discountValue;
            $Order_data[0]['vDiscount'] = $vDiscount;
        }
        /* Check Coupon Code Total Fare  End */
        /* Checking For Passenger Outstanding Amount */
        $fOutStandingAmount = 0;
        $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
        $fOutStandingAmount = $generalobj->setTwoDecimalPoint($fOutStandingAmount * $Ratio);
        if ($fOutStandingAmount > 0) {
            $fNetTotal = $fNetTotal + $fOutStandingAmount;
            $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
        }

        /* Checking For Passenger Outstanding Amount */
        /* Check debit wallet For Count Total Order Fare Start */
        $user_wallet_debit_amount = 0;
        $DisplayCardPayment = "Yes";
        if ($iUserId > 0 && $CheckUserWallet == "Yes") {
            $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
            $user_available_balance = $generalobj->setTwoDecimalPoint($user_available_balance * $Ratio);
            if ($fNetTotal > $user_available_balance) {
                $fNetTotal = $fNetTotal - $user_available_balance;
                $user_wallet_debit_amount = $user_available_balance;
                $fTotalGenerateFare = $fNetTotal;
                $DisplayCardPayment = "Yes";
            } else {
                $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
                $fNetTotal = 0;
                $fTotalGenerateFare = $fNetTotal;
                $DisplayCardPayment = "No";
            }
        }

        /* Check debit wallet For Count Total Order Fare End */
        if ($fNetTotal < 0) {
            $fNetTotal = $fTotalGenerateFare = 0;
        }

        #############################
        //added by SP on 15-11-2019 for rounding off start
        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, cu.Ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iUserId . "'";
        $currData = $obj->MySQLSelect($sqlp);
        $vCurrency = $currData[0]['vName'];

        if ($currData[0]['eRoundingOffEnable'] == "Yes") {
            $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $vCurrency, '', 'true');

            $roundingOffTotal_fare_amountArr = getRoundingOffAmount($fNetTotal * $userCurrencyRatio, $vCurrency);

            $fNetTotal = $fTotalGenerateFare = $roundingOffTotal_fare_amountArr['finalFareValue'];

            if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                $eRoundingType = "Addition";
            } else {
                $eRoundingType = "Substraction";
            }

            $fRoundingAmount = $generalobj->setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue']);

            $fRoundingAmount = $fRoundingAmount;
            $eRoundingType = $eRoundingType;
        }


        $Data['fSubTotal'] = $currencySymbol . " " . formatnum($fFinalTotal);
        $Data['fTotalDiscount'] = $currencySymbol . " " . formatnum($fTotalDiscount);
        $fPackingCharge = $generalobj->setTwoDecimalPoint($fPackingCharge);
        $Data['fPackingCharge'] = ($fPackingCharge > 0) ? $currencySymbol . " " . formatnum($fPackingCharge) : 0;
        $fDeliveryCharge = $generalobj->setTwoDecimalPoint($fDeliveryCharge);
        $Data['fDeliveryCharge'] = ($fDeliveryCharge > 0) ? $currencySymbol . " " . formatnum($fDeliveryCharge) : 0;
        $fTax = $generalobj->setTwoDecimalPoint($fTax);
        $Data['fTax'] = ($fTax > 0) ? $currencySymbol . " " . formatnum($fTax) : 0;
        $fDiscount_Val = 0;
        if (isset($Order_data[0]['fDiscount']) && $Order_data[0]['fDiscount'] > 0) {
            $fDiscount_Val = $generalobj->setTwoDecimalPoint($Order_data[0]['fDiscount']);
        }
        $Data['fDiscount'] = ($fDiscount_Val > 0) ? $currencySymbol . " " . $fDiscount_Val : 0;

        // $Data['vDiscount'] = $Order_data[0]['vDiscount'];
        $fCommision = $generalobj->setTwoDecimalPoint($fCommision);
        $Data['fCommision'] = ($fCommision > 0) ? $currencySymbol . " " . formatnum($fCommision) : 0;
        $fNetTotal = $generalobj->setTwoDecimalPoint($fNetTotal);
        $Data['fNetTotal'] = ($fNetTotal > 0) ? $currencySymbol . " " . formatnum($fNetTotal) : $currencySymbol . " 0";
        $Data['fNetTotalAmount'] = $fNetTotal;
        $fTotalGenerateFare = $generalobj->setTwoDecimalPoint($fTotalGenerateFare);
        $Data['fTotalGenerateFare'] = ($fTotalGenerateFare > 0) ? $currencySymbol . " " . formatnum($fTotalGenerateFare) : $currencySymbol . " 0";
        $Data['fTotalGenerateFareAmount'] = $fTotalGenerateFare;
        $Data['fOutStandingAmount'] = ($fOutStandingAmount > 0) ? $currencySymbol . " " . formatnum($fOutStandingAmount) : $currencySymbol . " 0";
        $Data['fWalletDebit'] = ($user_wallet_debit_amount > 0) ? $currencySymbol . " " . formatnum($user_wallet_debit_amount) : $currencySymbol . " 0";
        $Data['user_wallet_debit_amount'] = $user_wallet_debit_amount;
        $Data['currencySymbol'] = $currencySymbol;
        $Data['DisplayCardPayment'] = $DisplayCardPayment;
        $Data['DisplayUserWalletDebitAmount'] = ($user_wallet_debit_amount > 0) ? $currencySymbol . " " . formatnum($user_wallet_debit_amount) : "";
        $Data['DISABLE_CASH_PAYMENT_OPTION'] = ($fOutStandingAmount > 0) ? "Yes" : "No";

        $OrderFareDetailsArr = $OrderFareDetailsArrNew = array();
        /* if($fFinalTotal > 0) {
          $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $Data['fSubTotal'];
          $arrindex++;
          } */

        if ($fFinalTotal > 0) {
            $OrderFareDetailsArr[][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $Data['fSubTotal'];
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $Data['fSubTotal'];
        }

        if ($fTotalDiscount > 0) {
            $OrderFareDetailsArr[][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fTotalDiscount);
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fTotalDiscount);
        }

        if ($fPackingCharge > 0) {
            $OrderFareDetailsArr[][$languageLabelsArr['LBL_PACKING_CHARGE']] = $currencySymbol . " " . formatnum($fPackingCharge);
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_PACKING_CHARGE']] = $currencySymbol . " " . formatnum($fPackingCharge);
        }
        //echo "<pre>";
        //print_r($OrderFareDetailsArr);die;

        if ($fDeliveryCharge > 0) {
            $OrderFareDetailsArr[][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = $currencySymbol . " " . formatnum($fDeliveryCharge);
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = $currencySymbol . " " . formatnum($fDeliveryCharge);
        }

        if ($fTax > 0) {
            $OrderFareDetailsArr[][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $currencySymbol . " " . formatnum($fTax);
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $currencySymbol . " " . formatnum($fTax);
        }

        if ($fOutStandingAmount > 0) {
            $OrderFareDetailsArr[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fOutStandingAmount);
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fOutStandingAmount);
        }

        if ($fDiscount_Val > 0) {
            $OrderFareDetailsArr[][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fDiscount_Val);
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fDiscount_Val);
        }

        if ($user_wallet_debit_amount > 0) {
            $OrderFareDetailsArr[][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "- " . $currencySymbol . " " . formatnum($user_wallet_debit_amount);
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "- " . $currencySymbol . " " . formatnum($user_wallet_debit_amount);
        }

        //added by SP on 15-11-2019 for rounding off start
        //if($currData[0]['eRoundingOffEnable'] == "Yes" && ){
        if (isset($fRoundingAmount) && !empty($fRoundingAmount) && $fRoundingAmount != 0 && $currData[0]['eRoundingOffEnable'] == "Yes") {
            $fRoundingAmount = $fRoundingAmount;
            $eRoundingType = $eRoundingType;

            if ($eRoundingType == "Addition") {
                $roundingMethod = "";
            } else {
                $roundingMethod = "-";
            }

            $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";

            $OrderFareDetailsArr[][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = $roundingMethod . " " . $currencySymbol . "" . $fRoundingAmount;
            $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = $roundingMethod . " " . $currencySymbol . "" . $rounding_diff;
        }

        // if ($fTotalGenerateFare > 0) {
        $OrderFareDetailsArr[][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fTotalGenerateFare);
        $OrderFareDetailsArrNew[][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fTotalGenerateFare);
        // }
    }


    $restaurant_status_arr = calculate_restaurant_time_span($iCompanyId, $iUserId);
    $Data['restaurantstatus'] = $restaurant_status_arr['restaurantstatus'];
    //echo "<pre>";print_r($OrderFareDetailsArr);die;
    $Data['FareDetailsArr'] = $OrderFareDetailsArr;
    $Data['FareDetailsArrNew'] = $OrderFareDetailsArrNew;

    /* if($restaurantnotavailable==0) $totalAddress = "0";
      else if($restaurantnotavailable==1) $totalAddress = "1";
      else $totalAddress = "0"; */

    //added by SP for selected user address remove if restaurant is not in that location on 12-08-2019
    if ($restaurantnotavailable == 0) {
        $Data['RestaurantAddressNotMatch'] = "0";
        $Data['RestaurantAddressNotMatchLBL'] = 'LBL_CHANGE_ADDRESS_AVAILABLE_NOTE';
    } else {
        $Data['RestaurantAddressNotMatch'] = "";
    }
    //added by SP for selected user address remove if restaurant is not in that location on 12-08-2019
    //$Data['ToTalAddress'] = GetTotalUserAddress($iUserId, "Passenger", $passengerLat, $passengerLon, $iCompanyId);
    $Data['ToTalAddress'] = !empty($UserSelectedAddressArr) ? "1" : "0";
    if ($eTakeAway == 'Yes') {
        $Data['ToTalAddress'] = 1;
    }
    $Data['vCompany'] = $vCompany;
    //added by SP on 9-9-2019 for new design
    $Data['vCaddress'] = $vCaddress;
    $Data['vImage'] = $tconfig['tsite_upload_images_compnay'] . "/" . $iCompanyId . "/" . $vCompanyImage;
    $Data['iMaxItemQty'] = $iMaxItemQty;
    $Data['eTakeaway'] = (isTakeAwayEnable() && $db_companydata[0]['eTakeaway'] == 'Yes') ? 'Yes' : 'No';
    $returnArr = $Data;

    $returnArr['DeliveryPreferences']['Enable'] = (isDeliveryPreferenceEnable() == true) ? 'Yes' : 'No';
    if (isDeliveryPreferenceEnable()) {
        $deliveryPrefSql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_EXTRACT(tTitle, '$.tTitle_" . $vLang . "')) as tTitle, JSON_UNQUOTE(JSON_EXTRACT(tDescription, '$.tDescription_" . $vLang . "')) as tDescription, ePreferenceFor, eImageUpload, iDisplayOrder, eContactLess, eStatus FROM delivery_preferences WHERE eStatus = 'Active' AND is_deleted = 0";
        $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);

        foreach ($deliveryPrefSqlData as $pkey => $pref) {
            if ($APP_PAYMENT_MODE == "Cash" && $pref['eContactLess'] == 'Yes') {
                unset($deliveryPrefSqlData[$pkey]);
            }

            if ($eTakeAway == "Yes" && $pref['ePreferenceFor'] == 'Provider') {
                unset($deliveryPrefSqlData[$pkey]);
            }
        }

        $deliveryPrefSqlData = array_values($deliveryPrefSqlData);
        if (count($deliveryPrefSqlData) > 0) {
            $returnArr['DeliveryPreferences']['vTitle'] = $languageLabelsArr['LBL_DELIVERY_PREF'];
            $returnArr['DeliveryPreferences']['Data'] = $deliveryPrefSqlData;
        } else {
            $returnArr['DeliveryPreferences']['Enable'] = 'No';
        }
    }

    $returnArr['Action'] = "1";
    if (!empty($iUserId)) {
        $returnArr['message'] = getPassengerDetailInfo($iUserId, "", ""); // Added By HJ On 08-11-2019 As Per Dicuss WIth KS and DT
    }
    setDataResponse($returnArr);
}

// ############################ Calculate Order Estimate Amount ###################################################################
// ############################# Display User's Active Orders ###################################################################
if ($type == "DisplayActiveOrder") {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; // Passenger, Driver , Company
    $vSubFilterParam = isset($_REQUEST["vSubFilterParam"]) ? $_REQUEST["vSubFilterParam"] : "";

    $per_page = 10;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $iMemberId = 'ord.iUserId';
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
    } else if ($UserType == "Driver") {
        $tblname = "register_driver";
        $iMemberId = 'ord.iDriverId';
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iUserId);
    } else {
        $tblname = "company";
        $iMemberId = 'ord.iCompanyId';
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iUserId);
    }

    $enable_takeaway = 0;
    if (isTakeAwayEnable()) {
        $enable_takeaway = 1;
    }

    $whereStatusCode = "  AND ord.iStatusCode NOT IN(12)";
    //$whereStatusCode = " AND  ord.iStatusCode NOT IN(12) ";
    if ($vSubFilterParam != "") {
        if ($enable_takeaway == 1) {
            if ($vSubFilterParam == '6-1') {
                $whereStatusCode = " AND ord.iStatusCode IN (6) AND ord.eTakeaway = 'Yes'";
            } else if ($vSubFilterParam == '6') {
                $whereStatusCode = " AND ord.iStatusCode IN ($vSubFilterParam) AND ord.eTakeaway = 'No'";
            } else if ($vSubFilterParam == '8') {
                $whereStatusCode = " AND ord.iStatusCode IN (8,9)";
            } else {
                $whereStatusCode = " AND ord.iStatusCode IN ($vSubFilterParam)";
            }
        } else if ($vSubFilterParam == '8') {
            $whereStatusCode = " AND ord.iStatusCode IN (8,9)";
        } else {
            $whereStatusCode = " AND ord.iStatusCode IN ($vSubFilterParam)";
        }
    }

    $filterSelected = "All";
    if ($vSubFilterParam != "") {
        $filterSelected = $vSubFilterParam;
    }
    $filterSelected = $vSubFilterParam;
    $Ratio = $UserDetailsArr['Ratio'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $vLang = $UserDetailsArr['vLang'];

    $data_count_all = $obj->MySQLSelect("select COUNT(ord.iOrderId) As TotalIds from orders as ord where $iMemberId = '" . $iUserId . "' AND ord.iStatusCode NOT IN(12) ORDER BY ord.iOrderId DESC");
    $TotalPages = 0;
    if (isset($data_count_all[0]['TotalIds'])) {
        $TotalPages += ceil($data_count_all[0]['TotalIds'] / $per_page);
    }
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    $sql = "select co.vDemoStoreImage,co.vCompany,co.iServiceId,sc.vServiceName_" . $vLang . " as vServiceCategoryName, co.vCaddress as vRestuarantLocation,co.vImage,ord.iOrderId,ord.tOrderRequestDate,ord.fNetTotal,ord.iCompanyId,ord.iStatusCode,ord.vOrderNo,ord.fRoundingAmount,ord.eRoundingType,ord.eTakeaway from orders as ord LEFT JOIN company as co ON ord.iCompanyId=co.iCompanyId LEFT JOIN service_categories as sc on sc.iServiceId=co.iServiceId where $iMemberId = '" . $iUserId . "' $whereStatusCode ORDER BY ord.iOrderId DESC" . $limit; //added by SP on 01-10-2019 for cubex design


    $data_order = $obj->MySQLSelect($sql);


    $sql_rating = "select co.vDemoStoreImage,co.vCompany,co.iServiceId,sc.vServiceName_" . $vLang . " as vServiceCategoryName, co.vCaddress as vRestuarantLocation,co.vImage,ord.iOrderId,ord.tOrderRequestDate,ord.fNetTotal,ord.iCompanyId,ord.iStatusCode,ord.vOrderNo from orders as ord LEFT JOIN company as co ON ord.iCompanyId=co.iCompanyId LEFT JOIN service_categories as sc on sc.iServiceId=co.iServiceId where $iMemberId = '" . $iUserId . "' $whereStatusCode ORDER BY ord.iOrderId DESC" . $limit; //added by SP on 01-10-2019 for cubex design

    $data_order_rating = $obj->MySQLSelect($sql_rating);
    for ($s = 0; $s < count($data_order_rating); $s++) {
        $orderIds_rating .= "'" . $data_order_rating[$s]['iOrderId'] . "',";
    }
    $getDriverRateData = $obj->MySQLSelect("SELECT SUM(vRating1) vRating1 FROM ratings_user_driver WHERE eUserType='Passenger' AND iOrderId IN ($orderIds_rating)");
    $totalDriverRate = $driverAvgRate_new = 0;
    if (count($getDriverRateData) > 0) {
        $totalDriverRate = $getDriverRateData[0]['vRating1'];
    }
    if (count($data_order) > 0) {
        $driverAvgRate_new = $totalDriverRate / count($data_order);
    }
    $driverAvgRate_new = round($driverAvgRate_new, 1);

    $serverTimeZone = date_default_timezone_get();
    $appTypeFilterArr = AppTypeFilterArr($iUserId, $UserType, $vLang);
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $returnData['AppTypeFilterArr'] = $appTypeFilterArr;

    $takeaway_orderstaus = '';
    if ($enable_takeaway == 0) {
        $takeaway_orderstaus = " AND eTakeaway != 'Yes'";
    }
    $getOrderStatus = $obj->MySQLSelect("SELECT vStatus_" . $vLang . ",iStatusCode,eTakeaway FROM order_status WHERE iStatusCode IN (1,6,8)" . $takeaway_orderstaus);
    $returnArr['orderStatusFilter'] = $getOrderStatus;
    $appTypeFilterArr = $optionArr = array();
    $optionArr[] = array("vSubFilterParam" => "", "vTitle" => $languageLabelsArr['LBL_ALL']);
    for ($d = 0; $d < count($getOrderStatus); $d++) {
        $statusArr = array();
        //$statusArr['vSubFilterParam'] = $getOrderStatus[$d]['iStatusCode'];
        if ($getOrderStatus[$d]['iStatusCode'] == 6 && $getOrderStatus[$d]['eTakeaway'] == 'Yes' && $enable_takeaway == 1) {
            $statusArr['vSubFilterParam'] = "6-1";
        } else {
            $statusArr['vSubFilterParam'] = $getOrderStatus[$d]['iStatusCode'];
        }
        $statusArr['vTitle'] = $getOrderStatus[$d]['vStatus_' . $vLang];
        $optionArr[] = $statusArr;
    }
    $returnArr['subFilterOption'] = $optionArr;

    if (count($data_order) > 0) {
        //$seviceCategoriescount = getServiceCategoryCounts();//commented bc for grocery it takes from conf file not from db..bc in table all entries are there..
        $seviceCategoriescount = count($service_categories_ids_arr);
        $orderIds = "";
        for ($s = 0; $s < count($data_order); $s++) {
            $orderIds .= "'" . $data_order[$s]['iOrderId'] . "',";
        }
        $orderStatusArr = array();
        if ($orderIds != "") {
            $orderIds = trim($orderIds, ",");
            $OrderStatus = $obj->MySQLSelect("SELECT os.vStatus_Track,ord.iOrderId FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode WHERE ord.iOrderId IN ($orderIds)");
            for ($d = 0; $d < count($OrderStatus); $d++) {
                $ordStatus = $OrderStatus[$d]['vStatus_Track'];
                $orderIds = $OrderStatus[$d]['iOrderId'];
                $orderStatusArr[$orderIds] = $ordStatus;
            }

            $getDriverRateData = $obj->MySQLSelect("SELECT SUM(vRating1) vRating1 FROM ratings_user_driver WHERE eUserType='Passenger' AND iOrderId IN ($orderIds)");
            $totalDriverRate = $driverAvgRate = 0;
            if (count($getDriverRateData) > 0) {
                $totalDriverRate = $getDriverRateData[0]['vRating1'];
            }
            if (count($data_order) > 0) {
                $driverAvgRate = $totalDriverRate / count($data_order);
            }
            $driverAvgRate = round($driverAvgRate, 1);

            for ($i = 0; $i < count($data_order); $i++) {
                $iCompanyId = $data_order[$i]['iCompanyId'];
                $Photo_Gallery_folder = $tconfig['tsite_upload_images_compnay'] . "/" . $iCompanyId . "/3_";
                if ($data_order[$i]['vImage'] != "") {
                    $data_order[$i]['vImage'] = $Photo_Gallery_folder . $data_order[$i]['vImage'];
                }
                //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
                if (isset($data_order[$i]['vDemoStoreImage']) && $data_order[$i]['vDemoStoreImage'] != "" && SITE_TYPE == "Demo") {
                    $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $data_order[$i]['vDemoStoreImage'];
                    if (file_exists($demoImgPath)) {
                        $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $data_order[$i]['vDemoStoreImage'];
                        $data_order[$i]['vImage'] = $demoImgUrl;
                    }
                }
                //echo "<pre>";print_r($Data[$i]['vImage']);die;
                //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
                if ($seviceCategoriescount > 1) {
                    $data_order[$i]['vServiceCategoryName'] = $data_order[$i]['vServiceCategoryName'];
                } else {
                    $data_order[$i]['vServiceCategoryName'] = '';
                }

                // $fNetTotal = round($fNetTotal*$Ratio,2);
                // $data_order[$i]['fNetTotal'] = $currencySymbol." ".$fNetTotal;
                $fNetTotal = $data_order[$i]['fNetTotal'];
                //$fNetTotal_Arr = getPriceUserCurrency($iUserId, $UserType, $fNetTotal, $data_order[$i]['iOrderId']);
                $fPrice = $generalobj->setTwoDecimalPoint($fNetTotal * $Ratio);

                if ($UserType == "Passenger") {
                    $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable,cu.ratio,ru.vCurrencyPassenger FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iUserId . "'";
                    $currData = $obj->MySQLSelect($sqlp);
                    $vCurrency = $currData[0]['vName'];

                    $query = "SELECT vCurrencyDriver,vCurrencyPassenger FROM `trips` WHERE iOrderId = '" . $data_order[$i]['iOrderId'] . "'";
                    $TripsData = $obj->MySQLSelect($query);

                    if (isset($data_order[$i]['fRoundingAmount']) && !empty($data_order[$i]['fRoundingAmount']) && $data_order[$i]['fRoundingAmount'] != 0 && $TripsData[0]['vCurrencyPassenger'] == $currData[0]['vCurrencyPassenger'] && $currData[0]['eRoundingOffEnable'] == "Yes") {

                        $roundingOffTotal_fare_amountArr['method'] = $data_order[$i]['eRoundingType'];
                        $roundingOffTotal_fare_amountArr['differenceValue'] = $data_order[$i]['fRoundingAmount'];

                        $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($fPrice, $data_order[$i]['fRoundingAmount'], $data_order[$i]['eRoundingType']); ////start

                        if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                            $roundingMethod = "";
                        } else {
                            $roundingMethod = "-";
                        }
                        $roundingOffTotal_fare_amount = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00";
                        $fPrice = formatNum($roundingOffTotal_fare_amount);
                    }
                }

                $data_order[$i]['fNetTotal'] = $currencySymbol . " " . $fPrice;
                //$data_order[$i]['vStatus'] = getOrderStatus($data_order[$i]['iOrderId']);
                $vStatus = "";
                if (isset($orderStatusArr[$data_order[$i]['iOrderId']])) {
                    $vStatus = $orderStatusArr[$data_order[$i]['iOrderId']];
                }
                $data_order[$i]['vStatus'] = $vStatus;
                $iStatusCode = $data_order[$i]['iStatusCode'];
                $data_order[$i]['DisplayLiveTrack'] = "Yes";
                if ($iStatusCode == 6 || $iStatusCode == 7 || $iStatusCode == 8 || $iStatusCode == 11) {
                    $data_order[$i]['DisplayLiveTrack'] = "No";
                }
                $tOrderRequestDate = $data_order[$i]['tOrderRequestDate'];
                $tOrderRequestDate = converToTz($tOrderRequestDate, $vTimeZone, $serverTimeZone);
                $data_order[$i]['tOrderRequestDate'] = $tOrderRequestDate;

                //added by SP for cubex on 01-10-2019 start
                if ($data_order[$i]['iStatusCode'] == '11' || $data_order[$i]['iStatusCode'] == '9') {
                    $status = $languageLabelsArr["LBL_HISTORY_REST_DECLINED"];
                } else if ($data_order[$i]['iStatusCode'] == '8') {
                    $status = $languageLabelsArr["LBL_HISTORY_REST_CANCELLED"];
                } else if ($data_order[$i]['iStatusCode'] == '7' && $UserType == "Passenger") {
                    $status = $languageLabelsArr["LBL_HISTORY_REST_REFUNDS"];
                } else if ($data_order[$i]['iStatusCode'] == '7' && $UserType != "Passenger") {
                    $status = $languageLabelsArr["LBL_HISTORY_REST_CANCELLED"];
                } else if ($data_order[$i]['iStatusCode'] == '6') {
                    if ($data_order[$i]['eTakeaway'] == 'Yes') {
                        $status = $languageLabelsArr["LBL_TAKE_AWAY_ORDER_PICKEDUP_TXT"];
                    } else {
                        $status = $languageLabelsArr["LBL_HISTORY_REST_DELIVERED"];
                    }
                } else if ($data_order[$i]['iStatusCode'] == '2' || $data_order[$i]['iStatusCode'] == '1') {
                    $status = $languageLabelsArr["LBL_ORDER_PLACED"];
                } else {
                    $status = '';
                }
                $data_order[$i]['vOrderStatus'] = $status;

                $data_order[$i]['vService_BG_color'] = RANDOM_COLORS_ARR[array_rand(RANDOM_COLORS_ARR, 1)];
                $data_order[$i]['vService_TEXT_color'] = "#FFFFFF";
                //added by SP for cubex on 01-10-2019 end
            }
        }
        //8 (Cancelled) = 6,8
        //2 (Inprocess) =  2,4,5
        //6 (Order Placed) = 1
        //$getOrderStatus = $obj->MySQLSelect("SELECT vStatus_" . $vLang . ",iStatusCode FROM order_status WHERE iStatusCode IN (1,6,8)");
        $inProcessStatus = array("vStatus_" . $vLang => "Inprocess", "iStatusCode" => "2");
        $returnArr['Action'] = "1";
        $returnArr['message'] = $data_order;
        /* $returnArr['orderStatusFilter'] = $getOrderStatus;
          $appTypeFilterArr = $optionArr = array();
          $optionArr[] = array("vSubFilterParam" => "", "vTitle" => $languageLabelsArr['LBL_ALL']);
          for ($d = 0; $d < count($getOrderStatus); $d++) {
          $statusArr = array();
          $statusArr['vSubFilterParam'] = $getOrderStatus[$d]['iStatusCode'];
          $statusArr['vTitle'] = $getOrderStatus[$d]['vStatus_' . $vLang];
          $optionArr[] = $statusArr;
          }
          $returnArr['subFilterOption'] = $optionArr; */
        if ($filterSelected != "All" && $filterSelected != "") {
            if ($pending > 0) {
                $filterSelected = $selPending;
            } else if ($upcoming > 0) {
                $filterSelected = $selUpcoming;
            }
        }
        if ($TotalPages > $page) {
            $returnArr['NextPage'] = "" . ($page + 1);
        } else {
            $returnArr['NextPage'] = "0";
        }
        //$returnArr['AvgRating'] = $driverAvgRate;
        $returnArr['AvgRating'] = $driverAvgRate_new;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }
    $returnArr['eFilterSel'] = $filterSelected;
    //echo "<pre>";print_r($returnArr);die;
    setDataResponse($returnArr);
}

// ############################# Display User's Active Orders ###################################################################
// ############################# Config Company Order Status  ###################################################################
if ($type == "configCompanyOrderStatus") {
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Company';
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    if ($iCompanyId != "") {
        if (!empty($isSubsToCabReq) && $isSubsToCabReq == 'true') {
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

            // Update User Location Date #
            Updateuserlocationdatetime($iMemberId, "Driver", $vTimeZone);

            // Update User Location Date #
        }
    }

    if ($iTripId != "") {
        $sql = "SELECT tMessage as msg, iStatusId FROM trip_status_messages WHERE iDriverId='" . $iMemberId . "' AND eToUserType='Driver' AND eReceived='No' ORDER BY iStatusId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    } else {
        $date = @date("Y-m-d");
        $sql = "SELECT passenger_requests.tMessage as msg  FROM passenger_requests LEFT JOIN driver_request ON  driver_request.iRequestId=passenger_requests.iRequestId  LEFT JOIN register_driver ON register_driver.iDriverId=passenger_requests.iDriverId where date_format(passenger_requests.dAddedDate,'%Y-%m-%d')= '" . $date . "' AND  passenger_requests.iDriverId=" . $iMemberId . " AND driver_request.eStatus='Timeout' AND driver_request.iDriverId='" . $iMemberId . "' AND register_driver.vTripStatus IN ('Not Active','NONE','Cancelled') ORDER BY passenger_requests.iRequestId DESC LIMIT 1 ";
        $msg = $obj->MySQLSelect($sql);
    }

    $returnArr['Action'] = "0";
    if (!empty($msg)) {
        $returnArr['Action'] = "1";
        if ($iTripId != "") {
            $updateQuery = "UPDATE trip_status_messages SET eReceived = 'Yes' WHERE iDriverId='" . $iMemberId . "'";
            $obj->sql_query($updateQuery);
            $returnArr['Action'] = "1";
            $returnArr['message'] = $msg[0]['msg'];
        } else {
            $driver_request['eStatus'] = "Received";
            $where = " iDriverId =" . $iMemberId . " and date_format(tDate,'%Y-%m-%d') = '" . $date . "' AND eStatus = 'Timeout' ";
            $obj->MySQLQueryPerform("driver_request", $driver_request, "update", $where);
            $returnArr['Action'] = "1";
            $dataArr = array();
            for ($i = 0; $i < count($msg); $i++) {
                $dataArr[$i] = $msg[$i]['msg'];
            }

            $returnArr['message'] = $dataArr;
        }
    }


    setDataResponse($returnArr);
}

// ############################# Config Company Order Status ######################################################
// ############################### Get Order States Tracking  ###################################################################
if ($type == "getOrderDeliveryLog") {
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; // Passenger, Driver , Company
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $iMemberId = 'ord.iUserId';
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
        $Ratio = $UserDetailsArr['Ratio'];
        $currencySymbol = $UserDetailsArr['currencySymbol'];
        $vLang = $UserDetailsArr['vLang'];
        $NotInStatusCode = "12";
        $fields = "concat(vName,' ',vLastName) as drivername,vImgName AS vImage";
    } else if ($UserType == "Driver") {
        $tblname = "register_driver";
        $iMemberId = 'ord.iDriverId';
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iUserId, $iOrderId);
        $Ratio = $UserDetailsArr['Ratio'];
        $currencySymbol = $UserDetailsArr['currencySymbol'];
        $vLang = $UserDetailsArr['vLang'];
        $NotInStatusCode = "12";
        $fields = "concat(vName,' ',vLastName) as drivername,vImage";
    } else {
        $tblname = "company";
        $iMemberId = 'ord.iCompanyId';
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iUserId, $iOrderId);
        $Ratio = $UserDetailsArr['Ratio'];
        $currencySymbol = $UserDetailsArr['currencySymbol'];
        $vLang = $UserDetailsArr['vLang'];
        $NotInStatusCode = "1,2,12";
        $fields = "concat(vName,' ',vLastName) as drivername,vImage";
    }


    /* $getUserImgData  = $obj->MySQLSelect("SELECT $fields FROM ".$tblname." AS ord WHERE $iMemberId='".$iUserId."'");
      $driverName = $imgaeName = "";
      if(count($getUserImgData) > 0){
      $driverName = $getUserImgData[0]['drivername'];
      $imgaeName = $getUserImgData[0]['vImage'];
      } */

    //takeaway feature start
    $orderdata = get_value('orders', 'iServiceId,eTakeaway', 'iOrderId', $iOrderId, '');
    $iServiceId = $orderdata[0]['iServiceId'];
    $eTakeaway = !empty($orderdata[0]['eTakeaway']) ? $orderdata[0]['eTakeaway'] : "No";

    if ($eTakeaway == 'Yes') {
        $NotInStatusCode .= ", 4 ,5";
    }
    //takeaway feature end

    $OrderStatusMain = $OrderStatusNotExistMain = array();
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $LBL_ITEMSLBL_ITEMS = $languageLabelsArr['LBL_ITEMSLBL_ITEMS'];
    $LBL_ITEMSLBL_ITEM = $languageLabelsArr['LBL_ITEMSLBL_ITEM'];
    $LBL_RESTAURANT_TXT = $languageLabelsArr['LBL_RESTAURANT_TXT'];
    $LBL_VEHICLE_DRIVER_TXT_FRONT = $languageLabelsArr['LBL_VEHICLE_DRIVER_TXT_FRONT'];
    $sql = "SELECT os.vStatus_" . $vLang . " as vStatus,os.vStatus_Track_" . $vLang . " as vStatus_Track,osl.dDate,osl.iStatusCode,ord.iUserId,ord.iCompanyId,ord.iDriverId,ord.iStatusCode as OrderCurrentStatusCode,ord.iUserAddressId,ord.vOrderNo,ord.tOrderRequestDate,ord.fNetTotal,ord.iOrderId,ord.vImageDeliveryPref,selectedPreferences,os.eTakeaway FROM order_status_logs as osl LEFT JOIN order_status as os ON osl.iStatusCode = os.iStatusCode LEFT JOIN orders as ord ON osl.iOrderId=ord.iOrderId WHERE osl.iOrderId = " . $iOrderId . " AND osl.iStatusCode NOT IN(" . $NotInStatusCode . ") ORDER BY osl.iStatusCode ASC";
    $OrderStatusMain = $obj->MySQLSelect($sql);

    $eDisplayDottedLine = "No";
    $eDisplayRouteLine = "No";
    if (count($OrderStatusMain) > 0) {
        $returnArr['Action'] = "1";
        $UserSelectedAddressArr = GetUserAddressDetail($OrderStatusMain[0]['iUserId'], "Passenger", $OrderStatusMain[0]['iUserAddressId']);
        $sql = "SELECT concat(vName,' ',vLastName) as drivername,vImage from  register_driver WHERE iDriverId ='" . $OrderStatusMain[0]['iDriverId'] . "'";
        $driverdetail = $obj->MySQLSelect($sql);
        $drivername = $driverdetail[0]['drivername'];
        $imgaeName = empty($driverdetail[0]['vImage']) ? "" : $driverdetail[0]['vImage'];
        if ($drivername == "" || $drivername == NULL) {
            //$drivername = "Delivery Driver";
            $drivername = $LBL_VEHICLE_DRIVER_TXT_FRONT;
        }
        $OrderPickedUpDate =$OrderStatusCode= "";
        $CheckOtherStatusCode = "Yes";
        $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,fPrepareTime";
        $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $OrderStatusMain[0]['iCompanyId']);

        $serverTimeZone = date_default_timezone_get();
        for ($i = 0; $i < count($OrderStatusMain); $i++) {

            //takeaway feature start
            if ($OrderStatusMain[$i]['iStatusCode'] == 6 || $OrderStatusMain[$i]['iStatusCode'] == 2) {
                $ordtakeaway = !empty($OrderStatusMain[$i]['eTakeaway']) ? $OrderStatusMain[$i]['eTakeaway'] : "No";
                if ($eTakeaway == 'Yes' && $ordtakeaway == "No") {
                    continue;
                }
                if ($eTakeaway == 'No' && $ordtakeaway == "Yes") {
                    continue;
                }
            }

            $OrderStatus[$i] = $OrderStatusMain[$i];
            //takeaway feature end

            $OrderStatusCode .= $OrderStatus[$i]['iStatusCode'] . ",";
            $dDate = $OrderStatus[$i]['dDate'];
            $dDate = converToTz($dDate, $vTimeZone, $serverTimeZone);
            $OrderStatus[$i]['dDate'] = $dDate;
            $OrderStatus[$i]['driverName'] = $drivername;
            $OrderStatus[$i]['driverImage'] = $imgaeName;
            $iStatusCode = $OrderStatus[0]['OrderCurrentStatusCode'];
            if ($iStatusCode == 1 || $iStatusCode == 2 || $iStatusCode == 8 || $iStatusCode == 8) {
                $eDisplayDottedLine = "Yes";
                $eDisplayRouteLine = "No";
            }
            if ($iStatusCode == 5) {
                $eDisplayDottedLine = "No";
                $eDisplayRouteLine = "Yes";
                $OrderPickedUpDate = $OrderStatus[$i]['dDate'];
            }
            $OrderStatus[$i]['eShowCallImg'] = "No";
            $StatusCodeLogwise = $OrderStatus[$i]['iStatusCode'];
            if ($StatusCodeLogwise == 5) {
                $OrderStatus[$i]['eShowCallImg'] = "Yes";
            }

            $OrderStatus[$i]['vStatus_Track'] = str_replace("#DriverName#", $drivername, $OrderStatus[$i]['vStatus_Track']);
            $OrderStatus[$i]['vStatus_Track'] = str_replace("#STORE#", $LBL_RESTAURANT_TXT, $OrderStatus[$i]['vStatus_Track']);
            $OrderStatus[$i]['vStatus'] = str_replace("#STORE#", $LBL_RESTAURANT_TXT, $OrderStatus[$i]['vStatus']);
            $OrderStatus[$i]['eCompleted'] = "Yes";
            if ($iStatusCode == 8 || $iStatusCode == 9) {
                $CheckOtherStatusCode = "No";
            }

            if (isDeliveryPreferenceEnable()) {
                if ($OrderStatus[$i]['iStatusCode'] == 6) {
                    $OrderStatus[$i]['isPrefrenceImageUploaded'] = 'No';
                    if ($OrderStatus[$i]['vImageDeliveryPref'] != "") {
                        $OrderStatus[$i]['isPrefrenceImageUploaded'] = 'Yes';
                        $OrderStatus[$i]['vImageDeliveryPref'] = $tconfig['tsite_upload_order_delivery_pref_images'] . $OrderStatus[$i]['vImageDeliveryPref'];
                    } else {
                        $OrderStatus[$i]['vImageDeliveryPref'] = "";
                    }
                }
            }
        }

        if ($CheckOtherStatusCode == "Yes" && $UserType == "Passenger") {
            //$OrderStatusCode = substr($OrderStatusCode, 0, -1);
            $OrderStatusCode = trim($OrderStatusCode, ",");
            $OrderStatusCode = $OrderStatusCode . ",7,8,9,11,12";
            if ($eTakeaway == 'Yes') {
                $OrderStatusCode .= ", 4 ,5";
            }
            $OrderStatusCode = trim($OrderStatusCode, ",");
            $sql = "SELECT vStatus_" . $vLang . " as vStatus,vStatus_Track_" . $vLang . " as vStatus_Track,iStatusCode,eTakeaway FROM order_status WHERE iStatusCode NOT IN(" . $OrderStatusCode . ") ORDER BY iDisplayOrder ASC";
            $OrderStatusNotExistMain = $obj->MySQLSelect($sql);

            for ($i = 0; $i < count($OrderStatusNotExistMain); $i++) {

                if ($OrderStatusNotExistMain[$i]['iStatusCode'] == 6 || $OrderStatusNotExistMain[$i]['iStatusCode'] == 2) {
                    $ordtakeaway = !empty($OrderStatusNotExistMain[$i]['eTakeaway']) ? $OrderStatusNotExistMain[$i]['eTakeaway'] : "No";
                    if ($eTakeaway == 'Yes' && $ordtakeaway == "No") {
                        continue;
                    }
                    if ($eTakeaway == 'No' && $ordtakeaway == "Yes") {
                        continue;
                    }
                }
                $OrderStatusNotExist[$i] = $OrderStatusNotExistMain[$i];
                $OrderStatusNotExist[$i]['vStatus'] = $OrderStatusNotExist[$i]['vStatus'];
                $OrderStatusNotExist[$i]['vStatus_Track'] = str_replace("#DriverName#", $drivername, $OrderStatusNotExist[$i]['vStatus_Track']);
                $OrderStatusNotExist[$i]['vStatus_Track'] = str_replace("#STORE#", $LBL_RESTAURANT_TXT, $OrderStatusNotExist[$i]['vStatus_Track']);
                $OrderStatusNotExist[$i]['vStatus'] = str_replace("#STORE#", $LBL_RESTAURANT_TXT, $OrderStatusNotExist[$i]['vStatus']);
                $OrderStatusNotExist[$i]['dDate'] = "";
                $OrderStatusNotExist[$i]['iStatusCode'] = $OrderStatusNotExist[$i]['iStatusCode'];
                $OrderStatusNotExist[$i]['iUserId'] = $OrderStatus[0]['iUserId'];
                $OrderStatusNotExist[$i]['iCompanyId'] = $OrderStatus[0]['iCompanyId'];
                $OrderStatusNotExist[$i]['iDriverId'] = $OrderStatus[0]['iDriverId'];
                $OrderStatusNotExist[$i]['OrderCurrentStatusCode'] = $OrderStatus[0]['OrderCurrentStatusCode'];
                $OrderStatusNotExist[$i]['iUserAddressId'] = $OrderStatus[0]['iUserAddressId'];
                $OrderStatusNotExist[$i]['vOrderNo'] = $OrderStatus[0]['vOrderNo'];
                $OrderStatusNotExist[$i]['tOrderRequestDate'] = $OrderStatus[0]['tOrderRequestDate'];
                $OrderStatusNotExist[$i]['fNetTotal'] = $OrderStatus[0]['fNetTotal'];
                $OrderStatusNotExist[$i]['eShowCallImg'] = $OrderStatus[0]['eShowCallImg'];
                $OrderStatusNotExist[$i]['eCompleted'] = "No";
                array_push($OrderStatus, $OrderStatusNotExist[$i]);
            }
        }
        foreach ($OrderStatus as $k => $v) {
            $Data_name['iStatusCode'][$k] = $v['iStatusCode'];
        }
        array_multisort($Data_name['iStatusCode'], SORT_ASC, $OrderStatus); //Added By HJ ON 3-1-2019 For Sort BY iStatusCode

        $returnArr['message'] = $OrderStatus;
        $fNetTotal = $OrderStatus[0]['fNetTotal'];
        $fNetTotal = round($fNetTotal * $Ratio, 2);
        $returnArr['fNetTotal'] = $currencySymbol . " " . formatnum($fNetTotal);
        $returnArr['vOrderNo'] = $OrderStatus[0]['vOrderNo'];
        $TotalOrderItems = getTotalOrderDetailItemsCount($iOrderId);
        $returnArr['TotalOrderItems'] = ($TotalOrderItems > 1) ? $TotalOrderItems . " " . $LBL_ITEMSLBL_ITEMS : $TotalOrderItems . " " . $LBL_ITEMSLBL_ITEM;
        $tOrderRequestDate = $OrderStatus[0]['tOrderRequestDate'];
        $tOrderRequestDate = converToTz($tOrderRequestDate, $vTimeZone, $serverTimeZone);
        $returnArr['tOrderRequestDate'] = $tOrderRequestDate;
        $returnArr['OrderCurrentStatusCode'] = $OrderStatus[0]['OrderCurrentStatusCode'];
        $returnArr['PassengerLat'] = empty($UserSelectedAddressArr['vLatitude']) ? "" : $UserSelectedAddressArr['vLatitude'];
        $returnArr['PassengerLong'] = empty($UserSelectedAddressArr['vLongitude']) ? "" : $UserSelectedAddressArr['vLongitude'];
        $returnArr['DeliveryAddress'] = empty($UserSelectedAddressArr['UserAddress']) ? "" : $UserSelectedAddressArr['UserAddress'];
        $returnArr['vCompany'] = $Data_cab_requestcompany[0]['vCompany'];
        $returnArr['CompanyLat'] = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
        $returnArr['CompanyLong'] = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
        $returnArr['CompanyAddress'] = $Data_cab_requestcompany[0]['vRestuarantLocation'];
        $returnArr['iDriverId'] = $OrderStatus[0]['iDriverId'];
        $returnArr['eDisplayDottedLine'] = $eDisplayDottedLine;
        $returnArr['eDisplayRouteLine'] = $eDisplayRouteLine;
        $returnArr['OrderPickedUpDate'] = $OrderPickedUpDate;
        $returnArr['iServiceId'] = $iServiceId;
        if ($OrderStatus[0]['iDriverId'] > 0) {
            $Data_cab_driverlatlong = get_value('register_driver', 'vLatitude,vLongitude,vCode,vPhone', 'iDriverId', $OrderStatus[0]['iDriverId']);
            $returnArr['DriverLat'] = $Data_cab_driverlatlong[0]['vLatitude'];
            $returnArr['DriverLong'] = $Data_cab_driverlatlong[0]['vLongitude'];
            $returnArr['DriverPhone'] = '+' . $Data_cab_driverlatlong[0]['vCode'] . $Data_cab_driverlatlong[0]['vPhone'];
        } else {
            $returnArr['DriverLat'] = "";
            $returnArr['DriverLong'] = "";
            $returnArr['DriverPhone'] = "";
        }

        if (isDeliveryPreferenceEnable()) {
            $selectedPreferences = $OrderStatus[0]['selectedPreferences'];
            $deliveryPrefSql = "SELECT eContactLess FROM delivery_preferences WHERE iPreferenceId IN (" . $selectedPreferences . ")";
            $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);

            $returnArr['isContactLessDeliverySelected'] = 'No';
            foreach ($deliveryPrefSqlData as $value) {
                if ($value['eContactLess'] == 'Yes') {
                    $returnArr['isContactLessDeliverySelected'] = 'Yes';
                }
            }
        }

        $returnArr['eTakeAway'] = $eTakeaway;
        if ($eTakeaway == 'Yes' && $OrderStatus[0]['OrderCurrentStatusCode'] == 2) {
            $preparetimedata = $languageLabelsArr['LBL_REST_PREPARATION_TIME'] . " " . $Data_cab_requestcompany[0]['fPrepareTime'] . " " . $languageLabelsArr['LBL_MINUTES_TXT'];
            $returnArr['prepareTime'] = $preparetimedata;
        }

        $returnArr['eTakeAwayPickedUpNote'] = "";
        if ($eTakeaway == 'Yes' && $OrderStatus[0]['OrderCurrentStatusCode'] == 6) {
            $returnArr['eTakeAwayPickedUpNote'] = str_replace('#RESTAURANT_NAME#', $Data_cab_requestcompany[0]['vCompany'], $languageLabelsArr['LBL_TAKE_AWAY_ORDER_NOTE']);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    }



    setDataResponse($returnArr);
}

// ############################### Get Order States Tracking  ###################################################################
// ###################### start getOrderHistory #############################
if ($type == "getOrderHistory") {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iGeneralUserId = isset($_REQUEST["iGeneralUserId"]) ? $_REQUEST["iGeneralUserId"] : "";
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : "Company";
    $vFromDate = isset($_REQUEST["vFromDate"]) ? $_REQUEST["vFromDate"] : "";
    $vToDate = isset($_REQUEST["vToDate"]) ? $_REQUEST["vToDate"] : "";
    $vSubFilterParam = isset($_REQUEST["vSubFilterParam"]) ? $_REQUEST["vSubFilterParam"] : "6";
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : "Asia/Kolkata";
    $systemTimeZone = date_default_timezone_get();

    $vConvertFromDate = converToTz($vFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $vConvertToDate = '';
    if (!empty($vToDate)) {
        $vConvertToDate = converToTz($vToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    }

    if ($UserType == 'Driver') {
        $conditonalFields = 'iDriverId';
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iGeneralUserId);
    } else if ($UserType == 'Passenger') {
        $conditonalFields = 'iUserId';
        $UserDetailsArr = getUserCurrencyLanguageDetails($iGeneralUserId);
    } else {
        $conditonalFields = 'iCompanyId';
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iGeneralUserId);
    }
    $filterSelected = $vSubFilterParam;
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];

    $enable_takeaway = 0;
    if (isTakeAwayEnable() && $UserType != 'Driver') {
        $enable_takeaway = 1;
    }

    // $priceRatio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $per_page = 10;
    $whereFilter = "";
    if ($vFromDate != "") {
        $whereFilter = "DATE(tOrderRequestDate) = '" . $vFromDate . "' AND ";
    }
    if ($vFromDate != "" && $vConvertToDate != "") {
        $whereFilter = "(DATE(tOrderRequestDate) BETWEEN '$vConvertFromDate' AND '$vConvertToDate') AND ";
    }
    $whereStatusCode = "AND  `iStatusCode` IN (6, 7, 8, 11)";
    if ($vSubFilterParam != "") {
        if ($vSubFilterParam == '6-1' && $enable_takeaway == 1) {
            $whereStatusCode = " AND ord.iStatusCode IN (6) AND ord.eTakeaway = 'Yes'";
        } else if ($vSubFilterParam == '6' && $enable_takeaway == 1) {
            $whereStatusCode = " AND ord.iStatusCode IN ($vSubFilterParam) AND ord.eTakeaway = 'No'";
        } else if ($vSubFilterParam == 8) {
            $whereStatusCode = "AND  `iStatusCode` IN (7, 8)";
        } else {
            $whereStatusCode = "AND  `iStatusCode` IN ($vSubFilterParam)";
        }
    }
    $sql_all = "SELECT COUNT(iOrderId) As TotalIds FROM orders WHERE $whereFilter $conditonalFields='$iGeneralUserId' $whereStatusCode";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    //$sql = "SELECT *,vOrderNo, iCompanyId,iDriverId,iUserAddressId,vCompany,iOrderId, tOrderRequestDate, iUserId, fNetTotal, fTotalGenerateFare, fCommision, fOffersDiscount, fDeliveryCharge, iStatusCode, fRatio_" . $currencycode . " as Ratio, fRestaurantPaidAmount, fDriverPaidAmount,eRestaurantPaymentStatus,eAdminPaymentStatus  FROM `orders` WHERE $whereFilter $conditonalFields='$iGeneralUserId' $whereStatusCode ORDER BY tOrderRequestDate DESC " . $limit;

    $sql = "SELECT ord.vOrderNo, ord.iCompanyId,ord.iDriverId, ord.iUserAddressId, ord.vCompany, ord.iOrderId, ord.tOrderRequestDate, ord.iUserId, ord.fNetTotal, ord.fTotalGenerateFare, ord.fCommision, ord.fOffersDiscount, ord.fDeliveryCharge, ord.iStatusCode, ord.fRatio_" . $currencycode . " as Ratio, ord.fRestaurantPaidAmount, ord.fDriverPaidAmount, ord.eRestaurantPaymentStatus, ord.eAdminPaymentStatus,sc.vServiceName_" . $vLang . " as vServiceCategoryName,ord.eTakeaway FROM `orders` as ord LEFT JOIN service_categories as sc on sc.iServiceId=ord.iServiceId WHERE $whereFilter $conditonalFields='$iGeneralUserId' $whereStatusCode ORDER BY tOrderRequestDate DESC " . $limit;
    //added by SP on 30-09-2019 for cubex to get service category name as per HJ added on DisplayActiveOrder 
    $Data = $obj->MySQLSelect($sql);
    $existingArr = $storeIdArr = $newdata = $storeImageArr = $addressIdArr = $orderAddressArr = $driverRateArr = $orderIdArr = Array();
    $count = $totalOrder = 0;
    $sql_whole = "SELECT ord.vOrderNo, ord.iCompanyId,ord.iDriverId, ord.iUserAddressId, ord.vCompany, ord.iOrderId, ord.tOrderRequestDate, ord.iUserId, ord.fNetTotal, ord.fTotalGenerateFare, ord.fCommision, ord.fOffersDiscount, ord.fDeliveryCharge, ord.iStatusCode, ord.fRatio_" . $currencycode . " as Ratio, ord.fRestaurantPaidAmount, ord.fDriverPaidAmount, ord.eRestaurantPaymentStatus, ord.eAdminPaymentStatus,sc.vServiceName_" . $vLang . " as vServiceCategoryName FROM `orders` as ord LEFT JOIN service_categories as sc on sc.iServiceId=ord.iServiceId WHERE $whereFilter $conditonalFields='$iGeneralUserId' $whereStatusCode ORDER BY tOrderRequestDate DESC ";
    $Data_whole = $obj->MySQLSelect($sql_whole);
    for ($d = 0; $d < count($Data_whole); $d++) {
        $storeIdArr[] = $Data_whole[$d]['iCompanyId'];
        $addressIdArr[] = $Data_whole[$d]['iUserAddressId'];
        $orderIdArr[] = $Data_whole[$d]['iOrderId'];
        $totalOrder += 1;
    }
    $takeaway_orderstaus = '';
    if ($enable_takeaway == 0) {
        $takeaway_orderstaus = " AND eTakeaway != 'Yes'";
    }
    $subStatusArr = array();
    $getOrderStatus = $obj->MySQLSelect("SELECT vStatus_" . $vLang . " As vTitle,iStatusCode,eTakeaway FROM order_status WHERE iStatusCode IN (6,8)" . $takeaway_orderstaus);
    if ($UserType == "Driver" || $UserType == "Company") {
        $optionArr = array();
        $optionArr['vSubFilterParam'] = "";
        $optionArr['vTitle'] = $languageLabelsArr['LBL_ALL']; //added by SP on cubex design for 01-10-2019
        $subStatusArr[] = $optionArr;
        //print_R($getOrderStatus);die;
        for ($s = 0; $s < count($getOrderStatus); $s++) {
            if ($getOrderStatus[$s]['iStatusCode'] == 6 && $getOrderStatus[$s]['eTakeaway'] == 'Yes' && $enable_takeaway == 1) {
                $optionArr['vSubFilterParam'] = "6-1";
            } else {
                $optionArr['vSubFilterParam'] = $getOrderStatus[$s]['iStatusCode'];
            }
            //$optionArr['vSubFilterParam'] = $getOrderStatus[$s]['iStatusCode'];
            $optionArr['vTitle'] = $getOrderStatus[$s]['vTitle'];
            $subStatusArr[] = $optionArr;
        }
    }
    $returnData['eFilterSel'] = $filterSelected;
    $returnData['TotalOrder'] = strval($totalOrder);
    $returnData['subFilterOption'] = $subStatusArr;
    if (count($Data) > 0) {
        if (count($storeIdArr) > 0) {
            $storeIds = implode($storeIdArr, ",");
            $addressIds = implode($addressIdArr, ",");
            $orderIds = implode($orderIdArr, ",");
            /* $getCompanyData = $obj->MySQLSelect("SELECT vImage,iCompanyId FROM company WHERE iCompanyId IN ($storeIds)");
              for ($c = 0; $c < count($getCompanyData); $c++) {
              $storeImageArr[$getCompanyData[$c]['iCompanyId']] = $getCompanyData[$c]['vImage'];
              } */
            $getDeliveryAddress = $obj->MySQLSelect("SELECT iUserAddressId,vServiceAddress FROM user_address WHERE iUserAddressId IN ($addressIds) AND eUserType='Rider'");
            for ($a = 0; $a < count($getDeliveryAddress); $a++) {
                $orderAddressArr[$getDeliveryAddress[$a]['iUserAddressId']] = $getDeliveryAddress[$a]['vServiceAddress'];
            }
        }
        if ($UserType == "" || $UserType == NULL) {
            $UserType = "Company";
        }

        //$getDriverRateData = $obj->MySQLSelect("SELECT SUM(vRating1) vRating1 FROM ratings_user_driver WHERE eUserType='Passenger' AND iOrderId IN ($orderIds)";)
        if ($UserType == 'Driver') {
            $getDriverRateData = $obj->MySQLSelect("SELECT SUM(vRating1) vRating1 FROM ratings_user_driver WHERE eFromUserType='Passenger' AND eToUserType='Driver' AND iOrderId IN ($orderIds)");
        } else if ($UserType == 'Company') {
            //echo "SELECT SUM(vRating1) vRating1 FROM ratings_user_driver WHERE eFromUserType='Passenger' AND eToUserType='Company' AND iOrderId IN ($orderIds)";exit;
            $getDriverRateData = $obj->MySQLSelect("SELECT SUM(vRating1) vRating1 FROM ratings_user_driver WHERE eFromUserType='Passenger' AND eToUserType='Company' AND iOrderId IN ($orderIds)");
        }
        $totalDriverRate = $driverAvgRate = 0;
        if (count($getDriverRateData) > 0) {
            $totalDriverRate = $getDriverRateData[0]['vRating1'];
        }

        if ($totalOrder > 0) {
            $driverAvgRate = $totalDriverRate / $totalOrder;
        }
        $driverAvgRate = round($driverAvgRate, 2);
        //echo $driverAvgRate;exit;
        //echo "<pre>";print_r($orderAddressArr);die;
        //echo "<pre>";print_r($getDriverRateData);die;
        $imgUrl = $tconfig['tsite_upload_images_compnay'];
        //$seviceCategoriescount = getServiceCategoryCounts();//added by SP on 30-09-2019 for cubex to get service category name as per HJ added on DisplayActiveOrder //commented bc for grocery it takes from conf file not from db..bc in table all entries are there..
        $seviceCategoriescount = count($service_categories_ids_arr);
        for ($i = 0; $i < count($Data); $i++) {
            $priceRatio = $Data[$i]['Ratio'];
            $date = converToTz($Data[$i]['tOrderRequestDate'], $vTimeZone, $systemTimeZone, "Y-m-d H:i:s");
            $OrderTime = date('h:i A', strtotime($date));
            $OrderTimeNew = date('d M Y', strtotime($date));
            $dateName = get_day_name(strtotime($date));
            if (array_key_exists($dateName, $existingArr)) {
                continue;
            }

            $odata[$count]['vDate'] = $dateName;
            $existingArr[$dateName] = "Yes";
            $subDataCount = 0;
            for ($j = 0; $j < count($Data); $j++) {
                $date_tmp = converToTz($Data[$j]['tOrderRequestDate'], $vTimeZone, $systemTimeZone, "Y-m-d H:i:s");
                $dateName_tmp = get_day_name(strtotime($date_tmp));
                if ($dateName == $dateName_tmp) {
                    $storeImg = $delAddress = "";
                    /* if (isset($storeImageArr[$Data[$j]['iCompanyId']])) {
                      $storeImg = $storeImageArr[$Data[$j]['iCompanyId']];
                      } */
                    if (isset($orderAddressArr[$Data[$j]['iUserAddressId']])) {
                        $delAddress = $orderAddressArr[$Data[$j]['iUserAddressId']];
                    }
                    //echo "<pre>";print_r($orderAddressArr);die;
                    $date_j = converToTz($Data[$j]['tOrderRequestDate'], $vTimeZone, $systemTimeZone, "Y-m-d H:i:s");
                    $OrderTime_j = date('d M, Y h:i A', strtotime($date_j)); //h:iA
                    $OrderTimeNew_j = date('d M Y', strtotime($date_j));
                    $uniquedate = date('jnY', strtotime($date_j));
                    $odata[$count]['Data'][$subDataCount]['iUniqueId'] = $uniquedate;
                    $odata[$count]['Data'][$subDataCount]['iOrderId'] = $Data[$j]['iOrderId'];
                    $odata[$count]['Data'][$subDataCount]['vOrderNo'] = $Data[$j]['vOrderNo'];
                    $odata[$count]['Data'][$subDataCount]['iStatusCode'] = $Data[$j]['iStatusCode'];
                    $odata[$count]['Data'][$subDataCount]['vCompany'] = $Data[$j]['vCompany'];
                    $odata[$count]['Data'][$subDataCount]['tOrderRequestDate_Org'] = $date_j;
                    //$odata[$count]['Data'][$subDataCount]['vImage'] = $imgUrl . "/" . $Data[$j]['iCompanyId'] . "/" . $storeImg;
                    $odata[$count]['Data'][$subDataCount]['vUserAddress'] = $delAddress;

                    //added by SP on 30-09-2019 for  cubex to get service category name as per HJ added on DisplayActiveOrder start
                    if ($seviceCategoriescount > 1) {
                        $odata[$count]['Data'][$subDataCount]['vServiceCategoryName'] = $Data[$j]['vServiceCategoryName'];
                    } else {
                        $odata[$count]['Data'][$subDataCount]['vServiceCategoryName'] = '';
                    }
                    //added by SP on 30-09-2019 for  cubex to get service category name as per HJ added on DisplayActiveOrder end
                    //added by SP on 30-09-2019 for  cubex to get payment option and itemlist start
                    $db_orders = DisplayOrderDetailList($Data[$j]['iOrderId'], $vTimeZone, $UserType);
                    $odata[$count]['Data'][$subDataCount]['ePaymentOption'] = $db_orders[0]['ePaymentOption'];
                    $odata[$count]['Data'][$subDataCount]['itemlist'] = $db_orders[0]['itemlist'];

                    $ratingStore = 0;
                    if ($UserType == 'Driver') {
                        $getUserToCompanyRateData = $obj->MySQLSelect("SELECT SUM(vRating1) vRating1 FROM ratings_user_driver WHERE eFromUserType='Passenger' AND eToUserType='Driver' AND iOrderId = " . $Data[$j]['iOrderId']);
                    } else if ($UserType == 'Company') {
                        $getUserToCompanyRateData = $obj->MySQLSelect("SELECT SUM(vRating1) vRating1 FROM ratings_user_driver WHERE eFromUserType='Passenger' AND eToUserType='Company' AND iOrderId = " . $Data[$j]['iOrderId']);
                    }
                    if (!empty($getUserToCompanyRateData[0]['vRating1']))
                        $ratingStore = $getUserToCompanyRateData[0]['vRating1'];
                    $odata[$count]['Data'][$subDataCount]['vAvgRating'] = $ratingStore;
                    //added by SP on 30-09-2019 for  cubex to get payment option and itemlist end

                    $odata[$count]['Data'][$subDataCount]['vCompany'] = $Data[$j]['vCompany'];

                    //$odata[$count]['Data'][$subDataCount]['tOrderRequestDate'] = $storeImageArr;
                    $query1 = "SELECT vName,vLastName,vImgName FROM register_user WHERE iUserId = '" . $Data[$j]['iUserId'] . "'";
                    $orderDetail = $obj->MySQLSelect($query1);
                    $odata[$count]['Data'][$subDataCount]['UseName'] = $orderDetail[0]['vName'] . " " . $orderDetail[0]['vLastName'];

                    if (!empty($orderDetail[0]['vImgName'])) {
                        $odata[$count]['Data'][$subDataCount]['vImage'] = $tconfig["tsite_upload_images_passenger"] . "/" . $Data[$j]['iUserId'] . "/" . $orderDetail[0]['vImgName'];
                    } else {
                        $odata[$count]['Data'][$subDataCount]['vImage'] = $tconfig["tsite_img"] . "/profile-user-img.png";
                    }

                    $query = "SELECT iOrderDetailId FROM order_details WHERE iOrderId = '" . $Data[$j]['iOrderId'] . "'";
                    $orderDetailId = $obj->MySQLSelect($query);
                    $odata[$count]['Data'][$subDataCount]['TotalItems'] = strval(count($orderDetailId));
                    if ($Data[$j]['iStatusCode'] == '11' || $Data[$j]['iStatusCode'] == '9') {
                        $status = $languageLabelsArr["LBL_HISTORY_REST_DECLINED"];
                    } else if ($Data[$j]['iStatusCode'] == '8') {
                        $status = $languageLabelsArr["LBL_HISTORY_REST_CANCELLED"];
                    } else if ($Data[$j]['iStatusCode'] == '7' && $UserType == "Passenger") {
                        $status = $languageLabelsArr["LBL_HISTORY_REST_REFUNDS"];
                    } else if ($Data[$j]['iStatusCode'] == '7' && $UserType != "Passenger") {
                        $status = $languageLabelsArr["LBL_HISTORY_REST_CANCELLED"];
                    } else if ($Data[$j]['iStatusCode'] == '6') {
                        if ($Data[$j]['eTakeaway'] == 'Yes') {
                            $status = $languageLabelsArr["LBL_TAKE_AWAY_ORDER_PICKEDUP_TXT"];
                        } else {
                            $status = $languageLabelsArr["LBL_HISTORY_REST_DELIVERED"];
                        }
                    } else {
                        $status = '';
                    }

                    $odata[$count]['Data'][$subDataCount]['iStatus'] = $status;
                    if ($UserType == 'Driver') {
                        $OrderId = $Data[$j]['iOrderId'];
                        $subquery = "SELECT fDeliveryCharge,eDriverPaymentStatus FROM trips WHERE iOrderId = '" . $OrderId . "'";
                        $DriverCharge = $obj->MySQLSelect($subquery);
                        if ($Data[$j]['iStatusCode'] == '7' || $Data[$j]['iStatusCode'] == '8') {
                            $EarningFare = $Data[$j]['fDriverPaidAmount'];
                        } else {
                            $EarningFare = $DriverCharge[0]['fDeliveryCharge'];
                        }
                    } else if ($UserType == 'Passenger') {
                        $EarningFare = $Data[$j]['fNetTotal'];
                    } else {
                        if ($Data[$j]['iStatusCode'] == '7' || $Data[$j]['iStatusCode'] == '8') {
                            $EarningFare = $Data[$j]['fRestaurantPaidAmount'];
                        } else {
                            $EarningFare = $Data[$j]['fTotalGenerateFare'] - ($Data[$j]['fCommision'] + $Data[$j]['fOffersDiscount'] + $Data[$j]['fDeliveryCharge']);
                        }
                    }
                    $returnArr['fTotalGenerateFare'] = $generalobj->setTwoDecimalPoint($EarningFare) * $priceRatio;
                    $fTotalGenerateFare = formatNum($returnArr['fTotalGenerateFare']);
                    if ($fTotalGenerateFare == 0) {
                        $odata[$count]['Data'][$subDataCount]['EarningFare'] = '';
                    } else {
                        $odata[$count]['Data'][$subDataCount]['EarningFare'] = $vSymbol . $fTotalGenerateFare;
                    }

                    $odata[$count]['Data'][$subDataCount]['vService_BG_color'] = RANDOM_COLORS_ARR[array_rand(RANDOM_COLORS_ARR, 1)];
                    $odata[$count]['Data'][$subDataCount]['vService_TEXT_color'] = "#FFFFFF";
                    $subDataCount++;
                }
            }

            $count++;

            // $i++;
        }

        $returnData['message'] = $odata;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = "" . ($page + 1);
        } else {
            $returnData['NextPage'] = "0";
        }
        if ($UserType == 'Driver') {
            $totalEarning = OrderTotalEarningForDriver($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType, $vTimeZone, $vSubFilterParam);
        } else if ($UserType == 'Passenger') {
            $totalEarning = OrderTotalEarningForPassanger($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType, $vTimeZone);
        } else {
            $totalEarning = OrderTotalEarningForRestaurant($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType, $vTimeZone, $vSubFilterParam);
        }
        $returnData['TotalEarning'] = $vSymbol . $totalEarning;
        //$returnData['AvgRating'] = $generalobj->setTwoDecimalPoint($driverAvgRate);
        $returnData['AvgRating'] = number_format(floatval($driverAvgRate), 1, ".", "");
        $returnData['Action'] = "1";
        setDataResponse($returnData);
    } else {
        $returnData['Action'] = "0";
        $totalEarning = "0";
        $returnData['AvgRating'] = number_format(floatval($driverAvgRate), 1, ".", "");
        $returnData['TotalEarning'] = $vSymbol . $totalEarning;
        $returnData['message'] = "LBL_NO_DATA_AVAIL";
        setDataResponse($returnData);
    }
}

// ###############################End getOrderHistory###########################################
// ##################################START FOOD MENU ITEM FOR RESTAURANT########################################
if ($type == "ManageFoodItem") {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iGeneralUserId = isset($_REQUEST["iGeneralUserId"]) ? $_REQUEST["iGeneralUserId"] : "";
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : "Asia/Kolkata";
    $SearchWord = isset($_REQUEST["SearchWord"]) ? $_REQUEST["SearchWord"] : "";
    $UserDetailsArr = getCompanyCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];
    $priceRatio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $per_page = 10;
    $sql_all = "SELECT COUNT(m.iMenuItemId) As TotalIds FROM food_menu as f LEFT JOIN menu_items as m on  m.iFoodMenuId=f.iFoodMenuId WHERE f.iCompanyId='" . $iGeneralUserId . "' AND m.eStatus!='Deleted'";
    $data_count_all = $obj->MySQLSelect($sql_all);
    $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    $query = "SELECT fm.iFoodMenuId, fm.iFoodMenuId, fm.vMenu_" . $vLang . " as catName, fm.vMenuDesc_" . $vLang . " as catDesc, mt.eAvailable FROM food_menu as fm, menu_items as mt WHERE fm.iCompanyId = '$iGeneralUserId' AND fm.eStatus = 'Active' AND mt.eStatus = 'Active' AND mt.iFoodMenuId = fm.iFoodMenuId GROUP BY mt.iFoodMenuId ORDER BY fm.iDisplayOrder ASC";
    $Data = $obj->MySQLSelect($query);
    //echo "<pre>";print_r($Data);die;
    $i = 0;
    $foodItemIds = "";
    $itemCatDataArr = $getItemData = array();
    foreach ($Data as $key => $value) {
        $iFoodMenuId = $value['iFoodMenuId'];
        $foodItemIds .= "," . $iFoodMenuId;
    }
    if ($foodItemIds != "") {
        $trimData = trim($foodItemIds, ",");
        $ssql = '';
        if (!empty($SearchWord))
            $ssql = " AND vItemType_" . $vLang . " Like '%$SearchWord%'";
        $getItemData = $obj->MySQLSelect("SELECT iFoodMenuId,iMenuItemId,vImage,vItemType_" . $vLang . " as menuitemname, vItemDesc_" . $vLang . " as menuitemdesc, fPrice, eFoodType,eAvailable FROM menu_items WHERE iFoodMenuId IN ($trimData) and eStatus = 'Active' $ssql ORDER BY iDisplayOrder DESC");
    }
    //echo "<pre>";print_r($getItemData);die;
    for ($r = 0; $r < count($getItemData); $r++) {
        $foodItemId = $getItemData[$r]['iFoodMenuId'];
        $itemCatDataArr[$foodItemId][] = $getItemData[$r];
    }
    //echo "<pre>";print_r($itemCatDataArr);die;
    if (count($Data) > 0) {
        foreach ($Data as $key => $value) {
            $CategoryData[$i]['CategoryName'] = $value['catName'];
            $iFoodMenuId = $value['iFoodMenuId'];
            // $subQuery = "SELECT iMenuItemId,vItemType_".$vLang." as menuitemname, vItemDesc_".$vLang." as menuitemdesc, fPrice, eFoodType,eAvailable FROM menu_items WHERE iFoodMenuId = '".$iFoodMenuId."' ORDER BY iDisplayOrder DESC". $limit;
            //$subQuery = "SELECT iMenuItemId,vItemType_" . $vLang . " as menuitemname, vItemDesc_" . $vLang . " as menuitemdesc, fPrice, eFoodType,eAvailable FROM menu_items WHERE iFoodMenuId = '" . $iFoodMenuId . "' ORDER BY iDisplayOrder DESC";
            //$MenuItemData = $obj->MySQLSelect($subQuery);
            $returnDataArr = $MenuItemData = array();
            if (isset($itemCatDataArr[$iFoodMenuId])) {
                $MenuItemData = $itemCatDataArr[$iFoodMenuId];
            }
            //echo "<pre>";print_r($MenuItemData);die;
            foreach ($MenuItemData as $k => $val) {
                $returnDataArr[$k]['vService_BG_color'] = RANDOM_COLORS_ARR[array_rand(RANDOM_COLORS_ARR, 1)];
                $returnDataArr[$k]['vService_TEXT_color'] = "#FFFFFF";

                $returnDataArr[$k]['MenuItemName'] = $val['menuitemname'];
                $returnDataArr[$k]['iMenuItemId'] = $val['iMenuItemId'];
                $returnDataArr[$k]['iFoodMenuId'] = $iFoodMenuId;
                $returnDataArr[$k]['MenuItemDesc'] = $val['menuitemdesc'];
                $oldImage = $val['vImage'];
                $imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $oldImage;
                if ($oldImage != "" && file_exists($imgpth)) {
                    $returnDataArr[$k]['vImage'] = $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $oldImage;
                } else {
                    $returnDataArr[$k]['vImage'] = $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/sample_image.png';
                }
                $returnArr['fPrice'] = $val['fPrice'] * $priceRatio;
                $fPrice = formatNum($returnArr['fPrice']);
                $returnDataArr[$k]['fPrice'] = $vSymbol . $fPrice;
                $returnDataArr[$k]['eAvailable'] = $val['eAvailable'];
            }
            $CategoryData[$i]['Data'] = $returnDataArr;
            $i++;
        }
        // ## Checking For Pagination ###
        $per_page = 10;
        $TotalPages = ceil(count($CategoryData) / $per_page);
        $pagecount = $page - 1;
        $start_limit = $pagecount * $per_page;
        $CategoryData = array_slice($CategoryData, $start_limit, $per_page);
        // ## Checking For Pagination ###
        $returnData['message'] = $CategoryData;
        if ($TotalPages > $page) {
            $returnData['NextPage'] = $page + 1;
        } else {
            $returnData['NextPage'] = "0";
        }
        $returnData['Action'] = "1";
        setDataResponse($returnData);
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NOTE_NO_FOOD_ITEMS";
        setDataResponse($returnData);
    }
}

// #################################END FOOD MENU ITEM FOR RESTAURANT#########################################
// #################################Update Foodmenu Item For Restaurant#########################################
if ($type == "UpdateFoodMenuItemForRestaurant") {
    global $generalobj;
    $iMenuItemId = isset($_REQUEST["iMenuItemId"]) ? $_REQUEST["iMenuItemId"] : "";
    $eAvailable = isset($_REQUEST["eAvailable"]) ? $_REQUEST["eAvailable"] : "Yes";
    $where = " iMenuItemId = '$iMenuItemId'";
    $Data_update_menuItem['eAvailable'] = $eAvailable;
    $id = $obj->MySQLQueryPerform("menu_items", $Data_update_menuItem, 'update', $where);
    if ($id) {
        $returnData['Action'] = "1";
        $returnData['message'] = "LBL_INFO_UPDATED_TXT";

        setDataResponse($returnData);
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_DATA_AVAIL";
        setDataResponse($returnData);
    }
}

// #################################end Update Foodmenu Item For Restaurant######################################
// ############################################## Order Pickup Type ########################################
if ($type == "UpdateOrderStatusDriver") {

    $iTripId = isset($_REQUEST["iTripid"]) ? $_REQUEST["iTripid"] : "";
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : "";
    $iDriverId = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : "";
    $orderStatus = isset($_REQUEST["orderStatus"]) ? $_REQUEST["orderStatus"] : "";
    $billAmount = isset($_REQUEST["billAmount"]) ? $_REQUEST["billAmount"] : "";
    $fields = "iUserId,iDriverId,iCompanyId,fNetTotal,ePaymentOption,ePaid,vOrderNo,vCouponCode,fRoundingAmount,eRoundingType";
    $OrderData = get_value('orders', $fields, 'iOrderId', $iOrderId);
    $iUserId = $OrderData[0]['iUserId'];
    $iCompanyId = $OrderData[0]['iCompanyId'];
    $iDriverId = $OrderData[0]['iDriverId'];
    $ePaymentOption = $OrderData[0]['ePaymentOption'];
    $ePaid = $OrderData[0]['ePaid'];
    $vOrderNo = $OrderData[0]['vOrderNo'];
    $vCouponCode = $OrderData[0]['vCouponCode'];
    $UserDetailsArr = getDriverCurrencyLanguageDetails($OrderData[0]['iDriverId'], $iOrderId);
    $vSymbol = $UserDetailsArr['currencySymbol'];
    $priceRatio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $confirmprice = getPriceUserCurrency($OrderData[0]['iDriverId'], "Driver", $OrderData[0]['fNetTotal'], $iOrderId);

    //added by SP for rounding off currency wise on 19-11-2019 start
    $query = "SELECT vCurrencyDriver,vCurrencyPassenger FROM `trips` WHERE iOrderId = '" . $iOrderId . "'";
    $TripsData = $obj->MySQLSelect($query);

    $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, rd.vCurrencyDriver, cu.ratio FROM register_driver AS rd LEFT JOIN currency AS cu ON rd.vCurrencyDriver = cu.vName WHERE rd.iDriverId = '" . $OrderData[0]['iDriverId'] . "'";
    $currData = $obj->MySQLSelect($sqlp);
    $vCurrency = $currData[0]['vName'];
    $samecur = ($TripsData[0]['vCurrencyDriver'] == $currData[0]['vCurrencyDriver'] && $TripsData[0]['vCurrencyDriver'] == $TripsData[0]['vCurrencyPassenger']) ? 1 : 0;

    if (isset($OrderData[0]['fRoundingAmount']) && !empty($OrderData[0]['fRoundingAmount']) && $OrderData[0]['fRoundingAmount'] != 0 && $samecur == 1 && $currData[0]['eRoundingOffEnable'] == "Yes") {
        $roundingOffTotal_fare_amountArr['method'] = $OrderData[0]['eRoundingType'];
        $roundingOffTotal_fare_amountArr['differenceValue'] = $OrderData[0]['fRoundingAmount'];

        $roundingOffTotal_fare_amountArr = getRoundingOffAmounttrip($confirmprice['fPrice'], $OrderData[0]['fRoundingAmount'], $OrderData[0]['eRoundingType']);

        if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
            $roundingMethod = "";
        } else {
            $roundingMethod = "-";
        }

        //$confirmprice['fPrice'] = isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00"; // Commented By HJ On 16-12-2019 As Per Discuss With SP
    }
    //added by SP for rounding off currency wise on 19-11-2019 end

    if (!empty($iOrderId)) {
        $sql = "select ru.iUserId, ord.iStatusCode, ord.vOrderNo from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "' AND ord.iStatusCode='8'";
        $data_order = $obj->MySQLSelect($sql);
        if (count($data_order) > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_CANCEL_ORDER_ADMIN_TXT";
            $returnArr['DO_RESTART'] = "Yes";
            setDataResponse($returnArr);
        }
    }

    if ($orderStatus == "OrderPickedup") {
        $billAmount = $confirmprice['fPrice'];
    }

    if (isDeliveryPreferenceEnable()) {
        if ($orderStatus == "OrderDelivered") {
            // Upload Delivery Preference Image
            $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
            $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
            if ($image_object != "") {
                if ($image_object) {
                    ExifCleaning::adjustImageOrientation($image_object);
                }
                $where = " iOrderId = '$iOrderId'";
                if ($image_name != "") {
                    $Photo_Gallery_folder = $tconfig['tsite_upload_order_delivery_pref_images_path'];
                    if (!is_dir($Photo_Gallery_folder))
                        mkdir($Photo_Gallery_folder, 0777);
                    $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "bmp,pdf,doc,docx,jpg,jpeg,gif,png,xls,xlsx,csv");
                    $vImageName = $vFile[0];
                    $Data_update_order['vImageDeliveryPref'] = $vImageName;

                    $vImageDeliveryPref = $tconfig['tsite_upload_order_delivery_pref_images'] . $vImageName;

                    $obj->MySQLQueryPerform("orders", $Data_update_order, 'update', $where);
                }
            }
        }
    }


    if (empty($billAmount) && $orderStatus == "OrderDelivered") {
        $billAmount = $confirmprice['fPrice'];
    }

    if ($billAmount == "") {
        $billAmount = 0;
    }

    $billAmount = $generalobj->setTwoDecimalPoint(str_replace(",", "", $billAmount));
    if (isset($confirmprice['fPrice']) && $confirmprice['fPrice'] == "") {
        $confirmprice['fPrice'] = $generalobj->setTwoDecimalPoint(0);
    }
    // echo $confirmprice['fPrice']."====".$billAmount;die;
    if ($confirmprice['fPrice'] == $billAmount) {
        $sql = "SELECT vCurrencyPassenger,iAppVersion,iUserPetId FROM `register_user` WHERE iUserId = '$iUserId'";
        $Data_passenger_detail = $obj->MySQLSelect($sql);
        $sql = "SELECT iDriverVehicleId,vCurrencyDriver,iAppVersion,CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
        $Data_vehicle = $obj->MySQLSelect($sql);
        $drivername = $Data_vehicle[0]['driverName'];

        $sql = "SELECT vt.fDeliveryCharge from vehicle_type as vt LEFT JOIN trips as tr ON tr.iVehicleTypeId=vt.iVehicleTypeId WHERE iTripId = '" . $iTripId . "'";
        $Data_trip_vehicle = $obj->MySQLSelect($sql);
        $fDeliveryCharge = $Data_trip_vehicle[0]['fDeliveryCharge'];

        // Notify only user
        $DriverMessage = $orderStatus;
        if ($orderStatus == 'OrderPickedup') {
            $Data_update_Trips['tDriverArrivedDate'] = @date("Y-m-d H:i:s");
            $Data_update_Trips['tStartDate'] = @date("Y-m-d H:i:s");
            $Data_update_Trips['iActive'] = 'On Going Trip';
            $Data_update_orders['iStatusCode'] = '5';
            $Data_update_driver['vTripStatus'] = 'On Going Trip';
            $Order_Status_id = createOrderLog($iOrderId, "5");

            // $tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_EXECUTIVE_TXT']." ".$drivername." ".$languageLabelsArr['LBL_DELIVERY_ON_WAY_TXT']." #".$vOrderNo;
            $tripdriverarrivlbl = $drivername . " " . $languageLabelsArr['LBL_PICKUP_ORDER_NOTIFICATION_TXT'];
        } else if ($orderStatus == 'OrderDelivered') {
            $Data_update_Trips['iActive'] = 'Finished';
            $Data_update_Trips['tEndDate'] = @date("Y-m-d H:i:s");
            $Data_update_Trips['fDeliveryCharge'] = $fDeliveryCharge;
            if ($ePaymentOption == "Cash") {
                $Data_update_orders['ePaid'] = "Yes";
            }

            $Data_update_orders['dDeliveryDate'] = @date("Y-m-d H:i:s");
            $Data_update_orders['iStatusCode'] = '6';
            $Data_update_driver['vTripStatus'] = 'Finished';
            $Order_Status_id = createOrderLog($iOrderId, "6");
            $tripdriverarrivlbl = $languageLabelsArr['LBL_DELIVERY_EXECUTIVE_TXT'] . " " . $drivername . " " . $languageLabelsArr['LBL_DELIVERY_DELIVER_TXT'] . " #" . $vOrderNo;



            /* added by PM for Auto credit wallet driver on 25-01-2020 start */
            if (checkAutoCreditDriverModule()) {
                $Dataorder = array();
                $Dataorder['ePaymentOption'] = $ePaymentOption;
                $Dataorder['iUserId'] = $iUserId;
                $Dataorder['vOrderNo'] = $vOrderNo;
                $Dataorder['iOrderId'] = $iOrderId;
                $Dataorder['fDeliveryCharge'] = $fDeliveryCharge;

                AutoCreditWalletDriver($Dataorder, "UpdateOrderStatusDriver", $iServiceId);
            } else {
                $updateQury = "UPDATE trip_outstanding_amount set ePaidByPassenger = 'Yes',vOrderAdjusmentId = '" . $vOrderNo . "' WHERE iUserId = '" . $iUserId . "' AND ePaidByPassenger = 'No'";
                $obj->sql_query($updateQury);
            }

            /* added by PM for Auto credit wallet driver on 25-01-2020 start */


            // # Deduct Order Amount From Driver's Wallet Only For Cash Delivered Orders ##
            if ($ePaymentOption == "Cash" && $COMMISION_DEDUCT_ENABLE == 'Yes' && $OrderData[0]['fNetTotal'] > 0) {
                $iBalance = $OrderData[0]['fNetTotal'];
                $eType = "Debit";
                $eFor = "Withdrawl";
                $tDescription = '#LBL_DEBITED_BOOKING_DL# ' . ' ' . $vOrderNo;
                $ePaymentStatus = 'Settelled';
                $dDate = @date('Y-m-d H:i:s');

                // Added By HJ On 18-12-2019 For Prevent Duplication Issue Dicuss with KS Sir Start
                $getPaymentStatus = $obj->MySQLSelect("SELECT eUserType,ePaymentStatus,iUserWalletId,eType FROM user_wallet WHERE iTripId='" . $iTripId . "'");
                $walletArray = array();
                for ($h = 0; $h < count($getPaymentStatus); $h++) {
                    $walletArray[$getPaymentStatus[$h]['eType']][$getPaymentStatus[$h]['eUserType']] = $getPaymentStatus[$h]['eType'];
                }
                // Added By HJ On 18-12-2019 For Prevent Duplication Issue Dicuss with KS Sir End
                if (!isset($walletArr[$eType]['Driver'])) {
                    $generalobj->InsertIntoUserWallet($iDriverId, "Driver", $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate, $iOrderId, "");
                }
                $Where_Order = " iTripId = '$iTripId'";
                $Data_update_driver_paymentstatus['eDriverPaymentStatus'] = "Settelled";

                // $Update_Payment_Id = $obj->MySQLQueryPerform("trips",$Data_update_driver_paymentstatus,'update',$Where_Order);
            }

            // # Deduct Order Amount From Driver's Wallet Only For Cash Delivered Orders ##
            //$generalobj->orderemaildataDelivered($iOrderId, "Passenger"); //added by SP on 2-7-2019 to work emails properly put it below after update
            // # Update Coupon Used Limit ##
            if ($vCouponCode != '') {
                $Data_update_order['vCouponCode'] = $vCouponCode;
                $noOfCouponUsed = get_value('coupon', 'iUsed', 'vCouponCode', $vCouponCode, '', 'true');
                $where_coupon = " vCouponCode = '" . $vCouponCode . "'";
                $data_coupon['iUsed'] = $noOfCouponUsed + 1;
                $obj->MySQLQueryPerform("coupon", $data_coupon, 'update', $where_coupon);

                ## Check Coupon Code Usage Limit , Send Email to Admin if Usage  Limit is over ##

                $UpdatedCouponUsedNo = $noOfCouponUsed + 1;

                $sql = "SELECT iUsed, iUsageLimit from coupon WHERE vCouponCode = '" . $vCouponCode . "'";

                $coupon_result = $obj->MySQLSelect($sql);

                if ($iUsageLimit == $UpdatedCouponUsedNo) {

                    $maildata['vCouponCode'] = $vCouponCode;

                    $maildata['iUsageLimit'] = $iUsageLimit;

                    $maildata['COMPANY_NAME'] = $COMPANY_NAME;

                    $mail = $generalobj->send_email_user('COUPON_LIMIT_COMPLETED_TO_ADMIN', $maildata);
                }

                ## Check Coupon Code Usage Limit , Send Email to Admin if Usage  Limit is over ##
            }

            // # Update Coupon Used Limit ##
        }

        $twhere = " iTripId = '" . $iTripId . "'";
        $TripId = $obj->MySQLQueryPerform("trips", $Data_update_Trips, 'update', $twhere);
        $owhere = " iOrderId = '" . $iOrderId . "'";
        $OrderId = $obj->MySQLQueryPerform("orders", $Data_update_orders, 'update', $owhere);
        $rdwhere = " iDriverId = '" . $OrderData[0]['iDriverId'] . "'";
        $OrderStatus = $obj->MySQLQueryPerform("register_driver", $Data_update_driver, 'update', $rdwhere);




        // # Deduct Order Amount From Driver's Wallet Only For Cash Delivered Orders ##
        $generalobj->orderemaildataDelivered($iOrderId, "Passenger"); //added by SP on 2-7-2019 to work emails properly

        $alertMsg = $tripdriverarrivlbl;
        $message_arr = array();
        $message_arr['iDriverId'] = $iDriverId;
        $message_arr['Message'] = $DriverMessage;
        $message_arr['iTripId'] = strval($iTripId);
        $message_arr['DriverAppVersion'] = strval($Data_vehicle[0]['iAppVersion']);
        $message_arr['driverName'] = $Data_vehicle[0]['vName'] . " " . $Data_vehicle[0]['vLastName'];

        // $message_arr['vRideNo'] = $TripRideNO;
        $message_arr['iOrderId'] = $iOrderId;
        $message_arr['vTitle'] = $alertMsg;
        $message_arr['eSystem'] = "DeliverAll";

        if (isDeliveryPreferenceEnable()) {
            if ($orderStatus == "OrderDelivered" && $vImageDeliveryPref != "") {
                $message_arr['vImageDeliveryPref'] = $vImageDeliveryPref;
            }
        }

        $message = json_encode($message_arr);

        // ####################Add Status Message#########################
        /* $DataTripMessages['tMessage']= $message;
          $DataTripMessages['iDriverId']= $iDriverId;
          $DataTripMessages['iTripId']= $iTripId;
          $DataTripMessages['iOrderId']= $iOrderId;
          $DataTripMessages['iUserId']= $iUserId;
          $DataTripMessages['eFromUserType']= "Driver";
          $DataTripMessages['eToUserType']= "Passenger";
          $DataTripMessages['eReceived']= "Yes";
          $DataTripMessages['dAddedDate']= @date("Y-m-d H:i:s");
          $obj->MySQLQueryPerform("trip_status_messages",$DataTripMessages,'insert'); */

        // ###############################################################
        // Notify user and restaurant for OrderDelivered and order Pickup
        if ($iTripId > 0) {
            if ($PUBNUB_DISABLED == "Yes") {
                $ENABLE_PUBNUB = "No";
            }

            $alertSendAllowed = true;
            /* For PubNub Setting */
            $tableName = "register_user";
            $iMemberId_VALUE = $iUserId;
            $iMemberId_KEY = "iUserId";
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            /* For PubNub Setting Finished */
            $sql = "SELECT iGcmRegId,eDeviceType FROM register_user WHERE iUserId='$iUserId'";
            $result = $obj->MySQLSelect($sql);
            $registatoin_ids = $result[0]['iGcmRegId'];
            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();
            $sql = "SELECT iGcmRegId,eDeviceType,iAppVersion,tSessionId FROM company WHERE iCompanyId='$iCompanyId'";
            $result_company = $obj->MySQLSelect($sql);
            $registatoin_ids_company = $result_company[0]['iGcmRegId'];
            $deviceTokens_arr_ios_company = array();
            $registation_ids_new_company = array();

            if ($alertSendAllowed == true) {
                if ($result[0]['eDeviceType'] == "Android") {
                    array_push($registation_ids_new, $result[0]['iGcmRegId']);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                } else {
                    array_push($deviceTokens_arr_ios, $result[0]['iGcmRegId']);
                    sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                }

                if ($result_company[0]['eDeviceType'] == "Android") {
                    array_push($registation_ids_new_company, $result_company[0]['iGcmRegId']);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $resultc = send_notification($registation_ids_new_company, $Rmessage, 0);
                } else {
                    array_push($deviceTokens_arr_ios_company, $result_company[0]['iGcmRegId']);
                    sendApplePushNotification(2, $deviceTokens_arr_ios_company, $message, $alertMsg, 0);
                }
            }

            //sleep(3);
            // if($ENABLE_PUBNUB == "Yes"  && $PUBNUB_PUBLISH_KEY != "" && $PUBNUB_SUBSCRIBE_KEY != "") {
            /* $pubnub = new Pubnub\Pubnub(array(
              "publish_key" => $PUBNUB_PUBLISH_KEY,
              "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
              "uuid" => $uuid
              )); */
            $channelName = "PASSENGER_" . $iUserId;
            $tSessionId = get_value("register_user", 'tSessionId', "iUserId", $iUserId, '', 'true');
            $message_arr['tSessionId'] = $tSessionId;

            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

            // $info = $pubnub->publish($channelName, $message_pub);
            if (count($deviceTokens_arr_ios) > 0) {
                sleep(3);
            }
            /* if ($PUBNUB_DISABLED == "Yes") {
              publishEventMessage($channelName, $message_pub);
              } else {
              $info = $pubnub->publish($channelName, $message_pub);
              } */
            publishEventMessage($channelName, $message_pub);
            $channelName_company = "COMPANY_" . $iCompanyId;
            $message_arr['tSessionId'] = $result_company[0]['tSessionId'];
            $message_pub_company = json_encode($message_arr, JSON_UNESCAPED_UNICODE);

            if (count($deviceTokens_arr_ios_company) > 0) {
                sleep(3);
            }
            // $info_company = $pubnub->publish($channelName_company, $message_pub_company);
            /* if ($PUBNUB_DISABLED == "Yes") {
              publishEventMessage($channelName_company, $message_pub_company);
              } else {
              $info_company = $pubnub->publish($channelName_company, $message_pub_company);
              } */

            // }
            /* else {
              $alertSendAllowed = true;
              } */

            $returnArr['Action'] = "1";
            if ($orderStatus == 'OrderDelivered') { // Added BY HJ On 09-07-2019 For Prevent Multiple Referrer Amount Issue With Discuss KS
                $generalobj->get_benefit_amount($iTripId);
            }
            /* $data['iTripId'] = $iTripId;
              $data['PAppVersion'] = $Data_passenger_detail[0]['iAppVersion'];
              $returnArr['message']=$data; */
            /* if($iOrderId !="") {
              $passengerData = get_value('register_user', 'vName,vLastName,vImgName,vFbId,vAvgRating,vPhone,vPhoneCode,iAppVersion', 'iUserId', $iUserId);
              $returnArr['PassengerId'] = $iUserId;
              $returnArr['PName'] = $passengerData[0]['vName'].' '.$passengerData[0]['vLastName'];
              $returnArr['PPicName'] = $passengerData[0]['vImgName'];
              $returnArr['PFId'] = $passengerData[0]['vFbId'];
              $returnArr['PRating'] = $passengerData[0]['vAvgRating'];
              $returnArr['PPhone'] = $passengerData[0]['vPhone'];
              $returnArr['PPhoneC'] = $passengerData[0]['vPhoneCode'];
              $returnArr['PAppVersion'] = $passengerData[0]['iAppVersion'];
              $returnArr['TripId'] = strval($iTripId);
              } */
            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_BILL_VALUE_ERROR_TXT";
        setDataResponse($returnArr);
    }
}

// ############################################# Order Pickup Type #########################################
// ###################################### Image Upload after order Picked up #####################################
if ($type == "OrderImageUpload") {
    global $generalobj;
    $iTripId = isset($_REQUEST["iTripid"]) ? $_REQUEST["iTripid"] : "";
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : "";
    $eImgSkip = isset($_REQUEST["eImgSkip"]) ? $_REQUEST["eImgSkip"] : "";
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    if ($image_object) {
        ExifCleaning::adjustImageOrientation($image_object);
    }
    $where = " iTripId = '$iTripId'";
    if ($image_name != "") {
        $Photo_Gallery_folder = $tconfig['tsite_upload_order_images_path'];
        if (!is_dir($Photo_Gallery_folder))
            mkdir($Photo_Gallery_folder, 0777);
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "bmp,pdf,doc,docx,jpg,jpeg,gif,png,xls,xlsx,csv");
        $vImageName = $vFile[0];
        $Data_update_trips['vImage'] = $vImageName;
    }
    $Data_update_trips['eImgSkip'] = $eImgSkip;
    $id = $obj->MySQLQueryPerform("trips", $Data_update_trips, 'update', $where);
    if ($id) {
        $returnData['Action'] = "1";
    } else {
        $returnData['Action'] = "0";
    }

    setDataResponse($returnData);
}

// ###################################### Image Uplaod after order Picked up #####################################
// ############################ Get State Using country code ######################
if ($type == "GetStatesFromCountry") {
    global $generalobj, $obj, $vTimeZone, $vUserDeviceCountry;
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : "";
    $vCountry = isset($_REQUEST["vCountry"]) ? $_REQUEST["vCountry"] : "";
    $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId);
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    if ($vCountry == '') {
        $usercountrydetailbytimezone = GetUserCounryDetail($iCompanyId, "Company", $vTimeZone, $vUserDeviceCountry);
        $vCountryCode = $usercountrydetailbytimezone['vDefaultCountryCode'];
    } else {
        $vCountryCode = $vCountry;
    }

    $Sql = "SELECT iCountryId FROM country WHERE vCountryCode = '" . $vCountryCode . "'";
    $DataCountry = $obj->MySQLSelect($Sql);
    $iCountryId = $DataCountry[0]['iCountryId'];
    $query = "SELECT iStateId,vStateCode,vState FROM state WHERE iCountryId = '" . $iCountryId . "' AND eStatus = 'Active' ORDER BY vState";
    $db_rec = $obj->MySQLSelect($query);
    if (count($db_rec) > 0) {
        $StateArr['Action'] = "1";
        $StateArr['totalValues'] = count($db_rec);
        $StateArr['StateList'] = $db_rec;
    } else {
        $StateArr['Action'] = "0";
        $cityArr['message'] = $languageLabelsArr['LBL_NO_STATE_AVAILABLE'];
    }

    setDataResponse($StateArr);
}

// ############################ Get State Using country code ######################
// ############################ Get State Using country code ######################
if ($type == "GetCityFromState") {
    global $generalobj, $obj, $vTimeZone, $vUserDeviceCountry;
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : "";
    $iStateId = isset($_REQUEST["iStateId"]) ? $_REQUEST["iStateId"] : "";
    $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId);
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    if ($iStateId == '') {
        $usercountrydetailbytimezone = GetUserCounryDetail($iCompanyId, "Company", $vTimeZone, $vUserDeviceCountry);
        $vCountryCode = $usercountrydetailbytimezone['vDefaultCountryCode'];
        $Sql = "SELECT iCountryId FROM country WHERE vCountryCode = '" . $vCountryCode . "'";
        $DataCountry = $obj->MySQLSelect($Sql);
        $iCountryId = $DataCountry[0]['iCountryId'];
        $query = "SELECT iStateId FROM state WHERE iCountryId = '" . $iCountryId . "' AND eStatus = 'Active'";
        $db_rec = $obj->MySQLSelect($query);
        $iStateId = $db_rec[0]['iStateId'];
    }

    $query1 = "SELECT iCityId,vCity,eStatus FROM city WHERE  iStateId = '" . $iStateId . "' AND eStatus ='Active' ORDER BY vCity";
    $City_rec = $obj->MySQLSelect($query1);
    if (count($City_rec) > 0) {
        $cityArr['Action'] = "1";
        $cityArr['totalValues'] = count($City_rec);
        $cityArr['CityList'] = $City_rec;
    } else {
        $cityArr['Action'] = "0";
        $cityArr['message'] = $languageLabelsArr['LBL_NO_CITY_AVAILABLE'];
    }

    setDataResponse($cityArr);
}

// ############################ Get State Using country code ######################
// ################################## For Strappers Scree Update Restaurant Details ##################################
if ($type == "UpdateRestaurantDetails") {
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : "";
    $CALL_TYPE = isset($_REQUEST["CALL_TYPE"]) ? $_REQUEST["CALL_TYPE"] : "Display"; // Display , Update
    $sql = "SELECT vName,vSymbol,Ratio FROM  `currency` WHERE  `eDefault` = 'Yes' ";
    $defCurrencyValues = $obj->MySQLSelect($sql);
    $vCurrency = $defCurrencyValues[0]['vName'];
    $vCurrencySymbol = $defCurrencyValues[0]['vSymbol'];
    $returnArr['vCurrency'] = $vCurrency;
    $returnArr['vCurrencySymbol'] = $vCurrencySymbol;
    if ($CALL_TYPE == "Display") {
        $sqlc = "SELECT co.vContactName,co.vRestuarantLocation,co.vRestuarantLocationLat,co.vRestuarantLocationLong,co.vCaddress,co.vState as iStateId,co.vCity as iCityId,co.vZip,co.iMaxItemQty,co.fPrepareTime,co.fMinOrderValue,st.vState,ci.vCity FROM company as co LEFT JOIN state as st ON st.iStateId=co.vState LEFT JOIN city as ci ON ci.iCityId=co.vCity WHERE co.iCompanyId = '" . $iCompanyId . "'";
        $result_company = $obj->MySQLSelect($sqlc);
        $result_company[0]['iMaxItemQty'] = ($result_company[0]['iMaxItemQty'] > 0) ? $result_company[0]['iMaxItemQty'] : "";
        $result_company[0]['fPrepareTime'] = ($result_company[0]['fPrepareTime'] > 0) ? $result_company[0]['fPrepareTime'] : "";
        $result_company[0]['fMinOrderValue'] = ($result_company[0]['fMinOrderValue'] > 0) ? $result_company[0]['fMinOrderValue'] : "";
        $result_company[0]['vCity'] = (!empty($result_company[0]['vCity'])) ? $result_company[0]['vCity'] : "";
        $result_company[0]['vState'] = (!empty($result_company[0]['vState'])) ? $result_company[0]['vState'] : "";
        $returnArr['Action'] = "1";
        $returnArr['message'] = $result_company[0];

        setDataResponse($returnArr);
    } else {
        $vContactName = isset($_REQUEST["vContactName"]) ? $_REQUEST["vContactName"] : "";
        $vRestuarantLocation = isset($_REQUEST["vRestuarantLocation"]) ? $_REQUEST["vRestuarantLocation"] : "";
        $vRestuarantLocationLat = isset($_REQUEST["vRestuarantLocationLat"]) ? $_REQUEST["vRestuarantLocationLat"] : "";
        $vRestuarantLocationLong = isset($_REQUEST["vRestuarantLocationLong"]) ? $_REQUEST["vRestuarantLocationLong"] : "";
        $vCaddress = isset($_REQUEST["vCaddress"]) ? $_REQUEST["vCaddress"] : "";
        $vState = isset($_REQUEST["vState"]) ? $_REQUEST["vState"] : "";
        $vCity = isset($_REQUEST["vCity"]) ? $_REQUEST["vCity"] : "";
        $vZip = isset($_REQUEST["vZip"]) ? $_REQUEST["vZip"] : "";
        $iMaxItemQty = isset($_REQUEST["iMaxItemQty"]) ? $_REQUEST["iMaxItemQty"] : "";
        $fPrepareTime = isset($_REQUEST["fPrepareTime"]) ? $_REQUEST["fPrepareTime"] : "";
        $fMinOrderValue = isset($_REQUEST["fMinOrderValue"]) ? $_REQUEST["fMinOrderValue"] : "";
        $where = " iCompanyId = '$iCompanyId'";
        $Data_update_Companies['vContactName'] = $vContactName;
        $Data_update_Companies['vRestuarantLocation'] = $vRestuarantLocation;
        $Data_update_Companies['vRestuarantLocationLat'] = $vRestuarantLocationLat;
        $Data_update_Companies['vRestuarantLocationLong'] = $vRestuarantLocationLong;
        $Data_update_Companies['vCaddress'] = $vCaddress;
        $Data_update_Companies['vState'] = $vState;
        $Data_update_Companies['vCity'] = $vCity;
        $Data_update_Companies['vZip'] = $vZip;
        if (isset($_REQUEST["iMaxItemQty"])) {
            $Data_update_Companies['iMaxItemQty'] = $iMaxItemQty;
        }

        if (isset($_REQUEST["fPrepareTime"])) {
            $Data_update_Companies['fPrepareTime'] = $fPrepareTime;
        }

        if (isset($_REQUEST["fMinOrderValue"])) {
            $Data_update_Companies['fMinOrderValue'] = $fMinOrderValue;
        }

        $Companyid = $obj->MySQLQueryPerform("company", $Data_update_Companies, 'update', $where);
        if ($Companyid) {
            $returnData['Action'] = "1";
            $returnData['message'] = "LBL_INFO_UPDATED_TXT";
        } else {
            $returnData['Action'] = "0";
            $returnData['message'] = "LBL_TRY_AGAIN_LATER";
        }


        setDataResponse($returnData);
    }
}

// ################################## Update Restaurant Details ##################################
// ############################## Company States ###############################
if ($type == "getCompanyStates") {
    $iCompanyId = isset($_REQUEST['iCompanyId']) ? clean($_REQUEST['iCompanyId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Company';
    if ($userType == 'company' || $userType == 'Company') {
        $doc_usertype = 'store';
        // $doc_usertype = 'company';
    }
    $docUpload = 'Yes';
    $CompanyDetailCompleted = 'Yes';
    $WorkingHoursCompleted = 'Yes';
    $CompanyStateActive = 'Yes';
    $fields = "vCountry,vContactName,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,vState,vFromMonFriTimeSlot1,vToMonFriTimeSlot1,vFromSatSunTimeSlot1,vToSatSunTimeSlot1";
    $CompanyData = get_value('company', $fields, 'iCompanyId', $iCompanyId);
    $vContactName = $CompanyData[0]['vContactName'];
    $vRestuarantLocation = $CompanyData[0]['vRestuarantLocation'];
    $vRestuarantLocationLat = $CompanyData[0]['vRestuarantLocationLat'];
    $vRestuarantLocationLong = $CompanyData[0]['vRestuarantLocationLong'];
    $vCaddress = $CompanyData[0]['vCaddress'];
    $vState = $CompanyData[0]['vState'];
    if ($vContactName == '' || $vRestuarantLocation == '' || $vRestuarantLocationLat == '' || $vRestuarantLocationLong == '' || $vCaddress == '' || $vState == '') {
        $CompanyDetailCompleted = 'No';
    }

    $vFromMonFriTimeSlot1 = $CompanyData[0]['vFromMonFriTimeSlot1'];
    $vToMonFriTimeSlot1 = $CompanyData[0]['vToMonFriTimeSlot1'];
    $vFromSatSunTimeSlot1 = $CompanyData[0]['vFromSatSunTimeSlot1'];
    $vToSatSunTimeSlot1 = $CompanyData[0]['vToSatSunTimeSlot1'];
    if (($vFromMonFriTimeSlot1 == '00:00:00' || $vFromMonFriTimeSlot1 == '') || ($vToMonFriTimeSlot1 == '00:00:00' || $vToMonFriTimeSlot1 == '') || ($vFromSatSunTimeSlot1 == '00:00:00' || $vFromSatSunTimeSlot1 == '') || ($vToSatSunTimeSlot1 == '00:00:00' || $vToSatSunTimeSlot1 == '')) {
        $WorkingHoursCompleted = 'No';
    }

    $vCountry = $CompanyData[0]['vCountry'];
    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $iCompanyId . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='" . $doc_usertype . "' and (dm.country='" . $vCountry . "' OR dm.country='All') and dm.status='Active'";
    $db_document = $obj->MySQLSelect($sql1);
    if (count($db_document) > 0) {
        for ($i = 0; $i < count($db_document); $i++) {
            if ($db_document[$i]['doc_file'] == "") {
                $docUpload = 'No';
            }
        }
    } else {
        $docUpload = 'No';
    }

    $sql = "SELECT eStatus FROM `company` WHERE iCompanyId ='" . $iCompanyId . "'";
    $Data = $obj->MySQLSelect($sql);
    if (strtolower($Data[0]['eStatus']) != "active" || strtolower($Data[0]['eStatus']) != "active") {
        $CompanyStateActive = 'No';
    }

    if ($CompanyStateActive == "Yes") {
        $docUpload = "Yes";
        $CompanyDetailCompleted = "Yes";
        $WorkingHoursCompleted = "Yes";
    }

    $returnArr['Action'] = "1";
    $returnArr['IS_COMPANY_DETAIL_COMPLETED'] = $CompanyDetailCompleted;
    $returnArr['IS_DOCUMENT_PROCESS_COMPLETED'] = $docUpload;
    $returnArr['IS_WORKING_HOURS_COMPLETED'] = $WorkingHoursCompleted;
    $returnArr['IS_COMPANY_STATE_ACTIVATED'] = $CompanyStateActive;

    setDataResponse($returnArr);
}

// ############################## Company States ###############################
// ##########################displayDocList for company##########################################################
if ($type == "displayCompanyDocList") {
    global $generalobj, $tconfig;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $doc_usertype = isset($_REQUEST['doc_usertype']) ? clean(strtolower($_REQUEST['doc_usertype'])) : 'company';
    $doc_userid = $iMemberId;
    $UserData = get_value('company', 'vCountry,vLang', 'iCompanyId', $iMemberId);
    $vCountry = $UserData[0]['vCountry'];
    $vLang = $UserData[0]['vLang'];
    if ($vLang == '' || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    if ($doc_usertype == 'company') {
        $doc_usertype = 'store';
        //$doc_usertype = 'Company';
    }
    $sql1 = "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name_" . $vLang . " as doc_name ,dm.ex_status,dm.status, COALESCE(dl.doc_id,  '' ) as doc_id,COALESCE(dl.doc_masterid, '') as masterid_list ,COALESCE(dl.ex_date, '') as ex_date,COALESCE(dl.doc_file, '') as doc_file, COALESCE(dl.status, '') as status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $doc_userid . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='" . $doc_usertype . "' AND (dm.country='" . $vCountry . "' OR dm.country='All') and dm.status='Active' GROUP BY dm.doc_masterid ORDER BY dl.doc_id DESC"; // (GROUP BY dm.doc_masterid ORDER BY dl.doc_id) DESC Added By HJ For Solved Duplicate Data issues As Per Discuss with KS Sir 
    $db_vehicle = $obj->MySQLSelect($sql1);
    if (count($db_vehicle) > 0) {
        $Photo_Gallery_folder = $tconfig['tsite_upload_compnay_doc'] . "/" . $iMemberId . "/";
        for ($i = 0; $i < count($db_vehicle); $i++) {
            if ($db_vehicle[$i]['doc_file'] != "") {
                $db_vehicle[$i]['vimage'] = $Photo_Gallery_folder . $db_vehicle[$i]['doc_file'];
            } else {
                $db_vehicle[$i]['vimage'] = "";
            }

            // # Checking for expire date of document ##
            $ex_date = $db_vehicle[$i]['ex_date'];
            $todaydate = date('Y-m-d');
            if ($ex_date == "" || $ex_date == "0000-00-00") {
                $expire_document = "No";
            } else {
                if (strtotime($ex_date) < strtotime($todaydate)) {
                    $expire_document = "Yes";
                } else {
                    $expire_document = "No";
                }
            }
            $db_vehicle[$i]['exp_date'] = "";
            if ($ex_date != "0000-00-00") {
                $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
                $expireLabel = $languageLabelsArr['LBL_EXPIRE_TXT'];
                //$newFormat = date("jS F Y", strtotime($db_vehicle[$i]['ex_date']));
                $newFormat = date("d M, Y (D)", strtotime($db_vehicle[$i]['ex_date']));
                $db_vehicle[$i]['exp_date'] = $expireLabel . ": " . $newFormat;
            }


            $allowDate = date('Y-m-d', strtotime($db_vehicle[$i]['ex_date'] . ' - ' . $BEFORE_DAYS_ALLLOW_UPDATE_DOCS . ' days'));

            if (($db_vehicle[$i]['ex_date'] == '' || $todaydate >= $allowDate) || $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'No') {
                $db_vehicle[$i]['allow_date_change'] = 'Yes';
                $db_vehicle[$i]['doc_update_disable'] = '';
            } else {
                $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);

                $db_vehicle[$i]['allow_date_change'] = 'No';
                $db_vehicle[$i]['doc_update_disable'] = $languageLabelsArr['LBL_DOC_UPDATE_DISABLE'];
            }


            $db_vehicle[$i]['EXPIRE_DOCUMENT'] = $expire_document;
            // # Checking for expire date of document ##
        }

        $returnArr['Action'] = "1";
        $returnArr['message'] = $db_vehicle;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_DOC_AVAIL";
    }


    setDataResponse($returnArr);
}

// ###################################################################################################
// ##########################Add/Update Company Documents ############################
if ($type == "uploadcompanydocument") {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    //echo "<pre>";print_r($_REQUEST);die;
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $memberType = isset($_REQUEST['MemberType']) ? clean($_REQUEST['MemberType']) : 'Driver';
    $doc_usertype = isset($_REQUEST['doc_usertype']) ? clean(strtolower($_REQUEST['doc_usertype'])) : 'company';
    $doc_masterid = isset($_REQUEST['doc_masterid']) ? clean($_REQUEST['doc_masterid']) : '';
    $doc_name = isset($_REQUEST['doc_name']) ? clean($_REQUEST['doc_name']) : '';
    $doc_id = isset($_REQUEST['doc_id']) ? clean($_REQUEST['doc_id']) : '';
    $doc_file = isset($_REQUEST['doc_file']) ? clean($_REQUEST['doc_file']) : '';
    $ex_date = isset($_REQUEST['ex_date']) ? clean($_REQUEST['ex_date']) : '';
    $ex_status = isset($_REQUEST['ex_status']) ? clean($_REQUEST['ex_status']) : '';

    $Today = Date('Y-m-d');

    $doc_userid = $iMemberId;
    $status = "Inactive";
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $action = ($doc_id != '') ? 'Edit' : 'Add';
    //echo "<pre>";print_r($_FILES);die;
    $addupdatemode = ($action == 'Add') ? 'insert' : 'update';
    if ($doc_usertype == 'company') {
        $doc_usertype = 'store';
        // $doc_usertype = 'company';
    }
    //echo "hello";die;
    if ($doc_file != "") {
        $vImageName = $doc_file;
    } else {
        $extensionArr = explode(".", $image_name);
        $extension = $extensionArr[count($extensionArr) - 1];
        $extension = strtolower($extension);
        if ($extension == "png" || $extension == "jpg" || $extension == "jpeg") {
            if ($image_object) {
                ExifCleaning::adjustImageOrientation($image_object);
            }
        }

        $Photo_Gallery_folder = $tconfig['tsite_upload_compnay_doc_path'] . "/" . $iMemberId . "/";
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }
        $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "bmp,pdf,doc,docx,jpg,jpeg,gif,png,xls,xlsx,csv");
        //echo "<pre>";print_r($vFile);die;

        $vImageName = $vFile[0];
    }
    //echo $doc_id;die;
    if ($vImageName != '') {
        $Data_Update["doc_masterid"] = $doc_masterid;
        $Data_Update["doc_usertype"] = $doc_usertype;
        $Data_Update["doc_userid"] = $doc_userid;
        $Data_Update["edate"] = @date("Y-m-d H:i:s");

        $returnArr['doc_under_review'] = '';

        $exitingExpDate = 'SELECT dm.ex_status,dl.ex_date,dl.req_date FROM document_list AS dl LEFT JOIN document_master as dm ON dm.doc_masterid = dl.doc_masterid  WHERE doc_id = ' . $doc_id;
        $db_data1 = $obj->MySQLSelect($exitingExpDate);


        // echo "<pre>";print_r($db_data1);die;
        $allowDate = date('Y-m-d', strtotime($db_data1[0]['ex_date'] . ' - ' . $BEFORE_DAYS_ALLLOW_UPDATE_DOCS . ' days'));
        if ($Today >= $allowDate && $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'Yes' && $action != "Add" && $db_data1[0]['ex_status'] == 'yes') {
            $ex_date = $ex_date == $db_data1[0]['ex_date'] ? $db_data1[0]['req_date'] : $ex_date;

            $Data_Update["req_date"] = $ex_date;
            $Data_Update["req_file"] = $vImageName;

            $returnArr['doc_under_review'] = 'LBL_FOR_DOCS_UNDER_REVIEW';
        } else {
            $Data_Update["ex_date"] = $ex_date;
            $Data_Update["doc_file"] = $vImageName;
        }

        if ($action == "Add") {
            $Data_Update["status"] = $status;
            $id = $obj->MySQLQueryPerform("document_list", $Data_Update, 'insert');
        } else {
            $where = " doc_id = '" . $doc_id . "'";
            $id = $obj->MySQLQueryPerform("document_list", $Data_Update, 'update', $where);
        }

        $generalobj->save_log_data($iMemberId, $iMemberId, 'company', $doc_name, $vImageName);
        if ($id > 0) {
            $returnArr['Action'] = "1";
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

// ##########################Add/Update Driver's Document and Vehilcle Document Ends#######################
// ##########################Update Time Slot for Restaurant#######################
if ($type == "UpdateCompanyTiming") {
    $iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
    $CALL_TYPE = isset($_REQUEST["CALL_TYPE"]) ? $_REQUEST["CALL_TYPE"] : "Display"; // Display , Update
    if ($CALL_TYPE == "Display") {
        $sqlc = "SELECT vFromMonFriTimeSlot1,vToMonFriTimeSlot1,vFromMonFriTimeSlot2,vToMonFriTimeSlot2,vFromSatSunTimeSlot1,vToSatSunTimeSlot1,vFromSatSunTimeSlot2,vToSatSunTimeSlot2 FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        $result_company = $obj->MySQLSelect($sqlc);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $result_company[0];

        setDataResponse($returnArr);
    } else {
        $vFromMonFriTimeSlot1 = isset($_REQUEST['vFromMonFriTimeSlot1']) ? $_REQUEST['vFromMonFriTimeSlot1'] : '';
        $vToMonFriTimeSlot1 = isset($_REQUEST['vToMonFriTimeSlot1']) ? $_REQUEST['vToMonFriTimeSlot1'] : '';
        $vFromMonFriTimeSlot2 = isset($_REQUEST['vFromMonFriTimeSlot2']) ? $_REQUEST['vFromMonFriTimeSlot2'] : '';
        $vToMonFriTimeSlot2 = isset($_REQUEST['vToMonFriTimeSlot2']) ? $_REQUEST['vToMonFriTimeSlot2'] : '';
        $vFromSatSunTimeSlot1 = isset($_REQUEST['vFromSatSunTimeSlot1']) ? $_REQUEST['vFromSatSunTimeSlot1'] : '';
        $vToSatSunTimeSlot1 = isset($_REQUEST['vToSatSunTimeSlot1']) ? $_REQUEST['vToSatSunTimeSlot1'] : '';
        $vFromSatSunTimeSlot2 = isset($_REQUEST['vFromSatSunTimeSlot2']) ? $_REQUEST['vFromSatSunTimeSlot2'] : '';
        $vToSatSunTimeSlot2 = isset($_REQUEST['vToSatSunTimeSlot2']) ? $_REQUEST['vToSatSunTimeSlot2'] : '';
        $where = " iCompanyId = '" . $iCompanyId . "'";
        $Data_Update['vFromMonFriTimeSlot1'] = $vFromMonFriTimeSlot1;
        $Data_Update['vToMonFriTimeSlot1'] = $vToMonFriTimeSlot1;
        $Data_Update['vFromMonFriTimeSlot2'] = $vFromMonFriTimeSlot2;
        $Data_Update['vToMonFriTimeSlot2'] = $vToMonFriTimeSlot2;
        $Data_Update['vFromSatSunTimeSlot1'] = $vFromSatSunTimeSlot1;
        $Data_Update['vToSatSunTimeSlot1'] = $vToSatSunTimeSlot1;
        $Data_Update['vFromSatSunTimeSlot2'] = $vFromSatSunTimeSlot2;
        $Data_Update['vToSatSunTimeSlot2'] = $vToSatSunTimeSlot2;
        $id = $obj->MySQLQueryPerform("company", $Data_Update, 'update', $where);
        if ($id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_INFO_UPDATED_TXT";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER";
        }


        setDataResponse($returnArr);
    }
}

// ##########################Update Time Slot for Restaurant#######################
// ################################## For Update Restaurant Availability  ##################################
if ($type == "UpdateRestaurantAvailability") {
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : "";
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Company';
    $eAvailable = isset($_REQUEST["eAvailable"]) ? $_REQUEST["eAvailable"] : "Yes";
    $CALL_TYPE = isset($_REQUEST["CALL_TYPE"]) ? $_REQUEST["CALL_TYPE"] : "Display"; // Display , Update

    if ($CALL_TYPE == "Display") {
        $sqlc = "SELECT eAvailable FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        $result_company = $obj->MySQLSelect($sqlc);
        $returnArr['Action'] = "1";
        $returnArr['message'] = $result_company[0];

        setDataResponse($returnArr);
    } else {
        $isAllInformationUpdate = "Yes";
        if ($eAvailable == "Yes") {
            //checkmemberemailphoneverification($iCompanyId, $UserType);
            checkmemberphoneverification($iCompanyId, $UserType);
            $sqlc = "SELECT iMaxItemQty,fPrepareTime,fMinOrderValue,vContactName,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,vState,vZip,vFromMonFriTimeSlot1,vToMonFriTimeSlot1,vFromSatSunTimeSlot1,vToSatSunTimeSlot1 FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
            $result_company = $obj->MySQLSelect($sqlc);

            // remove from condition $result_company[0]['fMinOrderValue']
            if ($result_company[0]['iMaxItemQty'] == 0 || $result_company[0]['iMaxItemQty'] == "" || $result_company[0]['fPrepareTime'] == 0 || $result_company[0]['fPrepareTime'] == "" || $result_company[0]['vContactName'] == "" || $result_company[0]['vRestuarantLocation'] == "" || $result_company[0]['vRestuarantLocationLat'] == "" || $result_company[0]['vRestuarantLocationLong'] == "" || $result_company[0]['vCaddress'] == "" || $result_company[0]['vState'] == "" || $result_company[0]['vZip'] == "" || $result_company[0]['vFromMonFriTimeSlot1'] == "" || $result_company[0]['vFromMonFriTimeSlot1'] == "00:00:00" || $result_company[0]['vToMonFriTimeSlot1'] == "" || $result_company[0]['vToMonFriTimeSlot1'] == "00:00:00" || $result_company[0]['vFromSatSunTimeSlot1'] == "" || $result_company[0]['vFromSatSunTimeSlot1'] == "00:00:00" || $result_company[0]['vToSatSunTimeSlot1'] == "" || $result_company[0]['vToSatSunTimeSlot1'] == "00:00:00") {
                $isAllInformationUpdate = "No";
            }
        }

        if ($isAllInformationUpdate == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER";
            $returnArr['isAllInformationUpdate'] = $isAllInformationUpdate;
            setDataResponse($returnArr);
        }

        $sql = "SELECT count(cu.cuisineId) as cnt FROM cuisine as cu INNER JOIN company_cuisine as ccu ON ccu.cuisineId=cu.cuisineId INNER JOIN company cmp ON ccu.iCompanyId=cmp.iCompanyId WHERE ccu.iCompanyId = $iCompanyId AND cu.eStatus = 'Active'";
        $db_cuisine = $obj->MySQLSelect($sql);
        if ($db_cuisine[0]['cnt'] <= 0) {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT";
            setDataResponse($returnArr);
        }

        $CheckRideDeliveryFeatureDisable_Arr = CheckRideDeliveryFeatureDisable(); //Checked By HJ On 10-01-2019 As Per Discuss WIth KS Sir For Solve Bug
        $eShowDeliverAllVehicles = $CheckRideDeliveryFeatureDisable_Arr['eShowDeliverAllVehicles'];
        if ($eShowDeliverAllVehicles == "Yes") {
            $CompanyDetailsArr = getCompanyDetails($iCompanyId, 0, "No", "");
            $CompanyFoodDataCount = $CompanyDetailsArr['CompanyFoodDataCount'];
            if ($CompanyFoodDataCount == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT";
                setDataResponse($returnArr);
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_DELIVER_ALL_SERVICE_DISABLE_TXT";
            setDataResponse($returnArr);
        }

        $where = " iCompanyId = '$iCompanyId'";
        $Data_update_Companies['eAvailable'] = $eAvailable;
        $Company_Update_id = $obj->MySQLQueryPerform("company", $Data_update_Companies, 'update', $where);
        if ($Company_Update_id) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_INFO_UPDATED_TXT";
            $returnArr['isAllInformationUpdate'] = $isAllInformationUpdate;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER";
            $returnArr['isAllInformationUpdate'] = $isAllInformationUpdate;
        }


        setDataResponse($returnArr);
    }
}

// ################################## For Update Restaurant Availability  ##################################
// ################################## For Update Restaurant Store Settings  ##################################
if ($type == "UpdateDisplayRestaurantStoreSettings") {
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : "";
    $UserType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Company';
    $CALL_TYPE = isset($_REQUEST["CALL_TYPE"]) ? $_REQUEST["CALL_TYPE"] : "Display"; // Display , Update
    $vScreenName = isset($_REQUEST["vScreenName"]) ? $_REQUEST["vScreenName"] : "StoreSetting"; // Order , StoreSetting
    $eDriverOption = isset($_REQUEST["eDriverOption"]) ? $_REQUEST["eDriverOption"] : "All"; // Order , StoreSetting
    $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : "EN"; // Order , StoreSetting
    $eTakeAway = isset($_REQUEST["eTakeAway"]) ? $_REQUEST["eTakeAway"] : "No"; // Order , StoreSetting
    $sql = "SELECT vName,vSymbol,Ratio FROM  `currency` WHERE  `eDefault` = 'Yes' ";
    $defCurrencyValues = $obj->MySQLSelect($sql);
    $vCurrency = $defCurrencyValues[0]['vName'];
    $vCurrencySymbol = $defCurrencyValues[0]['vSymbol'];
    $returnArr['vCurrency'] = $vCurrency;
    $returnArr['vCurrencySymbol'] = $vCurrencySymbol;
    if ($vScreenName == "StoreSetting") {
        if ($CALL_TYPE == "Display") {
            $langage_lblData = getLanguageLabelsArr($vGeneralLang, "1", $iServiceId);
            $sqlc = "SELECT eDriverOption,iMaxItemQty,eAvailable,fPrepareTime,fMinOrderValue,eTakeaway FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
            $result_company = $obj->MySQLSelect($sqlc);
            $result_company[0]['iMaxItemQty'] = ($result_company[0]['iMaxItemQty'] > 0) ? $result_company[0]['iMaxItemQty'] : "";
            $result_company[0]['fPrepareTime'] = ($result_company[0]['fPrepareTime'] > 0) ? $result_company[0]['fPrepareTime'] : "";
            $result_company[0]['fMinOrderValue'] = ($result_company[0]['fMinOrderValue'] > 0) ? $result_company[0]['fMinOrderValue'] : "";
            $result_company[0]['eDriverOptionLabel'] = $langage_lblData['LBL_BOTH_DELIEVERY_DRIVERS'];
            //echo "<pre>";print_r($langage_lblData);die;
            if ($result_company[0]['eDriverOption'] == "Personal") {
                $result_company[0]['eDriverOptionLabel'] = $langage_lblData['LBL_PERSONAL_DELIVERY_DRIVER'];
            } else if ($result_company[0]['eDriverOption'] == "Site") {
                $result_company[0]['eDriverOptionLabel'] = $langage_lblData['LBL_SITE_DELIVERY_DRIVER'];
            }
            $result_company[0]['eTakeAway'] = ($result_company[0]['eTakeaway'] == "Yes") ? "Yes" : "No";

            //echo "<pre>";print_r($result_company);die;
            $returnArr['Action'] = "1";
            $returnArr['message'] = $result_company[0];
            setDataResponse($returnArr);
        } else {
            $iMaxItemQty = isset($_REQUEST["iMaxItemQty"]) ? $_REQUEST["iMaxItemQty"] : "";
            // $eAvailable = isset($_REQUEST["eAvailable"]) ? $_REQUEST["eAvailable"] : "Yes";
            $fPrepareTime = isset($_REQUEST["fPrepareTime"]) ? $_REQUEST["fPrepareTime"] : "";
            $fMinOrderValue = isset($_REQUEST["fMinOrderValue"]) ? $_REQUEST["fMinOrderValue"] : "";
            if ($eAvailable == "Yes") {
                // checkmemberemailphoneverification($iCompanyId, $UserType);
            }
            $Data_update_Companies = array();
            $where = " iCompanyId = '$iCompanyId'";
            $Data_update_Companies['iMaxItemQty'] = $iMaxItemQty;
            // $Data_update_Companies['eAvailable'] = $eAvailable;
            $Data_update_Companies['fPrepareTime'] = $fPrepareTime;
            $Data_update_Companies['fMinOrderValue'] = $fMinOrderValue;
            $Data_update_Companies['eDriverOption'] = $eDriverOption;
            $Data_update_Companies['eTakeaway'] = $eTakeAway;
            $Company_Update_id = $obj->MySQLQueryPerform("company", $Data_update_Companies, 'update', $where);
            if ($Company_Update_id) {
                $returnArr['Action'] = "1";
                $returnArr['message'] = "LBL_INFO_UPDATED_TXT";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRY_AGAIN_LATER";
            }
            setDataResponse($returnArr);
        }
    } else {
        if ($CALL_TYPE == "Display") {
            $sqlc = "SELECT eAvailable FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
            $result_company = $obj->MySQLSelect($sqlc);
            $returnArr['Action'] = "1";
            $returnArr['message'] = $result_company[0];

            setDataResponse($returnArr);
        } else {
            $eAvailable = isset($_REQUEST["eAvailable"]) ? $_REQUEST["eAvailable"] : "Yes";
            if ($eAvailable == "Yes") {
                checkmemberemailphoneverification($iCompanyId, $UserType);
            }

            $where = " iCompanyId = '$iCompanyId'";
            $Data_update_Companies['eAvailable'] = $eAvailable;
            $Company_Update_id = $obj->MySQLQueryPerform("company", $Data_update_Companies, 'update', $where);
            if ($Company_Update_id) {
                $returnArr['Action'] = "1";
                $returnArr['message'] = "LBL_INFO_UPDATED_TXT";
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRY_AGAIN_LATER";
            }


            setDataResponse($returnArr);
        }
    }
}

// ################################## For Update Restaurant Store Settings  ##################################
if ($type == "GetExistingOrderDetails") {
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iCompanyId = isset($_REQUEST["iCompanyId"]) ? $_REQUEST["iCompanyId"] : '';
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
    $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
    $vSymbol = $UserDetailsArr['currencySymbol'];
    $priceRatio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $query = "SELECT * FROM order_details WHERE iOrderId = '" . $iOrderId . "'";
    $orderDetails = $obj->MySQLSelect($query);
    $Data = array();
    for ($i = 0; $i < count($orderDetails); $i++) {
        $Data[$i] = DisplayOrderDetailItemList($orderDetails[$i]['iOrderDetailId'], $iUserId, "Passenger", $iOrderId);
    }

    $returnArr['Action'] = "1";
    $returnArr['message'] = $Data;

    setDataResponse($returnArr);
}

// ################################## Get Details of Existing Orders  ########################################
// ##########################################Send Verification Email #########################################
if ($type == 'sendVerificationEmail') {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    if ($userType == "Passenger") {
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

    $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
    $db_member = $obj->MySQLSelect($sql);
    $vName = $db_member[0]['vName'] . " " . $db_member[0]['vLastName'];
    $vEmail = $db_member[0]['vEmail'];
    $dt = date("Y-m-d H:i:s");
    $random = substr(number_format(time() * rand(), 0, '', ''), 0, 20);
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
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        $returnArr['act_link'] = $Data_Mail['act_link'];
    }


    setDataResponse($returnArr);
}

// #############################Send Verification Email #####################################
// ################### UserLangugaes as per service type ###################
if ($type == "getUserLanguagesAsPerServiceType") {
    $iServiceId = isset($_REQUEST["iServiceId"]) ? $_REQUEST["iServiceId"] : '';
    $languageCode = isset($_REQUEST["LanguageCode"]) ? $_REQUEST['LanguageCode'] : '';
    $returnArr = array();
    $returnArr['changeLangCode'] = "Yes";
    $returnArr['message'] = getLanguageLabelsArr($languageCode, "1", $iServiceId);
    $returnArr['vLanguageCode'] = $languageCode;
    $Data_checkLangCode = $obj->MySQLSelect("SELECT eDirectionCode,vGMapLangCode FROM language_master WHERE `vCode` = '" . $languageCode . "' ");
    $langType = "ltr";
    $vGMapLangCode = "en";
    if (count($Data_checkLangCode) > 0) {
        $langType = $Data_checkLangCode[0]['eDirectionCode'];
        $vGMapLangCode = $Data_checkLangCode[0]['vGMapLangCode'];
    }
    $returnArr['langType'] = $langType;
    $returnArr['vGMapLangCode'] = $vGMapLangCode;
    $returnArr['Action'] = "1";

    $storeCatArr = json_decode(serviceCategories, true);
    $systemStoreEnable = checkSystemStoreSelection();
    if ($systemStoreEnable > 0) {
        for ($g = 0; $g < count($storeCatArr); $g++) {
            $storeData = $generalobj->getStoreDataForSystemStoreSelection($storeCatArr[$g]['iServiceId']);
            $iCompanyId = $storeData['iCompanyId'];
            $storeData['ispriceshow'] = $storeCatArr[$g]['iServiceId'];
            $storeCatArr[$g]['iCompanyId'] = $iCompanyId;
            $storeCatArr[$g]['STORE_DATA'] = $storeData;
            $storeCatArr[$g]['STORE_ID'] = $iCompanyId;
        }
        if (count($storeCatArr) == 1) {
            $companyData = $generalobj->getStoreDataForSystemStoreSelection($storeCatArr[0]['iServiceId']);
            $returnArr['STORE_ID'] = $companyData['iCompanyId'];
            $returnArr['ispriceshow'] = $storeCatArr[0]['ispriceshow'];
        } else {
            $companyData = $generalobj->getStoreDataForSystemStoreSelection(0);
            $returnArr['STORE_ID'] = $companyData[0]['iCompanyId'];
        }

        $returnArr['StoreSelectionData'] = $storeCatArr;
    }

    setDataResponse($returnArr);
}

// ################### UserLangugaes as per service type ###################
// ##############################Add Money into wallet by charge credit card#################
if ($type == "addMoneyUserWalletByChargeCard") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver
    $fAmount = isset($_REQUEST["fAmount"]) ? $_REQUEST["fAmount"] : '';
    $vStripeToken = isset($_REQUEST["vStripeToken"]) ? $_REQUEST["vStripeToken"] : '';

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    $vFlutterWaveToken = isset($_REQUEST["vFlutterWaveToken"]) ? $_REQUEST["vFlutterWaveToken"] : '';
    $txref = isset($_REQUEST["txref"]) ? $_REQUEST["txref"] : '';
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */

    if ($eMemberType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iUserId = "iUserId";
        $eUserType = "Rider";
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
    } else {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iUserId = "iDriverId";
        $eUserType = "Driver";
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
    }

    $Ratio = $UserDetailsArr['Ratio'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $vLang = $UserDetailsArr['vLang'];
    $userCurrencyRatio = $Ratio;
    $walletamount = round($fAmount / $userCurrencyRatio, 2);
    $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
    $currencyCode = $DefaultCurrencyData[0]['vName'];
    $currencyratio = $DefaultCurrencyData[0]['Ratio'];
    $price = $fAmount * $currencyratio;
    $price_new = $walletamount * 100;
    $price_new = round($price_new);
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    if ($vStripeToken == "" && $APP_PAYMENT_METHOD != "Flutterwave") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    if ($txref == "" && $APP_PAYMENT_METHOD == "Flutterwave") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */

    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $iTripId = 0;

    // $tDescription = "Amount credited";
    $tDescription = '#LBL_AMOUNT_CREDIT_BY_USER#';
    $ePaymentStatus = 'Unsettelled';

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    if ($vStripeToken != "" && $APP_PAYMENT_METHOD != "Flutterwave") {
        try {
            $charge_create = Stripe_Charge::create(array(
                        "amount" => $price_new,
                        "currency" => $currencyCode,
                        "source" => $vStripeToken,
                        "description" => $tDescription
            ));
            $details = json_decode($charge_create);
            $result = get_object_vars($details);

            // echo "<pre>";print_r($result);exit;
            if ($result['status'] == "succeeded" && $result['paid'] == "1") {
                $generalobj->InsertIntoUserWallet($iMemberId, $eUserType, $walletamount, 'Credit', 0, $eFor, $tDescription, $ePaymentStatus, $dDate, 0, $result['id']);

                // $user_available_balance = $generalobj->get_user_available_balance($iMemberId,$eUserType);
                $user_available_balance = $generalobj->get_user_available_balance_app_display($iMemberId, $eUserType);
                $returnArr["Action"] = "1";

                // $returnArr["MemberBalance"] = strval($generalobj->userwalletcurrency(0,$user_available_balance,$userCurrencyCode));
                $returnArr["MemberBalance"] = strval($user_available_balance);
                $returnArr['message1'] = "LBL_WALLET_MONEY_CREDITED";
                if ($eMemberType != "Driver") {
                    $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
                } else {
                    $returnArr['message'] = getDriverDetailInfo($iMemberId);
                }

                setDataResponse($returnArr);
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_WALLET_MONEY_CREDITED_FAILED";
                setDataResponse($returnArr);
            }
        } catch (Exception $e) {

            // echo "<pre>";print_r($e);exit;
            $error3 = $e->getMessage();
            $returnArr["Action"] = "0";
            $returnArr['message'] = $error3;
        }
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */

    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    if ($txref != "" && $APP_PAYMENT_METHOD == "Flutterwave") {
        $updateQuery = 0;
        $verifiedData = flutterwave_verify($txref);
        if (isset($verifiedData['token']) && $verifiedData['token'] != "") {
            $updateQuery = $chargeAmtFlag = 1;
            $updateData['vFlutterWaveToken'] = $verifiedData['token'];
            $updateData['vCreditCard'] = $verifiedData['card'];
            $chargeAmt = $verifiedData['chargedAmt'];
            $chargedCurrency = $verifiedData['chargedCurrency'];
        }
        if ($updateQuery > 0) {
            $generalobj->InsertIntoUserWallet($iMemberId, $eUserType, $walletamount, 'Credit', 0, $eFor, $tDescription, $ePaymentStatus, $dDate, 0, $result['id']);

            $user_available_balance = $generalobj->get_user_available_balance_app_display($iMemberId, $eUserType);
            $returnArr["Action"] = "1";

            $returnArr["MemberBalance"] = strval($user_available_balance);
            $returnArr['message1'] = "LBL_WALLET_MONEY_CREDITED";
            if ($eMemberType != "Driver") {
                $returnArr['message'] = getPassengerDetailInfo($iMemberId, "", "");
            } else {
                $returnArr['message'] = getDriverDetailInfo($iMemberId);
            }
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_WALLET_MONEY_CREDITED_FAILED";
        }
    }
    /* Added By PM On 09-12-2019 For Flutterwave Code Start */
    setDataResponse($returnArr);
}

#################################Add Money into wallet by charge credit card##########################
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
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_RECORDS_FOUND1";
        $returnArr['vCurrency'] = $validPromoCodesArr['vCurrency'];
        $returnArr['vSymbol'] = $validPromoCodesArr['vSymbol'];

        setDataResponse($returnArr);
    }
}
#####################################DisplayCouponList ###########################################################
####################### For Prescription required start added by SP #################################
if ($type == "PrescriptionImages") { // used for uploading and delete images 
    global $generalobj, $tconfig;

    $action_type = isset($_REQUEST["action_type"]) ? $_REQUEST["action_type"] : 'ADD';
    $image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
    $iImageId = isset($_REQUEST["iImageId"]) ? $_REQUEST["iImageId"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
//echo $image_name."aaaaaaa".$iImageId."bbbbB".$image_object; exit;
    if ($action_type == "ADD") {
        if ($image_name != "") {
            $Photo_Gallery_folder = $tconfig['tsite_upload_prescription_image_path'];

            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }

            $imgext = explode('.', $image_name);
            $unique = uniqid('', true);
            $file_name = substr($unique, strlen($unique) - 4, strlen($unique));
            $new_imagename = $file_name . "." . $imgext[1];

            $vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $new_imagename, $prefix = '', $vaildExt = "jpg,jpeg,gif,png,pdf,doc,docx");
            $vImageName = $vFile[0];
            $Data_update_images['vImage'] = $vImageName;
        }
        $Data_update_images['iUserId'] = $iUserId;
        $Data_update_images['tAddedDate'] = @date("Y-m-d H:i:s");

        $id = $obj->MySQLQueryPerform("prescription_images", $Data_update_images, 'insert');
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_IMAGE_UPLOAD_SUCCESS_NOTE";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else if ($action_type == "DELETE" && $iImageId != "") {
        $Photo_Gallery_folder = $tconfig['tsite_upload_prescription_image_path'];

        $OldImageName = get_value('prescription_images', 'vImage', 'iImageId', $iImageId, '', 'true');

        if ($OldImageName != '') {
            unlink($Photo_Gallery_folder . $OldImageName);
        }

        $sql = "DELETE FROM prescription_images WHERE `iImageId`='" . $iImageId . "'";
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

if ($type == "getPrescriptionImages") { //get prescription image data, here when user uploaded images at that time order id 0, then when placce imag using that image then it will be updated orderid...
    global $tconfig;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST['iUserId'] : '';
    $PreviouslyUploaded = isset($_REQUEST["PreviouslyUploaded"]) ? $_REQUEST['PreviouslyUploaded'] : ''; //check whether to get previously uploaded images
    $getImages = array();
    if ($PreviouslyUploaded == 1) { // here it is displayed in prescription uploaded by you/in prescription history
        $getImagearray = $obj->MySQLSelect("SELECT iImageId FROM prescription_images WHERE eStatus='Active' AND iUserId='" . $iUserId . "' AND order_id != 0");
        foreach ($getImagearray as $key => $value) {
            $getImages_duplicate = $obj->MySQLSelect("SELECT iImageId FROM prescription_images WHERE duplicate_id = '" . $value['iImageId'] . "'");
            foreach ($getImages_duplicate as $key => $value) {
                if (!empty($value['iImageId']))
                    $except_imagearray .= $value['iImageId'] . ",";
            }
        }
        $except_imagearray = rtrim($except_imagearray, ',');
        if (!empty($except_imagearray)) {
            $getImages = $obj->MySQLSelect("SELECT * FROM prescription_images WHERE eStatus='Active' AND iUserId='" . $iUserId . "' AND order_id != 0 AND iImageId NOT IN (" . $except_imagearray . ")");
        } else {
            $getImages = $obj->MySQLSelect("SELECT * FROM prescription_images WHERE eStatus='Active' AND iUserId='" . $iUserId . "' AND order_id != 0");
        }
        for ($p = 0; $p < count($getImages); $p++) {
            $getImages[$p]['vImage'] = $tconfig['tsite_upload_prescription_image'] . '/' . $getImages[$p]['vImage'];
        }
    } else { //displaying recent list
        $getImagearray = $obj->MySQLSelect("SELECT iImageId FROM prescription_images WHERE eStatus='Active' AND iUserId='" . $iUserId . "' AND order_id = 0");
        foreach ($getImagearray as $key => $value) {
            $getImages_duplicate = $obj->MySQLSelect("SELECT iImageId FROM prescription_images WHERE duplicate_id = '" . $value['iImageId'] . "'");
            foreach ($getImages_duplicate as $key => $value) {
                if (!empty($value['iImageId']))
                    $except_imagearray .= $value['iImageId'] . ",";
            }
        }
        $except_imagearray = rtrim($except_imagearray, ',');
        if (!empty($except_imagearray)) {
            $getImages = $obj->MySQLSelect("SELECT * FROM prescription_images WHERE eStatus='Active' AND iUserId='" . $iUserId . "' AND order_id = 0 AND iImageId NOT IN (" . $except_imagearray . ")");
        } else {
            $getImages = $obj->MySQLSelect("SELECT * FROM prescription_images WHERE eStatus='Active' AND iUserId='" . $iUserId . "' AND order_id = 0");
        }
        for ($p = 0; $p < count($getImages); $p++) {
            $getImages[$p]['vImage'] = $tconfig['tsite_upload_prescription_image'] . '/' . $getImages[$p]['vImage'];
        }
    }
    if (!empty($getImages)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $getImages;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "";
    }
    setDataResponse($returnArr);
}

if ($type == 'CheckPrescriptionRequired') { //check prescription required or not for the every menu items which is set from the admin side
    $iServiceId = isset($_REQUEST["iServiceId"]) ? $_REQUEST['iServiceId'] : '';
    $iMenuItemId = isset($_REQUEST["iMenuItemId"]) ? $_REQUEST['iMenuItemId'] : '';

    /* $servFields = 'prescription_required'; //check using menuitem only bc using serviceid getting issue in ios(Dhruvin) in cart...
      $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId)[0]['prescription_required'];

      if($ServiceCategoryData=='No') {
      $returnArr['Action'] = "0";
      $returnArr['message'] = "LBL_NO_RECORDS_FOUND1";
      setDataResponse($returnArr);
      } */
    $items = $obj->MySQLSelect("SELECT iMenuItemId FROM menu_items WHERE eStatus='Active' AND iMenuItemId IN(" . $iMenuItemId . ") AND prescription_required = 'Yes'");
    if ((count($items)) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_PRESCRIPTION_UPLOAD";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_NO_RECORDS_FOUND1";
    }
    setDataResponse($returnArr);
}

if ($type == 'PreviouslyUploadedbyYou') { //previously uploaded by you, here when user select image from the history which is he was uploaded before... then we have generated duplicate entry for it in the table..and in the image folder image is copied..in the field duplicate_id..id is image_id
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST['iUserId'] : '';
    $iImageId = isset($_REQUEST["iImageId"]) ? $_REQUEST['iImageId'] : ''; //uploaded from previously uploaded by you
    if (!empty($iImageId)) {
        $Data_update_images['iUserId'] = $iUserId;
        $Data_update_images['tAddedDate'] = @date("Y-m-d H:i:s");

        $getImages = $obj->MySQLSelect("SELECT * FROM prescription_images WHERE eStatus='Active' AND iImageId IN (" . $iImageId . ") AND iUserId = '" . $iUserId . "'"); // put in for the multiple image select

        foreach ($getImages as $key => $value) { //foreach because if multiple image select
            $getImages_already = $obj->MySQLSelect("SELECT * FROM prescription_images WHERE duplicate_id = '" . $value['iImageId'] . "' AND order_id = 0"); //check if in history select img123, then again in attach prescription select img123 then it is not added, in recent list displayed images one time only..order id = 0 because if it is from previous order then it is first time..then again add that item then for that item orderid will be 0 

            if (empty($getImages_already)) {
                $imgext = explode('.', $value['vImage']);
                $Data_update_images['duplicate_id'] = $value['iImageId'];
                //$new_imagename = uniqid().".".$imgext[1];

                $unique = uniqid('', true);
                $file_nameTmp = substr($unique, strlen($unique) - 4, strlen($unique));

                $new_imagename = $file_nameTmp . "_" . date("YmdHis") . "." . $imgext[1];

                $copyfile = copy($tconfig['tsite_upload_prescription_image_path'] . '/' . $value['vImage'], $tconfig['tsite_upload_prescription_image_path'] . '/' . $new_imagename);
                if ($copyfile == 1) {
                    $Data_update_images['vImage'] = $new_imagename;
                }
                $id = $obj->MySQLQueryPerform("prescription_images", $Data_update_images, 'insert');
            } else {
                $id = 1;
            }
        }
        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = "LBL_IMAGE_UPLOAD_SUCCESS_NOTE";
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
if ($type == 'GetOrderPrescriptionImages') { //Get all images from the order(store)...
    global $obj;
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST['iOrderId'] : '';
    $getImages = $obj->MySQLSelect("Select * from prescription_images WHERE order_id = '" . $iOrderId . "'");
    if (!empty($getImages)) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_IMAGE_UPLOAD_SUCCESS_NOTE";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
if ($type == 'removePrescriptionImagesForCart') { //when remove all items from the cart, it will remove prescription images
    global $obj;
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST['iUserId'] : '';
    $sql = "DELETE FROM prescription_images WHERE `iUserId`='" . $iUserId . "' AND `order_id` = 0";
    $id = $obj->sql_query($sql);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_IMAGE_DELETE_SUCCESS_NOTE";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}

function setorderid_for_prescription($iUserId, $iOrderId) { //when place order in prescription images table orderid is updated
    global $obj;
    if (!empty($iUserId) && !empty($iOrderId)) {
        $updateQuery = "UPDATE prescription_images SET order_id = '" . $iOrderId . "' WHERE iUserId='" . $iUserId . "' AND order_id = 0";
        //$obj->MySQLSelect("UPDATE prescription_images SET order_id = '".$iOrderId."' WHERE iUserId='" . $iUserId . "'");
        $obj->sql_query($updateQuery);
    }
    return true;
}

####################### For Prescription required end added by SP #################################

if ($type == "updateThermalPrintStatus") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST['iMemberId'] : '';
    $eThermalAutoPrint = isset($_REQUEST["eThermalAutoPrint"]) ? $_REQUEST['eThermalAutoPrint'] : '';
    $eThermalPrintEnable = isset($_REQUEST["eThermalPrintEnable"]) ? $_REQUEST['eThermalPrintEnable'] : '';

    if (isset($iMemberId) && !empty($iMemberId) && !empty($eThermalAutoPrint) && !empty($eThermalPrintEnable)) {
        $where = " iCompanyId = '" . $iMemberId . "'";
        $data_company['eThermalPrintEnable'] = $eThermalPrintEnable;
        $data_company['eThermalAutoPrint'] = $eThermalAutoPrint;
        $obj->MySQLQueryPerform("company", $data_company, 'update', $where);
        $returnArr['Action'] = "1";
        $returnArr['message'] = getCompanyDetailInfo($iMemberId);
        $returnArr['message1'] = "LBL_INFO_UPDATED_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
// ##########################Call Masking##########################################################
if ($type == "getCallMaskNumber") {
    global $generalobj, $tconfig;
    $returnArr = array();
    $iTripId = isset($_REQUEST['iTripid']) ? $_REQUEST['iTripid'] : '';
    $GeneralDeviceType = isset($_REQUEST['GeneralDeviceType']) ? $_REQUEST['GeneralDeviceType'] : '';
    $UserType = isset($_REQUEST['UserType']) ? $_REQUEST['UserType'] : '';

    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";


    setDataResponse($returnArr);
}
// ###########################call masking Ends##########################################################

if ($type == 'getReceiptOrder') {
    $iOrderId = isset($_REQUEST['iOrderId']) ? clean($_REQUEST['iOrderId']) : '';

    if (empty($iServiceId))
        $iServiceId = 1;
    $value = $generalobj->orderemaildataRecipt($iOrderId, 'Passenger', $iServiceId);

    if ($value == true || $value == "true" || $value == "1") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_CHECK_INBOX_TXT";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_FAILED_SEND_RECEIPT_EMAIL_TXT";
    }
    setDataResponse($returnArr);
}

if ($type == "fetchAPIDetails") {
    fetchAPIDetails();
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
    } else {
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
    } else {
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

$obj->MySQLClose();
?>
