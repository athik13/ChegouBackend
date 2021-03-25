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
$total_results = count($totalData);
//$total_results = $totalData[0]['Total'];
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
$sql = "SELECT o.iOrderId,o.vOrderNo,sc.vServiceName_" . $default_lang . " as vServiceName,o.tOrderRequestDate,o.fTotalGenerateFare,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fDriverPaidAmount,o.eAdminPaymentStatus,o.ePaymentOption,CONCAT(d.vName,' ',d.vLastName) AS driverName,o.iStatusCode,os.vStatus,t.fDeliveryCharge as driverearning,o.fCancellationCharge,oa.fCancellationFare,oa.ePaidByPassenger,oa.vOrderAdjusmentId FROM orders AS o LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN trips as t ON t.iOrderId=o.iOrderId LEFT JOIN trip_outstanding_amount as oa on oa.iOrderId=o.iOrderId LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND o.iStatusCode IN ('7','8') $ssql $trp_ssql $ord";
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

$header = $data = "";
if(count($allservice_cat_data) > 1) {
  $header .= "Service type". "\t";
}
$header .= $langage_lbl_admin['LBL_RIDE_NO_ADMIN_DL']."#" . "\t";
$header .= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']." Date" . "\t";
$header .= "PayOut To ".$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . "\t";
$header .= "Payout to ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "\t";
$header .= "Cancellation Charges For ".$langage_lbl_admin['LBL_RIDER'] . "\t";
$header .= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']." Status". "\t";
$header .= "Payment method". "\t";
$header .= "Action". "\t";

if(count($db_trip) > 0){
    for($i=0;$i<count($db_trip);$i++) {
      
      $payment_to_driver = $generalobjAdmin->getPaymentToDriver($db_trip[$i]['iOrderId']);
      
      if (count($allservice_cat_data) > 1) {
          $data .= $db_trip[$i]['vServiceName']."\t";
      }
        
        $data .= $db_trip[$i]['vOrderNo']."\t";
        
        $data .= $generalobjAdmin->DateTime($db_trip[$i]['tOrderRequestDate'])."\t";
        
        $data .= "Actual Amount : ".$generalobj->trip_currency($db_trip[$i]['fRestaurantPayAmount'])." ,You Paid : ".$generalobj->trip_currency($db_trip[$i]['fRestaurantPaidAmount']);
        $data .= "\t";
        
        if ($payment_to_driver == 0) {
            $data .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." not Assign";
        } else {
            $data .= "Actual Amount : ".$generalobj->trip_currency($db_trip[$i]['driverearning'])." , You Paid : ".$generalobj->trip_currency($db_trip[$i]['fDriverPaidAmount']);
        }
        $data .= "\t";
        
        $data .= $generalobj->trip_currency($db_trip[$i]['fCancellationCharge']);
        if ($db_trip[$i]['ePaymentOption'] == 'Cash' && $db_trip[$i]['ePaidByPassenger'] == 'Yes') {
            $data .= " ( Paid In Order No# : ".$db_trip[$i]['vOrderAdjusmentId']." )";
        } else if ($db_trip[$i]['ePaymentOption'] == 'Cash') {
            $data .= " ( Outstanding )";
        } else if ($db_trip[$i]['ePaymentOption'] == 'Card') { 
            $data .= " ( Paid )";
        }
        $data .= "\t";
                
        $data .= $db_trip[$i]['vStatus'];
        $data .= "\t";
        
        $ePaymentOption = $db_trip[$i]['ePaymentOption'];
        if ($db_trip[$i]['ePaymentOption'] == 'Card') {
            $ePaymentOption = $cardText;
        }
        
        $data .= $ePaymentOption;
        $data .= "\t";
        
        if ($db_trip[$i]['eAdminPaymentStatus'] == 'Settled') {
            $data .= "Setteled";
        } else {
          $data .= "Unsetteled";
        }

        $data .= "\n";
    }
}


$data = str_replace("\r", "", $data);

ob_clean();

header("Content-type: application/octet-stream");

header("Content-Disposition: attachment; filename=payment_reports.xls");

header("Pragma: no-cache");

header("Expires: 0");

print "$header\n$data";

exit;
//added by SP on 28-06-2019 end
?>

