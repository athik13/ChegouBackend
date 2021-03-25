<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
include_once('common.php');
include_once('generalFunctions.php');

$tbl_name = 'orders';
$script = "Order";

//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-invoice_deliverall.php");
        exit;
}


$generalobj->check_member_login();

$abc = 'rider,driver,company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);
if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "") {
    $HTTP_REFERER = $_SERVER['HTTP_REFERER'];
    $_SESSION['HTTP_REFERER'] = $HTTP_REFERER;
}
//$encodeTripId = base64_encode(base64_encode(1203));
//echo $encodeTripId;die;
$_REQUEST['iOrderId'] = base64_decode(base64_decode(trim($_REQUEST['iOrderId'])));
$iOrderId = isset($_REQUEST['iOrderId']) ? $_REQUEST['iOrderId'] : '';

$ssql_order = "";
if($_SESSION['sess_user'] == "rider") {
   $ssql_order = " where iOrderId = '".$iOrderId."' AND iUserId = '".$_SESSION['sess_iUserId']."'";
} else if($_SESSION['sess_user'] == "driver") {
    $ssql_order = " ord LEFT JOIN trips as t ON t.iOrderId=ord.iOrderId where ord.iOrderId = '".$iOrderId."' AND ord.iDriverId = '".$_SESSION['sess_iUserId']."'";
} else {
    $ssql_order = " ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId where iOrderId = '".$iOrderId."' AND iCompanyId = '".$_SESSION['sess_iUserId']."'";
}

$user_order_sql = "select * from orders ".$ssql_order; 
$user_order_sql_data = $obj->MySQLSelect($user_order_sql);
if(!empty($user_order_sql_data)){
    if ($_SESSION['sess_user'] == 'driver') {
        $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $_SESSION['sess_iUserId'], 'Driver');
    } else if ($_SESSION['sess_user'] == 'rider') {
        $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $_SESSION['sess_iUserId'], 'Passenger');
    } else {
        $db_order_data = $generalobj->getOrderPriceDetailsForWeb($iOrderId, $_SESSION['sess_iUserId'], 'Company');
    }
}


//echo "<pre>";print_r($user_order_sql_data[0]['iCompanyId']);die;
if ($_SESSION['sess_user'] == 'driver') {
    $UserType = 'Driver';
} else if ($_SESSION['sess_user'] == 'rider') {
    $UserType = 'Passenger';
} else {
    $UserType = 'Company';
}

$getratings = $generalobj->getrating($iOrderId);

$iStatusCode = $db_order_data['iStatusCode'];

$PUBSUB_TECHNIQUE = "SocketCluster"; //changed by sneha
//$latlongSelect = $obj->MySQLSelect("SELECT vLatitude,vLongitude,tDestinationLatitude,tDestinationLongitude FROM `register_user` where iUserId = '" . $_SESSION['sess_iUserId'] . "'");

$vLatitude = $db_order_data['vRestuarantLocationLat'];
$vLongitude = $db_order_data['vRestuarantLocationLong'];
$vLatitudeSource = $db_order_data['vRestuarantLocationLat'];
$vLongitudeSource = $db_order_data['vRestuarantLocationLong'];
$tDestinationLatitude = $db_order_data['vLatitude'];
$tDestinationLongitude = $db_order_data['vLongitude'];

