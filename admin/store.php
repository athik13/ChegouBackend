<?php

include_once('../common.php');

define("SERVICE_CATEGORIES", "service_categories");

define("CUISINE", "cuisine");

define("COMPANY", "company");

define("COMPANY_CUISINE", "company_cuisine");

define("FOOD_MENU", "food_menu");

define("MENU_ITEMS", "menu_items");

if (!isset($generalobjAdmin)) {

    require_once(TPATH_CLASS . "class.general_admin.php");

    $generalobjAdmin = new General_admin();

}



/* Use For Demo */

$storeIdArray = array('36');

/* Use For Demo */

////$generalobjAdmin->check_member_login();

if (!$userObj->hasPermission('view-store')) {

    $userObj->redirect();

}
$adminUrl = $tconfig["tsite_url_main_admin"];
if(isset($_REQUEST['eAutoaccept']) && trim($_REQUEST['eAutoaccept']) != ""){
    $chnageStatus = $_REQUEST['eAutoaccept'] == "Yes" ? 'No' : 'Yes';
    $iCompanyId = $_REQUEST['iCompanyId'];
    //echo "<pre>";print_r($_REQUEST);die;
    $Company_Update_id = $obj->sql_query("UPDATE company SET eAutoaccept = '" . $chnageStatus . "' WHERE iCompanyId IN (" . $iCompanyId . ")");
    if ($Company_Update_id == "1") {
        $successtype = "2";
        $successMsg = $langage_lbl_admin["LBL_DISABLE_AUTO_ACCEPT_ORDER_TXT"];
        if ($chnageStatus == "Yes") {
            $successtype = "1";
            $successMsg = $langage_lbl_admin["LBL_AUTO_ACCEPT_ORDER_TXT"];
        }
        $_SESSION['success'] = $successtype;
        $_SESSION['var_msg'] = $successMsg;
    } else {
        $_SESSION['success'] = '2';
        $_SESSION['var_msg'] = $langage_lbl_admin["LBL_ERROR_OCCURED"];
    }
    header("Location:" . $adminUrl . "store.php");
    exit;
}
if (isset($_POST['action']) && $_POST['action'] == "autoaccept") {
    //echo "<pre>";print_r($langage_lbl_admin);die;
    $iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : "";
    $eAutoaccept = isset($_POST['eAutoaccept']) ? $_POST['eAutoaccept'] : "No";
    $sql = "SELECT iOrderId FROM orders WHERE iCompanyId = '$iCompanyId' And iStatusCode='1'";
    $totalData = $obj->MySQLSelect($sql);
    $where = " iCompanyId = '$iCompanyId'";
    $Data_update_Companies['eAutoaccept'] = $eAutoaccept;
    $Company_Update_id = $obj->MySQLQueryPerform("company", $Data_update_Companies, 'update', $where);
    if ($Company_Update_id == "1") {
        $successtype = "2";
        $successMsg = $langage_lbl_admin["LBL_DISABLE_AUTO_ACCEPT_ORDER_TXT"];
        if ($eAutoaccept == "Yes") {
            $successtype = "1";
            $successMsg = $langage_lbl_admin["LBL_AUTO_ACCEPT_ORDER_TXT"];
        }
        $_SESSION['success'] = $successtype;
        $_SESSION['var_msg'] = $successMsg;
    } else {
        $_SESSION['success'] = '2';
        $_SESSION['var_msg'] = $langage_lbl_admin["LBL_ERROR_OCCURED"];
    }
    header("Location:" . $adminUrl . "store.php");
    exit;
}
$script = 'DeliverAllStore';

$eSystem = " AND  c.eSystem ='DeliverAll'";



//Start Sorting

$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;

$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$ord = ' ORDER BY c.iCompanyId DESC';

if ($sortby == 1) {

    if ($order == 0)

        $ord = " ORDER BY c.vCompany ASC";

    else

        $ord = " ORDER BY c.vCompany DESC";

}



if ($sortby == 2) {

    if ($order == 0)

        $ord = " ORDER BY c.vEmail ASC";

    else

        $ord = " ORDER BY c.vEmail DESC";

}



if ($sortby == 3) {

    if ($order == 0)

        $ord = " ORDER BY `count` ASC";

    else

        $ord = " ORDER BY `count` DESC";

}



if ($sortby == 4) {

    if ($order == 0)

        $ord = " ORDER BY c.eStatus ASC";

    else

        $ord = " ORDER BY c.eStatus DESC";

}

//End Sorting



$cmp_ssql = "";

// if (SITE_TYPE == 'Demo') {

// $cmp_ssql = " And c.tRegistrationDate > '" . WEEK_DATE . "'";

// }

$cmp_ssql = "";

if (SITE_TYPE == 'Demo') {

    $cmp_ssql = " And c.tRegistrationDate > '" . WEEK_DATE . "'";

}

// Start Search Parameters

$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";

$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

$select_cat = isset($_REQUEST['selectcategory']) ? stripslashes($_REQUEST['selectcategory']) : "";

$select_store_cat = isset($_REQUEST['selectstorecategory']) ? stripslashes($_REQUEST['selectstorecategory']) : "";

$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";



