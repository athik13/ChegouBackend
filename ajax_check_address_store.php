<?php

include_once("common.php");
$vLang = "EN";
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
}
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}
include_once ('include_generalFunctions_dl.php');
$usertTypeSesstion = "MANUAL_ORDER_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$chkusertype = check_user_mr();
$_SESSION[$usertTypeSesstion] = $chkusertype;
$iCompanyId = 0;
//echo "test";
//print_r($_SESSION);
$vLatitude = (!empty($_REQUEST['from_lat'])) ? $_REQUEST['from_lat'] : '';
$from_long = (!empty($_REQUEST['from_long'])) ? $_REQUEST['from_long'] : '';
$iUserAddressId = !empty($_REQUEST['iUserAddressId']) ? $_REQUEST['iUserAddressId'] : '';
if (isset($_REQUEST['iCompanyIds']) && !empty($_REQUEST['iCompanyIds'])) {
    $iCompanyId = $_REQUEST['iCompanyIds'];
} else if (!empty($_SESSION[$orderAddressIdSession]) && !empty($_SESSION[$usertTypeSesstion]) && strtolower($_SESSION[$usertTypeSesstion]) == 'store') {
    $iCompanyId = $_SESSION[$orderUserIdSession];
} else {
    $returnArr['Action'] = "1";
    $returnArr["message"] = '';
    echo json_encode($returnArr);
    exit;
}
$returnArr['Action'] = "1";
$returnArr["message"] = '';
if (isset($iCompanyId) && !empty($iCompanyId)) {
    if (strtolower($chkusertype) == 'store') {
        $sql = "select vRestuarantLocationLat,vRestuarantLocationLong,iServiceId from `company` where iCompanyId = '" . $iCompanyId . "'";
        $db_companydata = $obj->MySQLSelect($sql);
        $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
        $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
        $iServiceId = $db_companydata[0]['iServiceId'];
        $languageArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $distance = distanceByLocation($vLatitude, $from_long, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
        $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
        if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
            $returnArr['Action'] = "0";
            $returnArr["message"] = $languageArr['LBL_LOCATION_FAR_AWAY_TXT'];
        }
    } else if (strtolower($chkusertype) == 'rider' || strtolower($chkusertype) == 'user') {
        $sql = "select vRestuarantLocationLat,vRestuarantLocationLong,iServiceId from `company` where iCompanyId = '" . $iCompanyId . "'";
        $db_companydata = $obj->MySQLSelect($sql);
        $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
        $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
        $iServiceId = $db_companydata[0]['iServiceId'];
        $languageArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
        $distance = distanceByLocation($vLatitude, $from_long, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
        if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
            $returnArr['Action'] = "0";
            $returnArr["message"] = $languageArr['LBL_LOCATION_FAR_AWAY_TXT'];
            echo json_encode($returnArr);
            exit;
        }
    }
}
echo json_encode($returnArr);
exit;
?>
