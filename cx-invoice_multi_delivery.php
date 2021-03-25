<?
include_once('common.php');
include_once('generalFunctions.php');

$tbl_name 	= 'trips';
$script="Trips";
$generalobj->check_member_login();

// $APP_DELIVERY_MODE = $generalobj->getConfigurations("configurations","APP_DELIVERY_MODE");
$APP_DELIVERY_MODE = "Multi";
	
if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "") {
	$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	$_SESSION['HTTP_REFERER'] = $HTTP_REFERER;
	
} else {
	//$_SESSION['HTTP_REFERER'] = "";
}

$_REQUEST['iTripId'] = base64_decode(base64_decode(trim($_REQUEST['iTripId'])));

$iTripId = isset($_REQUEST['iTripId'])?$_REQUEST['iTripId']:'';


/*Start original route*/
$sql="select tPlatitudes,tPlongitudes from trips_locations where iTripId = '".$iTripId."'";
$data_locations = $obj->MySQLSelect($sql);

$lat_array = explode(",",$data_locations[0]['tPlatitudes']);
$long_array = explode(",",$data_locations[0]['tPlongitudes']);

$total_ele = count($lat_array);
  
$inc=1;
if($total_ele > 200){
	$inc = round($total_ele / 200);
}
// echo $inc=5;
for($i=0;$i<$total_ele;$i+=$inc){
	$latitudes[] = $lat_array[$i];
	$longitudes[] = $long_array[$i];
}
array_push($latitudes,$lat_array[$total_ele-1]);
array_push($longitudes,$long_array[$total_ele-1]);
/*End original route*/

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
    <?php include_once("top/top_script.php");?>  
     <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>"></script>
</head>
<body>
	<!-- home page -->
	<!-- home page -->
	<div id="main-uber-page">
	<!-- Left Menu -->
	<?php include_once("top/left_menu.php"); ?>
	<!-- End: Left Menu-->
	<!-- Top Menu -->
	<?php include_once("top/header_topbar.php"); ?>
	<!-- End: Top Menu-->
	<!-- First Section -->
	<?php include_once("top/header.php"); ?>
	<!-- End: First Section -->
	<?php 
    if($_SESSION['sess_user'] == 'driver'){
    	$db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId,$_SESSION['sess_iUserId'],'Driver'); 
		$db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId,$_SESSION['sess_iUserId'],'Driver');
    } else if($_SESSION['sess_user'] == 'rider') {
    	$db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId,$_SESSION['sess_iUserId'],'Passenger');
		$db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId,$_SESSION['sess_iUserId'],'Passenger');
    }
    else if($_SESSION['sess_user'] == 'organization') {
    	$db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId,$_SESSION['sess_iUserId'],'Passenger','','Yes');
		$db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId,$_SESSION['sess_iUserId'],'Passenger','','Yes');
    } else {
    	$db_trip_data = $generalobj->getTripPriceDetailsForWeb($iTripId,'','Driver');
		$db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId,'','');
    }
   // echo"<pre>";print_r($db_trip_data);die;
   $systemTimeZone = date_default_timezone_get();
	if($db_trip_data['fCancellationFare'] > 0 && $db_trip_data['vTimeZone'] != "") {
		$dBookingDate = $endDate = converToTz($db_trip_data['tEndDateOrig'],$db_trip_data['vTimeZone'],$systemTimeZone);
	} else if($db_trip_data['tTripRequestDateOrig']!= "" && $db_trip_data['vTimeZone'] != "")  {
		$dBookingDate = converToTz($db_trip_data['tTripRequestDateOrig'],$db_trip_data['vTimeZone'],$systemTimeZone);
		$endDate = converToTz($db_trip_data['tEndDateOrig'],$db_trip_data['vTimeZone'],$systemTimeZone);
	} else {
		$dBookingDate = $db_trip_data['tTripRequestDateOrig'];
		$endDate = $db_trip_data['tEndDateOrig'];
	} ?>
