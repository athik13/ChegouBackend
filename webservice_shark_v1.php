<?php
// ini_set('display_errors', 1);
//     error_reporting(E_ALL);
//define('SITE_TYPE','Demo');
include_once ('assets/libraries/server_configurations_params.php');
include_once ('assets/libraries/server_configurations.php');
include_once "DataHelper.php";
global $dataHelperObj;
$dataHelperObj = new DataHelper();
// echo "here:::";exit;
// $uniqval = '345';
// $testvar = $dataHelperObj;
// $GLOBALS['dataHelperObj']= new DataHelper();

/* define('DELIVERALL', 'Yes');
  define('ONLYDELIVERALL', 'No'); */
  
  /* echo "<PRE>====";
  print_r($POST);exit; */
############################################# Code Filter Process Part ###########################################
if (isset($_POST["APP_CONFIG_PARAMS_PACKAGE"]) != "" && !empty($_POST["APP_CONFIG_PARAMS_PACKAGE"])) {
    $APP_CONFIG_PARAMS_PACKAGE = $_POST["APP_CONFIG_PARAMS_PACKAGE"];

    $temp_APP_CONFIG_PARAMS_PACKAGE_arr = explode("&", $APP_CONFIG_PARAMS_PACKAGE);
    //echo "<pre>";print_r($appdata);die;
    $_REQUEST = array();
    foreach ($temp_APP_CONFIG_PARAMS_PACKAGE_arr as $value) {
        $array = explode('=', $value);
        $array[1] = trim($array[1], '"');
        $_REQUEST[$array[0]] = urldecode($array[1]);
    }
}
//print_r($_REQUEST);exit;
############################################# Code Filter Process Part ###########################################
$iServiceId = isset($_REQUEST["iServiceId"]) ? $_REQUEST["iServiceId"] : '';
$eSystem = isset($_REQUEST["eSystem"]) ? $_REQUEST["eSystem"] : '';
$UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
//if ($iServiceId != "" || $eSystem == "DeliverAll" || $UserType == "Company" || ONLYDELIVERALL == "Yes") { //Commented By HJ On 10-01-2019 As Per Discuss With KS Sir
//Added By HJ On 07-02-2020 For Solved Issue Of Payment Report On Collect Payment As Per Discuss With KS Sir Start
if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'CollectPayment' || $_REQUEST['type'] == 'ConfirmDelivery') {
    $iServiceId =$eSystem=$UserType= "";
}
//Added By HJ On 07-02-2020 For Solved Issue Of Payment Report On Collect Payment As Per Discuss With KS Sir End
if ($iServiceId != "" || $eSystem == "DeliverAll" || $UserType == "Company") {
    if (file_exists("webservice_dl_shark_v1.php")) {
        require_once ('webservice_dl_shark_v1.php'); // applicable for DELIVERALL == Yes OR ONLYDELIVERALL == Yes
    } else {
        require_once ('include_webservice_shark_v1.php');
    }
} else {
    if (file_exists("include_webservice_shark_v1.php")) {
        require_once ('include_webservice_shark_v1.php');
    } else {
        require_once ('webservice_dl_shark_v1.php'); // applicable for ONLYDELIVERALL == Yes
    }
}
?>
