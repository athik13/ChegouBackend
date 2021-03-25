<?php

include_once('common.php');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
/* if (!isset($generalobjAdmin)) {
  require_once(TPATH_CLASS . "class.general_admin.php");
  $generalobjAdmin = new General_admin();
  } */
include_once ('include_generalFunctions_dl.php');
$responce = array();
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$_REQUEST["OrderDetails"] = json_encode($_SESSION[$orderDetailsSession]);

$iServiceId = $_SESSION[$orderServiceSession];
$iUserId = $_SESSION[$orderUserIdSession];
$iUserAddressId = $_SESSION[$orderAddressIdSession];
$iCompanyId = isset($_REQUEST["id"]) ? $_REQUEST["id"] : '';
$cart_id_update = isset($_REQUEST["cart_id_update"]) ? $_REQUEST["cart_id_update"] : '';
$CheckNonVegFoodType = isset($_REQUEST["CheckNonVegFoodType"]) ? $_REQUEST["CheckNonVegFoodType"] : 'No';
$fDeliverytime = 0;
$sql = "SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'";
$Dataua = $obj->MySQLSelect($sql);
$fMaxOfferAmt = $fTargetAmt = $iMaxItemQty = $fCompanyTax = 0;
$vServiceAddress = $vBuildingNo = $vLandmark = $vAddressType = $vLatitude = $vLongitude = $vTimeZone = "";
if (count($Dataua) > 0) {
    $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
    $vBuildingNo = $Dataua[0]['vBuildingNo'];
    $vLandmark = $Dataua[0]['vLandmark'];
    $vAddressType = $Dataua[0]['vAddressType'];
    $vLatitude = $Dataua[0]['vLatitude'];
    $vLongitude = $Dataua[0]['vLongitude'];
    $vTimeZone = $Dataua[0]['vTimeZone'];
}
$TotaliQty = 0;
$couponCode = isset($_SESSION[$orderCouponSession]) ? $_SESSION[$orderCouponSession] : '';
$ePaymentOption = isset($_REQUEST["ePaymentOption"]) ? $_REQUEST["ePaymentOption"] : '';
$vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
$OrderDetails = isset($_REQUEST["OrderDetails"]) ? $_REQUEST["OrderDetails"] : '';
$vInstruction = isset($_REQUEST["vInstruction"]) ? $_REQUEST["vInstruction"] : '';
$passengerLat = $vLatitude;
$passengerLon = $vLongitude;
$fChangeAmount = isset($_REQUEST["changeAmount"]) ? $_REQUEST["changeAmount"] : '';
$CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';

