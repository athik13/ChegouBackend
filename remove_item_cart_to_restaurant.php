<?php

include_once('common.php');
$responce = array();
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}

$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
$OrderDetails = $_SESSION[$orderDetailsSession];
$count = count($_SESSION[$orderDetailsSession]);
//unset($_SESSION[$orderDetailsSession]);
$menuItemId = $_REQUEST['id'];
$removeid = $_REQUEST['removeid'];
$responce['OrderDetails'] = array();
//echo "<pre>";print_r($OrderDetails);die;
for ($i = 0; $i < $count; $i++) {
    $addoptions = array();
    if ($OrderDetails[$i]['iMenuItemId'] == $menuItemId) {
        $addoptions['iMenuItemId'] = $OrderDetails[$i]['iMenuItemId'];
        $addoptions['iFoodMenuId'] = $OrderDetails[$i]['iFoodMenuId'];
        $addoptions['vOptionId'] = $OrderDetails[$i]['vOptionId'];
        $addoptions['iQty'] = $OrderDetails[$i]['iQty'];
        $addoptions['vAddonId'] = $OrderDetails[$i]['vAddonId'];
        $addoptions['tInst'] = $OrderDetails[$i]['tInst'];
        $addoptions['eFoodType'] = $OrderDetails[$i]['eFoodType'];
        $addoptions['typeitem'] = 'remove';
    } else {
        $addoptions['iMenuItemId'] = $OrderDetails[$i]['iMenuItemId'];
        $addoptions['iFoodMenuId'] = $OrderDetails[$i]['iFoodMenuId'];
        $addoptions['vOptionId'] = $OrderDetails[$i]['vOptionId'];
        $addoptions['iQty'] = $OrderDetails[$i]['iQty'];
        $addoptions['vAddonId'] = $OrderDetails[$i]['vAddonId'];
        $addoptions['tInst'] = $OrderDetails[$i]['tInst'];
        $addoptions['typeitem'] = $OrderDetails[$i]['typeitem'];
        $addoptions['eFoodType'] = $OrderDetails[$i]['eFoodType'];
        array_push($responce['OrderDetails'], $addoptions);
    }
}
//echo "<pre>";print_r($responce['OrderDetails']);die;
$_SESSION[$orderDetailsSession] = $responce['OrderDetails'];
//echo "<pre>";print_r($_SESSION[$orderDetailsSession]);die;
$ld = 0;
foreach ($responce['OrderDetails'] as $OrderDeta) {
    $OrderDeta['typeitem'];
    if ($OrderDeta['typeitem'] == 'new') {
        $ld = 1;
    }
}
if ($ld == 0) {
    unset($_SESSION[$orderDetailsSession]);
    unset($_SESSION[$orderCouponSession]);
    unset($_SESSION[$orderCouponNameSession]);
}
//echo "<pre>";print_r($responce['OrderDetails']);die;
echo json_encode(array('count' => '1', 'responce' => $responce['OrderDetails']));
exit;
?>