<?php	
	ob_start();
	session_start();
	
//    include_once "linkedin.php";
	include_once "linkedin_outh2.php"; //changed by me

	include_once('../common.php');
	include_once('../assets/libraries/class.general.php');
	$generalobj = new General();

	$appId = $LINKEDIN_APP_ID;
	$appsecretkey = $LINKEDIN_APP_SECRET_KEY;
	
		$config['base_url']             =   $tconfig['tsite_url'].'linkedin-login/auth.php';
		$config['callback_url']         =   $tconfig['tsite_url'].'linkedin-login/linkedinconfig.php';
		$config['linkedin_access']      =   $appId;
		$config['linkedin_secret']      =   $appsecretkey;

		$userType = (isset($_REQUEST['userType'])) ? $_REQUEST['userType'] : '';

		$_SESSION['linkedin_user'] = 'Driver';
		
		$_SESSION['linkedin_usertype'] = $userType;
		 /* 
	   unset($_SESSION['linkedin_user']);
	   unset($_SESSION['oauth_access_token']);
	   unset($_SESSION['oauth_verifier']);
	   unset($_SESSION['requestToken']); */
	
    # First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
	
		$oauth_problem = isset($_GET['oauth_problem']) ? $_GET['oauth_problem'] : '';
	 
		$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
		if($oauth_problem=="user_refused")
		{ 	 
				$link=$tconfig['tsite_url'].'sign-in.php';
				header("Location:".$link);
				exit; 			  
		}
		
	$linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url']);   
	if(!empty($_GET['code'])) { //changed by me start
		$data = $linkedin->linkedin_auth_get();  
	} else {
		$data = $linkedin->getAuthorizationCode();
	} //changed by me end
	
			if (isset($_REQUEST['oauth_verifier'])){
				
					$_SESSION['oauth_verifier']     = $_REQUEST['oauth_verifier'];
				 
						$linkedin->request_token    =   unserialize($_SESSION['requestToken']);
						$linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
						$linkedin->getAccessToken($_SESSION['oauth_verifier']);				 
						$_SESSION['oauth_access_token'] = serialize($linkedin->access_token);					 
						header("Location: " . $config['callback_url']);
						exit;
				   }
				   else{  
						$linkedin->request_token    =   unserialize($_SESSION['requestToken']);
						$linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
						$linkedin->access_token     =   unserialize($_SESSION['oauth_access_token']);
				 
				  }  
				    
			// print_r($linkedin);
		 
			/* 
		    if (empty($_REQUEST['oauth_verifier'])){				
			   
					$linkedin->getRequestToken();   
					$_SESSION['requestToken'] = serialize($linkedin->request_token);
					header("Location: " . $linkedin->generateAuthorizeUrl());
					  
					$linkedin->request_token    =   unserialize($_SESSION['requestToken']);
					$linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
					$linkedin->access_token     =   unserialize($_SESSION['oauth_access_token']);
			   }
			   else   {     
					$_SESSION['oauth_verifier']=$_REQUEST['oauth_verifier'];
					$linkedin->request_token    =   unserialize($_SESSION['requestToken']);
					$linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
					$linkedin->getAccessToken($_REQUEST['oauth_verifier']); 
				} 
				
				*/ 
					# You now have a $linkedin->access_token and can make calls on behalf of the current member
					//$xml_response = $linkedin->getProfile("~:(id,first-name,last-name,email-address,headline,picture-url,picture-urls::(original))?format=json");
  
					//$data = json_decode($xml_response, TRUE); //changed by me
			 					 
				 	/* foreach($data['pictureUrls'] as $key => $value)
						{
							 
							foreach($value as $keys => $values)
								{
									$values;
								}
							
						}
				    */
					
					
 				 	$fbid =$data['id'];
					//echo "$fbfirstname".$fbfirstname =$data['firstName'];
				 	//echo "$fblastname".$fblastname =$data['lastName'];
					
					//changed by me
					$fbfirstname =$data['localizedFirstName'];
				 	$fblastname =$data['localizedLastName'];
					
				 	$headline =$data['headline'];
					
					$femail =$data['email'];
				 	$picture =$data['profile_pic'];
					
				 	//$femail =$data['emailAddress'];
				 	//$picture =$values;
					$status =$data['status'];
					//$status = $_SESSION['state'];
				  
			if(empty($status))
			{  
					include_once($tconfig["tsite_libraries_v"]."/Imagecrop.class.php");
					$thumb = new thumbnail();
					$temp_gallery = $tconfig["tsite_temp_gallery"];

					include_once($tconfig["tsite_libraries_v"]."/SimpleImage.class.php");
					$img = new SimpleImage();
					 
   
			try
			{
				$db_user = array();
				if($femail != '') {
					$sqll001 = " vEmail='".$femail."'";
				}else {
					$sqll001 = " vFbId = '".$fbid."' AND eSignUpType = 'LinkedIn'";
				}
				 
					{
				
				if($femail != '' || $fbid != '') {
					$sql = "SELECT iDriverId,vImage,eGender,vPhone,eStatus FROM register_driver WHERE $sqll001";
					$db_user = $obj->MySQLSelect($sql);
				}
				
				if(count($db_user) > 0){
					if($db_user[0]['eStatus'] == "Deleted"){ 
						
						$_SESSION['sess_error_social'] = addslashes($langage_lbl['LBL_ACC_DELETE_TXT']);

						if($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
							$link = $tconfig["tsite_url"]."provider-login";
						} else{
							$link = $tconfig["tsite_url"]."driver-login";
						}
						header("Location:".$link);exit;
					}
					
				 	$Photo_Gallery_folder =$tconfig["tsite_upload_images_driver_path"]."/".$db_user[0]['iDriverId']."/";
					
					unlink($Photo_Gallery_folder.$db_user[0]['vImage']);
					unlink($Photo_Gallery_folder."1_".$db_user[0]['vImage']);
					unlink($Photo_Gallery_folder."2_".$db_user[0]['vImage']);
					unlink($Photo_Gallery_folder."3_".$db_user[0]['vImage']);   
					unlink($Photo_Gallery_folder."4_".$db_user[0]['vImage']);   
				
					if(!is_dir($Photo_Gallery_folder)) { 
						mkdir($Photo_Gallery_folder, 0777); 
					}
					$baseurl =  $picture;
					$url = strtolower($fbid).".jpg"; //changed by me
					$image_name = $generalobj->copyRemoteFile($baseurl,  $Photo_Gallery_folder.$url);                              
				
					if(is_file($Photo_Gallery_folder.$url)) {
				 
						list($width, $height, $type, $attr)= getimagesize($Photo_Gallery_folder.$url);           
						if($width < $height){
							$final_width = $width;
						}else{
							$final_width = $height;
						}      
						$img->load($Photo_Gallery_folder.$url)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder.$url);
						$imgname = $generalobj->img_data_upload($Photo_Gallery_folder,$url,$Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"],""); 
					}

				 	$sql = "UPDATE register_driver set vFbId='".$fbid."', vImage='".$imgname."',eSignUpType = 'LinkedIn' WHERE iDriverId='".$db_user[0]['iDriverId']."'";			
					$obj->sql_query($sql);					 

					if(SITE_TYPE=='Demo'){
					  $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('".$db_user[0]['iDriverId']."', 'Passenger', 'WebLogin','".$_SERVER['REMOTE_ADDR']."')";
					  $obj->sql_query($login_sql);
					}
					return $generalobj->Checkverification_mobile($db_user[0]['iDriverId'],'driver');
				}else{

				if(SITE_TYPE=='Demo'){
					$_SESSION['sess_error_social'] = addslashes($langage_lbl['LBL_SIGNUP_DEMO_CONTENT']);
					if($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
						$link = $tconfig["tsite_url"]."provider-login";
					} else{
						$link = $tconfig["tsite_url"]."driver-login";
					}
					header("Location:".$link);exit;
				}
					$sql = "select * from currency where eDefault = 'Yes'";
					$db_curr = $obj->MySQLSelect($sql);
					$curr = $db_curr[0]['vName'];
					
					$sql = "select * from language_master where eDefault = 'Yes'";
					$db_lang = $obj->MySQLSelect($sql);
					
					$lang = $db_lang[0]['vCode'];
					$eReftype = "Driver";
					$refercode = $generalobj->ganaraterefercode($eReftype);
					$dRefDate  = Date('Y-m-d H:i:s');
					$tRegistrationDate	= Date('Y-m-d H:i:s');
					if($femail != "") {
						$sql = "INSERT INTO register_driver (vFbId,vName, vLastName, vEmail, iCompanyId, eStatus,vImage,eGender,vLang,vCurrencyDriver,vRefCode,dRefDate,tRegistrationDate) VALUES ('".$fbid."','".$fbfirstname."', '".$fblastname."', '".$femail."', '1', 'Inactive','','".$fbgender."','".$lang."','".$curr."','".$refercode."','".$dRefDate."','".$tRegistrationDate."')";
						$iDriverId =$obj->MySQLInsert($sql);
					} else {
						$sql = "INSERT INTO register_driver (vFbId,vName, vLastName, vEmail, iCompanyId, eStatus,vImage,eGender,vLang,vCurrencyDriver,vRefCode,dRefDate,tRegistrationDate) VALUES ('".$fbid."','".$fbfirstname."', '".$fblastname."', '".$femail."', '1', 'Inactive','','".$fbgender."','".$lang."','".$curr."','".$refercode."','".$dRefDate."','".$tRegistrationDate."')";
						$iDriverId =  $obj->MySQLInsert($sql);
					}
					
					if($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
						$query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE `eType` = 'UberX'";
						$result = $obj->MySQLSelect($query);
						
						$Drive_vehicle['iDriverId'] = $iDriverId;
						$Drive_vehicle['iCompanyId'] = "1";
						$Drive_vehicle['iMakeId'] = "3";
						$Drive_vehicle['iModelId'] = "1";
						$Drive_vehicle['iYear'] = Date('Y');
						$Drive_vehicle['vLicencePlate'] = "My Services";
						$Drive_vehicle['eStatus'] = "Active";
						$Drive_vehicle['eCarX'] = "Yes";
						$Drive_vehicle['eCarGo'] = "Yes";
						$Drive_vehicle['eType'] = "UberX";		
						$Drive_vehicle['vCarType'] = $result[0]['countId'];
						$iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle,'insert');

						if($APP_TYPE == 'UberX') {
							$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId."' WHERE iDriverId='".$iDriverId."'";
							$obj->sql_query($sql);
						}
						
/*						if($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes"){
							$sql="select iVehicleTypeId,iVehicleCategoryId,eFareType,fFixedFare,fPricePerHour from vehicle_type where 1=1";
							$data_vehicles = $obj->MySQLSelect($sql);
							
							if($data_vehicles[$i]['eFareType'] != "Regular")
							{
								for($i=0 ; $i < count($data_vehicles); $i++){
									$Data_service['iVehicleTypeId'] = $data_vehicles[$i]['iVehicleTypeId'];
									$Data_service['iDriverVehicleId'] = $iDriver_VehicleId;
									
									if($data_vehicles[$i]['eFareType'] == "Fixed"){
										$Data_service['fAmount'] = $data_vehicles[$i]['fFixedFare'];
									}
									else if($data_vehicles[$i]['eFareType'] == "Hourly"){
										$Data_service['fAmount'] = $data_vehicles[$i]['fPricePerHour'];
									}
									$data_service_amount = $obj->MySQLQueryPerform('service_pro_amount',$Data_service,'insert');
								}
							}
						}*/

							if($APP_TYPE == 'Ride-Delivery-UberX') {
								if(SITE_TYPE=='Demo')
								{
									$query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE (`eType` = 'Ride' OR `eType` = 'Deliver')";
									$result = $obj->MySQLSelect($query);
									$Drive_vehicle_Ride['iDriverId'] = $iDriverId;
									$Drive_vehicle_Ride['iCompanyId'] = "1";
									$Drive_vehicle_Ride['iMakeId'] = "5";
									$Drive_vehicle_Ride['iModelId'] = "18";
									$Drive_vehicle_Ride['iYear'] = "2014";
									$Drive_vehicle_Ride['vLicencePlate'] = "CK201";
									$Drive_vehicle_Ride['eStatus'] = "Active";
									$Drive_vehicle_Ride['eCarX'] = "Yes";
									$Drive_vehicle_Ride['eCarGo'] = "Yes";
									$Drive_vehicle_Ride['eType'] = "Ride";	
									$Drive_vehicle_Ride['vCarType'] = $result[0]['countId'];
									$iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle_Ride,'insert');
									$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId."' WHERE iDriverId='".$iDriverId."'";
									$obj->sql_query($sql);

									$query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE (`eType` = 'Ride' OR `eType` = 'Deliver')";
									$result = $obj->MySQLSelect($query);
									$Drive_vehicle_Deliver['iDriverId'] = $iDriverId;
									$Drive_vehicle_Deliver['iCompanyId'] = "1";
									$Drive_vehicle_Deliver['iMakeId'] = "5";
									$Drive_vehicle_Deliver['iModelId'] = "18";
									$Drive_vehicle_Deliver['iYear'] = "2014";
									$Drive_vehicle_Deliver['vLicencePlate'] = "CK201";
									$Drive_vehicle_Deliver['eStatus'] = "Active";
									$Drive_vehicle_Deliver['eCarX'] = "Yes";
									$Drive_vehicle_Deliver['eCarGo'] = "Yes";
									$Drive_vehicle_Deliver['eType'] = "Delivery";	
									$Drive_vehicle_Deliver['vCarType'] = $result[0]['countId'];
									$iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle_Deliver,'insert');
								}
							}
					}
					else
					{
						if(SITE_TYPE=='Demo')
						{
							if($APP_TYPE == 'Delivery'){
								$app_type='Deliver';
							} else {
								$app_type= $APP_TYPE;
							}

							$query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE `eType` = '".$app_type ."'";
							$result = $obj->MySQLSelect($query);
							$Drive_vehicle['iDriverId'] = $iDriverId;
							$Drive_vehicle['iCompanyId'] = "1";
							$Drive_vehicle['iMakeId'] = "5";
							$Drive_vehicle['iModelId'] = "18";
							$Drive_vehicle['iYear'] = "2014";
							$Drive_vehicle['vLicencePlate'] = "CK201";
							$Drive_vehicle['eStatus'] = "Active";
							$Drive_vehicle['eCarX'] = "Yes";
							$Drive_vehicle['eCarGo'] = "Yes";
							$Drive_vehicle['eType'] = $app_type;		
							$Drive_vehicle['vCarType'] = $result[0]['countId'];
							$iDriver_VehicleId=$obj->MySQLQueryPerform('driver_vehicle',$Drive_vehicle,'insert');
							$sql = "UPDATE register_driver set iDriverVehicleId='".$iDriver_VehicleId."' WHERE iDriverId='".$iDriverId."'";
							$obj->sql_query($sql);
						}		
					}				 
					
					$db_sql = "select * from register_driver WHERE iDriverId='".$iDriverId."'";
					$db_user = $obj->MySQLSelect($db_sql);	
					
					$type= base64_encode(base64_encode('driver'));	
					$id = $generalobj->encrypt($iDriverId);
					$newToken = $generalobj->RandomString(32);
					$url = $tconfig["tsite_url"].'reset_password.php?type='.$type.'&id='.$id.'&_token='.$newToken;					
					
					$maildata['EMAIL'] = $femail;
					$maildata['NAME'] = $fbfirstname." ".$fblastname;
					$maildata['PASSWORD'] = '';
					$maildata['SOCIALNOTES'] = $langage_lbl['LBL_SOCIAL_MEDIA_NOTES1_TXT'].'<br>'.$url.'<br>'.$langage_lbl['LBL_SOCIAL_MEDIA_NOTES2_TXT'];	
					
					$generalobj->send_email_user("DRIVER_REGISTRATION_USER",$maildata);

					$Photo_Gallery_folder = $tconfig["tsite_upload_images_driver_path"]."/". $iDriverId . '/';
				   
					@unlink($Photo_Gallery_folder.$db_user[0]['vImage']);
					@unlink($Photo_Gallery_folder."1_".$db_user[0]['vImage']);
					@unlink($Photo_Gallery_folder."2_".$db_user[0]['vImage']);
					@unlink($Photo_Gallery_folder."3_".$db_user[0]['vImage']);   
					@unlink($Photo_Gallery_folder."4_".$db_user[0]['vImage']);   
			
					if(!is_dir($Photo_Gallery_folder))
					{
						mkdir($Photo_Gallery_folder, 0777);
					}
		  
					$baseurl =  $picture;
					$url = strtolower($fbid).".jpg"; //changed by me
					$image_name = $generalobj->copyRemoteFile($baseurl,  $Photo_Gallery_folder.$url);                              
				  
					if(is_file($Photo_Gallery_folder.$url)) {
					 
						list($width, $height, $type, $attr)= getimagesize($Photo_Gallery_folder.$url);           
						if($width < $height){
							$final_width = $width;
						}else{
							$final_width = $height;
						}       
						$img->load($Photo_Gallery_folder.$url)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder.$url);
						$imgname = $generalobj->img_data_upload($Photo_Gallery_folder,$url,$Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"],""); 
					}  
					 
					$sql = "UPDATE register_driver set vImage='".$imgname."',eSignUpType = 'LinkedIn' WHERE iDriverId='".$iDriverId."'";
					$obj->sql_query($sql); 
						 
								
								$_SESSION['oauth_access_token'] = serialize($linkedin->access_token);
					 
								return $generalobj->Checkverification_mobile($db_user[0]['iDriverId'],'driver');
						}
		 
					}
					}catch (Exception $e) {
						$error = $e->getMessage();
						echo $error; exit;
					}
			}else{
							$msg1="Invalid Token";
						$link=$tconfig['tsite_url'].'sign-in.php';
						header("Location:".$link);
						exit;
			}
?>