<?php

function getFavSelectQuery($iMemberId) {
    return ", (SELECT IFNULL((SELECT df.eFavDriver FROM driver_favorites as df where df.iUserId=" . $iMemberId . " AND df.iDriverId = tr.iDriverId AND df.eType = IF(tr.eType = 'Multi-Delivery', 'Deliver', tr.eType) ) ,'No')) AS eFavDriver";
}

function getFavSelectQueryToLoadCabs($eType, $iMemberId) {
    if ($eType == "Multi-Delivery" || $eType == "Delivery" || $eType == "Deliver") {
        $eType = "Deliver";
    }
    return ", (SELECT IFNULL((SELECT df.eFavDriver FROM driver_favorites as df where df.iUserId=" . $iMemberId . " AND df.iDriverId = register_driver.iDriverId AND df.eType = '" . $eType . "' ) ,'No')) AS eFavDriver";
}

function addUpdateFavDriver() {
    global $_REQUEST, $obj,$vSystemDefaultLangCode,$tripDetailsArr,$userDetailsArr;
    $tripID = isset($_REQUEST["tripID"]) ? $_REQUEST["tripID"] : '';
    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? clean($_REQUEST['iDriverId']) : '';
    $eType = isset($_REQUEST['eType']) ? trim(clean($_REQUEST['eType'])) : '';
    $eFavDriver = isset($_REQUEST['eFavDriver']) ? clean($_REQUEST['eFavDriver']) : 'No'; // No=> 'Not Favorite','Yes'=> 'Favorite'
    if (!empty($tripID)) {
        //Added By HJ On 25-06-2020 For Optimize trips Table Query Start
        if(isset($tripDetailsArr['trips_'.$tripID])){
            $tripsData = $tripDetailsArr['trips_'.$tripID];
        }else{
            $tripsData = get_value('trips', '*', 'iTripId', $tripID, '');
            $tripDetailsArr['trips_'.$tripID] = $tripsData;
        }
        //Added By HJ On 25-06-2020 For Optimize trips Table Query End
        $iUserId = $tripsData[0]['iUserId'];
        $iDriverId = $tripsData[0]['iDriverId'];
        $eType = $tripsData[0]['eType'];
    }
    if ($eType == 'Multi-Delivery') {
        $eType = 'Deliver';
    }
    //Added By HJ On 25-06-2020 For Optimize register_driver Table Query Start
    if(isset($userDetailsArr['register_driver_'.$iDriverId])){
        $driverData = $userDetailsArr['register_driver_'.$iDriverId];
    }else{
        $driverData = $obj->MySQLSelect("SELECT * FROM register_driver WHERE iDriverId='".$iDriverId."'");
        $userDetailsArr['register_driver_'.$iDriverId] = $driverData;
    }
    $vLangCode =$vName=$vLastName= "";
    if(count($driverData) > 0){
        $vLangCode = $driverData[0]['vLang'];
        $vName = $driverData[0]['vName'];
        $vLastName = $driverData[0]['vLastName'];
    }
    //Added By HJ On 25-06-2020 For Optimize register_driver Table Query End
    //$vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true');
    //$vName = get_value('register_driver', 'vName', 'iDriverId', $iDriverId, '', 'true');
    //$vLastName = get_value('register_driver', 'vLastName', 'iDriverId', $iDriverId, '', 'true');
    $vName = $vName . ' ' . $vLastName;
    if ($vLangCode == "" || $vLangCode == NULL) {
        //Added By HJ On 25-06-2020 For Optimize language_master Table Query Start
        if (!empty($vSystemDefaultLangCode)) {
            $vLangCode = $vSystemDefaultLangCode;
        } else {
            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        //Added By HJ On 25-06-2020 For Optimize language_master Table Query End
    }
    $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1");
    $sql = "SELECT iDriverFavorite FROM `driver_favorites` WHERE iUserId = '" . $iUserId . "' AND iDriverId = '" . $iDriverId . "' AND eType = '" . trim($eType) . "'";
    $iDriverFavoriteData = $obj->MySQLSelect($sql);
    $tableName1 = 'driver_favorites';
    if (count($iDriverFavoriteData) > 0) {
        $iDriverFavorite = $iDriverFavoriteData[0]['iDriverFavorite'];
    }
    $Data_update_driver_favorites['iUserId'] = $iUserId;
    $Data_update_driver_favorites['iDriverId'] = $iDriverId;
    $Data_update_driver_favorites['eFavDriver'] = $eFavDriver;
    $Data_update_driver_favorites['eType'] = trim($eType);

    if (count($iDriverFavoriteData) > 0) {
        $where1 = "iDriverFavorite='" . $iDriverFavorite . "'";
        $iDriverFavoriteDataId = $obj->MySQLQueryPerform($tableName1, $Data_update_driver_favorites, 'update', $where1);
    } else {
        $iDriverFavoriteDataId = $obj->MySQLQueryPerform($tableName1, $Data_update_driver_favorites, 'insert');
    }
    if (!empty($iDriverFavoriteDataId)) {
        $favlabel = '';
        $returnArr['Action'] = "1";
        if (!empty($iDriverFavoriteDataId) && $eFavDriver == 'No') {
            $favlabel = $languageLabelsArr['LBL_FAVOURITE_REMOVE_SUCESSFULLY'];
        } else {
            $favlabel = $languageLabelsArr['LBL_FAVOURITE_ADDED_SUCESSFULLY'];
        }
        $returnArr['message'] = $vName . ' ' . $favlabel;
    } else {
        $returnArr['Action'] = "0";
        if (!empty($iDriverFavoriteDataId) && $eFavDriver == 'No') {
            $favlabel = $languageLabelsArr['LBL_FAVOURITE_REMOVE_FAIL'];
        } else {
            $favlabel = $languageLabelsArr['LBL_FAVOURITE_ADDED_FAIL'];
        }
        $returnArr['message'] = $vName . ' ' . $favlabel;
    }
    return $returnArr;
}

if ($type == "getFavDriverList") {
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : 0;
    $contentType = isset($_REQUEST['contentType']) ? clean($_REQUEST['contentType']) : 'ALL'; // ALL OR FAV
    $vFilterParam = isset($_REQUEST['vFilterParam']) ? clean($_REQUEST['vFilterParam']) : 'Ride'; //Ride,Uberx,Deliver (Mutiple selected eType)
    $vLang = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    $per_page = 10;
    $start_limit = ($page - 1) * $per_page;
    $limit = " LIMIT " . $start_limit . ", " . $per_page;

    $where = " Where df.iUserId = '" . $iUserId . "'";
    $where1 = "";
    //FAV CONDITION
    if (isset($contentType) && !empty($contentType) && $contentType == 'FAV') {
        $where .= " AND df.eFavDriver='Yes'";
    }
    //ETYPE CONDITION
    if (isset($vFilterParam) && !empty($vFilterParam)) {
        $vFilterParam = explode(',', $vFilterParam);
        if (in_array('Deliver', $vFilterParam)) {
            $vFilterParam[count($vFilterParam)] = 'Multi-Delivery';
        }
        $vFilterParam = join(',', $vFilterParam);
        $vFilterParam = "'" . str_replace(",", "','", $vFilterParam) . "'";
        $where .= " AND df.eType IN ($vFilterParam)";
        $where1 .= " AND tp.eType IN ($vFilterParam)";
    }
    if (isset($contentType) && !empty($contentType) && $contentType != 'FAV') {
        //echo $register_driver_sql = "SELECT tp.eType,(SELECT IFNULL((SELECT df.eFavDriver FROM driver_favorites as df where df.iUserId=".$iUserId." AND df.iDriverId = tp.iDriverId AND df.eType = IF(tp.eType = 'Multi-Delivery', 'Deliver', tp.eType)) ,'No')) AS eFavDriver,rd.iDriverId,rd.vAvgRating,rd.vName,rd.vImage FROM register_driver rd INNER JOIN trips tp ON tp.iDriverId = rd.iDriverId Where  tp.iUserId = '".$iUserId."' AND (tp.iActive ='Finished' OR tp.iActive ='Canceled') ORDER BY tp.eType ASC " . $limit . "";
        $register_driver_sql = "SELECT * FROM (SELECT IF(tp.eType = 'Multi-Delivery', 'Deliver', tp.eType) AS eType,(SELECT IFNULL((SELECT df.eFavDriver FROM driver_favorites as df where df.iUserId=" . $iUserId . " AND df.iDriverId = tp.iDriverId AND df.eType = IF(tp.eType = 'Multi-Delivery', 'Deliver', tp.eType)) ,'No')) AS eFavDriver,rd.iDriverId,rd.vAvgRating,CONCAT(rd.vName,' ',rd.vLastName) AS vName,rd.vImage FROM register_driver rd INNER JOIN trips tp ON tp.iDriverId = rd.iDriverId Where  tp.iUserId = '" . $iUserId . "' AND (tp.iActive ='Finished' OR tp.iActive ='Canceled') " . $where1 . " group by tp.iDriverId,tp.eType) AS X group by iDriverId,eType  ORDER BY eType ASC " . $limit . "";
        //$register_driver_sql_total = "SELECT COUNT(tp.iDriverId) As TotalIds FROM register_driver rd INNER JOIN trips tp ON tp.iDriverId = rd.iDriverId Where  tp.iUserId = '" . $iUserId . "' AND (tp.iActive ='Finished' OR tp.iActive ='Canceled') group by tp.iDriverId,tp.eType  ORDER BY tp.eType ASC";
        $register_driver_sql_total = "SELECT * FROM (SELECT IF(tp.eType = 'Multi-Delivery', 'Deliver', tp.eType) AS eType,(SELECT IFNULL((SELECT df.eFavDriver FROM driver_favorites as df where df.iUserId=" . $iUserId . " AND df.iDriverId = tp.iDriverId AND df.eType = IF(tp.eType = 'Multi-Delivery', 'Deliver', tp.eType)) ,'No')) AS eFavDriver,rd.iDriverId,rd.vAvgRating,CONCAT(rd.vName,' ',rd.vLastName) AS vName,rd.vImage FROM register_driver rd INNER JOIN trips tp ON tp.iDriverId = rd.iDriverId Where  tp.iUserId = '" . $iUserId . "' AND (tp.iActive ='Finished' OR tp.iActive ='Canceled') " . $where1 . " group by tp.iDriverId,tp.eType) AS X group by iDriverId,eType  ORDER BY eType ASC";
    } else {
        $register_driver_sql = "SELECT * FROM (SELECT df.eType AS eType,UPPER(df.eType) AS eType1,df.eFavDriver,rd.iDriverId,rd.vAvgRating,CONCAT(rd.vName,' ',rd.vLastName) AS vName,rd.vImage FROM driver_favorites df INNER JOIN register_driver rd ON df.iDriverId = rd.iDriverId " . $where . ") AS X ORDER BY eType1 ASC " . $limit . "";
        //$register_driver_sql_total = "SELECT COUNT(rd.iDriverId) As TotalIds FROM driver_favorites df INNER JOIN register_driver rd ON df.iDriverId = rd.iDriverId " . $where . " ORDER BY df.eType ASC";
        $register_driver_sql_total = "SELECT *  FROM (SELECT df.eType AS eType,UPPER(df.eType) AS eType1,df.eFavDriver,rd.iDriverId,rd.vAvgRating,CONCAT(rd.vName,' ',rd.vLastName) AS vName,rd.vImage FROM driver_favorites df INNER JOIN register_driver rd ON df.iDriverId = rd.iDriverId " . $where . ") AS X ORDER BY eType1 ASC";
    }
    $Data = $obj->MySQLSelect($register_driver_sql);
    $data_count_all = $obj->MySQLSelect($register_driver_sql_total);
    
    //$TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
    $TotalPages = ceil(count($data_count_all) / $per_page);

    $arr_cat = array();
    $i = $k = 0;
    for ($j = 0; $j < count($Data); $j++) {
        if (($Data[$j]['eType'] == 'Multi-Delivery') && in_array($Data[$j]['iDriverId'], $iDriveIds_Arr)) {
            continue;
        }
        if ($Data[$j]['eType'] == 'Multi-Delivery') {
            $eType = 'Deliver';
        } else {
            $eType = $Data[$j]['eType'];
        }
        if (($Data[$j]['eType'] == 'Multi-Delivery' || $Data[$j]['eType'] == 'Deliver')) {
            $iDriveIds_Arr[$k] = $Data[$j]['iDriverId'];
            $k++;
        }

        $arr_cat[$i]['vService_BG_color'] = RANDOM_COLORS_ARR[array_rand(RANDOM_COLORS_ARR, 1)];
        $arr_cat[$i]['vService_TEXT_color'] = "#FFFFFF";

        $arr_cat[$i]['iDriverId'] = $Data[$j]['iDriverId'];
        $arr_cat[$i]['vAvgRating'] = $Data[$j]['vAvgRating'];
        $arr_cat[$i]['vName'] = $Data[$j]['vName'];
        $driver_img_path = "";
        if ($Data[$i]['vImage'] != "" && file_exists($tconfig["tsite_upload_images_driver_path"] . "/" . $arr_cat[$i]['iDriverId'] . "/2_" . $Data[$j]['vImage'])) {
            $driver_img_path = $tconfig["tsite_upload_images_driver"] . "/" . $arr_cat[$j]['iDriverId'] . "/2_" . $Data[$i]['vImage'];
        }
        //$Photo_Gallery_folder = $tconfig['tsite_upload_driver_doc'] . "/" . $iMemberId . "/";
        $arr_cat[$i]['vImage'] = $driver_img_path;
        $arr_cat[$i]['eType'] = $eType;
        $arr_cat[$i]['eFavDriver'] = $Data[$j]['eFavDriver'];
        $i++;
    }
    if (count($Data) > 0) {
        $returnData['Action'] = "1";
        $returnData['message'] = $arr_cat;
    } else {
        $returnData['Action'] = "0";
        $returnData['message'] = "LBL_NO_DATA_AVAIL";
    }
    //$returnData['AppTypeFilterArr'] = AppTypeFilterArrFav($iUserId, 'Passenger', $vLang); //Commented By HJ On 13-11-2019 As Per Discuss with KS Sir
    $enableFlyFilter = "No"; // Added By HJ On 13-11-2019 As Per Discuss With KS In Cubex Remove Fly Filter
    $returnData['AppTypeFilterArr'] = AppTypeFilterArr($iUserId, 'Passenger', $vLang,$enableFlyFilter); //Added By HJ On 13-11-2019 As Per Discuss with KS Sir
    if ($vFilterParam == "") {
        $vFilterParam = "All";
    }
    $returnData['eFilterSel'] = $vFilterParam;
    if ($TotalPages > $page) {
        $returnData['NextPage'] = "" . ($page + 1);
    } else {
        $returnData['NextPage'] = "0";
    }
    echo json_encode($returnData);
}
if ($type == "addDriverInFavList") {
    setDataResponse(addUpdateFavDriver());
}
###################################### AppTypeFilterArr ##################################################

function AppTypeFilterArrFav($iMemberId, $UserType, $vLang) {
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
    $LBL_RIDE_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_RIDE', " and vCode='" . $vLang . "'", 'true');
    $LBL_DELIVER = get_value('language_label', 'vValue', 'vLabel', 'LBL_DELIVERY', " and vCode='" . $vLang . "'", 'true');
    $LBL_JOB_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_SERVICES', " and vCode='" . $vLang . "'", 'true');
    $LBL_ALL = get_value('language_label', 'vValue', 'vLabel', 'LBL_ALL', " and vCode='" . $vLang . "'", 'true');
    $returnArr[0]["vTitle"] = $LBL_ALL;
    $returnArr[0]["vFilterParam"] = "";
    if ($APP_TYPE == "Ride-Delivery" || $APP_TYPE == "Ride-Delivery-UberX") {
        $returnArr[1]["vTitle"] = $LBL_RIDE_TXT;
        $returnArr[1]["vFilterParam"] = "Ride";
        $returnArr[2]["vTitle"] = $LBL_DELIVER;
        $returnArr[2]["vFilterParam"] = "Deliver";
        if ($APP_TYPE == "Ride-Delivery-UberX") {
            $returnArr[3]["vTitle"] = $LBL_JOB_TXT;
            $returnArr[3]["vFilterParam"] = "UberX";
        }
    }


    return $returnArr;
}

?>