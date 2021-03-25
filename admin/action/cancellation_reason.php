<?php

include_once('../../common.php');
if (!isset($generalobjDriver)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjDriver = new General_admin();
}
$generalobjDriver->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iCancelReasonId = isset($_REQUEST['iCancelReasonId']) ? $_REQUEST['iCancelReasonId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
// echo "<pre>"; print_r($_REQUEST);exit;
// die;
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iCancelReasonId != '' || $checkbox != "")) {
    //Added By Hasmukh On 05-10-2018 For Solved Bug Start
    if (!$userObj->hasPermission('delete-cancel-reasons')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete cancel reason';
    } else {
        if ($iCancelReasonId != "") {
            $reasonIds = $iCancelReasonId;
        } else {
            $reasonIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE cancel_reason SET eStatus = 'Deleted' WHERE iCancelReasonId IN (" . $reasonIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "cancellation_reason.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iCancelReasonId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-cancel-reasons')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of cancel reason';
    } else {
        if (SITE_TYPE != 'Demo') {
            // echo "sa";exit;
            $query = "UPDATE cancel_reason SET eStatus = '" . $status . "' WHERE iCancelReasonId = '" . $iCancelReasonId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "cancellation_reason.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-cancel-reasons')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of cancel reason';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE cancel_reason SET eStatus = '" . $statusVal . "' WHERE iCancelReasonId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "cancellation_reason.php?" . $parameters);
    exit;
}
//End Change All Selected Status
//if ($iDriverId != '' && $status != '') {
//    if (SITE_TYPE != 'Demo') {
//        $query = "UPDATE register_driver SET eStatus = '" . $status . "' WHERE iDriverId = '" . $iDriverId . "'";
//        $obj->sql_query($query);
//        $_SESSION['success'] = '1';
//        $_SESSION['var_msg'] = "Driver " . $status . " Successfully.";
//        header("Location:".$tconfig["tsite_url_main_admin"]."driver.php?".$parameters);
//        exit;
//    } else {
//        $_SESSION['success']=2;
//        header("Location:".$tconfig["tsite_url_main_admin"]."driver.php?".$parameters);
//        exit;
//    }
//}
?>