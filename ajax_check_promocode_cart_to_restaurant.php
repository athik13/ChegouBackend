<?php

include_once('common.php');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//echo "asd";die;
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
include_once ('include_generalFunctions_dl.php');
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);

$iServiceId = "1";
$iUserId = $iUserAddressId = "";
if (isset($_SESSION[$orderServiceSession])) {
    $iServiceId = $_SESSION[$orderServiceSession];
}
if (isset($_SESSION[$orderUserIdSession])) {
    $iUserId = $_SESSION[$orderUserIdSession];
}
if (isset($_SESSION[$orderAddressIdSession])) {
    $iUserAddressId = $_SESSION[$orderAddressIdSession];
}
$vLang = $_SESSION['sess_lang'];
$promoCode = isset($_REQUEST['couponCode']) ? clean($_REQUEST['couponCode']) : '';
$UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
$Ratio = $UserDetailsArr['Ratio'];
$curr_date = @date("Y-m-d");
$promoCode = strtoupper($promoCode);
$langage_lbl = getLanguageLabelsArr($vLang, $iServiceId);
// $sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '".$promoCode."' AND iUsageLimit > iUsed AND (eValidityType = 'Permanent' OR dExpiryDate > '$curr_date')";
// $sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '".$promoCode."' AND iUsageLimit > iUsed ORDER BY iCouponId ASC LIMIT 0,1";
$sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '" . $promoCode . "' AND eSystemType IN ('DeliverAll','General') ORDER BY iCouponId ASC LIMIT 0,1";
$data = $obj->MySQLSelect($sql);
if (count($data) > 0) {
    $discountValueType = $data[0]['eType'];
    $discountValue = $data[0]['fDiscount'];
    $discountValue = round(($discountValue * $Ratio), 2);
    $sql = "select iOrderId from orders where vCouponCode = '" . $promoCode . "' and iStatusCode NOT IN(11,12) and iUserId='$iUserId'";
    $data_coupon = $obj->MySQLSelect($sql);
    if (!empty($data_coupon)) {
        $returnArr['Action'] = "0"; // code is already used one time
        $returnArr["message"] = $langage_lbl["LBL_PROMOCODE_ALREADY_USED"];
        echo json_encode($returnArr);
        exit;
    } else {
        $eValidityType = $data[0]['eValidityType'];
        $iUsageLimit = $data[0]['iUsageLimit'];
        $iUsed = $data[0]['iUsed'];
        if ($iUsageLimit <= $iUsed) {
            $returnArr['Action'] = "0"; // code is invalid due to Usage Limit
            $returnArr["message"] = $langage_lbl["LBL_PROMOCODE_COMPLETE_USAGE_LIMIT"];
            unset($_SESSION[$orderCouponSession]);
            unset($_SESSION[$orderCouponNameSession]);
            echo json_encode($returnArr);
            exit;
        }
        if ($eValidityType == "Permanent") {
            $returnArr['Action'] = "1"; // code is valid
            $returnArr["message"] = $langage_lbl["LBL_PROMO_APPLIED"];
            $returnArr["discountValueType"] = $discountValueType;
            $returnArr["discountValue"] = $discountValue;
            $_SESSION[$orderCouponSession] = $promoCode;
            echo json_encode($returnArr);
            exit;
        } else {
            $dActiveDate = $data[0]['dActiveDate'];
            $dExpiryDate = $data[0]['dExpiryDate'];
            if ($dActiveDate <= $curr_date && $dExpiryDate >= $curr_date) {
                $returnArr['Action'] = "1"; // code is valid
                $returnArr["message"] = $langage_lbl["LBL_PROMO_APPLIED"];
                $returnArr["discountValueType"] = $discountValueType;
                $returnArr["discountValue"] = $discountValue;
                $_SESSION[$orderCouponSession] = $promoCode;
                echo json_encode($returnArr);
                exit;
            } else {
                $returnArr['Action'] = "0"; // code is invalid due to expiration
                $returnArr["message"] = $langage_lbl["LBL_PROMOCODE_EXPIRED"];
                unset($_SESSION[$orderCouponSession]);
                unset($_SESSION[$orderCouponNameSession]);
                echo json_encode($returnArr);
                exit;
            }
        } // languageLabelsArr[
    }
} else {
    $returnArr['Action'] = "0"; // code is invalid
    // $returnArr['Action']="01";// code is used by this user
    $returnArr["message"] = $langage_lbl["LBL_INVALID_PROMOCODE"];
    unset($_SESSION[$orderCouponSession]);
    unset($_SESSION[$orderCouponNameSession]);
    echo json_encode($returnArr);
    exit;
}
//  echo json_encode($returnArr); exit;
?>