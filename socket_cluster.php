<?php
include_once('common.php');
$message_arr = array();
$vLatitude = "23.010931";
$vLongitude = "72.510014";
$driverId = 41;
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
//http://192.168.1.131/cubejekdev/socket_cluster.php?vLatitude=23.010931&vLongitude=72.510014
?>
<script type='text/javascript' src='../cubejekdev/assets/js/jquery.min.js'></script>
<script type="text/javascript" src="<?= $tconfig["tsite_url"]; ?>assets/libraries/socketcluster-client-master/socketcluster.js"></script>
<script>
    $(document).ready(function () {
        var options = {
            hostname: '142.93.244.42',
            secure: false,
            port: 8000,
            rejectUnauthorized: false // Only necessary during debug if using a self-signed certificate
        };
        var socket = socketCluster.create(options);
        physChannel = '<?php echo $channel; ?>';
        var messageData = {
            vLatitude: '<?= $vLatitude; ?>',
            vLongitude: '<?= $vLongitude; ?>',
            ChannelName: '<?= $channel; ?>'
        }
        messageData1 = JSON.stringify(messageData);
        //var messageData = "Hello";
        console.log(messageData);
        socket.on('connect', function (status) {
            console.log('CONNECTED');
            console.log(physChannel);
            console.log(status);
            socket.publish(physChannel, messageData1);
            //(socket.subscribe(physChannel)).publish(messageData);
            //socket.publish('bob123', {from: 'alice456', message: 'Hi Bob!'});
        });
    });


</script>
