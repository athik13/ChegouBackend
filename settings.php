<?php
include_once('common.php');

//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
	include_once("cx-settings.php");
	exit;
}

$generalobj->check_member_login();

$abc = 'admin,driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$user = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';
$script = "Settings";
$tbl_name = 'company';
$_REQUEST['id'] = base64_decode(base64_decode(trim($_REQUEST['id'])));
$id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : $_SESSION['sess_iCompanyId'];
$action = ($id != '') ? 'Edit' : 'Add';

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;

$sql = "select iCountryId,vCountry,vCountryCode from country ORDER BY  vCountry ASC ";
$db_country = $obj->MySQLSelect($sql);

$sql = "select vCode,vTitle from language_master where eStatus = 'Active' order by iDispOrder asc";
$db_lang = $obj->MySQLSelect($sql);

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
}

// set all variables with either post (when submit) either blank (when insert)
$vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
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
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '';

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
$eDriverOption = isset($_POST['eDriverOption']) ? $_POST['eDriverOption'] : 'Site';
$eTakeaway = isset($_POST['eTakeaway']) ? $_POST['eTakeaway'] : '';
//$fTax =  isset($_POST['fTax'])?$_POST['fTax']:'';
if (isset($_POST['submitsettings'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location:settings?success=2");
        exit;
    }
    $sql = "select vPhoneCode from country where vCountryCode = '$vCountry'";
    $db_country_data = $obj->MySQLSelect($sql);
    if ($vCode == "") {
        $vCode = $db_country_data[0]['vPhoneCode'];
    }
    $eSystem = "DeliverAll";
    $checEmailExist = $generalobj->checkMemberDataInfo($vPhone, "", 'COMPANY', $vCountry, $id, $eSystem); //Added By HJ On 12-09-2019
    $action = 2;
    if ($checEmailExist['status'] == 0) {
        //$messge = "LBL_MOBILE_EXIST";
        $var_msg = $langage_lbl['LBL_MOBILE_EXIST'];
    } else if ($checEmailExist['status'] == 2) {
        //$messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
        $var_msg = $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
    } else {
        $CompanyData ['vCompany'] = $vCompany;
        $CompanyData ['vEmail'] = $vEmail;
        $CompanyData ['vContactName'] = $vContactName;
        $CompanyData ['vCode'] = $vCode;
        $CompanyData ['vPhone'] = $vPhone;
        $CompanyData ['vCountry'] = $vCountry;
        $CompanyData ['vState'] = $vState;
        $CompanyData ['vCity'] = $vCity;
        $CompanyData ['vRestuarantLocation'] = $vRestuarantLocation;
        $CompanyData ['vRestuarantLocationLat'] = $vRestuarantLocationLat;
        $CompanyData ['vRestuarantLocationLong'] = $vRestuarantLocationLong;
        $CompanyData ['vCaddress'] = $vCaddress;
        $CompanyData ['vZip'] = $vZip;
        $CompanyData ['vLang'] = $vLang;
        $CompanyData ['vAcctHolderName'] = $vAcctHolderName;
        $CompanyData ['vAcctNo'] = $vAcctNo;
        $CompanyData ['vBankName'] = $vBankName;
        $CompanyData ['vBankLocation'] = $vBankLocation;
        $CompanyData ['vSwiftCode'] = $vSwiftCode;
        $CompanyData ['iServiceId'] = $iServiceId;
        $vFromMonFriTimeSlot1_arr = explode(" ", $vFromMonFriTimeSlot1);

        $CompanyData ['vFromMonFriTimeSlot1'] = $vFromMonFriTimeSlot1_arr[0];

        $vToMonFriTimeSlot1_arr = explode(" ", $vToMonFriTimeSlot1);
        $CompanyData ['vToMonFriTimeSlot1'] = $vToMonFriTimeSlot1_arr[0];

        $vFromMonFriTimeSlot2_arr = explode(" ", $vFromMonFriTimeSlot2);
        $CompanyData ['vFromMonFriTimeSlot2'] = $vFromMonFriTimeSlot2_arr[0];

        $vToMonFriTimeSlot2_arr = explode(" ", $vToMonFriTimeSlot2);
        $CompanyData ['vToMonFriTimeSlot2'] = $vToMonFriTimeSlot2_arr[0];

        $vFromSatSunTimeSlot1_arr = explode(" ", $vFromSatSunTimeSlot1);
        $CompanyData ['vFromSatSunTimeSlot1'] = $vFromSatSunTimeSlot1_arr[0];

        $vToSatSunTimeSlot1_arr = explode(" ", $vToSatSunTimeSlot1);
        $CompanyData ['vToSatSunTimeSlot1'] = $vToSatSunTimeSlot1_arr[0];

        $vFromSatSunTimeSlot2_arr = explode(" ", $vFromSatSunTimeSlot2);
        $CompanyData ['vFromSatSunTimeSlot2'] = $vFromSatSunTimeSlot2_arr[0];

        $vToSatSunTimeSlot2_arr = explode(" ", $vToSatSunTimeSlot2);
        $CompanyData ['vToSatSunTimeSlot2'] = $vToSatSunTimeSlot2_arr[0];

        $CompanyData ['fMinOrderValue'] = $fMinOrderValue;
        $CompanyData ['fPackingCharge'] = $fPackingCharge;
        $CompanyData ['iMaxItemQty'] = $iMaxItemQty;
        $CompanyData ['fPrepareTime'] = $fPrepareTime;
        $CompanyData ['fOfferAppyType'] = $fOfferAppyType;
        $CompanyData ['fOfferType'] = $fOfferType;
        $CompanyData ['fTargetAmt'] = $fTargetAmt;
        $CompanyData ['fOfferAmt'] = $fOfferAmt;
        $CompanyData ['fMaxOfferAmt'] = $fMaxOfferAmt;
        $CompanyData ['fPricePerPerson'] = $fPricePerPerson;
	$CompanyData ['eDriverOption'] = $eDriverOption;
        $CompanyData ['eTakeaway'] = $eTakeaway;
        if ($fOfferAppyType == 'None') {
            $CompanyData ['fTargetAmt'] = 0;
            $CompanyData ['fOfferAmt'] = 0;
            $CompanyData ['fMaxOfferAmt'] = 0;
        }
        if ($fOfferType == 'Flat') {
            $CompanyData ['fMaxOfferAmt'] = 0;
        }
        $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $CompanyData ['vCode'] . "'";
        $CountryData = $obj->MySQLSelect($csql);
        $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
        if ($eZeroAllowed == 'Yes') {
            $CompanyData ['vPhone'] = $CompanyData ['vPhone'];
        } else {
            $first = substr($CompanyData ['vPhone'], 0, 1);
            if ($first == "0") {
                $CompanyData ['vPhone'] = substr($CompanyData ['vPhone'], 1);
            }
        }
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
        if ($_FILES['vCoverImage']['name'] != '') {
            /* if (isset($_POST['imagebase64'])) { */
            $image_object = $_FILES['vCoverImage']['tmp_name'];
            $image_name = $_FILES['vCoverImage']['name'];
            $img_path = $tconfig["tsite_upload_images_compnay_path"];
            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            $temp_gallery = $img_path . '/';
            $check_file_query = "select iCompanyId,vCoverImage from company where iCompanyId=" . $id;
            $check_file = $obj->sql_query($check_file_query);
            $oldCoverImageImage = $check_file[0]['vCoverImage'];
            $check_file = $img_path . '/' . $id . '/' . $oldCoverImageImage;
            if ($oldCoverImageImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $id . '/' . $oldCoverImageImage);
                @unlink($img_path . '/' . $id . '/1_' . $oldCoverImageImage);
                @unlink($img_path . '/' . $id . '/2_' . $oldCoverImageImage);
                @unlink($img_path . '/' . $id . '/3_' . $oldCoverImageImage);
            }
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
            $sql = "UPDATE company SET `vCoverImage` = '" . $vImgName . "' WHERE `iCompanyId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
        $action = 1;
        $var_msg = $langage_lbl['LBL_PORFILE_UPDATE_MSG'];
    }
	$_SESSION['sess_lang']= $vLang;
    header("location:settings?success=" . $action . "&error_msg=" . $var_msg);
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
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCompany = $value['vCompany'];
            $vEmail = $value['vEmail'];
            $vContactName = $generalobj->cleanall(htmlspecialchars($value['vContactName']));
            $vCode = $value['vCode'];
            $vPhone = $value['vPhone'];
            $vCountry = $value['vCountry'];
            $vCity = $value['vCity'];
            $vState = $value['vState'];
            $vRestuarantLocation = $value['vRestuarantLocation'];
            $vRestuarantLocationLat = $value['vRestuarantLocationLat'];
            $vRestuarantLocationLong = $value['vRestuarantLocationLong'];
            $vCaddress = $generalobj->cleanall(htmlspecialchars($value['vCaddress']));
            $vZip = $value['vZip'];
            $vLang = $value['vLang'];
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
            $oldvCoverImageImage = $value['vCoverImage'];
            $fMaxOfferAmt = $value['fMaxOfferAmt'];
            $iServiceId = $value['iServiceId'];
	    $eDriverOption = $value['eDriverOption'];
            $eTakeaway = $value['eTakeaway'];
        }
    }
}
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
$selectcuisine_sql = "SELECT cuisineId,cuisineName_" . $default_lang . " as cuisineName FROM  `cuisine` WHERE iServiceId='" . $iServiceId . "' AND eStatus = 'Active'";
$db_cuisine = $obj->MySQLSelect($selectcuisine_sql);

