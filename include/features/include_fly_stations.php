<?php
if($type=='getNearestFlyStations') {
	global $FLY_RADIUS;

	$address = isset($_REQUEST['address']) ? clean($_REQUEST['address']) : '';
	$lattitude = isset($_REQUEST['lattitude']) ? clean($_REQUEST['lattitude']) : '';
	$longitude = isset($_REQUEST['longitude']) ? clean($_REQUEST['longitude']) : '';
	$iLocationId = isset($_REQUEST['iLocationId']) ? clean($_REQUEST['iLocationId']) : '';
	$GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? clean($_REQUEST['GeneralMemberId']) : '';

	$vLatitude = 'tCentroidLattitude';
	$vLongitude = 'tCentroidLongitude';
	
	
	//$unit = "0.621371";
	$data_unit = $obj->MySQLSelect("SELECT eUnit,vLang FROM register_user as r, country as c WHERE r.vCountry = c.vCountryCode AND iUserId = ".$GeneralMemberId);
	
	$userlangcode = $data_unit[0]['vLang'];
	if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');  
    }
	$languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
	
	if(isset($data_unit[0]['eUnit']) && $data_unit[0]['eUnit'] == 'Miles') {
		$unit = 0.621371;
		$unit_field = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
	} else {
		$unit = 1;
		$unit_field = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
	}
	
	$ssql = '';
	$FLY_RADIUS_VAR = $FLY_RADIUS;
	
	if(SITE_TYPE == 'Demo') {
		//$sql = " OR eSiteDemo = 'Yes'";
		$FLY_RADIUS_VAR = '15000';
		$ssql = " LIMIT 0, 20";
	}

 	$sql_fly = "SELECT ROUND(( 6371 * acos( cos( radians(" . $lattitude . ") )
					* cos( radians( ROUND(" . $vLatitude . ",8) ) )
					* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $longitude . ") )
					+ sin( radians(" . $lattitude . ") )
					* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance,  location_master.iLocationId , location_master.vLocationName,location_master.vLocationAddress,location_master.tCentroidLattitude,location_master.tCentroidLongitude,eSiteDemo   FROM `location_master`
					WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' AND eStatus='Active' AND iLocationId != '".$iLocationId."') HAVING distance < " . $FLY_RADIUS_VAR ."  ORDER BY distance ASC". " ".$ssql;

	$db_flydata = $obj->MySQLSelect($sql_fly);
	
	foreach($db_flydata as $key=>$val) {
		$db_flydata[$key]['distance'] = round(($val['distance'] * $unit),2) ." ".$unit_field;
	}
	
	if(!empty($db_flydata)) { 
		$returnArr['Action'] = 1;
		$returnArr['message'] = $db_flydata;
	} else {
		$returnArr['Action'] = 0;
		$returnArr['message'] = 'LBL_NOSTATIONS_AVAILABLE';
	}
	//print_R($returnArr); exit;
	setDataResponse($returnArr);
}
function getFareForFlyVehicles($iVehicleTypeId,$iFromStationId,$iToStationId) {
	global $obj;
	$sql_flyfare = "select * from fly_location_wise_fare where iVehicleTypeId = ".$iVehicleTypeId." and iFromLocationId = ".$iFromStationId." and iToLocationId = ".$iToStationId;
    $flyfare_data = $obj->MySQLSelect($sql_flyfare);
	return $flyfare_data[0]['fFlatfare'];
}
//its used for website manual booking only start
if($type=='getNearestFlyStations_booking') {
	$data = isset($_REQUEST['data']) ? $_REQUEST['data'] : '';
	$iLocationId = isset($_REQUEST['iLocationId']) ? $_REQUEST['iLocationId'] : '';
	$fromto = isset($_REQUEST['fromto']) ? $_REQUEST['fromto'] : "to";
	$data_arr = json_decode($data,true);
	foreach($data_arr['message'] as $key=>$value) {
		$selected = '';
        if ($value['iLocationId'] == $iLocationId) {
            //$selected = "selected=selected";
            $selected = "checked = checked";
        }
		$location = $value['vLocationName'];
		if(!empty($value['vLocationAddress'])) {
			$location .= "-".$value['vLocationAddress'];
		} ?>
		<li>
                <label class='veh-left cursor-pointer'>
                    <div class='radio-main'>
                        <span class='radio-hold'>
                            <input type='radio' name='iLocationId' required onChange="savelocationid(this.value,'<?= $fromto; ?>'); triggerflystations('<?= $fromto; ?>');" value="<?= $value['iLocationId'] ?>" <?=$selected ?>>
                            <span class='radio-button'></span>
                        </span>
                    </div>
					<i class='vehicle-ico'></i>
					<span class='vehicle-name'><?= $location ?></span>
                </label>
                <div class='price-caption'>
                    <strong></strong>
                </div>
            </li>
	<?php }
	exit;
}

