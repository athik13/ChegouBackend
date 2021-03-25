<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
include_once ('../app_common_functions.php');
if (!$userObj->hasPermission('manage-organization-payment-report')) {
    $userObj->redirect();
}
$script = 'OrganizationPaymentReport';

function cleanNumber($num) {
    return str_replace(',', '', $num);
}

function setTwoDecimalValue($amount) {
    $amount = number_format($amount, 2);
    return $amount;
}

$org_sql = "SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization order by vCompany";
$db_organization = $obj->MySQLSelect($org_sql);
$orgNameArr = array();
for ($g = 0; $g < count($db_organization); $g++) {
    $orgNameArr[$db_organization[$g]['iOrganizationId']] = $db_organization[$g]['driverName'];
}
//echo "<pre>";
//print_r($orgNameArr);die;
$rider_sql = "SELECT RU.iUserId,CONCAT(RU.vName,' ',RU.vLastName) AS riderName,RU.vEmail FROM register_user AS RU INNER JOIN user_profile UP ON RU.iUserId=UP.iUserId WHERE RU.eStatus != 'Deleted' AND UP.eStatus != 'Deleted' GROUP BY UP.iUserId order by vName";
$db_rider = $obj->MySQLSelect($rider_sql);
//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY tr.iTripId DESC';
//print_r($_REQUEST);die;
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY ru.vName ASC";
    else
        $ord = " ORDER BY ru.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY tr.tTripRequestDate ASC";
    else
        $ord = " ORDER BY tr.tTripRequestDate DESC";
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
if ($sortby == 6) {
    if ($order == 0)
        $ord = " ORDER BY tr.eType ASC";
    else
        $ord = " ORDER BY tr.eType DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchOrganization = isset($_REQUEST['searchOrganization']) ? $_REQUEST['searchOrganization'] : '';
$searchUser = isset($_REQUEST['searchUser']) ? $_REQUEST['searchUser'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(tr.tTripRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tr.tTripRequestDate) <='" . $endDate . "'";
    }
    if ($serachTripNo != '') {
        $ssql .= " AND tr.vRideNo ='" . $serachTripNo . "'";
    }
    if ($searchOrganization != '') {
        $ssql .= " AND tr.iOrganizationId ='" . $searchOrganization . "'";
    }
    if ($searchUser != '') {
        $ssql .= " AND tr.iUserId ='" . $searchUser . "'";
    }
    if ($searchDriverPayment != '') {
        $ssql .= " AND tr.eOrganizationPaymentStatus ='" . $searchDriverPayment . "'";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND tr.vTripPaymentMode ='" . $searchPaymentType . "'";
    }
    if ($eType != '') {
        if ($eType == 'Fly') {
            $ssql .= " AND tr.iFromStationId > 0 AND tr.iToStationId > 0";
        } else if ($eType == 'Ride') {
            $ssql .= " AND tr.eType ='" . $eType . "' AND tr.iRentalPackageId = 0 AND tr.eHailTrip = 'No' AND  tr.iFromStationId = 0 AND tr.iToStationId = 0 ";
        } elseif ($eType == 'RentalRide') {
            $ssql .= " AND tr.eType ='Ride' AND tr.iRentalPackageId > 0";
        } elseif ($eType == 'HailRide') {
            $ssql .= " AND tr.eType ='Ride' AND tr.eHailTrip = 'Yes'";
        } else {
            $ssql .= " AND tr.eType ='" . $eType . "' ";
        }
    }
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And tr.tTripRequestDate > '" . WEEK_DATE . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$commonWhereCon = "if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iOrganizationId >0 AND tr.iActive ='Finished' AND tr.eSystem='General'";
$sql = "SELECT tr.iFromStationId,tr.iToStationId,tr.ePoolRide,tr.iOrganizationId,tr.iFare,tr.fTripGenerateFare,tr.fHotelCommision,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.fOutStandingAmount,tr.vTripPaymentMode,( SELECT COUNT(tr.iTripId) FROM trips AS tr WHERE $commonWhereCon $ssql $trp_ssql) AS Total FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE $commonWhereCon $ssql $trp_ssql";
//echo $sql;die;
//echo "<pre>";
$totalData = $obj->MySQLSelect($sql);
//print_r($totalData);die;
$total_tip = $tot_fare = $tot_site_commission = $tot_hotel_commision = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $total_results = $start = 0;
if (isset($totalData[0]['Total'])) {
    $total_results = $totalData[0]['Total'];
}
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$end = $per_page;
//-------------if page is setcheck------------------//
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
$sql = "SELECT tr.iFromStationId,tr.iToStationId,tr.ePoolRide,tr.iOrganizationId,tr.iTripId,tr.fHotelCommision,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.tTripRequestDate, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eOrganizationPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE $commonWhereCon $ssql $trp_ssql $ord LIMIT $start, $per_page";
$db_trip = $obj->MySQLSelect($sql);
$endRecord = count($db_trip);

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
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Organization Payment Report</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
        <style>
            .setteled-class{
                background-color:#bddac5
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
                                <h2>Organization Payment Report</h2>
                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page payment-report">
                            <input type="hidden" name="action" value="search" />
                            <div>
                                <div style="float: left;"><h3>Search by Date...</h3></div>
                                <?php if ($userObj->hasPermission('manage-org-cancellation-payment-report')) { ?>
                                    <div style="text-align: right;font-size:15px;"><a href="org_cancellation_payment_report.php" target="_blank" class="btn btn-primary">View Cancelled <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?> Payment Report</a></div>
                                <?php } ?>
                            </div>
                            <h3>Search <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>...</h3>
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
                            <span>
                                <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff" />
                                <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>

                                <div class="col-lg-3 select001">
                                    <select class="form-control filter-by-text driver_container" name = 'searchOrganization' data-text="Select Organize">
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_ORGANIZATION']; ?></option>
                                        <?php foreach ($db_organization as $dbd) { ?>
                                            <option value="<?php echo $dbd['iOrganizationId']; ?>" <?php
                                            if ($searchOrganization == $dbd['iOrganizationId']) {
                                                echo "selected";
                                            }
                                            ?>><?php echo $generalobjAdmin->clearName($dbd['driverName']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbd['vEmail']); ?> )</option>
                                                <?php } ?>
                                    </select>
                                </div>
                                <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') { ?>
                                    <div class="col-lg-3 select001">
                                        <select class="form-control" name = 'eType' >
                                            <option value="">Service Type</option>
                                            <?php if ($APP_TYPE == "Ride" || $APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Delivery-UberX") { ?>
                                                <option value="Ride" <?php
                                                if ($eType == "Ride") {
                                                    echo "selected";
                                                }
                                                ?>><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                                    <?php } ?>
                                                    <?php if (ENABLE_RENTAL_OPTION == 'Yes' && $APP_TYPE != 'Delivery') { ?>
                                                <option value="RentalRide" <?php
                                                if ($eType == "RentalRide") {
                                                    echo "selected";
                                                }
                                                ?>>Rental <?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_SEARCH']; ?> </option>
                                                    <?php } ?>
                                                    <?php if (ENABLE_MULTI_DELIVERY == "Yes") { ?>
                                                <option value="Multi-Delivery" <?php
                                                if ($eType == "Multi-Delivery") {
                                                    echo "selected";
                                                }
                                                ?>>Multi-Delivery</option>
                                                    <?php } ?>

                                            <?php if (($APP_TYPE == "Ride" || $APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Delivery-UberX") && $PACKAGE_TYPE == "SHARK") { ?>
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
                                <?php } ?>
                            </span>
                        </div>

                        <div class="row payment-report payment-report1 payment-report2">
                            <div class="col-lg-3">
                                <select class="form-control filter-by-text" name = 'searchUser' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                                    <?php foreach ($db_rider as $dbr) { ?>
                                        <option value="<?php echo $dbr['iUserId']; ?>" <?php
                                        if ($searchUser == $dbr['iUserId']) {
                                            echo "selected";
                                        }
                                        ?>><?php echo $generalobjAdmin->clearName($dbr['riderName']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbr['vEmail']); ?> )</option>
                                            <?php } ?>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control" name='searchPaymentType' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select Payment Type</option>
                                    <option value="Cash" <? if ($searchPaymentType == "Cash") { ?>selected <? } ?>>Cash</option>
                                    <option value="Card" <? if ($searchPaymentType == "Card") { ?>selected <? } ?>>Card</option>
                                    <option value="Organization" <? if ($searchPaymentType == "Organization") { ?>selected <? } ?>>Organization</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control" name='searchDriverPayment' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                    <option value="">Select Payment Status</option>
                                    <option value="Settelled" <?php if ($searchDriverPayment == "Settelled") { ?>selected <?php } ?>>Settled</option>
                                    <option value="Unsettelled" <?php if ($searchDriverPayment == "Unsettelled") { ?>selected <?php } ?>>Unsettled</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number" class="form-control search-trip001" value="<?php echo $serachTripNo; ?>"/>
                            </div>
                        </div>
                        <div class="tripBtns001"><b>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'org_payment_report.php'"/>
                                <?php if (count($db_trip) > 0) { ?>
                                    <button type="button" onClick="reportExportTypes('organization_payment')" class="export-btn001" >Export</button></b>
                            <?php } ?>
                        </div>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" id="actionpayment" name="actionpayment" value="pay_driver">
                                        <input type="hidden"  name="iTripId" id="iTripId" value="">
                                        <input type="hidden"  name="ePayDriver" id="ePayDriver" value="">
                                        <table class="table table-bordered" id="dataTables-example123" >
                                            <thead>
                                                <tr>
                                                    <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?>
                                                        <th width="10%"><a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                            if ($sortby == '6') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']; ?> <?php
                                                                               if ($sortby == 6) {
                                                                                   if ($order == 0) {
                                                                                       ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                            <?php } ?>
                                                    <th><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']; ?> </th>
                                                    <!--<th width="10%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                    if ($sortby == '1') {
                                                        echo $order;
                                                    } else {
                                                        ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?php
                                                    if ($sortby == 1) {
                                                        if ($order == 0) {
                                                            ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                        }
                                                    } else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>-->
                                                    <th width="10%"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == '2') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?php
                                                                           if ($sortby == 2) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="10%"><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                        if ($sortby == '3') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']; ?> <?php
                                                                           if ($sortby == 3) {
                                                                               if ($order == 0) {
                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <!--<th>Address</th>-->
                                                    <!--<th style="text-align:right;">A=Total Fare</th>-->
                                                    <!--<th style="text-align:right;">A=(Base Fare+ Distance Fare+ Time Fare)</th>-->
                                                    <th style="text-align:right;">A=Total Fare</th>
                                                    <th style="text-align:right;">B=Platform Fees</th>
                                                    <th style="text-align:right;">C = Wallet Debit</th>
                                                    <?php if ($ENABLE_TIP_MODULE == "Yes") { ?>
                                                        <th>D=Tip</th>
                                                        <th>E=<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Outstanding Amount</th>

                                                    <?php } else { ?>
                                                        <th>F=<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Outstanding Amount</th>
                                                    <? } ?>
                                                    <th style="text-align:right;"><?= $langage_lbl_admin['LBL_ORGANIZATION']; ?> pay Amount</th>
                                                    <th><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Status</th>
                                                    <th>Payment method</th>
                                                    <th><?= $langage_lbl_admin['LBL_ORGANIZATION']; ?> Payment Status</th> 
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?
                                                $set_unsetarray = array();
                                                if (count($db_trip) > 0) {
                                                    for ($i = 0; $i < count($db_trip); $i++) {
                                                        $orfName = "";
                                                        if (isset($orgNameArr[$db_trip[$i]['iOrganizationId']]) && $orgNameArr[$db_trip[$i]['iOrganizationId']] != "") {
                                                            $orfName = "(" . $orgNameArr[$db_trip[$i]['iOrganizationId']] . ")";
                                                        }
                                                        $totalfare = $generalobj->trip_currency_payment($db_trip[$i]['fTripGenerateFare']);
                                                        $site_commission = $generalobj->trip_currency_payment($db_trip[$i]['fCommision']);
                                                        $hotel_commision = $generalobj->trip_currency_payment($db_trip[$i]['fHotelCommision']);
                                                        $promocodediscount = $generalobj->trip_currency_payment($db_trip[$i]['fDiscount']);
                                                        $wallentPayment = $generalobj->trip_currency_payment($db_trip[$i]['fWalletDebit']);
                                                        $fTipPrice = $generalobj->trip_currency_payment($db_trip[$i]['fTipPrice']);
                                                        $fOutStandingAmount = $generalobj->trip_currency_payment($db_trip[$i]['fOutStandingAmount']);
                                                        if ($db_trip[$i]['vTripPaymentMode'] == "Cash") {
                                                            //$driver_payment = ($promocodediscount + $wallentPayment) - ($site_commission + $fOutStandingAmount + $hotel_commision);
                                                        } else {
                                                            //$driver_payment = ($fTipPrice + $totalfare) - ($site_commission + $fOutStandingAmount + $hotel_commision);
                                                        }
                                                        $driver_payment = ($fTipPrice + $totalfare) - ($site_commission + $fOutStandingAmount + $hotel_commision);
                                                        $orgPayAmount = $totalfare - $promocodediscount;
                                                        $tot_fare += $orgPayAmount;
                                                        $tot_site_commission += $site_commission;
                                                        $tot_hotel_commision += $hotel_commision;
                                                        $tot_promo_discount += $promocodediscount;
                                                        $tot_wallentPayment += $wallentPayment;
                                                        $total_tip += $fTipPrice;
                                                        $tot_driver_refund += $driver_payment;
                                                        $cashPayment = $site_commission;
                                                        $cardPayment = $orgPayAmount - $site_commission;
                                                        $tot_outstandingAmount += $fOutStandingAmount;
                                                        $class_setteled = "";
                                                        if ($db_trip[$i]['eOrganizationPaymentStatus'] == 'Settelled') {
                                                            $class_setteled = "setteled-class";
                                                        }
                                                        $poolTxt = "";
                                                        if ($db_trip[$i]['ePoolRide'] == "Yes") {
                                                            $poolTxt = " (Pool)";
                                                        }
                                                        $tipPayment = 0;
                                                        if ($ENABLE_TIP_MODULE == "Yes") {
                                                            $tipPayment = $fTipPrice;
                                                        }
                                                        $set_unsetarray[] = $db_trip[$i]['eOrganizationPaymentStatus'];
                                                        $eTypenew = $db_trip[$i]['eType'];
                                                        if ($eTypenew == 'Ride') {
                                                            $trip_type = 'Ride';
                                                        } else if ($eTypenew == 'UberX') {
                                                            $trip_type = 'Other Services';
                                                        } else {
                                                            $trip_type = 'Delivery';
                                                        }
                                                        $trip_type .= $poolTxt;
                                                        ?>
                                                        <tr class="gradeA <?= $class_setteled ?>">
                                                            <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?> 
                                                                <td align="left">
                                                                    <?
                                                                    if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {
                                                                        echo "Rental " . $trip_type . "<br/> ( Hail )";
                                                                    } else if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                                        echo "Rental " . $trip_type;
                                                                    } else if ($db_trip[$i]['eHailTrip'] == "Yes") {
                                                                        echo "Hail " . $trip_type;
                                                                    } else {
                                                                        echo $trip_type;
                                                                    }
                                                                    ?>
                                                                </td>
                                                            <?php } ?>
                                                            <td><?= $db_trip[$i]['vRideNo']; ?></td>
                                                            <!--<td><?= $generalobjAdmin->clearName($db_trip[$i]['drivername']); ?></td>-->
                                                            <td><?= $generalobjAdmin->clearName($db_trip[$i]['riderName']); ?></td>
                                                            <td><?= $generalobjAdmin->DateTime($db_trip[$i]['tTripRequestDate']); ?></td>

                                                            <td align="right">
                                                                <?php
                                                                if ($totalfare != "" && $totalfare != 0) {
                                                                    echo $totalfare;
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>



                                                            <td align="right"><?php
                                                                if ($promocodediscount != "" && $promocodediscount != 0) {
                                                                    echo $promocodediscount;
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>

                                                            <td align="right"><?php
                                                                if ($wallentPayment != "" && $wallentPayment != 0) {
                                                                    echo $wallentPayment;
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?></td>
                                                            <?php if ($ENABLE_TIP_MODULE == "Yes") { ?>
                                                                <td align="right">
                                                                    <?php
                                                                    if ($fTipPrice != "0") {
                                                                        echo $fTipPrice;
                                                                    } else {
                                                                        echo "-";
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td align="right">
                                                                    <?php
                                                                    if ($fOutStandingAmount != "" && $fOutStandingAmount != 0) {
                                                                        echo $fOutStandingAmount;
                                                                    } else {
                                                                        echo "-";
                                                                    }
                                                                    ?>
                                                                </td>
                                                            <?php } else { ?>
                                                                <td align="right">
                                                                    <?php
                                                                    if ($fOutStandingAmount != "" && $fOutStandingAmount != 0) {
                                                                        echo $fOutStandingAmount;
                                                                    } else {
                                                                        echo "-";
                                                                    }
                                                                    ?>
                                                                </td>
                                                            <?php } ?>
                                                            <td align="right">
                                                                <?php
                                                                if ($orgPayAmount != "" && $orgPayAmount != 0) {
                                                                    echo $orgPayAmount;
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= $db_trip[$i]['iActive']; ?></td>
                                                            <td><?= $db_trip[$i]['vTripPaymentMode'] . "<br>" . $orfName; ?></td>
                                                            <td><?
                                                                if ($db_trip[$i]['eOrganizationPaymentStatus'] == "Settelled") {
                                                                    echo "Settled";
                                                                } else if ($db_trip[$i]['eOrganizationPaymentStatus'] == "Unsettelled") {
                                                                    echo "Unsettled";
                                                                } else {
                                                                    echo $db_trip[$i]['eOrganizationPaymentStatus'];
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?
                                                                if ($db_trip[$i]['eOrganizationPaymentStatus'] == 'Unsettelled') {
                                                                    ?>
                                                                    <input class="validate[required]" type="checkbox" value="<?= $db_trip[$i]['iTripId'] ?>" id="iTripId_<?= $db_trip[$i]['iTripId'] ?>" name="iTripId[]">
                                                                    <?
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <? } ?>
                                                    <tr class="gradeA">
                                                        <td colspan="13" align="right">Total Fare</td>
                                                        <td align="right" colspan="2"><?= setTwoDecimalValue($tot_fare); ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="13" align="right">Total Platform Fees</td>
                                                        <td  align="right" colspan="2"><?= setTwoDecimalValue($tot_site_commission) ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="13" align="right">Total Promo Discount</td>
                                                        <td  align="right" colspan="2"><?= setTwoDecimalValue($tot_promo_discount); ?></td>
                                                    </tr>
                                                    <tr class="gradeA">
                                                        <td colspan="13" align="right">Total Wallet Debit</td>
                                                        <td  align="right" colspan="2"><?= setTwoDecimalValue($tot_wallentPayment); ?></td>
                                                    </tr>
                                                    <?php if ($ENABLE_TIP_MODULE == "Yes") { ?>
                                                        <tr class="gradeA">
                                                            <td colspan="13" align="right">Total Tip Amount</td>
                                                            <td  align="right" colspan="2"><?= setTwoDecimalValue($total_tip); ?></td>
                                                        </tr>
                                                        <tr class="gradeA">
                                                            <td colspan="13" align="right">Total Trip Outstanding Amount</td>
                                                            <td  align="right" colspan="2"><?= setTwoDecimalValue($tot_outstandingAmount); ?></td>
                                                        </tr>
                                                        <tr class="gradeA">
                                                            <td colspan="13" align="right">Total Booking Fees</td>
                                                            <td  align="right" colspan="2"><?= setTwoDecimalValue($tot_hotel_commision); ?></td>
                                                        </tr>
                                                        <tr class="gradeA">
                                                            <td colspan="13" align="right">Total Payment Amount</td>
                                                            <td  align="right" colspan="2"><?= setTwoDecimalValue($tot_driver_refund); ?></td>
                                                        </tr>
                                                    <? } else { ?>
                                                        <tr class="gradeA">
                                                            <td colspan="13" align="right">Total Trip Outstanding Amount</td>
                                                            <td  align="right" colspan="2"><?= setTwoDecimalValue($tot_outstandingAmount); ?></td>
                                                        </tr>
                                                        <tr class="gradeA">
                                                            <td colspan="13" align="right">Total Payment Amount</td>
                                                            <td  align="right" colspan="2"><?= setTwoDecimalValue($tot_driver_refund); ?></td>
                                                        </tr>
                                                    <? } ?>

                                                    <?php if (in_array("Unsettelled", $set_unsetarray)) { ?>
                                                        <tr class="gradeA">
                                                            <td colspan="14" align="right"><div class="row payment-report-button">
                                                                    <span style="margin-right: 15px;">
                                                                        <a onClick="PaytoorganizationforCancel()" href="javascript:void(0);"><button class="btn btn-primary" type="button">Mark As Settled</button></a>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="17" style="text-align:center;">No Payment Details Found.</td>
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

        <form name="pageForm" id="pageForm" action="action/payment_report.php" method="post" >
            <input type="hidden" name="orgpay" id="orgpay" value="1">
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="action" value="<?php echo $action; ?>" >
            <input type="hidden" name="searchDriver" value="<?php echo $searchOrganization; ?>" >
            <input type="hidden" name="searchRider" value="<?php echo $searchUser; ?>" >
            <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>" >
            <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>" >
            <input type="hidden" name="searchDriverPayment" value="<?php echo $searchDriverPayment; ?>" >
            <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
            <input type="hidden" name="eType" value="<?php echo $eType; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>

        <?php include_once('footer.php'); ?>
        <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css" />
        <script src="js/plugins/select2.min.js"></script>
        <script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
        <script>
                                                                            $('#dp4').datepicker()
                                                                                    .on('changeDate', function (ev) {
                                                                                        var endDate = $('#dp5').val();
                                                                                        if (ev.date.valueOf() < endDate.valueOf()) {
                                                                                            $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                                                                                        } else {
                                                                                            $('#alert').hide();
                                                                                            var startDate = new Date(ev.date);
                                                                                            $('#startDate').text($('#dp4').data('date'));
                                                                                        }
                                                                                        $('#dp4').datepicker('hide');
                                                                                    });
                                                                            $('#dp5').datepicker()
                                                                                    .on('changeDate', function (ev) {
                                                                                        var startDate = $('#dp4').val();
                                                                                        if (ev.date.valueOf() < startDate.valueOf()) {
                                                                                            $('#alert').show().find('strong').text('The end date can not be less then the start date');
                                                                                        } else {
                                                                                            $('#alert').hide();
                                                                                            var endDate = new Date(ev.date);
                                                                                            $('#endDate').text($('#dp5').data('date'));
                                                                                        }
                                                                                        $('#dp5').datepicker('hide');
                                                                                    });

                                                                            $(document).ready(function () {
                                                                                $("#dp5").click(function () {
                                                                                    $('#dp5').datepicker('show');
                                                                                    $('#dp4').datepicker('hide');
                                                                                });

                                                                                $("#dp4").click(function () {
                                                                                    $('#dp4').datepicker('show');
                                                                                    $('#dp5').datepicker('hide');
                                                                                });

                                                                                if ('<?= $startDate ?>' != '') {
                                                                                    $("#dp4").val('<?= $startDate ?>');
                                                                                    $("#dp4").datepicker('update', '<?= $startDate ?>');
                                                                                }
                                                                                if ('<?= $endDate ?>' != '') {
                                                                                    $("#dp5").datepicker('update', '<?= $endDate; ?>');
                                                                                    $("#dp5").val('<?= $endDate; ?>');
                                                                                }

                                                                            });
                                                                            function setRideStatus(actionStatus) {
                                                                                window.location.href = "trip.php?type=" + actionStatus;
                                                                            }
                                                                            function todayDate() {
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
        </script>
    </body>
    <!-- END BODY-->
</html>