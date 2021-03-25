<?php
include_once('common.php');
$message_arr = array();
$vLatitude = "23.010931";
$vLongitude = "72.510014";
$driverId = 1;
if (isset($_REQUEST['vLatitude']) && $_REQUEST['vLatitude'] != '') {
    $vLatitude = $_REQUEST['vLatitude'];
}
if (isset($_REQUEST['vLongitude']) && $_REQUEST['vLongitude'] != '') {
    $vLongitude = $_REQUEST['vLongitude'];
}
if (isset($_REQUEST['driverId']) && $_REQUEST['driverId'] > 0) {
    $driverId = $_REQUEST['driverId'];
}
$message_arr['MsgType'] = "LocationUpdateOnTrip";
$message_arr['iDriverId'] = $driverId;
$message_arr['vLatitude'] = $vLatitude;
$message_arr['vLongitude'] = $vLongitude;
$channel = "ONLINE_DRIVER_LOC_" . $driverId;
$message_arr['ChannelName'] = $channel;
$message = json_encode($message_arr);
//print_R($message);die;
//http://192.168.1.131/cubejekdev/socket_cluster.php?vLatitude=23.01228587052134&vLongitude=72.50292515388922
?>
<script type='text/javascript' src='../cubejekdev/assets/js/jquery.min.js'></script>
<script type="text/javascript" src="<?= $tconfig["tsite_url"]; ?>assets/libraries/Yalgaar/yalgaar.js"></script>
<script>
    var messageData = {
        vLatitude: '<?= $vLatitude; ?>',
        vLongitude: '<?= $vLongitude; ?>',
        ChannelName: '<?= $channel; ?>'
    }
    ConnectToYalgaar({
        ClientKey: 'ck-9359b0b9ffb342df',
        SSL: false,
        Callback: function (acknowledgement) {
            PublishMessage({
                ChannelName: '<?php echo $channel; ?>',
                Message: 'This is Yalgaar Pub/Sub Example',
                Callback: function (acknowledgment) {
                }
            });
        },
        Error: function (err) {
        }
    });
</script>
