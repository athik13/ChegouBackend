<?php

include_once('../../common.php');


if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
// echo "<pre>"; print_r($_REQUEST);
//Start make deleted

if ($method == 'delete' && $id != '') {
	if(SITE_TYPE !='Demo'){
            $query = "UPDATE admin_permissions SET status = 'Deleted' WHERE id = '" . $id . "'";

            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];   
	}
	else{
            $_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."admin_permissions.php?".$parameters); exit;
}
//End make deleted

//Start Change single Status
if ($id != '' && $status != '') {
	if(SITE_TYPE !='Demo'){
            $query = "UPDATE admin_permissions SET status = '" . $status . "' WHERE id = '" . $id . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            if($status == 'Active') {
                   $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
            }else {
                   $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
            }
	}
	else{
        $_SESSION['success']=2;
	}
    header("Location:".$tconfig["tsite_url_main_admin"]."admin_permissions.php?".$parameters);
    exit;
}
//End Change single Status

//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") {
	if(SITE_TYPE !='Demo'){
		 $query = "UPDATE admin_permissions SET status = '" . $statusVal . "' WHERE id IN (" . $checkbox . ")";
		 $obj->sql_query($query);
		 $_SESSION['success'] = '1';
		 $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
	}
	else{
		$_SESSION['success']=2;
	}
        header("Location:".$tconfig["tsite_url_main_admin"]."admin_permissions.php?".$parameters);
        exit;
}
//End Change All Selected Status

//if ($id != '' && $status != '') {
//    if (SITE_TYPE != 'Demo') {
//        $query = "UPDATE admin_permissions SET status = '" . $status . "' WHERE id = '" . $id . "'";
//        $obj->sql_query($query);
//        $_SESSION['success'] = '1';
//        $_SESSION['var_msg'] = "Admin " . $status . " Successfully.";
//        header("Location:".$tconfig["tsite_url_main_admin"]."admin_permissions.php?".$parameters);
//        exit;
//    } else {
//        $_SESSION['success']=2;
//        header("Location:".$tconfig["tsite_url_main_admin"]."admin_permissions.php?".$parameters);
//        exit;
//    }
//}
?>