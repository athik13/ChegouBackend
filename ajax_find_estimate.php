<?php
include_once("common.php");
include_once ('include_generalFunctions_shark.php');
include_once ('app_common_functions.php');
if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
    include_once ('include/include_webservice_enterprisefeatures.php');
}
global $generalobj;

$dist_fare = isset($_REQUEST['dist_fare']) ? $_REQUEST['dist_fare'] : '';
$time_fare = isset($_REQUEST['time_fare']) ? $_REQUEST['time_fare'] : '';
$fromLoc = isset($_REQUEST['fromLoc']) ? $_REQUEST['fromLoc'] : '';
$from_lat = isset($_REQUEST['from_lat']) ? $_REQUEST['from_lat'] : '';
$from_long = isset($_REQUEST['from_long']) ? $_REQUEST['from_long'] : '';
$to_lat = isset($_REQUEST['to_lat']) ? $_REQUEST['to_lat'] : '';
$to_long = isset($_REQUEST['to_long']) ? $_REQUEST['to_long'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$estimete = isset($_REQUEST['estimete']) ? $_REQUEST['estimete'] : '';
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId']) ? $_REQUEST['iVehicleTypeId'] : '';
$iUserId = 0;
$userType = "";
$default_lang = $_SESSION['sess_lang'];
if ($default_lang == "") {
    $default_lang = "EN";
}
$langcodefront = $default_lang;

// added for general function condition 06-01-2020
$DisplayFrontEstimate = "Yes";
function mediaTimeDeFormater_old($seconds) {
    $ret = "";
    $hours = (string) floor($seconds / 3600);
    $secs = (string) $seconds % 60;
    $mins = (string) floor(($seconds - ($hours * 3600)) / 60);
    if (strlen($hours) == 1)
        $hours = "0" . $hours;
    if (strlen($secs) == 1)
        $secs = "0" . $secs;
    if (strlen($mins) == 1)
        $mins = "0" . $mins;

    if ($hours == 0) {
        $mint = "";
        $secondss = "";
        if ($mins > 01) {
            $mint = "$mins Mins";
        } else {
            $mint = "$mins Min";
        }
        if ($secs > 01) {
            $secondss = "$secs Seconds";
        } else {
            $secondss = "$secs Second";
        }
        $ret = "$mint $secondss";
    } else {
        $mint = "";
        $secondss = "";
        if ($mins > 01) {
            $mint = "$mins Mins";
        } else {
            $mint = "$mins Min";
        }
        if ($secs > 01) {
            $secondss = "$secs Seconds";
        } else {
            $secondss = "$secs Second";
        }
        if ($hours > 01) {
            $ret = "$hours Hrs $mint"; // $secondss
        } else {
            $ret = "$hours hr $mint"; //$secondss
        }
    }
    return $ret;
}

function get_currency_old($from_Currency, $to_Currency, $amount) {
    $forignalamount = $amount;
    $amount = urlencode($amount);
    $from_Currency = urlencode($from_Currency);
    $to_Currency = urlencode($to_Currency);
    //$url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
    $url = "https://finance.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
    $ch = curl_init();
    $timeout = 0;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt ($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $rawdata = curl_exec($ch);
    curl_close($ch);
    $data = explode('bld>', $rawdata);
    $data = explode($to_Currency, $data[1]);
    $ftollprice = round($data[0], 2);
    if ($ftollprice == 0 || $ftollprice == 0.00) {
        $ftollprice = $amount;
    }
    //return round($data[0], 2);
    return $ftollprice;
}

$countrydata = $generalobj->fetch_address_geocode($fromLoc);
$db_con = $obj->MySQLSelect("select cn.vCountryCode,cn.vCountry,cn.vPhoneCode,cn.vTimeZone from country cn inner join configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'");
//print_r($db_con);die;
$vCountry = "Thailand";
$vTimeZone = "Asia/Bangkok";
$vPhoneCode = 66;
$vCountryCode = "TH";
if (count($db_con) > 0) {
    $vTimeZone = $db_con[0]['vTimeZone'];
    $vCountry =$vCountryCode= $db_con[0]['vCountryCode'];
    $address = $db_con[0]['vCountry']; // Google HQ
    $vPhoneCode = $db_con[0]['vPhoneCode'];
}
$countryCodeAdmin = $vCountryCode;
//Added By HJ On 09-09-2019 For Get Configuration Data By One Query Start
$configData = $obj->MySQLSelect("SELECT vName,vValue FROM configurations WHERE vName IN('ENABLE_TOLL_COST','TOLL_COST_APP_ID','TOLL_COST_APP_CODE','APPLY_SURGE_ON_FLAT_FARE')");
for ($c = 0; $c < count($configData); $c++) {
    $configArr[$configData[$c]['vName']] = $configData[$c]['vValue'];
}
foreach ($configArr as $key => $val) {
    $$key = $val;
}
//echo $ENABLE_TOLL_COST . "==" . $TOLL_COST_APP_ID . "==" . $TOLL_COST_APP_CODE . "==" . $APPLY_SURGE_ON_FLAT_FARE;die;
//Added By HJ On 09-09-2019 For Get Configuration Data By One Query End
//Commented By HJ On 09-09-2019 For Get Configuration Data By One Query Start
/* $ENABLE_TOLL_COST = $generalobj->getConfigurations("configurations", "ENABLE_TOLL_COST");
  $TOLL_COST_APP_ID = $generalobj->getConfigurations("configurations", "TOLL_COST_APP_ID");
  $TOLL_COST_APP_CODE = $generalobj->getConfigurations("configurations", "TOLL_COST_APP_CODE");
  $APPLY_SURGE_ON_FLAT_FARE = $generalobj->getConfigurations("configurations", "APPLY_SURGE_ON_FLAT_FARE"); */
//Commented By HJ On 09-09-2019 For Get Configuration Data By One Query End
$db_currency = $obj->MySQLSelect("select vName from currency where eStatus='Active' AND eDefault='Yes'");
$priceRatio = 1;
$db_country = array();
$pickuplocationarr = array($from_lat, $from_long);
$dropofflocationarr = array($to_lat, $to_long);
$isDestinationAdded = "Yes";
$eFlatTrip = "No";
$fFlatTripPrice = 0;
if ($dist_fare != '' && $time_fare != "" && $estimete == "single") {
    if (!empty($pickuplocationarr) && !empty($dropofflocationarr)) {
        $data_flattrip = checkFlatTripnew($pickuplocationarr, $dropofflocationarr, $iVehicleTypeId);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    }
    $Fare_data = calculateFareEstimateAll($time_fare, $dist_fare, $iVehicleTypeId, $iUserId, 1, "", "", "", 1, 0, 0, 0, "DisplySingleVehicleFare", $userType, 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $pickuplocationarr, $dropofflocationarr, "", $eType, "");

    for ($r = 0; $r < count($Fare_data); $r++) {
        foreach ($Fare_data[$r] as $key => $val) {
            if ($key == "total_fare_amount" || $key == "eDisplaySeperator") {
                
            } else {
                $fareArr = array();
                $fareArr['key'] = $key;
                $fareArr['value'] = $val;
                $estimateArr[] = $fareArr;
            }
            if ($key == $subtotalLbl) {
                $totalFare = $val;
            }
	  if($key=='Subtotal') {
		
	  }
        }
    }
    $cont_vehicle = "<div id='content_" . $iVehicleTypeId . "' style='vertical-align:top; margin:0px; padding:0px; float:left; width:100%;' title='Click to close.' >
						<!-- <b title='Close'>X</b> -->
						<h3 align='center' style='vertical-align:top; margin:0px; padding:0px; float:left; width:100%; text-align:center;margin-top: -30px;font-weight:500;'>" . $langage_lbl['LBL_FARE_ESTIMATION_TXT'] . "</h3>
						<div class='demo1'>
						<table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'>";
    for ($g = 0; $g < count($estimateArr); $g++) {

        $cont_vehicle .= "<tr>
								<td>" . $estimateArr[$g]['key'] . "</td><td style='width:2%'></td>
								<td>" . $estimateArr[$g]['value'] . "</td>
							</tr>
							<tr>
								<td colspan='3'><hr/></td>
							</tr>";
							
							if($estimateArr[$g]['key']=='Subtotal') {
								
								$rounding_arr = getRoundingOffAmount($estimateArr[$g]['value'],'USD');
								
								if(!empty($rounding_arr) && $rounding_arr['differenceValue'] != 0) {
									if($rounding_arr['method']=='Addition') {
									    $differenceValue = $rounding_arr['differenceValue'];
									    
									} else {
									    $differenceValue = '-'.$rounding_arr['differenceValue'];
									}
									if ($eFlatTrip == 'Yes' && $fFlatTripPrice > 0) {
									    $total_fare_fixfare = $rounding_arr['finalFareValue'];
									} else {
									    $total_fare = $rounding_arr['finalFareValue'];    
									}
				
									$cont_vehicle .= "<tr>
									<td>" . $estimateArr[$g]['key'] . "</td><td style='width:2%'></td>
									<td>" . $total_fare . "</td>
									</tr>
									<tr>
										<td colspan='3'><hr/></td>
									</tr>";
								}
							}
    }

    $cont_vehicle .= "</table>
					</div>
				</div>";
    echo $cont_vehicle;
    die;
} else {
    $priceRatio = 1;
    $db_country = array();
    $pickuplocationarr = array($from_lat, $from_long);
    $dropofflocationarr = array($to_lat, $to_long);
    $isDestinationAdded = "Yes";
    $eFlatTrip = "No";
    $fFlatTripPrice = 0;
    
    $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $vSymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    
    if (!empty($pickuplocationarr) && !empty($dropofflocationarr)) {
      $data_flattrip = $generalobj->checkFlatTripnew($pickuplocationarr, $dropofflocationarr, $iVehicleTypeId);
      $eFlatTrip = $data_flattrip['eFlatTrip'];
      $fFlatTripPrice = $data_flattrip['Flatfare'];
      //$total_fare_fixfare = $fFlatTripPrice;
      } 
    $Fare_data = calculateFareEstimateAll($time_fare, $dist_fare, 0, $iUserId, 1, "", "", "", 1, 0, 0, 0, "", $userType, 1, "", $isDestinationAdded, $eFlatTrip, $fFlatTripPrice, $pickuplocationarr, $dropofflocationarr, "", $eType, "");
    
    $cont = '';
    if (count($Fare_data) > 0) {
        $cont .= '<ul>';
        for ($f = 0; $f < count($Fare_data); $f++) {
            //print_r($Fare_data[$f]);die;
            $vVehicleType = $Fare_data[$f]['vVehicleType_' . $default_lang];
            $iVehicleTypeId = $Fare_data[$f]['iVehicleTypeId'];
            $total_fare = $Fare_data[$f]['total_fare'];
	  
	$rounding_arr = getRoundingOffAmount($Fare_data[$f]['FinalFare'],'USD');
								
	if(!empty($rounding_arr) && $rounding_arr['differenceValue'] != 0) {
		if($rounding_arr['method']=='Addition') {
		    $differenceValue = $rounding_arr['differenceValue'];
		    
		} else {
		    $differenceValue = '-'.$rounding_arr['differenceValue'];
		}
		if ($eFlatTrip == 'Yes' && $fFlatTripPrice > 0) {
		    $total_fare_fixfare = $rounding_arr['finalFareValue'];
		} else {
		    $total_fare = $rounding_arr['finalFareValue'];    
		}
		$total_fare = $vSymbol." ".$total_fare;
	}
	
            $cont .= '<li ><label>' . $vVehicleType . '<img src="assets/img/question-icon.jpg" id="tooltip_' . $iVehicleTypeId . '" data-id="' . trim($iVehicleTypeId) . '" data-time="' . $time_fare . '" data-dist="' . $dist_fare . '" data=etype="' . $eType . '" onclick="getVehicleFare(this);" alt="" <!--title="' . $langage_lbl['LBL_APPROX_DISTANCE_TXT'] . ' ' . $langage_lbl['LBL_FARE_ESTIMATE_TXT'] . '-->"><b>' . $total_fare . '</b></label></li>';
        }
    } else {
        $cont .= "<h4>" . $langage_lbl['LBL_SORRY_NO_VEHICLES'] . "</h4>";
    }
    echo $cont;
    exit;
}
?>
