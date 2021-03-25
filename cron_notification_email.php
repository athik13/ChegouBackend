<?php
  include_once("common.php");

/* Cron Log Update */
$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/cron_notification_email_status.txt", "w");
fwrite($myfile, "running");
fclose($myfile);
/* Cron Log Update End */
date_default_timezone_set('Asia/Kuala_Lumpur');

  global $generalobj;

	$query = "SELECT * FROM cab_booking WHERE iDriverId != '0' AND ( eStatus = 'Pending' OR eStatus = 'Assign' ) AND eAutoAssign = 'No'";
	$db_cab = $obj->MySQLSelect($query);

    function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        
        $interval = date_diff($datetime1, $datetime2);
        
        return $interval->format($differenceFormat);
        
    }
	if(count($db_cab) > 0) { 
        for($i=0;$i<count($db_cab);$i++) {
          
            $current_date_time = @date('Y-m-d H:i:s');         
            $current_date = @date('Y-m-d'); 

            $booking_date = @date('Y-m-d',strtotime($db_cab[$i]['dBooking_date']));
            $time_diff = dateDifference($current_date_time,$db_cab[$i]['dBooking_date'],'%a:%h:%i:%s');
            $times = explode(':', $time_diff);
            $total_minutes_diff = (($times[0]*24*60) + ($times[1]*60) + $times[2] + ($times[3]/60));
           

            $added_date = @date('Y-m-d',strtotime($db_cab[$i]['dAddredDate']));
            $time_diff_add = dateDifference($current_date_time,$db_cab[$i]['dAddredDate'],'%a:%h:%i:%s');
            $times_add = explode(':', $time_diff_add);
            $total_minutes_diff_new = (($times_add[0]*24*60) + ($times_add[1]*60) + $times_add[2] + ($times_add[3]/60));
            $total_minutes_diff_add  =  round($total_minutes_diff_new, 2);

            // addon
            if($total_minutes_diff_add >= 30) {

                if($db_cab[$i]['eMessageAdminOne'] == 'No') {                              

                    $getMaildata = getMailDetail($db_cab[$i]['iDriverId'],$db_cab[$i]['dBooking_date'],$db_cab[$i]['iUserId'],$db_cab[$i]['vBookingNo'],$db_cab[$i]['vTimeZone']);
                   
                    $getMaildata['SourceAddress'] = $db_cab[$i]['vSourceAddresss'];
                    $getMaildata['DestAddress'] = $db_cab[$i]['tDestAddress'];
                    
                    $mail = $generalobj->send_email_user('CRON_EMAIL_TO_ADMIN',$getMaildata);
                    if($mail == 1){

                       echo "Send Mail Successfully....";     

                    } else {
                        echo " Send Mail Failed..... ";     

                    }                        

                    $updateQuery = "UPDATE cab_booking SET eMessageAdminOne ='Yes' WHERE iCabBookingId=".$db_cab[$i]['iCabBookingId'];                       
                    $db_cab = $obj->sql_query($updateQuery);
                }

            }

            if($current_date == $booking_date){
                // addon
                if($total_minutes_diff <= 120) {
                    
                    if($db_cab[$i]['eMessageAdminOne'] == 'Yes' && $db_cab[$i]['eMessageAdminTwo'] == 'No'){ 

                       $getMaildata = getMailDetail($db_cab[$i]['iDriverId'],$db_cab[$i]['dBooking_date'],$db_cab[$i]['iUserId'],$db_cab[$i]['vBookingNo'],$db_cab[$i]['vTimeZone']);
                       
                        $getMaildata['SourceAddress'] = $db_cab[$i]['vSourceAddresss'];
                        $getMaildata['DestAddress'] = $db_cab[$i]['tDestAddress'];
                        
                        $mail = $generalobj->send_email_user('CRON_EMAIL_TO_ADMIN',$getMaildata);
                        if($mail == 1){

                           echo "Send Mail Successfully....";     

                        }else{
                            echo " Send Mail Failed..... ";     

                        }                        

                        $updateQuery = "UPDATE cab_booking SET eMessageAdminTwo ='Yes' WHERE iCabBookingId=".$db_cab[$i]['iCabBookingId'];                       
                        $db_cab = $obj->sql_query($updateQuery);
                    }
                }        
                
                if($total_minutes_diff <= 30) {
                    
                    if($db_cab[$i]['eMessageSend'] == 'No'){

                        $getdata_driver = get_DriverDetail($db_cab[$i]['iDriverId'],$db_cab[$i]['dBooking_date'],$db_cab[$i]['iUserId'],$db_cab[$i]['vBookingNo'],$db_cab[$i]['vTimeZone']);
                        $getdata_user  =  get_PassengerDetail($db_cab[$i]['iUserId'],$db_cab[$i]['dBooking_date'],$db_cab[$i]['iDriverId'],$db_cab[$i]['vBookingNo'],$db_cab[$i]['vTimeZone']);
                        if($getdata_driver['action'] == 1 &&  $getdata_user['action'] == 1){
                           echo "Send Mail Successfully....";     
                        }else{
                            echo " Send Mail Failed..... ";     
                        }                        
                        $updateQuery = "UPDATE cab_booking SET eMessageSend ='Yes' WHERE iCabBookingId=".$db_cab[$i]['iCabBookingId'];                       
                        $db_cab = $obj->sql_query($updateQuery);
                    }

                }

            }

        } 
    
	}

    /* Cron Log Update */
    $myfile = fopen($tconfig['tpanel_path']."webimages/script_files/cron_notification_email_status.txt", "w");
    fwrite($myfile, "executed");
    fclose($myfile);
    /* Cron Log Update End */  

    function get_DriverDetail($id,$booking_date,$iUserId,$BookingNo,$vTimeZone){
        global $generalobj,$obj;

        $query = "SELECT * FROM register_driver WHERE iDriverId=".$id;
        $db_driver = $obj->MySQLSelect($query);
        $vPhone = $db_driver[0]['vPhone'];

        //added by SP for sms functionality on 15-7-2019 start
        $driverData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_driver` AS r, `country` AS c WHERE r.iDriverId = $id AND r.vCountry = c.vCountryCode");
        $vcode = $driverData[0]['vPhoneCode'];
        //$vcode = $db_driver[0]['vCode'];
        //added by SP for sms functionality on 15-7-2019 end

        $vLang = $db_driver[0]['vLang'];  

        //$Booking_Date = @date('d-m-Y',strtotime($booking_date));    
        //$Booking_Time = @date('H:i:s',strtotime($booking_date)); 
        $systemTimeZone = date_default_timezone_get();
    	// echo "hererrrrr:::".$systemTimeZone;exit;
    	$scheduleDate = converToTz($booking_date,$vTimeZone,$systemTimeZone); 
        $Booking_Date = @date('d-m-Y',strtotime($scheduleDate));    
        $Booking_Time = @date('H:i:s',strtotime($scheduleDate));  

        $query = "SELECT * FROM register_user WHERE iUserId=".$iUserId;
        $db_user= $obj->MySQLSelect($query);
        $Pass_name = $db_user[0]['vName'].' '.$db_user[0]['vLastName']; 

        $maildata['PASSENGER_NAME'] = $Pass_name;      
        $maildata['BOOKING_DATE'] = $Booking_Date;      
        $maildata['BOOKING_TIME'] =  $Booking_Time;      
        $maildata['BOOKING_NUMBER'] = $BookingNo;      

        $message_layout = send_messages_user("DRIVER_SEND_MESSAGE",$maildata,"",$vLang);
        
        return sendCode($vPhone,$vcode,$message_layout,"");    

    }

    function get_PassengerDetail($iUserId,$booking_date,$id,$BookingNo,$vTimeZone){
        global $generalobj,$obj;

        $query = "SELECT * FROM register_user WHERE iUserId=".$iUserId;
        $db_pass = $obj->MySQLSelect($query);

        $query = "SELECT * FROM register_driver WHERE iDriverId=".$id;
        $db_driver = $obj->MySQLSelect($query);

        $query = "SELECT * FROM driver_vehicle WHERE iDriverVehicleId=".$db_driver[0]['iDriverVehicleId'];  
        $db_driver_vehicles = $obj->MySQLSelect($query);

        $vPhone = $db_pass[0]['vPhone'];

        //added by SP for sms functionality on 15-7-2019 start
        $passengerData = $obj->MySQLSelect("SELECT r.vPhone,c.vPhoneCode FROM  `register_user` AS r, `country` AS c WHERE r.iUserId = $iUserId AND r.vCountry = c.vCountryCode");
        $vcode = $passengerData[0]['vPhoneCode'];
        //$vcode = $db_pass[0]['vPhoneCode'];
        //added by SP for sms functionality on 15-7-2019 end

        $vLang = $db_pass[0]['vLang'];
        $driver_name = $db_driver[0]['vName'].' '.$db_driver[0]['vLastName'];

        //$Booking_Date = @date('d-m-Y',strtotime($booking_date));    
        //$Booking_Time = @date('H:i:s',strtotime($booking_date));
        //$Booking_Date = @date('d-m-Y',strtotime($booking_date));    
        //$Booking_Time = @date('H:i:s',strtotime($booking_date)); 
        $systemTimeZone = date_default_timezone_get();
    		// echo "hererrrrr:::".$systemTimeZone;exit;
    		$scheduleDate = converToTz($booking_date,$vTimeZone,$systemTimeZone); 
        $Booking_Date = @date('d-m-Y',strtotime($scheduleDate));    
        $Booking_Time = @date('H:i:s',strtotime($scheduleDate));
      

        $maildata['DRIVER_NAME'] = $driver_name;      
        $maildata['PLATE_NUMBER'] = $db_driver_vehicles[0]['vLicencePlate'];      
        $maildata['BOOKING_DATE'] = $Booking_Date;      
        $maildata['BOOKING_TIME'] =  $Booking_Time;      
        $maildata['BOOKING_NUMBER'] = $BookingNo;      

        $message_layout = send_messages_user("USER_SEND_MESSAGE",$maildata,"",$vLang);
        return sendCode($vPhone,$vcode,$message_layout,"");    

    }

    function getMailDetail($id,$booking_date,$iUserId,$BookingNo,$vTimeZone){
        global $generalobj,$obj;

        $query = "SELECT * FROM register_driver WHERE iDriverId=".$id;
        $db_driver = $obj->MySQLSelect($query);
        $vPhone = $db_driver[0]['vPhone'];
        $vcode = $db_driver[0]['vCode'];
        $vLang = $db_driver[0]['vLang'];
        $driver_name = $db_driver[0]['vName'].' '.$db_driver[0]['vLastName'];

        $systemTimeZone = date_default_timezone_get();
        $scheduleDate = converToTz($booking_date,$vTimeZone,$systemTimeZone); 
        $Booking_Date = @date('d-m-Y',strtotime($scheduleDate));    
        $Booking_Time = @date('H:i:s',strtotime($scheduleDate));  

        $query = "SELECT * FROM register_user WHERE iUserId=".$iUserId;
        $db_user= $obj->MySQLSelect($query);
        $Pass_name = $db_user[0]['vName'].' '.$db_user[0]['vLastName']; 

        $maildata['Rider'] = $Pass_name;      
        $maildata['Ddate'] = $Booking_Date;      
        $maildata['Time'] =  $Booking_Time;      
        $maildata['BookingNo'] = $BookingNo;
        $maildata['Driver'] = $driver_name;
        
        return $maildata; 

    }


    function send_messages_user($type, $db_rec = '', $newsid = ''){
        
        global $MAIL_FOOTER,$generalobj,$obj;
            $str = "select * from send_message_templates where vEmail_Code='" . $type . "'";
            $res = $obj->MySQLSelect($str);
            switch ($type) {
                case "DRIVER_SEND_MESSAGE":              
                $key_arr = Array("#PASSENGER_NAME#","#BOOKING_DATE#","#BOOKING_TIME#","#BOOKING_NUMBER#","#MAILFOOTER#");
                $val_arr = Array($db_rec['PASSENGER_NAME'], $db_rec['BOOKING_DATE'], $db_rec['BOOKING_TIME'] , $db_rec['BOOKING_NUMBER'],$MAIL_FOOTER);
                break;

                case "USER_SEND_MESSAGE":               
                 $key_arr = Array("#DRIVER_NAME#","#PLATE_NUMBER#","#BOOKING_DATE#","#BOOKING_TIME#","#BOOKING_NUMBER#","#MAILFOOTER#");
                $val_arr = Array($db_rec['DRIVER_NAME'], $db_rec['PLATE_NUMBER'],$db_rec['BOOKING_DATE'], $db_rec['BOOKING_TIME'] , $db_rec['BOOKING_NUMBER'],$MAIL_FOOTER);      
                break;
            }  
            
           // $maillanguage = get_user_preffered_language($to_email);
            $maillanguage = (isset($maillanguage) && $maillanguage != '') ? $maillanguage : 'EN';

            $mailsubject = $res[0]['vSubject_' . $maillanguage];
            $tMessage = $res[0]['vBody_' . $maillanguage];  
            $tMessage = str_replace($key_arr, $val_arr, $tMessage);
            return $tMessage;
    }

    function get_user_preffered_language($vEmail) {
         global $obj, $tconfig;
        $sql = "select vLang from register_user where vEmail ='" . $vEmail . "'";
        $res = $obj->MySQLSelect($sql);
        $preflang = "EN";

        if (count($res) > 0) {
            $preflang = $res[0]['vLang'];
        }
        return $preflang;
    }
    function getConfigurations($tabelName, $LABEL) {
        global $obj;

        $sql = "SELECT vValue FROM `" . $tabelName . "` WHERE vName='$LABEL'";
        $Data = $obj->MySQLSelect($sql);
        $Data_value = $Data[0]['vValue'];
        return $Data_value;
    } 
  
    function sendCode($mobileNo,$code,$fpass,$pass=''){
        global $site_path;
        // $mobileNo=$this->clearPhone($mobileNo);
        // $mobileNo=$code.$mobileNo;

        $return = $generalobj->sendSystemSms($mobileNo,$code,$fpass); //added by SP for sms functionality on 15-7-2019
        if($return==1) $returnArr['action'] = "1";
        else $returnArr['action'] = "0";
        /*require_once(TPATH_CLASS .'twilio/Services/Twilio.php');

        $account_sid = getConfigurations("configurations","MOBILE_VERIFY_SID_TWILIO");
        $auth_token = getConfigurations("configurations","MOBILE_VERIFY_TOKEN_TWILIO");
        $twilioMobileNum= getConfigurations("configurations","MOBILE_NO_TWILIO");

        $client = new Services_Twilio($account_sid, $auth_token);

        $toMobileNum= "+".$code.$mobileNo;      
        
        try{
            $sms = $client->account->messages->sendMessage($twilioMobileNum,$toMobileNum,$fpass);
            $returnArr['action'] ="1";
        } catch (Services_Twilio_RestException $e) {
            $returnArr['action'] ="0";
        } */
        $returnArr['verificationCode'] =$code;
        return $returnArr;
    }


    function converToTz($time, $toTz, $fromTz,$dateFormat="Y-m-d H:i:s") {
		$date = new DateTime($time, new DateTimeZone($fromTz));
		$date->setTimezone(new DateTimeZone($toTz));
		$time = $date->format($dateFormat);
		return $time;
	}    
?>
