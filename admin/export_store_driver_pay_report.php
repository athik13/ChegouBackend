<?php
include_once('../common.php');
$tbl_name 	= 'trips';
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();

if(!$userObj->hasPermission('manage-provider-payment')){
  $userObj->redirect();
}

$script='Deliverall Driver Payment Report';

$action = isset($_REQUEST['action']) ? $_REQUEST['action']: '';
//$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';

//data for select fields
/*$sql = "select iCompanyId,vCompany,vEmail from company WHERE eStatus != 'Deleted' order by vCompany";
$db_company = $obj->MySQLSelect($sql);*/


$sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$ord = ' ORDER BY rd.iDriverId DESC';
if($sortby == 1){
  if($order == 0)
  $ord = " ORDER BY rd.iDriverId ASC";
  else
  $ord = " ORDER BY rd.iDriverId DESC";
}

if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY rd.vName ASC";
  else
  $ord = " ORDER BY rd.vName DESC";
}

if($sortby == 3){
  if($order == 0)
  $ord = " ORDER BY rd.vBankAccountHolderName ASC";
  else
  $ord = " ORDER BY rd.vBankAccountHolderName DESC";
}

if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY rd.vBankName ASC";
  else
  $ord = " ORDER BY rd.vBankName DESC";
}
//End Sorting


// Start Search Parameters

$ssql='';
$ssql1 = '';
if ($action == 'search') {
	if($startDate!=''){
		//$ssql.=" AND Date(tr.tEndDate) >='".$startDate."'";
	  $ssql.=" AND Date(tr.tTripRequestDate) >='".$startDate."'";
	}
	if($endDate!=''){
		//$ssql.=" AND Date(tr.tEndDate) <='".$endDate."'";
	  $ssql.=" AND Date(tr.tTripRequestDate) <='".$endDate."'";
	}
	/*if ($searchCompany != '') {
        $ssql1 .= " AND rd.iCompanyId ='" . $searchCompany . "'";
    }*/
    if ($searchDriver != '') {
        $ssql .= " AND tr.iDriverId ='" . $searchDriver . "'";
    }
}
//Select dates
$Today=Date('Y-m-d');
$tdate=date("d")-1;
$mdate=date("d");
$Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));

$curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
$curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
$prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")-1));
$prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")-1));

$currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$tdate,date("Y")));
$currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m")+1,date("d")-$mdate,date("Y")));
$prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d")-$tdate,date("Y")));
$prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$mdate,date("Y")));

$monday = date( 'Y-m-d', strtotime( 'sunday this week -1 week' ) );
$sunday = date( 'Y-m-d', strtotime( 'saturday this week' ) );

$Pmonday = date( 'Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date( 'Y-m-d', strtotime('saturday this week -1 week'));

$ssql .= " AND tr.iServiceId IN(".$enablesevicescategory.")";

$per_page = $DISPLAY_RECORD_NUMBER;	
$sql = "select rd.iDriverId,tr.eDriverPaymentStatus,concat(rd.vName,' ',rd.vLastName) as dname,rd.vCountry,rd.vBankAccountHolderName,rd.vAccountNumber,CONCAT(rd.vCode,' ',rd.vPhone)  as user_phone,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code from register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='Unsettelled' AND tr.eSystem = 'DeliverAll' $ssql $ssql1 GROUP BY rd.iDriverId";
$totalData = $obj->MySQLSelect($sql);
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
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End

$sql = "select rd.iDriverId,tr.eDriverPaymentStatus,concat(rd.vName,' ',rd.vLastName) as dname,rd.vCountry,rd.vBankAccountHolderName,rd.vAccountNumber,CONCAT(rd.vCode,' ',rd.vPhone)  as user_phone,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code from register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='Unsettelled' AND tr.eSystem = 'DeliverAll' $ssql $ssql1 GROUP BY rd.iDriverId $ord";
$db_payment = $obj->MySQLSelect($sql);
$endRecord = count($db_payment);
$var_filter = "";
foreach ($_REQUEST as $key=>$val) {
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;

for($i=0;$i<count($db_payment);$i++) {
	$db_payment[$i]['transferAmount'] = $generalobjAdmin->getTransforAmountbyDeliveryDriverId($db_payment[$i]['iDriverId'],$ssql);
	//$db_payment[$i]['earningAmount'] = $generalobjAdmin->getEarningAmountbyDeliveryDriverId($db_payment[$i]['iDriverId'],$ssql);
}

$header = $data = "";
$header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Name" . "\t";
$header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Account Name" . "\t";
$header .= "Bank Name" . "\t";
$header .= "Account Number" . "\t";
$header .= "Sort Code" . "\t";
$header .= "Final Amount Pay to ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']. "\t";
$header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . "Payment Status";

if(count($db_payment) > 0){
    for($i=0;$i<count($db_payment);$i++) { 
        $user_available_balance = $generalobj->get_user_available_balance($db_payment[$i]['iDriverId'], "Driver");
        if($db_payment[$i]['user_phone'] != '') {
            $data .= $generalobjAdmin->clearName($db_payment[$i]['dname']) .",";
            $data .= 'Phone: +' .$generalobjAdmin->clearPhone( $db_payment[$i]['user_phone']).",";
            $data .= 'Wallet Balance: '.$generalobj->trip_currency($user_available_balance)."\t";
        } else {
            $data .=  $generalobjAdmin->clearName($db_payment[$i]['dname']). ",";
            $data .= 'Wallet Balance: '.$generalobj->trip_currency($user_available_balance)."\t";
        }
        
        $data .= ($db_payment[$i]['vBankAccountHolderName'] != "") ? $generalobjAdmin->clearName(" " . $db_payment[$i]['vBankAccountHolderName']) : '---';
        $data .= "\t";
        
        $data .= ($db_payment[$i]['vBankName'] != "") ? $generalobjAdmin->clearName(" " . $db_payment[$i]['vBankName']) : '---';
        $data .= "\t";
        
        $data .= ($db_payment[$i]['vAccountNumber'] != "") ? $generalobjAdmin->clearName(" " . $db_payment[$i]['vAccountNumber']) : '---';
        $data .= "\t";
        
        $data .= ($db_payment[$i]['vBIC_SWIFT_Code'] != "") ? $generalobjAdmin->clearName(" " . $db_payment[$i]['vBIC_SWIFT_Code']) : '---';
        $data .= "\t";
        
        $data .= ($db_payment[$i]['transferAmount'] > 0) ? $generalobj->trip_currency($db_payment[$i]['transferAmount']) : '---';
        $data .= "\t";
        
        $data .= ($db_payment[$i]['earningAmount'] > 0) ? $generalobj->trip_currency($db_payment[$i]['earningAmount']) : '---';
        $data .= "\t";
        
        $data .= $db_payment[$i]['eDriverPaymentStatus'];

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

