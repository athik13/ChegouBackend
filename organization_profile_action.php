<?php

include_once('common.php');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$iOrganizationId = $_SESSION['sess_iOrganizationId'];
$tbl = 'organization';
$where = " WHERE `iOrganizationId` = '" . $iOrganizationId . "'";
$str = '';
if ($action == 'login') {
    $phone = isset($_REQUEST['vPhone']) ? $_REQUEST['vPhone'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $tProfileDescription = isset($_POST['tProfileDescription']) ? $_POST['tProfileDescription'] : '';
    $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
    $vCurrency = isset($_POST['vCurrency']) ? $_POST['vCurrency'] : '';
    $vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
    $vWorkLocationLatitude = isset($_POST['vWorkLocationLatitude']) ? $_POST['vWorkLocationLatitude'] : '';
    $vWorkLocationLongitude = isset($_POST['vWorkLocationLongitude']) ? $_POST['vWorkLocationLongitude'] : '';
    $vWorkLocation = isset($_POST['vWorkLocation']) ? $_POST['vWorkLocation'] : '';
    $vWorkLocationRadius = isset($_POST['vWorkLocationRadius']) ? $_POST['vWorkLocationRadius'] : '';
    $ePaymentBy = isset($_REQUEST['ePaymentBy']) ? $_REQUEST['ePaymentBy'] : '';
    $_SESSION["sess_vCurrency"] = $vCurrencyDriver;

    $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $vCode . "'";
    $CountryData = $obj->MySQLSelect($csql);
    $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    //echo $phone."===".$vCountry."===".$iOrganizationId;die;
    if ($eZeroAllowed == 'Yes') {
        $phone = $phone;
    } else {
        $first = substr($phone, 0, 1);
        if ($first == "0") {
            $phone = substr($phone, 1);
        }
    }
    $eSystem = "";
    $checEmailExist = $generalobj->checkMemberDataInfo($phone, "", 'ORGANIZATION', $vCountry, $iOrganizationId,$eSystem); //Added By HJ On 12-09-2019
    if ($checEmailExist['status'] == 0) {
        $var_msg = $langage_lbl['LBL_MOBILE_EXIST'];
        $action = 0;
    } else if ($checEmailExist['status'] == 2) {
        $var_msg = $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        $action = 2;
    } else {
        $q = "UPDATE ";
        $sql = "select * from " . $tbl . $where;
        $edit_data = $obj->sql_query($sql);
        if ($_SESSION['sess_user'] == 'driver' && $_REQUEST['email'] != $edit_data[0]['vEmail']) {
            $query = $q . " `" . $tbl . "` SET `eEmailVerified` = 'No' " . $where;
            $obj->sql_query($query);
        }
        if ($_SESSION['sess_user'] == 'driver' && $_REQUEST['phone'] != $edit_data[0]['vPhone']) {
            $query = $q . " `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
            $obj->sql_query($query);
        }

        if ($_SESSION['sess_user'] == 'driver' && $_REQUEST['vCode'] != $edit_data[0]['vCode']) {
            $query = $q . " `" . $tbl_name . "` SET `ePhoneVerified` = 'No' " . $where;
            $obj->sql_query($query);
        }

        if ($_SESSION['sess_user'] == 'driver' && $APP_TYPE == 'UberX') {
            $query = $q . " `" . $tbl . "` SET `tProfileDescription` = 'No' " . $where;
            $obj->sql_query($query);
        }
        $query = $q . " `" . $tbl . "` SET
			`vEmail` = '" . $email . "',
			`vLoginId` = '" . $username . "',
			`vCountry` = '" . $vCountry . "',
			`vCode` = '" . $vCode . "',
			`ePaymentBy` = '" . $ePaymentBy . "',
			`vCurrency` = '" . $vCurrency . "',
			`vPhone` = '" . $phone . "' $str" . $where;
        $obj->sql_query($query);
        $action = 1;
        $var_msg = $langage_lbl['LBL_PORFILE_UPDATE_MSG'];
    }
    echo $action;
    die;
    $returnArr['action'] = $action;
    $returnArr['message'] = $var_msg;
    return $var_msg;
    exit;
}
if ($action == 'address') {
    $address1 = isset($_REQUEST['address1']) ? $_REQUEST['address1'] : '';
    //$address2 = isset($_POST['address2'])?$_POST['address2']:'';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
    $vState = isset($_POST['vState']) ? $_POST['vState'] : '';
    $zipcode = isset($_POST['vZipcode']) ? $_POST['vZipcode'] : '';

    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
			`vCaddress` = '" . $address1 . "',
			`vCity` = '" . $vCity . "',
			`vCountry` = '" . $vCountry . "',
			`vState` = '" . $vState . "',
			`vZip` = '" . $zipcode . "' $str" . $where;

    $obj->sql_query($query);

    echo $var_msg = $langage_lbl['LBL_ADDRESS_UPDATE_MSG'];
    return $var_msg;
    exit;
}
if ($action == 'pass') {

    $npass = isset($_REQUEST['npass']) ? $_REQUEST['npass'] : '';
    $npass = $generalobj->encrypt_bycrypt($npass);

    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
			`vPassword` = '" . $npass . "' $str" . $where;
    $obj->sql_query($query);

    echo $var_msg = $langage_lbl['LBL_PASS_UPDATE_MSG'];
    return $var_msg;
    exit;
}

if ($action == 'lang1') {

    $lang = isset($_REQUEST['lang1']) ? $_REQUEST['lang1'] : '';

    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
			`vLang` = '" . $lang . "' $str" . $where;
    $obj->sql_query($query);

    $_SESSION["sess_lang"] = $lang;
    $sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
    $lang = $obj->MySQLSelect($sql);
    $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
    echo $var_msg = $langage_lbl['LBL_LANG_UPDATE_MSG'];
    return $var_msg;
    exit;
}

if ($action == 'access') {
    $access = isset($_REQUEST['access']) ? $_REQUEST['access'] : '';

    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
			`eAccess` = '" . $access . "' $str" . $where;
    $obj->sql_query($query);

    echo $var_msg = $langage_lbl['LBL_ACCESSIBILITY_UPDATE_MSG'];
    return $var_msg;
    exit;
}

if ($action == 'allInOne') {
$phone = isset($_REQUEST['vPhone']) ? $_REQUEST['vPhone'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $tProfileDescription = isset($_POST['tProfileDescription']) ? $_POST['tProfileDescription'] : '';
    $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
    $vCurrency = isset($_POST['vCurrency']) ? $_POST['vCurrency'] : '';
    $vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
    $vWorkLocationLatitude = isset($_POST['vWorkLocationLatitude']) ? $_POST['vWorkLocationLatitude'] : '';
    $vWorkLocationLongitude = isset($_POST['vWorkLocationLongitude']) ? $_POST['vWorkLocationLongitude'] : '';
    $vWorkLocation = isset($_POST['vWorkLocation']) ? $_POST['vWorkLocation'] : '';
    $vWorkLocationRadius = isset($_POST['vWorkLocationRadius']) ? $_POST['vWorkLocationRadius'] : '';
    $ePaymentBy = isset($_REQUEST['ePaymentBy']) ? $_REQUEST['ePaymentBy'] : '';
    $_SESSION["sess_vCurrency"] = $vCurrencyDriver;

    $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $vCode . "'";
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

    $sql = "select * from " . $tbl . $where;
    $edit_data = $obj->sql_query($sql);
    
    	if(!empty($_REQUEST['cpass']) && !empty($_REQUEST['npass']) && !empty($_REQUEST['ncpass'])){
		if($_REQUEST['cpass']!='') {
			$hash = $edit_data[0]['vPassword'];
			$checkValid = $generalobj->check_password($_REQUEST['cpass'], $hash);
			
			if($checkValid==0) { 
				echo "0";
				exit;
			} 
		}			
	}

    if ($_SESSION['sess_user'] == 'driver' && $_REQUEST['email'] != $edit_data[0]['vEmail']) {
        $query = $q . " `" . $tbl . "` SET `eEmailVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }
    if ($_SESSION['sess_user'] == 'driver' && $_REQUEST['phone'] != $edit_data[0]['vPhone']) {
        $query = $q . " `" . $tbl . "` SET `ePhoneVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }

    if ($_SESSION['sess_user'] == 'driver' && $_REQUEST['vCode'] != $edit_data[0]['vCode']) {
        $query = $q . " `" . $tbl_name . "` SET `ePhoneVerified` = 'No' " . $where;
        $obj->sql_query($query);
    }

    if ($_SESSION['sess_user'] == 'driver' && $APP_TYPE == 'UberX') {
        $query = $q . " `" . $tbl . "` SET `tProfileDescription` = 'No' " . $where;
        $obj->sql_query($query);
    }
    $query = $q . " `" . $tbl . "` SET
			`vEmail` = '" . $email . "',
			`vLoginId` = '" . $username . "',
			`vCountry` = '" . $vCountry . "',
			`vCode` = '" . $vCode . "',
			`ePaymentBy` = '" . $ePaymentBy . "',
			`vCurrency` = '" . $vCurrency . "',
			`vPhone` = '" . $phone . "' $str" . $where;

    $obj->sql_query($query);

	/* Address */	
	
    $address1 = isset($_REQUEST['address1']) ? $_REQUEST['address1'] : '';
    //$address2 = isset($_POST['address2'])?$_POST['address2']:'';
    $vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
    $vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
    $vState = isset($_POST['vState']) ? $_POST['vState'] : '';
    $zipcode = isset($_POST['vZipcode']) ? $_POST['vZipcode'] : '';

    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
			`vCaddress` = '" . $address1 . "',
			`vCity` = '" . $vCity . "',
			`vCountry` = '" . $vCountry . "',
			`vState` = '" . $vState . "',
			`vZip` = '" . $zipcode . "' $str" . $where;

    $obj->sql_query($query);	
	
	/* Password */
	if(!empty($_REQUEST['cpass']) && !empty($_REQUEST['npass']) && !empty($_REQUEST['ncpass'])){
		
	    $npass = isset($_REQUEST['npass']) ? $_REQUEST['npass'] : '';
		$npass = $generalobj->encrypt_bycrypt($npass);

		$q = "UPDATE ";
		$query = $q . " `" . $tbl . "` SET
				`vPassword` = '" . $npass . "' $str" . $where;
		$obj->sql_query($query);
        
        
	}


	
	/* Language */

	$lang = isset($_REQUEST['lang1']) ? $_REQUEST['lang1'] : '';

    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
			`vLang` = '" . $lang . "' $str" . $where;
    $obj->sql_query($query);

    $_SESSION["sess_lang"] = $lang;
	$sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
    $lang = $obj->MySQLSelect($sql);
    $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
	/* Access */

	$access = isset($_REQUEST['access']) ? $_REQUEST['access'] : '';

    $q = "UPDATE ";
    $query = $q . " `" . $tbl . "` SET
			`eAccess` = '" . $access . "' $str" . $where;
    $obj->sql_query($query);
    
    echo "1";
    exit;
	
}
?>
