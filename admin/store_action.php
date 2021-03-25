<?php
//ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL);
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

if (checkSystemStoreSelection()) {
    header("Location:store.php");
    exit;
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'company';
$script = 'DeliverAllStore';
$eSystem = 'DeliverAll';
$sql = "select iCountryId,vCountry,vCountryCode from country ORDER BY vCountry ASC ";
// $sql = "SELECT iCountryId,vCountryCode,vCountry FROM country WHERE eStatus='Active' AND iCountryId='101' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);
$sql = "select vCode,vTitle from language_master where eStatus = 'Active' order by vTitle asc";
$db_lang = $obj->MySQLSelect($sql);

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
}

// set all variables with either post (when submit) either blank (when insert)
$vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
$iServiceIdNew = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '';
$vEmail = isset($_POST['vEmail']) ? strtolower($_POST['vEmail']) : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPass = ($vPassword != "") ? $generalobj->encrypt_bycrypt($vPassword) : '';
$vContactName = isset($_POST['vContactName']) ? $_POST['vContactName'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vState = isset($_POST['vState']) ? $_POST['vState'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vRestuarantLocation = isset($_POST['vRestuarantLocation']) ? $_POST['vRestuarantLocation'] : '';
$vRestuarantLocationLat = isset($_POST['vRestuarantLocationLat']) ? $_POST['vRestuarantLocationLat'] : '';
$vRestuarantLocationLong = isset($_POST['vRestuarantLocationLong']) ? $_POST['vRestuarantLocationLong'] : '';
$vCaddress = isset($_POST['vCaddress']) ? $_POST['vCaddress'] : '';
$vZip = isset($_POST['vZip']) ? $_POST['vZip'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vAcctHolderName = isset($_POST['vAcctHolderName']) ? $_POST['vAcctHolderName'] : '';
$vAcctNo = isset($_POST['vAcctNo']) ? $_POST['vAcctNo'] : '';
$vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
$vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
$vSwiftCode = isset($_POST['vSwiftCode']) ? $_POST['vSwiftCode'] : '';
$cuisineId = isset($_POST['cuisineId']) ? $_POST['cuisineId'] : '';
$vFromMonFriTimeSlot1 = isset($_POST['vFromMonFriTimeSlot1']) ? $_POST['vFromMonFriTimeSlot1'] : '';
$vToMonFriTimeSlot1 = isset($_POST['vToMonFriTimeSlot1']) ? $_POST['vToMonFriTimeSlot1'] : '';
$vFromMonFriTimeSlot2 = isset($_POST['vFromMonFriTimeSlot2']) ? $_POST['vFromMonFriTimeSlot2'] : '';
$vToMonFriTimeSlot2 = isset($_POST['vToMonFriTimeSlot2']) ? $_POST['vToMonFriTimeSlot2'] : '';
$vFromSatSunTimeSlot1 = isset($_POST['vFromSatSunTimeSlot1']) ? $_POST['vFromSatSunTimeSlot1'] : '';
$vToSatSunTimeSlot1 = isset($_POST['vToSatSunTimeSlot1']) ? $_POST['vToSatSunTimeSlot1'] : '';
$vFromSatSunTimeSlot2 = isset($_POST['vFromSatSunTimeSlot2']) ? $_POST['vFromSatSunTimeSlot2'] : '';
$vToSatSunTimeSlot2 = isset($_POST['vToSatSunTimeSlot2']) ? $_POST['vToSatSunTimeSlot2'] : '';
$fMinOrderValue = isset($_POST['fMinOrderValue']) ? $_POST['fMinOrderValue'] : '';
$fPackingCharge = isset($_POST['fPackingCharge']) ? $_POST['fPackingCharge'] : '';
$iMaxItemQty = isset($_POST['iMaxItemQty']) ? $_POST['iMaxItemQty'] : '';
$fPrepareTime = isset($_POST['fPrepareTime']) ? $_POST['fPrepareTime'] : '';
$fOfferAppyType = isset($_POST['fOfferAppyType']) ? $_POST['fOfferAppyType'] : '';
$fOfferType = isset($_POST['fOfferType']) ? $_POST['fOfferType'] : '';
$fTargetAmt = isset($_POST['fTargetAmt']) ? $_POST['fTargetAmt'] : '';
$fOfferAmt = isset($_POST['fOfferAmt']) ? $_POST['fOfferAmt'] : '';
$fMaxOfferAmt = isset($_POST['fMaxOfferAmt']) ? $_POST['fMaxOfferAmt'] : '';
$fPricePerPerson = isset($_POST['fPricePerPerson']) ? $_POST['fPricePerPerson'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vFromMonFriTimeSlot1_validation = explode(" ", $vFromMonFriTimeSlot1);
$storeCategoryIds = isset($_POST['storeCategoryId']) ? $_POST['storeCategoryId'] : '';
$eTakeaway = isset($_POST['eTakeaway']) ? $_POST['eTakeaway'] : '';
$eDriverOption = isset($_POST['eDriverOption']) ? $_POST['eDriverOption'] : 'Site';
$eSafetyPractices = isset($_POST['eSafetyPractices']) ? $_POST['eSafetyPractices'] : '';

if ($vFromMonFriTimeSlot1_validation[0] == "00:00") {
    $vFromMonFriTimeSlot1_req = "";
} else {
    $vFromMonFriTimeSlot1_req = $vFromMonFriTimeSlot1_validation[0];
}
if (!empty($_POST)) {
    if (SITE_TYPE == 'Demo') {
        header("Location:store_action.php?id=" . $id . '&success=2');
        exit;
    }
    //Add Custom validation
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vCompany'], 'req', 'Restaurant Name is required');
    $validobj->add_fields(strtolower($_POST['vEmail']), 'req', 'Email Address is required.');
    if ($action == "Add") {
        $validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
    }
    $validobj->add_fields($_POST['vPhone'], 'req', 'Phone Number is required.');
    $validobj->add_fields($_POST['vCaddress'], 'req', 'Address is required.');
    $validobj->add_fields($_POST['vZip'], 'req', 'Zip Code is required.');
    $validobj->add_fields($_POST['vLang'], 'req', 'Language is required.');
    $validobj->add_fields($_POST['vCountry'], 'req', 'Country is required.');
    $error = $validobj->validate();
    if ($vEmail != "") {
        if ($id != "") {
            $msg1 = $generalobj->checkDuplicateAdminNew('iCompanyId', 'company', array('vEmail'), $id, "");
        } else {
            $msg1 = $generalobj->checkDuplicateAdminNew('vEmail', 'company', array('vEmail'), "", "");
        }
        if ($msg1 == 1) {
            $error .= 'Email Address is already exists.<br>';
        }
    }
    //comment by Rs bcz check Email,phone validation using checkMemberDataInfo function
    /* if ($vPhone != "") {
      if ($id != "") {
      $msg1 = $generalobj->checkDuplicateAdminNew('iCompanyId', 'company', Array('vPhone'), $id, "");
      } else {
      $msg1 = $generalobj->checkDuplicateAdminNew('vPhone', 'company', Array('vPhone'), "", "");
      }
      if ($msg1 == 1) {
      $error .= 'Phone number is already exists.<br>';
      }
      } */

    /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
    $checPhoneExist = $generalobj->checkMemberDataInfo($vPhone, "", 'COMPANY', $vCountry, $id, $eSystem);
    if ($checPhoneExist['status'] == 0) {
        $error .= '* Phone number already exists.<br>';
    } else if ($checPhoneExist['status'] == 2) {
        $error .= $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
    }
    /* 06-09-219 check phone validation end */
    $error .= $validobj->validateFileType($_FILES['vImage'], 'jpg,jpeg,png,gif,bmp', '* Image file is not valid.');
    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        $sql = "select vPhoneCode from country where vCountryCode = '$vCountry'";
        $db_country_data = $obj->MySQLSelect($sql);
        if ($vCode == "") {
            $vCode = $db_country_data[0]['vPhoneCode'];
        }
        $CompanyData['vCompany'] = stripslashes($vCompany);
        $CompanyData['vEmail'] = stripslashes($vEmail);
        if ($vPass != "") {
            $CompanyData['vPassword'] = stripslashes($vPass);
        }
        $CompanyData ['vContactName'] = stripslashes($vContactName);
        $CompanyData ['vCode'] = $vCode;
        $CompanyData ['vPhone'] = $vPhone;
        $CompanyData ['vCountry'] = $vCountry;
        $CompanyData ['vState'] = $vState;
        $CompanyData ['vCity'] = $vCity;
        $CompanyData ['vRestuarantLocation'] = stripcslashes($vRestuarantLocation);
        $CompanyData ['vRestuarantLocationLat'] = $vRestuarantLocationLat;
        $CompanyData ['vRestuarantLocationLong'] = $vRestuarantLocationLong;
        $CompanyData ['vCaddress'] = stripslashes($vCaddress);
        $CompanyData ['vZip'] = $vZip;
        $CompanyData ['vLang'] = $vLang;
        $CompanyData ['vAcctHolderName'] = stripslashes($vAcctHolderName);
        $CompanyData ['vAcctNo'] = $vAcctNo;
        $CompanyData ['vBankName'] = stripslashes($vBankName);
        $CompanyData ['vBankLocation'] = $vBankLocation;
        $CompanyData ['vSwiftCode'] = $vSwiftCode;
        $CompanyData ['iServiceId'] = $iServiceIdNew;

        $vFromMonFriTimeSlot1_arr = explode(" ", $vFromMonFriTimeSlot1);
        $CompanyData['vFromMonFriTimeSlot1'] = $vFromMonFriTimeSlot1_arr[0];

        $vToMonFriTimeSlot1_arr = explode(" ", $vToMonFriTimeSlot1);
        $CompanyData['vToMonFriTimeSlot1'] = $vToMonFriTimeSlot1_arr[0];

        $vFromMonFriTimeSlot2_arr = explode(" ", $vFromMonFriTimeSlot2);
        $CompanyData['vFromMonFriTimeSlot2'] = $vFromMonFriTimeSlot2_arr[0];

        $vToMonFriTimeSlot2_arr = explode(" ", $vToMonFriTimeSlot2);
        $CompanyData['vToMonFriTimeSlot2'] = $vToMonFriTimeSlot2_arr[0];

        $vFromSatSunTimeSlot1_arr = explode(" ", $vFromSatSunTimeSlot1);
        $CompanyData['vFromSatSunTimeSlot1'] = $vFromSatSunTimeSlot1_arr[0];

        $vToSatSunTimeSlot1_arr = explode(" ", $vToSatSunTimeSlot1);
        $CompanyData['vToSatSunTimeSlot1'] = $vToSatSunTimeSlot1_arr[0];

        $vFromSatSunTimeSlot2_arr = explode(" ", $vFromSatSunTimeSlot2);
        $CompanyData['vFromSatSunTimeSlot2'] = $vFromSatSunTimeSlot2_arr[0];

        $vToSatSunTimeSlot2_arr = explode(" ", $vToSatSunTimeSlot2);
        $CompanyData['vToSatSunTimeSlot2'] = $vToSatSunTimeSlot2_arr[0];

        $CompanyData['fMinOrderValue'] = $fMinOrderValue;
        $CompanyData['fPackingCharge'] = $fPackingCharge;
        $CompanyData['iMaxItemQty'] = $iMaxItemQty;
        $CompanyData['fPrepareTime'] = $fPrepareTime;
        $CompanyData['fOfferAppyType'] = $fOfferAppyType;
        $CompanyData['fOfferType'] = $fOfferType;
        $CompanyData['fTargetAmt'] = $fTargetAmt;
        $CompanyData['fOfferAmt'] = $fOfferAmt;
        $CompanyData['fMaxOfferAmt'] = $fMaxOfferAmt;
        $CompanyData['eTakeaway'] = $eTakeaway;
        $CompanyData['eSafetyPractices'] = $eSafetyPractices;
        
	$CompanyData['eDriverOption'] = $eDriverOption;
        if ($fOfferAppyType == 'None') {
            $CompanyData['fTargetAmt'] = $CompanyData['fOfferAmt'] = $CompanyData['fMaxOfferAmt'] = 0;
        }
        if ($fOfferType == 'Flat') {
            $CompanyData['fMaxOfferAmt'] = 0;
        }

        $CompanyData['fPricePerPerson'] = $fPricePerPerson;
        if ($action == 'Add') {
            $CompanyData['tRegistrationDate'] = date("Y-m-d H:i:s");
        }
        $CompanyData['eSystem'] = $eSystem;
        if ($id != '') {
            $cQuery = 'SELECT vEmail,vPhone FROM company WHERE  `iCompanyId` = "' . $id . '"';
            $CompanyOldData = $obj->MySQLSelect($cQuery);
            $OldEmail = $CompanyOldData[0]['vEmail'];
            $OldPhone = $CompanyOldData[0]['vPhone'];
            if ($OldEmail != '' && $vEmail != '') {
                if ($OldEmail != $vEmail) {
                    $CompanyData['eAvailable'] = 'No';
                    $CompanyData['eEmailVerified'] = 'No';
                }
            }
            if ($OldPhone != '' && $vPhone != '') {
                if ($OldPhone != $vPhone) {
                    $CompanyData['eAvailable'] = 'No';
                    $CompanyData['ePhoneVerified'] = 'No';
                }
            }
            $where = " `iCompanyId` = '" . $id . "'";
            $company_id = $obj->MySQLQueryPerform($tbl_name, $CompanyData, 'update', $where);
        } else {
            $company_id = $obj->MySQLQueryPerform($tbl_name, $CompanyData, 'insert');
        }
        $id = ($id != '') ? $id : $company_id;
        $q = "SELECT count(ccId) as total_cuisine FROM company_cuisine WHERE iCompanyId ='" . $id . "'";
        $CuisineOldData = $obj->MySQLSelect($q);
        if ($CuisineOldData[0]['total_cuisine'] > 0) {
            $q1 = "DELETE FROM company_cuisine WHERE `iCompanyId`='" . $id . "'";
            $oldid = $obj->sql_query($q1);
        }
        foreach ($cuisineId as $key => $value) {
            $cusdata['iCompanyId'] = $id;
            $cusdata['cuisineId'] = $value;
            $cusine_id = $obj->MySQLQueryPerform('company_cuisine', $cusdata, 'insert');
        }
        if ($_FILES['vImage']['name'] != "") {
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            $img_path = $tconfig["tsite_upload_images_compnay_path"];
            $temp_gallery = $img_path . '/';
            $check_file = $img_path . '/' . $id . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $id . '/' . $oldImage);
                @unlink($img_path . '/' . $id . '/1_' . $oldImage);
                @unlink($img_path . '/' . $id . '/2_' . $oldImage);
                @unlink($img_path . '/' . $id . '/3_' . $oldImage);
            }
            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);

            if ($img1 != '') {
                if (is_file($Photo_Gallery_folder . $img1)) {
                    include_once(TPATH_CLASS . "/SimpleImage.class.php");
                    $img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }
            }
            $vImgName = $img1;
            $sql = "UPDATE " . $tbl_name . " SET `vImage` = '" . $vImgName . "' WHERE `iCompanyId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
        if ($_FILES['vCoverImage']['name'] != "") {
            $image_object = $_FILES['vCoverImage']['tmp_name'];
            $image_name = $_FILES['vCoverImage']['name'];
            $img_path = $tconfig["tsite_upload_images_compnay_path"];
            $temp_gallery = $img_path . '/';
            $check_file = $img_path . '/' . $id . '/' . $oldvCoverImageImage;
            if ($oldvCoverImageImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $id . '/' . $oldvCoverImageImage);
                @unlink($img_path . '/' . $id . '/1_' . $oldvCoverImageImage);
                @unlink($img_path . '/' . $id . '/2_' . $oldvCoverImageImage);
                @unlink($img_path . '/' . $id . '/3_' . $oldvCoverImageImage);
            }
            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
            if ($img1 != '') {
                if (is_file($Photo_Gallery_folder . $img1)) {
                    include_once(TPATH_CLASS . "/SimpleImage.class.php");
                    $img = new SimpleImage();
                    $img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_cover_size3"], $tconfig["tsite_upload_images_cover_size4"], $tconfig["tsite_upload_images_cover_size5"], "");
                }
            }
            $vImgName = $img1;
            $sql = "UPDATE " . $tbl_name . " SET `vCoverImage` = '" . $vImgName . "' WHERE `iCompanyId` = '" . $id . "'";
            $obj->sql_query($sql);
        }

        $q1 = "SELECT count(id) as total_categories FROM store_category_tags WHERE iCompanyId ='" . $id . "'";
        $SCategoryOldData = $obj->MySQLSelect($q1);
        if ($SCategoryOldData[0]['total_categories'] > 0) {
            $q1 = "DELETE FROM store_category_tags WHERE `iCompanyId`='" . $id . "'";
            $obj->sql_query($q1);
        }
        foreach ($storeCategoryIds as $skey => $svalue) {
            $cusdata1['iCompanyId'] = $id;
            $cusdata1['iCategoryId'] = $svalue;
            $obj->MySQLQueryPerform('store_category_tags', $cusdata1, 'insert');
        }

        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        header("location:" . $backlink);
    }
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iCompanyId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $sql1 = "SELECT cuisineId FROM `company_cuisine` WHERE iCompanyId = '" . $id . "'";
    $db_cusinedata = $obj->MySQLSelect($sql1);
    foreach ($db_cusinedata as $key => $value) {
        $cusineselecteddata[] = $value['cuisineId'];
    }

    $sql3 = "SELECT iCategoryId FROM `store_category_tags` WHERE iCompanyId = '" . $id . "'";
    $db_store_category_tags = $obj->MySQLSelect($sql3);
    foreach ($db_store_category_tags as $tkey => $tvalue) {
        $storecatselecteddata[] = $tvalue['iCategoryId'];
    }

    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCompany = $value['vCompany'];
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
            $vPassword = $value['vPassword'];
            $vContactName = $generalobjAdmin->clearName($value['vContactName']);
            $vCode = $value['vCode'];
            $vPhone = $generalobjAdmin->clearPhone($value['vPhone']);
            $vCountry = $value['vCountry'];
            $vCity = $value['vCity'];
            $vState = $value['vState'];
            $vRestuarantLocation = $value['vRestuarantLocation'];
            $vRestuarantLocationLat = $value['vRestuarantLocationLat'];
            $vRestuarantLocationLong = $value['vRestuarantLocationLong'];
            $vCaddress = $value['vCaddress'];
            $vZip = $value['vZip'];
            $vLang = $value['vLang'];
            $oldImage = $value['vImage'];
            $oldvCoverImageImage = $value['vCoverImage'];
            $vAcctHolderName = $value['vAcctHolderName'];
            $vAcctNo = $value['vAcctNo'];
            $vBankName = $value['vBankName'];
            $vBankLocation = $value['vBankLocation'];
            $vSwiftCode = $value['vSwiftCode'];
            $vFromMonFriTimeSlot1 = $value['vFromMonFriTimeSlot1'];
            $vToMonFriTimeSlot1 = $value['vToMonFriTimeSlot1'];
            $vFromMonFriTimeSlot2 = $value['vFromMonFriTimeSlot2'];
            $vToMonFriTimeSlot2 = $value['vToMonFriTimeSlot2'];
            $vFromSatSunTimeSlot1 = $value['vFromSatSunTimeSlot1'];
            $vToSatSunTimeSlot1 = $value['vToSatSunTimeSlot1'];
            $vFromSatSunTimeSlot2 = $value['vFromSatSunTimeSlot2'];
            $vToSatSunTimeSlot2 = $value['vToSatSunTimeSlot2'];
            $fMinOrderValue = $value['fMinOrderValue'];
            $fPackingCharge = $value['fPackingCharge'];
            $iMaxItemQty = $value['iMaxItemQty'];
            $fPrepareTime = $value['fPrepareTime'];
            $fOfferAppyType = $value['fOfferAppyType'];
            $fOfferType = $value['fOfferType'];
            $fTargetAmt = $value['fTargetAmt'];
            $fOfferAmt = $value['fOfferAmt'];
            $fPricePerPerson = $value['fPricePerPerson'];
            $fMaxOfferAmt = $value['fMaxOfferAmt'];
            $iServiceIdNew = $value['iServiceId'];
            $eTakeaway = $value['eTakeaway'];
            $eDriverOption = $value['eDriverOption'];
            $eSafetyPractices = $value['eSafetyPractices'];
        }
    }

    // Added by HV for store categories (service categories wise)
    $sql22 = '';
    if($iServiceIdNew != "") {
        $sql22 = " AND iServiceId = ".$iServiceIdNew;
    }
}
else{
    // Added by HV for store categories (service categories wise)
    $sql22 = '';
    if(count($iServiceIdArr)==1) {
        $iServiceIdNew = $iServiceIdArr[0];
        $sql22 = " AND iServiceId = ".$iServiceIdNew;
    }
}
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_" . $default_lang . " as servicename,eStatus FROM service_categories WHERE iServiceId IN (" . $serviceIds . ") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);

