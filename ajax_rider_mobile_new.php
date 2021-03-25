<?php

include_once('common.php');
$ssql =$eSystem= "";
$riderId = 0;
if (isset($_REQUEST['iUserId']) && $_REQUEST['iUserId'] != "") {
    $ssql = " AND iUserId!='" . $_REQUEST['iUserId'] . "'";
    $riderId = $_REQUEST['iUserId'];
}
$reqType = "phone";
if (isset($_REQUEST['type']) && trim($_REQUEST['type']) != "") {
    $reqType = trim($_REQUEST['type']);
}
if (isset($_REQUEST['vPhone']) && $reqType == "phone") {
    $vPhone = $_REQUEST['vPhone'];
    $vPhoneCode = $_REQUEST['vPhoneCode'];
    //echo $vPhone."==".$vPhoneCode."===".$riderId;die;
    $checEmailExist = $generalobj->checkMemberDataInfo($vPhone, "", 'RIDER', $vPhoneCode, $riderId,$eSystem); //Added By HJ On 12-09-2019
/*    print_r($checEmailExist);die;*/
    $sql = "SELECT vPhone,eStatus FROM register_user WHERE vPhone = '" . $vPhone . "'" . $ssql;
    $db_user = $obj->MySQLSelect($sql);
    if ($checEmailExist['status'] == 0) {
        echo 'false';
        $messge = "LBL_MOBILE_EXIST";
    } else if ($checEmailExist['status'] == 2) {
        echo 'false';
        $messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
    } else {
        echo 'true';
    }
    /*if (count($db_user) > 0) {
        if ((ucfirst($db_user[0]['eStatus']) == 'Deleted') || (ucfirst($db_user[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
    } else {
        echo 'true';
    }*/
} else if ($reqType == "email") {
    $emailExists = 0;
    //echo "<pre>";print_r($_REQUEST);die;
    $custEmail = $_REQUEST['custEmail'];
    $wherePhone = "";
    $vPhone = $_REQUEST['phone'];
    if ($vPhone != "") {
        $wherePhone = " AND vPhone !='" . $vPhone . "'";
    }
    if ($custEmail != "") {
        $checkUserEmail = $obj->MySQLSelect("SELECT vPhone,eStatus FROM register_user WHERE vEmail = '" . $custEmail . "' AND eStatus = 'Active'" . $ssql . $wherePhone);
        if (count($checkUserEmail) > 0) {
            $emailExists = 1;
        }
    }
    echo $emailExists;
}
?>