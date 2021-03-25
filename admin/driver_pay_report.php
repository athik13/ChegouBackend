<?php
include_once('../common.php');
$tbl_name = 'trips';
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('manage-provider-payment-report')) {
    $userObj->redirect();
}
$script = 'Driver Payment Report';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
//data for select fields
$sql = "SELECT iCompanyId,vCompany,vEmail FROM company WHERE eStatus != 'Deleted' AND eSystem = 'General' order by vCompany";
$db_company = $obj->MySQLSelect($sql);
$sql = "SELECT iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail FROM register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY rd.iDriverId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY rd.iDriverId ASC";
    else
        $ord = " ORDER BY rd.iDriverId DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY rd.vBankAccountHolderName ASC";
    else
        $ord = " ORDER BY rd.vBankAccountHolderName DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY rd.vBankName ASC";
    else
        $ord = " ORDER BY rd.vBankName DESC";
}
//End Sorting
// Start Search Parameters
//$ssql='';
$ssql = " AND tr.iActive = 'Finished' ";
$ssql1 = $whereDriverId = '';
if ($action == 'search') {
    if ($startDate != '') {
        //$ssql.=" AND Date(tr.tEndDate) >='".$startDate."'";
        $ssql .= " AND Date(tr.tTripRequestDate) >='" . $startDate . "'";
        $whereDriverId .= " AND Date(tTripRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        //$ssql.=" AND Date(tr.tEndDate) <='".$endDate."'";
        $ssql .= " AND Date(tr.tTripRequestDate) <='" . $endDate . "'";
        $whereDriverId .= " AND Date(tTripRequestDate) <='" . $endDate . "'";
    }
    if ($searchCompany != '') {
        $ssql1 .= " AND rd.iCompanyId ='" . $searchCompany . "'";
    }
    if ($searchDriver != '') {
        $ssql .= " AND tr.iDriverId ='" . $searchDriver . "'";
        $whereDriverId .= " AND iDriverId ='" . $searchDriver . "'";
    }
}
$locations_where = "";
if (count($userObj->locations) > 0) {
    $locations = implode(', ', $userObj->locations);
    $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE tr.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
}
//Select dates
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

$per_page = $DISPLAY_RECORD_NUMBER;
$sql = "SELECT COUNT( DISTINCT rd.iDriverId ) AS Total FROM register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='Unsettelled' AND tr.eSystem = 'General' $ssql $ssql1";
$totalData = $obj->MySQLSelect($sql);
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

$sql = "SELECT rd.iDriverId,tr.eDriverPaymentStatus,concat(rd.vName,' ',rd.vLastName) as dname,rd.vCountry,rd.vBankAccountHolderName,rd.vAccountNumber,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code FROM register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='Unsettelled' AND tr.eSystem = 'General' AND tr.iActive = 'Finished'  $ssql $ssql1 GROUP BY rd.iDriverId $ord LIMIT $start, $per_page";
//echo $sql;die;
$db_payment = $obj->MySQLSelect($sql);

