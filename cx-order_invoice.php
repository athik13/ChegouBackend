<?
include_once('common.php');
include_once('generalFunctions.php');

$tbl_name = 'orders';
$script = "Order";
$generalobj->check_member_login();

if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "") {
    $HTTP_REFERER = $_SERVER['HTTP_REFERER'];
    $_SESSION['HTTP_REFERER'] = $HTTP_REFERER;
}
$_REQUEST['iOrderId'] = base64_decode(base64_decode(trim($_REQUEST['iOrderId'])));
$iOrderId = isset($_REQUEST['iOrderId']) ? $_REQUEST['iOrderId'] : '';

if ($_SESSION['sess_user'] == 'driver') {
    $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $_SESSION['sess_iUserId'], 'Driver');
} else if ($_SESSION['sess_user'] == 'rider') {
    $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $_SESSION['sess_iUserId'], 'Passenger');
} else {
    $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $_SESSION['sess_iUserId'], 'Company');
}

/* echo "<pre>";
  print_r($db_order_data);
  exit();
 */
//print_R($db_order_data);die;
$getratings = $generalobj->getrating($iOrderId);
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>  
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>
            <section class="profile-section">
    <div class="profile-section-inner">
        <div class="profile-caption _MB0_">
            <div class="page-heading">
                <h1><?= $langage_lbl['LBL_ORDERS_TXT']; ?> </h1>
            </div>
            <ul class="overview-detail">
                <li>
                    <div class="overview-data">
                        <strong><?= $langage_lbl['LBL_ORDER_NO_TXT']; ?></strong>
                        <span><?= $db_order_data['vOrderNo']; ?></span>
                    </div>
                </li>
                <li>
                    <div class="overview-data">
                        <strong><?= $langage_lbl['LBL_ORDER_STATUS_TXT']; ?></strong>
                        <span><?= $db_order_data['vStatus']; ?></span>
                    </div>
                </li>
				<li>
                    <div class="overview-data">
                        <strong><?= $langage_lbl['LBL_ORDER_DATE_TXT']; ?></strong>
                        <span><?= @date('d M Y', @strtotime($db_order_data['DeliveryDate'])); ?></span>
                    </div>
                </li>
				<li>
                    <div class="overview-data">
                        <strong><?= $langage_lbl['LBL_PASSENGER_NAME_TEXT_DL']; ?></strong>
                        <span><?= $db_order_data['UserName'] ?>
                        <? if (!empty($getratings['UserRate'])) { ?>
                            (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>) 
                        <? } ?>
                        </span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>
