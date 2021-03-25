<?
include_once('common.php');
include_once('generalFunctions.php');
include_once('include/config.php');


//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-payment_request.php");
        exit;
}

$generalobj->check_member_login();
$abc = 'driver';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);


$tbl_name = 'register_driver';
$script = "Payment Request";
$sql = "SELECT `vCurrencySymbol` FROM `language_master` WHERE `vCode`='" . $_SESSION['sess_lang'] . "'";
$cur_code = $obj->MySQLSelect($sql);
$curr_code = $cur_code[0]['vCurrencySymbol'];
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';

if ($_SESSION['sess_user'] == "driver") {
    $sql = "SELECT * FROM register_" . $_SESSION['sess_user'] . " WHERE iDriverId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);

    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyDriver'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
} else {
    $sql = "SELECT * FROM register_" . $_SESSION['sess_user'] . " WHERE iUserId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);

    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyPassenger'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
}
$tripcursymbol = $db_curr_ratio[0]['vSymbol'];
$tripcur = $db_curr_ratio[0]['Ratio'];
$tripcurname = $db_curr_ratio[0]['vName'];
$tripcurthholsamt = $db_curr_ratio[0]['fThresholdAmount'];


$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = '';

$paidtype = (isset($_REQUEST['paidStatus']) && $_REQUEST['paidStatus'] != '') ? $_REQUEST['paidStatus'] : $langage_lbl['LBL_MYEARNING_RECENT_RIDE'];

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
//$sql = "SELECT t.*, t.iTripId,t.tSaddress, t.tEndDate,t.tDaddress,t.iFare,t.fCommision,t.ePayment_request, t.fWalletDebit FROM trips t WHERE t.iDriverId = '" . $_SESSION['sess_iUserId'] . "'" . $ssql . " AND ((t.iActive = 'Finished') OR (t.iActive = 'Canceled' AND t.fTripGenerateFare > 0)) AND t.eSystem = 'General' AND vTripPaymentMode = 'card' ORDER BY t.iTripId DESC";
$sql = "SELECT t.*, t.iTripId,t.tSaddress, t.tEndDate,t.tDaddress,t.iFare,t.fCommision,t.ePayment_request, t.fWalletDebit FROM trips t WHERE t.iDriverId = '" . $_SESSION['sess_iUserId'] . "'" . $ssql . " AND ((t.iActive = 'Finished') OR (t.iActive = 'Canceled' AND t.fTripGenerateFare > 0)) AND t.eSystem = 'General'  ORDER BY t.iTripId DESC";
$db_dtrip = $obj->MySQLSelect($sql);
$type = "Available";


