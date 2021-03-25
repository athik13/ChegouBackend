<?php
include_once("common.php");
include_once ('include_generalFunctions_dl.php');
include_once('assets/libraries/configuration.php');
$service_categories = array();
if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
    $service_categories = $serviceCategoriesTmp;
}
//echo "<pre>";print_R($_POST);die;
if (isset($_POST['SUBMIT'])) {
    //echo "<pre>";print_r($_POST);die;
    $fromOrder = "guest";
    if (isset($_POST['fromOrder']) && $_POST['fromOrder'] != "") {
        $fromOrder = $_POST['fromOrder'];
    }
    $userSession = "MANUAL_ORDER_" . strtoupper($fromOrder);
    $orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
    $orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
    $orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
    $orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
    $orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
    $orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
    $orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
    $orderCurrencyNameSession = "MANUAL_ORDER_CURRENCY_NAME_" . strtoupper($fromOrder);
    $orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
    $orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
    $orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_" . strtoupper($fromOrder);
    $orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);
    $orderUserSession = "MANUAL_ORDER_USER_" . strtoupper($fromOrder);
    $orderUserNameSession = "MANUAL_ORDER_USER_NAME_" . strtoupper($fromOrder);
    $orderCompanyNameSession = "MANUAL_ORDER_COMPANY_NAME_" . strtoupper($fromOrder);
    $orderUserEmailSession = "MANUAL_ORDER_USER_EMAIL_" . strtoupper($fromOrder);
    $orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);
    $extraParameter = "?order=" . $fromOrder;

    unset($_SESSION[$orderDetailsSession]);
    unset($_SESSION[$userSession]);
    unset($_SESSION[$orderUserSession]);
    unset($_SESSION[$orderServiceSession]);
    unset($_SESSION[$orderUserIdSession]);
    unset($_SESSION[$orderAddressIdSession]);
    unset($_SESSION[$orderCouponSession]);
    unset($_SESSION[$orderCouponNameSession]);

    unset($_SESSION[$orderCurrencyNameSession]);
    //unset($_SESSION['sess_currentpage_url_mr']);
    unset($_SESSION[$orderLatitudeSession]);
    unset($_SESSION[$orderLongitudeSession]);
    unset($_SESSION[$orderAddressSession]);
    unset($_SESSION[$orderDataSession]);

    unset($_SESSION[$orderUserNameSession]);
    unset($_SESSION[$orderCompanyNameSession]);
    unset($_SESSION[$orderUserEmailSession]);
    unset($_SESSION[$orderStoreIdSession]);
    unset($_SESSION[$orderServiceNameSession]);
    //unset($_SESSION["sess_user_mr"]);
    $Data = $DataAddress = array();
    //$msg= $generalobj->checkDuplicateFront('vEmail', "register_user" , Array('vEmail=>$_POST[customer_info_vLastName']'),$tconfig["tsite_url"]."customer_info.php?error=1&var_msg=Email already Exists", "Email already Exists",'',"");
    $eReftype = "Rider";
    $_POST['eRefType'] = 'Driver';
    $_REQUEST['vPassword'] = '123456';
    $Data['vRefCode'] = $generalobj->ganaraterefercode($eReftype);
    if (isset($_POST['iRefUserId'])) {
        $Data['iRefUserId'] = $_POST['iRefUserId'];
    }
    $Data['eRefType'] = $_POST['eRefType'];
    $Data['vName'] = $_POST['customer_info_vName'];
    $Data['vLang'] = $_SESSION['sess_lang'];
    $Data['vLastName'] = $_POST['customer_info_vLastName'];
    $vEmail = $_POST['customer_info_vEmail'];
    $Data['vEmail'] = $_POST['customer_info_vEmail'];
    $Data['vPhone'] = $_POST['customer_info_vPhone'];
    $Data['vPhone'] = ltrim($Data['vPhone'], '0');
    $customer_info_vCountry = $_POST['customer_info_vCountry'];
    $sql = "SELECT vCountryCode,vCountry,vTimeZone from country where eStatus = 'Active' AND  vCountry='" . $customer_info_vCountry . "'";
    $db_code = $obj->MySQLSelect($sql);
    $Data['vCountry'] = $db_code[0]['vCountryCode'];
    $Data['vPhoneCode'] = $_POST['customer_info_vPhoneCode'];
    $vZip = "123456";
    if (isset($_POST['vZip'])) {
        $vZip = $_POST['vZip'];
    }
    $Data['vZip'] = $vZip;
    if (isset($_POST['vInviteCode'])) {
        $Data['vInviteCode'] = $_POST['vInviteCode'];
    }
    $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
    $currencyData = $obj->MySQLSelect($sqlp);
    $currencycode = $currencyData[0]['vName'];
    $Data['vCurrencyPassenger'] = $currencycode;
    //$Data['eGender'] = $_POST['eGender'];
    $Data['dRefDate'] = Date('Y-m-d H:i:s');
    $Data['vLatitude'] = $_POST['from_lat'];
    $Data['vLongitude'] = $_POST['from_long'];
    $Data['vLang'] = $_SESSION['sess_lang'];
    $Data['tRegistrationDate'] = Date('Y-m-d H:i:s');
    $Data['eStatus'] = 'Active';
    $areatext = $_POST['areatext'];
    $Data['vPassword'] = $generalobj->encrypt_bycrypt($_REQUEST['vPassword']);

    $registerid = '0';
    $iUserId = $_POST['iUserId'];
    if ($iUserId == '') {
        $_POST['iUserAddressId'] = '';
        if (SITE_TYPE == 'Demo') {
        $Data['eEmailVerified'] = $Data['ePhoneVerified'] = 'Yes';
        }
        if ($vEmail != '') {
            $sql = "SELECT iUserId  FROM `register_user`  WHERE vEmail = '" . $vEmail . "'";
            $Dataua = $obj->MySQLSelect($sql);
            if (count($Dataua) > 0) {
                header("Location:" . $tconfig["tsite_url"] . "user-order-information?error=1&var_msg=" . $langage_lbl["LBL_EMAIL_EXISTS_MSG"] . "&order=" . $fromOrder);
                exit;
            }
        }
        //echo "<pre>";print_r($Data);die;
        $registerid = $obj->MySQLQueryPerform("register_user", $Data, 'insert');
    } else {
        $whereUserId = " iUserId = '" . $iUserId . "'";
        //echo "<pre>";print_r($Data);die;
        $data_update = array();
        $data_update['vName'] = $Data['vName'];
        $data_update['vLastName'] = $Data['vLastName'];
        $data_update['vEmail'] = $Data['vEmail'];
        $data_update['vPhone'] = $Data['vPhone'];
        $data_update['vCountry'] = $Data['vCountry'];
        $data_update['vPhoneCode'] = $Data['vPhoneCode'];
        //echo "<pre>";print_r($Data);die;
        if ($vEmail != '') {
            $sql = "SELECT iUserId  FROM `register_user`  WHERE vEmail = '" . $vEmail . "' AND iUserId !='" . $iUserId . "'";
            $Dataua = $obj->MySQLSelect($sql);
            //echo "<pre>";print_r($iUserId);die;
            if (count($Dataua) > 0) {
                header("Location:" . $tconfig["tsite_url"] . "user-order-information?error=1&var_msg=" . $langage_lbl["LBL_EMAIL_EXISTS_MSG"] . "&order=" . $fromOrder);
                exit;
            }
        }
        $user_Update_Id = $obj->MySQLQueryPerform("register_user", $Data, 'update', $whereUserId);
    }
    if ($registerid == "0") {
        $iUserId = $_POST['iUserId'];
        $mail = 'No';
    } else {
        $iUserId = $registerid;
        if ($vEmail != '') {
            $mail = 'Yes';
        } else {
            $mail = 'No';
        }
    }
    $iUserAddressId = $_POST['iUserAddressId'];
    if ($_POST['vServiceAddress'] != "" && !empty($_POST['vServiceAddress']) && $_POST['vBuildingNo'] != "" && !empty($_POST['vBuildingNo']) && $_POST['vLandmark'] != "" && !empty($_POST['vLandmark']) && empty($_POST['iUserAddressId'])) {
        $DataAddress['iUserId'] = $iUserId;
        $DataAddress['eUserType'] = 'Rider';
        $DataAddress['vServiceAddress'] = $_POST['vServiceAddress'];
        $DataAddress['vBuildingNo'] = $_POST['vBuildingNo'];
        $DataAddress['vLandmark'] = $_POST['vLandmark'];
        $DataAddress['vAddressType'] = $_POST['vAddressType'];
        $DataAddress['vLatitude'] = $_POST['from_lat'];
        $DataAddress['vLongitude'] = $_POST['from_long'];
        $DataAddress['dAddedDate'] = date('Y-m-d H:i:s');
        $DataAddress['vTimeZone'] = $db_code[0]['vTimeZone'];
        $DataAddress['eStatus'] = 'Active';
        $iUserAddressId = $obj->MySQLQueryPerform("user_address", $DataAddress, 'insert');
    } else {
        $iUserAddressId = $_POST['iUserAddressId'];
    }
    //echo $iUserId."eeeeeeeeeeeeee";exit;
    //echo $orderUserIdSession;die;
    if ($iUserId != "") {
        $_SESSION[$orderUserIdSession] = $iUserId;
        ///echo "<pre>";print_r($_SESSION);die;
        $_SESSION[$orderUserNameSession] = $Data['vName'] . ' ' . $Data['vLastName'];
        $_SESSION[$orderCompanyNameSession] = " ";
        $_SESSION[$orderUserEmailSession] = $Data['vEmail'];
        $_SESSION[$orderAddressIdSession] = $iUserAddressId;
        //$_SESSION["sess_user_mr"] = "rider";
        //$_SESSION['sess_userby_mr'] = check_user_mr();
        $_SESSION[$orderServiceSession] = $_POST['serviceid'];
        $_SESSION[$orderServiceNameSession] = $service_categories[($_SESSION[$orderServiceSession] - 1)]['vServiceName'];
        //$_SESSION[$orderCurrencyNameSession] = $Data['vCurrencyPassenger'];
        $maildata['EMAIL_mr'] = $_SESSION[$orderUserEmailSession];
        $maildata['NAME_mr'] = $_SESSION[$orderUserNameSession];
        $maildata['PASSWORD_mr'] = $langage_lbl["LBL_PASSWORD"] . ": " . $_REQUEST['vPassword'];
        $maildata['SOCIALNOTES_mr'] = '';
        unset($_SESSION[$orderDetailsSession]);
        if ($mail == "Yes") {
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);
        }
        if ($_REQUEST['depart'] != "" && $_REQUEST['depart'] == 'mobi') {
            header("Location:mobi" . $extraParameter);
            exit;
        }
        
        if(checkSystemStoreSelection()) {
            $service_categories = array();
            if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
                $service_categories = $serviceCategoriesTmp;
            }
            $cnt_sc = count($service_categories);
            //if($cnt_sc==1) {
                session_start();
                $store_data = $generalobj->getStoreDataForSystemStoreSelection($_POST['serviceid']);
                //$iCompanyId = $store_data[0]['iCompanyId'];
                $iCompanyId = $store_data['iCompanyId'];
                
                $_SESSION[$orderLongitudeSession] = $store_data['vRestuarantLocationLat'];
                $_SESSION[$orderLatitudeSession] = $store_data['vRestuarantLocationLong'];
                $_SESSION[$orderServiceSession] = $store_data['iServiceId'];
                $_SESSION[$orderAddressSession] = $store_data['vCaddress'];
                $_SESSION[$orderServiceNameSession] = $service_categories[($store_data['iServiceId'] - 1)]['vServiceName'];
                header("location: store-items?id=" . $iCompanyId . "&order=" . $fromOrder);
                exit;
            //}
        }
        header("Location:store-listing" . $extraParameter);
        exit;
    }
}
?>
