<?php
include_once("../common.php");

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$iVehicleTypeId = isset($_REQUEST['id'])?$_REQUEST['id']:'';
if($iVehicleTypeId != '')
{
	$sql = "select vCarType from driver_vehicle";  
	$db_model = $obj->MySQLSelect($sql); 
	
	$store = array();
	for($i=0;$i<count($db_model);$i++){
		$abc= explode(",", $db_model[$i]['vCarType']);
		$flag = true;
		if(in_array($iVehicleTypeId,$abc)){		
            $flag = true;
            echo $flag;
		}else{
            $flag = false; 
            echo $flag;
            exit;
		}
	}
}
?>