<?php

function setStopOverPointLocation($iCabRequestId) {
    global $_REQUEST, $obj;
    $stopoverpoint_arr = isset($_REQUEST["stopoverpoint_arr"]) ? $_REQUEST["stopoverpoint_arr"] : '';
    if ($stopoverpoint_arr != "") {
        $details_arr = json_decode($stopoverpoint_arr, true);
        $last_key = end(array_keys($details_arr));
        foreach ($details_arr as $key => $values) {
            $tDAddress = $values['tDAddress'];
            $tDestLatitude = $values['tDestLatitude'];
            $tDestLongitude = $values['tDestLongitude'];
            $Data_stopoverpoint_location['tDAddress'] = $tDAddress;
            $Data_stopoverpoint_location['tDestLatitude'] = $tDestLatitude;
            $Data_stopoverpoint_location['tDestLongitude'] = $tDestLongitude;
            $Data_stopoverpoint_location['iCabRequestId'] = $iCabRequestId;
            $Data_stopoverpoint_location['fWaitingTime'] = 0.00;
            $Data_stopoverpoint_location['tReachedTime'] = '0000:00:00 00:00:00';
            $Data_stopoverpoint_location['tEndTime'] = '0000:00:00 00:00:00';
            $Data_stopoverpoint_location['eReached'] = 'No';
            $Data_stopoverpoint_location['eCanceled'] = 'No';
            $Data_stopoverpoint_location['iTripId'] = 0;
            $insert_dfid = $obj->MySQLQueryPerform("trips_stopoverpoint_location", $Data_stopoverpoint_location, 'insert');
        }
    }
}

