<?php

include_once('../common.php');
header('Content-Type: text/html; charset=utf-8');
if(!empty($_SESSION['sess_iAdminUserId'])) {
	
} else {
    $generalobj->check_member_login();
}
function fetchtripstatustimeMAXinterval() {
    global $generalobj, $FETCH_TRIP_STATUS_TIME_INTERVAL;
    //$FETCH_TRIP_STATUS_TIME_INTERVAL = $generalobj->getConfigurations("configurations", "FETCH_TRIP_STATUS_TIME_INTERVAL");
    $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL);
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
    return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
}

$type = $_REQUEST['type'];
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + 60) / 60);
$str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
$ssql = " AND rd.eStatus='Active'";
$eLadiesRide = isset($_REQUEST['eLadiesRide']) ? $_REQUEST['eLadiesRide'] : 'No';
$eHandicaps = isset($_REQUEST['eHandicaps']) ? $_REQUEST['eHandicaps'] : 'No';
$eChildSeat = isset($_REQUEST['eChildSeat']) ? $_REQUEST['eChildSeat'] : 'No';
$eWheelChair = isset($_REQUEST['eWheelChair']) ? $_REQUEST['eWheelChair'] : 'No';
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$vCountry = isset($_REQUEST['vCountry']) ? $_REQUEST['vCountry'] : '';

/*if ($eLadiesRide == 'Yes') {
    $ssql .= " AND (rd.eFemaleOnlyReqAccept = 'Yes' OR rd.eGender = 'Female')";
}
if ($eHandicaps == 'Yes') {
    $ssql .= " AND dv.eHandiCapAccessibility = 'Yes'";
}
if ($eChildSeat == 'Yes') {
    $ssql .= " AND dv.eChildSeatAvailable = 'Yes'";
}
if ($eWheelChair == 'Yes') {
    $ssql .= " AND dv.eWheelChairAvailable = 'Yes'";
}
if (!empty($vCountry)) {
    $ssql .= " AND rd.vCountry LIKE '" . $vCountry . "'";
}
if ($type != "") {
    if ($type == 'Available') {
        $ssql .= " AND rd.vAvailability = '" . $type . "' AND rd.vTripStatus != 'Active' AND rd.tLocationUpdateDate > '$str_date'";
    } else {
        $ssql .= " AND rd.vTripStatus = '" . $type . "' ";
    }
}
$sess_iCompanyId = isset($_REQUEST['sess_iCompanyId']) ? $_REQUEST['sess_iCompanyId'] : '';
if ($sess_iCompanyId != '') {
    $ssql .= " AND rd.iCompanyId = '" . $sess_iCompanyId . "'";
}
$sql = "SELECT rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS fullname ,rd.vEmail,rd.iCompanyId, rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline, rd.vImage, rd.vCode, rd.vPhone, dv.vCarType,rd.tLocationUpdateDate FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' " . $ssql;
echo $sql;
$db_records = $obj->MySQLSelect($sql);*/


