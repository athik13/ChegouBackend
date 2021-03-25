<?php
include_once('common.php');

$script="CabBooking";
$tbl_name 	= 'register_driver';
$generalobj->check_member_login();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);

$ufxEnable = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";
$deliverallEnable = isDeliverAllModuleAvailable() ? "Yes" : "No";

$ssql = "";
if($ufxEnable != "Yes") {
	$ssql .= " AND cb.eType != 'UberX'";
}
if(!checkFlyStationsModule()) {
    $ssql.= " AND cb.iFromStationId = '0' AND cb.iToStationId = '0'";
}
if($rideEnable != "Yes") {
    $ssql .= " AND cb.eType != 'Ride'";
}
if($deliveryEnable != "Yes") {
    $ssql .= " AND cb.eType != 'Deliver' AND cb.eType != 'Multi-Delivery'";
}

$sql = "SELECT cb.*,CONCAT(ru.vName,' ',ru.vLastName) as rider,CONCAT(rd.vName,' ',rd.vLastName) as driver,vt.vVehicleType_".$_SESSION['sess_lang']." as vVehicleType ,vt.vRentalAlias_".$_SESSION['sess_lang']." as vRentalVehicleTypeName FROM cab_booking as cb LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE 1=1 AND cb.iCompanyId = '".$_SESSION['sess_iUserId']."' $ssql ORDER BY iCabBookingId DESC";
$data_drv = $obj->MySQLSelect($sql);
$estatus_label = array("Pending"=>$langage_lbl['LBL_PENDING'],"Assign"=>$langage_lbl['LBL_ASSIGNED'],"Accepted"=>$langage_lbl['LBL_ACCEPTED'],"Declined"=>$langage_lbl['LBL_DECLINED'],"Failed"=>$langage_lbl['LBL_FAILED_TXT'],"Cancel"=>$langage_lbl['LBL_CANCELED_TXT'],"Completed"=>$langage_lbl['LBL_COMPLETED']);
$iactive_label = array("Active"=>$langage_lbl['LBL_ACTIVE'],"Canceled"=>$langage_lbl['LBL_CANCELED_TXT'],"Finished"=>$langage_lbl['LBL_FINISHED_TXT'],"On Going Trip"=>$langage_lbl['LBL_ON_GOING_TRIP'],"Arrived"=>$langage_lbl['LBL_ARRIVED']);

