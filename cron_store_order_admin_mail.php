<?php

//Create By HJ On 22-10-2019 For Send mail to admin when Not Accept Order of Delivery Driver Addon
include_once("common.php");
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
include_once("app_common_functions.php");
if ($ENABLE_AUTO_ACCEPT_STORE_ORDER == "Yes") {
//echo $ENABLE_AUTO_ACCEPT_STORE_ORDER;die;
    $getOrderData = $obj->MySQLSelect("SELECT vOrderNo,dCronExpiredDate,iOrderId FROM orders WHERE dCronExpiredDate != '0' AND iStatusCode = '2' AND eSentMailAdmin='No' AND tDriverIds != ''");
//echo "<pre>";print_r($getOrderData);die;
    if (count($getOrderData) > 0) {
        for ($i = 0; $i < count($getOrderData); $i++) {
            $currentTime = time();
            $dCronExpiredDate = $getOrderData[$i]['dCronExpiredDate'];
            $vOrderNo = $getOrderData[$i]['vOrderNo'];
            if ($currentTime > $dCronExpiredDate) {
                //echo $currentTime . "===" . $dCronExpiredDate;die;
                sendMailToAdmin($vOrderNo);
                $obj->sql_query("UPDATE orders SET eSentMailAdmin ='Yes' WHERE iOrderId=" . $getOrderData[$i]['iOrderId']);
            }
        }
    }
}
?>