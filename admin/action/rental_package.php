<?php
include_once('../../common.php');

if (!isset($generalobjRider)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjRider = new General_admin();
}
$generalobjRider->check_member_login();

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iRentalPackageId = isset($_REQUEST['iRentalPackageId']) ? $_REQUEST['iRentalPackageId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
//echo "<pre>"; print_r($_REQUEST); die;

//Start make deleted
/*if ($method == 'delete' && $iRentalPackageId != '') {
    if(!$userObj->hasPermission('delete-rental-packages')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You have not permission to delete Rental Package';   
    }else{
    	if(SITE_TYPE !='Demo'){
                $query = "DELETE FROM rental_package WHERE iRentalPackageId ='".$iRentalPackageId."'";
                $obj->sql_query($query);
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = 'Rental Package deleted successfully.';   
    	}
    	else{
                $_SESSION['success'] = '2';
    	}
    }
	header("Location:".$tconfig["tsite_url_main_admin"]."rental_package.php?id=".$id."&".$parameters); exit;
}*/
//End make deleted
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iRentalPackageId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-rental-packages')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete rental package';
    } else {
        if ($iRentalPackageId != "") {
            $typeIds = $iRentalPackageId;
        } else {
            $typeIds = $checkbox;
        }
        if (SITE_TYPE != 'Demo') {
            $query = "DELETE FROM rental_package WHERE iRentalPackageId IN (" . $typeIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."rental_package.php?id=".$id."&".$parameters); exit;
}
//End make deleted


?>