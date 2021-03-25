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

$dataLblArr=array();
$lCode = "EN";

// Checking For Live Language Label Table
$sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label` WHERE  `vCode` = '" . $lCode . "'";
$data = $obj->MySQLSelect($sql);
?>
<table style="font-size: 20px;" cellspacing="5" cellpadding="5" width="100%" border="1">
<tr>
		<th width="100%" colspan="2">Cubejek Language Label and Language_Label_1</th>
</tr>
</table>
<table style="font-size: 18px;" cellspacing="5" cellpadding="5" width="100%" border="1">
    <tr>
		  	<th width="10%">Sr No.</th>
        <th width="45%">Cubejek Language Label Name/Value</th>
				<th width="45%">Cubejek Language Label 1 Name/Value</th>
		</tr>

<?
$j = 1;
$TotalChanges = 0;
for($i=0;$i<count($data);$i++){
  // echo $data[$i]['vLabel']." - ".$data[$i]['vValue'];
  $vLabel = $data[$i]['vLabel'];
  $vValue = $data[$i]['vValue'];
  $vValue_cmp = strtolower($vValue);                                    
  $sql = "SELECT  vLabel as LanguageLabel1 , vValue as LanguageValue1  FROM  `language_label_1` WHERE  `vCode` = '" . $lCode . "' AND vLabel = '".$vLabel."' ";
  $data_live = $obj->MySQLSelect($sql);
  if(count($data_live) > 0){
     $vLiveLabel = $data_live[0]['LanguageLabel1'];
     $vLiveValue = $data_live[0]['LanguageValue1'];
     $vLiveValue_cmp = strtolower($vLiveValue);
     if($vValue_cmp != $vLiveValue_cmp){
     //if(strcmp($vValue_cmp, $vLiveValue_cmp) < 0){
        $textdifference = "Yes";
        $TotalChanges = $TotalChanges+1;
     }else{
        //$sql = "DELETE FROM language_label_1 WHERE `vCode` = '" . $lCode . "' AND vLabel = '".$vLabel."'";
		    //$obj->sql_query($sql);
        $textdifference = "No";
     }
?>
		<tr <?if($textdifference == "Yes"){ ?> style="color:black;background:red;font-weight:bold;" <?}?>>
        <td><?=$j;?></td>
		  	<td><?=$vLabel;?> >>  <br /> <?=$vValue?></td>
				<td><?=$vLiveLabel;?> >>  <br /> <?=$vLiveValue?></td>
		</tr>
<?   
     $j++;  
  }
} 
?>
</table>
<?
echo "<br />";
echo "Total Changes of Labels >> ".$TotalChanges;
?>
<?
// Checking For Live Language Label Other Table
$sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_cubejek` WHERE  `vCode` = '" . $lCode . "' UNION SELECT `vLabel` , `vValue`  FROM  `language_label_other_cubejek` WHERE  `vCode` = '" . $lCode . "' ";
$data = $obj->MySQLSelect($sql);
?>
<table style="font-size: 15px;" cellspacing="5" cellpadding="5" width="100%" border="1">
<tr>
		  	<th width="100%" colspan="2">Live Language Label Other</th>
</tr>
</table>
<table style="font-size: 15px;" cellspacing="5" cellpadding="5" width="100%" border="1">
		<tr>
		  	<th width="10%">Sr No.</th>
        <th width="45%">Cubejek Label Name/Value</th>
				<th width="45%">Live Label Name/Value</th>
		</tr>

<?
$j = 1;
for($i=0;$i<count($data);$i++){
  // echo $data[$i]['vLabel']." - ".$data[$i]['vValue'];
  $vLabel = $data[$i]['vLabel'];
  $vValue = $data[$i]['vValue'];
  $vValue_cmp = strtolower($vValue);                                    
  $sql = "SELECT  vLabel as LiveLabel , vValue as LiveValue  FROM  `language_label_other_live` WHERE  `vCode` = '" . $lCode . "' AND vLabel = '".$vLabel."' ";
  $data_live = $obj->MySQLSelect($sql);
  if(count($data_live) > 0){
     $vLiveLabel = $data_live[0]['LiveLabel'];
     $vLiveValue = $data_live[0]['LiveValue'];
     $vLiveValue_cmp = strtolower($vLiveValue);
     //if($vValue != $vLiveValue_cmp){
     if(strcmp($vValue_cmp, $vLiveValue_cmp) < 0){
?>
		<tr>
        <td><?=$j;?></td>
		  	<td><?=$vLabel;?> >>  <br />  <?=$vValue?></td>
				<td><?=$vLiveLabel;?> >>  <br /> <?=$vLiveValue?></td>
		</tr>
<?   
     $j++;  
     }
  }
} 
?>
</table>