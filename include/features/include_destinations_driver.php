<?php
function getDriverFiveDestination($iDriverId){
    global $generalobj, $obj;
    $fields = "tDestLatitude,tDestLongitude,tDaddress";
    $sql = "SELECT $fields FROM driver_destinations where   iDriverId = '" . $iDriverId . "' GROUP BY tDaddress ORDER BY idriverdestinations DESC";

    $db_driver_destinations = $obj->MySQLSelect($sql);
    if (count($db_driver_destinations) > 0) {
        $db_driver_destinations_arr = array_slice($db_driver_destinations, 0, 5);
    } else {
        $db_driver_destinations_arr = array();
    }
    return $db_driver_destinations_arr;
}
if ($type == "startDriverDestination") {
    global $_REQUEST,$obj,$MAX_DRIVER_DESTINATIONS,$DRIVER_DESTINATIONS_RESET_TIME;

    $DRIVER_DESTINATIONS_RESET_TIME_ARR = explode(":",$DRIVER_DESTINATIONS_RESET_TIME);
    $DRIVER_DESTINATIONS_RESET_TIME_HOUR = $DRIVER_DESTINATIONS_RESET_TIME_ARR[0];
    $DRIVER_DESTINATIONS_RESET_TIME_MINITE = $DRIVER_DESTINATIONS_RESET_TIME_ARR[1];
    // Add Requerst parameter
    $iDriverId = isset($_REQUEST['iDriverId']) ? trim($_REQUEST['iDriverId']) : '';

    $tDestLatitudes = isset($_REQUEST['tRootDestLatitudes']) ? trim($_REQUEST['tRootDestLatitudes']) : '';

    $tDestLongitudes = isset($_REQUEST['tRootDestLongitudes']) ? trim($_REQUEST['tRootDestLongitudes']) : '';

    $tAdress = isset($_REQUEST['tAdress']) ? trim($_REQUEST['tAdress']) : '';

    $eStatus = isset($_REQUEST['eStatus']) ? trim($_REQUEST['eStatus']) : 'Active';

    $tDriverLatitudes = isset($_REQUEST['tDriverDestLatitude']) ? trim($_REQUEST['tDriverDestLatitude']) : '';

    $tDriverDestLongitude = isset($_REQUEST['tDriverDestLongitude']) ? trim($_REQUEST['tDriverDestLongitude']) : '';

    //table nanme 
    $tableName1 = 'driver_destinations_route';
      
    //added by SP for fly to not allow driver destinations on 27-09-2019 start
   $sql = "SELECT dv.vCarType, rd.iDriverVehicleId as iSelectedVehicleId FROM `driver_vehicle` dv, register_driver as rd WHERE dv.iDriverId='$iDriverId' AND rd.iDriverId='$iDriverId' AND dv.`eStatus`='Active' AND rd.iDriverVehicleId = dv.iDriverVehicleId";
     $Data_Car = $obj->MySQLSelect($sql);
     
   $vCarType = $Data_Car[0]['vCarType'];
   if ($vCarType != "") {
       $sql = "SELECT count(iVehicleTypeId) as Totalrec,SUM(CASE WHEN eType='Ride' AND eFly=1 THEN 1 ELSE 0 END) Totalfly,SUM(CASE WHEN eType!='DeliverAll'  THEN 1 ELSE 0 END) TotalRide  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
       $db_cartype = $obj->MySQLSelect($sql);
       if (count($db_cartype) > 0 && $db_cartype[0]['Totalfly'] >0 && $db_cartype[0]['TotalRide']==$db_cartype[0]['Totalfly']) {
                  $returnArr['Action'] = "0";
                  $returnArr['message'] = "LBL_FLY_VEHICLE_DRIVER_DESTINATIONS";
                  setDataResponse($returnArr);
       }
   }
       //added by SP for fly to not allow driver destinations on 27-09-2019 end

   $iDestinationCount = get_value('register_driver', 'iDestinationCount', 'iDriverId', $iDriverId, '', 'true');
   if($iDestinationCount < $MAX_DRIVER_DESTINATIONS){

            $where_driver_registration_data = "iDriverId='" . $iDriverId . "' AND eStatus='active'";
            $Data_update_driver_registration_data1['eDestinationMode'] = 'Yes';
            $Data_update_driver_registration_data1['iDestinationCount'] = $iDestinationCount+1;
            //$Data_update_driver_registration_data1['tDestinationModifiedDate'] = date('Y-m-d H:i:s',strtotime('+'.$DRIVER_DESTINATIONS_RESET_TIME_HOUR.' hour +'.$DRIVER_DESTINATIONS_RESET_TIME_MINITE.' minutes',time()));
           	$data=  $obj->MySQLQueryPerform('register_driver', $Data_update_driver_registration_data1, 'update', $where_driver_registration_data);
            
            //status change 
            $where = "iDriverId='" . $iDriverId . "' AND eStatus='Active'";

            $Data_insert_driver_destination_root_data['eStatus'] = 'Inactive';

            $obj->MySQLQueryPerform($tableName1, $Data_insert_driver_destination_root_data, 'update', $where);
            
            //destination route add
            $Data_insert_driver_destination_root['iDriverId'] = $iDriverId;

            $Data_insert_driver_destination_root['tDestLatitudes'] = $tDestLatitudes;

            $Data_insert_driver_destination_root['tDestLongitudes'] = $tDestLongitudes;

            $Data_insert_driver_destination_root['tAdress'] = $tAdress;

            $Data_insert_driver_destination_root['eStatus'] = $eStatus; 

            $iDriverDestinationsDataId = $obj->MySQLQueryPerform($tableName1, $Data_insert_driver_destination_root, 'insert');

            $driver_destination_sql = "SELECT idriverdestinations FROM `driver_destinations` WHERE tDestLatitude = '" . trim($tDriverLatitudes) . "' AND iDriverId = '" . $iDriverId . "' AND tDestLongitude = '" . trim($tDriverDestLongitude) . "'";
            
            $driver_destination_data = $obj->MySQLSelect($driver_destination_sql);


            //status change inactive
            $where_driver_destination_data = "iDriverId='" . $iDriverId . "' AND eStatus='Active'";
            $Data_insert_driver_destination_root_data1['eStatus'] = 'Inactive';
           $data=  $obj->MySQLQueryPerform('driver_destinations', $Data_insert_driver_destination_root_data1, 'update', $where_driver_destination_data);


            
            if(count($driver_destination_data) > 0){

                $idriverdestinations = $driver_destination_data[0]['idriverdestinations'];
                $where_driver_destination = "idriverdestinations=".$idriverdestinations;
                $Data_insert_driver_destination['eStatus'] = 'Active';
                $Data_insert_driver_destination['tDaddress'] = $tAdress;
                //$Data_insert_driver_destination['tStartDestinationDt'] = date('Y-m-d h:i:s');
                $obj->MySQLQueryPerform('driver_destinations', $Data_insert_driver_destination, 'update', $where_driver_destination);
            
            }else{
                $Data_insert_driver_destination['iDriverId'] = $iDriverId;
                $Data_insert_driver_destination['eStatus'] = 'Active';
                $Data_insert_driver_destination['tDestLatitude'] = $tDriverLatitudes;
                $Data_insert_driver_destination['tDestLongitude'] = $tDriverDestLongitude;
                $Data_insert_driver_destination['tDaddress'] = $tAdress;
                //$Data_insert_driver_destination['tStartDestinationDt'] = date('Y-m-d h:i:s');
                $id = $obj->MySQLQueryPerform("driver_destinations", $Data_insert_driver_destination, 'insert');
           
            }
            $Driverdata = getDriverDetailInfo($iDriverId);

            if(!empty($iDriverDestinationsDataId)){

                 $returnArr['Action'] = "1";
                 $returnArr['message'] = $Driverdata;
            }else{
                $returnArr['Action'] = "0";
                $returnArr['message'] = "";
            } 
        }else{
                $tableName = "register_driver";
                $iMemberId_VALUE = $iDriverId;
                $iMemberId_KEY = "iDriverId";

                $vLang = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
                if ($vLang == "" || $vLang == NULL) {
                   $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
               }
               $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
               $LBL_DRIVER_DEST_LIMIT_REACHED = $languageLabelsArr['LBL_DRIVER_DEST_LIMIT_REACHED'];
               $LBL_FOR_A_DAY = $languageLabelsArr['LBL_FOR_A_DAY'];
               $msg = $LBL_DRIVER_DEST_LIMIT_REACHED." ".$MAX_DRIVER_DESTINATIONS." ".$LBL_FOR_A_DAY;

            $returnArr['Action'] = "0";
            $returnArr['message'] = $msg;
        }
    setDataResponse($returnArr);
}