if (isset($_POST['copystore'])) {

    $companyId = $_POST['storeId'];

    $copyStores = $_POST['store_sel'];

    //echo "<pre>";print_r($_POST);die;

    if (count($copyStores) > 0 && $companyId > 0) {

        $getStoreFoodMenu = $obj->MySQLSelect("SELECT * FROM " . FOOD_MENU . " WHERE iCompanyId='" . $companyId . "' AND eStatus!='eStatus'");

        for ($m = 0; $m < count($getStoreFoodMenu); $m++) {

            $iFoodMenuId = $getStoreFoodMenu[$m]['iFoodMenuId'];

            $vMenu_EN = $getStoreFoodMenu[$m]['vMenu_EN'];

            $getMenuItems = $obj->MySQLSelect("SELECT * FROM " . MENU_ITEMS . " WHERE iFoodMenuId='" . $iFoodMenuId . "' AND eStatus!='eStatus'");

            for ($s = 0; $s < count($copyStores); $s++) {

                $storeId = $copyStores[$s];

                if ($companyId != $storeId) {

                    //Start For Copy Food Menu and Menu Item Data

                    $chekc_food = $obj->MySQLSelect("SELECT iFoodMenuId FROM " . FOOD_MENU . " WHERE iCompanyId='" . $storeId . "' AND vMenu_EN='" . $vMenu_EN . "'");

                    if (count($chekc_food) > 0) { // Update Exists Food menu Data

                        for ($f = 0; $f < count($chekc_food); $f++) {

                            $oldFoodId = $chekc_food[$f]['iFoodMenuId'];

                            $updateFields = "";

                            unset($getStoreFoodMenu[$m]['iFoodMenuId']);

                            foreach ($getStoreFoodMenu[$m] as $key1 => $val1) {

                                if ($key1 == "iCompanyId") {

                                    $val1 = $storeId;

                                }

                                $updateFields .= ",`$key1`='" . $obj->cleanQuery(stripslashes($val1)) . "'";

                            }

                            if ($updateFields != "") {

                                $updateFields = trim($updateFields, ",");

                                $updateQuery = "UPDATE  `" . FOOD_MENU . "` SET $updateFields WHERE iFoodMenuId='" . $oldFoodId . "'";

                                $obj->sql_query($updateQuery);

                            }

                            for ($mi = 0; $mi < count($getMenuItems); $mi++) {

                                $menuItemName = $getMenuItems[$mi]['vItemType_EN'];

                                $chekc_item = $obj->MySQLSelect("SELECT iMenuItemId FROM " . MENU_ITEMS . " WHERE iFoodMenuId='" . $oldFoodId . "' AND vItemType_EN='" . $menuItemName . "'");

                                $updateFields1 = "";

                                unset($getMenuItems[$mi]['iMenuItemId']);

                                foreach ($getMenuItems[$mi] as $key2 => $val2) {

                                    if ($key2 == "iFoodMenuId") {

                                        $val2 = $oldFoodId;

                                    }

                                    $updateFields1 .= ",`$key2`='" . $obj->cleanQuery(stripslashes($val2)) . "'";

                                }

                                if ($updateFields1 != "") {

                                    $updateFields1 = trim($updateFields1, ",");

                                    if (count($chekc_item) > 0) {

                                        for ($mr = 0; $mr < count($chekc_item); $mr++) {

                                            $food_ItemId = $chekc_item[$mr]['iMenuItemId'];

                                            $updateQuery1 = "UPDATE  `" . MENU_ITEMS . "` SET $updateFields1 WHERE iMenuItemId='" . $food_ItemId . "'";

                                            $obj->sql_query($updateQuery1);

                                        }

                                    } else {

                                        $updateQuery1 = "INSERT INTO  `" . MENU_ITEMS . "` SET $updateFields1";

                                        $obj->sql_query($updateQuery1);

                                        $food_ItemId = $obj->GetInsertId();

                                    }

                                }

                            }

                        }

                    } else { // Insert New Food menu Data

                        $updateFields = "";

                        unset($getStoreFoodMenu[$m]['iFoodMenuId']);

                        foreach ($getStoreFoodMenu[$m] as $key1 => $val1) {

                            if ($key1 == "iCompanyId") {

                                $val1 = $storeId;

                            }

                            $updateFields .= ",`$key1`='" . $obj->cleanQuery(stripslashes($val1)) . "'";

                        }

                        if ($updateFields != "") {

                            $updateFields = trim($updateFields, ",");

                            $updateQuery = "INSERT INTO  `" . FOOD_MENU . "` SET $updateFields";

                            $obj->sql_query($updateQuery);

                            $company_foodId = $obj->GetInsertId();

                            if ($company_foodId > 0) {

                                for ($mi = 0; $mi < count($getMenuItems); $mi++) {

                                    $menuItemName = $getMenuItems[$mi]['vItemType_EN'];

                                    $updateFields1 = "";

                                    unset($getMenuItems[$mi]['iMenuItemId']);

                                    foreach ($getMenuItems[$mi] as $key2 => $val2) {

                                        if ($key2 == "iFoodMenuId") {

                                            $val2 = $company_foodId;

                                        }

                                        $updateFields1 .= ",`$key2`='" . $obj->cleanQuery(stripslashes($val2)) . "'";

                                    }

                                    if ($updateFields1 != "") {

                                        $updateFields1 = trim($updateFields1, ",");

                                        $updateQuery1 = "INSERT INTO  `" . MENU_ITEMS . "` SET $updateFields1";

                                        $obj->sql_query($updateQuery1);

                                        $food_ItemId = $obj->GetInsertId();

                                    }

                                }

                            }

                        }

                    }

                    //End For Copy Food Menu and Menu Item Data

                    //Start For Copy Company Cuisine Data

                    $getCompanyCuisine = $obj->MySQLSelect("SELECT * FROM " . COMPANY_CUISINE . " WHERE iCompanyId='" . $companyId . "'");

                    $existCompanyCuisine = $obj->MySQLSelect("SELECT * FROM " . COMPANY_CUISINE . " WHERE iCompanyId='" . $storeId . "'");

                    $cCuisinePkArr = array();

                    if (count($existCompanyCuisine) > 0) {

                        for ($ec = 0; $ec < count($existCompanyCuisine); $ec++) {

                            $cCuisinePkArr[] = $existCompanyCuisine[$ec]['ccId'];

                        }

                    }

                    for ($c = 0; $c < count($getCompanyCuisine); $c++) {

                        $cCuisineId = $getCompanyCuisine[$c]['cuisineId'];

                        $cuisinePkId = $getCompanyCuisine[$c]['ccId'];

                        if (!in_array($cuisinePkId, $cCuisinePkArr)) {

                            //print_r($getCompanyCuisine[$c]);die;

                            $insertFields = "";

                            unset($getCompanyCuisine[$c]['ccId']);

                            foreach ($getCompanyCuisine[$c] as $key3 => $val3) {

                                if ($key3 == "iCompanyId") {

                                    $val3 = $storeId;

                                }

                                $insertFields .= ",`$key3`='" . $obj->cleanQuery(stripslashes($val3)) . "'";

                            }

                            //print_r($checkCompanyCuisine);die;

                            if ($insertFields != "") {

                                $insertFields = trim($insertFields, ",");

                                $checkCompanyCuisine = $obj->MySQLSelect("SELECT ccId FROM " . COMPANY_CUISINE . " WHERE iCompanyId='" . $storeId . "' AND cuisineId='" . $cCuisineId . "' ORDER BY ccId ASC");

                                if (count($checkCompanyCuisine) > 0) {

                                    for ($cu = 0; $cu < count($checkCompanyCuisine); $cu++) {

                                        $copmCId = $checkCompanyCuisine[$cu]['ccId'];

                                        $wherecId = "ccId='" . $copmCId . "'";

                                        if ($cu == 0) {

                                            $insertQuery = "UPDATE `" . COMPANY_CUISINE . "` SET $insertFields WHERE $wherecId";

                                            $obj->sql_query($insertQuery);

                                        } else {

                                            $deletequery = "DELETE FROM `" . COMPANY_CUISINE . "` WHERE $wherecId";

                                            $obj->sql_query($deletequery);

                                        }

                                    }

                                } else {

                                    $insertQuery = "INSERT INTO  `" . COMPANY_CUISINE . "` SET $insertFields";

                                    $obj->sql_query($insertQuery);

                                    $company_cuisineId = $obj->GetInsertId();

                                }

                            }

                        }

                    }

                    //End For Copy Company Cuisine Data

                }

            }

        }

    } else {

        

    }

}



