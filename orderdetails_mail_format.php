<?
 //include_once('generalFunctions.php'); 
$sql = "SELECT * FROM  `orders` WHERE iOrderId = '" . $iOrderId . "'";
$db_order_data = $obj->MySQLSelect($sql);
$returnArrData['vOrderNo'] = $db_order_data[0]['vOrderNo'];
$returnArrData['iStatusCode'] = $db_order_data[0]['iStatusCode'];
$iCompanyId = $db_order_data[0]['iCompanyId'];
$iDriverId = $db_order_data[0]['iDriverId'];
$iUserId = $db_order_data[0]['iUserId'];
$iServiceId = $db_order_data[0]['iServiceId'];
$ssql1 = '';
if ($sendTo == 'Restaurant') {
    $eUserType = 'Company';
    $UserDetailsArr = $generalobj->getCompanyCurrencyLanguageDetailsWeb($iCompanyId, $iOrderId);
    $iMemberId = $iCompanyId;
    $CompanyData = $generalobj->get_value('company', 'vEmail', 'iCompanyId', $iMemberId);
    $returnArrData['vEmail'] = $CompanyData[0]['vEmail'];
} else if ($sendTo == 'Driver') {
    $eUserType = 'Driver';
    $ssql1 .= "AND eAvailable = 'Yes'";
    $UserDetailsArr = $generalobj->getDriverCurrencyLanguageDetailsWeb($iDriverId, $iOrderId);
    $iMemberId = $iDriverId;
    $DriverData = $generalobj->get_value('register_driver', 'vEmail', 'iDriverId', $iMemberId);
    $returnArrData['vEmail'] = $DriverData[0]['vEmail'];
} else {
    $eUserType = 'Passenger';
    $UserDetailsArr = $generalobj->getUserCurrencyLanguageDetailsWeb($iUserId, $iOrderId);
    $iMemberId = $iUserId;
    $UserData = $generalobj->get_value('register_user', 'vEmail', 'iUserId', $iMemberId);
    $returnArrData['vEmail'] = $UserData[0]['vEmail'];
}

