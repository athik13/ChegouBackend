<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../common.php';
global $userObj;
session_start();
if (!isset($generalobjAdmin)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjAdmin = new General_admin();
}
$vServiceIDValue = isset($_REQUEST['iServiceOid']) ? $_REQUEST['iServiceOid'] : '';
$DbName = TSITE_DB;
$TableName = "auth_master_accounts_places";

$uniqueFieldValue = trim($vServiceIDValue);
$uniqueFieldName = '_id';
$updatedActiveData = implode(',', $_REQUEST['selectedval']);
$tempData["vActiveServices"] = $updatedActiveData;
$updated = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);
$_SESSION['success'] = '1';
$_SESSION['var_msg'] = "Active services updated successfully.";
?>