$ssql = '';
$service_sql = '';

if ($keyword != '') {

    $keyword_new = $keyword;

    $chracters = array("(", "+", ")");

    $removespacekeyword = preg_replace('/\s+/', '', $keyword);

    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

    if (is_numeric($keyword_new)) {

        $keyword_new = $keyword_new;

    } else {

        $keyword_new = $keyword;

    }

    if ($option != '') {

        $option_new = $option;

        if ($option == 'MobileNumber') {

            $option_new = "CONCAT(c.vCode,'',c.vPhone)";

        }

        if ($eStatus != '') {

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
            }
            else{
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
            }

        }if ($select_cat != "") {


            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }
            else{
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }

        } if ($select_store_cat != "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";

            }
            else{
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0 ";
            }

        } if ($select_cat != "" && $eStatus != '') {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }
            else{
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'  ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }

        }if ($select_cat != "" && $eStatus != '' && $select_store_cat != "") {

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
            }
            else{
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
            }

        } else {

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";

            }
            else{
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'";
            }
        }

    } else {



        if ($eStatus == '' && $select_cat != "" && $keyword_new != "" && $select_store_cat == "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "')) ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }

        } else if ($eStatus == '' && $select_cat != "" && $keyword_new != "" && $select_store_cat != "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "'))  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'))  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }

        } else if ($eStatus != '' && $select_cat != "" && $select_store_cat == "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }

        } else if ($eStatus != '' && $select_cat != "" && $select_store_cat != "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }

        } else if ($eStatus != '' && $select_store_cat == "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
            }

        } else if ($eStatus != '' && $select_store_cat != "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";

            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
            }

        } else if ($select_cat != "" && $select_store_cat == "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' ";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }

        } else if ($select_cat != "" && $select_store_cat != "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
                $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";
            }

        } else if ($select_store_cat != "") {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";

            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')) AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
            }

        } else {

            

            if (SITE_TYPE == 'Demo') {

                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . $generalobjAdmin->clean($keyword_new) . "'))";

            }
            else{
                $ssql .= " AND (c.vCompany LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR c.vEmail LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%'))";
            }

        }

    }

} else if ($eStatus != '' && $select_cat != "" && $keyword == '' && $select_store_cat == "") {

    $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' ";
    $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

} else if ($eStatus != '' && $select_cat != "" && $keyword == '' && $select_store_cat != "") {

    $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
    $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

} else if ($eStatus != '' && $keyword == '' && $select_cat == "" && $select_store_cat == "") {

    $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";

} else if ($eStatus != '' && $keyword == '' && $select_cat == "" && $select_store_cat != "") {

    $ssql .= " AND c.eStatus = '" . $generalobjAdmin->clean($eStatus) . "' AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";

} else if ($eStatus == '' && $keyword == '' && $select_cat != "" && $select_store_cat == "") {

    $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

} else if ($eStatus == '' && $keyword == '' && $select_cat != "" && $select_store_cat != "") {

    $ssql .= "  AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";
    $service_sql .= " AND sc.iServiceId = '" . $generalobjAdmin->clean($select_cat) . "' ";

} else if ($eStatus == '' && $keyword == '' && $select_cat == "" && $select_store_cat != "") {

    $ssql .= " AND find_in_set('" . $select_store_cat . "', (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId)) != 0";

}


// $ssql .= " AND sc.iServiceId IN(" . $enablesevicescategory . ")";
$cnt_serv_cat = count(explode(',',$enablesevicescategory));
// End Search Parameters

//Pagination Start

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

if (!empty($eStatus)) {

    $eStatus_sql = "";

} else {

    $eStatus_sql = " AND c.eStatus != 'Deleted'";

}

$isStoreCategoriesEnable = isStoreCategoriesEnable();

$checkSystemStoreSelection = 0;
$checkSystemStoreSelection = checkSystemStoreSelection();

if($checkSystemStoreSelection==0) {
    $sql = "SELECT c.iCompanyId,(SELECT sc.vServiceName_" . $default_lang . " FROM service_categories sc WHERE c.iServiceId = sc.iServiceId AND sc.eStatus = 'Active' AND sc.iServiceId IN (" . $enablesevicescategory . ") $service_sql) as servicename FROM company AS c left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active' $eSystem $eStatus_sql $ssql $cmp_ssql HAVING servicename IS NOT NULL ";

} else {
    $sql = "SELECT c.iCompanyId,(SELECT sc.vServiceName_" . $default_lang . " FROM service_categories sc WHERE c.iServiceId = sc.iServiceId AND sc.eStatus = 'Active' AND sc.iServiceId IN (" . $enablesevicescategory . ") $service_sql) as servicename FROM company AS c left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active' $eSystem $eStatus_sql $ssql $cmp_ssql HAVING servicename IS NOT NULL GROUP BY c.iServiceId";

}
$totalData = $obj->MySQLSelect($sql);
$total_results = count($totalData);
//$total_results = $totalData[0]['Total'];

$catIds = explode(",", $generalobj->getCurrentActiveServiceCategoriesIds()); // Added By HJ On 06-02-2020 For Solved 141 Mantis Issue #3321

$total_pages = ceil($total_results / $per_page); //total pages we going to have

$show_page = 1;

$start = 0;

$end = $per_page;

//-------------if page is setcheck------------------//

if (isset($_GET['page'])) {

    $show_page = $_GET['page'];             //it will telles the current page

    if ($show_page > 0 && $show_page <= $total_pages) {

        $start = ($show_page - 1) * $per_page;

        $end = $start + $per_page;

    }

}

// display pagination

$page = isset($_GET['page']) ? intval($_GET['page']) : 0;

$tpages = $total_pages;

