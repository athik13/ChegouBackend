<?php
include_once('../common.php');
include_once ('../app_common_functions.php');
global $userObj;

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $generalobj->getConfigurations("configurations", "SHOW_CITY_FIELD");
}
$ufxService = $generalobj->CheckUfxServiceAvailable();
$sql = "SELECT vCountryCode,vCountry FROM country WHERE eStatus='Active' ORDER BY vCountry ASC";
// $sql = "SELECT vCountryCode,vCountry FROM country WHERE eStatus='Active' AND iCountryId='101' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);

$sql = "SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active' ORDER BY vTitle ASC";
$db_lang = $obj->MySQLSelect($sql);

$sql = "SELECT iCompanyId,vCompany,eStatus FROM company WHERE eStatus = 'Active' AND eSystem = 'General' ORDER BY vCompany ASC";
$db_company = $obj->MySQLSelect($sql);

//For Currency
$sql = "SELECT vName,eDefault FROM currency WHERE eStatus='Active' ORDER BY vName ASC";
$db_currency = $obj->MySQLSelect($sql);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : ''; // Added By HJ On 12-08-2019 For Edit eEnableDemoLocDispatch Value If QA User as Per Disucss WIth KS
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'register_driver';
$script = 'Driver';

// set all variables with either post (when submit) either blank (when insert)
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? strtolower($_POST['vEmail']) : '';
$vUserName = isset($_POST['vEmail']) ? strtolower($_POST['vEmail']) : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCaddress = isset($_POST['vCaddress']) ? $_POST['vCaddress'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vZip = isset($_POST['vZip']) ? $_POST['vZip'] : '';
$vState = isset($_POST['vState']) ? $_POST['vState'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
//$dBirthDate = isset($_POST['dBirthDate']) ? $_POST['dBirthDate'] : '';
//$dBirthDate = $_POST['vYear'].'-'.$_POST['vMonth'].'-'.$_POST['vDay'];
$vPaymentEmail = isset($_POST['vPaymentEmail']) ? $_POST['vPaymentEmail'] : '';
$vBankAccountHolderName = isset($_POST['vBankAccountHolderName']) ? $_POST['vBankAccountHolderName'] : '';
$vAccountNumber = isset($_POST['vAccountNumber']) ? $_POST['vAccountNumber'] : '';
$vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
$vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
$vBIC_SWIFT_Code = isset($_POST['vBIC_SWIFT_Code']) ? $_POST['vBIC_SWIFT_Code'] : '';
$tProfileDescription = isset($_POST['tProfileDescription']) ? $_POST['tProfileDescription'] : '';
$vCurrencyDriver = isset($_POST['vCurrencyDriver']) ? $_POST['vCurrencyDriver'] : '';
$vPass = ($vPassword != "") ? $generalobj->encrypt_bycrypt($vPassword) : '';
$eGender = isset($_POST['eGender']) ? $_POST['eGender'] : '';
$oldImage = isset($_POST['oldImage']) ? $_POST['oldImage'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '0';

if($iServiceId > 0){
    $_POST['iCompanyId'] = isset($_POST['storeId']) ? $_POST['storeId'] : '1';
}

$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '1';
//echo $iCompanyId."<pre>";print_r($_POST);die;

//echo "<pre>";print_r($iCompanyId);die;
$eReftype = "Driver";

/* if($action == 'Add'){
  $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
  } */
$onlyDeliverallModule = ONLYDELIVERALL;


$cubeDeliverallOnly = isDeliverAllOnlySystem();
if($cubeDeliverallOnly > 0){
    $onlyDeliverallModule = "Yes";
}
if (isset($_POST['btnsubmit'])) {
    if ($SITE_VERSION == "v5") {
        $data_driver_pref = $generalobj->Update_User_Preferences($id, $_REQUEST);

        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Preferences updated successfully.';

        header("Location:driver_action.php?id=" . $id);
        exit;
    }
}
if (isset($_POST['submit'])) {
    
    if ($action == 'Add' && !$userObj->hasPermission('create-providers')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"];
        header("Location:driver.php");
        exit;
    }

    if ($action == 'Edit' && !$userObj->hasPermission('edit-providers')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"];
        header("Location:driver.php");
        exit;
    }

    if (!empty($id) && SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:driver.php?id=" . $id);
        exit;
    }

    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vName'], 'req', ' Name is required');
    $validobj->add_fields($_POST['vLastName'], 'req', 'Last Name is required');
    $validobj->add_fields(strtolower($_POST['vEmail']), 'req', 'Email Address is required.');
    $validobj->add_fields(strtolower($_POST['vEmail']), 'email', 'Please enter valid Email Address.');
    // $validobj->add_fields($_POST['eGender'], 'req', 'Please choose gender.');
    if ($action == "Add") {
        $validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
        if ($onlyDeliverallModule == 'Yes') {
            $validobj->add_fields($_POST['iCompanyId'], 'req', 'Company is required.');
        }
    }
    $validobj->add_fields($_POST['vPhone'], 'req', 'Phone Number is required.');
    $validobj->add_fields($_POST['vCountry'], 'req', 'Country is required.');
    //$validobj->add_fields($_POST['vCaddress'], 'req', 'Address is required.');
    $validobj->add_fields($_POST['vLang'], 'req', 'Language is required.');
    //  $validobj->add_fields($_POST['dBirthDate'], 'req', 'Birth Date is required.');
    //$validobj->add_fields($_POST['vYear'], 'req', 'Birth Year is required.');
    //$validobj->add_fields($_POST['vMonth'], 'req', 'Birth Month is required.');
    //$validobj->add_fields($_POST['vDay'], 'req', 'Birth Day is required.');
    $validobj->add_fields($_POST['vCurrencyDriver'], 'req', 'Currency is required.');

    $error = $validobj->validate();


    //Other Validations
    //comment by Rs bcz check Email,phone validation using checkMemberDataInfo function
    /*if ($vEmail != "") {
        if ($id != "") {
            $msg1 = $generalobj->checkDuplicateAdminNew('iDriverId', $tbl_name, Array('vEmail'), $id, "");
        } else {
            $msg1 = $generalobj->checkDuplicateAdminNew('vEmail', $tbl_name, Array('vEmail'), "", "");
        }

        if ($msg1 == 1) {
            $error .= '* Email Address is already exists.<br>';
        }
    }*/
    /* Added by SP for phone unique validation start */
    
    /*if ($vPhone != "") {
        if ($id != "") {
            $msg1 = $generalobj->checkDuplicateAdminNew('iUserId', $tbl_name, Array('vPhone'), $id, "");
        } else {
            $msg1 = $generalobj->checkDuplicateAdminNew('vPhone', $tbl_name, Array('vPhone'), "", "");
        }
        if ($msg1 == 1) {
            //$error .= '* Phone number is already exists.<br>';
        }
    }*/
    /* Added by SP for phone unique validation end */
    /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
    $eSystem = "";
        $checEmailExist= $generalobj->checkMemberDataInfo($vEmail,"",'DRIVER',$vCountry,$id,$eSystem);
        if($checEmailExist['status'] == 0){
            $error .= '* Email Address is already exists.<br>';
        }else if($checEmailExist['status'] == 2){
            $error .= $langage_lbl_admin['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        }
        $checPhoneExist = $generalobj->checkMemberDataInfo($vPhone,"",'DRIVER',$vCountry,$id,$eSystem);

        if($checPhoneExist['status'] == 0){
            $error .= '* Phone number already exists.<br>';
        }else if($checPhoneExist['status'] == 2){
            $error .= $langage_lbl_admin['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        }
   /* 06-09-219 check phone validation end */
    $error .= $validobj->validateFileType($_FILES['vImage'], 'jpg,jpeg,png,gif,bmp', '* Image file is not valid.');
    //Other Validations

    if ($error) {
        $success = 3;
        $newError = $error;
        //exit;
    } else {
        $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $vCode . "'";
        $CountryData = $obj->MySQLSelect($csql);
        $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
        if ($eZeroAllowed == 'Yes') {
            $vPhone = $vPhone;
        } else {
            $first = substr($vPhone, 0, 1);
            if ($first == "0") {
                $vPhone = substr($vPhone, 1);
            }
        }

        $vRefCodePara = '';
        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Edit') {
            $str = " ";
        } else {
            $str = " , eStatus = '$eStatus' ";
            $vRefCode = $generalobj->ganaraterefercode($eReftype);
            $vRefCodePara = "`vRefCode` = '" . $vRefCode . "',";
        }

        if (SITE_TYPE == 'Demo') {
            $str = " , eStatus = 'active' ";
        }

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iDriverId` = '" . $id . "'";
        }
        $str2 = $passPara = $str1 = $companyid = '';
        if ($action == 'Add') {
            $str1 = "`tRegistrationDate` = '" . date("Y-m-d H:i:s") . "',";
            $companyid = "`iCompanyId` = '" . $iCompanyId . "',";
        }

        if ($action == 'Add') {
            if (checkDriverDestinationModule(1)) {
                $str2 = "`eDestinationMode` = 'No', iDestinationCount = 0  , `tDestinationModifiedDate` = '" . date("Y-m-d H:i:s") . "' ,";
            } else {
                $str2 = "`eDestinationMode` = 'No', iDestinationCount = 0 , `tDestinationModifiedDate` = '" . date("Y-m-d H:i:s") . "' ,";
            }
        }
        if ($vPass != "") {
            $passPara = "`vPassword` = '" . $vPass . "',";
        }

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iDriverId` = '" . $id . "'";

            $sql1 = "select vEmail,vPhone,eEmailVerified,ePhoneVerified,vCode from " . $tbl_name . $where;
            $edit_data = $obj->sql_query($sql1);

            if ($vEmail != $edit_data[0]['vEmail']) {
                $query3 = $q . " `" . $tbl_name . "` SET `eEmailVerified` = 'No' " . $where;
                $obj->sql_query($query3);
            }

            if ($vPhone != $edit_data[0]['vPhone']) {
                $query4 = $q . " `" . $tbl_name . "` SET `ePhoneVerified` = 'No' " . $where;
                $obj->sql_query($query4);
            }

            if ($vCode != $edit_data[0]['vCode']) {
                $query5 = $q . " `" . $tbl_name . "` SET `ePhoneVerified` = 'No' " . $where;
                $obj->sql_query($query5);
            }
        }
        if ($action == 'Edit') {
            $sql = "select iDriverVehicleId from driver_vehicle where iDriverId='" . $id . "'";
            $data_vehicle = $obj->MySQLSelect($sql);

            if (count($data_vehicle) > 0) {
                $sql = "UPDATE driver_vehicle set iCompanyId='" . $iCompanyId . "' WHERE iDriverId='" . $id . "'";
                $obj->sql_query($sql);
            }
        }
        if (strtoupper($userType) == "QA") {
            $eEnableDemoLocDispatch = isset($_POST['eEnableDemoLocDispatch']) ? $_POST['eEnableDemoLocDispatch'] : 'off';
            $eEnableDemoLocDispatch = ($eEnableDemoLocDispatch == 'on') ? 'Yes' : 'No';
            $str3 = "`eEnableDemoLocDispatch` = '" . $eEnableDemoLocDispatch . "',";
        }
        $query = $q . " `" . $tbl_name . "` SET
			`vName` = '" . $vName . "',
			`vLastName` = '" . $vLastName . "',
			`vCountry` = '" . $vCountry . "',
			`vCaddress` = '" . $vCaddress . "',
			`vCity` = '" . $vCity . "',
			`vZip` = '" . $vZip . "',
			`vState` = '" . $vState . "',
			`vCode` = '" . $vCode . "',
			`vEmail` = '" . $vEmail . "',
			`vLoginId` = '" . $vEmail . "',
			 $passPara		
			$companyid
			`vPhone` = '" . $vPhone . "',
			`vImage` = '" . $oldImage . "',
			 $vRefCodePara
			`vPaymentEmail` = '" . $vPaymentEmail . "',
			`eGender` = '" . $eGender . "',
			`vBankAccountHolderName` = '" . $vBankAccountHolderName . "',
			`vBankLocation` = '" . $vBankLocation . "',
			`vBankName` = '" . $vBankName . "',
			`vAccountNumber` = '" . $vAccountNumber . "',
			`vBIC_SWIFT_Code` = '" . $vBIC_SWIFT_Code . "',
			`tProfileDescription` = '" . $tProfileDescription . "',
			`vCurrencyDriver`='" . $vCurrencyDriver . "',
			 $str1
             $str2
                 $str3
			`vLang` = '" . $vLang . "' $str" . $where;
        $obj->sql_query($query);
        if ($id == "") {
            $id = $obj->GetInsertId();
        }

        if ($action == 'Add') {
            if ($SITE_VERSION == "v5") {
                $set_driver_pref = $generalobj->Insert_Default_Preferences($id);
            }
            if (SITE_TYPE == 'Demo') {
                //Added By HJ On 27-07-2019 For Add Money Into Wallet When Register Driver In Demo Mode Start - Discuss With CD and KS Sir
                $tDescription = '#LBL_AMOUNT_CREDIT#';
                $ePaymentStatus = 'Unsettelled';
                $dDate = Date('Y-m-d H:i:s');
                $generalobj->InsertIntoUserWallet($id, "Driver", 500, 'Credit', 0, "Deposit", $tDescription, $ePaymentStatus, $dDate);
                //Added By HJ On 27-07-2019 For Add Money Into Wallet When Register Driver In Demo Mode End - Discuss With CD and KS Sir
            }
            if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $onlyDeliverallModule == "No") {
                $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE `eType` = 'UberX'";
                $result = $obj->MySQLSelect($query);

                $Drive_vehicle['iDriverId'] = $id;
                $Drive_vehicle['iCompanyId'] = $iCompanyId;
                $Drive_vehicle['iMakeId'] = "3";
                $Drive_vehicle['iModelId'] = "1";
                $Drive_vehicle['iYear'] = Date('Y');
                $Drive_vehicle['vLicencePlate'] = "My Services";
                $Drive_vehicle['eStatus'] = "Active";
                $Drive_vehicle['eCarX'] = "Yes";
                $Drive_vehicle['eType'] = "UberX";
                $Drive_vehicle['eCarGo'] = "Yes";
                if (SITE_TYPE == 'Demo') {
                    $Drive_vehicle['vCarType'] = $result[0]['countId'];
                } else {
                    $Drive_vehicle['vCarType'] = "";
                }
                //$Drive_vehicle['vCarType'] = $result[0]['countId'];
                $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');

                if ($APP_TYPE == 'UberX') {
                    $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                    $obj->sql_query($sql);
                }

                /* $sql="select iVehicleTypeId,iVehicleCategoryId,eFareType,fFixedFare,fPricePerHour from vehicle_type where 1=1";
                  $data_vehicles = $obj->MySQLSelect($sql);
                  //echo "<pre>";print_r($data_vehicles);exit;

                  if($data_vehicles[$i]['eFareType'] != "Regular")
                  {
                  for($i=0 ; $i < count($data_vehicles); $i++){
                  $Data_service['iVehicleTypeId'] = $data_vehicles[$i]['iVehicleTypeId'];
                  $Data_service['iDriverVehicleId'] = $iDriver_VehicleId;

                  if($data_vehicles[$i]['eFareType'] == "Fixed"){
                  $Data_service['fAmount'] = $data_vehicles[$i]['fFixedFare'];
                  }
                  else if($data_vehicles[$i]['eFareType'] == "Hourly"){
                  $Data_service['fAmount'] = $data_vehicles[$i]['fPricePerHour'];
                  }
                  $data_service_amount = $obj->MySQLQueryPerform('service_pro_amount',$Data_service,'insert');
                  }
                  } */

                if ($APP_TYPE == 'Ride-Delivery-UberX') {
                    if (SITE_TYPE == 'Demo') {
                        $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE (`eType` = 'Ride' OR `eType` = 'Deliver')";
                        $result = $obj->MySQLSelect($query);

                        $query1 = "SELECT GROUP_CONCAT(iVehicleTypeId)as countRentalId FROM `vehicle_type` WHERE `eType` = 'Ride'";
                        $resultrental = $obj->MySQLSelect($query1);

                        $Drive_vehicle_Ride['iDriverId'] = $id;
                        $Drive_vehicle_Ride['iCompanyId'] = $iCompanyId;
                        $Drive_vehicle_Ride['iMakeId'] = "5";
                        $Drive_vehicle_Ride['iModelId'] = "18";
                        $Drive_vehicle_Ride['iYear'] = "2014";
                        $Drive_vehicle_Ride['vLicencePlate'] = "CK201";
                        $Drive_vehicle_Ride['eStatus'] = "Active";
                        $Drive_vehicle_Ride['eCarX'] = "Yes";
                        $Drive_vehicle_Ride['eCarGo'] = "Yes";
                        //$Drive_vehicle_Ride['eType'] = "Ride";
                        $Drive_vehicle_Ride['vCarType'] = $result[0]['countId'];
                        if (ENABLE_RENTAL_OPTION == 'Yes') {
                            $Drive_vehicle_Ride['vRentalCarType'] = $resultrental[0]['countRentalId'];
                        }
                        $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle_Ride, 'insert');
                        $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                        $obj->sql_query($sql);

                        //commented following code bc no need to add two etype vehicles and not given etype as told by KS on 09-05-2020..
                        //$query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE (`eType` = 'Ride' OR `eType` = 'Deliver')";
                        //$result = $obj->MySQLSelect($query);
                        //$Drive_vehicle_Deliver['iDriverId'] = $id;
                        //$Drive_vehicle_Deliver['iCompanyId'] = $iCompanyId;
                        //$Drive_vehicle_Deliver['iMakeId'] = "5";
                        //$Drive_vehicle_Deliver['iModelId'] = "18";
                        //$Drive_vehicle_Deliver['iYear'] = "2014";
                        //$Drive_vehicle_Deliver['vLicencePlate'] = "CK201";
                        //$Drive_vehicle_Deliver['eStatus'] = "Active";
                        //$Drive_vehicle_Deliver['eCarX'] = "Yes";
                        //$Drive_vehicle_Deliver['eCarGo'] = "Yes";
                        //$Drive_vehicle_Deliver['eType'] = "Delivery";
                        //$Drive_vehicle_Deliver['vCarType'] = $result[0]['countId'];
                        //$iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle_Deliver, 'insert');
                    }
                }
            } else {
                if (SITE_TYPE == 'Demo') {
                    if ($APP_TYPE == 'Delivery') {
                        $app_type = 'Deliver';
                    } else {
                        $app_type = $APP_TYPE;
                    }
                    if ($app_type == 'Ride-Delivery') {
                        $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE (`eType` = 'Ride' OR `eType` = 'Deliver')";

                        $query1 = "SELECT GROUP_CONCAT(iVehicleTypeId)as RentalcountId FROM `vehicle_type` WHERE (`eType` = 'Ride')";
                        $resultReantal = $obj->MySQLSelect($query1);
                    } else {
                        $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`  WHERE `eType` = '" . $app_type . "'";
                    }
                    $result = $obj->MySQLSelect($query);

                    $Drive_vehicle['iDriverId'] = $id;
                    $Drive_vehicle['iCompanyId'] = $iCompanyId;
                    $Drive_vehicle['iMakeId'] = "5";
                    $Drive_vehicle['iModelId'] = "18";
                    $Drive_vehicle['iYear'] = "2014";
                    $Drive_vehicle['vLicencePlate'] = "CK201";
                    $Drive_vehicle['eStatus'] = "Active";
                    $Drive_vehicle['eCarX'] = "Yes";
                    $Drive_vehicle['eCarGo'] = "Yes";
                    $Drive_vehicle['eType'] = $app_type;
                    $Drive_vehicle['vCarType'] = $result[0]['countId'];

                    if (($app_type == 'Ride' || $app_type == 'Ride-Delivery') && ENABLE_RENTAL_OPTION == 'Yes') {
                        $Drive_vehicle['vRentalCarType'] = $resultReantal[0]['RentalcountId'];
                    }
                    $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
                    $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                    $obj->sql_query($sql);
                }
            }
        }

        if ($_FILES['vImage']['name'] != "") {

            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            $img_path = $tconfig["tsite_upload_images_driver_path"];
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
                    $img = new SimpleImage();
                    list($width, $height, $type, $attr) = getimagesize($Photo_Gallery_folder . $img1);
                    if ($width < $height) {
                        $final_width = $width;
                    } else {
                        $final_width = $height;
                    }
                    $img->load($Photo_Gallery_folder . $img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $img1);
                    $img1 = $generalobj->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }
            }
            $vImgName = $img1;
            $sql = "UPDATE " . $tbl_name . " SET `vImage` = '" . $vImgName . "' WHERE `iDriverId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }

        if ($action == 'Add') {
            $maildata['EMAIL'] = $vEmail;
            $maildata['NAME'] = $vName . ' ' . $vLastName;
            $maildata['PASSWORD'] = $langage_lbl_admin["LBL_PASSWORD"] . ": " . $vPassword;
            $maildata['SOCIALNOTES'] = '';
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
        }

        //End :: Upload Image Script
        header("Location:" . $backlink);
        exit;
    }
}
// for Edit

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iDriverId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    // $vPass = $generalobj->decrypt($db_data[0]['vPassword']);
    if ($db_data[0]['eStatus'] == "active") {
        $actionType = "approve";
    } else {
        $actionType = "pending";
    }
    $vLabel = $id;
    if (count($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vName = $generalobjAdmin->clearName(" " . $value['vName']);;
            $iCompanyId = $value['iCompanyId'];
            $vLastName = $generalobjAdmin->clearName(" " . $value['vLastName']);
            $vCaddress = $value['vCaddress'];
            $vCountry = $value['vCountry'];
            $vCity = $value['vCity'];
            $vZip = $value['vZip'];
            $vState = $value['vState'];
            $vCode = $value['vCode'];
            $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
            $vUserName = $value['vLoginId'];
            $vPassword = $value['vPassword'];
            /* $dBirthDate = $value['dBirthDate'];
              if($dBirthDate == "0000-00-00")
              {
              $dBirthDate = "";
              } */
            /* 				$dBirthYear = date("Y",strtotime($value['dBirthDate']));
              $dBirthMonth = date("m",strtotime($value['dBirthDate']));
              $dBirthDay = date("d",strtotime($value['dBirthDate']));
              if($dBirthYear == "0000" ||  $dBirthMonth == "00" || $dBirthDay == "00")
              {
              $dBirthDate = "";
              } */
            $eGender = $value['eGender'];
            $vPhone = $generalobjAdmin->clearPhone($value['vPhone']);
            $vLang = $value['vLang'];
            $oldImage = $value['vImage'];
            $vCurrencyDriver = $value['vCurrencyDriver'];
            $vPaymentEmail = $value['vPaymentEmail'];
            $vBankAccountHolderName = $value['vBankAccountHolderName'];
            $vAccountNumber = $value['vAccountNumber'];
            $vBankLocation = $value['vBankLocation'];
            $vBankName = $value['vBankName'];
            $vBIC_SWIFT_Code = $value['vBIC_SWIFT_Code'];
            $tProfileDescription = $value['tProfileDescription'];
            $eEnableDemoLocDispatch = $value['eEnableDemoLocDispatch'];
        }
    }

    if ($SITE_VERSION == "v5") {
        $sql = "select * from preferences where eStatus ='Active'";
        $data_preference = $obj->MySQLSelect($sql);

        $data_driver_pref = $generalobj->Get_User_Preferences($id);
    }
}
$isStoreDriverOption = isStoreDriverAvailable();
$serviceStoreArr =$serviceArr= array();
$selectedServiceId = 0;
if($isStoreDriverOption > 0){
    $serviceArr = json_decode(serviceCategories, true);
    $getStoreList = $obj->MySQLSelect("SELECT iServiceId,iCompanyId,vCompany,eStatus FROM company WHERE eStatus = 'Active' AND vCompany != '' AND iServiceId > 0 ORDER BY vCompany ASC");
    for($g=0;$g<count($getStoreList);$g++){
        if($iCompanyId == $getStoreList[$g]['iCompanyId']){
            $selectedServiceId = $getStoreList[$g]['iServiceId'];
        }
        $serviceStoreArr[$getStoreList[$g]['iServiceId']][] = $getStoreList[$g];
    }
}
//echo "<pre>";print_r($serviceArr);die;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?
        include_once('global_files.php');
        ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
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
                            <h2><?= $action; ?> <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>  <?= $vName; ?></h2>
                            <a href="javascript:void(0);" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } ?>
                            <? if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php print_r($error); ?>
                                </div><br/>
                            <? } ?>
                            <form id="_driver_form" name="_driver_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                                <input type="hidden" name="id" id="iDriverId" value="<?= $id; ?>"/>
                                <input type="hidden" name="oldImage" value="<?= $oldImage; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="driver.php"/>
                                <?php if ($id) { ?>
                                    <div class= "row col-md-12" id="hide-profile-div">
                                        <? $class = ($SITE_VERSION == "v5") ? "col-lg-3" : "col-lg-4"; ?>
                                        <div class="<?= $class ?>">
                                            <b><?php if ($oldImage == 'NONE' || $oldImage == '') { ?>
                                                    <img src="../assets/img/profile-user-img.png" alt="" >
                                                    <?
                                                } else {
                                                    if (file_exists('../webimages/upload/Driver/' . $id . '/3_' . $oldImage)) {
                                                        ?>
                                                       <!--  <img src = "<?php echo $tconfig["tsite_upload_images_driver"] . '/' . $id . '/3_' . $oldImage ?>" class="img-ipm" /> -->

                                                         <img src = "<?= $tconfig["tsite_url"].'resizeImg.php?h=170&src='.$tconfig["tsite_upload_images_driver"] . '/' . $id . '/3_' . $oldImage ?>" class="img-ipm" />

                                                    <? } else { ?>
                                                        <img src="../assets/img/profile-user-img.png" alt="" >
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </b>
                                        </div>
                                        <? if ($SITE_VERSION == "v5") { ?>
                                            <div class="col-lg-4">
                                                <fieldset class="col-md-12 field">
                                                    <legend class="lable"><h4 class="headind1"> Preferences: </h4></legend>
                                                    <p>
                                                    <div class=""> <? foreach ($data_driver_pref as $val) { ?>
                                                            <img data-toggle="tooltip" class="borderClass-aa1 border_class-bb1" title="<?= $val['pref_Title'] ?>" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $val['pref_Image'] ?>">
                                                        <? } ?>
                                                    </div>

                                                    <span class="col-md-12"><a href="" data-toggle="modal" data-target="#myModal" id="show-edit-language-div" class="hide-language1">
                                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                                            Manage Preferences</a></span>
                                                    </p>
                                                </fieldset>
                                            </div>
                                        <? } ?>
                                    </div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>First Name<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vName"  id="vName" value="<?= $vName; ?>" placeholder="First Name" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Last Name<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vLastName"  id="vLastName" value="<?= $vLastName; ?>" placeholder="Last Name" >
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Email<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vEmail"  id="vEmail" value="<?= $vEmail; ?>" placeholder="Email" >
                                    </div><div id="emailCheck"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Password <span class="red"> *</span>
                                            <?php if ($action == 'Edit') { ?>
                                                <span>&nbsp;[Leave blank to retain assigned password.]</span>
                                            <?php } ?>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="password" class="form-control" name="vPassword"  id="vPassword" value="" placeholder="Password" >
                                    </div>
                                </div>

                                <!--<div class="row">
<div class="col-lg-12">
  <label>Birth date <span class="red"> *</span></label>
</div>
<div class="col-lg-6">
  <input type="text" id="dp5" name="dBirthDate" placeholder="From Date"  readonly class="form-control" value="<?= $dBirthDate ?>" style="cursor:default; background-color: #fff" required />
</div>
</div>-->

                                <!-- 									<div class="row">
                                                                                                                        <div class="col-md-6">
                                                                                                                                <label class="date-birth">
                                <?= $langage_lbl_admin['LBL_Date_of_Birth']; ?><label>
                                                                                                                                <select name="vDay" Id="vDay" data="DD" class="custom-select-new required">
                                                                                                                                        <option value="">Date</option>
                                <?php for ($i = 1; $i <= 31; $i++) { ?>
                                                                                                                                                                    <option value="<?= $i ?>" <?= ($i == $dBirthDay ) ? 'Selected' : ''; ?>>
                                    <?= $i ?>
                                                                                                                                                                    </option>
                                <?php } ?>
                                                                                                                                </select>
                                                                                                                                <select data="MM" Id="vMonth" name="vMonth" class="custom-select-new required" >
                                                                                                                                        <option value="">Month</option>
                                <?php for ($i = 1; $i <= 12; $i++) { ?>
                                                                                                                                                                    <option value="<?= $i ?>" <?= ($i == $dBirthMonth ) ? 'Selected' : ''; ?>>
                                    <?= $i ?>
                                                                                                                                                                    </option>
                                <?php } ?>
                                                                                                                                </select>
                                                                                                                                <select data="YYYY" Id="vYear" name="vYear" class="custom-select-new required">
                                                                                                                                        <option value="">Year</option>
                                <?php for ($i = (date("Y") - $START_BIRTH_YEAR_DIFFERENCE); $i >= (date("Y") - $BIRTH_YEAR_DIFFERENCE); $i--) { ?>
                                                                                                                                                                    <option value="<?= $i ?>" <?= ($i == $dBirthYear ) ? 'Selected' : ''; ?>>
                                    <?= $i ?>
                                                                                                                                                                    </option>
                                <?php } ?>
                                                                                                                                </select>
                                                                                                                                                                                                        
                                                                                                                        </div>
                                                                                                                </div> -->	

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Profile Picture</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="file" class="form-control" name="vImage"  id="vImage" placeholder="Name Label" style="padding-bottom: 39px;">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Country <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                         <?php 
                                        if(count($db_country) > 1){ 
                                              $style = "";
                                             }else{
                                            $style = " disabled=disabled";
                                        } ?>
                                        <select <?= $style ?> class="form-control" name = 'vCountry' id="vCountry" onChange="setState(this.value, ''), changeCode(this.value);" >
                                            <?php 
                                            if(count($db_country) > 1){ ?>
                                                <option value="">Select</option>
                                             <?php } ?>
                                            <? for ($i = 0; $i < count($db_country); $i++) { ?>
                                                <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>State</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name = 'vState' id="vState" onChange="setCity(this.value, '');" >
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <?php if($SHOW_CITY_FIELD=='Yes') { ?>   
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>City</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name = 'vCity' id="vCity"  >
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                </div>
                                <?php } ?>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Address</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text"  class="form-control" name="vCaddress"  id="vCaddress" value="<?= $vCaddress ?>" placeholder="Address" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_ZIP_CODE_SIGNUP']; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vZip"  id="vZip" value="<?= $vZip; ?>" placeholder="<?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Phone<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-select-2" id="code" name="vCode" value="<?= $vCode ?>"  readonly style="width: 10%;height: 36px;text-align: center;"/ >
                                               <input type="text" class="form-control"  style="margin-top: 5px; width:90%;" name="vPhone"  id="vPhone" value="<?= $vPhone; ?>" placeholder="Phone" >
                                    </div>
                                </div>
                                <?php if($isStoreDriverOption > 0){ ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Service Category<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select onchange="displayStoreList(this.value);"  class="form-control" name = 'iServiceId'  id= 'iServiceId' required="required" <? if($action!="Add") { ?>disabled<? } ?>>
                                                <option value="0">General</option>
                                                <?php for ($s = 0; $s < count($serviceArr); $s++) { ?>
                                                    <option value = "<?= $serviceArr[$s]['iServiceId'] ?>" <?= ($serviceArr[$s]['iServiceId'] == $iCompanyId) ? 'selected' : ''; ?>>
                                                        <?= $generalobjAdmin->clearCmpName($serviceArr[$s]['vServiceName']); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" id="storelisthtml">
                                        <div class="col-lg-12">
                                            <label>Store Selection<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select class="form-control" name = 'storeId'  id= 'iCompanyIdhtml' required="required"<? if($action!="Add") { ?>disabled<? } ?>>
                                                
                                            </select>
                                        </div>
                                    </div>
                                <?php } ?>
                                
                                <? if ($onlyDeliverallModule == 'No') { ?>
                                    <div class="row" id="companylisthtml">
                                        <div class="col-lg-12">
                                            <label>Company<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">

                                            <select  class="form-control" name = 'iCompanyId'  id= 'iCompanyId' required="required"<? if($action!="Add") { ?>disabled<? } ?>>
                                                <option value="">--select--</option>
                                                <?
                                                for ($i = 0; $i < count($db_company); $i++) {
                                                    $status_cmp = ($db_company[$i]['eStatus'] == "Inactive") ? " (Inactive)" : "";
                                                    ?>
                                                    <option value = "<?= $db_company[$i]['iCompanyId'] ?>" <?= ($db_company[$i]['iCompanyId'] == $iCompanyId) ? 'selected' : ''; ?>>
                                                        <?= $generalobjAdmin->clearCmpName($db_company[$i]['vCompany'] . $status_cmp); ?>
                                                    </option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                <? } ?>
                                <? if($onlyDeliverallModule != 'Yes' && APP_TYPE != "Delivery" && APP_TYPE != "UberX") { ?>
                                <div class="row" id="gendershown">
                                    <div class="col-lg-12">
                                        <label>Gender</label>
                                    </div>
                                    <div class="col-lg-6 ">
                                        <input id="r4" name="eGender" type="radio" value="Male"
                                        <?php
                                        if ($eGender == 'Male' && $action != "Add") {
                                            echo 'checked';
                                        }
                                        ?> >
                                        <label for="r4">Male</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input id="r5" name="eGender" type="radio" value="Female" class="required" 
                                        <?php
                                        if ($eGender == 'Female' && $action != "Add") {
                                            echo 'checked';
                                        }
                                        ?> >
                                        <label for="r5">Female</label>
                                    </div>
                                </div>
                                <? } ?>
                                <?php if (count($db_lang) <= 1) { ?>
                                    <input name="vLang" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode']; ?>"/>
                                <?php } else { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Language<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select  class="form-control" name='vLang' id='vLang'>
                                                <option value="">--select--</option>
                                                <? for ($i = 0; $i < count($db_lang); $i++) { ?>
                                                    <option value = "<?= $db_lang[$i]['vCode'] ?>" <?= ($db_lang[$i]['vCode'] == $vLang) ? 'selected' : ''; ?>>
                                                        <?= $db_lang[$i]['vTitle'] ?>
                                                    </option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php if(count($db_currency) <= 1) { ?>
                            <input name="vCurrencyDriver" type="hidden" class="create-account-input" value="<?php echo $db_currency[0]['vName']; ?>"/>
                            <? } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Currency <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name = 'vCurrencyDriver'  id= 'vCurrencyDriver' >
                                            <option value="">--select--</option>
                                            <? for ($i = 0; $i < count($db_currency); $i++) { ?>
                                                <option value = "<?= $db_currency[$i]['vName'] ?>" <? if ($vCurrencyDriver == $db_currency[$i]['vName']) { ?>selected<? } else if ($db_currency[$i]['eDefault'] == "Yes" && $vCurrencyDriver == '') { ?>selected<? } ?>><?= $db_currency[$i]['vName'] ?></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                </div>                                     
                            <? } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Payment Email</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="email"  class="form-control" name="vPaymentEmail"  id="vPaymentEmail" value="<?= $generalobj->clearEmail(" " . $vPaymentEmail) ?>" placeholder="Payment Email" >
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Account Holder Name</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text"  class="form-control" name="vBankAccountHolderName"  id="vBankAccountHolderName" value="<?= $generalobj->clearName(" " . $vBankAccountHolderName) ?>" placeholder="Account Holder Name" >
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Account Number</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text"  class="form-control" name="vAccountNumber"  id="vAccountNumber" value="<?= $generalobj->clearName(" " .$vAccountNumber)   ?>" placeholder="Account Number" >
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Bank Name</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text"  class="form-control" name="vBankName"  id="vBankName" value="<?= $generalobj->clearName(" " .$vBankName) ?>" placeholder="Bank Name" >
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Bank Location</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vBankLocation"  id="vBankLocation" value="<?= $generalobj->clearName(" " .$vBankLocation) ?>" placeholder="Bank Location" >
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>BIC/SWIFT Code</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text"  class="form-control" name="vBIC_SWIFT_Code"  id="vBIC_SWIFT_Code" value="<?= $generalobj->clearName(" " .$vBIC_SWIFT_Code) ?>" placeholder="BIC/SWIFT Code" >
                                    </div>
                                </div>
                                <?php if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $onlyDeliverallModule != 'Yes' && strtoupper($ufxService) == "YES") { ?>
                                    <div style="clear: both;"></div>
                                    <div class="row" id="servicedesc">
                                        <div class="col-lg-12">
                                            <label>Service Description </label>
                                        </div>
                                        <div class="col-lg-6"><textarea name="tProfileDescription" rows="3" cols="40" class="form-control" id="tProfileDescription" placeholder="Service Description"><?= $tProfileDescription; ?></textarea>
                                        </div>
                                    </div>
                                <?php } if (strtoupper($userType) == "QA") { ?>
                                    <div class="row" id="poolenable">
                                        <div class="col-lg-12">
                                            <label>Enable Demo Loation Dispatch</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" id="eEnableDemoLocDispatch" name="eEnableDemoLocDispatch" <?= ($eEnableDemoLocDispatch == 'Yes') ? 'checked' : ''; ?>/>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">

                                        <?php if (($action == 'Add' && $userObj->hasPermission('create-providers')) || ($action == 'Edit' && $userObj->hasPermission('edit-providers'))) { ?>

                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php if ($action == 'Add') { ?><?= $action; ?> <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?><?php } else { ?>Update<?php } ?>">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <!-- <a href="javascript:void(0);" onClick="reset_form('_driver_form');" class="btn btn-default">Reset</a> -->
                                        <a href="driver.php" class="btn btn-default back_link">Cancel</a>
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

        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-medium">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">x</span>
                        </button>
                        <h4 class="modal-title " id="myModalLabel">Manage <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Preferences</h4>
                    </div>
                    <div class="modal-body">
                        <span>
                            <form name="frm112" action="" method="POST">
                                <? foreach ($data_preference as $value) { ?>

                                    <div class="preferences-chat">
                                        <b class="car-preferences-right-part1"><?= $value['vName'] ?></b>

                                        <b class="car-preferences-right-part-a">
                                            <span data-toggle="tooltip" title="<?= $value['vYes_Title'] ?>"><a href="#"><img class="borderClass-aa1 borderClass-aa2" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $value['vPreferenceImage_Yes'] ?>" alt="" id="img_Yes_<?= $value['iPreferenceId'] ?>" onClick="checked_val('<?= $value['iPreferenceId'] ?>', 'Yes')"/></a></span></b>
                                        <b class="car-preferences-right-part-a"><span data-toggle="tooltip" title="<?= $value['vNo_Title'] ?>"><a href="#"><img class="borderClass-aa1 borderClass-aa2" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $value['vPreferenceImage_No'] ?>" alt="" id="img_No_<?= $value['iPreferenceId'] ?>" onClick="checked_val('<?= $value['iPreferenceId'] ?>', 'No')"/></a></span></b>
                                    </div>


                                    <span style="display:none;">
                                        <input type="radio" name="vChecked_<?= $value['iPreferenceId'] ?>" id="Yes_<?= $value['iPreferenceId'] ?>" value="Yes">
                                        <input type="radio" name="vChecked_<?= $value['iPreferenceId'] ?>" id="No_<?= $value['iPreferenceId'] ?>" value="No">
                                    </span> 
                                <? } ?>
                                <p class="car-preferences-right-part-b">
                                    <input name="btnsubmit" type="submit" value="<?= $langage_lbl_admin['LBL_Save']; ?>" class="save-but1">

                                </p>

                            </form>
                        </span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once('footer.php'); ?>

        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script>
                                            $('#_driver_form').validate({
                                            rules: {
                                            vName: {
                                            required: true, minlength: 2, maxlength: 30
                                            },
                                                    vLastName: {
                                                    required: true, minlength: 2, maxlength: 30
                                                    },
                                                    vEmail: {
                                                    required: true, email: true
                                                    },
<?php if ($id == '') { ?>	vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},<?php } ?>
                                            vCountry: {
                                            required: true
                                            },
                                                    vPhone: {
                                                    required: true, minlength: 3, digits: true
                                                    },
                                                    vLang: {
                                                    required: true
                                                    },
<?php if ($onlyDeliverallModule == 'No') { ?> iCompanyId: { 	required: true },<?php } ?>
                                            vCurrencyDriver: {
                                            required: true
                                            }
                                            },
                                                submitHandler: function (form) {
                                                    $("#vCountry").prop('disabled',false);
                                                    if ($(form).valid())
                                                        form.submit();
                                                    return false; // prevent normal form posting
                                                }
                                            });
                                            var serviceStoreArr = <?= json_encode($serviceStoreArr); ?>;
                                            var selCompanyId = '<?= $iCompanyId; ?>';
                                            var selServiceId = '<?= $selectedServiceId; ?>';
                                            <?php if($isStoreDriverOption > 0){ ?>
                                                displayStoreList(selServiceId);
                                            <?php } ?>
                                            function displayStoreList(serviceId){
                                                $("#iServiceId").val(serviceId);
                                                if(serviceId > 0){
                                                    $("#storelisthtml").show();
                                                    $("#gendershown").hide();
                                                    $("#companylisthtml,#servicedesc").hide();
                                                    var optionhtml = "";
                                                    var serviceData = serviceStoreArr[serviceId];
                                                    for(var h=0;h<serviceData.length;h++){
                                                        var selectionhtml = "";
                                                        if(selCompanyId == serviceData[h]['iCompanyId']){
                                                            selectionhtml = "selected='selected'";
                                                        }
                                                        optionhtml += "<option "+selectionhtml+" value='"+serviceData[h]['iCompanyId']+"'>"+serviceData[h]['vCompany']+"</option>"
                                                    }
                                                }else{
                                                    $("#storelisthtml").hide();
                                                    $("#gendershown").show();
                                                    $("#companylisthtml,#servicedesc").show();
                                                    //$("#iCompanyIdhtml").removeAttr("required");
                                                }
                                                
                                                $("#iCompanyIdhtml").html(optionhtml);
                                                console.log(serviceStoreArr);
                                            }
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

                                            $(document).ready(function() {
                                            var referrer;
                                            if ($("#previousLink").val() == ""){
                                            referrer = document.referrer;
                                            } else {
                                            referrer = $("#previousLink").val();
                                            }
                                            if (referrer == "") {
                                            referrer = "driver.php";
                                            } else {
                                            $("#backlink").val(referrer);
                                            }
                                            $(".back_link").attr('href', referrer);
                                            var date = new Date();
                                            var currentMonth = date.getMonth();
                                            var currentDate = date.getDate();
                                            var currentYear = date.getFullYear();
                                            /*			$("#dp56").datepicker({
                                             endDate: new Date(),
                                             autoclose: true 
                                             },'update' , '<?= $dBirthDate ?>');*/
                                            });
                                            function setCity(id, selected)
                                            {
                                            var fromMod = 'driver';
                                            var request = $.ajax({
                                            type: "POST",
                                                    url: 'change_stateCity.php',
                                                    data: {stateId: id, selected: selected, fromMod:fromMod},
                                                    success: function (dataHtml)
                                                    {
                                                    $("#vCity").html(dataHtml);
                                                    }
                                            });
                                            }

                                            function setState(id, selected)
                                            {
                                            var fromMod = 'driver';
                                            var request = $.ajax({
                                            type: "POST",
                                                    url: 'change_stateCity.php',
                                                    data: {countryId: id, selected: selected, fromMod:fromMod},
                                                    success: function (dataHtml)
                                                    {
                                                    $("#vState").html(dataHtml);
                                                    if (selected == '')
                                                            setCity('', selected);
                                                    }
                                            });
                                            }
                                            $('#dp5').datepicker({
                                            maxDate: 0,
                                                    onRender: function(date) {
                                                    return date.valueOf() > new Date().valueOf() ? 'disabled' : '';
                                                    }
                                            });
                                            setState('<?php echo $vCountry; ?>', '<?php echo $vState; ?>');
                                            changeCode('<?php echo $vCountry; ?>');
                                            setCity('<?php echo $vState; ?>', '<?php echo $vCity; ?>');
                                            function checked_val(id, value){
                                            $("#img_Yes_" + id).removeClass('border_class-aa1');
                                            $("#img_No_" + id).removeClass('border_class-aa1');
                                            $("#img_" + value + "_" + id).addClass('border_class-aa1');
                                            $("#Yes_" + id).prop("checked", false);
                                            $("#No_" + id).prop("checked", false);
                                            $("#" + value + "_" + id).prop("checked", true);
                                            return false;
                                            }

                                            $(window).on("load", function(){
<? if (count($data_driver_pref) > 0) { ?>
                                                var dataarr = '<?= json_encode($data_driver_pref) ?>';
                                                var arr1 = JSON.parse(dataarr);
                                                for (var i = 0; i < arr1.length; i++){
                                                checked_val(arr1[i].pref_Id, arr1[i].pref_Type)
                                                }
<? } ?>
                                            });


        </script>
    </body>
    <!-- END BODY-->
</html>
