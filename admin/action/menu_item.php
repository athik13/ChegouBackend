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
$iMenuItemId = isset($_REQUEST['iMenuItemId']) ? $_REQUEST['iMenuItemId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//Start Menu Items deleted
//print_R($_REQUEST);die;
if (($statusVal == 'Deleted' || $method == 'delete') && ($iMenuItemId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-item')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete food item';
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iMenuItemId != "") {
            $itemIds = $iMenuItemId;
        } else {
            $itemIds = $checkbox;
        }
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE `menu_items` SET `eStatus`='Deleted' WHERE iMenuItemId IN (" . $itemIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
            /* $sql = "SELECT * FROM menu_items WHERE iMenuItemId IN (" . $itemIds . ")";
              $db_oldData = $obj->MySQLSelect($sql);
              if (!empty($db_oldData)) {
              $iDisplayOrder = $db_oldData[0]['iDisplayOrder'];
              $iFoodMenuId = $db_oldData[0]['iFoodMenuId'];

              //$query = "DELETE FROM menu_items WHERE iMenuItemId = '" . $iMenuItemId . "'";
              $query = "UPDATE `menu_items` SET `eStatus`='Deleted' WHERE iMenuItemId = '" . $iMenuItemId . "'";
              $obj->sql_query($query);

              $query1 = "DELETE FROM menuitem_options WHERE iMenuItemId = '" . $iMenuItemId . "'";
              $obj->sql_query($query1); */

            //Update Display Order
            /* $sql = "SELECT * FROM menu_items where iFoodMenuId = '$iFoodMenuId' AND iDisplayOrder >= '$iDisplayOrder' AND `eStatus`!='Deleted' ORDER BY iDisplayOrder ASC";
              $db_orders = $obj->MySQLSelect($sql);

              if(!empty($db_orders)){
              $j = $iDisplayOrder;
              for($i=0;$i<count($db_orders);$i++){
              $query = "UPDATE menu_items SET iDisplayOrder = '$j' WHERE iMenuItemId = '".$db_orders[$i]['iMenuItemId']."'";
              $obj->sql_query($query);
              $j++;
              }
              } */

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "menu_item.php?" . $parameters);
    exit;
}
//End Menu Items deleted
//Start Change single Status
if ($iMenuItemId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-item')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of food item';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE menu_items SET eStatus = '" . $status . "' WHERE iMenuItemId = '" . $iMenuItemId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "menu_item.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-item')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of food item';
    } else {
        if (SITE_TYPE != 'Demo') {
            if ($statusVal == "Deleted") {
                //$query = "DELETE FROM menu_items WHERE iMenuItemId IN (" . $checkbox . ")";
                $query = "UPDATE `menu_items` SET eStatus = '" . $statusVal . "' WHERE iMenuItemId IN (" . $checkbox . ")";
                $obj->sql_query($query);
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
            } else {
                $query = "UPDATE menu_items SET eStatus = '" . $statusVal . "' WHERE iMenuItemId IN (" . $checkbox . ")";
                $obj->sql_query($query);
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "menu_item.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>