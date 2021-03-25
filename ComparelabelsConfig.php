<?php
	include_once('include_taxi_webservices.php');
	include_once(TPATH_CLASS.'configuration.php');
	
	require_once('assets/libraries/stripe/config.php');
	require_once('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
	require_once('assets/libraries/pubnub/autoloader.php');
	include_once(TPATH_CLASS .'Imagecrop.class.php');
	include_once(TPATH_CLASS .'twilio/Services/Twilio.php');
	include_once('generalFunctions.php');
	include_once('send_invoice_receipt.php');
	
	$sql_config_food = "SELECT * from configurations";
	$data_config_food = $obj->MySQLSelect($sql_config_food);
  echo "Remove Exit";exit;
  $j=0;
  $k=0;
	for($i=0;$i<count($data_config_food);$i++){
		$vName_tmp = $data_config_food[$i]['vName'];
    $vName_tmp = trim($vName_tmp);
    $eInputType = $data_config_food[$i]['eInputType'];
    $eStatus = $data_config_food[$i]['eStatus'];
    $eZeroAllowed = $data_config_food[$i]['eZeroAllowed'];
    $eSpaceAllowed = $data_config_food[$i]['eSpaceAllowed'];
    $eDoubleValueAllowed = $data_config_food[$i]['eDoubleValueAllowed'];
    $tSelectVal = $data_config_food[$i]['tSelectVal'];
    $eAdminDisplay = $data_config_food[$i]['eAdminDisplay'];
    $eRequireField = $data_config_food[$i]['eRequireField'];
    $eConfigRequired = $data_config_food[$i]['eConfigRequired'];
    $vOrder = $data_config_food[$i]['vOrder']; 
		
		$sql_config = "SELECT * from configurations_cubejek WHERE vName='".$vName_tmp."'";
		$data_config = $obj->MySQLSelect($sql_config);
		
		if(count($data_config) == 0){
			//echo "Name==".$vName_tmp;echo "<BR/><BR/>";
			
			//echo $sql_insert_query = "INSERT INTO `configurations_live`(`tDescription`, `vName`, `vValue`, `vOrder`, `eType`, `eStatus`, `tHelp`, `eInputType`, `eZeroAllowed`, `eSpaceAllowed`, `eDoubleValueAllowed`, `tSelectVal`, `eAdminDisplay`, `eRequireField`, `eConfigRequired`) VALUES ('".$data_config_food[$i]['tDescription']."','".$data_config_food[$i]['vName']."','".$data_config_food[$i]['vValue']."','".$data_config_food[$i]['vOrder']."','Store Settings','".$data_config_food[$i]['eStatus']."','".$data_config_food[$i]['tHelp']."','".$data_config_food[$i]['eInputType']."','".$data_config_food[$i]['eZeroAllowed']."','".$data_config_food[$i]['eSpaceAllowed']."','".$data_config_food[$i]['eDoubleValueAllowed']."','".$data_config_food[$i]['tSelectVal']."','".$data_config_food[$i]['eAdminDisplay']."','".$data_config_food[$i]['eRequireField']."','".$data_config_food[$i]['eConfigRequired']."');";
			array_shift($data_config_food[$i]);
      $Data_Insert = array();
      $Data_Insert = $data_config_food[$i];
      //$Data_Insert_id = $obj->MySQLQueryPerform("configurations_cubejek",$Data_Insert,'insert');  // Insert Food Menu Items
		  //echo "<pre>";print_r($Data_Insert);
			//echo "<BR/><BR/>";
      $j++;
		}else{
      //echo "<pre>";print_r($data_config);
      $iSettingId = $data_config[0]['iSettingId'];
      $where = " iSettingId = '".$iSettingId."'";
      $Data_Update = array();
      $Data_Update['eInputType'] = $eInputType;
      $Data_Update['eStatus'] = $eStatus;
      $Data_Update['eZeroAllowed'] = $eZeroAllowed;
      $Data_Update['eSpaceAllowed'] = $eSpaceAllowed;
      $Data_Update['eDoubleValueAllowed'] = $eDoubleValueAllowed;
      $Data_Update['tSelectVal'] = $tSelectVal;
      $Data_Update['eAdminDisplay'] = $eAdminDisplay;
      $Data_Update['eRequireField'] = $eRequireField;
      $Data_Update['eConfigRequired'] = $eConfigRequired;
      $Data_Update['vOrder'] = $vOrder;        
      //$updateid = $obj->MySQLQueryPerform("configurations_cubejek",$Data_Update,'update',$where);
           
      if($updateid > 0){
        $strid .=  $iSettingId.",";
        $k++;
      }
    }
	}
  $strid = substr($strid,0,-1);
  echo "Total Inserted Records >> ".$j;echo "<BR/><BR/>";
  echo "Total Updated Records >> ".$k;echo "<BR/><BR/>";
  //echo "Updated iSettingId are >> ".$strid;echo "<BR/><BR/>";   configurations_cubejek
?>