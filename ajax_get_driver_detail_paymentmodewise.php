<?php
include_once('common.php');
include_once('generalFunctions.php');
//include_once ('include_generalFunctions_dl.php');
include_once('include/config.php');
global $generalobj;
$paidtype = (isset($_REQUEST['paidStatus']) && $_REQUEST['paidStatus'] != '') ? $_REQUEST['paidStatus'] : $langage_lbl['LBL_MYEARNING_RECENT_RIDE'];
$paymentMode = (isset($_REQUEST['paymentMode']) && $_REQUEST['paymentMode'] != '') ? $_REQUEST['paymentMode'] : 'cash';

$class1 = $class2 = $class3 = '';
if ($paidtype == $langage_lbl['LBL_PAYMENT_REQUEST_PAYMENT']) {
    $class2 = 'active';
    $ssql = " AND t.ePayment_request = 'Yes' AND t.eDriverPaymentStatus = 'Unsettelled'";
} else if ($paidtype == $langage_lbl['LBL_MYEARNING_PAID_TRIPS']) {
    $class3 = 'active';
    //$ssql = " AND t.ePayment_request = 'Yes' AND t.eDriverPaymentStatus = 'Settelled'";

    $ssql = " AND t.eDriverPaymentStatus = 'Settelled'";
} else {
    $class1 = 'active';
    $ssql = " AND t.ePayment_request = 'No' AND t.eDriverPaymentStatus = 'Unsettelled' ";
}
$sql = "SELECT t.*, t.iTripId,t.tSaddress, t.tEndDate,t.tDaddress,t.iFare,t.fCommision,t.ePayment_request, t.fWalletDebit FROM trips t WHERE t.iDriverId = '" . $_SESSION['sess_iUserId'] . "'" . $ssql . " AND ((t.iActive = 'Finished') OR (t.iActive = 'Canceled' AND t.fTripGenerateFare > 0)) AND t.eSystem = 'General' AND t.vTripPaymentMode = '".$paymentMode."' ORDER BY t.iTripId DESC";
$db_dtrip = $obj->MySQLSelect($sql);

$fareTotal = $commTotal = $tipPriceTotal = $driverPayTotal = $HotelCommisionTotal = $total_tax = 0;

