<?php
//echo "dsddsd"; exit;
include_once('common.php');
include_once('generalFunctions.php');
//For Set Trip Tracking Old Method
//1) Simple Refresh - Enable TRIP_TRACKING_METHOD Simple Refresh From Configuration Then Set PUBSUB_TECHNIQUE = None
//2) Pubnub - Enable TRIP_TRACKING_METHOD Pubnub From Configuration Then Set PUBSUB_TECHNIQUE = PubNub
//3) SocketCluster - Enable TRIP_TRACKING_METHOD SocketCluster From Configuration Then Set PUBSUB_TECHNIQUE = SocketCluster
//For Set Trip Tracking New Method As Per Discuss With KS Sir On 12-01-2019
//1) Simple Refresh - Don't Set TRIP_TRACKING_METHOD Of Configuration But Set PUBSUB_TECHNIQUE = None
//2) Pubnub - Don't Set TRIP_TRACKING_METHOD Of Configuration But Set PUBSUB_TECHNIQUE = PubNub
//3) SocketCluster - Don't Set TRIP_TRACKING_METHOD Of Configuration But Set PUBSUB_TECHNIQUE = SocketCluster
//4) Yalgaar - Don't Set TRIP_TRACKING_METHOD Of Configuration But Set PUBSUB_TECHNIQUE = Yalgaar
$iTripId = isset($_REQUEST['iTripId']) ? base64_decode(base64_decode($_REQUEST['iTripId'])) : '';
$iTripDeliveryLocationId = isset($_REQUEST['Del_loc_id']) ? base64_decode(base64_decode($_REQUEST['Del_loc_id'])) : '';
$getConfig = $obj->MySQLSelect("SELECT vName,vValue FROM configurations WHERE vName='PUBSUB_TECHNIQUE' OR vName='YALGAAR_CLIENT_KEY'");
$PUBSUB_TECHNIQUE = "None";
$YALGAAR_CLIENT_KEY = "";
for ($r = 0; $r < count($getConfig); $r++) {
    if (isset($getConfig[$r]['vName']) && $getConfig[$r]['vName'] == "PUBSUB_TECHNIQUE") {
        $PUBSUB_TECHNIQUE = $getConfig[$r]['vValue'];
    }
    if (isset($getConfig[$r]['vName']) && $getConfig[$r]['vName'] == "YALGAAR_CLIENT_KEY") {
        $YALGAAR_CLIENT_KEY = $getConfig[$r]['vValue'];
    }
}
$driverName = $riderName = $phone = $avgRating = $vehicle_number = $starHtml = $vMake = $vTitle = $vehicle_modal = $ssql = "";
$tsite_sc_host = $tconfig['tsite_sc_host'];
$tsite_host_sc_port = $tconfig['tsite_host_sc_port'];
$sql = "select tdl.iTripDeliveryLocationId,tdl.ePaymentBy,tdl.ePaymentByReceiver,tdl.tStartTime,tdl.tEndTime,tr.iDriverId,tr.iActive from trips_delivery_locations tdl left join trips tr on tr.iTripId = tdl.iTripId where tr.iTripId='$iTripId' ";
$data_all_details = $obj->MySQLSelect($sql);
// echo "<pre>";print_r($data_all_details); exit;
$sql = "select tdl.*,concat(rd.vName,' ',rd.vLastName) as DriverName,rd.vCode,rd.vPhone,rd.iDriverVehicleId,rd.vImage as DriverImg,rd.vAvgRating as DRate,concat(ru.vName,' ',ru.vLastName) as SenderName,ru.vImgName as SenderImg,ru.vAvgRating as SRate,tr.vRideNo,tr.iFare,tr.iUserId,tr.iDriverId,tr.fPricePerKM,tr.iBaseFare,tr.fPricePerMin,tr.fCommision,tr.fDistance,tr.fDuration,tr.fPickUpPrice,tr. fNightPrice,tr.vTripPaymentMode,tr.vCouponCode,tr.vDiscount,tr.fDiscount,tr.fTripGenerateFare,tr.fTax1,tr.fTax2 from trips_delivery_locations tdl left join trips tr on tr.iTripId = tdl.iTripId left join register_driver rd on tr.iDriverId = rd.iDriverId left join register_user ru on tr.iUserId = ru.iUserId where tr.iTripId='$iTripId' $ssql "; //exit;
$data_delivery = $obj->MySQLSelect($sql);
#####################images and rating of driver and sender#####################
$Driver_image = "assets/img/profile-user-img.png";
if ($data_delivery[0]['DriverImg'] != "" && file_exists($tconfig['tsite_upload_images_driver_path'] . "/" . $data_delivery[0]['iDriverId'] . "/" . $data_delivery[0]['DriverImg'])) {
    $Driver_image = $tconfig['tsite_upload_images_driver'] . "/" . $data_delivery[0]['iDriverId'] . "/2_" . $data_delivery[0]['DriverImg'];
}
$halfStart = $avgRating = $vehicleId = 0;
if (isset($data_delivery[0]['DRate']) && $data_delivery[0]['DRate'] > 0) {
    $avgRating = $data_delivery[0]['DRate'];
}
$starRate = $starLoop = floor($avgRating);
$starHtml = "";
if ($avgRating > $starRate) {
    $halfStart = 1;
    $starLoop += $halfStart;
}
$offStart = 5 - $starLoop;
for ($s = 0; $s < $starRate; $s++) {
    $starHtml .= '<img src="assets/img/star-on-big.png">';
}
if ($halfStart > 0) {
    $starHtml .= '<img src="assets/img/star-half-big.png">';
}
for ($d = 0; $d < $offStart; $d++) {
    $starHtml .= '<img src="assets/img/star-off-big.png">';
}
$driverName = $riderName = "";
if (isset($data_delivery[0]['DriverName']) && trim($data_delivery[0]['DriverName']) != "") {
    $driverName = $data_delivery[0]['DriverName'];
}
if (isset($data_delivery[0]['SenderName']) && trim($data_delivery[0]['SenderName']) != "") {
    $riderName = $data_delivery[0]['SenderName'];
}
//echo "<pre>";print_r($data_delivery);exit;
if (isset($data_delivery[0]['vPhone']) && $data_delivery[0]['vPhone'] != "") {
    $phone = "+" . $data_delivery[0]['vCode'] . "-" . $data_delivery[0]['vPhone'];
}
if (isset($data_delivery[0]['iDriverVehicleId']) && $data_delivery[0]['iDriverVehicleId'] > 0) {
    $vehicleId = $data_delivery[0]['iDriverVehicleId'];
}
if ($vehicleId > 0) {
    $get_vehicle_data = $obj->MySQLSelect("SELECT iYear,iMakeId,iModelId,vLicencePlate FROM driver_vehicle WHERE iDriverVehicleId='" . $vehicleId . "'");
    if (count($get_vehicle_data) > 0) {
        $iMakeId = $get_vehicle_data[0]['iMakeId'];
        $iModelId = $get_vehicle_data[0]['iModelId'];
        $vehicle_number = $get_vehicle_data[0]['vLicencePlate'];
        $get_make_data = $obj->MySQLSelect("SELECT vMake FROM make WHERE iMakeId='" . $iMakeId . "'");
        if (count($get_make_data) > 0) {
            $vMake = $get_make_data[0]['vMake'];
            $vehicle_modal = $vMake;
        }
        $get_model_data = $obj->MySQLSelect("SELECT vTitle FROM model WHERE iModelId='" . $iModelId . "'");
        if (count($get_model_data) > 0) {
            $vTitle = $get_model_data[0]['vTitle'];
            if ($vehicle_modal == "") {
                $vehicle_modal = $vTitle;
            } else {
                $vehicle_modal .= " " . $vTitle;
            }
        }
    }
}
//echo $driverName;die;
//echo "<pre>";print_r($data_delivery);exit;
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_TRIP_TRACKING']; ?></title>
        <?php include_once("top/top_script.php"); ?>
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
    </head>
    <body>
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <link href="assets/css/checkbox.css" rel="stylesheet" type="text/css" />
            <link href="assets/css/radio.css" rel="stylesheet" type="text/css" />
            <?php
            include_once("top/validation.php");
            include_once("top/header_topbar.php");
            ?>
            <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
            <style type="text/css">
                .page-contant{background:none}
                tr:nth-child(odd) {background:#CCC}
                tr td{padding:5px!important}
            </style>
            <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant page-contant trip-tracking-main">
                <div class="page-contant-inner trip-tracking">	
                    <h2 class="header-page add-car-vehicle">
                        <?php if ($riderName == "") { ?>
                            <?= $langage_lbl['LBL_RECIPIENT']; ?> <?= $langage_lbl['LBL_NOT_FOUND']; ?>
                        <?php } else {
                            ?>
                            <?= $riderName; ?>'s <?= $langage_lbl['LBL_ORDER']; ?>
                        <?php } if (isset($db_dtrip[0]['iActive']) && $db_dtrip[0]['iActive'] == 'Active' || isset($db_dtrip[0]['iActive']) && $db_dtrip[0]['iActive'] == 'On Going Trip') { ?>
                            <!--<font class="trip-start"><?= $langage_lbl['LBL_MY_ONGOING_TRIPS_HEADER_TXT']; ?></font>-->
                        <?php } ?>
                    </h2>
                    <?php if ($data_all_details[0]['iActive'] == 'Active' || $data_all_details[0]['iActive'] == 'On Going Trip') { ?>
                        <div class="map-page" style="display:none;">
                            <div class="panel-heading location-heading">
                                <b><i class="icon-map-marker"></i>
                                    <?= $langage_lbl['LBL_LOCATIONS_TXT']; ?> </b>

                                <? if ($data_all_details[0]['ePaymentBy'] != "Sender") { ?><em><button type="button" class="btn btn-success" onClick="show_slider()"><?= $langage_lbl['LBL_VIEW_DELIVERY_DETAILS'] ?></button></em><? } ?>
                            </div>
                            <div class="panel-heading location-map" style="background:none;" >
                                <div class="google-map-wrap" >
                                    <!--<div id="google-map" class="google-map">-->
                                    <div class="gmap-div gmap-div1">
                                        <div id="map-canvas" class="gmap3 google-map" style="height:500px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="trip-track-cub">
                            <div class="trip-track-cub-left">
                                <div class="trip-track-cub-left-l">
                                    <img src="assets/img/car-img.png">
                                </div>
                                <div class="trip-track-cub-left-l-sec">
                                    <div class="trip-track-cub-left-l-ab"><h2><?= $vehicle_modal; ?></h2></div>
                                    <div class="trip-track-cub-left-l-bb"><b><?= $vehicle_number; ?></b></div>
                                </div>
                            </div>
                            <div class="trip-track-cub-right"> 
                                <div class="trip-track-lrft-text">
                                    <b><?= $driverName; ?></b>
                                    <a href="javascript:void(0);">
                                        <?= $starHtml; ?>
                                    </a>
                                    <span><?= $phone; ?></span>
                                </div>
                                <div class="trip-track-right-img">
                                    <div class="driver-profile-img">
                                        <a href="javascript:void(0);">
                                            <img src="<?= $Driver_image; ?>" title="<?= $driverName; ?>" alt="<?= $driverName; ?>">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                    <?php } else if ($data_all_details[0]['iActive'] == 'Finished') { ?>
                        <br><br><br>
                        <div class="row">
                            <div class="alert alert-danger paddiing-10">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $langage_lbl['LBL_TRIP_IS_FINISHED']; ?>.
                            </div>
                        </div>
                    <?php } else if ($data_all_details[0]['iActive'] == 'Canceled') { ?> 
                        <br><br><br>
                        <div class="row">
                            <div class="alert alert-danger paddiing-10">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $langage_lbl['LBL_TRIP_IS_CANCELLED']; ?>.
                            </div>
                        </div>
                    <?php } ?>
                    <div style="clear:both;"></div>    
                </div>				
                <div  class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                    <div class="modal-dialog modal-lg" style="width:70%">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>
                                    <i><img src="assets/img/delivery.png" alt="" width="30px"></i>
                                    <b><?= $langage_lbl['LBL_DELIVERY_DETAILS'] ?></b>
                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                </h3>
                            </div>
                            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                                <div id="imageIcons" style="display:none">
                                    <div align="center">                                                                       
                                        <img src="admin/default.gif"><br/>                                                            
                                        <span>Retrieving details,please Wait...</span>                       
                                    </div>    
                                </div>
                                <div id="driver_detail"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- End:contact page -->
    <?php include_once('top/footer_script.php'); ?>
    <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/gmap3.js"></script>
    <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/jquery_easing.js"></script>
    <script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/markerAnimate.js"></script>
    <script>
                                    var iTripId = '<?php echo $iTripId; ?>';
                                    var latlng;
                                    var locallat;
                                    var locallang;
                                    var map;
                                    var interval3;
                                    var marker = [];
                                    var myOptions = [];
                                    function moveToLocation(lat, lng) {
                                        var center = new google.maps.LatLng(lat, lng);
                                        // using global variable:
                                        map.panTo(center);
                                    }
                                    function handleResponse(response) {
                                        //var response = JSON.parse(response.message);
                                        //var response = response.message;
                                        //console.log(response);
                                        if (response.vLatitude != "" && response.vLongitude != "") {
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
                                                //marker.setAnimation(null)
                                                marker.animateTo(latlng, {easing: 'linear', duration: duration});
                                                map.panTo(latlng); // For Move Google Map By Animate
                                            }, 2000);
                                            //map.setCenter(latlng); // For Set Center Location of Google Map Marker
                                            //changeMarker(90);
                                        }
                                    }
                                    function changeMarker(deg) {
                                        //var deg = 90
                                        //document.getElementById("#markerLayer img").style.transform = 'rotate(' + deg + 'deg)';
                                        //document.querySelector('#markerLayer img').style.transform = 'rotate(' + deg + 'deg)'
                                        google.maps.event.clearListeners(map, 'idle');
                                    }
                                    function initialize() {
                                        directionsService2 = new google.maps.DirectionsService();
                                        directionsDisplay2 = new google.maps.DirectionsRenderer();
                                        $.ajax({
                                            type: "POST",
                                            url: "ajax_getdirver_detail.php",
                                            dataType: "json",
                                            data: {iTripId: iTripId},
                                            success: function (driverdetail) {
                                                if (driverdetail != 1) {
                                                    $('.map-page').show();
                                                    var latdrv = driverdetail.vLatitude;
                                                    var longdrv = driverdetail.vLongitude;
                                                    latlng = new google.maps.LatLng(latdrv, longdrv);
                                                    locallat = new google.maps.LatLng(driverdetail.tStartLat, driverdetail.tStartLong);
                                                    locallang = new google.maps.LatLng(driverdetail.tEndLat, driverdetail.tEndLong);
                                                    fromLatlongs = driverdetail.tStartLat + ", " + driverdetail.tStartLong;
                                                    toLatlongs = driverdetail.tEndLat + ", " + driverdetail.tEndLong;
                                                    //toLatlongs = '23.0146207'+", "+'72.5284118';
                                                    myOptions = {
                                                        zoom: 16,
                                                        center: latlng,
                                                    }
                                                    map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
                                                    var overlay = new google.maps.OverlayView()
                                                    overlay.draw = function () {
                                                        this.getPanes().markerLayer.id = 'markerLayer'
                                                    }
                                                    marker = new google.maps.Marker({
                                                        position: latlng,
                                                        map: map,
                                                        //animation:google.maps.Animation.BOUNCE,
                                                        //icon: "webimages/upload/mapmarker/car_driver.png",
                                                        icon: {
                                                            url: 'webimages/upload/mapmarker/source_marker.png',
                                                            // This marker is 20 pixels wide by 32 pixels high.
                                                            scaledSize: new google.maps.Size(50, 50),
                                                            rotation: 90
                                                        },
                                                        id: 'marker'
                                                    });
                                                } else {
                                                    $('.map-page').hide();
                                                }
                                            }
                                        });
                                    }
                                    google.maps.event.addDomListener(window, 'load', initialize);
    </script>
    <?php if ($PUBSUB_TECHNIQUE == "None") { ?>
        <script>
            interval3 = setInterval(function () {
                $.ajax({
                    type: "POST",
                    url: "ajax_getdirver_detail.php",
                    dataType: "json",
                    data: {iTripId: iTripId},
                    success: function (driverdetail) {
                        //marker.setMap(null);	
                        if (driverdetail != 1) {
                            $('.map-page').show();
                            var latdrv = driverdetail.vLatitude;
                            var longdrv = driverdetail.vLongitude;
                            latlng = new google.maps.LatLng(latdrv, longdrv);
                            locallat = new google.maps.LatLng(driverdetail.tStartLat, driverdetail.tStartLong);
                            locallang = new google.maps.LatLng(driverdetail.tEndLat, driverdetail.tEndLong);
                            /*marker.setMap(null);
                             marker = new google.maps.Marker({
                             position: latlng,
                             map: map,
                             icon: "webimages/upload/mapmarker/car_driver.png"
                             });*/
                        } else {
                            $('.map-page').hide();
                            clearInterval(interval3);
                            alert('No Online Vehicle');
                        }
                    }
                });
            }, 30000);
        </script>
    <?php } else if ($PUBSUB_TECHNIQUE == "PubNub") { ?>
        <!--PubNub Publish Data File Name pubnubconfig.php In Root Directory BY HJ On 12-01-2019 -->
        <script src="https://cdn.pubnub.com/sdk/javascript/pubnub.4.21.6.js"></script>
        <script>
            (function () {
                var publishKey = '<?php echo $PUBNUB_PUBLISH_KEY; ?>';
                var subscribeKey = '<?php echo $PUBNUB_SUBSCRIBE_KEY; ?>';
                var uuid = '<?php echo $PUBNUB_UUID; ?>';
                //var pubnub = new PUBNUB({publish_key: publishKey, subscribe_key: subscribeKey, uuid: uuid});
                var pubnub = new PubNub({subscribeKey: subscribeKey, publishKey: publishKey, uuid: uuid});
                var channel = 'ONLINE_DRIVER_LOC_<?php echo $iDriverId; ?>';
                //alert(channel);
                pubnub.addListener({
                    status: function (statusEvent) {

                    },
                    message: function (message) {
                        // handle message
                        var response = JSON.parse(message.message);
                        handleResponse(response);
                    },
                    presence: function (presenceEvent) {
                        // handle presence
                    }
                });
                pubnub.subscribe({
                    channels: [channel],
                });
            })();
        </script>
    <?php } else if ($PUBSUB_TECHNIQUE == "SocketCluster") {
        ?>
        <!--Socket Cluster Publish Data File Name socket_cluster.php In Root Directory BY HJ On 12-01-2019 -->
        <script type="text/javascript" src="<?= $tconfig["tsite_url"]; ?>assets/libraries/socketcluster-client-master/socketcluster.js"></script>
        <script>
            var options = {
                hostname: '<?= $tsite_sc_host; ?>',
                secure: false,
                port: '<?= $tsite_host_sc_port; ?>',
                rejectUnauthorized: false // Only necessary during debug if using a self-signed certificate
            };
            var socket = socketCluster.create(options);
            var channel = 'ONLINE_DRIVER_LOC_<?php echo $iDriverId; ?>';
            socket.on('connect', function (status) {
                var channelObject = socket.subscribe(channel);
                channelObject.watch(function (handlerFn) {
                    //console.log(handlerFn);
                    var response = JSON.parse(handlerFn);
                    console.log("Socket Cluster Message Found");
                    handleResponse(response);
                });
            });
        </script>
    <?php } else if ($PUBSUB_TECHNIQUE == "Yalgaar") { ?>
        <!--Yalgaar Publish Data File Name yalgaar.php In Root Directory BY HJ On 12-01-2019-->
        <script type="text/javascript" src="<?= $tconfig["tsite_url"]; ?>assets/libraries/Yalgaar/yalgaar.js"></script>
        <script type="text/javascript">
            connectToYalgaar({
                ClientKey: '<?= $YALGAAR_CLIENT_KEY; ?>',
                SSL: false,
                Callback: function (acknowledgement) {

                },
                Error: function (err) {
                }
            });
            SubscribeMessage({
                ChannelName: 'ONLINE_DRIVER_LOC_<?php echo $iDriverId; ?>',
                Callback: function (message, channel, acknowledgement) {
                    // Shows alert when message receive
                    alert(message);
                }
            });
        </script>
    <?php } ?>
</body>
<!-- END BODY-->
</html>