<?php

include_once('../../common.php');
define("USER_PROFILE_MASTER", "user_profile_master");
if (!isset($generalobjRider)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjRider = new General_admin();
}
$generalobjRider->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iUserProfileMasterId = isset($_REQUEST['iUserProfileMasterId']) ? $_REQUEST['iUserProfileMasterId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
// echo "<pre>"; print_r($_REQUEST); die;
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iUserProfileMasterId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-user-profile')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete user profile';
    } else {
        //echo "<pre>";
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iUserProfileMasterId != "") {
            $profileIds = $iUserProfileMasterId;
        } else {
            $profileIds = $checkbox;
        }
        $sql = "SELECT * FROM organization WHERE eStatus != 'Deleted' AND iUserProfileMasterId IN (" . $profileIds . ")";
        $orgProfile = $obj->MySQLSelect($sql);
        //print_R($orgProfile);die;
        if (count($orgProfile) > 0) {
            $_SESSION['success'] = '2';
            $_SESSION['var_msg'] = 'This profile is already accosiated with the organization, kindly delete organization first.';
        } else {
            //Added By Hasmukh On 05-10-2018 For Solved Bug End
            if (SITE_TYPE != 'Demo') {
                $query = "UPDATE " . USER_PROFILE_MASTER . " SET eStatus = 'deleted' WHERE iUserProfileMasterId IN (" . $profileIds . ")";
                $obj->sql_query($query);
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
            } else {
                $_SESSION['success'] = '2';
            }
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "user_profile_master.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iUserProfileMasterId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-user-profile')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of user profile';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE " . USER_PROFILE_MASTER . " SET eStatus = '" . $status . "' WHERE iUserProfileMasterId = '" . $iUserProfileMasterId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "user_profile_master.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-user-profile')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of user profile';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE " . USER_PROFILE_MASTER . " SET eStatus = '" . $statusVal . "' WHERE iUserProfileMasterId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "user_profile_master.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>