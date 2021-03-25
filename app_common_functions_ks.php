<?php

/* start to clean function */
function setDataResponse($responseArr) {
    global $dataHelperObj, $websocket, $obj;
    if (!empty($websocket)) {
        $websocket->close();
    }

    /* Create a log of request/Response of all api */
    if (!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == "192.168.1.131" && !empty($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], 'cubejekdev') !== false) == true && isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
        $request_data_param = $obj->SqlEscapeString(json_encode($_REQUEST));
        $response_data_param = $obj->SqlEscapeString(json_encode($responseArr));

        $data_req = $obj->MySQLSelect("SELECT * FROM request_data WHERE tType = '" . $_REQUEST['type'] . "'");
        $tTitle = $tDescription = $tPurpose = $tCallToAction = $tResponse = $tErrorResponse = "";

        if (!empty($data_req) && count($data_req) > 0) {
            $tTitle = $obj->SqlEscapeString($data_req[0]['tTitle']);
            $tDescription = $obj->SqlEscapeString($data_req[0]['tDescription']);
            $tPurpose = $obj->SqlEscapeString($data_req[0]['tPurpose']);
            $tCallToAction = $obj->SqlEscapeString($data_req[0]['tCallToAction']);
            $tResponse = $obj->SqlEscapeString($data_req[0]['tResponse']);
            $tErrorResponse = $obj->SqlEscapeString($data_req[0]['tErrorResponse']);
        }

        if (isset($responseArr['Action']) && $responseArr['Action'] == "0") {
            $tErrorResponse = $obj->SqlEscapeString(json_encode($responseArr));
        } else {
            $tResponse = $obj->SqlEscapeString(json_encode($responseArr));
        }

        $sql_request_data = "REPLACE INTO request_data (`tTitle`, `tType`, `tDescription`, `tPurpose`, `tCallToAction`, `tRequestParam`, `tResponse`, `tErrorResponse`) VALUES('" . $tTitle . "', '" . $_REQUEST['type'] . "', '" . $tDescription . "','" . $tPurpose . "','" . $tCallToAction . "','" . $request_data_param . "','" . $tResponse . "','" . $tErrorResponse . "')";
        $obj->sql_query($sql_request_data);
    }
    /* Create a log of request/Response of all api */

    if (!empty($obj)) {
        $obj->MySQLClose();
    }

    $dataHelperObj->setResponse($responseArr);
}

function clean($str) {
    global $obj;
    $str = trim($str);
    // $str = mysqli_real_escape_string($str);
    $str = $obj->SqlEscapeString($str);
    $str = htmlspecialchars($str);
    $str = strip_tags($str);
    return ($str);
}

/* End to clean function */

/* get vLangCode as per member or if member not found check lcode and then defualt take lang code set at $lang_label */

function getLanguageCode($memberId = '', $lcode = '') {
    global $lang_label, $lang_code, $obj;
    /* find vLanguageCode using member id */
    if ($memberId != '') {
        $sql = "SELECT  `vLanguageCode` FROM  `member` WHERE iMemberId = '" . $memberId . "' AND `eStatus` = 'Active' ";
        $get_vLanguageCode = $obj->MySQLSelect($sql);
        if (count($get_vLanguageCode) > 0)
            $lcode = (isset($get_vLanguageCode[0]['vLanguageCode']) && $get_vLanguageCode[0]['vLanguageCode'] != '') ? $get_vLanguageCode[0]['vLanguageCode'] : '';
    }
    /* find default language of website set by admin */
    if ($lcode == '') {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);
        $lcode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }
    $lang_code = $lcode;
    $sql = "SELECT  `vLabel` ,  `vValue`  FROM  `language_label`  WHERE  `vCode` = '" . $lcode . "' ";
    $all_label = $obj->MySQLSelect($sql);
    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $lang_label[$vLabel] = $vValue;
    }
}

/* End function */

#function to get value from table can be use for any table - create to get value from configuration
#$check_phone = get_value('configurations', 'vValue', 'vName', 'PHONE_VERIFICATION_REQUIRED');

/* Start get value */

function get_value($table, $field_name, $condition_field = '', $condition_value = '', $setParams = '', $directValue = '') {
    global $obj;
    $returnValue = array();
    $where = ($condition_field != '') ? ' WHERE ' . clean($condition_field) : '';
    $where .= ($where != '' && $condition_value != '') ? ' = "' . clean($condition_value) . '"' : '';
    if ($table != '' && $field_name != '' && $where != '') {
        $sql = "SELECT $field_name FROM  $table $where";
        if ($setParams != '') {
            $sql .= $setParams;
        }
        $returnValue = $obj->MySQLSelect($sql);
    } else if ($table != '' && $field_name != '') {
        $sql = "SELECT $field_name FROM  $table";
        if ($setParams != '') {
            $sql .= $setParams;
        }
        $returnValue = $obj->MySQLSelect($sql);
    }
    if ($directValue == '') {
        return $returnValue;
    } else {
        $temp = "";
        if (isset($returnValue[0][$field_name])) {
            $temp = $returnValue[0][$field_name];
        }
        return $temp;
    }
}
/* End get value */
function dateDifference($date_1, $date_2, $differenceFormat = '%a') {
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
    $interval = date_diff($datetime1, $datetime2);
    return $interval->format($differenceFormat);
}

function getVehicleTypes($cityName = "") {
    global $obj;
    $sql_vehicle_type = "SELECT * FROM vehicle_type";
    $row_result_vehivle_type = $obj->MySQLSelect($sql_vehicle_type);
    return $row_result_vehivle_type;
}

function paymentimg($paymentm) {
    global $tconfig;
    if ($paymentm == "Card") {
        // return "webimages/icons/payment_images/ic_payment_type_card.png";
        return $tconfig["tsite_url"] . "webimages/icons/payment_images/ic_payment_type_card.png";
    } else if ($paymentm == "Organization") {
        return $tconfig["tsite_url"] . "webimages/icons/payment_images/ic_payment_type_org.png";
    } else {
        // return "webimages/icons/payment_images/ic_payment_type_cash.png";
        return $tconfig["tsite_url"] . "webimages/icons/payment_images/ic_payment_type_cash.png";
    }
}

function ratingmark($ratingval) {
    global $tconfig;
    $a = $ratingval;
    $b = explode('.', $a);
    $c = $b[0];
    $str = "";
    $count = 0;
    for ($i = 0; $i < 5; $i++) {
        if ($c > $i) {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Full.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
        } elseif ($a > $c && $count == 0) {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Half-Full.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
            $count = 1;
        } else {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-blank.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
        }
    }

    return $str;
}

function getVehicleFareConfig($tabelName, $vehicleTypeID) {
    global $obj;
    $sql = "SELECT * FROM `" . $tabelName . "` WHERE iVehicleTypeId='$vehicleTypeID'";
    $Data_fare = $obj->MySQLSelect($sql);
    return $Data_fare;
}

function processTripsLocations($tripId, $latitudes, $longitudes) {
    global $obj;
    $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
    $DataExist = $obj->MySQLSelect($sql);
    if (count($DataExist) > 0) {
        $latitudeList = $DataExist[0]['tPlatitudes'];
        $longitudeList = $DataExist[0]['tPlongitudes'];
        if ($latitudeList != '') {
            $data_latitudes = $latitudeList . ',' . $latitudes;
        } else {
            $data_latitudes = $latitudes;
        }
        if ($longitudeList != '') {
            $data_longitudes = $longitudeList . ',' . $longitudes;
        } else {
            $data_longitudes = $longitudes;
        }
        $where = " iTripId = '" . $tripId . "'";
        $Data_tripsLocations['tPlatitudes'] = $data_latitudes;
        $Data_tripsLocations['tPlongitudes'] = $data_longitudes;
        $id = $obj->MySQLQueryPerform("trips_locations", $Data_tripsLocations, 'update', $where);
    } else {
        $sql = "SELECT tStartLat,tStartLong FROM `trips` WHERE iTripId = '$tripId'";
        $TripData = $obj->MySQLSelect($sql);
        $tStartLat = $TripData[0]['tStartLat'];
        $tStartLong = $TripData[0]['tStartLong'];
        if ($latitudes != "") {
            $insertlat = $tStartLat . "," . $latitudes;
        } else {
            $insertlat = $tStartLat;
        }
        if ($longitudes != "") {
            $insertlong = $tStartLong . "," . $longitudes;
        } else {
            $insertlong = $tStartLong;
        }
        $Data_trips_locations['iTripId'] = $tripId;
        $Data_trips_locations['tPlatitudes'] = $insertlat;
        $Data_trips_locations['tPlongitudes'] = $insertlong;
        $id = $obj->MySQLQueryPerform("trips_locations", $Data_trips_locations, 'insert');
    }
    return $id;
}

function calcluateTripDistance($tripId) {
    global $obj, $GOOGLE_SEVER_API_KEY_WEB, $FILTER_ROUTE_RAW_DATA;
    $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
    $Data_tripsLocations = $obj->MySQLSelect($sql);
    $TotalDistance = 0;
    $arrOfLocations = array();
    if (count($Data_tripsLocations) > 0) {
        $trip_path_latitudes = $Data_tripsLocations[0]['tPlatitudes'];
        $trip_path_longitudes = $Data_tripsLocations[0]['tPlongitudes'];
        $trip_path_latitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_latitudes);
        $trip_path_longitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_longitudes);
        $TripPathLatitudes = explode(",", $trip_path_latitudes);
        $TripPathLongitudes = explode(",", $trip_path_longitudes);

        /*********************** Refine Raw Data *************************** */
		if(strtoupper($FILTER_ROUTE_RAW_DATA) == "YES" && !empty($TripPathLatitudes) && !empty($TripPathLongitudes) && count($TripPathLatitudes) > 0 && count($TripPathLongitudes) > 0){
			$sql_snap_locations = "SELECT * FROM `trips_route_locations` WHERE iTripId = '$tripId' AND eType='SnapToRoad'";
			$Data_snap_locations = $obj->MySQLSelect($sql_snap_locations);

			if (!empty($Data_snap_locations) && count($Data_snap_locations) > 0) {
				$TripPathLatitudes = explode(",", $Data_snap_locations[0]['tPlatitudes']);
				$TripPathLongitudes = explode(",", $Data_snap_locations[0]['tPlongitudes']);
			} else {
				$snap_route_latitudes_arr = $TripPathLatitudes;
				$snap_route_longitudes_arr = $TripPathLongitudes;

				$snap_route_locations_arr = array();

				if (count($snap_route_latitudes_arr) > 0) {

					if (count($snap_route_latitudes_arr) > 100) {
						for ($i = 0; $i < count($snap_route_latitudes_arr); $i++) {
							$currentLatitude = $snap_route_latitudes_arr[$i];
							
							$currentLongitude = $snap_route_longitudes_arr[$i];

							$last_added_location_arr = explode(",", $snap_route_locations_arr[count($snap_route_locations_arr) - 1]);
							$last_added_location_latitude = $last_added_location_arr[0];
							$last_added_location_longitude = $last_added_location_arr[1];

							if (empty($snap_route_locations_arr) || distanceByLocation($last_added_location_latitude, $last_added_location_longitude, $currentLatitude, $currentLongitude, "K") > 0.020) {
								$snap_route_locations_arr[] = $snap_route_latitudes_arr[$i] . "," . $snap_route_longitudes_arr[$i];
							}
						}
					} else {
						for ($i = 0; $i < count($snap_route_latitudes_arr); $i++) {
							$snap_route_locations_arr[] = $snap_route_latitudes_arr[$i] . "," . $snap_route_longitudes_arr[$i];
						}
					}
				}

				$snap_route_locations_chunk_arr = array_chunk($snap_route_locations_arr, 99);
				
				
				if(!empty($snap_route_locations_arr) && count($snap_route_locations_arr) > 0){
					$lat_array = array();
					$long_array = array();
					$location_path_arr = array();
					foreach ($snap_route_locations_chunk_arr as $snap_route_locations_chunk_arr_tmp) {
						$snap_route_locations_str = implode("|", $snap_route_locations_chunk_arr_tmp);

						$location_path_arr[] = $snap_route_locations_str;

						$snap_to_road_api = "https://roads.googleapis.com/v1/snapToRoads?path=" . $snap_route_locations_str . "&interpolate=true&key=" . $GOOGLE_SEVER_API_KEY_WEB;
						
						$snap_to_road_result = file_get_contents($snap_to_road_api);

						if (!empty($snap_to_road_result)) {

							$snap_to_road_result_json = json_decode($snap_to_road_result);
							$snappedPointsArr = $snap_to_road_result_json->snappedPoints;

							if(!empty($snappedPointsArr)){
								foreach ($snappedPointsArr as $item_location) {

									if (!empty($item_location->location->latitude) && !empty($item_location->location->longitude)) {
										$latitude = $item_location->location->latitude;
										$longitude = $item_location->location->longitude;

										$lat_array[] = $latitude;
										$long_array[] = $longitude;
									}
								}
							}
						}
					}
				
					if (count($lat_array) > 0 && count($long_array) > 0) {
						$tPlatitudes_str = implode(",", $lat_array);
						$tPlongitudes_str = implode(",", $long_array);
						$TripPathLatitudes = $lat_array;
						$TripPathLongitudes = $long_array;

						$Data_route_locations['iTripId'] = $tripId;
						$Data_route_locations['tPlatitudes'] = $tPlatitudes_str;
						$Data_route_locations['tPlongitudes'] = $tPlongitudes_str;
						$Data_route_locations['tPath'] = json_encode($location_path_arr);
						$Data_route_locations['eType'] = 'SnapToRoad';
						$Data_route_locations['tDate'] = @date("Y-m-d H:i:s");

						$obj->MySQLQueryPerform('trips_route_locations', $Data_route_locations, 'insert');
					}
				}
			}
		}
        /*********************** Refine Raw Data ****************************/


        $previousDistance = 0;
        $isFirstProcessed = false;
        for ($i = 0; $i < count($TripPathLatitudes) - 1; $i++) {
            if ($isFirstProcessed == false) {
                $firsttemplat = $TripPathLatitudes[0];
                $firsttempLon = $TripPathLongitudes[0];
                $nexttempLat = $TripPathLatitudes[$i];
                $nexttempLon = $TripPathLongitudes[$i];
                $TempDistance_First = distanceByLocation($firsttemplat, $firsttempLon, $nexttempLat, $nexttempLon, "K");
                if ($TempDistance_First > 2) {
                    continue;
                } else {
                    $isFirstProcessed = true;
                    $previousDistance = $TempDistance_First;
                    continue;
                }
            }
            $tempLat_current = $TripPathLatitudes[$i];
            $tempLon_current = $TripPathLongitudes[$i];
            $tempLat_next = $TripPathLatitudes[$i + 1];
            $tempLon_next = $TripPathLongitudes[$i + 1];

            $arrAddLocValue = $tempLat_current . "," . $tempLon_current;
            if (in_array($arrAddLocValue, $arrOfLocations)) {
                continue;
            } else {
                $arrOfLocations[] = $arrAddLocValue;
            }

            if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_next == '0.0' || $tempLon_next == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0' || $tempLat_next == '-180.0' || $tempLon_next == '-180.0' || ($tempLat_current == $tempLat_next && $tempLon_current == $tempLon_next)) {
                //if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_next == '0.0' || $tempLon_next == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0' || $tempLat_next == '-180.0' || $tempLon_next == '-180.0' || $tempLat_current == $tempLat_next || $tempLon_current == $tempLon_next) {
                continue;
            }
            $TempDistance = distanceByLocation($tempLat_current, $tempLon_current, $tempLat_next, $tempLon_next, "K");
            if (is_nan($TempDistance)) {
                $TempDistance = 0;
            }
            if (abs($previousDistance - $TempDistance) > 0.1) {
                $TempDistance = 0;
            } else {
                $previousDistance = $TempDistance;
            }
            $TotalDistance += $TempDistance;
        }
    }
    return round($TotalDistance, 2);
}

/* function calcluateTripDistance($tripId) {
  global $obj;
  $sql = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
  $Data_tripsLocations = $obj->MySQLSelect($sql);

  $TotalDistance = 0;
  if (count($Data_tripsLocations) > 0) {
  $trip_path_latitudes = $Data_tripsLocations[0]['tPlatitudes'];
  $trip_path_longitudes = $Data_tripsLocations[0]['tPlongitudes'];

  $trip_path_latitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_latitudes);
  $trip_path_longitudes = preg_replace("/[^0-9,.-]/", '', $trip_path_longitudes);

  $TripPathLatitudes = explode(",", $trip_path_latitudes);

  $TripPathLongitudes = explode(",", $trip_path_longitudes);

  $previousDistance = 0;
  for ($i = 0; $i < count($TripPathLatitudes) - 1; $i++) {
  $tempLat_current = $TripPathLatitudes[$i];
  $tempLon_current = $TripPathLongitudes[$i];
  $tempLat_next = $TripPathLatitudes[$i + 1];
  $tempLon_next = $TripPathLongitudes[$i + 1];

  if ($tempLat_current == '0.0' || $tempLon_current == '0.0' || $tempLat_next == '0.0' || $tempLon_next == '0.0' || $tempLat_current == '-180.0' || $tempLon_current == '-180.0' || $tempLat_next == '-180.0' || $tempLon_next == '-180.0') {
  continue;
  }

  $TempDistance = distanceByLocation($tempLat_current, $tempLon_current, $tempLat_next, $tempLon_next, "K");

  if (is_nan($TempDistance)) {
  $TempDistance = 0;
  }
  if($previousDistance == 0){
  $previousDistance = $TempDistance;
  }else if(abs($previousDistance - $TempDistance) > 0.1){
  $TempDistance = 0;
  }else{
  $previousDistance = $TempDistance;
  }
  $TotalDistance += $TempDistance;
  }
  }

  return round($TotalDistance, 2);
  } */
/* function checkDistanceWithGoogleDirections($tripDistance, $startLatitude, $startLongitude, $endLatitude, $endLongitude, $isFareEstimate = "0", $vGMapLangCode = "") {
  global $generalobj, $obj, $GOOGLE_SEVER_GCM_API_KEY;

  if ($vGMapLangCode == "" || $vGMapLangCode == NULL) {
  $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault', 'Yes');
  $vGMapLangCode = $vLangCodeData[0]['vGMapLangCode'];
  }

  $GOOGLE_API_KEY = $GOOGLE_SEVER_GCM_API_KEY;
  $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $startLatitude . "," . $startLongitude . "&destination=" . $endLatitude . "," . $endLongitude . "&sensor=false&key=" . $GOOGLE_API_KEY . "&language=" . $vGMapLangCode;

  try {
  $jsonfile = file_get_contents($url);
  } catch (ErrorException $ex) {
  // return $tripDistance;

  $returnArr['Action'] = "0";
  setDataResponse($returnArr);
  exit;
  // echo 'Site not reachable (' . $ex->getMessage() . ')';
  }

  $jsondata = json_decode($jsonfile);
  $distance_google_directions = ($jsondata->routes[0]->legs[0]->distance->value) / 1000;

  if ($isFareEstimate == "0") {
  $comparedDist = ($distance_google_directions * 85) / 100;

  if ($tripDistance > $comparedDist) {
  return $tripDistance;
  } else {
  return round($distance_google_directions, 2);
  }
  } else {
  $duration_google_directions = ($jsondata->routes[0]->legs[0]->duration->value) / 60;
  $sAddress = ($jsondata->routes[0]->legs[0]->start_address);
  $dAddress = ($jsondata->routes[0]->legs[0]->end_address);
  $steps = ($jsondata->routes[0]->legs[0]->steps);

  $returnArr['Time'] = $duration_google_directions;
  $returnArr['Distance'] = $distance_google_directions;
  $returnArr['SAddress'] = $sAddress;
  $returnArr['DAddress'] = $dAddress;
  $returnArr['steps'] = $steps;

  return $returnArr;
  }
  } */
  

function calculateBearing( $lat1_d, $lon1_d, $lat2_d, $lon2_d ){

   $lat1 = deg2rad($lat1_d);
   $lon1 = deg2rad($lon1_d);
   $lat2 = deg2rad($lat2_d);
   $lon2 = deg2rad($lon2_d);

   $L    = $lon2 - $lon1;

   $cosD = sin($lat1)*sin($lat2) + cos($lat1)*cos($lat2)*cos($L);
   $D    = acos($cosD);
   $cosC = (sin($lat2) - $cosD*sin($lat1))/(sin($D)*cos($lat1));
    
    $C = 180.0*acos($cosC)/pi();

    if( sin($L) < 0.0 )
        $C = 360.0 - $C;

    return $C;
}