$selectcuisine_sql = "SELECT cuisineId,cuisineName_" . $default_lang . " FROM cuisine WHERE  iServiceId = '" . $iServiceIdNew . "' AND eStatus = 'Active'";
$db_cuisine = $obj->MySQLSelect($selectcuisine_sql);

$sql2 = "SELECT iCategoryId,tCategoryName FROM `store_categories` WHERE eType = 'manual'".$sql22;
$db_store_categories = $obj->MySQLSelect($sql2);
$isStoreDriver = isStoreDriverAvailable(); // Added By HJ On 20-03-2020 For Check Manage Driver By Store Enable
$takeaway = isTakeAwayEnable() ? "Yes" : "No";
$SafetyPractices = checkSafetyPractice() ? "Yes" : "No";
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
        <script type='text/javascript' src='../assets/map/gmaps.js'></script>
        <link rel="stylesheet" href="css/select2/select2.min.css" type="text/css" >
        <script type="text/javascript" src="js/plugins/select2.min.js"></script>
        <script type='text/javascript' src='../assets/js/bootbox.min.js'></script>
        <style>.selection .select2-selection{height: auto !Important}</style>

    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php
            include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> <?= $vCompany; ?></h2>
                            <a class="back_link" href="store.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    "Edit / Delete Record Feature" has been disabled on the Demo <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Panel. This feature will be enabled on the main script we will provide you.
                                </div><br/>
                            <?php } ?>
                            <?php if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php print_r($error); ?>
                                </div><br/>
                            <?php } ?>
                            <form name="_company_form" id="_company_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="iCompanyId" value="<?php echo $id; ?>"/>
                                <input type="hidden" name="oldImage" value="<?= $oldImage; ?>"/>
                                <input type="hidden" name="oldvCoverImageImage" value="<?= $oldvCoverImageImage; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="store.php"/>
                                <?php if ($action == 'Edit') { ?>
                                    <input type="hidden" name="iServiceId"  value="<?php echo $iServiceIdNew; ?>">
                                <?php } ?>
                                <!-- Changes For Restaurant -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Name<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vCompany"  id="vCompany" value="<?= $generalobjAdmin->clearName(" " . $vCompany); ?>" placeholder="<?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Name">
                                            </div>
                                        </div>
                                        <?php
                                        if ($action == 'Add') {
                                            if (count($allservice_cat_data) <= 1) {
                                                ?>
                                                <input name="iServiceId" type="hidden" class="create-account-input" value="<?php echo $service_cat_list[0]['iServiceId']; ?>" id="iServiceId"/>
                                            <?php } else { ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Service Type<span class="red"> *</span></label>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <select class="form-control" name = 'iServiceId' id="iServiceId" onchange="changeServiceType(this.value)">
                                                            <option value="">Select</option>
                                                            <?php for ($i = 0; $i < count($service_cat_list); $i++) { ?>
                                                                <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>" <?php if ($iServiceIdNew == $service_cat_list[$i]['iServiceId']) { ?>selected<?php } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>


                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Email<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vEmail" id="vEmail" value="<?= $vEmail; ?>" placeholder="Email">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Password<span class="red"> *</span>
                                                    <?php if ($action == 'Edit') { ?>
                                                        <span>&nbsp;[Leave blank to retain assigned password.]</span>
                                                    <?php } ?>
                                                </label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="password" class="form-control" name="vPassword"  id="vPassword" value="" placeholder="Password">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Location<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" id="vRestuarantLocation" class="form-control" name="vRestuarantLocation"  id="vRestuarantLocation" value="<?= $vRestuarantLocation; ?>" placeholder="<?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Location" required>
                                            </div>
                                            <input type="hidden" name="vRestuarantLocationLat" id="vRestuarantLocationLat" value="<?= $vRestuarantLocationLat ?>">
                                            <input type="hidden" name="vRestuarantLocationLong" id="vRestuarantLocationLong" value="<?= $vRestuarantLocationLong ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="map" style="width:100%;height:200px;"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Address<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vCaddress"  id="vCaddress" value="<?= $vCaddress; ?>" placeholder="Address" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Zip Code<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vZip"  id="vZip" value="<?= $vZip; ?>" placeholder="Zip Code" >
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Country <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <?php 
                                                    if(count($db_country) > 1){ 
                                                            $style = "";
                                                         }else{
                                                            $style = " disabled=disabled ";
                                                    } ?>
                                                <select <?= $style ?> class="form-control" name = 'vCountry' id="vCountry" onChange="setState(this.value, '');changeCode(this.value);" required>
                                                    <?php 
                                                        if(count($db_country) > 1){ ?>
                                                            <option value="">Select</option>
                                                         <?php } ?>
                                                    <?php for ($i = 0; $i < count($db_country); $i++) { ?>
                                                        <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                    <? } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>State <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name = 'vState' id="vState" onChange="setCity(this.value, '');" required>
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </div>

                                        <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>City </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <select class="form-control" name = 'vCity' id="vCity"  >
                                                        <option value="">Select</option>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Contact Person Name<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vContactName"  id="vContactName" value="<?= $vContactName; ?>" placeholder="Person Name" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Phone Number<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12" style="float: left;">
                                                <input type="text" class="form-select-2" id="code" name="vCode" value="<?= $vCode ?>"  readonly style="width: 10%;height: 36px;text-align: center;"/ >
                                                       <input type="text" class="form-control" name="vPhone"  id="vPhone" value="<?= $vPhone; ?>" placeholder="Phone" style="margin-top: 5px; width:90%;" required>
                                            </div>
                                        </div>
                                        <?php if ($id) { ?>
                                            <div class= "row col-md-12" id="hide-profile-div">
                                                <?php $class = "col-lg-12"; ?>
                                                <div class="<?= $class ?>">
                                                    <b>
                                                        <?php if ($oldImage == 'NONE' || $oldImage == '') { ?>
                                                            <img src="../assets/img/profile-user-img.png" alt="" >
                                                            <?php
                                                        } else {
                                                            if (file_exists('../webimages/upload/Company/' . $id . '/' . $oldImage)) {
                                                                ?>
                                                                <img src = "<?php echo $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_images_compnay"] . '/' . $id . '/' . $oldImage ?>"  style="width: 150px;height: auto"/>
                                                            <?php } else { ?>
                                                                <img src="../assets/img/profile-user-img.png" alt="" >
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </b>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Logo<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="file" class="form-control" name="vImage"  id="vImage" style="padding-bottom: 39px;" <?php if ((!empty($id) && ($oldImage == 'NONE' || $oldImage == '')) || empty($id)) { ?> required <?php } ?>><!-- Required validation added by SP on 26-7-2019 -->
                                                <b>[Note: Recommended dimension is 2048px * 2048px.]</b>
                                            </div>
                                        </div>
                                        <!--  <?php if ($id) { ?>
                                                                                                 <div class= "row col-md-12" id="hide-profile-div">
                                            <? $class = "col-lg-12"; ?>
                                                                                                      <div class="<?= $class ?>">
                                                                                                           <b>
                                            <?php if ($oldvCoverImageImage == 'NONE' || $oldvCoverImageImage == '') { ?>
                                                                                                                                                                         <img src="../assets/img/profile-user-img.png" alt="" >
                                                <?
                                            } else {
                                                if (file_exists('../webimages/upload/Company/' . $id . '/3_' . $oldvCoverImageImage)) {
                                                    ?>
                                                                                                                                                                                                     <img src = "<?php echo $tconfig["tsite_upload_images_compnay"] . '/' . $id . '/3_' . $oldvCoverImageImage ?>" style="width: 60%;height: auto"/>
                                                <?php } else { ?>
                                                                                                                                                                                                     <img src="../assets/img/profile-user-img.png" alt="" >
                                                    <?php
                                                }
                                            }
                                            ?>
                                                                                   </b>
                                                                              </div>
                                                                         </div>
                                        <?php } ?>
                     <div class="row">
                          <div class="col-lg-12">
                               <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Cover Photo</label>
                          </div>
                          <div class="col-lg-12">
                               <input type="file" class="form-control" name="vCoverImage"  id="vCoverImage" style="padding-bottom: 39px;">
                          </div>
                     </div>-->


                                        <?php if (count($db_lang) <= 1) { ?>
                                            <input name="vLang" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode']; ?>"/>
                                        <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Language<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <select  class="form-control" name = 'vLang' >
                                                        <option value="">--select--</option>
                                                        <?php for ($i = 0; $i < count($db_lang); $i++) { ?>
                                                            <option value = "<?= $db_lang[$i]['vCode'] ?>" <?= ($db_lang[$i]['vCode'] == $vLang) ? 'selected' : ''; ?>>
                                                                <?= $db_lang[$i]['vTitle'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <? if($takeaway=='Yes') { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $langage_lbl_admin['LBL_TAKE_AWAY'] ?></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="eTakeaway">
                                                    <!--<option value=''><?= $langage_lbl_admin['LBL_SELECT_TXT']; ?></option>-->
                                                    <option value="Yes" <?php if ($eTakeaway == 'Yes') { echo 'selected'; } ?>><?= $langage_lbl_admin['LBL_BTN_YES_TXT'] ?></option>
                                                    <option value="No" <?php if ($eTakeaway == 'No') { echo 'selected'; } ?>><?= $langage_lbl_admin['LBL_BTN_NO_TXT'] ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <? } if($SafetyPractices=='Yes') { ?>
                                        <div class="row" id="eSafetyPractices" style="<? if($iServiceIdNew==1 || $iServiceIdNew==2) { ?>display:block<? } else { ?>display:none<? } ?>">
                                            <div class="col-lg-12">
                                                <label><?= $langage_lbl_admin['LBL_SAFETY_PRACTICES'] ?></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="eSafetyPractices">
                                                    <!--<option value=''><?= $langage_lbl_admin['LBL_SELECT_TXT']; ?></option>-->
                                                    <option value="Yes" <?php if ($eSafetyPractices == 'Yes') { echo 'selected'; } ?>><?= $langage_lbl_admin['LBL_BTN_YES_TXT'] ?></option>
                                                    <option value="No" <?php if ($eSafetyPractices == 'No' || $eSafetyPractices == '') { echo 'selected'; } ?>><?= $langage_lbl_admin['LBL_BTN_NO_TXT'] ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <? } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-store')) || ($action == 'Add' && $userObj->hasPermission('create-store'))) { ?>
                                                    <input type="submit" class="btn btn-default" name="submitBtn" id="submitBtn" value="<?= $action; ?> <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>" >

                                                    <input type="reset" value="Reset" class="btn btn-default">
                                                <?php } ?>
                                                <a href="store.php" class="btn btn-default back_link">Cancel</a>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Select <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Category</label>
                                            </div>
                                            <?php if (count($db_store_categories)) { ?>
                                                <div class="col-lg-12">
                                                    <select class="form-control"  id="js-store-category-multiple" name="storeCategoryId[]" multiple="multiple">
                                                        <?php
                                                        foreach ($db_store_categories as $store_category) {
                                                            $category_name = (array) json_decode($store_category['tCategoryName']);
                                                            ?>
                                                            <option value="<?= $store_category['iCategoryId'] ?>" <?php echo (isset($storecatselecteddata) && in_array($store_category['iCategoryId'], $storecatselecteddata)) ? 'selected="selected"' : ""; ?> style="display: none;"><?= $category_name['tCategoryName_' . $default_lang] ?></option>
                                                        <?php } ?>    
                                                    </select>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Available <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Item Types<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" required=""  id="js-cuisine-multiple" name="cuisineId[]" multiple="multiple">
                                                    <?php foreach ($db_cuisine as $cuisinedata) { ?>
                                                        <option name="<?= $cuisinedata['cuisineId'] ?>" value="<?= $cuisinedata['cuisineId'] ?>" <?php echo (isset($cusineselecteddata) && in_array($cuisinedata['cuisineId'], $cusineselecteddata)) ? 'selected="selected"' : ""; ?>><?= $cuisinedata["cuisineName_" . $default_lang] ?></option>
                                                    <?php } ?>
                                                </select>
                                                <div class="CuisineClass">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Slot1: Monday to Friday<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vFromMonFriTimeSlot1'>
                                                            <input type='text' class="form-control TimeField" name="vFromMonFriTimeSlot1" id= "vFromMonFriTimeSlot1-1" value="<?= $vFromMonFriTimeSlot1; ?>" required/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                        <span class="FromError1"></span>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vToMonFriTimeSlot1'>
                                                            <input type='text' class="form-control TimeField" name="vToMonFriTimeSlot1" id="vToMonFriTimeSlot-2" value="<?= $vToMonFriTimeSlot1; ?>" required/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                        <span class="ToError1"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Slot2 : Monday to Friday</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vFromMonFriTimeSlot2'>
                                                            <input type='text' class="form-control" name="vFromMonFriTimeSlot2"  id= "vFromMonFriTimeSlot-first" value="<?= $vFromMonFriTimeSlot2; ?>"/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vToMonFriTimeSlot2'>
                                                            <input type='text' class="form-control" name="vToMonFriTimeSlot2" id= "vToMonFriTimeSlot-second" value="<?= $vToMonFriTimeSlot2; ?>"/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Slot1 : Saturday &amp; Sunday<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vFromSatSunTimeSlot1'>
                                                            <input type='text' class="form-control TimeField" name="vFromSatSunTimeSlot1" value="<?= $vFromSatSunTimeSlot1; ?>" required/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                        <span class="FromError2"></span>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vToSatSunTimeSlot1'>
                                                            <input type='text' class="form-control TimeField" name="vToSatSunTimeSlot1" value="<?= $vToSatSunTimeSlot1; ?>" required/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="ToError2"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Slot2 : Saturday &amp; Sunday</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vFromSatSunTimeSlot2'>
                                                            <input type='text' class="form-control" name="vFromSatSunTimeSlot2" value="<?= $vFromSatSunTimeSlot2; ?>"/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <div class='input-group date' id='vToSatSunTimeSlot2'>
                                                            <input type='text' class="form-control" name="vToSatSunTimeSlot2" value="<?= $vToSatSunTimeSlot2; ?>"/>
                                                            <span class="input-group-addon">
                                                                <span class="glyphicon glyphicon-calendar"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Minimum Amount Per Order (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the minimum order amount that the customer should add to the cart to get the delivery of the order.'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fMinOrderValue"  id="fMinOrderValue" value="<?= $fMinOrderValue; ?>" placeholder="Minimum Order" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Additional Packing Charges (In <?= $db_currency[0]['vName'] ?>)</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fPackingCharge"  id="fPackingCharge" value="<?= $fPackingCharge; ?>" placeholder="Packing Charges" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Max order qty<span class="red"> *</span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Maximum number of quantity the <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?> can place per order'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="iMaxItemQty"  id="iMaxItemQty" value="<?= $iMaxItemQty; ?>" placeholder="Max qty place by <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>" >
                                            </div>
                                        </div>

                                        <div class="row estimateval">
                                            <div class="col-lg-12">
                                                <label>Estimated Order Time including Approx Delivery time (in minutes)
                                                    <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fPrepareTime"  id="fPrepareTime" value="<?= $fPrepareTime; ?>" placeholder="Estimated Order Time including Approx Delivery time" >
                                            </div>
                                        </div>
                                        <?php if($isStoreDriver > 0) { $lgCount = 6; } else { $lgCount = 12; } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Offer Applies On<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="fOfferAppyType"  id="fOfferAppyType">
                                                <option value="None" <?php
                                                if ($fOfferAppyType == 'None') {
                                                    echo 'selected';
                                                }
                                                ?>>None</option>
                                                <option value="First" <?php
                                                if ($fOfferAppyType == 'First') {
                                                    echo 'selected';
                                                }
                                                ?>>First Order</option>
                                                <option value="All" <?php
                                                if ($fOfferAppyType == 'All') {
                                                    echo 'selected';
                                                }
                                                ?>>All Order</option>
                                            </select>
                                            <small>Note: The discount will be applied on Base price including options and others.</small>
                                        </div>
                                    </div>
                                    <div class="row" id="fOfferTypeDiv">
                                        <div class="col-lg-12">
                                            <label>Offer Type</label>
                                        </div>
                                        <div class="col-lg-12">
                                            <select class="form-control" name="fOfferType"  id="fOfferType">
                                                <!-- <option value="">Select Offer Type</option> -->
                                                <option value="Flat" <?php
                                                if ($fOfferType == 'Flat') {
                                                    echo 'selected';
                                                }
                                                ?>>Flat Offer</option>
                                                <option value="Percentage" <?php
                                                if ($fOfferType == 'Percentage') {
                                                    echo 'selected';
                                                }
                                                ?>>Percentage Offer</option>
                                            </select>
                                        </div>
                                    </div>

                                        <div class="row" id="fOfferAmtDiv">
                                            <div class="col-lg-12">
                                                <label>Offer Discount <span class="addnote"></span><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fOfferAmt"  id="fOfferAmt" value="<?= $fOfferAmt; ?>" placeholder="Offer Amount">
                                            </div>
                                        </div>

                                        <div class="row" id="fTargetAmtDiv">
                                            <div class="col-lg-12">
                                                <label>Target Amount (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='"Set the minimum total order amount to avail the offer. E.g. "Get <?= $db_currency[0]['vSymbol']; ?>7 off on order above <?= $db_currency[0]['vSymbol']; ?>50" OR "Get 20% off on order above <?= $db_currency[0]['vSymbol']; ?>50", so <?= $db_currency[0]['vSymbol']; ?>50 is the target amount to get the off."'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fTargetAmt"  id="fTargetAmt" value="<?= $fTargetAmt; ?>" placeholder="Target Amount">
                                                <small>Note: "If the offer type is 'Flat Offer' then set target amount (<?= $db_currency[0]['vSymbol']; ?>11) greater than offer discount price(<?= $db_currency[0]['vSymbol']; ?>10), Ex. Get <?= $db_currency[0]['vSymbol']; ?>10 off on orders above <?= $db_currency[0]['vSymbol']; ?>11".</small>
                                            </div>

                                        </div>

                                        <div class="row" id="fMaxOfferAmtDiv" style="display: none;">
                                            <div class="col-lg-12">
                                                <label>Max Off Amount (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the amount to limit <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?> to get the maximum off amount on each order. E.g. If offer is 50% off, and maximum off amount is $250, then on order of $2000 <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?> can get $250 off, but not $1000 off.'></i></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fMaxOfferAmt"  id="fMaxOfferAmt" value="<?= $fMaxOfferAmt; ?>" placeholder="Max Off Amount" >
                                            </div>
                                        </div>
                                        <div class="row servicecatresponsive"  style="display:none;">
                                            <div class="col-lg-12">
                                                <label>Cost Per Person (In <?= $db_currency[0]['vName'] ?>)<span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="fPricePerPerson"  id="fPricePerPerson" value="<?= $fPricePerPerson; ?>" placeholder="Cost Per Person">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Account Holder Name</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text"  class="form-control" name="vAcctHolderName"  id="vAcctHolderName" value="<?= $generalobjAdmin->clearName(" " . $vAcctHolderName) ?>" placeholder="Account Holder Name" >
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Account Number</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text"  class="form-control" name="vAcctNo"  id="vAcctNo" value="<?= $generalobjAdmin->clearName(" " . $vAcctNo) ?>" placeholder="Account Number" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Bank Name</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text"  class="form-control" name="vBankName"  id="vBankName" value="<?= $generalobjAdmin->clearName(" " . $vBankName) ?>" placeholder="Bank Name" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Bank Location</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text" class="form-control" name="vBankLocation"  id="vBankLocation" value="<?= $generalobjAdmin->clearName(" " . $vBankLocation) ?>" placeholder="Bank Location" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>BIC/SWIFT Code</label>
                                            </div>
                                            <div class="col-lg-12">
                                                <input type="text"  class="form-control" name="vSwiftCode"  id="vSwiftCode" value="<?= $generalobjAdmin->clearName(" " . $vSwiftCode) ?>" placeholder="BIC/SWIFT Code" >
                                            </div>
                                        </div>
                                        <?php if($isStoreDriver > 0){ ?>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <label><?= $langage_lbl_admin['LBL_PROVIDER_SELECTION_TXT']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="eDriverOption"  id="eDriverOption">
                                                    <option value="All" <?php
                                                    if ($eDriverOption == 'All') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?= $langage_lbl_admin['LBL_BOTH_DELIEVERY_DRIVERS']; ?></option>
                                                    <option value="Personal" <?php
                                                    if ($eDriverOption == 'Personal') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?= $langage_lbl_admin['LBL_PERSONAL_DELIVERY_DRIVER']; ?></option>
                                                    <option value="Site" <?php
                                                    if ($eDriverOption == 'Site') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?= $langage_lbl_admin['LBL_SITE_DELIVERY_DRIVER']; ?></option>
                                                </select>
                                            </div>
                                        </div>
 <?php } ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?php include_once('footer.php'); ?>
        <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
        <script type="text/javascript" src="js/moment.min.js"></script>
        <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
        <script>
                                                        $('[data-toggle="tooltip"]').tooltip();
                                                        var successMSG1 = '<?php echo $success; ?>';
                                                        if (successMSG1 != '') {
                                                            setTimeout(function () {
                                                                $(".msgs_hide").hide(1000)
                                                            }, 5000);
                                                        }

                                                        $(document).ready(function () {

                                                            // * * * * * * * * * * * * START AUTO COMPLETE* * * * * * * * * * * * * * * *
                                                            $('#vRestuarantLocation').keyup(function (e) {
                                                                buildAutoComplete("vRestuarantLocation", e, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>", "<?= $_SESSION['sess_lang']; ?>", function (latitude, longitude, address) {
                                                                    // set map marker...
                                                                    var map;
                                                                    var myLatlng;

                                                                    // var location = '('+latitude+','+longitude+')';
                                                                    myLatlng = new google.maps.LatLng(latitude, longitude);
                                                                    map = new google.maps.Map(document.getElementById('map'), {
                                                                        center: {lat: parseFloat(latitude), lng: parseFloat(longitude)},
                                                                        zoom: 13
                                                                    });
                                                                    if (marker && marker.setMap) {
                                                                        marker.setMap(null);
                                                                    }
                                                                    var marker = new google.maps.Marker({
                                                                        map: map,
                                                                        anchorPoint: new google.maps.Point(0, -29),
                                                                        position: myLatlng
                                                                    });
                                                                    marker.setVisible(false);
                                                                    map.setZoom(17);
                                                                    marker.setVisible(true);
                                                                    $("#vRestuarantLocationLat").val(latitude);
                                                                    $("#vRestuarantLocationLong").val(longitude);
                                                                });

                                                            });
                                                            // * * * * * * * * * * * * END AUTO COMPLETE* * * * * * * * * * * * * * * *
                                                            var referrer;
                                                            if ($("#previousLink").val() == "") {
                                                                referrer = document.referrer;
                                                            } else {
                                                                referrer = $("#previousLink").val();
                                                            }
                                                            if (referrer == "") {
                                                                referrer = "store.php";
                                                            } else {
                                                                $("#backlink").val(referrer);
                                                            }
                                                            $(".back_link").attr('href', referrer);
                                                        });

                                                        function setCity(id, selected)
                                                        {
                                                            var fromMod = 'company';
                                                            var request = $.ajax({
                                                                type: "POST",
                                                                url: 'change_stateCity.php',
                                                                data: {stateId: id, selected: selected, fromMod: fromMod},
                                                                success: function (dataHtml)
                                                                {
                                                                    $("#vCity").html(dataHtml);
                                                                }
                                                            });
                                                        }

                                                        function setState(id, selected)
                                                        {
                                                            var fromMod = 'company';
                                                            var request = $.ajax({
                                                                type: "POST",
                                                                url: 'change_stateCity.php',
                                                                data: {countryId: id, selected: selected, fromMod: fromMod},
                                                                success: function (dataHtml)
                                                                {
                                                                    $("#vState").html(dataHtml);
                                                                    if (selected == '')
                                                                        setCity('', selected);
                                                                }
                                                            });
                                                        }

                                                        setState('<?php echo $vCountry; ?>', '<?php echo $vState; ?>');
                                                        setCity('<?php echo $vState; ?>', '<?php echo $vCity; ?>');

                                                        function changeServiceType(iServiceid) {
                                                            var iCompanyId = "<? echo $id ?>";
                                                            $.ajax({
                                                                type: "POST",
                                                                url: 'ajax_get_cuisine.php',
                                                                data: {iServiceid: iServiceid, iCompanyId: iCompanyId},
                                                                success: function (response)
                                                                {
                                                                    //console.log(response);
                                                                    $("#js-cuisine-multiple").html('');
                                                                    $("#js-cuisine-multiple").html(response);
                                                                }
                                                            });
                                                            var storeId = '<?= $id ?>';
                                                            $.ajax({
                                                                type: "POST",
                                                                url: 'ajax_get_store_categories.php',
                                                                data: {iServiceid: iServiceid, storeId: storeId},
                                                                success: function (response)
                                                                {
                                                                    //console.log(response);
                                                                    $("#js-store-category-multiple").html('');
                                                                    $("#js-store-category-multiple").html(response);
                                                                }
                                                            });
                                                            if (iServiceid==1 || iServiceid==2) {
                                                                $("#eSafetyPractices").show();
                                                            } else {
                                                                $("#eSafetyPractices").hide();
                                                            }
                                                        }

                                                        /*function changeServiceTypesecond(iServiceid){
                                                         var iCompanyId = "<? echo $id ?>";
                                                         $.ajax({
                                                         type: "POST",
                                                         url: 'ajax_get_cuisine.php',
                                                         data: {iServiceid:iServiceid,iCompanyId:iCompanyId},
                                                         success: function (response)
                                                         {
                                                         //console.log(response);
                                                         $("#js-cuisine-multiple").html('');
                                                         $("#js-cuisine-multiple").html(response);
                                                         }
                                                         });
                                                         }
                                                         */
                                                        function changeCode(id) {
                                                            var request = $.ajax({
                                                                type: "POST",
                                                                url: 'change_code.php',
                                                                data: 'id=' + id,
                                                                success: function (data)
                                                                {
                                                                    document.getElementById("code").value = data;
                                                                    //window.location = 'profile.php';
                                                                }
                                                            });
                                                        }
                                                        changeCode('<?php echo $vCountry; ?>');

                                                        var map;
                                                        var geocoder;
                                                        var geocoder = new google.maps.Geocoder();
                                                        function initialize() {
                                                            map = new google.maps.Map(document.getElementById('map'), {
                                                                center: {lat: -33.8688, lng: 151.2195},
                                                                zoom: 13
                                                            });
                                                            var input = document.getElementById('vRestuarantLocation');
                                                            // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
                                                            // var autocomplete = new google.maps.places.Autocomplete(input);
                                                            // autocomplete.bindTo('bounds', map);

                                                            var marker = new google.maps.Marker({
                                                                map: map,
                                                                draggable: true,
                                                                animation: google.maps.Animation.DROP,
                                                                anchorPoint: new google.maps.Point(0, -29)
                                                            });

                                                            // autocomplete.addListener('place_changed', function () {

                                                            // marker.setVisible(false);
                                                            // var place = autocomplete.getPlace();
                                                            // if (!place.geometry) {
                                                            //     window.alert("Autocomplete's returned place contains no geometry");
                                                            //     return;
                                                            // }

                                                            // // If the place has a geometry, then present it on a map.
                                                            // if (place.geometry.viewport) {
                                                            //     map.fitBounds(place.geometry.viewport);
                                                            // } else {
                                                            //     map.setCenter(place.geometry.location);
                                                            //     map.setZoom(17);
                                                            // }
                                                            // /*        marker.setIcon(({
                                                            //  size: new google.maps.Size(71, 71),
                                                            //  origin: new google.maps.Point(0, 0),
                                                            //  anchor: new google.maps.Point(17, 34),
                                                            //  scaledSize: new google.maps.Size(35, 35)
                                                            //  }));*/
                                                            // marker.setPosition(place.geometry.location);
                                                            // marker.setVisible(true);

                                                            // var address = '';
                                                            // if (place.address_components) {
                                                            //     address = [
                                                            //         (place.address_components[0] && place.address_components[0].short_name || ''),
                                                            //         (place.address_components[1] && place.address_components[1].short_name || ''),
                                                            //         (place.address_components[2] && place.address_components[2].short_name || '')
                                                            //     ].join(' ');
                                                            // }
                                                            // //$("#vRestuarantLocation").val(place.formatted_address);
                                                            // $("#vRestuarantLocationLat").val(place.geometry.location.lat());
                                                            // $("#vRestuarantLocationLong").val(place.geometry.location.lng());
                                                            // });

                                                            if ($("#vRestuarantLocation").val() != "") {
                                                                var myLatLng = new google.maps.LatLng($("#vRestuarantLocationLat").val(), $("#vRestuarantLocationLong").val());
                                                                marker.setPosition(myLatLng);
                                                                map.setCenter(myLatLng);
                                                                map.setZoom(17);
                                                                marker.setVisible(true);
                                                            }
                                                        }

                                                        google.maps.event.addDomListener(window, 'load', initialize);

                                                        // var input = document.getElementById('vRestuarantLocation');
                                                        // google.maps.event.addDomListener(input, 'keydown', function (event) {
                                                        //     if (event.keyCode === 13) {
                                                        //         event.preventDefault();
                                                        //     }
                                                        // });

                                                        function getAddress(mDlatitude, mDlongitude, addId) {
                                                            var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);
                                                            geocoder.geocode({'latLng': mylatlang},
                                                                    function (results, status) {
                                                                        console.log(results);
                                                                        if (status == google.maps.GeocoderStatus.OK) {
                                                                            /*if (results[0]) {
                                                                             // document.getElementById(addId).value = results[0].formatted_address;
                                                                             $('#' + addId).val(results[0].formatted_address);
                                                                             }*/
                                                                            if (results[0].address_components[0]['short_name'] == "Unnamed Road") {
                                                                                $('#' + addId).val(results[1].formatted_address);
                                                                            } else if (results[0]) {
                                                                                $('#' + addId).val(results[0].formatted_address);
                                                                            } else {
                                                                                document.getElementById('#' + addId).value = "No results";
                                                                            }
                                                                        } else {
                                                                            document.getElementById('#' + addId).value = status;
                                                                        }
                                                                    });
                                                        }


                                                        $(function () {
                                                            $('#vFromMonFriTimeSlot1').datetimepicker({
                                                                format: 'HH:mm A',
                                                                ignoreReadonly: true,
                                                            });
                                                            $('#vToMonFriTimeSlot1').datetimepicker({
                                                                format: 'HH:mm A',
                                                                ignoreReadonly: true,

                                                                useCurrent: false //Important! See issue #1075
                                                            });

                                                            /* $("#vFromTimeSlot1").on("dp.change", function (e) {
                                                             $('#vToTimeSlot1').data("DateTimePicker").minDate(e.date);
                                                             });
                                                             $("#vToTimeSlot1").on("dp.change", function (e) {
                                                             $('#vFromTimeSlot1').data("DateTimePicker").maxDate(e.date);
                                                             });*/

                                                            $('#vFromMonFriTimeSlot2').datetimepicker({
                                                                format: 'HH:mm A',
                                                                ignoreReadonly: true,
                                                            });
                                                            $('#vToMonFriTimeSlot2').datetimepicker({
                                                                format: 'HH:mm A',
                                                                ignoreReadonly: true,
                                                                useCurrent: false
                                                            });

                                                            /* $("#vFromMonFriTimeSlot1").on("dp.change", function (e) {
                                                             var startTime = $('#vFromMonFriTimeSlot1-1').val();
                                                             var st = minFromMidnight(startTime);
                                                             if(st == 0){
                                                             alert('Please enter proper time.');
                                                             return false;
                                                             
                                                             }
                                                             });
                                                             
                                                             $("#vToMonFriTimeSlot1").on("dp.change", function (e) {
                                                             
                                                             var startTime = $('#vFromMonFriTimeSlot1-1').val();
                                                             var endTime = $('#vToMonFriTimeSlot-2').val();
                                                             
                                                             var st = minFromMidnight(startTime);
                                                             var et = minFromMidnight(endTime);
                                                             
                                                             
                                                             
                                                             if (st > et) {
                                                             alert('End time always greater then start time.');
                                                             return false;
                                                             }
                                                             
                                                             });
                                                             $("#vFromMonFriTimeSlot2").on("dp.change", function (e) {
                                                             
                                                             var startTime = $('#vToMonFriTimeSlot-2').val();
                                                             var endTime = $('#vFromMonFriTimeSlot-first').val();
                                                             var st = minFromMidnight(startTime);
                                                             var et = minFromMidnight(endTime);
                                                             if (st > et) {
                                                             alert('First slot End time always greater then Second start time.');
                                                             return false;
                                                             }
                                                             
                                                             });*/

                                                            /*  $("#vToMonFriTimeSlot2").on("dp.change", function (e) {
                                                             
                                                             var startTime = $('#vFromMonFriTimeSlot-first').val();
                                                             var endTime = $('#vToMonFriTimeSlot-second').val();
                                                             var st = minFromMidnight(startTime);
                                                             var et = minFromMidnight(endTime);
                                                             if (st > et) {
                                                             alert('End time always greater then Second Slot start time.');
                                                             return false;
                                                             }
                                                             
                                                             });*/

                                                            function minFromMidnight(tm) {
                                                                var ampm = tm.substr(-2);
                                                                var clk;
                                                                if (tm.length <= 6) {
                                                                    clk = tm.substr(0, 4);
                                                                } else {
                                                                    clk = tm.substr(0, 5);
                                                                }
                                                                var m = parseInt(clk.match(/\d+$/)[0], 10);
                                                                var h = parseInt(clk.match(/^\d+/)[0], 10);
                                                                h += (ampm.match(/pm/i)) ? 12 : 0;
                                                                return h * 60 + m;
                                                            }

                                                            /*$("#vFromTimeSlot2").on("dp.change", function (e) {
                                                             $('#vToTimeSlot2').data("DateTimePicker").minDate(e.date);
                                                             });
                                                             $("#vToTimeSlot2").on("dp.change", function (e) {
                                                             $('#vFromTimeSlot2').data("DateTimePicker").maxDate(e.date);
                                                             });*/

                                                            $('#vFromSatSunTimeSlot1').datetimepicker({
                                                                format: 'HH:mm A',
                                                                ignoreReadonly: true,
                                                            });
                                                            $('#vToSatSunTimeSlot1').datetimepicker({
                                                                format: 'HH:mm A',
                                                                ignoreReadonly: true,
                                                                useCurrent: false //Important! See issue #1075
                                                            });

                                                            $('#vFromSatSunTimeSlot2').datetimepicker({
                                                                format: 'HH:mm A',
                                                                ignoreReadonly: true,
                                                            });
                                                            $('#vToSatSunTimeSlot2').datetimepicker({
                                                                format: 'HH:mm A',
                                                                ignoreReadonly: true,
                                                                useCurrent: false
                                                            });


                                                            /*Offer Apply Type*/
                                                            var fOfferAppyType = $('#fOfferAppyType').val();
                                                            var fOfferTypeval = $('#fOfferType').val();
                                                            if (fOfferAppyType == 'None') {
                                                                $("#fOfferTypeDiv,#fTargetAmtDiv,#fOfferAmtDiv,#fMaxOfferAmtDiv").hide();
                                                                $('#fOfferAmt').removeAttr('required');
                                                            } else {
                                                                $("#fOfferTypeDiv,#fOfferAmtDiv,#fTargetAmtDiv").show();
                                                                if (fOfferAppyType != 'None' && fOfferTypeval == 'Percentage') {
                                                                    $("#fMaxOfferAmtDiv").show();
                                                                }
                                                                if (fOfferAppyType != 'None' && fOfferTypeval == 'Flat') {
                                                                    $('#fTargetAmt').attr('required', 'required');
                                                                } else {
                                                                    $('#fTargetAmt').removeAttr('required');
                                                                }
                                                                $('#fOfferAmt').attr('required', 'required');
                                                            }

                                                            $('#fOfferAppyType').on('change', function () {
                                                                var fOfferAppyTypechange = this.value;
                                                                var fOfferTypevalonchange = $('#fOfferType').val();
                                                                if (fOfferAppyTypechange == 'None') {
                                                                    $("#fOfferTypeDiv,#fTargetAmtDiv,#fOfferAmtDiv,#fMaxOfferAmtDiv").hide();
                                                                    $('#fOfferAmt').removeAttr('required');
                                                                    $('#fTargetAmt').removeAttr('required');
                                                                } else {
                                                                    $("#fOfferTypeDiv,#fTargetAmtDiv,#fOfferAmtDiv").show();
                                                                    if (fOfferAppyTypechange != 'None' && fOfferTypevalonchange == 'Percentage') {
                                                                        $("#fMaxOfferAmtDiv").show();
                                                                    }
                                                                    if (fOfferAppyTypechange != 'None' && fOfferTypevalonchange == 'Flat') {
                                                                        $('#fTargetAmt').attr('required', 'required');
                                                                    } else {
                                                                        $('#fTargetAmt').removeAttr('required');
                                                                    }
                                                                    $('#fOfferAmt').attr('required', 'required');
                                                                }
                                                            });
                                                            var sid1 = "<?php echo $iServiceIdNew; ?>";
                                                            if (sid1 != '') {
                                                                changeServiceType(sid1);
                                                            } else {
                                                                var iServiceidvar = $("#iServiceId").val();
                                                                changeServiceType(iServiceidvar);
                                                            }
                                                            var sid = "<?php echo $iServiceIdNew; ?>";
                                                            if (sid == null || sid == "") {
                                                                var sid = $("#iServiceId").val();
                                                            }
                                                            if (sid == '1' && sid != '') {
                                                                $(".servicecatresponsive").show();
                                                            }
                                                            $("#iServiceId").change(function () {
                                                                var iServiceid = $(this).val();
                                                                if (iServiceid != '1') {
                                                                    $(".servicecatresponsive").hide();
                                                                    $("#fPricePerPerson").rules("remove", "required");
                                                                } else if (iServiceid == '1') {
                                                                    $(".servicecatresponsive").show();
                                                                }
                                                            });
                                                            /*Offer Type*/
                                                            var fOfferType1 = $('#fOfferType').val();
                                                            var fOfferAppyType1 = $('#fOfferAppyType').val();
                                                            if (fOfferAppyType1 != 'None' && fOfferType1 == 'Percentage') {
                                                                $("#fMaxOfferAmtDiv").show();
                                                                $('#fTargetAmt').removeAttr('required');
                                                                $(".addnote").html("(%)");
                                                            } else {
                                                                $("#fMaxOfferAmtDiv").hide();
                                                                $(".addnote").html("(In <?= $db_currency[0]['vName'] ?>)");
                                                                if (fOfferAppyType1 != 'None' && fOfferType1 == 'Flat') {
                                                                    $('#fTargetAmt').attr('required', 'required');
                                                                } else {
                                                                    $('#fTargetAmt').removeAttr('required');
                                                                }
                                                            }

                                                            $('#fOfferType').on('change', function () {
                                                                var fOfferAppyType2 = $('#fOfferAppyType').val();
                                                                var fOfferType2 = this.value;
                                                                if (fOfferAppyType2 != 'None' && fOfferType2 == 'Percentage') {
                                                                    $("#fMaxOfferAmtDiv").show();
                                                                    $('#fTargetAmt').removeAttr('required');
                                                                    $(".addnote").html("(%)");
                                                                } else {
                                                                    $("#fMaxOfferAmtDiv").hide();

                                                                    if (fOfferAppyType2 != 'None' && fOfferType2 == 'Flat') {
                                                                        $('#fTargetAmt').attr('required', 'required');
                                                                    } else {
                                                                        $('#fTargetAmt').removeAttr('required');
                                                                    }

                                                                    $(".addnote").html("(In <?= $db_currency[0]['vName'] ?>)");
                                                                }
                                                            });

                                                        });
                                                        var sid = "<?php echo $iServiceIdNew; ?>";
                                                        $(document).ready(function () {
                                                            
                                                            const myInput = document.getElementById('vRestuarantLocation');
                                                            myInput.onpaste = function(e) {
                                                              e.preventDefault();
                                                            }
                                                            
                                                            $('#js-cuisine-multiple, #js-store-category-multiple').select2();
                                                            $("#submitBtn").on("click", function (event) {
                                                                var isvalidate = $("#_company_form")[0].checkValidity();
                                                                var maxOrderQty = $("#iMaxItemQty").val();
                                                                var prepareTime = $("#fPrepareTime").val();
                                                                var pricePerson = $("#fPricePerPerson").val();
                                                                var iServiceid = $("#iServiceId").val();
                                                                if (maxOrderQty == "" || maxOrderQty <= 0) {
                                                                    alert("Order qty. value must be greater than or equal to 1.");
                                                                    $("#iMaxItemQty").focus();
                                                                    return false;
                                                                }
                                                                if (prepareTime == "" || prepareTime <= 0) {
                                                                    alert("Delivery time value must be greater than or equal to 1.");
                                                                    $("#fPrepareTime").focus();
                                                                    return false;
                                                                }
                                                                if ((pricePerson == "" || pricePerson <= 0) && iServiceid == "1") {
                                                                    alert("Cost Per Person value must be greater than or equal to 1.");
                                                                    $("#fPricePerPerson").focus();
                                                                    return false;
                                                                }
                                                                if (isvalidate) {
                                                                    /*event.preventDefault();*/
                                                                    var vEmail = $("#vEmail").val();
                                                                    var vPhone = $("#vPhone").val();
                                                                    var iCompanyId = '<?php echo $id ?>';
                                                                    //alert(iCompanyId);
                                                                    if (iCompanyId != '') {
                                                                        $.ajax({
                                                                            type: "POST",
                                                                            url: '../ajax_check_Email_Country.php',
                                                                            dataType: 'html',
                                                                            data: {vEmail: vEmail, vPhone: vPhone, iCompanyId: iCompanyId},
                                                                            success: function (dataHtml5)
                                                                            {
                                                                                //alert(dataHtml5);
                                                                                if ($.trim(dataHtml5) != '') {
                                                                                    alert($.trim(dataHtml5));
                                                                                    $("#_company_form")[0].submit();
                                                                                } else {
                                                                                    $("#_company_form")[0].submit();
                                                                                }
                                                                            },
                                                                            error: function (dataHtml5)
                                                                            {
                                                                            }
                                                                        });
                                                                    } else {
                                                                        $("#_company_form")[0].submit();
                                                                    }
                                                                }
                                                            });
                                                        });
        </script>
    </body>
    <!-- END BODY-->
</html>
