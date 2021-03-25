<?php
include_once("system_global_functions.php");

global $IS_DEBUG_MODE_CUSTOM;

function get_client_ip_temp() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// print_r(get_client_ip_temp());exit;
if (get_client_ip_temp() == "192.168.1.183") {
    $IS_DEBUG_MODE_CUSTOM = true;
}
include_once('configuration_variables.php');
// This Code For APP

/* * ************************ Language Code Setting ********************************* */
// $Lsql = "SELECT vCode,vTitle FROM language_master WHERE eDefault = 'Yes'";
$Lsql = "SELECT vCode,vGMapLangCode,vTitle, eDefault, eStatus, eDirectionCode,eDirectionCode as eType,vCurrencySymbol, vCurrencyCode  FROM language_master ORDER BY iDispOrder ASC";
$Data_ALL_langArr = $obj->MySQLSelect($Lsql);
$language_codes_arr =$active_language_codes_arr=$languageAssociateArr= array();
foreach ($Data_ALL_langArr as $language_item) {
    if (strtoupper($language_item['eDefault']) == "YES") {
        $vSystemDefaultLangCode = $language_item['vCode'];
        $vSystemDefaultLangName = $language_item['vTitle'];
        $vSystemDefaultLangDirection = $language_item['eDirectionCode'];
        $vSystemDefaultLangvGMapLangCode = $language_item['vGMapLangCode'];
    }
    $language_codes_arr[] = $language_item['vCode'];
    $languageAssociateArr[$language_item['vCode']] = $language_item;
    if ($language_item['eStatus'] == "Active") {
        $active_language_codes_arr[] = $language_item['vCode'];
    }
}

$vSystemDefaultLangCode = empty($vSystemDefaultLangCode) ? "EN" : $vSystemDefaultLangCode;
$vSystemDefaultLangName = empty($vSystemDefaultLangName) ? "English" : $vSystemDefaultLangName;
$vSystemDefaultLangDirection = empty($vSystemDefaultLangDirection) ? "ltr" : $vSystemDefaultLangDirection;
$vSystemDefaultLangvGMapLangCode = empty($vSystemDefaultLangvGMapLangCode) ? "en" : $vSystemDefaultLangvGMapLangCode;

$Data_langArr[0]['vCode'] = $vSystemDefaultLangCode;

if (isset($_SESSION['sess_lang']) && $_SESSION['sess_lang'] != "") {
    $langugaeCode = $_SESSION['sess_lang'];
}

if (empty($langugaeCode)) {
    $langugaeCode = isset($_REQUEST["vLang"]) ? ($_REQUEST["vLang"] == "" ? $Data_langArr[0]['vCode'] : $_REQUEST["vLang"]) : $Data_langArr[0]['vCode'];
}

if (empty($langugaeCode) || in_array_ci($langugaeCode, $active_language_codes_arr) == false) {
    $langugaeCode = $vSystemDefaultLangCode;
}

/* * ************************ Language Code Setting ********************************* */


/* * ************************ Currency Setting ********************************* */
$currency_sql = "SELECT * FROM currency ORDER BY iDispOrder ASC";
$Data_ALL_currency_Arr = $obj->MySQLSelect($currency_sql);
$active_currency_name_arr =$currency_arr=$currencyAssociateArr= array();
foreach ($Data_ALL_currency_Arr as $currency_item) {
    if (strtoupper($currency_item['eDefault']) == "YES") {
        $vSystemDefaultCurrencyName = $currency_item['vName'];
        $vSystemDefaultCurrencySymbol = $currency_item['vSymbol'];
        $vSystemDefaultCurrencyRatio = $currency_item['Ratio'];
    }
    $currency_arr[] = $currency_item;
    $currencyAssociateArr[$currency_item['vName']] = $currency_item;
    if ($currency_item['eStatus'] == "Active") {
        $active_currency_name_arr[] = $currency_item['vName'];
    }
}

