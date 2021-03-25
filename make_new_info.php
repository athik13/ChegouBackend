<?php
ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
ini_set('default_socket_timeout', 10);
ini_set('memory_limit', '-1');

@session_start();
$_SESSION['sess_hosttype'] = 'ufxall';
$inwebservice = "1";
$intervalmins = "86400";
//error_reporting(0);
//include_once('include_taxi_webservices.php');

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
include_once ('include_config.php');
include_once (TPATH_CLASS . 'configuration.php');

$generalConfigPaymentArr = $generalobj->getGeneralVarAll_Payment_Array();
require_once ('assets/libraries/stripe/config.php');
require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
require_once ('assets/libraries/pubnub/autoloader.php');
require_once ('assets/libraries/SocketCluster/autoload.php');
require_once ('assets/libraries/class.ExifCleaning.php');
include_once (TPATH_CLASS . 'Imagecrop.class.php');
include_once (TPATH_CLASS . 'twilio/Services/Twilio.php');
//include_once ('include_generalFunctions_shark.php');
include_once ('send_invoice_receipt.php');
include_once ('send_invoice_receipt_multi.php');


//$obj->sql_query("TRUNCATE TABLE `model_new`");

// Fetch Existing Data of countries
$existing_make_data_arr = $obj->MySQLSelect("SELECT * FROM `make_new` WHERE `eModelFound` = 'No'");

// Fetch timezones of countries
$data_make = file_get_contents("carmake/make_models.txt");

//$json_make_arr = json_decode($data_make, true, 512, JSON_UNESCAPED_UNICODE);
//$json_make_arr = $json_make_arr[0];
echo "<PRE>";
// print_r(json_last_error());exit;
// print_r($json_make_arr);exit;
/* foreach($json_make_arr as $json_make_arr_item){
	// print_r($json_make_arr_item);exit;
	$titleOfMake = $json_make_arr_item['name'];
	$matchFound = false;
	
	foreach($existing_make_data_arr as $existing_make_data_arr_item){
		$vMake = $existing_make_data_arr_item['vMake'];
		
		if(strtolower($titleOfMake) == strtolower($vMake)){
			$matchFound = true;
			break;
		}
	}
	
	if($matchFound == false){
		$data_make = array();
		$data_make['vMake'] = ucwords($titleOfMake);
		$id = $obj->MySQLQueryPerform("make_new", $data_make, 'insert');
	}
} */

/* foreach($existing_make_data_arr as $existing_make_data_arr_item){
	//print_r($existing_make_data_arr_item);exit;
	$vMake = $existing_make_data_arr_item['vMake'];
	$iMakeId = $existing_make_data_arr_item['iMakeId'];
	
	$where = " iMakeId = '" . $iMakeId . "'";
	$data_make = array();
	$data_make['vMake'] = ucwords(strtolower($vMake));
    $obj->MySQLQueryPerform("make_new", $data_make, 'update', $where);
} */


foreach($existing_make_data_arr as $existing_make_data_arr_item){
	//print_r($existing_make_data_arr_item);exit;
	$vMake = $existing_make_data_arr_item['vMake'];
	$iMakeId = $existing_make_data_arr_item['iMakeId'];
	
	$URlOfData = "https://vpic.nhtsa.dot.gov/api/vehicles/getmodelsformake/".rawurlencode($vMake)."?format=json";
	
	echo "vMake:: ".$vMake." :: URL: ".$URlOfData."<BR/><BR/>";
	
	$dataOfUrl = file_get_contents($URlOfData);
	$dataOfJsonFile = json_decode($dataOfUrl, true);
	
	
		$where = " iMakeId = '" . $iMakeId . "'";
		
		$data_make = array();
		
	if(!empty($dataOfJsonFile) && count($dataOfJsonFile) > 0 && $dataOfJsonFile['Count'] > 0 && !empty($dataOfJsonFile['Results']) && count($dataOfJsonFile['Results']) > 0){
		foreach($dataOfJsonFile['Results'] as $model_data_item){
			$modelDataArr = array();
			$modelDataArr['iMakeId'] = $iMakeId;
			$modelDataArr['vTitle'] = ucwords(strtolower($model_data_item['Model_Name']));
			$obj->MySQLQueryPerform("model_new", $modelDataArr, 'insert');
		}
		
		
		$data_make['eModelFound'] = 'Yes';
	}else{
	
		$data_make['eModelFound'] = 'No';
		
	}
		$obj->MySQLQueryPerform("make_new", $data_make, 'update', $where);
	// print_r($dataOfJsonFile);exit;
}


?>
