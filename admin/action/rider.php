<?php

include_once('../../common.php');
if (!isset($generalobjRider)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjRider = new General_admin();
}
$generalobjRider->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$hardDelete = 0; //0-Soft Delete,1-Hard Delete
if ($_SERVER['HTTP_HOST'] == "192.168.1.131" || $_SERVER['HTTP_HOST'] == "mobileappsdemo.com" || $_SERVER['HTTP_HOST'] == "webprojectsdemo.com" || $_SERVER['HTTP_HOST'] == "192.168.1.141") {
    $hardDelete = 1; //0-Soft Delete,1-Hard Delete
}
$allTables = $generalobj->getAllTableArray();
if ($method == 'delete' && $iUserId != '') {
    if (!$userObj->hasPermission('delete-users')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete user';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE register_user SET eStatus = 'Deleted' WHERE iUserId = '" . $iUserId . "'";
            $obj->sql_query($query);
            if ($hardDelete == 1) {
                removedRiderData($iUserId);
            }
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "rider.php?" . $parameters);
    exit;
}
//End make deleted
//Start make reset
/* if($method == 'reset' && $iUserId != '')
  {
  if(SITE_TYPE !='Demo'){
  $query = "UPDATE register_user SET iTripId='0',vTripStatus='NONE',vCallFromDriver=' ' WHERE iUserId = '".$iUserId."'";
  $obj->sql_query($query);
  $_SESSION['success'] = '1';
  $_SESSION['var_msg'] = 'Record reset successfully.';
  }
  else{
  $_SESSION['success'] = '2';
  }
  header("Location:".$tconfig["tsite_url_main_admin"]."rider.php?".$parameters); exit;
  } */
//End make reset
//Start Change single Status
if ($iUserId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-users')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of user';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE register_user SET eStatus = '" . $status . "' WHERE iUserId = '" . $iUserId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            if ($status == 'Active') {

                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
            } else {

                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "rider.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission(['update-status-users', 'delete-users'])) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of user';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE register_user SET eStatus = '" . $statusVal . "' WHERE iUserId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $explodeId = explode(",", $checkbox);
            for ($i = 0; $i < count($explodeId); $i++) {
                if ($hardDelete == 1) {
                    removedRiderData($explodeId[$i]);
                }
            }
            $_SESSION['success'] = '1';

            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "rider.php?" . $parameters);
    exit;
}


