<?php

include_once('common.php');
$responce = array();
$id = $_REQUEST['id'];
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_".strtoupper($fromOrder);
if ($_SESSION[$orderStoreIdSession] != $id) {
    unset($_SESSION[$orderDetailsSession]);
}
$iMenuItemId = $_REQUEST['MenuItemId'];
$iFoodMenuId = $_REQUEST['FoodMenuId'];
$iQty = $_REQUEST['numbers'];
$eFoodType = $_REQUEST['eFoodType'];
$addon = $_REQUEST['addon'];
$option = $_REQUEST['option'];
$tInst = "";
if (isset($_REQUEST['inst'])) {
    $tInst = (trim($_REQUEST['inst']));
}
$typeitem = 'new';
if (empty($_REQUEST['addonother'])) {
    $vAddonId = $_REQUEST['addon'];
} else {
    $vAddonotherId = $_REQUEST['addonother'];
    $vAddonotherId = explode(',', $vAddonotherId);
    $vAddonotherId = array_unique($vAddonotherId);
    $vAddonotherId = implode(',', $vAddonotherId);
    $vAddonotherId = trim($vAddonotherId, ",");
    $vAddonId = $addon . "," . $vAddonotherId;
}
if (empty($_REQUEST['optionother'])) {
    $vOptionId = $_REQUEST['option'];
} else {
    $vOptionotherId = $_REQUEST['optionother'];
    $vOptionotherId = explode(',', $vOptionotherId);
    $vOptionotherId = array_unique($vOptionotherId);
    $vOptionotherId = implode(',', $vOptionotherId);
    $vOptionotherId = trim($vOptionotherId, ",");
    $vOptionId = $option . "," . $vOptionotherId;
}
$vAddonId = trim($vAddonId, ",");
$vOptionId = trim($vOptionId, ",");
$counter = "1";
$addorderdetails = array();
if (isset($_SESSION[$orderDetailsSession])) {
    $addorderdetails = $_SESSION[$orderDetailsSession];
}
$responce = array();
if ($id != '' && $iMenuItemId != '' && $iFoodMenuId != '' && $id != '') {
    $_SESSION[$orderStoreIdSession] = $id;
    $responce['OrderDetails'] = array();
    if (count($addorderdetails) == 0) {
        $optionsi = array();
        $optionsi['iMenuItemId'] = $iMenuItemId;
        $optionsi['iFoodMenuId'] = $iFoodMenuId;
        $optionsi['vOptionId'] = $vOptionId;
        $optionsi['iQty'] = $iQty;
        $optionsi['vAddonId'] = $vAddonId;
        $optionsi['tInst'] = $tInst;
        $optionsi['typeitem'] = $typeitem;
        $optionsi['eFoodType'] = $eFoodType;
        array_push($responce['OrderDetails'], $optionsi);
        $_SESSION[$orderDetailsSession] = $responce['OrderDetails'];
    } else {
        $check = '0';
        for ($i = 0; $i < count($addorderdetails); $i++) {
            $addMenuItemId = $addorderdetails[$i]['iMenuItemId'];
            $addFoodMenuId = $addorderdetails[$i]['iFoodMenuId'];
            $addOptionId = $addorderdetails[$i]['vOptionId'];
            $addQty = $addorderdetails[$i]['iQty'];
            $addAddonId = $addorderdetails[$i]['vAddonId'];
            $addtInst = $addorderdetails[$i]['tInst'];
            $addtypeitem = $addorderdetails[$i]['typeitem'];
            if ($addMenuItemId == $iMenuItemId && $addFoodMenuId == $iFoodMenuId && $addOptionId == $vOptionId && $addAddonId == $vAddonId) {
                $check = '1';
            }
        }
        if ($check == '0') {
            $responce['addOrderDetails'] = array();
            $addoptions = array();
            $addoptions['iMenuItemId'] = $iMenuItemId;
            $addoptions['iFoodMenuId'] = $iFoodMenuId;
            $addoptions['vOptionId'] = $vOptionId;
            $addoptions['iQty'] = $iQty;
            $addoptions['vAddonId'] = $vAddonId;
            $addoptions['tInst'] = $tInst;
            $addoptions['typeitem'] = $typeitem;
            $addoptions['eFoodType'] = $eFoodType;
            array_push($responce['addOrderDetails'], $addoptions);
            $new_array = array_merge($_SESSION[$orderDetailsSession], $responce['addOrderDetails']);
            $responce['OrderDetails'] = $new_array;
            $_SESSION[$orderDetailsSession] = $responce['OrderDetails'];
        } else {
            for ($i = 0; $i < count($addorderdetails); $i++) {
                $addMenuItemId = $addorderdetails[$i]['iMenuItemId'];
                $addFoodMenuId = $addorderdetails[$i]['iFoodMenuId'];
                $addOptionId = $addorderdetails[$i]['vOptionId'];
                $addQty = $addorderdetails[$i]['iQty'];
                $addAddonId = $addorderdetails[$i]['vAddonId'];
                $addtInst = $addorderdetails[$i]['tInst'];
                $addtypeitem = $addorderdetails[$i]['typeitem'];
                $nqty = 0;
                $addoptions = array();
                if ($addMenuItemId == $iMenuItemId && $addFoodMenuId == $iFoodMenuId && $addOptionId == $vOptionId && $addAddonId == $vAddonId) {
                    if ($addtypeitem == 'remove') {
                        $nqty = $iQty;
                    } else {
                        $nqty = $addQty + $iQty;
                    }
                    $addoptions['iMenuItemId'] = $iMenuItemId;
                    $addoptions['iFoodMenuId'] = $iFoodMenuId;
                    $addoptions['vOptionId'] = $vOptionId;
                    $addoptions['iQty'] = $nqty;
                    $addoptions['vAddonId'] = $vAddonId;
                    $addoptions['tInst'] = $addtInst;
                    $addoptions['typeitem'] = $typeitem;
                    $addoptions['eFoodType'] = $eFoodType;
                } else {
                    $addoptions['iMenuItemId'] = $addMenuItemId;
                    $addoptions['iFoodMenuId'] = $addFoodMenuId;
                    $addoptions['vOptionId'] = $addOptionId;
                    $addoptions['iQty'] = $addQty;
                    $addoptions['vAddonId'] = $addAddonId;
                    $addoptions['tInst'] = $addtInst;
                    $addoptions['typeitem'] = $addtypeitem;
                    $addoptions['eFoodType'] = $eFoodType;
                }
                array_push($responce['OrderDetails'], $addoptions);
                $_SESSION[$orderDetailsSession] = $responce['OrderDetails'];
            }
        }
    }
}
$OrderDetailss = $_SESSION[$orderDetailsSession];
//print_r($OrderDetailss);die;
$totalQtyty = 0;
for ($ig = 0; $ig < count($OrderDetailss); $ig++) {
    if ($OrderDetailss[$ig]['typeitem'] != 'remove') {
        $addQtyty = $OrderDetailss[$ig]['iQty'];
        $totalQtyty += $addQtyty;
    }
}
echo json_encode(array('responce' => $responce['OrderDetails'], 'totalcounter' => $totalQtyty));
exit;
?>