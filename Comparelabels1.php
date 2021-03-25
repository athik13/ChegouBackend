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
	
	$sql_languageLabels = "SELECT vLabel,vValue from language_label WHERE vCode = 'EN'";
	$labelsData = $obj->MySQLSelect($sql_languageLabels);

	for($i=0;$i<count($labelsData);$i++){
		$origvValue = $labelsData[$i]['vValue'];
		
		$sql_languageLabels_1 = "SELECT vLabel,vValue from language_label_1 WHERE vLabel='".$labelsData[$i]['vLabel']."' AND vCode = 'EN'";
		$labelsData_1 = $obj->MySQLSelect($sql_languageLabels_1);
		
		if(count($labelsData_1)>0){
			$origvLabel_1 = $labelsData_1[0]['vLabel'];
			if($origvValue != $labelsData_1[0]['vValue']){
				$renameQuery = "UPDATE language_label_1 SET vLabel='".$origvLabel_1."_DL' WHERE vLabel='".$origvLabel_1."'";
				//echo "<br/>";
				$obj->MySQLSelect($renameQuery);
			}else{
				$deleteQuery = "DELETE FROM language_label_1 WHERE vLabel ='".$origvLabel_1."'";
				//echo "<br/>";
				$obj->MySQLSelect($deleteQuery);
			}
		}
	}
	
?>