<?php

include_once('../common.php');
$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
$hdn_HTTP_REFERER = isset($_POST['hdn_HTTP_REFERER']) ? $_POST['hdn_HTTP_REFERER'] : '';
$_SESSION['hdn_HTTP_REFERER'] = $hdn_HTTP_REFERER;
$remember = isset($_POST['remember-me']) ? $_POST['remember-me'] : '';
$tbl = 'administrators';
$fields = 'iAdminId, vFirstName,vLastName, vEmail, eStatus, iGroupId, vPassword';
//echo "<pre>";
//print_r($group_id);die;
//Added By HJ On 31-01-2019 For Login All Admin From All Admin Tab As Per Discuss With CD,KL Sir and Also BM QA Mam Start
if (isset($group_id) && !empty($group_id) && $group_id != '1') {
    $sql = "SELECT $fields FROM $tbl WHERE vEmail = '" . $email . "' AND iGroupId = '" . $group_id . "'";
    $db_login = $obj->MySQLSelect($sql);
    $sql = "SELECT vEmail from $tbl WHERE vEmail = '" . $email . "' AND iGroupId = '" . $group_id . "'";
    $db_mail = $obj->MySQLSelect($sql);
} else {
    $sql = "SELECT $fields FROM $tbl WHERE vEmail = '" . $email . "'";
    $db_login = $obj->MySQLSelect($sql);
    $sql = "SELECT vEmail from $tbl WHERE vEmail = '" . $email . "'";
    $db_mail = $obj->MySQLSelect($sql);
}
//Added By HJ On 31-01-2019 For Login All Admin From All Admin Tab As Per Discuss With CD,KL Sir and Also BM QA Mam End
//Comment By HJ On 31-01-2019 As Per Discuss With CD,KL Sir and Also BM QA Mam Start - FOr Login Particular Admin Enabel This
/* $sql = "SELECT $fields FROM $tbl WHERE vEmail = '" . $email . "' AND iGroupId = '" . $group_id . "'";
  $db_login = $obj->MySQLSelect($sql);

  $sql = "SELECT vEmail from $tbl WHERE vEmail = '" . $email . "' AND iGroupId = '" . $group_id . "'";
  $db_mail = $obj->MySQLSelect($sql); */
//Comment By HJ On 31-01-2019 As Per Discuss With CD,KL Sir and Also BM QA Mam End - FOr Login Particular Admin Enabel This
if (count($db_login) == 0) {
    if (count($db_mail) > 0) {
        echo "3";
        exit;
    } else {
        echo "4";
        exit;
    }
}
if (count($db_login) > 0) {
    $hash = $db_login[0]['vPassword'];
    $checkValid = $generalobj->check_password($pass, $hash);

    if ($checkValid == 0) {
        echo "4";
        exit;
    }
    if ($db_login[0]['eStatus'] != 'Active') {
        echo "1";
        exit;
    } else {

        $_SESSION['sess_iAdminUserId'] = $db_login[0]['iAdminId'];
        $_SESSION['sess_iGroupId'] = $db_login[0]['iGroupId'];
        $_SESSION["sess_vAdminFirstName"] = $db_login[0]['vFirstName'];
        $_SESSION["sess_vAdminLastName"] = $db_login[0]['vLastName'];
        $_SESSION["sess_vAdminEmail"] = $db_login[0]['vEmail'];
        if ($db_login[0]['iGroupId'] == '4') {
            $_SESSION["SessionUserType"] = 'hotel';
            $_SESSION["sess_user"] = 'hotel';
        } /*else if ($db_login[0]['iGroupId'] == '1') {
            $_SESSION["SessionUserType"] = 'main';
            $_SESSION["sess_user"] = 'main';
        } else if ($db_login[0]['iGroupId'] == '2') {
            $_SESSION["SessionUserType"] = 'dispatcher';
            $_SESSION["sess_user"] = 'dispatcher';
        } else if ($db_login[0]['iGroupId'] == '3') {
            $_SESSION["SessionUserType"] = 'billing';
            $_SESSION["sess_user"] = 'billing';
        }*/

        //save login log added by Rs start
        if ($db_login[0]['iGroupId'] == '4')
            $checkValid = $generalobj->createUserLog('Hotel', 'Yes', $db_login[0]['iAdminId'], 'Web');
        else
            $checkValid = $generalobj->createUserLog('Admin', 'Yes', $db_login[0]['iAdminId'], 'Web');
        //save login log added by Rs end
        if (SITE_TYPE == 'Demo') {
            $q = "UPDATE company SET `tRegistrationDate` = '" . date("Y-m-d H:i:s") . "' WHERE `iCompanyId` = '1'";
            $obj->sql_query($q);
        }
        if ($remember == "Yes") {
            setcookie("member_login_cookie", $email, time() + 2592000);
            setcookie("member_password_cookie", $pass, time() + 2592000);
        } else {
            setcookie("member_login_cookie", "", time());
            setcookie("member_password_cookie", "", time());
        }
        echo 2;
        exit;
    }
}
?>