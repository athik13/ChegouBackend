<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$iServiceid = isset($_REQUEST['iServiceid']) ? $_REQUEST['iServiceid'] : '';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';

$selectcuisine_sql = "SELECT cuisineId,cuisineName_".$default_lang." FROM cuisine WHERE  iServiceId = '".$iServiceid."' AND eStatus = 'Active'";
$db_cuisine = $obj->MySQLSelect($selectcuisine_sql);

$sql1 = "SELECT cuisineId FROM `company_cuisine` WHERE iCompanyId = '" . $iCompanyId . "'";
$db_cusinedata = $obj->MySQLSelect($sql1);
foreach ($db_cusinedata as $key => $value) {
    $cusineselecteddata[] = $value['cuisineId'];
}

if (count($db_cuisine) > 0) {
	foreach($db_cuisine as $cuisinedata){ 
		$selected='';				
		if(isset($cusineselecteddata) && in_array($cuisinedata['cuisineId'], $cusineselecteddata)){
			$selected = "selected=selected";
		}

		echo "<option name='".$cuisinedata['cuisineId']."' value='".$cuisinedata['cuisineId']."' ".$selected." >".$cuisinedata["cuisineName_".$default_lang]."</option>";			
		
	}
	 exit;
	
}

?>
