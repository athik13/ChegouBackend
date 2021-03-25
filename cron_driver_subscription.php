<?php
/* 	
  This api run only 1 time per a day
 */
session_start();
include_once('common.php');
include_once(TPATH_CLASS . 'class.general.php');
require_once(TPATH_CLASS . 'pubnub/autoloader.php');
include_once(TPATH_CLASS . 'configuration.php');
include_once('generalFunctions.php');


date_default_timezone_set('Asia/Kuala_Lumpur');
/* Cron Log Update */
$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/cron_driver_subscription_status.txt", "w");
fwrite($myfile, "running");
fclose($myfile);
/* Cron Log Update End */

$DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS = '';

$DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS = $generalobj->getConfigurations("configurations", "DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS");
$DRIVER_SUBSCRIPTION_ENABLE = $generalobj->getConfigurations("configurations", "DRIVER_SUBSCRIPTION_ENABLE");
$curdate = date("Y-m-d H:i:s");

$tblDetails = 'driver_subscription_details';
//$date = '2019-09-13 00:00:00';
//$selExpired = "SELECT iDriverSubscriptionDetailsId,iDriverId,tExpiryDate >= '$date' AS tExpiryDate, datediff(tExpiryDate,'$date') AS daysRemain,eSubscriptionStatus FROM $tblDetails"; 

$selExpired = "SELECT iDriverSubscriptionDetailsId,iDriverId,tExpiryDate >= '$curdate' AS tExpiryDate, datediff(tExpiryDate,'$curdate') AS daysRemain,eSubscriptionStatus FROM $tblDetails"; 
$dataExpired = $obj->MySQLSelect($selExpired);

foreach($dataExpired as $key=>$value) {
    
    ######################  UPDATE STATUS WHEN EXPIRY DATE IS OVER  ############################
    
    if($value['tExpiryDate']<=0 && $value['eSubscriptionStatus']!='Expired') { 
        $where = "iDriverSubscriptionDetailsId = '".$value['iDriverSubscriptionDetailsId']."'";
        $DataUpdate['eSubscriptionStatus'] = 'Expired';
        $id = $obj->MySQLQueryPerform($tblDetails,$DataUpdate,'update',$where);
    }
    
    ######################  SEND NOTIFICATION WHEN X DAYS REMAIN IN SUBSCRIPTION  ############################
    
    if($DRIVER_SUBSCRIPTION_ENABLE=='Yes') { 
    if(!empty($DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS) && $value['daysRemain']<=$DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS && $value['eSubscriptionStatus']=='Subscribed') {
        $driverData = get_value('register_driver', 'vName,vLastName,vEmail,iAppVersion,eDeviceType,iGcmRegId,vLang', 'iDriverId', $value['iDriverId']);
        
        if (count($driverData) > 0) {
            
            $deviceTokens_arr_ios = $registation_ids_new = array();
            
            $iGcmRegId = $driverData[0]['iGcmRegId'];
            $eDeviceType = $driverData[0]['eDeviceType'];
            $vLang = $driverData[0]['vLang'];
            if ($vLang == "" || $vLang == NULL) {
                $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            $languageLabelsArr = getLanguageLabelsArr($vLang, "1");
            $alertMsg = $languageLabelsArr['LBL_SUBSCRIPTION_EXPIRED_REMAIN_DAYS'];
            
            if($value['daysRemain']==0) {
                $daysRemainTxt = ' '.$languageLabelsArr['LBL_SUBSCRIPTION_MAIL_REMAINTXT_ONE'];
            } else {
                $daysRemainTxt = $value['daysRemain'].' '.$languageLabelsArr['LBL_SUBSCRIPTION_MAIL_REMAINTXT_SECOND'];
            }
            $message = str_replace('##',$daysRemainTxt,$alertMsg);

            if ($eDeviceType == "Android") {
                array_push($registation_ids_new, $iGcmRegId);
                $Rmessage = array(
                    "message" => $message
                );
                $result = send_notification($registation_ids_new, $Rmessage, 0);
            } else if ($eDeviceType != "Android") {
                array_push($deviceTokens_arr_ios, $iGcmRegId);
                if ($message != "") {
                    sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                }
            }
            
            ######################  SEND MAIL WHEN X DAYS REMAIN IN SUBSCRIPTION  ############################
            
            $getMaildata['vEmail'] = $driverData[0]['vEmail'];
             if($value['daysRemain']==0) {
                $getMaildata['daysRemainTxt'] = ' '.$languageLabelsArr['LBL_SUBSCRIPTION_MAIL_REMAINTXT_ONE'];
            } else {
                $getMaildata['daysRemainTxt'] = $value['daysRemain'].' '.$languageLabelsArr['LBL_SUBSCRIPTION_MAIL_REMAINTXT_SECOND'];
            }            
            //$getMaildata['daysRemain'] = $value['daysRemain'];
            $getMaildata['FromName'] = $driverData[0]['vName'].' '.$driverData[0]['vLastName'];
            $mail = $generalobj->send_email_user('CRON_SUBSCRIBE_REMAIN_DAYS',$getMaildata);

            if(!empty($mail)) {
                echo "Mail has been sent successfully to ".$driverData[0]['vEmail'];
            }
        }
    }
    }
}

/* Cron Log Update */
$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/cron_driver_subscription_status.txt", "w");
fwrite($myfile, "executed");
fclose($myfile);
/* Cron Log Update End */
?>