if ($CheckUserWallet == "" || $CheckUserWallet == NULL) {
    $CheckUserWallet = "No";
}
$Data = array();
$UserSelectedAddressArr = GetUserSelectedLastOrderAddressCompanyLocationWise($iUserId, "Passenger", $passengerLat, $passengerLon, $iCompanyId);
if (!empty($UserSelectedAddressArr)) {
    $Data['UserSelectedAddress'] = $UserSelectedAddressArr['UserSelectedAddress'];
    $Data['UserSelectedLatitude'] = $UserSelectedAddressArr['UserSelectedLatitude'];
    $Data['UserSelectedLongitude'] = $UserSelectedAddressArr['UserSelectedLongitude'];
    $Data['UserSelectedAddressId'] = $UserSelectedAddressArr['UserSelectedAddressId'];
}
// # Checking Distance Between Company and User Address ##
// # Checking Distance Between Company and User Address ##
$UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
$Ratio = $UserDetailsArr['Ratio'];
$currencySymbol = $UserDetailsArr['currencySymbol'];
$vLang = $_SESSION['sess_lang'];
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
    $checkvalidpromocode = CheckPromoCode($couponCode, $iUserId);
}
$ispriceshow = '';
if (isset($iServiceId) && !empty($iServiceId)) {
    $servFields = 'eType';
    $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
    if (!empty($ServiceCategoryData)) {
        if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
            $ispriceshow = $ServiceCategoryData[0]['eType'];
        }
    }
}
$OrderDetails = json_decode(stripcslashes($OrderDetails), true);
$OrderDetailscount = count($OrderDetails);
$OrderDetailsItemsArr = $OrderFareDetailsArr = array();
if (!empty($OrderDetails)) {
    $fFinalTotal = $fTotalDiscount = $fTotalMenuItemBasePrice = $fFinalDiscountPercentage = 0;
    //Added By HJ On 09-05-2019 For Optimize Code Start
    $optionPriceArr = getAllOptionAddonPriceArr();
    $ordItemPriceArr = getAllMenuItemPriceArr();
    //Added By HJ On 09-05-2019 For Optimize Code End
    for ($j = 0; $j < count($OrderDetails); $j++) {
        $typeitem = $typeitems = trim($OrderDetails[$j]['typeitem']);
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
    $fTotalMenuItemBasePrice = $generalobj->setTwoDecimalPoint($fTotalMenuItemBasePrice * $Ratio);
    $fFinalDiscountPercentage = $generalobj->setTwoDecimalPoint($fFinalDiscountPercentage * $Ratio);
    for ($i = 0; $i < count($OrderDetails); $i++) {
        $typeitem = trim($OrderDetails[$i]['typeitem']);
        if ($typeitem == 'new') {
            $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $iFoodMenuId = $OrderDetails[$i]['iFoodMenuId'];
            $vOptionId = $OrderDetails[$i]['vOptionId'];
            $vAddonId = $OrderDetails[$i]['vAddonId'];
            $iQty = $OrderDetails[$i]['iQty'];
            $vItemType = get_value('menu_items', 'vItemType_' . $vLang, 'iMenuItemId', $iMenuItemId, '', 'true');

            $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, "1", $iUserId, "Calculate", $vOptionId, $vAddonId, $iServiceId);
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
            if ($fOfferType == "Flat" && $fOfferAppyType != "None" && $TotOrders == 0) {
                $fTotalDiscount = $fDiscountPrice;
            } else {
                $fTotalDiscount = $fTotalDiscount + $fDiscountPrice;
            }

            if ($fMaxOfferAmt > 0 && $fOfferType == "Percentage" && $fOfferAppyType != "None") {
                $fTotalDiscount = ($fTotalDiscount > $fMaxOfferAmt) ? $fMaxOfferAmt : $fTotalDiscount;
                $fPrice = ($fFinalDiscountPercentage > $fMaxOfferAmt) ? $fOriginalPrice : $fPrice;
                $fOfferAmt = ($fFinalDiscountPercentage > $fMaxOfferAmt) ? 0 : $fOfferAmt;
            }

            $OrderDetailsItemsArr[$i]['iMenuItemId'] = $iMenuItemId;
            $OrderDetailsItemsArr[$i]['iFoodMenuId'] = $iFoodMenuId;
            $OrderDetailsItemsArr[$i]['vItemType'] = $vItemType;
            $OrderDetailsItemsArr[$i]['iQty'] = $iQty;
            $OrderDetailsItemsArr[$i]['fOfferAmt'] = $fOfferAmt;
            $OrderDetailsItemsArr[$i]['fOriginalPrice'] = formatnum($fOriginalPrice);
            $OrderDetailsItemsArr[$i]['fPrice'] = formatnum($fPrice);
            $optionaddonname = "";
            if ($vOptionId != "") {
                $optionname = GetMenuItemOptionsToppingName($vOptionId);
                $optionaddonname = $optionname;
            }

            if ($vAddonId != "") {
                $addonname = GetMenuItemOptionsToppingName($vAddonId);
                if ($optionaddonname != "") {
                    $optionaddonname .= ", " . $addonname;
                } else {
                    $optionaddonname = $addonname;
                }
            }
            $OrderDetailsItemsArr[$i]['optionaddonname'] = $optionaddonname;
            if ($cart_id_update == $i) {
                $showfPrice = $currencySymbol . ' ' . formatnum($fTotalPrice);
            }
            $TotaliQty += $iQty;
        }
    }
    $Data['OrderDetailsItemsArr'] = $OrderDetailsItemsArr;
    $fPackingCharge = 0;
    //$fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
    if (isset($db_companydata[0]['fPackingCharge']) && $db_companydata[0]['fPackingCharge'] > 0) {
        $fPackingCharge = $generalobj->setTwoDecimalPoint($fPackingCharge * $Ratio);
    }
    // # Calculate Order Delivery Charge ##
    $fDeliveryCharge = 0;
    if (isset($Data['UserSelectedLatitude']) && isset($Data['UserSelectedLongitude'])) {
        $sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong FROM company WHERE iCompanyId  = '" . $iCompanyId . "'";
        $datac = $obj->MySQLSelect($sql);
        if (count($datac) > 0) {
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
            //Added By HJ On 02-01-2019 For Get All Location Delivery Charge End As Per Discuss With CD Sir
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
            $distance = distanceByLocation($Data['UserSelectedLatitude'], $Data['UserSelectedLongitude'], $datac[0]['restaurantlat'], $datac[0]['restaurantlong'], "K");
            if ($fFinalTotal >= $fOrderPriceValue) {
                $fDeliveryCharge = $fDeliveryChargeAbove;
            } else {
                $fDeliveryCharge = $fDeliveryChargeBelow;
            }
            if ($iFreeDeliveryRadius >= 0) {
                if ($distance < $iFreeDeliveryRadius) {
                    $fDeliveryCharge = 0;
                }
            }
            if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0) { //added by SP for delivery charge blank then it does not count on 27-06-2019
                if ($fFinalTotal > $fFreeOrderPriceSubtotal) {
                    $fDeliveryCharge = 0;
                }
            }
        }
    }


    $Data['OrderDetailsItemsArr'] = $OrderDetailsItemsArr;
    $fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
    $fPackingCharge = $generalobj->setTwoDecimalPoint($fPackingCharge * $Ratio);

    // # Calculate Order Delivery Charge ##
    $fDeliveryCharge = 0;
    if (isset($Data['UserSelectedLatitude']) && isset($Data['UserSelectedLongitude'])) {
        $sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong FROM company WHERE iCompanyId  = '" . $iCompanyId . "'";
        $datac = $obj->MySQLSelect($sql);
        if (count($datac) > 0) {
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
            $distance = distanceByLocation($Data['UserSelectedLatitude'], $Data['UserSelectedLongitude'], $datac[0]['restaurantlat'], $datac[0]['restaurantlong'], "K");
            if ($fFinalTotal >= $fOrderPriceValue) {
                $fDeliveryCharge = $fDeliveryChargeAbove;
            } else {
                $fDeliveryCharge = $fDeliveryChargeBelow;
            }
            if ($iFreeDeliveryRadius >= 0) {
                if ($distance < $iFreeDeliveryRadius) {
                    $fDeliveryCharge = 0;
                }
            }
            if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0) { //added by SP for delivery charge blank then it does not count on 27-06-2019
                if ($fFinalTotal > $fFreeOrderPriceSubtotal) {
                    $fDeliveryCharge = 0;
                }
            }
        }
    }
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
    $fNetTotal = $fTotalGenerateFare = $fFinalTotal + $fPackingCharge + $fDeliveryCharge + $fTax + $fCompanyTax - $fTotalDiscount;
    $fOrderFare_For_Commission = $fFinalTotal;
    $fCommision = $generalobj->setTwoDecimalPoint(($fOrderFare_For_Commission * $fCommision) / 100);
    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data Start
        $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eStatus='Active'");
        if (count($getCouponCode) > 0) {
            $discountValue = $getCouponCode[0]['fDiscount'];
            $discountValueType = $getCouponCode[0]['eType'];
        }
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data End
        //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
        $discountValue = $generalobj->setTwoDecimalPoint($discountValue * $Ratio);
        //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
    }
    $Order_data = array();
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($fNetTotal * $discountValue), 1) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $fNetTotal) {
                $vDiscount = round($fNetTotal, 1) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
            }
        }
        $fNetTotal = $fNetTotal - $discountValue;
        if ($fNetTotal < 0) {
            $fNetTotal = $fTotalGenerateFare = 0;
            // $discountValue = $fNetTotal;
        }
        $fTotalGenerateFare = $fNetTotal;
        $Order_data[0]['fDiscount'] = $discountValue;
        $Order_data[0]['vDiscount'] = $vDiscount;
    }
    /* Check Coupon Code Total Fare  End */
    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = 0;
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    $fOutStandingAmount = $generalobj->setTwoDecimalPoint($fOutStandingAmount * $Ratio);
    if ($fOutStandingAmount > 0) {
        $fNetTotal = $fNetTotal + $fOutStandingAmount;
        $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
    }

    /* Checking For Passenger Outstanding Amount */
    /* Check debit wallet For Count Total Order Fare Start */
    $user_wallet_debit_amount = 0;
    $DisplayCardPayment = "Yes";
    if ($iUserId > 0 && $CheckUserWallet == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        $user_available_balance = $generalobj->setTwoDecimalPoint($user_available_balance * $Ratio);
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
            $fTotalGenerateFare = $fNetTotal;
            $DisplayCardPayment = "Yes";
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal = 0;
            $fTotalGenerateFare = $fNetTotal;
            $DisplayCardPayment = "No";
        }
    }

    /* Check debit wallet For Count Total Order Fare End */
    if ($fNetTotal < 0) {
        $fNetTotal = $fTotalGenerateFare = 0;
    }
    $Data['showfPrice'] = $showfPrice;
    $Data['TotaliQty'] = $TotaliQty;
    $Data['totalDiscount'] = $fTotalDiscount;
    $Data['fSubTotal'] = $currencySymbol . " " . formatnum($fFinalTotal);
    $Data['fTotalDiscount'] = $currencySymbol . " " . formatnum($fTotalDiscount);
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
    if (isset($Order_data[0]['fDiscount'])) {
        $fDiscount_Val = $generalobj->setTwoDecimalPoint($Order_data[0]['fDiscount']);
    }
    $Data['Discount_Val'] = $fDiscount_Val;
    $Data['fDiscount'] = ($fDiscount_Val > 0) ? $currencySymbol . " " . $fDiscount_Val : 0;
    //$fCompanyTax = round($fCompanyTax, 2);
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
$restaurant_status_arr = calculate_restaurant_time_span_web($iCompanyId, $iUserId);
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
echo json_encode($returnArr);
exit;
?>