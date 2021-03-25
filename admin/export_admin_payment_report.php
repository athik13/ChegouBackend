<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();

if (!$userObj->hasPermission('manage-admin-earning')) {
    $userObj->redirect();
}


$script = 'Admin Payment_Report';
$eSystem = " AND eSystem = 'DeliverAll'";

function cleanNumber($num) {
    return str_replace(',', '', $num);
}

//data for select fields
$ssqlsc = " AND iServiceId IN(".$enablesevicescategory.")";
$sql = "select iCompanyId,vCompany,vEmail from company WHERE eStatus != 'Deleted' $eSystem $ssqlsc order by vCompany";
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
    if ($searchServiceType != '') {
        $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "'";
    }
    if ($searchRestaurantPayment != '') {
        $ssql .= " AND o.eRestaurantPaymentStatus ='" . $searchRestaurantPayment . "'";
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
$sql1 = "SELECT o.iOrderId,o.vOrderNo,o.iCompanyId,sc.vServiceName_" . $default_lang . " as vServiceName,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.fOutStandingAmount,o.iStatusCode,os.vStatus,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,CONCAT(ru.vPhoneCode,' ',ru.vPhone)  as user_phone,CONCAT(rd.vCode,' ',rd.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone,t.fDeliveryCharge as driverearning FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN trips as t ON t.iOrderId=o.iOrderId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.iStatusCode = '6' $ssql $trp_ssql";

$totalData = $obj->MySQLSelect($sql1);

$tot_order_amount = 0.00;
$tot_site_commission = 0.00;
$tot_delivery_charges = 0.00;
$tot_offer_discount = 0.00;
$tot_admin_payment = 0.00;
$tot_outstanding_amount = 0.00;
foreach ($totalData as $dtps) {
    $totalfare = $dtps['fTotalGenerateFare'];
    $fOffersDiscount = $dtps['fOffersDiscount'];
    $fDeliveryCharge = $dtps['fDeliveryCharge'];
    $site_commission = $dtps['fCommision'];
    $fOutStandingAmount = $dtps['fOutStandingAmount'];

    $restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount);

    $siteearnig = cleanNumber($site_commission) + cleanNumber($fDeliveryCharge) + cleanNumber($fOutStandingAmount);
    $driverearning = $db_trip[$i]['driverearning'];
    $adminearning = $siteearnig - cleanNumber($driverearning);

    $tot_order_amount = $tot_order_amount + cleanNumber($totalfare);
    $tot_offer_discount = $tot_offer_discount + cleanNumber($fOffersDiscount);
    $tot_delivery_charges = $tot_delivery_charges + cleanNumber($fDeliveryCharge);
    $tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
    $tot_outstanding_amount = $tot_outstanding_amount + cleanNumber($fOutStandingAmount);
    $tot_admin_payment = $tot_admin_payment + cleanNumber($adminearning);
}

$total_results = $totalData[0]['Total'];
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
$sql = "SELECT o.iOrderId,o.vOrderNo,o.iCompanyId,sc.vServiceName_" . $default_lang . " as vServiceName,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.fOutStandingAmount,o.iStatusCode,os.vStatus,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,CONCAT(ru.vPhoneCode,' ',ru.vPhone)  as user_phone,CONCAT(rd.vCode,' ',rd.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone,t.fDeliveryCharge as driverearning FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN trips as t ON t.iOrderId=o.iOrderId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.iStatusCode = '6' $ssql $trp_ssql $ord";
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
$header .= "A=Total Order Amount" . "\t";
$header .= "B=Site Commision" . "\t";
$header .= "C=Delivery Charges" . "\t";
$header .= "D=OutStanding Amount". "\t";
$header .= "E=Driver Pay Amount". "\t";
$header .= "F = B+C+D-E,Admin Earning Amount". "\t";
$header .= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']." Status". "\t";
$header .= "Payment method";

