<?php            
include_once('../common.php');
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

function cleanNumber($num) {
    return str_replace(',', '', $num);
}
//added by SP for hotel report export start
//data for select fields
$sql = "SELECT iCompanyId,vCompany,vEmail FROM company WHERE eStatus != 'Deleted' order by vCompany";
$db_company = $obj->MySQLSelect($sql);

$sql = "SELECT iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail FROM register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);

$sql = "SELECT iUserId,CONCAT(vName,' ',vLastName) AS riderName,vEmail FROM register_user WHERE eStatus != 'Deleted' order by vName";
$db_rider = $obj->MySQLSelect($sql);

$sql1 = "SELECT iAdminId,CONCAT(vFirstName,' ',vLastName) AS hotel,vEmail FROM administrators WHERE eStatus != 'Deleted' AND iGroupId = 4 order by vFirstName";
$db_hotels = $obj->MySQLSelect($sql1);

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
$searchCompany = isset($_REQUEST['prevsearchCompany']) ? $_REQUEST['prevsearchCompany'] : '';
$searchDriver = isset($_REQUEST['prevsearchDriver']) ? $_REQUEST['prevsearchDriver'] : '';
$searchRider = isset($_REQUEST['prevsearchRider']) ? $_REQUEST['prevsearchRider'] : '';

