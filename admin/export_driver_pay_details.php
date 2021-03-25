<?php

include_once('../common.php');

$tbl_name = 'trips';

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();

$abc = 'admin,company';

$action = $_REQUEST['action'];
//added by SP on 28-06-2019 start
$searchCompany = isset($_REQUEST['prevsearchCompany']) ? $_REQUEST['prevsearchCompany'] : '';
$searchDriver = isset($_REQUEST['prevsearchDriver']) ? $_REQUEST['prevsearchDriver'] : '';
$startDate = isset($_REQUEST['prev_start']) ? $_REQUEST['prev_start'] : '';
$endDate = isset($_REQUEST['prev_end']) ? $_REQUEST['prev_end'] : '';
//data for select fields
$sql = "SELECT iCompanyId,vCompany,vEmail FROM company WHERE eStatus != 'Deleted' AND eSystem = 'General' order by vCompany";
$db_company = $obj->MySQLSelect($sql);
$sql = "SELECT iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail FROM register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);
//Start Sorting
$sortby = isset($_REQUEST['prev_sortby']) ? $_REQUEST['prev_sortby'] : 0;
$order = isset($_REQUEST['prev_order']) ? $_REQUEST['prev_order'] : '';
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

$db_payment = $obj->MySQLSelect($sql);
$endRecord = count($db_payment);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
//$getDriverTripData = $obj->MySQLSelect("SELECT iDriverId,SUM(fTipPrice) AS fTipPrice,SUM(fTripGenerateFare) AS fTripGenerateFare,SUM(fCommision) AS fCommision,SUM(fTax1) AS fTax1,SUM(fTax2) AS fTax2,SUM(fOutStandingAmount) AS fOutStandingAmount,SUM(fHotelCommision) AS fHotelCommision,SUM(fDiscount) AS fDiscount,SUM(fWalletDebit) AS fWalletDebit,SUM(iFare) AS iFare,SUM(iBaseFare) AS iBaseFare,SUM(fPricePerKM) AS fPricePerKM,SUM(fPricePerMin) AS fPricePerMin,vTripPaymentMode FROM trips WHERE iActive='Finished' AND iDriverId >0 AND eSystem = 'General' AND eDriverPaymentStatus='Unsettelled' GROUP BY iDriverId,vTripPaymentMode");
$getDriverTripData = $obj->MySQLSelect("SELECT iTripId,iDriverId,SUM(fTipPrice) AS fTipPrice,SUM(fTripGenerateFare) AS fTripGenerateFare,SUM(fCommision) AS fCommision,SUM(fTax1) AS fTax1,SUM(fTax2) AS fTax2,SUM(fOutStandingAmount) AS fOutStandingAmount,SUM(fHotelCommision) AS fHotelCommision,SUM(fDiscount) AS fDiscount,SUM(fWalletDebit) AS fWalletDebit,SUM(iFare) AS iFare,SUM(iBaseFare) AS iBaseFare,SUM(fPricePerKM) AS fPricePerKM,SUM(fPricePerMin) AS fPricePerMin,vTripPaymentMode FROM trips WHERE if(iActive ='Canceled',if(vTripPaymentMode='Card',1=1,0),1=1) AND iActive='Finished' AND iDriverId >0 AND eSystem = 'General' AND eDriverPaymentStatus='Unsettelled' $whereDriverId GROUP BY iDriverId,vTripPaymentMode");
//echo "<pre>";print_r($getDriverTripData);die;
$driverArr = array();
for ($r = 0; $r < count($getDriverTripData); $r++) {
    $driverArr[$getDriverTripData[$r]['iDriverId']][$getDriverTripData[$r]['iTripId']] = $getDriverTripData[$r];
}
//echo "<pre>";print_r($driverArr);die;
$enableCashReceivedCol = $enableTipCol = array();
for ($i = 0; $i < count($db_payment); $i++) {
    $cashPayment = $cardPayment = $transferAmount = $walletPayment = $promocodePayment = $tripoutstandingAmount = $bookingfees = $totTaxAmt = $totalCashReceived = $tot_fare = $providerAmtCard = $providerAmtCash =$providerAmtOrg= 0;
    $iDriverId = $db_payment[$i]['iDriverId'];
    if (isset($driverArr[$iDriverId])) {
        $driverData = $driverArr[$iDriverId];
        //echo "<pre>";print_r($driverData);die;
        // Added By HJ On 10-05-2019 For Provide Payment Data Start
        foreach ($driverData as $key => $val) {
            $providerAmtCard = $providerAmtCash =$providerAmtOrg= 0;
            //echo "<pre>";print_r($val);die;
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
            }else if (strtoupper($val['vTripPaymentMode']) == "ORGANIZATION") {
                $providerAmtOrg = ($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $hotel_commision);
                $organizationPayment += $providerAmtOrg;
            }
            $tot_fare += $totalfare;
            $transferAmount += $providerAmtCash + $providerAmtCard+$providerAmtOrg;
            //echo $transferAmount."<br>";
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
}
//echo "<pre>";print_r($db_payment);die;
$header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Name" . "\t";

$header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Bank Details" . "\t";

$header .= "Bank Name" . "\t";

$header .= "Account Number" . "\t";

$header .= "Sort Code" . "\t";
$header .= "Total Fare" . "\t";
if (in_array(1, $enableCashReceivedCol)) {
    $header .= "Total Cash Received" . "\t";
}
if (in_array(1, $enableTipCol)) {
    $header .= "Total Tip Amount Pay to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "\t";
}
if (in_array(1, $enableCashReceivedCol)) {
    $header .= "Total " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Commission Take From " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . "\t";
}

$header .= "Total " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Amount Pay to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Card " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . "\t";

$header .= "Total Tax Amount Take From " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . "\t";

$header .= "Total  " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Outstanding Amount Take From " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . "\t";

$hotelPanel = isHotelPanelEnable(); 
$kioskPanel = isKioskPanelEnable(); 

if ($hotelPanel > 0 || $kioskPanel > 0) {
    $header .= "Total " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Booking Fee Take From " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash  " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] . "\t";
}

