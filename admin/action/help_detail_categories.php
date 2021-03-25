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
$iHelpDetailCategoryId = isset($_REQUEST['iHelpDetailCategoryId']) ? $_REQUEST['iHelpDetailCategoryId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$iUniqueId = $_REQUEST['iUniqueId'];
$hdn_del_id = isset($_REQUEST['hdn_del_id2']) ? $_REQUEST['hdn_del_id2'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//Start help detail cat deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iUniqueId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-help-detail-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete help detail category';
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iUniqueId != "") {
            $uniqueIds = $iUniqueId;
        } else {
            $uniqueIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "DELETE FROM help_detail_categories WHERE iUniqueId IN (" . $uniqueIds . ")"; //die;
            //echo $query;die;
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
        header("Location:" . $tconfig["tsite_url_main_admin"] . "help_detail_categories.php?" . $parameters);
        exit;
    }
}
//End faq_categories deleted
//Start Change single Status
if ($iUniqueId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-help-detail-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of help detail category';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE help_detail_categories SET eStatus = '" . $status . "' WHERE iUniqueId = '" . $iUniqueId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "help_detail_categories.php?" . $parameters);
    echo "test";
    die;
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal == "Deleted") {
    if (!$userObj->hasPermission('delete-help-detail-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete help detail category';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "DELETE FROM help_detail_categories WHERE iUniqueId IN (" . $checkbox . ")"; //die;
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "help_detail_categories.php?" . $parameters);
    exit;
} else {
    if (!$userObj->hasPermission('update-status-help-detail-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of help detail category';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE help_detail_categories SET eStatus = '" . $statusVal . "' WHERE iUniqueId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "help_detail_categories.php?" . $parameters);
    exit;
}
?>