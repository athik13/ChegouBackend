<script src="https://cdn.pubnub.com/sdk/javascript/pubnub.4.4.3.js"></script>
<?php
require_once('assets/libraries/pubnub/autoloader.php');
$vLatitude = "23.010931";
$vLongitude = "72.510014";
$driverId = 41;
if (isset($_REQUEST['driverId']) && $_REQUEST['driverId'] > 0) {
    $driverId = $_REQUEST['driverId'];
}
if (isset($_REQUEST['vLatitude']) && $_REQUEST['vLatitude'] != '') {
    $vLatitude = $_REQUEST['vLatitude'];
}
if (isset($_REQUEST['vLongitude']) && $_REQUEST['vLongitude'] != '') {
    $vLongitude = $_REQUEST['vLongitude'];
}
// $pubnub = new Pubnub\Pubnub(array("publish_key" => "pub-c-4b576e8a-2726-4e0b-901b-d4b8e3217f42","subscribe_key" => "sub-c-eb8cf22c-96ed-11e7-ae90-9e4639429209", "uuid" => "789789789789789"));
//$pubnub = new Pubnub\Pubnub(array("publish_key" => "pub-c-7ee299c4-85f5-4cc7-a30a-6e70afad816f","subscribe_key" => "sub-c-f8037534-d8e2-11e8-8de9-32e579725a69", "uuid" => "874564788d5d74252"));
$pubnub = new Pubnub\Pubnub(array("publish_key" => "pub-c-1a2ffc67-6517-44aa-b61c-694518042f74", "subscribe_key" => "sub-c-c2fa69b8-b7d5-11e6-b737-0619f8945a4f", "uuid" => "fg5k3i7i7l5ghgk1jcv43w0j41"));

// $info = $pubnub->publish('ONLINE_DRIVER_LOC_38', 'Hey World!');

$message_arr = array();
$message_arr['MsgType'] = "LocationUpdateOnTrip";
$message_arr['iDriverId'] = $driverId;
$message_arr['vLatitude'] = $vLatitude;
$message_arr['vLongitude'] = $vLongitude;
$message_arr['ChannelName'] = "ONLINE_DRIVER_LOC_" . $driverId;

$message = json_encode($message_arr);
$info = $pubnub->publish('ONLINE_DRIVER_LOC_' . $driverId, $message);

// $history = $pubnub->history('ONLINE_DRIVER_LOC_156',100);
// echo "History==<BR><PRE/>";
// print_r($history);
print_r($info);
?>