$latlongSelect = $obj->MySQLSelect("SELECT u.tDestinationLatitude as tDestinationLatitude, u.tDestinationLongitude as tDestinationLongitude, ua.vLatitude as vLatitude, ua.vLongitude as vLongitude FROM  `user_address` AS ua,  `register_user` AS u WHERE u.iUserId = '" . $_SESSION['sess_iUserId'] . "' AND u.iUserId = ua.iUserId AND ua.eUserType =  'Rider'");
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
        $vLang = "EN";
        $ssql .= " WHERE iPreferenceId IN (".$selectedPrefIds.")";
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
        <title><?= $SITE_NAME ?> |<?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>  
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
        <link rel="stylesheet" type="text/css" href="assets/css/invoiceDeliverall.css" />
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

            .delivery-pref-list li label span {
                margin-left: 5px;
                cursor: pointer;
                font-size: 18px
            }

            .delivery-pref-list {
                padding: 0 20px 10px !important;
                display: block !important;
                background-color: #ffffff !important;
            }

            .delivery-pref-list li {
                text-align: left !important;
                padding: 10px 0 !important;
                width: 100% !important;
                border-right: none !important;
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
                        var channel1 = 'PASSENGER_<?= $_SESSION['sess_iUserId']; ?>';
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
                                    url: 'ajax_getOrderstatus.php',
                                    data: {'order_details': response, 'iUserId': '<?= $_SESSION['sess_iUserId']; ?>', 'iUserType': '<?= $UserType; ?>'},
                                    success: function (data)
                                    {
                                        $("#orderstatusall").html(data);
                                    }
                                });
                                driverid = $("#driverid").val();
                                console.log(driverid);
                                if (driverid == 0) {
                                    $.ajax({
                                        type: "POST",
                                        datatype: "JSON",
                                        url: 'ajax_getOrderstatus.php',
                                        data: {'chk_driver_assign': 1, 'order_details': response, 'iUserId': '<?= $_SESSION['sess_iUserId']; ?>', 'iUserType': '<?= $UserType; ?>'},
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
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>
            <?php 
                if(isDeliveryPreferenceEnable()) 
                { 
                    include_once('contactless_pref_modal.php');
                } 
                ?>
            <div class="page-contant clearfix">
                <div class="page-contant-inner page-trip-detail">
                    <h2 class="header-page trip-detail hide_after">
                    <?php if($takeaway=='Yes') {
                    echo $langage_lbl['LBL_ORDERS_TXT']." - ".$langage_lbl['LBL_TAKE_AWAY'];	
                } else {
                    echo $langage_lbl['LBL_ORDERS_TXT'];
                } ?>
                <?php if (isset($_REQUEST['action']) && $_REQUEST['action'] == "manual") { ?>
                            <a href="<?= $siteUrl; ?>order-items"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>
                        <?php } else { ?>
                            <a onClick="javascript:window.top.close();"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>
                        <?php } ?>
                        <?php
                        if (!empty($db_order_data)) {
                            ?>
                            <p><?= $langage_lbl['LBL_RATING_PAGE_HEADER_TXT']; ?> <strong><?= @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?> <?= $langage_lbl['LBL_ON']; ?> <?= @date('d M Y', @strtotime($db_order_data['DeliveryDate'])); ?></strong></p>

                        </h2>
                        <div class="invoice-main-section">
                            <div class="invoice-top-part">	        			
                                <!--<div class="trip-detail-map trip-detail-map-new">
                                        <div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
                                </div>-->
                                <?php if ($UserType == 'Passenger') { ?>
                                    <input type="hidden" name="driverid" id="driverid" value="<?= $driverId; ?>">
                                    <div class="map-page" style="display:none;">
                                        <!--<div class="panel-heading location-heading">
                                            <i class="icon-map-marker"></i>
                                        <?= $langage_lbl['LBL_LOCATIONS_TXT']; ?>
                                        </div>-->
                                        <div class="panel-heading location-map" style="background:none;">
                                            <div class="google-map-wrap" >
                                                <div class="gmap-div gmap-div1"><div id="map-canvas" class="gmap3 google-map" style="height:500px;"></div></div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class=" map-address-new">
                                        <div class="new-box-one">
                                            <h4><b><?= $db_order_data['CompanyName']; ?></b></h4>
                                        </div>

                                        <div class="new-box-second">

                                            <div class="box-one">
                                                <ul id="orderstatusall" class="orderstatusall-new new-work-li cancel-work-invo">
                                                    <?php
                                                    $orderStatus_json = getOrderDetailsforGettingliveStatus($iOrderId, $_SESSION['sess_iUserId'], $UserType);
                                                    $orderStatus = json_decode($orderStatus_json);
                                                    $flag_cancelled = 0;
                                                    $all_order_status = array();
                                                    foreach ($orderStatus->message as $key => $value) {
                                                        $all_order_status[] = $value->iStatusCode;
                                                        $time = date("h:i A", strtotime($value->dDate));
                                                        if ($value->eCompleted == 'Yes') {
                                                            if ($value->iStatusCode == 8 || $value->iStatusCode == 9 || $value->iStatusCode == 11) {
                                                                ?>
                                                                <b style="text-align:center;font-size: 17px;"><?= $time; ?></b><li class="cancel"><strong class="color-invo" id="status<?= $value->iStatusCode; ?>"><?= $value->vStatus; ?></strong><p style="margin:0 0 0 60px"><?= $value->vStatus_Track; ?></p></li>   
                                                                <?php
                                                                $flag_cancelled = 1;
                                                            } else {
                                                                ?>
                                                                <b style="text-align:center;font-size: 17px;"><?= $time; ?></b><li class="work work-invoice"><strong class="color-invo" id="status<?= $value->iStatusCode; ?>"><?= $value->vStatus; ?>
                                                                        <?php if ($value->iStatusCode == 5) { ?><a class="open-popup" data-id="call-info-model" onClick="displayDriverDetails('<?= $value->phoneCode; ?>', '<?= $generalobj->clearPhone($value->vPhone); ?>', '<?= $generalobj->clearName($value->driverName); ?>');" tell=""><img src="<?= $siteUrl; ?>assets/img/call-img.svg" width="15px" height="15px"  alt="" style="margin:0 0 0 10px"></a><?php } ?>
                                                                    </strong><p style="margin:0 0 0 60px"><?= $value->vStatus_Track; ?></p>
                                                                </li>
                                                                <?php
                                                            }
                                                        } else {

                                                            ?>
                                                            <b style="visibility: hidden;text-align:center;font-size: 17px;"><?= $time; ?></b>
                                                            <li class="work work-invoice-new">
                                                                <strong class="color-invo-new" id="status<?= $value->iStatusCode; ?>"><?= $value->vStatus; ?></strong>
                                                                <p style="margin:0 0 0 60px"><?= $value->vStatus_Track; ?></p>
                                                                
                                                            </li>
                                                            <?php
                                                        }
                                                    }
                                                    ?>

                                                </ul>
                                                <?php if ($flag_cancelled == 1) { ?>
                                                    <div class="order_cancel"><?= strtoupper($langage_lbl['LBL_ORDER_CANCELLED']); ?></div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <? if($takeaway=='Yes') { ?>
                                            <?php if(in_array(2, $all_order_status) && !in_array(6, $all_order_status)) { ?>
                                             <div class="new-box-one" style="margin-top:20px">
                                                <h4><b><?= $langage_lbl['LBL_REST_PREPARATION_TIME']?></b>: <?= $preparetimedata ?></h4>
                                                <div style="margin-top:5px"><i aria-hidden="true" class="fa fa-map-marker fa-22 green-location"></i>
                                                <span><?= $db_order_data['vRestuarantLocation'];  ?></span></div>
                                                <div>
                                                    <!-- <a target="new" href="https://www.google.com/maps/search/?api=1&query=<?= $vLatitudeSource ?>, <?= $vLongitudeSource; ?>"><?= $langage_lbl['LBL_NAVIGATE'] ?></a> -->
                                                    <a class="gen-btn" target="new" href="https://www.google.com/maps/dir/<?= $vLatitudeSource.','.$vLongitudeSource.'/'.$tDestinationLatitude.','.$tDestinationLongitude ?>" style="margin: 15px 0 0 0;"><?= $langage_lbl['LBL_NAVIGATE'] ?></a>
                                                </div>
                                                
                                             </div>
                                             <? } ?>
                                       <? } ?>
                                    </div>
                                <?php } ?>
                                
                                <?php if(isDeliveryPreferenceEnable() && $selectedPrefIds != "") { ?>
                                    <div class="location-time" style="padding: 20px">
                                        <h3 class="delivery-pref-title"><?= $langage_lbl['LBL_DELIVERY_PREF']; ?></h3>
                                        <ul class="delivery-pref-list">
                                            <?php foreach ($deliveryPrefSqlData as $delivery_pref) { ?>
                                            <li>
                                                <label>
                                                    <?= $delivery_pref['tTitle'] ?>
                                                    <?php if($delivery_pref['eContactLess'] == 'Yes') { ?>
                                                    <span data-toggle="modal" data-target="#contactless_pref_modal" title="How it works?"><i class="icon-question-sign"></i></span>
                                                    <?php } ?>
                                                </label>
                                                <div><?= $delivery_pref['tDescription'] ?></div>
                                                <?php if($value->iStatusCode == 6) { ?>
                                                <?php if($delivery_pref['eContactLess'] == 'Yes' && $user_order_sql_data[0]['vImageDeliveryPref'] != "") { ?>
                                                    <div class="trip-detail">
                                                        <a href="<?= $tconfig['tsite_upload_order_delivery_pref_images'].$user_order_sql_data[0]['vImageDeliveryPref']; ?>" target="_blank" style="float: left;">View Image</a>
                                                    </div>
                                                <?php } } ?>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                                
                                <div class="location-time">
                                    <ul>
                                        <li>
                                            <h3><?= $langage_lbl['LBL_ORDER_NO_TXT']; ?></h3>
                                            <?= $db_order_data['vOrderNo']; ?>
                                        </li>

                                        <li>
                                            <h3><?= $langage_lbl['LBL_ORDER_STATUS_TXT']; ?></h3>
                                            <?= $db_order_data['vStatus']; ?> 
                                        </li>
                                        <li>
                                            <h3><?= $langage_lbl['LBL_PAID_BY_TXT']; ?></h3><?= $paymentType; ?>
                                        </li>
                                    </ul>
                                </div>
                                <?php
                                if ($_SESSION['sess_user'] != 'driver') {
                                    if ($db_order_data['DriverName'] != '') {
                                        ?> 
                                        <div class="location-time">
                                            <div style="padding: 10px;text-align: center;">
                                                <b><?= $langage_lbl['LBL_ORDER_DELIVERED_BY_DRIVER']; ?> : <?= $db_order_data['DriverName']; ?>
                                                    <?php if (!empty($getratings['DriverRate'])) { ?>
                                                        (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>) 
                                                    <?php } ?>
                                                </b>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <div class="clearfix">
                                <div class="map-address map-address-new-work">
                                    <ul>
                                        <li> 
                                            <b><i aria-hidden="true" class="fa fa-map-marker fa-22 green-location"></i></b>
                                            <span>
                                                <h3><?= @date('h:i A', @strtotime($db_order_data['OrderRequestDatenew'])); ?></h3>
                                                <p>
                                                    <?= $db_order_data['CompanyName'] ?>
                                                    <?php if (!empty($getratings['CompanyRate'])) { ?>
                                                        (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate'] ?>) 
                                                    <?php } ?>
                                                </p>
                                                <?= $db_order_data['vRestuarantLocation']; ?>
                                            </span> 
                                        </li>
                                        <li> 
                                            <b><i aria-hidden="true" class="fa fa-map-marker fa-22 red-location"></i></b> 
                                            <span>
                                                <h3><?= @date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?></h3>
                                                <p>
                                                    <?= $db_order_data['UserName'] ?>
                                                    <?php if (!empty($getratings['UserRate'])) { ?>
                                                        (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>) 
                                                    <?php } ?>
                                                </p>
                                                <?= $db_order_data['DeliveryAddress']; ?>
                                            </span> 
                                        </li>
                                    </ul>
                                </div>

                                <?php if ($_SESSION['sess_user'] == 'driver') { ?>
                                    <div class="driver-info new-driver" style="height: auto;">
                                        <h3 style=" margin: 30px 0;"> <?= $langage_lbl['LBL_DELIVERY_EARNING_FRONT']; ?></h3>
                                    </div>
                                    <div class="fare-breakdown">
                                        <div class="fare-breakdown-inner">
                                            <ul>
                                                <?php foreach ($db_order_data['History_Arr'] as $key => $value) { ?>
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
                                    <?php if ($db_order_data['DriverName'] != '') { ?> 
                                        <div class="location-time" style="border: 1px solid #dfdfdf">
                                            <div style="padding: 10px;">
                                                <b><?= $langage_lbl['LBL_ORDER_DELIVERED_BY_DRIVER']; ?> : <?= $db_order_data['DriverName']; ?>
                                                    <?php if (!empty($getratings['DriverRate'])) { ?>
                                                        (<img src="<?= $siteUrl; ?>assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>) 
                                                    <?php } ?>
                                                </b>
                                            </div>
                                        </div>
                                    <?php } ?>

                                <?php } else if ($_SESSION['sess_user'] == 'rider') { ?>
                                    <div class="driver-info new-driver" style="height: auto;">
                                        <h3 style=" margin: 15px 0;"> <?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></h3>
                                    </div>
                                    <div class="fare-breakdown new-fare">
                                        <div class="fare-breakdown-inner">
                                            <?php $db_menu_item_list = $db_order_data['itemlist']; ?>
                                            <h3><?= $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></h3>
                                            <?php if (!empty($db_menu_item_list)) {
					//added by SP for rounding off currency wise on 26-8-2019 start
                                                        $roundoff = 0;
					      
                                                        if (array_key_exists($langage_lbl['LBL_ROUNDING_DIFF_TXT'], $db_order_data['History_Arr_first']) && !empty($db_order_data['History_Arr_first'][$langage_lbl['LBL_ROUNDING_DIFF_TXT']])) {
                                                            $roundoff = 1;
                                                        }
					      //added by SP for rounding off currency wise on 26-8-2019 end
					?>
                                                <ul class="order-items">
                                                    <?php foreach ($db_menu_item_list as $key => $val) { ?>
                                                        <li>
                                                            <strong style="background:# !important;"><?= $val['MenuItem']; ?> X <?= $val['iQty']; ?></strong>
                                                            <?php if (trim($val['SubTitle'], "/") != '') { ?>
                                                                <br/><br/><strong style="font-size: 10px;">(<?= trim($val['SubTitle'], "/"); ?>)</strong>
                                                            <?php } ?>
                                                            <b><?= $val['fTotPrice'] ?></b>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                                <span></span>
                                            <?php } ?>
                                            <ul>
                                                <?php
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
                                                <?php
                                                foreach ($db_order_data['History_Arr_first'] as $key => $value) {
                                                    if ($key == $langage_lbl['LBL_TOTAL_BILL_AMOUNT_TXT']) {
						?>
						
							<?= ($roundoff==1) ? $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT']] : $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_SUBTOTAL_TXT']]; ?>
						
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
                                    <?php } else { ?>
                                        <div class="driver-info new-driver" style="height: auto;">
                                            <h3 style=" margin: 30px 0;"> <?= $langage_lbl['LBL_ORDER_DETAIL_TXT']; ?></h3>
                                        </div>
                                        <div class="fare-breakdown new-fare">
                                            <div class="fare-breakdown-inner">
                                                <?php $db_menu_item_list = $db_order_data['itemlist']; ?>
                                                <h3><?= $langage_lbl['LBL_TOTAL_ITEM_TXT']; ?> : <b><?= $db_order_data['TotalItems']; ?></b></h3>
                                                <?php if (!empty($db_menu_item_list)) { ?>
                                                    <ul class="order-items">
                                                        <?php foreach ($db_menu_item_list as $key => $val) { ?>
                                                            <li>
                                                                <strong><?= $val['MenuItem']; ?> X <?= $val['iQty']; ?><br/>
                                                                    <?php if ($val['SubTitle'] != '') { ?>
                                                                        <small style="font-size: 10px;">(<?= $val['SubTitle']; ?>)</small>
                                                                    <?php } ?>
                                                                </strong>
                                                                <b><?= $val['fTotPrice'] ?></b>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                    <span></span>
                                                <?php } ?>
                                                <ul>
                                                    <?php
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
                                                    <?php
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
                                                    <?php foreach ($db_order_data['History_Arr_second'] as $key => $value) { ?>
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
                                                        <?php if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?= $db_order_data['vOrderAdjusmentId'] ?>)
                                                        <?php } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                            ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"] ?> )
                                                        <?php } else if ($db_order_data['ePaymentOption'] == 'Card') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"] ?> )
                                                        <?php } ?>
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
                                                        <?php if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?= $db_order_data['vOrderAdjusmentId'] ?>)
                                                        <?php } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                            ( <?= $langage_lbl["LBL_UNPAID_WEB_TXT"] ?> )
                                                        <?php } else if ($db_order_data['ePaymentOption'] == 'Card') { ?>
                                                            ( <?= $langage_lbl["LBL_PAID_BY_CARD_WEB_TXT"] ?> )
                                                        <?php } ?>
                                                    </div>
                                                    <div> <?= $langage_lbl["LBL_REFUND_WEB_TXT"] ?> : <?= $db_order_data['RefundAmount']; ?></div>
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
        <div class="product-model-overlay small-model" id="call-info-model">
            <div class="product-model payment-block-row">
                <form class="add-new-card-data" name="frmcreditcard" id="frmcreditcard" onSubmit="return false;" novalidate="novalidate">
                    <div class="close-icon">
                        <svg width="16" height="16" viewBox="0 0 14 14"><path fill="#fff" d="M7 5.586L2.05.636.636 2.05 5.586 7l-4.95 4.95 1.414 1.414L7 8.414l4.95 4.95 1.414-1.414L8.414 7l4.95-4.95L11.95.636 7 5.586z"></path></svg>
                    </div>
                    <h4 id="drverName"></h4>  
                    <h4 id="phnnumber"><?= $langage_lbl['LBL_NOT_FOUND']; ?></h4>  
                </form>
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
                        if (phone != "") {
                            $("#phnnumber").text("+" + phoneCode + " " + phone);
                        }
                        $("#drverName").text('<?= $langage_lbl['LBL_PHONE']; ?>(' + driverName + ')');
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
						 var directionsService = new google.maps.DirectionsService(); // For Route Services on map
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
							var source_latitude = fromlat;
							var source_longitude = fromlong;
							var dest_latitude = tolat;
							var dest_longitude = tolong;
							var waypoint0 = newFrom;
							var waypoint1 = newTo;

							getReverseGeoDirectionCode(source_latitude,source_longitude,dest_latitude,dest_longitude,waypoint0,waypoint1,function(data_response){
								 if (MAPS_API_REPLACEMENT_STRATEGY.toUpperCase() == 'NONE'){
									$("#distance").val(data_response.routes[0].legs[0].distance.value);
									$("#duration").val(data_response.routes[0].legs[0].duration.value);
									var points = data_response.routes[0].overview_polyline.points;
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
                                    // // alert(dist_fare);
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
                                        url: 'ajax_estimate_by_vehicle_type.php',
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
<?php if ($iVehicleTypeId != "") { ?>
                                var iVehicleTypeId = '<?= $iVehicleTypeId ?>';
                                getFarevalues(iVehicleTypeId);
                                showAsVehicleType(iVehicleTypeId);
<?php } ?>

                        }
                    }
                    $('.map-page').show();
					var latdrvSource = '<?= $vLatitudeSource; ?>';
                    var longdrvSource = '<?= $vLongitudeSource; ?>';
                    var tEndLat1 = '<?= $tDestinationLatitude; ?>';
                    var tEndLong1 = '<?= $tDestinationLongitude; ?>';
                    var latdrv = '<?= $vLatitude; ?>';
                    var longdrv = '<?= $vLongitude; ?>';
                    
                    console.log(latdrv + 'aaaaaa' + longdrv);
                    console.log(tEndLat1 + 'bbbbb' + tEndLong1);
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