$vSymbol = $UserDetailsArr['currencySymbol'];
$priceRatio = $UserDetailsArr['Ratio'];
$vLang = $UserDetailsArr['vLang'];
if(empty($iServiceId)) $iServiceId = 1;
$languageLabelsArr = getLanguageLabelsArr($vLang, "1",$iServiceId);
$returnArrData['vSymbol'] = $vSymbol;
$returnArrData['priceRatio'] = $priceRatio;
$returnArrData ['vLang'] = $vLang;
if ($iDriverId > 0) {
    $DriverData = $generalobj->get_value('register_driver', 'concat(vName," ",vLastName) as drivername', 'iDriverId', $iDriverId);
    $DriverName = $DriverData[0]['drivername'];
    $returnArrData['DriverName'] = $DriverName;
    $driver_vehicle_info = $generalobj->getDriverVehicleInfo($iOrderId);
    $returnArrData['DriverVehicle'] = $driver_vehicle_info[0]['DriverVehicle'];
    $returnArrData['DriverVehicleLicencePlate'] = $driver_vehicle_info[0]['vLicencePlate'];
}
$returnArrData['UserName'] = $db_order_data[0]['vName'] . " " . $db_order_data[0]['vLastName'];
$restFields = 'vCompany,vCaddress as vRestuarantLocation,vPhone,vCode,vRestuarantLocationLat,vRestuarantLocationLong';
$CompanyData = get_value('company', $restFields, 'iCompanyId', $iCompanyId);
$returnArrData['CompanyName'] = $CompanyData[0]['vCompany'];
$returnArrData['vRestuarantLocation'] = $CompanyData[0]['vRestuarantLocation'];
$returnArrData['vRestuarantLocationLat'] = $CompanyData[0]['vRestuarantLocationLat'];
$returnArrData['vRestuarantLocationLong'] = $CompanyData[0]['vRestuarantLocationLong'];
$UserAddressArr = $generalobj->GetUserAddressDetailWeb($iUserId, "Passenger", $db_order_data[0]['iUserAddressId']);
$returnArrData['DeliveryAddress'] = $UserAddressArr['UserAddress'];
$returnArrData['vLatitude'] = $UserAddressArr['vLatitude'];
$returnArrData['vLongitude'] = $UserAddressArr['vLongitude'];
$returnArrData['vStatus'] = $generalobj->getOrderStatus($iOrderId, $vLang, $CompanyData[0]['vCompany']);
$serverTimeZone = date_default_timezone_get();
if ($db_order_data[0]['vTimeZone'] == '') {
    $db_order_data[0]['vTimeZone'] = $serverTimeZone;
}
$date = converToTz($db_order_data[0]['tOrderRequestDate'], $db_order_data[0]['vTimeZone'], $serverTimeZone, "Y-m-d H:i:s");
$OrderTime = date('d M, Y h:iA', strtotime($date));
$returnArrData['tOrderRequestDate_Org'] = $date;
$returnArrData['OrderRequestDate'] = $OrderTime;
if ($db_order_data[0]['fCancellationCharge'] > 0 && $db_order_data[0]['vTimeZone'] != "") {
    $dDeliveryDate = converToTz($db_order_data[0]['dDeliveryDate'], $db_order_data[0]['vTimeZone'], $serverTimeZone);
    $tOrderRequestDatenew = converToTz($db_order_data[0]['tOrderRequestDate'], $db_order_data[0]['vTimeZone'], $serverTimeZone);
} else {
    $dDeliveryDate = $db_order_data[0]['dDeliveryDate'];
    $tOrderRequestDatenew = $db_order_data[0]['tOrderRequestDate'];
}
$returnArrData['OrderRequestDatenew'] = $tOrderRequestDatenew;
$returnArrData['DeliveryDate'] = $dDeliveryDate;
$query = "SELECT iOrderDetailId FROM order_details WHERE iOrderId = '" . $iOrderId . "' $ssql1";
$orderDetailId = $obj->MySQLSelect($query);
$returnArrData['TotalItems'] = strval(count($orderDetailId));
foreach ($orderDetailId as $k => $val) {
    $ItemLists[] = $generalobj->DisplayOrderDetailItemList($val['iOrderDetailId'], $iMemberId, $eUserType, $iOrderId);
}
$all_data_new = array();
if ($ItemLists != '') {
    foreach ($ItemLists as $k => $item) {
        $iQty = ($item['iQty'] != '') ? $item['iQty'] : '';
        $MenuItem = ($item['MenuItem'] != '') ? $item['MenuItem'] : '';
        $fPrice = ($item['fPrice'] != '') ? $item['fPrice'] : '';
        $fTotPrice = ($item['fTotPrice'] != '') ? $item['fTotPrice'] : '';
        $eAvailable = ($item['eAvailable'] != '') ? $item['eAvailable'] : '';
        $AddOnItemArr = ($item['AddOnItemArr'] != '') ? $item['AddOnItemArr'] : '';
        $iOrderDetailId = ($item['iOrderDetailId'] != '') ? $item['iOrderDetailId'] : '';
        $eFoodType = ($item['eFoodType'] != '') ? $item['eFoodType'] : '';
        $all_data_new[$k]['iOrderDetailId'] = $iOrderDetailId;
        $all_data_new[$k]['iQty'] = $iQty;
        $all_data_new[$k]['MenuItem'] = $MenuItem;
        $all_data_new[$k]['fPrice'] = $fPrice;
        $all_data_new[$k]['fTotPrice'] = $fTotPrice;
        $all_data_new[$k]['eAvailable'] = $eAvailable;
        $all_data_new[$k]['eFoodType'] = $eFoodType;
        $vOptionName = ($item['vOptionName'] != '') ? $item['vOptionName'] : '';
        $addonTitleArr = array();
        $addonTitle = '';
        if (!empty($AddOnItemArr)) {
            foreach ($AddOnItemArr as $addonkey => $addonvalue) {
                $addonTitleArr[] = $addonvalue['vAddOnItemName'];
            }
            $addonTitle = implode("/", $addonTitleArr);
        }
        $all_data_new[$k]['SubTitle'] = '';
        if ($vOptionName != '' && $addonTitle != '') {
            $all_data_new[$k]['SubTitle'] = $vOptionName . "/" . $addonTitle;
        }
    }
}
$returnArrData['itemlist'] = $all_data_new;
$returnArr['subtotal'] = $db_order_data[0]['fSubTotal'] * $priceRatio;
$returnArr['PackingCharge'] = $db_order_data[0]['fPackingCharge'] * $priceRatio;
$returnArr['DeliveryCharge'] = $db_order_data[0]['fDeliveryCharge'] * $priceRatio;
$returnArr['Tax'] = $db_order_data[0]['fTax'] * $priceRatio;
$returnArr['TotalGenerateFare'] = $db_order_data[0]['fTotalGenerateFare'] * $priceRatio;
$returnArr['fDiscount'] = $db_order_data[0]['fDiscount'] * $priceRatio;
$returnArr['fNetTotal'] = $db_order_data[0]['fNetTotal'] * $priceRatio;
$returnArr['fWalletDebit'] = $db_order_data[0]['fWalletDebit'] * $priceRatio;
$returnArr['fOutStandingAmount'] = $db_order_data[0]['fOutStandingAmount'] * $priceRatio;
$returnArr['fCommision'] = $db_order_data[0]['fCommision'] * $priceRatio;
$returnArr['fOffersDiscount'] = $db_order_data[0]['fOffersDiscount'] * $priceRatio;
$returnArr['fRefundAmount'] = $db_order_data[0]['fRefundAmount'] * $priceRatio;
$returnArr['fRestaurantPaidAmount'] = $db_order_data[0]['fRestaurantPaidAmount'] * $priceRatio;
$returnArr['fCancellationCharge'] = $db_order_data[0]['fCancellationCharge'] * $priceRatio;
$returnArr['fDriverPaidAmount'] = $db_order_data[0]['fDriverPaidAmount'] * $priceRatio;
$subtotal = formatNum($returnArr['subtotal']);
$fPackingCharge = formatNum($returnArr['PackingCharge']);
$fDeliveryCharge = formatNum($returnArr['DeliveryCharge']);
$fTax = formatNum($returnArr['Tax']);
$fTotalGenerateFare = formatNum($returnArr['TotalGenerateFare']);
$fNetTotal = formatNum($returnArr['fNetTotal']);
$fDiscount = formatNum($returnArr['fDiscount']);
$fWalletDebit = formatNum($returnArr['fWalletDebit']);
$fOutStandingAmount = formatNum($returnArr['fOutStandingAmount']);
$fCommision = formatNum($returnArr['fCommision']);
$fOffersDiscount = formatNum($returnArr['fOffersDiscount']);
$fRefundAmount = formatNum($returnArr['fRefundAmount']);
$fRestaurantPaidAmount = formatNum($returnArr['fRestaurantPaidAmount']);
$fCancellationCharge = formatNum($returnArr['fCancellationCharge']);
$fDriverPaidAmount = formatNum($returnArr['fDriverPaidAmount']);

