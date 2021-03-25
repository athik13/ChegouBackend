<?php

include_once('common.php');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$iCompanyId = $_SESSION['sess_iCompanyId'];
$iUserId = $_SESSION['sess_iUserId'];
$tbl = 'register_user';
$where = " WHERE `iUserId` = '" . $iUserId . "'";
if ($action == 'all') { //added by SP for cubex theme on 5-9-2019
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'user@demo.com')) {
        echo $var_msg = '2';
        return $var_msg;
        exit;
    }

    $cpass = isset($_REQUEST['cpass']) ? $_REQUEST['cpass'] : '';
    $email = isset($_REQUEST['email']) ? strtolower($_REQUEST['email']) : '';
    $fname = isset($_REQUEST['fname']) ? $_REQUEST['fname'] : '';
    $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
    $location = isset($_POST['country']) ? $_POST['country'] : '';
    $lang = isset($_POST['lang1']) ? $_POST['lang1'] : '';
    $vCurrencyPassenger = isset($_POST['vCurrencyPassenger']) ? $_POST['vCurrencyPassenger'] : '';
    $_SESSION["sess_vCurrency"] = $vCurrencyPassenger;

    $npass = isset($_REQUEST['npass']) ? $_REQUEST['npass'] : '';
    $npass = $generalobj->encrypt_bycrypt($npass);

    $phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
    $phonecode = isset($_REQUEST['vPhoneCode']) ? $_REQUEST['vPhoneCode'] : '';

    $sql = "SELECT * from " . $tbl . $where;
    $edit_data = $obj->MySQLSelect($sql);




    if ($_REQUEST['phone'] != $edit_data[0]['vPhone']) {
        $query1 = "UPDATE `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
        $obj->sql_query($query1);
    }

    if ($_REQUEST['vPhoneCode'] != $edit_data[0]['vCountry']) {
        $query1 = "UPDATE `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
        $obj->sql_query($query1);
    }

    $pwdstr = '';
    if (!empty($_REQUEST['cpass']) && !empty($_REQUEST['npass']) && !empty($_REQUEST['ncpass'])) {
        if ($_REQUEST['cpass'] != '') {
            $hash = $edit_data[0]['vPassword'];
            $checkValid = $generalobj->check_password($_REQUEST['cpass'], $hash);

            if ($checkValid == 0) {
                echo '0';
                exit;
            }
            $pwdstr = "`vPassword` = '" . $npass . "',";
        }
    }

    $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $phonecode . "'";
    $CountryData = $obj->MySQLSelect($csql);
    $eZeroAllowed = $CountryData[0]['eZeroAllowed'];

    if ($eZeroAllowed == 'Yes') {
        $phone = $phone;
    } else {
        $first = substr($phone, 0, 1);

        if ($first == "0") {
            $phone = substr($phone, 1);
        }
    }

    $q = "UPDATE ";
    if (strtolower($_REQUEST['email']) != strtolower($edit_data[0]['vEmail'])) {
        $query = $q . " `" . $tbl . "` SET `eEmailVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }

    $query = "UPDATE  `" . $tbl . "` SET
		    `vEmail` = '" . $email . "',
			`vName` = '" . $fname . "',
			`vLastName` = '" . $lname . "',
			`vCountry` = '" . $location . "',
			`vLang` = '" . $lang . "',
			`vCurrencyPassenger`='" . $vCurrencyPassenger . "',
			$pwdstr
			`vPhone` = '" . $phone . "',
			`vPhoneCode` = '" . $phonecode . "'
		" . $where;

    $obj->sql_query($query);

    $_SESSION["sess_lang"] = $lang;
    $sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
    $lang = $obj->MySQLSelect($sql);
    $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
    echo '1';
    exit;
}
if ($action == 'login') {
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'user@demo.com')) {
        echo $var_msg = '2';
        return $var_msg;
        exit;
    }
    $email = isset($_REQUEST['email']) ? strtolower($_REQUEST['email']) : '';
    $fname = isset($_REQUEST['fname']) ? $_REQUEST['fname'] : '';
    $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
    $location = isset($_POST['country']) ? $_POST['country'] : '';
    $lang = isset($_POST['lang1']) ? $_POST['lang1'] : '';
    $vCurrencyPassenger = isset($_POST['vCurrencyPassenger']) ? $_POST['vCurrencyPassenger'] : '';
    $_SESSION["sess_vCurrency"] = $vCurrencyPassenger;

    $sql = "select vPhoneCode from country where vCountryCode = '" . $location . "'";
    $db_code = $obj->MySQLSelect($sql);

    $sql = "select * from " . $tbl . $where;
    $edit_data = $obj->MySQLSelect($sql);
    $q = "UPDATE ";
    if (strtolower($_REQUEST['email']) != strtolower($edit_data[0]['vEmail'])) {
        $query = $q . " `" . $tbl . "` SET `eEmailVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }

    if ($db_code[0]['vPhoneCode'] != $edit_data[0]['vPhoneCode']) {
        $query = $q . " `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }

    $query = "UPDATE  `" . $tbl . "` SET
		    `vEmail` = '" . $email . "',
			`vName` = '" . $fname . "',
			`vLastName` = '" . $lname . "',
			`vCountry` = '" . $location . "',
			`vLang` = '" . $lang . "',
			`vCurrencyPassenger`='" . $vCurrencyPassenger . "',
			`vPhoneCode` = '" . $db_code[0]['vPhoneCode'] . "'
		" . $where;
    $obj->sql_query($query);

    $_SESSION["sess_lang"] = $lang;
    $sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
    $lang = $obj->MySQLSelect($sql);
    $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
    echo '1';
    exit;
}

if ($action == 'pass') {
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'user@demo.com')) {
        echo $var_msg = '2';
        return $var_msg;
        exit;
    }

    $npass = isset($_REQUEST['npass']) ? $_REQUEST['npass'] : '';
    $npass = $generalobj->encrypt_bycrypt($npass);

    $query = "UPDATE `" . $tbl . "` SET
			`vPassword` = '" . $npass . "'" . $where;
    $obj->sql_query($query);

    echo '1';
    exit;
}
/* code for email update quickly */
if ($action == 'email') {
    $email = isset($_REQUEST['email']) ? strtolower($_REQUEST['email']) : '';

    $query = "UPDATE `" . $tbl . "` SET `vEmail` = '" . $email . "'" . $where;
    $obj->sql_query($query);

    //header("Location:profile_rider.php?success=1");
    echo '1';
    exit;
}
/* code for email update quickly */
if ($action == 'phone' || $action == 'phonecode') {
    if (SITE_TYPE == 'Demo' && ($_SESSION['sess_vEmail'] == 'user@demo.com')) {
        echo $var_msg = '2';
        return $var_msg;
        exit;
    }
    $phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
    $phonecode = isset($_REQUEST['phonecode']) ? $_REQUEST['phonecode'] : '';
    $q = "SELECT vPhoneCode FROM  `country` WHERE  vCountryCode = '" . $phonecode . "'";
    $vPhoneCode = $obj->MySQLSelect($q);
    $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $vPhoneCode[0]['vPhoneCode'] . "'";
    $CountryData = $obj->MySQLSelect($csql);
    $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    if ($eZeroAllowed == 'Yes') {
        $phone = $phone;
    } else {
        $first = substr($phone, 0, 1);
        if ($first == "0") {
            $phone = substr($phone, 1);
        }
    }
    //echo $phone."===".$phonecode."===".$iUserId;die;
    $eSystem = "";
    $checEmailExist = $generalobj->checkMemberDataInfo($phone, "", 'RIDER', $phonecode, $iUserId, $eSystem); //Added By HJ On 12-09-2019
    //print_r($checEmailExist);die;
    if ($checEmailExist['status'] == 0) {
        $var_msg = $langage_lbl['LBL_MOBILE_EXIST'];
        $action = 0;
    } else if ($checEmailExist['status'] == 2) {
        $var_msg = $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        $action = 2;
    } else {
        $sql = "SELECT * from " . $tbl . $where;
        $edit_data = $obj->MySQLSelect($sql);
        if ($_REQUEST['phone'] != $edit_data[0]['vPhone']) {
            $query1 = "UPDATE `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
            $obj->sql_query($query1);
        }
        if ($_REQUEST['phonecode'] != $edit_data[0]['vCountry']) {
            $query1 = "UPDATE `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
            $obj->sql_query($query1);
        }
        $query = "UPDATE `" . $tbl . "` SET `vPhone` = '" . $phone . "',`vCountry` = '" . $phonecode . "',`vPhoneCode` = '" . $vPhoneCode[0]['vPhoneCode'] . "'" . $where;
        $obj->sql_query($query);
        $action = 1;
        //echo '1';
    }
    echo $action;
    exit;
}
?>