if(file_exists($logogpath."driver-view-icon.png")){
    $invoice_icon = $logogpath."driver-view-icon.png";
}else{
    $invoice_icon = "assets/img/driver-view-icon.png";
}
$hotelPanel = isHotelPanelEnable(); 
$kioskPanel = isKioskPanelEnable();
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MYEARNING_PAYMENT_TXT']; ?></title>
        <!-- Default Top Script and css -->
        <?php
        include_once("top/top_script.php");
        $rtls = "";
        if ($lang_ltr == "yes") {
            $rtls = "dir='rtl'";
        }
        ?>

        <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
        <!-- End: Default Top Script and css-->
        <style>
            .trips-table-inner table .last_row_record .last_record_row {
                width: 108px !important;
            }
        </style>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant">
                <div class="page-contant-inner">
                    <h2 class="header-page"><?= $langage_lbl['LBL_MY_EARN']; ?></h2>
                    <!-- trips page -->
                    <!-- <div class="trips-page"> -->
                    <form name="frmreview" id="frmreview" method="post" action="">
                        <input type="hidden" name="paidStatus" value="" id="paidStatus">
                        <input type="hidden" name="action" value="" id="action">
                        <input type="hidden" name="iRatingId" value="" id="iRatingId">
                    </form>

                    <div class="trips-table">
                        <div class="payment-tabs">
                            <ul>
                                <li><a href="javascript:void(0);" onClick="getReview('<?= $langage_lbl['LBL_MYEARNING_RECENT_RIDE']; ?>');" class="<?= $class1; ?>"><?= $langage_lbl['LBL_MYEARNING_RECENT_RIDE']; ?></a></li>
                                <li><a href="javascript:void(0);" onClick="getReview('<?= $langage_lbl['LBL_PAYMENT_REQUEST_PAYMENT']; ?>');" class="<?= $class2; ?>"><?= $langage_lbl['LBL_PAYMENT_REQUEST_PAYMENT']; ?></a></li>
                                <li><a href="javascript:void(0);" onClick="getReview('<?= $langage_lbl['LBL_MYEARNING_PAID_TRIPS']; ?>');" class="<?= $class3; ?>"><?= $langage_lbl['LBL_MYEARNING_PAID_TRIPS']; ?></a></li>
                            </ul>
                        </div>
                        <div class="trips-table-inner">
                            <div class="driver-trip-table">
                                <form  name="frmbooking" id="frmbooking" method="post" action="payment_request_a.php">
                                    <input type="hidden" id="type" name="type" value="<?= $type; ?>">
                                    <input type="hidden" id="action" name="action" value="send_equest">
                                    <input type="hidden"  name="eTransRequest" id="eTransRequest" value="">
                                    <input type="hidden"  name="iBookingId" id="iBookingId" value="">
                                    <input type="hidden"  name="vHolderName1" id="vHolderName1" value="">
                                    <input type="hidden"  name="vBankName1" id="vBankName1" value="">
                                    <input type="hidden"  name="iBankAccountNo1" id="iBankAccountNo1" value="">
                                    <input type="hidden"  name="BICSWIFTCode1" id="BICSWIFTCode1" value="">
                                    <input type="hidden"  name="vBankBranch1" id="vBankBranch1" value="">
                                    <?php if ($_REQUEST['success'] == 1) { ?>
                                        <div class="alert alert-success alert-dismissable">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                                            <?= $var_msg ?>
                                        </div>
                                    <? } else if ($_REQUEST['success'] == 2) { ?>
                                        <div class="alert alert-danger alert-dismissable">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                            <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                                        </div>
                                    <?php } else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 0) {
                                        ?>
                                        <div class="alert alert-danger alert-dismissable">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                                            <?= $var_msg ?>
                                        </div>
                                    <? }
                                    ?>
                                    <!--<div class="col-md-2" style="float:right;">
                                    <select name="payment_mode" class="form-control" onchange="getdatapaymentwise(this.value);">
                                        <option value="cash"><?php echo $langage_lbl['LBL_CASH_TXT']; ?></option>
                                        <option value="card"><?php echo $langage_lbl['LBL_CARD']; ?></option>
                                    </select>
                                    </div>-->
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" id="dataTables-example" <?php echo $rtls; ?>>
                                        <thead>
                                            <tr>
                                                <th><?= $langage_lbl['LBL_MYEARNING_ID']; ?></th>
                                                <th><?= $langage_lbl['LBL_MYTRIP_TRIPDATE']; ?></th>
                                                <th><?= $langage_lbl['LBL_FARE_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_Commission']; ?></th>
                                                <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                                    <th><?= $langage_lbl['LBL_HOTEL_BOOKING_CHARGE']; ?></th>
                                                <? } ?>
                                                <th><?= ucfirst(strtolower($langage_lbl['LBL_TOTAL_TXT'])) . " " . ucfirst(strtolower($langage_lbl['LBL_TAX1_TXT'])); ?></th>
                                                <th><?= ucfirst(strtolower($langage_lbl['LBL_TIP_TITLE_TXT'])); ?></th>
                                                <th><?= $langage_lbl['LBL_MYEARNING_PAYMENT_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_PAYMENT_TYPE_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?></th>
                                                <th><?= $langage_lbl['LBL_MYEARNING_REQUEST_PAYMENT']; ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?
                                            $fareTotal = $commTotal = $tipPriceTotal = $driverPayTotal = $HotelCommisionTotal = $total_tax = $cardNo = 0; //added by SP if anyone request is of the card then only this btn shown for cardno only on 31-07-2019
                                            for ($i = 0; $i < count($db_dtrip); $i++) {
                                                //echo "<pre>";print_r($db_dtrip);die;
                                                $db_dtrip[$i]['iTripId'] = base64_encode(base64_encode($db_dtrip[$i]['iTripId']));
                                                $pickup = $db_dtrip[$i]['tSaddress'];
                                                $Endup = $db_dtrip[$i]['tDaddress'];
                                                //$totalfare = $generalobj->trip_currency_payment($db_dtrip[$i]['iFare']+$db_dtrip[$i]['fWalletDebit']+$db_dtrip[$i]['fDiscount'],$db_dtrip[$i]['fRatio_'.$tripcurname]);
                                                 $totalfare = round($db_dtrip[$i]['fTripGenerateFare'] * $db_dtrip[$i]['fRatio_' . $tripcurname],2);
                                                 $site_commission = round($db_dtrip[$i]['fCommision'] * $db_dtrip[$i]['fRatio_' . $tripcurname],2);
                                                 $hotel_commision = round($db_dtrip[$i]['fHotelCommision'] * $db_dtrip[$i]['fRatio_' . $tripcurname],2);
                                                 $fTipPrice = round($db_dtrip[$i]['fTipPrice'] * $db_dtrip[$i]['fRatio_' . $tripcurname],2);
											    /*  $totalfare = $generalobj->trip_currency_payment($db_dtrip[$i]['fTripGenerateFare'], $db_dtrip[$i]['fRatio_' . $tripcurname]);  
												$site_commission = $generalobj->trip_currency_payment($db_dtrip[$i]['fCommision'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
                                                $hotel_commision = $generalobj->trip_currency_payment($db_dtrip[$i]['fHotelCommision'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
                                                $fTipPrice = $generalobj->trip_currency_payment($db_dtrip[$i]['fTipPrice'], $db_dtrip[$i]['fRatio_' . $tripcurname]); */
                                                $tax = $db_dtrip[$i]['fTax1'] + $db_dtrip[$i]['fTax2'];
                                                $totTax = round($tax * $db_dtrip[$i]['fRatio_' . $tripcurname],2);
                                                $fOutStandingAmount = round($db_dtrip[$i]['fOutStandingAmount'] * $db_dtrip[$i]['fRatio_' . $tripcurname],2);
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
                                                ?>
                                                <tr class="gradeA">
                                                    <td><?= $db_dtrip[$i]['vRideNo']; ?></td>
                                                    <td><?= $generalobj->DateTime1($dBookingDate, 'no'); ?></td>
                                                    <td align="right"><?=
                                                        $generalobj->formateNumAsPerCurrency($totalfare, $tripcurname);
                                                        $fareTotal += $totalfare;
                                                        ?></td>
                                                    <td align="right"><?=
														$generalobj->formateNumAsPerCurrency($site_commission, $tripcurname);
                                                        $commTotal += $site_commission;
                                                        ?></td>
                                                        <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                                        <td align="right"><?=
														$generalobj->formateNumAsPerCurrency($hotel_commision, $tripcurname);
                                                            $HotelCommisionTotal += $hotel_commision;
                                                            ?></td>
                                                        <? } ?>

                                                    <td align="right"><?php
                                                        if ($totTax > 0) {
														echo $generalobj->formateNumAsPerCurrency($totTax, $tripcurname);
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?></td>

                                                    <td align="right"><?php
                                                        if ($fTipPrice > 0) {
														echo $generalobj->formateNumAsPerCurrency($fTipPrice, $tripcurname); 
                                                            $tipPriceTotal += $fTipPrice;
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?></td>
                                                    <td align="right"><?=
														 $generalobj->formateNumAsPerCurrency($driver_payment, $tripcurname);
                                                        $driverPayTotal += $driver_payment;
                                                        ?></td>
                                                    <td><?= $vTripPaymentMode ?></td>
                                                    <td class="center">
                                                        <?php if (($db_dtrip[$i]['iActive'] == 'Finished' && $db_dtrip[$i]['eCancelled'] == "Yes") || ($db_dtrip[$i]['fCancellationFare'] > 0)) { ?>
                                                            <a target = "_blank" href="invoice.php?iTripId=<?php echo $db_dtrip[$i]['iTripId'] ?>"><img src="<?php echo $invoice_icon;?>"></a>
                                                            <div style="font-size: 10px;">Cancelled</div>
                                                        <?php } else { ?>
                                                            <a target = "_blank" href="invoice.php?iTripId=<?php echo $db_dtrip[$i]['iTripId'] ?>"><img src="<?php echo $invoice_icon;?>"></a>
    <?php } ?>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox-n">
                                                        <?php if ($db_dtrip[$i]['vTripPaymentMode'] != "Cash") { $cardNo++; //added by SP if anyone request is of the card then only this btn shown on 31-07-2019 ?>
                                                                <input id="payment_<?= $db_dtrip[$i]['iTripId']; ?>" name="iTripId[]" value="<?= base64_decode(base64_decode(trim($db_dtrip[$i]['iTripId']))); ?>" type="checkbox" <? if ($db_dtrip[$i]['ePayment_request'] == 'Yes') { ?> checked="checked" disabled <? } ?> >
                                                                <label for="payment_<?= $db_dtrip[$i]['iTripId']; ?>"></label></div>
                                                <?php } ?>
                                                    </td>
                                                </tr>
<? } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="last_row_record">
                                                <td></td>
                                                <td></td>
                                                <td class="last_record_row"><?= $generalobj->formateNumAsPerCurrency($fareTotal, $tripcurname); ?></td>
                                                <td class="last_record_row midddle_rw"><?=$generalobj->formateNumAsPerCurrency($commTotal, $tripcurname); ?></td>
                                                <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                                    <td class="last_record_row midddle_rw"><?= $generalobj->formateNumAsPerCurrency($HotelCommisionTotal, $tripcurname);?></td>
<? } ?>
                                                <td class="last_record_row midddle_rw"><?=$generalobj->formateNumAsPerCurrency($total_tax, $tripcurname); ?></td>
                                                <td class="last_record_row midddle_rw"><?=$generalobj->formateNumAsPerCurrency($tipPriceTotal, $tripcurname); ?></td>
                                                <td class="last_record_row"> <?= $generalobj->formateNumAsPerCurrency($driverPayTotal, $tripcurname); ?></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </form>
                            </div>
                            <?php if ($paidtype == $langage_lbl['LBL_MYEARNING_RECENT_RIDE']) { ?>
                                <?php if($cardNo!=0) { ?><div class="singlerow-login-log"><a href="javascript:void(0);" onClick="javascript:check_skills_edit(); return false;"><?= $langage_lbl['LBL_Send_transfer_Request']; ?></a></div><?php } //added by SP if anyone request is of the card then only this btn shown on 31-07-2019 ?>
                                <div class="your-requestd"><b><?= $langage_lbl['LBL_THRESHOLDAMOUNT_NOTE1']; ?></b> <?= $langage_lbl['LBL_THRESHOLDAMOUNT_NOTE2']; ?><?= '  ' .    $generalobj->formateNumAsPerCurrency($tripcurthholsamt, $tripcurname); ?></div>
                    <?php } ?>
                        </div>
                    </div>
<? //if(SITE_TYPE=="Demo"){    ?>
                    <!-- <div class="record-feature"> 
                         <span><strong>“Edit / Delete Record Feature�?</strong> has been disabled on the Demo Admin Version you are viewing now.
                         This feature will be enabled in the main product we will provide you.</span> 
                 </div>	-->
<?php //}    ?>
                    <!-- </div> -->
                    <!-- -->
                    <div style="clear:both;"></div>
                </div>
            </div>
            <!-- footer part -->
<?php include_once('footer/footer_home.php'); ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>

        <div class="col-lg-12">
<? $type = $_SESSION['sess_user']; ?>
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-content image-upload-1 popup-box1">
                    <div class="upload-content">
                        <h4><?= $langage_lbl['LBL_WITHDRAW_REQUEST']; ?></h4>
                        <form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" name="frm6">
                            <input type="hidden" id="action" name="action" value="send_equest">
                            <input type="hidden"  name="iUserId" id="iUserId" value="<?= $_SESSION['sess_iUserId']; ?>">
                            <input type="hidden"  name="eUserType" id="eUserType" value="<?= $type; ?>">

                            <div class="col-lg-13">
                                <div class="input-group input-append" >
                                    <h5><?= $langage_lbl['LBL_WALLET_ACCOUNT_HOLDER_NAME']; ?>*</h5>
                                    <input type="text" name="vHolderName" id="vHolderName" class="form-control vHolderName"  <? if ($type == 'driver') { ?>value="<?= $db_booking[0]['vBankAccountHolderName']; ?>"<? } ?>>
                                    <h5><?= $langage_lbl['LBL_WALLET_NAME_OF_BANK']; ?>*</h5>
                                    <input type="text" name="vBankName" id="vBankName" class="form-control vBankName" <? if ($type == 'driver') { ?>value="<?= $db_booking[0]['vBankName']; ?>"<? } ?>>
                                    <h5><?= $langage_lbl['LBL_WALLET_ACCOUNT_NUMBER']; ?>*</h5>
                                    <input type="text" name="iBankAccountNo" id="iBankAccountNo" class="form-control iBankAccountNo" <? if ($type == 'driver') { ?>value="<?= $db_booking[0]['vAccountNumber']; ?>"<? } ?>>
                                    <h5><?= $langage_lbl['LBL_WALLET_BIC_SWIFT_CODE']; ?>*</h5>
                                    <input type="text" name="BICSWIFTCode" id="BICSWIFTCode" class="form-control BICSWIFTCode" <? if ($type == 'driver') { ?>value="<?= $db_booking[0]['vBIC_SWIFT_Code']; ?>"<? } ?>>
                                    <h5><?= $langage_lbl['LBL_WALLET_BANK_LOCATION']; ?>*</h5>
                                    <input type="text" name="vBankBranch" id="vBankBranch" class="form-control vBankBranch" <? if ($type == 'driver') { ?>value="<?= $db_booking[0]['vBankLocation']; ?>"<? } ?>>
                                </div>
                            </div>
                            <input type="button" onClick="check_login_small();" id="withdrawal_request" class="save" name="<?= $langage_lbl['LBL_WALLET_save']; ?>" value="<?= $langage_lbl['LBL_BTN_SEND_TXT']; ?>">
                            <input type="button" class="cancel" data-dismiss="modal" name="<?= $langage_lbl['LBL_WALLET_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>" value="<?= $langage_lbl['LBL_WALLET_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>">
                        </form>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
        <script src="assets/js/jquery-ui.min.js"></script>
        <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
        <script type="text/javascript">
                                function getCheckCount(frmbooking)
                                {
                                    var x = 0;
                                    var threasold_value = 0;
                                    for (i = 0; i < frmbooking.elements.length; i++)
                                    {
                                        if (frmbooking.elements[i].checked == true && frmbooking.elements[i].disabled == false)
                                        {
                                            x++;
                                        }
                                    }
                                    return x;
                                }


                                function check_skills_edit() {
                                    y = getCheckCount(document.frmbooking);
                                    if (y > 0)
                                    {
                                        $("#eTransRequest").val('Yes');
                                        $('#myModal').modal('show');
                                        //document.frmbooking.submit();
                                    } else {
                                        alert("<?php echo addslashes($langage_lbl['LBL_SELECT_RIDE_FOR_TRANSFER_MSG']) ?>")
                                        return false;
                                    }
                                }
<?php if ($ENABLE_TIP_MODULE == "Yes") { ?>
    <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                        $(document).ready(function () {
                                            $('#dataTables-example').dataTable({
                                                fixedHeader: {
                                                    footer: true
                                                },
                                                "oLanguage": langData,
                                                "aaSorting": [],
                                                "aoColumns": [
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    {"bSortable": false},
                                                    null
                                                ]
                                            });
                                        });
    <? } else { ?>
                                        $(document).ready(function () {
                                            $('#dataTables-example').dataTable({
                                                fixedHeader: {
                                                    footer: true
                                                },
                                                "oLanguage": langData,
                                                "aaSorting": [],
                                                "aoColumns": [
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    {"bSortable": false},
                                                    null
                                                ]
                                            });
                                        });
    <? } ?>
<?php } else { ?>
    <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                        $(document).ready(function () {
                                            $('#dataTables-example').dataTable({
                                                fixedHeader: {
                                                    footer: true
                                                },
                                                "oLanguage": langData,
                                                "aaSorting": [],
                                                "aoColumns": [
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    {"bSortable": false},
                                                    null
                                                ]
                                            });
                                        });
    <? } else { ?>
                                        $(document).ready(function () {
                                            $('#dataTables-example').dataTable({
                                                fixedHeader: {
                                                    footer: true
                                                },
                                                "oLanguage": langData,
                                                "aaSorting": [],
                                                "aoColumns": [
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    null,
                                                    {"bSortable": false},
                                                    null
                                                ]
                                            });
                                        });
    <? } ?>