<section class="profile-section">
    <div class="profile-section-inner">
        <div class="profile-caption _MB0_">
            <div class="page-heading">
                <h1><?=$langage_lbl['LBL_Invoice']; ?></h1>
            </div>
            <ul class="overview-detail">
                <li>
                    <div class="overview-data">
                        <strong><?= $langage_lbl['LBL_RIDE_NO']; ?></strong>
                        <span><?= !empty($db_trip_data['vRideNo']) ? $db_trip_data['vRideNo'] : '&nbsp;'; ?></span>
                    </div>
                </li>
                <li>
                    <div class="overview-data">
								<? if($db_trip_data['eType'] == 'UberX') { ?>
									<strong><?=$langage_lbl['LBL_MYTRIP_TRIP_TYPE']; ?></strong>
								<? } else { ?>
									<strong><?=$langage_lbl['LBL_INVOICE_Car']; ?></strong>
								<? } ?>
								<?php if(!empty($db_trip_data['vVehicleCategory'])){
								  echo "<span>".$db_trip_data['vVehicleCategory'] . "-" . $db_trip_data['vVehicleType']."</span>";
								} else {
									$carname = !empty($db_trip_data['carTypeName']) ? $db_trip_data['carTypeName'] : "&nbsp;";
								  echo "<span>".$carname."</span>";
								} ?>
                        
                    </div>
                </li>
                <li>
                    <div class="overview-data">
                        <strong><?=$langage_lbl['LBL_Trip_time']; ?></strong>
                        <span><?= !empty($db_trip_data['TripTimeInMinutes']) ? $db_trip_data['TripTimeInMinutes'] : '&nbsp;'; ?></span>
                    </div>
                </li>
                <li>
                    <div class="overview-data">
                        <strong><?=$langage_lbl['LBL_DISTANCE_TXT']; ?></strong>
                        <span><?= !empty($db_trip_data['fDistance']) ? $db_trip_data['fDistance']." ".$db_trip_data['DisplayDistanceTxt'] : '&nbsp;'; ?></span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>