$searchHotel = isset($_REQUEST['prevsearchHotel']) ? $_REQUEST['prevsearchHotel'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$startDate = isset($_REQUEST['prev_start']) ? $_REQUEST['prev_start'] : '';
$endDate = isset($_REQUEST['prev_end']) ? $_REQUEST['prev_end'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';

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
if ($searchHotel != '') {
    $ssql .= " AND a.iAdminId ='" . $searchHotel . "'";
}
if ($searchDriverPayment != '') {
    $ssql .= " AND tr.eHotelPaymentStatus ='" . $searchDriverPayment . "'";
}
if ($searchPaymentType != '') {
    $ssql .= " AND tr.vTripPaymentMode ='" . $searchPaymentType . "'";
}
if ($eType != '') {
    if ($eType == 'Ride') {
        $ssql .= " AND tr.eType ='" . $eType . "' AND tr.iRentalPackageId = 0 AND tr.eHailTrip = 'No' ";
    } elseif ($eType == 'RentalRide') {
        $ssql .= " AND tr.eType ='Ride' AND tr.iRentalPackageId > 0";
    } elseif ($eType == 'HailRide') {
        $ssql .= " AND tr.eType ='Ride' AND tr.eHailTrip = 'Yes'";
    } else {
        $ssql .= " AND tr.eType ='" . $eType . "' ";
    }
    //$ssql .= " AND tr.eType ='" . $eType . "'";
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

$hotelQuery = "";
if ($_SESSION['SessionUserType'] == 'hotel') {
    /*  $sql1 = "SELECT * FROM hotel where iAdminId = '".$_SESSION['sess_iAdminUserId']."'";
      $hoteldata = $obj->MySQLSelect($sql1); */
    $iHotelBookingId = $_SESSION['sess_iAdminUserId'];
    $hotelQuery = " And tr.iHotelBookingId = '" . $iHotelBookingId . "'";
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT tr.iFare,tr.fTripGenerateFare,tr.fCommision,tr.iBaseFare,a.vFirstName,a.vLastName,a.fHotelServiceCharge,tr.fHotelBookingChargePercentage, tr.fDiscount, tr.fWalletDebit,tr.fHotelCommision, tr.fTipPrice,tr.fOutStandingAmount,tr.vTripPaymentMode,( SELECT COUNT(tr.iTripId) FROM trips AS tr LEFT JOIN hotel as h on h.iHotelId=tr.iHotelId LEFT JOIN administrators as a on a.iAdminId=tr.iHotelBookingId WHERE if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND (tr.eBookingFrom = 'Hotel' || tr.eBookingFrom = 'Kiosk') $ssql $trp_ssql $hotelQuery) AS Total FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId LEFT JOIN hotel as h on h.iHotelId=tr.iHotelId LEFT JOIN administrators as a on a.iAdminId=tr.iHotelBookingId WHERE if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND (tr.eBookingFrom = 'Hotel' || tr.eBookingFrom = 'Kiosk')AND tr.eSystem = 'General' $hotelQuery $ssql $trp_ssql";
$totalData = $obj->MySQLSelect($sql);
$driver_payment = $total_tip = $tot_fare = $tot_site_commission = 0.00;
foreach ($totalData as $dtps) {
    $totalfare = $dtps['fTripGenerateFare'];
    $site_commission = $dtps['fHotelCommision'];
    $driver_payment = cleanNumber($site_commission);
    $tot_fare = $tot_fare + cleanNumber($totalfare);
    $tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
    $tot_driver_refund = $tot_driver_refund + cleanNumber($driver_payment);
    $cashPayment = $site_commission;
    $cardPayment = $totalfare - $site_commission;
}

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
$sql = "SELECT tr.iTripId,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.iBaseFare,tr.tTripRequestDate,concat(a.vFirstName,' ',a.vLastName) as hotelname,a.fHotelServiceCharge,tr.fHotelBookingChargePercentage,tr.fHotelCommision, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eHotelPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId, tr.eBookingFrom,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId LEFT JOIN hotel as h on h.iHotelId=tr.iHotelId LEFT JOIN administrators as a on a.iAdminId=tr.iHotelBookingId  WHERE  if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND (tr.eBookingFrom = 'Hotel' || tr.eBookingFrom = 'Kiosk') AND tr.eSystem = 'General' $hotelQuery $ssql $trp_ssql $ord LIMIT $start, $per_page";
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


$header .= "Booked By" . "\t";
$header .= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." No." . "\t";
if($_SESSION['SessionUserType'] != 'hotel') { 
$header .= "Hotel Name" . "\t";
}
$header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']. " Name" . "\t";
$header .= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']. " Name" . "\t";
$header .= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Date" . "\t";
$header .= "Total Fare" . "\t";
$header .= "Base Fare" . "\t";
$header .= "Hotel Booking Charge (in %)" . "\t";
$header .= "Booking Fees" . "\t";
$header .=  "Hotel pay Amount" . "\t";
$header .= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Status" . "\t";
$header .= "Payment method" . "\t";
$header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']. " Payment Status";

//$driver_payment = 0.00;
//$total_tip = 0.00;
//$tot_fare = 0.00;
//$tot_site_commission = 0.00;
 
for ($j = 0; $j < count($db_trip); $j++) {

    $totalfare = $db_trip[$j]['fTripGenerateFare'];
    $site_commission = $db_trip[$j]['fHotelCommision'];
    $driver_payment =  cleanNumber($site_commission);
    
    //$tot_fare = $tot_fare + cleanNumber($totalfare);
    //$tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
    //$tot_driver_refund = $tot_driver_refund + cleanNumber($driver_payment);  
    //$cashPayment = $site_commission;
    //$cardPayment = $totalfare-$site_commission;
    
    if ($db_trip[$j]['eMBirr'] == "Yes") {
        $paymentmode = "M-birr";
    } else {
        $paymentmode = $db_trip[$j]['vTripPaymentMode'];
    }

    $eType = $db_trip[$j]['eType'];

    $data .= $db_trip[$j]['eBookingFrom']."\t";
    $data .= $db_trip[$j]['vRideNo'] . "\t";
    if($_SESSION['SessionUserType'] != 'hotel') { 
        $data .= $generalobjAdmin->clearName($db_trip[$j]['hotelname']) . "\t";
    }
    $data .= $generalobjAdmin->clearName($db_trip[$j]['drivername']) . "\t";
    $data .= $generalobjAdmin->clearName($db_trip[$j]['riderName']) . "\t";
    $data .= $generalobjAdmin->DateTime($db_trip[$j]['tTripRequestDate']) . "\t";
    $data .= ($db_trip[$j]['fTripGenerateFare'] != "" && $db_trip[$j]['fTripGenerateFare'] != 0) ? $generalobj->trip_currency($db_trip[$j]['fTripGenerateFare']) . "\t" : "- \t";
    $data .= ($db_trip[$j]['iBaseFare'] != "" && $db_trip[$j]['iBaseFare'] != 0) ? $generalobj->trip_currency($db_trip[$j]['iBaseFare']) . "\t" : "- \t";
     $data .= ($db_trip[$j]['fHotelBookingChargePercentage'] != "" && $db_trip[$j]['fHotelBookingChargePercentage'] != 0) ? $db_trip[$j]['fHotelBookingChargePercentage'] . "\t" : "- \t";
    $data .= ($db_trip[$j]['fHotelCommision'] != "" && $db_trip[$j]['fHotelCommision'] != 0) ? $generalobj->trip_currency($db_trip[$j]['fHotelCommision']) . "\t" : "- \t";
    $data .= ($driver_payment != "" && $driver_payment != 0) ? $generalobj->trip_currency($driver_payment) . "\t" : "- \t";
    $data .= $db_trip[$j]['iActive'] . "\t";
    $data .= $paymentmode . "\t";
    $data .= $db_trip[$j]['eHotelPaymentStatus'] . "\n";
}
    $data .= "\n\t\t\t\t\t\t\t\t\tTotal Fare\t" . $generalobj->trip_currency($tot_fare) . "\n";
    $data .= "\t\t\t\t\t\t\t\t\tTotal Platform Fees\t" . $generalobj->trip_currency($tot_site_commission) . "\n";
    $data .= "\t\t\t\t\t\t\t\t\tTotal Hotel Payment\t" . $generalobj->trip_currency($tot_driver_refund) . "\n";
    $data = str_replace("\r", "", $data);
    #echo "<br>".$data; exit;
    ob_clean();
    header("Content-type: application/octet-stream; charset=utf-8");
    header("Content-Disposition: attachment; filename=hotel_payment_reports.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
//added by SP for hotel report export end    
?>