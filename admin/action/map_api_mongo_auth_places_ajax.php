<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../../common.php';

global $userObj;
if (!isset($generalobjDriver)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjDriver = new General_admin();
}

$RequestData = json_decode(stripslashes($_REQUEST['info']));
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $RequestData->checkbox) : '';
$deleteme = $RequestData->deleteme;
$vServiceId = $RequestData->vServiceId;
$DbName = TSITE_DB;
$TableName = "auth_accounts_places";
$status = "Inactive";
$uniqueFieldName = '_id';
$uniqueFieldValue = trim($RequestData->ioid);
$tempData['eStatus'] = $status;

if($deleteme == 'Y'){

	$DbName = TSITE_DB;
    $TableName = "auth_accounts_places";
    $searchQuery = [];
    if ($uniqueFieldValue != '') {
        $searchQuery['_id'] = new MongoDB\BSON\ObjectID($uniqueFieldValue);
    }
    $deleted = $obj->deleteRecordsFromMongoDB($DbName, $TableName, $searchQuery);
	
		$DbName = TSITE_DB;
        $TableNameMaster = "auth_master_accounts_places";
        $uniqueFieldNameMaster = 'vServiceId';
        $uniqueFieldValueMaster = intval($vServiceId);
        $tempDataMaster = [];
        $tempDataMaster["eStatus"] = "Inactive";
        $asdasd = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableNameMaster, $uniqueFieldNameMaster, $uniqueFieldValueMaster, $tempDataMaster);
        header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters);
        exit;
}

if ($status == 'Active') {
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_ACTIVATE_MSG"];
} else {
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_RECORD_INACTIVATE_MSG"];
}

if ($checkbox != '') {
    if ($statusVal != '') {
        $tempData['eStatus'] = $statusVal;
        $checkbox = explode(",", $checkbox);
        for ($i = 0; $i < count($checkbox); $i++) {
            $updated = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $checkbox[$i], $tempData);
        }

        $DbName = TSITE_DB;
        $TableNameMaster = "auth_master_accounts_places";
        $uniqueFieldNameMaster = 'vServiceId';
        $uniqueFieldValueMaster = intval($vServiceId);
        $tempDataMaster = [];
        $tempDataMaster["eStatus"] = $status;
        $asdasd = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableNameMaster, $uniqueFieldNameMaster, $uniqueFieldValueMaster, $tempDataMaster);

        header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters);
        exit;
    }
} else {
    if ($uniqueFieldValue != '') {
        $updated = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);
        $DbName = TSITE_DB;
        $TableNameMaster = "auth_master_accounts_places";
        $uniqueFieldNameMaster = 'vServiceId';
        $uniqueFieldValueMaster = intval($vServiceId);
        $tempDataMaster = [];
        $tempDataMaster["eStatus"] = $status;
        $asdasd = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableNameMaster, $uniqueFieldNameMaster, $uniqueFieldValueMaster, $tempDataMaster);
        header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?" . $parameters);
        exit;
    }
}