function getDestionsDriverList($driverArr,$userDestinationLatitude, $userDestinationLongitude){

    global $_REQUEST,$obj,$MAX_DRIVER_DESTINATIONS,$RESTRICTION_KM_NEAREST_DESTINATION_DRIVER;
    $newdriverArr = array();
    $newcount = 0;

    foreach ($driverArr as $driverArrKey => $driverArrValue) {
        if((($userDestinationLatitude=='0.0') || ($userDestinationLongitude=='0.0')) && strtoupper($driverArrValue['eDestinationMode'])=='NO'){
                $newdriverArr[$newcount] = $driverArrValue;
                $newcount++;
                continue;
        }

        if((empty($userDestinationLatitude) || empty($userDestinationLongitude)) && strtoupper($driverArrValue['eDestinationMode'])=='NO'){
                $newdriverArr[$newcount] = $driverArrValue;
                $newcount++;
                continue;
        }
        if((($userDestinationLatitude=='0.0') || ($userDestinationLongitude=='0.0')) && strtoupper($driverArrValue['eDestinationMode'])=='YES'){
                continue;
        }
        if((empty($userDestinationLatitude) || empty($userDestinationLongitude)) && strtoupper($driverArrValue['eDestinationMode'])=='YES'){
                continue;
        }
        if(strtoupper($driverArrValue['eDestinationMode'])=='YES')
        {

            if($driverArrValue['iDestinationCount']<=$MAX_DRIVER_DESTINATIONS){
               $iDriverId = $driverArrValue['iDriverId'];
                $sql = "SELECT `iRootId` ,  `iDriverId` ,  `tDestLatitudes` ,  `tDestLongitudes` ,  `tAdress` ,  `eStatus`  FROM `driver_destinations_route` WHERE iDriverId='".$iDriverId."' AND eStatus='Active'";
               $row = $obj->MySQLSelect($sql);
               if(count($row) >0){
               $tDestLatitudes  =   $row[0]['tDestLatitudes'];
               $tDestLongitudes =   $row[0]['tDestLongitudes'];
               $iDriverId       =   $row[0]['iDriverId'];

               $tDestLatitudesArr = explode(',',$tDestLatitudes);
               $tDestLongitudesArr = explode(',',$tDestLongitudes);

               foreach($tDestLatitudesArr as $tDestLatitudesArrKey => $tDestLatitudesArrValue){
                    $tDestLongitudesValue = $tDestLongitudesArr[$tDestLatitudesArrKey];

                    // echo $tDestLatitudesArrValue.",".$tDestLongitudesValue;
                    //   echo '<br>';  
                    $Distance_Km = distanceByLocation($userDestinationLatitude, $userDestinationLongitude, $tDestLatitudesArrValue, $tDestLongitudesValue, "K");
                    // echo '<br>';
                    // echo $RESTRICTION_KM_NEAREST_DESTINATION_DRIVER;
                    // echo '<br>';
                    if ($Distance_Km > $RESTRICTION_KM_NEAREST_DESTINATION_DRIVER) {
                        continue;
                    }else{
                        $newdriverArr[$newcount] = $driverArrValue;
                        $newcount++;
                        break;
                    }
               }

               }else{
                $newdriverArr[$newcount] = $driverArrValue;
                $newcount++;
               }

            }else{
                continue;
            }
        }else{
            $newdriverArr[$newcount] = $driverArrValue;
            $newcount++;    
        }
        //$newcount++;    
    }
    return $newdriverArr;
}

