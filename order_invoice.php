<?
include_once('common.php');
include_once('generalFunctions.php');


//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
	include_once("cx-invoice_deliverall.php");
	exit;
}

$tbl_name = 'orders';
$script = "Order";
$generalobj->check_member_login();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);
if($_SESSION["sess_eSystem"] != "DeliverAll")
{
    header('Location:profile.php');
}
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
        <title><?= $SITE_NAME ?> |<?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>  
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>
            <div class="page-contant">
                <div class="page-contant-inner page-trip-detail clearfix">
                    <h2 class="header-page trip-detail"><?= $langage_lbl['LBL_ORDERS_TXT']; ?>
                        <a onClick="javascript:window.top.close();"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>
                        <?php
                        if (!empty($db_order_data)) {
                            ?>
                            <p><?= $langage_lbl['LBL_RATING_PAGE_HEADER_TXT']; ?> <strong><?= @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?> <?= $langage_lbl['LBL_ON']; ?> <?= @date('d M Y', @strtotime($db_order_data['DeliveryDate'])); ?></strong></p>

                        </h2>
                        <div class="trip-detail-page">
                            <div class="trip-detail-page-inner">
                                <div class="trip-detail-page-left">	        			
                                    <div class="trip-detail-map">
                                        <div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
                                    </div>
                                    <div class="map-address">
                                        <ul>
                                            <li> 
                                                <b><i aria-hidden="true" class="fa fa-map-marker fa-22 green-location"></i></b>
                                                <span>
                                                    <h3><?= @date('h:i A', @strtotime($db_order_data['OrderRequestDatenew'])); ?></h3>
                                                    <p>
                                                        <?= $generalobj->clearCmpNameFront($db_order_data['CompanyName']) ?>
                                                        <? if (!empty($getratings['CompanyRate'])) { ?>
                                                            (<img src="assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate'] ?>) 
                                                        <? } ?>
                                                    </p>
                                                    <?= $db_order_data['vRestuarantLocation']; ?>
                                                </span> 
                                            </li>
                                            <li> 
                                                <b><i aria-hidden="true" class="fa fa-map-marker fa-22 red-location"></i></b> 
                                                <span>
                                                    <h3><?= @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?></h3>
                                                    <p>
                                                        <?= $generalobj->clearName($db_order_data['UserName']) ?>
                                                        <? if (!empty($getratings['UserRate'])) { ?>
                                                            (<img src="assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>) 
                                                        <? } ?>
                                                    </p>
                                                    <?= $db_order_data['DeliveryAddress']; ?>
                                                </span> 
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="location-time">
                                        <ul>
                                            <li>
                                                <h3><?= $langage_lbl['LBL_ORDER_NO_TXT']; ?></h3>
                                                <?php echo $db_order_data['vOrderNo']; ?>
                                            </li>

                                            <li>
                                                <h3><?= $langage_lbl['LBL_ORDER_STATUS_TXT']; ?></h3>
                                                <?= $db_order_data['vStatus']; ?> 
                                            </li>
                                            <li>
                                                <h3><?= $langage_lbl['LBL_PAID_BY_TXT']; ?></h3>
                                                <?php
                                                $paymentMode = $db_order_data['ePaymentOption'];
                                                if ($db_order_data['ePayWallet'] == 'Yes') {
                                                    $paymentMode = $langage_lbl['LBL_WALLET_TXT'];
                                                }
                                                ?>
                                                <?= $paymentMode; ?>
                                            </li>

                                        </ul>
                                    </div>
                                    <?
                                    if ($_SESSION['sess_user'] != 'driver') {
                                        if ($db_order_data['DriverName'] != '') {
                                            ?> 
                                            <div class="location-time">
                                                <div style="padding: 10px;text-align: center;">
                                                    <b><?= $langage_lbl['LBL_ORDER_DELIVERED_BY_DRIVER']; ?> : <?php echo $generalobj->clearName($db_order_data['DriverName']); ?>
                                                        <? if (!empty($getratings['DriverRate'])) { ?>
                                                            (<img src="assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>) 
                                                        <? } ?>
                                                    </b>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="trip-detail-page-right">				
                                    <?php if ($_SESSION['sess_user'] == 'driver') { ?>
                                        <div class="driver-info" style="height: auto;">
                                            <h3 style=" margin: 30px 0;"> <?= $langage_lbl['LBL_DELIVERY_EARNING_FRONT']; ?></h3>
                                        </div>
                                        <div class="fare-breakdown">
                                            <div class="fare-breakdown-inner">
                                                <ul>
                                                    <? foreach ($db_order_data['History_Arr'] as $key => $value) { ?>
                                                        <li>
                                                            <strong style="font-weight: bold;"><?= $key; ?></strong>
                                                            <b><?= $value; ?></b>
                                                        </li>
                                                    <?php }
                                                    ?>
                                                </ul>
                                                <div style="clear:both;"></div>
                                            </div>
                                        </div>
                                        <? if ($db_order_data['DriverName'] != '') { ?> 
                                            <div class="location-time" style="border: 1px solid #dfdfdf">
                                                <div style="padding: 10px;">
                                                    <b><?= $langage_lbl['LBL_ORDER_DELIVERED_BY_DRIVER']; ?> : <?php echo $generalobj->clearName($db_order_data['DriverName']); ?>
                                                        <? if (!empty($getratings['DriverRate'])) { ?>
                                                            (<img src="assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>) 
                                                        <? } ?>
                                                    </b>
                                                </div>
                                            </div>
                                        <?php } ?>

                                    <?php } else if ($_SESSION['sess_user'] == 'rider') { ?>
                                        <div class="driver-info" style="height: auto;">
                                            <h3 style=" margin: 30px 0;"> <?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></h3>
                                        </div>
                                        <div class="fare-breakdown">
                                            <div class="fare-breakdown-inner">
                                                <? $db_menu_item_list = $db_order_data['itemlist']; ?>
                                                <h3><?php echo $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></h3>
                                                <?php if (!empty($db_menu_item_list)) { ?>
                                                    <ul class="order-items">
                                                        <? foreach ($db_menu_item_list as $key => $val) { ?>
                                                            <li>
                                                                <strong><?= $val['MenuItem']; ?> X <?php echo $val['iQty']; ?></strong>
                                                                <b><?= $val['fTotPrice'] ?></b>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                    <span></span>
                                                <?php } ?>
                                                <ul>
                                                    <?
                                                    foreach ($db_order_data['History_Arr'] as $key => $value) {
                                                        if ($key == $langage_lbl['LBL_BILL_SUB_TOTAL']) {
                                                            ?>
                                                            <li>
                                                                <strong style="font-weight: bold;"><?= $key; ?></strong>
                                                                <b><?= $value; ?></b>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <strong><?= $key; ?></strong>
                                                                <b><?= $value; ?></b>
                                                            </li>
                                                        <?php } ?>
                                                    <?php }
                                                    ?>
                                                </ul>
                                                <span class ="final"></span>
                                                <ul class="FirstArray">
                                                    <?
                                                    foreach ($db_order_data['History_Arr_first'] as $key => $value) {
                                                        if ($key == $langage_lbl['LBL_TOTAL_BILL_AMOUNT_TXT']) {
                                                            ?>
                                                            <li>
                                                                <strong style="font-weight: bold;"><?= $key; ?></strong>
                                                                <b><?= $value; ?></b>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <strong><?= $key; ?></strong>
                                                                <b><?= $value; ?></b>
                                                            </li>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="driver-info" style="height: auto;">
                                            <h3 style=" margin: 30px 0;"> <?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></h3>
                                        </div>
                                        <div class="fare-breakdown">
                                            <div class="fare-breakdown-inner">
                                                <? $db_menu_item_list = $db_order_data['itemlist']; ?>
                                                <h3><?php echo $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></h3>
                                                <?php if (!empty($db_menu_item_list)) { ?>
                                                    <ul class="order-items">
                                                        <? foreach ($db_menu_item_list as $key => $val) { ?>
                                                            <li>
                                                                <strong><?= $val['MenuItem']; ?> X <?php echo $val['iQty']; ?><br/>
                                                                    <? if ($val['SubTitle'] != '') { ?>
                                                                        <small style="font-size: 10px;">(<?= $val['SubTitle']; ?>)</small>
                                                                    <? } ?>
                                                                </strong>
                                                                <b><?= $val['fTotPrice'] ?></b>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                    <span></span>
                                                <?php } ?>
                                                <ul>
                                                    <?
                                                    foreach ($db_order_data['History_Arr'] as $key => $value) {
                                                        if ($key == $langage_lbl['LBL_BILL_SUB_TOTAL']) {
                                                            ?>
                                                            <li>
                                                                <strong style="font-weight: bold;"><?= $key; ?></strong>
                                                                <b><?= $value; ?></b>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <strong><?= $key; ?></strong>
                                                                <b><?= $value; ?></b>
                                                            </li>
                                                        <?php } ?>
                                                    <?php }
                                                    ?>
                                                </ul>
                                                <span class ="final"></span>
                                                <ul class="FirstArray">
                                                    <?
                                                    foreach ($db_order_data['History_Arr_first'] as $key => $value) {
                                                        if ($key == $langage_lbl['LBL_TOTAL_BILL_AMOUNT_TXT']) {
                                                            ?>
                                                            <li>
                                                                <strong style="font-weight: bold;"><?= $key; ?></strong>
                                                                <b><?= $value; ?></b>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <strong><?= $key; ?></strong>
                                                                <b><?= $value; ?></b>
                                                            </li>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                                <span class ="final"></span>
                                                <ul>
                                                    <? foreach ($db_order_data['History_Arr_second'] as $key => $value) { ?>
                                                        <li>
                                                            <strong style="font-weight: bold;"><?= $key; ?></strong>
                                                            <b><?= $value; ?></b>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <div style="clear:both"></div>
                                    <?php if ($db_order_data['iStatusCode'] == '8') { ?>
                                        <div class="panel panel-warning" style="margin-bottom: 0">
                                            <div class="panel-heading">
                                                <div><?= $langage_lbl["LBL_ORDER_CANCEL_WEB_TEXT"]; ?></div>
                                                <?php
                                                if ($_SESSION['sess_user'] == 'company') {
                                                    if ($db_order_data['fRestaurantPaidAmount'] > 0) {
                                                        ?>
                                                        <div><?php echo $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>  : <?php echo $db_order_data['RestaurantPaidAmount']; ?></div>
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
                                                        <div><?php echo $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>: <?php echo $db_order_data['DriverPaidAmount']; ?></div>
                                                    <?php } else { ?>
                                                        <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                        <?php
                                                    }
                                                }
                                                ?>

                                                <?php if ($_SESSION['sess_user'] == 'rider') { ?>
                                                    <div><?= $langage_lbl["LBL_CANCELLATION_CHARGE_WEB"] ?> : <?php echo $db_order_data['CancellationCharge']; ?>
                                                        <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?php echo $db_order_data['vOrderAdjusmentId'] ?>)
                                                        <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                            ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"] ?> )
                                                            <?
                                                        } else if ($db_order_data['ePaymentOption'] == 'Card') {
                                                            $paymentMode = $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"];
                                                            if ($db_order_data['ePayWallet'] == 'Yes') {
                                                                $paymentMode = $langage_lbl['LBL_WALLET_TXT'];
                                                            }
                                                            ?>
                                                            ( <?= $paymentMode; ?> )
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
                                                        <div><?php echo $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> : <?php echo $db_order_data['RestaurantPaidAmount']; ?></div>
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
                                                        <div><?php echo $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>: <?php echo $db_order_data['DriverPaidAmount']; ?></div>
                                                    <?php } else { ?>
                                                        <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                        <?php
                                                    }
                                                }
                                                ?>

                                                <?php if ($_SESSION['sess_user'] == 'rider') { ?>
                                                    <div> <?= $langage_lbl["LBL_CANCELLATION_CHARGE_WEB"] ?> : <?php echo $db_order_data['CancellationCharge']; ?>
                                                        <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?php echo $db_order_data['vOrderAdjusmentId'] ?>)
                                                        <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                            ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"] ?> )
                                                            <?
                                                        } else if ($db_order_data['ePaymentOption'] == 'Card') {
                                                            $paymentMode = $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"];
                                                            if ($db_order_data['ePayWallet'] == 'Yes') {
                                                                $paymentMode = $langage_lbl['LBL_WALLET_TXT'];
                                                            }
                                                            ?>
                                                            ( <?= $paymentMode; ?> )
                                                        <? } ?>
                                                    </div>
                                                    <div> <?= $langage_lbl["LBL_REFUND_WEB_TXT"] ?> : <?php echo $db_order_data['RefundAmount']; ?></div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>

                                </div>		
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="trip-detail-page">
                            <div class="trip-detail-page-inner">
                                We could not find INVOICE details for this Order. Please click browser's back button and check again.
                            </div>
                        </div>
                    <?php } ?> 
                </div>
            </div>
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
