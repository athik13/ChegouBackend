<?php
#################### For Prescription required start added by sneha  #######################
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$iServiceId = isset($_REQUEST['iServiceIdNew']) ? $_REQUEST['iServiceIdNew'] : '';
if(!empty($iServiceId)){
	$sql = "SELECT prescription_required FROM `service_categories` WHERE iServiceId = '".$iServiceId."'";
	$db_prescription = $obj->MySQLSelect($sql);
        echo $db_prescription[0]['prescription_required']; 
} 
exit;
################### For Prescription required end added by sneha  ######################
?>