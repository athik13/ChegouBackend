<?

include_once('common.php');
include_once('generalFunctions.php');
include_once('include/config.php');
$script="Order";
//$tbl_name 	= 'register_driver';
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
    $dateRange = isset($_REQUEST['dateRange']) ? $_REQUEST['dateRange'] : '';   	
	if($startDate!=''){
		$ssql.=" AND Date(ord.tOrderRequestDate) >='".$startDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(ord.tOrderRequestDate) <='".$endDate."'";
	}
}
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
	  $vLang = $default_lang;
}
$sql = "SELECT ord.iOrderId,ord.vOrderNo,ord.vTimeZone,ord.tOrderRequestDate,ord.fDriverPaidAmount,ord.iStatusCode,cmp.vCompany,ordst.vStatus_".$vLang." as vStatus,t.fDeliveryCharge From orders as ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId LEFT JOIN order_status as ordst ON ordst.iStatusCode = ord.iStatusCode LEFT JOIN trips as t ON t.iOrderId=ord.iOrderId WHERE ord.iDriverId = '".$_SESSION['sess_iUserId']."' AND IF(ord.eTakeaway = 'Yes' && ordst.iStatusCode = 6, ordst.eTakeaway='Yes', ordst.eTakeaway != 'Yes') AND ord.iStatusCode NOT IN ('11','12') ".$ssql." ORDER BY ord.tOrderRequestDate DESC ";

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
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_HEADER_TRIPS_TXT']; ?></title>
        <?php include_once("top/top_script.php"); ?>
    </head>
<body id="wrapper">
    <!-- home page -->
    <!-- home page -->
    <?php if($template!='taxishark'){?>
    <div id="main-uber-page">
    <?php } ?>
        <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- First Section -->
        <?php include_once("top/header.php");?>
        <!-- End: First Section -->
<section class="profile-section my-trips">
    <div class="profile-section-inner">
        <div class="profile-caption">
            <div class="page-heading">
                <h1><?= $langage_lbl['LBL_DRIVER_ORDERS_TXT']; ?></h1>
            </div>
            
            <div class="button-block oppData">
            <form class="tabledata-filter-block filter-form" name="search"  method="post" onSubmit="return checkvalid()">
                <input type="hidden" name="action" value="search" />
                <div class="filters-column mobile-full">
                    <label><?= $langage_lbl['LBL_ORDER_SEARCH_BY_DATE']; ?></label>
                    <select id="timeSelect" name="dateRange">
                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                    <option value="today" <?php if($dateRange == 'today'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></option>
                                    <option value="yesterday" <?php if($dateRange == 'yesterday'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></option>
                                    <option value="currentWeek" <?php if($dateRange == 'currentWeek'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></option>
                                    <option value="previousWeek" <?php if($dateRange == 'previousWeek'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></option>
                                    <option value="currentMonth" <?php if($dateRange == 'currentMonth'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></option>
                                    <option value="previousMonth" <?php if($dateRange == 'previousMonth'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></option>
                                    <option value="currentYear" <?php if($dateRange == 'currentYear'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></option>
                                    <option value="previousYear" <?php if($dateRange == 'previousYear'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></option>

                    </select>
                </div>
                <div class="filters-column mobile-half">
                    <label><?= $langage_lbl['LBL_MYTRIP_FROM_DATE'] ?></label>
                    <input type="text" id="dp4" name="startDate" placeholder="<?= $langage_lbl['LBL_WALLET_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                    <i class="icon-cal" id="from-date"></i>
                </div>
                <div class="filters-column mobile-half">
                    <label><?= $langage_lbl['LBL_MYTRIP_TO_DATE'] ?></label>
                    <input type="text" id="dp5" name="endDate" placeholder="<?= $langage_lbl['LBL_WALLET_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                    <i class="icon-cal" id="to-date"></i>
                </div>
                <div class="filters-column mobile-full">
                    <button class="driver-trip-btn"><?= $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>
                    <!-- <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button> -->
                    <a href="driver-order" class="gen-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></a>

                </div>
            </form>
			<ul class="value-listing">
					<li><b><?=$langage_lbl['LBL_TOTAL_ORDERS_DRIVER']; ?> :</b> <span><?= count($db_order_detail);?></span></li>
					<li><b><?=$langage_lbl['LBL_TOTAL_ORDERS_EARNING_DRIVER']; ?> :</b> <span><?= $currencySymbol;?> <?=$generalobj->trip_currency_payment($TotalDrvEarning);?></span></li>
			</ul>
			</div>

        </div>
    </div>
</section>
<section class="profile-earning">
    <div class="profile-earning-inner">

    <div class="table-holder">


        <table id="my-trips-data" class="ui celled table custom-table" style="width:100%">
            <thead>
                <tr>
					<th style="text-align: center"><?=$langage_lbl['LBL_ORDER_NO_TXT'];?></th>				
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
					?>
					<tr class="gradeA">
						<td align="center"><?=$db_order_detail[$i]['vOrderNo'];?></td>
						<td data-order="<?php echo $tOrderRequestDate; ?>"><?= $generalobj->DateTime1($tOrderRequestDate,'yes');?></td>
						<td align="center"><?= $generalobj->clearName($db_order_detail[$i]['vCompany']);?></td>
						<td align="center"><?=$currencySymbol." ".$generalobj->trip_currency_payment($expectedearning);?></td>
						<td align="center">
							<? if($driverEarning > 0) { 
								echo $currencySymbol." ".$generalobj->trip_currency_payment($driverEarning);
								 } else { 
								echo $langage_lbl['LBL_PENDING_WEB'];
							 } ?>
						</td>
						<td align="center"><?=$db_order_detail[$i]['vStatus'];?></td>
						<td align="center" width="10%">
						  <a target = "_blank" href="cx-invoice_deliverall.php?iOrderId=<?=base64_encode(base64_encode($db_order_detail[$i]['iOrderId']))?>">
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
					<td class="last_record_row"><?= $currencySymbol;?> <?=$generalobj->trip_currency_payment($TotalDrvExpectedEarning);?></td>
					<td class="last_record_row"><?= $currencySymbol;?> <?=$generalobj->trip_currency_payment($TotalDrvEarning);?></td>
					<td></td>
					<td></td>
				</tr>
			</tfoot>			
        </table>
    </div>
    </div>
</section>


    <!-- add money-->

    <!-- home page end-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>

    <div style="clear:both;"></div>
     <?php if($template!='taxishark'){?>
     </div>
     <?php } ?>
    <!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>


<script type="text/javascript">
      if($('#my-trips-data').length > 0) {
        $('#my-trips-data').DataTable({"oLanguage": langData});
    }
    



    $(document).on('change','#timeSelect',function(e){
        e.preventDefault();
        
        var timeSelect = $(this).val();
        
        if(timeSelect == 'today'){ todayDate('dp4', 'dp5') }
        if(timeSelect == 'yesterday'){yesterdayDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentWeek'){currentweekDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousWeek'){previousweekDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentMonth'){currentmonthDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousMonth'){previousmonthDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentYear'){currentyearDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousYear'){previousyearDate('dFDate', 'dTDate')}

    });




</script>
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
