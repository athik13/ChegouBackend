<?php
include_once '../common.php';

if (!isset($generalobjAdmin)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('manage-ride-job-later-bookings')) {
    $userObj->redirect();
}
$APP_DELIVERY_MODE = $generalobj->getConfigurations("configurations", "APP_DELIVERY_MODE");

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

$script = 'CabBooking';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';

$ord = ' ORDER BY cb.iCabBookingId DESC';
if ($sortby == 1) {
    if ($order == 0) {
        $ord = " ORDER BY ru.vName ASC";
    } else {
        $ord = " ORDER BY ru.vName DESC";
    }
}
if ($sortby == 2) {
    if ($order == 0) {
        $ord = " ORDER BY cb.dBooking_date ASC";
    } else {
        $ord = " ORDER BY cb.dBooking_date DESC";
    }
}
if ($sortby == 3) {
    if ($order == 0) {
        $ord = " ORDER BY cb.vSourceAddresss ASC";
    } else {
        $ord = " ORDER BY cb.vSourceAddresss DESC";
    }
}
if ($sortby == 4) {
    if ($order == 0) {
        $ord = " ORDER BY cb.tDestAddress ASC";
    } else {
        $ord = " ORDER BY cb.tDestAddress DESC";
    }
}
if ($sortby == 5) {
    if ($order == 0) {
        $ord = " ORDER BY cb.eStatus ASC";
    } else {
        $ord = " ORDER BY cb.eStatus DESC";
    }
}
if ($sortby == 6) {
    if ($order == 0) {
        $ord = " ORDER BY cb.vBookingNo ASC";
    } else {
        $ord = " ORDER BY cb.vBookingNo DESC";
    }
}
if ($sortby == 7) {
    if ($order == 0) {
        $ord = " ORDER BY cb.eType ASC";
    } else {
        $ord = " ORDER BY cb.eType DESC";
    }
}
//End Sorting

$adm_ssql = "";
if (SITE_TYPE == 'Demo') {
    $adm_ssql = " And cb.dAddredDate > '" . WEEK_DATE . "'";
}
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
$ssql = '';
if ($eType == 'RentalRide') {
    $eType_new = 'Ride';
    $sql11 = " AND cb.iRentalPackageId > 0";
} else {
    $eType_new = $eType;
    $sql11 = 'AND cb.iRentalPackageId = 0';
}
$eStatus =$searchusertype= '';
if(isset($_REQUEST['searchusertype'])){
    $searchusertype = $_REQUEST['searchusertype'];
}
if ($keyword != '') {
    if ($option != '') {
        if ($option == 'user') {
            if ($eType_new != '') {
                $ssql .= " AND CONCAT(ru.vName,' ',ru.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND cb.eType = '" . $generalobjAdmin->clean($eType_new) . "' $sql11";
            } else {
                $ssql .= " AND CONCAT(ru.vName,' ',ru.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' $sql11";
            }
        } else {
            if ($eType_new != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' AND cb.eType = '" . $generalobjAdmin->clean($eType_new) . "' $sql11";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword) . "%' $sql11";
            }
        }
    } else {

        if ($eType_new != '') {
            $ssql .= " AND (CONCAT(ru.vName,' ',ru.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR cb.tDestAddress LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR cb.vSourceAddresss   LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR cb.vBookingNo LIKE '" . $generalobjAdmin->clean($keyword) . "' OR cb.eStatus LIKE '%" . $generalobjAdmin->clean($keyword) . "%') AND cb.eType = '" . $generalobjAdmin->clean($eType_new) . "' $sql11";
        } else {
            $ssql .= " AND (CONCAT(ru.vName,' ',ru.vLastName) LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR cb.tDestAddress LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR cb.vSourceAddresss  LIKE '%" . $generalobjAdmin->clean($keyword) . "%' OR cb.vBookingNo LIKE '" . $generalobjAdmin->clean($keyword) . "' OR cb.eStatus LIKE '%" . $generalobjAdmin->clean($keyword) . "%') $sql11";
        }
    }
} else if ($eType_new != '' && $keyword == '') {
    $ssql .= " AND cb.eType = '" . $generalobjAdmin->clean($eType_new) . "' $sql11";
} elseif ($option == 'cb.eStatus' && !empty($searchusertype)) {
    $eStatus = $searchusertype;
    if ($searchusertype == 'Expired') { //changed by me
        $ssql .= " AND ((cb.eStatus LIKE '%Pending%' or cb.eStatus LIKE '%Accepted%') AND DATE( NOW( ) ) >= DATE_ADD( DATE( cb.dBooking_date ) , INTERVAL 10 MINUTE )) " . $sql11;
    } else if ($searchusertype == 'Completed') {
        $ssql .= " AND ((cb.eStatus LIKE '%Completed%') AND DATE( NOW( ) ) >= DATE_ADD( DATE( cb.dBooking_date ) , INTERVAL 10 MINUTE )) " . $sql11;
    } else {
        $ssql .= " AND cb.eStatus LIKE '%" . $generalobjAdmin->clean($searchusertype) . "%' " . $sql11;
    }
}
//$ufxEnable = $generalobj->CheckUfxServiceAvailable();
$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";

if($ufxEnable != "Yes") {
	$ssql .= " AND cb.eType != 'UberX'";
}
if(!checkFlyStationsModule(1)) {
    $ssql.= " AND cb.iFromStationId = '0' AND cb.iToStationId = '0'";
}
if($rideEnable != "Yes") {
    $ssql .= " AND cb.eType != 'Ride'";
}
if($deliveryEnable != "Yes") {
    $ssql .= " AND cb.eType != 'Deliver' AND cb.eType != 'Multi-Delivery'";
}
//if(!isDeliverAllModuleAvailable()) {
//    $ssql .= " AND cb.eType != 'DeliverAll'";  
//}

// End Search Parameters
//	exit;
if (count($userObj->locations) > 0) {
    $locations = implode(', ', $userObj->locations);
    $ssql .= " AND vt.iLocationid IN(-1, {$locations}) ";
}

if (!empty($promocode) && isset($promocode)) {
    $ssql .= " AND cb.vCouponCode LIKE '" . $promocode . "' AND cb.eStatus != 'Completed' AND cb.eStatus != 'Cancel' ";
}


