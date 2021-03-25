<?php

include_once("common.php");
include_once('assets/libraries/configuration.php');
$service_categories = array();
if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
    $service_categories = $serviceCategoriesTmp;
}
if (isset($_POST['SUBMIT'])) {
    //echo "<pre>";print_R($_POST);die;
    $fromOrder = "guest";
    if(isset($_POST['fromOrder']) && $_POST['fromOrder'] != ""){
        $fromOrder = $_POST['fromOrder'];
    }
    $userSession = "MANUAL_ORDER_".strtoupper($fromOrder);
    $orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
    $orderServiceSession = "MAUAL_ORDER_SERVICE_".strtoupper($fromOrder);
    $orderUserIdSession = "MANUAL_ORDER_USERID_".strtoupper($fromOrder);
    $orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_".strtoupper($fromOrder);
    $orderAddressSession = "MANUAL_ORDER_ADDRESS_".strtoupper($fromOrder);
    $orderCouponSession = "MANUAL_ORDER_PROMOCODE_".strtoupper($fromOrder);
    $orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
    $orderCurrencyNameSession = "MANUAL_ORDER_CURRENCY_NAME_".strtoupper($fromOrder);
    $orderLatitudeSession = "MANUAL_ORDER_LATITUDE_".strtoupper($fromOrder);
    $orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_".strtoupper($fromOrder);
    $orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_".strtoupper($fromOrder);
    $orderDataSession = "MANUAL_ORDER_DATA_".strtoupper($fromOrder);
    $orderUserSession = "MANUAL_ORDER_USER_".strtoupper($fromOrder);
    $orderUserNameSession = "MANUAL_ORDER_USER_NAME_".strtoupper($fromOrder);
    $orderCompanyNameSession = "MANUAL_ORDER_COMPANY_NAME_".strtoupper($fromOrder);
    $orderUserEmailSession = "MANUAL_ORDER_USER_EMAIL_".strtoupper($fromOrder);
    $orderStoreIdSession = "MANUAL_ORDER_STORE_ID_".strtoupper($fromOrder);
    $extraParameter = "?order=" . $fromOrder;
    unset($_SESSION[$orderDetailsSession]);
    unset($_SESSION[$userSession]);
    
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
    //unset($_SESSION["sess_user_mr"]);
    
    unset($_SESSION[$orderUserSession]);
    unset($_SESSION[$orderServiceNameSession]);
    //echo "<pre>";print_r($_POST);die;
    if (isset($_POST['from_long']) && !empty($_POST['from_long'])) {
        if (isset($_SESSION['sess_iUserId']) && !empty($_SESSION['sess_iUserId']) && $_SESSION['sess_user'] == 'rider') {
            $_SESSION[$orderUserIdSession] = $_SESSION['sess_iUserId'];
            $_SESSION[$orderUserNameSession] = $_SESSION['sess_vName'];
            $_SESSION[$orderCompanyNameSession] = "";
            $_SESSION[$orderUserEmailSession] = $_SESSION['sess_vEmail'];
            $_SESSION[$orderAddressSession] = "";
            //$_SESSION["sess_user_mr"] = "rider";
            //$_SESSION[$orderCurrencyNameSession] = $Data['vCurrencyPassenger'];
            $maildata['EMAIL_mr'] = $_SESSION[$orderUserEmailSession];
            $maildata['NAME_mr'] = $_SESSION[$orderUserNameSession];
            $_SESSION[$orderLongitudeSession] = $_POST['from_long'];
            $_SESSION[$orderLatitudeSession] = $_POST['from_lat'];
        } else {
            $_SESSION[$orderUserIdSession] = '';
            $_SESSION[$orderAddressIdSession] = '';
            $_SESSION[$orderLongitudeSession] = $_POST['from_long'];
            $_SESSION[$orderLatitudeSession] = $_POST['from_lat'];
        }
        $_SESSION[$orderAddressSession] = $_POST['vServiceAddress'];
        $_SESSION[$orderServiceSession] = $_POST['serviceid'];
        $_SESSION[$orderServiceNameSession] = $service_categories[($_SESSION[$orderServiceSession] - 1)]['vServiceName'];
        header("Location:store-listing".$extraParameter);
        exit;
    } else {
        header("Location:order-items".$extraParameter);
        exit;
    }
}
?>
