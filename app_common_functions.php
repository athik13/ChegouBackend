<?php

include_once("assets/libraries/modules_availibility.php");

/* start to clean function */

function setDataResponse($responseArr) {
    global $dataHelperObj, $websocket, $obj, $IS_INHOUSE_DOMAINS;
    if (!empty($websocket)) {
        $websocket->close();
    }

    $responseArr['TSITE_DB'] = TSITE_DB;
    $responseArr['GOOGLE_API_REPLACEMENT_URL'] = GOOGLE_API_REPLACEMENT_URL;
    /* Create a log of request/Response of all api */
    if (!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == "192.168.1.131" && !empty($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], 'cubejekdev') !== false) == true && isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
        /*
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
         */
    }
    /* Create a log of request/Response of all api */
$request_data_param = http_build_query($_REQUEST, '', '&');
        $sql_request_data = "REPLACE INTO request_data_debug (`tTitle`, `tType`, `tDescription`, `tPurpose`, `tCallToAction`, `tRequestParam`, `tResponse`, `tErrorResponse`) VALUES('', '" . $_REQUEST['type'] . "', '','','','" . $request_data_param . "','','')";
		//$obj->sql_query($sql_request_data);
   /* if (!empty(SITE_TYPE) && strtoupper(SITE_TYPE) == "LIVE" && $IS_INHOUSE_DOMAINS == true) {
		$request_data_param = http_build_query($_REQUEST, '', '&');
        $sql_request_data = "REPLACE INTO request_data_debug (`tTitle`, `tType`, `tDescription`, `tPurpose`, `tCallToAction`, `tRequestParam`, `tResponse`, `tErrorResponse`) VALUES('', '" . $_REQUEST['type'] . "', '','','','" . $request_data_param . "','','')";
		$obj->sql_query($sql_request_data);
    }
	*/
        //

    /* if (!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == "192.168.1.131" && !empty($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], 'cubejekdev_development') !== false) == true && isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
      $request_data_param = http_build_query($_REQUEST, '', '&');
      $sql_request_data = "REPLACE INTO request_data_debug (`tTitle`, `tType`, `tDescription`, `tPurpose`, `tCallToAction`, `tRequestParam`, `tResponse`, `tErrorResponse`) VALUES('', '" . $_REQUEST['type'] . "', '','','','" . $request_data_param . "','','')";
      //$obj->sql_query($sql_request_data);
      //$request_data_param = http_build_query($_REQUEST, '', '&');
      //
      //$sql_request_data = "REPLACE INTO request_data_debug (`tTitle`, `tType`, `tDescription`, `tPurpose`, `tCallToAction`, `tRequestParam`, `tResponse`, `tErrorResponse`) VALUES('', '" . $_REQUEST['type'] . "', '','','','" . $request_data_param . "','','')";
      //$obj->sql_query($sql_request_data);
      } */

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
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Full-resize.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" height="20" width="20" align="left" >';
        } elseif ($a > $c && $count == 0) {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Half-Full-resize.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" height="20" width="20" align="left" >';
            $count = 1;
        } else {
            $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-blank-resize.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" height="20" width="20" align="left" >';
        }
        // if ($c > $i) {
        //     $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Full.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
        // } elseif ($a > $c && $count == 0) {
        //     $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-Half-Full.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
        //     $count = 1;
        // } else {
        //     $str .= '<img src="' . $tconfig["tsite_url"] . 'webimages/icons/ratings_images/Star-blank.png" style="outline:none;text-decoration:none;width:20px;border:none" width="20px;" align="left" >';
        // }
    }

    return $str;
}

