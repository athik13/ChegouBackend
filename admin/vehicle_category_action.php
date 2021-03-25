<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$form = "";
////$generalobjAdmin->check_member_login();
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
$descEnable = 0;
$required_rule = "accept='image/*'";
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$sub_action = isset($_REQUEST['sub_action']) ? $_REQUEST['sub_action'] : '';
$sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';
$sql = "SELECT vName,vSymbol FROM currency WHERE eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
$goback = $iServiceIdEdit = 0;
$message_print_id = $id;

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : "";
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

$tbl_name = $sql_vehicle_category_table_name;
$script = 'VehicleCategory';

$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);

$cubexthemeon = 0;
if($generalobj->checkXThemOn()=='Yes') {
    $cubexthemeon = 1;
}
$homepage_cubejekx = 0;
if($generalobj->checkCubeJekXThemOn()=='Yes') {
    if(isset($_REQUEST['homepage']) && $_REQUEST['homepage']==1) {
        $homepage_cubejekx = 1;
    } else {
        $homepage_cubejekx = 2;    
    }
}

$vCatNameHomepageArr = $vCatTitleHomepageArr = $vCatSloganHomepageArr = $lCatDescHomepage = $vCatDescbtnHomepage = $vServiceCatTitleHomepageArr = array();

if($cubexthemeon == 1 && $action == 'Edit') {
    $getHomeDataQry = "SELECT vHomepageLogo,vHomepageBanner,vCatNameHomepage,vCatTitleHomepage,vCatSloganHomepage,lCatDescHomepage,vCatDescbtnHomepage,iDisplayOrderHomepage,vServiceCatTitleHomepage,vServiceHomepageBanner FROM ".$sql_vehicle_category_table_name." where iVehicleCategoryId='" . $id . "'";
    $getHomeData = $obj->MySQLSelect($getHomeDataQry);
    $vHomepageLogo = $getHomeData[0]['vHomepageLogo'];
    $vHomepageBanner = $getHomeData[0]['vHomepageBanner'];
    $vServiceHomepageBanner = $getHomeData[0]['vServiceHomepageBanner'];

    foreach ($getHomeData as $key => $value) {
        $vCatNameHomepage = (array) json_decode($value['vCatNameHomepage']);
        foreach ($vCatNameHomepage as $key1 => $value1) {
            $vCatNameHomepageArr[$key1] = $value1;
        }
        $vCatTitleHomepage = (array) json_decode($value['vCatTitleHomepage']);
        foreach ($vCatTitleHomepage as $key2 => $value2) {
            $vCatTitleHomepageArr[$key2] = $value2;
        }

        $vCatSloganHomepage = (array) json_decode($value['vCatSloganHomepage']);
        foreach ($vCatSloganHomepage as $key2 => $value2) {
            $vCatSloganHomepageArr[$key2] = $value2;
        }

        $lCatDescHomepage = (array) json_decode($value['lCatDescHomepage']);
        foreach ($lCatDescHomepage as $key3 => $value3) {
            $lCatDescHomepageArr[$key3] = $value3;
        }
        $vCatDescbtnHomepage = (array) json_decode($value['vCatDescbtnHomepage']);
        foreach ($vCatDescbtnHomepage as $key4 => $value4) {
            $vCatDescbtnHomepageArr[$key4] = $value4;
        }
        
        $vServiceCatTitleHomepage = (array) json_decode($value['vServiceCatTitleHomepage']);
        foreach ($vServiceCatTitleHomepage as $key2 => $value2) {
            $vServiceCatTitleHomepageArr[$key2] = $value2;
        }
        $iDisplayOrderHomepage_db = $value['iDisplayOrderHomepage'];
    }
}
if($cubexthemeon == 1) {
    /* to fetch max iDisplayOrder from table for insert */
    $select_order   = $obj->MySQLSelect("SELECT count(iDisplayOrderHomepage) AS iDisplayOrderHomepage FROM ".$tbl_name." WHERE iParentId = 0 AND eStatus =  'Active'");
    $iDisplayOrderHomepage  = isset($select_order[0]['iDisplayOrderHomepage'])?$select_order[0]['iDisplayOrderHomepage']:0;
    $iDisplayOrder_max_Homepage = $iDisplayOrderHomepage + 1; // Maximum order number
}
/************************************Homepage settings start***********************************************/
if(!empty($_POST['btnsubmit_homepage']) && $cubexthemeon == 1) {


    if (isset($_FILES['vHomepageLogo']) && $_FILES['vHomepageLogo']['name'] != "") {
        $filecheck = basename($_FILES['vHomepageLogo']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        $data = getimagesize($_FILES['vHomepageLogo']['tmp_name']);
        /*$width = $data[0];
        $height = $data[1];
        if ($width != 360 && $height != 360) {
            $flag_error = 1;
            $var_msg = "Please Upload image only 360px * 360px";
        }*/
        if ($flag_error == 1) {
            $_SESSION['success'] = '';
            $_SESSION['var_msg'] = '';
            header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
            exit;
        }
    }

    if (isset($_FILES['vHomepageBanner']) && $_FILES['vHomepageBanner']['name'] != "") {
        $filecheck = basename($_FILES['vHomepageBanner']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        $data = getimagesize($_FILES['vHomepageBanner']['tmp_name']);
        $width = $data[0];
        $height = $data[1];
        if ($flag_error == 1) {
            $_SESSION['success'] = '';
            $_SESSION['var_msg'] = '';
            header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
            exit;
        }
    }
    if (isset($_FILES['vServiceHomepageBanner']) && $_FILES['vServiceHomepageBanner']['name'] != "") {
        $filecheck = basename($_FILES['vServiceHomepageBanner']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        $data = getimagesize($_FILES['vServiceHomepageBanner']['tmp_name']);
        $width = $data[0];
        $height = $data[1];
        if ($flag_error == 1) {
            $_SESSION['success'] = '';
            $_SESSION['var_msg'] = '';
            header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
            exit;
        }
    }

    $vacategoryid = $id;
    $img_arr = $_FILES;

    if (!empty($img_arr)) {
        foreach ($img_arr as $key => $value) {
            if (!empty($value['name'])) {
                $img_path = $tconfig["tsite_upload_home_page_service_images_panel"];
                $temp_gallery = $img_path . '/';
                $image_object = $value['tmp_name'];
                $image_name = $value['name'];
                $check_file_query = "SELECT " . $key . " FROM ".$sql_vehicle_category_table_name." where iVehicleCategoryId='" . $vacategoryid . "'";
                $check_file = $obj->MySQLSelect($check_file_query);
                if ($message_print_id != "") {
                    $check_file = $img_path . '/' . $check_file[0][$key];
                    if ($check_file != '' && file_exists($check_file[0][$key])) {
                        @unlink($check_file);
                    }
                }
                $Photo_Gallery_folder = $img_path . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img = $generalobj->imageupload($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif,svg');

                if ($img[2] == "1") {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                    header("location:" . $backlink);
                }
                if (!empty($img[0])) {
                    $sql = "UPDATE ".$sql_vehicle_category_table_name." SET " . $key . " = '" . $img[0] . "' WHERE iVehicleCategoryId = '" . $vacategoryid . "'";
                    $obj->sql_query($sql);
                    //$_SESSION['success'] = '1';
                    //$_SESSION['var_msg'] = $img[1];
                } else {print_R($img);exit;
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                }
            }
        }
    }

    

    //$vCatNameHomepage_EN = isset($_POST['vCatNameHomepage_EN']) ? $_POST['vCatNameHomepage_EN'] : '';
    //$vCatTitleHomepage_EN = isset($_POST['vCatTitleHomepage_EN']) ? $_POST['vCatTitleHomepage_EN'] : '';
    //$lCatDescHomepage_EN = isset($_POST['lCatDescHomepage_EN']) ? $_POST['lCatDescHomepage_EN'] : '';
    //$vCatDescbtnHomepage_EN = isset($_POST['vCatDescbtnHomepage_EN']) ? $_POST['vCatDescbtnHomepage_EN'] : '';

    $iDisplayOrderHomepage  = isset($_POST['iDisplayOrderHomepage'])?$_POST['iDisplayOrderHomepage']:$iDisplayOrderHomepage;
    $temp_orderHomepage     = isset($_POST['temp_orderHomepage'])? $_POST['temp_orderHomepage'] : "";

    if($temp_orderHomepage == "1" && $action == "Add"){
        $temp_orderHomepage = $iDisplayOrder_max_Homepage;
    }
    if($temp_orderHomepage > $iDisplayOrderHomepage) {
        for($i = $temp_orderHomepage-1; $i >= $iDisplayOrderHomepage; $i--) {
            $sql="UPDATE ".$tbl_name." SET iDisplayOrderHomepage = '".($i+1)."' WHERE iDisplayOrderHomepage = '".$i."'";
            $obj->sql_query($sql);
        }
    } else if($temp_orderHomepage < $iDisplayOrderHomepage) {
        for($i = $temp_orderHomepage+1; $i <= $iDisplayOrderHomepage; $i++) {
            //echo "temp_orderHomepage:".$temp_orderHomepage."<br>"."iDisplayOrderHomepage:".$iDisplayOrderHomepage;
            $sql="UPDATE ".$tbl_name." SET iDisplayOrderHomepage = '".($i-1)."' WHERE iDisplayOrderHomepage = '".$i."'";
            $obj->sql_query($sql);
        }
    }
    
    $vCatNameHomepageArr = $vCatTitleHomepageArr  = $vCatSloganHomepageArr  = $lCatDescHomepageArr = $vServiceCatTitleHomepageArr = array();
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $tTypeDesc = "";
            if (isset($_POST['vCatNameHomepage_' . $db_master[$i]['vCode']])) {
                $tTypeDesc = $_POST['vCatNameHomepage_' . $db_master[$i]['vCode']];
            }
            $vCatNameHomepageArr["vCatNameHomepage_" . $db_master[$i]['vCode']] = $tTypeDesc;

            $tTypeDesc = "";
            if (isset($_POST['vCatTitleHomepage_' . $db_master[$i]['vCode']])) {
                $tTypeDesc = $_POST['vCatTitleHomepage_' . $db_master[$i]['vCode']];
            }
            $vCatTitleHomepageArr["vCatTitleHomepage_" . $db_master[$i]['vCode']] = $tTypeDesc;

            $tTypeDesc = "";
            if (isset($_POST['vCatSloganHomepage_' . $db_master[$i]['vCode']])) {
                $tTypeDesc = $_POST['vCatSloganHomepage_' . $db_master[$i]['vCode']];
            }
            $vCatSloganHomepageArr["vCatSloganHomepage_" . $db_master[$i]['vCode']] = $tTypeDesc;

            $tTypeDesc = "";
            if (isset($_POST['lCatDescHomepage_' . $db_master[$i]['vCode']])) {
                $tTypeDesc = $_POST['lCatDescHomepage_' . $db_master[$i]['vCode']];
            }
            $lCatDescHomepageArr["lCatDescHomepage_" . $db_master[$i]['vCode']] = $tTypeDesc;

            $tTypeDesc = "";
            if (isset($_POST['vCatDescbtnHomepage_' . $db_master[$i]['vCode']])) {
                $tTypeDesc = $_POST['vCatDescbtnHomepage_' . $db_master[$i]['vCode']];
            }
            $vCatDescbtnHomepageArr["vCatDescbtnHomepage_" . $db_master[$i]['vCode']] = $tTypeDesc;
            
            if (isset($_POST['vServiceCatTitleHomepage_' . $db_master[$i]['vCode']])) {
                $tTypeDesc = $_POST['vServiceCatTitleHomepage_' . $db_master[$i]['vCode']];
            }
            $vServiceCatTitleHomepageArr["vServiceCatTitleHomepage_" . $db_master[$i]['vCode']] = $tTypeDesc;
        }
    }

    $vCatNameHomepage = $vCatTitleHomepage = $lCatDescHomepage = $vCatDescbtnHomepage = $vServiceCatTitleHomepage = '';
    if (count($vCatNameHomepageArr) > 0) {
        $vCatNameHomepage = $generalobj->getJsonFromAnArr($vCatNameHomepageArr);
    }
    if (count($vCatTitleHomepageArr) > 0) {
        $vCatTitleHomepage = $generalobj->getJsonFromAnArr($vCatTitleHomepageArr);
    }
    if (count($vCatSloganHomepageArr) > 0) {
        $vCatSloganHomepage = $generalobj->getJsonFromAnArr($vCatSloganHomepageArr);
    }    
    if (count($lCatDescHomepageArr) > 0) {
        $lCatDescHomepage = $generalobj->getJsonFromAnArr($lCatDescHomepageArr);
    }
    if (count($vCatDescbtnHomepageArr) > 0) {
        $vCatDescbtnHomepage = $generalobj->getJsonFromAnArr($vCatDescbtnHomepageArr);
    }
    if (count($vServiceCatTitleHomepageArr) > 0) {
        $vServiceCatTitleHomepage = $generalobj->getJsonFromAnArr($vServiceCatTitleHomepageArr);
    }

    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iVehicleCategoryId` = '" . $id . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET
    `vCatNameHomepage` = '" . $vCatNameHomepage . "',
    `vCatTitleHomepage` = '" . $vCatTitleHomepage . "',
    `vCatSloganHomepage` = '" . $vCatSloganHomepage . "',
    `lCatDescHomepage` = '" . $lCatDescHomepage . "',
    `vCatDescbtnHomepage` = '" . $vCatDescbtnHomepage . "',
    `iDisplayOrderHomepage` = '".$iDisplayOrderHomepage."',
    `vServiceCatTitleHomepage` = '" . $vServiceCatTitleHomepage . "'" . $where;
    $obj->sql_query($query);


    $id = ($id != '') ? $id : $obj->GetInsertId();
    
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("Location:" . $backlink);
    exit;
} else {
    /************************************Homepage settings end***********************************************/

    $vCategory_EN = isset($_POST['vCategory_EN']) ? $_POST['vCategory_EN'] : '';
    $tCategoryDesc_EN = isset($_POST['tCategoryDesc_EN']) ? $_POST['tCategoryDesc_EN'] : '';
    $eBeforeUpload = isset($_POST['eBeforeUpload']) ? $_POST['eBeforeUpload'] : '';
    $eAfterUpload = isset($_POST['eAfterUpload']) ? $_POST['eAfterUpload'] : '';
    $eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
    $iParentId = isset($_POST['vCategory']) ? $_POST['vCategory'] : '';
    $eShowType = isset($_POST['eShowType']) ? $_POST['eShowType'] : 'Icon';
    $ePriceType = isset($_POST['ePriceType']) ? $_POST['ePriceType'] : 'Service';
    $eMaterialCommision = isset($_POST['eMaterialCommision']) ? $_POST['eMaterialCommision'] : 'No';
    $fCommision = isset($_POST['fCommision']) ? $_POST['fCommision'] : 0;
    $iCancellationTimeLimit = isset($_POST['iCancellationTimeLimit']) ? $_POST['iCancellationTimeLimit'] : '';
    $fCancellationFare = isset($_POST['fCancellationFare']) ? $_POST['fCancellationFare'] : '';
    $iWaitingFeeTimeLimit = isset($_POST['iWaitingFeeTimeLimit']) ? $_POST['iWaitingFeeTimeLimit'] : '';
    $fWaitingFees = isset($_POST['fWaitingFees']) ? $_POST['fWaitingFees'] : '';
    $vTitle_store = $vDesc_store = $descArr = $serviceNameArr = array();
    $eShowTerms = isset($_POST['eShowTerms']) ? $_POST['eShowTerms'] : 'No';
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vValue = 'vCategory_' . $db_master[$i]['vCode'];
            $vValue_desc = 'tCategoryDesc_' . $db_master[$i]['vCode'];
            array_push($vTitle_store, $vValue);
            $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
            array_push($vDesc_store, $vValue_desc);
            $tCategoryDesc = $vValue = "";
            if (isset($_POST['vCategory_' . $db_master[$i]['vCode']])) {
                $vValue = $_POST['vCategory_' . $db_master[$i]['vCode']];
            }
            if (isset($_POST['tCategoryDesc_' . $db_master[$i]['vCode']])) {
                $tCategoryDesc = $_POST['tCategoryDesc_' . $db_master[$i]['vCode']];
            }
            $$vValue_desc = isset($_POST[$vValue_desc]) ? $_POST[$vValue_desc] : '';
            $descArr["vCategory_" . $db_master[$i]['vCode']] = $vValue;
            $serviceNameArr["vServiceName_" . $db_master[$i]['vCode']] = $vValue;
            $descArr["tCategoryDesc_" . $db_master[$i]['vCode']] = $tCategoryDesc;
        }
    }
    $sql = "select vCategory_" . $default_lang . ", iVehicleCategoryId, eCatType, eFor from ".$sql_vehicle_category_table_name." where iVehicleCategoryId='" . $sub_cid . "'";
    $db_data1 = $obj->MySQLSelect($sql);
    /* to fetch max iDisplayOrder from table for insert */
    if ($sub_action == "sub_category") {
        $select_order = $obj->MySQLSelect("SELECT count(iVehicleCategoryId) AS iDisplayOrder FROM " . $tbl_name . " WHERE iParentId = '" . $sub_cid . "'");
        $iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
    $iDisplayOrder = $iDisplayOrder + 1; // Maximum order number
} else {
    $select_order = $obj->MySQLSelect("SELECT count(iVehicleCategoryId) AS iDisplayOrder FROM " . $tbl_name . " WHERE iParentId = 0");
    $iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
    $iDisplayOrder = $iDisplayOrder + 1; // Maximum order number
}
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$tBannerButtonTextArr = $tDescriptionArr = array();
if (isset($_REQUEST['goback'])) {
    $goback = $_REQUEST['goback'];
}
if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-vehicle-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create service category.';
        header("Location:vehicle_category.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-vehicle-category')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create service category.';
        header("Location:vehicle_category.php");
        exit;
    }
    if (isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != "") {
        $filecheck = basename($_FILES['vLogo']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        $data = getimagesize($_FILES['vLogo']['tmp_name']);
        $width = $data[0];
        $height = $data[1];
        if ($width < 360 && $height < 360) {
            $flag_error = 1;
            $var_msg = "Please Upload minimum image 360px * 360px";
        }
        if ($flag_error == 1) {
            //$form = $obg->getPostForm($_REQUEST,$var_msgs,"");
            //echo $sub_action;die;
            if ($action == "Add") {
                if ($sub_action == "sub_category") {
                    header("Location:vehicle_category_action.php?sub_action=sub_category&sub_cid=" . $sub_cid . "&var_msg=" . $var_msg);
                    exit;
                } else {
                    header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
                    exit;
                }
            } else {
                if ($sub_action == "sub_category") {
                    header("Location:vehicle_category_action.php?id=" . $id . "&sub_action=sub_category&sub_cid=" . $sub_cid . "&var_msg=" . $var_msg);
                    exit;
                } else {
                    header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");
                    exit;
                }
            }
        }
    }
    if (isset($_FILES['vBannerImage']) && $_FILES['vBannerImage']['name'] != "") {
        $filecheck = basename($_FILES['vBannerImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "png" && $ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "bmp") {
            $flag_error = 1;
            $var_msg = "You have selected wrong file format for Image. Valid formats are jpg,jpeg,gif,png,bmp.";
        }
        /*$data = getimagesize($_FILES['vBannerImage']['tmp_name']);
        $width = $data[0];
        $height = $data[1];*/
        if ($flag_error == 1) {
            if ($action == "Add") {
                if ($sub_action == "sub_category") {
                    header("Location:vehicle_category_action.php?sub_action=sub_category&sub_cid=" . $sub_cid . "&var_msg=" . $var_msg);
                    exit;
                } else {
                    header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg);
                    exit;
                }
            } else {
                if ($sub_action == "sub_category") {
                    header("Location:vehicle_category_action.php?id=" . $id . "&sub_action=sub_category&sub_cid=" . $sub_cid . "&var_msg=" . $var_msg);
                    exit;
                } else {
                    header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg);
                    exit;
                }
            }
        }
    }
    if (SITE_TYPE == 'Demo') {
        if ($sub_action == "sub_category") {
            header("Location:vehicle_category_action.php?id=" . $id . "&sub_action=sub_category&sub_cid=" . $sub_cid . "&success=2");
            exit;
        } else {
            header("Location:vehicle_category_action.php?id=" . $id . "&success=2");
            exit;
        }
    }
    for ($d = 0; $d < count($db_master); $d++) {
        $tBannerButtonText = "Book Now";
        $tDescription = "";
        if (isset($_POST['tBannerButtonText_' . $db_master[$d]['vCode']])) {
            $tBannerButtonText = $_POST['tBannerButtonText_' . $db_master[$d]['vCode']];
        }
        if (isset($_POST['tDescription_' . $db_master[$d]['vCode']])) {
            $tDescription = $_POST['tDescription_' . $db_master[$d]['vCode']];
        }
        if ($tBannerButtonText == "") {
            $tBannerButtonText = "Book Now";
        }
        $tBannerButtonTextArr["tBannerButtonText_" . $db_master[$d]['vCode']] = $tBannerButtonText;
        $tDescriptionArr["tDescription_" . $db_master[$d]['vCode']] = $tDescription;
    }
    $tDescriptionArr = array();
    //echo "<pre>";print_r($tDescriptionArr);die;
    if (count($vTitle_store) > 0) {
        $setlanguage = $setServiceLanguage = "";
        foreach ($descArr as $key => $value) {
            $setlanguage .= "`" . $key . "`= '" . $value . "',";
        }
        //Added By HJ On 09-01-2019 For Update Data Into service_categories Table When Upadte Vehicle Category As Per Discuss With KS Sir Start
        $iServiceIdEdit = $_POST['iServiceIdEdit'];
        if ($iServiceIdEdit > 0) {
            foreach ($serviceNameArr as $key1 => $value1) {
                $setServiceLanguage .= "`" . $key1 . "`= '" . $value1 . "',";
            }
            $setImage = "";
            if (isset($_FILES['vBannerImage']) && $_FILES['vBannerImage']['name'] != "") {
                $bannerImage = $_FILES['vBannerImage'];
                $img_path = $tconfig["tsite_upload_service_categories_images_path"];
                $temp_gallery = $img_path . '/';
                $image_object = $bannerImage['tmp_name'];
                $image_name = $bannerImage['name'];
                $check_file_query = "SELECT vImage FROM service_categories where iServiceId='" . $iServiceIdEdit . "'";
                $check_file = $obj->MySQLSelect($check_file_query);
                if ($message_print_id != "") {
                    $check_file = $img_path . '/' . $check_file[0]['vImage'];
                    if ($check_file != '' && file_exists($check_file[0]['vImage'])) {
                        @unlink($check_file);
                    }
                }
                $Photo_Gallery_folder = $img_path . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img = $generalobj->imageupload($Photo_Gallery_folder, $image_object, $image_name, '', 'png,jpg,jpeg,gif');
                //$img = $generalobj->fileupload_home($Photo_Gallery_folder,$image_object,$image_name,'','png,jpg,jpeg,gif','');
                if ($img[2] == "1") {
                    $_SESSION['success'] = '0';
                    $_SESSION['var_msg'] = $img[1];
                    header("location:" . $backlink);
                }
                if (!empty($img[0])) {
                    $setImage = ",vImage='" . $img[0] . "'";
                }
            }
            //Added By HJ On 14-08-2019 For Update Category Description Start Comment On 14-08-2019 As Per Discuss With KS Sir
            //$jsonServiceDesc = $obj->cleanQuery(json_encode($tDescriptionArr));
            //`tBannerButtonText` = '" . $jsonBannerButtonText . "'"
            //$update_service = "UPDATE `service_categories` SET " . trim($setServiceLanguage, ",") . " $setImage,`tDescription`='" . $jsonServiceDesc . "' WHERE iServiceId=" . $iServiceIdEdit;
            //Added By HJ On 14-08-2019 For Update Category Description End Comment On 14-08-2019 As Per Discuss With KS Sir
            $update_service = "UPDATE `service_categories` SET eShowTerms = '" . $eShowTerms . "', " . trim($setServiceLanguage, ",") . " $setImage  ,`eStatus` = '" . $eStatus . "' WHERE iServiceId=" . $iServiceIdEdit;
            $obj->sql_query($update_service);
        }
        //Added By HJ On 09-01-2019 For Update Data Into service_categories Table When Upadte Vehicle Category As Per Discuss With KS Sir End
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iVehicleCategoryId` = '" . $id . "'";
        }
        $query = $q . " `" . $tbl_name . "` SET
        `eBeforeUpload` = '" . $eBeforeUpload . "',
        `eAfterUpload` = '" . $eAfterUpload . "',
        `eStatus` = '" . $eStatus . "',
        `iParentId` = '" . $iParentId . "',
        `ePriceType`= '" . $ePriceType . "',
        `eMaterialCommision`= '" . $eMaterialCommision . "',
        `fCommision`= '" . $fCommision . "',
        `iCancellationTimeLimit` = '" . $iCancellationTimeLimit . "',
        `fCancellationFare` = '" . $fCancellationFare . "',
        `iWaitingFeeTimeLimit` = '" . $iWaitingFeeTimeLimit . "',
        `fWaitingFees` = '" . $fWaitingFees . "',
        `iDisplayOrder` = '" . $iDisplayOrder . "', " . $setlanguage . "
        `eShowType` = '" . $eShowType . "'"
        . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }
    if ($id > 0 && count($tBannerButtonTextArr) > 0) {
        $jsonBannerButtonText = $generalobj->getJsonFromAnArr($tBannerButtonTextArr);
        $q = "UPDATE ";
        $whereCondition = " WHERE `iVehicleCategoryId` = '" . $id . "'";
        $update_query = $q . " `" . $tbl_name . "` SET `tBannerButtonText` = '" . $jsonBannerButtonText . "'" . $whereCondition;
        $obj->sql_query($update_query);
    }
    if (isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != "") {
        $currrent_upload_time = time();
        $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vLogo']['tmp_name'];
        $image_name = $_FILES['vLogo']['name'];
        $check_file_query = "select iVehicleCategoryId,vLogo from ".$sql_vehicle_category_table_name." where iVehicleCategoryId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        if ($image_name != "") {
            if ($message_print_id != "") {
                $check_file['vLogo'] = $check_file[0]['vLogo'];
                $android_path = $img_path . '/' . $id . '/android';
                $ios_path = $img_path . '/' . $id . '/ios';
                if ($check_file['vLogo'] != '') {
                    @unlink($android_path . '/' . $check_file['vLogo']);
                    @unlink($android_path . '/mdpi_' . $check_file['vLogo']);
                    @unlink($android_path . '/hdpi_' . $check_file['vLogo']);
                    @unlink($android_path . '/xhdpi_' . $check_file['vLogo']);
                    @unlink($android_path . '/xxhdpi_' . $check_file['vLogo']);
                    @unlink($android_path . '/xxxhdpi_' . $check_file['vLogo']);
                    @unlink($ios_path . '/' . $check_file['vLogo']);
                    @unlink($ios_path . '/1x_' . $check_file['vLogo']);
                    @unlink($ios_path . '/2x_' . $check_file['vLogo']);
                    @unlink($ios_path . '/3x_' . $check_file['vLogo']);
                }
            }
            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            $Photo_Gallery_folder_android = $Photo_Gallery_folder . 'android/';
            $Photo_Gallery_folder_ios = $Photo_Gallery_folder . 'ios/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                mkdir($Photo_Gallery_folder_android, 0777);
                mkdir($Photo_Gallery_folder_ios, 0777);
            }
            $vVehicleType1 = str_replace(' ', '', $vCategory_ . $default_lang);
            $img = $generalobj->general_upload_image_vehicle_category_android($image_object, $image_name, $Photo_Gallery_folder_android, $tconfig["tsite_upload_images_vehicle_category_size1_android"], $tconfig["tsite_upload_images_vehicle_category_size2_android"], $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size4_android"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_category_size5_both"], $Photo_Gallery_folder_android, $vVehicleType1, NULL);
            $img1 = $generalobj->general_upload_image_vehicle_category_ios($image_object, $image_name, $Photo_Gallery_folder_ios, '', '', $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size5_both"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_category_size5_ios"], $Photo_Gallery_folder_ios, $vVehicleType1, NULL);
            $img_time = explode("_", $img);

            $filecheck = basename($_FILES['vLogo']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[count($fileextarr) - 1]);
            $time_val = $img_time[0];
            $vImage = "ic_car_" . $vVehicleType1 . "_" . $time_val . "." . $ext;
            //$vImage = "ic_car_".$vVehicleType1.".png";      
            $sql = "UPDATE " . $tbl_name . " SET `vLogo` = '" . $vImage . "' WHERE `iVehicleCategoryId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }
    if (isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != "") {
        $currrent_upload_time = time() + 10;
        $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vLogo1']['tmp_name'];
        $image_name = $_FILES['vLogo1']['name'];
        $check_file_query = "select iVehicleCategoryId,vLogo1 from ".$sql_vehicle_category_table_name." where iVehicleCategoryId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        if ($image_name != "") {
            if ($message_print_id != "") {
                $check_file['vLogo1'] = $check_file[0]['vLogo1'];
                $android_path = $img_path . '/' . $id . '/android';
                $ios_path = $img_path . '/' . $id . '/ios';
                if ($check_file['vLogo1'] != '') {
                    @unlink($android_path . '/' . $check_file['vLogo1']);
                    @unlink($android_path . '/mdpi_hover_' . $check_file['vLogo1']);
                    @unlink($android_path . '/hdpi_hover_' . $check_file['vLogo1']);
                    @unlink($android_path . '/xhdpi_hover_' . $check_file['vLogo1']);
                    @unlink($android_path . '/xxhdpi_hover_' . $check_file['vLogo1']);
                    @unlink($android_path . '/xxxhdpi_hover_' . $check_file['vLogo1']);
                    @unlink($ios_path . '/' . $check_file['vLogo1']);
                    @unlink($ios_path . '/1x_hover_' . $check_file['vLogo1']);
                    @unlink($ios_path . '/2x_hover_' . $check_file['vLogo1']);
                    @unlink($ios_path . '/3x_hover_' . $check_file['vLogo1']);
                }
            }
            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            $Photo_Gallery_folder_android = $Photo_Gallery_folder . '/android/';
            $Photo_Gallery_folder_ios = $Photo_Gallery_folder . '/ios/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                mkdir($Photo_Gallery_folder_android, 0777);
                mkdir($Photo_Gallery_folder_ios, 0777);
            }
            $vVehicleType1 = str_replace(' ', '', $vCategory_ . $default_lang);
            $img = $generalobj->general_upload_image_vehicle_category_android($image_object, $image_name, $Photo_Gallery_folder_android, $tconfig["tsite_upload_images_vehicle_category_size1_android"], $tconfig["tsite_upload_images_vehicle_category_size2_android"], $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size4_android"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_type_size5_both"], $Photo_Gallery_folder_android, $vVehicleType1, "hover_");
            $img1 = $generalobj->general_upload_image_vehicle_category_ios($image_object, $image_name, $Photo_Gallery_folder_ios, '', '', $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size5_both"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_category_size5_ios"], $Photo_Gallery_folder_ios, $vVehicleType1, "hover_");
            $img_time = explode("_", $img);
            $time_val = $img_time[0];
            $vImage1 = "ic_car_" . $vVehicleType1 . "_" . $time_val . ".png";
            //$vImage1 = "ic_car_".$vVehicleType1.".png";
            $sql = "UPDATE " . $tbl_name . " SET `vLogo1` = '" . $vImage1 . "' WHERE `iVehicleCategoryId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }

    if (isset($_FILES['vBannerImage']) && $_FILES['vBannerImage']['name'] != "") {
        $currrent_upload_time = time();
        $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vBannerImage']['tmp_name'];
        $image_name = $_FILES['vBannerImage']['name'];
        $data = getimagesize($_FILES['vBannerImage']['tmp_name']);
        $imgwidth = $data[0];
        $imgheight = $data[1];
        /* Calculate aspect ratio by dividing height by width */
        $aspectRatio = $imgwidth / $imgheight;
        $aspect = round($aspectRatio, 2);
        if ($aspect != "1.78") {
            echo"<script>alert('Please upload image with recommended dimensions and aspect ratio 16:9. Otherwise image will look stretched.');</script>";
        }
        if ($imgwidth < 2880) {
            echo"<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
        }
        if ($imgheight > 2880) {
            echo"<script>alert('Uploaded image size is larger than recommended size, Image may take much time to load.');</script>";
        }
        $check_file_query = "select iVehicleCategoryId,vBannerImage from ".$sql_vehicle_category_table_name." where iVehicleCategoryId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        if ($image_name != "") {
            if ($message_print_id != "") {
                $check_file['vBannerImage'] = $img_path . '/' . $id . '/' . $check_file[0]['vBannerImage'];
                $android_path = $img_path . '/' . $id;
                if ($check_file['vBannerImage'] != '' && file_exists($check_file['vBannerImage'])) {
                    @unlink($check_file['vBannerImage']);
                }
            }
            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            $Photo_Gallery_folder_android = $Photo_Gallery_folder;
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                mkdir($Photo_Gallery_folder_android, 0777);
                mkdir($Photo_Gallery_folder_ios, 0777);
            }
            $vVehicleType1 = str_replace(' ', '', $vCategory_ . $default_lang);
            $img = $generalobj->general_upload_image_vehicle_category_android($image_object, $image_name, $Photo_Gallery_folder_android, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder_android, $vVehicleType1, NULL);
            $img_time = explode("_", $img);
            $time_val = $img_time[0];
            $filecheck = basename($_FILES['vBannerImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[count($fileextarr) - 1]);
            $vImage = "ic_car_" . $vVehicleType1 . "_" . $time_val . "." . $ext;
            $sql = "UPDATE " . $tbl_name . " SET `vBannerImage` = '" . $vImage . "' WHERE `iVehicleCategoryId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
    }
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("Location:" . $backlink);
    exit;
}
}
// for Edit
$userEditDataArr = $db_data = $serviceDescArr = array();
$eCatType = "ServiceProvider"; // Default Define ServiceProvider As Per Discuss with KS For Solved Mantis #11176
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iVehicleCategoryId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>";
    //print_r($db_data);die;
    $vLabel = $id;
    $getServiceDesc = $obj->MySQLSelect("SELECT iServiceId,tDescription FROM service_categories");
    for ($d = 0; $d < count($getServiceDesc); $d++) {
        $serviceDescArr[$getServiceDesc[$d]['iServiceId']] = $getServiceDesc[$d];
    }
    if (count($db_data) > 0) {
        $tBannerButtonText = (array) json_decode($db_data[0]['tBannerButtonText']);
        foreach ($tBannerButtonText as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        //echo "<pre>";print_R($userEditDataArr);die;
        for ($i = 0; $i < count($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vValue = 'vCategory_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vValue_desc = 'tCategoryDesc_' . $db_master[$i]['vCode'];
                $$vValue_desc = $value[$vValue_desc];
                $eBeforeUpload = $value['eBeforeUpload'];
                $eAfterUpload = $value['eAfterUpload'];
                $eStatus = $value['eStatus'];
                $iParentId = $value['iParentId'];
                $ePriceType = $value['ePriceType'];
                $eMaterialCommision = $value['eMaterialCommision'];
                $fCommision = $value['fCommision'];
                $eShowType = $value['eShowType'];
                $vLogo = $value['vLogo'];
                $iVehicleCategoryId = $value['iVehicleCategoryId'];
                $iDisplayOrder = $value['iDisplayOrder'];
                $vBannerImage = $value['vBannerImage'];
                $eCatType = $value['eCatType'];
                $iServiceIdEdit = $value['iServiceId'];
                $eFor = $value['eFor'];
                $iCancellationTimeLimit = ($value['iCancellationTimeLimit'] == 0) ? '' : $value['iCancellationTimeLimit'];
                $fCancellationFare = ($value['fCancellationFare'] == 0) ? '' : $value['fCancellationFare'];
                $iWaitingFeeTimeLimit = ($value['iWaitingFeeTimeLimit'] == 0) ? '' : $value['iWaitingFeeTimeLimit'];
                $fWaitingFees = ($value['fWaitingFees'] == 0) ? '' : $value['fWaitingFees'];
            }
        }
    }
    if($iServiceIdEdit > 0 && isEnableTermsServiceCategories())
    {
        $scsql = "select eShowTerms from service_categories WHERE iServiceId = ".$iServiceIdEdit;
        $scsqlData = $obj->MySQLSelect($scsql);
        $eShowTerms = $scsqlData[0]['eShowTerms'];
    }
}
if (isset($serviceDescArr[$iServiceIdEdit]['tDescription']) && $serviceDescArr[$iServiceIdEdit]['tDescription'] != "") {
    $tDescription = (array) json_decode($serviceDescArr[$iServiceIdEdit]['tDescription']);
    foreach ($tDescription as $key1 => $value1) {
        $userEditDataArr[$key1] = $value1;
    }
}

