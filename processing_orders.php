<?
include_once('common.php');
include_once('generalFunctions.php');


//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
	include_once("cx-processing_orders.php");
	exit;
}
$abc = 'company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);
$script="ProcessingOrder";
$generalobj->check_member_login();

$abc = 'admin,company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);
$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$ssql='';
if($action!='')
{
	$startDate=$_REQUEST['startDate'];
	$endDate=$_REQUEST['endDate'];
	if($startDate!=''){
		$ssql.=" AND Date(o.tOrderRequestDate) >='".$startDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(o.tOrderRequestDate) <='".$endDate."'";
	}
}
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
	  $vLang = $default_lang;
}
$processOrderArray = array('1','2','4','5');
$iStatusCode = '('.implode(',',$processOrderArray).')';
$sql = "SELECT o.iOrderId,o.vOrderNo,o.tOrderRequestDate, o.vTimeZone, o.fNetTotal,o.fTotalGenerateFare,o.fOutStandingAmount,o.fCommision,o.fDeliveryCharge,o.fOffersDiscount, Concat(u.vName,' ',u.vLastName) as Username,c.vCompany,os.vStatus_".$vLang." as vStatus,(select count(od.iOrderId) from order_details as od where od.iOrderId = o.iOrderId) as TotalItem,o.eTakeaway From orders as o LEFT JOIN company as c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os ON os.iStatusCode = o.iStatusCode LEFT JOIN register_user as u ON u.iUserId = o.iUserId WHERE o.iCompanyId = '".$_SESSION['sess_iUserId']."' AND o.iStatusCode IN $iStatusCode ".$ssql." AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') ORDER BY o.iOrderId DESC "; 

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

// $invoice_icon = "driver-view-icon.png";
// $canceled_icon = "canceled-invoice.png";

if(file_exists($logogpath."driver-view-icon.png")){
    $invoice_icon = $logogpath."driver-view-icon.png";
}else{
    $invoice_icon = "assets/img/driver-view-icon.png";
}

if(file_exists($logogpath."canceled-invoice.png")){
 $canceled_icon = $logogpath."canceled-invoice.png";   
}else{
 $canceled_icon = "assets/img/canceled-invoice.png";   
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_PROCESSING_ORDERS_TXT_WEB']; ?></title>
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
			  	<h2 class="header-page"><?=$langage_lbl['LBL_PROCESSING_ORDERS_TXT_WEB']; ?></h2>
		  		<!-- trips page -->
			  	<div class="trips-page">
			  		<form name="search" action="" method="post" onSubmit="return checkvalid()">
			  		<input type="hidden" name="action" value="search" />
				    	<div class="Posted-date">
				      		<h3><?=$langage_lbl['LBL_PROCESSING_ORDER_SEARCH_BY_DATE']; ?></h3>
				      		<span>
				      			<input type="text" id="dp4" name="startDate" placeholder="<?=$langage_lbl['LBL_WALLET_FROM_DATE']; ?>" class="form-control" value=""/>
				      			<input type="text" id="dp5" name="endDate" placeholder="<?=$langage_lbl['LBL_WALLET_TO_DATE']; ?>" class="form-control" value=""/>
					      	</span>
				      	</div>
				    	<div class="time-period">
				      		<h3><?=$langage_lbl['LBL_PROCESSING_ORDER_SEARCH_BY_TIME_PERIOD']; ?></h3>
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
			      		<div class="trips-table-inner">
                        <div class="driver-trip-table">
			        		<table width="100%" border="0" cellpadding="0" cellspacing="1" id="dataTables-example">
			          			<thead>
									<tr>
										<th><?=$langage_lbl_admin['LBL_ORDER_NO_TXT'];?></th>				
										<th><?=$langage_lbl['LBL_ORDER_DATE_TXT']; ?></th>
	        							<th><?=$langage_lbl['LBL_PASSENGER_NAME_TEXT_DL']; ?></th>
										<th><?=$langage_lbl['LBL_TOTAL_ITEM_TXT']; ?></th>
										<th><?=$langage_lbl['LBL_ORDER_EARNING_TXT']; ?></th>
										<th><?=$langage_lbl['LBL_ORDER_STATUS_TXT']; ?></th>
										<th><?=$langage_lbl['LBL_VIEW_DETAIL_TXT']; ?></th>
									</tr>
								</thead>
								<tbody>
								<? 
									for($i=0;$i<count($db_order_detail);$i++)
									{
									
										$getUserCurrencyLanguageDetails = $generalobj->getCompanyCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'],$db_order_detail[$i]['iOrderId']);
										$Ratio = $getUserCurrencyLanguageDetails['Ratio'];
										  $currencycode = $getUserCurrencyLanguageDetails['currencycode'];
 
										$fTotalGenerateFare = $db_order_detail[$i]['fTotalGenerateFare'] - $db_order_detail[$i]['fCommision'] - $db_order_detail[$i]['fDeliveryCharge']- $db_order_detail[$i]['fOffersDiscount']-$db_order_detail[$i]['fOutStandingAmount'];

										$systemTimeZone = date_default_timezone_get();
										if($db_order_detail[$i]['tOrderRequestDate']!= "" && $db_order_detail[$i]['vTimeZone'] != "")  {
											$tOrderRequestDate = converToTz($db_order_detail[$i]['tOrderRequestDate'],$db_order_detail[$i]['vTimeZone'],$systemTimeZone);
										} else {
											$tOrderRequestDate = $db_order_detail[$i]['tOrderRequestDate'];
										}
									 	$fTotalGenerateFare= ($fTotalGenerateFare * $Ratio);
									?>
									<tr class="gradeA">
										<td align="center">
											<?=$db_order_detail[$i]['vOrderNo'];?>
											<?= $db_order_detail[$i]['eTakeaway'] == 'Yes' ? '<br><span class="grey-color">'.$langage_lbl['LBL_TAKE_AWAY'].'</span>' : ''?>
										</td>
										<td data-order="<?php echo $tOrderRequestDate; ?>"><?= $generalobj->DateTime1($tOrderRequestDate,'yes');?></td>
										<td align="center"><?=$db_order_detail[$i]['Username'];?></td>
										<td align="center"><?=$db_order_detail[$i]['TotalItem'];?></td>
										<td align="center"><?=$generalobj->formateNumAsPerCurrency($fTotalGenerateFare, $currencycode);?></td>
										<td align="center"><?= str_replace("#STORE#", $db_order_detail[$i]['vCompany'], $db_order_detail[$i]['vStatus']); ?></td>
										<td align="center" width="10%">
										  <a target = "_blank" href="order_invoice.php?iOrderId=<?=base64_encode(base64_encode($db_order_detail[$i]['iOrderId']))?>">
												<img alt="" src="<?php echo $invoice_icon;?>">
										 </a>
										</td>		
									</tr>
								<? } ?>		
								</tbody>
			        		</table>
			      		</div>	</div>
			    </div>
			    <!-- -->
			    <? //if(SITE_TYPE=="Demo"){?>
			    <!-- <div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
			      This feature will be enabled in the main product we will provide you.</span> </div>
			      <?php //}?> -->
			    <!-- -->
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