if ($eLadiesRide == 'Yes') {
    $ssql .= " AND (rd.eFemaleOnlyReqAccept = 'Yes' OR rd.eGender = 'Female')";
}
if ($eHandicaps == 'Yes') {
    $ssql .= " AND dv.eHandiCapAccessibility = 'Yes'";
}
if ($eChildSeat == 'Yes') {
    $ssql .= " AND dv.eChildSeatAvailable = 'Yes'";
}
if ($eWheelChair == 'Yes') {
    $ssql .= " AND dv.eWheelChairAvailable = 'Yes'";
}
if (!empty($vCountry)) {
    $ssql .= " AND rd.vCountry LIKE '" . $vCountry . "'";
}
$sess_iCompanyId = isset($_REQUEST['sess_iCompanyId']) ? $_REQUEST['sess_iCompanyId'] : '';
if ($sess_iCompanyId != '') {
    $ssql .= " AND rd.iCompanyId = '" . $sess_iCompanyId . "'";
}
if ($eType == "UberX" && !empty($dBooking_date)) {
    $vday = date('l', strtotime($dBooking_date));
    $curr_hour = date('H', strtotime($dBooking_date));
    $next_hour = $curr_hour + 01;
    if ($curr_hour == "00") {
        $curr_hour = "12";
        $next_hour = "01";
    }
    $selected_time = $curr_hour . "-" . $next_hour;
    $ssql .= "AND vDay LIKE '%" . $vday . "%' AND dmt.vAvailableTimes LIKE '%" . $selected_time . "%'";
}
if ($eType == "UberX") {
    $sql = "SELECT rd.iDriverId,rd.vEmail,rd.iCompanyId, CONCAT(rd.vName,' ',rd.vLastName) AS FULLNAME,rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline, rd.vImage, rd.vCode, rd.vPhone,rd.tLocationUpdateDate FROM register_driver AS rd RIGHT JOIN driver_manage_timing  AS dmt ON rd.iDriverId = dmt.iDriverId  WHERE rd.vLatitude !='' AND rd.vLongitude !='' " . $ssql . " GROUP BY dmt.iDriverId";
    $db_records = $obj->MySQLSelect($sql);
    foreach ($db_records as $key => $value) {
        $sql_vehicle = "SELECT vCarType FROM `driver_vehicle` WHERE iDriverId = '" . $value['iDriverId'] . "' AND eType='UberX'";
        $dbvehicle_records = $obj->MySQLSelect($sql_vehicle);
        $db_records[$key]['vCarType'] = $dbvehicle_records[0]['vCarType'];
    }
} else {
    $sql = "SELECT rd.iDriverId,rd.vEmail,rd.iCompanyId, CONCAT(rd.vName,' ',rd.vLastName) AS FULLNAME,rd.vLatitude,rd.vLongitude,rd.vServiceLoc,rd.vAvailability,rd.vTripStatus,rd.tLastOnline,rd.tLocationUpdateDate, rd.vImage, rd.vCode, rd.vPhone, dv.vCarType FROM register_driver AS rd LEFT JOIN driver_vehicle AS dv ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.vLatitude !='' AND rd.vLongitude !='' " . $ssql;
    $db_records = $obj->MySQLSelect($sql);
}


