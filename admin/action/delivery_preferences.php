<?php
include_once('../../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$iPreferenceId = isset($_REQUEST['iPreferenceId']) ? $_REQUEST['iPreferenceId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$delivery_preference_ids = isset($_REQUEST['delivery_preference_ids']) ? $_REQUEST['delivery_preference_ids'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//Start Change single Status
if ($iPreferenceId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-delivery-preference')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of '.$langage_lbl_admin['LBL_DELIVERY_PREF'];
    } 
    else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE delivery_preferences SET eStatus = '" . $status . "' WHERE iPreferenceId = '" . $iPreferenceId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "delivery_preferences.php?" . $parameters);
    exit;
}
//End Change single Status

if(count($delivery_preference_ids) > 0 && $statusVal != "")
{
    $delivery_preference_ids = implode(',', $delivery_preference_ids);

    if($statusVal == "Deleted")
    {
        if (!$userObj->hasPermission('delete-delivery-preference')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to change status of '.$langage_lbl_admin['LBL_DELIVERY_PREF'];
        } 
        else {
            if (SITE_TYPE != 'Demo') {
                $query = "UPDATE delivery_preferences SET is_deleted = '1' WHERE iPreferenceId IN (" . $delivery_preference_ids . ") AND eContactLess = 'No'";
                $obj->sql_query($query);
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
            } else {
                $_SESSION['success'] = 2;
            }
        }
    }
    else{
        if (!$userObj->hasPermission('update-status-delivery-preference')) {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'You do not have permission to change status of '.$langage_lbl_admin['LBL_DELIVERY_PREF'];
        } 
        else {
            if (SITE_TYPE != 'Demo') {
                $query = "UPDATE delivery_preferences SET eStatus = '" . $statusVal . "' WHERE iPreferenceId IN (" . $delivery_preference_ids . ")";
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
    }
    
    header("Location:" . $tconfig["tsite_url_main_admin"] . "delivery_preferences.php?" . $parameters);
    exit;
}

if($method == "delete" && $iPreferenceId != '')
{
    if (!$userObj->hasPermission('delete-delivery-preference')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete '.$langage_lbl_admin['LBL_DELIVERY_PREF'];
    } 
    else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE delivery_preferences SET is_deleted = '1' WHERE iPreferenceId = '" . $iPreferenceId . "' AND eContactLess = 'No'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "delivery_preferences.php?" . $parameters);
    exit;
}
?>