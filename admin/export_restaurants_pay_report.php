<?php
include_once('../common.php');
$tbl_name 	= 'trips';
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();

if(!$userObj->hasPermission('manage-store-payment')){
  $userObj->redirect();
}


$script='Restaurant Payment Report';
$eSystem = " AND eSystem = 'DeliverAll'";

$action = isset($_REQUEST['action']) ? $_REQUEST['action']: '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchServiceType= isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';

//data for select fields
$ssqlsc = " AND iServiceId IN(".$enablesevicescategory.")";
$sql = "select iCompanyId,vCompany,vEmail from company WHERE eStatus != 'Deleted' $eSystem $ssqlsc order by vCompany";
$db_company = $obj->MySQLSelect($sql);

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$ord = ' ORDER BY c.iCompanyId DESC';

if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY c.vCompany ASC";
  else
  $ord = " ORDER BY c.vCompany DESC";
}

if($sortby == 3){
  if($order == 0)
  $ord = " ORDER BY c.vAcctHolderName ASC";
  else
  $ord = " ORDER BY c.vAcctHolderName DESC";
}

if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY c.vBankName ASC";
  else
  $ord = " ORDER BY c.vBankName DESC";
}
//End Sorting

// Start Search Parameters

$ssql='';
$ssql1 = '';
if ($action == 'search') {
	if($startDate!=''){
	  $ssql.=" AND Date(o.tOrderRequestDate) >='".$startDate."'";
	}
	if($endDate!=''){
	  $ssql.=" AND Date(o.tOrderRequestDate) <='".$endDate."'";
	}
	if ($searchCompany != '') {
        $ssql1 .= " AND c.iCompanyId ='" . $searchCompany . "'";
    }
    if($searchServiceType != ''){
        $ssql.=" AND sc.iServiceId ='".$searchServiceType."'";
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

$ssql .= " AND sc.iServiceId IN(".$enablesevicescategory.")";

$per_page = $DISPLAY_RECORD_NUMBER;	
$sql = "SELECT c.iCompanyId,o.eRestaurantPaymentStatus,sc.vServiceName_".$default_lang." as vServiceName,c.vCompany,c.vPaymentEmail,c.vAcctHolderName,c.vAcctNo,c.vBankName,c.vBankLocation,c.vSwiftCode,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone FROM company as c LEFT JOIN orders as o on o.iCompanyId= c.iCompanyId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE o.eRestaurantPaymentStatus='Unsettled' $ssql $ssql1 GROUP BY c.iCompanyId";
$totalData = $obj->MySQLSelect($sql);
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
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
//echo "<PRE>"; print_R($_REQUEST);	
$sql = "SELECT c.iCompanyId,o.eRestaurantPaymentStatus,sc.vServiceName_".$default_lang." as vServiceName,c.vCompany,c.vPaymentEmail,c.vAcctHolderName,c.vAcctNo,c.vBankName,c.vBankLocation,c.vSwiftCode,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone FROM company as c LEFT JOIN orders as o on o.iCompanyId= c.iCompanyId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE o.eRestaurantPaymentStatus='Unsettled' $ssql $ssql1 GROUP BY c.iCompanyId $ord";
//exit;
$db_payment = $obj->MySQLSelect($sql);
$endRecord = count($db_payment);
$var_filter = "";
foreach ($_REQUEST as $key=>$val) {
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;

for($i=0;$i<count($db_payment);$i++) {
	$db_payment[$i]['transferAmount'] = $generalobjAdmin->getTransforAmountbyRestaurant($db_payment[$i]['iCompanyId'],$ssql);
	$db_payment[$i]['expectedAmount'] = $generalobjAdmin->getExpectedforAmountbyRestaurant($db_payment[$i]['iCompanyId'],$ssql);
	
}

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata,true);

$header = $data = "";
if(count($allservice_cat_data) > 1) {
  $header .= "Service type". "\t";
}
$header .= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." Name" . "\t";
$header .= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." Account Name" . "\t";
$header .= "Bank Name" . "\t";
$header .= "Account Number" . "\t";
$header .= "Sort Code" . "\t";
$header .= "Final Amount Pay to ".$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']. "\t";
$header .= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Payment Status";

if(count($db_payment) > 0){
    for($i=0;$i<count($db_payment);$i++) {
      
        if(count($allservice_cat_data) > 1) {
          $data .= $db_payment[$i]['vServiceName']."\t";
        }
        
        if($db_payment[$i]['resturant_phone'] != '') {
          $data .= $generalobjAdmin->clearName($db_payment[$i]['vCompany']).",";
          $data .= 'Phone: +' . $generalobjAdmin->clearPhone($db_payment[$i]['resturant_phone'])."\t";
        } else { 
          $data .= $generalobjAdmin->clearName($db_payment[$i]['vCompany'])."\t";
        }
        
        $data .= ($db_payment[$i]['vAcctHolderName'] != "")?$generalobjAdmin->clearName($db_payment[$i]['vAcctHolderName']):'---';
        $data .= "\t";
        
        $data .= ($db_payment[$i]['vBankName'] != "")?$generalobjAdmin->clearName($db_payment[$i]['vBankName']):'---';
        $data .= "\t";
        
        $data .= ($db_payment[$i]['vAcctNo'] != "")?$generalobjAdmin->clearName($db_payment[$i]['vAcctNo']):'---';
        $data .= "\t";
        
        $data .= ($db_payment[$i]['vSwiftCode'] != "")?$generalobjAdmin->clearName($db_payment[$i]['vSwiftCode']):'---';
        $data .= "\t";
        
        //$data .= ($db_payment[$i]['expectedAmount'] > 0) ? $generalobj->trip_currency($db_payment[$i]['expectedAmount']) : '---';
        //$data .= "\t"; 
        
        $data .= ($db_payment[$i]['transferAmount'] > 0) ? $generalobj->trip_currency($db_payment[$i]['transferAmount']) : '---';
        $data .= "\t";
        
        $data .= $db_payment[$i]['eRestaurantPaymentStatus'];

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