if($type=='getNearestFlyStationsSectionBooking') {
	
	global $FLY_RADIUS;

	$address = isset($_REQUEST['address']) ? clean($_REQUEST['address']) : '';
	$from_lattitude = isset($_REQUEST['from_lattitude']) ? clean($_REQUEST['from_lattitude']) : '';
	$lattitude = isset($_REQUEST['lattitude']) ? clean($_REQUEST['lattitude']) : '';
	$from_longitude = isset($_REQUEST['from_longitude']) ? clean($_REQUEST['from_longitude']) : '';
	$longitude = isset($_REQUEST['longitude']) ? clean($_REQUEST['longitude']) : '';
	$iLocationId = isset($_REQUEST['iLocationId']) ? clean($_REQUEST['iLocationId']) : '';
	$iFromStationId = isset($_REQUEST['iFromStationId']) ? clean($_REQUEST['iFromStationId']) : '';
	$iToStationId = isset($_REQUEST['iToStationId']) ? clean($_REQUEST['iToStationId']) : '';
	$fromlang = isset($_REQUEST['fromlang']) ? clean($_REQUEST['fromlang']) : '';
	$tolang = isset($_REQUEST['tolang']) ? clean($_REQUEST['tolang']) : '';
	$GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? clean($_REQUEST['GeneralMemberId']) : '';

	$vLatitude = 'tCentroidLattitude';
	$vLongitude = 'tCentroidLongitude';
	
	$data_unit = $obj->MySQLSelect("SELECT eUnit,vLang FROM register_user as r, country as c WHERE r.vCountry = c.vCountryCode AND iUserId = ".$GeneralMemberId);
	
	$userlangcode = $data_unit[0]['vLang'];
	if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');  
    }
	$languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
	
	if(isset($data_unit[0]['eUnit']) && $data_unit[0]['eUnit'] == 'Miles') {
		$unit = 0.621371;
		$unit_field = $languageLabelsArr['LBL_MILE_DISTANCE_TXT'];
	} else {
		$unit = 1;
		$unit_field = $languageLabelsArr['LBL_KM_DISTANCE_TXT'];
	}
	
	$sql = '';
	if(SITE_TYPE == 'Demo') {
		$sql = " OR eSiteDemo = 'Yes'";
	}

 	$sql_fly = "SELECT ROUND(( 6371 * acos( cos( radians(" . $from_lattitude . ") )
					* cos( radians( ROUND(" . $vLatitude . ",8) ) )
					* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $from_longitude . ") )
					+ sin( radians(" . $from_lattitude . ") )
					* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance,  location_master.*   FROM `location_master`
					WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' AND eStatus='Active' AND iLocationId != '".$iToStationId."') HAVING distance < " . $FLY_RADIUS . " ".$sql." ORDER BY distance ASC";
	$db_flydata = $obj->MySQLSelect($sql_fly);
	
	foreach($db_flydata as $key=>$val) {
		$db_flydata[$key]['distance'] = round(($val['distance'] * $unit),2) ." ".$unit_field;
	}
	
	if(!empty($db_flydata)) {
	
	$fromto = 'from';
	?>
	<div style="margin-bottom:10px"><?php echo $fromlang; ?></div>
	<select name="iFromLocationIdStations" id="iFromLocationIdStations" onChange="savelocationid(this.value,'<?= $fromto; ?>',1);"><?php
	foreach($db_flydata as $key=>$value) {
		$selected = '';
        if ($value['iLocationId'] == $iFromStationId) {
            $selected = "selected=selected";
            //$selected = "checked = checked";
        }
		$location = $value['vLocationName'];
		
		if(!empty($value['vLocationAddress'])) {
			$location .= "-".$value['vLocationAddress'];
		} ?>
		
			<option value="<?= $value['iLocationId'] ?>" <?=$selected ?> data-lat="<?= $value['tLatitude'] ?>" data-lng="<?= $value['tLongitude'] ?>"><?= $location ?></option>
	<?php } ?></select><?php
	}
	$sql_fly = "SELECT ROUND(( 6371 * acos( cos( radians(" . $lattitude . ") )
					* cos( radians( ROUND(" . $vLatitude . ",8) ) )
					* cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $longitude . ") )
					+ sin( radians(" . $lattitude . ") )
					* sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance,  location_master.*   FROM `location_master`
					WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' AND eStatus='Active' AND iLocationId != '".$iFromStationId."') HAVING distance < " . $FLY_RADIUS . " ".$sql." ORDER BY distance ASC";
	$db_flydata = $obj->MySQLSelect($sql_fly);
	
	foreach($db_flydata as $key=>$val) {
		$db_flydata[$key]['distance'] = round(($val['distance'] * $unit),2) ." ".$unit_field;
	}
	
	if(!empty($db_flydata)) {
	
	$fromto = 'to';
	?><br><br><div style="margin-bottom:10px"><?php echo $tolang; ?></div><select name="iToLocationIdStations" id="iToLocationIdStations" onChange="savelocationid(this.value,'<?= $fromto; ?>',1);"><?php
	foreach($db_flydata as $key=>$value) {
		$selected = '';
        if ($value['iLocationId'] == $iToStationId) {
            $selected = "selected=selected";
            //$selected = "checked = checked";
        }
		
		$location = $value['vLocationName'];
		if(!empty($value['vLocationAddress'])) {
			$location .= "-".$value['vLocationAddress'];
		} ?>
		<option value="<?= $value['iLocationId'] ?>" <?=$selected ?> data-lat="<?= $value['tLatitude'] ?>" data-lng="<?= $value['tLongitude'] ?>"><?= $location ?></option>
	<?php } ?></select><?php
	}
	exit;
}
//its used for website manual booking only end


