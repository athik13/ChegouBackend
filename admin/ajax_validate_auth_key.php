<?php
include_once '../common.php';
include_once '../app_common_functions.php';
global $userObj;


 $sql = "select cn.vCountryCode,cn.vCountry,cn.tLatitude,cn.tLongitude from country cn inner join configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
 $db_con = $obj->MySQLSelect($sql);

 $vCountry = $db_con[0]['vCountryCode'];
 $tLatitude = $db_con[0]['tLatitude'];
 $tLongitude = $db_con[0]['tLongitude'];
 $session_token = "Passenger_4_7899765332757";
 $search_address = $db_con[0]['vCountry']; // Country Name
  $vAuthKey = $_REQUEST['vAuthKey']; // new auth key
  $vServiceAccountId = $_REQUEST['vServiceAccountId']; // service ID

   /* if($MAPS_API_REPLACEMENT_STRATEGY != "Advance"){ */  
		$returnValue = false;
		$language_code = $_SESSION['sess_lang'];
// =========autocomplete
			$search_address = str_replace(' ','+',$search_address);
			$params_autocomp = "?language_code=".$language_code."&search_query=".$search_address."&latitude=".$tLatitude."&longitude=".$tLongitude."&TSITE_DB=".TSITE_DB."&vServiceAccountId=".$vServiceAccountId."&vServiceAccountAuthKey=".$vAuthKey."&session_token=".$session_token."";
		$url_autocomplete = GOOGLE_API_REPLACEMENT_URL."autocomplete".$params_autocomp;
		// $response = json_encode(file_get_contents($url));
		$response_autocomp = json_decode(file_get_contents($url_autocomplete));		
		$response_count_auto = count($response_autocomp->data);
// =========geocode
			$params_geo_code = "?language_code=".$language_code."&latitude=".$tLatitude."&longitude=".$tLongitude."&TSITE_DB=".TSITE_DB."&vServiceAccountId=".$vServiceAccountId."&vServiceAccountAuthKey=".$vAuthKey."&session_token=".$session_token."";
		$url_geo_code = GOOGLE_API_REPLACEMENT_URL."reversegeocode".$params_geo_code;
		$response_geo_code = json_decode(file_get_contents($url_geo_code));		
		$response_count_geo_code = count($response_geo_code->address);
// =========direction
        $waypoint0 =  $tLatitude.",".$tLongitude;
		$waypoint1 = $tLatitude.",".$tLongitude;
			$params_direction = "?language_code=".$language_code."&source_latitude=".$tLatitude."&source_longitude=".$tLongitude."&dest_latitude=".$tLatitude."&dest_longitude=".$tLongitude."&TSITE_DB=".TSITE_DB."&vServiceAccountId=".$vServiceAccountId."&vServiceAccountAuthKey=".$vAuthKey."&session_token=".$session_token."&waypoint0=".$waypoint0."&waypoint1=".$waypoint1."";
		$url_direction = GOOGLE_API_REPLACEMENT_URL . "direction".$params_direction;
		$response_direction = json_decode(file_get_contents($url_direction));
		$response_count_direction = count($response_direction->data);
// =========check in all condition
		if($response_count_auto > 0 && $response_count_geo_code > 0 && $response_count_direction > 0){			
			$returnValue = true;
		}
		echo $returnValue;
		exit;
  /* }else{
	  echo $returnValue = true;
	  exit;
  } */
  
?>
