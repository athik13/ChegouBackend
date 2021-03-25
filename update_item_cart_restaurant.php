<?php

include_once('common.php');
$responce = array();
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$OrderDetails = $_SESSION[$orderDetailsSession];
$count = count($_SESSION[$orderDetailsSession]);
$id = $_REQUEST['id'];
$no = $_REQUEST['no'];
$numbercart_update = $_REQUEST['numbercart_update'];
$MenuItemId = $_REQUEST['MenuItemId'];
$FoodMenuId = $_REQUEST['FoodMenuId'];
$iQty = $_REQUEST['numbers'];
$eFoodType = $_REQUEST['eFoodType'];
$addon = $_REQUEST['addon'];
$option = $_REQUEST['option'];
$tInst = (trim($_REQUEST['inst']));
$vAddonId = $_REQUEST['addon'];
$vOptionId = $_REQUEST['option'];
$responce = array();
if ($no != '' && $MenuItemId != '' && $FoodMenuId != '' && $id != '') {
    $responce['OrderDetails'] = array();
    $check = '0';
    for ($i = 0; $i < count($OrderDetails); $i++) {
        $addMenuItemId = $OrderDetails[$i]['iMenuItemId'];
        $addFoodMenuId = $OrderDetails[$i]['iFoodMenuId'];
        $addOptionId = $OrderDetails[$i]['vOptionId'];
        $addQty = $OrderDetails[$i]['iQty'];
        $addAddonId = $OrderDetails[$i]['vAddonId'];
        $addtInst = $OrderDetails[$i]['tInst'];
        $typeitem = $OrderDetails[$i]['typeitem'];
        if ($addMenuItemId == $MenuItemId && $addFoodMenuId == $FoodMenuId && $addOptionId == $vOptionId && $addAddonId == $vAddonId) {
            $check = '1';
        }
    }
    if ($check == '0') {
        for ($i = 0; $i < count($OrderDetails); $i++) {
            $addMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $addFoodMenuId = $OrderDetails[$i]['iFoodMenuId'];
            $addOptionId = $OrderDetails[$i]['vOptionId'];
            $addQty = $OrderDetails[$i]['iQty'];
            $addAddonId = $OrderDetails[$i]['vAddonId'];
            $addtInst = $OrderDetails[$i]['tInst'];
            $typeitem = $OrderDetails[$i]['typeitem'];
            $addoptions = array();
            if ($i == $no) {
                $addoptions['iMenuItemId'] = $MenuItemId;
                $addoptions['iFoodMenuId'] = $FoodMenuId;
                $addoptions['vOptionId'] = $vOptionId;
                $addoptions['iQty'] = $iQty;
                $addoptions['vAddonId'] = $vAddonId;
                $addoptions['tInst'] = $tInst;
                $addoptions['typeitem'] = $typeitem;
                $addoptions['eFoodType'] = $eFoodType;
            } else {
                $addoptions['iMenuItemId'] = $addMenuItemId;
                $addoptions['iFoodMenuId'] = $addFoodMenuId;
                $addoptions['vOptionId'] = $addOptionId;
                $addoptions['iQty'] = $addQty;
                $addoptions['vAddonId'] = $addAddonId;
                $addoptions['tInst'] = $addtInst;
                $addoptions['typeitem'] = $typeitem;
                $addoptions['eFoodType'] = $eFoodType;
            }
            array_push($responce['OrderDetails'], $addoptions);
        }
    } else {
        for ($i = 0; $i < count($OrderDetails); $i++) {
            $addMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $addFoodMenuId = $OrderDetails[$i]['iFoodMenuId'];
            $addOptionId = $OrderDetails[$i]['vOptionId'];
            $addQty = $OrderDetails[$i]['iQty'];
            $addAddonId = $OrderDetails[$i]['vAddonId'];
            $addtInst = $OrderDetails[$i]['tInst'];
            $typeitem = $OrderDetails[$i]['typeitem'];
            $addoptions = array();
            if ($i == $no) {
                if ($addMenuItemId == $MenuItemId && $addFoodMenuId == $FoodMenuId && $addOptionId == $vOptionId && $addAddonId == $vAddonId) {
                    $addoptions['iMenuItemId'] = $addMenuItemId;
                    $addoptions['iFoodMenuId'] = $addFoodMenuId;
                    $addoptions['vOptionId'] = $addOptionId;
                    $addoptions['iQty'] = $iQty;
                    $addoptions['vAddonId'] = $addAddonId;
                    $addoptions['tInst'] = $tInst;
                    $addoptions['typeitem'] = $typeitem;
                    $addoptions['eFoodType'] = $eFoodType;
                } else {
                    $addoptions['iMenuItemId'] = $MenuItemId;
                    $addoptions['iFoodMenuId'] = $FoodMenuId;
                    $addoptions['vOptionId'] = $vOptionId;
                    $addoptions['iQty'] = $iQty;
                    $addoptions['vAddonId'] = $vAddonId;
                    $addoptions['tInst'] = $addtInst;
                    $addoptions['typeitem'] = $typeitem;
                    $addoptions['eFoodType'] = $eFoodType;
                }
                array_push($responce['OrderDetails'], $addoptions);
            } else {
                if ($addMenuItemId == $MenuItemId && $addFoodMenuId == $FoodMenuId && $addOptionId == $vOptionId && $addAddonId == $vAddonId) {
                    $addoptions['iMenuItemId'] = $MenuItemId;
                    $addoptions['iFoodMenuId'] = $FoodMenuId;
                    $addoptions['vOptionId'] = $vOptionId;
                    $addoptions['iQty'] = $addQty;
                    $addoptions['vAddonId'] = $vAddonId;
                    $addoptions['tInst'] = $tInst;
                    $addoptions['typeitem'] = $typeitem;
                    $addoptions['eFoodType'] = $eFoodType;
                } else {
                    $addoptions['iMenuItemId'] = $addMenuItemId;
                    $addoptions['iFoodMenuId'] = $addFoodMenuId;
                    $addoptions['vOptionId'] = $addOptionId;
                    $addoptions['iQty'] = $addQty;
                    $addoptions['vAddonId'] = $addAddonId;
                    $addoptions['tInst'] = $addtInst;
                    $addoptions['typeitem'] = $typeitem;
                    $addoptions['eFoodType'] = $eFoodType;
                }
                array_push($responce['OrderDetails'], $addoptions);
            }
        }
    }
    $_SESSION[$orderDetailsSession] = $responce['OrderDetails'];
}
$OrderDetailss = $_SESSION[$orderDetailsSession];
//echo '<pre>';
//print_r($_SESSION['OrderDetails']);
$totalQtyty = $finalcout = $checkfinalarr = 0;
$finalArr = array();
for ($ig = 0; $ig < count($OrderDetailss); $ig++) {
    $addQtyty = $OrderDetailss[$ig]['iQty'];
    $totalQtyty += $addQtyty;
}
foreach ($OrderDetailss as $OrderDetailsskey => $OrderDetailssvalue) {
    if ($OrderDetailssvalue['typeitem'] == "remove") {
        continue;
    }
    $checkfinalarr = 0;
    //print_r($OrderDetailssvalue);
    foreach ($finalArr as $finalArrKey => $finalArrvalue) {
        if ($OrderDetailssvalue['iMenuItemId'] == $finalArrvalue['iMenuItemId'] && $OrderDetailssvalue['iFoodMenuId'] == $finalArrvalue['iFoodMenuId'] && $OrderDetailssvalue['vOptionId'] == $finalArrvalue['vOptionId'] && $OrderDetailssvalue['vAddonId'] == $finalArrvalue['vAddonId']) {
            $checkfinalarr = 1;
            $finalArr[$finalArrKey]['iQty'] = ($finalArr[$finalArrKey]['iQty'] + $OrderDetailssvalue['iQty']);
            continue;
        }
    }
    if ($checkfinalarr == 1) {
        continue;
    }
    $finalArr[$finalcout] = $OrderDetailssvalue;
    $finalcout++;
}
$_SESSION[$orderDetailsSession] = $finalArr;
echo json_encode(array('count' => '1', 'totalcounter' => $totalQtyty, 'responce' => $_SESSION[$orderDetailsSession]));
exit;
?>