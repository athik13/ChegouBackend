<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();
//include_once("../generalFunctions_dl_shark.php");

if (!$userObj->hasPermission('manage-cancelled-order-report')) {
    $userObj->redirect();
}


$script = 'Cancelled Order Report';

function cleanNumber($num) {
    return str_replace(',', '', $num);
}

//data for select fields
$sql = "SELECT iCompanyId,vCompany,vEmail FROM company WHERE eStatus != 'Deleted' AND eSystem='DeliverAll' order by vCompany";
$db_company = $obj->MySQLSelect($sql);
//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY o.iOrderId DESC';

if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
    else
        $ord = " ORDER BY c.vCompany DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY ru.vName ASC";
    else
        $ord = " ORDER BY ru.vName DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY o.tOrderRequestDate ASC";
    else
        $ord = " ORDER BY o.tOrderRequestDate DESC";
}

if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY o.ePaymentOption ASC";
    else
        $ord = " ORDER BY o.ePaymentOption DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$serachOrderNo = isset($_REQUEST['serachOrderNo']) ? $_REQUEST['serachOrderNo'] : '';
$searchRestaurantPayment = isset($_REQUEST['searchRestaurantPayment']) ? $_REQUEST['searchRestaurantPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';

if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
    }
    if ($serachOrderNo != '') {
        $ssql .= " AND o.vOrderNo ='" . $serachOrderNo . "'";
    }
    if ($searchCompany != '') {
        $ssql .= " AND c.iCompanyId ='" . $searchCompany . "'";
    }
    if ($searchRestaurantPayment != '') {
        $ssql .= " AND o.eRestaurantPaymentStatus ='" . $searchRestaurantPayment . "'";
    }
    if ($searchServiceType != '') {
        $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "'";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND o.ePaymentOption ='" . $searchPaymentType . "'";
    }
}

$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
}
$ssql .= " AND sc.iServiceId IN(".$enablesevicescategory.")";

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql1 = "SELECT o.iOrderId,o.vOrderNo,sc.vServiceName_" . $default_lang . " as vServiceName,o.tOrderRequestDate,o.fTotalGenerateFare,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fDriverPaidAmount,o.eAdminPaymentStatus,o.ePaymentOption,CONCAT(d.vName,' ',d.vLastName) AS driverName,o.iStatusCode,os.vStatus,t.fDeliveryCharge as driverearning,o.fCancellationCharge,oa.fCancellationFare,oa.ePaidByPassenger,oa.vOrderAdjusmentId FROM orders AS o LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN trips as t ON t.iOrderId=o.iOrderId LEFT JOIN trip_outstanding_amount as oa on oa.iOrderId=o.iOrderId LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND o.iStatusCode IN ('7','8') $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql1);

//$total_results = $totalData[0]['Total'];
$total_results = count($totalData);
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}

// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
$sql = "SELECT o.iOrderId,o.vOrderNo,sc.vServiceName_" . $default_lang . " as vServiceName,o.tOrderRequestDate,o.fTotalGenerateFare,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fDriverPaidAmount,o.eAdminPaymentStatus,o.ePaymentOption,CONCAT(d.vName,' ',d.vLastName) AS driverName,o.iStatusCode,os.vStatus,t.fDeliveryCharge as driverearning,o.fCancellationCharge,oa.fCancellationFare,oa.ePaidByPassenger,oa.vOrderAdjusmentId FROM orders AS o LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN trips as t ON t.iOrderId=o.iOrderId LEFT JOIN trip_outstanding_amount as oa on oa.iOrderId=o.iOrderId LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND o.iStatusCode IN ('7','8') $ssql $trp_ssql $ord LIMIT $start, $per_page";
$db_trip = $obj->MySQLSelect($sql);
//print_R($db_trip);die;
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