<?php } ?>

                                function check_login_small() {
                                    var vHolderName = document.getElementById("vHolderName").value;
                                    var vBankName = document.getElementById("vBankName").value;
                                    var iBankAccountNo = document.getElementById("iBankAccountNo").value;
                                    var BICSWIFTCode = document.getElementById("BICSWIFTCode").value;
                                    var vBankBranch = document.getElementById("vBankBranch").value;

                                    if (vHolderName == '') {
                                        alert("<?php echo addslashes($langage_lbl['LBL_ACCOUNT_HOLDER_NAME_MSG']) ?>");
                                        return false;
                                    }
                                    if (vBankName == '') {
                                        alert("<?php echo addslashes($langage_lbl['LBL_BANK_MSG']) ?>");
                                        return false;
                                    }
                                    if (iBankAccountNo == '') {
                                        alert("<?php echo addslashes($langage_lbl['LBL_ACCOUNT_NUM_MSG']) ?>");
                                        return false;
                                    }
                                    if (BICSWIFTCode == '') {
                                        alert("<?php echo addslashes($langage_lbl['LBL_BIC_SWIFT_CODE_MSG']) ?>");
                                        return false;
                                    }
                                    if (vBankBranch == '') {
                                        alert("<?php echo addslashes($langage_lbl['LBL_BANK_BRANCH_MSG']) ?>");
                                        return false;
                                    }

                                    if (vHolderName != "" && vBankName != "" && iBankAccountNo != "" && BICSWIFTCode != "" && vBankBranch != "") {
                                        $("#withdrawal_request").val('Please wait ...').attr('disabled', 'disabled');
                                        //console.log($("#frm6").serialize());
                                        $('#vHolderName1').val(vHolderName);
                                        $('#vBankName1').val(vBankName);
                                        $('#iBankAccountNo1').val(iBankAccountNo);
                                        $('#BICSWIFTCode1').val(BICSWIFTCode);
                                        $('#vBankBranch1').val(vBankBranch);

                                        document.frmbooking.submit();
                                    }
                                }

        </script>


        <script type="text/javascript">
            $(document).ready(function () {
                $("[name='dataTables-example_length']").each(function () {
                    $(this).wrap("<em class='select-wrapper'></em>");
                    $(this).after("<em class='holder'></em>");
                });
                $("[name='dataTables-example_length']").change(function () {
                    var selectedOption = $(this).find(":selected").text();
                    $(this).next(".holder").text(selectedOption);
                }).trigger('change');
            });
            function getReview(type)
            {
                window.history.pushState(null, null, window.location.pathname);
                $('#paidStatus').val(type);
                //	window.location.href = "payment_request.php?paidStatus="+type;
                document.frmreview.submit();
            }
            function getdatapaymentwise(val) {

            }
        </script>
        <!-- End: Footer Script -->
    </body>
</html>

