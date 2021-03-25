<?php

include_once("include/config.php");
//Added By HJ On 30-04-2020 As Per Discuss With KS For Hide My earning Left Menu When eSystem = Deliverall Start
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//echo "<pre>";print_r($_SESSION['sess_user']);die;
$myearnigMenuHide = 1; // 0- Hide,1- Show
if (isset($_SESSION['sess_user']) && strtoupper($_SESSION['sess_user']) == "DRIVER") {
    $driverStoreId = 0;
    if (isset($_SESSION['sess_iCompanyId']) && $_SESSION['sess_iCompanyId'] > 0) {
        $driverStoreId = $_SESSION['sess_iCompanyId'];
    } else if (isset($_SESSION['sess_iUserId']) && $_SESSION['sess_iUserId'] > 0) {
        $storeDriverId = $_SESSION['sess_iUserId'];
        $getDriver = $obj->MySQLSelect("SELECT iCompanyId FROM register_driver WHERE iDriverId = '" . $storeDriverId . "'");
        if (count($getDriver) > 0) {
            $driverStoreId = $getDriver[0]['iCompanyId'];
        }
    }
    if ($driverStoreId > 0) {
        $getStore = $obj->MySQLSelect("SELECT eSystem FROM company WHERE iCompanyId = '" . $driverStoreId . "'");
        if (isset($getStore[0]['eSystem']) && strtoupper($getStore[0]['eSystem']) == "DELIVERALL") {
            $myearnigMenuHide = 0; // 0- Hide,1- Show
        }
    }
}
//Added By HJ On 30-04-2020 As Per Discuss With KS For Hide My earning Left Menu When eSystem = Deliverall End
include($templatePath . "top/left_menu.php");
?>