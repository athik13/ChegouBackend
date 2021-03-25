<?
include_once("common.php");
include_once('generalFunctions.php');

$iTripId = isset($_REQUEST['trip_id'])?$_REQUEST['trip_id']:'';
$iUserId = isset($_REQUEST['uid'])?$_REQUEST['uid']:'';


$db_reci_data = $generalobj->getRecepientDetails_Trip($iTripId,$iUserId,'Passenger');
// echo "<pre>";print_r($db_reci_data);exit;
?>
	
	        			<div class="invoice-part-bottom invoice-part-bottom1">	
	                        <?php if(!empty($db_reci_data)){
							foreach($db_reci_data as $key1=>$value1) { 
								$no = $key1+1;
								$class = (!empty($value1['vSignImage'])) ? 'sign-img' : ''; ?>
								<div class="col-sm-6 <?php echo $class;?>"> 
								<h3><img src="assets/img/pin-rec.png" width="26px"> <?= $langage_lbl['LBL_RECIPIENT_LIST_TXT'].'&nbsp;'. $no; ?></h3>
								<table style="width:100%" class="deliverytable deliverytable-a" cellpadding="10" cellspacing="10" border="1">
									<? foreach($value1 as $key2=>$value2) {?>
										<tr>
											<td style="min-width: 150px;"><b class="bold-font"><?=$value2['vFieldName']; ?> </b></td>
											<td><?=$value2['vValue']; ?> </td>
										</tr>
									<? } ?>
									<? if(!empty($value2['ePaymentBy']) && $value2['ePaymentBy'] == "Individual"){?>
									<tr>
										<td ><b><?=$langage_lbl['LBL_AMOUNT_PAID_TXT']; ?></b></td>
										<td>
											<?=$value2['PaymentAmount'];?>
										</td>
									</tr>
								<? }?>
									<?php if(!empty($value2['Receipent_Signature'])) {?>
										<tr>
											<td><b><?=$langage_lbl['LBL_RECEIVER_SIGN']; ?></b></td>
											<td>
												<img width="100px" src="<?php echo $value2['Receipent_Signature'];?>" align="left" >
											</td>
										</tr>
									<?php }else if($value2['vDeliveryConfirmCode'] != ""){ ?>
										<tr>
											<td><b><?=$langage_lbl['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?></b></td>
											<td >
												<?=$value2['vDeliveryConfirmCode']?>
											</td>
										</tr>
									<? } ?>
								</table>
								</div>
								<?php 
								} 
							}   ?>               				 
						</div>