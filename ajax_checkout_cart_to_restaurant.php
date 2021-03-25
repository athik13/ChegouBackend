<?php

include_once('common.php');
include_once ('include_generalFunctions_dl.php');
$responce = array();
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
checkCartItemStatus($fromOrder); // Added By HJ On 18-01-2020 For Check Cart's Item Status 141 Mantis Bug - #2367
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);
if (isset($_SESSION[$orderDetailsSession])) {
    $_REQUEST["OrderDetails"] = json_encode($_SESSION[$orderDetailsSession]);
}
//echo $orderDetailsSession."<br>";
//echo "<pre>";print_r($_REQUEST["OrderDetails"]);die;
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
//print_r($iUserAddressId);die;
$cart_id_update = isset($_REQUEST["cart_id_update"]) ? $_REQUEST["cart_id_update"] : '';
$iCompanyId = isset($_SESSION[$orderStoreIdSession]) ? $_SESSION[$orderStoreIdSession] : '';
$CheckNonVegFoodType = isset($_REQUEST["CheckNonVegFoodType"]) ? $_REQUEST["CheckNonVegFoodType"] : 'No';
$fDeliverytime = 0;
$sql = "SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'";
$Dataua = $obj->MySQLSelect($sql);
$vServiceAddress = $vBuildingNo = $vLandmark = $vAddressType = $vLatitude = $vLongitude = "";
$vTimeZone = date_default_timezone_get();
if (count($Dataua) > 0) {
    $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
    $vBuildingNo = $Dataua[0]['vBuildingNo'];
    $vLandmark = $Dataua[0]['vLandmark'];
    $vAddressType = $Dataua[0]['vAddressType'];
    $vLatitude = $Dataua[0]['vLatitude'];
    $vLongitude = $Dataua[0]['vLongitude'];
    $vTimeZone = !empty($Dataua[0]['vTimeZone']) ? $Dataua[0]['vTimeZone'] : $vTimeZone;
}

