<?php
include_once('../common.php');
include_once("../generalFunctions.php");

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
if (!$userObj->hasPermission('view-order-invoice')) {
    $userObj->redirect();
}
include_once('../send_invoice_receipt.php');
////$generalobjAdmin->check_member_login();
$iOrderId = isset($_REQUEST['iOrderId']) ? $_REQUEST['iOrderId'] : '';
$script = "All Orders";
$tbl_name = 'orders';
$db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, '', '');
if(empty($db_order_data)) {
   header("location: allorders.php?type=allorders");
}

$sql = "SELECT iTripId,vImage FROM trips WHERE iOrderId = '" . $iOrderId . "'";
$TripData = $obj->MySQLSelect($sql);
$getratings = $generalobj->getrating($iOrderId);
//echo "<pre>";print_r($db_order_data);die;
// prescription Images displayed by sneha start
$prescriptiondata = '';
$table_pres = 'prescription_images';
$val = $obj->MySQLSelect('select 1 from ' . $table_pres . ' LIMIT 1');

if ($val !== FALSE) {
    $prescriptiondata = $obj->MySQLSelect("SELECT vImage FROM " . $table_pres . " WHERE order_id = '" . $iOrderId . "'");
}
// prescription Images displayed by sneha end
//Added By HJ On 13-02-2020 For Display Paymen Type Start
$paymentType = ucwords($db_order_data['ePaymentOption']);
if(isset($db_order_data['fNetTotal']) > 0 && (strtoupper($db_order_data['ePayWallet']) == "YES" || $db_trip_data['fWalletDebit']  > 0)){
    if(strtoupper($db_order_data['ePaymentOption']) == "CARD"){
        //$paymentType = ucwords($langage_lbl_admin["LBL_CARD_CAPS"])."-".ucwords($langage_lbl_admin['LBL_WALLET_TXT']);//commented by SP bc of solving issue to be fixed 1312
        $paymentType = ucwords($langage_lbl_admin["LBL_CARD_CAPS"]);
    }else if(strtoupper($db_order_data['ePaymentOption']) == "CASH"){
        //$paymentType = ucwords($langage_lbl_admin["LBL_CASH_CAPS"])."-".ucwords($langage_lbl_admin['LBL_WALLET_TXT']);//commented by SP bc of solving issue to be fixed 1312
        $paymentType = ucwords($langage_lbl_admin["LBL_CASH_CAPS"]);
    }else {
        $paymentType = ucwords($langage_lbl_admin['LBL_WALLET_TXT']);
    }
}
//Added By HJ On 13-02-2020 For Display Paymen Type End

$takeaway = 'No';
//if(isTakeAwayEnable()) {
    if($db_order_data['eTakeaway']=='Yes') {
      //$prepareTime = $obj->MySQLSelect("select fPrepareTime from company where iCompanyId = ".$user_order_sql_data[0]['iCompanyId']);
      //$preparetimedata = $prepareTime[0]['fPrepareTime']." ".$langage_lbl['LBL_MINUTES_TXT'];
      $takeaway = 'Yes';   
   }
//}

