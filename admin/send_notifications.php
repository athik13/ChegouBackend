<?php
include_once('../common.php');

include_once(TPATH_CLASS . '/class.general.php');
include_once(TPATH_CLASS . '/configuration.php');
include_once('../generalFunctions.php');
$APP_MODE = $generalobj->getConfigurations("configurations", "APP_MODE");
$APP_MODE_TEMP_WEB = '';
define("FOOD_MENU", "food_menu");

function send_notification_fun($registation_ids_new, $deviceTokens_arr_ios, $message, $userType, $deviceTokens_arr_ios_pro) {
    global $APP_MODE_TEMP_WEB;
    $message = stripslashes($message);
    $alertMsg = $message;
    $count = 1;
    if (!empty($registation_ids_new)) {
        $newArr = array();
        $newArr = array_chunk($registation_ids_new, 999);
        //echo "<pre>";print_r($newArr);die;
        foreach ($newArr as $newRegistration_ids) {
            //$messageNoti = $message . "--" . $count;
            //$count++;
            $messageNoti = $message;
            $Rmessage = array("message" => $messageNoti);
            $result = send_notification($newRegistration_ids, $Rmessage, 0);
        }
    }
    if (!empty($deviceTokens_arr_ios)) {
        $APP_MODE_TEMP_WEB = 'Development';
        if ($userType == "rider") {
            $result = sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0, 'admin');
        } else if ($userType == "company") {
            $result = sendApplePushNotification(2, $deviceTokens_arr_ios, $message, $alertMsg, 0, 'admin');
        } else {
            $result = sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0, 'admin');
        }
    }
    if (!empty($deviceTokens_arr_ios_pro)) {
        $APP_MODE_TEMP_WEB = 'Production';
        if ($userType == "rider") {
            $result = sendApplePushNotification(0, $deviceTokens_arr_ios_pro, $message, $alertMsg, 0, 'admin');
        } else if ($userType == "company") {
            $result = sendApplePushNotification(2, $deviceTokens_arr_ios_pro, $message, $alertMsg, 0, 'admin');
        } else {
            $result = sendApplePushNotification(1, $deviceTokens_arr_ios_pro, $message, $alertMsg, 0, 'admin');
        }
    }
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = 'Push Notification send successfully.';
    header("location:send_notifications.php");
    exit;
}

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('manage-send-push-notification')) {
    $userObj->redirect();
}
//added by SP for comment eStatus active bc for all driver notification send but in log table not inserted so remove condition so inserted on 3-10-2019 as per the discussion with the HJ
//$sql = "select concat(vName,' ',vLastName) as DriverName,iDriverId,eDeviceType,eDebugMode from register_driver where eStatus = 'Active' AND (vEmail != '' OR vPhone != '')  order by vName";
$sql = "select concat(vName,' ',vLastName) as DriverName,iDriverId,eDeviceType,eDebugMode from register_driver where (vEmail != '' OR vPhone != '')  order by vName";
$db_drvlist = $obj->MySQLSelect($sql);
$db_drv_list = array();

for ($i = 0; $i < count($db_drvlist); $i++) {
    $data = array();
    $data['DriverName'] = mb_convert_encoding($generalobjAdmin->clearName(ucfirst($db_drvlist[$i]['DriverName'])), 'utf-8', 'auto');
    $data['iDriverId'] = $db_drvlist[$i]['iDriverId'];
    $data['eDeviceType'] = $db_drvlist[$i]['eDeviceType'];
    $data['eDebugMode'] = $db_drvlist[$i]['eDebugMode'];
    array_push($db_drv_list, $data);
}
//$sql = "select concat(vName,' ',vLastName) as riderName,iUserId,eDeviceType from register_user where eStatus = 'Active' AND (vEmail != '' OR vName != '' OR vPhone != '') order by vName";
$sql = "select concat(vName,' ',vLastName) as riderName,iUserId,eDeviceType from register_user where (vEmail != '' OR vName != '' OR vPhone != '') order by vName";
$db_rdrlist = $obj->MySQLSelect($sql);
$db_rdr_list = array();
for ($ii = 0; $ii < count($db_rdrlist); $ii++) {
    $data = array();
    $data['riderName'] = mb_convert_encoding($generalobjAdmin->clearName(ucfirst($db_rdrlist[$ii]['riderName'])), 'utf-8', 'auto');
    $data['iUserId'] = $db_rdrlist[$ii]['iUserId'];
    $data['eDeviceType'] = $db_rdrlist[$ii]['eDeviceType'];
    array_push($db_rdr_list, $data);
}