if ($page <= 0)

    $page = 1;

//Pagination End

if (!empty($eStatus)) {

    $equery = "";

} else {

    $equery = " AND  c.eStatus != 'Deleted'";

}


/*if($checkSystemStoreSelection==0) {

$sql = "SELECT c.eAutoaccept,c.iCompanyId, c.vCompany, c.vEmail, c.vCode,c.vPhone, c.eStatus,c.iServiceId, c.tRegistrationDate , sc.vServiceName_" . $default_lang . " as servicename ,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId AND eStatus != 'Deleted') as foodcatCount, (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId) as storecats FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active'  $eSystem $equery $ssql $cmp_ssql $ord LIMIT $start, $per_page";
} else {
    $sql = "SELECT c.eAutoaccept,c.iCompanyId, c.vCompany, c.vEmail, c.vCode,c.vPhone, c.eStatus,c.iServiceId, c.tRegistrationDate , sc.vServiceName_" . $default_lang . " as servicename ,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId AND eStatus != 'Deleted') as foodcatCount, (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId) as storecats FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active' $eSystem $equery $ssql $cmp_ssql GROUP BY c.iServiceId $ord";
}*/

if($checkSystemStoreSelection==0) {

    $sql = "SELECT c.eAutoaccept,c.iCompanyId, c.vCompany, c.vEmail, c.vCode,c.vPhone, c.eStatus,c.iServiceId, c.tRegistrationDate , (SELECT sc.vServiceName_" . $default_lang . " FROM service_categories sc WHERE c.iServiceId = sc.iServiceId AND sc.eStatus = 'Active' AND sc.iServiceId IN (" . $enablesevicescategory . ") $service_sql) as servicename,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId AND eStatus != 'Deleted') as foodcatCount, (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId) as storecats FROM company AS c WHERE 1 = 1 $eSystem $equery $ssql $cmp_ssql HAVING servicename IS NOT NULL $ord LIMIT $start, $per_page";
} else {
    $sql = "SELECT c.eAutoaccept,c.iCompanyId, c.vCompany, c.vEmail, c.vCode,c.vPhone, c.eStatus,c.iServiceId, c.tRegistrationDate , (SELECT sc.vServiceName_" . $default_lang . " FROM service_categories sc WHERE c.iServiceId = sc.iServiceId AND sc.eStatus = 'Active' AND sc.iServiceId IN (" . $enablesevicescategory . ") $service_sql) as servicename,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId AND eStatus != 'Deleted') as foodcatCount, (SELECT GROUP_CONCAT(iCategoryId SEPARATOR ',') FROM store_category_tags WHERE iCompanyId = c.iCompanyId) as storecats FROM company AS c WHERE 1 = 1 $eSystem $equery $ssql $cmp_ssql HAVING servicename IS NOT NULL GROUP BY c.iServiceId $ord";
}

// echo $sql; exit;
$data_drv = $obj->MySQLSelect($sql);


$endRecord = count($data_drv);



$getAllStore = $obj->MySQLSelect("SELECT c.iCompanyId, c.vCompany FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active' $eSystem $equery");

$catdata = serviceCategories;

$service_cat_data = json_decode($catdata, true);

foreach ($service_cat_data as $k => $val) {

    $iServiceIdArr[] = $val['iServiceId'];

}

$serviceIds = implode(",", $iServiceIdArr);



$sql1 = "SELECT doc_masterid as total FROM `document_master` WHERE `doc_usertype` ='store' AND status = 'Active'";

$doc_count_query = $obj->MySQLSelect($sql1);

$doc_count = count($doc_count_query);



 $sql2 = "SELECT iCategoryId,tCategoryName FROM `store_categories` WHERE eType = 'manual' AND iServiceId IN (".$serviceIds.") AND eStatus = 'Active'";

$db_store_categories = $obj->MySQLSelect($sql2);



$var_filter = "";

foreach ($_REQUEST as $key => $val) {

    if ($key != "tpages" && $key != 'page')

        $var_filter .= "&$key=" . stripslashes($val);

}



$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;




$isStoreDriver = isStoreDriverAvailable(); // Added By HJ On 20-03-2020 For Check Manage Driver By Store Enable

$dri_ssql = "";

$dri_ssql .= " AND (vEmail != '' OR vPhone != '')";



if (SITE_TYPE == 'Demo') {

    $dri_ssql .= " And tRegistrationDate > '" . WEEK_DATE . "'";

}

$storeDriverCountArr = array();

if ($isStoreDriver > 0) {

    $getDrivers = $obj->MySQLSelect("SELECT count(iDriverId) As driverCount,iCompanyId FROM register_driver WHERE iCompanyId > 0 AND eStatus != 'Deleted' $dri_ssql GROUP BY iCompanyId");

    for($h=0;$h<count($getDrivers);$h++){

        $storeDriverCountArr[$getDrivers[$h]['iCompanyId']] = $getDrivers[$h]['driverCount'];

    }

    //echo "<pre>";print_r($storeDriverCountArr);die;

}
$become_restaurant = 'Store';
if(strtoupper(DELIVERALL) == "YES") {
    if (count($iServiceIdArr) == 1 && $iServiceIdArr[0]==1) {
        $become_restaurant = $langage_lbl_admin['LBL_RESTAURANT_TXT'];
    } else {
        $become_restaurant = $langage_lbl_admin['LBL_STORE'];
    }
}
$sel_store_cat_txt = $langage_lbl_admin['LBL_SELECT_TXT']." ".$become_restaurant." ".$langage_lbl_admin['LBL_CATEGORY_FRONT'];
if(count($service_cat_data) == 1)
{
    $select_cat = $service_cat_data[0]['iServiceId'];
}
?>

<!DOCTYPE html>

