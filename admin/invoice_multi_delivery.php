<?php
include_once('../common.php');
include_once("../generalFunctions.php");

$tbl_name = 'trips';
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//$ENABLE_TIP_MODULE = $generalobj->getConfigurations("configurations", "ENABLE_TIP_MODULE");
// $APP_DELIVERY_MODE = $generalobj->getConfigurations("configurations","APP_DELIVERY_MODE");
include_once('../send_invoice_receipt.php');
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('view-invoice')) {
    $userObj->redirect();
}
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
$script = "Trips";
/* Start original route */
$sql = "select tPlatitudes,tPlongitudes from trips_locations where iTripId = '" . $iTripId . "'";
$data_locations = $obj->MySQLSelect($sql);
$lat_array = explode(",", $data_locations[0]['tPlatitudes']);
$long_array = explode(",", $data_locations[0]['tPlongitudes']);
$total_ele = count($lat_array);
$inc = 1;
if ($total_ele > 200) {
    $inc = round($total_ele / 200);
}
// echo $inc=5;
for ($i = 0; $i < $total_ele; $i += $inc) {
    $latitudes[] = $lat_array[$i];
    $longitudes[] = $long_array[$i];
}
array_push($latitudes, $lat_array[$total_ele - 1]);
array_push($longitudes, $long_array[$total_ele - 1]);
/* End original route */

