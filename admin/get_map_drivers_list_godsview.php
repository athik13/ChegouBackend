<?php

include_once('../common.php');
header('Content-type: text/html; charset=utf-8');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$intervalmins = INTERVAL_SECONDS; // Added By HJ On 13-03-2020 Which is Defined In configuration_variables.php 
function fetchtripstatustimeMAXinterval() {
    global $generalobjAdmin, $FETCH_TRIP_STATUS_TIME_INTERVAL;
    $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL);
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
    return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
}

$type = $_REQUEST['type'];
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$ssql = " AND rd.eStatus='Active'";
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : '';
$eLadiesRide = isset($_REQUEST['eLadiesRide']) ? $_REQUEST['eLadiesRide'] : '';
$eHandicaps = isset($_REQUEST['eHandicaps']) ? $_REQUEST['eHandicaps'] : '';
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';

$per_page = 10;
$pagecount = $page - 1;
$start_limit = $pagecount * $per_page;
$next_page = $page + 1;

if(empty($_REQUEST['page'])) {
if($eLadiesRide == 'Yes'){
	$ssql .= " AND (rd.eFemaleOnlyReqAccept = 'Yes' OR rd.eGender = 'Female')";
}
if ($eHandicaps == 'Yes') {
    $ssql .= " AND dv.eHandiCapAccessibility = 'Yes'";
}
if ($type != "") {
    if ($type == 'Available') {
        $ssql .= " AND rd.vAvailability = '" . $type . "' AND rd.vTripStatus != 'Active' AND rd.tLocationUpdateDate > '$str_date'";
    } else {
        $ssql .= " AND rd.vTripStatus = '" . $type . "' ";
    }
}


//$sql = "SELECT rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS fullname ,rd.vEmail,rd.iCompanyId, rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline, rd.vImage, rd.vCode, rd.vPhone, dv.vCarType,rd.tLocationUpdateDate FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' ".$ssql." LIMIT $start_limit,$per_page"; 
$sql = "SELECT rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS fullname ,rd.vEmail,rd.iCompanyId, rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline, rd.vImage, rd.vCode, rd.vPhone, dv.vCarType,rd.tLocationUpdateDate FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' ".$ssql; 
$db_records = $obj->MySQLSelect($sql);
/* echo "<pre>"; print_r($db_records); die; */
$markers = [];
foreach ($db_records as $key => $value) {
    $DriverId = $value['iDriverId'];
    $marker = [];
    $time = time();
    $last_online_time = strtotime($value['tLastOnline']);
    $time_difference = $time - $last_online_time;
    $vTripStatus = $value['vTripStatus'];
    //echo $value['fullname']."==".$vTripStatus."===".$value['vAvailability']."====".$value['tLocationUpdateDate']."====".$str_date.'<br>';
    if ($APP_TYPE == 'UberX') {
        //if($value['vAvailability'] == "Available") {
        if ($vTripStatus != 'Active' && $value['vAvailability'] == "Available" && $value['tLocationUpdateDate'] > $str_date) {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/male-green.png";
        } else if ($value['vAvailability'] == "Active") {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/male-red.png";
        } else if ($value['vAvailability'] == "Arrived") {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/male-blue.png";
        } else if ($value['vAvailability'] == "On Going Trip") {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/male-yellow.png";
        } else if ($value['vAvailability'] == "Not Available") {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/male-gray.png";
        } else {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/male-gray.png";
        }
    } else {
        //if($value['vAvailability'] == "Available") {
        if ($vTripStatus != 'Active' && $value['vAvailability'] == "Available" && $value['tLocationUpdateDate'] > $str_date) {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/available_pin.png";
        } else if ($value['vAvailability'] == "Active" || $vTripStatus == 'Active') {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/enroute_pin.png";
        } else if ($value['vAvailability'] == "Arrived" || $vTripStatus == 'Arrived') {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/reached_pin.png";
        } else if ($value['vAvailability'] == "On Going Trip" || $vTripStatus == 'On Going Trip') {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/started_pin.png";
        } else if ($value['vAvailability'] == "Not Available") {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/offline_pin.png";
        } else {
            $statusIcon = $tconfig["tsite_url"] . "webimages/upload/mapmarker/offline_pin.png";
        }
    }
    $location = array(
        'lat' => $value['vLatitude'],
        'lng' => $value['vLongitude'],
        'icon' => $statusIcon,
        //'image' => $value['vImageDriver'],
        'address' => $value['vServiceLoc'],
        'status' => $value['vAvailability'],
        'car_type' => $value['vCarType'],
    );
if ($value['vImage'] != 'NONE' && $value['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"]. '/' . $value['iDriverId'] . '/2_'.$value['vImage'])) { 
        $DriverImage = $tconfig["tsite_upload_images_driver"] . '/' . $value['iDriverId'] . '/2_' . $value['vImage'];
    } else {
        $DriverImage = $tconfig["tsite_url"] . "assets/img/profile-user-img.png";
    }
    if ($vTripStatus == 'Active') {
        $value['vAvailability'] = $vTripStatus;
    } else if ($vTripStatus == 'Arrived') {
        $value['vAvailability'] = $vTripStatus;
    } else if ($vTripStatus == 'On Going Trip') {
        $value['vAvailability'] = $vTripStatus;
    } else if ($vTripStatus != 'Active' && $value['vAvailability'] == "Available" && $value['tLocationUpdateDate'] > $str_date) {
        $value['vAvailability'] = "Available";
    } else {
        $value['vAvailability'] = "Not Available";
    }
    //if($value['vAvailability'] == "Available") {
    if ($vTripStatus != 'Active' && $value['vAvailability'] == "Available" && $value['tLocationUpdateDate'] > $str_date) {
        $statusIcon = "../assets/img/green-icon.png";
    } else if ($value['vAvailability'] == "Active") {
        $statusIcon = "../assets/img/red.png";
    } else if ($value['vAvailability'] == "On Going Trip") {
        $statusIcon = "../assets/img/yellow.png";
    } else if ($value['vAvailability'] == "Arrived") {
        $statusIcon = "../assets/img/blue.png";
    } else {
        $statusIcon = "../assets/img/offline-icon.png";
    }
    $marker['image'] = $DriverImage;
    $marker['id'] = $value['iDriverId'];
    $marker['status_icon'] = $statusIcon;
    $marker['fullname'] = mb_convert_encoding($generalobjAdmin->clearName(ucfirst($value['fullname'])), 'utf-8', 'auto');
    $marker['email'] = utf8_encode($generalobjAdmin->clearEmail($value['vEmail']));
    $marker['phone'] = utf8_encode($value['vCode'] . $generalobjAdmin->clearPhone($value['vPhone']));
    $marker['location'] = $location;
    $sql = "SELECT t.iTripId  FROM  register_driver d LEFT JOIN trips t  ON t.iDriverId = d.iDriverId WHERE t.iDriverId =" . $DriverId . " AND (t.iActive = 'Active' OR t.iActive = 'On Going Trip' OR t.iActive = 'Arrived') AND d.eStatus = 'Active' AND (d.vTripStatus = 'Active' OR d.vTripStatus = 'On Going Trip' OR d.vTripStatus = 'Arrived') ORDER BY t.iTripId DESC  limit 1";
    $db_dtrip = $obj->MySQLSelect($sql);
    $iTripId = "";
    if (count($db_dtrip) > 0) {
        $iTripId = $db_dtrip[0]['iTripId'];
        $TripId = $generalobj->encrypt($iTripId);
    }
    if (empty($iTripId)) {
        $marker['trip'] = '';
    } else {
        $marker['trip'] = $tconfig['tsite_url_main_admin'] . "map_tracking.php?iTripId=$TripId";
    }
    $markers[] = $marker;
}
$main_location = array();
if ($option != "") {
    $ssql = "SELECT  tLatitude,tLongitude FROM location_master WHERE eStatus = 'Active' AND eFor = 'VehicleType' AND iLocationId=" . $option . "";
} else {
    $ssql = "SELECT  tLatitude,tLongitude FROM location_master WHERE eStatus = 'Active' AND eFor = 'VehicleType' ORDER BY `iLocationId` ASC ";
}
$db_latlong = $obj->MySQLSelect($ssql);
$count = count($db_latlong);
if ($count > 0) {
    $Latitudes = explode(",", $db_latlong[0]['tLatitude']);
    $Longitudes = explode(",", $db_latlong[0]['tLongitude']);
    for ($i = 0; $i < count($Latitudes) - 1; $i++) {
        $all = array();
        $all['Latitude'] = $Latitudes[$i];
        $all['Longitude'] = $Longitudes[$i];
        array_push($main_location, $all);
    }
}
$returnArr['Action'] = "0";
$returnArr['markers'] = $markers;
$returnArr['main_location'] = $main_location;
//$returnArr['newStatus'] = $newStatus;
}
$returnArr['page'] = $next_page;
/*echo "<pre>"; print_r($returnArr); die;*/
echo json_encode($returnArr,JSON_UNESCAPED_UNICODE);exit;
?>