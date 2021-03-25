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
$doc_masterid = isset($_REQUEST['doc_masterid']) ? $_REQUEST['doc_masterid'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($doc_masterid != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-documents')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete document';
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($doc_masterid != "") {
            $docIds = $doc_masterid;
        } else {
            $docIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE document_master SET status = 'Deleted' WHERE doc_masterid IN (" . $docIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "document_master_list.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($doc_masterid != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-documents')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update status of document';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE document_master SET status = '" . $status . "' WHERE doc_masterid = '" . $doc_masterid . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "document_master_list.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-documents')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update status of document';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE document_master SET status = '" . $statusVal . "' WHERE doc_masterid IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "document_master_list.php?" . $parameters);
    exit;
}
//End Change All Selected Status
//if ($doc_masterid != '' && $status != '') {
//    if (SITE_TYPE != 'Demo') {
//        $query = "UPDATE document_master SET status = '" . $status . "' WHERE doc_masterid = '" . $doc_masterid . "'";
//        $obj->sql_query($query);
//        $_SESSION['success'] = '1';
//        $_SESSION['var_msg'] = "Document List " . $status . " Successfully.";
//        header("Location:".$tconfig["tsite_url_main_admin"]."document_master_list.php?".$parameters);
//        exit;
//    } else {
//        $_SESSION['success']=2;
//        header("Location:".$tconfig["tsite_url_main_admin"]."document_master_list.php?".$parameters);
//        exit;
//    }
//}
?>