<?php
include_once('include_config.php');
include_once(TPATH_CLASS.'configuration.php');

/*$PickUpAddress = "New Patidar Furnishing, 16, Satyamev Royal Shopping B/H Swagat Rain Forest 3, Pramukhnagar Road, KH 0, Gandhinagar, Ellisbridge, Ahmedabad, Gujarat 382414, India";
$latitude = 23.022505;
$longitude = 72.571362;*/

/*$PickUpAddress = "Oberdorla, Germany";
$latitude = 51.1657;
$longitude = 10.4515; */

$PickUpAddress = "Flying K Ranch Airport, Independence, KS 67301, USA";
$latitude = 37.090240;
$longitude = -95.712891;


echo $UserTimeZoneDate = getPassengerTimeZoneDate($PickUpAddress,$latitude,$longitude,$scheduleDate = "");exit;
function getPassengerTimeZoneDate($PickUpAddress,$latitude,$longitude,$scheduleDate = ""){
   global $obj, $generalobj, $tconfig,$GOOGLE_SEVER_API_KEY_WEB,$vTimeZone;
   
   $UserTimeZone = "";
   $UserTimeZoneDate = @date("Y-m-d H:i:s");
   if($PickUpAddress != ""){
      $vAddress_arr = explode(",",$PickUpAddress);
      $vAddress = end($vAddress_arr);
      $vAddress = trim($vAddress);
      
      $sql = "SELECT vTimeZone FROM  `country` WHERE `vCountry` like '%$vAddress%' OR vCountryCodeISO_3 like '%$vAddress%'";
	    $db_sql = $obj->MySQLSelect($sql);     
      if(count($db_sql) == 1){
         $UserTimeZone = $db_sql[0]['vTimeZone'];
      }
   }
         
   if($UserTimeZone == ""){
      $UserTimeZone =  getlatlongTimeZone($latitude,$longitude);
   }
            
   if($UserTimeZone == ""){
      $UserTimeZone =  $vTimeZone;
   }
   
   if($scheduleDate == ""){
     $scheduleDate = @date("Y-m-d H:i:s");
   }
      
	 if($UserTimeZone != ""){
     $systemTimeZone = date_default_timezone_get();
     $UserTimeZoneDate = converToTz($scheduleDate,$UserTimeZone,$systemTimeZone);
   }
   
   return $UserTimeZoneDate;
}

//echo $timeZone = getlatlongTimeZone($latitude,$longitude); exit;
function getlatlongTimeZone($latitude,$longitude){
   global $obj, $generalobj, $tconfig,$GOOGLE_SEVER_API_KEY_WEB;
   
   $time = time();
   $url = "https://maps.googleapis.com/maps/api/timezone/json?location=$latitude,$longitude&timestamp=$time&key=".$GOOGLE_SEVER_API_KEY_WEB;
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $responseJson = curl_exec($ch);
   curl_close($ch);
   
   $response = json_decode($responseJson);  //echo "<pre>"; print_r($response);exit;
   //var_dump($response);
   $timeZone = $response->timeZoneId;
   $errorMessage = $response->errorMessage;
   if($errorMessage != ""){
     $timeZone = "";
   }

   return $timeZone;
}

function converToTz($time, $toTz, $fromTz,$dateFormat="Y-m-d H:i:s") {
		$date = new DateTime($time, new DateTimeZone($fromTz));
		$date->setTimezone(new DateTimeZone($toTz));
		$time = $date->format($dateFormat);
		return $time;
}

?>