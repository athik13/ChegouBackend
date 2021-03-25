<?
include_once('common.php');

$script="CabBooking";
$tbl_name 	= 'register_driver';
$generalobj->check_member_login();
$abc = 'rider';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$generalobj->setRole($abc,$url);

$sql = "SELECT cb.*,CONCAT(ru.vName,' ',ru.vLastName) as rider,CONCAT(rd.vName,' ',rd.vLastName) as driver,vt.vVehicleType,vt.vRentalAlias_".$_SESSION['sess_lang']." as vRentalVehicleTypeName FROM cab_booking as cb LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE 1=1 AND cb.iUserId = '".$_SESSION['sess_iUserId']."' AND cb.eType	IN ('Ride','Deliver') AND ( cb.eStatus = 'Assign' OR cb.eStatus = 'Pending' OR cb.eStatus  = 'Accepted' OR cb.eStatus  = 'Declined'  OR cb.eStatus  = 'Cancel')  ORDER BY cb.dBooking_date DESC";
$data_drv = $obj->MySQLSelect($sql);

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
    $Data1['dBookingdate'] = $dBooking_date;
    $Data1['vBookingNo'] = $vBookingNo;

    $Data['vRider'] = $user_detail[0]['vName'] . " " . $user_detail[0]['vLastName'];
    $Data['vDriver'] = $driver_db[0]['vName'] . " " . $driver_db[0]['vLastName'];
    $Data['vDriverMail'] = $driver_db[0]['vEmail'];
    $Data['vSourceAddresss'] = $vSourceAddresss;
    $Data['dBookingdate'] = $dBooking_date;
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
    $message_layout = $generalobj->send_messages_user("DRIVER_SEND_MESSAGE_JOB_CANCEL", $maildata1, "", $vLang);
    $return5 = $generalobj->sendUserSMS($vPhone, $vcode, $message_layout, "");
    $message_layout = $generalobj->send_messages_user("USER_SEND_MESSAGE_JOB_CANCEL", $maildata, "", $vLang1);
    $return4 = $generalobj->sendUserSMS($vPhone1, $vcode1, $message_layout, "");
    echo "<script>location.href='usercabbooking.php'</script>";
    //header("Location:cab_booking.php");
    //exit;
}

