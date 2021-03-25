<?php

include_once('../../common.php');
if (!isset($generalobjRider)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjRider = new General_admin();
}
$generalobjRider->check_member_login();
 
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : '';
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
$tpages = isset($_REQUEST['tpages']) ? $_REQUEST['tpages'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

?>