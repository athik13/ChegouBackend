<?php
######################  Auto Credit Driver Wallet start by PM ########################

function AutoCreditWalletDriver($data,$checktype = 'ProcessEndTrip',$iServiceId="0") {
    global $generalobj,$obj;
	 
	 
	   $iUserId =$data['iUserId'];
	   $iTripId =$data['iTripId'];
	 if($checktype=='ChargePassengerOutstandingAmount' || $checktype=='cancelTrip' ) {
			## Update passenger outstanding amount by giving refund to previous drivers for card trips ##	
					 $sql = "SELECT iTripOutstandId,fDriverPendingAmount,vTripAdjusmentId,iDriverId,fWalletDebit,iTripId FROM trip_outstanding_amount WHERE iUserId = '" . $iUserId . "' AND ePaidToDriver = 'No'";
					$tripoutstandingdata = $obj->MySQLSelect($sql);  	
					if(count($tripoutstandingdata) > 0){
						for($i=0;$i<count($tripoutstandingdata);$i++) {
							$TripOutstandId = $tripoutstandingdata[$i]['iTripOutstandId'];
							$fDriverPendingAmount = $tripoutstandingdata[$i]['fDriverPendingAmount']-$tripoutstandingdata[$i]['fWalletDebit'];
							$iTripRefundId = $tripoutstandingdata[$i]['iTripId'];
							$iDriverRefundId = $tripoutstandingdata[$i]['iDriverId'];
							$sql = "SELECT vRideNo FROM trips WHERE iTripId = '" . $iTripRefundId . "'";
							$tripridedata = $obj->MySQLSelect($sql);
							$vRideNo = $tripridedata[0]['vRideNo'];
							$iBalance = $fDriverPendingAmount;
							$eFor = "Booking";
							$eType = "Credit";
							$ePaymentStatus = "Settelled";
							$dDate = Date('Y-m-d H:i:s');
							$tDescription_credit = '#LBL_AMOUNT_CANCELTRIP_CREDIT# ' . $vRideNo;
							$generalobj->InsertIntoUserWallet($iDriverRefundId, "Driver", $iBalance, $eType, $iTripRefundId, $eFor, $tDescription_credit, $ePaymentStatus, $dDate);
							
						    $where = " iTripId = '" . $iTripRefundId . "'";
							$data['ePaymentDriverStatus']="Paid";
							$idd = $obj->MySQLQueryPerform("payments", $data, 'update', $where);
						
							$WhereCard = " iTripId = '$iTripRefundId'";
							$Data_update_driver_paymentstatus_Card = array();
							$Data_update_driver_paymentstatus_Card['eDriverPaymentStatus'] = "Settelled";
							$Update_Payment_Card_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus_Card, 'update', $WhereCard); 
							## Add Trip Earning Amount Into Driver Wallet ##
								
							$updateQuery = "UPDATE trip_outstanding_amount set ePaidToDriver = 'Yes' WHERE iTripOutstandId = " . $TripOutstandId;
							$obj->sql_query($updateQuery);
							
							
						}
					} 
					  if($checktype=='cancelTrip' && !empty($iTripId)) {
					 
						$sql = "SELECT vTripPaymentMode FROM trips WHERE iTripId = '" . $iTripId . "'";
						$tripoutstandingdata = $obj->MySQLSelect($sql);
						$vTripPaymentMode=$tripData[0]['vTripPaymentMode']; 
						 if ($vTripPaymentMode == "Card"){
								$updateQuery2 = "UPDATE trips set fOutStandingAmount = 0 WHERE iTripId = '" . $iTripId. "'";
								$obj->sql_query($updateQuery2);
						 }
					 }
				## Update passenger outstanding amount by giving refund to previous drivers for card trips ##
				 
			$updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $iUserId;
			$obj->sql_query($updateQuery);
				
	} elseif($checktype=='CollectPayment') {	
	
			    $isCollectCash=$data['isCollectCash'];   
				$dDate= Date('Y-m-d H:i:s');

			    $sql = "SELECT vTripPaymentMode,iUserId,iDriverId,iFare,vRideNo,fWalletDebit,fTripGenerateFare,fDiscount,fCommision,fTollPrice,eHailTrip,fOutStandingAmount,fHotelCommision,eBookingFrom, ePaymentCollect_Delivery,fTax1,fTax2, eType FROM trips WHERE iTripId='$iTripId'";
				$tripData = $obj->MySQLSelect($sql);
				$eType=$tripData[0]['eType'];
				$vTripPaymentMode=$tripData[0]['vTripPaymentMode'];
				$ePaymentCollect_Delivery=$tripData[0]['ePaymentCollect_Delivery']; 
				$vRideNo = $tripData[0]['vRideNo'];
				$iDriverId = $tripData[0]['iDriverId'];
				$totalTax = $tripData[0]['fTax1'] + $tripData[0]['fTax2'];
 
			
		  if ($vTripPaymentMode == "Cash" && $isCollectCash == "" && ( $eType != "Multi-Delivery" || ($ePaymentCollect_Delivery == "No" && $eType == "Multi-Delivery"))) {  
								 $sql = "SELECT iTripOutstandId,fDriverPendingAmount,vTripAdjusmentId,iDriverId,fWalletDebit,iTripId FROM trip_outstanding_amount WHERE iUserId = '" . $iUserId . "' AND ePaidToDriver = 'No'";
									$tripoutstandingdata = $obj->MySQLSelect($sql);  	
									if(count($tripoutstandingdata) > 0){
										for($i=0;$i<count($tripoutstandingdata);$i++) {
											$TripOutstandId = $tripoutstandingdata[$i]['iTripOutstandId'];
											$fDriverPendingAmount = $tripoutstandingdata[$i]['fDriverPendingAmount']-$tripoutstandingdata[$i]['fWalletDebit'];
											$iTripRefundId = $tripoutstandingdata[$i]['iTripId'];
											$iDriverRefundId = $tripoutstandingdata[$i]['iDriverId'];
											$sql = "SELECT vRideNo FROM trips WHERE iTripId = '" . $iTripRefundId . "'";
											$tripridedata = $obj->MySQLSelect($sql);
											$vRideNo = $tripridedata[0]['vRideNo'];
											$iBalance = $fDriverPendingAmount;
											$eFor = "Booking";
											$eType = "Credit";
											$ePaymentStatus = "Settelled";
											$dDate = Date('Y-m-d H:i:s');
											$tDescription_credit = '#LBL_AMOUNT_CANCELTRIP_CREDIT# ' . $vRideNo;
											$generalobj->InsertIntoUserWallet($iDriverRefundId, "Driver", $iBalance, $eType, $iTripRefundId, $eFor, $tDescription_credit, $ePaymentStatus, $dDate);
											
											$updateQuery = "UPDATE trip_outstanding_amount set ePaidToDriver = 'Yes', ePaidByPassenger = 'Yes' WHERE iTripOutstandId = " . $TripOutstandId;
											$obj->sql_query($updateQuery);
											
											$WhereCard = " iTripId = '$iTripRefundId'";
											$Data_update_driver_paymentstatus_Card = array();
											$Data_update_driver_paymentstatus_Card['eDriverPaymentStatus'] = "Settelled";
											$Update_Payment_Card_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus_Card, 'update', $WhereCard); 
											## Add Trip Earning Amount Into Driver Wallet ##
										}	   
									$updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $iUserId;
									$obj->sql_query($updateQuery);
								}								
					} 
					if (($vTripPaymentMode == "Card" || $vTripPaymentMode == "Organization") && $isCollectCash == "" && ( $eType != "Multi-Delivery" || ($ePaymentCollect_Delivery == "No" && $eType == "Multi-Delivery"))) {
					 
						## Update passenger outstanding amount by giving refund to previous drivers for card trips ##	
							$fOutStandingAmount = $tripData[0]['fOutStandingAmount'];
							
							 $sql = "SELECT iTripOutstandId,fDriverPendingAmount,vTripAdjusmentId,iDriverId,fWalletDebit,iTripId FROM trip_outstanding_amount WHERE iUserId = '" . $iUserId . "' AND ePaidToDriver = 'No'";
						$tripoutstandingdata = $obj->MySQLSelect($sql);  	
						if(count($tripoutstandingdata) > 0){
							for($i=0;$i<count($tripoutstandingdata);$i++) {
								$TripOutstandId = $tripoutstandingdata[$i]['iTripOutstandId'];
									$fDriverPendingAmount = $tripoutstandingdata[$i]['fDriverPendingAmount']-$tripoutstandingdata[$i]['fWalletDebit'];
									$iTripRefundId = $tripoutstandingdata[$i]['iTripId'];
									$iDriverRefundId = $tripoutstandingdata[$i]['iDriverId'];
									$sql = "SELECT vRideNo FROM trips WHERE iTripId = '" . $iTripRefundId . "'";
									$tripridedata = $obj->MySQLSelect($sql);
									$vRideNo = $tripridedata[0]['vRideNo'];
									$iBalance = $fDriverPendingAmount;
									$eFor = "Booking";
									$eType = "Credit";
									$ePaymentStatus = "Settelled";
									$dDate = Date('Y-m-d H:i:s');
									$tDescription_credit = '#LBL_AMOUNT_CANCELTRIP_CREDIT# ' . $vRideNo;
									$generalobj->InsertIntoUserWallet($iDriverRefundId, "Driver", $iBalance, $eType, $iTripRefundId, $eFor, $tDescription_credit, $ePaymentStatus, $dDate);
									
									$updateQuery = "UPDATE trip_outstanding_amount set ePaidToDriver = 'Yes', ePaidByPassenger = 'Yes' WHERE iTripOutstandId = " . $TripOutstandId;
									$obj->sql_query($updateQuery);
								}
									$updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $iUserId;
									$obj->sql_query($updateQuery);
							} 
						## Update passenger outstanding amount by giving refund to previous drivers for card trips ##	
					    // Added By HJ On 18-12-2019 For Prevent Duplication Issue Dicuss with KS Sir Start
							$getPaymentStatus = $obj->MySQLSelect("SELECT eUserType,ePaymentStatus,iUserWalletId,eType FROM user_wallet WHERE iTripId='" . $iTripId . "'");
							$walletArray = array();
							for ($h = 0; $h < count($getPaymentStatus); $h++) {
								$walletArray[$getPaymentStatus[$h]['eType']][$getPaymentStatus[$h]['eUserType']][$getPaymentStatus[$h]['ePaymentStatus']] = $getPaymentStatus[$h]['eType'];
							}
						// Added By HJ On 18-12-2019 For Prevent Duplication Issue Dicuss with KS Sir End
							
						## Add Trip Earning Amount Into Driver Wallet ##
						$vRideNo = $tripData[0]['vRideNo']; 
					  	$iBalanceCard = $tripData[0]['fTripGenerateFare']-( $tripData[0]['fCommision'] + $tripData[0]['fHotelCommision'] + $tripData[0]['fOutStandingAmount'] + $totalTax );
						$eForCard = "Deposit";
						$eTypeCard = "Credit";
						$iTripId = $iTripId;
						$tDescriptionCard = '#LBL_CREDITED_TRIP_EARNING_PROVIDER#' . $vRideNo;
						$ePaymentStatusCard = 'Settelled';
						$dDateCard = Date('Y-m-d H:i:s');
					
						$generalobj->InsertIntoUserWallet($iDriverId, "Driver", $iBalanceCard, $eTypeCard, $iTripId, $eForCard, $tDescriptionCard, $ePaymentStatusCard, $dDateCard);
					
						$WhereCard = " iTripId = '$iTripId'";
						$Data_update_driver_paymentstatus_Card = array();
						$Data_update_driver_paymentstatus_Card['eDriverPaymentStatus'] = "Settelled";
						$Update_Payment_Card_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus_Card, 'update', $WhereCard); 
						## Add Trip Earning Amount Into Driver Wallet ##
					}
									
        } else if($checktype=='TripCollectTip') {
	
			  $price=$data['price'];		 	
			$TripsData = get_value('trips', 'iDriverId,vRideNo', 'iTripId', $iTripId);
			$vRideNo = $TripsData[0]['vRideNo'];
			$iDriverId = $TripsData[0]['iDriverId'];  
			$datad_wallet['iUserId'] = $iDriverId;
			$datad_wallet['eUserType'] = "Driver";
			$datad_wallet['iBalance'] = $price;
			$datad_wallet['eType'] = "Credit";
			$datad_wallet['dDate'] = date("Y-m-d H:i:s");
			$datad_wallet['iTripId'] = $iTripId;
			$datad_wallet['eFor'] = "Deposit";
			$datad_wallet['ePaymentStatus'] = "Unsettelled";
			$datad_wallet['tDescription'] = '#LBL_CREDITED_TIP_AMOUNT_TXT#' ." - ". $vRideNo; //Debited for Tip of Trip
			
			$generalobj->InsertIntoUserWallet($datad_wallet['iUserId'], $datad_wallet['eUserType'], $datad_wallet['iBalance'], $datad_wallet['eType'], $datad_wallet['iTripId'], $datad_wallet['eFor'], $datad_wallet['tDescription'], $datad_wallet['ePaymentStatus'], $datad_wallet['dDate']);
	 
		}
	 elseif($checktype=='UpdateOrderStatusDriver') {	
				$vOrderNo=$data['vOrderNo'];
				$ePaymentOption=$data['ePaymentOption'];  
				$iUserId=$data['iUserId'];
				$iOrderId=$data['iOrderId']; 
				$fDeliveryCharge=$data['fDeliveryCharge']; 
				
			     $sql = "SELECT vTripPaymentMode,iUserId,iDriverId,iTripId,vRideNo FROM trips WHERE iOrderId = '" . $iOrderId . "'";
				$tripData = $obj->MySQLSelect($sql);
				$eType=$tripData[0]['eType'];
				$vTripPaymentMode=$tripData[0]['vTripPaymentMode'];
				$vRideNo = $tripData[0]['vRideNo'];
				$iDriverId = $tripData[0]['iDriverId'];
				$iTripId = $tripData[0]['iTripId'];
				$totalTax = $tripData[0]['fTax1'] + $tripData[0]['fTax2'];
			 
			 
				 $sql = "SELECT iTripOutstandId,fDriverPendingAmount,vTripAdjusmentId,iDriverId,fWalletDebit,iTripId FROM trip_outstanding_amount WHERE iUserId = '" . $iUserId . "' AND ePaidToDriver = 'No'";
					$tripoutstandingdata = $obj->MySQLSelect($sql);  	
					if(count($tripoutstandingdata) > 0){
					for($i=0;$i<count($tripoutstandingdata);$i++) {
						$TripOutstandId = $tripoutstandingdata[$i]['iTripOutstandId'];
						$fDriverPendingAmount = $tripoutstandingdata[$i]['fDriverPendingAmount']-$tripoutstandingdata[$i]['fWalletDebit'];
						$iTripRefundId = $tripoutstandingdata[$i]['iTripId'];
						$iDriverRefundId = $tripoutstandingdata[$i]['iDriverId'];
						$sql = "SELECT vRideNo FROM trips WHERE iTripId = '" . $iTripRefundId . "'";
						$tripridedata = $obj->MySQLSelect($sql);
						$vRideNo = $tripridedata[0]['vRideNo'];
						$iBalance = $fDriverPendingAmount;
						$eFor = "Booking";
						$eType = "Credit";
						$ePaymentStatus = "Settelled";
						$dDate = Date('Y-m-d H:i:s');
						$tDescription_credit = '#LBL_AMOUNT_CANCELTRIP_CREDIT# ' . $vRideNo;
						$generalobj->InsertIntoUserWallet($iDriverRefundId, "Driver", $iBalance, $eType, $iTripRefundId, $eFor, $tDescription_credit, $ePaymentStatus, $dDate);
						
						$updateQuery = "UPDATE trip_outstanding_amount set ePaidToDriver = 'Yes', ePaidByPassenger = 'Yes',vOrderAdjusmentId = '" . $vOrderNo . "'  WHERE iTripOutstandId = " . $TripOutstandId;
						$obj->sql_query($updateQuery);
					}
						$updateQuery = "UPDATE register_user set fTripsOutStandingAmount = '0' WHERE iUserId = " . $iUserId;
						$obj->sql_query($updateQuery);
				}
				  
					$eForCard = "Deposit";
					$eTypeCard = "Credit";
					$iTripId = $iTripId;
					$tDescriptionCard = '#LBL_CREDITED_TRIP_EARNING_PROVIDER#' . $vOrderNo;
					$ePaymentStatusCard = 'Settelled';
					$dDateCard = Date('Y-m-d H:i:s');
					$generalobj->InsertIntoUserWallet($iDriverId, "Driver", $fDeliveryCharge, $eTypeCard, $iTripId, $eForCard, $tDescriptionCard, $ePaymentStatusCard, $dDateCard, $iOrderId);
					$WhereCard = " iTripId = '$iTripId'";
					$Data_update_driver_paymentstatus_Card = array();
					$Data_update_driver_paymentstatus_Card['eDriverPaymentStatus'] = "Settelled";
					$Update_Payment_Card_Id = $obj->MySQLQueryPerform("trips", $Data_update_driver_paymentstatus_Card, 'update', $WhereCard); 
				
				
		}
	 
	}
######################  Auto Credit Driver Wallet End by PM ########################
?>