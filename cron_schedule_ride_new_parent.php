<?php
  ini_set('max_execution_time', 0); 
  set_time_limit(0);
  error_reporting(0);
	//include_once('common.php');     
    
  
  geturl("https://www.chegou.site/cron_schedule_ride_new.php");
  
  function geturl($url){
     $ch = curl_init();
     curl_setopt ($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
     $contents = curl_exec($ch);
     
     if (curl_errno($ch)) {
        //echo curl_error($ch);
        //echo "\n<br />"; 
     } else {       
        /* test Email */  
     }
     curl_close($ch);
     return $contents;
  }
	
?>