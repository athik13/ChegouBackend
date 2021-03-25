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
	
$sql = "SELECT c.iCompanyId,o.eRestaurantPaymentStatus,sc.vServiceName_".$default_lang." as vServiceName,c.vCompany,c.vPaymentEmail,c.vAcctHolderName,c.vAcctNo,c.vBankName,c.vBankLocation,c.vSwiftCode,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone FROM company as c LEFT JOIN orders as o on o.iCompanyId= c.iCompanyId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE o.eRestaurantPaymentStatus='Unsettled' $ssql $ssql1 GROUP BY c.iCompanyId $ord LIMIT $start, $per_page ";
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
?>
<!DOCTYPE html>
<html lang="en">

<!-- BEGIN HEAD-->
<head>
	<meta charset="UTF-8" />
    <title><?=$SITE_NAME?> | Restaurant Payment Report</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<meta content="" name="keywords" />
	<meta content="" name="description" />
	<meta content="" name="author" />
    <? include_once('global_files.php');?>
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
						<h2><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']?> Payment Report</h2>
					</div>
				</div>
				<hr />
				<?php include('valid_msg.php'); ?>
						<form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post" >
						<div class="Posted-date mytrip-page">
								<input type="hidden" name="action" value="search" />
								<h3>Search by Date...</h3>
								<span>
								<a onClick="return todayDate('dp4','dp5');"><?=$langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>
								<a onClick="return yesterdayDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>
								<a onClick="return currentweekDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>
								<a onClick="return previousweekDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>
								<a onClick="return currentmonthDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>
								<a onClick="return previousmonthDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>
								<a onClick="return currentyearDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>
								<a onClick="return previousyearDate('dFDate','dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>
								</span> 
								<span>
                                                                <!-- changed by me -->
                                                                <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff" />
                                                                <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff"/>
								<!--<input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value=""/>
								<input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" />-->

 								<div class="col-lg-3 select001">
                                    <select class="form-control filter-by-text" name = 'searchCompany' data-text="Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']?>" id="searchCompany">
                                        <option value="">Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']?></option>
                                            <?php foreach ($db_company as $dbc) { ?>
                                            <option value="<?php echo $dbc['iCompanyId']; ?>" <?php if ($searchCompany == $dbc['iCompanyId']) {
                                                echo "selected";
                                            } ?>><?php echo $generalobjAdmin->clearCmpName($dbc['vCompany']); ?> - ( <?php echo $generalobjAdmin->clearEmail($dbc['vEmail']); ?> )</option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <? if(count($allservice_cat_data) > 1){ ?>
                                <div class="col-lg-3 select001" style="padding-right:15px;">
		                            <select class="form-control filter-by-text" name = "searchServiceType" data-text="Select Serivce Type">
		                                <option value="">Select <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];?></option>
		                               <?php foreach($allservice_cat_data as $value){ ?>
		                               <option value="<?php echo $value['iServiceId']; ?>" <?php if($searchServiceType == $value['iServiceId']) { echo "selected"; } ?>><?php echo $generalobjAdmin->clearName($value['vServiceName']); ?></option>
		                               <?php } ?>
		                            </select>
		                        </div>
		                        <? } ?>
                                <div class="tripBtns001">
                                <b>
									<input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
									<input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'restaurants_pay_report.php'"/> 
									 <?php if(count($db_payment) > 0 && SITE_TYPE != 'Demo'){ ?>
									<button type="button" onClick="exportlist()" class="export-btn001" >Export</button>
									<?php } ?> </b>
                                </div>
							</span>
							<div class="tripBtns001">
							</div>
							</div>
						</form>

                      <form name="_list_form" id="_list_form" class="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
							<input type="hidden" id="actionpay" name="action" value="pay_restaurant">
							<input type="hidden" name="ePayRestaurant" id="ePayRestaurant" value="">
							<input type="hidden" name="prev_start" id="prev_start" value="<?=$startDate?>">
							<input type="hidden" name="prev_end" id="prev_end" value="<?=$endDate?>">
							<input type="hidden" name="prev_order" id="prev_order" value="<?=$order?>">
							<input type="hidden" name="prev_sortby" id="prev_sortby" value="<?=$sortby?>">
							<input type="hidden" name="prevsearchCompany" id="prevsearchCompany" value="<?=$searchCompany?>">
							<table class="table table-striped table-bordered table-hover" id="dataTables-example123" >
								<thead>
									<tr>
										<? if(count($allservice_cat_data) > 1){ ?>
										<th>Service type</th>
										<? } ?>
										<th><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']?> Name <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
										<th><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']?> <br/> Account Name <?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
										<th><a href="javascript:void(0);" onClick="Redirect(4,<?php if($sortby == '4'){ echo $order; }else { ?>0<?php } ?>)">Bank Name <?php if ($sortby == 4) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
										<th>Account Number</th>
										<th>Sort Code</th>
										<!-- <th>Expected Amount Pay <br/> to Restaurant</th> -->
										<th>Final Amount Pay <br/> to <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']?></th>
										<th><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']?> <br/>Payment Status</th> 
										<th></th>                            
									</tr>
								</thead>
								<tbody>
									<?if(count($db_payment) > 0){
		                          	for($i=0;$i<count($db_payment);$i++) { ?>
									<tr class="gradeA">
										<? if(count($allservice_cat_data) > 1){ ?>
										<td><? echo $db_payment[$i]['vServiceName']; ?></td>
										<? } ?>
									  	<td>
									  		<?php if($db_payment[$i]['resturant_phone'] != '') {
												echo $generalobjAdmin->clearName($db_payment[$i]['vCompany']);
												echo '<br>';
												echo'<b>Phone: </b> +' . $generalobjAdmin->clearPhone($db_payment[$i]['resturant_phone']);
											 } else { 
											 	echo $generalobjAdmin->clearName($db_payment[$i]['vCompany']);
											 }?>
										</td>
									  <td><?=($db_payment[$i]['vAcctHolderName'] != "")?$generalobjAdmin->clearName($db_payment[$i]['vAcctHolderName']):'---';?></td>
									  <td><?=($db_payment[$i]['vBankName'] != "")?$generalobjAdmin->clearName($db_payment[$i]['vBankName']):'---';?></td>
									  <td><?=($db_payment[$i]['vAcctNo'] != "")?$generalobjAdmin->clearName($db_payment[$i]['vAcctNo']):'---';?></td>
									  <td><?=($db_payment[$i]['vSwiftCode'] != "")?$generalobjAdmin->clearName($db_payment[$i]['vSwiftCode']):'---';?></td>
									<!--   <td style="text-align:right;">
									  <?php 
										if($db_payment[$i]['expectedAmount'] > 0) {
											echo $generalobj->trip_currency($db_payment[$i]['expectedAmount']);	
										} else {
											echo "---";
										}
									  ?>
									  </td> -->
									  <td style="text-align:right;">
									  <?php 
										if($db_payment[$i]['transferAmount'] > 0) {
											echo $generalobj->trip_currency($db_payment[$i]['transferAmount']);	
										} else {
											echo "---";
										}
									  ?>
									  </td>
									  <td ><?=$db_payment[$i]['eRestaurantPaymentStatus'];?> <br /><a href="store_payment_report.php?action=search&startDate=<?=$startDate;?>&endDate=<?=$endDate;?>&searchCompany=<?=$db_payment[$i]['iCompanyId'];?>&searchRestaurantPayment=Unsettled" target="_blank">[View Detail]</a></td>
									  <td>
										  <? if($db_payment[$i]['eRestaurantPaymentStatus'] == 'Unsettled'){ ?>
											  <input class="validate[required]" type="checkbox" value="<?=$db_payment[$i]['iCompanyId']?>" id="iTripId_<?=$db_payment[$i]['iCompanyId']?>" name="iCompanyId[]">
										  <? } ?>
										</td>
									</tr>
							<? } ?>
                            <tr class="gradeA">
                              <td colspan="14" align="right"><div class="row">
									<span style="margin:26px 13px 0 0;">
										<a onClick="javascript:PaytoRestaurant(); return false;" href="javascript:void(0);"><button class="btn btn-primary ">Mark As Settled</button></a>
									</span>
							</div></td>
                            </tr>
                          
                          <? } else { ?>
                          <tr class="gradeA">
                               <td colspan="13" style="text-align:center;"> No Payment Details Found.</td>
                          </tr>
                          <?}?>
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
	
