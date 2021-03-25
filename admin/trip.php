<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

include_once(TPATH_CLASS.'modules_availibility.php');
include_once('../generalFunctions.php');

//include_once ('../app_common_functions.php'); //added by SP on 6-9-2019 for fly changes
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('manage-trip-jobs')) {
    $userObj->redirect();
}

$default_lang = $generalobj->get_default_lang();
$script = 'Trips';
$rdr_ssql = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}
//data for select fields
$sql = "select iCompanyId,vCompany from company WHERE eStatus != 'Deleted' AND eSystem = 'General' $rdr_ssql";
$db_company = $obj->MySQLSelect($sql);
$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName from register_driver WHERE eStatus != 'Deleted' $rdr_ssql";
$db_drivers = $obj->MySQLSelect($sql);
$sql = "select iUserId,CONCAT(vName,' ',vLastName) AS riderName from register_user WHERE eStatus != 'Deleted' $rdr_ssql";
$db_rider = $obj->MySQLSelect($sql);
//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';
$ord = ' ORDER BY t.iTripId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY t.eType ASC";
    else
        $ord = " ORDER BY t.eType DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY t.tTripRequestDate ASC";
    else
        $ord = " ORDER BY t.tTripRequestDate DESC";
}
if ($sortby == 3) {

    if ($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
    else
        $ord = " ORDER BY c.vCompany DESC";
}
if ($sortby == 4) {

    if ($order == 0)
        $ord = " ORDER BY d.vName ASC";
    else
        $ord = " ORDER BY d.vName DESC";
}
if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY u.vName ASC";
    else
        $ord = " ORDER BY u.vName DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
if ($startDate != '') {
    $ssql .= " AND Date(t.tTripRequestDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(t.tTripRequestDate) <='" . $endDate . "'";
}
if ($serachTripNo != '') {
    $ssql .= " AND t.vRideNo ='" . $serachTripNo . "'";
}
if ($searchCompany != '') {
    $ssql .= " AND d.iCompanyId ='" . $searchCompany . "'";
}
if ($searchDriver != '') {
    $ssql .= " AND t.iDriverId ='" . $searchDriver . "'";
}
if ($searchRider != '') {
    $ssql .= " AND t.iUserId ='" . $searchRider . "'";
}
if ($vStatus == "onRide") {
    $ssql .= " AND (t.iActive = 'On Going Trip' OR t.iActive = 'Active') AND t.eCancelled='No'";
} else if ($vStatus == "cancel") {
    $ssql .= " AND (t.iActive = 'Canceled' OR t.eCancelled='yes')";
} else if ($vStatus == "complete") {
    $ssql .= " AND t.iActive = 'Finished' AND t.eCancelled='No'";
}
if ($eType != '') {
    if ($eType == 'Ride') {
        $ssql .= " AND t.eType ='" . $eType . "' AND t.iRentalPackageId = 0 AND t.eHailTrip = 'No' ";
        //if(checkFlyStationsModule(1)) {
        $ssql .= " AND  t.iFromStationId = 0 AND t.iToStationId = 0 ";
        //}
    } elseif ($eType == 'RentalRide') {
        $ssql .= " AND t.eType ='Ride' AND t.iRentalPackageId > 0";
    } elseif ($eType == 'HailRide') {
        $ssql .= " AND t.eType ='Ride' AND t.eHailTrip = 'Yes'";
    } else if ($eType == "Pool") {
        $ssql .= " AND t.eType ='Ride' AND t.ePoolRide = 'Yes'";
    } else if ($eType == "Fly") {
        $ssql .= " AND t.eType ='Ride' AND t.iFromStationId != 0 AND t.iToStationId != 0 ";
    } else {
        $ssql .= " AND t.eType ='" . $eType . "' ";
    }
}

//$ufxEnable = $generalobj->CheckUfxServiceAvailable(); 
$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";

if ($ufxEnable != 'Yes') {
    $ssql .= " AND t.eType != 'UberX'";
}
if(!checkFlyStationsModule(1)) {
    $ssql.= " AND t.iFromStationId = '0' AND t.iToStationId = '0'";
}
if($rideEnable != "Yes") {
    $ssql .= " AND t.eType != 'Ride'";
}
if($deliveryEnable != "Yes") {
    $ssql .= " AND t.eType != 'Deliver' AND t.eType != 'Multi-Delivery'";
}

if (!empty($promocode) && isset($promocode)) {
    $ssql .= " AND t.vCouponCode LIKE '" . $promocode . "' AND t.iActive !='Canceled'";
}
if (count($userObj->locations) > 0) {
    $locations = implode(', ', $userObj->locations);
    $ssql .= " AND vt.iLocationid IN(-1, {$locations}) ";
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And t.tTripRequestDate > '" . WEEK_DATE . "'";
}
$hotelQuery = "";
if ($_SESSION['SessionUserType'] == 'hotel') {
    /* $sql1 = "SELECT * FROM hotel where iAdminId = '".$_SESSION['sess_iAdminUserId']."'";
      $hoteldata = $obj->MySQLSelect($sql1); */
    $iHotelBookingId = $_SESSION['sess_iAdminUserId'];
    $hotelQuery = " AND (t.eBookingFrom = 'Hotel' || t.eBookingFrom = 'Kiosk') AND t.iHotelBookingId = '" . $iHotelBookingId . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(t.iTripId) AS Total FROM trips t LEFT JOIN register_driver d ON d.iDriverId = t.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId LEFT JOIN  register_user u ON t.iUserId = u.iUserId LEFT JOIN company c ON c.iCompanyId=d.iCompanyId WHERE 1=1 AND t.eSystem = 'General' $ssql $trp_ssql $hotelQuery";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;

//Pagination End
$sql = "SELECT t.iFromStationId, t.iToStationId,t.ePoolRide,t.tStartDate,t.tEndDate, t.tTripRequestDate,t.eBookingFrom,t.vCancelReason,t.vCancelComment,t.iCancelReasonId, t.eHailTrip, t.iUserId, t.iFare, t.eType, d.iDriverId, t.tSaddress, t.vRideNo, t.tDaddress,  t.fWalletDebit, t.eCarType, t.iTripId, t.iActive, t.fCancellationFare, t.eCancelledBy, t.eCancelled,	t.iRentalPackageId , CONCAT(u.vName,' ',u.vLastName) AS riderName, CONCAT(d.vName,' ',d.vLastName) AS driverName, d.vAvgRating,t.vDeliveryConfirmCode, c.vCompany, vt.vVehicleType_{$default_lang} as vVehicleType, vt.vRentalAlias_{$default_lang} as vRentalVehicleTypeName,t.tVehicleTypeData FROM trips t LEFT JOIN register_driver d ON d.iDriverId = t.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId LEFT JOIN  register_user u ON t.iUserId = u.iUserId LEFT JOIN company c ON c.iCompanyId=d.iCompanyId WHERE 1=1 AND t.eSystem = 'General' {$ssql} {$trp_ssql} {$hotelQuery} {$ord} LIMIT {$start}, {$per_page}";
$db_trip = $obj->MySQLSelect($sql);
$endRecord = count($db_trip);


$driverIdArr = $userIdArr = array();

if ($_SESSION['SessionUserType'] == 'hotel') {
    foreach ($db_trip as $dtps) {
        $driverIdArr[] = $dtps['iDriverId'];
        $userIdArr[] = $dtps['iUserId'];
    }
} else {
        foreach ($db_drivers as $driver) {
        $driverIdArr[] = $driver['iDriverId'];
    }
    foreach ($db_rider as $rider) {
        $userIdArr[] = $rider['iUserId'];
    }
}

    
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
$curryearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y")));
$curryearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
$prevyearFDate = date("Y-m-d", mktime(0, 0, 0, '1', '1', date("Y") - 1));
$prevyearTDate = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
$currmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $tdate, date("Y")));
$currmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, date("d") - $mdate, date("Y")));
$prevmonthFDate = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d") - $tdate, date("Y")));
$prevmonthTDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $mdate, date("Y")));
$monday = date('Y-m-d', strtotime('sunday this week -1 week'));
$sunday = date('Y-m-d', strtotime('saturday this week'));
$Pmonday = date('Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date('Y-m-d', strtotime('saturday this week -1 week'));

//Added By HJ On 31-08-2019 For Reset Trip Functionality Start
if ($method == 'reset' && $iTripId != '') {
    $TripData = $obj->MySQLSelect("SELECT iTripId,iActive,iDriverId,iUserId,vRideNo,eType FROM trips WHERE iTripId = '" . $iTripId . "'");
    $iDriverId = $TripData[0]['iDriverId'];
    $iUserId = $TripData[0]['iUserId'];
    $vRideNo = $TripData[0]['vRideNo'];
    //$vTripStatus = "Not Active"; // Commented By HJ On 06-02-2020 For Solved User all Crash Issue
    $vTripStatus = "NONE"; // Added By HJ On 06-02-2020 For Solved User all Crash Issue
    if ($TripData[0]['iActive'] == 'Active') {
        $vTripStatus = "Cancelled";
    }
    //Get Driver Data For Send Notification Start
    $drvdata = $obj->MySQLSelect("SELECT iTripId,vTripStatus,tSessionId,iGcmRegId,eDeviceType,CONCAT(vName,' ',vLastName) AS driverName FROM register_driver WHERE iDriverId = '" . $iDriverId . "' AND iTripId = '" . $iTripId . "'");
    $tSessionId = $drvdata[0]['tSessionId'];
    $iGcmRegId = $drvdata[0]['iGcmRegId'];
    $eDeviceType = $drvdata[0]['eDeviceType'];
    //Get Driver Data For Send Notification End

    //Get User Data For Send Notification Start
    $userdata = $obj->MySQLSelect("SELECT tSessionId,iGcmRegId,eDeviceType FROM register_user WHERE iUserId = '" . $iUserId . "' AND iTripId = '" . $iTripId . "'");
    $iGcmRegIdUser = $userdata[0]['iGcmRegId'];
    $eDeviceTypeUser = $userdata[0]['eDeviceType'];
    //Get User Data For Send Notification End
    if ($TripData[0]['iActive'] != 'Finished' && $TripData[0]['iActive'] != 'Canceled') {
        $date = date("Y:m:d H:i:s");
        $obj->sql_query("UPDATE trips SET iActive='Canceled',tEndDate = '".$date."',eCancelled = 'Yes', eCancelledBy='', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $iTripId . "'");
        $obj->sql_query("UPDATE register_driver SET vTripStatus='" . $vTripStatus . "',iTripId=0 WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'  AND iTripId = '" . $iTripId . "'");
        $obj->sql_query("UPDATE register_user SET vTripStatus='" . $vTripStatus . "',iTripId=0 WHERE iUserId = '" . $iUserId . "'  AND iTripId = '" . $iTripId . "'");

        $dbAllTableArr = $generalobj->getAllTableArray();
        $tripDeliveryLocationTable = 0;
        if (in_array("trips_delivery_locations", $dbAllTableArr)) {
            $tripDeliveryLocationTable = 1;
        }
        if ($tripDeliveryLocationTable == 1) {
            $obj->sql_query("UPDATE trips_delivery_locations SET iActive='Canceled' WHERE iTripId = '" . $iTripId . "'");
        }
        if ($TripData[0]['iActive'] != 'Active') {
            $TripRateDatadriver = $obj->MySQLSelect("SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $iTripId . "' AND eUserType='Driver'");
            if (count($TripRateDatadriver) > 0) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $iTripId . "' AND eUserType='Driver'";
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $iTripId . "','0.0',NOW(),'Driver','')";
            }
            $obj->sql_query($rateq);
            $TripRateDatapass = $obj->MySQLSelect("SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $iTripId . "' AND eUserType='Passenger'");
            if (count($TripRateDatapass) > 0) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $iTripId . "' AND eUserType='Passenger'";
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $iTripId . "','0.0',NOW(),'Passenger','')";
            }
            $obj->sql_query($rateq);
        }
        $tMessage = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'].' Reset successfully.';
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'].' Reset successfully.';
        $var_filterd = "";
        foreach ($_REQUEST as $key => $val) {
            if ($key == "method" || $key == 'iTripId' || $key == 'iServiceId') {
                $var_filterd .= "&$key=";
            } else {
                $var_filterd .= "&$key=" . $val;
            }
        }
        //Added BY HJ On 30-09-2019 For Send Notification to Driver When Cancel/Reset Trip From hotel Panel Start
        $tMessageNotification = "#" . $vRideNo . "" . str_replace("This", "", $langage_lbl_admin['LBL_CANCELED_TRIP_ADMIN_TXT']) . " " . $langage_lbl_admin['LBL_CANCELLED_BY_ADMIN'];
        $deviceTokens_arr_ios = $registation_ids_new = array();
        //Commented By HJ On 16-10-2019 For Send Notification Details Start
        /* $tMessage = trim(stripslashes($obj->SqlEscapeString($tMessage)));
          $tMessage = str_replace(array('\r', '\n'), array(chr(13), chr(10)), $tMessage);
          $message = $alertMsg = stripslashes($tMessage); */
        //Commented By HJ On 16-10-2019 For Send Notification Details End
        $MsgType = "TripCancelled";
        $message_arr = array();
        $message_arr['Message'] = $message_arr['MsgType'] = $MsgType;
        $message_arr['vTitle'] = $tMessageNotification;
        $message_arr['eType'] = $eType;
        $message_arr['iTripId'] = $iTripId;
        $message_arr['uString'] = time();
        $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
        //For Send To Driver App Start
        if ($eDeviceType == "Android") {
            array_push($registation_ids_new, $iGcmRegId);
            $Rmessage = array(
                "message" => $message
            );
            $result = send_notification($registation_ids_new, $Rmessage, 0);
        } else if ($eDeviceType != "Android") {
            array_push($deviceTokens_arr_ios, $iGcmRegId);
            if ($message != "") {
                $alertMsg = $message_arr['vTitle'];
                sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0, 'admin');
            }
        }
        //For Send To Driver App End
        //For send To User App Start
        $message_arr = array();
        $message_arr['Message'] = $message_arr['MsgType'] = $MsgType;
        $message_arr['vTitle'] = $tMessageNotification;
        $message_arr['eType'] = $eType;
        $message_arr['iTripId'] = $iTripId;
        $message_arr['uString'] = time();
        $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
        $deviceTokens_arr_ios = $registation_ids_new = array();
        if ($eDeviceTypeUser == "Android") {
            array_push($registation_ids_new, $iGcmRegIdUser);
            $Rmessage = array(
                "message" => $message
            );
            $result = send_notification($registation_ids_new, $Rmessage, 0);
        } else if ($eDeviceTypeUser != "Android") {
            array_push($deviceTokens_arr_ios, $iGcmRegIdUser);
            if ($message != "") {
                $alertMsg = $message_arr['vTitle'];
                sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0, 'admin');
            }
        }
        //For send To User App End
        //Added BY HJ On 30-09-2019 For Send Notification to Driver When Cancel/Reset Trip From hotel Panel End
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "trip.php?" . $var_filterd);
    exit;
}
//Added By HJ On 31-08-2019 For Reset Trip Functionality End
if ($method == 'reset_old' && $iTripId != '') { // Rename reset to reset_old BY HJ On 31-08-2019 For Optimized Code As Per Above Condition
    $sql = "SELECT iTripId,iActive,iDriverId,iUserId FROM trips WHERE iTripId = '" . $iTripId . "'";
    $TripData = $obj->MySQLSelect($sql);
    $userquery = "SELECT iTripId,vTripStatus FROM register_user WHERE iUserId = '" . $TripData[0]['iUserId'] . "' AND iTripId = '" . $iTripId . "'";
    $useData = $obj->MySQLSelect($userquery);
    // driver
    $q = "SELECT iTripId,vTripStatus FROM register_driver WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "' AND iTripId = '" . $iTripId . "'";
    $drvdata = $obj->MySQLSelect($q);
    if ($TripData[0]['iActive'] == 'On Going Trip') {
        // trip
        $query1 = "UPDATE trips SET iActive='Canceled',tEndDate = NOW(),eCancelled = 'Yes', eCancelledBy='', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $iTripId . "'";
        $obj->sql_query($query1);
        if ($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
            // driver
            $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'  AND iTripId = '" . $iTripId . "'";
            $obj->sql_query($query);
            // rating 
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $iTripId . "' AND eUserType='Driver'";
            $TripRateDatadriver = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatadriver)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $iTripId . "' AND eUserType='Driver'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $iTripId . "','0.0',NOW(),'Driver','')";
                $obj->sql_query($rateq);
            }
            // rating
        }
        if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
            // user
            $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
            $obj->sql_query($uquery);
            // rating 
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $iTripId . "' AND eUserType='Passenger'";
            $TripRateDatapass = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatapass)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $iTripId . "' AND eUserType='Passenger'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $iTripId . "','0.0',NOW(),'Passenger','')";
                $obj->sql_query($rateq);
            }
        }
    } else if ($TripData[0]['iActive'] == 'Active') {
        // trip
        $qu1 = "UPDATE trips SET iActive = 'Canceled',tEndDate = NOW(),eCancelled = 'Yes', eCancelledBy='', vCancelReason='Status Reset By Admin' WHERE iTripId = '" . $iTripId . "'";
        $obj->sql_query($qu1);

        if ($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
            // driver
            $aquery = "UPDATE register_driver SET vTripStatus='Cancelled' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
            $obj->sql_query($aquery);
        }
        // user
        if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
            // user
            $uquery = "UPDATE register_user SET vTripStatus='Cancelled' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
            $obj->sql_query($uquery);
        }
    } else {
        // Driver 
        if ($drvdata[0]['iTripId'] == $TripData[0]['iTripId']) {
            $query = "UPDATE register_driver SET vTripStatus='Not Active' WHERE iDriverId = '" . $TripData[0]['iDriverId'] . "'";
            $obj->sql_query($query);
            // rating 
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $TripData[0]['iTripId'] . "' AND eUserType='Driver'";
            $TripRateDatadriver = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatadriver)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $TripData[0]['iTripId'] . "' AND eUserType='Driver'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $TripData[0]['iTripId'] . "','0.0',NOW(),'Driver','')";
                $obj->sql_query($rateq);
            }
        }
        // Rider
        if ($useData[0]['iTripId'] == $TripData[0]['iTripId']) {
            // user
            $uquery = "UPDATE register_user SET vTripStatus='Not Active' WHERE iUserId = '" . $TripData[0]['iUserId'] . "'";
            $obj->sql_query($uquery);
            // rating
            $checkrate = "SELECT `iRatingId` FROM `ratings_user_driver` WHERE iTripId = '" . $TripData[0]['iTripId'] . "' AND eUserType='Passenger'";
            $TripRateDatapass = $obj->MySQLSelect($checkrate);
            if (!empty($TripRateDatapass)) {
                $rateq = "UPDATE ratings_user_driver SET vRating1='0.0' WHERE iTripId = '" . $TripData[0]['iTripId'] . "' AND eUserType='Passenger'";
                $obj->sql_query($rateq);
            } else {
                $rateq = "INSERT INTO `ratings_user_driver`(`iTripId`, `vRating1`, `tDate`, `eUserType`, `vMessage`) VALUES ('" . $TripData[0]['iTripId'] . "','0.0',NOW(),'Passenger','')";
                $obj->sql_query($rateq);
            }
        }
    }
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] =  $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'].'  Reset successfully.';
    foreach ($_REQUEST as $key => $val) {
        if ($key == "method" || $key == 'iTripId' || $key == 'iServiceId') {
            $var_filterd .= "&$key=";
        } else {
            $var_filterd .= "&$key=" . $val;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "trip.php?" . $var_filterd);
    exit;
}
$vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0; $r < count($getVehicleTypes); $r++) {
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
}
//$ufxEnable = $generalobj->CheckUfxServiceAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status
$hotelPanel = isHotelPanelEnable(); 
$kioskPanel = isKioskPanelEnable();
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <!-- <meta http-equiv="refresh" content="60"/> -->
        <?php include_once('global_files.php'); ?>
        <style type="text/css">
            .form-group .row{
                padding: 0;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2><?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?> </h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page">
                            <input type="hidden" name="action" value="search" />
                            <h3>Search <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']; ?> ...</h3>
                            <span>
                                <a style="cursor:pointer" onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                                <a style="cursor:pointer" onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                                <a style="cursor:pointer" onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                                <a style="cursor:pointer" onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                                <a style="cursor:pointer" onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                                <a style="cursor:pointer" onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                                <a style="cursor:pointer" onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                                <a style="cursor:pointer" onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                            </span> 
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-3">
                                    <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff" />
                                </div>
                                <div class="col-lg-3">
                                    <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>
                                </div>
                                <div class="col-lg-3">
                                    <select class="form-control" name = 'vStatus' >
                                        <option value="">All Status</option>
                                        <option value="onRide" <?php
                    if ($vStatus == "onRide") {
                        echo "selected";
                    }
                    ?>>On Going <?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> </option>
                                        <option value="complete" <?php
                                        if ($vStatus == "complete") {
                                            echo "selected";
                                        }
                    ?>>Completed</option>
                                        <option value="cancel" <?php
                                        if ($vStatus == "cancel") {
                                            echo "selected";
                                        }
                    ?>>Cancelled</option>
                                    </select>
                                </div>
                                <!-- <div class="col-lg-3">
                                <?php
                                echo getSelect($user_locations_select_data, 'locations', ['class' => "form-control"]);
                                ?>
                                </div> -->
                                <div class="col-lg-3">
                                    <input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number" class="form-control search-trip001" value="<?= $serachTripNo; ?>"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php if ($_SESSION['SessionUserType'] != 'hotel') { ?>
                                <div class="col-lg-3">
                                    <select class="form-control filter-by-text" name = 'searchCompany' id="searchCompany" data-text="Select Company">
                                        <option value="">Select Company</option>
                                        <?php foreach ($db_company as $dbc) { ?>
                                            <option value="<?= $dbc['iCompanyId']; ?>" <?php
                                    if ($searchCompany == $dbc['iCompanyId']) {
                                        echo "selected";
                                    }
                                            ?>><?= $generalobjAdmin->clearCmpName($dbc['vCompany']); ?></option>
                                                <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>
                            <div class="col-lg-3">
                                <select class="form-control filter-by-text driver_container" name = 'searchDriver' data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>">
                                    <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                    <?php foreach ($db_drivers as $dbd) {
                                        if (in_array($dbd['iDriverId'], $driverIdArr)) { ?>
                                        <option value="<?= $dbd['iDriverId']; ?>" <?php
                                    if ($searchDriver == $dbd['iDriverId']) {
                                        echo "selected";
                                    }
                                        ?>><?= $generalobjAdmin->clearName($dbd['driverName']); ?></option>
                                            <?php } } ?>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control filter-by-text" name = 'searchRider' data-text="Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>">
                                    <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                                    <?php foreach ($db_rider as $dbr) {
                                        if (in_array($dbr['iUserId'], $userIdArr)) { ?>
                                        <option value="<?= $dbr['iUserId']; ?>" <?php
                                    if ($searchRider == $dbr['iUserId']) {
                                        echo "selected";
                                    }
                                        ?>><?= $generalobjAdmin->clearName($dbr['riderName']); ?></option>
                                            <?php } } ?>
                                </select>
                            </div>
                            <?php
                            if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                                if ($_SESSION['SessionUserType'] != 'hotel') {
                                    ?>
                                    <div class="col-lg-3">
                                        <select class="form-control" name = 'eType' >
                                            <option value="">Service Type</option>
                                            <?php if ($rideEnable=='Yes') { ?>
                                                <option value="Ride" <?php
                                                if ($eType == "Ride") {
                                                    echo "selected";
                                                }
                                                ?>><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                                    <?php if ($ENABLE_HAIL_RIDES == "Yes" && $APP_TYPE != 'Delivery') { ?>
                                                <option value="HailRide" <?php
                                            if ($eType == "HailRide") {
                                                echo "selected";
                                            }
                                                        ?>> Hail <?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                                    <?php } if (ENABLE_RENTAL_OPTION == 'Yes' && $APP_TYPE != 'Delivery') { ?>
                                                <option value="RentalRide" <?php
                                            if ($eType == "RentalRide") {
                                                echo "selected";
                                            }
                                                        ?>>Rental <?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                                    <?php } } if ($deliveryEnable == "Yes") { ?>
                                                <option value="Deliver" <?php
                                            if ($eType == "Deliver") {
                                                echo "selected";
                                            }
                                                        ?>>Delivery</option>
                                                    <?php if (ENABLE_MULTI_DELIVERY == "Yes") { ?>
                                                <option value="Multi-Delivery" <?php
                                            if ($eType == "Multi-Delivery") {
                                                echo "selected";
                                            }
                                                        ?>>Multi-Delivery</option>
                                                    <?php } } if ($ufxEnable == "Yes") { ?>
                                                <option value="UberX" <?php
                                            if ($eType == "UberX") {
                                                echo "selected";
                                            }
                                                        ?>>Other Services</option>
                                                    <?php } if ($rideEnable == "Yes" && $PACKAGE_TYPE == "SHARK") { ?>
                                                <option value="Pool" <?php
                                            if ($eType == "Pool") {
                                                echo "selected";
                                            }
                                                        ?>><?php echo $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH'] . " " . $langage_lbl_admin['LBL_POOL']; ?> </option>
                                                    <?php } if (checkFlyStationsModule(1)) { ?>
                                                <option value="Fly" <?php
                                            if ($eType == "Fly") {
                                                echo "selected";
                                            }
                                                        ?>><?php echo $langage_lbl_admin['LBL_HEADER_RDU_FLY_RIDE']; ?> </option>
                                                    <? } ?>
                                        </select>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <div class="tripBtns001"><b>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'trip.php'"/>
                                <?php if (!empty($db_trip)) { ?>
                                    <button type="button" onClick="reportExportTypes('triplist')" class="export-btn001" style="float:none;">Export</button>
                                <?php } ?>
                            </b>
                        </div>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <?php
                                                    if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
                                                        if ($_SESSION['SessionUserType'] != 'hotel') {
                                                            ?>
                                                            <th width="10%" class="align-left"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                    if ($sortby == '1') {
                                                        echo $order;
                                                    } else {
                                                                ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']; ?> <?php
                                                                                                  if ($sortby == 1) {
                                                                                                      if ($order == 0) {
                                                                                                          ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                        }
                                                                    } else {
                                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                    <?php
                                                    if ($hotelPanel > 0 || $kioskPanel > 0) {
                                                        ?>
                                                        <th class="align-center">Booked By</th>
                                                        <?php
                                                    }
                                                    ?>
                                                    <th class="align-center"><?= $langage_lbl_admin['LBL_TRIP_NO_ADMIN']; ?></th>
                                                    <th>Address</th>
                                                    <th width="8%" class="align-left"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                    if ($sortby == '2') {
                                                        echo $order;
                                                    } else {
                                                        ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']; ?> <?php
                                                                                         if ($sortby == 2) {
                                                                                             if ($order == 0) {
                                                                                                 ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                            <?php if ($_SESSION['SessionUserType'] != 'hotel') { ?>
                                                        <th width="12%"><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                            if ($sortby == '3') {
                                                                echo $order;
                                                            } else {
                                                                    ?>0<?php } ?>)">Company <?php
                                                                           if ($sortby == 3) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <?php } ?>
                                                    <th width="12%"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                    if ($sortby == '4') {
                                                        echo $order;
                                                    } else {
                                                        ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?php
                                                                       if ($sortby == 4) {
                                                                           if ($order == 0) {
                                                                               ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="12%"><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                            if ($sortby == '5') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?php
                                                                       if ($sortby == 5) {
                                                                           if ($order == 0) {
                                                                               ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="8%" class="align-right"><?= $langage_lbl_admin['LBL_DRIVER_TRIP_FARE_TXT']; ?></th>
                                                    <th class="align-center">Type<!-- <?= $langage_lbl_admin['LBL_TEXI_ADMIN']; ?>  --></th>
                                                    <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                        <th class="align-center">View Invoice</th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($db_trip)) {
                                                    for ($i = 0; $i < count($db_trip); $i++) {
                                                        $poolTxt = $seriveJson = "";
                                                        if ($db_trip[$i]['ePoolRide'] == "Yes") {
                                                            $poolTxt = " (Pool)";
                                                        }
                                                        $eTypenew = $db_trip[$i]['eType'];
                                                        $link_page = "invoice.php";
                                                        if ($eTypenew == 'Ride') {
                                                            $trip_type = 'Ride';
                                                        } else if ($eTypenew == 'UberX') {
                                                            $trip_type = 'Other Services';
                                                        } else if ($eTypenew == 'Multi-Delivery') {
                                                            $trip_type = 'Multi-Delivery';
                                                            $link_page = "invoice_multi_delivery.php";
                                                        } else {
                                                            $trip_type = 'Delivery';
                                                        }
                                                        $trip_type .= $poolTxt;
                                                        $viewService = 0;
                                                        if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                             // added by sunita
                                                            if(!empty($db_trip[$i]['vRentalVehicleTypeName'])){
                                                                $vehicleTypeName = $db_trip[$i]['vRentalVehicleTypeName'];
                                                            } else {
                                                                $vehicleTypeName = $db_trip[$i]['vVehicleType'];
                                                            }
                                                        } else {
                                                            $vehicleTypeName = $db_trip[$i]['vVehicleType'];
                                                        }
                                                        
                                                        if (isset($db_trip[$i]['tVehicleTypeData']) && $db_trip[$i]['tVehicleTypeData'] != "" && $vehicleTypeName == "") {
                                                            $viewService = 1;
                                                            $seriveJson = $db_trip[$i]['tVehicleTypeData'];
                                                        }
                                                        ?>
                                                        <tr class="gradeA">
                                                            <?php
                                                            if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
                                                                if ($_SESSION['SessionUserType'] != 'hotel') {
                                                                    ?> 
                                                                    <td align="left">
                                                                        <?php
                                                                        if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {
                                                                            echo "Rental " . $trip_type . "<br/> ( Hail )";
                                                                        } else if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                                            echo "Rental " . $trip_type;
                                                                        } else if ($db_trip[$i]['eHailTrip'] == "Yes") {
                                                                            echo "Hail " . $trip_type;
                                                                        } else {
                                                                            if (!empty($db_trip[$i]['iFromStationId']) && !empty($db_trip[$i]['iToStationId'])) {
                                                                                $trip_type = 'Fly';
                                                                            }
                                                                            echo $trip_type;
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <?php
                                                                }
                                                                if ($db_trip[$i]['eBookingFrom'] != '') {
                                                                    $eBookingFrom = $db_trip[$i]['eBookingFrom'];
                                                                } else {
                                                                    $eBookingFrom = $langage_lbl_admin['LBL_RIDER'];
                                                                }
                                                            }
                                                            if ($hotelPanel > 0 || $kioskPanel > 0) {
                                                                ?>
                                                                <td class="align-center"><?= $eBookingFrom; ?></td>
                                                                <?php
                                                            }
                                                            ?>
                                                            <td align="center">
                                                                <?= $db_trip[$i]['vRideNo']; ?>
                                                            </td>

                                                            <td width="30%"><?php
                                                                echo $db_trip[$i]['tSaddress'];

                                                                if ($APP_TYPE != "UberX" && $eTypenew != "Multi-Delivery" && !empty($db_trip[$i]['tDaddress'])) {
                                                                    echo ' -> ' . $db_trip[$i]['tDaddress'];
                                                                }
                                                                ?></td>                                                                                                                                        <!--<td><?= date('d F, Y', strtotime($db_trip[$i]['tStartDate'])); ?></td>-->                                                                                                                                        <!-- <td align="center"><?
                                                                if ($db_trip[$i]['tStartDate'] == "0000-00-00 00:00:00") {
                                                                    echo date('d-F-Y', strtotime($db_trip[$i]['tTripRequestDate']));
                                                                } else {
                                                                    echo date('d-F-Y', strtotime($db_trip[$i]['tStartDate']));
                                                                }
                                                                ?></td>-->

                                                            <td align="center"><?= date('d-F-Y', strtotime($db_trip[$i]['tTripRequestDate'])); ?></td>

                                                            <?php if ($_SESSION['SessionUserType'] != 'hotel') { ?>

                                                                <td> 

                                                                    <?= $generalobjAdmin->clearCmpName($db_trip[$i]['vCompany']); ?>

                                                                </td>

                                                            <?php } ?>

                                                            <td>

                                                                <?php if ($_SESSION['SessionUserType'] == 'hotel') { ?>

                                                                    <?php if ($userObj->hasPermission('view-providers')) { ?>

                                                                        <a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" >

                                                                        <?php } ?>



                                                                        <?= $generalobjAdmin->clearName($db_trip[$i]['driverName']); ?>



                                                                        <?php if ($userObj->hasPermission('view-providers')) { ?>		

                                                                        </a>

                                                                    <?php } ?>

                                                                <?php } else { ?>

                                                                    <?php if ($userObj->hasPermission('view-providers')) { ?>

                                                                        <a href="driver_action.php?id=<?= $db_trip[$i]['iDriverId'] ?>" target="_blank">

                                                                        <?php } ?>

                                                                        <?= $generalobjAdmin->clearName($db_trip[$i]['driverName']); ?>

                                                                        <?php if ($userObj->hasPermission('view-providers')) { ?>

                                                                        </a>

                                                                    <?php } ?>

                                                                <?php } ?>



                                                            </td>



                                                            <td>

                                                                <?php if ($_SESSION['SessionUserType'] == 'hotel') { ?>

                                                                    <?php if ($userObj->hasPermission('view-users')) { ?>

                                                                        <a href="javascript:void(0);" onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')" >

                                                                        <?php } ?>

                                                                        <?= $generalobjAdmin->clearName($db_trip[$i]['riderName']); ?>

                                                                        <?php if ($userObj->hasPermission('view-users')) { ?>

                                                                        </a>

                                                                    <?php } ?>

                                                                <?php } else { ?>

                                                                    <?php if ($db_trip[$i]['eHailTrip'] != "Yes") { ?>

                                                                        <?php if ($userObj->hasPermission('view-users')) { ?>

                                                                            <a href="rider_action.php?id=<?= $db_trip[$i]['iUserId'] ?>" target="_blank">

                                                                            <?php } ?>

                                                                            <?= $generalobjAdmin->clearName($db_trip[$i]['riderName']); ?>

                                                                            <?php if ($userObj->hasPermission('view-users')) { ?>

                                                                            </a>

                                                                        <?php } ?>

                                                                        <?php
                                                                    } else {

                                                                        echo " ---- ";
                                                                    }
                                                                }
                                                                ?>                                                           </td>

                                                            <td align="right">

                                                                <?php
                                                                $cancelprice = $db_trip[$i]['fCancellationFare'] + $db_trip[$i]['fWalletDebit'];

                                                                if ($db_trip[$i]['fCancellationFare'] > 0) {

                                                                    echo $generalobj->formateNumAsPerCurrency($db_trip[$i]['fCancellationFare'], '');
                                                                } else {
                                                                    echo $generalobj->formateNumAsPerCurrency($db_trip[$i]['iFare'], '');
                                                                }
                                                                ?>

                                                            </td>
                                                            <td align="center">
        <?php if ($viewService == 1) { ?>
                                                                    <button class="btn btn-success" data-trip="<?= $db_trip[$i]['vRideNo']; ?>" data-json='<?= $seriveJson; ?>' onclick="return showServiceModal(this);">
                                                                        <i class="fa fa-certificate icon-white"><b> View Service</b></i>
                                                                    </button>
            <?php
        } else {
            if ($db_trip[$i]['iRentalPackageId'] > 0) {
                // added by sunita
                if(!empty($db_trip[$i]['vRentalVehicleTypeName'])){
                    echo $db_trip[$i]['vRentalVehicleTypeName'];
                } else {
                    echo $db_trip[$i]['vVehicleType'];
                }
            } else {
                echo $db_trip[$i]['vVehicleType'];
            }
        }
        ?>
                                                            </td>
                                                                <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                                <td align="center" width="10%">
                                                                <?php if (($db_trip[$i]['iActive'] == 'Finished' && $db_trip[$i]['eCancelled'] == "Yes") || ($db_trip[$i]['fCancellationFare'] > 0) || ($db_trip[$i]['iActive'] == 'Canceled' && $db_trip[$i]['fWalletDebit'] > 0)) { ?>
                                                                        <button class="btn btn-primary" onclick='return !window.open("<?= $link_page ?>?iTripId=<?= $db_trip[$i]['iTripId'] ?>", "_blank")';">
                                                                            <i class="icon-th-list icon-white"><b>View Invoice</b></i>
                                                                        </button>
                                                                        <div style="font-size: 12px;">Cancelled</div>
            <?php } else if ($db_trip[$i]['iActive'] == 'Finished') { ?> 
                                                                        <button class="btn btn-primary" onclick='return !window.open("<?= $link_page ?>?iTripId=<?= $db_trip[$i]['iTripId'] ?>", "_blank")';">
                                                                            <i class="icon-th-list icon-white"><b>View Invoice</b></i>

                                                                        </button>

                                                                        <!-- </a> -->

                <?php
            } else {

                if ($db_trip[$i]['iActive'] == "Active" OR $db_trip[$i]['iActive'] == "On Going Trip") {

                    if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {

                        echo "On Job";
                    } else {

                        echo "On Ride";
                    }
                    ?>
                                                                            <br/> <!-- Commented By HJ On 11-01-2019 As Per Discuss withQA BM  -->
                                                                            <a href="javascript:void(0);" onClick="resetOnlyTripStatus('<?= $db_trip[$i]['iTripId']; ?>')" data-toggle="tooltip" title="Reset">
                                                                                Reset Trip</a>

                    <?php
                } else if ($db_trip[$i]['iActive'] == "Canceled" && ($db_trip[$i]['iCancelReasonId'] > 0 || $db_trip[$i]['vCancelReason'] != '')) {
                    ?>

                                                                            <a href="javascript:void(0);" class="btn btn-info" data-toggle="modal" data-target="#uiModal1_<?= $db_trip[$i]['iTripId']; ?>">Cancel Reason</a>



                    <?php
                } else if ($db_trip[$i]['iActive'] == "Canceled" && $db_trip[$i]['fWalletDebit'] < 0) {
                    echo "Cancelled";
                    ?>                                                                                                                                                                                                                                 <!--<a href="javascript:void(0);" onclick='javascript:window.open("cancel_trips_invoice.php?iTripId=<?= $db_trip[$i]['iTripId'] ?>","_blank")';"><button class="btn btn-primary"><i class="icon-th-list  icon-white"><b>Cancel Invoice</b></i></button> /a>-->                                                                       <?php
                                                                        } else {

                                                                            echo $db_trip[$i]['iActive'];
                                                                        }
                                                                    }
                                                                    //if (!empty($db_trip[$i]['vDeliveryConfirmCode']) && $DELIVERY_VERIFICATION_METHOD == "Code") {
                                                                    if ((!empty($db_trip[$i]['vDeliveryConfirmCode']) && (($db_trip[$i]['eType']=='Deliver') || ($db_trip[$i]['eType']=='Multi-Delivery' && $DELIVERY_VERIFICATION_METHOD == "Code"))) && ($db_trip[$i]['iActive']!='Finished' && $db_trip[$i]['iActive']!='Canceled')) {
                                                                        echo '<div style="margin-top:15px;">Delivery Confirmation Code: ' . $db_trip[$i]['vDeliveryConfirmCode'] . '</div>';
                                                                    }
                                                                    ?>

                                                                </td>

        <?php } ?>

                                                        </tr>

                                                    <div class="clear"></div>
                                                    <div class="modal fade" id="uiModal1_<?= $db_trip[$i]['iTripId']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                        <div class="modal-content image-upload-1" style="width:400px;">
                                                            <div class="upload-content" style="width:350px;">
                                                                <h3><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Cancel Reason</h3>	
                                                                <h4>Cancel Reason: <b style="font-size: 15px;font-weight: normal;">
        <?php
        if ($db_trip[$i]['iCancelReasonId'] > 0) {
            $cancelreasonarray = $generalobj->getCancelReason($db_trip[$i]['iCancelReasonId'], $default_lang);
            $db_trip[$i]['vCancelReason'] = $cancelreasonarray['vCancelReason'];
        }
        ?>

                                                                        <?= stripcslashes($db_trip[$i]['vCancelReason']); ?></b></h4>
                                                                        <?php if (!empty($db_trip[$i]['eCancelledBy'])) { ?>
                                                                    <h4>Cancel By: <b style="font-size: 15px;font-weight: normal;"><?= stripcslashes($db_trip[$i]['eCancelledBy']); ?></b></h4>
                                                                <?php } else { ?>
                                                                    <h4><b style="font-size: 15px;font-weight: normal;"></b></h4>
                                                                <?php } ?>
                                                                <input type="button" class="save" data-dismiss="modal" name="cancel" value="Close">
                                                            </div>
                                                        </div>
                                                    </div>
        <?php
    }
} else {
    ?>
                                                <tr class="gradeA">
                                                    <td colspan="11"> No Records Found.</td>
                                                </tr>
<?php } ?>
                                            </tbody>
                                        </table>
                                    </form>
<?php include('pagination_n.php'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="" method="post" >
            <input type="hidden" name="page" id="page" value="<?= $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
            <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?= $order; ?>" >
            <input type="hidden" name="action" value="<?= $action; ?>" >
            <input type="hidden" name="searchCompany" value="<?= $searchCompany; ?>" >
            <input type="hidden" name="searchDriver" value="<?= $searchDriver; ?>" >
            <input type="hidden" name="searchRider" value="<?= $searchRider; ?>" >
            <input type="hidden" name="serachTripNo" value="<?= $serachTripNo; ?>" >
            <input type="hidden" name="startDate" value="<?= $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?= $endDate; ?>" >
            <input type="hidden" name="vStatus" value="<?= $vStatus; ?>" >
            <input type="hidden" name="eType" value="<?= $eType; ?>" >
            <input type="hidden" name="promocode" value="<?= $promocode; ?>" >
            <!-- for reset -->
            <input type="hidden" name="iTripId" id="iMainId01" value="" >
            <input type="hidden" name="method" id="method" value="" >
        </form>

        <div  class="modal fade" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="servicetitle">
                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                            Service Details
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <div id="service_detail"></div>
                    </div>
                </div>
            </div>
        </div>
        <div  class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>
                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                            Driver Details
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <div id="imageIcons" style="display:none">
                            <div align="center">                                                                       
                                <img src="default.gif"><br/>                                                            
                                <span>Retrieving details,please Wait...</span>                       
                            </div>    
                        </div>
                        <div id="driver_detail"></div>
                    </div>
                </div>
            </div>
        </div>

        <div  class="modal fade " id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>
                            <i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>
<?= $langage_lbl_admin['LBL_RIDER']; ?> Details
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <div id="imageIcons1">
                            <div align="center">                                                                       
                                <img src="default.gif"><br/>                                                            
                                <span>Retrieving details,please Wait...</span>                       
                            </div>    
                        </div>
                        <div id="rider_detail" ></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Reset Trip Status Modal -->
        <div data-backdrop="static" data-keyboard="false" class="modal fade" id="is_resetTrip_modal_trip" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h4>Reset Record(s) ?</h4></div>
                    <div class="modal-body">
                        <p>Resetting <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> will end the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> and release the <?= strtolower($langage_lbl_admin['LBL_RIDER']); ?> and <?= strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?>. Confirm to reset <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?>?
                        </p>
                        <br/>
<?php /* 1. <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> will be marked as cancelled and fare of <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> will set to 0.Please use this feature only when rider & driver are stuck in a trip. */ ?>
                        <p>Note:<br/>
                            <br/> 1. <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> status will be marked as Cancelled and Fare of <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'];?>  will set to 0. Please use this feature only when <?= $langage_lbl_admin['LBL_RIDER']?> & <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']?> are stuck in the application and under any circumstances, they want to end the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?>.
                            <br/>
                            <br/>
                            2. Please restart the <?= $langage_lbl_admin['LBL_RIDER']?> and <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']?> application once the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> is reset.</p>
                    </div>

                    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button><a class="btn btn-success btn-ok action_modal_submit">Yes</a></div>
                </div>
            </div>
        </div>
<? include_once('footer.php'); ?>
        <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css" />
        <script src="js/plugins/select2.min.js"></script>
        <script src="../assets/js/jquery-ui.min.js"></script>
        <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
        <script>
            var startDate;
            var endDate;
            var typeArr = '<?= json_encode($vehilceTypeArr, JSON_HEX_APOS); ?>';
            $('#dp4').datepicker()
                    .on('changeDate', function (ev) {
                        startDate = new Date(ev.date);
                        if (endDate != null) {
                            if (ev.date.valueOf() < endDate.valueOf()) {
                                $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                            } else {
                                $('#alert').hide();
                                $('#startDate').text($('#dp4').data('date'));
                            }
                        }
                        $('#dp4').datepicker('hide');
                    });
            $('#dp5').datepicker()
                    .on('changeDate', function (ev) {
                        endDate = new Date(ev.date);
                        if (startDate != null) {
                            if (ev.date.valueOf() < startDate.valueOf()) {
                                $('#alert').show().find('strong').text('The end date can not be less then the start date');
                            } else {
                                $('#alert').hide();
                                $('#endDate').text($('#dp5').data('date'));
                            }
                        }
                        $('#dp5').datepicker('hide');
                    });
            $(document).ready(function () {
                if ('<?= $startDate ?>' != '') {
                    $("#dp4").val('<?= $startDate ?>');
                    $("#dp4").datepicker('update', '<?= $startDate ?>');
                }
                if ('<?= $endDate ?>' != '') {
                    $("#dp5").datepicker('update', '<?= $endDate; ?>');
                    $("#dp5").val('<?= $endDate; ?>');
                }
            });
            function showServiceModal(elem) {
                var tripJson = JSON.parse($(elem).attr("data-json"));
                var rideNo = $(elem).attr("data-trip");
                var typeNameArr = JSON.parse(typeArr)
                var serviceHtml = "";
                var srno = 1;
                // added by sunita
                for (var g = 0; g < tripJson.length; g++) {
                    serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?=$langage_lbl_admin['LBL_QTY_TXT']?>: <b>"+ [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
                    srno++;
                }
                $("#service_detail").html(serviceHtml);
                $("#servicetitle").text("Service Details : " + rideNo);
                $("#service_modal").modal('show');
                return false;
            }
            function setRideStatus(actionStatus) {
                window.location.href = "trip.php?type=" + actionStatus;
            }
            function todayDate()
            {
                $("#dp4").val('<?= $Today; ?>');
                $("#dp5").val('<?= $Today; ?>');
            }
            function reset() {
                location.reload();
            }
            function yesterdayDate()
            {
                $("#dp4").val('<?= $Yesterday; ?>');
                $("#dp4").datepicker('update', '<?= $Yesterday; ?>');
                $("#dp5").datepicker('update', '<?= $Yesterday; ?>');
                $("#dp4").change();
                $("#dp5").change();
                $("#dp5").val('<?= $Yesterday; ?>');
            }
            function currentweekDate(dt, df)
            {
                $("#dp4").val('<?= $monday; ?>');
                $("#dp4").datepicker('update', '<?= $monday; ?>');
                $("#dp5").datepicker('update', '<?= $sunday; ?>');
                $("#dp5").val('<?= $sunday; ?>');
            }
            function previousweekDate(dt, df)
            {
                $("#dp4").val('<?= $Pmonday; ?>');
                $("#dp4").datepicker('update', '<?= $Pmonday; ?>');
                $("#dp5").datepicker('update', '<?= $Psunday; ?>');
                $("#dp5").val('<?= $Psunday; ?>');
            }
            function currentmonthDate(dt, df)
            {
                $("#dp4").val('<?= $currmonthFDate; ?>');
                $("#dp4").datepicker('update', '<?= $currmonthFDate; ?>');
                $("#dp5").datepicker('update', '<?= $currmonthTDate; ?>');
                $("#dp5").val('<?= $currmonthTDate; ?>');
            }
            function previousmonthDate(dt, df)
            {
                $("#dp4").val('<?= $prevmonthFDate; ?>');
                $("#dp4").datepicker('update', '<?= $prevmonthFDate; ?>');
                $("#dp5").datepicker('update', '<?= $prevmonthTDate; ?>');
                $("#dp5").val('<?= $prevmonthTDate; ?>');
            }
            function currentyearDate(dt, df)
            {
                $("#dp4").val('<?= $curryearFDate; ?>');
                $("#dp4").datepicker('update', '<?= $curryearFDate; ?>');
                $("#dp5").datepicker('update', '<?= $curryearTDate; ?>');
                $("#dp5").val('<?= $curryearTDate; ?>');
            }
            function previousyearDate(dt, df)
            {
                $("#dp4").val('<?= $prevyearFDate; ?>');
                $("#dp4").datepicker('update', '<?= $prevyearFDate; ?>');
                $("#dp5").datepicker('update', '<?= $prevyearTDate; ?>');
                $("#dp5").val('<?= $prevyearTDate; ?>');
            }
            $("#Search").on('click', function () {
                if ($("#dp5").val() < $("#dp4").val()) {
                    alert("From date should be lesser than To date.")
                    return false;
                } else {
                    var action = $("#_list_form").attr('action');
                    var formValus = $("#frmsearch").serialize();
                    window.location.href = action + "?" + formValus;
                }
            });
            $(function () {
                $("select.filter-by-text").each(function () {
                    $(this).select2({
                        placeholder: $(this).attr('data-text'),
                        allowClear: true
                    }); //theme: 'classic'
                });
            });
            $('#searchCompany').change(function () {
                var company_id = $(this).val(); //get the current value's option
                $.ajax({
                    type: 'POST',
                    url: 'ajax_find_driver_by_company.php',
                    data: {'company_id': company_id},
                    cache: false,
                    success: function (data) {
                        $(".driver_container").html(data);
                    }
                });
            });
            function show_driver_details(driverid) {
                $("#driver_detail").html('');
                $("#imageIcons").show();
                $("#detail_modal").modal('show');
                if (driverid != "") {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_driver_details.php",
                        data: "iDriverId=" + driverid,
                        datatype: "html",
                        success: function (data) {
                            $("#driver_detail").html(data);
                            $("#imageIcons").hide();
                        }
                    });
                }
            }
            function show_rider_details(userid) {
                $("#rider_detail").html('');
                $("#imageIcons").show();
                $("#detail_modal1").modal('show');
                if (userid != "") {
                    var request = $.ajax({
                        type: "POST",
                        url: "ajax_rider_details.php",
                        data: "iUserId=" + userid,
                        datatype: "html",
                        success: function (data) {
                            $("#rider_detail").html(data);
                            $("#imageIcons1").hide();
                        }
                    });
                }
            }
            function resetOnlyTripStatus(iAdminId) {
                $('#is_resetTrip_modal_trip').modal('show');
                $(".action_modal_submit").unbind().click(function () {
                    var action = $("#pageForm").attr('action');
                    var page = $("#pageId").val();
                    $("#pageId01").val(page);
                    $("#iMainId01").val(iAdminId);
                    $("#method").val('reset');
                    var formValus = $("#pageForm").serialize();
                    window.location.href = action + "?" + formValus;
                });
            }
        </script>
    </body>
    <!-- END BODY-->
</html>