$settlementorderid = isset($_REQUEST['settlementorderid']) ? $_REQUEST['settlementorderid'] : '';
if ($action == 'settelled' && $settlementorderid != '') {
    $fDriverPaidAmount = isset($_REQUEST['fDeliveryCharge']) ? $_REQUEST['fDeliveryCharge'] : '';
    $fRestaurantPaidAmount = isset($_REQUEST['fRestaurantPayAmount']) ? $_REQUEST['fRestaurantPayAmount'] : '';

    $query = "UPDATE orders SET fRestaurantPaidAmount = '" . $fRestaurantPaidAmount . "' ,fDriverPaidAmount='" . $fDriverPaidAmount . "',eAdminPaymentStatus = 'Settled',eRestaurantPaymentStatus = 'Settled' WHERE iOrderId = '" . $settlementorderid . "'";
    $obj->sql_query($query);

    $tQuery = "UPDATE trips SET eDriverPaymentStatus = 'Settled' WHERE iOrderId = '" . $settlementorderid . "'";
    $obj->sql_query($tQuery);
    echo "<script>location.href='cancelled_report.php'</script>";
}
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 Start
$cardText = "Card";
if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
    $cardText = "Wallet";
}
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 End
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Order Payment Report</title>
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
        <!-- Main Loading -->
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
                                <h2>Cancelled / Refunded Report</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page payment-report">
                            <input type="hidden" name="action" value="search" />
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
                                    <select class="form-control" name='searchPaymentType' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>">
                                        <option value="">Select Payment Type</option>
                                        <option value="Cash" <? if ($searchPaymentType == "Cash") { ?>selected <? } ?>>Cash</option>
                                        <option value="Card" <? if ($searchPaymentType == "Card") { ?>selected <? } ?>><?= $cardText; ?></option>
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <input type="text" id="serachOrderNo" name="serachOrderNo" placeholder="<?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> Number" class="form-control search-trip001" value="<?php echo $serachOrderNo; ?>"/>
                                </div>
                            </span>
                        </div>
                        <? if (count($allservice_cat_data) > 1) { ?>
                            <div class="row payment-report payment-report1 payment-report2">
                                <span>
                                    <div class="col-lg-2 select001" style="padding-right:15px;">
                                        <select class="form-control filter-by-text" name = "searchServiceType" data-text="Select Serivce Type">
                                            <option value="">Select <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                                            <?php foreach ($allservice_cat_data as $value) { ?>
                                                <option value="<?php echo $value['iServiceId']; ?>" <?php
                                                if ($searchServiceType == $value['iServiceId']) {
                                                    echo "selected";
                                                }
                                                ?>><?php echo $generalobjAdmin->clearName($value['vServiceName']); ?></option>
                                                    <?php } ?>
                                        </select>
                                    </div>
                                </span>
                            </div>
                        <? } ?>
                        <div class="tripBtns001"><b>
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'cancelled_report.php'"/>
                             <?php if (count($db_trip) > 0 && SITE_TYPE != 'Demo') { ?>
                                        <button type="button" onClick="exportlist()" class="export-btn001" >Export</button>
                            <?php } ?>
                            </b>
                        </div>
                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" id="actionpayment" name="actionpayment" value="pay_restaurant">
                                        <input type="hidden"  name="iOrderId" id="iOrderId" value="">
                                        <input type="hidden"  name="ePayRestaurant" id="ePayRestaurant" value="">
                                        <table class="table table-bordered" id="dataTables-example123" >
                                            <thead>
                                                <tr>
                                                    <? if (count($allservice_cat_data) > 1) { ?>
                                                        <th>Service Type</th>
                                                    <? } ?>
                                                    <th><?php echo $langage_lbl_admin['LBL_RIDE_NO_ADMIN_DL']; ?># </th>
                                                    <th><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                        if ($sortby == '4') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']; ?> Date <?php
                                                               if ($sortby == 4) {
                                                                   if ($order == 0) {
                                                                       ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th style="text-align:right;">PayOut To <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?></th>
                                                    <th style="text-align:right;">Payout to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></th>
                                                    <th style="text-align:right;">Cancellation Charges For <?= $langage_lbl_admin['LBL_RIDER'] ?></th>
                                                    <th><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']; ?> Status</th>
                                                    <th><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                        if ($sortby == '5') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Payment method<?php
                                                            if ($sortby == 5) {
                                                                if ($order == 0) {
                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?
                                                $set_unsetarray = array();
                                                if (count($db_trip) > 0) {
                                                    for ($i = 0; $i < count($db_trip); $i++) {
                                                        $class_setteled = "";
                                                        if ($db_trip[$i]['eAdminPaymentStatus'] == 'Settled') {
                                                            $class_setteled = "setteled-class";
                                                        }
                                                        $set_unsetarray[] = $db_trip[$i]['eAdminPaymentStatus'];

                                                        $payment_to_driver = $generalobjAdmin->getPaymentToDriver($db_trip[$i]['iOrderId']);
                                                        //echo "<pre>";print_r($db_trip[$i]);die;
                                                        ?>
                                                        <tr class="gradeA <?= $class_setteled ?>">
                                                            <? if (count($allservice_cat_data) > 1) { ?>
                                                                <td><? echo $db_trip[$i]['vServiceName']; ?></td>
                                                            <? } ?>
                                                            <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                                <td><a href="order_invoice.php?iOrderId=<?= $db_trip[$i]['iOrderId'] ?>" target="_blank"><? echo $db_trip[$i]['vOrderNo']; ?></a></td>
        <? } else { ?>
                                                                <td><? echo $db_trip[$i]['vOrderNo']; ?></td>
        <?php } ?>


                                                            <td><?= $generalobjAdmin->DateTime($db_trip[$i]['tOrderRequestDate']); ?></td>
                                                            <td align="right">
                                                                Actual Amount : <?php echo $generalobj->trip_currency($db_trip[$i]['fRestaurantPayAmount']); ?><br/>
                                                                You Paid : <?php echo $generalobj->trip_currency($db_trip[$i]['fRestaurantPaidAmount']); ?>
                                                            </td>
                                                            <td align="right">
                                                                <? if ($payment_to_driver == 0) { ?>
                                                                    <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> not Assign
        <?php } else { ?>
                                                                    Actual Amount : <?php echo $generalobj->trip_currency($db_trip[$i]['driverearning']); ?><br/>
                                                                    You Paid : <?php echo $generalobj->trip_currency($db_trip[$i]['fDriverPaidAmount']); ?>
                                                                <?php } ?>
                                                            </td>
                                                            <td align="right"><?php echo $generalobj->trip_currency($db_trip[$i]['fCancellationCharge']); ?>
                                                                <br/>
                                                                <? if ($db_trip[$i]['ePaymentOption'] == 'Cash' && $db_trip[$i]['ePaidByPassenger'] == 'Yes') { ?>
                                                                    ( Paid In Order No# : <?php echo $db_trip[$i]['vOrderAdjusmentId'] ?>)
                                                                <? } else if ($db_trip[$i]['ePaymentOption'] == 'Cash') { ?>
                                                                    ( Outstanding )
        <? } else if ($db_trip[$i]['ePaymentOption'] == 'Card') { ?>
                                                                    ( Paid )
                                                                <? } ?>
                                                            </td>
                                                            <td><?= $db_trip[$i]['vStatus']; ?></td>
                                                            <td>
                                                                <?php
                                                                $ePaymentOption = $db_trip[$i]['ePaymentOption'];
                                                                if ($db_trip[$i]['ePaymentOption'] == 'Card') {
                                                                    $ePaymentOption = $cardText;
                                                                }
                                                                ?>
                                                                <?= $ePaymentOption; ?></td>
                                                            <td align="center">
        <?php if ($db_trip[$i]['eAdminPaymentStatus'] == 'Settled') { ?>
                                                                    Setteled
        <?php } else { ?>
                                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#settlement_form<?= $db_trip[$i]['iOrderId']; ?>">Mark As Setteled</button>
                                                                    <!-- Modal -->
                                                                    <div id="settlement_form<?= $db_trip[$i]['iOrderId']; ?>" class="modal fade settlement_form text-left" role="dialog">
                                                                        <div class="modal-dialog">
                                                                            <!-- Modal content-->
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                                                                    <h4 class="modal-title">Payout Amount</h4>
                                                                                </div>
                                                                                <form role="form" name="settlement_form" id="settlement_form1" method="post" action="" class="margin0">
                                                                                    <div class="modal-body">
                                                                                        <div class="form-group col-lg-12" style="display: inline-block;">
                                                                                            <label class="col-lg-5 control-label">Enter Amount You Paid To <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <span class="red">*</span></label>
                                                                                            <div class="col-lg-7"> 
                                                                                                <?php if ($payment_to_driver == 0): ?>
                                                                                                    <input type="text" name="fDeliveryCharge" id="fDeliveryCharge" value="<?php echo $payment_to_driver; ?>" disabled><br>
                                                                                                    <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> not Assign 
            <?php else: ?>
                                                                                                    <input type="text" name="fDeliveryCharge" id="fDeliveryCharge" value="<?php echo $payment_to_driver; ?>" required>
                <?= $db_trip[$i]['driverName']; ?>
            <?php endif; ?>
                                                                                            </div>
                                                                                            <div class="fDeliveryChargeError error red"></div>
                                                                                        </div>
                                                                                        <div class="form-group col-lg-12" style="display: inline-block;">
                                                                                            <label class="col-lg-5 control-label">Enter Amount You Paid To Store<span class="red">*</span></label>

            <?php $payment_to_restaurant = $generalobjAdmin->getPaymentToRestaurant($db_trip[$i]['iOrderId']);
            //$eConfirm = checkOrderStatus($db_trip[$i]['iOrderId'], "2");
            //$payment_to_restaurant is 0 means store not confirmed order..getPaymentToRestaurant in this fun put checkOrderStatus so here not need..it commented bc in case in future if need open comment
            ?>

                                                                                            <div class="col-lg-7">
                                                                                                <? if($payment_to_restaurant == 0) { ?>
                                                                                                <input type="text" name="fRestaurantPayAmount" id="fRestaurantPayAmount" value="<?php echo $payment_to_restaurant; ?>" disabled><br>
                                                                                                <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." has not confirmed order"; ?>
                                                                                                <? } else { ?>
                                                                                                <input type="text" name="fRestaurantPayAmount" id="fRestaurantPayAmount" value="<?php echo $payment_to_restaurant; ?>" required>
                                                                                                <? } ?>
                                                                                            </div>
                                                                                            <div class="fRestaurantPayAmountError error red"></div>
                                                                                        </div>

                                                                                        <input type="hidden" name="settlementorderid" id="settlementorderid" value="<?= $db_trip[$i]['iOrderId']; ?>">
                                                                                        <input type="hidden" name="action" id="action" value="settelled">

                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="submit" class="btn btn-info" id="cnl_booking" title="Payout Amount">PayOut Amount</button>
                                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button>
                                                                                    </div>
                                                                                </form> 
                                                                            </div>
                                                                            <!-- Modal content-->  
                                                                        </div>
                                                                    </div>
                                                                    <!-- Modal -->
                                                                    <script>
                                                                        $('#settlement_form<?= $db_trip[$i]['iOrderId']; ?>').on('show.bs.modal', function () {
                                                                            $("#fDeliveryCharge").val("<?php echo $payment_to_driver; ?>");
                                                                            $("#fRestaurantPayAmount").val("<?php echo $payment_to_restaurant; ?>");
                                                                            $(".fDeliveryChargeError").html("");
                                                                            $(".fRestaurantPayAmountError").html("");
                                                                        });
                                                                    </script>
                                                        <?php } ?>
                                                            </td>
                                                        </tr>
        <?
    }
} else {
    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="9" style="text-align:center;">No Payment Details Found.</td>
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

        <form name="pageForm" id="pageForm" action="action/cancelled_report.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="action" value="<?php echo $action; ?>" >
            <input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>" >
            <input type="hidden" name="searchDriver" value="<?php echo $searchDriver; ?>" >
            <input type="hidden" name="searchRider" value="<?php echo $searchRider; ?>" >
            <input type="hidden" name="serachOrderNo" value="<?php echo $serachOrderNo; ?>" >
            <input type="hidden" name="searchPaymentType" value="<?php echo $searchPaymentType; ?>" >
            <input type="hidden" name="searchRestaurantPayment" value="<?php echo $searchRestaurantPayment; ?>" >
            <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
            <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
            <input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>" >
            <input type="hidden" name="searchServiceType" value="<?php echo $searchServiceType; ?>" >
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

                                                            $(function () {

                                                                $("#cnl_booking").on('click', function (e) {
                                                                    var fDeliveryCharge = $('#fDeliveryCharge').val()
                                                                    var fRestaurantPayAmount = $('#fRestaurantPayAmount').val();
                                                                    $(".fDeliveryChargeError").html("");
                                                                    $(".fRestaurantPayAmountError").html("");

                                                                    if (fDeliveryCharge == '' || fRestaurantPayAmount == '') {
                                                                        if (fDeliveryCharge == '') {
                                                                            $(".fDeliveryChargeError").html("This Field is required.");
                                                                        }
                                                                        if (fRestaurantPayAmount == '') {
                                                                            $(".fRestaurantPayAmountError").html("This Field is required.");
                                                                        }
                                                                        return false;
                                                                    } else {
                                                                        $(".fDeliveryChargeError").html("");
                                                                        $(".fRestaurantPayAmountError").html("");
                                                                        $("#settlement_form1")[0].submit();
                                                                    }

                                                                });
                                                            });
                                                            function exportlist(){
                                                                $("#actionpay").val("export");
                                                                $("#pageForm").attr("action","export_cancelled_report.php");
                                                                document.pageForm.submit();
                                                            }
        </script>
    </body>
    <!-- END BODY-->
</html>