<html lang="en">

    <!-- BEGIN HEAD-->

    <head>

        <meta charset="UTF-8" />

        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></title>

        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <?php include_once('global_files.php'); ?>

    </head>

    <!-- END  HEAD-->

    <!-- BEGIN BODY-->

    <body class="padTop53 " >

        <!-- Main LOading -->

        <!-- MAIN WRAPPER -->

        <div id="wrap">

            <?php include_once('header.php'); ?>

            <style>

                .multiselect {

                    width: 533px !important;

                }

            </style>

            <?php include_once('left_menu.php'); ?>

            <!--PAGE CONTENT -->

            <div id="content">

                <div class="inner">

                    <div id="add-hide-show-div">

                        <div class="row">

                            <div class="col-lg-12">

                                <h2><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></h2>

                            </div>

                        </div>

                        <hr />

                    </div>

                    <?php include('valid_msg.php'); ?>
                    <? if($checkSystemStoreSelection==0) {?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">

                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">

                            <tbody>

                                <tr>

                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>

                                    <td width="10%" class="padding-right10">

                                        <select name="option" id="option" class="form-control">

                                            <option value="">All</option>

                                            <option  value="c.vCompany" <?php

                                            if ($option == "c.vCompany") {

                                                echo "selected";

                                            }

                                            ?> >Name</option>

                                            <option value="c.vEmail" <?php

                                            if ($option == 'c.vEmail') {

                                                echo "selected";

                                            }

                                            ?> >E-mail</option>

                                            <option value="MobileNumber" <?php

                                            if ($option == 'MobileNumber') {

                                                echo "selected";

                                            }

                                            ?> >Mobile</option>

                                        </select>

                                    </td>

                                    <td width="145px" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?= $keyword; ?>"  class="form-control" /></td>

                                    <td width="140px" class="estatus_options" id="eStatus_options" >

                                        <select name="eStatus" id="estatus_value" class="form-control">

                                            <option value="" >Select Status</option>

                                            <option value='Active' <?php

                                            if ($eStatus == 'Active') {

                                                echo "selected";

                                            }

                                            ?> >Active</option>

                                            <option value="Inactive" <?php

                                            if ($eStatus == 'Inactive') {

                                                echo "selected";

                                            }

                                            ?> >Inactive</option>

                                            <option value="Deleted" <?php

                                            if ($eStatus == 'Deleted') {

                                                echo "selected";

                                            }

                                            ?> >Delete</option>

                                        </select>

                                    </td>

                                    <?php if (count($service_cat_data) > 1) { ?>

                                        <td width="155px" class="estatus_options" id="ecategory_options" >

                                            <select name="selectcategory" id="selectcategory" class="form-control"  onChange="getcategories(this.value);">

                                                <option value="">Select Store Type</option>

                                                <?php foreach ($service_cat_data as $servicedata) { ?>

                                                    <option value="<?= $servicedata['iServiceId'] ?>" <?php

                                                    if ($select_cat == $servicedata['iServiceId']) {

                                                        echo "selected";

                                                    }

                                                    ?>> <?= $servicedata['vServiceName']; ?></option>

                                                        <?php } ?>

                                            </select>

                                        </td>

                                    <?php } ?>

                                    <?php if($isStoreCategoriesEnable==1) { ?>

                                        <td width="200px" class="estatus_options">

                                            <select name="selectstorecategory" id="js-store-category-multiple" class="form-control" >

                                                <option value="" ><?= $sel_store_cat_txt ?></option>

                                                <?php

                                                foreach ($db_store_categories as $store_category) {

                                                    $category_name = json_decode($store_category['tCategoryName'], true);

                                                    ?>

                                                    <option value="<?= $store_category['iCategoryId'] ?>" <?php

                                                    if ($select_store_cat == $store_category['iCategoryId']) {

                                                        echo "selected";

                                                    }

                                                    ?>><?= $category_name['tCategoryName_' . $default_lang] ?></option>

                                                        <?php } ?>

                                            </select>

                                        </td>

                                    <?php } ?>

                                    <td width="170px">

                                        <button type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search">Search</button>

                                        <button type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'store.php'">Reset</button>

                                    </td>

                                    <?php if ($userObj->hasPermission('create-store')) { ?>

                                        <td width="15%"><a class="add-btn" href="store_action.php" style="text-align: center;">Add <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></a></td>

                                    <?php } ?>

                                </tr>

                            </tbody>

                        </table>

                    </form>
                   <?php } ?>
                    <div class="table-list">

                        <div class="row">

                            <div class="col-lg-12">

                                <? if($checkSystemStoreSelection==0) {?>
                                <div class="admin-nir-export">

                                    <div class="changeStatus col-lg-12 option-box-left">

                                        <span class="col-lg-2 new-select001">

                                            <?php if ($userObj->hasPermission(['update-status-store', 'delete-store'])) { ?>

                                                <select name="changeStatus" id="changeStatus" class="form-control" onChange="status_check(this.value);">

                                                    <option value="" >Select Action</option>

                                                    <?php if ($userObj->hasPermission('update-status-store')) { ?>

                                                        <option value='Active' <?php

                                                        if ($option == 'Active') {

                                                            echo "selected";

                                                        }

                                                        ?> >Activate</option>

                                                        <option value="Inactive" <?php

                                                        if ($option == 'Inactive') {

                                                            echo "selected";

                                                        }

                                                        ?> >Deactivate</option>

                                                            <?php } ?>

                                                            <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-store')) { ?>

                                                        <option value="Deleted" <?php

                                                        if ($option == 'Delete') {

                                                            echo "selected";

                                                        }

                                                        ?> >Delete</option>

                                                            <?php } ?>

                                                </select>

                                            <?php } ?>

                                        </span>

                                    </div>

                                    <?php if (!empty($data_drv)) { ?>

                                        <div class="panel-heading">

                                            <form name="_export_form" id="_export_form" method="post" >

                                                <button type="button" onClick="showExportTypes('store')" >Export</button>

                                            </form>

                                        </div>

                                    <?php } ?>

                                </div>
                                <?php } ?>
                                <? if($checkSystemStoreSelection==1) {?>
                                <div class="admin-nir-export">
                                    <?php if ($userObj->hasPermission('create-store') && $total_results == 0) { ?>
                                        <div><a class="add-btn" href="store_action.php" style="text-align: center;">Add <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></a></div>
                                    <?php } ?>
                                    <?php if (!empty($data_drv)) { ?>
                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <button type="button" onClick="showExportTypes('store')" >Export</button>
                                            </form>
                                        </div>
                                    <?php } ?>
                                </div>
                                <? } ?>

                                <div style="clear:both;"></div>

                                <div class="table-responsive">

                                    <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">

                                        <table class="table table-striped table-bordered table-hover">

                                            <thead>

                                                <tr>

                                                    <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>

                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php

                                                        if ($sortby == '1') {

                                                            echo $order;

                                                        } else {

                                                            ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Name <?php

                                                                           if ($sortby == 1) {

                                                                               if ($order == 0) {

                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php

                                                                }

                                                            } else {

                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(2,<?php

                                                        if ($sortby == '2') {

                                                            echo $order;

                                                        } else {

                                                            ?>0<?php } ?>)">Email <?php

                                                                           if ($sortby == 2) {

                                                                               if ($order == 0) {

                                                                                   ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php

                                                                }

                                                            } else {

                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                            <?php if (count($service_cat_data) > 1) { ?>

                                                        <th width="10%">Service Category</th>

                                                    <?php } ?>

                                                    <?php if ($isStoreDriver > 0) { ?>

                                                        <th width="5%" style="text-align:center;"><?php echo $langage_lbl_admin['LBL_DASHBOARD_DRIVERS_ADMIN']; ?></th>

                                                    <?php } ?>

                                                    <th width="5%" style="text-align:center;">Item Categories</th>

                                                    <?php if($isStoreCategoriesEnable==1) { ?><th width="8%" style="text-align:center;"><?= $become_restaurant." ".$langage_lbl_admin['LBL_CATEGORY_FRONT'] ?></th><? } ?>

                                                    <th width="15%">Mobile</th>

                                                    <th width="12%">Registration Date</th>

                                                    <th width="8%" class='align-center'>View/Edit Documents</th>

                                                    <?php if ($ENABLE_AUTO_ACCEPT_STORE_ORDER == "Yes") { ?>

                                                        <th width="15%">Auto Accept</th>

                                                    <?php } ?>

                                                    <th width="6%" class='align-center' style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php

                                                        if ($sortby == '4') {

                                                            echo $order;

                                                        } else {

                                                            ?>0<?php } ?>)">Status <?php                                                                                                                          if ($sortby == 4) {                                                                                                                              if ($order == 0) {                                                                                                                                 ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php

                                                                }

                                                            } else {

                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="6%" align="center" style="text-align:center;">Action</th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                <?php

                                                if (!empty($data_drv)) {

                                                    for ($i = 0; $i < count($data_drv); $i++) {

                                                        $default = '';

                                                        $iCompanyId = $data_drv[$i]['iCompanyId'];

                                                        if ($iCompanyId == 1) {

                                                            $default = 'disabled';

                                                        }

                                                        $driverCount = 0;

                                                        if(isset($storeDriverCountArr[$iCompanyId])){

                                                            $driverCount = $storeDriverCountArr[$iCompanyId];

                                                        }

                                                        ?>

                                                        <tr class="gradeA">

                                                            <td align="center" style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?= $default; ?> value="<?= $iCompanyId; ?>" data-count="<?= $data_drv[$i]['foodcatCount'] ?>"/>&nbsp;</td>

                                                            <td>

                                                                <a href="javascript:void(0);" onClick="show_company_details('<?= $iCompanyId; ?>')" style="text-decoration: underline;"><?= $generalobjAdmin->clearName(stripslashes(ucfirst($data_drv[$i]['vCompany']))); ?>

                                                                </a>

                                                            </td>

                                                            <td><?= $generalobjAdmin->clearEmail($data_drv[$i]['vEmail']); ?></td>

                                                            <?php if (count($service_cat_data) > 1) { ?>

                                                                <td>

                                                                    <?php foreach ($service_cat_data as $servicedata) { ?>

                                                                        <?php if ($servicedata['iServiceId'] == $data_drv[$i]['iServiceId']) { ?><span><?= (isset($servicedata['vServiceName']) ? $servicedata['vServiceName'] : ''); ?></span><?php } ?>

                                                                    <?php } ?>

                                                                </td>

                                                            <?php } ?>



                                                            <?php if ($isStoreDriver > 0) { ?>

                                                                <td style="text-align:center;"><a href="driver.php?iCompanyId=<?= $iCompanyId; ?>&store=1" target="_blank"><?= $driverCount; ?></a></td>

                                                            <?php } ?>

                                                            <td style="text-align:center;">

                                                                <?php if ($data_drv[$i]['foodcatCount'] > 0 && $userObj->hasPermission('view-item-categories')) { ?>

                                                                    <a href="food_menu.php?iFoodMenuId=&option=c.vCompany&keyword=<?= stripslashes(ucfirst($data_drv[$i]['vCompany'])); ?>&eStatus=" target="_blank"><?= $data_drv[$i]['foodcatCount']; ?></a>

                                                                    <?php

                                                                } else {

                                                                    echo $data_drv[$i]['foodcatCount'];

                                                                }

                                                                ?>

                                                            </td>

                                                            <?php
                                                            if($isStoreCategoriesEnable==1) {
                                                            $storecattags = "";

                                                            if (!empty($data_drv[$i]['storecats'])) {

                                                                $storecatsql = "select JSON_UNQUOTE(JSON_EXTRACT(tCategoryName, '$.tCategoryName_".$default_lang."')) as tCategoryName from store_categories where iCategoryId in (" . $data_drv[$i]['storecats'] . ")";

                                                                $storecatdata = $obj->MySQLSelect($storecatsql);

                                                                $stcnt = 1;

                                                                foreach ($storecatdata as $stkey => $stvalue) {

                                                                    $stvalue_tmp = $stvalue['tCategoryName'];

                                                                    if ($stcnt == count($storecatdata)) {

                                                                        $storecattags .= $stvalue_tmp;

                                                                    } else {

                                                                        $storecattags .= $stvalue_tmp . ", ";

                                                                    }

                                                                    $stcnt++;

                                                                }

                                                            } ?>

                                                            <td>

                                                                <?php echo $storecattags; ?>

                                                            </td>
                                                            <? } ?>

                                                            <td>

                                                                <?php if (!empty($data_drv[$i]['vPhone'])) { ?>

                                                                    (+<?= $data_drv[$i]['vCode'] ?>) <?= $generalobjAdmin->clearPhone($data_drv[$i]['vPhone']); ?>

                                                                <?php } ?>

                                                            </td>

                                                            <td><?= $generalobjAdmin->DateTime($data_drv[$i]['tRegistrationDate']); ?></td>

                                                            <td align="center" >

                                                                <a href="store_document_action.php?id=<?= $iCompanyId; ?>&action=edit" target="_blank">

                                                                    <img src="img/edit-doc.png" alt="Edit Document" >

                                                                </a>

                                                            </td>

                                                            <?php if ($ENABLE_AUTO_ACCEPT_STORE_ORDER == "Yes") { ?>
                                                                <td align="center" style="text-align:center;"> 
                                                                    <a href="<?= $adminUrl."store.php?eAutoaccept=".$data_drv[$i]['eAutoaccept']."&iCompanyId=".$iCompanyId; ?>"><?= $data_drv[$i]['eAutoaccept'] ?></a> 
                                                                      <!--<button data-id="<?= $iCompanyId; ?>" data-status="<?= ($data_drv[$i]['eAutoaccept'] == "Yes") ? 'No' : 'Yes' ?>" onclick="return autoAcceptStatus(this);" class="btn <?php if ($data_drv[$i]['eAutoaccept'] == "Yes") { ?>btn-primary<?php } ?>"><?= $data_drv[$i]['eAutoaccept'] ?></button>-->
                                                                </td>

                                                            <?php } ?>

                                                            <td align="center" style="text-align:center;">

                                                                <?php

                                                                if ($data_drv[$i]['eStatus'] == 'Active') {

                                                                    $dis_img = "img/active-icon.png";

                                                                } else if ($data_drv[$i]['eStatus'] == 'Inactive') {

                                                                    $dis_img = "img/inactive-icon.png";

                                                                } else if ($data_drv[$i]['eStatus'] == 'Deleted') {

                                                                    $dis_img = "img/delete-icon.png";

                                                                }

                                                                ?>

                                                                <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip" title="<?= $data_drv[$i]['eStatus']; ?>">

                                                            </td>

                                                            <td align="center" style="text-align:center;" class="action-btn001">

                                                                <?php
                                                            if($checkSystemStoreSelection==0) {

                                                                if (in_array($iCompanyId, $storeIdArray) && SITE_TYPE == 'Demo') {

                                                                    ?>

                                                                    <a href="store_action.php?id=<?= $iCompanyId; ?>" data-toggle="tooltip" title="Edit">

                                                                        <img src="img/edit-icon.png" alt="Edit">

                                                                    </a>

                                                                <?php 
                                                                 } else { ?>

                                                                    <div class="share-button share-button4 openHoverAction-class" style="display:block;">

                                                                        <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>

                                                                        <div class="social show-moreOptions openPops_<?= $iCompanyId; ?>">

                                                                            <ul>

                                                                                <li class="entypo-twitter" data-network="twitter"><a href="store_action.php?id=<?= $iCompanyId; ?>" data-toggle="tooltip" title="Edit">

                                                                                        <img src="img/edit-icon.png" alt="Edit">

                                                                                    </a>

                                                                                </li>

                                                                                <?php if ($userObj->hasPermission('update-status-store')) { ?>

                                                                                    <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="checkitemcount(<?= $iCompanyId; ?>, '<?= $data_drv[$i]['foodcatCount']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">

                                                                                            <img src="img/active-icon.png" alt="<?= $data_drv[$i]['eStatus']; ?>" >

                                                                                        </a>

                                                                                    </li>

                                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?= $iCompanyId; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">

                                                                                            <img src="img/inactive-icon.png" alt="<?= $data_drv[$i]['eStatus']; ?>" >

                                                                                        </a>

                                                                                    </li>

                                                                                <?php } ?>

                                                                                <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-store')) { ?>

                                                                                    <?php if (!in_array($iCompanyId, $DEMO_NOT_DEL_COMPANY_ID)) { ?> 

                                                                                        <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDeletestore('<?= $iCompanyId; ?>')"  data-toggle="tooltip" title="Delete"> <img src="img/delete-icon.png" alt="Delete" > </a></li>

                                                                                        <?php

                                                                                    }

                                                                                }

                                                                                ?>  <!--<li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" data-name="<?= $generalobjAdmin->clearCmpName($data_drv[$i]['vCompany']); ?>" data-id="<?= $iCompanyId; ?>" onClick="copyStoreData(this)"  data-toggle="tooltip" title="Copy"> <img src="img/right-green.png" alt="Copy" > </a></li>-->

                                                                            </ul>

                                                                        </div>

                                                                    </div>

                                                                <?php } 
                                                                } else { ?>

                                                                <a href="store_action.php?id=<?= $iCompanyId; ?>" data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                    </a>
                                                                <? } ?>
                                                            </td>

                                                        </tr>

                                                        <?php

                                                    }

                                                } else {

                                                    ?>

                                                    <tr class="gradeA">

                                                        <td colspan="11"> No Records Found.</td>

                                                    </tr>

                                                <?php } ?>

                                            </tbody>

                                        </table>

                                    </form>

                                    <?php include('pagination_n.php'); ?>

                                </div>

                            </div>

                            <!--TABLE-END-->

                        </div>

                    </div>
                    <? if($checkSystemStoreSelection==0) { ?>
                    <div class="admin-notes">

                        <h4>Notes:</h4>

                        <ul>

                            <li><?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> module will list all <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> on this page.</li>

                            <li>Admin can Activate / Deactivate / Delete any <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>. Default <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> cannot be Activated / Deactivated / Deleted.</li>

                            <li>Admin can export data in XLS format.</li>

                            <li>This module will list the <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> registered as a <?php if (in_array(1, $catIds)) { ?>Food delivery<?php } if (in_array(2, $catIds)) { ?>,Grocery Delivery<?php } if (in_array(3, $catIds)) { ?>, Wine Delivery<?php } ?>. <br/> ( * As per the package selection Paid services data will be shown here.)</li>

                        </ul>

                    </div>
                    <? }  ?>
                </div>

            </div>

            <!--END PAGE CONTENT -->

        </div>

        <!--END MAIN WRAPPER -->

        <form name="pageForm" id="pageForm" action="action/store.php" method="post" >

            <input type="hidden" name="page" id="page" value="<?= $page; ?>">

            <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">

            <input type="hidden" name="iCompanyId" id="iMainId01" value="" >

            <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus; ?>" >

            <input type="hidden" name="status" id="status01" value="" >

            <input type="hidden" name="statusVal" id="statusVal" value="" >

            <input type="hidden" name="option" value="<?= $option; ?>" >

            <input type="hidden" name="keyword" value="<?= $keyword; ?>" >

            <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>" >

            <input type="hidden" name="order" id="order" value="<?= $order; ?>" >

            <input type="hidden" name="selectcategory" id="selectcategory" value="<?= $select_cat; ?>" >

            <input type="hidden" name="selectstorecategory" id="selectstorecategory" value="<?= $select_store_cat; ?>" >

            <input type="hidden" name="method" id="method" value="" >

        </form>

        <div  class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >

            <div class="modal-dialog" >

                <div class="modal-content">

                    <div class="modal-header">

                        <h4><i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>

                            <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Details<button type="button" class="close" data-dismiss="modal">x</button>

                        </h4>

                    </div>

                    <div class="modal-body" style="max-height: 450px;overflow: auto;">

                        <div id="imageIcons" style="display:none">

                            <div align="center">                                                                       

                                <img src="default.gif"><br/>                                                            

                                <span>Retrieving details,please Wait...</span>                       

                            </div>

                        </div>

                        <div id="comp_detail"></div>

                    </div>

                </div>

            </div>

        </div>

        <div  class="modal fade" id="copy_store" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >

            <div class="modal-dialog" >

                <div class="modal-content">

                    <div class="modal-header">

                        <button type="button" class="close" data-dismiss="modal">x</button>

                        <h4 id="storenametxt"></h4>

                    </div>

                    <div class="modal-body">

                        <form name="_company_form" id="_company_form" method="post" action="" enctype="multipart/form-data">

                            <div class="row">

                                <div class="col-lg-12">

                                    <label>Select Store :</label>

                                </div>

                                <input type="hidden" id="storeid" name="storeId">

                                <div class="col-lg-12">

                                    <select class="form-control" multiple="multiple" name='store_sel[]' id="store_sel">

                                        <?php for ($s = 0; $s < count($getAllStore); $s++) { ?>

                                            <option value="<?= $getAllStore[$s]['iCompanyId']; ?>"><?= stripslashes(ucfirst($getAllStore[$s]['vCompany'])); ?></option>

                                        <?php } ?>

                                    </select>

                                </div>

                            </div>

                            <br><br><br>

                            <div class="row">

                                <div class="col-lg-12">

                                    <input type="submit" class="btn btn-default" name="copystore" id="copystore" value="Copy" >

                                    <a href="store.php" class="btn btn-default back_link">Cancel</a>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

        <?php include_once('footer.php'); ?>

        <script type="text/javascript" src="js/bootstrap-multiselect.js"></script>

        <link rel="stylesheet" href="css/bootstrap-multiselect.css" type="text/css"/>

        <script>

    function checkitemcount(id, countitem, status) {

        if (countitem == 0) {

            var retVal = confirm("This <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> has not added any items yet. Confirm to activate this <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>?");

            if (retVal == true) {

                $("#pageForm").attr("action", "action/store.php");

                changeStatus(id, status);

                return true;

            } else {

                $("#pageForm").attr("action", "javascript:void(0);");

                return false;

            }

        } else {

            changeStatus(id, status);

        }



    }

    function autoAcceptStatus(elem) {
        var companyId = $(elem).attr("data-id");
        var status = $(elem).attr("data-status");
        var changeStatus = "disable";
        if (status == "Yes") {
            changeStatus = "enable";
        }
        var typed = "autoaccept";
        if (confirm('Are you sure to ' + changeStatus + ' selected record(s)?')) {
            //setTimeout(function(){ 
                $.ajax({
                    type: "POST",
                    url: "action/store.php",
                    data: {iCompanyId: companyId, statusVal: status, method: typed},
                    dataType: "json",
                    success: function (data) {
                        if (data.status == "1") {
                            //location.reload();
                        }
                    }
                });
            //}, 2000); 
        } else {
            return false;
        }
        //return false;
    }
    $(document).ready(function () {

        /*$('#store_sel').multiselect({

            enableCaseInsensitiveFiltering: true,

            includeSelectAllOption: true,

            maxHeight: 400

        });*/

    });

    $("#setAllCheck").on('click', function () {

        if ($(this).prop("checked")) {

            jQuery("#_list_form input[type=checkbox]").each(function () {

                if ($(this).attr('disabled') != 'disabled') {

                    this.checked = 'true';

                }

            });

        } else {

            jQuery("#_list_form input[type=checkbox]").each(function () {

                this.checked = '';

            });

        }

    });



    $("#Search").on('click', function () {

        var action = $("#_list_form").attr('action');

        var formValus = $("#frmsearch").serialize();

        window.location.href = action + "?" + formValus;

    });



    $('.entypo-export').click(function (e) {

        e.stopPropagation();

        var $this = $(this).parent().find('div');

        $(".openHoverAction-class div").not($this).removeClass('active');

        $this.toggleClass('active');

    });



    $(document).on("click", function (e) {

        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {

            $(".show-moreOptions").removeClass("active");

        }

    });



    function show_company_details(companyid) {

        $("#comp_detail").html('');

        $("#imageIcons").show();

        $("#detail_modal").modal('show');



        if (companyid != "") {

            var request = $.ajax({

                type: "POST",

                url: "ajax_store_details.php",

                data: "iCompanyId=" + companyid,

                datatype: "html",

                success: function (data) {

                    $("#comp_detail").html(data);

                    $("#imageIcons").hide();

                }

            });

        }

    }



    function status_check(status) {

        if (status == "Active") {

            var zero_values = "No";

            $("input[type=checkbox]:checked").each(function () {

                var cnt = $(this).attr('data-count');

                if (cnt == 0) {

                    zero_values = "Yes";

                    return false;

                }

            });



            if (zero_values == "No") {

                ChangeStatusAll(status);

                $('#new-msg-activeid').html("Are you sure to activate selected record(s)?");

            } else {



                ChangeStatusAll(status);



                $('#new-msg-activeid').html('This <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> has not added any items yet? Are you sure to activate selected record(s)?');

            }

        } else {

            ChangeStatusAll(status);

            $('#new-msg-activeid').html("Are you sure to activate selected record(s)?");

        }

    }

    function copyStoreData(elem) {

        var storeId = $(elem).attr("data-id");

        var storeName = $(elem).attr("data-name");

        $("#storenametxt").html('<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>Store Data Copy : ' + storeName);

        $("#storeid").val(storeId);

        $('#store_sel option[value="' + storeName + '"]').remove();

        $("#copy_store").modal('show');

    }
    //$("#ecategory_options").on('change', function () {
    
    cntserv = '<?= $cnt_serv_cat ?>';
    if (cntserv!=1) {
        getcategories("<?= $select_cat?>","<?= $select_store_cat?>");
    }
    //});
    function getcategories(iServiceid,select_store_cat){
        $.ajax({
                type: "POST",
                url: 'ajax_get_store_categories.php',
                data: {iServiceid: iServiceid,selectedcatid:select_store_cat},
                success: function (response)
                {
                    $("#js-store-category-multiple").html('');
                    if(response == ''){
                         $("#js-store-category-multiple").html('<option value="" >Select Store Category</option>');
                    } else {
                        $("#js-store-category-multiple").html(response);
                    }
                }
            });
    }


        </script>

    </body>

    <!-- END BODY-->

</html>