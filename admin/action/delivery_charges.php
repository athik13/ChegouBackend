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
$iDeliveyChargeId = isset($_REQUEST['iDeliveyChargeId']) ? $_REQUEST['iDeliveyChargeId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//echo "<pre>"; print_r($_REQUEST); die;
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iDeliveyChargeId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-delivery-charges')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete delivery charges';
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iDeliveyChargeId != "") {
            $chargeIds = $iDeliveyChargeId;
        } else {
            $chargeIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            //$query = "UPDATE delivery_charges SET eStatus = 'deleted' WHERE iDeliveyChargeId IN (" . $chargeIds . ")";
            $query = "DELETE FROM `delivery_charges` WHERE iDeliveyChargeId IN (" . $chargeIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "delivery_charges.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iDeliveyChargeId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-delivery-charges')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of delivery charges';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE delivery_charges SET eStatus = '" . $status . "' WHERE iDeliveyChargeId = '" . $iDeliveyChargeId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "delivery_charges.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-delivery-charges')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of delivery charges';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE delivery_charges SET eStatus = '" . $statusVal . "' WHERE iDeliveyChargeId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "delivery_charges.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>