$endRecord = count($db_payment);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$org_sql = "SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization order by vCompany";
$db_organization = $obj->MySQLSelect($org_sql);
$orgNameArr = array();
for ($g = 0; $g < count($db_organization); $g++) {
    $orgNameArr[$db_organization[$g]['iOrganizationId']] = $db_organization[$g]['driverName'];
}
$getDriverTripData = $obj->MySQLSelect("SELECT iOrganizationId,iTripId,iDriverId,SUM(fTipPrice) AS fTipPrice,SUM(fTripGenerateFare) AS fTripGenerateFare,SUM(fCommision) AS fCommision,SUM(fTax1) AS fTax1,SUM(fTax2) AS fTax2,SUM(fOutStandingAmount) AS fOutStandingAmount,SUM(fHotelCommision) AS fHotelCommision,SUM(fDiscount) AS fDiscount,SUM(fWalletDebit) AS fWalletDebit,SUM(iFare) AS iFare,SUM(iBaseFare) AS iBaseFare,SUM(fPricePerKM) AS fPricePerKM,SUM(fPricePerMin) AS fPricePerMin,vTripPaymentMode FROM trips WHERE if(iActive ='Canceled',if(vTripPaymentMode='Card',1=1,0),1=1) AND iActive='Finished' AND iDriverId >0 AND eSystem = 'General' AND eDriverPaymentStatus='Unsettelled' $whereDriverId GROUP BY iDriverId,iTripId,vTripPaymentMode ORDER BY iTripId ASC");
//echo "<pre>";print_r($getDriverTripData);die;
$driverArr = array();
for ($r = 0; $r < count($getDriverTripData); $r++) {
    $iTripId = $getDriverTripData[$r]['iTripId'];
    $totalfare = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['fTripGenerateFare']);
    $site_commission = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['fCommision']);
    $promocodediscount = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['fDiscount']);
    $wallentPayment = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['fWalletDebit']);
    $fOutStandingAmount = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['fOutStandingAmount']);
    $fHotelCommision = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['fHotelCommision']);
    $fTipPrice = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['fTipPrice']);
    $totTax = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['fTax1'] + $getDriverTripData[$r]['fTax2']);
    $iFare = $generalobj->setTwoDecimalPoint($getDriverTripData[$r]['iFare']);
    $orgName = "";
    if (isset($orgNameArr[$getDriverTripData[$r]['iOrganizationId']]) && $orgNameArr[$getDriverTripData[$r]['iOrganizationId']] != "") {
        $orgName = "(" . $orgNameArr[$getDriverTripData[$r]['iOrganizationId']] . ")";
        $getDriverTripData[$r]['iFare'] = $iFare = 0;
    }
    //echo $iTripId . "===>" . "(" . $totalfare . "+" . $fTipPrice . ")-(" . $site_commission . "+" . $totTax . "+" . $fOutStandingAmount . "+" . $fHotelCommision . "+" . $iFare . ")<br>";
    $driver_payment = $generalobj->setTwoDecimalPoint(($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision));
    if ($getDriverTripData[$r]['vTripPaymentMode'] == "Cash") {
        $driver_payment = $generalobj->setTwoDecimalPoint($driver_payment - $iFare);
    }
    //echo $iTripId . "===>" .$driver_payment."<br>";
    $driverArr[$getDriverTripData[$r]['iDriverId']][$getDriverTripData[$r]['iTripId']] = $getDriverTripData[$r];
}
//echo "<pre>";print_r($driverArr);die;
$enableCashReceivedCol = $enableTipCol = array();
for ($i = 0; $i < count($db_payment); $i++) {
    $cashPayment = $cardPayment = $transferAmount = $walletPayment = $promocodePayment = $tripoutstandingAmount = $bookingfees = $totTaxAmt = $totalCashReceived = $tot_fare = $providerAmtCard = $providerAmtCash = $providerAmtOrg = 0;
    $iDriverId = $db_payment[$i]['iDriverId'];
    if (isset($driverArr[$iDriverId])) {
        $driverData = $driverArr[$iDriverId];
        //echo "<pre>";print_r($driverData);die;
        // Added By HJ On 10-05-2019 For Provide Payment Data Start
        foreach ($driverData as $key => $val) {
            $providerAmtCard = $providerAmtCash = $providerAmtOrg = 0;
            //echo "<pre>";print_r($val['vTripPaymentMode']);die;
            $iTripId = $val['iTripId'];
            $iFare = $generalobj->setTwoDecimalPoint($val['iFare']);
            $fTipPrice = $generalobj->setTwoDecimalPoint($val['fTipPrice']);
            if ($fTipPrice > 0) {
                $enableTipCol[] = 1;
            }
            $totalfare = $generalobj->setTwoDecimalPoint($val['fTripGenerateFare']);
            $site_commission = $generalobj->setTwoDecimalPoint($val['fCommision']);
            $hotel_commision = $generalobj->setTwoDecimalPoint($val['fHotelCommision']);
            $fOutStandingAmount = $generalobj->setTwoDecimalPoint($val['fOutStandingAmount']);
            $fWalletDebit = $generalobj->setTwoDecimalPoint($val['fWalletDebit']);
            $totTax = $generalobj->setTwoDecimalPoint($val['fTax1'] + $val['fTax2']);
            $fDiscount = $generalobj->setTwoDecimalPoint($val['fDiscount']);
            $tipPayment += $fTipPrice;
            if (strtoupper($val['vTripPaymentMode']) == "CASH") {
                $cashPayment += $site_commission;
                $walletPayment += $fWalletDebit;
                $promocodePayment += $fDiscount;
                $tripoutstandingAmount += $fOutStandingAmount;
                $bookingfees += $hotel_commision;
                $totalCashReceived += $iFare;
                $totTaxAmt += $totTax;
                $enableCashReceivedCol[] = 1;
                //echo "(" . $totalfare . "+" . $fTipPrice . ")-(" . $site_commission . "+" . $totTax . "+" . $fOutStandingAmount . "+" . $hotel_commision ."+".$iFare. ")<br>";
                $providerAmtCash = ($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $hotel_commision + $iFare);
            } else if (strtoupper($val['vTripPaymentMode']) == "CARD") {
                //echo "(" . $totalfare . "+" . $fTipPrice . ")-(" . $site_commission . "+" . $totTax . "+" . $fOutStandingAmount . "+" . $hotel_commision .")<br>";
                $providerAmtCard = ($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $hotel_commision);
                $cardPayment += $providerAmtCard;
            } else if (strtoupper($val['vTripPaymentMode']) == "ORGANIZATION") {
                $providerAmtOrg = ($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $hotel_commision);
                $organizationPayment += $providerAmtOrg;
            }
            $tot_fare += $totalfare;
            $transferAmount += $providerAmtCash + $providerAmtCard + $providerAmtOrg;
            //echo $iTripId."===>".($providerAmtCash + $providerAmtCard+$providerAmtOrg)."<br>";
        }
        // Added By HJ On 10-05-2019 For Provide Payment Data End
    }
    $db_payment[$i]['transferAmount'] = $generalobj->setTwoDecimalPoint($transferAmount); // Added By HJ On 10-05-2019
    //$db_payment[$i]['cashPayment'] = $generalobjAdmin->getAllCashCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['cashPayment'] = $generalobj->setTwoDecimalPoint($cashPayment); // Added By HJ On 10-05-2019
    //$db_payment[$i]['cardPayment'] = $generalobjAdmin->getAllCardCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['cardPayment'] = $generalobj->setTwoDecimalPoint($cardPayment); // Added By HJ On 10-05-2019
    //$db_payment[$i]['walletPayment'] = $generalobjAdmin->getAllWalletCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['walletPayment'] = $generalobj->setTwoDecimalPoint($walletPayment); // Added By HJ On 10-05-2019
    //$db_payment[$i]['promocodePayment'] = $generalobjAdmin->getAllPromocodeCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['promocodePayment'] = $generalobj->setTwoDecimalPoint($promocodePayment); // Added By HJ On 10-05-2019
    //$db_payment[$i]['tripoutstandingAmount'] = $generalobjAdmin->getAllOutstandingAmountCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['tripoutstandingAmount'] = $generalobj->setTwoDecimalPoint($tripoutstandingAmount); // Added By HJ On 10-05-2019
    //$db_payment[$i]['bookingfees'] = $generalobjAdmin->getAllBookingAmountCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['bookingfees'] = $generalobj->setTwoDecimalPoint($bookingfees); // Added By HJ On 10-05-2019
    $db_payment[$i]['tipPayment'] = $generalobj->setTwoDecimalPoint($tipPayment); // Added By HJ On 10-05-2019
    $db_payment[$i]['totalTaxAmt'] = $generalobj->setTwoDecimalPoint($totTaxAmt); // Added By HJ On 10-05-2019
    $db_payment[$i]['totalCashReceived'] = $generalobj->setTwoDecimalPoint($totalCashReceived); // Added By HJ On 10-05-2019
    $db_payment[$i]['totalFare'] = $generalobj->setTwoDecimalPoint($tot_fare); // Added By HJ On 10-05-2019
    /* if ($ENABLE_TIP_MODULE == "Yes") {
      $db_payment[$i]['tipPayment'] = $generalobjAdmin->getAllTipCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
      $db_payment[$i]['transferAmount'] = $generalobjAdmin->getTransforAmountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
      } else {
      $db_payment[$i]['transferAmount'] = $generalobjAdmin->getTransforAmountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
      } */
}
//echo "<pre>";print_r($db_payment);die;
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 Start
$cardText = "Card";
if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
    $cardText = "Wallet";
}
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 End
$hotelPanel = isHotelPanelEnable(); 
$kioskPanel = isKioskPanelEnable();
?>
<!DOCTYPE html>
<html lang="en">

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Report</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="keywords" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <? include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Report</h2>
                        </div>
                    </div>
                    <hr />
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
                        <div class="Posted-date mytrip-page">
                            <input type="hidden" name="action" value="search" />
                            <div>
                                <div style="float: left;"><h3>Search by Date...</h3></div>
                                <?php if ($userObj->hasPermission('manage-cancellation-payment-report')) { ?>
                                    <div style="text-align: right;font-size:15px;"><a href="cancellation_payment_report.php" target="_blank" class="btn btn-primary">View Cancelled <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?> Payment Report</a></div>
                                <?php } ?>
                            </div>
                            <span>
                                <a onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
                                <a onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
                                <a onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
                                <a onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
                                <a onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
                                <a onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
                                <a onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
                                <a onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
                            </span> 
                            <span>
                                <!-- changed by me -->
                                <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff" />
                                <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff" />

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
                                <div class="tripBtns001">
                                    <b>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'driver_pay_report.php'"/>
                                        <?php if (count($db_payment) > 0) { ?>
                                            <button type="button" onClick="exportlist()" class="export-btn001" >Export</button></b>
                                    <?php } ?>
                                </div>
                            </span>
                            <div class="tripBtns001">
                            </div>
                        </div>
                    </form>
                    <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" id="actionpay" name="action" value="pay_driver">
                        <input type="hidden" name="ePayDriver" id="ePayDriver" value="">
                        <input type="hidden" name="prev_start" id="prev_start" value="<?= $startDate ?>">
                        <input type="hidden" name="prev_end" id="prev_end" value="<?= $endDate ?>">
                        <input type="hidden" name="prev_order" id="prev_order" value="<?= $order ?>">
                        <input type="hidden" name="prev_sortby" id="prev_sortby" value="<?= $sortby ?>">
                        <input type="hidden" name="prevsearchDriver" id="prevsearchDriver" value="<?= $searchDriver ?>">
                        <input type="hidden" name="prevsearchCompany" id="prevsearchCompany" value="<?= $searchCompany ?>">
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example123" >
                            <thead>
                                <tr>
                                    <th><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name</th>
                                    <th><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Bank Details</th>
                                    <th>Total Fare</th>
                                    <?php
                                    if (in_array(1, $enableCashReceivedCol)) {
                                        //<div style="text-align: CENTER;font-size: 17px;">B</div>
                                        ?>
                                        <th>Total Cash Received</th>
                                    <?php } if (in_array(1, $enableTipCol)) { ?>

                                        <th>Total Tip Amount Pay to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></th>
                                    <?php } ?>
                                    <?php if (in_array(1, $enableCashReceivedCol)) { ?>
                                        <th>Total <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] ?> Commission Take From <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For Cash <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?></th>
                                    <?php } ?>
                                    <th>Total <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] ?> Amount Pay to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For <?= $cardText; ?> <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?></th>


                                    <th>Total Tax Amount Take From <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For Cash <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?></th>