$hotelQuery =$userType= "";
if(isset($_SESSION['SessionUserType'])){
    $userType = strtolower($_SESSION['SessionUserType']);
}
if ($userType == 'hotel') {
    $iHotelBookingId = $_SESSION['sess_iAdminUserId'];
    $hotelQuery = " And cb.eBookingFrom = 'Hotel' AND cb.iHotelBookingId = '" . $iHotelBookingId . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
//$sql = "SELECT COUNT(cb.iCabBookingId) as Total FROM cab_booking as cb
//	 LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId
//	 LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId
//	 LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE 1=1 $ssql $adm_ssql $hotelQuery";
$sql = "SELECT COUNT(cb.iCabBookingId) as Total FROM cab_booking as cb
	 LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId
	 LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId
	 LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId
     LEFT JOIN trips AS t ON t.iTripId = cb.iTripId WHERE 1=1 $ssql $adm_ssql $hotelQuery";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];

$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page']; //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) {
    $page = 1;
}
//Pagination End
//print_R($_REQUEST['searchusertype']);

$sql = "SELECT cb.*,CONCAT(ru.vName,' ',ru.vLastName) as rider,CONCAT(rd.vName,' ',rd.vLastName) as driver,vt.vVehicleType,vt.vRentalAlias_" . $default_lang . " as vRentalVehicleTypeName FROM cab_booking as cb LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE 1=1 $ssql $adm_ssql $hotelQuery $ord LIMIT $start, $per_page";

$data_drv = $obj->MySQLSelect($sql);

//changed by me start
if ($searchusertype == 'Completed') {
    foreach ($data_drv as $key_com => $val_com) {
        $sql_trip = "select iActive, eCancelledBy from trips where iTripId=" . $data_drv[$key_com]['iTripId'];
        $data_trip = $obj->MySQLSelect($sql_trip);
        if (!empty($data_trip)) {
            if ($data_trip[0]['iActive'] == "Canceled" && $data_trip[0]['eCancelledBy'] == "Driver") {
                
            } else {
                $cabbookingid[] = $val_com['iCabBookingId'];
            }
        }
    }
    $cabbookingid_implode = implode(",", $cabbookingid);
    $ssql .= " AND cb.iCabBookingId IN($cabbookingid_implode)";
    $sql = "SELECT cb.*,CONCAT(ru.vName,' ',ru.vLastName) as rider,CONCAT(rd.vName,' ',rd.vLastName) as driver,vt.vVehicleType,vt.vRentalAlias_" . $default_lang . " as vRentalVehicleTypeName FROM cab_booking as cb LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE 1=1 $ssql $adm_ssql $hotelQuery $ord LIMIT $start, $per_page";
    $data_drv = $obj->MySQLSelect($sql);

    //For pagination change
    $sql = "SELECT COUNT(cb.iCabBookingId) as Total FROM cab_booking as cb
	 LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId
	 LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId
     LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE 1=1 $ssql $adm_ssql $hotelQuery";
     

    $totalData = $obj->MySQLSelect($sql);
    $total_results = $totalData[0]['Total'];

    $total_pages = ceil($total_results / $per_page); //total pages we going to have
    $show_page = 1;
    //-------------if page is setcheck------------------//
    $start = 0;
    $end = $per_page;
    if (isset($_GET['page'])) {
        $show_page = $_GET['page']; //it will telles the current page
        if ($show_page > 0 && $show_page <= $total_pages) {
            $start = ($show_page - 1) * $per_page;
            $end = $start + $per_page;
        }
    }
    // display pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $tpages = $total_pages;
    if ($page <= 0) {
        $page = 1;
    }
}
//changed by me end