$sql_country = "select tLatitude,tLongitude from country where vCountryCode = '$DEFAULT_COUNTRY_CODE_WEB'";
$db_countryx = $obj->MySQLSelect($sql_country);
$lat_default = $db_countryx[0]['tLatitude'];
$long_default = $db_countryx[0]['tLongitude'];
$isStoreDriver = isStoreDriverAvailable(); // Added By HJ On 20-03-2020 For Check Manage Driver By Store Enable
$takeaway = isTakeAwayEnable() ? "Yes" : "No";
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_SETTINGS_FRONT']; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
        <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
        <script type='text/javascript' src='assets/map/gmaps.js'></script>
        <link rel="stylesheet" href="assets/css/select2/select2.min.css" type="text/css" >
        <script type="text/javascript" src="assets/plugins/select2/select2.min.js"></script>
        <!-- <script type='text/javascript' src='<?php // echo $tconfig["tsite_url"] ?>assets/js/ajax_for_advance_strategy.js'></script>
        <script type='text/javascript' src='<?php // echo $tconfig["tsite_url"] ?>assets/js/network_js.js'></script> -->
        <style>
            #image_demo img{
                width: 250px;
                height: auto;
            }
            .cover-photo-banner-control-restaurant{
                margin-top: 10px; 
            }
        </style>
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant">
                <div class="page-contant-inner page-trip-detail">
                    <h2 class="header-page trip-detail driver-detail1"><?= $langage_lbl['LBL_SETTINGS_FRONT']; ?></h2>
                    <!-- trips detail page -->
                    <div class="driver-add-vehicle-setting"> 
                        <?php if ($success == 1) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $langage_lbl['LBL_Record_Updated_successfully']; ?>
                            </div>
                        <?php } else if ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                            </div>
                        <?} else if ($success == 0 && $var_msg != "") { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                                <?= isset($_REQUEST['error_msg']) ? $_REQUEST['error_msg'] : ' '; ?>
                        </div> 
                        <? } ?>
                        <div class="form-group">
                            <form method="post" action="" class="company_form" id="company_form" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= base64_encode(base64_encode($id)); ?>"/>
                                <input type="hidden" name="oldvCoverImageImage" value="<?= $oldvCoverImageImage; ?>"/>
                                <input type="hidden" name="uid" id="u_id1" value="<?= $_SESSION['sess_iUserId']; ?>">
                                <input type="hidden" name="iServiceId" id="iServiceId" value="<?= $iServiceId; ?>">
        <!-- <input type="hidden" name="imagebase64" id="imagebase64" value="" /> -->
                                <div class="row">
                                    <div class="col-sm-6 col-xs-12">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_COMPANY_SIGNUP_DL']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" id="vCompany" class="form-control" name="vCompany"  id="vCompany" value="<?= $vCompany; ?>" placeholder="<?= $langage_lbl['LBL_COMPANY_SIGNUP']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_PROFILE_YOUR_EMAIL_ID']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" id="vEmail" class="form-control" name="vEmail"  id="vEmail" value="<?= $vEmail; ?>" placeholder="<?= $langage_lbl['LBL_PROFILE_YOUR_EMAIL_ID']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_RESTURANT_LOCATION_WEB_TXT']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" id="vRestuarantLocation" class="form-control" name="vRestuarantLocation"  id="vRestuarantLocation" value="<?= $vRestuarantLocation; ?>" placeholder="<?= $langage_lbl['LBL_RESTURANT_LOCATION_WEB_TXT']; ?>" required>
                                            </div>
                                            <input type="hidden" name="vRestuarantLocationLat" id="vRestuarantLocationLat" value="<?= $vRestuarantLocationLat ?>">
                                            <input type="hidden" name="vRestuarantLocationLong" id="vRestuarantLocationLong" value="<?= $vRestuarantLocationLong ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div id="map" style="width:100%;height:200px;"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_RESTAURANT_ADDRESS']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="vCaddress"  id="vCaddress" value="<?= $vCaddress; ?>" placeholder="<?= $langage_lbl['LBL_RESTAURANT_ADDRESS']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_ZIP_CODE_WEB']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="vZip"  id="vZip" value="<?= $vZip; ?>" placeholder="<?= $langage_lbl['LBL_ZIP_CODE_WEB']; ?>" >
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_COUNTRY_TXT']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <select class="form-control" name = 'vCountry' id="vCountry" onChange="setState(this.value, '');changeCode(this.value);" required>
                                                    <option value=""><?= $langage_lbl['LBL_SELECT_CONTRY']; ?></option>
                                                    <?php for ($i = 0; $i < count($db_country); $i++) { ?>
                                                        <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_STATE_TXT']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <select class="form-control" name = 'vState' id="vState" onChange="setCity(this.value, '');" required>
                                                    <option value=""><?= $langage_lbl['LBL_SELECT_STATE']; ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <?php if($SHOW_CITY_FIELD=='Yes') { ?>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_CITY_TXT']; ?></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <select class="form-control" name = 'vCity' id="vCity"  >
                                                    <option value=""><?= $langage_lbl['LBL_SELECT_CITY']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_WEB_CONTACT_PERSON_NAME']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="vContactName"  id="vContactName" value="<?= $vContactName; ?>" placeholder="<?= $langage_lbl['LBL_WEB_CONTACT_PERSON_NAME']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_Phone_Number']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12" style="float: left;">
                                                <input type="text" class="form-control form-select-2" id="code" name="vCode" value="<?= $vCode ?>"  readonly style="width: 10%;text-align: center;margin:0;"/ >
                                                       <input type="text" class="form-control" name="vPhone"  id="vPhone" value="<?= $vPhone; ?>" placeholder="<?= $langage_lbl['LBL_Phone_Number']; ?>" style="width:90%;" required>
                                            </div>
                                        </div>
                                        <?php if (count($db_lang) <= 1) { ?>
                                            <input name="vLang" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode']; ?>"/>
                                        <?php } else { ?>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <label><?= $langage_lbl['LBL_WEB_LANGUAGE_TXT']; ?><span class="red"> *</span></label>
                                                </div>
                                                <div class="col-sm-12">
                                                    <select  class="form-control" name = 'vLang' required>
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

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_WEB_ACCOUNT_HOLDER_NAME']; ?></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text"  class="form-control" name="vAcctHolderName"  id="vAcctHolderName" value="<?= $vAcctHolderName ?>" placeholder="<?= $langage_lbl['LBL_WEB_ACCOUNT_HOLDER_NAME']; ?>" >
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_WEB_ACCOUNT_NUMBER']; ?></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text"  class="form-control" name="vAcctNo"  id="vAcctNo" value="<?= $vAcctNo ?>" placeholder="<?= $langage_lbl['LBL_WEB_ACCOUNT_NUMBER']; ?>" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_BANK_NAME']; ?></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text"  class="form-control" name="vBankName"  id="vBankName" value="<?= $vBankName ?>" placeholder="<?= $langage_lbl['LBL_BANK_NAME']; ?>" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_BANK_LOCATION_WEB']; ?></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="vBankLocation"  id="vBankLocation" value="<?= $vBankLocation ?>" placeholder="<?= $langage_lbl['LBL_BANK_LOCATION_WEB']; ?>" >
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_BIC_SWIFT_CODE']; ?></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text"  class="form-control" name="vSwiftCode"  id="vSwiftCode" value="<?= $vSwiftCode ?>" placeholder="<?= $langage_lbl['LBL_BIC_SWIFT_CODE']; ?>" >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xs-12 dd1t">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_COMPANY_CUISINE_WEB']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12" id="AvilableCusineSetting">
                                                <select class="form-control"  id="js-cuisine-multiple" name="cuisineId[]" multiple="multiple" required>
                                                    <?php foreach ($db_cuisine as $cuisinedata) { ?>
                                                        <option name="<?= $cuisinedata['cuisineId'] ?>" value="<?= $cuisinedata['cuisineId'] ?>" <?php echo (isset($cusineselecteddata) && in_array($cuisinedata['cuisineId'], $cusineselecteddata)) ? 'selected="selected"' : ""; ?>><?= $cuisinedata['cuisineName'] ?></option>
                                                    <?php } ?>    
                                                </select>
                                                <div class="CuisineClass">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_SLOT1_MONDAY_TO_FRIDAY']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="clearfix res-row-set">
                                                <div class='col-xs-5'>
                                                    <div class='input-group date' id='vFromMonFriTimeSlot1'>
                                                        <input type='text' class="form-control TimeField" name="vFromMonFriTimeSlot1"  id= "vFromMonFriTimeSlot1-1" required value="<?= $vFromMonFriTimeSlot1; ?>"/>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                    <span class="FromError1"></span>
                                                </div>
                                                <div class='col-xs-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-xs-5'>
                                                    <div class='input-group date' id='vToMonFriTimeSlot1'>
                                                        <input type='text' class="form-control TimeField" name="vToMonFriTimeSlot1" id="vToMonFriTimeSlot-1" required value="<?= $vToMonFriTimeSlot1; ?>"/>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                    <span class="ToError1"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="new-vToMonFriTimeSlot-1" id="new-vToMonFriTimeSlot-1" value="">
                                        <input type="hidden" name="new-vFromMonFriTimeSlot2-2" id="new-vFromMonFriTimeSlot2-2" value="">

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_SLOT2_MONDAY_TO_FRIDAY']; ?></label>
                                            </div>
                                            <div class="clearfix res-row-set">
                                                <div class='col-xs-5'>
                                                    <div class='input-group date' id='vFromMonFriTimeSlot2'>
                                                        <input type='text' class="form-control" name="vFromMonFriTimeSlot2" id="vFromMonFriTimeSlot2-2" value="<?= $vFromMonFriTimeSlot2; ?>"/>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class='col-xs-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-xs-5'>
                                                    <div class='input-group date' id='vToMonFriTimeSlot2'>
                                                        <input type='text' class="form-control" name="vToMonFriTimeSlot2" value="<?= $vToMonFriTimeSlot2; ?>"/>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="ToErrortime"></span>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_SLOT1_SATURDAY_AND_SUNDAY']; ?><span class="red"> *</span> </label>
                                            </div>
                                            <div class="clearfix res-row-set">
                                                <div class='col-xs-5'>
                                                    <div class='input-group date' id='vFromSatSunTimeSlot1'>
                                                        <input type='text' class="form-control TimeField" name="vFromSatSunTimeSlot1" required value="<?= $vFromSatSunTimeSlot1; ?>"/>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                    <span class="FromError2"></span>
                                                </div>
                                                <div class='col-xs-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-xs-5'>
                                                    <div class='input-group date' id='vToSatSunTimeSlot1'>
                                                        <input type='text' required class="form-control TimeField" name="vToSatSunTimeSlot1" id="new-vToSatSunTimeSlot1" value="<?= $vToSatSunTimeSlot1; ?>"/>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                    <span class="ToError2"></span>  
                                                </div> 
                                            </div>

                                        </div>

                                        <input type="hidden" name="new-vToSatSunTimeSlot1-1" id="new-vToSatSunTimeSlot1-1" value="">
                                        <input type="hidden" name="new-vFromSatSunTimeSlot2-2" id="new-vFromSatSunTimeSlot2-2" value="">

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_SLOT2_SATURDAY_AND_SUNDAY']; ?></label>
                                            </div>
                                            <div class="clearfix res-row-set">
                                                <div class='col-xs-5'>
                                                    <div class='input-group date' id='vFromSatSunTimeSlot2'>
                                                        <input type='text' class="form-control" name="vFromSatSunTimeSlot2" id="new-vFromSatSunTimeSlot2" value="<?= $vFromSatSunTimeSlot2; ?>"/>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class='col-xs-2' style="text-align: center;">
                                                    <div style="font-weight: bold;">To</div>
                                                </div>
                                                <div class='col-xs-5'>
                                                    <div class='input-group date' id='vToSatSunTimeSlot2'>
                                                        <input type='text' class="form-control" name="vToSatSunTimeSlot2" value="<?= $vToSatSunTimeSlot2; ?>"/>
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="ToErrortimeSun"></span>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_MIN_AMOUNT_PER_ORDER']; ?> (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the price if you want to deliver order only after XX price.'></i></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="fMinOrderValue"  id="fMinOrderValue" value="<?= $fMinOrderValue; ?>" placeholder="<?= $langage_lbl['LBL_MIN_AMOUNT_PER_ORDER']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_ADDITIONAL_CAHRGES_WEB']; ?> (In <?= $db_currency[0]['vName'] ?>)</label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="fPackingCharge"  id="fPackingCharge" value="<?= $fPackingCharge; ?>" placeholder="<?= $langage_lbl['LBL_ADDITIONAL_CAHRGES_WEB']; ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_MAX_QTY_PLACE_ORDER_TXT_WEB']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="iMaxItemQty"  id="iMaxItemQty" value="<?= $iMaxItemQty; ?>" placeholder="<?= $langage_lbl['LBL_MAX_QTY_PLACE_ORDER_TXT_WEB']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_ESTIMATED_FOOD_PREPARATION_TIME_TXT']; ?> (<?= $langage_lbl['LBL_IN_MINUTES_TXT']; ?>)<span class="red">*</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="fPrepareTime"  id="fPrepareTime" value="<?= $fPrepareTime; ?>" placeholder="<?= $langage_lbl['LBL_ESTIMATED_FOOD_PREPARATION_TIME_TXT']; ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_OFFER_APPLY_ON_TXT']; ?><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <select class="form-control" name="fOfferAppyType"  id="fOfferAppyType" required>
                                                    <option value="None" <?php
                                                    if ($fOfferAppyType == 'None') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?= $langage_lbl['LBL_OPTION_NONE']; ?></option>
                                                    <option value="First" <?php
                                                    if ($fOfferAppyType == 'First') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?= $langage_lbl['LBL_FIRST_ORDER']; ?></option>
                                                    <option value="All" <?php
                                                    if ($fOfferAppyType == 'All') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?= $langage_lbl['LBL_ALL_ORDER']; ?></option>
                                                </select>
                                                <small>[<?= $langage_lbl['LBL_OFFER_NOTE'] ?>]</small>
                                            </div>
                                        </div>

                                        <div class="row" id="fOfferTypeDiv">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_OFFER_TYPE_WEB_TXT'] ?></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <select class="form-control" name="fOfferType"  id="fOfferType">
                                                    <option value="Flat" <?php
                                                    if ($fOfferType == 'Flat') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?= $langage_lbl['LBL_FLAT_OFFER'] ?></option>
                                                    <option value="Percentage" <?php
                                                    if ($fOfferType == 'Percentage') {
                                                        echo 'selected';
                                                    }
                                                    ?>><?= $langage_lbl['LBL_PERCENTAGE_OFFER'] ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row" id="fOfferAmtDiv">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_OFFER_DISCOUNT_TXT'] ?><span class="addnote" style="float: none;"></span><span class="red"> *</span></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="fOfferAmt"  id="fOfferAmt" value="<?= $fOfferAmt; ?>" placeholder="<?= $langage_lbl['LBL_OFFER_DISCOUNT_TXT'] ?>">
                                            </div>
                                        </div>
                                        <div class="row" id="fTargetAmtDiv">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_TARGET_AMOUNT_TXT'] ?> (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='"Set the minimum total order amount to avail the offer. E.g. "Get <?= $db_currency[0]['vSymbol']; ?>7 off on order above <?= $db_currency[0]['vSymbol']; ?>50" OR "Get 20% off on order above <?= $db_currency[0]['vSymbol']; ?>50", so <?= $db_currency[0]['vSymbol']; ?>50 is the target amount to get the off."'></i> </label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="fTargetAmt"  id="fTargetAmt" value="<?= $fTargetAmt; ?>" placeholder="<?= $langage_lbl['LBL_TARGET_AMOUNT_TXT'] ?>">
                                                <small>[<?php echo $langage_lbl['LBL_NOTE_FRONT'] ?> "If the offer type is 'Flat Offer' then set target amount (<?= $db_currency[0]['vSymbol']; ?>11) greater than offer discount price(<?= $db_currency[0]['vSymbol']; ?>10), Ex. Get <?= $db_currency[0]['vSymbol']; ?>10 off on orders above <?= $db_currency[0]['vSymbol']; ?>11".]</small>
                                            </div>
                                        </div>

                                        <div class="row" id="fMaxOfferAmtDiv" style="display: none;">
                                            <div class="col-sm-12">
                                                <label><?= $langage_lbl['LBL_MAX_OFF_AMOUNT'] ?> (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Set the amount to limit user to get the maximum off amount on each order. E.g. If offer is 50% off, and maximum off amount is <?= $db_currency[0]['vSymbol']; ?>250, then on order of <?= $db_currency[0]['vSymbol']; ?>2000 user can get <?= $db_currency[0]['vSymbol']; ?>250 off, but not <?= $db_currency[0]['vSymbol']; ?>1000 off.'></i></label>
                                            </div>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control" name="fMaxOfferAmt"  id="fMaxOfferAmt" value="<?= $fMaxOfferAmt; ?>" placeholder="<?= $langage_lbl['LBL_MAX_OFF_AMOUNT'] ?>" >
                                            </div>
                                        </div>
                                        <?php if ($iServiceId == '1') { ?>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <label><?= $langage_lbl['LBL_COST_PER_ORDER']; ?> (In <?= $db_currency[0]['vName'] ?>)<span class="red"> *</span></label>
                                                </div>
                                                <div class="col-sm-12">
                                                    <input type="text" class="form-control" name="fPricePerPerson"  id="fPricePerPerson" value="<?= $fPricePerPerson; ?>" placeholder="<?= $langage_lbl['LBL_COST_PER_ORDER']; ?> " >
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($isStoreDriver > 0) { ?>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <label><?= $langage_lbl['LBL_PROVIDER_SELECTION_TXT']; ?><span class="red"> *</span></label>
                                                </div>
                                                <div class="col-sm-12">
                                                    <select class="form-control" name="eDriverOption"  id="eDriverOption">
                                                        <option value="All" <?php
                                                        if ($eDriverOption == 'All') {
                                                            echo 'selected';
                                                        }
                                                        ?>><?= $langage_lbl['LBL_BOTH_DELIEVERY_DRIVERS']; ?></option>
                                                        <option value="Personal" <?php
                                                        if ($eDriverOption == 'Personal') {
                                                            echo 'selected';
                                                        }
                                                        ?>><?= $langage_lbl['LBL_PERSONAL_DELIVERY_DRIVER']; ?></option>
                                                        <option value="Site" <?php
                                                        if ($eDriverOption == 'Site') {
                                                            echo 'selected';
                                                        }
                                                        ?>><?= $langage_lbl['LBL_SITE_DELIVERY_DRIVER']; ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>
										
					<? if($takeaway=='Yes') { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label><?= $langage_lbl['LBL_TAKE_AWAY'] ?></label>
                                            </div>
                                            <div class="col-lg-12">
                                                <select class="form-control" name="eTakeaway">
                                                    <!--<option value=''><?= $langage_lbl_admin['LBL_SELECT_TXT']; ?></option>-->
                                                    <option value="Yes" <?php if ($eTakeaway == 'Yes') { echo 'selected'; } ?>><?= $langage_lbl_admin['LBL_BTN_YES_TXT'] ?></option>
                                                    <option value="No" <?php if ($eTakeaway == 'No' || $eTakeaway == '') { echo 'selected'; } ?>><?= $langage_lbl_admin['LBL_BTN_NO_TXT'] ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <? } ?>
                                    </div>
                                </div>
                                <div class="car-type">
                                    <strong><input type="submit" class="save-vehicle" name="submitsettings" id="submitsettings" value="<?= $langage_lbl['LBL_SAVE_SETTINGS']; ?>"> </strong>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php
        include_once('top/footer_script.php');
        $lang = get_langcode($_SESSION['sess_lang']);
        ?>
        <script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/validation/jquery.validate.min.js" ></script>
        <?php if ($lang != 'en') { ?>
            <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
            <? include_once('otherlang_validation.php');?>
        <?php } ?>
        <script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
        <script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script type="text/javascript" src="assets/js/moment.min.js"></script>
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
        <script type="text/javascript" src="assets/js/bootstrap-datetimepicker.min.js"></script>
        <link rel="stylesheet" href="assets/css/croppie.css" />
        <script src="assets/js/croppie.js"></script>
        <script>
        var map;
        function initialize() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: parseFloat('<?= $lat_default ?>'), lng: parseFloat('<?= $long_default ?>')},
                zoom: 8
            });
			
			var marker = new google.maps.Marker({
                    map: map,
                    anchorPoint: new google.maps.Point(0, -29)
                });
			
			$('#vRestuarantLocation').keyup(function (e) {
				buildAutoComplete("vRestuarantLocation",e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>","<?=$_SESSION['sess_lang'];?>", function(latitude, longitude, address){
					
					var location = new google.maps.LatLng(latitude, longitude);
					map.setCenter(location);
					map.setZoom(17);
					marker.setPosition(location);
                    marker.setVisible(true);
					
					$("#vRestuarantLocation").val(address);
                    $("#vRestuarantLocationLat").val(latitude);
                    $("#vRestuarantLocationLong").val(longitude);
					

				});
			});
            
                // var input = document.getElementById('vRestuarantLocation');
                // // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
                // var autocomplete = new google.maps.places.Autocomplete(input);
                // autocomplete.bindTo('bounds', map);
                // var marker = new google.maps.Marker({
                    // map: map,
                    // anchorPoint: new google.maps.Point(0, -29)
                // });
                // autocomplete.addListener('place_changed', function () {
                    // marker.setVisible(false);
                    // var place = autocomplete.getPlace();
                    // if (!place.geometry) {
                        // window.alert("Autocomplete's returned place contains no geometry");
                        // return;
                    // }
                    // // If the place has a geometry, then present it on a map.
                    // if (place.geometry.viewport) {
                        // map.fitBounds(place.geometry.viewport);
                    // } else {
                        // map.setCenter(place.geometry.location);
                        // map.setZoom(17);
                    // }
                    // marker.setPosition(place.geometry.location);
                    // marker.setVisible(true);
                    // var address = '';
                    // if (place.address_components) {
                        // address = [
                            // (place.address_components[0] && place.address_components[0].short_name || ''),
                            // (place.address_components[1] && place.address_components[1].short_name || ''),
                            // (place.address_components[2] && place.address_components[2].short_name || '')
                        // ].join(' ');
                    // }
                    // $("#vRestuarantLocation").val(place.formatted_address);
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
            $('#vFromMonFriTimeSlot2').datetimepicker({
                format: 'HH:mm A',
                ignoreReadonly: true,
            });
            $('#vToMonFriTimeSlot2').datetimepicker({
                format: 'HH:mm A',
                ignoreReadonly: true,
                useCurrent: false
            });
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
            $("#vToMonFriTimeSlot1").on("dp.change", function (e) {
                var vToMonFriTimeSlotstartTime = $('#vToMonFriTimeSlot-1').val();
                var st = minFromMidnight(vToMonFriTimeSlotstartTime);
                $('#new-vToMonFriTimeSlot-1').val(st);

            });
            $("#vFromMonFriTimeSlot2").on("dp.change", function (e) {
                var vFromMonFriTimeSlot2 = $('#vFromMonFriTimeSlot2-2').val();
                var st = minFromMidnight(vFromMonFriTimeSlot2);
                $('#new-vFromMonFriTimeSlot2-2').val(st);

            });
            $("#vToSatSunTimeSlot1").on("dp.change", function (e) {
                var vToSatSunTimeSlot1 = $('#new-vToSatSunTimeSlot1').val();
                var st = minFromMidnight(vToSatSunTimeSlot1);
                $('#new-vToSatSunTimeSlot1-1').val(st);
            });
            $("#vFromSatSunTimeSlot2").on("dp.change", function (e) {
                var vFromSatSunTimeSlot2 = $('#new-vFromSatSunTimeSlot2').val();
                var st = minFromMidnight(vFromSatSunTimeSlot2);
                $('#new-vFromSatSunTimeSlot2-2').val(st);
            });
            /*Offer Apply Type*/
            var fOfferAppyType = $('#fOfferAppyType').val();
            var fOfferTypeval = $('#fOfferType').val();
            if (fOfferAppyType == 'None') {
                $("#fOfferTypeDiv").hide();
                $("#fTargetAmtDiv").hide();
                $("#fOfferAmtDiv").hide();
                $("#fMaxOfferAmtDiv").hide();
                $('#fOfferAmt').removeAttr('required');
            } else {
                $("#fOfferTypeDiv").show();
                $("#fTargetAmtDiv").show();
                $("#fOfferAmtDiv").show();
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
                    $("#fOfferTypeDiv").hide();
                    $("#fTargetAmtDiv").hide();
                    $("#fOfferAmtDiv").hide();
                    $("#fMaxOfferAmtDiv").hide();
                    $('#fOfferAmt').removeAttr('required');
                } else {
                    $("#fOfferTypeDiv").show();
                    $("#fTargetAmtDiv").show();
                    $("#fOfferAmtDiv").show();

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
        $(document).ready(function () {

            // $('#vRestuarantLocation').keyup(function (e) {
            //     buildAutoComplete("<?= $MAPS_API_REPLACEMENT_STRATEGY ?>", "vRestuarantLocation",e.keyCode, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>","<?= $_SESSION['sess_lang']; ?>", function(latitude, longitude, address){
            //         initialize();
            //     }); // (orignal function)
            
            // });

            $('#js-cuisine-multiple').select2();
            var startTime = $('#vToMonFriTimeSlot-1').val();
            var st = minFromMidnight(startTime);
            $('#new-vToMonFriTimeSlot-1').val(st);
            var vFromMonFriTimeSlot2 = $('#vFromMonFriTimeSlot2-2').val();
            var st2 = minFromMidnight(vFromMonFriTimeSlot2);
            $('#new-vFromMonFriTimeSlot2-2').val(st2);
        });
        $(function () {
            $.validator.addMethod("greaterThan",
                    function (value, element, param) {
                        var $min = $(param);
                        if (this.settings.onfocusout) {
                            $min.off(".validate-greaterThan").on("blur.validate-greaterThan", function () {
                                $(element).valid();
                            });
                        }
                        if (param != '') {
                            return parseInt(value) > parseInt($min.val());
                        } else {
                            return true;
                        }
                    }, "Max must be greater than min");

            //Company Start
            var errormessage;
            user = '<?= $user ?>';

            var dataa = {};
            dataa.iCompanyId = "<?= $_SESSION['sess_iUserId']; ?>";
            dataa.usertype = user;
            if ($('#company_form').length !== 0) {

                $.validator.addMethod('ge', function (value, element, param) {
                    var value2 = $('#new-vFromMonFriTimeSlot2-2').val();
                    return this.optional(element) || parseInt(value2) > parseInt($(param).val());
                }, '<?= addslashes($langage_lbl['LBL_SLT_2_FRM_RESTRICT']); ?>');


                $.validator.addMethod('gesun', function (value, element, param) {
                    var value2 = $('#new-vFromSatSunTimeSlot2-2').val();
                    return this.optional(element) || parseInt(value2) > parseInt($(param).val());
                }, '<?= addslashes($langage_lbl['LBL_SLT_2_FRM_RESTRICT']); ?>');


                $('#company_form').validate({
                    ignore: 'input[type=hidden]',
                    errorClass: 'help-block error',
                    errorElement: 'span',
                    errorPlacement: function (error, element) {
                        if (element.attr("name") == "cuisineId[]")
                        {
                            error.insertAfter(".CuisineClass");
                        } else if (element.attr("name") == "vFromMonFriTimeSlot1") {
                            error.appendTo(".FromError1");
                        } else if (element.attr("name") == "vToMonFriTimeSlot1") {
                            error.appendTo(".ToError1");
                        } else if (element.attr("name") == "vFromSatSunTimeSlot1") {
                            error.appendTo(".FromError2");
                        } else if (element.attr("name") == "vToSatSunTimeSlot1") {
                            error.appendTo(".ToError2");
                        } else if (element.attr("name") == "vFromMonFriTimeSlot2") {
                            error.appendTo(".ToErrortime");


                        } else if (element.attr("name") == "vFromSatSunTimeSlot2") {
                            error.appendTo(".ToErrortimeSun");


                        } else {
                            error.insertAfter(element);
                        }
                    },
                    invalidHandler: function (form, validator) {
                        var errors = validator.numberOfInvalids();
                        if (errors) {
                            validator.errorList[0].element.focus();
                        }
                    },
                    highlight: function (e) {
                        $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                        $(e).closest('.help-block').remove();
                    },
                    success: function (e) {
                        e.closest('.row').removeClass('has-success has-error');
                        e.closest('.help-block').remove();
                        e.closest('.help-inline').remove();
                    },

                    rules: {
                        vCompany: {required: true, minlength: 2},
                        vContactName: {required: true},
                        vEmail: {required: true, email: true,
                            remote: {
                                url: 'ajax_validate_email.php',
                                type: "post",
                                cache: false,
                                data: {
                                    id: function (e) {
                                        return $('#vEmail').val();
                                    },
                                    usr: function (e) {
                                        return user;
                                    },
                                    uid: function (e) {
                                        return $("#u_id1").val();
                                    }
                                },
                                dataFilter: function (response) {
                                    if (response == 'deleted') {
                                        errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                                        return false;
                                    } else if (response == 'false') {
                                        errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                                        return false;
                                    } else {
                                        return true;
                                    }
                                },
                                async: false
                            }
                        },
                        vPhone: {required: true, minlength: 3, digits: true, //phonevalidate: true,
                            /*remote: {
                             url: 'ajax_driver_mobile_new.php',
                             type: "post",
                             data: dataa,
                             dataFilter: function (response) {
                             if (response == 'deleted') {
                             errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                             return false;
                             } else if (response == 'false') {
                             errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                             return false;
                             } else {
                             return true;
                             }
                             },
                             async: false
                             }*/
                        },
                        vCaddress: {required: true, minlength: 2},
                        vZip: {required: true, minlength: 2},
                        vLang: {required: true},
                        'cuisineId[]': {required: true},
                        fMinOrderValue: {/*required: true,*/number: true},
                        fPackingCharge: {number: true},
                        iMaxItemQty: {required: true, digits: true, min: 1},
                        fPrepareTime: {required: true, digits: true},
                        fOfferAppyType: {required: true},
                        vCountry: {required: true},
                        vRestuarantLocation: {required: true},
                        fOfferAmt: {
                            number: function () {
                                return $("#fOfferAmt").prop('required');
                            },
                            min: function () {
                                return $("#fOfferAmt").prop('required');
                            },
                            max: function () {
                                if ($("#fOfferAmt").prop('required') == true && $("#fOfferType").val() == 'Percentage') {
                                    return 100;
                                }
                            }
                        },
                        fTargetAmt: {
                            number: function () {
                                if ($("#fTargetAmt").prop('required') == true && $("#fOfferType").val() != 'Percentage') {
                                    return true;
                                }
                            },
                            greaterThan: function () {
                                if ($("#fTargetAmt").prop('required') == true && $("#fOfferType").val() != 'Percentage') {
                                    return '#fOfferAmt';
                                } else {
                                    return '';
                                }
                            },
                            min: function () {
                                return $("#fTargetAmt").prop('required');
                            }
                        },
                        fMaxOfferAmt: {number: true},
                        fPricePerPerson: {required: true, number: true}
                    },
                    messages: {
                        vCompany: {
                            required: '<?= addslashes($langage_lbl['LBL_STORE_REQUIRED_MSG_FRONT']); ?>',
                            minlength: '<?= addslashes($langage_lbl['LBL_STORE_NAME_MINIMUM_MSG_FRONT']); ?>'
                        },
                        vEmail: {
                            required: '<?= addslashes($langage_lbl['LBL_EMAIL_ADDRESS_FRONT_MSG']); ?>',
                            remote: function () {
                                return errormessage;
                            }
                        },
                        vContactName: {
                            required: '<?= addslashes($langage_lbl['LBL_STORE_CONTACT_PERSON_NAME_TXT']); ?>'
                        },
                        vPhone: {
                            required: '<?= addslashes($langage_lbl['LBL_PHONE_REQUIRED_TXT']); ?>',
                            minlength: '<?= addslashes($langage_lbl['LBL_PHONE_MINIMUM_MSG_FRONT']); ?>',
                            digits: '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG_FRONT']); ?>',
                            remote: function () {
                                return errormessage;
                            }
                        },
                        vCaddress: {
                            required: '<?= addslashes($langage_lbl['LBL_ADDRESS_REQUIRED_FRONT']); ?>'
                        },
                        vZip: {
                            required: '<?= addslashes($langage_lbl['LBL_ZIP_CODE_FRONT_WEB']); ?>'
                        },
                        vLang: {
                            required: '<?= addslashes($langage_lbl['LBL_LANG_REQUIRED_FRONT']); ?>'
                        },
                        'cuisineId[]': {
                            required: '<?= addslashes($langage_lbl['LBL_SELECT_CUISINE_FRONT']); ?>'
                        },
                        vCountry: {
                            required: '<?= addslashes($langage_lbl['LBL_COUNTRY_REQUIRED_FRONT']); ?>'
                        },
                        fOfferAmt: {
                            min: '<?= addslashes($langage_lbl['LBL_OFFERAMT_MSG_REQUIRED']); ?>'
                        },
                        fTargetAmt: {
                            greaterThan: '<?= addslashes($langage_lbl['LBL_TARGET_VALIDATION_FRONT']); ?>',
                            min: '<?= addslashes($langage_lbl['LBL_TRGAMT_VALIDATION_MAX_FRONT']); ?>'
                        },
                        fPrepareTime: {
                            required: '<?= addslashes($langage_lbl['LBL_ESTIMATE_TIME_MSG_FRONT']); ?>'
                        },
                        fPricePerPerson: {
                            required: '<?= addslashes($langage_lbl['LBL_COST_PER_PERSON_FRONT_MSG']); ?>'
                        },

                    },
                    submitHandler: function (form) {
                        if ($(form).valid())
                            form.submit();
                        return false; // prevent normal form posting
                    }
                });
            }
        });
        function minFromMidnight(tm) {
            var ampm = tm.substr(-2);
            var clk;
            if (tm.length <= 6) {
                clk = tm.substr(0, 4);
            } else {
                clk = tm.substr(0, 5);
            }
            if (clk != "") {
                var m = parseInt(clk.match(/\d+$/)[0], 10);
                var h = parseInt(clk.match(/^\d+/)[0], 10);
                h += (ampm.match(/pm/i)) ? 12 : 0;
                return h * 60 + m;
            }
        }
        $('[data-toggle="tooltip"]').tooltip();
        var successMSG1 = '<?php echo $success; ?>';
        if (successMSG1 != '') {
            setTimeout(function () {
                $(".msgs_hide").hide(1000)
            }, 5000);
        }
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
        function changeCode(id) {
            var request = $.ajax({
                type: "POST",
                url: 'change_code.php',
                data: 'id=' + id,
                success: function (data)
                {
                    document.getElementById("code").value = data;
                }
            });
        }
        changeCode('<?php echo $vCountry; ?>');
        $("#submitsettings").on("click", function (event) {
            var isvalidate = $("#company_form")[0].checkValidity();
            if (isvalidate) {
                event.preventDefault();
                var vEmail = $("#vEmail").val();
                var vPhone = $("#vPhone").val();
                var vCountry = $("#vCountry").val();
                var iCompanyId = '<?php echo $id ?>';
                if (iCompanyId != '') {
                    $.ajax({
                        type: "POST",
                        url: 'ajax_check_Email_Country.php',
                        dataType: 'html',
                        data: {vEmail: vEmail, vPhone: vPhone, iCompanyId: iCompanyId, vCountry: vCountry},
                        success: function (dataHtml5)
                        {
                            if ($.trim(dataHtml5) != '') {
                                alert($.trim(dataHtml5));
                                $("#company_form").submit();
                                return true;
                            } else {
                                $("#company_form").submit();
                                return true;
                            }
                        },
                        error: function (dataHtml5)
                        {
                        }
                    });
                } else {
                    $("#_company_form").submit();
                    return true;
                }
            }
    });
        </script>
        <!-- End: Footer Script -->
    </body>
</html>