//$sql_drv = "select concat(vName,' ',vLastName) as DriverName,iDriverId,eDeviceType from register_driver where eStatus = 'Active' AND `eLogout` = 'No' AND (vEmail != '' OR vPhone != '') order by vName";
$sql_drv = "select concat(vName,' ',vLastName) as DriverName,iDriverId,eDeviceType from register_driver where `eLogout` = 'No' AND (vEmail != '' OR vPhone != '') order by vName";
$db_login_drvlist = $obj->MySQLSelect($sql_drv);
$db_login_drv_list = array();
for ($iii = 0; $iii < count($db_login_drvlist); $iii++) {
    $data = array();
    $data['DriverName'] = mb_convert_encoding($generalobjAdmin->clearName(ucfirst($db_login_drvlist[$iii]['DriverName'])), 'utf-8', 'auto');
    $data['iDriverId'] = $db_login_drvlist[$iii]['iDriverId'];
    $data['eDeviceType'] = $db_login_drvlist[$iii]['eDeviceType'];
    array_push($db_login_drv_list, $data);
}

//$sql_rdr = "select concat(vName,' ',vLastName) as riderName,iUserId,eDeviceType from register_user where eStatus = 'Active' AND `eLogout` = 'No'  AND (vEmail != '' OR vPhone != '') order by vName";
$sql_rdr = "select concat(vName,' ',vLastName) as riderName,iUserId,eDeviceType from register_user where `eLogout` = 'No'  AND (vEmail != '' OR vPhone != '') order by vName";
$db_login_rdrlist = $obj->MySQLSelect($sql_rdr);
$db_login_rdr_list = array();
for ($iv = 0; $iv < count($db_login_rdrlist); $iv++) {
    $data = array();
    $data['riderName'] = mb_convert_encoding($generalobjAdmin->clearName(ucfirst($db_login_rdrlist[$iv]['riderName'])), 'utf-8', 'auto');
    $data['iUserId'] = $db_login_rdrlist[$iv]['iUserId'];
    $data['eDeviceType'] = $db_login_rdrlist[$iv]['eDeviceType'];
    array_push($db_login_rdr_list, $data);
}
$sql_inactive_drv = "select concat(vName,' ',vLastName) as DriverName,iDriverId,eDeviceType from register_driver where eStatus = 'Inactive' AND (vEmail != '' OR vPhone != '') order by vName";
$db_inactive_drvlist = $obj->MySQLSelect($sql_inactive_drv);

$db_inactive_drv_list = array();
for ($v = 0; $v < count($db_inactive_drvlist); $v++) {
    $data = array();
    $data['DriverName'] = mb_convert_encoding($generalobjAdmin->clearName(ucfirst($db_inactive_drvlist[$v]['DriverName'])), 'utf-8', 'auto');
    $data['iDriverId'] = $db_inactive_drvlist[$v]['iDriverId'];
    $data['eDeviceType'] = $db_inactive_drvlist[$v]['eDeviceType'];
    array_push($db_inactive_drv_list, $data);
}

$sql_inactive_rdr = "select concat(vName,' ',vLastName) as riderName,iUserId,eDeviceType from register_user where eStatus = 'Inactive' AND (vEmail != '' OR vPhone != '') order by vName";
$db_inactive_rdrlist = $obj->MySQLSelect($sql_inactive_rdr);
$db_inactive_rdr_list = array();
for ($vi = 0; $vi < count($db_inactive_rdrlist); $vi++) {
    $data = array();
    $data['riderName'] = mb_convert_encoding($generalobjAdmin->clearName(ucfirst($db_inactive_rdrlist[$vi]['riderName'])), 'utf-8', 'auto');
    $data['iUserId'] = $db_inactive_rdrlist[$vi]['iUserId'];
    $data['eDeviceType'] = $db_inactive_rdrlist[$vi]['eDeviceType'];
    array_push($db_inactive_rdr_list, $data);
}
if (DELIVERALL == 'Yes') {
    $sql = "SELECT c.iCompanyId, c.vCompany,c.eDeviceType FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE  c.eStatus = 'Active' AND sc.eStatus='Active' AND  c.iServiceId>0  order by c.vCompany";
    $db_storelist = $obj->MySQLSelect($sql);
    $db_store_list = array();
    for ($vii = 0; $vii < count($db_storelist); $vii++) {
        $data = array();
        $data['vCompany'] = mb_convert_encoding($generalobjAdmin->clearCmpName(ucfirst($db_storelist[$vii]['vCompany'])), 'utf-8', 'auto');
        $data['iCompanyId'] = $db_storelist[$vii]['iCompanyId'];
        $data['eDeviceType'] = $db_storelist[$vii]['eDeviceType'];
        array_push($db_store_list, $data);
    }

    $sql = "SELECT c.iCompanyId, c.vCompany,c.eDeviceType FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE  c.eStatus = 'Active' AND sc.eStatus='Active' AND c.eLogout = 'No'AND  c.iServiceId>0  order by c.vCompany";
    $db_login_rstlist = $obj->MySQLSelect($sql);
    $db_login_rst_list = array();
    for ($ix = 0; $ix < count($db_login_rstlist); $ix++) {
        $data = array();
        $data['vCompany'] = mb_convert_encoding($generalobjAdmin->clearCmpName(ucfirst($db_login_rstlist[$ix]['vCompany'])), 'utf-8', 'auto');
        $data['iCompanyId'] = $db_login_rstlist[$ix]['iCompanyId'];
        $data['eDeviceType'] = $db_login_rstlist[$ix]['eDeviceType'];
        array_push($db_login_rst_list, $data);
    }

    $sql = "SELECT c.iCompanyId, c.vCompany,c.eDeviceType FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE  c.eStatus = 'Inactive' AND sc.eStatus='Active' AND  c.eStatus = 'Inactive' AND  c.iServiceId>0  order by c.vCompany";
    $db_inactive_rstlist = $obj->MySQLSelect($sql);
    $db_inactive_rst_list = array();
    for ($x = 0; $x < count($db_inactive_rstlist); $x++) {
        $data = array();
        $data['vCompany'] = mb_convert_encoding($generalobjAdmin->clearCmpName(ucfirst($db_inactive_rstlist[$x]['vCompany'])), 'utf-8', 'auto');
        $data['iCompanyId'] = $db_inactive_rstlist[$x]['iCompanyId'];
        $data['eDeviceType'] = $db_inactive_rstlist[$x]['eDeviceType'];
        array_push($db_inactive_rst_list, $data);
    }
}