// Added by HV for Delivery Preference
if(isDeliveryPreferenceEnable()) {
    $selectedPrefSql = "SELECT selectedPreferences FROM orders WHERE iOrderId = ".$iOrderId;
    $selectedPrefData = $obj->MySQLSelect($selectedPrefSql);

    $selectedPrefIds = "";
    if($selectedPrefData[0]['selectedPreferences'] != "")
    {
        $selectedPrefIds = $selectedPrefData[0]['selectedPreferences'];
    }

    if($selectedPrefIds != "")
    {
        $vLang = "EN";
        $ssql .= " WHERE iPreferenceId IN (".$selectedPrefIds.")";
        $deliveryPrefSql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_EXTRACT(tTitle, '$.tTitle_".$vLang."')) as tTitle, JSON_UNQUOTE(JSON_EXTRACT(tDescription, '$.tDescription_".$vLang."')) as tDescription, ePreferenceFor, eImageUpload, iDisplayOrder, eContactLess, eStatus FROM delivery_preferences ".$ssql;

        $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);    
    }   
}
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
        <? include_once('global_files.php'); ?>		
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <style>
            .location-to b span,.location-from b span, .location-username span {
                margin: 0px;
                padding: 0px;
                display: inline-block;
                width: auto;
                font-weight: 600;
                vertical-align: middle;
            }

        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>

            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner" id="page_height" style="">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>Invoice</h2>
                            <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">
                            <div style="clear:both;"></div>
                        </div>
                    </div>
                    <hr />
                    <?php if (isset($_REQUEST['success']) && $_REQUEST['success'] == 1) { ?>
                        <div class="alert alert-success paddiing-10">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Email send successfully.
                        </div>
                    <?php } ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <b>Your <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> </b> <?
                                        if ($db_order_data['DeliveryDate'] == "0000-00-00 00:00:00") {
                                            echo "Was Cancelled.";
                                        } else {
                                            echo @date('h:i A', @strtotime($db_order_data['DeliveryDate']));
                                            ?> on <?=
                                            @date('d M Y', @strtotime($db_order_data['DeliveryDate']));
                                        }
                                        ?>
                                    </div>
                                    <div class="panel-body rider-invoice-new">
                                        <div class="row">
                                            <div class="col-sm-6 rider-invoice-new-left">							<div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
                                                <span class="location-from"><i class="icon-map-marker"></i>
                                                    <b><?= @date('h:i A', @strtotime($db_order_data['OrderRequestDatenew'])); ?></b>
                                                    <b><span><?= $generalobjAdmin->clearName($db_order_data['CompanyName']); ?></span>
                                                        <? if (!empty($getratings['CompanyRate'])) { ?>
                                                            <span>(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate'] ?>)</span> 
                                                        <? } ?>
                                                    </b>

                                                    <b><p><?= $db_order_data['vRestuarantLocation']; ?></p></b>

                                                </span>
                                             <!--    <span class="location-from"><i class="icon-map-marker"></i>
                                                    <b>
                                                <?= @date('h:i A', @strtotime($db_order_data['OrderRequestDatenew'])); ?>
                                                        <p><?= $generalobjAdmin->clearName($db_order_data['CompanyName']); ?>
                                                <? if (!empty($getratings['CompanyRate'])) { ?>
                                                                        (<img src="../assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate'] ?>) 
                                                <? } ?>
                                                        </p>
                                                        <p><?= $db_order_data['vRestuarantLocation']; ?></p>
                                                    </b>
                                                </span> -->
                                                <!-- <span class="location-to"><i class="icon-map-marker"></i>
                                                    <b><?= @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?>
                                                        <p>
                                                <?= $generalobjAdmin->clearName($db_order_data['UserName']); ?> 
                                                <? if (!empty($getratings['UserRate'])) { ?>
                                                                        (<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>) 
                                                <? } ?>
                                                        </p>
                                                        <p><?= $db_order_data['DeliveryAddress']; ?></p>
                                                    </b>
                                                </span>
                                                -->

                                                <span class="location-to"><i class="icon-map-marker"></i>
                                                    <b><?= @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?></b>
                                                    <b><span>   

                                                            <?= $generalobjAdmin->clearName($db_order_data['UserName']); ?> 

                                                        </span>         

                                                        <? if (!empty($getratings['UserRate'])) { ?>                                                                
                                                            <span>(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>) <? } ?></span>

                                                    </b>
                                                    <b> <p><?= $db_order_data['DeliveryAddress']; ?></p>
                                                    </b>
                                                </span>


                                                <div class="rider-invoice-bottom">
                                                    <div class="col-sm-4">
                                                        <?php echo $langage_lbl_admin['LBL_ORDER_NO_TXT']; ?> <br />
                                                        <b>	
                                                            <?php echo $db_order_data['vOrderNo']; ?>
                                                        </b><br/>
                                                    </div>									
                                                    <div class="col-sm-4">
                                                        <?php echo $langage_lbl_admin['LBL_ORDER_STATUS_TXT']; ?><br /> 
                                                        <b><?= $db_order_data['vStatus']; ?> </b> <br/>
                                                    </div>														
                                                    <div class="col-sm-4">
                                                        <?php echo $langage_lbl_admin['LBL_PAYMENT_TYPE_TXT']; ?><br />
                                                        <b><?= $paymentType; ?></b>
                                                    </div>

                                                    <br><br><br>
                                                    <?php /* if (isset($db_order_data['DriverName']) && $db_order_data['DriverName'] != '') { ?>
                                                      <div class="col-sm-4">
                                                      <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name<br />
                                                      <b>	<?php echo $db_order_data['DriverName']; ?>
                                                      <? if (!empty($getratings['DriverRate'])) { ?>
                                                      (<img src="../assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>)
                                                      <? } ?>
                                                      </b><br/>
                                                      </div>
                                                      <?php } ?>
                                                      <?php if (isset($db_order_data['DriverVehicle']) && $db_order_data['DriverVehicle'] != '') { ?>
                                                      <div class="col-sm-4">
                                                      <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Vehicle<br />
                                                      <b><?= $db_order_data['DriverVehicle']; ?> </b> <br/>
                                                      </div>
                                                      <?php } ?>
                                                      <?php if ($db_order_data['UserName'] != '') { ?>
                                                      <div class="col-sm-4">
                                                      Username<br />
                                                      <b><?= $generalobjAdmin->clearName($db_order_data['UserName']); ?>
                                                      <? if (!empty($getratings['UserRate'])) { ?>
                                                      (<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>)
                                                      <? } ?>
                                                      </b>
                                                      </div>
                                                      <?php } */ ?>
                                                    <?php if (isset($db_order_data['DriverName']) && $db_order_data['DriverName'] != '') { ?>
                                                        <div class="col-sm-4">
                                                            Driver Name<br />
                                                            <b class="location-username"><span> <?php echo $generalobjAdmin->clearName($db_order_data['DriverName']); ?></span>
                                                                <? if (!empty($getratings['DriverRate'])) { ?>
                                                                    <span>(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>)</span> 
                                                                <? } ?>
                                                            </b><br/>
                                                        </div>  
                                                    <?php } ?>
                                                    <?php if (isset($db_order_data['DriverVehicle']) && $db_order_data['DriverVehicle'] != '') { ?> 
                                                        <div class="col-sm-4">
                                                            Driver Vehicle<br /> 
                                                            <b><?= $db_order_data['DriverVehicle']; ?> </b> <br/>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if ($db_order_data['UserName'] != '') { ?>                    
                                                        <div class="col-sm-4">
                                                            Username<br />
                                                            <b class="location-username"><span><?= $generalobjAdmin->clearName($db_order_data['UserName']); ?></span>
                                                                <? if (!empty($getratings['UserRate'])) { ?>
                                                                    <span>(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>)</span>
                                                                <? } ?>
                                                            </b>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <?php 
                                                //echo $db_order_data['vInstruction'];die;
                                                if(!empty(trim($db_order_data['vInstruction']))) { ?>
                                                <div class="rider-invoice-bottom">
                                                <!-- 
                                                    Addon to show Order Instruction as per discuss with CD BY TP
                                                -->
                                                    <div class="col-sm-4" style="margin-top: 12px;">
                                                        Instruction<br />
                                                        <b class="location-username">
                                                            <span><?= !empty($db_order_data['vInstruction']) ? $generalobjAdmin->clearName($db_order_data['vInstruction']) : '<code>No Instruction Added</code>'; ?></span>
                                                        </b>
                                                    </div>
                                                </div>
                                                <?php } ?>

                                                <!-- 
                                                    Addon to show Delivery Preference Instruction as per discuss with KS BY HV
                                                -->
                                                <?php if(isDeliveryPreferenceEnable() && $selectedPrefIds != "") { ?>
                                                    <div class="rider-invoice-bottom">
                                                        <div class="col-sm-4" style="margin-top: 12px;">
                                                            <span><?= $langage_lbl_admin['LBL_DELIVERY_PREF']; ?></span>
                                                            
                                                            <br />
                                                            <?php //if($selectedPrefIds != "") { ?>
                                                            <?php foreach ($deliveryPrefSqlData as $delivery_pref) { ?>
                                                            <b class="location-username">
                                                                <span>- <?= $delivery_pref['tTitle'] ?></span><br>
                                                            </b>
                                                            <?php if($delivery_pref['eContactLess'] == 'Yes' && $db_order_data['vImageDeliveryPref'] != "") { ?>
                                                                <span><a href="<?= $tconfig['tsite_upload_order_delivery_pref_images'].$db_order_data['vImageDeliveryPref']; ?>" class="btn btn-sm btn-info" style="margin-left: 10px; line-height: 1.1" target="_blank">View Image</a></span><br>
                                                            <?php } ?>
                                                            <?php } ?>
                                                            <?php //} else { ?>
                                                            <!--<b class="location-username">
                                                                <span><code>No Delivery Preference Instruction(s) Added</code></span>
                                                            </b>-->
                                                            <?php //} ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                
                                                <? if($takeaway=='Yes' && $db_order_data['iStatusCode']==6) { ?>
                                                    <div class="rider-invoice-bottom">
                                                    <span>
                                                    <b><?= $langage_lbl_admin['LBL_TAKE_AWAY']; ?></b>
                                                    <p><?= str_replace('#RESTAURANT_NAME#',$db_order_data['CompanyName'],$langage_lbl_admin['LBL_TAKE_AWAY_ORDER_NOTE']); ?></p>   
                                                   </span>
                                                   </div>
                                                <? } ?>
                                                
                                            </div>

                                            <div class="col-sm-6 rider-invoice-new-right">
                                                <h4 style="text-align:center;">	<?php echo $langage_lbl_admin['LBL_ORDER_DETAIL_TXT']; ?> </h4><hr/>	

                                                <div class="fare-breakdown">
                                                    <div class="fare-breakdown-inner">
                                                        <? $db_menu_item_list = $db_order_data['itemlist']; ?>
                                                        <h5><?php echo $langage_lbl_admin['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></h5>
                                                        <?php if (!empty($db_menu_item_list)) { ?>
                                                            <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                                <tbody>
                                                                    <? foreach ($db_menu_item_list as $key => $val) { ?>
                                                                        <tr>
                                                                            <td><?= $val['MenuItem']; ?> X <?php echo $val['iQty']; ?>
                                                                                <? if (trim($val['SubTitle'], "/") != '') { ?>
                                                                                    <br/><small style="font-size: 10px;">(<?= trim($val['SubTitle'], "/"); ?>)</small>
                                                                                <? } ?>
                                                                            </td>
                                                                            <td align="right"><?= $val['fTotPrice'] ?></td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                    <tr>
                                                                        <td colspan="2"><hr style="margin-bottom:0px;border-style: dotted;"/></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>

                                                        <?php } ?>
                                                        <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                            <tbody>
                                                                <?
                                                                foreach ($db_order_data['History_Arr'] as $key => $value) {


                                                                    if ($key == $langage_lbl_admin['LBL_BILL_SUB_TOTAL']) {
                                                                        ?>
                                                                        <tr>
                                                                            <td style="font-weight: bold;"><?= $key; ?></td>
                                                                            <td align="right"><?= $value; ?></td>
                                                                        </tr>
                                                                    <?php } else { ?>
                                                                        <tr>
                                                                            <td><?= $key; ?></td>
                                                                            <td align="right"><?= $value; ?></td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                                <tr>
                                                                    <td colspan="2"><hr style="margin-bottom:0px;border-style: dotted;"/></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                            <tbody>
                                                                <?
                                                                foreach ($db_order_data['History_Arr_first'] as $key => $value) {
                                                                    if ($key == $langage_lbl_admin['LBL_TOTAL_BILL_AMOUNT_TXT']) {
                                                                        ?>
                                                                        <tr>
                                                                            <td style="font-weight: bold;"><?= $key; ?></td>
                                                                            <td align="right"><?= $value; ?></td>
                                                                        </tr>
                                                                    <?php } else { ?>
                                                                        <tr>
                                                                            <td><?= $key; ?></td>
                                                                            <td align="right"><?= $value; ?></td>
                                                                        </tr>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <?php
                                                if (isset($TripData[0]['vImage']) && $TripData[0]['vImage'] != '') {
                                                    $img_path = $tconfig["tsite_upload_order_images"];
                                                    ?>
                                                    <br/><br/><br/>
                                                    <div class="invoice-right-bottom-img">
                                                        <div class="col-sm-6">
                                                            <b><a href="<?= $img_path . $TripData[0]['vImage']; ?>" target="_blank" ><img src = "<?= $img_path . $TripData[0]['vImage']; ?>" style="width:200px;" alt ="Order Images"/></a></b>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <br/>
                                                <?php if ($db_order_data['iStatusCode'] == '8') { ?>
                                                    <div class="panel panel-warning">
                                                        <div class="panel-heading">
                                                            <p><?= $langage_lbl_admin["LBL_ORDER_CANCEL_WEB_TEXT"]; ?></p>
                                                            <? if ($db_order_data['eCancelledBy'] != '') { ?>
                                                                <p>Cancelled By : <?php echo $db_order_data['eCancelledBy']; ?></p>
                                                            <? } if ($db_order_data['vCancelReason'] != '') { ?>
                                                                <p>Cancellation Reason : <?php echo $db_order_data['vCancelReason']; ?></p>
                                                            <? } ?>
                                                            <p><?= $langage_lbl_admin["LBL_CANCELLATION_CHARGE_WEB"] ?> For <?php echo $langage_lbl_admin['LBL_RIDER']; ?> : <?php echo $generalobj->trip_currency($db_order_data['fCancellationCharge']); ?>
                                                                <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                                    ( <?= $langage_lbl_admin["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?php echo $db_order_data['vOrderAdjusmentId'] ?>)
                                                                <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                                    ( <?= $langage_lbl_admin["LBL_UNPAID_WEB_TXT"] ?> )
                                                                    <?
                                                                } else if ($db_order_data['ePaymentOption'] == 'Card') {
                                                                    $ePaymentOption = $langage_lbl_admin["LBL_PAID_BY_CARD_WEB_TXT"];
                                                                    if ($db_order_data['ePayWallet'] == "Yes") {
                                                                        $ePaymentOption = $langage_lbl_admin['LBL_WALLET_TXT'];
                                                                    }
                                                                    ?>
                                                                    ( <?= $ePaymentOption; ?> )
                                                                <? } ?>
                                                            </p>
                                                            <p><?php echo $langage_lbl_admin["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> To Restaurant : <?php echo $generalobj->trip_currency($db_order_data['fRestaurantPaidAmount']); ?></p>
                                                            <p><?php echo $langage_lbl_admin["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> To <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>: <?php echo $generalobj->trip_currency($db_order_data['fDriverPaidAmount']); ?></p>
                                                        </div>
                                                    </div>
                                                <?php } else if ($db_order_data['iStatusCode'] == '7') { ?>
                                                    <div class="panel panel-warning">
                                                        <div class="panel-heading">
                                                            <p><?= $langage_lbl_admin["LBL_ORDER_REFUND_WEB_TEXT"]; ?></p>
                                                            <? if ($db_order_data['eCancelledBy'] != '') { ?>
                                                                <p>Cancelled By : <?php echo $db_order_data['eCancelledBy']; ?></p>
                                                            <? } if ($db_order_data['vCancelReason'] != '') { ?>
                                                                <p>Cancellation Reason : <?php echo $db_order_data['vCancelReason']; ?></p>
                                                            <? } ?>
                                                            <p><?= $langage_lbl_admin["LBL_CANCELLATION_CHARGE_WEB"] ?> : <?php echo $generalobj->trip_currency($db_order_data['fCancellationCharge']); ?>
                                                                <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                                    ( <?= $langage_lbl_admin["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?php echo $db_order_data['vOrderAdjusmentId'] ?>)
                                                                <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                                    ( <?= $langage_lbl_admin["LBL_UNPAID_WEB_TXT"] ?> )
                                                                    <?
                                                                } else if ($db_order_data['ePaymentOption'] == 'Card') {
                                                                    $ePaymentOption = $langage_lbl_admin["LBL_PAID_BY_CARD_WEB_TXT"];
                                                                    if ($db_order_data['ePayWallet'] == "Yes") {
                                                                        $ePaymentOption = $langage_lbl_admin['LBL_WALLET_TXT'];
                                                                    }
                                                                    ?>
                                                                    ( <?= $ePaymentOption; ?> )
                                                                <? } ?>
                                                            </p>
                                                            <p>Refunded Amount To <?php echo $langage_lbl_admin['LBL_RIDER']; ?> : <?php echo $generalobj->trip_currency($db_order_data['fRefundAmount']); ?>
                                                            <p><?php echo $langage_lbl_admin["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> To Restaurant: <?php echo $generalobj->trip_currency($db_order_data['fRestaurantPaidAmount']); ?></p>
                                                            <p><?php echo $langage_lbl_admin["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> To <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>: <?php echo $generalobj->trip_currency($db_order_data['fDriverPaidAmount']); ?></p>
                                                        </div>
                                                    </div>
                                                <?php } ?>


                                                <?php
                                                // prescription Images displayed by sneha start
                                                $img_url = $tconfig["tsite_upload_prescription_image"];
                                                if (!empty($prescriptiondata)) {
                                                    ?> 
                                                    <h4 style="text-align:center;"><?php echo "Prescription Images"; ?></h4><hr/>
                                                    <div class="invoice-right-bottom-img">
                                                        <?php foreach ($prescriptiondata as $key => $val) { ?>
                                                            <div class="col-sm-3 new-id-work">
                                                                <b class="img-thumbnail"><a href="<?= $img_url . "/" . $val['vImage'] ?>" target="_blank" ><img src = "<?= $img_url . "/" . $val['vImage'] ?>" alt ="Prescription Images"/></a></b>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                <?php }  // prescription Images displayed by sneha end   ?>

                                            </div>



                                            <div class="clear"></div>
                                            <!-- <div class="row invoice-email-but">
                                                    <span>
                                                            <a href="../send_invoice_receipt.php?action_from=mail&iTripId=<?= $db_order_data['iTripId'] ?>"><button class="btn btn-primary ">E-mail</button></a>
                                                    </span>
                                            </div> -->
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

                                function from_to() {

                                    $("#map-canvas").gmap3({
                                        getroute: {
                                            options: {
                                                origin: '<?= $db_order_data['vRestuarantLocationLat'] . "," . $db_order_data['vRestuarantLocationLong'] ?>',
                                                destination: '<?= $db_order_data['vLatitude'] . "," . $db_order_data['vLongitude'] ?>',
                                                travelMode: google.maps.DirectionsTravelMode.DRIVING
                                            },
                                            callback: function (results) {
                                                if (!results)
                                                    return;
                                                $(this).gmap3({
                                                    map: {
                                                        options: {
                                                            zoom: 13,
                                                            center: [-33.879, 151.235]
                                                        }
                                                    },
                                                    directionsrenderer: {
                                                        options: {
                                                            directions: results
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    });
                                }

                                from_to();

        </script>
    </body>
    <!-- END BODY-->
</html>
