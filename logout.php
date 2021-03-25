<?php

include_once("common.php");
session_start();

unset($_SESSION['sess_iUserId']);
unset($_SESSION["sess_iCompanyId"]);
unset($_SESSION["sess_vName"]);
unset($_SESSION["sess_vEmail"]);
unset($_SESSION["sess_user"]);

unset($_SESSION['sess_iMemberId']);
unset($_SESSION['sess_eGender']);
unset($_SESSION['sess_vImage']);
unset($_SESSION['fb_user']);

unset($_SESSION['linkedin_user']);
unset($_SESSION['oauth_access_token']);
unset($_SESSION['oauth_verifier']);
unset($_SESSION['requestToken']);
unset($_SESSION['sess_currentpage_url_ub']);
$_SESSION['sess_currentpage_url_ub'] = "";

//Added By HJ On 29-07-2019 For Manage Manual Store Order All Session Start
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}
$userSession = "MANUAL_ORDER_" . strtoupper($fromOrder);
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
$orderCurrencyNameSession = "MANUAL_ORDER_CURRENCY_NAME_" . strtoupper($fromOrder);
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_" . strtoupper($fromOrder);
$orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);
$orderUserSession = "MANUAL_ORDER_USER_" . strtoupper($fromOrder);
$orderUserNameSession = "MANUAL_ORDER_USER_NAME_" . strtoupper($fromOrder);
$orderCompanyNameSession = "MANUAL_ORDER_COMPANY_NAME_" . strtoupper($fromOrder);
$orderUserEmailSession = "MANUAL_ORDER_USER_EMAIL_" . strtoupper($fromOrder);
$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);
$loggedInUserId = 0;
if (isset($_SESSION['sess_iUserId']) && $_SESSION['sess_iUserId'] != "") {
    $loggedInUserId = $_SESSION['sess_iUserId'];
}
$changedUser = "";
//echo "<pre>";print_R($_SERVER['REDIRECT_URL']);die;

$page_name_mr = "restaurant_listing";
if (isset($_REQUEST['page']) && $_REQUEST['page'] != "") {
    $page_name_mr = $_REQUEST['page'];
}
if ($loggedInUserId > 0 && $fromOrder != "user") {
    $changedUser = "user";
    changeManualOrderSession($fromOrder, $changedUser);
} else if ($loggedInUserId == 0 && $fromOrder == "user") {
    $changedUser = "guest";
    changeManualOrderSession($fromOrder, $changedUser);
}
if ($page_name_mr == "user_info") {
    $screenName = "order-items";
} else if ($page_name_mr == "customer_info") {
    $screenName = "user-order-information";
} else if ($page_name_mr == "restaurant_place-order") {
    $screenName = "store-order";
} else if ($page_name_mr == "restaurant_menu") {
    $screenName = "store-items";
} else if ($page_name_mr == "restaurant_listing") {
    $screenName = "store-listing";
}

