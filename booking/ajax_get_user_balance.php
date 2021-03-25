<?php
	// error_reporting(E_ALL);
	include_once('../common.php');
	if(!empty($_SESSION['sess_iAdminUserId'])) {
	
	} else {
		$generalobj->check_member_login();
	}
	
	$iDriverId = isset($_REQUEST['driverId']) ? $_REQUEST['driverId'] : ''; 
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : ''; 
	
	$user_available_balance = $generalobj->get_user_available_balance($iDriverId,$type);
	$cont="";
	 if($COMMISION_DEDUCT_ENABLE == 'Yes') {
		 if($user_available_balance > $WALLET_MIN_BALANCE){
			 $cont.=1;
			 $cont.="|".$user_available_balance;
		 }else{
			 $cont.=0;
			 $cont.="|".$user_available_balance;
		 }
	 }else{
		  $cont.=1;
		  $cont.="|".$user_available_balance;
	 }
         
	 echo $cont;
	 exit;
?>