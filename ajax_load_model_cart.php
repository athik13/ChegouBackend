<?php

include_once('common.php');
//ini_set("display_errors", "1");
//error_reporting(E_ALL);
include_once ('include_generalFunctions_dl.php');
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$id = $iCompanyId = 0;
if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
    $id = $_REQUEST['id'];
}
if (isset($_REQUEST['Company']) && $_REQUEST['Company'] > 0) {
    $iCompanyId = $_REQUEST['Company'];
}
$no = $vLanguage = $iUserId = "";
if (isset($_REQUEST['no']) && $_REQUEST['no'] != "") {
    $no = $_REQUEST['no'];
}
$iServiceId = "1";
if (isset($_SESSION[$orderServiceSession]) && $_SESSION[$orderServiceSession] != "") {
    $iServiceId = $_SESSION[$orderServiceSession];
}
if (isset($_SESSION['sess_language']) && $_SESSION['sess_language'] != "") {
    $vLanguage = $_SESSION['sess_language'];
}
if (isset($_SESSION[$orderUserIdSession]) && $_SESSION[$orderUserIdSession] != "") {
    $iUserId = $_SESSION[$orderUserIdSession];
}
$vLang = $vLanguage;
$languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
$LBL_PRICE_FOR_MENU_ITEM = $languageLabelsArr['LBL_PRICE_FOR_MENU_ITEM'];
$LBL_SELECT_TOPPING = $languageLabelsArr['LBL_SELECT_TOPPING'];
$LBL_SELECT_OPTIONS = $languageLabelsArr['LBL_SELECT_OPTIONS'];
$LBL_DESCRIPTION = $languageLabelsArr['LBL_DESCRIPTION'];
$currency_Arr = getUserCurrencyLanguageDetails($iUserId);
$Ratio = 1;
if (isset($currency_Arr['Ratio']) && $currency_Arr['Ratio'] > 0) {
    $Ratio = $currency_Arr['Ratio'];
}
//$FoodData = $_REQUEST['FoodData1'];
$foodItemData = array();
if (isset($_SESSION[$orderDataSession])) {
    $foodItemData = $_SESSION[$orderDataSession];
}
//echo "<pre>";print_r($FoodData);die;
//$json = json_decode(stripslashes($FoodData), true);
//echo "<pre>";print_r($json);die;
$counter = "0";
$responce = array();
$iQty = 1;
$OrderDetails = $_SESSION[$orderDetailsSession];
$tInst = '';
$typeitem = 'new';
$count = count($OrderDetails);
$currencySymbol = $currency_Arr['currencySymbol'];
if ($no != '') {
    for ($i = 0; $i < $count; $i++) {
        $addoptions = array();
        if ($i == $no) {
            $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $iFoodMenuId = $OrderDetails[$i]['iFoodMenuId'];
            $vOptionId = $OrderDetails[$i]['vOptionId'];
            $vOptionId = explode(",", $vOptionId);
            $iQty = $OrderDetails[$i]['iQty'];
            $vAddonId = $OrderDetails[$i]['vAddonId'];
            $vAddonId = explode(",", $vAddonId);
            $tInst = $OrderDetails[$i]['tInst'];
            $typeitem = $OrderDetails[$i]['typeitem'];
        }
    }
}
$servFields = 'eType';
$ispriceshow = '';
$ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
if (!empty($ServiceCategoryData)) {
    if (!empty($ServiceCategoryData[0]['eType'])) {
        $ispriceshow = $ServiceCategoryData[0]['eType'];
    }
}
for ($aii = 0; $aii < count($foodItemData); $aii++) {
    $vMenuItemCount = $foodItemData[$aii]['vMenuItemCount'];
    $menu_item = $foodItemData[$aii]['menu_items'];
    foreach ($menu_item as $menu_items) {
        if ($id == $menu_items['iMenuItemId']) {
            $toofPrice = $opofPrice = $adofPrice = 0;
            $counter = array();
            $counter = "1";
            $responce['vItemType'] = ucfirst($menu_items['vItemType']);
            $responce['vItemDesc'] = ucfirst($menu_items['vItemDesc']);
            $responce['eFoodType'] = $menu_items['eFoodType'];
            $responce['ItemId'] = $menu_items['iMenuItemId'];
            $responce['MenuId'] = $menu_items['iFoodMenuId'];
            $responce['LBL_PRICE_FOR_MENU_ITEM'] = $LBL_PRICE_FOR_MENU_ITEM;
            $responce['LBL_SELECT_TOPPING'] = $LBL_SELECT_TOPPING;
            $responce['LBL_DESCRIPTION'] = $LBL_DESCRIPTION;
            $responce['vImage'] = $menu_items['vImage'];
            $responce['vImage1'] = $menu_items['vImage'];
            if (empty($responce['vImage'])) {
                $responce['vImage1'] = "";
                if ($iServiceId == 1) {
                    $responce['vImage'] = $tconfig['tsite_url'] . '/assets/img/custome-store/food-detail-holder.png';
                } else {
                    $responce['vImage'] = $tconfig['tsite_url'] . '/assets/img/custome-store/deliveryall-detail-holder.png';
                }
            }
            $responce['vImageName'] = $menu_items['vImageName'];
            $responce['LBL_SELECT_OPTIONS'] = $LBL_SELECT_OPTIONS;
            $currencySymbol = $menu_items['currencySymbol'];
            $responce['currencySymbol'] = $menu_items['currencySymbol'];
            $MenuItemOptionToppingArr = $menu_items['MenuItemOptionToppingArr'];
            $addon = $MenuItemOptionToppingArr['addon'];
            $options = $MenuItemOptionToppingArr['options'];
            //print_r($options);die;
            $otherAddons = $MenuItemOptionToppingArr['otherAddons'];
            $customItemArray = $MenuItemOptionToppingArr['customItemArray'];
            $fDiscountPrice = str_replace(',', '', $menu_items['fPrice']);
            $fDiscountsPrice = str_replace(',', '', $menu_items['fDiscountPrice']);
            $addoncounter = count($addon);
            //$otherAddonscounter = count($otherAddons);
            $optionscounter = count($options);
            //$customItemArraycounter = count($customItemArray);
            if ($no == '') {
                $responce['type'] = "Add";
                $iQty = 1;
                $responce['Qty'] = "1";
            } else {
                $responce['type'] = "Edit";
                $responce['Qty'] = "$iQty";
            }
            $responce['optionscounter'] = $optionscounter;
            $responce['addoncounter'] = $addoncounter;
            //echo "<pre>";print_r($menu_items);die;
            $defaultOptionPrice = $menu_items['fPrice'];
            $defaultOptionPriceWithSymbol = $menu_items['fDiscountPricewithsymbol'];
            if ($optionscounter > 0) {
                $responce['options'] = array();
                for ($i = 0; $i < $optionscounter; $i++) {
                    $optionsi = array();

                    $optionsi['iOptionId'] = $options[$i]['iOptionId'];
                    $fPrice = $options[$i]['fUserPrice'];
                    if ($options[$i]['eDefault'] == "Yes" && $ispriceshow == "separate") {
                        $fPrice = $defaultOptionPrice;
                        $options[$i]['fUserPriceWithSymbol'] = $defaultOptionPriceWithSymbol;
                    }
                    $optionsi['fPrice'] = $fPrice;
                    $optionsi['selected'] = 'No';
                    if ($no != '') {
                        if (in_array($options[$i]['iOptionId'], $vOptionId)) {
                            $ofPrice = $options[$i]['fUserPrice'];
                            $optionsi['selected'] = 'Yes';
                            $toofPrice += $ofPrice;
                        }
                    }
                    $optionsi['fUserPriceWithSymbol'] = $options[$i]['fUserPriceWithSymbol'];
                    $optionsi['vOptionName'] = ucfirst($options[$i]['vOptionName']);
                    array_push($responce['options'], $optionsi);
                }
            }

            if ($addoncounter > 0) {
                $responce['addon'] = array();
                for ($ii = 0; $ii < $addoncounter; $ii++) {
                    $addoni = array();
                    $addoni['iOptionId'] = $addon[$ii]['iOptionId'];
                    $fPrice = $addon[$ii]['fUserPrice'];
                    $addoni['fPrice'] = $fPrice;
                    $adprice = 0;
                    $addoni['selected'] = 'No';
                    if ($no != '') {
                        if (in_array($addon[$ii]['iOptionId'], $vAddonId)) {
                            $adprice = $addon[$ii]['fUserPrice'];

                            $addoni['selected'] = 'Yes';
                            $adofPrice += $adprice;
                        }
                    }
                    $addoni['fUserPriceWithSymbol'] = $addon[$ii]['fUserPriceWithSymbol'];
                    $addoni['vOptionName'] = ucfirst($addon[$ii]['vOptionName']);
                    array_push($responce['addon'], $addoni);
                }
            }
            if (!empty($ispriceshow) && $ispriceshow == 'separate') {
                $totaltal = ($opofPrice + $toofPrice + $adofPrice) * $iQty;
                if ($totaltal == 0) {
                    $totaltal = $fDiscountPrice;
                }
            } else {
                $totaltal = (($fDiscountPrice + $toofPrice + $adofPrice) * $iQty);
            }
            $responce['discountoption'] = 'No';
            if ($fDiscountPrice != $fDiscountsPrice) {
                $responce['discountoption'] = 'Yes';
            }
            $responce['discountoption'] = 'No';
            $responce['fdiscountedPrice'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($fDiscountsPrice);
            $responce['fmainPrice'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($fDiscountPrice);
            $responce['fDiscountPrice'] = $generalobj->setTwoDecimalPoint($totaltal);
            $responce['fDiscountPricest'] = $generalobj->setTwoDecimalPoint($fDiscountPrice);
            $responce['tInst'] = $tInst;
            $responce['fDiscountPricewithsymbol'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($totaltal);
            //echo "<pre>";print_r($responce);die;
            echo json_encode(array('counter' => $counter, 'responce' => $responce, 'ispriceshow' => $ispriceshow));
            exit;
        }
    }
}
?>