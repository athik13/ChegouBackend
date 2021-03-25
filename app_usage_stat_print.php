<?
include_once('common.php');
if($_REQUEST['todaydate'] != ""){
	$today = $_REQUEST['todaydate'];	
}else{
	$today = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d")-1,date("Y")));
}

$IPSTACK_ACCESS_KEY = "3238ef0e445d2a264f4dda65ae787115";
$Inhouse_IP_List_Arr = array("14.102.161.227","14.102.161.226");
//echo "<pre>";print_r($Inhouse_IP_List_Arr);
## Update countryname and Location from IP Address ##
$sql = "select iMemberLogId,iMemberId, eMemberType, vIP, vLocation,vCountry  from member_log where dDateTime >= '".$today." 00:00:00' and dDateTime <= '".$today." 23:59:59' order by iMemberLogId desc";
$db_ip_update = $obj->MySQLSelect($sql);
for($i=0;$i<count($db_ip_update);$i++){
	$iMemberLogId = $db_ip_update[$i]['iMemberLogId'];
	$iMemberId = $db_ip_update[$i]['iMemberId'];
	$eMemberType = $db_ip_update[$i]['eMemberType'];
	$vIP = $db_ip_update[$i]['vIP'];
	$vLocation = $db_ip_update[$i]['vLocation'];
	$vCountry = $db_ip_update[$i]['vCountry'];
	if($vCountry == ""){
		if(in_array($vIP,$Inhouse_IP_List_Arr)){
			$updateQuery = "UPDATE member_log SET vCountry='V3cube Internet IP',vLocation='V3cube Office Blazenet' WHERE iMemberLogId='" . $iMemberLogId . "'";
            $obj->sql_query($updateQuery);
		}else{
			$sql = "SELECT iMemberLogId,vLocation,vCountry from member_log where vIP = '".$vIP."' and vCountry != '' Order By iMemberLogId desc LIMIT 0,1";
		    $db_repeat_user = $obj->MySQLSelect($sql);
			if(count($db_repeat_user) > 0){
				$existvCountry = cleanString($db_repeat_user[0]["vCountry"]);
				$existvLocation = cleanString($db_repeat_user[0]["vLocation"]);
				$updateQuery = "UPDATE member_log SET vCountry='".$existvCountry."',vLocation='".$existvLocation."' WHERE iMemberLogId='" . $iMemberLogId . "'";
                $obj->sql_query($updateQuery);
			}else{
				$locationfromip = ip_visitor_country($vIP); 
				$vIPlocationCountry = $locationfromip["country_name"];
				$vIPlocationdetails = cleanString($locationfromip["city"]).", ".cleanString($locationfromip["region_name"]).", ".cleanString($locationfromip["country_name"]);
				if($locationfromip["zip"] != ""){
					$vIPlocationdetails .= ", ".$locationfromip["zip"];
				}
				$tApiError = "";
				if($vIPlocationCountry == ""){
					$vIPlocationCountry = "No Location";
					$vIPlocationdetails = "No Location";
					$tApiError = $locationfromip["error"]["info"];
				}
				$updateQuery = "UPDATE member_log SET vCountry='".$vIPlocationCountry."', vLocation='".$vIPlocationdetails."', tApiError = '".$tApiError."' WHERE iMemberLogId='" . $iMemberLogId . "'";
                $obj->sql_query($updateQuery);				
			}
		}	
	}
}
## Update countryname and Location from IP Address ##

$sql = "select iMemberLogId,iMemberId, count(iMemberId) as totlogin, eMemberType, vIP, vLocation, vCountry, tApiError  from member_log where dDateTime >= '".$today." 00:00:00' and dDateTime <= '".$today." 23:59:59' group by iMemberId order by count(iMemberId)";
$db_rec = $obj->MySQLSelect($sql);

for($i=0;$i<count($db_rec);$i++){
	$tot_login = $tot_login+$db_rec[$i]['totlogin'];	
}

function cleanString($str) {
    global $obj;
    $str = trim($str);
    //$str = mysqli_real_escape_string($str);
    $str = $obj->SqlEscapeString($str);
    $str = htmlspecialchars($str);
    $str = strip_tags($str);
    return ($str);
}

function ip_visitor_country($vIP){
	global $IPSTACK_ACCESS_KEY;
	$response = array();
	
	try{
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://api.ipstack.com/".$vIP."?access_key=".$IPSTACK_ACCESS_KEY."&format=1",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_CUSTOMREQUEST => "POST",
		));

		$api_response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($api_response, true);		
	}catch(Exception $error){	
	    $response = json_decode($error, true);
	}

	return $response;

}