<section class="profile-earning">
    <div class="profile-earning-inner"> 
        <div class="left-block">
            <div class="inv-block-inner">
                <?php if($db_trip_data['eType'] != 'UberX' && ($APP_DELIVERY_MODE != "Multi" || $db_trip_data['eType'] == "Ride")) { ?>
						<div class="trip-detail-map" id="invoice_map">
							<div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
						</div>
					<?php } ?>
               
                <div class="invoice-data-holder">
                    <div>
							 <strong class="sub-block-title"><?php echo $langage_lbl['LBL_FARE_BREAKDOWN_TXT']; ?></strong>
							<?php if ($_SESSION['sess_user'] == "company") { ?>
                        
                        <div class="inv-data">
                            <strong><?= $generalobj->clearName($db_trip_data['PassengerDetails']['vName'] . ' ' . $db_trip_data['PassengerDetails']['vLastName']); ?> <?= $langage_lbl['LBL_RIDE_TXT_ADMIN']; ?> <?= $langage_lbl['LBL_WITH_TXT']; ?>  <?= $generalobj->clearName($db_trip_data['DriverDetails']['vName'] . ' ' . $db_trip_data['DriverDetails']['vLastName']); ?>
								<?php } else if ($_SESSION['sess_user'] == "driver") { ?>
                        <div class="profile-image">
                            <? if ($db_trip_data['PassengerDetails']['vImgName'] != '' && file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'])) {
                            ?>
                            <img src = "<?= $tconfig["tsite_upload_images_passenger"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'] ?>" /><!-- style="height:150px;" -->
                            <? } else { ?>
                            <img src="assets/img/profile-user-img.png" alt="">
                            <? } ?>
                        </div>
                        <div class="inv-data">
                            <strong><?= $langage_lbl['LBL_You_ride_with']; ?> <?= $generalobj->clearName($db_trip_data['PassengerDetails']['vName'] . ' ' . $db_trip_data['PassengerDetails']['vLastName']); ?>
                    <?php } else { ?>
                        <div class="profile-image">
                            <? if ($db_trip_data['DriverDetails']['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'])) { ?>
                                <img src = "<?= $tconfig["tsite_upload_images_driver"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'] ?>" style="height:150px;"/>
                            <? } else { ?>
                                <img src="assets/img/profile-user-img.png" alt="">
                            <? } ?>
                        </div>
                        <div class="inv-data">
                            <strong><?= $langage_lbl['LBL_You_ride_with']; ?> <?= $generalobj->clearName($db_trip_data['DriverDetails']['vName'] . ' ' . $db_trip_data['DriverDetails']['vLastName']); ?>
                                <?php } ?>
									</strong>	 
                        
                            <ul>
										<?
										foreach ($db_trip_data['HistoryFareDetailsNewArr'] as $key => $value) {
											foreach ($value as $k => $val) {
												if($k == $langage_lbl['LBL_EARNED_AMOUNT']) {
													continue;
												} else if($k == $langage_lbl['LBL_SUBTOTAL_TXT']){
													continue;
												} else if($k == "eDisplaySeperator"){
													//echo '<li class="eDisplaySeperator"><hr/></li>';
												} else { ?>
													<li><span><?=$k;?></span><b><?php echo $val; ?></b></li>
										<?		}
											}
										}
									 ?>
									 <?php if($_SESSION['sess_user']== "driver" || $_SESSION['sess_user']== "company"){ ?>
											<li><strong><?= $langage_lbl['LBL_TOTAL_EARNINGS_FRONT']; ?></strong>
											<b><?= $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_EARNED_AMOUNT']]; ?></b></li>
	              					<?php } else { ?>
											<li><strong><?= $langage_lbl['LBL_Total_Fare']; ?></strong>
											<b><?= $db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_SUBTOTAL_TXT']]; ?></b></li>
	              					<?php } ?>
	              				</ul>
									<ul>
										<li style="border: 1px dashed #d1d1d1; padding: 4px;">
										<?
											foreach($db_reci_data as $key1=>$value1) { 
												foreach($value1 as $key2=>$value2) {
												 if(!empty($value2['ePaymentBy'])){
														?>
														<h4><?=$langage_lbl['LBL_PAYMENT_BY_TXT']?></h4>
														<?
															if($value2['ePaymentBy']=="Sender") {
																?><em><?=$langage_lbl['LBL_SENDER']?></em><?
															}else if($value2['ePaymentBy']=="Receiver"){
																?><em><?=$value2['PaymentPerson'];?></em><?
															}else if($value2['ePaymentBy']=="Individual"){
																?><em><?=$langage_lbl_admin['LBL_EACH_RECIPIENT'];?></em><?
															}
														?>
														</tr>
														<?
														break 2;
													}
												}
											}
										?>
										</li>
									</ul>
											
									<?php if(($db_trip_data['iActive'] == 'Finished' && $db_trip_data['eCancelled'] == "Yes") || ($db_trip_data['fCancellationFare'] > 0) || ($db_trip_data['iActive'] == 'Canceled' && $db_trip_data['fWalletDebit'] > 0)) {
										?>
										<ul>
											<li style="border:dotted 2px #000000;background: none;">
												<strong style="font-weight: bold;padding: 2px;" >
													<? if($db_trip_data['eCancelledBy'] == 'Driver'){
														echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_DRIVER_ADMIN'];
														if(!empty($db_trip_data['vCancelReason'])){
														 echo 'Reason: '.$db_trip_data['vCancelReason'];
														}
													} else if($db_trip_data['eCancelledBy'] == 'Passenger'){
														echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_PASSANGER_ADMIN'];
														if(!empty($db_trip_data['vCancelReason'])){
														 echo 'Reason: '.$db_trip_data['vCancelReason'];
														}
													} else { 
														echo $langage_lbl_admin['LBL_CANCELED_TRIP_ADMIN_TXT'];
													}?>
												</strong>
											</li>
										</ul>
										<div style="clear:both;"></div>
									<? } ?>	
								<?php if($_SESSION['sess_user'] != "driver" && $_SESSION['sess_user'] != "company"){
									if($db_trip_data['fTipPrice'] !="" && $db_trip_data['fTipPrice'] !="0" && $db_trip_data['fTipPrice'] !="0.00") { ?>
									<ul><li class="no-border"><strong><?=$langage_lbl['LBL_TIP_RS_TXT']; ?></strong><b> <?=$db_trip_data['fTipPrice'];?></b></li></ul>
								<?}  }?>
                        </div>
                    </div>
							<div class="inv-rating">
                        <?php if ($_SESSION['sess_user'] == "company") { ?>
                        <?php } else if ($_SESSION['sess_user'] == "driver") { ?>
                        <strong><?= $langage_lbl['LBL_Rate_Your_Ride']; ?>:</strong>
                        <?php
                            $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                            $db_trip_data['TripRating'] = '<span class="rating_img" style="width: 68px; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template .'/rating-stripe.svg);">
									<span style="margin: 0;float:left;display: block; width: ' . $rating_width . '%; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);"></span>
									</span>';
                            ?>
                            <?= $db_trip_data['TripRating']; ?>
                        <?php } else { ?>
                        <strong><?= $langage_lbl['LBL_Rate_Your_Ride']; ?>:</strong>
                        <?php
									$rating_width = ($db_trip_data['TripRating'] * 100) / 5;
									$db_trip_data['TripRating'] = '<span class="rating_img" style="display: block; width: 68px; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);">
									<span style="margin: 0;float:left;display: block; width: ' . $rating_width . '%; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);"></span>
									</span>';
								?>
								<?= $db_trip_data['TripRating']; ?>
                        <?php } ?>
								
								<?php if((!empty($db_trip_data['vSignImage']))){ ?>
									<div class="sign-block">
										
										  <?php $img1 = '';
											if(file_exists($tconfig["tsite_upload_trip_signature_images_path"]. '/'. $db_trip_data['vSignImage'])){
											 $img1=$tconfig["tsite_upload_trip_signature_images"]. '/' .$db_trip_data['vSignImage'];
											} ?>
											<b><?php echo $langage_lbl['LBL_SENDER_SIGN'];?></b>
											<img src="<?php echo $img1;?>" align="left">
									</div>
								<?php } ?>
							</div>
                </div>
					<?php
					if($db_trip_data['eType'] == 'UberX' &&  ($db_trip_data['vBeforeImage'] != '' || $db_trip_data['vAfterImage'] != '')){
						 $img_path = $tconfig["tsite_upload_trip_images"];
					?>
					<div>&nbsp;</div>
					<div class="invoice-data-holder">
					<h3><?php echo $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT'];?></b></h3>                      

					<div class="invoice-right-bottom-img">
					  <?php if($db_trip_data['vBeforeImage'] != '') { ?>                     
						 <div class="col-sm-6">
						 <h4> <?php echo $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN'];?></h4>
						  <b><a href="<?= $db_trip_data['vBeforeImage'] ?>" target="_blank" ><img src = "<?= $db_trip_data['vBeforeImage'] ?>" style="width:200px;" alt ="Before Images"/></a></b>
						 </div>
						 <?php } 
						  if($db_trip_data['vAfterImage'] != '') {?>
					  <div class="col-sm-6">
						 <h4><?php echo $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN'];?></h4>
						  <b><a href="<?= $db_trip_data['vAfterImage'] ?>" target="_blank" ><img src = "<?= $db_trip_data['vAfterImage'] ?>" style="width:200px;" alt ="After Images"/></a></b>
					  </div>
						  <?php } ?>
					</div>
					</div>
					<?php } ?>
					
					<? if($APP_DELIVERY_MODE == "Multi"){?>
					<div>&nbsp;</div>
               <div class="invoice-data-holder">
	        			<div class="invoice-part-bottom invoice-recipient">	
	      				
	                        <?php if(!empty($db_reci_data)){
							foreach($db_reci_data as $key1=>$value1) { 
								$no = $key1+1;
								$class = (!empty($value1['vSignImage'])) ? 'sign-img' : ''; ?>
								<div class="col-sm-6 <?php echo $class;?>">
								<div class="recepient_list">
								<h3><?= $langage_lbl['LBL_RECIPIENT_LIST_TXT'].'&nbsp;'. $no; ?></h3>
								<ul class="recipient_details">
									<? foreach($value1 as $key2=>$value2) {?>
										<li>
											<span><?=$value2['vFieldName']; ?> </span>
											<b><?=$value2['vValue']; ?> </b>
										</li>
									<? } ?>
									<? if(!empty($value2['ePaymentBy']) && $value2['ePaymentBy'] == "Individual"){?>
									<li>
										<span><?=$langage_lbl['LBL_AMOUNT_PAID_TXT']; ?></span>
										<b>
											<?=$value2['PaymentAmount'];?>
										</b>
									</li>
								<? }?>
									<?php if(!empty($value2['Receipent_Signature'])) {?>
										<li>
											<span><?=$langage_lbl['LBL_RECEIVER_SIGN']; ?></span>
											<b>
												<img width="100px" src="<?php echo $value2['Receipent_Signature'];?>" align="left" >
											</b>
										</li>
									<?php }else if($value2['vDeliveryConfirmCode'] != ""){ ?>
										<li>
											<span><?=$langage_lbl['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?></span>
											<b>
												<?=$value2['vDeliveryConfirmCode']?>
											</b>
										</li>
									<? } ?>
								</ul>
								</div>
								</div>
								<?php 
								} 
							}   ?>               				 
						</div>
					 </div>
					<? } ?>
            </div>
        </div>
        <div class="left-right">
            <div class="inv-destination-data">
                <ul>
						<?php if ($_SESSION['sess_user'] != "driver") { ?>    
						<li>
                        <i class="fa fa-user"></i>
                        <strong><?= $langage_lbl['LBL_DRIVER_NAME']; ?>:</strong>
                        <p><?= $generalobj->clearName($db_trip_data['DriverDetails']['vName'] . ' ' . $db_trip_data['DriverDetails']['vLastName']); ?></p>
						</li>
						<?php } ?>
						<li>
                        <i class="fa fa-calendar"></i>
                        <strong><?= $langage_lbl['LBL_TRIP_DATE_TXT']; ?>:</strong>
                        <p><?=@date('d M Y',@strtotime($dBookingDate));?></p>
						</li>
						<li>
                        <i class="fa fa-map-marker"></i>
                        <strong><?= $langage_lbl['LBL_PICKUP_LOCATION_TXT']; ?>:</strong>
                        <p><?=$db_trip_data['tSaddress'];?></p>
						</li>
                  <li>
                        <i class="fa fa-clock-o"></i>
                        <strong><?= $langage_lbl['LBL_PICKUP_TIME']; ?>:</strong>
                        <p><?=@date('h:i A',@strtotime($dBookingDate));?></p>
						</li>
						<?php if($db_trip_data['eType'] != 'UberX' && ($APP_DELIVERY_MODE != "Multi" || $db_trip_data['eType'] == "Ride")){ ?> 
						<li>
                        <i class="fa fa-map-marker"></i>
                        <strong><?= $langage_lbl['LBL_DROPOFF_LOCATION']; ?>:</strong>
                        <p><?=$db_trip_data['tDaddress'];?></p>
						</li>
						<li>
                        <i class="fa fa-clock-o"></i>
                        <strong><?= $langage_lbl['LBL_DROPOFF_TIME']; ?>:</strong>
                        <p><?=@date('h:i A',@strtotime($endDate));?></p>
                  </li>
						<?php } ?>
                </ul>
					 
					 <?php if($db_trip_data['eType'] == 'Deliver' && $APP_DELIVERY_MODE != 'Multi'){ ?>
						<br>
					 <h3><?=$langage_lbl['LBL_DELIVERY_DETAILS']; ?></h3><hr/>

					 <ul style="border-bottom:none">
						  <li><strong><?=$langage_lbl['LBL_RECEIVER_NAME']; ?> </strong><b><?=$db_trip_data['vReceiverName'];?></b></li>
						  <li><strong><?=$langage_lbl['LBL_RECEIVER_MOBILE']; ?> </strong><b><?=$db_trip_data['vReceiverMobile'];?></b></li>
						  <li><strong><?=$langage_lbl['LBL_PICK_UP_INS']; ?> </strong><b><?=$db_trip_data['tPickUpIns'];?></b></li>
						  <li><strong><?=$langage_lbl['LBL_DELIVERY_INS']; ?> </strong><b><?=$db_trip_data['tDeliveryIns'];?></b></li>
						  <li><strong><?=$langage_lbl['LBL_PACKAGE_DETAILS']; ?></strong><b><?=$db_trip_data['tPackageDetails'];?></b></li>
						  <li><strong><?=$langage_lbl['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?> </strong><b><?=$db_trip_data['vDeliveryConfirmCode'];?></b></li>       
						
					 </ul>

					 <?php } ?>
					 
                <div class="invoice-pay-type">
                    <strong><?= $langage_lbl['LBL_PAYMENT_TYPE_CAPS']; ?> :</strong>
						  <?php 
							if ($db_trip_data['ePayWallet'] == 'Yes'){
											$paymentMode = $langage_lbl['LBL_HEADER_RDU_WALLET'];
							} else {
							if($db_trip_data['vTripPaymentMode'] == 'Cash')
								$paymentMode = $langage_lbl['LBL_CASH_CAPS'];
							else if($db_trip_data['vTripPaymentMode'] == 'Card')
								$paymentMode = $langage_lbl['LBL_CARD_CAPS'];
							else if($db_trip_data['vTripPaymentMode'] == 'Organization')
								$paymentMode = $langage_lbl['LBL_ORGANIZATION_CAPS'];
							}              
						?>
                    <strong><?=$paymentMode?></strong>
                </div>
            </div>
        </div>
    </div>
</section><!-- *************call to section end************* -->

<?php include_once('footer/footer_home.php'); ?>
	<div style="clear:both;"></div>
	</div>
	<!-- footer part end -->
	<!-- Footer Script -->
	<?php include_once('top/footer_script.php'); ?>
	<!-- End: Footer Script -->
	<?php
	$lang = get_langcode($_SESSION['sess_lang']);
	?>
	<?php if ($lang != 'en') { ?>
		 <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
		 <? include_once('otherlang_validation.php');?>
	<?php } ?>
	<script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
	<!-- home page end-->
	<div  class="modal fade" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none">
		<div class="modal-dialog" >
			 <div class="modal-content">
				  <div class="modal-header">
						<h4 id="servicetitle">
							 <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
							 Service Details
							 <button type="button" class="close" data-dismiss="modal">x</button>
						</h4>
				  </div>
				  <div class="modal-body" style="max-height: 450px;overflow: auto;">
						<div id="service_detail"></div>
				  </div>
			 </div>
		</div>
  </div>
  <!-- Footer Script -->
<?php //include_once('top/footer_script.php'); ?>
<script src="assets/js/gmap3.js"></script>
<script type="text/javascript">
		var typeArr = '<?= json_encode($vehilceTypeArr, JSON_HEX_APOS); ?>';
		h = window.innerHeight;
		$("#page_height").css('min-height', Math.round(h - 99) + 'px');
		var arr1 = [];
		var lats = [];
		var longs = [];
		var markers = [];
		var map;
		function initialize() {
			 var thePoint = new google.maps.LatLng('20.1849963', '64.4125062');
			 var mapOptions = {
				  zoom: 4,
				  center: thePoint
			 };
			 map = new google.maps.Map(document.getElementById('map-canvas'),
						mapOptions);
			 from_to_polyline();
		}
		var tPlatitudes = '<?= json_encode($latitudes) ?>';
		lats = JSON.parse(tPlatitudes);
		var tPlongitudes = '<?= json_encode($longitudes) ?>';
		longs = JSON.parse(tPlongitudes);
		var pts = [];
		var bounds = new google.maps.LatLngBounds();
		for (var i = 0; i < lats.length; i++) {
			 var latlongs = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
			 pts.push(latlongs);
			 var point = latlongs;
			 bounds.extend(point);
			 if (i == 0) {
				  var start = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
			 } else if (i == lats.length - 1) {
				  var end = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
			 }
		}
		var directionsService = new google.maps.DirectionsService();
		var directionsOptions = {// For Polyline Route line options on map
			 polylineOptions: {
				  path: pts,
				  strokeColor: '#f35e2f',
				  strokeOpacity: 1.0,
				  strokeWeight: 4
			 }
		};
		var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);
		function from_to() {
			 var request = {
				  origin: start, // From locations latlongs
				  destination: end, // To locations latlongs
				  travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
			 };
			 directionsService.route(request, function (response, status) {
				  directionsDisplay.setMap(map);
				  directionsDisplay.setDirections(response);
			 });
		}
		$(document).ready(function () {
			 google.maps.event.addDomListener(window, 'load', initialize);
		});
		function from_to_polyline() {
			 DeleteMarkers('from_loc');
			 DeleteMarkers('to_loc');
			 setMarker(start, 'from_loc');
			 setMarker(end, 'to_loc');
			 var flightPath = '';
			 var flightPath = new google.maps.Polyline({
				  path: pts,
				  geodesic: true,
				  strokeColor: '#f35e2f',
				  strokeOpacity: 1.0,
				  strokeWeight: 4
			 });
			 map.fitBounds(bounds);
			 flightPath.setMap(map);
		}
		function setMarker(postitions, valIcon) {
			 var newIcon;
			 if (valIcon == 'from_loc') {
				  newIcon = 'webimages/upload/mapmarker/PinFrom.png';
			 } else if (valIcon == 'to_loc') {
				  newIcon = 'webimages/upload/mapmarker/PinTo.png';
			 } else {
				  newIcon = 'webimages/upload/mapmarker/PinTo.png';
			 }
			 marker = new google.maps.Marker({
				  map: map,
				  animation: google.maps.Animation.DROP,
				  position: postitions,
				  icon: newIcon
			 });
			 marker.id = valIcon;
			 markers.push(marker);
		}
		function DeleteMarkers(newId) {
			 for (var i = 0; i < markers.length; i++) {
				  if (newId != '') {
						if (markers[i].id == newId) {
							 markers[i].setMap(null);
						}
				  } else {
						markers[i].setMap(null);
						markers = [];
				  }
			 }
		}
		function showServiceModal(elem) {
			 //console.log(elem);
			 //return false;
			 var tripJson = JSON.parse($(elem).attr("data-json"));
			 var rideNo = $(elem).attr("data-trip");
			 var typeNameArr = JSON.parse(typeArr)
			 var serviceHtml = "";
			 var srno = 1;
			 for (var g = 0; g < tripJson.length; g++) {
				  serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "</p>";
				  srno++;
			 }
			 $("#service_detail").html(serviceHtml);
			 $("#servicetitle").text("Service Details : " + rideNo);
			 //$("#service_modal").modal('show');
			 $("#service_modal").addClass('active');
			 return false;
		}
</script>
<!-- End: Footer Script -->
</body>
</html>