function checkDistanceWithGoogleDirections($tripDistance, $startLatitude, $startLongitude, $endLatitude, $endLongitude, $isFareEstimate = "0", $vGMapLangCode = "", $isReturnArr = false) {
    global $generalobj, $obj, $DISTANCE_CALCULATION_STRATEGY, $GOOGLE_SEVER_GCM_API_KEY, $FILTER_ROUTE_RAW_DATA;
    if ($vGMapLangCode == "" || $vGMapLangCode == NULL) {
        $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault', 'Yes');
        $vGMapLangCode = $vLangCodeData[0]['vGMapLangCode'];
    }
	
	if(empty($GOOGLE_SEVER_GCM_API_KEY)){
		$GOOGLE_API_KEY = $generalobj->getConfigurations("configurations", "GOOGLE_SEVER_GCM_API_KEY");
	}else{
		$GOOGLE_API_KEY = $GOOGLE_SEVER_GCM_API_KEY;
	}
	
	if(!empty($_REQUEST['type']) && $_REQUEST['type'] == "ProcessEndTrip" && !empty($_REQUEST["TripId"]) && !empty($DISTANCE_CALCULATION_STRATEGY) && strtoupper($DISTANCE_CALCULATION_STRATEGY) != "DISABLE"){
		$tripId = $_REQUEST["TripId"];
		$TripPathLatitudes = "";
		$TripPathLongitudes = "";
		
		if(strtoupper($FILTER_ROUTE_RAW_DATA) == "YES"){
			$sql_snap_locations = "SELECT * FROM `trips_route_locations` WHERE iTripId = '$tripId' AND eType='SnapToRoad'";
			$Data_snap_locations = $obj->MySQLSelect($sql_snap_locations);
		}else{
			$sql_snap_locations = "SELECT * FROM `trips_locations` WHERE iTripId = '$tripId'";
			$Data_snap_locations = $obj->MySQLSelect($sql_snap_locations);
		}
		
		if (!empty($Data_snap_locations) && count($Data_snap_locations) > 0) {
			$TripPathLatitudes = explode(",", $Data_snap_locations[0]['tPlatitudes']);
			$TripPathLongitudes = explode(",", $Data_snap_locations[0]['tPlongitudes']);
		}
		//echo count($TripPathLatitudes);exit;
		
		if(!empty($TripPathLatitudes) && !empty($TripPathLongitudes) && count($TripPathLatitudes) > 0 && count($TripPathLongitudes) > 0){
			$TripPathLatitudes_tmp = array();
			$TripPathLongitudes_tmp = array();
			
			$lastLatitude = "";
			$lastLongitude = "";
			for($i = 0; $i < count($TripPathLatitudes); $i++){
				if(empty($lastLatitude)){
					$lastLatitude = $TripPathLatitudes[$i];
					$lastLongitude = $TripPathLongitudes[$i];
					
					$TripPathLatitudes_tmp[] = $lastLatitude;
					$TripPathLongitudes_tmp[] = $lastLongitude;
					
					continue;
				}
				
				$currentLatitude = $TripPathLatitudes[$i];
				$currentLongitude = $TripPathLongitudes[$i];
				
				$angleOfLocation = calculateBearing($lastLatitude, $lastLongitude, $currentLatitude, $currentLongitude);
				//echo "Angle:".$angleOfLocation."::LastLocation:".$lastLatitude.",".$lastLongitude."::CurrentLocation::".$currentLatitude.",".$currentLongitude."<BR/><BR/>";
				if((($angleOfLocation > 30 && $angleOfLocation < 150) || ($angleOfLocation > 210 && $angleOfLocation < 330)) && $lastLatitude != $currentLatitude && $lastLongitude != $currentLongitude){
				//echo "<BR/>INOfAngle<BR/>";
			//	echo "Angle:".$angleOfLocation."::LastLocation:".$lastLatitude.",".$lastLongitude."::CurrentLocation::".$currentLatitude.",".$currentLongitude."<BR/><BR/>";
					/* $TripPathLatitudes_tmp[] = $lastLatitude;
					$TripPathLongitudes_tmp[] = $lastLongitude; */
					
					$TripPathLatitudes_tmp[] = $currentLatitude;
					$TripPathLongitudes_tmp[] = $currentLongitude;
					
					
				}
				/* $lastLatitude = $TripPathLatitudes[$i];
					$lastLongitude = $TripPathLongitudes[$i]; */
			}
			
			if(!empty($TripPathLatitudes_tmp) && count($TripPathLatitudes_tmp) > 0){
				$TripPathLatitudes = $TripPathLatitudes_tmp;
				$TripPathLongitudes = $TripPathLongitudes_tmp;
			}
		}
	}
	
	//echo count($TripPathLatitudes);exit;
	$waypoints = "";
	
	//echo $DISTANCE_CALCULATION_STRATEGY;exit;
	
	if(!empty($TripPathLatitudes) && !empty($TripPathLongitudes) && count($TripPathLatitudes) > 0 && count($TripPathLongitudes) > 0){
		if($DISTANCE_CALCULATION_STRATEGY == "Stage-3" || $DISTANCE_CALCULATION_STRATEGY == "Stage-4"){
			$waypoints = array();
		}
		
		if(is_array($waypoints)){
			$waypoints[] = "&waypoints=optimize:false|";
		}else{
			$waypoints = "&waypoints=optimize:false|";
		}
		
		$max_points =  ($DISTANCE_CALCULATION_STRATEGY == "Stage-2" || $DISTANCE_CALCULATION_STRATEGY == "Stage-4") ? 25 : 10;
		
		if($max_points > count($TripPathLatitudes)){
			$max_points = count($TripPathLatitudes);
		}
		
		$position_interval = floor(count($TripPathLatitudes)/$max_points);
		
		if($position_interval == 0 || is_array($waypoints)){
			$position_interval = 1;
		}
		
		$countOfLoc = $position_interval;
		
		$waypoint_tmp_positions = "";
		$positionOfPoints = 1;
		$positionInArr = 1;
		for($i=0; $i < (is_array($waypoints) ? count($TripPathLatitudes) : $max_points); $i++){
			
			$waypoint_tmp = "via:".$TripPathLatitudes[$countOfLoc - 1].",".$TripPathLongitudes[$countOfLoc - 1];
			
			if(is_array($waypoints)){
				if($positionOfPoints >= $max_points){
					$positionOfPoints = 1;
					$positionInArr = $positionInArr + 1;
					$waypoint_tmp_positions = "";
				}
				
				$waypoint_tmp_positions .= $waypoint_tmp_positions == "" ? $waypoint_tmp : "|".$waypoint_tmp;
				
				$positionOfPoints = $positionOfPoints + 1;
				
				$waypoints[$positionInArr] = $waypoint_tmp_positions;
				
			}else{
				$waypoints .= ($i == 0) ? $waypoint_tmp : "|".$waypoint_tmp;
			}
			
			// echo "WayPoint:".$waypoint_tmp."::Position:".$countOfLoc."<BR/>";
			
			
			$countOfLoc = $countOfLoc + $position_interval;
		}
	}
	
	/*    echo "<PRE>";
	print_r($waypoints);
	exit;    */
    // exit;
	if(is_array($waypoints)){
		
		$jsonfile = array();
		$waypoints_initial_str = $waypoints[0];
		$countOfWayArr = 0;
		
		$waypoints_routs_arr = array();
		$tPlatitudes_google_waypoints = array();
		$tPlongitudes_google_waypoints = array();
		
		foreach($waypoints as $waypoints_item){
			if($countOfWayArr == 0){
				$countOfWayArr = $countOfWayArr + 1;
				continue;
			}
			 $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $startLatitude . "," . $startLongitude . "&destination=" . $endLatitude . "," . $endLongitude . "&sensor=false&key=" . $GOOGLE_API_KEY . "&language=" . $vGMapLangCode.$waypoints_initial_str. $waypoints_item;
			
			try {
				$datOfDirection = file_get_contents($url);
				$jsonfile[] = $datOfDirection;
				
				$dataOfDirection_obj = json_decode($datOfDirection, true);
				
				if(strtoupper($dataOfDirection_obj['status']) == "OK"){
					$routs_arr = $dataOfDirection_obj['routes'];
					$steps_arr = $routs_arr[0]['legs'][0]['steps'];
					
					if(!empty($steps_arr) && count($steps_arr) > 0){
						foreach($steps_arr as $steps_arr_item){
							$end_location_latitude = $steps_arr_item['end_location']['lat'];
							$end_location_longitude = $steps_arr_item['end_location']['lng'];
							
							$tPlatitudes_google_waypoints[] = $end_location_latitude;
							$tPlongitudes_google_waypoints[] = $end_location_longitude;
						}
					}
				}
				
			} catch (ErrorException $ex) {
				// return $tripDistance;
				$returnArr['Action'] = "0";
				setDataResponse($returnArr);
				// echo 'Site not reachable (' . $ex->getMessage() . ')';
			}
		}
	
	}else{
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $startLatitude . "," . $startLongitude . "&destination=" . $endLatitude . "," . $endLongitude . "&sensor=false&key=" . $GOOGLE_API_KEY . "&language=" . $vGMapLangCode. $waypoints;
		
		try {
			$jsonfile = file_get_contents($url);
		} catch (ErrorException $ex) {
			// return $tripDistance;
			$returnArr['Action'] = "0";
			setDataResponse($returnArr);
			// echo 'Site not reachable (' . $ex->getMessage() . ')';
		}
	}
	
	if(is_array($jsonfile)){
		$distance_google_directions = 0;
		$duration_google_directions = 0;
		
		foreach($jsonfile as $jsonfile_item){
			$jsondata = json_decode($jsonfile_item);
			$distance_google_directions += ($jsondata->routes[0]
				->legs[0]
				->distance
				->value) / 1000;
				
			$duration_google_directions += ($jsondata->routes[0]
				->legs[0]
				->duration
				->value);
		}
	}else{
		$jsondata = json_decode($jsonfile);
		$distance_google_directions = ($jsondata->routes[0]
				->legs[0]
				->distance
				->value) / 1000;
	}
    
	
	if(!empty($_REQUEST['type']) && $_REQUEST['type'] == "ProcessEndTrip" && !empty($_REQUEST["TripId"]) && !empty($DISTANCE_CALCULATION_STRATEGY) && strtoupper($DISTANCE_CALCULATION_STRATEGY) != "DISABLE"){
		$duration_google_directions_ins = $duration_google_directions / 60;
		
		$waypoints_tmp_str = json_encode($waypoints);
		
		$sql_trip_route = "REPLACE INTO trips_route_locations( `iTripId`, `tPlatitudes`, `tPlongitudes`, `tPath`, `tDistance`, `tDuration`, `eType` ) VALUES( '".$_REQUEST["TripId"]."', '".implode(",",$tPlatitudes_google_waypoints)."', '".implode(",",$tPlongitudes_google_waypoints)."','".$waypoints_tmp_str."', '".$distance_google_directions."', '".$duration_google_directions_ins."', 'GoogleDirection' )";
		$obj->sql_query($sql_trip_route);
	}
	
    if ($isFareEstimate == "0") {
        $comparedDist = ($distance_google_directions * 85) / 100;
        if ($isReturnArr == true) {
            if ($tripDistance > $comparedDist) {
                $distance_google_directions_val = $tripDistance;
            } else {
                $distance_google_directions_val = round($distance_google_directions, 2);
            }
			
			if(is_array($jsondata) == false){
				$duration_google_directions = ($jsondata->routes[0]
                    ->legs[0]
                    ->duration
                    ->value);
			}
            
            $sAddress = ($jsondata->routes[0]
                    ->legs[0]
                    ->start_address);
            $dAddress = ($jsondata->routes[0]
                    ->legs[0]
                    ->end_address);
            $steps = ($jsondata->routes[0]
                    ->legs[0]
                    ->steps);
            $returnArr['Time'] = $duration_google_directions;
            $returnArr['Distance'] = $distance_google_directions_val;
            $returnArr['GDistance'] = $distance_google_directions;
            $returnArr['SAddress'] = $sAddress;
            $returnArr['DAddress'] = $dAddress;
            $returnArr['steps'] = $steps;
            return $returnArr;
        } else {
            if ($tripDistance > $comparedDist) {
                return $tripDistance;
            } else {
                return round($distance_google_directions, 2);
            }
        }
    } else {
        $duration_google_directions = ($jsondata->routes[0]
                ->legs[0]
                ->duration
                ->value) / 60;
        $sAddress = ($jsondata->routes[0]
                ->legs[0]
                ->start_address);
        $dAddress = ($jsondata->routes[0]
                ->legs[0]
                ->end_address);
        $steps = ($jsondata->routes[0]
                ->legs[0]
                ->steps);
        $returnArr['Time'] = $duration_google_directions;
        $returnArr['Distance'] = $distance_google_directions;
        $returnArr['SAddress'] = $sAddress;
        $returnArr['DAddress'] = $dAddress;
        $returnArr['steps'] = $steps;
        return $returnArr;
    }
}

function distanceByLocation($lat1, $lon1, $lat2, $lon2, $unit) {
    if ((($lat1 == $lat2) && ($lon1 == $lon2)) || ($lat1 == '' || $lon1 == '' || $lat2 == '' || $lon2 == '')) {
        return 0;
    }
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);
    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

function getLanguageLabelsArr_01092017($lCode = '', $directValue = "") {
    global $obj;
    /* find default language of website set by admin */
    $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
    $default_label = $obj->MySQLSelect($sql);
    if ($lCode == '') {
        $lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
    }
    $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label`  WHERE lPage_id >= 27 AND  `vCode` = '" . $lCode . "' ";
    $all_label = $obj->MySQLSelect($sql);
    $x = array();
    for ($i = 0; $i < count($all_label); $i++) {
        $vLabel = $all_label[$i]['vLabel'];
        $vValue = $all_label[$i]['vValue'];
        $x[$vLabel] = $vValue;
    }
    /*
      $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label_other`  WHERE  `vCode` = '" . $lCode . "' ";
      $all_label = $obj->MySQLSelect($sql);

      for ($i = 0; $i < count($all_label); $i++) {
      $vLabel = $all_label[$i]['vLabel'];

      $vValue = $all_label[$i]['vValue'];
      $x[$vLabel] = $vValue;
      } */
    $x['vCode'] = $lCode; // to check in which languge code it is loading
    if ($directValue == "") {
        $returnArr['Action'] = "1";
        $returnArr['LanguageLabels'] = $x;
        return $returnArr;
    } else {
        return $x;
    }
}

function sendEmeSms($toMobileNum, $message) {
    global $generalobj, $MOBILE_VERIFY_SID_TWILIO, $MOBILE_VERIFY_TOKEN_TWILIO, $MOBILE_NO_TWILIO;
    $account_sid = $MOBILE_VERIFY_SID_TWILIO;
    $auth_token = $MOBILE_VERIFY_TOKEN_TWILIO;
    $twilioMobileNum = $MOBILE_NO_TWILIO;
    $client = new Services_Twilio($account_sid, $auth_token);
    try {
        $sms = $client
                ->account
                ->messages
                ->sendMessage($twilioMobileNum, $toMobileNum, $message);
        return 1;
    } catch (Services_Twilio_RestException $e) {
        return 0;
    }
}

function converToTz($time, $toTz, $fromTz, $dateFormat = "Y-m-d H:i:s") {
    $date = new DateTime($time, new DateTimeZone($fromTz));
    $date->setTimezone(new DateTimeZone($toTz));
    $time = $date->format($dateFormat);
    return $time;
}

/* Sending Push Notification */

function send_notification($registatoin_ids, $message, $filterMsg = 0) {
    // include_once './config.php';
    global $generalobj, $obj, $FIREBASE_API_ACCESS_KEY, $ENABLE_PUBNUB;
    //global $generalobj, $obj;
    if (empty($FIREBASE_API_ACCESS_KEY)) {
        $FIREBASE_API_ACCESS_KEY = $generalobj->getConfigurations("configurations", "FIREBASE_API_ACCESS_KEY");
    }

    if (empty($ENABLE_PUBNUB)) {
        $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    }

    $fields = array(
        'registration_ids' => $registatoin_ids,
        'click_action' => ".MainActivity",
        'priority' => "high",
        //'data'          => $msg
        'data' => $message
    );
    $finalFields = json_encode($fields, JSON_UNESCAPED_UNICODE);
    if ($filterMsg == 1) {
        $finalFields = stripslashes(preg_replace("/[\n\r]/", "", $finalFields));
    }
    $headers = array(
        'Authorization: key=' . $FIREBASE_API_ACCESS_KEY,
        'Content-Type: application/json',
    );
    //Setup headers:
    // echo "<pre>";print_r($headers);exit;
    //Setup curl, add headers and post parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $finalFields);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //Send the request
    $response = curl_exec($ch); //echo "<pre>";print_r($response);exit;
    if ($response === false) {
        // die('Curl failed: ' . curl_error($ch));
        if ($ENABLE_PUBNUB == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_SERVER_COMM_ERROR";
            $returnArr['ERROR'] = curl_error($ch);
            setDataResponse($returnArr);
        }
    }
    $responseArr = json_decode($response);
    $success = $responseArr->success;
    //Close request
    curl_close($ch);
    return $success;
}

function checkRestrictedArea($address_data, $DropOff) {
    global $generalobj, $obj;
    $ssql = "";
    if ($DropOff == "No") {
        $ssql .= " AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
    } else {
        $ssql .= " AND (eRestrictType = 'Drop Off' OR eRestrictType = 'All')";
    }

    if (!empty($address_data)) {
        $pickaddrress = strtolower($address_data['CheckAddress']);
        $pickaddrress = preg_replace('/\d/', '', $pickaddrress);
        $pickaddrress = preg_replace('/\s+/', '', $pickaddrress);

        // $pickArr = explode(',',$pickaddrress);
        $pickArr = array_map('trim', array_filter(explode(',', $pickaddrress)));
        $sqlaa = "SELECT cr.vCountry,ct.vCity,st.vState,replace(rs.vAddress, ' ','') as vAddress FROM `restricted_negative_area` AS rs
        LEFT JOIN country as cr ON cr.iCountryId = rs.iCountryId
            LEFT JOIN state as st ON st.iStateId = rs.iStateId
            LEFT JOIN city as ct ON ct.iCityId = rs.iCityId
            WHERE eType='Allowed'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        $allowed_ans = 'No';
        if (!empty($allowed_data)) {
            foreach ($allowed_data as $rds) {
                $alwd_country = $alwd_state = $alwd_city = $alwd_address = 'allowed';
                if ($rds['vCountry'] != "") {

                    // if($rds['vCountry'] == $address_data['countryId']){
                    if (in_array(strtolower($rds['vCountry']), $pickArr)) {
                        $alwd_country = 'allowed';
                    } else {
                        $alwd_country = 'Disallowed';
                    }
                }

                if ($rds['vState'] != "") {
                    if (in_array(strtolower($rds['vState']), $pickArr)) {
                        $alwd_state = 'allowed';
                    } else {
                        $alwd_state = 'Disallowed';
                    }
                }

                if ($rds['vCity'] != "") {
                    if (in_array(strtolower($rds['vCity']), $pickArr)) {
                        $alwd_city = 'allowed';
                    } else {
                        $alwd_city = 'Disallowed';
                    }
                }

                if ($rds['vAddress'] != "") {
                    if (strstr(strtolower($pickaddrress), strtolower($rds['vAddress']))) {
                        $alwd_address = 'allowed';
                    } else {
                        $alwd_address = 'Disallowed';
                    }
                }

                if ($alwd_country == 'allowed' && $alwd_state == 'allowed' && $alwd_city == 'allowed' && $alwd_address == 'allowed') {
                    $allowed_ans = 'Yes';
                    break;
                }
            }
        }

        if ($allowed_ans == 'No') {

            // $sqlas = "SELECT * FROM `restricted_negative_area` WHERE (iCountryId='".$address_data['countryId']."' OR iStateId='".$address_data['stateId']."' OR iCityId='".$address_data['cityId']."') AND eType='Disallowed' AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
            $sqlas = "SELECT cr.vCountry,ct.vCity,st.vState,replace(rs.vAddress, ' ','') as vAddress FROM `restricted_negative_area` AS rs
        LEFT JOIN country as cr ON cr.iCountryId = rs.iCountryId
            LEFT JOIN state as st ON st.iStateId = rs.iStateId
            LEFT JOIN city as ct ON ct.iCityId = rs.iCityId
            WHERE eType='Disallowed'" . $ssql;
            $restricted_data = $obj->MySQLSelect($sqlas);
            $allowed_ans = 'Yes';
            if (!empty($restricted_data)) {
                foreach ($restricted_data as $rds) {
                    $alwd_country = $alwd_state = $alwd_city = $alwd_address = 'Disallowed';
                    if ($rds['vCountry'] != "") {
                        if (in_array(strtolower($rds['vCountry']), $pickArr)) {
                            $alwd_country = 'Disallowed';
                        } else {
                            $alwd_country = 'allowed';
                        }
                    }

                    if ($rds['vState'] != "") {
                        if (in_array(strtolower($rds['vState']), $pickArr)) {
                            $alwd_state = 'Disallowed';
                        } else {
                            $alwd_state = 'allowed';
                        }
                    }

                    if ($rds['vCity'] != "") {
                        if (in_array(strtolower($rds['vCity']), $pickArr)) {
                            $alwd_city = 'Disallowed';
                        } else {
                            $alwd_city = 'allowed';
                        }
                    }

                    if ($rds['vAddress'] != "") {
                        if (strstr(strtolower($pickaddrress), strtolower($rds['vAddress']))) {
                            $alwd_address = 'Disallowed';
                        } else {
                            $alwd_address = 'allowed';
                        }
                    }

                    if ($alwd_country == 'Disallowed' && $alwd_state == 'Disallowed' && $alwd_city == 'Disallowed' && $alwd_address == "Disallowed") {
                        $allowed_ans = 'No';
                        break;
                    }
                }
            }
        }
    }

    return $allowed_ans;
}

function getAddressFromLocation($latitude, $longitude, $Google_Server_key) {
    $location_Address = "";
    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . "," . $longitude . "&key=" . $Google_Server_key;
    try {
        $jsonfile = file_get_contents($url);
        $jsondata = json_decode($jsonfile);
        $address = $jsondata->results[0]->formatted_address;
        $location_Address = $address;
    } catch (ErrorException $ex) {
        $returnArr['Action'] = "0";
        setDataResponse($returnArr);

        // echo 'Site not reachable (' . $ex->getMessage() . ')';
    }

    if ($location_Address == "") {
        $returnArr['Action'] = "0";
        setDataResponse($returnArr);
    }

    return $location_Address;
}

function getLanguageTitle($vLangCode) {
    global $obj;
    $sql = "SELECT vTitle FROM language_master WHERE vCode = '" . $vLangCode . "' ";
    $db_title = $obj->MySQLSelect($sql);
    return $db_title[0]['vTitle'];
}

function check_email_send($iDriverId, $tablename, $field) {
    global $obj, $generalobj;
    $sql = "SELECT * FROM " . $tablename . " WHERE " . $field . "= '" . $iDriverId . "'";
    $db_data = $obj->MySQLSelect($sql);
    //print_r($db_data);//exit;
    //$valid=0;
    if ($tablename == 'register_driver') {
        //echo "hi";exit;
        if ($db_data[0]['vNoc'] != NULL && $db_data[0]['vLicence'] != NULL && $db_data[0]['vCerti'] != NULL) {
            //global $generalobj;
            $maildata['USER'] = "Driver";
            $maildata['NAME'] = $db_data[0]['vName'];
            $maildata['EMAIL'] = $db_data[0]['vEmail'];
            $generalobj->send_email_user("PROFILE_UPLOAD", $maildata);
            //header("location:profile.php?success=1&var_msg=" . $var_msg);
            //return;
        }
    } else {
        if ($db_data[0]['vNoc'] != NULL && $db_data[0]['vCerti'] != NULL) {
            $maildata['USER'] = "Company";
            $maildata['NAME'] = $db_data[0]['vName'];
            $maildata['EMAIL'] = $db_data[0]['vEmail'];
            //var_dump($maildata);
            //var_dump(($generalobj));
            $generalobj->send_email_user("PROFILE_UPLOAD", $maildata);
        }
    }
    return true;
}

function formatNum($number) {
    return strval(number_format($number, 2));
}

