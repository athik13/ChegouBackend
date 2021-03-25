<?php

include_once('common.php');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';

$user_type = isset($_POST['type_usr']) ? $_POST['type_usr'] : '';
$countryCode = $id = $eSystem = "";
$npass = $generalobj->encrypt($pass);
$remember = isset($_POST['remember-me']) ? $_POST['remember-me'] : '';
/* Use For Organization Module */
/* if($action == 'organization')
  { */

$db_comp_org = array();
//$sql = "SELECT iOrganizationId,vCompany, vLang, vEmail,vPhone, eStatus,vPassword from organization WHERE (vEmail = '" . $email . "' OR vPhone = '" . $email . "')";
//$db_comp_org = $obj->MySQLSelect($sql);
$userType = 'organization';
$checkValid = $generalobj->checkMemberDataInfo($email, $pass, $userType, $countryCode,$id,$eSystem);
//if (count($db_comp_org) > 0) {
//$hash = $db_comp_org[0]['vPassword'];
//$checkValid = $generalobj->check_password($pass, $hash);

if ($checkValid['status'] == 1) {
    $db_comp_org = array();
    $db_comp_org[0] = $checkValid['USER_DATA'];

    if ($db_comp_org[0]['eStatus'] != 'Deleted') {
        //$vLang = $db_comp_org[0]['vLang'];
        //$sql = "select eDirectionCode from language_master where vCode='$vLang'";
        $sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
        $lang = $obj->MySQLSelect($sql);
        $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
        $_SESSION["sess_iUserId"] = $db_comp_org[0]['iOrganizationId'];
        $_SESSION["sess_vCompany"] = $db_comp_org[0]['vCompany'];
        $_SESSION["sess_iOrganizationId"] = $db_comp_org[0]['iOrganizationId'];
        //$_SESSION["sess_vName"]=$db_comp_org[0]['vName'];
        //$_SESSION["sess_lang"] = $db_comp_org[0]['vLang'];
        //$_SESSION["sess_vLastName"]=$db_comp_org[0]['vLastName'];
        $_SESSION["sess_vEmail"] = $db_comp_org[0]['vEmail'];
        $_SESSION["sess_vPhone"] = $db_driver[0]['vPhone'];
        //$_SESSION["sess_eSystem"]=$db_comp_org[0]['eSystem'];
        $_SESSION["sess_user"] = "organization";

        //User login log added by Rs start
        $generalobj->createUserLog('Organization', 'Yes', $db_comp_org[0]['iOrganizationId'], 'Web');

        $update_sql = "UPDATE organization set vLang='".$_SESSION["sess_lang"]."' WHERE iOrganizationId='" . $_SESSION["sess_iUserId"] . "'";
        $db_update = $obj->sql_query($update_sql);
        
        if ($remember == "Yes") {
            setcookie("member_login_cookie", $email, time() + 2592000);
            setcookie("member_password_cookie", $pass, time() + 2592000);
        } else {
            setcookie("member_login_cookie", "", time());
            setcookie("member_password_cookie", "", time());
        }

        $json_data = array('login_status' => 2);
        echo json_encode($json_data);
        //echo 2;
        exit;
    } else {
        $json_data = array('login_status' => 1);
        echo json_encode($json_data);
        //echo 1;
        exit;
    }
} elseif ($checkValid['status'] == 2) {
    $json_data = array('login_status' => 5);
    echo json_encode($json_data);
    exit;
} else {
    $json_data = array('login_status' => 3);
    echo json_encode($json_data);
    exit;
}
/* } else {
  $json_data = array('login_status' => 3);
  echo json_encode($json_data);
  exit;
  } */
//}

/* Use For Organization Module */

exit;
?>