<form name="pageForm" id="pageForm" action="action/restaurants_pay_report.php" method="post" >
<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
<input type="hidden" name="action" value="<?php echo $action; ?>" >
<input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
<input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
<input type="hidden" name="searchServiceType" value="<?php echo $searchServiceType; ?>" >
<input type="hidden" name="searchCompany" value="<?php echo $searchCompany; ?>" >
<input type="hidden" name="startDate" value="<?php echo $startDate; ?>" >
<input type="hidden" name="endDate" value="<?php echo $endDate; ?>" >
<input type="hidden" name="method" id="method" value="" >
</form>
<? include_once('footer.php');?>
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
			$("#dp5").click(function(){
                 $('#dp5').datepicker('show');
                 $('#dp4').datepicker('hide');
            });

            $("#dp4").click(function(){
                 $('#dp4').datepicker('show');
                 $('#dp5').datepicker('hide');
            });

			if('<?=$startDate?>'!=''){
					$("#dp4").val('<?=$startDate?>');
					$("#dp4").datepicker('update' , '<?=$startDate?>');
			}
			if('<?=$endDate?>'!=''){
					$("#dp5").datepicker('update' , '<?= $endDate;?>');
					$("#dp5").val('<?= $endDate;?>');
			}
		});
	 
	function setRideStatus(actionStatus) {
	 window.location.href = "trip.php?type="+actionStatus;
	}
	function todayDate()
	{
	 $("#dp4").val('<?= $Today;?>');
	 $("#dp5").val('<?= $Today;?>');
	}
	function reset() {
		location.reload();
		
	}	
	function yesterdayDate()
	{
	 $("#dp4").val('<?= $Yesterday;?>');
	 $("#dp4").datepicker('update' , '<?= $Yesterday;?>');
	 $("#dp5").datepicker('update' , '<?= $Yesterday;?>');
	 $("#dp4").change();
	 $("#dp5").change();
	 $("#dp5").val('<?= $Yesterday;?>');
	}
	function currentweekDate(dt,df)
	{
	 $("#dp4").val('<?= $monday;?>');
	 $("#dp4").datepicker('update' , '<?= $monday;?>');
	 $("#dp5").datepicker('update' , '<?= $sunday;?>');
	 $("#dp5").val('<?= $sunday;?>');
	}
	function previousweekDate(dt,df)
	{
	 $("#dp4").val('<?= $Pmonday;?>');
	 $("#dp4").datepicker('update' , '<?= $Pmonday;?>');
	 $("#dp5").datepicker('update' , '<?= $Psunday;?>');
	 $("#dp5").val('<?= $Psunday;?>');
	}
	function currentmonthDate(dt,df)
	{
	 $("#dp4").val('<?= $currmonthFDate;?>');
	 $("#dp4").datepicker('update' , '<?= $currmonthFDate;?>');
	 $("#dp5").datepicker('update' , '<?= $currmonthTDate;?>');
	 $("#dp5").val('<?= $currmonthTDate;?>');
	}
	function previousmonthDate(dt,df)
	{
	 $("#dp4").val('<?= $prevmonthFDate;?>');
	 $("#dp4").datepicker('update' , '<?= $prevmonthFDate;?>');
	 $("#dp5").datepicker('update' , '<?= $prevmonthTDate;?>');
	 $("#dp5").val('<?= $prevmonthTDate;?>');
	}
	function currentyearDate(dt,df)
	{
	 $("#dp4").val('<?= $curryearFDate;?>');
	 $("#dp4").datepicker('update' , '<?= $curryearFDate;?>');
	 $("#dp5").datepicker('update' , '<?= $curryearTDate;?>');
	 $("#dp5").val('<?= $curryearTDate;?>');
	}
	function previousyearDate(dt,df)
	{
	 $("#dp4").val('<?= $prevyearFDate;?>');
	 $("#dp4").datepicker('update' , '<?= $prevyearFDate;?>');
	 $("#dp5").datepicker('update' , '<?= $prevyearTDate;?>');
	 $("#dp5").val('<?= $prevyearTDate;?>');
	}
    
    function exportlist(){
		$("#actionpay").val("export");
		$("#pageForm").attr("action","export_restaurants_pay_report.php");
		document.pageForm.submit();
    }
	
	$("#Search").on('click', function(){
		 if($("#dp5").val() < $("#dp4").val()){
			 alert("From date should be lesser than To date.")
			 return false;
		 }else {
			var action = $("#_list_form").attr('action');
			var formValus = $("#frmsearch").serialize();
			window.location.href = action+"?"+formValus;
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
</script>
</body>
<!-- END BODY-->
</html>
