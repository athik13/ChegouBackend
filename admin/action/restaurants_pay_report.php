<?php
include_once('../../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$ePayRestaurant = isset($_REQUEST['ePayRestaurant']) ? $_REQUEST['ePayRestaurant'] : '';

if($action == "pay_restaurant" && $_REQUEST['ePayRestaurant'] == "Yes"){
	if(SITE_TYPE !='Demo'){
		foreach($_REQUEST['iCompanyId'] as $ids) {
			$sql1 = " UPDATE orders set eRestaurantPaymentStatus = 'Settelled'
			WHERE iCompanyId = '".$ids."' AND eRestaurantPaymentStatus='Unsettelled' $ssql";
			$obj->sql_query($sql1);
		}
		$_SESSION['success'] = '1';
		$_SESSION['var_msg'] = 'Record(s) mark as settlled successful.'; 
	}else {
		$_SESSION['success'] = '2';
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."restaurants_pay_report.php?".$parameters); exit;
}
?>