$returnArrData['subtotal'] = $vSymbol . " " . $subtotal;
$returnArrData['fPackingCharge'] = $vSymbol . " " . $fPackingCharge;
$returnArrData['fDeliveryCharge'] = $vSymbol . " " . $fDeliveryCharge;
$returnArrData['fTax'] = $vSymbol . " " . $fTax;
$returnArrData['fTotalGenerateFare'] = $vSymbol . " " . $fTotalGenerateFare;

$returnArrData['fDiscount'] = $vSymbol . " " . $fDiscount;
$returnArrData['fNetTotal'] = $vSymbol . " " . $fNetTotal;
$returnArrData['fWalletDebit'] = $vSymbol . " " . $fWalletDebit;
$returnArrData['fOutStandingAmount'] = $vSymbol . " " . $fOutStandingAmount;
$returnArrData['fCommision'] = $vSymbol . " " . $fCommision;
$returnArrData['fOffersDiscount'] = $vSymbol . " " . $fOffersDiscount;
$returnArrData['fRefundAmount'] = $vSymbol . " " . $fRefundAmount;
$returnArrData['fRestaurantPaidAmount'] = $vSymbol . " " . $fRestaurantPaidAmount;
$returnArrData['fCancellationCharge'] = $vSymbol . " " . $fCancellationCharge;
$returnArrData['fDriverPaidAmount'] = $vSymbol . " " . $fDriverPaidAmount;

// prescription Images displayed by sneha start
$prescriptiondata = '';
$table_pres = 'prescription_images';
$val = $obj->MySQLSelect('select 1 from '.$table_pres.' LIMIT 1');

if($val !== FALSE) { 
    $prescriptiondata = $obj->MySQLSelect("SELECT vImage FROM ".$table_pres." WHERE order_id = '".$iOrderId."'");
}

