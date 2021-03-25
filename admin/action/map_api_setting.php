<?php

include_once '../../common.php';
global $userObj;
if (!isset($generalobjDriver)) {
    require_once TPATH_CLASS . "class.general_admin.php";
    $generalobjDriver = new General_admin();
}

$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';
$date = Date('Y-m-d');
$generalobjDriver->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iMongoName = isset($_REQUEST['iMongoName']) ? $_REQUEST['iMongoName'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$DbName = TSITE_DB;
$TableName = "auth_master_accounts_places";
$uniqueFieldName = "vServiceName";
$uniqueFieldValue = $iMongoName;
$tempData['eStatus'] = $status;

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
            $updated = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableName, $uniqueFieldName, $checkbox[$i], $tempData);
        }
        header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_setting.php?" . $parameters);
        exit;
    }
} else {
    if ($uniqueFieldValue != '') {
        $updated = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);
        header("Location:" . $tconfig["tsite_url_main_admin"] . "map_api_setting.php?" . $parameters);
        exit;
    }
}
