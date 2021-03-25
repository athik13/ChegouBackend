<?php

include_once('../../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
////$generalobjAdmin->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iServiceId = isset($_REQUEST['iServiceId']) ? $_REQUEST['iServiceId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//Start make deleted
if ($method == 'delete' && $iServiceId != '') {
    /*    $checkRestaurant = "SELECT count(iCompanyId) as TotalRes FROM company_cuisine WHERE cuisineId = '" . $cuisineId . "'";
      $ResData=$obj->MySQLSelect($checkRestaurant); */
    if (!$userObj->hasPermission('delete-service-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete service category';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE service_categories SET eStatus = 'Deleted' WHERE iServiceId = '" . $iServiceId . "'";
            $obj->sql_query($query);
            removedCategoryData($iServiceId);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "service_category.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iServiceId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-service-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of service category';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE service_categories SET eStatus = '" . $status . "' WHERE iServiceId = '" . $iServiceId . "'";
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
    header("Location:" . $tconfig["tsite_url_main_admin"] . "service_category.php?" . $parameters);
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission(['update-status-service-category', 'delete-service-category'])) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of service category';
    } else {
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE service_categories SET eStatus = '" . $statusVal . "' WHERE iServiceId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $explodeId = explode(",", $checkbox);
            /*for ($i = 0; $i < count($explodeId); $i++) {
                removedCategoryData($explodeId[$i]);
            }*/
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "service_category.php?" . $parameters);
    exit;
}

//End Change All Selected Status
//Added By Hasmukh On 04-12-2018 For Removed Category All Data Start
function removedCategoryData($catId) {
    global $obj;
    //echo "<pre>";
    $companyCusineArr = $deleteTableArr = array();
    $cuisineIds = $companyIds = $foodMenuIds = $menuItemIds = "";
    $getCuisineIds = $obj->MySQLSelect("SELECT cuisineId FROM cuisine WHERE iServiceId='" . $catId . "'");
    for ($c = 0; $c < count($getCuisineIds); $c++) {
        $cuisineIds .= ",'" . $getCuisineIds[$c]['cuisineId'] . "'";
    }
    $deleteTableArr[] = array("table" => "service_categories", "field" => "iServiceId", "ids" => $catId);
    $getCompanyIds = $obj->MySQLSelect("SELECT iCompanyId FROM company WHERE iServiceId='" . $catId . "'");
    for ($i = 0; $i < count($getCompanyIds); $i++) {
        $companyIds .= ",'" . $getCompanyIds[$i]['iCompanyId'] . "'";
    }
    if ($companyIds != "") {
        $companyIds = trim($companyIds, ",");
        $deleteTableArr[] = array("table" => "company", "field" => "iCompanyId", "ids" => $companyIds);
        $getCompanyCuisineIds = $obj->MySQLSelect("SELECT ccId FROM company_cuisine WHERE iCompanyId IN($companyIds)");
        for ($co = 0; $co < count($getCompanyCuisineIds); $co++) {
            if (!in_array($getCompanyCuisineIds[$co]['ccId'], $companyCusineArr)) {
                $companyCusineArr[] = $getCompanyCuisineIds[$co]['ccId'];
            }
        }
        $getFoodMenu = $obj->MySQLSelect("SELECT iFoodMenuId FROM food_menu WHERE iCompanyId IN($companyIds)");
        for ($f = 0; $f < count($getFoodMenu); $f++) {
            $foodMenuIds .= ",'" . $getFoodMenu[$f]['iFoodMenuId'] . "'";
        }
        if ($foodMenuIds != "") {
            $foodMenuIds = trim($foodMenuIds, ",");
            $deleteTableArr[] = array("table" => "food_menu", "field" => "iFoodMenuId", "ids" => $foodMenuIds);
            $getMenuItems = $obj->MySQLSelect("SELECT iMenuItemId FROM menu_items WHERE iFoodMenuId IN($foodMenuIds)");
            for ($m = 0; $m < count($getMenuItems); $m++) {
                $menuItemIds .= ",'" . $getMenuItems[$m]['iMenuItemId'] . "'";
            }
            if ($menuItemIds != "") {
                $menuItemIds = trim($menuItemIds, ",");
                $deleteTableArr[] = array("table" => "menu_items", "field" => "iMenuItemId", "ids" => $menuItemIds);
            }
        }
    }
    if ($cuisineIds != "") {
        $cuisineIds = trim($cuisineIds, ",");
        $deleteTableArr[] = array("table" => "cuisine", "field" => "cuisineId", "ids" => $cuisineIds);
        $getCompanyCuisineIds = $obj->MySQLSelect("SELECT ccId FROM company_cuisine WHERE cuisineId IN ($cuisineIds)");
        for ($cc = 0; $cc < count($getCompanyCuisineIds); $cc++) {
            if (!in_array($getCompanyCuisineIds[$cc]['ccId'], $companyCusineArr)) {
                $companyCusineArr[] = $getCompanyCuisineIds[$cc]['ccId'];
            }
        }
    }
    if (count($companyCusineArr) > 0) {
        $companyCuisineIds = implode(",", $companyCusineArr);
        $deleteTableArr[] = array("table" => "company_cuisine", "field" => "ccId", "ids" => $companyCuisineIds);
    }
    for ($j = 0; $j < count($deleteTableArr); $j++) {
        $idsW = $deleteTableArr[$j]['ids'];
        //echo "DELETE FROM " . $deleteTableArr[$j]['table'] . " WHERE " . $deleteTableArr[$j]['field'] . " IN($idsW)<br>";
        $obj->sql_query("DELETE FROM ".$deleteTableArr[$j]['table']." WHERE ".$deleteTableArr[$j]['field']." IN($idsW)");
    }
}

//Added By Hasmukh On 04-12-2018 For Removed Category All Data End
?>