// prescription Images displayed by sneha start
$returnArrData['PrescriptionImages'] = '';
$img_url = $tconfig["tsite_upload_prescription_image"];
if(!empty($prescriptiondata)) {
    $returnArrData['PrescriptionImages'] = 'Click on below links to check prescription<br>';
    foreach($prescriptiondata as $key=>$val) {
        $PresImageUrl = $img_url."/".$val['vImage']; $key_pres = $key+1;
        $returnArrData['PrescriptionImages'] .= '<a href='.$PresImageUrl.' target="_blank" >Prescription Image '.$key_pres.'</a><br>';
    } 
}
// prescription Images displayed by sneha end
//added by SP for rounding off currency wise on 19-11-2019 start
if($eUserType=='Passenger') { 
    $tripsql = "SELECT vCurrencyPassenger FROM trips WHERE iOrderId='" . $iOrderId . "'";
    $DataTrips = $obj->MySQLSelect($tripsql);
        
    $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, ru.vCurrencyPassenger, cu.ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $db_order_data[0]['iUserId'] . "'";
    $currData = $obj->MySQLSelect($sqlp);
    $vCurrency = $currData[0]['vName'];
    $samecur = ($DataTrips[0]['vCurrencyPassenger']==$currData[0]['vCurrencyPassenger']) ? 1 : 0;
       
    if(isset($db_order_data[0]['fRoundingAmount']) && !empty($db_order_data[0]['fRoundingAmount']) && $db_order_data[0]['fRoundingAmount']!=0 && $samecur==1 && $currData[0]['eRoundingOffEnable'] == "Yes") { 

        $roundingOffTotal_fare_amountArr['method'] = $db_order_data[0]['eRoundingType'];
        $roundingOffTotal_fare_amountArr['differenceValue'] = $db_order_data[0]['fRoundingAmount'];
        
        $roundingOffTotal_fare_amountArr = $generalobj->getRoundingOffAmounttripweb($fNetTotal,$db_order_data[0]['fRoundingAmount'],$db_order_data[0]['eRoundingType']);
        
        if($roundingOffTotal_fare_amountArr['method'] == "Addition"){
            $roundingMethod = "";
        }else{
            $roundingMethod = "-";
        }
        $returnArrData['fNetTotal'] = $vSymbol . " ". isset($roundingOffTotal_fare_amountArr['finalFareValue']) && $roundingOffTotal_fare_amountArr['finalFareValue'] != '' ? $roundingOffTotal_fare_amountArr['finalFareValue'] : "0.00"; 
        $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00"; 
          
        $returnArrData['roundingamt'] = $roundingMethod." ". $vSymbol . "". $rounding_diff;
    }
}
//added by SP for rounding off currency wise on 19-11-2019 end

$returnArrData['eTakeaway'] = $db_order_data[0]['eTakeaway'];