function getusername($id,$type){
	global $obj;
	if($type == "Passenger"){
		$sql = "SELECT concat(vName,' ',vLastName) as name, vEmail, vCountry from register_user where iUserId='".$id."'";
		$db_user = $obj->MySQLSelect($sql);
		if($db_user[0]['vEmail'] == "rider@gmail.com"){
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].") <font color='red'>[Demo User]</font>";			
		}else{
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].")";
		}
	}

	if($type == "Driver"){
		$sql = "SELECT concat(vName,' ',vLastName) as name, vEmail, vCountry from register_driver where iDriverId='".$id."'";
		$db_user = $obj->MySQLSelect($sql);
		if($db_user[0]['vEmail'] == "driver@gmail.com"){
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].") <font color='red'>[Demo User]</font>";			
		}else{
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].")";
		}
	}
  
  if($type == "Company"){
		$sql = "SELECT vCompany as name, vEmail, vCountry from company where iCompanyId='".$id."'";
		$db_user = $obj->MySQLSelect($sql);
		if($db_user[0]['vEmail'] == "company@gmail.com"){
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].") <font color='red'>[Demo Company]</font>";			
		}else{
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].")";
		}
	}
  
  if($type == "Hotel"){
		$sql = "SELECT concat(ad.vFirstName,' ',ad.vLastName) as name, ad.vEmail, ad.vCountry from administrators as ad LEFT JOIN hotel as h ON ad.iAdminId = h.iAdminId where ad.iAdminId='".$id."'";
		$db_user = $obj->MySQLSelect($sql);
		if($db_user[0]['vEmail'] == "hotel@demo.com"){
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].") <font color='red'>[Demo Hotel]</font>";			
		}else{
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].")";
		}
	}
  
  if($type == "Admin"){
		$sql = "SELECT concat(vFirstName,' ',vLastName) as name, vEmail, vCountry from administrators where iAdminId='".$id."'";
		$db_user = $obj->MySQLSelect($sql);
		if($db_user[0]['vEmail'] == "demo@demo.com"){
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].") <font color='red'>[Demo Admin]</font>";			
		}else{
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].")";
		}
	}
  
   if($type == "Store"){
		$sql = "SELECT vCompany as name, vEmail, vCountry from company where iCompanyId ='".$id."'";
		$db_user = $obj->MySQLSelect($sql);
		if($db_user[0]['vEmail'] == "company@gmail.com"){
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].") <font color='red'>[Demo Store]</font>";			
		}else{
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].")";
		}
	}
  
  if($type == "Organization"){
		$sql = "SELECT vCompany as name, vEmail, vCountry from organization where iOrganizationId ='".$id."'";
		$db_user = $obj->MySQLSelect($sql);
		if($db_user[0]['vEmail'] == "org@demo.com"){
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].") <font color='red'>[Demo Organization]</font>";			
		}else{
			return $db_user[0]['name']." (".$db_user[0]['vEmail'].")";
		}
	}
}

function getvisitortype($id){
	global $obj, $today;
	$sql = "SELECT count(iMemberLogId) as tot from member_log where iMemberId='".$id."' and dDateTime < '".$today." 00:00:00'";
	$db_memlog = $obj->MySQLSelect($sql);
	if($db_memlog[0]['tot'] > 0){
		return "Returning";
	}else{
		return "New";		
	}
}

function gettotaltrips($id,$type){
	global $obj;
	if($type == "Passenger"){
		$sql = "SELECT count(iTripId) as tot from trips where iUserId='".$id."'";
	}
	if($type == "Driver"){
		$sql = "SELECT count(iTripId) as tot from trips where iDriverId='".$id."'";
	}
	$db_memtrips = $obj->MySQLSelect($sql);
	return $db_memtrips[0]['tot'];
}

function gettodaytrips($id,$type,$today){
	global $obj;
	if($type == "Passenger"){
		$sql = "SELECT count(iTripId) as tot from trips where iUserId='".$id."' AND tTripRequestDate >= '".$today." 00:00:00' and tTripRequestDate <= '".$today." 23:59:59' ";
	}
	if($type == "Driver"){
		$sql = "SELECT count(iTripId) as tot from trips where iDriverId='".$id."' AND tTripRequestDate >= '".$today." 00:00:00' and tTripRequestDate <= '".$today." 23:59:59' ";
	}
	$db_memtrips = $obj->MySQLSelect($sql);
	return $db_memtrips[0]['tot'];
}
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Total Logins/Usage Today: <?=$tot_login?></h2>
<table style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;width: 100%;">
<tr>
	<th style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;">Name</th>
	<th style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;">Type</th>
	<th style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;">IP</th>
	<th style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;">Location</th>
	<th style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;">Login count of Today</th>
	<th style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;">Visitor Type</th>
	<th style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;">Trips Taken Till Now</th>	
  <th style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;">Trips Taken Today</th>	
</tr>
<?for($i=0;$i<count($db_rec);$i++){
	$vCountry = $db_rec[$i]["vCountry"];
	$vLocation = $db_rec[$i]["vLocation"];	
	if($vCountry != ""){
		$vCountryStr = " ( ".$vCountry." ) ";
	}else{
		$vCountryStr = "";
	}
?>
<tr>
	<td style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;"><?echo getusername($db_rec[$i]['iMemberId'],$db_rec[$i]['eMemberType']);?></td>
	<td style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;"><?=$db_rec[$i]['eMemberType'];?></td>
	<td style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;"><?=$db_rec[$i]['vIP'].$vCountryStr;?></td>
	<td style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;"><?=$vLocation;?></td>
	<td style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;"><?=$db_rec[$i]['totlogin'];?></td>
	<td style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;"><?=getvisitortype($db_rec[$i]['iMemberId'])?></td>
	<td style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;"><?echo gettotaltrips($db_rec[$i]['iMemberId'],$db_rec[$i]['eMemberType']);?></td>
  <td style="border: 1px solid #ddd;text-align: left;border-collapse: collapse;padding: 10px;"><?echo gettodaytrips($db_rec[$i]['iMemberId'],$db_rec[$i]['eMemberType'],$today);?></td>
</tr>
<?}?>
</table>
</body>
</html>

