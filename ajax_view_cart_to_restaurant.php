<?php

include_once('common.php');
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
include_once ('include_generalFunctions_dl.php');
$responce = array();
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
///$_REQUEST["OrderDetails"] = json_encode($_SESSION['OrderDetails']);
checkCartItemStatus($fromOrder); // Added By HJ On 18-01-2020 For Check Cart's Item Status 141 Mantis Bug - #2367
$_REQUEST["OrderDetails"] = "";
if (isset($_SESSION[$orderDetailsSession])) {
    $_REQUEST["OrderDetails"] = json_encode($_SESSION[$orderDetailsSession]);
}
//print_R($_SESSION[$orderDetailsSession]);die;
$iServiceId = '1';
$fDeliverytime = 0;
$vServiceAddress = $vBuildingNo = $vLandmark = $vAddressType = $vLatitude = $vLongitude = $vTimeZone = $iUserAddressId = $iUserId = "";
if (isset($_SESSION[$orderServiceSession])) {
    $iServiceId = $_SESSION[$orderServiceSession];
}
if (isset($_SESSION[$orderUserIdSession])) {
    $iUserId = $_SESSION[$orderUserIdSession];
}
if (isset($_SESSION[$orderLatitudeSession])) {
    $vLatitude = $_SESSION[$orderLatitudeSession];
}
if (isset($_SESSION[$orderLongitudeSession])) {
    $vLongitude = $_SESSION[$orderLongitudeSession];
}
if (isset($_SESSION[$orderAddressIdSession])) {
    $iUserAddressId = $_SESSION[$orderAddressIdSession];
}
if (!empty($iUserId) && empty($vLongitude) && empty($vLatitude) && !empty($iUserAddressId)) {
    $Dataua = $obj->MySQLSelect("SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'");
    if (count($Dataua) > 0) {
        $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
        $vBuildingNo = $Dataua[0]['vBuildingNo'];
        $vLandmark = $Dataua[0]['vLandmark'];
        $vAddressType = $Dataua[0]['vAddressType'];
        $vLatitude = $Dataua[0]['vLatitude'];
        $vLongitude = $Dataua[0]['vLongitude'];
        $vTimeZone = $Dataua[0]['vTimeZone'];
    }
}
$iCompanyId = isset($_REQUEST["id"]) ? $_REQUEST["id"] : '';
$CheckNonVegFoodType = isset($_REQUEST["CheckNonVegFoodType"]) ? $_REQUEST["CheckNonVegFoodType"] : 'No';
$couponCode = isset($_REQUEST["vCouponCode"]) ? $_REQUEST["vCouponCode"] : '';
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
if ($vLang == "" || $vLang == NULL) {
    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
}
$languageArr = getLanguageLabelsArr($vLang, '1', '');
$languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);

$restaurant_status_arr = calculate_restaurant_time_span($iCompanyId, $iUserId);
$Data['restaurantstatus'] = $restaurant_status_arr['restaurantstatus'];
$restaurantstatus = $restaurant_status_arr['restaurantstatus'];

