<?

include_once('common.php');
include_once('generalFunctions.php');
include_once('include/config.php');
$script="Order";
//$tbl_name 	= 'register_driver';
//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-driver_order.php");
        exit;
}
$generalobj->check_member_login();
$abc = 'driver';
 $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 $generalobj->setRole($abc,$url);
 
$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$ssql='';
if($action!='')
{
	$startDate=$_REQUEST['startDate'];
	$endDate=$_REQUEST['endDate'];
	if($startDate!=''){
		$ssql.=" AND Date(ord.tOrderRequestDate) >='".$startDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(ord.tOrderRequestDate) <='".$endDate."'";
	}
}

$sql = "SELECT ord.iOrderId,ord.vOrderNo,ord.vTimeZone,ord.tOrderRequestDate,ord.fDriverPaidAmount,ord.iStatusCode,cmp.vCompany,ordst.vStatus,t.fDeliveryCharge From orders as ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId LEFT JOIN order_status as ordst ON ordst.iStatusCode = ord.iStatusCode LEFT JOIN trips as t ON t.iOrderId=ord.iOrderId WHERE ord.iDriverId = '".$_SESSION['sess_iUserId']."' AND IF(ord.eTakeaway = 'Yes' && ordst.iStatusCode = 6, ordst.eTakeaway='Yes', ordst.eTakeaway != 'Yes') AND ord.iStatusCode NOT IN ('11','12') ".$ssql." ORDER BY ord.tOrderRequestDate DESC ";

$db_order_detail = $obj->MySQLSelect($sql);

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
if(file_exists($logogpath."driver-view-icon.png")){
    $invoice_icon = $logogpath."driver-view-icon.png";
}else{
    $invoice_icon = "assets/img/driver-view-icon.png";
}


