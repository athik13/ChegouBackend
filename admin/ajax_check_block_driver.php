<?php
//added by SP for popup showing on block driver start
include_once('../common.php');

$iDriverId = isset($_REQUEST['driverId']) ? $_REQUEST['driverId'] : '';
$eIsBlocked = "No";

if($PACKAGE_TYPE == 'SHARK' && ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Delivery" || $APP_TYPE == "Ride-Delivery-UberX")) {
    $sql_blocked = "SELECT eIsBlocked FROM `register_driver` WHERE iDriverId ='" . $iDriverId . "'";
    $db_blocked = $obj->MySQLSelect($sql_blocked);
    $eIsBlocked = $db_blocked[0]['eIsBlocked'];
}
echo $eIsBlocked;
exit;
//added by SP for popup showing on block driver start
?>
