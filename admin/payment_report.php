<?php

include_once('../common.php');

if (!isset($generalobjAdmin)) {

    require_once(TPATH_CLASS . "class.general_admin.php");

    $generalobjAdmin = new General_admin();

}

include_once ('../app_common_functions.php');

////$generalobjAdmin->check_member_login();

if (!$userObj->hasPermission('manage-payment-report')) {

    $userObj->redirect();

}

$script = 'Payment_Report';



function cleanNumber($num) {

    return str_replace(',', '', $num);

}



// ini_set('display_errors', 1);

// error_reporting(E_ALL);

//data for select fields

$sql = "SELECT iCompanyId,vCompany,vEmail FROM company WHERE eStatus != 'Deleted' AND eSystem = 'General' order by vCompany";

$db_company = $obj->MySQLSelect($sql);



$sql = "SELECT iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail FROM register_driver WHERE eStatus != 'Deleted' order by vName";

$db_drivers = $obj->MySQLSelect($sql);



$sql = "SELECT iUserId,CONCAT(vName,' ',vLastName) AS riderName,vEmail FROM register_user WHERE eStatus != 'Deleted' order by vName";

$db_rider = $obj->MySQLSelect($sql);

$db_curr_mst = $obj->MySQLSelect("select vSymbol from currency where eDefault='Yes'");

$vSymbol = "$";

if (count($db_curr_mst) > 0) {

    $vSymbol = $db_curr_mst[0]['vSymbol'];

}

//data for select fields

//Start Sorting

$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;

$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$ord = ' ORDER BY tr.iTripId DESC';

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

$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';

$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';

$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';

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

    if ($searchCompany != '') {

        $ssql .= " AND rd.iCompanyId ='" . $searchCompany . "'";

    }

    if ($searchDriver != '') {

        $ssql .= " AND tr.iDriverId ='" . $searchDriver . "'";

    }

    if ($searchRider != '') {

        $ssql .= " AND tr.iUserId ='" . $searchRider . "'";

    }

    if ($searchDriverPayment != '') {

        $ssql .= " AND tr.eDriverPaymentStatus ='" . $searchDriverPayment . "'";

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

        } else if ($eType == "Pool") {

            $ssql .= " AND tr.eType ='Ride' AND tr.ePoolRide = 'Yes'";

        } else {

            $ssql .= " AND tr.eType ='" . $eType . "' ";

        }

    }

}

$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";
$deliverallEnable = isDeliverAllModuleAvailable() ? "Yes" : "No";

if($ufxEnable != "Yes") {
	$ssql .= " AND tr.eType != 'UberX'";
}
if(!checkFlyStationsModule()) {
    $ssql.= " AND tr.iFromStationId = '0' AND tr.iToStationId = '0'";
}
if($rideEnable != "Yes") {
    $ssql .= " AND tr.eType != 'Ride'";
}
if($deliveryEnable != "Yes") {
    $ssql .= " AND tr.eType != 'Deliver' AND tr.eType != 'Multi-Delivery'";
}

$locations_where = "";

if (count($userObj->locations) > 0) {

    $locations = implode(', ', $userObj->locations);

    $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE tr.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";

}

$trp_ssql = "";

if (SITE_TYPE == 'Demo') {

    $trp_ssql = " And tr.tTripRequestDate > '" . WEEK_DATE . "'";

}

$org_sql = "SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization order by vCompany";

$db_organization = $obj->MySQLSelect($org_sql);

$orgNameArr = array();

for ($g = 0; $g < count($db_organization); $g++) {

    $orgNameArr[$db_organization[$g]['iOrganizationId']] = $db_organization[$g]['driverName'];

}

//echo "<pre>";print_r($orgNameArr);die;

//Pagination Start

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$sql = "SELECT tr.iFromStationId,tr.iToStationId,tr.ePayWallet,tr.fTax1,tr.fTax2,tr.iOrganizationId,tr.ePoolRide,tr.iFare,tr.fTripGenerateFare,tr.fHotelCommision,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.fOutStandingAmount,tr.vTripPaymentMode,( SELECT COUNT(tr.iTripId) FROM trips AS tr WHERE if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND tr.eSystem='General' $ssql $trp_ssql) AS Total FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId	LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE  if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND tr.eSystem='General' $ssql $trp_ssql";

$totalData = $obj->MySQLSelect($sql);



$driver_payment = $total_tip = $tot_fare = $tot_site_commission = $tot_hotel_commision = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $tot_ifare = $tot_tax = 0.00;