$header .= "Final Amount Pay to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "\t";
$header .= "Final Amount to take back from " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "\t";
$header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Payment Status" . "\t";

for ($j = 0; $j < count($db_payment); $j++) {
    $data .= $generalobjAdmin->clearCmpName($db_payment[$j]['dname']) . "\t";

    $data .= ($db_payment[$j]['vBankAccountHolderName'] != "") ? $generalobjAdmin->clearCmpName($db_payment[$j]['vBankAccountHolderName']) : '---';

    $data .= "\t";

    $data .= ($db_payment[$j]['vBankName'] != "") ? $db_payment[$j]['vBankName'] : '---';

    $data .= "\t";

    $data .= ($db_payment[$j]['vAccountNumber'] != "") ? $generalobjAdmin->clearCmpName($db_payment[$j]['vAccountNumber']) : '---';

    $data .= "\t";

    $data .= ($db_payment[$j]['vBIC_SWIFT_Code'] != "") ? $generalobjAdmin->clearCmpName($db_payment[$j]['vBIC_SWIFT_Code']) : '---';

    $data .= "\t";

    $data .= $db_payment[$j]['totalFare'] . "\t";

    if (in_array(1, $enableCashReceivedCol)) {
        $data .= $db_payment[$j]['totalCashReceived'] . "\t";
    } if (in_array(1, $enableTipCol)) {
        $data .= $db_payment[$j]['tipPayment'] . "\t";
    } if (in_array(1, $enableCashReceivedCol)) {
        $data .= $db_payment[$j]['cashPayment'] . "\t";
    }
    $data .= $db_payment[$j]['cardPayment'] . "\t";
    $data .= $db_payment[$j]['totalTaxAmt'] . "\t";
    $data .= $db_payment[$j]['tripoutstandingAmount'] . "\t";

    if ($hotelPanel > 0 || $kioskPanel > 0) {
        $data .= $db_payment[$j]['bookingfees'] . "\t";
    }
    if ($db_payment[$j]['transferAmount'] > 0) {
        $data .= $db_payment[$j]['transferAmount'] . "\t";
    } else {
        $data .= "---" . "\t";
    }

    if ($db_payment[$j]['transferAmount'] >= 0) {
        $data .= "---" . "\t";
    } else {
        $data .= abs($db_payment[$j]['transferAmount']) . "\t";
    }

    if ($db_payment[$j]['eDriverPaymentStatus'] == "Unsettelled") {
        $data .= "Unsettled" . "\t";
    } else {
        $data .= $db_trip[$j]['eDriverPaymentStatus'] . "\t";
    }
    $data .= "\n";
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