function converToTz($time, $toTz, $fromTz,$dateFormat="Y-m-d H:i:s") {
	$date = new DateTime($time, new DateTimeZone($fromTz));
	$date->setTimezone(new DateTimeZone($toTz));
	$time = $date->format($dateFormat);
	return $time;
}
	

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_RIDE_LATER_BOOKINGS_ADMIN']; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
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
			  	<h2 class="header-page"><?=$langage_lbl['LBL_RIDE_LATER_BOOKINGS_USER']; ?></h2>
		  		<!-- trips page -->
			  	<div class="trips-page">
			    	<div class="trips-table"> 
			      		<div class="trips-table-inner">
                        <div class="driver-trip-table">
			        		<table width="100%" border="0" cellpadding="0" cellspacing="1" id="dataTables-example">
			          			<thead>
									<tr>
										<?php if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery'){ ?> 
										<th><?=$langage_lbl_admin['LBL_TRIP_JOB_TYPE_FRONT'];?></th>
										<?php } ?>
										<th><?=$langage_lbl['LBL_MYTRIP_RIDE_NO_TXT']; ?></th>
										<!-- <th><?=$langage_lbl['LBL_COMPANY_TRIP_RIDER']; ?></th> -->
										<th><?=$langage_lbl['LBL_COMPANY_TRIP_Trip_Date']; ?></th>
	        							<th><?=$langage_lbl['LBL_Pick_Up']; ?></th>
	        							<? if($APP_TYPE != "UberX"){ ?>
	        							<th><?=$langage_lbl['LBL_ADMIN_DROPOFF']; ?></th>
	        							<? } ?>
										<!-- <th><?=$langage_lbl['LBL_COMPANY_TRIP_DRIVER']; ?></th> -->
										<th><?=$langage_lbl['LBL_TRIP_DETAILS']; ?></th>
										<th><?=$langage_lbl['LBL_Status']; ?></th>
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
								$trip_type = 'Rental Ride';
							} else if($eType_new == 'Ride'){
								$trip_type = 'Ride';
							} else if($eType_new == 'UberX') {
								$trip_type = 'Other Services';
							}  else if($eType_new == 'Deliver') {
								$trip_type = 'Delivery';
							}
							if($trip_type == 'Other Services') {
								$service_type = $langage_lbl_admin['LBL_MYTRIP_TRIP_TYPE'];
							}else{
								$service_type = $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'];
							}

                        	$rentalquery = 'SELECT vPackageName_'.$default_lang.' as pkgName FROM rental_package WHERE iRentalPackageId = "'.$data_drv[$i]['iRentalPackageId'].'"';
                        	$rental_data = $obj->MySQLSelect($rentalquery);
                          	
                           ?>
                        	<tr>
                           		<?php if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery'){ ?> 
		                        <td><? echo $trip_type; ?></td>
		                        <?php } ?>
                            	<td><?= $data_drv[$i]['vBookingNo']; ?></td>
								<!-- <td><?= $generalobj->clearName($data_drv[$i]['rider']); ?></td> -->
								<td><?php if($data_drv[$i]['dBooking_date']!= "" && $data_drv[$i]['vTimeZone'] != "")  {
                                 	$dBookingDate = converToTz($data_drv[$i]['dBooking_date'],$data_drv[$i]['vTimeZone'],$systemTimeZone);
                              	} else {
                                 	$dBookingDate = $data_drv[$i]['dBooking_date'];
                              	}
                              	echo $generalobj->DateTime($dBookingDate,7); ?></td>
							  	<td><?= $data_drv[$i]['vSourceAddresss']; ?></td>
							  	<?if($APP_TYPE != "UberX"){?>
								<td><?= $data_drv[$i]['tDestAddress']; ?></td>
							  	<? } ?>
							  	<?php /*if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['iRentalPackageId'] > 0  ) { ?>
								<td>
									<?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> : <?=$langage_lbl_admin['LBL_AUTO_ASSIGN_WEB'];?> </b><br />( <?=$langage_lbl_admin['LBL_Car_Type'];?> : <?= $data_drv[$i]['vRentalVehicleTypeName']; ?>)<br/>
									<? if($rental_data[0]['pkgName'] != ''  ) { ?>
									(<?=$langage_lbl_admin['LBL_VEHICLE_TYPE_RENTAL_TXT'];?> : <?= $rental_data[0]['pkgName'];?>)
									<? } ?>
								</td>
                            	<?php } else if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['eType'] == "Deliver" && $data_drv[$i]['iDriverId'] == 0 && $data_drv[$i]['eStatus'] != 'Cancel' && $APP_DELIVERY_MODE == "Multi") { ?>
								<td><?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> :  <?=$langage_lbl_admin['LBL_AUTO_ASSIGN_WEB'];?> </b><br />( <?= ucfirst(strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_TXT']));?> : <?= $data_drv[$i]['vVehicleType']; ?>)<br/><?php if(strtotime($data_drv[$i]['dBooking_date'])>strtotime(date('Y-m-d'))){ ?><a class="btn btn-info" href="javascript:void(0);" onclick="assignDriver('<?= $data_drv[$i]['iCabBookingId']; ?>');" data-tooltip="tooltip" title="Edit"><i class="icon-edit icon-flip-horizontal icon-white"></i> <?=$langage_lbl_admin['LBL_ASSIGN_DRIVER_BUTTON'];?></a><?php } ?></td>
								<?php } else if ($data_drv[$i]['eAutoAssign'] == "Yes" && $data_drv[$i]['iDriverId'] == 0 && $data_drv[$i]['eStatus'] != 'Cancel') { ?>
								<td><?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> :  <?=$langage_lbl_admin['LBL_AUTO_ASSIGN_WEB'];?>  </b><br />( <?=$langage_lbl_admin['LBL_Car_Type'];?> : <?= $data_drv[$i]['vVehicleType']; ?>)<br/>
									<?php if(strtotime($data_drv[$i]['dBooking_date'])>strtotime(date('Y-m-d'))){ if($_SESSION['SessionUserType'] != 'hotel') {?>
                                	<a class="btn btn-info" href="userbooking.php?userType1=company&booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>" data-tooltip="tooltip" title="Edit"><i class="icon-edit icon-flip-horizontal icon-white"></i></a>
                              		<?php } else { ?>
                               		<a class="btn btn-info" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>" data-tooltip="tooltip" title="Edit"><i class="icon-edit icon-flip-horizontal icon-white"></i></a>
                              		<? }
                            		} ?>
                            	</td>
                            	<?php } else if ($data_drv[$i]['eStatus'] == "Pending" && (strtotime($data_drv[$i]['dBooking_date'])>strtotime(date('Y-m-d'))) && $data_drv[$i]['iDriverId'] == 0) { ?>
								<td width="10%">
	                                <? if($_SESSION['SessionUserType'] != 'hotel') {?>
	                                <a class="btn btn-info" href="userbooking.php?userType1=company&booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> Assign <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?></a>
	                                <? } else { ?>
	                                <a class="btn btn-info" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> Assign <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?></a>
	                                <? } ?>
                                <br>( <?=$service_type;?> : <?= $data_drv[$i]['vVehicleType']; ?>)</td>
                                <?php } else if($data_drv[$i]['eCancelBy'] == "Driver" && $data_drv[$i]['eStatus'] == "Cancel" && $data_drv[$i]['iDriverId'] == 0) { ?>
								<td width="10%">
	                                <? if($_SESSION['SessionUserType'] != 'hotel') {?> 
	                                <a class="btn btn-info" href="userbooking.php?userType1=company&booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> Assign <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?></a>
	                                <? } else { ?>
	                                <a class="btn btn-info" href="create_request.php?booking_id=<?= $data_drv[$i]['iCabBookingId']; ?>"><i class="icon-shield icon-flip-horizontal icon-white"></i> Assign <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?></a>
	                                <? } ?><br>( <?=$service_type;?> : <?= $data_drv[$i]['vVehicleType']; ?>)
	                            </td>
	                            <?php } else if ($data_drv[$i]['driver'] != "" && $data_drv[$i]['driver'] != "0") { ?>
								<td width="10%"><b><?= $generalobj->clearName($data_drv[$i]['driver']); ?></b><br>( <?=$service_type;?> : <?= $data_drv[$i]['vVehicleType']; ?>) </td>
								<?php } else  { ?>
								<td width="10%">---<br>( <?=$service_type;?> : <?= $data_drv[$i]['vVehicleType']; ?>)</td>
								<?php }*/?>
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
		                                    <a target = "_blank" class="btn btn-primary" href="invoice.php?iTripId=<?=$data_drv[$i]['iTripId']?>" target="_blank">View</a>
		                                 <?php  } else { 
		                                   echo "---";
		                                 }
		                                }
		                              } else {
		                                  if($data_drv[$i]['iTripId'] != "" && $data_drv[$i]['eStatus'] == "Completed") { ?><a target = "_blank" class="btn btn-primary" href="invoice.php?iTripId=<?=$data_drv[$i]['iTripId']?>" target="_blank">View</a>
		  								<?php } else { echo "---"; 	}
		                            } ?>
								</td>
								<td width="15%">
                            <?php 
                            $setcurrentTime = strtotime(date('Y-m-d H:i:s'));
                            $bookingdate =  date("Y-m-d H:i", strtotime('+30 minutes',  strtotime($data_drv[$i]['dBooking_date'])));
                            $bookingdatecmp =strtotime($bookingdate);
                            if($data_drv[$i]['eStatus'] == "Assign" && $bookingdatecmp > $setcurrentTime) {
														  echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Assigned";
							} else if($data_drv[$i]['eStatus'] == 'Accepted'){
                                  if($bookingdatecmp > $setcurrentTime ){
                                    echo $data_drv[$i]['eStatus'];
                                  } else {
                                    echo 'Expired';
                                  }
                            } else if($data_drv[$i]['eStatus'] == 'Declined'){
                                    echo $data_drv[$i]['eStatus'];
                            ?>
                          <!--   <br /><a href="javascript:void(0);" class="btn btn-info" data-toggle="modal" data-target="#uiModal_<?=$data_drv[$i]['iCabBookingId'];?>"><?php echo $langage_lbl_admin['LBL_CANCEL_REASON'];?></a> -->
                            <?php 
                            } else {
							$sql="select iActive, eCancelledBy from trips where iTripId=".$data_drv[$i]['iTripId'];
							$data_stat=$obj->MySQLSelect($sql);
							if($data_stat)
							{
								for($d=0;$d<count($data_stat);$d++)
								{
									if($data_stat[$d]['iActive'] == "Canceled") {
										$eCancelledBy = ($data_stat[$d]['eCancelledBy'] == 'Passenger') ? $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] : $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
    									echo "Canceled By ".$eCancelledBy;
									} else if($data_stat[$d]['iActive'] == "Finished" && $data_stat[$d]['eCancelledBy'] == "Driver" ){
                                    echo "Canceled By ".$eCancelledBy;
                                  } else {
										echo $data_stat[$d]['iActive']; 	
									}
								}
							} else {
								if($data_drv[$i]['eStatus'] == "Cancel") {
									//echo "Canceled By ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
									if($data_drv[$i]['eCancelBy'] == "Driver"){
										echo "Canceled By ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
									}else if($data_drv[$i]['eCancelBy'] == "Rider"){
										echo "Canceled By ".$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];
									} else{
                                    	echo "Canceled By Admin";
                                  	}
								} else {
									if($data_drv[$i]['eStatus'] == 'Pending' && $bookingdatecmp > $setcurrentTime ){
										echo $data_drv[$i]['eStatus'];
									} else {
										echo 'Expired';
									}
								}
							}
						}
						?>
													<?
														/*if ($data_drv[$i]['eStatus'] == "Cancel") {
													?>
														<br /><a href="javascript:void(0);" class="btn btn-info" data-toggle="modal" data-target="#uiModal_<?=$data_drv[$i]['iCabBookingId'];?>"><?php echo $langage_lbl_admin['LBL_CANCEL_REASON'];?></a>
													<?           
														}*/
                            if(($bookingdatecmp >  time()) && ($data_drv[$i]['eStatus'] == 'Pending' || $data_drv[$i]['eStatus'] == "Assign" || $data_drv[$i]['eStatus'] == "Accepted") ) {
													?>
                          <div>
                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#delete_form<?php echo $data_drv[$i]['iCabBookingId'];?>">Cancel Booking</button> 
                            <!-- Modal -->
                            <div id="delete_form<?php echo $data_drv[$i]['iCabBookingId'];?>" class="modal fade delete_form" role="dialog">
                              <div class="modal-dialog">

                                <!-- Modal content-->
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                    <h4 class="modal-title"><?php echo $langage_lbl_admin['LBL_BOOKING_CANCEL_WEB'];?></h4>
                                  </div>
                                    <form  role="form" name="delete_form" id="delete_form1" method="post" action="" class="margin0">
                                  <div class="modal-body">
                                    <div class="form-group" style="display: inline-block;">
                                        <label class="col-xs-4 control-label"><?php echo $langage_lbl_admin['LBL_CANCEL_REASON'];?><span class="red">*</span></label>
                                        <div class="col-xs-7">
                                            <textarea name="cancel_reason" id="cancel_reason" rows="4" cols="40" required="required" autofocus></textarea>
                                            <div class="cnl_error error red"></div>
                                        </div>
                                    </div>
                                      <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $data_drv[$i]['iCabBookingId']; ?>">
                                      <input type="hidden" name="action" id="action" value="delete">
                                      <input type="hidden" name="iDriverId" id="iDriverId" value="<?= $data_drv[$i]['iDriverId']; ?>">
                                      <input type="hidden" name="iUserId" id="iUserId" value="<?= $data_drv[$i]['iUserId']; ?>">
                                  </div>
                                  <div class="modal-footer">
                                    <button type="submit" class="btn btn-info" id="cnl_booking" title="Cancel Booking"><?=$langage_lbl_admin['LBL_CANCEL_BOOKING'];?></button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?=$langage_lbl_admin['LBL_CLOSE_TXT'];?></button>
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
						<div class="modal fade" id="uiModal_<?=$data_drv[$i]['iCabBookingId'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							  <div class="modal-content image-upload-1" style="width:400px;">
								   <div class="upload-content" style="width:350px; padding:0px;">
										<h3><?php echo $langage_lbl_admin['LBL_BOOKING_CANCEL_REASON'];?></h3>
        								<?php if(!empty($data_drv[$i]['eCancelBy'])) { ?> 

									  <h4>Cancel By:<? if($APP_TYPE != "UberX"){
										
										echo $data_drv[$i]['eCancelBy'];
										
										} else{
											if($data_drv[$i]['eCancelBy'] == "Driver"){
											echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
											} else if($data_drv[$i]['eCancelBy'] == "Rider"){
											echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];
											} else {
           									 echo $data_drv[$i]['eCancelBy'];
											}
										} 
										?></h4>
        								<?php } ?>
										<h4><?php echo $langage_lbl_admin['LBL_CANCEL_REASON'];?>: <?=$data_drv[$i]['vCancelReason'];?></h4>
										<form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" action="" name="frm6">
										<input style="margin:10px 0 20px;" type="button" class="save" data-dismiss="modal" name="cancel" value="<?=$langage_lbl_admin['LBL_CLOSE_TXT'];?>"></form>
								   </div>
							  </div>
                            <?php } } //else { ?>
                               <!--  <tr class="gradeA">
                                    <td colspan="8"> No Records Found.</td>
                                </tr> -->
                            <?php //} ?>
								</tbody>
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
				  "order": [[ 3, "desc" ]],
				});
            <? } ?>

         });
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