//Start make reset
if ($method == 'reset' && $iUserId != '') {
    $q = "SELECT iTripId,vTripStatus FROM register_user WHERE iUserId = '" . $iUserId . "'";
    $userdata = $obj->MySQLSelect($q);
    if (!empty($userdata) && $userdata[0]['iTripId'] != '0') {
        $sql = "SELECT iTripId,iActive,iDriverId,iUserId FROM trips WHERE iTripId = '" . $userdata[0]['iTripId'] . "'";
        $TripData = $obj->MySQLSelect($sql);

        // user
        $drvquery = "SELECT iTripId,vTripStatus FROM register_driver WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
        $drvData = $obj->MySQLSelect($drvquery);

        if ($TripData[0]['iActive'] == 'On Going Trip') {

            // driver
            $query = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $iUserId . "'";
            $obj->sql_query($query);

            // trip
            $query1 = "UPDATE trips SET iActive='Finished',tEndDate = NOW() WHERE iTripId = '" . $userdata[0]['iTripId'] . "'";
            $obj->sql_query($query1);

            // rating 
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $userdata[0]['iTripId'] . "' AND eUserType='Passenger'";
            $TripRateDatapass = $obj->MySQLSelect($checkrate);

            if (!empty($TripRateDatapass)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $userdata[0]['iTripId'] . "' AND eUserType='Passenger'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $userdata[0]['iTripId'] . "','0.0',NOW(),'Passenger','')";
                $obj->sql_query($rateq);
            }
            // rating

            if ($drvData[0]['iTripId'] == $TripData[0]['iTripId']) {
                // user
                $dquery = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
                $obj->sql_query($dquery);
                // rating 
                $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $userdata[0]['iTripId'] . "' AND eUserType='Driver'";
                $TripRateDatadrv = $obj->MySQLSelect($checkrate);
                if (!empty($TripRateDatadrv)) {
                    $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $userdata[0]['iTripId'] . "' AND eUserType='Driver'";
                    $obj->sql_query($rateq);
                } else {
                    $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $userdata[0]['iTripId'] . "','0.0',NOW(),'Driver','')";
                    $obj->sql_query($rateq);
                }
            }
        } else if ($TripData[0]['iActive'] == 'Active') {

            // user
            $aquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $iUserId . "'";
            $obj->sql_query($aquery);

            // trip
            $qu1 = "UPDATE trips SET iActive = 'Canceled',tEndDate = NOW(),eCancelled = 'Yes', eCancelledBy='Passenger', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $userdata[0]['iTripId'] . "'";
            $obj->sql_query($qu1);

            // driver
            if ($drvData[0]['iTripId'] == $TripData[0]['iTripId']) {
                // driver
                $uquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
                $obj->sql_query($uquery);
            }
        } else {

            if ($TripData[0]['iActive'] == 'Canceled') {
                // user 
                if ($userdata[0]['vTripStatus'] != 'Cancelled' && $userdata[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $uquery1 = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $iUserId . "'";
                    $obj->sql_query($uquery1);
                }

                // driver
                if ($drvData[0]['vTripStatus'] != 'Cancelled' && $drvData[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $rquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
                    $obj->sql_query($rquery);
                }
            } else {

                // Rider
                if ($userdata[0]['iTripId'] == $TripData[0]['iTripId']) {
                    // user
                    $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $iUserId . "'";
                    $obj->sql_query($uquery);
                    // rating 
                    $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $userdata[0]['iTripId'] . "' AND eUserType='Passenger'";
                    $TripRateDatapass = $obj->MySQLSelect($checkrate);
                    if (!empty($TripRateDatapass)) {
                        $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $userdata[0]['iTripId'] . "' AND eUserType='Passenger'";
                        $obj->sql_query($rateq);
                    } else {
                        $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $userdata[0]['iTripId'] . "','0.0',NOW(),'Passenger','')";
                        $obj->sql_query($rateq);
                    }
                }


                // Driver 
                if ($drvData[0]['iTripId'] == $TripData[0]['iTripId']) {
                    $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
                    $obj->sql_query($query);

                    // rating 
                    $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $userdata[0]['iTripId'] . "' AND eUserType='Driver'";
                    $TripRateDatadriver = $obj->MySQLSelect($checkrate);

                    if (!empty($TripRateDatadriver)) {
                        $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $userdata[0]['iTripId'] . "' AND eUserType='Driver'";
                        $obj->sql_query($rateq);
                    } else {
                        $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $userdata[0]['iTripId'] . "','0.0',NOW(),'Driver','')";
                        $obj->sql_query($rateq);
                    }
                }
            }
        }
    }
    /* 	$query = "UPDATE register_user SET iTripId='0',vTripStatus='NONE',vCallFromDriver=' ' WHERE iUserId = '".$iUserId."'";
      $obj->sql_query($query); */
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = 'Record reset successfully.';
    header("Location:" . $tconfig["tsite_url_main_admin"] . "rider.php?" . $parameters);
    exit;
}

//End make reset
//End Change All Selected Status
//if ($iUserId != '' && $status != '') {
//    if (SITE_TYPE != 'Demo') {
//        $query = "UPDATE register_user SET eStatus = '" . $status . "' WHERE iUserId = '" . $iUserId . "'";
//        $obj->sql_query($query);
//        $_SESSION['success'] = '1';
//        $_SESSION['var_msg'] = "Rider " . $status . " Successfully.";
//        header("Location:".$tconfig["tsite_url_main_admin"]."rider.php?".$parameters);
//        exit;
//    } else {
//        $_SESSION['success']=2;
//        header("Location:".$tconfig["tsite_url_main_admin"]."rider.php?".$parameters);
//        exit;
//    }
//}
//Added By Hasmukh On 04-12-2018 For Hard Remove Rider all Data Start
function removedRiderData($riderId) {
    global $obj, $allTables, $generalobj;
    //echo "<pre>";
    $deleteTableArr = array();
    $tripIds = $cabIds = $cabRequestIds = "";
    $deleteTableArr[] = array("table" => "register_user", "field" => "iUserId", "ids" => $riderId);
    if ($ENABLE_CORPORATE_PROFILE == "Yes") {
        $deleteTableArr[] = array("table" => "user_profile", "field" => "iUserId", "ids" => $riderId);
    }
    $deleteTableArr[] = array("table" => "user_address", "field" => "iUserId", "ids" => $riderId);
    $deleteTableArr[] = array("table" => "user_emergency_contact", "field" => "iUserId", "ids" => $riderId);
    $deleteTableArr[] = array("table" => "user_fave_address", "field" => "iUserId", "ids" => $riderId);
    //$deleteTableArr[] = array("table" => "user_pets", "field" => "iUserId", "ids" => $riderId);
    $deleteTableArr[] = array("table" => "user_wallet", "field" => "iUserId", "ids" => $riderId);
    $deleteTableArr[] = array("table" => "passenger_requests", "field" => "iUserId", "ids" => $riderId);
    $deleteTableArr[] = array("table" => "driver_request", "field" => "iUserId", "ids" => $riderId);
    $getTrips = $obj->MySQLSelect("SELECT iTripId FROM trips WHERE iUserId='" . $riderId . "'");
    for ($t = 0; $t < count($getTrips); $t++) {
        $tripIds .= ",'" . $getTrips[$t]['iTripId'] . "'";
    }
    if ($tripIds != "") {
        $tripIds = trim($tripIds, ",");
        $deleteTableArr[] = array("table" => "trips", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "temp_trips_delivery_locations", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "temp_trip_order_details", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trips_delivery_locations", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trips_locations", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_call_masking", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_delivery_fields", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_destinations", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_help_detail", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_messages", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_order_details", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_outstanding_amount", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_status_messages", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "trip_times", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "driver_user_messages", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "ratings_user_driver", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "user_wallet", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "payments", "field" => "iTripId", "ids" => $tripIds);
        $deleteTableArr[] = array("table" => "driver_request", "field" => "iTripId", "ids" => $tripIds);
    }
    $getCabBooking = $obj->MySQLSelect("SELECT iCabBookingId FROM cab_booking WHERE iUserId='" . $riderId . "'");
    for ($c = 0; $c < count($getCabBooking); $c++) {
        $cabIds .= ",'" . $getCabBooking[$c]['iCabBookingId'] . "'";
    }
    if ($cabIds != "" || $riderId > 0) {
        $cabIds = trim($cabIds, ",");
        if ($cabIds != "") {
            $deleteTableArr[] = array("table" => "cab_booking", "field" => "iCabBookingId", "ids" => $cabIds);
        }
        //echo "SELECT iCabRequestId FROM cab_request_now WHERE iCabBookingId IN($cabIds) OR iUserId='".$riderId."'";die;
        $getCabRequest = $obj->MySQLSelect("SELECT iCabRequestId FROM cab_request_now WHERE iCabBookingId IN($cabIds) OR iUserId='" . $riderId . "'");
        for ($r = 0; $r < count($getCabRequest); $r++) {
            $cabRequestIds .= ",'" . $getCabRequest[$r]['iCabRequestId'] . "'";
        }
        if ($cabRequestIds != "") {
            $cabRequestIds = trim($cabRequestIds, ",");
            $deleteTableArr[] = array("table" => "cab_request_now", "field" => "iCabRequestId", "ids" => $cabRequestIds);
        }
    }
    for ($j = 0; $j < count($deleteTableArr); $j++) {
        $idsW = $deleteTableArr[$j]['ids'];
        $checkTable = $generalobj->checkTableExists($deleteTableArr[$j]['table'], $allTables);
        if ($checkTable == "1") {
            //echo "DELETE FROM " . $deleteTableArr[$j]['table'] . " WHERE " . $deleteTableArr[$j]['field'] . " IN($idsW)<br>";
            $obj->sql_query("DELETE FROM " . $deleteTableArr[$j]['table'] . " WHERE " . $deleteTableArr[$j]['field'] . " IN($idsW)");
        }
    }
}

//Added By Hasmukh On 04-12-2018 For Hard Remove Rider all Data End
?>