<section class="profile-earning">
    <div class="profile-earning-inner"> 
        <div class="left-block">
            <div class="inv-block-inner">
                <?php //if ($UserType == 'Passenger') { ?>
                    <input type="hidden" name="driverid" id="driverid" value="<?= $driverId; ?>">
                    <div class="gmap-div gmap-div1" id="invoice_map"><div id="map-canvas" class="gmap3 google-map" style="height: 302px"></div></div>
                
                <!--<strong class="sub-block-title"><?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></strong>-->
                <?php //} ?>
                <div class="invoice-data-holder track-order">
                    <div>
                        <?php if ($UserType != 'Passenger') { ?>
                        <strong class="sub-block-title"><?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></strong>
                        <?php } ?>
                        <!--<div class="profile-image">
                            <img src="images/user_place.svg" alt="">
                        </div>-->
                        <!--<div class="profile-image-blank"></div>-->
                        <div class="inv-data profile-image-blank">
                            <?php if ($_SESSION['sess_user'] == 'driver') { ?>
                            <div class="driver-info new-driver" style="height: auto;">
                                        <h3 style=" margin: 30px 0;"> <?= $langage_lbl['LBL_DELIVERY_EARNING_FRONT']; ?></h3>
                                    </div>
                                    <div class="fare-breakdown">
                                        <div class="fare-breakdown-inner">
                                            <ul>
                                                <? foreach ($db_order_data['History_Arr'] as $key => $value) { ?>
                                                    <li>
                                                        <strong style="font-weight: bold;background: # !important;"><?= $key; ?></strong>
                                                        <b><?= $value; ?></b>
                                                    </li>
                                                <?php }
                                                ?>
                                            </ul>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                            <?php } else if ($_SESSION['sess_user'] == 'rider') { ?>
                            <strong><?= $langage_lbl['LBL_YOUR_ORDER']; ?></strong>
                            <ul>
                                <li><?= $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></li>
                                <?php
                                $db_menu_item_list = $db_order_data['itemlist']; 
                                if (!empty($db_menu_item_list)) { ?>
                                <li class="item-list">
                                    <? foreach ($db_menu_item_list as $key => $val) { ?>
                                    <div class="itme-row">
                                        <span><?= $val['MenuItem']; ?> X <?= $val['iQty']; ?><? if ($val['SubTitle'] != '') { ?><strong style="font-size: 10px;">(<?= $val['SubTitle']; ?>)</strong><? } ?></span>
                                        <b><?= $val['fTotPrice'] ?></b>		
                                    </div>
                                    <?php } ?>
                                </li>
                                <?php } ?>
                                <? foreach ($db_order_data['History_Arr'] as $key => $value) { ?>
                                <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                <?php } ?>
                                <? foreach ($db_order_data['History_Arr_first'] as $key => $value) { ?>
                                <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                <? } ?>
                            </ul>
                            <?php } else { ?>
                            <strong><?= $langage_lbl['LBL_YOUR_ORDER']; ?></strong>
                            <ul>
                                <li><?= $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></li>
                                <?php
                                $db_menu_item_list = $db_order_data['itemlist']; 
                                if (!empty($db_menu_item_list)) { ?>
                                <li class="item-list">
                                    <? foreach ($db_menu_item_list as $key => $val) { ?>
                                    <div class="itme-row">
                                        <span><?= $val['MenuItem']; ?> X <?= $val['iQty']; ?><? if ($val['SubTitle'] != '') { ?><strong style="font-size: 10px;">(<?= $val['SubTitle']; ?>)</strong><? } ?></span>
                                        <b><?= $val['fTotPrice'] ?></b>		
                                    </div>
                                    <?php } ?>
                                </li>
                                <?php } ?>
                                <? foreach ($db_order_data['History_Arr'] as $key => $value) { ?>
                                <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                <?php } ?>
                                <? foreach ($db_order_data['History_Arr_first'] as $key => $value) { ?>
                                <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                <? } ?>
                                <? foreach ($db_order_data['History_Arr_second'] as $key => $value) { ?>
                                <li><span><?= $key; ?></span><b><?= $value; ?></b></li>
                                <? } ?>
                            </ul>
                            <?php } ?>
                            
                            <?php if ($db_order_data['iStatusCode'] == '8') { ?>
                                        <div class="panel panel-warning" style="margin-bottom: 0">
                                            <div class="panel-heading">
                                                <div><?= $langage_lbl["LBL_ORDER_CANCEL_WEB_TEXT"]; ?></div>
                                                <?php
                                                if ($_SESSION['sess_user'] == 'company') {
                                                    if ($db_order_data['fRestaurantPaidAmount'] > 0) {
                                                        ?>
                                                        <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>  : <?= $db_order_data['RestaurantPaidAmount']; ?></div>
                                                    <?php } else { ?>
                                                        <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                        <?php
                                                    }
                                                }
                                                ?>

                                                <?php
                                                if ($_SESSION['sess_user'] == 'driver') {
                                                    if ($db_order_data['fDriverPaidAmount'] > 0) {
                                                        ?>
                                                        <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>: <?= $db_order_data['DriverPaidAmount']; ?></div>
                                                    <?php } else { ?>
                                                        <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                        <?php
                                                    }
                                                }
                                                ?>

                                                <?php if ($_SESSION['sess_user'] == 'rider') { ?>
                                                    <div><?= $langage_lbl["LBL_CANCELLATION_CHARGE_WEB"] ?> : <?= $db_order_data['CancellationCharge']; ?>
                                                        <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?= $db_order_data['vOrderAdjusmentId'] ?>)
                                                        <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                            ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"] ?> )
                                                        <? } else if ($db_order_data['ePaymentOption'] == 'Card') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"] ?> )
                                                        <? } ?>
                                                    </div>
                                                <?php } ?> 
                                            </div>
                                        </div>
                                    <?php } else if ($db_order_data['iStatusCode'] == '7') { ?>
                                        <div class="panel panel-warning" style="margin-bottom: 0">
                                            <div class="panel-heading">
                                                <div><?= $langage_lbl["LBL_ORDER_REFUND_WEB_TEXT"]; ?></div>
                                                <?php
                                                if ($_SESSION['sess_user'] == 'company') {
                                                    if ($db_order_data['fRestaurantPaidAmount'] > 0) {
                                                        ?>
                                                        <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> : <?= $db_order_data['RestaurantPaidAmount']; ?></div>
                                                    <?php } else { ?>
                                                        <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                        <?php
                                                    }
                                                }
                                                ?>

                                                <?php
                                                if ($_SESSION['sess_user'] == 'driver') {
                                                    if ($db_order_data['fDriverPaidAmount'] > 0) {
                                                        ?>
                                                        <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>: <?= $db_order_data['DriverPaidAmount']; ?></div>
                                                    <?php } else { ?>
                                                        <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                        <?php
                                                    }
                                                }
                                                ?>

                                                <?php if ($_SESSION['sess_user'] == 'rider') { ?>
                                                    <div> <?= $langage_lbl["LBL_CANCELLATION_CHARGE_WEB"] ?> : <?= $db_order_data['CancellationCharge']; ?>
                                                        <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?= $db_order_data['vOrderAdjusmentId'] ?>)
                                                        <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                            ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"] ?> )
                                                        <? } else if ($db_order_data['ePaymentOption'] == 'Card') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"] ?> )
                                                        <? } ?>
                                                    </div>
                                                    <div> <?= $langage_lbl["LBL_REFUND_WEB_TXT"] ?> : <?= $db_order_data['RefundAmount']; ?></div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                        </div>
                    </div>
                    <div class="inv-destination-data test_head">
                        <ul>
                            <li>
                                <i class="fa fa-map-marker"></i>
                                <strong><?= $langage_lbl["LBL_RESTAURANT_ADDRESS"] ?>:</strong>
                                <p><?= $db_order_data['vRestuarantLocation']; ?></p>
                            </li>
							<li>
                                <i class="fa fa-clock-o"></i>
                                <strong><?= $langage_lbl["LBL_ORDER_PICKUP_TIME"] ?>:</strong>
                                <p><?= @date('h:i A', @strtotime($db_order_data['OrderRequestDatenew'])); ?></p>
                            </li>  
							<li>
                                <i class="fa fa-map-marker"></i>
                                <strong><?= $langage_lbl["LBL_USER_ADDRESS"] ?>:</strong>
                                <p><?= $db_order_data['DeliveryAddress']; ?></p>
                            </li>
                            <li>
                                <i class="fa fa-clock-o"></i>
                                <strong><?= $langage_lbl["LBL_DELIVERY_TIME"] ?>:</strong>
                                <p><?= @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?></p>
                            </li>                            
                        </ul>
                    </div>
                </div>


            </div>            
        </div>

        <div class="left-right">
            <div class="track-order-data">
                <div>
                    <div class="rest-name-holder">
                        <i><img src="assets/img/apptype/<?php echo $template;?>/restaurant.svg" alt=""></i>
                        <strong><?= $langage_lbl["LBL_RESTAURANT_NAME_TXT"] ?>:</strong>
                        <p><?= $db_order_data['CompanyName']; ?>
                        <? if (!empty($getratings['CompanyRate'])) { ?>
                            (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate'] ?>) 
                        <? } ?>
                        </p>
                    </div>
                    <? if ($db_order_data['DriverName'] != '') { ?>
                    <div class="rest-name-holder">
                        <i><img src="assets/img/apptype/<?php echo $template;?>/scooter_driver.svg" alt=""></i>
                        <strong><?= $langage_lbl['LBL_ORDER_DELIVERED_BY_DRIVER']; ?> : </strong>
                        <p><?= $db_order_data['DriverName']; ?>
                        <? if (!empty($getratings['DriverRate'])) { ?>
                            (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>) 
                        <? } ?>
                        </p>
                    </div>
                     <? } ?>
                    <?php if ($UserType == 'Passenger') { ?>
                    <ul id="orderstatusall">
                    <?php
                    $orderStatus_json = getOrderDetailsforGettingliveStatus($iOrderId, $_SESSION['sess_iUserId'], $UserType);
                    $orderStatus = json_decode($orderStatus_json);
                    
                    $img_array = array("time-left.svg","tick.svg","taxi-driver.svg","deliver_scooter.svg","tick.svg");
                    
                    //print_R($img_array); exit;
                    //echo "<pre>";print_r($orderStatus->message);die;
                    $flag_cancelled = 0;
                    foreach ($orderStatus->message as $key => $value) {
                        //echo "<pre>";print_R($value);die;
                        $time = date("h:i A", strtotime($value->dDate));
                        if ($value->eCompleted == 'Yes') {
                            if ($value->iStatusCode == 8 || $value->iStatusCode == 9 || $value->iStatusCode == 11) {
                                ?>
                                <li class="cancel passed" date-time="<?= $time; ?>">
                                    <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/close.svg" alt=""></i>
                                    <strong><?= $value->vStatus; ?></strong>
                                    <p><?= $value->vStatus_Track; ?></p>
                                </li>   
                                <?php
                                $flag_cancelled = 1;
                            } else {
                                ?>
                                <li class="passed" date-time="<?= $time; ?>">
                                    <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/<?= $img_array[$key]; ?>" alt=""></i>
                                    <strong><?= $value->vStatus; ?></strong><?php if ($value->iStatusCode == 5) { ?><a class="open-popup" data-id="call-info-model" onClick="displayDriverDetails('<?= $value->phoneCode; ?>', '<?= $generalobj->clearPhone($value->vPhone); ?>', '<?= $generalobj->clearName($value->driverName); ?>');" tell=""><img src="<?= $siteUrl; ?>assets/img/call-img.svg" width="15px" height="15px"  alt="" style="margin:0 0 0 10px"></a><?php } ?></strong>
                                    <p><?= $value->vStatus_Track; ?></p>
                                </li>
                                <?php
                            }
                        } else {
                            ?>
                            <li class="" id="status<?= $value->iStatusCode; ?>">
                                <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/<?= $img_array[$key]; ?>" alt=""></i>
                                <strong><?= $value->vStatus; ?></strong>
                                <p><?= $value->vStatus_Track; ?></p>
                            </li>
                            <?php
                        }
                    }
                    ?>

                    </ul>
                <?php if ($flag_cancelled == 1) { ?>
                    <div class="order_cancel"><?= strtoupper($langage_lbl['LBL_ORDER_CANCELLED']); ?></div>
                <?php } } ?>
                    
                </div>
                <div class="invoice-pay-type">
                        <?php
                        //Added By HJ On 08-08-2019 For Solved Bug - 6722 Start
                        $ePaymentOption = $db_order_data['ePaymentOption'];
                        if ($db_order_data['ePaymentOption'] == "Card" && $db_order_data['ePayWallet'] == 'Yes') {
                            $ePaymentOption = $langage_lbl_admin['LBL_WALLET_TXT'];
                        }
                        //Added By HJ On 08-08-2019 For Solved Bug - 6722 End
                        ?>
                    <strong><?= $langage_lbl['LBL_PAYMENT_TYPE_TXT']; ?> : </strong>
					<strong><?= $ePaymentOption; ?></strong>
                </div>
            </div>
        </div>

       <div class="btn" style="margin-top: 30px;">
            <a onclick="javascript:window.top.close();" class="gen-btn">Close</a>

        </div>

    </div>






</section>
            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <script src="assets/js/gmap3.js"></script>
        <script type="text/javascript">
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
        <!-- End: Footer Script -->
    </body>
</html>
