<?php

include_once('../../common.php');
if (!isset($generalobjRider)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjRider = new General_admin();
}
ini_set("display_errors", 1);
error_reporting(E_ALL);
$generalobjRider->check_member_login();
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$iAdvertBannerId = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iAdvertBannerId = isset($_REQUEST['iAdvertBannerId']) ? $_REQUEST['iAdvertBannerId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//echo "<pre>";
//print_r($_REQUEST);die;
//Start make deleted
$tableName = "banner_impression";
$redirectUrl = $tconfig["tsite_url_main_admin"] . "banner_impression.php?" . $parameters; 
?>