<?php

include_once('../../common.php');
if (!isset($generalobjCompany)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjCompany = new General_admin();
}

$date = Date('Y-m-d');
$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';
//echo '<pre>';print_r($_REQUEST);die;
//$generalobjCompany->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//Start make deleted
$adminUrl = $tconfig["tsite_url_main_admin"];
//print_R($iCompanyId);die;
if ($statusVal != '' && $method == "autoaccept") {
    //echo "UPDATE company SET eAutoaccept = '" . $statusVal . "' WHERE iCompanyId IN (" . $iCompanyId . ")";die;
    $obj->sql_query("UPDATE company SET eAutoaccept = '" . $statusVal . "' WHERE iCompanyId IN (" . $iCompanyId . ")");
    if ($iCompanyId > 0) {
        $successtype = "2";
        $successMsg = $langage_lbl_admin["LBL_DISABLE_AUTO_ACCEPT_ORDER_TXT"];
        if ($statusVal == "Yes") {
            $successtype = "1";
            $successMsg = $langage_lbl_admin["LBL_AUTO_ACCEPT_ORDER_TXT"];
        }
        $_SESSION['success'] = $successtype;
        $_SESSION['var_msg'] = $successMsg;
    } else {
        $_SESSION['success'] = '2';
        $_SESSION['var_msg'] = $langage_lbl_admin["LBL_ERROR_OCCURED"];
    }
    $data['status'] = "1";
    echo json_encode($data);
    die;
}
if (($statusVal == 'Deleted' || $method == 'delete') && ($iCompanyId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-store')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete ' . strtolower($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']);
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iCompanyId != "") {
            $storeIds = $iCompanyId;
        } else {
            $storeIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $qur2 = "UPDATE company SET eStatus = 'Deleted' WHERE iCompanyId IN (" . $storeIds . ")";
            $res2 = $obj->sql_query($qur2);

            $storeIds = explode(",", $storeIds);
            for ($i = 0; $i < count($storeIds); $i++) {

                /* Insert status log on user_log table*/
                $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$storeIds[$i].", eUserType = 'store', dDate = '".$date."', eStatus = 'Deleted', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";
                $obj->sql_query($queryIn);
            }

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $adminUrl . "store.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iCompanyId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-store')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of ' . strtolower($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']);
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE company SET eStatus = '" . $status . "' WHERE iCompanyId = '" . $iCompanyId . "'";
            $obj->sql_query($query);

            /* Insert status log on user_log table*/
            $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$iCompanyId.", eUserType = 'store', dDate = '".$date."', eStatus = '".$status."', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";
            $obj->sql_query($queryIn);


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
    header("Location:" . $adminUrl . "store.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-store')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of ' . strtolower($langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']);
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE company SET eStatus = '" . $statusVal . "' WHERE iCompanyId IN (" . $checkbox . ")";
            $obj->sql_query($query);

            $checkbox = explode(",", $checkbox);
            for ($i = 0; $i < count($checkbox); $i++) {

                /* Insert status log on user_log table*/
                $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$checkbox[$i].", eUserType = 'store', dDate = '".$date."', eStatus = '".$statusVal."', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";
                $obj->sql_query($queryIn);
            }
            
            
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $adminUrl . "store.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>