///echo $iServiceIdEdit;die;
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title>Admin | <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_ADMIN']; ?> <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <?
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
    <style type="text/css">
        .logo-preview-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: 20px
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 " >

    <!-- MAIN WRAPPER -->
    <div id="wrap">
        <?
        include_once('header.php');
        include_once('left_menu.php');
        ?>
        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
                <div class="row">
                    <div class="col-lg-12">
                        <h2> <?php if ($sub_cid != "") { ?><?= "Sub " . $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?>(<?php echo $db_data1[0]['vCategory_' . $default_lang] ?>)<?php } else { ?> <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?><?php } ?></h2>
                        <?php
                        if ($sub_cid != "") {
                            $redirect_back_page = 'vehicle_sub_category.php?sub_cid=' . $sub_cid;
                                if ($parent_ufx_catid != '0') { //added by SP on 05-10-2019 for changeredirect url change when direct sub category opened
                                    $redirect_back_page .= '&subcat='.$sub_cid;
                                }
                            } else {
                                $redirect_back_page = 'vehicle_category.php';
                            }
                            ?>
                            <a href="<?php echo $redirect_back_page; ?>">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>

                        </div>
                    </div>
                    <hr />
                    <?= $form; ?>
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <? } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                                <?
                            }
                            if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != "") {
                                ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $_REQUEST['var_msg']; ?>
                                </div><br/>  
                            <?php }
                            ?>
                            <?php if($cubexthemeon == 1 && empty($sub_cid)) {
                                $activetab = 'general';
                                if(isset($_REQUEST['homepage']) && $_REQUEST['homepage']==1) {
                                    $activetab = 'homepage';
                                }
                                if($homepage_cubejekx==0) {
                                    ?>
                                    <ul class="nav nav-tabs">
                                        <li class="">
                                            <a data-toggle="tab" href="#"></a>
                                        </li>
                                        <li class="<?php if($activetab=='general') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#Generalsettings">General</a>
                                        </li>
                                        <li class="<?php if($activetab=='homepage') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#Homepagesettings">Home page settings</a>
                                        </li>
                                    </ul>
                                <?php } ?>
                                <div class="tab-content">
                                    <div id="Generalsettings" class="tab-pane <?php if($activetab=='general') { ?> active <?php }  ?>">
                                    <?php } ?>
                                    <div id="price1" ></div>
                                    <div id="price" ></div>
                                    <form id="vtype" method="post" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                        <input type="hidden" name="backlink" id="backlink" value="vehicle_category.php"/>
                                        <?php if ($sub_action == "sub_category") { ?>
                                            <div class="row" style="display: none;">
                                                <div class="col-lg-12">
                                                    <label>Parent Category :</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <select  class="form-control" name = 'vCategory'  id= 'vCategory' >
                                                        <?php for ($i = 0; $i < count($db_data1); $i++) { ?>
                                                            <option value = "<?php echo $db_data1[$i]['iVehicleCategoryId'] ?>" <?= ($db_data1[$i]['iVehicleCategoryId'] == $iVehicleCategoryId) ? 'selected' : ''; ?>><?php echo $db_data1[$i]['vCategory_' . $default_lang]; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <input type="hidden" name= "vCategory" value="0">
                                            <?php
                                        }
                                        if ($eCatType == 'ServiceProvider' || $action == 'Add') {
                                            ?>
                                            <div class="row epricetype" style="display: none;">
                                                <div class="col-lg-12">
                                                    <label>Price Based On <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="1. Service - Administrator will define Service Charge <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> - You want <?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']; ?> to Edit the charges defined by you. From the <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Application, they can set their own service charges."></i></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <select  class="form-control" name = 'ePriceType'  id= 'ePriceType' >
                                                        <option value="Service" <? if ('Service' == $db_data[0]['ePriceType']) { ?>selected<? } ?>>Service ( Site Administrator will define the price)</option>
                                                        <option value="Provider" <? if ('Provider' == $db_data[0]['ePriceType']) { ?>selected<? } ?>><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> ( <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> will set their own price )</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row eMaterialCommision" style="display: none;">
                                                <div class="col-lg-12">
                                                    <label>Commission On Material/Misc Fee <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Apply commission on the extra materials used during service, apart from service charge."></i></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <select class="form-control" name = "eMaterialCommision"  id="eMaterialCommision">
                                                        <option value="Yes" <? if ("Yes" == $eMaterialCommision) { ?>selected<? } ?> >Yes</option>
                                                        <option value="No" <? if ("No" == $eMaterialCommision) { ?>selected<? } ?> >No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        if ($count_all > 0) {
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'vCategory_' . $vCode;
                                                $vValue_desc = 'tCategoryDesc_' . $vCode;
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                $tBannerButtonText = 'tBannerButtonText_' . $vCode;
                                                $tBannerButtonTextdefault = 'tBannerButtonText_' . $default_lang;
                                                
                                                $tDescription = 'tDescription_' . $vCode;
                                                $serviceDescValue = "";
                                                if(isset($userEditDataArr[$tDescription])){
                                                    $serviceDescValue = $userEditDataArr[$tDescription];
                                                }
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Category (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                        
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                    </div>
                                                    
                                                    <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                        <div class="col-lg-6">
                                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCategory');">Convert To All Language</button>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                                $displayBannerBottonText = 0;
                                                if ($sub_action == "sub_category" && $APP_TYPE == 'Ride-Delivery-UberX' && $db_data1[0]['eCatType'] == 'MoreDelivery' && $db_data1[0]['eFor'] == "DeliverAllCategory") {
                                                    $displayBannerBottonText = 1;
                                                }
                                                if (($eShowType != "Icon" || $action == 'Add' || $eCatType != 'ServiceProvider') && $sub_action != "sub_category" && $APP_TYPE == 'Ride-Delivery-UberX') {
                                                    $displayBannerBottonText = 1;
                                                }
                                                if ($displayBannerBottonText == 1) {
                                                    ?>
                                                    <div class="row bannerbutton" id="bannerbutton">
                                                        <div class="col-lg-12">
                                                            <label>Banner Button Text (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <input type="text" class="form-control" name="<?= $tBannerButtonText; ?>" id="<?= $tBannerButtonText; ?>" value="<?= $userEditDataArr[$tBannerButtonText]; ?>" placeholder="<?= $vTitle; ?> Value">
                                                        </div>
                                                        <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                            <div class="col-lg-6">
                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tBannerButtonText');">Convert To All Language</button>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <?php
                                                }
                                                if (($sub_action == "sub_category" && $eCatType != "DeliverAll" && $eCatType != "ServiceProvider") || ($sub_action == "sub_category" && $eCatType == "ServiceProvider" && $SERVICE_PROVIDER_FLOW != "Provider")) {
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Category Description (<?= $vTitle; ?>) </label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <textarea class="form-control" name="<?= $vValue_desc; ?>" id="<?= $vValue_desc; ?>" placeholder="<?= $vTitle; ?> Value"><?= $$vValue_desc; ?></textarea>
                                                        </div>
                                                        <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                            <div class="col-lg-6">
                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tCategoryDesc');">Convert To All Language</button>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <?
                                                }
                                                if ($iServiceIdEdit > 0 && $descEnable == 1) {
                                                    ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Service Description (<?= $vTitle; ?>) </label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <textarea <?= $required; ?> class="form-control" name="<?= $tDescription; ?>" id="<?= $tDescription; ?>" placeholder="<?= $vTitle; ?> Value"><?= $serviceDescValue; ?></textarea>                                              
                                                        </div>
                                                        <? if ($vCode == $default_lang && count($db_master) > 1) { ?>
                                                            <div class="col-lg-6">
                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription');">Convert To All Language</button>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                        }
                                        if (($eCatType == 'ServiceProvider' || $action == 'Add') && $sub_action != "sub_category" && $SERVICE_PROVIDER_FLOW == "Provider") {
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label> <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> Cancellation Time Limit ( in minute )<span class="red"></span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="This is the timelimit based on which the <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> would be charged if he/she cancel's the ride after the specified period limit."></i></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="iCancellationTimeLimit"  id="iCancellationTimeLimit" value="<?= $iCancellationTimeLimit; ?>" onblur="checkblanktimelimit('iCancellationTimeLimit','fCancellationFare');">
                                                    
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label> <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> Cancellation Charges  (Price In <?= $db_currency[0]['vName'] ?>)<span class="red"></span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Below mentioned charges would be applied to the <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>s when the <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> cancel's the ride after the specific period of time."></i></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="fCancellationFare"  id="fCancellationFare" value="<?= $fCancellationFare; ?>" onfocus="checkcancellationfare('iCancellationTimeLimit');"> <!-- onchange="getpriceCheck_digit(this.value)" -->
                                                    Note : Cancellation charges would only be applied if the mode of payment is CreditCard.
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label> Waiting Time Limit ( in minute )<span class="red"></span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Waiting charge will be applied if duration exceeds than the defined.
                                                       e.g.: Let's say that the 'Waiting Time Limit' has set to 5 Minutes. From the app, the '<?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>' has marked as arrived and if the <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> has to wait for 8 minutes which is more than 5 minutes(Waiting Time Limit) then in that case the <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?> has to pay for the exceeded 3 minutes based on defined 'Waiting Charges' fees."></i></label>
                                                   </div>
                                                   <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="iWaitingFeeTimeLimit"  min="1" onkeypress="return isNumberKey(event)" id="iWaitingFeeTimeLimit" value="<?= $iWaitingFeeTimeLimit; ?>" onblur="checkblanktimelimit('iWaitingFeeTimeLimit','fWaitingFees');">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label> Waiting Charges  (Price In <?= $db_currency[0]['vName'] ?>)<span class="red"></span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="The defined charges would be applied to the invoice into the total fare when the <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> has to wait for more than the specific defined waiting time prior to starting the <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?>"></i></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="fWaitingFees" min="1" onkeypress="return isNumberKey(event)"  id="fWaitingFees" value="<?= $fWaitingFees; ?>" onfocus="checkcancellationfare('iWaitingFeeTimeLimit');">
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        if (($eCatType == 'ServiceProvider' || $action == 'Add') && $sub_action != "sub_category") {
                                            ?>
                                            <div class="row" id="commisionperdiv">
                                                <div class="col-lg-12">
                                                    <label>Commission Percentage on Waiting/Cancellation/Material - Charges <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Enter the commission percentage for the waiting charge/ Cancellation charge/ Material charge."></i></label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <input class="form-control" min="1" onkeypress="return isNumberKey(event)" type="text" name="fCommision" id="fCommision" required="" value="<?= $fCommision; ?>" onkeyup="this.value = minmax(this.value, 0, 100)" placeholder="Commission Percentage On Material/Misc Fee">
                                                </div>
                                            </div>
                                        <?php } if ($sub_action != "sub_category" && $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Category view Type</label>
                                                </div>
                                                <div class="col-lg-6 ">
                                                    <input checked="checked" id="r4" name="eShowType" type="radio" value="Icon" <?php
                                                    if ($eShowType == 'Icon') {
                                                        echo 'checked';
                                                    }
                                                    ?>>
                                                    <label for="r4">Icon</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <input id="r5" name="eShowType" type="radio" value="Banner" <?php
                                                    if ($eShowType == 'Banner') {
                                                        echo 'checked';
                                                    }
                                                    ?>>
                                                    <label for="r5">Banner</label>
                                                    <input id="r6" name="eShowType" type="radio" value="Icon-Banner" <?php
                                                    if ($eShowType == 'Icon-Banner') {
                                                        echo 'checked';
                                                    }
                                                    ?>>
                                                    <label for="r6">Icon-Banner</label>
                                                </div>
                                            </div>
                                            <div class="row Icon imagebox">
                                                <div class="col-lg-12">
                                                    <label>Logo</label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <? if (isset($vLogo) && $vLogo != '') { ?>                                               
                                                     <!--  <img src="<?= $tconfig['tsite_upload_images_vehicle_category'] . "/" . $id . "/ios/3x_" . $vLogo; ?>" class="logo-preview-img"> -->

                                                     <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=200&h=200&src='.$tconfig['tsite_upload_images_vehicle_category'] . "/" . $id . "/ios/3x_" . $vLogo; ?>" class="logo-preview-img">
                                                     
                                                 <? } else { ?>
                                                    <img src="../assets/img/placeholder-img.png" class="logo-preview-img" />
                                                <?php } ?>
                                                <input type="file" class="form-control" name="vLogo" <?php echo $required_rule; ?> id="vLogo" placeholder="" style="padding-bottom: 39px;">
                                                <br/>
                                                Note: Upload only png image size of 360px*360px.
                                            </div>
                                        </div>
                                        
                                        <div class="row Banner imagebox">
                                            <div class="col-lg-12">
                                                <label>Banner</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($vBannerImage != '') { ?>                                               
                                                 <!--  <img src="<?= $tconfig['tsite_upload_images_vehicle_category'] . "/" . $id . "/" . $vBannerImage; ?>" style="width:200px;"> -->

                                                 <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=400&src='.$tconfig['tsite_upload_images_vehicle_category'] . "/" . $id . "/" . $vBannerImage;  ?>" style="width:200px;">
                                                 
                                             <? } ?>
                                             <input type="file" class="form-control" name="vBannerImage" <?php echo $required_rule; ?> id="vBannerImage" placeholder="" style="padding-bottom: 39px;">
                                             <br/>
                                             Note: Recommended dimension for banner image is 2880 * 1620.
                                         </div>
                                     </div>
                                     <?php
                                 } else {
                                    if ($db_data1[0]['eCatType'] != 'MoreDelivery') { ?>
                                        <!-- <input type="hidden" name="eShowType" value="Icon"> -->
                                        <div class="row Icon imagebox">
                                            <div class="col-lg-12">
                                                <label>Logo</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($vLogo != '') { ?>                                               
                                                    <!-- <img src="<?= $tconfig['tsite_upload_images_vehicle_category'] . "/" . $id . "/ios/3x_" . $vLogo; ?>" style="width:100px;height:100px;"> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=200&h=200&src='.$tconfig['tsite_upload_images_vehicle_category'] . "/" . $id . "/ios/3x_" . $vLogo;  ?>" style="width:100px;height:100px;">
                                                    
                                                <? } ?>
                                                <input type="file" class="form-control" name="vLogo" <?php echo $required_rule; ?> id="vLogo" placeholder="" style="padding-bottom: 39px;">
                                                <br/>
                                                Note: Upload only png image size of 360px*360px.
                                            </div>
                                        </div>
                                    <?php } else if ($db_data1[0]['eCatType'] == 'MoreDelivery' && $db_data1[0]['eFor'] == "DeliverAllCategory") { ?>
                                        <input type="hidden" name="eShowType" value="Banner">
                                        <div class="row Banner imagebox">
                                            <div class="col-lg-12">
                                                <label>Banner</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <? if ($vBannerImage != '') { ?>                                               
                                                    <!-- <img src="<?= $tconfig['tsite_upload_images_vehicle_category'] . "/" . $id . "/" . $vBannerImage; ?>" style="width:200px;"> -->

                                                    <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=400&&src='.$tconfig['tsite_upload_images_vehicle_category'] . "/" . $id . "/" . $vBannerImage; ?>" style="width:200px;">
                                                    
                                                <? } ?>
                                                <input type="file" class="form-control" name="vBannerImage" <?php echo $required_rule; ?> id="vBannerImage" placeholder="" style="padding-bottom: 39px;">
                                                <br/>
                                                Note: Recommended dimension for banner image is 2880 * 1620.
                                            </div>
                                        </div>
                                        <?
                                    }
                                }
                                ?>
                                <input type="hidden" name="iServiceIdEdit" value="<?= $iServiceIdEdit; ?>">
                                <?php
                                if ($sub_action == "sub_category" && ($eCatType == 'ServiceProvider' || $action == 'Add')) {
                                    ?>            
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Allow photo Upload before Job Starts <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="<?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']; ?> can upload the photo, how it looks before service"."></i></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select  class="form-control" name = 'eBeforeUpload'  id= 'eBeforeUpload' required>                                   
                                                <option value="No"<? if ('No' == $db_data[0]['eBeforeUpload']) { ?>selected<? } ?>>No</option>                           
                                                <option value="Yes" <? if ('Yes' == $db_data[0]['eBeforeUpload']) { ?>selected<? } ?>>Yes</option>
                                            </option>                                                    
                                        </select>
                                    </div>
                                </div> 
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Allow photo Upload after Job Completes <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="<?php echo $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']; ?> can upload the photo, how it looks after service"."></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select  class="form-control" name = 'eAfterUpload'  id= 'eAfterUpload' required>                                   
                                            <option value="No"<? if ('No' == $db_data[0]['eAfterUpload']) { ?>selected<? } ?>>No</option>                     
                                            <option value="Yes" <? if ('Yes' == $db_data[0]['eAfterUpload']) { ?>selected<? } ?>>Yes</option>
                                        </option>                                                    
                                    </select>
                                </div>
                            </div> 
                            <?php
                        }
                        ?>
                                                <?php 
                                                    if($iServiceIdEdit > 0 && isEnableTermsServiceCategories()) { 
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Enable Age Feature<span class="red"> *</span></label>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <select  class="form-control" name='eShowTerms'  id= 'eShowTerms' required>
                                                            <option value="Yes" <? if ('Yes' == $eShowTerms) { ?>selected<? } ?>>Yes</option>
                                                            <option value="No"<? if ('No' == $eShowTerms) { ?>selected<? } ?>>No</option>
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Display Order</label>
                            </div>
                            <?php if ($sub_action == "sub_category") { ?>
                                <div class="col-lg-6">
                                    <?
                                    $temp = 1;
                                    $query1 = $obj->MySQLSelect("SELECT count(iVehicleCategoryId) as maxnumber FROM " . $tbl_name . " WHERE iParentId = '" . $sub_cid . "' ORDER BY iDisplayOrder");
                                    $maxnum = isset($query1[0]['maxnumber']) ? $query1[0]['maxnumber'] : 0;
                                    $dataArray = array();
                                    for ($i = 1; $i <= $maxnum; $i++) {
                                        $dataArray[] = $i;
                                        $temp = $iDisplayOrder;
                                    }
                                    ?>
                                    
                                    <select name="iDisplayOrder" class="form-control">
                                        <? foreach ($dataArray as $arr): ?>
                                            <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>" >
                                                -- <?= $arr ?> --
                                            </option>
                                        <? endforeach; ?>
                                        <? if ($action == "Add") { ?>
                                            <option value="<?= $temp; ?>">-- <?= $temp ?> -- </option>
                                        <? } ?>
                                    </select>
                                    
                                </div>
                            <?php } else { ?>
                                
                                <div class="col-lg-6">
                                    <?
                                    $temp = 1;
                                    $query1 = $obj->MySQLSelect("SELECT count(iVehicleCategoryId) as maxnumber FROM " . $tbl_name . " WHERE iParentId = 0 ORDER BY iDisplayOrder");
                                    $maxnum = isset($query1[0]['maxnumber']) ? $query1[0]['maxnumber'] : 0;
                                    $dataArray = array();
                                    for ($i = 1; $i <= $maxnum; $i++) {
                                        $dataArray[] = $i;
                                        $temp = $iDisplayOrder;
                                    }
                                    ?>
                                    
                                    <select name="iDisplayOrder" class="form-control">
                                        <? foreach ($dataArray as $arr): ?>
                                            <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>" >
                                                -- <?= $arr ?> --
                                            </option>
                                        <? endforeach; ?>
                                        <? if ($action == "Add") { ?>
                                            <option value="<?= $temp; ?>">-- <?= $temp ?> -- </option>
                                        <? } ?>
                                    </select>
                                    
                                </div>
                                
                            <?php } ?>
                        </div>
                        <?php if ($userObj->hasPermission('update-status-vehicle-category')) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Status<span class="red"> *</span></label>
                                </div>
                                <div class="col-lg-6">
                                    <select  class="form-control" name = 'eStatus'  id= 'eStatus' required>                                   
                                        <option value="Active" <? if ('Active' == $db_data[0]['eStatus']) { ?>selected<? } ?>>Active</option>
                                        <option value="Inactive"<? if ('Inactive' == $db_data[0]['eStatus']) { ?>selected<? } ?>>Inactive</option>                                                      
                                    </option>                                                    
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <?php if (($action == 'Edit' && $userObj->hasPermission('edit-vehicle-category')) || ($action == 'Add' && $userObj->hasPermission('create-user-profile'))) { ?>
                                <input type="submit" class="save btn-info" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Category">
                                <input type="reset" value="Reset" class="btn btn-default">
                            <?php } ?>
                            <a href="vehicle_category.php" class="btn btn-default back_link">Cancel</a>
                        </div>
                    </div>
                </form>
                <?php if($cubexthemeon == 1 && empty($sub_cid)) { ?>
                </div>
                <div id="Homepagesettings" class="tab-pane <?php if($activetab=='homepage') { ?> active <?php }  ?>">
                    <form id="vtype" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="vehicle_category.php"/>
                        <?php
                        if ($count_all > 0) {
                            for ($i = 0; $i < $count_all; $i++) {
                                $vCode = $db_master[$i]['vCode'];
                                $vTitle = $db_master[$i]['vTitle'];
                                $vSlogan = $db_master[$i]['vSlogan'];

                                $eDefault = $db_master[$i]['eDefault'];
                                $vCatNameHomepageN = 'vCatNameHomepage_' . $vCode;
                                $vCatTitleHomepageN = 'vCatTitleHomepage_' . $vCode;
                                $vCatSloganHomepageN = 'vCatSloganHomepage_' . $vCode;
                                $lCatDescHomepageN = 'lCatDescHomepage_' . $vCode;
                                $vCatDescbtnHomepageN = 'vCatDescbtnHomepage_' . $vCode;
                                $vServiceCatTitleHomepageN = 'vServiceCatTitleHomepage_' . $vCode;

                                ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Name (<?= $vTitle; ?>)</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="<?= $vCatNameHomepageN; ?>" id="<?= $vCatNameHomepageN; ?>" value="<?= $vCatNameHomepageArr[$vCatNameHomepageN]; ?>" placeholder="<?= $vTitle." Value"; ?>">
                                    </div>
                                    <?
                                    if ($vCode == $default_lang && count($db_master) > 1) {
                                        ?>
                                        <div class="col-lg-6">
                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCatNameHomepage');">Convert To All Language</button>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title (<?= $vTitle; ?>)</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name='<?= $vCatTitleHomepageN; ?>' id='<?= $vCatTitleHomepageN; ?>' value="<?= $vCatTitleHomepageArr[$vCatTitleHomepageN]; ?>" placeholder="<?= $vTitle." Value"; ?>">
                                    </div>
                                    <?
                                    if ($vCode == $default_lang && count($db_master) > 1) {
                                        ?>
                                        <div class="col-lg-6">
                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCatTitleHomepage');">Convert To All Language</button>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Slogan (<?= $vTitle; ?>)</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name='<?= $vCatSloganHomepageN; ?>' id='<?= $vCatSloganHomepageN; ?>' value="<?= $vCatSloganHomepageArr[$vCatSloganHomepageN]; ?>" placeholder="<?= $vSlogan." Value"; ?>">
                                    </div>
                                    <?
                                    if ($vCode == $default_lang && count($db_master) > 1) {
                                        ?>
                                        <div class="col-lg-6">
                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCatSloganHomepage');">Convert To All Language</button>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description (<?= $vTitle; ?>)</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <textarea class="form-control" name="<?= $lCatDescHomepageN; ?>" id="<?= $lCatDescHomepageN; ?>" placeholder="<?= $vTitle." Value"; ?>"><?= $lCatDescHomepageArr[$lCatDescHomepageN]; ?></textarea>
                                    </div>
                                    <?
                                    if ($vCode == $default_lang && count($db_master) > 1) {
                                        ?>
                                        <div class="col-lg-6">
                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('lCatDescHomepage');">Convert To All Language</button>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Button text (<?= $vTitle; ?>)</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name='<?= $vCatDescbtnHomepageN; ?>' id='<?= $vCatDescbtnHomepageN; ?>' value="<?= $vCatDescbtnHomepageArr[$vCatDescbtnHomepageN]; ?>" placeholder="<?= $vTitle." Value"; ?>">
                                    </div>
                                    <?
                                    if ($vCode == $default_lang && count($db_master) > 1) {
                                        ?>
                                        <div class="col-lg-6">
                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCatDescbtnHomepage');">Convert To All Language</button>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            <?php } } ?>
                            <div class="row imagebox">
                                <div class="col-lg-12">
                                    <label>Logo</label>
                                </div>
                                <div class="col-lg-6">
                                    <? if (isset($vHomepageLogo) && $vHomepageLogo != '') { ?>
                                        <!-- <img src="<?= $tconfig['tsite_upload_home_page_service_images'] . "/" . $vHomepageLogo; ?>" style="width:100px;height:100px;"> -->

                                        <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=200&h=200&src='.$tconfig['tsite_upload_home_page_service_images'] . "/" . $vHomepageLogo; ?>" style="width:100px;height:100px;">

                                    <? } ?>
                                    <input type="file" class="form-control" name="vHomepageLogo" <?php echo $required_rule; ?> id="vHomepageLogo" placeholder="" style="padding-bottom: 39px;">
                                    <br/>
                                    Note: Upload only png image size of 360px*360px.
                                </div>
                            </div>

                            <div class="row imagebox">
                                <div class="col-lg-12">
                                    <label>Banner</label>
                                </div>
                                <div class="col-lg-6">
                                    <? if (isset($vHomepageBanner) && $vHomepageBanner != '') { ?>
                                        <!-- <img src="<?= $tconfig['tsite_upload_home_page_service_images'] . "/" . $vHomepageBanner; ?>" style="width:200px;"> -->

                                        <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=400&&src='.$tconfig['tsite_upload_home_page_service_images'] . "/" . $vHomepageBanner; ?>" style="width:200px;">

                                    <? } ?>
                                    <input type="file" class="form-control" name="vHomepageBanner" <?php echo $required_rule; ?> id="vHomepageBanner" placeholder="" style="padding-bottom: 39px;">
                                    <br/>
                                    Note: Recommended dimension for banner image is 2880 * 1620.
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Order</label>
                                </div>
                                <div class="col-lg-6">

                                    <input type="hidden" name="temp_orderHomepage" id="temp_orderHomepage" value="<?=($action == 'Edit') ? $iDisplayOrderHomepage_db : '1';?>">
                                    <?
                                    $display_numbers = ($action=="Add") ? $iDisplayOrder_max_Homepage : $iDisplayOrderHomepage;
                                    ?>
                                    <select name="iDisplayOrderHomepage" class="form-control">
                                        <? for($i=1; $i <= $display_numbers; $i++){ ?>
                                            <option value="<?=$i?>" <?if($i == $iDisplayOrderHomepage_db){echo "selected";}?>> -- <?=$i?> --</option>
                                        <? } ?>
                                    </select>

                                </div>
                            </div>
                            
                            <? if($homepage_cubejekx!=0) { ?> 
                                <h3>Shown In Service Section</h3>
                                <?php
                                if ($count_all > 0) {
                                    for ($i = 0; $i < $count_all; $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $vSlogan = $db_master[$i]['vSlogan'];
                                        
                                        $vServiceCatTitleHomepageN = 'vServiceCatTitleHomepage_' . $vCode;
                                        
                                        ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Title (<?= $vTitle; ?>)</label>
                                            </div>
                                            <div class="col-lg-6">
                                                <input type="text" class="form-control" name='<?= $vServiceCatTitleHomepageN; ?>' id='<?= $vServiceCatTitleHomepageN; ?>' value="<?= $vServiceCatTitleHomepageArr[$vServiceCatTitleHomepageN]; ?>" placeholder="<?= $vTitle." Value"; ?>">
                                            </div>
                                            <?
                                            if ($vCode == $default_lang && count($db_master) > 1) {
                                                ?>
                                                <div class="col-lg-6">
                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vServiceCatTitleHomepage');">Convert To All Language</button>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    <?php }} ?>
                                    <div class="row imagebox">
                                        <div class="col-lg-12">
                                            <label>Background Image</label>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                            <? if (isset($vServiceHomepageBanner) && $vServiceHomepageBanner != '') { ?>
                                                <!-- <img src="<?= $tconfig['tsite_upload_home_page_service_images'] . "/" . $vServiceHomepageBanner; ?>" style="width:200px;"> -->

                                                <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=400&&src='.$tconfig['tsite_upload_home_page_service_images'] . "/" . $vServiceHomepageBanner; ?>" style="width:200px;">

                                            <? } ?>
                                            <input type="file" class="form-control" name="vServiceHomepageBanner" <?php echo $required_rule; ?> id="vServiceHomepageBanner" placeholder="" style="padding-bottom: 39px;">
                                            <br/>
                                            Note: Recommended dimension for banner image is 2880 * 1620.
                                        </div>
                                    </div>
                                <? } ?>
                                
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-vehicle-category')) || ($action == 'Add' && $userObj->hasPermission('create-user-profile'))) { ?>
                                            <input type="submit" class="save btn-info" name="btnsubmit_homepage" id="btnsubmit_homepage" value="<?= $action; ?>">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <a href="vehicle_category.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>

        </div>
        <div style="clear:both;"></div>
    </div>

</div>

<!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="imageIcon" style="display:none;">
    <div align="center">                                                                       
        <img src="default.gif">                                                              
        <span>Language Translation is in Process. Please Wait...</span>                       
    </div>                                                                                 
</div>

<? include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>              
<script>
    $(document).ready(function () {
        var referrer;
        <?php if ($goback == 1) { ?>
            alert('<?php echo $var_msg; ?>');
            history.go(-1);
        <?php } ?>
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
                                                    //alert(referrer);
                                                } else {
                                                    referrer = $("#previousLink").val();
                                                }
                                                if (referrer == "") {
                                                    referrer = "vehicles.php";
                                                } else {
                                                    $("#backlink").val(referrer);
                                                }
                                                $(".back_link").attr('href', referrer);
                                                var commisionVal = $("#eMaterialCommision").val();
                                                //showHidePercentage(commisionVal);
                                            });
    $(function () {
        var value = $("#vCategory").val();
        if (value > 0) {
            $(".epricetype").hide();
            $(".eMaterialCommision").hide();
        } else {
            $(".epricetype").show();
            $(".eMaterialCommision").show();
        }
    });
</script>
<script type="text/javascript">



    $(document).ready(function () {
        $('input[name="eShowType"]').click(function () {
            var inputValue = $(this).attr("value");
            var targetBox = $("." + inputValue);
            if (inputValue == "Icon-Banner") {
                $(".Icon").show();
                $(".Banner").show();
                        $(".bannerbutton").show(); // Comment By HJ On 23-01-2019 For Prevent JS Error Like : An invalid form control with name='tBannerButtonText_EN' is not focusable.
                    } else if (inputValue == "Banner") {

                        $(".imagebox").not(targetBox).hide();
                        $(targetBox).show();
                        $(".bannerbutton").show(); // Comment By HJ On 23-01-2019 For Prevent JS Error Like : An invalid form control with name='tBannerButtonText_EN' is not focusable.
                        document.getElementById("<?php echo $tBannerButtonTextdefault; ?>").setAttribute('required', 'required');
                    } else {
                        $(".imagebox").not(targetBox).hide();
                        $(targetBox).show();
                        //$("#vtype").attr('novalidate', 'novalidate');
                        $(".bannerbutton").hide(); // Comment By HJ On 23-01-2019 For Prevent JS Error Like : An invalid form control with name='tBannerButtonText_EN' is not focusable.
                        document.getElementById("<?php echo $tBannerButtonTextdefault; ?>").removeAttribute('required', 'required');
                    }
                });

        var checkvalue = $('input[name="eShowType"]:checked').val();
        if (typeof checkvalue === "undefined") {
            var checkvalue = $('input[name="eShowType"]').val();
        }
        if (checkvalue != '') {
            var targetBox1 = checkvalue;
            if (targetBox1 == 'Icon') {
                $(".Icon").show();
                $(".Banner").hide();
                        //$("#vtype").attr('novalidate', 'novalidate');
                        $(".bannerbutton").hide();  // Comment By HJ On 23-01-2019 For Prevent JS Error Like : An invalid form control with name='tBannerButtonText_EN' is not focusable.
                        document.getElementById("<?php echo $tBannerButtonTextdefault; ?>").removeAttribute('required', 'required');
                    } else if (targetBox1 == 'Banner') {
                        $(".Icon").hide();
                        $(".Banner").show();
                        $(".bannerbutton").show(); // Comment By HJ On 23-01-2019 For Prevent JS Error Like : An invalid form control with name='tBannerButtonText_EN' is not focusable.
                        document.getElementById("<?php echo $tBannerButtonTextdefault; ?>").setAttribute('required', 'required');
                    } else if (targetBox1 == "Icon-Banner") {
                        $(".Icon").show();
                        $(".Banner").show();
                        $(".bannerbutton").show(); // Comment By HJ On 23-01-2019 For Prevent JS Error Like : An invalid form control with name='tBannerButtonText_EN' is not focusable.
                        document.getElementById("<?php echo $tBannerButtonTextdefault; ?>").setAttribute('required', 'required');
                    } else {
                        $(".Icon").show();
                        $(".Banner").hide();
                        //$("#vtype").attr('novalidate', 'novalidate');
                        $(".bannerbutton").hide(); // Comment By HJ On 23-01-2019 For Prevent JS Error Like : An invalid form control with name='tBannerButtonText_EN' is not focusable. 
                        document.getElementById("<?php echo $tBannerButtonTextdefault; ?>").removeAttribute('required', 'required');
                    }
                }
            });

    function getAllLanguageCode(textBoxId) {
        var def_lang = '<?= $default_lang ?>';
        var def_lang_name = '<?= $def_lang_name ?>';
        var getEnglishText = $('#' + textBoxId + '_' + def_lang).val();
        var error = false;
        var msg = '';

        if (getEnglishText == '') {
            msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter ' + def_lang_name + ' Value</strong></div> <br>';
            error = true;
        }

        if (error == true) {
            $('#errorMessage').html(msg);
            return false;
        } else {
            $('#imageIcon').show();
            $.ajax({
                url: "ajax_get_all_language_translate.php",
                type: "post",
                data: {'englishText': getEnglishText},
                dataType: 'json',
                success: function (response) {
                    $.each(response, function (name, Value) {
                        var key = name.split('_');
                        $('#' + textBoxId + '_' + key[1]).val(Value);
                    });
                    $('#imageIcon').hide();
                }
            });
        }
    }
    function minmax(value, min, max) {
        if (parseInt(value) < min || isNaN(value))
            return 0;
        else if (parseInt(value) > max)
            return 100;
        else
            return value;
    }
    function showHidePercentage(commision) {
        $("#commisionperdiv").hide();
        if (commision == "Yes") {
            $("#commisionperdiv").show();
        }
    }
    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 47 && charCode < 58 || charCode == 46 || charCode == 127 || charCode == 8)
            return true;
        return false;
    }
    
            //added by SP 27-06-2019 start
            var timeLimit = $("#iCancellationTimeLimit").val();
            if (timeLimit.trim() == '') {
                $("#fCancellationFare").val('');
            }

            var timeLimit = $("#iWaitingFeeTimeLimit").val();
            if (timeLimit.trim() == '') {
                $("#fWaitingFees").val('');
            }
            
            function checkcancellationfare(idval) {
                var timeLimit = $("#"+idval).val();
                if (timeLimit.trim() == '') {
                    document.getElementById(idval).focus();
                    $("#"+idval).val('');
                    return false;
                }
            }
            function checkblanktimelimit(idval,idcanval) {
                var timeLimit = $("#"+idval).val();
                if (timeLimit.trim() == '') {
                    $("#"+idcanval).val('');
                }
            }
            //added by SP 27-06-2019 end

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('.logo-preview-img').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#vLogo").change(function() {
                readURL(this);
            });
        </script>
    </body>
    <!-- END BODY-->
    </html>