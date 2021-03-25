<?php

include_once('../../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iFoodMenuId = isset($_REQUEST['iFoodMenuId']) ? $_REQUEST['iFoodMenuId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

if($iFoodMenuId != ''){
    $check_items = "SELECT count(iMenuItemId) as FoodItems FROM  `menu_items` WHERE iFoodMenuId = '" . $iFoodMenuId . "' AND eStatus!='Deleted'";
    $total_items = $obj->MySQLSelect($check_items);

    $check_items1 = "SELECT count(iMenuItemId) as FoodItems FROM  `menu_items` WHERE iFoodMenuId = '" . $iFoodMenuId . "' AND eStatus='Active'";
    $total_items_active = $obj->MySQLSelect($check_items1);
}
//Start food_menu deleted
if ($method == 'delete' && $iFoodMenuId != '') {

    if(!$userObj->hasPermission('delete-item-categories')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete food cateogry';   
    }else{
    	if(SITE_TYPE !='Demo'){
            if($total_items[0]['FoodItems'] <= 0) {
                $sql = "SELECT * FROM `food_menu` WHERE iFoodMenuId = '".$iFoodMenuId."'";
                $db_oldData = $obj->MySQLSelect($sql);
                if(!empty($db_oldData)){
                    $iParentId = $db_oldData[0]['iParentId'];
                    $iDisplayOrder = $db_oldData[0]['iDisplayOrder'];
                    $iCompanyId = $db_oldData[0]['iCompanyId'];

                   // $query = "DELETE FROM food_menu WHERE iFoodMenuId = '".$iFoodMenuId."'";
                    $query = "UPDATE food_menu SET eStatus = 'Deleted' WHERE iFoodMenuId = '" . $iFoodMenuId . "'";
                    $obj->sql_query($query);

                    /* $sql = "SELECT * FROM `food_menu` where iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' AND `eStatus`!='Deleted' ORDER BY iDisplayOrder ASC";
                    $db_orders = $obj->MySQLSelect($sql);
                    
                    if(!empty($db_orders)){
                        $j = $iDisplayOrder;
                        for($i=0;$i<count($db_orders);$i++){
                            $query = "UPDATE food_menu SET iDisplayOrder = '$j' WHERE iFoodMenuId = '".$db_orders[$i]['iFoodMenuId']."'";
                            $obj->sql_query($query);
                            $j++;
                        }
                    }*/

                    $_SESSION['success'] = '1';
                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
                }
            } else {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = 'This category contains menu items. Please delete menu items to delete this category.';
            }
    	} else {
            $_SESSION['success'] = '2';
    	}
    }
	header("Location:".$tconfig["tsite_url_main_admin"]."food_menu.php?".$parameters); exit;
}
//End food_menu deleted

//Start Change single Status
if ($iFoodMenuId != '' && $status != '') {
    if(!$userObj->hasPermission('update-status-item-categories')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of food cateogry';   
    }else{
    	if(SITE_TYPE !='Demo') {

            /*if($status == 'Active' && $total_items_active[0]['FoodItems'] > 0) {*/
                if($status == 'Active'){
                    $query = "UPDATE food_menu SET eStatus = '" . $status . "' WHERE iFoodMenuId = '" . $iFoodMenuId . "'";
                    $obj->sql_query($query);
                    $_SESSION['success'] = '1';
                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
                }
            /*} else if ($status == 'Active' && $total_items_active[0]['FoodItems'] <= 0) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = 'This category does not have any active menu items. Please add or check menu items to Active this category.';
            }
            */

            if($status == 'Inactive' && $total_items_active[0]['FoodItems'] <= 0) {
                $query = "UPDATE food_menu SET eStatus = '" . $status . "' WHERE iFoodMenuId = '" . $iFoodMenuId . "'";
                $obj->sql_query($query);
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
            } else if($status == 'Inactive' && $total_items_active[0]['FoodItems'] > 0){
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = 'This category contains ative menu items. Please inactive menu items to inactive this category.';
            }

    	} else {
            $_SESSION['success']='2';
    	}
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."food_menu.php?".$parameters);
    exit;
}
//End Change single Status

//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") {
    if(!$userObj->hasPermission(['update-status-item-categories', 'delete-item-categories'])) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of food cateogry';   
    }else{
    	if(SITE_TYPE !='Demo'){
    	     $query = "UPDATE food_menu SET eStatus = '" . $statusVal . "' WHERE iFoodMenuId IN (" . $checkbox . ")";
    		 $obj->sql_query($query);
    		 $_SESSION['success'] = '1';
    		 $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    	}
    	else{
    		$_SESSION['success']=2;
    	}
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."food_menu.php?".$parameters);
    exit;
}
//End Change All Selected Status

?>