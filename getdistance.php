<?
include_once('common.php');
//include_once('generalFunctions.php');
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

$data= checkSurgePrice("2","","0");
echo "<pre>";print_r($data);exit;
function checkSurgePrice($vehicleTypeID, $selectedDateTime = "",$iRentalPackageId = "0") {
  global $ENABLE_SURGE_CHARGE_RENTAL;
  
  $ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
  $eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
  
  $fPickUpPrice = 1;
  $fNightPrice = 1;
  
  if ($selectedDateTime == "") {
    $currentDateTime = @date("Y-m-d H:i:s");
    $currentTime = @date("H:i:s");
    $currentDay = @date("D");     
                                               
    $PreviousDayDate=@date('Y-m-d', strtotime('-1 day'));
    $PreviousDay=@date('D', strtotime($PreviousDayDate));  
  } else {
    // $currentTime = $selectedDateTime;    
    $PreviousDayDate=@date('Y-m-d', strtotime($selectedDateTime. '-1 day'));
    $PreviousDay=@date('D', strtotime($PreviousDayDate));
    
    $currentTime = @date("H:i:s", strtotime($selectedDateTime));
    $currentDay = @date("D", strtotime($selectedDateTime));
  }    
  echo $currentTime;     
  ## Checking For Previous Day NightSurge Charge For 0-5 am ##
  if($currentTime > "09:00:00" &&  $currentTime < "23:00:00" && $eNightStatus == "Active" && ($iRentalPackageId == 0 || ($iRentalPackageId != 0 && $ENABLE_SURGE_CHARGE_RENTAL == "Yes"))){
    $previousnightStartTime_str = "t" . $PreviousDay . "NightStartTime";
    $previousnightEndTime_str = "t" . $PreviousDay . "NightEndTime";
    $fpreviousNightPrice_str = "f" . $PreviousDay . "NightPrice";
    $tNightSurgeData_PrevDay = get_value('vehicle_type', 'tNightSurgeData', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $tNightSurgeDataPrevDayArr = json_decode($tNightSurgeData_PrevDay, true); //  echo "<pre>";print_r($tNightSurgeDataPrevDayArr);exit;
    if(count($tNightSurgeDataPrevDayArr) > 0){
        $nightStartTime_PrevDay = $tNightSurgeDataPrevDayArr[$previousnightStartTime_str];
        $nightEndTime_PrevDay = $tNightSurgeDataPrevDayArr[$previousnightEndTime_str];
        $fNightPrice_PrevDay = $tNightSurgeDataPrevDayArr[$fpreviousNightPrice_str];
        if($currentTime > $nightStartTime_PrevDay &&  $currentTime < $nightEndTime_PrevDay){
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
            $returnArr['SurgePrice'] = $fNightPrice_PrevDay . "X";
            $returnArr['SurgePriceValue'] = $fNightPrice_PrevDay;
            return $returnArr;
        } 
    }
  }  
  ## Checking For Previous Day NightSurge Charge For 0-5 am ##
       
  /* added for rental */
  
  if (($ePickStatus == "Active" || $eNightStatus == "Active") && ($iRentalPackageId == 0 || ($iRentalPackageId != 0 && $ENABLE_SURGE_CHARGE_RENTAL == "Yes"))) {
  
    $startTime_str = "t" . $currentDay . "PickStartTime";
    $endTime_str = "t" . $currentDay . "PickEndTime";
    $price_str = "f" . $currentDay . "PickUpPrice";
    
    $pickStartTime = get_value('vehicle_type', $startTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $pickEndTime = get_value('vehicle_type', $endTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $fPickUpPrice = get_value('vehicle_type', $price_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    
    /*$nightStartTime = get_value('vehicle_type', 'tNightStartTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $nightEndTime = get_value('vehicle_type', 'tNightEndTime', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $fNightPrice = get_value('vehicle_type', 'fNightPrice', 'iVehicleTypeId', $vehicleTypeID, '', 'true');  */
    $tNightSurgeData = get_value('vehicle_type', 'tNightSurgeData', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
    $tNightSurgeDataArr = json_decode($tNightSurgeData, true); 
    $nightStartTime_str = "t" . $currentDay . "NightStartTime";
    $nightEndTime_str = "t" . $currentDay . "NightEndTime";
    $fNightPrice_str = "f" . $currentDay . "NightPrice"; 
    $nightStartTime = $tNightSurgeDataArr[$nightStartTime_str];
    $nightEndTime = $tNightSurgeDataArr[$nightEndTime_str];
    $fNightPrice = $tNightSurgeDataArr[$fNightPrice_str];
    //echo "<pre>";print_r($tNightSurgeDataArr);exit;
    
    $tempNightHour = "12:00:00";
    if ($currentTime > $pickStartTime && $currentTime < $pickEndTime && $ePickStatus == "Active") {
    
      $returnArr['Action'] = "0";
      $returnArr['message'] = "LBL_PICK_SURGE_NOTE";
      $returnArr['SurgePrice'] = $fPickUpPrice . "X";
      $returnArr['SurgePriceValue'] = $fPickUpPrice;
    } 
    // else if ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $eNightStatus == "Active") {
    else if((($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime > $tempNightHour) || ($currentTime <$nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime > $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour)) && $eNightStatus == "Active"){
    
      $returnArr['Action'] = "0";
      $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
      $returnArr['SurgePrice'] = $fNightPrice . "X";
      $returnArr['SurgePriceValue'] = $fNightPrice;
    } else {
      $returnArr['Action'] = "1";
    }
  } else {
      $returnArr['Action'] = "1";
  }
  
  return $returnArr;
}

function get_value($table, $field_name, $condition_field = '', $condition_value = '', $setParams = '', $directValue = '') {
    global $obj;
    $returnValue = array();
    
    $where = ($condition_field != '') ? ' WHERE ' . clean($condition_field) : '';
    $where .= ($where != '' && $condition_value != '') ? ' = "' . clean($condition_value) . '"' : '';
        
    if ($table != '' && $field_name != '' && $where != '') {
      $sql = "SELECT $field_name FROM  $table $where";
      if ($setParams != '') {
        $sql .= $setParams;
      }  
      $returnValue = $obj->MySQLSelect($sql);
    } else if ($table != '' && $field_name != '') {
      $sql = "SELECT $field_name FROM  $table";
      if ($setParams != '') {
        $sql .= $setParams;
      }    
      $returnValue = $obj->MySQLSelect($sql);
    }
    if ($directValue == '') {
      return $returnValue;
    } else {
      $temp = $returnValue[0][$field_name];
      return $temp;
    }
}


	function clean($str) {
		global $obj;  
		$str = trim($str);
		//$str = mysqli_real_escape_string($str);
    	$str = $obj->SqlEscapeString($str);
		$str = htmlspecialchars($str);
		$str = strip_tags($str);
		return($str);
	}


echo $finalamt = get_user_available_balance_app_display("7", "Rider");exit;
function get_user_available_balance_app_display($sess_iMemberId, $type) {
        global $obj;
                
		// echo "sss";exit;
       if ($type == "Rider") {
         $sqld = "SELECT ru.vCurrencyPassenger as vCurrency,cu.vSymbol FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '".$sess_iMemberId."'";
       }else{
         $sqld = "SELECT rd.vCurrencyDriver as vCurrency,cu.vSymbol FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '".$sess_iMemberId."'";
       }
          $db_currency = $obj->MySQLSelect($sqld);
       $vCurrency = $db_currency[0]['vCurrency'];
       $vSymbol = $db_currency[0]['vSymbol'];
       
       
       if($vCurrency == "" || $vCurrency == NULL) {
    			$sql = "SELECT vName,vSymbol from currency WHERE eDefault = 'Yes'";
    		  $currencyData = $obj->MySQLSelect($sql);
          $vCurrency = $currencyData[0]['vName'];
          $vSymbol = $currencyData[0]['vSymbol'];
    	 }
    
       $balance = 0;
       $sql = "SELECT SUM(iBalance*fRatio_".$vCurrency.") as totcredit FROM user_wallet WHERE iUserId = '" . $sess_iMemberId . "' AND eUserType = '" . $type . "' AND eType = 'Credit'";
       $db_credit_balance = $obj->MySQLSelect($sql);

       $sql = "SELECT SUM(iBalance*fRatio_".$vCurrency.") as totdebit FROM user_wallet WHERE iUserId = '" . $sess_iMemberId . "' AND eUserType = '" . $type . "' AND eType = 'Debit'";
       $db_debit_balance = $obj->MySQLSelect($sql);

       $balance = $db_credit_balance[0]['totcredit'] - $db_debit_balance[0]['totdebit'];
       
       if($balance == 0) {
            $finalamt = $vSymbol . " 0.00";
       } else {
            $finalamt = $vSymbol . ' ' . number_format($balance, 2, '.', '');
        }


       return $finalamt;
    }

ChangeDriverVehicleRideDeliveryFeatureDisable("1");
########################### Check Ride Delivery Feature Enable ##############################################
function getGeneralVarAll_IconBanner() {
        global $obj,$APP_TYPE;
        //$listField = $obj->MySQLGetFieldsQuery("setting");
        $ssql = "";
        /*if(ENABLE_RENTAL_OPTION == 'No') {
           $ssql .= " AND eRentalType = 'No' ";
        }*/
        $wri_usql = "SELECT iSettingId,vName,TRIM(vValue) as vValue,eImageType,eRentalType FROM configurations_cubejek where 1".$ssql;
        $wri_ures = $obj->MySQLSelect($wri_usql);

        return $wri_ures; 
}


function ChangeDriverVehicleRideDeliveryFeatureDisable($iDriverId) {
        global $obj,$APP_TYPE,$generalobj;
        $eShowRideVehicles = "Yes";
        $eShowDeliveryVehicles = "Yes";
                  
        $sqldata = "SELECT iTripId FROM `trips` WHERE ( iActive='On Going Trip' OR iActive='Active' ) AND iDriverId='" .$iDriverId. "'";
		    $TripData = $obj->MySQLSelect($sqldata);
        $TripRunCount = count($TripData); 
                      
        if($APP_TYPE = "Ride-Delivery-UberX" && $TripRunCount == 0){
           $RideDeliveryIconArr = getGeneralVarAll_IconBanner();
           for($i=0;$i<count($RideDeliveryIconArr);$i++){
              $vName = $RideDeliveryIconArr[$i]['vName'];
              $vValue = $RideDeliveryIconArr[$i]['vValue'];
              $$vName = $vValue;
              $Data[0][$vName] = $$vName;   
      		 }
           //echo "<pre>";print_r($Data);exit;
           if($Data[0]['RIDE_SHOW_SELECTION'] == 'None' && $Data[0]['RENTAL_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_RIDE_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_RENTAL_SHOW_SELECTION'] == 'None'){
               $eShowRideVehicles = "No";
               $sql = "SELECT eType FROM `driver_vehicle` as dv LEFT JOIN register_driver as rd ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.iDriverId='" .$iDriverId. "'";
		           $DriverVehicleType = $obj->MySQLSelect($sql);
               $eType = $DriverVehicleType[0]['eType']; 
               if($eType == "Ride"){
                  $sql = "UPDATE register_driver set iDriverVehicleId='0' WHERE iDriverId='".$iDriverId."'";
    					    $obj->sql_query($sql);
               }
           }
           if($Data[0]['DELIVERY_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_DELIVERY_SHOW_SELECTION'] == 'None'){
               $eShowDeliveryVehicles = "No";
               $sql = "SELECT eType FROM `driver_vehicle` as dv LEFT JOIN register_driver as rd ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.iDriverId='" .$iDriverId. "'";
		           $DriverVehicleType = $obj->MySQLSelect($sql);
               $eType = $DriverVehicleType[0]['eType']; 
               if($eType == "Delivery"){
                  $sql = "UPDATE register_driver set iDriverVehicleId='0' WHERE iDriverId='".$iDriverId."'";
    					    $obj->sql_query($sql);
               }
           }
        }
        
        return $iDriverId;
}
########################### Check Ride Delivery Feature Enable ##############################################
echo "end";exit;

$Source_point_Address = array("23.0734262","72.6243823");
//checkDriverAirpotLocation($Source_point_Address,"31");
function checkDriverAirpotLocation($Source_point_Address, $iDriverId) {
	global $generalobj,$obj;
	$returnArr = array();
	/*$sql = "SELECT ls.fFlatfare,lm1.vLocationName as vFromname,lm2.vLocationName as vToname, lm1.tLatitude as fromlat, lm1.tLongitude as fromlong, lm2.tLatitude as tolat, lm2.tLongitude as tolong FROM `location_wise_fare` ls left join location_master lm1 on ls.iToLocationId = lm1.iLocationId left join location_master lm2 on ls.iFromLocationId = lm2.iLocationId  UNION ALL
          SELECT ls.fFlatfare,lm1.vLocationName as vToname,lm2.vLocationName as vFromname, lm1.tLatitude as tolat, lm1.tLongitude as tolong, lm2.tLatitude as fromlat, lm2.tLongitude as fromlong FROM `location_wise_fare` ls left join location_master lm1 on ls.iFromLocationId = lm1.iLocationId left join location_master lm2 on ls.iToLocationId = lm2.iLocationId
          WHERE lm1.eFor = 'FixFare' and lm1.eStatus = 'Active'";*/
  $sql = "SELECT lm1.iLocationId,lm1.vLocationName as vFromname,lm1.tLatitude as fromlat,lm1.tLongitude as fromlong FROM location_master lm1 WHERE lm1.eFor = 'Airport' AND lm1.eStatus = 'Active'";        
	$location_data = $obj->MySQLSelect($sql);
	//echo"<pre>";
	//print_r($location_data);die;
	$polygon = array();
	foreach ($location_data as $key => $value) {
	$fromlat = explode(",",$value['fromlat']);
	$fromlong = explode(",",$value['fromlong']);
		for ($x = 0; $x < count($fromlat); $x++) {
			if(!empty($fromlat[$x]) || !empty($fromlong[$x])) {
				$from_polygon[$key][] = array($fromlat[$x],$fromlong[$x]);
			}
		}	
		if(!empty($Source_point_Address)) {
			if(!empty($from_polygon[$key])) {
/*				print_r($from_polygon[$key]);
				echo"<br/>";*/
				$from_source_addresss = contains($Source_point_Address,$from_polygon[$key]) ? 'IN' : 'OUT';
				if($from_source_addresss == "IN") {
					$returnArr['iLocationId']=$location_data[$key]['iLocationId'];
					$returnArr['vFromname'] = $location_data[$key]['vFromname'];
					//return $returnArr;
				}
			}
		}
	} 
	if(empty($returnArr)) {
		$returnArr['iLocationId']=0;
		$returnArr['vFromname']="";
	}	
	echo "<pre>";print_r($returnArr);die;
	return $returnArr;
}


$milliseconds = time();
$number = 1021;
$len = 4-strlen($number);
$newstring = substr($milliseconds,0,$len);
echo $newstring = $number.$newstring;
$str .= 'pr' . $newstring;
exit;


//getVehicleCountryUnit_PricePerKm1("93","2");
function getVehicleCountryUnit_PricePerKm1($vehicleTypeID,$fPricePerKM){
    global $generalobj,$obj;
    
    $iCountryId = get_value("vehicle_type", "iCountryId", "iVehicleTypeId", $vehicleTypeID, '', 'true');
    if($iCountryId == "-1"){
       $eUnit = $generalobj->getConfigurations("configurations","DEFAULT_DISTANCE_UNIT");
    }else{
       $eUnit = get_value("country", "eUnit", "iCountryId", $iCountryId, '', 'true');
    }
    
    if($eUnit == "" || $eUnit == NULL){
        $eUnit = $generalobj->getConfigurations("configurations","DEFAULT_DISTANCE_UNIT");
    }
    
    if($eUnit == "Miles"){
       $PricePerKM = $fPricePerKM * 1.60934; 
    }else{
       $PricePerKM = $fPricePerKM;
    }
    echo $PricePerKM;exit;
    return  $PricePerKM;
    
}

//getMemberCountryUnit1("17");
function getMemberCountryUnit1($iMemberId,$UserType="Passenger"){
    global $generalobj,$obj;
                    
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $vCountryfield = "vCountry";
        $iUserId = "iUserId";
    } else {
        $tblname = "register_driver";
        $vCountryfield = "vCountry";
        $iUserId = "iDriverId";
    }        
    $vCountry = get_value($tblname, $vCountryfield, $iUserId, $iMemberId, '', 'true');    
               
    if($vCountry == "" || $vCountry == NULL){
        $vCountryCode = $generalobj->getConfigurations("configurations","DEFAULT_DISTANCE_UNIT");
    }else{
        $vCountryCode = get_value("country", "eUnit", "vCountryCode", $vCountry, '', 'true');
    }
    echo $vCountryCode;exit;
    return $vCountryCode;
}


$vWorkLocationRadius = "14";
$radusArr = array(5,10,15);
if(!in_array($vWorkLocationRadius,$radusArr)){
   array_push($radusArr,$vWorkLocationRadius);
}

echo "<pre>";print_r($radusArr);exit;

?>