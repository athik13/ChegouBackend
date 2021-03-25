<?php
include_once('../../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iCityId = isset($_REQUEST['iCityId']) ? $_REQUEST['iCityId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
// echo "<pre>"; print_r($_REQUEST);
//Start city deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iCityId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-city')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete city';
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iCityId != "") {
            $cityIds = $iCityId;
        } else {
            $cityIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE city SET eStatus = 'Deleted' WHERE iCityId IN (" . $cityIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "city.php?" . $parameters);
    exit;
}
//End city deleted
//Start Change single Status
if ($iCityId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-city')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of city';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE city SET eStatus = '" . $status . "' WHERE iCityId = '" . $iCityId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "city.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-city')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of city';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE city SET eStatus = '" . $statusVal . "' WHERE iCityId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "city.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>