function get_tiny_url($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function addToUserRequest($iUserId, $iDriverId, $message, $iMsgCode) {
    global $obj;
    $data['iUserId'] = $iUserId;
    $data['iDriverId'] = $iDriverId;
    $data['tMessage'] = $message;
    $data['iMsgCode'] = $iMsgCode;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");
    $dataId = $obj->MySQLQueryPerform("passenger_requests", $data, 'insert');
    return $dataId;
}

function addToDriverRequest($iDriverId, $iUserId, $iTripId, $eStatus) {
    global $obj;
    $data['iDriverId'] = $iDriverId;
    $data['iUserId'] = $iUserId;
    $data['iTripId'] = $iTripId;
    $data['eStatus'] = $eStatus;
    $data['tDate'] = @date("Y-m-d H:i:s");
    $data['dAddedDate'] = @date("Y-m-d H:i:s");
    $id = $obj->MySQLQueryPerform("driver_request", $data, 'insert');
    return $id;
}

function addToUserRequest2($data) {
    global $obj;
    $dataId = $obj->MySQLQueryPerform("passenger_requests", $data, 'insert');
    return $dataId;
}

function addToDriverRequest2($data) {
    global $obj;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");
    $id = $obj->MySQLQueryPerform("driver_request", $data, 'insert');
    return $id;
}

function UpdateDriverRequest($iDriverId, $iUserId, $iTripId, $eStatus) {
    global $obj;
    $sql = "SELECT * FROM `driver_request` WHERE iDriverId = '" . $iDriverId . "' AND iUserId = '" . $iUserId . "' AND iTripId = '0' ORDER BY iDriverRequestId DESC LIMIT 0,1";
    $db_sql = $obj->MySQLSelect($sql);
    $request_count = count($db_sql);
    if ($request_count > 0) {
        $where = " iDriverRequestId = '" . $db_sql[0]['iDriverRequestId'] . "'";
        $Data_Update['eStatus'] = $eStatus;
        $Data_Update['tDate'] = @date("Y-m-d H:i:s");
        $Data_Update['iTripId'] = $iTripId;
        $id = $obj->MySQLQueryPerform("driver_request", $Data_Update, 'update', $where);
    }
    return $request_count;
}

function fetch_address_geocode($address, $geoCodeResult = "") {
    global $generalobj, $GOOGLE_SEVER_API_KEY_WEB;
    $address = str_replace(" ", "+", "$address");

    // $GOOGLE_SEVER_API_KEY_WEB=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");
    $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=" . $GOOGLE_SEVER_API_KEY_WEB;

    // $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false";
    if ($geoCodeResult == "") {
        $result = file_get_contents("$url");
        $result = preg_replace("/[\n\r]/", "", $result);
    } else {
        $result = $geoCodeResult;
        $result = stripslashes(preg_replace("/[\n\r]/", "", $result));
    }

    // $result = stripslashes(preg_replace("/[\n\r]/", "", $result));
    $json = json_decode($result);
    $city = $state = $country = $country_code = '';
    foreach ($json->results as $result) {
        foreach ($result->address_components as $addressPart) {
            if (((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types))) || ((in_array('sublocality', $addressPart->types)) && (in_array('political', $addressPart->types)) && (in_array('sublocality_level_1', $addressPart->types)))) {
                $city = $addressPart->long_name;
            } else if ((in_array('administrative_area_level_1', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                $state = $addressPart->long_name;
            } else if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                $country = $addressPart->long_name;
                $country_code = $addressPart->short_name;
            }
        }
    }

    // if(($city != '') && ($state != '') && ($country != ''))
    // $address = $city.', '.$state.', '.$country;
    // else if (($city != '') && ($state != ''))
    // $address = $city.', '.$state;
    // else if (($state != '') && ($country != ''))
    // $address = $state.', '.$country;
    // else if ($country != '')
    // $address = $country;
    $returnArr = array(
        'city' => $city,
        'state' => $state,
        'country' => $country,
        'country_code' => $country_code
    );
    return $returnArr;
}

function get_address_geocode($address) {
    global $generalobj, $GOOGLE_SEVER_API_KEY_WEB;
    $address = str_replace(" ", "+", "$address");
    //$GOOGLE_SEVER_API_KEY_WEB=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_API_KEY_WEB");
    $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=" . $GOOGLE_SEVER_API_KEY_WEB;
    $result = file_get_contents("$url");
    $result = stripslashes(preg_replace("/[\n\r]/", "", $result));
    $json = json_decode($result);
    $city = $state = $country = $country_code = '';
    foreach ($json->results as $result) {
        foreach ($result->address_components as $addressPart) {
            if (((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types))) || ((in_array('sublocality', $addressPart->types)) && (in_array('political', $addressPart->types)) && (in_array('sublocality_level_1', $addressPart->types)))) {
                $city = $addressPart->long_name;
            } else if ((in_array('administrative_area_level_1', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                $state = $addressPart->long_name;
            } else if ((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                $country = $addressPart->long_name;
                $country_code = $addressPart->short_name;
            }
        }
    }
    $returnArr = array(
        'city' => $city,
        'state' => $state,
        'country' => $country,
        'country_code' => $country_code
    );
    return $returnArr;
}

function UploadUserImage($iMemberId, $UserType = "Passenger", $eSignUpType, $vFbId, $vImageURL = "") {
    global $generalobj, $tconfig, $TWITTER_OAUTH_ACCESS_TOKEN, $TWITTER_OAUTH_ACCESS_TOKEN_SECRET, $TWITTER_CONSUMER_KEY, $TWITTER_CONSUMER_SECRET, $GOOGLE_SEVER_API_KEY_WEB;
    $vimage = "";
    if ($UserType == "Passenger") {
        $Photo_Gallery_folder = $tconfig["tsite_upload_images_passenger_path"] . "/" . $iMemberId . "/";
        $OldImage = get_value('register_user', 'vImgName', 'iUserId', $iMemberId, '', 'true');
    } else {
        $Photo_Gallery_folder = $tconfig["tsite_upload_images_driver_path"] . "/" . $iMemberId . "/";
        $OldImage = get_value('register_driver', 'vImage', 'iDriverId', $iMemberId, '', 'true');
    }
    unlink($Photo_Gallery_folder . $OldImage);
    unlink($Photo_Gallery_folder . "1_" . $OldImage);
    unlink($Photo_Gallery_folder . "2_" . $OldImage);
    unlink($Photo_Gallery_folder . "3_" . $OldImage);
    unlink($Photo_Gallery_folder . "4_" . $OldImage);
    if (!is_dir($Photo_Gallery_folder)) {
        mkdir($Photo_Gallery_folder, 0777);
    }
    if ($eSignUpType == "Facebook") {
        if ($vImageURL != "") {
            $vImageURL = str_replace("type=large", "width=256", $vImageURL);
            $baseurl = $vImageURL;
        } else {
            //$baseurl =  "http://graph.facebook.com/".$vFbId."/picture?type=large";
            $baseurl = "http://graph.facebook.com/" . $vFbId . "/picture?width=256";
            //$url = $vFbId."_".time().".jpg";
        }
        $url = time() . ".jpg";
        /* file_get_content */
        $profile_Image = $baseurl;
        $userImage = $url;
        $thumb_image = file_get_contents($baseurl);
        $thumb_file = $Photo_Gallery_folder . $url;
        $image_name = file_put_contents($thumb_file, $thumb_image);
        /* file_get_content  ends */
        if (is_file($Photo_Gallery_folder . $url)) {
            $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
            $vimage = $imgname;
        }
    }
    if ($eSignUpType == "Google") {
        if ($vImageURL != "") {
            $baseurl = $vImageURL;
            $url = time() . ".jpg";
        } else {
            //$GOOGLE_SEVER_API_KEY_WEB = $generalobj->getConfigurations("configurations", "GOOGLE_SEVER_API_KEY_WEB");
            //$baseurl1 =  "https://www.googleapis.com/plus/v1/people/114434193354602240754?fields=image&key=AIzaSyB7_FaMl2gU1ItcomolF2S1Fzh8prnvNNw";
            $baseurl1 = "https://www.googleapis.com/plus/v1/people/" . $vFbId . "?fields=image&key=" . $GOOGLE_SEVER_API_KEY_WEB;
            //$url = $vFbId."_".time().".jpg";
            //$url = time().".jpg";
            $url = time() . ".jpg";
            try {
                $jsonfile = file_get_contents($baseurl1);
                $jsondata = json_decode($jsonfile);
                $baseurl = $jsondata
                        ->image->url;
                if (!empty($baseurl)) {
                    $baseurl = str_replace("?sz=50", "?sz=256", $baseurl);
                } else {
                    $baseurl = '';
                }
            } catch (ErrorException $ex) {
                $imgname = "";
                $vimage = $imgname;
            }
        }
        if (!empty($baseurl)) {
            /* file_get_content */
            $profile_Image = $baseurl;
            $userImage = $url;
            $thumb_image = file_get_contents($baseurl);
            $thumb_file = $Photo_Gallery_folder . $url;
            $image_name = file_put_contents($thumb_file, $thumb_image);
            /* file_get_content  ends */
        } else {
            $imgname = "";
            $vimage = $imgname;
        }
        if (is_file($Photo_Gallery_folder . $url)) {
            $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
            //$imgname = $generalobj->general_upload_image($url, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);
            $vimage = $imgname;
        }
    }
    if ($eSignUpType == "Twitter") {
        if ($vImageURL != "") {
            $baseurl = $vImageURL;
        } else {
            require_once ('assets/libraries/twitter/TwitterAPIExchange.php');
            /* $TWITTER_OAUTH_ACCESS_TOKEN = $generalobj->getConfigurations("configurations", "TWITTER_OAUTH_ACCESS_TOKEN");
              $TWITTER_OAUTH_ACCESS_TOKEN_SECRET = $generalobj->getConfigurations("configurations", "TWITTER_OAUTH_ACCESS_TOKEN_SECRET");
              $TWITTER_CONSUMER_KEY = $generalobj->getConfigurations("configurations", "TWITTER_CONSUMER_KEY");
              $TWITTER_CONSUMER_SECRET = $generalobj->getConfigurations("configurations", "TWITTER_CONSUMER_SECRET"); */
            $settings = array(
                'oauth_access_token' => $TWITTER_OAUTH_ACCESS_TOKEN,
                'oauth_access_token_secret' => $TWITTER_OAUTH_ACCESS_TOKEN_SECRET,
                'consumer_key' => $TWITTER_CONSUMER_KEY,
                'consumer_secret' => $TWITTER_CONSUMER_SECRET
            );
            $url = 'https://api.twitter.com/1.1/users/show.json';
            $getfield = '?user_id=' . $vFbId;
            $requestMethod = 'GET';
            $twitter = new TwitterAPIExchange($settings);
            $twitterArr = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
            $jsondata = json_decode($twitterArr); //echo "<pre>";print_r($jsondata);exit;
            $profile_image_url = $jsondata->profile_image_url;
            $baseurl = str_replace("_normal", "", $profile_image_url);
        }
        //$url = $vFbId."_".time().".jpg";
        $url = time() . ".jpg";
        /* file_get_content */
        $profile_Image = $baseurl;
        $userImage = $url;
        $thumb_image = file_get_contents($baseurl);
        $thumb_file = $Photo_Gallery_folder . $url;
        $image_name = file_put_contents($thumb_file, $thumb_image);
        /* file_get_content  ends */
        if (is_file($Photo_Gallery_folder . $url)) {
            $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
            $vimage = $imgname;
        }
    }
    if ($eSignUpType == "LinkedIn") {
        $baseurl = $vImageURL;
        $url = time() . ".jpg";
        $thumb_image = file_get_contents($baseurl);

        $thumb_file = $Photo_Gallery_folder . $url;
        $image_name = file_put_contents($thumb_file, $thumb_image);
        /* file_get_content  ends */
        if (is_file($Photo_Gallery_folder . $url)) {
            $imgname = $generalobj->img_data_upload($Photo_Gallery_folder, $url, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
            $vimage = $imgname;
        }
        //echo $vimage;die;
    }
    return $vimage;
}

function getMemberCountryUnit($iMemberId, $UserType = "Passenger") {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT, $countryCodeAdmin;
    $vCountryfield = "vCountry";
    if (empty($countryCodeAdmin)) {
        if ($UserType == "Passenger") {
            $tblname = "register_user";
            $iUserId = "iUserId";
        } else {
            $tblname = "register_driver";
            $iUserId = "iDriverId";
        }
        $sqlcountryCode = $obj->MySQLSelect("SELECT co.eUnit FROM country as co LEFT JOIN $tblname as rd ON co.vCountryCode = rd.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'");
    } else {
        $sqlcountryCode = $obj->MySQLSelect("SELECT co.eUnit FROM country as co WHERE vCountryCode='" . $countryCodeAdmin . "'");
    }
    $vCountry = "US";
    if (isset($sqlcountryCode[0]['eUnit'])) {
        $vCountry = $sqlcountryCode[0]['eUnit'];
    }
    //$vCountry = $sqlcountryCode[0]['eUnit'];
    //$vCountry = get_value($tblname, $vCountryfield, $iUserId, $iMemberId, '', 'true');
    if ($vCountry == "" || $vCountry == NULL) {
        $vCountryCode = $DEFAULT_DISTANCE_UNIT;
    } else {
        $vCountryCode = $vCountry;
    }
    return $vCountryCode;
}

function getVehicleCountryUnit_PricePerKm($vehicleTypeID, $fPricePerKM, $iMemberId = "", $userType = "") {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT;
    $iLocationid = get_value("vehicle_type", "iLocationid", "iVehicleTypeId", $vehicleTypeID, '', 'true');
    $iCountryId = get_value("location_master", "iCountryId", "iLocationId", $iLocationid, '', 'true');
    if ($iLocationid == "-1") {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    } else {
        $eUnit = get_value("country", "eUnit", "iCountryId", $iCountryId, '', 'true');
    }
    if ($eUnit == "" || $eUnit == NULL) {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    }
    if ($iMemberId != "" && $userType != "") {
        $vCountry = get_value("register_user", "vCountry", "iUserId", $iMemberId, '', 'true');
        if ($vCountry == "") {
            $userUnit = $DEFAULT_DISTANCE_UNIT;
        } else {
            $userUnit = get_value("country", "eUnit", "vCountryCode", $vCountry, '', 'true');
        }
        if ($userUnit == "" || $userUnit == NULL) {
            $userUnit = $DEFAULT_DISTANCE_UNIT;
        }
        if ($userUnit == "Miles" && $eUnit == "Miles") {
            return $fPricePerKM * 0.621371;
        } else if ($userUnit == "KMs" && $eUnit == "Miles") {
            return $fPricePerKM * 1.60934;
        } else if ($userUnit == "Miles" && $eUnit == "KMs") {
            return $fPricePerKM * 0.621371;
        } else if ($userUnit == "KMs" && $eUnit == "KMs") {
            return $fPricePerKM;
        }
    }
    if ($eUnit == "Miles") {
        $PricePerKM = $fPricePerKM * 0.621371;
    } else {
        $PricePerKM = $fPricePerKM;
    }
    return $PricePerKM;
}

function getVehiclePrice_ByUSerCountry($iUserId, $fPricePerKM) {
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT;
    $vCountry = get_value("register_user", "vCountry", "iUserId", $iUserId, '', 'true');
    if ($vCountry == "") {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    } else {
        $eUnit = get_value("country", "eUnit", "vCountryCode", $vCountry, '', 'true');
    }
    if ($eUnit == "" || $eUnit == NULL) {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    }
    if ($eUnit == "Miles") {
        $PricePerKM = $fPricePerKM * 1.60934;
    } else {
        $PricePerKM = $fPricePerKM;
    }
    return $PricePerKM;
}

function GenerateHailTrip($iUserId, $driverId, $selectedCarTypeID, $PickUpLatitude, $PickUpLongitude, $PickUpAddress, $DestLatitude, $DestLongitude, $DestAddress, $fTollPrice = 0, $vTollPriceCurrencyCode = "", $eTollSkipped = "No", $iRentalPackageId = "") {
    global $generalobj, $obj, $APPLY_SURGE_ON_FLAT_FARE, $vTimeZone, $ENABLE_AIRPORT_SURCHARGE_SECTION, $PACKAGE_TYPE;
    $Data['vRideNo'] = rand(10000000, 99999999);
    $Data['iVerificationCode'] = rand(1000, 9999);
    $Data['iUserId'] = $iUserId;
    $Data['iDriverId'] = $driverId;
    $Data['tTripRequestDate'] = @date("Y-m-d H:i:s");
    $Data['iVehicleTypeId'] = $selectedCarTypeID;
    $Data['iDriverVehicleId'] = get_value('register_driver', 'iDriverVehicleId', 'iDriverId', $driverId, '', 'true');
    $Data['iActive'] = 'On Going Trip';
    $Data['tStartDate'] = @date("Y-m-d H:i:s");
    $Data['tDriverArrivedDate'] = @date("Y-m-d H:i:s");
    $Data['tStartLat'] = $PickUpLatitude;
    $Data['tStartLong'] = $PickUpLongitude;
    $Data['tSaddress'] = $PickUpAddress;
    $Data['tEndLat'] = $DestLatitude;
    $Data['tEndLong'] = $DestLongitude;
    $Data['tDaddress'] = $DestAddress;
    $Data['eFareType'] = get_value('vehicle_type', 'eFareType', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');
    $Data['fVisitFee'] = get_value('vehicle_type', 'fVisitFee', 'iVehicleTypeId', $selectedCarTypeID, '', 'true');
    $Data['vTripPaymentMode'] = "Cash";
    $Data['eType'] = "Ride";
    $Data['eHailTrip'] = "Yes";
    $Data['eFareType'] = "Regular";
    $Data['vCountryUnitRider'] = getMemberCountryUnit($iUserId, "Passenger");
    $Data['vCountryUnitDriver'] = getMemberCountryUnit($driverId, "Driver");
    $Data['fTollPrice'] = $fTollPrice;
    $Data['vTollPriceCurrencyCode'] = $vTollPriceCurrencyCode;
    $Data['eTollSkipped'] = $eTollSkipped;
    $currencyList = get_value('currency', '*', 'eStatus', 'Active');
    for ($i = 0; $i < count($currencyList); $i++) {
        $currencyCode = $currencyList[$i]['vName'];
        $Data['fRatio_' . $currencyCode] = $currencyList[$i]['Ratio'];
    }
    $Data['vCurrencyPassenger'] = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    $Data['vCurrencyDriver'] = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $driverId, '', 'true');
    $Data['fRatioPassenger'] = get_value('currency', 'Ratio', 'vName', $Data['vCurrencyPassenger'], '', 'true');
    $Data['fRatioDriver'] = get_value('currency', 'Ratio', 'vName', $Data['vCurrencyDriver'], '', 'true');
    $fPickUpPrice = 1;
    $fNightPrice = 1;
    $sourceLocationArr = array(
        $PickUpLatitude,
        $PickUpLongitude
    );
    $destinationLocationArr = array(
        $DestLatitude,
        $DestLongitude
    );
    $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $selectedCarTypeID, $iRentalPackageId);
    /* changed for rental */
    $data_surgePrice = checkSurgePrice($selectedCarTypeID, $Data['tStartDate'], $iRentalPackageId);
    if ($data_surgePrice['Action'] == "0") {
        if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
            $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
        } else {
            $fNightPrice = $data_surgePrice['SurgePriceValue'];
        }
    }
    if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
        $fPickUpPrice = $fNightPrice = 1;
    }
    $fpickupsurchargefare = $fdropoffsurchargefare = 0;
    if ($ENABLE_AIRPORT_SURCHARGE_SECTION == 'Yes') {
        $GetVehicleIdfromGeoLocation = CheckSurgeAirportFromGeoLocation($sourceLocationArr, $destinationLocationArr, $selectedCarTypeID);
        $fpickupsurchargefare = $GetVehicleIdfromGeoLocation['fpickupsurchargefare'];
        $fdropoffsurchargefare = $GetVehicleIdfromGeoLocation['fdropoffsurchargefare'];
    }

    $Data['eFlatTrip'] = $data_flattrip['eFlatTrip'];
    $Data['fFlatTripPrice'] = $data_flattrip['Flatfare'];
    $Data['fPickUpPrice'] = $fPickUpPrice;
    $Data['fNightPrice'] = $fNightPrice;
    $Data['fAirportPickupSurge'] = $fpickupsurchargefare;
    $Data['fAirportDropoffSurge'] = $fdropoffsurchargefare;
    $Data['vTimeZone'] = $vTimeZone;
    /* added for rental */
    $Data['iRentalPackageId'] = $iRentalPackageId;
    $id = $obj->MySQLQueryPerform("trips", $Data, 'insert');
    //update insurance log
    if ($PACKAGE_TYPE == "SHARK") {
        $details_arr['iTripId'] = $id;
        $details_arr['LatLngArr']['vLatitude'] = $PickUpLatitude;
        $details_arr['LatLngArr']['vLongitude'] = $PickUpLongitude;
        // $details_arr['LatLngArr']['vLocation'] = $Source_point_Address;
        update_driver_insurance_status($driverId, "Accept", $details_arr, "GenerateTrip");
    }
    //update insurance log
    return $id;
}

function sendTripMessagePushNotification($iFromMemberId, $UserType, $iToMemberId, $iTripId, $tMessage) {
    global $generalobj, $obj, $FIREBASE_API_ACCESS_KEY;
    //$FIREBASE_API_ACCESS_KEY = $generalobj->getConfigurations("configurations", "FIREBASE_API_ACCESS_KEY");
    if ($UserType == "Passenger") {
        $tblname = "register_driver";
        $condfield = 'iDriverId';
        $field = 'vFirebaseDeviceToken';
        $Fromtblname = "register_user";
        $Fromcondfield = 'iUserId';
        $pemFileIdentifier = 1;
        $vImageName = "vImgName";
    } else {
        $tblname = "register_user";
        $condfield = 'iUserId';
        $field = 'vFirebaseDeviceToken';
        $Fromtblname = "register_driver";
        $Fromcondfield = 'iDriverId';
        $pemFileIdentifier = 0;
        $vImageName = "vImage";
    }
    $vFirebaseDeviceToken = get_value($tblname, $field, $condfield, $iToMemberId, '', 'true');
    $iGcmRegId = get_value($tblname, "iGcmRegId", $condfield, $iToMemberId, '', 'true');
    $eDeviceType = get_value($tblname, "eDeviceType", $condfield, $iToMemberId, '', 'true');
    $eLogout = get_value($tblname, "eLogout", $condfield, $iToMemberId, '', 'true');
    $MemberName = get_value($Fromtblname, 'vName,vLastName', $Fromcondfield, $iFromMemberId);
    $FromMemberImageName = get_value($Fromtblname, $vImageName, $Fromcondfield, $iFromMemberId, '', 'true');
    $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $iTripId, '', 'true');
    $FromMemberName = $MemberName[0]['vName'];
    // ." ".$MemberName[0]['vLastName']
    if ($eLogout != "Yes") {
        if ($eDeviceType == "Ios") {
            $msg_encode['Msg'] = $tMessage;
            $msg_encode['MsgType'] = "CHAT";
            $msg_encode['iFromMemberId'] = strval($iFromMemberId);
            $msg_encode['iTripId'] = strval($iTripId);
            $msg_encode['vBookingNo'] = strval($vRideNo);
            $msg_encode['FromMemberName'] = strval($FromMemberName);
            $msg_encode['FromMemberImageName'] = strval($FromMemberImageName);
            $msg_encode = json_encode($msg_encode, JSON_UNESCAPED_UNICODE);
            $deviceTokens_arr_ios = array();
            array_push($deviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification($pemFileIdentifier, $deviceTokens_arr_ios, $msg_encode, $tMessage, 0);
        } else {
            $registrationIds = (array) $vFirebaseDeviceToken;
            $msg['aps'] = array(
                'iFromMemberId' => $iFromMemberId,
                'iTripId' => $iTripId,
                'vBookingNo' => $vRideNo,
                'FromMemberName' => $FromMemberName,
                'Msg' => $tMessage,
                'MsgType' => "CHAT",
                'FromMemberImageName' => $FromMemberImageName
                    //'title'   => 'Title Of Notification',
                    //'icon'    => 'myicon',/*Default Icon*/
                    //'sound' => 'mySound'/*Default sound*/
            );
            $fields = array(
                'registration_ids' => $registrationIds,
                'click_action' => ".MainActivity",
                'priority' => "high",
                //'data'          => $msg
                'data' => array(
                    "message" => $msg['aps']
                )
            );
            $headers = array(
                'Authorization: key=' . $FIREBASE_API_ACCESS_KEY,
                'Content-Type: application/json',
            );
            //Setup headers:
            // echo "<pre>";print_r($headers);exit;
            //Setup curl, add headers and post parameters.
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Send the request
            $response = curl_exec($ch); //echo "<pre>";print_r($response);exit;
            $responseArr = json_decode($response);
            //echo "<pre>";print_r($responseArr);exit;
            $success = $responseArr->success;
            //Close request
            curl_close($ch);
            return $success;
        }
    }
}

function UpdateOtherLanguage($vLabel, $vValue, $vLangCode, $tablename) {
    global $generalobj, $obj;
    $sql = "SELECT vCode,vLangCode FROM `language_master` where vCode!='" . $vLangCode . "' ORDER BY `iDispOrder`";
    $db_master = $obj->MySQLSelect($sql);
    $count_all = count($db_master);
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $vCode = $db_master[$i]['vCode'];
            $vGmapCode = $db_master[$i]['vLangCode'];
            $url = 'http://api.mymemory.translated.net/get?q=' . urlencode($vValue) . '&de=harshilmehta1982@gmail.com&langpair=en|' . $vGmapCode;
            $result = file_get_contents($url);
            $finalResult = json_decode($result);
            $getText = $finalResult->responseData;
            $resulttext = $getText->translatedText;
            if ($resulttext == "") {
                $resulttext = $vValue;
            }
            $sql = "SELECT LanguageLabelId FROM $tablename where vLabel = '" . $vLabel . "' AND vCode = '" . $vCode . "'";
            $db_language_label = $obj->MySQLSelect($sql);
            $count = count($db_language_label);
            if ($count > 0) {
                $where = " LanguageLabelId = '" . $db_language_label[0]['LanguageLabelId'] . "'";
                $data_update['vValue'] = $resulttext;
                $obj->MySQLQueryPerform($tablename, $data_update, 'update', $where);
            }
        }
    }
    return $count_all;
}