$tbl_name = 'pushnotification_log';
$script = 'Push Notification';
// set all variables with either post (when submit) either blank (when insert)
$eUserType = isset($_POST['eUserType']) ? $_POST['eUserType'] : '';
$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
$iRiderId = isset($_POST['iRiderId']) ? $_POST['iRiderId'] : '';

$eDeviceType = isset($_POST['eDeviceType']) ? $_POST['eDeviceType'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$iLoginCompanyId = isset($_POST['iLoginCompanyId']) ? $_POST['iLoginCompanyId'] : '';
$iInactiveCompanyId = isset($_POST['iInactiveCompanyId']) ? $_POST['iInactiveCompanyId'] : '';

$iLoginDriverId = isset($_POST['iLoginDriverId']) ? $_POST['iLoginDriverId'] : '';
$iLoginRiderId = isset($_POST['iLoginRiderId']) ? $_POST['iLoginRiderId'] : '';

$iInactiveDriverId = isset($_POST['iInactiveDriverId']) ? $_POST['iInactiveDriverId'] : '';
$iInactiveRiderId = isset($_POST['iInactiveRiderId']) ? $_POST['iInactiveRiderId'] : '';

$tMessage = isset($_POST['tMessage']) ? $_POST['tMessage'] : '';
$dDate = date("Y-m-d H:i:s");
// $ipAddress = $_SERVER['REMOTE_HOST'];
$ipAddress = $generalobj->get_client_ip();
if (isset($_POST['submit'])) {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    //for news feed table entry
    if (isset($tMessage) && $tMessage != '') {
        $tPublishdate = date("Y-m-d H:i:s");
        //Commented By HJ On 26-12-2018 As Per Discuss With CD Start
        /* $queryNews = "INSERT INTO `newsfeed` SET
          `vTitle` = '',
          `tDescription` = '" . $tMessage . "',
          `tPublishdate` = '".$tPublishdate."', eStatus = 'Active', eType = 'Notification'";

          $obj->sql_query($queryNews); */
        //Commented By HJ On 26-12-2018 As Per Discuss With CD End
    }
    //echo "<pre>";
    //print_r($_POST);die;
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = "Sending push notification has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.";
        header("Location:send_notifications.php");
        exit;
    }

    if ($eUserType == 'driver') {
        $set_table = 'register_driver';
        $set_userId = 'iDriverId';
        if ($iDriverId != "") {
            $userArr = explode(",", $iDriverId);
        } else {
            foreach ($db_drv_list as $dbd) {
                $userArr[] = $dbd['iDriverId'];
            }
        }
    } else if ($eUserType == 'rider') {
        $set_table = 'register_user';
        $set_userId = 'iUserId';
        if ($iRiderId != "") {
            $userArr = explode(",", $iRiderId);
        } else {
            foreach ($db_rdr_list as $dbr) {
                $userArr[] = $dbr['iUserId'];
            }
        }
    } else if ($eUserType == 'logged_driver') {
        $eUserType = 'driver';
        $set_table = 'register_driver';
        $set_userId = 'iDriverId';
        if ($iLoginDriverId != "") {
            $userArr = explode(",", $iLoginDriverId);
        } else {
            foreach ($db_login_drv_list as $dbd) {
                $userArr[] = $dbd['iDriverId'];
            }
        }
    } else if ($eUserType == 'logged_rider') {
        $eUserType = 'rider';
        $set_table = 'register_user';
        $set_userId = 'iUserId';
        if ($iLoginRiderId != "") {
            $userArr = explode(",", $iLoginRiderId);
        } else {
            foreach ($db_login_rdr_list as $dbr) {
                $userArr[] = $dbr['iUserId'];
            }
        }
    } else if ($eUserType == 'inactive_driver') {
        $eUserType = 'driver';
        $set_table = 'register_driver';
        $set_userId = 'iDriverId';
        if ($iInactiveDriverId != "") {
            $userArr = explode(",", $iInactiveDriverId);
        } else {
            foreach ($db_inactive_drv_list as $dbd) {
                $userArr[] = $dbd['iDriverId'];
            }
        }
    } else if ($eUserType == 'inactive_rider') {
        $eUserType = 'rider';
        $set_table = 'register_user';
        $set_userId = 'iUserId';
        if ($iInactiveRiderId != "") {
            $userArr = explode(",", $iInactiveRiderId);
        } else {
            foreach ($db_inactive_rdr_list as $dbr) {
                $userArr[] = $dbr['iUserId'];
            }
        }
    } else if ($eUserType == 'store') {
        $eUserType = 'company';
        $set_table = 'company';
        $set_userId = 'iCompanyId';
        if ($iCompanyId != "") {
            $userArr = explode(",", $iCompanyId);
        } else {
            foreach ($db_store_list as $dbr) {
                $userArr[] = $dbr['iCompanyId'];
            }
        }
    } else if ($eUserType == 'logged_store') {
        $eUserType = 'company';
        $set_table = 'company';
        $set_userId = 'iCompanyId';
        if ($iLoginCompanyId != "") {
            $userArr = explode(",", $iLoginCompanyId);
        } else {
            foreach ($db_login_rst_list as $dbd) {
                $userArr[] = $dbd['iCompanyId'];
            }
        }
    } else if ($eUserType == 'inactive_store') {
        $eUserType = 'company';
        $set_table = 'company';
        $set_userId = 'iCompanyId';
        if ($iInactiveCompanyId != "") {
            $userArr = explode(",", $iInactiveCompanyId);
        } else {
            foreach ($db_inactive_rst_list as $dbd) {
                $userArr[] = $dbd['iCompanyId'];
            }
        }
    }
    //echo "<pre>";print_r($userArr);die;
    $getUserData = $obj->MySQLSelect("SELECT eDeviceType,iGcmRegId,eDebugMode,$set_userId FROM " . $set_table);
    $notificationDataArr = array();
    for ($f = 0; $f < count($getUserData); $f++) {
        $notificationDataArr[$getUserData[$f][$set_userId]] = $getUserData[$f];
    }

    //echo "<pre>";print_r($notificationDataArr);die;
    $deviceTokens_arr_ios = $deviceTokens_arr_ios_pro = $registation_ids_new =$db_insert_arr= array();
    foreach ($userArr as $usAr) {
        //send_notification_fun($usAr);
        //Commented By HJ On 17-10-2019 For Prevent Load When Send Notification to More than User Start
        /* $q = "INSERT INTO ";
          $query = $q . " `" . $tbl_name . "` SET
          `eUserType` = '" . $eUserType . "',
          `iUserId` = '" . $usAr . "',
          `tMessage` = '" . $tMessage . "',
          `dDateTime` = '" . $dDate . "',
          `IP_ADDRESS` = '" . $ipAddress . "'";
          $responce = $obj->sql_query($query); */
        //$gcmIds = get_value($set_table, 'eDeviceType,iGcmRegId,eDebugMode', $set_userId, $usAr);
        //Commented By HJ On 17-10-2019 For Prevent Load When Send Notification to More than User End
        $db_insert_arr_tmp = array();
        $db_insert_arr_tmp['eUserType'] = $eUserType;
        $db_insert_arr_tmp['iUserId'] = $usAr;
        $db_insert_arr_tmp['tMessage'] = $obj->SqlEscapeString($tMessage);
        $db_insert_arr_tmp['dDateTime'] = $dDate;
        $db_insert_arr_tmp['IP_ADDRESS'] = $ipAddress;
        $db_insert_arr[] = $db_insert_arr_tmp;

        $eDeviceType = $iGcmRegId = $eDebugMode = "";
        if (isset($notificationDataArr[$usAr])) {
            $eDeviceType = $notificationDataArr[$usAr]['eDeviceType'];
            $iGcmRegId = $notificationDataArr[$usAr]['iGcmRegId'];
            $eDebugMode = $notificationDataArr[$usAr]['eDebugMode'];
        }
        if ($iGcmRegId != '' && strlen($iGcmRegId) > 15) {
            if ($eDeviceType == 'Android') {
                array_push($registation_ids_new, $iGcmRegId);
            } else {
                if ($iGcmRegId != "simulator_demo_1234") {
                    if ($APP_MODE == 'Development') {
                        if ($eDebugMode == 'Yes') {
                            array_push($deviceTokens_arr_ios, $iGcmRegId);
                        } else {
                            array_push($deviceTokens_arr_ios_pro, $iGcmRegId);
                        }
                    } else {
                        if ($eDebugMode == 'No') {
                            array_push($deviceTokens_arr_ios_pro, $iGcmRegId);
                        } else {
                            array_push($deviceTokens_arr_ios, $iGcmRegId);
                        }
                    }
                }
            }
        }
    }
    $db_insert_arr_final = array_chunk($db_insert_arr, 250);
    foreach ($db_insert_arr_final as $db_insert_arr_final_item_arr) {
        $ins_query = "INSERT INTO `" . $tbl_name . "` (`" . implode("`,`", array_keys($db_insert_arr_final_item_arr[0])) . "`) VALUES ";
        $isFirstItem = true;
        foreach ($db_insert_arr_final_item_arr as $db_insert_arr_final_item_arr_item) {
            $data = " (" . implode(', ', array_map(
                                    function ($v, $k) {
                                return sprintf("'%s'", $v);
                            }, $db_insert_arr_final_item_arr_item, array_keys($db_insert_arr_final_item_arr_item)
                    )) . ")";
            $ins_query .= $isFirstItem == false ? ", " . $data : $data;
            $isFirstItem = false;
        }
        $obj->sql_query($ins_query);
    }
    //$tMessage=str_replace('\r\n','\n',$tMessage);
    $tMessage = trim(stripslashes($obj->SqlEscapeString($tMessage)));
    $tMessage = str_replace(array('\r', '\n'), array(chr(13), chr(10)), $tMessage);
    // $tMessage = nl2br($tMessage,false); die;
    send_notification_fun($registation_ids_new, $deviceTokens_arr_ios, $tMessage, $eUserType, $deviceTokens_arr_ios_pro);
}
/* add for test */
if ($_GET['send_notification_fun'] == 'test') {
    $tMessage = $_GET['tMessage'];
    $eUserType = $_GET['eUserType'];
    $deviceTokens_arr_ios = $_GET['deviceTokens_arr_ios'];
    $message = stripslashes($tMessage);
    if ($eUserType == "rider") {
        $result = sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $tMessage, 0, 'admin');
    } else {
        $result = sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $tMessage, 0, 'admin');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Send Push-Notification </title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php
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
            <?php
            include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>Send Push-Notification </h2>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php include('valid_msg.php'); ?>
                            <div class="clear"></div>
                            <form id="_notification_form" name="_notification_form" method="post" action="javascript:void(0);" >
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Select User Type</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name = 'eUserType' id="eUserType" onChange="showUsers(this.value);">
                                            <option value="driver">All <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?></option>
                                            <option value="rider">All <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?></option>
                                            <?php if (!empty($db_login_drv_list)) { ?>
                                                <option value="logged_driver">All Logged in <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?></option>
                                            <?php } ?>
                                            <?php if (!empty($db_login_rdr_list)) { ?>
                                                <option value="logged_rider">All Logged in <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?></option>
                                            <?php } ?>
                                            <?php if (!empty($db_inactive_drv_list)) { ?>
                                                <option value="inactive_driver">All Inactive <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?></option>
                                            <?php } ?>
                                            <?php if (!empty($db_inactive_rdr_list)) { ?>
                                                <option value="inactive_rider">All Inactive <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?></option>
                                            <?php } ?>
                                            <?php if (DELIVERALL == 'Yes') { ?>
                                                <?php if (!empty($db_store_list)) { ?>
                                                    <option value="store">All <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?></option>
                                                <?php } ?>
                                                <?php if (!empty($db_login_rst_list)) { ?>
                                                    <option value="logged_store">All Logged in <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?></option>
                                                <?php } ?>
                                                <?php if (!empty($db_inactive_rst_list)) { ?>
                                                    <option value="inactive_store">All Inactive <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?></option>
                                                <?php } ?> 
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>			
                                <div class="row set-dd-css" id="driverRw">
                                    <div class="col-lg-12">
                                        <label>Select <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" name="iDriverId" id="iDriverId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>" value=""/>
                                    </div>
                                </div>
                                <div class="row set-dd-css" id="riderRw" style="display:none;">
                                    <div class="col-lg-12">
                                        <label>Select <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?><span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" name="iRiderId" id="iRiderId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>" value=""/>
                                    </div>
                                </div>
                                <?php if (!empty($db_login_drv_list)) { ?>
                                    <div class="row set-dd-css" id="logindriverRw" style="display:none;">
                                        <div class="col-lg-12">
                                            <label>Select Logged in <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?><span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="iLoginDriverId" id="iLoginDriverId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>" value=""/>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($db_login_rdr_list)) { ?>
                                    <div class="row set-dd-css" id="loginriderRw" style="display:none;">
                                        <div class="col-lg-12">
                                            <label>Select Logged in <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?><span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="iLoginRiderId" id="iLoginRiderId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>" value=""/>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($db_inactive_drv_list)) { ?>
                                    <div class="row set-dd-css" id="inactive_driverRw" style="display:none;">
                                        <div class="col-lg-12">
                                            <label>Select Inactive <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?><span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="iInactiveDriverId" id="iInactiveDriverId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>" value=""/>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($db_inactive_rdr_list)) { ?>
                                    <div class="row set-dd-css" id="inactive_riderRw" style="display:none;">
                                        <div class="col-lg-12">
                                            <label>Select Inactive <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?><span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="iInactiveRiderId" id="iInactiveRiderId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>" value=""/>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (DELIVERALL == 'Yes') { ?>

                                    <div class="row set-dd-css" id="storeRw" style="display:none;">
                                        <div class="col-lg-12">
                                            <label>Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?><span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="iCompanyId" id="iCompanyId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>" value=""/>
                                        </div>
                                    </div>
                                    <div class="row set-dd-css" id="loginstoreRw" style="display:none;">
                                        <div class="col-lg-12">
                                            <label>Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?><span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="iLoginCompanyId" id="iLoginCompanyId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>" value=""/>
                                        </div>
                                    </div>
                                    <div class="row set-dd-css" id="inactive_storeRw" style="display:none;">
                                        <div class="col-lg-12">
                                            <label>Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?><span class="red"> *</span></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="iInactiveCompanyId" id="iInactiveCompanyId" class="form-control magicsearch" style="width:600px !important;" placeholder="Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>" value=""/>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Message<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <textarea name="tMessage" class="form-control" id="tMessage" required maxlength="100" ></textarea>
                                        Note:Do not include any special characters, symbols, emoji. This may break push notification.  
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" onClick="submit_form();" value="Send Notification" >
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
        <link href="css/jquery.magicsearch.css" rel="stylesheet">
        <script src="js/jquery.magicsearch.js"></script>
        <style>
            .error {
                color:red;
                font-weight: normal;
            }
            .select2-container--default .select2-search--inline .select2-search__field{
                width:500px !important;
            }
        </style>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script>
                                            var allDriverArr = [];
                                            var allRiderArr = [];
                                            var loggedInDriverArr = [];
                                            var loggedInRiderArr = [];
                                            var inactiveDriverArr = [];
                                            var inactiveRiderArr = [];
                                            var allStoreArr = [];
                                            var loggedInStoreArr = [];
                                            var inactiveStoreArr = [];
                                            var deliverAll = '<?= DELIVERALL; ?>';
                                            allDriverArr = <?= json_encode($db_drv_list, JSON_UNESCAPED_UNICODE); ?>;
                                            allRiderArr = <?= json_encode($db_rdr_list, JSON_UNESCAPED_UNICODE); ?>;
                                            loggedInDriverArr = <?= json_encode($db_login_drv_list, JSON_UNESCAPED_UNICODE); ?>;
                                            loggedInRiderArr = <?= json_encode($db_login_rdr_list, JSON_UNESCAPED_UNICODE); ?>;
                                            inactiveDriverArr = <?= json_encode($db_inactive_drv_list, JSON_UNESCAPED_UNICODE); ?>;
                                            inactiveRiderArr = <?= json_encode($db_inactive_rdr_list, JSON_UNESCAPED_UNICODE); ?>;
                                            if (deliverAll == "Yes") {
                                                allStoreArr = <?= json_encode($db_store_list, JSON_UNESCAPED_UNICODE); ?>;
                                                loggedInStoreArr = <?= json_encode($db_login_rst_list, JSON_UNESCAPED_UNICODE); ?>;
                                                inactiveStoreArr = <?= json_encode($db_inactive_rst_list, JSON_UNESCAPED_UNICODE); ?>;
                                            }
                                            $(function () {
                                                //setDropDownData("iDriverId", "alldriver");
                                                setTimeout(function () {
                                                    $('#iDriverId').magicsearch({
                                                        dataSource: allDriverArr,
                                                        fields: ['DriverName'],
                                                        id: 'iDriverId',
                                                        format: '%DriverName%',
                                                        multiple: true,
                                                        multiField: 'DriverName'
                                                    });
                                                    $('#iRiderId').magicsearch({
                                                        dataSource: allRiderArr,
                                                        fields: ['riderName'],
                                                        id: 'iUserId',
                                                        format: '%riderName%',
                                                        multiple: true,
                                                        multiField: 'riderName'
                                                    });
                                                    $('#iLoginDriverId').magicsearch({
                                                        dataSource: loggedInDriverArr,
                                                        fields: ['DriverName'],
                                                        id: 'iDriverId',
                                                        format: '%DriverName%',
                                                        multiple: true,
                                                        multiField: 'DriverName'
                                                    });
                                                    $('#iLoginRiderId').magicsearch({
                                                        dataSource: loggedInRiderArr,
                                                        fields: ['riderName'],
                                                        id: 'iUserId',
                                                        format: '%riderName%',
                                                        multiple: true,
                                                        multiField: 'riderName'
                                                    });
                                                    $('#iInactiveDriverId').magicsearch({
                                                        dataSource: inactiveDriverArr,
                                                        fields: ['DriverName'],
                                                        id: 'iDriverId',
                                                        format: '%DriverName%',
                                                        multiple: true,
                                                        multiField: 'DriverName'
                                                    });
                                                    $('#iInactiveRiderId').magicsearch({
                                                        dataSource: inactiveRiderArr,
                                                        fields: ['riderName'],
                                                        id: 'iUserId',
                                                        format: '%riderName%',
                                                        multiple: true,
                                                        multiField: 'riderName'
                                                    });
                                                    if (deliverAll == "Yes") {
                                                        $('#iCompanyId').magicsearch({
                                                            dataSource: allStoreArr,
                                                            fields: ['vCompany'],
                                                            id: 'iCompanyId',
                                                            format: '%vCompany%',
                                                            multiple: true,
                                                            multiField: 'vCompany'
                                                        });
                                                        $('#iLoginCompanyId').magicsearch({
                                                            dataSource: loggedInStoreArr,
                                                            fields: ['vCompany'],
                                                            id: 'iCompanyId',
                                                            format: '%vCompany%',
                                                            multiple: true,
                                                            multiField: 'vCompany'
                                                        });
                                                        $('#iInactiveCompanyId').magicsearch({
                                                            dataSource: inactiveStoreArr,
                                                            fields: ['vCompany'],
                                                            id: 'iCompanyId',
                                                            format: '%vCompany%',
                                                            multiple: true,
                                                            multiField: 'vCompany'
                                                        });
                                                    }
                                                }, 1000);
                                            });
                                            function setDropDownData(dpId, requestType) {
                                                notificationArr = [];
                                                $(".loader-default").fadeOut("slow");
                                                $.ajax({
                                                    type: 'get',
                                                    datatype: 'JSON',
                                                    url: 'ajax_get_notification_details.php?qt=' + requestType,
                                                    success: function (data) {
                                                        notificationArr = data;
                                                        console.log(dpId + " Data Count :" + data.length);
                                                    },
                                                    error: function (e) {
                                                        alert('Error Generating Notification List');
                                                    }
                                                });
                                            }
                                            function submit_form() {
                                                var joinTxt = '';
                                                if ($("#_notification_form").valid()) {
                                                    var userType = $("#eUserType").val();
                                                    if (userType == 'rider') {
                                                        if ($("#iRiderId").val() == '' || $("#iRiderId").val() == null) {
                                                            joinTxt = 'All <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iRiderId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>(s)';
                                                        }
                                                    } else if (userType == 'driver') {
                                                        if ($("#iDriverId").val() == '' || $("#iDriverId").val() == null) {
                                                            joinTxt = '<?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iDriverId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>(s)';
                                                        }
                                                    } else if (userType == 'logged_driver') {
                                                        if ($("#iLoginDriverId").val() == '' || $("#iLoginDriverId").val() == null) {
                                                            joinTxt = 'All Logged In <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iLoginDriverId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' Logged In <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>(s)';
                                                        }
                                                    } else if (userType == 'logged_rider') {
                                                        if ($("#iLoginRiderId").val() == '' || $("#iLoginRiderId").val() == null) {
                                                            joinTxt = 'All Logged In <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iLoginRiderId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' Logged In <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>(s)';
                                                        }
                                                    } else if (userType == 'inactive_driver') {
                                                        if ($("#iInactiveDriverId").val() == '' || $("#iInactiveDriverId").val() == null) {
                                                            joinTxt = 'All Inactive <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iInactiveDriverId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' Inactive <?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN'] ?>(s)';
                                                        }
                                                    } else if (userType == 'inactive_rider') {
                                                        if ($("#iInactiveRiderId").val() == '' || $("#iInactiveRiderId").val() == null) {
                                                            joinTxt = 'All Inactive <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iInactiveRiderId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' Inactive <?= $langage_lbl_admin['LBL_RIDERS_ADMIN'] ?>(s)';
                                                        }
                                                    } else if (userType == 'store' && deliverAll == "Yes") {
                                                        if ($("#iCompanyId").val() == '' || $("#iCompanyId").val() == null) {
                                                            joinTxt = 'All <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iCompanyId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>(s)';
                                                        }
                                                    } else if (userType == 'logged_store' && deliverAll == "Yes") {
                                                        if ($("#iLoginCompanyId").val() == '' || $("#iLoginCompanyId").val() == null) {
                                                            joinTxt = 'All Logged In <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iLoginCompanyId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>(s)';
                                                        }
                                                    } else if (userType == 'inactive_store' && deliverAll == "Yes") {
                                                        if ($("#iInactiveCompanyId").val() == '' || $("#iInactiveCompanyId").val() == null) {
                                                            joinTxt = 'All Inactive <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>';
                                                        } else {
                                                            var len = $('#iInactiveCompanyId option:selected').length;
                                                            joinTxt = 'Selected ' + len + ' <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>(s)';
                                                        }
                                                    }

                                                    if (confirm("Confirm to send push notification to " + joinTxt + "?")) {
                                                        $("#_notification_form").attr('action', '');
                                                        $("#_notification_form").submit();
                                                    }
                                                }
                                            }
                                            function showUsers(userType) {
                                                if (userType == 'driver') {
                                                    $("#driverRw").show();
                                                    $("#riderRw,#logindriverRw,#loginriderRw,#inactive_driverRw,#inactive_riderRw").hide();
                                                    if (deliverAll == "Yes") {
                                                        $("#inactive_storeRw,#loginstoreRw,#storeRw").hide();
                                                    }
                                                } else if (userType == 'rider') {
                                                    $("#riderRw").show();
                                                    $("#driverRw,#logindriverRw,#loginriderRw,#inactive_driverRw,#inactive_riderRw").hide();
                                                    if (deliverAll == "Yes") {
                                                        $("#inactive_storeRw,#loginstoreRw,#storeRw").hide();
                                                    }
                                                } else if (userType == 'logged_driver') {
                                                    $("#logindriverRw").show();
                                                    $("#riderRw,#driverRw,#loginriderRw,#inactive_driverRw,#inactive_riderRw").hide();
                                                    if (deliverAll == "Yes") {
                                                        $("#inactive_storeRw,#loginstoreRw,#storeRw").hide();
                                                    }
                                                } else if (userType == 'logged_rider') {
                                                    $("#loginriderRw").show();
                                                    $("#riderRw,#driverRw,#logindriverRw,#inactive_driverRw,#inactive_riderRw").hide();
                                                    if (deliverAll == "Yes") {
                                                        $("#inactive_storeRw,#loginstoreRw,#storeRw").hide();
                                                    }
                                                } else if (userType == 'inactive_driver') {
                                                    $("#inactive_driverRw").show();
                                                    $("#riderRw,#driverRw,#logindriverRw,#loginriderRw,#inactive_riderRw").hide();
                                                    if (deliverAll == "Yes") {
                                                        $("#inactive_storeRw,#loginstoreRw,#storeRw").hide();
                                                    }
                                                } else if (userType == 'inactive_rider') {
                                                    $("#inactive_riderRw").show();
                                                    $("#loginriderRw,#riderRw,#driverRw,#logindriverRw,#inactive_driverRw").hide();
                                                    if (deliverAll == "Yes") {
                                                        $("#inactive_storeRw,#loginstoreRw,#storeRw").hide();
                                                    }
                                                } else if (userType == 'store' && deliverAll == "Yes") {
                                                    $("#storeRw").show();
                                                    $("#inactive_riderRw,#loginriderRw,#riderRw,#driverRw,#logindriverRw,#inactive_driverRw,#inactive_storeRw,#loginstoreRw").hide();
                                                } else if (userType == 'logged_store' && deliverAll == "Yes") {
                                                    $("#loginstoreRw").show();
                                                    $("#inactive_riderRw,#loginriderRw,#riderRw,#driverRw,#logindriverRw,#inactive_driverRw,#inactive_storeRw,#storeRw").hide();
                                                } else if (userType == 'inactive_store' && deliverAll == "Yes") {
                                                    $("#inactive_storeRw").show();
                                                    $("#loginstoreRw,#storeRw,#inactive_riderRw,#loginriderRw,#riderRw,#driverRw,#logindriverRw,#inactive_driverRw").hide();
                                                }
                                            }
        </script>
    </body>
    <!-- END BODY-->
</html>