function updateTripIds($iCabRequestId, $iTripId) {
    global $obj;
    $where_trips_stopoverpoint_location_data = "iCabRequestId='" . $iCabRequestId . "'";
    $update_trips_stopoverpoint_location_data['iTripId'] = $iTripId;
    $data = $obj->MySQLQueryPerform('trips_stopoverpoint_location
', $update_trips_stopoverpoint_location_data, 'update', $where_trips_stopoverpoint_location_data);
    return $data;
}

function getStopOverPointData($iTripId) {
    global $generalobj, $obj;

    $fields = "iStopId,tDestLatitude,tDestLongitude,tDAddress,(SELECT count(iStopId) from trips_stopoverpoint_location where iTripId = '" . $iTripId . "') AS totalDestination";
    $sql = "SELECT $fields FROM trips_stopoverpoint_location
 where iTripId = '" . $iTripId . "' AND eReached='No' AND eCanceled='No'  ORDER BY iStopId ASC";

    $db_trips_stopoverpoint_location = $obj->MySQLSelect($sql);

    $trips_stopoverpoint_location_data = array();
    if (!empty($db_trips_stopoverpoint_location) && count($db_trips_stopoverpoint_location) > 0) {
        $trips_stopoverpoint_location_data['iStopId'] = $db_trips_stopoverpoint_location[0]['iStopId'];
        $trips_stopoverpoint_location_data['tDestLatitude'] = $db_trips_stopoverpoint_location[0]['tDestLatitude'];
        $trips_stopoverpoint_location_data['tDestLongitude'] = $db_trips_stopoverpoint_location[0]['tDestLongitude'];
        $trips_stopoverpoint_location_data['tDAddress'] = $db_trips_stopoverpoint_location[0]['tDAddress'];
        $trips_stopoverpoint_location_data['remaininStopOverPoint'] = (count($db_trips_stopoverpoint_location) - 1);
        $trips_stopoverpoint_location_data['totalStopOverPoint'] = $db_trips_stopoverpoint_location[0]['totalDestination'];
        $trips_stopoverpoint_location_data['currentStopOverPoint'] = ($trips_stopoverpoint_location_data['totalStopOverPoint'] - $trips_stopoverpoint_location_data['remaininStopOverPoint']);
    } else {
        $trips_stopoverpoint_location_data['tDestLatitude'] = "";
        $trips_stopoverpoint_location_data['tDestLongitude'] = "";
        $trips_stopoverpoint_location_data['tDAddress'] = "";
        $trips_stopoverpoint_location_data['totalStopOverPoint'] = 0;
        $trips_stopoverpoint_location_data['currentStopOverPoint'] = 0;
        $trips_stopoverpoint_location_data['remaininStopOverPoint'] = 0;
    }
    // print_r($trips_stopoverpoint_location_data);
    return $trips_stopoverpoint_location_data;
}

function dropStopOverPoint() {
    global $generalobj, $obj, $_REQUEST;
    $iStopId = isset($_REQUEST["iStopId"]) ? $_REQUEST["iStopId"] : 0;
    $isTripCanceled = isset($_REQUEST["isTripCanceled"]) ? $_REQUEST["isTripCanceled"] : '';
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $isDropAll = isset($_REQUEST["isDropAll"]) ? $_REQUEST["isDropAll"] : '';
    $userId = isset($_REQUEST["PassengerId"]) ? $_REQUEST["PassengerId"] : '';
    $returnArr = array();
    if (isset($iStopId) && !empty($iStopId)) {
        $where_trips_stopoverpoint_location_data = "iStopId='" . $iStopId . "'";
        $update_trips_stopoverpoint_location_data['eCanceled'] = 'No';
        if (isset($isTripCanceled) && !empty($isTripCanceled) && $isTripCanceled == "true") {
            $where_trips_stopoverpoint_location_data = "iTripId=" . $tripId . " AND eReached='No'";
            $update_trips_stopoverpoint_location_data['eCanceled'] = 'Yes';
            $update_trips_stopoverpoint_location_data['eReached'] = 'No';
        } else {
            $update_trips_stopoverpoint_location_data['eReached'] = 'Yes';
        }
        $update_trips_stopoverpoint_location_data['tReachedTime'] = date('Y-m-d H:i:s');
        $data = $obj->MySQLQueryPerform('trips_stopoverpoint_location', $update_trips_stopoverpoint_location_data, 'update', $where_trips_stopoverpoint_location_data);
        if (isset($isDropAll) && !empty($isDropAll) && $isDropAll == "true") {
            $where_trips_stopoverpoint_location_dropAll = "iTripId=" . $tripId . " AND eReached='No'";
            $update_trips_stopoverpoint_location_dropAll['eCanceled'] = 'Yes';
            $update_trips_stopoverpoint_location_dropAll['eReached'] = 'No';

            $data = $obj->MySQLQueryPerform('trips_stopoverpoint_location', $update_trips_stopoverpoint_location_dropAll, 'update', $where_trips_stopoverpoint_location_dropAll);
        }
        if (isset($isTripCanceled) && !empty($isTripCanceled) && $isTripCanceled == "true") {
            
        } else {
            $stopoverpoint_arr = getStopOverPointData($tripId);
            //$stopoverpoint_arr['tDestLatitude'];
            if (!empty($stopoverpoint_arr['tDestLatitude']) && $stopoverpoint_arr['tDestLatitude'] != "") {
                $tableName = "register_user";
                $iMemberId_VALUE = $userId;
                $iMemberId_KEY = "iUserId";
                $AppData = get_value($tableName, 'iAppVersion,eDeviceType,eLogout,tLocationUpdateDate,iGcmRegId,vLang', $iMemberId_KEY, $iMemberId_VALUE);
                $iAppVersion = $AppData[0]['iAppVersion'];
                $eDeviceType = $AppData[0]['eDeviceType'];
                $eLogout = $AppData[0]['eLogout'];
                $tLocationUpdateDate = $AppData[0]['tLocationUpdateDate'];
                $iGcmRegId = $AppData[0]['iGcmRegId'];
                $vLang = $AppData[0]['vLang'];
                if ($vLang == "" || $vLang == NULL) {
                    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                }
                $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
                $alertMsg = $languageLabelsArr['LBL_STOP_OVER_POINT_REACHED'];
                $alertMsg = str_replace("####", ($stopoverpoint_arr['currentStopOverPoint'] - 1), $alertMsg);
                $deviceTokens_arr = array();
                array_push($deviceTokens_arr, $iGcmRegId);

                if ($eDeviceType == "Android") {
                    $Rmessage = array("message" => $alertMsg);
                    send_notification($deviceTokens_arr, $Rmessage, 0);
                } else {
                    $message = array("message" => $alertMsg);
                    //echo "tset";

                    sendApplePushNotification(0, $deviceTokens_arr, $message, $alertMsg, 0);
                }
                publishEventMessage("PASSENGER_" . $userId, $alertMsg);
                // $returnArr['reminderStopOverPoint'] = count($stopoverpoint_arr);
                $returnArr = array();
                $returnArr['Action'] = "0";
                $returnArr['message'] = "DO_RESTART";
                setDataResponse($returnArr);
            }
        }
    }
}

function getDropOverPointHistory($iTripId = 0) {

    global $_REQUEST, $generalobj, $obj;
    $iCabRequestId = isset($_REQUEST["iCabRequestId"]) ? $_REQUEST["iCabRequestId"] : '';
    $tripId = isset($_REQUEST["iTripId"]) ? $_REQUEST["iTripId"] : '';
    if (!empty($iCabRequestId)) {
        $wher_conditon = "iCabRequestId = '" . $iCabRequestId . "'";
    } else if (!empty($tripId)) {
        $wher_conditon = "iTripId = '" . $tripId . "'";
    } else if (!empty($iTripId)) {
        $wher_conditon = "iTripId = '" . $iTripId . "'";
    }


    if (!empty($iCabRequestId) || !empty($tripId) || !empty($iTripId)) {
        $fields = "iStopId,tDestLatitude,tDestLongitude,tDAddress,eReached,eCanceled";
        $sql = "SELECT $fields FROM trips_stopoverpoint_location
   where " . $wher_conditon . " ORDER BY iStopId ASC";

        $db_trips_stopoverpoint_location = $obj->MySQLSelect($sql);
        $trips_stopoverpoint_location_data = array();

        if (!empty($db_trips_stopoverpoint_location) && count($db_trips_stopoverpoint_location) > 0) {
            foreach ($db_trips_stopoverpoint_location as $db_trips_stopoverpoint_locationKey => $db_trips_stopoverpoint_locationValue) {
                $trips_stopoverpoint_location_data[$db_trips_stopoverpoint_locationKey]['iStopId'] = $db_trips_stopoverpoint_locationValue['iStopId'];
                $trips_stopoverpoint_location_data[$db_trips_stopoverpoint_locationKey]['tDestLatitude'] = $db_trips_stopoverpoint_locationValue['tDestLatitude'];

                $trips_stopoverpoint_location_data[$db_trips_stopoverpoint_locationKey]['tDestLongitude'] = $db_trips_stopoverpoint_locationValue['tDestLongitude'];


                $trips_stopoverpoint_location_data[$db_trips_stopoverpoint_locationKey]['tDAddress'] = $db_trips_stopoverpoint_locationValue['tDAddress'];

                $trips_stopoverpoint_location_data[$db_trips_stopoverpoint_locationKey]['eReached'] = $db_trips_stopoverpoint_locationValue['eReached'];

                $trips_stopoverpoint_location_data[$db_trips_stopoverpoint_locationKey]['eCanceled'] = $db_trips_stopoverpoint_locationValue['eCanceled'];
            }
        } else {
            $trips_stopoverpoint_location_data['tDestLatitude'] = "";
            $trips_stopoverpoint_location_data['tDestLongitude'] = "";
            $trips_stopoverpoint_location_data['tDAddress'] = "";
            $trips_stopoverpoint_location_data['eReached'] = "";
            $trips_stopoverpoint_location_data['eCanceled'] = "";
            $trips_stopoverpoint_location_data['remaininStopOverPoint'] = 0;
        }
    } else {
        $trips_stopoverpoint_location_data['tDestLatitude'] = "";
        $trips_stopoverpoint_location_data['tDestLongitude'] = "";
        $trips_stopoverpoint_location_data['tDAddress'] = "";
        $trips_stopoverpoint_location_data['eReached'] = "";
        $trips_stopoverpoint_location_data['eCanceled'] = "";
        $trips_stopoverpoint_location_data['remaininStopOverPoint'] = 0;
    }
    return $trips_stopoverpoint_location_data;
}

function addActualStopOverPoint() {
    global $generalobj, $obj, $_REQUEST;
    $iStopId = isset($_REQUEST["iStopId"]) ? $_REQUEST["iStopId"] : 0;
    $dAddress = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
    $destLat = isset($_REQUEST["dest_lat"]) ? $_REQUEST["dest_lat"] : '';
    $destLon = isset($_REQUEST["dest_lon"]) ? $_REQUEST["dest_lon"] : '';
    
    //$isTripCanceled = isset($_REQUEST["isTripCanceled"]) ? $_REQUEST["isTripCanceled"] : '';
    $tripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    //$isDropAll = isset($_REQUEST["isDropAll"]) ? $_REQUEST["isDropAll"] : '';
    //$userId = isset($_REQUEST["PassengerId"]) ? $_REQUEST["PassengerId"] : '';
    $returnArr = array();
    if (isset($iStopId) && !empty($iStopId)) {
        
        $where_trips_stopoverpoint_location_data = "iStopId='" . $iStopId . "' AND iTripId=" . $tripId;
        $update_trips_stopoverpoint_location_data['tActualDAddress'] = $dAddress;
        $update_trips_stopoverpoint_location_data['tActualDestLatitude'] = $destLat;
        $update_trips_stopoverpoint_location_data['tActualDestLongitude'] = $destLon;
        $data = $obj->MySQLQueryPerform('trips_stopoverpoint_location', $update_trips_stopoverpoint_location_data, 'update', $where_trips_stopoverpoint_location_data);
        
    }
    return 1;
}

if ($type == "GetStopOverPoint") {
    $stop_over_point_history = getDropOverPointHistory();
    if (!empty($stop_over_point_history['tDestLatitude'])) {
        $stop_over_point_history = getDropOverPointHistory();
        $returnArr['message'] = $stop_over_point_history;
        $returnArr['Action'] = 0;
    } else {
        $returnArr['message'] = $stop_over_point_history;
        $returnArr['Action'] = 1;
    }
    echo json_encode($returnArr);
}
?>