function get_currency($from_Currency, $to_Currency, $amount) {
    $forignalamount = $amount;
    $amount = urlencode($amount);
    $from_Currency = urlencode($from_Currency);
    $to_Currency = urlencode($to_Currency);
    //$url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
    $url = "https://finance.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
    $ch = curl_init();
    $timeout = 0;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt ($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $rawdata = curl_exec($ch);
    curl_close($ch);
    $data = explode('bld>', $rawdata);
    $data = explode($to_Currency, $data[1]);
    $ftollprice = round($data[0], 2);
    if ($ftollprice == 0 || $ftollprice == 0.00) {
        $ftollprice = $amount;
    }
    //return round($data[0], 2);
    return $ftollprice;
}

function Updateuserlocationdatetime($iMemberId, $user_type = "Passenger", $vTimeZone) {
    global $generalobj, $obj;
    if ($user_type == "Passenger") {
        $tableName = "register_user";
        $iUserId = 'iUserId';
    } else {
        $tableName = "register_driver";
        $iUserId = 'iDriverId';
    }
    $systemTimeZone = date_default_timezone_get();
    $currentdate = @date("Y-m-d H:i:s");
    // $tLocationUpdateDate = converToTz($currentdate,$systemTimeZone,$vTimeZone);
    $tLocationUpdateDate = $currentdate;
    $where = " $iUserId = '$iMemberId' ";
    $Data_update['vTimeZone'] = $vTimeZone;
    $Data_update['tLocationUpdateDate'] = $tLocationUpdateDate;
    $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);
    return true;
}

function getusertripsourcelocations($iMemberId, $type = "SourceLocation") {
    global $generalobj, $obj;
    $ssql = "";
    if ($type == "SourceLocation") {
        $fields = "tStartLat,tStartLong,tSaddress";
        $ssql .= "";
    } else {
        $fields = "tEndLat,tEndLong,tDaddress";
        $ssql .= "AND eType != 'UberX'";
    }
    $sql = "SELECT $fields FROM trips where iUserId = '" . $iMemberId . "' AND iActive = 'Finished' $ssql ORDER BY iTripId DESC";
    $db_passenger_source = $obj->MySQLSelect($sql);
    if (count($db_passenger_source) > 0) {
        $db_passenger_source = array_slice($db_passenger_source, 0, 5);
    } else {
        $db_passenger_source = array();
    }
    return $db_passenger_source;
}

function fetchtripstatustimeinterval() {
    global $generalobj, $obj, $FETCH_TRIP_STATUS_TIME_INTERVAL, $PACKAGE_TYPE;

    $range = "";

    if ($PACKAGE_TYPE == "SHARK") {
        global $Data, $POOL_ENABLE;
        $range = fetchtripstatustimeintervalForPool();
    }

    if ($range == "") {
        //$FETCH_TRIP_STATUS_TIME_INTERVAL = $generalobj->getConfigurations("configurations", "FETCH_TRIP_STATUS_TIME_INTERVAL");
        $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL);
        $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[0];
        $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN - 4;
        if ($FETCH_TRIP_STATUS_TIME_INTERVAL_MIN < 15) {
            $FETCH_TRIP_STATUS_TIME_INTERVAL_MIN = 15;
        }
        $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
        $range = rand($FETCH_TRIP_STATUS_TIME_INTERVAL_MIN, $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX);
    }
    return $range;
}

function fetchtripstatustimeMAXinterval() {
    global $generalobj, $obj, $FETCH_TRIP_STATUS_TIME_INTERVAL;
    //$FETCH_TRIP_STATUS_TIME_INTERVAL = $generalobj->getConfigurations("configurations", "FETCH_TRIP_STATUS_TIME_INTERVAL");
    $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR = explode("-", $FETCH_TRIP_STATUS_TIME_INTERVAL);
    $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX = $FETCH_TRIP_STATUS_TIME_INTERVAL_ARR[1];
    return $FETCH_TRIP_STATUS_TIME_INTERVAL_MAX;
}

function CheckAvailableTimes($str) {
    if ($str != "") {
        $str = str_replace("00", "12", $str);
        $strArr = explode(",", $str);
        $returnArr = array();
        for ($i = 0; $i < count($strArr); $i++) {
            $number = $strArr[$i];
            $numberArr = explode("-", $number);
            $number1 = $numberArr[0];
            $number2 = $numberArr[1];
            $number1 = str_pad($number1, 2, '0', STR_PAD_LEFT);
            $number2 = str_pad($number2, 2, '0', STR_PAD_LEFT);
            $finalnumber = $number1 . "-" . $number2;
            $returnArr[] = $finalnumber;
        }

        $vAvailableTimes = implode(",", $returnArr);
    } else {
        $vAvailableTimes = "";
    }

    return $vAvailableTimes;
}

function checkRestrictedAreaNew($Address_Array, $DropOff) {
    global $generalobj, $obj;
    $ssql = "";
    if ($DropOff == "No") {
        $ssql .= " AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
    } else {
        $ssql .= " AND (eRestrictType = 'Drop Off' OR eRestrictType = 'All')";
    }

    if (!empty($Address_Array)) {
        $sqlaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Allowed'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        $allowed_ans = 'No';
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array(
                            $latitude[$x],
                            $longitude[$x]
                        );
                    }
                }

                // print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $allowed_ans = 'Yes';
                        break;
                    }
                }
            }
        }

        if ($allowed_ans == 'No') {
            $sqlas = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Disallowed'" . $ssql;
            $restricted_data = $obj->MySQLSelect($sqlas);
            $allowed_ans = 'Yes';
            if (!empty($restricted_data)) {
                $polygon_dis = array();
                foreach ($restricted_data as $key => $value) {
                    $latitude = explode(",", $value['tLatitude']);
                    $longitude = explode(",", $value['tLongitude']);
                    for ($x = 0; $x < count($latitude); $x++) {
                        if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                            $polygon_dis[$key][] = array(
                                $latitude[$x],
                                $longitude[$x]
                            );
                        }
                    }

                    if ($polygon_dis[$key]) {
                        $address_dis = contains($Address_Array, $polygon_dis[$key]) ? 'IN' : 'OUT';
                        if ($address_dis == 'IN') {
                            $allowed_ans = 'No';
                            break;
                        }
                    }
                }
            }
        }
    }

    return $allowed_ans;
}

function contains($point, $polygon) {
    if ($polygon[0] != $polygon[count($polygon) - 1])
        $polygon[count($polygon)] = $polygon[0];
    $j = 0;
    $oddNodes = false;
    $x = $point[1];
    $y = $point[0];
    $n = count($polygon);
    for ($i = 0; $i < $n; $i++) {
        $j++;
        if ($j == $n) {
            $j = 0;
        }
        //echo $polygon[$i][0]."==".$y."<br>";
        //echo $polygon[$i][1]."==".$x."<br>";
        if ((($polygon[$i][0] < $y) && ($polygon[$j][0] >= $y)) || (($polygon[$j][0] < $y) && ($polygon[$i][0] >= $y))) {
            if ($polygon[$i][1] + ($y - $polygon[$i][0]) / ($polygon[$j][0] - $polygon[$i][0]) * ($polygon[$j][1] - $polygon[$i][1]) < $x) {
                $oddNodes = !$oddNodes;
            }
        }
    }
    return $oddNodes;
}

function GetVehicleTypeFromGeoLocation($Address_Array) {
    global $generalobj, $obj;
    $Vehicle_Str = "-1";
    if (!empty($Address_Array)) {
        $sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'VehicleType'";
        $allowed_data = $obj->MySQLSelect($sqlaa);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array(
                            $latitude[$x],
                            $longitude[$x]
                        );
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $Vehicle_Str .= "," . $val['iLocationId'];
                        //break;
                    }
                }
            }
        }
    }
    return $Vehicle_Str;
}

function DisplayBookingDetails($iCabBookingId) {
    global $generalobj, $obj;
    $returnArr = array();
	
	$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
	
    $sql = "SELECT * FROM `cab_booking` WHERE iCabBookingId = '" . $iCabBookingId . "'";
    $db_booking = $obj->MySQLSelect($sql);
    $serverTimeZone = date_default_timezone_get();
    $db_booking[0]['dBooking_dateOrig'] = converToTz($db_booking[0]['dBooking_date'], $db_booking[0]['vTimeZone'], $serverTimeZone);
    $seldatetime = $db_booking[0]['dBooking_dateOrig'];
    $selecteddate = date("Y-m-d", strtotime($seldatetime));
    $newdate = explode(" ", $seldatetime);
    $time_in_12_hour_format = date("a", strtotime($seldatetime));
    $timearr = explode(":", $newdate[1]);
    $timearr1 = $timearr[0];
    $timearr1 = $timearr1 % 12;
    $timearr2 = $timearr1 + 1;
    $number1 = str_pad($timearr1, 2, '0', STR_PAD_LEFT);
    $number2 = str_pad($timearr2, 2, '0', STR_PAD_LEFT);
    $selectedtime = $number1 . "-" . $number2 . " " . $time_in_12_hour_format;
    $scheduletime1 = $timearr[0];
    $scheduletime2 = $scheduletime1 + 1;
    $scheduletime1 = str_pad($scheduletime1, 2, '0', STR_PAD_LEFT);
    $scheduletime2 = str_pad($scheduletime2, 2, '0', STR_PAD_LEFT);
    $scheduledate = $selecteddate . " " . $scheduletime1 . "-" . $scheduletime2;
    $userId = $db_booking[0]['iUserId'];
    $sql1 = "SELECT vLang,vCurrencyPassenger FROM `register_user` WHERE iUserId='$userId'";
    $row = $obj->MySQLSelect($sql1);
    $lang = $row[0]['vLang'];
    //if($lang == "" || $lang == NULL) { $lang = "EN"; }
    if ($lang == "" || $lang == NULL) {
        $lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $vCurrencyPassenger = $row[0]['vCurrencyPassenger'];
    if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
        $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    }
    $UserCurrencyData = get_value('currency', 'vSymbol, Ratio', 'vName', $vCurrencyPassenger);
    $priceRatio = $UserCurrencyData[0]['Ratio'];
    $vSymbol = $UserCurrencyData[0]['vSymbol'];
    $driverId = $db_booking[0]['iDriverId'];
    $sql = "SELECT iDriverVehicleId from driver_vehicle WHERE iDriverId = '" . $driverId . "'";
    $db_drv_vehicle = $obj->MySQLSelect($sql);
    $iDriverVehicleId = $db_drv_vehicle[0]['iDriverVehicleId'];
    $iVehicleTypeId = $db_booking[0]['iVehicleTypeId'];

    $tVehicleTypeDataArr = array();
    if ($db_booking[0]['tVehicleTypeData'] != "" /* && $iVehicleTypeId == 0 */) {
        $tVehicleTypeDataArr = (array) json_decode($db_booking[0]['tVehicleTypeData']);

        if (count($tVehicleTypeDataArr) > 0) {
            $tmpTVehicleTypeDataArr = (array) $tVehicleTypeDataArr[0];
            $iVehicleTypeId = $tmpTVehicleTypeDataArr['iVehicleTypeId'];
        }
    }

    $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.vCategoryTitle_" . $lang . " as vCategoryTitle, vc.tCategoryDesc_" . $lang . " as tCategoryDesc, vc.ePriceType, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM ".$sql_vehicle_category_table_name." as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vt.iVehicleTypeId='" . $iVehicleTypeId . "'";
    $Data = $obj->MySQLSelect($sql2);
    $iParentId = $Data[0]['iParentId'];
    // echo "ParentID:".$iParentId;exit;
    if ($iParentId == 0) {
        $ePriceType = $Data[0]['ePriceType'];
    } else {
        $data_category_tmp_price = get_value($sql_vehicle_category_table_name, "ePriceType,vCategory_" . $lang . " as vCategory", 'iVehicleCategoryId', $iParentId);
        $ePriceType = $data_category_tmp_price[0]['ePriceType'];

        if (count($tVehicleTypeDataArr) > 0) {
            $Data[0]['vCategory'] = $data_category_tmp_price[0]['vCategory'];
        }
    }
    $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";
    $fAmount = 0;
    if ($Data[0]['eFareType'] == "Fixed") {
        //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fFixedFare'];
        $fAmount = $Data[0]['fFixedFare'];
    } else if ($Data[0]['eFareType'] == "Hourly") {
        //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fPricePerHour']."/hour";
        $fAmount = $Data[0]['fPricePerHour'];
    }
    $iPrice = $fAmount;
    if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
        $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
        $serviceProData = $obj->MySQLSelect($sqlServicePro);
        if (count($serviceProData) > 0) {
            $fAmount = $serviceProData[0]['fAmount'];
        } else {
            $fAmount = $iPrice;
        }
        $iPrice = $fAmount;
    }
    $returnArr['selectedtime'] = $selectedtime; // 01-02 am
    $returnArr['selecteddatetime'] = $scheduledate; // 2017-10-25 01-02
    $returnArr['SelectedFareType'] = $Data[0]['eFareType'];
    $returnArr['SelectedQty'] = $db_booking[0]['iQty'];
    $returnArr['SelectedPrice'] = $iPrice;
    $returnArr['SelectedCurrencySymbol'] = $vSymbol;
    $returnArr['SelectedCurrencyRatio'] = $priceRatio;
    $returnArr['SelectedVehicle'] = $Data[0]['vVehicleType'];

    $returnArr['SelectedCategory'] = $Data[0]['vCategory'];
    $returnArr['SelectedCategoryId'] = $Data[0]['iVehicleCategoryId'];
    $returnArr['SelectedCategoryTitle'] = $Data[0]['vCategoryTitle'];
    $returnArr['SelectedCategoryDesc'] = $Data[0]['tCategoryDesc'];
    $returnArr['SelectedAllowQty'] = $Data[0]['eAllowQty'];
    $returnArr['SelectedPriceType'] = $Data[0]['ePriceType'];
    $returnArr['ALLOW_SERVICE_PROVIDER_AMOUNT'] = $ALLOW_SERVICE_PROVIDER_AMOUNT;

    return $returnArr;
}

function getTripChatDetails($iTripId) {
    global $obj, $generalobj, $tconfig, $FIREBASE_DEFAULT_URL, $FIREBASE_DEFAULT_TOKEN, $GOOGLE_SENDER_ID;
    require_once ('assets/libraries/firebase/src/firebaseInterface.php');
    require_once ('assets/libraries/firebase/src/firebaseLib.php');
    //$DEFAULT_URL = 'https://ufxv4app.firebaseio.com/';
    //$DEFAULT_TOKEN = 'xcmWvKUsFF9rP7UmZp9qd14powmT1VH8GW1457aO';
    //$DEFAULT_PATH = '835770094542-chat';
    /* $FIREBASE_DEFAULT_URL = $generalobj->getConfigurations("configurations", "FIREBASE_DEFAULT_URL");
      $FIREBASE_DEFAULT_TOKEN = $generalobj->getConfigurations("configurations", "FIREBASE_DEFAULT_TOKEN");
      $GOOGLE_SENDER_ID = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID"); */
    $DEFAULT_PATH = $GOOGLE_SENDER_ID . "-chat";
    $firebase = new \Firebase\FirebaseLib($FIREBASE_DEFAULT_URL, $FIREBASE_DEFAULT_TOKEN);
    $fetch = $firebase->get($DEFAULT_PATH . '/' . $iTripId . '-Trip'); // reads value from Firebase
    $fetchdeco = json_decode($fetch);
    foreach ($fetchdeco as $Tripobj) {
        $Data['iTripId'] = $Tripobj->iTripId;
        $Data['tMessage'] = $Tripobj->Text;
        $iUserId = $Tripobj->passengerId;
        $iDriverId = $Tripobj->driverId;
        $Data['dAddedDate'] = @date("Y-m-d H:i:s");
        $eUserType = $Tripobj->eUserType;
        $Data['eUserType'] = $eUserType;
        $Data['eStatus'] = "Unread";
        $Data['iFromMemberId'] = ($eUserType == "Passenger") ? $iUserId : $iDriverId;
        $Data['iToMemberId'] = ($eUserType == "Passenger") ? $iDriverId : $iUserId;
        $id = $obj->MySQLQueryPerform("trip_messages", $Data, 'insert');
    }
    $delchat = $firebase->delete($DEFAULT_PATH . '/' . $iTripId . '-Trip'); // deletes value from Firebase
    return $iTripId;
}

function getMemberAverageRating($iMemberId, $eFor = "Passenger", $date = "") {
    global $generalobj, $obj;
    $ssql = "";
    if ($eFor == "Passenger") {
        $UserType = "Driver";
        $iUserId = "iUserId";
        $ssql .= "AND tr.iUserId = '" . $iMemberId . "'";
    } else {
        $UserType = "Passenger";
        $iUserId = "iDriverId";
        $ssql .= "AND tr.iDriverId = '" . $iMemberId . "'";
    }
    if ($date != "") {
        $ssql .= " AND tr.tTripRequestDate LIKE '" . $date . "%' ";
    }
    $sqlcount = "SELECT vRating1 FROM ratings_user_driver as rsu LEFT JOIN trips as tr ON rsu.iTripId=tr.iTripId WHERE rsu.eUserType='" . $UserType . "' AND tr.eHailTrip = 'No' AND (tr.eBookingFrom != 'Hotel' OR tr.eBookingFrom != 'Kiosk') And tr.iActive = 'Finished'" . $ssql;
    $dbtriprating = $obj->MySQLSelect($sqlcount);
    $avgRating = 0;
    $totalRating = 0;
    $count = count($dbtriprating);
    if (count($dbtriprating) > 0) {
        for ($i = 0; $i < count($dbtriprating); $i++) {
            $vRating1 = $dbtriprating[$i]['vRating1'];
            $totalRating = $totalRating + $vRating1;
        }
        $avgRating = round(($totalRating / $count), 2);
    }
    return $avgRating;
}

function checkAllowedAreaNew($Address_Array, $DropOff) {
    global $generalobj, $obj;
    $ssql = "";
    if ($DropOff == "No") {
        $ssql .= " AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
    } else {
        $ssql .= " AND (eRestrictType = 'Drop Off' OR eRestrictType = 'All')";
    }
    if (!empty($Address_Array)) {
        ############### Check For Allow Location ######################################
        $sqlaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Allowed'" . $ssql;
        $allowed_data = $obj->MySQLSelect($sqlaa);
        if (count($allowed_data) > 0) {
            $allowed_ans = 'No';
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array(
                            $latitude[$x],
                            $longitude[$x]
                        );
                    }
                }
                //print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $allowed_ans = 'Yes';
                        break;
                    }
                }
            }
        } else {
            $allowed_ans = 'Yes';
        }
        ############### Check For Allow Location ######################################
        ############### Check For DisAllow Location ######################################
        if ($allowed_ans == 'Yes') {
            $sqldaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Disallowed'" . $ssql;
            $disallowed_data = $obj->MySQLSelect($sqldaa);
            if (count($disallowed_data) > 0) {
                $allowed_ans = 'Yes';
                $polygon = array();
                foreach ($disallowed_data as $key => $val) {
                    $latitude = explode(",", $val['tLatitude']);
                    $longitude = explode(",", $val['tLongitude']);
                    for ($x = 0; $x < count($latitude); $x++) {
                        if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                            $polygon[$key][] = array(
                                $latitude[$x],
                                $longitude[$x]
                            );
                        }
                    }
                    //print_r($polygon[$key]);
                    if ($polygon[$key]) {
                        $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                        if ($address == 'IN') {
                            $allowed_ans = 'No';
                            break;
                        }
                    }
                }
            } else {
                $allowed_ans = 'Yes';
            }
        }
        ############### Check For DisAllow Location ######################################
    }
    return $allowed_ans;
}

############### Insert Pushnotification Message Into Firebase  ######################################

