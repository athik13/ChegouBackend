<?php

include_once('../../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iLocatioId = isset($_REQUEST['iLocatioId']) ? $_REQUEST['iLocatioId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//print_R($_REQUEST);die;
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iLocatioId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-fly-fare')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete fly fare';
    } else {
        //Added By Hasmukh On 12-10-2018 For Solved Bug Start
        if ($iLocatioId != "") {
            $locationIds = $iLocatioId;
        } else {
            $locationIds = $checkbox;
        }
        //Added By Hasmukh On 12-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "DELETE FROM fly_location_wise_fare WHERE iLocatioId IN (" . $locationIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "fly_locationwise_fare.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iLocatioId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-fly-fare')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update status of fly fare';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE fly_location_wise_fare SET eStatus = '" . $status . "' WHERE iLocatioId = '" . $iLocatioId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            if ($status == 'Active') {
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
            } else {
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "fly_locationwise_fare.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-fly-fare')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update status of fly fare';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE fly_location_wise_fare SET eStatus = '" . $statusVal . "' WHERE iLocatioId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "fly_locationwise_fare.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>
