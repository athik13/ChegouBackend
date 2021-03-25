<?php
header('X-XSS-Protection:0');
include_once('../common.php');
$$msgType = "";
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
define("CONFIGURATIONS_PAYMENT", "configurations_payment");
define("CONFIGURATIONS", "configurations");
define("NOTIFICATION_SOUND", "notification_sound");
////$generalobjAdmin->check_member_login();
if (!$userObj->hasPermission('manage-general-settings')) {
    $userObj->redirect();
}
include_once('common.php');
//ini_set("display_errors", 1);
$script = $activeTab = 'General';
$msgType = isset($_REQUEST['msgType']) ? $_REQUEST['msgType'] : '';
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$projectname = isset($_REQUEST['projectname']) ? trim($_REQUEST['projectname']) : '';
if (isset($_POST['submitbutton']) && $_POST['submitbutton'] != "") {
    //echo "<pre>";
    //POOL_ENABLE
    if (SITE_TYPE == 'Demo') {
        $msgType = 0;
        $msg = $langage_lbl_admin['LBL_EDIT_DELETE_RECORD'];
        header("Location:general.php?msgType=" . $msgType . "&msg=" . $msg);
        exit;
    }
    $activeTab = str_replace(" ", "_", $_REQUEST['frm_type']);
    $configTable = CONFIGURATIONS;
    if ($activeTab == "Payment") {
        $configTable = CONFIGURATIONS_PAYMENT;
    }
    //print_r($_REQUEST);die;
    foreach ($_REQUEST['Data'] as $key => $value) {
        unset($updateData);
        //Added By HJ On 11-01-2019 For Solved Bug - 6178 As Per Discuss With CD Sir Start
        if ($key == "POOL_ENABLE" && $value == "No") {
            //echo "UPDATE vehicle_type SET eStatus='Inactive' WHERE ePoolStatus='Yes'";die;
            //$obj->sql_query("UPDATE vehicle_type SET eStatus='Inactive' WHERE ePoolStatus='Yes'");
        } else if ($key == "APP_PAYMENT_MODE") {
            $value = str_replace("Wallet", "Card", $value);
        } else if ($key == "SITE_NAME") {
            //Added BY HJ On 25-06-2019 For Replace Project Name In All Table when Changed From Configuration Screen Start
            if ($projectname != "" && $projectname != $value) {
                $dbAllTablesArr = $generalobj->getAllTableArray(); // For Get Current Db's All Table Arr
                $tableReplaceQuery = array();
                //echo "<pre>";print_r($dbAllTablesArr);die;
                for ($t = 0; $t < count($dbAllTablesArr); $t++) {
                    //echo $dbAllTablesArr[$t];die;
                    //$projectname = "Myprojectname";
                    //$value = "ProjectName";
                    $tableArr = $generalobj->searchnReplaceWord($projectname, trim($value), $dbAllTablesArr[$t]);
                    if ($tableArr != "") {
                        $tableReplaceQuery[$dbAllTablesArr[$t]] = $tableArr;
                    }
                }
                //echo "<pre>";print_r($tableReplaceQuery);die;
                foreach ($tableReplaceQuery as $table => $query) {
                    //print_r($query);die;
                    //echo $query . "<br>";
                    $obj->sql_query($query);
                }
            }
            //Added BY HJ On 25-06-2019 For Replace Project Name In All Table when Changed From Configuration Screen End
        }
        //Added By HJ On 11-01-2019 For Solved Bug - 6178 As Per Discuss With CD Sir End
        $updateData['vValue'] = trim($value);
        $where = " vName = '" . $key . "' AND eType = '" . $_REQUEST['frm_type'] . "'";
        $res = $obj->MySQLQueryPerform($configTable, $updateData, 'update', $where);
    }
    if ($res) {
        $msgType = 1;
        $msg = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    } else {
        $msgType = 0;
        $msg = "Error in update configuration";
    }

    /* ADDED BY PJ FOR REMOVE CLEAR SERVICE REQUESTS */
    if ($ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes' && $_REQUEST['Data']['ENABLE_DRIVER_SERVICE_REQUEST_MODULE'] == 'No') {
        $qry = "TRUNCATE TABLE driver_service_request";
        $serviceRequests = $obj->sql_query($qry);
    }
    /* END REMOVE CLEAR SERVICE REQUESTS */
}
if (isset($_POST['notificationbutton'])) {
    //echo "<pre>";print_r($_POST);die;
    $userFile = isset($_POST['User']) ? $_POST['User'] : '0';
    $storeFile = isset($_POST['Store']) ? $_POST['Store'] : '0';
    $providerFile = isset($_POST['Provider']) ? $_POST['Provider'] : '0';
    $dialFile = isset($_POST['Dial']) ? $_POST['Dial'] : '0';
    $voipFile = isset($_POST['Voip']) ? $_POST['Voip'] : '0';
    //echo "<pre>";print_r($_POST);die;
    $selSql = $soundIds = "";
    if (count($userFile) > 0) {
        $selSql .= "'User'";
        $soundIds .= "'" . $userFile[0] . "'";
    }
    if (count($storeFile) > 0) {
        $selSql .= ",'Store'";
        $soundIds .= ",'" . $storeFile[0] . "'";
    }
    if (count($providerFile) > 0) {
        $selSql .= ",'Provider'";
        $soundIds .= ",'" . $providerFile[0] . "'";
    }
    if (count($dialFile) > 0) {
        $selSql .= ",'Dial'";
        $soundIds .= ",'" . $dialFile[0] . "'";
    }
    if (count($voipFile) > 0) {
        $selSql .= ",'Voip'";
        $soundIds .= ",'" . $voipFile[0] . "'";
    }
    if ($selSql != "") {
        $remTrim = trim($selSql, ",");
        $remTrimIds = trim($soundIds, ",");
        //echo "<pre>";print_r($remTrimIds);die;
        $obj->sql_query("UPDATE " . NOTIFICATION_SOUND . " SET eIsSelected='No' WHERE eSoundFor IN ($remTrim)");
        $obj->sql_query("UPDATE " . NOTIFICATION_SOUND . " SET eIsSelected='Yes' WHERE iSoundId IN ($remTrimIds)");
    }
}
/* $sql = "SELECT * FROM " . CONFIGURATIONS . " WHERE eAdminDisplay = 'Yes' ORDER BY eType, vOrder";
  $data_gen = $obj->MySQLSelect($sql); */
$ssql_config = "";
//if (strtoupper($generalobj->CheckUfxServiceAvailable()) != "YES") {
//    $ssql_config = " AND eFor != 'UberX'";
//}
//$uberxService = $generalobj->CheckUfxServiceAvailable();
$flymodule = 'No';
if (checkFlyStationsModule()) {
    $flymodule = 'Yes';
}

$uberxService = isUberXModuleAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = isRideModuleAvailable() ? "Yes" : "No";
$deliveryEnable = isDeliveryModuleAvailable() ? "Yes" : "No";
$deliverallEnable = isDeliverAllModuleAvailable() ? "Yes" : "No";