//Added By HJ On 08-08-2019 For Get Driver Wallet Debit Amount Start As Per Discuss With KS Sir

$tripWalletArr = array();

$getWalletData = $obj->MySQLSelect("SELECT iBalance,iTripId FROM user_wallet WHERE eType ='Debit' AND iTripId > 0");

for ($d = 0; $d < count($getWalletData); $d++) {

    $tripWalletArr[$getWalletData[$d]['iTripId']] = $getWalletData[$d]['iBalance'];

}

//Added By HJ On 08-08-2019 For Get Driver Wallet Debit Amount End As Per Discuss With KS Sir

//echo "<pre>";print_r($tripWalletArr);die;

$enableCashReceivedCol = $enableTipCol = array();

foreach ($totalData as $dtps) {

    $fTipPrice = $dtps['fTipPrice'];

    //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start

    if ($dtps['vTripPaymentMode'] == "Cash") {

        $enableCashReceivedCol[] = 1;

    }

    //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start

    if ($fTipPrice > 0) {

        $enableTipCol[] = 1;

    }

}



$total_results = $totalData[0]['Total'];

$total_pages = ceil($total_results / $per_page); //total pages we going to have

$show_page = 1;

$start = 0;

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

$sql = "SELECT tr.iFromStationId,tr.iToStationId,tr.ePayWallet,tr.iFare, tr.fTax1,tr.fTax2,tr.iOrganizationId,tr.ePoolRide,tr.iTripId,tr.fHotelCommision,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.tTripRequestDate, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eDriverPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE  if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND tr.eSystem='General' $ssql $trp_ssql $ord LIMIT $start, $per_page";

//echo $sql;die;

$db_trip = $obj->MySQLSelect($sql);

//echo "<pre>";print_r($db_trip);die;

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



$generalConfigPaymentArr = $generalobj->getGeneralVarAll_Payment_Array();

//echo "<pre>";print_r($generalConfigPaymentArr);die;

$SYSTEM_PAYMENT_FLOW = "Method-1";

if (isset($generalConfigPaymentArr['SYSTEM_PAYMENT_FLOW'])) {

    $SYSTEM_PAYMENT_FLOW = $generalConfigPaymentArr['SYSTEM_PAYMENT_FLOW'];

}

$ufxEnable = $generalobj->CheckUfxServiceAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status

$hotelPanel = isHotelPanelEnable(); 

$kioskPanel = isKioskPanelEnable();

?>

<!DOCTYPE html>

