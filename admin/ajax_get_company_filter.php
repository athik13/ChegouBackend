<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$iServiceId = isset($_REQUEST['iServiceIdNew']) ? $_REQUEST['iServiceIdNew'] : '';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
if(!empty($iServiceId)){
	$sql = "SELECT c.iCompanyId,c.vCompany,c.iServiceId,c.vEmail FROM `company` AS c LEFT JOIN food_menu AS f ON f.iCompanyId = c.iCompanyId WHERE iServiceId = '".$iServiceId."' and  c.eStatus!='Deleted' GROUP BY c.iCompanyId ORDER BY `vCompany`";
	$db_company = $obj->MySQLSelect($sql);
	echo "<option value=''>Select ".$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']."</option>";
	if (count($db_company) > 0) {
		for($i=0;$i<count($db_company);$i++){
			$selected='';					
			if($db_company[$i]['iCompanyId'] == $iCompanyId){
				$selected = "selected=selected";						
				
			}
			echo "<option value=".$db_company[$i]['iCompanyId']." ".$selected.">".$generalobjAdmin->clearName($db_company[$i]['vCompany']) ." - ( ". $generalobjAdmin->clearEmail($db_company[$i]['vEmail']).") </option>";
			
		}
		 exit;
		
	}
} else {
	echo "<option value=''>Select ".$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']."</option>";exit;
}		
?>