$vSystemDefaultCurrencyName = empty($vSystemDefaultCurrencyName) ? "USD" : $vSystemDefaultCurrencyName;
$vSystemDefaultCurrencySymbol = empty($vSystemDefaultCurrencySymbol) ? "$" : $vSystemDefaultCurrencySymbol;
$vSystemDefaultCurrencyRatio = empty($vSystemDefaultCurrencyRatio) ? "1.00" : $vSystemDefaultCurrencyRatio;
/* * ************************ Currency Setting ********************************* */


$Ssql = "SELECT eType AS ispriceshow,iServiceId,vService, vServiceName_" . $langugaeCode . " as vServiceName,vImage,if(tDescription != '',JSON_UNQUOTE(json_extract(`tDescription`, '$.tDescription_EN')),'') AS tDescription FROM  `service_categories` WHERE iServiceId IN (" . $enablesevicescategory . ") AND eStatus='Active' order by iDisplayOrder ASC";
//echo $Ssql;die;
$ServiceData = $obj->MySQLSelect($Ssql);
// echo "<pre>"; print_r($enablesevicescategory); exit;
$serviceCategoriesTmp = array();
$serviceCategoriesIdsArrTmp = array();
$service_id_admin = -1;
if (!empty($ServiceData)) {
    foreach ($ServiceData as $key => $value) {
        if ($value['vImage'] != '') {
            $value['vImage'] = $tconfig["tsite_upload_service_categories_images"] . $value['vImage'];
        }
        $serviceCategoriesTmp[] = $value;
        $serviceCategoriesIdsArrTmp[] = $value['iServiceId'];

        if ($service_id_admin == -1 && $value['iServiceId'] > 1) {
            $service_id_admin = $value['iServiceId'];
        }
    }
}

$iServiceId = isset($_REQUEST["iServiceId"]) ? $_REQUEST["iServiceId"] : $ServiceData[0]['iServiceId'];
if (empty($_REQUEST["iServiceId"])) {
    $iServiceId = $ServiceData[0]['iServiceId'];
    $_REQUEST["iServiceId"] = $iServiceId;
}
define('serviceCategories', json_encode($serviceCategoriesTmp));
// End Code
//$host_system = "uberridedelivery4"; 
//Ride=cubetaxiplus   Ride+Delivery = uberridedelivery4,  Deliveryonly = uberdelivery4  
if ($hst_var == "ufxforall") {
    $host_system = "ufxforall";
} elseif ($hst_var == "deliveryonly") {
    $host_system = "uberdelivery4";
} elseif ($hst_var == "ridedelivery") {
    $host_system = "uberridedelivery4";
} elseif ($hst_var == "massage") {
    $host_system = "ufxforall";
} elseif ($hst_var == "doctor") {
    $host_system = "ufxforall";
} elseif ($hst_var == "beautician") {
    $host_system = "ufxforall";
} elseif ($hst_var == "carwash") {
    $host_system = "ufxforall";
} elseif ($hst_var == "dogwalking") {
    $host_system = "ufxforall";
} elseif ($hst_var == "towtruck_v4") {
    $host_system = "ufxforall";
} else {
    $host_system = "cubetaxiplus";
}

###### General label changes #################
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'company') {
    $query = "SELECT iServiceId FROM company WHERE iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
    $dbQueryData = $obj->MySQLSelect($query);
    if (count($dbQueryData) > 0) {
        $iServiceIdWeb = $dbQueryData[0]['iServiceId'];
    } else {
        $iServiceIdWeb = $ServiceData[0]['iServiceId'];
    }
} else {
    $iServiceIdWeb = $ServiceData[0]['iServiceId'];
}


if ($iServiceIdWeb != "0") {
    $sql = "SELECT vLabel,vValue,LanguageLabelId FROM language_label_" . $iServiceIdWeb . " WHERE vCode='" . $langugaeCode . "'";
    $db_lbl = $obj->MySQLSelect($sql);
    if (!empty($db_lbl)) {
        foreach ($db_lbl as $key => $value) {
            if (isset($_SESSION['sess_editingToken']) && $_SESSION['sess_editingToken'] == $db_config[0]['vValue']) {
                $langage_lbl[$value['vLabel']] = "<em class='label-dynmic'><i class='fa fa-edit label-i' data-id='" . $value['LanguageLabelId'] . "' data-value='main'></i>" . $value['vValue'] . "</em>";
            } else {
                $langage_lbl[$value['vLabel']] = $value['vValue'];
            }
        }
    }
}

