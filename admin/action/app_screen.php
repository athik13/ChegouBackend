<?php

include_once('../../common.php');
if (!isset($generalobjRider)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjRider = new General_admin();
}
ini_set("display_errors", 1);
error_reporting(E_ALL);
$generalobjRider->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$lPage_id = isset($_REQUEST['lPage_id']) ? $_REQUEST['lPage_id'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$tableName = "app_screen_master";
$redirectUrl = $tconfig["tsite_url_main_admin"] . "app_screen.php?" . $parameters;
if (($statusVal == 'Deleted' || $method == 'delete') && ($lPage_id != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-app-screen')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete record';
    } else {
        //Added By Hasmukh On 12-10-2018 For Solved Bug Start
        if ($lPage_id != "") {
            $catIds = $lPage_id;
        } else {
            $catIds = $checkbox;
        }
        //Added By Hasmukh On 12-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $getImages = $obj->MySQLSelect("SELECT vScreenImage FROM  " . $tableName . " WHERE lPage_id IN (" . $catIds . ")");
            $query = "DELETE FROM  " . $tableName . " WHERE lPage_id IN (" . $catIds . ")";
            $obj->sql_query($query);
            for ($g = 0; $g < count($getImages); $g++) {
                $img_path = $tconfig["tsite_upload_manage_app_screen"] . "/" . $getImages[$g]['vScreenImage'];
                unlink($img_path);
            }
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $redirectUrl);
    exit;
}

//Start Change single Status
if ($lPage_id != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-app-screen')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of record';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE " . $tableName . " SET eStatus = '" . $status . "' WHERE lPage_id = '" . $lPage_id . "'";
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
    header("Location:" . $redirectUrl);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-app-screen')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of record';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE " . $tableName . " SET eStatus = '" . $statusVal . "' WHERE lPage_id IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $redirectUrl);
    exit;
}
?>