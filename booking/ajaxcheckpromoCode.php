<?php
include_once('../common.php');	
$generalobj->check_member_login();

include('../app_common_functions.php');

$promoCode = isset($_REQUEST['PromoCode']) ? $_REQUEST['PromoCode'] : '';
$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';

$validPromoCodesArr = getValidPromoCodes();
if (!empty($validPromoCodesArr) && !empty($validPromoCodesArr['CouponList']) && count($validPromoCodesArr['CouponList']) > 0) {
    $returnArr['Action'] = "1"; // code is valid
    $returnArr["message"] = "LBL_PROMO_APPLIED";
    echo json_encode($returnArr);
    exit;
} else {
    $returnArr['Action'] = "0";
    $returnArr["message"] = "LBL_INVALID_PROMOCODE";
    echo json_encode($returnArr);
    exit;
}

/*$curr_date = @date("Y-m-d");
$promoCode = strtoupper($promoCode);

$sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '" . $promoCode . "' ORDER BY iCouponId ASC LIMIT 0,1";
$data = $obj->MySQLSelect($sql);
//print_R($data); eSystemType
if (count($data) > 0)
{
    $sql = "select iTripId from trips where vCouponCode = '$promoCode' and iActive = 'Finished' and iUserId='$iUserId'";
    $data_coupon = $obj->MySQLSelect($sql);
    if (!empty($data_coupon))
    {
        $returnArr['Action'] = "01"; // code is already used one time
        $returnArr["message"] = "LBL_PROMOCODE_ALREADY_USED";
        echo json_encode($returnArr);
        exit;
    }
    else
    {
        $eValidityType = $data[0]['eValidityType'];
        $iUsageLimit = $data[0]['iUsageLimit'];
        $iUsed = $data[0]['iUsed'];
        if ($iUsageLimit <= $iUsed)
        {
            $returnArr['Action'] = "0"; // code is invalid due to Usage Limit
            $returnArr["message"] = "LBL_PROMOCODE_COMPLETE_USAGE_LIMIT";
            echo json_encode($returnArr);
            exit;
        }
        if ($eValidityType == "Permanent")
        {
            $returnArr['Action'] = "1"; // code is valid
            $returnArr["message"] = "LBL_PROMO_APPLIED";
            echo json_encode($returnArr);
            exit;
        }
        else
        {
            $dActiveDate = $data[0]['dActiveDate'];
            $dExpiryDate = $data[0]['dExpiryDate'];
            if ($dActiveDate <= $curr_date && $dExpiryDate >= $curr_date)
            {
                $returnArr['Action'] = "1"; // code is valid
                $returnArr["message"] = "LBL_PROMO_APPLIED";
                echo json_encode($returnArr);
                exit;
            }
            else
            {
                $returnArr['Action'] = "0"; // code is invalid due to expiration
                $returnArr["message"] = "LBL_PROMOCODE_EXPIRED";
                echo json_encode($returnArr);
                exit;
            }
        }
    }
}
else
{
    $returnArr['Action'] = "0";
    $returnArr["message"] = "LBL_INVALID_PROMOCODE";
    echo json_encode($returnArr);
    exit;
}*/
?>