function InsertMessageIntoFirebase($UserType, $iMemberId, $Message_arr) {
    global $obj, $generalobj, $tconfig, $FIREBASE_DEFAULT_URL, $FIREBASE_DEFAULT_TOKEN, $GOOGLE_SENDER_ID;
    require_once ('assets/libraries/firebase/src/firebaseInterface.php');
    require_once ('assets/libraries/firebase/src/firebaseLib.php');
    //$DEFAULT_URL = 'https://ufxv4app.firebaseio.com/';
    //$DEFAULT_TOKEN = 'xcmWvKUsFF9rP7UmZp9qd14powmT1VH8GW1457aO';
    //$DEFAULT_PATH = '835770094542-chat';
    /* $FIREBASE_DEFAULT_URL = $generalobj->getConfigurations("configurations", "FIREBASE_DEFAULT_URL");
      $FIREBASE_DEFAULT_TOKEN = $generalobj->getConfigurations("configurations", "FIREBASE_DEFAULT_TOKEN");
      $GOOGLE_SENDER_ID = $generalobj->getConfigurations("configurations", "GOOGLE_SENDER_ID"); */
    $FIREBASE_DEFAULT_URL = "https://cubetaxiplus-app.firebaseio.com/";
    $FIREBASE_DEFAULT_TOKEN = "FlKf2SLG0J015ZHyxz4T69njoYD8ssDFsYEYjm6g";
    $GOOGLE_SENDER_ID = "835770094542";
    $DEFAULT_PATH = $UserType;
    $firebase = new \Firebase\FirebaseLib($FIREBASE_DEFAULT_URL, $FIREBASE_DEFAULT_TOKEN);
    $insert = $firebase->push($DEFAULT_PATH . '/' . $iMemberId, $Message_arr); // Insert value into Firebase
    $returnJSON = json_decode($insert);
    return $returnJSON;
}

############### Insert Pushnotification Message Into Firebase Ends ######################################
############### Get User Country Tax ###################################################################

function getMemberCountryTax($iMemberId, $UserType = "Passenger") {
    global $generalobj, $obj, $countryCodeAdmin;
    $returnArr = array();
    $vCountryfield = "vCountry";
    if (empty($countryCodeAdmin)) {
        if ($UserType == "Passenger") {
            $tblname = "register_user";
            $iUserId = "iUserId";
        } else {
            $tblname = "register_driver";
            $iUserId = "iDriverId";
        }
        $sqlcountryTax = $obj->MySQLSelect("SELECT COALESCE(co.fTax1, '0') as fTax1,COALESCE(co.fTax2, '0') as fTax2 FROM country as co LEFT JOIN $tblname as ru ON co.vCountryCode = ru.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'");
    } else {
        $sqlcountryTax = $obj->MySQLSelect("SELECT COALESCE(co.fTax1, '0') as fTax1,COALESCE(co.fTax2, '0') as fTax2 FROM country as co WHERE vCountryCode='" . $countryCodeAdmin . "'");
    }
    $fTax1 = $fTax2 = 0;
    if (count($sqlcountryTax) > 0) {
        $fTax1 = $sqlcountryTax[0]['fTax1'];
        $fTax2 = $sqlcountryTax[0]['fTax2'];
    }
    $returnArr['fTax1'] = $fTax1;
    $returnArr['fTax2'] = $fTax2;
    return $returnArr;
}

############### Get User Country Tax ###################################################################
############### Check FlatTrip Or Not  ###################################################################
########################### Get Passenger Outstanding Amount#############################################################

function GetPassengerOutstandingAmount($iUserId) {
    global $generalobj, $obj, $_REQUEST, $data_trips, $PACKAGE_TYPE;
    if ($PACKAGE_TYPE == "SHARK") {
        global $_REQUEST, $data_trips;
        return GetPassengerOutstandingAmountShark($iUserId);
    } else {
        return GetPassengerOutstandingAmountOrg($iUserId);
    }
}

function GetPassengerOutstandingAmountOrg($iUserId) {
    global $generalobj, $obj, $iOrganizationId, $ePaymentBy, $SYSTEM_PAYMENT_FLOW;
    $iOrganizationId = 0;
    $ePaymentBy = "Passenger";
    $outStandingSql = "";
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
        $outStandingSql = " AND eAuthoriseIdName='No' AND iAuthoriseId ='0'";
    }
    if ($ePaymentBy == "Passenger") {
        $sql = "SELECT SUM(fPendingAmount) as fPendingAmount FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND iUserId > 0 AND ePaidByPassenger = 'No' $outStandingSql";
    }

    $tripoutstandingdata = $obj->MySQLSelect($sql);
    $fPendingAmount = round($tripoutstandingdata[0]['fPendingAmount'], 2);
    if ($fPendingAmount == "" || $fPendingAmount == NULL) {
        $fPendingAmount = 0;
    }
    return $fPendingAmount;
}

########################### Get Passenger  Outstanding Amount#############################################################
############################# Update  User's  SMS Resending Limit and Rest Verification count and date For Emergency Contact###################################################################

function UpdateUserSmsLimitForEmergency($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY, $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_EMERGENCY;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCountEmergency,dSendverificationDateEmergency';
        $condfield = 'iUserId';
    } else {
        $tblname = "register_driver";
        $fields = 'vVerificationCountEmergency,dSendverificationDateEmergency';
        $condfield = 'iDriverId';
    }
    $sql = "select $fields from $tblname where $condfield='" . $iMemberId . "'";
    $db_user = $obj->MySQLSelect($sql);
    $vVerificationCount = $db_user[0]['vVerificationCountEmergency'];
    $dSendverificationDate = $db_user[0]['dSendverificationDateEmergency'];
    $currentdate = @date("Y-m-d H:i:s");
    $checklastcount = $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY - 1;
    if ($vVerificationCount == $checklastcount) {
        $minutes = $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_EMERGENCY;
        $expire_stamp = date('Y-m-d H:i:s', strtotime("+" . $minutes . " minute"));
        $updateQuery = "UPDATE $tblname set dSendverificationDateEmergency='" . $expire_stamp . "',vVerificationCountEmergency = vVerificationCountEmergency+1 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
    } else {
        $vVerificationCount = $vVerificationCount + 1;
        if ($vVerificationCount > $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY) {
            $vVerificationCount = $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY;
        }
        $updateQuery = "UPDATE $tblname set vVerificationCountEmergency = '" . $vVerificationCount . "' WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
    }
    return $iMemberId;
}

############################# Update  User's  SMS Resending Limit and Rest Verification count and date For Emergency Contact ###################################################################
############################################################## Get Socket URL ###############################################################################################################

function getSocketURL() {
    global $tconfig;
    $url = $tconfig["tsite_sc_protocol"] . $tconfig["tsite_sc_host"] . ":" . $tconfig["tsite_host_sc_port"] . $tconfig["tsite_host_sc_path"];
    return $url;
}

############################################################## Get Socket URL ###############################################################################################################
############################################################## Get publishEventMessage ###############################################################################################################

function publishEventMessage($channelName, $message) {
    global $tconfig, $ENABLE_SOCKET_CLUSTER, $PUBSUB_TECHNIQUE, $YALGAAR_CLIENT_KEY, $PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY, $uuid;
    if ($PUBSUB_TECHNIQUE == "SocketCluster") {
        global $socketClsObj, $websocket;
        if (empty($socketClsObj)) {
            $optionsOrUri = ['secure' => false, 'host' => $tconfig['tsite_sc_host'], 'port' => $tconfig['tsite_host_sc_port'], 'path' => $tconfig['tsite_host_sc_path']];
            $websocket = \SocketCluster\WebSocket::factory($optionsOrUri);
            $socket = new \SocketCluster\SocketCluster($websocket);
            $socketClsObj = $socket;
        } else {
            $socket = $socketClsObj;
        }

        $dataCHK = $socket->publish($channelName, $message);
        //$websocket->close();
    } else if ($PUBSUB_TECHNIQUE == "PubNub") {
        global $pubNubClsObj;
        if (empty($pubNubClsObj)) {
            $pubnub = new Pubnub\Pubnub(array(
                "publish_key" => $PUBNUB_PUBLISH_KEY,
                "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
                "uuid" => $uuid
            ));

            $pubNubClsObj = $pubnub;
        } else {
            $pubnub = $pubNubClsObj;
        }

        $info = $pubnub->publish($channelName, $message);
    } else if ($PUBSUB_TECHNIQUE == "Yalgaar") {
        $postdata = array();
        $postdata['yalgaarClientKey'] = $YALGAAR_CLIENT_KEY;
        $postdata['channelName'] = $channelName;
        $postdata['messageData'] = json_decode($message, true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tconfig["tsite_yalgaar_url"]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata, JSON_UNESCAPED_UNICODE)); //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
    }
    return true;
}

############################################################## Get publishEventMessage ###############################################################################################################
########################### General Icon Banner #############################################################

function getGeneralVarAll_IconBanner() {
    global $obj, $APP_TYPE;
    //$listField = $obj->MySQLGetFieldsQuery("setting");
    $ssql = "";
    /* if(ENABLE_RENTAL_OPTION == 'No') {
      $ssql .= " AND eRentalType = 'No' ";
      } */
    $wri_usql = "SELECT iSettingId,vName,TRIM(vValue) as vValue,eImageType,eRentalType FROM configurations_cubejek where 1" . $ssql;
    $wri_ures = $obj->MySQLSelect($wri_usql);
    return $wri_ures;
}

########################### General Icon Banner #############################################################
########################### Change Driver's Selected Vehicle  to  0 if Ride Delivery Feature Enable ##############################################

function ChangeDriverVehicleRideDeliveryFeatureDisable($iDriverId) {
    global $obj, $APP_TYPE, $generalobj;
    $eShowRideVehicles = "Yes";
    $eShowDeliveryVehicles = "Yes";
    $sqldata = "SELECT iTripId FROM `trips` WHERE ( iActive='On Going Trip' OR iActive='Active' ) AND iDriverId='" . $iDriverId . "'";
    $TripData = $obj->MySQLSelect($sqldata);
    $TripRunCount = count($TripData);
    if ($APP_TYPE == "Ride-Delivery-UberX" && $TripRunCount == 0) {
        $RideDeliveryIconArr = getGeneralVarAll_IconBanner();
        for ($i = 0; $i < count($RideDeliveryIconArr); $i++) {
            $vName = $RideDeliveryIconArr[$i]['vName'];
            $vValue = $RideDeliveryIconArr[$i]['vValue'];
            $$vName = $vValue;
            $Data[0][$vName] = $$vName;
        }
        $checkridedelivery = CheckRideDeliveryFeatureDisable();
        $eShowRideVehicles = $checkridedelivery['eShowRideVehicles'];
        $eShowDeliveryVehicles = $checkridedelivery['eShowDeliveryVehicles'];

        $sql = "SELECT eType,dv.vCarType FROM `driver_vehicle` as dv LEFT JOIN register_driver as rd ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.iDriverId='" . $iDriverId . "'";
        $DriverVehicleType = $obj->MySQLSelect($sql);
        $vCarType = $DriverVehicleType[0]['vCarType'];

        $sql1 = "SELECT eType,iVehicleTypeId FROM  `vehicle_type` WHERE iVehicleTypeId IN (" . $vCarType . ")";
        $VehicleTypeData = $obj->MySQLSelect($sql1);
        $vehiclearray = array();
        foreach ($VehicleTypeData as $key => $value) {
            $vehiclearray[] = $value['eType'];
        }

        if ($eShowRideVehicles == 'No' && (count(array_unique($vehiclearray)) === 1 && end($vehiclearray) === 'Ride')) {
            $sql = "UPDATE register_driver set iDriverVehicleId='0' WHERE iDriverId='" . $iDriverId . "'";
            $obj->sql_query($sql);
        }

        if ($eShowDeliveryVehicles == 'No' && (count(array_unique($vehiclearray)) === 1 && end($vehiclearray) === 'Deliver')) {
            $sql = "UPDATE register_driver set iDriverVehicleId='0' WHERE iDriverId='" . $iDriverId . "'";
            $obj->sql_query($sql);
        }
    }
    return $iDriverId;
}

########################### Change Driver's Selected Vehicle  to  0 if Ride Delivery Feature Enable ##############################################
########################### Check Ride Delivery Feature Enable ##############################################

/* function CheckRideDeliveryFeatureDisable() {
  global $obj, $APP_TYPE, $generalobj;
  $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
  $eShowRideVehicles = "No";
  $eShowDeliveryVehicles = "Yes";
  $eShowDeliverAllVehicles = "Yes";
  $RideDeliveryBothFeatureDisable = "No";
  if ($APP_TYPE == "Ride-Delivery-UberX") {
  //$RideDeliveryIconArr = getGeneralVarAll_IconBanner();
  $vCatSQL = "SELECT iVehicleCategoryId,eStatus,eCatType,iParentId  FROM ".$sql_vehicle_category_table_name." WHERE eCatType IN ('Ride','MotoRide','Rental','MotoRental') AND eStatus = 'Active'";
  $RideDeliveryIconArrNew = $obj->MySQLSelect($vCatSQL);


  for ($i = 0; $i < count($RideDeliveryIconArrNew); $i++) {
  $vName = $RideDeliveryIconArrNew[$i]['eCatType'];
  $vValue = $RideDeliveryIconArrNew[$i]['iParentId'];
  $$vName = $vValue;
  $CatData[$vName] = $$vName;
  }

  if ($CatData['Ride'] == 0 || $CatData['MotoRide'] == 0 || $CatData['Rental'] == 0 || $CatData['MotoRental'] == 0) {
  $eShowRideVehicles = "Yes";
  }

  $Gsql = "SELECT iVehicleCategoryId,eStatus,eCatType,iParentId FROM ".$sql_vehicle_category_table_name." WHERE eCatType != 'ServiceProvider' AND efor = ''";
  $RideDeliveryIconArr = $obj->MySQLSelect($Gsql);
  // for ($i = 0; $i < count($RideDeliveryIconArr); $i++) {
  //  $vName = $RideDeliveryIconArr[$i]['eCatType'];
  //  $vValue = $RideDeliveryIconArr[$i]['eStatus'];
  //  $$vName = $vValue;
  //  $Data[0][$vName] = $$vName;
  //  }
  foreach ($RideDeliveryIconArr as $key => $value) {
  $vName = $value['eCatType'];
  $vValue = $value['eStatus'];
  $$vName = $vValue;
  $Data[$key][$vName] = $$vName;
  }

  for ($i = 0; $i < count($Data); $i++) {
  if (isset($Data[$i]['Delivery']) == false && isset($Data[$i]['MotoDelivery']) == false && isset($Data[$i]['DeliverAll']) == false && $eShowRideVehicles == 'No') {
  if ($Data[$i][key($Data[$i])] == 'Active') {
  $eShowRideVehicles = "Yes";
  }
  }
  if ((isset($Data[$i]['Delivery']) == true || isset($Data[$i]['MotoDelivery']) == true) && $eShowDeliveryVehicles == 'No') {
  if ($Data[$i][key($Data[$i])] == 'Active') {
  $eShowDeliveryVehicles = "Yes";
  }
  }
  if (isset($Data[$i]['DeliverAll']) == true && $eShowDeliverAllVehicles == 'No') {
  if ($Data[$i][key($Data[$i])] == 'Active') {
  $eShowDeliverAllVehicles = "Yes";
  }
  }
  }

  // if ($RideDeliveryIconArr[0]['eCatType'] == 'None' && $Data[0]['RENTAL_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_RIDE_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_RENTAL_SHOW_SELECTION'] == 'None') {
  //  $eShowRideVehicles = "No";
  //  }
  //  if ($Data[0]['DELIVERY_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_DELIVERY_SHOW_SELECTION'] == 'None') {
  //  $eShowDeliveryVehicles = "No";
  //  }
  //  if (($Data[0]['FOOD_APP_SHOW_SELECTION'] == 'None' && $Data[0]['GROCERY_APP_SHOW_SELECTION'] == 'None') || $Data[0]['DELIVER_ALL_APP_SHOW_SELECTION'] == 'None') {
  //  $eShowDeliverAllVehicles = "No";
  //  }
  }
  if ($eShowRideVehicles == "No" && $eShowDeliveryVehicles == "No") {
  $RideDeliveryBothFeatureDisable = "Yes";
  }
  $returnArr['eShowRideVehicles'] = $eShowRideVehicles;
  $returnArr['eShowDeliveryVehicles'] = $eShowDeliveryVehicles;
  $returnArr['eShowDeliverAllVehicles'] = $eShowDeliverAllVehicles;
  $returnArr['RideDeliveryBothFeatureDisable'] = $RideDeliveryBothFeatureDisable;
  return $returnArr;
  } */

//Added By HJ On 10-01-2019 For Check Ride and Delivery Feature Start

function CheckRideDeliveryFeatureDisable() {
    global $obj, $APP_TYPE, $generalobj;
	$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    $eShowRideVehicles = $eShowDeliveryVehicles = $eShowDeliverAllVehicles = $RideDeliveryBothFeatureDisable = "No";
    $eMotoRideEnable = $eMotoDeliveryEnable = $eRentalEnable = $eMotoRentalEnable = "Yes";
    if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Delivery") {
        $eMotoRideEnable = $eMotoDeliveryEnable = $eRentalEnable = $eMotoRentalEnable = "No";
        $ssql = '';
        if ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Delivery") {
            $ssql .= " AND eFor = 'DeliveryCategory' AND eCatType ='MoreDelivery'";
        }
        $vCatSQL = "SELECT iVehicleCategoryId,eStatus,eCatType,iParentId,eFor  FROM ".$sql_vehicle_category_table_name." WHERE eCatType != 'ServiceProvider' ";
        $rideDeliveryIconData = $obj->MySQLSelect($vCatSQL);
        //echo "<pre>";
        //print_r($rideDeliveryIconData);die;
        ########### Stage 1 ###########
        if ($APP_TYPE == "Ride-Delivery-UberX") {
            for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                $data_temp = $rideDeliveryIconData[$i];
                if ($data_temp['eCatType'] == "Ride" || $data_temp['eCatType'] == "MotoRide" || $data_temp['eCatType'] == "Rental" || $data_temp['eCatType'] == "MotoRental") {
                    $iParentId_tmp = $data_temp['iParentId'];
                    $eStatus_tmp = $data_temp['eStatus'];
                    if ($eStatus_tmp == "Active" && ($iParentId_tmp == 0 || $iParentId_tmp == "0")) {
                        $eShowRideVehicles = "Yes";
                    }
                } else if ($data_temp['eCatType'] == "Delivery" || $data_temp['eCatType'] == "MotoDelivery") {
                    $iParentId_tmp = $data_temp['iParentId'];
                    $eStatus_tmp = $data_temp['eStatus'];
                    if ($eStatus_tmp == "Active" && ($iParentId_tmp == 0 || $iParentId_tmp == "0")) {
                        $eShowDeliveryVehicles = "Yes";
                    }
                } else if ($data_temp['eCatType'] == "DeliverAll") {
                    $iParentId_tmp = $data_temp['iParentId'];
                    $eStatus_tmp = $data_temp['eStatus'];
                    if ($eStatus_tmp == "Active" && ($iParentId_tmp == 0 || $iParentId_tmp == "0")) {
                        $eShowDeliverAllVehicles = "Yes";
                    }
                }
            }
        }
        // Ride Enable Checking
        ########## Stage 1 ############ Get Main Category #######
        if ($eShowRideVehicles == "No") {
            $main_category_ids = array();
            $count_main_category = 0;
            for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                $data_temp = $rideDeliveryIconData[$i];
                if ($data_temp['eCatType'] == "MoreDelivery" && $data_temp['eStatus'] == "Active") {
                    $main_category_ids[$count_main_category] = $data_temp['iVehicleCategoryId'];
                    $count_main_category++;
                }
            }

            if (count($main_category_ids) > 0) {
                $sub_category_ids = array();
                $count_sub_category = 0;
                foreach ($main_category_ids as $k => $val) {
                    for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                        $data_temp = $rideDeliveryIconData[$i];
                        if ($data_temp['eStatus'] == "Active" && $data_temp['iParentId'] == $val) {
                            $sub_category_ids[$count_sub_category] = $data_temp['iVehicleCategoryId'];
                            $count_sub_category++;
                        }
                    }
                }
                if (count($sub_category_ids) > 0) {
                    $ssub_category_ids = $tempsubcat = array();
                    $count_ssub_category = 0;
                    foreach ($sub_category_ids as $k => $val) {
                        for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                            $data_temp = $rideDeliveryIconData[$i];
                            if ($data_temp['eStatus'] == "Active" && $data_temp['iParentId'] == $val) {
                                $ssub_category_ids[$count_ssub_category] = $data_temp['iVehicleCategoryId'];
                                $count_ssub_category++;
                            } else if ($data_temp['iParentId'] == $val) {
                                $tempsubcat[$count_ssub_category] = $data_temp['iVehicleCategoryId'];
                                $count_ssub_deliverycategory++;
                            }
                        }
                    }
                    if (count($tempsubcat) == 0 || count($ssub_category_ids) > 0) {
                        $eShowRideVehicles = "Yes";
                    }
                }
            }
        }

        // Delivery Enable Checking
        ########### Stage 1 ###########
        if ($eShowDeliveryVehicles == "No") {
            $main_category_ids = array();
            $count_main_category = 0;
            for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                $data_temp = $rideDeliveryIconData[$i];
                if ($data_temp['eCatType'] == "MoreDelivery" && $data_temp['eFor'] == "DeliveryCategory" && $data_temp['eStatus'] == "Active") {
                    $main_category_ids[$count_main_category] = $data_temp['iVehicleCategoryId'];
                    $count_main_category++;
                }
            }
            // 178
            ########## Stage 2 ############ Get Main Category #######
            if (count($main_category_ids) > 0) {
                $sub_deliverycategory_ids = array();
                $count_sub_category = 0;
                foreach ($main_category_ids as $k => $val) {
                    for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                        $data_temp = $rideDeliveryIconData[$i];
                        if ($data_temp['eStatus'] == "Active" && $data_temp['iParentId'] == $val) {
                            $sub_deliverycategory_ids[$count_sub_category] = $data_temp['iVehicleCategoryId'];
                            $count_sub_category++;
                        }
                    }
                }

                if (count($sub_deliverycategory_ids) > 0) {
                    $ssub_deliverycategory_ids = $tempsubcat = array();
                    $count_ssub_deliverycategory = 0;
                    foreach ($sub_deliverycategory_ids as $k => $val) {
                        for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                            $data_temp = $rideDeliveryIconData[$i];
                            if ($data_temp['eStatus'] == "Active" && $data_temp['iParentId'] == $val) {
                                $ssub_deliverycategory_ids[$count_ssub_deliverycategory] = $data_temp['iVehicleCategoryId'];
                                $count_ssub_deliverycategory++;
                            } else if ($data_temp['iParentId'] == $val) {
                                $tempsubcat[$count_ssub_deliverycategory] = $data_temp['iVehicleCategoryId'];
                                $count_ssub_deliverycategory++;
                            }
                        }
                    }

                    if (count($tempsubcat) == 0 || count($ssub_deliverycategory_ids) > 0) {
                        $eShowDeliveryVehicles = "Yes";
                    }
                }
            }
        }

        // Deliverall Enable Checking
        ########### Stage 1 ###########
        $main_Deliverallcategory_ids = array();
        if ($eShowDeliverAllVehicles == "No") {
            $count_mainDeliverall_category = 0;
            for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                $data_temp = $rideDeliveryIconData[$i];
                if ($data_temp['eCatType'] == "MoreDelivery" && $data_temp['eFor'] == "DeliverAllCategory" && $data_temp['eStatus'] == "Active") {
                    $main_Deliverallcategory_ids[$count_mainDeliverall_category] = $data_temp['iVehicleCategoryId'];
                    $count_mainDeliverall_category++;
                }
            }
        }
        // 185
        ########## Stage 2 ############ Get Main Category #######
        if (count($main_Deliverallcategory_ids) > 0) {
            $sub_deliverAllcategory_ids = array();
            $count_deliverallsub_category = 0;
            foreach ($main_Deliverallcategory_ids as $k => $val) {
                for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                    $data_temp = $rideDeliveryIconData[$i];
                    if ($data_temp['eStatus'] == "Active" && $data_temp['iParentId'] == $val) {
                        $sub_deliverAllcategory_ids[$count_deliverallsub_category] = $data_temp['iVehicleCategoryId'];
                        $count_deliverallsub_category++;
                    }
                }
            }

            if (count($sub_deliverAllcategory_ids) > 0) {
                $ssub_deliverallcategory_ids = $tempsubcat = array();
                $count_ssub_deliverallcategory = 0;
                foreach ($sub_deliverAllcategory_ids as $k => $val) {
                    for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
                        $data_temp = $rideDeliveryIconData[$i];
                        if ($data_temp['eStatus'] == "Active" && $data_temp['iParentId'] == $val) {
                            $ssub_deliverallcategory_ids[$count_ssub_deliverallcategory] = $data_temp['iVehicleCategoryId'];
                            $count_ssub_deliverallcategory++;
                        } elseif ($data_temp['iParentId'] == $val) {
                            $tempsubcat[$count_ssub_deliverallcategory] = $data_temp['iVehicleCategoryId'];
                            $count_ssub_deliverallcategory++;
                        }
                    }
                }

                if (count($tempsubcat) == 0 || count($ssub_deliverallcategory_ids) > 0) {
                    $eShowDeliverAllVehicles = "Yes";
                }
            }
        }

        for ($i = 0; $i < count($rideDeliveryIconData); $i++) {
            $data_temp = $rideDeliveryIconData[$i];
            if ($data_temp['eCatType'] == "MotoRide" && $data_temp['eStatus'] == "Active") {
                $eMotoRideEnable = "Yes";
            } else if ($data_temp['eCatType'] == "MotoDelivery" && $data_temp['eStatus'] == "Active") {
                $eMotoDeliveryEnable = "Yes";
            } else if ($data_temp['eCatType'] == "Rental" && $data_temp['eStatus'] == "Active") {
                $eRentalEnable = "Yes";
            } else if ($data_temp['eCatType'] == "MotoRental" && $data_temp['eStatus'] == "Active") {
                $eMotoRentalEnable = "Yes";
            }
        }
    } else if ($APP_TYPE == "Ride") {
        $eShowRideVehicles = "Yes";
        $eMotoRideEnable = "Yes";
        $eRentalEnable = "Yes";
        $eMotoRentalEnable = "Yes";
    }

    if ($eShowRideVehicles == "No" && $eShowDeliveryVehicles == "No") {
        $RideDeliveryBothFeatureDisable = "Yes";
    }
    if (ONLYDELIVERALL == "Yes") {
        $eShowDeliverAllVehicles = "Yes";
    }
    $returnArr['eShowRideVehicles'] = ONLYDELIVERALL == "Yes" ? 'No' : $eShowRideVehicles;
    $returnArr['eShowDeliveryVehicles'] = ONLYDELIVERALL == "Yes" ? 'No' : $eShowDeliveryVehicles;
    $returnArr['eShowDeliverAllVehicles'] = ONLYDELIVERALL == "Yes" ? 'Yes' : $eShowDeliverAllVehicles;
    $returnArr['RideDeliveryBothFeatureDisable'] = ONLYDELIVERALL == "Yes" ? 'Yes' : $RideDeliveryBothFeatureDisable;
    $returnArr['eMotoRideEnable'] = ONLYDELIVERALL == "Yes" ? 'No' : $eMotoRideEnable;
    $returnArr['eMotoDeliveryEnable'] = ONLYDELIVERALL == "Yes" ? 'No' : $eMotoDeliveryEnable;
    $returnArr['eRentalEnable'] = ONLYDELIVERALL == "Yes" ? 'No' : $eRentalEnable;
    $returnArr['eMotoRentalEnable'] = ONLYDELIVERALL == "Yes" ? 'No' : $eMotoRentalEnable;
    return $returnArr;
}