<html lang="en">

    <!-- BEGIN HEAD-->

    <head>

        <meta charset="UTF-8" />

        <title><?= $SITE_NAME ?> | Payment Report</title>

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

    <body class="padTop53" >

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

                                <h2>Payment Report</h2>

                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->

                            </div>

                        </div>

                        <hr />

                    </div>

                    <?php include('valid_msg.php'); ?>

                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >

                        <div class="Posted-date mytrip-page payment-report">

                            <input type="hidden" name="action" value="search" />

                            <!-- <h3>Search <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>...</h3> -->

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

                                    <select class="form-control filter-by-text" name = 'searchCompany' data-text="Select Company" id="searchCompany">

                                        <option value="">Select Company</option>

                                        <?php foreach ($db_company as $dbc) { ?>

                                            <option value="<?php echo $dbc['iCompanyId']; ?>" <?php

                                            if ($searchCompany == $dbc['iCompanyId']) {

                                                echo "selected";

                                            }

                                            ?>><?php echo $generalobjAdmin->clearCmpName($dbc['vCompany']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbc['vEmail']); ?> )</option>

                                                <?php } ?>

                                    </select>

                                </div>

                                <div class="col-lg-3 select001">

                                    <select class="form-control filter-by-text driver_container" name = 'searchDriver' data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>">

                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>

                                        <?php foreach ($db_drivers as $dbd) { ?>

                                            <option value="<?php echo $dbd['iDriverId']; ?>" <?php

                                            if ($searchDriver == $dbd['iDriverId']) {

                                                echo "selected";

                                            }

                                            ?>><?php echo $generalobjAdmin->clearName($dbd['driverName']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbd['vEmail']); ?> )</option>

                                                <?php } ?>

                                    </select>

                                </div>

                            </span>

                        </div>



                        <div class="row payment-report payment-report1 payment-report2">

                            <div class="col-lg-3">

                                <select class="form-control filter-by-text" name = 'searchRider' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">

                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>

                                    <?php foreach ($db_rider as $dbr) { ?>

                                        <option value="<?php echo $dbr['iUserId']; ?>" <?php

                                        if ($searchRider == $dbr['iUserId']) {

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

                                    <?php

                                    $payMethod = "Card";

//echo $SYSTEM_PAYMENT_FLOW;die;

                                    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {

                                        $payMethod = "Wallet";

                                    }

                                    ?>

                                    <option value="Card" <? if ($searchPaymentType == "Card") { ?>selected <? } ?>><?= $payMethod; ?></option>

                                    <?php if ($ENABLE_CORPORATE_PROFILE == "Yes") { ?>

                                        <option value="Organization" <? if ($searchPaymentType == "Organization") { ?>selected <? } ?>>Organization</option>

                                    <?php } ?>

<!--  <option value="Paypal" <? if ($searchPaymentType == "Paypal") { ?>selected <? } ?>>Paypal</option> -->

                                </select>

                            </div>

                            <div class="col-lg-3">

                                <select class="form-control" name='searchDriverPayment' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">

                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Status</option>

                                    <option value="Settelled" <?php if ($searchDriverPayment == "Settelled") { ?>selected <?php } ?>>Settled</option>

                                    <option value="Unsettelled" <?php if ($searchDriverPayment == "Unsettelled") { ?>selected <?php } ?>>Unsettled</option>

                                </select>

                            </div>

                            <div class="col-lg-2">

                                <input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number" class="form-control search-trip001" value="<?php echo $serachTripNo; ?>"/>

                            </div>

                        </div>



                        <?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') { ?>

                            <div class="row payment-report payment-report1 payment-report2" style="margin-top: 21px;">

                                <div class="col-lg-2">

                                    <select class="form-control" name = 'eType' >

                                        <option value="">Service Type</option>

                                        <?php if ($rideEnable=="Yes") { ?>

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

                                                <?php } } if (($APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery-UberX") && $ufxEnable == "Yes") { ?>

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

                                                <?php } if (checkFlyStationsModule()) { ?>

                                            <option value="Fly" <?php

                                            if ($eType == "Fly") {

                                                echo "selected";

                                            }

                                            ?>><?php echo $langage_lbl_admin['LBL_HEADER_RDU_FLY_RIDE']; ?> </option>

                                                <? } ?>

                                    </select>

                                </div>

                            </div>

                        <?php } ?>

                        <div class="tripBtns001"><b>

                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />

                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'payment_report.php'"/>

                                <?php if (count($db_trip) > 0) { ?>

                                    <button type="button" onClick="reportExportTypes('driver_payment')" class="export-btn001" >Export</button>



                                <?php } ?>

                            </b>

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



                                                    <th><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN']; ?> </th>

                                                    <th width="10%"><a href="javascript:void(0);" onClick="Redirect(1,<?php

                                                        if ($sortby == '1') {

                                                            echo $order;

                                                        } else {

                                                            ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "/" . $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?php

                                                                           if ($sortby == 1) {

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

                                                    <th style="text-align:right;">A=Total Fare</th>

                                                    <?php

                                                    $nxtChar = "B";

                                                    $recAmt = "";

                                                    if (in_array(1, $enableCashReceivedCol)) {

                                                        $recAmt = $nxtChar;

                                                        ?>

                                                        <th style="text-align:right;"><?= $nxtChar; ?>=Cash Received</th>

                                                        <?php

                                                        $nxtChar = "C";

                                                    }

                                                    $pltAmt = $nxtChar;

                                                    ?>

                                                    <th style="text-align:right;"><?= $nxtChar; ?>=Commission Amount</th>

                                                    <?php

                                                    $nxtChar = "C";

                                                    if ($nxtChar == "C") {

                                                        $nxtChar = "D";

                                                    }

                                                    $ttaxAmt = $nxtChar;

                                                    ?>

                                                    <th style="text-align:right;"><?= $nxtChar; ?>=Total Tax</th>

                                                    <?php

                                                    $nxtChar = "D";

                                                    if ($nxtChar == "D") {

                                                        $nxtChar = "E";

                                                    }

                                                    $tipAmt = "";

                                                    ?>

                                                    <?php

                                                    if (in_array(1, $enableTipCol)) {

                                                        $tipAmt = $nxtChar;

                                                        ?>

                                                        <th><?= $nxtChar; ?>=Tip</th>

                                                    <?php } ?>

                                                    <?php

                                                    $nxtChar = "E";

                                                    if ($nxtChar == "E") {

                                                        $nxtChar = "F";

                                                    }

                                                    $outAmt = $nxtChar;

                                                    ?>

                                                    <th><?= $nxtChar; ?>=<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Outstanding Amount</th>

                                                    <?php

                                                    $nxtChar = "F";

                                                    if ($nxtChar == "F") {

                                                        $nxtChar = "G";

                                                    }

                                                    ?>

                                                    <?

                                                    $bookAmt = "";

                                                    if ($hotelPanel > 0 || $kioskPanel  > 0) {

                                                        $bookAmt = $nxtChar;

                                                        ?>

                                                        <th><?= $nxtChar; ?>=Booking Fees</th>

                                                    <? } ?>

                                                    <?php

                                                    $nxtChar = "G";

                                                    if ($nxtChar == "G") {

                                                        $nxtChar = "H";

                                                    }

                                                    $ppAmt = $nxtChar;

                                                    ?>

                                                    <th style="text-align:right;"><?= $nxtChar; ?> = <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> pay / Take Amount</th>

                                                    <th><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Status</th>

                                                    <th>Payment method</th>

                                                    <th><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Status</th> 

                                                    <th></th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                <?php

                                                $set_unsetarray = array();

                                                //echo "<pre>";print_r($db_trip);die;

                                                if (count($db_trip) > 0) {

                                                    $driver_payment = $total_tip = $tot_fare = $tot_site_commission = $tot_hotel_commision = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $tot_ifare = $tot_tax = 0.00;

                                                    for ($i = 0; $i < count($db_trip); $i++) {

                                                        $class_setteled = "";

                                                        if ($db_trip[$i]['eDriverPaymentStatus'] == 'Settelled') {

                                                            $class_setteled = "setteled-class";

                                                        }

                                                        $iTripId = $db_trip[$i]['iTripId'];

                                                        //echo "<pre>";print_r($db_trip);die;

                                                        $iFare = $generalobj->setTwoDecimalPoint($db_trip[$i]['iFare']);

                                                        $totTax = $generalobj->setTwoDecimalPoint($db_trip[$i]['fTax1'] + $db_trip[$i]['fTax2']);

                                                        $orgName = "";

                                                        if (isset($orgNameArr[$db_trip[$i]['iOrganizationId']]) && $orgNameArr[$db_trip[$i]['iOrganizationId']] != "") {

                                                            $orgName = "(" . $orgNameArr[$db_trip[$i]['iOrganizationId']] . ")";

                                                            $iFare = 0;

                                                        }

                                                        $poolTxt = "";

                                                        if ($db_trip[$i]['ePoolRide'] == "Yes") {

                                                            $poolTxt = " (Pool)";

                                                        }

                                                        $totalfare = $generalobj->setTwoDecimalPoint($db_trip[$i]['fTripGenerateFare']);

                                                        $site_commission = $generalobj->setTwoDecimalPoint($db_trip[$i]['fCommision']);

                                                        $promocodediscount = $generalobj->setTwoDecimalPoint($db_trip[$i]['fDiscount']);

                                                        $wallentPayment = $generalobj->setTwoDecimalPoint($db_trip[$i]['fWalletDebit']);

                                                        $fOutStandingAmount = $generalobj->setTwoDecimalPoint($db_trip[$i]['fOutStandingAmount']);

                                                        $fHotelCommision = $generalobj->setTwoDecimalPoint($db_trip[$i]['fHotelCommision']);

                                                        $fTipPrice = $generalobj->setTwoDecimalPoint($db_trip[$i]['fTipPrice']);

                                                        $tipPayment = 0;

                                                        //echo $iTripId."===>"."(" . $totalfare . "+" . $fTipPrice . ")-(" . $site_commission . "+" . $totTax . "+" . $fOutStandingAmount . "+" . $fHotelCommision ."+".$iFare. ")<br>";

                                                        //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start

                                                        $driver_payment = $dispay_driver_payment = $generalobj->setTwoDecimalPoint(($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision));

                                                        if ($db_trip[$i]['vTripPaymentMode'] == "Cash") {

                                                            $driver_payment = $dispay_driver_payment = $generalobj->setTwoDecimalPoint($driver_payment - $iFare);

                                                            $tot_ifare += $iFare;

                                                        }

                                                        //echo $iTripId . "===>" .$driver_payment."<br>";

                                                        //Added By HJ On 08-08-2019 For Check Driver Wallet Debit Amount Start As Per Discuss With KS Sir

                                                        $driverDebitAmt = 0;



                                                        if (isset($tripWalletArr[$iTripId]) && $tripWalletArr[$iTripId] > 0) {

                                                            $driverDebitAmt = $generalobj->setTwoDecimalPoint($tripWalletArr[$iTripId]);

                                                            //echo $driverDebitAmt."+".$driver_payment."<br>";die;

                                                            //$driver_payment = $dispay_driver_payment = $generalobj->setTwoDecimalPoint($driverDebitAmt + $driver_payment);

                                                            ///echo $generalobj->setTwoDecimalPoint($driver_payment);die;

                                                        }

                                                        //echo $iTripId . "===>" .$driver_payment."<br>";

                                                        //Added By HJ On 08-08-2019 For Check Driver Wallet Debit Amount End As Per Discuss With KS End

                                                        //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start

                                                        $set_unsetarray[] = $db_trip[$i]['eDriverPaymentStatus'];

                                                        $eTypenew = $db_trip[$i]['eType'];

                                                        if ($eTypenew == 'Ride') {

                                                            $trip_type = 'Ride';

                                                        } else if ($eTypenew == 'UberX') {

                                                            $trip_type = 'Other Services';

                                                        } else {

                                                            $trip_type = 'Delivery';

                                                        }

                                                        if (!empty($db_trip[$i]['iFromStationId']) && !empty($db_trip[$i]['iToStationId'])) {

                                                            $trip_type = 'Fly';

                                                        }

                                                        $trip_type .= $poolTxt;

                                                        if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {

                                                            $tripTypeTxt = "Rental " . $trip_type . "<br/> ( Hail )";

                                                        } else if ($db_trip[$i]['iRentalPackageId'] > 0) {

                                                            $tripTypeTxt = "Rental " . $trip_type;

                                                        } else if ($db_trip[$i]['eHailTrip'] == "Yes") {

                                                            $tripTypeTxt = "Hail " . $trip_type;

                                                        } else {

                                                            $tripTypeTxt = $trip_type;

                                                        }

                                                        $tot_fare += $totalfare;

                                                        $tot_site_commission += $site_commission;

                                                        $tot_hotel_commision += $fHotelCommision;

                                                        $tot_promo_discount += $promocodediscount;

                                                        $tot_wallentPayment += $wallentPayment;

                                                        $tot_tax += $totTax;

                                                        $total_tip += $fTipPrice;

                                                        $tot_driver_refund += $driver_payment;

                                                        //echo $iTripId . "===>" .$driver_payment."<br>";

                                                        $tot_outstandingAmount += $fOutStandingAmount;

                                                        ?>

                                                        <tr class="gradeA <?= $class_setteled ?>">

                                                            <td align="center"><a href="javascript:void(0);" onclick='javascript:window.open("invoice.php?iTripId=<?= $db_trip[$i]['iTripId'] ?>", "_blank")'; ><?= $db_trip[$i]['vRideNo']; ?></a><br>(<?= $tripTypeTxt; ?>)</td>

                                                            <td><b><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "</b>: " . $generalobjAdmin->clearName($db_trip[$i]['drivername']); ?><br><b><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] . "</b>: " . $generalobjAdmin->clearName($db_trip[$i]['riderName']); ?></td>

                                                                        <td><?= $generalobjAdmin->DateTime($db_trip[$i]['tTripRequestDate']); ?></td>

                                                                        <td align="right">

                                                                            <?php

                                                                            if ($db_trip[$i]['fTripGenerateFare'] != "" && $db_trip[$i]['fTripGenerateFare'] != 0) {

                                                                                $totFareHtml = "<a href='javascript:void(0);' style='text-decoration: underline;' onClick='showTotalFareDetails(" . $db_trip[$i]['iUserId'] . "," . $db_trip[$i]['iTripId'] . "," . $db_trip[$i]['vRideNo'] . ",1);'>" . $generalobj->trip_currency($db_trip[$i]['fTripGenerateFare']) . "</a>";

                                                                            } else {

                                                                                $totFareHtml = "-";

                                                                            }

                                                                            echo $totFareHtml;

                                                                            ?>

                                                                        </td>

                                                                        <?php

                                                                        if (in_array(1, $enableCashReceivedCol)) {

                                                                            if ($db_trip[$i]['vTripPaymentMode'] != "Card") {

                                                                                ?>

                                                                                <td align="right"><?= $totFareHtml = "<a href='javascript:void(0);' style='text-decoration: underline;' onClick='showTotalFareDetails(" . $db_trip[$i]['iUserId'] . "," . $db_trip[$i]['iTripId'] . "," . $db_trip[$i]['vRideNo'] . ",2);'>" . $generalobj->trip_currency($iFare) . "</a>"; ?></td>

                                                                            <?php } else { ?>

                                                                                <td align="right">-</td>

                                                                            <?php } ?>

                                                                        <?php } ?>

                                                                        <td align="right"><?php

                                                                            if ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0) {

                                                                                echo $generalobj->trip_currency($db_trip[$i]['fCommision']);

                                                                            } else {

                                                                                echo '-';

                                                                            }

                                                                            ?></td>

                                                                        <td align="right"><?= $generalobj->trip_currency($totTax); ?></td>

                                                                        <?php if (in_array(1, $enableTipCol)) { ?>

                                                                            <td align="right">

                                                                                <?php

                                                                                if ($db_trip[$i]['fTipPrice'] != "0") {

                                                                                    echo $generalobj->trip_currency($db_trip[$i]['fTipPrice']);

                                                                                    //echo $db_trip[$i]['fTipPrice'];

                                                                                } else {

                                                                                    echo "-";

                                                                                }

                                                                                ?>

                                                                            </td>

                                                                        <?php } ?>

                                                                        <td align="right">

                                                                            <?php

                                                                            if ($db_trip[$i]['fOutStandingAmount'] != "" && $db_trip[$i]['fOutStandingAmount'] != 0) {

                                                                                echo $generalobj->trip_currency($db_trip[$i]['fOutStandingAmount']);

                                                                            } else {

                                                                                echo "-";

                                                                            }

                                                                            ?>

                                                                        </td>

                                                                        <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>

                                                                            <td align="right"><?php

                                                                                if ($db_trip[$i]['fHotelCommision'] != "" && $db_trip[$i]['fHotelCommision'] != 0) {

                                                                                    echo $generalobj->trip_currency($db_trip[$i]['fHotelCommision']);

                                                                                } else {

                                                                                    echo '-';

                                                                                }

                                                                                ?></td>

                                                                        <? } ?>

                                                                        <td align="right">

                                                                            <?php

                                                                            if ($driver_payment != "" && $driver_payment != 0) {

                                                                                //echo $generalobj->trip_currency($driver_payment);

                                                                                $totFareHtml = "<a href='javascript:void(0);' data-provideamt=" . $generalobj->setTwoDecimalPoint($dispay_driver_payment) . " data-symbol=" . $vSymbol . " data-payMode=" . $db_trip[$i]['vTripPaymentMode'] . " data-totFare=" . $generalobj->setTwoDecimalPoint($db_trip[$i]['fTripGenerateFare']) . " data-ifare=" . $generalobj->setTwoDecimalPoint($iFare) . " data-pfees=" . $generalobj->setTwoDecimalPoint($db_trip[$i]['fCommision']) . " data-tax=" . $generalobj->setTwoDecimalPoint($totTax) . " data-tip=" . $generalobj->setTwoDecimalPoint($db_trip[$i]['fTipPrice']) . " data-out=" . $generalobj->setTwoDecimalPoint($db_trip[$i]['fOutStandingAmount']) . " data-book=" . $generalobj->setTwoDecimalPoint($db_trip[$i]['fHotelCommision']) . " style='text-decoration: underline;' onClick='showProvicePayDetails(this);'>" . $generalobj->trip_currency($driver_payment) . "</a>";

                                                                            } else {

                                                                                $totFareHtml = "-";

                                                                            }

                                                                            echo $totFareHtml;

                                                                            ?>

                                                                        </td>

                                                                        <td><?= $db_trip[$i]['iActive']; ?></td>

                                                                        <?php if ($db_trip[$i]['vTripPaymentMode'] == "Card" && $db_trip[$i]['ePayWallet'] == 'Yes') { ?>

                                                                            <td><?= $langage_lbl_admin['LBL_WALLET_TXT']; ?></td>

                                                                        <?php } else { ?>

                                                                            <td><?= $db_trip[$i]['vTripPaymentMode'] . "<br>" . $orgName; ?></td>

                                                                        <?php } ?>

                                                                        <td><?php

                                                                            if ($db_trip[$i]['eDriverPaymentStatus'] == "Settelled") {

                                                                                echo "Settled";

                                                                            } else if ($db_trip[$i]['eDriverPaymentStatus'] == "Unsettelled") {

                                                                                echo "Unsettled";

                                                                            } else {

                                                                                echo $db_trip[$i]['eDriverPaymentStatus'];

                                                                            }

                                                                            ?>

                                                                        </td>

                                                                        <td>

                                                                            <?

                                                                            if ($db_trip[$i]['eDriverPaymentStatus'] == 'Unsettelled') {

                                                                                ?>

                                                                                <input class="validate[required]" type="checkbox" value="<?= $db_trip[$i]['iTripId'] ?>" id="iTripId_<?= $db_trip[$i]['iTripId'] ?>" name="iTripId[]">

                                                                                <?

                                                                            }

                                                                            ?>

                                                                        </td>

                                                                        </tr>

                                                                    <? } ?>

                                                                    <tr class="gradeA">

                                                                        <td><b>Total</b></td>

                                                                        <td>--</td>

                                                                        <td>--</td>

                                                                        <td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_fare)); ?></td>

                                                                        <?php if (in_array(1, $enableCashReceivedCol)) { ?>

                                                                            <td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_ifare)); ?></td>

                                                                        <?php } ?>

                                                                        <td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_site_commission)); ?></td>

                                                                        <td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_tax)); ?></td>

                                                                        <!--<td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_promo_discount)); ?></td>

                                                                        <td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_wallentPayment)); ?></td>-->

                                                                        <?php if (in_array(1, $enableTipCol)) { ?>

                                                                            <td align="right"><?= $generalobj->trip_currency(cleanNumber($total_tip)); ?></td>

                                                                        <?php } ?>

                                                                        <td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_outstandingAmount)); ?></td>

                                                                        <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>

                                                                            <td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_hotel_commision)); ?></td>

                                                                        <?php } ?>

                                                                        <td align="right"><?= $generalobj->trip_currency(cleanNumber($tot_driver_refund)); ?></td>

                                                                        <?php if (in_array("Unsettelled", $set_unsetarray)) { ?>



                                                                            <td colspan="4"  align="right"><div class="row payment-report-button">

                                                                                    <span style="margin-right: 15px;">

                                                                                        <a onClick="Paytodriver()" href="javascript:void(0);"><button class="btn btn-primary" type="button">Mark As Settled</button></a>

                                                                                    </span>

                                                                                </div>

                                                                            </td>

                                                                        <? }

                                                                        ?>

                                                                    </tr>

                                                                <?php } else {

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

                                                                <div class="admin-notes">

                                                                    <h4>Note:</h4>

                                                                    <ul>

                                                                        <li>

                                                                            Payement Mode : Card <br><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Pay / Take Amount (<?= $ppAmt; ?>) = A-<?= $pltAmt; ?>-<?= $ttaxAmt; ?><?php

                                                                            if ($tipAmt != "") {

                                                                                echo " + " . $tipAmt;

                                                                            }

                                                                            ?><?= " - " . $outAmt; ?><?php

                                                                            if ($bookAmt != "") {

                                                                                echo " - " . $bookAmt;

                                                                            }

                                                                            ?>

                                                                        </li>

                                                                        <?php if ($recAmt != "") { ?>

                                                                            <li>

                                                                                Payement Mode : Cash <br><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Pay / Take Amount (<?= $ppAmt; ?>) = (A-<?= $pltAmt; ?>-<?= $ttaxAmt; ?><?php

                                                                                if ($tipAmt != "") {

                                                                                    echo " + " . $tipAmt;

                                                                                }

                                                                                ?><?= " - " . $outAmt; ?>



                                                                                <?php

                                                                                if ($bookAmt != "") {

                                                                                    echo " - " . $bookAmt . ")-";

                                                                                } else {

                                                                                    echo ") - ";

                                                                                }

                                                                                ?><?= $recAmt; ?>

                                                                            </li>



                                                                        <?php } ?>



                                                                    </ul>

                                                                </div>

                                                                <div class="clear"></div>

                                                                </div>

                                                                </div>

                                                                <!--END PAGE CONTENT -->

                                                                </div>

                                                                <!--END MAIN WRAPPER -->



                                                                <form name="pageForm" id="pageForm" action="action/payment_report.php" method="post" >

                                                                    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">

                                                                    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">

                                                                    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >

                                                                    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >

                                                                    <input type="hidden" name="action" value="<?php echo $action; ?>" >

                                                                    <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>" >

                                                                    <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>" >

                                                                    <input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>" >

                                                                    <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>" >

                                                                    <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>" >

                                                                    <input type="hidden" name="searchDriverPayment" value="<?php echo $searchDriverPayment; ?>" >

                                                                    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >

                                                                    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >

                                                                    <input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>" >

                                                                    <input type="hidden" name="eType" value="<?php echo $eType; ?>" >

                                                                    <input type="hidden" name="method" id="method" value="" >

                                                                </form>

                                                                <div  class="modal fade" id="fare_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >

                                                                    <div class="modal-dialog" >

                                                                        <div class="modal-content">

                                                                            <div class="modal-header">

                                                                                <h4>

                                                                                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>

                                                                                    <span id="fareRideNo"></span>

                                                                                    <button type="button" class="close" data-dismiss="modal">x</button>

                                                                                </h4>

                                                                            </div>

                                                                            <div class="modal-body" style="max-height: 450px;overflow: auto;">

                                                                                <div id='faredata'></div>

                                                                            </div>

                                                                        </div>

                                                                    </div>



                                                                </div>

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

            function showProvicePayDetails(invelem) {

            //console.log(invelem);

            var provideAmt = $(invelem).attr("data-provideamt");

            var symbol = $(invelem).attr("data-symbol");

            var payMode = $(invelem).attr("data-paymode");

            var totFareAmt = $(invelem).attr("data-totfare");

            var cashReceivedAmt = $(invelem).attr("data-ifare");

            var platformFeeAmt = $(invelem).attr("data-pfees");

            var taxAmt = $(invelem).attr("data-tax");

            var tipAmt = $(invelem).attr("data-tip");

            var outStandingAmt = $(invelem).attr("data-out");

            var bookingFeeAmt = $(invelem).attr("data-book");



            var fareHtml = '<table style="width:100%" cellpadding="5" cellspacing="0" border="0"><tbody>';

            fareHtml += '<tr><td><b>Description</b></td><td align="right"><b>Amount</b></td></tr>';

            fareHtml += '<tr><td colspan="2"><div style="border-top:1px dashed #d1d1d1"></div></td></tr>';

            fareHtml += '<tr><td><b>A</b>-Total Fare</td><td align="right">' + symbol + '' + totFareAmt + '</td></tr>';

            fareHtml += '<tr><td><b><?= $pltAmt; ?></b>-Platform Fees</td><td align="right">' + symbol + '-' + platformFeeAmt + '</td></tr>';

            fareHtml += '<tr><td><b><?= $ttaxAmt; ?></b>-Total Tax</td><td align="right">' + symbol + '-' + taxAmt + '</td></tr>';

            <?php if (in_array(1, $enableTipCol)) { ?>

                fareHtml += '<tr><td><b><?= $tipAmt; ?></b>-Tip</td><td align="right">' + symbol + '' + tipAmt + '</td></tr>';

            <?php } ?>

            fareHtml += '<tr><td><b><?= $outAmt; ?></b>-Outstanding Amount</td><td align="right">' + symbol + '-' + outStandingAmt + '</td></tr>';

            <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>

                fareHtml += '<tr><td><b><?= $bookAmt; ?></b>-Booking Fees</td><td align="right">' + symbol + '-' + bookingFeeAmt + '</td></tr>';

            <?php } ?>

            <?php if (in_array(1, $enableCashReceivedCol)) { ?>

                if (payMode == "Cash") {

                    fareHtml += '<tr><td><b><?= $recAmt; ?></b>-Cash Received</td><td align="right">' + symbol + '-' + cashReceivedAmt + '</td></tr>';

                }

            <?php } ?>

            fareHtml += '<tr><td colspan="2"><div style="border-top:1px dashed #d1d1d1"></div></td></tr>';

            fareHtml += '<tr><td><b><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Pay / Take Amount</b></td><td align="right"><b>' + symbol + '' + provideAmt + '</b></td></tr>';

            $("#faredata").html("");

            $("#fare_detail_modal").modal('show');

            $("#fareRideNo").text("<?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Pay / Take Amount Breakdown");

            $("#faredata").html(fareHtml);

            }

            function showTotalFareDetails(userId, tripId, rideNo, actionId) {

            if (actionId == 1) {

                var action = "totalFare";

                var headTxt = "Total Fare";

            } else if (actionId == 2) {

                var action = "cashreceived";

                var headTxt = "Cash Received";

            }

            //$(".loader-default").fadeIn("slow");

            $.ajax({

                type: 'POST',

                url: 'ajax_get_fare_details.php',

                data: {'action': action, 'userId': userId, 'tripId': tripId},

                cache: false,

                success: function (data) {

                    //console.log(data);

                    //$(".loader-default").fadeOut("slow");

                    $("#faredata").html("");

                    $("#fare_detail_modal").modal('show');

                    $("#fareRideNo").text(headTxt + " Breakdown For <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> No :" + rideNo);

                    $("#faredata").html(data);

                },

                error: function (e) {

                    //$(".loader-default").fadeOut("slow");

                    alert("Fare details not found");

                    $("#fare_detail_modal").modal('hide');

                    return false;

                }

            });

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

                                                                </script>

                                                                </body>

                                                                <!-- END BODY-->

                                                                </html>