$db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId, '', '');
if(!isset($db_trip_data['iTripId'])) {
   header("location: trip.php");
}
$db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId, '', '');
if (file_exists($tconfig["tsite_upload_images_driver_path"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'])) {
    $img = $tconfig["tsite_upload_images_driver"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'];
} else {
    $img = $tconfig["tsite_url"] . "webimages/icons/help/driver.png";
}
if (file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'])) {
    $img1 = $tconfig["tsite_upload_images_passenger"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'];
} else {
    $img1 = $tconfig["tsite_url"] . "webimages/icons/help/taxi_passanger.png";
}
// echo "<pre>";print_r($db_trip_data);exit;
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Invoice</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="keywords" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <?php include_once('global_files.php'); ?>		
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <?php $APP_DELIVERY_MODE = "Multi"; ?>
            <!--PAGE CONTENT -->
            <div id="content">

                <div class="inner" id="page_height" style="">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>Invoice</h2>
                            <!-- <a href="mytrip.php">-->
                            <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">
                            <!-- </a> -->
                            <div style="clear:both;"></div>
                        </div>
                    </div>
                    <hr />
                    <?php if ($_REQUEST['success'] == 1) { ?>
                        <div class="alert alert-success paddiing-10">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Email send successfully.
                        </div>
                    <?php } ?>
                    <?php
                    $systemTimeZone = date_default_timezone_get();
                    if ($db_trip_data['fCancellationFare'] > 0 && $db_trip_data['vTimeZone'] != "") {
                        $dBookingDate = $endDate = $generalobj->converToTz($db_trip_data['tEndDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
                    } else if ($db_trip_data['tTripRequestDateOrig'] != "" && $db_trip_data['vTimeZone'] != "") {
                        $dBookingDate = $generalobj->converToTz($db_trip_data['tTripRequestDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
                        $endDate = $generalobj->converToTz($db_trip_data['tEndDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
                    } else {
                        $dBookingDate = $db_trip_data['tTripRequestDateOrig'];
                        $endDate = $db_trip_data['tEndDateOrig'];
                    }
                    ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <b>Your <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> </b> 
                                        <?php
                                        if (($db_trip_data['tTripRequestDateOrig'] == "0000-00-00 00:00:00")) {
                                            echo "Was Cancelled.";
                                        } else {
                                            echo @date('h:i A', @strtotime($dBookingDate));
                                            ?> on <?=
                                            @date('d M Y', @strtotime($dBookingDate));
                                        }
                                        ?>
                                    </div>
                                    <div class="panel-body rider-invoice-new">
                                        <div class="row">

                                            <div class="col-sm-6 rider-invoice-new-left">
                                                <?php if ($db_trip_data['eType'] != 'UberX' && ($APP_DELIVERY_MODE != "Multi" || $db_trip_data['eType'] == "Ride")) { ?>
                                                    <div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
<?php } ?>
                                                <span class="location-from"><i class="icon-map-marker"></i>
                                                    <b><?= @date('h:i A', @strtotime($dBookingDate)); ?><p><?= $db_trip_data['tSaddress']; ?></p></b></span>
<?php if ($db_trip_data['eType'] != 'UberX' && ($APP_DELIVERY_MODE != "Multi" || $db_trip_data['eType'] == "Ride")) { ?> 

                                                    <span class="location-to"><i class="icon-map-marker"></i> <b><?= @date('h:i A', @strtotime($endDate)); ?><p><?= $db_trip_data['tDaddress']; ?></p></b></span>
                                                <?php } ?> 
                                                <?php
                                                if ($db_trip_data['eType'] == 'UberX') {
                                                    $class_name = 'col-sm-6';
                                                    $style = "style='text-align:center;width:100%;'";
                                                } else {
                                                    $class_name = 'col-sm-4';
                                                    $style = '';
                                                }
                                                ?>
                                                <div class="rider-invoice-bottom">
                                                    <div class="<?php echo $class_name; ?>" <?= $style; ?> >
                                                        <?php if ($db_trip_data['eType'] == 'UberX') { ?> Service Type
                                                        <?php
                                                        } else {
                                                            echo $langage_lbl_admin['LBL_CAR_TXT_ADMIN'];
                                                        }
                                                        ?> <br /> 
                                                        <b>	
                                                            <?php
                                                            if (!empty($db_trip_data['vVehicleCategory'])) {
                                                                echo $db_trip_data['vVehicleCategory'] . "-" . $db_trip_data['vVehicleType'];
                                                            } else {
                                                                echo $db_trip_data['carTypeName'];
                                                            }
                                                            ?>
                                                        </b><br/>
                                                    </div>

<?php if ($db_trip_data['eType'] != 'UberX') { ?> 

                                                        <div class="<?php echo $class_name; ?>">
                                                            Distance<br /> 
                                                            <b><?= $db_trip_data['fDistance'] . $db_trip_data['DisplayDistanceTxt']; ?></b> <br/>
                                                        </div>					
                                                        <div class="<?php echo $class_name; ?>">
                                                        <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?>  time<br />
                                                            <b><? echo $db_trip_data['TripTimeInMinutes']; ?></b>
                                                        </div>
                                                <?php } ?> 
                                                </div>

<?php if ((!empty($db_trip_data['vSignImage'])) && $APP_DELIVERY_MODE == 'Multi') { ?>
                                                    <div class="rider-invoice-bottom">
                                                        <div class="col-sm-6">
                                                            <b><?php echo $langage_lbl_admin['LBL_SENDER_SIGN']; ?></b>
                                                        </div>
                                                        <?php
                                                        if (file_exists($tconfig["tsite_upload_trip_signature_images_path"] . '/' . $db_trip_data['vSignImage'])) {
                                                            $img123 = $tconfig["tsite_upload_trip_signature_images"] . '/' . $db_trip_data['vSignImage'];
                                                        }
                                                        ?>
                                                        <div class="col-sm-6">
                                                            <img src="<?php echo $img123; ?>" align="left" style="width: 100px;">
                                                        </div>
                                                    </div>
<?php } ?>

<? if ($APP_DELIVERY_MODE != 'Multi') { ?>
                                                    <div class="rider-invoice-bottom">
                                                        <div class="col-sm-6">
                                                            <div class="left col-sm-3"> 
                                                                <img src="<?php echo $img; ?>" style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7" align="left" height="45" width="45" class="CToWUd">
                                                            </div>
                                                            <div class="right col-sm-9" style="word-wrap: break-word;">
                                                                <div><b><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></b></div>
                                                                <!--<div><?php echo $generalobjAdmin->clearName($db_trip_data['DriverDetails']['vName']) . "&nbsp;" . $generalobjAdmin->clearName($db_trip_data['DriverDetails']['vLastName']); ?></div>-->
                                                                <div><?php echo $generalobjAdmin->clearName($db_trip_data['DriverDetails']['vName'] . " " . $db_trip_data['DriverDetails']['vLastName']); ?></div>
                                                                <div><?php echo $generalobjAdmin->clearEmail($db_trip_data['DriverDetails']['vEmail']); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="left col-sm-3"> 
                                                                <img src="<?php echo $img1; ?>" style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7" align="left" height="45" width="45" class="CToWUd">
                                                            </div>
                                                            <div class="right col-sm-9" style="word-wrap: break-word;">
                                                                <div><b><?php echo $langage_lbl_admin['LBL_RIDER']; ?></b></div>
                                                                <!--<div><?php echo $generalobjAdmin->clearName($db_trip_data['PassengerDetails']['vName']) . "&nbsp;" . $generalobjAdmin->clearName($db_trip_data['PassengerDetails']['vLastName']); ?></div>-->
                                                                <div><?php echo $generalobjAdmin->clearName($db_trip_data['PassengerDetails']['vName'] . " " . $db_trip_data['PassengerDetails']['vLastName']); ?></div>
                                                                <div><?php echo $generalobjAdmin->clearEmail($db_trip_data['PassengerDetails']['vEmail']); ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
<? } ?>
                                            </div>

                                            <div class="col-sm-6 rider-invoice-new-right">
                                                <h4 style="text-align:center;">	<?php echo $langage_lbl_admin['LBL_FARE_BREAKDOWN_RIDE_NO_TXT']; ?> :<?= $db_trip_data['vRideNo']; ?></h4><hr/>
                                                <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                    <tbody>
                                                        <?
                                                        foreach ($db_trip_data['HistoryFareDetailsNewArr'] as $key => $value) {
                                                            foreach ($value as $k => $val) {
                                                                if ($k == $langage_lbl_admin['LBL_EARNED_AMOUNT']) {
                                                                    continue;
                                                                } else if ($k == $langage_lbl_admin['LBL_SUBTOTAL_TXT']) {
                                                                    continue;
                                                                } else if ($k == "eDisplaySeperator") {
                                                                    echo '<tr><td colspan="2"><div style="border-top:1px dashed #d1d1d1"></div></td></tr>';
                                                                } else {
                                                                    ?>
                                                                    <tr>
                                                                        <td><?= $k; ?></td>
                                                                        <td align="right"><?php echo $val; ?></td>
                                                                    </tr>
                                                                    <?
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td colspan="2"><hr style="margin-bottom:0px"/></td>
                                                        </tr>
                                                        <tr>
                                                            <td><b><?php echo $langage_lbl_admin['LBL_Total_Fare_TXT']; ?> (Via <?
                                                                    if ($db_trip_data['ePayWallet'] == 'Yes') {
                                                                        echo $langage_lbl_admin['LBL_WALLET_TXT'];
                                                                    } else {
                                                                        echo $db_trip_data['vTripPaymentMode'];
                                                                    }
                                                                    ?>)</b></td>
                                                            <td align="right">
                                                                <b>
                                                        <?= $db_trip_data['FareSubTotal']; ?>
                                                                </b>
                                                            </td>
                                                        </tr>
                                                        <?
                                                        if ($APP_DELIVERY_MODE == 'Multi') {

                                                            foreach ($db_reci_data as $key1 => $value1) {
                                                                foreach ($value1 as $key2 => $value2) {
                                                                    //if(!empty($value2['ePaymentBy'])){ //Commented By HJ On 04-06-2019 As Per Changed By PM
                                                                    if ($value2['ePaymentBy'] == "Sender" || $value2['ePaymentBy'] == "Individual" || ($value2['ePaymentBy'] == "Receiver" && $value2['ePaymentByReceiver'] == "Yes")) { //Added By HJ On 04-06-2019 As Per Changed By PM
                                                                        ?>
                                                                        <tr>
                                                                            <td><b>Payment By</b></td>
                                                                            <?
                                                                            if ($value2['ePaymentBy'] == "Sender") {
                                                                                ?><td align="right"><?= $value2['ePaymentBy']; ?></td><?
                                                                                } else if ($value2['ePaymentBy'] == "Receiver") {
                                                                                    ?><td align="right"><?= $value2['PaymentPerson']; ?></td><?
                                                                                } else if ($value2['ePaymentBy'] == "Individual") {
                                                                                    ?><td align="right"><?= $langage_lbl_admin['LBL_EACH_RECIPIENT']; ?></td><?
                                                                        }
                                                                        ?>
                                                                        </tr>
                                                                        <?
                                                                        break 2;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        if (isset($db_trip_data['fCommision']) && $db_trip_data['fCommision'] > 0 && $comminsionDisp == 0) {
                                                            ?>
                                                        <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="2px" width="100%">
                                                            <tr>
                                                                <td><b><?php echo $langage_lbl_admin['LBL_Commision']; ?></b></td>
                                                                <td align="right"><b><?= $db_trip_data['CurrencySymbol'] . $db_trip_data['fCommision']; ?></b></td>
                                                            </tr>
                                                        </table><br>
<?php }
?>
                                                    </tbody>
                                                </table>
                                                <br><br><br>

<?php if (($db_trip_data['iActive'] == 'Finished' && $db_trip_data['eCancelled'] == "Yes") || ($db_trip_data['fCancellationFare'] > 0) || ($db_trip_data['iActive'] == 'Canceled' && $db_trip_data['fWalletDebit'] > 0)) {
    ?>
                                                    <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td>
                                                                <b>
                                                                    <?php
                                                                    if ($db_trip_data['eCancelledBy'] == 'Driver') {
                                                                        echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_DRIVER_ADMIN'];
                                                                        if (!empty($db_trip_data['vCancelReason'])) {
                                                                            echo 'Reason: ' . $db_trip_data['vCancelReason'];
                                                                        }
                                                                    } else if ($db_trip_data['eCancelledBy'] == 'Passenger') {
                                                                        echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_PASSANGER_ADMIN'];
                                                                        if (!empty($db_trip_data['vCancelReason'])) {
                                                                            echo 'Reason: ' . $db_trip_data['vCancelReason'];
                                                                        }
                                                                    } else {
                                                                        echo $langage_lbl_admin['LBL_CANCELED_TRIP_ADMIN_TXT'];
                                                                    }
                                                                    ?>
                                                                </b></td>
                                                        </tr>
                                                    </table><br>
                                                <? } ?>

                                                <?php
                                                if ($db_trip_data['fTipPrice'] != "" && $db_trip_data['fTipPrice'] != "0" && $db_trip_data['fTipPrice'] != "0.00") {
                                                    if ($ENABLE_TIP_MODULE == "Yes") {
                                                        ?>
                                                        <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="2px" width="100%">
                                                            <tr>
                                                                <td><b>Tip given to <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></b></td>
                                                                <td align="right"><b><?= $db_trip_data['fTipPrice']; ?></b></td>
                                                            </tr>
                                                        </table><br>
                                                    <?
                                                    }
                                                }
                                                ?>

<?php if ($db_trip_data['eType'] == 'Deliver' && $APP_DELIVERY_MODE != 'Multi') { ?>

                                                    <h4 style="text-align:center;"><?php echo $langage_lbl_admin['LBL_DELIVERY_DETAILS_TXT_ADMIN']; ?></h4><hr/>

                                                    <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                        <tr>
                                                            <td><?php echo $langage_lbl_admin['LBL_RECEIVER_NAME']; ?></td>
                                                            <td><?= $db_trip_data['vReceiverName']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php echo $langage_lbl_admin['LBL_RECEIVER_MOBILE']; ?></td>
                                                            <td><?= $db_trip_data['vReceiverMobile']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php echo $langage_lbl_admin['LBL_PICK_UP_INS']; ?></td>
                                                            <td><?= $db_trip_data['tPickUpIns']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php echo $langage_lbl_admin['LBL_DELIVERY_INS']; ?></td>
                                                            <td><?= $db_trip_data['tDeliveryIns']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php echo $langage_lbl_admin['LBL_PACKAGE_DETAILS']; ?></td>
                                                            <td><?= $db_trip_data['tPackageDetails']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><?php echo $langage_lbl_admin['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?></td>
                                                            <td><?= $db_trip_data['vDeliveryConfirmCode']; ?></td>
                                                        </tr>
                                                    </table>

                                                <?php } ?>


<?php
if ($db_trip_data['eType'] == 'UberX' && ($db_trip_data['vBeforeImage'] != '' || $db_trip_data['vAfterImage'] != '')) {
    $img_path = $tconfig["tsite_upload_trip_images"];
    ?> 
                                                    <h4 style="text-align:center;"><?php echo $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT']; ?></h4><hr/>
                                                    <div class="invoice-right-bottom-img">
                                                        <?php if ($db_trip_data['vBeforeImage'] != '') { ?>
                                                            <div class="col-sm-6">											
                                                                <h3><?php echo $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN']; ?></h3>
                                                                <b><a href="<?= $db_trip_data['vBeforeImage']; ?>" target="_blank" ><img src = "<?= $db_trip_data['vBeforeImage'] ?>" style="width:200px;" alt ="Before Images"/></a></b>
                                                            </div>
    <?php } ?>
                                                        <?php if ($db_trip_data['vAfterImage'] != '') { ?>
                                                            <div class="col-sm-6">
                                                                <h3><?php echo $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN']; ?></h3>
                                                                <b><a href="<?= $db_trip_data['vAfterImage']; ?>" target="_blank" ><img src = "<?= $db_trip_data['vAfterImage']; ?>" style="width:200px;" alt ="After Images"/></a></b>
                                                            </div>
    <?php } ?>
                                                    </div>
                                            <?php } ?>
                                            </div>

                                            <div class="clear"></div>

                                                <? if ($APP_DELIVERY_MODE == "Multi") { ?>
                                                <div class="invoice-table">
                                                    <?php
                                                    if (!empty($db_reci_data)) {
                                                        foreach ($db_reci_data as $key1 => $value1) {
                                                            $no = $key1 + 1;
                                                            $class = (!empty($dtls['vSignImage'])) ? 'sign-img' : '';
                                                            ?>
                                                            <div class="col-sm-6 <?php echo $class; ?>">
                                                                <h4><?php echo $langage_lbl_admin['LBL_RECIPIENT_LIST_TXT'] . '&nbsp;' . $no; ?></h4><hr/>
                                                                <table style="width:100%" cellpadding="5" cellspacing="0" border="0">

                                                                    <? foreach ($value1 as $key2 => $value2) { ?>
                                                                        <tr>
                                                                            <td class="label_left"><?php echo $value2['vFieldName']; ?></td>
                                                                            <td class="detail_right"><?= $value2['vValue']; ?></td>
                                                                        </tr>
                                                                            <? } ?>
                                                                            <? if (!empty($value2['ePaymentBy']) && $value2['ePaymentBy'] == "Individual") { ?>
                                                                        <tr>
                                                                            <td class="label_left"><?= $langage_lbl_admin['LBL_AMOUNT_PAID_TXT']; ?></td>
                                                                            <td class="detail_right">
                                                                        <?= $value2['PaymentAmount']; ?>
                                                                            </td>
                                                                        </tr>
            <? } ?>
            <?php if (!empty($value2['Receipent_Signature'])) { ?>
                                                                        <tr>
                                                                            <td class="label_left"><?= $langage_lbl_admin['LBL_RECEIVER_SIGN']; ?></td>
                                                                            <td class="detail_right">
                                                                                <img width="100px" src="<?php echo $value2['Receipent_Signature']; ?>" align="left" >
                                                                            </td>
                                                                        </tr>
                                                                            <?php } else if ($value2['vDeliveryConfirmCode'] != "") { ?>
                                                                        <tr>
                                                                            <td class="label_left"><?= $langage_lbl_admin['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?></td>
                                                                            <td class="detail_right">
                <?= $value2['vDeliveryConfirmCode'] ?>
                                                                            </td>
                                                                        </tr>
                                                            <? } ?>
                                                                </table>
                                                            </div>
            <?php
        }
    }
    ?>
                                                </div>

<? } ?>

                                            <div class="row invoice-email-but">
                                                <span>
                                                    <a href="../send_invoice_receipt_multi.php?action_from=mail&iTripId=<?= $db_trip_data['iTripId'] ?>&multidelivery=1"><button class="btn btn-primary ">E-mail</button></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>

        <!--END MAIN WRAPPER -->

<? include_once('footer.php'); ?>
        <script src="../assets/js/gmap3.js"></script>
        <script>
                                h = window.innerHeight;
                                $("#page_height").css('min-height', Math.round(h - 99) + 'px');

                                // var waypts = [];
                                var arr1 = [];
                                var lats = [];
                                var longs = [];
                                var markers = [];
                                var map;
                                function initialize() {
                                    var thePoint = new google.maps.LatLng('20.1849963', '64.4125062');
                                    var mapOptions = {
                                        zoom: 4,
                                        center: thePoint
                                    };
                                    map = new google.maps.Map(document.getElementById('map-canvas'),
                                            mapOptions);

                                    from_to_polyline();
                                }


                                var tPlatitudes = '<?= json_encode($latitudes) ?>';
                                lats = JSON.parse(tPlatitudes);
                                var tPlongitudes = '<?= json_encode($longitudes) ?>';
                                longs = JSON.parse(tPlongitudes);

                                var pts = [];
                                var bounds = new google.maps.LatLngBounds();
                                for (var i = 0; i < lats.length; i++) {
                                    var latlongs = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
                                    pts.push(latlongs);
                                    var point = latlongs;
                                    bounds.extend(point);

                                    if (i == 0) {
                                        var start = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
                                    } else if (i == lats.length - 1) {
                                        var end = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
                                    }
                                }

                                var directionsService = new google.maps.DirectionsService();
                                var directionsOptions = {// For Polyline Route line options on map
                                    polylineOptions: {
                                        path: pts,
                                        strokeColor: '#f35e2f',
                                        strokeOpacity: 1.0,
                                        strokeWeight: 4
                                    }
                                };
                                var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);

                                function from_to() {
                                    var request = {
                                        origin: start, // From locations latlongs
                                        destination: end, // To locations latlongs
                                        travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
                                    };
                                    directionsService.route(request, function (response, status) {
                                        directionsDisplay.setMap(map);
                                        directionsDisplay.setDirections(response);
                                    });
                                }

                                $(document).ready(function () {
                                    google.maps.event.addDomListener(window, 'load', initialize);
                                })

                                function from_to_polyline() {
                                    DeleteMarkers('from_loc');
                                    DeleteMarkers('to_loc');
                                    setMarker(start, 'from_loc');
                                    setMarker(end, 'to_loc');

                                    var flightPath = '';
                                    var flightPath = new google.maps.Polyline({
                                        path: pts,
                                        geodesic: true,
                                        strokeColor: '#f35e2f',
                                        strokeOpacity: 1.0,
                                        strokeWeight: 4
                                    });
                                    map.fitBounds(bounds);
                                    flightPath.setMap(map);
                                }

                                function setMarker(postitions, valIcon) {
                                    var newIcon;
                                    if (valIcon == 'from_loc') {
                                        newIcon = '../webimages/upload/mapmarker/PinFrom.png';
                                    } else if (valIcon == 'to_loc') {
                                        newIcon = '../webimages/upload/mapmarker/PinTo.png';
                                    } else {
                                        newIcon = '../webimages/upload/mapmarker/PinTo.png';
                                    }
                                    marker = new google.maps.Marker({
                                        map: map,
                                        animation: google.maps.Animation.DROP,
                                        position: postitions,
                                        icon: newIcon
                                    });
                                    marker.id = valIcon;
                                    markers.push(marker);
                                }

                                function DeleteMarkers(newId) {
                                    for (var i = 0; i < markers.length; i++) {
                                        if (newId != '') {
                                            if (markers[i].id == newId) {
                                                markers[i].setMap(null);
                                            }
                                        } else {
                                            markers[i].setMap(null);
                                            markers = [];
                                        }
                                    }
                                }

        </script>
    </body>
    <!-- END BODY-->
</html>