$idss = isset($_REQUEST["idss"]) ? $_REQUEST["idss"] : '';
if (!empty($idss)) {
    $couponCode = isset($_SESSION[$orderCouponSession]) ? $_SESSION[$orderCouponSession] : '';
} else {
    $couponCode = isset($_REQUEST["couponCode"]) ? $_REQUEST["couponCode"] : '';
}
$ePaymentOption = isset($_REQUEST["ePaymentOption"]) ? $_REQUEST["ePaymentOption"] : '';
$vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
$OrderDetails = isset($_REQUEST["OrderDetails"]) ? $_REQUEST["OrderDetails"] : '';
$eTakeAway = isset($_REQUEST["eTakeAway"]) ? $_REQUEST["eTakeAway"] : 'No';
//echo "<pre>";print_r($_SESSION);die;
$vInstruction = isset($_REQUEST["vInstruction"]) ? $_REQUEST["vInstruction"] : '';
$passengerLat = $vLatitude;
$passengerLon = $vLongitude;
$fChangeAmount = isset($_REQUEST["changeAmount"]) ? $_REQUEST["changeAmount"] : '';
$CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';
if ($CheckUserWallet == "" || $CheckUserWallet == NULL) {
    $CheckUserWallet = "No";
}
$Data = array();
$a = $b = "";
if ($vBuildingNo != '') {
    $a = ucfirst($vBuildingNo) . ", ";
}
if ($vLandmark != '') {
    $b = ucfirst($vLandmark) . ", ";
}
$fulladdress = $a . "" . $b . "" . $vServiceAddress;
$Data['UserSelectedAddress'] = $fulladdress;
$Data['UserSelectedLatitude'] = $vLatitude;
$Data['UserSelectedLongitude'] = $vLongitude;
$Data['UserSelectedAddressId'] = $iUserAddressId;
// # Checking Distance Between Company and User Address ##
$UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
$Ratio = $UserDetailsArr['Ratio'];
$currencySymbol = $UserDetailsArr['currencySymbol'];
$vLang = $UserDetailsArr['vLang'];
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
}
if ($vLang == "" || $vLang == NULL) {
    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
}
$languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
$sql = "select vCompany,vCaddress,fMaxOfferAmt,fTargetAmt,fOfferType,fOfferAppyType,fOfferAmt,vRestuarantLocation,vImage,iCompanyId,vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPackingCharge,iMaxItemQty,vDemoStoreImage from `company` where iCompanyId = '" . $iCompanyId . "'";
$db_companydata = $obj->MySQLSelect($sql);
$vCompany = $fOfferAppyType = $fOfferType = $vRestuarantLocation = $vCaddress = "";
$fMaxOfferAmt = $fTargetAmt = $iMaxItemQty = $fCompanyTax = 0;
//echo "<pre>";print_R($db_companydata);die;
$TotaliQty = 0;
if (count($db_companydata) > 0) {
    //$vCompany = ucwords(strtolower($db_companydata[0]['vCompany']));
    $vCompany = stripslashes(ucfirst($db_companydata[0]['vCompany']));
    $vCaddress = ucwords(strtolower($db_companydata[0]['vCaddress']));
    $vImage = $db_companydata[0]['vImage'];
    $vRestuarantLocation = $db_companydata[0]['vRestuarantLocation'];
    $fMaxOfferAmt = $db_companydata[0]['fMaxOfferAmt'];
    $fMaxOfferAmt = $generalobj->setTwoDecimalPoint($fMaxOfferAmt * $Ratio);
    $fTargetAmt = $db_companydata[0]['fTargetAmt'];
    $fTargetAmt = $generalobj->setTwoDecimalPoint($fTargetAmt * $Ratio);
    $fOfferAppyType = $db_companydata[0]['fOfferAppyType'];
    $fOfferType = $db_companydata[0]['fOfferType'];
    $iMaxItemQty = $db_companydata[0]['iMaxItemQty'];
}
if ($vImage != "") {
    $db_companydata[0]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $db_companydata[0]['iCompanyId'] . '/1_' . $vImage;
} else {
    /* if ($iServiceId != 1) {
      $db_companydata[0]['vImage'] = $tconfig['tsite_url'] . '/assets/img/custome-store/deliveryall-menu-order-list.png';
      } else {
      $db_companydata[0]['vImage'] = $tconfig['tsite_url'] . '/assets/img/custome-store/food-menu-order-list.png';
      } */
    $db_companydata[0]['vImage'] = $tconfig['tsite_url'] . 'assets/img/custome-store/food-menu-order-list.png';
}
//Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
if (isset($db_companydata[0]['vDemoStoreImage']) && $db_companydata[0]['vDemoStoreImage'] != "" && SITE_TYPE == "Demo") {
    $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $db_companydata[0]['vDemoStoreImage'];
    if (file_exists($demoImgPath)) {
        $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $db_companydata[0]['vDemoStoreImage'];
        $db_companydata[0]['vImage'] = $demoImgUrl;
    }
}
//Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
$couponCode = trim($couponCode);
if ($couponCode != "") {
    //$checkvalidpromocode = CheckPromoCode($couponCode, $iUserId, $iCompanyId, $passengerLat, $passengerLon); //Added By HJ On 07-06-2019 For Optimized Code
}
$OrderDetails = json_decode(stripcslashes($OrderDetails), true);
$OrderDetailscount = count($OrderDetails);
//print_r($OrderDetailscount);die;
$OrderFareDetailsArr = array();
$fFinalTotal = $fTotalMenuItemBasePrice = $fFinalDiscountPercentage = 0;
if (!empty($OrderDetails)) {
    //Added By HJ On 09-05-2019 For Optimize Code Start
    $optionPriceArr = getAllOptionAddonPriceArr();
    $ordItemPriceArr = getAllMenuItemPriceArr();
    //Added By HJ On 09-05-2019 For Optimize Code End
    for ($j = 0; $j < count($OrderDetails); $j++) {
        $typeitems = trim($OrderDetails[$j]['typeitem']);
        $iQty = $OrderDetails[$j]['iQty'];
        if ($typeitems == 'new') {
            //$fMenuItemPrice = FoodMenuItemBasicPrice($OrderDetails[$j]['iMenuItemId'], $iQty); //Commnent By HJ On 17-05-2019 For Optimize Below Code
            //Added By HJ On 09-05-2019 For Optimize Code Start
            $fMenuItemPrice = 0;
            if (isset($ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']]) && $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] > 0) {
                $fMenuItemPrice = $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] * $iQty;
            }
            //Added By HJ On 09-05-2019 For Optimize Code End
            //$vOptionPrice = GetFoodMenuItemOptionPrice($OrderDetails[$j]['vOptionId']); //Commnent By HJ On 17-05-2019 For Optimize Below Code
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
            //$vAddonPrice = GetFoodMenuItemAddOnPrice($OrderDetails[$j]['vAddonId']); //Commnent By HJ On 17-05-2019 For Optimize Below Code
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
            $fTotalMenuItemBasePrice = $fTotalMenuItemBasePrice + $vOptionPrice + $vAddonPrice + $fMenuItemPrice;
        }
    }
    if ($db_companydata[0]['fMaxOfferAmt'] > 0 && $fOfferType == "Percentage" && $fOfferAppyType != "None") {
        $fFinalDiscountPercentage = (($fTotalMenuItemBasePrice * $db_companydata[0]['fOfferAmt']) / 100);
    }
    $fTotalDiscount = $iQty = 0;
    $fTotalMenuItemBasePrice = $generalobj->setTwoDecimalPoint($fTotalMenuItemBasePrice * $Ratio);
    $fFinalDiscountPercentage = $generalobj->setTwoDecimalPoint($fFinalDiscountPercentage * $Ratio);
    $OrderDetailsItemsArr = array();
    for ($i = 0; $i < count($OrderDetails); $i++) {
        $typeitem = $OrderDetails[$i]['typeitem'];
        if ($OrderDetails[$i]['typeitem'] == 'new') {
            $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $iFoodMenuId = $OrderDetails[$i]['iFoodMenuId'];
            $vOptionId = $OrderDetails[$i]['vOptionId'];
            $vOptionId = trim($vOptionId, ",");
            $vAddonId = $OrderDetails[$i]['vAddonId'];
            $vAddonId = trim($vAddonId, ",");
            $iQty = $OrderDetails[$i]['iQty'];
            $tInst = $OrderDetails[$i]['tInst'];
            $vItemType = get_value('menu_items', 'vItemType_' . $vLang, 'iMenuItemId', $iMenuItemId, '', 'true');
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
            if ($fOfferAppyType != "None" && $TotOrders == 0) {
                if ($fOfferType == "Flat" && $fOfferAppyType != "None") {
                    $fTotalDiscount = $fDiscountPrice;
                } elseif ($fOfferType == "Percentage" && $fOfferAppyType != "None") {
                    $fTotalDiscount += $fDiscountPrice;
                } else {
                    $fTotalDiscount += $fDiscountPrice;
                }
            } else {
                if ($fOfferType == "Flat" && $fOfferAppyType != "None") {
                    $fTotalDiscount = $fDiscountPrice;
                } elseif ($fOfferType == "Percentage" && $fOfferAppyType != "None") {
                    $fTotalDiscount += $fDiscountPrice;
                } else {
                    $fTotalDiscount += $fDiscountPrice;
                }
            }
            /* if ($fMaxOfferAmt > 0 && $fOfferType == "Percentage" && $fOfferAppyType != "None") {
              $fTotalDiscount = ($fTotalDiscount > $fMaxOfferAmt) ? $fMaxOfferAmt : $fTotalDiscount;
              $fPrice = ($fFinalDiscountPercentage > $fMaxOfferAmt) ? $fOriginalPrice : $fPrice;
              $fOfferAmt = ($fFinalDiscountPercentage > $fMaxOfferAmt) ? 0 : $fOfferAmt;
              } */
        }
        $type = "edit";
        if (isset($OrderDetails[$i]['eFoodType']) && !empty($OrderDetails[$i]['eFoodType'])) {
            $OrderDetailsItemsArr[$i]['eFoodType'] = $OrderDetails[$i]['eFoodType'];
        }
        $OrderDetailsItemsArr[$i]['iMenuItemId'] = $iMenuItemId;
        $OrderDetailsItemsArr[$i]['type'] = $type;
        $OrderDetailsItemsArr[$i]['iFoodMenuId'] = $iFoodMenuId;
        $OrderDetailsItemsArr[$i]['vItemType'] = $vItemType;
        $OrderDetailsItemsArr[$i]['iQty'] = $OrderDetails[$i]['iQty'];
        $OrderDetailsItemsArr[$i]['fOfferAmt'] = $fOfferAmt;
        $OrderDetailsItemsArr[$i]['fOriginalPrice'] = $currencySymbol . ' ' . formatnum($fOriginalPrice);
        /* $OrderDetailsItemsArr[$i]['fPrice'] = $currencySymbol.' '.formatnum($fPrice); */
        $OrderDetailsItemsArr[$i]['fPrice'] = $currencySymbol . ' ' . formatnum($fOriginalPrice);
        $OrderDetailsItemsArr[$i]['tInst'] = $tInst;
        $OrderDetailsItemsArr[$i]['typeitem'] = $typeitem;
        $optionaddonname = "";
        if ($vOptionId != "") {
            $optionname = GetMenuItemOptionsToppingName($vOptionId);
            $optionaddonname = ucfirst(trim($optionname));
        }
        if ($optionaddonname != "") {
            $optionaddonname = trim(trim($optionaddonname), ",");
        }
        if ($vAddonId != "") {
            $addonname = GetMenuItemOptionsToppingName($vAddonId);
            if ($optionaddonname != "") {
                $optionaddonname .= ", " . ucfirst(trim($addonname));
            } else {
                $optionaddonname = ucfirst(trim($addonname));
            }
        }
        if ($optionaddonname != "") {
            $optionaddonname = trim(trim($optionaddonname), ",");
        }
        $OrderDetailsItemsArr[$i]['optionaddonname'] = $optionaddonname;
        if ($OrderDetails[$i]['typeitem'] == 'new') {
            $TotaliQty += $iQty;
        }
        if ($cart_id_update == $i) {
            $showfoptionaddonname = $optionaddonname;
            $showfPrice = $currencySymbol . ' ' . formatnum($fOriginalPrice);
            /* $showfPrice = $currencySymbol.' '.formatnum($fPrice); */
        }
    }
    $Data['OrderDetailsItemsArr'] = $OrderDetailsItemsArr;
    //$fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
    $fPackingCharge = 0;
    if (isset($db_companydata[0]['fPackingCharge']) && $db_companydata[0]['fPackingCharge'] > 0) {
        $fPackingCharge = $generalobj->setTwoDecimalPoint($db_companydata[0]['fPackingCharge'] * $Ratio);
    }
    // # Calculate Order Delivery Charge ##
    $fDeliveryCharge = 0;
    $sql = "SELECT vLatitude as passengerlat,vLongitude as passengerlong FROM user_address as ua WHERE iUserAddressId	= '" . $iUserAddressId . "'";
    $datad = $obj->MySQLSelect($sql);
    //$sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPackingCharge FROM company WHERE iCompanyId	= '" . $iCompanyId . "'";
    //$datac = $obj->MySQLSelect($sql);
    if (count($datad) > 0) {
        $User_Address_Array = array($datad[0]['passengerlat'], $datad[0]['passengerlong']);
        $Rest_Address_Array = array($db_companydata[0]['restaurantlat'], $db_companydata[0]['restaurantlong']);
        $iToLocationId = GetUserGeoLocationId($User_Address_Array);  /*  user location */
        $iLocationId = GetUserGeoLocationId($Rest_Address_Array);    /*  restaurant location */
        if (isset($Data['UserSelectedLatitude']) && isset($Data['UserSelectedLongitude'])) {
            //$sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPackingCharge FROM company WHERE iCompanyId	= '" . $iCompanyId . "'";
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
                //print_r($data_location);die;
                //Added By HJ On 02-01-2019 For Get All Location Delivery Charge End As Per Discuss With CD Sir
                $iFreeDeliveryRadius = $distance = $fOrderPriceValue = $fDeliveryChargeAbove = $fDeliveryChargeBelow = $fFreeOrderPriceSubtotal = 0;
                if (count($data_location) > 0) {
                    $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
                    $fOrderPriceValue = $generalobj->setTwoDecimalPoint($fOrderPriceValue * $Ratio);
                    $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
                    $fDeliveryChargeAbove = $generalobj->setTwoDecimalPoint($fDeliveryChargeAbove * $Ratio);
                    $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
                    $fDeliveryChargeBelow = $generalobj->setTwoDecimalPoint($fDeliveryChargeBelow * $Ratio);
                    $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
                    $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
                    $fFreeOrderPriceSubtotal = $generalobj->setTwoDecimalPoint($fFreeOrderPriceSubtotal * $Ratio);
                }
                $distance = distanceByLocation($Data['UserSelectedLatitude'], $Data['UserSelectedLongitude'], $db_companydata[0]['restaurantlat'], $db_companydata[0]['restaurantlong'], "K");

                if ($fFinalTotal >= $fOrderPriceValue) {
                    $fDeliveryCharge = $fDeliveryChargeAbove;
                    //$fDeliveryCharge = $fDeliveryChargeBelow;
                } else {
                    $fDeliveryCharge = $fDeliveryChargeBelow;
                    //$fDeliveryCharge = $fDeliveryChargeAbove;
                }
                if ($iFreeDeliveryRadius >= 0) {
                    if ($distance < $iFreeDeliveryRadius && $iFreeDeliveryRadius >= 0 && !empty($iFreeDeliveryRadius)) { //when zero for free order and radius then do not allow free order
                        $fDeliveryCharge = 0;
                    }
                }
                //if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0) { //added by SP for delivery charge blank then it does not count on 27-06-2019
                 if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0 && !empty($fFreeOrderPriceSubtotal)) { //when zero for free order and radius then do not allow free order
                    if ($fFinalTotal > $fFreeOrderPriceSubtotal) {
                        $fDeliveryCharge = 0;
                    }
                }
            }
        }
    }
    //Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3793 Start
    if($fTotalDiscount > $fFinalTotal){
        $fTotalDiscount = $fFinalTotal;
    }
    //Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3793 End
    $fTax = 0;
    // # Calculate Order Delivery Charge ##
    if ($fCompanyTax > 0) {
        $fcotaxamount = $fFinalTotal - $fTotalDiscount + $fPackingCharge;
        $fCompanyTax = $generalobj->setTwoDecimalPoint(($fcotaxamount * $fCompanyTax) / 100);
    } else {
        $TaxArr = getMemberCountryTax($iUserId, "Passenger");
        $fTax = $TaxArr['fTax1'];
        
        if ($fTax > 0) {
            $ftaxamount = $fFinalTotal - $fTotalDiscount + $fPackingCharge;
            $fTax = $generalobj->setTwoDecimalPoint(($ftaxamount * $fTax) / 100);
        }
    }
    $fCommision = $ADMIN_COMMISSION;
    //Added By HJ On 30-01-2019 For Applied Store Wise Commission Per Order If > 0 and ENABLE_STORE_COMMISSION==Yes Start
    if (isset($db_companydata[0]['fComissionPerOrder']) && $db_companydata[0]['fComissionPerOrder'] > 0 && $ENABLE_STORE_COMMISSION == "Yes") {
        $fCommision = $db_companydata[0]['fComissionPerOrder'];
    }
    //Added By HJ On 30-01-2019 For Applied Store Wise Commission Per Order If > 0 and ENABLE_STORE_COMMISSION==Yes End
    //check deliver charages $fDeliveryCharge 
    //echo $fFinalTotal."+".$fPackingCharge."+".$fDeliveryCharge."+".$fTax."+".$fCompanyTax."-".$fTotalDiscount;die;
    if($eTakeAway == 'Yes')
    {
        $fDeliveryCharge = 0;
    }

    $fNetTotal = $fTotalGenerateFare = ($fFinalTotal + $fPackingCharge + $fDeliveryCharge + $fTax + $fCompanyTax) - $fTotalDiscount;
    $fOrderFare_For_Commission = $fFinalTotal;
    $fCommision = $generalobj->setTwoDecimalPoint(($fOrderFare_For_Commission * $fCommision) / 100);
    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    //check discount value
    if ($couponCode != '') {
        $getCouponData = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eSystemType IN ('DeliverAll','General') AND eStatus='Active' ORDER BY iCouponId ASC LIMIT 0,1");
        if (count($getCouponData) > 0) {
            $discountValue = $getCouponData[0]['fDiscount'];
            $discountValueType = $getCouponData[0]['eType'];
        }
        //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
        //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $discountApplyOn = $fNetTotal - ($fDeliveryCharge+$fTax); // Added By HJ On 27-06-2019 As Per Discuss With BM Mam // Tax Minus From Coupon Code As Per Discuss With CD sir and KS Sir On 31-01-2020
            $vDiscount = $generalobj->setTwoDecimalPoint($discountValue) . ' ' . "%";
            $discountValue = $generalobj->setTwoDecimalPoint(($discountApplyOn * $discountValue) / 100);
            //echo $discountValue;
        } else {
            //$curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $fNetTotal) {
                $vDiscount = $generalobj->setTwoDecimalPoint($fNetTotal) . ' ' . $currencySymbol;
            } else {
                $vDiscount = $generalobj->setTwoDecimalPoint($discountValue) . ' ' . $currencySymbol;
            }
        }
        //Added By HJ On 07-06-2019 For Convert Promocode Amount Into User Currency Ratio Start
        if ($discountValue > 0 && strtolower($discountValueType) == "cash") {
            $discountValue = $discountValue * $Ratio;
        }
        //Added By HJ On 07-06-2019 For Convert Promocode Amount Into User Currency Ratio End
        $fNetTotal =$fTotalGenerateFare= $fNetTotal - $discountValue;
        $Order_data[0]['fDiscount'] = $discountValue;
        $Order_data[0]['vDiscount'] = $vDiscount;
    }
    /* Check Coupon Code Total Fare  End */
    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    $fOutStandingAmount = $generalobj->setTwoDecimalPoint($fOutStandingAmount * $Ratio);
    if ($fOutStandingAmount > 0) {
        $fNetTotal += $fOutStandingAmount;
        $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
    }
    /* 	Checking For Passenger Outstanding Amount */
    /* Check debit wallet For Count Total Order Fare Start */
    $user_wallet_debit_amount = 0;
    $DisplayCardPayment = "No";
    if ($iUserId > 0 && $CheckUserWallet == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        //echo $user_available_balance;die;
        $user_available_balance = $generalobj->setTwoDecimalPoint($user_available_balance * $Ratio);
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
            $fTotalGenerateFare = $fNetTotal;
            $DisplayCardPayment = "Yes";
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal =$fTotalGenerateFare= 0;
            $DisplayCardPayment = "No";
        }
    }
    //echo $user_wallet_debit_amount;die;
    /* Check debit wallet For Count Total Order Fare End */
    if ($fNetTotal < 0) {
        $fNetTotal = $fTotalGenerateFare = 0;
    }
    $Data['optionaddonname'] = $showfoptionaddonname;
    $Data['showfPrice'] = $showfPrice;
    $Data['fSubTotal'] = $currencySymbol . " " . formatnum($fFinalTotal);
    $Data['fTotalDiscount'] = $currencySymbol . " " . formatnum($fTotalDiscount);
    $Data['totalDiscount'] = $fTotalDiscount;
    $fPackingCharge = $generalobj->setTwoDecimalPoint($fPackingCharge);
    $Data['PackingCharge'] = $fPackingCharge;
    $Data['fPackingCharge'] = ($fPackingCharge > 0) ? $currencySymbol . " " . formatnum($fPackingCharge) : 0;
    $fDeliveryCharge = $generalobj->setTwoDecimalPoint($fDeliveryCharge);
    $Data['DeliveryCharge'] = $fDeliveryCharge;
    $Data['fDeliveryCharge'] = ($fDeliveryCharge > 0) ? $currencySymbol . " " . formatnum($fDeliveryCharge) : 0;
    $fTax = $generalobj->setTwoDecimalPoint($fTax);
    $Data['tax'] = $fTax;
    $Data['fTax'] = ($fTax > 0) ? $currencySymbol . " " . formatnum($fTax) : 0;
    $fDiscount_Val = 0;
    if (isset($Order_data[0]['fDiscount']) && $Order_data[0]['fDiscount'] > 0) {
        $fDiscount_Val = $generalobj->setTwoDecimalPoint($Order_data[0]['fDiscount']);
    }
    $Data['Discount_Val'] = $fDiscount_Val;
    $Data['fDiscount'] = ($fDiscount_Val > 0) ? $currencySymbol . " " . $fDiscount_Val : 0;
    $Data['CompanyTax'] = $fCompanyTax;
    $Data['OutStandingAmount'] = $fOutStandingAmount;
    //$Data['fCompanyTax'] = ($fCompanyTax > 0) ? $currencySymbol . " " . formatnum($fCompanyTax) : 0;
    // $Data['vDiscount'] = $Order_data[0]['vDiscount'];
    $fCommision = $generalobj->setTwoDecimalPoint($fCommision);
    $Data['fCommision'] = ($fCommision > 0) ? $currencySymbol . " " . formatnum($fCommision) : 0;
    $fNetTotal = $generalobj->setTwoDecimalPoint($fNetTotal);
    $Data['fNetTotal'] = ($fNetTotal > 0) ? $currencySymbol . " " . formatnum($fNetTotal) : $currencySymbol . " 0";
    $Data['fNetTotalAmount'] = $fNetTotal;
    $fTotalGenerateFare = $generalobj->setTwoDecimalPoint($fTotalGenerateFare);
    $Data['GenerateFare'] = $fTotalGenerateFare;
    $Data['fTotalGenerateFare'] = ($fTotalGenerateFare > 0) ? $currencySymbol . " " . formatnum($fTotalGenerateFare) : $currencySymbol . " 0";
    $Data['fTotalGenerateFareAmount'] = $fTotalGenerateFare;
    $Data['fOutStandingAmount'] = ($fOutStandingAmount > 0) ? $currencySymbol . " " . formatnum($fOutStandingAmount) : $currencySymbol . " 0";
    $Data['fWalletDebit'] = ($user_wallet_debit_amount > 0) ? $currencySymbol . " " . formatnum($user_wallet_debit_amount) : $currencySymbol . " 0";
    $Data['user_wallet_debit_amount'] = $user_wallet_debit_amount;
    $Data['currencySymbol'] = $currencySymbol;
    $Data['DisplayCardPayment'] = $DisplayCardPayment;
    $Data['DisplayUserWalletDebitAmount'] = ($user_wallet_debit_amount > 0) ? $currencySymbol . " " . formatnum($user_wallet_debit_amount) : "";
    $Data['DISABLE_CASH_PAYMENT_OPTION'] = ($fOutStandingAmount > 0) ? "Yes" : "No";
    $arrindex = 0;
    if ($fTotalDiscount > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fTotalDiscount);
        $arrindex++;
    }
    if ($fPackingCharge > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $currencySymbol . " " . formatnum($fPackingCharge);
        $arrindex++;
    }
    if ($fDeliveryCharge > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = $currencySymbol . " " . formatnum($fDeliveryCharge);
        $arrindex++;
    }
    if ($fTax > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $currencySymbol . " " . formatnum($fTax);
        $arrindex++;
    }
    if ($fOutStandingAmount > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fOutStandingAmount);
        $arrindex++;
    }

    if ($fDiscount_Val > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fDiscount_Val);
        $arrindex++;
    }
    if ($user_wallet_debit_amount > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "- " . $currencySymbol . " " . formatnum($user_wallet_debit_amount);
        $arrindex++;
    }
    /* if ($fTotalGenerateFare > 0) { */
    $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fTotalGenerateFare);
    $arrindex++;
}
$Data['fsubTotallabel'] = $languageLabelsArr['LBL_SUBTOTAL_APP_TXT'];
$Data['fTotalDiscountlabel'] = $languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT'];
$Data['fPackinlabel'] = $languageLabelsArr['LBL_PACKING_CHARGE'];
$Data['fDeliverylabel'] = $languageLabelsArr['LBL_DELIVERY_CHARGES_TXT'];
//$Data['fCompanytaxlabel'] =$languageLabelsArr['LBL_INFO_COMPANY_TAX_TXT'];
$Data['fTaxlabel'] = $languageLabelsArr['LBL_TOTAL_TAX_TXT'];
$Data['fOutStandinglabel'] = $languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT'];
$Data['fDiscount_Vallabel'] = $languageLabelsArr['LBL_DISCOUNT_TXT'];
$Data['fdebitlabel'] = $languageLabelsArr['LBL_WALLET_ADJUSTMENT'];
$Data['fTotalGeneratelabel'] = $languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'];
$storeDataArr = array();
$restaurant_status_arr = calculate_restaurant_time_span($iCompanyId, $iUserId,$vLang,$languageLabelsArr,$storeDataArr);
$Data['restaurantstatus'] = $restaurant_status_arr['restaurantstatus'];
$Data['Ordercounters'] = $OrderDetailscount;
$Data['FareDetailsArr'] = $OrderFareDetailsArr;
$Data['ToTalAddress'] = GetTotalUserAddress($iUserId, "Passenger", $passengerLat, $passengerLon, $iCompanyId);
$Data['vCompany'] = $vCompany;
$Data['vCaddress'] = $vCaddress;
$Data['vImage'] = $db_companydata[0]['vImage'];
$Data['vRestuarantLocation'] = $vRestuarantLocation;
$Data['iMaxItemQty'] = $iMaxItemQty;
$Data['TotaliQty'] = $TotaliQty;
$Data['fFinalTotal'] = $fFinalTotal;
$returnArr = $Data;
$returnArr['Action'] = "1";

//echo "<pre>";print_r($returnArr);die;
echo json_encode($returnArr);
exit;
?>