if(count($db_trip) > 0){
    for($i=0;$i<count($db_trip);$i++) {
      
      $totalfare = $db_trip[$i]['fTotalGenerateFare'];
      $site_commission = $db_trip[$i]['fCommision'];
      $fOffersDiscount = $db_trip[$i]['fOffersDiscount'];
      $fDeliveryCharge = $db_trip[$i]['fDeliveryCharge'];
      $fOutStandingAmount = $db_trip[$i]['fOutStandingAmount'];

      $restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount);

      $set_unsetarray[] = $db_trip[$i]['eRestaurantPaymentStatus'];

      if (!empty($db_trip[$i]['drivername'])) {
          $drivername = $db_trip[$i]['drivername'];
      } else {
          $drivername = '--';
      }

      $siteearnig = cleanNumber($site_commission) + cleanNumber($fDeliveryCharge) + cleanNumber($fOutStandingAmount);
      $driverearning = $db_trip[$i]['driverearning'];
      $adminearning = $siteearnig - cleanNumber($driverearning);
      
      
        if(count($allservice_cat_data) > 1) {
          $data .= $db_trip[$i]['vServiceName']."\t";
        }
        
        $data .= $db_trip[$i]['vOrderNo']."\t";
        
        $data .= $generalobjAdmin->DateTime($db_trip[$i]['tOrderRequestDate'])."\t";
        
        $data .= ($db_trip[$i]['fTotalGenerateFare'] != "" && $db_trip[$i]['fTotalGenerateFare'] != 0) ? $generalobj->formateNumAsPerCurrency($db_trip[$i]['fTotalGenerateFare'], ''):'---';
        $data .= "\t";
        
        $data .= ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0) ? $generalobj->formateNumAsPerCurrency($db_trip[$i]['fCommision'], '') : '---';
        $data .= "\t";
        
        $data .= ($db_trip[$i]['fDeliveryCharge'] != "" && $db_trip[$i]['fDeliveryCharge'] != 0) ? $generalobj->formateNumAsPerCurrency($db_trip[$i]['fDeliveryCharge'], '') : '---';
        $data .= "\t";
        
        //$data .= ($db_trip[$i]['fOffersDiscount'] != "" && $db_trip[$i]['fOffersDiscount'] != 0) ? $generalobj->formateNumAsPerCurrency($db_trip[$i]['fOffersDiscount'], '') : '---'."\t";
        //$data .= "\t";
        
        $data .= ($db_trip[$i]['fOutStandingAmount'] != "" && $db_trip[$i]['fOutStandingAmount'] != 0) ? $generalobj->formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'], '') : '---';
        $data .= "\t";
        
        //$data .= ($restaurant_payment != "" && $restaurant_payment != 0) ? $generalobj->formateNumAsPerCurrency($restaurant_payment, '') : '---'."\t";
        //$data .= "\t";
        
        $data .= ($db_trip[$i]['driverearning'] != "" && $db_trip[$i]['driverearning'] != 0) ? $generalobj->formateNumAsPerCurrency($db_trip[$i]['driverearning'], '') : '---';
        $data .= "\t";
        
        $data .= ($adminearning != "" && $adminearning != 0) ? $generalobj->formateNumAsPerCurrency($adminearning, '') : '---';
        $data .= "\t";
        
        $data .= $db_trip[$i]['vStatus'];
        $data .= "\t";
        
        $ePaymentOption = $db_trip[$i]['ePaymentOption'];
        if ($db_trip[$i]['ePaymentOption'] == 'Card') {
            $ePaymentOption = $cardText;
        }
        
        $data .= $ePaymentOption;

        $data .= "\n";
    }
}

$data .= "\t\t\t\t\t\t\t\t\t";
$data .= "Total Fare: "."\t".$generalobj->formateNumAsPerCurrency($tot_order_amount, '')."\n";
$data .= "\t\t\t\t\t\t\t\t\t";
$data .= "Total Site Commision: "."\t".$generalobj->formateNumAsPerCurrency($tot_site_commission, '')."\n";
$data .= "\t\t\t\t\t\t\t\t\t";
$data .= "Total Delivery Charges: "."\t".$generalobj->formateNumAsPerCurrency($tot_delivery_charges, '')."\n";
$data .= "\t\t\t\t\t\t\t\t\t";
$data .= "Total Outstanding Amount: "."\t".$generalobj->formateNumAsPerCurrency($tot_outstanding_amount, '')."\n";
$data .= "\t\t\t\t\t\t\t\t\t";
$data .= "Total Admin Earning  Payment: "."\t".$generalobj->formateNumAsPerCurrency($tot_admin_payment, '')."\n";


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