$TotalDrvEarning = 0;
$TotalDrvExpectedEarning = 0; 
for($i=0;$i<count($db_order_detail);$i++){
	$iOrderIdnew = $db_order_detail[$i]['iOrderId'];
	$getUserCurrencyLanguageDetails = $generalobj->getDriverCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'],$iOrderIdnew);
	$currencySymbol = $getUserCurrencyLanguageDetails['currencySymbol'];
	$Ratio = $getUserCurrencyLanguageDetails['Ratio'];


	$driverExpectedEarn = $db_order_detail[$i]['fDeliveryCharge'] * $Ratio;


	if($db_order_detail[$i]['iStatusCode'] == '7' || $db_order_detail[$i]['iStatusCode'] == '8'){
		$driverEarn = $db_order_detail[$i]['fDriverPaidAmount'] * $Ratio;
	} else {
		$driverEarn = $db_order_detail[$i]['fDeliveryCharge'] * $Ratio;
	}

	$TotalDrvExpectedEarning += $driverExpectedEarn;
	$TotalDrvEarning += $driverEarn;
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_ORDERS_TXT']; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
   
    <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
    <!-- End: Default Top Script and css-->
</head>
<body>
  <!-- home page -->
    <div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- contact page-->
		<div class="page-contant">
			<div class="page-contant-inner">
			  	<h2 class="header-page"><?=$langage_lbl['LBL_DRIVER_ORDERS_TXT']; ?></h2>
		  		<!-- trips page -->
			  	<div class="trips-page">
			  		<form name="search" action="" method="post" onSubmit="return checkvalid()">
			  		<input type="hidden" name="action" value="search" />
				    	<div class="Posted-date">
				      		<h3><?=$langage_lbl['LBL_ORDER_SEARCH_BY_DATE']; ?></h3>
				      		<span>
				      			<input type="text" id="dp4" name="startDate" placeholder="<?=$langage_lbl['LBL_WALLET_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
				      			<input type="text" id="dp5" name="endDate" placeholder="<?=$langage_lbl['LBL_WALLET_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
					      	</span>
				      	</div>
				    	<div class="time-period">
				      		<h3><?=$langage_lbl['LBL_ORDER_SEARCH_BY_TIME_PERIOD']; ?></h3>
				      		<span>
								<a onClick="return todayDate('dp4','dp5');"><?=$langage_lbl['LBL_COMPANY_TRIP_Today']; ?></a>
								<a onClick="return yesterdayDate('dFDate','dTDate');"><?=$langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></a>
								<a onClick="return currentweekDate('dFDate','dTDate');"><?=$langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></a>
								<a onClick="return previousweekDate('dFDate','dTDate');"><?=$langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></a>
								<a onClick="return currentmonthDate('dFDate','dTDate');"><?=$langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></a>
								<a onClick="return previousmonthDate('dFDate','dTDate');"><?=$langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></a>
								<a onClick="return currentyearDate('dFDate','dTDate');"><?=$langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></a>
								<a onClick="return previousyearDate('dFDate','dTDate');"><?=$langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></a>
				      		</span> 
				      		<b><button class="driver-trip-btn"><?=$langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>
				      		<button onClick="reset();" class="driver-trip-btn"><?=$langage_lbl['LBL_MYTRIP_RESET']; ?></button></b> 
			      		</div>
		      		</form>
			    	<div class="trips-table">
			    		<div class="row">
						  <div class="col-sm-6 text-left"><h4><span><b><?=$langage_lbl['LBL_TOTAL_ORDERS_DRIVER']; ?>: </b></span><?= count($db_order_detail);?></h4></div>
						  <div class="col-sm-6 text-right"><h4><span><b><?=$langage_lbl['LBL_TOTAL_ORDERS_EARNING_DRIVER']; ?>: </b></span><?= $currencySymbol;?> <?=$generalobj->trip_currency_payment($TotalDrvEarning);?></h4></div>
						</div>
			      		<div class="trips-table-inner">
                        <div class="driver-trip-table">
			        		<table width="100%" border="0" cellpadding="0" cellspacing="1" id="dataTables-example">
			          			<thead>
									<tr>
									
										<th style="text-align: center"><?=$langage_lbl_admin['LBL_ORDER_NO_TXT'];?></th>				
										<th width="17%" style="text-align: center"><?=$langage_lbl['LBL_ORDER_DATE_TXT']; ?></th>
										<th style="text-align: center"><?=$langage_lbl['LBL_RESTAURANT_TXT']; ?></th>
										<th style="text-align: center"><?=$langage_lbl['LBL_ORDER_EXPECTED_EARNING_TXT']; ?></th>
										<th style="text-align: center"><?=$langage_lbl['LBL_EARNING_AMOUNT_DRIVER']; ?></th>
										<th style="text-align: center"><?=$langage_lbl['LBL_ORDER_STATUS_TXT']; ?></th>
										<th style="text-align: center"><?=$langage_lbl['LBL_VIEW_DETAIL_TXT']; ?></th>
										
									</tr>
								</thead>
								<tbody>
								<? 
									for($i=0;$i<count($db_order_detail);$i++)
									{ 
										$iOrderIdnew = $db_order_detail[$i]['iOrderId'];
										$getUserCurrencyLanguageDetails = $generalobj->getDriverCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'],$iOrderIdnew);
										$currencySymbol = $getUserCurrencyLanguageDetails['currencySymbol'];
										$Ratio = $getUserCurrencyLanguageDetails['Ratio'];
										$currencycode = $getUserCurrencyLanguageDetails['currencycode'];

										$expectedearning = $db_order_detail[$i]['fDeliveryCharge'] * $Ratio;

										if($db_order_detail[$i]['iStatusCode'] == '7' || $db_order_detail[$i]['iStatusCode'] == '8'){
											$driverEarning = $db_order_detail[$i]['fDriverPaidAmount'] * $Ratio;
										} else {
											$driverEarning = $db_order_detail[$i]['fDeliveryCharge'] * $Ratio;
										}

										$systemTimeZone = date_default_timezone_get();
										if($db_order_detail[$i]['tOrderRequestDate']!= "" && $db_order_detail[$i]['vTimeZone'] != "")  {
											$tOrderRequestDate = converToTz($db_order_detail[$i]['tOrderRequestDate'],$db_order_detail[$i]['vTimeZone'],$systemTimeZone);
										} else {
											$tOrderRequestDate = $db_order_detail[$i]['tOrderRequestDate'];
										}
										$expectedearning=$generalobj->trip_currency_payment($expectedearning);
										$TotalDrvExpectedEarning=$generalobj->trip_currency_payment($TotalDrvExpectedEarning);
										$TotalDrvEarning=$generalobj->trip_currency_payment($TotalDrvEarning);
									?>
									<tr class="gradeA">
										<td align="center"><?=$db_order_detail[$i]['vOrderNo'];?></td>
										<td data-order="<?php echo $tOrderRequestDate; ?>"><?= $generalobj->DateTime1($tOrderRequestDate,'yes');?></td>
										<td align="center"><?=$db_order_detail[$i]['vCompany'];?></td>
										<td align="center"><?= $generalobj->formateNumAsPerCurrency($expectedearning, $currencycode);?></td>
										<td align="center">
											<? if($driverEarning > 0) {
											echo $generalobj->formateNumAsPerCurrency($driverEarning, $currencycode);
												 } else { 
												echo $langage_lbl['LBL_PENDING_WEB'];
											 } ?>
										</td>
										<td align="center"><?=$db_order_detail[$i]['vStatus'];?></td>
										<td align="center" width="10%">
										  <a target = "_blank" href="invoice_deliverall.php?iOrderId=<?=base64_encode(base64_encode($db_order_detail[$i]['iOrderId']))?>">
												<img alt="" src="<?php echo $invoice_icon;?>">
										 </a>
										</td>		
									</tr>
								<? } ?>		
								</tbody>
								<tfoot>
									<tr class="last_row_record">
										<td></td>
										<td></td>
										<td></td>
										<td class="last_record_row"><?= $generalobj->formateNumAsPerCurrency($TotalDrvExpectedEarning, $currencycode); ?></td>
										<td class="last_record_row"><?=$generalobj->formateNumAsPerCurrency($TotalDrvEarning, $currencycode); ?></td>
										<td></td>
										<td></td>
									</tr>
								</tfoot>
			        		</table>
			      		</div>	</div>
			    </div>
			  </div>
			  <!-- -->
			  <div style="clear:both;"></div>
			</div>
		</div>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>
    <!-- footer part end -->
        <!-- End:contact page-->
        <div style="clear:both;"></div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
    <script src="assets/js/jquery-ui.min.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script type="text/javascript">
         $(document).ready(function () {
         	$( "#dp4" ).datepicker({
         		dateFormat: "yy-mm-dd",
         		changeYear: true,
     		  	changeMonth: true,
     		  	yearRange: "-100:+10"
         	});
         	$( "#dp5" ).datepicker({
         		dateFormat: "yy-mm-dd",
         		changeYear: true,
     		  	changeMonth: true,
     		  	yearRange: "-100:+10"
         	});
			 if('<?=$startDate?>'!=''){
				 $("#dp4").val('<?=$startDate?>');
				 $("#dp4").datepicker('refresh');
			 }
			 if('<?=$endDate?>'!=''){
				 $("#dp5").val('<?= $endDate;?>');
				 $("#dp5").datepicker('refresh');
			 }

			$('#dataTables-example').DataTable( {
    "oLanguage": langData,
			  "order": [[ 1, "desc" ]]
			} );
         });
        function reset() {
			location.reload();
		}
		 function todayDate()
		 {
			 $("#dp4").val('<?= $Today;?>');
			 $("#dp5").val('<?= $Today;?>');
		 }
		 function yesterdayDate()
		 {
			 $("#dp4").val('<?= $Yesterday;?>');
			 $("#dp5").val('<?= $Yesterday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');			 
		 }
		 function currentweekDate(dt,df)
		 {
			 $("#dp4").val('<?= $monday;?>');			 
			 $("#dp5").val('<?= $sunday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousweekDate(dt,df)
		 {
			 $("#dp4").val('<?= $Pmonday;?>');
			 $("#dp5").val('<?= $Psunday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function currentmonthDate(dt,df)
		 {
			 $("#dp4").val('<?= $currmonthFDate;?>');
			 $("#dp5").val('<?= $currmonthTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousmonthDate(dt,df)
		 {
			 $("#dp4").val('<?= $prevmonthFDate;?>');
			 $("#dp5").val('<?= $prevmonthTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function currentyearDate(dt,df)
		 {
			 $("#dp4").val('<?= $curryearFDate;?>');
			 $("#dp5").val('<?= $curryearTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousyearDate(dt,df)
		 {
			 $("#dp4").val('<?= $prevyearFDate;?>');
			 $("#dp5").val('<?= $prevyearTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
	 	function checkvalid(){
			 if($("#dp5").val() < $("#dp4").val()){
				 //bootbox.alert("<h4>From date should be lesser than To date.</h4>");
			 	bootbox.dialog({
				 	message: "<h4><?php echo addslashes($langage_lbl['LBL_FROM_TO_DATE_ERROR_MSG']);?></h4>",
				 	buttons: {
				 		danger: {
				      		label: "OK",
				      		className: "btn-danger"
				   	 	}
			   	 	}
		   	 	});
			 	return false;
		 	}
	 	}
    </script>
    
    <script type="text/javascript">
    $(document).ready(function(){
        $("[name='dataTables-example_length']").each(function(){
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'></em>");
        });
        $("[name='dataTables-example_length']").change(function(){
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        }).trigger('change');
    })
</script>
    <!-- End: Footer Script -->
</body>
</html>