<!--<th><div style="text-align: CENTER;font-size: 17px;">F</div> <br/> Total Wallet Adjustment Amount Pay to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For Cash <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?></th>
<th><div style="text-align: CENTER;font-size: 17px;">G</div> <br/> Total Promocode Discount Amount Pay to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For Cash <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?></th>-->
                                    <th>Total <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] ?> Outstanding Amount Take From <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For Cash <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?></th>
                                    <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                        <th>Total <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] ?> Booking Fee Take From <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> For Cash  <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?></th>
                                    <? } ?>
            <th>Final Amount Pay to <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> <br ><!--I = B-A<?php if ($APP_TYPE != 'UberX') { ?>+C<?php } ?>+D+E-F--></th>

                                    <th>Final Amount to take back from <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> <br ><!--J = B-A<?php if ($APP_TYPE != 'UberX') { ?>+C<?php } ?>+D+E-F--></th>										
                                    <th><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Status</th> 
                                    <th></th>                            
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                if (count($db_payment) > 0) {
                                    for ($i = 0; $i < count($db_payment); $i++) {
                                        ?>
                                        <tr class="gradeA">
                                            <td><?= $generalobjAdmin->clearCmpName($db_payment[$i]['dname']); ?></td>
                                            <td><button type="button" onClick="show_driver_details('<?= $db_payment[$i]['iDriverId']; ?>', '<?= $generalobjAdmin->clearCmpName($db_payment[$i]['dname']); ?>', '<?= $generalobjAdmin->clearCmpName($db_payment[$i]['vBankAccountHolderName']); ?>', '<?= $db_payment[$i]['vBankName']; ?>', '<?= $generalobjAdmin->clearCmpName($db_payment[$i]['vAccountNumber']); ?>', '<?= $generalobjAdmin->clearCmpName($db_payment[$i]['vBIC_SWIFT_Code']); ?>')" class="btn btn-success btn-xs">View Details</button></td>
                                            <!--<td style="text-align:right;">
                                                <a href='javascript:void(0);' style='text-decoration: underline;' onClick='showTotalFareDetails(this);'><?= $generalobj->trip_currency($db_payment[$i]['totalFare']); ?></a></td>-->
                                            <td style="text-align:right;"><?= $db_payment[$i]['totalFare']; ?></td>
                                            <?php if (in_array(1, $enableCashReceivedCol)) { ?>
                                                <td style="text-align:right;"><?= $db_payment[$i]['totalCashReceived']; ?></td>
                                            <?php } if (in_array(1, $enableTipCol)) { ?>
                                                <td style="text-align:right;"><?= $db_payment[$i]['tipPayment']; ?></td>
                                            <?php } ?>
                                            <?php if (in_array(1, $enableCashReceivedCol)) { ?>
                                                <td style="text-align:right;"><?= $db_payment[$i]['cashPayment']; ?></td>
                                            <?php } ?>
                                            <td style="text-align:right;"><?= $db_payment[$i]['cardPayment']; ?></td>

                                            <td style="text-align:right;"><?= $db_payment[$i]['totalTaxAmt']; ?></td>

                                                                                                                        <!--<td style="text-align:right;"><?= $db_payment[$i]['walletPayment']; ?></td>
                                                                                                                        <td style="text-align:right;"><?= $db_payment[$i]['promocodePayment']; ?></td>-->
                                            <td style="text-align:right;"><?= $db_payment[$i]['tripoutstandingAmount']; ?></td>
                                            <? if ($hotelPanel > 0 || $kioskPanel > 0) { ?>
                                                <td style="text-align:right;"><?= $db_payment[$i]['bookingfees']; ?></td>
                                            <? } ?>
                                            <td style="text-align:right;">
                                                <?php
                                                if ($db_payment[$i]['transferAmount'] > 0) {
                                                    echo $db_payment[$i]['transferAmount'];
                                                } else {
                                                    echo "---";
                                                }
                                                ?>
                                            </td>
                                            <td style="text-align:right;">
                                                <?php
                                                if ($db_payment[$i]['transferAmount'] >= 0) {
                                                    echo "---";
                                                } else {
                                                    echo abs($db_payment[$i]['transferAmount']);
                                                }
                                                ?>
                                            </td>

                                            <td ><?
                                                if ($db_payment[$i]['eDriverPaymentStatus'] == "Unsettelled") {
                                                    echo "Unsettled";
                                                } else {
                                                    echo $db_trip[$i]['eDriverPaymentStatus'];
                                                }
                                                ?> 
                                                <?php if ($userObj->hasPermission('manage-payment-report')) { ?>
                                                    <br />
                                                    <a href="payment_report.php?action=search&startDate=<?= $startDate; ?>&endDate=<?= $endDate; ?>&searchDriver=<?= $db_payment[$i]['iDriverId']; ?>&searchDriverPayment=Unsettelled" target="_blank">[View Detail]</a>
                                                <?php } ?></td>
                                            <td>
                                                <? if ($db_payment[$i]['eDriverPaymentStatus'] == 'Unsettelled') { ?>
                                                    <input class="validate[required]" type="checkbox" value="<?= $db_payment[$i]['iDriverId'] ?>" id="iTripId_<?= $db_payment[$i]['iDriverId'] ?>" name="iDriverId[]">
                                                <? } ?>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr class="gradeA">
                                        <td colspan="15" align="right"><div class="row">
                                                <span style="margin:26px 13px 0 0;">
                                                    <a onClick="javascript:Paytodriver(); return false;" href="javascript:void(0);"><button class="btn btn-primary">Mark As Settled</button></a>
                                                </span>
                                            </div></td>
                                    </tr>

                                <? } else { ?>
                                    <tr class="gradeA">
                                        <td colspan="15" style="text-align:center;"> No <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Payment Details Found.</td>
                                    </tr>
                                <? } ?>
                            </tbody>
                        </table>
                    </form>
                    <?php include('pagination_n.php'); ?>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->

    <form name="pageForm" id="pageForm" action="action/driver_pay_report.php" method="post" >
        <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
        <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
        <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
        <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
        <input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
        <input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
        <input type="hidden" name="method" id="method" value="" >
    </form>
    <div class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
        <div class="modal-dialog" >
            <div class="modal-content">
                <div class="modal-header">
                    <h4>
                        <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                        <span id="provideName"></span> Bank Details
                        <button type="button" class="close" data-dismiss="modal">x</button>
                    </h4>
                </div>
                <div class="modal-body" style="max-height: 450px;overflow: auto;">
                    <table border="1" class="table table-bordered" width="100%" align="center" cellspacing="5" cellpadding="10px">
                        <tbody>
                            <tr>
                                <td class="text_design"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Account Name</td>
                                <td id="pacName"></td>
                            </tr>
                            <tr>
                                <td class="text_design">Bank Name</td>
                                <td id="pbankName"></td>
                            </tr>
                            <tr>
                                <td class="text_design">Account Number</td>
                                <td id="pacNumber"></td>
                            </tr>
                            <tr>
                                <td class="text_design">Sort Code</td>
                                <td id="psortcode"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <? include_once('footer.php'); ?>
    <link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
    <link rel="stylesheet" href="css/select2/select2.min.css" />
    <script src="js/plugins/select2.min.js"></script>
    <!-- <script src="../assets/js/jquery-ui.min.js"></script> -->
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
                                                    function showTotalFareDetails(invelem) {



                                                    }
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
                                                    function todayDate()
                                                    {
                                                        $("#dp4").val('<?= $Today; ?>');
                                                        $("#dp5").val('<?= $Today; ?>');
                                                    }
                                                    function reset() {
                                                        location.reload();
                                                    }
                                                    function show_driver_details(driverid, provideName, acName, bankName, acNumber, sortCode) {
                                                        $("#provideName").text("");
                                                        $("#pacName,#pbankName,#pacNumber,#psortcode").html("");
                                                        if (acName == "" && sortCode == "" && bankName == "" && acNumber == "") {
                                                            alert("<?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> bank details are not available");
                                                            return false;
                                                        } else {
                                                            $("#detail_modal").modal('show');
                                                            $("#provideName").text(provideName + "'s");
                                                            $("#pacName").html(acName);
                                                            $("#pbankName").html(bankName);
                                                            $("#pacNumber").html(acNumber);
                                                            $("#psortcode").html(sortCode);
                                                        }
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

                                                    function exportlist() {
                                                        $("#actionpay").val("export");
                                                        var act = $("#_list_form").attr("action");
                                                        $("#_list_form").attr("action", "export_driver_pay_details.php");
                                                        document._list_form.submit();
                                                        $("#_list_form").attr("action", act);
                                                        return true;
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