for ($i = 0; $i < count($db_records); $i++) {
     $newArray = array();
    $newArray = explode(',', $db_records[$i]['vCarType']);
    $vTripStatus = $db_records[$i]['vTripStatus'];
    if ($vTripStatus != 'Active' && $vTripStatus != 'On Going Trip' && $vTripStatus != 'Arrived') {
        if ($iVehicleTypeId == '' || (!empty($newArray) && in_array($iVehicleTypeId, $newArray))) {
            if ($db_records[$i]['vImage'] != 'NONE' && $db_records[$i]['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"] . '/' . $db_records[$i]['iDriverId'] . '/2_' . $db_records[$i]['vImage'])) {
                $DriverImage = $tconfig["tsite_upload_images_driver"] . '/' . $db_records[$i]['iDriverId'] . '/2_' . $db_records[$i]['vImage'];
            } else {
                $DriverImage = $tconfig["tsite_url"] . "assets/img/profile-user-img.png";
            }
            $db_records[$i]['vImageDriver'] = $DriverImage;
            $time = time();
            $last_online_time = strtotime($db_records[$i]['tLastOnline']);
            $time_difference = $time - $last_online_time;
            $vTripStatus = $db_records[$i]['vTripStatus'];
            /*if ($vTripStatus == 'Active') {
                $db_records[$i]['vAvailability'] = $vTripStatus;
            } else if ($vTripStatus == 'Arrived') {
                $db_records[$i]['vAvailability'] = $vTripStatus;
            } else if ($vTripStatus == 'On Going Trip') {
                $db_records[$i]['vAvailability'] = $vTripStatus;
            } else if ($vTripStatus != 'Active' && $db_records[$i]['vAvailability'] == "Available" && $db_records[$i]['tLocationUpdateDate'] > $str_date) {
                $db_records[$i]['vAvailability'] = "Available";
            } else {
                $db_records[$i]['vAvailability'] = "Not Available";
            }*/
            /* if($db_records[$i]['vAvailability'] == "Available"){
              $db_records[$i]['vAvailability'] = "Available";
              }else{
              $vTripStatus = $db_records[$i]['vTripStatus'];
              //if($vTripStatus == 'Active' || $vTripStatus == 'On Going Trip' || $vTripStatus == 'Arrived'){
              if($vTripStatus == 'Active' || $vTripStatus == 'On Going Trip' || $vTripStatus == 'Arrived'){
              $db_records[$i]['vAvailability'] = $vTripStatus;
              }else{
              $db_records[$i]['vAvailability'] = "Not Available";
              }
              } */
        
            $db_records[$i]['fullname'] = $generalobj->clearName($db_records[$i]['fullname']);
            $db_records[$i]['vEmail'] = $generalobj->clearEmail($db_records[$i]['vEmail']);
            $db_records[$i]['vPhone'] = $generalobj->clearPhone($db_records[$i]['vPhone']);
            
            if ($vTripStatus != 'Active' && $db_records[$i]['vAvailability'] == "Available" && $db_records[$i]['tLocationUpdateDate'] > $str_date) {
                $db_records[$i]['vAvailability'] = "Available";
                $dbDrivers[$i] = $db_records[$i];
            } else {
                if ($vTripStatus == 'Active' || $vTripStatus == 'On Going Trip' || $vTripStatus == 'Arrived') {
                    $db_records[$i]['vAvailability'] = $vTripStatus;
                } else {
                    $db_records[$i]['vAvailability'] = "Not Available";
                }
                $dbDrivers[$i] = $db_records[$i];
            }
            
        }
    }
}

$locations = array();
// if($type != "") {
// }
#marker Add

$markerPath = $tconfig["tsite_url"] . "webimages/upload/mapmarker/";

if($eType=='UberX') {
    $markerPath = $markerPath.'UberX/';
} else if($eType=='Fly') {
    $markerPath = $markerPath.'Fly/';
} else {
    if(!empty($iVehicleTypeId)) {
    $sql_vehicle_type = "SELECT eIconType FROM vehicle_type WHERE iVehicleTypeId = $iVehicleTypeId";
    $db_vehicle_type = $obj->MySQLSelect($sql_vehicle_type);
    $iconFolder = $db_vehicle_type[0]['eIconType'];
    $markerPath = $markerPath.$iconFolder.'/';
    }
}

foreach ($db_records as $key => $value) {  
    if ($APP_TYPE == 'UberX' || $eType=='UberX') {
        /*if ($value['vAvailability'] == "Available") {
            $statusIcon = $markerPath . "male-green.png";
        } else if ($value['vAvailability'] == "Active") {
            $statusIcon = $markerPath . "male-red.png";
        } else if ($value['vAvailability'] == "Arrived") {
            $statusIcon = $markerPath . "male-blue.png";
        } else if ($value['vAvailability'] == "On Going Trip") {
            $statusIcon = $markerPath . "male-yellow.png";
        } else if ($value['vAvailability'] == "Not Available") {
            $statusIcon = $markerPath . "male-gray.png";
        } else {
            $statusIcon = $markerPath . "male-gray.png";
        }*/
        $statusIcon = !empty($value['vImageDriver']) ? $value['vImageDriver'] : $tconfig["tsite_url"] . "assets/img/profile-user-img.png";
        //$statusIcon = $markerPath . "male-green.png";
    } else {
        if ($value['vAvailability'] == "Available") {
            $statusIcon = $markerPath . "available.png";
        } else if ($value['vAvailability'] == "Active") {
            $statusIcon = $markerPath . "enroute.png";
        } else if ($value['vAvailability'] == "Arrived") {
            $statusIcon = $markerPath . "reached.png";
        } else if ($value['vAvailability'] == "On Going Trip") {
            $statusIcon = $markerPath . "started.png";
        } else if ($value['vAvailability'] == "Not Available") {
            $statusIcon = $markerPath . "offline.png";
        } else {
            $statusIcon = $markerPath . "offline.png";
        }
    }
    $locations[] = array(
        'google_map' => array(
            'lat' => $value['vLatitude'],
            'lng' => $value['vLongitude'],
        ),
        'location_icon' => $statusIcon,
        'location_address' => $value['vServiceLoc'],
        'location_image' => $value['vImageDriver'],
        'location_mobile' => $generalobj->clearPhone($value['vCode'] . $value['vPhone']),
        'location_ID' => $generalobj->clearEmail($value['vEmail']),
        'location_name' => $value['fullname'],
        'location_type' => $value['vAvailability'],
        'location_online_status' => $value['vAvailability'],
        'location_carType' => $value['vCarType'],
        'location_driverId' => $value['iDriverId'],
    );
}

$returnArr['Action'] = "0";
$returnArr['locations'] = $locations;
$returnArr['db_records'] = $db_records;
$returnArr['newStatus'] = $newStatus;

// echo "<pre>"; print_r($returnArr); die;
echo json_encode($returnArr);
exit;
?>