function getDriverDestination($iDriverId){
    global $generalobj, $obj;
    $fields = "tDestLatitude,tDestLongitude,tDaddress";
    $sql = "SELECT $fields FROM driver_destinations where   iDriverId = '" . $iDriverId . "' AND eStatus='Active' ORDER BY idriverdestinations DESC";

    $db_driver_destinations = $obj->MySQLSelect($sql);
    $Driverdata = array();
    if(count($db_driver_destinations) > 0){
        
        $Driverdata['tDestinationStartedLatitude'] = $db_driver_destinations[0]['tDestLatitude'];
        $Driverdata['tDestinationStartedLongitude'] = $db_driver_destinations[0]['tDestLongitude'];
        $Driverdata['tDestinationStartedAddress'] = $db_driver_destinations[0]['tDaddress'];   
    }else{
        $Driverdata['tDestinationStartedLatitude'] = "";
        $Driverdata['tDestinationStartedLongitude'] = "";
        $Driverdata['tDestinationStartedAddress'] = "";   
    }
    return $Driverdata;
}


if ($type == "CancelDriverDestination") {
    
    global $_REQUEST,$obj;
    
    $iDriverId = isset($_REQUEST['iDriverId']) ? trim($_REQUEST['iDriverId']) : '';

	$where_driver_registration_data = "iDriverId='" . $iDriverId . "' AND eStatus='active'";
    $Data_update_driver_registration_data1['eDestinationMode'] = 'No';
    $data=  $obj->MySQLQueryPerform('register_driver', $Data_update_driver_registration_data1, 'update', $where_driver_registration_data);

   $where = " iDriverId='" . $iDriverId . "' AND eStatus='Active'";

   $Data_insert_driver_destination_root_data['eStatus'] = 'Inactive';
   $obj->MySQLQueryPerform('driver_destinations_route', $Data_insert_driver_destination_root_data, 'update', $where);

    
	$where_driver_destination_data = " iDriverId='" . $iDriverId . "' AND eStatus='Active'";
	$Data_insert_driver_destination_root_data1['eStatus'] = 'Inactive';
	$iDriverDestinationsDataId =  $obj->MySQLQueryPerform('driver_destinations', $Data_insert_driver_destination_root_data1, 'update', $where_driver_destination_data);

	$Driverdata = getDriverDetailInfo($iDriverId);
    if(!empty($iDriverDestinationsDataId)){
         $returnArr['Action'] = "1";
         $returnArr['message'] = $Driverdata;
    }else{
        $returnArr['Action'] = "0";
        $returnArr['message'] = "";
    } 
 
	setDataResponse($returnArr);
}    
?>