$db_companydata = $obj->MySQLSelect("select vCompany,fMaxOfferAmt,fTargetAmt,fOfferType,fOfferAppyType,iMaxItemQty,fMinOrderValue,fOfferAmt,fPackingCharge,vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong from `company` where iCompanyId = '" . $iCompanyId . "'");
$vCompany = "Not Found";
$fMaxOfferAmt = $fTargetAmt = $iMaxItemQty = $fMinOrderValue = 0;
$fOfferAppyType = "First";
$fOfferType = "Flat";
if (count($db_companydata) > 0) {
    $vCompany = $db_companydata[0]['vCompany'];
    $fMaxOfferAmt = $db_companydata[0]['fMaxOfferAmt'];
    $fMaxOfferAmt = round($fMaxOfferAmt * $Ratio, 2);
    $fTargetAmt = $db_companydata[0]['fTargetAmt'];
    $fTargetAmt = round($fTargetAmt * $Ratio, 2);
    $fOfferAppyType = $db_companydata[0]['fOfferAppyType'];
    $fOfferType = $db_companydata[0]['fOfferType'];
    $iMaxItemQty = $db_companydata[0]['iMaxItemQty'];
    $fMinOrderValue = round($db_companydata[0]['fMinOrderValue'] * $Ratio, 2);
}
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
$OrderDetailsItemsArr = $OrderFareDetailsArr = array();
$fFinalTotal = $fTotalDiscount = $fTotalMenuItemBasePrice = $fFinalDiscountPercentage = $TotaliQty = 0;
if (!empty($OrderDetails)) {
    //Added By HJ On 09-05-2019 For Optimize Code Start
    $optionPriceArr = getAllOptionAddonPriceArr();
    $ordItemPriceArr = getAllMenuItemPriceArr();
    //Added By HJ On 09-05-2019 For Optimize Code End
    for ($j = 0; $j < count($OrderDetails); $j++) {
        $iQty = $OrderDetails[$j]['iQty'];
        //$fMenuItemPrice = FoodMenuItemBasicPrice($OrderDetails[$j]['iMenuItemId'], $iQty); //Commnent By HJ On 17-05-2019 For Optimize Below Code
        //Added By HJ On 17-05-2019 For Optimize Code Start
        $fMenuItemPrice = 0;
        if (isset($ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']]) && $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] > 0) {
            $fMenuItemPrice = $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] * $iQty;
        }
        //Added By HJ On 17-05-2019 For Optimize Code End
        //$vOptionPrice = GetFoodMenuItemOptionPrice($OrderDetails[$j]['vOptionId']); //Commnent By HJ On 17-05-2019 For Optimize Below Code
        //Added By HJ On 17-05-2019 For Optimize Code Start
        $vOptionPrice = 0;
        $explodeOption = explode(",", $OrderDetails[$j]['vOptionId']);
        for ($fd = 0; $fd < count($explodeOption); $fd++) {
            if (isset($optionPriceArr[$explodeOption[$fd]]) && $optionPriceArr[$explodeOption[$fd]] > 0) {
                $vOptionPrice += $optionPriceArr[$explodeOption[$fd]];
            }
        }
        //Added By HJ On 17-05-2019 For Optimize Code End
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
        if (!empty($ispriceshow)) {
            if ($vOptionPrice == 0) {
                $vOptionPrice = $vOptionPrice + $fMenuItemPrice;
            }
            $fTotalMenuItemBasePrice = $fTotalMenuItemBasePrice + $vOptionPrice + $vAddonPrice;
        } else {
            $fTotalMenuItemBasePrice = $fTotalMenuItemBasePrice + $vOptionPrice + $vAddonPrice + $fMenuItemPrice;
        }
    }
    if ($db_companydata[0]['fMaxOfferAmt'] > 0 && $fOfferType == "Percentage" && $fOfferAppyType != "None") {
        $fFinalDiscountPercentage = (($fTotalMenuItemBasePrice * $db_companydata[0]['fOfferAmt']) / 100);
    }

    $fTotalMenuItemBasePrice = round($fTotalMenuItemBasePrice * $Ratio, 2);
    $fFinalDiscountPercentage = round($fFinalDiscountPercentage * $Ratio, 2);
    echo '<div class="cart-data" style="display:block;">';
    echo '<div class="cart-header">
                <h5>' . $languageLabelsArr['LBL_UFX_CART'] . '</span>
              </div>';
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
            //echo "<pre>";print_r($MenuItemPriceArr);die;

            $TotOrders = $MenuItemPriceArr['TotOrders'];
            if ($fOfferType == "Flat" && $fOfferAppyType != "None" && $TotOrders == 0) {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = round($fOriginalPrice, 2);
                $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $Ratio;
                $fDiscountPrice = round($fDiscountPrice, 2);
                $fPrice = $fOriginalPrice;
                $fOfferAmt = 0;
            } else {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = round($fOriginalPrice, 2);
                $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $iQty * $Ratio;
                $fDiscountPrice = round($fDiscountPrice, 2);
                $fPrice = $MenuItemPriceArr['fPrice'] * $iQty * $Ratio;
                $fPrice = round($fPrice, 2);
                $fOfferAmt = $MenuItemPriceArr['fOfferAmt'];
                $fOfferAmt = round($fOfferAmt, 2);

                if ($fOfferType == "Flat" && $fOfferAppyType == "All") {
                    $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $Ratio;
                    $fDiscountPrice = round($fDiscountPrice, 2);
                    $fPrice = $fOriginalPrice;
                    $fOfferAmt = 0;
                }
            }

            if ($fTotalMenuItemBasePrice < $fTargetAmt && $fOfferAppyType != "None") {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = round($fOriginalPrice, 2);
                $fDiscountPrice = $fOfferAmt = 0;
                $fPrice = $fOriginalPrice;
            }

            $fTotalPrice = $fOriginalPrice;
            $fTotalPrice = round($fTotalPrice, 2);
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
            if ($optionaddonname != "") {
                $optionaddonname = trim(trim($optionaddonname), ",");
            }
            if ($vAddonId != "") {
                $addonname = GetMenuItemOptionsToppingName($vAddonId);
                if ($optionaddonname != "") {
                    $optionaddonname .= ", " . $addonname;
                } else {
                    $optionaddonname = $addonname;
                }
            }
            if ($optionaddonname != "") {
                $optionaddonname = trim(trim($optionaddonname), ",");
            }
            $OrderDetailsItemsArr[$i]['optionaddonname'] = $optionaddonname;
            $eFoodType = '';
            if (isset($OrderDetails[$i]['eFoodType']) && !empty($OrderDetails[$i]['eFoodType'])) {
                if ($OrderDetails[$i]['eFoodType'] == 'Veg') {
                    $eFoodType = '<img src="assets/img/cart-veg.jpg" alt="">';
                } else if ($OrderDetails[$i]['eFoodType'] == 'NonVeg') {
                    $eFoodType = '<img src="assets/img/cart-nonveg.jpg" alt="">';
                } else {
                    $eFoodType = '';
                }
            }
            $type = "edit";
            echo '<div class="cart-data-row"  >
                        <div class="open-modal">
                            ' . $eFoodType . '  
                            <span class="cart-item-name" onclick=showMenuTypes(' . $iMenuItemId . ',"' . $type . '","' . $i . '")>' . $vItemType . '</span>
                            <span class="cart-item"  onclick=showMenuTypes(' . $iMenuItemId . ',"' . $type . '","' . $i . '")>' . $optionaddonname . '</span>
                            <span class="edit_ele" onClick=showMenuTypes(' . $iMenuItemId . ',"' . $type . '","' . $i . '")>' . $languageArr['LBL_EDIT'] . '</span>
                            <span class="remove_ele" onClick="remove_item(' . $i . ','.$iMenuItemId.')">' . $languageArr['LBL_REMOVE_TEXT'] . '</span>
                        </div>
                        <div class="count-block complex"  id="number_update' . $i . '">
                        <button class="plus"  id="numbercart_minus_update"  onClick="numbercart_minus_update(' . $iQty . ',' . $i . ')"></button>
                        <input  id="cart_id_update"  value="' . $i . '" type="hidden">
                        <input id="numbercart_update' . $i . '" class="numbercart_update"  name="numbercart_update" value="' . $iQty . '" type="text" readonly>
                        <button class="minus" id="numbercart_plus_update" onClick="numbercart_plus_update(' . $iQty . ',' . $i . ')";></button>              
                            </div>
                        <div id="show_price_update' . $i . '" >
                            <span class="cart-item-cost" >' . $currencySymbol . ' ' . formatnum($fTotalPrice) . '</span>    
                        </div>
                    </div>';
            $TotaliQty += $iQty;
        }
    }
    echo '</div>';
    $Data['OrderDetailsItemsArr'] = $OrderDetailsItemsArr;
    //$fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
    $fPackingCharge = 0;
    if (isset($db_companydata[0]['fPackingCharge']) && $db_companydata[0]['fPackingCharge'] > 0) {
        $fPackingCharge = round($db_companydata[0]['fPackingCharge'] * $Ratio, 2);
    }
    // # Calculate Order Delivery Charge ##
    $fDeliveryCharge = 0;
    if (isset($Data['UserSelectedLatitude']) && isset($Data['UserSelectedLongitude'])) {
        //$sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong FROM company WHERE iCompanyId  = '" . $iCompanyId . "'";
        //$datac = $obj->MySQLSelect($sql);
        if (count($db_companydata) > 0) {
            $restaurantlat = $db_companydata[0]['restaurantlat'];
            $restaurantlong = $db_companydata[0]['restaurantlong'];
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
            $iFreeDeliveryRadius = $fFreeOrderPriceSubtotal = $fOrderPriceValue = $fDeliveryChargeAbove = $fDeliveryChargeBelow = 0;
            if (count($data_location) > 0) {
                $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
                $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
                $fOrderPriceValue = round($fOrderPriceValue * $Ratio, 2);
                $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
                $fDeliveryChargeAbove = round($fDeliveryChargeAbove * $Ratio, 2);
                $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
                $fDeliveryChargeBelow = round($fDeliveryChargeBelow * $Ratio, 2);
                $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
            }
            //Added By HJ On 02-01-2019 For Get All Location Delivery Charge End As Per Discuss With CD Sir
            $distance = distanceByLocation($Data['UserSelectedLatitude'], $Data['UserSelectedLongitude'], $restaurantlat, $restaurantlong, "K");
            if ($fFinalTotal >= $fOrderPriceValue) {
                $fDeliveryCharge = $fDeliveryChargeAbove;
                //$fDeliveryCharge = $fDeliveryChargeBelow;
            } else {
                $fDeliveryCharge = $fDeliveryChargeBelow;
                //$fDeliveryCharge = $fDeliveryChargeAbove;
            }
            if ($iFreeDeliveryRadius >= 0) {
                if ($distance < $iFreeDeliveryRadius) {
                    $fDeliveryCharge = 0;
                }
            }
            $fFreeOrderPriceSubtotal = round($fFreeOrderPriceSubtotal * $Ratio, 2);
            if ($fFinalTotal > $fFreeOrderPriceSubtotal) {
                $fDeliveryCharge = 0;
            }
        }
    }
    // # Calculate Order Delivery Charge ##
    $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    $fTax = $TaxArr['fTax1'];
    if ($fTax > 0) {
        $ftaxamount = $fFinalTotal - $fTotalDiscount + $fPackingCharge;
        $fTax = round((($ftaxamount * $fTax) / 100), 2);
    }
    $fCommision = $ADMIN_COMMISSION;
    $fNetTotal = $fFinalTotal + $fPackingCharge + $fDeliveryCharge + $fTax - $fTotalDiscount;
    $fTotalGenerateFare = $fNetTotal;
    $fOrderFare_For_Commission = $fFinalTotal;
    $fCommision = round((($fOrderFare_For_Commission * $fCommision) / 100), 2);
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
        $discountValue = round($discountValue * $Ratio, 2);
        //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
    }
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
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    $fOutStandingAmount = round($fOutStandingAmount * $Ratio, 2);
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
        $user_available_balance = round($user_available_balance * $Ratio, 2);
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
    $Data['fSubTotal'] = $currencySymbol . " " . formatnum($fFinalTotal);
    $Data['fTotalDiscount'] = $currencySymbol . " " . formatnum($fTotalDiscount);
    $fPackingCharge = round($fPackingCharge, 2);
    $Data['fPackingCharge'] = ($fPackingCharge > 0) ? $currencySymbol . " " . formatnum($fPackingCharge) : 0;
    $fDeliveryCharge = round($fDeliveryCharge, 2);
    $Data['fDeliveryCharge'] = ($fDeliveryCharge > 0) ? $currencySymbol . " " . formatnum($fDeliveryCharge) : 0;
    $fTax = round($fTax, 2);
    $Data['fTax'] = ($fTax > 0) ? $currencySymbol . " " . formatnum($fTax) : 0;
    $fDiscount_Val = 0;
    if (isset($Order_data[0]['fDiscount']) && $Order_data[0]['fDiscount'] > 0) {
        $fDiscount_Val = round($Order_data[0]['fDiscount'], 2);
    }
    $Data['fDiscount'] = ($fDiscount_Val > 0) ? $currencySymbol . " " . $fDiscount_Val : 0;

    // $Data['vDiscount'] = $Order_data[0]['vDiscount'];
    $fCommision = round($fCommision, 2);
    $Data['fCommision'] = ($fCommision > 0) ? $currencySymbol . " " . formatnum($fCommision) : 0;
    $fNetTotal = round($fNetTotal, 2);
    $Data['fNetTotal'] = ($fNetTotal > 0) ? $currencySymbol . " " . formatnum($fNetTotal) : $currencySymbol . " 0";
    $Data['fNetTotalAmount'] = $fNetTotal;
    $fTotalGenerateFare = round($fTotalGenerateFare, 2);
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
    /* if($fFinalTotal > 0) {
      $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $Data['fSubTotal'];
      $arrindex++;
      } */
    if ($fTotalDiscount > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fTotalDiscount);
        $arrindex++;
    }
    if ($fPackingCharge > 0) {
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $currencySymbol . " " . formatnum($fPackingCharge);
        $arrindex++;
    }
    //echo "<pre>";
    //print_r($OrderFareDetailsArr);die;

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
    // if ($fTotalGenerateFare > 0) {
    $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fTotalGenerateFare);
    $arrindex++;
    echo '<div id="total-row">';
    echo '<div class="total-row" style="display:flex;">';
    if ($TotaliQty == 1) {
        echo'<div id="subtotalamount">' . $languageLabelsArr['LBL_SUBTOTAL_TXT'] . ' (' . $TotaliQty . ' ' . $languageLabelsArr['LBL_ITEM'] . ')</div>';
    } else {
        echo'<div id="subtotalamount">' . $languageLabelsArr['LBL_SUBTOTAL_TXT'] . ' (' . $TotaliQty . ' ' . $languageLabelsArr['LBL_ITEMS'] . ')</div>';
    }
    echo'<div>' . $Data['fSubTotal'] . '</div></div>';
    echo'</div>';
    $msg = $languageLabelsArr['LBL_MAX_QTY_NOTE'] . ' ' . $iMaxItemQty . ' ' . $languageLabelsArr['LBL_TO_PROCEED'];
    if ($TotaliQty > $iMaxItemQty) {
        $class = 'show';
    } else {
        $class = 'hide';
    }
    echo '<div id="total-row" style="color:red;"  class="' . $class . ' msgmaxquty">';
    echo $msg;
    echo'</div>';
    if (count($OrderDetails) > 0) {
        echo '<div id="total-row" style="color:red;font-size: 12px !important;text-align: center !important;padding-bottom: 10px !important;" class="taxMessage">' . $languageLabelsArr['LBL_EXCLUDING_TAXES_TXT'] . '</div>';
    }
    if ($fFinalTotal < $fMinOrderValue) {
        $class = 'hide';
    } else {
        $class = 'hide';
    }
    $msg1 = $languageLabelsArr['LBL_MINIMUM_ORDER_NOTE'] . ' ' . $currencySymbol . ' ' . $fMinOrderValue;
    echo '<div id="total-row" style="color:red;"  class="' . $class . ' msgminimumtotal">';
    echo $msg1;
    echo'</div>';
} else {
    $showButton = 1;
    if ($restaurantstatus == "closed" || $TotaliQty > $iMaxItemQty) {
        $showButton = 0;
    }
    if ($showButton == 1) {
        echo '<div class="btn-hold cart-disable btnstatus">
                    <button  disabled>' . $languageLabelsArr['LBL_CHECKOUT'] . '</button>
                </div><div class="note_"id="note_cart">' . $languageLabelsArr['LBL_MANUAL_STORE_ADD_ITEMS'] . '</div>';
    }
}
//echo $TotaliQty."===".$iMaxItemQty;die;
if ($restaurantstatus == "closed" || $TotaliQty > $iMaxItemQty) {
    echo '<div class="cart-data" style="display:block;">';
    echo '<div class="cart-header" style="display:none;">
                <h5>' . $languageLabelsArr['LBL_UFX_CART'] . '</span>
              </div><div class="note_"id="note_cart" style="display:none;">' . $languageLabelsArr['LBL_MANUAL_STORE_ADD_ITEMS'] . '</div>';
    echo '<div class="btn-hold btnstatus" data-toggle="tooltip" data-placement="top" >
                <button  disabled>' . $languageLabelsArr['LBL_CHECKOUT'] . '</button>
            </div></div>';
} else {
    if (!empty($OrderDetails)) {
        echo '<div class="btn-hold btnstatus">
                <form  action="store-order?order=' . $fromOrder . '" method="post"><input type="hidden" id="id" value="' . $iCompanyId . '" name="id">
                <button type="submit" id="checkout-block" onClick="return changeValidation(' . $fFinalTotal . ',' . $fMinOrderValue . ')" >' . $languageLabelsArr['LBL_CHECKOUT'] . '</button></form>
            </div>';
    }
}
$Data['FareDetailsArr'] = $OrderFareDetailsArr;
$Data['ToTalAddress'] = GetTotalUserAddress($iUserId, "Passenger", $passengerLat, $passengerLon, $iCompanyId);
$Data['vCompany'] = $vCompany;
$Data['iMaxItemQty'] = $iMaxItemQty;
$Data['fFinalTotal'] = $fFinalTotal;
$Data['fMinOrderValue'] = $fMinOrderValue;
$returnArr = $Data;
$returnArr['Action'] = "1";
//echo json_encode($returnArr);exit;
?>