function changeManualOrderSession($existSession, $changeSession) {
    session_start();
    $existSession = strtoupper($existSession);
    $changeSession = strtoupper($changeSession);
    $userSession = "MANUAL_ORDER_";
    $orderDetailsSession = "ORDER_DETAILS_";
    $orderServiceSession = "MAUAL_ORDER_SERVICE_";
    $orderUserIdSession = "MANUAL_ORDER_USERID_";
    $orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_";
    $orderAddressSession = "MANUAL_ORDER_ADDRESS_";
    $orderCouponSession = "MANUAL_ORDER_PROMOCODE_";
    $orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_";
    $orderCurrencyNameSession = "MANUAL_ORDER_CURRENCY_NAME_";
    $orderLatitudeSession = "MANUAL_ORDER_LATITUDE_";
    $orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_";
    $orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_";
    $orderDataSession = "MANUAL_ORDER_DATA_";
    $orderUserSession = "MANUAL_ORDER_USER_";
    $orderUserNameSession = "MANUAL_ORDER_USER_NAME_";
    $orderCompanyNameSession = "MANUAL_ORDER_COMPANY_NAME_";
    $orderUserEmailSession = "MANUAL_ORDER_USER_EMAIL_";
    $orderStoreIdSession = "MANUAL_ORDER_STORE_ID_";

    $_SESSION[$userSession . $changeSession] = $_SESSION[$userSession . $existSession];
    $_SESSION[$orderDetailsSession . $changeSession] = $_SESSION[$orderDetailsSession . $existSession];
    $_SESSION[$orderServiceSession . $changeSession] = $_SESSION[$orderServiceSession . $existSession];
    $_SESSION[$orderUserIdSession . $changeSession] = $_SESSION[$orderUserIdSession . $existSession];
    $_SESSION[$orderAddressIdSession . $changeSession] = $_SESSION[$orderAddressIdSession . $existSession];
    $_SESSION[$orderAddressSession . $changeSession] = $_SESSION[$orderAddressSession . $existSession];
    $_SESSION[$orderCouponSession . $changeSession] = $_SESSION[$orderCouponSession . $existSession];
    $_SESSION[$orderCouponNameSession . $changeSession] = $_SESSION[$orderCouponNameSession . $existSession];
    $_SESSION[$orderCurrencyNameSession . $changeSession] = $_SESSION[$orderCurrencyNameSession . $existSession];
    $_SESSION[$orderLatitudeSession . $changeSession] = $_SESSION[$orderLatitudeSession . $existSession];
    $_SESSION[$orderLongitudeSession . $changeSession] = $_SESSION[$orderLongitudeSession . $existSession];
    $_SESSION[$orderServiceNameSession . $changeSession] = $_SESSION[$orderServiceNameSession . $existSession];
    $_SESSION[$orderDataSession . $changeSession] = $_SESSION[$orderDataSession . $existSession];
    $_SESSION[$orderUserSession . $changeSession] = $_SESSION[$orderUserSession . $existSession];
    $_SESSION[$orderUserNameSession . $changeSession] = $_SESSION[$orderUserNameSession . $existSession];
    $_SESSION[$orderCompanyNameSession . $changeSession] = $_SESSION[$orderCompanyNameSession . $existSession];
    $_SESSION[$orderUserEmailSession . $changeSession] = $_SESSION[$orderUserEmailSession . $existSession];
    $_SESSION[$orderStoreIdSession . $changeSession] = $_SESSION[$orderStoreIdSession . $existSession];
}

//echo "<pre>";print_R($_SESSION);die;
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
//echo "<pre>";print_R($_SESSION);die;
if ($changedUser != "") {
    header('Location:' . $screenName . "?order=" . $changedUser);
}
//unset($_SESSION["sess_user_mr"]);
//Added By HJ On 29-07-2019 For Manage Manual Store Order All Session End
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach ($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time() - 1000);
        setcookie($name, '', time() - 1000, '/');
    }
}
setcookie('login_redirect_url_user', $_SERVER['HTTP_REFERER'], time()+2*24*60*60);
// Check default custom parameters - added by KS
$defaultSessionParam_arr = array();

if(!empty($_SESSION['CUS_APP_TYPE'])){
	$defaultSessionParam_arr['CUS_APP_TYPE'] = $_SESSION['CUS_APP_TYPE'];
}

if(!empty($_SESSION['CUS_SITE_TYPE'])){
	$defaultSessionParam_arr['CUS_SITE_TYPE'] = $_SESSION['CUS_SITE_TYPE'];
}

if(!empty($_SESSION['CUS_PACKAGE_TYPE'])){
	$defaultSessionParam_arr['CUS_PACKAGE_TYPE'] = $_SESSION['CUS_PACKAGE_TYPE'];
}

if(!empty($_SESSION['CUS_PARENT_UFX_CATID'])){
	$defaultSessionParam_arr['CUS_PARENT_UFX_CATID'] = $_SESSION['CUS_PARENT_UFX_CATID'];
}

if(!empty($_SESSION['CUS_CUBE_X_THEME'])){
	$defaultSessionParam_arr['CUS_CUBE_X_THEME'] = $_SESSION['CUS_CUBE_X_THEME'];
}

if(!empty($_SESSION['FOOD_ONLY'])){
	$defaultSessionParam_arr['FOOD_ONLY'] = $_SESSION['FOOD_ONLY'];
}

if(!empty($_SESSION['ONLYDELIVERALL'])){
	$defaultSessionParam_arr['ONLYDELIVERALL'] = $_SESSION['ONLYDELIVERALL'];
}

session_destroy();
  
$query_data = "";
if(!empty($defaultSessionParam_arr) && count($defaultSessionParam_arr) > 0){
	$query_data = "?".http_build_query($defaultSessionParam_arr);
}

if (isset($_REQUEST['depart']) && $_REQUEST['depart'] == 'mobi') {
    header("Location:mobi");
} else {
    if($generalobj->checkXThemOn() == 'Yes') {
        header("Location: sign-in".$query_data);
    } else {
        header("Location:".$cjSignIn.$query_data);
    }
}
exit;
?>