$sql = "SELECT * FROM " . CONFIGURATIONS . " WHERE eAdminDisplay = 'Yes' " . $ssql_config . " ORDER BY eType, vOrder";
$data_gen = $obj->MySQLSelect($sql);
//echo "<pre>";print_r($data_gen);die;
//country
$sql1 = "SELECT * FROM country WHERE eStatus = 'Active' ";
$country_name = $obj->MySQLSelect($sql1);
foreach ($data_gen as $key => $value) {
    if(($value['eFor']=='' || $value['eFor']=='General')
           || ($flymodule == 'Yes' && $value['eFor'] == "Fly")
           || (ENABLEKIOSKPANEL == 'Yes' && $value['eFor'] == "Kiosk")
           || ((strtoupper(DELIVERALL) == 'YES' || strtoupper(ONLYDELIVERALL) == 'YES') && $value['eFor'] == "DeliverAll" && $deliverallEnable == "Yes")
           || (APP_TYPE == 'Ride-Delivery-UberX' && $value['eFor'] == 'Ride-Delivery-UberX' && strtoupper(ONLYDELIVERALL) == 'NO')
           || ((APP_TYPE == 'Ride' || APP_TYPE == 'Ride-Delivery-UberX' || APP_TYPE == 'Ride-Delivery') && ($value['eFor'] == "Ride" || in_array("Ride", explode(",",  $value['eFor'])) || in_array("Ride", explode("-",  $value['eFor']))) && strtoupper(ONLYDELIVERALL) == 'NO' && $rideEnable == "Yes")
           || ((APP_TYPE == 'Delivery' || APP_TYPE == 'Ride-Delivery-UberX' || APP_TYPE == 'Ride-Delivery') && ($value['eFor'] == "Delivery" || $value['eFor'] == "Multi-Delivery" || in_array("Delivery", explode(",",  $value['eFor'])) || in_array("Delivery", explode("-",  $value['eFor']))) && strtoupper(ONLYDELIVERALL) == 'NO' && $deliveryEnable == "Yes")
           || (($uberxService == "Yes") && ($value['eFor'] == "UberX" || in_array("UberX", explode(",",  $value['eFor'])) || in_array("UberX", explode("-",  $value['eFor']))))
           ) {
    $db_gen[$value['eType']][$key]['iSettingId'] = $value['iSettingId'];
    //$db_gen[$value['eType']][$key]['tDescription'] = $value['tDescription']; // Commented By HJ On 21-08-2019 Replace Of Below Line
    $value['tDescription'] = str_replace($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."/".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tDescription']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : #680
    if (strpos($value['tDescription'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].'/'.$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) !== false) {
        $value['tDescription'] = str_replace($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."/".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tDescription']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : #680
    }
    $db_gen[$value['eType']][$key]['tDescription'] = str_replace("User", $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'], $value['tDescription']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : 250 BY HS Mam
    $db_gen[$value['eType']][$key]['vValue'] = $value['vValue'];
    //$db_gen[$value['eType']][$key]['tHelp'] = $value['tHelp']; // Commented By HJ On 21-08-2019 Replace Of Below Line
    $value['tHelp'] = str_replace("Provider", $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tHelp']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : 250 BY HS Mam
    if (strpos($value['tHelp'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].'/'.$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) !== false) {
        $value['tHelp'] = str_replace($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."/".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'], $value['tHelp']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : #680
    }
    $db_gen[$value['eType']][$key]['tHelp'] = str_replace("User", $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'], $value['tHelp']); //Added By HJ On 21-08-2019 For Solved Issue to fixed Sheet Id : 250 BY HS Mam
    $db_gen[$value['eType']][$key]['vName'] = $value['vName'];
    $db_gen[$value['eType']][$key]['eInputType'] = $value['eInputType'];
    $db_gen[$value['eType']][$key]['tSelectVal'] = $value['tSelectVal'];
    $db_gen[$value['eType']][$key]['eZeroAllowed'] = $value['eZeroAllowed'];
    $db_gen[$value['eType']][$key]['eDoubleValueAllowed'] = $value['eDoubleValueAllowed'];
    $db_gen[$value['eType']][$key]['eSpaceAllowed'] = $value['eSpaceAllowed'];
    $db_gen[$value['eType']][$key]['eConfigRequired'] = $value['eConfigRequired'];
    }
}
//echo "<pre>";print_r($db_gen);die;
$sandboxArr = array("STRIPE_SECRET_KEY_SANDBOX", "STRIPE_PUBLISH_KEY_SANDBOX", "BRAINTREE_TOKEN_KEY_SANDBOX", "BRAINTREE_TOKEN_KEY_LIVE", "BRAINTREE_ENVIRONMENT_SANDBOX", "BRAINTREE_MERCHANT_ID_SANDBOX", "BRAINTREE_PUBLIC_KEY_SANDBOX", "BRAINTREE_PRIVATE_KEY_SANDBOX", "PAYMAYA_API_URL_SANDBOX", "PAYMAYA_SECRET_KEY_SANDBOX", "PAYMAYA_PUBLISH_KEY_SANDBOX", "PAYMAYA_ENVIRONMENT_MODE_SANDBOX", "OMISE_SECRET_KEY_SANDBOX", "OMISE_PUBLIC_KEY_SANDBOX", "ADYEN_MERCHANT_ACCOUNT_SANDBOX", "ADYEN_USER_NAME_SANDBOX", "ADYEN_PASSWORD_SANDBOX", "ADYEN_API_URL_SANDBOX", "XENDIT_SECRET_KEY_SANDBOX", "XENDIT_PUBLIC_KEY_SANDBOX", "FLUTTERWAVE_ENCRYPTION_KEY_SANDBOX", "FLUTTERWAVE_SECRET_KEY_SANDBOX", "FLUTTERWAVE_API_URL_SANDBOX", "FLUTTERWAVE_PUBLIC_KEY_SANDBOX");
//echo "<pre>";
for ($r = 0; $r < count($sandboxArr); $r++) {
    $sandboxArr[$r] = rtrim($sandboxArr[$r], "SANDBOX");
}
$getPayDataQuery = "SELECT * FROM " . CONFIGURATIONS_PAYMENT . " WHERE eAdminDisplay = 'Yes' ORDER BY eType, vOrder";
$fetchData = $obj->MySQLSelect($getPayDataQuery);
$getPayFlow = $obj->MySQLSelect("SELECT * FROM " . CONFIGURATIONS_PAYMENT . " WHERE vName = 'SYSTEM_PAYMENT_FLOW'");
$eSystemPayFlow = "Method-1";
if (count($getPayFlow) > 0) {
    $eSystemPayFlow = $getPayFlow[0]['vValue'];
}
//echo "<pre>";print_r($eSystemPayFlow);die;
//Added By HJ On 12-08-2019 For Remove Only Card Option If Payment Flow 2 Or 3 As Per Discuss with KS Sir Start
if ($eSystemPayFlow == "Method-2" || $eSystemPayFlow == "Method-3") {
    $obj->sql_query("UPDATE " . CONFIGURATIONS_PAYMENT . " SET tSelectVal='Cash,Cash-Card' WHERE vName='APP_PAYMENT_MODE'");
} else {
    $obj->sql_query("UPDATE " . CONFIGURATIONS_PAYMENT . " SET tSelectVal='Cash,Card,Cash-Card' WHERE vName='APP_PAYMENT_MODE'");
}
//Added By HJ On 12-08-2019 For Remove Only Card Option If Payment Flow 2 Or 3 As Per Discuss with KS Sir End
$cardTxt = $cardTxt1 = "Card";
foreach ($fetchData as $payKey => $payValue) {
    
    if(($payValue['eFor']=='' || $payValue['eFor']=='General')
           || ($flymodule == 'Yes' && $payValue['eFor'] == "Fly")
           || (ENABLEKIOSKPANEL == 'Yes' && $payValue['eFor'] == "Kiosk")
           || ((strtoupper(DELIVERALL) == 'YES' || strtoupper(ONLYDELIVERALL) == 'YES') && $payValue['eFor'] == "DeliverAll" && $deliverallEnable == "Yes")
           || (APP_TYPE == 'Ride-Delivery-UberX' && $payValue['eFor'] == 'Ride-Delivery-UberX' && strtoupper(ONLYDELIVERALL) == 'NO')
           || ((APP_TYPE == 'Ride' || APP_TYPE == 'Ride-Delivery-UberX' || APP_TYPE == 'Ride-Delivery') && ($payValue['eFor'] == "Ride" || in_array("Ride", explode(",",  $payValue['eFor'])) || in_array("Ride", explode("-",  $payValue['eFor']))) && strtoupper(ONLYDELIVERALL) == 'NO' && $rideEnable == "Yes")
           || ((APP_TYPE == 'Delivery' || APP_TYPE == 'Ride-Delivery-UberX' || APP_TYPE == 'Ride-Delivery') && ($payValue['eFor'] == "Delivery" || $payValue['eFor'] == "Multi-Delivery" || in_array("Delivery", explode(",",  $payValue['eFor'])) || in_array("Delivery", explode("-",  $payValue['eFor']))) && strtoupper(ONLYDELIVERALL) == 'NO' && $deliveryEnable == "Yes")
           || (($uberxService == "Yes") && ($payValue['eFor'] == "UberX" || in_array("UberX", explode(",",  $payValue['eFor'])) || in_array("UberX", explode("-",  $payValue['eFor']))))
           ) {
    
    
    if (isset($payValue['vName']) && $payValue['vName'] == "APP_PAYMENT_MODE" && ($eSystemPayFlow == "Method-2" || $eSystemPayFlow == "Method-3")) {
        $walletTxt = "Wallet";
        $payValue['vValue'] = str_replace($cardTxt1, $walletTxt, $payValue['vValue']);
        $payValue['tSelectVal'] = str_replace($cardTxt1, $walletTxt, $payValue['tSelectVal']);
        $cardTxt = $walletTxt;
    }
    $db_gen[$payValue['eType']][$payKey] = $payValue;
    }
}

//Added BY HJ On 05-08-2019 For Get Notification Sound Data Start
$soundSql = " AND eSoundFor != 'Store'";
if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Foodonly" || $APP_TYPE == "Deliverall" || DELIVERALL == "Yes" || ONLYDELIVERALL == "Yes") {
    $soundSql = "";
}
$soundData = $obj->MySQLSelect("SELECT * FROM " . NOTIFICATION_SOUND . " WHERE eStatus = 'Active' AND eAdminDisplay='Yes' $soundSql");
$useNotificationFile = $providerNotificationFile = $dialNotificationFile = $storeNotificationFile = "default.mp3";
$mp3Url = $tconfig['tsite_url'];
$mp3path = $tconfig["tpanel_path"] . "webimages/notification_sound/";
//echo "<pre>";print_R($soundData);die;
$userSoundDataArr = array();
for ($r = 0; $r < count($soundData); $r++) {
    $vFileName = $soundData[$r]['vFileName'];
    $eSoundFor = $soundData[$r]['eSoundFor'];
    $eDefault = $soundData[$r]['eDefault'];
    $checkFile = $mp3path . strtolower($eSoundFor) . "/" . $vFileName;

    if (file_exists($checkFile)) {
        $userSoundDataArr[$eSoundFor][] = $soundData[$r];
    } else if ($eDefault == "Yes") {
        $userSoundDataArr[$eSoundFor][] = $soundData[$r];
    }
}
//echo "<pre>";print_R($userSoundDataArr);die;
//Added BY HJ On 05-08-2019 For Get Notification Sound Data End
//echo "<pre>";print_r($db_gen);exit();
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> 
<html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | Configuration</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />


        <? include_once('global_files.php'); ?>

    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2> General Settings </h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">General  Settings</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <?php if ($msgType == '1') { ?>	
                                                    <div class="alert alert-success alert-dismissable">
                                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button><?= $msg ?>
                                                    </div>
                                                <?php } elseif ($msgType == '0') {
                                                    ?>
                                                    <div class="alert alert-danger alert-dismissable">
                                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>	<?= $msg ?> 
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <ul class="nav nav-tabs">
                                            <?php
                                            foreach ($db_gen as $key => $value) {
                                                $newKey = str_replace(" ", "_", $key);
                                                ?>
                                                <li class="<?php echo $activeTab == $newKey ? 'active' : '' ?>">
                                                    <a data-toggle="tab" href="#<?= $newKey ?>">
                                                        <?php
                                                        if ($key == "Apperance")
                                                            echo "Appearance";
                                                        else
                                                            echo $key;
                                                        ?>
                                                    </a>
                                                </li>
                                            <?php }
                                            ?>
                                            <li>
                                                <a data-toggle="tab" href="#soundsetting">
                                                    Notification Sound
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <?php
                                            $paymentEnvMode = "";
                                            foreach ($db_gen as $key => $value) {
                                                if($key != ""){
                                                $value = array_values($value);
                                                //echo "<pre>";print_r($value);die;
                                                $cnt = count($value);
                                                $tab1 = ceil(count($value) / 2);
                                                $tab2 = $cnt - $tab1;
                                                $newKey = str_replace(" ", "_", $key);
                                                if ($key != "Payment") {
                                                    ?>
                                                    <div id="<?= $newKey ?>" class="tab-pane <?php echo $activeTab == $newKey ? 'active' : '' ?>">
                                                        <form method="POST" action="" name="frm_<?= $key ?>">
                                                            <input type="hidden" name="frm_type" value="<?= $key ?>">
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <?php
                                                                    $i = 0;
                                                                    $temp = true;
                                                                    foreach ($value as $key1 => $value1) {
                                                                        $i++;
                                                                        if ($tab1 < $i && $temp) {
                                                                            $temp = false;
                                                                            ?>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php
                                                                        }

                                                                        if (isset($value1['vName']) && $value1['vName'] == "SITE_NAME") {
                                                                            ?>
                                                                            <input type="hidden" value="<?= $value1['vValue'] ?>" name="projectname">
                                                                        <?php }
                                                                        ?>
                                                                        <div class="form-group">
                                                                            <?php
                                                                            if ($value1['vName'] == 'RIDER_EMAIL_VERIFICATION') {
                                                                                if (ONLYDELIVERALL != "Yes") {
                                                                                    ?>
                                                                                    <label><?= $value1['tDescription'] ?><?php if ($value1['tHelp'] != "") { ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($value1['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php } ?></label>
                                                                                    <?php
                                                                                }
                                                                            } else {
                                                                                ?>	
                                                                                <label><?= $value1['tDescription'] ?><?php if ($value1['tHelp'] != "") { ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($value1['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php } ?></label>
                                                                            <?php } ?>
                                                                            <?php if ($value1['eInputType'] == 'Textarea') { ?>
                                                                                <textarea class="form-control" rows="5" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?> ><?= $value1['vValue'] ?></textarea>
                                                                                <?php
                                                                            } elseif ($value1['eInputType'] == 'Select') {
                                                                                $optionArr = explode(',', $value1['tSelectVal']);
                                                                                if ($value1['vName'] == 'DEFAULT_COUNTRY_CODE_WEB') {
                                                                                    ?>
                                                                                    <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?>>
                                                                                        <?php
                                                                                        foreach ($country_name as $Value) {
                                                                                            $selected = $value1['vValue'] == $Value['vCountryCode'] ? 'selected' : '';
                                                                                            ?>
                                                                                            <option value="<?= $Value['vCountryCode'] ?>" <?= $selected ?>><?= $Value['vCountry'] . ' (' . $Value['vCountryCode'] . ')'; ?></option>
                                                                                            <?php
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                <?php } else if ($value1['vName'] == 'ENABLE_HAIL_RIDES') { ?>
                                                                                    <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>>
                                                                                        <?php
                                                                                        foreach ($optionArr as $oKey => $oValue) {
                                                                                            $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                            ?>
                                                                                            <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                            <?php
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                    <div> [Note: This option will not work if you have selected payment mode "<?= $cardTxt; ?>"] </div>
                                                                                <?php } else if ($value1['vName'] == 'DRIVER_REQUEST_METHOD') {
                                                                                    ?>
                                                                                    <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <?php } ?>>
                                                                                        <?php
                                                                                        foreach ($optionArr as $oKey => $oValue) {
                                                                                            $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                            if ($oValue == 'All') {
                                                                                                $oValuenew = $oValue . " (COMPETITIVE ALGORITHM)";
                                                                                            } else if ($oValue == 'Distance') {
                                                                                                $oValuenew = $oValue . " (Nearest 1st Algorithm)";
                                                                                            } else if ($oValue == 'Time') {
                                                                                                $oValuenew = $oValue . " (FIFO Algorithm)";
                                                                                            } else {
                                                                                                $oValuenew = $oValue;
                                                                                            }
                                                                                            ?>
                                                                                            <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValuenew ?></option>
                                                                                        <?php } ?>
                                                                                    </select>
                                                                                    <?php
                                                                                } else if ($value1['vName'] == 'RIDER_EMAIL_VERIFICATION') {
                                                                                    if (ONLYDELIVERALL != "Yes") {
                                                                                        ?>
                                                                                        <select class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?>>
                                                                                            <?php
                                                                                            foreach ($optionArr as $oKey => $oValue) {
                                                                                                $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                                ?>
                                                                                                <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                                <?php
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                        <?php
                                                                                    }
                                                                                } else {
                                                                                    $onChangeEvent = "";
                                                                                    if ($value1['vName'] == 'TRIP_TRACKING_METHOD') {
                                                                                        $onChangeEvent = 'onchange="showConfimbox(this.value);"';
                                                                                    }
                                                                                    ?>
                                                                                    <select <?= $onChangeEvent; ?> class="form-control" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?>>
                                                                                        <?php
                                                                                        foreach ($optionArr as $oKey => $oValue) {
                                                                                            $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                            ?>
                                                                                            <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                            <?php
                                                                                        }
                                                                                        ?>
                                                                                    </select>

                                                                                <?php } ?>
                                                                                <?php
                                                                            } else {
                                                                                if ($value1['eInputType'] == 'Number') {
                                                                                    if ($value1['vName'] == 'MAX_NUMBER_STOP_OVER_POINTS') {
                                                                                        ?>
                                                                                        <input type="number" name="Data[<?= $value1['vName'] ?>]" id = "<?= $value1['vName'] ?>" class="form-control numberfield <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?> <? if ($value1['eZeroAllowed'] == 'Yes') { ?> min="0" <? } else { ?> min="2" <? } ?>  <? if ($value1['eDoubleValueAllowed'] == 'No') { ?> onkeypress="return event.charCode >= 48 && event.charCode <= 57" <? } else { ?> step = 0.01 <? } ?> >    
                                                                                    <?php } else { ?>
                                                                                        <input type="number" name="Data[<?= $value1['vName'] ?>]" id = "<?= $value1['vName'] ?>" class="form-control numberfield <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?> <? if ($value1['eZeroAllowed'] == 'Yes') { ?> min="0" <? } else { ?> min="1" <? } ?>  <? if ($value1['eDoubleValueAllowed'] == 'No') { ?> onkeypress="return event.charCode >= 48 && event.charCode <= 57" <? } else { ?> step = 0.01 <? } ?> >
                                                                                        <?php
                                                                                    }
                                                                                } elseif ($value1['eInputType'] == 'Time') {
                                                                                    ?>	

                                                                                    <input type="time" name="Data[<?= $value1['vName'] ?>]" class="form-control date" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?>>
                                                                                <? } else { ?>

                                                                                    <input type="text" name="Data[<?= $value1['vName'] ?>]" class="form-control date" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?> <? if ($value1['eSpaceAllowed'] == 'No') { ?> onkeyup="nospaces(this)" <? } ?> >
                                                                                    <?
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="form-group" style="text-align: center;">
                                                                        <input type="submit" name="submitbutton" class="btn btn-primary save-configuration" value="Save Changes">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                <?php } else {
                                                    ?>
                                                    <div id="<?= $newKey ?>" class="tab-pane <?php echo $activeTab == $newKey ? 'active' : '' ?>">
                                                        <form method="POST" action="" name="frm_<?= $key ?>">
                                                            <input type="hidden" name="frm_type" value="<?= $key ?>">
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <?php
                                                                    $i = 0;
                                                                    $temp = true;
                                                                    foreach ($value as $key1 => $value1) {
                                                                        $i++;
                                                                        if ($tab1 < $i && $temp) {
                                                                            $temp = false;
                                                                        }
                                                                        ?>
                                                                        <div class="form-group">
                                                                            <label class="<?= $value1['vName'] ?>"><?= $value1['tDescription'] ?><?php if ($value1['tHelp'] != "") { ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= htmlspecialchars($value1['tHelp'], ENT_QUOTES, 'UTF-8') ?>'></i><?php } ?></label>
                                                                            <?php
                                                                            if ($value1['eInputType'] == 'Textarea') {
                                                                                ?>
                                                                                <textarea class="form-control" rows="5" name="Data[<?= $value1['vName'] ?>]" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required="required" <? } ?>><?= $value1['vValue'] ?></textarea>
                                                                                <?php
                                                                            } elseif ($value1['eInputType'] == 'Select') {
                                                                                $optionArr = explode(',', $value1['tSelectVal']);
                                                                                $onChangedEvent = "";

                                                                                if ($value1['vName'] == "SYSTEM_PAYMENT_ENVIRONMENT") {
                                                                                    $onChangedEvent = 'onchange="changePayEnv();"';
                                                                                    $paymentEnvMode = $value1['vValue'];
                                                                                    //echo "<pre>";print_r($value1['vValue']);die;
                                                                                }
                                                                                ?>
                                                                                <select class="form-control <?= $value1['vName'] ?>" name="Data[<?= $value1['vName'] ?>]" id="<?= $value1['vName'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?>>
                                                                                    <?php
                                                                                    foreach ($optionArr as $oKey => $oValue) {
                                                                                        $selected = $oValue == $value1['vValue'] ? 'selected' : '';
                                                                                        ?>
                                                                                        <option value="<?= $oValue ?>" <?= $selected ?>><?= $oValue ?></option>
                                                                                        <?php
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            <?php } elseif ($value1['eInputType'] == 'Number') {
                                                                                ?>
                                                                                <input type="number" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?> name="Data[<?= $value1['vName'] ?>]"<? if ($value1['eZeroAllowed'] == 'Yes') { ?> min="0" <? } else { ?> min="1" <? } ?>  id = "<?= $value1['vName'] ?>" class="form-control numberfield <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <? if ($value1['eDoubleValueAllowed'] == 'No') { ?> onkeypress="return event.charCode >= 48 && event.charCode <= 57" <? } ?> >
                                                                            <?php } elseif ($value1['eInputType'] == 'Time') {
                                                                                ?>
                                                                                <input type="time" name="Data[<?= $value1['vName'] ?>]" id = "<?= $value1['vName'] ?>" class="form-control <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?> >
                                                                            <? } else {
                                                                                ?>
                                                                                <input type="text" name="Data[<?= $value1['vName'] ?>]" id = "<?= $value1['vName'] ?>" class="form-control <?= $value1['vName'] ?>" value="<?= $value1['vValue'] ?>" <?php if ($value1['eConfigRequired'] == 'Yes') { ?> required <? } ?> <? if ($value1['eSpaceAllowed'] == 'No') { ?>onkeyup="nospaces(this)" <? } ?> >
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="form-group" style="text-align: center;">
                                                                        <input type="submit" name="submitbutton" class="btn btn-primary save-configuration" value="Save Changes">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                            }
                                            ?>
                                            <div id="soundsetting" class="tab-pane">
                                                <form method="POST" action="" name="frm_soundsetting" novalidate>
                                                    <input type="hidden" name="frm_type" value="soundsetting">
                                                    <?php
                                                    foreach ($userSoundDataArr as $for => $data) {
                                                        $headName = $for . " App";
                                                        $helpTxt = "";
                                                        if ($for == "Dial") {
                                                            $headName = "New Job Request (i.e 30 second Dial) Screen";
                                                            $helpTxt = "Selected notification sound will be played when service provider/driver receives new service request. If you select 'Phone's default Notification Sound' option then it will play your phone's default tone.";
                                                        } else if ($for == "Voip") {
                                                            $headName = "Voip Calling";
                                                            $helpTxt = "Selected notification sound will be played when user and service provider receives In App/VOIP based calls as a part of call masking . If you select 'Phone's default Notification Sound' option then it will play your phone's default tone.";
                                                        } else if ($for == "Provider") {
                                                            $helpTxt = "Selected notification sound will be played when service provider/driver receives rest of notifications apart from new service/job notification. If you select 'Phone's default Notification Sound' option then it will play your phone's default tone. ";
                                                        } else if ($for == "Store") {
                                                            $helpTxt = "Selected notification sound will be played when Store app receives notifications for events like new order request and all other kind of push notifications. If you select 'Phone's default Notification Sound' option then it will play your phone's default tone.";
                                                        } else if ($for == "User") {
                                                            $helpTxt = "Selected notification sound will be played when user app receives notifications for events like service start, service end and all other kind of push notifications. If you select 'Phone's default Notification Sound' option then it will play your phone's default tone.";
                                                        }
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-lg-8">
                                                                <div class="form-group">
                                                                    <h3>Notification Sound For <?= $headName; ?> <i class="icon-question-sign" data-placement="auto top" data-toggle="tooltip" data-original-title='<?= $helpTxt; ?>'></i></h3>
                                                                </div>
                                                                <?php
                                                                for ($s = 0; $s < count($data); $s++) {
                                                                    $iSoundId = $data[$s]['iSoundId'];
                                                                    $eIsSelected = $data[$s]['eIsSelected'];
                                                                    $eSoundFor = strtolower($data[$s]['eSoundFor']);
                                                                    $vFileName = $data[$s]['vFileName'];
                                                                    $eDefault = $data[$s]['eDefault'];
                                                                    ?>
                                                                    <div class="form-group notificationcls">
                                                                        <input class="mp3checkbox" type="radio" value="<?= $iSoundId; ?>" name="<?= $for; ?>[]" <?php if ($eIsSelected == "Yes") { ?>checked=""<?php } ?>>
                                                                        <input class="form-control mp3text" type="text" disabled="disabled" name="user_mp3" value="<?= $vFileName; ?>">
                                                                        <?php if ($eDefault == "No") { ?>
                                                                            <audio controls>
                                                                                <source src="<?= $mp3Url; ?>webimages/notification_sound/<?= $eSoundFor; ?>/<?= $vFileName; ?>" type="audio/mpeg">
                                                                            </audio>
                                                                        <?php } ?>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="form-group" style="text-align: center;">
                                                                <input type="submit" name="notificationbutton" class="btn btn-primary save-configuration" value="Save Changes">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="clear"></div>
                    <?php if (SITE_TYPE != 'Demo') { ?>
                        <div class="admin-notes">
                            <h4>Notes:</h4>
                            <ul>
                                <li>
                                    Please close the application and open it again to see the settings reflected after saving the new setting values above.
                                </li>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?php
        include_once('footer.php');
        ?>
        <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script>
                                                                                    var boxesArr = stripearray = xenditarray = braintreearray = paymayarray = omisarray = adyenarray = someKeys = fakeArr = [];
                                                                                    var previous = '';
                                                                                    var prevEnvMode = '<?= $paymentEnvMode; ?>';
                                                                                    function checkDec(el) {
                                                                                        var ex = /^\d+(\.\d{0,2})?$/g;
                                                                                        if (ex.test(el.value) == false) {
                                                                                            el.value = el.value.substring(0, el.value.length - 1);
                                                                                        }
                                                                                    }
                                                                                    $('[data-toggle="tooltip"]').tooltip();
                                                                                    $(document).ready(function () {
                                                                                        $('#dataTables-example').dataTable();
<?php foreach ($sandboxArr as $key => $val) { ?>
                                                                                            boxesArr.push('<?php echo $val; ?>');
<?php } ?>
                                                                                        changePayEnv();
                                                                                    });
                                                                                    function changePayEnv() {
                                                                                        var payEnv = $("#SYSTEM_PAYMENT_ENVIRONMENT").val();
                                                                                        var apppaymentmethod = $("#APP_PAYMENT_METHOD").val();
																		  
																		
                                                                        someKeys = ['APP_PAYMENT_METHOD', 'STRIPE_SECRET_KEY_SANDBOX', 'STRIPE_PUBLISH_KEY_SANDBOX', 'STRIPE_SECRET_KEY_LIVE', 'STRIPE_PUBLISH_KEY_LIVE', 'XENDIT_SECRET_KEY_SANDBOX', 'XENDIT_PUBLIC_KEY_SANDBOX', 'XENDIT_SECRET_KEY_LIVE', 'XENDIT_PUBLIC_KEY_LIVE', 'BRAINTREE_TOKEN_KEY_SANDBOX', 'BRAINTREE_TOKEN_KEY_LIVE', 'BRAINTREE_ENVIRONMENT_SANDBOX', 'BRAINTREE_MERCHANT_ID_SANDBOX', 'BRAINTREE_PUBLIC_KEY_SANDBOX', 'BRAINTREE_PRIVATE_KEY_SANDBOX', 'BRAINTREE_ENVIRONMENT_LIVE', 'BRAINTREE_MERCHANT_ID_LIVE', 'BRAINTREE_PUBLIC_KEY_LIVE', 'BRAINTREE_PRIVATE_KEY_LIVE', 'BRAINTREE_CHARGE_AMOUNT', 'PAYMAYA_API_URL_SANDBOX', 'PAYMAYA_SECRET_KEY_SANDBOX', 'PAYMAYA_PUBLISH_KEY_SANDBOX', 'PAYMAYA_ENVIRONMENT_MODE_SANDBOX', 'PAYMAYA_API_URL_LIVE', 'PAYMAYA_SECRET_KEY_LIVE', 'PAYMAYA_PUBLISH_KEY_LIVE', 'PAYMAYA_ENVIRONMENT_MODE_LIVE', 'OMISE_SECRET_KEY_SANDBOX', 'OMISE_PUBLIC_KEY_SANDBOX', 'OMISE_SECRET_KEY_LIVE', 'OMISE_PUBLIC_KEY_LIVE', 'ADYEN_MERCHANT_ACCOUNT_SANDBOX', 'ADYEN_USER_NAME_SANDBOX', 'ADYEN_PASSWORD_SANDBOX', 'ADYEN_API_URL_SANDBOX', 'ADYEN_MERCHANT_ACCOUNT_LIVE', 'ADYEN_USER_NAME_LIVE', 'ADYEN_PASSWORD_LIVE', 'ADYEN_API_URL_LIVE', 'ADYEN_CHARGE_AMOUNT', 'PAYMAYA_CHECKOUT_PUBLISH_KEY_SANDBOX', 'PAYMAYA_CHECKOUT_PUBLISH_KEY_LIVE', 'DEFAULT_CURRENCY_CONVERATION_CODE_RATIO', 'DEFAULT_CURRENCY_CONVERATION_CODE', 'DEFAULT_CURRENCY_CONVERATION_ENABLE', 'FLUTTERWAVE_PUBLIC_KEY_SANDBOX', 'FLUTTERWAVE_SECRET_KEY_SANDBOX','FLUTTERWAVE_PUBLIC_KEY_LIVE', 'FLUTTERWAVE_SECRET_KEY_LIVE', 'FLUTTERWAVE_ENCRYPTION_KEY_SANDBOX', 'FLUTTERWAVE_API_URL_SANDBOX','FLUTTERWAVE_CHARGE_AMOUNT', 'FLUTTERWAVE_ENCRYPTION_KEY_LIVE', 'FLUTTERWAVE_API_URL_LIVE', 'FLUTTERWAVE_STAGING_URL_SANDBOX', 'FLUTTERWAVE_STAGING_URL_LIVE',
                                                                        xenditarray = ['XENDIT_SECRET_KEY_SANDBOX', 'XENDIT_PUBLIC_KEY_SANDBOX', 'XENDIT_SECRET_KEY_LIVE', 'XENDIT_PUBLIC_KEY_LIVE'];
                                                                        braintreearray = ['BRAINTREE_TOKEN_KEY_SANDBOX', 'BRAINTREE_TOKEN_KEY_LIVE', 'BRAINTREE_ENVIRONMENT_SANDBOX', 'BRAINTREE_MERCHANT_ID_SANDBOX', 'BRAINTREE_PUBLIC_KEY_SANDBOX', 'BRAINTREE_PRIVATE_KEY_SANDBOX', 'BRAINTREE_ENVIRONMENT_LIVE', 'BRAINTREE_MERCHANT_ID_LIVE', 'BRAINTREE_PUBLIC_KEY_LIVE', 'BRAINTREE_PRIVATE_KEY_LIVE', 'BRAINTREE_CHARGE_AMOUNT'];
                                                                        paymayarray = ['PAYMAYA_API_URL_SANDBOX', 'PAYMAYA_SECRET_KEY_SANDBOX', 'PAYMAYA_PUBLISH_KEY_SANDBOX', 'PAYMAYA_ENVIRONMENT_MODE_SANDBOX', 'PAYMAYA_API_URL_LIVE', 'PAYMAYA_SECRET_KEY_LIVE', 'PAYMAYA_PUBLISH_KEY_LIVE', 'PAYMAYA_ENVIRONMENT_MODE_LIVE', 'PAYMAYA_CHECKOUT_PUBLISH_KEY_SANDBOX', 'PAYMAYA_CHECKOUT_PUBLISH_KEY_LIVE'];
                                                                        omisarray = ['OMISE_SECRET_KEY_SANDBOX', 'OMISE_PUBLIC_KEY_SANDBOX', 'OMISE_SECRET_KEY_LIVE', 'OMISE_PUBLIC_KEY_LIVE'];
																		flutterarray = ['FLUTTERWAVE_PUBLIC_KEY_SANDBOX', 'FLUTTERWAVE_SECRET_KEY_SANDBOX','FLUTTERWAVE_PUBLIC_KEY_LIVE', 'FLUTTERWAVE_SECRET_KEY_LIVE', 'FLUTTERWAVE_ENCRYPTION_KEY_SANDBOX', 'FLUTTERWAVE_API_URL_SANDBOX','FLUTTERWAVE_CHARGE_AMOUNT', 'FLUTTERWAVE_ENCRYPTION_KEY_LIVE', 'FLUTTERWAVE_API_URL_LIVE', 'FLUTTERWAVE_STAGING_URL_SANDBOX', 'FLUTTERWAVE_STAGING_URL_LIVE'];
                                                                        adyenarray = ['ADYEN_MERCHANT_ACCOUNT_SANDBOX', 'ADYEN_USER_NAME_SANDBOX', 'ADYEN_PASSWORD_SANDBOX', 'ADYEN_API_URL_SANDBOX', 'ADYEN_MERCHANT_ACCOUNT_LIVE', 'ADYEN_USER_NAME_LIVE', 'ADYEN_PASSWORD_LIVE', 'ADYEN_API_URL_LIVE', 'ADYEN_CHARGE_AMOUNT'];

                                                                                        defaultCurrencyarray = ['DEFAULT_CURRENCY_CONVERATION_CODE_RATIO', 'DEFAULT_CURRENCY_CONVERATION_CODE', 'DEFAULT_CURRENCY_CONVERATION_ENABLE'];

                                                                                        $.each(boxesArr, function (key, value) {
                                                                                            someKeys.push(value + "LIVE");
                                                                                            someKeys.push(value + "SANDBOX");
                                                                                        });
                                                                                        paymentConfing();
                                                                                    }
                                                                                    function confirm_delete()
                                                                                    {
                                                                                        var confirm_ans = confirm("Are You sure You want to Delete Driver?");
                                                                                        return confirm_ans;
                                                                                        //document.getElementById(id).submit();
                                                                                    }
                                                                                    function changeCode(id)
                                                                                    {
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
                                                                                    $("form").submit(function () {
                                                                                            //Added By HJ On 11-06-2019 For Reset User Data When Change Payment Environment Mode Start
                                                                                            var clearUserData = 0;
                                                                                            if (prevEnvMode != $('#SYSTEM_PAYMENT_ENVIRONMENT').val() && $('#SYSTEM_PAYMENT_ENVIRONMENT').val() != undefined) {
                                                                                                var clearUserData = 1;
                                                                                            }
                                                                                            //Added By HJ On 11-06-2019 For Reset User Data When Change Payment Environment Mode End
                                                                                            if ((previous != '' && $('#APP_PAYMENT_MODE').val() != 'Cash') || clearUserData == 1) {
                                                                                                var status = confirm("Please note that changing payment gateway will reset all your <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>'s saved credit <?= $cardTxt; ?> details through last set payment gateway. <?php echo $langage_lbl_admin['LBL_RIDERS_ADMIN']; ?> will have to re-enter credit <?= $cardTxt; ?> details for new payment gateway once they make a first transaction.Click OK to continue?");
                                                                                                if (status == false) {
                                                                                                    return false;
                                                                                                } else {
                                                                                                    var request = $.ajax({
                                                                                                        type: "POST",
                                                                                                        url: "ajax_payment_method.php",
                                                                                                        data: {paymentmethod: previous, envmode: clearUserData},
                                                                                                        success: function (data) {
                                                                                                            //alert(previous);
                                                                                                            return false;
                                                                                                        }
                                                                                                    });
                                                                                                    //return false;
                                                                                                }
                                                                                            } else {
                                                                                                //$("#APP_PAYMENT_METHOD").val(previous);
                                                                                                return true;
                                                                                            }
                                                                                        });
                                                                                    $(function () {
                                                                                        paymentConfing();
                                                                                        var apppaymentmode = $('#APP_PAYMENT_MODE').val();
                                                                                        var apppaymentmethod = $("#APP_PAYMENT_METHOD").val();
                                                                                        $('#APP_PAYMENT_MODE').change(function () {
                                                                                            if ($('#APP_PAYMENT_MODE').val() == '<?= $cardTxt; ?>' || $('#APP_PAYMENT_MODE').val() == 'Cash-<?= $cardTxt; ?>') {
																						
                                                                                                $('#APP_PAYMENT_METHOD,.APP_PAYMENT_METHOD').show();
                                                                                                if ($("#APP_PAYMENT_METHOD").val() !== 'Stripe') {
                                                                                                    $.each(stripearray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(stripearray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Braintree') {
                                                                                                    $.each(braintreearray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(braintreearray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Paymaya') {
                                                                                                    $.each(paymayarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(paymayarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Omise') {
                                                                                                    $.each(omisarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(omisarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                $.each(defaultCurrencyarray, function (key, value) {
                                                                                                    $('.' + value).show();
                                                                                });
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Flutterwave') {
                                                                                    $.each(flutterarray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(flutterarray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Adyen') {
                                                                                    $.each(adyenarray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(adyenarray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Xendit') {
                                                                                    $.each(xenditarray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(xenditarray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }
                                                                              if ($("#APP_PAYMENT_METHOD").val() == 'Senangpay') {
                                                                                $.each(senangpayarray, function (key, value) {
                                                                                    $('.' + value).show();
                                                                                });
                                                                            } else {
                                                                                $.each(senangpayarray, function (key, value) {
                                                                                    $('.' + value).hide();
                                                                                });
                                                                            }
                                                                                $("#APP_PAYMENT_METHOD").on('focus', function () {
                                                                                    previous = this.value;
                                                                                    console.log(previous);
                                                                                }).change(function () {
																				
                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Stripe') {
                                                                                        $.each(stripearray, function (key, value) {
                                                                                            $('.' + value).show();
                                                                                        });
                                                                                        paymentConfing();
                                                                                    }
                                                                                    function confirm_delete()
                                                                                    {
                                                                                        var confirm_ans = confirm("Are You sure You want to Delete Driver?");
                                                                                        return confirm_ans;
                                                                                        //document.getElementById(id).submit();
                                                                                    }
                                                                                    function changeCode(id)
                                                                                    {
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
                                                                                    $(function () {
                                                                                        paymentConfing();
                                                                                        var apppaymentmode = $('#APP_PAYMENT_MODE').val();
                                                                                        var apppaymentmethod = $("#APP_PAYMENT_METHOD").val();
                                                                                        $('#APP_PAYMENT_MODE').change(function () {
                                                                                            if ($('#APP_PAYMENT_MODE').val() == '<?= $cardTxt; ?>' || $('#APP_PAYMENT_MODE').val() == 'Cash-<?= $cardTxt; ?>') {
                                                                                                $('#APP_PAYMENT_METHOD,.APP_PAYMENT_METHOD').show();
                                                                                                if ($("#APP_PAYMENT_METHOD").val() !== 'Stripe') {
                                                                                                    $.each(stripearray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(stripearray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Braintree') {
                                                                                                    $.each(braintreearray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(braintreearray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Paymaya') {
                                                                                                    $.each(paymayarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(paymayarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Omise') {
                                                                                                    $.each(omisarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(omisarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                $.each(defaultCurrencyarray, function (key, value) {
                                                                                                    $('.' + value).show();
                                                                                                });
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Adyen') {
                                                                                                    $.each(adyenarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(adyenarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Xendit') {
                                                                                                    $.each(xenditarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(xenditarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }

                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Senangpay') {
                                                                                $.each(senangpayarray, function (key, value) {
                                                                                    $('.' + value).show();
                                                                                });
                                                                            } else {
                                                                                $.each(senangpayarray, function (key, value) {
                                                                                    $('.' + value).hide();
                                                                                });
                                                                            }
                                                                                                

																								if ($("#APP_PAYMENT_METHOD").val() == 'Flutterwave') {
																									$.each(flutterarray, function (key, value) {
																										$('.' + value).show();
																									});
																								} else {
																									$.each(flutterarray, function (key, value) {
																										$('.' + value).hide();
																									});
																								}
                                                                                                $("#APP_PAYMENT_METHOD").on('focus', function () {
                                                                                                    previous = this.value;
                                                                                                    console.log(previous);
                                                                                                }).change(function () {
                                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Stripe') {
                                                                                                        $.each(stripearray, function (key, value) {
                                                                                                            $('.' + value).show();
                                                                                                        });
                                                                                                    } else {
                                                                                                        $.each(stripearray, function (key, value) {
                                                                                                            $('.' + value).hide();
                                                                                                        });
                                                                                                    }
                                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Braintree') {
                                                                                                        $.each(braintreearray, function (key, value) {
                                                                                                            $('.' + value).show();
                                                                                                        });
                                                                                                    } else {
                                                                                                        $.each(braintreearray, function (key, value) {
                                                                                                            $('.' + value).hide();
                                                                                                        });
                                                                                                    }
                                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Paymaya') {
                                                                                                        $.each(paymayarray, function (key, value) {
                                                                                                            $('.' + value).show();
                                                                                                        });
                                                                                                    } else {
                                                                                                        $.each(paymayarray, function (key, value) {
                                                                                                            $('.' + value).hide();
                                                                                                        });
                                                                                                    }
                                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Omise') {
                                                                                                        $.each(omisarray, function (key, value) {
                                                                                                            $('.' + value).show();
                                                                                                        });
                                                                                                    } else {
                                                                                                        $.each(omisarray, function (key, value) {
                                                                                                            $('.' + value).hide();
                                                                                                        });
                                                                                                    }
                                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Adyen') {
                                                                                                        $.each(adyenarray, function (key, value) {
                                                                                                            $('.' + value).show();
                                                                                                        });
                                                                                                    } else {
                                                                                                        $.each(adyenarray, function (key, value) {
                                                                                                            $('.' + value).hide();
                                                                                                        });
                                                                                                    }
                                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Xendit') {
                                                                                                        $.each(xenditarray, function (key, value) {
                                                                                                            $('.' + value).show();
                                                                                                        });
                                                                                                    } else {
                                                                                                        $.each(xenditarray, function (key, value) {
                                                                                                            $('.' + value).hide();
                                                                                                        });
                                                                                                    } 
																								if ($("#APP_PAYMENT_METHOD").val() == 'Flutterwave') {
																									$.each(flutterarray, function (key, value) {
																										$('.' + value).show();
																									});
																								} else {
																									$.each(flutterarray, function (key, value) {
																										$('.' + value).hide();
																									});
																								}
                                                                                                    $.each(defaultCurrencyarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                });
                                                                                            } else {
                                                                                                $.each(someKeys, function (key, value) {
                                                                                                    $('.' + value).hide();
                                                                                                });
                                                                                            }
                                                                                        });
                                                                        
                                                                                    });
                                                                                    function paymentConfing() {
                                                                                        var apppaymentmode = $('#APP_PAYMENT_MODE').val();
                                                                                        var apppaymentmethod = $("#APP_PAYMENT_METHOD").val();
                                                                                        if (apppaymentmode == '<?= $cardTxt; ?>' || apppaymentmode == 'Cash-<?= $cardTxt; ?>') {
                                                                                            $('#APP_PAYMENT_METHOD,.APP_PAYMENT_METHOD').show();
                                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Stripe') {
                                                                                                $.each(stripearray, function (key, value) {
                                                                                                    $('.' + value).show();
                                                                                                });
                                                                                            } else {
                                                                                                $.each(stripearray, function (key, value) {
                                                                                                    $('.' + value).hide();
                                                                                                });
                                                                                            }
                                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Braintree') {
                                                                                                $.each(braintreearray, function (key, value) {
                                                                                                    $('.' + value).show();
                                                                                                });
                                                                                            } else {
                                                                                                $.each(braintreearray, function (key, value) {
                                                                                                    $('.' + value).hide();
                                                                                                });
                                                                                            }
                                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Paymaya') {
                                                                                                $.each(paymayarray, function (key, value) {
                                                                                                    $('.' + value).show();
                                                                                                });
                                                                                            } else {
                                                                                                $.each(paymayarray, function (key, value) {
                                                                                                    $('.' + value).hide();
                                                                                                });
                                                                                            }
                                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Omise') {
                                                                                                $.each(omisarray, function (key, value) {
                                                                                                    $('.' + value).show();
                                                                                                });
                                                                                            } else {
                                                                                                $.each(omisarray, function (key, value) {
                                                                                                    $('.' + value).hide();
                                                                                                });
                                                                                            }
                                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Adyen') {
                                                                                                $.each(adyenarray, function (key, value) {
                                                                                                    $('.' + value).show();
                                                                                                });
                                                                                            } else {
                                                                                                $.each(adyenarray, function (key, value) {
                                                                                                    $('.' + value).hide();
                                                                                                });
                                                                                            }
                                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Xendit') {
                                                                                                $.each(xenditarray, function (key, value) {
                                                                                                    $('.' + value).show();
                                                                                                });
                                                                                            } else {
                                                                                                $.each(xenditarray, function (key, value) {
                                                                                                    $('.' + value).hide();
                                                                                                });
                                                                                            } 
																								if ($("#APP_PAYMENT_METHOD").val() == 'Flutterwave') {
																									$.each(flutterarray, function (key, value) {
																										$('.' + value).show();
																									});
																								} else {
																									$.each(flutterarray, function (key, value) {
																										$('.' + value).hide();
																									});
																								}
                                                                                            $("#APP_PAYMENT_METHOD").on('focus', function () {
                                                                                                previous = this.value;
                                                                                                console.log(previous);
                                                                                            }).change(function () {
                                                                                                if ($("#APP_PAYMENT_METHOD").val() !== 'Stripe') {
                                                                                                    $.each(stripearray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(stripearray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Braintree') {
                                                                                                    $.each(braintreearray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(braintreearray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Paymaya') {
                                                                                                    $.each(paymayarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(paymayarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Omise') {
                                                                                                    $.each(omisarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(omisarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Adyen') {
                                                                                                    $.each(adyenarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(adyenarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                }
                                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Xendit') {
                                                                                                    $.each(xenditarray, function (key, value) {
                                                                                                        $('.' + value).show();
                                                                                                    });
                                                                                                } else {
                                                                                                    $.each(xenditarray, function (key, value) {
                                                                                                        $('.' + value).hide();
                                                                                                    });
                                                                                                } 
																								if ($("#APP_PAYMENT_METHOD").val() == 'Flutterwave') {
																									$.each(flutterarray, function (key, value) {
																										$('.' + value).show();
																									});
																								} else {
																									$.each(flutterarray, function (key, value) {
																										$('.' + value).hide();
																									});
																								}
                                                                                            });
                                                                                        } else {
                                                                                            $.each(someKeys, function (key, value) {
                                                                                                $('.' + value).hide();
                                                                                            });
                                                                                        }

                                                                                    }
																					if ($("#APP_PAYMENT_METHOD").val() == 'Flutterwave') {
																						$.each(flutterarray, function (key, value) {
																							$('.' + value).show();
																						});
																					} else {
																						$.each(flutterarray, function (key, value) {
																							$('.' + value).hide();
																						});
																					}
                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Adyen') {
                                                                                        $.each(adyenarray, function (key, value) {
                                                                                            $('.' + value).show();
                                                                                        });
                                                                                    } else {
                                                                                        $.each(adyenarray, function (key, value) {
                                                                                            $('.' + value).hide();
                                                                                        });
                                                                                    }
                                                                                    if ($("#APP_PAYMENT_METHOD").val() == 'Xendit') {
                                                                                        $.each(xenditarray, function (key, value) {
                                                                                            $('.' + value).show();
                                                                                        });
                                                                                    } else {
                                                                                        $.each(xenditarray, function (key, value) {
                                                                                            $('.' + value).hide();
                                                                                        });
                                                                                    }
                                                                                    $.each(defaultCurrencyarray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                    $("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").on('blur', function () {
                                                                                        if (Number($("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val()) < Number($("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").val()) && $("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val() != '') {
                                                                                            alert("Maximum amount limit per day should be greater than or equal to maximum limit per transaction for money transfer");
                                                                                            $("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").val('');
                                                                                        }
                                                                                    });
                                                                                    //return false;
                                                                                });
                                                                            } else { 
																			
                                                                                 $.each(someKeys, function (key, value) {
                                                                                $('.' + value).hide();
                                                                            });
																			//$("#APP_PAYMENT_METHOD").val(previous);
                                                                                return true;
                                                                            }
                                                                        });
                                                                    });
                                                                    function paymentConfing() {
                                                                        var apppaymentmode = $('#APP_PAYMENT_MODE').val();
                                                                        var apppaymentmethod = $("#APP_PAYMENT_METHOD").val();
                                                                        if (apppaymentmode == '<?= $cardTxt; ?>' || apppaymentmode == 'Cash-<?= $cardTxt; ?>') {
                                                                            $('#APP_PAYMENT_METHOD,.APP_PAYMENT_METHOD').show();
                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Stripe') {
                                                                                $.each(stripearray, function (key, value) {
                                                                                    $('.' + value).show();
                                                                                });
                                                                            } else {
                                                                                $.each(stripearray, function (key, value) {
                                                                                    $('.' + value).hide();
                                                                                });
                                                                            }
                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Braintree') {
                                                                                $.each(braintreearray, function (key, value) {
                                                                                    $('.' + value).show();
                                                                                });
                                                                            } else {
                                                                                $.each(braintreearray, function (key, value) {
                                                                                    $('.' + value).hide();
                                                                                });
                                                                            }
                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Paymaya') {
                                                                                $.each(paymayarray, function (key, value) {
                                                                                    $('.' + value).show();
                                                                                });
                                                                            } else {
                                                                                $.each(paymayarray, function (key, value) {
                                                                                    $('.' + value).hide();
                                                                                });
                                                                            }if ($("#APP_PAYMENT_METHOD").val() == 'Flutterwave') {
																						$.each(flutterarray, function (key, value) {
																							$('.' + value).show();
																						});
																					} else {
																						$.each(flutterarray, function (key, value) {
																							$('.' + value).hide();
																						});
																					}
                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Omise') {
                                                                                $.each(omisarray, function (key, value) {
                                                                                    $('.' + value).show();
                                                                                });
                                                                            } else {
                                                                                $.each(omisarray, function (key, value) {
                                                                                    $('.' + value).hide();
                                                                                });
                                                                            }
                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Adyen') {
                                                                                $.each(adyenarray, function (key, value) {
                                                                                    $('.' + value).show();
                                                                                });
                                                                            } else {
                                                                                $.each(adyenarray, function (key, value) {
                                                                                    $('.' + value).hide();
                                                                                });
                                                                            }
                                                                            if ($("#APP_PAYMENT_METHOD").val() == 'Xendit') {
                                                                                $.each(xenditarray, function (key, value) {
                                                                                    $('.' + value).show();
                                                                                });
                                                                            } else {
                                                                                $.each(xenditarray, function (key, value) {
                                                                                    $('.' + value).hide();
                                                                                });
                                                                            }
                                                                            $("#APP_PAYMENT_METHOD").on('focus', function () {
                                                                                previous = this.value;
                                                                                console.log(previous);
                                                                            }).change(function () {
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Stripe') {
                                                                                    $.each(stripearray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(stripearray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Braintree') {
                                                                                    $.each(braintreearray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(braintreearray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Paymaya') {
                                                                                    $.each(paymayarray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(paymayarray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Omise') {
                                                                                    $.each(omisarray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(omisarray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Adyen') {
                                                                                    $.each(adyenarray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(adyenarray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }if ($("#APP_PAYMENT_METHOD").val() == 'Flutterwave') {
																						$.each(flutterarray, function (key, value) {
																							$('.' + value).show();
																						});
																					} else {
																						$.each(flutterarray, function (key, value) {
																							$('.' + value).hide();
																						});
																					}
                                                                                if ($("#APP_PAYMENT_METHOD").val() == 'Xendit') {
                                                                                    $.each(xenditarray, function (key, value) {
                                                                                        $('.' + value).show();
                                                                                    });
                                                                                } else {
                                                                                    $.each(xenditarray, function (key, value) {
                                                                                        $('.' + value).hide();
                                                                                    });
                                                                                }
                                                                            });
                                                                        } else {
                                                                            $.each(someKeys, function (key, value) {
                                                                                $('.' + value).hide();
                                                                            });
                                                                        }

                                                                    }
                                                                    //added by SP for maximum transaction limit on 29-07-2019
                                                                    $("#GOPAY_MAXIMUM_LIMIT_PER_DAY").on('blur', function () {
                                                                        if (Number($("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val()) < Number($("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").val())) {
                                                                            alert("Maximum amount limit per day should be greater than or equal to maximum limit per transaction for money transfer");
                                                                                            $("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val('');
                                                                                        }
                                                                                    });
                                                                                    $("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").on('blur', function () {
                                                                                        if (Number($("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val()) < Number($("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").val()) && $("#GOPAY_MAXIMUM_LIMIT_PER_DAY").val() != '') {
                                                                                            alert("Maximum amount limit per day should be greater than or equal to maximum limit per transaction for money transfer");
                                                                                            $("#GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION").val('');
                                                                                        }
                                                                                    });
        </script>
        <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
        <script type="text/javascript" src="js/moment.min.js"></script>

        <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
        <script type="text/javascript">
                                                                                    function nospaces(t) {
                                                                                        if (t.value.match(/\s/g)) {
                                                                                            alert('Sorry, you are not allowed to enter any spaces');
                                                                                            t.value = t.value.replace(/\s/g, '');
                                                                                        }
                                                                                    }
                                                                                    function showConfimbox(type) {
                                                                                        if (type == "Pubnub") {
                                                                                            alert("This option will increase Pubnub.com usage and so increase overall billing. Are you sure you want to select it..?");
                                                                                        }
                                                                                    }
                                                                                    $('input[type="time"]').datetimepicker({
                                                                                        format: 'HH:mm',
                                                                                        ignoreReadonly: true,
                                                                                        useCurrent: false
                                                                                    });


        </script>
    </body>
    <!-- END BODY-->
</html>