//Added By HJ On 10-01-2019 For Check Ride and Delivery Feature End
########################### Check Ride Delivery Feature Enable ##############################################
########################### GenerateCustomer App Payment Method Wise #############################################################

function GenerateCustomer($Data) {
    global $generalobj, $obj, $STRIPE_SECRET_KEY, $STRIPE_PUBLISH_KEY, $gateway, $BRAINTREE_TOKEN_KEY, $BRAINTREE_ENVIRONMENT, $BRAINTREE_MERCHANT_ID, $BRAINTREE_PUBLIC_KEY, $BRAINTREE_PRIVATE_KEY, $BRAINTREE_CHARGE_AMOUNT, $PAYMAYA_API_URL, $tconfig, $XENDIT_PUBLIC_KEY, $XENDIT_SECRET_KEY, $APP_PAYMENT_METHOD, $SYSTEM_PAYMENT_ENVIRONMENT; // Stripe,Braintree
    foreach ($Data as $key => $value) {
        //$value = urldecode(stripslashes($value));
        $$key = $value;
    }
    if ($UserType == "Passenger") {
        $tbl_name = "register_user";
        $vEmail = "vEmail";
        $iMemberId = "iUserId";
        $eUserType = "Rider";
        $eMemberType = "Passenger";
        $UserDetailPaymaya = get_value($tbl_name, 'vName,vLastName,vEmail,vPhone,vPhoneCode as phonecode,vPaymayaCustId,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
    } else {
        $tbl_name = "register_driver";
        $vEmail = "vEmail";
        $iMemberId = "iDriverId";
        $eUserType = "Driver";
        $eMemberType = "Driver";
        $UserDetailPaymaya = get_value($tbl_name, 'vName,vLastName,vEmail,vPhone,vCode as phonecode,vPaymayaCustId,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
    }
    if ($APP_PAYMENT_METHOD == "Stripe") {
        require_once ('assets/libraries/stripe/config.php');
        require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
        $UserDetail = get_value($tbl_name, 'vStripeCusId,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
        $vEmail = $UserDetail[0]['memberemail'];
        $vStripeCusId = $UserDetail[0]['vStripeCusId'];
        try {
            if ($vStripeCusId != "") {
                $customer = Stripe_Customer::retrieve($vStripeCusId);
                $sources = $customer->sources;
                $stripeData = $sources->data;
                if (count($stripeData) > 0 && $stripeData[0]['id'] != '') {
                    $customer->sources->retrieve($stripeData[0]['id'])->delete();
                }
                $card = $customer->sources->create(array("source" => $vStripeToken));
            } else {
                try {
                    $customer = Stripe_Customer::create(array("source" => $vStripeToken, "email" => $vEmail));
                    $vStripeCusId = $customer->id;
                } catch (Exception $e) {
                    $error3 = $e->getMessage();
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $error3;
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            if (strpos($errMsg, 'No such customer') !== false) {
                try {
                    $customer = Stripe_Customer::create(array("source" => $vStripeToken, "email" => $vEmail));
                } catch (Exception $e) {
                    $error3 = $e->getMessage();
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $error3;

                    setDataResponse($returnArr);
                }

                $vStripeCusId = $customer->id;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = $errMsg;

                setDataResponse($returnArr);
            }
        }

        $where = " $iMemberId = '$iUserId'";
        $updateData['vStripeToken'] = $vStripeToken;
        $updateData['vStripeCusId'] = $vStripeCusId;
        $updateData['vCreditCard'] = $CardNo;

        $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
        if ($eMemberType == "Passenger") {
            $profileData = getPassengerDetailInfo($iUserId, "", "");
        } else {
            $profileData = getDriverDetailInfo($iUserId);
        }

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $profileData;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else if ($APP_PAYMENT_METHOD == "Braintree") {
        require_once ('assets/libraries/braintree/lib/Braintree.php');
        $UserDetail = get_value($tbl_name, 'vBrainTreeCustId,vName,vLastName,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
        $vEmail = $UserDetail[0]['memberemail'];
        $vBrainTreeCustId = $UserDetail[0]['vBrainTreeCustId'];
        $vName = $UserDetail[0]['vName'];
        $vLastName = $UserDetail[0]['vLastName'];

        try {
            if ($vBrainTreeCustId != "") {
                ## Charge First Transaction Amount For existing customer##
                try {
                    $charge = $gateway->transaction()
                            ->sale(['amount' => $BRAINTREE_CHARGE_AMOUNT, 'paymentMethodNonce' => $paymentMethodNonce, 'customerId' => $vBrainTreeCustId, 'options' => ['storeInVaultOnSuccess' => true, 'submitForSettlement' => true]]);

                    $result = $charge->success;
                    if ($result == 1) {
                        $transaction_id = $charge->transaction->id;
                        $creditCardArr = $charge->transaction->creditCard;
                        $paypalArr = $charge->transaction->paypal;
                        $payerEmail = $paypalArr['payerEmail'];
                        if ($payerEmail != "") {
                            $vBrainTreeCustEmail = $payerEmail;
                            $vBrainTreeToken = $paypalArr['token'];
                            $CardNo = "";
                            $message1 = "LBL_SUCESS_ADD_PAYPAL_BRAINTREE_TXT";
                        } else {
                            $vBrainTreeCustEmail = "";
                            $vBrainTreeToken = $creditCardArr['token'];
                            $CardNo = "XXXXXXXXXXXX" . $creditCardArr['last4'];
                            $message1 = "LBL_SUCESS_ADD_BRAINTREE_TXT";
                        }
                        $WalletId = $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $BRAINTREE_CHARGE_AMOUNT, 'Credit', 0, 'Deposit', '#LBL_AMOUNT_CREDIT_BY_USER#', 'Unsettelled', Date('Y-m-d H:i:s'));

                        $braintree_arr['BRAINTREE_TOKEN_KEY'] = $BRAINTREE_TOKEN_KEY;
                        $braintree_arr['BRAINTREE_ENVIRONMENT'] = $BRAINTREE_ENVIRONMENT;
                        $braintree_arr['BRAINTREE_MERCHANT_ID'] = $BRAINTREE_MERCHANT_ID;
                        $braintree_arr['BRAINTREE_PUBLIC_KEY'] = $BRAINTREE_PUBLIC_KEY;
                        $braintree_arr['BRAINTREE_PRIVATE_KEY'] = $BRAINTREE_PRIVATE_KEY;
                        $braintree_arr['BRAINTREE_CHARGE_AMOUNT'] = $BRAINTREE_CHARGE_AMOUNT;
                        $tPaymentDetails = json_encode($braintree_arr, JSON_UNESCAPED_UNICODE);

                        $pay_data['tPaymentUserID'] = $transaction_id;
                        $pay_data['vPaymentUserStatus'] = "approved";
                        $pay_data['iUserWalletId'] = $WalletId;
                        $pay_data['iAmountUser'] = $BRAINTREE_CHARGE_AMOUNT;
                        $pay_data['tPaymentDetails'] = $tPaymentDetails;
                        $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
                        $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                        $pay_data['eEvent'] = "Wallet";
                        $pay_data['iUserId'] = $iUserId;
                        $pay_data['eUserType'] = $UserType;
                        $paymentid = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                    } else {
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
                        setDataResponse($returnArr);
                    }
                } catch (Exception $e) {
                    $error3 = $e->getMessage();
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $error3;
                    setDataResponse($returnArr);
                }
                ## Charge First Transaction Amount For existing customer##
            } else {
                try {
                    $customer = $gateway->customer()
                            ->create(['firstName' => $vName, 'lastName' => $vLastName, 'email' => $vEmail,]);
                    $vBrainTreeCustId = $customer
                            ->customer->id;
                    ## Charge First Transaction Amount ##
                    try {
                        $charge = $gateway->transaction()
                                ->sale(['amount' => $BRAINTREE_CHARGE_AMOUNT, 'paymentMethodNonce' => $paymentMethodNonce, 'customerId' => $vBrainTreeCustId, 'options' => ['storeInVaultOnSuccess' => true,]]);

                        $result = $charge->success;
                        if ($result == 1) {
                            $transaction_id = $charge
                                    ->transaction->id;
                            $creditCardArr = $charge
                                    ->transaction->creditCard;
                            $paypalArr = $charge
                                    ->transaction->paypal;
                            $payerEmail = $paypalArr['payerEmail'];
                            if ($payerEmail != "") {
                                $vBrainTreeCustEmail = $payerEmail;
                                $vBrainTreeToken = $paypalArr['token'];
                                $CardNo = "";
                                $message1 = "LBL_SUCESS_ADD_PAYPAL_BRAINTREE_TXT";
                            } else {
                                $vBrainTreeCustEmail = "";
                                $vBrainTreeToken = $creditCardArr['token'];
                                $CardNo = "XXXXXXXXXXXX" . $creditCardArr['last4'];
                                $message1 = "LBL_SUCESS_ADD_BRAINTREE_TXT";
                            }
                            $WalletId = $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $BRAINTREE_CHARGE_AMOUNT, 'Credit', 0, 'Deposit', '#LBL_AMOUNT_CREDIT_BY_USER#', 'Unsettelled', Date('Y-m-d H:i:s'));

                            $braintree_arr['BRAINTREE_TOKEN_KEY'] = $BRAINTREE_TOKEN_KEY;
                            $braintree_arr['BRAINTREE_ENVIRONMENT'] = $BRAINTREE_ENVIRONMENT;
                            $braintree_arr['BRAINTREE_MERCHANT_ID'] = $BRAINTREE_MERCHANT_ID;
                            $braintree_arr['BRAINTREE_PUBLIC_KEY'] = $BRAINTREE_PUBLIC_KEY;
                            $braintree_arr['BRAINTREE_PRIVATE_KEY'] = $BRAINTREE_PRIVATE_KEY;
                            $braintree_arr['BRAINTREE_CHARGE_AMOUNT'] = $BRAINTREE_CHARGE_AMOUNT;
                            $tPaymentDetails = json_encode($braintree_arr, JSON_UNESCAPED_UNICODE);

                            $pay_data['tPaymentUserID'] = $transaction_id;
                            $pay_data['vPaymentUserStatus'] = "approved";
                            $pay_data['iUserWalletId'] = $WalletId;
                            $pay_data['iAmountUser'] = $BRAINTREE_CHARGE_AMOUNT;
                            $pay_data['tPaymentDetails'] = $tPaymentDetails;
                            $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
                            $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
                            $pay_data['eEvent'] = "Wallet";
                            $pay_data['iUserId'] = $iUserId;
                            $pay_data['eUserType'] = $UserType;
                            $paymentid = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
                        } else {
                            $returnArr['Action'] = "0";
                            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
                            setDataResponse($returnArr);
                        }
                    } catch (Exception $e) {
                        $error3 = $e->getMessage();
                        $returnArr['Action'] = "0";
                        $returnArr['message'] = $error3;
                        setDataResponse($returnArr);
                    }
                    ## Charge First Transaction Amount ##
                } catch (Exception $e) {
                    $error3 = $e->getMessage();
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $error3;
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            $returnArr['Action'] = "0";
            $returnArr['message'] = $errMsg;

            setDataResponse($returnArr);
        }

        $where = " $iMemberId = '$iUserId'";
        $updateData['vBrainTreeToken'] = $vBrainTreeToken;
        $updateData['vBrainTreeCustEmail'] = $vBrainTreeCustEmail;
        $updateData['vBrainTreeCustId'] = $vBrainTreeCustId;
        $updateData['vCreditCard'] = $CardNo;

        $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
        if ($eMemberType == "Passenger") {
            $profileData = getPassengerDetailInfo($iUserId, "", "");
        } else {
            $profileData = getDriverDetailInfo($iUserId);
        }

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $profileData;
            $returnArr['message1'] = $message1;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else if ($APP_PAYMENT_METHOD == "Paymaya") {
        $vName = $UserDetailPaymaya[0]['vName'];
        $vLastName = $UserDetailPaymaya[0]['vLastName'];
        $vPhone = $UserDetailPaymaya[0]['vPhone'];
        $phonecode = $UserDetailPaymaya[0]['phonecode'];
        $phone = "+" . $phonecode . $vPhone;
        $vEmail = $UserDetailPaymaya[0]['memberemail'];
        $vPaymayaCustId = $UserDetailPaymaya[0]['vPaymayaCustId'];

        if ($vPaymayaCustId == "") {
            $POST_URL = $PAYMAYA_API_URL . "payments/v1/customers";
            $postdata = array(
                'firstName' => $vName,
                'lastName' => $vLastName,
                'contact' => array(
                    'phone' => $phone,
                    'email' => $vEmail
                )
            );

            $result = check_paymaya_api($POST_URL, $postdata);
            $vPaymayaCustId = $result['id'];
            if ($vPaymayaCustId != "") {
                ## Vault a Card ##
                $postdata_vault = array(
                    'paymentTokenId' => $vPaymayaToken,
                    'isDefault' => true,
                    'redirectUrl' => array(
                        'success' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/success.php',
                        'failure' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/failure.php',
                        'cancel' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/cancel.php'
                    )
                );
                $POST_URL_Vault = $PAYMAYA_API_URL . "payments/v1/customers/" . $vPaymayaCustId . "/cards";
                $result_vault = check_paymaya_api($POST_URL_Vault, $postdata_vault);
                $verificationUrl = $result_vault['verificationUrl'];
                if ($verificationUrl == "" || $verificationUrl == NULL) {
                    $error = $result_vault['message'];
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $error;
                    setDataResponse($returnArr);
                }
                ## Vault a Card ##
                $updateData['vPaymayaCustId'] = $vPaymayaCustId;
            } else {
                $error = $result['message'];
                $returnArr['Action'] = "0";
                $returnArr['message'] = $error;
                setDataResponse($returnArr);
            }
        } else {
            ## Vault a Card ##
            $postdata_vault = array(
                'paymentTokenId' => $vPaymayaToken,
                'isDefault' => true,
                'redirectUrl' => array(
                    'success' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/success.php',
                    'failure' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/failure.php',
                    'cancel' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/cancel.php'
                )
            );
            $POST_URL_Vault = $PAYMAYA_API_URL . "payments/v1/customers/" . $vPaymayaCustId . "/cards";
            $result_vault = check_paymaya_api($POST_URL_Vault, $postdata_vault);
            $verificationUrl = $result_vault['verificationUrl'];
            if ($verificationUrl == "" || $verificationUrl == NULL) {
                $error = $result_vault['message'];
                $returnArr['Action'] = "0";
                $returnArr['message'] = $error;
                setDataResponse($returnArr);
            }
            ## Vault a Card ##
        }

        $where = " $iMemberId = '$iUserId'";
        $updateData['vCreditCard'] = $CardNo;
        $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $verificationUrl;
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else if ($APP_PAYMENT_METHOD == "Omise") {
        require_once ('assets/libraries/omise/config.php');
        $UserDetail = get_value($tbl_name, 'vOmiseCustId,vOmiseToken,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
        $vEmail = $UserDetail[0]['memberemail'];
        $vOmiseCustId = $UserDetail[0]['vOmiseCustId'];
        $vOldOmiseToken = $UserDetail[0]['vOmiseToken'];

        try {
            if ($vOmiseCustId != "") {
                //$customer = OmiseCustomer::retrieve($vOmiseCustId);
                //$card = $customer->getCards()->retrieve($vOldOmiseToken);
                //$card->destroy();
                //$card->isDestroyed(); # => true
                /* $customer = OmiseCustomer::create(array(
                  'email' => $vEmail,
                  'description' => $eMemberType."_".$iUserId,
                  'card' => $vOmiseToken
                  )); */
                $customer = OmiseCustomer::retrieve($vOmiseCustId);
                $customer->update(array(
                    'card' => $vOmiseToken
                ));

                $customer1 = OmiseCustomer::retrieve($vOmiseCustId);
                $cards = $customer1->getCards();
                $cardArr = $cards['data'];
                $lastcardArr = end($cardArr);

                $vOmiseCardId = $lastcardArr['id'];
                $LastFour = "XXXXXXXXXXXX" . $lastcardArr['last_digits'];

                //$vOmiseCardId = $customer['default_card'];
                //$LastFour = "XXXXXXXXXXXX".$customer['cards']['data'][0]['last_digits'];
            } else {
                try {

                    $customer = OmiseCustomer::create(array(
                                'email' => $vEmail,
                                'description' => $eMemberType . "_" . $iUserId,
                                'card' => $vOmiseToken
                    ));

                    $vOmiseCustId = $customer['id'];
                    $vOmiseCardId = $customer['default_card'];
                    $LastFour = "XXXXXXXXXXXX" . $customer['cards']['data'][0]['last_digits'];
                } catch (Exception $e) {
                    $error3 = $e->getMessage();
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = $error3;
                    setDataResponse($returnArr);
                }
            }
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            $returnArr['Action'] = "0";
            $returnArr['message'] = $errMsg;
            setDataResponse($returnArr);
        }

        $where = " $iMemberId = '$iUserId'";
        $updateData['vOmiseToken'] = $vOmiseCardId;
        $updateData['vOmiseCustId'] = $vOmiseCustId;
        $updateData['vCreditCard'] = $LastFour;

        $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
        if ($eMemberType == "Passenger") {
            $profileData = getPassengerDetailInfo($iUserId, "", "");
        } else {
            $profileData = getDriverDetailInfo($iUserId);
        }

        if ($id > 0) {
            $returnArr['Action'] = "1";
            $returnArr['message'] = $profileData;
            $returnArr['message1'] = "LBL_SUCESS_ADD_BRAINTREE_TXT";
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        }
    } else if ($APP_PAYMENT_METHOD == "Adyen") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $tconfig['tsite_url'] . "/assets/libraries/adyen/clienttoken.php?iUserId=" . $iUserId . "&UserType=" . $eMemberType;
    } else if ($APP_PAYMENT_METHOD == "Xendit") {
        require_once ('assets/libraries/xendit/config.php');
        require_once ('assets/libraries/xendit/src/XenditPHPClient.php');
        $options['secret_api_key'] = $XENDIT_SECRET_KEY;
        $xenditPHPClient = new XenditClient\XenditPHPClient($options);
        $external_id = substr(number_format(time() * rand(), 0, '', ''), 0, 15);
        $token_id = $vXenditToken;
        $amount = 0;
        $response = $xenditPHPClient->captureCreditCardPayment($external_id, $token_id, $amount);
        $result = $response['status'];
        $CardNo = $response['masked_card_number'];
        if ($result == "AUTHORIZED") {
            $where = " $iMemberId = '$iUserId'";
            $updateData['vXenditToken'] = $vXenditToken;
            $updateData['vCreditCard'] = $CardNo;
            $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
            if ($eMemberType == "Passenger") {
                $profileData = getPassengerDetailInfo($iUserId, "", "");
            } else {
                $profileData = getDriverDetailInfo($iUserId);
            }
            if ($id > 0) {
                $returnArr['Action'] = "1";
                $returnArr['message'] = $profileData;
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            }
        } else {
            $error3 = $response['message'];
            $returnArr['Action'] = "0";
            $returnArr['message'] = $error3;
            setDataResponse($returnArr);
        }
    }


    return $returnArr;
}

########################### GenerateCustomer App Payment Method Wise #############################################################
########################### Paymaya Payment API  ##############################################################################

function check_paymaya_api($url, $postdata = array()) {
    global $generalobj, $obj, $PAYMAYA_SECRET_KEY, $PAYMAYA_PUBLISH_KEY;
    $result = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata)); //Post Fields
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $paymaya_auth = base64_encode($PAYMAYA_SECRET_KEY . ":");
    $headers = ['Authorization: Basic ' . $paymaya_auth, 'Content-Type: application/json',];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $request = curl_exec($ch); //echo "<pre>";print_r($request);exit;
    curl_close($ch);
    if ($request) {
        $result = json_decode($request, true);
    }
    return $result;
}

########################### Paymaya Payment API  ##############################################################################
####################################### Functions taken from food webservice ######################################################################

function GetUserAddressDetail($iUserId, $eUserType = "Passenger", $iUserAddressId) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }
    $sql = "SELECT * from user_address WHERE iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND iUserAddressId = '" . $iUserAddressId . "'";
    $result_Address = $obj->MySQLSelect($sql);
    $ToTalAddress = count($result_Address);
    if ($ToTalAddress > 0) {
        $vAddressType = $result_Address[0]['vAddressType'];
        $vBuildingNo = $result_Address[0]['vBuildingNo'];
        $vLandmark = $result_Address[0]['vLandmark'];
        $vServiceAddress = $result_Address[0]['vServiceAddress'];
        $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
        $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
        $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
        $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
        $result_Address[0]['UserAddress'] = $PickUpAddress;
        $returnArr = $result_Address[0];
    }
    return $returnArr;
}

####################################### Functions taken from food webservice ######################################################################
####################################### Functions taken from food webservice ######################################################################

function GetTotalUserAddress($iUserId, $eUserType = "Passenger", $passengerLat, $passengerLon, $iCompanyId = 0) {
    global $obj, $generalobj, $tconfig, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $ToTalAddress = 0;
    if ($iUserId == "" || $iUserId == 0 || $iUserId == NULL) {
        return $ToTalAddress;
    }
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }
    $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
    $db_userdata = $obj->MySQLSelect($sql);
    $db_userdata_new = array();
    $db_userdata_new = $db_userdata;
    if (count($db_userdata) > 0) {
        for ($i = 0; $i < count($db_userdata); $i++) {
            $isRemoveAddressFromList = "No";
            $passengeraddlat = $db_userdata[$i]['vLatitude'];
            $passengeraddlong = $db_userdata[$i]['vLongitude'];
            if ($iCompanyId == 0) {
                $distance = distanceByLocation($passengerLat, $passengerLon, $passengeraddlat, $passengeraddlong, "K");
                if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }
            ## Checking Distance Between Company and User Address ##
            if ($iCompanyId > 0) {
                $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
                $db_companydata = $obj->MySQLSelect($sql);
                $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
                $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
                $distancewithcompany = distanceByLocation($passengeraddlat, $passengeraddlong, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
                if ($distancewithcompany > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }
            ## Checking Distance Between Company and User Address ##
            if ($isRemoveAddressFromList == "Yes") {
                unset($db_userdata_new[$i]);
            }
        }
        $db_userdata = array_values($db_userdata_new);
        $ToTalAddress = count($db_userdata);
    }
    return $ToTalAddress;
}

####################################### Functions taken from food webservice ######################################################################

function GetUserSelectedAddress($iUserId, $eUserType = "Passenger") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }
    $sql = "SELECT * from user_address WHERE iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND eStatus = 'Active'";
    $result_Address = $obj->MySQLSelect($sql);
    $ToTalAddress = count($result_Address);
    if ($ToTalAddress > 0) {
        ## Checking First Last Orders Selected Address ##
        $sqlo = "SELECT ord.iUserAddressId,ua.eStatus,ua.vServiceAddress,ua.vBuildingNo,ua.vLandmark,ua.vAddressType,ua.vLatitude,ua.vLongitude from orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId WHERE ord.iUserId = '" . $iUserId . "' ORDER BY ord.iOrderId DESC limit 0,1";
        $last_order_Address = $obj->MySQLSelect($sqlo);
        $iUserAddressId = $last_order_Address[0]['iUserAddressId'];
        if (count($last_order_Address) > 0 && $iUserAddressId > 0) {
            $eStatus = $last_order_Address[0]['eStatus'];
            if ($eStatus == "Active") {
                $vAddressType = $last_order_Address[0]['vAddressType'];
                $vBuildingNo = $last_order_Address[0]['vBuildingNo'];
                $vLandmark = $last_order_Address[0]['vLandmark'];
                $vServiceAddress = $last_order_Address[0]['vServiceAddress'];
                $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
                $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
                $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
                $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
                $PickUpLatitude = $last_order_Address[0]['vLatitude'];
                $PickUpLongitude = $last_order_Address[0]['vLongitude'];
                $returnArr['UserSelectedAddress'] = $PickUpAddress;
                $returnArr['UserSelectedLatitude'] = $PickUpLatitude;
                $returnArr['UserSelectedLongitude'] = $PickUpLongitude;
                $returnArr['UserSelectedAddressId'] = $iUserAddressId;
            } else {
                $returnArr['UserSelectedAddress'] = "";
                $returnArr['UserSelectedLatitude'] = "";
                $returnArr['UserSelectedLongitude'] = "";
                $returnArr['UserSelectedAddressId'] = 0;
            }
        } else {
            $vAddressType = $result_Address[0]['vAddressType'];
            $vBuildingNo = $result_Address[0]['vBuildingNo'];
            $vLandmark = $result_Address[0]['vLandmark'];
            $vServiceAddress = $result_Address[0]['vServiceAddress'];
            $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
            $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
            $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
            $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
            $PickUpLatitude = $result_Address[0]['vLatitude'];
            $PickUpLongitude = $result_Address[0]['vLongitude'];
            $returnArr['UserSelectedAddress'] = $PickUpAddress;
            $returnArr['UserSelectedLatitude'] = $PickUpLatitude;
            $returnArr['UserSelectedLongitude'] = $PickUpLongitude;
            $returnArr['UserSelectedAddressId'] = $result_Address[0]['iUserAddressId'];
        }
        ## Checking First Last Orders Selected Address ##
    } else {
        $returnArr['UserSelectedAddress'] = "";
        $returnArr['UserSelectedLatitude'] = "";
        $returnArr['UserSelectedLongitude'] = "";
        $returnArr['UserSelectedAddressId'] = 0;
    }
    return $returnArr;
}

/* End added */

//Added By HJ On 31-01-2019 For Get Vehicle Type Fare Details End

function checkSharkPackage() {
    if (strtoupper(PACKAGE_TYPE) != "SHARK") {
        return false;
    }
    $shark_file_path = "include/include_webservice_sharkfeatures.php";
    if (file_exists($shark_file_path)) {
        include_once($shark_file_path);
        return true;
    }
    return false;
}

function getCurrentActiveTripsTotal($iMemberId) {
    global $obj;
    // $sql_trips_chk = "SELECT iTripId FROM trips as tr WHERE tr.iActive != 'Canceled' AND tr.iActive != 'Finished' AND tr.tUserWalletBalance != '' AND tr.vTripPaymentMode = 'Card' AND tr.iUserId = '" . $iMemberId . "'";
    $sql_trips_chk = "SELECT iTripId, iOrderId FROM trips as tr WHERE tr.iActive != 'Canceled' AND tr.iActive != 'Finished' AND tr.tUserWalletBalance != '' AND tr.tUserWalletBalance != '0' AND tr.iUserId = '" . $iMemberId . "'";
    $data_trips = $obj->MySQLSelect($sql_trips_chk);
    $totalCount = 0;
    if (strtoupper(DELIVERALL) == "YES") {
        $ssql_orderIds = "";
        if (!empty($data_trips) && count($data_trips) > 0) {
            for ($i = 0; $i < count($data_trips); $i++) {
                if (!empty($data_trips[$i]['iOrderId']) && $data_trips[$i]['iOrderId'] > 0) {
                    $ssql_orderIds = $ssql_orderIds == "" ? " AND NOT IN( " . $data_trips[$i]['iOrderId'] : $ssql_orderIds . ", " . $data_trips[$i]['iOrderId'];
                }
            }

            if (!empty($ssql_orderIds)) {
                $ssql_orderIds = $ssql_orderIds . ")";
            }
        }
        // $sql_orders_chk = "SELECT iOrderId FROM orders as ord WHERE ord.ePaid = 'No' ".$ssql_orderIds." AND ord.iStatusCode IN(1,2,4,5,12) AND ord.ePaymentOption = 'Card' AND ord.iUserId = '" . $iMemberId . "'";
        $sql_orders_chk = "SELECT iOrderId FROM orders as ord WHERE ord.ePaid = 'No' " . $ssql_orderIds . " AND ord.iStatusCode IN(1,2,4,5,12)  AND ord.tUserWalletBalance != '' AND ord.tUserWalletBalance != '0' AND ord.iUserId = '" . $iMemberId . "'";
        $data_orders = $obj->MySQLSelect($sql_orders_chk);
    }

    $tripIdsArr = array();
    $orderIdsArr = array();
    if (!empty($data_trips) && count($data_trips) > 0) {
        $totalCount = count($data_trips);

        foreach ($data_trips as $data_trips_tmp) {
            $tripIdsArr[] = $data_trips_tmp['iTripId'];
        }
    }

    if (!empty($data_orders) && count($data_orders) > 0) {
        $totalCount = $totalCount + count($data_orders);

        foreach ($data_orders as $data_orders_tmp) {
            $orderIdsArr[] = $data_orders_tmp['iOrderId'];
        }
    }

    $returnArr['TotalCount'] = $totalCount;
    $returnArr['ActiveTripIds'] = $tripIdsArr;
    $returnArr['ActiveOrderIds'] = $orderIdsArr;
    return $returnArr;
}

function getValidPromoCodes() {
    global $obj, $generalobj;
    $eType = isset($_REQUEST['eType']) ? clean($_REQUEST['eType']) : '';
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    if (empty($iMemberId)) {
        $iMemberId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    }
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Passenger';
    $eSystemType = isset($_REQUEST['eSystem']) ? clean($_REQUEST['eSystem']) : '';
    $promoCode = isset($_REQUEST['PromoCode']) ? clean($_REQUEST['PromoCode']) : '';
    if (empty($promoCode)) {
        $promoCode = isset($_REQUEST['vCouponCode']) ? clean($_REQUEST['vCouponCode']) : '';
    }
    $curr_date = @date("Y-m-d");
    $ssql = "";
    if ($eType == "DeliverAll" || !empty($eSystemType)) {
        // Display Only deliverAll category + General Category related promocodes
        $ssql = " AND (eSystemType = 'DeliverAll' OR eSystemType = 'General')";
    } else if (strtoupper($eType) == "DELIVER" || strtoupper($eType) == "DELIVERY") {
        // Display Only Delivery category + General Category Promocodes
        $ssql = " AND (eSystemType = 'Delivery' OR eSystemType = 'Deliver' OR eSystemType = 'General')";
    } else if (strtoupper($eType) == "RIDE") {
        // Display Only Ride category + General Category Promocodes
        $ssql = " AND (eSystemType = 'Ride' OR eSystemType = 'General')";
    } else if (strtoupper($eType) == "UBERX") {
        // Display Only UberX category + General Category Promocodes
        $ssql = " AND (eSystemType = 'UberX' OR eSystemType = 'General')";
    } else {
        // Display Only General category Promocodes
        $ssql = " AND eSystemType = 'General'";
    }
    if ($promoCode != "") {
        // This will be used to validate promocode. If blank then this function gives array of all valid promo codes
        $ssql .= " AND vCouponCode='" . $promoCode . "'";
    }
    if ($userType == "Passenger") {
        $UserDetail = get_value('register_user AS ru LEFT JOIN currency AS c ON c.vName=ru.vCurrencyPassenger', 'ru.vCurrencyPassenger as currencyCode, ru.vLang as vLang, c.Ratio as currencyRatio, c.vSymbol as currencySymbol', 'ru.iUserId', $iMemberId);
    } else {
        $UserDetail = get_value('register_driver AS rd LEFT JOIN currency AS c ON c.vName=rd.vCurrencyDriver', 'rd.vCurrencyDriver as currencyCode, rd.vLang as vLang, c.Ratio as currencyRatio, c.vSymbol as currencySymbol', 'rd.iDriverId', $iMemberId);
    }
    $couponData = $obj->MySQLSelect("SELECT iCouponId,vCouponCode,fDiscount, eType, eValidityType, dActiveDate, dExpiryDate, eSystemType, JSON_UNQUOTE(json_extract(tDescription, '$.tDescription_" . $UserDetail[0]['vLang'] . "')) AS tDescription from coupon WHERE eStatus = 'Active' AND iUsageLimit > iUsed " . $ssql . " ORDER BY iCouponId DESC");
    $validCoponsList = "";
    foreach ($couponData as $couponData_tmp) {
        if ($couponData_tmp['eValidityType'] == "Defined") {
            $dActiveDate = $couponData_tmp['dActiveDate'];
            $dExpiryDate = $couponData_tmp['dExpiryDate'];
            if (($dActiveDate <= $curr_date && $dExpiryDate >= $curr_date) == false) {
                continue;
            }
        }
        $validCoponsList = empty($validCoponsList) ? $couponData_tmp['vCouponCode'] : $validCoponsList . ',' . $couponData_tmp['vCouponCode'];
    }
    if (strtoupper(ONLYDELIVERALL) != "YES") {
        $validCoponsList_sql = "'" . implode("', '", explode(",", $validCoponsList)) . "'";
        $trips_data = $obj->MySQLSelect("select GROUP_CONCAT(`vCouponCode`) as UsedCoupons from `trips` where `vCouponCode` IN (" . $validCoponsList_sql . ") and `iUserId`='$iMemberId' and `iOrderId`='0'");
        if (!empty($trips_data) && count($trips_data) > 0 && !empty($trips_data[0]['UsedCoupons'])) {
            $validCoponsList = implode(",", array_diff(explode(",", $validCoponsList), explode(",", $trips_data[0]['UsedCoupons'])));
        }
        //Added By HJ On 04-09-2019 For Check Later Booking Promocode Used Or Not Start
        $validCoponsList_sql = "'" . implode("', '", explode(",", $validCoponsList)) . "'";
        $booking_data = $obj->MySQLSelect("select GROUP_CONCAT(`vCouponCode`) as UsedCoupons from `cab_booking` where `vCouponCode` IN (" . $validCoponsList_sql . ") and `iUserId`='$iMemberId' and `eStatus`!='Declined' and `eStatus`!='Cancel' and `eStatus`!='Completed'");
        if (!empty($booking_data) && count($booking_data) > 0 && !empty($booking_data[0]['UsedCoupons'])) {
            $validCoponsList = implode(",", array_diff(explode(",", $validCoponsList), explode(",", $booking_data[0]['UsedCoupons'])));
        }
        //Added By HJ On 04-09-2019 For Check Later Booking Promocode Used Or Not End
    }
    if (strtoupper(DELIVERALL) == "YES") {
        $validCoponsList_sql = "'" . implode("', '", explode(",", $validCoponsList)) . "'";
        $trips_data = $obj->MySQLSelect("select GROUP_CONCAT(`vCouponCode`) as UsedCoupons from `orders` where `vCouponCode` IN (" . $validCoponsList_sql . ") and `iUserId`='$iMemberId'");
        if (!empty($trips_data) && count($trips_data) > 0 && !empty($trips_data[0]['UsedCoupons'])) {
            $validCoponsList = implode(",", array_diff(explode(",", $validCoponsList), explode(",", $trips_data[0]['UsedCoupons'])));
        }
    }
    $finalCouponData = array();
    $validCoponsListArr = explode(",", $validCoponsList);
    $userRatio = $UserDetail[0]['currencyRatio'];
    foreach ($couponData as $couponData_tmp) {
        if (in_array($couponData_tmp['vCouponCode'], $validCoponsListArr)) {
            if ($couponData_tmp['eType'] != "percentage") {
                $couponData_tmp['fDiscount'] = $generalobj->setTwoDecimalPoint($couponData_tmp['fDiscount'] * $userRatio); //Added By HJ On 31-12-2018 Convert Default Currency Into User Currency
            }
            $couponData_tmp['vCurrency'] = $UserDetail[0]['currencyCode'];
            $couponData_tmp['vSymbol'] = $UserDetail[0]['currencySymbol'];
            $finalCouponData[] = $couponData_tmp;
        }
    }
    $returnData = array();
    $returnData['CouponList'] = $finalCouponData;
    $returnData['vCurrency'] = $UserDetail[0]['currencyCode'];
    $returnData['vSymbol'] = $UserDetail[0]['currencySymbol'];
    return $returnData;
}

function checkFavDriverModule() {
    global $ENABLE_FAVORITE_DRIVER_MODULE;

    $fav_driver_file_path = "include/features/include_fav_driver.php";
    if (file_exists($fav_driver_file_path) && strtoupper($ENABLE_FAVORITE_DRIVER_MODULE) == 'YES' && strtoupper(ONLYDELIVERALL) == "NO") {
        return true;
    }
    return false;
}

function checkFavStoreModule() {
    global $ENABLE_FAVORITE_STORE_MODULE;

    $fav_store_file_path = "include/features/include_fav_store.php";
    if (file_exists($fav_store_file_path) && strtoupper($ENABLE_FAVORITE_STORE_MODULE) == 'YES' && strtoupper(DELIVERALL) == "YES") {
        return true;
    }
    return false;
}

function checkDriverDestinationModule($adminfilepath = 0) {

    global $ENABLE_DRIVER_DESTINATIONS, $APP_TYPE;

    if (!empty($adminfilepath)) {
        $driver_destination_file_path = "../include/features/include_destinations_driver.php";
    } else {
        $driver_destination_file_path = "include/features/include_destinations_driver.php";
    }

    if (file_exists($driver_destination_file_path) && strtoupper($ENABLE_DRIVER_DESTINATIONS) == 'YES' && (($APP_TYPE == "Ride-Delivery") || ($APP_TYPE == "Ride-Delivery-UberX") || ($APP_TYPE == "Ride"))) {
        return true;
    }
    return false;
}

function checkStopOverPointModule() {
    global $ENABLE_STOPOVER_POINT, $APP_TYPE;
    $stop_over_point_file_path = "include/features/include_stop_over_point.php";

    if (file_exists($stop_over_point_file_path) && strtoupper($ENABLE_STOPOVER_POINT) == 'YES' && (($APP_TYPE == "Ride-Delivery") || ($APP_TYPE == "Ride-Delivery-UberX") || ($APP_TYPE == "Ride"))) {
        return true;
    }
    return false;
}

function checkDonationModule() {
    global $obj, $APP_TYPE, $DONATION_ENABLE, $generalobj;

    $DonationFilepath = "include/features/include_donation.php";

//    if (!empty($generalConfigPaymentArr['DRIVER_SUBSCRIPTION_ENABLE'])) {
//        $DRIVER_SUBSCRIPTION_ENABLE = $generalConfigPaymentArr['DRIVER_SUBSCRIPTION_ENABLE'];
//    } 
    if (empty($DONATION_ENABLE)) {
        $DONATION_ENABLE = $generalobj->getConfigurations("configurations", "DONATION_ENABLE");
        $DONATION_ENABLE = $DRIVER_SUBSCRIPTION_ENABLE[0]['vValue'];
    }

    if (file_exists($DonationFilepath) && strtoupper($DONATION_ENABLE) == 'YES') {
        return true;
    }
    return false;
}

function checkGojekGopayModule() {
    global $obj, $APP_TYPE, $PACKAGE_TYPE, $generalConfigPaymentArr;

    $gojek_gopay_filepath = "include/features/include_gojek_gopay.php";

    if (!empty($generalConfigPaymentArr['ENABLE_GOPAY'])) {
        $EnableGopay = $generalConfigPaymentArr['ENABLE_GOPAY'];
    } else {
        $EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY', '', true);
    }

    //if (file_exists($gojek_gopay_filepath) && strtoupper($EnableGopay) == 'YES' && ($PACKAGE_TYPE == "SHARK")) {
    if (file_exists($gojek_gopay_filepath) && strtoupper($EnableGopay) == 'YES') {
        return true;
    }
    return false;
}

/* For Gojek-gopay added by SP end */

/* For DriverSubscription added by SP start */

function checkDriverSubscriptionModule() {
    global $obj, $APP_TYPE, $PACKAGE_TYPE, $generalobj, $generalConfigPaymentArr, $DRIVER_SUBSCRIPTION_ENABLE;

    $DriverSubscriptionFilepath = "include/features/include_driver_subscription.php";

//    if (!empty($generalConfigPaymentArr['DRIVER_SUBSCRIPTION_ENABLE'])) {
//        $DRIVER_SUBSCRIPTION_ENABLE = $generalConfigPaymentArr['DRIVER_SUBSCRIPTION_ENABLE'];
//    } 
    if (empty($DRIVER_SUBSCRIPTION_ENABLE)) {
        $DRIVER_SUBSCRIPTION_ENABLE = $generalobj->getConfigurations("configurations", "DRIVER_SUBSCRIPTION_ENABLE");
        $DRIVER_SUBSCRIPTION_ENABLE = $DRIVER_SUBSCRIPTION_ENABLE[0]['vValue'];
    }

    if (file_exists($DriverSubscriptionFilepath) && strtoupper($DRIVER_SUBSCRIPTION_ENABLE) == 'YES' && ONLYDELIVERALL != "Yes") {
        return true;
    }
    return false;
}

function getGooglelocatiotionTrackingURL($iTripId, $iDriverId) {
    $trackingURL = '';
    if (isset($iTripId) && !empty($iTripId)) {
        $tripsLocationsData = get_value('trips_locations', 'tPlatitudes,tPlongitudes', 'iTripId', $iTripId);
        $lasttPlatitudes = '';
        $lasttPlongitudes = '';
        if (isset($tripsLocationsData) && !empty($tripsLocationsData)) {
            $tPlatitudes = $tripsLocationsData[0]['tPlatitudes'];
            $tPlongitudes = $tripsLocationsData[0]['tPlongitudes'];
            $tPlatitudesArr = explode(",", $tPlatitudes);
            $tPlongitudesArr = explode(",", $tPlongitudes);
            $lasttPlatitudes = $tPlatitudesArr[(count($tPlatitudesArr) - 1)];
            $lasttPlongitudes = $tPlongitudesArr[(count($tPlongitudesArr) - 1)];
        } else {
            $registerDriverData = get_value('register_driver', 'vLatitude,vLongitude', 'iDriverId', $iDriverId);
            $lasttPlatitudes = $registerDriverData[0]['vLatitude'];
            $lasttPlongitudes = $registerDriverData[0]['vLongitude'];
        }
        if (isset($lasttPlongitudes) && !empty($lasttPlongitudes)) {
            $formatted_address = getLocationNameLatLog($lasttPlatitudes, $lasttPlongitudes);
            if (!empty($formatted_address)) {
                $geoUrl = "http://maps.google.com/maps?q=" . urlencode($formatted_address);
            } else {
                $geoUrl = "http://maps.google.com/maps?q=loc:" . $lasttPlatitudes . "," . $lasttPlongitudes;
            }
            //exit;
            $trackingURL = get_tiny_url($geoUrl);
        }
    }
    return $trackingURL;
}

function getLocationNameLatLog($latitudes, $longitudes) {
    global $GOOGLE_SEVER_API_KEY_WEB;
    $formatted_address = '';
    if (!empty($latitudes) && !empty($longitudes)) {
        $url = 'latlng=' . $latitudes . ',' . $longitudes . '&key=' . $GOOGLE_SEVER_API_KEY_WEB;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/geocode/json?' . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($data);
        if (!empty($data)) {

            if ($data->status == "OK") {
                if (!empty($data->results)) {
                    $result = $data->results;
                    $formatted_address = $result[0]->formatted_address;
                }
            }
        }
        return $formatted_address;
    }
}

/* For DriverSubscription added by SP end */

function getcuisinelist($CompanyId, $iUserId, $languageLabelsArr = array(), $serviceId = 0) {
    global $obj, $generalobj, $tconfig, $vTimeZone, $iServiceId;
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    date_default_timezone_set($vTimeZone);
    $vCurrentTime = @date("Y-m-d H:i:s");
    $day = date('l', strtotime($vCurrentTime));
    $timingArray = array('vMonFromSlot', 'vMonToSlot', 'vTueFromSlot', 'vTueToSlot', 'vWedFromSlot', 'vWedToSlot', 'vThuFromSlot', 'vThuToSlot', 'vFriFromSlot', 'vFriToSlot', 'vSatFromSlot', 'vSatToSlot', 'vSunFromSlot', 'vSunToSlot');
    $orgtimingArray = array('vMonFromSlot1', 'vMonToSlot1', 'vTueFromSlot1', 'vTueToSlot1', 'vWedFromSlot1', 'vWedToSlot1', 'vThuFromSlot1', 'vThuToSlot1', 'vFriFromSlot1', 'vFriToSlot1', 'vSatFromSlot1', 'vSatToSlot1', 'vSunFromSlot1', 'vSunToSlot1', 'vMonFromSlot2', 'vMonToSlot2', 'vTueFromSlot2', 'vTueToSlot2', 'vWedFromSlot2', 'vWedToSlot2', 'vThuFromSlot2', 'vThuToSlot2', 'vFriFromSlot2', 'vFriToSlot2', 'vSatFromSlot2', 'vSatToSlot2', 'vSunFromSlot2', 'vSunToSlot2');
    $sltAry = array(1, 2);

    if ($day == "Sunday" || $day == "Saturday") {
        $vFromTimeSlot1 = "vFromSatSunTimeSlot1";
        $vFromTimeSlot2 = "vFromSatSunTimeSlot2";
        $vToTimeSlot1 = "vToSatSunTimeSlot1";
        $vToTimeSlot2 = "vToSatSunTimeSlot2";
    } else {
        $vFromTimeSlot1 = "vFromMonFriTimeSlot1";
        $vFromTimeSlot2 = "vFromMonFriTimeSlot2";
        $vToTimeSlot1 = "vToMonFriTimeSlot1";
        $vToTimeSlot2 = "vToMonFriTimeSlot2";
    }

    $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $userCurrencyRatio = 1;
    $vLanguage = "EN";
    $currencySymbol = "$";
    //echo "<pre>";print_r($iUserId);die;
    if (count($passengerData) > 0) {
        $vLanguage = $passengerData[0]['vLang'];
        $userCurrencyRatio = $passengerData[0]['Ratio'];
        $currencySymbol = $passengerData[0]['vSymbol'];
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        if (count($currencyData) > 0) {
            $userCurrencyRatio = $currencyData[0]['Ratio'];
            $currencySymbol = $currencyData[0]['vSymbol'];
        }
    }
    if (count($languageLabelsArr) == 0) {
        //$iServiceId = 1;
        $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    }
    $cuisine_all = $companyCuisineArr = $companyLatLangArr = $offerMsgArr = $restaurantStatusArr = $companyCuisineIdArr = $pricePerPersonArr = $storeMinOrdValueArr = array();
    $db_cuisine_str = "";

    //if ($iUserId != "" && count($CompanyId) > 0) { // Commented By HJ On 13-08-2019 As Per Discuss DT 
    if (count($CompanyId) > 0) {
        $storeIds = implode(",", $CompanyId);
        $whereServiceId = "";
        if ($serviceId > 0) {
            $whereServiceId = " AND cu.iServiceId='" . $serviceId . "'";
        }
        $sql = "SELECT cu.cuisineName_" . $vLanguage . " as cuisineName,cu.cuisineId,cmp.vRestuarantLocationLat as restaurantlat,cmp.vRestuarantLocationLong as restaurantlong,cmp.* FROM cuisine as cu INNER JOIN company_cuisine as ccu ON ccu.cuisineId=cu.cuisineId INNER JOIN company cmp ON ccu.iCompanyId=cmp.iCompanyId WHERE ccu.iCompanyId IN ($storeIds) $whereServiceId AND cu.eStatus = 'Active'";
        $db_cuisine = $obj->MySQLSelect($sql);
        $getStoreLantLangData = $obj->MySQLSelect("SELECT iCompanyId,vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPricePerPerson,fMinOrderValue FROM company WHERE iCompanyId IN($storeIds)");
        for ($re = 0; $re < count($getStoreLantLangData); $re++) {
            $companyLatLangArr[$getStoreLantLangData[$re]['iCompanyId']]['restaurantlat'] = $getStoreLantLangData[$re]['restaurantlat'];
            $companyLatLangArr[$getStoreLantLangData[$re]['iCompanyId']]['restaurantlong'] = $getStoreLantLangData[$re]['restaurantlong'];
            $pricePerPersonArr[$getStoreLantLangData[$re]['iCompanyId']] = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$re]['fPricePerPerson'] * $userCurrencyRatio);
            $storeMinOrdValueArr[$getStoreLantLangData[$re]['iCompanyId']] = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$re]['fMinOrderValue'] * $userCurrencyRatio);
        }
        //echo "<pre>";print_r($companyLatLangArr);die;
        if (count($db_cuisine) > 0) {
            for ($i = 0; $i < count($db_cuisine); $i++) {
                $db_cuisine_str = $db_cuisine[$i]['cuisineName'];
                $companyCuisineArr[$db_cuisine[$i]['iCompanyId']][] = $db_cuisine[$i]['cuisineName'];
                $companyCuisineIdArr[$db_cuisine[$i]['iCompanyId']][] = $db_cuisine[$i]['cuisineId'];

                array_push($cuisine_all, $db_cuisine_str);
                //START CODE FOR GET STORE OFFER MESSAGE BY HJ ON 01-04-2019
                $fOfferType = $db_cuisine[$i]['fOfferType'];
                $fOfferAppyType = $db_cuisine[$i]['fOfferAppyType'];
                $fOfferAmt = $generalobj->setTwoDecimalPoint($db_cuisine[$i]['fOfferAmt']);
                $fTargetAmt = $generalobj->setTwoDecimalPoint($db_cuisine[$i]['fTargetAmt']);
                $fTargetAmt = $generalobj->setTwoDecimalPoint($fTargetAmt * $userCurrencyRatio);
                $fMaxOfferAmt = $generalobj->setTwoDecimalPoint($db_cuisine[$i]['fMaxOfferAmt']);
                $fMaxOfferAmt = $generalobj->setTwoDecimalPoint($fMaxOfferAmt * $userCurrencyRatio);
                $MaxDiscountAmount = $ALL_ORDER_TXT = $offermsg = $offermsg_short = "";
                if ($fMaxOfferAmt > 0) {
                    $MaxDiscountAmount = " ( " . $languageLabelsArr['LBL_MAX_DISCOUNT_TXT'] . " " . $currencySymbol . "" . $fMaxOfferAmt . " )";
                }
                if ($fTargetAmt > 0) {
                    $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'] . " " . $languageLabelsArr['LBL_ORDERS_ABOVE_TXT'] . " " . $currencySymbol . "" . $fTargetAmt . " ";
                } else {
                    $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'];
                    $ALL_ORDER_TXT = $languageLabelsArr['LBL_ALL_ORDER_TXT'];
                }
                if ($fOfferType == "Percentage") {
                    if ($fOfferAppyType == "First") {
                        $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . $languageLabelsArr['LBL_FIRST_ORDER_TXT'] . "" . $MaxDiscountAmount;
                        $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
                    } elseif ($fOfferAppyType == "All") {
                        $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT . " " . $MaxDiscountAmount;

                        // $offermsg =  $languageLabelsArr['LBL_GET_TXT']." ".$fOfferAmt."% ".$TargerAmountTXT." ".$MaxDiscountAmount;
                        $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
                    }
                } else {
                    $fOfferAmt = $generalobj->setTwoDecimalPoint($fOfferAmt * $userCurrencyRatio);
                    $DiscountAmount = $currencySymbol . "" . $fOfferAmt;
                    if ($fOfferAppyType == "First" && $fOfferAmt > 0) {
                        $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
                        $offermsg_short = $offermsg;
                    } elseif ($fOfferAppyType == "All" && $fOfferAmt > 0) {
                        $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;

                        // $offermsg =  $languageLabelsArr['LBL_GET_TXT']." ".$DiscountAmount." ".$TargerAmountTXT;
                        $offermsg_short = $offermsg;
                    }
                }
                $offerMsgArr[$db_cuisine[$i]['iCompanyId']]['Restaurant_OfferMessage'] = $offermsg;
                $offerMsgArr[$db_cuisine[$i]['iCompanyId']]['Restaurant_OfferMessage_short'] = $offermsg_short;
                //END CODE FOR GET STORE OFFER MESSAGE BY HJ ON 01-04-2019
                //START CODE FOR GET RESTAURANT STATUS BY HJ ON 01-04-2019
                if (isset($db_cuisine[$i][$vFromTimeSlot1]) && $db_cuisine[$i][$vFromTimeSlot1] == "00:00:00" && $db_cuisine[$i][$vToTimeSlot1] == "00:00:00" && $db_cuisine[$i][$vFromTimeSlot2] == "00:00:00" && $db_cuisine[$i][$vToTimeSlot2] == "00:00:00") {
                    $restaurantStatusArr[$db_cuisine[$i]['iCompanyId']]['status'] = "Closed";
                } else {
                    if ($db_cuisine[$i][$vToTimeSlot1] < $db_cuisine[$i][$vFromTimeSlot1]) {
                        $endTime = strtotime($db_cuisine[$i][$vToTimeSlot1]);
                        $vFromTimeSlot_1 = date(("H:i"), strtotime($db_cuisine[$i][$vFromTimeSlot1]));
                        $vToTimeSlot_1 = date(("H:i"), strtotime('+1 day', $endTime));
                    } else {
                        $vFromTimeSlot_1 = date(("H:i"), strtotime($db_cuisine[$i][$vFromTimeSlot1]));
                        $vToTimeSlot_1 = date(("H:i"), strtotime($db_cuisine[$i][$vToTimeSlot1]));
                    }

                    if ($db_cuisine[$i][$vToTimeSlot2] < $db_cuisine[$i][$vFromTimeSlot2]) {
                        $endTime2 = strtotime($db_cuisine[$i][$vToTimeSlot2]);
                        $vFromTimeSlot_2 = date(("H:i"), strtotime($db_cuisine[$i][$vFromTimeSlot2]));
                        $vToTimeSlot_2 = date(("H:i"), strtotime('+1 day', $endTime2));
                    } else {
                        $vFromTimeSlot_2 = date(("H:i"), strtotime($db_cuisine[$i][$vFromTimeSlot2]));
                        $vToTimeSlot_2 = date(("H:i"), strtotime($db_cuisine[$i][$vToTimeSlot2]));
                    }
                    //$date = @date("H:i");
                    $date = @date("H:i", strtotime($vCurrentTime));
                    // $currenttime = strtotime($date);
                    $status = "closed";
                    $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
                    $opentime = $closetime = "";
                    $OpenAt = $languageLabelsArr['LBL_RESTAURANT_OPEN_TXT'];
                    $timeslotavailable = "No";
                    //echo isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date);exit;
                    if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1 || isBetween($vFromTimeSlot_2, $vToTimeSlot_2, $date) == 1) {
                        $status = "open";
                        $timeslotavailable = "Yes";
                        $status_display = $languageLabelsArr['LBL_RESTAURANT_OPEN_STAUS_TXT'];
                        $currentdate = @date("Y-m-d H:i:s");
                        $enddate = @date("Y-m-d");
                        if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1) {
                            $enddate = $enddate . " " . $vToTimeSlot_1 . ":00";
                        } else {
                            $enddate = $enddate . " " . $vToTimeSlot_2 . ":00";
                        }

                        $datediff = strtotime($enddate) - strtotime($currentdate);
                        if ($datediff < 900) {
                            $closein = $languageLabelsArr['LBL_RESTAURANT_CLOSE_MINS_TXT'];
                            $closemins = round($datediff / 60);
                            $closetime = $closein . " " . $closemins . " " . $languageLabelsArr['LBL_MINS_SMALL'];
                        }
                    } else {
                        $newdate = @date("Y-m-d");
                        // $newdate = $newdate." ".$vFromTimeSlot_2.":00";
                        if (isBetween($vFromTimeSlot_1, $vFromTimeSlot_1, $date) == 1) {
                            $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                        } else {
                            if ($vFromTimeSlot_1 < $vFromTimeSlot_2 && $vFromTimeSlot_1 > $date) {
                                $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                            } else {
                                $newdate = ($vFromTimeSlot_2 == "00:00") ? $newdate . " " . $vFromTimeSlot_1 . ":00" : $newdate . " " . $vFromTimeSlot_2 . ":00";
                            }
                        }
                        $currentdate = @date("Y-m-d H:i:s");
                        $datediff = strtotime($newdate) - strtotime($currentdate);
                        if ($datediff > 0) {
                            $opentime = $OpenAt . " " . date("h:i a", strtotime($newdate));
                        }
                    }
                    $eAvailable = $db_cuisine[$i]['eAvailable'];
                    $eLogout = $db_cuisine[$i]['eLogout'];
                    $eStatus = $db_cuisine[$i]['eStatus'];
                    if ($eAvailable == "No" || $eLogout == "Yes" || $eStatus != "Active") {
                        $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
                        $closetime = "";
                        $status = "closed";
                    }
                    $restaurantStatusArr[$db_cuisine[$i]['iCompanyId']]['opentime'] = $opentime;
                    $restaurantStatusArr[$db_cuisine[$i]['iCompanyId']]['closetime'] = $closetime;
                    $restaurantStatusArr[$db_cuisine[$i]['iCompanyId']]['timeslotavailable'] = $timeslotavailable;
                    $restaurantStatusArr[$db_cuisine[$i]['iCompanyId']]['status'] = $status;
                }
                //END CODE FOR GET RESTAURANT STATUS BY HJ ON 01-04-2019
            }
        }
    }
    //echo "<pre>";print_r($companyCuisineIdArr);die;
    $cuisine_all = array_unique($cuisine_all);
    $count = count($cuisine_all);
    $returnArr['cuisinecount'] = $count;
    $returnArr['cuisineArr'] = $cuisine_all;
    $returnArr['companyCuisineArr'] = $companyCuisineArr;
    $returnArr['companyCuisineIdArr'] = $companyCuisineIdArr;
    $returnArr['latLangArr'] = $companyLatLangArr;
    $returnArr['offerMsgArr'] = $offerMsgArr;
    $returnArr['restaurantStatusArr'] = $restaurantStatusArr;
    $returnArr['restaurantPricePerPerson'] = $pricePerPersonArr;
    $returnArr['restaurantMinOrdValue'] = $storeMinOrdValueArr;
    $returnArr['currencySymbol'] = $currencySymbol;
    return $returnArr;
}

