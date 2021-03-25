<?php
include_once('../common.php');

$adminId = isset($_REQUEST['adminId']) ? $_REQUEST['adminId'] : '';
if($adminId != "")
{
	$sql = "SELECT * FROM administrators WHERE iAdminId = '" . $adminId . "'";
	$db_data = $obj->MySQLSelect($sql);
	if(!empty($db_data))
	{
		echo json_encode($db_data);
	}
}
?>