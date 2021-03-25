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
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iVehicleTypeId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-vehicle-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete vehicle type';
    } else {
        //Added By Hasmukh On 16-10-2018 For Solved Bug Start
        if ($iVehicleTypeId != "") {
            $typeIds = $iVehicleTypeId;
        } else {
            $typeIds = $checkbox;
        }
        //Added By Hasmukh On 16-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE vehicle_type SET eStatus ='Deleted' WHERE iVehicleTypeId IN (" . $typeIds . ")";
            //$query = "DELETE FROM vehicle_type WHERE iVehicleTypeId ='".$iVehicleTypeId."'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "store_vehicle_type.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iVehicleTypeId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-vehicle-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of vehicle type';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE vehicle_type SET eStatus = '" . $status . "' WHERE iVehicleTypeId = '" . $iVehicleTypeId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "store_vehicle_type.php?" . $parameters);
    exit;
}

//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-vehicle-type')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of vehicle type';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE vehicle_type SET eStatus = '" . $statusVal . "' WHERE iVehicleTypeId IN (" . $checkbox . ")";
            //$query = "DELETE FROM vehicle_type WHERE iVehicleTypeId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "store_vehicle_type.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>