function getUserOutstandingAmount($iUserId, $tableFieldName, $tripId = 0) {
    global $obj, $data_trips;
    $whereCondi = "AND eAuthoriseIdName='No' AND iAuthoriseId=0";
    if ($tripId > 0) {
        $whereCondi = "AND eAuthoriseIdName='" . $tableFieldName . "' AND iAuthoriseId='" . $tripId . "'";
    }
    $iOrganizationId = isset($_REQUEST["iOrganizationId"]) ? $_REQUEST["iOrganizationId"] : $data_trips[0]['iOrganizationId'];
    $sql = "SELECT iTripOutstandId,fPendingAmount FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND ePaidByPassenger = 'No' AND ePaymentBy = 'Passenger' AND fPendingAmount >0 $whereCondi";
    if ($iOrganizationId > 0) {
        $sql .= " AND iOrganizationId ='" . $iOrganizationId . "'";
    } else {
        $sql .= " AND iOrganizationId ='0'";
    }
    $getOutStandingAmt = $obj->MySQLSelect($sql);
    $ids = "";
    $outStandingAmt = 0;
    for ($r = 0; $r < count($getOutStandingAmt); $r++) {
        $ids .= ",'" . $getOutStandingAmt[$r]['iTripOutstandId'] . "'";
        $outStandingAmt += $getOutStandingAmt[$r]['fPendingAmount'];
    }
    $returnArr = array();
    if ($ids != "") {
        $ids = trim($ids, ",");
    }
    $returnArr['iTripOutstandId'] = $ids;
    $returnArr['fPendingAmount'] = $outStandingAmt;
    //echo "<pre>";print_r($returnArr);die;
    return $returnArr;
}

