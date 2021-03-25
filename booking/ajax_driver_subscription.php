<?php
	// error_reporting(E_ALL);
	include_once('../common.php');
	if(!empty($_SESSION['sess_iAdminUserId'])) {
	
	} else {
		$generalobj->check_member_login();
	}
	
	$iDriverId = isset($_REQUEST['driverId']) ? $_REQUEST['driverId'] : ''; 
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : ''; 
	
	$cont="";
	 
         /* Driversubscription added by SP */ 
        $DRIVER_SUBSCRIPTION_ENABLE = $generalobj->getConfigurations("configurations", "DRIVER_SUBSCRIPTION_ENABLE");
        if($DRIVER_SUBSCRIPTION_ENABLE=='Yes') {
            $sql_subscribe = "SELECT count(iDriverSubscriptionPlanId) as cnt FROM driver_subscription_details WHERE iDriverId = $iDriverId AND eSubscriptionStatus = 'Subscribed'";
            $db_subscribe = $obj->MySQLSelect($sql_subscribe);
            $subscribeCount = $db_subscribe[0]['cnt'];
            if($subscribeCount==0) {
                $cont = "1";
            }
        }
        /* Driversubscription added by SP */  
	 echo $cont;
	 exit;
?>