if (empty($langage_lbl)) {
    $sql = "select vLabel,vValue,LanguageLabelId from language_label where vCode='" . $langugaeCode . "'";
    $db_lbl = $obj->MySQLSelect($sql);
    foreach ($db_lbl as $key => $value) {
        if (isset($_SESSION['sess_editingToken']) && $_SESSION['sess_editingToken'] == $db_config[0]['vValue']) {
            $langage_lbl[$value['vLabel']] = "<em class='label-dynmic'><i class='fa fa-edit label-i' data-id='" . $value['LanguageLabelId'] . "' data-value='other'></i>" . $value['vValue'] . "</em>";
        } else {
            $langage_lbl[$value['vLabel']] = $value['vValue'];
        }
    }

    $sql_en = "SELECT  `vLabel` , `vValue`  FROM  `language_label` WHERE  `vCode` = 'EN' UNION SELECT `vLabel` , `vValue`  FROM  `language_label_other` WHERE  `vCode` = 'EN'";
    $all_label_en = $obj->MySQLSelect($sql_en);
    if (count($all_label_en) > 0) {
        for ($i = 0; $i < count($all_label_en); $i++) {
            $vLabel_tmp = $all_label_en[$i]['vLabel'];
            $vValue_tmp = $all_label_en[$i]['vValue'];
            if (isset($langage_lbl[$vLabel_tmp]) || array_key_exists($vLabel_tmp, $langage_lbl)) {
                if ($langage_lbl[$vLabel_tmp] == "") {
                    $langage_lbl[$vLabel_tmp] = $vValue_tmp;
                }
            } else {
                $langage_lbl[$vLabel_tmp] = $vValue_tmp;
            }
        }
    }
}

if (!empty($_SESSION['sess_iAdminUserId'])) { //added bc in front side $langage_lbl_admin array given only en language value..and in many files use this array so do it like this
    if ($ServiceData[0]['iServiceId'] > 0) {

        if ($service_id_admin != -1) {
            $iServiceIdWeb = $service_id_admin;
        } else {
            $iServiceIdWeb = $ServiceData[0]['iServiceId'];
        }
        //$iServiceIdWeb = $ServiceData[0]['iServiceId'];

        $sql = "select vLabel,vValue from language_label_" . $iServiceIdWeb . " where vCode='EN'";
        $db_lbl_admin = $obj->MySQLSelect($sql);

        foreach ($db_lbl_admin as $key => $value) {
            $langage_lbl_admin[$value['vLabel']] = $value['vValue'];
        }
    }
} else {
    $langage_lbl_admin = $langage_lbl;
}
//print_R($langage_lbl_admin); exit;
if (isset($_REQUEST['debug'])) {

    $_REQUEST['debug'] = empty($_REQUEST['debug']) ? E_ALL : $_REQUEST['debug'];
    ini_set('display_errors', 'On');
    ini_set('error_reporting', 1);
    error_reporting($_REQUEST['debug']);
}

/* include_once(dirname(dirname(dirname(__FILE__))).'/admin/library/helper.php');
  include_once(dirname(dirname(dirname(__FILE__))).'/admin/library/User.php');

  $userObj = new Admin\library\User(); */

$exclude_login = [
    'index.php',
    'ajax_login_action.php'
];

/* * **** Build Country Data ******** */
$sql_country_data_retrieve = "SELECT * FROM country";
//echo $Ssql;die;
$country_data_retrieve = $obj->MySQLSelect($sql_country_data_retrieve);
$country_data_arr =$countryAssociateArr= array();

foreach ($country_data_retrieve as $country_data_retrieve_item) {
    $country_data_arr[$country_data_retrieve_item['vCountryCode']] = $country_data_retrieve_item;
    $countryAssociateArr[$country_data_retrieve_item['iCountryId']] = $country_data_retrieve_item;
}
/* * **** Build Country Data ******** */
//include('member_session_validate.php');
?>