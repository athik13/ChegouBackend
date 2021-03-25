<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$iFoodMenuId = isset($_REQUEST['iFoodMenuId']) ? $_REQUEST['iFoodMenuId'] : '';
 
$sql = "SELECT fm.iFoodMenuId,fm.vMenu_".$default_lang." as menuTitle,c.vCompany,c.iCompanyId FROM  food_menu AS fm
			LEFT JOIN `company` as c on c.iCompanyId=fm.iCompanyId WHERE fm.iCompanyId='".$iCompanyId."' AND fm.eStatus != 'Deleted'";
$db_menu = $obj->MySQLSelect($sql);
	echo "<option value=''>--select--</option>";
if (count($db_menu) > 0) {
	for($i=0;$i<count($db_menu);$i++){
		$selected='';					
		if($db_menu[$i]['iFoodMenuId'] == $iFoodMenuId){
			$selected = "selected=selected";						
		}
		echo "<option value=".$db_menu[$i]['iFoodMenuId']." ".$selected.">".$generalobjAdmin->clearName($db_menu[$i]['menuTitle'])."</option>";	
	}
	 exit;
}
?>