if($db_order_data[0]['selectedPreferences'] != "")
{
    // Added by HV for Delivery Preference
    $deliveryPrefSql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_EXTRACT(tTitle, '$.tTitle_".$vLang."')) as tTitle, JSON_UNQUOTE(JSON_EXTRACT(tDescription, '$.tDescription_".$vLang."')) as tDescription FROM delivery_preferences WHERE iPreferenceId IN (".$db_order_data[0]['selectedPreferences'].")";
    $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);

    $deliveryPrefHtml = "";

    if(count($deliveryPrefSqlData) > 0)
    {
       foreach ($deliveryPrefSqlData as $pref) 
       {
           $deliveryPrefHtml .= '<p style="margin:0;padding:0;margin-bottom:0px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">- <span>'.$pref['tTitle'].'</span></p>';
       }    
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
        <?php if ($returnArrData['iStatusCode'] == '6') { ?>
            <table>
                <tbody>
                    <tr>
                        <td>
                            <div>
                                <table align="center" border="0" cellpadding="2" cellspacing="0" width="671">
                                    <tbody>
                                        <tr>
                                            <td style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#4d4d4d; text-align:justify;" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <table width="100%" cellspacing="0" cellpadding="0" style="margin:0;padding:0">
                                                                    <tbody><tr style="margin:0;padding:0">
                                                                            <td width="20%" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:12px;min-width:100px;max-width:100%;display:inline-block">
                                                                                <p style="margin:0;padding:0;margin-bottom:5px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">Order No:</p>
                                                                                <h5 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px"><?= $returnArrData['vOrderNo'] ?></h5>
                                                                            </td>
                                                                            <td width="30%" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:12px;/*min-width:100px*/;max-width:100%;display:inline-block">
                                                                                <p style="margin:0;padding:0;margin-bottom:5px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6"><?= $languageLabelsArr['LBL_STORE_NAME']; ?>:</p>
                                                                                <h5 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px"><?= ucfirst($returnArrData['CompanyName']) ?></h5>
                                                                            </td>
                                                                        </tr>
                                                                        <!-- <tr style="margin:0;padding:0">
                                                            <td width="20%" align="right" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:12px;min-width:100px;max-width:100%;display:inline-block;vertical-align:top">

                                                                <a href="#" alt="Download Invoice" style="margin:0;padding:5px 10px;color:#fff;background:#f5861f;display:inherit;text-decoration:none;text-align:center;line-height:30px;width:190px;height:30px;border-radius:3px;font-weight:600;font-size:12px;outline:0;border:0" target="_blank">
                                                                    Download Order Summary</a>
                                                            </td>
                                                </tr> -->
                                                                    </tbody></table>
                                                            </td></tr>
                                                            <?php if($db_order_data[0]['eTakeaway'] == 'Yes') { ?>
                                                            <tr style="margin:0;padding:0">
                                                                <td width="100%" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:12px;min-width:100px;max-width:100%;display:inline-block">
                                                                    <p style="margin:0;padding:0;margin-bottom:5px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6"><?= $languageLabelsArr['LBL_TAKE_WAY']; ?>:</p>
                                                                    <h5 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px"><?= str_replace('#RESTAURANT_NAME#',$returnArrData['CompanyName'],$languageLabelsArr['LBL_TAKE_AWAY_ORDER_NOTE']); ?></h5>
                                                                </td>
                                                            </tr>
                                                            <?php } ?>
                                                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                                                        <tr>                                
                                                    <table width="100%" cellspacing="0" cellpadding="0" style="margin:0;padding:0">
                                                        <thead style="margin:0;padding:0;text-align:left;background:#dedede;border-collapse:collapse;border-spacing:0;border-color:#eee">
                                                            <tr style="margin:0;padding:0">
                                                                <th style="margin:0;padding:15px;font-size:12px">Item Name</th>
                                                                <th style="margin:0;padding:15px;font-size:12px;">Quantity</th>
                                                                <th align="right" style="margin:0;padding:15px;font-size:12px">Price</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody style="margin:0;padding:0">
                                                            <?php foreach ($returnArrData['itemlist'] as $key => $value) { ?>
                                                                <tr><td><span></span></td></tr>
                                                                <tr style="margin:0;padding:0">
                                                                    <td style="vertical-align:top;margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #d3d3d3;font-size:12px"><?= $value['MenuItem'] ?>
                                                                        <br/><small><?= $value['SubTitle'] ?></small>
                                                                    </td>
                                                                    <td style="margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #d3d3d3;font-size:12px;"><?= $value['iQty'] ?></td>

                                                                    <td align="right" style="margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #d3d3d3;font-size:12px"><?= $value['fTotPrice'] ?></td>
                                                                </tr>
                                                                <tr width="100%">
                                                                    <td> <div style="height:1px;width:100%;clear:both"></div> </td>
                                                                    <td> <div style="height:1px;width:100%;clear:both"></div> </td>
                                                                    <td> <div style="height:1px;width:100%;clear:both"></div> </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                        <tfoot style="margin:0;padding:0">
                                                            <tr style="margin:0;padding:0">
                                                                <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_BILL_SUB_TOTAL']; ?></td>
                                                                <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['subtotal'] ?></span></td>
                                                            </tr>
                                                            <?php if ($fOffersDiscount > 0) { ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']; ?></td>
                                                                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= '- ' . $returnArrData['fOffersDiscount'] ?></span></td>
                                                                </tr>
                                                            <?php } if ($fPackingCharge > 0) { ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_PACKING_CHARGE']; ?></td>
                                                                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['fPackingCharge'] ?></span></td>
                                                                </tr>
                                                            <?php } if ($fDeliveryCharge > 0) { ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']; ?></td>
                                                                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['fDeliveryCharge'] ?></span></td>
                                                                </tr>
                                                            <?php } if ($fOutStandingAmount > 0) { ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']; ?></td>
                                                                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['fOutStandingAmount'] ?></span></td>
                                                                </tr>
                                                            <?php } if ($fTax > 0) { ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px;"><?= $languageLabelsArr['LBL_TOTAL_TAX_TXT']; ?></td>
                                                                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px"><span><?= $returnArrData['fTax'] ?></span></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            if ($fDiscount > 0) {
                                                                ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px;"><?= $languageLabelsArr['LBL_DISCOUNT_TXT']; ?></td>
                                                                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px"><span><?= "- " . $returnArrData['fDiscount'] ?></span></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            if ($fWalletDebit > 0) {
                                                                ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px;"><?= $languageLabelsArr['LBL_WALLET_ADJUSTMENT']; ?></td>
                                                                    <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px"><span><?= "- " . $returnArrData['fWalletDebit'] ?></span></td>
                                                                </tr>
                                                           <?php } if(!empty($returnArrData['roundingamt'])) { ?>
                        <tr style="margin:0;padding:0">
                              <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px;"><?= $languageLabelsArr['LBL_ROUNDING_DIFF_TXT']; ?></td>
                              <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['roundingamt'] ?></span></td>
                          </tr>
                          <?php } ?>
                                                            <tr style="margin:0;padding:0">
                                                                <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:13px;color:#F17E13"><?= $languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']; ?></td>
                                                                <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:13px;text-align:right;border:0;padding-right:15px;color:#F17E13"><span><?= $returnArrData['fNetTotal'] ?></span></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table width="100%" cellspacing="0" cellpadding="0" style="margin:0;padding:0">
                                                    <tbody>
                                                        <?php /*if($db_order_data[0]['eTakeaway'] == "Yes") { ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td style="margin:0;padding:0">
                                                                <table align="left" cellspacing="0" cellpadding="0" style="margin:0;padding:0;width:190px;max-width:100%;padding-bottom:10px;text-align:left!important">
                                                                    <tbody>
                                                                        <tr style="margin:0;padding:0">
                                                                            <td style="margin:0;padding:0">
                                                                                <h6 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;text-transform:initial"><?= $languageLabelsArr['LBL_RESTAURANT_ADDRESS'] ?>:</h6>
                                                                                <p style="margin:0;padding:0;margin-bottom:0px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">
                                                                                    <span><?= $returnArrData['vRestuarantLocation'] ?></span>
                                                                                </p>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <?php } else { ?>   
                                                        <tr style="margin:0;padding:0">
                                                            <td style="margin:0;padding:0">
                                                                <table align="left" cellspacing="0" cellpadding="0" style="margin:0;padding:0;width:190px;max-width:100%;padding-bottom:10px;text-align:left!important">
                                                                    <tbody>
                                                                        <tr style="margin:0;padding:0">
                                                                            <td style="margin:0;padding:0">
                                                                                <h6 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;text-transform:initial"><?= $languageLabelsArr['LBL_DELIVERY_ADDRESS'] ?>:</h6>
                                                                                <p style="margin:0;padding:0;margin-bottom:0px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">
                                                                                    <span><?= $returnArrData['DeliveryAddress'] ?></span>
                                                                                </p>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <?php } */?>
                                                        <?php if($deliveryPrefHtml != "") { ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td style="margin:0;padding:0">
                                                                <table align="left" cellspacing="0" cellpadding="0" style="margin:0;padding:0;width:190px;max-width:100%;padding-bottom:10px;text-align:left!important">
                                                                    <tbody>
                                                                        <tr style="margin:0;padding:0">
                                                                            <td style="margin:0;padding:0">
                                                                                <h6 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;text-transform:initial"><?= $languageLabelsArr['LBL_DELIVERY_PREF'] ?>:</h6>
                                                                                <p style="margin:0;padding:0;margin-bottom:0px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">
                                                                                    <span><?= $deliveryPrefHtml ?></span>
                                                                                </p>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </tbody>
    </table>
<?php } else { ?>
    <table>
        <tbody>
            <tr>
                <td><div>
                        <table align="center" border="0" cellpadding="2" cellspacing="0" width="671">
                            <tbody>
                                <tr>
                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#4d4d4d; text-align:justify;" valign="top">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <table width="100%" cellspacing="0" cellpadding="0" style="margin:0;padding:0">
                                                            <tbody>
                                                                <?php if($db_order_data[0]['eTakeaway'] == 'Yes') { ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="100%" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:12px;min-width:100px;max-width:100%;display:inline-block;text-align: center;">
                                                                        <h5 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px"><?= str_replace('#RESTAURANT_NAME#',$returnArrData['CompanyName'],$languageLabelsArr['LBL_TAKE_WAY_ORDER']) ?></h5>
                                                                    </td>
                                                                </tr>
                                                                <?php } ?>
                                                                <tr style="margin:0;padding:0">
                                                                    <td width="30%" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:12px;min-width:100px;max-width:100%;display:inline-block">
                                                                        <p style="margin:0;padding:0;margin-bottom:5px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">Order No:</p>
                                                                        <h5 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px"><?= $returnArrData['vOrderNo'] ?></h5>
                                                                    </td>
                                                                    <!-- <td width="30%" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:12px;min-width:100px;max-width:100%;display:inline-block">
                                                                            <p style="margin:0;padding:0;margin-bottom:5px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">Restaurant:</p>
                                                                            <h5 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px"><?= ucfirst($returnArrData['CompanyName']) ?></h5>
                                                                    </td> -->
                                                                    <td width="30%" style="margin:0;padding:0;margin-bottom:15px;font-weight:bold;font-size:12px;/*min-width:100px*/;max-width:100%;display:inline-block">
                                                                        <p style="margin:0;padding:0;margin-bottom:5px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6"><?= $languageLabelsArr['LBL_STORE_NAME']; ?>:</p>
                                                                        <h5 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px"><?= ucfirst($returnArrData['CompanyName']) ?></h5>
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                    </td></tr>
                                                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                                                <tr>
                                            <table width="100%" cellspacing="0" cellpadding="0" style="margin:0;padding:0">
                                                <thead style="margin:0;padding:0;text-align:left;background:#dedede;border-collapse:collapse;border-spacing:0;border-color:#eee">
                                                    <tr style="margin:0;padding:0">
                                                        <th style="margin:0;padding:15px;font-size:12px">Item Name</th>
                                                        <th style="margin:0;padding:15px;font-size:12px;">Quantity</th>
                                                        <th align="right" style="margin:0;padding:15px;font-size:12px">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="margin:0;padding:0">
                                                    <?php foreach ($returnArrData['itemlist'] as $key => $value) { ?>
                                                        <tr><td><span></span></td></tr>
                                                        <tr style="margin:0;padding:0">
                                                            <td style="vertical-align:top;margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #d3d3d3;font-size:12px"><?= $value['MenuItem'] ?>
                                                                <br/><small><?= $value['SubTitle'] ?></small>
                                                            </td>
                                                            <td style="margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #d3d3d3;font-size:12px;"><?= $value['iQty'] ?></td>

                                                            <td align="right" style="margin:0;padding:15px;font-weight:bold;border-bottom:1px solid #d3d3d3;font-size:12px"><?= $value['fTotPrice'] ?></td>
                                                        </tr>
                                                        <tr width="100%">
                                                            <td> <div style="height:1px;width:100%;clear:both"></div> </td>
                                                            <td> <div style="height:1px;width:100%;clear:both"></div> </td>
                                                            <td> <div style="height:1px;width:100%;clear:both"></div> </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                                <tfoot style="margin:0;padding:0">
                                                    <tr style="margin:0;padding:0">
                                                        <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_BILL_SUB_TOTAL']; ?></td>
                                                        <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['subtotal'] ?></span></td>
                                                    </tr>
                                                    <?php if ($fOffersDiscount > 0) { ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']; ?></td>
                                                            <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= '- ' . $returnArrData['fOffersDiscount'] ?></span></td>
                                                        </tr>
                                                    <?php } if ($fPackingCharge > 0) { ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_PACKING_CHARGE']; ?></td>
                                                            <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['fPackingCharge'] ?></span></td>
                                                        </tr>
                                                    <?php } if ($fDeliveryCharge > 0) { ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']; ?></td>
                                                            <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['fDeliveryCharge'] ?></span></td>
                                                        </tr>
                                                    <?php } if ($fOutStandingAmount > 0) { ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px"><?= $languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']; ?></td>
                                                            <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px;"><span><?= $returnArrData['fOutStandingAmount'] ?></span></td>
                                                        </tr>
                                                    <?php } if ($fTax > 0) { ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px;"><?= $languageLabelsArr['LBL_TOTAL_TAX_TXT']; ?></td>
                                                            <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px"><span><?= $returnArrData['fTax'] ?></span></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    if ($fDiscount > 0) {
                                                        ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px;"><?= $languageLabelsArr['LBL_DISCOUNT_TXT']; ?></td>
                                                            <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px"><span><?= "- " . $returnArrData['fDiscount'] ?></span></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    if ($fWalletDebit > 0) {
                                                        ?>
                                                        <tr style="margin:0;padding:0">
                                                            <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:12px;"><?= $languageLabelsArr['LBL_WALLET_ADJUSTMENT']; ?></td>
                                                            <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:12px;text-align:right;border:0;padding-right:15px"><span><?= "- " . $returnArrData['fWalletDebit'] ?></span></td>
                                                        </tr>
                                                    <?php } if(!empty($returnArrData['roundingamt'])) { ?>
                    <tr style="margin:0;padding:0">
                                                        <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:13px;color:#F17E13"><?= $languageLabelsArr['LBL_ROUNDING_DIFF_TXT']; ?></td>
                                                        <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:13px;text-align:right;border:0;padding-right:15px;color:#F17E13"><span><?= $returnArrData['roundingamt'] ?></span></td>
                                                    </tr>
                      <?php } ?>
                                                    <tr style="margin:0;padding:0">
                                                        <td width="40%" scope="row" colspan="2" style="margin:0;padding:5px 0;text-align:right;font-weight:bold;border:0;font-size:13px;color:#F17E13"><?= $languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']; ?></td>
                                                        <td width="20%" style="margin:0;padding:5px 0;font-weight:bold;border-bottom:1px solid #e9e9e9;font-size:13px;text-align:right;border:0;padding-right:15px;color:#F17E13"><span><?= $returnArrData['fNetTotal'] ?></span></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                </tr>
                                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                                <tr>
                                    <td>
                                        <table width="100%" cellspacing="0" cellpadding="0" style="margin:0;padding:0">
                                            <tbody>
                                                <?php if($db_order_data[0]['eTakeaway'] == "Yes") { ?>
                                                <tr style="margin:0;padding:0">
                                                    <td style="margin:0;padding:0">
                                                        <table align="left" cellspacing="0" cellpadding="0" style="margin:0;padding:0;width:190px;max-width:100%;padding-bottom:10px;text-align:left!important">
                                                            <tbody>
                                                                <tr style="margin:0;padding:0">
                                                                    <td style="margin:0;padding:0">
                                                                        <h6 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;text-transform:initial"><?= $languageLabelsArr['LBL_RESTAURANT_ADDRESS'] ?>:</h6>
                                                                        <p style="margin:0;padding:0;margin-bottom:0px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">
                                                                            <span><?= $returnArrData['vRestuarantLocation'] ?></span>
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <?php } else { ?>   
                                                <tr style="margin:0;padding:0">
                                                    <td style="margin:0;padding:0">
                                                        <table align="left" cellspacing="0" cellpadding="0" style="margin:0;padding:0;width:190px;max-width:100%;padding-bottom:10px;text-align:left!important">
                                                            <tbody>
                                                                <tr style="margin:0;padding:0">
                                                                    <td style="margin:0;padding:0">
                                                                        <h6 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;text-transform:initial"><?= $languageLabelsArr['LBL_DELIVERY_ADDRESS'] ?>:</h6>
                                                                        <p style="margin:0;padding:0;margin-bottom:0px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">
                                                                            <span><?= $returnArrData['DeliveryAddress'] ?></span>
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <!-- <table align="left" cellspacing="0" cellpadding="0" style="margin:0;padding:0;width:190px;max-width:100%;padding-bottom:10px;text-align:left!important">
                                                            <tbody>
                                                            <tr style="margin:0;padding:0">
                                                                    <td style="margin:0;padding:0">
                                                                            <h6 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;text-transform:initial">Landmark:</h6>
                                                                            <p style="margin:0;padding:0;margin-bottom:0px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6"><?= $returnArrData['subtotal'] ?></p>
                                                                    </td>
                                                            </tr>
                                                            </tbody>
                                                            </table> -->
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                                <?php if($deliveryPrefHtml != "") { ?>
                                                <tr style="margin:0;padding:0">
                                                    <td style="margin:0;padding:0">
                                                        <table align="left" cellspacing="0" cellpadding="0" style="margin:0;padding:0;width:190px;max-width:100%;padding-bottom:10px;text-align:left!important">
                                                            <tbody>
                                                                <tr style="margin:0;padding:0">
                                                                    <td style="margin:0;padding:0">
                                                                        <h6 style="margin:0;padding:0;line-height:1.1;margin-bottom:5px;color:#1a1a1a;font-weight:900;font-size:14px;text-transform:initial"><?= $languageLabelsArr['LBL_DELIVERY_PREF'] ?>:</h6>
                                                                        <p style="margin:0;padding:0;margin-bottom:0px;color:#585858;font-weight:normal;font-size:12px;line-height:1.6">
                                                                            <span><?= $deliveryPrefHtml ?></span>
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                                <tr>
                                    <td><?= $returnArrData['PrescriptionImages']; ?></td>
                                </tr>
                                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                            </tbody>
                        </table>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
    </td>
    </tr>
    </tbody>
    </table>
<?php } ?>
</body>
</html>