$invoice_icon = "driver-view-icon.png";
$canceled_icon = "canceled-invoice.png";
$systemTimeZone = date_default_timezone_get();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
if ($action == 'delete' && $hdn_del_id != '') {
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
    $cancelreason = isset($_REQUEST['cancel_reason']) ? $_REQUEST['cancel_reason'] : '';
    $query = "UPDATE cab_booking SET eStatus = 'Cancel',eAutoAssign = 'No', eCancelBy= 'Rider',`vCancelReason`='" . $cancelreason . "' WHERE iCabBookingId = '" . $hdn_del_id . "'";
    $obj->sql_query($query);
    $sql1 = "select * from cab_booking where iCabBookingId=" . $hdn_del_id;
    $bookind_detail = $obj->MySQLSelect($sql1);
    $dBooking_date = $bookind_detail[0]['dBooking_date'];
    $vBookingNo = $bookind_detail[0]['vBookingNo'];
    $vSourceAddresss = $bookind_detail[0]['vSourceAddresss'];

    $dBookingDate = converToTz($bookind_detail[0]['dBooking_date'], $bookind_detail[0]['vTimeZone'], $systemTimeZone);
    $dBookingDate_new_mail = date("jS F, Y", strtotime($dBookingDate));
    $dBookingDate_new_mail_time = date("h:i A", strtotime($dBookingDate));
    $dBookingDate_new_mail = $dBookingDate_new_mail." ".$langage_lbl_admin['LBL_AT_TXT']." ".$dBookingDate_new_mail_time;
    
    $sql2 = "select vName,vLastName,vEmail,iDriverVehicleId,vPhone,vcode,vLang from register_driver where iDriverId=" . $iDriverId;
    $driver_db = $obj->MySQLSelect($sql2);
    $vPhone = $driver_db[0]['vPhone'];
    $vcode = $driver_db[0]['vcode'];
    $vLang = $driver_db[0]['vLang'];

    $SQL3 = "SELECT vName,vLastName,vEmail,iUserId,vPhone,vPhoneCode,vLang FROM register_user WHERE iUserId = '$iUserId'";
    $user_detail = $obj->MySQLSelect($SQL3);
    $vPhone1 = $user_detail[0]['vPhone'];
    $vcode1 = $user_detail[0]['vPhoneCode'];
    $vLang1 = $user_detail[0]['vLang'];

    $Data1['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $Data1['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $Data1['vRiderMail'] = $user_detail[0]['vEmail'];
    $Data1['vSourceAddresss'] = $vSourceAddresss;
    $Data1['dBookingdate'] = $dBookingDate_new_mail;
    $Data1['vBookingNo'] = $vBookingNo;

    $Data['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $Data['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $Data['vDriverMail'] = $driver_db[0]['vEmail'];
    $Data['vSourceAddresss'] = $vSourceAddresss;
    $Data['dBookingdate'] = $dBookingDate_new_mail;
    $Data['vBookingNo'] = $vBookingNo;

    $return = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_DRIVER", $Data);
    $return1 = $generalobj->send_email_user("MANUAL_CANCEL_TRIP_ADMIN_TO_RIDER", $Data1);

    $Booking_Date = @date('d-m-Y', strtotime($dBooking_date));
    $Booking_Time = @date('H:i:s', strtotime($dBooking_date));

    $maildata['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $maildata['dBookingdate'] = $Booking_Date;
    $maildata['dBookingtime'] = $Booking_Time;
    $maildata['vBookingNo'] = $vBookingNo;

    $maildata1['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $maildata1['dBookingdate'] = $Booking_Date;
    $maildata1['dBookingtime'] = $Booking_Time;
    $maildata1['vBookingNo'] = $vBookingNo;

    //added by SP for sms functionality on 15-7-2019 start
    $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
    $PhoneCodeP = $passengerData[0]['vPhoneCode'];
    $DriverData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $iDriverId AND r.vCountry = c.vCountryCode");
    $PhoneCodeD = $DriverData[0]['vPhoneCode'];
    //added by SP for sms functionality on 15-7-2019 end

    $message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE_JOB_CANCEL", $maildata1, "", $vLang);
    //$return5 = $generalobj->sendUserSMS($vPhone, $vcode, $message_layout, "");
    $message_layout = str_replace("Administrator",$langage_lbl['LBL_RIDER'],$message_layout);
    $return5 = $generalobj->sendSystemSms($vPhone,$PhoneCodeD,$message_layout); //added by SP for sms functionality on 15-7-2019
    $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_JOB_CANCEL", $maildata, "", $vLang1);
    //$return4 = $generalobj->sendUserSMS($vPhone1, $vcode1, $message_layout, "");
    $message_layout = str_replace("Administrator",$langage_lbl['LBL_RIDER'],$message_layout);
    $return4 = $generalobj->sendSystemSms($vPhone1,$PhoneCodeP,$message_layout); //added by SP for sms functionality on 15-7-2019
    echo "<script>location.href='cabbooking.php'</script>";
    //header("Location:cab_booking.php");
    //exit;
}
//function converToTz($time, $toTz, $fromTz,$dateFormat="Y-m-d H:i:s") {
//	$date = new DateTime($time, new DateTimeZone($fromTz));
//	$date->setTimezone(new DateTimeZone($toTz));
//	$time = $date->format($dateFormat);
//	return $time;
//}

/* Added By PJ for Drivers dropdown */
$driverSql1 = "select iDriverId,vName,vLastName,vEmail,vPhone,vCode from register_driver where eStatus='active'";
$driverDatas = $obj->MySQLSelect($driverSql1);
/* End Added By PJ for Drivers dropdown */

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?=$SITE_NAME?></title>-->
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_RIDE_LATER_BOOKINGS_ADMIN']; ?></title>
    <meta name="keywords" value="<?=$meta_arr['meta_keyword'];?>"/>
    <meta name="description" value="<?=$meta_arr['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php
    include_once("top/top_script.php");
    $rtls = "";
    if ($lang_ltr == "yes") {
        $rtls = "dir='rtl'";
    }
        ?>
    <!-- End: Default Top Script and css-->

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
                <h1><?=$langage_lbl['LBL_RIDE_LATER_BOOKINGS_ADMIN']; ?></h1>
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
					<?php if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery'){ ?> 
					<th><?=$langage_lbl['LBL_TRIP_JOB_TYPE_FRONT'];?></th>
					<?php } ?>
					<th><?=$langage_lbl['LBL_MYTRIP_RIDE_NO_TXT']; ?></th>
					<th><?=$langage_lbl['LBL_COMPANY_TRIP_RIDER']; ?> / <?=$langage_lbl['LBL_COMPANY_TRIP_DRIVER']; ?></th>
					<th><?=$langage_lbl['LBL_COMPANY_TRIP_Trip_Date']; ?></th>
					<th><?=$langage_lbl['LBL_Pick_Up']; ?>  
						<? if($APP_TYPE != "UberX"){ ?>
						/<?=$langage_lbl['LBL_ADMIN_DROPOFF']; ?>
						<? } ?>
					</th>

					<th><?=$langage_lbl['LBL_TRIP_DETAILS']; ?></th>
					<th><?=$langage_lbl['LBL_COMPANY_TRIP_Car_Type']; ?></th>
                </tr>
            </thead>
				<tbody>
						<?php 
                          if(!empty($data_drv)) {
                          for ($i = 0; $i < count($data_drv); $i++) {
							$setcurrentTime = strtotime(date('Y-m-d H:i:s'));
							$bookingdate = 	date("H:i", strtotime('+30 minutes',  strtotime($data_drv[$i]['dBooking_date'])));
							$bookingdatecmp =strtotime($bookingdate);                     
	                        $default = '';
							if($data_drv[$i]['eDefault']=='Yes'){
							      $default = 'disabled';
							}

	                        $eType_new = $data_drv[$i]['eType'];
							if($eType_new == 'Ride' && $data_drv[$i]['iRentalPackageId'] > 0){
								$trip_type = $langage_lbl['LBL_RENTAL_CATEGORY_TXT']." ".$langage_lbl['LBL_RIDE']; //'Rental Ride';
							} else if($eType_new == 'Ride'){
								$trip_type = $langage_lbl['LBL_RIDE']; //'Ride';
							} else if($eType_new == 'UberX') {
								$trip_type1 = 'Other Services';
								$trip_type = $langage_lbl['LBL_OTHER']; //'Other Services';
							}  else if($eType_new == 'Deliver') {
								$trip_type = $langage_lbl['LBL_DELIVERY']; //'Delivery';
							}
							if($trip_type1 == 'Other Services') {
								$service_type = $langage_lbl['LBL_MYTRIP_TRIP_TYPE'];
							}else{
								$service_type = $langage_lbl['LBL_VEHICLE_TYPE_SMALL_TXT'];
							}
                             if(!empty($data_drv[$i]['iFromStationId']) && !empty($data_drv[$i]['iToStationId'])) {
                                $trip_type = $langage_lbl['LBL_HEADER_RDU_FLY_RIDE'];
                            }

                        	$rentalquery = 'SELECT vPackageName_'.$default_lang.' as pkgName FROM rental_package WHERE iRentalPackageId = "'.$data_drv[$i]['iRentalPackageId'].'"';
                        	$rental_data = $obj->MySQLSelect($rentalquery);
                          	
                           ?>
                        	<tr>
                           		<?php if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery'){ ?> 
		                        <td><? echo $trip_type; ?></td>
		                        <?php } ?>
                            	<td><?= $data_drv[$i]['vBookingNo']; ?></td>
								<td class="lableCombineData">
									<label><?= $langage_lbl['LBL_RIDER']; ?></label>
									<span><?= $generalobj->clearName($data_drv[$i]['rider']); ?></span>

								<label><?= $langage_lbl['LBL_PROVIDER'] ?></label>	
								<span>
							  	<!-- Provider -->
							  	<?php if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['iRentalPackageId'] > 0  ) { ?>
								
									<?=$langage_lbl['LBL_DRIVER_TXT_ADMIN'];?> : <?=$langage_lbl['LBL_AUTO_ASSIGN_WEB'];?> </b><br />( <?=$langage_lbl['LBL_Car_Type'];?> : <?= $data_drv[$i]['vRentalVehicleTypeName']; ?>)<br/>
									<? if($rental_data[0]['pkgName'] != ''  ) { ?>
									(<?=$langage_lbl['LBL_VEHICLE_TYPE_RENTAL_TXT'];?> : <?= $rental_data[0]['pkgName'];?>)
									<? } ?>
								
                            	<?php } else if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['eType'] == "Deliver" && $data_drv[$i]['iDriverId'] == 0 && $data_drv[$i]['eStatus'] != 'Cancel' && $APP_DELIVERY_MODE == "Multi") { ?>
								<?=$langage_lbl['LBL_DRIVER_TXT_ADMIN'];?> :  <?=$langage_lbl['LBL_AUTO_ASSIGN_WEB'];?> </b><br />( <?= ucfirst(strtolower($langage_lbl['LBL_VEHICLE_TYPE_TXT']));?> : <?= $data_drv[$i]['vVehicleType']; ?>)<br/><?php if(strtotime($data_drv[$i]['dBooking_date'])>strtotime(date('Y-m-d'))){ ?><a class="gen-btn small-btn" href="javascript:void(0);" onclick="assignDriver('<?= $data_drv[$i]['iCabBookingId']; ?>');" data-tooltip="tooltip" title="Edit"> <?=$langage_lbl['LBL_ASSIGN_DRIVER_BUTTON'];?></a><?php } ?>
								<?php } else if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['iDriverId'] == 0 && $data_drv[$i]['eStatus'] != 'Cancel') { ?>
								<?=$langage_lbl['LBL_DRIVER_TXT_ADMIN'];?> :  <?=$langage_lbl['LBL_AUTO_ASSIGN_WEB'];?>  </b><br />( <?=$langage_lbl['LBL_Car_Type'];?> : <?= $data_drv[$i]['vVehicleType']; ?>)<br/>
									<?php if(strtotime($data_drv[$i]['dBooking_date'])>strtotime(date('Y-m-d'))){ if($_SESSION['SessionUserType'] != 'hotel') {?>
                                	<a class="gen-btn small-btn" href="userbooking.php?userType1=company&booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>" data-tooltip="tooltip" title="Edit"><i class="icon-edit icon-flip-horizontal icon-white"></i></a>
                              		<?php } else { ?>
                               		<a class="gen-btn small-btn" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>" data-tooltip="tooltip" title="Edit"><i class="icon-edit icon-flip-horizontal icon-white"></i></a>
                              		<? }
                            		} ?>
                            	
                            	<?php } else if ($data_drv[$i]['eStatus'] == "Pending" && (strtotime($data_drv[$i]['dBooking_date'])>strtotime(date('Y-m-d'))) && $data_drv[$i]['iDriverId'] == 0) { ?>
								
	                                <? if($_SESSION['SessionUserType'] != 'hotel') {?>
	                                <a class="gen-btn small-btn" href="userbooking.php?userType1=company&booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> <?=$langage_lbl['LBL_ASSIGN_DRIVER_BUTTON'];?></a>
	                                <? } else { ?>
	                                <a class="gen-btn small-btn" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> <?=$langage_lbl['LBL_ASSIGN_DRIVER_BUTTON'];?></a>
	                                <? } ?>
                                <br>( <?=$service_type;?> : <?= $data_drv[$i]['vVehicleType']; ?>)
                                <?php } else if($data_drv[$i]['eCancelBy'] == "Driver" && $data_drv[$i]['eStatus'] == "Cancel" && $data_drv[$i]['iDriverId'] == 0) { ?>
								
	                                <? if($_SESSION['SessionUserType'] != 'hotel') {?> 
	                                <a class="gen-btn small-btn" href="userbooking.php?userType1=company&booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> <?=$langage_lbl['LBL_ASSIGN_DRIVER_BUTTON'];?></a>
	                                <? } else { ?>
	                                <a class="gen-btn small-btn" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> <?=$langage_lbl['LBL_ASSIGN_DRIVER_BUTTON'];?></a>
	                                <? } ?><br>( <?=$service_type;?> : <?= $data_drv[$i]['vVehicleType']; ?>)
	                            
	                            <?php } else if ($data_drv[$i]['driver'] != "" && $data_drv[$i]['driver'] != "0") { ?>
								<b><?= $generalobj->clearName($data_drv[$i]['driver']); ?></b><br>( <?=$service_type;?> : <?= $data_drv[$i]['vVehicleType']; ?>) 
								<?php } else  { ?>
								
								---<br>( <?=$service_type;?> : <?= $data_drv[$i]['vVehicleType']; ?>)
								<?php } ?>

								<!-- End Provider -->									
								</span>
								</td>


								<td><?php if($data_drv[$i]['dBooking_date']!= "" && $data_drv[$i]['vTimeZone'] != "")  {
                                 	$dBookingDate = converToTz($data_drv[$i]['dBooking_date'],$data_drv[$i]['vTimeZone'],$systemTimeZone);
                              	} else {
                                 	$dBookingDate = $data_drv[$i]['dBooking_date'];
                              	}
                              	echo $generalobj->DateTime($dBookingDate,7); ?></td>
							  	<td>
							  		<div class="lableCombineData ">
								  		<label><?= $langage_lbl['LBL_Pick_Up'] ?></label><span><?= $data_drv[$i]['vSourceAddresss']; ?></span>
									  	<?if($APP_TYPE != "UberX" && $data_drv[$i]['eType']!='UberX'){?>
									  	<label><?= $langage_lbl['LBL_DROP_AT'] ?></label><span><?= $data_drv[$i]['tDestAddress']; ?></span>
									  	<? } ?>
								  	</div>
							  	</td>

                                <?php
                                $link_page = "invoice.php";
                                if ($eType == 'Multi-Delivery') {
                                    $link_page = "invoice_multi_delivery.php";
                                }
                                ?>

								<td width="10%">
									<?php $sql="select iActive, eCancelledBy from trips where iTripId=".$data_drv[$i]['iTripId'];
		                            $data_stat_check=$obj->MySQLSelect($sql);
		                            if(!empty($data_stat_check))
		                              {
		                                for($d=0;$d<count($data_stat_check);$d++)
		                                {
		                                  if($data_stat_check[$d]['iActive'] == "Canceled") {
		                                    echo "---";
		                                  } else if($data_stat_check[$d]['iActive'] == "Finished"){ ?>
		                                    <a target = "_blank" class="gen-btn small-btn" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($data_drv[$i]['iTripId'])) ?>" target="_blank"><?= $langage_lbl['LBL_VIEW'] ?></a>
		                                 <?php  } else { 
		                                   echo "---";
		                                 }
		                                }
		                              } else {
		                                  if($data_drv[$i]['iTripId'] != "" && $data_drv[$i]['eStatus'] == "Completed") { ?><a target = "_blank" class="gen-btn small-btn" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($data_drv[$i]['iTripId'])) ?>" target="_blank"><?= $langage_lbl['LBL_VIEW'] ?></a>
		  								<?php } else { echo "---"; 	}
		                            } ?>
								</td>
								<td width="15%" class="statusType">
                            <?php 
                            $setcurrentTime = strtotime(date('Y-m-d H:i:s'));
                            $bookingdate =  date("Y-m-d H:i", strtotime('+30 minutes',  strtotime($data_drv[$i]['dBooking_date'])));
                            $bookingdatecmp =strtotime($bookingdate);
                            if($data_drv[$i]['eStatus'] == "Assign" && $bookingdatecmp > $setcurrentTime) {
								echo $langage_lbl['LBL_DRIVER_TXT_ADMIN']." ".$langage_lbl['LBL_ASSIGNED'];
							} else if($data_drv[$i]['eStatus'] == 'Accepted'){
                                  if($bookingdatecmp > $setcurrentTime ){
                                    echo $estatus_label[$data_drv[$i]['eStatus']];
                                  } else {
                                    echo $langage_lbl['LBL_EXPIRED_TXT'];
                                  }
                            } else if($data_drv[$i]['eStatus'] == 'Declined'){
                                    echo $estatus_label[$data_drv[$i]['eStatus']];
                            ?>
                            <br /><a href="javascript:void(0);" class="gen-btn btn-info" data-toggle="modal" data-target="#uiModal_<?=$data_drv[$i]['iCabBookingId'];?>"><?php echo $langage_lbl['LBL_CANCEL_REASON'];?></a>
                            <?php 
                            } else {
							$sql="select iActive, eCancelledBy from trips where iTripId=".$data_drv[$i]['iTripId'];
							$data_stat=$obj->MySQLSelect($sql);
							if($data_stat)
							{
								for($d=0;$d<count($data_stat);$d++)
								{
									if($data_stat[$d]['iActive'] == "Canceled") {
										$eCancelledBy = ($data_stat[$d]['eCancelledBy'] == 'Passenger') ? $langage_lbl['LBL_RIDER_NAME_TXT_ADMIN'] : $langage_lbl['LBL_DRIVER_TXT_ADMIN'];
    									echo  $langage_lbl['LBL_CANCEL_BY']." ".$eCancelledBy;
									} else if($data_stat[$d]['iActive'] == "Finished" && $data_stat[$d]['eCancelledBy'] == "Driver" ){
                                    echo $langage_lbl['LBL_CANCEL_BY']." ".$eCancelledBy;
                                  } else {
										echo $iactive_label[$data_stat[$d]['iActive']]; 	
									}
								}
							} else {
								if($data_drv[$i]['eStatus'] == "Cancel") {
									//echo "Canceled By ".$langage_lbl['LBL_DRIVER_TXT_ADMIN'];
									if($data_drv[$i]['eCancelBy'] == "Driver"){
										echo $langage_lbl['LBL_CANCEL_BY']." ".$langage_lbl['LBL_DRIVER_TXT_ADMIN'];
									}else if($data_drv[$i]['eCancelBy'] == "Rider"){
										echo $langage_lbl['LBL_CANCEL_BY']." ".$langage_lbl['LBL_RIDER_NAME_TXT_ADMIN'];
									} else{
                                    	echo $langage_lbl['LBL_CANCELLED_BY_ADMIN'];
                                  	}
								} else {
									if($data_drv[$i]['eStatus'] == 'Pending' && $bookingdatecmp > $setcurrentTime ){
										echo $estatus_label[$data_drv[$i]['eStatus']];
									} else {
										echo $langage_lbl['LBL_EXPIRED_TXT'];
									}
								}
							}
						}
						?>
													<?
														if ($data_drv[$i]['eStatus'] == "Cancel") {
													?>
														<br /><a href="javascript:void(0);" class="gen-btn btn-info" data-toggle="modal" data-target="#uiModal_<?=$data_drv[$i]['iCabBookingId'];?>"><?php echo $langage_lbl['LBL_CANCEL_REASON'];?></a>
													<?           
														}
                            if(($bookingdatecmp >  time()) && ($data_drv[$i]['eStatus'] == 'Pending' || $data_drv[$i]['eStatus'] == "Assign" || $data_drv[$i]['eStatus'] == "Accepted") ) {
													?>
                          <div>
                             <button type="button" class="gen-btn small-btn" data-toggle="modal" data-target="#delete_form<?php echo $data_drv[$i]['iCabBookingId'];?>"><?= $langage_lbl['LBL_CANCEL_BOOKING'] ?></button> 
                            
                            <!-- Modal -->
                            <div id="delete_form<?php echo $data_drv[$i]['iCabBookingId'];?>" class="custom-modal-main in delete_form" role="dialog">
                            	<div class="custom-modal">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="model-header">
						<h4 class="model-title"><?php echo $langage_lbl['LBL_BOOKING_CANCEL_WEB'];?></h4>
						<i class="icon-close" data-dismiss="modal"></i>    
					</div>
					<form  role="form" name="delete_form" id="delete_form1" method="post" action="" class="margin0 general-form">
						<div class="model-body">
						  <div class="form-group">
						      <strong style="text-align: initial;"><?php echo $langage_lbl['LBL_CANCEL_REASON'];?><span class="red">*</span></strong>
						      <div>
							<textarea name="cancel_reason" id="cancel_reason" rows="4" cols="40" required="required" autofocus></textarea>
							<div class="cnl_error error red"></div>
						      </div>
						  </div>
						    <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $data_drv[$i]['iCabBookingId']; ?>">
						    <input type="hidden" name="action" id="action" value="delete">
						    <input type="hidden" name="iDriverId" id="iDriverId" value="<?= $data_drv[$i]['iDriverId']; ?>">
						    <input type="hidden" name="iUserId" id="iUserId" value="<?= $data_drv[$i]['iUserId']; ?>">
						</div>
						<div class="model-footer button-block">
						  <button type="submit" class="gen-btn btn-primary" id="cnl_booking" title="Cancel Booking"><?=$langage_lbl['LBL_CANCEL_BOOKING'];?></button>
						  <button type="button" class="gen-btn btn-default" data-dismiss="modal"><?=$langage_lbl['LBL_CLOSE_TXT'];?></button>
						</div>
					</form> 
	                              </div>
	                          </div>
                            </div>
                            <!-- Modal -->

                          </div> 
                          <?php } ?>
						  </td>
						</tr>
						<div class="custom-modal-main fade" id="uiModal_<?=$data_drv[$i]['iCabBookingId'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="custom-modal">
							  <div class="model-content image-upload-1" >
								   <div class="upload-content" >

	                                  <div class="model-header">
	                                    <h4 class="model-title"><?php echo $langage_lbl['LBL_BOOKING_CANCEL_REASON'];?></h4>
	                                  </div>

	                                  <div class="model-body">
										
										<ul class="value-listing">
        								<?php if(!empty($data_drv[$i]['eCancelBy'])) { ?> 

									  	<li><b><?= $langage_lbl['LBL_CANCEL_BY'] ?> </b> <span><? /*if($APP_TYPE != "UberX"){
										
										echo $data_drv[$i]['eCancelBy'];
										
										} else{*/
											if($data_drv[$i]['eCancelBy'] == "Driver"){
											echo $langage_lbl['LBL_DRIVER_TXT_ADMIN'];
											} else if($data_drv[$i]['eCancelBy'] == "Rider"){
											echo $langage_lbl['LBL_RIDER_NAME_TXT_ADMIN'];
											} else {
           									 echo $data_drv[$i]['eCancelBy'];
											}
										/*} */
										?></span></li>

        								<?php } ?>
        								<li><b><?php echo $langage_lbl['LBL_CANCEL_REASON'];?></b> <span><?=$data_drv[$i]['vCancelReason'] ? $data_drv[$i]['vCancelReason'] : ' --- ';?></span></li>
									
										</ul>
									</div>
									<div class="model-footer">
										<form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" action="" name="frm6">
										<input style="margin:10px 0 20px;" type="button" class="save gen-btn" data-dismiss="modal" name="cancel" value="<?=$langage_lbl['LBL_CLOSE_TXT'];?>"></form>
									</div>	
								   </div>
								</div>
							  </div>
                            <?php } } //else { ?>
                               <!--  <tr class="gradeA">
                                    <td colspan="8"> No Records Found.</td>
                                </tr> -->
                            <?php //} ?>
					</tbody>
        </table>
    </div>
    </div>

	<div id="myModal" class="custom-modal-main" role="dialog">
		<div class="custom-modal">						

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="model-header">
					<h4 class="modal-title"><?= $langage_lbl['LBL_DELIVER_DETAILS'] ?></h4>
					<i class="icon-close" data-dismiss="modal"></i>    
                    </div>
                    <div class="model-body">
                        <div class="general-form">
                            <div class="col-lg-12">
                                <label><?= $langage_lbl['LBL_DRIVERS_NAME_ADMIN'] ?> <span class="red"> *</span></label>
                            </div>
                            <div class="form-group">
                                <select name="frmDriver" id="frmDriver" onChange="shoeDriverDetail002(this.value);" class="form-control  filter-by-text">
                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT']." ".$langage_lbl['LBL_DRIVER_TXT_ADMIN'] ?></option>
                                    <?php
                                    if (count($driverDatas) > 0) {
                                        for ($i = 0; $i < count($driverDatas); $i++) {
                                            ?>
                                            <option value="<?php echo $driverDatas[$i]['iDriverId']; ?>"><?php echo $driverDatas[$i]['vName'] . ' ' . $driverDatas[$i]['vLastName'] . " ( +" . $driverDatas[$i]['vCode'] . "&nbsp;" . $driverDatas[$i]['vPhone'] . " )"; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <span class="col-lg-6" id="showDriver003"></span>
                        <input type="hidden" name="iBookingId" id="iBookingId" value="" >
                    </div>
                    <div class="model-footer">
					<div class="button-block">
						<button type="button" class="gen-btn btn-primary" onclick="assignDriverForBooking();"><?= $langage_lbl['LBL_ASSIGN_DRIVER_BUTTON'] ?></button>
                        <button type="button" class="gen-btn btn-default" onclick="closeModal();"><?= $langage_lbl['LBL_CLOSE_TXT'] ?></button>
					</div>
										

                    </div>
                </div>
        </div>
	</div>	
</section>
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
        $('#my-trips-data').DataTable({
            "oLanguage": langData,
            });
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
            //$('#dataTables-example').dataTable();
            <? if($APP_TYPE == 'Ride-Delivery-UberX'){ ?>
            	$('#dataTables-example').dataTable({
                    "oLanguage": langData,
				  "order": [[ 3, "desc" ]],
				});
			<? } else if($APP_TYPE == 'Ride-Delivery'){ ?>
            	$('#dataTables-example').dataTable({
                    "oLanguage": langData,
				  "order": [[ 3, "desc" ]],
				});
            <? } else { ?>
            	$('#dataTables-example').dataTable({
                    "oLanguage": langData,
				  "order": [[ 2, "desc" ]],
				});
            <? } ?>

         });


		function assignDriver(bookingId) {
			$('#iBookingId').val(bookingId);
			$('#txtDriverEmail').val('');
			$('#txtDriverCompanyName').val('');
			$('#txtDriverMobileNumber').val('');
			$('#driverdetail').css('display', 'none');
			$('#myModal').modal('show');
			$('#myModal').addClass('active');
		}
		function showServiceModal(elem) {
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
			$("#service_modal").modal('show');
			return false;
		}
		function assignDriverForBooking() {
			driverId = $('#frmDriver').val();
			if (driverId != "") {
				bookingId = $('#iBookingId').val();
				var request = $.ajax({
					type: "POST",
					url: 'cx-ajax_assign_driver_cabbooking.php',
					data: {'driverId': driverId, 'bookingId': bookingId},
					success: function (data)
					{
						if (data.trim() == 1) {
							window.location = 'cabbooking.php';
						} else {
							alert('Email sending failed.');
							window.location = 'cabbooking.php';
						}
					}
				});
			} else {
				alert('Please assign a Driver.');
			}
		}

		function closeModal() {
                $('#myModal').modal('hide');
                $('#myModal').removeClass('active');
                $('#driverdetail').css('display', 'none');
                $('#frmDriver').val('');
                $('#txtDriverEmail').val('');
                $('#txtDriverCompanyName').val('');
                $('#txtDriverMobileNumber').val('');
            }
            $('select.filter-by-text').select2();
            function shoeDriverDetail002(id) {
                if (id != "") {
                    var request2 = $.ajax({
                        type: "POST",
                        url: 'show_driver.php',
                        dataType: 'html',
                        data: 'id=' + id,
                        success: function (data)
                        {
                            $("#showDriver003").html(data);
                        }, error: function (data) {
                        }
                    });
                } else {
                    $("#showDriver003").html('');
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
