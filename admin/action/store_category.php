<?php
include_once('../../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$iCategoryId = isset($_REQUEST['iCategoryId']) ? $_REQUEST['iCategoryId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

//Start Change single Status
if ($iCategoryId != '' && $status != '') {
     if (!$userObj->hasPermission('update-status-store-categories')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of news';
    } else   
		{
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE store_categories SET eStatus = '" . $status . "' WHERE iCategoryId = '" . $iCategoryId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "store_category.php?" . $parameters);
    exit;
}
//End Change single Status
?>