//Added By HJ On 31-07-2019 For Insert Default Corporate User When Add New User/Rider In Demo Copy Start
function insertCorporateUserProfile($iUserId, $email) {
    global $obj;
    $insert_user = array();
    $insert_user['iUserId'] = $iUserId;
    $insert_user['iUserProfileMasterId'] = 1;
    $insert_user['iOrganizationId'] = 1;
    $insert_user['vProfileEmail'] = $email;
    $insert_user['eStatus'] = "Active";
    $id = $obj->MySQLQueryPerform("user_profile", $insert_user, 'insert');
    return $id;
}

//Added By HJ On 31-07-2019 For Insert Default Corporate User When Add New User/Rider In Demo Copy End
//Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir Start
function calculateCouponCodeValue($getCouponCode, $fareAmount, $priceRatio) {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    global $obj;
    $discountValue = $discountValue_orig = 0;
    $discountValueType = "cash";
    if ($getCouponCode != "") {
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data Start
        $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $getCouponCode . "' AND eStatus='Active'");
        if (count($getCouponCode) > 0) {
            $discountValue = $getCouponCode[0]['fDiscount'];
            $discountValueType = $getCouponCode[0]['eType'];
            $discountValue_orig = $discountValue;
        }
        if ($discountValueType == "percentage") {
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($fareAmount * $discountValue), 1) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            $discountValue = round(($discountValue * $priceRatio), 2);
            if ($discountValue > $fareAmount) {
                $discountValue = $fareAmount;
                $vDiscount = round($fareAmount, 1) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
            }
        }
    }
    return $discountValue;
}

//Added By HJ On 07-08-2019 For Calculate Promocode Discount For UbeX App Type As Per Discuss with KS Sir End
//Added By HJ On 06-08-2019 For Get Selected Custome Notification Sound File Name Start
function getCustomeNotificationSound($DataArr) {
    global $obj, $APP_TYPE;
    $soundSql = " AND eSoundFor != 'Store'";
    if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Foodonly" || $APP_TYPE == "Deliverall" || DELIVERALL == "Yes" || ONLYDELIVERALL == "Yes") {
        $soundSql = "";
    }
    $DataArr['USER_NOTIFICATION'] = $DataArr['PROVIDER_NOTIFICATION'] = $DataArr['DIAL_NOTIFICATION'] = $DataArr['STORE_NOTIFICATION'] = $DataArr['VOIP_NOTIFICATION'] = "";
    $notificationData = $obj->MySQLSelect("SELECT * FROM notification_sound WHERE eStatus='Active' AND eIsSelected ='Yes' AND eAdminDisplay='Yes' $soundSql");
    for ($s = 0; $s < count($notificationData); $s++) {
        $eSoundFor = $notificationData[$s]['eSoundFor'];
        $vFileName = $notificationData[$s]['vFileName'];
        $eDefault = $notificationData[$s]['eDefault'];
        if ($eDefault == "Yes") {
            $vFileName = "";
        }
        if ($eSoundFor == "User") {
            $DataArr['USER_NOTIFICATION'] = $vFileName;
        } else if ($eSoundFor == "Store") {
            $DataArr['STORE_NOTIFICATION'] = $vFileName;
        } else if ($eSoundFor == "Provider") {
            $DataArr['PROVIDER_NOTIFICATION'] = $vFileName;
        } else if ($eSoundFor == "Dial") {
            $DataArr['DIAL_NOTIFICATION'] = $vFileName;
        } else if ($eSoundFor == "Voip") {
            $DataArr['VOIP_NOTIFICATION'] = $vFileName;
        }
    }
    //echo "<pre>";print_r($DataArr);die;
    return $DataArr;
}

//Added By HJ On 06-08-2019 For Get Selected Custome Notification Sound File Name End
?>