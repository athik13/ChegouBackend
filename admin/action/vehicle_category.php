<?php

include_once('../../common.php');
if (!isset($generalobjRider)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjRider = new General_admin();
}
$generalobjRider->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//echo "<pre>"; print_r($_REQUEST);die;
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iVehicleCategoryId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-service-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete record';
    } else {
        //Added By Hasmukh On 12-10-2018 For Solved Bug Start
        if ($iVehicleCategoryId != "") {
            $catIds = $iVehicleCategoryId;
        } else {
            $catIds = $checkbox;
        }
        //Added By Hasmukh On 12-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            /*$sql = "SELECT count(iVehicleCategoryId) as total_sub FROM vehicle_category WHERE iParentId = '" . $iVehicleCategoryId . "'";
            $data_cat = $obj->MySQLSelect($sql);
            if ($data_cat[0]['total_sub'] > 0) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = 'This category have sub categories so you can not delete this category. Please delete sub category than after delete this category.';
            } else {*/
                //$query = "DELETE FROM  vehicle_category WHERE iVehicleCategoryId IN (" . $catIds . ")";
                $query = "UPDATE vehicle_category SET eStatus ='Deleted' WHERE iVehicleCategoryId IN (" . $catIds . ")";

                $obj->sql_query($query);

                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
            /*}*/
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "vehicle_category.php?" . $parameters);
    exit;
}

//Start Change single Status
if ($iVehicleCategoryId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-service-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of record';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE vehicle_category SET eStatus = '" . $status . "' WHERE iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "vehicle_category.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-service-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of record';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE vehicle_category SET eStatus = '" . $statusVal . "' WHERE iVehicleCategoryId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "vehicle_category.php?" . $parameters);
    exit;
}
?>