function getVehicleFareConfig($tabelName, $vehicleTypeID) {
    global $obj,$vehicleTypeDataArr;
    //Added By HJ On 20-06-2020 For Optimized vehicle_type Table Query Start
    if(isset($vehicleTypeDataArr[$tabelName])){
        $VehicleTypeData = $vehicleTypeDataArr[$tabelName];
    }else{
        $VehicleTypeData = $obj->MySQLSelect("SELECT * from ".$tabelName);
        $vehicleTypeDataArr['vehicle_type'] = $VehicleTypeData;
    }
    $tripVehicleDataArr =$tripVehicleData=$Data_fare= array();
    for($h=0;$h<count($VehicleTypeData);$h++){
        $tripVehicleDataArr[$VehicleTypeData[$h]['iVehicleTypeId']] = $VehicleTypeData[$h];
    }
    if(isset($tripVehicleDataArr[$vehicleTypeID])){
        $Data_fare[] = $tripVehicleDataArr[$vehicleTypeID];
    }
    //Added By HJ On 20-06-2020 For Optimized vehicle_type Table Query End
    //$sql = "SELECT * FROM `" . $tabelName . "` WHERE iVehicleTypeId='$vehicleTypeID'";
    //$Data_fare = $obj->MySQLSelect($sql);
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

function checkDistanceWithGoogleDirections($tripDistance, $startLatitude, $startLongitude, $endLatitude, $endLongitude, $isFareEstimate = "0", $vGMapLangCode = "", $isReturnArr = false) {
    global $generalobj, $obj, $DISTANCE_CALCULATION_STRATEGY, $GOOGLE_SEVER_GCM_API_KEY, $FILTER_ROUTE_RAW_DATA, $MAPS_API_REPLACEMENT_STRATEGY, $tconfig,$vSystemDefaultLangvGMapLangCode;
    if ($vGMapLangCode == "" || $vGMapLangCode == NULL) {
        //Added By HJ On 24-06-2020 For Optimize language_master Table Query Start
        if (!empty($vSystemDefaultLangvGMapLangCode)) {
            $vGMapLangCode = $vSystemDefaultLangvGMapLangCode;
        } else {
            $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault', 'Yes');
            $vGMapLangCode = $vLangCodeData[0]['vGMapLangCode'];
        }
        //Added By HJ On 24-06-2020 For Optimize language_master Table Query End
    }
    if (empty($GOOGLE_SEVER_GCM_API_KEY)) {
        $GOOGLE_API_KEY = $generalobj->getConfigurations("configurations", "GOOGLE_SEVER_GCM_API_KEY");
    } else {
        $GOOGLE_API_KEY = $GOOGLE_SEVER_GCM_API_KEY;
    }

    if (!empty($_REQUEST['type']) && $_REQUEST['type'] == "ProcessEndTrip" && !empty($_REQUEST["TripId"]) && !empty($DISTANCE_CALCULATION_STRATEGY)) {
        $tripId = $_REQUEST["TripId"];
        $TripPathLatitudes =$TripPathLongitudes= "";
        if (strtoupper($FILTER_ROUTE_RAW_DATA) == "YES") {
            $sql_snap_locations = "SELECT * FROM `trips_route_locations` WHERE iTripId = '$tripId' AND eType='SnapToRoad'";
            $Data_snap_locations = $obj->MySQLSelect($sql_snap_locations);

            if (!empty($Data_snap_locations) && count($Data_snap_locations) > 0) {
                $TripPathLatitudes = explode(",", $Data_snap_locations[0]['tPlatitudes']);
                $TripPathLongitudes = explode(",", $Data_snap_locations[0]['tPlongitudes']);
            }
        }
    }
    include_once($tconfig["tpanel_path"] . "assets/libraries/include_advance_api.php");
    $requestDataArr = array();
    $requestDataArr['SOURCE_LATITUDE'] = $startLatitude;
    $requestDataArr['SOURCE_LONGITUDE'] = $startLongitude;
    $requestDataArr['DEST_LATITUDE'] = $endLatitude;
    $requestDataArr['DEST_LONGITUDE'] = $endLongitude;
    $requestDataArr['LANGUAGE_CODE'] = $vGMapLangCode;

    $direction_data = getPathInfoBetweenLocations($requestDataArr);

    $distance_google_directions = $direction_data['distance'] / 1000;

    if ($isFareEstimate == "0") {
        $comparedDist = ($distance_google_directions * 85) / 100;
        if ($isReturnArr == true) {
            if ($tripDistance > $comparedDist) {
                $distance_google_directions_val = $tripDistance;
            } else {
                $distance_google_directions_val = round($distance_google_directions, 2);
            }

            $duration_google_directions = $direction_data['duration'];
            $sAddress = "";
            $dAddress = "";
            $steps = $direction_data['data'];
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
        $duration_google_directions = $direction_data['duration'] / 60;
        $sAddress = "";
        $dAddress = "";
        $steps = $direction_data['data'];
        $returnArr['Time'] = $duration_google_directions;
        $returnArr['Distance'] = $distance_google_directions;
        $returnArr['SAddress'] = $sAddress;
        $returnArr['DAddress'] = $dAddress;
        $returnArr['steps'] = $steps;
        return $returnArr;
    }
}

/* function checkDistanceWithGoogleDirections($tripDistance, $startLatitude, $startLongitude, $endLatitude, $endLongitude, $isFareEstimate = "0", $vGMapLangCode = "", $isReturnArr = false) {
  global $generalobj, $obj, $DISTANCE_CALCULATION_STRATEGY, $GOOGLE_SEVER_GCM_API_KEY, $FILTER_ROUTE_RAW_DATA;
  if ($vGMapLangCode == "" || $vGMapLangCode == NULL) {
  $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault', 'Yes');
  $vGMapLangCode = $vLangCodeData[0]['vGMapLangCode'];
  }

  if (empty($GOOGLE_SEVER_GCM_API_KEY)) {
  $GOOGLE_API_KEY = $generalobj->getConfigurations("configurations", "GOOGLE_SEVER_GCM_API_KEY");
  } else {
  $GOOGLE_API_KEY = $GOOGLE_SEVER_GCM_API_KEY;
  }

  if (!empty($_REQUEST['type']) && $_REQUEST['type'] == "ProcessEndTrip" && !empty($_REQUEST["TripId"]) && !empty($DISTANCE_CALCULATION_STRATEGY)) {
  $tripId = $_REQUEST["TripId"];
  $TripPathLatitudes = "";
  $TripPathLongitudes = "";
  if (strtoupper($FILTER_ROUTE_RAW_DATA) == "YES") {
  $sql_snap_locations = "SELECT * FROM `trips_route_locations` WHERE iTripId = '$tripId' AND eType='SnapToRoad'";
  $Data_snap_locations = $obj->MySQLSelect($sql_snap_locations);

  if (!empty($Data_snap_locations) && count($Data_snap_locations) > 0) {
  $TripPathLatitudes = explode(",", $Data_snap_locations[0]['tPlatitudes']);
  $TripPathLongitudes = explode(",", $Data_snap_locations[0]['tPlongitudes']);
  }
  }
  }

  $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $startLatitude . "," . $startLongitude . "&destination=" . $endLatitude . "," . $endLongitude . "&sensor=false&key=" . $GOOGLE_API_KEY . "&language=" . $vGMapLangCode;
  try {
  $jsonfile = file_get_contents($url);
  } catch (ErrorException $ex) {
  // return $tripDistance;
  $returnArr['Action'] = "0";
  setDataResponse($returnArr);
  // echo 'Site not reachable (' . $ex->getMessage() . ')';
  }
  $jsondata = json_decode($jsonfile);
  $distance_google_directions = ($jsondata->routes[0]
  ->legs[0]
  ->distance
  ->value) / 1000;
  if ($isFareEstimate == "0") {
  $comparedDist = ($distance_google_directions * 85) / 100;
  if ($isReturnArr == true) {
  if ($tripDistance > $comparedDist) {
  $distance_google_directions_val = $tripDistance;
  } else {
  $distance_google_directions_val = round($distance_google_directions, 2);
  }

  $duration_google_directions = ($jsondata->routes[0]
  ->legs[0]
  ->duration
  ->value);
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
  } */

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

//temporary put this new fun in app_common_function file..bc error when include generalfunction and also error when in app_common_function put that function so change name..its include in cx-myorder.php,include_webservice_delivery.php
function sendApplePushNotificationOrder($PassengerToDriver = 0, $deviceTokens, $message, $alertMsg, $filterMsg, $fromDepart = '') {
    //global $generalobj, $obj, $IPHONE_PEM_FILE_PASSPHRASE,$APP_MODE,$ENABLE_PUBNUB, $PARTNER_APP_IPHONE_PEM_FILE_NAME, $PASSENGER_APP_IPHONE_PEM_FILE_NAME;
    global $generalobj, $obj, $APP_MODE_TEMP_WEB;
    $sql = "select vValue,vName from configurations where vName in('IPHONE_PEM_FILE_PASSPHRASE','APP_MODE','ENABLE_PUBNUB','PARTNER_APP_IPHONE_PEM_FILE_NAME','PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PARTNER_APP_IPHONE_PEM_FILE_NAME','COMPANY_APP_IPHONE_PEM_FILE_NAME','PRO_COMPANY_APP_IPHONE_PEM_FILE_NAME','PRO_PASSENGER_DL_APP_IPHONE_PEM_FILE_NAME','PASSENGER_DL_APP_IPHONE_PEM_FILE_NAME')";
    $Data_config = $obj->MySQLSelect($sql);
    for ($i = 0; $i < count($Data_config); $i++) {
        $temp_val = $Data_config[$i]['vValue'];
        $temp_vName = $Data_config[$i]['vName'];
        $$temp_vName = trim($temp_val);
    }
    if ($message == "") {
        return "";
    }
    //Added By HJ On 09-08-2019 For Set Apple Push Notification Sound As Per Choosen From Admin Panel Start
    $eUserType = $notificationSound = "default";
    if ($PassengerToDriver == 1) {
        $eUserType = "Provider";
    } else if ($PassengerToDriver == 2) {
        $eUserType = "Store";
    } else if ($PassengerToDriver == 0) {
        $eUserType = "User";
    }
    if ($eUserType != "default") {
        $notificationSound = getCustomeNotificationSound($eUserType);
        $explodeData = explode("_", $notificationSound);
        if (count($explodeData) > 1) {
            $notificationSound = $explodeData[1];
        }
    }
    if (trim($notificationSound) == "") {
        $notificationSound = "default";
    }
    //Added By HJ On 09-08-2019 For Set Apple Push Notification Sound As Per Choosen From Admin Panel End
    $passphrase = $IPHONE_PEM_FILE_PASSPHRASE;
    //$APP_MODE = $APP_MODE;
    //$ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
    $APP_MODE_NOTIFICATION = $APP_MODE;
    if (!empty($APP_MODE_TEMP_WEB) && $APP_MODE_TEMP_WEB != "" && !is_null($APP_MODE_TEMP_WEB)) {
        $APP_MODE_NOTIFICATION = $APP_MODE_TEMP_WEB;
    }
    $prefix = "";
    $url_apns = 'ssl://gateway.sandbox.push.apple.com:2195';
    if ($APP_MODE_NOTIFICATION == "Production") {
        $prefix = "PRO_";
        $url_apns = 'ssl://gateway.push.apple.com:2195';
    }

    if ($PassengerToDriver == 1) {
        //$name = $generalobj->getConfigurations("configurations", $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME");   // send notification to driver
        $name1 = $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else if ($PassengerToDriver == 2) {
        //$name = $generalobj->getConfigurations("configurations", $prefix . "COMPANY_APP_IPHONE_PEM_FILE_NAME");   // send notification to company
        $name1 = $prefix . "COMPANY_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else {
        //$name = $generalobj->getConfigurations("configurations", $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME"); // send notification to passenger
        $name1 = $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    }


    $ctx = stream_context_create();

    if ($fromDepart == 'admin') {
        $name = '../' . $name;
    }

    stream_context_set_option($ctx, 'ssl', 'local_cert', $name);

    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    $fp = stream_socket_client($url_apns, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    // echo "deviceTokens => <pre>";
    // print_r($deviceTokens);
    // echo "<pre>"; print_r($fp); die;
    if (!$fp) {
        if ($ENABLE_PUBNUB == "No") {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_SERVER_COMM_ERROR";
            $returnArr['ERROR'] = $err . $errstr . " " . PHP_EOL;
            echo json_encode($returnArr);
            exit;
            //exit("Failed to connect: $err $errstr" . PHP_EOL);
        }
    }

    // Create the payload body
    if (is_array($alertMsg)) {
        for ($device = 0; $device < count($deviceTokens); $device++) {
            $body['aps'] = array(
                'alert' => $alertMsg[$device],
                'content-available' => 1,
                'body' => $message[$device],
                'sound' => $notificationSound
            );
            // Build the binary notification
            // Encode the payload as JSON
            $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
            //        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
            if ($filterMsg == 1) {
                $payload = stripslashes(preg_replace("/[\n\r]/", "", $payload));
            }
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokens[$device]) . pack('n', strlen($payload)) . $payload;
            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
        }
    } else {
        $body['aps'] = array(
            'alert' => $alertMsg,
            'content-available' => 1,
            'body' => $message,
            'sound' => $notificationSound
        );
        // Encode the payload as JSON
        $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
        //        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
        if ($filterMsg == 1) {
            $payload = stripslashes(preg_replace("/[\n\r]/", "", $payload));
        }

        for ($device = 0; $device < count($deviceTokens); $device++) {
            // Build the binary notification
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokens[$device]) . pack('n', strlen($payload)) . $payload;
            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
        }
    }
    // Close the connection to the server
    fclose($fp);
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
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT, $countryCodeAdmin,$userDetailsArr,$country_data_arr;
    $vCountryfield = "vCountry";
    if (empty($countryCodeAdmin)) {
        if ($UserType == 'Company') {
            $tblname = "company";
            $iUserId = "iCompanyId";
        } else if ($UserType == "Passenger") {
            $tblname = "register_user";
            $iUserId = "iUserId";
        } else {
            $tblname = "register_driver";
            $iUserId = "iDriverId";
        }
        //Added By HJ On 09-06-2020 For Optimization Start
        if(isset($userDetailsArr[$tblname."_".$iMemberId]) && count($userDetailsArr[$tblname."_".$iMemberId]) > 0){
            $sqlcountryCode = array();
            if(isset($userDetailsArr[$tblname."_".$iMemberId][0][$vCountryfield]) && trim($userDetailsArr[$tblname."_".$iMemberId][0][$vCountryfield]) != ""){
                $memberCountryCode = $userDetailsArr[$tblname."_".$iMemberId][0][$vCountryfield];
                if(isset($country_data_arr[$memberCountryCode])){
                    $sqlcountryCode[] = $country_data_arr[$memberCountryCode];
                }
            }
        }
        //Added By HJ On 09-06-2020 For Optimization End
        if(count($sqlcountryCode) > 0){
            // Data Found From Global Array
        }else{
            $sqlcountryCode = $obj->MySQLSelect("SELECT co.eUnit FROM country as co LEFT JOIN $tblname as rd ON co.vCountryCode = rd.$vCountryfield WHERE $iUserId = '" . $iMemberId . "'");
        }
    } else {
        //Added By HJ On 09-06-2020 For Optimization Start
        if(isset($country_data_arr[$countryCodeAdmin])){
            $sqlcountryCode = array();
            $sqlcountryCode[] = $country_data_arr[$countryCodeAdmin];
        } else{
            $sqlcountryCode = $obj->MySQLSelect("SELECT co.eUnit FROM country as co WHERE vCountryCode='" . $countryCodeAdmin . "'");
        }
        //Added By HJ On 09-06-2020 For Optimization End
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
    global $generalobj, $obj, $DEFAULT_DISTANCE_UNIT,$vehicleTypeDataArr,$country_data_arr,$countryAssociateArr,$userDetailsArr,$locationDataArr;
    //Added By HJ On 22-06-2020 For Optimize vehicle_type Table Query Start
    if(isset($vehicleTypeDataArr['vehicle_type'])){
        $vehicleTypeData = $vehicleTypeDataArr['vehicle_type'];
        $typeDataArr = array();
        for($h=0;$h<count($vehicleTypeData);$h++){
            $typeDataArr[$vehicleTypeData[$h]['iVehicleTypeId']] = $vehicleTypeData[$h]['iLocationid'];
        }
        if(isset($typeDataArr[$vehicleTypeID])){
            $iLocationid =$typeDataArr[$vehicleTypeID];
        }else{
            $iLocationid = get_value("vehicle_type", "iLocationid", "iVehicleTypeId", $vehicleTypeID, '', 'true');
        }
    }else {
        $iLocationid = get_value("vehicle_type", "iLocationid", "iVehicleTypeId", $vehicleTypeID, '', 'true');
    }
    //Added By HJ On 22-06-2020 For Optimize vehicle_type Table Query End
    //Added By HJ On 23-06-2020 For Optimize location_master Table Query Start
    if($iLocationid > 0){
        if(isset($locationDataArr['location_master'])){
            $locationData = $locationDataArr['location_master'];
        }else{
            $locationData = $obj->MySQLSelect("SELECT * FROM location_master");
            $locationDataArr['location_master'] = $locationData;
        }
        $locationArr = array();
        for($g=0;$g<count($locationData);$g++){
            $locationArr[$locationData[$g]['iLocationId']] = $locationData[$g]['iCountryId'];
        }
        //echo $iLocationid."<br>";
        //echo "<pre>";print_r($locationDataArr);die;
        if(isset($locationArr[$iLocationid])){
            $iCountryId = $locationArr[$iLocationid];
        }else{
            $iCountryId = get_value("location_master", "iCountryId", "iLocationId", $iLocationid, '', 'true');
        }
    }
    //Added By HJ On 23-06-2020 For Optimize location_master Table Query End
    //echo "<pre>";print_r($iCountryId);die;
    if ($iLocationid == "-1") {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    } else {
        if(isset($countryAssociateArr[$iCountryId])){
            $eUnit = $countryAssociateArr[$iCountryId]['eUnit'];
        }else{
            $eUnit = get_value("country", "eUnit", "iCountryId", $iCountryId, '', 'true');
        }
    }
    if ($eUnit == "" || $eUnit == NULL) {
        $eUnit = $DEFAULT_DISTANCE_UNIT;
    }
    if ($iMemberId != "" && $userType != "") {
        if(isset($userDetailsArr['register_user_'.$iMemberId])){
            $vCountry = $userDetailsArr['register_user_'.$iMemberId][0]['vCountry'];
        }else{
            $vCountry = get_value("register_user", "vCountry", "iUserId", $iMemberId, '', 'true');
        }
        if ($vCountry == "") {
            $userUnit = $DEFAULT_DISTANCE_UNIT;
        } else {
            if(isset($country_data_arr[$vCountry])){
                $userUnit = $country_data_arr[$vCountry]['eUnit'];
            }else{
                $userUnit = get_value("country", "eUnit", "vCountryCode", $vCountry, '', 'true');
            }
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
        $ssql .= " AND tStartLat!='' AND tStartLong!='' AND tSaddress!=''";
    } else {
        $fields = "tEndLat,tEndLong,tDaddress";
        $ssql .= "AND eType != 'UberX' AND tEndLat!='' AND tEndLong!='' AND tDaddress!=''";
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
        include_once("include/include_webservice_sharkfeatures.php");
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

//added by SP for fly changes on 7-9-2019
/* function GetFlyVehicleTypeFromGeoLocation($Address_Array) { //get location without all
  global $generalobj, $obj;
  $Vehicle_Str = "";
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
  function GetVehicleTypeFromFlyLocation($Address_Array) {
  global $generalobj, $obj;
  $Vehicle_Str = "-1";

  if (!empty($Address_Array)) {
  //$sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'FlyStation'";
  $sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'FlyStation'";
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
  } */

function DisplayBookingDetails($iCabBookingId) {
    global $generalobj, $obj;
    $returnArr = array();
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
    //print_r($db_drv_vehicle);die;
    $iDriverVehicleId = $iVehicleTypeId = 0;
    if (count($db_drv_vehicle) > 0) {
        $iDriverVehicleId = $db_drv_vehicle[0]['iDriverVehicleId'];
        $iVehicleTypeId = $db_booking[0]['iVehicleTypeId'];
    }
    $tVehicleTypeDataArr = array();
    if ($db_booking[0]['tVehicleTypeData'] != "" /* && $iVehicleTypeId == 0 */) {
        $tVehicleTypeDataArr = (array) json_decode($db_booking[0]['tVehicleTypeData']);
        if (count($tVehicleTypeDataArr) > 0) {
            $tmpTVehicleTypeDataArr = (array) $tVehicleTypeDataArr[0];
            $iVehicleTypeId = $tmpTVehicleTypeDataArr['iVehicleTypeId'];
        }
    }

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    $sql2 = "SELECT vc.iVehicleCategoryId, vc.iParentId,vc.vCategory_" . $lang . " as vCategory, vc.vCategoryTitle_" . $lang . " as vCategoryTitle, vc.tCategoryDesc_" . $lang . " as tCategoryDesc, vc.ePriceType, vt.vVehicleType_" . $lang . " as vVehicleType, vt.eFareType, vt.fFixedFare, vt.fPricePerHour, vt.fPricePerKM, vt.fPricePerMin, vt.iBaseFare,vt.fCommision, vt.iMinFare,vt.iPersonSize, vt.vLogo as vVehicleTypeImage, vt.eType, vt.eIconType, vt.eAllowQty, vt.iMaxQty, vt.iVehicleTypeId, fFixedFare FROM " . $sql_vehicle_category_table_name . " as vc LEFT JOIN vehicle_type AS vt ON vt.iVehicleCategoryId = vc.iVehicleCategoryId WHERE vt.iVehicleTypeId='" . $iVehicleTypeId . "'";
    $Data = $obj->MySQLSelect($sql2);
    $iParentId = 0;
    if (isset($Data[0]['iParentId']) && $Data[0]['iParentId'] > 0) {
        $iParentId = $Data[0]['iParentId'];
    }
    // echo "ParentID:".$iParentId;exit;
    if (isset($Data[0]['ePriceType']) && $iParentId == 0) {
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
    if (isset($Data[0]['eFareType']) && $Data[0]['eFareType'] == "Fixed") {
        //$fAmount = $vCurrencySymbol.$vehicleTypeData[0]['fFixedFare'];
        $fAmount = $Data[0]['fFixedFare'];
    } else if (isset($Data[0]['eFareType']) && $Data[0]['eFareType'] == "Hourly") {
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
    $eFareType = "Regular";
    if (isset($Data[0]['eFareType'])) {
        $eFareType = $Data[0]['eFareType'];
    }
    $vVehicleType = "";
    if (isset($Data[0]['vVehicleType'])) {
        $vVehicleType = $Data[0]['vVehicleType'];
    }
    $returnArr['SelectedFareType'] = $eFareType;
    $returnArr['SelectedQty'] = $db_booking[0]['iQty'];
    $returnArr['SelectedPrice'] = $iPrice;
    $returnArr['SelectedCurrencySymbol'] = $vSymbol;
    $returnArr['SelectedCurrencyRatio'] = $priceRatio;
    $returnArr['SelectedVehicle'] = $vVehicleType;

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
    global $generalobj, $obj,$restrictNegativeAreaDataArr;
    $ssql = "";
    if ($DropOff == "No") {
        $ssql .= " AND (eRestrictType = 'Pick Up' OR eRestrictType = 'All')";
    } else {
        $ssql .= " AND (eRestrictType = 'Drop Off' OR eRestrictType = 'All')";
    }
    if (!empty($Address_Array)) {
        ############### Check For Allow Location ######################################
        //Commented By HJ On 20-06-2020 For Optimize Query As Per Below Start
        //$sqlaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Allowed'" . $ssql;
        //$allowed_data = $obj->MySQLSelect($sqlaa);
        //Commented By HJ On 20-06-2020 For Optimize Query As Per Below End
        //echo "<pre>d";print_r($allowed_data);die;
        //Added By HJ On 20-06-2020 For Optimized restricted_negative_area Table Query Start
        if(isset($restrictNegativeAreaDataArr['restricted_negative_area'])){
            $getAreaData = $restrictNegativeAreaDataArr['restricted_negative_area'];
        }else{
            $getAreaData = $obj->MySQLSelect("SELECT eType,eRestrictType,rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict'");
            $restrictNegativeAreaDataArr['restricted_negative_area'] = $getAreaData; 
        }
        $allowed_data = $disallowed_data = array();
        for($a=0;$a<count($getAreaData);$a++){
            $eRestrictType = $getAreaData[$a]['eRestrictType'];
            $eType = $getAreaData[$a]['eType'];
            if ($DropOff == "No") {
                if(strtoupper($eRestrictType) == "PICk UP" || strtoupper($eRestrictType) == "ALL"){
                   if(strtoupper($eType) == "ALLOWED"){
                       $allowed_data[] = $getAreaData[$a]; 
                   }else{
                       $disallowed_data[] = $getAreaData[$a];
                   }
                }
            }else{
                if(strtoupper($eRestrictType) == "DROP OFF" || strtoupper($eRestrictType) == "ALL"){
                    if(strtoupper($eType) == "ALLOWED"){
                       $allowed_data[] = $getAreaData[$a];
                   }else{
                       $disallowed_data[] = $getAreaData[$a];
                   }
                }
            }
        }
        //Added By HJ On 20-06-2020 For Optimized restricted_negative_area Table Query End
        ///echo "<pre>";print_r($allowed_data);die;
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
            //Commented By HJ On 20-06-2020 For Optimize Query As Per Above Start
            //$sqldaa = "SELECT rs.iLocationId,lm.vLocationName,lm.tLatitude,lm.tLongitude FROM `restricted_negative_area` AS rs LEFT JOIN location_master as lm ON lm.iLocationId = rs.iLocationId WHERE rs.eStatus='Active' AND lm.eFor = 'Restrict' AND eType='Disallowed'" . $ssql;
            //$disallowed_data = $obj->MySQLSelect($sqldaa);
            //Commented By HJ On 20-06-2020 For Optimize Query As Per Above End
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
        if ($UserType == 'Company') {
            $tblname = "company";
            $iUserId = "iCompanyId";
        } else if ($UserType == "Passenger") {
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
        //$sql = "SELECT fPendingAmount as fPendingAmount FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND iUserId > 0 AND ePaidByPassenger = 'No' $outStandingSql";
        //$sql = $sql .  " ORDER BY iTripOutstandId DESC LIMIT 1";
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
  $eShowRideVehicles = "No";
  $eShowDeliveryVehicles = "Yes";
  $eShowDeliverAllVehicles = "Yes";
  $RideDeliveryBothFeatureDisable = "No";
  if ($APP_TYPE == "Ride-Delivery-UberX") {
  //$RideDeliveryIconArr = getGeneralVarAll_IconBanner();
  $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
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
    global $obj, $APP_TYPE, $generalobj,$vehicleCategoryDataArr;
    $eShowRideVehicles = $eShowDeliveryVehicles = $eShowDeliverAllVehicles = $RideDeliveryBothFeatureDisable = "No";
    $eMotoRideEnable = $eMotoDeliveryEnable = $eRentalEnable = $eMotoRentalEnable = "Yes";
    if ($APP_TYPE == "Ride-Delivery-UberX" || $APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Delivery") {
        $eMotoRideEnable = $eMotoDeliveryEnable = $eRentalEnable = $eMotoRentalEnable = "No";
        $ssql = '';
        if ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Delivery") {
            $ssql .= " AND eFor = 'DeliveryCategory' AND eCatType ='MoreDelivery'";
        }
        $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
        //Added By HJ On 13-06-2020 For Optimization Vehicle Category Table Query Start
        if(isset($vehicleCategoryDataArr[$sql_vehicle_category_table_name])){
            $getVehicleCatData = $vehicleCategoryDataArr[$sql_vehicle_category_table_name];
        }else{
            $getVehicleCatData = $obj->MySQLSelect("SELECT * FROM " . $sql_vehicle_category_table_name);
            $vehicleCategoryDataArr[$sql_vehicle_category_table_name] = $getVehicleCatData;
        }
        $rideDeliveryIconData  = array();
        for($v=0;$v<count($getVehicleCatData);$v++){
            $vehicleeCatType = $getVehicleCatData[$v]['eCatType'];
            if(strtoupper($vehicleeCatType) != "SERVICEPROVIDER"){
                $rideDeliveryIconData[] = $getVehicleCatData[$v];
            }
        }
        //Added By HJ On 17-06-2020 For Optimization Vehicle Category Table Query End
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
        $eShowRideVehicles =$eMotoRideEnable=$eRentalEnable=$eMotoRentalEnable= "Yes";
    }
    
    if(!isRideModuleAvailable()) {
        $eShowRideVehicles =$eMotoRideEnable=$eRentalEnable=$eMotoRentalEnable= "No";
    }
    
    if(!isDeliveryModuleAvailable()) {
        $eShowDeliveryVehicles =$eMotoDeliveryEnable= "No";
    }
    if(!isDeliverAllModuleAvailable()) {
        $eShowDeliverAllVehicles = "No";
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
    
    if ($APP_PAYMENT_METHOD == "Senangpay") {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $tconfig['tsite_url'] . "senang_register.php?iUserId=" . $iUserId . "&UserType=" . $eMemberType;
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
    //Added By HJ On 09-01-2020 For Solved 141 Mantis Bug #2799 Start
    $favAddress = 0;
    if (count($result_Address) == 0 || empty($result_Address)) {
        $sql = "SELECT * from user_fave_address WHERE iUserId = '" . $iUserId . "' AND eUserType = '" . $eUserType . "' AND iUserFavAddressId = '" . $iUserAddressId . "'";
        $result_Address = $obj->MySQLSelect($sql);
        $favAddress = 1;
    }
    //Added By HJ On 09-01-2020 For Solved 141 Mantis Bug #2799 End
    //print_r($result_Address);die;
    $ToTalAddress = count($result_Address);
    if ($ToTalAddress > 0) {
        if ($favAddress > 0) {
            $result_Address[0]['UserAddress'] = $result_Address[0]['vAddress'];
        } else {
            $vAddressType = $result_Address[0]['vAddressType'];
            $vBuildingNo = $result_Address[0]['vBuildingNo'];
            $vLandmark = $result_Address[0]['vLandmark'];
            $vServiceAddress = $result_Address[0]['vServiceAddress'];
            $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
            $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
            $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
            $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
            $result_Address[0]['UserAddress'] = $PickUpAddress;
        }

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
    global $obj, $generalobj, $tconfig,$userAddressDataArr;
    $returnArr =$result_Address= array();
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }
    //Added By HJ On 13-06-2020 For Optimization user_address Table Query Start
    if(isset($userAddressDataArr['user_address_'.$iUserId])){
        $allUserAddress = $userAddressDataArr['user_address_'.$iUserId];
    }else{
        $userAddressDataArr = array();
        $allUserAddress = $obj->MySQLSelect("SELECT * from user_address WHERE iUserId = '" . $iUserId . "' AND eStatus = 'Active'");
        $userAddressDataArr['user_address_'.$iUserId] = $allUserAddress;
    }
    $totalAddressCount = 0;
    for($a=0;$a<count($allUserAddress);$a++){
        $addresUser = $allUserAddress[$a]['eUserType'];
        if(strtoupper($addresUser) == strtoupper($UserType)){
            $totalAddressCount += 1;
            $result_Address[]= $allUserAddress[$a];
        }
    }
    //Added By HJ On 13-06-2020 For Optimization user_address Table Query End
    $ToTalAddress = $totalAddressCount;
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
    global $tconfig;
    if (strtoupper(PACKAGE_TYPE) != "SHARK") {
        return false;
    }
    $shark_file_path = $tconfig['tpanel_path'] . "include/include_webservice_sharkfeatures.php";
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
    $eFly = isset($_REQUEST['eFly']) ? clean($_REQUEST['eFly']) : 'No';
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
        $ssql = " AND (eSystemType = 'Ride' OR eSystemType = 'General') AND eFly = '0'";
    } else if (strtoupper($eType) == "UBERX") {
        // Display Only UberX category + General Category Promocodes
        $ssql = " AND (eSystemType = 'UberX' OR eSystemType = 'General')";
    } else {
        // Display Only General category Promocodes
        $ssql = " AND eSystemType = 'General'";
    }
    if ($eFly == 'Yes' || $eType == 'Fly') { //eType condition because of website
        $ssql = " AND ((eSystemType = 'Ride' AND eFly = '1') OR eSystemType = 'General')";
    }
    if ($promoCode != "") {
        // This will be used to validate promocode. If blank then this function gives array of all valid promo codes
        $ssql .= " AND vCouponCode='" . $promoCode . "'";
    } else {
        $ssql .= " AND vPromocodeType = 'Public'";
    }

    if ($userType == "Passenger") {
        $UserDetail = get_value('register_user AS ru LEFT JOIN currency AS c ON c.vName=ru.vCurrencyPassenger', 'ru.vCurrencyPassenger as currencyCode, ru.vLang as vLang, c.Ratio as currencyRatio, c.vSymbol as currencySymbol', 'ru.iUserId', $iMemberId);
    } else {
        $UserDetail = get_value('register_driver AS rd LEFT JOIN currency AS c ON c.vName=rd.vCurrencyDriver', 'rd.vCurrencyDriver as currencyCode, rd.vLang as vLang, c.Ratio as currencyRatio, c.vSymbol as currencySymbol', 'rd.iDriverId', $iMemberId);
    }
    // add condition when tDescription blank for json extract
    $couponData = $obj->MySQLSelect("SELECT iCouponId,vCouponCode,fDiscount, eType, eValidityType, dActiveDate, dExpiryDate, eSystemType, eFly, CASE WHEN JSON_VALID(tDescription) THEN JSON_UNQUOTE(json_extract(tDescription, '$.tDescription_" . $UserDetail[0]['vLang'] . "')) ELSE null END AS tDescription  from coupon WHERE eStatus = 'Active' AND iUsageLimit > iUsed " . $ssql . " ORDER BY iCouponId DESC");

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
            $couponData_tmp['eSystemType'] = ($couponData_tmp['eSystemType'] == 'Ride' && $couponData_tmp['eFly'] == 1) ? 'Fly' : $couponData_tmp['eSystemType'];
            $finalCouponData[] = $couponData_tmp;
        }
    }
    $returnData = array();
    $returnData['CouponList'] = $finalCouponData;
    $returnData['vCurrency'] = $UserDetail[0]['currencyCode'];
    $returnData['vSymbol'] = $UserDetail[0]['currencySymbol'];
    return $returnData;
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
    $userCurrencyRatio = 1;
    $vLanguage = "EN";
    $currencySymbol = "";
    $passengerData = array();
    if ($iUserId > 0) {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
    }
    //echo "<pre>";print_r($iUserId);die;
    if (count($passengerData) > 0) {
        $vLanguage = $passengerData[0]['vLang'];
        $userCurrencyRatio = $passengerData[0]['Ratio'];
        $currencySymbol = $passengerData[0]['vSymbol'];
    } else {
        //Added By HJ On 23-01-2020 For Get Currency Data Start
        $currencycode = isset($_REQUEST["vGeneralCurrency"]) ? $_REQUEST["vGeneralCurrency"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Curren cy Code
        $vLanguage = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : ''; // Added By HJ On 23-01-2020 When User Not Logged In Get Language Code
        if (($currencySymbol == "" || $currencySymbol == NULL) && $currencycode != "") {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE vName = '" . $currencycode . "'");
        } else {
            $currencyData = $obj->MySQLSelect("SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'");
        }
        if (count($currencyData) > 0) {
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $userCurrencyRatio = $currencyData[0]['Ratio'];
        } else {
            $currencycode = "USD";
            $currencySymbol = "$";
            $userCurrencyRatio = 1.0000;
        }
        //Added By HJ On 23-01-2020 For Get Currency Data End
    }
    if ($vLanguage == "") {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    if (count($languageLabelsArr) == 0) {
        //$iServiceId = 1;
        $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    }
    $cuisine_all = $companyCuisineArr = $companyLatLangArr = $offerMsgArr = $restaurantStatusArr = $companyCuisineIdArr = $pricePerPersonArr = $storeMinOrdValueArr = $storePrepareTimeArr = array();
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
        $getStoreLantLangData = $obj->MySQLSelect("SELECT iCompanyId,vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPricePerPerson,fMinOrderValue,fPrepareTime FROM company WHERE iCompanyId IN($storeIds)");
        $LBL_MINS_SMALL = $languageLabelsArr['LBL_MINS_SMALL'];
        for ($re = 0; $re < count($getStoreLantLangData); $re++) {
            $companyLatLangArr[$getStoreLantLangData[$re]['iCompanyId']]['restaurantlat'] = $getStoreLantLangData[$re]['restaurantlat'];
            $companyLatLangArr[$getStoreLantLangData[$re]['iCompanyId']]['restaurantlong'] = $getStoreLantLangData[$re]['restaurantlong'];
            $pricePerPersonArr[$getStoreLantLangData[$re]['iCompanyId']] = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$re]['fPricePerPerson'] * $userCurrencyRatio);
            $storeMinOrdValueArr[$getStoreLantLangData[$re]['iCompanyId']] = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$re]['fMinOrderValue'] * $userCurrencyRatio);
            $storePrepareTimeArr[$getStoreLantLangData[$re]['iCompanyId']] = $getStoreLantLangData[$re]['fPrepareTime'] . " " . $LBL_MINS_SMALL;
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
    $returnArr['restaurantPrepareTime'] = $storePrepareTimeArr;
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
    /*11-06-2020*/
    /*$sql = "SELECT iTripOutstandId,fPendingAmount FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND ePaidByPassenger = 'No' AND ePaymentBy = 'Passenger' AND fPendingAmount >0 $whereCondi";
    if ($iOrganizationId > 0) {
        $sql .= " AND iOrganizationId ='" . $iOrganizationId . "'";
    } else {
        $sql .= " AND iOrganizationId ='0'";
    }
    $sql = $sql .  " ORDER BY iTripOutstandId DESC LIMIT 1";
    $getOutStandingAmt = $obj->MySQLSelect($sql);

    $outStandingAmt = $getOutStandingAmt[0]['fPendingAmount'];*/
    /*11-06-2020*/
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
############################ AppTypeFilterArr #####################################################

function AppTypeFilterArr($iMemberId, $UserType, $vLang, $enableFlyFilter = "Yes") {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    //optimized Done By HJ On 18-10-2019
    global $generalobj, $obj, $APP_TYPE;
    $returnArr = array();
    if ($UserType == "Passenger") {
        $tbl_name = "register_user";
        $currencycode = "vCurrencyPassenger";
        $iUserId = "iUserId";
        $eUserType = "Rider";
    } else {
        $tbl_name = "register_driver";
        $currencycode = "vCurrencyDriver";
        $iUserId = "iDriverId";
        $eUserType = "Driver";
    }
    $langLabels = "'LBL_RIDE','LBL_DELIVERY','LBL_SERVICES','LBL_ALL','LBL_HEADER_RDU_FLY_RIDE'";
    $getLangLabels = $obj->MySQLSelect("SELECT vValue,vLabel FROM `language_label` WHERE vLabel IN ($langLabels) AND vCode = '" . $vLang . "'");
    //echo "<pre>";print_r($getLangLabels);die;
    $codeArr = array();
    for ($l = 0; $l < count($getLangLabels); $l++) {
        $codeArr[$getLangLabels[$l]['vLabel']] = $getLangLabels[$l]['vValue'];
    }
    //echo "<pre>";print_r($codeArr);die;
    foreach ($codeArr as $key => $value) {
        $$key = $value;
    }
    //Commented By HJ On 18-10-2019 For Purpose Of Optimization Start
    //$LBL_RIDE_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_RIDE', " and vCode='" . $vLang . "'", 'true');
    //$LBL_DELIVER = get_value('language_label', 'vValue', 'vLabel', 'LBL_DELIVERY', " and vCode='" . $vLang . "'", 'true');
    //$LBL_JOB_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_SERVICES', " and vCode='" . $vLang . "'", 'true');
    //$LBL_ALL = get_value('language_label', 'vValue', 'vLabel', 'LBL_ALL', " and vCode='" . $vLang . "'", 'true');
    //added by SP for fly stations on 31-08-2019 start
    /* if (checkFlyStationsModule()) {
      $LBL_FLY = get_value('language_label', 'vValue', 'vLabel', 'LBL_HEADER_RDU_FLY_RIDE', " and vCode='" . $vLang . "'", 'true');
      } */
    //added by SP for fly stations on 31-08-2019 end
    //Commented By HJ On 18-10-2019 For Purpose Of Optimization End
    if ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Delivery-UberX") {
        $returnArr[] = array("vFilterParam" => "", "vTitle" => $LBL_ALL);
        $returnArr[] = array("vFilterParam" => "Ride", "vTitle" => $LBL_RIDE);
    
         if (isDeliveryModuleAvailable()) {
        $returnArr[] = array("vFilterParam" => "Deliver", "vTitle" => $LBL_DELIVERY);
	   }
        if ($APP_TYPE == "Ride-Delivery-UberX" && $generalobj->CheckUfxServiceAvailable() == 'Yes') {
            $returnArr[] = array("vFilterParam" => "UberX", "vTitle" => $LBL_SERVICES);
        }
    }

    //added by SP for fly stations on 31-08-2019 start
    if (checkFlyStationsModule() && $enableFlyFilter == "Yes") {
        $returnArr[] = array("vFilterParam" => "eFly", "vTitle" => $LBL_HEADER_RDU_FLY_RIDE);
    }
    //added by SP for fly stations on 31-08-2019 end
    //echo "<pre>";print_r($returnArr);die;
    return $returnArr;
}

###################################### AppTypeFilterArr ##################################################
//Added By HJ On 23-10-2019 For Auto Accept Order By Store Addon Start

function ConfirmOrderByRestaurantcall($iCompanyId, $iOrderId) {
    global $obj, $generalobj, $PUBNUB_DISABLED;
    $where = " iOrderId = '" . $iOrderId . "'";
    $Data_update_order['iStatusCode'] = '2';
    $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order, 'update', $where);
    $id = createOrderLog($iOrderId, "2");
    // # Send Notification To User ##
    $Message = "OrderConfirmByRestaurant";
    $sql = "select ru.iUserId,ru.iGcmRegId,ru.eDeviceType,ru.tSessionId,ru.iAppVersion,ru.vLang,ord.vOrderNo,ord.iServiceId from orders as ord LEFT JOIN register_user as ru ON ord.iUserId=ru.iUserId where ord.iOrderId = '" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $vLangCode = $data_order[0]['vLang'];
    $vOrderNo = $data_order[0]['vOrderNo'];
    $iUserId = $data_order[0]['iUserId'];
    $iServiceId = $data_order[0]['iServiceId'];
    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    $alertMsg = $languageLabelsArr['LBL_CONFIRM_ORDER_BY_RESTAURANT_APP_TXT'];
    $message_arr = array();
    $message_arr['Message'] = $Message;
    $message_arr['iOrderId'] = $iOrderId;
    $message_arr['vOrderNo'] = $vOrderNo;
    $message_arr['vTitle'] = $alertMsg;
    $message_arr['tSessionId'] = $data_order[0]['tSessionId'];
    $message_arr['eSystem'] = "DeliverAll";
    $message = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }
    $alertSendAllowed = true;
    /* For PubNub Setting */
    $tableName = "register_user";
    $iMemberId_VALUE = $iUserId;
    $iMemberId_KEY = "iUserId";
    $iAppVersion = $data_order[0]['iAppVersion'];
    $eDeviceType = $data_order[0]['eDeviceType'];
    $iGcmRegId = $data_order[0]['iGcmRegId'];
    $tSessionId = $data_order[0]['tSessionId'];
    $registatoin_ids = $iGcmRegId;
    /* For PubNub Setting Finished */
    $deviceTokens_arr_ios = $registation_ids_new = array();
    if ($alertSendAllowed == true) {
        if ($eDeviceType == "Android") {
            array_push($registation_ids_new, $iGcmRegId);
            $Rmessage = array(
                "message" => $message
            );
            $result = send_notification($registation_ids_new, $Rmessage, 0);
        } else {
            array_push($deviceTokens_arr_ios, $iGcmRegId);
            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
        }
    }
    $channelName = "PASSENGER_" . $iUserId;
    if ($eDeviceType == "Ios") {
        sleep(3);
    }
    publishEventMessage($channelName, $message);
    // # Send Notification To User ##
    if ($Order_Update_Id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = "LBL_CONFIRM_ORDER_BY_RESTAURANT";
        $generalobj->orderemaildata($iOrderId, 'Passenger');
    }
}

//Added By HJ On 23-10-2019 For Auto Accept Order By Store Addon End
//Added By HJ On 21-10-2019 For Send Request to Delivery Driver Auto After Accepted Store Start
function sendAutoRequestToDriver($iOrderId, $vCountry) {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    global $PUBNUB_PUBLISH_KEY, $PUBNUB_SUBSCRIBE_KEY, $uuid, $obj, $iServiceId, $intervalmins, $PUBNUB_DISABLED;
    $checkOrderRequestStatusArr = checkOrderRequestStatus($iOrderId);
    //echo "<pre>";print_r($checkOrderRequestStatusArr);die;
    $action = $checkOrderRequestStatusArr['Action'];
    $db_order = $obj->MySQLSelect('select * from orders where iOrderId="' . $iOrderId . '" and iStatusCode="2"');
    $vOrderNo = $db_order[0]['vOrderNo'];
    //echo "<pre>";print_r($action);die;
    if ($action == 0) {
        //echo json_encode($checkOrderRequestStatusArr);exit;
        //Send Mail To Admin
        sendMailToAdmin($vOrderNo);
    }
    //echo "<pre>";print_r($db_order);die;
    $iUserId = $db_order[0]['iUserId'];
    $iCompanyId = $db_order[0]['iCompanyId'];
    $iUserAddressId = $db_order[0]['iUserAddressId'];
    $ePaymentOption = $db_order[0]['ePaymentOption'];

    $companyfields = "vCompany,vRestuarantLocation,vRestuarantLocationLat,vRestuarantLocationLong,vCaddress,iGcmRegId,vImage as vImgName,vAvgRating,vPhone,vCode as vPhoneCode";
    $Data_cab_requestcompany = get_value('company', $companyfields, 'iCompanyId', $iCompanyId);
    $UserSelectedAddressArr = GetUserAddressDetail($iUserId, "Passenger", $iUserAddressId);
    $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
    //echo "<pre>";print_r($UserSelectedAddressArr);exit;
    $userwaitinglabel = $languageLabelsArr['LBL_TRIP_USER_WAITING_DL'];
    $alertMsg = $userwaitinglabel;
    $PickUpAddress = $Data_cab_requestcompany[0]['vRestuarantLocation'];
    $DestAddress = $UserSelectedAddressArr['UserAddress'];
    $PickUpLatitude = $Data_cab_requestcompany[0]['vRestuarantLocationLat'];
    $PickUpLongitude = $Data_cab_requestcompany[0]['vRestuarantLocationLong'];
    $DestLatitude = $UserSelectedAddressArr['vLatitude'];
    $DestLongitude = $UserSelectedAddressArr['vLongitude'];
    $address_data['PickUpAddress'] = $PickUpAddress;
    $address_data['DropOffAddress'] = $DestAddress;
    $DataArr = getOnlineDriverArr($PickUpLatitude, $PickUpLongitude, $address_data, "Yes", "No", "No", "", $DestLatitude, $DestLongitude);
    //echo "<pre>";print_r($DataArr);die;
    $Data = $DataArr['DriverList'];
    $driver_id_auto = "";
    if (isset($DataArr['driver_id_auto'])) {
        $driver_id_auto = $DataArr['driver_id_auto'];
    }
    //echo "<pre>";print_r($isFullWalletCharge);die;
    $fWalletDebit = $db_order[0]['fWalletDebit'];
    $fNetTotal = $db_order[0]['fNetTotal'];
    $isFullWalletCharge = "No";
    if ($fWalletDebit > 0 && $fNetTotal == 0) {
        $isFullWalletCharge = "Yes";
    }
    //echo "<pre>";print_r($isFullWalletCharge);die;
    // # Exclude Drivers From list if wallet balance is lower than minimum wallet balance only for cash orders ##
    if ($ePaymentOption == "Cash" && $isFullWalletCharge == "No") {
        $Data_new = array();
        $Data_new = $Data;
        for ($i = 0; $i < count($Data); $i++) {
            $isRemoveFromList = "No";
            $ACCEPT_CASH_TRIPS = $Data[$i]['ACCEPT_CASH_TRIPS'];
            if ($ACCEPT_CASH_TRIPS == "No") {
                $isRemoveFromList = "Yes";
            }
            if ($isRemoveFromList == "Yes") {
                unset($Data_new[$i]);
            }
        }
        $Data = array_values($Data_new);
        $driver_id_auto = "";
        for ($j = 0; $j < count($Data); $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        $driver_id_auto = trim($driver_id_auto, ",");
    } else if ($driver_id_auto == "") {
        for ($j = 0; $j < count($Data); $j++) {
            $driver_id_auto .= $Data[$j]['iDriverId'] . ",";
        }
        $driver_id_auto = trim($driver_id_auto, ",");
    }
    $final_message['Message'] = "CabRequested";
    $final_message['sourceLatitude'] = strval($PickUpLatitude);
    $final_message['sourceLongitude'] = strval($PickUpLongitude);
    $final_message['PassengerId'] = strval($iUserId);
    $final_message['iCompanyId'] = strval($iCompanyId);
    $final_message['iOrderId'] = strval($iOrderId);
    $passengerFName = $Data_cab_requestcompany[0]['vCompany'];
    $final_message['PName'] = $passengerFName;
    $final_message['PPicName'] = $Data_cab_requestcompany[0]['vImgName'];
    $final_message['PRating'] = $Data_cab_requestcompany[0]['vAvgRating'];
    $final_message['PPhone'] = $Data_cab_requestcompany[0]['vPhone'];
    $final_message['PPhoneC'] = $Data_cab_requestcompany[0]['vPhoneCode'];
    $final_message['PPhone'] = '+' . $final_message['PPhoneC'] . $final_message['PPhone'];
    $final_message['destLatitude'] = strval($DestLatitude);
    $final_message['destLongitude'] = strval($DestLongitude);
    $final_message['MsgCode'] = strval(time() . mt_rand(1000, 9999));
    $final_message['vTitle'] = $alertMsg;
    $final_message['eSystem'] = "DeliverAll";
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $result = $obj->MySQLSelect("SELECT iGcmRegId,eDeviceType,iDriverId,vLang,tSessionId,iAppVersion,vCountry FROM register_driver WHERE iDriverId IN (" . $driver_id_auto . ") AND tLocationUpdateDate > '$str_date' AND vAvailability='Available' AND vCountry LIKE '" . $vCountry . "'");
    //echo "<pre>";print_r($result);die;
    if (count($result) == 0 || $driver_id_auto == "" || count($Data) == 0) {
        //Send Mail To Admin
        sendMailToAdmin($vOrderNo);
    } else {
        $currentDateTime = date("Y-m-d H:i:s");
        $totalSecond = $RIDER_REQUEST_ACCEPT_TIME + 5;
        $cenvertedTime = date('Y-m-d H:i:s', strtotime('+' . $totalSecond . ' seconds', strtotime($currentDateTime)));
        //echo $currentDateTime."<br>";echo $cenvertedTime."<br>";echo strtotime($cenvertedTime);die;
        $obj->sql_query("UPDATE orders SET dCronExpiredDate='" . strtotime($cenvertedTime) . "',tDriverIds='" . $driver_id_auto . "' WHERE iOrderId='" . $iOrderId . "'");
    }
    $where = "";
    if ($PUBNUB_DISABLED == "Yes") {
        $ENABLE_PUBNUB = "No";
    }
    $sourceLoc = $PickUpLatitude . ',' . $PickUpLongitude;
    $destLoc = $DestLatitude . ',' . $DestLongitude;
    $deviceTokens_arr_ios = $registation_ids_new = array();
    foreach ($result as $item) {
        if ($item['eDeviceType'] == "Android") {
            array_push($registation_ids_new, $item['iGcmRegId']);
        } else {
            array_push($deviceTokens_arr_ios, $item['iGcmRegId']);
        }
        $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $item['vLang'] . "'", 'true');
        $tSessionId = $item['tSessionId'];
        $final_message['tSessionId'] = $tSessionId;
        $final_message['vTitle'] = $alertMsg_db;
        $msg_encode = json_encode($final_message, JSON_UNESCAPED_UNICODE);

        // Add User Request
        $data_userRequest = array();
        $data_userRequest['iUserId'] = $iUserId;
        $data_userRequest['iDriverId'] = $item['iDriverId'];
        $data_userRequest['tMessage'] = $msg_encode;
        $data_userRequest['iMsgCode'] = $final_message['MsgCode'];
        $data_userRequest['dAddedDate'] = @date("Y-m-d H:i:s");
        $requestId = addToUserRequest2($data_userRequest);

        // Add Driver Request
        $data_driverRequest = array();
        $data_driverRequest['iDriverId'] = $item['iDriverId'];
        $data_driverRequest['iRequestId'] = $requestId;
        $data_driverRequest['iUserId'] = $iUserId;
        $data_driverRequest['iTripId'] = 0;
        $data_driverRequest['iOrderId'] = $iOrderId;
        $data_driverRequest['eStatus'] = "Timeout";
        $data_driverRequest['vMsgCode'] = $final_message['MsgCode'];
        $data_driverRequest['vStartLatlong'] = $sourceLoc;
        $data_driverRequest['vEndLatlong'] = $destLoc;
        $data_driverRequest['tStartAddress'] = $PickUpAddress;
        $data_driverRequest['tEndAddress'] = $DestAddress;
        $data_driverRequest['tDate'] = @date("Y-m-d H:i:s");
        addToDriverRequest2($data_driverRequest);
        // addToUserRequest($passengerId,$item['iDriverId'],$msg_encode,$final_message['MsgCode']);
        // addToDriverRequest($item['iDriverId'],$passengerId,0,"Timeout");
    }
    if (count($registation_ids_new) > 0) {
        // $Rmessage = array("message" => $message);
        $Rmessage = array("message" => $msg_encode);
        $result = send_notification($registation_ids_new, $Rmessage, 0);
    }
    if (count($deviceTokens_arr_ios) > 0) {
        $result = sendApplePushNotification(1, $deviceTokens_arr_ios, $msg_encode, $alertMsg, 0);
    }

    sleep(3);
    $pubnub = new Pubnub\Pubnub(array(
        "publish_key" => $PUBNUB_PUBLISH_KEY,
        "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
        "uuid" => $uuid
    ));
    $filter_driver_ids = str_replace(' ', '', $driver_id_auto);
    $driverIds_arr = explode(",", $filter_driver_ids);
    //Added By HJ On 01-06-2019 For Get All Driver Data Start
    $driverTripData = $obj->MySQLSelect("SELECT tSessionId,vLang,iDriverId FROM register_driver");
    $driverArr = array();
    for ($d = 0; $d < count($driverTripData); $d++) {
        $driverArr[$driverTripData[$d]['iDriverId']] = $driverTripData[$d];
    }
    $deviceTokens_arr_ios = $registation_ids_new = array();
    for ($j = 0; $j < count($driverIds_arr); $j++) {
        $vLang = $vLangCode;
        $tSessionId = "";
        if (isset($driverArr[$driverIds_arr[$j]])) {
            $tSessionId = $driverArr[$driverIds_arr[$j]]['tSessionId'];
            $vLang = $driverArr[$driverIds_arr[$j]]['vLang'];
        }
        //echo $tSessionId;die;
        $final_message['tSessionId'] = $tSessionId;
        $alertMsg_db = get_value('language_label', 'vValue', 'vLabel', 'LBL_TRIP_USER_WAITING_DL', " and vCode='" . $vLang . "'", 'true');
        $final_message['vTitle'] = $alertMsg_db;
        $msg_encode_pub = json_encode($final_message, JSON_UNESCAPED_UNICODE);
        $channelName = "CAB_REQUEST_DRIVER_" . $driverIds_arr[$j];
        if ($PUBNUB_DISABLED == "Yes") {
            publishEventMessage($channelName, $msg_encode_pub);
        } else {
            $info = $pubnub->publish($channelName, $msg_encode_pub);
        }
    }
}

//Added By HJ On 21-10-2019 For Send Request to Delivery Driver Auto After Accepted Store End
//Added By HJ On 21-10-2019 For Send Mail to Admin When Driver Not Found Start
function sendMailToAdmin($vOrderNo) {
    global $tconfig, $generalobj;
    $maildata = array();
    $maildata['ORDER_NO'] = $vOrderNo;
    $maildata['ADMIN_URL'] = $tconfig['tsite_url_main_admin'] . "allorders.php?serachTripNo=" . $vOrderNo;
    $generalobj->send_email_user("MANUAL_ACCEPT_STORE_ORDER_BY_ADMIN", $maildata);
}

/* Added By PM On 09-12-2019 For Flutterwave Code Start */

function flutterwave_charge($txRefId, $tokenId, $currency, $amount, $email) {
    global $FLUTTERWAVE_SECRET_KEY, $FLUTTERWAVE_API_URL;
    $postdata['SECKEY'] = $FLUTTERWAVE_SECRET_KEY;
    $postdata['token'] = $tokenId;
    $postdata['currency'] = $currency;
    $postdata['amount'] = $amount;
    $postdata['email'] = $email;
    $postdata['txRef'] = $txRefId;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $FLUTTERWAVE_API_URL . "tokenized/charge");
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
    curl_setopt($ch, CURLOPT_TIMEOUT, 200);
    $headers = array('Content-Type: application/json');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $request = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($request, true);
    return $result;
}

//Added By HJ On 24-07-2019 For Verify Flutterwave Transaction Start
function flutterwave_verify($txRefId) {
    global $FLUTTERWAVE_SECRET_KEY, $FLUTTERWAVE_API_URL;
    $result = $token_data = array();
    $apiUrl = $FLUTTERWAVE_API_URL;
    $secretKey = $FLUTTERWAVE_SECRET_KEY;

    $postdata = array('txref' => $txRefId, 'SECKEY' => $secretKey);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . "v2/verify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = [
        'Content-Type: application/json',
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $request = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($request, true);

    $CardNo = $token_id = $chargedCurrency = "";
    $chargedAmt = 0;
    if (isset($result['status']) && $result['status'] == "success") {
        if (isset($result['data']['card']['life_time_token'])) {
            $token_id = $result['data']['card']['life_time_token'];
        }

        if (isset($result['data']['chargedamount'])) {
            $chargedAmt = $result['data']['chargedamount'];
            $chargedCurrency = $result['data']['currency'];
        }
        $last4digits = "XXXX";
        if (isset($result['data']['card']['last4digits'])) {
            $last4digits = $result['data']['card']['last4digits'];
        }
        $CardNo = "XXXXXXXXXXXX" . $last4digits;
    }
    $token_data['token'] = $token_id;
    $token_data['card'] = $CardNo;
    $token_data['chargedAmt'] = $chargedAmt;
    $token_data['chargedCurrency'] = $chargedCurrency;
    $token_data['status'] = $result['status'];
    return $token_data;
}

/* Added By PM On 09-12-2019 For Flutterwave Code End */

//Added By HJ On 21-10-2019 For Send Mail to Admin When Driver Not Found End
//Added By HJ On 31-10-2019 For Get Member Data Start
function getMemberBookingData($vSubFilterParamSel) {
    global $obj, $generalobj, $tconfig, $BOOKING_LATER_ACCEPT_AFTER_INTERVAL, $iServiceId, $_REQUEST, $SERVICE_PROVIDER_FLOW, $APP_TYPE;
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $cabdataPage = isset($_REQUEST['cabdataPage']) ? trim($_REQUEST['cabdataPage']) : 0;
    $tripdataPage = !empty($_REQUEST['tripdataPage']) ? trim($_REQUEST['tripdataPage']) : 0;
    $memberId = isset($_REQUEST["memberId"]) ? $_REQUEST["memberId"] : '';
    $memberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    if (isset($_REQUEST["memberType"])) {
        $memberType = $_REQUEST["memberType"];
    }
    //echo "<pre>";print_r($_REQUEST);die;
    $iTripId = $reqTripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    $iCabBookingId = $reqBookingId = isset($_REQUEST["iCabBookingId"]) ? $_REQUEST["iCabBookingId"] : '';
    $vFilterParam = isset($_REQUEST["vFilterParam"]) ? $_REQUEST["vFilterParam"] : ''; // Ride , Deliver Or UberX
    $vSubFilterParam = $reqSubFilter = isset($_REQUEST["vSubFilterParam"]) ? $_REQUEST["vSubFilterParam"] : ''; // All,Pending,Upcoming,Past
    if ($vSubFilterParamSel != "") {
        $vSubFilterParam = $vSubFilterParamSel;
    }
    $date = $serchDate = isset($_REQUEST["dDateOrig"]) ? $_REQUEST["dDateOrig"] : '';
    $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
    $date = $date . " " . "12:01:00";
    $date = date("Y-m-d H:i:s", strtotime($date));
    $serverTimeZone = date_default_timezone_get();
    $date = converToTz($date, $serverTimeZone, $vTimeZone, "Y-m-d");
    //echo $memberId."===".$memberType;die;
    //$per_page = 10;
    //echo "<pre>";print_r($_REQUEST);die;
    $per_page = 3;
    $cab_per_page = 3;
    $whereTripId = $whereCabType = "";
    if ($vFilterParam == "" || $vFilterParam == NULL) {
        $vFilterParam = "";
    }
    ##  App Type Filtering ##
    if ($vFilterParam != "") {
        if ($vFilterParam == 'eFly') {
            $whereTripId .= " AND tr.eType = 'Ride' AND tr.iFromStationId!=0 AND tr.iToStationId!=0 ";
            $whereCabType = $whereTripId;
        } else if ($vFilterParam == "Deliver") {
            $whereTripId .= " AND tr.eType IN ('Deliver','Multi-Delivery')  AND tr.iFromStationId=0 AND tr.iToStationId=0 ";
            $whereCabType = $whereTripId;
        } else {
            $whereTripId .= " AND tr.eType IN ('" . $vFilterParam . "')  AND tr.iFromStationId=0 AND tr.iToStationId=0 ";
            $whereCabType = $whereTripId;
        }
    }
    //Added By HJ On 06-03-2020 For Get Data As Per App Type Start
    if (strtoupper($APP_TYPE) == "RIDE") {
        $whereTripId = $whereCabType = " AND tr.eType = 'Ride' AND tr.iFromStationId=0 AND tr.iToStationId=0 ";
    } else if (strtoupper($APP_TYPE) == "DELIVERY") {
        $whereTripId = $whereCabType = " AND tr.eType IN ('Deliver','Multi-Delivery')  AND tr.iFromStationId=0 AND tr.iToStationId=0 ";
    } else if (strtoupper($APP_TYPE) == "RIDE-DELIVERY") {
        $whereTripId = $whereCabType = " AND tr.eType IN ('Ride','Deliver','Multi-Delivery')  AND tr.iFromStationId=0 AND tr.iToStationId=0 ";
    } else if (strtoupper($APP_TYPE) == "UBERX") {
        $whereTripId = $whereCabType = " AND tr.eType = 'UberX' AND tr.iFromStationId=0 AND tr.iToStationId=0 ";
    }
    //Added By HJ On 06-03-2020 For Get Data As Per App Type End
    $filterSelected = "inprocess";
    if ($memberType != "Passenger") {
        $filterSelected = "pending";
    }
    if ($vSubFilterParam != "") {
        $filterSelected = $vSubFilterParam;
    } else {
        $vSubFilterParam = $filterSelected;
    }
    //$filterSelected = $vSubFilterParam;
    $subSqlTrip = $subSqlBook = "";
    if ($vSubFilterParam != "" && $vSubFilterParam != "all") {
        if ($vSubFilterParam == "pending") {
            $subSqlTrip .= " AND tr.iActive='Active'";
            $subSqlBook .= " AND tr.eStatus='Pending'";
        } else if ($vSubFilterParam == "upcoming") {
            //$subSqlTrip .= " AND (tr.iActive='Active' OR tr.iActive ='On Going Trip')"; //Commented By HJ On 26-10-2019 As Per Discuss With GP Mam - Active
            $subSqlTrip .= " AND (tr.iActive='Arrived' OR tr.iActive ='On Going Trip')"; //Added By HJ On 26-10-2019 As Per Discuss With GP Mam - Arrived
            $subSqlBook .= " AND (tr.eStatus='Assign' OR tr.eStatus='Accepted')";
        } else if ($vSubFilterParam == "past") {
            /* when last delivery confirmation code remaining and in db trips table its status finished so it will shown in process status and not in finished status */

            $tripdeliverySql = "";
            //if ($memberType == "Passenger") {
            //    $tripDeliveryData = $obj->MySQLSelect("SELECT tr.iTripId FROM `trips_delivery_locations` as td, trips as tr WHERE td.eSignVerification = 'No' AND td.iTripId=tr.iTripId AND tr.iActive = 'Finished'");
            //    $tripdeliverySql = "";
            //    if (!empty($tripDeliveryData)) {
            //        $tripdeliverIds = array_column($tripDeliveryData, 'iTripId');
            //        $tripdeliverIds = implode(',', $tripdeliverIds);
            //        $tripdeliverySql = " and tr.iTripId NOT IN ($tripdeliverIds)";
            //    }
            //}

            $subSqlTrip .= " AND ((tr.iActive='Finished' OR tr.iActive='Canceled') $tripdeliverySql)";
            $subSqlBook .= " AND (tr.eStatus='Declined' OR tr.eStatus='Failed' OR tr.eStatus='Cancel' OR tr.eStatus='Completed')";
        }
        if ($memberType == "Passenger") {
            if ($vSubFilterParam == "upcoming") {
                $subSqlTrip = $subSqlBook = "";
                $subSqlTrip .= " AND tr.iActive=''";
                $subSqlBook .= " AND (tr.eStatus='Assign' OR tr.eStatus='Pending')";
            } else if ($vSubFilterParam == "inprocess") {
                $subSqlTrip = $subSqlBook = "";
                /* when last delivery confirmation code remaining and in db trips table its status finished so it will shown in process status and not in finished status */
                $tripdeliverySql = "";
                //$tripDeliveryData = $obj->MySQLSelect("SELECT tr.iTripId FROM `trips_delivery_locations` as td, trips as tr WHERE td.eSignVerification = 'No' AND td.iTripId=tr.iTripId AND tr.iActive = 'Finished'");
                //if (!empty($tripDeliveryData)) {
                //    $tripdeliverIds = array_column($tripDeliveryData, 'iTripId');
                //    $tripdeliverIds = implode(',', $tripdeliverIds);
                //    $tripdeliverySql = " OR (tr.iActive='Finished' and tr.iTripId IN ($tripdeliverIds))";
                //}
                $subSqlTrip .= " AND (tr.iActive='On Going Trip' OR tr.iActive='Active' OR tr.iActive='Arrived' $tripdeliverySql)";
                $subSqlBook .= " AND tr.eStatus=''";
            }
        }
    }
    //echo $filterSelected."===".$subSqlBook."====".$subSqlTrip;die;
    ##  App Type Filtering ##
    $dateFilter = 1;
    if ($iTripId > 0) {
        $dateFilter = 0;
        $subSqlTrip = $subSqlBook = "";
        $whereTripId .= " AND tr.iTripId='" . $iTripId . "'";
    }
    $searchDate = date("Y-m-d", strtotime($date));
    if ($iCabBookingId > 0) {
        $dateFilter = 0;
        $subSqlTrip = $subSqlBook = "";
        $whereCabType .= " AND tr.iCabBookingId='" . $iCabBookingId . "'";
    }
    if ($date != "" && $memberType != "Passenger" && $dateFilter > 0 && strtolower($vSubFilterParam) == "past") {
        $whereTripId .= " AND tr.tTripRequestDate LIKE '" . $date . "%'";
        $whereCabType .= " AND tr.dBooking_date LIKE '" . $date . "%'";
    }
    $ssql_fav_q = "";
    if (checkFavDriverModule() && $memberType == "Passenger") {
        $ssql_fav_q = getFavSelectQuery($memberId);
    }
    //$tableName = "register_driver";
    //$pkFieldName = "iDriverId";
    if ($memberType == "Passenger") {
        $tableName = "register_user";
        $pkFieldName = "iUserId";
        $fieldName = "vLang,vCurrencyPassenger as vCurrencyDriver";
    } else if ($memberType == "Driver") {
        $tableName = "register_driver";
        $pkFieldName = "iDriverId";
        $fieldName = "vLang,vCurrencyDriver as vCurrencyDriver";
    } else {
        $tblname = "company";
        $pkFieldName = "iCompanyId";
        $fieldName = "vLang,vCurrencyCompany as vCurrencyDriver";
    }
    //LBL_ALL
    $getMemberData = $obj->MySQLSelect("SELECT $fieldName FROM " . $tableName . " WHERE $pkFieldName='" . $memberId . "'");
    $vLanguage = "";
    $vCurrencyDriver = "USD";
    $priceRatio = 1;
    if (count($getMemberData) > 0) {
        $vLanguage = $getMemberData[0]['vLang'];
        $vCurrencyDriver = $getMemberData[0]['vCurrencyDriver'];
    }
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    //$currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyDriver, '', 'true');
    $getCurrencyData = $obj->MySQLSelect("SELECT vSymbol,Ratio FROM currency WHERE vName='" . $vCurrencyDriver . "'");
    if (count($getCurrencyData) > 0) {
        $currencySymbol = $getCurrencyData[0]['vSymbol'];
        $priceRatio = $getCurrencyData[0]['Ratio'];
    }
    //echo "<pre>";print_r($Ratio);die;
    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    //print_r($languageLabelsArr['LBL_ALL']);die;
    //$optionArr = array($languageLabelsArr['LBL_PENDING'], $languageLabelsArr['LBL_UPCOMING'], $languageLabelsArr['LBL_PAST']);
    $optionArr = $returnData = array();
    //$optionArr[] = array("vSubFilterParam" => "", "vTitle" => $languageLabelsArr['LBL_ALL']);
    $optionArr[] = array("vSubFilterParam" => "pending", "vTitle" => $languageLabelsArr['LBL_PENDING']);
    $optionArr[] = array("vSubFilterParam" => "upcoming", "vTitle" => $languageLabelsArr['LBL_UPCOMING']);
    $optionArr[] = array("vSubFilterParam" => "past", "vTitle" => $languageLabelsArr['LBL_PAST']);
    $selPending = "pending";
    $selUpcoming = "upcoming";
    $imgUrl = $tconfig['tsite_upload_images_passenger'];
    if ($memberType == "Passenger") {
        $selPending = "upcoming";
        $selUpcoming = "inprocess";
        $imgUrl = $tconfig['tsite_upload_images_driver'];
        $optionArr = array();
        $tableName = "register_user";
        $pkFieldName = "iUserId";
        $optionArr[] = array("vSubFilterParam" => "inprocess", "vTitle" => $languageLabelsArr['LBL_INPROCESS']);
        $optionArr[] = array("vSubFilterParam" => "upcoming", "vTitle" => $languageLabelsArr['LBL_UPCOMING']);
        $optionArr[] = array("vSubFilterParam" => "past", "vTitle" => $languageLabelsArr['LBL_PAST']);
        $optionArr[] = array("vSubFilterParam" => "all", "vTitle" => $languageLabelsArr['LBL_ALL']);
    }
    //echo "<pre>";print_r($optionArr);die;
    $returnData['subFilterOption'] = $optionArr;
    $additional_mins = $BOOKING_LATER_ACCEPT_AFTER_INTERVAL;
    $currDate = date('Y-m-d H:i:s');
    $currDate = date("Y-m-d H:i:s", strtotime($currDate . "-" . $additional_mins . " minutes"));
    $bookingDate = "";
    if ($memberType == "Driver") {
        $bookingDate .= " AND tr.dBooking_date > '" . $currDate . "'";
    }
    if ($memberType == "Driver") {
        $cabBookingQuery = "SELECT COUNT(tr.iCabBookingId) As TotalIds FROM cab_booking as tr WHERE tr.iDriverId != '' AND ( tr.eStatus = 'Accepted' OR tr.eStatus = 'Assign' OR tr.eStatus = 'Pending') AND tr.iDriverId='" . $memberId . "'" . $bookingDate . $whereCabType . $subSqlBook;
    } else {
        $cabBookingQuery = "SELECT COUNT(tr.iCabBookingId) As TotalIds FROM cab_booking as tr WHERE tr.iUserId='" . $memberId . "' AND  ( tr.eStatus = 'Assign' OR tr.eStatus = 'Pending' OR tr.eStatus = 'Accepted' OR tr.eStatus = 'Declined' OR tr.eStatus = 'Cancel') AND tr.eCancelBy != 'Rider'" . $bookingDate . $whereCabType . $subSqlBook;
    }
    //echo $cabBookingQuery;die;
    $data_count_all = $obj->MySQLSelect($cabBookingQuery);
    if ($iTripId > 0) {
        $data_count_all = array();
    }
    //echo "<pre>";print_r($data_count_all);die;
    $TotalPages = $totalBookingCount = 0;
    if (isset($data_count_all[0]['TotalIds'])) {
        $totalBookingCount = $data_count_all[0]['TotalIds'];
    }
    //print_R($data_count_all);
    //echo $totalBookingCount."dddd";
    //echo $totalBookingCount;exit;
    if ($totalBookingCount > 0) {
        $TotalPages += ceil($totalBookingCount / $per_page);
    }
    $cab_start_limit = ($page - 1) * $cab_per_page;
    $cablimit = " LIMIT " . $cab_start_limit . ", " . $cab_per_page;
    /* if(count($cabData)>0 && $page==1) {
      $per_page = $per_page - count($cabData);
      $start_limit = ($page - 1) * $per_page;
      $limit = " LIMIT " . $start_limit . ", " . $per_page;
      $returnData['tripdataPage'] = $per_page;
      } else if(!empty($tripdataPage)) {
      $start_limit = $tripdataPage;
      //$start_limit = ($page - 1) * $per_page;
      $limit = " LIMIT " . $start_limit . ", " . $per_page;
      //$returnData['tripdataPage'] = $tripdataPage + 1;
      $returnData['tripdataPage'] = ($start_limit + $per_page) ;
      } else {
      $start_limit = ($page - 1) * $per_page;
      $limit = " LIMIT " . $start_limit . ", " . $per_page;
      } */
    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();
    $tableCommonData = "concat(rd.vName,' ',rd.vLastName) as vName ,rd.vPhone,rd.vLatitude,rd.vLongitude, rd.vAvgRating,rd.vTripStatus,tr.vCancelReason,tr.iCancelReasonId,tr.iRentalPackageId,tr.iVehicleTypeId,tr.vTimeZone, tr.eType, tr.eServiceLocation, tr.vWorkLocation, tr.vWorkLocationLatitude, tr.vWorkLocationLongitude, tr.eSelectWorkLocation, tr.tVehicleTypeData, tr.tVehicleTypeFareData,tr.iCabBookingId,tr.iUserAddressId,tr.iDriverId,tr.iFromStationId, tr.iToStationId";
    $memberJoin = "LEFT JOIN register_driver as rd ON rd.iDriverId=tr.iDriverId";
    //echo "<pre>";
    if ($memberType == "Driver") {
        $memberJoin = "LEFT JOIN register_user as rd ON rd.iUserId=tr.iUserId";
        $tableCommonData .= ",rd.vImgName As vImage, rd.vPhoneCode AS vCode,rd.iUserId As iMemberId";
    } else {
        $tableCommonData .= ",rd.vImage, rd.vCode,rd.iDriverId AS iMemberId";
    }
    //vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData = '', '0', tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as vServiceName
    if ($memberType == "Driver") {
        $cabDataQuery = "SELECT 'schedule' as vBookingType," . $tableCommonData . ",tr.eStatus AS iActive, tr.vBookingNo AS vRideNo, tr.vSourceAddresss AS tSaddress,tDestAddress AS tDaddress,tr.iCabBookingId AS iTripId, tr.dBooking_date AS tTripRequestDate,tr.eAutoAssign, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLanguage . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData='','0',tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as ParentCategoryName " . $ssql_fav_q . " FROM `cab_booking` as tr " . $memberJoin . " WHERE tr.iDriverId != '' AND  ( tr.eStatus = 'Accepted' OR tr.eStatus = 'Assign' OR tr.eStatus = 'Pending')  AND tr.iDriverId='" . $memberId . "' $bookingDate $whereCabType $subSqlBook ORDER BY tr.iCabBookingId DESC" . $cablimit; //added by SP on 01-10-2019 for vBookingType instead of BookingType
    } else {
        $cabDataQuery = "SELECT 'schedule' as vBookingType," . $tableCommonData . ",tr.eStatus AS iActive,tr.vBookingNo AS vRideNo, tr.vSourceAddresss AS tSaddress,tDestAddress AS tDaddress,tr.iCabBookingId AS iTripId, tr.dBooking_date AS tTripRequestDate,tr.eAutoAssign, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLanguage . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData='','0',tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as ParentCategoryName " . $ssql_fav_q . " FROM `cab_booking` as tr " . $memberJoin . " WHERE tr.iUserId='$memberId' AND ( tr.eStatus = 'Assign' OR tr.eStatus = 'Pending' OR tr.eStatus = 'Accepted' OR tr.eStatus = 'Declined'  OR tr.eStatus = 'Cancel' ) AND tr.eCancelBy != 'Rider' $bookingDate  $whereCabType $subSqlBook ORDER BY tr.iCabBookingId DESC" . $cablimit;
    }

    $cabData = $obj->MySQLSelect($cabDataQuery);
    //echo $TotalPages;
    //print_r($cabData);die;
    $pending = $upcoming = $past = 0;
    for ($h = 0; $h < count($cabData); $h++) {
        $status = $cabData[$h]['iActive'];
        if ($status == "Pending") {
            $pending += 1;
        } else if ($status == "Assign" || $status == "Accepted") {
            $upcoming += 1;
        }
    }
    if ($iTripId > 0) {
        $cabData = array();
    }
    $data_count_all = $obj->MySQLSelect("SELECT COUNT(tr.iTripId) As TotalIds from trips as tr WHERE tr.$pkFieldName='" . $memberId . "' AND tr.eSystem = 'General' $whereTripId $subSqlTrip");
    //print_r($data_count_all);die;
    if ($iCabBookingId > 0) {
        $data_count_all = array();
    }
    $tripCount = 0;
    if (isset($data_count_all[0]['TotalIds'])) {
        $tripCount = $data_count_all[0]['TotalIds'];
    }

    if ($tripCount > 0) {
        //echo $tripCount ."rrrR". $per_page;exit;
        $TotalPages += ceil($tripCount / $per_page);
    }
    //echo $TotalPages;exit;
    //if ($TotalPages > $page) {
    //echo $tripCount."eeee".$per_page."ttttt";
    //echo $page."ddddd".$TotalPages;exit;
    if (count($cabData) > 0 && count($cabData) == $per_page) {
        $per_page1 = $per_page - count($cabData);
        $start_limit = ($page - 1) * $per_page1;
        $limit = " LIMIT " . $start_limit . ", " . $per_page1;
        $returnData['tripdataPage'] = $per_page1;
    } else { //if(!empty($tripdataPage))
        if ($page == 1) {
            $tripdataPage = 0;
        }
        $start_limit = $tripdataPage;
        //$start_limit = ($page - 1) * $per_page;
        $limit = " LIMIT " . $start_limit . ", " . $per_page;
        //$returnData['tripdataPage'] = $tripdataPage + 1;
        $returnData['tripdataPage'] = ($start_limit + $per_page);
    } /* else {
      $start_limit = ($page - 1) * $per_page;
      $limit = " LIMIT " . $start_limit . ", " . $per_page;
      $returnData['tripdataPage'] = $tripdataPage;
      } */
    //echo $limit;die;
    //$tripData = $obj->MySQLSelect("SELECT tr.* " . $ssql_fav_q . " FROM `trips` as tr WHERE tr.$pkFieldName='$memberId' AND (tr.iActive='Canceled' || tr.iActive='Finished') AND tr.eSystem = 'General' $whereTripId ORDER BY tr.iTripId DESC" . $limit);
    //echo "SELECT 'history' as vBookingType," . $tableCommonData . ",tr.iActive,tr.eFareType,tr.vVerificationMethod, tr.`vRideNo`, tr.tSaddress,tr.tDaddress,tr.iTripId, tr.tTripRequestDate,tr.iFare,tr.fDiscount,tr.fCommision,tr.fTax1,tr.fTax2,tr.fOutStandingAmount,tr.fHotelCommision,tr.fTipPrice,tr.fCancellationFare,tr.fWalletDebit,tr.eHailTrip,tr.eBookingFrom,tr.iFromStationId, tr.iToStationId,fRatio_" . $vCurrencyDriver . " AS priceRatio, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLanguage . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData='','0',tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as ParentCategoryName " . $ssql_fav_q . " from trips as tr " . $memberJoin . " WHERE tr.$pkFieldName='" . $memberId . "' AND tr.eSystem = 'General' $whereTripId $subSqlTrip GROUP BY tr.iTripId ORDER BY tr.iTripId DESC" . $limit;
    //exit;

    $tripData = $obj->MySQLSelect("SELECT 'history' as vBookingType," . $tableCommonData . ",tr.iActive,tr.eFareType,tr.vVerificationMethod, tr.`vRideNo`, tr.tSaddress,tr.tDaddress,tr.iTripId, tr.tTripRequestDate,tr.iFare,tr.fDiscount,tr.fCommision,tr.fTax1,tr.fTax2,tr.fOutStandingAmount,tr.fHotelCommision,tr.fTipPrice,tr.fCancellationFare,tr.fWalletDebit,tr.eHailTrip,tr.eBookingFrom,tr.iFromStationId, tr.iToStationId,fRatio_" . $vCurrencyDriver . " AS priceRatio, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLanguage . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData='','0',tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as ParentCategoryName " . $ssql_fav_q . " from trips as tr " . $memberJoin . " WHERE tr.$pkFieldName='" . $memberId . "' AND tr.eSystem = 'General' $whereTripId $subSqlTrip GROUP BY tr.iTripId ORDER BY tr.iTripId DESC" . $limit);

    if ($iCabBookingId > 0) {
        $tripData = array();
    }
    $orgTripData = $tripData;
    $tripData = array_merge($cabData, $tripData);

    $totalNum = count($tripData);
    if ($memberType == "Driver") {
        $cabDataQuery_rating = $obj->MySQLSelect("SELECT 'schedule' as vBookingType," . $tableCommonData . ",tr.eStatus AS iActive, tr.vBookingNo AS vRideNo, tr.vSourceAddresss AS tSaddress,tDestAddress AS tDaddress,tr.iCabBookingId AS iTripId, tr.dBooking_date AS tTripRequestDate,tr.eAutoAssign, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLanguage . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData='','0',tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as ParentCategoryName " . $ssql_fav_q . " FROM `cab_booking` as tr " . $memberJoin . " WHERE tr.iDriverId != '' AND  ( tr.eStatus = 'Accepted' OR tr.eStatus = 'Assign' OR tr.eStatus = 'Pending')  AND tr.iDriverId='" . $memberId . "' $bookingDate $whereCabType $subSqlBook ORDER BY tr.iCabBookingId DESC");
    } else {
        $cabDataQuery_rating = $obj->MySQLSelect("SELECT 'schedule' as vBookingType," . $tableCommonData . ",tr.eStatus AS iActive,tr.vBookingNo AS vRideNo, tr.vSourceAddresss AS tSaddress,tDestAddress AS tDaddress,tr.iCabBookingId AS iTripId, tr.dBooking_date AS tTripRequestDate,tr.eAutoAssign, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLanguage . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData='','0',tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as ParentCategoryName " . $ssql_fav_q . " FROM `cab_booking` as tr " . $memberJoin . " WHERE tr.iUserId='$memberId' AND ( tr.eStatus = 'Assign' OR tr.eStatus = 'Pending' OR tr.eStatus = 'Accepted' OR tr.eStatus = 'Declined'  OR tr.eStatus = 'Cancel' ) AND tr.eCancelBy != 'Rider' $bookingDate  $whereCabType $subSqlBook ORDER BY tr.iCabBookingId DESC");
    }
    if ($iTripId > 0) {
        $cabDataQuery_rating = array();
    }
    $tripData_rating = $obj->MySQLSelect("SELECT 'history' as vBookingType," . $tableCommonData . ",tr.iActive,tr.eFareType,tr.vVerificationMethod, tr.`vRideNo`, tr.tSaddress,tr.tDaddress,tr.iTripId, tr.tTripRequestDate,tr.iFare,tr.fDiscount,tr.fCommision,tr.fTax1,tr.fTax2,tr.fOutStandingAmount,tr.fHotelCommision,tr.fTipPrice,tr.fCancellationFare,tr.fWalletDebit,tr.eHailTrip,tr.eBookingFrom,fRatio_" . $vCurrencyDriver . " AS priceRatio, IF(tr.iVehicleTypeId > 0, (SELECT vt.vVehicleType_" . $vLanguage . " FROM vehicle_type as vt WHERE vt.iVehicleTypeId = tr.iVehicleTypeId), '') as vVehicleType, IF(tr.tVehicleTypeFareData != '', (SELECT vc.vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " as vc WHERE vc.iVehicleCategoryId = JSON_UNQUOTE(json_extract(IF(tr.tVehicleTypeFareData='','0',tr.tVehicleTypeFareData), '$.ParentVehicleCategoryId'))), '') as ParentCategoryName " . $ssql_fav_q . " from trips as tr " . $memberJoin . " WHERE tr.$pkFieldName='" . $memberId . "' AND tr.eSystem = 'General' $whereTripId $subSqlTrip GROUP BY tr.iTripId ORDER BY tr.iTripId DESC");
    if ($iCabBookingId > 0) {
        $tripData_rating = array();
    }
    //echo "<pre>";print_r($cabDataQuery_rating);
    //echo "<pre>";print_r($tripData_rating);die;
    $tripData_rating = array_merge($cabDataQuery_rating, $tripData_rating);
    $totalNum_rating = count($tripData_rating);
    //Added By HJ On 31-10-2019 For Get Data If Empty Found Start As Per Discuss with KS Sir
    if ($totalNum_rating == 0 && ($filterSelected == "inprocess" || $filterSelected == "pending") && $reqSubFilter == "") {
        getMemberBookingData("upcoming");
    } else if ($totalNum_rating == 0 && $filterSelected == "upcoming" && $reqSubFilter == "") {
        getMemberBookingData("past");
    }
    //Added By HJ On 31-10-2019 For Get Data If Empty Found End As Per Discuss with KS Sir
    //echo "<pre>";print_r($tripData_rating);die;
    $TotalPages = ceil($totalNum_rating / $per_page);
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;
    if (count($cabDataQuery_rating) > 0) {
        $per_page = $per_page - count($cabDataQuery_rating);
        $start_limit = ($page - 1) * $per_page;
        $limit = " LIMIT " . $start_limit . ", " . $per_page;
    }
    //echo $limit;die;
    if ($memberType == "Driver") {
        //$getDriverRateData = $obj->MySQLSelect("SELECT vRating1, vMessage,iTripId FROM ratings_user_driver WHERE eUserType='Passenger'");
        $getDriverRateData = $obj->MySQLSelect("SELECT vRating1, vMessage,iTripId FROM ratings_user_driver WHERE eFromUserType='Passenger' AND eToUserType='Driver'");
        for ($b = 0; $b < count($getDriverRateData); $b++) {
            $driverRateArr[$getDriverRateData[$b]['iTripId']] = $getDriverRateData[$b];
        }
    }
    $tripData_ratingArr = array();
    $totalEarnings_new = $avgRating = $countTrip = $avgRating_new = $countTrip_new = $iFareSum = 0;
    for ($t = 0; $t < $totalNum_rating; $t++) {
        $iTripId = $tripData_rating[$t]['iTripId'];
        $iActive = $tripData_rating[$t]['iActive'];
        $treqDate = $tripData_rating[$t]['tTripRequestDate'];
        $searchReqDate = date("Y-m-d", strtotime($treqDate));
        if (isset($driverRateArr[$iTripId])) {
            $eHailTrip = $tripData_rating[$t]['eHailTrip'];
            $eBookingFrom = $tripData_rating[$t]['eBookingFrom'];
            $vRating1 = $driverRateArr[$iTripId]['vRating1'];
            if ($iActive == "Finished" && $eHailTrip == "No" && ($eBookingFrom != "Hotel" || $eBookingFrom != "Kiosk")) {
                if ($serchDate != "" && $searchReqDate == $searchDate) {
                    $avgRating_new += $vRating1;
                    $countTrip_new += 1;
                } else if ($serchDate == "") {
                    $avgRating_new += $vRating1;
                    $countTrip_new += 1;
                }
            }
        }
        $iActive = $tripData_rating[$t]['iActive'];
        $fCancellationFare = $fWalletDebit = 0;
        if (isset($tripData_rating[$t]['fCancellationFare'])) {
            $fCancellationFare = $tripData_rating[$t]['fCancellationFare'];
        }
        if (isset($tripData_rating[$t]['fWalletDebit'])) {
            $fWalletDebit = $tripData_rating[$t]['fWalletDebit'];
        }
        $tripData_ratingArr[$tripData_rating[$t]['iTripId']] = "0.00";
        if ($iActive == "Finished" || ($iActive == "Canceled" && ($fCancellationFare > 0 || $fWalletDebit > 0))) {
            $tripPriceDetails = getTripPriceDetails($iTripId, $memberId, $memberType, 'DISPLAY');
            $iFare = $tripPriceDetails['iFare'];
            $tripData_rating[$t]['iFare'] = $iFare;
            $tripData_ratingArr[$tripData_rating[$t]['iTripId']] = $iFare;
            $iFareSum = str_replace(',', '', $iFare);
            $totalEarnings_new += $iFareSum;
        }
    }
    //echo $totalEarnings_new."====".count($tripData_rating)."****************";
    //echo "<pre>";print_r($tripData_rating);die;
    $appTypeFilterArr = AppTypeFilterArr($memberId, $memberType, $vLanguage);
    $returnData['Action'] = "0";
    $returnData['message'] = "LBL_NO_DATA_AVAIL";
    $returnData['AppTypeFilterArr'] = $appTypeFilterArr;
    $ratingArr = $vehicleTypeArr = $delLocationArr = $rentalPackageArr = $cancelReasonArr = $verificationDataArr = $driverRateArr = array();
    $tripIds = "";
    //tr.iFare,tr.fDiscount,tr.fCommision,tr.fTax1,tr.fTax2,tr.fOutStandingAmount,tr.fHotelCommision,tr.fTipPrice,tr.fCancellationFare,tr.fWalletDebit,tr.eHailTrip,tr.eBookingFrom
    $driverIdArr = $driverImgArr = $getDriverData = array();
    for ($l = 0; $l < count($tripData); $l++) {
        $driverIdArr[] = $tripData[$l]['iDriverId'];
    }
    if (count($driverIdArr) > 0) {
        $driverIdArr = array_unique($driverIdArr);
        $driverIds = implode($driverIdArr, ",");
        $getDriverData = $obj->MySQLSelect("SELECT vImage,iDriverId FROM register_driver WHERE iDriverId IN ($driverIds)");
    }
    for ($s = 0; $s < count($getDriverData); $s++) {
        $driverImgArr[$getDriverData[$s]['iDriverId']] = $getDriverData[$s]['vImage'];
    }
    $totalEarnings = $avgRating = $countTrip = 0;
    for ($v = 0; $v < count($orgTripData); $v++) {
        $tripIds .= ",'" . $orgTripData[$v]['iTripId'] . "'";
    }

    if (count($tripData) > 0) {
        $iVehicleCategoryIds_str_ufx = "";
        $getRatingData = $obj->MySQLSelect("SELECT count(iRatingId) AS Total,iTripId FROM `ratings_user_driver` WHERE eUserType = '$memberType' GROUP BY iTripId");
        //echo "<pre>";print_r($getRatingData);die;
        for ($r = 0; $r < count($getRatingData); $r++) {
            $ratingArr[$getRatingData[$r]['iTripId']] = $getRatingData[$r]['Total'];
        }
        //echo "<pre>";print_r($ratingArr);die;
        if ($tripIds != "") {
            $tripIds = trim($tripIds, ",");
            //Commented By HJ On 19-06-2020 For Optimized Query As Per Below Start - Removed Join
            //$verificationData = $obj->MySQLSelect("SELECT tr.iTripId,tdl.iTripDeliveryLocationId,tdl.eSignVerification,tr.iActive, tr.eServiceLocation from trips as tr LEFT JOIN trips_delivery_locations as tdl ON tdl.iTripId=tr.iTripId WHERE tr.iTripId IN ($tripIds) AND tr.eSystem = 'General' GROUP BY tdl.iTripDeliveryLocationId ORDER BY tr.iTripId DESC");
            //Commented By HJ On 19-06-2020 For Optimized Query As Per Below End Removed Join
            $verificationData = $obj->MySQLSelect("SELECT tdl.iTripId,tdl.iTripDeliveryLocationId,tdl.eSignVerification from trips_delivery_locations as tdl WHERE tdl.iTripId IN ($tripIds) GROUP BY tdl.iTripDeliveryLocationId ORDER BY tdl.iTripId DESC");
            //echo "<pre>";print_r($verificationData);die;
            for ($f = 0; $f < count($verificationData); $f++) {
                if ($verificationData[$f]['eSignVerification'] == "No") {
                    $verificationDataArr[$verificationData[$f]['iTripId']] = $verificationData[$f]['eSignVerification'];
                } else if (!isset($verificationDataArr[$verificationData[$f]['iTripId']])) {
                    $verificationDataArr[$verificationData[$f]['iTripId']] = $verificationData[$f]['eSignVerification'];
                }
            }
            if ($memberType == "Driver") {
                //$getDriverRateData = $obj->MySQLSelect("SELECT vRating1, vMessage,iTripId FROM ratings_user_driver WHERE eUserType='Passenger'");
                $getDriverRateData = $obj->MySQLSelect("SELECT vRating1, vMessage,iTripId FROM ratings_user_driver WHERE eFromUserType='Passenger' AND eToUserType='Driver'");
                for ($b = 0; $b < count($getDriverRateData); $b++) {
                    $driverRateArr[$getDriverRateData[$b]['iTripId']] = $getDriverRateData[$b];
                }
            }
        }
        $parent_data_arr = $obj->MySQLSelect("SELECT vt.iVehicleTypeId,(SELECT vcs.vCategory_" . $vLanguage . " FROM " . $sql_vehicle_category_table_name . " as vcs WHERE vcs.iVehicleCategoryId = vc.iParentId) as vCategory FROM " . $sql_vehicle_category_table_name . " as vc, vehicle_type as vt WHERE vc.iVehicleCategoryId = vt.iVehicleCategoryId");
        for ($v = 0; $v < count($parent_data_arr); $v++) {
            $vehicleTypeArr[$parent_data_arr[$v]['iVehicleTypeId']] = $parent_data_arr[$v]['vCategory'];
        }
        
        //Commented By HJ On 19-06-2020 For Optimized Query As Per Below Start Removed Join
        //$deliveryLocation = $obj->MySQLSelect("SELECT tdl.iTripDeliveryLocationId,tdl.eSignVerification,tr.iActive, tr.eServiceLocation,tr.iTripId from trips as tr LEFT JOIN trips_delivery_locations as tdl ON tdl.iTripId=tr.iTripId WHERE tr.eSystem = 'General' ORDER BY tr.iTripId DESC");
        //Commented By HJ On 19-06-2020 For Optimized Query As Per Below End Removed Join
        $deliveryLocation = $obj->MySQLSelect("SELECT tdl.iTripId,tdl.iTripDeliveryLocationId,tdl.eSignVerification from trips_delivery_locations as tdl ORDER BY tdl.iTripId DESC");
        for ($d = 0; $d < count($deliveryLocation); $d++) {
            $delLocationArr[$deliveryLocation[$d]['iTripId']][] = $deliveryLocation[$d];
        }
        $getRentalPackage = $obj->MySQLSelect("SELECT iRentalPackageId,vPackageName_" . $vLanguage . " AS vPackageName FROM rental_package");
        for ($p = 0; $p < count($getRentalPackage); $p++) {
            $rentalPackageArr[$getRentalPackage[$p]['iRentalPackageId']] = $getRentalPackage[$p]['vPackageName'];
        }
        $getCancelReasons = $obj->MySQLSelect("SELECT vTitle_" . $vLanguage . " AS cancelReason,iCancelReasonId FROM cancel_reason");
        for ($c = 0; $c < count($getCancelReasons); $c++) {
            $cancelReasonArr[$getCancelReasons[$c]['iCancelReasonId']] = $getCancelReasons[$c]['cancelReason'];
        }
        //echo $totalNum;die;
        //echo "<pre>";print_r($tripData);die;
        for ($t = 0; $t < $totalNum; $t++) {
            $showViewRequestedServicesBtn = $showReScheduleBtn = $showReBookingBtn = $showCancelBookingBtn = $showViewCancelReasonBtn = $showViewDetailBtn = $showLiveTrackBtn = $showAcceptBtn = $showDeclineBtn = $showStartBtn = $showCancelBtn = "No";
            $iTripId = $tripData[$t]['iTripId'];
            $eType = $tripData[$t]['eType'];
            $iActive = $tripData[$t]['iActive'];
            $vBookingType = $tripData[$t]['vBookingType'];
            $iCabBookingId = $tripData[$t]['iCabBookingId'];
            $treqDate = $tripData[$t]['tTripRequestDate'];
            //Added By HJ On 15-05-2020 As Per Discuss With KS Start
            //added by SP iFare not calculated here bc in any case it will gives wrong value so it takes from the getTripPriceDetails fun..
            if (isset($tripData_ratingArr[$iTripId]['iFare']) && $tripData_ratingArr[$iTripId]['iFare'] != "") {
                $tripData[$t]['iFare'] = $tripData_ratingArr[$iTripId]['iFare'];
            } else {
                $tripData[$t]['iFare'] = $tripData_ratingArr[$iTripId];
            }

            if (isset($tripData[$t]['fDiscount'])) {
                $tripData[$t]['fDiscount'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fDiscount'] * $priceRatio);
            }
            if (isset($tripData[$t]['fCommision'])) {
                $tripData[$t]['fCommision'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fCommision'] * $priceRatio);
            }
            if (isset($tripData[$t]['fTax1'])) {
                $tripData[$t]['fTax1'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fTax1'] * $priceRatio);
            }
            if (isset($tripData[$t]['fTax2'])) {
                $tripData[$t]['fTax2'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fTax2'] * $priceRatio);
            }
            if (isset($tripData[$t]['fOutStandingAmount'])) {
                $tripData[$t]['fOutStandingAmount'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fOutStandingAmount'] * $priceRatio);
            }
            if (isset($tripData[$t]['fHotelCommision'])) {
                $tripData[$t]['fHotelCommision'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fHotelCommision'] * $priceRatio);
            }
            if (isset($tripData[$t]['fTipPrice'])) {
                $tripData[$t]['fTipPrice'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fTipPrice'] * $priceRatio);
            }
            if (isset($tripData[$t]['fCancellationFare'])) {
                $tripData[$t]['fCancellationFare'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fCancellationFare'] * $priceRatio);
            }
            if (isset($tripData[$t]['fWalletDebit'])) {
                $tripData[$t]['fWalletDebit'] = $generalobj->setTwoDecimalPoint($tripData[$t]['fWalletDebit'] * $priceRatio);
            }
            if (isset($tripData[$t]['eHailTrip']) && $tripData[$t]['eHailTrip'] == 'Yes') {
                $tripData[$t]['vName'] = $languageLabelsArr['LBL_HAIL_RIDER'];
            }

            $searchReqDate = date("Y-m-d", strtotime($treqDate));
            $vImage = $tripData[$t]['vImage'];
            $iDriverId = $tripData[$t]['iDriverId'];
            $eFareType = $vVerificationMethod = "";
            if (isset($tripData[$t]['eFareType'])) {
                $eFareType = $tripData[$t]['eFareType'];
            }
            if (isset($tripData[$t]['vVerificationMethod']) && $tripData[$t]['vVerificationMethod'] != "" && $tripData[$t]['vVerificationMethod'] != "None") {
                $vVerificationMethod = $tripData[$t]['vVerificationMethod'];
            }
            $signVerification = "";
            if (isset($verificationDataArr[$iTripId])) {
                $signVerification = $verificationDataArr[$iTripId];
            }
            $viewButtonStatus = 0;
            if ($vVerificationMethod != "" && $signVerification == "No" && $iActive == "Finished") {
                $viewButtonStatus = 1;
            }
            $tripData[$t]['eFareType'] = $eFareType;
            if ($vBookingType == 'history') {
                $tripData[$t]['iCabBookingId'] = "";
                if ($reqTripId > 0) {
                    $tripDetailArr = getTripPriceDetails($iTripId, $memberId, $memberType);
                    //echo "<pre>";print_r($tripDetailArr);die;
                    $tripData[$t] = array_merge($tripData[$t], $tripDetailArr);
                }
            }
            $vRating1 = 0;
            $vMessage = "";
            if (isset($driverRateArr[$iTripId])) {
                $eHailTrip = $tripData[$t]['eHailTrip'];
                $eBookingFrom = $tripData[$t]['eBookingFrom'];
                $vRating1 = $driverRateArr[$iTripId]['vRating1'];
                if ($iActive == "Finished" && $eHailTrip == "No" && ($eBookingFrom != "Hotel" || $eBookingFrom != "Kiosk")) {
                    if ($serchDate != "" && $searchReqDate == $searchDate) {
                        $avgRating += $vRating1;
                        $countTrip += 1;
                    } else if ($serchDate == "") {
                        $avgRating += $vRating1;
                        $countTrip += 1;
                    }
                }
                $vMessage = $driverRateArr[$iTripId]['vMessage'];
            }
            $tripData[$t]['vRating1'] = $vRating1;
            $tripData[$t]['vMessage'] = $vMessage;

            $tripData[$t]['vImage'] = $imgUrl . "/" . $tripData[$t]['iMemberId'] . "/" . $vImage;

            $driverImage = "";
            if (isset($driverImgArr[$iDriverId])) {
                $driverImage = $driverImgArr[$iDriverId];
            }
            $tripData[$t]['driverImage'] = $driverImage;

            if ($iCabBookingId > 0 && $vBookingType == 'schedule') {
                $tripData[$t]['iTripId'] = "";
                $DisplayBookingDetails = array();
                $DisplayBookingDetails = DisplayBookingDetails($iCabBookingId);
                $tripData[$t]['tDestAddress'] = "";
                $tripData[$t]['selectedtime'] = $DisplayBookingDetails['selectedtime'];
                $tripData[$t]['selecteddatetime'] = $DisplayBookingDetails['selecteddatetime'];
                $tripData[$t]['SelectedFareType'] = $DisplayBookingDetails['SelectedFareType'];
                $tripData[$t]['SelectedQty'] = $tripData[$t]['iQty'] = $DisplayBookingDetails['SelectedQty'];
                $tripData[$t]['SelectedPrice'] = strval($DisplayBookingDetails['SelectedPrice']);
                $tripData[$t]['SelectedCurrencySymbol'] = $DisplayBookingDetails['SelectedCurrencySymbol'];
                $tripData[$t]['SelectedCurrencyRatio'] = $DisplayBookingDetails['SelectedCurrencyRatio'];
                $tripData[$t]['SelectedVehicle'] = $DisplayBookingDetails['SelectedVehicle'];
                $tripData[$t]['SelectedCategory'] = $DisplayBookingDetails['SelectedCategory'];
                $tripData[$t]['vVehicleType'] = $DisplayBookingDetails['SelectedVehicle'];
                $tripData[$t]['vVehicleCategory'] = $DisplayBookingDetails['SelectedCategory'];
                $tripData[$t]['SelectedCategoryId'] = $DisplayBookingDetails['SelectedCategoryId'];
                $tripData[$t]['SelectedCategoryTitle'] = $DisplayBookingDetails['SelectedCategoryTitle'];
                $tripData[$t]['SelectedCategoryDesc'] = $DisplayBookingDetails['SelectedCategoryDesc'];
                $tripData[$t]['SelectedAllowQty'] = $DisplayBookingDetails['SelectedAllowQty'];
                $tripData[$t]['SelectedPriceType'] = $DisplayBookingDetails['SelectedPriceType'];
                //$tripData[$t]['vService_TEXT_color'] = "#FFFFFF";
                $eAutoAssign = $tripData[$t]['eAutoAssign'];

                if ($memberType != "Driver") {
                    if ($eType == "UberX") {
                        $showCancelBookingBtn = 'Yes';
                        if ($iActive == "Cancel" || $iActive == "Declined") {
                            $showCancelBookingBtn = "No";
                            $showReBookingBtn = $showViewCancelReasonBtn = "Yes";
                        }
                    } else {

                        if ($eAutoAssign == "Yes") {
                            $showCancelBookingBtn = $showReScheduleBtn = "Yes";
                        } else if ($eAutoAssign == "No") {
                            $showCancelBookingBtn = "Yes";
                        }
                        if ($iActive == "Cancel" || $iActive == "Declined") {
                            $showCancelBookingBtn = "No";
                            $showViewCancelReasonBtn = "Yes";
                        }
                    }
                } else {
                    if ($iActive == "Pending") {
                        $showAcceptBtn = $showDeclineBtn = "Yes";
                    }
                    if ($iActive == "Accepted" || $iActive == "Assign") {
                        $showStartBtn = $showCancelBtn = "Yes";
                    }
                }
            }
            
            if(!empty($tripData[$t]['iFromStationId']) && !empty($tripData[$t]['iToStationId']) && $tripData[$t]['iActive']=="Arrived" && $tripData[$t]['vTripStatus']=="Arrived") { //nareshbhai have told me to do this
                $tripData[$t]['vTripStatus'] = "";
                $tripData[$t]['iActive'] = "";
            }

            if ($memberType != "Driver") {
                //'Pending','Assign','Accepted','Declined','Failed','Cancel','Completed'
                if ($eType == "UberX" || $eType == "Multi-Delivery") {
                    if ($iActive == "Accepted") {
                        //$showViewDetailBtn = "Yes";
                        if ($eType == "Multi-Delivery") {
                            $showLiveTrackBtn = $showViewDetailBtn = "Yes";
                        }
                    }
                    if (($iActive == "On Going Trip" || $iActive == "Active") && $eType == "Multi-Delivery") {
                        $showLiveTrackBtn = $showViewDetailBtn = "Yes";
                    }
                    if ($iActive == "On Going Trip" || $iActive == "Active" || $viewButtonStatus == 1) { // Remain sign verification condition
                        $showViewDetailBtn = "Yes";
                    }
                }
            }
            //echo $showViewDetailBtn;die;
            //Start Code For getRideHistory Type
            $rateCount = 0;
            if (isset($ratingArr[$iTripId])) {
                $rateCount = $ratingArr[$iTripId];
            }
            $tripData[$t]['is_rating'] = 'No';
            if ($rateCount > 0) {
                $tripData[$t]['is_rating'] = 'Yes';
            }
            //if ($tripData[$t]["eType"] == 'UberX' && $tripData[$t]["eFareType"] != "Regular") {
            //$tripData[$t]['tDaddress'] = "";
            //}
            /* Start Added For Rental */
            $vPackageName = "";
            if (isset($rentalPackageArr[$tripData[$t]['iRentalPackageId']])) {
                $vPackageName = $rentalPackageArr[$tripData[$t]['iRentalPackageId']];
            }
            $tripData[$t]['vPackageName'] = $vPackageName;
            /* End Added For Rental */
            //End Code For getRideHistory Type
            //Start Code For getOngoingUserTrips Type
            $tripData[$t]['moreServices'] = "No";
            if ($SERVICE_PROVIDER_FLOW == "Provider" && $eType == "UberX" && $tripData[$t]['eServiceLocation'] == "Driver") {
                $tripData[$t]['tSaddress'] = $tripData[$t]['vWorkLocation'];
            }
            $tripData[$t]['SelectedTypeName'] = $tripData[$t]['vServiceTitle'] = $tripData[$t]['vServiceDetailTitle'] = $tripData[$t]['vVehicleType'];

            if ($eType == "Ride") {
                $tripData[$t]['vServiceTitle'] = $languageLabelsArr['LBL_TAXI_BOOKING'];
                if ($vFilterParam == "Ride") {
                    $tripData[$t]['vServiceTitle'] = "";
                }
                if (!empty($tripData[$t]['iFromStationId']) && !empty($tripData[$t]['iToStationId'])) {
                    $tripData[$t]['vServiceTitle'] = $languageLabelsArr['LBL_HEADER_RDU_FLY_RIDE'];
                    if ($vFilterParam == "eFly") {
                        $tripData[$t]['vServiceTitle'] = "";
                    }
                }
            } else if ($eType == "Multi-Delivery" || $eType == "Deliver") {
                $tripData[$t]['vServiceTitle'] = $languageLabelsArr['LBL_DELIVERY'];
                if ($vFilterParam == "Multi-Delivery" || $vFilterParam == "Deliver") {
                    $tripData[$t]['vServiceTitle'] = "";
                }
            }
            //added by SP on 30-9-2019 for cubex when  from app side $vSubFilterParam pass then vServiceTitle be blank start
            if ($vFilterParam != '') {
                $tripData[$t]['vServiceTitle'] = "";
            }
            //added by SP on 30-9-2019 for cubex when  from app side $vSubFilterParam pass then vServiceTitle be blank start

            if ($generalobj->checkDeliveryXThemOn() == 'Yes' || $generalobj->checkRideCXThemOn() == 'Yes') {
                $tripData[$t]['vServiceTitle'] = "";
            }
            $vCategory = "";
            if ($eType == "UberX" && !empty($tripData[$t]['tVehicleTypeFareData'])) {
                $tripData[$t]['moreServices'] = "Yes";
                $tVehicleTypeFareDataArr = (array) json_decode($tripData[$t]['tVehicleTypeFareData']);
                $ParentVehicleCategoryId = isset($tVehicleTypeFareDataArr['ParentVehicleCategoryId']) ? $tVehicleTypeFareDataArr['ParentVehicleCategoryId'] : 0;
                if ($ParentVehicleCategoryId == 0) {
                    $tVehicleTypeFareDataArr_fareArr = (array) ($tVehicleTypeFareDataArr['FareData']);
                    if (count($tVehicleTypeFareDataArr_fareArr) > 0) {
                        if (isset($vehicleTypeArr[$tVehicleTypeFareDataArr_fareArr[0]->id])) {
                            $vCategory = $vehicleTypeArr[$tVehicleTypeFareDataArr_fareArr[0]->id];
                        }
                    }
                } else {
                    $tripData[$t]['ParentVehicleCategoryId'] = $ParentVehicleCategoryId;
                    $iVehicleCategoryIds_str_ufx = $iVehicleCategoryIds_str_ufx == "" ? $ParentVehicleCategoryId : $iVehicleCategoryIds_str_ufx . "," . $ParentVehicleCategoryId;
                }
                $tripData[$t]['eFareTypeServices'] = $tVehicleTypeFareDataArr['eFareTypeServices'];
                if (!empty($tripData[$t]['ParentVehicleCategoryId']) && $tVehicleTypeFareDataArr['eFareTypeServices'] == "Fixed") {
                    $tripData[$t]['vServiceDetailTitle'] = $tripData[$t]['ParentCategoryName'];
                } else {
                    if (isset($vehicleTypeArr[$tripData[$t]['ParentCategoryName']])) {
                        //echo "asd";die;
                        $tripData[$t]['vServiceDetailTitle'] = $vehicleTypeArr[$tripData[$t]['ParentCategoryName']] . " - " . $tripData[$t]['vVehicleType'];
                    }
                }
            }
            //added by SP on 1-10-2019 for  cubex design same for driver and user so change condition
            if ($eType == "UberX" && $SERVICE_PROVIDER_FLOW == "Provider" && $tripData[$t]['moreServices'] == "Yes" && $vBookingType == 'schedule') {
                $showViewRequestedServicesBtn = "Yes";
            }
            //echo $showViewRequestedServicesBtn;die;
            $tripData[$t]['vCategory'] = $vCategory;
            //echo "<pre>";print_r($tripData[$t]);die;
            // Convert Into Timezone
            $tripTimeZone = $tripData[$t]['vTimeZone'];
            if ($tripTimeZone != "") {
                $serverTimeZone = date_default_timezone_get();
                //echo $treqDate."===".$tripTimeZone."=".$serverTimeZone;die;
                $tripData[$t]['tTripRequestDate'] = $tripData[$t]['dBooking_dateOrig'] = converToTz($treqDate, $tripTimeZone, $serverTimeZone);
            }
            // Convert Into Timezone
            $tripData[$t]['dDateOrig'] = $tripData[$t]['tTripRequestDate'];
            $tripData[$t]['dBooking_date'] = date('dS M Y \a\t h:i a', strtotime($tripData[$t]['tTripRequestDate']));
            /* ---------------------Multi delivery start--------------------------- */
            if ($eType == "Multi-Delivery") {
                //$sql = "SELECT tdl.iTripDeliveryLocationId,tdl.eSignVerification,tr.iActive, tr.eServiceLocation from trips as tr LEFT JOIN trips_delivery_locations as tdl ON tdl.iTripId=tr.iTripId WHERE tr.iTripId='" . $tripData[$t]['iTripId'] . "' AND tr.eSystem = 'General' GROUP BY tr.iTripId ORDER BY tr.iTripId DESC";
                //$Data2 = $obj->MySQLSelect($sql);
                $delLocationData = array();
                if (isset($delLocationArr[$tripData[$t]['iTripId']])) {
                    $delLocationData = $delLocationArr[$tripData[$t]['iTripId']];
                }
                for ($j = 0; $j < count($delLocationData); $j++) {
                    if ($delLocationData[$j]['eSignVerification'] == "No") {
                        $tripData[$t]['iTripDeliveryLocationId'] = $delLocationData[$j]['iTripDeliveryLocationId'];
                        $tripData[$t]['eSignVerification'] = $delLocationData[$j]['eSignVerification'];
                    }
                }
            }

            /* ---------------------Multi delivery end ---------------------------- */
            //End Code For getOngoingUserTrips Type
            //Start Code For checkBookings Type
            $iCancelReasonId = $tripData[$t]['iCancelReasonId'];
            $vCancelReason = $tripData[$t]['vCancelReason'];
            if (isset($cancelReasonArr[$iCancelReasonId])) {
                $vCancelReason = $cancelReasonArr[$iCancelReasonId];
            }
            $tripData[$t]['vCancelReason'] = $vCancelReason;
            //Start Code For checkBookings Type
            //Start Code For Show/Hide Button
            $tripData[$t]['showViewRequestedServicesBtn'] = $showViewRequestedServicesBtn;
            $tripData[$t]['showReScheduleBtn'] = $showReScheduleBtn;
            $tripData[$t]['showReBookingBtn'] = $showReBookingBtn;
            $tripData[$t]['showCancelBookingBtn'] = $showCancelBookingBtn;
            $tripData[$t]['showViewCancelReasonBtn'] = $showViewCancelReasonBtn;
            $tripData[$t]['showViewDetailBtn'] = $showViewDetailBtn;
            $tripData[$t]['showLiveTrackBtn'] = $showLiveTrackBtn;
            //Driver Side Schedule Button Status Start
            $tripData[$t]['showStartBtn'] = $showStartBtn;
            $tripData[$t]['showAcceptBtn'] = $showAcceptBtn;
            $tripData[$t]['showDeclineBtn'] = $showDeclineBtn;
            $tripData[$t]['showCancelBtn'] = $showCancelBtn;
            $tripData[$t]['ALLOW_SERVICE_PROVIDER_AMOUNT'] = isset($DisplayBookingDetails['ALLOW_SERVICE_PROVIDER_AMOUNT']) ? $ALLOW_SERVICE_PROVIDER_AMOUNT : "No";
            $tripData[$t]['vService_BG_color'] = RANDOM_COLORS_ARR[array_rand(RANDOM_COLORS_ARR, 1)];
            $tripData[$t]['vService_TEXT_color'] = "#FFFFFF";
            //Driver Side Schedule Button Status End
            //End Code For Show/Hide Button
            if ($iActive == "Active") {
                $tripData[$t]['iActive'] = $languageLabelsArr['LBL_INPROCESS'];
            }

            if (!empty($tripdeliverIds)) {
                $tripdeliverIdsArr = explode(',', $tripdeliverIds);
            }
            if ($iActive == "Finished" && in_array($tripData[$t]['iTripId'], $tripdeliverIdsArr)) {
                $tripData[$t]['iActive'] = $languageLabelsArr['LBL_INPROCESS'];
            }
            //added by SP on 30-9-2019 for cubex when cancel or finished at that time pass yes start
            $tripData[$t]['eShowHistory'] = 'No';
            if ($iActive == "Canceled" || $iActive == "Finished") {
                $tripData[$t]['eShowHistory'] = 'Yes';
            }
            //added by SP on 30-9-2019 for cubex when cancel or finished at that time pass yes end
            //added by SP  for cubex on 12-10-2019 for cubex start
            $Sender_Signature = "";
            if (isset($tripData[$t]['vSignImage']) && $tripData[$t]['vSignImage'] != "") {
                if ((file_exists($tconfig["tsite_upload_trip_signature_images_path"] . $tripData[$t]['vSignImage'])) && $tripData[$t]['vSignImage'] != "") {
                    $Sender_Signature = $tconfig["tsite_upload_trip_signature_images"] . $tripData[$t]['vSignImage'];
                }
            }
            $tripData[$t]['vSignImage'] = $Sender_Signature;
            //added by SP  for cubex on 12-10-2019 for cubex start
        }
        //echo "<pre>";print_r($tripData);die;
        $returnData['message'] = $tripData;
        $returnData['SERVER_TIME'] = date('Y-m-d H:i:s');
        $returnData['AppTypeFilterArr'] = $appTypeFilterArr;
        if ($TotalPages > $page) {
            //echo $TotalPages."===".$page;die;
            $returnData['NextPage'] = "" . ($page + 1);
        } else {
            $returnData['NextPage'] = "0";
        }
        $returnData['Action'] = "1";
    }
    //echo $pending;die;
    //Commented By HJ On 18-10-2019 For Prevent Filter Selection Priority Based On Data Count Start
    /* if ($filterSelected != "all" && $filterSelected != "") {
      if ($pending > 0) {
      $filterSelected = $selPending;
      } else if ($upcoming > 0) {
      $filterSelected = $selUpcoming;
      }
      } */
    //Commented By HJ On 18-10-2019 For Prevent Filter Selection Priority Based On Data Count End
    //$returnData['TotalEarning'] = strval(formatnum($totalEarnings));
    $returnData['TotalEarning'] = strval(formatnum($totalEarnings_new));
    //$returnData['TripCount'] = strval(count($tripData));
    $returnData['TripCount'] = strval(count($tripData_rating));
    $totalRating = 0;
    if ($avgRating_new > 0) {
        $totalRating = number_format($avgRating_new / $countTrip_new, 1);
    }
    /* if ($avgRating > 0) {
      $totalRating = $generalobj->setTwoDecimalPoint($avgRating / $countTrip);
      } */
    $returnData['AvgRating'] = $totalRating;
    $returnData['CurrencySymbol'] = $currencySymbol;
    $returnData['eFilterSel'] = $filterSelected;
    //echo "<pre>";print_r($returnData);die;
    setDataResponse($returnData);
    //print_r($tripData);die;
}

//Added By HJ On 31-10-2019 For Get Member Data End
//Added By HJ On 06-08-2019 For Get Selected Custome Notification Sound File Name End
function modulo($value, $modulus) {
    return ( $value % $modulus + $modulus ) % $modulus;
}

function is_decimal($val) {
    return is_numeric($val) && floor($val) != $val;
}

//added by SP for rounding off currency wise on 26-8-2019 start
//function getRoundingOffAmount($originalFare,$countryCode) {
function getRoundingOffAmount($originalFare, $currCode) {
    global $lang_label, $lang_code, $obj, $generalobj,$currencyAssociateArr;
    if ($currCode != '') {
        if(isset($currencyAssociateArr[$currCode])){
            $getCurrData[] = $currencyAssociateArr[$currCode];
        }else{
            $getCurrData = $obj->MySQLSelect("SELECT  * FROM  `currency` WHERE vName = '" . $currCode . "' AND `eStatus` = 'Active' ");
        }
        if (count($getCurrData) > 0) {
            if ($getCurrData[0]['eRoundingOffEnable'] == "Yes") {
                $fMiddleRangeValue = (isset($getCurrData[0]['fMiddleRangeValue']) && $getCurrData[0]['fMiddleRangeValue'] != '0.00') ? $getCurrData[0]['fMiddleRangeValue'] : '0.00';
                $fFirstRangeValue = (isset($getCurrData[0]['fFirstRangeValue']) && $getCurrData[0]['fFirstRangeValue'] != '0.00') ? $getCurrData[0]['fFirstRangeValue'] : '0.00';
                $fSecRangeValue = (isset($getCurrData[0]['fSecRangeValue']) && $getCurrData[0]['fSecRangeValue'] != '0.00') ? $getCurrData[0]['fSecRangeValue'] : '0.00';
                if ($originalFare != "" && $originalFare != "0.00") {
                    $min1 = 0;
                    $modBy = "100";
                    if (is_decimal($getCurrData[0]['fMiddleRangeValue'])) {
                        $modBy = "1";
                    }
                    $modValue = fmod($originalFare, $modBy);
                    $difValue = $originalFare - $modValue;
                    if (($modValue >= $min1) && ($modValue <= $fMiddleRangeValue)) {
                        //echo "first";
                        $updatedModValue = $fFirstRangeValue;
                        $finalUpdateValue = $difValue + $fFirstRangeValue;
                    } else if (($modValue >= $fMiddleRangeValue) && ($modValue <= $fSecRangeValue)) {
                        //echo "second";
                        $updatedModValue = $fSecRangeValue;
                        $finalUpdateValue = $difValue + $fSecRangeValue;
                    } else if (($modValue >= $fMiddleRangeValue) && ($modValue >= $fSecRangeValue)) {
                        //echo "third";
                        $updatedModValue = $fSecRangeValue;
                        $finalUpdateValue = $difValue + $fSecRangeValue;
                    }
                    $methodValue = $originalFare - $finalUpdateValue;
                    if ($methodValue < 0) {
                        $method = "Addition";
                    } else if ($methodValue > 0) {
                        $method = "Substraction";
                    } else {
                        $method = "None";
                    }
                    $DataArr['originalFareValue'] = $originalFare;
                    $DataArr['method'] = $method;
                    $DataArr['differenceValue'] = abs($methodValue);
                    //$DataArr['finalFareValue'] = $generalobj->formatNum($generalobj->setTwoDecimalPoint($finalUpdateValue,2));//coomented this one bc then in all files where this is used i have to remove setTwoDecimalPoint there
                    $DataArr['finalFareValue'] = $generalobj->setTwoDecimalPoint($finalUpdateValue, 2);
                }
            }
        }
    }
    return $DataArr;
}

function getRoundingOffAmounttrip($originalFare, $rAmt, $rtype, $ratio = 1) {
    global $lang_label, $lang_code, $obj, $generalobj;

    $originalFare = $generalobj->setTwoDecimalPoint($originalFare * $ratio, 2);
    //$rAmt = $generalobj->setTwoDecimalPoint($rAmt * $ratio,2);
    $rAmt = $generalobj->setTwoDecimalPoint($rAmt, 2);

    if ($rtype == 'Addition') {
        $fare = $originalFare + $rAmt;
    } else if ($rtype == 'Substraction') {
        $fare = $originalFare - $rAmt;
    }
    $DataArr['originalFareValue'] = $originalFare;
    $DataArr['method'] = $rtype;
    $DataArr['differenceValue'] = abs($rAmt);
    $DataArr['finalFareValue'] = $generalobj->setTwoDecimalPoint($fare, 2);

    return $DataArr;
    /* find vLanguageCode using member id */
    //$originalFare = 0.56;
    //if ($countryCode != '') {
    if ($currCode != '') {
        //$sql = "SELECT  * FROM  `country` WHERE vCountryCode = '" . $countryCode . "' AND `eStatus` = 'Active' ";
        //$getCountryData = $obj->MySQLSelect($sql);

        $sql = "SELECT  * FROM  `currency` WHERE vName = '" . $currCode . "' AND `eStatus` = 'Active' ";
        $getCurrData = $obj->MySQLSelect($sql);

        if (count($getCurrData) > 0) {
            if ($getCurrData[0]['eRoundingOffEnable'] == "Yes") {

                $fMiddleRangeValue = (isset($getCurrData[0]['fMiddleRangeValue']) && $getCurrData[0]['fMiddleRangeValue'] != '0.00') ? $getCurrData[0]['fMiddleRangeValue'] : '0.00';
                $fFirstRangeValue = (isset($getCurrData[0]['fFirstRangeValue']) && $getCurrData[0]['fFirstRangeValue'] != '0.00') ? $getCurrData[0]['fFirstRangeValue'] : '0.00';
                $fSecRangeValue = (isset($getCurrData[0]['fSecRangeValue']) && $getCurrData[0]['fSecRangeValue'] != '0.00') ? $getCurrData[0]['fSecRangeValue'] : '0.00';

                if ($originalFare != "" && $originalFare != "0.00") {
                    $min1 = 0;

                    /* if(($min1 <= $originalFare) && ($originalFare <= $fFirstRangeValue)){ 

                      $modBy = $fFirstRangeValue;
                      } else if(($fFirstRangeValue <= $originalFare) && ($originalFare <= $fSecRangeValue)){

                      $modBy = $fSecRangeValue;
                      } else{ */

                    //$modBy = 100;
                    //} 

                    if (is_decimal($getCurrData[0]['fMiddleRangeValue'])) {

                        $modBy = "1";
                    } else {
                        $modBy = "100";
                    }
                    $modValue = fmod($originalFare, $modBy);
                    $difValue = $originalFare - $modValue;

                    if (($modValue >= $min1) && ($modValue <= $fMiddleRangeValue)) {
                        //echo "first";
                        $updatedModValue = $fFirstRangeValue;
                        $finalUpdateValue = $difValue + $fFirstRangeValue;
                    } else if (($modValue >= $fMiddleRangeValue) && ($modValue <= $fSecRangeValue)) {
                        //echo "second";
                        $updatedModValue = $fSecRangeValue;
                        $finalUpdateValue = $difValue + $fSecRangeValue;
                    } else if (($modValue >= $fMiddleRangeValue) && ($modValue >= $fSecRangeValue)) {
                        //echo "third";
                        $updatedModValue = $fSecRangeValue;
                        $finalUpdateValue = $difValue + $fSecRangeValue;
                    }

                    $methodValue = $originalFare - $rAmt;
                    if ($methodValue < 0) {
                        $method = "Addition";
                    } else if ($methodValue > 0) {
                        $method = "Substraction";
                    } else {
                        $method = "None";
                    }

                    $DataArr['originalFareValue'] = $originalFare;
                    $DataArr['method'] = $method;
                    $DataArr['differenceValue'] = abs($methodValue);
                    $DataArr['finalFareValue'] = $generalobj->setTwoDecimalPoint($finalUpdateValue, 2);
                }
            }
        }
    }
    return $DataArr;
}

//added by SP for rounding off currency wise on 26-8-2019 end

function fetchAPIDetails() {
    global $_REQUEST;
    $API_URL = isset($_REQUEST['API_URL']) ? utf8_decode(utf8_encode(urldecode($_REQUEST['API_URL']))) : '';
    if ($API_URL != "" || empty($API_URL) == false) {
        $API_URL = preg_replace("/ /", "%20", $API_URL);
        $dataOfAPI = file_get_contents($API_URL);
        //echo $dataOfAPI;
        //$returnData = array();
        //$returnData['DATA_RESULT'] = json_decode($dataOfAPI, true);
        setDataResponse(json_decode($dataOfAPI, true));
    }
}

function getUserLanguageCode() {
    global $_REQUEST;

    $vLangCode = isset($_REQUEST['vLangCode']) ? clean($_REQUEST['vLangCode']) : '';
    $appType = isset($_REQUEST['appType']) ? clean($_REQUEST['appType']) : ''; // Passenger OR Driver
    $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';

    $languageCode = "";
    if (!empty($vGeneralLang)) {
        $languageCode = $vGeneralLang;
    } else if (!empty($vLangCode)) {
        $languageCode = $vLangCode;
    } else if ($iMemberId != "") {
        if ($appType == "Company") {
            $tableName = "company";
            $fieldName = "iCompanyId";
        } else if ($appType == "Driver") {
            $tableName = "register_driver";
            $fieldName = "iDriverId";
            //$languageCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
        } else {
            $tableName = "register_user";
            $fieldName = "iUserId";
            //$languageCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
        }
        $langData = $obj->MySQLSelect("SELECT vLang FROM " . $tableName . " WHERE $fieldName=$iMemberId");
        if (count($langData) > 0) {
            $languageCode = $langData[0]['vLang'];
        }
    }

    if (empty($languageCode)) {
        $languageCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    return $languageCode;
}

function checkFavDriverModule() {
    global $ENABLE_FAVORITE_DRIVER_MODULE, $tconfig;

    $fav_driver_file_path = $tconfig["tpanel_path"] . "include/features/include_fav_driver.php";
    if (file_exists($fav_driver_file_path) && strtoupper($ENABLE_FAVORITE_DRIVER_MODULE) == 'YES' && strtoupper(ONLYDELIVERALL) == "NO") {
        return true;
    }
    return false;
}

function checkDriverDestinationModule($adminfilepath = 0) {
    global $ENABLE_DRIVER_DESTINATIONS, $APP_TYPE, $tconfig;
    $driver_destination_file_path = $tconfig["tpanel_path"] . "include/features/include_destinations_driver.php";
    if (file_exists($driver_destination_file_path) && strtoupper($ENABLE_DRIVER_DESTINATIONS) == 'YES' && (($APP_TYPE == "Ride-Delivery") || ($APP_TYPE == "Ride-Delivery-UberX") || ($APP_TYPE == "Ride"))) {
        return true;
    }
    return false;
}

function checkStopOverPointModule() {
    global $ENABLE_STOPOVER_POINT, $APP_TYPE, $tconfig;
    $stop_over_point_file_path = $tconfig["tpanel_path"] . "include/features/include_stop_over_point.php";

    if (file_exists($stop_over_point_file_path) && strtoupper($ENABLE_STOPOVER_POINT) == 'YES' && (($APP_TYPE == "Ride-Delivery") || ($APP_TYPE == "Ride-Delivery-UberX") || ($APP_TYPE == "Ride"))) {
        return true;
    }
    return false;
}

function checkDonationModule() {
    global $obj, $APP_TYPE, $DONATION_ENABLE, $generalobj, $tconfig;
    $DonationFilepath = $tconfig["tpanel_path"] . "include/features/include_donation.php";
    if (empty($DONATION_ENABLE)) {
        $DONATION_ENABLE = $generalobj->getConfigurations("configurations", "DONATION_ENABLE");
        $DONATION_ENABLE = $DRIVER_SUBSCRIPTION_ENABLE[0]['vValue'];
    }
    if (file_exists($DonationFilepath) && strtoupper($DONATION_ENABLE) == 'YES') {
        return true;
    }
    return false;
}

function isAllowFetchAPIDetails() {
    if (!empty($_REQUEST['type']) && $_REQUEST['type'] == "fetchAPIDetails" && !empty($_REQUEST['GeneralUserType']) && (strtoupper($_REQUEST['GeneralUserType']) == "PASSENGER" || strtoupper($_REQUEST['GeneralUserType']) == "USER" || strtoupper($_REQUEST['GeneralUserType']) == "RIDER" ) && !empty($_REQUEST['iServiceId']) && empty($_REQUEST['tSessionId'])) {
        return true;
    }
    return false;
}

//Added By HJ On 27-05-2020 For Generate Common Random Number Start
function generateCommonRandom() {
    global $COMMON_RANDOM_NUMBER_LENGTH;
    if (isset($COMMON_RANDOM_NUMBER_LENGTH) && $COMMON_RANDOM_NUMBER_LENGTH > 0) {
        $digit = $COMMON_RANDOM_NUMBER_LENGTH;
    } else {
        $digit = 4;
    }
    //echo $COMMON_RANDOM_NUMBER_LENGTH;die;
    $verificationCode = rand(str_repeat(0, $digit), str_repeat(9, $digit));
    return $verificationCode;
}

//Added By HJ On 27-05-2020 For Generate Common Random Number End
?>