for ($i = 0; $i < count($db_dtrip); $i++) {
//echo "<pre>";print_r($db_dtrip);die;
$db_dtrip[$i]['iTripId'] = base64_encode(base64_encode($db_dtrip[$i]['iTripId']));
$pickup = $db_dtrip[$i]['tSaddress'];
$Endup = $db_dtrip[$i]['tDaddress'];
//$totalfare = $generalobj->trip_currency_payment($db_dtrip[$i]['iFare']+$db_dtrip[$i]['fWalletDebit']+$db_dtrip[$i]['fDiscount'],$db_dtrip[$i]['fRatio_'.$tripcurname]);
$totalfare = $generalobj->trip_currency_payment($db_dtrip[$i]['fTripGenerateFare'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
$site_commission = $generalobj->trip_currency_payment($db_dtrip[$i]['fCommision'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
$hotel_commision = $generalobj->trip_currency_payment($db_dtrip[$i]['fHotelCommision'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
$fTipPrice = $generalobj->trip_currency_payment($db_dtrip[$i]['fTipPrice'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
$totTax = $db_dtrip[$i]['fTax1'] + $db_dtrip[$i]['fTax2'];
$fOutStandingAmount = $db_dtrip[$i]['fOutStandingAmount'];
$total_tax += $totTax;
$driver_payment = $totalfare - $site_commission - $totTax - $fOutStandingAmount - $hotel_commision + $fTipPrice; //Added By HJ New Formula On 11-05-2019 As Per Discuss With KS Sir
//$driver_payment = $totalfare - $site_commission - $hotel_commision; //Commented By HJ On 11-05-2019 As Per Discuss With KS Sir
$name = $db_dtrip[$i]['vName'] . ' ' . $db_dtrip[$i]['vLastName'];
$vstatus = $db_dtrip[$i]['ePayment_request'];

$systemTimeZone = date_default_timezone_get();
if ($db_dtrip[$i]['tEndDate'] != "" && $db_dtrip[$i]['vTimeZone'] != "") {
    $dBookingDate = converToTz($db_dtrip[$i]['tEndDate'], $db_dtrip[$i]['vTimeZone'], $systemTimeZone);
} else {
    $dBookingDate = $db_dtrip[$i]['tEndDate'];
}

if ($db_dtrip[$i]['vTripPaymentMode'] == 'Cash') {
    $vTripPaymentMode = $langage_lbl['LBL_CASH_TXT'];
} else if ($db_dtrip[$i]['vTripPaymentMode'] == 'Card') {
    $vTripPaymentMode = $langage_lbl['LBL_CARD'];
} else if ($db_dtrip[$i]['vTripPaymentMode'] == 'Paypal') {
    $vTripPaymentMode = 'Paypal';
}
if(file_exists($logogpath."driver-view-icon.png")){
    $invoice_icon = $logogpath."driver-view-icon.png";
}else{
    $invoice_icon = "assets/img/driver-view-icon.png";
}
$hotelPanel = isHotelPanelEnable(); 
$kioskPanel = isKioskPanelEnable();
?>
<tr class="gradeA">
    <td><?= $db_dtrip[$i]['vRideNo']; ?></td>
    <td><?= $generalobj->DateTime1($dBookingDate, 'no'); ?></td>
    <td align="right"><?= $tripcursymbol; ?><?=
        $totalfare;
        $fareTotal += $totalfare;
        ?></td>
    <td align="right"><?= $tripcursymbol; ?><?=
        $site_commission;
        $commTotal += $site_commission;
        ?></td>
        <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
        <td align="right"><?= $tripcursymbol; ?><?=
            $hotel_commision;
            $HotelCommisionTotal += $hotel_commision;
            ?></td>
        <? } ?>

    <td align="right"><?php
        if ($totTax > 0) {
            echo $tripcursymbol . ' ' . $totTax;
        } else {
            echo '-';
        }
        ?></td>

    <td align="right"><?php
        if ($fTipPrice > 0) {
            echo $tripcursymbol . ' ' . $fTipPrice;
            $tipPriceTotal += $fTipPrice;
        } else {
            echo '-';
        }
        ?></td>
    <td align="right"><?= $tripcursymbol; ?><?=
        $generalobj->trip_currency_payment($driver_payment);
        $driverPayTotal += $driver_payment;
        ?></td>
    <td><?= $vTripPaymentMode ?></td>
    <td class="center">
        <?php if (($db_dtrip[$i]['iActive'] == 'Finished' && $db_dtrip[$i]['eCancelled'] == "Yes") || ($db_dtrip[$i]['fCancellationFare'] > 0)) { ?>
            <a target = "_blank" href="invoice.php?iTripId=<?php echo $db_dtrip[$i]['iTripId'] ?>"><img src="<?php echo $invoice_icon; ?>"></a>
            <div style="font-size: 10px;">Cancelled</div>
        <?php } else { ?>
            <a target = "_blank" href="invoice.php?iTripId=<?php echo $db_dtrip[$i]['iTripId'] ?>"><img src="<?php echo $invoice_icon; ?>"></a>
<?php } ?>
    </td>
    <td>
        <div class="checkbox-n">
        <?php if ($db_dtrip[$i]['vTripPaymentMode'] != "Cash") { ?>
                <input id="payment_<?= $db_dtrip[$i]['iTripId']; ?>" name="iTripId[]" value="<?= base64_decode(base64_decode(trim($db_dtrip[$i]['iTripId']))); ?>" type="checkbox" <? if ($db_dtrip[$i]['ePayment_request'] == 'Yes') { ?> checked="checked" disabled <? } ?> >
                <label for="payment_<?= $db_dtrip[$i]['iTripId']; ?>"></label></div>
<?php } ?>
    </td>
</tr>
<?php } exit; ?>