$endRecord = count($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$systemTimeZone = date_default_timezone_get();
//$systemTimeZone = $_COOKIE['vUserDeviceTimeZone'];
//echo $systemTimeZone;
//echo $_COOKIE['vUserDeviceTimeZone'];
//exit;
function converToTz($time, $toTz, $fromTz, $dateFormat = "Y-m-d H:i:s") {
    $date = new DateTime($time, new DateTimeZone($fromTz));
    $date->setTimezone(new DateTimeZone($toTz));
    $time = $date->format($dateFormat);
    return $time;
}

if ($action == 'delete' && $hdn_del_id != '') {
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
    $cancelreason = isset($_REQUEST['cancel_reason']) ? $_REQUEST['cancel_reason'] : '';
    $query = "UPDATE cab_booking SET eStatus = 'Cancel',eAutoAssign = 'No', eCancelBy= 'Admin',`vCancelReason`='" . $cancelreason . "',`dCancelDate` = NOW() WHERE iCabBookingId = '" . $hdn_del_id . "'";
    $obj->sql_query($query);
    $sql1 = "select * from cab_booking where iCabBookingId=" . $hdn_del_id;
    $bookind_detail = $obj->MySQLSelect($sql1);
    $dBooking_date = $bookind_detail[0]['dBooking_date'];
    $vBookingNo = $bookind_detail[0]['vBookingNo'];
    $vSourceAddresss = $bookind_detail[0]['vSourceAddresss'];
    
    $dBookingDate = converToTz($bookind_detail[0]['dBooking_date'], $bookind_detail[0]['vTimeZone'], $systemTimeZone);
    $dBookingDate_new_mail = date("jS F, Y", strtotime($dBookingDate));
    $dBookingDate_new_mail_time = date("h:i A", strtotime($dBookingDate));
    $dBookingDate_new_mail_date = $dBookingDate_new_mail;
    $dBookingDate_new_mail = $dBookingDate_new_mail." ".$langage_lbl_admin['LBL_AT_TXT']." ".$dBookingDate_new_mail_time;
    
    //$dBookingDate_new = date('Y-m-d H:i', strtotime($dBookingDate));
    //$dBookingDate_new_mail = date('Y-m-d H:i A', strtotime($dBookingDate)); //added by SP for date format in mail from issue#332 on 03-10-2019 

    //$sql2 = "select vName,vLastName,vEmail,iDriverVehicleId,vPhone,vcode,vLang from register_driver where iDriverId=" . $iDriverId;
    //$driver_db = $obj->MySQLSelect($sql2);
    $driver_db = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode,r.vName,r.vLastName,r.vEmail,r.iDriverVehicleId,r.vLang FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iDriverId AND r.vCountry = c.vCountryCode"); //added by SP for sms functionality on 13-7-2019
    $vPhone = $driver_db[0]['vPhone'];
    //$vcode = $driver_db[0]['vcode'];
    $vcode = $driver_db[0]['vPhoneCode']; //added by SP for sms functionality on 13-7-2019
    $vLang = $driver_db[0]['vLang'];

    //$SQL3 = "SELECT vName,vLastName,vEmail,iUserId,vPhone,vPhoneCode,vLang FROM register_user WHERE iUserId = '$iUserId'";
    //$user_detail = $obj->MySQLSelect($SQL3);
    $user_detail = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode,r.vName,r.vLastName,r.vEmail,r.vLang,r.iUserId FROM `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
    $vPhone1 = $user_detail[0]['vPhone'];
    $vcode1 = $user_detail[0]['vPhoneCode']; //added by SP for sms functionality on 13-7-2019
    $vLang1 = $user_detail[0]['vLang'];

    $Data1['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $Data1['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $Data1['vRiderMail'] = $user_detail[0]['vEmail'];
    $Data1['vSourceAddresss'] = $vSourceAddresss;
    //$Data1['dBookingdate'] = $dBooking_date;
    $Data1['dBookingdate'] = $dBookingDate_new_mail;
    $Data1['vBookingNo'] = $vBookingNo;

    $Data['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $Data['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $Data['vDriverMail'] = $driver_db[0]['vEmail'];
    $Data['vSourceAddresss'] = $vSourceAddresss;
    //$Data['dBookingdate'] = $dBooking_date;
    $Data['dBookingdate'] = $dBookingDate_new_mail;
    $Data['vBookingNo'] = $vBookingNo;

    $return = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_DRIVER", $Data);
    $return1 = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_RIDER", $Data1);

    $Booking_Date = @date('d-m-Y', strtotime($dBooking_date));
    $Booking_Time = @date('H:i:s', strtotime($dBooking_date));

    $maildata['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $maildata['dBookingdate'] = $dBookingDate_new_mail_date;
    $maildata['dBookingtime'] = $dBookingDate_new_mail_time;
    $maildata['vBookingNo'] = $vBookingNo;

    $maildata1['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $maildata1['dBookingdate'] = $dBookingDate_new_mail_date;
    $maildata1['dBookingtime'] = $dBookingDate_new_mail_time;
    $maildata1['vBookingNo'] = $vBookingNo;
    $message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE_JOB_CANCEL", $maildata1, "", $vLang);
    //$return5 = $generalobj->sendUserSMS($vPhone, $vcode, $message_layout, "");
    $return5 = $generalobj->sendSystemSms($vPhone,$vcode,$message_layout); //added by SP for sms functionality on 13-7-2019
    $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_JOB_CANCEL", $maildata, "", $vLang1);
    //$return4 = $generalobj->sendUserSMS($vPhone1, $vcode1, $message_layout, "");
    $return4 = $generalobj->sendSystemSms($vPhone1,$vcode1,$message_layout); //added by SP for sms functionality on 13-7-2019
    echo "<script>location.href='cab_booking.php'</script>";
    //header("Location:cab_booking.php");
    //exit;
}
$driverSql = "select iDriverId,vName,vLastName,vEmail,vPhone,vCode from register_driver where eStatus='active'";
$driverData = $obj->MySQLSelect($driverSql);
$vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0; $r < count($getVehicleTypes); $r++) {
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
}
//Added By HJ On 04-06-2019 For Get CancelReason Data Start
$getCancelReasons = $obj->MySQLSelect("SELECT vTitle_" . $default_lang . ",iCancelReasonId FROM cancel_reason");
$reasonArr = array();
for ($c = 0; $c < count($getCancelReasons); $c++) {
    $reasonArr[$getCancelReasons[$c]['iCancelReasonId']] = $getCancelReasons[$c]['vTitle_' . $default_lang];
}
//Added By HJ On 04-06-2019 For Get CancelReason Data End
//echo "<pre>";print_r($reasonArr);die;
$hotelPanel = isHotelPanelEnable(); 
$kioskPanel = isKioskPanelEnable();
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN']; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once 'global_files.php'; ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once 'header.php'; ?>
            <?php include_once 'left_menu.php'; ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2><?= $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN']; ?></h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include 'valid_msg.php'; ?> 



                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="1%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <?php
                                    if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                                        if ($userType != 'hotel') {
                                            ?>
                                            <td width="5%" class="padding-right10">
                                                <select class="form-control" name = 'eType' >
                                                    <option value="">Service Type</option>
                                                    <?php if($rideEnable == "Yes") { ?>
                                                    <option value="Ride" <?php
                                                    if ($eType == "Ride") {
                                                        echo "selected";
                                                    }
                                                    ?>><?php echo $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                                    <? if (ENABLE_RENTAL_OPTION == 'Yes') { ?>
                                                        <option value="RentalRide" <?php
                                                        if ($eType == "RentalRide") {
                                                            echo "selected";
                                                        }
                                                        ?>>Rental <?php echo $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                                    <? } }
                                                    if($deliveryEnable == "Yes") {
                                                    ?>
                                                    <option value="Deliver" <?php
                                                    if ($eType == "Deliver") {
                                                        echo "selected";
                                                    }
                                                    ?>>Delivery</option>
                                                    <? } if($ufxEnable == "Yes") { ?>
                                                    <option value="UberX" <?php
                                                    if ($eType == "UberX") {
                                                        echo "selected";
                                                    }
                                                    ?>>Other Services</option>
                                                    <? } ?>
                                                </select>
                                            </td>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <td width="10%" class=" padding-right10">
                                        <select name="option" id="option" class="form-control">
                                            <option value="">All</option>
                                            <option value="user" <?php
                                            if ($option == "user") {
                                                echo "selected";
                                            }
                                            ?> ><?= $langage_lbl_admin['LBL_RIDERS_ADMIN']; ?></option>
                                            <option value="cb.vSourceAddresss" <?php
                                            if ($option == 'cb.vSourceAddresss') {
                                                echo "selected";
                                            }
                                            ?> >Expected Source Location </option>
                                                    <? if ($APP_TYPE != "UberX") { ?>
                                                <option value="cb.tDestAddress" <?php
                                                if ($option == 'cb.tDestAddress') {
                                                    echo "selected";
                                                }
                                                ?> >Expected Destination Location</option>
                                                    <?php } ?>
                                            <option value="cb.vBookingNo" <?php
                                            if ($option == 'cb.vBookingNo') {
                                                echo "selected";
                                            }
                                            ?> >Booking Number </option>
                                            <option value="cb.eStatus" <?php
                                            if ($option == 'cb.eStatus') {
                                                echo "selected";
                                            }
                                            ?> >Status</option>
                                        </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="15%" class="usertype_options" id="usertype_options" >
                                        <select class="form-control" name ="searchusertype" id="searchusertype" required="required">
                                            <option value="">Select Status</option>
                                            <option value="Cancel"  <?php
                                            if ($searchusertype == 'Cancel') {
                                                echo 'selected';
                                            }
                                            ?>>Cancelled</option>
                                            <option value="Declined" <?php
                                            if ($searchusertype == 'Declined') {
                                                echo 'selected';
                                            }
                                            ?>>Declined</option>
                                            <option value="Completed" <?php
                                            if ($searchusertype == 'Completed') {
                                                echo 'selected';
                                            }
                                            ?>>Finished</option>
                                            <option value="Expired" <?php
                                            if ($searchusertype == 'Expired') {
                                                echo 'selected';
                                            }
                                            ?>>Expired</option>
                                        </select>
                                    </td>
                                    <td width="12%">
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'cab_booking.php'"/>
                                        <?php if (!empty($data_drv)) { ?>
<!--                                            <button type="button" onClick="reportExportTypes('cab_booking')" class="export-btn001"  style="float:none;">Export</button></b>-->
                                        <?php } ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">

                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <?php
                                                    if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                                                        if ($userType != 'hotel') {
                                                            ?>
                                                            <th width="10%" class="align-left"><a href="javascript:void(0);" onClick="Redirect(7,<?php
                                                                if ($sortby == '7') {
                                                                    echo $order;
                                                                } else {
                                                                    ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']; ?> <?php
                                                                                                      if ($sortby == 7) {
                                                                                                          if ($order == 0) {
                                                                                                              ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                        }
                                                                    } else {
                                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                            <?
                                                            if ($hotelPanel > 0 || $kioskPanel  > 0) {
                                                                if ($userType != 'hotel') {
                                                                    ?>
                                                            <th>Booked By</th>
                                                            <?
                                                        }
                                                    }
                                                    ?>
                                                    <th width="12%"><a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                        if ($sortby == '6') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_MYTRIP_RIDE_NO']; ?><?php
                                                                           if ($sortby == 6) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDERS_ADMIN']; ?><?php
                                                                        if ($sortby == 1) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == '2') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"> Date <?php
                                                                        if ($sortby == 2) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width=""><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                        if ($sortby == '3') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Expected Source Location <?php
                                                                        if ($sortby == 3) {
                                                                            if ($order == 0) {
                                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                            <? if ($APP_TYPE != "UberX") { ?>
                                                        <th width=""><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                            if ($sortby == '4') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)">Expected Destination Location <?php
                                                                            if ($sortby == 4) {
                                                                                if ($order == 0) {
                                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                            <?php } ?>
                                                    <th width="" align="left" style="text-align:left;"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></th>

                                                    <th><?= $langage_lbl_admin['LBL_TRIP_DETAILS']; ?></th>

                                                    <th width="" align="left" style="text-align:left;"><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                        if ($sortby == '5') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Status <?php
                                                                                                              if ($sortby == 5) {
                                                                                                                  if ($order == 0) {
                                                                                                                      ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < count($data_drv); $i++) {
                                                        $setcurrentTime = strtotime(date('Y-m-d H:i:s'));
                                                        $bookingdate = date("H:i", strtotime('+30 minutes', strtotime($data_drv[$i]['dBooking_date'])));
                                                        $bookingdatecmp = strtotime($bookingdate);
                                                        $default = $seriveJson = '';
                                                        if (isset($data_drv[$i]['eDefault']) && $data_drv[$i]['eDefault'] == 'Yes') {
                                                            $default = 'disabled';
                                                        }
                                                        //Added By HJ On 04-06-2019 For Get Cancel Reason Start
                                                        $cancelReasonTxt = $data_drv[$i]['vCancelReason'];
                                                        if (isset($data_drv[$i]['iCancelReasonId']) && $data_drv[$i]['iCancelReasonId'] > 0) {
                                                            if (isset($reasonArr[$data_drv[$i]['iCancelReasonId']])) {
                                                                $cancelReasonTxt = $reasonArr[$data_drv[$i]['iCancelReasonId']];
                                                            }
                                                        }
                                                        //Added By HJ On 04-06-2019 For Get Cancel Reason End
                                                        //Added By HJ On 04-06-2019 For Get Cancel Reason By Nane Start
                                                        $eCancelBy = $data_drv[$i]['eCancelBy'];
                                                        /*if ($APP_TYPE != "UberX") { // hide it by SP becoz for all the cases word comes from the label only
                                                            $eCancelBy = $data_drv[$i]['eCancelBy'];
                                                        } else {*/
                                                            if ($data_drv[$i]['eCancelBy'] == "Driver") {
                                                                $eCancelBy = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
                                                            } else if ($data_drv[$i]['eCancelBy'] == "Rider") {
                                                                $eCancelBy = $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];
                                                            }
                                                        //}
                                                        //Added By HJ On 04-06-2019 For Get Cancel Reason By Nane End
                                                        //Added By HJ On 08-02-2019 For Get Vehicle Parent Category Name Start
                                                        $viewService = 0;
                                                        if ($data_drv[$i]['tVehicleTypeData'] != "" && $data_drv[$i]['vVehicleType'] == "") {
                                                            $typeData = json_decode($data_drv[$i]['tVehicleTypeData']);
                                                            $viewService = 1;
                                                            $seriveJson = $data_drv[$i]['tVehicleTypeData'];
                                                            if (count($typeData) > 0) {
                                                                $typeId = $typeData[0]->iVehicleTypeId;
                                                                $getMainCat = $obj->MySQLSelect("SELECT VC.vCategory_" . $default_lang . " AS vVehicleCategory,VT.iVehicleCategoryId,if(VC.iParentId >0,(SELECT vCategory_" . $default_lang . " FROM ".$sql_vehicle_category_table_name." VC1 WHERE VC.iParentId=VC1.iVehicleCategoryId),'') AS vVehicleCategory FROM vehicle_type VT INNER JOIN ".$sql_vehicle_category_table_name." VC ON VT.iVehicleCategoryId=VC.iVehicleCategoryId WHERE iVehicleTypeId='" . $typeId . "'");
                                                                if (count($getMainCat) > 0) {
                                                                    $data_drv[$i]['vVehicleType'] = $getMainCat[0]['vVehicleCategory'];
                                                                }
                                                            }
                                                        }
                                                        //Added By HJ On 08-02-2019 For Get Vehicle Parent Category Name End
                                                        $eType_new = $data_drv[$i]['eType'];
                                                        if ($eType_new == 'Ride' && $data_drv[$i]['iRentalPackageId'] > 0) {
                                                            $trip_type = 'Rental Ride';
                                                        } else if ($eType_new == 'Ride') {
                                                            $trip_type = 'Ride';
                                                        } else if ($eType_new == 'UberX') {
                                                            $trip_type = 'Other Services';
                                                        } else if ($eType_new == 'Deliver') {
                                                            $trip_type = 'Delivery';
                                                        }
                                                        if ($trip_type == 'Other Services') {
                                                            $service_type = $langage_lbl_admin['LBL_MYTRIP_TRIP_TYPE'];
                                                        } else {
                                                            $service_type = $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];
                                                        }
                                                        if (!empty($data_drv[$i]['iFromStationId']) && !empty($data_drv[$i]['iToStationId'])) {
                                                            $trip_type = 'Fly';
                                                            $service_type = " Fly Type";

                                                        }
                                                        $rentalquery = 'SELECT vPackageName_' . $default_lang . ' as pkgName FROM rental_package WHERE iRentalPackageId = "' . $data_drv[$i]['iRentalPackageId'] . '"';
                                                        $rental_data = $obj->MySQLSelect($rentalquery);

                                                        if ($data_drv[$i]['eBookingFrom'] != '') {
                                                            $eBookingFrom = $data_drv[$i]['eBookingFrom'];
                                                        } else {
                                                            $eBookingFrom = 'User';
                                                        }
                                                        ?>
                                                        <tr class="gradeA">
                                                            <?php
                                                            if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                                                                if ($userType != 'hotel') {
                                                                    ?>
                                                                    <td align="left">
                                                                        <? echo $trip_type; ?>
                                                                    </td>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                            <?
                                                            if ($hotelPanel > 0 || $kioskPanel  > 0) {
                                                                if ($userType != 'hotel') {
                                                                    ?>
                                                                    <td class="align-center"><? echo $eBookingFrom; ?></td>
                                                                    <?
                                                                }
                                                            }
                                                            ?>
                                                            <td width="12%"><?= $generalobjAdmin->clearName($data_drv[$i]['vBookingNo']); ?></td>
                                                            <td width="10%"><?= $generalobjAdmin->clearName($data_drv[$i]['rider']); ?></td>
                                                            <td width="10%" data-order="<?= $data_drv[$i]['iCabBookingId']; ?>"><?php
                                                                if ($data_drv[$i]['dBooking_date'] != "" && $data_drv[$i]['vTimeZone'] != "") {
                                                                    $dBookingDate = converToTz($data_drv[$i]['dBooking_date'], $data_drv[$i]['vTimeZone'], $systemTimeZone);
                                                                } else {
                                                                    $dBookingDate = $data_drv[$i]['dBooking_date'];
                                                                }
                                                                echo $generalobjAdmin->DateTime($dBookingDate);
                                                                ?></td>
                                                            <td><?= $data_drv[$i]['vSourceAddresss']; ?></td>
                                                            <? if ($APP_TYPE != "UberX") { ?>
                                                                <td><?= $data_drv[$i]['tDestAddress']; ?></td>
                                                            <? } ?>
                                                            <?php if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['iRentalPackageId'] > 0) { 

                                                                    if (($data_drv[$i]['iFromStationId'] > 0) && ($data_drv[$i]['iToStationId'] > 0)) {
                                                                        $vehicleType  = "Fly Type";
                                                                    }
                                                                    else{
                                                                        $vehicleType  = "Car Type";
                                                                    }
                                                                
                                                                
                                                            ?>

                                                                <td width="10%"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> : Auto Assign </b><br />( <?php echo $vehicleType ?> : <?= $data_drv[$i]['vRentalVehicleTypeName']; ?>)<br/>
                                                                    <? if ($rental_data[0]['pkgName'] != '') { ?>
                                                                        (Rental Package : <?= $rental_data[0]['pkgName']; ?>)
                                                                    <? } ?>
                                                                </td>

                                                            <?php } else if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['eType'] == "Deliver" && $data_drv[$i]['iDriverId'] == 0 && $data_drv[$i]['eStatus'] != 'Cancel' && $APP_DELIVERY_MODE == "Multi") { ?>

                                                                <td width="10%"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> : Auto Assign </b><br />( Vehicle Type : <?= $data_drv[$i]['vVehicleType']; ?>)<br/><?php if (strtotime($data_drv[$i]['dBooking_date']) > strtotime(date('Y-m-d'))) { ?><a class="btn btn-info" href="javascript:void(0);" onclick="assignDriver('<?= $data_drv[$i]['iCabBookingId']; ?>');" data-tooltip="tooltip" title="<?= $langage_lbl_admin['LBL_ASSIGN_DRIVER_BUTTON']; ?>"><i class="icon-edit icon-flip-horizontal icon-white"></i> <?= $langage_lbl_admin['LBL_ASSIGN_DRIVER_BUTTON']; ?></a><?php } ?></td>

                                                            <?php } else if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['iDriverId'] == 0 && $data_drv[$i]['eStatus'] != 'Cancel') {

                                                                        if (($data_drv[$i]['iFromStationId'] > 0) && ($data_drv[$i]['iToStationId'] > 0)){
                                                                            $vehicleType  = "Fly Type";
                                                                        }
                                                                        else{
                                                                            $vehicleType  = "Car Type";
                                                                        }


                                                                ?>

                                                                <td width="10%"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> : Auto Assign </b><br />( <?= $vehicleType; ?> : <?= $data_drv[$i]['vVehicleType']; ?>)<br/><?php
                                                                    if (strtotime($data_drv[$i]['dBooking_date']) > strtotime(date('Y-m-d'))) {
                                                                        if ($userType != 'hotel') {
                                                                            ?>
                                                                            <a class="btn btn-info" href="add_booking.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>" data-tooltip="tooltip" title="Edit"><i class="icon-edit icon-flip-horizontal icon-white"></i></a>
                                                                        <?php } else { ?>
                                                                            <a class="btn btn-info" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>" data-tooltip="tooltip" title="Edit"><i class="icon-edit icon-flip-horizontal icon-white"></i></a>
                                                                            <?
                                                                        }
                                                                    }
                                                                    ?></td>

                                                            <?php } else if ($data_drv[$i]['eStatus'] == "Pending" && (strtotime($data_drv[$i]['dBooking_date']) > strtotime(date('Y-m-d'))) && $data_drv[$i]['iDriverId'] == 0) { ?>

                                                                <td width="10%">
                                                                    <? if ($userType != 'hotel') { ?>
                                                                        <a class="btn btn-info" href="add_booking.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></a>
                                                                    <? } else { ?>
                                                                        <a class="btn btn-info" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></a>
                                                                    <? } ?>
                                                                    <br>( <?= $service_type; ?> : <?= $data_drv[$i]['vVehicleType']; ?>)</td>

                                                            <?php } else if ($data_drv[$i]['eCancelBy'] == "Driver" && $data_drv[$i]['eStatus'] == "Cancel" && $data_drv[$i]['iDriverId'] == 0) { ?>

                                                                <td width="10%">
                                                                    <? if ($userType != 'hotel') { ?>
                                                                        <a class="btn btn-info" href="add_booking.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></a>
                                                                    <? } else { ?>
                                                                        <a class="btn btn-info" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></a>
                                                                    <? } ?>

                                                                    <br>( <?= $service_type; ?> : <?= $data_drv[$i]['vVehicleType']; ?>)</td>

                                                            <?php } else if ($data_drv[$i]['driver'] != "" && $data_drv[$i]['driver'] != "0") { ?>

                                                                <td width="10%"><b><?= $generalobjAdmin->clearName($data_drv[$i]['driver']); ?></b><br>( <?= $service_type; ?> : <?= $data_drv[$i]['vVehicleType']; ?>) </td>

                                                            <?php } else { ?>

                                                                <td width="10%">---<br>( <?= $service_type; ?> : <?= $data_drv[$i]['vVehicleType']; ?>)</td>

                                                            <?php } ?>

                                                            <td width="10%"><?php
                                                                $sql = "select iActive, eCancelledBy from trips where iTripId=" . $data_drv[$i]['iTripId'];
                                                                $data_stat_check = $obj->MySQLSelect($sql);
                                                                if (!empty($data_stat_check)) {
                                                                    for ($d = 0; $d < count($data_stat_check); $d++) {
                                                                        if ($data_stat_check[$d]['iActive'] == "Canceled") {
                                                                            echo "---";
                                                                        } else if ($data_stat_check[$d]['iActive'] == "Finished") {
                                                                            ?>
                                                                            <a target = "_blank" class="btn btn-primary" href="invoice.php?iTripId=<?= $data_drv[$i]['iTripId'] ?>" target="_blank">View</a>
                                                                        <?php } else if ($viewService == 1) {
                                                                            ?>
                                                                            <button class="btn btn-success" data-trip="<?= $data_drv[$i]['vBookingNo']; ?>" data-json='<?= $seriveJson; ?>' onclick="return showServiceModal(this);">
                                                                                <i class="fa fa-certificate icon-white"><b>View Service</b></i>
                                                                            </button>
                                                                            <?
                                                                        } else {
                                                                            echo "---";
                                                                        }
                                                                    }
                                                                } else {
                                                                    if ($data_drv[$i]['iTripId'] != "" && $data_drv[$i]['eStatus'] == "Completed") {
                                                                        ?>
                                                                        <a target = "_blank" class="btn btn-primary" href="invoice.php?iTripId=<?= $data_drv[$i]['iTripId'] ?>" target="_blank">View</a>
                                                                    <?php } else if ($viewService == 1) {
                                                                        ?>
                                                                        <button class="btn btn-success" data-trip="<?= $data_drv[$i]['vBookingNo']; ?>" data-json='<?= $seriveJson; ?>' onclick="return showServiceModal(this);">
                                                                            <i class="fa fa-certificate icon-white"><b>View Service</b></i>
                                                                        </button>
                                                                        <?
                                                                    } else {
                                                                        echo "---";
                                                                    }
                                                                }
                                                                ?>
                                                            </td>
                                                            <td width="15%">
                                                                <?php
                                                                /* 	
                                                                  Cancel
                                                                  Cancelled By User
                                                                  Cancelled By Provider
                                                                  Expired
                                                                  Declined
                                                                  Finished  Completed
                                                                  Cancelled
                                                                 */
//                                  changed by me
                                                                $status_array['bookid'] = $data_drv[$i]['iCabBookingId'];
                                                                $setcurrentTime = strtotime(date('Y-m-d H:i:s'));
                                                                $bookingdate = date("Y-m-d H:i", strtotime('+30 minutes', strtotime($data_drv[$i]['dBooking_date'])));
                                                                $bookingdatecmp = strtotime($bookingdate);
                                                                if ($data_drv[$i]['eStatus'] == "Assign" && $bookingdatecmp > $setcurrentTime) {
                                                                    echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Assigned";
                                                                } else if ($data_drv[$i]['eStatus'] == 'Accepted') {
                                                                    if ($bookingdatecmp > $setcurrentTime) {
                                                                        echo $data_drv[$i]['eStatus'];
                                                                    } else {
                                                                        echo 'Expired';
                                                                        $status_array['status'] = 'Expired';
                                                                    }
                                                                    // echo $data_drv[$i]['eStatus'];
                                                                } else if ($data_drv[$i]['eStatus'] == 'Declined') {
                                                                    echo $data_drv[$i]['eStatus'];
                                                                    $status_array['status'] = 'Declined';
                                                                    ?>
                                                                    <br /><a data-bookId="<?= $data_drv[$i]['iCabBookingId']; ?>" onclick="displayBookingReason(this);" data-cancelBy="<?= $eCancelBy; ?>" data-reason="<?= $cancelReasonTxt; ?>" class="btn btn-info" data-toggle="modal">Cancel Reason</a>
                                                                    <?php
                                                                } else {
                                                                    $sql = "select iActive, eCancelledBy from trips where iTripId=" . $data_drv[$i]['iTripId'];
                                                                    $data_stat = $obj->MySQLSelect($sql);

                                                                    if ($data_stat) {
                                                                        for ($d = 0; $d < count($data_stat); $d++) {
                                                                            if ($data_stat[$d]['iActive'] == "Canceled") {
                                                                                $eCancelledBy = ($data_stat[$d]['eCancelledBy'] == 'Passenger') ? $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] : $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
                                                                                echo "Cancelled By " . $eCancelledBy;
                                                                            } else if ($data_stat[$d]['iActive'] == "Finished" && $data_stat[$d]['eCancelledBy'] == "Driver") {
                                                                                echo "Cancelled By " . $eCancelledBy;
                                                                            } else {
                                                                                echo $data_stat[$d]['iActive'];
                                                                            }
                                                                        }
                                                                    } else {
                                                                        if ($data_drv[$i]['eStatus'] == "Cancel") {
                                                                            if ($data_drv[$i]['eCancelBy'] == "Driver") {
                                                                                echo "Cancelled By " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
                                                                            } else if ($data_drv[$i]['eCancelBy'] == "Rider") {
                                                                                echo "Cancelled By " . $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];
                                                                            } else {
                                                                                echo "Cancelled By Admin";
                                                                            }
                                                                        } else {

                                                                            if ($data_drv[$i]['eStatus'] == 'Pending' && $bookingdatecmp > $setcurrentTime) {
                                                                                echo $data_drv[$i]['eStatus'];
                                                                            } else {
                                                                                echo 'Expired';
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                if ($data_drv[$i]['eStatus'] == "Cancel") {
                                                                    ?>
                                                                    <br /><a data-bookId="<?= $data_drv[$i]['iCabBookingId']; ?>" onclick="displayBookingReason(this);" data-cancelBy="<?= $eCancelBy; ?>" data-reason="<?= $cancelReasonTxt; ?>" class="btn btn-info" data-toggle="modal">Cancel Reason</a>
                                                                    <?
                                                                }
                                                                if (($bookingdatecmp > time()) && ($data_drv[$i]['eStatus'] == 'Pending' || $data_drv[$i]['eStatus'] == "Assign" || $data_drv[$i]['eStatus'] == "Accepted")) {
                                                                    ?>
                                                                    <div>
                                                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#delete_form<?php echo $data_drv[$i]['iCabBookingId']; ?>">Cancel Booking</button>
                                                                        <!-- Modal -->
                                                                        <div id="delete_form<?php echo $data_drv[$i]['iCabBookingId']; ?>" class="modal fade delete_form" role="dialog">
                                                                            <div class="modal-dialog">

                                                                                <!-- Modal content-->
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <button type="button" class="close" data-dismiss="modal">x</button>
                                                                                        <h4 class="modal-title">Booking Cancel</h4>
                                                                                    </div>
                                                                                    <form  role="form" name="delete_form" id="delete_form1" method="post" action="" class="margin0">
                                                                                        <div class="modal-body">
                                                                                            <div class="form-group" style="display: inline-block;">
                                                                                                <label class="col-xs-4 control-label">Cancel Reason<span class="red">*</span></label>
                                                                                                <div class="col-xs-7">
                                                                                                    <textarea name="cancel_reason" id="cancel_reason" rows="4" cols="40" required="required" autofocus></textarea>
                                                                                                    <div class="cnl_error error red"></div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $data_drv[$i]['iCabBookingId']; ?>">
                                                                                            <input type="hidden" name="action" id="action" value="delete">
                                                                                            <input type="hidden" name="iDriverId" id="iDriverId" value="<?= $data_drv[$i]['iDriverId']; ?>">
                                                                                            <input type="hidden" name="iUserId" id="iUserId" value="<?= $data_drv[$i]['iUserId']; ?>">
                                                                                        </div>
                                                                                        <div class="modal-footer">
                                                                                            <button type="submit" class="btn btn-info" id="cnl_booking" title="Cancel Booking">Cancel Booking</button>
                                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                        <!-- Modal -->
                                                                    </div>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>

                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="9"> No Records Found.</td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </form>
                                    <?php include 'pagination_n.php'; ?>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>
                                Bookings module will list all Bookings on this page.
                            </li>
                            <!-- <li>
                                            Administrator can Activate / Deactivate / Delete any booking.
                            </li> -->
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <div  class="modal fade" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
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
        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Manual Taxi Dispatch</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <label><?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?> <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select name="frmDriver" id="frmDriver" onChange="shoeDriverDetail002(this.value);" class="form-control  filter-by-text">
                                    <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></option>
                                    <?php
                                    if (count($driverData) > 0) {
                                        for ($i = 0; $i < count($driverData); $i++) {
                                            ?>
                                            <option value="<?php echo $driverData[$i]['iDriverId']; ?>"><?php echo $driverData[$i]['vName'] . ' ' . $driverData[$i]['vLastName'] . " ( +" . $driverData[$i]['vCode'] . "&nbsp;" . $driverData[$i]['vPhone'] . " )"; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <br><span class="col-lg-6" id="showDriver003"></span>
                        <input type="hidden" name="iBookingId" id="iBookingId" value="" >
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="assignDriverForBooking();">Assign <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></button>
                        <button type="button" class="btn btn-default" onclick="closeModal();">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="uiModalCancelReason" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-content image-upload-1" style="width:400px;">
                <div class="upload-content" style="width:350px; padding:0px;">
                    <h3>Booking Cancel Reason</h3>
                    <h4 id="cancelbytxt"></h4>
                    <h4 id="cancelreason">Cancel Reason: <?= $data_drv[$i]['vCancelReason']; ?></h4>
                    <input style="margin:10px 0 20px;" type="button" class="save" data-dismiss="modal" name="cancel" value="Close">
                </div>
            </div>
        </div>
        <form name="pageForm" id="pageForm" action="action/admin.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iAdminId" id="iMainId01" value="" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="eType" id="eType" value="<?php echo $eType; ?>" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>
        <link rel="stylesheet" href="css/select2/select2.min.css" />
        <script src="js/plugins/select2.min.js"></script>

        <?php include_once 'footer.php'; ?>


        <script>

                            /*		Cancel
                             Cancelled By User 
                             Cancelled By Provider 
                             Expired	
                             Declined	
                             Finished  Completed 
                             Cancelled 
                             */
                            //changed by me start
                            $(document).ready(function () {
                                $('#usertype_options').hide();
                                $('#option').each(function () {
                                    if (this.value == 'cb.eStatus') {
                                        $('#usertype_options').show();
                                        $('.searchform').hide();
                                    }
                                });
                            });

                            $(function () {
                                $('#option').change(function () {
                                    if ($('#option').val() == 'cb.eStatus') {
                                        $('#usertype_options').show();
                                        $("input[name=keyword]").val("");
                                        $('.searchform').hide();
                                    } else {
                                        $('#usertype_options').hide();
                                        $("#estatus_value").val("");
                                        $('.searchform').show();
                                    }
                                });
                            });
                            //changed by me end
        </script>

        <script>
            var typeArr = '<?= $generalobj->getJsonFromAnArr($vehilceTypeArr); ?>';
            $("#setAllCheck").on('click', function () {
                if ($(this).prop("checked")) {
                    jQuery("#_list_form input[type=checkbox]").each(function () {
                        if ($(this).attr('disabled') != 'disabled') {
                            this.checked = 'true';
                        }
                    });
                } else {
                    jQuery("#_list_form input[type=checkbox]").each(function () {
                        this.checked = '';
                    });
                }
            });
            //Added By HJ On 04-06-2019 FOr Get Cancel Reason Data Start
            function displayBookingReason(elem) {
                var bookId = $(elem).attr("data-bookId");
                var cancelBy = $(elem).attr("data-cancelBy");
                var reason = $(elem).attr("data-reason");
                //alert(reason);
                $("#cancelbytxt").text("Cancel By: " + cancelBy);
                $("#cancelreason").text("Cancel Reason: " + reason);
                $('#uiModalCancelReason').modal('show');
            }
            //Added By HJ On 04-06-2019 FOr Get Cancel Reason Data End
            $("#Search").on('click', function () {
                var action = $("#_list_form").attr('action');
                var formValus = $("#frmsearch").serialize();
                window.location.href = action + "?" + formValus;
            });

            $('.entypo-export').click(function (e) {
                e.stopPropagation();
                var $this = $(this).parent().find('div');
                $(".openHoverAction-class div").not($this).removeClass('active');
                $this.toggleClass('active');
            });

            $(document).on("click", function (e) {
                if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
                    $(".show-moreOptions").removeClass("active");
                }
            });

            /*            function confirm_delete()
             {
             var confirm_ans = confirm("Are You sure You want to Cancel this Booking?");
             
             if (confirm_ans == true) {
             
             document.getElementById('delete_form1').submit();
             
             }else{
             
             return false;
             }
             
             }*/

            $(function () {
                $("#cnl_booking").on('click', function (e) {
                    var cancel_reason = $('#cancel_reason');
                    if (!cancel_reason.val()) {
                        $(".cnl_error").html("This Field is required.");
                        return false;
                    } else {
                        $("#delete_form1")[0].submit();
                    }

                });
            });

            function assignDriver(bookingId) {
                $('#iBookingId').val(bookingId);
                $('#txtDriverEmail').val('');
                $('#txtDriverCompanyName').val('');
                $('#txtDriverMobileNumber').val('');
                $('#driverdetail').css('display', 'none');
                $('#myModal').modal('show');
            }
            function showServiceModal(elem) {
                var tripJson = JSON.parse($(elem).attr("data-json"));
                var rideNo = $(elem).attr("data-trip");
                var typeNameArr = JSON.parse(typeArr)
                var serviceHtml = "";
                var srno = 1;
                for (var g = 0; g < tripJson.length; g++) {
                    serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?=$langage_lbl_admin['LBL_QTY_TXT']?>: <b>"+ [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
                    srno++;
                }
                $("#service_detail").html(serviceHtml);
                $("#servicetitle").text("Service Details : " + rideNo);
                $("#service_modal").modal('show');
                return false;
            }
            function assignDriverForBooking() {
                driverId = $('#frmDriver').val();
                $(".loader-default").fadeIn("slow");
                if (driverId != "") {
                    bookingId = $('#iBookingId').val();
                    var request = $.ajax({
                        type: "POST",
                        url: 'ajax_assign_driver_cabbooking.php',
                        data: {'driverId': driverId, 'bookingId': bookingId},
                        success: function (data)
                        {
                            if (data.trim() == 1) {
                                window.location = 'cab_booking.php';
                            } else {
                                alert('Email sending failed.');
                                window.location = 'cab_booking.php';
                            }
                        }
                    });
                } else {
                    alert('Please assign a Driver.');
                }
                $(".loader-default").fadeOut("slow");
            }

            function closeModal() {
                $('#myModal').modal('hide');
                $('#driverdetail').css('display', 'none');
                $('#frmDriver').val('');
                $('#txtDriverEmail').val('');
                $('#txtDriverCompanyName').val('');
                $('#txtDriverMobileNumber').val('');
            }
            $('select.filter-by-text').select2();
            function shoeDriverDetail002(id) {
                if (id != "") {
                    var request2 = $.ajax({
                        type: "POST",
                        url: 'show_driver.php',
                        dataType: 'html',
                        data: 'id=' + id,
                        success: function (data)
                        {
                            $("#showDriver003").html(data);
                        }, error: function (data) {
                        }
                    });
                } else {
                    $("#showDriver003").html('');
                }
            }
        </script>

    </body>
    <!-- END BODY-->
</html>
