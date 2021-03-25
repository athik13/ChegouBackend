<?php

include_once('common.php');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$user_type = isset($_POST['type_usr']) ? $_POST['type_usr'] : '';
$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
//echo "<pre>";print_r($_POST);die;
$eSystem = $countryCode = $id = "";
$npass = $generalobj->encrypt($pass);
$remember = isset($_POST['remember-me']) ? $_POST['remember-me'] : '';


if ($action == 'driver') {
    $tbl_d = $tbl = '';
    $db_driver = $db_comp = array();
    if ($user_type == 'Driver') {
        $userType = "driver";
        $sql = "SELECT iDriverId,vCompany, iCompanyId, vName, vLastName, vEmail, vPhone,eStatus, vCurrencyDriver,vPassword,vLang FROM register_driver WHERE (vEmail = '" . $email . "' OR vPhone = '" . $email . "')";
        $db_driver = $obj->MySQLSelect($sql);
    } else {
        $userType = "company";
        $sql = "SELECT iCompanyId,vCompany, vName, vLang, vLastName, vEmail,vPhone, eStatus,vPassword,eSystem from company WHERE (vEmail = '" . $email . "' OR vPhone = '" . $email . "')";
        $db_comp = $obj->MySQLSelect($sql);
    }
    if (count($db_driver) > 0) {
        //$hash = $db_driver[0]['vPassword'];
        //$checkValid = $generalobj->check_password($pass, $hash);
        //$checkValid = $generalobj->validateMember($email, $pass,$userType);
        $checkValid = $generalobj->checkMemberDataInfo($email, $pass, $userType, $countryCode, $id, $eSystem);
        if ($checkValid['status'] == 1) {
            
            $db_driver = array();
            $db_driver[0] = $checkValid['USER_DATA'];
            //if ($checkValid == 1) {
            if ($db_driver[0]['eStatus'] != 'Deleted') {
                //$vLang = $db_driver[0]['vLang'];
                //if(empty($_SESSION['eDirectionCode'])) {
                //$sql = "select eDirectionCode from language_master where vCode='".$vLang."'";
                $sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
                $lang = $obj->MySQLSelect($sql);
                $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
                //}
                $_SESSION["sess_iUserId"] = $db_driver[0]['iDriverId'];
                $_SESSION["sess_iCompanyId"] = $db_driver[0]['iCompanyId'];
                $_SESSION["sess_vCompany"] = $db_driver[0]['vCompany'];
                $_SESSION["sess_vName"] = $db_driver[0]['vName'];
                $_SESSION["sess_vLastName"] = $db_driver[0]['vLastName'];
                $_SESSION["sess_vEmail"] = $db_driver[0]['vEmail'];
                $_SESSION["sess_vPhone"] = $db_driver[0]['vPhone'];
                $_SESSION["sess_vCurrency"] = $db_driver[0]['vCurrencyDriver'];
                $_SESSION["sess_user"] = "driver";
                if (SITE_TYPE == 'Demo') {
                    $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('" . $_SESSION["sess_iUserId"] . "', 'Driver', 'WebLogin','" . $_SERVER['REMOTE_ADDR'] . "')";
                    $obj->sql_query($login_sql);

                    $update_sql = "UPDATE register_driver set tRegistrationDate='" . date('Y-m-d H:i:s') . "' WHERE iDriverId='" . $_SESSION["sess_iUserId"] . "'";
                    $db_update = $obj->sql_query($update_sql);
                }
                $update_sql = "UPDATE register_driver set vLang='".$_SESSION["sess_lang"]."' WHERE iDriverId='" . $_SESSION["sess_iUserId"] . "'";
                $db_update = $obj->sql_query($update_sql);
                if ($remember == "Yes") {
                    setcookie("member_login_cookie", $email, time() + 2592000);
                    setcookie("member_password_cookie", $pass, time() + 2592000);
                } else {
                    setcookie("member_login_cookie", "", time());
                    setcookie("member_password_cookie", "", time());
                }

                //save login log added by Rs start
                $generalobj->createUserLog('Driver', 'Yes', $db_driver[0]['iDriverId'], 'Web');

                $json_data = array('login_status' => 2);
                echo json_encode($json_data);
                exit;
            } else {
                $json_data = array('login_status' => 1);
                echo json_encode($json_data);
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
    } else {
        if (count($db_comp) > 0) {
            $_SESSION['postDetail']['user_type'] = "company"; //addd by SP for cubex design, its moved from the login file to the here on 16-8-2019
            //$hash = $db_comp[0]['vPassword'];
            //$checkValid = $generalobj->check_password($pass, $hash);
            $countryCode = $id = "";
            $eSystem = $db_comp[0]['eSystem'];
            $checkValid = $generalobj->checkMemberDataInfo($email, $pass, $userType, $countryCode, $id, $eSystem);
            //print_R($checkValid);die;
            if ($checkValid['status'] == 1) {
                $db_comp = array();
                $db_comp[0] = $checkValid['USER_DATA'];
                //if ($checkValid == 1) {
                if ($db_comp[0]['eStatus'] != 'Deleted') {
                    //$vLang = $db_comp[0]['vLang'];
                    //if(empty($_SESSION['eDirectionCode'])) {
                    //$sql = "select eDirectionCode from language_master where vCode='".$vLang."'";
                    $sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
                    $lang = $obj->MySQLSelect($sql);
                    $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
                    //}
                    $_SESSION["sess_iUserId"] = $db_comp[0]['iCompanyId'];
                    $_SESSION["sess_vCompany"] = $db_comp[0]['vCompany'];
                    $_SESSION["sess_iCompanyId"] = $db_comp[0]['iCompanyId'];
                    $_SESSION["sess_vName"] = $db_comp[0]['vName'];
                    $_SESSION["sess_vLastName"] = $db_comp[0]['vLastName'];
                    $_SESSION["sess_vEmail"] = $db_comp[0]['vEmail'];
                    $_SESSION["sess_vPhone"] = $db_driver[0]['vPhone'];
                    $_SESSION["sess_eSystem"] = $eSystem;
                    $_SESSION["sess_user"] = "company";
                    $update_sql = "UPDATE company set vLang='".$_SESSION["sess_lang"]."' WHERE iCompanyId='" . $_SESSION["sess_iUserId"] . "'";
                    $db_update = $obj->sql_query($update_sql);
                    if ($remember == "Yes") {
                        setcookie("member_login_cookie", $email, time() + 2592000);
                        setcookie("member_password_cookie", $pass, time() + 2592000);
                    } else {
                        setcookie("member_login_cookie", "", time());
                        setcookie("member_password_cookie", "", time());
                    }
                    //User login log added by Rs start
                    if ($eSystem == 'DeliverAll')
                        $generalobj->createUserLog('Store', 'Yes', $db_comp[0]['iCompanyId'], 'Web');
                    else
                        $generalobj->createUserLog('Company', 'Yes', $db_comp[0]['iCompanyId'], 'Web');

                    $json_data = array('login_status' => 2, 'eSystem' => $eSystem);
                    echo json_encode($json_data);
                    exit;
                } else {
                    $json_data = array('login_status' => 1);
                    echo json_encode($json_data);
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
        } else {
            $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
    }
}
if ($action == 'rider') {
    $tbl = 'register_user';
    $userType = $action;
    //$fields = 'iUserId, vName, vEmail, eStatus, vCurrencyPassenger, vPhone,vPassword,vLang,vCountry';
    //$sql = "SELECT $fields FROM $tbl WHERE (vEmail = '" . $email . "' OR vPhone = '" . $email . "')";
    //$db_logins = $obj->MySQLSelect($sql);
    //if (count($db_logins) > 0) {
    //$hash = $db_logins[0]['vPassword'];
    /* 04-09-2019 Process to change mobile number as per country - one country has one number - same number can available as per different countries start */
    //$checkValid = $generalobj->validateMember($email, $pass,$userType);

    $checkValid = $generalobj->checkMemberDataInfo($email, $pass, $userType, $countryCode, $id, $eSystem);
    //echo "<pre>";
    //print_r($checkValid);exit;
    /* 04-09-2019 end */
    if ($checkValid['status'] == 1) {
        $db_login = array();
        $db_login[0] = $checkValid['USER_DATA'];
        //echo "<pre>";
        //print_r($db_login);exit;
        if ($db_login[0]['eStatus'] != "Deleted" && $db_login[0]['eStatus'] != "Inactive") {
            //$vLang = $db_login[0]['vLang'];
            //if(empty($_SESSION['eDirectionCode'])) {
                //$sql = "select eDirectionCode from language_master where vCode='$vLang'";
                $sql = "select eDirectionCode from language_master where vCode='".$_SESSION["sess_lang"]."'";
                $lang = $obj->MySQLSelect($sql);
                $_SESSION['eDirectionCode'] = $lang[0]['eDirectionCode'];
            //}
            $_SESSION['sess_iUserId'] = $db_login[0]['iUserId'];
            $_SESSION["sess_vName"] = $db_login[0]['vName'];
            //$_SESSION["sess_lang"]=$db_login[0]['vLang'];
            $_SESSION["sess_vEmail"] = $db_login[0]['vEmail'];
            $_SESSION["sess_vPhone"] = $db_driver[0]['vPhone'];
            $_SESSION["sess_user"] = "rider";
            $_SESSION["sess_vCurrency"] = $db_login[0]['vCurrencyPassenger'];
            if (SITE_TYPE == 'Demo') {
                $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('" . $_SESSION["sess_iUserId"] . "', 'Passenger', 'WebLogin','" . $_SERVER['REMOTE_ADDR'] . "')";
                $obj->sql_query($login_sql);
                $update_sql = "UPDATE register_user set tRegistrationDate='" . date('Y-m-d H:i:s') . "' WHERE iUserId='" . $_SESSION["sess_iUserId"] . "'";
                $db_update = $obj->sql_query($update_sql);
            }
            $update_sql = "UPDATE register_user set vLang='".$_SESSION["sess_lang"]."' WHERE iUserId='" . $_SESSION["sess_iUserId"] . "'";
            $db_update = $obj->sql_query($update_sql);
            if ($remember == "Yes") {
                setcookie("member_login_cookie", $vEmail, time() + 2592000);
                setcookie("member_password_cookie", $vPassword, time() + 2592000);
            } else {
                setcookie("member_login_cookie", "", time());
                setcookie("member_password_cookie", "", time());
            }

            $generalobj->createUserLog('Passenger', 'Yes', $db_login[0]['iUserId'], 'Web');

            $json_data = array('login_status' => 2);
            echo json_encode($json_data);
            exit;
        } else {
            if ($db_login[0]['eStatus'] == "Deleted") {
                $json_data = array('login_status' => 1);
                echo json_encode($json_data);
                exit;
            } else {
                $json_data = array('login_status' => 4);
                echo json_encode($json_data);
                exit;
            }
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
}
//added by SP for cubex changes 
if($action=='hotel') {
    //$email = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
    //$pass = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
    $group_id = 4; //isset($_POST['group_id']) ? $_POST['group_id'] : '';
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
             $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        } else { 
             $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
    }
    if (count($db_login) > 0) {
        $hash = $db_login[0]['vPassword'];
        $checkValid = $generalobj->check_password($pass, $hash);
    
        if ($checkValid == 0) {
            $json_data = array('login_status' => 3);
            echo json_encode($json_data);
            exit;
        }
        if ($db_login[0]['eStatus'] != 'Active') {
             $json_data = array('login_status' => 4);
            echo json_encode($json_data);
            exit;
        } else {
            
            $_SESSION['sess_iAdminUserId'] = $db_login[0]['iAdminId'];
            $_SESSION['sess_iGroupId'] = $db_login[0]['iGroupId'];
            $_SESSION["sess_vAdminFirstName"] = $db_login[0]['vFirstName'];
            $_SESSION["sess_vAdminLastName"] = $db_login[0]['vLastName'];
            $_SESSION["sess_vAdminEmail"] = $db_login[0]['vEmail'];
            if ($db_login[0]['iGroupId'] == '4') {
                $_SESSION["SessionUserType"] = 'hotel';
            }
            //save login log added by Rs start
            if ($db_login[0]['iGroupId'] == '4') 
                $checkValid = $generalobj->createUserLog('Hotel','Yes',$db_login[0]['iAdminId'],'Web');
            else
                $checkValid = $generalobj->createUserLog('Admin','Yes',$db_login[0]['iAdminId'],'Web');
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
	  $_SESSION["SessionRedirectUserPanel"] = 'Yes';
            $json_data = array('login_status' => 2);
            echo json_encode($json_data);
            exit;
        }
    }
}
exit;
?>