/*if($type=='getNearestFlyStations1') {
    $PickUpAddress = isset($_REQUEST['PickUpAddress']) ? clean($_REQUEST['PickUpAddress']) : '';
    $lattitude = isset($_REQUEST['lattitude']) ? clean($_REQUEST['lattitude']) : '';
    $longitude = isset($_REQUEST['longitude']) ? clean($_REQUEST['longitude']) : '';
	
	$sourcelatArr = array($lattitude,$longitude);
	$sql = "select * from `location_master` where eFor = 'FlyStation' and eStatus='Active'";
    $db_flydata = $obj->MySQLSelect($sql);
	foreach($db_flydata as $key=>$value) {
		$latArr = explode(',',$value['tLatitude']);
		$longArr = explode(',',$value['tLongitude']);
		$latlongArr = array();
		foreach($latArr as $key1=>$value1) {
			if(!empty($value1)) {
				$lattitudeArr[$key][$key1][] = $value1;
				$lattitudeArr[$key][$key1][] = $longArr[$key1];
			}
		}
		
		//print_R($lattitudeArr[$key]); echo "aaaa";
		$polygon = new stdClass();
		$latlongArr[] = $lattitudeArr[$key];
		$polygon->rings = $latlongArr;	
		$a = getCentroidOfPolygon($polygon);
		echo '<pre>';print_R($a);
		
		//echo $sql_fly = "SELECT * , (3956 * 2 * ASIN(SQRT( POWER(SIN(( $lattitude - $a[0]) *  pi()/180 / 2), 2) +COS( $lattitude * pi()/180) * COS($a[0] * pi()/180) * POWER(SIN(( $longitude - $a[1]) * pi()/180 / 2), 2) ))) as distance from location_master WHERE eFor = 'FlyStation' and eStatus = 'Active' having  distance <= 10 order by distance";
		//exit;
		
		//echo $from_source_addresss;
		echo "==================================================<br>";
	}
	
	echo $sql_fly = "SELECT * , (3956 * 2 * ASIN(SQRT( POWER(SIN(( $lattitude - tcLattitude) *  pi()/180 / 2), 2) +COS( $lattitude * pi()/180) * COS(tcLattitude * pi()/180) * POWER(SIN(( $longitude - tcLongitude) * pi()/180 / 2), 2) ))) as distance from location_master WHERE eFor = 'FlyStation' having  distance <= 10 order by distance";

    $db_flydata = $obj->MySQLSelect($sql_fly);
	echo '<pre>';
	print_R($db_flydata); 
	exit;

}

function getCentroidOfPolygon($geometry) {
    $cx = 0;
    $cy = 0;

    for ($ri=0, $rl=sizeof($geometry->rings); $ri<$rl; $ri++) {
        $ring = $geometry->rings[$ri];

        for ($vi=0, $vl=sizeof($ring); $vi<$vl; $vi++) {
            $thisx = $ring[ $vi ][0];
            $thisy = $ring[ $vi ][1];
            $nextx = $ring[ ($vi+1) % $vl ][0];
            $nexty = $ring[ ($vi+1) % $vl ][1];

            $p = ($thisx * $nexty) - ($thisy * $nextx);
            $cx += ($thisx + $nextx) * $p;
            $cy += ($thisy + $nexty) * $p;
        }
    }

    // last step of centroid: divide by 6*A
    $area = getAreaOfPolygon($geometry);
    $cx = -$cx / ( 6 * $area);
    $cy = -$cy / ( 6 * $area);

    // done!
    return array($cx,$cy);
}
function getAreaOfPolygon($geometry) {
    $area = 0;
    for ($ri=0, $rl=sizeof($geometry->rings); $ri<$rl; $ri++) {
        $ring = $geometry->rings[$ri];

        for ($vi=0, $vl=sizeof($ring); $vi<$vl; $vi++) {
            $thisx = $ring[ $vi ][0];
            $thisy = $ring[ $vi ][1];
            $nextx = $ring[ ($vi+1) % $vl ][0];
            $nexty = $ring[ ($vi+1) % $vl ][1];
            $area += ($thisx * $nexty) - ($thisy * $nextx);
        }
    }

    // done with the rings: "sign" the area and return it
    $area = abs(($area / 2));
    return $area;
}*/
?>
