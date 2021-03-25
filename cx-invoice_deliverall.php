<?php
    include_once('common.php');
    include_once('generalFunctions.php');
//     ini_set('display_errors', 1);
// error_reporting(E_ALL);
    $tbl_name = 'orders';
    $script = "Order";
    $generalobj->check_member_login();
    $abc = 'rider,driver,company';
    $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $generalobj->setRole($abc,$url);
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "") {
        $HTTP_REFERER = $_SERVER['HTTP_REFERER'];
        $_SESSION['HTTP_REFERER'] = $HTTP_REFERER;
    }
    $vLang = "EN";
    if(isset($_SESSION['sess_lang']) && trim($_SESSION['sess_lang']) != ""){
        $vLang = $_SESSION['sess_lang'];
    }
    $sessionUserType = "rider";
    if(isset($_SESSION['sess_user']) && $_SESSION['sess_user'] != ""){
        $sessionUserType = $_SESSION['sess_user'];
    }
    $sessionUserId = 0;
    if(isset($_SESSION['sess_iUserId']) && $_SESSION['sess_iUserId'] != ""){
        $sessionUserId = $_SESSION['sess_iUserId'];
    }
    //$encodeTripId = base64_encode(base64_encode(1203));
    //echo $encodeTripId;die;
    $_REQUEST['iOrderId'] = base64_decode(base64_decode(trim($_REQUEST['iOrderId'])));
    $iOrderId = isset($_REQUEST['iOrderId']) ? $_REQUEST['iOrderId'] : '';
    
    $ssql_order = "";
    if($sessionUserType == "rider") {
       $ssql_order = " where iOrderId = '".$iOrderId."' AND iUserId = '".$sessionUserId."'";
    } else if($sessionUserType == "driver") {
        $ssql_order = " ord LEFT JOIN trips as t ON t.iOrderId=ord.iOrderId where ord.iOrderId = '".$iOrderId."' AND ord.iDriverId = '".$sessionUserId."'";
    } else {
        $ssql_order = " ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId where ord.iOrderId = '".$iOrderId."' AND ord.iCompanyId = '".$sessionUserId."'";
    }
    
    $user_order_sql = "select * from orders ".$ssql_order; 
    $user_order_sql_data = $obj->MySQLSelect($user_order_sql);
    $iServiceId = 1;
    if(!empty($user_order_sql_data)){
        $iServiceId  = $user_order_sql_data[0]['iServiceId'];
        if ($sessionUserType == 'driver') {
            $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $sessionUserId, 'Driver');
        } else if ($sessionUserType == 'rider') {
            $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $sessionUserId, 'Passenger');
        } else {
            $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $sessionUserId, 'Company');
        }
    }
    $langage_lbl  = getLanguageLabelsArr($vLang, "1", $iServiceId);
    if ($sessionUserType == 'driver') {
        $UserType = 'Driver';
    } else if ($sessionUserType == 'rider') {
        $UserType = 'Passenger';
    } else {
        $UserType = 'Company';
    }
    
    $getratings = $generalobj->getrating($iOrderId);
    
    $iStatusCode = $db_order_data['iStatusCode'];
    
    $PUBSUB_TECHNIQUE = "SocketCluster"; //changed by sneha
    //$latlongSelect = $obj->MySQLSelect("SELECT vLatitude,vLongitude,tDestinationLatitude,tDestinationLongitude FROM `register_user` where iUserId = '" . $sessionUserId . "'");
    
    $vLatitude = $db_order_data['vRestuarantLocationLat'];
    $vLongitude = $db_order_data['vRestuarantLocationLong'];
    $vLatitudeSource = $db_order_data['vRestuarantLocationLat'];
    $vLongitudeSource = $db_order_data['vRestuarantLocationLong'];
    $tDestinationLatitude = $db_order_data['vLatitude'];
    $tDestinationLongitude = $db_order_data['vLongitude'];
    
    $latlongSelect = $obj->MySQLSelect("SELECT u.tDestinationLatitude as tDestinationLatitude, u.tDestinationLongitude as tDestinationLongitude, ua.vLatitude as vLatitude, ua.vLongitude as vLongitude FROM  `user_address` AS ua,  `register_user` AS u WHERE u.iUserId = '" . $sessionUserId . "' AND u.iUserId = ua.iUserId AND ua.eUserType =  'Rider'");
    //$vLatitude = $latlongSelect[0]['vLatitude'];
    //$vLongitude = $latlongSelect[0]['vLongitude'];
    //$tDestinationLatitude = $latlongSelect[0]['tDestinationLatitude'];
    //$tDestinationLongitude = $latlongSelect[0]['tDestinationLongitude'];
    if (empty($tDestinationLatitude) && empty($tDestinationLongitude)) {
        $tDestinationLatitude = $latlongSelect[0]['vLatitude'];
        $tDestinationLongitude = $latlongSelect[0]['vLongitude'];
    }
    
    $driverId = $db_order_data['iDriverId'];
    if ($driverId != 0) {
        $latlongSelect = $obj->MySQLSelect("SELECT vLatitude,vLongitude,vWorkLocationLatitude,vWorkLocationLongitude,eSelectWorkLocation FROM `register_driver` where iDriverId = '" . $driverId . "'");
        if ($latlongSelect[0]['eSelectWorkLocation'] == 'Dynamic') {
            $vLatitude = $latlongSelect[0]['vLatitude'];
            $vLongitude = $latlongSelect[0]['vLongitude'];
        } else {
            $vLatitude = $latlongSelect[0]['vWorkLocationLatitude'];
            $vLongitude = $latlongSelect[0]['vWorkLocationLongitude'];
        }
    }
    
    $tripsOrders = $obj->MySQLSelect("SELECT iTripId,tStartLat,tStartLong,tEndLat,tEndLong FROM `trips` where iOrderId = '" . $iOrderId . "'");
    if (!empty($tripsOrders)) {
        $iTripId = $tripsOrders[0]['iTripId'];
        $vLatitude = $tripsOrders[0]['tStartLat'];
        $vLongitude = $tripsOrders[0]['tStartLong'];
        $tDestinationLatitude = $tripsOrders[0]['tEndLat'];
        $tDestinationLongitude = $tripsOrders[0]['tEndLong'];
    }
    //echo $vLongitude;exit;
    //echo $vLatitude."aaaaaa".$vLongitude."<br>"; exit;
    //echo $tDestinationLatitude."bbbbbb".$tDestinationLongitude."<br>";
    //print_r($_REQUEST);die;
    $tsite_sc_host = $tconfig['tsite_sc_host'];
    $tsite_host_sc_port = $tconfig['tsite_host_sc_port'];
    $siteUrl = $tconfig["tsite_url"];
    //Added By HJ On 13-02-2020 For Display Paymen Type Start
    $paymentType = ucwords($db_order_data['ePaymentOption']);
    if(isset($db_order_data['fNetTotal']) > 0 && $db_order_data['ePayWallet'] == "Yes"){
        if(strtoupper($db_order_data['ePaymentOption']) == "CARD"){
            //$paymentType = ucwords($langage_lbl_admin["LBL_PAY_BY_CARD_TXT"])."-".ucwords($langage_lbl_admin['LBL_WALLET_TXT']); // Commented BY HJ On 24-02-2020 As Per Discuss with KS Sir
            $paymentType = ucwords($langage_lbl_admin["LBL_CARD_CAPS"]);
        }else if(strtoupper($db_order_data['ePaymentOption']) == "CASH"){
            //$paymentType = ucwords($langage_lbl_admin["LBL_PAY_BY_CASH_TXT"])."-".ucwords($langage_lbl_admin['LBL_WALLET_TXT']); // Commented BY HJ On 24-02-2020 As Per Discuss with KS Sir
            $paymentType = ucwords($langage_lbl_admin["LBL_CASH_CAPS"]);
        }
    }
    //Added By HJ On 13-02-2020 For Display Paymen Type End
    
    $takeaway = 'No';
    //if(isTakeAwayEnable()) {
       //$takeawayenable = isTakeAwayEnable();
       if($user_order_sql_data[0]['eTakeaway']=='Yes') {
          $prepareTime = $obj->MySQLSelect("select fPrepareTime from company where iCompanyId = ".$user_order_sql_data[0]['iCompanyId']);
          $preparetimedata = $prepareTime[0]['fPrepareTime']." ".$langage_lbl['LBL_MINUTES_TXT'];
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
            $vLang  = ($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : 'EN';
            $ssql = " WHERE iPreferenceId IN (".$selectedPrefIds.")";
            $deliveryPrefSql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_EXTRACT(tTitle, '$.tTitle_".$vLang."')) as tTitle, JSON_UNQUOTE(JSON_EXTRACT(tDescription, '$.tDescription_".$vLang."')) as tDescription, ePreferenceFor, eImageUpload, iDisplayOrder, eContactLess, eStatus FROM delivery_preferences ".$ssql;
    
            $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);    
        }
    }

    ?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>  
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
        <!--<link rel="stylesheet" type="text/css" href="assets/css/invoiceDeliverall.css" />-->
        <style>
            .Msgbox{
            width:90%;padding-left:50px;text-align: center;
            }
            .marker {
            transform: rotate(-180deg);
            }
            .cancel {
            margin: 0 0 50px 0;
            }
            .delivery-pref-title {
            padding: 0 10px 20px 0;
            margin-top: 0
            }
            .delivery-pref-list {
            margin: 0 0 30px 0 !important;
            display: block !important;
            background-color: #ffffff !important;
            padding: 10px 0;
            }
            .delivery-pref-list li {
            text-align: left !important;
            padding: 10px 20px !important;
            width: 100% !important;
            border-right: none !important;
            margin-bottom: 0 !important;
            }
            .delivery-pref-list li label {
            font-weight: 600;
            }
            .delivery-pref-list li label span {
            margin-left: 5px;
            cursor: pointer;
            font-size: 18px
            }
            .delivery-pref-list li p {
            color: #404040;
            font-size: 12px;
            font-weight: 400;
            line-height: 18px;
            margin-top: 5px;
            }
            .delivery-pref-list .gen-btn {
            margin: 0;
            font-size: 14px;
            padding: 10px 20px;
            }
        </style>
        <?php if ($PUBSUB_TECHNIQUE == "SocketCluster" && $UserType == 'Passenger') {
            ?>
        <!--Socket Cluster Publish Data File Name socket_cluster.php In Root Directory BY HJ On 12-01-2019 -->
        <script type="text/javascript" src="<?= $siteUrl; ?>assets/libraries/socketcluster-client-master/socketcluster.js"></script>
        <script>
            $(document).ready(function () {
                var statuscode = '<?= $iStatusCode; ?>';
                //if(statuscode==5 || statuscode==6 || statuscode==11 || statuscode==13) {} else {
                if (statuscode == 6 || statuscode == 8) {
                } else {
                    var options = {
                        hostname: '<?= $tsite_sc_host; ?>',
                        secure: false,
                        port: '<?= $tsite_host_sc_port; ?>',
                        rejectUnauthorized: false // Only necessary during debug if using a self-signed certificate
                    };
            
                    var driverid = <?= $driverId; ?>;
            
                    var socket = socketCluster.create(options);
                    //var channel = 'ONLINE_DRIVER_LOC_1';
                    var channel = 'ONLINE_DRIVER_LOC_<?= $driverId; ?>';
                    var channel1 = 'PASSENGER_<?= $sessionUserId; ?>';
                    var takeaway = '<?= $takeaway; ?>';
            
            
                    socket.on('connect', function (status) {
                        var channelObject1 = socket.subscribe(channel1);
                        console.log(channelObject1);
                        if (driverid != 0) {
                            subscribechannel(channel);
                        }

                        channelObject1.watch(function (handlerFn) {
                            //console.log("handlerFnaaaaaaaaaaaaaaaaaaaa");
                            var response = JSON.parse(handlerFn);
                            console.log("Socket Cluster Message Found");
                            console.log(response);
                            $.ajax({
                                type: "POST",
                                datatype: "JSON",
                                url: 'cx-ajax_getOrderstatus.php',
                                data: {'order_details': response, 'iUserId': '<?= $sessionUserId; ?>', 'iUserType': '<?= $UserType; ?>','template':'<?= $template ?>','iOrderId':'<?= $iOrderId ?>'},
                                success: function (data)
                                {
                                    if(data!="No") {
                                        $("#orderstatusall").html(data);
                                        if (response.Message=='OrderConfirmByRestaurant' && takeaway=='Yes') {
                                            $(".preparetime").show();
                                        } else {
                                            $(".preparetime").hide();
                                        }
                                        if (response.Message=="OrderDelivered" && takeaway=='Yes') {
                                          $(".pickuptext").show();
                                        } else {
                                            $(".pickuptext").hide();

                                        }   
                                    }
                                }
                            });
                            driverid = $("#driverid").val();
                            console.log(driverid);
                            if (driverid == 0) {
                                $.ajax({
                                    type: "POST",
                                    datatype: "JSON",
                                    url: 'cx-ajax_getOrderstatus.php',
                                    data: {'chk_driver_assign': 1, 'order_details': response, 'iUserId': '<?= $sessionUserId; ?>', 'iUserType': '<?= $UserType; ?>','iOrderId':'<?= $iOrderId ?>'},
                                    success: function (data)
                                    {
                                        console.log(data);
                                        if (data != 0) {
                                            
                                            $("#driverid").val(data);
                                            var channel = 'ONLINE_DRIVER_LOC_' + data;
                                            subscribechannel(channel);
                                        }
                                    }
                                });
                            }
                        });
                    });
                }
                function subscribechannel(channel) {
                    var channelObject = socket.subscribe(channel);
                    console.log(channelObject);
            
                    channelObject.watch(function (handlerFn) {
                        //console.log("handlerFnaaaaaaaaaaaaaaaaaaaa");
                        var response = JSON.parse(handlerFn);
                        console.log("Socket Cluster Message Found");
                        console.log(response);
                        handleResponse(response);
                    });
                }
            });
        </script>
        <?php } ?>
    </head>
    <body>
        <!-- home page -->
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- First Section -->
            <?php include_once("top/header.php"); ?>
            <!-- End: First Section -->
            <?php 
                if(isDeliveryPreferenceEnable()) 
                { 
                    include_once('contactless_pref_modal.php');
                } 
                ?>
            <section class="profile-section">
                <div class="profile-section-inner">
                    <div class="profile-caption _MB0_">
                        <div class="page-heading">
                            <h1><?php if($takeaway=='Yes') {
                                echo $langage_lbl['LBL_ORDERS_TXT']." - ".$langage_lbl['LBL_TAKE_AWAY'];    
                                } else {
                                echo $langage_lbl['LBL_ORDERS_TXT'];
                                } ?></h1>
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
                                    <?php if($db_order_data['iStatusCode'] == 6 && $db_order_data['eTakeaway']=='Yes' && ($sessionUserType == 'company' || $sessionUserType == 'rider')) { ?>
                                    <span><?= $langage_lbl['LBL_PICKED_UP']; ?></span>
                                    <?php } else { ?>
                                    <span><?= $db_order_data['vStatus']; ?></span>
                                    <?php } ?>
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
                                    <span><?= $generalobj->clearName($db_order_data['UserName']) ?>
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
                            <?php if ($UserType == 'Passenger') { ?>
                            <input type="hidden" name="driverid" id="driverid" value="<?= $driverId; ?>">
                            <div class="gmap-div gmap-div1" id="invoice_map">
                                <div id="map-canvas" class="gmap3 google-map" style="height: 302px"></div>
                            </div>
                            <?php if(isDeliveryPreferenceEnable() && $selectedPrefIds != "") { ?>
                            <strong class="sub-block-title"><?= $langage_lbl['LBL_DELIVERY_PREF']; ?></strong>
                            <div class="track-order">
                                <ul class="delivery-pref-list">
                                    <?php foreach ($deliveryPrefSqlData as $delivery_pref) { ?>
                                    <li>
                                        <label>
                                        <?= $delivery_pref['tTitle'] ?>
                                        <?php if($delivery_pref['eContactLess'] == 'Yes') { ?>
                                        <span data-toggle="modal" data-target="#contactless_pref_modal" title="How it works?"><i class="fa fa-question-circle-o"></i></span>
                                        <?php } ?>            
                                        </label>
                                        <p><?= $delivery_pref['tDescription'] ?></p>
                                        <?php if($iStatusCode == 6) { ?>
                                        <?php if($delivery_pref['eContactLess'] == 'Yes' && $user_order_sql_data[0]['vImageDeliveryPref'] != "") { ?>
                                        <p><a href="<?= $tconfig['tsite_upload_order_delivery_pref_images'].$user_order_sql_data[0]['vImageDeliveryPref']; ?>" target="_blank" class="gen-btn">View Image</a></p>
                                        <?php } } ?>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php } ?>
                            <strong class="sub-block-title"><?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></strong>
                            <?php } ?>
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
                                        <?php if ($sessionUserType == 'driver') { ?>
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
                                        <?php } else if ($sessionUserType == 'rider') { ?>
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
                                                    if ($sessionUserType == 'company') {
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
                                                    if ($sessionUserType == 'driver') {
                                                        if ($db_order_data['fDriverPaidAmount'] > 0) {
                                                            ?>
                                                <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>: <?= $db_order_data['DriverPaidAmount']; ?></div>
                                                <?php } else { ?>
                                                <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                <?php
                                                    }
                                                    }
                                                    ?>
                                                <?php if ($sessionUserType == 'rider') { ?>
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
                                                    if ($sessionUserType == 'company') {
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
                                                    if ($sessionUserType == 'driver') {
                                                        if ($db_order_data['fDriverPaidAmount'] > 0) {
                                                            ?>
                                                <div><?= $langage_lbl["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?>: <?= $db_order_data['DriverPaidAmount']; ?></div>
                                                <?php } else { ?>
                                                <div><?= $langage_lbl["LBL_AMT_GENERATE_PENDING"]; ?></div>
                                                <?php
                                                    }
                                                    }
                                                    ?>
                                                <?php if ($sessionUserType == 'rider') { ?>
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
                                            <p>
                                                <?php if($sessionUserType != 'company') { ?>
                                                <?= $generalobj->clearName($db_order_data['vRestuarantLocation']); ?>
                                            </p>
                                            <?php } else { ?>
                                            <?= $db_order_data['vRestuarantLocation']; ?>
                                            <?php } ?>
                                            </p>
                                        </li>
                                        <li>
                                            <i class="fa fa-clock-o"></i>
                                            <strong><?= $langage_lbl["LBL_ORDER_PICKUP_TIME"] ?>:</strong>
                                            <p><?= @date('h:i A', @strtotime($db_order_data['OrderRequestDatenew'])); ?></p>
                                        </li>
                                        <?php if($db_order_data['eTakeaway']!='Yes') { ?>
                                        <li>
                                            <i class="fa fa-map-marker"></i>
                                            <strong><?= $langage_lbl["LBL_USER_ADDRESS"] ?>:</strong>
                                            <p>
                                                <?php if($sessionUserType != 'company') { ?>
                                                <?= $generalobj->clearName($db_order_data['DeliveryAddress']); ?>
                                                <?php } else { ?>
                                                <?= $db_order_data['DeliveryAddress']; ?>
                                                <?php } ?>
                                            </p>
                                        </li>
                                        <li>
                                            <i class="fa fa-clock-o"></i>
                                            <strong><?= $langage_lbl["LBL_DELIVERY_TIME"] ?>:</strong>
                                            <p><?= @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?></p>
                                        </li>
                                        <?php } ?>
                                        <li>
                                            <i class="fa fa-list-alt"></i>
                                            <strong><?= $langage_lbl["LBL_SPECIAL_INSTRUCTION_TXT"] ?>:</strong>
                                            <p><?= !empty(trim($db_order_data['vInstruction'])) ? $generalobj->clearName($db_order_data['vInstruction']) : $langage_lbl["LBL_NO_SPECIAL_INSTRUCTION"]; ?></p>
                                        </li>
                                        <?php if($db_order_data['eTakeaway']=='Yes' && $sessionUserType == 'company') { ?>
                                            <li>
                                                <i class="fa fa-first-order"></i>
                                                <strong><?= $langage_lbl["LBL_ORDER_PICKDUP"] ?>:</strong>
                                                <?php if($sessionUserType == 'company') { ?>
                                                <p><?= str_replace("#RESTAURANT_NAME#", $db_order_data['CompanyName'], $langage_lbl["LBL_TAKE_AWAY_ORDER_NOTE"]); ?></p>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
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
                                    <p>
                                        <?php if($sessionUserType != 'company') { ?>
                                        <?= $generalobj->clearName($db_order_data['CompanyName']); ?>
                                        <?php } else { ?>
                                        <?= $db_order_data['CompanyName']; ?>
                                        <?php } ?>
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
                                        $orderStatus_json = getOrderDetailsforGettingliveStatus($iOrderId, $sessionUserId, $UserType);
                                        $orderStatus = json_decode($orderStatus_json);
                                        
                                        if($db_order_data['eTakeaway'] == 'Yes') {
                                            $img_array = array("time-left.svg","shop.svg","tick.svg");
                                        } else {    
                                            $img_array = array("time-left.svg","shop.svg","taxi-driver.svg","deliver_scooter.svg","tick.svg");
                                        }
                                        
                                        //print_R($img_array); exit;
                                        $flag_cancelled = 0;
                                        $all_order_status = array();
                                        foreach ($orderStatus->message as $key => $value) {
                                            //echo "<pre>";print_R($value);die;
                                            $all_order_status[] = $value->iStatusCode;
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
                                        <?php /*if($value->eTakeaway == 'Yes') { ?>
                                        <p><?= str_replace('#RESTAURANT_NAME#',$db_order_data['CompanyName'],$langage_lbl['LBL_TAKE_AWAY_ORDER_NOTE']); ?></p>
                                        <?php } else {*/ ?>
                                        <p><?= $value->vStatus_Track; ?></p>
                                        <?php //} ?>
                                    </li>
                                    <?php
                                        }
                                        } else {
                                        ?>
                                    <li class="" id="status<?= $value->iStatusCode; ?>">
                                        <i class="track-icon"><img src="assets/img/apptype/<?php echo $template;?>/<?= $img_array[$key]; ?>" alt=""></i>
                                        <strong><?= $value->vStatus; ?></strong>
                                        <?php /*if($value->eTakeaway == 'Yes') { ?>
                                        <p><?= str_replace('#RESTAURANT_NAME#',$db_order_data['CompanyName'],$langage_lbl['LBL_TAKE_AWAY_ORDER_NOTE']); ?></p>
                                        <?php } else {*/ ?>
                                        <p><?= $value->vStatus_Track; ?></p>
                                        <?php //} ?>
                                    </li>
                                    <?php
                                        }
                                        }
                                        ?>
                                </ul>
                                <?php if ($flag_cancelled == 1) { ?>
                                <div class="order_cancel"><?= strtoupper($langage_lbl['LBL_ORDER_CANCELLED']); ?></div>
                                <?php }
                                    if($takeaway=='Yes') { ?>
                                <div class="new-box-one preparetime" style="margin-top:20px; <?php if($value->OrderCurrentStatusCode==2) { ?> display:block; <? } else { ?> display:none; <? } ?>">
                                    <h4><b><?= $langage_lbl['LBL_REST_PREPARATION_TIME']?></b>: <?= $preparetimedata ?></h4>
                                    <div style="margin-top:5px"><i aria-hidden="true" class="fa fa-map-marker fa-22 green-location"></i>
                                        <span><?= $db_order_data['vRestuarantLocation'];  ?></span>
                                    </div>
                                    
                                    <div>
                                        <!-- <a class="gen-btn" target="new" href="https://www.google.com/maps/search/?api=1&query=<?= $vLatitudeSource ?>, <?= $vLongitudeSource; ?>" style="margin: 15px 0 0 0;"><?= $langage_lbl['LBL_NAVIGATE'] ?></a> -->
                                        <a class="gen-btn" target="new" href="https://www.google.com/maps/dir/<?= $vLatitudeSource.','.$vLongitudeSource.'/'.$tDestinationLatitude.','.$tDestinationLongitude ?>" style="margin: 15px 0 0 0;"><?= $langage_lbl['LBL_NAVIGATE'] ?></a>
                                    </div>
                                    
                                </div>
                                <div class="new-box-one pickuptext" style="margin-top:20px; <?php if($value->OrderCurrentStatusCode==6) { ?> display:block; <? } else { ?> display:none; <? } ?>">
                                    <h4><b><?= $langage_lbl["LBL_ORDER_PICKDUP"] ?></b>: <?= str_replace("#RESTAURANT_NAME#", $db_order_data['CompanyName'], $langage_lbl["LBL_TAKE_AWAY_ORDER_NOTE"]); ?></h4>
                                </div>
                                <? } ?>
                                <? } ?>
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
                                <strong><?= $langage_lbl['LBL_PAYMENT_TYPE_TXT']; ?> : </strong><strong><?= $paymentType; ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="btn" style="margin-top: 30px;">
                        <a onclick="javascript:window.top.close();" class="gen-btn"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>
                    </div>
                </div>
            </section>
            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>
        <div class="custom-modal-main in" id="call-info-model">
            <div class="custom-modal">
                <div class="model-header">
                    <h4><?= $langage_lbl['LBL_DELIVER_DETAILS'] ?></h4>
                    <i class="icon-close" data-dismiss="modal"></i>
                </div>
                <div class="model-body">
                    <form class="add-new-card-data" name="frmcreditcard" id="frmcreditcard" onSubmit="return false;" novalidate="novalidate">
                        <!--<div class="close-icon">
                            <svg width="16" height="16" viewBox="0 0 14 14"><path fill="#fff" d="M7 5.586L2.05.636.636 2.05 5.586 7l-4.95 4.95 1.414 1.414L7 8.414l4.95 4.95 1.414-1.414L8.414 7l4.95-4.95L11.95.636 7 5.586z"></path></svg>
                            </div>-->
                        <div id="service_detail">
                            <p id="drverName"></p>
                            <p id="phnnumber"><?= $langage_lbl['LBL_NOT_FOUND']; ?></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include_once('top/footer_script.php'); ?>
        <script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"]; ?>js/gmap3.js"></script>
        <script type="text/javascript" src="<?= $siteUrl; ?>assets/js/jquery_easing.js"></script>
        <script type="text/javascript" src="<?= $siteUrl; ?>assets/js/markerAnimate.js"></script>
        <script>
            $(document).ready(function () {
            
                $(document).on('click', '.open-popup', function (e) {
                    var DATAID = $(this).attr('data-id');
                    $('.small-model').removeClass('active');
                    $(document).find('#' + DATAID).addClass('active');
                });
                $(document).on('click', '.close-icon', function () {
                    $(this).closest('.product-model-overlay').removeClass('active');
                })
            
                $('body').keydown(function (e) {
                    if (e.keyCode == 27) {
                        $('.close-icon').trigger('click')
                    }
                    console.log(e);
                });
            
                var e = $.Event("keydown", {
                    keyCode: 27
                });
            
                $('#escape').click(function () {
                    $("body").trigger(e);
                });
            })
            var iTripId = '<?= $iTripId; ?>';
            var latlng;
            var locallat;
            var locallang;
            var map;
            var interval3;
            var marker = [];
            var marker1 = [];
            var myOptions = [];
            // var directionsService = new google.maps.DirectionsService(); // For Route Services on map
            // var directionsOptions = {// For Polyline Route line options on map
                // polylineOptions: {
                    // strokeColor: '#FF7E00',
                    // strokeWeight: 5
                // }
            // };
            // var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
            
            function moveToLocation(lat, lng) {
                var center = new google.maps.LatLng(lat, lng);
                map.panTo(center);
            }
            function handleResponse(response) {
                if (response.vLatitude != "" && response.vLongitude != "") {
                    console.log(response.vLatitude);
                    console.log(response.vLongitude);
                    $('.map-page').show();
                    latlng = new google.maps.LatLng(response.vLatitude, response.vLongitude);
                    myOptions = {
                        zoom: 4,
                        center: latlng,
                    }
                    var duration = parseInt(950);
                    if (duration < 0) {
                        duration = 1;
                    }
                    setTimeout(function () {
                        marker.animateTo(latlng, {easing: 'linear', duration: duration});
                        map.panTo(latlng); // For Move Google Map By Animate
                    }, 2000);
                }
            }
            function displayDriverDetails(phoneCode, phone, driverName) {
                $('#call-info-model').addClass('active');
                $("#drverName").text(driverName);
                if (phone != "") {
                    $("#phnnumber").text('<?= $langage_lbl['LBL_PHONE']; ?>: ' + "+" + phoneCode + " " + phone);
                }
                //$("#drverName").text('<?= $langage_lbl['LBL_PHONE']; ?>(' + driverName + ')');
            }
            function changeMarker(deg) {
                google.maps.event.clearListeners(map, 'idle');
            }
            function createPolyLine(cus_polyline) {
                        if(typeof flightPath !== 'undefined'){
                            flightPath.setMap(null);
                            flightPath ='';
                        }
            
                        flightPath = cus_polyline;
                        flightPath.setMap(map);
                    }
            function routeDirections(fromlat, fromlong, tolat, tolong) {
            // var directionsService = new google.maps.DirectionsService(); // For Route Services on map
            var directionsOptions = {// For Polyline Route line options on map
            polylineOptions: {
            strokeColor: '#FF7E00',
            strokeWeight: 5
            }
            };
            var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
                directionsDisplay.setMap(null); // Remove Previous Route.
                //console.log(fromlat + "from" + fromlong + "TO" + tolat + "-- " + tolong);
                if (fromlat != "" && tolat != "") {
                    var newFrom = fromlat + ", " + fromlong;
                    //if (eType == 'UberX') {
                    //var newTo = fromlat + ", " + fromlong;
                    //} else {
                    var newTo = tolat + ", " + tolong;
                    //}
            
                    //Make an object for setting route
                    var request = {
                        origin: newFrom, // From locations latlongs
                        destination: newTo, // To locations latlongs
                        travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
                    };
            
                    //Draw route from the object
            
            //var source_latitude = "23.0121727";
            //var source_longitude = "72.5033563";
            //var dest_latitude = "23.0230532";
            //var dest_longitude = "72.50676709999999";
            //var waypoint0 = "23.0121727, 72.5033563";
            //var waypoint1 = "23.0230532, 72.50676709999999";
            
            var source_latitude = fromlat;
            var source_longitude = fromlong;
            var dest_latitude = tolat;
            var dest_longitude = tolong;
            var waypoint0 = newFrom;
            var waypoint1 = newTo;
            
            getReverseGeoDirectionCode(source_latitude,source_longitude,dest_latitude,dest_longitude,waypoint0,waypoint1,function(data_response){
					//if((data_response.Action==0 && data_response.message=="LBL_DEST_ROUTE_NOT_FOUND") || data_response.status=="ZERO_RESULTS") { 
					//} else {
                    
console.log(source_latitude);
console.log(source_longitude);
console.log(dest_latitude);
console.log(dest_longitude);
console.log(waypoint0);
console.log(waypoint1);
                    
                        if (MAPS_API_REPLACEMENT_STRATEGY.toUpperCase() == 'NONE'){
            $("#distance").val(data_response.routes[0].legs[0].distance.value);
            $("#duration").val(data_response.routes[0].legs[0].duration.value);
            var points = data_response.routes[0].overview_polyline.points;
            console.log(points);
            var polyPoints = google.maps.geometry.encoding.decodePath(points);
            // var polyPoints = data_response;
            directionsDisplay.setMap(null);
            directionsDisplay.setMap(map);
            directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
            createPolyLine(new google.maps.Polyline({path: polyPoints,strokeColor: '#FF7E00',strokeWeight: 5}));
            points = '';
            data_response = [];
            polyPoints = '';
            temp_points = '';
            }else{
            // removePolyLine();
            $("#distance").val(data_response.distance);
            $("#duration").val(data_response.duration);
            var polyLinesArr = new Array();
            var i;
            
            if((data_response.data != 'undefined') && (data_response.data != undefined)){
            for (i = 0; i < (data_response.data).length; i++) {
            polyLinesArr.push({ lat: parseFloat(data_response.data[i].latitude), lng: parseFloat(data_response.data[i].longitude) });
            }
            var polyPoints = polyLinesArr;
            directionsDisplay.setMap(null);
            directionsDisplay.setMap(map);
            directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
            createPolyLine(new google.maps.Polyline({path: polyPoints,strokeColor: '#FF7E00',strokeWeight: 5}));
            data_response = [];
            polyPoints = '';
            }
            }
                    //}
            });
                    // directionsService.route(request, function (response, status) {
                        // if (status == google.maps.DirectionsStatus.OK) {
                            // // Check for allowed and disallowed.
                            // var response1 = JSON.stringify(response);
                            // directionsDisplay.setMap(map);
                            // directionsDisplay.setOptions({suppressMarkers: true}); //, preserveViewport: true, suppressMarkers: false for setting auto markers from google api
                            // directionsDisplay.setDirections(response); // Set route
                            // var route = response.routes[0];
            
                            // for (var i = 0; i < route.legs.length; i++) {
                                // $("#distance").val(route.legs[i].distance.value);
                                // $("#duration").val(route.legs[i].duration.value);
                            // }
            
                            var dist_fare = parseFloat($("#distance").val(), 10) / parseFloat(1000, 10);
                            // alert(dist_fare);
                            if ($("#eUnit").val() != 'KMs') {
                                dist_fare = dist_fare * 0.621371;
                            }
                            // alert(dist_fare);
                            $('#dist_fare').text(dist_fare.toFixed(2));
                            var time_fare = parseFloat($("#duration").val(), 10) / parseFloat(60, 10);
                            $('#time_fare').text(time_fare.toFixed(2));
                            var vehicleId = $('#iVehicleTypeId').val();
                            var booking_date = $('#datetimepicker4').val();
                            var vCountry = $('#vCountry').val();
                            var tollcostval = $('#fTollPrice').val();
                            var userId = $('#iUserId').val();
                            var timeVal = parseFloat($("#duration").val(), 10) / parseFloat(60, 10);
                            var distanceVal = parseFloat($("#distance").val(), 10) / parseFloat(1000, 10);
                            $.ajax({
                                type: "POST",
                                url: 'booking/ajax_estimate_by_vehicle_type.php',
                                dataType: 'json',
                                data: {'vehicleId': vehicleId, 'booking_date': booking_date, 'vCountry': vCountry, 'FromLatLong': newFrom, 'ToLatLong': newTo, 'timeduration': timeVal, 'distance': distanceVal, 'userId': userId},
                                success: function (dataHtml)
                                {
                                    if (dataHtml != "") {
                                        var estimateData = dataHtml.estimateArr;
                                        var totalFare = dataHtml.totalFare;
                                        var estimateHtml = "";
                                        for (var i = 0; i < estimateData.length; i++) {
                                            console.log(estimateData[i])
                                            var eKey = estimateData[i]['key'];
                                            var eVal = estimateData[i]['value']
                                            estimateHtml += '<li><b>' + eKey + '</b> <em>' + eVal + '</em></li>';
                                        }
                                        $("#total_fare_price").text(totalFare);
                                        $("#estimatedata").html(estimateHtml);
                                    } else {
                                        $('#minimum_fare_price,#base_fare_price,#dist_fare_price,#time_fare_price,#total_fare_price').text('0');
                                    }
                                }
                            });
                        // } else {
                            // alert("Directions request failed: " + status);
                        // }
                    // });
                }
            }
            $('.map-page').show();
            var tEndLat1 = '<?= $tDestinationLatitude; ?>';
            var tEndLong1 = '<?= $tDestinationLongitude; ?>';
            var latdrv = '<?= $vLatitude; ?>';
            var longdrv = '<?= $vLongitude; ?>';
            var latdrvSource = '<?= $vLatitudeSource; ?>';
            var longdrvSource = '<?= $vLongitudeSource; ?>';
            
            latlng = new google.maps.LatLng(latdrv, longdrv);
            latlngSource = new google.maps.LatLng(latdrvSource, longdrvSource);
            latlngdest = new google.maps.LatLng(tEndLat1, tEndLong1);
            myOptions = {
                zoom: 14,
                center: latlng,
            }
            map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
            marker = new google.maps.Marker({
                position: latlng,
                map: map,
                icon: {
                    url: '<?= $siteUrl; ?>webimages/upload/mapmarker/middle.png',
                    //scaledSize: new google.maps.Size(50, 50),
                    rotation: 90
                },
                id: 'marker'
            });
            marker2 = new google.maps.Marker({
                position: latlngSource,
                map: map,
                icon: {
                    url: '<?= $siteUrl; ?>webimages/upload/mapmarker/source_marker_store.png',
                    scaledSize: new google.maps.Size(50, 50),
                    rotation: 90
                },
                id: 'marker'
            });
            marker1 = new google.maps.Marker({
                position: latlngdest,
                map: map,
                icon: {
                    url: '<?= $siteUrl; ?>webimages/upload/mapmarker/destination_marker.png',
                    scaledSize: new google.maps.Size(50, 50),
                    rotation: 90
                },
                id: 'marker1'
            });
            
            var statuscode = '<?= $iStatusCode; ?>';
            if (statuscode == 6 || statuscode == 8) {
            } else {
                routeDirections(latdrv, longdrv, tEndLat1, tEndLong1);
            }
            
            $(document).on('click','[data-dismiss="modal"]',function(e){
                e.preventDefault();
                $(this).closest('.custom-modal-main').removeClass('active');
                $('body').css('overflow', 'auto');
            });
            
            $(document).on('keydown', 'body', function(e){
                if (e.which==27){
                   $('.custom-modal-main').removeClass('active');
                   $('.modal-backdrop').remove();
                   $('body').css('overflow', 'auto');
                }
            });
            
            $(document).on('click','[data-toggle="modal"]',function(e){
                e.preventDefault();
                var data_target = $(this).attr('data-target');
                $('.custom-modal-main').removeClass('active');
                $(document).find(data_target).addClass('active');
                $('body').css('overflow', 'hidden');
            });
        </script>
    </body>
</html>
