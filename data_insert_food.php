<?php
  $sql    = "SELECT * FROM all_database_details";
  $db_food  = $obj->MySQLSelect($sql);
  
  if(count($db_food) > 0){
    for($i=0;$i<count($db_food);$i++){
        $host_name = $db_food[$i]['host_name'];
        $db_name = $db_food[$i]['db_name'];
        $db_password = $db_food[$i]['password'];
        $db_user = $db_food[$i]['db_user']; 
        //echo "<pre>";print_r($db_food);exit;
        $obj->MySQLClose();
        $obj=	new DBConnection($host_name, $db_name, $db_user,$db_password);
        //$obj=	new DBConnection("localhost", "foodapp", "root","root");  
        if($user_type == "Passenger"){
          $tblname = "register_user";
          $sql    = "SELECT * FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$phone_mobile'!='',vPhone = '$phone_mobile',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
      		$check_food_passenger  = $obj->MySQLSelect($sql);
          if(count($check_food_passenger) == 0){
            $Data_passenger['vTimeZone'] = $vTimeZone;
            $food_user_id = $obj->MySQLQueryPerform("register_user",$Data_passenger,'insert');
          }
        }/*else{
          $tblname = "register_driver";
          $sql    = "SELECT * FROM $tblname WHERE 1=1 AND IF('$email'!='',vEmail = '$email',0) OR IF('$phone_mobile'!='',vPhone = '$phone_mobile',0) OR IF('$fbid'!='',vFbId = '$fbid',0)";
      		$check_food_driver  = $obj->MySQLSelect($sql);
          if(count($check_food_driver) == 0){
            $Data_passenger['vTimeZone'] = $vTimeZone;
            $food_driver_id = $obj->MySQLQueryPerform("register_driver",$Data_passenger,'insert');
            
            if(SITE_TYPE=='Demo')
      			{
              $query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`";
      				$result_food = $obj->MySQLSelect($query);
              $Drive_vehicle['iDriverId'] = $food_driver_id;
              $Drive_vehicle['eType'] = "Ride";
              $Drive_vehicle['vCarType'] = $result_food[0]['countId'];
              $iDriver_VehicleId_food=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle,'insert');
      				$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId_food."' WHERE iDriverId='".$food_driver_id."'";
      				$obj->sql_query($sql);
      			}
            
          }
        } */
    }    
    $obj->MySQLClose();
    $obj=	new DBConnection(TSITE_SERVER, TSITE_DB, TSITE_USERNAME,TSITE_PASS);
 } 
?>